<?php

namespace frontend\modules\walmartapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;


class Vieworder extends Component
{

    /**
     * @param $Output
     * @return array|bool
     */
    public function getVieworder($Output)
    {
        if(isset($Output['shopify_order_id']) && !empty($Output['shopify_order_id'])){

            try {
                $orderdetail = self::getDetails($Output);

            } catch (\Exception $e) // an exception is raised if a query fails
            {
                // print_r($e->getMessage());

                return ['error'=>true,'message'=>$e->getMessage()];
//                return false;
            }
        }else{
            return ['error'=>true, 'message'=>'Invalid Shopify Order Id.'];
//            return false;
        }

        return $orderdetail;
    }

    /**
     * @param $Output
     * @return array
     */
    public function getDetails($Output)
    {
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');
        if(!empty($Output['shopify_order_id'])){
            $shopify_order_id = $Output['shopify_order_id'];
        }

        $data = Datahelper::sqlRecords("SELECT * FROM `walmart_order_details` WHERE `merchant_id`='".$merchant_id."' AND `shopify_order_id`='".$shopify_order_id."'  ",'one');

        if(empty($data))
        {
            return ['error'=>true,'message'=>'No Order available for this merchant'];
        }else{
            return ['data'=>$data,'success'=>true,'message'=>'Successfully done'];
            //return $data;
        }

    }
}
