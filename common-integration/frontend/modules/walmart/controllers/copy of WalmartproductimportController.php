<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Jetproductinfo;

class WalmartproductimportController extends Controller
{

    protected $connection;

    protected $shopifyClientHelper;

    const MAX_CUSTOM_PRODUCT_IMPORT_PER_REQUEST = 50;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    public function beforeAction($action){
        Yii::$app->request->enableCsrfValidation = false;

        $merchant_id = Yii::$app->user->identity->id;
        $shopDetails = Data::getWalmartShopDetails($merchant_id);
        $shopname = Yii::$app->user->identity->username;
        $token = isset($shopDetails['token'])?$shopDetails['token']:'';

        $this->shopifyClientHelper = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);

        return parent::beforeAction($action);
    }
    /**
     * Lists all JetProduct models.
     * @return mixed
     */
    
    public function actionImport()
    {
        //$this->layout = 'main2';
        return $this->render('import');
    }

    public function actionGettotaldetails(){
        $result = [];
        $merchantId = isset($_REQUEST['merchant_id'])?$_REQUEST['merchant_id']:"";
        $select = isset($_REQUEST['select'])?$_REQUEST['select']:"";
        $select = $select=='custom'?'any':$select;
        $allowedSelectValues = ['any', 'published'];
        if($merchantId && in_array($select, $allowedSelectValues)){

            $shopDetails = Data::getWalmartShopDetails($merchantId);

            $connection = Yii::$app->getDb();
            define("MERCHANT_ID",Yii::$app->user->identity->id);
            define("SHOP",Yii::$app->user->identity->username);
            define("TOKEN",isset($shopDetails['token'])?$shopDetails['token']:'');
            $merchant_id = MERCHANT_ID?:$merchantId;
            $shopname = SHOP;
            $token = TOKEN;
            $countProducts = 0;
            $pages = 0;
            $index = 1;
            $nonSkuCount = 0;
            $nonProductType = 0;
            //$shopifymodel=Shopifyinfo::getShipifyinfo();
            $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
            $countProducts = $sc->call('GET', '/admin/products/count.json', ['published_status' => $select]);
            if(isset($countProducts['errors'])){
                $result['err'] = $countProducts['errors'];
                return json_encode($result);
            }

            $productData = [];
            $pages = ceil($countProducts/250);
            while($index <= $pages) {
                $products = "";
                $products = $sc->call('GET', '/admin/products.json', ['fields' => "id, title, variants, product_type",'published_status' => 'published','limit' => 250,'page' => $index]);
                if(isset($products['errors'])){
                    $result['err'] = $products['errors'];
                    return json_encode($result);
                }
                
                foreach($products as $prod) {
                    if(trim($prod['product_type']) == ''){
                        $nonProductType ++;
                        continue;
                    }

                    $varientArray = $prod['variants'];
                    usort($varientArray, array($this, "interchangeArray"));
                    $prod['variants'] = $varientArray;
                    foreach($prod['variants'] as $variant) {
                        if($variant['position'] == '1' && trim($variant['sku'])=="") {
                            $nonSkuCount ++;
                            break;
                        }
                    }
                    $productData[$prod['id']] = $prod;
                }
                $index ++;
            }
            $result ['total'] = $countProducts;    
            $result ['non_sku'] = $nonSkuCount;    
            $result ['ready'] = $countProducts - ($nonSkuCount + $nonProductType); 
            $result ['non_type'] = $nonProductType;
            $result ['csrf'] = Yii::$app->request->getCsrfToken();
            $result ['products'] = $productData;
            $inserted="";
            $resultSQL = array();
            $inserted = $connection->createCommand("SELECT `merchant_id` FROM `insert_product` WHERE merchant_id='".$merchant_id."'");
            $resultSQL = $inserted->queryOne();
            if(empty($resultSQL))
            {
                   $queryObj = "";
                   $query = 'INSERT INTO `insert_product`
                                (
                                    `merchant_id`,
                                    `product_count`,
                                    `not_sku`,
                                    `status`,
                                    `total_product`
                                )
                                VALUES(
                                    "'.$merchant_id.'",
                                    "'.($countProducts - $nonSkuCount).'",
                                    "'.$nonSkuCount.'",
                                    "inserted",
                                    "'.$countProducts.'"
                                )';
                    $queryObj = $connection->createCommand($query)->execute();
            }else{
                    $updateQuery = "UPDATE `insert_product` SET `product_count`='".($countProducts - $nonSkuCount)."' ,`total_product`='".$countProducts."', `not_sku`='".$nonSkuCount."' WHERE merchant_id='".$merchant_id."'";
                    $updated = $connection->createCommand($updateQuery)->execute();
            } 
        }
        return json_encode($result);
    }
    
    public function interchangeArray($a , $b){
        if ($a["position"] == $b["position"]) {
            return 0;
        }
        return ($a["position"] < $b["position"]) ? -1 : 1;
    }
    
    public function actionBatchimport(){
        $index = Yii::$app->request->post('index');
        $select = Yii::$app->request->post('select');
        $merchant_id = Yii::$app->request->post('merchant_id');
        $shopDetails = Data::getWalmartShopDetails($merchant_id);
        try
        {
            $sc = "";
            $connection = Yii::$app->getDb();
            define("MERCHANT_ID",Yii::$app->user->identity->id);
            define("SHOP",Yii::$app->user->identity->username);
            define("TOKEN",isset($shopDetails['token'])?$shopDetails['token']:'');
            $shopname = SHOP;
            $token = TOKEN;
            
            $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
            $products = $sc->call('GET', '/admin/products.json', ['published_status' => $select, 'limit' => 250, 'page' => $index]);
            if(isset($products['errors'])){
                $returnArr['error'] = $products['errors'];
                return json_encode($returnArr);
            }
            $readyCount = 0;
            $notSku = 0;
            $notType = 0;
            if($products){
                foreach ($products as $prod){
                    $noSkuFlag = 0;
                    if(trim($prod['product_type'])==''){
                        $notType ++;
                        continue;
                    }
                    $value = $prod;
                    $varientArray = $prod['variants'];
                    usort($varientArray, array($this, "interchangeArray"));
                    $value['variants'] = $varientArray;
                    foreach($value['variants'] as $variant) {
                        if($variant['position'] == '1' && trim($variant['sku'])=="") {
                            $noSkuFlag = 1;
                            $notSku ++;
                            break;
                        }
                    }
                    
                    if(!$noSkuFlag){
                        $readyCount ++;
                        Jetproductinfo::saveNewRecords($value, $merchant_id, $connection);
                    }
                    
                }
            }
          
        }
        catch (ShopifyApiException $e){
            return $returnArr['error'] = $e->getMessage();
        }
        catch (ShopifyCurlException $e){
            return $returnArr['error'] = $e->getMessage();
        }
        $returnArr['success']['count'] = $readyCount;
        $returnArr['success']['not_sku'] = $notSku;
        $returnArr['success']['not_type'] = $notType;
        $connection->close();
        return json_encode($returnArr);
    }

    public function actionCustomImport()
    {
        $productIds = Yii::$app->request->post('product_ids',false);
        $page = Yii::$app->request->post('page', false);
        if($productIds && $page!==false)
        {
            try
            {
                $merchant_id = Yii::$app->user->identity->id;
                $connection = Yii::$app->getDb();
                $max = self::MAX_CUSTOM_PRODUCT_IMPORT_PER_REQUEST;
                $ids = array_chunk($productIds, $max);
                //foreach ($ids as $id) 
                if(isset($ids[$page]))
                {
                    $id =  $ids[$page];

                    $product_ids = implode(',', $id);
                    $products = $this->shopifyClientHelper->call('GET', '/admin/products.json?ids='.$product_ids, array());

                    if(isset($products['errors'])){
                        $returnArr['error'] = $products['errors'];
                        return json_encode($returnArr);
                    }

                    $readyCount = 0;
                    $notSku = 0;
                    $notType = 0;
                    if($products){
                        foreach ($products as $prod){
                            $noSkuFlag = 0;
                            if(trim($prod['product_type'])==''){
                                $notType ++;
                                continue;
                            }
                            $value = $prod;
                            $varientArray = $prod['variants'];
                            usort($varientArray, array($this, "interchangeArray"));
                            $value['variants'] = $varientArray;
                            foreach($value['variants'] as $variant) {
                                if($variant['position'] == '1' && trim($variant['sku'])=="") {
                                    $noSkuFlag = 1;
                                    $notSku ++;
                                    break;
                                }
                            }
                            
                            if(!$noSkuFlag){
                                $readyCount ++;
                                Jetproductinfo::saveNewRecords($value, $merchant_id, $connection);
                            }
                            
                        }
                    }
                }
            }
            catch (ShopifyApiException $e){
                return $returnArr['error'] = $e->getMessage();
            }
            catch (ShopifyCurlException $e){
                return $returnArr['error'] = $e->getMessage();
            }

            $returnArr['success']['count'] = $readyCount;
            $returnArr['success']['not_sku'] = $notSku;
            $returnArr['success']['not_type'] = $notType;
            $connection->close();
            return json_encode($returnArr);
        }
        else
        {
            return json_encode(['error'=>'No product selected for import.']);
        }
    }
}
