<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\models\NeweggOrderDetail;
use frontend\modules\neweggmarketplace\models\NeweggShopDetail;
use Yii;
use common\models\User;
use yii\web\Controller;
use frontend\modules\neweggmarketplace\components\product\Productimport;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Sendmail;
use frontend\modules\neweggmarketplace\components\productinfo;
use frontend\modules\neweggmarketplace\components\Helper;
use frontend\modules\neweggmarketplace\components\product\ProductPrice;
use frontend\modules\neweggmarketplace\components\order\Orderdetails;
use frontend\components\Webhook;

class NeweggWebhookController extends Controller
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
                    $result = Productimport::saveProduct($data, $merchant_id, $connection);
                } catch (\yii\db\Exception $e) {
                    Data::createExceptionLog('actionCurlproductcreate', $e->getMessage(), $data['shopName']);
                    exit(0);
                } catch (Exception $e) {
                    Data::createExceptionLog('actionCurlproductcreate', $e->getMessage(), $data['shopName']);
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
                    $syncFields = json_decode($syncConfigJson, true);
                } else {
                    $sync_fields = [
                        'sku' => '1',
                        'title' => '1',
                        'image' => '1',
                        'product_type' => '1',
                        'inventory' => '1',
                        'weight' => '1',
                        'price' => '1',
                        'upc' => '1',
                        'vendor' => '1',
                        'description' => '1',
                        'variant_options' => '1',
                    ];
                    $syncFields['sync-fields'] = $sync_fields;
                }
                productinfo::updateDetails($product, $syncFields, $merchant_id, true);
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
            //$obj = new Walmartapi($config['consumer_id'],$config['secret_key']);
            if (isset($data['archiveSku']) && $data['archiveSku']) {
                foreach ($data['archiveSku'] as $key => $value) {
                    $message = ProductPrice::updatePriceOnNewegg($data, $data['merchant_id'], null, true);
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

        /*$shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) ? $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] : 'common';
        $mode = 'a+';
        $merchant_id = Helper::getMerchantId($shopName);

        $file_path = 'order/cancelorder/' . $merchant_id['merchant_id'] . '/webhook/' . date('d-m-Y');

        Helper::createLog($merchant_id['merchant_id'], $file_path, $mode);

        $webhook_content = '';
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhook_content .= fread($webhook, 4096);
        }
        $realdata = "";
        $data = array();
        $orderData = array();
        fclose($webhook);
        $realdata = $webhook_content;
        if ($webhook_content == '' || empty(json_decode($realdata, true))) {
            return;
        }
        $data = json_decode($realdata, true);*/
        $data = Yii::$app->request->post();
        if (isset($data['shopName'])) {


            $shopName = $data['shopName'];
            $merchant_id = Helper::getMerchantId($shopName);
            $mode = 'a+';

            $file_path = 'order/cancelorder/' . $merchant_id['merchant_id'] . '/webhook/' . date('d-m-Y');

            $log = '';

            $log .= PHP_EOL . "[" . date('d-m-Y H:i:s') . "]\n";

            if ($data && isset($data['id'])) {

                $log .= PHP_EOL . '-------data-------' . PHP_EOL . json_encode($data) . PHP_EOL . '------- end data ---------';

                Helper::createLog($log, $file_path, $mode);

                $orderData = NeweggOrderDetail::find()->where(['shopify_order_id' => $data['id'], 'shopify_order_name' => $data['name'], 'merchant_id' => $merchant_id['merchant_id']])->one();

                $log .= PHP_EOL . '------ start order data -------' . PHP_EOL . json_encode($orderData) . '-------- end order data --------';

                if ($orderData) {

                    $log .= PHP_EOL . '-----order number------' . PHP_EOL . $orderData->order_number;

                    $return = Orderdetails::cancelorder($orderData->order_number);

                    $log .= PHP_EOL . '-----order response------' . PHP_EOL . json_encode($return);

                    Helper::createLog($log, $file_path);

                }
            } else {
                $log .= PHP_EOL . '------- Order Data Not Found -------' . PHP_EOL;
                Helper::createLog($log, $file_path, $mode);
            }
        }
    }


    public function actionShipment()
    {
        if ($_POST && isset($_POST['id'])) {
            $shop = isset($_POST['shopName']) ? $_POST['shopName'] : "NA";
            $path = 'order/ship/webhook/' . $shop . '/' . date('d-m-Y') . '/' . time() . '.log';
            try {

                Orderdetails::curlprocessfororder($_POST);

            } catch (Exception $e) {
                Helper::createLog("order shipment error " . json_encode($_POST), $path, 'a', true);
            }

        } else {
            Helper::createLog("order shipment error wrong post");
        }
    }

    public function actionIsinstall()
    {
        $webhook_content = '';
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) { //loop through the input stream while the end of file is not reached
            $webhook_content .= fread($webhook, 4096); //append the content on the current iteration
        }
        fclose($webhook); //close the resource
        $data = "";
        $data = $webhook_content;
        if ($webhook_content == '' || empty(json_decode($data, true))) {
            return;
        }
        $data = json_decode($webhook_content, true); //convert the json to array
        $data['shopName'] = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) ? $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] : "common";
        $path = \Yii::getAlias('@webroot') . '/var/newegg/uninstall/' . date('d-m-Y') . '/' . $data['shopName'];
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }
        $file_path = $path . '/data.log';
        $myfile = fopen($file_path, "a+");
        fwrite($myfile, "\n[" . date('d-m-Y H:i:s') . "]\n");
        fwrite($myfile, print_r($data, true));
        fclose($myfile);

        $url = Yii::getAlias('@webneweggurl') . "/newegg-webhook/curlprocessforuninstall";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function actionCurlprocessforuninstall()
    {
        $data = $_POST;
        $shop = "";
        $model = "";
        $model1 = "";
        $modelnew = "";

        $path = \Yii::getAlias('@webroot') . '/var/newegg/uninstall/' . date('d-m-Y') . '/' . $data['shopName'];
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }
        $file_path = $path . '/data.log';
        $myfile = fopen($file_path, "a+");
        // fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
        fwrite($myfile, print_r($data, true));
        //fclose($myfile);

        if ($data && isset($data['id'])) {
            $shop = $data['myshopify_domain'];
            $model = User::find()->where(['username' => $shop])->one();

            fwrite($myfile, print_r($model, true));
            fwrite($myfile, PHP_EOL . "SHOP NAME" . PHP_EOL);
            fwrite($myfile, $shop . PHP_EOL);

            if ($model) {
                fwrite($myfile, PHP_EOL . 'In if condition' . PHP_EOL);

                $extensionModel = "";
                $email_id = "";
                $extensionModel = NeweggShopDetail::find()->where(['LIKE', 'shop_url', $shop])->one();

                fwrite($myfile, PHP_EOL . 'Extension Detail' . PHP_EOL);
                fwrite($myfile, print_r($extensionModel, true));

                if ($extensionModel) {
                    $email_id = $extensionModel->email;
                    $extensionModel->app_status = "uninstall";
                    $extensionModel->uninstall_date = date('Y-m-d H:i:s');
                    $extensionModel->install_status = 0;
                    $extensionModel->save(false);
                    Webhook::createWebhooks($shop);

                    fwrite($myfile, PHP_EOL . 'Extension Detail After Save' . PHP_EOL);
                    fwrite($myfile, print_r($extensionModel, true));
                    Sendmail::uninstallmail($email_id);
                }
            }
        }
    }

}