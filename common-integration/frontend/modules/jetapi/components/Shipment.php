<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;


use app\models\AppStatus;
use app\models\JetConfiguration;
use app\models\JetOrderDetail;
use app\models\JetOrderImportError;
use app\models\JetProduct;
use app\models\JetProductVariants;
use app\models\JetShipmentDetails;
use common\models\JetExtensionDetail;
use common\models\User;
use frontend\components\Jetapimerchant;
use frontend\components\Jetproductinfo;
use frontend\components\Sendmail;
use frontend\components\ShopifyClientHelper;
use frontend\components\Shopifyinfo;
use frontend\models\JetConfig;
use yii\web\Controller;

use app\models\JetOrderDetailSearch;
use frontend\modules\jetapi\components\Jetapi;

// use frontend\components\Jetapi;
use frontend\components\Data;

class Shipment extends Controller{
	
	public function getShipment($Output)
	{		
		$merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
		define('MERCHANT',$merchant_id);

		$products=json_decode($Output['shopify_order_id'],true);

        $Output['shopify_order_id']=$products;
        $data = $Output;

		date_default_timezone_set("Asia/Kolkata");
		$query="";	$orderAckCollection=array();
		$query="select `jet_order_detail`.`id`,`jet_order_detail`.`merchant_id`,`jet_order_detail`.`merchant_order_id`,shopify_order_id,username,auth_key,api_user,api_password,fullfilment_node_id from `jet_order_detail` inner join `user` on jet_order_detail.merchant_id=user.id inner join `jet_configuration` on jet_configuration.merchant_id=user.id where jet_order_detail.status='acknowledged' and jet_order_detail.shopify_order_id ='".$data['shopify_order_id']."' ";
		$orderAckCollection=Data::sqlRecords($query,"all","select");
		unset($query);

			if (!empty($orderAckCollection) && is_array($orderAckCollection))
				{
					foreach ($orderAckCollection as $key => $value) 
					{
						if (!empty($value) && is_array($value) && isset($value['shopify_order_id'])) 
						{

							$query1 = $shopify_order_id = $shopname = $token = $Jet_api_user = $Jet_api_password  = $errorMessage ="";
							$sc = $jetHelper = $shipmentResponse = $client_address_details = $address_detail_array =  $shipment_items = $shopify_shipment_data = array();

							$order_row_id=$value['id'];
							$merchant_id=$value['merchant_id'];
							$shopify_order_id=$value['shopify_order_id'];
							$shopname=$value['username'];
							$token=$value['auth_key'];
							$merchant_order_id=$value['merchant_order_id'];

							$Jet_api_host="https://merchant-api.jet.com/api";
							$Jet_api_user=$value['api_user'];
							$Jet_api_password=$value['api_password'];
							
							try{
								if (!file_exists(Yii::getAlias('@webroot').'/var/shipment-log-cron/'.$shopname.'/'.date('d-m-Y'))){
									mkdir(Yii::getAlias('@webroot').'/var/shipment-log-cron/'.$shopname.'/'.date('d-m-Y'),0775, true);
								}
								$filename1=Yii::getAlias('@webroot').'/var/shipment-log-cron/'.$shopname.'/'.date('d-m-Y').'/'.$merchant_order_id.'-shiplog.txt';
								$file1=fopen($filename1,'a+');
									
								$errorMessage.=$shopname."[".date('d-m-Y H:i:s')."]\n";
								$query1="SELECT `data`,`value` FROM `jet_config` WHERE `merchant_id`='".$merchant_id."' "; 
								$client_address_details=Data::sqlRecords($query1,"all","select");
														
								if (!empty($client_address_details)) 
								{
									foreach ($client_address_details  as $key3 => $value3) 
									{
										$address_detail_array[$value3['data']]=$value3['value'];
									} 
									$zip_code =	$address_detail_array['zipcode'];
								}
								unset($query1);unset($address_detail_array);unset($client_address_details);

								$jetHelper = new Jetapimerchant($Jet_api_host,$Jet_api_user,$Jet_api_password);
								
								$jetHelper1 =new Jetapi($Jet_api_host,$Jet_api_user,$Jet_api_password);// api for checking current order status on jet
								
								$errorMessage="Order shipment started:\n";

								$sc = new ShopifyClientHelper($shopname, $token, PUBLIC_KEY,PRIVATE_KEY);
								$shipmentResponse= $sc->call('GET', '/admin/orders/'.$shopify_order_id.'.json?fields=fulfillments');

								if (!empty($shipmentResponse['fulfillments']) && is_array($shipmentResponse))
								{
									$errorMessage="shipment not created from shopify:\n";
									foreach ($shipmentResponse as $key1 => $value1)
									{
										if (!empty($value1) && is_array($value1))
										{
											foreach ($value1 as $key2 => $value2)
											{
												$tracking_number=isset($value2['tracking_number']) ? $value2['tracking_number'] : "";
												$tracking_company=isset($value2['tracking_company']) ? $value2['tracking_company'] : "";
												$updated_at = isset($value2['updated_at']) ? $value2['updated_at'] : "";
												if ( ($tracking_number == null) || ($tracking_number==""))
												{
													return ['success'=>'false','message'=>'Blank trackin number']; //exit(0) ;
												}
												if (isset($value2['line_items']) && is_array($value2['line_items']))
												{
													$errorMessage="shipment created from shopify:\n";
													fwrite($file1, $errorMessage);
													foreach ($value2['line_items'] as $key_data => $value_data)
													{
														$shipment_id=isset($value_data['id']) ? $value_data['id'] : "";
														$shipment_items[]=array(
																"shipment_item_id" => $value_data['id'].'-'.$value_data['sku'],
																"alt_shipment_item_id" =>''. $value_data['id'],
																"merchant_sku"=> $value_data['sku'],
																"response_shipment_sku_quantity" => (int)$value_data['quantity'],
														);
														$shopify_shipment_data[]=implode(',',array(0=>$value_data['sku'],1=>$value_data['quantity'],2=>$value_data['fulfillment_status']));
													}
												}
											}										
										}
									}	
								}else{
									return ['error'=>true,'message'=>'Order not shipped from Shopify, please first shipment in shopify'];
								} // shopify shipment response closed	

								$offset_end="";
								$offset_end = self::getStandardOffsetUTC();
								if(empty($offset_end) || trim($offset_end)=='')
									$offset = '.0000000-00:00';
								else
									$offset = '.0000000'.trim($offset_end);
								$dt = new \DateTime($updated_at);
								$expected_delivery_date = date("Y-m-d\TH:i:s", time()).$offset;
								$data_ship['shipments'][]=array	(
										'shipment_tracking_number'=>$tracking_number,
										'response_shipment_date'=>$expected_delivery_date,
										'response_shipment_method'=>$tracking_company,
										'expected_delivery_date'=>$expected_delivery_date,
										'ship_from_zip_code'=>$zip_code ,
										'carrier_pick_up_date'=>$expected_delivery_date,
										'carrier'=>$tracking_company,
										'shipment_items'=>$shipment_items
								);
								
								if(!empty($data_ship))
								{							
									$errorMessage="shipment data send on jet.com:\n".json_encode($data_ship)."\n";
									fwrite($file1, $errorMessage);
									$jetdata=array();
									$jetdata=$jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped',json_encode($data_ship),$merchant_id);
									$responseArray=array();
									$responseArray=json_decode($jetdata,true);

									if(!isset($responseArray['errors']))
									{
										$errorMessage="shipment data send to jet \n";
									
										$resultResponse=array();
										$resultResponse = $jetHelper1->CGetRequest('/orders/withoutShipmentDetail/'.$merchant_order_id);
										
										$resultObject = json_decode($resultResponse,true);
										$errorMessage.="shipment response from jet \n".$resultObject;

										if ($resultObject['status']=='complete')
										{
											$modelShip=new JetShipmentDetails();
											$result=array();
											$result=$modelShip->find()->where(['merchant_id' => $merchant_id,'jet_order_id'=>$merchant_order_id])->one();
											if(!$result)
											{
												$modelShip->merchant_id=$merchant_id;
												$modelShip->order_increment_id=$order_row_id;
												$modelShip->jet_order_id=$merchant_order_id;
												$modelShip->shopify_order_id=$shopify_order_id;
												$modelShip->shopify_shipment_id=$shipment_id;
												$modelShip->order_items=implode(',',$shopify_shipment_data);
												$modelShip->ship_to_date=date("Y-m-d H:i:s");
												$modelShip->carrier_pick_up_date=date("Y-m-d H:i:s");
												$modelShip->expected_delivery_date=date("Y-m-d H:i:s");
												$modelShip->tracking_number=$tracking_number;
												$modelShip->shipping_carrier=$tracking_company;
												$modelShip->save(false);
											}
											Data::sqlRecords("UPDATE  `jet_order_detail` SET `status`='complete' WHERE `merchant_id`='".$merchant_id."' AND `shopify_order_id`='".$shopify_order_id."' ","","update");
											
											$errorMessage ="shipment created\n";
											return ['success'=>true,'message'=>$errorMessage];
										}else{
											$errorMessage="shipment details sent to jet, but not completed \n";
											return ['error'=>true,'message'=>'shipment details sent to jet, but not completed'];
											Sendmail::orderShipmentError($shopname,$merchant_order_id,$merchant_id);
										}									
										
									}else{

										$errorMessage="shipment not completed\n".json_encode($responseArray['errors']);

										return ['error'=>true,'message'=>$errorMessage];
									}
								}// Sending shipmentDataArray to jet close
							}catch(Exception $e)
							{
								$errorMessage="shipment data send on jet.com:\n".$e->getMessage()."\n";
								return ['error'=>true,'message'=>$errorMessage];

							}
						// echo $shopname."<hr>".$merchant_order_id."<hr>";	
						//die("test order");	
						} // Check shopify_order_id exist
					} // foreach  close	 (All data collection) 
				}	// if exist (collection) close	
				//die("test order");	
				// Check shopify_order_id exist
			 // foreach  close	 (All data collection) 
			else{
				return ['error'=>true,'message'=>'This order is already Shipped'];
			}
			// if exist (collection) close	
	}// Get Shipment function close
	
	public function getStandardOffsetUTC()
    {
    	$timezone = date_default_timezone_get();
    	if($timezone == 'UTC') {
    		return '';
    	} else {
    		$timezone = new \DateTimeZone($timezone);
    		$transitions = array_slice($timezone->getTransitions(), -3, null, true);
    
    		foreach (array_reverse($transitions, true) as $transition)
    		{
    			if ($transition['isdst'] == 1)
    			{
    				continue;
    			}
    			return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
    		}
    
    		return false;
    	}
    }

}