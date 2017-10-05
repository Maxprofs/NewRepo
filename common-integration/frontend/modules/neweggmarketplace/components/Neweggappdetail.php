<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 7/2/17
 * Time: 4:53 PM
 */
namespace frontend\modules\neweggmarketplace\components;

use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\components\Data;
use yii\base\Response;
use frontend\modules\neweggmarketplace\components\AttributeMap;
use frontend\modules\neweggmarketplace\components\Neweggapi;


class Neweggappdetail extends Component
{

    public static function validateApiCredentials($seller_id,$secret_key,$authorization)
    {
        $url = '/sellermgmt/servicestatus';
        $params = [
            'seller_id' => $seller_id,
            'authorization' => $authorization,
            'secretKey' => $secret_key

        ];

        $response = Cronrequest::postRequest( $url, $params);
//        var_dump($response);die;
//        print_r(json_decode($response,true));die;

        $lastchar = substr($response, strlen($response) - 1);
        $firstchar = substr($response, 0);
        if ($firstchar[0] == '[') {
            $string = substr($response, 0);
        } else {
            $string = $response;
        }
        if ($lastchar == ']') {
            $string = substr($string, 0, -1);
        }
        $value = json_decode($string, true);
        //$value = json_decode($response, true);

        if(isset($value['Code']) || empty($value)){
//            return ['status'=>'error','message'=>$value['Message']];
            return false;
        }
        return true;

    }

    public static function validateCANApiCredentials($seller_id,$secret_key,$authorization)
    {
        $url = '/can/sellermgmt/servicestatus';
        $params = [
            'seller_id' => $seller_id,
            'authorization' => $authorization,
            'secretKey' => $secret_key

        ];

        $response = Cronrequest::postRequest( $url, $params);
//        var_dump($response);die;
//        print_r(json_decode($response,true));die;

        $lastchar = substr($response, strlen($response) - 1);
        $firstchar = substr($response, 0);
        if ($firstchar[0] == '[') {
            $string = substr($response, 0);
        } else {
            $string = $response;
        }
        if ($lastchar == ']') {
            $string = substr($string, 0, -1);
        }
        $value = json_decode($string, true);
        //$value = json_decode($response, true);

        if(isset($value['Code']) || empty($value)){
//            return ['status'=>'error','message'=>$value['Message']];
            return false;
        }
        return true;

    }

    public static function validateManufacturer($seller_id,$secret_key,$authorization,$manufacturer)
    {
            $jsonData = [];
            $requestBody = [];
            $jsonData['OperationType']='GetManufacturerRequest';

            $requestBody['RequestCriteria']=self::preparesyncjson($manufacturer);
            $jsonData['RequestBody']=$requestBody;
            $postData= json_encode($jsonData);
            $obj = new Neweggapi($seller_id,$authorization,$secret_key);
            $params['body']=$postData;
            $params['authorization'] = $authorization;
            $params['secretKey']=$secret_key;
            $response = $obj->putRequest('/contentmgmt/manufacturer/manufacturerinfo',$params);
            $server_output = json_decode($response,true);
            if(isset($server_output['ResponseBody']['ManufacturerList']) && !empty($server_output['ResponseBody']['ManufacturerList'])){
                return true;
            }
            else{
                return false;
            }


    }

    public static function preparesyncjson($data){
            $array = [];
            $array['ManufacturerName']=$data;
            return $array;

    }
}