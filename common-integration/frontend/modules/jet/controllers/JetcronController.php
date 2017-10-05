<?php
namespace frontend\modules\jet\controllers;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Jetrepricing;
use frontend\modules\jet\components\Mail;
use frontend\modules\jet\components\Orderdata;
use frontend\modules\jet\components\Sendmail;
use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\controllers\JetorderimporterrorController;
use frontend\modules\jet\models\JetCronSchedule;
use Yii;
use yii\web\Controller;

/**
* Test controller
*/
class JetcronController extends Controller 
{
  public function actionDynamicprice() 
  {  
    ob_start ();
    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();

    $cronData="";
    $cron_array = $status_array = $error_array = [];      
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'dynamic_price'])->one();
    
    if($cronData && trim($cronData['cron_data'])!=""){      
      $cron_array=json_decode($cronData['cron_data'],true);
    }
    else
    {        
      //$cron_array = Jetappdetails::getConfig();
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    }     
    $countArr=0;
    $status_array['total_count']=count($cron_array);
    
    if(is_array($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $key=>$merchant)
      {
        try
        {
          if(!in_array($merchant['db'], $dbList)) {
            unset($cron_array[$key]);
            continue;
          }
          Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
          Yii::$app->merchant_id = $merchant['merchant_id'];
          Yii::$app->shop_name = $merchant['shop_name'];
                    
          $merchant_id=Yii::$app->merchant_id;
          $jetappDetailsObj = new Jetappdetails();
          $jetConfig = $jetappDetailsObj->getConfigurationDetails($merchant_id);
          if (!empty($jetConfig))
          {
            //$merchant_id=$key;            
            $response = $jetHelper = $allSkus = [];
            $fullfillmentnodeid = $jetConfig['fullfilment_node_id'];
            $api_host = $jetConfig['api_host'];
            $api_user = $jetConfig['api_user'];
            $api_password = $jetConfig['api_password'];
            $jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
            $countArr++;
            $count=0;
            unset($cron_array[$key]);

            $path=Yii::getAlias('@webroot').'/var/jet/product/dynamic_price/'.$merchant_id;
            if(!file_exists($path)){
              mkdir($path,0775, true);
            } 
            $filenameOrig = $path.'/dynamicPriceUpdate-'.time().'.log';                    
            $fileOrig = fopen($filenameOrig,'w'); 

            $sql = "SELECT `sku`,`min_price`,`current_price`,`max_price`,`bid_price`,`value` FROM `jet_dynamic_price` INNER JOIN jet_config on jet_dynamic_price.merchant_id=jet_config.merchant_id WHERE jet_dynamic_price.`merchant_id`='{$merchant_id}' and jet_config.data='dynamic_repricing' AND jet_config.value='Yes'";  

            $allSkus = Data::sqlRecords($sql,"all","select");                   
            if (!empty($allSkus)) 
            {
              foreach ($allSkus as $key => $value) 
              {
                $count++;
                $skuDetails = $priceArr = [];
                $price = 0.00;
                
                $sku = trim($value['sku']);
                $minPrice = trim($value['min_price']);
                $currentPrice = trim($value['current_price']);
                $maxPrice = trim($value['max_price']);
                $bidPrice = trim($value['bid_price']);              
                          
                $skuDetails = $jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'/salesdata',$merchant_id);
                $skuDetails = json_decode($skuDetails,true);
                
                if (isset($skuDetails['my_best_offer'],$skuDetails['best_marketplace_offer'])) 
                {
                  $price = Jetappdetails::calculateDynamicPrice($merchant_id,$currentPrice,$minPrice,$maxPrice,$bidPrice,$skuDetails['my_best_offer'][0],$skuDetails['best_marketplace_offer'][0]);  
                }
                if ($price >0.00) 
                {
                  $priceArr[$sku]['price']=(float)$price;
                  $node['fulfillment_node_id']=$fullfillmentnodeid;
                  $node['fulfillment_node_price']=(float)$price;
                  $priceArr[$sku]['fulfillment_nodes'][]=$node; 
                  
                  fwrite($fileOrig,PHP_EOL."-------------------------".$sku."----------------------------------");
                  fwrite($fileOrig,PHP_EOL."NEW PRICE ARRAY".PHP_EOL.json_encode($priceArr));
                  $response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($priceArr[$sku]),$merchant_id);
                  fwrite($fileOrig,PHP_EOL."RESPONSE FROM JET".PHP_EOL.json_encode($response));
                }
                else
                {
                  fwrite($fileOrig,PHP_EOL."--------this =>".$sku." HAVE NO COMPETITOR-----------------------");
                  continue;
                }               
              }  
            }            
            $status_array[$merchant_id]=$count;
          }
          
          if($countArr>=500){
            fclose($fileOrig);            
            break;
          }                        
        }
        catch (Exception $e)
        {
          unset($cron_array[$k]);
          continue;
        }
      }
    } 
    if(count($cron_array)==0)
      $cronData->cron_data="";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);     
  } 
  public function actionRemovefailedorders()
  { 
    ob_start ();
    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();
    $cron_array = $cronData = [];  
    $cronData = JetCronSchedule::find()->where(['cron_name'=>'remove_failed_order'])->one();
    if($cronData && trim($cronData['cron_data'])!="")
    {
        $cron_array = json_decode($cronData['cron_data'],true);
    }
    else
    {
      //  $cron_array = Jetappdetails::getConfig();
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    }
    if (!empty($cron_array)) 
    {
      foreach ($cron_array as $key => $merchant) 
      {
        if(!in_array($merchant['db'], $dbList)) {
          unset($cron_array[$key]);
          continue;
        }
        Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
        Yii::$app->merchant_id = $merchant['merchant_id'];
        Yii::$app->shop_name = $merchant['shop_name'];
                  
        $merchant_id=Yii::$app->merchant_id;
        $model = $cron_array = $jetConfig = $failedOrders =  [];
        $jetappDetailsObj = new Jetappdetails();
        $jetConfig = $jetappDetailsObj->getConfigurationDetails($merchant_id);
        if (!empty($jetConfig))
        {
          $fullfilment_node_id = $jetConfig['fullfilment_node_id'];
          $api_host = "https://merchant-api.jet.com/api";
          $api_user = $jetConfig['api_user'];
          $api_password = $jetConfig['api_password'];
          $jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);

          $failedOrders = Data::sqlRecords("SELECT `merchant_order_id`,`reference_order_id` FROM `jet_order_import_error` WHERE `merchant_id`='".$merchant_id."'  ","all","select");
          
          foreach ($failedOrders as $key => $value) 
          { 
            $path = Yii::getAlias('@webroot').'/var/jet/order/'.$merchant_id.'/'.$value['reference_order_id'];
            if(!file_exists($path)){
                mkdir($path,0775, true);
            }
            $handle=fopen($path.'/ordercancel.log','a+');

            $orderdata = array();
            $orderdata = $jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$value['merchant_order_id'],$merchant_id);
            fwrite($handle,PHP_EOL." ORDER RESPONSE DATA FOR MERCHANT_ORDER_ID =>".$value['merchant_order_id'].PHP_EOL.$orderdata.PHP_EOL);
            $orderdata = json_decode($orderdata,true);
            
            if ( ($orderdata['status']=="complete") || ($orderdata['status']=="acknowledged") ) 
            {
              $removeQuery = "DELETE FROM `jet_order_import_error` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$value['merchant_order_id']."' ";
              fwrite($handle,PHP_EOL." ORDER ALREADY ACKNOWLEDGED/COMPLETED ON JET (REMOVING FROM FAILED ORDER TABLE-> QUERY) ".PHP_EOL.$removeQuery.PHP_EOL);
              Data::sqlRecords($removeQuery,null,"update");
            }
            elseif (isset($orderdata['order_ready_date']))              
            {
              $cancelOrderCheck = array();
              $cancelOrderCheck = Data::sqlRecords("SELECT value FROM `jet_config` WHERE `merchant_id`='{$merchant_id}' and `data`='cancel_order'",'one','select');
              if ($cancelOrderCheck['value']!='Yes') 
              {
                return;
              }
              else
              {
                /* $datetime1 = new \DateTime($orderdata['order_ready_date']);
                $datetime2 = new \DateTime(date("Y-m-d\TH:i:s\Z"));
                $difference = $datetime1->diff($datetime2);
                fwrite($handle,PHP_EOL." DATE INTERVAL  ".PHP_EOL.json_encode($difference).PHP_EOL);
                if ( (($difference->y)>0) || (($difference->m)>0) || (($difference->d)>0) || (($difference->h)>2) ) 
                { */
                  $postData = ['jetHelper'=>$jetHelper,'merchant_id'=>$merchant_id,'merchant_order_id'=>$orderdata['merchant_order_id'],'reference_order_id'=>$orderdata['reference_order_id']];
                  fwrite($handle,PHP_EOL." CANCELLING THE READY STATE ORDERS(MORE THAN 2 HOURS)=>DATA  ".PHP_EOL.json_encode($postData).PHP_EOL);
                  $obj = new JetorderimporterrorController(Yii::$app->controller->id,'');
                  $obj->actionCancel($postData);  
                //}
              }            
            }
            fclose($handle);
          }
        }
        //$merchant_id = $key;
        /*
          if ($merchant_id !=14) {
            continue;
          }
        */
        
      } 
    }   
  }    

  public function actionFetchorder()
  {
    ob_start ();
    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();

    $cronData="";
    $responseOrder = $cron_array = $status_array = $mailData = $error_array = [];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'fetch_order'])->one();
    if($cronData && trim($cronData['cron_data'])!=""){
      $cron_array=json_decode($cronData['cron_data'],true);
    }
    else
    {
      //$cron_array = Jetappdetails::getConfig();     
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    }
    $mailSource = 'php';
    
    $start = $count = $countArr = 0;
    $status_array['total_count']=count($cron_array);
    
    if(!empty($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $key=>$merchant)
      {
        try
        {
          if(!in_array($merchant['db'], $dbList)) {
            unset($cron_array[$key]);
            continue;
          }
          Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
          Yii::$app->merchant_id = $merchant['merchant_id'];
          Yii::$app->shop_name = $merchant['shop_name'];
                    
          $merchant_id=Yii::$app->merchant_id;
          $model = $cron_array = $jetConfig = [];
          $jetappDetailsObj = new Jetappdetails();
          $jetConfig = $jetappDetailsObj->getConfigurationDetails($merchant_id);
          if (!empty($jetConfig))
          {
          	$countOrder = $errorCount = 0;
          	$orderdata = $jetHelper = "";
          	$response = $error_array = $mailData = [];
          	$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
          	$api_user=$jetConfig['api_user'];
          	$api_password=$jetConfig['api_password'];
          	$merchantEmail=isset($jetConfig['merchant_email'])?$jetConfig['merchant_email']:false;
          	$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
          	$status_array[$merchant_id]=$countOrder;
          	
          	$responseOrder[$merchant_id]= Orderdata::createOrder($jetHelper,$merchant_id,$merchantEmail,$mailData,$error_array);
          	//create order import error
          	
          	if(is_array($error_array) && count($error_array)>0)
          	{
          		$errorFlag=false;
          		$message1="";
          		foreach ($error_array as $order_error)
          		{
          			$isAutoCancel = $post = $result = [];
          			Yii::$app->merchant_id = $order_error['merchant_id'];
          			$post['merchant_id']=$order_error['merchant_id'];
          			$post['merchant_order_id']=$order_error['merchant_order_id'];
          			$post['jetHelper']= $jetHelper;
          			$isAutoCancel = Data::sqlRecords("SELECT `value`  FROM `jet_config` WHERE `merchant_id` = {$merchant_id} AND `data` ='cancel_order'",'one','select');
          			if (!empty($isAutoCancel) && $isAutoCancel['value']=='Yes') {
          				$obj = new JetorderimporterrorController(Yii::$app->controller->id,'');
          				$obj->actionCancel($post);
          			}
          	
          			$result = Data::sqlRecords("SELECT `merchant_order_id` FROM `jet_order_import_error` WHERE merchant_order_id='".$order_error['merchant_order_id']."'",'one','select');
          			if(!empty($result))
          			{
          				continue;
          			}
          			else
          			{
          				$mailData1 = [];
          				if (!empty($isAutoCancel) && $isAutoCancel['value']=='Yes') {
          				{
          					continue;
          				}
          	
          				$mailData1['merchant_order_id'] = $order_error['merchant_order_id'];
          				$mailData1['subject'] = 'Something wrong in order import (JET) ';
          				$mailData1['error'] = $order_error['reason'];
          				$mailer = new Mail($mailData1,'email/order-error.html',$mailSource,true);
          				$mailer->sendMail();
          				 
          				$sql='INSERT INTO `jet_order_import_error`(`merchant_order_id`,`reference_order_id`,`merchant_id`,`reason`,`created_at`)
            VALUES("'.$order_error['merchant_order_id'].'","'.$order_error['reference_order_id'].'","'.$order_error['merchant_id'].'","'.addslashes($order_error['reason']).'","'.$order_error['created_at'].'")';
          				try{
          					$errorCount++;
          					Data::sqlRecords($sql,null,'insert');
          				}catch(Exception $e)
          				{
          					return $e->getMessage();
          				}
          			}
          		}
          		unset($sql,$result,$error_array);
          	}
          }
                   
          unset($cron_array[$key]);
          $countArr++;

          if($countArr>=150)
            break;
          }
        }
        catch (Exception $e)
        {
          unset($cron_array[$key]);
          continue;
        }
      }
    }
    
    if(count($cron_array)==0)
      $cronData->cron_data=" ";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);    
    unset($cronData,$status_array);
  }
  public function actionSyncorder()
  {
    ob_start ();
    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();

    $cronData="";
    $responseOrder = $cron_array = $status_array = $mailData = $error_array = [];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'fetch_order'])->one();
    if($cronData && trim($cronData['cron_data'])!=""){
      $cron_array=json_decode($cronData['cron_data'],true);
    }
    else
    {
      //$cron_array = Jetappdetails::getConfig();     
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    }
    $mailSource = 'php';
    
    $start = $count = $countArr = 0;
    $status_array['total_count']=count($cron_array);
    
    if(!empty($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $key=>$merchant)
      {
        try
        {
          if(!in_array($merchant['db'], $dbList)) {
            unset($cron_array[$key]);
            continue;
          }
          Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
          Yii::$app->merchant_id = $merchant['merchant_id'];
          Yii::$app->shop_name = $merchant['shop_name'];
                    
          $merchant_id=Yii::$app->merchant_id;
          $model = $cron_array = $jetConfig = [];

          $jetOrderData = [];
          
          $query="SELECT `merchant_id`,`merchant_order_id`,`order_data`,`username`,`auth_key`  FROM `jet_order_detail` INNER JOIN `user` ON `jet_order_detail`.`merchant_id`=  `user`.`id`  WHERE jet_order_detail.`merchant_id`='{$merchant_id}' AND jet_order_detail.`status` ='acknowledged' AND `shopify_order_name`=''";
                    
          $jetOrderData = Data::sqlRecords($query,'all','select');
          
          if(!empty($jetOrderData) && count($jetOrderData)>0)
          {
            foreach ($jetOrderData as $order_value)
            {
              $result = $error_array = $configSetting = [];
              $merchant_id = $token = $shopname = "";

              $merchant_id = $order_value['merchant_id']; 
              $token=$order_value['auth_key'];
              $shopname = $order_value['username'];
              $configSetting = Jetproductinfo::getConfigSettings($merchant_id);
              $sc = new ShopifyClientHelper($shopname, $token, PUBLIC_KEY, PRIVATE_KEY);
              
              $result = json_decode($order_value['order_data'],true);  
              
              Orderdata::syncJetOrder($sc,$configSetting,$result,$merchant_id,$countOrder);
            }
          } 
          unset($cron_array[$key]);
          $countArr++;

          if($countArr>=50)
            break;         
        }catch(Exception $e){
          unset($cron_array[$key]);
          continue;
        }
      }
    } 
    if(count($cron_array)==0)
      $cronData->cron_data=" ";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);    
    unset($cronData,$status_array);   
  }
  public function actionShiporder($cron=false)
  {
    ob_start ();
    date_default_timezone_set("Asia/Kolkata");

    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();
    $cronData="";
    $responseOrder = $cron_array = $status_array = $mailData = $error_array = [];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'ship_order'])->one();
    if($cronData && trim($cronData['cron_data'])!="")
      $cron_array=json_decode($cronData['cron_data'],true);    
    else
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    
    $status_array['total_count']=count($cron_array);

    $flag = $countShip = $countArr = 0;

    $query = "";
    $orderAckCollection = [];
    $oderDataObj = new Orderdata();
      
    if(!$cron)
    {
      $merchant_id = Yii::$app->user->identity->id;
      $oderDataObj->processOrderShipment($merchant_id,$countShip);
      if($countShip>0)
        Yii::$app->session->setFlash('success',$countShip. "Order(s) successfully fulfilled on jet");
      else
        Yii::$app->session->setFlash('success',"All orders are already completed on jet");
      return $this->redirect(Yii::getAlias('@webjeturl').'/jetorderdetail/index');
    }
    elseif(!empty($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $key=>$merchant)
      {
        try
        {
          if(!in_array($merchant['db'], $dbList)) {
            unset($cron_array[$key]);
            continue;
          }
          Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
          Yii::$app->merchant_id = $merchant['merchant_id'];
          Yii::$app->shop_name = $merchant['shop_name'];
                    
          $merchant_id=Yii::$app->merchant_id;
          $oderDataObj->processOrderShipment($merchant_id,$countShip);
          unset($cron_array[$key]);
          $countArr++;

          if($countArr>=150)
            break;
        }
        catch (Exception $e)
        {
          unset($cron_array[$key]);
          continue;
        }
      }
    }                
  }// Get Shipment function close

  public function actionUpdaterecurringpaymentstatus()
  {
    ob_start ();
    date_default_timezone_set('Asia/Kolkata');

    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();

    $cronData="";
    $responseOrder = $cron_array = $status_array = $mailData = $error_array = [];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'sync_payment_status'])->one();
    if($cronData && trim($cronData['cron_data'])!="")
      $cron_array=json_decode($cronData['cron_data'],true);    
    else    
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    
    $start = $count = $countArr = 0;
    $status_array['total_count']=count($cron_array);
    
    if(!empty($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $key=>$merchant)
      {
        try
        {
          if(!in_array($merchant['db'], $dbList)) {
            unset($cron_array[$key]);
            continue;
          }
          Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
          Yii::$app->merchant_id = $merchant['merchant_id'];
          Yii::$app->shop_name = $merchant['shop_name'];
                    
          $merchant_id=Yii::$app->merchant_id;

          $clientDetails = $response = []; 
          $filenameOrig = $fileOrig = "";

          $jetappDetailsObj = new Jetappdetails();
          $sc = $jetappDetailsObj->getShpoifyClientObj($merchant_id);

          $CurDate= date("Y-m-d H:i:s");
          $nextDate =date('Y-m-d H:i:s',strtotime('+3 days', strtotime(date('Y-m-d H:i:s'))));
          $finalExpiredate =date('Y-m-d H:i:s',strtotime('+30 days', strtotime(date('Y-m-d H:i:s'))));
           
          $sqlrecurring = "SELECT jrp.`id`,jrp.`status`,jrp.`merchant_id`, `plan_type` , jsd.`install_status`  FROM `jet_recurring_payment` jrp LEFT JOIN `user` ON jrp.`merchant_id`=  `user`.`id` LEFT JOIN `jet_shop_details` jsd ON jsd.`merchant_id`=  `user`.`id` WHERE  jsd.merchant_id='{$merchant_id}' AND jsd.`expired_on` BETWEEN '{$CurDate}' AND '{$nextDate}'";
          $clientDetails =  Data::sqlRecords($sqlrecurring,'all','select');
          $dir = Yii::getAlias('@webroot').'/var/jet/recurringPayment/cron/'.date('d-m-Y');
          if (!file_exists($dir))
          {
            mkdir($dir,0775, true);
          }
          
          if (!empty($clientDetails)) 
          {
            foreach ($clientDetails as $key => $value) 
            {
              $merchant_id = $value['merchant_id'];
              $planType = $value['plan_type'];             
              $payment_id = $value['id'];
              
              $filenameOrig=$dir.'/'.$merchant_id.'.log';
          
              $fileOrig=fopen($filenameOrig,'a+');
              if ($value['install_status']=='0')
              {
                $updatePaymentQuery="UPDATE `jet_recurring_payment` SET `status` = 'cancelled' WHERE `merchant_id`='".$merchant_id."' AND `jet_recurring_payment`.`id` ='".$payment_id."'";
                fwrite($fileOrig,PHP_EOL."CHANGING STATUS TO CANCELLED (APP UN-INSTALLED) [QUERY]".PHP_EOL.$updatePaymentQuery.PHP_EOL);
                Data::sqlRecords($updatePaymentQuery,null,'update');
              }
              else
              {                              
                if ($planType=='Recurring Plan (Monthly)'){
                    $response=$sc->call('GET','/admin/recurring_application_charges/'.$payment_id.'.json');
                }elseif ( ($planType=='Recurring Plan (Yearly)') || ($planType=='Recurring Plan (Half Yearly)')){
                    $response=$sc->call('GET','/admin/application_charges/'.$payment_id.'.json');
                }
                if (isset($response) && isset($response['status']) && $response['status']=='cancelled')
                {
                  $updatePaymentQuery="UPDATE `jet_recurring_payment` SET `status` = '".$response['status']."' WHERE `merchant_id`='".$merchant_id."' AND `jet_recurring_payment`.`id` ='".$payment_id."'";
                  fwrite($fileOrig,PHP_EOL."CHANGING STATUS TO CANCELLED (APP INSTALLED) [QUERY]".PHP_EOL.$updatePaymentQuery.PHP_EOL);
                  Data::sqlRecords($updatePaymentQuery,null,'update');
                  $updatePlanQuery="UPDATE `jet_shop_details` SET `purchase_status` = 'License Expired' WHERE `merchant_id` ='".$merchant_id."' ";
                  fwrite($fileOrig,PHP_EOL."CHANGING PURCHASE STATUS TO NOT PURCHASE (APP INSTALLED) [QUERY]".PHP_EOL.$updatePlanQuery.PHP_EOL);
                  Data::sqlRecords($updatePlanQuery,null,'update');           
                }elseif (!empty($response) && isset($response['status']) && $response['status']=='active' && $planType=='Recurring Plan (Monthly)')
                {            
                  $updatePlanQuery="UPDATE `jet_shop_details` SET `expired_on`='".$finalExpiredate."' , `purchase_status`='Purchased' WHERE `merchant_id` ='".$merchant_id."' ";
                  fwrite($fileOrig,PHP_EOL."EXTENDING EXPIRE DATE (APP INSTALLED) [QUERY]".PHP_EOL.$updatePlanQuery.PHP_EOL);
                  Data::sqlRecords($updatePlanQuery,null,'update');                       
                }               
                fclose($fileOrig);
              }       
            }       
          }

          unset($cron_array[$key]);
          $countArr++;

          if($countArr>=150)
            break;       
        }catch (Exception $e)
        {
          unset($cron_array[$key]);
          continue;
        }
      }
    }
    if(count($cron_array)==0)
      $cronData->cron_data=" ";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);    
    unset($cronData,$status_array);               
  } 

  public function actionUpdaterefundstatus()
  {
    ob_start ();
    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();

    $cronData=$status="";
    $cron_array=$status_array=[];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'retund_status'])->one();
    if($cronData && trim($cronData['cron_data'])!=""){
      $cron_array=json_decode($cronData['cron_data'],true);
    }
    else
    {
      //$cron_array = Jetappdetails::getConfig();
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    }    
    $countArr=0;
    $status_array['total_count']=count($cron_array);
    foreach($cron_array as $key=>$merchant)
    {      
      try
      {
        if(!in_array($merchant['db'], $dbList)) {
          unset($cron_array[$key]);
          continue;
        }
        Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
        Yii::$app->merchant_id = $merchant['merchant_id'];
        Yii::$app->shop_name = $merchant['shop_name'];
                  
        $merchant_id=Yii::$app->merchant_id;

        //$merchant_id=$key;
        $jetappDetailsObj = new Jetappdetails();
        $jetConfig = $jetappDetailsObj->getConfigurationDetails($merchant_id);
        if (!empty($jetConfig))
        {
          $count=0;
          $fullfillmentnodeid=$api_host=$api_user=$api_password=$jetHelper="";
          $fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
          $api_host=$jetConfig['api_host'];
          $api_user=$jetConfig['api_user'];
          $api_password=$jetConfig['api_password'];
          $jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
          $countArr++;
          unset($cron_array[$key]);
          $result= [];
          $query="SELECT `refund_id` FROM `jet_refund` WHERE merchant_id='".$merchant_id."' AND refund_status='created'";
          $result = Data::sqlRecords($query,'all','select');
          
          if(!empty($result))
          {
            foreach($result as $res)
            {
              $refundid=$responsedata=$data="";
              $refundid=$res['refund_id'];
              
              $data=$jetHelper->CGetRequest('/refunds/state/'.$refundid,$merchant_id,$status);
              if($data==false)
                continue;
              $responsedata=json_decode($data,true);
              if( ($status== 200) && isset($responsedata['refund_status']) && $responsedata['refund_status']!='created')
              {
                $count++;
                
                $updateResult="";
                $query="UPDATE `jet_refund` SET refund_status='".addslashes($responsedata['refund_status'])."' where refund_id='".$refundid."'";
                Data::sqlRecords($query,null,'update');
              }
            }     
          }
        }
        
        $status_array[$merchant_id]=$count;
        if($countArr>=20)
          break;
      }
      catch(Exception $e)
      {
        if(array_key_exists($key,$cron_array)){
          unset($cron_array[$key]);
        }
        $status_array[$key]['error']=$e->getMessage();
        continue;
      }
    }
    if(count($cron_array)==0)
      $cronData->cron_data="";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);
  } 
  public function actionCreatejetreturn()
  {
    ob_start ();
    $connection = Yii::$app->get(Yii::$app->getBaseDb());
    $dbList = Yii::$app->getDbList();

    $cronData="";
    $cron_array=$status_array=[];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'fetch_return'])->one();
    if($cronData && trim($cronData['cron_data'])!=""){
      $cron_array=json_decode($cronData['cron_data'],true);
    }
    else
    {
      // $cron_array = Jetappdetails::getConfig(); 
      $cron_array = $connection->createCommand('SELECT * FROM `merchant_db`')->queryAll();
    }
   
    $countArr=0;
    $status_array['total_count']=count($cron_array);
    foreach($cron_array as $key=>$merchant)
    {
      try
      {
        if(!in_array($merchant['db'], $dbList)) {
          unset($cron_array[$key]);
          continue;
        }
        Yii::$app->dbConnection = Yii::$app->get($merchant['db']);
        Yii::$app->merchant_id = $merchant['merchant_id'];
        Yii::$app->shop_name = $merchant['shop_name'];
                  
        $merchant_id=Yii::$app->merchant_id;
        $model = $cron_array = $jetConfig = [];
        $jetappDetailsObj = new Jetappdetails();
        $jetConfig = $jetappDetailsObj->getConfigurationDetails($merchant_id);
        if (!empty($jetConfig))
        {
          $count=0;
          $merchant_id=$api_host=$api_user=$api_password=$jetHelper=$fullfillmentnodeid="";
          $merchant_id=$key;
                  
          $fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
          $api_host=$jetConfig['api_host'];
          $api_user=$jetConfig['api_user'];
          $api_password=$jetConfig['api_password'];
          $jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
          $countArr++;
          unset($cron_array[$key]);
          $data="";
          $response="";
          $data = $jetHelper->CGetRequest('/returns/created',$merchant_id);
          if($data==false)
            continue;
          $response  = json_decode($data);
          $response=$response->return_urls;
          $count=0;
          if(!empty($response) && count($response)>0)
          {
            foreach($response as $res)
            {
              $arr=$resultdata=[];
              $arr=explode("/",$res);
              $returnid="";
              $returnid=$arr[3];
              
              $query="SELECT `returnid` FROM `jet_return` WHERE merchant_id='".$merchant_id."' AND returnid='".$returnid."'";
              $resultdata = Data::sqlRecords($query,'one','select');
              
              if(empty($resultdata))
              {
                $returndetails="";
                $returndetails = $jetHelper->CGetRequest(rawurlencode($res),$merchant_id);
                if($returndetails)
                {
                  $return = $order = [];
                  $return = json_decode($returndetails,true);
                  $return['timestamp']=date('d-m-Y H:i:s');
                  
                  $query="SELECT `merchant_order_id` FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND merchant_order_id='".$return['merchant_order_id']."'";
                  $order = Data::sqlRecords($query,'one','select');
                  if(!empty($order))
                  {
                    $count++;
                    $query='INSERT INTO `jet_return`
                              (
                                `returnid`,
                                `order_reference_id`,
                                `merchant_id`,
                                `status`,
                                `return_data`
                              )
                            VALUES(
                                "'.$returnid.'",
                                "'.$return['reference_order_id'].'",
                                "'.$merchant_id.'",
                                "created",
                              "'.addslashes(json_encode($return)).'"
                              )';
                    Data::sqlRecords($query,null,'insert');
                  }
                  else
                    continue;
                }
              }
            }
          }
          $status_array[$merchant_id]=$count;
        }
        
        if($countArr>=20)
          break;
      }
      catch(Exception $e)
      {
        if(array_key_exists($key,$cron_array)){
          unset($cron_array[$key]);
        }
        $status_array[$merchant_id]['error']=$e->getMessage();
        continue;
      }
    }
    if(count($cron_array)==0)
      $cronData->cron_data="";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);
  }
  /*public function actionUpdatejetproductstatus()
  {        
    $cronData="";
    $cron_array=$status_array=[];
    $cronData=JetCronSchedule::find()->where(['cron_name'=>'product_status'])->one();
    if($cronData && trim($cronData['cron_data'])!=""){
      $cron_array=json_decode($cronData['cron_data'],true);
    }
    else
    {
      $cron_array = Jetappdetails::getConfig();      
    }
    $count=0;
    $status_array['total_count']=count($cron_array);
    foreach($cron_array as $key=>$jetConfig)
    {
      $count++;
      $value_array=array();
      $return_count=0;
      $ids_array=array();
      
      $jetHelper="";
      $merchant_id=$key;
      $value_array['merchant_id']=$merchant_id;
      $fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
      $api_user=$jetConfig['api_user'];
      $api_password=$jetConfig['api_password'];
      $jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
      
      if(isset($collection['ids']) && $collection['ids'] > 0)
      {
        $response = $status = "";
        $updateCount = 0;
        $checkUploadedCount = $resArray = [];
        $checkUploadedCount = json_decode($this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size=1',$merchant_id),true);  

        $response =$this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size='.$checkUploadedCount['total'],$merchant_id,$status);
        $resArray=json_decode($response,true);                  
        if(is_array($resArray) && count($resArray)>0 && $status==200)
        {
          foreach($resArray['merchant_skus'] as $value)
          {
            if (isset($value['status'])) 
            {
              $updateCount++;
              Data::sqlRecords('UPDATE jet_product set status="'.$value['status'].'" WHERE sku="'.addslashes($value['merchant_sku']).'" AND merchant_id="'.$merchant_id.'"',null,'update');
              Data::sqlRecords('UPDATE jet_product_variants set status="'.$value['status'].'" WHERE option_sku="'.addslashes($value['merchant_sku']).'" AND merchant_id="'.$merchant_id.'"',null,'update');
            }                    
          } 
          if($updateCount>0)
          	echo $merchant_id."->".$updateCount."<br>";          
        }
      }
      unset($cron_array[$key]);
      if($count>=100){
        break;
      }
    }
    if(count($cron_array)==0)
      $cronData->cron_data="";
    else
      $cronData->cron_data=json_encode($cron_array);
    $cronData->save(false);
    
    $status_array['remaining_array']=count($cron_array);   
    die;
  }
  public function actionUpdateProductOnJet()
  {
    try
    {
      $message="";
      $productUpdate=Data::sqlRecords('SELECT product_id,data,temp.merchant_id,fullfilment_node_id,api_user,api_password FROM `jet_product_tmp` as temp LEFT JOIN `jet_configuration` as config ON temp.merchant_id=config.merchant_id ORDER BY temp.id  ASC LIMIT 0,500','all','select');
      
      $fullfillmentnodeid = '';
      if(is_array($productUpdate) && count($productUpdate)>0)
      {  
        foreach($productUpdate as $value)
        {
          $merchant_id = $value['merchant_id'];
          $path=\Yii::getAlias('@webroot').'/var/jet/product/update/'.$merchant_id.'/';
          if (!file_exists($path))
          {
            mkdir($path,0775, true);
          }
          $filename=$path.'/'.$value['product_id'].'.log';
          $file=fopen($filename,'w');
          fwrite($file,PHP_EOL.date('d-m-Y H:i:s')." Product Update".PHP_EOL);
          $jetHelper="";
          $fullfillmentnodeid = '';
          if($value['api_user'] && $value['api_password'])
          {
            $check='\n product config set';
            fwrite($file,PHP_EOL.$check);
            $fullfillmentnodeid = $value['fullfilment_node_id'];
            $jetHelper = new Jetapimerchant(API_HOST,$value['api_user'],$value['api_password']);
          }
          $customData = JetProductInfo::getConfigSettings($merchant_id);
          $customPrice = (isset($customData['fixed_price']) && $customData['fixed_price']=='yes')?$customData['fixed_price']:"";
          $newCustomPrice = (isset($customData['set_price_amount']) && $customData['set_price_amount'])?$customData['set_price_amount']:"";
          $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";

          Data::checkInstalledApp($merchant_id,$type=false,$installData);
          $onWalmart=isset($installData['walmart'])?true:false;
          $onNewEgg=isset($installData['newegg'])?true:false;
        
          $query='SELECT id,title,sku,type,product_type,description,variant_id,image,qty,weight,price,attr_ids,jet_attributes,vendor,upc,fulfillment_node FROM `jet_product` WHERE `id`="'.$value['product_id'].'" LIMIT 0,1';
          $result=Data::sqlRecords($query,"one","select");
          $data = json_decode($value['data'],true);
          if(isset($result['id'])) 
          {
            //$count++;
            if(is_array($data) && count($data)>0)
                Jetproductinfo::productUpdateData($result,$data,$jetHelper,$fullfillmentnodeid,$merchant_id,$file,$customPrice,$newCustomPrice,$onWalmart,$onNewEgg,$import_status);
          }
          else
          {
              //add new product
              $message= "add new product with product id: ".$value['product_id'].PHP_EOL;
              fwrite($file, $message);
              $customData = JetProductInfo::getConfigSettings($merchant_id);
              $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:""; 
              Jetproductinfo::saveNewRecords($data, $merchant_id, $import_status);
          }
          Data::sqlRecords('DELETE FROM `jet_product_tmp` where id="'.$value['product_id'].'"');
          fclose($file);
          //$resultValues[$merchant_id]=$check;
        }
      }
      unset($productUpdate,$result,$data,$jetHelper,$customPrice,$jetConfig,$customData);
       
    }
    catch(Exception $e)
    {
        //echo $e->getMessage();die;
        $path=\Yii::getAlias('@webroot').'/var/jet/product/update/Exception';
        if (!file_exists($path)){
            mkdir($path,0775, true);
        }
        $file="";
        $filename=$path.'/Error.log';
        $file=fopen($filename,'a+');
        fwrite($file,"\n".date('d-m-Y H:i:s')."Exception Error:\n".$e->getMessage());
        fclose($file);
    }
  }*/
  public function actionFetchackorder($cron=false)
  {   
    if (!$cron) 
    {      
      $merchant_id = Yii::$app->user->identity->id;
      $configDetails = [];
      $configDetails = Data::getjetConfiguration($merchant_id);  
      if(!empty($configDetails))
      {
        try
        {
          $jetHelper = new Jetapimerchant('https://merchant-api.jet.com/api',$configDetails['api_user'],$configDetails['api_password']);

          $countOrder=0;
          $orderdata="";
          $response=[];
          $orderdata = $jetHelper->CGetRequest('/orders/acknowledged',$merchant_id);
          $response  = json_decode($orderdata,True);
          if(isset($response['order_urls']) && count($response['order_urls']) > 0)
          {
            foreach($response['order_urls'] as $jetorderurl)
            {
              $result1 = $result = "";
              $result1 = $jetHelper->CGetRequest($jetorderurl,$merchant_id);
              $result = json_decode($result1,true);
              
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
                  unset($response,$result,$resultdata,$ackData);
                }                 
              }
            }         
          }

          if ($countOrder>0)
            Yii::$app->session->setFlash('success',$countOrder. "Order(s) has been successfully fetched in app");
          else 
            Yii::$app->session->setFlash('success'," There is no Order in acknowledged state on jet");
          return $this->redirect(Yii::getAlias('@webjeturl').'/jetorderdetail/index');
        }
        catch (Exception $e)
        {
          Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
          return $this->redirect(['index']);
        }
      }
    }        
  }
  public function actionGetnsaverepricingforall()
  {       
      /*$jetConfig=Data::sqlRecords("SELECT `fullfilment_node_id`,`api_user`,`api_password`,`merchant_email` FROM `jet_configuration` WHERE merchant_id='7' LIMIT 0,1",'one','select');
      if(isset($jetConfig['api_user']))
      {
        define("FULLFILMENT_NODE_ID",$jetConfig['fullfilment_node_id']);
        define("API_USER",$jetConfig['api_user']);
        define("API_PASSWORD",$jetConfig['api_password']);
        define("EMAIL",$jetConfig['merchant_email']);
        $jetHelper = new Jetapimerchant(API_HOST,API_USER,API_PASSWORD);
      }
      Jetrepricing::getNSaveRepricingForAll([7], $jetHelper);
      die("testing ends..");*/
      $cronData="";
      $cron_array=$status_array=[];
      $cronData=JetCronSchedule::find()->where(['cron_name'=>'repricing_status'])->one();
      if($cronData && trim($cronData['cron_data'])!=""){
        $cron_array=json_decode($cronData['cron_data'],true);
      }
      else
      {
        if(!$cronData) $cronData = new JetCronSchedule();
        $cron_array = Jetappdetails::getConfig();      
      }
       
      $count=0;
      $status_array['total_count']=count($cron_array);
      foreach($cron_array as $key=>$jetConfig)
      {
          $count++;
          $value_array=array();
          $return_count=0;
          $ids_array=array();
          $fullfillmentnodeid="";
          $api_host="";
          $api_user="";
          $api_password="";
          $jetHelper="";
          $merchant_id=$key;
          $value_array['merchant_id']=$merchant_id;
          $fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
          $api_host=$jetConfig['api_host'];
          $api_user=$jetConfig['api_user'];
          $api_password=$jetConfig['api_password'];
          $jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
          Jetrepricing::getNSaveRepricingForAll([$merchant_id], $jetHelper);
          unset($cron_array[$key]);
          break;
      }
      $cronData->cron_name="repricing_status";
      if(count($cron_array)==0)
        $cronData->cron_data="";
      else
        $cronData->cron_data=json_encode($cron_array);
      $cronData->save(false);

  }
}