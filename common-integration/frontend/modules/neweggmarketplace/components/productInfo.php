<?php
namespace frontend\modules\neweggmarketplace\components;

use Yii;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\product\Productimport;
use yii\base\Component;

class productInfo extends Component
{
    public static function checkUpcType($product_upc)
    {
        if (is_numeric($product_upc)) {
            if (strlen($product_upc) == 12)
                return "UPC";
            elseif (strlen($product_upc) == 10)
                return "ISBN";
            elseif (strlen($product_upc) == 13)
                return "ISBN";
            elseif (strlen($product_upc) == 14)
                return "GTIN";
        }
        return "";
    }

    public static function checkUpcVariants($product_upc = "", $product_id = "", $variant_id = "", $variant_as_parent = 0, $connection = array())
    {
        if (!isset($connection)) {
            $connection = Yii::$app->getDb();
        }
        $variant_count = 0;
        $main_product_count = 0;
        $main_products = array();
        $variant = array();
        if ($variant_as_parent) {
            /*$queryObj="";
            $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE upc='".trim($product_upc)."' AND id <> '".$product_id."'");
            $main_products = $queryObj->queryAll();*/

            $main_products = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE upc='" . trim($product_upc) . "' AND id <> '" . $product_id . "'", 'all');
        } else {
            /*$queryObj="";
            $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE upc='".trim($product_upc)."'");
            $main_products = $queryObj->queryAll();*/

            $main_products = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE upc='" . trim($product_upc) . "'", 'all');
        }
        $main_product_count = count($main_products);
        unset($main_products);
        /*$queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='".trim($product_upc)."' and option_id <>'".$variant_id."'");
        $variant = $queryObj->queryAll();*/
        $variant = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='" . trim($product_upc) . "' and option_id <>'" . $variant_id . "'", 'all');
        $variant_count = count($variant);
        unset($variant);
        if ($main_product_count > 0 || $variant_count > 0) {
            //$msg['success']=true;
            return true;
        }
        return false;
    }

    public static function checkUpcVariantSimple($product_upc = "", $product_id = "", $product_sku = "", $connection = array())
    {
        if (!isset($connection)) {
            $connection = Yii::$app->getDb();
        }
        $connection = Yii::$app->getDb();
        $product_upc = trim($product_upc);
        $main_product_count = 0;
        $main_products = array();
        $variant = array();
        $variant_count = 0;
        $queryObj = "";
        $query = "SELECT `id` FROM `jet_product` WHERE `upc`='" . $product_upc . "' AND `id`<>'" . $product_id . "'";
        $queryObj = $connection->createCommand($query);
        $main_products = $queryObj->queryAll();
        $main_product_count = count($main_products);
        unset($main_products);
        $queryObj = "";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_sku <> '" . $product_sku . "' AND option_unique_id='" . $product_upc . "'");
        $variant = $queryObj->queryAll();
        $variant_count = count($variant);
        unset($variant);
        if ($main_product_count > 0 || $variant_count > 0) {
            return true;
        }
        return false;
    }

    /*Product Upadte using configuration*/
    // Update Product details
    public static function updateDetails($value = [], $sync = [], $merchant_id, $webhook = false)
    {
        try {
            $archiveSKU = [];
            $product_id = $value['id'];
            $count = 0;
            //check if product is not exits in database
            $result = Data::sqlRecords("SELECT title,sku,type,description,variant_id,image,qty,price,weight,attr_ids,vendor,upc,fulfillment_node,status FROM `jet_product` WHERE id='" . $product_id . "' LIMIT 0,1", "one", "select");
            $resultDetails = Data::sqlRecords("SELECT product_title,shopify_product_type,long_description,product_price FROM `newegg_product` WHERE product_id='" . $product_id . "' LIMIT 0,1", "one", "select");
            if (!$result) {
                $import_option = Data::getConfigValue($merchant_id, 'import_product_option');
                Productimport::saveProduct($value, $merchant_id);
                $count++;
                return $count;
            }
            $vendor = addslashes($value['vendor']);
            $product_type = isset($value['product_type']) ? $value['product_type'] : "";
            $title = addslashes($value['title']);

            $description = addslashes(preg_replace("/<script.*?\/script>/", "", $value['body_html']) ?: $value['body_html']);
            if (mb_detect_encoding($description) !== 'UTF-8') {
                $description = utf8_encode($description);
                $description = str_replace('Â', '', $description);
            } else {
                $description = utf8_encode($description);
                $description = str_replace('Â', '', $description);
            }
            $description = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $description);
            $image = self::getImplodedImages($value['images']);
            $attr_ids = "";
            $attrId = [];
            foreach ($value['options'] as $val) {
                if ($val['name'] != 'Title') {
                    $attrId[$val['id']] = $val['name'];
                }
            }
            $attr_ids = (is_array($attrId) && count($attrId) > 0) ? addslashes(json_encode($attrId)) : '';
            $product_weight = 0.00;
            $status = Data::PRODUCT_STATUS_NOT_UPLOADED;
            $fulfillment_node = 0;
            $isProductTypeChanged = false;
            $isVendorChanged = false;
            $isTitleChanged = false;
            $isImageChanged = false;
            $isDescriptionChanged = false;
            $isVariantAttributeChanged = false;
            $updateTitleDetails = false;
            $updateDescriptionDetails = false;
            $updatePriceDetails = false;
            // Saving shopify product type
            if ($product_type != $resultDetails['shopify_product_type'] && isset($sync['sync-fields']['product_type'])) {
                $isProductTypeChanged = true;
                $query = 'SELECT id,category_id FROM `newegg_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($value['product_type']) . '" LIMIT 0,1';
                $walmodelmap = Data::sqlRecords($query, 'one', 'select');
                if ($walmodelmap) {
                    $category = $walmodelmap['category_id'];
                } else {
                    $query = 'INSERT INTO `newegg_category_map`(`merchant_id`,`product_type`) VALUES("' . $merchant_id . '","' . addslashes($value['product_type']) . '")';
                    Data::sqlRecords($query, null, 'insert');
                }
            }
            if ($resultDetails['title'] != $title && isset($sync['sync-fields']['title'])) {
                $isTitleChanged = true;
            }
            if ($result['description'] != $description && isset($sync['sync-fields']['description'])) {
                $isDescriptionChanged = true;
            }
            if ($result['image'] != $image && isset($sync['sync-fields']['image'])) {
                $isImageChanged = true;
            }
            if ($result['vendor'] != $vendor && isset($sync['sync-fields']['vendor'])) {
                $isVendorChanged = true;
            }
            if ($result['attr_ids'] != $attr_ids && isset($sync['sync-fields']['variant_options'])) {
                $isVariantAttributeChanged = true;
            }
            /* save variants start */
            $variants = $value['variants'];
            $skus = [];
            $inventoryData = [];
            $priceData = [];
            $updateChanges = false;
            $skus = [];
            $variant_ids = [];
            $isParentExist = false;
            if (is_array($variants)) {
                foreach ($variants as $variant) {
                    $updateProduct = $updateProductDetails = $updateProductVariant = $updateNeweggProductVariant = "";
                    if ($variant['sku'] == "" || !Productimport::validateSku($variant['sku'], $product_id, $merchant_id) || in_array($variant['sku'], $skus)) {
                        continue;
                    }
                    $skus[] = $variant['sku'];
                    $variant_ids[] = $variant['id'];
                    $option_id = $variant['id'];
                    $option_title = addslashes($variant['title']);
                    $option_sku = addslashes($variant['sku']);
                    $option_image = '';
                    if (isset($variant['image_id'])) {
                        if (!is_null($variant['image_id'])) {
                            $image_array = self::getImage($value['images'], $variant['image_id']);
                            $option_image = addslashes($image_array['src']);
                        }
                    }
                    $option_weight = 0.00;
                    $weight_unit = $variant['weight_unit'];
                    if ($variant['weight'] > 0) {
                        $option_weight = (float)Productimport::convertWeight($variant['weight'], $weight_unit);
                    }
                    $option_price = $variant['price'];
                    $option_qty = $variant['inventory_quantity'];
                    $option_barcode = $variant['barcode'];
                    $variant_option1 = isset($variant['option1']) ? $variant['option1'] : '';
                    $variant_option2 = isset($variant['option2']) ? $variant['option2'] : '';
                    $variant_option3 = isset($variant['option3']) ? $variant['option3'] : '';

                    //save data in `jet_product_variants`
                    $resulVar = Data::sqlRecords("SELECT option_title,option_sku,option_image,option_qty,option_weight,option_price,option_unique_id,variant_option1,variant_option2,variant_option3 FROM `jet_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1", "one", "select");
                    $walresult = Data::sqlRecords("SELECT * FROM `newegg_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1", 'one', 'select');

                    $isVariantExist = false;
                    $isMainProduct = false;

                    if (!isset($resulVar['option_sku']) || !isset($walresult['option_id'])) {
                        //variant doesn't exist
                        if (count($variants) > 1) {
                            if (!isset($resulVar['option_sku'])) {
                                $sql = "INSERT INTO `jet_product_variants`(`option_id`, `product_id`, `merchant_id`, `option_title`, `option_sku`, `jet_option_attributes`, `option_image`, `option_qty`, `option_weight`, `option_price`, `option_unique_id`,`variant_option1`, `variant_option2`, `variant_option3`, `vendor`) VALUES ({$option_id},{$product_id},{$merchant_id},'{$option_title}','{$option_sku}',NULL,'" . addslashes($option_image) . "','{$option_qty}','" . (float)$option_weight . "','" . (float)$option_price . "','{$option_barcode}','{$variant_option1}','{$variant_option2}','{$variant_option3}','" . addslashes($vendor) . "')";
                                Data::sqlRecords($sql, null, "insert");
                            }
                            $updateChanges = true;
                            if (!isset($walresult['option_sku'])) {
                                $sql = "INSERT INTO `newegg_product_variants`(
                                        `option_id`,`product_id`,`merchant_id`,`upload_status`,`new_variant_option_1`,`new_variant_option_2`,`new_variant_option_3`
                                        )
                                        VALUES({$option_id},{$product_id},{$merchant_id},'" . Data::PRODUCT_STATUS_NOT_UPLOADED . "','{$variant_option1}','{$variant_option2}','{$variant_option3}')";
                                Data::sqlRecords($sql);
                            }
                        }
                        if ($result['variant_id'] == $option_id) {
                            $isMainProduct = true;
                            $isParentExist = true;

                            if ($result['sku'] != $option_sku && isset($sync['sync-fields']['sku'])) {
                                $archiveSKU[] = $result['sku'];
                                $updateProduct .= "`sku`='" . addslashes($option_sku) . "',";
                                $updateProduct .= "`status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "',";

                            }
                            if ($result['qty'] != $option_qty && isset($sync['sync-fields']['inventory'])) {
                                $inventoryData[$option_id] = ["inventory" => (int)$option_qty, "sku" => $option_sku, "merchant_id" => $merchant_id];
                                $updateProduct .= "`qty`='" . (int)$option_qty . "',";
                            }
                            if (round($result['weight'], 2) != round($option_weight, 2) && isset($sync['sync-fields']['weight'])) {
                                $updateProduct .= "`weight`='" . (float)$option_weight . "',";
                            }
                            if ($result['price'] != $option_price && isset($sync['sync-fields']['price'])) {
                                /* $isRepricingEnabled = WalmartRepricing::isRepricingEnabled($option_sku);
                                     if(!$isRepricingEnabled){
                                         $priceData[$option_id] =["product_id"=>$product_id,"price"=> (float)$option_price,"sku"=>$option_sku,"merchant_id"=>$merchant_id];
                                     }*/
                                $updateProductDetails .= "`product_price`='" . (float)$option_price . "',";
                            }
                            if ($option_barcode && $result['upc'] != $option_barcode && isset($sync['sync-fields']['upc'])) {
                                $updateProduct .= "`upc`='" . $option_barcode . "',";
                            }
                        }

                    } else {
                        if ($result['variant_id'] == $option_id) {
                            $isMainProduct = true;
                            $isParentExist = true;
                        }
                        if ($resulVar['option_sku'] != $option_sku && isset($sync['sync-fields']['sku'])) {
                            $archiveSKU[] = $result['sku'];
                            if ($isMainProduct) {
                                $updateProduct .= "`sku`='" . addslashes($option_sku) . "',";
                                $updateProduct .= "`status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "',";
                                $updateProductDetails .= "`upload_status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "',";
                            }
                            $updateProductVariant .= "`option_sku`='" . addslashes($option_sku) . "',";
                            $updateProductVariant .= "`status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "',";
                            $updateNeweggProductVariant .= "`upload_status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "',";
                        }
                        if ($resulVar['option_qty'] != $option_qty && isset($sync['sync-fields']['inventory'])) {
                            $inventoryData[$option_id] = ["inventory" => (int)$option_qty, "sku" => $option_sku, "merchant_id" => $merchant_id];
                            if ($isMainProduct)
                                $updateProduct .= "`qty`='" . (int)$option_qty . "',";
                            $updateProductVariant .= "`option_qty`='" . (int)$option_qty . "',";
                        }
                        if (round($resulVar['option_weight'], 2) != round($option_weight, 2) && isset($sync['sync-fields']['weight'])) {
                            if ($isMainProduct)
                                $updateProduct .= "`weight`='" . (float)$option_weight . "',";
                            $updateProductVariant .= "`option_weight`='" . (float)$option_weight . "',";
                        }
                        if (isset($sync['sync-fields']['price'])) {
                            if ($walresult['option_prices']) {
                                if ($walresult['option_prices'] != $option_price) {
                                    if ($isMainProduct) {
                                        $updateProductDetails .= "`product_price`='" . (float)$option_price . "',";
                                    }
                                    $updateNeweggProductVariant .= "`option_prices`='" . (float)$option_price . "',";
                                }
                            } else {
                                if ($resulVar['option_price'] != $option_price) {

                                    if ($isMainProduct) {
                                        $updateProductDetails .= "`product_price`='" . (float)$option_price . "',";
                                    }
                                    $updateNeweggProductVariant .= "`option_prices`='" . (float)$option_price . "',";
                                }
                            }
                        }
                        if ($option_barcode && $resulVar['option_unique_id'] != $option_barcode && isset($sync['sync-fields']['upc'])) {
                            if ($isMainProduct)
                                $updateProduct .= "`upc`='" . $option_barcode . "',";
                            $updateProductVariant .= "`option_unique_id`='" . $option_barcode . "',";
                        }
                        if ($resulVar['option_title'] != $option_title && isset($sync['sync-fields']['title'])) {
                            $updateProductVariant .= "`option_title`='" . $option_title . "',";
                        }
                        if ($resulVar['option_image'] != $option_image && isset($sync['sync-fields']['image'])) {
                            $updateProductVariant .= "`option_image`='" . $option_image . "',";
                        }
                        if ($resulVar['variant_option1'] != $variant_option1 && isset($sync['sync-fields']['variant_options'])) {
                            $updateProductVariant .= "`variant_option1`='" . addslashes($variant_option1) . "',";
                            $updateNeweggProductVariant .= "`new_variant_option_1`='" . addslashes($variant_option1) . "',";
                        }
                        if ($resulVar['variant_option2'] != $variant_option2 && isset($sync['sync-fields']['variant_options'])) {
                            $updateProductVariant .= "`variant_option2`='" . addslashes($variant_option2) . "',";
                            $updateNeweggProductVariant .= "`new_variant_option_2`='" . addslashes($variant_option2) . "',";
                        }
                        if ($resulVar['variant_option3'] != $variant_option3 && isset($sync['sync-fields']['variant_options'])) {
                            $updateProductVariant .= "`variant_option3`='" . addslashes($variant_option3) . "',";
                            $updateNeweggProductVariant .= "`new_variant_option_3`='" . addslashes($variant_option3) . "',";
                        }
                    }
                    if ($isMainProduct) {
                        if ($isProductTypeChanged) {
                            $updateProduct .= "`product_type`='" . addslashes($product_type) . "',";
                        }
                        if ($isTitleChanged) {

                            $updateProductDetails .= "`product_title`='" . $title . "',";
                        }
                        if ($isDescriptionChanged) {
                            $updateProduct .= "`description`='" . $description . "',";
                            if (isset($resultDetails['long_description']) && $resultDetails['long_description'])
                                $updateProductDetails .= "`long_description`='" . $description . "',";
                        }
                        if ($isImageChanged) {
                            $updateProduct .= "`image`='" . addslashes($image) . "',";
                        }
                        if ($isVendorChanged) {
                            $updateProduct .= "`vendor`='" . $vendor . "',";
                        }
                        if ($isVariantAttributeChanged) {
                            $updateProduct .= "`attr_ids`='" . addslashes($attr_ids) . "',";
                        }
                        if ($fulfillment_node) {
                            $updateProduct .= "`fulfillment_node`='" . $fulfillment_node . "',";
                        }
                    }
                    /*else
                    {
                        $archiveSKU=self::addNewVariants($value,$product_id,$merchant_id);
                    }*/


                    if ($updateProduct) {
                        //echo "<br>updateProduct".$updateProduct;
                        $updateChanges = true;
                        $updateProduct = rtrim($updateProduct, ',');
                        //echo $updateProduct;
                        $query = "UPDATE `jet_product` SET " . $updateProduct . " WHERE id=" . $product_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query, null, 'update');
                    }
                    if ($updateProductDetails) {
                        //echo "<br>updateProductDetails".$updateProductDetails;
                        $updateChanges = true;
                        $updateProductDetails = rtrim($updateProductDetails, ',');
                        $query = "UPDATE `newegg_product` SET " . $updateProductDetails . " WHERE product_id=" . $product_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query, null, 'update');
                    }
                    if ($updateProductVariant) {
                        //echo "<br>updateProductVariant".$updateProductVariant;
                        $updateChanges = true;
                        $updateProductVariant = rtrim($updateProductVariant, ',');
                        $query = "UPDATE `jet_product_variants` SET " . $updateProductVariant . " WHERE option_id=" . $option_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query, null, 'update');
                    }
                    if ($updateNeweggProductVariant) {
                        //echo "<br>updateProductVariant".$updateProductVariant;
                        $updateChanges = true;
                        $updateNeweggProductVariant = rtrim($updateNeweggProductVariant, ',');
                        $query = "UPDATE `newegg_product_variants` SET " . $updateNeweggProductVariant . " WHERE option_id=" . $option_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query, null, 'update');
                    }
                }
            }
            if (!$isParentExist) {
                $archiveSKU = self::addNewVariants($value, $product_id, $merchant_id);
            }
            //delete old variants
            if (is_array($variant_ids) && count($variant_ids) > 1) {
                $archiveSKU = self::extraDeleteVariants($product_id, $variant_ids);
            }
            //check update changes
            if ($updateChanges)
                $count++;
            //check product simple/variants 
            if (is_array($skus) && count($skus) > 0) {
                if (count($skus) == 1 && $result['type'] == "variants") {
                    Data::sqlRecords("UPDATE `jet_product` SET type='simple' WHERE id=" . $product_id);
                } elseif (count($skus) > 1 && $result['type'] == "simple") {
                    Data::sqlRecords("UPDATE `jet_product` SET type='variants' WHERE id=" . $product_id);
                }
            }
            //var_dump($inventoryData);var_dump($priceData);
            if (is_array($inventoryData) && count($inventoryData) > 0) {
                //update inventory on jet
                $url = Yii::getAlias('@webjeturl') . "/jetwebhook/curlprocessforinventoryupdate?maintenanceprocess=1";
                Data::sendCurlRequest($inventoryData, $url);
                //update inventory on walmart
                $url = Yii::getAlias('@webwalmarturl') . "/walmart-webhook/inventoryupdate?maintenanceprocess=1";
                Data::sendCurlRequest($inventoryData, $url);

            }
            if (is_array($priceData) && count($priceData) > 0) {
                //update price on jet
                $url = Yii::getAlias('@webjeturl') . "/jetwebhook/curlprocessforpriceupdate?maintenanceprocess=1";
                Data::sendCurlRequest($priceData, $url);
                //update price on walmart
                if ($webhook) {
                    $url = Yii::getAlias('@webwalmarturl') . "/walmart-webhook/priceupdate?maintenanceprocess=1";
                    Data::sendCurlRequest($priceData, $url);
                }
            }
            if (is_array($archiveSKU) && count($archiveSKU) > 0) {
                //send curl request to archive/retire on jet/walmart
                $archive_data = ['archiveSku' => $archiveSKU, 'merchant_id' => $merchant_id];
                if ($webhook) {
                    $url = Yii::getAlias('@webwalmarturl') . "/walmart-webhook/productdelete?maintenanceprocess=1";
                    Data::sendCurlRequest($archive_data, $url);
                }
                $url = Yii::getAlias('@webwalmarturl') . "/jetwebhook/curlprocessfordelete?maintenanceprocess=1";
                Data::sendCurlRequest($archive_data, $url);
            }
            return $count;
        } catch (\yii\db\Exception $e) {
            Data::createExceptionLog('actionCurlproductcreate', $e->getMessage(), $merchant_id);
            exit(0);
        } catch (Exception $e) {
            Data::createExceptionLog('actionCurlproductcreate', $e->getMessage(), $merchant_id);
            exit(0);
        }
    }

    /**
     * Validate Product Barcode
     *
     * @param $optionId
     * @return void
     */
    public static function validateProductBarcode($barcode, $variant_id, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = Yii::$app->user->identity->id;


        $query = "SELECT `merged_data`.* FROM ((SELECT `variant_id` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple' AND `jet_product`.upc='{$barcode}') UNION (SELECT `newegg_product_variants`.`option_id` AS `variant_id` FROM `newegg_product_variants`  INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=" . $merchant_id . " AND `jet_product_variants`.option_unique_id='{$barcode}')) as `merged_data`";

        $result = Data::sqlRecords($query, 'all', 'select');

        if ($result) {
            if (count($result) > 1) {
                return false;
            } elseif ($result[0]['variant_id'] == $variant_id) {
                return true;
            } else {
                return false;
            }
        } else
            return true;
    }

    public static function addNewVariants($data, $product_id, $merchant_id)
    {
        $archiveSkus = array();
        $modelProVar = Data::sqlRecords('SELECT `option_sku` from `jet_product_variants` where product_id="' . $product_id . '"', 'all', 'select');
        if (is_array($modelProVar) && count($modelProVar) > 0) {
            foreach ($modelProVar as $value) {
                $archiveSkus[] = $value['option_sku'];
            }
        }
        Data::sqlRecords('DELETE FROM `jet_product` WHERE merchant_id="' . $merchant_id . '" AND  id="' . $product_id . '"', null, 'delete');
        Data::sqlRecords('DELETE FROM `jet_product_variants` WHERE merchant_id="' . $merchant_id . '" AND  product_id="' . $product_id . '"', null, 'delete');
        Productimport::saveProduct($data, $merchant_id);
        return $archiveSkus;
    }

    public static function extraDeleteVariants($id, $variant_ids)
    {
        $archiveSKU = [];
        $variantIds = Data::sqlRecords("SELECT option_id,option_sku FROM `jet_product_variants` WHERE product_id=" . $id, "all", "select");
        if (is_array($variantIds) && count($variantIds) > 0) {
            foreach ($variantIds as $value) {
                if (!in_array($value['option_id'], $variant_ids)) {
                    $archiveSKU[] = $value['option_sku'];
                    Data::sqlRecords("DELETE FROM `jet_product_variants` WHERE option_id=" . $value['option_id'], null, 'delete');
                }
            }
        }
        return $archiveSKU;
    }

    public static function getImplodedImages($images)
    {
        $img_arr = [];
        if (count($images)) {
            foreach ($images as $image) {
                $img_arr[] = $image['src'];
            }
        }
        return implode(',', $img_arr);
    }

    public static function getImage($images, $image_id)
    {
        if (count($images)) {
            foreach ($images as $image) {
                if ($image['id'] == $image_id) {
                    return $image;
                }
            }
        }
        return ['src' => ''];
    }
}