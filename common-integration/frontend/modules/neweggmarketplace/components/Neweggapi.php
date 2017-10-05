<?php
namespace frontend\modules\neweggmarketplace\components;

use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Helper;
use yii\base\Response;
use frontend\modules\neweggmarketplace\components\AttributeMap;
use frontend\modules\neweggmarketplace\components\WalmartCategory;


class Neweggapi extends Component
{

    public $seller_id;
    public $authorization;
    public $secret_key;
    public $cron;
    const API_URL = 'https://api.newegg.com/marketplace';

    public function __construct($seller_id = "", $authorization = "", $secret_key = "",$cron = false)
    {
        $this->seller_id = $seller_id;
        $this->authorization = $authorization;
        $this->secret_key = $secret_key;
        $this->cron = $cron;
    }

    public function postRequest($url, $params)
    {

        if (!isset($params['append'])) {
            $params['append'] = '';
        }
        $url = self::API_URL . $url . "?sellerid=" . $this->seller_id . $params['append'];
        $headers = [];
        if (isset($params['authorization'], $params['secretKey'])) {
            $headers[] = "Authorization: " . trim($params['authorization']);
            $headers[] = "SecretKey: " . trim($params['secretKey']);
            $url = $params['url'];
        } else {
            $headers[] = "Authorization: " . trim($this->authorization);
            $headers[] = "SecretKey: " . trim($this->secret_key);
        }
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
        curl_close ($ch);
        $validJson = self::formatJson($server_output);
        return $validJson;

    }




    /**
     * Get Request on https://api.newegg.com/marketplace/
     * @param string $url
     * @param string|[] $params
     * @return string bob
     */
    public function getRequest($url = '', $params = [])
    {

        $url = self::API_URL . $url . "?sellerid=" . $this->seller_id;
        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        if (isset($params['authorization'], $params['secretKey'])) {
            $headers[] = "Authorization: " . $params['authorization'];
            $headers[] = "SecretKey: " . $params['secretKey'];
            $url = $params['url'];
        } else {
            $headers[] = "Authorization: " . $this->authorization;
            $headers[] = "SecretKey: " . $this->secret_key;
        }

        if (isset($params['body'])) {
            $putString = stripslashes($params['body']);
            $putData = tmpfile();
            fwrite($putData, $putString);
            fseek($putData, 0);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (isset($params['body'])) {
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
            curl_setopt($ch, CURLOPT_INFILE, $putData);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $serverOutput = curl_exec ($ch);
        curl_close ($ch);
        $validJson = self::formatJson($serverOutput);

        return $validJson;
    }

    public function putRequest($url, $params)
    {
        if (!isset($params['append'])) {
            $params['append'] = '';
        }
        if(!empty($this->seller_id)){
            $url = self::API_URL . $url . "?sellerid=" . $this->seller_id . $params['append'];

        }else{
            $url = self::API_URL . $url . "?sellerid=" . SELLER_ID . $params['append'];
        }

        $headers = [];
        if (isset($params['authorization'], $params['secretKey'])) {
            $headers[] = "Authorization: " . trim($params['authorization']);
            $headers[] = "SecretKey: " . trim($params['secretKey']);
            //$url = $params['url'];
        } else {
            /*$headers[] = "Authorization: " . trim(AUTHORIZATION);
            $headers[] = "SecretKey: " . trim(SECRET_KEY);*/
            $headers[] = "Authorization: " . trim($this->authorization);
            $headers[] = "SecretKey: " . trim($this->secret_key);
        }
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        if (isset($params['body'])) {
            $putString = stripslashes($params['body']);
            $putData = tmpfile();
            fwrite($putData, $putString);
            fseek($putData, 0);
        }

        $ch = curl_init();
        /*print_r($params['body']);die;*/

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
        curl_close($ch);

        $validJson = self::formatJson($server_output);
//        $log = self::log($server_output, MERCHANT_ID);
        return $validJson;

    }


    public static function formatJson($postData)
    {
        $hexdata = bin2hex($postData);
        $subvalidData = strstr($hexdata,'7b');
        $b = strrev($subvalidData);
        $data = strstr($b , 'd7');
        $mainData = strrev($data);
        $validData = hex2bin($mainData);
        return $validData;
    }

    public static function log($postData, $merchant_id)
    {
        $file_dir = dirname(\Yii::getAlias('@webroot')) . '/var/productuploadresponse/' . $merchant_id . '';
        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0775, true);
        }
        $filenameOrig = "";
        $filenameOrig = $file_dir . '/' . time() . '.json';
        $fileOrig = "";
        $fileOrig = fopen($filenameOrig, 'w+');
        fwrite($fileOrig, $postData);
        fclose($fileOrig);
    }

}
