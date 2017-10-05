<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/1/17
 * Time: 5:34 PM
 */
namespace frontend\modules\neweggmarketplace\controllers;

use common\models\AppStatus;
use common\models\NeweggExtensionDetail;
use common\models\User;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Helper;
use frontend\modules\neweggmarketplace\components\order\Orderdetails;
use frontend\modules\neweggmarketplace\models\NeweggOrderDetail;
use Yii;

use yii\base\Exception;
use yii\web\Controller;

class ShopifywebhookController extends Controller
{
    public function beforeAction($action)
    {
        if ($this->action->id == 'productcreate') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'productupdate') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'productdelete') {
            Yii::$app->controller->enableCsrfValidation = false;
        }

        if ($this->action->id == 'ship') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'createshipment') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'isinstall') {
            Yii::$app->controller->enableCsrfValidation = false;
        }

        if ($this->action->id == 'ordership') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'createorder') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'cancelled') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        return true;
    }

    public function actionCreateshipment()
    {

        $shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) ? $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] : 'common';
        try {
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
            $data = json_decode($realdata, true); // ab ye data ka array hai



            if (isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
                $data['shopName'] = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
            if (isset($data['note']) && $data['note'] == "Newegg-Integration(newegg.com)") {
                $url = Yii::getAlias('@webneweggurl') . "/shopifywebhook/shipment";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                $result = curl_exec($ch);
                curl_close($ch);
                exit(0);
            }

        } catch (Exception $e) {
            Helper::createExceptionLog('actioncreateshipment', $e->getMessage(), $shopName);
            exit(0);
        }
    }

    /*
     * function for creating log
     */
    /*public function createExceptionLog($functionName,$msg,$shopName = 'common'){
        $dir = \Yii::getAlias('@webroot').'/var/exceptions/'.$functionName.'/'.$shopName.'/'.date('d-m-Y');
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        $filenameOrig = $dir.'/'.time().'.txt';
        $handle = fopen($filenameOrig,'a');
        $msg = date('d-m-Y H:i:s')."\n".$msg;
        fwrite($handle,$msg);
        fclose($handle);
        Data::sendEmail($filenameOrig,$msg);
    }*/


    public function actionShipment()
    {
        if ($_POST && isset($_POST['id'])) {
            $shop = isset($_POST['shopName']) ? $_POST['shopName'] : "NA";
            $path = 'order/shipment/' . $shop . '/' . Data::getKey($_POST['id']);
            try {
                //create shipment data
                Helper::createLog("order shipment in newegg" . PHP_EOL . json_encode($_POST), $path,'a+');
                $objController = Yii::$app->createController('neweggmarketplace/neweggorderdetail');
                $objController[0]->actionCurlprocessfororder();
            } catch (Exception $e) {
                Helper::createLog("order shipment error " . json_decode($_POST), $path, 'a', true);
            }

        } else {
            Helper::createLog("order shipment error wrong post");
        }
    }

    public function actionCancelled()
    {
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
        $data = json_decode($realdata, true);

        $log = '';

        $log .= PHP_EOL . "[" . date('d-m-Y H:i:s') . "]\n";

        if ($data && isset($data['id'])) {

            $log .= PHP_EOL . '-------data-------' . PHP_EOL . $data . PHP_EOL . '-------end data ---------';

            $orderData = NeweggOrderDetail::find()->where(['shopify_order_id' => $data['id'], 'shopify_order_name' => $data['name']])->one();

            $log .= PHP_EOL . '------ start order data -------' . PHP_EOL . $orderData . '-------- end order data --------';

            if ($orderData) {
                $merchant_id = $orderData->merchant_id;

                $file_path = 'order/cancelorder/' . $merchant_id . '/webhook/' . date('d-m-Y');

                $log .= PHP_EOL . '-----order number------' . PHP_EOL . $orderData->order_number;

                $return = Orderdetails::cancelorder($orderData->order_number);

                $log .= PHP_EOL . '-----order response------' . PHP_EOL . $return;

                Helper::createLog($log, $file_path);

            }
        }
    }

    public function actionIsinstall()
    {
        $webhook_content = '';
        $webhook = fopen('php://input' , 'rb');
        while(!feof($webhook)){ //loop through the input stream while the end of file is not reached
            $webhook_content .= fread($webhook, 4096); //append the content on the current iteration
        }
        fclose($webhook); //close the resource
        $data="";
        $data=$webhook_content;
        if ( $webhook_content=='' || empty(json_decode($data,true))) {
            return;
        }
        $data = json_decode($webhook_content,true); //convert the json to array
        $data['shopName']= isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:"common";
        $path=\Yii::getAlias('@webroot').'/var/uninstall/'.date('d-m-Y').$data['shopName'];
        if (!file_exists($path)){
            mkdir($path,0775, true);
        }
        $file_path = $path.'/data.log';
        $myfile = fopen($file_path, "a+");
        fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
        fwrite($myfile, print_r($data,true));
        fclose($myfile);

        $url = Yii::getAlias('@weburl')."/shopifywebhook/curlprocessforuninstall";
        //var_dump(http_build_query( $data ));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
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
        if ($data && isset($data['id'])) {
            $shop = $data['myshopify_domain'];
            $model = User::find()->where(['username' => $shop])->one();
            if ($model) {
                $model1 = AppStatus::find()->where(['shop' => $shop])->one();
                if ($model1) {
                    $model1->status = 0;
                    $model1->save(false);
                } else {
                    $modelnew = new AppStatus();
                    $modelnew->shop = $shop;
                    $modelnew->status = 0;
                    $modelnew->save(false);
                }
                $extensionModel = "";
                $email_id = "";
                $extensionModel = NeweggExtensionDetail::find()->where(['LIKE', 'shopurl', $shop])->one();
                if ($extensionModel) {
                    $email_id = $extensionModel->email;
                    $extensionModel->app_status = "uninstall";
                    $extensionModel->uninstall_date = date('Y-m-d H:i:s');
                    $extensionModel->save(false);
//                    Sendmail::uninstallmail($email_id);
                }
            }
        }
    }

    }