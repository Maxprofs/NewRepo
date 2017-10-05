<?php
namespace console\controllers;
use yii\console\Controller;
use Yii;
use yii\web;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Walmartappdetails;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Inventory\InventoryUpdate;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\controllers\WalmartrepricingController;
use frontend\modules\walmart\models\WalmartCronSchedule;
use frontend\modules\walmart\controllers\WalmartorderdetailController;

/**
* Cron controller
*/
class CronController extends Controller 
{

  public function beforeAction($action)
  {
    if (file_exists(Yii::getAlias('@webroot').'/maintenance.flag')) {
      return false;
    }
    return true;    
  }
  public function actionIndex() 
  {
      /*Yii::$app->shop_name = 'ask-ergo-works.myshopify.com';
      Yii::$app->merchant_id = '656';
      $merchants = Data::sqlRecords("SELECT `merchant_id`,`id` FROM `walmart_price_inventory_log` GROUP BY `merchant_id` ORDER BY `id` ASC",'all','select');*/
      // ob_start ();
      echo "cron service runnning";
      echo getcwd();
      // $html = ob_get_clean();
      die("hello");
  }

  /**
   * Sync Price and Inventory on Walamrt
   */
  public function actionProcessInventoryUpdateLog()
  {
    //ob_start ();
    $merchants = Data::sqlRecords("SELECT `merchant_id`,`id` FROM `walmart_price_inventory_log` GROUP BY `merchant_id` ORDER BY `id` ASC",'all','select');
    
    foreach($merchants  as $data){

      $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$data['merchant_id']."'",'one','select');
      $walmartHelper = new Walmartapi($walmartConfig['consumer_id'],$walmartConfig['secret_key'],$walmartConfig['consumer_channel_type_id']);
             // define("MERCHANT_ID", $merchant_id);
      $products = [];
      $collection = Data::sqlRecords("SELECT * FROM `walmart_price_inventory_log` WHERE `merchant_id`='{$data['merchant_id']}' ORDER BY `id` ASC ",'all','select');
      
      foreach($collection as $row){
        $products[] = json_decode($row['data'],true);
      }
     if(!$data['merchant_id']==810)
      $walmartHelper->updateInventoryOnWalmart($products,'cron',$data['merchant_id']);
      Data::sqlRecords("DELETE FROM `walmart_price_inventory_log` WHERE `merchant_id`='{$data['merchant_id']}' ",'','delete'); 
    }
    //$html = ob_get_clean();
  }
  
  /**
   * Sync Orders on Walamrt
   */
  public function actionSyncWalmartOrder()
  {
    ob_start ();
    $obj = new WalmartorderdetailController(Yii::$app->controller->id,'');
     
    $cron_array = array();
    $connection = Yii::$app->getDb();
    $processedMerchantCount = 0;
    $size = 40;
    $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'sync_order'])->one();
    if($cronData && $cronData['cron_data'] != ""){
      $cron_array = json_decode($cronData['cron_data'],true);
    }
    else
    {
      $cron_array = Walmartappdetails::getConfig();
    }
    
    $status_array['total_count'] = count($cron_array);
    if(is_array($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $k=>$Config)
      {
        try
        {
            $Config['merchant_id'] = $k;
            $obj->actionSyncorder($Config);
           
            unset($cron_array[$k]);
        }
        catch (Exception $e)
        {
          Data::createLog("order fetch exception ".$e->getTraceAsString(),'walmartOrderSyncCron/exception.log','a',true);
          unset($cron_array[$k]);
          continue;
        }
        catch(\yii\db\IntegrityException $e)
        {
          Data::createLog("order fetch db-integrity-exception ".$e->getTraceAsString(),'walmartOrderSyncCron/exception.log','a',true);
          unset($cron_array[$k]);
          continue;
        }
        catch(\yii\db\Exception $e)
        {
          Data::createLog("order fetch db-exception ".$e->getTraceAsString(),'walmartOrderSyncCron/exception.log','a',true);
          unset($cron_array[$k]);
          continue;
        }
        $processedMerchantCount++;
        if($processedMerchantCount==$size)
        break;
      }
     
    }

    if(count($cron_array)==0)
      $cronData->cron_data="";
    else
      $cronData->cron_data=json_encode($cron_array);

    $cronData->save(false);
    unset($cronData);
    $html = ob_get_clean();
   
  }

  /**
   * Fetch New Orders from Walamrt
   */
  /*public function actionWalmartorder()
  {
    ob_start ();
    $obj = new WalmartorderdetailController(Yii::$app->controller->id,'');
    $cron_array = array();
    $connection = Yii::$app->getDb();
    $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'fetch_order'])->one();
    if($cronData && $cronData['cron_data'] != ""){
      $cron_array = json_decode($cronData['cron_data'],true);
    }
    else
    {
      $cron_array = Walmartappdetails::getConfig();
    }
    $processedMerchantCount = 0;
    $size = 100;
    
    $status_array['total_count'] = count($cron_array);
    $error_array = array();
    if(is_array($cron_array) && count($cron_array)>0)
    {
      foreach($cron_array as $k=>$Config)
      {
        try
        {
            $Config['merchant_id'] = $k;
            $obj->actionCreate($Config);
           
            unset($cron_array[$k]);
        }
        catch (Exception $e)
        {
          //$OrderError["error"][]=$e->getMessage();
          Data::createLog("order fetch exception ".$e->getTraceAsString(),'walmartOrderCron/exception.log','a',true);
          unset($cron_array[$k]);
          continue;
        }
        $processedMerchantCount++;
        if($processedMerchantCount==$size)
          break;
      }
      
    }

    if(count($cron_array)==0)
      $cronData->cron_data="";
    else
      $cronData->cron_data=json_encode($cron_array);

    $cronData->save(false);
    //print_r($status_array);
    unset($cronData);
    unset($status_array);
    $html = ob_get_clean();
    
  }*/
  public function actionWalmartorder()
  {
    ob_start ();
    $obj = new WalmartorderdetailController(Yii::$app->controller->id,'');
    $cron_array = array();
    $connection = Yii::$app->getDb();
    $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'fetch_order'])->one();
    if($cronData && $cronData['cron_data'] != "") {
        $cron_array = json_decode($cronData['cron_data'],true);
    }
    else {
        $cron_array = Walmartappdetails::getMerchants();
    }

    $processedMerchantCount = 0;
    $size = 100;

    if(is_array($cron_array) && count($cron_array)>0)
    {
        foreach($cron_array as $key=>$Config)
        {
          try
          {
              $merchant_id = $key;
              $walmartApiData = Walmartappdetails::getWalmartAPiDetails($merchant_id);
              $shop_detail = Data::getWalmartShopDetails($merchant_id);
              if(isset($walmartApiData['consumer_id']) && isset($walmartApiData['secret_key']))
              {
                $Config['merchant_id'] = $merchant_id;
                $Config['consumer_id'] = $walmartApiData['consumer_id'];
                $Config['secret_key'] = $walmartApiData['secret_key'];
                $Config['currency'] = $shop_detail['currency'];
                $obj->actionCreate($Config);
              }
           
              unset($cron_array[$key]);
          }
          catch (Exception $e)
          {
            Data::createLog("order fetch exception ".$e->getTraceAsString(),'walmartOrderCron/exception.log','a',true);
            unset($cron_array[$key]);
            continue;
          }
          catch(\yii\db\IntegrityException $e)
          {
            Data::createLog("order fetch db-integrity-exception ".$e->getTraceAsString(),'walmartOrderSyncCron/exception.log','a',true);
            unset($cron_array[$k]);
            continue;
          }
          catch(\yii\db\Exception $e)
          {
            Data::createLog("order fetch db-exception ".$e->getTraceAsString(),'walmartOrderSyncCron/exception.log','a',true);
            unset($cron_array[$k]);
            continue;
          }
          $processedMerchantCount++;
          if($processedMerchantCount == $size)
              break;
        }
    }

    if(count($cron_array)==0)
        $cronData->cron_data = "";
    else
        $cronData->cron_data = json_encode($cron_array);

    $cronData->save(false);
    unset($cronData);
    $html = ob_get_clean();
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
          $status = isset($item['orderLineStatuses']['orderLineStatus']['status'])?$item['orderLineStatuses']['orderLineStatus']['status']:'';
          $items[$sku][$item['lineNumber']] = array('lineNumber'=>$item['lineNumber'],'status'=>$status,'sku'=>$sku);
          
      }
      return $items;
  }

  /**
   * Sync Product Status from Walmart
   */
  public function actionProductstatus()
  {
      ob_start ();
      $cron_array = array();
      //$connection = Yii::$app->getDb();
      $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'fetch_status'])->one();
      if($cronData && $cronData['cron_data'] != ""){
        $cron_array = json_decode($cronData['cron_data'],true);
      }
      else
      {
        $cron_array = Walmartappdetails::getConfig();
      }
      $start = 0;
      $count = 0;
      $countArr = 0;
      $updateStatus=[];
      if(is_array($cron_array) && count($cron_array)>0)
      {
          foreach($cron_array as $k=>$Config)
          {
              try
              {
                  $isError=false;
                  $merchant_id = $k;
                  $count=0;
                  $consumer_id = $Config['consumer_id'];
                  $secret_key = $Config['secret_key'];
                  $channel_type_id = $Config['consumer_channel_type_id'];
                  $walmartAPi = new Walmartapi($consumer_id, $secret_key, $channel_type_id);
                  $countArr++;
                  unset($cron_array[$k]);
                  $query="select count(*) as product from `walmart_product` where merchant_id='".$merchant_id."'";
                  $collCount=Data::sqlRecords($query);
                  if(is_array($collCount) && count($collCount)>0)
                  {
                      $pages=ceil($collCount[0]['product']/20);
                      for($i=1;$i<=$pages;$i++) 
                      {
                          $offset = $i*20;
                          // Get 20 products status(s) from walmart
                          $productArray = $walmartAPi->getItems(['limit'=>20,'offset'=>$offset]);
                          
                          if(is_array($productArray) && count($productArray)>0 && isset($productArray['MPItemView']))
                          {
                              foreach ($productArray['MPItemView'] as $key => $value) 
                              {
                                  //get product sku
                                  $product=[];
                                  $query="select sku,id from jet_product where merchant_id='".$merchant_id."' and sku='".$value['sku']."' LIMIT 1";
                                  $product=Data::sqlRecords($query,'one','select');
                                  if(is_array($product) && count($product)>0)
                                  {
                                      //update main product status(s)
                                      $query="update walmart_product set status='".$value['publishedStatus']."' where product_id='".$product['id']."'";
                                      //echo $query;die;
                                      Data::sqlRecords($query,null,'update');
                                      $count++;
                                  }
                                  else
                                  {
                                      //update variants product status
                                      $query="select option_sku,option_id from jet_product_variants where merchant_id='".$merchant_id."' and option_sku='".$value['sku']."' LIMIT 1";
                                      $productVariant=Data::sqlRecords($query,'one','select');
                                      if(is_array($productVariant) && count($productVariant)>0)
                                      {
                                          //update main product status(s)
                                          $query="update walmart_product_variants set status='".$value['publishedStatus']."' where option_id='".$productVariant['option_id']."'";
                                          //echo $query;die;
                                          Data::sqlRecords($query,null,'update');
                                          $count++;
                                      }
                                  }
                              }
                          }
                          //update all product having status is "item_processing"
                          $query="update `walmart_product` set status='".WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED."' where status='".WalmartProduct::PRODUCT_STATUS_PROCESSING."' and merchant_id='".$merchant_id."'";
                          Data::sqlRecords($query,null,'update');
                      }
                  }
                  $updateStatus[$merchant_id]=$count;
                  unset($cron_array[$k]);
                  if($countArr>=10)
                      break;
              }
              catch(Exception $e)
              {
                  Data::createLog("product status exception ".$e->getTraceAsString(),'productStatus/exception.log','a',true);
              }
          }
      }
      if(count($cron_array)==0)
          $cronData->cron_data="";
      else
          $cronData->cron_data=json_encode($cron_array);
      $cronData->save(false);
      var_dump($updateStatus);
      $html = ob_get_clean();

  }

  /**
   * Sync Inventory on Walmart
   */
  public function actionInventoryupdate()
  {
      ob_start ();
      $cron_array = array();
      //$connection = Yii::$app->getDb();
      $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'fetch_inventory'])->one();
      if($cronData && $cronData['cron_data'] != "")
      {
        $cron_array = json_decode($cronData['cron_data'],true);
      }
      else
      {
        $cron_array = Walmartappdetails::getConfig();
      }
      $start = 0;
      $countArr = 0;
      $updateInventory=[];
      if(is_array($cron_array) && count($cron_array)>0)
      {
          foreach($cron_array as $k=>$Config)
          {
              try
              {
                  $isError=false;
                  $merchant_id = $k;
                  $count=0;
                  $consumer_id = $Config['consumer_id'];
                  $secret_key = $Config['secret_key'];
                  $channel_type_id = $Config['consumer_channel_type_id'];
                  $inventoryUpdateObj = new InventoryUpdate($consumer_id, $secret_key, $channel_type_id);
                  $countArr++;
                  unset($cron_array[$k]);
                  $query='select jet.id,sku,type,qty,jet.merchant_id from `walmart_product` wal INNER JOIN `jet_product` jet ON jet.id=wal.product_id where wal.status!="Not Uploaded" and wal.merchant_id="'.$merchant_id.'"';
                  $product = Data::sqlRecords($query,"all","select");
                  if(is_array($product) && count($product)>0)
                  {
                      $response=[];
                      if(!$product['merchant_id']==810)
                      $response = $inventoryUpdateObj->updateInventoryOnWalmart($product,"product");
                      if(isset($response['errors']))
                          $error++;
                      else
                          $count=count($product);
                  }
                  $updateInventory[$merchant_id]=$count;
                  unset($cron_array[$k]);
                  if($countArr>=10)
                      break;
              }
              catch(Exception $e)
              {
                  Data::createLog("product status exception ".$e->getTraceAsString(),'productInventory/exception.log','a',true);
              }
          }
      }
      if(count($cron_array)==0)
          $cronData->cron_data="";
      else
          $cronData->cron_data=json_encode($cron_array);
      $cronData->save(false);
      var_dump($updateInventory);
      $html = ob_get_clean();
  }
  /**
   * Walmart Repricing cron
   * 
   * @return bool
  */
   public function actionProductrepricing()
  {
    ob_start ();
    $obj = new WalmartrepricingController(Yii::$app->controller->id,''); 
    /*check already saved file*/
    if (file_exists(\Yii::getAlias('@webroot').'/var/walmart/productpricing')) {
        $files = glob(\Yii::getAlias('@webroot').'/var/walmart/productpricing/*'); // get all file names
        if(!empty($files)){
            foreach($files as $file){ // iterate files
               $merchant_id = basename($file);
               $contentFiles = glob($file.'/*');
               if(!empty($contentFiles)){
                    foreach ($contentFiles as $key => $value) {
                      $data = file_get_contents($value);
                      $status= WalmartrepricingController::postPricedata($data,$merchant_id);
                       unlink($file);// delete file
                    }
               }
            }
        }
    }
    $cron_array = array();
    $connection = Yii::$app->getDb();
    $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'product_repricing'])->one();
    if($cronData && $cronData['cron_data'] != "") {
        $query = "SELECT * FROM `walmart_product_repricing` WHERE `repricing_status`='1' AND `id`>'".$cronData['cron_data']."'ORDER BY `id` ASC LIMIT 100000";
        $cron_array = Data::sqlRecords($query,'all','select');
       if(empty($cron_array)){
        $query = "SELECT * FROM `walmart_product_repricing` WHERE `repricing_status`='1' ORDER BY `id` ASC LIMIT 100000";
        $cron_array = Data::sqlRecords($query,'all','select');
       }
    }
    else {
        $query = "SELECT * FROM `walmart_product_repricing` WHERE `repricing_status`='1' ORDER BY `id` ASC LIMIT 100000";
        $cron_array = Data::sqlRecords($query,'all','select');
    }

    if(is_array($cron_array) && count($cron_array)>0)
    {
        $obj->actionCronPriceUpdate($cron_array);

    }
    unset($cronData);
    $html = ob_get_clean();
  }
}
