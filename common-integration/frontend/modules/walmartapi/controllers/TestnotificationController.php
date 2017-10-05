<?php

namespace frontend\modules\walmartapi\controllers;

class TestnotificationController extends \yii\web\Controller
{
    public function actionTester()
    {

		$url = "https://shopify.cedcommerce.com/jet/jetapi/jetnotification/order-place";
		//$url = "localhost/training/shopify/jetapi/jetnotification/order-place";
	    $curtRequestParams =['order_id'=> '00test','merchant_id'=>680];
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
	/*	$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartnotification/order-place";
		//$url = "http://localhost/advanced/frontend/web/index.php?r=walmartapi/walmartnotification/order-place";
	    $curtRequestParams =['order_id'=> 'ced-jet.myshopify.com','merchant_id'=>14];
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
		print_r( $resp);*/



		
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
	  	

	





    

	}
		


}



 
