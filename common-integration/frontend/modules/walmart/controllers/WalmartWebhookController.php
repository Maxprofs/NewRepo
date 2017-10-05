<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use common\models\MerchantDb;
use frontend\components\Webhook;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Sendmail;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\models\WalmartShopDetails;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\Generator;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\models\WalmartExtensionDetail;
use frontend\modules\walmart\components\Jetappdetails;
use frontend\modules\walmart\controllers\WalmartscriptController;

class WalmartWebhookController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Process Product Create Webhook
     */
    public function actionProductcreate()
    {
        $data = Yii::$app->request->post();
        if (isset($data['shopName'])) {
            $shopName = $data['shopName'];
            $merchant_id = Data::getMerchantIdFromShop($shopName);

            if ($merchant_id) {
                $id = $data['id'];
                $logFIle = 'product/create/' . $merchant_id . '/' . $id;
                Data::createLog('Data : ' . json_encode($data), $logFIle, 'a');

                try {
                    $connection = Yii::$app->getDb();
                    $result = Jetproductinfo::saveNewRecords($data, $merchant_id, $connection);
                } catch (\yii\db\Exception $e) {
                    $this->createExceptionLog('actionCurlproductcreate', $e->getMessage(), $data['shopName']);
                    exit(0);
                } catch (Exception $e) {
                    $this->createExceptionLog('actionCurlproductcreate', $e->getMessage(), $data['shopName']);
                    exit(0);
                }
            }
        }
    }

    /**
     * Process Product Update Webhook
     */
    public function actionProductupdate()
    {
        $product = Yii::$app->request->post();
        if (is_array($product) && count($product) > 0) {
            if (isset($product['shopName'])) {
                $shopName = $product['shopName'];
                $merchant_id = Data::getMerchantIdFromShop($shopName);
                $logFIle = 'product/UpdateProduct/' . $merchant_id . '/' . time();
                Data::createLog('Requested Data: ' . json_encode($product), $logFIle, 'a');
                $import_option = Data::getConfigValue($merchant_id, 'import_product_option');
                if ($import_option == 'published') {
                    if (!isset($product['published_at']) || (isset($product['published_at']) && empty($product['published_at']))) {
                        return;
                    }
                }
                $syncConfigJson = Data::getConfigValue($merchant_id, 'sync-fields');
                if ($syncConfigJson) {
                    $checkConfig = true;
                    $syncFields['sync-fields'] = json_decode($syncConfigJson, true);
                } else {
                    $sync_fields = [
                        'sku' => '1',
                        'title' => '1',
                        'image' => '1',
                        'product_type' => '1',
                        'qty' => '1',
                        'weight' => '1',
                        'price' => '1',
                        'upc' => '1',
                        'vendor' => '1',
                        'description' => '1',
                        'variant_options' => '1',
                    ];
                    $syncFields['sync-fields'] = $sync_fields;
                }
                Jetproductinfo::updateDetails($product, $syncFields, $merchant_id, true);
            } else {
                Data::createLog("Product Update error wrong post");
            }
        } else {
            Data::createLog("Product Update error wrong post");
        }
    }

    /**
     * Process Product Delete Webhook
     */

    public function actionProductdelete()
    {

        $data = Yii::$app->request->post();
        if (is_array($data) && count($data) > 0) {
            $logFIle = 'product/delete/' . $data['merchant_id'] . '/' . time();
            Data::createLog('Requested Data: ' . json_encode($data), $logFIle, 'a');
            $config = Data::getConfiguration($data['merchant_id']);
            $obj = new Walmartapi($config['consumer_id'], $config['secret_key']);
            if (isset($data['archiveSku']) && $data['archiveSku']) {
                foreach ($data['archiveSku'] as $key => $value) {
                    $obj->retireProduct($value);
                }
            } else {
                Data::createLog("Product Detete error wrong post");
            }
        } else {
            Data::createLog("Product Detete error wrong post");
        }

    }

    /**
     * Process Order Create Webhook
     */
    public function actionOrdercreate()
    {
        die('actionOrdercreate');
        //http://localhost/integration/walmart/walmart-webhook/ordercreate
    }

    /**
     * Process Order Cancel Webhook
     */
    public function actionOrdercancel()
    {
        $data = $data = Yii::$app->request->post();
        
        if ($data && isset($data['id']) && isset($data['shopName'])) {

            $merchant_id = Data::getMerchantIdFromShop($data['shopName']);
            $config = Data::getConfiguration($merchant_id);

            $directory = \Yii::getAlias('@webroot') . '/var/order/webhook/' . $merchant_id . '/' . $data['id'] . '/';
            if (!file_exists($directory)) {
                mkdir($directory, 0775, true);
            }
            $handle = fopen($directory . '/cancel.log', 'a');

            $walmartHelper = new Walmartapi($config['consumer_id'], $config['secret_key']);
            if (isset($data['id'])) {

                $lineNumbers = [];
                $connection = Yii::$app->getDb();
                $query = "SELECT * FROM `walmart_order_details` WHERE shopify_order_id='" . $data['id'] . "' AND `shopify_order_name`='" . $data['name'] . "'";
                $order = $connection->createCommand($query)->queryOne();

                if($order){

                    if ($merchant_id == $order['merchant_id']) {
                        $orderData = json_decode($order['order_data'], true);
                        if (isset($orderData['orderLines']['orderLine'])) {
                            $items = isset($orderData['orderLines']['orderLine'][0]) ? $orderData['orderLines']['orderLine'] : [$orderData['orderLines']['orderLine']];
                            foreach ($items as $item) {
                                if (isset($item['lineNumber'])) {
                                    $lineNumbers[] = $item['lineNumber'];
                                } elseif (isset($item[0]['lineNumber'])) {
                                    $lineNumbers[] = $item[0]['lineNumber'];
                                }
                            }
                        }
                        $dataShip = ['shipments' => [['cancel_items' => [['lineNumber' => implode(',', $lineNumbers)]]]]];

                        fwrite($handle, 'Cancel SHIP DATA : ' . print_r($dataShip, true) . PHP_EOL . PHP_EOL);

                        $response = $walmartHelper->rejectOrder($orderData['purchaseOrderId'], $dataShip);
                        if (isset($response['errors'])) {
                            if (isset($response['errors']['error'])){
                                fwrite($handle, 'Cancel Error : ' . json_encode($response['errors']['error']['description'], true) . PHP_EOL . PHP_EOL);

                            }
                            else{
                                fwrite($handle, 'Cancel Error : ' . json_encode('Order Can not be Cancelled', true) . PHP_EOL . PHP_EOL);

                            }
                        } else {
                            $query = "UPDATE `walmart_order_details` SET status='canceled' WHERE purchase_order_id='" . $order['purchase_order_id'] . "'";
                            $order = $connection->createCommand($query)->execute();

                            fwrite($handle, 'Cancel Successfully Cancel : ' . json_encode('Order Can not be Cancelled', true) . PHP_EOL . PHP_EOL);

                        }
                        fwrite($handle, 'RESPONSE:' . print_r($response, true));
                        fclose($handle);
                        return $this->redirect(['index']);
                    }else{
                        fwrite($handle, 'Cancel Error : ' . json_encode('You are not an authorized user to cancel this order.', true) . PHP_EOL . PHP_EOL);

                    }
                }else{

                    fwrite($handle, 'Cancel Error : ' . json_encode('Order Data Not found', true) . PHP_EOL . PHP_EOL);

                }

            }
        }
    }

    /**
     * Process Create Shipment Webhook
     */
    public function actionOrdershipment()
    {
        if ($_POST && isset($_POST['id'])) 
        {
            $shop = isset($_POST['shopName']) ? $_POST['shopName'] : "NA";
            $path = 'shipment/' . $shop . '/' . Data::getKey($_POST['id']) . '.log';
            try {
                //create shipment data
                Data::createLog("order shipment in walmart" . PHP_EOL . json_encode($_POST), $path);
                $objController = Yii::$app->createController('walmart/walmartorderdetail');
                $objController[0]->actionCurlprocessfororder();
            } catch (Exception $e) {
                Data::createLog("order shipment error " . json_decode($_POST), $path, 'a', true);
            }

        } else {
            Data::createLog("order shipment error wrong post");
        }
    }

    /**
     * Inventory Update Action
     */
    public function actionInventoryupdate()
    {
        $data = Yii::$app->request->post();
        if (is_array($data) && count($data) > 0) {
            $first_index_data = current($data);
            $merchant_id = $first_index_data['merchant_id'];
            $logFIle = 'product/inventoryupdate/' . $merchant_id . '/' . time();
            Data::createLog('Update Inventory Data: ' . json_encode($data), $logFIle, 'a');
            $config = Data::getConfiguration($merchant_id);
            $obj = new Walmartapi($config['consumer_id'], $config['secret_key']);
            foreach ($data as $key => $value) {
                $id = Data::getProductId($value['sku'], $merchant_id);
                if(!Data::checkThresholdValue($inventoryArray['inventory'],$merchant_id)){
                    $inventoryArray['inventory'] = '0';
                }
                $fulfillment_lag_time = Data::getFulfillmentlagtime($id, $merchant_id);
                if (isset($fulfillment_lag_time['fulfillment_lag_time'])) {
                    $value['fulfillment_lag_time'] = $fulfillment_lag_time['fulfillment_lag_time'];
                }
                $inventoryArray = [];
                $inventoryArray = [
                    'wm:inventory' => [
                        '_attribute' => [
                            'xmlns:wm' => "http://walmart.com/",
                        ],
                        '_value' => [
                        ]
                    ]
                ];
                $keys = 1;
                $this->prepareInventoryData($inventoryArray, $value);

                if (!file_exists(\Yii::getAlias('@webroot') . '/var/product/webhook/xml/' . $merchant_id . '/updateinventory')) {
                    mkdir(\Yii::getAlias('@webroot') . '/var/product/webhook/xml/' . $merchant_id . '/updateinventory', 0775, true);
                }
                $file = Yii::getAlias('@webroot') . '/var/product/webhook/xml/' . $merchant_id . '/updateinventory/MPProduct-' . time() . '.xml';
                $xml = new Generator();
                $xml->arrayToXml($inventoryArray)->save($file);
                $response = $obj->putRequest(Walmartapi::GET_INVENTORY_SUB_URL . '?sku=' . $value['sku'], ['data' => file_get_contents($file)]);
                $responseArray = Walmartapi::xmlToArray($response, true);
                return $responseArray;
            }
        } else {
            Data::createLog("Product Inventory error wrong post");
        }

    }

    /**
     * Price Update Action
     */
    public function actionPriceupdate()
    {
        $data = Yii::$app->request->post();
        if (is_array($data) && count($data) > 0) {
            $first_index_data = current($data);
            $merchant_id = $first_index_data['merchant_id'];
            $currency = Data::getConfiguration($merchant_id);
            $logFIle = 'product/priceupdate/' . $merchant_id . '/' . time();
            Data::createLog('Update Price Data: ' . json_encode($data), $logFIle, 'a');
            $config = Data::getConfiguration($merchant_id);
            $obj = new Walmartapi($config['consumer_id'], $config['secret_key']);
            foreach ($data as $key => $value) {
                //walmart product price
                $type = Data::getProductType($key, $merchant_id);
                if ($type) {
                    if ($type == 'simple') {
                        $price = Data::getWalmartPrice($value['product_id'], $merchant_id);
                        if (isset($price['product_price']) && !empty($price)) {
                            $value['price'] = WalmartRepricing::getProductPrice($price['product_price'], 'simple', $value['product_id'], $merchant_id);

                        } else {
                            $value['price'] = WalmartRepricing::getProductPrice($value['price'], 'simple', $value['product_id'], $merchant_id);
                        }
                    } else {
                        $price = Data::getWalmartPrice($key, $merchant_id);
                        if (isset($price['product_price']) && !empty($price)) {
                            $value['price'] = WalmartRepricing::getProductPrice($price['product_price'], 'simple', $key, $merchant_id);

                        } else {
                            $value['price'] = WalmartRepricing::getProductPrice($value['price'], 'simple', $key, $merchant_id);
                        }
                    }
                    $priceArray = [];
                    $priceArray = [
                        'PriceFeed' => [
                            '_attribute' => [
                                'xmlns:gmp' => "http://walmart.com/",
                            ],
                            '_value' => [
                                0 => [
                                    'PriceHeader' => [
                                        'version' => '1.5',
                                    ],
                                ],
                            ]
                        ]
                    ];
                    $keys = 1;
                    $priceArray['PriceFeed']['_value'][$keys] = [
                        'Price' => [
                            'itemIdentifier' => [
                                'sku' => $value['sku']
                            ],
                            'pricingList' => [
                                'pricing' => [
                                    'currentPrice' => [
                                        'value' => [
                                            '_attribute' => [
                                                'currency' => $currency['currency'],
                                                'amount' => $value['price']
                                            ],
                                            '_value' => [

                                            ]
                                        ]
                                    ],
                                    'currentPriceType' => 'BASE',
                                    'comparisonPrice' => [
                                        'value' => [
                                            '_attribute' => [
                                                'currency' => $currency['currency'],
                                                'amount' => $value['price']
                                            ],
                                            '_value' => [

                                            ]
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ];
                    if (!file_exists(\Yii::getAlias('@webroot') . '/var/product/xml/' . $merchant_id . '/updatePrice')) {
                        mkdir(\Yii::getAlias('@webroot') . '/var/product/xml/' . $merchant_id . '/updatePrice', 0775, true);
                    }
                    $file = Yii::getAlias('@webroot') . '/var/product/xml/' . $merchant_id . '/updatePrice/MPProduct-' . time() . '.xml';
                    $xml = new Generator();
                    $xml->arrayToXml($priceArray)->save($file);
                    $response = $obj->postRequest(Walmartapi::GET_FEEDS_PRICE_SUB_URL, ['file' => $file]);
                    $responseArray = Walmartapi::xmlToArray($response);
                } else {
                    Data::createLog("Product type not set for product id '" . $value['product_id'] . "' error wrong post");
                }
            }
        } else {
            Data::createLog("Product updatePrice error wrong post");
        }
    }

    /*Pre inventory xml data for single product*/

    public function prepareInventoryData(&$inventoryArray, $product = [])
    {
        $inventoryArray['wm:inventory']['_value'] = [
            'wm:sku' => $product['sku'],
            'wm:quantity' => [
                'wm:unit' => 'EACH',
                'wm:amount' => $product['inventory'],
            ],
            'wm:fulfillmentLagTime' => (isset($product['fulfillment_lag_time']) && $product['fulfillment_lag_time']) ? $product['fulfillment_lag_time'] : '1',
        ];
        return $inventoryArray;
    }

    public function actionIsinstall()
    {
        $file_name = \Yii::getAlias('@webroot').'/var/uninstall/'.date('d-m-Y').'/uninstall-'.time().'.txt';
        $path = dirname($file_name);
        if(!file_exists($path)){
            mkdir($path, 0777, true);
        }
        $myfile = fopen($file_name, "a+");
        try {
            $webhook_content = '';
            $webhook = fopen('php://input' , 'rb');
            while(!feof($webhook)) { //loop through the input stream while the end of file is not reached
                $webhook_content .= fread($webhook, 4096); //append the content on the current iteration
            }
            fclose($webhook); //close the resource

            $data = json_decode($webhook_content,true); //convert the json to array
            
            fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
            fwrite($myfile, print_r($data,true));

            if($data && isset($data['id'])) 
            {
                $data['log_file_name'] = $file_name;
                $url = Yii::getAlias('@webwalmarturl')."/walmart-webhook/processinstall";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch, CURLOPT_TIMEOUT,1);
                $result = curl_exec($ch);
                curl_close($ch);
                exit(0);
            }
        }
        catch(Exception $e) {
            fwrite($myfile,$e->getMessage());
            exit(0);
        }
    }

    public function actionProcessinstall()
    {
        $data = $_POST;
        if(isset($data['myshopify_domain']) && isset($data['id']))
        {
            try 
            {
                $file_name = $data['log_file_name'];
                $path = dirname($file_name);
                if(!file_exists($path)){
                    mkdir($path, 0777, true);
                }
                $myfile = fopen($file_name, "a+");

                $shop = $data['myshopify_domain'];
                $model = User::find()->where(['username'=>$shop])->one();
                if($model) 
                {
                    $walmartShopDetails = WalmartShopDetails::find()->where(['shop_url'=>$shop])->one();    
                    if($walmartShopDetails)
                    {
                        $shopUrl = $walmartShopDetails->shop_url;
                        $token = $walmartShopDetails->token;

                        $install_status = false;//Data::isAppInstalled($shopUrl, $token);
                        if(!$install_status) {
                            $email_id = $walmartShopDetails->email;
                            $walmartShopDetails->status = 0;
                            $walmartShopDetails->save(false);
                            Sendmail::uninstallmail($email_id);

                            $extensionModel = WalmartExtensionDetail::find()->where(['merchant_id'=>$walmartShopDetails->merchant_id])->one();
                            if($extensionModel) {
                                $extensionModel->app_status="uninstall";
                                $extensionModel->uninstall_date=date('Y-m-d H:i:s');
                                $extensionModel->save(false);
                            }

                            $merchantDbModel = MerchantDb::find()->where(['merchant_id'=>$walmartShopDetails->merchant_id])->one();
                            if($merchantDbModel) {
                                $app_name = $merchantDbModel->app_name;
                                if(strpos($app_name, Data::APP_NAME_WALMART) !== false){
                                    $apps = explode(',', $app_name);
                                    if(($index = array_search(Data::APP_NAME_WALMART, $apps))!==false) {
                                        unset($apps[$index]);

                                        if(count($apps)) {
                                            $merchantDbModel->app_name = implode(',', $apps);
                                        } else {
                                            $merchantDbModel->app_name = '';
                                        }

                                        $merchantDbModel->save(false);
                                    }
                                }
                            }
                        }

                        fwrite($myfile, "\nToken : ".$token);
                    }

                    fwrite($myfile, "\nShop : ".$shop);

                    Webhook::createWebhooks($shop);
                }
            }
            catch(Exception $e){
                fwrite($myfile,$e->getMessage());
            }
        }
    }
}