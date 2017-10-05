<?php

namespace backend\modules\walmart\controllers;


use Yii;




/**
 * Reports controller
 */
class DashboardController extends BaseController 
{
	
    /**
     * @inheritdoc
     */
	public $detailArray=array();
	public $P_avail=0;
	public $P_complete=0;
	public $p_under=0;
    

    
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
    		return $this->redirect(['login']);
    	}
    	
    	$connection = Yii::$app->getDb();
    	
    	date_default_timezone_set('Asia/Kolkata');
    	$countArray=array();
        
        $today = count($connection->createCommand("SELECT  `merchant_id` from `jet_active_merchants` WHERE updated_at LIKE '%".date('Y-m-d')."%'")->queryAll());
        
        $yesterday = count($connection->createCommand("SELECT  `merchant_id` from `jet_active_merchants` WHERE updated_at LIKE '%".date('Y-m-d',strtotime("-1 days"))."%'")->queryAll());
        
        $sevenDays = count($connection->createCommand('select `merchant_id` from `jet_active_merchants` where  `updated_at` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->queryAll());
        
        $fifteenDays = count($connection->createCommand('select `merchant_id` from `jet_active_merchants` where  `updated_at` >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)')->queryAll());
        
        $month = count($connection->createCommand('select `merchant_id` from `jet_active_merchants` where  `updated_at` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')->queryAll());
        
        $countArray['today']=$today;
        $countArray['yesterday']=$yesterday;
        $countArray['sevenday']=$sevenDays;
        $countArray['fifteenday']=$fifteenDays;
        $countArray['onemonth']=$month;
        
        unset($today);unset($yesterday);unset($sevenDays);unset($fifteenDays);unset($month);
        $purchased=count($connection->createCommand("SELECT  `merchant_id` from `jet_extension_detail` WHERE status ='Purchased'")->queryAll());
        $license_expired=count($connection->createCommand("SELECT  `merchant_id` from `jet_extension_detail` WHERE status ='License Expired'")->queryAll());
        $not_purchase=count($connection->createCommand("SELECT  `merchant_id` from `jet_extension_detail` WHERE status ='Not Purchase'")->queryAll());
        $trial_expired=count($connection->createCommand("SELECT  `merchant_id` from `jet_extension_detail` WHERE status ='Trial Expired'")->queryAll());
        $install= count($connection->createCommand("SELECT  `merchant_id` from `jet_extension_detail` WHERE app_status ='install'")->queryAll());
        $uninstall=count($connection->createCommand("SELECT  `merchant_id` from `jet_extension_detail` WHERE app_status ='uninstall'")->queryAll());
        
        $extArray=array();
        
        $extArray['license_expired']=$license_expired;
        $extArray['purchased']=$purchased;
        $extArray['not_purchase']=$not_purchase;
        $extArray['trial_expired']=$trial_expired;
        $extArray['install']=$install;
        $extArray['uninstall']=$uninstall;
        
        unset($purchased);unset($license_expired);unset($not_purchase);unset($trial_expired);unset($install);unset($uninstall);
    	return $this->render('index',[
    			'active'=>$countArray,
    			'extArray'=>$extArray,
    			
    	]);
    }
    
}
