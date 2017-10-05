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

class ProductInventory extends Component
{

    public static function updateInventoryOnNewegg($products,$merchant_id = false,$connection,$cron = false)
    {  
        $timeStamp = (string)time();
        if (count($products) > 0 ){
            $error=[];
            $productUpload = [];
            $newegg_envelope = array('-xmlns:xsi'=>'http://www.w3.org/2001/XMLSchema-instance','-xsi:noNamespaceSchemaLocation'=>'NeweggEnvelop.xsd');
            $newegg_envelope['Header'] = array('DocumentVersion' => '1.0');
            $newegg_envelope['MessageType'] = 'Inventory';
            $message = array();
            $itemFeed = array();
            $first = '';
            $last = '';
            $item = [];
            $uploadProductIds=[];
            foreach ($products as $product) {

                $productArray = $product;
                if($first=='' && $last==''){
                    $first = $last = $productArray['merchant_id'];
                }
                $first = $productArray['merchant_id'];
                if($first==$last){ 
                    self::getPrepareData($productArray,$item,$itemFeed,$newegg_envelope,$productUpload,$connection,$uploadProductIds);
                }
                else{
                    if($cron){
                        $sendFeedData = self::sendFeed($productUpload,$last,true);
                        $first = $last = $productArray['merchant_id'];
                        $productUpload = [];
                        $item = [];
                        $merchantId = $productArray['merchant_id'];
                        self::getPrepareData($productArray,$item,$itemFeed,$newegg_envelope,$productUpload,$connection,$uploadProductIds);
                    }
                    

                }

            }
            if(count($productUpload)>0){
                if($cron){
                    $merchantId = $productArray['merchant_id'];
                    $sendFeedData = self::sendFeed($productUpload,$last,true);
                    return $sendFeedData;
                }
                else{
                    $sendFeedData = self::sendFeed($productUpload,$merchant_id,false);
                        if(isset($sendFeedData['IsSuccess']) && $sendFeedData['IsSuccess']){
                        foreach ($sendFeedData['ResponseBody'] as $key => $value) {
                            foreach ($value as $key1 => $val1) {
                               return ['uploadIds'=>$uploadProductIds,'feedId'=>$val1['RequestId'], 'erroredSkus'=>$error];
                            }
                            
                        }
                        
                         
                    }
                }
            }
            if($cron){
                return;
            }
            if(count($error)>0){
                return ['errors'=>$error];
            }
        }

    }


    public static function sendFeed($feedData,$merchantId,$cron = false){
          $postData = json_encode($feedData);
                    if($cron){
                        $configData = Helper::configurationDetail($merchantId);
                        $logPath = $merchantId.'/cron/inventoryUpdate/'.time();
                        Helper::createLog($postData,$logPath);
                        $obj = new Neweggapi($configData['seller_id'],$configData['authorization'],$configData['secret_key'],true);
                        $response = $obj->postRequest('/datafeedmgmt/feeds/submitfeed',['body' => $postData,
                        'append' => '&requesttype=INVENTORY_DATA']);
                    }
                    else{
                        $logPath = $merchantId.'/inventoryUpdate/'.time();
                        Helper::createLog($postData,$logPath);
                        $obj = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
                        $response = $obj->postRequest('/datafeedmgmt/feeds/submitfeed',['body' => $postData,
                        'append' => '&requesttype=INVENTORY_DATA']);
                    }
                   
                    $server_output = json_decode($response,true);
                
            
    }
      /**
     * validate product
     * @param [] $product
     * @return bool
     */    
    public static function validateProduct($product,$connection)
    {
        $qty=$product['qty'];
        if(isset($product['country_code']) && !empty($product['country_code'])){
            $country_code = $product['country_code'];
        }
        else{
            $country_code = COUNTRY_CODE;
        }
        
        $errorArr=[];
        $validatedProduct=[];
        $validatedPro=[];
        if(($qty<=0 || ($qty && !is_numeric($qty))) || trim($qty)==""){
                $errorArr[]="Missing/invalid Inventory";
        }
        if(!$country_code){
            $errorArr[]="Missing country code";
        }
        if(count($errorArr)>0){
            $validatedProduct['error'] = $errorArr;
        } elseif(count($validatedPro)>0) {
            $validatedProduct['success'] = $validatedPro;
        }
        return $validatedProduct;
    }

    /*
    product basic info prepare

    */
    public static function getProductBasicInfo($productArray)
    {
        $data = [];
        //$data['SellerPartNumber'] =trim('SPN'.$productArray['id']);
        $data['SellerPartNumber'] =trim($productArray['sku']);
        if(isset($productArray['country_code']) && !empty($productArray['country_code'])){
            $country_code = $product['country_code'];
        }
        else{
            $country_code = COUNTRY_CODE;
        }
        $data['WarehouseLocation'] = $country_code;
        $data['Inventory'] = $productArray['qty'];
        if(isset($productArray['webhook'])){
            $data['ActivationMark']=true;
        }
        return $data;
       

    }
   /*
    product basic info prepare

    */
    public static function getVariantsProductBasicInfo($productArray)
    {
        $data = [];
        //$data['SellerPartNumber'] =trim('SPN9639256140');
        //$data['SellerPartNumber'] =trim('SPN'.$productArray['option_id']);
        $data['SellerPartNumber'] =trim($productArray['option_sku']);
        $data['WarehouseLocation'] = 'USA';
        $data['Inventory'] = $productArray['option_qty'];
        if(isset($productArray['webhook'])){
            $data['ActivationMark']=true;
        }
        return $data;
       

    }
     /*
    product basic info prepare

    */
    public static function getPrepareData($productArray,&$item,&$itemFeed,&$newegg_envelope,&$productUpload,$connection)
    {
       if($productArray['type']=='simple'){
                    $validateResponse = self::validateProduct($productArray,$connection);
                    $uploadProductIds[] = $productArray['id'];
                    if(isset($validateResponse['error'])){
                        $error[$productArray['sku']]=$validateResponse['error'];
                        /*continue*/;
                    } 
                $basicinfo = self::getProductBasicInfo($productArray);
                $item[]=$basicinfo;
                $itemFeed['Item']=$item;
                $newegg_envelope['Message']['Inventory'] = $itemFeed;
                $productUpload['NeweggEnvelope'] = $newegg_envelope;
                
        }
        else{
                $query = 'SELECT jet.option_id,option_title,option_sku,ngg.newegg_option_attributes,option_image,option_qty,option_price,option_weight,option_unique_id FROM `newegg_product_variants` ngg INNER JOIN `jet_product_variants` jet ON jet.option_id=ngg.option_id WHERE ngg.product_id="'.$productArray['id'].'"';
                $productVarArray = Data::sqlRecords($query,"all","select");
                $validateResponse = self::validateProduct($productArray,$connection);
                foreach($productVarArray as $value)
                {
                    $value['sku']=$productArray['sku'];
                    $basicinfo = self::getVariantsProductBasicInfo($value);
                    $uploadProductIds[] = $productArray['id'];
                    $item[]=$basicinfo;
                    $itemFeed['Item']=$item;
                    $newegg_envelope['Message']['Inventory'] = $itemFeed;
                    $productUpload['NeweggEnvelope'] = $newegg_envelope;
                    
                }

        }
           // return $productUpload;
       

    }

    public static function updateinventoryviacsv($products = [])
    {
        $newegg_envelope = array('-xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', '-xsi:noNamespaceSchemaLocation' => 'NeweggEnvelop.xsd');
        $newegg_envelope['Header'] = array('DocumentVersion' => '1.0');
        $newegg_envelope['MessageType'] = 'Inventory';
        $itemFeed = [];
        $item = [];

        if (!empty($products)) {
            foreach ($products as $product) {
                $item['SellerPartNumber']=$product['sku'];
                $item['WarehouseLocation']='USA';
                $item['Inventory']=$product['qty'];

                $itemFeed['Item'][] =$item;
            }
        }

        $newegg_envelope['Message']['Inventory'] = $itemFeed['Item'];
        $feed['NeweggEnvelope'] = $newegg_envelope;

        $obj = new Neweggapi(SELLER_ID, AUTHORIZATION, SECRET_KEY);
        $response = $obj->postRequest('/datafeedmgmt/feeds/submitfeed', ['body' => json_encode($feed),
            'append' => '&requesttype=INVENTORY_DATA']);

        return $response;
    }

    
}