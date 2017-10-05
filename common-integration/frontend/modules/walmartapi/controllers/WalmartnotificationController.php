<?php

namespace frontend\modules\walmartapi\controllers;
use Yii;
use yii\helpers\BaseJson;
use frontend\modules\walmartapi\components\Notification;
use frontend\modules\walmartapi\components\Datahelper;

class WalmartnotificationController extends \yii\web\Controller
{

	public function beforeAction($action) { 
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action);
    }
 	/**
     * check new order
     * @return notification meaaage  
     */
    public function actionOrderPlace()
    {
    	$getRequest = Yii::$app->request->post();
        if(isset($getRequest['order_id']) && !empty($getRequest['order_id']) && isset($getRequest['merchant_id']) && !empty($getRequest['merchant_id'])){
            $notification['mtitle'] = 'New order from walmart';
            $notification['mdesc'] = 'You have received a new order on walmart.com with order_id:'.$getRequest['order_id'];
            $notification['platform'] ='walmart';
            $notification['id'] = $getRequest['order_id'];
            $notification['relation'] = 'order_list';
            $data = Datahelper::sqlRecords("SELECT `device_access_token`,`android_reg_id` FROM `app_login_check` WHERE `merchant_id`='".$getRequest['merchant_id']."' LIMIT 0,1", 'one');
            if($data){
                $devce_token = explode(',', $data['device_access_token']);
                $android_reg_id = explode(',', $data['android_reg_id']);
                foreach ($devce_token as $key => $value) {
                    $data = Notification::iOS($notification,$value);
                }
                foreach ($android_reg_id as $key => $value) {
                     Notification::android($notification,$value);
                }
            }     
        }
          
			
    }
    /**
     * check order shipment status
     * @return notification meaaage  
     */
    public function actionOrderFulfilment()
    {

    	 $getRequest = Yii::$app->request->post();
        if(isset($getRequest['order_id']) && !empty($getRequest['order_id']) && isset($getRequest['order_status']) && !empty($getRequest['order_status']) && isset($getRequest['merchant_id']) && !empty($getRequest['merchant_id'])){
            $data = Datahelper::sqlRecords("SELECT `device_access_token`,`android_reg_id` FROM `app_login_check` WHERE `merchant_id`='".$getRequest['merchant_id']."' LIMIT 0,1", 'one');
            if($data){
                $devce_token = explode(',', $data['device_access_token']);
                $android_reg_id = explode(',', $data['android_reg_id']);
                    if($getRequest['order_status'] =='completed'){
                        $notification['mtitle'] = 'Order Status on walmart';
                        $notification['mdesc'] = ' Your order successfully shipped on walmart with order_id: '.$getRequest['order_id'];
                        $notification['platform'] ='walmart';
                        $notification['id'] = $getRequest['order_id'];
                        $notification['relation'] = 'order_list';
                        foreach ($devce_token as $key => $value) {
                            Notification::iOS($notification,$value);
                        }

                    }
                    else{
                        $notification['mtitle'] = 'Order Status on walmart';
                        $notification['mdesc'] = $getRequest['order_id'].': Order not to be shipped on walmart store';
                        $notification['platform'] ='walmart';
                        $notification['id'] = $getRequest['order_id'];
                        $notification['relation'] = 'order_list';
                        foreach ($devce_token as $key => $value) {
                            Notification::iOS($notification,$value);
                        }
                        foreach ($android_reg_id as $key => $value) {
                            Notification::android($notification,$value);
                        }
                    }
            }

        }

			
    }
    /**
     * Fetch  product Inventory
     * @return notification meaaage 
     */
    public function actionProductInventoryUpdate()
    {

    	$getRequest = Yii::$app->request->post();
    	if(isset($getRequest['product_id']) && !empty($getRequest['product_id']) && isset($getRequest['qty']) && !empty($getRequest['qty']) && isset($getRequest['merchant_id']) && !empty($getRequest['merchant_id'])){
    		$merchant_id = $getRequest['merchant_id'];
    		$data = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `merchant_id`='".$getRequest['merchant_id']."' LIMIT 0,1", 'one');
    		$walmartProductthresholdConfig = Datahelper::sqlRecords("SELECT `value` FROM `walmart_config` WHERE merchant_id='".$merchant_id."' AND data='product_threshold'", 'one');
    		if(!empty($walmartProductthresholdConfig)){
    			$productThreshold = $walmartProductthresholdConfig['value'];
    		}
    		else{
    			$productThreshold = 20;
    		}
    		if($getRequest['qty'] <= $productThreshold){
                $notification['mtitle'] = 'Product Status on Walmart';
                $notification['mdesc'] = 'Product Stock Low with product_id:'.$getRequest['product_id'].'on walmart store';
    			$devce_token = explode(',', $data['device_access_token']);
                foreach ($devce_token as $key => $value) {
                        Notification::iOS($notification,$value);
                }
    		}
    	}

			
    }
    /**
     * check merchant configuration setting
     * @return notification meaaage 
     */
    public function actionConfiguration()
    {
    	$getRequest = Yii::$app->request->post();
    	if(isset($getRequest['merchant_id']) && !empty($getRequest['merchant_id'])){
            $data = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `merchant_id`='".$getRequest['merchant_id']."' LIMIT 0,1", 'one');
    		$notification = 'Goto your Shopify panel and set the Api configuration';
    		$devce_token = explode(',', $data['device_access_token']);
            foreach ($devce_token as $key => $value) {
                Notification::iOS($notification,$value);
            }
			
    	}
    }

}
