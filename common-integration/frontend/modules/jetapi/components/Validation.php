<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;
use frontend\modules\jetapi\components\Datahelper;

class Validation extends Component
{
    /**
     * validate user authentication
     * @param $hash_key string
     * @param $merchant_id integer 
     * @return array
     */
    public static function isAuthenticated($hash_key,$merchant_id)
    {

        if(self::checkAppStatus($merchant_id))
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
            $validateData = ['success' =>false ,'message' =>'Please Install the Jet Integartion App from Shopify app Store.'];
            return $validateData;
        }
    }

    /**
     * @param $merchant_id
     * @return bool
     */
    public static function checkAppStatus($merchant_id) {

        $query = "SELECT `status` FROM `app_status` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";

        $model = Datahelper::sqlRecords($query, 'one');

        if(!$model || ($model && $model['status']==0)){
                return false;
        }
        return true;
    }
}