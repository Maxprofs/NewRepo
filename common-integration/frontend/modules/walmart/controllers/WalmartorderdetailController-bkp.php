<?php
namespace frontend\modules\walmart\controllers;

use common\models\User;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Mail;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\models\WalmartOrderDetail;
use frontend\modules\walmart\models\WalmartOrderDetailSearch;
use frontend\modules\walmart\models\WalmartOrderImportError;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;


/**
 * WalmartorderdetailController implements the CRUD actions for WalmartOrderDetail model.
 */
class WalmartorderdetailController extends WalmartmainController
{
    protected $connection;
    protected $walmartHelper;

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
     * function for getting walmart api connection
     */
    public function beforeAction($action){
    

        if(parent::beforeAction($action)){
            $this->walmartHelper = new Walmartapi(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
            return true;
        }
    }

    /**
     * Lists all WalmartOrderDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $searchModel = new WalmartOrderDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $connection = Yii::$app->getDb();
        $merchant_id=Yii::$app->user->identity->id;
        $resultdata=array();
        $queryObj="";
        $query="SELECT `shopify_order_id` FROM `walmart_order_details` WHERE merchant_id='".$merchant_id."' AND (status='acknowledged' OR status='Partially Acknowledged') AND shopify_order_id=''";
        $queryObj = $connection->createCommand($query);
        $resultdata = $queryObj->queryAll();
        $countOrders=0;
        $countOrders=count($resultdata);
        unset($resultdata);
        return $this->render('index', [
            'countOrders'=>$countOrders,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * render shipment item refund form
     * @return mixed
     */
    public function actionRefundData()
    {
        $this->layout = 'main2';
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $id=trim(Yii::$app->request->post('id'));
        $connection = Yii::$app->getDb();
        $query="SELECT * FROM `walmart_order_details` WHERE id='".$id."'";
        $queryObj = $connection->createCommand($query);

        $orderData = $queryObj->queryOne();

        return $this->render('refunddata', [
            'orderData' => $orderData,
        ],true);
    }

    public function actionCancelOrder($config=false){

        $merchant_id = $config ? $config['merchant_id']:Yii::$app->user->identity->id;
        if (!$config && Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        if($config){
            $this->walmartHelper = new Walmartapi($config['consumer_id'],$config['secret_key'],$config['consumer_channel_type_id']);
        }
        $data = Yii::$app->request->queryParams;
        if(isset($data['pid'])){
            
            $lineNumbers = [];
            $connection = Yii::$app->getDb();
            $query="SELECT * FROM `walmart_order_details` WHERE purchase_order_id='".$data['pid']."'";
            $order = $connection->createCommand($query)->queryOne();
            if($merchant_id==$order['merchant_id']){
                $orderData = json_decode($order['order_data'],true);
                //print_r($orderData);
                if(isset($orderData['orderLines']['orderLine']))
                {
                    $items = isset($orderData['orderLines']['orderLine'][0])?$orderData['orderLines']['orderLine']:[$orderData['orderLines']['orderLine']];
                    foreach($items as $item){
                        if (isset($item['lineNumber'])) {
                           $lineNumbers[]=$item['lineNumber'];
                        }
                        elseif(isset($item[0]['lineNumber'])){
                            $lineNumbers[]=$item[0]['lineNumber'];
                        }
                    }
                }
                $dataShip = ['shipments'=>[['cancel_items'=>[['lineNumber'=>implode(',',$lineNumbers)]]]]];
                $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$data['pid'].'/';
                if (!file_exists($directory)){
                    mkdir($directory,0775, true);
                }
                $handle = fopen($directory.'/cancel.log','a');
                fwrite($handle,'Cancel SHIP DATA : '.print_r($dataShip,true).PHP_EOL.PHP_EOL);
                $response = $this->walmartHelper->rejectOrder($data['pid'],$dataShip);
                if(isset($response['errors'])){
                    if(isset($response['errors']['error']))
                        Yii::$app->session->setFlash('error', $response['errors']['error']['description']);
                    else
                        Yii::$app->session->setFlash('error', 'Order Can\'t be cancelled.');
                }
                else
                {
                    $query="UPDATE `walmart_order_details` SET status='canceled' WHERE purchase_order_id='".$data['pid']."'";
                    $order = $connection->createCommand($query)->execute();
                    Yii::$app->session->setFlash('success', 'Order has been cancelled.');
                }
                //var_dump($response);
                fwrite($handle,'RESPONSE:'.print_r($response,true));
                fclose($handle);
                return $this->redirect(['index']);
                //die;
            }else
            {
                Yii::$app->session->setFlash('error', 'You are not authorized to cancel this order.');
                die('You are not authorized to cancel this order');
            }
        }
    }

    /**
     * refund items
     * @return mixed
     */
    /*public function actionRefundItems()
    {
        $merchant_id= Yii::$app->user->identity->id;
        $data = Yii::$app->request->post();
        //print_r($data);die;
        $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$data['purchaseOrderId'];
        if (!file_exists($directory)){
            mkdir($directory,0775, true);
        }
        $handle=fopen($directory.'/refund.log','a');
        fwrite($handle,'Requested refund Data : '.PHP_EOL.json_encode($data).PHP_EOL.PHP_EOL);
        $connection = Yii::$app->getDb();
        foreach($data['lineNumber'] as $number => $item){
            $orderData = [];
            //print_r($item);die;
            $orderData['lineNumber'] = $number;
            $orderData['refundComments'] = $data['refundComments'];
            $orderData['refundReason'] = $data['refundReason'];
            if(isset($item['ItemPrice'])){

                $orderData['amount'] = $item['ItemPrice']['amount'];
                $orderData['taxAmount'] = isset($item['ItemPrice']['tax'])?$item['ItemPrice']['tax']:0;
            }
            else
            {
                $orderData['amount'] = 0;
                $orderData['taxAmount'] = 0;
            }
            
            if(isset($item['Shipping'])){
                $orderData['shipping'] = $item['Shipping']['amount'];
                $orderData['shippingTax'] = isset($item['Shipping']['tax'])?$item['ItemPrice']['tax']:0;
            }
            else
            {
                $orderData['shipping'] = 0;
                $orderData['shippingTax'] = 0;
            }
            $orderData['refunReasonShipping'] = $data['refundComments'];
            //print_r($orderData);die();
            fwrite($handle,'Prepared refund Data : '.PHP_EOL.json_encode($orderData).PHP_EOL.PHP_EOL);
            $result = $this->walmartHelper->refundOrder($data['purchaseOrderId'],$orderData);
            //print_r($result);die();
            fwrite($handle,'refundResponse from walmart : '.PHP_EOL.json_encode($result).PHP_EOL.PHP_EOL);
            //echo $result.'<hr>';
           
            if(isset($result['ns4:errors'])){
                fwrite($handle,'Prepared xml Data : '.PHP_EOL.$this->walmartHelper->requestedXml.PHP_EOL.PHP_EOL);
                if(isset($result['ns4:errors']['ns4:error']))
                    Yii::$app->session->setFlash('error', $result['ns4:errors']['ns4:error']['ns4:description']);
            }
            else
            {
                $query="UPDATE `walmart_order_details` SET status='refunded' WHERE purchase_order_id='".$data['purchaseOrderId']."'";
                $order = $connection->createCommand($query)->execute();
                Yii::$app->session->setFlash('success', 'Order has been refunded.');
            }
            //var_dump($result);die;
             
            
        }

        return $this->redirect(['index']);
    }*/

    public function actionRefundItems()
    {
        $merchant_id= Yii::$app->user->identity->id;
        $data = Yii::$app->request->post();

        //print_r($data);die;
        $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$data['purchaseOrderId'];
        if (!file_exists($directory)){
            mkdir($directory,0775, true);
        }

        $handle = fopen($directory.'/refund.log','a');
        fwrite($handle,'Requested refund Data : '.PHP_EOL.json_encode($data).PHP_EOL.PHP_EOL);

        $connection = Yii::$app->getDb();
        if(isset($data['selectedlineNumber']) && count($data['selectedlineNumber']))
        {
            foreach($data['lineNumber'] as $number => $item)
            {
                if(!in_array($number, $data['selectedlineNumber']))
                    continue;

                $orderData = [];
                $orderData['lineNumber'] = $number;
                $orderData['refundComments'] = empty($data['refundComments'])?'Refund this Order.':$data['refundComments'];
                $orderData['refundReason'] = $data['refundReason'];
                $orderData['charges'] = $item;

                if(is_array($data['includeShipping']) && in_array($number, $data['includeShipping']))
                    $orderData['includeShipping'] = 1;
                else
                    $orderData['includeShipping'] = 0;
                
                fwrite($handle,'Prepared refund Data : '.PHP_EOL.json_encode($orderData).PHP_EOL.PHP_EOL);
                $result = $this->walmartHelper->refundOrder($data['purchaseOrderId'],$orderData);
                fwrite($handle,'refundResponse from walmart : '.PHP_EOL.json_encode($result).PHP_EOL.PHP_EOL);
               
                if(isset($result['ns4:errors'])){
                    fwrite($handle,'Prepared xml Data : '.PHP_EOL.$this->walmartHelper->requestedXml.PHP_EOL.PHP_EOL);
                    if(isset($result['ns4:errors']['ns4:error']))
                        Yii::$app->session->setFlash('error', $result['ns4:errors']['ns4:error']['ns4:description']);
                }
                else
                {
                    $query="UPDATE `walmart_order_details` SET status='refunded' WHERE purchase_order_id='".$data['purchaseOrderId']."'";
                    $order = $connection->createCommand($query)->execute();
                    Yii::$app->session->setFlash('success', 'Order has been refunded.');
                }
            }
        }
        else
        {
            Yii::$app->session->setFlash('error', 'No LineItems Selected for Refund.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Fetch orders from walmart
     * @return mixed
     */
    public function actionCreate($config = false,$test = false)
    {
        $merchant_id = $config ? $config['merchant_id']:Yii::$app->user->identity->id;
        if (!$config && Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $connection = Yii::$app->getDb();
        $query="";
        $model="";
        $queryObj="";
        $count = 0;
        $isError=false;
        $countOrder=0;
        $allProductError = [];
        
        $fieldname = 'ordersync';
        $value = Data::getConfigValue($merchant_id,$fieldname);
        if($value == 'no')
        {
            if(!$config)
            {
                Yii::$app->session->setFlash('error',"Your Order Fetching / Syncing is disabled from configuration setting . <a href=".Yii::$app->request->getBaseUrl()."/walmart/walmartconfiguration/index>Click Here</a> If you want to sync order in your shopify store then set 'Yes' to 'sync_order' in Configuration");
                return $this->redirect(['index']);

            }
            return;
        }

        if(defined('API_USER')||$config)//foreach($model as $k=>$jetConfig)
        {
            try
            {
                $orderdata="";
                $response=array();
                $prev_date = date('Y-m-d', strtotime(date('Y-m-d') .' -2 month'));
                if($config){
                    $this->walmartHelper = new Walmartapi($config['consumer_id'],$config['secret_key']);
                }

                $orderdata = $this->walmartHelper->getOrders(['status'=>'Created','limit' => '100', 'createdStartDate'=>$prev_date],Walmartapi::GET_ORDERS_SUB_URL,$test);
                if($orderdata==false)
                {
                    if($config){
                        echo "No Order in Ready State";
                        return;
                    }else
                    {
                        Yii::$app->session->setFlash('error', "No Order in Ready State");
                        return $this->redirect(['index']);
                    }
                }
                
                $response  = $orderdata;
                if(isset($response['errors']['error']['info'])){
                    if(isset($response['errors']['error']['info']) && strpos($response['errors']['error']['info'], 'Requested content could not be found.'
                        )!==false){

                        $isError='No Orders found in created state.';
                    }
                    else
                        $isError=json_encode($response['errors']);
                }
                else if(isset($response['errors']['error'][0]['info'])){
                    if(isset($response['errors']['error'][0]['info']) && strpos($response['errors']['error'][0]['info'], 'Requested content could not be found'
                        )!==false){

                        $isError='No Order found in created state.';
                    }
                    else
                        $isError=json_encode($response['errors']);
                }
                $orders = isset($response['elements']['order'])?$response['elements']['order']:array();
                if(count($orders) > 0)
                {

                    $message="";
                    $error_array=[];
                    foreach($orders as $order)
                    {
                        $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$order['purchaseOrderId'];
                        if (!file_exists($directory)){
                            mkdir($directory,0775, true);
                        }
                        $handle=fopen($directory.'/fetch.log','a');
                        fwrite($handle,'Requested Order Data From Walmart : '.PHP_EOL.json_encode($order).PHP_EOL.PHP_EOL);
                        //send acknowledge request if auto-acknowledge order
                        $order_ack=array();
                        $order_ack['acknowledgement_status'] = "accepted";
                        $merchantOrderid="";
                        $purchaseOrderId = $order['purchaseOrderId'];
                        $resultdata="";
                        $skus = array();
                        $queryObj="";
                        $query="SELECT `purchase_order_id` FROM `walmart_order_details` WHERE merchant_id='".$merchant_id."' AND purchase_order_id='".$purchaseOrderId."'";
                        $queryObj = $connection->createCommand($query);

                        $resultdata = $queryObj->queryOne();
                        if(!$resultdata)
                        {
                            $OrderItemData=array();
                            $autoReject = false;
                            $i=0;
                            $ikey=0;
                            $check_key = [];
                            foreach ($this->getItems($order['orderLines']['orderLine']) as $key=>$value)
                            {
                                $collection="";
                                $queryObj="";
                                $query="SELECT sku,qty FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND sku='".$value['item']['sku']."'";
                                $queryObj = $connection->createCommand($query);
                                $collection = $queryObj->queryOne();
                                
                                if($collection=="")
                                {
                                    $collectionOption="";
                                    $queryObj = "";
                                    $query="SELECT option_sku,option_qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND option_sku='".$value['item']['sku']."'";
                                    $queryObj = $connection->createCommand($query);
                                    $collectionOption = $queryObj->queryOne();
                                    
                                    if($collectionOption=="")
                                    {
                                        $error_array[]=array(
                                                'purchase_order_id'=>$order['purchaseOrderId'],
                                                'reference_order_id'=>$order['purchaseOrderId'],
                                                'lineNumber'=>$value['lineNumber'],
                                                'merchant_id'=>$merchant_id,
                                                'reason'=>'Order Rejetcted-Product sku: '.$value['item']['sku'].' not available in shopify',
                                                'created_at'=>date("d-m-Y H:i:s"),
                                        );
                                        $check_key[]=$key;
                                        $count++;
                                        if($merchant_id!=7 && $merchant_id!=694 )
                                            $autoReject=true;
                                        continue;
                                    }
                                    elseif($collectionOption && $value['qty']>$collectionOption['option_qty'])
                                    {
                                        $autoReject=true;
                                        $count++;
                                        $error_array[]=array(
                                                'purchase_order_id'=>$order['purchaseOrderId'],
                                                'reference_order_id'=>$order['purchaseOrderId'],
                                                'lineNumber'=>$value['lineNumber'],
                                                'merchant_id'=>$merchant_id,
                                                'reason'=>'Order Rejetcted-Requested Order quantity is not available for product Option sku: '.addslashes($value['item']['sku']),
                                                'created_at'=>date("d-m-Y H:i:s"),
                                        );
                                        $check_key[]=$key;
                                        continue;
                                    }
                                }
                                elseif($collection && $value['qty']>$collection['qty'])
                                {
                                    $count++;
                                    $autoReject=true;
                                    $error_array[]=array(
                                            'purchase_order_id'=>$order['purchaseOrderId'],
                                            'reference_order_id'=>$order['purchaseOrderId'],
                                            'lineNumber'=>$value['lineNumber'],
                                            'merchant_id'=>$merchant_id,
                                            'reason'=>'Order Rejetcted-Requested Order quantity is not available for product sku: '.addslashes($value['item']['sku']),
                                            'created_at'=>date("d-m-Y H:i:s"),
                                    );
                                    $check_key[]=$key;
                                    continue;
                                }
                                //send acknowledge request if auto-acknowledge order
                                $OrderItemData['sku'][]=$value['item']['sku'];
                                $skus[] = $value['item']['sku'];
                                $OrderItemData['order_item_id'][]=$value['lineNumber'];
                                $order_ack['order_items'][$value['lineNumber']] = array(
                                        'order_item_acknowledgement_status'=>'fulfillable',
                                        'order_item_id' =>$value['lineNumber']
                                );
                            }
                            $lineNumbers = [];
                            $getConfigValue = Data::getConfigValue($merchant_id,'partialorder');
                            if($autoReject){
                                if($getConfigValue){
                                    if($getConfigValue=='no'){
                                        $message.="Item Level Error\n";
                                        continue;
                                    }
                                    else
                                    {
                                        if(!isset($order_ack['order_items'])){
                                            $allProductError[$order['purchaseOrderId']] = true;
                                            $message.="Item Level Error\n";
                                            continue;
                                        }

                                        if(count($error_array)>0){
                                            foreach ($error_array as $key => $value1) {
                                                $lineNumbers[] = $value['lineNumber'];
                                                unset($order_ack['order_items'][$value1['lineNumber']]);
                                            }
                                        }
                                        if(count($lineNumbers)>0){
                                            $dataShip = ['shipments'=>[['cancel_items'=>[['lineNumber'=>implode(',',$lineNumbers)]]]]];
                                            $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$error_array[0]['purchase_order_id'].'/';
                                            if (!file_exists($directory)){
                                                mkdir($directory,0775, true);
                                            }
                                            $handle = fopen($directory.'/cancel.log','a');
                                            fwrite($handle,'Cancel Order Item : '.print_r($dataShip,true).PHP_EOL.PHP_EOL);
                                            $response = $this->walmartHelper->rejectOrder($error_array[0]['purchase_order_id'],$dataShip);
                                            if(isset($response['errors'])){
                                                if($config){

                                                    }else{
                                                        if(isset($response['errors']['error']))
                                                            Yii::$app->session->setFlash('error', $response['errors']['error']['description']);
                                                        else
                                                            Yii::$app->session->setFlash('error', 'Order Can\'t be cancelled.');
                                                        
                                                    }
                                            }
                                            //var_dump($response);
                                            fwrite($handle,'RESPONSE:'.print_r($response,true));
                                            fclose($handle);
                                            if(count($check_key)>0)
                                            {
                                                foreach ($check_key as $ckey => $cvalue) {
                                                        unset($order['orderLines']['orderLine'][$ckey]);       
                                                }
                                                $order['orderLines']['orderLine'] =array_values($order['orderLines']['orderLine']);
                                            }
                                        }
                                    }  
                                }
                                else{
                                    $message.="Item Level Error\n";
                                    continue;
                                }
                                
                            }

                            /* if ($merchant_id==139){
                                echo "<pre>";
                                print_r($order_ack);
                                die;
                            } */
                            if(isset($order_ack['order_items']) && count($order_ack['order_items'])>0)
                            {
                                $skus = implode(',',$skus);
                                $ackData=array();
                                $ackResponse="";
                                $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$order['purchaseOrderId'];
                                if (!file_exists($directory)){
                                    mkdir($directory,0775, true);
                                }
                                $handle=fopen($directory.'/fetch.log','a');
                                $ackResponse=$test?$order:$this->walmartHelper->acknowledgeOrder($order['purchaseOrderId']);
                                fwrite($handle,'Acknowlegde Response From Walmart : '.PHP_EOL.json_encode($ackResponse).PHP_EOL.PHP_EOL);
                                if(isset($ackResponse['purchaseOrderId'])){
                                    $countOrder++;
                                    if(count($error_array)>0){
                                        $status='Partially Acknowledged';
                                    }else{
                                        $status='acknowledged';
                                    }
                                    $message.="Order created on app and Ack\n";
                                    $queryObj="";

                                    $shippingData = isset($order['orderLines'])?$order['orderLines']:array();

                                    $query='INSERT INTO `walmart_order_details`
                                                (
                                                    `merchant_id`,
                                                    `sku`,
                                                    `purchase_order_id`,
                                                    `order_data`,
                                                    `shipment_data`,
                                                    `status`,
                                                    `ship_request`
                                                )
                                                VALUES(
                                                    "'.$merchant_id.'",
                                                    "'.$skus.'",
                                                    "'.$order['purchaseOrderId'].'",
                                                    "'.addslashes(json_encode($order)).'",
                                                    "'.addslashes(json_encode($this->getShippingItems($order['orderLines']['orderLine']))).'",
                                                    "'.$status.'",
                                                    "[]"
                                                )';
                                    $queryObj = $connection->createCommand($query)->execute();
                                    
                                    $sql_email = 'SELECT email FROM walmart_shop_details where merchant_id='.$merchant_id;
                                    $model_email = Data::sqlRecords($sql_email,"one","select");
                                    $email = $model_email['email'];
                                    $mailData = ['sender' => 'shopify@cedcommerce.com',
                                                'reciever' => $email,
                                                'email' => $email,
                                                'merchant_id'=>$merchant_id,
                                                'subject' => 'You have an order from Walmart.com',
                                                'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                                                'purchase_order_id' => $order['purchaseOrderId'],
                                                'product_sku' => $skus
                                                ];
                                    $mailer = new Mail($mailData,'email/order.html','php',true);
                                    $mailer->sendMail();

                                }else{
                                    $message.="Order Not Acknowlegde\n";
                                    $error_array[]=array(
                                            'purchase_order_id'=>$order['purchaseOrderId'],
                                            'merchant_id'=>$merchant_id,
                                            'reason'=>isset($ackResponse['errors'])?$ackResponse['errors']:'Unable to acknowledge',
                                            'created_at'=>date("d-m-Y H:i:s"),
                                    );
                                    $count++;
                                    continue;
                                }
                            }
                        }
                        else
                        {
                            continue;
                        }

                    }
                    
                    unset($order_ack);
                    unset($ackData);
                    unset($itemArray);
                    unset($OrderItemData);
                    unset($collection);
                    unset($collectionOption);
                    unset($result);
                    if($message!=''){
                        $fileOrig=fopen($directory.'/fetch-error.log','a');
                        fwrite($fileOrig,$message);
                        fclose($fileOrig);
                    }
                    
                    fclose($handle);
                    unset($message);
                }
                unset($response);
    
            }
            catch (Exception $e)
            {
                Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
                return $this->redirect(['index']);
            }
        }
        //create order import error
        $errorCount=0;
        $getConfigValue = Data::getConfigValue($merchant_id,'partialorder');
        if(($getConfigValue && $getConfigValue=='no')  || !$getConfigValue || (count($allProductError)>0 && $getConfigValue=='yes'))
        {
                if($count>0 && count($error_array)>0){
                $errorFlag=false;
                $message1="";
                foreach ($error_array as $order_error){
                    $result="";
                    $orderErrorModel = $connection->createCommand("SELECT * FROM `walmart_order_import_error` WHERE purchase_order_id='".$order_error['purchase_order_id']."'");
                    $result = $orderErrorModel->queryOne();
                    if($result)
                    {
                        //$count=0;
                        $sql = Data::sqlRecords('UPDATE `walmart_order_import_error` SET `reason`= "'.addslashes($order_error['reason']).'" WHERE `purchase_order_id`="'.$order_error['purchase_order_id'].'"',null,'update');
                        continue;
                    }else{
                        if($getConfigValue=='yes'){
                            if(isset($allProductError[$order_error['purchase_order_id']])){
                                $sql='INSERT INTO `walmart_order_import_error`(`purchase_order_id`,`merchant_id`,`reason`)
                                VALUES("'.$order_error['purchase_order_id'].'","'.$order_error['merchant_id'].'","'.addslashes($order_error['reason']).'")';
                            }

                        }
                        else{
                            $sql='INSERT INTO `walmart_order_import_error`(`purchase_order_id`,`merchant_id`,`reason`)
                                VALUES("'.$order_error['purchase_order_id'].'","'.$order_error['merchant_id'].'","'.addslashes($order_error['reason']).'")';
                        }
                        try{
                            $errorCount++;
                            $model = $connection->createCommand($sql)->execute();
                        }catch(Exception $e){
                            $message1.='Invalid query: ' . $e->getMessage() . "\n";
                        }
                         $sql_email = 'SELECT email FROM walmart_shop_details where merchant_id='.$merchant_id;
                                        $model_email = Data::sqlRecords($sql_email,"one","select");
                                        $email = $model_email['email'];
                                        $mailData = ['sender' => 'shopify@cedcommerce.com',
                                                    'reciever' => $email,
                                                    'email' => $email,
                                                    'merchant_id'=>$order_error['merchant_id'],
                                                    'subject' => 'You have failed order from Walmart.com',
                                                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                                                    'purchase_order_id' => $order_error['purchase_order_id'],
                                                    'reason' => $order_error['reason']
                                                    ];
                                        $mailer = new Mail($mailData,'email/failedOrderMail.html','php',true);
                                        $mailer->sendMail();

                    }
                }
            }
            if($count>0){
                if($config){
                    echo "There is error for some orders.Please <a href='https://shopify.cedcommerce.com/integration/walmart/walmartorderimporterror/index'>click</a> to check failed order errors.";
                    return;
                }
                Yii::$app->session->setFlash('error',"There is error for some orders.Please <a href=".Yii::$app->request->getBaseUrl()."/walmart/walmartorderimporterror/index>click</a> to check failed order errors.");
            }
        }
            if($countOrder==0 && $count==0 && !$isError){
                if($config){
                    echo "No Orders in ready state";
                    return;
                }
                Yii::$app->session->setFlash('success',"No Orders in ready state");

            }
            elseif($isError)
            {
                if($config){
                    echo $isError;
                    return;
                }
                Yii::$app->session->setFlash('error'," ".$isError);
            }
            if($countOrder>0){
                 if($config){
                    echo ( $countOrder." Orders created successfully in shopify");
                    return;
                }
                Yii::$app->session->setFlash('success', $countOrder." Orders created successfully in shopify");
            }
            if($config){
                echo 'done';
                return;
            }
        return $this->redirect(['index']);
    }

    /**
     * function gor getting items with all calculated details
     */
    public function getItems($data){
 
        $items = array();
        if(!isset($data[0])){
            $data = [$data];
        }
        foreach($data as $item){
            $sku = $item['item']['sku'];
            $key = Data::getKey($sku);
            if(isset($items[$key])){
                $items[$key]['qty'] += 1; 
                /*$items[$key]['price'] += $this->getPrice($item['charges']['charge']);*/
                $items[$key]['shipping'] += $this->getShipping($item['charges']['charge']);
                /*$items[$key]['tax'] += $this->getTax($item['charges']['charge']);*/
                $items[$key]['total'] += $this->getTotal($item['charges']['charge']) + $items[$key]['tax'];
                
            }
            else
            {
                $items[$key] = $item;
                $items[$key]['qty'] = 1; 
                $items[$key]['price'] = $this->getPrice($item['charges']['charge']);
                $items[$key]['shipping'] = $this->getShipping($item['charges']['charge']);
                $items[$key]['tax'] = $this->getTax($item['charges']['charge']);
                $items[$key]['total'] = $this->getTotal($item['charges']['charge']) + $items[$key]['tax'];
            }
        }
        
        
        return $items;
    }

    public function getShippedItemsProcessedData($data){
        $price = 0;
        if(!isset($data[0])){
            $data = [$data];
        }
        $orderStatus = 'completed';
        foreach($data as $item){
            $status = isset($item['orderLineStatuses']['orderLineStatus']['status'])?$item['orderLineStatuses']['orderLineStatus']['status']:$item['orderLineStatuses']['orderLineStatus'][0]['status'];
            if($status=='Shipped')
                $price += $this->getTotal($item['charges']['charge'])+$this->getTax($item['charges']['charge']);  
            if($status == 'Created'||$status == 'Acknowledged'||$status == 'Partially Acknowledged'){
                $orderStatus = 'inprogress';
            }
        }
        return ['price'=>$price,'status'=>$orderStatus];
    }

    /**
     * function for getting shipping data
     */
    public function getShippingItems($data){

        $items = array();
        if(!isset($data[0])){
            $data = [$data];
        }
        foreach($data as $item){
            $sku = $item['item']['sku'];
            $key = Data::getKey($sku);
            $status = isset($item['orderLineStatuses']['orderLineStatus']['status'])?$item['orderLineStatuses']['orderLineStatus']['status']:'';
            $items[$key][$item['lineNumber']] = array('lineNumber'=>$item['lineNumber'],'status'=>$status,'sku'=>$sku);
            
        }
        
        
        return $items;
    }

    /**
     * function for getting total shipping amount of item
     */
    public function getShipping($data){
        if(!isset($data[0])){
            $data = [$data];
        }
        $price = 0;
        foreach($data as $priceDetails){
            if($priceDetails['chargeType']=='SHIPPING' && $priceDetails['chargeName']=='Shipping')
            {
                $price += $priceDetails['chargeAmount']['amount'];
            }
        }
        return $price;
    }

    /**
     * function for getting total product price of item
     */
    public function getPrice($data){
        if(!isset($data[0])){
            $data = [$data];
        }
        $price = 0;
        foreach($data as $priceDetails){
            if($priceDetails['chargeType']=='PRODUCT' && $priceDetails['chargeName']=='ItemPrice')
            {
                $price += $priceDetails['chargeAmount']['amount'];
            }
        }
        return $price;
    }

    /**
     * function for getting total tax price of item
     */
    public function getTax($data){
        if(!isset($data[0])){
            $data = [$data];
        }
        $price = 0;
        foreach($data as $priceDetails){
            if(isset($priceDetails['tax']) && isset($priceDetails['tax']['taxAmount']))
            {
                $price += (float)$priceDetails['tax']['taxAmount']['amount'];
            }
        }
        return $price;
    }

    /**
     * function for getting total price of item
     */
    public function getTotal($data){
        if(!isset($data[0])){
            $data = [$data];
        }
        $price = 0;
        foreach($data as $priceDetails){
                $price += (float)$priceDetails['chargeAmount']['amount'];
        }
        return $price;
    }

    public function actionSyncorder($config = false)
    {

        $merchant_id = $config ? $config['merchant_id']:Yii::$app->user->identity->id;
        if($merchant_id=='359'||$merchant_id=='50'||$merchant_id=='95'||$merchant_id=='880'||$merchant_id=='611')
            return;

        $fieldname = 'ordersync';
        $value = Data::getConfigValue($merchant_id,$fieldname);
        if($value == 'no')
            return;

        $connection = Yii::$app->getDb();
        $model="";
        $queryObj="";
        if(defined('CONSUMER_CHANNEL_TYPE_ID')||$config)
        {
            try
            {
                $countOrder=0;
                $token="";
                $shopname="";
                $token = $config ? $config['token']: TOKEN;
                $shopname = $config ? $config['shop_url']: SHOP;
                $shopifyError="";
                $resultdata=array();
                $queryObj="";
                $query="SELECT `purchase_order_id`,`order_data` FROM `walmart_order_details` WHERE merchant_id='".$merchant_id."' AND (status='acknowledged' OR status='Partially Acknowledged') AND shopify_order_id=0";

                $queryObj = $connection->createCommand($query);
                $resultdata = $queryObj->queryAll();

                $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
                if(count($resultdata)>0)
                {
                    foreach($resultdata as $val)
                    {
                        $Orderarray=array();
                        $itemArray=array();
                        $OrderTotal=0;
                        $totalWeight=0;
                        $autoReject = false;
                        $ikey=0;
                        $result=array();
                        $order=json_decode($val['order_data'],true);
                        $fulfillment_variant_ids=[];
                        $shipping = 0;
                        if(count($order)>0)
                        {

                            foreach ($this->getItems($order['orderLines']['orderLine']) as $key=>$value)
                            {

                                $collection="";
                                $queryObj="";
                                $query="SELECT id,sku,vendor,variant_id,qty,title FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND sku='".$value['item']['sku']."'";
                                $queryObj = $connection->createCommand($query);
                                $collection = $queryObj->queryOne();

                                if($collection=="")
                                {

                                    $collectionOption="";
                                    $queryObj="";
                                    $query="SELECT option_id,product_id,option_sku,vendor,option_qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND option_sku='".$value['item']['sku']."'";
                                    $queryObj = $connection->createCommand($query);
                                    $collectionOption = $queryObj->queryOne();
                                    if($collectionOption=="")
                                    {
                                        $shopifyError.=$order['purchaseOrderId']."=> Error: Product not found in shopify store\n";
                                        continue;
                                    }
                                    elseif($collectionOption && $value['qty']>$collectionOption['option_qty'])
                                    {
                                        $shopifyError.=$order['purchaseOrderId']."=> Error: Requested Order quantity is not available \n";
                                        continue;
                                    }
                                    else
                                    {
                                        $itemArray[$ikey]['product_id']=$collectionOption['product_id'];
                                        $itemArray[$ikey]['title']=$value['item']['productName'];
                                        $itemArray[$ikey]['variant_id']=$collectionOption['option_id'];
                                        $itemArray[$ikey]['vendor']=$collectionOption['vendor'];
                                        $itemArray[$ikey]['sku']=$collectionOption['option_sku'];
                                    }
                                }
                                elseif($collection && $value['qty']>$collection['qty'])
                                {
                                    $shopifyError.=$order['purchaseOrderId']."=> Error: Requested Order quantity is not available \n";
                                    continue;
                                }
                                else
                                {
                                    $itemArray[$ikey]['product_id']=$collection['id'];
                                    $itemArray[$ikey]['title']=$collection['title'];//$value['product_title']; // if error ['line item: title is too long']
                                    $itemArray[$ikey]['variant_id']=$collection['variant_id'];
                                    $itemArray[$ikey]['vendor']=$collection['vendor'];
                                    $itemArray[$ikey]['sku']=$collection['sku'];
                                }
                                $qty=0;
                                $Totalprice=0;
                                $qty=$value['qty'];
                                $itemArray[$ikey]['id']=$order['purchaseOrderId'];
                                $itemArray[$ikey]['price'] = $value['price'] + $value['tax'];
                                $weight = self::getWeightbyId($collection['id'],$merchant_id);
                                if($weight){
                                    $productWeight=self::convertWeightlbsToGram($weight['weight']);
                                    if($productWeight){
                                        $totalWeight+=$productWeight*$qty;
                                    }
                                }
                                $shipping += $value['shipping'];
                                $Totalprice = $value['total'];
                                $OrderTotal += $Totalprice;
                                $itemArray[$ikey]['quantity'] = $qty;
                                $itemArray[$ikey]['requires_shipping']=true;
                                $prodVariants=$sc->call('GET',"/admin/variants/".$itemArray[$ikey]['variant_id'].".json");
                                if(isset($prodVariants['fulfillment_service']) && ($prodVariants['fulfillment_service']=='amazon_marketplace_web' || $prodVariants['inventory_management']=='amazon_marketplace_web'))
                                {
                                    $fulfillment_variant_ids[]=$itemArray[$ikey]['variant_id'];
                                    $itemArray[$ikey]['fulfillment_service']='amazon_marketplace_web';
                                }

                                $ikey++;

                            }
                        }

                        $customer_Info="";

                        /* if(isset($result['buyer']['name']))
                        {
                            $customer_Info=$result['buyer']['name'];

                            $customer_Info = explode(" ", $customer_Info);

                            if(!isset($customer_Info[1]))
                                $customer_Info[1] = $customer_Info[0];
                        }
                        else
                        {
                            $customer_Info = $result['shipping_to']['recipient']['name'];
                            $customer_Info = explode(" ", $customer_Info);
                            if(!isset($customer_Info[1]))
                                $customer_Info[1] = $customer_Info[0];
                        } */

                        if(isset($order['shippingInfo']))
                        {
                            $customer_Info = $order['shippingInfo']['postalAddress']['name'];
                            $customer_Info = preg_replace('/\s+/', ' ', $customer_Info);
                            $customer_Info = explode(" ", $customer_Info);
                            if(!isset($customer_Info[1]))
                                $customer_Info[1] = $customer_Info[0];
                        }
                        else
                        {
                            $customer_Info=$result['buyer']['name'];

                            $customer_Info = explode(" ", $customer_Info);

                            if(!isset($customer_Info[1]))
                                $customer_Info[1] = $customer_Info[0];
                        }
                        $first_name="";
                        $last_name="";
                        $email="";

                        $first_name=$customer_Info[0];
                        $last_name=$customer_Info[1];
                        $email = $order['customerEmailId'];
                        //echo $email;die;
                        /* if ($merchant_id==219){
                            $email="edison@misslandia.com";
                        } */
                        //new code
                        //first address
                        $first_addr="";$second_addr="";

                        $first_addr=$order['shippingInfo']['postalAddress']['address1'];
                        $second_addr=$order['shippingInfo']['postalAddress']['address2'];

                        /* if(!$second_addr){
                            $second_addr=$result['shipping_to']['address']['address1'];
                        } */
                        $phone_number="";
                        if(isset($order['shippingInfo']['phone']) && $order['shippingInfo']['phone']){
                            $phone_number=$order['shippingInfo']['phone'];
                        }
                        $billing_addr=[];
                        $shipping_addr=[];

                        if(!empty($phone_number))
                        {
                            $billing_addr=array(
                                "first_name"=> $customer_Info[0],
                                "last_name"=>$customer_Info[1],
                                "address1"=> $first_addr,
                                "address2"=> $second_addr,
                                "phone"=> $phone_number,
                                "city"=> $order['shippingInfo']['postalAddress']['city'],
                                "province"=> $order['shippingInfo']['postalAddress']['state'],
                                "country"=> "United States",
                                "zip"=> $order['shippingInfo']['postalAddress']['postalCode']
                            );
                            $shipping_addr= array(
                                "first_name"=> $customer_Info[0],
                                "last_name"=>$customer_Info[1],
                                "address1"=> $first_addr,
                                "address2"=> $second_addr,
                                "phone"=> $phone_number,
                                "city"=> $order['shippingInfo']['postalAddress']['city'],
                                "province"=> $order['shippingInfo']['postalAddress']['state'],
                                "country"=> "United States",
                                "zip"=> $order['shippingInfo']['postalAddress']['postalCode']
                            );
                        }
                        else
                        {
                            $phone_number = time();
                            $billing_addr=array(
                                "first_name"=> $customer_Info[0],
                                "last_name"=>$customer_Info[1],
                                "address1"=> $first_addr,
                                "address2"=> $second_addr,
                                "phone"=> $phone_number,
                                "city"=> $order['shippingInfo']['postalAddress']['city'],
                                "province"=> $order['shippingInfo']['postalAddress']['state'],
                                "country"=> "United States",
                                "zip"=> $order['shippingInfo']['postalAddress']['postalCode']
                            );
                            $shipping_addr= array(
                                "first_name"=> $customer_Info[0],
                                "last_name"=>$customer_Info[1],
                                "address1"=> $first_addr,
                                "address2"=> $second_addr,
                                "phone"=> $phone_number,
                                "city"=> $order['shippingInfo']['postalAddress']['city'],
                                "province"=> $order['shippingInfo']['postalAddress']['state'],
                                "country"=> "United States",
                                "zip"=> $order['shippingInfo']['postalAddress']['postalCode']
                            );
                        }

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
                                "shipping_address"=> $shipping_addr,
                                "note"=>"Walmart Marketplace-Integration",
                                'tags'=>"walmart.com",
                                "email"=> $email,
                                "inventory_behaviour"=>"decrement_obeying_policy",
                                "financial_status"=>"paid",   //"financial_status"=>"pending",
                                "format"=> "json"
                            );
                            if($totalWeight>0){
                                $Orderarray['order']['total_weight']=$totalWeight;
                            }
                            if($shipping>0 || true){
                                if($merchant_id !='335'){
                                    if (strcasecmp($order['shippingInfo']['methodCode'], 'Value') == 0) {
                                        $order['shippingInfo']['methodCode'] = 'Standard Shipping';
                                    }

                                    elseif(strcasecmp($order['shippingInfo']['methodCode'], 'Standard') == 0){
                                        $order['shippingInfo']['methodCode'] = 'Standard Shipping';
                                    }
                                    elseif (strcasecmp($order['shippingInfo']['methodCode'], 'Expedited') == 0) {
                                        $order['shippingInfo']['methodCode'] = 'Expedited Shipping';
                                    }
                                    elseif(strcasecmp($order['shippingInfo']['methodCode'], 'Next Day') == 0){
                                        $order['shippingInfo']['methodCode'] = 'Priority Shipping';
                                    }

                                    elseif(strcasecmp($order['shippingInfo']['methodCode'], 'Priority') == 0){
                                        $order['shippingInfo']['methodCode'] = 'Priority Shipping';
                                    }

                                    elseif(strcasecmp($order['shippingInfo']['methodCode'], 'Express') == 0){
                                        $order['shippingInfo']['methodCode'] = 'Expedited Shipping';
                                    }
                                }
                                $Orderarray['order']['shipping_lines'] = array(
                                    array(
                                        "title"=> $order['shippingInfo']['methodCode'],
                                        "price"=> $shipping,
                                        "code"=> $order['shippingInfo']['methodCode'],
                                        "source"=> "Walmart",
                                        "requested_fulfillment_service_id"=> null,
                                        "delivery_category"=> null,
                                        "carrier_identifier"=>$order['shippingInfo']['methodCode'],
                                        "tax_lines"=> [],
                                    )
                                );
                            }
                            /*  if ($merchant_id==219){
                                echo "Orderarray <hr><pre>";
                                print_r($Orderarray);
                                die;
                            } */

                            $response=array();
                            $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$order['purchaseOrderId'];
                            if (!file_exists($directory)){
                                mkdir($directory,0775, true);
                            }
                            $handle = fopen($directory.'/sync.log','a');
                            fwrite($handle,json_encode($Orderarray).PHP_EOL);

                            $response = $sc->call('POST', '/admin/orders.json',$Orderarray);

                            fwrite($handle,PHP_EOL.'Shopify Response: '.json_encode($response).PHP_EOL);

//                                                  if ($merchant_id==3){
//                                                      echo "<pre>";
//                                                      print_r($response);
//                                                      die;
//                                                  }

                            $lineArray=array();
                            if(!array_key_exists('errors',$response))
                            {
                                //send request for order acknowledge
                                foreach($response['line_items'] as $key=>$value)
                                {
                                    $lineArray[$key]=$value['id'];
                                    if(is_array($fulfillment_variant_ids) && in_array($value['variant_id'], $fulfillment_variant_ids))
                                    {
                                        $linesItemFulfillment[$key]['id']=$value['id'];
                                    }
                                }
                                $queryObj="";
                                $query="UPDATE `walmart_order_details` SET  shopify_order_name='".$response['name']."',shopify_order_id='".$response['id']."'
                                                    where merchant_id='".$merchant_id."' AND purchase_order_id='".$order['purchaseOrderId']."'";

                                $countOrder++;
                                $queryObj = $connection->createCommand($query)->execute();
                                if(is_array($fulfillment_variant_ids) && count($fulfillment_variant_ids)>0)
                                {
                                    $shopifyShip['fulfillment']=[
                                        'line_items'=>$linesItemFulfillment,
                                    ];
                                    $shipmentResponse = $sc->call('POST', '/admin/orders/'.$response['id'].'/fulfillments.json',$shopifyShip);
                                }


                            }
                            else
                            {
                                $shopifyError.=$order['purchaseOrderId']."=> Error: ".json_encode($response['errors'])."\n";
                            }
                        }
                        elseif(count($order)>0)
                        {
                            $shopifyError.=$order['purchaseOrderId']."=> Error: Product not found \n";
                        }
                    }
                }else{
                    if($config){

                    }else{
                        Yii::$app->session->setFlash('warning', "No Orders found to create in shopify");
                    }
                }
                if($shopifyError){

                    if($config)
                        echo ("Order(s) not created in shopify:\n".$shopifyError);
                    else
                        Yii::$app->session->setFlash('error', "Order(s) not created in shopify:\n".$shopifyError);
                }
                unset($Orderarray);
                unset($itemArray);
                unset($result);
                unset($response);
                unset($lineArray);
                unset($resultdata);
            }
            catch (Exception $e)
            {
                if($config)
                    echo ($e->getMessage());
                else
                    Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
            }
        }
        if($countOrder>0){

            if($config)
                echo ($countOrder." Order Created in shopify..");
            else
                Yii::$app->session->setFlash('success', $countOrder." Order Created in shopify...");
        }
        if($config)
            return;
        return $this->redirect(['index']);
    }

    /**
     * Displays a single WalmartOrderDetail model.
     * @param string $id
     * @return mixed
     */
   /* public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }*/

    public function actionVieworderdetails()
    {
        $this->layout="main2";
        if(Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }        
        $PurchaseOrderId = "";
        $PurchaseOrderId = $_POST['purchase_order_id'];                        
        $response = ""; 
        $responseOrders = array();
        $this->walmartHelper = new Walmartapi(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
        $orderdata = $this->walmartHelper->getOrder($PurchaseOrderId);
        //$response = $this->walmartHelper->getRequestorder('v3/orders/'.$PurchaseOrderId);    
        $responseOrders=json_decode($orderdata,true);
        $html=$this->render('view', [
            //'model' => $responseOrders['list']['elements']['order'][0],
            'model' => $responseOrders['order'],
        ],true);
        return $html;        
    }

    /**
     * Creates a new WalmartOrderDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    /*
    public function actionCreate()
    {
        $model = new WalmartOrderDetail();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    */

    /**
     * Updates an existing WalmartOrderDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing WalmartOrderDetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the WalmartOrderDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return WalmartOrderDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartOrderDetail::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /*
     * function for getting mapped carrier
     */
    public function getCarrier($request_shipping_carrier,$merchant_id){
        $query = "SELECT value FROM `walmart_config` WHERE `merchant_id`={$merchant_id} AND data='shipping_mappings' ";
        $result = Data::sqlRecords($query, null, 'select');
        
        $shipping_mappings = '';
        if(count($result)==0)
        {
            $shipping_mappings = [];
        }
        else
        {
            $shipping_mappings = json_decode($result[0]['value'],true);
        }
        $key = trim(str_replace(' ','',$request_shipping_carrier));
        if(isset($shipping_mappings[$key])){
            return $shipping_mappings[$key]['walmart'];
        }
        $carriers = Data::getWalmartCarriers();
        if(!isset($carriers[$request_shipping_carrier]))
            return 'Other';
        return $request_shipping_carrier;
    }

    public function actionCurlprocessfororder($data= false){
        //$data = $_POST['data'];
        if (!$data) 
        $data = $_POST;
        $mannual = false;
        $testing = false;
        $shipData = [];
        if(isset($_GET['testing'])||isset($_POST['testing'])){
            $data = $this->getTestData();
            $testing = true;
        }
        if(isset($_GET['mannual'])){
            $shipData = $data = $_GET;
            $mannual = true;
            if($_GET['id']==''||$_GET['name']=='')
                die('Name or id blank');
        }
        $connection = Yii::$app->getDb();
        
        // yaha tumhara pura data aa jayega $_POST mein jo bhi webhook pe aaya hoga, ab uss data se jo bhi kaam krana ho wo kra lo
        $address1="";
        $city="";
        $state="";
        $zip="";
        $address2="";
        $orderData="";
        $flag=false;
        $modelUser="";
        $jetOrderdata="";
        $shipdatetime="";
        $request_shipping_carrier="";
        $logMessage="";
        $errorMessage="";
        $shiptime="";
        if($data && isset($data['id']))
        {
            $orderData = WalmartOrderDetail::find()->where(['shopify_order_id'=>$data['id'],'shopify_order_name'=>$data['name']])->one();
            
            if($mannual && $orderData->ship_request!=''&& $orderData->ship_request!='[]'){
                $ship_request = json_decode($orderData->ship_request,true);
                $data = $ship_request[$shipData['key']];
                unset($ship_request[$shipData['key']]);
                
            }

            if($orderData)
            {
                $merchant_id="";
                $merchant_id=$orderData->merchant_id;
                $directory = \Yii::getAlias('@webroot').'/var/order/'.$merchant_id.'/'.$orderData->purchase_order_id;
                if (!file_exists($directory)){
                    mkdir($directory,0775, true);
                }
                $handle=fopen($directory.'/shipment.log','a');
                fwrite($handle,'Requested Shipment Data From Shopify : '.PHP_EOL.json_encode($data).PHP_EOL.PHP_EOL);

                $jetConfig=[];
                $jetConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$merchant_id."'", 'one');
                if($jetConfig)
                {
                    if(!defined('CONSUMER_CHANNEL_TYPE_ID')){
                        define("CONSUMER_CHANNEL_TYPE_ID",$jetConfig['consumer_channel_type_id']);
                    }
                    if(!defined('API_USER')){
                        define("API_USER",$jetConfig['consumer_id']);
                    }
                    if(!defined('API_PASSWORD')){
                        define("API_PASSWORD",$jetConfig['secret_key']);
                    }
                }else{
                    return false;
                }
                $filename="";
                $filename1="";
                $file="";
                $file1="";
                $modelUser="";
                $token="";
                $shopname="";
                $shopifymodel="";
                $jetConfig="";
                $email="";
                $fullfillmentnodeid="";

                if (!file_exists(\Yii::getAlias('@webroot').'/var/shipment-log-final/'.date('d-m-Y'))){
                    mkdir(\Yii::getAlias('@webroot').'/var/shipment-log-final/'.date('d-m-Y'),0775, true);
                }
                    
                $errorMessage.=$shopname."[".date('d-m-Y H:i:s')."]\n";
                
                $this->walmartHelper = new Walmartapi(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
                
                
                if($orderData->status=="acknowledged" || $orderData->status=='inprogress' || $orderData->status=="Partially Acknowledged")
                {
                    fwrite($handle,'Order Status in DB : '.$orderData->status.PHP_EOL);
                    $errorMessage.="Enter under acknowledged \n";
                    //fwrite($file1, $errorMessage);
                    try
                    {
                        $customerModel="";
                        $merchantDetails = Data::sqlRecords("SELECT `data`,`value` FROM `walmart_config` WHERE merchant_id='".$merchant_id."'", 'all');
                        
                        $merchantData = array();
                        foreach($merchantDetails as $row){
                            $merchantData[$row['data']] = $row['value'];
                        }
                       
                        unset($merchantDetails);
                        $data['timestamp']=date("d-m-Y H:i:s");
                       
    
                        //$orderData->save(false);
                        $flagCarr=false;
                        $merchant_order_id="";
                        //$request_shipping_carrier="";
                        $merchant_order_id=$orderData->purchase_order_id;
                        $id=$orderData->id;
                        $walmartOrderData=json_decode($orderData->order_data,true);
                        
                        
                        if(isset($data['fulfillments']) && isset($data['fulfillments'][0])){
                            if($mannual)
                            {
                                $request_shipping_carrier = $shipData['carrier'];
                            }
                            else
                            {
                                $request_shipping_carrier = $data['fulfillments'][0]['tracking_company'];
                            }
                            fwrite($handle,'Requested Shipment : '.$request_shipping_carrier.PHP_EOL);
                            $request_shipping_carrier = $this->getCarrier($request_shipping_carrier,$merchant_id);
                            fwrite($handle,'Mapped Shipment : '.$request_shipping_carrier.PHP_EOL);
                            $response=array();
                            $response=$data['fulfillments'];
                            $shipment_id=$response[0]['id'];
                            $offset_end="";
                            $offset_end = $this->getStandardOffsetUTC();
                            if(empty($offset_end) || trim($offset_end)=='')
                                $offset = '.0000000-00:00';
                            else
                                $offset = '.0000000'.trim($offset_end);
                            $dt = new \DateTime($response[0]['updated_at']);
                            $shiptime="";
                            $shipdatetime="";
                            $expected_delivery_date="";
                            $shiptime=$dt->format('Y-m-d H:i:s');
                            $shipdatetime=strtotime($dt->format('Y-m-d H:i:s'));
                            $expected_delivery_date = date("Y-m-d", $shipdatetime) . 'T' . date("H:i:s", $shipdatetime).$offset;

                            $tracking_number="";
                            if(isset($response[0]['tracking_number'])){
                                $flagCarr=false;
                                $tracking_number=$response[0]['tracking_number'];
                                //$request_shipping_carrier=$response[0]['tracking_company'];
                            }else{
                                $tracking_number=time()."98563";
                            }
                            $resultAdd="";
                            $resultAdd2="";
                            $Resultcity="";
                            $Resultstate="";
                            $Resultzip="";
                            $trackingUrl = isset($response[0]['tracking_url'])?$response[0]['tracking_url']:'';
                            $resultAdd=$merchantData['first_address']?:'';
                            

                            $resultAdd2=$merchantData['second_address']?:'';
                        

                            $Resultcity=$merchantData['city']?:'';
                            

                            $Resultstate=$merchantData['state']?:'';
                            

                            $Resultzip=$merchantData['zipcode']?:'';
                            

                            if($address1!='' || $city!='' || $state!='' || $zip!='')
                            {
                                $flag=true;
                                $array_return = array('address1'=>$address1,
                                        'address2'=>$address2,
                                        'city'=>$city,
                                        'state'=>$state,
                                        'zip_code'=>$zip
                                );
                            }
                            $shipment_arr=array();
                            $shopify_shipment_data=array();

                            //$errorMessage="shipment Items".print_r($response[0]['line_items'],true)."\n";
                            //fwrite($file1, $errorMessage);
                            foreach($response[0]['line_items'] as $key=>$value)
                            {
                                $sku="";
                                $product="";
                                $sku = $value['sku'];
                                $updateInventory=array();
                                $resquest_cancel=0;
                                $cancel_qunt=0;
                                $updateQty=0;
                                //$resquest_cancel=$this->checkcancelQty($sku,$jetOrderdata['order_items']);
                                
                               
                                if($flag)
                                {
                                    $RMA_number = "";
                                    $days_to_return = 30;
                                    $shipment_arr[]= array('shipment_item_id'=>$shipment_id,
                                            'merchant_sku'=>$sku,
                                            'response_shipment_sku_quantity'=>(int)$value['quantity'],
                                            'response_shipment_cancel_qty'=>(int)$cancel_qunt,
                                            'RMA_number'=>$RMA_number,
                                            'days_to_return'=>(int)$days_to_return,
                                            'return_location'=>$array_return
                                    );
                                }
                                else
                                {
                                    $shipment_arr[]= array('shipment_item_id'=>$shipment_id,
                                            'merchant_sku'=>$sku,
                                            'response_shipment_sku_quantity'=>(int)$value['quantity'],
                                            'response_shipment_cancel_qty'=>(int)$cancel_qunt
                                    );
                                }
                                $shopify_shipment_data[]=implode(',',array(0=>$sku,1=>$value['quantity'],2=>$value['fulfillment_status']));
                            }
                            $data_ship=array();
                            if($zip=="")
                                $zip=85705;
                            if($flagCarr)
                            {
                                $data_ship['shipments'][]=array (
                                        'purchase_order_id' => $orderData->purchase_order_id,
                                        'shipment_tracking_number' => $tracking_number,
                                        'shipment_tracking_url' => $trackingUrl,
                                        'response_shipment_date'=>$expected_delivery_date,
                                        'response_shipment_method'=>'Standard',
                                        'expected_delivery_date'=>$expected_delivery_date,
                                        'ship_from_zip_code'=>$zip,
                                        'carrier_pick_up_date'=>$expected_delivery_date,
                                        'carrier'=>"",
                                        'shipment_items'=>$this->getShipmentItems($shipment_arr,$orderData->shipment_data)
                                );
                            }
                            else{
                                $data_ship['shipments'][]=array (
                                        'purchase_order_id' => $orderData->purchase_order_id,
                                        'shipment_tracking_number' => $tracking_number,
                                        'shipment_tracking_url' => $trackingUrl,
                                        'response_shipment_date'=>$expected_delivery_date,
                                        'response_shipment_method'=>'Standard',
                                        'expected_delivery_date'=>$expected_delivery_date,
                                        'ship_from_zip_code'=>$zip,
                                        'carrier_pick_up_date'=>$expected_delivery_date,
                                        'carrier'=>$request_shipping_carrier,
                                        'shipment_items'=>$this->getShipmentItems($shipment_arr,$orderData->shipment_data)
                                );
                            }
                            
                            $walmartData="";
                            if($data_ship)
                            {
                                fwrite($handle,"Sending prepared shipment data on walmart : ".PHP_EOL.json_encode($data_ship).PHP_EOL.PHP_EOL);
                                $helper = $this->walmartHelper;
                                $walmartData = $helper->shipOrder($data_ship);
                                
                                
                                

                                if($testing)
                                    $walmartData = $this->getShipingResponseData();

                                if($data['email']=='satyaprakash@cedcoss.com')
                                    $walmartData = $this->getShipingResponseData($data,$orderData,false);

                                
                                fwrite($handle,'Walmart shipment response : '.$walmartData.PHP_EOL);

                                $walmartData = str_replace('ns3:','',$walmartData);
                                $walmartData = str_replace('ns4:','',$walmartData);

                                $responseArray=array();
                                $responseArray=json_decode($walmartData,true);
                                


                                if(!isset($responseArray['errors']))
                                {
                                    $responseArray = $responseArray['order'];
                                    $walmartOrderData['orderLines']['orderLine'] = isset($responseArray['orderLines']['orderLine'][0])?$responseArray['orderLines']['orderLine']:[$responseArray['orderLines']['orderLine']];
                                    fwrite($handle,'calling getShippedItemsProcessedData: '.PHP_EOL);
                                    $processedData = $this->getShippedItemsProcessedData($walmartOrderData['orderLines']['orderLine']);
                                    fwrite($handle,'getShippedItemsProcessedData Response: '.print_r($processedData,true).PHP_EOL);
                                    $price = $processedData['price'];
                                    $status = $processedData['status'];
                                    $orderData1 = addslashes(json_encode($walmartOrderData));
                                    fwrite($handle,'calling getShippingItems: '.PHP_EOL);
                                    $shipmentData = addslashes(json_encode($this->getShippingItems($responseArray['orderLines']['orderLine'])));


                                    fwrite($handle,'after getShippingItems shipmentData: '.print_r($shipmentData,true).PHP_EOL);
                                    $errorMessage.="shipment data send to walmart \n";
                                    //fwrite($file1, $errorMessage);
                                    if($mannual){
                                        $ship_request = addslashes(json_encode($ship_request));
                                        $query="UPDATE `walmart_order_details` SET  order_data='".$orderData1 ."',shipment_data='".$shipmentData."',status='{$status}',order_total='{$price}',ship_request='{$ship_request}'
                                                where merchant_id='".$orderData->merchant_id."' AND purchase_order_id='".$orderData->purchase_order_id."'";
                                    }
                                    else
                                    {
                                        $query="UPDATE `walmart_order_details` SET  order_data='".$orderData1 ."',shipment_data='".$shipmentData."',status='{$status}',order_total='{$price}'
                                                where merchant_id='".$orderData->merchant_id."' AND purchase_order_id='".$orderData->purchase_order_id."'";
                                    }
                                    

                                    $connection->createCommand($query)->execute();
                                    $errorMessage.="shipment created\n";
                                }
                                else
                                {
                                    fwrite($handle,"Reqeusted Xml : ".PHP_EOL.$helper->requestedXml.PHP_EOL.PHP_EOL);
                                    fwrite($handle,'shipment not created for order.'.PHP_EOL);
                                  
                                    if(!$mannual){
                                        if($orderData->ship_request==''){
                                            $ship_request = [];
                                        }
                                        else
                                        {
                                            $ship_request = json_decode($orderData->ship_request);
                                        }
                                        $ship_request[] = $data;
                                        $ship_request = addslashes(json_encode($ship_request));
                                        $query="UPDATE `walmart_order_details` SET ship_request='{$ship_request}'
                                                where merchant_id='".$orderData->merchant_id."' AND purchase_order_id='".$orderData->purchase_order_id."'";
                                        $connection->createCommand($query)->execute();
                                    }
                                    //fwrite($file1, $errorMessage);
                                    fwrite($handle,' Saving errror msg'.PHP_EOL);

                                    $orderData->save(false);
                                    /*$ordererrorMdoel = "";
                                    $ordererrorMdoel = new WalmartOrderImportError();
                                    $ordererrorMdoel->purchase_order_id=$orderData->purchase_order_id;
                                    $ordererrorMdoel->reason="Order Not fulfilled on Walmart.\nError:".json_encode($responseArray['errors']);
                                    //$ordererrorMdoel->created_at=date("d-m-Y H:i:s");
                                    $ordererrorMdoel->merchant_id=$merchant_id;
                                    $ordererrorMdoel->save(false);*/

                                    $orderErrorModel = $connection->createCommand("SELECT * FROM `walmart_order_import_error` WHERE purchase_order_id='".$orderData->purchase_order_id."'");
                                    $result = $orderErrorModel->queryOne();
                                    if($result)
                                    {
                                        $reason = 'Order Not fulfilled on Walmart.\nError:'.json_encode($responseArray['errors']);
                                        /*print_r("UPDATE `walmart_order_import_error` SET `reason`= '".$reason."' WHERE `purchase_order_id`='".$orderData->purchase_order_id."'");die;*/

                                        $sql = Data::sqlRecords("UPDATE `walmart_order_import_error` SET `reason`= '".addslashes($reason)."' WHERE `purchase_order_id`='".$orderData->purchase_order_id."'",null,'update');
                                    }else{
                                        $ordererrorMdoel = "";
                                        $ordererrorMdoel = new WalmartOrderImportError();
                                        $ordererrorMdoel->purchase_order_id=$orderData->purchase_order_id;
                                        $ordererrorMdoel->reason=addslashes(json_encode($responseArray['errors']));
                                        //$ordererrorMdoel->created_at=date("d-m-Y H:i:s");
                                        $ordererrorMdoel->merchant_id=$merchant_id;
                                        $ordererrorMdoel->save(false);
                                    }

                                }
                            }
                        }
                    }
                    catch (ShopifyApiException $e)
                    {
                        $errorMessage.=$shopname."[".date('d-m-Y H:i:s')."]\n"."Error in shopify api".$e->getMessage()."\n";
                        fwrite($handle, $errorMessage);
                        fclose($handle);
                        return;
                    }
                    catch (ShopifyCurlException $e)
                    {
                        $errorMessage.=$shopname."[".date('d-m-Y H:i:s')."]\n"."Error in shopify api".$e->getMessage()."\n";
                        fwrite($handle, $errorMessage);
                        fclose($handle);
                        return;
                    }
                    catch(Exception $e){
                        $errorMessage.=$shopname."[".date('d-m-Y H:i:s')."]\n"."Error exception".$e->getMessage()."\n";
                        fwrite($handle, $errorMessage);
                        fclose($handle);
                        return;
                    }   
                }
                fclose($handle);
                return;
            }
            else{
                return;
            }
        }else{
            //$errorMessage.=$shopname."[".date('d-m-Y H:i:s')."]\n"."Shipment Data not found\n";
            //fwrite($file1, $errorMessage);
            //fclose($file1);
            return;
        }
    }

    /**
     * function for getting shipiing items with lineNumber
     * 
     * @return  array
     */
    public function getShipmentItems($items,$shipmentData){
        $shipmentData = json_decode($shipmentData,true);
        $itemsToShip = array();
       
        foreach($items as $item){
            $sku = $item['merchant_sku'];
            $key = Data::getKey($sku);
            $key = isset($shipmentData[$key])?$key:$sku;
            $qty = $item['response_shipment_sku_quantity'];
            if(isset($shipmentData[$key])){
                foreach($shipmentData[$key] as $shimpentItem)
                {
                    if(strtolower($shimpentItem['status'])!='shipped'){

                        $item['response_shipment_sku_quantity'] = 1;
                        $item['lineNumber'] = $shimpentItem['lineNumber'];
                        $itemsToShip[] = $item;
                        $qty--;
                    }
                    if($qty==0){
                        break;
                    }
                }
                
            }
        }
       
        return $itemsToShip;
    }

    public function getStandardOffsetUTC()
    {
        $timezone="";
        $timezone = date_default_timezone_get();
        if($timezone == 'UTC') {
            return '';
        } else {
            $timezone = new \DateTimeZone($timezone);
            $transitions="";
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);
    
            foreach (array_reverse($transitions, true) as $transition)
            {
                if (isset($transition['isdst']) && $transition['isdst'] == 1)
                {
                    continue;
                }
                return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
            }
            return false;
        }
    }

    public function actionCorrectTotal(){
        $orders = Data::sqlRecords("SELECT `merchant_id`,`order_data`,`shipment_data`,`order_total` FROM `walmart_order_details`", 'all');
        foreach($orders as $order){
            
            $orderData = json_decode($order['order_data'],true);
            $processedData = $this->getShippedItemsProcessedData($orderData['orderLines']['orderLine']);
            if($processedData['price']!=$order['order_total']){
               var_dump($processedData['price']); print_r($order);
            }
            
        }
    }

    public function actionViewShopifyShipment($shipment)
    {
        $query = "select `walmart_order_details`.`id`,`walmart_order_details`.`merchant_id`,`purchase_order_id`,`shopify_order_id`,`shopify_order_name`,`username`,`auth_key`,`consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_order_details` inner join `user` ON `walmart_order_details`.`merchant_id`=`user`.`id`inner join `walmart_configuration` ON `walmart_order_details`.`merchant_id`=`walmart_configuration`.`merchant_id` WHERE (`walmart_order_details`.`status`='acknowledged' OR `walmart_order_details`.`status`='Partially Acknowledged') AND `shopify_order_id`!='' AND `walmart_order_details`.`merchant_id`!=14";
        $orderAckCollection = Data::sqlRecords($query, "all", "select");
        $count=0;
        $a=0;
        if (!empty($orderAckCollection) && is_array($orderAckCollection)) 
        {
            foreach ($orderAckCollection as $key => $value) 
            {               
                $shopname = $value['username'];
                $token = $value['auth_key'];
                $shopify_order_id = $value['shopify_order_id'];
                
                $sc = new ShopifyClientHelper($shopname, $token, PUBLIC_KEY, PRIVATE_KEY);
                $shipmentResponse = $sc->call('GET', '/admin/orders/' . $shopify_order_id . '.json?fields=fulfillments');
                //print_r($shipmentResponse);die;
                if(is_array($shipmentResponse) && isset($shipmentResponse['fulfillments']) && count($shipmentResponse['fulfillments'])>0) 
                {   
                    $response=array();
                   
                    $this->walmartHelper = new Walmartapi($value['consumer_id'],$value['secret_key'],$value['consumer_channel_type_id']);

                    $orderdata = $this->walmartHelper->getOrder($value['purchase_order_id']);

                    $trackingnumber = "";
                    $carrier = "";
                    $status = "";
                    $orderdata1 = json_decode($orderdata,true);
                    $shipdata = $orderdata1['list']['elements']['order'][0]['orderLines'];
                    if (is_array($shipdata)) {
                        foreach ($shipdata as $key => $data) {
                            $status = $data[0]['orderLineStatuses']['orderLineStatus'][0]['status'];
                            if ($status == "Shipped") {
                                $trackingnumber = $data[0]['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['trackingNumber'];
                                $carrier = $data[0]['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['carrierName']['carrier'];
                            }
                        }
                    }
                    if ($shipment==2 && $status== "Shipped") {
                         $query="UPDATE `walmart_order_details` SET status='completed' WHERE purchase_order_id='".$value['purchase_order_id']."'";
                        $orderCollection = Data::sqlRecords($query, "one", "update");
                        echo 'merchant_id: '.$value['merchant_id']."  =>  PurchaseID:  ".$value['purchase_order_id']."  =>  ShopifyID:  ".$value['shopify_order_id']."  =>  Walmart status:  ".$status."  =>  Tracking no.:  ".$trackingnumber."  =>  Carrier:  ".$carrier."<br><br>";
                    $count++;
                    }
                    else if ($shipment==1 && $status != "Shipped") {
                        //manual shipment start here.....raza
                       if (file_exists(\Yii::getAlias('@webroot').'/var/order/'.$value['merchant_id'].'/'.$value['purchase_order_id'].'/shipment.log')) {
                         $handle = fopen( \Yii::getAlias('@webroot').'/var/order/'.$value['merchant_id'].'/'.$value['purchase_order_id'].'/shipment.log','r');
                            $line = 0;
                            while (($buffer = fgets($handle)) !== FALSE) {
                               if ($line == 1) {
                                   
                                   break;
                               }   
                               $line++;
                            }
                           $data= json_decode(trim($buffer),true);
                           $ship = $this->actionCurlprocessfororder($data);
                           //print_r($ship."<br>");
                           $a++;
                            fclose($handle);
                        }
                        else{
                            $data=array();
                            $data['id'] = $value['shopify_order_id'];
                            $data['name'] = $value['shopify_order_name'];
                            $data['fulfillments'] = $shipmentResponse['fulfillments'];
                            $ship = $this->actionCurlprocessfororder($data);
                            $a++;
                        }

                            //manual shipment end
                       echo 'merchant_id: '.$value['merchant_id']."  =>  PurchaseID:  ".$value['purchase_order_id']."  =>  ShopifyID:  ".$value['shopify_order_id']."  =>  Walmart status:  ".$status."  =>  Tracking no.:  ".$trackingnumber."  =>  Carrier:  ".$carrier."<br><br>";
                    $count++;
                    }
                    
                }  
            }                           
        }
        echo $a."Total Orders not shipped on Walmart :: ".$count;                                  
    }

    public function actionSyncShipmentData()
    {   
        $merchant_id = Yii::$app->user->identity->id;
        $query = "select `walmart_order_details`.`id`,`walmart_order_details`.`merchant_id`,`purchase_order_id`,`shopify_order_id`,`shopify_order_name`,`username`,`auth_key`,`consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_order_details` inner join `user` ON `walmart_order_details`.`merchant_id`=`user`.`id`inner join `walmart_configuration` ON `walmart_order_details`.`merchant_id`=`walmart_configuration`.`merchant_id` WHERE (`walmart_order_details`.`status`='acknowledged' OR `walmart_order_details`.`status`='Partially Acknowledged') AND `shopify_order_id`!='' AND `walmart_order_details`.`merchant_id`=".$merchant_id;
        $orderAckCollection = Data::sqlRecords($query, "all", "select");
        $count=0;
        $a=0;
        if (!empty($orderAckCollection) && is_array($orderAckCollection)) 
        {
            foreach ($orderAckCollection as $key => $value) 
            {               
                $shopname = $value['username'];
                $token = $value['auth_key'];
                $shopify_order_id = $value['shopify_order_id'];
                
                $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);
                $shipmentResponse = $sc->call('GET', '/admin/orders/' . $shopify_order_id . '.json?fields=fulfillments');
                if(is_array($shipmentResponse) && isset($shipmentResponse['fulfillments']) && count($shipmentResponse['fulfillments'])>0) 
                {   
                    $response=array();
                   
                    $this->walmartHelper = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                    $orderdata = $this->walmartHelper->getOrder($value['purchase_order_id']);

                    $trackingnumber = "";
                    $carrier = "";
                    $status = "";
                    $orderdata1 = json_decode($orderdata,true);
                    $shipdata = $orderdata1['order']['orderLines'];
                    if (is_array($shipdata)) {
                        foreach ($shipdata as $key => $data) {
                            $status = $data[0]['orderLineStatuses']['orderLineStatus'][0]['status'];
                            if ($status == "Shipped") {
                                $trackingnumber = $data[0]['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['trackingNumber'];
                                $carrier = $data[0]['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['carrierName']['carrier'];
                            }
                        }
                    }
                    //print_r($status);die();
                    if ($status== "Shipped") {
                         $query="UPDATE `walmart_order_details` SET status='completed' WHERE purchase_order_id='".$value['purchase_order_id']."'";
                        $orderCollection = Data::sqlRecords($query, "one", "update");
                        
                    $count++;
                    }
                    else if ($status != "Shipped") {
                        //manual shipment start here.....raza
                       if (file_exists(\Yii::getAlias('@webroot').'/var/order/'.$value['merchant_id'].'/'.$value['purchase_order_id'].'/shipment.log')) {
                         $handle = fopen( \Yii::getAlias('@webroot').'/var/order/'.$value['merchant_id'].'/'.$value['purchase_order_id'].'/shipment.log','r');
                            $line = 0;
                            while (($buffer = fgets($handle)) !== FALSE) {
                               if ($line == 1) {
                                   
                                   break;
                               }   
                               $line++;
                            }
                           $data= json_decode(trim($buffer),true);
                           $ship = $this->actionCurlprocessfororder($data);
                           //print_r($ship."<br>");
                           $count++;
                            fclose($handle);
                        }
                        else{
                            $data=array();
                            $data['id'] = $value['shopify_order_id'];
                            $data['name'] = $value['shopify_order_name'];
                            $data['fulfillments'] = $shipmentResponse['fulfillments'];
                            $ship = $this->actionCurlprocessfororder($data);
                            $count++;
                        }

                            //manual shipment end
                    }
                    
                } 
                else{
                    $response=array();
                   
                   $this->walmartHelper = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                    $orderdata = $this->walmartHelper->getOrder($value['purchase_order_id']);

                    $trackingnumber = "";
                    $carrier = "";
                    $status = "";
                    $orderdata1 = json_decode($orderdata,true);
                    $shipdata = $orderdata1['order']['orderLines'];

                    if (is_array($shipdata)) {
                        //print_r($shipdata);die();
                        foreach ($shipdata as $key => $data) {
                            $status = $data[0]['orderLineStatuses']['orderLineStatus'][0]['status'];
                            if ($status == "Shipped") {
                                $trackingnumber = $data[0]['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['trackingNumber'];
                                $carrier = $data[0]['orderLineStatuses']['orderLineStatus'][0]['trackingInfo']['carrierName']['carrier'];
                            }
                        }
                    }
                    //print_r($status);die('gg');
                    if ($status== "Shipped") {
                         $query="UPDATE `walmart_order_details` SET status='completed' WHERE purchase_order_id='".$value['purchase_order_id']."'";
                        $orderCollection = Data::sqlRecords($query, "one", "update");
                        
                    $count++;
                    }
                } 
            }                           
        }
       Yii::$app->session->setFlash('success', $count.'  Orders shipped on walmart');
     

        return $this->redirect(['index']);
    }

    public function actionCancelbulkorder()
    {
        $query = "SELECT `walmart_order_details`.`id`,`walmart_order_details`.`merchant_id`,`purchase_order_id`,`consumer_id` ,`secret_key`,`consumer_channel_type_id`FROM `walmart_order_details`INNER JOIN `walmart_configuration` ON `walmart_order_details`.`merchant_id` = `walmart_configuration`.`merchant_id`WHERE (`walmart_order_details`.`status` = 'acknowledged' OR `walmart_order_details`.`status` = 'Partially Acknowledged')AND `walmart_order_details`.`sku` = 'TOOL-03-3T'AND `walmart_order_details`.`merchant_id` ='335'";
        $orderAckCollection = Data::sqlRecords($query, "all", "select");
        $count = 0;
        foreach ($orderAckCollection as $key => $value) 
        { 
             $config = array();
             $config['merchant_id'] = $value['merchant_id'];
             $config['consumer_id'] = $value['consumer_id'];
             $config['secret_key'] = $value['secret_key'];
             $config['consumer_channel_type_id'] = $value['consumer_channel_type_id'];
             $data = array();
             $data['pid'] = $value['purchase_order_id'];
             //print_r($data);die;
             //$cancel = $this->actionCancelOrder($config,$data);
              echo 'merchant_id: '.$value['merchant_id']."  =>  PurchaseID:  ".$value['purchase_order_id']."<br><br>";
                $count++;

        }
      echo "Total Inprogress Orders  :: ".$count;     
               
    }

    public function actionCheckstatus()
    {
        if($_GET['id']){
            $this->walmartHelper = new Walmartapi(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
            $orderdata = $this->walmartHelper->getOrder($_GET['id']);
            $shipdata = json_decode($orderdata,true);
            print_r($shipdata);die;

        }
        
    }

    public function getTestData(){
        $data = '{"id":"4572529740","email":"satyaprakash@cedcoss.com","created_at":"2016-11-07T03:57:54-05:00","updated_at":"2016-11-07T03:58:11-05:00","number":"405","note":"Walmart Marketplace-Integration","token":"0c2d235ced30b1ff1353c784edf23adc","gateway":"","test":"0","total_price":"8618.00","subtotal_price":"8618.00","total_weight":"0","total_tax":"0.00","taxes_included":"0","currency":"AUD","financial_status":"paid","confirmed":"1","total_discounts":"0.00","total_line_items_price":"8618.00","buyer_accepts_marketing":"0","name":"#1405","total_price_usd":"6616.12","processed_at":"2016-11-07T03:57:54-05:00","order_number":"1405","processing_method":"","source_name":"1456995","fulfillment_status":"fulfilled","tags":"walmart.com","contact_email":"satyaprakash@cedcoss.com","line_items":[{"id":"9122140940","variant_id":"21083941126","title":"New1 Engagement Rings","quantity":"1","price":"70.00","grams":"50","sku":"beauty_ring","variant_title":"steal","vendor":"Cedcommerce","fulfillment_service":"manual","product_id":"4211750342","requires_shipping":"1","taxable":"1","gift_card":"0","name":"New1 Engagement Rings - steal","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141004","variant_id":"13528400454","title":"Gold Ring Platinum new","quantity":"1","price":"444.00","grams":"0","sku":"20-LR8724-3278X","variant_title":"18K White Gold & 18K PG","vendor":"Engagement Ring","fulfillment_service":"manual","product_id":"4211751046","requires_shipping":"1","taxable":"1","gift_card":"0","name":"Gold Ring Platinum new - 18K White Gold & 18K PG","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141068","variant_id":"13528402502","title":"Jewelry gold(necklace)","quantity":"1","price":"5004.00","grams":"29484","sku":"21-LR8724-32505P-","variant_title":"18K White Gold & 18K PG","vendor":"Fashion Apparel","fulfillment_service":"manual","product_id":"4211751366","requires_shipping":"1","taxable":"1","gift_card":"0","name":"Jewelry gold(necklace) - 18K White Gold & 18K PG","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141132","variant_id":"13528404358","title":"Necklace - 2.01 ctw ghjk","quantity":"1","price":"3000.00","grams":"0","sku":"46-PD1647-30525W","variant_title":"18K White Gold & 22K PG","vendor":"Fashion Apparel","fulfillment_service":"manual","product_id":"4211751750","requires_shipping":"1","taxable":"1","gift_card":"0","name":"Necklace - 2.01 ctw ghjk - 18K White Gold & 22K PG","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141196","variant_id":"13528404934","title":"New Cotton Towel","quantity":"1","price":"100.00","grams":"2268","sku":"53-PD172","variant_title":"cotton","vendor":"Cedcommerce","fulfillment_service":"manual","product_id":"4211752006","requires_shipping":"1","taxable":"1","gift_card":"0","name":"New Cotton Towel - cotton","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"}],"billing_address":{"first_name":"Test","address1":"15 Brown ave Ext","phone":"8608181642","city":"STAFFORD SPRINGS","zip":"06076","province":"Connecticut","country":"United States","last_name":"Test","latitude":"41.9542632","longitude":"-72.3023022","name":"Test Test","country_code":"US","province_code":"CT"},"shipping_address":{"first_name":"Test","address1":"15 Brown ave Ext","phone":"8608181642","city":"STAFFORD SPRINGS","zip":"06076","province":"Connecticut","country":"United States","last_name":"Test","latitude":"41.9542632","longitude":"-72.3023022","name":"Test Test","country_code":"US","province_code":"CT"},"fulfillments":[{"id":"3808896588","order_id":"4572529740","status":"success","created_at":"2016-11-07T03:58:11-05:00","service":"manual","updated_at":"2016-11-07T03:58:11-05:00","tracking_company":"Other","tracking_number":"test","tracking_numbers":["test"],"tracking_url":"http:\/\/test","tracking_urls":["http:\/\/test"],"line_items":[{"id":"9122140940","variant_id":"21083941126","title":"New1 Engagement Rings","quantity":"1","price":"70.00","grams":"50","sku":"beauty_ring","variant_title":"steal","vendor":"Cedcommerce","fulfillment_service":"manual","product_id":"4211750342","requires_shipping":"1","taxable":"1","gift_card":"0","name":"New1 Engagement Rings - steal","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141004","variant_id":"13528400454","title":"Gold Ring Platinum new","quantity":"1","price":"444.00","grams":"0","sku":"20-LR8724-3278X","variant_title":"18K White Gold & 18K PG","vendor":"Engagement Ring","fulfillment_service":"manual","product_id":"4211751046","requires_shipping":"1","taxable":"1","gift_card":"0","name":"Gold Ring Platinum new - 18K White Gold & 18K PG","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141068","variant_id":"13528402502","title":"Jewelry gold(necklace)","quantity":"1","price":"5004.00","grams":"29484","sku":"21-LR8724-32505P-","variant_title":"18K White Gold & 18K PG","vendor":"Fashion Apparel","fulfillment_service":"manual","product_id":"4211751366","requires_shipping":"1","taxable":"1","gift_card":"0","name":"Jewelry gold(necklace) - 18K White Gold & 18K PG","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141132","variant_id":"13528404358","title":"Necklace - 2.01 ctw ghjk","quantity":"1","price":"3000.00","grams":"0","sku":"46-PD1647-30525W","variant_title":"18K White Gold & 22K PG","vendor":"Fashion Apparel","fulfillment_service":"manual","product_id":"4211751750","requires_shipping":"1","taxable":"1","gift_card":"0","name":"Necklace - 2.01 ctw ghjk - 18K White Gold & 22K PG","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"},{"id":"9122141196","variant_id":"13528404934","title":"New Cotton Towel","quantity":"1","price":"100.00","grams":"2268","sku":"53-PD172","variant_title":"cotton","vendor":"Cedcommerce","fulfillment_service":"manual","product_id":"4211752006","requires_shipping":"1","taxable":"1","gift_card":"0","name":"New Cotton Towel - cotton","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"}]}],"customer":{"id":"4964320396","email":"satyaprakash@cedcoss.com","accepts_marketing":"0","created_at":"2016-11-05T08:17:22-04:00","updated_at":"2016-11-07T03:57:54-05:00","first_name":"Test","last_name":"Test","orders_count":"6","state":"disabled","total_spent":"0.00","last_order_id":"4572529740","verified_email":"1","tax_exempt":"0","tags":"","last_order_name":"#1405","default_address":{"id":"5292595084","first_name":"Test","last_name":"Test","address1":"15 Brown ave Ext","city":"STAFFORD SPRINGS","province":"Connecticut","country":"United States","zip":"06076","phone":"8608181642","name":"Test Test","province_code":"CT","country_code":"US","country_name":"United States","default":"1"}},"shopName":"ced-jet.myshopify.com"}';
        return json_decode($data,true);
    }

    public function getShipingResponseData($data=false,$order=false,$test = true){
        if($test){
            $data1 = '{"order":{"purchaseOrderId":"3576798843216","customerOrderId":"5621663399206","customerEmailId":"mparthasarathy@walmartlabs.com","orderDate":"2016-10-18T06:45:05.000Z","shippingInfo":{"phone":"6505151012","estimatedDeliveryDate":"2016-11-02T06:00:00.000Z","estimatedShipDate":"2016-10-19T06:00:00.000Z","methodCode":"Standard","postalAddress":{"name":"DonotShip WalmartTestOrder","address1":"860 West California Ave","address2":"Cube 860.2.127 ","city":"Sunnyvale","state":"CA","postalCode":"94086","country":"USA","addressType":"RESIDENTIAL"}},"orderLines":{"orderLine":{"lineNumber":"1","item":{"productName":"Toms Classics Round Toe Canvas Loafer","sku":"78370"},"charges":{"charge":{"chargeType":"PRODUCT","chargeName":"ItemPrice","chargeAmount":{"currency":"USD","amount":"58.00"}}},"orderLineQuantity":{"unitOfMeasurement":"EACH","amount":"1"},"statusDate":"2016-10-18T08:03:30.000Z","orderLineStatuses":{"orderLineStatus":{"status":"Shipped","statusQuantity":{"unitOfMeasurement":"EACH","amount":"1"},"trackingInfo":{"shipDateTime":"2016-10-03T00:57:58.000Z","carrierName":{"carrier":"FedEx"},"methodCode":"Standard","trackingNumber":"351313135512","trackingURL":"http:\/\/www.fedex.com\/Tracking?tracknumbers=351313135512&action=track"}}}}}}}';
            return $data1;
        }
        else{
            $orderLines = [];
            $count = 1;
            foreach($data['line_items'] as $item){
                $status = 'Created'; 
                if(isset($item['fulfillment_status']) && $item['fulfillment_status']=='fulfilled')
                {
                    $status = 'Shipped';
                }
                $orderLines[] = [
                                                    'lineNumber' => $count++,
                                                    'item' => 
                                                        [
                                                            'productName' => $item['title'],
                                                            'sku' => $item['sku']
                                                        ],

                                                    'charges' => 
                                                        [
                                                            'charge' => 
                                                                [
                                                                    'chargeType' => 'PRODUCT',
                                                                    'chargeName' => 'ItemPrice',
                                                                    'chargeAmount' => 
                                                                        [
                                                                            'currency' => 'USD',
                                                                            'amount' => $item['price']
                                                                        ],
                                                                    'tax' => [
                                                                            'taxName' => 'Tax1',
                                                                            'taxAmount' => 
                                                                            [
                                                                                'currency' => 'USD',
                                                                                'amount' => 5
                                                                            ],
                                                                        ],

                                                                ],

                                                        ],

                                                    'orderLineQuantity' => 
                                                        [
                                                            'unitOfMeasurement' => 'EACH',
                                                            'amount' => 1
                                                        ],

                                                    'statusDate' =>$data['created_at'],
                                                    'orderLineStatuses' => 
                                                        [
                                                            'orderLineStatus' => 
                                                                [
                                                                    'status' => $status,
                                                                    'statusQuantity' => 
                                                                        [
                                                                            'unitOfMeasurement' => 'EACH',
                                                                            'amount' => 1
                                                                        ],

                                                                    'trackingInfo' => 
                                                                        [
                                                                            'shipDateTime' => $data['created_at'],
                                                                            'carrierName' => 
                                                                                [
                                                                                    'carrier' => $data['fulfillments'][0]['tracking_company'],
                                                                                ],

                                                                            'methodCode' => 'Standard',
                                                                            'trackingNumber' => $data['fulfillments'][0]['tracking_number'],
                                                                            'trackingURL' => $data['fulfillments'][0]['tracking_url'],
                                                                        ],

                                                                ],

                                                        ],

                                                ];
            }
            $data1 = 
                        [
                            'order' => [
                                    'purchaseOrderId' => $order->purchase_order_id,
                                    'customerOrderId' => $data['fulfillments'][0]['order_id'],
                                    'customerEmailId' => $data['email'],
                                    'orderDate' => $data['created_at'],
                                    'shippingInfo' => 
                                        [
                                            'phone' => $data['shipping_address']['phone'],
                                            'estimatedDeliveryDate' => $data['fulfillments'],
                                            'estimatedShipDate' => $data['created_at'],
                                            'methodCode' => 'OneDay',
                                            'postalAddress' => 
                                                [
                                                    'name' => $data['shipping_address']['first_name'],
                                                    'address1' => $data['shipping_address']['address1'],
                                                    'address2' => $data['shipping_address']['address1'],
                                                    'city' => $data['shipping_address']['city'],
                                                    'state' => $data['shipping_address']['province'],
                                                    'postalCode' => $data['shipping_address']['zip'],
                                                    'country' => $data['shipping_address']['country'],
                                                    'addressType' => 'RESIDENTIAL'
                                                ],

                                        ],

                                    'orderLines' => 
                                        [
                                            'orderLine' => $orderLines

                                        ],

                                ],

                        ];
                        return json_encode($data1);
        }   
    }
    /*Convert Weight lbs to gram*/
    public static function convertWeightlbsToGram($weight){
        if(!empty($weight)){
            $newWeight = (float)($weight*453.592);
            return $newWeight;
        }
    }
    /*Convert Weight lbs to gram*/
    public static function getWeightbyId($id,$merchant_id){
        $query = "SELECT `weight` FROM `jet_product` WHERE `merchant_id`='".$merchant_id."' AND `id`='".$id."'";
        $data = Data::sqlRecords($query,'one','select');
        if(!empty($data)){
             return $data;
        }
    }

    public function actionViewOrder()
    {
        if(Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $PurchaseOrderId = "4577682674798";
        //$PurchaseOrderId = $_POST['purchase_order_id'];
        $response = "";
        $responseOrders = array();
        $this->walmartHelper = new Walmartapi(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
        $orderdata = $this->walmartHelper->getOrder($PurchaseOrderId);
        //$response = $this->walmartHelper->getRequestorder('v3/orders/'.$PurchaseOrderId);
        $responseOrders=json_decode($orderdata,true);
        $html=$this->render('vieworder', [
            //'model' => $responseOrders['list']['elements']['order'][0],
            'model' => $responseOrders['order'],
        ],true);
        return $html;
    }
}