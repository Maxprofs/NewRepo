<?php 
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use frontend\modules\jet\components\Activateapi;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapitest;
use frontend\modules\jet\models\JetConfig;
use frontend\modules\jet\models\JetConfiguration;
use frontend\modules\jet\models\JetTestApi;

use common\models\JetExtensionDetail;

class JetConfigController extends Controller
{
	const API_HOST = "https://merchant-api.jet.com/api";
	public function actionIndex()
	{
		//$this->layout="main2";
		$api_host=self::API_HOST;
		$merchant_id=Yii::$app->user->identity->id;
		$enable=false;
		$apiStatus='';
		$is_enabled=false;
		$testConfig=[];
		$query="SELECT user,secret,merchant,fulfillment_node,contact_number,skype_id FROM `jet_test_api` WHERE merchant_id='".$merchant_id."' LIMIT 0,1";
		$testConfig=Data::sqlRecords($query,'one','select');
		if(Yii::$app->request->post())
        {
        	$data=Yii::$app->request->post();
        	$api_user=trim($data['username']);
			$api_password=trim($data['password']);
			$fullfillmentnodeid=trim($data['fulfillment']);
			$merchant=trim($data['merchant']);
			if($api_user=="" || $api_password=="" || $fullfillmentnodeid=="")
			{
				return "Please enter valid api credentials";
			}
			$query="SELECT id FROM `jet_test_api` WHERE merchant_id<>'".$merchant_id."' and fulfillment_node='".$fullfillmentnodeid."' LIMIT 0,1";
			$configExist=Data::sqlRecords($query,'one','select');
			
			if(!(isset($_GET['ced_developer']) && $_GET['ced_developer']=='amit') && (is_array($configExist) && count($configExist)>0))
			{
				return "Your test api keys already exist. If you want to use same api keys then create another fulfillment node on jet <a href='https://partner.jet.com/fulfillmentnode/node/new' target='_blank'>CLICK HERE</a> to avoid confict between orders and products for 2 shops.";
			}
			$jetHelper = new Jetapitest($api_host,$api_user,$api_password);
			$responseToken ="";
			$responseToken = $jetHelper->JrequestTokenCurl();
			//$is_enabled=Activateapi::isEnabled($jetHelper,$merchant_id);
			if($responseToken==false)
			{
				return "Unable to get api token either api user and secret key is incorrect or jet partner panel down. Require <a target='_blank' href='".yii::$app->request->baseUrl."/jet/jet-install/help?step=2'>HELP</a>";
			}	
			$resultApi=JetTestApi::find()->where(['merchant_id'=>$merchant_id])->one();
			if($resultApi=="")
			{
				$modelApi=new JetTestApi();
				$modelApi->merchant_id = $merchant_id;
				$modelApi->user = $api_user;
				$modelApi->secret = $api_password;
				$modelApi->merchant = $merchant;
				$modelApi->fulfillment_node = $fullfillmentnodeid;
				$modelApi->save(false);
			}
			$testConfig['user']=$api_user;
			$testConfig['secret']=$api_password;
			$testConfig['merchant']=$merchant;
			$testConfig['fulfillment_node']=$fullfillmentnodeid;
        }
        $enableFlag=false;
        
		if(is_array($testConfig) && count($testConfig)>0)
		{
			$jetHelper = new Jetapitest($api_host,$testConfig['user'],$testConfig['secret']);
			
			//$is_enabled=Activateapi::isEnabled($jetHelper,$merchant_id);
			
			/* if($is_enabled)
			{
				$enableFlag=true;
			}
			else
			{ */
				$apiStatus=Activateapi::enabletestapi($jetHelper,$testConfig,$merchant_id);
				if($apiStatus=="enabled" && Activateapi::isEnabled($jetHelper,$merchant_id)){
					$enableFlag=true;
				}
				else
				{
					return $apiStatus; 
				}
			//}
		}
		if($enableFlag==true)
		{
			return "2";
		}
		else
		{
			return "Jet api(s) not enabled yet there are some lagging in jet partner panel, please re-enter your api details and click on next to activate";
		} 
	}
	public function actionSave()
	{
		//$this->layout="main2";
		$merchant_id= Yii::$app->user->identity->id;
		$query="SELECT api_user FROM `jet_configuration` WHERE merchant_id='".$merchant_id."' LIMIT 0,1";
		$jetConfig=Data::sqlRecords($query,'one','select');
		$query="SELECT user,secret,fulfillment_node FROM `jet_test_api` WHERE merchant_id='".$merchant_id."' LIMIT 0,1";
		$testConfig=Data::sqlRecords($query,'one','select');
		if(Yii::$app->request->post())
        {
			$data=Yii::$app->request->post();
        	$api_user=$data['username'];
			$api_password=$data['password'];
			$fullfillmentnodeid=$data['fulfillment'];
			$merchant=$data['merchant'];
			$result="";
			if($api_user=="" || $api_password=="" || $merchant=="" || $fullfillmentnodeid==""){
				return "Please enter valid api credentials";
			}

			//check either api are test api(s);
			if( !(isset($_GET['ced_developer']) && $_GET['ced_developer']=='amit') && (isset($testConfig['user'],$testConfig['secret']) && ($testConfig['user']==$api_user || $testConfig['secret']==$api_password)) )
			{
				return " Entered Api User or Secret key belong to sandbox api(s). Please enter valid live api credentials. Require <a target='_blank' href='".yii::$app->request->baseUrl."/jet/jet-install/help?step=3'>HELP</a>";
			}
			
			$jetHelper="";
			$jetHelper = new Jetapitest(self::API_HOST,$api_user,$api_password);
			$responseToken ="";
			$responseToken = $jetHelper->JrequestTokenCurl();
			if($responseToken==false){
				return "Api User or Secret key is invalid.Please enter valid live api credentials. Require <a target='_blank' href='".yii::$app->request->baseUrl."/jet/jet-install/help?step=3'>HELP</a>";
			}
			//check fulfillmentnode details
			$email="";
			$resFulfillmentInfo="";$resFulfillmentInfoarr = $configValues= [];
			/* $resFulfillmentInfo = $jetHelper->CGetRequest('/fulfillmentnodesbymerchantid/',$merchant_id);
			$resFulfillmentInfoarr = json_decode($resFulfillmentInfo,true);
			
			if(count($resFulfillmentInfoarr)>0 && !(isset($resFulfillmentInfoarr['errors']))){
				foreach($resFulfillmentInfoarr as $value){
					if($value['FulfillmentNodeId']==$fullfillmentnodeid){
						//$email = $value[''];
						$configValues['first_address']=isset($value['Address1'])? $value['Address1'] : "";
						$configValues['second_address']=isset($value['Address2'])? $value['Address2'] : "";
						$configValues['city']=isset($value['City'])? $value['City'] : "";
						$configValues['state']=isset($value['State'])? $value['State'] : "";
						$configValues['zipcode']=isset($value['ZipCode'])? $value['ZipCode'] : "";
						break;
					}
				}
			} */
			//$configValues=['first_address'=>$address1,'second_address'=>$address2,'city'=>$city,'state'=>$state,'zipcode'=>$zipcode];
			if(is_array($configValues) && count($configValues)>0)
			{
				foreach ($configValues as $key => $value) 
				{
					Data::jetsaveConfigValue($merchant_id,$key,$value);
				}
				
			}
			//get email_id of shop owner
			$extenModel="";
	        $extenModel=JetExtensionDetail::find()->select('email')->where(['merchant_id'=>$merchant_id])->one();
	        if($extenModel){
	        	$email = $extenModel->email;
	        }
			$result=JetConfiguration::find()->where(['merchant_id'=>$merchant_id])->one();
			if($result==""){
				$model=new JetConfiguration();
				$model->api_host=self::API_HOST;
				$model->api_user=$api_user;
				$model->merchant_id=$merchant_id;
				$model->api_password=$api_password;
				$model->merchant=$merchant;
				$model->fullfilment_node_id=$fullfillmentnodeid;
				$model->merchant_email = $email;
				$model->jet_token = json_encode($responseToken);
				$model->save(false);
			}else{
				$result->api_user=$api_user;
				$result->api_password=$api_password;
				$result->merchant=$merchant;
				$result->fullfilment_node_id=$fullfillmentnodeid;
				$result->merchant_email = $email;
				$result->jet_token = json_encode($responseToken);
				$result->save(false);
			}
			return "3";
		}
		if(isset($jetConfig['api_user'])){
			return "3";
		}
		else
		{
			return;
		}
	}
}	