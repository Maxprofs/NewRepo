<?php

namespace frontend\modules\walmartapi\controllers;

use yii\web\Controller;
use frontend\modules\walmartapi\components\Validation;
use yii\helpers\BaseJson;
use Yii;

class WalmartapiController extends Controller
{
    /**
     * Check request authentication
     * @return user status 
    */
    public function beforeAction($action)
    {
    	$merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
    	$hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

        if(is_null($merchant_id) || is_null($hash_key)) {
            echo BaseJson::encode(['success'=>false, 'message'=>'Request is not Authenticated.']);
            return false;
        }
        else {
        	$response = Validation::isAuthenticated($hash_key, $merchant_id);

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
}