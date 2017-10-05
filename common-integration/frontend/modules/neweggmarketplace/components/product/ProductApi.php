<?php
namespace frontend\modules\neweggmarketplace\components\product;

use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Helper;
use frontend\modules\neweggmarketplace\components\productInfo;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use frontend\modules\neweggmarketplace\components\Neweggapi;
use frontend\modules\neweggmarketplace\components\product\VariantsProduct;
use frontend\modules\neweggmarketplace\components\AttributeMap;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Url;

class ProductApi extends Component
{
    public static function createProductOnNewegg($ids, $merchant_id, $connection)
    {
        $session = Yii::$app->session;
        $timeStamp = (string)time();

        if (count($ids) > 0) {
            $error = [];
            $productUpload = [];
            $newegg_envelope = array();
            $newegg_envelope['Header'] = array('DocumentVersion' => '1.0');
            $newegg_envelope['MessageType'] = 'BatchItemCreation';
            $message = array();
            $itemFeeds = array();
            $item = array();

            foreach ($ids as $id) {
                $query = "SELECT `ncm`.`category_path`,product_id,title,`ngp`.`merchant_id`,vendor,sku,type,`ngp`.`shopify_product_type`,`variant_id`,description,mpn,`ngp`.`long_description`,image,qty,price,weight,vendor,upc,upload_status,`ngp`.`newegg_data`,`ngp`.`manufacturer`,`ngp`.`mapped_value_data`,`ngp`.`short_description`,`ngp`.`newegg_attributes`,`ngp`.`spn`,`ngp`.`item_condition` FROM `newegg_product` ngp INNER JOIN `jet_product` jet ON `jet`.`id`=`ngp`.`product_id` INNER JOIN `newegg_category_map` ncm ON `ncm`.`product_type`=`ngp`.`shopify_product_type` WHERE `ngp`.`product_id`='" . $id . "' and `ncm`.`merchant_id`='" . MERCHANT_ID . "' LIMIT 1 ";
                $productArray = Data::sqlRecords($query, "one", "select");

                if (empty($productArray)) {
                    $query1 = "SELECT `jet`.sku FROM `newegg_product` ngp INNER JOIN `jet_product` jet ON `jet`.`id`=`ngp`.`product_id`  WHERE `ngp`.`product_id`='" . $id . "' and `ngp`.`merchant_id`='" . MERCHANT_ID . "' LIMIT 1 ";
                    $productArray1 = Data::sqlRecords($query1, "one", "select");
                    $a['error'] = "Missing shopify product type";
                    $error[$productArray1['sku']] = $a;
                    continue;
                }

                /*$mainId = $productArray['product_id'];
                $mainSku = $productArray['sku'];*/
                $subCategoryID = explode(',', $productArray['category_path']);
                if ($productArray['type'] == 'simple') {
                    $validateResponse = self::validateProduct($productArray);

                    /*$subCategoryID = explode(',',$productArray['category_path']);*/
                    if (isset($validateResponse['error'])) {
                        $error[$productArray['sku']] = $validateResponse['error'];
                        $query1 = 'UPDATE `newegg_product` SET upload_status ="' . Data::PRODUCT_STATUS_NOT_UPLOADED . '", error="" where product_id="' . $id . '"';
                        Data::sqlRecords($query1, null, 'update');
                        continue;
                    } else {
                        if (isset($session['newegg_subcategory_' . $subCategoryID[0]]) && !empty($session['newegg_subcategory_' . $subCategoryID[0]])) {
                            $catCollection = $session['newegg_subcategory_' . $subCategoryID[0]];
                        } else {
                            $catCollection = Categoryhelper::getNeweggCategory($subCategoryID[0]);
                        }
                        if (!$catCollection) {
                            $error[$productArray['sku']] = "Invalid Category Selected for Product having sku :'" . $productArray['sku'] . "'";
                            continue;
                        }
                    }

                    $uploadProductIds[] = $id;
                    $itemFeed['SummaryInfo'] = array('SubCategoryID' => trim($subCategoryID[1]));

                    if ($productArray['upload_status'] == Data::PRODUCT_STATUS_NOT_UPLOADED) {
                        $item['Action'] = trim('Create Item');
                        $basicinfo = self::getProductBasicInfo($productArray);
                        $item['BasicInfo'] = $basicinfo;
                        $subCategoryProperty = self::getSubCategoryProperty($productArray);
                        $item['SubCategoryProperty'] = $subCategoryProperty;
                    } elseif ($productArray['upload_status'] == Data::PRODUCT_STATUS_SUBMITTED || $productArray['upload_status'] == Data::PRODUCT_STATUS_ACTIVATED || $productArray['upload_status'] == Data::PRODUCT_STATUS_DEACTIVATED) {
                        $item['Action'] = trim('Update Item');
                        $basicinfo = self::getProductBasicInfo($productArray);
                        $item['BasicInfo'] = $basicinfo;
                        $subCategoryProperty = self::getSubCategoryProperty($productArray);
                        $item['SubCategoryProperty'] = $subCategoryProperty;
                    } else {
                        continue;
                    }
                    $itemFeed['Item'][] = $item;
                    $newegg_envelope['Message']['Itemfeed'] = $itemFeed;
                    $productUpload['NeweggEnvelope'] = $newegg_envelope;

                } else {
                    $query = 'SELECT jet.option_id,option_title,jet.option_mpn,option_sku,ngg.newegg_option_attributes,option_image,jet.variant_option1,jet.variant_option2,jet.variant_option3,option_qty,option_price,option_weight,option_unique_id,ngg.item_condition FROM `newegg_product_variants` ngg INNER JOIN `jet_product_variants` jet ON jet.option_id=ngg.option_id WHERE ngg.product_id="' . $id . '"';
                    $productVarArray = Data::sqlRecords($query, "all", "select");
                    $validateResponse = self::validateProduct($productArray);

                    $flag = true;
                    $mainId = [];
                    $countflag = true;
                    $arrayCount = count($productVarArray);
                    foreach ($productVarArray as $value) {

                        $value['product_id'] = $productArray['product_id'];
                        // $value['sku']=$productArray['sku'];
                        $value['spn'] = $productArray['spn'];
                        $value['newegg_data'] = $productArray['newegg_data'];
                        if (isset($productArray['manufacturer']) && $productArray['manufacturer']) {
                            $value['manufacturer'] = $productArray['manufacturer'];
                        } else {
                            $value['manufacturer'] = $productArray['vendor'];
                        }
                        $value['bullet_description'] = $productArray['short_description'];
                        if (isset($productArray['long_description']) && !is_null($productArray['long_description']) && $productArray['long_description']) {
                            $value['description'] = self::changeDescription($productArray['long_description']);
                        } else {
                            $value['description'] = self::changeDescription($productArray['description']);
                        }
                        $value['category_path'] = $productArray['category_path'];
                        $value['newegg_attributes'] = $productArray['newegg_attributes'];
                        $value['shopify_product_type'] = $productArray['shopify_product_type'];
                        $value['category_id'] = $subCategoryID[0];
                        $value['mapped_value_data'] = $productArray['mapped_value_data'];
                        $subCategoryID = explode(',', $productArray['category_path']);
                        if (isset($validateResponse['error'])) {
                            $error[$productArray['sku']] = $validateResponse['error'];
                            $query1 = 'UPDATE `newegg_product` SET upload_status ="' . Data::PRODUCT_STATUS_NOT_UPLOADED . '", error="" where product_id="' . $id . '"';
                            Data::sqlRecords($query1, null, 'update');
                            continue;
                        } else {
                            if (isset($session['newegg_subcategory_' . $subCategoryID[0]]) && !empty($session['newegg_subcategory_' . $subCategoryID[0]])) {
                                $catCollection = $session['newegg_subcategory_' . $subCategoryID[0]];
                            } else {
                                $catCollection = Categoryhelper::getNeweggCategory($subCategoryID[0]);
                            }
                            if (!$catCollection) {
                                $error[$productArray['sku']] = "Invalid Category Selected for Product having sku :'" . $productArray['sku'] . "'";
                                continue;
                            }
                        }
                        $itemFeed['SummaryInfo'] = array('SubCategoryID' => trim($subCategoryID[1]));

                        if ($productArray['upload_status'] == Data::PRODUCT_STATUS_NOT_UPLOADED) {
                            $item['Action'] = trim('Create Item');
                        } elseif ($productArray['upload_status'] == Data::PRODUCT_STATUS_SUBMITTED || $productArray['upload_status'] == Data::PRODUCT_STATUS_ACTIVATED) {
                            $item['Action'] = trim('Update Item');
                        }
                        $basicinfo = VariantsProduct::getProductBasicInfo($value);
                        if ($flag) {
                            $mainId['id'] = trim($value['option_sku']);
                            //$mainId['id']=trim($mainSku);
                            $uploadProductIds[] = $id;
                            $flag = false;
                        }
                        if (!$countflag) {
                            $basicinfo['RelatedSellerPartNumber'] = $mainId['id'];
                            $item['BasicInfo'] = $basicinfo;
                            if ($productArray['upload_status'] != Data::PRODUCT_STATUS_SUBMITTED || $productArray['upload_status'] != Data::PRODUCT_STATUS_ACTIVATED) {
                                $subCategoryProperty = VariantsProduct::getVariantsSubCategoryProperty($value);
                                $item['SubCategoryProperty'] = $subCategoryProperty;
                            }
                            $itemFeed['Item'][] = $item;
                            $newegg_envelope['Message']['Itemfeed'] = $itemFeed;
                            $productUpload['NeweggEnvelope'] = $newegg_envelope;
                        } else {
                            $item['BasicInfo'] = $basicinfo;
                            if ($productArray['upload_status'] != Data::PRODUCT_STATUS_SUBMITTED || $productArray['upload_status'] != Data::PRODUCT_STATUS_ACTIVATED) {
                                $subCategoryProperty = VariantsProduct::getVariantsSubCategoryProperty($value);
                                $item['SubCategoryProperty'] = $subCategoryProperty;
                            }
                            $itemFeed['Item'][] = $item;
                            $newegg_envelope['Message']['Itemfeed'] = $itemFeed;
                            $productUpload['NeweggEnvelope'] = $newegg_envelope;
                        }
                        $countflag = false;

                    }

                }

            }

            $postData = json_encode($productUpload);
            $postData = str_replace("\/", "/", $postData);
            if (count($productUpload) > 0) {
                $obj = new Neweggapi(SELLER_ID, AUTHORIZATION, SECRET_KEY);
                $response = $obj->postRequest('/datafeedmgmt/feeds/submitfeed', ['body' => $postData,
                    'append' => '&requesttype=ITEM_DATA']);
                $server_output = json_decode($response, true);
                if (isset($server_output['IsSuccess']) && $server_output['IsSuccess']) {
                    foreach ($server_output['ResponseBody'] as $key => $value) {
                        foreach ($value as $key1 => $val1) {
                            $old_date_timestamp = strtotime($val1['RequestDate']);
                            $new_date = date('Y-m-d H:i:s', $old_date_timestamp);
                            $query = "INSERT INTO `newegg_product_feed` (`merchant_id`,`feed_id`,`product_ids`,`status`,`created_at`,`request_for`)VALUES(" . MERCHANT_ID . ",'" . $val1['RequestId'] . "','" . implode(',', $uploadProductIds) . "','" . $val1['RequestStatus'] . "','" . $new_date . "','" . Data::FEED_PRODUCT_UPLOAD . "')";
                            Data::sqlRecords($query, null, 'insert');
                            foreach ($uploadProductIds as $pid) {
                                $query1 = 'UPDATE `newegg_product` SET upload_status ="' . $val1['RequestStatus'] . '", error="" where product_id="' . $pid . '"';
                                Data::sqlRecords($query1, null, 'update');

                            }
                            $logPath = MERCHANT_ID . '/productupload/' . $val1['RequestId'];
                            Helper::createLog($postData, $logPath);
                            return ['uploadIds' => $uploadProductIds, 'feedId' => $val1['RequestId'], 'erroredSkus' => $error];
                        }

                    }

                }
            }
            if (count($error) > 0) {
                return ['errors' => $error, 'erroredSkus' => $error];
            }
        }

    }

    /**
     * validate product
     * @param [] $product
     * @return bool
     */
    public static function validateProduct($product)
    {
        $price = $product['price'];
        $qty = $product['qty'];
        $errorArr = [];
        $validatedProduct = [];
        $validatedPro = [];
        $subCategoryID = explode(',', $product['category_path']);
        $descriptionCheckbool = true;
        $error_set = false;
        if (!$product['description'] && !$product['long_description'] && !$error_set) {
            $errorArr[] = "Product description is required";
            $descriptionCheckbool = false;
            $error_set = true;
        }
        if ($descriptionCheckbool) {
            /*$product['description'] = self::changeDescription($product['description']);*/

            if (isset($product['long_description']) && !is_null($product['long_description']) && !empty($product['long_description'])) {
                $product['long_description'] = self::changeDescription($product['long_description']);
                $descriptionCheck = self::descriptionValidate($product['long_description']);
                if (!$descriptionCheck) {
                    $errorArr[] = "product description invalid only 'ol','ul','li','br','p','b','i','u','em','strong','sub','sup' tags are allowed";
                    $error_set = true;
                }

            } else {
                if ($product['description']) {
                    $product['description'] = self::changeDescription($product['description']);
                    $descriptionCheck = self::descriptionValidate($product['description']);
                    if (!$descriptionCheck) {
                        $errorArr[] = "product description invalid only 'ol','ul','li','br','p','b','i','u','em','strong','sub','sup' tags are allowed";
                        $error_set = true;
                    }
                }
            }
        }
        /*if(isset($product['long_description']) && !is_null($product['long_description']) && !empty($product['long_description'])){
            $product['long_description'] = self::changeDescription($product['long_description']);
             $descriptionCheck = self::descriptionValidate($product['long_description']);
                if(!$descriptionCheck){
                    $errorArr[]="product description invalid only 'ol','ul','li','br','p','b','i','u','em','strong','sub','sup' tags are allowed";
                }

        }
        else{
            if($product['description']){
                $product['description'] = self::changeDescription($product['description']);
                $descriptionCheck = self::descriptionValidate($product['description']);
                if(!$descriptionCheck){
                    $errorArr[]="product description invalid only 'ol','ul','li','br','p','b','i','u','em','strong','sub','sup' tags are allowed";
                }
            }
        }*/
        if (!$error_set) {
            if (isset($subCategoryID[0]) && empty($subCategoryID[0])) {
                $errorArr[] = "Missing category";
                $error_set = true;
            }
        }

        if (!$error_set) {
            if (!$product['weight']) {
                $errorArr[] = "Missing Product weight";
                $error_set = true;
            } else {
                $weight = trim(sprintf("%.2f", trim($product['weight'])));
                if ($weight <= 0.00) {
                    $errorArr[] = "Product weight must be greater than 0.00 lbs";
                    $error_set = true;
                }
            }
        }

        /*if(empty($product['manufacturer'])){
            $manufacturerData =Helper::categoryManufacturerDetail($product['shopify_product_type']);
            if(empty($manufacturerData['manufacturer'])){
                 $manufacturerConfigData =Helper::configurationDetail(MERCHANT_ID);
                 if(empty($manufacturerConfigData['manufacturer'])){
                     $errorArr[]="Product manufacturer not set";
                 }

            }

        }*/

        if (!$product['vendor']) {
            $errorArr[] = "Product Brand not set";
            $error_set = true;
        }
        if (!$error_set) {
            $image = "";
            $image = trim($product['image']);
            $countImage = 0;
            $imageArr = [];
            $ImageFlag = false;
            $imageArr = explode(',', $image);
            if ($image != "" && count($imageArr) > 0) {
                foreach ($imageArr as $value) {
                    if (self::checkRemoteFile($value) == false)
                        $countImage++;
                }
                if (count($imageArr) == $countImage)
                    $ImageFlag = true;
            }
            if ($image == '' || $ImageFlag) {
                $errorArr[] = "Missing or Invalid Image,";
                $error_set = true;

            }
        }

        /*if(count($imageArr)>0){
            $imageCheck = self::imageValidate($imageArr[0]);
            if(!$imageCheck){
                $errorArr[]="Invalid image";
            }
        }*/


        $upc = '';
        $upc = $product['upc'];
        if ($product['type'] == "simple") {
            if (($price <= 0 || ($price && !is_numeric($price))) || trim($price) == "") {
                $errorArr[] = "Missing/invalid price";
                $error_set = true;

            }
            if (($qty && !is_numeric($qty)) || trim($qty) == "" || ($qty <= 0 && is_numeric($qty))) {
                $errorArr[] = "Missing/invalid inventory";
                $error_set = true;

            }
            $type = "";
            $type = self::checkUpcType($upc);
            $existUpc = false;

            if ($type) {
                /*if (!$type)
                    $existUpc = self::checkUpcSimple($upc, $product['product_id']);*/
                $existUpc = self::validateProductBarcode($upc, $product['variant_id']);


                if ($product['upc'] == "" || (strlen($product['upc']) > 0 && $type == "") || (strlen($product['upc']) > 0 && !$existUpc)) {
                    $errorArr[] = "Missing/invalid barcode";
                    $error_set = true;
                }
            } else {
                if (empty($product['mpn']) || is_null($product['mpn']) || $product['mpn'] == '') {
                    $errorArr[] = "Missing/invalid barcode";
                    $error_set = true;
                }
            }

            /*if ($product['upload_status'] == Data::PRODUCT_STATUS_NOT_UPLOADED)
                $validatedPro[$product['sku']] = "not_uploaded";
            else
                $validatedPro[$product['sku']] = "";*/


        } else {

            //check if newegg attributes exist
            $isexistAttr = false;
            $checkattributes = false;

            if (!$error_set) {
                $checkattributes = self::checkAttribute($product['category_path']);
                $isexistAttr = self::checkAttributes($product);
                if ($checkattributes) {
                    if (!$isexistAttr) {
                        $errorArr[] = "Missing newegg attributes";
                        $error_set = true;
                    }
                }
            }

            if (!$error_set) {
                /*if (self::checkAttribute($product['category_path'])) {*/
                if ($checkattributes) {
                    if (!$isexistAttr) {
                        $attr = self::checkVariantsProductMapping($product);
                        if (!$attr) {
                            $errorArr[] = "group attribute not map";
                            $error_set = true;
                        }
                    }
                }
            }

            $par_qty = 0;
            /*$par_price = "";*/
            $par_qty = trim($product['qty']);
            if ($par_qty == "")
                $par_qty = 0;
            /*$par_price = trim($product['price']);
            $c_par_price = false;*/
            $c_par_qty = false;
            /*if ($par_price <= 0 || (trim($par_price) && !is_numeric($par_price)) || trim($par_price) == "") {
                $c_par_price = false;
            } else {
                $c_par_price = true;
            }*/
            if ((trim($par_qty) <= 0 || !is_numeric($par_qty))) {
                $c_par_qty = false;
            } else {
                $c_par_qty = true;
            }
            //check if newegg attributes not available for category
            /*if (!$isexistAttr) {
                if (($price <= 0 || ($price && !is_numeric($price))) || trim($price) == "") {
                    $errorArr[] = "Missing/invalid price";
                }
                if (($qty && !is_numeric($qty)) || trim($qty) == "" || ($qty <= 0 && is_numeric($qty))) {
                    $errorArr[] = "Missing/invalid inventory";
                }
                //product variant as simple
                $type = "";
                $type = productInfo::checkUpcType($upc);
                if ($type != "")
                    $existUpc = self::checkUpcVariantSimple($product['upc'], $product['product_id'], $product['sku']);
                //$validatedPro[$product['sku']] = $this->getItem($product['sku'], 'publishedStatus');
            } else {*/
            $productVarArray = [];
            /* print_r($product);*/
            $query = 'SELECT ngg.option_id,option_sku,option_image,option_qty,option_mpn,option_price,option_weight,option_unique_id,ngg.upload_status FROM `jet_product_variants` jet INNER JOIN `newegg_product_variants` ngg ON jet.option_id=ngg.option_id WHERE jet.product_id="' . $product['product_id'] . '"';
            $productVarArray = Data::sqlRecords($query, "all", "select");

            foreach ($productVarArray as $pro) {
                $upc = "";
                $price = "";
                $qty = 0;
                $opt_sku = "";
                $opt_sku = trim($pro['option_sku']);
                $qty = trim($pro['option_qty']);
                if ($qty == "")
                    $qty = 0;
                $price = trim($pro['option_price']);
                $upc = trim($pro['option_unique_id']);
                if (!$c_par_qty && trim($qty) <= 0) {
                    $errorArr[] = "Missing/invalid inventory for variants sku: " . $pro['option_sku'];
                }
                if (!$pro['option_weight']) {
                    $errorArr[] = "Missing Product weight";
                }
                if ($pro['option_weight']) {

                    $weight = trim(sprintf("%.2f", trim($product['weight'])));
                    if ($weight <= 0.00) {
                        $errorArr[] = "Product weight must be greater than 0.00 lbs";
                    }
                }
                if (isset($pro['option_image']) && empty($pro['option_image'])) {
                    $errorArr[] = "Missing or Invalid Option Image,";
                }
                //check upc type
                $type = "";
                $existUpc = false;
                $type = productinfo::checkUpcType($upc);
                $productasparent = 0;
                if ($product['sku'] == $pro['option_sku']) {
                    $productasparent = 1;
                }

                if ($type) {
                    /*if (!$type)
                        $existUpc = self::checkUpcSimple($upc, $product['product_id']);*/
                    $existUpc = self::validateProductBarcode($upc, $pro['option_id']);

                    if ($product['upc'] == "" || (strlen($product['upc']) > 0 && $type == "") || (strlen($product['upc']) > 0 && !$existUpc)) {
                        $errorArr[] = "Missing/invalid barcode";
                        $error_set = true;
                    }
                } else {
                    if (empty($product['mpn']) || is_null($product['mpn']) || $product['mpn'] == '') {
                        $errorArr[] = "Missing/invalid barcode";
                        $error_set = true;
                    }
                }

            }

            /*}*/
        }
        if (count($errorArr) > 0) {
            $validatedProduct['error'] = $errorArr;
        } else {
            $validatedProduct['success'] = true;
        } /*elseif (count($validatedPro) > 0) {
            $validatedProduct['success'] = $validatedPro;
        }*/
        return $validatedProduct;
    }

    public static function checkRemoteFile($url)
    {
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $headers = get_headers($url);
        if (substr($headers[0], 9, 3) == '200') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check required category attributes On Newegg
     * @param string|[] $category_id
     * @return bool
     */
    public static function checkAttributes($product)
    {
        if (isset($product['newegg_attributes']) && !empty($product['newegg_attributes'])) {
            return true;
        } elseif ($product['shopify_product_type'] && !empty($product['shopify_product_type']) && $product['category_path'] && !empty($product['category_path'])) {
            $category_id = explode(',', $product['category_path']);

            $query = 'SELECT `attribute_map_data` from newegg_attribute_map Where `shopify_product_type`="' . $product['shopify_product_type'] . '" and `newegg_category_id`="' . $category_id[0] . '"';
            $productVarArray = Data::sqlRecords($query, "one", "select");
            if (!empty($productVarArray) && !is_null($productVarArray)) {
                return true;
            }
        }
        return false;
    }

    /**
     * check variant product attribute mapping
     * @return bool
     */
    public static function checkVariantsProductMapping($product, $connection = array())
    {
        $query = 'SELECT `newegg_option_attributes` from newegg_product_variants Where `product_id`="' . $product['product_id'] . '"';
        $productVarArray = Data::sqlRecords($query, "one", "select");
        if (!empty($productVarArray['newegg_option_attributes'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkUpcType($product_upc)
    {
        if (is_numeric($product_upc)) {
            if (strlen($product_upc) == 12)
                return "UPC";
            elseif (strlen($product_upc) == 10)
                return "ISBN";
            elseif (strlen($product_upc) == 13) {
                /*$string = substr($product_upc, 0, 3);
                if (($string == 978) || ($string == 979)) {
                    return "ISBN";
                } else {
                    return "EAN";
                }*/
                return "EAN";

            }
        }
        return "";
    }

    public static function checkUpcSimple($product_upc = "", $product_id = "", $connection = array())
    {
        if (!isset($connection)) {
            $connection = Yii::$app->getDb();
        }
        $product_upc = trim($product_upc);
        $main_product_count = 0;
        $main_products = array();
        $variant = array();
        $variant_count = 0;
        $queryObj = "";
        $query = "SELECT `id` FROM `jet_product` WHERE upc='" . $product_upc . "' AND id <> '" . $product_id . "'";
        $queryObj = $connection->createCommand($query);
        $main_products = $queryObj->queryAll();
        $main_product_count = count($main_products);
        unset($main_products);
        $queryObj = "";
        $query = "SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='" . $product_upc . "'";
        $queryObj = $connection->createCommand($query);
        $variant = $queryObj->queryAll();
        $variant_count = count($variant);
        unset($variant);
        if ($main_product_count > 0 || $variant_count > 0) {
            return true;
        }
        return false;
    }

    /*
    product basic info prepare
    */
    public static function getProductBasicInfo($productArray)
    {
        $data = [];
        if (isset($productArray['spn']) && !empty($productArray['spn'])) {
            $data['SellerPartNumber'] = trim($productArray['spn']);
        } else {
            $data['SellerPartNumber'] = trim($productArray['sku']);
        }

        if (isset($productArray['manufacturer']) && !empty($productArray['manufacturer'])) {
            $data['Manufacturer'] = trim($productArray['manufacturer']);
        } elseif (isset($productArray['vendor']) && !empty($productArray['vendor'])) {
            $data['Manufacturer'] = trim($productArray['vendor']);
        }
        $type = self::checkUpcType($productArray['upc']);
        if ($type == 'UPC' || $type == "EAN") {
            $data['UPC'] = $productArray['upc'];
        } else {
            $data['ManufacturerPartNumberOrISBN'] = $productArray['mpn'];
        }
        $data['WebsiteShortTitle'] = trim($productArray['title']);
        if (!empty($productArray['short_description'])) {
            $data['BulletDescription'] = trim($productArray['short_description']);
        }
        if (isset($productArray['long_description']) && !empty($productArray['long_description']) && !is_null($productArray['long_description'])) {
            $description = Data::trimString(self::changeDescription($productArray['long_description']), 3500);
            $data['ProductDescription'] = trim($description);
        } else {
            $description = Data::trimString(self::changeDescription($productArray['description']), 3500);
            $data['ProductDescription'] = trim($description);
        }

        $data['ItemWeight'] = trim(sprintf("%.2f", trim($productArray['weight'])));

        // for new / refurbished products.

        if (isset($data['item_condition']) && !empty($data['item_condition'])) {
            $data['ItemCondition'] = $data['item_condition'];
        } else {
            $data['ItemCondition'] = 'New';
        }

        $data['PacksOrSets'] = '1';
        $data['ShippingRestriction'] = trim('no');
        $data['SellingPrice'] = "<script> </script>";
        $data['Shipping'] = trim('Free');
        $data['Inventory'] = (int)trim($productArray['qty']);
        //print_r($_SESSION['REMOTE-ADDR']);die("KK");
        $data['ActivationMark'] = 'True';
        $image = explode(',', $productArray['image']);
        foreach ($image as $key => $value) {
            if ($key > 7) {
                continue;
            }
            if ($key == 0) {
                $imageData[] = ['ImageUrl' => $value, 'IsPrimary' => 'True'];
            } else {
                $imageData[] = ['ImageUrl' => $value];
            }
        }
        // $data['ItemImages']=[['Image'=>['ImageUrl'=>'http://www.hdwallpapers.in/walls/minimal_play_vector-wide.jpg']]];
        $data['ActivationMark'] = 'True';
        $imageData = [];
        $image = explode(',', $productArray['image']);

        foreach ($image as $key => $value) {
            if ($key > 7) {
                continue;
            }
            if ($key == 0) {
                $imageData[] = ['ImageUrl' => $value, 'IsPrimary' => 'True'];
            } else {
                $imageData[] = ['ImageUrl' => $value];
            }
        }

        $data['ItemImages'] = [['Image' => $imageData]];

        return $data;

    }

    /*
    Subcategory basic info prepare
    */
    public static function getSubCategoryProperty($productArray)
    {

        $valueData = [];
        if ($productArray['newegg_data']) {
            $data = json_decode($productArray['newegg_data'], true);
            $category = [];
            /*$subCategoryID = explode(',', $productArray['category_path']);*/
            if ($productArray['newegg_attributes']) {
                $neweggAttribute = json_decode($productArray['newegg_attributes'], true);
                $category[trim(str_replace(" ", "", $data['subcategory']['name']))] = $neweggAttribute;
                return $category;
            } elseif ($productArray['shopify_product_type'] && !empty($productArray['shopify_product_type']) && $productArray['category_path'] && !empty($productArray['category_path'])) {
                $category_id = explode(',', $productArray['category_path']);
                $query = 'SELECT `attribute_map_data` from newegg_attribute_map Where merchant_id="' . $productArray['merchant_id'] . '" and `shopify_product_type` COLLATE latin1_general_cs LIKE "' . $productArray['shopify_product_type'] . '" and `newegg_category_id`="' . $category_id[0] . '"';
                $product = Data::sqlRecords($query, "one", "select");
                if (!empty($product)) {
                    $neweggAttribute = json_decode($product['attribute_map_data'], true);
                    $neweggDataValue = $neweggAttribute[$productArray['shopify_product_type']][$category_id[0]];

                    foreach ($neweggDataValue as $key => $value) {
                        if ($value['attribute_value_type'] == AttributeMap::VALUE_TYPE_MIXED || $value['attribute_value_type'] == AttributeMap::VALUE_TYPE_NEWEGG)
                            $valueData[$key] = $value['value'];
                    }
                    if (count($valueData) > 0) {
                        $category[trim(str_replace(" ", "", $data['subcategory']['name']))] = $valueData;
                    }
                    return $category;
                }
            }
        }/* else {

        }*/
    }


    /*function for image validate*/
    public static function imageValidate($image)
    {
        $width = getimagesize($image);
        if ($width[0] < 641 && $width[1] < 481) {
            $headers = get_headers('https://cdn.shopify.com/s/files/1/0080/2962/products/OBNES34.jpg?v=1407546658');
            foreach ($headers as $key => $value) {
                if (strpos($value, 'Content-Length') !== false) {
                    $size = explode(":", $value);
                    break;
                }
            }
            if (5000000 > $size[1]) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    /*
    function for log content for post Data
    */

    public static function log($postData, $merchant_id)
    {
        $file_dir = dirname(\Yii::getAlias('@webroot')) . '/var/productupload/' . $merchant_id . '';
        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0775, true);
        }
        $filenameOrig = "";
        $filenameOrig = $file_dir . '/' . time() . '.json';
        $fileOrig = "";
        $fileOrig = fopen($filenameOrig, 'w+');
        fwrite($fileOrig, $postData);
        fclose($fileOrig);
    }


    /*
    function for description setting
    */
    public static function description()
    {
        $query = "SELECT `value` FROM  `newegg_config` WHERE `merchant_id`='" . MERCHANT_ID . "' AND `data`='product_description'";
        $description = Data::sqlRecords($query, "one");
        if ($description['value'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /*
   function for description setting
   */
    public static function descriptionValidate($description)
    {
        $checkDescription = [];
        $flag = false;
        $neweggDefinedTag = ['ol', 'ul', 'li', 'br', 'p', 'b', 'i', 'u', 'em', 'strong', 'sub', 'sup'];
        $html1 = preg_match_all(
            '/<([\w]+)[^>]*>(.*?)<\/\1>/', $description, $out, PREG_PATTERN_ORDER
        );
        if (isset($out[1])) {
            $result = array_diff($out[1], $neweggDefinedTag);
            if (!empty($result)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }


    }

    public static function checkProductOptionBarcodeOnUpdate($option_array = array(), $variant_array = array(), $variant_id = "", $barcode_type = "", $product_barcode = "", $product_upc = "", $product_id = "", $product_sku = "", $connection = array())
    {
        //$collection=Yii::$app->getDb();
        $return_array = array();
        $return_array['success'] = true;
        $return_array['error_msg'] = "";
        $variant_upc = "";
        $variant_sku = "";
        $err_msg = "";
        $variant_upc = trim($variant_array['upc']);
        $variant_sku = trim($variant_array['optionsku']);
        $match_skus_array = array();
        $matched_flag = false;
        $db_matched_flag = false;
        $parent_matched_flag = false;
        $variant_as_parent = 0;
        if ($variant_sku == trim($product_sku)) {
            $variant_as_parent = 1;
        }
        foreach ($option_array as $option_id => $option_attributes) {
            if (isset($option_attributes['optionsku']) && trim($option_attributes['optionsku']) != $variant_sku) {
                if ($variant_upc == trim($option_attributes['upc']) && isset($option_attributes['barcode_type']) && trim($option_attributes['barcode_type']) == trim($barcode_type)) {
                    $match_skus_array[] = trim($option_attributes['optionsku']);
                    $matched_flag = true;
                }
            }
        }
        if ($variant_as_parent != 1 && $product_upc == $variant_upc && $product_barcode == $barcode_type) {
            $matched_flag = true;
            $parent_matched_flag = true;
        }
        if (!$matched_flag) {
            $matched_flag = $db_matched_flag = productInfo::checkUpcVariants($variant_upc, $product_id, $variant_id, $variant_as_parent, $connection);
        }
        if ($matched_flag) {
            if (count($match_skus_array) > 0) {
                $err_msg = "Entered Barcode matched with Option Sku(s) : " . implode(' , ', $match_skus_array);
            }
            if ($parent_matched_flag) {
                if ($err_msg == "") {
                    $err_msg = "Entered Barcode matched with its Main Product";
                } else {
                    $err_msg .= " & with its Main Product";
                }
            }
            if ($db_matched_flag) {
                $err_msg = "Entered Barcode already exists";
            }
            $err_msg .= ".Please enter unique Barcode.";
            $return_array['success'] = false;
            $return_array['error_msg'] = $err_msg;
        }

        return array($return_array['success'], $return_array['error_msg']);
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


    public static function updateProductImageOnNewegg($ids, $merchant_id, $connection)
    {
        $session = Yii::$app->session;

        $timeStamp = (string)time();
        if (count($ids) > 0) {

            $error = [];
            $productUpload = [];
            $newegg_envelope = array();
            $newegg_envelope['Header'] = array('DocumentVersion' => '1.0');
            $newegg_envelope['MessageType'] = 'BatchItemCreation';
            $message = array();
            $itemFeeds = array();
            $item = array();
            foreach ($ids as $id) {
                $query = "SELECT `ncm`.`category_path`,product_id,title,`ngp`.`merchant_id`,vendor,sku,type,`ngp`.`shopify_product_type`,description,mpn,`ngp`.`long_description`,image,qty,price,weight,vendor,upc,upload_status,`ngp`.`newegg_data`,`ngp`.`manufacturer`,`ngp`.`mapped_value_data`,`ngp`.`short_description`,`ngp`.`newegg_attributes`,`ngp`.`spn` FROM `newegg_product` ngp INNER JOIN `jet_product` jet ON `jet`.`id`=`ngp`.`product_id` INNER JOIN `newegg_category_map` ncm ON `ncm`.`product_type`=`ngp`.`shopify_product_type` WHERE `ngp`.`product_id`='" . $id . "' and `ncm`.`merchant_id`='" . MERCHANT_ID . "' LIMIT 1 ";
                $productArray = Data::sqlRecords($query, "one", "select");

                if (empty($productArray)) {
                    $query1 = "SELECT `jet`.sku FROM `newegg_product` ngp INNER JOIN `jet_product` jet ON `jet`.`id`=`ngp`.`product_id`  WHERE `ngp`.`product_id`='" . $id . "' and `ngp`.`merchant_id`='" . MERCHANT_ID . "' LIMIT 1 ";
                    $productArray1 = Data::sqlRecords($query1, "one", "select");
                    $a['error'] = "Missing shopify product type";
                    $error[$productArray1['sku']] = $a;
                    continue;

                }
                $mainId = $productArray['product_id'];
                $mainSku = $productArray['sku'];
                $subCategoryID = explode(',', $productArray['category_path']);
                if ($productArray['type'] == 'simple') {
                    $validateResponse = self::validateProduct($productArray);
                    $subCategoryID = explode(',', $productArray['category_path']);
                    $uploadProductIds[] = $id;
                    if (isset($validateResponse['error'])) {
                        $error[$productArray['sku']] = $validateResponse['error'];
                        $query1 = 'UPDATE `newegg_product` SET upload_status ="' . Data::PRODUCT_STATUS_NOT_UPLOADED . '", error="" where product_id="' . $id . '"';
                        Data::sqlRecords($query1, null, 'update');
                        continue;
                    } else {
                        if (isset($session['newegg_subcategory_' . $subCategoryID[0]]) && !empty($session['newegg_subcategory_' . $subCategoryID[0]])) {
                            $catCollection = $session['newegg_subcategory_' . $subCategoryID[0]];
                        } else {
                            $catCollection = Categoryhelper::getNeweggCategory($subCategoryID[0]);
                        }
                        if (!$catCollection) {
                            $error[$productArray['sku']] = "Invalid Category Selected for Product having sku :'" . $productArray['sku'] . "'";
                            continue;
                        }
                    }
                    if ($productArray['upload_status'] == Data::PRODUCT_STATUS_SUBMITTED || $productArray['upload_status'] == Data::PRODUCT_STATUS_ACTIVATED || $productArray['upload_status'] == Data::PRODUCT_STATUS_DEACTIVATED) {
                        $itemFeed['SummaryInfo'] = array('SubCategoryID' => trim($subCategoryID[1]));
                        $item = array();
                        $item['Action'] = trim('Update/Append Image');
                        $basicinfo = self::getProductBasicInfo($productArray);
                        $item['BasicInfo'] = $basicinfo;

                        $itemFeed['Item'] = $item;
                        $newegg_envelope['Message']['Itemfeed'][] = $itemFeed;
                        $productUpload['NeweggEnvelope'] = $newegg_envelope;
                        var_dump($productUpload);
                        die;
                    } else {
                        continue;
                    }
                } else {

                    $query = 'SELECT jet.option_id,option_title,jet.option_mpn,option_sku,ngg.newegg_option_attributes,option_image,jet.variant_option1,jet.variant_option2,jet.variant_option3,option_qty,option_price,option_weight,option_unique_id FROM `newegg_product_variants` ngg INNER JOIN `jet_product_variants` jet ON jet.option_id=ngg.option_id WHERE ngg.product_id="' . $id . '"';

                    $productVarArray = Data::sqlRecords($query, "all", "select");
                    $validateResponse = self::validateProduct($productArray);
                    $flag = true;
                    $mainId = [];
                    $countflag = true;
                    $arrayCount = count($productVarArray);
                    foreach ($productVarArray as $value) {
                        $value['product_id'] = $productArray['product_id'];
                        // $value['sku']=$productArray['sku'];
                        $value['spn'] = $productArray['spn'];
                        $value['newegg_data'] = $productArray['newegg_data'];
                        if (isset($productArray['manufacturer']) && $productArray['manufacturer']) {
                            $value['manufacturer'] = $productArray['manufacturer'];
                        } else {
                            $value['manufacturer'] = $productArray['vendor'];
                        }
                        $value['bullet_description'] = $productArray['short_description'];
                        if (isset($productArray['long_description']) && !is_null($productArray['long_description']) && $productArray['long_description']) {
                            $value['description'] = self::changeDescription($productArray['long_description']);
                        } else {
                            $value['description'] = self::changeDescription($productArray['description']);
                        }
                        $value['category_path'] = $productArray['category_path'];
                        $value['newegg_attributes'] = $productArray['newegg_attributes'];
                        $value['shopify_product_type'] = $productArray['shopify_product_type'];
                        $value['category_id'] = $subCategoryID[0];
                        $value['mapped_value_data'] = $productArray['mapped_value_data'];
                        $subCategoryID = explode(',', $productArray['category_path']);
                        if (isset($validateResponse['error'])) {
                            $error[$productArray['sku']] = $validateResponse['error'];
                            $query1 = 'UPDATE `newegg_product` SET upload_status ="' . Data::PRODUCT_STATUS_NOT_UPLOADED . '", error="" where product_id="' . $id . '"';
                            Data::sqlRecords($query1, null, 'update');
                            continue;
                        } else {
                            if (isset($session['newegg_subcategory_' . $subCategoryID[0]]) && !empty($session['newegg_subcategory_' . $subCategoryID[0]])) {
                                $catCollection = $session['newegg_subcategory_' . $subCategoryID[0]];
                            } else {
                                $catCollection = Categoryhelper::getNeweggCategory($subCategoryID[0]);
                            }
                            if (!$catCollection) {
                                $error[$productArray['sku']] = "Invalid Category Selected for Product having sku :'" . $productArray['sku'] . "'";
                                continue;
                            }
                        }
                        $itemFeed['SummaryInfo'] = array('SubCategoryID' => trim($subCategoryID[1]));
                        $item = array();
                        $item['Action'] = trim('Update/Append Image');
                        $basicinfo = VariantsProduct::getProductBasicInfo($value);
                        if ($flag) {
                            $mainId['id'] = trim($value['option_sku']);
                            //$mainId['id']=trim($mainSku);
                            $uploadProductIds[] = $id;
                            $flag = false;
                        }
                        if (!$countflag) {
                            $basicinfo['RelatedSellerPartNumber'] = $mainId['id'];
                            $item['BasicInfo'] = $basicinfo;
                            $itemFeed['Item'] = $item;
                            $newegg_envelope['Message']['Itemfeed'][] = $itemFeed;
                            $productUpload['NeweggEnvelope'] = $newegg_envelope;
                        } else {
                            $item['BasicInfo'] = $basicinfo;
                            $itemFeed['Item'] = $item;
                            $newegg_envelope['Message']['Itemfeed'][] = $itemFeed;
                            $productUpload['NeweggEnvelope'] = $newegg_envelope;
                        }
                        $countflag = false;

                    }

                }

            }
            $postData = json_encode($productUpload);
            var_dump($postData);
            die;
            $postData = str_replace("\/", "/", $postData);
            if (count($productUpload) > 0) {
                $obj = new Neweggapi(SELLER_ID, AUTHORIZATION, SECRET_KEY);
                $response = $obj->postRequest('/datafeedmgmt/feeds/submitfeed', ['body' => $postData,
                    'append' => '&requesttype=ITEM_DATA']);
                $server_output = json_decode($response, true);
                if (isset($server_output['IsSuccess']) && $server_output['IsSuccess']) {
                    foreach ($server_output['ResponseBody'] as $key => $value) {
                        foreach ($value as $key1 => $val1) {
                            $old_date_timestamp = strtotime($val1['RequestDate']);
                            $new_date = date('Y-m-d H:i:s', $old_date_timestamp);
                            $query = "INSERT INTO `newegg_product_feed` (`merchant_id`,`feed_id`,`product_ids`,`status`,`created_at`,`request_for`)VALUES(" . MERCHANT_ID . ",'" . $val1['RequestId'] . "','" . implode(',', $ids) . "','" . $val1['RequestStatus'] . "','" . $new_date . "','" . Data::FEED_PRODUCT_UPLOAD . "')";
                            Data::sqlRecords($query, null, 'insert');
                            foreach ($ids as $pid) {
                                $query1 = 'UPDATE `newegg_product` SET upload_status ="' . $val1['RequestStatus'] . '" where product_id="' . $pid . '"';
                                Data::sqlRecords($query1, null, 'update');

                            }
                            $logPath = MERCHANT_ID . '/productImageupdate/' . $val1['RequestId'];
                            Helper::createLog($postData, $logPath);
                            return ['uploadIds' => $uploadProductIds, 'feedId' => $val1['RequestId'], 'erroredSkus' => $error];
                        }

                    }


                }
            }

            if (count($error) > 0) {
                return ['errors' => $error];
            }
        }

    }

    /* Attribute check for those category which not have required attribute and groupby attribute*/

    public static function checkAttribute($data)
    {
        $data = explode(',', $data);
        $model = Categoryhelper::subcategoryAttribute($data[0], $data[1]);
        if (!empty($model)) {
            $attributesValue = "";
            foreach ($model as $key => $value) {
                if ($value['IsRequired'] == 1 || $value['IsGroupBy'] == 1) {
                    $data[] = $value;
                    return true;
                }
            }

        } /*else {
            return false;
        }
        if (!empty($data)) {
            return false;
        }*/
        return false;
    }

    /*Change product description according to Newegg Market place*/

    public static function changeDescription($description)
    {
        //$description = addslashes($description);
        $removeValueTag = ['button', 'a', 'img'];
        $newdescription = '';
        foreach ($removeValueTag as $key => $value) {
            if ($newdescription != '') {
                $newdescription = preg_replace('/<' . $value . '[^>]*>([\s\S]*?)<\/' . $value . '[^>]*>/', "", $newdescription);
            } else {
                $newdescription = preg_replace('/<' . $value . '[^>]*>([\s\S]*?)<\/' . $value . '[^>]*>/', "", $description);
            }
        }
        $html = $newdescription;
        $html1 = preg_replace_callback(
            '/(<([\w]+)|<\/([\w]+))[^>]*>/',
            function ($matches) {
                $value = '';
                if (isset($matches[1])) {
                    $replaceArray = ['div' => '', 'span' => 'p', 'strong' => 'b', 'a' => '', 'table' => '', 'tr' => 'ul', 'td' => 'li', 'h1' => 'sup', 'h2' => 'sup', 'h3' => 'sup', 'h4' => 'sup', 'button' => '', 'th' => 'b'];
                    $neweggDefinedTag = ['ol' => 1, 'ul' => 1, 'li' => 1, 'br' => 1, 'p' => 1, 'b' => 1, 'i' => 1, 'u' => 1, 'em' => 1, 'strong' => 1, 'sub' => 1, 'sup' => 1];
                    $matches[1] = trim($matches[1]);
                    $check = preg_match('/<([\w]+)[\w]/', $matches[1], $matche);
                    $trimData = trim($matches[1], '<');
                    $trimData = trim($trimData, '/');
                    if ($check) {
                        if (isset($replaceArray[$trimData]) && $replaceArray[$trimData]) {
                            $value = '<' . $replaceArray[$trimData] . '>';
                        } else {
                            $value = '';

                        }

                    } else {
                        if (isset($replaceArray[$trimData]) && $replaceArray[$trimData]) {
                            $value = '</' . $replaceArray[$trimData] . '>';
                        } else {
                            if (!isset($neweggDefinedTag[$trimData])) {
                                $value = '';
                            }
                        }

                    }

                }

                if (is_string($value)) {
                    $value = preg_replace('<!--(.*?)-->', '', $value);
                    return $value;
                } elseif (is_array($value)) {

                    $value = preg_replace('<!--(.*?)-->', '', $value);
                    return json_encode($value);
                } else {
                    $value = preg_replace('<!--(.*?)-->', '', $value);
                    return $value;
                }

            },
            $html
        );
        $foo = preg_replace('/\s+/', ' ', $html1);
        $foo = preg_replace('<!--(.*?)-->', '', $foo);
        $foo = str_replace("<>", "", $foo);
        $postData = str_replace(",", "", $foo);
        return $postData;
    }

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

    public static function deleteProduct($product_ids = [], $merchant_id)
    {

        if (!is_array($product_ids))
            $product_ids = explode(',', $product_ids);

        $val = [];
        $option_id = [];
        $prod_ids = [];
        $prod_id = [];
        $when_product_status = '';
        $when_option_status = '';
        $deletedstatus = 'DELETED';

        if ($product_ids && count($product_ids)) {
            try {
                $errors = [];
                foreach ($product_ids as $product_id) {
                    $productData = Data::getProductData($product_id);

                    if ($productData && isset($productData['type'])) {

                        if ($productData['type'] == 'simple') {
                            $sku = $productData['sku'];

                            $arr['sku'] = $sku;

                            if (!empty($productData['product_price'])) {
                                $arr['price'] = $productData['product_price'];
                            } else {
                                $arr['price'] = $productData['price'];
                            }
                            $arr['webhook'] = true;
                            $when_product_status .= ' WHEN ' . $product_id . ' THEN ' . '"' . $deletedstatus . '"';

                            $prod_id[] = $product_id;
                            $val[] = ProductPrice::getProductBasicInfo($arr);

                        } elseif ($productData['type'] == 'variants') {
                            $productVariants = Data::getProductVariants($product_id);

                            $when_product_status .= ' WHEN ' . $product_id . ' THEN ' . '"' . $deletedstatus . '"';
                            $prod_id[] = $product_id;
                            if ($productVariants) {

                                foreach ($productVariants as $variant) {
                                    $sku = $variant['option_sku'];

                                    $arr['sku'] = $sku;

                                    if (!empty($variant['option_prices'])) {
                                        $arr['price'] = $variant['option_prices'];
                                    } else {
                                        $arr['price'] = $variant['option_price'];
                                    }
                                    $arr['webhook'] = true;
                                    $when_option_status .= ' WHEN ' . $variant['option_id'] . ' THEN ' . '"' . $deletedstatus . '"';

                                    $option_id[] = $variant['option_id'];

                                    $val[] = ProductPrice::getProductBasicInfo($arr);
                                }

                            } else {
                                $errors[$productData['sku']] = "no variants found for this product.";
                            }
                        }
                    }
                }

                if (count($val) > 0) {

                    $option_ids = implode(',', $option_id);
                    $prod_ids = implode(',', $prod_id);
                    $response = ProductPrice::senddeleterequest($val);

                    $res = json_decode($response, true);
                    if (isset($res['IsSuccess']) && isset($res['ResponseBody']['ResponseList'])) {
                        foreach ($res['ResponseBody']['ResponseList'] as $feed) {
                            if ($feed['RequestStatus'] == 'SUBMITTED') {

                                if (!empty($option_ids)) {
                                    $query2 = "UPDATE `newegg_product_variants` SET
                                                      `upload_status` = CASE `option_id`
                                                        " . $when_option_status . " 
                                                        END
                                                        WHERE option_id IN (" . $option_ids . ") AND merchant_id =" . $merchant_id . "";
                                    Data::sqlRecords($query2, null, 'update');
                                }

                                if (!empty($prod_ids)) {
                                    $query = "UPDATE newegg_product SET 
                                                        upload_status= CASE `product_id`
                                                        " . $when_product_status . "
                                                        END
                                                        WHERE product_id IN (" . $prod_ids . ") AND merchant_id='" . $merchant_id . "'";

                                    Data::sqlRecords($query, null, 'update');
                                }
                            }
                        }
                    } else {
                        $errors[] = "Product deleted feed not submitted successfully";
                    }
                }

                if (count($errors))
                    return json_encode(['error' => true, 'message' => implode(',', $errors)]);
                else
                    return json_encode(['success' => true, 'success_count' => count($product_ids), 'message' => "Product(s) Deleted Successfully!!"]);
            } catch (Exception $e) {
                return json_encode(['error' => true, 'message' => "Error : " . $e->getMessage()]);
            }
        } else {
            return json_encode(['error' => true, 'message' => "No product selected for delete."]);
        }
    }
}