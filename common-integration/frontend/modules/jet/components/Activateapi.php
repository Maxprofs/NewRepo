<?php
namespace frontend\modules\jet\components;
use frontend\modules\jet\models\JetTestApi;
use yii\base\Component;
use yii\helpers\Url;

class Activateapi extends component
{
	public static function enabletestapi($jetHelper=[],$testConfig=[],$merchant_id='')
	{
		$sku='test_product';
		$fullfillmentnodeid=$testConfig['fulfillment_node'];
		$api_user=$testConfig['user'];
		$api_password=$testConfig['secret'];
		$merchant=$testConfig['merchant'];
		$handle=Data::createFile('jetConfiguration/'.$merchant_id,'w');
		$uploadSkuStatus = self::uploadSku($jetHelper,$fullfillmentnodeid,$merchant_id,$handle);
		/* echo "<hr><pre>";
		var_dump($uploadSkuStatus);
		die("<hr>upload"); */
		if($uploadSkuStatus=="uploaded")
		{
			$status = "";
			$responseSkuData = $jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id);
			$responseSkuDataArr = json_decode($responseSkuData,true);
			
			if($responseSkuDataArr && isset($responseSkuDataArr['http_code']) && $responseSkuDataArr['http_code']==200 && isset($responseSkuDataArr['inventory_by_fulfillment_node'],$responseSkuDataArr['inventory_by_fulfillment_node'][0]['quantity'],$responseSkuDataArr['price_by_fulfillment_node'],$responseSkuDataArr['price_by_fulfillment_node'][0]['fulfillment_node_price']))
			{
				fwrite($handle,"Merchant sku successfully send on jet".PHP_EOL);
				$order = [];
				$order['fulfillment_node']=$fullfillmentnodeid;
				$response='';
				$order['items']=array(array('sku'=>$sku,'order_quantity'=>0,'order_cancel_quantity'=>1));
				$response=$jetHelper->CPostRequest('/orders/generate/1',json_encode($order),$merchant_id);
				$response=json_decode($response,true);
				
				if(is_array($response) && isset($response['http_code']) && $response['http_code']==200 && isset($response['order_urls']) && count($response['order_urls']) > 0)
				{
					fwrite($handle,"Order successfully generated on jet".PHP_EOL);
					$responseData="";
					//$sku="";
					$qty=0;
					$cancel=0;
					$responsearr=[];
					$responseData=$jetHelper->CGetRequest($response['order_urls'][0],$merchant_id);
					$responsearr = json_decode($responseData,true);
					$data_ship = self::orderCancelData($responsearr);
					$data=$jetHelper->CPutRequest('/orders/'.$responsearr['merchant_order_id'].'/shipped',json_encode($data_ship),$merchant_id);
					$responseShip=json_decode($data,true);
					if($responseShip && isset($responseShip['http_code']) && $responseShip['http_code']==204)
					{
						fwrite($handle,"Order ship on jet".PHP_EOL);
					}
					else
					{
						fwrite($handle,"Order not ship on jet".PHP_EOL.json_encode($responseShip).PHP_EOL);
						$status['error']=json_encode($responseShip);
						return $status['error'];
					}
				}
				else
				{
					fwrite($handle,"Order not generated on jet:".PHP_EOL.json_encode($response).PHP_EOL);
					$status['error']=json_encode($response);
					return $status['error'];
				}
				//create order by api and acknowledge and shipped				
				$order=array();
				$order['fulfillment_node']=$fullfillmentnodeid;
				$order['items']=array(array('sku'=>$sku,'order_quantity'=>1,'order_cancel_quantity'=>0));
				$response="";
				$response=$jetHelper->CPostRequest('/orders/generate/1',json_encode($order),$merchant_id);
				$response=json_decode($response,true);
				
				//var_dump($response);echo "<hr>";
				if($response && isset($response['http_code']) && $response['http_code']==200 && isset($response['order_urls']) && count($response['order_urls']) > 0)
				{
					fwrite($handle,"Order successfully generated on jet".PHP_EOL);
					$responseData="";
					$responsearr=array();
					$responseData=$jetHelper->CGetRequest($response['order_urls'][0],$merchant_id);
					$responsearr = json_decode($responseData,true);
					
					if(count($responsearr) > 0 && isset($responsearr['merchant_order_id']))
					{
						$order_ack=array();
						$order_ack['acknowledgement_status'] = "accepted";
						$order_ack['order_items'][] = array(
								'order_item_acknowledgement_status'=>'fulfillable',
								'order_item_id' =>$responsearr['order_items'][0]['order_item_id']
						);
						$ackResponse="";
						$ackResponse=$jetHelper->CPutRequest('/orders/'.$responsearr['merchant_order_id'].'/acknowledge',json_encode($order_ack),$merchant_id);
						$ackData="";
						$ackData=json_decode($ackResponse,true);
						
						if(isset($ackData['http_code']) && $ackData['http_code']==204)
						{
							fwrite($handle,"Order successfully acknowledge on jet".PHP_EOL);
							//ship order data
							$data_ship = self::orderShipmentData($responsearr);
							$data=$jetHelper->CPutRequest('/orders/'.$responsearr['merchant_order_id'].'/shipped',json_encode($data_ship),$merchant_id);
							$data= json_decode($data,true);
						}
						else
						{
							fwrite($handle,"Order not acknowledge on jet".PHP_EOL.json_encode($ackData).PHP_EOL);
						}
					}
					else
					{
						fwrite($handle,"Order response not generated on jet:".PHP_EOL.json_encode($responsearr).PHP_EOL);
						$status['error']=json_encode($responsearr);
						return $status['error'];
					}
					
				}
				else
				{
					fwrite($handle,"Order not generated on jet:".PHP_EOL.json_encode($response).PHP_EOL);
					$status['error']=json_encode($response);
					return $status['error'];
				}
				//create return
				
				$order_id=$responsearr['merchant_order_id'];
				$return="";
				$returnarr=[];
				$return=$jetHelper->CGetRequest('/returns/generate/'.$order_id,$merchant_id);
				$returnarr = json_decode($return,true);
				
				if(isset($returnarr['url']) && count($returnarr)>0)
				{
					fwrite($handle,"Return generated on jet".PHP_EOL);
					$testurlarray=explode("/",$returnarr['url']);
					$returnid=$testurlarray[3];
					//echo $returnid;echo "<hr>";
					$resturnData="";
					$refund="";
					$item_id="";
					$shipping="";
					$principal=0;
					$resturnData = json_decode($jetHelper->CGetRequest($returnarr['url'],$merchant_id),true);
					//echo json_encode($data_ship);echo "return<hr>";
					$data_ship = self::orderReturnData($resturnData);
					$data=$jetHelper->CPutRequest('/returns/'.$returnid.'/complete',json_encode($data_ship),$merchant_id);
					/* echo "<hr><pre>";
					var_dump($data);
					die("<hr>get order return complete"); */
				}
				else
				{
					fwrite($handle,"Return not generated on jet".PHP_EOL.json_encode($returnarr).PHP_EOL);
					$status['error']=json_encode($returnarr);
					return $status['error'];
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
				
				if(self::isEnabled($jetHelper,$merchant_id)==true){
					fwrite($handle,"Jet api successfully enabled.".PHP_EOL);
					return "enabled";
				}
				else{
					fwrite($handle,"Jet api not enabled".PHP_EOL);
					return "enabled";//return "not enabled, please try again!";
				}
			}
			else
			{
				fwrite($handle,"Jet api seems to be down, please try after some time..".PHP_EOL);
				return $status['error']="Jet Api seems to be down, please try after some time..";
			}
		}
		else
		{
			return json_encode($uploadSkuStatus);
		}
	}
	public static function uploadSku($jetHelper,$fullfillmentnodeid,$merchant_id,$handle)
	{
		$status=[];
		$sku='test_product';
		$id="12345678";
		$productsku["product_title"]="Cedcommerce Test Product";
		$productsku["jet_browse_node_id"]=3000001;
		$productsku["brand"]="Cedcommerce";
		$upcinfo["standard_product_code"]="719236005030";
		$upcinfo["standard_product_code_type"]="UPC";
		$productsku["standard_product_codes"][]=$upcinfo;
		$productsku["multipack_quantity"]= 1;
		$productsku['product_description']="Test Product is used to enable product api";
		$productsku['main_image_url'] = Url::to('@web/images/Product_Lg_Type.jpg', true);
		$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku),json_encode($productsku),$merchant_id);
		$responseArray=json_decode($response,true);
		
		fwrite($handle,date('d-m-Y H:i:s').PHP_EOL);
		if(isset($responseArray['http_code']) && ($responseArray['http_code']==201 || $responseArray['http_code']==202))
		{
			fwrite($handle,"Sku data successfully send on jet".PHP_EOL);
			$price=array();
			$price['price']=38.00;
			$priceinfo['fulfillment_node_id']=$fullfillmentnodeid;
			$priceinfo['fulfillment_node_price']=38.00;
			$price['fulfillment_nodes'][]=$priceinfo;
			$responsePrice="";
			$responsePrice = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($price),$merchant_id);
			$responsePrice=json_decode($responsePrice,true);
			if(isset($responsePrice['http_code']) && ($responsePrice['http_code']==201 || $responsePrice['http_code']==202))
			{
				fwrite($handle,"Price data successfully send on jet".PHP_EOL);
				$inv=array();
				$inventory=array();
				$inv['fulfillment_node_id']=$fullfillmentnodeid;
				$qty=20;
				$inv['quantity']=$qty;
				$inventory['fulfillment_nodes'][]=$inv;
				$responseInventory="";
				$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory),$merchant_id);
				$responseInventory = json_decode($response,true);
				
				if(isset($responseInventory['http_code']) && ($responseInventory['http_code']==201 || $responseInventory['http_code']==202))
				{	
					fwrite($handle,"Inventory data successfully send on jet".PHP_EOL);
				}
				elseif(isset($responseInventory['errors']))
				{
					fwrite($handle,"Inventory data error :".PHP_EOL.json_encode($responseInventory['errors']).PHP_EOL);
					$status['error']=$responseInventory['errors'];
					return $status['error'];
				}
			}
			elseif(isset($responsePrice['errors']))
			{
				fwrite($handle,"price data error :".PHP_EOL.json_encode($responsePrice['errors']).PHP_EOL);
				$status['error']=$responsePrice['errors'];
				return $status['error']; 
			}
		}
		elseif(isset($responseArray['errors']))
		{
			fwrite($handle,"sku data error :".PHP_EOL.json_encode($responseArray['errors']).PHP_EOL);
			$status['error']=$responseArray['errors'];
			return $status['error'];
		}
		return "uploaded";
	}
	public static function orderCancelData($responsearr)
	{
		$item_sku=$responsearr['order_items'][0]['merchant_sku'];
		$carrier=$responsearr['order_detail']['request_shipping_carrier'];
		$qty=$responsearr['order_items'][0]['request_order_quantity'];
		$cancel=$responsearr['order_items'][0]['request_order_cancel_qty'];
		$deliver = $responsearr['order_detail']['request_delivery_by'];
		if($deliver){
			$time=explode('.', $deliver);
			$deliver=$time[0].'.0000000-07:00';
		}
		$t=time();
		$data_ship=array();
		$data_ship['shipments'][]=array (
			"alt_shipment_id"=> (string)time(),
			//'shipment_tracking_number'=>time().'5454',
			'response_shipment_date'=>$deliver,
			'response_shipment_method'=>'',
			'expected_delivery_date'=>$deliver,
			'ship_from_zip_code'=>'84047',
			'carrier_pick_up_date'=>$deliver,
			//'carrier'=>$carrier,
			'shipment_items'=>array(
				array(
					//'shipment_item_id'=>time().'-123',
					'merchant_sku'=>$item_sku,
					'response_shipment_sku_quantity'=>$qty,
					'response_shipment_cancel_qty'=>$cancel,
					'RMA_number'=>'abcedef',
					'days_to_return'=>30,
					'return_location'=>array('address1'=>'6909 South State Street','address2'=>'Suite C',
							'city'=>'Midvale','state'=>'UT',
							'zip_code'=>'84047'
					)
				)
			)
		);
		return $data_ship;
	}
	public static function orderShipmentData($responsearr)
	{
		$deliver="";
		$request_shipping_carrier="";
		$deliver = $responsearr['order_detail']['request_delivery_by'];
		$request_shipping_carrier= $responsearr['order_detail']['request_shipping_carrier'];
		if($deliver)
		{
			$time=explode('.', $deliver);
			$deliver=$time[0].'.0000000-07:00';
		}
		$shipment_arr=array();
		$shipment_arr[]= array(
				//'shipment_item_id'=>time().'-123',
				'merchant_sku'=>'test_product',
				'response_shipment_sku_quantity'=>1,
				'response_shipment_cancel_qty'=>0,
				'RMA_number'=>"abcdef",
				'days_to_return'=>30,
				'return_location'=>array('address1'=>'6909 South State Street',
                                                        'address2'=>'Suite C',
                                                        'city'=>'Midvale',
                                                        'state'=>'UT','zip_code'=>'84047'
                                           )
		);
		$data_ship=array();
		$data_ship['shipments'][]=array	(
				"alt_shipment_id"=> time()."96",
				'shipment_tracking_number'=>time().'5454',
				'response_shipment_date'=>$deliver,
				'response_shipment_method'=>'',
				'expected_delivery_date'=>$deliver,
				'ship_from_zip_code'=>"12345",
				'carrier_pick_up_date'=>$deliver,
				'carrier'=>$request_shipping_carrier,
				'shipment_items'=>$shipment_arr
		);
		return $data_ship;
	}
	public static function orderReturnData($resturnData)
	{
		$order_id=$resturnData['merchant_order_id'];
		$item_id=$resturnData['return_merchant_SKUs'][0]['order_item_id'];
		$refund=$resturnData['return_merchant_SKUs'][0]['return_quantity'];
		$refund=$resturnData['return_merchant_SKUs'][0]['return_quantity'];
		$feedback="item damaged";
		$shipping=(int)$resturnData['return_merchant_SKUs'][0]['requested_refund_amount']['shipping_cost'];
		$s_tax=0;
		$tax=0;
		$principal=(int)$resturnData['return_merchant_SKUs'][0]['requested_refund_amount']['principal'];
		$data_ship=array();
		$data_ship['merchant_order_id']=$order_id;
		$data_ship['items'][]=array(
				'order_item_id'=>$item_id,
				'total_quantity_returned'=>$refund,
				'order_return_refund_qty'=>$refund,
				'return_refund_feedback'=>$feedback,
				'refund_amount'=>array(
						'principal'=>$principal,
						'tax'=>$tax,
						'shipping_cost'=>$shipping,
						'shipping_tax'=>$s_tax
				)
			);
		$data_ship['agree_to_return_charge']=true;
		return $data_ship;
	}
	public static function isEnabled($jetHelper,$merchant_id)
	{
		$status = "";
		$response = $jetHelper->CGetRequest('/portal/merchantsetupstatus/',$merchant_id,$status);
		$enableApiResponse=json_decode($response,true);
		/* echo "<hr><pre>";
		print_r($enableApiResponse);
		die("<hr>T156est"); */
		$enableParams=['IsMerchantSkuUploaded','IsInventoryUploaded','IsPriceUploaded','IsOrderCreated','IsOrderAcknowledged','IsOrderShipped','IsOrderCanceled','IsReturnComplete'];
		$notEnabled=false;
		if(is_array($enableApiResponse) && count($enableApiResponse)>0)
		{
			foreach ($enableApiResponse as $key => $value) 
			{
				if(in_array($key, $enableParams) && $value==false && $key!="Message")
				{
					$notEnabled=true;
					
					break;
				}	
			}
		}
				
		if($notEnabled)		
			return false;
		elseif (!$notEnabled && ($status!=200 && ($status==401 || $status==403)) )  // if API is not working 
			return false;
		
		return true;
	}
}