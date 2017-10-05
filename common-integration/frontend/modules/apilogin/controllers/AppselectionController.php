<?php

namespace frontend\modules\apilogin\controllers;
use Yii;
use yii\web\Controller;
use yii\helpers\BaseJson;
use frontend\modules\walmartapi\components\Datahelper;
class AppselectionController extends \yii\web\Controller
{
    public function beforeAction($action) {
            $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
            $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');
            $getRequest = Yii::$app->request->post('selected_app');
            $type = $getRequest;
            if(is_null($merchant_id) || is_null($hash_key) || is_null($type)) {
                  echo BaseJson::encode(['success'=>false, 'message'=>'Request is not Authenticated.']);
                  return false;
              }
              else {
                $response = $this->isAuthenticated($hash_key, $merchant_id, $type);
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
            $this->enableCsrfValidation = false; 
            return parent::beforeAction($action);
    }

    public function actionSelectedApp()
    {
        $getRequest = Yii::$app->request->post();
        if ((isset($getRequest['selected_app']) && !empty($getRequest['selected_app'])))
        {
            $data = $this->urlDetail($getRequest['selected_app']);
            if($data){
                $validateData = ['success'=>true ,'message' =>'url for '.$getRequest['selected_app'].' integration','data'=>$data];
                $headerData = BaseJson::encode($validateData);
                return $headerData;
            }
            else{
                $validateData = ['success'=>false ,'message' =>'Not a valid app selected'];
                $headerData = BaseJson::encode($validateData);
                return $headerData;

            }
            
        }  
    }

    public function urlDetail($data){


        $urlDetails = ['walmart'=>array('dashboard'=>'/integration/walmartapi/walmartdashboard/dashboard','order_list'=>'/integration/walmartapi/walmartorderdetail/list','product_list'=>'/integration/walmartapi/walmartproduct/list','upload'=>'/integration/walmartapi/walmartproduct/upload','product_view'=>'/integration/walmartapi/walmartproduct/view','order_view'=>'/integration/walmartapi/walmartorderdetail/view'),'jet'=>array('dashboard'=>'/jet/jetapi/jetdashboard/dashboard','product_list'=>'/jet/jetapi/jetproduct/list','upload'=>'/jet/jetapi/jetproduct/upload','product_view'=>'/jet/jetapi/jetproduct/view','order_view'=>'/jet/jetapi/jetorderdetail/view','order_list'=>'/jet/jetapi/jetorderdetail/orderlist')];

        if (array_key_exists($data,$urlDetails))
          {
          return $urlDetails[$data];
          }
        else
          {
          return false;
          }
       
    }

    /**
     * validate user authentication
     * @param $hash_key string
     * @param $merchant_id integer 
     * @return array
     */
    public static function isAuthenticated($hash_key,$merchant_id ,$type)
    {
        if(self::checkAppStatus($merchant_id ,$type))
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
        else
        {
            $validateData = ['success' =>false ,'message' =>'Please Install the '.$type.'Integartion App from Shopify app Store.'];
            return $validateData;
        }
    }

    /**
     * @param $merchant_id
     * @return bool
     */
    public static function checkAppStatus($merchant_id ,$type) {
        if($type == 'walmart'){
          $query = "SELECT `status` FROM `walmart_shop_details` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
        }
        elseif($type == "jet"){
            $query = "SELECT `status` FROM `app_status` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
        }
        
        $model = Datahelper::sqlRecords($query, 'one');

        if(!$model || ($model && $model['status']==0)){
                return false;
        }
        return true;
    }


}
