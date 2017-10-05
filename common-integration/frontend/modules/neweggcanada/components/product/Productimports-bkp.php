<?php
namespace frontend\modules\neweggcanada\components\product;


use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\ShopifyClientHelper;
use Yii;
use yii\base\Component;
use yii\helpers\Url;

class Productimports extends Component
{

    public static function batchimport($index, $countUpload)
    {
        if (empty($index)) { //by default index page is 0
            $index = 0;
        }
        if (empty($countUpload)) { //return if count product is 0
            return ['message' => 'No product available'];
        }

        try {
            $pages = "";
            $sc = "";
            $merchant_id = "";
            $session = "";
            $session = Yii::$app->session;
            if (!isset($connection)) {
                $connection = Yii::$app->getDb();
            }
            $pages = $session->get('product_page');
            $sc = unserialize($session->get('shopify_object'));
            $merchant_id = $session->get('merchant_id');
            if (!$merchant_id) {
                $merchant_id = MERCHANT_ID;
            }
            if (!is_object($sc)) {
                $sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
            }
            if ($index == 0) {
                $jProductTotal = 0;
                $not_skuTotal = 0;
            }
            $products = $sc->call('GET', '/admin/products.json', array('published_status' => 'published', 'limit' => 250, 'page' => $index));
            $product_qty = 0;
            $attr_id = "";
            $attributes_val = "";
            $brand = "";
            $created_at = "";
            $product_sku = "";
            $product_type = "";
            $jProduct = 0;
            $not_sku = 0;

            if ($products) {
                foreach ($products as $value) {
                    $weight = 0;
                    $unit = "";
                    $product_id = $value['id'];
                    $product_title = $value['title'];
                    $vendor = $value['vendor'];
                    $product_type = $value['product_type'];
                    $product_des = $value['body_html'];
                    $variants = $value['variants'];
                    $images = array();
                    $images = $value['images'];
                    $created_at = $value['created_at'];
                    $product_price = $value['variants'][0]['price'];
                    $barcode = $value['variants'][0]['barcode'];
                    $weight = $value['variants'][0]['weight'];
                    $unit = $value['variants'][0]['weight_unit'];
                    if ($weight > 0) {
                        $weight = (float)Self::convertWeight($weight, $unit);
                    }
                    $imagArr = array();
                    $variantArr = array();
                    if (is_array($images) && count($images) > 0) {
                        foreach ($images as $valImg) {
                            $imagArr[] = $valImg['src'];
                        }
                    }
                    $product_images = implode(',', $imagArr);
                    $product_sku = $value['variants'][0]['sku'];
                    if ($product_sku == "") {
                        $not_sku++;
                        continue;
                    }
                    $jProduct++;
                    //$product_base_image=$value['image']['src'];
                    $product_qty = $value['variants'][0]['inventory_quantity'];
                    $variant_id = $value['variants'][0]['id'];

                    if (count($variants) > 1) {
                        foreach ($variants as $value1) {
                            $option_weight = 0;
                            $variantArr[] = $value1['id'];
                            $option_id = $value1['id'];
                            $option_title = $value1['title'];
                            $option_sku = $value1['sku'];
                            $option_image_id = $value1['image_id'];
                            $option_price = $value1['price'];
                            $option_weight = $value1['weight'];
                            if ($option_weight > 0) {
                                $option_weight = (float)Self::convertWeight($option_weight, $value1['weight_unit']);
                            }
                            $option_qty = $value1['inventory_quantity'];
                            $option_variant1 = $value1['option1'];
                            $option_variant2 = $value1['option2'];
                            $option_variant3 = $value1['option3'];
                            $option_barcode = $value1['barcode'];
                            $option_image_url = '';
                            foreach ($images as $value2) {
                                if ($value2['id'] == $option_image_id) {
                                    $option_image_url = $value2['src'];
                                }
                            }
                            $result = "";
                            $optionmodel = "";
                            $optionmodel = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_id='" . $option_id . "'");
                            $result = $optionmodel->queryOne();

                            if (!$result) {
                                $sql = "INSERT INTO `jet_product_variants`(
                                            `option_id`,`product_id`,`merchant_id`,
                                            `option_title`,`option_sku`,`option_image`,
                                            `option_price`,`option_qty`,`variant_option1`,
                                            `variant_option2`,`variant_option3`,`vendor`,
                                            `option_unique_id`,`option_weight`
                                            )
                                            VALUES('" . $option_id . "','" . $product_id . "','" . $merchant_id . "','" . addslashes($option_title) . "','" . addslashes($option_sku) . "','" . addslashes($option_image_url) . "','" . $option_price . "','" . $option_qty . "','" . addslashes($option_variant1) . "','" . addslashes($option_variant2) . "','" . addslashes($option_variant3) . "','" . addslashes($vendor) . "','" . addslashes($option_barcode) . "','" . $option_weight . "')";
                                $connection->createCommand($sql)->execute();

                            }
                            $walresult = "";
                            $walresult = $connection->createCommand("SELECT `option_id` FROM `newegg_product_variants` WHERE option_id='" . $option_id . "'")->queryOne();
                            if (!$walresult) {
                                $sql = "INSERT INTO `newegg_product_variants`(
                                            `option_id`,`product_id`,`merchant_id`
                                            )
                                            VALUES('" . $option_id . "','" . $product_id . "','" . $merchant_id . "')";
                                $connection->createCommand($sql)->execute();
                            }
                        }
                        $options = $value['options'];

                        $attrId = array();
                        foreach ($options as $key => $val) {
                            $attrname = $val['name'];
                            $attrId[$val['id']] = $val['name'];
                            foreach ($val['values'] as $k => $v) {
                                $option_value[$attrname][$k] = $v;
                            }
                        }
                        $attr_id = json_encode($attrId);
                    }
                    if (count($variants) == 1) {
                        $attr_id = Self::getOptionValuesForSimpleProduct($value1);
                    }
                    //insert product data
                    $result = "";
                    $productmodel = "";
                    $new_product_flag = false;
                    $productmodel = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE id='" . $product_id . "'");
                    $result = $productmodel->queryOne();
                    if (!$result) {
                        if (count($variants) > 1) {
                            $new_product_flag = true;
                            $sql = "INSERT INTO `jet_product` (`id`,`merchant_id`,`title`,`sku`,`type`,`description`,`image`,`price`,`qty`,`attr_ids`,`upc`,`status`,`vendor`,`variant_id`,`product_type`,`weight`) VALUES ('" . $product_id . "','" . $merchant_id . "','" . addslashes($product_title) . "','" . addslashes($product_sku) . "','variants','" . addslashes($product_des) . "','" . addslashes($product_images) . "','" . $product_price . "','" . $product_qty . "','" . addslashes($attr_id) . "','" . addslashes($barcode) . "','Not Uploaded','" . addslashes($vendor) . "','" . addslashes($variant_id) . "','" . addslashes($product_type) . "','" . $weight . "')";
                            $model = $connection->createCommand($sql)->execute();

                        } else {
                            $new_product_flag = true;
                            $sql = "INSERT INTO `jet_product` (`id`,`merchant_id`,`title`,`sku`,`type`,`description`,`image`,`price`,`qty`,`attr_ids`,`upc`,`status`,`vendor`,`variant_id`,`product_type`,`weight`) VALUES ('" . $product_id . "','" . $merchant_id . "','" . addslashes($product_title) . "','" . addslashes($product_sku) . "','simple','" . addslashes($product_des) . "','" . addslashes($product_images) . "','" . $product_price . "','" . $product_qty . "','" . addslashes($attr_id) . "','" . addslashes($barcode) . "','Not Uploaded','" . addslashes($vendor) . "','" . addslashes($variant_id) . "','" . addslashes($product_type) . "','" . $weight . "')";
                            $model = $connection->createCommand($sql)->execute();

                        }
                    }
                    $walresult = "";
                    $walresult = $connection->createCommand("SELECT `product_id` FROM `newegg_product` WHERE product_id='" . $product_id . "'")->queryOne();
                    if (!$walresult) {
                        $new_product_flag = true;
                        $sql = "INSERT INTO `newegg_product` (`product_id`,`merchant_id`,`upload_status`,`shopify_product_type`) VALUES ('" . $product_id . "','" . $merchant_id . "','Not Uploaded','" . addslashes($product_type) . "')";
                        $model = $connection->createCommand($sql)->execute();
                    }
                    $modelNew = '';
                    if ($product_type) {
//                        print_r($product_type);

                        $modelmap = "";
                        $query = "";
                        $queryObj = "";
                        $query = 'SELECT category_id FROM `newegg_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($product_type) . '"';
                        $queryObj = $connection->createCommand($query);
                        $modelmap = $queryObj->queryOne();
                        if ($modelmap && $new_product_flag) {
                            $updateResult = "";
                            $query = 'UPDATE `jet_product` SET fulfillment_node="' . $modelmap['category_id'] . '" where id="' . $product_id . '"';
                            $updateResult = $connection->createCommand($query)->execute();

                        } else {
                            $query = 'INSERT INTO `newegg_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . $product_type . '")';
                            $queryObj = $connection->createCommand($query)->execute();
                        }
                        //newegg new product type
                        $walmodelmap = "";
                        $query = "";
                        $queryObj = "";
                        $query = 'SELECT category_id FROM `newegg_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($product_type) . '"';
                        $queryObj = $connection->createCommand($query);
                        $walmodelmap = $queryObj->queryOne();
                        if ($walmodelmap && $new_product_flag) {
                            //newegg new product
                            $updateResult = "";
//                            $query='UPDATE `newegg_product` SET newegg_category="'.$walmodelmap['category_id'].'" where product_id="'.$product_id.'"';
//                            $updateResult = $connection->createCommand($query)->execute();
                        } else {
                            //newegg category map
                            $queryObj = "";
                            $query = 'INSERT INTO `newegg_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . $product_type . '")';
                            $queryObj = $connection->createCommand($query)->execute();
                        }
                    }
                }
            }
            $jProductTotal = "";
            $not_skuTotal = "";
            $jProductTotal += $jProduct;
            $not_skuTotal += $not_sku;
            unset($result);
            unset($product);

            if ($index == $pages - 1) {
                $inserted = "";
                $result = "";
                $inserted = $connection->createCommand("SELECT `merchant_id` FROM `insert_product` WHERE merchant_id='" . $merchant_id . "'");
                $result = $inserted->queryOne();
                //insert data into insert products
                if (!$result) {
                    $queryObj = "";
                    $query = 'INSERT INTO `insert_product`
                                (
                                    `merchant_id`,
                                    `product_count`,
                                    `total_product`,
                                    `not_sku`,
                                    `status`
                                )
                                VALUES(
                                    "' . $merchant_id . '",
                                    "' . $jProductTotal . '",
                                    "' . $countUpload . '",
                                    "' . $not_skuTotal . '",
                                    "inserted"  
                                )';
                    $queryObj = $connection->createCommand($query)->execute();
                } else {
                    $updateQuery = "UPDATE `insert_product` SET `product_count`='" . $jProductTotal . "' ,`total_product`='" . $countUpload . "', `not_sku`='" . $not_skuTotal . "' WHERE merchant_id='" . $merchant_id . "'";
                    $updated = $connection->createCommand($updateQuery)->execute();
                }
            }
        } catch (ShopifyApiException $e) {
            return $returnArr['error'] = $e->getMessage();
        } catch (ShopifyCurlException $e) {
            return $returnArr['error'] = $e->getMessage();
        }
        $returnArr['success']['count'] = $jProduct;
        if ($not_sku > 0)
            $returnArr['success']['not_sku'] = $not_sku;
        return $returnArr;
    }

    public static function convertWeight($weight = "", $unit = "")
    {
        $newWeight = 0;
        if ($unit == 'kg') {
            $newWeight = (float)($weight * 2.2046226218);
            return $newWeight;
        }
        if ($unit == 'g') {
            $newWeight = (float)($weight * 0.0022046226218);
            return $newWeight;
        }
        if ($unit == 'oz') {
            $newWeight = (float)($weight / 16);
            return $newWeight;
        }
        if ($unit == 'lb') {
            return $weight;
        } else {
            return "";
        }
    }

    /**
     * @param $product
     * @return string
     */
    public function getOptionValuesForSimpleProduct($product)
    {
        $options = [];

        if (isset($product['variants'])) {
            $variant = reset($product['variants']);
        }
        if (isset($product['options'])) {
            foreach ($product['options'] as $value) {
                if ($value['name'] != 'Title') {
                    $options[$value['name']] = $variant['option' . $value['position']];
                }
            }
        }
        if (count($options))
            return json_encode($options);
        else
            return '';
    }
}