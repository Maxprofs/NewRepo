<?php

namespace frontend\modules\jetapi\controllers;
use yii\helpers\Url;
class AnkittestController extends \yii\web\Controller
{
    public function actionTest()
    {

    	
    	/*var_dump(Yii::$app->getUrlManager()->getBaseUrl());
    	print_r(Yii::getAlias('@webroot'));die("hhh");*/
    	/*$request = Yii::$app->getRequest();
    	 $hello = $request->getBaseUrl();
    	 var_dump($hello);die;*/
    	/* $url = Url::home(true)."jetapi/jetnotification/order-fulfilment";
    	 print_r($url);die;*/


	    /* Parameters  DEATILS
		Filter value: user enter grid parameter for filtering product

		curtRequestParams : prepare parameter array for curl request

		*/

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
	     URL DEATILS with example 



		For registration: https://shopify.cedcommerce.com/jet/walmartapi/walmartlogin/register
		
		//example :
		
*/
//		$url = "https://shopify.cedcommerce.com/jet/jetapi/jetlogin/register";
//		//$url = "http://localhost/training/shopify/jetapi/jetlogin/register";
//	    $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com','device_access_token'=>'safaffsaafafs24'];
//				$curl = curl_init();
//				curl_setopt_array($curl, array(
//			    CURLOPT_RETURNTRANSFER => 1,
//			    CURLOPT_URL =>$url,
//			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
//			    CURLOPT_POST => 1,
//			    CURLOPT_POSTFIELDS => $curtRequestParams,
//			    CURLOPT_SSL_VERIFYHOST => 0,
//	    		CURLOPT_SSL_VERIFYPEER => 0,
//			));
//		$resp = curl_exec($curl);
//		curl_close($curl);
//		print_r( $resp);
     //  $url = "localhost/shopify/jetapi/jetproduct/view";
//    $filterValue = ['product_id' => 4211752006];
//    $filterValue = json_encode($filterValue,true);
/*    $curtRequestParams = ['product_id' => 7953655750];
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL =>$url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $curtRequestParams,
            CURLOPT_HTTPHEADER => array(
                                'MERCHANTID : 14',
                                'HASHKEY : 41885b75719814a7dfdadf0b0e263f26'
                ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,

        ));
    $resp = curl_exec($curl);
    curl_close($curl);
    print_r( $resp);
        print_r(json_decode($resp,true));*/

		//valid output formate :{"message":"Verification Password has been send to your Registered Shopify Store Email.","success":true,"login_status":"pending"}
/*
		success :Get json array 
			hash_key : unique user hash key
			merchant_id : unique user_id/merchant_id
			success :true

		error: Get json array
			message : define error type
			error : true;

d8b466ffd09831384189aa5920a2c84d

		For login: https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/login
		
		//example :
*/
	/*$url = "http://192.168.0.31/training/integration/apilogin/apilogin/login";
	    $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com','password' => 'steve@123'];
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
		print_r( $resp)*/;
/*
		valid output formate :{"hash_key":"7051aa584716e8cc2e5c2c863a4313af","merchant_id":"14","success":true,"login_status":"complete"}


		success :Get json array 
			hash_key : unique user hash key
			merchant_id : unique user_id/merchant_id
			success :true

		error: Get json array
			message : define error type
			error : true;



*/

/* 
Next time login:

	For login:https://shopify.cedcommerce.com/jet/jetapi/jetlogin/login
		
		//example :
*/
	//$password = 'steve@123';
/*	$url = "http://shopify.cedcommerce.com/jet/jetapi/jetlogin/login";
	    $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com','password' => 'steve@123'];
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
		valid output formate :{"hash_key":"7051aa584716e8cc2e5c2c863a4313af","merchant_id":"14","success":true,"login_status":"complete"}


		success :Get json array 
			hash_key : unique user hash key
			merchant_id : unique user_id/merchant_id
			success :true

		error: Get json array
			message : define error type
			error : true;



*/
		/*	$url = "http://192.168.0.31/training/integration/apilogin/appselection/selected-app";
	            $filterValue = ['selected_app'=>"walmart"];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $filterValue,
			    CURLOPT_HTTPHEADER => array(
			    					'MERCHANTID : 14',
			    					'HASHKEY : 41885b75719814a7dfdadf0b0e263f26'
			    	),
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,

			));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r( $resp);*/
	  
	  	

		/*
		
		For product list: https://shopify.cedcommerce.com/jet/jetapi/jetproduct/list



		example :

	*/
		//$url = "https://shopify.cedcommerce.com/jet/jetapi/jetdashboard/dashboard";
		$url = "https://shopify.cedcommerce.com/jet/jetapi/jetproduct/list";
	    //$filterValue = ['status'=>"Not Uploaded"];
		//$filterValue = json_encode($filterValue,true);
		//$curtRequestParams = ['filter' => $filterValue/*,'page' =>'0','limit' =>'10'*/];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => '',
			    CURLOPT_HTTPHEADER => array(
			    					'MERCHANTID : 14',
			    					'HASHKEY : d95385df7a80eb5b7c4e8a92419df29c'
			    	),
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,

			));
		$resp = curl_exec($curl);
		print_r( $resp);
		curl_close($curl);
		
		
/*
		Note : $filterValue = ['type'=>"s",'merchant_id' =>'2' ,];
				in this s indicates simple product and 2 indicated unique user id
		$allowFilterIndex :that means filter product view with respect  to these indexes given below


		$allowFilterIndex = 'title','sku','type','merchant_id','status';

		success :Get all product detail in json formate 
			

		error: Get json array
			message : define error type
			error : true;
*/
		
		

	/*	For multiple uploads: https://shopify.cedcommerce.com/jet/jetapi/jetproduct/batchupload

		example :
		

	
		*/
		//$url = "http://192.168.0.31/training/shopify/jetapi/jetproduct/upload";
		//$url = "https://shopify.cedcommerce.com/jet/jetapi/jetproduct/upload";
		//$id = json_encode(["4211751046","7384079302"]);
		//$curtRequestParams =['ids'=> $id];
		//print_r($curtRequestParams['ids']);die;
		/*		$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_HTTPHEADER => array(
			    					'MERCHANTID : 14',
			    					'HASHKEY : d95385df7a80eb5b7c4e8a92419df29c'*/
			    				/*'HASHKEY : 41885b75719814a7dfdadf0b0e263f26'*/
		/*	    	),
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r( $resp);*/
		/*
		Note : ids: this index used for multiple product upload operation
		4211752006 and 4839342278 : these are my walmart product id
		
		valid output formate :{"success":true,"message":"product feed successfully submitted on walmart.","count":2,"feed_id":"9A941C9E9CCD4971B27FBC644E638FC8@AQMBAQA","error_count":1,"erroredSkus":"BJB1793812MPNK"}
		
		success :Get all product detail in json formate 
			

		error: Get json array
			message : define error type
			error : true;



		*/

		/*

		For product View: https://shopify.cedcommerce.com/integration/frontend/web/index.php?r=walmartapi/walmartproduct/view


		example :
*/
	/*	$url = "https://shopify.cedcommerce.com/jet/jetapi/jetproduct/view";
		$curtRequestParams = ['product_id' => 4211752006 ];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_HTTPHEADER => array(
			    					'MERCHANTID : 14',
			    					'HASHKEY : d95385df7a80eb5b7c4e8a92419df29c'
			    	),
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		curl_close($curl);
		print_r( $resp);*/
/*
		Note : $product_id = use exactly same index name for product view
			   4211752006 : This is my walmart product id

		success :Get  product detail in json formate 
			

		error: Get json array
			message : define error type
			error : true;


		*/




    

	}
		


}



