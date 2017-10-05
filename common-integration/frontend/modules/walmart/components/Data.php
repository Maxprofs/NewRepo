<?php
namespace frontend\modules\walmart\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;
use frontend\modules\walmart\components\Walmartappdetails;

class Data extends component
{
    const FEED_MARGIN = 5;
    const TOTAL_PRODUCT_LIMIT = 10000;
    const APP_NAME_WALMART = 'walmart';

    public static function getWalmartCarriers()
    {
        return ['UPS' => 'UPS', 'USPS' => 'USPS', 'FedEx' => 'FedEx', 'Airborne' => 'Airborne', 'OnTrac' => 'OnTrac', 'Other' => 'Other'];
    }

    public static function sqlRecords($query, $type = null, $queryType = null)
    {
        $connection = Yii::$app->getDb();
        $response = [];
        if ($queryType == "update" || $queryType == "delete" || $queryType == "insert" || ($queryType == null && $type == null)) {
            $response = $connection->createCommand($query)->execute();
        } elseif ($type == 'one') {
            $response = $connection->createCommand($query)->queryOne();
        } elseif ($type == 'column') {
            $response = $connection->createCommand($query)->queryColumn();
        } else {
            $response = $connection->createCommand($query)->queryAll();
        }
        unset($connection);
        return $response;
    }

    public static function getWalmartShopDetails($merchant_id)
    {
        $shopDetails = array();
        if (is_numeric($merchant_id)) {
            $shopDetails = self::sqlRecords("SELECT `token`,`currency`,`email`,`shop_name`,`shop_url` FROM `walmart_shop_details` WHERE merchant_id=" . $merchant_id, 'one');
        }
        return $shopDetails;
    }

    public static function getUrl($path)
    {
        $url = Url::toRoute(['/walmart/' . $path]);
        return $url;
    }

    /*public static function createWebhooks($sc)
    {
        $response=$sc->call('GET','/admin/webhooks.json');
        $charge=array();
        $arr=array();
        $charge1=array();
        $charge2=array();
        $charge3=array();
        $charge4=array();
        $charge5=array();
        $charge6=array();
        $arr1=array();
        $arr2=array();
        $arr3=array();
        $arr4=array();
        $arr5=array();
        $arr6=array();
        $arr['topic']="products/update";
        $arr1['topic']="products/delete";
        $arr2['topic']="app/uninstalled";
        $arr3['topic']="orders/fulfilled";
        $arr4['topic']="orders/cancelled";
        $arr5['topic']="products/create";
        $arr6['topic']="orders/partially_fulfilled";
        $return_url="https://shopify.cedcommerce.com/jet/shopifywebhook/productupdate";
        $return_url1="https://shopify.cedcommerce.com/jet/shopifywebhook/productdelete";
        //$return_url2="https://shopify.cedcommerce.com/jet/shopifywebhook/isinstall";
        $return_url2="https://shopify.cedcommerce.com/integration/walmartwebhook/isinstall";
        $return_url3="https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment";
        $return_url4="https://shopify.cedcommerce.com/jet/shopifywebhook/cancelled";
        $return_url5="https://shopify.cedcommerce.com/jet/shopifywebhook/productcreate";
        $return_url6="https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment";
        $arr['address']=$return_url;
        $arr1['address']=$return_url1;
        $arr2['address']=$return_url2;
        $arr3['address']=$return_url3;
        $arr4['address']=$return_url4;
        $arr5['address']=$return_url5;
        $arr6['address']=$return_url6;
        $charge['webhook']=$arr;
        $charge1['webhook']=$arr1;
        $charge2['webhook']=$arr2;
        $charge3['webhook']=$arr3;
        $charge4['webhook']=$arr4;
        $charge5['webhook']=$arr5;
        $charge6['webhook']=$arr6;
        $flag=false;
        $flag1=false;
        $flag2=false;
        $flag3=false;
        $flag4=false; 
        $flag5=false;
        $flag6=false;
        if(count($response)>0 && !isset($response['errors']))
        {
            foreach ($response as $value)
            {
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/productupdate")
                {
                    $flag=true;
                }
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/productdelete")
                {
                    $flag1=true;
                }
                //if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/isinstall")
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/integration/walmartwebhook/isinstall")
                {
                    $flag2=true;
                }
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment")
                {
                    $flag3=true;
                }
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/cancelled")
                {
                    $flag4=true;
                }
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/productcreate")
                {
                    $flag5=true;
                }
                if(isset($value['address']) && $value['address']=="https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment")
                {
                    $flag6=true;
                }
            }
        }
        if(!$flag)
            $response1 = $sc->call('POST','/admin/webhooks.json',$charge);
        if(!$flag1)
            $response2 = $sc->call('POST','/admin/webhooks.json',$charge1);
        if(!$flag2)
            $response3 = $sc->call('POST','/admin/webhooks.json',$charge2);
        if(!$flag3)
            $response4 = $sc->call('POST','/admin/webhooks.json',$charge3);
         if(!$flag4)
            $response5 = $sc->call('POST','/admin/webhooks.json',$charge4);
         if(!$flag5)
            $response6 = $sc->call('POST','/admin/webhooks.json',$charge5);
        if(!$flag6)
            $response7 = $sc->call('POST','/admin/webhooks.json',$charge6);
        
        unset($arr);
        unset($arr1);
        unset($arr2);
        unset($arr3);
        unset($arr4);
        unset($arr5);
        unset($charge);
        unset($charge1);
        unset($charge2);
        unset($charge3);
        unset($charge4);
        unset($charge5);
    }*/

    /*public static function createWebhooks($sc, $shop, $token)
    {
        $urls = [
            "https://shopify.cedcommerce.com/jet/shopifywebhook/productupdate",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/productdelete",
            "https://shopify.cedcommerce.com/integration/walmartwebhook/isinstall",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/cancelled",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/productcreate",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/createorder"
        ];

        $topics = [
            "products/update",
            "products/delete",
            "app/uninstalled",
            "orders/fulfilled",
            "orders/cancelled",
            "products/create",
            "orders/partially_fulfilled",
            "orders/create"
        ];

        $otherWebhooks = self::getOtherAppsWebhooks($shop);

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
                if (!in_array($url, $otherWebhooks)) {
                    $charge = ['webhook' => ['topic' => $topics[$key], 'address' => $url]];
                    $sc->call('POST', '/admin/webhooks.json', $charge);
                }
            }
        }
    }

    public static function getOtherAppsWebhooks($shop)
    {
        $query = "SELECT `auth_key` FROM `user` WHERE `username` LIKE '" . $shop . "' LIMIT 0 , 1";
        $results = Data::sqlRecords($query, 'one');

        $webhooks = [];

        if (is_array($results) && isset($results['auth_key'])) {
            $token = $results['auth_key'];

            $jet_app_key = '5c6572757797b3edb02915535ce47d11';
            $jet_app_secret = '50110e94818b0399dc08d3c4daf2dbb5';

            try {
                $sc = new ShopifyClientHelper($shop, $token, $jet_app_key, $jet_app_secret);

                $response = $sc->call('GET', '/admin/webhooks.json');

                if (count($response) > 0 && !isset($response['errors'])) {
                    foreach ($response as $k => $value) {
                        if (isset($value['address'])) {
                            $webhooks[] = $value['address'];
                        }
                    }
                }
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
        return $webhooks;
    }*/

    public static function createWebhooks($sc, $shop, $token)
    {
        $walmartBaseUrl = "https://shopify.cedcommerce.com/integration/";
        $secureWebUrl = "https://shopify.cedcommerce.com/integration/shopifywebhook/";

        $urls = [
            $secureWebUrl . "productupdate",
            $secureWebUrl . "productdelete",
            $walmartBaseUrl . "walmartwebhook/isinstall",
            $secureWebUrl . "createshipment",
            $secureWebUrl . "cancelled",
            $secureWebUrl . "productcreate",
            $secureWebUrl . "createshipment",
            $secureWebUrl . "createorder",
        ];

        $topics = [
            "products/update",
            "products/delete",
            "app/uninstalled",
            "orders/fulfilled",
            "orders/cancelled",
            "products/create",
            "orders/partially_fulfilled",
            "orders/create"
        ];

        $otherWebhooks = self::getOtherAppsWebhooks($shop);

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
                if (!in_array($url, $otherWebhooks)) {
                    $charge = ['webhook' => ['topic' => $topics[$key], 'address' => $url]];
                    $sc->call('POST', '/admin/webhooks.json', $charge);
                }
            }
        }
    }

    public static function getOtherAppsWebhooks($shop)
    {
        $query = "SELECT `auth_key` FROM `user` WHERE `username` LIKE '" . $shop . "' LIMIT 0 , 1";
        $results = Data::sqlRecords($query, 'one');

        $webhooks = [];

        if (is_array($results) && isset($results['auth_key'])) {
            $token = $results['auth_key'];

            $jet_app_key = PUBLIC_KEY;//'5c6572757797b3edb02915535ce47d11';
            $jet_app_secret = PRIVATE_KEY;//'50110e94818b0399dc08d3c4daf2dbb5';

            try {
                $sc = new ShopifyClientHelper($shop, $token, $jet_app_key, $jet_app_secret);

                $response = $sc->call('GET', '/admin/webhooks.json');

                if (count($response) > 0 && !isset($response['errors'])) {
                    foreach ($response as $k => $value) {
                        if (isset($value['address'])) {
                            $webhooks[] = $value['address'];
                        }
                    }
                }
            } catch (Exception $e) {
                $e->getMessage();
            }
        }

        $neweggToken = self::getShopifyTokenForNewgegg($shop);
        if (is_array($neweggToken)) {
            $neweggtoken = $neweggToken['token'];

            try {
                $sc = new ShopifyClientHelper($shop, $neweggtoken, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);

                $response = $sc->call('GET', '/admin/webhooks.json');

                if (count($response) > 0 && !isset($response['errors'])) {
                    foreach ($response as $k => $value) {
                        if (isset($value['address'])) {
                            $webhooks[] = $value['address'];
                        }
                    }
                }
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
        return $webhooks;
    }

    public static function getShopifyTokenForNewgegg($shop)
    {
        $query = "SELECT `token` FROM `newegg_shop_detail` WHERE `shop_name`='{$shop}' LIMIT 0,1";
        $result = Data::sqlRecords($query, 'one');

        return $result;
    }

    public static function saveConfigValue($merchant_id, $field_name, $field_value)
    {
        $query = "SELECT `data`,`value` FROM  `walmart_config` WHERE `merchant_id`='" . $merchant_id . "' AND `data`='" . $field_name . "'";
        if (empty(self::sqlRecords($query, "one"))) {
            self::sqlRecords("INSERT INTO `walmart_config` (`data`,`value`,`merchant_id`) values('" . $field_name . "','" . $field_value . "','" . $merchant_id . "')", null, "insert");
        } else {
            self::sqlRecords("UPDATE `walmart_config` SET `value`='" . $field_value . "' where `merchant_id`='" . $merchant_id . "' AND `data`='" . $field_name . "'", null, "update");
        }
    }

    public static function getConfigValue($merchant_id, $field_name = 'all')
    {
        if ($field_name != 'all') {
            $query = "SELECT `data`,`value` FROM  `walmart_config` WHERE `merchant_id`='" . $merchant_id . "' AND `data` LIKE '" . $field_name . "'";
            $result = self::sqlRecords($query, "one");
            if (!empty($result)) {
                return $result['value'];
            }
        } else {
            $query = "SELECT `data`,`value` FROM  `walmart_config` WHERE `merchant_id`='" . $merchant_id . "'";
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

    public static function importWalmartProduct($merchant_id)
    {
        $productColl = [];
        $query = "select id,product_type,type from jet_product where merchant_id='" . $merchant_id . "'";
        $productColl = self::sqlRecords($query, 'all', 'select');
        $countVar = 0;
        if (is_array($productColl) && count($productColl) > 0) {
            $queryProduct = "INSERT INTO `walmart_product` (`product_id`,`merchant_id`,`product_type`,`status`)VALUES";
            $queryVariant = "INSERT INTO `walmart_product_variants` (`product_id`,`merchant_id`,`option_id`)VALUES";
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
            $query = "select id from `walmart_category_map` where merchant_id='" . $merchant_id . "'";
            $productTypeColl = self::sqlRecords($query, "all", 'select');
            if (!$productTypeColl) {
                $jetproductTypeColl = [];
                $query = "select product_type from `jet_category_map` where merchant_id='" . $merchant_id . "'";
                $jetproductTypeColl = self::sqlRecords($query, "all", 'select');
                if (is_array($jetproductTypeColl) && count($jetproductTypeColl) > 0) {
                    $queryProType = "INSERT INTO `walmart_category_map` (`product_type`,`merchant_id`)VALUES";
                    foreach ($jetproductTypeColl as $v) {
                        $queryProType .= "('" . addslashes($v['product_type']) . "','" . $merchant_id . "'),";
                    }
                    $queryProType = rtrim($queryProType, ',');
                    self::sqlRecords($queryProType, null, 'insert');
                }
            }
        }
    }

    public static function getShopifyShopDetails($sc)
    {
        $response = $sc->call('GET', '/admin/shop.json');
        return $response;
    }

    /**
     * Get Product Tax code
     * @param [] $product
     * @return string | bool
     */
    public static function GetTaxCode($product, $merchant_id)
    {
        $tax_code = '';
        $productType = '';
        if (is_array($product)) {
            $tax_code = $product['tax_code'];
            //$productType = $product['product_type'];
            $productType = str_replace("'", "''", $product['product_type']);
        } else {
            $tax_code = $product->tax_code;
            //$productType = $product->product_type;
            $productType = str_replace("'", "''", $product->product_type);
        }

        if (!$tax_code || $tax_code == '') {
            $query = "SELECT `tax_code` FROM `walmart_category_map` WHERE `product_type`='" . $productType . "' AND `merchant_id`=" . $merchant_id . " LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');
            if ($result && isset($result['tax_code']) && !is_null($result['tax_code']) && $result['tax_code'] != '') {
                return $result['tax_code'];
            } else {
                $query = "SELECT `value` FROM `walmart_config` WHERE `data`='tax_code' AND `merchant_id`=" . $merchant_id . " LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if ($result && isset($result['value']) && !is_null($result['value'])) {
                    return $result['value'];
                }
            }
        } else {
            if (!is_numeric($tax_code) || strlen($tax_code) != 7)
                return false;
            else
                return $tax_code;
        }
        return false;
    }

    public static function createLog($message, $path = 'walmart-common.log', $mode = 'a', $sendMail = false, $trace = false)
    {
        $file_path = Yii::getAlias('@webroot') . '/var/walmart/' . $path;
        $dir = dirname($file_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        $fileOrig = fopen($file_path, $mode);
        if ($trace) {
            try {
                throw new \Exception($message);
            } catch (Exception $e) {
                $message = $e->getTraceAsString();
            }
        }
        fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . $message);
        fclose($fileOrig);
        if ($sendMail) {

            self::sendEmail($file_path, $message);
        }
    }

    public static function writeFile($message, $path = 'walmart-common.log', $mode = 'a')
    {
        $file_path = Yii::getAlias('@webroot') . '/var/walmart/' . $path;
        $dir = dirname($file_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        $fileOrig = fopen($file_path, $mode);
        
        fwrite($fileOrig, $message);
        fclose($fileOrig);
    }

    public static function getKey($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    /**
     * function for sending mail with attachment
     */
    public static function sendEmail($file, $msg, $email = 'satyaprakash@cedcoss.com', $EmailSubject = "Walmart Shopify Cedcommerce Exception Log")
    {
        try {
            $name = 'Walmart Shopify Cedcommerce';

            $EmailTo = $email . ',kshitijverma@cedcoss.com';
            $EmailFrom = $email;
            $from = 'Walmart Shopify Cedcommerce';
            $message = $msg;
            $separator = md5(time());

            // carriage return type (we use a PHP end of line constant)
            $eol = PHP_EOL;

            // attachment name
            $filename = 'exception';//store that zip file in ur root directory
            $attachment = chunk_split(base64_encode(file_get_contents($file)));

            // main header
            $headers = "From: " . $from . $eol;
            $headers .= "MIME-Version: 1.0" . $eol;
            $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"";

            // no more headers after this, we start the body! //

            $body = "--" . $separator . $eol;
            $body .= "Content-Type: text/html; charset=\"iso-8859-1\"" . $eol . $eol;
            $body .= $message . $eol;

            // message
            $body .= "--" . $separator . $eol;
            /*  $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
            $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
            $body .= $message.$eol; */

            // attachment
            $body .= "--" . $separator . $eol;
            $body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
            $body .= "Content-Transfer-Encoding: base64" . $eol;
            $body .= "Content-Disposition: attachment" . $eol . $eol;
            $body .= $attachment . $eol;
            $body .= "--" . $separator . "--";

            // send message
            if (mail($EmailTo, $EmailSubject, $body, $headers)) {
                $mail_sent = true;
            } else {
                $mail_sent = false;
            }
        } catch (Exception $e) {

        }
    }

    /**
     * Get Option Values Simple Product
     */
    public function getOptionValuesForSimpleProduct($product)
    {
        $options = [];
        if (is_array($product) && isset($product['variants'])) {
            $variant = reset($product['variants']);
            if (isset($product['options'])) {
                foreach ($product['options'] as $value) {
                    if ($value['name'] != 'Title') {
                        $options[$value['name']] = $variant['option' . $value['position']];
                    }
                }
            }
        }

        if (count($options))
            return json_encode($options);
        else
            return '';
    }

    public static function priceChange($price, $priceType, $changePrice, $priceValueType = "increase")
    {

        $updatePrice = 0;
        if ($priceValueType == "increase") {

            if ($priceType == "percent")
                $updatePrice = (float)($price + ($changePrice / 100) * ($price));
            elseif ($priceType == "fixed")
                $updatePrice = (float)($price + $changePrice);

        } else {

            if ($priceType == "percent")
                $updatePrice = (float)($price - ($changePrice / 100) * ($price));
            elseif ($priceType == "fixed")
                $updatePrice = (float)($price - $changePrice);

        }

        return $updatePrice;
    }

    public static function getCustomPrice($price, $merchant_id)
    {
        $walmartPriceConfig = Data::sqlRecords("SELECT `value` FROM `walmart_config` WHERE merchant_id='" . $merchant_id . "' && data='custom_price'", 'one');
        if (isset($walmartPriceConfig['value']) && $walmartPriceConfig['value']) {
            $pricData = explode('-', $walmartPriceConfig['value']);
            if (is_array($pricData) && count($pricData) > 0) {
                if ($pricData[0] == "fixed")
                    return Data::priceChange($price, $pricData[0], $pricData[1], $pricData[2]);
                else
                    return Data::priceChange($price, $pricData[0], $pricData[1], $pricData[2]);
            }
        }
        return "";
    }

    public static function trimString($str, $maxLen)
    {
        if (strlen($str) > $maxLen && $maxLen > 1) {
            preg_match("#^.{1," . $maxLen . "}\.#s", $str, $matches);

            if (isset($matches[0])) {
                return $matches[0];
            } else {
                return substr($str, 0, $maxLen);
            }

        } else {
            return $str;
        }
    }

    public static function getBrand($brand)
    {
        $brandMaxLength = WalmartProductValidate::MAX_LENGTH_BRAND;
        $brand = htmlspecialchars($brand);
        if (strlen($brand) > $brandMaxLength) {
            $brand = substr($brand, 0, $brandMaxLength);
        }
        return $brand;
    }

    public static function getName($name)
    {
        $nameMaxLength = WalmartProductValidate::MAX_LENGTH_NAME;
        $name = htmlspecialchars($name);
        if (strlen($name) > $nameMaxLength) {
            $name = substr($name, 0, $nameMaxLength);
        }
        return $name;
    }

    public static function checkNonVariantAttributes($attr)
    {
        $NonVariantAttributes = ['unit'];
        foreach ($NonVariantAttributes as $attribute) {
            if (in_array($attribute, $attr)) {
                return true;
                break;
            }
        }
    }

    public function formatTime($date)
    {
        $date_create = date_create_from_format('Y-m-d H:i:s', $date);
        if ($date_create) {
            $timeStamp = $date_create->getTimestamp();
            $utcTime = date('Y-m-d\TH:i:s', $timeStamp) . substr((string)microtime(), 1, 4) . 'Z';
            return $utcTime;
        } else {
            return false;
        }
    }

    public function formatUTCTime($date)
    {
        $date_create = date_create_from_format('Y-m-d H:i:s', $date);
        if ($date_create) {
            $timeStamp = $date_create->getTimestamp();
            $utcTime = gmdate('Y-m-d\TH:i:s', $timeStamp) . substr((string)microtime(), 1, 4) . 'Z';
            return $utcTime;
        } else {
            return false;
        }
    }

    public function isAppInstalled($shop, $token)
    {
        $sc = new ShopifyClientHelper($shop, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
        $response = $sc->call('GET', '/admin/webhooks.json');
        if (isset($response['errors']))
            return false;
        else
            return true;
    }

    public function getCombinedAttributes($category_id, $parent_id = null)
    {
        $flipCatOrder = [];
        $catOrder = WalmartCategory::getCategoryOrder($category_id);
        if (is_array($catOrder) && count($catOrder)) {
            $flipCatOrder = array_flip($catOrder);
        }

        $flipParentCatOrder = [];
        if (!is_null($parent_id)) {
            $parentCatOrder = WalmartCategory::getCategoryOrder($parent_id);
            if (is_array($parentCatOrder) && count($parentCatOrder)) {
                $flipParentCatOrder = array_flip($parentCatOrder);
            }
        }

        return array_merge($flipCatOrder, $flipParentCatOrder);
    }

    /**
     * @param $barcode
     * @return bool
     */
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

    public static function getWalmartTitle($productId, $merchant_id)
    {

        $walmarttitle = '';
        $walmarttitle = Data::sqlRecords("SELECT `product_title` FROM `walmart_product` WHERE `merchant_id` ='" . $merchant_id . "' && `product_id` ='" . $productId . "'", 'one');
        return $walmarttitle;

    }

    public static function getWalmartPrice($productId, $merchant_id)
    {

        $walmartprice = Data::sqlRecords("SELECT `product_price` FROM `walmart_product` WHERE `merchant_id` ='" . $merchant_id . "' && `product_id` ='" . $productId . "'", 'one');
        if (empty($walmartprice)) {
            $walmartprice = Data::sqlRecords("SELECT `option_prices` FROM `walmart_product_variants` WHERE `merchant_id` ='" . $merchant_id . "' && `option_id` ='" . $productId . "'", 'one');
        }
        return $walmartprice;

    }

    //by shivam
    public static function getAttributevalue($merchant_id, $walmart_attribute_code, $shopify_product_type)
    {
        $attr_value = self::sqlRecords("SELECT * FROM `walmart_attribute_map` WHERE `merchant_id` = '" . $merchant_id . "' AND `walmart_attribute_code`='" . $walmart_attribute_code . "' AND `shopify_product_type`='" . addslashes($shopify_product_type) . "'", 'one');

        return $attr_value;
    }

    public static function getWamartattributecode($merchant_id, $walmart_attribute_value, $shopify_product_type)
    {
        $attr_value = self::sqlRecords("SELECT * FROM `walmart_attribute_map` WHERE `merchant_id` = '" . $merchant_id . "' AND `attribute_value`='" . addslashes($walmart_attribute_value) . "' AND `shopify_product_type`='" . addslashes($shopify_product_type) . "'", 'one');

        return $attr_value;
    }

    /**
     * get errored product info on shopify
     * @return string
     */
    public static function getCustomsku($product_id)
    {
        $custom_sku = 'ced-' . $product_id;
        return $custom_sku;
    }

    /*Get Product sku using product Id */
    public static function getProductSku($product_id)
    {
        $product_sku = Data::sqlRecords('SELECT `sku` as `sku` FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '" AND (`id` = "' . $product_id . '" OR `variant_id` = "' . $product_id . '")', 'one');
        if (empty($product_sku)) {
            $product_sku = Data::sqlRecords('SELECT `option_sku` as `sku` FROM `jet_product_variants` WHERE `merchant_id`="' . MERCHANT_ID . '" AND `option_id` = "' . $product_id . '"', 'one');
        }
        return $product_sku['sku'];
    }

    /*Get Product id using product sku */
    public static function getProductId($product_sku, $merchant_id = '')
    {
        if ($merchant_id == '') {
            $merchant_id = MERCHANT_ID;
        }

        $product_sku = Data::sqlRecords('SELECT `id` as `id` FROM `jet_product` WHERE `merchant_id`="' . $merchant_id . '" AND `sku` = "' . $product_sku . '"', 'one');
        if (empty($product_sku)) {
            $product_sku = Data::sqlRecords('SELECT `option_id` as `id` FROM `jet_product_variants` WHERE `merchant_id`="' . $merchant_id . '" AND `option_sku` = "' . $product_sku . '"', 'one');
        }
        return $product_sku['id'];
    }

    /*Get Product id using product sku */
    public static function getFulfillmentlagtime($product_id, $merchant_id)
    {
        $product_sku = Data::sqlRecords('SELECT `fulfillment_lag_time`  FROM `walmart_product` WHERE `merchant_id`="' . $merchant_id . '" AND `product_id` = "' . $product_id . '"', 'one');
        return $product_sku;
    }

    public function createDirectory($directoryPath, $permission)
    {
        if (!file_exists($directoryPath)) {
            $old_umask = umask(0);
            mkdir($directoryPath, $permission, true);
            umask($old_umask);
        }
    }

    /*Get walmart Pricing data  using product sku */
    public static function getWalmartPricing($product_sku)
    {
        $product_sku = Data::sqlRecords('SELECT  * FROM `walmart_product_pricing` WHERE `merchant_id`="' . MERCHANT_ID . '" AND `sku` = "' . $product_sku . '"', 'one');
        return $product_sku;
    }

    /**
     * preapre curl request for ios and android notification
     */
    public function curlrequest($url, $curtRequestParams, $merchant_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $curtRequestParams,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
        $resp = curl_exec($curl);
        var_dump($resp);
        die('hjh');
        curl_close($curl);
        $file_dir = \Yii::getAlias('@webroot') . '/var/walmart/notification/' . $merchant_id . '/' . date("Y-m-d H:i:s");
        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0775, true);
        }
        $filenameOrig = "";
        $filenameOrig = $file_dir . '/' . $merchant_id . '.log';
        $fileOrig = "";
        $fileOrig = fopen($filenameOrig, 'w+');
        // $handle = fopen($file_dir.'/'.$data['id'].'-qty-price.log', 'w+');
        fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . json_encode($resp));
        fclose($fileOrig);
        return;
    }

    /**
     * Check feed send count are remaining or not
     */
    public static function checkFeedStatus($merchant_id)
    {
        $returnArray = [];
        $query = "SELECT `last_feed_time`,`feed_count` FROM `walmart_cron_feed` WHERE merchant_id='" . $merchant_id . "'";
        $checkFeedStatus = self::sqlRecords($query, 'one', 'select');
        if (!empty($checkFeedStatus)) {
            $feed_send_margin = self::FEED_MARGIN;
            $date = $checkFeedStatus['last_feed_time'];
            $currentDate = strtotime($date);
            $futureDate = $currentDate + (60 * $feed_send_margin) + (60 * 60);
            $formatDate = date("Y-m-d H:i:s", $futureDate);
            if (strtotime($formatDate) >= strtotime(date("Y-m-d H:i:s"))) {
                if ($checkFeedStatus['feed_count'] < 10) {
                    $returnArray['success'] = true;
                } else {
                    $returnArray['limit_cross'] = false;
                }
            } else {
                $update = "UPDATE `walmart_cron_feed` SET `last_feed_time`='" . date("Y-m-d H:i:s") . "',`feed_count`='0' WHERE `merchant_id`='" . $merchant_id . "'";
                Data::sqlRecords($update, null, 'update');
                $returnArray['success'] = false;
            }

        } else {
            $returnArray['notsave'] = false;
        }
        return $returnArray;


    }

    /**
     * Check feed send count are remaining or not
     */
    public static function getConfiguration($merchant_id)
    {
        $query = "SELECT `wsd`.`currency`,`wc`.`consumer_id`,`wc`.`secret_key` FROM `walmart_configuration` wc INNER JOIN `walmart_shop_details` wsd ON `wsd`.`merchant_id`=`wc`.`merchant_id` WHERE `wc`.`merchant_id`='" . $merchant_id . "'";
        $getConfiguration = self::sqlRecords($query, 'one', 'select');
        return $getConfiguration;
    }

    /**
     * 1 hrs Feed count
     */
    public static function isSendPriceFeed($merchant_id)
    {
        $query = "SELECT `last_feed_time`,`feed_count` FROM `walmart_cron_feed` WHERE merchant_id='" . $merchant_id . "'";
        $data = self::sqlRecords($query, 'one', 'select');
        return $data;
    }

    public function getMerchantIdFromShop($shop_name)
    {
        $query = "SELECT `merchant_id` FROM `walmart_shop_details` WHERE `shop_url` LIKE '{$shop_name}' LIMIT 0,1";
        $data = self::sqlRecords($query, 'one', 'select');

        if ($data) {
            return $data['merchant_id'];
        } else {
            return false;
        }
    }

    /*Get Product Type From varient Id in walmart*/
    public static function getProductType($unique_id, $merchant_id)
    {
        $query = "SELECT `type` FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "' and `variant_id`='" . $unique_id . "' LIMIT 0,1";
        $data = self::sqlRecords($query, 'one', 'select');

        if (isset($data['type']) && !empty($data['type'])) {
            return $data['type'];
        } else {
            return false;
        }
    }

    public static function sendCurlRequest($data = [], $url = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    /*
    * function for creating log
    */
    public static function createExceptionLog($functionName, $msg, $shopName = 'common')
    {
        $dir = \Yii::getAlias('@webroot') . '/var/walmart/exceptions/' . $functionName . '/' . $shopName;
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        try {
            throw new \Exception($msg);
        } catch (\yii\db\Exception $e) {
            $filenameOrig = $dir . '/' . time() . '.txt';
            $handle = fopen($filenameOrig, 'a');
            $msg = date('d-m-Y H:i:s') . "\n" . $msg . "\n" . $e->getTraceAsString();
            fwrite($handle, $msg);
            fclose($handle);
            //$this->sendEmail($filenameOrig,$msg);   
        }
    }

    public static function getmerchantMessage($merchant_id)
    {
        $message = '';
        $shop = Yii::$app->user->identity->username;

        $obj = new Walmartappdetails();
        if ($obj->appstatus($shop) == false) {
            //return $this->redirect('https://apps.shopify.com/walmart-marketplace-integration');
            $message = 'uninstalled';
        }

        $auth = Walmartappdetails::authoriseAppDetails($merchant_id, $shop);
        if (isset($auth['status']) && !$auth['status']) {
            if (isset($auth['purchase_status']) && $auth['purchase_status'] == 'license_expired') {

                $message = 'license expired';
            } elseif (isset($auth['purchase_status']) && $auth['purchase_status'] == 'trial_expired') {
                $message = 'trial expired';

            } else {
                $message = $auth['message'];
            }
        }
        return $message;
    }

    public static function getInventoryData($merchant_id)
    {
        $syncConfigJson = self::getConfigValue($merchant_id, 'sync-fields');
        if ($syncConfigJson) {
            $syncFields = json_decode($syncConfigJson, true);
            if (isset($syncFields['qty']) && $syncFields['qty'] == '1') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function updateProductCount($merchant_id)
    {

        if (!$merchant_id) {
            $merchant_id = Yii::$app->user->identity->id;
        }
        $shopDetails = Data::getWalmartShopDetails($merchant_id);
        $published_status = Data::getConfigValue($merchant_id, 'import_product_option');
        $sc = new ShopifyClientHelper($shopDetails['shop_url'], $shopDetails['token'], WALMART_APP_KEY, WALMART_APP_SECRET);
        $countProducts = $sc->call('GET', '/admin/products/count.json', ['published_status' => $published_status]);
        $limit = 5;

        $pages = ceil($countProducts / $limit);
        $prod_count = 0;
        for ($i = 0; $i <= $pages; $i++) {
            $products = $sc->call('GET', '/admin/products.json', ['published_status' => $published_status, 'limit' => $limit, 'page' => $i]);
            foreach ($products as $prod) {

                foreach ($prod['variants'] as $variant) {
                    $prod_count++;
                }

            }
        }
        print_r($prod_count);
        die;

    }
    /**
    * check threshold limit
    * @var $data send only inventory value eg:10
    * @return bool
    **/

    public static function checkThresholdValue($data,$merchant_id)
    {
        $threshold_value = self::getConfigValue($merchant_id,'inventory');
        if($threshold_value){
            if($data){
                if($threshold_value>$data){
                    return false;
                }
                return true;
            }
        }
        return true;  
    }
}

?>