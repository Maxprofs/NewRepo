<?php

namespace frontend\modules\walmartapi\controllers;

use yii\web\Controller;

class TestshivamController extends Controller
{
    public function actionDashboard()
    {
        $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartdashboard/dashboard";

        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL =>$url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS =>array(
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                'MERCHANTID : 842',
                'HASHKEY   : 04e07bb0e59eb9e94d6bee1fe4b882a5'
//                'MERCHANTID : 14',
//                'HASHKEY   : 2ca7591d59605a8160fe0f6e69814611'
            )


        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        echo $resp;
    }
    public function actionOrderlisting()
    {
        // status => acknowledged, completed, canceled
        //$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/list";
        $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/list";
        $data = json_encode(array(
                /*'merchant_id'=>'14',*/
//                'sku' =>'beauty',
                'status'=>'completed'
            )
        );

        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL =>$url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS =>array(
                'page' =>'0',
                'limit' => '1',
                'filter' =>$data
		    	),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                'MERCHANTID : 14',
                'HASHKEY   : 2ca7591d59605a8160fe0f6e69814611'
            )
        ));
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        echo $resp;
        print_r(json_decode($resp, true));
    }
    public function actionOrderview()
    {
        // status => acknowledged, completed, canceled
        //$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/list";
        $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/view";
        $data = array(
                'shopify_order_id'=>4572529740

        );

        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL =>$url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS =>$data,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                'MERCHANTID : 14',
                'HASHKEY   : 2ca7591d59605a8160fe0f6e69814611'
            )
        ));
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        echo $resp;
        print_r(json_decode($resp, true));
    }
	/*
		ORDER LISTING

		Enter Url: $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/list";

		For filter : $data = json_encode(array(
     				'merchant_id'=>'14',
     				'sku' =>'2890',
     				'status'=>'acknowledge'
     				)
		     );
		For pagnation : Post data in CURLOPT_POSTFIELDS as mentioned below.

		note:- we have fixed filter attribute - merchant_id,sku,status,shopify_order_id,id.

		CURLOPT_POSTFIELDS =>array(
		        				 'page' =>'0',
		        				 'limit' => '1'
		        				 'filter' =>$data
		    	),
		     CURLOPT_HTTPHEADER => array(
		    					'MERCHANTID : 14',
		    					'HASHKEY : 7051aa584716e8cc2e5c2c863a4313af'
		    	)
		)
		INPUT: mandatory -> Send merchant id and hash key in curl header .
				optional -> Pagination and filter.
		
		EXPECTED OUTPUT: error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

	*/
		/*
		ORDER VIEW

		Enter Url: $url ="https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/view";

		Enter Id in POSTFILED
		CURLOPT_POSTFIELDS =>array(
		        				 'id'=>'28',
		    	),

		For Validation send MERCHANTID and HASHKEY in curl requestheader.
		CURLOPT_HTTPHEADER => array(
		    					'MERCHANTID : 14',
		    					'HASHKEY : 7051aa584716e8cc2e5c2c863a4313af'
		    	)
		)

		INPUT mandatory-> id from walmart_order_details table in params and  merchant_id and hashkey in curl header.

		OUTPUT: success-> order's data.
				error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

	*/

	/*
		UPLOAD A SINGLE PRODUCT

		Enter Url: $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/upload";

		Enter Id in POSTFILED
		CURLOPT_POSTFIELDS =>array(
		        				 'product_id' => 8348584963,
		    	),

		For Validation send MERCHANTID and HASHKEY in curl requestheader.
		CURLOPT_HTTPHEADER => array(
		    					'MERCHANTID : 14',
		    					'HASHKEY : 7051aa584716e8cc2e5c2c863a4313af'
		    	)
		)

		INPUT -> mandatory= product_id in params and merchant id and hashkey in curl header.

		OUTPUT: Success-> upload message.
				error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

	*/

		/*
		DASHBOARD

		Enter Url: $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartdashboard/dashboard";

		For Validation send MERCHANTID and HASHKEY in curl requestheader.
		CURLOPT_HTTPHEADER => array(
		    					'MERCHANTID : 14',
		    					'HASHKEY : 7051aa584716e8cc2e5c2c863a4313af'
		    	)
		)

		INPUT : mandatory -> merchant_id and hashkey in curl header.

		OUTPUT : success-> In json format = {"totalOrders":"0","availableProduct":"0","readytoshipOrders":"0","totalrevenue":0}
				error-> {"error":true,"message":" error message "}

	*/

		/*
		FORGET PASSWORD

		Enter Url: $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartforgetpassword/forgetpassword";


		INPUT : mandatory -> merchant_id and shop_url in curl params.

		ex- CURLOPT_POSTFIELDS =>array(
		    					'merchant_id' => 14,
		        				'shop_url' =>'ced-jet.myshopify.com',				
		    	)

		OUTPUT : success-> "{\"success\":true,\"message\":\"password send to you email\"}".
				error-> {"error":true,"message":" error message "}

	*/
		/*
	
		NEW PASSWORD

		Enter Url: $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartforgetpassword/newpassword";

		note:- old_password is the password that was sended in your email at the time of forget password.

		INPUT : mandatory -> merchant_id , old_password and new_password in curl params.

		ex- CURLOPT_POSTFIELDS =>array(
		        				'old_password' => '2cfb00de6a6775a0cf24e14faceca089',
		        				'new_password' =>'shivamverma2904'
		    	),

		OUTPUT : success-> "{\"success\":true,\"message\":\"Password Updated.\"}".
				error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

	*/
				
	public function actionTest2()
	{
		// $url ="http://192.168.0.31/advanced/frontend/web/index.php?r=walmartapi/walmartdashboard/dashboard";
		 // $url = "https://192.168.0.31/advanced/frontend/web/index.php?r=walmartapi/walmartforgetpassword/newpassword";
		// $url = "https://192.168.0.31/advanced/frontend/web/index.php?r=walmartapi/walmartforgetpassword/forgetpassword";
		 // $url = "https://192.168.0.31/advanced/frontend/web/index.php?r=walmartapi/walmartdashboard/dashboard";

		// $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/login";
//		$data = json_encode(array(
//     				'sku' =>'2860',
//					// 'merchant_id' => 6,
//     				'status'=>'acknowledged'
//     				)
//		     );
		$url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartdashboard/dashboard";
		// $url ="https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/view";
		// $url ="https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/upload";

		// $data = json_encode(array(
  //   				'merchant_id'=>'14',
  //   				'sku' =>'2890',
  //   				'status'=>'acknowledge'
  //   				)
		//     );

			$curl = curl_init();
		// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL =>$url,
		    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS =>array(
		    					// 'merchant_id' => 6,
//		        				'old_password' => '27cf7587cb6570a87ba8fda2bf995e24',
		        				// 'shop_url' =>'ced-jet.myshopify.com',
//		        				'new_password' =>'shivamverma2904'
		        				// 'id'=>'2',
		        				// 'page' =>'0',
		        				// 'limit' => '1',
//		        				 'filter' =>$data
		    	),
		    CURLOPT_SSL_VERIFYHOST => 0,
    		CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => array(
                    'MERCHANTID : 14',
                    'HASHKEY   : 2ca7591d59605a8160fe0f6e69814611'
                )


		));
		 $resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
        echo $resp;
//		echo json_decode($resp);

	}
}

                    // 'HASHKEY : 7051aa584716e8cc2e5c2c863a4313af'
