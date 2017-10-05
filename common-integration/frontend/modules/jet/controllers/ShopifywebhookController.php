<?php
namespace frontend\modules\jet\controllers;
use common\models\User;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Sendmail;
use frontend\modules\jet\models\JetConfig;
use frontend\modules\jet\models\JetConfiguration;
use frontend\modules\jet\models\JetOrderDetail;
use frontend\modules\jet\models\JetOrderImportError;
use Yii;
use yii\web\Controller;

class ShopifywebhookController extends Controller
{
	public function beforeAction($action)
	{
		if ($this->action->id == 'productcreate') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'productupdate') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'productdelete') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'ship') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'createshipment') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'isinstall') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'cancelled') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'ordership') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		if ($this->action->id == 'createorder') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		return true;
	}

	public function actionProductcreate()
	{
		$shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
		try{
			$webhook_content = '';
			$webhook = fopen('php://input' , 'rb');
			while(!feof($webhook)){
				$webhook_content .= fread($webhook, 4096);
			}
			$realdata="";
			$data=array();
			fclose($webhook);
			$realdata=$webhook_content;
			if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
				return;
			}
			$data = json_decode($realdata,true);// array of webhook data
			
			if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
			   $data['shopName']=$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
			$url = Yii::getAlias('@webjeturl')."/shopifywebhook/curlproductcreate";
			//var_dump(http_build_query( $data ));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_TIMEOUT,1);
			$result = curl_exec($ch);
			curl_close($ch);
			exit(0);
		}
		catch(Exception $e){
			$this->createExceptionLog('actionProductcreate',$e->getMessage(),$shopName);
			exit(0);
		}
    }
    public function actionTestproduct(){
    	$data='{"id":"8874517772","title":"New shirt blue","body_html":"New shirt blue","vendor":"Fashion Apparel ","product_type":"tshirt","created_at":"2016-11-09T07:49:30-05:00","handle":"new-shirt-blue","updated_at":"2016-11-09T07:49:30-05:00","published_at":"2016-11-09T07:43:00-05:00","published_scope":"global","tags":"","variants":[{"id":"30642204172","product_id":"8874517772","title":"l","price":"20.00","sku":"","position":"1","grams":"0","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"l","created_at":"2016-11-09T07:49:30-05:00","updated_at":"2016-11-09T07:49:30-05:00","taxable":"0","barcode":"435656565656","inventory_quantity":"20","weight":"0","weight_unit":"kg","old_inventory_quantity":"20","requires_shipping":"1"},{"id":"30642204236","product_id":"8874517772","title":"s","price":"20.00","sku":"new","position":"2","grams":"0","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"s","created_at":"2016-11-09T07:49:30-05:00","updated_at":"2016-11-09T07:49:30-05:00","taxable":"0","barcode":"435656565656","inventory_quantity":"20","weight":"0","weight_unit":"kg","old_inventory_quantity":"20","requires_shipping":"1"},{"id":"30642204300","product_id":"8874517772","title":"m","price":"20.00","sku":"check321","position":"3","grams":"0","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"m","created_at":"2016-11-09T07:49:30-05:00","updated_at":"2016-11-09T07:49:30-05:00","taxable":"0","barcode":"435656565656","inventory_quantity":"20","weight":"0","weight_unit":"kg","old_inventory_quantity":"20","requires_shipping":"1"}],"options":[{"id":"10712096012","product_id":"8874517772","name":"Size","position":"1","values":["l","s","m"]}],"shopName":"ced-jet.myshopify.com"}';
    	var_dump(Jetproductinfo::saveNewRecords(json_decode($data,true),14));
    }
	public function actionCurlproductcreate()
	{
		$data = $_POST;
		if(isset($data['shopName']) && isset($data['id']))
		{
			try
			{
				$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/create/'.$data['shopName'];
			    if (!file_exists($file_dir)) {
			        mkdir($file_dir, 0775, true);
			    }
			    $filenameOrig="";
			    $filenameOrig=$file_dir.'/'.$data['id'].'.log';
			    $fileOrig="";
			    $fileOrig=fopen($filenameOrig,'w');
			    fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($data));
			    fclose($fileOrig);
				$query="SELECT `id,merchant_id` FROM `jet_product` WHERE id='".$data['id']."' LIMIT 0,1";
				$proresult = Data::sqlRecords($query);
				if(!$proresult)
				{	
					$customData = JetProductInfo::getConfigSettings($merchant_id);
	    			$import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";					
					Jetproductinfo::saveNewRecords($data,$merchant_id,$import_status);
				}
				unset($data);unset($query);
			}
			catch(\yii\db\Exception $e){
				$this->createExceptionLog('actionCurlproductcreate',$e->getMessage(),$data['shopName']);
				exit(0);
			}
			catch(Exception $e){
				$this->createExceptionLog('actionCurlproductcreate',$e->getMessage(),$data['shopName']);
				exit(0);
			}
		}
	}

	public function actionCheckLog(){
		$this->createExceptionLog('test','messagess');
		die('done');
	}
	/* 
	 * function for creating log 
	 */
	public function createExceptionLog($functionName,$msg,$shopName = 'common'){
		$dir = \Yii::getAlias('@webroot').'/var/jet/exceptions/'.$functionName.'/'.$shopName;
		if (!file_exists($dir)){
	        mkdir($dir,0775, true);
	    }
	    $filenameOrig = $dir.'/'.time().'.txt';
	    $handle = fopen($filenameOrig,'a');
	    $msg = date('d-m-Y H:i:s')."\n".$msg;
	    fwrite($handle,$msg);
	    fclose($handle);
	    $this->sendEmail($filenameOrig,$msg);
	}

	/**
	 * function for sending mail with attachment
	 */
	public function sendEmail($file,$msg,$email = 'satyaprakash@cedcoss.com')
	{
	   try{
			$name = 'Jet Shopify Cedcommerce';
        
		 	$EmailTo = $email.',amitkumar@cedcoss.com,kshitijverma@cedcoss.com';
			$EmailFrom = $email;
		 	$EmailSubject = "Jet Shopify Exception Log" ;
		  	$from ='Jet Shopify Cedcommerce';
		  	$message = $msg;
		  	$separator = md5(time());

		  	// carriage return type (we use a PHP end of line constant)
		  	$eol = PHP_EOL;

		  	// attachment name
		  	$filename = 'exception';//store that zip file in ur root directory
		  	$attachment = chunk_split(base64_encode(file_get_contents($file)));

		  	// main header
		  	$headers  = "From: ".$from.$eol;
		  	$headers .= "MIME-Version: 1.0".$eol; 
		  	$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

		  	// no more headers after this, we start the body! //

		  	$body = "--".$separator.$eol;
		  	$body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol.$eol;
		  	$body .= $message.$eol;

		  	// message
		  	$body .= "--".$separator.$eol;
		 	/*  $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
		  	$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
		  	$body .= $message.$eol; */

		  	// attachment
		  	$body .= "--".$separator.$eol;
		  	$body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol; 
		  	$body .= "Content-Transfer-Encoding: base64".$eol;
		  	$body .= "Content-Disposition: attachment".$eol.$eol;
		  	$body .= $attachment.$eol;
		  	$body .= "--".$separator."--";

		  	// send message
		    if (mail($EmailTo, $EmailSubject, $body, $headers)) {
		 	 	$mail_sent = true;
			} else {
			  	$mail_sent = false;
			}
        }
        catch(Exception $e)
        {
            
        }
	}
	public function actionCreateshipment()
	{
		$shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
		try
		{
			$webhook_content = '';
			$webhook = fopen('php://input' , 'rb');
			while(!feof($webhook)){
				$webhook_content .= fread($webhook, 4096);
			}
			$realdata="";
			$data = $orderData = array();
			fclose($webhook);
			$realdata=$webhook_content;
			if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
				return;
			}
			$data = json_decode($realdata,true);

			if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
			  $data['shopName']=$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
			if(isset($data['note']) && $data['note']=="Walmart Marketplace-Integration")
			{
				$url = Yii::getAlias('@webwalmarturl')."/webhookupdate/createshipment";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($ch, CURLOPT_TIMEOUT,1);
				$result = curl_exec($ch);
				curl_close($ch);
				exit(0);
			}
			else
			{
				$url = Yii::getAlias('@webjeturl')."/shopifywebhook/curlprocessfororder";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($ch, CURLOPT_TIMEOUT,1);
				$result = curl_exec($ch);
				curl_close($ch);
				exit(0);
			}  
			
		}
		catch(Exception $e){
			$this->createExceptionLog('actionProductcreate',$e->getMessage(),$shopName);
			exit(0);
		}
	}

	public function actionCurlprocessfororder()
	{		
		$data = $_POST;
		$flag=false;
			
		if($data && isset($data['id']))
		{
			try
			{
				$orderData = array();
				$query="SELECT jet_order_detail.merchant_id,order_data,status,api_user,api_password FROM `jet_order_detail` INNER JOIN `jet_configuration` ON jet_order_detail.merchant_id=jet_configuration.merchant_id WHERE shopify_order_id='".$data['id']."' and (status='acknowledged' or status='inprogress') LIMIT 0,1";
				$orderData = Data::sqlRecords($query,'one','select');
				if(isset($orderData['merchant_id'],$orderData['api_user']))
				{
					$jetClientDatails = $jetHelper = $jetOrderdata = $resultConfig = $addressDetails = array();
					$merchant_id = $merchant_order_id = $reference_order_id = $api_provider = $id = $token = $shopname = $api_user = $api_password = $email = $sc = "";

					$flagCarr=false;
					$orderStatus="";
					$merchant_id=$orderData['merchant_id'];
					//$orderData->shipment_data=json_encode($data);									
					//$id=$orderData->id;
					$jetOrderdata = json_decode($orderData['order_data'],true);
					$merchant_order_id = $jetOrderdata['merchant_order_id'];
					$reference_order_id = $jetOrderdata['reference_order_id'];
					
					$file_dir = \Yii::getAlias('@webroot').'/var/jet/order/'.$merchant_id.'/'.$reference_order_id;
				    if (!file_exists($file_dir)){
				        mkdir($file_dir,0775, true);
				    }
				    $filename1=$file_dir.'/shipment.log';
					$file1=fopen($filename1,'a+');
					fwrite($file1, PHP_EOL."ORDER SHIPMENT DETAILS =>".PHP_EOL.json_encode($data).PHP_EOL);
					
					$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$orderData['api_user'],$orderData['api_password']);
					$resultConfig = Jetappdetails::getJetConfigDetails($merchant_id);	
					if(!empty($resultConfig) && count($resultConfig)>0)
		            {
		                foreach($resultConfig as $v)
		                {
		                    $addressDetails[$v['data']] = $v['value'];                    
		                }
		            }
		            if($addressDetails['first_address'] =='' || $addressDetails['city'] =='' || $addressDetails['state'] =='' || $addressDetails['zipcode']=='')
		            {
		            	$errorMessage="Kidnly fill the return address details";
						fwrite($file1, $errorMessage);
						fclose($file1);
						return;	                	                
		            }
		            if(isset($addressDetails['day_to_return']) && $addressDetails['day_to_return'])
		            {
		            	$days_to_return = $addressDetails['day_to_return'];
		            }
		            else
		            {
		            	$days_to_return = 30;
		            }
		            $array_return = array(
		            	'address1'=>$addressDetails['first_address'],
	                    'address2'=>isset($addressDetails['second_address'])? $addressDetails['second_address'] : "",
	                    'city'=>$addressDetails['city'],
	                    'state'=>$addressDetails['state'],
	                    'zip_code'=>$addressDetails['zipcode']
		            );						
					$data['timestamp']=date("d-m-Y H:i:s");						

					$offset_end = $expected_delivery_date = $tracking_number = "";
					$offset_end = $this->getStandardOffsetUTC();

					if(empty($offset_end) || trim($offset_end)=='')
						$offset = '.0000000-00:00';
					else
						$offset = '.0000000'.trim($offset_end);
											
					$expected_delivery_date = date("Y-m-d\TH:i:s").$offset;
					fwrite($file1, PHP_EOL."expected_delivery_date => ".$expected_delivery_date.PHP_EOL);
					
					$carriers=[
                        'FedEx','FedEx SmartPost','FedEx Freight','Fedex Ground','UPS','UPS Freight','UPS Mail Innovations','UPS SurePost','OnTrac','OnTrac Direct Post','DHL','DHL Global Mail','USPS','CEVA','Laser Ship','Spee Dee','A Duie Pyle','A1','ABF','APEX','Averitt','Dynamex','Eastern Connection','Ensenda','Estes','Land Air Express','Lone Star','Meyer','New Penn','Pilot','Prestige','RBF','Reddaway','RL Carriers','Roadrunner','Southeastern Freight','UDS','UES','YRC','GSO','A&M Trucking','SAIA Freight','Other'
                    ];
                    $response = $shipment_arr = array();
					$response=$data['fulfillments'];
					foreach ($response as $key23 => $value23) 
					{	
						fwrite($file1, PHP_EOL."Inside fulfilment foreach loop".PHP_EOL);		
						$data_ship = array();	
						$tracking_number = $shipment_id = $request_shipping_carrier = "";				
						$shipment_id = $value23['id'];
						if(isset($value23['tracking_number'],$value23['tracking_company']) )
						{								
							$tracking_number = preg_replace('/\s+/', '', $value23['tracking_number']); 
							$request_shipping_carrier = $value23['tracking_company'];
							fwrite($file1, PHP_EOL."tracking_number and request_shipping_carrier".PHP_EOL.$tracking_number."<=>".$request_shipping_carrier.PHP_EOL);		
							if(!in_array($request_shipping_carrier, $carriers))								
								$request_shipping_carrier="Other";								
						}
						else
						{
							fwrite($file1, PHP_EOL."Missing Tracking Information".PHP_EOL);
							fclose($file1);
							return;
						}
						if (isset($value23['line_items'])) 
						{	
							foreach ($value23['line_items'] as $ke21 => $va21) 
							{
								$sku = "";									
								$matchResponse = self::matchShipmentSku($va21,$jetOrderdata['order_items']);
								fwrite($file1, PHP_EOL." SKU MATCH RESPONSE ".$matchResponse.PHP_EOL);

								if ($matchResponse=='ced') 
								{
									$sku = $va21['sku'];
								}
								else
								{
									$sku = $va21['product_id'].'-'.$va21['variant_id'];
								}																	
								$shipment_arr[]= array(
									'merchant_sku'=>$sku,
									'response_shipment_sku_quantity'=>(int)$va21['quantity'],
									'days_to_return'=>(int)$days_to_return,
									'return_location'=>$array_return
								);						
								fwrite($file1, PHP_EOL." shipment_arr ".json_encode($shipment_arr).PHP_EOL);	
							}								
						}											
						$data_ship['shipments'][]=array	(
							'shipment_tracking_number'=>$tracking_number,
							'response_shipment_date'=>$expected_delivery_date,
							'response_shipment_method'=>"",
							'expected_delivery_date'=>$expected_delivery_date,
							'ship_from_zip_code'=>(string)$addressDetails['zipcode'],
							'carrier_pick_up_date'=>$expected_delivery_date,
							'carrier'=>$request_shipping_carrier,
							'shipment_items'=>$shipment_arr
						);		
						if(!empty($data_ship))
						{
							fwrite($file1,PHP_EOL."shipment data send on jet.com".PHP_EOL.json_encode($data_ship).PHP_EOL);
							$status=true;	
							$jetdata=$jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($data_ship),$merchant_id,$status);
							$responseArray=array();
							$responseArray=json_decode($jetdata,true);
							if($status==204)
							{
								fwrite($file1, PHP_EOL."ORDER SUCCESSFULLY SHIPPED ON JET".PHP_EOL);	
								$resultResponse = array();
                                $resultResponse = $jetHelper->CGetRequest('/orders/withoutShipmentDetail/' . $merchant_order_id,$merchant_id);
                                $resultObject = json_decode($resultResponse, true);
								if(isset($resultObject['status']))
								{
									fwrite($file1, PHP_EOL."ORDER shipped status: ".$resultObject['status'].PHP_EOL);	
                                	$orderStatus=$resultObject['status'];						
								}                      
							}
							else
							{
								fwrite($file1, "ORDER NOT SHIPPED ON JET (ERROR)=>".$jetdata.PHP_EOL);
								//$orderData->save(false);
								$ordererrorMdoel="";
								$ordererrorMdoel=new JetOrderImportError();
								$ordererrorMdoel->merchant_order_id=$merchant_order_id;
								$ordererrorMdoel->reference_order_id=$reference_order_id;
								$ordererrorMdoel->reason="Order Not fulfilled on jet.\nError:".json_encode($responseArray['errors']);
								$ordererrorMdoel->created_at=date("d-m-Y H:i:s");
								$ordererrorMdoel->merchant_id=$merchant_id;
								$ordererrorMdoel->save(false);
							}
						}
					}
					//save order information
					if($orderStatus)
					{
						$query="UPDATE `jet_order_detail` SET status='".$orderStatus."',shipment_data='".json_encode($data)."',shipped_at='".date('Y-m-d H:i:s')."' WHERE reference_order_id='".$reference_order_id."'";
					}
					else
					{
						$query="UPDATE `jet_order_detail` SET shipment_data='".json_encode($data)."',shipped_at='".date('Y-m-d H:i:s')."' WHERE reference_order_id='".$reference_order_id."'";
					}
					fclose($file1);
					return;
				}
				else{
					return;
				}
			}
			catch (ShopifyApiException $e)
			{
				$errorMessage=$shopname."[".date('d-m-Y H:i:s')."]\n"."Error in shopify api".$e->getMessage()."\n";
				//fwrite($file1, $errorMessage);
				//fclose($file1);
				$this->createExceptionLog('actionCurlprocessfororder',$e->getMessage(),$shopname);
				return;
			}
			catch (ShopifyCurlException $e)
			{
				$errorMessage=$shopname."[".date('d-m-Y H:i:s')."]\n"."Error in shopify curl api".$e->getMessage()."\n";
				//fwrite($file1, $errorMessage);
				//fclose($file1);
				$this->createExceptionLog('actionCurlprocessfororder',$e->getMessage(),$shopname);
				return;
			}
		}else{
			return;
		}
	}

	public function getShipementStatus($items){
		$items = isset($items[0])?$items:[$items];
		foreach($items as $item){
			if($item['fulfillment_status']!='fulfilled'){
				return 'inprogress';
			}
		}
		return 'complete';
	}
	public function getStandardOffsetUTC()
	{
		$timezone="";
		$timezone = date_default_timezone_get();
		if($timezone == 'UTC') {
			return '';
		} else {
			$timezone = new \DateTimeZone($timezone);
			$transitions="";
			$transitions = array_slice($timezone->getTransitions(), -3, null, true);
	
			foreach (array_reverse($transitions, true) as $transition)
			{
				if (isset($transition['isdst']) && $transition['isdst'] == 1)
				{
					continue;
				}
				return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
			}
			return false;
		}
	}
	public function actionProductupdate()
	{		
		$shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
		try{
			$webhook = fopen('php://input' , 'rb');
			$webhook_content = '';
			while(!feof($webhook)){
				$webhook_content .= fread($webhook, 4096);
			}
			$realdata="";
			$data=array();
			$orderData=array();
			fclose($webhook);
			$realdata=$webhook_content;
			if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
				return;
			}
			$data = json_decode($realdata,true);// ab ye data ka array hai
			
			if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
			    $data['shopName']=$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
			//$this->emailforproductupdate($data);
			$url = Yii::getAlias('@webjeturl')."/shopifywebhook/curlprocessforproductupdate";
			//var_dump(http_build_query( $data ));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_TIMEOUT,1);
			$result = curl_exec($ch);
			curl_close($ch);
	        exit(0);
	    }
	    catch (Exception $e)
		{
			$this->createExceptionLog('actionProductupdate',$e->getMessage(),$shopName);
			return;
		}

	}

	
	public function actionCurlprocessforproductupdate()
	{
		$data = json_decode(json_encode($_POST), true);
		if($data && isset($data['id']) && isset($data['shopName']))
		{
			try
			{
				$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/update/'.$data['shopName'];
			    if (!is_dir($file_dir)){
			        mkdir($file_dir,0775, true);
			    }
			    $filenameOrig="";
			    $filenameOrig=$file_dir.'/'.$data['id'].'.log';
			    $fileOrig="";
			    $fileOrig=fopen($filenameOrig,'w+');
			    $handle = fopen($file_dir.'/'.$data['id'].'-qty-price.log', 'w+');
			    fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($data));
			    fclose($fileOrig); 
			    
			    $merchant_id="";
			    
			    $connection = Yii::$app->getDb();
			    $prodExist = $connection->createCommand("SELECT `sku`,`merchant_id`,`status` FROM `jet_product` WHERE id='".$data['id']."' LIMIT 0,1")->queryOne();
			    //get merchant_id

			    $query="SELECT id FROM `user` WHERE username='".$data['shopName']."' LIMIT 0,1";

			    $userCollection=Data::sqlRecords($query,"one","select");

			    if (is_array($userCollection) && count($userCollection)>0){
			    	$merchant_id = $userCollection['id'];
			    } 
			    else {

			    	return false;
			    }

			    if (!$prodExist)
			    {	
			    	$customData = JetProductInfo::getConfigSettings($merchant_id);
	    			$import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";	
			    	Jetproductinfo::saveNewRecords($data,$merchant_id,$import_status);
			    	return false;
			    }
		
			    $jetConfig=array();$jetHelper='';$jetHelperFlag=false;
			    $jetConfig=$connection->createCommand('SELECT `fullfilment_node_id`,`api_user`,`api_password` from `jet_configuration` where merchant_id="'.$merchant_id.'"')->queryOne();
			    if($jetConfig)
			    {
			    	$jetHelperFlag = true;
			    	$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
			    	$api_host="https://merchant-api.jet.com/api";
			    	$api_user=$jetConfig['api_user'];
			    	$api_password=$jetConfig['api_password'];
			    	$jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
			    	$responseToken ="";
			    	$responseToken = $jetHelper->JrequestTokenCurl();
			    	if($responseToken==false){
			    		$jetHelperFlag = false;
			    		//return "Api Details are incorrect";
			    	}
			    }
			    $productOnJet=false;
			    if ($prodExist['status']!='Not Uploaded' && $jetHelperFlag) 
	    		{
	    			$productOnJet=true;
	   			}
			    // Inventory Sync (Start)
			    if($data)
			    {
			    	fwrite($handle,PHP_EOL.date('d-m-Y H:i:s').": Inventory-Price Update In".PHP_EOL);
			    	$configSetting = Jetproductinfo::getConfigSettings($merchant_id, $connection);

			    	$variants = $data['variants'];
			    	
			    	$product_id=$variants[0]['product_id'];
		    		$product_sku=$variants[0]['sku'];
		    		$product_qty=$variants[0]['inventory_quantity'];
		    		$product_price = $variants[0]['price'];
		    		if(count($variants)>1)
		    		{
		    			fwrite($handle,PHP_EOL."VARIANT PRODUCT ".PHP_EOL);
		    			foreach($variants as $value1)
		    			{
		    				$product_id=$value1['product_id'];
		    				$option_id=$value1['id'];
		    				$option_sku=$value1['sku'];
		    				$option_qty=$value1['inventory_quantity'];
		    				$option_price = $value1['price'];
		    				
		    				/*$result=array();$optionmodel=array();
		    				$optionmodel = $connection->createCommand("SELECT `option_id`,`product_id`,`option_sku`  FROM `jet_product_variants` WHERE option_id='".$option_id."' ");
		    				$result = $optionmodel->queryOne();
		    				if(($result) && ($option_sku !=''))
		    				{*/

		    				if(!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) 
		    				{ 
		    					fwrite($handle,"variant sku: ".$option_sku." --- inventory: ".$option_qty." --- Price: ".$option_price.PHP_EOL);
		    					$sql = "UPDATE `jet_product_variants` SET `option_qty`='".$option_qty."', `option_price`=".$option_price." WHERE `option_id`='".$option_id."' AND `merchant_id`='".$merchant_id."'";
		    					
		    				}
		    				else
		    				{
		    					fwrite($handle,"variant sku: ".$option_sku." --- inventory: ".$option_qty." --- Price: No Change".PHP_EOL);
		    					$sql = "UPDATE `jet_product_variants` SET `option_qty`='".$option_qty."' WHERE `option_id`='".$option_id."' AND `merchant_id`='".$merchant_id."'";
		
		    				}

		    				$model = $connection->createCommand($sql)->execute();
	    					/*$get_sql = "SELECT `id`,`sku` FROM `jet_product`  WHERE `id`='".$result['product_id']."' AND `merchant_id`='".$merchant_id."' AND `status`!='Not Uploaded' ";
	    					$model_prod = $connection->createCommand($get_sql)->queryOne();*/

	    					if ($productOnJet) 
	    					{
	    						fwrite($handle,"update variant inventory on jet ".PHP_EOL."variant sku: ".$option_sku." --- inventory: ".$option_qty.PHP_EOL);
	    						Jetproductinfo::updateQtyOnJet($option_sku,$option_qty,$jetHelper,$fullfillmentnodeid,$merchant_id);
	    					}
	    					unset($sql);unset($model);

	    					if((!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) && $productOnJet) 
	    					{
								$price = Jetproductinfo::getPriceToBeUpdatedOnJet($merchant_id, $option_price, $configSetting, $connection);
								fwrite($handle,"update variant custom price on jet ".PHP_EOL."variant sku: ".$option_sku." --- price: ".$price.PHP_EOL);
								Jetproductinfo::updatePriceOnJet($option_sku,$price,$jetHelper,$fullfillmentnodeid,$merchant_id);
							}

	    					//set curl inventory request to walmart
	    					$url = Yii::getAlias('@webwalmarturl')."/webhookupdate/productupdate";
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL,$url);
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['inventory'=>$option_qty,'sku'=>$option_sku,'type'=>'inventory','merchant_id'=>$merchant_id]));
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
							curl_setopt($ch, CURLOPT_TIMEOUT,1);
							$result = curl_exec($ch);
							$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
							if($http_code==200)
								fwrite($handle,"variant inventory send on walmart app ".PHP_EOL);
							else
								fwrite($handle,"variant inventory data unable to send on walmart app ".PHP_EOL);
							curl_close($ch);


							//set curl price request to walmart
	    					$wal_price_update_url = Yii::getAlias('@webwalmarturl')."/webhookupdate/productupdate";
							$curl = curl_init();
							curl_setopt($curl, CURLOPT_URL,$wal_price_update_url);
							curl_setopt($curl, CURLOPT_POST, 1);
							curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['price'=>$option_price,'sku'=>$option_sku,'type'=>'price','merchant_id'=>$merchant_id,'id'=>$option_id,'type'=>'variants']));
							curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
							curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
							curl_setopt($curl, CURLOPT_TIMEOUT,1);
							$result = curl_exec($curl);
							if($http_code==200)
								fwrite($handle,"variant price send on walmart app ".PHP_EOL);
							else
								fwrite($handle,"variant price data unable to send on walmart app ".PHP_EOL);
							curl_close($curl);
	    				//}
		    			}
		    		}
			    		
		    		/*$result="";$productmodel="";
		    		$productmodel = $connection->createCommand("SELECT `id`,`sku` FROM `jet_product` WHERE id='".$product_id."' ");
		    		$result = $productmodel->queryOne();*/
		    		if(is_array($prodExist) && count($prodExist)>0 && $product_sku !='')
		    		{	
		    			fwrite($handle,PHP_EOL."SIMPLE PRODUCT ".PHP_EOL);
		    			if(!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) 
		    			{	
		    				fwrite($handle,"sku: ".$product_sku." --- inventory: ".$product_qty." --- Price: ".$product_price.PHP_EOL);
		    				$sql = "UPDATE `jet_product` SET `qty`='".$product_qty."', `price`=".$product_price." WHERE `id`='".$product_id."' AND `merchant_id`='".$merchant_id."'";
		    			}else{
		    				fwrite($handle,"sku: ".$product_sku." --- inventory: ".$product_qty." --- Price: No Change".PHP_EOL);
		    				$sql = "UPDATE `jet_product` SET `qty`='".$product_qty."' WHERE `id`='".$product_id."' AND `merchant_id`='".$merchant_id."'";
		    			}	
		    			$model = $connection->createCommand($sql)->execute();
		    			/*$get_sql = "SELECT `id`,`sku` FROM `jet_product`  WHERE `id`='".$result['id']."' AND `merchant_id`='".$merchant_id."' AND `status`!='Not Uploaded' ";
		    			$model_prod = $connection->createCommand($get_sql)->queryOne();*/
		    			if ($productOnJet) {
		    				fwrite($handle,"update simple inventory on jet ".PHP_EOL." sku: ".$prodExist['sku']." --- inventory: ".$product_qty.PHP_EOL);
		    				Jetproductinfo::updateQtyOnJet($prodExist['sku'],$product_qty,$jetHelper,$fullfillmentnodeid,$merchant_id);
		    			}
		    			unset($sql);unset($model);

		    			if((!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) && $productOnJet) 
		    			{
							$price = Jetproductinfo::getPriceToBeUpdatedOnJet($merchant_id, $product_price, $configSetting, $connection);
							fwrite($handle,"update simple custom price on jet ".PHP_EOL." sku: ".$prodExist['sku']." --- price: ".$price.PHP_EOL);
							Jetproductinfo::updatePriceOnJet($prodExist['sku'],$price,$jetHelper,$fullfillmentnodeid,$merchant_id);
						}
						//set curl inventory request to walmart
    					$url = Yii::getAlias('@webwalmarturl')."/webhookupdate/productupdate";
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$url);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['inventory'=>$product_qty,'sku'=>$prodExist['sku'],'type'=>'inventory','merchant_id'=>$merchant_id]));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
						curl_setopt($ch, CURLOPT_TIMEOUT,1);
						$result = curl_exec($ch);
						$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						if($http_code==200)
							fwrite($handle,"simple inventory send on walmart app ".PHP_EOL);
						else
							fwrite($handle,"inventory data unable to send on walmart app ".PHP_EOL);
						curl_close($ch);


						//set curl price request to walmart
    					$wal_price_update_url = Yii::getAlias('@webwalmarturl')."/webhookupdate/productupdate";
						$curl = curl_init();
						curl_setopt($curl, CURLOPT_URL,$wal_price_update_url);
						curl_setopt($curl, CURLOPT_POST, 1);
						curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['price'=>$product_price,'sku'=>$prodExist['sku'],'type'=>'price','merchant_id'=>$merchant_id,'id'=>$product_id,'type'=>'simple']));
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
						curl_setopt($curl, CURLOPT_TIMEOUT,1);
						$result = curl_exec($curl);
						if($http_code==200)
							fwrite($handle,"simple price send on walmart app ".PHP_EOL);
						else
							fwrite($handle,"price data unable to send on walmart app ".PHP_EOL);
						curl_close($curl);
		    		}			    	
			    }
			    // Inventory Sync (end)

			    $errorstr="";
				
				$checkExistPro=array();
				$curDate=date('Y-m-d H:i:s');
				$checkExistPro=$connection->createCommand('SELECT `product_id` from `jet_product_tmp` where `product_id`="'.$data['id'].'" LIMIT 0,1')->queryOne();
				if(is_array($checkExistPro) && count($checkExistPro)>0){
					fwrite($handle,PHP_EOL."Product Data updated in jet_product_tmp".PHP_EOL);
					$connection->createCommand('UPDATE `jet_product_tmp` SET `data`="'.addslashes(json_encode($data)).'",`created_at`="'.$curDate.'" where `product_id`="'.$data['id'].'"')->execute();
				}
				else{
					fwrite($handle,PHP_EOL."Product Data insert in jet_product_tmp".PHP_EOL);
					$connection->createCommand('INSERT into `jet_product_tmp` (`merchant_id`,`product_id`,`data`,`created_at`)VALUES
						("'.$merchant_id.'","'.$data['id'].'","'.addslashes(json_encode($data)).'","'.$curDate.'")')->execute();
				}
				fclose($handle);				
			}
			catch(Exception $e){
				$this->createExceptionLog('actionCurlprocessforproductupdate', $e->getMessage(), $data['shopName']);
			}
		}
	}
	
	public function actionProductdelete()
	{
		$webhook_content = '';
		$webhook = fopen('php://input' , 'rb');
		while(!feof($webhook)){ //loop through the input stream while the end of file is not reached
			$webhook_content .= fread($webhook, 4096); //append the content on the current iteration
		}
		fclose($webhook); //close the resource
		$connection = Yii::$app->getDb();
		$data=$webhook_content;
		if ( $webhook_content=='') {
				return;
		}
		$data = json_decode($webhook_content,true); //convert the json to array
		$data['shopName']= isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:"common";
		$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/delete/'.$data['shopName'];
		if (!file_exists($file_dir)){
            mkdir($file_dir,0775, true);
        }
		$file_path = $file_dir.'/'.$data['id'].'.log';
        $myfile = fopen($file_path, "w");
        fwrite($myfile, print_r($data,true));
        //fclose($myfile);
        $errorstr="";
		if($data && isset($data['id']))
		{
			$product_id="";
			$product_id=trim($data['id']);
			$deleted_variants=0;
			$count_variants=0;
			$sqlresult="";
			$query="";
			$productmodel ="";
			$merchant_id="";
			$archiveSku=[];
			$result=false;
			$query="SELECT u.id,api_user,api_password FROM `user` u INNER JOIN `jet_configuration` config ON u.id=config.merchant_id WHERE u.username='".$data['shopName']."' LIMIT 0,1";
			$configColl=Data::sqlRecords($query,'one','select');
			$isConfig=false;
			if($configColl)
			{
				$isConfig=true;
				$api_host="https://merchant-api.jet.com/api";
				$merchant_id=$configColl['merchant_id'];
				$jetHelper=new Jetapimerchant($api_host,$$configColl['api_user'],$$configColl['api_password']);
			}
			$query="SELECT `product_id`,`option_sku` FROM `jet_product_variants` WHERE product_id='".$product_id."'";
			try{
			    $productmodel = $connection->createCommand($query);
        		$result = $productmodel->queryOne();
        		$sqlresult=$connection->createCommand($query)->queryAll();
        		$count_variants=count($sqlresult);
			   // $transaction->commit();
			}
			catch(Exception $e) // an exception is raised if a query fails
			{
				$message1  = 'Invalid query: ' . $e->getMessage() . "\n";
				$message1 .= 'Whole query: ' . $query;
				//$errorstr.="<hr>".$message1;
				fwrite($myfile, $message1.PHP_EOL);
				$this->createExceptionLog('actionProductdelete',$message1);
			    //$transaction->rollback();
			}
			if(is_array($result)){
				$result1=false;
				//prepare skus for archive
				if(is_array($sqlresult) && count($sqlresult)>0 && $isConfig)
				{
					foreach ($sqlresult as $value_sku) {
						$archiveSku[]=$value_sku['option_sku'];
					}
					fwrite($myfile, "variant sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
					//$message.=Jetproductinfo::archiveProductOnJet($archiveSkus,$jetHelper,$merchant_id);
				}
				$sql = "DELETE FROM `jet_product_variants` WHERE product_id='".$product_id."'";
				try{
						$result1 = $connection->createCommand($sql)->execute();
						$deleted_variants=count($result1);
				}catch(Exception $e){
							$message1  = 'Invalid query: ' . $e->getMessage() . "\n";
							$message1 .= 'Whole query: ' . $sql;
							fwrite($myfile, $message1.PHP_EOL);
							$this->createExceptionLog('actionProductdelete',$message1);
							//$errorstr.="<hr>".$message1;
				}	
			}

		//delete product data 
			$deleted_product=0;
			$query="";
			$productmodel ="";
			$result=false;
			$query="SELECT `id`,`merchant_id`,`sku` FROM `jet_product` WHERE id='".$product_id."'";
			try
			{
			    $productmodel = $connection->createCommand($query);
        		$result = $productmodel->queryOne();
			    // $transaction->commit();
			}
			catch(Exception $e) // an exception is raised if a query fails
			{
				$message1  = 'Invalid query: ' . $e->getMessage() . "\n";
				$message1 .= 'Whole query: ' . $query;
				fwrite($myfile, $message1.PHP_EOL);
				//$errorstr.="<hr>".$message1;
				$this->createExceptionLog('actionProductdelete',$message1);
			    //$transaction->rollback();
			}
			if(is_array($result)){
				$merchant_id=$result['merchant_id'];
				$result1=false;
				//prepare skus for archive
				if($isConfig){
					$archiveSku[]=$result['sku'];
					fwrite($myfile, "simple sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
				}

				$sql = "DELETE FROM `jet_product` WHERE id='".$product_id."'";
				try{
					$result1 = $connection->createCommand($sql)->execute();
					$deleted_product=count($result1);
					$query="";
					$productmodel ="";
					$result=false;
					$query="SELECT * FROM `insert_product` WHERE merchant_id='".$merchant_id."'";
					$productmodel = $connection->createCommand($query);
				    $result = $productmodel->queryOne();
				    if(is_array($result)){
						if($result['product_count']>0)
							$count=$result['product_count']-1;
						$sqlresult1=false;
						$query1="UPDATE `insert_product` SET  product_count='".$count."' where merchant_id='".$merchant_id."'";
						$result1 = $connection->createCommand($query1)->execute();
						
					}
				}catch(Exception $e){
					$message1  = 'Invalid query: ' . $e->getMessage() . "\n";
					$message1 .= 'Whole 1st query: ' . $sql . "\n";
					$message1 .= 'Whole 2nd query: ' . $query . "\n";
					$message1 .= 'Whole 3rd query: ' . $query1 . "\n";
					//$errorstr.="<hr>".$message1;
					fwrite($myfile, $message1.PHP_EOL);
					$this->createExceptionLog('actionProductdelete',$message1);
				}
					
			}else{
				$message1  = json_encode($result).' Either select result is false or deleted varients-'.$deleted_variants.' not equal to count varients-'.$count_variants;
				fwrite($myfile, $message1.PHP_EOL);
				//$errorstr.="<hr>".$message1;
			}
			if(count($archiveSku)>0 && $isConfig){
				//send request for archive
				$message=Jetproductinfo::archiveProductOnJet($archiveSku,$jetHelper,$merchant_id);
				fwrite($myfile,PHP_EOL."archive sku(s) response from jet:".PHP_EOL.$message.PHP_EOL);
			}
		}
        fclose($myfile);
	}

	public function actionIsinstall()
	{
		$webhook_content = '';
		$webhook = fopen('php://input' , 'rb');
		while(!feof($webhook)){ //loop through the input stream while the end of file is not reached
			$webhook_content .= fread($webhook, 4096); //append the content on the current iteration
		}
		fclose($webhook); //close the resource
		$data="";
		$data=$webhook_content;
		if ( $webhook_content=='' || empty(json_decode($data,true))) {
				return;
		}
		$data = json_decode($webhook_content,true); //convert the json to array
		$data['shopName']= isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:"common";
		$path=\Yii::getAlias('@webroot').'/var/jet/uninstall/'.$data['shopName'];
		if (!file_exists($path)){
			mkdir($path,0775, true);
		}
		$file_path = $path.'/data.log';
		$myfile = fopen($file_path, "a+");
		fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
		fwrite($myfile, print_r($data,true));
		fclose($myfile);
		
		$url = Yii::getAlias('@webjeturl')."/shopifywebhook/curlprocessforuninstall";
		//var_dump(http_build_query( $data ));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_TIMEOUT,1);
		$result = curl_exec($ch);
		curl_close($ch);
		exit(0);
	}

	public function actionCurlprocessforuninstall()
	{
		
		$data = $_POST;
		$shop="";
		$model="";
		$model1="";
		$modelnew="";
		if($data && isset($data['id'])){
			$shop=$data['myshopify_domain'];
			$storeData=Data::sqlRecords("SELECT id,email FROM `jet_shop_details` WHERE `shop_url`='".$shop."' LIMIT 0,1","one","select");
			if(isset($storeData['id']))
			{
				Data::sqlRecords("UPDATE `jet_shop_details` SET install_status=0,uninstall_date='".date('Y-m-d H:i:s')."' WHERE shop_url='".$shop."'");
				if($storeData['email'])
					Sendmail::uninstallmail($storeData['email']);
			}
		}
		
	}
	public function actionCancelled()
	{
		$webhook = fopen('php://input' , 'rb');
		while(!feof($webhook)){
			$webhook_content .= fread($webhook, 4096);
		}
		$realdata="";
		$data=array();
		$orderData=array();
		fclose($webhook);
		$realdata=$webhook_content;
		if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
				return;
		}
		$data = json_decode($realdata,true);

		if($data && isset($data['id']))
		{	
			$orderData=JetOrderDetail::find()->where(['shopify_order_id'=>$data['id'],'shopify_order_name'=>$data['name']])->one();
			if($orderData)
			{
				$merchant_id=$orderData->merchant_id;
				$filename="";
				$filename1="";
				$file="";

				$file_path = \Yii::getAlias('@webroot').'/var/jet/order/'.$merchant_id.'/'.$orderData->reference_order_id;
				if(!file_exists($file_path)){
					mkdir($file_path, 0775, true);
				}
				$filename=$file_path.'/cancel.log';
				$file=fopen($filename,'a+');
				$errorMessage=PHP_EOL."[".date('d-m-Y H:i:s')."]\n";
				fwrite($file, $errorMessage);
				$modelUser="";
				$token="";
	
				$logMessage=PHP_EOL."[".date('d-m-Y H:i:s')."]\n"."Cancel Data:\n".$realdata."\n";
				fwrite($file, $logMessage);

				$jetConfigModel= new JetConfiguration();
				$jetConfig=$jetConfigModel->find()->where(['merchant_id' => $merchant_id])->one();
				if($jetConfig){
					$fullfillmentnodeid=$jetConfig->fullfilment_node_id;
					$jetHelper = new Jetapimerchant($jetConfig->api_host,$jetConfig->api_user,$jetConfig->api_password);
					$email=$jetConfig->merchant_email;
				}else{
					$errorMessage=PHP_EOL."Jet Api credentials is either incomplete or incorrect\n";
					fwrite($file, $errorMessage);
					fclose($file);
					exit(0);
				}	
				if($orderData->status=="acknowledged")
				{
					$errorMessage="Enter under acknowledged \n";
					fwrite($file, $errorMessage);
					try
					{
						$customerModel=new JetConfig();
						$data['timestamp']=date("d-m-Y H:i:s");
						//$orderData->shipment_data=json_encode($data);
						$merchant_order_id="";
						$reference_order_id="";
						$id="";
						$jetOrderdata="";
						//$orderData->save(false);
						$merchant_order_id=$orderData->merchant_order_id;
						$reference_order_id=$orderData->reference_order_id;
						$id=$orderData->id;
						$jetOrderdata=json_decode($orderData->order_data,true);
						$flagCarr=false;
						$request_shipping_carrier="";
						$request_shipping_level="";
						$request_shipping_carrier=$jetOrderdata['order_detail']['request_shipping_carrier'];
						if($request_shipping_carrier==""){
							if(isset($jetOrderdata['order_detail']['request_shipping_method'])){
								$flagCarr=true;
								$request_shipping_carrier=$jetOrderdata['order_detail']['request_shipping_method'];
							}
						}
						//$response=$data['fulfillments'];
						$offset_end="";
						$shiptime="";
						$shipdatetime="";
						$expected_delivery_date="";
						$shipment_id=time();
						$offset_end = $this->getStandardOffsetUTC();
						if(empty($offset_end) || trim($offset_end)=='')
							$offset = '.0000000-00:00';
						else
							$offset = '.0000000'.trim($offset_end);
						$dt = new \DateTime($data['updated_at']);
						$shiptime=$dt->format('Y-m-d H:i:s');
						$shipdatetime=strtotime($dt->format('Y-m-d H:i:s'));
						$expected_delivery_date = date("Y-m-d", $shipdatetime) . 'T' . date("H:i:s", $shipdatetime).$offset;
						$tracking_number=time()."98563";
						$address1="";
						$resultAdd="";
						$resultAdd2="";
						$address2="";
						$Resultcity="";
						$city="";
						$Resultstate="";
						$state="";
						$Resultzip="";
						$zip="";
						$resultAdd=$customerModel->find()->where(['merchant_id' => $merchant_id,'data'=>'first_address'])->one();
						if($resultAdd)
							$address1=$resultAdd->value;
						
						$resultAdd2=$customerModel->find()->where(['merchant_id' => $merchant_id,'data'=>'second_address'])->one();
						if($resultAdd2)
							$address2=$resultAdd2->value;
						
						$Resultcity=$customerModel->find()->where(['merchant_id' => $merchant_id,'data'=>'city'])->one();
						if($Resultcity)
							$city=$Resultcity->value;
						
						$Resultstate=$customerModel->find()->where(['merchant_id' => $merchant_id,'data'=>'state'])->one();
						if($Resultstate)
							$state=$Resultstate->value;
						
						$Resultzip=$customerModel->find()->where(['merchant_id' => $merchant_id,'data'=>'zipcode'])->one();
						if($Resultzip)
							$zip=$Resultzip->value;
						if($address1!="" || $city!="" || $state!="" || $zip!="")
						{
							$flag=true;
							$array_return = array('address1'=>$address1,
									'address2'=>$address2,
									'city'=>$city,
									'state'=>$state,
									'zip_code'=>$zip
							);
						}
						$shipment_arr=array();
						$shopify_shipment_data=array();
						//$errorMessage="shipment Items".print_r($response[0]['line_items'],true)."\n";
						//fwrite($file1, $errorMessage);
						foreach($jetOrderdata['order_items'] as $key=>$value)
						{
							if($flag)
							{
								$RMA_number = "";
								$days_to_return = 30;
								$shipment_arr[]= array(/*'shipment_item_id'=>$shipment_id.'-'.$key,*/
										'merchant_sku'=>$value['merchant_sku'],
										'response_shipment_sku_quantity'=>0,
										'response_shipment_cancel_qty'=>(int)$value['request_order_quantity'],
										'RMA_number'=>$RMA_number,
										'days_to_return'=>(int)$days_to_return,
										'return_location'=>$array_return
								);
							}
							else
							{
								$shipment_arr[]= array(/*'shipment_item_id'=>$shipment_id.'-'.$key,*/
										'merchant_sku'=>$value['merchant_sku'],
										'response_shipment_sku_quantity'=>0,
										'response_shipment_cancel_qty'=>(int)$value['request_order_quantity']
								);
							}
							$shopify_shipment_data[]=implode(',',array(0=>$value['merchant_sku'],1=>0,2=>null));
						}
						$data_ship=array();
						if($zip=="")
							$zip=85705;
						
						if($flagCarr)
						{
							$data_ship['shipments'][]=array(
									'alt_shipment_id'=>time()."123",
									'shipment_tracking_number'=>$tracking_number,
									'response_shipment_date'=>$expected_delivery_date,
									'response_shipment_method'=>"",
									'expected_delivery_date'=>$expected_delivery_date,
									'ship_from_zip_code'=>(string)$zip,
									'carrier_pick_up_date'=>$expected_delivery_date,
									'carrier'=>"USPS",
									'shipment_items'=>$shipment_arr
							);
						}else{
							$data_ship['shipments'][]=array(
									'alt_shipment_id'=>time()."123",
									'shipment_tracking_number'=>$tracking_number,
									'response_shipment_date'=>$expected_delivery_date,
									'response_shipment_method'=>"",
									'expected_delivery_date'=>$expected_delivery_date,
									'ship_from_zip_code'=>(string)$zip,
									'carrier_pick_up_date'=>$expected_delivery_date,
									'carrier'=>$request_shipping_carrier,
									'shipment_items'=>$shipment_arr
							);
						}
						$jetdata="";
						if($data_ship)
						{
							//Shipment data
							$errorMessage="shipment data send on jet.com".PHP_EOL.print_r($data_ship,true)."\n";
							fwrite($file, $errorMessage);
							
							$jetdata=$jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($data_ship),$merchant_id);
							$responseArray=json_decode($jetdata,true);
							if(!isset($responseArray['errors']))
							{
								$errorMessage="Order Canceled\n";
								fwrite($file, $errorMessage);
								$orderData->status='canceled';
								$orderData->save(false);
		    				}
		    				else
							{
								$errorMessage="Order not canceled for order ".$merchant_order_id.":".PHP_EOL.$jetdata."\n";
								fwrite($file, $errorMessage);
								$orderData->save(false);
								$ordererrorMdoel="";
								$ordererrorMdoel=new JetOrderImportError();
								$ordererrorMdoel->merchant_order_id=$merchant_order_id;
								$ordererrorMdoel->reference_order_id=$reference_order_id;
								$ordererrorMdoel->reason="Order not Canceled on jet.\nError:".json_encode($responseArray['errors']);
								$ordererrorMdoel->created_at=date("d-m-Y H:i:s");
								$ordererrorMdoel->merchant_id=$merchant_id;
								$ordererrorMdoel->save(false);
								fclose($file);
								exit(0);
								//display eror log
							}
						}
				   }
				   catch (ShopifyApiException $e)
				   {
						$errorMessage=PHP_EOL."[".date('d-m-Y H:i:s')."]\n"."Error in shopify api".$e->getMessage()."\n";
						fwrite($file, $errorMessage);
						fclose($file);
						exit(0);
				   }
				   catch (ShopifyCurlException $e)
				   {
						$errorMessage=PHP_EOL."[".date('d-m-Y H:i:s')."]\n"."Error in shopify api".$e->getMessage()."\n";
						fwrite($file, $errorMessage);
						fclose($file);
						exit(0);
				    }	  
				}
				fclose($file);
			}
			/*code by Himanshu Start*/
			if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {
				$shopName = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
				$data['shopName'] = $shopName;
			}
			$file_path = \Yii::getAlias('@webroot').'/var/jet/order/cancel/'.$shopName;
			self::log("Order Cancel data "."[".date('d-m-Y H:i:s')."]".PHP_EOL,$file_path);
			self::log($data,$file_path);
			$url = Yii::getAlias('@webjeturl')."/shopifywebhook/curlprocessforordercancel";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			/*code by Himanshu End*/
		}
	}

	public function actionCurlprocessforordercancel()
	{
		$data = (array)json_decode(json_encode($_POST), true);
		if($data && isset($data['id']) && isset($data['shopName']))
		{
			try
			{
				$query = "SELECT id FROM `user` WHERE username LIKE '".$data['shopName']."' LIMIT 0,1";
				$userCollection = Data::sqlRecords($query,"one","select");

				if($userCollection) {
					$merchant_id = $userCollection['id'];
				} else {
					return;
				}

				$shopName = $data['shopName'];
				$file_path = \Yii::getAlias('@webroot').'/var/jet/order/cancel/'.$shopName;

				if(!file_exists($file_path)){
					mkdir($file_path, 0775, true);
				}

				$orderId = $data['order_number'];

				$filename = $file_path.'/'.$orderId.'.log';
				$file = fopen($filename,'a+');
				$Message = PHP_EOL."[actionCurlprocessforordercancel][".date('d-m-Y H:i:s')."]\n";
				$Message .= "Process Qty After Order Process.";

				fwrite($file, $Message);

				$line_items = isset($data['line_items'])?$data['line_items']:[];
				if(count($line_items) == 0)
					return;

				foreach ($line_items as $key=>$product) 
				{
					if(is_object($product) && property_exists($product , 'product_id') && property_exists($product , 'variant_id'))
					{
						$product_id = $product->product_id;
						$variant_id = $product->variant_id;
						$sku = $product->sku;
						$ordered_qty = $product->quantity;
						$fulfillable_quantity = $product->fulfillable_quantity;
						$canceled_qty = $ordered_qty-$fulfillable_quantity;
					}
					elseif(is_array($product) && isset($product['product_id'], $product['variant_id']))
					{
						$product_id = $product['product_id'];
						$variant_id = $product['variant_id'];
						$sku = $product['sku'];
						$ordered_qty = $product['quantity'];
						$fulfillable_quantity = $product['fulfillable_quantity'];
						$canceled_qty = $ordered_qty-$fulfillable_quantity;
					}
					else
					{
						return;
					}
					
					$query = "SELECT id,sku,type FROM `jet_product` WHERE id=".$product_id." AND `merchant_id`=".$merchant_id." LIMIT 0,1";
			    	$result = Data::sqlRecords($query,"one","select");
			    	if($result)
			    	{
			    		if(isset($result['type'])) {
			    			if($result['type'] == 'variants')
			    			{
			    				$query = "UPDATE `jet_product_variants` SET `option_qty`=`option_qty`+".$canceled_qty."  WHERE `option_id`=".$variant_id." AND `merchant_id`=".$merchant_id;
			    				$result = Data::sqlRecords($query,null,"update");
			    			}
			    			else
			    			{
			    				$query = "UPDATE `jet_product` SET `qty`=`qty`+".$canceled_qty."  WHERE `variant_id`=".$variant_id." AND `merchant_id`=".$merchant_id;
			    				$result = Data::sqlRecords($query,null,"update");
			    			}
			    			$Message = PHP_EOL."[actionCurlprocessforordercancel][".date('d-m-Y H:i:s')."]\n";
			    			$Message .= "Qty Updated Successfully for Product Id:".$product_id;
			    			fwrite($file, $Message);
			    		} else {
			    			continue;
			    		}
			    	}
				}
				fclose($file);
			}
			catch (Exception $e)
			{
				$this->createExceptionLog('actionCancelorder',$e->getMessage(),$shopName);
				return;
			}
		}
	}

	public function checkcancelQty($sku,$order_items)
	{
		$cancel=0;
		foreach($order_items as $value)
		{
			if($value['merchant_sku']==$sku){
				$cancel=$value['request_order_cancel_qty'];
				break;
			}
		}
		return $cancel;
	}	

	public function actionCreateorder()
	{
		try
		{
			$webhook = fopen('php://input' , 'rb');
			while(!feof($webhook)){
				$webhook_content .= fread($webhook, 4096);
			}
			fclose($webhook);

			$realdata = $webhook_content;
			if ( $webhook_content == '' || empty(json_decode($realdata,true))) {
				return;
			}
			$data = json_decode($realdata,true);

			$file_path = \Yii::getAlias('@webroot').'/var/jet/order/create';
			if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {
				$shopName = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
				$data['shopName'] = $shopName;
				$file_path .= '/'.$shopName;
			}
			else {
				return;
			}

			if(!file_exists($file_path)){
				mkdir($file_path, 0775, true);
			}
			$orderId = $data['order_number'];
			$filename = $file_path.'/'.$orderId.'.log';
			$file = fopen($filename,'a+');
			//$Message = PHP_EOL."[][".date('d-m-Y H:i:s')."]\n";
			//$Message .= $webhook_content;
			$Message = PHP_EOL."[actionCreateorder][".date('d-m-Y H:i:s')."]\n";
			$Message .= print_r($data,true);
			fwrite($file, $Message);
			fclose($file);
				
			$url = Yii::getAlias('@webjeturl')."/shopifywebhook/curlprocessforordercreate";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_TIMEOUT,1);
			$result = curl_exec($ch);
			curl_close($ch);
	        exit(0);
        }
	    catch (Exception $e)
		{
			$this->createExceptionLog('actionCreateorder',$e->getMessage(),$shopName);
			return;
		}
	}

	public function actionCurlprocessforordercreate()
	{
		$data = (array)json_decode(json_encode($_POST), true);
		if($data && isset($data['id']) && isset($data['shopName']))
		{
			try
			{
				$query = "SELECT id FROM `user` WHERE username LIKE '".$data['shopName']."' LIMIT 0,1";
				$userCollection = Data::sqlRecords($query,"one","select");

				if($userCollection) {
					$merchant_id = $userCollection['id'];
				} else {
					return;
				}

				$shopName = $data['shopName'];
				$file_path = \Yii::getAlias('@webroot').'/var/jet/order/create/'.$shopName;

				if(!file_exists($file_path)){
					mkdir($file_path, 0775, true);
				}

				$orderId = $data['order_number'];

				$filename = $file_path.'/'.$orderId.'.log';
				$file = fopen($filename,'a+');
				$Message = PHP_EOL."[actionCurlprocessforordercreate][".date('d-m-Y H:i:s')."]\n";
				//$Message .= print_r($data,true);
				$Message .= "Entered In actionCurlprocessforordercreate";
				fwrite($file, $Message);

				$line_items = isset($data['line_items'])?$data['line_items']:[];
				if(!$line_items || count($line_items) == 0)
					return;

				foreach ($line_items as $key=>$product) 
				{
					if(!isset($product['product_id'],$product['variant_id']))
					{
						continue;
					}
					$product_id = $product['product_id'];
					$variant_id = $product['variant_id'];
					$sku = $product['sku'];
					$ordered_qty = $product['quantity'];
					$fulfillable_quantity = $product['fulfillable_quantity'];
					
					$query = "SELECT id,sku,type,qty FROM `jet_product` WHERE id=".$product_id." AND `merchant_id`=".$merchant_id." LIMIT 0,1";
			    	$result = Data::sqlRecords($query,"one","select");
			    	$remQty=0;
			    	$isChangeQty=false;
			    	if($result)
			    	{
			    		$sku=$result['sku'];
			    		$Message = "inventory update in product id:".$product_id.PHP_EOL;
			    		fwrite($file, $Message);
			    		if(isset($result['type'])) 
			    		{
			    			if($result['type'] == 'variants')
			    			{
			    				$query = "SELECT `option_qty`,`option_sku` FROM `jet_product_variants` WHERE `option_id`=".$variant_id." AND `merchant_id`=".$merchant_id;
			    				$varResult = Data::sqlRecords($query,"one","select");
			    				if(is_array($varResult) && count($varResult)>0)
			    				{
			    					$sku=$varResult['option_sku'];
			    					$Message = "Variant inventory update".PHP_EOL;
			    					fwrite($file, $Message);
			    					$isChangeQty=true;
			    					$remQty=(int)$varResult['option_qty']-$ordered_qty;
			    					$query = "UPDATE `jet_product_variants` SET `option_qty`=".$remQty." WHERE `option_id`=".$variant_id." AND `merchant_id`=".$merchant_id;
			    					Data::sqlRecords($query,null,"update");
			    					//check if sku is parent sku
			    					$query = "UPDATE `jet_product` SET `qty`=".$remQty." WHERE `variant_id`=".$variant_id." AND `merchant_id`=".$merchant_id;
			    					Data::sqlRecords($query,null,"update");
			    				}
			    				
			    			}
			    			else
			    			{
			    				$isChangeQty=true;
			    				$Message = "simple product inventory update".PHP_EOL;
			    				fwrite($file, $Message);
			    				$remQty=$result['qty']-$ordered_qty;
			    				$query = "UPDATE `jet_product` SET `qty`=".$remQty."  WHERE `variant_id`=".$variant_id." AND `merchant_id`=".$merchant_id;
			    				Data::sqlRecords($query,null,"update");
			    			}
			    			if($isChangeQty)
			    			{
			    				if($remQty<0)
			    					$remQty=0;
			    				$jetConfig=Data::sqlRecords('SELECT `fullfilment_node_id`,`api_user`,`api_password` FROM `jet_configuration` WHERE merchant_id="'.$merchant_id.'"',"one","select");
							    if($jetConfig)
							    {
							    	$jetHelperFlag = true;
							    	$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
							    	$api_host="https://merchant-api.jet.com/api";
							    	$api_user=$jetConfig['api_user'];
							    	$api_password=$jetConfig['api_password'];
							    	$jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
							    	fwrite($file,"send update inventory on jet ".PHP_EOL." sku: ".$sku." --- inventory: ".$remQty.PHP_EOL);
							    	$message=Jetproductinfo::updateQtyOnJet($sku,$remQty,$jetHelper,$fullfillmentnodeid,$merchant_id);
							    	fwrite($file,"inventory response fron jet :".PHP_EOL.$message.PHP_EOL);
							    }
							    $url = Yii::getAlias('@webwalmarturl')."/webhookupdate/productupdate";
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL,$url);
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['inventory'=>$remQty,'sku'=>$sku,'type'=>'inventory','merchant_id'=>$merchant_id]));
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
								curl_setopt($ch, CURLOPT_TIMEOUT,1);
								$result = curl_exec($ch);
								$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
								if($http_code==200)
									fwrite($file,"inventory send on walmart app ".PHP_EOL);
								else
									fwrite($file,"inventory data unable to send on walmart app ".PHP_EOL);
								curl_close($ch);
								$Message = "Qty Updated Successfully for Product Id:".$product_id;
			    				fwrite($file, $Message);
			    			}
			    			
			    		} else {
			    			continue;
			    		}
			    	}
				}

				fclose($file);
			}
			catch (Exception $e)
			{
				$this->createExceptionLog('actionCurlprocessforordercreate',$e->getMessage(),$shopName);
				return;
			}
		}
		return;
	}
	public static function log($data="Himanshu",$file_path="cancel.log")
	{
		if(!file_exists($file_path)) {
			mkdir($file_path, 0775, true);
		}
		$filename = $file_path.'/cancel.log';
		$file = fopen($filename,'a+');
		
		if(is_array($data))
			fwrite($file, print_r($data,true));
		else
			fwrite($file, $data);

		fclose($file);
	}
	public function emailforproductupdate($data)
	{
			
			    $date =  date('Y-m-d H:i:s');
				$productinfo =[];
				$query = 'select jet.email,jet.merchant_id from `jet_extension_detail` jet INNER JOIN `user` user ON user.id=jet.merchant_id where user.username ="'.$data['shopName'].'" limit 0,1';
			
		        $allData = Data::sqlRecords($query, 'one');
		        $productThresoldValue = 10;
		        if (!isset($allData['email']) && empty($allData['email']))
		        {
		            $productThresoldValue = 10;
		            $query="SELECT `email`,`merchant_id` FROM `walmart_shop_details` WHERE shop_url='".$data['shopName']."'";
		            $allData = Data::sqlRecords($query, 'one');
		            $email = $allData['email'];

		        }
		        else{
		        	$email = $allData['email'];
		        }
		        if(isset($email)){
					            $templatedata = '';
					            foreach ($data['variants'] as $key => $value) {
					                        if($productThresoldValue > $value['inventory_quantity']){
					                                $templatedata .= '<tr><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
					                                    .$data['title'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
					                                    .$value['sku'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
					                                    .$value['inventory_quantity'].'</td></tr>';
					                                    $productinfo[] = $value['sku'];
					                                    
					                        }
					                       
					                    }
					if(!empty($productinfo)){

						        $model = Data::sqlRecords("SELECT * FROM `jet_notifications` WHERE `merchant_id`='".$allData['merchant_id']."' AND `status`=1 AND `product_id`='".$data['id']."'" , 'one');
						        
						        if(empty($model)){
						        	
						            $i = 1;
					 				$date = strtotime($date . " +".$i."days");
					 				$date = date('Y-m-d H:i:s',$date);
					 				$productSku = implode(",",$productinfo);
					 				$query ="INSERT INTO `jet_notifications` (`merchant_id`,`product_id`,`child_sku`,`date`,`type`,`status`) VALUES ('".$allData['merchant_id']."',".$data['id'].",'".$productSku."','".$date."','for inventry update','1')";
				                	$model = Data::sqlRecords($query, 'all','insert');
						            Sendmail::productStockMail($email,$templatedata);
					        	}
					        	else{
					        		$currentDate =  date('Y-m-d H:i:s');
					        		$dbchildsKu = explode(",",$model['child_sku']);
					        		sort($dbchildsKu);
					        		sort($productinfo);
					        		if($dbchildsKu != $productinfo){
						        		if(strtotime($model['date']) < strtotime($currentDate)){
						        			$templatedata = '';
								            foreach ($data['variants'] as $key => $value) {
								                        if($productThresoldValue > $value['inventory_quantity']){ 
								                        	foreach ($dbchildsKu as $key => $val) {
								                        	if($val != $value['sku'])
									                        	{
									                                $templatedata .= '<tr><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
									                                    .$data['title'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
									                                    .$value['sku'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
									                                    .$value['inventory_quantity'].'</td></tr>';
									                                    $model = Data::sqlRecords("INSERT INTO `jet_notifications` (`merchant_id`,`product_id`,`status`,`date`,`type`) VALUES ('".$allData['merchant_id']."','".$value['product_id']."','1','".$date."','for productinventry update')", 'all','insert');
									                        	}
								                             }
								                        }
								                       
								                    }
								            
								            Sendmail::productStockMail($email,$templatedata);
						        		}
						        	}
					        	}
			        	}
			    }


	}
	public function matchShipmentSku($lineItems,$orderItems)
	{		
		$matchFlag = "";
		foreach ($orderItems as $key => $value) 
		{
			if ($lineItems['sku']==$value['merchant_sku']) {
				$matchFlag = "ced";
			}
		}
		return $matchFlag;		
	}
}
