<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\PricingPlaninfo;

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

    public function beforeAction($action)
    {
        Yii::$app->request->enableCsrfValidation = false;
        $merchant_id = Yii::$app->user->identity->id;
        $shopDetails = Data::getWalmartShopDetails($merchant_id);
        $shopname = Yii::$app->user->identity->username;
        $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';

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

    public function actionGettotaldetails()
    {
        $result = [];
        $session = Yii::$app->session;
        $merchantId = isset($_REQUEST['merchant_id']) ? $_REQUEST['merchant_id'] : "";
        $non_sku_total = isset($_REQUEST['non_sku_total']) ? $_REQUEST['non_sku_total'] : 0;
        $non_type_total = isset($_REQUEST['non_type_total']) ? $_REQUEST['non_type_total'] : 0;
        $total_variants_product = isset($_REQUEST['total_variants_prod']) ? $_REQUEST['total_variants_prod'] : 0;
        $select = isset($_REQUEST['select']) ? $_REQUEST['select'] : "";
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 250;
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
        $select = $select == 'custom' ? 'any' : $select;
        $allowedSelectValues = ['any', 'published'];
        if ($merchantId && in_array($select, $allowedSelectValues)) {

            $shopDetails = Data::getWalmartShopDetails($merchantId);

            $connection = Yii::$app->getDb();
            define("MERCHANT_ID", Yii::$app->user->identity->id);
            define("SHOP", Yii::$app->user->identity->username);
            define("TOKEN", isset($shopDetails['token']) ? $shopDetails['token'] : '');
            $merchant_id = MERCHANT_ID ?: $merchantId;
            $shopname = SHOP;
            $token = TOKEN;
            $countProducts = 0;
            $pages = 0;
            $index = 1;
            $nonSkuCount = 0;
            $nonProductType = 0;
            $currentTotalProd = 0;
            $prod_count = 0;
            $sameSku = "";
            $notSku = "";
            $notProductType = "";
            $sameSkuArray = [];
            if (isset($session[$merchant_id . 'samesku'])) {
                $sameSkuArray = $session[$merchant_id . 'samesku'];
            }
            $sameBarcodeArray = [];
            if (isset($session[$merchant_id . 'samebarcode'])) {
                $sameBarcodeArray = $session[$merchant_id . 'samebarcode'];
            }
            /* if (file_exists(\Yii::getAlias('@webroot').'/var/importproduct/'.$merchant_id.'/sku.txt')) {
                 $sameSkuArray=json_decode(file_get_contents(\Yii::getAlias('@webroot').'/var/importproduct/'.$merchant_id.'/sku.txt'),true);
             }*/
            $notSkuArray = [];
            $notProductTypeArray = [];
            //$shopifymodel=Shopifyinfo::getShipifyinfo();
            $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
            $countProducts = $sc->call('GET', '/admin/products/count.json', ['published_status' => $select]);


            if (isset($countProducts['errors'])) {
                $result['err'] = $countProducts['errors'];
                return json_encode($result);
            }

            $productData = [];
            $pages = ceil($countProducts / $limit);
            if ($page <= $pages) {//while($index <= $pages) {
                $products = "";
                $products = $sc->call('GET', '/admin/products.json', ['fields' => "id, title, variants, product_type", 'published_status' => $select, 'limit' => $limit, 'page' => $page]);
                if (isset($products['errors'])) {
                    $result['err'] = $products['errors'];
                    return json_encode($result);
                }
                $currentTotalProd = count($products);
                foreach ($products as $prod) {
                    if (trim($prod['product_type']) == '') {
                        $notProductType .= ',' . $prod['id'];
                        $nonProductType++;
                        continue;
                    }
                    $fg = true;
                    $varientArray = $prod['variants'];
                    usort($varientArray, array($this, "interchangeArray"));
                    $prod['variants'] = $varientArray;
                    foreach ($prod['variants'] as $variant) {
                        if ($variant['position'] == '1' && trim($variant['sku']) == "") {
                            $nonSkuCount++;
                            $notSku .= ',' . $prod['id'];
                            $fg = false;
                            break;
                        }
                        if (empty($sameSkuArray)) {
                            $skuKey = Data::getKey($variant['sku']);
                            $sameSkuArray[$skuKey] = '1';
                        } else {
                            if (isset($sameSkuArray[$variant['sku']])) {
                                $sameSku .= ',' . $prod['id'];
                                $fg = false;
                                break;
                            } else {
                                $skuKey = Data::getKey($variant['sku']);
                                $sameSkuArray[$skuKey] = '1';
                            }
                        }

                        $prod_count++;
                    }
                    if (!$fg) {
                        continue;
                    }
                    $productData[$prod['id']] = $prod;
                }
                //$index ++;
            }
            //var_dump($sameSku);
           /* $product_limit_count = PricingPlaninfo::getProductlimit($merchant_id);
            if ($prod_count>$product_limit_count)
            {
                $result['err'] = 'You have '.$prod_count. ' product(s) including variants which is beyond the limit. Contact us at <a target="_blank" href="mailto:shopify@cedcommerce.com">shopify@cedcommerce.com</a> for importing your products.';
                return json_encode($result);
            }*/
//            $result ['total'] = $prod_count;
            /*if($total_variants_product == 2839)
            {
                $result['err'] = 'You have '.$total_variants_product. ' product(s) including variants which is beyond the limit. Contact us at <a target="_blank" href="mailto:shopify@cedcommerce.com">shopify@cedcommerce.com</a> for importing your products.';
                return json_encode($result);
            }*/
            $result ['total'] = $countProducts;
            $result ['non_sku'] = $nonSkuCount;
            $result ['ready'] = $currentTotalProd - ($nonSkuCount + $nonProductType);
            $result ['non_type'] = $nonProductType;
            $result ['csrf'] = Yii::$app->request->getCsrfToken();
            $result ['total_variants_prod'] = $prod_count;
            $result ['products'] = $productData;

            $non_sku_total += $nonSkuCount;
            $non_type_total += $nonProductType;
            $total_variants_product += $prod_count;
            $merchant_id = Yii::$app->user->identity->id;
            if (!file_exists(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id)) {
                mkdir(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id, 0775, true);
            }
            $base_path = \Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/samesku.txt';
            $file = fopen($base_path, "a");
            fwrite($file, $sameSku);
            fclose($file);
            $base_path = \Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/notsku.txt';
            $file = fopen($base_path, "a");
            fwrite($file, $notSku);
            fclose($file);
            $base_path = \Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/notProductType.txt';
            $file = fopen($base_path, "a");
            fwrite($file, $notProductType);
            fclose($file);
            if (count($sameSkuArray) > 0) {
                $session[$merchant_id . 'samesku'] = $sameSkuArray;
            }
            if ($page == $pages) {
                $inserted = "";
                $resultSQL = array();
                $inserted = $connection->createCommand("SELECT `merchant_id` FROM `insert_product` WHERE merchant_id='" . $merchant_id . "'");
                $resultSQL = $inserted->queryOne();
                if (empty($resultSQL)) {
                    $queryObj = "";
                    $query = 'INSERT INTO `insert_product`
                                        (
                                            `merchant_id`,
                                            `product_count`,
                                            `not_sku`,
                                            `status`,
                                            `total_product`,
                                            `total_variant_products`
                                        )
                                        VALUES(
                                            "' . $merchant_id . '",
                                            "' . ($countProducts - ($non_sku_total + $non_type_total)) . '",
                                            "' . ($non_sku_total + $non_type_total) . '",
                                            "inserted",
                                            "' . $countProducts . '",
                                            "'. $total_variants_product.'"
                                        )';
                    /*$query = 'INSERT INTO `insert_product`
                                        (
                                            `merchant_id`,
                                            `product_count`,
                                            `not_sku`,
                                            `status`,
                                            `total_product`
                                        )
                                        VALUES(
                                            "' . $merchant_id . '",
                                            "' . ($countProducts - ($non_sku_total + $non_type_total)) . '",
                                            "' . ($non_sku_total + $non_type_total) . '",
                                            "inserted",

                                            "' . $countProducts . '",
                                        )';*/
                    $queryObj = $connection->createCommand($query)->execute();
                } else {
                    $updateQuery = "UPDATE `insert_product` SET `product_count`='" . ($countProducts - ($non_sku_total + $non_type_total)) . "' ,`total_product`='" . $countProducts . "', `not_sku`='" . ($non_sku_total + $non_type_total) . "' , `total_variant_products`='".$total_variants_product."' WHERE merchant_id='" . $merchant_id . "'";
                    //$updateQuery = "UPDATE `insert_product` SET `product_count`='" . ($countProducts - ($non_sku_total + $non_type_total)) . "' ,`total_product`='" . $countProducts . "', `not_sku`='" . ($non_sku_total + $non_type_total) . "' WHERE merchant_id='" . $merchant_id . "'";
                    $updated = $connection->createCommand($updateQuery)->execute();
                }

                // Add product variants count in cache.
                $key = $merchant_id.'total__variants_products';
                //\Yii::$app->cache->set($key, $prod_count, 86400); // time in seconds to store cache
                \Yii::$app->cache->set($key, $total_variants_product); // time in seconds to store cache

            }

        }
        /*print_r($result);die;*/
        return json_encode($result);
    }

    public function interchangeArray($a, $b)
    {
        if ($a["position"] == $b["position"]) {
            return 0;
        }
        return ($a["position"] < $b["position"]) ? -1 : 1;
    }

    public function actionBatchimport()
    {
        $session = Yii::$app->session;
        $sameSkuArray = [];
        $custom_sku = Yii::$app->request->post('customsku');
        $create_custom = Yii::$app->request->post('create_custom');
        $index = Yii::$app->request->post('index');
        $select = Yii::$app->request->post('select');
        $merchant_id = Yii::$app->request->post('merchant_id');
        if (isset($session[$merchant_id . 'batchsamesku'])) {
            $sameSkuArray = $session[$merchant_id . 'batchsamesku'];
        }
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 250;
        /*if ($limit < 250) {
            $product_limit_count = $limit;
            $limit=250;
        }*/
        $shopDetails = Data::getWalmartShopDetails($merchant_id);
        try {
            $sc = "";
            $connection = Yii::$app->getDb();
            define("MERCHANT_ID", Yii::$app->user->identity->id);
            define("SHOP", Yii::$app->user->identity->username);
            define("TOKEN", isset($shopDetails['token']) ? $shopDetails['token'] : '');
            $shopname = SHOP;
            $token = TOKEN;

            $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
            $products = $sc->call('GET', '/admin/products.json', ['published_status' => $select, 'limit' => $limit, 'page' => $index]);
            Data::saveConfigValue($merchant_id, 'import_product_option', $select);
            if (isset($products['errors'])) {
                $returnArr['error'] = $products['errors'];
                return json_encode($returnArr);
            }
            $readyCount = 0;
            $notSku = 0;
            $notType = 0;
            $sameskucount = 0;
            if ($products) {
                foreach ($products as $prod) {
                    $noSkuFlag = 0;
                    if (trim($prod['product_type']) == '') {
                        $notType++;
                        continue;
                    }
                    $value = $prod;
                    $varientArray = $prod['variants'];
                    usort($varientArray, array($this, "interchangeArray"));
                    $value['variants'] = $varientArray;
                    foreach ($value['variants'] as $key => $variant) {
                        if ($variant['position'] == '1' && trim($variant['sku']) == "") {
                            if ($create_custom == 'false') {
                                $noSkuFlag = 1;
                                $notSku++;
                                break;
                            } else {
                                $value['variants'][$key]['sku'] = Data::getCustomsku($variant['id']);
                                if ($custom_sku == 'true') {
                                    self::CreateSkuOnShopify($variant['id']);
                                }
                            }
                        }
                        if (empty($sameSkuArray)) {
                            $skuKey = Data::getKey($variant['sku']);
                            $sameSkuArray[$skuKey] = '1';
                        } else {
                            if (isset($sameSkuArray[$variant['sku']])) {
                                if ($create_custom == 'false') {
                                    $noSkuFlag = 1;
                                    $sameskucount++;
                                    break;
                                } else {
                                    $value['variants'][$key]['sku'] = Data::getCustomsku($variant['id']);
                                    if ($custom_sku == 'true') {
                                        self::CreateSkuOnShopify($variant['id']);
                                    }
                                }

                            } else {
                                $skuKey = Data::getKey($variant['sku']);
                                $sameSkuArray[$skuKey] = '1';
                            }
                        }
                    }
                    if (!$noSkuFlag) {
                         /*if ($readyCount==$product_limit_count)
                             continue;*/
                        Jetproductinfo::saveNewRecords($value, $merchant_id, $connection, $select);
                        $readyCount++;
                    }

                }
            }

        } catch (ShopifyApiException $e) {
            return $returnArr['error'] = $e->getMessage();
        } catch (ShopifyCurlException $e) {
            return $returnArr['error'] = $e->getMessage();
        }
        $returnArr['success']['count'] = $readyCount;
        $returnArr['success']['not_sku'] = $notSku;
        $returnArr['success']['not_type'] = $notType;
        $returnArr['success']['sameskucount'] = $sameskucount;
        if (count($sameSkuArray) > 0) {
            $session[$merchant_id . 'batchsamesku'] = $sameSkuArray;
        }
        $connection->close();
        return json_encode($returnArr);
    }

    public function actionCustomImport()
    {
        $productIds = Yii::$app->request->post('product_ids', false);
        $page = Yii::$app->request->post('page', false);
        if ($productIds && $page !== false) {
            try {
                $merchant_id = Yii::$app->user->identity->id;
                $connection = Yii::$app->getDb();
                $max = self::MAX_CUSTOM_PRODUCT_IMPORT_PER_REQUEST;
                $ids = array_chunk($productIds, $max);
                //foreach ($ids as $id) 
                if (isset($ids[$page])) {
                    $id = $ids[$page];

                    $product_ids = implode(',', $id);
                    $products = $this->shopifyClientHelper->call('GET', '/admin/products.json?ids=' . $product_ids, array());

                    if (isset($products['errors'])) {
                        $returnArr['error'] = $products['errors'];
                        return json_encode($returnArr);
                    }

                    $readyCount = 0;
                    $notSku = 0;
                    $notType = 0;
                    if ($products) {
                        foreach ($products as $prod) {
                            $noSkuFlag = 0;
                            if (trim($prod['product_type']) == '') {
                                $notType++;
                                continue;
                            }
                            $value = $prod;
                            $varientArray = $prod['variants'];
                            usort($varientArray, array($this, "interchangeArray"));
                            $value['variants'] = $varientArray;
                            foreach ($value['variants'] as $variant) {
                                if ($variant['position'] == '1' && trim($variant['sku']) == "") {
                                    $noSkuFlag = 1;
                                    $notSku++;
                                    break;
                                }
                            }

                            if (!$noSkuFlag) {
                                $readyCount++;
                                Jetproductinfo::saveNewRecords($value, $merchant_id, $connection);
                            }

                        }
                    }
                }
            } catch (ShopifyApiException $e) {
                return $returnArr['error'] = $e->getMessage();
            } catch (ShopifyCurlException $e) {
                return $returnArr['error'] = $e->getMessage();
            }

            $returnArr['success']['count'] = $readyCount;
            $returnArr['success']['not_sku'] = $notSku;
            $returnArr['success']['not_type'] = $notType;
            $connection->close();
            return json_encode($returnArr);
        } else {
            return json_encode(['error' => 'No product selected for import.']);
        }
    }

    /**
     * unlink file
     * @return boolean
     */
    public function actionFileexist()
    {
        $merchant_id = Yii::$app->user->identity->id;
        $session = Yii::$app->session;
        if (isset($session[$merchant_id . 'samesku'])) {
            unset($session[$merchant_id . 'samesku']);
        }
        if (isset($session[$merchant_id . 'batchsamesku'])) {
            unset($session[$merchant_id . 'batchsamesku']);
        }
        if (file_exists(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/samesku.txt')) {
            $files = glob(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/*'); // get all file names
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    unlink($file); // delete file
            }
        }
        return false;

    }

    /**
     * get importproduct.txt file content
     * @return string data
     */
    public function actionGetfilecontent()
    {
        $merchant_id = Yii::$app->user->identity->id;
        if (file_exists(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/samesku.txt')) {
            return file_get_contents(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/samesku.txt');
        }
        return false;

    }

    /**
     * get errored product info on shopify
     * @return array
     */
    public function actionViewerroredproduct($file)
    {
        //$this->layout = 'blank';
        $merchant_id = Yii::$app->user->identity->id;
        $query = "SELECT `shop_url` from `walmart_shop_details` where `merchant_id`=" . $merchant_id;
        $shopUrl = Data::sqlRecords($query, 'one');
        $shop_url = is_array($shopUrl) && isset($shopUrl['shop_url']) ? trim($shopUrl['shop_url']) : "";
        $info_array = [];
        $content = file_get_contents(\Yii::getAlias('@webroot') . '/var/importproduct/' . $merchant_id . '/' . $file . '.txt');
        $real_content = trim($content, ',');
        $real_array = explode(',', $real_content);
        $valid_array = array_chunk($real_array, 49);
        $return_array = [];
        foreach ($valid_array as $key => $value) {
            $result_array[] = implode(',', $value);
        }
        //return $this->render('view',['result_array' => $result_array]);
        /*print_r($result_array);
        die;*/
        return json_encode($result_array);
        /* header('Location: https://'.$shop_url.'/admin/bulk?resource_name=Product&edit=variants.sku,variants.price,product_type,variants.inventory_quantity_adjustment&show=&return_to=/admin/products&metafield_titles=&metafield_options=&ids='.$real_content.'');
         die;*/

    }


    /**
     * Create custom sku on shopify
     * @return bool
     */
    public static function CreateSkuOnShopify($id)
    {
        $post_data = [];
        /*        if ($product['type'] == 'simple') {
                   $updateInventory['variant'] = array(
                       "id" => $product['variant_id'],
                       "inventory_quantity" => $product['qty'],
                   );

                   $id = trim($product['variant_id']);
               } else {
                   $updateInventory['variant'] = array(
                       "id" => $product['option_id'],
                       "inventory_quantity" => $product['qty'],
                   );
                   $id = trim($product['option_id']);
               }*/

        $post_data['variant'] = array('id' => $id, 'sku' => Data::getCustomsku($id));
        $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
        $response = $sc->call('PUT', '/admin/variants/' . $id . '.json', $post_data);
    }

}
