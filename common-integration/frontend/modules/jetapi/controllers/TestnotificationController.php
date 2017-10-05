<?php

namespace frontend\modules\jetapi\controllers;
use Yii;

class TestnotificationController extends \yii\web\Controller
{
    public function actionTester()
    {

		/* Must used these below parameter exactally same Name */

		/*
		ids: perform upload operation prepare array index as ids

		page: its indicate number of which user wants to show on your grid listing
		
		limit: Use for define limit of products and order 

		type : define the type of product like simple or variants

		MERCHANTID : it is a unique id for all user
		
		HASHKEY : its like auth_key which use for authentic validation

		

	    ;*/



	    
/*
	     For order place



		For order place: https://shopify.cedcommerce.com/integration/walmartapi/walmartnotification/order-place
		
		//example :
		
*/
		/*$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartnotification/order-place";*/
		//$url = "https://shopify.cedcommerce.com/jet/jetapi/jetnotification/order-place";
		//$url = "http://localhost/advanced/frontend/web/index.php?r=walmartapi/walmartnotification/order-place";

		$url = "https://shopify.cedcommerce.com/jet/jetapi/jetnotification/order-place";
		//$url = "localhost/training/shopify/jetapi/jetnotification/order-place";
	    $curtRequestParams =['order_id'=> '00test','merchant_id'=>14];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		curl_close($curl);

		print_r( $resp);



		
/*
	for OrderFulfilment
	For OrderFulfilment: https://shopify.cedcommerce.com/integration/walmartapi/walmartnotification/order-fulfilment
		

		$url = "http://localhost/advanced/frontend/web/index.php?r=walmartapi/walmartnotification/order-fulfilment";
	    $curtRequestParams =['order_id'=> 'ced-jet.myshopify.com','merchant_id'=>14,'order_status'=>'complete'];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r( $resp);



	/*
	for ProductInventoryUpdate

	For ProductInventoryUpdate: https://shopify.cedcommerce.com/integration/walmartapi/walmartnotification/product-inventory-update
*/
/*	  $url = "http://localhost/advanced/frontend/web/index.php?r=walmartapi/walmartnotification/product-inventory-update";
	    $curtRequestParams =['product_id'=> 100,'qty' => 18,'merchant_id'=>14];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r( $resp);
*/

/*
	for configuration

	For ProductInventoryUpdate: https://shopify.cedcommerce.com/integration/walmartapi/walmartnotification/configuration
*/
/*  $url = "http://localhost/advanced/frontend/web/index.php?r=walmartapi/walmartnotification/configuration";
	    $curtRequestParams =['merchant_id'=>14];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r( $resp);

*/

/*	try{
	  	

	$url = "http://localhost/training/shopify/jetapi/feedback/client-feedback";
	    $curlRequestParams =['email'=>"abc@gmail.com",'first_name'=>"Ankit",'last_name'=>"Singh",'description'=>"awesome",'type'=>'comment'];
	   // $data = json_encode($curlRequestParams);
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => http_build_query($curlRequestParams),
			));
		$resp = curl_exec($curl);

		curl_close($curl);

		print_r( $resp);
	}
	catch(Exception $e){
		echo $e->message();
		die("jjj");

	}*/
/*$path ="jetapi/feedback/client";
$curtRequestParams =['email'=>"ankitsingh1436@gmail.com",'first_name'=>"Ankit",'last_name'=>"Singh"];
$this->callCurl($path,$curtRequestParams);*/



    

	}
		
	/*public function callCurl($path,$data,$json=true,$returnTransfer = true){
		// URL on which we have to post data
		$baseUrl = Yii::getAlias('@weburl').'/';
		$url =  $baseUrl.$path;
		
		// Any other field you might want to catch
		
		
		$data_string = json_encode($data); 

		// Initialize cURL
		$ch = curl_init();
		// Set URL on which you want to post the Form and/or data
		curl_setopt($ch, CURLOPT_URL, $url);
		// Data+Files to be posted
		if($json){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			    'Content-Type: application/json',                                                                                
			    'Content-Length: ' . strlen($data_string))                                                                       
			);   
		}
		else
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			// Data+Files to be posted

			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));   
		}      
		// Pass TRUE or 1 if you want to wait for and catch the response against the request made
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
		// For Debug mode; shows up any error encountered during the operation
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		
		// Execute the request
		$response = curl_exec($ch);
		
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_errno($ch)) {
			return ['responseCode'=>$httpcode,'errorMsg'=>curl_error($ch)];  
		}
		
		curl_close($ch);
		if($httpcode!=200)
		{
			var_dump($response);die;
			preg_match_all('/<div class=\"header(.*?)\">(.*?)<\/div>/s',$response,$output,PREG_SET_ORDER);
			//preg_match_all('/<div class="header">(.*?)</div>/s', $response, $output);
			
			return ['responseCode'=>$httpcode,'errorMsg'=>isset($output[0][0])?$output[0][0]:''];
			//var_dump($output);
			
		}
		else
		{
			return ['responseCode'=>$httpcode,'response'=>$response];
		}
		// Just for debug: to see response
		
	}*/
}