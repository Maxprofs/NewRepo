<?php
namespace frontend\modules\neweggcanada\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Url;

class Data extends Component
{
    const MAX_LENGTH_BRAND = 4000;
    const MAX_LENGTH_LONG_DESCRIPTION = 4000;
    const ATTRIBUTE_SEPERATER = '->';
    const MAX_LENGTH_NAME = 200;
    const STATUS_INSTALL = 1;
    const STATUS_UNINSTALL = 0;
    const PURCHASE_STATUS_TRAIL = 'Trail';
    const PURCHASE_STATUS_TRAILEXPIRE = 'Trail Expired';
    const PURCHASE = 'Purchased';
    const NOT_PURCAHSE = 'Not Purchased';
    const LIECENCE_EXPIRED = 'License Expired';

    /* Product constant */
    const PRODUCT_STATUS_NOT_UPLOADED = 'Not Uploaded';
    const PRODUCT_STATUS_SUBMITTED = 'SUBMITTED';
    const PRODUCT_STATUS_ACTIVATED = 'ACTIVATED';
    const PRODUCT_STATUS_UPLOADED_WITH_ERROR = 'UPLOADED WITH ERROR';
    const PRODUCT_STATUS_DEACTIVATED = 'DEACTIVATED';
    const FEED_STATUS_COMPLETED = 'COMPLETED';
    const PRODUCT_STATUS_PARTIAL_UPLOAD = 'Partial Uploaded';

    /* Order constant */
    const ORDER_STATUS_SHIPPED = "Shipped";
    const ORDER_STATUS_UNSHIPPED = "Unshipped";
    const ORDER_STATUS_VOIDED = "Voided";
    const ORDER_STATUS_INVOICED = "Invoiced";

    /* manufacturer const*/
    const MANUFACTURER_PENDING_STATUS = "PENDING";
    const MANUFACTURER_COMPLETE_STATUS = "COMPLETED";


     /* feed const*/
    const FEED_PRODUCT_UPLOAD = "Product Upload";
    const FEED_PRODUCT_STATUS = "Product Status";
    const FEED_PRODUCT_IMAGE = "Product Image Update";

    /* store id */
    const NEWEGG_US_STORE_ID = "0";
    const NEWEGG_CAN_STORE_ID = "1";

    const TOTAL_PRODUCT_LIMIT = "10000";
    const APP_NAME_NEWEGG_CAN = "newegg_can";

    public static function createWebhooks($sc, $shop, $token)
    {
        return true;

        $urls = [
            "https://shopify.cedcommerce.com/integration/shopifywebhook/productupdate",
            "https://shopify.cedcommerce.com/integration/shopifywebhook/productdelete",
            "https://shopify.cedcommerce.com/integration/neweggcanada/newegg-webhook/isinstall",
            "https://shopify.cedcommerce.com/integration/shopifywebhook/createshipment",
            "https://shopify.cedcommerce.com/integration/neweggcanada/shopifywebhook/cancelled",
            "https://shopify.cedcommerce.com/integration/shopifywebhook/productcreate",
            "https://shopify.cedcommerce.com/integration/shopifywebhook/createshipment",
        ];

        $topics = [
            "products/update",
            "products/delete",
            "app/uninstalled",
            "orders/fulfilled",
            "orders/cancelled",
            "products/create",
            "orders/partially_fulfilled",
        ];

        $otherWebhooks = self::getOtherAppsWebhooks($shop, $token);
        $response = $sc->call('GET', '/admin/webhooks.json');

        if (count($response) > 0 && !isset($response['errors'])) {
            foreach ($urls as $key => $url) {
                $continueFlag = false;
                foreach ($response as $k => $value) {
                    if (isset($value['address']) && ($value['address'] == $url || in_array($value['address'], $otherWebhooks))) {
                        $continueFlag = true;
                        unset($response[$k]);
                        break;
                    }
                }

                if (!$continueFlag) {
                    $charge = ['webhook' => ['topic' => $topics[$key], 'address' => $url]];
                    $sc->call('POST', '/admin/webhooks.json', $charge);
                }
            }
        } else {
            foreach ($urls as $key => $url) {
                $charge = ['webhook' => ['topic' => $topics[$key], 'address' => $url]];
                $sc->call('POST', '/admin/webhooks.json', $charge);
            }
        }
    }

    public static function getOtherAppsWebhooks($shop, $token)
    {
        $webhooks = [];
        $jet_app_key = '9734f2dc206eacd36b36ece7f020091a';
        $jet_app_secret = 'eecac101ee8e176bdc541f9aa04936f4';
        $sc = new ShopifyClientHelper($shop, $token, $jet_app_key, $jet_app_secret);

        $response = $sc->call('GET', '/admin/webhooks.json');

        if (count($response) > 0 && !isset($response['errors'])) {
            foreach ($response as $k => $value) {
                if (isset($value['address'])) {
                    $webhooks[] = $value['address'];
                }
            }
        }
        return $webhooks;
    }

    public static function getShopifyShopDetails($sc)
    {
        $response = $sc->call('GET', '/admin/shop.json');
        return $response;
    }

    public static function sqlRecords($query,$type=null,$queryType=null)
    {
        $connection=Yii::$app->getDb();
        $response=[];
        if($queryType=="update" || $queryType=="delete" || $queryType=="insert" || ($queryType==null && $type==null))            
            $response = $connection->createCommand($query)->execute();    
        elseif($queryType=='column') 
            $response = $connection->createCommand($query)->queryColumn();        
        elseif($type=='one')
            $response=$connection->createCommand($query)->queryOne();        
        else
            $response=$connection->createCommand($query)->queryAll();        
        unset($connection);
        return $response;
    }

    public static function getUrl($path)
    {
        $url = Url::toRoute(['/neweggcanada/' . $path]);
        return $url;
    }

    public static function importNeweggProduct($merchant_id)
    {
        $productColl = [];
        $query = "select id,product_type,type from jet_product where merchant_id='" . $merchant_id . "'";
        $productColl = self::sqlRecords($query, 'all', 'select');
        $countVar = 0;
        if (is_array($productColl) && count($productColl) > 0) {
            $queryProduct = "INSERT INTO `newegg_can_product` (`product_id`,`merchant_id`,`shopify_product_type`,`upload_status`)VALUES";
            $queryVariant = "INSERT INTO `newegg_can_product_variants` (`product_id`,`merchant_id`,`option_id`)VALUES";
            foreach ($productColl as $value) {
                $queryProduct .= "('" . $value['id'] . "','" . $merchant_id . "','" . addslashes($value['product_type']) . "','Not Uploaded'),";
                if ($value['type'] == "variants") {
                    $query = "select option_id from jet_product_variants where product_id='" . $value['id'] . "'";
                    $productVarColl = [];
                    $productVarColl = self::sqlRecords($query, 'all', 'select');

                    foreach ($productVarColl as $val) {
                        $countVar++;
                        $queryVariant .= "('" . $value['id'] . "','" . $merchant_id . "','" . $val['option_id'] . "'),";
                    }
                }
            }
            $queryProduct = rtrim($queryProduct, ',');
            $queryVariant = rtrim($queryVariant, ',');
            self::sqlRecords($queryProduct, null, 'insert');
            if ($countVar > 0)
                self::sqlRecords($queryVariant, null, 'insert');
            //insert product type
            $productTypeColl = [];
            $query = "select id from `newegg_can_category_map` where merchant_id='" . $merchant_id . "'";
            $productTypeColl = self::sqlRecords($query, "all", 'select');
            if (!$productTypeColl) {
                $jetproductTypeColl = [];
                $query = "select product_type from `jet_category_map` where merchant_id='" . $merchant_id . "'";
                $jetproductTypeColl = self::sqlRecords($query, "all", 'select');
                if (is_array($jetproductTypeColl) && count($jetproductTypeColl) > 0) {
                    $queryProType = "INSERT INTO `newegg_can_category_map` (`product_type`,`merchant_id`)VALUES";
                    foreach ($jetproductTypeColl as $v) {
                        $queryProType .= "('" . addslashes($v['product_type']) . "','" . $merchant_id . "'),";
                    }
                    $queryProType = rtrim($queryProType, ',');
                    self::sqlRecords($queryProType, null, 'insert');
                }
            }
        }
    }

    public static function getKey($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    public static function getneweggConfig($merchant_id)
    {
        $neweggConfig=array();
        $neweggConfig=self::sqlRecords("SELECT `data`,`value` from `newegg_can_config` where merchant_id='".$merchant_id."'",'all','select');
        return $neweggConfig;
    }

    /**
     * function for sending mail with attachment
     */
    public static function sendEmail($file,$msg,$email = 'satyaprakash@cedcoss.com'){
        try{
            $name = 'Newegg Shopify Cedcommerce';

            $EmailTo = $email.',shivamverma@cedcoss.com, ankitsingh@cedcoss.com';
            $EmailFrom = $email;
            $EmailSubject = "Newegg Shopify Cedcommerce Exception Log" ;
            $from ='Newegg Shopify Cedcommerce';
            $message = $msg;
            $separator = md5(time());

            // carriage return type (we use a PHP end of line constant)
            $eol = PHP_EOL;

            // attachment name
            $filename = 'exception';//store that zip file in ur root directory
            $attachment = chunk_split(base64_encode(file_get_contents($file)));

            // main header
            $headers  = "From: ".$from.$eol;
            $headers .= "MIME-Version: 1.0".$eol;
            $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

            // no more headers after this, we start the body! //

            $body = "--".$separator.$eol;
            $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol.$eol;
            $body .= $message.$eol;

            // message
            $body .= "--".$separator.$eol;
            /*  $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
             $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
             $body .= $message.$eol; */

            // attachment
            $body .= "--".$separator.$eol;
            $body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
            $body .= "Content-Transfer-Encoding: base64".$eol;
            $body .= "Content-Disposition: attachment".$eol.$eol;
            $body .= $attachment.$eol;
            $body .= "--".$separator."--";

            // send message
            if (mail($EmailTo, $EmailSubject, $body, $headers)) {
                $mail_sent = true;
            } else {
                $mail_sent = false;
            }
        }
        catch(Exception $e)
        {


        }
    }

    public static function getNeweggShopDetails($merchant_id)
    {      
        $shopDetails = array();
        if(is_numeric($merchant_id)) {
            $shopDetails = self::sqlRecords("SELECT * FROM `newegg_can_shop_detail` WHERE merchant_id=".$merchant_id, 'one');
        }
        return $shopDetails;
    }

    public static function convertWeight($weight="",$unit=""){
        $newWeight=0;
        if($unit=='kg'){
            $newWeight = (float)($weight*2.2046226218);
            return $newWeight;
        }
        if($unit=='g'){
            $newWeight = (float)($weight*0.0022046226218);
            return $newWeight;
        }
        if($unit=='oz'){
            $newWeight = (float)($weight/16);
            return $newWeight;
        }
        if($unit=='lb'){
            return $weight;
        }
        else{
            return "";
        }
    }
    
      /**
     * Get Option Values Simple Product
     */
    public function getOptionValuesForSimpleProduct($product)
    {
        $options = [];
       
        $variant = reset($product['variants']);
        if(isset($product['options'])) {
            foreach ($product['options'] as $value) {
                if($value['name'] != 'Title') {
                    $options[$value['name']] = $variant['option'.$value['position']];
                }
            }
        }
        if(count($options))
            return json_encode($options);
        else
            return '';
    }

    /*send curl request*/
    public static function sendCurlRequest($data=[],$url="")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $merchant_id
     * @param $field_name
     * @param $field_value
     */
    public static function saveConfigValue($merchant_id, $field_name, $field_value)
    {
        $query = "SELECT `data`,`value` FROM  `newegg_can_config` WHERE `merchant_id`='" . $merchant_id . "' AND `data`='" . $field_name . "'";
        if (empty(self::sqlRecords($query, "one"))) {
            self::sqlRecords("INSERT INTO `newegg_can_config` (`data`,`value`,`merchant_id`) values('" . $field_name . "','" . $field_value . "','" . $merchant_id . "')", null, "insert");
        } else {
            self::sqlRecords("UPDATE `newegg_can_config` SET `value`='" . $field_value . "' where `merchant_id`='" . $merchant_id . "' AND `data`='" . $field_name . "'", null, "update");
        }
    }

    /**
     * @param $merchant_id
     * @param string $field_name
     * @return array|bool|mixed
     */
    public static function getConfigValue($merchant_id, $field_name = 'all')
    {
        if ($field_name != 'all') {
            $query = "SELECT `data`,`value` FROM  `newegg_can_config` WHERE `merchant_id`='" . $merchant_id . "' AND `data` LIKE '" . $field_name . "'";
            $result = self::sqlRecords($query, "one");
            if (!empty($result)) {
                return $result['value'];
            }
        } else {
            $query = "SELECT `data`,`value` FROM  `newegg_can_config` WHERE `merchant_id`='" . $merchant_id . "'";
            $result = self::sqlRecords($query, "all");
            if (empty($result)) {
                $config = [];
                foreach ($result as $value) {
                    $config[$value['data']] = $value['value'];
                }
                return $config;
            }
        }
        return false;
    }

    public static function validateUpc($barcode)
    {
        if (preg_match('/[^0-9]/', $barcode)) {
            // is not numeric
            return false;
        }
        // pad with zeros to lengthen to 14 digits
        switch (strlen($barcode)) {
            case 8:
                $barcode = "000000" . $barcode;
                break;
            case 12:
                $barcode = "00" . $barcode;
                break;
            case 13:
                $barcode = "0" . $barcode;
                break;
            case 14:
                break;
            default:
                // wrong number of digits
                return false;
        }
        // calculate check digit
        $a = '';
        $a[0] = (int)($barcode[0]) * 3;
        $a[1] = (int)($barcode[1]);
        $a[2] = (int)($barcode[2]) * 3;
        $a[3] = (int)($barcode[3]);
        $a[4] = (int)($barcode[4]) * 3;
        $a[5] = (int)($barcode[5]);
        $a[6] = (int)($barcode[6]) * 3;
        $a[7] = (int)($barcode[7]);
        $a[8] = (int)($barcode[8]) * 3;
        $a[9] = (int)($barcode[9]);
        $a[10] = (int)($barcode[10]) * 3;
        $a[11] = (int)($barcode[11]);
        $a[12] = (int)($barcode[12]) * 3;
        $sum = $a[0] + $a[1] + $a[2] + $a[3] + $a[4] + $a[5] + $a[6] + $a[7] + $a[8] + $a[9] + $a[10] + $a[11] + $a[12];
        $check = (10 - ($sum % 10)) % 10;
        // evaluate check digit
        $last = (int)($barcode[13]);
        return $check == $last;
    }

    public static function getProductData($product_id)
    {
        try
        {
            $query = 'SELECT product_id,title,sku,type,newegg.product_price,newegg.shopify_product_type,newegg.upload_status,description,image,qty,price,weight,vendor,upc,short_description FROM (SELECT * FROM `newegg_can_product` WHERE `product_id` ="'.$product_id.'") as newegg INNER JOIN (SELECT * FROM `jet_product` WHERE `id` ="'.$product_id.'") as jet ON jet.id=newegg.product_id WHERE newegg.product_id="'.$product_id.'" LIMIT 1';
            $product = Data::sqlRecords($query,"one","select");

        }
        catch(\Exception $e){
            return false;
        }
        return $product;
    }

    public static function getProductVariants($product_id)
    {
        try
        {
            $query = 'SELECT jet.option_id,option_title,option_sku,newegg.upload_status,option_image,option_qty,option_price,option_weight,option_unique_id FROM (SELECT * FROM `newegg_can_product_variants` WHERE `product_id` ="'.$product_id.'") as newegg INNER JOIN (SELECT * FROM `jet_product_variants` WHERE `product_id` ="'.$product_id.'") as jet ON jet.option_id=newegg.option_id WHERE newegg.product_id="'.$product_id.'"';
            $variants = Data::sqlRecords($query,"all","select");
        }
        catch(\Exception $e){
            return false;
        }
        return $variants;
    }

    /**
     * get errored product info on shopify
     * @return string
    */
    public static function getCustomsku($product_id)
    {
        $custom_sku = 'ced-'.$product_id;
        return $custom_sku;
    }
    
    public static function trimString($str, $maxLen)
    {
        if (strlen($str) > $maxLen && $maxLen > 1) {
            preg_match("#^.{1," . $maxLen . "}\.#s", $str, $matches);

            if(isset($matches[0])) {
                return $matches[0];
            } else {
                return substr($str, 0, $maxLen);
            }

        } else {
            return $str;
        }
    }

    public static function createLog($message, $path = 'walmart-common.log', $mode = 'a', $sendMail = false,$trace = false)
    {
        $file_path = Yii::getAlias('@webroot') . '/var/neweggcanada/' . $path;
        $dir = dirname($file_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        $fileOrig = fopen($file_path, $mode);
        if($trace){
            try{
                throw new \Exception($message);
            }
            catch(Exception $e){
                $message = $e->getTraceAsString();
            }
        }
        fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . $message);
        fclose($fileOrig);
        if ($sendMail) {

            self::sendEmail($file_path, $message);
        }
    }

    /* 
     * function for creating log 
     */
    public function createExceptionLog($functionName,$msg,$shopName = 'common')
    {
        $dir = \Yii::getAlias('@webroot').'/var/newegg/exceptions/'.$functionName.'/'.$shopName;
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        try
        {
            throw new Exception($msg);
        }catch(Exception $e){
            $filenameOrig = $dir.'/'.time().'.txt';
            $handle = fopen($filenameOrig,'a');
            $msg = date('d-m-Y H:i:s')."\n".$msg."\n".$e->getTraceAsString();
            fwrite($handle,$msg);
            fclose($handle);
            //$this->sendEmail($filenameOrig,$msg);   
        }
        
    }

    /*get merchant_id Through ShopName*/
    public function getMerchantIdFromShop($shop_name)
    {
        $query = "SELECT `merchant_id` FROM `newegg_can_shop_detail` WHERE `shop_url` LIKE '{$shop_name}' LIMIT 0,1";
        $data = self::sqlRecords($query,'one','select');
        
        if($data) {
            return $data['merchant_id'];
        } else {
            return false;
        }
    }
}