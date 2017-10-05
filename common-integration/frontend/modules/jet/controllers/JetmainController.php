<?php 
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Installation;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\components\ShopifyClientHelper;


class JetmainController extends Controller
{
	protected $sc, $jetHelper;
	public function beforeAction($action)
	{		
		$this->layout = 'main';
		$session = Yii::$app->session;		
		if(Yii::$app->user->isGuest)
		{
			if($_SERVER['SERVER_NAME'] =='apps.cedcommerce.com')
			{
                $unsuscribe = $_SERVER['QUERY_STRING'];
				$modalCookie= new \yii\web\Cookie();
                $modalCookie->name='redirect_url';
                $modalCookie->value=$unsuscribe;
                $cookie=\Yii::$app->getResponse()->getCookies()->add($modalCookie);
            }
            $this->redirect(Yii::getAlias('@webjeturl').'/site/index',302);
			return false;
		}
		if(Yii::$app->user->identity->auth_key=='')
		{
            return $this->redirect('http://apps.shopify.com/jet-integration');
        }
		if(!defined('MERCHANT_ID') || !defined('API_USER') || (Yii::$app->user->identity->id!=MERCHANT_ID) || ($_GET['to_switch']) )
		{
			$merchant_id = Yii::$app->user->identity->id;
			$shop  = Yii::$app->user->identity->username;
			$token = Yii::$app->user->identity->auth_key;

			define("MERCHANT_ID",$merchant_id);
			define("SHOP",$shop);
			define("TOKEN",$token);
			$jetConfig=Data::sqlRecords("SELECT `fullfilment_node_id`,`api_user`,`api_password`,`merchant_email` FROM `jet_configuration` WHERE merchant_id='".MERCHANT_ID."' LIMIT 0,1",'one','select');
			if(isset($jetConfig['api_user']))
			{
				define("FULLFILMENT_NODE_ID",$jetConfig['fullfilment_node_id']);
				define("API_USER",$jetConfig['api_user']);
				define("API_PASSWORD",$jetConfig['api_password']);
				define("EMAIL",$jetConfig['merchant_email']);
				$this->jetHelper = new Jetapimerchant(API_HOST,API_USER,API_PASSWORD);
			}
		}
        $this->sc = new ShopifyClientHelper(SHOP, TOKEN, PUBLIC_KEY, PRIVATE_KEY);
		// Check whether jet-integration app is installed or not
		if(Jetappdetails::appstatus(SHOP)==false)
		{
			$msg='Please install app to continue jet integration for your shop store';
			Yii::$app->session->setFlash('error',$msg);
			$this->redirect(Yii::getAlias('@webjeturl').'/site/logout',302);
			return false;
		}		
		//Code By Himanshu Start
		//print_r(Yii::$app->controller->action->id);die;
		if(Yii::$app->controller->id != 'jet-install')
		{
			Installation::completeInstallationForOldMerchants(MERCHANT_ID);
			
		    $installation = Installation::isInstallationComplete(MERCHANT_ID);
		    if($installation) 
		    {
		    	if (strcasecmp($installation['status'], Installation::INSTALLATION_STATUS_PENDING) == 0)
		        {
		            $step = $installation['step'];
		            return $this->redirect(Yii::getAlias('@webjeturl').'/jet-install/index',302);
		        }
		    } 
		    else 
		    {
		        $step = Installation::getFirstStep();
		        $this->redirect(Yii::getAlias('@webjeturl').'/jet-install/index',302);
		        return false;
		    }
		    // Check app subscription validity
			if(Jetappdetails::isValidateapp(MERCHANT_ID)=="expire" || Jetappdetails::isValidateapp(MERCHANT_ID)=="not purchase")
			{
				$this->redirect(Yii::getAlias('@webjeturl').'/site/index',302);
				return false;
			}			
    	}
		return true;
	}
}
?>