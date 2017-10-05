<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;
use frontend\components\Jetproductinfo;


class Uploadproduct extends Component
{
    /**
     * @param $Output
     * @return array|bool|string
     */
    public function getProductDetails($Output)
    {

        // die('zzz');
        if (isset($Output['filter'])) {
            $out = json_decode($Output['filter'], true);

            $Output['filter'] = $out;
        }

        try {
            $orderdetail = self::getDetails($Output);

        } catch (\Exception $e) // an exception is raised if a query fails
        {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return $orderdetail;
    }

    /**
     * @param $Output
     * @return array|string
     */
    public function getDetails($Output)
    {

        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');


        $pageInfo = Yii::$app->request->post();
       // $selectedProducts = explode(',', $pageInfo['ids']);
        $selectedProducts = json_decode( $pageInfo['ids'],true);
       // print_r(json_decode( $pageInfo['ids'],true));die;
        $connection = Yii::$app->getDb();
        $counts = count($selectedProducts);
        // print_r($counts);die;
        $newCustomPrice = false;
        $setCustomPrice = array();
        $setCustomPrice = $connection->createCommand('SELECT `data`,`value` from `jet_config` where merchant_id="' . $merchant_id . '" AND data="set_price_amount"  ')->queryOne();
        if (is_array($setCustomPrice) && isset($setCustomPrice['value'])) {
            $newCustomPrice = $setCustomPrice['value'];
        }
        $updatePriceType = "";
        $updatePriceValue = 0;
        if ($newCustomPrice) {
            $customPricearr = array();
            $customPricearr = explode('-', $newCustomPrice);
            $updatePriceType = $customPricearr[0];
            $updatePriceValue = $customPricearr[1];
        }
        $priceType = '';
        $priceValue = 0;
        $message = "";

        $apistatusmessage = array();
        $priceType = $updatePriceType;
        $priceValue = $updatePriceValue;
        define("MERCHANT", $merchant_id);
        foreach ($selectedProducts as $key => $value) {

            if (!$counts) {
                $returnArr = ['error' => true, 'message' => 'No Products to Upload'];
                return BaseJson::encode($returnArr);
            } else {
                if ($counts < 51) {
                    $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');


                    try {
                        $jetConfig = [];
                        $jetConfig = Datahelper::sqlRecords("SELECT `fullfilment_node_id`,`api_user`,`api_password` FROM `jet_configuration` WHERE merchant_id='" . $merchant_id . "'", 'one');
                        if ($jetConfig) {
                            $fullfillmentnodeid = $jetConfig['fullfilment_node_id'];
                            $api_host = "https://merchant-api.jet.com/api";
                            $api_user = $jetConfig['api_user'];
                            $api_password = $jetConfig['api_password'];
                            $jetHelper = new Jetapi($api_host, $api_user, $api_password);

                            $resultDes = "";
                            $result = array(); //mersku // basic info
                            $node = array();
                            $inventory = array();
                            $price = array();
                            $count = 0;
                            $statusmessage = '';
                            $uploadErrors = array();
                            $responseOption = array();
                            //$variation=array();
                            //$relationship = array();
                            //$uploadIds=array();
                            //$uploadFinal=array();
                            //$skuExist=array();
                            /* ------------------upload data preparation start-----------------*/
                            $SKU_Array = array();
                            $unique = array();
                            $variationCount = 0;
                            $Attribute_arr = array();
                            $Attribute_array = array();
                            $_uniquedata = array();
                            $pid = 0;
                            $pid = trim($selectedProducts[$key]);
                            //$product = JetProduct::findOne((int)$pid);
                            $product = "";
                            $queryObj = "";
                            $queryObj = $connection->createCommand("SELECT `id`,`title`,`sku`,`type`,`product_type`,`description`,`variant_id`,`image`,`qty`,`weight`,`price`,`attr_ids`,`jet_attributes`,`vendor`,`upc`,`barcode_type`,`mpn`,`ASIN`,`fulfillment_node` FROM `jet_product` WHERE id='" . $pid . "'");
                            $product = (object)$queryObj->queryOne();
                            $not_exists_flag = false;
                            //if category has no attributes---start
                            $errordisplay = "";

                            $not_exists_flag = Jetproductinfo::checkCategoryAttributeNotExists($product->fulfillment_node, $jetHelper, $merchant_id);

                            //$not_exists_flag=Jetproductinfo::checkCategoryAttributeNotExists($product->fulfillment_node,$merchant_id);


                            if ($not_exists_flag) {
                                $carray = array();
                                $carray = Jetproductinfo::checkproductnoattr($product, $merchant_id, $connection);

                                if (!$carray['success']) {
                                    if ($carray['error'] && is_array($carray['error'])) {
                                        $uploadErrors = array();
                                        $isCkeckUpc = false;
                                        foreach ($carray['error'] as $ckey => $cvalue) {
                                            if (is_array($cvalue)) {
                                                foreach ($cvalue as $ck => $cv) {
                                                    $uploadErrors[$ckey][] = $cv;
                                                }
                                            } else {
                                                $uploadErrors[$ckey][] = $cvalue;
                                                //$errordisplay.=$cvalue.'<br>';
                                                if ($ckey == "brand_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "node_id_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "image_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "sku_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "upc_error") {
                                                    $isCkeckUpc = true;
                                                    $errordisplay .= $cvalue . "<br>";
                                                }
                                                if ($ckey == "price_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "qty_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                /* if($ckey=="upc_error_info")
                                                 $errordisplay.=$cvalue."<br>"; */
                                                if ($ckey == "mpn_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "asin_error_info" && $isCkeckUpc == false)
                                                    $errordisplay .= $cvalue . "<br>";
                                            }
                                        }
                                        if (count($uploadErrors) > 0) {
                                            $message .= "<b>There are following information that are incomplete/wrong for given product(s):</b><ul>";
                                            if (isset($uploadErrors['price']) && count($uploadErrors['price']) > 0) {
                                                $message .= "<li><span class='required_label'>Wrong Price</span>
                                            <ul>
                                                <li>" . implode(', ', $uploadErrors['price']) . "</li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['qty']) && count($uploadErrors['qty']) > 0) {
                                                $message .= "<li><span class='required_label'>Wrong Quantity : Quantity must be greater than 0</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['qty']) . "</span></li>
                                            </ul>
                                      </li>";
                                            }
                                            if (isset($uploadErrors['upc']) && count($uploadErrors['upc']) > 0) {
                                                $message .= "<li><span class='required_label'>Product must require Unique Code either Barcode(UPC,GTIN-14,ISBN-10,ISBN-13) Or ASIN.Barcode or ASIN must be unique for each product and their variants.</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['upc']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['mpn']) && count($uploadErrors['mpn']) > 0) {
                                                $message .= "<li><span class='required_label'>Invalid MPN.Length must be atmost 50.</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['mpn']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['node_id']) && count($uploadErrors['node_id']) > 0) {
                                                $message .= "<li>
                                           <span class='required_label'>Missging Jet Browse Node</span>
                                           <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['node_id']) . "</span></li>
                                            </ul>
                                      </li>";
                                            }

                                            if (isset($uploadErrors['brand']) && count($uploadErrors['brand']) > 0) {
                                                $message .= "<li><span class='required_label'>Missing Brand</span></li>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['brand']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }

                                            if (isset($uploadErrors['image']) && count($uploadErrors['image']) > 0) {
                                                $message .= "<li><span class='required_label'>Product must have atleast one valid image</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['image']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }
                                            $message .= "</ul>";
                                            $return_msg['error'] = $message;
                                         //   $apistatusmessage[$sku] = $return_msg['error'];
                                            if ($errordisplay != "") {
                                                $sql = "UPDATE `jet_product` SET  error='" . $errordisplay . "' where id='" . $pid . "'";
                                                $model = $connection->createCommand($sql)->execute();
                                                //$product->error=$errordisplay;
                                                //$product->save(false);
                                            }
                                            /*unset($uploadErrors);
                                            unset($errordisplay);
                                            unset($product);
                                            return json_encode($return_msg);*/
                                        }
                                    }
                                }
                            } //if category has no attributes---end
                            else {
                                $carray = array();
                                $uploadErrors = array();
                                $carray = Jetproductinfo::checkBeforeDataPrepare($product, $merchant_id, $connection);
                                if (!$carray['success']) {
                                    if ($carray['error'] && is_array($carray['error'])) {
                                        $isCheckSimpleUpc = false;
                                        $isCheckVarUpc = false;
                                        foreach ($carray['error'] as $ckey => $cvalue) {
                                            if (is_array($cvalue)) {
                                                $str = "";
                                                foreach ($cvalue as $ck => $cv) {
                                                    $str .= $cv . ", ";
                                                    $uploadErrors[$ckey][] = $cv;
                                                }
                                                if ($ckey == "sku_error_var")
                                                    $errordisplay .= "Missing Variants Sku(s)[" . $str . "]<br>";
                                                if ($ckey == "upc_error_var") {
                                                    $isCheckVarUpc = true;
                                                    $errordisplay .= "Missing Variants Barcode or ASIN or MPN[" . $str . "]<br>";
                                                }
                                                if ($ckey == "price_error_var")
                                                    $errordisplay .= "Invalid Variants Price[" . $str . "]<br>";
                                                if ($ckey == "qty_error_var")
                                                    $errordisplay .= "Invalid Variants Quantity[" . $str . "]<br>";
                                                /* if($ckey=="upc_error_info_var")
                                                 $errordisplay.="Duplicate or Invalid Variants Barcode[".$str."]<br>"; */
                                                if ($ckey == "mpn_error_var")
                                                    $errordisplay .= "Invalid Variants Mpn[" . $str . "]<br>";
                                                if ($ckey == "asin_error_info_var" && $isCheckVarUpc == false)
                                                    $errordisplay .= "Duplicate/Invalid Variants Barcode or ASIN or MPN[" . $str . "]<br>";
                                                if ($ckey == "attribute_mapping_error")
                                                    $errordisplay .= "Need to map Variant Options with Jet Attributes[" . $str . "]<br>";
                                                //if($ckey=="brand_error")
                                            } else {
                                                $uploadErrors[$ckey][] = $cvalue;
                                                if ($ckey == "brand_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "node_id_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "image_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "sku_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "upc_error") {
                                                    $isCheckSimpleUpc = true;
                                                    $errordisplay .= $cvalue . "<br>";
                                                }
                                                if ($ckey == "price_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "qty_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                /* if($ckey=="upc_error_info")
                                                 $errordisplay.=$cvalue."<br>"; */
                                                if ($ckey == "mpn_error")
                                                    $errordisplay .= $cvalue . "<br>";
                                                if ($ckey == "asin_error_info" && $isCheckSimpleUpc == false)
                                                    $errordisplay .= $cvalue . "<br>";
                                            }
                                        }
                                        if (count($uploadErrors) > 0) {
                                            $message .= "<b>There are following information are incomplete/wrong for given product(s):</b><ul>";
                                            if (isset($uploadErrors['price']) && count($uploadErrors['price']) > 0) {
                                                $message .= "<li><span class='required_label'>Wrong Price</span>
                                            <ul>
                                                <li>" . implode(', ', $uploadErrors['price']) . "</li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['qty']) && count($uploadErrors['qty']) > 0) {
                                                $message .= "<li><span class='required_label'>Wrong Quantity : Quantity must be greater than 0</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['qty']) . "</span></li>
                                            </ul>
                                      </li>";
                                            }
                                            if (isset($uploadErrors['attribute_mapping']) && count($uploadErrors['attribute_mapping']) > 0) {
                                                $message .= "<li><span class='required_label'>For variants product - Shopify Option(s) must be mapped with at least one Jet Attributes.</span>
                                            <ul>
                                                <li>" . implode(', ', $uploadErrors['attribute_mapping']) . "</li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['upc']) && count($uploadErrors['upc']) > 0) {
                                                $message .= "<li><span class='required_label'>Product must require Unique Code either Barcode(UPC,GTIN-14,ISBN-10,ISBN-13) Or ASIN.Barcode or ASIN must be unique for each product and their variants.</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['upc']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['mpn']) && count($uploadErrors['mpn']) > 0) {
                                                $message .= "<li><span class='required_label'>Invalid MPN.Length must be atmost 50.</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['mpn']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }
                                            if (isset($uploadErrors['node_id']) && count($uploadErrors['node_id']) > 0) {
                                                $message .= "<li>
                                           <span class='required_label'>Missging Jet Browse Node.</span>
                                           <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['node_id']) . "</span></li>
                                            </ul>
                                      </li>";
                                            }

                                            if (isset($uploadErrors['brand']) && count($uploadErrors['brand']) > 0) {
                                                $message .= "<li><span class='required_label'>Missing Brand</span></li>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['brand']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }

                                            if (isset($uploadErrors['image']) && count($uploadErrors['image']) > 0) {
                                                $message .= "<li><span class='required_label'>Product must have atleast one valid image</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['image']) . "</span></li>
                                            </ul>
                                        </li>";
                                            }

                                            $message .= "</ul>";
                                            $return_msg['error'] = $message;
                                          //  $apistatusmessage[$sku] = $return_msg['error'];
                                            if ($errordisplay != "") {
                                                $sql = "UPDATE `jet_product` SET  error='" . $errordisplay . "' where id='" . $pid . "'";
                                                $model = $connection->createCommand($sql)->execute();
                                                //$product->error=$errordisplay;
                                                //$product->save(false);
                                            }
                                            /*unset($uploadErrors);
                                            unset($errordisplay);
                                            unset($product);
                                            return json_encode($return_msg);*/
                                        }
                                    }
                                }
                            }
                            $asin = '';
                            $upc = trim($product->upc);
                            $asin = trim($product->ASIN);
                            $mpn = trim($product->mpn);
                            $brand = trim($product->vendor);
                            $is_variation = false;
                            $_uniquedata = array();
                            $sku = $product->sku;
                            $name = $product->title;
                            $SKU_Array['product_title'] = $name;
                            $nodeid = (int)$product->fulfillment_node;
                            $attribute = $product->jet_attributes;
                            $Attribute_arr = json_decode($attribute);
                            $SKU_Array['jet_browse_node_id'] = $nodeid;
                            $type = "";
                            $type = $product->barcode_type;
                            if ($type == "")
                                $type = JetProductInfo::checkUpcType($upc);

                            if ($upc) {
                                $_uniquedata = array("type" => $type, "value" => $upc);
                                $unique['standard_product_code'] = $_uniquedata['value'];
                                $unique['standard_product_code_type'] = $_uniquedata['type'];
                                $SKU_Array['standard_product_codes'][] = $unique;
                            }

                            if ($asin != null && isset($carray['asin_simp']) && $carray['asin_simp']) {
                                $SKU_Array['ASIN'] = $asin;
                            }
                            $SKU_Array['manufacturer'] = $brand;
                            if ($mpn != null && isset($carray['mpn_simp']) && $carray['mpn_simp']) {
                                $SKU_Array['mfr_part_number'] = $mpn;
                            }
                            $SKU_Array['multipack_quantity'] = 1;
                            $SKU_Array['brand'] = $brand;
                            $description = "";
                            $description = $product->description;
                            //$description=strip_tags($description);

                            if (strlen($description) > 2000)
                                $description = $jetHelper->trimString($description, 2000);
                            $SKU_Array['product_description'] = $description;
                            //send images
                            $parentmainImage = "";
                            $kmain = 0;
                            $images = array();
                            $images = explode(',', $product->image);
                            foreach ($images as $key => $value) {
                                if ($value == "")
                                    continue;

                                if (strpos($value, 'upload/images') !== false) {
                                    $value = 'https://shopify.cedcommerce.com' . Yii::$app->getUrlManager()->getBaseUrl() . '/' . $value;
                                }
                                $value = preg_replace('~\s+~', '%20', $value);
                                if (Jetproductinfo::checkRemoteFile($value) == true) {
                                    $kmain = $key;


                                    $SKU_Array['main_image_url'] = $value;
                                    $SKU_Array['swatch_image_url'] = $value;
                                    break;
                                }
                            }

                            if (count($images) > 1) {
                                $i = 1;
                                foreach ($images as $key => $value) {
                                    if ($key == $kmain)
                                        continue;
                                    if ($i > 8)
                                        break;

                                    if (strpos($value, 'upload/images') !== false) {
                                        $value = 'https://shopify.cedcommerce.com' . Yii::$app->getUrlManager()->getBaseUrl() . '/' . $value;
                                    }
                                    $value = preg_replace('~\s+~', '%20', $value);
                                    if ($value != '' && Jetproductinfo::checkRemoteFile($value) == true) {

                                        $SKU_Array['alternate_images'][] = array(
                                            'image_slot_id' => (int)$i,
                                            'image_url' => $value
                                        );
                                        $i++;
                                    }
                                }
                            }

                            unset($images);
                            if (count($Attribute_arr) > 0 || isset($carray['attribute_mapped'])) {
                                if ($product->type == 'simple') {
                                    $uploadErrors = array();
                                    foreach ($Attribute_arr as $key => $arr) {
                                        // get value of type text/dropdown

                                        if (count($arr) == 1) {
                                            $Attribute_array[] = array(
                                                'attribute_id' => (int)$key,
                                                'attribute_value' => $arr[0]
                                            );
                                        } // get value of text type with unit
                                        elseif (count($arr) == 2) {
                                            /* $resultAttr = $connection->createCommand("SELECT * FROM `jet_attribute_value` WHERE attribute_id='".$key."'")->queryOne();
                                            if(isset($resultAttr['units']) && $resultAttr['units'])
                                            {
                                                $unitArray=explode(',',$resultAttr['units']);
                                                if ($arr[1]!='' && in_array($arr[1], $unitArray))
                                                {
                                                    $Attribute_array[] = array(
                                                                                    'attribute_id'=>(int)$key,
                                                                                    'attribute_value'=>$arr[0],
                                                                                    'attribute_value_unit'=>$arr[1]
                                                                                );
                                                }
                                                else
                                                {
                                                    $uploadErrors['units'][]="Product ".$product->sku." must be attribute units: ".$resultAttr['units'];
                                                    continue;
                                                }
                                            } */
                                            $Attribute_array[] = array(
                                                'attribute_id' => (int)$key,
                                                'attribute_value' => $arr[0],
                                                'attribute_value_unit' => $arr[1]
                                            );
                                        }
                                    }
                                    unset($Attribute_arr);
                                    if (count($uploadErrors) > 0) {
                                        $errordisplay = "";
                                        $message = "";
                                        $message .= "<b>There are following information that are incomplete/wrong for given product(s):</b><ul>";
                                        if (isset($uploadErrors['units']) && count($uploadErrors['units']) > 0) {
                                            $message .= "<li><span class='required_label'>Wrong attribute unit for Parent or Variant Options</span>
                                            <ul>
                                                <li><span class='required_values'>" . implode(', ', $uploadErrors['units']) . "</span></li>
                                            </ul>
                                        </li>";
                                        }
                                        $message .= "</ul>";
                                        $return_msg['error'] = $message;
                                      //  $apistatusmessage[$sku] = $return_msg['error'];
                                        $sql = "UPDATE `jet_product` SET  error='Invalid Jet Attribute Unit' where id='" . $pid . "'";
                                        $model = $connection->createCommand($sql)->execute();
                                        //$product->error="Invalid Jet Attribute Unit";
                                        //$product->save(false);
                                        /*unset($uploadErrors);
                                        unset($product);
                                        return json_encode($return_msg);*/
                                    }
                                } else {
                                    $errordisplay = "";
                                    $uploadErrors = array();
                                    if (isset($carray['attribute_mapped']))
                                        $responseOption = Jetproductinfo::createoption($product, $carray, $jetHelper, $fullfillmentnodeid, $merchant_id, $connection, $carray['attribute_mapped']);
                                    else
                                        $responseOption = Jetproductinfo::createoption($product, $carray, $jetHelper, $fullfillmentnodeid, $merchant_id, $connection);
                                    $responseA = array();
                                    $vresult = '';
                                    $vresponse = array();

                                    if (isset($responseOption['errors'])) {
                                        $uploadErrors['variation_upload'][$product->id] = $responseOption['errors'];
                                        $errordisplay .= "Variants Upload Error: " . $responseOption['errors'] . '<br>';
                                        unset($responseOption['errors']);
                                    }
                                    if (isset($responseOption['children_skus']) && count($responseOption['children_skus']) >= 1) {
                                        $path = \Yii::getAlias('@webroot') . '/var/product/upload/' . $merchant_id . '/' . date('d-m-Y') . '/variant/' . $product->sku . '<=>' . $sku;
                                        if (!file_exists($path)) {
                                            mkdir($path, 0775, true);
                                        }
                                        $filenameOrig = $path . '/variation.log';
                                        $fileOrig = fopen($filenameOrig, 'w');
                                        fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . json_encode($responseOption));

                                        //file log
                                        $responseA = $jetHelper->CPutRequest('/merchant-skus/' . rawurlencode($sku) . '/variation', json_encode($responseOption));
                                        fwrite($fileOrig, PHP_EOL . "variation response: " . PHP_EOL . json_encode($responseA));
                                        $responseA = json_decode($responseA, true);
                                        if (isset($responseA['errors'])) {
                                            $errordisplay .= json_encode($responseA['errors']) . '<br>';
                                            $uploadErrors['variation'][] = $sku . " : " . json_encode($responseA['errors']);
                                        } else {

                                            $vresult = $jetHelper->CGetRequest('/merchant-skus/' . rawurlencode($sku));
                                            $vresponse = json_decode($vresult, true);

                                            if (count($vresponse) > 0 && isset($vresponse['variation_refinements'])) {
                                                //$variationCount++;
                                                $sql = "UPDATE `jet_product` SET  error='',status='Under Jet Review' where id='" . $pid . "'";
                                                $model = $connection->createCommand($sql)->execute();
                                                $return_msg['success'] = "Product with sku : <b>" . $sku . "</b> successfully uploaded.";
                                                 $apistatusmessage[$sku]['Product with sku']=$sku . " successfully uploaded.";
                                                $apistatusmessage[$sku]['success'] = 'true';
                                                $apistatusmessage[$sku]['message'] = 'product successfully uploaded';
                                                
                                                /*unset($product);
                                                return json_encode($return_msg);*/
                                            }
                                        }
                                        fclose($fileOrig);
                                    }
                                    unset($responseA);
                                    unset($vresult);
                                    unset($vresponse);
                                    unset($responseOption);
                                    if (count($uploadErrors) > 0) {
                                        $message = "";
                                        $message .= "<b>There are following information that are incomplete/wrong for given product(s):</b><ul>";
                                        if (isset($uploadErrors['variation']) && count($uploadErrors['variation']) > 0) {
                                            $message .= "<li><span class='required_label'>Error in Variantion Product(s)</span>
                                        <ul>
                                            <li><span class='required_values'>" . implode(', ', $uploadErrors['variation']) . "</span></li>
                                        </ul>
                                    </li>";
                                        }
                                        if (isset($uploadErrors['variation_upload']) && count($uploadErrors['variation_upload']) > 0) {
                                            $message .= "<li><span class='required_label'>Some Variant Product(s) Not uploaded.</span><ul>";
                                            foreach ($uploadErrors['variation_upload'] as $key => $value) {
                                                //$result="";
                                                //$result=JetProduct::findOne($key);
                                                $message .= "<li><b>" . $sku . "</b> => <span class='required_values'>" . $value . "</span></li>";
                                            }
                                            $message .= "</ul></li>";
                                        }
                                        $message .= "</ul>";
                                        $return_msg['error'] = $message;
                                    //    $apistatusmessage[$sku][''] = $return_msg['error'];
                                        if ($errordisplay != "") {
                                            $sql = "UPDATE `jet_product` SET  error='" . $errordisplay . "' where id='" . $pid . "'";
                                            $model = $connection->createCommand($sql)->execute();
                                            //$product->error=$errordisplay;
                                            //$product->save(false);
                                        }
                                        /*unset($uploadErrors);
                                        unset($product);
                                        return json_encode($return_msg);*/
                                    }
                                }
                            }

                            $path = \Yii::getAlias('@webroot') . '/var/product/upload/' . $merchant_id . '/' . date('d-m-Y') . '/simple/' . $sku;
                            if (!file_exists($path)) {
                                mkdir($path, 0775, true);
                            }
                            if (!empty($SKU_Array)) {
                                if (count($Attribute_array) > 0)
                                    $SKU_Array['attributes_node_specific'] = $Attribute_array; // add attributes details
                                $result[$sku] = $SKU_Array; // add merchant sku

                                //file log

                                $filenameOrig = "";
                                $filenameOrig = $path . '/Sku.log';
                                $fileOrig = "";
                                $fileOrig = fopen($filenameOrig, 'a+');
                                fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . json_encode($result));
                                fclose($fileOrig);
                                //file log
                                unset($SKU_Array);
                                unset($Attribute_array);
                                $qty = 0;
                                $qty = $product->qty;
                                $resultQty = '';

                                $newPriceValue = $product->price;
                                // change new price
                                $option_price_new = 0;
                                if ($priceType != '' && $priceValue != 0) {
                                    $updatePrice = 0;
                                    $updatePrice = self::priceChange($newPriceValue, $priceType, $priceValue);
                                    if ($updatePrice != 0)
                                        $newPriceValue = $updatePrice;
                                }
                                $price[$sku]['price'] = (float)$newPriceValue;//$product->price;
                                $node['fulfillment_node_id'] = $fullfillmentnodeid;
                                $node['fulfillment_node_price'] = (float)$newPriceValue;//$product->price;
                                $price[$sku]['fulfillment_nodes'][] = $node; //price
                                // Add inventory
                                //$qty= $product->qty;
                                $node1['fulfillment_node_id'] = $fullfillmentnodeid;
                                $node1['quantity'] = (int)$qty;
                                $inventory[$sku]['fulfillment_nodes'][] = $node1; // inventory

                            }
                            /*---------------------upload data preparation ends------------------*/
                            /*-----------------direct upload code starts-----------------------------*/
                            if (!empty($result) && count($result) > 0) {
                                $uploaded_flag = false;
                                $responseArray = "";
                                /*
                                if ($merchant_id==14){
                                    echo "<pre>";
                                    print_r($result[$sku]);
                                    die("hello");
                                }
                                 */
                                $response = $jetHelper->CPutRequest('/merchant-skus/' . rawurlencode($sku), json_encode($result[$sku]));

                                $responseArray = json_decode($response, true);

                                unset($result);
                                if ($responseArray == "") {
                                    $responsePrice = "";
                                    $filenameOrig = "";
                                    //price log
                                    $filenameOrig = $path . '/Price.log';
                                    $fileOrig = "";
                                    $fileOrig = fopen($filenameOrig, 'a+');
                                    fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . json_encode($price[$sku]));
                                    //fclose($fileOrig);

                                    $responsePrice = $jetHelper->CPutRequest('/merchant-skus/' . rawurlencode($sku) . '/price', json_encode($price[$sku]));
                                    $responsePrice = json_decode($responsePrice, true);
                                    fwrite($fileOrig, PHP_EOL . "price response: " . $responsePrice . PHP_EOL);
                                    fclose($fileOrig);
                                    unset($node);
                                    unset($price);
                                    if ($responsePrice == "") {
                                        $errordisplay = "";
                                        $responseInventory = "";
                                        //inventory log
                                        $filenameOrig = $path . '/Inventory.log';
                                        $fileOrig = "";
                                        $fileOrig = fopen($filenameOrig, 'a+');
                                        fwrite($fileOrig, "\n" . date('d-m-Y H:i:s') . "\n" . json_encode($inventory[$sku]));
                                        //fclose($fileOrig);

                                        $response = $jetHelper->CPutRequest('/merchant-skus/' . rawurlencode($sku) . '/inventory', json_encode($inventory[$sku]));
                                        $responseInventory = json_decode($response, true);
                                        fwrite($fileOrig, PHP_EOL . "inventory response: " . $response . PHP_EOL);
                                        unset($node1);
                                        unset($inventory);
                                        fclose($fileOrig);
                                        if (isset($responseInventory['errors'])) {
                                            $message = "";
                                            $message .= "<b>There are following information are incomplete/wrong for given product:</b><ul>";
                                            $message .= "<li><span class='required_label'>Product with sku :<b>" . $sku . "</b> not uploaded due to error in Inventory information.</span>
                                    <ul>
                                        <li><span class='required_values'>Error from Jet : " . json_encode($responseInventory['errors']) . " </span></li>
                                    </ul>
                                </li>";
                                            $message .= "</ul>";
                                            $return_msg['error'] = $message;
                                            /*$apistatusmessage[$sku]['Product with'] = $return_msg['error'];*/
                                            $apistatusmessage[$sku]['Product with sku'] = $sku . " not uploaded due to error in Inventory information";
                                            $apistatusmessage[$sku]['Error from Jet'] = json_encode($responseInventory['errors']);
                                            $apistatusmessage[$sku]['success'] = 'false';
                                                $apistatusmessage[$sku]['message'] = 'product not uploaded';


                                            $sql = "UPDATE `jet_product` SET  error='" . json_encode($responseInventory['errors']) . "' where id='" . $pid . "'";
                                            $model = $connection->createCommand($sql)->execute();
                                            //$product->error=json_encode($responseInventory['errors']);
                                            //$product->save(false);
                                            /*unset($product);
                                            return json_encode($return_msg);*/
                                        }
                                        $uploaded_flag = true;
                                    } elseif (isset($responsePrice['errors'])) {
                                        $errordisplay = "";
                                        $message = "";
                                        $message .= "<b>There are following information that are incomplete/wrong for given product:</b><ul>";
                                        $message .= "<li><span class='required_label'>Product with sku :<b>" . $sku . "</b> not uploaded due to error in Price information.</span>
                                    <ul>
                                        <li><span class='required_values'>Error from Jet : " . json_encode($responsePrice['errors']) . " </span></li>
                                    </ul>
                                </li>";
                                        $message .= "</ul>";
                                        $return_msg['error'] = $message;
                                     //   $apistatusmessage[$sku] = $return_msg['error'];
                                         $apistatusmessage[$sku]['Product with sku'] = $sku . " not uploaded due to error in Price information";
                                            $apistatusmessage[$sku]['Error from Jet'] = json_encode($responsePrice['errors']);
                                            $apistatusmessage[$sku]['success'] = 'false';
                                                $apistatusmessage[$sku]['message'] = 'product not uploaded';
                                        $sql = "UPDATE `jet_product` SET  error='" . json_encode($responsePrice['errors']) . "' where id='" . $pid . "'";
                                        $model = $connection->createCommand($sql)->execute();
                                        //$product->error=json_encode($responsePrice['errors']);
                                        //$product->save(false);
                                        /*unset($responsePrice);
                                        unset($product);
                                        return json_encode($return_msg);*/
                                    }
                                } elseif (isset($responseArray['errors'])) {
                                    $errordisplay = "";
                                    $message = "";
                                    $message .= "<b>There are following information that are incomplete/wrong for given product:</b><ul>";
                                    $message .= "<li><span class='required_label'>Product with sku :<b>" . $sku . "</b> not uploaded due to error in information.</span>
                                    <ul>
                                        <li><span class='required_values'>Error from Jet : " . json_encode($responseArray['errors']) . " </span></li>
                                    </ul>
                                </li>";
                                    $message .= "</ul>";
                                    $return_msg['error'] = $message;
                                   // $apistatusmessage[$sku] = $return_msg['error'];
                                    $apistatusmessage[$sku]['Product with sku'] = $sku . " not uploaded due to error in information";
                                     $apistatusmessage[$sku]['Error from Jet'] = json_encode($responseArray['errors']);
                                     $apistatusmessage[$sku]['success'] = 'false';
                                                $apistatusmessage[$sku]['message'] = 'product not uploaded';
                                    $sql = "UPDATE `jet_product` SET  error='" . json_encode($responseArray['errors']) . "' where id='" . $pid . "'";
                                    $model = $connection->createCommand($sql)->execute();
                                    //$product->error=json_encode($responseArray['errors']);
                                    //$product->save(false);
                                   /* unset($product);
                                    return json_encode($return_msg);*/
                                }
                                if ($uploaded_flag) {
                                    $uploadErrors = array();
                                    $result = "";
                                    $response = "";
                                    $uploadCount = 0;
                                    $result = $jetHelper->CGetRequest('/merchant-skus/' . rawurlencode($sku));
                                    $response = json_decode($result, true);
                                    if ($response && !(isset($response['errors']))) {
                                        $uploadCount++;
                                        //$product->status=$response['status'];
                                        //$product->error="";
                                        //$product->save(false);
                                        $sql = "UPDATE `jet_product` SET  error='',status='Under Jet Review' where id='" . $pid . "'";
                                        $model = $connection->createCommand($sql)->execute();
                                        $return_msg['success'] =  $sku . " successfully uploaded.";
                                        $apistatusmessage[$sku]['Product with sku'] = $return_msg['success'];
                                        $apistatusmessage[$sku]['success'] = 'true';
                                                $apistatusmessage[$sku]['message'] = 'product successfully uploaded';
                                               
                                       /* unset($product);
                                        return json_encode($return_msg);*/
                                    } elseif ($response != "" && isset($response['errors'])) {
                                        $message = "";
                                        $message .= "<b>There are following information that are incomplete/wrong for given product:</b><ul>";
                                        $message .= "<li><span class='required_label'>Product with sku :<b>" . $sku . "</b></span>
                                        <ul>
                                            <li><span class='required_values'>Error from Jet : " . json_encode($response['errors']) . " </span></li>
                                        </ul>
                                    </li>";
                                        $message .= "</ul>";
                                        $return_msg['error'] = $message;
                                        $apistatusmessage[$sku]['Product with sku'] = $sku ;
                                        $apistatusmessage[$sku]['Error from Jet'] = json_encode($response['errors']);
                                        $apistatusmessage[$sku]['success'] = 'false';
                                                $apistatusmessage[$sku]['message'] = 'product not uploaded';
                                     //   $apistatusmessage[$sku] = $return_msg['error'];
                                       /* unset($product);
                                        return json_encode($return_msg);*/
                                    }

                                }
                            }

                            /*-----------------direct upload code ends-----------------------------*/
                            $connection->close();
                        }


                    } catch (Exception $e) {
                        $return_msg = ['error' => true, 'message' => $e->getMessage()];
                        return json_encode($return_msg);
                    }
                } else {
                    $return_msg[] = ['status' => false, 'message' => 'Upload only 50 products at a time'];
                    return BaseJson::encode($return_msg);
                }
            }

            unset($responseArray['errors']);
        }

        return $apistatusmessage;

    }

    public static function priceChange($price, $priceType, $changePrice)
    {
        $updatePrice = 0;
        if ($priceType == "percentageAmount")
            $updatePrice = (float)($price + ($changePrice / 100) * ($price));
        elseif ($priceType == "fixedAmount")
            $updatePrice = (float)($price + $changePrice);
        return $updatePrice;
    }

}
