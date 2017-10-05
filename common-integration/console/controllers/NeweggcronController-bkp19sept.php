<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 13/1/17
 * Time: 12:42 PM
 */
namespace console\controllers;
use frontend\modules\neweggmarketplace\controllers\NeweggorderdetailController;
use frontend\modules\neweggmarketplace\components\product\ProductPrice;
use frontend\modules\neweggmarketplace\components\product\ProductInventory;
use frontend\modules\neweggmarketplace\components\product\ProductStatus;
use frontend\modules\neweggmarketplace\components\Data;
use yii\console\Controller;
use Yii;
use yii\web;
/**
 * Cron controller
 */
class NeweggcronController extends Controller
{
    public function actionIndex()
    {
        ob_start();
        echo "cron service runnning";
        echo getcwd();
        $html = ob_get_clean();
    }

    public function actionNeweggorderdetails(){
        $obj = new NeweggorderdetailController(Yii::$app->controller->id,'');
        $obj->actionOrderdetails(true);
    }
    public function actionNeweggordersync(){
        $obj = new NeweggorderdetailController(Yii::$app->controller->id,'');
        $obj->actionSyncorder(true);
    }

    public function actionNeweggPriceUpdate(){
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
        
    }

    public function actionNeweggStatusRequest(){
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
        
    }

}