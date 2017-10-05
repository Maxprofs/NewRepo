<?php
namespace frontend\modules\walmart\components;

use Yii;
use yii\base\Component;
use common\models\User;
use frontend\modules\walmart\models\AppStatus;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartExtensionDetail as Detail;

class Walmartappdetails extends Component
{
    public static function isValidateapp($merchant_id)
    {
        try
        {
            $query = "SELECT `merchant_id`,`expire_date`,`status` FROM `walmart_extension_detail` WHERE `merchant_id`='".$merchant_id."'";
            $model = Data::sqlRecords($query, 'one');
            if($model)
            {
                $expdate = strtotime($model['expire_date']);

                if(time() > $expdate)
                {
                    if($model['status'] == Detail::STATUS_PURCHASED)
                    {
                        $sql = "UPDATE `walmart_extension_detail` SET `status`='".Detail::STATUS_LICENSE_EXPIRED."' WHERE `merchant_id`='".$merchant_id."'";
                        $result = Data::sqlRecords($sql, null, 'update');
                    }
                    elseif($model['status'] == Detail::STATUS_NOT_PURCHASED)
                    {
                        $sql = "UPDATE `walmart_extension_detail` SET `status`='".Detail::STATUS_TRIAL_EXPIRED."' WHERE `merchant_id`='".$merchant_id."'";
                        $result = Data::sqlRecords($sql, null, 'update');
                        return "trial_expired";
                    }
                    elseif ($model['status'] == Detail::STATUS_TRIAL_EXPIRED)
                    {
                        return 'trial_expired';
                    }
                    return "expire";
                }

            }
        }
        catch(Exception $e)
        {
            return false;
        }
        return false;
    }

    public static function appstatus($shop,$connection=null){
        $query="";
        $model="";
        $queryObj="";
        $query = "SELECT `status` FROM `walmart_shop_details` WHERE `shop_url`='".$shop."'";

        if($connection){
            $queryObj = $connection->createCommand($query);
            $model = $queryObj->queryOne();
        } else {
            $model = Data::sqlRecords($query, 'one');
        }
        if(!$model || ($model && $model['status']==0)){
            return false;
        }
        return true;
    }

    public function autologin()
    {
        $merchant_id= \Yii::$app->user->identity->id;
        $url="";
        $shop="";
        $model="";
        $model1="";
        if(isset($_SERVER['HTTP_REFERER']))
        {
            $url=parse_url($_SERVER['HTTP_REFERER']);
            if(isset($url['host']) && $url['host']!="shopify.cedcommerce.com")
            {
                $shop=$url['host'];
                $model=User::find()->where(['username'=>$shop])->one();
                if($model)
                {
                    return $shop;
                }
            }
        }
        //}
    }

    public static function getConfig($connection=null)
    {
        $cron_array = array();
        $query = "SELECT shop.merchant_id, consumer_id, secret_key, shop_url, token, email FROM `walmart_configuration` config INNER JOIN `walmart_shop_details` shop ON (shop.merchant_id = config.merchant_id)";
        $model = Data::sqlRecords($query, 'all');
        foreach($model as $Config)
        {
            $shop = $Config['shop_url'];
            $returnStatus = self::appstatus($shop);
            $isValidate = self::isValidateapp($Config['merchant_id']);

            if(!$returnStatus || $isValidate=="expire" || $isValidate=="trial_expired")
                continue;
            $cron_array[$Config['merchant_id']]= $Config;
        }

        $cron_array['1017'] = [
                                "merchant_id" => "1017",
                                "consumer_id" => "5a1a646a-63d6-4e3a-8c13-5f89c5b6d457",
                                "secret_key" => "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKU+VAA0YDBwF6Kn1echisKj/vziAjf+D/ImWLaxbJtLDHuEf1ATp06HQrgmc8Wkd7a5EzBURAhNCdncr/4xJ0+v3smbaHIPqF7FF19QHqYmKPyeHjsZ7p2VTQkDdlS42PvNCjp6GsP0XW2goCwb5hPy0iHGq+KwIhKsS/Z0mTFzAgMBAAECgYA7stnsPP/nYAfZ9uLbnw2fploQCKMekYY3SM1SK6V+MU3wLf1E8+TFBS8AkrvO0s2BUTnygu8VRKfjcsyOfDmkBqtonAGlLgWJQVMMOYxonM6TYehQ6VkjvcWqxhWEEclolV8nQglonc4DSG5JamOr2VJba7EbD73vkEOYFMgf4QJBANhQbXM3rcGtpcH/OWFn+kHZinUoydXdGYs44AKZTmxSj1TZZqE9Pdc3nX8bZiIdHZIvk7JP4dfxXaEDCMV26mMCQQDDj0aoa5J/GilaJpZZ4IYOxIowtBs8rB5BJAmQci1OVhxi1CZG8p9IRUwQ7/pPZ93P8bP0PdREfC8JJzZcCkGxAkEAhkUmKyacjGQlR84M6BGKneVStHalEkMz399l7TcMHuEAZ0KrGdrR0A2NjaCMPRClkmBF5aEAJvKDk7Y2c5vk0QJACSMKlBfiklmwkOu4np5k5Q+9vSTNGPqZt0VtxPfwsfZIAT4UQ8BWPNQwB6KOuWMK9ApA9CpnXoPG1tCkM4yvIQJBAJJK+idW7A3pBT4CmrfT9MpxSDkk0t8DsAjaEscupKfxPPhzsfrxMjaxSNkwbq0UuiqQSQTYGDt0a7gV8BIx4yc=",
                                "shop_url" => "chic-boutique-and-gift-emporium.myshopify.com",
                                "token" => "ac4360b65156d04e0a3c19049762180a",
                                "email" => "bobbie@chicboutiqueandgiftemporium.com"
                            ];
        return $cron_array;
    }

    public static function getMerchants($connection=null)
    {
        $cron_array = array();
        $query = "SELECT `merchant_id`/*,`shop_url`*/ FROM `walmart_shop_details` WHERE `status`='1'";
        $model = Data::sqlRecords($query, 'all');
        if(is_array($model))
        {
            foreach($model as $Config)
            {
                $isValidate = self::isValidateapp($Config['merchant_id']);

                if($isValidate=="expire" || $isValidate=="trial_expired")
                    continue;

                $cron_array[$Config['merchant_id']]= $Config;
            }
        }
        $cron_array['1017'] = ['merchant_id'=>'1017'];
        return $cron_array;
    }

    public function getWalmartAPiDetails($merchant_id)
    {
        $query = "SELECT `consumer_id`,`secret_key` FROM `walmart_configuration` WHERE `merchant_id`='".$merchant_id."'";
        $apiData = Data::sqlRecords($query, 'one');
        if($apiData) {
            return $apiData;
        } else {
            return [];
        }
    }

    public static function isAppConfigured($merchant_id)
    {
        if(!is_numeric($merchant_id) || is_null($merchant_id))
            return false;

        $query="SELECT `consumer_id` FROM `walmart_configuration` WHERE `merchant_id`=$merchant_id";
        $model = Data::sqlRecords($query, 'one');
        if($model)
            return true;
        else
            return false;
    }

    public static function validateApiCredentials($consumer_id, $secret_key, $consumer_channel_type_id)
    {
        $session = Yii::$app->session;

        if($consumer_id == '' || $secret_key == '')
            return false;

        if(!isset($session['walmart_configured'])) {

            $walmartAPi = new Walmartapi($consumer_id, $secret_key, $consumer_channel_type_id);
            $itemsResult = $walmartAPi->getItems();


            if(isset($itemsResult['ns2:errors'])) {
                /*if(isset($itemsResult['ns2:errors']['ns2:error']['ns2:code']) &&
                    $itemsResult['ns2:errors']['ns2:error']['ns2:code']=='UNAUTHORIZED.GMP_GATEWAY_API')
                    {*/
                return false;
                //}
            } elseif(isset($itemsResult['errors'])) {
                return false;
            } elseif(isset($itemsResult['MPItemView'])) {
                $session->set('walmart_configured', true);
                return true;
            } elseif(isset($itemsResult['error'])) {
                if(is_array($itemsResult['error'])) {
                    foreach ($itemsResult['error'] as $error) {
                        if(isset($error['code']) && $error['code'] == 'CONTENT_NOT_FOUND.GMP_ITEM_QUERY_API')
                        {
                            return true;
                        }
                    }
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
        return false;
    }

    public static function authoriseAppDetails($merchant_id, $shop)
    {
        $session = Yii::$app->session;

        $return = array('status' => true, 'message' => '');

        if(!isset($session['walmart_installed'])) {
            if(self::appstatus($shop))
                $session->set('walmart_installed', true);
            else {
                $msg = 'Please install app to continue walmart integration for your shop store';
                $return['status'] = false;
                $return['message'] = $msg;
            }
        }

        if(!isset($session['walmart_validateapp'])) {
            $isValidate = self::isValidateapp($merchant_id);

            if($isValidate == 'expire') {
                $msg = 'We would like to inform you that your app subscription has been expired. ';
                $msg .= 'Please renew the subscription to use the app services.';
                $return['status'] = false;
                $return['message'] = $msg;
                $return['purchase_status'] = 'license_expired';
            }
            elseif($isValidate == 'trial_expired')
            {

                $msg = 'We would like to inform you that your app trial period has been expired. ';
                $msg .= 'Please choose Payment plan to continue using app services';
                $return['status'] = false;
                $return['message'] = $msg;
                $return['purchase_status'] = 'trial_expired';
            }
            else
                $session->set('walmart_validateapp', true);
        }

        return $return;
    }
}
?>