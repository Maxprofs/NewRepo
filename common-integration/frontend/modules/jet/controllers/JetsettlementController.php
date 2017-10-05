<?php 
namespace frontend\modules\jet\controllers;

use Yii;
use yii\filters\VerbFilter;

use frontend\modules\jet\models\JetConfiguration;

use frontend\modules\jet\components\Jetapi;
use frontend\modules\jet\components\Jetappdetails;

class JetsettlementController extends JetmainController
{
	public function behaviors()
	{
		return [
				'verbs' => [
						'class' => VerbFilter::className(),
						'actions' => [
								'delete' => ['post'],
						],
				],
		];
	}
	/* public function beforeAction($action)
    {
    	if(Jetappdetails::isValidateapp()=="expire"){
    		//var_dump(Yii::$app->session);die;
    		Yii::$app->session->setFlash('error', "We would like to inform you that your app subscription has been expired. Please renew the subscription to use the app services. You can renew services by using following <a href=http://cedcommerce.com/shopify-extensions/jet-shopify-integration target=_blank>link</a>");
    		$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/site/index',302);
    		return false;
    	}elseif(Jetappdetails::isValidateapp()=="not purchase"){
    		$msg='We would like to inform you that your app trial period has been expired,If you purchase app then create license from your customer account page from cedcommerce Or <br>Purchase jet-shopify app from <a href=http://cedcommerce.com/shopify-extensions/jet-shopify-integration target=_blank>CedCommerce</a> and can review on <a href=http://shopify.cedcommerce.com/frontend/site/pricing target=_blank>pricing page</a>';
    		Yii::$app->session->setFlash('error', ''.$msg.'' );
    		$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/site/index',302);
    		return false;
    	}else
    		return true;
    } */
	public function actionIndex()
	{
		if (Yii::$app->user->isGuest) {
			return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
		}
		//die('fg');
		if(Yii::$app->request->post())
		{
			if($_POST['settlement']=='' || !is_numeric($_POST['settlement']) )
			{
				Yii::$app->session->setFlash('error', "Enter Days in numeric form");
				return $this->redirect(Yii::$app->request->referrer);
			}
			$days=$_POST['settlement'];
			$model = new JetConfiguration();
			$merchant_id=Yii::$app->user->identity->id;
			$jetConfig=$model->find()->where(['merchant_id' => $merchant_id])->one();
			if($jetConfig)
			{
				$fullfillmentnodeid=$jetConfig->fullfilment_node_id;
				$api_host=$jetConfig->api_host;
				$api_user=$jetConfig->api_user;
				$api_password=$jetConfig->api_password;
				 
			}
			else
			{
				Yii::$app->session->setFlash('error', "please fill the jet configurable before enable all api's");
				return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jetconfiguration/index',302);
			}
			$jetHelper = new Jetapi($api_host,$api_user,$api_password);
			
			$response=$jetHelper->CGetRequest('/settlement/'.$days);
			
			//comment it for live only for test
			
			//$response='{"settlement_report_urls": ["/settlement/report/57e542a613fa42d8b6e8362dbd5911f5","/settlement/report/a10b4b28467c490984e0b387e1cc2d9a","/settlement/report/32c05c3230fc498e95f82129efd10213"]}';
			
			//end code to comment for live
			//print_r($response);die;
			$result=json_decode($response,true);
			//print_r($result);die;
			return $this->render('index',['result'=>$result]);
			
			//print_r($days);
		}
		return $this->render('index');
	}
	public function actionView()
	{
		//print_r($_POST);
		$post=$_GET['label'];
		
		$model = new JetConfiguration();
		$merchant_id=Yii::$app->user->identity->id;
		$jetConfig=$model->find()->where(['merchant_id' => $merchant_id])->one();
		if($jetConfig)
		{
			$fullfillmentnodeid=$jetConfig->fullfilment_node_id;
			$api_host=$jetConfig->api_host;
			$api_user=$jetConfig->api_user;
			$api_password=$jetConfig->api_password;
				
		}
		else
		{
			Yii::$app->session->setFlash('error', "please fill the jet configurable before enable all api's");
			return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jetconfiguration/index',302);
		}
		$jetHelper = new Jetapi($api_host,$api_user,$api_password);
	
		$response=$jetHelper->CGetRequest($post);
		//$result=json_decode($response,true);
		
		/* $response='{
				  "settlement_report_id": "002d7b66c1794190b3d2985826462893",
				  "settlement_state": "deposited",
				  "currency": "dollars",
				  "unavailable_balance": 4102.2,
				  "settlement_period_start": "2014-07-24T00:00:00Z",
				  "settlement_period_end": "2014-07-24T23:59:59.9999999Z",
				  "order_balance_details": {
				    "merchant_price": 4012.45,
				    "jet_variable_commission": -401.25,
				    "fixed_commission": -120.37,
				    "tax": 351.09,
				    "shipping_revenue": 672.0,
				    "shipping_tax": 58.8,
				    "shipping_charge": 0.0,
				    "fulfillment_fee": 0.0,
				    "product_cost": 0.0
				  },
				  "order_balance": 4572.72,
				  "return_balance_details": {
				    "merchant_price": -325.72,
				    "jet_variable_commission": 32.57,
				    "fixed_commission": 13.03,
				    "tax": -28.5,
				    "shipping_tax": -7.0,
				    "merchant_return_charge": 0.0,
				    "return_processing_fee": 0.0,
				    "product_cost": 0.0
				  },
				  "return_balance": -315.62,
				  "jet_adjustment": 0.0,
				  "settlement_value": 4257.1
				}'; */
		 //$result=json_decode($response,true);
		//$html=''; 
		//print_r($result);
		/* $html='<div class="attribute_listing">';
		$html.='<span>Settlement Report Id</span><span>'.$result['settlement_report_id'].'</span><br>';
		$html.='<span>settlement_state</span><span>'.$result['settlement_state'].'</span><br>';
		$html.='<span>currency</span><span>'.$result['currency'].'</span><br>';
		$html.='<span>unavailable_balance</span><span>'.$result['unavailable_balance'].'</span>';
		$html.='<span>settlement_period_start</span><span>'.$result['settlement_period_start'].'</span>';
		$html.='<span>settlement_period_end</span><span>'.$result['settlement_period_end'].'</span>';
		$html.='<span>merchant_price</span><span>'.$result['order_balance_details']['merchant_price'].'</span>';
		$html.='<span>jet_variable_commission</span><span>'.$result['order_balance_details']['jet_variable_commission'].'</span>';
		$html.='<span>fixed_commission</span><span>'.$result['order_balance_details']['fixed_commission'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['order_balance_details']['tax'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['order_balance_details']['shipping_revenue'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['order_balance_details']['shipping_tax'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['order_balance_details']['fulfillment_fee'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['order_balance_details']['product_cost'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['order_balance'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['merchant_price'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['jet_variable_commission'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['fixed_commission'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['tax'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['merchant_return_charge'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['return_processing_fee'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance_details']['product_cost'].'</span>';
		$html.='<span>Settlement Report Id</span><span>'.$result['return_balance'].'</span>';
		$html.='<span>jet_adjustment</span><span>'.$result['jet_adjustment'].'</span>';
		$html.='<span>settlement_value</span><span>'.$result['settlement_value'].'</span>';
		$html.='</div>';
 */		
		
		//echo $html;
		echo $response;
		//echo "Success";die;
		
	}
	public function show()
	{
		die('sdgdfhjf');
	}
	
	
	
	
}


?>