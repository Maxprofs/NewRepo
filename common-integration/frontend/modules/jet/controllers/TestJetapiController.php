<?php 
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Activateapi;
use frontend\modules\jet\components\Jetapitest;

use Yii;
use yii\helpers\Url;

class TestJetapiController extends JetmainController
{
	public function actionTestapi()
	{
		if (Yii::$app->user->isGuest) {
			return $this->goHome();
		}
		$merchant_id=MERCHANT_ID;
		if($merchant_id==14)
		{
			$fullfillmentnodeid=FULLFILMENT_NODE_ID;
			$responseSatuses=[];
			$jetHelper = new Jetapitest("https://merchant-api.jet.com/api",API_USER,API_PASSWORD);

			//check token api
			$tokenResponse=$jetHelper->JrequestTokenCurl();
			$responseSatuses['token']=json_encode($tokenResponse);
			
			//check sku api
			$sku='new_product';

			$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku),json_encode(self::testSku()),$merchant_id);
			$responseSatuses['sku']=$response;

			//check sku price api
			$price['price']=38.00;
			$price['fulfillment_nodes'][]=['fulfillment_node_id'=>$fullfillmentnodeid,'fulfillment_node_price'=>38.00];
			$responsePrice = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($price),$merchant_id);
			$responseSatuses['price']=$responsePrice;
			
			//check sku inventory api
			$response='';
			$inventory['fulfillment_nodes'][]=['fulfillment_node_id'=>$fullfillmentnodeid,'quantity'=>20];
			$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/Inventory',json_encode($inventory),$merchant_id);
			$responseSatuses['inventory'] = $response;

			//get sku api
			$responseSkuData = $jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id);
			$responseSatuses['skuData']=$responseSkuData;

			//add product variation
			
			$responseOptions['children_skus']=['test_product'];
			$responseOptions['variation_refinements']=[50];
			$responseOptions['relationship']='Variation';
			$response=$jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/variation',json_encode($responseOptions),$merchant_id);
			$responseSatuses['variantion']=$response;

			//check sku archive api
			$response='';
			$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode(["is_archived"=> true]),$merchant_id);
			$responseSatuses['archive']=$response;

			//check sku unarchive api
			$response='';
			$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode(["is_archived"=> false]),$merchant_id);
			$responseSatuses['unarchive']=$response;
			
			$response='';
			$inventory['fulfillment_nodes'][]=['fulfillment_node_id'=>$fullfillmentnodeid,'quantity'=>20];
			$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/Inventory',json_encode($inventory),$merchant_id);
			$responseSatuses['inventory']=$response;

			//add shipping exception 
			$shipping_carr=['shipping_method'=>"FedEx 2 Day",'shipping_exception_type'=>"restricted"];
            $shipping['fulfillment_nodes'][]=array(
                'fulfillment_node_id'=>FULLFILMENT_NODE_ID,
                'shipping_exceptions'=>array(
                       $shipping_carr, 
                    )
                );
            unset($shipping_carr);
            $response=$jetHelper->CPutRequest("/merchant-skus/".rawurlencode($sku)."/shippingexception",json_encode($shipping),$merchant_id);
            $responseSatuses['shippingexception']=$response;

			//generate order by api
			if(isset($_GET['order']))
			{
				$order['fulfillment_node']=$fullfillmentnodeid;
				$order['items']=array(array('sku'=>$sku,'order_quantity'=>1,'order_cancel_quantity'=>0));
				$response=$jetHelper->CPostRequest('/orders/generate/1',json_encode($order),$merchant_id);
				$responseSatuses['order']=$response;
				$response=json_decode($response,true);
				if(isset($response['order_urls']) && count($response['order_urls']) > 0)
				{
					$responsearr=array();
					$responseData=$jetHelper->CGetRequest($response['order_urls'][0],$merchant_id);
					$responsearr = json_decode($responseData,true);
					$responseSatuses['get_order']=$responseData;
					if(count($responsearr) > 0 && isset($responsearr['merchant_order_id'])){
						$order_ack=array();
						$order_ack['acknowledgement_status'] = "accepted";
						$order_ack['order_items'][] = array(
								'order_item_acknowledgement_status'=>'fulfillable',
								'order_item_id' =>$responsearr['order_items'][0]['order_item_id']
						);
						$ackResponse="";
						$ackResponse=$jetHelper->CPutRequest('/orders/'.$responsearr['merchant_order_id'].'/acknowledge',json_encode($order_ack),$merchant_id);
						$responseSatuses['order_ack']=$ackResponse;
						$ackData=json_decode($ackResponse,true);
						if(isset($ackData['http_code']) && $ackData['http_code']==204)
						{
							//ship order data
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
									'shipment_item_id'=>time().'-123',
									'merchant_sku'=>$sku,
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
							//var_Dump($data_ship);echo "shipped<hr>";
							$data=$jetHelper->CPutRequest('/orders/'.$responsearr['merchant_order_id'].'/shipped',json_encode($data_ship),$merchant_id);
							$responseSatuses['order_ship']=$data;
							$data= json_decode($data,true);
							//var_Dump($data);echo "<hr>";
						}
						//put return request
						$order_id=$responsearr['merchant_order_id'];
						$return="";
						$returnarr=array();
						$return=$jetHelper->CGetRequest('/returns/generate/'.$order_id,$merchant_id);
						$responseSatuses['get_return']=$return;
						$returnarr = json_decode($return,true);
						if(isset($returnarr['url']) && count($returnarr)>0)
						{
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
							$data_ship = Activateapi::orderReturnData($resturnData);
							$data=$jetHelper->CPutRequest('/returns/'.$returnid.'/complete',json_encode($data_ship),$merchant_id);
							$responseSatuses['complete_return']=$data;
						}
					}
				}
			}

			//var_dump($responseSatuses);die("vcxbcb");
			return $this->render('viewapistatus', [
            	'response' => $responseSatuses,
        	]);
		}
		else
		{
			$msg='Please login with ced-jet account to get all api status';
			$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/site/index',302);
		}
	}
	public static function testSku(){
		$id="12345678";
		$productsku["product_title"]="Cedcommerce Test Product";
		$productsku["jet_browse_node_id"]=4000188;
		$productsku["brand"]="Cedcommerce";
		$upcinfo["standard_product_code"] = "719236005030";
		$upcinfo["standard_product_code_type"] = "UPC";
		$productsku["standard_product_codes"][] = $upcinfo;
		$productsku["multipack_quantity"] = 1;
		$description="Test Product is used to enable product api";
		$productsku['product_description'] = $description;
		$productsku['main_image_url'] = Url::to('@web/images/Product_Lg_Type.jpg', true);
		$productsku['attributes_node_specific'][] = ["attribute_id"=>50,"attribute_value"=>"Small"];
		return $productsku;
	}
}