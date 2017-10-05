<?php
namespace frontend\modules\neweggmarketplace\components\product;

use Yii;
use yii\helpers\Url;
use yii\base\Component;
use frontend\modules\neweggmarketplace\models\NeweggProduct;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;

class Productimport extends Component
{

    public static function batchimport($index, $countUpload, $merchant_id = false, $webhook = false)
    {
        if (!$webhook) {
            if (empty($index)) { //by default index page is 0
                $index = 0;
            }
            if (empty($countUpload)) { //return if count product is 0
                return ['message' => 'No product available'];
            }
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
        } else {
            if (count($index) > 0) { //by default index page is 0
                $jProductTotal = 0;
                $not_skuTotal = 0;
                $products = $index;
            } else {
                return;
            }

        }
        try {
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

                    $product_des = preg_replace("/<script.*?\/script>/", "", $value['body_html']) ?: $value['body_html'];//$value['body_html'];
                    $variants = $value['variants'];
                    $images = array();
                    if (isset($value['images'])) {
                        $images = $value['images'];
                    }
                    $created_at = $value['created_at'];
                    $product_price = $value['variants'][0]['price'];
                    if (isset($value['variants'][0]['barcode'])) {
                        $barcode = $value['variants'][0]['barcode'];
                    } else {
                        $barcode = '';
                    }

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

                            } else {
                                $model = Data::sqlRecords("UPDATE `jet_product_variants` SET product_id ='" . $product_id . "' ,merchant_id ='" . $merchant_id . "' ,option_title ='" . addslashes($option_title) . "' ,option_sku ='" . addslashes($option_sku) . "',option_image ='" . addslashes($option_image_url) . "',option_price ='" . $option_price . "',option_qty ='" . $option_qty . "',variant_option1 ='" . addslashes($option_variant1) . "',variant_option2 ='" . addslashes($option_variant2) . "',variant_option2 ='" . addslashes($option_variant2) . "',variant_option3 ='" . addslashes($option_variant3) . "',vendor ='" . addslashes($vendor) . "',option_unique_id ='" . addslashes($option_barcode) . "',option_weight ='" . addslashes($option_weight) . "' where option_id='" . $option_id . "'", 'all', 'update');
                            }


                            //newegg new product type
                            $neweggmodelmap = "";
                            $query = "";
                            $queryObj = "";
                            $query = 'SELECT category_id FROM `newegg_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($product_type) . '"';
                            $queryObj = $connection->createCommand($query);
                            $neweggmodelmap = $queryObj->queryOne();

                            if ($neweggmodelmap) {
                                //newegg new product
//                            $updateResult = "";
                                $query = 'UPDATE `newegg_product` SET newegg_category="' . $neweggmodelmap['category_id'] . '" where product_id="' . $product_id . '"';
                                $updateResult = $connection->createCommand($query)->execute();
                            } else {
                                //newegg category map
                                $queryObj = "";
                                if (!empty($product_type)) {
                                    $query = 'INSERT INTO `newegg_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . $product_type . '")';
                                    $queryObj = $connection->createCommand($query)->execute();
                                }
                            }

                            $neweggresult = $connection->createCommand("SELECT `option_id` FROM `newegg_product_variants` WHERE option_id='" . $option_id . "'")->queryOne();

                            if (!$neweggresult) {
                                $sql = "INSERT INTO `newegg_product_variants`(
                                            `option_id`,`product_id`,`merchant_id`,`upload_status`
                                            )
                                            VALUES('" . $option_id . "','" . $product_id . "','" . $merchant_id . "','Not Uploaded')";

                                $connection->createCommand($sql)->execute();
                            } else {
                                $model = Data::sqlRecords("UPDATE `newegg_product_variants` SET option_id='" . $option_id . "' ,product_id ='" . $product_id . "' ,merchant_id ='" . $merchant_id . "'where option_id='" . $option_id . "'", 'all', 'update');
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
                        $attr_id = self::getOptionValuesForSimpleProduct($value);
                    }

                    //insert product data
                    $result = "";
                    $productmodel = "";
                    $new_product_flag = false;
                    $connection = Yii::$app->getDb();
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
                    } else {
                        if (count($variants) > 1) {
                            $new_product_flag = true;
                            $model = Data::sqlRecords("UPDATE `jet_product` SET merchant_id ='" . $merchant_id . "' ,title ='" . addslashes($product_title) . "' ,sku ='" . addslashes($product_sku) . "',type ='variants',description ='" . addslashes($product_des) . "',image ='" . addslashes($product_images) . "',price ='" . $product_price . "',qty ='" . $product_qty . "',attr_ids ='" . addslashes($attr_id) . "',upc ='" . addslashes($barcode) . "',status ='Not Uploaded',vendor ='" . addslashes($vendor) . "',variant_id ='" . addslashes($variant_id) . "',product_type ='" . addslashes($product_type) . "',weight ='" . $weight . "' where id='" . $product_id . "'", 'all', 'update');
                        } else {
                            $new_product_flag = true;
                            $model = Data::sqlRecords("UPDATE `jet_product` SET merchant_id ='" . $merchant_id . "' ,title ='" . addslashes($product_title) . "' ,sku ='" . addslashes($product_sku) . "',type ='simple',description ='" . addslashes($product_des) . "',image ='" . addslashes($product_images) . "',price ='" . $product_price . "',qty ='" . $product_qty . "',attr_ids ='" . addslashes($attr_id) . "',upc ='" . addslashes($barcode) . "',status ='Not Uploaded',vendor ='" . addslashes($vendor) . "',variant_id ='" . addslashes($variant_id) . "',product_type ='" . addslashes($product_type) . "',weight ='" . $weight . "' where id='" . $product_id . "'", 'all', 'update');
                        }
                    }
                    $newresult = "";
                    $newresult = $connection->createCommand("SELECT `product_id` FROM `newegg_product` WHERE product_id='" . $product_id . "'")->queryOne();
                    if (!$newresult) {
                        $new_product_flag = true;
                        $sql = "INSERT INTO `newegg_product` (`product_id`,`merchant_id`,`upload_status`,`shopify_product_type`) VALUES ('" . $product_id . "','" . $merchant_id . "','Not Uploaded','" . addslashes($product_type) . "')";
                        $model = $connection->createCommand($sql)->execute();
                    } else {
                        $new_product_flag = true;
                        $model = Data::sqlRecords("UPDATE `newegg_product` SET product_id ='" . $product_id . "' ,merchant_id ='" . $merchant_id . "' ,shopify_product_type ='" . addslashes($product_type) . "' where product_id='" . $product_id . "'", 'all', 'update');
                    }

                    $modelNew = '';
                    if ($product_type) {

                        //newegg new product type
                        $neweggmodelmap = "";
                        $query = "";
                        $queryObj = "";
                        $query = 'SELECT category_id FROM `newegg_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($product_type) . '"';
                        $queryObj = $connection->createCommand($query);
                        $neweggmodelmap = $queryObj->queryOne();

                        if ($neweggmodelmap) {
                            //newegg new product
//                            $updateResult = "";
                            $query = 'UPDATE `newegg_product` SET newegg_category="' . $neweggmodelmap['category_id'] . '" where product_id="' . $product_id . '"';
                            $updateResult = $connection->createCommand($query)->execute();
                        } else {
                            //newegg category map
                            $queryObj = "";
                            if (!empty($product_type)) {
                                $query = 'INSERT INTO `newegg_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . $product_type . '")';
                                $queryObj = $connection->createCommand($query)->execute();
                            }
                        }

                    }

                }

            }
            $jProductTotal += $jProduct;
            $not_skuTotal += $not_sku;
            unset($result);
            unset($product);
            if ($webhook) {

            } elseif ($index == $pages - 1) {
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
        } catch (ShopifyCurlException $e) {
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

    public static function saveProduct($data, $merchant_id, $connection = false, $import_option = null)
    {
        try {
            $response = [];

            $insert_product = Data::sqlRecords("SELECT `total_variant_products` FROM `insert_product` WHERE `merchant_id`='" . $merchant_id . "'", 'one');
            $total_variants = $insert_product['total_variant_products'];

            if ($total_variants > Data::TOTAL_PRODUCT_LIMIT) {
                $response['error'] = 'You have ' . $total_variants . ' product(s) including variants which is beyond the limit';
                return $response;
            }
            if (isset($data['id'])) {
                $product_images = "";
                $images = [];
                if (is_null($import_option)) {
                    $import_option = Data::getConfigValue($merchant_id, 'import_product_option');
                }
                if ($import_option == 'published') {
                    if (is_null($data['published_at'])) {
                        self::insertImportErrorProduct($data['id'], $data['title'], 'hidden_product', $merchant_id);
                        $response['error'] = "hidden_product";
                        return $response;
                    }
                }
                if ($data['product_type'] == "") {
                    //save product info in product_import_error table
                    self::insertImportErrorProduct($data['id'], $data['title'], 'product_type', $merchant_id);

                    $response['error'] = "product_type";
                    return $response;
                }
                if (isset($data['images']))
                    $images = $data['images'];
                $product_id = $data['id'];
                $imagArr = [];
                if (is_array($images) && count($images)) {
                    foreach ($images as $valImg) {
                        $imagArr[] = $valImg['src'];
                    }
                    $product_images = implode(',', $imagArr);
                }

                $countVariants = 0;
                $skus = [];
                $variantData = [];
                /*if(count($data['variants'])>1)
                {*/
                foreach ($data['variants'] as $value) {
                    if ($value['sku'] == "" || !self::validateSku($value['sku'], $data['id'], $merchant_id) || in_array($value['sku'], $skus)) {
                        continue;
                    }
                    $skus[] = $value['sku'];
                    $option_weight = $option_price = 0.00;
                    $option_price = (float)$value['price'];
                    $option_variant1 = isset($value['option1']) ? $value['option1'] : '';
                    $option_variant2 = isset($value['option2']) ? $value['option2'] : '';
                    $option_variant3 = isset($value['option3']) ? $value['option3'] : '';
                    if ($value['weight'] > 0)
                        $option_weight = (float)Data::convertWeight($value['weight'], $value['weight_unit']);
                    $option_image_url = "";
                    foreach ($images as $value2) {
                        if (isset($value['image_id']) && $value2['id'] == $value['image_id']) {
                            $option_image_url = $value2['src'];
                        }
                    }

                    $countVariants++;
                    $variantData[$value['id']]['product_id'] = $value['product_id'];
                    $variantData[$value['id']]['title'] = addslashes($value['title']);
                    $variantData[$value['id']]['sku'] = addslashes($value['sku']);
                    $variantData[$value['id']]['image'] = addslashes($option_image_url);
                    $variantData[$value['id']]['price'] = (float)$option_price;
                    $variantData[$value['id']]['qty'] = (int)$value['inventory_quantity'];
                    $variantData[$value['id']]['variant_option1'] = addslashes($option_variant1);
                    $variantData[$value['id']]['variant_option2'] = addslashes($option_variant2);
                    $variantData[$value['id']]['variant_option3'] = addslashes($option_variant3);
                    $variantData[$value['id']]['barcode'] = $value['barcode'];
                    $variantData[$value['id']]['weight'] = $option_weight;
                }
                //check product if all product having no skus and skip product to create
                if ($countVariants == 0) {
                    self::insertImportErrorProduct($data['id'], $data['title'], 'sku', $merchant_id);

                    $response['error'] = "sku";
                    return $response;
                }
                //add attribute
                $options = $data['options'];
                $attrId = [];
                foreach ($options as $key => $val) {
                    $attrname = $val['name'];
                    $attrId[$val['id']] = $val['name'];
                    foreach ($val['values'] as $k => $v) {
                        $option_value[$attrname][$k] = $v;
                    }
                }
                $attr_id = json_encode($attrId);
                //}
                //save attribute values for simple products

                $walmart_new_product_flag = $new_product_flag = false;
                $type = "variants";
                if ($countVariants == 1) {
                    $type = "simple";
                }
                if (is_array($variantData) && count($variantData) > 0) {
                    $i = 0;
                    foreach ($variantData as $key => $val) {
                        //save data in jet_product 
                        if ($i == 0) {
                            $proResult = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE id='" . $product_id . "' LIMIT 0,1", "one", "select");
                            if (!$proResult) {
                                $response['success'] = true;
                                $descNew = preg_replace("/<script.*?\/script>/", "", $data['body_html']) ?: $data['body_html'];
                                $sql = 'INSERT INTO `jet_product`
                                    (
                                        `id`,`merchant_id`,
                                        `title`,`sku`,
                                        `type`,`description`,
                                        `image`,`price`,
                                        `qty`,`attr_ids`,
                                        `upc`,`status`,
                                        `vendor`,`variant_id`,
                                        `product_type`,`weight`
                                    )
                                    VALUES
                                    (
                                        "' . $product_id . '","' . $merchant_id . '",
                                        "' . addslashes($data['title']) . '","' . $val['sku'] . '",
                                        "' . $type . '","' . addslashes($descNew) . '",
                                        "' . addslashes($product_images) . '","' . (float)$val['price'] . '",
                                        "' . (int)$val['qty'] . '","' . addslashes($attr_id) . '",
                                        "' . $val['barcode'] . '","Not Uploaded",
                                        "' . addslashes($data['vendor']) . '","' . $key . '",
                                        "' . addslashes($data['product_type']) . '","' . $val['weight'] . '"
                                    )';
                                Data::sqlRecords($sql, null, 'insert');
                                $new_product_flag = true;
                            }
                            //save in `newegg_product` table
                            $neweggresult = Data::sqlRecords("SELECT `product_id` FROM `newegg_product` WHERE product_id='" . $product_id . "' LIMIT 0,1", 'one', 'select');
                            if (!$neweggresult) {
                                $sql = "INSERT INTO `newegg_product` (`product_id`,`merchant_id`,`upload_status`,`shopify_product_type`) VALUES ('" . $product_id . "','" . $merchant_id . "','" . Data::PRODUCT_STATUS_NOT_UPLOADED . "','" . addslashes($data['product_type']) . "')";
                                $model = Data::sqlRecords($sql, null, 'insert');
                            }
                        }
                        $i++;
                        if ($countVariants > 1) {
                            //save data in jet_product_variants
                            $proVarresult = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE option_id='" . $key . "' LIMIT 0,1", "one", "select");
                            if (!$proVarresult) {
                                $sql = 'INSERT INTO `jet_product_variants`(
                                    `option_id`,`product_id`,
                                    `merchant_id`,`option_title`,
                                    `option_sku`,`option_image`,
                                    `option_price`,`option_qty`,
                                    `variant_option1`,`variant_option2`,
                                    `variant_option3`,`vendor`,
                                    `option_unique_id`,`option_weight`,`status`
                                )VALUES(
                                    "' . $key . '","' . $product_id . '",
                                    "' . $merchant_id . '","' . $val['title'] . '",
                                    "' . $val['sku'] . '","' . $val['image'] . '",
                                    "' . (float)$val['price'] . '","' . (int)$val['qty'] . '",
                                    "' . $val['variant_option1'] . '","' . $val['variant_option2'] . '",
                                    "' . $val['variant_option3'] . '","' . addslashes($data['vendor']) . '",
                                    "' . $val['barcode'] . '","' . $val['weight'] . '","Not Uploaded"
                                )';
                                Data::sqlRecords($sql);

                            }
                            //Insert Data Into `newggg_product_variants`
                            $neweggresult = Data::sqlRecords("SELECT `option_id` FROM `newegg_product_variants` WHERE option_id='" . $key . "' LIMIT 0,1", "one", "select");
                            if (!$neweggresult) {
                                $sql = "INSERT INTO `newegg_product_variants`(
                                        `option_id`,`product_id`,`merchant_id`,`upload_status`,`new_variant_option_1`,`new_variant_option_2`,`new_variant_option_3`
                                        )

                                        VALUES('" . $key . "','" . $product_id . "','" . $merchant_id . "','" . Data::PRODUCT_STATUS_NOT_UPLOADED . "','" . addslashes($val['variant_option1']) . "','" . addslashes($val['variant_option2']) . "','" . addslashes($val['variant_option3']) . "')";
                                Data::sqlRecords($sql);
                            }
                        }
                    }

                    if (isset($data['product_type']) && $data['product_type']) {
                        //add product type in jet
                        $modelmap = "";
                        $query = "";
                        $queryObj = "";
                        //add product type in newegg
                        $query = 'SELECT category_id FROM `newegg_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($data['product_type']) . '" LIMIT 0,1';
                        $neweggmodelmap = Data::sqlRecords($query, "one", "select");

                        if ($neweggmodelmap) {
                            if (isset($neweggmodelmap['category_id'])) {
                                //newegg new product
                                $updateResult = "";
                                $query = 'UPDATE `newegg_product` SET newegg_category="' . $neweggmodelmap['category_id'] . '" where product_id="' . $product_id . '"';
                                Data::sqlRecords($query, null, 'update');
                            }

                        } else {
                            //newegg category map
                            $queryObj = "";
                            $query = 'INSERT INTO `newegg_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . addslashes($data['product_type']) . '")';
                            Data::sqlRecords($query);
                        }
                    }

                }
                //delete if product successfully saved but exit in product import error
                $checkExistProduct = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE id='" . $data['id'] . "' LIMIT 0,1", "one", "select");
                if (isset($checkExistProduct['id'])) {
                    $query = "DELETE FROM `product_import_error` WHERE id='" . $checkExistProduct['id'] . "'";
                    Data::sqlRecords($query, null, 'delete');
                }
            }
            unset($data, $images, $imagArr, $attrId, $options, $result);
            return $response;
        } catch (\yii\db\Exception $e) {
            Data::createLog($e->getMessage(), 'exception/' . $merchant_id, 'a', true, true);
            exit(0);
        } catch (Exception $e) {
            Data::createLog($e->getMessage(), 'exception/' . $merchant_id, 'a', true, true);
            exit(0);
        }

    }

    public static function insertImportErrorProduct($id, $title, $type, $merchant_id)
    {
        $checkExistProduct = Data::sqlRecords("SELECT `id` FROM `product_import_error` WHERE id='" . $id . "' LIMIT 0,1", "one", "select");
        if (!$checkExistProduct) {
            $query = "INSERT INTO `product_import_error`(`id`, `merchant_id`, `missing_value`, `title`) VALUES ('" . $id . "','" . $merchant_id . "','" . $type . "','" . addslashes($title) . "')";
            Data::sqlRecords($query, "", "insert");
        }
    }

    /*validate product sku*/
    public static function validateSku($sku, $productId, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = Yii::$app->user->identity->id;
        $sku = addslashes($sku);
        $query = "SELECT `result`.* FROM (SELECT `sku` , `id` AS `product_id` , `variant_id` AS `option_id` ,`merchant_id`, `type` FROM `jet_product` WHERE `merchant_id`='{$merchant_id}' AND `sku`='{$sku}' UNION SELECT `option_sku` AS `sku` , `product_id` , `option_id`, `merchant_id`, 'variants' AS `type` FROM `jet_product_variants` WHERE `merchant_id`='{$merchant_id}' AND `option_sku`='{$sku}') as `result`";
        $result = Data::sqlRecords($query, 'one', 'select');
        if ($result) {
            if ($result['product_id'] == $productId || $result['option_id'] == $productId) {
                return true;
            }
            return false;
        } else
            return true;
    }
}