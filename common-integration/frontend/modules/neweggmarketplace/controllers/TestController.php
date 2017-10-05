<?php

namespace frontend\modules\neweggmarketplace\controllers;

use yii\web\Controller;
use frontend\modules\neweggmarketplace\controllers;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Neweggapi;
use frontend\modules\neweggmarketplace\components\Cronrequest;
use yii\helpers\BaseJson;
use Yii;
use frontend\modules\neweggmarketplace\components\product\Productimport;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;
use frontend\modules\neweggmarketplace\components\product\ProductPrice;
use frontend\modules\neweggmarketplace\components\product\ProductInventory;
use frontend\modules\neweggmarketplace\components\product\ProductStatus;
use frontend\modules\neweggmarketplace\components\Mail;
use frontend\modules\neweggmarketplace\controllers\ShopifywebhookController;
use frontend\modules\neweggmarketplace\components\product\ValueMappingHelper;


class TestController extends NeweggMainController
{
    /**
     * Check request authentication
     * @return user status 
    */
    public function actionTest()
    {
        $start = microtime(true);
        $query = "UPDATE `newegg_product` ngg JOIN `jet_product` jp ON `ngg`.`product_id`=`jp`.`id` SET `ngg`.`spn`='beauty_ring',`ngg`.`upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE `jp`.`sku`='beauty_ring' AND `ngg`.`merchant_id`='".MERCHANT_ID."' AND `ngg`.`spn`='beauty_ring' AND `ngg`.`upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."'";
        Data::sqlRecords($query, null, "update");
        $time_elapsed_secs = microtime(true) - $start;
        print_r($time_elapsed_secs);die;
        /*0.034199953079224*/
      /*  $csvArrayvalue[]='beauty_ring';
        $query = "UPDATE `newegg_product` ngg JOIN `jet_product` jp ON `ngg`.`product_id`=`jp`.`id` SET `ngg`.`spn`='" .$csvArrayvalue[0]."' WHERE `jp`.`sku`='".$csvArrayvalue[0]."' AND `ngg`.`merchant_id`='".MERCHANT_ID."'";*/
      /*  print_r($query);die;*/
       /* Data::sqlRecords($query, null, "update");
        die;*/
     /*   $connection = Yii::$app->getDb();
        $query='select `merchant_id` from `newegg_shop_detail` nsd where nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");
        ProductStatus::updateProductStatus($product);
        die;*/
     /*    $query='select nsd.merchant_id,npf.feed_id,npf.request_for from `newegg_shop_detail` nsd RIGHT JOIN `newegg_product_feed` npf ON npf.merchant_id=nsd.merchant_id where  nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");
        print_r($product);die;*/
        $data = file_get_contents('202.CSV');
        $rows = explode("\n",$data);
        $csvArray = array();
        foreach($rows as $row) {
            $csvArray[] = str_getcsv($row);
        }
               foreach ($csvArray as $csvArraykey => $csvArrayvalue) {
                                if($csvArraykey=0){
                                    continue;
                                }
                                else{
                                    if(isset($csvArrayvalue[0]) && !empty($csvArrayvalue[0])){
                                        if($csvArrayvalue[10]){
                                            $query = "UPDATE `newegg_product` ngg JOIN `jet_product` jp ON `ngg`.`product_id`=`jp`.`id` SET `ngg`.`spn`='" .$csvArrayvalue[0]."',`ngg`.`upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE `jp`.`sku`='".$csvArrayvalue[0]."' AND `ngg`.`merchant_id`='".MERCHANT_ID."'";
                                            Data::sqlRecords($query, null, "update");
                                        }
                                        else{
                                            $query = "UPDATE `newegg_product` ngg JOIN `jet_product` jp ON `ngg`.`product_id`=`jp`.`id` SET `ngg`.`spn`='" .$csvArrayvalue[0]."',`ngg`.`upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE `jp`.`sku`='".$csvArrayvalue[0]."' AND `ngg`.`merchant_id`='".MERCHANT_ID."'";
                                            Data::sqlRecords($query, null, "update");
                                        }
                                    }
                                }
                            }
        die;
        $data = ValueMappingHelper::getCategoryaArribute('9feb');
        print_r($data);die;
        $params['body']=' { "OperationType": "GetManufacturerRequest", "RequestBody":{ "RequestCriteria": { "ManufacturerName": ".com Solutions Inc." },"RequestCriteria":  {"ManufacturerName": "Acedepot" } } }';
        $url = 'https://api.newegg.com/marketplace/contentmgmt/manufacturer/manufacturerinfo?sellerid=ABYU';
        $headers = [];
     
            $headers[] = "Authorization: 29bb526b1caeb49ac4e90a8bed288c3f ";
            $headers[] = "SecretKey: 24dbc5f5-ac3e-48b0-92d7-eafb98b20747";
        
         $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        if (isset($params['body'])) {
            $putString = stripslashes($params['body']);
            $putData = tmpfile();
            fwrite($putData, $putString);
            fseek($putData, 0);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($params['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
            curl_setopt($ch, CURLOPT_INFILE, $putData);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        print_r($server_output);die;
        curl_close($ch);
   
        print_r(\Yii::getAlias('@webroot'));die;
        $data = Array(
                        'id' => '9646334860',
                        'shopName' => 'ced-jet.myshopify.com'
                    );
        
        ShopifywebhookController::neweggproductdelete($data);

        die("kkkk");
        $data = 'Z5Z1VSP23W5A';
        $data = '{"id":"8842240961","title":"John Varvatos By John Varvatos Deodorant Stick 2.5 Oz","body_html":"Deodorant stick 2.5 oz<br> design house: john varvatos<br> year introduced: 2004<br> fragrance notes: a mix of tamarind leaves, herbs and dates, with spice, woods, leather and vanilla, an alluring scent.<br> recommended use: romantic","vendor":"John Varvatos","product_type":"Fragrance Master Men 2016","created_at":"2017-02-13T01:08:22-08:00","handle":"john-varvatos-by-john-varvatos-deodorant-stick-2-5-oz","updated_at":"2017-02-13T01:08:23-08:00","published_at":"2017-02-13T01:08:22-08:00","published_scope":"global","tags":"Bath & Body, Fragrances for Men","variants":[{"id":"30487523841","product_id":"8842240961","title":"Default Title","price":"20.00","sku":"FN177598","position":"1","grams":"454","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"Default Title","created_at":"2017-02-13T01:08:23-08:00","updated_at":"2017-02-13T01:08:23-08:00","taxable":"1","inventory_quantity":"30","weight":"1","weight_unit":"lb","old_inventory_quantity":"30","requires_shipping":"1"}],"options":[{"id":"10588235137","product_id":"8842240961","name":"Title","position":"1","values":["Default Title"]}],"images":[{"id":"20484896897","product_id":"8842240961","position":"1","created_at":"2017-02-13T01:08:23-08:00","updated_at":"2017-02-13T01:08:23-08:00","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/0802\/4599\/products\/FN177598.jpg?v=1486976903"}],"image":{"id":"20484896897","product_id":"8842240961","position":"1","created_at":"2017-02-13T01:08:23-08:00","updated_at":"2017-02-13T01:08:23-08:00","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/0802\/4599\/products\/FN177598.jpg?v=1486976903"},"shopName":"ced-jet.myshopify.com"}';
        $product = json_decode($data,true);
        $newdata [] =$product;
        $productCount = count($newdata);   
                        Productimport::batchimport($newdata, $productCount,'14',true); 


  /*      $query='select jet.id,jet.sku,jet.type,jet.price,jet.merchant_id,nsd.currency,nsd.country_code,nsd.purchase_status from `newegg_product` ngg INNER JOIN `jet_product` jet ON jet.id=ngg.product_id INNER JOIN `newegg_shop_detail` nsd ON nsd.merchant_id=ngg.merchant_id where ngg.upload_status!="'.Data::PRODUCT_STATUS_NOT_UPLOADED.'" and nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");
        print_r($product);die("kkk");*/
       /* $connection = Yii::$app->getDb();
        $query='select jet.id,jet.sku,jet.type,jet.price,jet.merchant_id,nsd.currency,nsd.country_code,nsd.purchase_status from `newegg_product` ngg INNER JOIN `jet_product` jet ON jet.id=ngg.product_id INNER JOIN `newegg_shop_detail` nsd ON nsd.merchant_id=ngg.merchant_id where ngg.upload_status!="'.Data::PRODUCT_STATUS_NOT_UPLOADED.'" and nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");*/
        $connection = Yii::$app->getDb();
        $product = Array ('0' =>Array('id' => '9209844236','sku' => 'bmw-1','type'=> 'simple' ,'qty' => '99.00' ,'merchant_id' => '1' ,'currency' => 'AUD' ,'country_code' => 'IN' ,'purchase_status' => 'Trail'), '1' =>Array('id' => '4449556742' ,'sku' => 'wallet123','type' => 'simple', 'qty' => '48.00', 'merchant_id' => '1' ,'currency' => 'AUD' ,'country_code' => 'IN', 'purchase_status' => 'Trail'),'2' =>Array('id' => '444sdsdsd9556742' ,'sku' => 'wallet123','type' => 'simple', 'qty' => '48.00', 'merchant_id' => '2' ,'currency' => 'AUD', 'country_code' => 'IN' ,'purchase_status' => 'Trail'),'3' =>Array('id' => '444sd333ddsdsd9556742' ,'sku' => 'wallet123','type' => 'simple', 'qty' => '48.00', 'merchant_id' => '2' ,'currency' => 'AUD', 'country_code' => 'IN' ,'purchase_status' => 'Trail'),'4' =>Array('id' => '444saaaaaaaaad333ddsdsd9556742' ,'sku' => 'wallet123','type' => 'simple', 'qty' => '48.00', 'merchant_id' => '3' ,'currency' => 'AUD', 'country_code' => 'IN' ,'purchase_status' => 'Trail'));
        //print_r($product);die;
        ProductInventory::updateInventoryOnNewegg($product,false,$connection,true);
        die;
        /*$sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
         $products = $sc->call('GET', '/admin/products.json', array('published_status' => 'published', 'limit' => 250));
            print_r($products);die;*/
    /*$params['body']=json_encode([
                    "Type" => "1",
                    "Value"=> 'SPN9008286604'
                    ]);

        $product_status = Neweggapi::postRequest('/contentmgmt/item/price',$params);
         if(isset($product_status['Active']))
         {   
                if($product_status['Active'] == 0)
                $content.='<span class="grid-severity-minor"><span>DeActivated</span></span>';


                if($product_status['Active'] == 1)
                $content.='<span class="grid-severity-notice"><span>Activated</span></span>';
        }
        die;*/
       $action = '/datafeedmgmt/feeds/result/27PKD2UXD1GHA';
        $response = Neweggapi::getRequest($action);
        $getResponse = json_decode($response);
        $content = [];
        if(!empty($getResponse))
        {
            if(isset($getResponse->Code)){
                $query="update `newegg_product_feed` set error ='".$getResponse->Code."' where feed_id='".$feed_id."'";
                Data::sqlRecords($query,null,"update");
            }
            else
            {
                if(isset($getResponse->NeweggEnvelope->Message->ProcessingReport->ProcessingSummary) && $getResponse->NeweggEnvelope->Message->ProcessingReport->ProcessingSummary->WithErrorCount>0){
                    if(isset($getResponse->NeweggEnvelope->Message->ProcessingReport->Result) && isset($getResponse->NeweggEnvelope->Message->ProcessingReport->Result->ErrorList)){
                        $errorDescription = $getResponse->NeweggEnvelope->Message->ProcessingReport->Result;
                        $arrayData = json_encode($errorDescription);
                        $error = json_decode($arrayData,true);
                        $id = str_replace('SPN','',$value['AdditionalInfo']['SellerPartNumber']);
                        $checkProduct = substr(trim($value['ErrorList']['ErrorDescription'][0]),0,5);
                        if($checkProduct=='Error'){
                            unset($value['ErrorList']['ErrorDescription'][0]);
                            foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                $content[]=$value1;
                            }
                            $errorFromNewegg = implode(',', $content);
                            $query="update `newegg_product` set status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' , error='".$errorFromNewegg."' where product_id='".$id."'";
                            Data::sqlRecords($query,null,"update");
                        }
                        else{
                            unset($value['ErrorList']['ErrorDescription'][0]);
                            foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                $content[]=$value1;
                            }
                            $errorFromNewegg = implode(',', $content);
                            $query="update `newegg_product` set status='".Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR."' , error='".$errorFromNewegg."' where product_id='".$id."'";
                            Data::sqlRecords($query,null,"update");
                            $count++;
                        }

                        
                    }
                    else{
                        $errorDescription = $getResponse->NeweggEnvelope->Message->ProcessingReport->Result;
                        $arrayData = json_encode($errorDescription);
                        $error = json_decode($arrayData,true);
                        foreach ($error as $key => $value) {
                            $id = str_replace('SPN','',$value['AdditionalInfo']['SellerPartNumber']);
                            $checkProduct = substr(trim($value['ErrorList']['ErrorDescription'][0]),0,5);
                            if($checkProduct=='Error'){
                                unset($value['ErrorList']['ErrorDescription'][0]);
                                foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                    $content[]=$value1;
                                }
                                $errorFromNewegg = implode(',', $content);
                                $query="update `newegg_product` set status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' , error='".$errorFromNewegg."' where product_id='".$id."'";
                                Data::sqlRecords($query,null,"update");
                            }
                            else{
                                unset($value['ErrorList']['ErrorDescription'][0]);
                                foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                    $content[]=$value1;
                                }
                                $errorFromNewegg = implode(',', $content);
                                $query="update `newegg_product` set status='".Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR."' , error='".$errorFromNewegg."' where product_id='".$id."'";
                                Data::sqlRecords($query,null,"update");
                                $count++;
                            }
                           
                        }
                        
                    }
                }
                else{
                    $query = "SELECT `product_ids` FROM `newegg_product_feed` WHERE feed_id='".$feed_id."' LIMIT 1";
                    $modelU = Data::sqlRecords($query, "one", "select");
                    $ids = explode(',',$modelU['product_ids']);
                    foreach ($ids as $id) {
                        $query="update `newegg_product` set status='".Data::PRODUCT_STATUS_ACTIVATED."' where product_id='".$id."'";
                        Data::sqlRecords($query,null,"update");
                        $count++;
                    }
                }
            }
        }
        else{
            Yii::$app->session->setFlash('error', "Something went wrong");
        }
    }

    public function actionTestMail(){
    
        $mailer = new Mail(['sender'=>'ankitsingh1436@gmail.com','reciever'=>'ankitsingh1436@gmail.com','email'=>'ankitsingh1436@gmail.com','merchant_id'=>'14','subject'=>'Testing'],'email/installmail.html','php',true);
        $mailer->sendMail();
    }

    public function actionTestpost(){
        $params['body']='{"OperationType":"SubmitManufacturerRequest","RequestBody":{"ManufacturerRequest":{"Name":"test ssmanufacturer","URL":"www.cedcommerce.com"}}}';
//$url = 'https://api.newegg.com/marketplace/contentmgmt/manufacturer/manufacturerinfo?sellerid=ABYU';

        $url = 'https://api.newegg.com/marketplace/contentmgmt/manufacturer/creationrequest?sellerid=ABYU
';
        $headers = [];
     
            $headers[] = "Authorization: 29bb526b1caeb49ac4e90a8bed288c3f";
            $headers[] = "SecretKey: 24dbc5f5-ac3e-48b0-92d7-eafb98b20747";
        
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        if (isset($params['body'])) {
            $putString = stripslashes($params['body']);
            $putData = tmpfile();
            fwrite($putData, $putString);
            fseek($putData, 0);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($params['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
            curl_setopt($ch, CURLOPT_INFILE, $putData);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        print_r($server_output);die;
        curl_close ($ch);
        
    }
    public function actionProductinfo(){
            $sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
            $products = $sc->call('GET', '/admin/products/9205306444.json');

    }

    public function actionProductattidupdate(){
        $connection = Yii::$app->getDb();
        $sql='UPDATE `jet_product` SET  attr_ids="" where merchant_id="'.MERCHANT_ID.'" AND `type` LIKE "%simple%" AND `attr_ids` LIKE "%{%"';
        $model = $connection->createCommand($sql)->execute();
    }
    public function actionSqlquery(){
        $sql=Data::sqlRecords('SELECT * FROM `newegg_product` WHERE `merchant_id`="'.MERCHANT_ID.'"',"all","select");
        print_r($sql);die;
    }

    public function actionProductstatus(){
        //die("kjj");
          /*  $params['body']='{"OperationType":"DailyInventoryReportRequest","RequestBody":{"DailyInventoryReportCriteria":{"RequestType":"INTERNATIONAL_INVENTORY_REPORT","FileType":"CSV"}}}';
*/
          
          $product = [];
          $product[]['merchant_id'] = '14';
          $params['body'] = '{"OperationType":"DailyInventoryReportRequest","RequestBody":{"DailyInventoryReportCriteria":{"RequestType":"DAILY_INVENTORY_REPORT","FileType":"CSV"}}}';
          $params['body']='{"OperationType": "DailyInventoryReportRequest","RequestBody": {"RequestID": "23L81RKHG8EHK","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';
              /* $params['body']='{"OperationType":"ItemLookupRequest","RequestBody":{"RequestCriteria":{"Item": [{"UPC": "978925055025"}]}}}';
               $params['body']='{"OperationType": "ItemLookupRequest","RequestBody": {"RequestID": "25O3F1L8YZKGY","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';*/

                $obj = new Neweggapi('ABYU','29bb526b1caeb49ac4e90a8bed288c3fd','24dbc5f5-ac3e-48b0-92d7-eafb98b20747');
                $response = $obj->putRequest('/reportmgmt/report/result',$params);
                print_r($response);die;

            /*$params['body']='{"OperationType": "PremierItemReportRequest","RequestBody": {"PremierItemReportCriteria":{"PremierMark":"0","RequestType":"PREMIER_ITEM_REPORT","FileType": "CSV"}}}';*/
            /*$params['body']='{"OperationType":"GetReportStatusRequest","RequestBody":{"GetRequestStatus": {"RequestIDList": { "RequestID": "210ASV9XGP0UB" },"MaxCount": "10"}}}';*/
           // $params['body']='{"OperationType": "PremierItemReportRequest","RequestBody": {"RequestID": "24X9PHG98VIR8","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';

         //   $params['body']='{"OperationType": "InternationalInventoryReportRequest","RequestBody": {"RequestID": "210ASV9XGP0UB","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';
            
             /*$params['body']='{"OperationType": "ItemLookupRequest","RequestBody": {"RequestID": "240AZURHHO70H","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';*/
            /*$params['body']='{"OperationType": "DailyInventoryReportRequest","RequestBody": {"RequestID": "23HJWN5KTXSDK","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';*/
            
//$url = 'https://api.newegg.com/marketplace/contentmgmt/manufacturer/manufacturerinfo?sellerid=ABYU';
           
            //$url = 'https://api.newegg.com/marketplace/reportmgmt/report/submitrequest?sellerid=ABYU';
     // $url = 'https://api.newegg.com/marketplace/reportmgmt/report/status?sellerid=ABYU';
       /* $url = 'https://api.newegg.com/marketplace/reportmgmt/report/result?sellerid=ABYU';

        $headers = [];
     
            $headers[] = "Authorization: 29bb526b1caeb49ac4e90a8bed288c3f";
            $headers[] = "SecretKey: 24dbc5f5-ac3e-48b0-92d7-eafb98b20747";
        
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";*/
      /*if (isset($params['body'])) {
            $putString = stripslashes($params['body']);
            $putData = tmpfile();
            fwrite($putData, $putString);
            fseek($putData, 0);
        }*/
        /*if (isset($params['body'])) {
            $putString = stripslashes($params['body']);
            $putData = tmpfile();
            fwrite($putData, $putString);
            fseek($putData, 0);
        }

        $ch = curl_init();
       */

        /*curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($params['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
            curl_setopt($ch, CURLOPT_INFILE, $putData);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $server_output = curl_exec ($ch);
        print_r($server_output);die("jj");
        curl_close ($ch);*/

      /*  $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($params['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
            curl_setopt($ch, CURLOPT_INFILE, $putData);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        print_r($server_output);die;
        curl_close ($ch);
        */
        
    }

    public function actionGetproductcategoryinfo(){
        $sku = $_GET['sku'];
        $postData = [];
        $postData['ContentQueryCriteria']=['Type'=>'1','Value'=>$sku];
        $postData = json_encode($postData);
        $obj = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
        $response = $obj->postRequest('/contentmgmt/item/inventory',['body' => $postData,'append' => '&version=304']);
        print_r($response);die;
    }

    public function actionGetproductpriceinfo(){
        $sku = $_GET['sku'];
        $postData = [];
        $params['body']=json_encode([
                    "Type" => "1",
                    "Value"=> $sku
                    ]);

        $obj = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
        $response = $obj->postRequest('/contentmgmt/item/price',$params);
        print_r($response);
    }

    public function actionGetorderinfo(){

        $status = $_GET['status'];
        $array = ['Unshipped'=>'0','PartiallyShipped'=>'1','Shipped'=>'2','Invoiced'=>'3','Void'=>'5'];
        $body = array(
            "OperationType" => "GetOrderInfoRequest",
            "RequestBody" => array(
                "RequestCriteria" => array(
                    "Status" => $array[$status],
                ),
            ),
        );
        $url = '/ordermgmt/order/orderinfo';
        $param = ['append' => '&version=304', 'body' => json_encode($body), 'config' => $config];
        $response = Cronrequest::getRequest($url, $param, $config);
        print_r($response);die;
    }

    public function actionGetsubcategoryinfo(){

        $subcategory_id = $_GET['subcategory'];
        $body = array(
            "OperationType" => "GetSellerSubcategoryPropertyRequest",
            "RequestBody" => array(
                    "SubcategoryID" => $subcategory_id,
            ),
        );
        $url = '/sellermgmt/seller/subcategoryproperty';
        $param = ['body' => json_encode($body)];
        $response = Cronrequest::getRequest($url, $param);
        print_r($response);die;
    }

       public function actionChangeWebhook()
    {
        if(isset($_GET['offset'],$_GET['limit']))
        {
            $userDetails=Data::sqlRecords("SELECT id,token,shop_url FROM `newegg_shop_detail` LIMIT ".$_GET['offset'].",".$_GET['limit'],"all","select");

            if(is_array($userDetails) && count($userDetails)>0)
            {
                foreach ($userDetails as $value) 
                {
                    $isJet=false;
                    $webhooks=[];
                    echo "shop_url: ".$value['shop_url']."<br>";
                    $sc = "";
                    if($value['token']!="")
                    {
                        $sc = new ShopifyClientHelper($value['shop_url'], $value['token'], NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
                        $webhooks=$sc->call('GET','/admin/webhooks.json');
                    }
                    if($sc != '')
                    {
                        $webhooks=$sc->call('GET','/admin/webhooks.json');
                        if(!isset($webhooks['errors']))
                        {
                            //print_r($webhooks);die;
                            foreach ($webhooks as $key => $val) 
                            {
                                if($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/neweggproductupdate'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/shopifywebhook/productupdate'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }
                                elseif($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/neweggproductdelete'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/shopifywebhook/productdelete'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }
                                 elseif($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/isinstall'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/neweggwebhook/isinstall'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }
                                 elseif($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/createshipment'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/shopifywebhook/createshipment'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }
                                 elseif($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/cancelled'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/cancelled'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }
                                 elseif($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/neweggproductcreate'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/shopifywebhook/productcreate'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }
                                 elseif($val['address']=='https://shopify.cedcommerce.com/integration/neweggmarketplace/shopifywebhook/createshipment'){
                                    $change_webhook_data['webhook']=[
                                            'id'=>$val['id'],
                                            'address'=>'https://shopify.cedcommerce.com/integration/shopifywebhook/createshipment'
                                        ];
                                    $response=$sc->call("PUT","/admin/webhooks/".$val['id'].".json",$change_webhook_data);
                                }       
                            }
                        }
                    }
                    print_r($sc->call("GET","/admin/webhooks.json"));
                    echo "<hr>";
                }
            }
        }
    }
    public function actionCheckWebhook(){
    $real_data ='{"id":"10841394956","title":"swatches test","body_html":"swatches test","vendor":"cedcommerce","product_type":"cat3","created_at":"2017-05-24T09:47:30-04:00","handle":"swatches-test","updated_at":"2017-07-03T04:03:58-04:00","published_at":"2017-05-24T09:45:01-04:00","published_scope":"global","tags":"","variants":[{"id":"40345551692","product_id":"10841394956","title":"red","price":"125.00","sku":"swatches","position":"1","grams":"10","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"red","created_at":"2017-05-24T09:47:30-04:00","updated_at":"2017-07-03T04:03:57-04:00","taxable":"1","barcode":"123456789321","inventory_quantity":"12","weight":"0.01","weight_unit":"kg","old_inventory_quantity":"12","requires_shipping":"1"},{"id":"40345551756","product_id":"10841394956","title":"green","price":"125.00","sku":"swatches2","position":"2","grams":"10","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"green","created_at":"2017-05-24T09:47:30-04:00","updated_at":"2017-07-03T04:03:58-04:00","taxable":"1","barcode":"123456789322","inventory_quantity":"122","weight":"0.01","weight_unit":"kg","old_inventory_quantity":"122","requires_shipping":"1"},{"id":"40345551820","product_id":"10841394956","title":"blue","price":"125.00","sku":"swatches3","position":"3","grams":"10","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"blue","created_at":"2017-05-24T09:47:30-04:00","updated_at":"2017-07-03T04:03:58-04:00","taxable":"1","barcode":"123456789323","inventory_quantity":"124444222","weight":"0.01","weight_unit":"kg","old_inventory_quantity":"12222","requires_shipping":"1"}],"options":[{"id":"13172009996","product_id":"10841394956","name":"Color","position":"1","values":["red","green","blue"]}],"images":[{"id":"25232109772","product_id":"10841394956","position":"1","created_at":"2017-05-24T09:47:35-04:00","updated_at":"2017-05-24T09:47:35-04:00","width":"1000","height":"985","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/tshirt3.jpg?v=1495633655"}],"image":{"id":"25232109772","product_id":"10841394956","position":"1","created_at":"2017-05-24T09:47:35-04:00","updated_at":"2017-05-24T09:47:35-04:00","width":"1000","height":"985","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/tshirt3.jpg?v=1495633655"},"shopName":"ced-jet.myshopify.com"}';
    $data = json_decode($real_data,true);
    $url = Yii::getAlias('@webbaseurl')."/shopifywebhook/curlprocessforproductupdate?maintenanceprocess=1";
    try{
        Data::sendCurlRequest($data,$url);
    }
    catch (Exception $e)
        {
            print_r($e->getMessage());die;
            $this->createExceptionLog('actionProductupdate',$e->getMessage(),$shopName);
            return;
        }

  }

  public function actionTestreport(){

  }

}

