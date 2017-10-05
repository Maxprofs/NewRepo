<?php

namespace frontend\modules\jetapi\controllers;

use Rhumsaa\Uuid\Console\Exception;
use yii\helpers\Url;
use yii\web\Controller;
use Yii;

class TestshivamController extends Controller
{
    /*
        DASHBOARD

        Enter Url: $url = "https://shopify.cedcommerce.com/jet/jetapi/jetdashboard/dashboard";

        For Validation send MERCHANTID and HASHKEY in curl requestheader.
        CURLOPT_HTTPHEADER => array(
                                'MERCHANTID : 14',
                                'HASHKEY : b9df1039a43261d9669b7922d40433c9'
                )
        )

        INPUT : mandatory -> merchant_id and hashkey in curl header.

        OUTPUT : success-> In json format = {"liveonjet":"0","underjetreview":"9","readytoshipOrders":"9","totalrevenue":574806.04}
                error-> {"error":true,"message":" error message "}

    */

    /*
        SALES ORDER VIEW

        Fixed index For filter  :- $filterarray = array(
                        'id'=>'int',
                        'reference_order_id'=>'int',
                        'merchant_order_id'=>'string',
                        'shopify_order_name'=>'string',
                        'merchant_sku'=>'string',
                        'status'=>'string'
                    );

        Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/sales";

        Enter Id in POSTFILED

        For filter : $data = json_encode(array(
                     'key'=>'value',
                     )
             );
        For Validation send MERCHANTID and HASHKEY in curl requestheader.
        CURLOPT_HTTPHEADER => array(
                                'MERCHANTID : 14',
                                'HASHKEY : b9df1039a43261d9669b7922d40433c9'
                )
        )
        CURLOPT_POSTFIELDS =>array(
                                 'page' =>'0',
                                 'limit' => '1'
                                 'filter' =>$data
                ),

        INPUT mandatory-> id from jet_order_details table in params and  merchant_id and hashkey in curl header.

        OUTPUT: success-> sales order's data.
                error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

    */
    /*
Failed ORDER VIEW

$filterarray = array(
            'id'=>'int',
            'reference_order_id'=>'int',
            'merchant_id'=>'int',
            'merchant_order_id'=>'string',
        );

Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/failed";

Enter Id in POSTFILED

For filter : $data = json_encode(array(
         'key'=>'value',
         )
 );

For Validation send MERCHANTID and HASHKEY in curl requestheader.
CURLOPT_HTTPHEADER => array(
                    'MERCHANTID : 14',
                    'HASHKEY : b9df1039a43261d9669b7922d40433c9'
    )
CURLOPT_POSTFIELDS =>array(
                     'page' =>'0',
                     'limit' => '1'
                     'filter' =>$data
    ),

INPUT mandatory-> merchant_id and hashkey in curl header.

OUTPUT: success-> failed order's data.
    error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

*/

    /*
        Return ORDER VIEW

        $filterarray = array(
                        'id'=>'int',
                        'merchant_id'=>'int',
                        'returnid'=>'string',
                        'order_reference_id'=>'string',
                        'status'=>'string'
                    );
        Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/return";

        Enter Id in POSTFILED

        For filter : $data = json_encode(array(
                     'key'=>'value',
                     )
             );

        For Validation send MERCHANTID and HASHKEY in curl requestheader.
        CURLOPT_HTTPHEADER => array(
                                'MERCHANTID : 14',
                                'HASHKEY : b9df1039a43261d9669b7922d40433c9'
                )
        CURLOPT_POSTFIELDS =>array(
                                 'page' =>'0',
                                 'limit' => '1'
                                 'filter' =>$data
                ),

        INPUT mandatory-> merchant_id and hashkey in curl header.

        OUTPUT: success-> return order's data.
                error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

    */
    /*
REFUND ORDER VIEW

$filterarray = array(
            'id'=>'int',
            'merchant_id'=>'int',
            'returnid'=>'string',
            'order_reference_id'=>'string',
            'status'=>'string'
        );
Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/refund";

Enter Id in POSTFILED

For filter : $data = json_encode(array(
         'key'=>'value',
         )
 );

For Validation send MERCHANTID and HASHKEY in curl requestheader.
CURLOPT_HTTPHEADER => array(
                    'MERCHANTID : 14',
                    'HASHKEY : b9df1039a43261d9669b7922d40433c9'
    )
CURLOPT_POSTFIELDS =>array(
                     'page' =>'0',
                     'limit' => '1'
                     'filter' =>$data
    ),

INPUT mandatory-> merchant_id and hashkey in curl header.

OUTPUT: success-> refund order's data.
    error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

*/
    /*
            SALES ORDER VIEW


            Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/view";

            Enter Id in POSTFILED

            For Validation send MERCHANTID and HASHKEY in curl requestheader.
            CURLOPT_HTTPHEADER => array(
                                    'MERCHANTID : 14',
                                    'HASHKEY : b9df1039a43261d9669b7922d40433c9'
                    )
            CURLOPT_POSTFIELDS =>array(
                                     'order_increment_id' =>'804',
                    ),

            INPUT mandatory-> merchant_id and hashkey in curl header.

            OUTPUT: success-> sales order view data.
                    error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

        */

    /*
        FORGET PASSWORD


        Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetforgetpassword/forgetpassword";

        Enter Id in POSTFILED

        For Validation send MERCHANTID and HASHKEY in curl requestheader.

        CURLOPT_POSTFIELDS =>array(
                                 'shop_url' =>'value',
                ),


        OUTPUT: success-> Password send to your email id
                error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

    */
    /*
NEW PASSWORD


Enter Url: $url ="https://shopify.cedcommerce.com/jet/jetapi/jetforgetpassword/newpassword";

Enter Id in POSTFILED

For Validation send MERCHANTID and HASHKEY in curl requestheader.

CURLOPT_POSTFIELDS =>array(
                     'shop_url' =>'ced-jet.myshopify.com',
                     'verification_password'=>'4456e77ecf37c691865b2a800b0fcd10',
                     'new_password'=>'newpassword'
    ),


OUTPUT: success-> password updated
    error -> error-> "{\"error\":true,\"message\":\"Invalid send data.\"}"

*/

    public function actionTest2()
    {
//        print_r(Yii::$app->getBasePath());
//
//        echo '<hr>';
//        print_r(Url::home(true));die('sdfghjk');
//        print_r(Yii::$app->getUrlManager()->getBaseUrl());die;
        //        Enter Url: $url = "https://shopify.cedcommerce.com/jet/jetapi/jetdashboard/dashboard";

        // $url ="http://192.168.0.31/advanced/frontend/web/index.php?r=walmartapi/walmartdashboard/dashboard";
//		 $url = "https://192.168.0.39/advanced/frontend/web/index.php?r=walmartapi/walmartforgetpassword/newpassword";
//         $url = "https://shopify.cedcommerce.com/jet/jetapi/jetforgetpassword/forgetpassword";


//        $url = 'https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/orderlist';
        $url = "https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/orderlist";

//$url = "http://192.168.0.31/training/integration/apilogin/apilogin/login";
//        $url = "https://shopify.cedcommerce.com/jet/jetapi/jetproduct/list";
//         $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartlogin/login";
        // $url = "https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/list";
        // $url ="https://shopify.cedcommerce.com/integration/walmartapi/walmartorderdetail/view";
        // $url ="https://shopify.cedcommerce.com/integration/walmartapi/walmartproduct/upload";
//        $url = "localhost/shopify/jetapi/jetproduct/list";

        // $filterarray = array(
        // 				'id'=>'int',
        // 				'merchant_order'=>'int',
        // 				'merchant_order_id'=>'string',
        // 				'shopify_order_name'=>'string',
        // 				'merchant_sku'=>'string',
        //                       'status'=>'string'
        // 			)

//        $data = json_encode(array(
//                'key' => 'value',
//            )
//        );


//         $data = json_encode(array('merchant_sku'=>array('0' => 'beauty_ring1'),
//                               				 'quantity_ulfillable'=>1,
//                               				 'send_return_address'=>1,
//                               				 'RMA_number'=>1,
//                               				 'days_to_return'=>1,
//                               				 'response_shipment_sku_quantity'=>array('0'=>1),
//                               				 'response_shipment_sku_quantity_hidden'=>0,
//                               				 'response_shipment_cancel_quantity_hidden'=>array('0'=>'0'),
//                               	)
//             );




        $filter = json_encode(array(
                'id' => '',
                'reference_order_id' => '',
                'merchant_order_id' => '',
                'shopify_order_name' => '',
                'merchant_sku' => '',
                'status' => 'completed'
            )
        );
        $curl = curl_init();

        // Set some options - we are passing in a useragent too here
       curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
//                                 'orderlistindex'=>'sales'
//		    					 'shop_url' =>'ced-jet.myshopify.com',
//                                 'password' => 'be44727b1f34b10bf58000c4a818451d',
//                                 'newpassword' => 'steve@123'
//                'device_access_token'=>'shivamverma2904'
//		        				 'verification_password'=>'1774e5955dbba4a0d3242fc4e9f62fd9',
//		        				 'new_password'=>'steve@123'
//                'shopify_order_id' => '4511424588'
//                2469627846
                // 'ids'=>'4440053068','merchant_order_id'=>'bb56de5b5a5d473ab835799675da8068',
//                 'shopify_order_id'=>'4529302924',
                // 'merchant_id'=>14,
                // 'order_increment_id'=>3270
                // 'merchant_order_id'=>'98b304b699404f82a57c91b61a196f69',
                // 'shopify_order_id'=>'4439806668',
                // 'shopify_order_name'=>'#1288',
                // 'username'=>'ced-jet.myshopify.com',
                // 'auth_key'=>'6eff789e2c67f37e6756dc389507b47d',
                // 'api_user'=>'3B1C8F1323EAEE249739ED97967AB1380AE0F76A',
                // 'api_password'=>'+VvgylgBIvM+FpFSbzvOIcjkk5WTBRJyFclsh3+vyRQL',
                // 'fullfilment_node_id'=>'b12d4d370f4c4abf91bf028c35994833',
                // 'request_ship_by'=>'14/10/2016 10:31',
                // 'deliver_by'=>'17/10/2016 10:31',
                // 'request_shipping_carrier'=>'USPS',
                // 'tracking_number'=>652626262,
                // 'ship_to_date'=>'2016/10/13 10:31',
                // 'carrier_pick_up_date'=>'2016/10/14 10:31',
                // 'expected_delivery_date'=>'2016/10/15 10:31',
                // 'products'=>$data,
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
//            CURLOPT_POSTFIELDS => array(
//                'page' => '0',
//                'limit' => '1',
//                'filter' => $data,
//            ),
            CURLOPT_HTTPHEADER => array(
                'MERCHANTID : 14',
                'HASHKEY : 2ca7591d59605a8160fe0f6e69814611'
            )


        ));
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        echo $resp;
        print_r(json_decode($resp, true));

    }

    public function actionTest()
    {
//        $url = "localhost/shopify/jetapi/jetorderdetail/order_list";
//                $url = "localhost/shopify/jetapi/jetorderdetail/view";

//        $url = "localhost/shopify/jetapi/jetorderdetail/orderlist";

//        $url = "localhost/shopify/jetapi/jetproduct/list";
        try {
            $url = "https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/orderlist";
//            $url = "https://shopify.cedcommerce.com/jet/jetapi/jetorderdetail/orderlist";
//            $url = "localhost/shopify/jetapi/jetorderdetail/orderlist";

//            $url = "https://shopify.cedcommerce.com/jet/jetapi/jetproduct/list";


            $curl = curl_init();

            $filter = json_encode(array(
                'id' => '',
                'reference_order_id' => '',
                'merchant_order_id' => '',
                'shopify_order_name' => '',
                'merchant_sku' => '',
                'status' => ''
                )
            );
//d95385df7a80eb5b7c4e8a92419df29c
            // Set some options - we are passing in a useragent too here 41885b75719814a7dfdadf0b0e263f26
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => 'Codular Sample cURL Request',
                CURLOPT_POST => 1,
//                CURLOPT_POSTFIELDS => array(
//                    'page' => '0',
//                    'filter' => $filter,
//                    'limit' => '2'
//                ),
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,

                CURLOPT_HTTPHEADER => array(
                    'MERCHANTID : 14',
                    'HASHKEY : d95385df7a80eb5b7c4e8a92419df29c'
                )


            ));
            $resp = curl_exec($curl);
            // Close request to clear up some resources
//            var_dump(curl_error($curl));

            curl_close($curl);
//            var_dump($resp);
            echo $resp;

            print_r(json_decode($resp, true));
        }catch (Exception $e){
            var_dump($e);
//            die('in catch');
        }

    }
}

//41885b75719814a7dfdadf0b0e263f26
//d95385df7a80eb5b7c4e8a92419df29c