<?php
namespace frontend\modules\neweggmarketplace\components;

use Yii;
use yii\base\Component;

class Cronrequest extends Component
{
    public $seller_id;
    public $authorization;
    public $secret_key;
    public $apiPrivateKey;
    public $apiSignature;

    const API_URL = 'https://api.newegg.com/marketplace';

    public function __construct($seller_id = "", $authorization = "", $secret_key = "")
    {
        $this->seller_id = $seller_id;
        $this->authorization = $authorization;
        $this->secret_key = $secret_key;
    }

    public static function postRequest($url, $params)
    {
//        $merchant_id = Yii::$app->user->id;

        if (!isset($params['append'])) {
            $params['append'] = '';
        }
        if(isset($params['seller_id'])){
            $url = self::API_URL . $url . "?sellerid=" . $params['seller_id'] . $params['append'];
        }else{
            $url = self::API_URL . $url . "?sellerid=" . SELLER_ID . $params['append'];
        }

        $headers = [];
        if (isset($params['authorization'], $params['secretKey'])) {
            $headers[] = "Authorization: " . trim($params['authorization']);
            $headers[] = "SecretKey: " . trim($params['secretKey']);
//            $url = $params['url'];
        } else {
            $headers[] = "Authorization: " . trim(AUTHORIZATION);
            $headers[] = "SecretKey: " . trim(SECRET_KEY);
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
    public static function getRequest($url = '', $params = [], $config = [])
    {
        if($config){
            /*define("SELLER_ID",$config['seller_id']);
            define("AUTHORIZATION",$config['authorization']);
            define("SECRET_KEY",$config['secret_key']);
            define("API_URL",'https://api.newegg.com/marketplace');*/

            $url = 'https://api.newegg.com/marketplace' . $url . "?sellerid=" . $config['seller_id'];

        }else{
            $url = API_URL . $url . "?sellerid=" . SELLER_ID;

        }

        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        if (isset($params['authorization']) && isset($params['secretKey'])) {

            $headers[] = "Authorization: " . $params['authorization'];
            $headers[] = "SecretKey: " . $params['secretKey'];
            $url = $params['url'];
        } elseif (isset($params['config']['authorization']) && ($params['config']['secret_key'])){
            $headers[] = "Authorization: " . $params['config']['authorization'];
            $headers[] = "SecretKey: " . $params['config']['secret_key'];
        }else {
            $headers[] = "Authorization: " . AUTHORIZATION;
            $headers[] = "SecretKey: " . SECRET_KEY;
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
        $serverOutput = curl_exec($ch);
        curl_close($ch);
        $validJson = self::formatJson($serverOutput);
        return $validJson;
    }

    public static function formatJson($postData)
    {
        $hexdata = bin2hex($postData);
        $subvalidData = strstr($hexdata, '7');
        $validData = hex2bin($subvalidData);
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
