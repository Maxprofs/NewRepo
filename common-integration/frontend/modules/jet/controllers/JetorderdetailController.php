<?php

namespace frontend\modules\jet\controllers;

use frontend\modules\jet\models\JetOrderDetail;
use frontend\modules\jet\models\JetOrderDetailSearch;
use frontend\modules\jet\models\JetOrderImportError;
use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\models\JetProductVariants;

use common\models\User;
use frontend\modules\jet\components\Dashboard\OrderInfo;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Orderdata;
use frontend\modules\jet\controllers\JetorderimporterrorController;

use yii\helpers\Url;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * JetOrderDetailController implements the CRUD actions for JetOrderDetail model.
 */
class JetorderdetailController extends JetmainController
{   
	protected $sc,$jetHelper;
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    /**
     * Lists all JetOrderDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        if(Yii::$app->user->isGuest) 
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        
        $searchModel = new JetOrderDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $merchant_id = MERCHANT_ID;
        $resultdata = [];
        $query="SELECT count(*) id FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND status='acknowledged' AND shopify_order_id='' LIMIT 0,1";        
        $resultdata = Data::sqlRecords($query,"one","select");
        $countOrders = $countReadyOrders = 0;
        if(isset($resultdata['id']) && $resultdata['id']>0)
            $countOrders=$resultdata['id'];
        
        $status=false;
        if($this->jetHelper)
        {
            $orderResponse=$this->jetHelper->CGetRequest('/orders/ready',$merchant_id,$status); 
            $orderResponseUrls=json_decode($orderResponse,true);
            if($status==200 && isset($orderResponseUrls['order_urls']) && count($orderResponseUrls['order_urls'])>0)
            {
                $countReadyOrders = count($orderResponseUrls['order_urls']);
            }
        }
        return $this->render('index', [
            'countReadyOrders'=>$countReadyOrders,
            'countOrders'=>$countOrders,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);       
    }
            
    /**
     * Creates a new JetOrderDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    
    public function actionCreate()
    {
        $merchant_id=MERCHANT_ID;          
        $model = $error_array = [];                
        $count=0;
        if(API_USER)
        {
            try
            {
                $countOrder=0;
                $orderdata="";
                $response = [];
                $responseOrder = Orderdata::createOrder($this->jetHelper,$merchant_id,$merchantEmail=null,$mailData,$error_array);
                
                if(isset($responseOrder['success'])){
                    $countOrder=$responseOrder['success'];
                }        
            }
            catch (Exception $e)
            {
                Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
                return $this->redirect(['index']);
            }
        }
        //create order import error
        $errorCount=0;
        $count=0;
        if(is_array($error_array) && count($error_array)>0)
        {
            $errorFlag=false;
            $message1="";
            foreach ($error_array as $order_error)
            {
            	$isAutoCancel = $post = $result = [];
                $post['merchant_id']=MERCHANT_ID;
        		$post['merchant_order_id']=$order_error['merchant_order_id'];
        		$post['jetHelper']= $this->jetHelper;
                $isAutoCancel = Data::sqlRecords("SELECT `value`  FROM `jet_config` WHERE `merchant_id` = {$merchant_id} AND `data` ='cancel_order'",'one','select');
		        if (!empty($isAutoCancel) && $isAutoCancel['value']=='Yes') {
		        	$obj = new JetorderimporterrorController(Yii::$app->controller->id,'');
		    		$obj->actionCancel($post);
		        }
                
                $result = Data::sqlRecords("SELECT `merchant_order_id` FROM `jet_order_import_error` WHERE merchant_order_id='".$order_error['merchant_order_id']."' LIMIT 0,1","one","select");
                if($result)
                {
                    $count=0;
                    continue;
                }
                else
                {                	                	                	
                    $sql='INSERT INTO `jet_order_import_error`(`merchant_order_id`,`reference_order_id`,`merchant_id`,`reason`)
                            VALUES("'.$order_error['merchant_order_id'].'","'.$order_error['reference_order_id'].'","'.$order_error['merchant_id'].'","'.$order_error['reason'].'")';
                    try{
                        $errorCount++;
                        Data::sqlRecords($sql,null,"insert");                                                                     
                    }catch(Exception $e){
                        $message1.='Invalid query: ' . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        OrderInfo::removeFailedOrders($merchant_id);
        if($count>0)
            Yii::$app->session->setFlash('error',"There is error for some orders.Please <a href=".Yii::getAlias('@webjeturl')."/jetorderimporterror/index>click</a> to check failed order errors.");
        if($countOrder>0)
            Yii::$app->session->setFlash('success', $countOrder." order has been fetched from jet successfully");
        return $this->redirect(['index']);
    }
    
    public function actionSyncorder()
    {
        $merchant_id=MERCHANT_ID;
        
        if(SHOP)
        {
            try
            {
                $countOrder=0;
                $token = $shopname = $shopifyError = "";
                
                $token = TOKEN;
                $shopname = SHOP;
                
                $resultdata = $configSetting = [];
                
                $configSetting = Jetproductinfo::getConfigSettings($merchant_id);                
                $query="SELECT `merchant_order_id`,`reference_order_id`,`order_data` FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND status='acknowledged' AND shopify_order_id=''";
                
                $resultdata = Data::sqlRecords($query,'all','select');
                
                if( !empty($resultdata) && count($resultdata)>0)
                {
                    foreach($resultdata as $val)
                    {
                        $Orderarray = $itemArray = $result = $fulfillment_variant_ids = $findSku = [];
                        $OrderTotal = $shippingTax = $itemTax = 0.00;
                        $autoReject = false;
                        $ikey=0;
                       
                        $result = json_decode($val['order_data'],true);
                        
                        if(count($result)>0)
                        {
                            foreach ($result['order_items'] as $key=>$value)
                            {
                                $collection = [];
                                
                                $query="SELECT id,sku,vendor,variant_id,qty,title FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND sku='".addslashes(trim($value['merchant_sku']))."'";
                                $collection = Data::sqlRecords($query,'one','select');
                                $findSku = explode('-',addslashes(trim($value['merchant_sku'])));
                                if(empty($collection))
                                {
                                    $collectionOption = [];
                                    $query="SELECT option_id,product_id,option_sku,vendor,option_qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND option_sku='".addslashes(trim($value['merchant_sku']))."'";
                                    
                                    $collectionOption = Data::sqlRecords($query,'one','select');
                                                                        
                                    if(empty($collectionOption))
                                    {                                       
                                        if ( (!empty($findSku)) && isset($findSku[0],$findSku[1]) ) 
                                        {
                                            $collection1 = [];
                                            $query="SELECT id,sku,vendor,variant_id,qty,title FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `id` = '{$findSku[0]}' AND `variant_id`='{$findSku[1]}' ";
                                            $collection1 = Data::sqlRecords($query,'one','select'); 
                                            if (empty($collection1)) 
                                            {
                                                $collectionOption1 = array();
                                                $query="SELECT option_id,product_id,option_sku,vendor,option_qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `product_id`='{$findSku[0]}' AND `option_id`='{$findSku[1]}' ";
                                                $collectionOption1 = Data::sqlRecords($query,'one','select');
                                                if(empty($collectionOption1))
                                                {                                       
                                                    continue;
                                                }
                                                else
                                                {
                                                    $itemArray[$ikey]['product_id']=$collectionOption1['product_id'];
                                                    $itemArray[$ikey]['title']=$value['product_title'];
                                                    $itemArray[$ikey]['variant_id']=$collectionOption1['option_id'];
                                                    $itemArray[$ikey]['vendor']=$collectionOption1['vendor'];
                                                    $itemArray[$ikey]['sku']=$collectionOption1['option_sku'];
                                                }
                                            }                                       
                                            else
                                            {
                                                $itemArray[$ikey]['product_id']=$collection1['id'];
                                                $itemArray[$ikey]['title']=$collection1['title'];
                                                $itemArray[$ikey]['variant_id']=$collection1['variant_id'];
                                                $itemArray[$ikey]['vendor']=$collection1['vendor'];
                                                $itemArray[$ikey]['sku']=$collection1['sku'];
                                            }
                                        }
                                        else
                                        {
                                            continue;
                                        } 
                                    }
                                    else
                                    {
                                        $itemArray[$ikey]['product_id']=$collectionOption['product_id'];
                                        $itemArray[$ikey]['title']=$value['product_title'];
                                        $itemArray[$ikey]['variant_id']=$collectionOption['option_id'];
                                        $itemArray[$ikey]['vendor']=$collectionOption['vendor'];
                                        $itemArray[$ikey]['sku']=$collectionOption['option_sku'];                                        
                                    }                                                                      
                                }                                
                                else
                                {
                                    $itemArray[$ikey]['product_id']=$collection['id'];
                                    $itemArray[$ikey]['title']=$collection['title'];
                                    $itemArray[$ikey]['variant_id']=$collection['variant_id'];
                                    $itemArray[$ikey]['vendor']=$collection['vendor'];
                                    $itemArray[$ikey]['sku']=$collection['sku'];
                                }
                                $qty=0;
                                $Totalprice=0.00;
                                $qty=$value['request_order_quantity'];
                                $itemArray[$ikey]['id']=$value['order_item_id'];
                                $itemArray[$ikey]['price']=$value['item_price']['base_price'];
                                $shippingTax+=$value['item_price']['item_shipping_tax']+$value['item_price']['item_shipping_cost'];
                                $itemTax=$value['item_price']['item_tax'];
                                $itemArray[$ikey]['quantity']=$qty;
                                $itemArray[$ikey]['requires_shipping']=true;
                                
                                //fulfillment_service for product 
                                $prodVariants=$this->sc->call('GET',"/admin/variants/".$itemArray[$ikey]['variant_id'].".json");
                                if(isset($prodVariants['fulfillment_service']) && ($prodVariants['fulfillment_service']=='amazon_marketplace_web' || $prodVariants['inventory_management']=='amazon_marketplace_web'))
                                {
                                   $fulfillment_variant_ids[]=$itemArray[$ikey]['variant_id'];
                                   $itemArray[$ikey]['fulfillment_service']='amazon_marketplace_web'; 
                                }
                                $ikey++;
                            }
                        }
                        
                        $customer_Info=[];
                        $first_name="";
                        $last_name="";
                        if(isset($result['shipping_to']['recipient']['name']))
                        {
                            $customer_Info = $result['shipping_to']['recipient']['name'];
                        } 
                        else 
                        {
                            $customer_Info=$result['buyer']['name'];
                        }
                        $customer_Info = preg_replace('/\s+/', ' ', $customer_Info);
                        $customer_Info = explode(" ", $customer_Info);
                        $first_name=$customer_Info[0];
                        if(isset($customer_Info[1]) && $customer_Info[1])
                            $last_name = $customer_Info[1];
                        else
                            $last_name = $first_name;   
                        $email="";
                        if(isset($configSetting['fba']) && $configSetting['fba']=='yes') 
                        {
                            $sql_email = 'SELECT email FROM jet_shop_details where merchant_id='.$merchant_id;
                            $model_email = Data::sqlRecords($sql_email,'one','select');
                            $email=$model_email->email;
                        }
                        $first_addr="";$second_addr="";
                        $first_addr=$result['shipping_to']['address']['address1'];
                        $second_addr=$result['shipping_to']['address']['address2'];
                        $phone_number="";
                        $phone_number=isset($result['shipping_to']['recipient']['phone_number']) ? $result['shipping_to']['recipient']['phone_number'] : time(); 
                        $billing_addr = $shipping_addr = [];
                        //add shipping lines for items
                        $item_info=isset($result['order_totals']['item_price']['tax_info'])?$result['order_totals']['item_price']['tax_info']:"";
                        if(!$item_info)
                            $item_info="Jet Item Tax";
                        $tax_lines=[];
                        if($itemTax>0)
                            $tax_lines=[["title"=> $item_info,"price"=> $itemTax]];
                        $shipping_carrier=isset($result['order_detail']['request_shipping_method'])?$result['order_detail']['request_shipping_method']:$result['order_detail']['request_shipping_carrier'];
                        $shipping_level=isset($result['order_detail']['request_service_level'])?$result['order_detail']['request_service_level']:$shipping_carrier;
                        $billing_addr=array(
                            "first_name"=> $first_name,
                            "last_name"=>$last_name,
                            "address1"=> $first_addr,
                            "address2"=> $second_addr,
                            "phone"=>$phone_number,
                            "city"=> $result['shipping_to']['address']['city'],
                            "province"=> $result['shipping_to']['address']['state'],
                            "country"=> "United States",
                            "zip"=> $result['shipping_to']['address']['zip_code']
                        );
                        
                        if(count($itemArray)>0)
                        {
                            $Orderarray['order']=array(
                                "line_items"=>$itemArray,
                                "customer"=>array(
                                    "first_name"=> $first_name,
                                    "last_name"=> $last_name,
                                    "email"=> $email
                                ),
                                "billing_address"=> $billing_addr,
                                "shipping_address"=> $billing_addr,
                                "note"=>"Jet-Integration(".$val['reference_order_id'].")",
                                'tags'=>"jet.com",
                                "email"=> $email,
                                "inventory_behaviour"=>"decrement_obeying_policy",
                                "financial_status"=>"paid",   //"financial_status"=>"pending",
                                "shipping_lines"=> array(
                                  array(
                                    "title"=> $shipping_carrier,
                                    "price"=> $shippingTax,
                                    "code"=> $shipping_carrier,
                                    "source"=> "Jet",
                                    "requested_fulfillment_service_id"=> null,
                                    "delivery_category"=> null,
                                    "carrier_identifier"=>$shipping_level,
                                    "tax_lines"=> $tax_lines,
                                  )
                                ),
                                "format"=> "json"
                            );
                            if($shipping_carrier=="" && $shipping_level=="")
                            {
                              if($merchant_id==397)
                              {
                                $Orderarray['order']['shipping_lines'][0]['title']="Standard";
                                $Orderarray['order']['shipping_lines'][0]['code']="Standard";
                                $Orderarray['order']['shipping_lines'][0]['carrier_identifier']="Standard";
                              }
                              else
                                unset($Orderarray['order']['shipping_lines']);
                            }
                            elseif($shipping_level!="" && $shipping_carrier=="") 
                            {
                              $Orderarray['order']['shipping_lines'][0]['title']=$shipping_level;
                              $Orderarray['order']['shipping_lines'][0]['code']=$shipping_level;
                            }
                            $response=array();
                            $response = $this->sc->call('POST', '/admin/orders.json',$Orderarray);
                            
                            $lineArray=array();
                            if(!array_key_exists('errors',$response))
                            {
                                //send request for order acknowledge
                                $linesItemFulfillment=[];
                                foreach($response['line_items'] as $key=>$value)
                                {
                                    $lineArray[$key]=$value['id'];
                                    if(is_array($fulfillment_variant_ids) && in_array($value['variant_id'], $fulfillment_variant_ids))
                                    {
                                        $linesItemFulfillment[$key]['id']=$value['id'];
                                    }
                                }
                                $queryObj="";
                                $query="UPDATE `jet_order_detail` SET  shopify_order_name='".$response['name']."',shopify_order_id='".$response['id']."',lines_items='".implode(',',$lineArray)."'
                                                    where merchant_id='".$merchant_id."' AND merchant_order_id='".$val['merchant_order_id']."'";
                                $countOrder++;
                                Data::sqlRecords($query,null,'update');
                                if(is_array($fulfillment_variant_ids) && count($fulfillment_variant_ids)>0) 
                                {
                                    $shopifyShip['fulfillment']=[
                                        'line_items'=>$linesItemFulfillment,
                                    ];
                                    $shipmentResponse = $this->sc->call('POST', '/admin/orders/'.$response['id'].'/fulfillments.json',$shopifyShip);
                                }
                            }
                            else
                            {
                                $shopifyError.=$val['merchant_order_id']."=> Error: ".json_encode($response['errors'])."\n";
                            }
                        }
                    }
                }
                else
                {
                    Yii::$app->session->setFlash('success', "No more order available to sync on store..");
                }
                if($shopifyError){
                    Yii::$app->session->setFlash('error', "Order(s) not created in shopify:\n".$shopifyError);
                }
                unset($Orderarray);unset($itemArray);unset($result);unset($response); unset($lineArray);unset($resultdata);
            }
            catch (Exception $e)
            {
                Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
            }
        }
        if($countOrder>0){
            Yii::$app->session->setFlash('success', $countOrder." Order(s) has been created in shopify");
        }
        return $this->redirect(['index']);
    }
    /* Fetch Acknowledge order from jet*/
    public function actionFetchackorder()
    {       
        $query=$model=$queryObj="";
        $merchant_id=MERCHANT_ID;
        $query="SELECT merchant_id, fullfilment_node_id, api_host, api_user, api_password, username, auth_key FROM `jet_configuration` config INNER JOIN `user` user_m ON (user_m.id = config.merchant_id) where config.merchant_id='".$merchant_id."'";
        $model = Data::sqlRecords($query,'one','select');
        if($model)
        {
            try
            {
                $countOrder=0;
                $orderdata="";
                $response=[];
                $orderdata = $this->jetHelper->CGetRequest('/orders/acknowledged',$merchant_id);
                $response  = json_decode($orderdata,True);
                if(isset($response['order_urls']) && count($response['order_urls']) > 0)
                {
                    foreach($response['order_urls'] as $jetorderurl)
                    {
                        $result1 = $result = "";
                        $result1 = $this->jetHelper->CGetRequest($jetorderurl,$merchant_id);
                        $result = json_decode($result,true);
                        
                        if(sizeof($result) > 0 && isset($result['merchant_order_id']))
                        {
                            $resultdata = [];
                            
                            $merchantOrderid = $result['merchant_order_id'];
                            $reference_order_id = $result['reference_order_id'];

                            $queryObj="";
                            $query="SELECT `merchant_order_id` FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND merchant_order_id='".$merchantOrderid."'";
                            $resultdata = Data::sqlRecords($query,'one','select');
                            
                            if(!$resultdata)
                            {
                                $OrderItemData=[];
                                $autoReject = false;
                                $i = $ikey = 0;
                                foreach ($result['order_items'] as $key=>$value)
                                {                                   
                                    $OrderItemData['sku'][]=$value['merchant_sku'];
                                    $OrderItemData['order_item_id'][]=$value['order_item_id'];
                                }
                                
                                if(isset($result['order_items']) && count($result['order_items'])>0)
                                {                                   
                                    if(isset($ackData['errors'])){
                                        $countOrder++;
                                        $queryObj="";
                                        $query='INSERT INTO `jet_order_detail`
                                                        (
                                                            `merchant_id`,
                                                            `merchant_order_id`,
                                                            `order_data`,
                                                            `reference_order_id`,
                                                            `status`,
                                                            `merchant_sku`,
                                                            `order_item_id`,
                                                            `deliver_by`
                                                        )
                                                        VALUES(
                                                            "'.$merchant_id.'",
                                                            "'.$result['merchant_order_id'].'",
                                                            "'.addslashes($result1).'",
                                                            "'.$result['reference_order_id'].'",
                                                            "acknowledged",
                                                            "'.implode(',',$OrderItemData['sku']).'",
                                                            "'.implode(',',$OrderItemData['order_item_id']).'",
                                                            "'.$result['order_detail']['request_delivery_by'].'"
                                                        )';
                                        Data::sqlRecords($query,null,'insert');
                                    }
                                }
                                unset($response,$result,$resultdata,$ackData);
                            }                                   
                        }
                    }                   
                }                   
            }
            catch (Exception $e)
            {
                Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
                return $this->redirect(['index']);
            }
        } 
        if ($countOrder!==0) {
                Yii::$app->session->setFlash('success', $countOrder." Acknowledge Order successfully fetched ");
                return $this->redirect(['index']);
        }
        else{
            Yii::$app->session->setFlash('error', " No Acknowledge Order for fetch in App");
                return $this->redirect(['index']);
        }
    }   
    /*fetch ack end*/
    /**
     * Finds the JetOrderDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JetOrderDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JetOrderDetail::findOne($id)) !== null) 
        {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionShipment($id)
    {        
        if(Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $model = $this->findModel($id);
        $merchant_id = MERCHANT_ID;
        $jetHelper="";
        
        if($model->load(Yii::$app->request->post()))
        {
            $data=Yii::$app->request->post();
            $productModel = new JetProduct();
            
            //get jet order_items
            $shippingItems = $shipment_arr = $shopify_shipment_data = $resultConfig = $order_items = $jetorderData = $itemArray = $data_ship = array();
            $jetorderData=json_decode($model->order_data,true);
            $order_items=$jetorderData['order_items'];
            //timezone offset
            $offset_end = $this->getStandardOffsetUTC(); 
            if(empty($offset_end) || trim($offset_end)=='')
                $offset = '.0000000-00:00';
            else
                $offset = '.0000000'.trim($offset_end);
            
            $expected_delivery_date = date("Y-m-d\TH:i:s").$offset; 
           
            $merchant_order_id=$data['JetOrderDetail']['merchant_order_id'];
            $shopify_order_id=$data['JetOrderDetail']['shopify_order_id'];
                       
            $request_shipping_carrier= isset($data['JetOrderDetail']['request_shipping_carrier']) ? $data['JetOrderDetail']['request_shipping_carrier'] : "";
            
            $tracking_number = $data['JetOrderDetail']['tracking_number'];
            if (strlen($tracking_number)<=4) 
            {                
                Yii::$app->session->setFlash('error','Tracking Number Must be Greater Than 4 Digit');
                return $this->redirect(Yii::$app->request->referrer);                    
            }
                        
            $shippingItems['sku']=$data['JetOrderDetail']['merchant_sku'];
            $shippingItems['qty']=$data['JetOrderDetail']['response_shipment_sku_quantity'];
            $shippingItems['request_order_quantity']=$data['JetOrderDetail']['response_shipment_sku_quantity_hidden'];
            if($shippingItems['qty']>$shippingItems['request_order_quantity'])
            {
                Yii::$app->session->setFlash('error','shipment quantity should be less than or equal to ordered quantity');
                return $this->redirect(Yii::$app->request->referrer);  
            }
            $shippingItems['request_order_cancel_quantity']=$data['JetOrderDetail']['response_shipment_cancel_quantity_hidden'];
            $shippingItems['send_return_address']=$data['JetOrderDetail']['send_return_address'];
            $shippingItems['RMA_number']=$data['JetOrderDetail']['RMA_number'];
            $shippingItems['days_to_return']=$data['JetOrderDetail']['days_to_return'];
            
            //fetch address information..                       
            $query="SELECT `data`,`value` FROM `jet_config` WHERE `merchant_id`=".$merchant_id;             
            $resultConfig = Data::sqlRecords($query,"all","select");
            if(!empty($resultConfig) && count($resultConfig)>0)
            {
                foreach($resultConfig as $v)
                {
                    $addressDetails[$v['data']] = $v['value'];                    
                }
            }

            if($addressDetails['first_address'] =='' || $addressDetails['city'] =='' || $addressDetails['state'] =='' || $addressDetails['zipcode']=='')
            {
                Yii::$app->session->setFlash('error','Kidnly fill the return address details');
                return $this->redirect(Yii::getAlias('@webjeturl').'/jetconfiguration/index',302);
            }
            $array_return = array('address1'=>$addressDetails['first_address'],
                    'address2'=>isset($addressDetails['second_address'])? $addressDetails['second_address'] : "",
                    'city'=>$addressDetails['city'],
                    'state'=>$addressDetails['state'],
                    'zip_code'=>$addressDetails['zipcode']
            );
            
            try
            {                
                foreach($shippingItems['sku'] as $key=>$value)
                {
                    $sku=$value; 
                    $cancel_qunt=0;
                    if(isset($shippingItems['request_order_cancel_quantity'][$key]) && $shippingItems['request_order_cancel_quantity'][$key]>0)
                    {
                        $cancel_qunt = $shippingItems['request_order_cancel_quantity'][$key];
                    }
                    $shipment_id=time();
                    if($shippingItems['send_return_address'][$key]==1)
                    {
                        $RMA_number = isset($shippingItems['RMA_number'][$key])?$shippingItems['RMA_number'][$key]: "";
                        $days_to_return = isset($shippingItems['days_to_return'][$key])?$shippingItems['days_to_return'][$key]:30;
                
                        $shipment_arr[]= array(                            
                            'merchant_sku'=>$sku,
                            'response_shipment_sku_quantity'=>(int)$shippingItems['qty'][$key],
                            'response_shipment_cancel_qty'=>(int)$cancel_qunt,
                            'RMA_number'=>$RMA_number,
                            'days_to_return'=>(int)$days_to_return,
                            'return_location'=>$array_return
                        );
                    }
                    else
                    {
                        $shipment_arr[]= array(                            
                            'merchant_sku'=>$sku,
                            'response_shipment_sku_quantity'=>(int)$shippingItems['qty'][$key],
                            'response_shipment_cancel_qty'=>(int)$cancel_qunt
                        );
                    }                    
                }

                if (($request_shipping_carrier=='UPS') || ($request_shipping_carrier=='USPS') || ($request_shipping_carrier=='FedEx') ) 
                {
                    $sent_shipping_carrier = $request_shipping_carrier;
                    $sent_shipping_method = "";
                }else{
                    $sent_shipping_carrier = $request_shipping_carrier;
                    $sent_shipping_method = $request_shipping_carrier;
                }
                $data_ship['shipments'][]=array (
                    'shipment_tracking_number'=>$tracking_number,
                    'response_shipment_date'=>$expected_delivery_date,
                    'response_shipment_method'=>$sent_shipping_method,
                    'expected_delivery_date'=>$expected_delivery_date,
                    'ship_from_zip_code'=>(string)$addressDetails['zipcode'],
                    'carrier_pick_up_date'=>$expected_delivery_date,
                    'carrier'=>$sent_shipping_carrier,
                    'shipment_items'=>$shipment_arr
                );               
            
                if(!empty($data_ship))
                {
                    $status='';
                    $data=$this->jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($data_ship),$merchant_id,$status);
                    $responseArray=json_decode($data,true);                    
                    if(isset($responseArray['errors']))
                    {                        
                        Yii::$app->session->setFlash('error','Shipment not created for order id: '.$merchant_order_id.'.Please see error: '.json_encode($responseArray['errors']));
                        return $this->redirect(['index']);
                    }
                    else
                    {
                        $data_ship['timestamp']=date('d-m-Y H:i:s');

                        $newresponse = $responseOrders = [];
                        $newresponse = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchant_order_id,$merchant_id);         
                        $responseOrders = json_decode($newresponse,true);
                        if ($status==204) 
                        {
                            $query = "UPDATE `jet_order_detail` SET  status='".$responseOrders['status']."',`shipment_data`='".addslashes(json_encode($data_ship))."',`shipped_at`='".date('Y-m-d H:i:s')."'  where merchant_id='".$merchant_id."' AND merchant_order_id='".$merchant_order_id."'  ";
                            Data::sqlRecords($query , null,"update");
                              // start sending notification to client , when order placed(for IOS and Android app)
                            $url = Yii::getAlias('@webjeturl')."/jetapi/jetnotification/order-fulfilment";
                            $curtRequestParams =['order_id'=> $responseOrders['reference_order_id'],'merchant_id'=>$merchant_id,'order_status'=>$responseOrders['status']];
                            Data::curlrequest($url,$curtRequestParams,$merchant_id);
                           //$model->save(false);

                           Yii::$app->session->setFlash('success','Shipment is created successfully for order id: '.$merchant_order_id);
                           return $this->redirect(['index']);
                        }
                    }
                }
            }
            catch (Exception $e)
            {        
                Yii::$app->session->setFlash('error', $e->getMessage());
                return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);                
            }                  
        }
        else
        {
            $carriers=[
                'FedEx','FedEx SmartPost','FedEx Freight','Fedex Ground','UPS','UPS Freight','UPS Mail Innovations','UPS SurePost','OnTrac','OnTrac Direct Post','DHL','DHL Global Mail','USPS','CEVA','Laser Ship','Spee Dee','A Duie Pyle','A1','ABF','APEX','Averitt','Dynamex','Eastern Connection','Ensenda','Estes','Land Air Express','Lone Star','Meyer','New Penn','Pilot','Prestige','RBF','Reddaway','RL Carriers','Roadrunner','Southeastern Freight','UDS','UES','YRC','GSO','A&M Trucking','SAIA Freight','Other'
            ];
            return $this->render('update', [
                'model' => $model,'carriers'=>$carriers,
            ]);
        }
    }
    
    public function actionCancelorderskupopup()
    {
        $this->layout="main2";
        $id=Yii::$app->request->post('id');
        $orderCollection=Data::sqlRecords("SELECT `order_data` FROM `jet_order_detail` WHERE id='".$id."' LIMIT 0,1","one","select");
        if(isset($orderCollection['order_data']))
        {
            $orderData=json_decode($orderCollection['order_data'],true);
            $html=$this->render('cancelorderskupopup',['order_data'=>$orderData],true);
            return $html;   
        }
        return "";                       
    }

    public function actionCancelsingleordersku()
    {
        $data=Yii::$app->request->post();
        if(isset($data['merchant_order_id']))
        {
            $merchant_order_id=$data['merchant_order_id'];
            $merchant_id=$data['merchant_id'];
            $shipment_items=[];
            $skuData=[];
            foreach ($data['cancel_data'] as $key => $value) 
            {
                if($value['request_cancel_quantity']>0 && $value['request_cancel_quantity']<=$value['request_order_quantity'])
                {
                    $shipment_items[]=
                    [
                        "merchant_sku"=> (string)$key,
                        "response_shipment_cancel_qty"=> (int)$value['request_cancel_quantity']
                    ];
                    $skuData[$key]=$value['request_cancel_quantity'];
                }
            }
            if(is_array($shipment_items) && count($shipment_items)>0)
            {
                $shipment_arr[] = ["alt_shipment_id" => (string)time(), "shipment_items" => $shipment_items];
                $shipmentData = array(
                    "alt_order_id"=>(string)time(),
                    "shipments"=>$shipment_arr,
                );                            
                $result="";
                $response = $responseOrders = [];
                $status=false;
                $result=$this->jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($shipmentData),$merchant_id,$status);
                $newresponse = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchant_order_id,$merchant_id);         
                $responseOrders = json_decode($newresponse,true);
                if($status==204) 
                {
                    if(isset($responseOrders['status']))
                    {
                        $query = "UPDATE `jet_order_detail` SET  `order_real_status`='canceled', status='".$responseOrders['status']."'  where merchant_id='".$merchant_id."' AND merchant_order_id='".$merchant_order_id."'";
                        Data::sqlRecords($query , null,"update");
                    }
                    foreach ($skuData as $k_sku=>$val) 
                    {
                        $flag=false;
                        $product_ID="";
                        $product=JetProduct::find()->select('qty,variant_id')->where(['merchant_id' => $merchant_id,'sku'=>$k_sku])->one();
                        if(!isset($product['qty']))
                        {
                            $vproduct=JetProductVariants::find()->select('option_qty,option_id')->where(['merchant_id' => $merchant_id,'option_sku'=>$k_sku])->one();
                            if(isset($vproduct['option_qty']))
                            {
                                $product_ID = $vproduct['option_id'];
                                $oldQty = (int)$vproduct['option_qty'];
                            }
                        }
                        else
                        {
                            $product_ID = $product['variant_id'];
                            $oldQty = (int)$product['qty'];
                        } 
                        if($product_ID)
                        {
                            $updateQty=(int)($oldQty+$val);    
                            $updateInventory['variant']=array(
                                        "id" => $product_ID,
                                        "inventory_quantity"=> $updateQty,
                            );
                            $this->sc->call('PUT', '/admin/variants/'.$product_ID.'.json',$updateInventory);                           
                        } 
                    }
                    return "success";
                }        
            }              
            return "failed";
        }
    }

    public function actionShipmentpartial()
    {
        $this->layout="main2";
        $merchant_id = MERCHANT_ID;
        $merchant_order_id=Yii::$app->request->post('merchant_order_id');
        $resFulfill = $resFulfillment = $carriers =  [];
        
        if(API_USER)
        {
            $fullfillmentnodeid = FULLFILMENT_NODE_ID;            
            $resFulfill = $this->jetHelper->CGetRequest('/fulfillmentnodesbymerchantid/',$merchant_id);
            $resFulfillment =json_decode($resFulfill,true);
            if(is_array($resFulfillment) && count($resFulfillment)>0)
            {                
                $fulfillmentArray = [];
                foreach($resFulfillment as $value)
                {
                    if(isset($value['FulfillmentNodeId']) && $value['FulfillmentNodeId']==$fullfillmentnodeid){
                        $fulfillmentArray=$value;
                        break;
                    }
                }
                if(isset($fulfillmentArray['SupportedShipMethods']))
                {
                    foreach($fulfillmentArray['SupportedShipMethods'] as $val){
                        $carriers[]=$val['Name'];
                    }
                }
            }
            $carriers[]="Other";
        }
        $html=$this->render('shipmentpartial',array('merchant_order_id'=>$merchant_order_id,'carriers'=>$carriers),true);
        return $html;
    }
    public function actionPartialshipment()
    {          
        date_default_timezone_set('Asia/Kolkata');
        $merchant_id=MERCHANT_ID;
        $merchant_sku=trim($_POST['merchant_sku']);
        $merchant_order_id=trim($_POST['merchant_order_id']);        
        $shipment_tracking_number =trim($_POST['shipment_tracking_number']);
        $carrier =trim($_POST['carrier']);                 
        $response_shipment_sku_quantity =trim($_POST['response_shipment_sku_quantity']);
        
        $offset_end = $this->getStandardOffsetUTC(); 
        if(empty($offset_end) || trim($offset_end)=='')
            $offset = '.0000000-00:00';
        else
            $offset = '.0000000'.trim($offset_end);
        $expected_delivery_date = date("Y-m-d\TH:i:s").$offset;
       
        $model = [];
        $model = Data::sqlRecords("SELECT `order_data` FROM `jet_order_detail` where `merchant_id`='".$merchant_id."' AND merchant_order_id='".$merchant_order_id."'","one","select");
        if(!empty($model))
        {
            $arraydata = array();
            $arraydata = json_decode($model['order_data'],true);
            $ship_from_zip_code = $arraydata['shipping_to']['address']['zip_code'];
            $shipment_items[] = array(
                "alt_shipment_item_id"=> "".time(),
                "merchant_sku"=> $merchant_sku,
                "response_shipment_sku_quantity"=>(int)$response_shipment_sku_quantity
            );

            $shipment_arr[] = array(
                "alt_shipment_id" => "".time(), 
                "shipment_tracking_number"=>(string)$shipment_tracking_number,
                "response_shipment_date"=> $expected_delivery_date,
                "response_shipment_method"=> $carrier,
                "expected_delivery_date"=> $expected_delivery_date,
                "ship_from_zip_code"=> (string)$ship_from_zip_code,
                "carrier_pick_up_date"=> $expected_delivery_date,
                "carrier"=> $carrier,
                "shipment_items" => $shipment_items
            );
             
            $array = array(
                "alt_order_id"=>"".time(),
                "shipments"=>$shipment_arr,
            );
                                
            if(API_USER)
            {                
                $responseToken ="";
                $responseToken = $this->jetHelper->JrequestTokenCurl();
                if($responseToken==false){
                    return "There is some issue with Jet API";
                }
                $result = $status = "";
                $response = [];
                $result=$this->jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($array),$merchant_id,$status);
                $response=json_decode($result,true);               
                if($status==204)
                {
                    $newresponse = $responseOrders = [];
                    $newresponse = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchant_order_id,$merchant_id);         
                    $responseOrders = json_decode($newresponse,true);

                    if (!empty($responseOrders) && isset($responseOrders['status'])) 
                    {
                        $query = "UPDATE `jet_order_detail` SET  `status`='".$responseOrders['status']."',`shipped_at`='".date('Y-m-d H:i:s')."'  where merchant_id='".$merchant_id."' and merchant_order_id='".$merchant_order_id."' ";
                        Data::sqlRecords($query , null,"update");                                                                  
                    }                    
                    return "Order Shipped Successfully";
                }else{
                    return $response['errors'][0];
                }        
            }
        }        
    }
    
    public function actionBulk()
    {
        $session = Yii::$app->session;        
        $selection = [];
        $merchant_id=Yii::$app->user->identity->id;
        $selection = (array)Yii::$app->request->post('selection');
        $action=Yii::$app->request->post('action');
        if (count($selection)<1) 
        {
            Yii::$app->session->setFlash('error', "Please Select Atleast one Order");
            return $this->redirect(['index']); 
        } 
        if($action=='exportcsv')
        {
            $dir= Yii::getAlias('@webroot').'/var/jet/order/csv/'.$merchant_id;
            if (!file_exists($dir)){
                mkdir($dir,0775, true);
            }
            $base_path=$dir."/".time().'.csv';
            $file = fopen($base_path,"w");
            $headers = array('Jet Reference ID','Merchant Order ID','Order Item ID','Jet Merchant Order ID','Merchant Sku','Total Price','Created At','Deliver By','Status');
            $row = $value = [];
             
            foreach($headers as $header) {
                $row[] = $header;
            }
            fputcsv($file,$row);

            $csvdata=array();
            $i=0;
            foreach ($selection as $merchant_order_id)
            {
                $OrderData=array();
                $OrderData=Data::sqlRecords("select `reference_order_id`,`order_item_id` ,`merchant_order_id`,`merchant_sku`,`order_data`,`status`,`created_at`,`deliver_by`   from  `jet_order_detail` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$merchant_order_id."' ",'one','select');

                $total=json_decode($OrderData['order_data'],true);

                $totalPrice=0;
                if (isset($total['order_totals']['item_price']['item_shipping_cost'])) {
                    $totalPrice +=$total['order_totals']['item_price']['item_shipping_cost'];
                }
                if (isset($total['order_totals']['item_price']['base_price'])) {
                    $totalPrice +=$total['order_totals']['item_price']['base_price'];
                }
                
                $csvdata[$i]['Jet Reference ID']=$OrderData['reference_order_id'];
                $csvdata[$i]['Merchant Order ID']=$OrderData['merchant_order_id'];
                $csvdata[$i]['Order Item ID']=$OrderData['order_item_id'];
                $csvdata[$i]['Jet Merchant Order ID']=$OrderData['merchant_order_id'];
                $csvdata[$i]['Merchant Sku']=$OrderData['merchant_sku'];
                $csvdata[$i]['Total Price']=$totalPrice;
                $csvdata[$i]['Created At']=$OrderData['created_at'];
                $csvdata[$i]['Deliver By']=$OrderData['deliver_by'];
                $csvdata[$i]['Status']=$OrderData['status'];
                $i++;        
            }
       
            foreach($csvdata as $v)
            {    
                $row = array();
                $row[] =$v['Jet Reference ID'];
                $row[] =$v['Merchant Order ID'];
                $row[] =$v['Order Item ID'];
                $row[] =$v['Jet Merchant Order ID'];
                $row[] =$v['Merchant Sku'];
                $row[] =$v['Total Price'];
                $row[] =$v['Created At'];
                $row[] =$v['Deliver By'];
                $row[] =$v['Status'];
                        
                fputcsv($file,$row);
            }
            fclose($file);
            $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
            $content = $encode . file_get_contents($base_path);
            return  Yii::$app->response->sendFile($base_path);  
        }
    }    
    
    public function getStandardOffsetUTC()
    {
        $timezone = date_default_timezone_get();
        if($timezone == 'UTC') {
            return '';
        } 
        else 
        {
            $timezone = new \DateTimeZone($timezone);
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);
    
            foreach (array_reverse($transitions, true) as $transition)
            {
                if ($transition['isdst'] == 1)
                {
                    continue;
                }
                return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
            }
    
            return false;
        }
    }  

    public function actionVieworderdetails()
    {
        $this->layout="main2";
        if(Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $response = $responseOrders = array();
        $merchantOrderid = isset($_POST['merchant_order_id'])?$_POST['merchant_order_id'] : "";
        
        if(API_USER)
        {            
            $responseToken = "";
            $responseToken = $this->jetHelper->JrequestTokenCurl();
        
            if ($responseToken == false) {
                Yii::$app->session->setFlash('error',"Jet API is not running Properly, Please try later");
                return $this->redirect(['index']);
            }
            $response = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchantOrderid,MERCHANT_ID);         
            $responseOrders = json_decode($response,true);
           
            $html=$this->render('vieworderdetail', [
                    'model' => $responseOrders,
            ],true);
            return $html;
        }                
    }
    public function actionViewShopifyShipment()
    {
        $query = "select `jet_order_detail`.`id`,`jet_order_detail`.`merchant_id`,`reference_order_id`,`merchant_order_id`,shopify_order_id,username,auth_key,api_user,api_password,fullfilment_node_id from `jet_order_detail` inner join `user` on jet_order_detail.merchant_id=user.id inner join `jet_configuration` on jet_configuration.merchant_id=user.id where jet_order_detail.status='acknowledged' and shopify_order_id!='' and jet_order_detail.merchant_id!=14";
        $orderAckCollection = Data::sqlRecords($query, "all", "select");
        $count=0;
        if (!empty($orderAckCollection) && is_array($orderAckCollection)) 
        {
            foreach ($orderAckCollection as $key => $value) 
            {               
                $shopname = $value['username'];
                $token = $value['auth_key'];
                $shopify_order_id = $value['shopify_order_id'];
                
                $shipmentResponse = $this->sc->call('GET', '/admin/orders/' . $shopify_order_id . '.json?fields=fulfillments');
                //print_r($shipmentResponse);die;
                if(is_array($shipmentResponse) && isset($shipmentResponse['fulfillments']) && count($shipmentResponse['fulfillments'])>0) 
                {   
                    echo 'merchant_id: '.$value['merchant_id']."  =>  Reference Order ID:".$value['reference_order_id']."<br>";
                    $count++;
                }  
            }                           
        }
        echo "Total Orders not shipped on jet :: ".$count;                                  
    } 
    public function actionAddfulfillment()
    {
        $shopifyShip['order']=[
                'id'=>4817172172,
                'line_items'=>[
                [
                    'id'=>9688482188,
                    "fulfillment_status"=>"pending",
                    "fulfillment_service"=>"amazon",
                ]
            ],
        ];
        echo json_encode($shopifyShip);die;
        $shipmentResponse = $this->sc->call('POST', '/admin/orders/4817172172.json',$shopifyShip);
    }
}