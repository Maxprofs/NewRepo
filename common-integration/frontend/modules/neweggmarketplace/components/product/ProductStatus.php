<?php
namespace frontend\modules\neweggmarketplace\components\product;


use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Helper;
use frontend\modules\neweggmarketplace\components\productInfo;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use frontend\modules\neweggmarketplace\components\Neweggapi;
use frontend\modules\neweggmarketplace\components\product\VariantsProduct;
use Yii;
use yii\base\Component;
use yii\helpers\Url;

class ProductStatus extends Component
{
    /**
     * update product Status of submitted product
     *
    */
    public static function updateProductStatusRequest($product)
    {
        if(!empty($product)){
            foreach ($product as $productkey => $productvalue) {
                $postData='{"OperationType":"DailyInventoryReportRequest","RequestBody":{"DailyInventoryReportCriteria":{"RequestType":"DAILY_INVENTORY_REPORT","FileType":"CSV"}}}';
                $merchantId=$productvalue['merchant_id'];
                $configData = Helper::configurationDetail($merchantId);
                $obj = new Neweggapi($configData['seller_id'],$configData['authorization'],$configData['secret_key'],true);
                $response = $obj->postRequest('/reportmgmt/report/submitrequest',['body' => $postData]);
                $server_output = json_decode($response,true);
                if(isset($server_output['IsSuccess']) && $server_output['IsSuccess']){
                        foreach ($server_output['ResponseBody'] as $key => $value) {
                            foreach ($value as $key1 => $val1) {
                                $old_date_timestamp  = strtotime($val1['RequestDate']);
                                $new_date = date('Y-m-d H:i:s', $old_date_timestamp);
                                $query="INSERT INTO `newegg_product_feed` (`merchant_id`,`feed_id`,`product_ids`,`status`,`created_at`,`request_for`)VALUES(".$merchantId.",'".$val1['RequestId']."','-','".$val1['RequestStatus']."','".$new_date."','".Data::FEED_PRODUCT_STATUS."')";
                                Data::sqlRecords($query,null,'insert');
                                $logPath = $merchantId.'/cron/productStatusUpdate/'.time().'/'.$val1['RequestId'];
                                Helper::createLog($postData,$logPath);
                            }
                            
                        }              
                }
            }

        }
    }

     public static function updateProductStatusResult($product)
    {
        foreach ($product as $productkey => $productvalue) {
            $postData='{"OperationType": "DailyInventoryReportRequest","RequestBody": {"RequestID": "'.$productvalue['feed_id'].'","PageInfo": {"PageSize": "10","PageIndex": "1"}}}';
            $merchantId=$productvalue['merchant_id'];
            $logPath = $merchantId.'/cron/productStatusUpdate/'.time().'/'.$productvalue['feed_id'];
            $configData = Helper::configurationDetail($merchantId);
            $obj = new Neweggapi($configData['seller_id'],$configData['authorization'],$configData['secret_key'],true);
            $response = $obj->putRequest('/reportmgmt/report/result',['body' => $postData]);
            $server_output = json_decode($response,true);
            Helper::createLog(json_encode($server_output),$logPath);
            if(isset($server_output['NeweggAPIResponse']) && $server_output['NeweggAPIResponse']){
            if(isset($server_output['NeweggAPIResponse']['IsSuccess']) && $server_output['NeweggAPIResponse']['IsSuccess']){
                            $old_date_timestamp  = strtotime($server_output['NeweggAPIResponse']['ResponseBody']['RequestDate']);
                            $new_date = date('Y-m-d H:i:s', $old_date_timestamp);
                            $data = file_get_contents($server_output['NeweggAPIResponse']['ResponseBody']['ReportFileURL']);
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
                                            $selectQuery = "SELECT `type`,`id`,`variant_id` FROM jet_product WHERE merchant_id='".$merchantId."' AND sku='".$csvArrayvalue[0]."'";
                                $selectData = Data::sqlRecords($selectQuery,'one','select');
                                if($selectData){
                                    if($selectData['type']=='simple'){
                                        $updateQuery="UPDATE `newegg_product` SET `upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE merchant_id='".$merchantId."' AND product_id='".$selectData['id']."'";
                                        Data::sqlRecords($updateQuery, null, "update");
                                    }else{
                                        $updateQuery="UPDATE `newegg_product` SET `upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE merchant_id='".$merchantId."' AND product_id='".$selectData['id']."'";
                                        Data::sqlRecords($updateQuery, null, "update");
                                        $updateVariantsQuery="UPDATE `newegg_product_variants` SET `upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE merchant_id='".$merchantId."' AND option_id='".$selectData['variant_id']."'";
                                        Data::sqlRecords($updateVariantsQuery, null, "update");
                                    }
                                }
                                else{
                                        $selectQuery = "SELECT `option_id` FROM jet_product_variants WHERE merchant_id='".$merchantId."' AND option_sku='".$csvArrayvalue[0]."'";
                                        $selectData = Data::sqlRecords($selectQuery,'one','select');
                                        if($selectData){
                                            $updateVariantsQuery="UPDATE `newegg_product_variants` SET `upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE merchant_id='".$merchantId."' AND option_id='".$selectData['option_id']."'";
                                            Data::sqlRecords($updateVariantsQuery, null, "update");
                                        }
                                    }
                                        }
                                        else{
                                            $selectQuery = "SELECT `type`,`id`,`variant_id` FROM jet_product WHERE merchant_id='".$merchantId."' AND sku='".$csvArrayvalue[0]."'";
                                $selectData = Data::sqlRecords($selectQuery,'one','select');
                                if($selectData){
                                    if($selectData['type']=='simple'){
                                        $updateQuery="UPDATE `newegg_product` SET `upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE merchant_id='".$merchantId."' AND product_id='".$selectData['id']."'";
                                        Data::sqlRecords($updateQuery, null, "update");
                                    }else{
                                        $updateQuery="UPDATE `newegg_product` SET `upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE merchant_id='".$merchantId."' AND product_id='".$selectData['id']."'";
                                        Data::sqlRecords($updateQuery, null, "update");
                                        $updateVariantsQuery="UPDATE `newegg_product_variants` SET `upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE merchant_id='".$merchantId."' AND option_id='".$selectData['variant_id']."'";
                                        Data::sqlRecords($updateVariantsQuery, null, "update");
                                    }
                                }else{
                                    $selectQuery = "SELECT `option_id` FROM jet_product_variants WHERE merchant_id='".$merchantId."' AND option_sku='".$csvArrayvalue[0]."'";
                                    $selectData = Data::sqlRecords($selectQuery,'one','select');
                                    if($selectData){
                                        $updateVariantsQuery="UPDATE `newegg_product_variants` SET `upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE merchant_id='".$merchantId."' AND option_id='".$selectData['option_id']."'";
                                        Data::sqlRecords($updateVariantsQuery, null, "update");
                                    }
                                }
                                        }
                                    }
                                }
                            }      
            }
        }
        $delete = "DELETE FROM `newegg_product_feed` WHERE `merchant_id`='".$merchantId."' AND `feed_id`='".$productvalue['feed_id']."'";
                    Data::sqlRecords($delete, null, 'delete'); 
            
        }
    }

    /*Product status update using Sku/sellerPartNumber*/

    public static function updateStatus($product,$merchant_id){
        $count = 0;
        $obj = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
        foreach ($product as $key => $value) {
            if($value['type']=='simple'){

                $params['body']=json_encode([
                    "Type" => "1",
                    "Value"=> $value['sku']
                    ]);
                $response = $obj->postRequest('/contentmgmt/item/price',$params);
                $response = json_decode($response,true);
               
                if(isset($response['Code']) && $response['Code']=='CT026'){
                           $updateMainProduct = "UPDATE `newegg_product` SET upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `product_id`='".$value['product_id']."'";
                            Data::sqlRecords($updateMainProduct,null,'update');
                }
                if(isset($response['Active'])){
                    if($response['Active']=='1'){
                        $updateMainProduct = "UPDATE `newegg_product` SET upload_status='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `product_id`='".$value['product_id']."'";
                        Data::sqlRecords($updateMainProduct,null,'update');
                        $count++;
                    }
                    else{
                        $updateMainProduct = "UPDATE `newegg_product` SET upload_status='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `product_id`='".$value['product_id']."'";
                        Data::sqlRecords($updateMainProduct,null,'update');
                         $count++;
                    }
                }
            }
            else{

                $query = 'select `option_sku`,`ngg`.`product_id`,`ngg`.`option_id` from (SELECT * FROM `jet_product_variants` WHERE `merchant_id`= "' . MERCHANT_ID . '" and `product_id`="'.$value['product_id'].'") as jet INNER JOIN (SELECT * FROM `newegg_product_variants` WHERE `merchant_id`=  "' . MERCHANT_ID . '" and `product_id`="'.$value['product_id'].'") as ngg ON jet.option_id=ngg.option_id where ngg.merchant_id="' . MERCHANT_ID . '" and `jet`.`product_id`="'.$value['product_id'].'"';
                $variproducts = Data::sqlRecords($query, "all", "select");
               
                if(count($variproducts)>0){
                    foreach ($variproducts as $variproduct) {
                    $params['body']=json_encode([
                    "Type" => "1",
                    "Value"=> $variproduct['option_sku']
                    ]);
                    $response = $obj->postRequest('/contentmgmt/item/price',$params);
                    $response = json_decode($response,true);
                    if(count($response)>0){
                        if(isset($response['Code']) && $response['Code']=='CT026'){
                           $mainProductSelectQuery = "SELECT * FROM jet_product WHERE `merchant_id`='".MERCHANT_ID."' AND `variant_id`='".$variproduct['option_id']."'"; 
                           $mainProductData = Data::sqlRecords($mainProductSelectQuery,null,'select');
                           if(count($mainProductData)>0){
                            $updateMainProduct = "UPDATE `newegg_product` SET upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `product_id`='".$variproduct['product_id']."'";
                            Data::sqlRecords($updateMainProduct,null,'update');
                            $updateChildProduct = "UPDATE `newegg_product_variants` SET upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `option_id`='".$variproduct['option_id']."'";
                            Data::sqlRecords($updateChildProduct,null,'update');
                             $count++;

                           }
                           else{
                                 $updateChildProduct = "UPDATE `newegg_product_variants` SET upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `option_id`='".$variproduct['option_id']."'";
                            Data::sqlRecords($updateChildProduct,null,'update');
                             $count++;
                           }
                        }
                        else{
                            if(isset($response['Active'])){
                                if($response['Active']=='1'){
                                   
                                    $mainProductSelectQuery = "SELECT * FROM jet_product WHERE `merchant_id`='".MERCHANT_ID."' AND `variant_id`='".$variproduct['option_id']."'"; 
                                   $mainProductData = Data::sqlRecords($mainProductSelectQuery,null,'select');
                                   if(count($mainProductData)>0){
                                    $updateMainProduct = "UPDATE `newegg_product` SET upload_status='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `product_id`='".$variproduct['product_id']."'";
                                    Data::sqlRecords($updateMainProduct,null,'update');
                                    $updateChildProduct = "UPDATE `newegg_product_variants` SET upload_status='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `option_id`='".$variproduct['option_id']."'";
                                    Data::sqlRecords($updateChildProduct,null,'update');
                                     $count++;

                                   }
                                   else{
                                         $updateChildProduct = "UPDATE `newegg_product_variants` SET upload_status='".Data::PRODUCT_STATUS_ACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `option_id`='".$variproduct['option_id']."'";
                                    Data::sqlRecords($updateChildProduct,null,'update');
                                     $count++;
                                   }
                                }
                                else{
                                    $mainProductSelectQuery = "SELECT * FROM jet_product WHERE `merchant_id`='".MERCHANT_ID."' AND `variant_id`='".$variproduct['option_id']."'"; 
                                   $mainProductData = Data::sqlRecords($mainProductSelectQuery,null,'select');
                                   if(count($mainProductData)>0){
                                    $updateMainProduct = "UPDATE `newegg_product` SET upload_status='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `product_id`='".$variproduct['product_id']."'";
                                    Data::sqlRecords($updateMainProduct,null,'update');
                                    $updateChildProduct = "UPDATE `newegg_product_variants` SET upload_status='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `option_id`='".$variproduct['option_id']."'";
                                    Data::sqlRecords($updateChildProduct,null,'update');
                                     $count++;

                                   }
                                   else{
                                         $updateChildProduct = "UPDATE `newegg_product_variants` SET upload_status='".Data::PRODUCT_STATUS_DEACTIVATED."' WHERE `merchant_id`='".MERCHANT_ID."' AND `option_id`='".$variproduct['option_id']."'";
                                    Data::sqlRecords($updateChildProduct,null,'update');
                                     $count++;
                                   }
                                }
                            }
                        }
                    }
                }
                }
            }
          
        
        }
        return ['count'=>$count];
    }
    
}
