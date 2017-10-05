<?php
namespace frontend\modules\jet\controllers;

use common\models\User;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Sendmail;
use frontend\modules\jet\models\JetConfig;
use frontend\modules\jet\models\JetOrderImportError;
use Yii;
use yii\web\Controller;

class JetwebhookController extends Controller
{
	public function beforeAction($action)
	{
		Yii::$app->controller->enableCsrfValidation = false;
		if(isset($_POST['shopName']) && !Jetappdetails::appstatus($_POST['shopName']))
		{
			return false;
		}				
		return true;
	}

	public function actionCurlproductcreate()
	{
		$data = $_POST;

		if(isset($data['shopName']) && isset($data['id']))
		{
			try
			{
				$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/create/'.$data['shopName'];
			    if (!file_exists($file_dir)) 
			    {
			        mkdir($file_dir, 0775, true);
			    }
			    $filenameOrig="";
			    $filenameOrig=$file_dir.'/'.$data['id'].'.log';
			    $fileOrig="";
			    $fileOrig=fopen($filenameOrig,'w');
			    fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($data));
			    fclose($fileOrig);
			    $sql="SELECT `id` FROM `user` WHERE username='".$data['shopName']."' LIMIT 0,1";
			    $userData = Data::sqlRecords($sql,"one","select");
				$query="SELECT `id` FROM `jet_product` WHERE id='".$data['id']."' LIMIT 0,1";
				$proresult = Data::sqlRecords($query);
				if(!$proresult && isset($userData['id']))
				{	
					$merchant_id=$userData['id'];
					$customData = JetProductInfo::getConfigSettings($merchant_id);
	    			$import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";					
					Jetproductinfo::saveNewRecords($data,$merchant_id,$import_status);
				}
				unset($data,$query);
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
	public function createExceptionLog($functionName,$msg,$shopName = 'common')
    {
        $dir = \Yii::getAlias('@webroot').'/var/jet/exceptions/'.$functionName.'/'.$shopName;
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        try
        {
            throw new Exception($msg);
        }catch(Exception $e){
            $filenameOrig = $dir.'/'.time().'.txt';
            $handle = fopen($filenameOrig,'a');
            $msg = date('d-m-Y H:i:s')."\n".$msg."\n".$e->getTraceAsString();
            fwrite($handle,$msg);
            fclose($handle);
            $this->sendEmail($filenameOrig,$msg);   
        }        
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
	
	public function actionCurlprocessfororder()
	{		
		$data = $_POST;
		$flag=false;
		
		if($data && isset($data['id']))
		{
			try
			{
				$orderData = [];
				$query="SELECT jet_order_detail.merchant_id,order_data,status,api_user,api_password FROM `jet_order_detail` INNER JOIN `jet_configuration` ON  jet_order_detail.merchant_id=jet_configuration.merchant_id WHERE  shopify_order_id='".$data['id']."' AND (status='acknowledged' or status='inprogress') LIMIT 0,1";
				$orderData = Data::sqlRecords($query,'one','select');
				if(isset($orderData['merchant_id'],$orderData['api_user']))
				{
					$jetClientDatails = $jetHelper = $jetOrderdata = $resultConfig = $addressDetails = [];
					$merchant_id = $merchant_order_id = $reference_order_id = $api_provider = $id = $token = $shopname = $api_user = $api_password = $email = $sc = $orderStatus = "";

					$flagCarr=false;
					
					$merchant_id=$orderData['merchant_id'];
					
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
		            $days_to_return = isset($addressDetails['day_to_return']) ? $addressDetails['day_to_return']:30;		            
		            
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
                    $response = $shipment_arr = [];
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
							
							$status=true;	
							$jetdata=$jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($data_ship),$merchant_id,$status);
							fwrite($file1,PHP_EOL."shipment data send on jet.com".PHP_EOL.json_encode($data_ship).PHP_EOL."Processing status (http_code) => ".$status);
							$responseArray=[];
							$responseArray=json_decode($jetdata,true);
							if($status==204)
							{
								fwrite($file1, PHP_EOL."ORDER SUCCESSFULLY SHIPPED ON JET".PHP_EOL);	
								$resultResponse = [];
                                $resultResponse = $jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchant_order_id,$merchant_id);
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
					if(trim($orderStatus)!="")
					{
						$query="UPDATE `jet_order_detail` SET status='".$orderStatus."',shipment_data='".addslashes(json_encode($data))."',shipped_at='".date('Y-m-d H:i:s')."' WHERE `merchant_id`='".$merchant_id."' AND reference_order_id='".$reference_order_id."'";
					}
					else
					{
						$query="UPDATE `jet_order_detail` SET shipment_data='".addslashes(json_encode($data))."',shipped_at='".date('Y-m-d H:i:s')."' WHERE `merchant_id`='".$merchant_id."' AND reference_order_id='".$reference_order_id."'";
						
					}
					fwrite($file1,PHP_EOL."Query to update order status".PHP_EOL.$query.PHP_EOL);
					Data::sqlRecords($query,null,'update');
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

	public function getShipementStatus($items)
	{
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

	public function actionCurlprocessforproductupdate()
	{
		$data = $_POST;
		//$data=json_decode('{"id":9832091084,"title":"dummy chairs","body_html":"kllllllllllllllllllllxcvxcv","vendor":"cedcommerce1","product_type":"dummy","created_at":"2017-03-03T05:40:52-05:00","handle":"fgjhjhjhj","updated_at":"2017-06-02T09:48:02-04:00","published_at":"2017-03-03T05:40:00-05:00","template_suffix":null,"published_scope":"global","tags":"","variants":[{"id":35231155020,"product_id":9832091084,"title":"Default Title","price":"40.00","sku":"dummy-1","position":1,"grams":3,"inventory_policy":"deny","compare_at_price":null,"fulfillment_service":"manual","inventory_management":"shopify","option1":"Default Title","option2":null,"option3":null,"created_at":"2017-03-03T05:40:52-05:00","updated_at":"2017-06-02T09:48:02-04:00","taxable":true,"barcode":"12345678950","image_id":null,"inventory_quantity":94,"weight":0.003,"weight_unit":"kg","old_inventory_quantity":94,"requires_shipping":true}],"options":[{"id":11904065932,"product_id":9832091084,"name":"Title","position":1,"values":["Default Title"]}],"images":[{"id":22767791756,"product_id":9832091084,"position":1,"created_at":"2017-03-03T05:40:54-05:00","updated_at":"2017-04-19T00:59:49-04:00","width":128,"height":128,"src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/shopping-cart-item5.png?v=1492577989","variant_ids":[]}],"image":{"id":22767791756,"product_id":9832091084,"position":1,"created_at":"2017-03-03T05:40:54-05:00","updated_at":"2017-04-19T00:59:49-04:00","width":128,"height":128,"src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/shopping-cart-item5.png?v=1492577989","variant_ids":[]},"shopName":"ced-jet.myshopify.com"}',true);
		if($data && isset($data['id']) && isset($data['shopName']))
		{
			try
			{

				$userData=Data::sqlRecords("SELECT `id` FROM `user` WHERE username='".$data['shopName']."' LIMIT 0,1","one","select");
				if(isset($userData['id']))
				{
					$syncFields=[];
					$merchant_id=$userData['id'];
					$configflag=false;
					$customData = JetProductInfo::getConfigSettings($merchant_id);	
					if(isset($customData['sync-fields']))
					{
						$syncFieldsData=json_decode($customData['sync-fields'],true);
						if(is_array($syncFieldsData) && count($syncFieldsData)>0)
						{
							$configflag=true;
							$syncFields['sync-fields']=$syncFieldsData;
							//Jetproductinfo::updateDetails($data,$syncFields,$merchant_id);
						}
					}
					if(!$configflag)
					{
						$sync_fields = [
						    'sku' => '1',
						    'title' => '1',
						    'image' => '1',
						    'product_type'=>'1',
						    'inventory' => '1',
						    'weight' => '1',
						    'price' => '1',
						    'upc' => '1',
						    'vendor' => '1',
						    'description' => '1',
						    'variant_options' => '1',
						];
						$syncFields['sync-fields']=$sync_fields;
					}	
					Jetproductinfo::updateDetails($data,$syncFields,$merchant_id,true);
				}
			}
			catch(Exception $e){
				$this->createExceptionLog('actionCurlprocessforproductupdate', $e->getMessage(), $data['shopName']);
			}
		}
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
		$url = Yii::getAlias('@webjeturl')."/jetwebhook/curlprocessforuninstall?maintenanceprocess=1";
		Data::sendCurlRequest($data,$url);
		exit(0);
	}
	
	public function actionCurlprocessforuninstall()
	{		
		$data = $_POST;
		$path=\Yii::getAlias('@webroot').'/var/jet/uninstall/'.$data['myshopify_domain'];
		if (!file_exists($path)){
			mkdir($path,0775, true);
		}
		$file_path = $path.'/data.log';
		$myfile = fopen($file_path, "a+");
		fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
		fwrite($myfile,json_encode($data));
		fclose($myfile);
		$shop="";		
		if($data && isset($data['id']))
		{
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

	// inventory update on jet for cancel order
	public function actionCurlprocessforinventoryupdate()
	{
		$inventoryData = $_POST;
		//$inventoryData[34823441804] =["inventory"=> 20.00,"sku"=>"levis_med","merchant_id"=>"14"];
		try
		{
			/*$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/update/';
		    if (!is_dir($file_dir)){
		        mkdir($file_dir,0775, true);
		    }
		    $filenameOrig=$file_dir.'/'.time().'.log';
		    $handle=fopen($filenameOrig,'w');
		    fwrite($handle, PHP_EOL."inventory data:".print_r($inventoryData,true));*/
			if(is_array($inventoryData))
			{		
				//fwrite($handle, "inventory data".PHP_EOL);
				foreach ($inventoryData as $data) 
				{
					$remQty=$data['inventory'];
					$merchant_id=$data['merchant_id'];
					$sku=$data['sku'];
					//$file=$data['file'];
					if($remQty<0)
				    	$remQty=0;
					$jetConfig=Data::sqlRecords('SELECT `fullfilment_node_id`,`api_user`,`api_password` FROM `jet_configuration` WHERE merchant_id="'.$merchant_id.'"',"one","select");
				    if(isset($jetConfig['api_user']))
				    {
				    	//fwrite($handle, "jet helper data".PHP_EOL);
				    	$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
				    	$api_host=API_HOST;
				    	$api_user=$jetConfig['api_user'];
				    	$api_password=$jetConfig['api_password'];
				    	$jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
				    	//if(is_object($file))
				    		//fwrite($file,"send update inventory on jet ".PHP_EOL." sku: ".$sku." --- inventory: ".$remQty.PHP_EOL);
				    	$message=Jetproductinfo::updateQtyOnJet($sku,$remQty,$jetHelper,$fullfillmentnodeid,$merchant_id);
				    	//fwrite($handle, "inventory on jet response".$message.PHP_EOL);
				    	//if(is_object($file))
				    		//fwrite($file,"inventory response fron jet :".PHP_EOL.$message.PHP_EOL);
				    }
				}
			}
			//fclose($handle);
		}	
		catch(Exception $e)
		{
			$this->createExceptionLog('actionCurlprocessforinventoryupdate',$e->getMessage());
			return;
		}	
	}

	public function actionCurlprocessforpriceupdate()
	{
		$priceData = $_POST;
		//$priceData[34823441804] =["product_id"=>9772728588,"price"=> 120.00,"sku"=>"levis_med","merchant_id"=>"14"];
		try
		{
			/*$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/update/';
		    if (!is_dir($file_dir)){
		        mkdir($file_dir,0775, true);
		    }
		    $filenameOrig=$file_dir.'/price'.time().'.log';
		    $handle=fopen($filenameOrig,'w');
		    fwrite($handle, PHP_EOL."price data:".print_r($priceData,true));*/
			if(is_array($priceData))
			{		
				foreach ($priceData as $key=>$data) 
				{
					$price=$data['price'];
					$merchant_id=$data['merchant_id'];
					$sku=$data['sku'];
					//fwrite($handle, PHP_EOL."price data:".$data['product_id'].PHP_EOL);
					//check client set custom price
					$query="SELECT `update_price` FROM `jet_product_details` WHERE product_id=".$data['product_id']." LIMIT 0,1";
					//fwrite($handle, PHP_EOL."jet product query:".PHP_EOL.$query);
					$product=Data::sqlRecords($query,"one","select");
					if(isset($product['update_price']) && $product['update_price']>0)
					{
						$price = $product['update_price'];
					}
					else
					{
						$query="SELECT `update_option_price` FROM `jet_product_variants` WHERE option_id=".$key." LIMIT 0,1";
						$productVariant=Data::sqlRecords($query,"one","select");
						//fwrite($handle, PHP_EOL."jet product variant query:".PHP_EOL.$query);
						if(isset($productVariant['update_option_price']) && $productVariant['update_option_price']>0)
						{
							$price = $productVariant['update_option_price'];
						}
					}
					if($price>0)
					{
						//fwrite($handle, PHP_EOL."price value:".$price.PHP_EOL);
						$jetConfig=Data::sqlRecords('SELECT `fullfilment_node_id`,`api_user`,`api_password` FROM `jet_configuration` WHERE merchant_id="'.$merchant_id.'"',"one","select");
					    if(isset($jetConfig['api_user']))
					    {
					    	//fwrite($handle, PHP_EOL."jet data:".PHP_EOL);
					    	$fullfillmentnodeid=$jetConfig['fullfilment_node_id'];
					    	$api_host=API_HOST;
					    	$api_user=$jetConfig['api_user'];
					    	$api_password=$jetConfig['api_password'];
					    	$jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
					    	$message=Jetproductinfo::updatePriceOnJet($sku,$price,$jetHelper,$fullfillmentnodeid,$merchant_id);
					    	//fwrite($handle, PHP_EOL."jet response:".$message.PHP_EOL);
					    }
					}
				}
			}
		}	
		catch(Exception $e)
		{
			$this->createExceptionLog('actionCurlprocessforpriceupdate',$e->getMessage());
			return;
		}	
	}

	// Cancle order on jet if cancelled from shopify store
	public function actionCurlprocessforordercancel()
	{
		$data = $_POST;
		if($data && isset($data['id']) && isset($data['shopName']))
		{		
			
			$orderData = [];
			$query = "SELECT `merchant_id`,`reference_order_id`,`merchant_order_id` FROM `jet_order_detail` WHERE `shopify_order_id`='".$data['id']."' AND `shopify_order_name`='".$data['name']."' AND status='acknowledged' ";
			$orderData=Data::sqlRecords($query,'one','select');
			if(!empty($orderData))
			{				
				try
				{
					$jetConfig = $post = $postData = [];
					$jetConfig = Data::getjetConfiguration($merchant_id);
					$jetHelper=Jetapimerchant("https://merchant-api.jet.com/api",$jetConfig['api_user'],$jetConfig['api_password']);
					$postData = ['merchant_id'=>$merchant_id,'merchant_order_id'=>$orderData->merchant_order_id,'reference_order_id'=>$orderData->reference_order_id,'jetHelper'=>$jetHelper];
					
					$obj = new JetorderimporterrorController(Yii::$app->controller->id,'');
					$obj->actionCancel($postData);
				}					
				catch (\Exception $e)
				{
					$this->createExceptionLog('actionCurlprocessforordercancel',$e->getMessage());
					return;
				}				
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
		        if(isset($email))
		        {
		            $templatedata = '';
		            foreach ($data['variants'] as $key => $value) 
		            {
                        if($productThresoldValue > $value['inventory_quantity'])
                        {
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
	public function actionCurlprocessfordelete()
	{
		$data = $_POST;
		if(is_array($data) && count($data)>0)
		{
			$query="SELECT api_user,api_password FROM `jet_configuration` WHERE merchant_id='".$data['merchant_id']."' LIMIT 0,1";
			$configColl = Data::sqlRecords($query,'one','select');
			if(isset($configColl['api_user']))
			{
				$api_host="https://merchant-api.jet.com/api";
				$jetHelper = new Jetapimerchant($api_host,$configColl['api_user'],$configColl['api_password']);
				$message=Jetproductinfo::archiveProductOnJet($data['archiveSku'],$jetHelper,$data['merchant_id']);
			}
		}
		/*if(isset($data['shopName']) && isset($data['id']))
		{
			$file_dir = \Yii::getAlias('@webroot').'/var/jet/product/delete/'.$data['shopName'];
			if (!file_exists($file_dir)){
	            mkdir($file_dir,0775, true);
	        }
			$file_path = $file_dir.'/'.$data['id'].'.log';
	        $myfile = fopen($file_path, "w");
	        fwrite($myfile, print_r($data,true));
	        $errorstr="";
			$deleted_variants = $count_variants = 0;			
			$archiveSku = $configColl = [];
			$result = $isConfig = false;			
			$product_id = $sqlresult = $query = $productmodel = $merchant_id =  $jetHelper = "";

			$product_id=trim($data['id']);
			
			$query="SELECT u.id,api_user,api_password FROM `user` u INNER JOIN `jet_configuration` config ON u.id=config.merchant_id WHERE u.username='".$data['shopName']."' LIMIT 0,1";
			$configColl = Data::sqlRecords($query,'one','select');
			
			if(!empty($configColl))
			{
				$isConfig=true;
				$api_host="https://merchant-api.jet.com/api";
				$merchant_id=$configColl['id'];
				$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$configColl['api_user'],$configColl['api_password']);
			}
			$query="SELECT `product_id`,`option_sku` FROM `jet_product_variants` WHERE product_id='".$product_id."'";
			$sqlresult = Data::sqlRecords($query,'all','select');
			$count_variants = count($sqlresult);
			
			if(!empty($sqlresult) && is_array($sqlresult))
			{
				$result1=false;
				//prepare skus for archive
				if(is_array($sqlresult) && count($sqlresult)>0 && $isConfig)
				{
					foreach ($sqlresult as $value_sku) {
						$archiveSku[]=$value_sku['option_sku'];
					}
					fwrite($myfile, "variant sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
				}
				$sql = "DELETE FROM `jet_product_variants` WHERE product_id='".$product_id."'";
				Data::sqlRecords($sql,null,'delete');						
			}

		    // delete product data 
			$deleted_product=0;
			$query="";
			$productmodel ="";
			$result=[];
			$query="SELECT `id`,`merchant_id`,`sku`,`product_type` FROM `jet_product` WHERE id='".$product_id."' LIMIT 0,1";
			$result = Data::sqlRecords($query,'one','select');
			
			if(isset($result['id']))
			{
				fwrite($myfile, PHP_EOL."product ready to delete".PHP_EOL);
				$merchant_id=$result['merchant_id'];
				$result1=false;
				//prepare skus for archive
				if($isConfig){
					$archiveSku[]=$result['sku'];
					fwrite($myfile, "simple sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
				}
				$product_type=$result['product_type'];
				$sql = "DELETE FROM `jet_product` WHERE id='".$product_id."'";
				Data::sqlRecords($sql,null,'delete');	
				//delete product type from product section
				if(isset($result['product_type']))
				{
					fwrite($myfile, PHP_EOL."delete product type from product section".PHP_EOL);
					$delRes=Jetproductinfo::deleteProductType($product_type,$merchant_id);
					fwrite($myfile,PHP_EOL."delete product type response:".PHP_EOL.$delRes.PHP_EOL);
				}	
				$query="SELECT `product_count` FROM `insert_product` WHERE merchant_id='".$merchant_id."' LIMIT 0,1";
			    $insertData = Data::sqlRecords($query,'one','select');
			    if(isset($insertData['product_count']))
			    {
					if($insertData['product_count']>0)
						$count=$insertData['product_count']-1;
					$sqlresult1=false;
					$query1="UPDATE `insert_product` SET  product_count='".$count."' where merchant_id='".$merchant_id."'";
					Data::sqlRecords($sql,null,'update');								
				}									
			}
			else
			{
				$message1  = json_encode($result).' Either select result is false or deleted varients-'.$deleted_variants.' not equal to count varients-'.$count_variants;
				fwrite($myfile, $message1.PHP_EOL);
			}
			if(count($archiveSku)>0 && $isConfig)
			{
				//send request for archive
				$message=Jetproductinfo::archiveProductOnJet($archiveSku,$jetHelper,$merchant_id);
				fwrite($myfile,PHP_EOL."archive sku(s) response from jet:".PHP_EOL.$message.PHP_EOL);
			}
		}
        fclose($myfile);*/
	}
}