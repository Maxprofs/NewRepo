<?php

namespace frontend\modules\walmartapi\controllers;

class AnkittestController extends \yii\web\Controller
{
    public function actionTest()
    {

    		/*	$url = "https://shopify.cedcommerce.com/integration/apilogin/apilogin/register";
	            $curtRequestParams =['shop_url'=> 'sam-mystore.myshopify.com','device_access_token'=>'safaffsaafafs24'];
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
				$url = "https://shopify.cedcommerce.com/integration/apilogin/apilogin/login";
//				$url = "http://192.168.0.39/training/shopify/integration/apilogin/apilogin/login";
				//$url = "http://192.168.0.31/training/integration/apilogin/apilogin/login";
	            $curtRequestParams =['shop_url'=> 'training-shopify.myshopify.com','password'=>'shivam2904'];
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
		print_r($resp);
		
		/*$url = "https://shopify.cedcommerce.com/integration/apilogin/appselection/selected-app";*/
/*		$url = "192.168.0.31/training/integration/apilogin/appselection/selected-app";
	            $curtRequestParams =['selected_app'=> 'jet'];
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
		print_r( $resp);*/
				/*$url = "https://shopify.cedcommerce.com/integration/apilogin/resendmail/resendmail";*/
		/*		$url = "192.168.0.31/training/integration/apilogin/resendmail/resendmail";
	            $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com'];
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
/*				$url = "https://shopify.cedcommerce.com/integration/apilogin/forgetpassword/forgetpassword";
	            $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com'];
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
			/*	$url = "192.168.0.39/integration/apilogin/forgetpassword/newpassword";
	            $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com','verification_password'=>'f3b4f4627e760fc8a4acc7af5e3f2a85','new_password'=>'steve@123'];
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


/*				$url = "192.168.0.31/training/integration/apilogin/apilogout/logout";
	            $curtRequestParams =['shop_url'=> 'ced-jet.myshopify.com','device_access_token'=>'shivamverma2904'];
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
		print_r( $resp);*/

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



		For registration: https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/register
		
		//example :
		
*/
		/*$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/register";
	    $curtRequestParams =['shop_url'=> 'sleektrends.myshopify.com'];
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

		//valid output formate :{"message":"Verification Password has been send to your Registered Shopify Store Email.","success":true,"login_status":"pending"}
/*
		success :Get json array 
			hash_key : unique user hash key
			merchant_id : unique user_id/merchant_id
			success :true

		error: Get json array
			message : define error type
			error : true;



		For login: https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/login
		
		//example :
*/
		/*$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/login";
	    $curtRequestParams =['shop_url'=> 'sleektrends.myshopify.com','password' => '9f6eac0b5d2f318881e83210fc8771ca','newpassword' => 'steve@123'];
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
	/*$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/login";
	    $curtRequestParams =['shop_url'=> 'sleektrends.myshopify.com','password' => 'steve@123'];
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
	  
	  	

		


	/*			$url = "192.168.0.31/training/integration/apilogin/feedback/client-feedback";
	    $curtRequestParams =['email'=> 'a.com','first_name' => 'ankit','last_name' => 'singh','description'=>'awesome mindblowing','type'=>'bug_report','type'=>'comment'];
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
		var_dump( $resp);*/
		
		//For product list: https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/list



	/*	example :

	*/
	//$url="192.168.0.31/training/integration/apilogin/apilogout/logout";
		 //$url = "192.168.0.31/training/integration/walmartapi/walmartproduct/list";

		/*$url = 'https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/list';
	    $filterValue = ['status'=>"Not Uploaded"];
		$filterValue = json_encode($filterValue,true);*/
		//$curtRequestParams = ['filter' => $filterValue,'page' =>'0','limit' =>'10'];
/*		$curtRequestParams = ['filter' => $filterValue];
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
			print_r($resp);
		curl_close($curl);*/
	

		
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
		
		

	/*	For multiple uploads: https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/batchupload

		example :
		

	
		*/
		/*$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/upload";
/*		$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/upload";
		$curtRequestParams =['ids'=> '9154592067,9154630467,9154807107'];
				$curl = curl_init();
				curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL =>$url,
			    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $curtRequestParams,
			    CURLOPT_HTTPHEADER => array(
			    					'MERCHANTID : 680',
			    					'HASHKEY : 5556b549bcf1e10c28fe951ec71de1ff'
			    	),
			    CURLOPT_SSL_VERIFYHOST => 0,
	    		CURLOPT_SSL_VERIFYPEER => 0,
			));
		$resp = curl_exec($curl);
		print_r( $resp);
		curl_close($curl);*/
		
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
/*		$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/view";

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



 