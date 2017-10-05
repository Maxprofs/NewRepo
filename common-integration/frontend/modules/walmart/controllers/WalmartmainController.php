<?php 
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Walmartappdetails;
use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Installation;

class WalmartmainController extends Controller
{
    public function beforeAction($action)
    {
        $session = Yii::$app->session;
        $this->layout = 'main';
        if(!Yii::$app->user->isGuest)
        {
            if(!defined('MERCHANT_ID') || Yii::$app->user->identity->id!=MERCHANT_ID)
            {
                $merchant_id = Yii::$app->user->identity->id;
                $shopDetails = Data::getWalmartShopDetails($merchant_id);
                $token = isset($shopDetails['token'])?$shopDetails['token']:'';
                $email = isset($shopDetails['email'])?$shopDetails['email']:'';
                $currency= isset($shopDetails['currency'])?$shopDetails['currency']:'USD';
                $shop = Yii::$app->user->identity->username;

                define("MERCHANT_ID", $merchant_id);
                define("SHOP", $shop);
                define("TOKEN", $token);
                define("CURRENCY", $currency);
                $walmartConfig=[];
                $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".MERCHANT_ID."'", 'one');
                if($walmartConfig)
                {
                    define("CONSUMER_CHANNEL_TYPE_ID",$walmartConfig['consumer_channel_type_id']);
                    define("API_USER",$walmartConfig['consumer_id']);
                    define("API_PASSWORD",$walmartConfig['secret_key']);
                    define("EMAIL",$email);
                }
            }

            $referer_url='';
            $current_action='';
            $referer_url = 'REFERER_URL : '. Yii::$app->request->referrer;
            $current_action = 'CURRENT_URL : '.Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
            $authorizeAction = array('walmartproduct/batchimport'=>true);
            $allowedIp = array('182.72.248.90'=>true,'127.0.0.1'=>true);
            if(!empty($referer_url) || !empty($current_action)){
                $msg = 'IP: '.$_SERVER['REMOTE_ADDR'].PHP_EOL .$referer_url . PHP_EOL .$current_action . PHP_EOL .'---------------------------------------------------'. PHP_EOL;
                $path = 'action-log/'.$merchant_id.'/'.date('Y-m-d').'/'.'flow.log';
                Data::createLog($msg,$path,'a');
                if(isset($authorizeAction[Yii::$app->controller->id.'/'.Yii::$app->controller->action->id])){
                    if(!preg_match('/(192.168.0.)\w+/', $_SERVER['REMOTE_ADDR']) && !isset($allowedIp[$_SERVER['REMOTE_ADDR']])){
                        echo "You are not authorize user to perform this Action";
                        die;
                    }
                }
            }

//            $manager_login = Yii::$app->user->identity->manager;
            $user_id = Yii::$app->user->id;
            $session = Yii::$app->session;
            $manager_login = $session->get('manager_login_'.$user_id);
            if($manager_login)
            {
            	return true;
            }

            $obj = new Walmartappdetails();
            if ($obj->appstatus($shop) == false) {
                return $this->redirect('https://apps.shopify.com/walmart-marketplace-integration');
            }

            //Code By Himanshu Start
            if(Yii::$app->controller->id != 'walmart-install' && Yii::$app->controller->id != 'walmarttaxcodes')
            {
                Installation::completeInstallationForOldMerchants(MERCHANT_ID);
                
                $installation = Installation::isInstallationComplete(MERCHANT_ID);
                if($installation) {
                    if($installation['status'] == Installation::INSTALLATION_STATUS_PENDING) {
                        $step = $installation['step'];
                        //$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet-install/index?step='.$step,302);
                        $this->redirect(Data::getUrl('walmart-install/index'),302);
                        return false;
                    }
                } else {
                    $step = Installation::getFirstStep();
                    //$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet-install/index?step='.$step,302);
                    $this->redirect(Data::getUrl('walmart-install/index'),302);
                    return false;
                }

                if(!Walmartappdetails::isAppConfigured($merchant_id) &&
                Yii::$app->controller->id != 'walmartconfiguration')
                {
                    $msg='Please activate walmart api(s) to start integration with Walmart';
                    Yii::$app->session->setFlash('error', $msg);
                    $this->redirect(Data::getUrl('site/index'));
                    return false;
                }
            }
            //Code By Himanshu End

            $auth = Walmartappdetails::authoriseAppDetails($merchant_id, $shop);
            if(isset($auth['status']) && !$auth['status'])
            {
                if(isset($auth['purchase_status']) && 
                    ($auth['purchase_status']=='license_expired' || $auth['purchase_status']=='trial_expired')) {
                    $url = yii::$app->request->baseUrl.'/walmart/paymentplan';
                    return $this->redirect($url);
                }
                else {
                    Yii::$app->session->setFlash('error', $auth['message']);
                    $this->redirect(Data::getUrl('site/logout'));
                    return false;
                }
            }

            return true;
        }
        else
        {
            if($_SERVER['SERVER_NAME'] =='shopify.cedcommerce.com'){
                $unsuscribe = $_SERVER['QUERY_STRING'];
                Yii::$app->session->set('redirect_url', $unsuscribe);
                $this->redirect(Data::getUrl('site/index')); 
                return false;
            }
            $this->redirect(Data::getUrl('site/index')); 
            return false;
        }
    }
}
?>