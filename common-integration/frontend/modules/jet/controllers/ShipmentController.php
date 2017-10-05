<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\models\JetOrderDetail;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Orderdata;
use frontend\modules\jet\components\ShopifyClientHelper;



use Yii;
use yii\web\Controller;

class ShipmentController extends Controller
{
    public function actionGetshipment($cron=false)
    {
        date_default_timezone_set("Asia/Kolkata");
        $flag = 0;        
        $query = "";
        $orderAckCollection = [];
        
        if(!$cron)
        {
            $merchant_id=Yii::$app->user->identity->id;

            $query = "select `jet_order_detail`.`id`,`jet_order_detail`.`merchant_id`,`reference_order_id`,`merchant_order_id`,`shopify_order_id`,`username`,`auth_key`,`api_user`,`api_password`,`fullfilment_node_id`,`order_data` from `jet_order_detail` left join `user` on jet_order_detail.merchant_id=user.id left join `jet_configuration` on jet_configuration.merchant_id=user.id where jet_order_detail.merchant_id='".$merchant_id."' AND jet_order_detail.shopify_order_id !='' AND (jet_order_detail.status='acknowledged' or jet_order_detail.status='inprogress')";
        }
        else
        {
            $query = "select `jet_order_detail`.`id`,`jet_order_detail`.`merchant_id`,`reference_order_id`,`merchant_order_id`,`shopify_order_id`,`username`,`auth_key`,`api_user`,`api_password`,`fullfilment_node_id`,`order_data`,`install_status` from `jet_order_detail` left join `user` on jet_order_detail.merchant_id=user.id left join `jet_configuration` on jet_configuration.merchant_id=user.id left join `jet_shop_details` on `jet_shop_details`.merchant_id=user.id where `jet_shop_details`.`install_status`!=0 AND jet_order_detail.shopify_order_id !='' AND (jet_order_detail.status='acknowledged' or jet_order_detail.status='inprogress')";
        }
        
        $orderAckCollection = Data::sqlRecords($query, "all", "select");
        
        if (!empty($orderAckCollection) && is_array($orderAckCollection)) 
        {
            foreach ($orderAckCollection as $key => $value) 
            {                
                if (!empty($value) && is_array($value) && isset($value['shopify_order_id'])) 
                {
                    $shopname = $value['username'];
                    $token = $value['auth_key'];

                    $Jet_api_user = $value['api_user'];
                    $Jet_api_password = $value['api_password'];
                    $configSetting = Jetproductinfo::getConfigSettings($merchant_id);
                    $sc = new ShopifyClientHelper($shopname, $token, PUBLIC_KEY, PRIVATE_KEY);  
                    $jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api", $Jet_api_user, $Jet_api_password); 
                    Orderdata::shipJetOrder($sc,$jetHelper,$configSetting,$value,$merchant_id,$countShip);
                }// Check shopify_order_id exist
            } // foreach  close  (All data collection)
        }else{
            Yii::$app->session->setFlash('success', "Order(s) is already shipped on jet...");
        }    // if exist (collection) close
        
        if(!$cron)
        {
            if($countShip>0)
                Yii::$app->session->setFlash('success',$countShip. "Order(s) successfully fulfilled on jet...");
            return $this->redirect(Yii::getAlias('@webjeturl').'/jetorderdetail/index',302);
        }
       fclose($file1);
    }// Get Shipment function close        
}
