<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use common\models\User;
use frontend\modules\jet\components\Createwebhook;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\models\JetConfiguration;
use Mailgun\Tests\Functional\InlineFileTest;

class JetapistatusController extends Controller
{
	protected $jetHelper,$merchant_id,$shopname,$token,$sc;
	
	public function beforeAction($action)
	{
		$this->merchant_id=Yii::$app->user->identity->id;
		$this->shopname = Yii::$app->user->identity->username;
		$this->token = Yii::$app->user->identity->auth_key;
		
		$this->sc = new ShopifyClientHelper($this->shopname, $this->token, PUBLIC_KEY, PRIVATE_KEY);

		$details = Data::getjetConfiguration($this->merchant_id);	

		$this->jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$details['api_user'],$details['api_password']);		
		return true;
	}
	public function actionCheckstatus()
	{	
		$status = "";
		if(isset($_GET['status_csv']) && $_GET['status_csv']==1){
			$response = $this->jetHelper->CGetRequest('/portal/merchantskus/export?statuses=Available+for+Purchase',$this->merchant_id,$status);
			echo $status."<hr>Available for purchase <hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['status_csv']) && $_GET['status_csv']==2){
			$response = $this->jetHelper->CGetRequest('/portal/merchantskus/export?statuses=Under+Jet+Review',$this->merchant_id,$status);
			echo $status."<hr>products Under Review <hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['status_csv']) && $_GET['status_csv']==3){
			$response = $this->jetHelper->CGetRequest('/portal/merchantskus/export?statuses=Missing+Listing+Data',$this->merchant_id,$status);
			echo $status."<hr>Missing Listing Data <hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['status_csv']) && $_GET['status_csv']==4){
			$response = $this->jetHelper->CGetRequest('/portal/merchantskus/export',$this->merchant_id,$status);
			echo $status."<hr>All Jet Products<pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['status'])){
			$value='';
			$value=implode('+',explode(' ',$_GET['status']));
			$response = $this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size='.$_GET['limit'].'&statuses='.$value,$this->merchant_id,$status);
			echo $status."<hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['setup'])){
			$response = $this->jetHelper->CGetRequest('/portal/merchantsetupstatus/',$this->merchant_id,$status);
			echo $status."<hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['return'])){
			$response = $this->jetHelper->CGetRequest('/portal/returnssetup/',$this->merchant_id,$status);
			echo $status."<hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['fulfillment'])){
			$response = $this->jetHelper->CGetRequest('/fulfillmentnodesbymerchantid/',$this->merchant_id,$status);
			echo $status."<hr><pre>";print_r($response);die("<hr>Details End");
		}
		if(isset($_GET['sku']))
		{
			$sku=$_GET['sku'];
			$response = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$this->merchant_id,$status);
			echo $status."<hr>SKU details<hr><pre>";print_r(json_decode($response,true));
			$response = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',$this->merchant_id);
			echo "<hr>QTY details<hr><pre>";print_r(json_decode($response,true));
			$response = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'/price',$this->merchant_id);
			echo "<hr>PRICE details<hr><pre>";print_r(json_decode($response,true));die("");
			
		}
		if(isset($_GET['sku_details'])){
			$sku=$_GET['sku_details'];
			$response = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'/salesdata',$this->merchant_id,$status);
			echo $status."<hr><pre>";
			print_r(json_decode($response,true));
			die("<hr>SKU SALES DATA ON JET");
		}
		if(isset($_GET['returns'])){
			if($_GET['returns']==1)
				$response = $this->jetHelper->CGetRequest('/returns/created',$this->merchant_id);
			elseif($_GET['returns']==2)
				$response = $this->jetHelper->CGetRequest('/returns/acknowledge',$this->merchant_id);
			elseif($_GET['returns']==3)
				$response = $this->jetHelper->CGetRequest('/returns/inprogress',$this->merchant_id);
			elseif($_GET['returns']==4)
				$response = $this->jetHelper->CGetRequest('/returns/'.rawurlencode('completed by merchant'),$this->merchant_id);

			$responseOrders=json_decode($response,true);
			echo "Return Data <hr><pre>";print_r($responseOrders);echo "<hr>";
			if(is_array($responseOrders) && count($responseOrders)>0){
				foreach($responseOrders['return_urls'] as $value)
				{
					//echo "dsvxcv";
					$result="";
					$resultObject=array();
					$result = $this->jetHelper->CGetRequest($value,$this->merchant_id);
					$resultObject = json_decode($result);
					echo "<pre>";
					echo "<pre>";print_r($resultObject);echo "<hr>";
				}
			}
		}
		if(isset($_GET['refund'])){
			$response = $this->jetHelper->CGetRequest('/refunds/state/'.$_GET['refund'],$this->merchant_id);
			var_dump($response);die;
		}
		if(isset($_GET['order']))
		{
			$response="";
			$responseOrders=array();
			if($_GET['order']==1)
				$response = $this->jetHelper->CGetRequest('/orders/created',$this->merchant_id);
			elseif($_GET['order']==2)
				$response = $this->jetHelper->CGetRequest('/orders/ready',$this->merchant_id);
			elseif($_GET['order']==3)
				$response = $this->jetHelper->CGetRequest('/orders/acknowledged',$this->merchant_id);
			elseif($_GET['order']==4)
				$response = $this->jetHelper->CGetRequest('/orders/inprogress',$this->merchant_id);
			elseif($_GET['order']==5)
				$response = $this->jetHelper->CGetRequest('/orders/complete',$this->merchant_id);
			elseif($_GET['order']==6)
				$response = $this->jetHelper->CGetRequest('/orders/directedCancel',$this->merchant_id);
				

			$responseOrders=json_decode($response,true);
			echo "Orders <hr><pre>";
			print_r($responseOrders);
			echo "<hr>";
			if(is_array($responseOrders) && count($responseOrders)>0)
			{
				foreach($responseOrders['order_urls'] as $value)
				{
					$result="";
					$resultObject=array();
					$result = $this->jetHelper->CGetRequest($value,$this->merchant_id);
					$resultObject = json_decode($result,true);
					echo "<pre>";
					print_r($resultObject);
					echo "<hr>";
				}
			}
		}
		if(isset($_GET['category'])){
		    $response = $this->jetHelper->CGetRequest('/taxonomy/nodes/'.$_GET['category'].'/attributes',$this->merchant_id);
		    echo "<pre>";
		    print_r(json_decode($response,true));
		    echo "<hr>";
		}
		if(isset($_GET['node_id'])){
			$response = $this->jetHelper->CGetRequest('/taxonomy/nodes/'.$_GET['node_id'],$this->merchant_id);
			echo "<pre>";
			var_dump(json_decode($response,true));
			echo "<hr>";
		}
		if(isset($_GET['shop']))
		{
			$shopData=$this->sc->call('GET', '/admin/shop.json');
			echo "<pre>";
		    print_r($shopData);
		    echo "<hr>";
		} 
	}	
	public function actionTotalproduct()
	{		 
		$countProducts = $countProductsUnpublish = [];
		$countProducts=$this->sc->call('GET', '/admin/products/count.json',array('published_status'=>'published'));
		$countProductsUnpublish=$this->sc->call('GET', '/admin/products/count.json',array('published_status'=>'unpublished'));
		if (isset($countProducts['errors'])) {
			echo "<pre>";
			print_r($countProducts);
			die;
		}
		$pages=ceil(count($countProducts)/250);
		
		$jProduct = $notType = 0;
		for($i=1;$i<=$pages;$i++)
		{
			$products = $this->sc->call('GET', '/admin/products.json', array('published_status'=>'published','limit'=>250,'page'=>$i));			
			if($products)
			{		
				
				foreach ($products as $value)
				{					
					if ($value['product_type']=="") {
						$notType++;
						continue;
					}
					if($value['variants'][0]['sku']=="") 
                    {
                        $jProduct ++;
                        continue;//break;
                    }                    									
				}
			}
		}
		echo "Products on shopify store (Total Published Products) :".$countProducts."<br>Products on shopify store (Total Unpublished Products) :".$countProductsUnpublish."<br>Products on shopify store (without SKU) :".$jProduct."<br>Products on shopify store (without Product Type) :".$notType;		
	}

	public function actionSwapasin()
	{
		$product=[];
		$count=0;
		$proParam="";
		$proOptParam="";
		if(isset($_GET['param']))
		{
			if($_GET['param']=="sku"){
				$proParam = $_GET['param'];
				$proOptParam = "option_sku";
			}
			elseif($_GET['param']=="upc")
			{
				$proParam = $_GET['param'];
				$proOptParam = "option_unique_id";
			}
			elseif ($_GET['param']=="mpn")
			{
				$proParam = $_GET['param'];
				$proOptParam = "option_mpn";
			}
		}
		if($proParam && $proOptParam)
		{
			$product=Data::sqlRecords('select `id`,'.$proParam.',`type` from jet_product where merchant_id="'.$this->merchant_id.'"','all','select');
			foreach($product as $value)
			{
				if ($value['type']=='variants')
				{
					$productVar=Data::sqlRecords('select `option_id`,'.$proOptParam.' from `jet_product_variants` where merchant_id="'.$this->merchant_id.'" AND `product_id`="'.$value['id'].'"','all','select');
					foreach($productVar as $value11)
					{
						if($value11[$proOptParam] && strlen($value11[$proOptParam])==10 && ctype_alnum($value11[$proOptParam]))
						{
							Data::sqlRecords('update `jet_product_variants` set `asin`="'.$value11[$proOptParam].'" , '.$proOptParam.'=""  where merchant_id="'.$this->merchant_id.'"  AND option_id="'.$value11['option_id'].'"  ',null,'update');
						}
					}
				}
				if($value[$proParam] && strlen($value[$proParam])==10 && ctype_alnum($value[$proParam]))
				{
					$count++;
					Data::sqlRecords('update jet_product set ASIN="'.$value[$proParam].'",'.$proParam.'="" where  merchant_id="'.$this->merchant_id.'" AND id="'.$value['id'].'"   ',null,'update');
				}
			}
		}
		echo "Total ASIN Swapped => ".$count ;
	}
	public function actionUpdatedata()
	{
		$target= \Yii::getAlias('@webroot').'/upload/UPC_PRODUCTS.csv';
		if(file_exists($target))
		{
			$row=0;
			$flag=true;
			
			if (($handle = fopen($target, "r")) !== FALSE)
			{
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
				{					
					$row++;
					$num = count($data);
					if($row==1)continue;
					$sql = "update jet_product set upc='".$data['1']."' WHERE id='".$data[0]."' AND merchant_id=1253";
					echo $sql."<hr>";
					//Data::sqlRecords($sql,null,'update');
					
				}
			}
		}	
	}
			
	public function actionFetchackorder()
	{		
		$query=$model=$queryObj="";
		$query="SELECT merchant_id, fullfilment_node_id, api_host, api_user, api_password, username, auth_key FROM `jet_configuration` config INNER JOIN `user` user_m ON (user_m.id = config.merchant_id) where config.merchant_id='".$merchant_id."'";
		$model = Data::sqlRecords($query,'one','select');
		if($model)
		{
			try
			{
				$countOrder=0;
				$orderdata="";
				$response=[];
				$orderdata = $this->jetHelper->CGetRequest('/orders/acknowledged',$merchant_id);
				$response  = json_decode($orderdata,True);
				if(isset($response['order_urls']) && count($response['order_urls']) > 0)
				{
					foreach($response['order_urls'] as $jetorderurl)
					{
						$result1 = $result = "";
						$result1 = $this->jetHelper->CGetRequest($jetorderurl,$merchant_id);
						$result = json_decode($result,true);
						
						if(sizeof($result) > 0 && isset($result['merchant_order_id']))
						{
							$resultdata = [];
							
							$merchantOrderid = $result['merchant_order_id'];
							$reference_order_id = $result['reference_order_id'];

							$queryObj="";
							$query="SELECT `merchant_order_id` FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND merchant_order_id='".$merchantOrderid."'";
							$resultdata = Data::sqlRecords($query,'one','select');
							
							if(!$resultdata)
							{
								$OrderItemData=[];
								$autoReject = false;
								$i = $ikey = 0;
								foreach ($result['order_items'] as $key=>$value)
								{									
									$OrderItemData['sku'][]=$value['merchant_sku'];
									$OrderItemData['order_item_id'][]=$value['order_item_id'];
								}
								
								if(isset($result['order_items']) && count($result['order_items'])>0)
								{									
									if(isset($ackData['errors'])){
										$countOrder++;
										$queryObj="";
										$query='INSERT INTO `jet_order_detail`
	    												(
		    												`merchant_id`,
		    												`merchant_order_id`,
		    												`order_data`,
		    												`reference_order_id`,
		    												`status`,
		    												`merchant_sku`,
		    												`order_item_id`,
		    												`deliver_by`
	    												)
														VALUES(
		    												"'.$merchant_id.'",
		    												"'.$result['merchant_order_id'].'",
															"'.addslashes($result1).'",
															"'.$result['reference_order_id'].'",
															"acknowledged",
		    												"'.implode(',',$OrderItemData['sku']).'",
		    												"'.implode(',',$OrderItemData['order_item_id']).'",
		    												"'.$result['order_detail']['request_delivery_by'].'"
	    												)';
										Data::sqlRecords($query,null,'insert');
									}
								}
								unset($response,$result,$resultdata,$ackData);
							}									
						}
					}					
				}					
			}
			catch (Exception $e)
			{
				Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
				return $this->redirect(['index']);
			}
		}	
	}		
	
	// Fetch all the orders from jet which are in completed state
	public function actionListcompletedorders()
	{
		$merchant_id=$this->merchant_id;//726;//Yii::$app->user->identity->id;
		
		$response="";
		$responseOrders=array();
			
		$response = $this->jetHelper->CGetRequest('/orders/complete',$merchant_id);
		$responseOrders=json_decode($response,true);
		echo "Orders <hr><pre>";print_r($responseOrders);die("<hr>");
		
		if(is_array($responseOrders) && count($responseOrders)>0)
		{
			foreach($responseOrders['order_urls'] as $value)
			{
				$result="";
				$resultObject=array();
				$result = $this->jetHelper->CGetRequest($value,$merchant_id);
				$resultObject = json_decode($result,true);
		
					
				$order_status_complete= $resultObject['status'];
		
				$reference_order_id= $resultObject['reference_order_id'];
		
				$merchant_order_id= $resultObject['merchant_order_id'];
		
				$merchant_sku = "";
				if (!empty($resultObject['order_items']))
				{
					foreach ($resultObject['order_items'] as $val)
					{
						$merchant_sku .=$val['merchant_sku'];
					}
				}
		
				$request_delivery_by=$resultObject['order_detail']['request_delivery_by'];
				$query = "SELECT reference_order_id FROM jet_order_detail WHERE merchant_id=".$merchant_id." AND reference_order_id=". $reference_order_id;
				$ISEXIAST = Data::sqlRecords($query,'one','select');
				if (empty($ISEXIAST))
				{
					$update_attr_query="INSERT INTO  `jet_order_detail` (`merchant_id`,`order_item_id`,`merchant_order_id`,`merchant_sku`,`deliver_by`,`status`,`order_data`,`reference_order_id`) values('".$merchant_id."','','$merchant_order_id','".$merchant_sku."','".$request_delivery_by."','".$order_status_complete."','','".$reference_order_id."')";
					die($update_attr_query);
					//Data::sqlRecords($update_attr_query,null,'insert');
				}
			}
		}
	}
	
	public function actionSyncshopifypriceonjet()
	{
		$connection=Yii::$app->getDb();
		$merchant_id = Yii::$app->user->identity->id;
		//if ($merchant_id==14){
			$shopname = Yii::$app->user->identity->username;
			$token = Yii::$app->user->identity->auth_key;
			
			$shopifymodel=Shopifyinfo::getShipifyinfo();
			define("SHOPIFY_API_KEY", $shopifymodel[0]['api_key']);
			define("SHOPIFY_SECRET", $shopifymodel[0]['secret_key']);
			$sc = new ShopifyClientHelper($shopname, $token, SHOPIFY_API_KEY, SHOPIFY_SECRET);
			
			$jetConfig=array();
						
			$jetConfig = $connection->createCommand("SELECT `fullfilment_node_id`,`api_user`,`api_password` FROM `jet_configuration` WHERE merchant_id='".$merchant_id."'")->queryOne();
			if($jetConfig)
			{
				$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
				$api_host="https://merchant-api.jet.com/api";
				$api_user=$jetConfig['api_user'];
				$api_password=$jetConfig['api_password'];
			}
			unset($jetconfig);
			$jetHelper = new Jetapi($api_host,$api_user,$api_password);
			
			$products=array();
			$products = $this->sc->call('GET', '/admin/products.json', array('published_status'=>'published','limit'=>250,'page'=>1));
			foreach ($products as $key)
			{
				if (count($key['variants'])<2)
				{
					$prod_sku=$key['variants'][0]['sku'];
					$prod_id=$key['variants'][0]['product_id'];
					$prod_price=$key['variants'][0]['price'];
					$prod_price=(float)$prod_price;
					$prod_detail = $connection->createCommand("SELECT `id`,`sku`,`price` FROM `jet_product` WHERE id='".$prod_id."' AND merchant_id='".$merchant_id."'")->queryOne();
					if (($prod_detail['price']!=$prod_price ) )
					{							
						if(Jetproductinfo::checkSkuOnJet($prod_sku,$jetHelper,$merchant_id)==false){
							return;
						}
						$response=Jetproductinfo::updatePriceOnJet($prod_sku,$prod_price,$jetHelper,$fullfillmentnodeid,$merchant_id);
						$update_prod = $connection->createCommand("UPDATE `jet_product` SET `price`='".$prod_price."' WHERE id='".$prod_id."' AND merchant_id='".$merchant_id."'")->execute();
					}					
				}
			}				
		//}
	}
		
	public function actionGetproduct()
	{
		$prod_id=$_GET['id'];
		$countProducts=$this->sc->call('GET', '/admin/products/'.$prod_id.'.json');
	  	echo "<pre>";
		print_r($countProducts);
		die ;
	}	
	
	public function actionShopifypayment()
	{	
		$isPayment=false;
		
		$response="";
//		$response=$this->sc->call('GET','/admin/recurring_application_charges/1144706.json');
		echo "<pre>";
		print_r($response);
		die;
	}	
				
	// update acknowledge order status in DB
	public function actionUpdateackorderstatus()
	{
		$allClients = [];
		$query = $sql = $model = $queryObj = "";
		$sql="SELECT DISTINCT `jet_order_detail`.`merchant_id` FROM `jet_order_detail` INNER JOIN `jet_shop_details` ON (`jet_order_detail`.`merchant_id` = `jet_shop_details`.`merchant_id`) where `jet_shop_details`.`install_status`='1' ";
		$allClients = Data::sqlRecords($sql,'all','select');
		foreach ($allClients as $key => $value) 
		{
			$query = ""; $model = [];
			$merchant_id = $value['merchant_id'];
			if ($merchant_id !=14) {
				continue;
			}
			$query = "SELECT merchant_id, fullfilment_node_id, api_host, api_user, api_password, username, auth_key FROM `jet_configuration` config INNER JOIN `user` user_m ON (user_m.id = config.merchant_id) where config.merchant_id='".$merchant_id."'";	
			$model = Data::sqlRecords($query,'one','select');
			if ( !empty($model) && count($model)>0) 
			{
				try
				{
					$orderdata="";
					$response=array();
					$fullfillmentnodeid=$model['fullfilment_node_id'];
					$api_host=$model['api_host'];
					$api_user=$model['api_user'];
					$api_password=$model['api_password'];
					$jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
					$orderdata = $jetHelper->CGetRequest('/orders/acknowledged',$merchant_id);
					$response  = json_decode($orderdata,True);

					if(isset($response['order_urls']) && count($response['order_urls']) > 0)
					{
						foreach($response['order_urls'] as $jetorderurl)
						{
							$merchantOrderDataArray = [];
							$merchantOrderDataArray = explode('/', $jetorderurl);
							if ( !empty($merchantOrderDataArray) && count($merchantOrderDataArray)>0) 
							{								
								$merchantOrderid = $query = "";
								$merchantOrderid = $merchantOrderDataArray[3];
								$resultdata = array();
								$query = "SELECT `merchant_order_id` FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND merchant_order_id='".$merchantOrderid."' AND `status` !='acknowledged' ";
								$resultdata = Data::sqlRecords($query,'one','select');
								
								if(!empty($resultdata) && count($resultdata)>0)
								{
									Data::sqlRecords("UPDATE `jet_order_detail` SET `status`='acknowledged' WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$merchantOrderid."' " , null,"UPDATE");
									echo $merchant_id." ===> ".$merchant_order_id." => status changed in DB <hr><hr><hr>";
								}	
							}							
						}					
					}					
				}catch (Exception $e)
				{
					Yii::$app->session->setFlash('error', "Exception:".$e->getMessage());
					return $this->redirect(['index']);
				}	
			} die("completed testing");
		}			
	}

	public function actionUpdateorderemail()
	{
		$updatedDetails = array();
		$order_id = $_GET['id'];
		$order_email = $_GET['email'];
		
		$emailArray = array();
		$emailArray=array("order"=> array(
				"id"=> $order_id,
		   		"email"=> $order_email
			)
		);		
		$updatedDetails = $this->sc->call('PUT', '/admin/orders/'.$order_id.'.json',$emailArray);
	  	echo "<pre>";
		print_r($updatedDetails);
		die ;
	}
	public function actionGetorderdetail()
	{
		$shipmentDetails = [];
		$order_id = $_GET['id'];
		
		$shipmentDetails = $this->sc->call('GET', '/admin/orders/'.$order_id.'.json');
	  	echo "<pre>";
		print_r($shipmentDetails);
		die ;
	}
	
	public function actionCancelpaymentplan()
	{
		$response=[];
		// $response=$this->sc->call('DELETE','/admin/recurring_application_charges/2752084.json');
		echo "<pre>";
		print_r($response);
		die;
	}
	public function actionRemovefailedorders()
	{
		$failedOrders = array();
		$failedOrders = Data::sqlRecords("SELECT `merchant_order_id` FROM `jet_order_import_error` WHERE `merchant_id`='".$this->merchant_id."'	","all","select");
		
		foreach ($failedOrders as $key => $value) 
		{				
			$orderExist = array();
			$orderExist = Data::sqlRecords("SELECT `merchant_order_id` FROM `jet_order_detail` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$value['merchant_order_id']."'	","one","select");

			if (!empty($orderExist)) 
			{
				Data::sqlRecords("DELETE FROM `jet_order_import_error` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$value['merchant_order_id']."'	",null,"update");	
				echo "Order deleted => ".$value['merchant_order_id']."<hr>";		
			}
		}
		
	}
	public function actionCheckorderstatus()
	{
		$merchantOrderid = ""; 
		$merchantOrderid = $_GET['merchant_order_id'];
		if($merchantOrderid !="")
		{			
			$response = ""; 
			$responseOrders = array();
			$response = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchantOrderid,$this->merchant_id);			
			$responseOrders=json_decode($response,true);
			echo "<pre>";
			print_r($responseOrders);die;
		}
	}
	public function actionCheckreturnstatus()
	{
		$returnId = ""; 
		$returnId = trim($_GET['return_id']);
		
		if($returnId !="")
		{			
			$response = ""; 
			$responseOrders = [];
			$response = $this->jetHelper->CGetRequest('/returns/state/'.rawurlencode($returnId),$this->merchant_id);			
			$responseOrders=json_decode($response,true);
			echo "<pre>";
			print_r($responseOrders);
			die("<hr>Return details end");
		}else
		{
			die("Please Enter return_id");
		}
	}
	
	public function actionGetwebhook()	
	{
		echo "<pre>";
		print_r(Webhook::createWebhooks($this->shopname));
		die("<hr>All Webhooks");
    }

	public function actionUpdaterecurringpaymentstatus()
	{
	    date_default_timezone_set('Asia/Kolkata');
	    
	    $clientDetails = $response = []; 
	    $filenameOrig = $fileOrig = "";
	    $CurDate= date("Y-m-d H:i:s");
	    $nextDate =date('Y-m-d H:i:s',strtotime('+3 days', strtotime(date('Y-m-d H:i:s'))));
	    $finalExpiredate =date('Y-m-d H:i:s',strtotime('+32 days', strtotime(date('Y-m-d H:i:s'))));
	     
	    $sqlrecurring = "SELECT `jet_recurring_payment`.`id`,`jet_recurring_payment`.`status`,`jet_recurring_payment`.`merchant_id`, `plan_type` , `username` ,`auth_key`,`jet_shop_details`.`install_status`  FROM `jet_recurring_payment` INNER JOIN `user` ON `jet_recurring_payment`.`merchant_id`=  `user`.`id` INNER JOIN `jet_shop_details` ON `jet_shop_details`.`merchant_id`=  `user`.`id` WHERE `jet_shop_details`.`expired_on` BETWEEN '{$CurDate}' AND '{$nextDate}' ";
        $clientDetails =  Data::sqlRecords($sqlrecurring,'all','select');
	   	$dir = Yii::getAlias('@webroot').'/var/jet/PaymentUpdateRecurring/cron';
	    if (!file_exists($dir))
	    {
	      mkdir($dir,0775, true);
	    }
	    
	    if (!empty($clientDetails)) 
	    {
	      foreach ($clientDetails as $key => $value) 
	      {
	      	$merchant_id = $value['merchant_id'];
	      	$planType = $value['plan_type'];
	      	$shop = $value['username'];
	      	$token = $value['auth_key'];
	      	$payment_id = $value['id'];
	      	$filenameOrig=$dir.'/'.$merchant_id.'.log';
	    
	    	$fileOrig=fopen($filenameOrig,'a+');
	      	if ($value['install_status']=='0')
	        {
		        $updatePaymentQuery="UPDATE `jet_recurring_payment` SET `status` = 'cancelled' WHERE `jet_recurring_payment`.`id` ='".$payment_id."' AND `merchant_id`='".$merchant_id."'";
		        fwrite($fileOrig,PHP_EOL."CHANGING STATUS TO CANCELLED (APP UN-INSTALLED) [QUERY]".PHP_EOL.$updatePaymentQuery.PHP_EOL);
		        Data::sqlRecords($updatePaymentQuery,null,'update');
	        }
	        else
	        {
	        	$sc = new ShopifyClientHelper($shop, $token,PUBLIC_KEY, PRIVATE_KEY);
	       
			    if ($planType=='Recurring Plan (Monthly)'){
			        $response=$this->sc->call('GET','/admin/recurring_application_charges/'.$payment_id.'.json');
			    }elseif ($planType=='Recurring Plan (Yearly)'){
			        $response=$this->sc->call('GET','/admin/application_charges/'.$payment_id.'.json');
			    }
			    if (isset($response) && isset($response['status']) && $response['status']=='cancelled')
			    {
			        $updatePaymentQuery="UPDATE `jet_recurring_payment` SET `status` = '".$response['status']."' WHERE `jet_recurring_payment`.`id` ='".$payment_id."' AND `merchant_id`='".$merchant_id."'";
			        fwrite($fileOrig,PHP_EOL."CHANGING STATUS TO CANCELLED (APP INSTALLED) [QUERY]".PHP_EOL.$updatePaymentQuery.PHP_EOL);
			        Data::sqlRecords($updatePaymentQuery,null,'update');
			        $updatePlanQuery="UPDATE `jet_shop_details` SET `purchase_status` = 'Not Purchase' WHERE `merchant_id` ='".$merchant_id."' ";
			        fwrite($fileOrig,PHP_EOL."CHANGING PURCHASE STATUS TO NOT PURCHASE (APP INSTALLED) [QUERY]".PHP_EOL.$updatePlanQuery.PHP_EOL);
			        Data::sqlRecords($updatePlanQuery,null,'update');		        
			    }elseif (isset($response) && isset($response['status']) && $response['status']=='active'  && $planType=='Recurring Plan (Monthly)')
			    {		         
			        $updatePlanQuery="UPDATE `jet_shop_details` SET `expired_on`='".$finalExpiredate."', `purchase_status`='Purchased' WHERE `merchant_id` ='".$merchant_id."' ";
			        fwrite($fileOrig,PHP_EOL."EXTENDING EXPIRE DATE (APP INSTALLED) [QUERY]".PHP_EOL.$updatePlanQuery.PHP_EOL);
			        Data::sqlRecords($updatePlanQuery,null,'update');		        		        
			    }		  	        
		        fclose($fileOrig);
	        }       
	      }       
	    }       
	}

    public function actionRemoveduplicateproducttype()
    {
    	$sql = "DELETE  FROM `jet_category_map` WHERE `id` NOT IN (SELECT `id` FROM (SELECT `id`,`product_type`,`merchant_id` FROM `jet_category_map` GROUP BY `product_type`,`merchant_id`) jetcat)";
    	Data::sqlRecords($sql,null,'delete');
    	echo "Duplicate product types are removed";
    }
    public function actionUpdateshippeddate()
    {
    	$count = 0;
    	$sql = "SELECT `created_at`,`merchant_order_id` FROM `jet_order_detail` ";
    	$details = Data::sqlRecords($sql,"ALL","select");
    	foreach ($details as $key => $value) 
    	{
    		$count++;
    		$sql1 = "UPDATE `jet_order_detail` SET `shipped_at`='{$value['created_at']}' WHERE `merchant_order_id`='{$value['merchant_order_id']}' ";
    		Data::sqlRecords($sql1,null,'update');    	
    	}
    	echo $count." rows updated";    	
    }

    // Update status in db based on jet status
    public function actionUpdateorderstatus()
    {
    	$merchant_id = $this->merchant_id;
    	$sql = "SELECT `merchant_order_id` FROM `jet_order_detail` WHERE `merchant_id`='{$merchant_id}' AND  `status`='acknowledged' " ;
    	$orderDetails = Data::sqlRecords($sql,"all","select");
    	foreach($orderDetails as $key=>$orderid)
    	{
    		$response = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$orderid['merchant_order_id'],$this->merchant_id);			
			$responseOrders=json_decode($response,true);
			$updateQuery = "UPDATE jet_order_detail SET status='".$responseOrders['status']."' WHERE `merchant_order_id` = '".$orderid['merchant_order_id']."' AND `merchant_id`='".$merchant_id."'";
			echo $updateQuery."<hr>";
			Data::sqlRecords($updateQuery,NULL,'update');
    	}		
    }

    // Inserting details into jet_shop_details for removing table app_status and jet_extention_detail 
    public function actionU ()
    {    	
    	$details = [];
    	// query for live db
    	$query="SELECT user.id as m_id, username as shop_url, shop_name, extn.email,app_status.status as install_status, extn.install_date as installed_on , extn.date as purchased_on, extn.expire_date as expired_on , extn.status as purchase_status,sendmail   FROM `user` LEFT JOIN `jet_extension_detail` as extn ON (user.id = extn.merchant_id) LEFT join app_status on (user.id = app_status.merchant_id) WHERE auth_key !='' AND user.id NOT in (SELECT merchant_id from jet_shop_details)";
    	
    	$details = Data::sqlRecords($query,'all','select');
    	
    	foreach ($details as $key => $value) 
    	{
    		$notExist = [];
    		$notExist = Data::sqlRecords("SELECT `merchant_id` FROM `jet_shop_details` WHERE `merchant_id`='".$value['m_id']."'",'one','select');
    		if (empty($notExist)) 
    		{
    			$sqlInsert = "INSERT INTO `jet_shop_details` (`merchant_id`, `shop_url`, `shop_name`, `email`, `install_status`, `installed_on`, `expired_on`, `purchased_on`, `purchase_status`,`sendmail`) VALUES ('".$value['m_id']."', '".addslashes($value['shop_url'])."', '".addslashes($value['shop_name'])."', '".addslashes($value['email'])."', '".$value['install_status']."', '".$value['installed_on']."', '".$value['expired_on']."', '".$value['purchased_on']."', '".$value['purchase_status']."','".$value['sendmail']."')";	
    			echo $sqlInsert."<hr>";
    			Data::sqlRecords($sqlInsert,null,'insert');
    		}/*else
    		{
    			$sql_update = "UPDATE `jet_shop_details` SET `purchase_status` = '".$value['purchase_status']."' where `merchant_id`= '".$value['m_id']."' ";
    			echo $sql_update."<hr>";
    			Data::sqlRecords($sql_update,null,'update');
    		}  */  		
    	}
    } 
    // updating webhook url for all shopify jet clients
    public function actionUpdatejetwebhookurl()
    {
    	$allClients = []; 

    	$sql = "SELECT `id`,`username`,`auth_key` FROM `user` WHERE `auth_key`!='' AND id=14";

    	$allClients = Data::sqlRecords($sql,'all','select');
    	foreach ($allClients as $key => $value) 
    	{
			$sc = new ShopifyClientHelper($value['username'], $value['auth_key'],PUBLIC_KEY,PRIVATE_KEY);
			//Createwebhook::createNewWebhook($sc,$value['username'],$value['auth_key']);
			//echo "<hr><pre>";
			print_r($sc->call('GET','/admin/webhooks.json'));
			die("<hr>Newly Created");
    	}    	
    }

    public function actionDeleteWebhook()
    {
    	echo $url=Yii::$app->getUrlManager()->createAbsoluteUrl('shopifywebhook/product', 'https');die;
    	$sql = "SELECT `id`,`username`,`auth_key` FROM `user` WHERE `auth_key`!='' AND id=1089";
    	$allClients = Data::sqlRecords($sql,'all','select');
    	foreach ($allClients as $key => $value) 
    	{
    		$sc = new ShopifyClientHelper($value['username'], $value['auth_key'],PUBLIC_KEY,PRIVATE_KEY);
    		$topics = [
                    "products/update",
                    "products/delete",
                    "app/uninstalled",
                    "orders/fulfilled",
                    "orders/cancelled",
                    "products/create",
                    "orders/partially_fulfilled",
                    "orders/create",
                   // "products/create"
                  ];
    		self::deleteWebhook(14,$value['username'],$topics,$sc);
    		$response=$sc->call('GET','/admin/webhooks.json');
    		var_dump($response);die("cbcvb");
    	}
    }

    public static function deleteWebhook($m_id,$shop,$topics,$sc)
    {

    	foreach ($topics as $val) 
    	{
    		$webhooks=$sc->call('GET','/admin/webhooks.json',['topic'=>$val]);
    		if(!isset($webhooks['errors']))
    		{
    			foreach ($webhooks as $key => $value) 
	    		{
	    			$sc->call('DELETE','/admin/webhooks/'.$value['id'].'.json');    		
	    		}
	    	}
    	}
    }

    public function actionCheckPayment()
    {
    	if(isset($_GET['mid']))
    	{
    		$shopData=Data::sqlRecords("SELECT `auth_key`,`username` FROM `user` WHERE id=".$_GET['mid']." LIMIT 0,1","one","select");
    		if(isset($shopData['username'])){
    			$sc = new ShopifyClientHelper($shopData['username'], $shopData['auth_key'],PUBLIC_KEY, PRIVATE_KEY);
    			$response=$sc->call('GET','/admin/application_charges.json');
    			echo "pre";print_r($response);die("cvb");
    		}
    	}
    }	

	public function actionImportproductType()
    {
        $merchant_id =MERCHANT_ID;        
        $countProducts=$pages=0;
                
        $countProducts=$this->sc->call('GET', '/admin/products/count.json',['published_status'=>'published']); 
        
        if(isset($countProducts['errors'])){
            Yii::$app->session->setFlash('error', "Shopify api token is incorrect...");
            return $this->redirect(['index']);
        }
        $pages=ceil($countProducts/250);
        // $pages+=1;
        $session = Yii::$app->session;
        if(!is_object($session)){
            Yii::$app->session->setFlash('error', "Can't initialize Session.Product(s) import cancelled");
            return $this->redirect(['index']);
        }
        $session->set('product_page',$pages);
        return $this->render('batchimport', [
                'totalcount' => $countProducts,
                'pages'=>$pages
        ]);
    }
    public function actionProducttypeimportajax()
    {
        $index = Yii::$app->request->post('index');

        $merchant_id = $this->merchant_id;
        try
        {                       
            $importParam = [];
            $importParam['published_status'] = "published";
            $importParam['limit']=250;
            $importParam['page']=$index;
            $products = $this->sc->call('GET', '/admin/products.json',$importParam); 
            if(isset($products['errors'])){
                $returnArr['error'] = $products['errors'];
                return json_encode($returnArr);
            }
            $readyCount = 0;
            if($products)
            {
                foreach ($products as $prod)
                {
                    $response = Jetproductinfo::updateProductType($prod, $merchant_id);
                    if(isset($response['success']))
                        $readyCount++;                    
                }
            }          
        }        
        catch (ShopifyCurlException $e){
            return $returnArr['error'] = $e->getMessage();
        }
        $returnArr['success']['count'] = $readyCount;        
        return json_encode($returnArr);
    }
    
    public function actionCheckRecurringPayment()
    {
    	$jet_extension_shop_detail=Data::sqlRecords("SELECT `shop`.`merchant_id`,`shop`.`expired_on` as `shop_expired`,`shop`.`purchase_status`,`extension`.`status`,`extension`.`expire_date` as `extension_expire` FROM `jet_shop_details` as `shop` LEFT JOIN `jet_extension_detail` as `extension` ON `shop`.`merchant_id`=`extension`.`merchant_id` WHERE 1","all","select");
    	foreach ($jet_extension_shop_detail as $value)
    	{
    		$extension_expire=strtotime($value['extension_expire']);
    		$shop_expired=strtotime($value['shop_expired']);
    		if($extension_expire>$shop_expired)
    		{
    			echo $value['merchant_id']."<br>";
    		}
    	}

    } 
    
    public function actionGetmarketingevent()
    {
    	$data = [];
    	 
    	$data = $this->sc->call("GET","/admin/marketing_events.json");
    	echo "<hr><pre>";
    	print_r($data);
    	die("<hr>hgf");
    }
    
    /*public function actionMarketingeventapi()
    {
    	$data = $eventData = [];
    	$eventData = [
    			"marketing_event"=> [
    					"started_at"=> date("Y-m-d H:i:s"),
    					"utm_campaign"=> "Event test",
    					"utm_source"=> "cedcommerce",
    					"utm_medium"=> "cpc",
    					"event_type"=> "ad",
    					"budget"=> "11.1",
    					"budget_type"=> "daily",
    					"currency"=> "CAD",
    					"manage_url"=> 'https://shopify.cedcommerce.com/integration/',
    					"preview_url"=> 'https://shopify.cedcommerce.com/integration/',
    					"referring_domain"=> "cedcommerce.com",
    					"marketing_channel"=> "social",
    					"paid"=> false
    			]
    	];
    	$data = $this->sc->call("POST","/admin/marketing_events.json",$eventData);
    	echo "<hr><pre>";
    	print_r($data);
    	die("<hr>hgf");
    }
    public function actionUpdateevent()
    {
    	$data = $eventData = [];
    	$eventData = [
    			"marketing_event"=> [
    					"id"=> 18550727,
    					"event_target"=> "",
    					"event_type"=> "post",
    					"remote_id"=> "1000:2000",
    					"started_at"=> "2017-02-01T19:00:00-05:00",
    					"ended_at"=>"2017-02-02T19:00:00-05:00",
    					"scheduled_to_end_at"=> "2017-02-03T19:00:00-05:00",
    					"budget"=> "11.1",
    					"currency"=> "CAD",
    					"manage_url"=> 'https://shopify.cedcommerce.com/integration/',
    					"preview_url"=> 'https://shopify.cedcommerce.com/integration/',
    					"utm_source"=> "cedcommerce",
    					"utm_medium"=> "cedcommerce-post",
    					"budget_type"=> "daily",
    					"description"=> 'This is a test compeign ',
    					"paid"=> false,
    					"breadcrumb_id"=> null,
    					"marketed_resources"=> [
    					]
    			]
    	];
    	$data = $this->sc->call("PUT","/admin/marketing_events/18550727/engagements.json ",$eventData);
    	echo "<hr><pre>";
    	print_r($data);
    	die("<hr>hgf");
    }*/
    public function actionSwapVariantUpc()
    {
    	if(isset($_GET['offset'],$_GET['limit'])){
    		$variantCollection = Data::sqlRecords("SELECT product_id,option_id,option_sku,option_unique_id FROM `jet_product_variants` WHERE merchant_id=".$this->merchant_id." LIMIT ".$_GET['offset'].",".$_GET['limit'],"all","select");
    		$count=0;
    		$ids=[];
    		if(is_array($variantCollection) && count($variantCollection)>0)
    		{
    			foreach ($variantCollection as $value) 
    			{
    				if(in_array($value['product_id'], $ids))
    				{
    					continue;
    				}	
    				$proCollection=Data::sqlRecords("SELECT upc,sku FROM `jet_product` WHERE variant_id=".$value['option_id']." LIMIT 0,1","one","select");
    				if(isset($proCollection['upc']) && ($proCollection['upc']!=$value['option_unique_id'] && $proCollection['sku']==$value['option_sku']))
					{

						$count++;
						$ids[]=$value['product_id'];
						Data::sqlRecords("UPDATE `jet_product` SET upc='".$value['option_unique_id']."' WHERE id=".$value['product_id']);
    				}
    			}	
    		}	
    	}
    	print_r($ids);
    	echo "<br>".$count;
    	
    }      
    public function actionGetMerchantData()
    {

    	$productCollection= Data::sqlRecords("select merchant_id,count(*) as product_count from jet_product group by merchant_id","all");
    	$jetOrderCollection= Data::sqlRecords("select merchant_id,count(*) as jet_order_count from jet_order_detail group by merchant_id","all");
    	$walmartOrderCollection= Data::sqlRecords("select merchant_id,count(*) as wal_order_count from walmart_order_details group by merchant_id","all");
    	$walpaymentPlanCollection= Data::sqlRecords("select merchant_id,status as plan_type from walmart_extension_detail","all");
    	$jetpaymentPlanCollection= Data::sqlRecords("select merchant_id,purchase_status as plan_type from jet_shop_details","all");

    	
    	//walmart order monthly
    	$walmartLastMonthOrder = Data::sqlRecords("SELECT merchant_id,count(*) wal_last_month_order_count FROM `walmart_order_details` WHERE created_at>=NOW() - INTERVAL 1 MONTH GROUP by merchant_id","all");
    	$walmartTwoLastMonthOrder = Data::sqlRecords("SELECT merchant_id,count(*) wal_two_month_order_count FROM `walmart_order_details` WHERE created_at>=NOW() - INTERVAL 2 MONTH GROUP by merchant_id","all");
    	$walmartThreeLastMonthOrder = Data::sqlRecords("SELECT merchant_id,count(*) wal_three_month_order_count FROM `walmart_order_details` WHERE created_at>=NOW() - INTERVAL 3 MONTH GROUP by merchant_id","all");

    	//jet order monthly
    	$jetLastMonthOrder = Data::sqlRecords("SELECT merchant_id,count(*) jet_last_month_order_count FROM `jet_order_detail` WHERE created_at>=NOW() - INTERVAL 1 MONTH GROUP by merchant_id","all");
    	$jetTwoLastMonthOrder = Data::sqlRecords("SELECT merchant_id,count(*) jet_two_month_order_count FROM `jet_order_detail` WHERE created_at>=NOW() - INTERVAL 2 MONTH GROUP by merchant_id","all");
    	$jetThreeLastMonthOrder = Data::sqlRecords("SELECT merchant_id,count(*) jet_three_month_order_count FROM `jet_order_detail` WHERE created_at>=NOW() - INTERVAL 3 MONTH GROUP by merchant_id","all");
    	//var_dump($jetThreeLastMonthOrder);die;


    	/*var_dump($walmartLastMonthOrder);
    	var_dump($walmartTwoLastMonthOrder);
    	var_dump($walmartThreeLastMonthOrder);
    	var_dump($jetLastMonthOrder);
    	var_dump($jetTwoLastMonthOrder);
    	var_dump($jetThreeLastMonthOrder);die("cb");*/

    	$logPath = \Yii::getAlias('@webroot').'/var/';
		if (!file_exists($logPath)){
			mkdir($logPath,0775, true);
		}
		$base_path=$logPath.'/analytics-report.csv';
		$file = fopen($base_path,"w");
		fputcsv($file,['MERCHANT ID','PRODUCT COUNT','ORDER COUNT(JET/WALMART)','PLAN STATUS(JET)','PLAN STATUS(WALMART)','LAST 1 MONTH ORDER(WALMART)','LAST 2 MONTH ORDER(WALMART)','LAST 3 MONTH ORDER(WALMART)','LAST 1 MONTH ORDER(JET)','LAST 2 MONTH ORDER(JET)','LAST 3 MONTH ORDER(JET)']);
		$analyticsData=[];

    	foreach ($productCollection as $key => $value) 
    	{
    		$analyticsData[$value['merchant_id']]['product_count']=$value['product_count'];
    	}
    	foreach ($jetOrderCollection as $order_value) 
    	{
    		if($order_value['jet_order_count']>1){

    			$analyticsData[$order_value['merchant_id']]['order_count']=$order_value['jet_order_count'];
    		}
    	}

    	foreach ($walmartOrderCollection as $order_val) 
    	{
    		if($order_val['wal_order_count']>1){
    			if(isset($analyticsData[$order_val['merchant_id']]['order_count']))
    				$analyticsData[$order_val['merchant_id']]['order_count']+=$order_val['wal_order_count'];
    			else
    				$analyticsData[$order_val['merchant_id']]['order_count']=$order_val['wal_order_count'];
    		}
    	}

    	foreach ($walpaymentPlanCollection as $order_val) 
    	{
    		
    		$analyticsData[$order_val['merchant_id']]['wal_plan_type']=$order_val['plan_type'];
    	}

    	foreach ($jetpaymentPlanCollection as $order_val) 
    	{
    		$analyticsData[$order_val['merchant_id']]['jet_plan_type']=$order_val['plan_type'];
    	}


    	//walmart orders monthly
    	if(is_array($walmartLastMonthOrder)){
    		foreach ($walmartLastMonthOrder as $order_val) 
	    	{
	    		if($order_val['wal_last_month_order_count']>1)
	    			$analyticsData[$order_val['merchant_id']]['wal_last_month_order_count']=$order_val['wal_last_month_order_count'];
	    		else
	    			$analyticsData[$order_val['merchant_id']]['wal_last_month_order_count']=0;
	    	}
    	}

    	if(is_array($walmartTwoLastMonthOrder))
    	{
	    	foreach ($walmartTwoLastMonthOrder as $order_val) 
	    	{
	    		if($order_val['wal_two_month_order_count']>1)
	    			if(isset($analyticsData[$order_val['merchant_id']]['wal_last_month_order_count']))
	    				$analyticsData[$order_val['merchant_id']]['wal_two_month_order_count']=$order_val['wal_two_month_order_count']-$analyticsData[$order_val['merchant_id']]['wal_last_month_order_count'];
	    			else
	    				$analyticsData[$order_val['merchant_id']]['wal_two_month_order_count']=$order_val['wal_two_month_order_count'];
	    		else
	    			$analyticsData[$order_val['merchant_id']]['wal_two_month_order_count']=0;
	    	}
	    }
	    	
	    if(is_array($walmartThreeLastMonthOrder))
    	{	
	    	foreach ($walmartThreeLastMonthOrder as $order_val) 
	    	{
	    		if($order_val['wal_three_month_order_count']>1)
	    			if(isset($analyticsData[$order_val['merchant_id']]['wal_last_month_order_count'],$analyticsData[$order_val['merchant_id']]['wal_two_month_order_count'])){
	    				$analyticsData[$order_val['merchant_id']]['wal_three_month_order_count']=$order_val['wal_three_month_order_count']-$analyticsData[$order_val['merchant_id']]['wal_two_month_order_count']-$analyticsData[$order_val['merchant_id']]['wal_last_month_order_count'];
	    			}
	    			else
	    				$analyticsData[$order_val['merchant_id']]['wal_three_month_order_count']=$order_val['wal_three_month_order_count'];
	    		else
	    			$analyticsData[$order_val['merchant_id']]['wal_three_month_order_count']=0;
	    	}
	    }	

    	//walmart orders monthly
    	if(is_array($jetLastMonthOrder))
    	{
	    	foreach ($jetLastMonthOrder as $order_val) 
	    	{
	    		if($order_val['jet_last_month_order_count']>1)
	    			$analyticsData[$order_val['merchant_id']]['jet_last_month_order_count']=$order_val['jet_last_month_order_count'];
	    		else
	    			$analyticsData[$order_val['merchant_id']]['jet_last_month_order_count']=0;
	    	}
	    }

	    if(is_array($jetTwoLastMonthOrder))
    	{
	    	foreach ($jetTwoLastMonthOrder as $order_val) 
	    	{
	    		if($order_val['jet_two_month_order_count']>1){
	    			if(isset($analyticsData[$order_val['merchant_id']]['jet_last_month_order_count']))
	    				$analyticsData[$order_val['merchant_id']]['jet_two_month_order_count']=$order_val['jet_two_month_order_count']-$analyticsData[$order_val['merchant_id']]['jet_last_month_order_count'];
	    			else
	    				$analyticsData[$order_val['merchant_id']]['jet_two_month_order_count']=$analyticsData[$order_val['merchant_id']]['jet_last_month_order_count'];
	    		}
	    		else
	    			$analyticsData[$order_val['merchant_id']]['jet_two_month_order_count']=0;
	    	}
    	}

    	if(is_array($jetThreeLastMonthOrder))
    	{
	    	foreach ($jetThreeLastMonthOrder as $order_val) 
	    	{
	    		if($order_val['jet_three_month_order_count']>1)
	    		{
	    			//echo $order_val['jet_three_month_order_count'];die("Cb");
	    			if(isset($analyticsData[$order_val['merchant_id']]['jet_last_month_order_count'],$analyticsData[$order_val['merchant_id']]['jet_two_month_order_count'])){
	    				$analyticsData[$order_val['merchant_id']]['jet_three_month_order_count']=$order_val['jet_three_month_order_count']-$analyticsData[$order_val['merchant_id']]['jet_two_month_order_count']-$analyticsData[$order_val['merchant_id']]['jet_last_month_order_count'];
	    			}
	    			else{
	    			
	    				$analyticsData[$order_val['merchant_id']]['jet_three_month_order_count']=$order_val['jet_three_month_order_count'];
	    				//var_dump($analyticsData[$order_val['merchant_id']]['jet_three_month_order_count']);die;
	    			}
	    		}
	    		else
	    			$analyticsData[$order_val['merchant_id']]['jet_three_month_order_count']=0;
	    	}
	   	}

    	foreach ($analyticsData as $key => $value) 
    	{
    		$row=[];
    		$row[]= $key;

    		if(isset($value['product_count']))
    			$row[]=$value['product_count'];
    		else
    			$row[]=0;

    		if(isset($value['order_count']))
    			$row[]=$value['order_count'];
    		else
    			$row[]=0;

    		if(isset($value['wal_plan_type']))
    			$row[]=$value['wal_plan_type'];
    		else
    			$row[]="Not Purchase";

    		if(isset($value['jet_plan_type']))
    			$row[]=$value['jet_plan_type'];
    		else
    			$row[]="Not Purchase";

    		if(isset($value['wal_last_month_order_count']))
    			$row[]=$value['wal_last_month_order_count'];
    		else
    			$row[]=0;
    		if(isset($value['wal_two_month_order_count']))
    			$row[]=$value['wal_two_month_order_count'];
    		else
    			$row[]=0;
    		if(isset($value['wal_three_month_order_count']))
    			$row[]=$value['wal_three_month_order_count'];
    		else
    			$row[]=0;

    		if(isset($value['jet_last_month_order_count']))
    			$row[]=$value['jet_last_month_order_count'];
    		else
    			$row[]=0;

    		if(isset($value['jet_two_month_order_count']))
    			$row[]=$value['jet_two_month_order_count'];
    		else
    			$row[]=0;

    		if(isset($value['jet_three_month_order_count']))
    			$row[]=$value['jet_three_month_order_count'];
    		else
    			$row[]=0;
    		fputcsv($file,$row);
    		
    	}
    	
    	fclose($file);
		$encode = "\xEF\xBB\xBF"; // UTF-8 BOM
		$content = $encode . file_get_contents($base_path);
		return \Yii::$app->response->sendFile($base_path);	
    }

    public function actionGetMerchantData1()
    {

    	$jetProductCollection= Data::sqlRecords("SELECT merchant_id,count(*) jet_product_count FROM ((SELECT `variant_id`,`merchant_id` FROM `jet_product`) UNION (SELECT `option_id` AS `variant_id`,`merchant_id` FROM `jet_product_variants`)) as `merged_data` GROUP BY merchant_id","all");
    	$walmartProductCollection= Data::sqlRecords("SELECT merchant_id,count(*) wal_product_count FROM ((SELECT `variant_id`,`walmart_product`.`merchant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id`) UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id`,`walmart_product_variants`.`merchant_id` FROM `walmart_product_variants` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id`)) as `merged_data` GROUP BY merchant_id","all");

    	$jetOrderCollection= Data::sqlRecords("select merchant_id,count(*) as jet_order_count from jet_order_detail group by merchant_id","all");
    	$walmartOrderCollection= Data::sqlRecords("select merchant_id,count(*) as wal_order_count,SUM(order_total) as wal_order_total from walmart_order_details group by merchant_id","all");
    	$walpaymentPlanCollection= Data::sqlRecords("select merchant_id,status as wal_plan_type from walmart_extension_detail","all");
    	$jetpaymentPlanCollection= Data::sqlRecords("select merchant_id,purchase_status as jet_plan_type from jet_shop_details","all");

    	$jetOrderTotalCollection= Data::sqlRecords("select merchant_id,shipment_data from jet_order_detail","all");
    	$analyticsData=[];
    	
    	$logPath = \Yii::getAlias('@webroot').'/var/';
		if (!file_exists($logPath)){
			mkdir($logPath,0775, true);
		}
		$base_path=$logPath.'/analytics-report.csv';
		$file = fopen($base_path,"w");
		fputcsv($file,['MERCHANT ID','JET PRODUCT COUNT','WALMART PRODUCT COUNT','JET ORDER COUNT','JET REVENUE','WALMART ORDER COUNT','WALMART REVENUE','PLAN STATUS(JET)','PLAN STATUS(WALMART)']);
		

    	foreach ($jetProductCollection as $key => $value) 
    	{
    		$analyticsData[$value['merchant_id']]['jet_products_count']=$value['jet_product_count'];
    	}
    	foreach ($walmartProductCollection as $key => $value) 
    	{
    		$analyticsData[$value['merchant_id']]['wal_products_count']=$value['wal_product_count'];
    	}
    	foreach ($jetOrderCollection as $order_value) 
    	{
    		if($order_value['jet_order_count']>1){

    			$analyticsData[$order_value['merchant_id']]['jet_orders_count']=$order_value['jet_order_count'];
    		}
    	}

    	foreach ($walmartOrderCollection as $order_val) 
    	{
    		if($order_val['wal_order_count']>1){
    			$analyticsData[$order_val['merchant_id']]['walmart_orders_count']=$order_val['wal_order_count'];
    		}
    		//walmart order total
    		if(isset($order_val['wal_order_total']))
    		{
    			if(isset($analyticsData[$order_val['merchant_id']]['wal_order_total']))
    				$analyticsData[$order_val['merchant_id']]['wal_order_total']+=$order_val['wal_order_total'];
    			else
    				$analyticsData[$order_val['merchant_id']]['wal_order_total']=$order_val['wal_order_total'];
    		}	
    	}

    	foreach ($walpaymentPlanCollection as $order_val) 
    	{
    		
    		$analyticsData[$order_val['merchant_id']]['wal_plan_type']=$order_val['wal_plan_type'];
    	}

    	foreach ($jetpaymentPlanCollection as $order_val) 
    	{
    		$analyticsData[$order_val['merchant_id']]['jet_plan_type']=$order_val['jet_plan_type'];
    	}

    	//jet order total
    	foreach ($jetOrderTotalCollection as $key => $value) 
    	{
    		if($value['merchant_id']==14)
    			continue;
    		$shipmentData=json_decode($value['shipment_data'],true);
    		if(isset($shipmentData['total_price']))
    		{
    			if(isset($analyticsData[$value['merchant_id']]['jet_order_total']))
    				$analyticsData[$value['merchant_id']]['jet_order_total']+=$shipmentData['total_price'];
    			else
    				$analyticsData[$value['merchant_id']]['jet_order_total']=$shipmentData['total_price'];
    		}
    	}

    	foreach ($analyticsData as $key => $value) 
    	{
    		$row=[];
    		$row[]= $key;

    		if(isset($value['jet_products_count']))
    			$row[]=$value['jet_products_count'];
    		else
    			$row[]=0;

    		if(isset($value['wal_products_count']))
    			$row[]=$value['wal_products_count'];
    		else
    			$row[]=0;

    		if(isset($value['jet_orders_count']))
    			$row[]=$value['jet_orders_count'];
    		else
    			$row[]=0;

    		if(isset($value['jet_order_total']))
    			$row[]=$value['jet_order_total'];
    		else
    			$row[]=0;

    		if(isset($value['walmart_orders_count']))
    			$row[]=$value['walmart_orders_count'];
    		else
    			$row[]=0;

    		if(isset($value['wal_order_total']))
    			$row[]=$value['wal_order_total'];
    		else
    			$row[]=0;

    		if(isset($value['jet_plan_type']))
    			$row[]=$value['jet_plan_type'];
    		else
    			$row[]="";

    		if(isset($value['wal_plan_type']))
    			$row[]=$value['wal_plan_type'];
    		else
    			$row[]="";

    		fputcsv($file,$row);
    		
    	}
    	
    	fclose($file);
		$encode = "\xEF\xBB\xBF"; // UTF-8 BOM
		$content = $encode . file_get_contents($base_path);
		return \Yii::$app->response->sendFile($base_path);	
    }

    public function actionGetWalmartMerchantData()
    {
    	
    	$analyticsData=[];
    	$logPath = \Yii::getAlias('@webroot').'/var/';
		if (!file_exists($logPath)){
			mkdir($logPath,0775, true);
		}
		$base_path=$logPath.'/analytics-report.csv';
		$file = fopen($base_path,"w");
		fputcsv($file,['MERCHANT ID','PRODUCT COUNT','ORDER COUNT','REVENUE','STATUS','IS INSTALL','PLAN STATUS','PAID TIME PERIOD']);
		$walmartClient= Data::sqlRecords("SELECT `walmart_extension_detail`.`merchant_id`,`walmart_extension_detail`.`status`,`app_status` FROM `walmart_extension_detail` LEFT JOIN `walmart_recurring_payment` ON `walmart_extension_detail`.`merchant_id`=`walmart_recurring_payment`.`merchant_id` group by `merchant_id`","all");
		//var_dump($walmartClient);die;
    	foreach ($walmartClient as $key => $value) 
    	{
    		if($value['merchant_id']==14)
    			continue;
    		$row=[];
    		$row[]=$value['merchant_id'];
    		// TOTAL PRODUCT COUNT
    		$merchant_id=$value['merchant_id'];
    		$walmartProductCollection= Data::sqlRecords("SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=" . $merchant_id . ")) as `merged_data`","one");
    		
    		if(isset($walmartProductCollection['count']))
    			$row[]= $walmartProductCollection['count'];	
    		else
    			$row[]= 0;	

    		// TOTAL ORDER COUNT & REVENUE
	    	$walmartOrderCollection= Data::sqlRecords("select merchant_id,count(*) as wal_order_count,SUM(order_total) as wal_order_total from walmart_order_details WHERE merchant_id=".$merchant_id,"one");
	    	
			if(isset($walmartOrderCollection['wal_order_count']))
    			$row[]= $walmartOrderCollection['wal_order_count'];	
    		else
    			$row[]= 0;	

    		if(isset($walmartOrderCollection['wal_order_total']))
    			$row[]= $walmartOrderCollection['wal_order_total'];	
    		else
    			$row[]= 0;	

    		$row[]= $value['status'];
    		$row[]= $value['app_status'];
    		$walmartPaymentDetails=Data::sqlRecords("SELECT activated_on,plan_type FROM `walmart_recurring_payment` WHERE merchant_id=".$merchant_id,"all");
    		if(is_array($walmartPaymentDetails) && count($walmartPaymentDetails)>0)
    		{
    			$date1='';
    			$month_used='';
    			if(count($walmartPaymentDetails)==1)
    			{
    				$row[]= $walmartPaymentDetails[0]['plan_type'];
    				$date1 = date('Y-m-d',strtotime($walmartPaymentDetails[0]['activated_on']));
    				$date2 = date('Y-m-d');
					$ts1 = strtotime($date1);
					$ts2 = strtotime($date2);
					$year1 = date('Y', $ts1);
					$year2 = date('Y', $ts2);
					$month1 = date('m', $ts1);
					$month2 = date('m', $ts2);
					$month_used = (($year2 - $year1) * 12) + ($month2 - $month1);
    			}
    			else
    			{
    				echo "<br>".$merchant_id;
    				$walmartPlan="";
	    			foreach ($walmartPaymentDetails as $val) 
	    			{
	    				$date = strtotime(date('Y-m-d',strtotime($val['activated_on'])));
	    				if (strpos($val['plan_type'], 'Year') !== false) {
	    					$date = strtotime('+'.preg_replace('/[^0-9]+/', '', $val['plan_type']).' year', $date);
	    				}else{
	    					$date = strtotime('+'.preg_replace('/[^0-9]+/', '', $val['plan_type']).' months', $date);
	    				}
	    				if($date<time())
	    				{
	    					if (strpos($val['plan_type'], 'Year') !== false) {
	    						$month_used+=12*preg_replace('/[^0-9]+/', '', $val['plan_type']);
	    					}
	    					else
	    					{
	    						$month_used+=preg_replace('/[^0-9]+/', '', $val['plan_type']);
	    					}
	    				}
	    				else
	    				{

							$ts1 = strtotime($date1);
							$ts2 = strtotime($date2);
							$year1 = date('Y', $ts1);
							$year2 = date('Y', $ts2);
							$month1 = date('m', $ts1);
							$month2 = date('m', $ts2);
							$month_used+= (($year2 - $year1) * 12) + ($month2 - $month1);
	    				}
	    				$walmartPlan.=str_replace("Subscription Plan"," ",$val['plan_type']);
	    			}

	    			$row[]= $walmartPlan;
	    			
    			}
    			if($month_used)
    			{
    				$row[]= $month_used;
    			}
    		}
    		//CALCULATE TOTAL NUMBER OF MONTH USED OUR PAID SERVICE
    		fputcsv($file,$row);
    		
    	}
	    	die;
    	fclose($file);
		$encode = "\xEF\xBB\xBF"; // UTF-8 BOM
		$content = $encode . file_get_contents($base_path);
		return \Yii::$app->response->sendFile($base_path);	
    }
    public function actionCheckreportdetails()
	{		
		$status = "";											
		$productStatusReportId = $this->jetHelper->CGetRequest('/reports/state/'.$_GET['report_id'],$this->merchant_id,$status);
		$productStatusReportIdDetails=json_decode($productStatusReportId,true);
		echo $status."<hr><pre>";
		print_r($productStatusReportIdDetails);
		die("<hr>Test");
	}

	public function actionDeleteUsers()
	{
		//affiliate
		$query="SELECT u.username,u.id,u.auth_key as jet_auth_key,n.token as newegg_auth_key,w.token as walmart_auth_key,s.token as sears_auth_key FROM `user` u LEFT JOIN `jet_shop_details` j ON u.id=j.merchant_id LEFT JOIN `walmart_shop_details` w ON u.id=w.merchant_id LEFT JOIN `newegg_shop_detail` n ON u.id=n.merchant_id LEFT JOIN `sears_shop_details` s ON u.id=s.merchant_id";
		$userData = Data::sqlRecords($query,"all");
		foreach ($userData as $key => $value) 
		{
			if($value['id']!=14)
			{
				$token="";
				$installed_on="";
				if($value['jet_auth_key']){
					$installed_on="jet";
					$token=$value['jet_auth_key'];
					$sc = new ShopifyClientHelper($value['username'],$value['jet_auth_key'],JET_APP_KEY,JET_APP_SECRET);
				}elseif($value['walmart_auth_key']){
					$installed_on="walmart";
					$token=$value['walmart_auth_key'];
					$sc = new ShopifyClientHelper($value['username'],$value['walmart_auth_key'],WALMART_APP_KEY,WALMART_APP_SECRET);
				}elseif($value['newegg_auth_key']){
					$installed_on="newegg";
					$token=$value['newegg_auth_key'];
					$sc = new ShopifyClientHelper($value['username'],$value['newegg_auth_key'],NEWEGG_APP_KEY,NEWEGG_APP_SECRET);
				}elseif($value['sears_auth_key']){
					$installed_on="sears";
					$token=$value['sears_auth_key'];
					$sc = new ShopifyClientHelper($value['username'],$value['sears_auth_key'],SEARS_APP_KEY,SEARS_APP_SECRET);
				}
			}
			if($sc)
			{
				$shopData=$sc->call('GET','/admin/shop.json');
				if(!isset($shopData['errors']) && ($shopData['plan_name']=='trial' || $shopData['plan_name']=='affiliate'))
				{
					$deletedUsers=Data::sqlRecords("SELECT id FROM `deleted_user_data` WHERE merchant_id=".$value['id'],"one");
					if(!isset($deletedUsers['id']))
					{
						Data::sqlRecords("INSERT INTO `deleted_user_data`(`merchant_id`, `email`, `shop_name`,`phone_number`,`installed_on`, `token`,`country`) VALUES ('".$value['id']."','".$shopData['email']."','".$value['username']."','".$shopData['phone']."','".$installed_on."','".$token."','".$shopData['country']."')");
					}
					echo $merchant_id.'-'.$value['username'];echo "<hr>";
					Data::sqlRecords("DELETE FROM `user` WHERE id=".$value['id']);
				}
			}
		}
	}

	public function actionDeleteTrialUninstallUsers()
	{
		$query="SELECT u.username,u.id,u.auth_key as jet_auth_key,n.token as newegg_auth_key,w.token as walmart_auth_key,COALESCE(j.email,w.email,n.email) as email,j.install_status as jet_install,j.purchase_status as jet_purchase_status,j.uninstall_date as jet_uninstall_date,j.expired_on as jet_expired_on,we.app_status as walmart_install,we.status as walmart_purchase_status,we.uninstall_date as walmart_uninstall_date,we.expire_date as walmart_expired_on,n.install_status as newegg_install,n.purchase_status as newegg_purchase_status,n.uninstall_date as newegg_uninstall_date,n.expire_date as newegg_expired_on FROM `user` u LEFT JOIN `jet_shop_details` j ON u.id=j.merchant_id LEFT JOIN `walmart_shop_details` w ON u.id=w.merchant_id LEFT JOIN `walmart_extension_detail` we ON u.id=we.merchant_id LEFT JOIN `newegg_shop_detail` n ON u.id=n.merchant_id";
		$userData = Data::sqlRecords($query,"all");
		$deletedUsers=[];
		$count=0;
		foreach ($userData as $key => $value) 
		{
			$jetdeleteTrialUser=false;
			$jetdeleteUninstallUser=false;
			$walmartdeleteTrialUser=false;
			$walmartdeleteUninstallUser=false;
			$neweggdeleteTrialUser=false;
			$neweggdeleteUninstallUser=false;
			if(!in_array($value['id'],[14,139,385,1136,1253,1418,1425,1452,1453,1474,1479]))
			{
				//trial expire clients
				if(!$value['jet_purchase_status'] || (($value['jet_purchase_status']=="License Expired" || $value['jet_purchase_status']=="Trial Expired") && strtotime($value['jet_expired_on']) < strtotime('-60 days')))
				{
					$jetdeleteTrialUser=true;
				}

				if(!$value['walmart_purchase_status'] || (($value['walmart_purchase_status']=="License Expired" || $value['walmart_purchase_status']=="Trial Expired") && strtotime($value['walmart_expired_on']) < strtotime('-60 days')))
				{
					$walmartdeleteTrialUser=true;
				}

				if(!$value['newegg_purchase_status'] || (($value['newegg_purchase_status']=="License Expired" || $value['newegg_purchase_status']=="Trail Expired") && strtotime($value['newegg_expired_on']) < strtotime('-60 days')))
				{
					$neweggdeleteTrialUser=true;
				}	

				///////////////////////////////////////////////////////////////////////////////
				//uninstalled clients
				if(!$value['jet_install'] || ($value['jet_install']=="0" && (strtotime($value['jet_uninstall_date']) < strtotime('-30 days') || !$value['jet_uninstall_date'])))
				{
					$jetdeleteUninstallUser=true;
				}	
				
				if(!$value['walmart_install'] || ($value['walmart_install']=="uninstall" && (strtotime($value['walmart_uninstall_date']) < strtotime('-30 days') || !$value['walmart_uninstall_date'])))
				{
					$walmartdeleteUninstallUser=true;
				}	
				
				if(!$value['newegg_install'] || ($value['newegg_install']=="0" && (strtotime($value['newegg_uninstall_date']) < strtotime('-30 days') || !$value['newegg_uninstall_date'])))
				{
					
					$neweggdeleteUninstallUser=true;
				}	
				
			}
			if(($jetdeleteUninstallUser && $walmartdeleteUninstallUser && $neweggdeleteUninstallUser) || ($jetdeleteTrialUser && $walmartdeleteTrialUser && $neweggdeleteTrialUser))
			{
				$deletedUsersData=Data::sqlRecords("SELECT id FROM `deleted_user_data` WHERE merchant_id=".$value['id'],"one");
				if(!isset($deletedUsersData['id']))
				{
					Data::sqlRecords("INSERT INTO `deleted_user_data`(`merchant_id`, `email`, `shop_name`,`installed_on`, `token`) VALUES ('".$value['id']."','".$value['email']."','".$value['username']."','".$installed_on."','".$token."')");
				}
				$deletedUsers[$value['id']]['jet_install']=$value['jet_install'];
				$deletedUsers[$value['id']]['jet_uninstall_date']=$value['jet_uninstall_date'];
				$deletedUsers[$value['id']]['jet_purchase_status']=$value['jet_purchase_status'];
				$deletedUsers[$value['id']]['jet_expired_on']=$value['jet_expired_on'];

				$deletedUsers[$value['id']]['walmart_install']=$value['walmart_install'];
				$deletedUsers[$value['id']]['walmart_uninstall_date']=$value['walmart_uninstall_date'];
				$deletedUsers[$value['id']]['walmart_purchase_status']=$value['walmart_purchase_status'];
				$deletedUsers[$value['id']]['walmart_expired_on']=$value['walmart_expired_on'];

				$deletedUsers[$value['id']]['newegg_install']=$value['newegg_install'];
				$deletedUsers[$value['id']]['newegg_uninstall_date']=$value['newegg_uninstall_date'];
				$deletedUsers[$value['id']]['newegg_purchase_status']=$value['newegg_purchase_status'];
				$deletedUsers[$value['id']]['newegg_expired_on']=$value['newegg_expired_on'];
				$count++;
				Data::sqlRecords("DELETE FROM `user` WHERE id=".$value['id']);
			}
		}
		echo "<pre>";print_r($deletedUsers);
		echo $count;
	}
	/* public function actionUpdateDeletedUserInfo()
	{
		$query="SELECT u.username,u.id,u.auth_key as jet_auth_key,n.token as newegg_auth_key,w.token as walmart_auth_key,COALESCE(j.email,w.email,n.email) as email,j.install_status as jet_install,j.purchase_status as jet_purchase_status,j.uninstall_date as jet_uninstall_date,j.expired_on as jet_expired_on,we.app_status as walmart_install,we.status as walmart_purchase_status,we.uninstall_date as walmart_uninstall_date,we.expire_date as walmart_expired_on,n.install_status as newegg_install,n.purchase_status as newegg_purchase_status,n.uninstall_date as newegg_uninstall_date,n.expire_date as newegg_expired_on FROM `user` u LEFT JOIN `jet_shop_details` j ON u.id=j.merchant_id LEFT JOIN `walmart_shop_details` w ON u.id=w.merchant_id LEFT JOIN `walmart_extension_detail` we ON u.id=we.merchant_id LEFT JOIN `newegg_shop_detail` n ON u.id=n.merchant_id";
		$userData = Data::sqlRecords($query,"all");
		foreach ($userData as $key => $value) 
		{
			$deletedUsersData=Data::sqlRecords("SELECT id FROM `deleted_user_data` WHERE merchant_id=".$value['id'],"one");
			if(!isset($deletedUsersData['id']))
			{
				Data::sqlRecords("INSERT INTO `deleted_user_data`(`merchant_id`, `email`, `shop_name`,`installed_on`, `token`) VALUES ('".$value['id']."','".$value['email']."','".$value['username']."','".$installed_on."','".$token."')");
			}
		}
	} */	

	public static function sqlRecords($query,$type=null,$queryType=null)
    {
        $connection = new yii\db\Connection([
		    'dsn' => 'mysql:host=localhost;dbname=cedcom5_shopify_bkp_1309',
		    'username' => 'cedcom5_sPy11F',
		    'password' => '-ZD,uo(M+N01',
		    'charset' => 'utf8',
		]);
        $response=[];
        if($queryType=="update" || $queryType=="delete" || $queryType=="insert" || ($queryType==null && $type==null))            
            $response = $connection->createCommand($query)->execute();    
        elseif($queryType=='column') 
            $response = $connection->createCommand($query)->queryColumn();        
        elseif($type=='one')
            $response=$connection->createCommand($query)->queryOne();        
        else
            $response=$connection->createCommand($query)->queryAll();        
        unset($connection);
        return $response;
    }

	public function actionUpdateDeletedUserInfo()
	{
		$query="SELECT u.username,u.id,u.auth_key as jet_auth_key,n.token as newegg_auth_key,w.token as walmart_auth_key,COALESCE(jr.email,wr.email,nr.email) as email,COALESCE(jr.mobile,wr.mobile,nr.mobile) as mobile FROM `user` u LEFT JOIN `walmart_shop_details` w ON u.id=w.merchant_id LEFT JOIN `newegg_shop_detail` n ON u.id=n.merchant_id LEFT JOIN `jet_registration` jr ON u.id=jr.merchant_id LEFT JOIN `walmart_registration` wr ON u.id=wr.merchant_id LEFT JOIN `newegg_registration` nr ON u.id=nr.merchant_id";
		$userData = self::sqlRecords($query,"all");
		//var_dump($userData);die;
		foreach ($userData as $key => $value) 
		{
			$deletedUsersData=self::sqlRecords("SELECT id FROM `deleted_user_data` WHERE merchant_id=".$value['id'],"one");
			if(isset($deletedUsersData['id']))
			{
				//Data::sqlRecords("INSERT INTO `deleted_user_data`(`merchant_id`, `email`, `shop_name`,`installed_on`, `token`) VALUES ('".$value['id']."','".$value['email']."','".$value['username']."','".$installed_on."','".$token."')");
				$token="";
				$installed_on="";
				if($value['jet_auth_key']){
					$installed_on.="jet,";
					$token=$value['jet_auth_key'];
					//$sc = new ShopifyClientHelper($value['username'],$value['jet_auth_key'],JET_APP_KEY,JET_APP_SECRET);
				}
				if($value['walmart_auth_key']){
					$installed_on.="walmart,";
					$token=$value['walmart_auth_key'];
					//$sc = new ShopifyClientHelper($value['username'],$value['walmart_auth_key'],WALMART_APP_KEY,WALMART_APP_SECRET);
				}
				if($value['newegg_auth_key']){
					$installed_on.="newegg,";
					$token=$value['newegg_auth_key'];
					//$sc = new ShopifyClientHelper($value['username'],$value['newegg_auth_key'],NEWEGG_APP_KEY,NEWEGG_APP_SECRET);
				}
				if($value['sears_auth_key']){
					$installed_on.="sears,";
					$token=$value['sears_auth_key'];
					//$sc = new ShopifyClientHelper($value['username'],$value['sears_auth_key'],SEARS_APP_KEY,SEARS_APP_SECRET);
				}
				echo $value['id'].'--'.$installed_on;
				$installed_on=rtrim($installed_on,',');
				self::sqlRecords("UPDATE `deleted_user_data` SET `installed_on`='".$installed_on."',`email`='".$value['email']."',phone_number='".$value['mobile']."' WHERE merchant_id=".$value['id']);
			}
		}
	}

	public function actionGetSimpleAttrValues()
	{
		$query="SELECT attr_ids,id,variant_id FROM 	`jet_product` WHERE merchant_id=".$this->merchant_id." and type='simple' and attr_ids!=''";
		$prodAttrData = Data::sqlRecords($query,"all");
		//var_dump($prodAttrData);die;
		if(is_array($prodAttrData) && count($prodAttrData)){
			foreach ($prodAttrData as $key => $value) {
				$attributeValue=[];
				$jetAttributes=[];
				$attrArray=json_decode(stripslashes($value['attr_ids']),true);
				if(!in_array('Title',$attrArray))
				{
					$shopifyProdData=$this->sc->call("GET","/admin/products/".$value['id'].".json",['fields'=>'options']);
					if(!isset($shopifyProdData['errors']) && isset($shopifyProdData['options'])){
						foreach ($shopifyProdData['options'] as $val) 
						{
							if(in_array($val['name'], $attrArray))
							{
								$attributeValue[$val['name']]=$val['values'][0];
								if(strcasecmp($val['name'], 'size') == 0){
									$jetAttributes[50][0]=$val['values'][0];
								}elseif(strcasecmp($val['name'], 'color') == 0){
									$jetAttributes[119][0]=$val['values'][0];
								}
							}
						}
					}
				}
				if(is_array($attributeValue) && count($attributeValue)){
					//save records in jet_product_details
					$query="UPDATE `jet_product_details` SET `attribute_values`='".addslashes(json_encode($attributeValue))."' WHERE product_id=".$value['id'];
					Data::sqlRecords($query);
				}
				if(is_array($jetAttributes) && count($jetAttributes)){
					//save records in jet_product_details
					$query="UPDATE `jet_product` SET `jet_attributes`='".addslashes(json_encode($jetAttributes))."' WHERE id=".$value['id'];
					Data::sqlRecords($query);
				}
			}
		}
	}	
}
