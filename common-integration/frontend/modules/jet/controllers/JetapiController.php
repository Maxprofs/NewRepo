<?php
namespace frontend\modules\jet\controllers;

use Yii;
use common\models\User;

use frontend\modules\jet\models\JetConfiguration;
use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\models\JetFileinfo;

use frontend\modules\jet\models\JetConfig;
use frontend\modules\jet\components\Jetapi;
use frontend\modules\jet\components\Jetappdetails;

class JetapiController extends JetmainController
{
	/* public function beforeAction($action)
    {
    	if(Jetappdetails::isValidateapp()=="expire"){
    		//var_dump(Yii::$app->session);die;
    		Yii::$app->session->setFlash('error', "We would like to inform you that your app subscription has been expired. Please renew the subscription to use the app services. You can renew services by using following <a href=http://cedcommerce.com/shopify-extensions/jet-shopify-integration target=_blank>link</a>");
    		$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/site/index',302);
    		return false;
    	}elseif(Jetappdetails::isValidateapp()=="not purchase"){
    		$msg='We would like to inform you that your app trial period has been expired,If you purchase app then create license from your customer account page from cedcommerce Or <br>Purchase jet-shopify app from <a href=http://cedcommerce.com/shopify-extensions/jet-shopify-integration target=_blank>CedCommerce</a> and can review on <a href=http://shopify.cedcommerce.com/frontend/site/pricing target=_blank>pricing page</a>';
    		Yii::$app->session->setFlash('error', ''.$msg.'' );
    		$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/site/index',302);
    		return false;
    	}else
    		return true;
    } */
    public function actionIndex()
    {
        if(Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }else
        return $this->render('index');
    }
    public function actionProductapi()
    {
        if(Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = \Yii::$app->user->identity->id;
        if (!file_exists(\Yii::getAlias('@webroot').'/var/jet/jetupload')) {
            mkdir(\Yii::getAlias('@webroot').'/var/jet/jetupload', 0775, true);
        }
        $model = new JetConfiguration();
        $jetConfig=$model->find()->where(['merchant_id' => $merchant_id])->one();
        if($jetConfig){
            $fullfillmentnodeid=$jetConfig->fullfilment_node_id;
            $api_host=$jetConfig->api_host;
            $api_user=$jetConfig->api_user;
            $api_password=$jetConfig->api_password;
        }else{
            Yii::$app->session->setFlash('error', "please fill the jet configurable before enable all api's");
            return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jetconfiguration/index',302);
        }
       
        $modelProduct = new JetProduct();
        $jetHelper = new Jetapi($api_host,$api_user,$api_password);
        $modelfileInfo=new JetFileinfo();
        $type=Yii::$app->getRequest()->getQueryParam('param'); 
        $resultDes="";
        $customerModel=new JetConfig();
        
        $response = $jetHelper->CGetRequest('/merchant-skus/'.$_GET['sku']);
        print_r($response);die;
        
        
        if($type == 'sku')
        {
        	$productsku=array();
        	$sku='test_product';
        	$id="12345678";
        	$productsku["product_title"]="Cedcommerce Test Product";
        	$productsku["jet_browse_node_id"]=3000001;
        	$productsku["brand"]="Cedcommerce";
        	$upcinfo["standard_product_code"]="719236005030";
        	$upcinfo["standard_product_code_type"]="UPC";
        	$productsku["standard_product_codes"][]=$upcinfo;
        	$productsku["multipack_quantity"]= 1;
        	$description="";
        	$description="Test Product is used to enable product api";
        	$productsku['product_description']=$description;
        	$responseArray="";
        	$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku),json_encode($productsku));
        	$responseArray=json_decode($response,true);
        	if($responseArray==""){
        		$connection = Yii::$app->getDb();
        		$skumodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='sku_data'");
        		$result1 = $skumodel->queryOne();
        		if(!$result1)
        		{
        			$sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","sku_data","completed")';
        			$connection->createCommand($sql)->execute();
        		}
        		Yii::$app->session->setFlash('success', "Product Sku api is enabled successfully");
        	}
        	return $this->redirect(['index']);
        }
        elseif($type=='price')
        {
            $price=array();
            $sku='test_product';
        	$id="12345678";
            $price['price']=38.00;
            $priceinfo['fulfillment_node_id']=$fullfillmentnodeid;
            $priceinfo['fulfillment_node_price']=38.00;
            $price['fulfillment_nodes'][]=$priceinfo;
            $responsePrice="";
            $responsePrice = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($price));
            $responsePrice=json_decode($responsePrice,true);
            if($responsePrice==""){
            	$connection = Yii::$app->getDb();
            	$pricemodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='price_data'");
            	$result2 = $pricemodel->queryOne();
            	if(!$result2)
            	{
            		$sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","price_data","completed")';
            		$connection->createCommand($sql)->execute();
            	}
            	Yii::$app->session->setFlash('success', "Product Price api is enabled successfully");
            }
            return $this->redirect(['index']); 
        }
        elseif($type=='inventory')
        {
            $inv=array();
            $inventory=array();
            $id="12345678";
            $sku="test_product";
            $inv['fulfillment_node_id']=$fullfillmentnodeid;
            $qty=20;
            $inv['quantity']=$qty;
            $inventory['fulfillment_nodes'][]=$inv;
            $responseInventory="";
            $response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory));
            $responseInventory = json_decode($response,true);
            //var_Dump($responseInventory);die;
            if($responseInventory==""){
            	$connection = Yii::$app->getDb();
            	$inventorymodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='inventory_data'");
            	$result3 = $inventorymodel->queryOne();
            	if(!$result3)
            	{
            		$sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","inventory_data","completed")';
            		$connection->createCommand($sql)->execute();
            	}
            	Yii::$app->session->setFlash('success', "Product Inventory api is enabled successfully");
            }
            return $this->redirect(['index']); 
        }
    }
    public function actionOrderapi()
    {
        $merchant_id = \Yii::$app->user->identity->id;
        $model = new JetConfiguration();
        $jetConfig=$model->find()->where(['merchant_id' => $merchant_id])->one();
        if($jetConfig){
            $fullfillmentnodeid=$jetConfig->fullfilment_node_id;
            $api_host=$jetConfig->api_host;
            $api_user=$jetConfig->api_user;
            $api_password=$jetConfig->api_password;
        }else{
            Yii::$app->session->setFlash('error', "please fill the jet configurable before enable all api's");
            return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jetconfiguration/index',302);
        }
        $jetHelper = new Jetapi($api_host,$api_user,$api_password);
        $type=Yii::$app->getRequest()->getQueryParam('param');
        if($type=='ack')
        {
            $response=$jetHelper->CGetRequest('/orders/ready');
            $response  = json_decode($response);
            if(!$response->order_urls)
            {
                Yii::$app->session->setFlash('error', "No order available in ready state.Kindly create new order in jet panel");
                return $this->redirect(['index']); 
            }
            $response=$response->order_urls;
            $testurl=end($response);
            $testurlarray=explode("/",$testurl);
            $order_id=$testurlarray[3];
            $result = $jetHelper->CGetRequest($testurl);
            $result=json_decode($result,true);
            if($result)
            {
	            $order_id=$result['merchant_order_id'];
	            $order_arr = array();
	            $order_arr['acknowledgement_status'] = "accepted";
	            foreach ($result['order_items'] as $value) 
	            {
	               $order_arr['order_items'][] = array('order_item_acknowledgement_status'=>'fulfillable',
	                                                'order_item_id' =>$value['order_item_id']);  
	            }
	           
	            $orderdata=$jetHelper->CPutRequest('/orders/'.$order_id.'/acknowledge',json_encode($order_arr));
	            if($orderdata==""){
	                $connection = Yii::$app->getDb();
	                $orderModel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='order_data'");
	                $result4 = $orderModel->queryOne();
	                
	                if(empty($result4))
	                {
	                	
	                	$sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","order_data","'.$order_id.'")';
	                	$connection->createCommand($sql)->execute();
	                	
	                }	
	               
	                Yii::$app->session->setFlash('success', "Order Acknowledgement api is enabled successfully");
	            }
            }else{
            	Yii::$app->session->setFlash('error', "No order available in ready state");
            }
            return $this->redirect(['index']);
        }
        elseif($type=='ship')
        {
            $connection = Yii::$app->getDb();
            $ordermodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='order_data'");
            $result = $ordermodel->queryOne();
            if(!$result){
                Yii::$app->session->setFlash('error', "No order id available.Kindly enable order api before shipment");
                return $this->redirect(['index']); 
            }
            //var_dump($result);die;
            $order_id =$result['value'];
            $resultdata = $jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$order_id.'');
            $result=json_decode($resultdata);
            $sku=$result->order_items[0]->merchant_sku;
            $carrier=$result->order_detail->request_shipping_carrier;
            $qty=$result->order_items[0]->request_order_quantity;
            $cancel=$result->order_items[0]->request_order_cancel_qty;
            $deliver= $result->order_detail->request_delivery_by;
            if($deliver)
            {
                $time=explode('.', $deliver);
                $deliver=$time[0].'.0000000-07:00';
            } 
            $t=time();
            $data_ship=array();
            $data_ship['shipments'][]=array ('shipment_tracking_number'=>"$t",
                                            'response_shipment_date'=>$deliver,
                                            'response_shipment_method'=>'',
                                            'expected_delivery_date'=>$deliver,
                                            'ship_from_zip_code'=>'84047',
                                            'carrier_pick_up_date'=>$deliver,
                                            'carrier'=>$carrier,
                                            'shipment_items'=>array(
                                                                array('shipment_item_id'=>'id'.$t.'',
                                                                         'merchant_sku'=>$sku,
                                                                         'response_shipment_sku_quantity'=>$qty,
                                                                         'response_shipment_cancel_qty'=>$cancel,
                                                                         'RMA_number'=>'',
                                                                         'days_to_return'=>30,
                                                                         'return_location'=>array('address1'=>'6909 South State Street',
                                                                                                    'address2'=>'Suite C',
                                                                                                    'city'=>'Midvale',
                                                                                                    'state'=>'UT','zip_code'=>'84047'
                                                                                                )
                                                                    )
                                                                )
                                            );
            
            $data=$jetHelper->CPutRequest('/orders/'.$order_id.'/shipped',json_encode($data_ship));
            if($data==""){
                $connection = Yii::$app->getDb();
                $shipmodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='shipped_data'");
                $result7 = $shipmodel->queryOne();
                if(!$result7)
                {
                	$sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","shipped_data","completed")';
                	$connection->createCommand($sql)->execute();
                }
                Yii::$app->session->setFlash('success', "Shipment api is enabled successfully");
            }else{
            	Yii::$app->session->setFlash('error', "Shipment api not enabled successfully");
            }
            return $this->redirect(['index']);
        }
        elseif($type=='cancel')
        {
            $response=$jetHelper->CGetRequest('/orders/directedCancel');
            $response  = json_decode($response);
            $responsedata=$response->order_urls;
            if(!$responsedata){
                Yii::$app->session->setFlash('error', "Kindly cancel order from jet panel to enable order cancel api");
                return $this->redirect(['index']); 
            }
            $testurl= end($responsedata);
            $testurlarray=explode("/",$testurl);
            $order_id=$testurlarray[3];
            $result = $jetHelper->CGetRequest($testurl);
            $result=json_decode($result);
            $sku=$result->order_items[0]->merchant_sku;
            $carrier=$result->order_detail->request_shipping_carrier;
            $qty=$result->order_items[0]->request_order_quantity;
            $cancel=$result->order_items[0]->request_order_cancel_qty;
            $deliver= $result->order_detail->request_delivery_by;
            if($deliver){
                $time=explode('.', $deliver);
                $deliver=$time[0].'.0000000-07:00';
            }
            $t=time();
            $data_ship=array();

            $data_ship['shipments'][]=array ('shipment_tracking_number'=>"$t",
                                 'response_shipment_date'=>$deliver,
                                 'response_shipment_method'=>'',
                                 'expected_delivery_date'=>$deliver,
                                 'ship_from_zip_code'=>'84047',
                                 'carrier_pick_up_date'=>$deliver,
                                 'carrier'=>$carrier,
                                 'shipment_items'=>array(
                                                        array('shipment_item_id'=>'id'.$t.'',
                                                              'merchant_sku'=>$sku,
                                                              'response_shipment_sku_quantity'=>$qty,
                                                              'response_shipment_cancel_qty'=>$cancel,
                                                              'RMA_number'=>'',
                                                              'days_to_return'=>30,
                                                              'return_location'=>array('address1'=>'6909 South State Street','address2'=>'Suite C',
                                                                                        'city'=>'Midvale','state'=>'UT',
                                                                                        'zip_code'=>'84047'
                                                                                       )
                                                               )
                                                        )
                                ); 
            $data=$jetHelper->CPutRequest('/orders/'.$order_id.'/shipped',json_encode($data_ship));
            if($data==""){
                $connection = Yii::$app->getDb();
                $cancelmodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='ordercancel_data'");
                $result5 = $cancelmodel->queryOne();
                if(!$result5)
                {
	                $sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","ordercancel_data","completed")';
	                $connection->createCommand($sql)->execute();
                }
                Yii::$app->session->setFlash('success', "Order cancel api is enabled successfully");
            }else{
            	Yii::$app->session->setFlash('error', "Order cancel api not enabled successfully");
            }
            return $this->redirect(['index']);
        }
    }
    public function actionReturnapi()
    {
        $merchant_id = \Yii::$app->user->identity->id;
        $model = new JetConfiguration();
        $jetConfig=$model->find()->where(['merchant_id' => $merchant_id])->one();
        if($jetConfig){
            $fullfillmentnodeid=$jetConfig->fullfilment_node_id;
            $api_host=$jetConfig->api_host;
            $api_user=$jetConfig->api_user;
            $api_password=$jetConfig->api_password;
        }else{
            Yii::$app->session->setFlash('error', "please fill the jet configurable before enable all api's");
            return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jetconfiguration/index',302);
        }
        $jetHelper = new Jetapi($api_host,$api_user,$api_password);

        $response=$jetHelper->CGetRequest('/returns/created');
        $response  = json_decode($response);
        $response=$response->return_urls;
        if(!$response){
            Yii::$app->session->setFlash('error', "Kindly create return order from jet panel to enable return order api");
            return $this->redirect(['index']); 
        }
        $testurl=end($response);
        $testurlarray=explode("/",$testurl);
        $returnid=$testurlarray[3];
        $result=$jetHelper->CGetRequest($testurl);
        $result=json_decode($result);
        $order_id=$result->merchant_order_id;
        $item_id=$result->return_merchant_SKUs[0]->order_item_id;
        $return=$result->return_merchant_SKUs[0]->return_quantity;
        $refund=$result->return_merchant_SKUs[0]->return_quantity;
        $feedback="item damaged";
        $status=true;
        $shipping=(int)$result->return_merchant_SKUs[0]->requested_refund_amount->shipping_cost;
        $s_tax=0;
        $tax=0;
        $principal=(int)$result->return_merchant_SKUs[0]->requested_refund_amount->principal;
        $data_ship=array();
        $data_ship['merchant_order_id']=$order_id;
        $data_ship['items'][]=array(
                            'order_item_id'=>$item_id,
                            'total_quantity_returned'=>$return,
                            'order_return_refund_qty'=>$refund,
                            'return_refund_feedback'=>$feedback,
                            'refund_amount'=>array(
                                            'principal'=>$principal,
                                            'tax'=>$tax,
                                            'shipping_cost'=>$shipping,
                                            'shipping_tax'=>$s_tax
                                            )
                                   );
        $data_ship['agree_to_return_charge']=$status;
        $data=$jetHelper->CPutRequest('/returns/'.$returnid.'/complete',json_encode($data_ship));
        if($data==""){
            $connection = Yii::$app->getDb();
            $returnmodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."' AND data='return_data'");
            $result6 = $returnmodel->queryOne();
            if(!$result6)
            {
	            $sql='INSERT INTO `jet_config`(`merchant_id`,`data`,`value`) VALUES("'.$merchant_id.'","return_data","completed")';
	            $connection->createCommand($sql)->execute();
            }    
            Yii::$app->session->setFlash('success', "Order Return api is enabled successfully");
        }else{
        	Yii::$app->session->setFlash('error', "Order Return api not enabled successfully");
        }
        return $this->redirect(['index']);
    }


}
