<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Sendmail;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\WalmartPromoStatus;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\WalmartReport;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\WalmartProduct;
use frontend\modules\walmart\components\ShopifyClientHelper;

class TestterController extends WalmartmainController
{
    public function actionTest()
    {
     
                $connection=Yii::$app->getDb();
               
                $query="SELECT `merchant_id`  FROM `walmart_extension_detail`";
                $emailtemp = $connection->createCommand($query)->queryAll();
                
                 $query="SELECT * FROM `email_template`";
                $email = Data::sqlRecords($query,"all");
               
                foreach ($email as $key => $value) {
                        $emailConfiguration['email/'.$value['template_title']] = isset($value["template_title"])?1:0;
                    }

                    if(!empty($emailConfiguration)){
                        foreach ($emailtemp as $temp => $tempdata) {
                          foreach ($emailConfiguration as $key => $value) 
                              {
                                  Data::saveConfigValue($tempdata['merchant_id'], $key, $value);
                              }
                      }
                    }
                
               die("jjjjj");
    }

    public function actionTest1()
    {
      $report = new WalmartReport(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
      var_dump($report->downloadItemReport('656'));die('success');
      //$reprice->fetchWalmartBuyboxReport(null, true);
    }

    public function actionIndex()
    {
      //$obj = WalmartPromoStatus::getPromoStatus([]); 
      /*$obj = new Walmartapi(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
      $data = $obj->getOrders();
      var_dump($data);die;*/
      //$obj->updateBulkPromotionalPriceOnWalmart('6271635717,565551465');
      //$obj->testPromo();

      /*$reprice = new WalmartRepricing();
      //$reprice->getBestMarketplacePrice('13214741');
      //print_r($reprice->getBestMarketplacePrice(['54596245','13213408']));
      print_r($reprice->getBestMarketplacePrice('021200507014', true));*/
      //var_dump(Jetproductinfo::validateSkuForExistingProduct('WR315LE'));die;

      $data = Data::sqlRecords("SELECT `product_type`, `category_path` FROM `walmart_category_map` WHERE `category_path` != '' AND `category_path` IS NOT NULL", 'all');
      $data = array_column($data, 'category_path', 'product_type');
      var_dump($data);die;
    }

    public function actionIndex1()
    {
      /*$obj = new WalmartRepricing(API_USER,API_PASSWORD,CONSUMER_CHANNEL_TYPE_ID);
      //$response = $obj->fetchWalmartProductReport();

      $product = $obj->getProductData('361133737');

      $price = $obj->getProductPrice($product, 'simple', $product['product_id'], MERCHANT_ID);
      print_r($price);die;*/

      //calculateBestPrice($orignal_price,$bestMktPrice,$minPrice,$maxPrice)
      //var_dump(WalmartRepricing::calculateBestPrice(8,7.6,6,10));die;
      var_dump(WalmartRepricing::calculateBestPrice(17,18,16,20));die;
    }

    public function actionIndex2()
    {
      $filePath = \Yii::getAlias('@webroot').'/var/report/'.MERCHANT_ID.'/item/';
      $fileName = $filePath.'test.zip';
      var_dump($fileName);die;
      $zip = new \ZipArchive;
      if ($zip->open($fileName) === TRUE) {
          $zip->extractTo($filePath);
          $zip->close();
          echo 'ok';
      } else {
          echo 'failed';
      }
    }

    public function actionSingleupdate()
    {
      $reprice = new WalmartProduct(API_USER,API_PASSWORD);
      $reprice->updateSinglePrice(['price'=>'10','sku'=>'test','currency'=>'USD']);
    }

    public function actionInternalimport()
    {
        $missing = [];
        $merchant_id = '';
        if($merchant_id=='')
          $merchant_id = Yii::$app->user->identity->id;

        $query = "SELECT * FROM `jet_product` WHERE `merchant_id` = {$merchant_id}";
        $jet_products = Data::sqlRecords($query, 'all');

        if($jet_products)
        {
            foreach ($jet_products as $jet_product) 
            {
                $checkExist = Data::sqlRecords("SELECT `id` FROM `walmart_product` WHERE `product_id` = {$jet_product['id']}", 'one');
                if(!$checkExist)
                {
                    $missing[] = $jet_product;

                    $product_id = $jet_product['id'];

                    $product_type = addslashes($jet_product['product_type']);

                    $category = self::insertProductType($merchant_id, $jet_product['product_type']);
                    if($category['category_id']!='NULL') {
                        $category = "'".$category['category_id']."'";
                    } else {
                        $category = $category['category_id'];
                    }


                    $status = WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED;

                    $insertQuery = "INSERT INTO `walmart_product`(`product_id`, `merchant_id`, `product_type`, `category`,`status`) VALUES ({$product_id},{$merchant_id},'{$product_type}',{$category},'{$status}')";
                    $insert = Data::sqlRecords($insertQuery, null, 'insert');

                    echo($jet_product['id']);
                    echo "<br>";
                    echo "Insert Status : ".$insert;
                    echo "<br>";
                }
            }
        }
    }

    public function insertProductType($merchant_id, $product_type)
    {
        //add product type in walmart
        $query = 'SELECT `category_id` FROM `walmart_category_map` where merchant_id="'.$merchant_id.'" AND product_type="'.addslashes($product_type).'" LIMIT 0,1';
        $walmodelmap = Data::sqlRecords($query,"one","select");
        if($walmodelmap)
        {
            if(is_null($walmodelmap['category_id']))
                return ['category_id'=>'NULL'];
            else
                return $walmodelmap;
        }
        else
        {
            $queryObj="";
            $query='INSERT INTO `walmart_category_map`(`merchant_id`,`product_type`)VALUES("'.$merchant_id.'","'.addslashes($data['product_type']).'")';
            Data::sqlRecords($query);
        }
        return ['category_id'=>'NULL'];
    }

    public function actionGetService()
    {
      $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
      $services = $sc->call('GET', '/admin/fulfillment_services.json', []);
      var_dump($services);die;
    }
}
