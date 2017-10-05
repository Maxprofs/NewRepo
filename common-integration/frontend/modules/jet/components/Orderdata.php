<?php 
namespace frontend\modules\jet\components;
use Yii;
use yii\base\Component;
use yii\helpers\Url;
use frontend\modules\jet\components\Sendmail;
use frontend\modules\jet\controllers\JetcronController as Shopifyjet;
use frontend\modules\jet\components\Data;

class Orderdata extends component
{
	// public static function createOrder($jetHelper=[],$merchant_id=null,&$error_array)
	/*
	* Fetch Ready state Orders from jet to app and send acknowledgement response to jet 
	*/
	public static function createOrder($jetHelper=[],$merchant_id=null,$merchantEmail=null,&$mailData=[],&$error_array)	
  	{
	    $status=false;    
	    $orderdata=$jetHelper->CGetRequest('/orders/ready',$merchant_id,$status); 	    
	    $responseOrderData = [];
	    /*($status!=200)
	    {
	      $responseOrderData['error'] = "Order api not working. Status code".$status;
	      return $responseOrderData;
	    }*/
	    $response  = json_decode($orderdata,True);
	    
	    $countOrder=0;
	    if(isset($response['order_urls']) && count($response['order_urls']) > 0)
	    {
	      $message = $merchantEmail = "";
	      foreach($response['order_urls'] as $jetorderurl)
	      {
	        $result = "";
	        $result = $jetHelper->CGetRequest($jetorderurl,$merchant_id);
	        $result = json_decode($result,true);
	        $path=Yii::getAlias('@webroot').'/var/jet/order/'.$merchant_id.'/'.$result['reference_order_id'];
	        if(!file_exists($path))
	        {
	          mkdir($path,0775, true);
	        }
	        $handle=fopen($path.'/orderfetch.log','w');
	        if(isset($result['merchant_order_id']))
	        {                
	          $query="SELECT `merchant_order_id` FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' AND merchant_order_id='".$result['merchant_order_id']."' LIMIT 0,1";
	          $resultdata = Data::sqlRecords($query,'one','select');
	          if(empty($resultdata))
	          {
	            fwrite($handle,PHP_EOL."Order data for acknowledge : ".date('d-m-Y H:i:s').PHP_EOL.json_encode($result).PHP_EOL);
	            $order_ack = $resultdata = $jetExten = $OrderItemData = [];

	            $order_ack['acknowledgement_status'] = "accepted";
	            $merchantOrderid = $result['merchant_order_id'];
	            $sql_email = 'SELECT `email` FROM jet_shop_details where merchant_id='.$merchant_id;
	            $jetExten = Data::sqlRecords($sql_email,'one','select');
	            $merchantEmail = $jetExten['email'];
	            
	            $mailData[$result['merchant_order_id']] = [      
	              'sender' => 'shopify@cedcommerce.com',
	              'reciever' => $merchantEmail,
	              'email' => $merchantEmail,
	              'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com,moattarraza@cedcoss.com,arpansrivastava@cedcoss.com',
	              'reference_order_id' => $result['reference_order_id'],
	              'merchant_order_id' => $result['merchant_order_id'],
	            ];

	            
	            $autoReject = false;
	            $i = $ikey = 0;
	            
	            foreach ($result['order_items'] as $key=>$value)
	            {
	              $isConnectorSku=false;
	              $query="SELECT `qty` FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND sku='".addslashes(trim($value['merchant_sku']))."' LIMIT 0,1";
	              $collection = Data::sqlRecords($query,"one","select");
	              if($collection=="")
	              {
	                $query="SELECT option_qty as qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND option_sku='".addslashes(trim($value['merchant_sku']))."' LIMIT 0,1";
	                $collection = Data::sqlRecords($query,"one","select");  
	                if($collection=="")
	                {
	                  //check jet-connector skus
	                  $findSku = explode('-',$value['merchant_sku']);
	                  if(isset($findSku[0],$findSku[1]) && is_numeric($findSku[0]))
	                  {
	                    $query="SELECT qty FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `id`='{$findSku[0]}' AND `variant_id`='{$findSku[1]}' LIMIT 0,1";
	                    $collection = Data::sqlRecords($query,"one","select");  
	                    if($collection=="")
	                    {
	                      $query="SELECT option_qty as qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `product_id`='{$findSku[0]}' AND `option_id`='{$findSku[1]}' LIMIT 0,1";
	                      $collection = Data::sqlRecords($query,"one","select"); 
	                    } 
	                  }
	                }
	              }
	              if($collection=="")
                  {
	                $error_array[]=array(
                        'merchant_order_id'=>$result['merchant_order_id'],
                        'reference_order_id'=>$result['reference_order_id'],
                        'merchant_id'=>$merchant_id,
                        'reason'=>'Product sku: '.$value['merchant_sku'].' not available in shopify',
                        'created_at'=>date("d-m-Y H:i:s"),
                        'code'=> 'sku_not_available',
                      );
	                $autoReject=true;
	                continue;
                  }  
	              elseif(isset($collection['qty']) && $value['request_order_quantity']>$collection['qty'])
                  {	                  
	                  $error_array[]=array(
	                    'merchant_order_id'=>$result['merchant_order_id'],
	                    'reference_order_id'=>$result['reference_order_id'],
	                    'merchant_id'=>$merchant_id,
	                    'reason'=>'Requested Order quantity is not available for product sku: '.$value['merchant_sku'],
	                    'created_at'=>date("d-m-Y H:i:s"),
	                    'code'=> 'qty_not_available',
	                  );
	                  $autoReject=true;
	                  continue;
                  }         
	              //send acknowledge request if auto-acknowledge order
	              $OrderItemData['sku'][]=addslashes(trim($value['merchant_sku']));
	              $OrderItemData['order_item_id'][]=$value['order_item_id'];
	              $order_ack['order_items'][] = array(
	                'order_item_acknowledgement_status'=>'fulfillable',
	                'order_item_id' =>$value['order_item_id']
	              );                  
	            }                                    
	            if($autoReject)
	            {
	              fwrite($handle,PHP_EOL."Error".json_encode($error_array).PHP_EOL);
	              $obj = new Shopifyjet(Yii::$app->controller->id,'');
	              $obj->actionRemovefailedorders(true);
	              continue;                                        
	            }
	            if(isset($order_ack['order_items']) && count($order_ack['order_items'])>0)
	            {                                    
	              $ackData=[];
	              $ackResponse="";
	              $status=false;
	              $ackResponse=$jetHelper->CPutRequest('/orders/'.$result['merchant_order_id'].'/acknowledge',json_encode($order_ack),$merchant_id,$status);
	              $ackData=json_decode($ackResponse,true);   
	              fwrite($handle,PHP_EOL."Order acknowledged response".PHP_EOL.$ackResponse.PHP_EOL);                  
	              if($status==204 && count($ackData)==0)
	              {
	                $status='acknowledged';
	                fwrite($handle,PHP_EOL."Order created on app and Ack".PHP_EOL);
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
	                              "'.addslashes(json_encode($result)).'",
	                              "'.$result['reference_order_id'].'",
	                              "'.$status.'",
	                              "'.implode(',',$OrderItemData['sku']).'",
	                              "'.implode(',',$OrderItemData['order_item_id']).'",
	                              "'.$result['order_detail']['request_delivery_by'].'"
	                            )';
	                fwrite($handle,PHP_EOL."Order insert query".PHP_EOL.$query);
	                Data::sqlRecords($query,null,'insert');
	                //Sending Mail to clients , when order placed
	                if ($merchantEmail) 
	                {
	                  Sendmail::ordermail($merchantEmail,$result['reference_order_id'],$result['merchant_order_id'],implode(',',$OrderItemData['sku']),$merchant_id);      
	                }                                                                                     
	                // start sending notification to client , when order placed(for IOS and Android app)
	                $url = Yii::getAlias('@webjeturl')."/jetapi/jetnotification/order-place";
	                $curtRequestParams =['order_id'=> $result['reference_order_id'],'merchant_id'=>$merchant_id];
	                Data::curlrequest($url,$curtRequestParams,$merchant_id);
	                // end sending notification to client , when order placed(for IOS and Android app) 

	                $countOrder++;
	              }
	              else
	              {
	                $message = "Order Not Acknowlegde ".json_encode($ackData['errors'])."\n";
	                fwrite($handle,$message);
	                $error_array[]=array(
	                        'merchant_order_id'=>$result['merchant_order_id'],
	                        'reference_order_id'=>$result['reference_order_id'],
	                        'merchant_id'=>$merchant_id,
	                        'reason'=>json_encode($ackData['errors']),
	                        'created_at'=>date("d-m-Y H:i:s"),
	                        'code'=> 'order_not_acknowledged',
	                );
	                continue;
	              }
	            }
	            else
	            {
	              fwrite($handle,"Order Items not available for acknowledgement".PHP_EOL);
	              continue;
	            }
	          }
	          else
	          {
	            fwrite($handle,"Order already availble in app".PHP_EOL);
	            continue;
	          } 
	          fclose($handle);                
	        }
	        else
	        {
	          continue;
	        } 
	      }
	      unset($order_ack,$ackData,$itemArray,$OrderItemData,$collection,$collectionOption,$result,$message);      
	    }
	    
	    if($countOrder>0)
	    {
	      $responseOrderData["success"]=$countOrder;
	      return $responseOrderData;
	    }
	    unset($response);	     
	    return false;
	}

	/*
	*  Create Fetched orders to shopify store 
	*/
	public static function syncJetOrder($sc=[],$configSetting=[],$result=[],$merchant_id=null,&$countOrder)
	{				
		  $path=Yii::getAlias('@webroot').'/var/jet/order/'.$merchant_id.'/'.$result['reference_order_id'];
	      if(!file_exists($path)){
	        mkdir($path,0775, true);
	      }
	      $fulfillment_variant_ids = [];
          $handle=fopen($path.'/orderSync.log','w');
          $ikey = 0;                                      
          $OrderTotal = $shippingTax = $itemTax = 0.00;
          $autoReject = false;
                    
          if(is_array($result) && count($result)>0)
          {
            fwrite($handle,PHP_EOL."Order data preparing".PHP_EOL);
            foreach ($result['order_items'] as $key=>$value)
            {
              $collection = $findSku = [];
              $query="SELECT id,sku,vendor,variant_id,qty,title FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND sku='".addslashes(trim($value['merchant_sku']))."'";                
              fwrite($handle,PHP_EOL."QUERY TO SEARCH SIMPLE PRODUCT (ACTUAL)===>".PHP_EOL.$query.PHP_EOL);
              $collection = Data::sqlRecords($query,'one','select');
              $findSku = explode('-',addslashes(trim($value['merchant_sku'])));
              if(empty($collection))
              {
                $collectionOption = [];
                $query="SELECT option_id,product_id,option_sku,vendor,option_qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND option_sku='".addslashes(trim($value['merchant_sku']))."'";
                $collectionOption = Data::sqlRecords($query,'one','select');
                if(empty($collectionOption))
                {                                       
                  if(!empty($findSku) && isset($findSku[1]) ) 
                  {
                    $collection1 = [];
                    $query="SELECT id,sku,vendor,variant_id,qty,title FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `id` = '{$findSku[0]}' AND `variant_id`='{$findSku[1]}' ";
                    $collection1 = Data::sqlRecords($query,'one','select'); 
                    if (empty($collection1)) 
                    {
                      $collectionOption1 = [];
                      $query="SELECT option_id,product_id,option_sku,vendor,option_qty FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `product_id`='{$findSku[0]}' AND `option_id`='{$findSku[1]}' ";
                      $collectionOption1 = Data::sqlRecords($query,'one','select');
                      if(empty($collectionOption1))
                      {                                       
                        continue;
                      }
                      else
                      {
                        $itemArray[$ikey]['product_id']=$collectionOption1['product_id'];
                        $itemArray[$ikey]['title']=$value['product_title'];
                        $itemArray[$ikey]['variant_id']=$collectionOption1['option_id'];
                        $itemArray[$ikey]['vendor']=$collectionOption1['vendor'];
                        $itemArray[$ikey]['sku']=$collectionOption1['option_sku'];
                      }
                    }                                       
                    else
                    {
                      $itemArray[$ikey]['product_id']=$collection1['id'];
                      $itemArray[$ikey]['title']=$collection1['title'];
                      $itemArray[$ikey]['variant_id']=$collection1['variant_id'];
                      $itemArray[$ikey]['vendor']=$collection1['vendor'];
                      $itemArray[$ikey]['sku']=$collection1['sku'];
                    }
                  }
                  else
                  {
                    continue;
                  } 
                }
                else
                {
                  $itemArray[$ikey]['product_id']=$collectionOption['product_id'];
                  $itemArray[$ikey]['title']=$value['product_title'];
                  $itemArray[$ikey]['variant_id']=$collectionOption['option_id'];
                  $itemArray[$ikey]['vendor']=$collectionOption['vendor'];
                  $itemArray[$ikey]['sku']=$collectionOption['option_sku'];
                }                                                                      
              }                                
              else
              {
                $itemArray[$ikey]['product_id']=$collection['id'];
                $itemArray[$ikey]['title']=$collection['title'];
                $itemArray[$ikey]['variant_id']=$collection['variant_id'];
                $itemArray[$ikey]['vendor']=$collection['vendor'];
                $itemArray[$ikey]['sku']=$collection['sku'];
              }
              $qty = $Totalprice = 0;
              $qty=$value['request_order_quantity'];
              $itemArray[$ikey]['id']=$value['order_item_id'];
              $itemArray[$ikey]['price']=$value['item_price']['base_price']+$value['item_price']['item_shipping_cost'];
              $shippingTax+=$value['item_price']['item_shipping_tax'];
              $itemTax=$value['item_price']['item_tax'];
              $itemArray[$ikey]['quantity']=$qty;
              $itemArray[$ikey]['requires_shipping']=true;
               
              fwrite($handle,PHP_EOL."ORDERS RAW DATA FOR ORDER CREATION".PHP_EOL.json_encode($itemArray).PHP_EOL);
              //fulfillment_service for product 
              $prodVariants=$sc->call('GET',"/admin/variants/".$itemArray[$ikey]['variant_id'].".json");
              if(isset($prodVariants['fulfillment_service']) && ($prodVariants['fulfillment_service']=='amazon_marketplace_web' || $prodVariants['inventory_management']=='amazon_marketplace_web'))
              {
                $fulfillment_variant_ids[]=$itemArray[$ikey]['variant_id'];
                $itemArray[$ikey]['fulfillment_service']='amazon_marketplace_web'; 
              }
              $ikey++;
            }
            $customer_Info=[];
            $first_name = $last_name = "";
            if(isset($result['shipping_to']['recipient']['name']))
            {
              $customer_Info = $result['shipping_to']['recipient']['name'];
            } 
            else 
            {
              $customer_Info=$result['buyer']['name'];
            }
            $customer_Info = preg_replace('/\s+/', ' ', $customer_Info);
            $customer_Info = explode(" ", $customer_Info);
            $first_name=$customer_Info[0];
            if(isset($customer_Info[1]) && $customer_Info[1])
                $last_name = $customer_Info[1];
            else
                $last_name = $first_name;   
            $email="";
            if(isset($configSetting['fba']) && $configSetting['fba']=='yes') 
            {
              $sql_email = 'SELECT email FROM jet_shop_details where merchant_id='.$merchant_id;
              $model_email = Data::sqlRecords($sql_email,'one','select');
              $email=$model_email->email;
            }
            $first_addr = $second_addr = $shipping_level = "";
            $first_addr=$result['shipping_to']['address']['address1'];
            $second_addr=$result['shipping_to']['address']['address2'];
            $phone_number=isset($result['shipping_to']['recipient']['phone_number']) ? $result['shipping_to']['recipient']['phone_number'] : time(); 
            $billing_addr = $shipping_addr = $tax_lines = [];
            //add shipping lines for items
            $item_info=isset($result['order_totals']['item_price']['tax_info'])?$result['order_totals']['item_price']['tax_info']:"Jet Item Tax";
                                    
            if($itemTax>0)
                $tax_lines=[["title"=> $item_info,"price"=> $itemTax]];
            $shipping_carrier=isset($result['order_detail']['request_shipping_method'])?$result['order_detail']['request_shipping_method']:$result['order_detail']['request_shipping_carrier'];
            $shipping_level=isset($result['order_detail']['request_service_level'])?$result['order_detail']['request_service_level']:$shipping_carrier;
            $billing_addr=array(
                "first_name"=> $first_name,
                "last_name"=>$last_name,
                "address1"=> $first_addr,
                "address2"=> $second_addr,
                "phone"=>$phone_number,
                "city"=> $result['shipping_to']['address']['city'],
                "province"=> $result['shipping_to']['address']['state'],
                "country"=> "United States",
                "zip"=> $result['shipping_to']['address']['zip_code']
            );            
              
              if(count($itemArray)>0)
              {
                $Orderarray['order']=array(
                    "line_items"=>$itemArray,
                    "customer"=>array(
                      "first_name"=> $first_name,
                      "last_name"=> $last_name,
                      "email"=> $email
                    ),
                    "billing_address"=> $billing_addr,
                    "shipping_address"=> $billing_addr,
                    "note"=>"Jet-Integration(".$result['reference_order_id'].")",
                    'tags'=>"jet.com",
                    "email"=> $email,
                    "inventory_behaviour"=>"decrement_obeying_policy",
                    "financial_status"=>"paid",
                    "shipping_lines"=> array(
                      array(
                        "title"=> $shipping_carrier,
                        "price"=> $shippingTax,
                        "code"=> $shipping_carrier,
                        "source"=> "Jet",
                        "requested_fulfillment_service_id"=> null,
                        "delivery_category"=> null,
                        "carrier_identifier"=>$shipping_level,
                        "tax_lines"=> $tax_lines,
                      )
                    ),
                    "format"=> "json"
                );
                if($shipping_carrier=="" && $shipping_level=="")
                {
                  if($merchant_id==397)
                  {
                    $Orderarray['order']['shipping_lines'][0]['title']="Standard";
                    $Orderarray['order']['shipping_lines'][0]['code']="Standard";
                    $Orderarray['order']['shipping_lines'][0]['carrier_identifier']="Standard";
                  }
                  else
                    unset($Orderarray['order']['shipping_lines']);
                }
                elseif($shipping_level!="" && $shipping_carrier=="") 
                {
                  $Orderarray['order']['shipping_lines'][0]['title']=$shipping_level;
                  $Orderarray['order']['shipping_lines'][0]['code']=$shipping_level;
                }
                $response = $lineArray = [];
                fwrite($handle,PHP_EOL."Ordered Data for shopify : ".PHP_EOL.json_encode($Orderarray).PHP_EOL);
                $response = $sc->call('POST', '/admin/orders.json',$Orderarray);
                
                fwrite($handle,PHP_EOL."Order response from shopify : ".PHP_EOL.json_encode($response).PHP_EOL);
                if(!array_key_exists('errors',$response))
                {
                  //send request for order acknowledge
                  $linesItemFulfillment=[];
                  foreach($response['line_items'] as $key=>$value)
                  {
                    $lineArray[$key]=$value['id'];
                    if(is_array($fulfillment_variant_ids) && in_array($value['variant_id'], $fulfillment_variant_ids))
                    {
                      $linesItemFulfillment[$key]['id']=$value['id'];
                    }
                  }
                  $queryObj="";
                  $query="UPDATE `jet_order_detail` SET  shopify_order_name='".$response['name']."',shopify_order_id='".$response['id']."',lines_items='".implode(',',$lineArray)."' where merchant_id='".$merchant_id."' AND merchant_order_id='".$result['merchant_order_id']."'";
                  $countOrder++;
                  fwrite($handle,PHP_EOL."shopify order name insertion query".PHP_EOL.$query.PHP_EOL);
                  Data::sqlRecords($query,null,'update');
                  fwrite($handle,PHP_EOL."shopify order name insertion successfully".PHP_EOL);
                  if(is_array($fulfillment_variant_ids) && count($fulfillment_variant_ids)>0) 
                  {
                    $shopifyShip['fulfillment']=[
                      'line_items'=>$linesItemFulfillment,
                    ];
                    $shipmentResponse = $sc->call('POST', '/admin/orders/'.$response['id'].'/fulfillments.json',$shopifyShip);
                    fwrite($handle,PHP_EOL." fullfillment response for FBA order ".PHP_EOL.json_encode($shipmentResponse).PHP_EOL);
                  }
                }                  
              }
          }
          unset($Orderarray);unset($itemArray);unset($result);unset($response);unset($lineArray);unset($resultdata);
	}

	/*
	*  Send tracking information to jet for completed orders (Shipped from shopify store but not completed on jet)
	*/
	public static function shipJetOrder($sc=[],$jetHelper=[],$client_address_details=[],$value=[],$merchant_id=null,&$countShip)
	{
		if (!empty($value) && is_array($value) && isset($value['shopify_order_id'])) 
        {
            $query1 = $shopify_order_id = $tracking_company = $tracking_number = $order_row_id = $merchant_order_id = $errorMessage = "";
            $shipmentResponse = $address_detail_array = $shipment_items = $isInstalled = [];

            $order_row_id = $value['id'];
            $merchant_id = $value['merchant_id'];
            $shopify_order_id = $value['shopify_order_id'];
            
            $merchant_order_id = $value['merchant_order_id'];            
            $notShipped = $saveRecords = false;
            $updated_at = date("Y-m-d H:i:s");
            $jetOrderdata = json_decode($value['order_data'],true);
            $dir = Yii::getAlias('@webroot').'/var/jet/order/'.$merchant_id.'/'.$value['reference_order_id'];
            if (!file_exists($dir)) {
                mkdir($dir,0755,true);
            }
           
            $filename1 = $dir.'/shipmentByCron.log';
            
            $file1 = fopen($filename1, 'w');
            
            try 
            {                                
                $errorMessage = 'Get address details from jet-config :'.PHP_EOL;
                fwrite($file1, $errorMessage);
                if (!empty($client_address_details)) 
                {                    
                    $zip_code = $client_address_details['zipcode'];
                }
                
                $errorMessage = 'Get shipment details from shopify'.PHP_EOL;
                $shipmentResponse = $sc->call('GET', '/admin/orders/'.$shopify_order_id.'.json?fields=fulfillments');

                $errorMessage.= json_encode($shipmentResponse);
                fwrite($file1, $errorMessage);

                if(is_array($shipmentResponse) && isset($shipmentResponse['fulfillments']) && count($shipmentResponse['fulfillments'])>0) 
                {                            
                    foreach ($shipmentResponse as $key1 => $value1) 
                    {                                
                        if (!empty($value1) && is_array($value1)) 
                        {
                            foreach ($value1 as $key2 => $value2) 
                            {
                                $tracking_number = isset($value2['tracking_number']) ? preg_replace('/\s+/', '', $value2['tracking_number'])  : "";
                                $tracking_company = isset($value2['tracking_company']) ? trim($value2['tracking_company']) : "";
                                $updated_at = isset($value2['updated_at']) ? $value2['updated_at'] : date('d-m-Y');

                                if ($tracking_number == "") {
                                    $errorMessage= "=>" . $merchant_order_id . " Missing Tracking number<hr>".PHP_EOL;
                                    fwrite($file1, $errorMessage);
                                    //fclose($file1);
                                    break;
                                    //goto orderslabel;
                                }

                                if (isset($value2['line_items']) && is_array($value2['line_items'])) 
                                {
                                    $errorMessage = "shipment lines items \n".PHP_EOL;
                                    fwrite($file1, $errorMessage);
                                    foreach ($value2['line_items'] as $key_data => $value_data) 
                                    {
                                        $sku = "";
                                        $matchResponse = self::matchShipmentSku($value_data,$jetOrderdata['order_items']);
                                        fwrite($file1, PHP_EOL." SKU MATCH RESPONSE ".$matchResponse.PHP_EOL);

                                        if ($matchResponse=='ced') {
                                            $sku = $value_data['sku'];
                                        }
                                        else
                                        {
                                            $sku = $value_data['product_id'].'-'.$value_data['variant_id'];
                                        }                                   
                                        fwrite($file1, PHP_EOL." MERCHANT-SKU NAME =>".$sku.PHP_EOL);

                                        $shipment_items[] = array(
                                            "alt_shipment_item_id" => ''.$value_data['id'],
                                            "merchant_sku" => $value_data['sku'],
                                            "response_shipment_sku_quantity" => (int)$value_data['quantity'],
                                        );                                                
                                    }
                                }
                            }
                        }
                    }
                } // shopify shipment response closed
                else
                {
                    $errorMessage = 'Order not fulfilled from shopify : '.PHP_EOL;
                    fwrite($file1, $errorMessage);
                    $notShipped = true; // continue;
                }
                $carriers=[
                    'FedEx','FedEx SmartPost','FedEx Freight','Fedex Ground','UPS','UPS Freight','UPS Mail Innovations','UPS SurePost','OnTrac','OnTrac Direct Post','DHL','DHL Global Mail','USPS','CEVA','Laser Ship','Spee Dee','A Duie Pyle','A1','ABF','APEX','Averitt','Dynamex','Eastern Connection','Ensenda','Estes','Land Air Express','Lone Star','Meyer','New Penn','Pilot','Prestige','RBF','Reddaway','RL Carriers','Roadrunner','Southeastern Freight','UDS','UES','YRC','GSO','A&M Trucking','SAIA Freight','Other'
                ];

                $errorMessage = 'Order tracking company : '.$tracking_company.'<----> tracking number'.$tracking_number.PHP_EOL;
                fwrite($file1, $errorMessage);
                if(isset($tracking_number))
                {
                    if(!in_array($tracking_company, $carriers))
                        $tracking_company="Other";
                }
                else
                {
                	$notShipped = true;//continue;
                }
                $offset_end = "";
                $offset_end = self::getStandardOffsetUTC();
                if (empty($offset_end) || trim($offset_end) == '')
                    $offset = '.0000000-00:00';
                else
                    $offset = '.0000000' . trim($offset_end);
                if (!$notShipped) // Order Shipped from shopify store
                {
                	$dt = new \DateTime($updated_at);
	                $expected_delivery_date = date("Y-m-d\TH:i:s", time()) . $offset;
	                $data_ship=[];
	                $data_ship['shipments'][] = array(
	                    'shipment_tracking_number' => $tracking_number,
	                    'response_shipment_date' => $expected_delivery_date,
	                    'response_shipment_method' => $tracking_company,
	                    'expected_delivery_date' => $expected_delivery_date,
	                    'ship_from_zip_code' => $zip_code,
	                    'carrier_pick_up_date' => $expected_delivery_date,
	                    'carrier' => $tracking_company,
	                    'shipment_items' => $shipment_items
	                );
	                
	                $resultObject="";
	                if (!empty($data_ship)) 
	                {
	                    $errorMessage = "shipment data going to send on jet.com:\n" . json_encode($data_ship) . "\n".PHP_EOL;
	                    fwrite($file1, $errorMessage);
	                    $jetdata = array();
	                    $status=true;
	                    $jetdata = $jetHelper->CPutRequest('/orders/'.$merchant_order_id.'/shipped', json_encode($data_ship), $merchant_id, $status);
	                    $responseArray = array();
	                    $responseArray = json_decode($jetdata, true);

	                    if($status==204) 
	                    {
	                        //orderslabelcomplete:
	                        $saveRecords=true;
	                        $errorMessage = "successfully shipped on jet\n".PHP_EOL;
	                        fwrite($file1,$errorMessage); 
	                    }
	                    elseif (isset($responseArray['errors'][0]) && $responseArray['errors'][0]=="Order is complete, no new messages accepted")
	                    {
	                        fwrite($file1, "order is already shipped on jet".PHP_EOL);
	                        $saveRecords=true;
	                    }
	                    else 
	                    {
	                        $errorMessage = "shipment not completed\n" . json_encode($responseArray['errors']).PHP_EOL;
	                        fwrite($file1, $errorMessage);
	                    }
	                }
	                if($saveRecords)
	                {
	                    $countShip++;
	                    $resultResponse = array();
	                    $status=true;
	                    $resultResponse = $jetHelper->CGetRequest('/orders/withoutShipmentDetail/' . $merchant_order_id,$merchant_id,$status);
	                    $resultObject = json_decode($resultResponse, true);
	                    if (isset($resultObject['status']) && ($resultObject['status'] == 'complete' || $resultObject['status'] == 'inprogress')) 
	                    {
	                        Data::sqlRecords("UPDATE `jet_order_detail` SET `status`='".$resultObject['status']."' WHERE `merchant_id`='" . $merchant_id . "' AND `shopify_order_id`='" . $shopify_order_id . "'", "", "update");
	                        $errorMessage = "shipment successfully processed on jet".PHP_EOL;
	                        fwrite($file1, $errorMessage);
	                        $url = Yii::getAlias('@webjeturl')."/jetapi/jetnotification/order-fulfilment";
	                        $curtRequestParams =['order_id'=> $value['reference_order_id'],'merchant_id'=>$merchant_id,'order_status'=>$resultObject['status']];
	                        Data::Curlrequest($url,$curtRequestParams,$merchant_id);
	                    }
	                }		
                }                
            } 
            catch (Exception $e) 
            {
                $errorMessage = "Eception in shipment :".PHP_EOL. $e->getMessage() .PHP_EOL;
                fwrite($file1, $errorMessage);
            }
        }
        return true;
	}

	public static function matchShipmentSku($lineItems,$orderItems)
    {       
        $matchFlag = "";
        foreach ($orderItems as $key => $value) 
        {
            if ($lineItems['sku']==addslashes(trim($value['merchant_sku']))) {
                $matchFlag = "ced";
            }
        }
        return $matchFlag;      
    }
    public static function getStandardOffsetUTC()
    {
        $timezone = date_default_timezone_get();
        if ($timezone == 'UTC') {
            return '';
        } else 
        {
            $timezone = new \DateTimeZone($timezone);
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);

            foreach (array_reverse($transitions, true) as $transition) 
            {
                if ($transition['isdst'] == 1) {
                    continue;
                }
                return sprintf('%+03d:%02u', $transition['offset'] / 3600, abs($transition['offset']) % 3600 / 60);
            }
            return false;
        }
    }

    public static function processOrderShipment($merchant_id,&$countShip){
	  $model = $cron_array = $jetConfig = [];
      $jetappDetailsObj = new Jetappdetails();
      $orderDataObj = new Orderdata();
      $storeObj = $jetappDetailsObj->getShpoifyClientObj($merchant_id);
      $jetConfig = $jetappDetailsObj->getConfigurationDetails($merchant_id);
      $configSetting = Jetproductinfo::getConfigSettings($merchant_id);
      
      if (!empty($jetConfig) && $storeObj)
      {
        $jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api", $jetConfig['api_user'], $jetConfig['api_password']); 

        $query = "select `jod`.`id`,`jod`.`merchant_id`,`reference_order_id`,`merchant_order_id`,`shopify_order_id`,`order_data` from `jet_order_detail` jod left join `jet_shop_details` on  `jet_shop_details`.merchant_id=jod.`merchant_id` where jod.merchant_id='".$merchant_id."' AND jod.shopify_order_id !='' AND `jet_shop_details`.`install_status`!=0 AND (jod.status='acknowledged' or jod.status='inprogress')";
        $orderAckCollection = Data::sqlRecords($query, "all", "select");
          
        if (!empty($orderAckCollection) && is_array($orderAckCollection)) 
        {
          foreach ($orderAckCollection as $key => $value) 
          {
            self::shipJetOrder($storeObj,$jetHelper,$configSetting,$value,$merchant_id,$countShip);
          }
        }
      }
    }
}