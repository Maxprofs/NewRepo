<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 13/1/17
 * Time: 12:42 PM
 */
namespace console\controllers;
use frontend\modules\neweggcanada\components\Neweggappdetail;
use frontend\modules\neweggcanada\controllers\NeweggorderdetailController;
use frontend\modules\neweggcanada\components\product\ProductPrice;
use frontend\modules\neweggcanada\components\product\ProductInventory;
use frontend\modules\neweggcanada\components\product\ProductStatus;
use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\controllers\NeweggproductController;
use frontend\modules\neweggcanada\models\NeweggCanCronSchedule;
use yii\base\Exception;
use yii\console\Controller;
use Yii;
use yii\web;
/**
 * Cron controller
 */
class NeweggcacronController extends Controller
{
    public function actionIndex()
    {
        ob_start();
        echo "cron service runnning";
        echo getcwd();
        $html = ob_get_clean();
    }

    /*public function actionNeweggorderdetails(){
        $obj = new NeweggorderdetailController(Yii::$app->controller->id,'');
        $obj->actionOrderdetails(true);
    }
    public function actionNeweggordersync(){
        $obj = new NeweggorderdetailController(Yii::$app->controller->id,'');
        $obj->actionSyncorder(true);
    }*/
    public function actionNeweggorderdetails()
    {
        ob_start();

        $connection = Yii::$app->get(Yii::$app->getBaseDb());

        $dbList = Yii::$app->getDbList();

        $processedMerchantCount = 0;
        $size = 100;

        $result = NeweggCanCronSchedule::find()->where(['cron_name' => 'fetch_order'])->one();
        if ($result && $result['cron_data'] != "") {
            $cron_array = json_decode($result['cron_data'], true);
        } else {
            $cron_array = $connection->createCommand("SELECT * FROM `merchant_db` WHERE `app_name` LIKE '%" . Data::APP_NAME_NEWEGG_CAN . "%'")->queryAll();
        }

        if (is_array($cron_array) && count($cron_array)) {
            foreach ($cron_array as $key => $merchant) {
                try {

                    if (!in_array($merchant['db_name'], $dbList)) {
                        unset($cron_array[$key]);
                        continue;
                    }

                    if ($processedMerchantCount == $size)
                        break;
                    $processedMerchantCount++;
                    unset($cron_array[$key]);

                    Yii::$app->dbConnection = Yii::$app->get($merchant['db_name']);
                    Yii::$app->merchant_id = $merchant['merchant_id'];
                    Yii::$app->shop_name = $merchant['shop_name'];

                    $query = "SELECT `config`.`seller_id`, `config`.`authorization`,`config`.`secret_key`,`shop`.`purchase_status`, `shop`.`token`, `shop`.`email`,`shop`.`app_status`, `shop`.`install_status`, `shop`.`currency` FROM `newegg_can_configuration` `config` INNER JOIN (SELECT `token`, `app_status`, `install_status`,`purchase_status`,`email`, `merchant_id`, `currency` FROM `newegg_can_shop_detail` WHERE `merchant_id`='{$merchant['merchant_id']}') `shop` ON `shop`.`merchant_id` = `config`.`merchant_id` WHERE `config`.`merchant_id`='{$merchant['merchant_id']}'";

                    $configData = Data::sqlRecords($query, 'one');

                    if ($configData) {
                        $isValidate = Neweggappdetail::validateApiCredentials($configData['seller_id'], $configData['secret_key'], $configData['authorization']);

                        if (!$configData['install_status'] || $configData['purchase_status'] == "License Expired" || $configData['purchase_status'] == "Trail Expired" || $configData['app_status']=="uninstall") {
                            continue;
                        }

                        $configData['merchant_id'] = $merchant['merchant_id'];
                        $configData['shop_url'] = $merchant['shop_name'];

                        /*$obj = new WalmartorderdetailController(Yii::$app->controller->id,'');
                        $obj->actionCreate($configData);*/
                        $obj = new NeweggorderdetailController(Yii::$app->controller->id, '');
                        $obj->actionOrderdetails($configData,true);
                    }
                } catch (Exception $e) {
                    Data::createLog("order fetch exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\IntegrityException $e) {
                    Data::createLog("order fetch db-integrity-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\Exception $e) {
                    Data::createLog("order fetch db-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                }
            }
        }

        if (count($cron_array) == 0)
            $result->cron_data = "";
        else
            $result->cron_data = json_encode($cron_array);

        $result->save(false);
        $html = ob_get_clean();
    }

    public function actionNeweggordersync()
    {
        /*$obj = new NeweggorderdetailController(Yii::$app->controller->id, '');
        $obj->actionSyncorder(true);*/
        ob_start();

        $connection = Yii::$app->get(Yii::$app->getBaseDb());

        $dbList = Yii::$app->getDbList();

        $processedMerchantCount = 0;
        $size = 100;

        $result = NeweggCanCronSchedule::find()->where(['cron_name' => 'sync_order'])->one();
        if ($result && $result['cron_data'] != "") {
            $cron_array = json_decode($result['cron_data'], true);
        } else {
            $cron_array = $connection->createCommand("SELECT * FROM `merchant_db` WHERE `app_name` LIKE '%" . Data::APP_NAME_NEWEGG_CAN . "%'")->queryAll();
        }

        if (is_array($cron_array) && count($cron_array)) {
            foreach ($cron_array as $key => $merchant) {
                try {

                    if (!in_array($merchant['db_name'], $dbList)) {
                        unset($cron_array[$key]);
                        continue;
                    }

                    if ($processedMerchantCount == $size)
                        break;
                    $processedMerchantCount++;
                    unset($cron_array[$key]);

                    Yii::$app->dbConnection = Yii::$app->get($merchant['db_name']);
                    Yii::$app->merchant_id = $merchant['merchant_id'];
                    Yii::$app->shop_name = $merchant['shop_name'];

                    $query = "SELECT `config`.`seller_id`, `config`.`authorization`,`config`.`secret_key`,`shop`.`purchase_status`, `shop`.`token`, `shop`.`email`,`shop`.`app_status`, `shop`.`install_status`, `shop`.`currency` FROM `newegg_can_configuration` `config` INNER JOIN (SELECT `token`, `app_status`, `install_status`,`purchase_status`,`email`, `merchant_id`, `currency` FROM `newegg_can_shop_detail` WHERE `merchant_id`='{$merchant['merchant_id']}') `shop` ON `shop`.`merchant_id` = `config`.`merchant_id` WHERE `config`.`merchant_id`='{$merchant['merchant_id']}'";

                    $configData = Data::sqlRecords($query, 'one');

                    if ($configData) {
                        $isValidate = Neweggappdetail::validateApiCredentials($configData['seller_id'], $configData['secret_key'], $configData['authorization']);

                        if (!$configData['install_status'] || $configData['purchase_status'] == "License Expired" || $configData['purchase_status'] == "Trail Expired" || $configData['app_status']=="uninstall") {
                            continue;
                        }

                        $configData['merchant_id'] = $merchant['merchant_id'];
                        $configData['shop_url'] = $merchant['shop_name'];

                        $obj = new NeweggorderdetailController(Yii::$app->controller->id, '');
                        $obj->actionSyncorder($configData,true);
                    }
                } catch (Exception $e) {
                    Data::createLog("order sync exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\IntegrityException $e) {
                    Data::createLog("order sync db-integrity-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\Exception $e) {
                    Data::createLog("order sync db-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                }
            }
        }

        if (count($cron_array) == 0)
            $result->cron_data = "";
        else
            $result->cron_data = json_encode($cron_array);

        $result->save(false);
        $html = ob_get_clean();
    }

    /*public function actionNeweggPriceUpdate(){
        $connection = Yii::$app->getDb();
        $query='select jet.id,jet.sku,jet.type,jet.price,jet.merchant_id,nsd.currency,nsd.country_code,nsd.purchase_status from `newegg_product` ngg INNER JOIN `jet_product` jet ON jet.id=ngg.product_id INNER JOIN `newegg_shop_detail` nsd ON nsd.merchant_id=ngg.merchant_id where ngg.upload_status!="'.Data::PRODUCT_STATUS_NOT_UPLOADED.'" and nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");
        ProductPrice::updatePriceOnNewegg($product,false,$connection,true);

    }

    public function actionNeweggInventoryUpdate(){
        $connection = Yii::$app->getDb();
        $query='select jet.id,jet.sku,jet.type,jet.qty,jet.merchant_id,nsd.currency,nsd.country_code,nsd.purchase_status from `newegg_product` ngg INNER JOIN `jet_product` jet ON jet.id=ngg.product_id INNER JOIN `newegg_shop_detail` nsd ON nsd.merchant_id=ngg.merchant_id where ngg.upload_status!="'.Data::PRODUCT_STATUS_NOT_UPLOADED.'" and nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");
        ProductInventory::updateInventoryOnNewegg($product,false,$connection,true);

    }*/

    public function actionNeweggPriceUpdate()
    {
        /*$connection = Yii::$app->getDb();
        $query = 'select jet.id,jet.sku,jet.type,jet.price,jet.merchant_id,nsd.currency,nsd.country_code,nsd.purchase_status from `newegg_product` ngg INNER JOIN `jet_product` jet ON jet.id=ngg.product_id INNER JOIN `newegg_shop_detail` nsd ON nsd.merchant_id=ngg.merchant_id where ngg.upload_status!="' . Data::PRODUCT_STATUS_NOT_UPLOADED . '" and nsd.purchase_status = "' . Data::PURCHASE_STATUS_TRAIL . '" OR nsd.purchase_status = "' . Data::PURCHASE . '"';
        $product = Data::sqlRecords($query, "all", "select");
        ProductPrice::updatePriceOnNewegg($product, false, $connection, true);*/
        ob_start();

        $connection = Yii::$app->get(Yii::$app->getBaseDb());

        $dbList = Yii::$app->getDbList();

        $processedMerchantCount = 0;
        $size = 100;

        $result = NeweggCanCronSchedule::find()->where(['cron_name' => 'price_update'])->one();
        if ($result && $result['cron_data'] != "") {
            $cron_array = json_decode($result['cron_data'], true);
        } else {
            $cron_array = $connection->createCommand("SELECT * FROM `merchant_db` WHERE `app_name` LIKE '%" . Data::APP_NAME_NEWEGG_CAN . "%'")->queryAll();
        }

        if (is_array($cron_array) && count($cron_array)) {
            foreach ($cron_array as $key => $merchant) {
                try {

                    if (!in_array($merchant['db_name'], $dbList)) {
                        unset($cron_array[$key]);
                        continue;
                    }

                    if ($processedMerchantCount == $size)
                        break;
                    $processedMerchantCount++;
                    unset($cron_array[$key]);

                    Yii::$app->dbConnection = Yii::$app->get($merchant['db_name']);
                    Yii::$app->merchant_id = $merchant['merchant_id'];
                    Yii::$app->shop_name = $merchant['shop_name'];

                    $query = "SELECT `config`.`seller_id`, `config`.`authorization`,`config`.`secret_key`,`shop`.`purchase_status`, `shop`.`token`, `shop`.`email`,`shop`.`app_status`, `shop`.`install_status`, `shop`.`currency` FROM `newegg_can_configuration` `config` INNER JOIN (SELECT `token`, `app_status`, `install_status`,`purchase_status`,`email`, `merchant_id`, `currency` FROM `newegg_can_shop_detail` WHERE `merchant_id`='{$merchant['merchant_id']}') `shop` ON `shop`.`merchant_id` = `config`.`merchant_id` WHERE `config`.`merchant_id`='{$merchant['merchant_id']}'";

                    $configData = Data::sqlRecords($query, 'one');

                    if ($configData) {
                        $isValidate = Neweggappdetail::validateApiCredentials($configData['seller_id'], $configData['secret_key'], $configData['authorization']);

                        if (!$configData['install_status'] || $configData['purchase_status'] == "License Expired" || $configData['purchase_status'] == "Trail Expired" || $configData['app_status']=="uninstall") {
                            continue;
                        }

                        $configData['merchant_id'] = $merchant['merchant_id'];
                        $configData['shop_url'] = $merchant['shop_name'];

                        $obj = new NeweggproductController(Yii::$app->controller->id, '');
                        $obj->actionPriceupdate($configData,true);
                    }
                } catch (Exception $e) {
                    Data::createLog("order sync exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\IntegrityException $e) {
                    Data::createLog("order sync db-integrity-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\Exception $e) {
                    Data::createLog("order sync db-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                }
            }
        }

        if (count($cron_array) == 0)
            $result->cron_data = "";
        else
            $result->cron_data = json_encode($cron_array);

        $result->save(false);
        $html = ob_get_clean();
    }

    public function actionNeweggInventoryUpdate()
    {
        /*$connection = Yii::$app->getDb();
        $query = 'select jet.id,jet.sku,jet.type,jet.qty,jet.merchant_id,nsd.currency,nsd.country_code,nsd.purchase_status from `newegg_product` ngg INNER JOIN `jet_product` jet ON jet.id=ngg.product_id INNER JOIN `newegg_shop_detail` nsd ON nsd.merchant_id=ngg.merchant_id where ngg.upload_status!="' . Data::PRODUCT_STATUS_NOT_UPLOADED . '" and nsd.purchase_status = "' . Data::PURCHASE_STATUS_TRAIL . '" OR nsd.purchase_status = "' . Data::PURCHASE . '"';
        $product = Data::sqlRecords($query, "all", "select");
        ProductInventory::updateInventoryOnNewegg($product, false, $connection, true);*/
        ob_start();

        $connection = Yii::$app->get(Yii::$app->getBaseDb());

        $dbList = Yii::$app->getDbList();

        $processedMerchantCount = 0;
        $size = 100;

        $result = NeweggCanCronSchedule::find()->where(['cron_name' => 'update_inventory'])->one();
        if ($result && $result['cron_data'] != "") {
            $cron_array = json_decode($result['cron_data'], true);
        } else {
            $cron_array = $connection->createCommand("SELECT * FROM `merchant_db` WHERE `app_name` LIKE '%" . Data::APP_NAME_NEWEGG_CAN . "%'")->queryAll();
        }

        if (is_array($cron_array) && count($cron_array)) {
            foreach ($cron_array as $key => $merchant) {
                try {

                    if (!in_array($merchant['db_name'], $dbList)) {
                        unset($cron_array[$key]);
                        continue;
                    }

                    if ($processedMerchantCount == $size)
                        break;
                    $processedMerchantCount++;
                    unset($cron_array[$key]);

                    Yii::$app->dbConnection = Yii::$app->get($merchant['db_name']);
                    Yii::$app->merchant_id = $merchant['merchant_id'];
                    Yii::$app->shop_name = $merchant['shop_name'];

                    $query = "SELECT `config`.`seller_id`, `config`.`authorization`,`config`.`secret_key`,`shop`.`purchase_status`, `shop`.`token`, `shop`.`email`,`shop`.`app_status`, `shop`.`install_status`, `shop`.`currency` FROM `newegg_can_configuration` `config` INNER JOIN (SELECT `token`, `app_status`, `install_status`,`purchase_status`,`email`, `merchant_id`, `currency` FROM `newegg_can_shop_detail` WHERE `merchant_id`='{$merchant['merchant_id']}') `shop` ON `shop`.`merchant_id` = `config`.`merchant_id` WHERE `config`.`merchant_id`='{$merchant['merchant_id']}'";

                    $configData = Data::sqlRecords($query, 'one');

                    if ($configData) {
                        $isValidate = Neweggappdetail::validateApiCredentials($configData['seller_id'], $configData['secret_key'], $configData['authorization']);

                        if (!$configData['install_status'] || $configData['purchase_status'] == "License Expired" || $configData['purchase_status'] == "Trail Expired" || $configData['app_status']=="uninstall") {
                            continue;
                        }

                        $configData['merchant_id'] = $merchant['merchant_id'];
                        $configData['shop_url'] = $merchant['shop_name'];

                        $obj = new NeweggproductController(Yii::$app->controller->id, '');
                        $obj->actionInventoryupdate($configData,true);
                    }
                } catch (Exception $e) {
                    Data::createLog("order sync exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\IntegrityException $e) {
                    Data::createLog("order sync db-integrity-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                } catch (\yii\db\Exception $e) {
                    Data::createLog("order sync db-exception " . $e->getTraceAsString(), 'walmartOrderCron/exception.log', 'a', true);
                    if (isset($cron_array[$key]))
                        unset($cron_array[$key]);
                    continue;
                }
            }
        }

        if (count($cron_array) == 0)
            $result->cron_data = "";
        else
            $result->cron_data = json_encode($cron_array);

        $result->save(false);
        $html = ob_get_clean();
    }

    /*public function actionNeweggStatusRequest(){
        $connection = Yii::$app->getDb();
        $query='select `merchant_id` from `newegg_shop_detail` nsd where nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'"';
        $product = Data::sqlRecords($query,"all","select");
        ProductStatus::updateProductStatusRequest($product);
    }

    public function actionNeweggStatusRequestResult(){
        $connection = Yii::$app->getDb();
        $query='select nsd.merchant_id,npf.feed_id,npf.request_for from `newegg_shop_detail` nsd RIGHT JOIN `newegg_product_feed` npf ON npf.merchant_id=nsd.merchant_id where  nsd.purchase_status = "'.Data::PURCHASE_STATUS_TRAIL.'" OR nsd.purchase_status = "'.Data::PURCHASE.'" AND npf.request_for="'.Data::FEED_PRODUCT_STATUS.'" AND npf.status="'.Data::PRODUCT_STATUS_SUBMITTED.'"';
        $product = Data::sqlRecords($query,"all","select");
        ProductStatus::updateProductStatusResult($product);

    }*/

}