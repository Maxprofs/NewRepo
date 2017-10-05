<?php 
namespace backend\modules\walmart\components;

use Yii;
use yii\base\Component;

class Curlrequests extends component
{
        public $apikey;
        public $root  = 'https://api.mailchimp.com/3.0';

        public function __construct($apikey=null, $opts=array()) {
        if (!$apikey) {
            //$apikey = '8c4e4e614a2f49abe06ac4792f355633-us13';
            $apikey = '4707c4e1061eb13f9c3f03257629055d-us7';
        }

        if (!$apikey) {
            $apikey = $this->readConfigs();
        }

        if (!$apikey) {
            throw new Mailchimp_Error('You must provide a MailChimp API key');
        }

        $this->apikey = $apikey;
        //$dc           = "us13";
        $dc           = "us7";

        if (strstr($this->apikey, "-")){
            list($key, $dc) = explode("-", $this->apikey, 2);
            if (!$dc) {
                $dc = "us13";
            }
        }

        $this->root = str_replace('https://api', 'https://' . $dc . '.api', $this->root);
        $this->root = rtrim($this->root, '/') . '/';

        if (!isset($opts['timeout']) || !is_int($opts['timeout'])){
            $opts['timeout'] = 600;
        }
        if (isset($opts['debug'])){
            $this->debug = true;
        }

    }
     /* function for getting template file path */
   public  function getRequest($url,$params){

       $params['apikey'] = 'apikey '.$this->apikey;
        $options = [
              'headers' => [
                'Authorization' => $params['apikey'],
              ],
        ];
        foreach ($options['headers'] as $header_name => $header_value) {
      $headers[] = $header_name . ': ' . $header_value;
        }
        $params = json_encode($params);
        //  $params = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->root.$url);
    // Get response as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response_body = curl_exec($ch);
        return $response_body; 

    }


public function postRequest($url,$data) {
    $dataCenter = substr($this->apikey,strpos($this->apikey,'-')+1);
    $url = $this->root . $url;
    print_r($url);die;
    $json = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $this->apikey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                                                        
    $result = curl_exec($ch);
    print_r($result);die;
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode;
}

}
?>
