<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use frontend\modules\jet\components\Jetclientsapi;


class JetclientstatusController extends Controller{
	public function actionCheckstatus()
	{
		$merchant_id=$_GET['id'];
		$connection = Yii::$app->getDb();
		$jetConfig=$connection->createCommand("SELECT `api_user`,`api_password`,`fullfilment_node_id` FROM `jet_configuration` WHERE `merchant_id`='".$merchant_id."' ")->queryOne();
		
		if($jetConfig)
		{
			$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
			$api_host="https://merchant-api.jet.com/api";
			$api_user=$jetConfig['api_user'];
			$api_password=$jetConfig['api_password'];
		}		
		$helperJet = new Jetclientsapi($merchant_id,$api_host,$api_user,$api_password);
		
		if(isset($_GET['product_status']) && $_GET['product_status']==1)
		{
			$response = $helperJet->CGetRequest('/portal/merchantskus/export?statuses=Available+for+Purchase');
			echo "Available for purchase <hr>";
			echo "<pre>";
			print_r($response);
			die('<hr>');
		}
		if(isset($_GET['product_status']) && $_GET['product_status']==2)
		{
			$response = $helperJet->CGetRequest('/portal/merchantskus/export?statuses=Under+Jet+Review');
			echo "Orders Under Review <hr><pre>";print_r($response);
			die('<hr>');
		}
		if(isset($_GET['product_status']) && $_GET['product_status']==3)
		{
			$response = $helperJet->CGetRequest('/portal/merchantskus/export?statuses=Missing+Listing+Data');
			echo "Missing Listing Data <hr><pre>";print_r($response);
			die('<hr>');
		}
		if(isset($_GET['product_status']) && $_GET['product_status']==4)
		{
			$response = $helperJet->CGetRequest('/portal/merchantskus/export');
			echo "All Product uploaded to Jet <hr> <pre>";print_r($response);
			die('<hr>');
		}
		
		if(isset($_GET['check_sku']))
		{
			$sku=$_GET['check_sku'];
			$response = $helperJet->CGetRequest('/merchant-skus/'.rawurlencode($sku));
			echo "<pre>";print_r(json_decode($response,true));
			die('<hr>');
		}
		if(isset($_GET['order_details']))
		{
			//$order=$_GET['order'];
			$response="";
			$responseOrders=array();
			if($_GET['order_details']==1)
				$response = $helperJet->CGetRequest('/orders/ready');
			elseif($_GET['order_details']==2)
				$response = $helperJet->CGetRequest('/orders/acknowledged');
			elseif($_GET['order_details']==3)
				$response = $helperJet->CGetRequest('/orders/complete');
			$responseOrders=json_decode($response,true);
			echo "Ready Orders <hr><pre>";print_r($responseOrders);echo "<hr>";
			if(is_array($responseOrders) && count($responseOrders)>0){
				foreach($responseOrders['order_urls'] as $value)
				{
					echo "dsvxcv";
					$result="";
					$resultObject=array();
					$result = $helperJet->CGetRequest($value);
					$resultObject = json_decode($result);
					echo "<pre>";
					echo "<pre>";print_r($resultObject);echo "<hr>";
				}
			}
		}
		 
	}	
}
?>