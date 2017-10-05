<?php

namespace frontend\modules\apilogin\controllers;
use Yii;
use yii\web\Controller;
use frontend\modules\walmartapi\components\Datahelper;
use yii\helpers\BaseJson;
use frontend\modules\walmartapi\controllers\WalmartapiController;
class ApilogoutController extends \yii\web\Controller
{

	  public function beforeAction($action) {
	  	$getRequest = Yii::$app->request->post(); 
	  	if ((isset($getRequest['shop_url']) && !empty($getRequest['shop_url'])) && (isset($getRequest['device_access_token']) && !empty($getRequest['device_access_token'])))
    	{ 
            $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
            $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');
             $shopUrl = $getRequest['shop_url'];
	        $currentDate =  date('Y-m-d H:i:s');
            if(is_null($merchant_id) || is_null($hash_key)) {
                  echo BaseJson::encode(['success'=>false, 'message'=>'Request is not Authenticated.']);
                  return false;
              }
              else {
                $response = $this->isAuthenticated($hash_key, $merchant_id);
                  if(isset($response['success']) && $response['success'] == true)
                  {
                    $this->enableCsrfValidation = false; 
                    return parent::beforeAction($action);
                  }else{
                      
                      $headerData = BaseJson::encode($response);
                      echo $headerData;
                      return false;
                  }
              }
        }
        else{
	 	$validateData = ['success'=>false,'message' =>'something went wrong'];
		$headerData = BaseJson::encode($validateData);
		return $headerData;
	 	}
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action);
    }
 	/**
     * validate login detail and manage db
     * @return json_array 
     */
    public function actionLogout()
    {
    		$getRequest = Yii::$app->request->post(); 
    		$shopUrl = $getRequest['shop_url'];
    		$loginUser = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `shop_url`='".$shopUrl."' LIMIT 0,1", 'one');
		    if(!empty($loginUser)){
		    	$logoutData = explode(',', $loginUser['device_access_token']);

		    	$data = array_flip($logoutData);
		    	foreach ($data  as $key => $value) {
		    		if($key == $getRequest['device_access_token']){
		    			unset($data[$key]);
		    			break;
		    		}
		    		else{

		    		}
		    	}
		    	$data = array_flip($data);
		    	$saveData = implode(',', $data);
		    	$model = Datahelper::sqlRecords("UPDATE `app_login_check` SET device_access_token='".$saveData."' where merchant_id='".$loginUser['merchant_id']."'", 'all','update');
		    	$validateData = ['success'=>true,'message' =>'You are successfully logout'];
				$headerData = BaseJson::encode($validateData);
				return $headerData;
		    }
		    else{
		    	$validateData = ['success'=>false,'message' =>'something went wrong'];
				$headerData = BaseJson::encode($validateData);
				return $headerData;
			}
	}
		


       /**
     * validate user authentication
     * @param $hash_key string
     * @param $merchant_id integer 
     * @return array
     */
    public static function isAuthenticated($hash_key,$merchant_id)
    {
            $loginUser = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `hash_key`='".$hash_key."'AND `merchant_id`=$merchant_id", 'all');
            if(!empty($loginUser)){
                    $currentDate =  date('Y-m-d H:i:s');
                    $expiry_date = strtotime($loginUser[0]['expiry_date']);
                    $today = strtotime($currentDate);
                    if($today<=$expiry_date){
                        $validateData = ['success' =>true ,'message' =>'ok'];
                        return $validateData;
                    }
                    else{
                        
                        $validateData = ['success' =>false ,'message' =>'Your Hash key has been Expired login and get new hash Key'];
                        return $validateData;

                    }
            }
            else{
                $validateData = ['success' =>false ,'message' =>'Your are not authentic user'];
                return $validateData;

            }
        
    }



}
