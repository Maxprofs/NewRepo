<?php
namespace frontend\modules\walmart\components;

use Yii;
use yii\base\Component;
use frontend\modules\walmart\components\Jetappdetails;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\controllers\WalmartscriptController;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartProduct as WalmartProductModel;

class Jetproductinfo extends component
{
    public static function checkRemoteFile($url)
    {
        $headers = get_headers($url);
        if (substr($headers[0], 9, 3) == '200') {
            return true;
        } else {
            return false;
        }
    }

    public static function productUpdateData($result, $data, $jetHelper, $fullfillmentnodeid, $merchant_id, $file, $customPrice = "", $connection)
    {
        //$connection=Yii::$app->getDb();
        $result1_rows = array();
        $updateInfo = array();
        $variants_ids = array();
        $new_variants_ids = array();
        $availble_variants = array();
        $archiveSkus = array();
        $updateProduct = array();
        $value = $data;
        //change custom price
        /*
        if($customPrice){
            $priceType="";
            $changePrice=0;
            $customPricearr=array();
            $customPricearr = explode('-',$customPrice);
            $priceType = $customPricearr[0];
            $changePrice = $customPricearr[1];
            unset($customPricearr);
        } */
        $product_id = $value['id'];
        $product_title = $value['title'];
        $vendor = $value['vendor'];
        $brand = $value['vendor'];
        $product_type = $value['product_type'];
        $product_des = $value['body_html'];

        // for checking product description is UTF-8 encoded.
        if (mb_detect_encoding($product_des) !== 'UTF-8') {
            $product_des = utf8_encode($product_des);
        }

        $product_des = strip_tags($product_des);

        $variants = $value['variants'];
        $images = $value['images'];
        $product_price = $value['variants'][0]['price'];
        /* if($priceType && $changePrice!=0){
            $updatePrice=0;
            $updatePrice=self::priceChange($product_price,$priceType,$changePrice);
            if($updatePrice!=0)
                $product_price = $updatePrice;
        } */
        $barcode = $value['variants'][0]['barcode'];
        $weight = 0;
        $unit = "";
        $weight = $value['variants'][0]['weight'];
        $unit = $value['variants'][0]['weight_unit'];
        $message = "";
        $message .= "\nProduct_id: " . $product_id . "\n";

        if ($weight > 0) {
            $weight = (float)Jetappdetails::convertWeight($weight, $unit);
        }
        $imagArr = array();
        $product_images = "";
        $variantArr = array();
        $simpleflag = false;
        $OldImages = array();
        $imageChange = false;
        $OldImages = explode(',', $result->image);
        if (is_array($images)) {
            foreach ($images as $valImg) {
                if (!in_array($valImg['src'], $OldImages)) {
                    $imageChange = true;
                }
                $imagArr[] = $valImg['src'];
            }
            $product_images = implode(',', $imagArr);
        }
        /* if($product_id==4211751366){
            var_dump($imageChange);
        echo "<br>".$product_images;} */
        unset($OldImages);
        $product_sku = "";
        $product_sku = $value['variants'][0]['sku'];
        $product_qty = $value['variants'][0]['inventory_quantity'];
        $variant_id = $value['variants'][0]['id'];
        if (trim($product_sku) == "") {
            return;
        }
        if (count($variants) == 1) {
            $simpleflag = true;
        }
        if (count($variants) > 1) {
            $options = $value['options'];
            $attrId = array();
            $attrValue = array();
            $attFlag = false;
            $attrValue = json_decode($result->attr_ids, true);
            foreach ($options as $key => $val) {
                $attrname = $val['name'];
                if (is_array($attrValue) && !in_array($attrname, $attrValue)) {
                    $attFlag = true;
                }
                $attrId[$val['id']] = $val['name'];
                foreach ($val['values'] as $k => $v) {
                    $option_value[$attrname][$k] = $v;
                }
            }
            if ($attFlag) {
                $message .= "wrong attr\n";
                //update product option label
                $updateProduct['attr_ids'] = json_encode($attrId);
                //$result->attr_ids=json_encode($attrId);
                //function to delete/archive product variants create new and update attr_id on parent product
                //$message.=Jetproductinfo::addNewVariants($product_id,$product_sku,$data,$jetHelper,$merchant_id,$connection);
                $message .= Jetproductinfo::addNewVariants($data, $product_id, $merchant_id);
                return;
            }
            $changeParentTitle = false;
            $changeParentDes = false;
            $changeParentCat = false;
            foreach ($variants as $value1) {
                $option_sku = "";
                $option_title = "";
                $option_image_id = "";
                $option_price = "";
                $option_qty = "";
                $option_barcode = "";
                $option_variant1 = "";
                $option_variant2 = "";
                $option_variant3 = "";
                $flagChange = false;
                $flagskuChange = false;
                $vskuChangeData = array();
                $option_weight = 0;
                $option_unit = "";
                $option_weight = $value1['weight'];
                $option_unit = $value1['weight_unit'];
                if ($option_weight > 0) {
                    $option_weight = (float)Jetappdetails::convertWeight($option_weight, $option_unit);
                }
                $variantArr[] = $value1['id'];
                $option_id = $value1['id'];
                $variants_ids[] = trim($option_id);
                $option_title = $value1['title'];
                $option_sku = $value1['sku'];
                $option_image_id = $value1['image_id'];
                $option_price = $value1['price'];
                /* if($priceType && $changePrice!=0){
                    $updatePrice=0;
                    $updatePrice=self::priceChange($option_price,$priceType,$changePrice);
                    if($updatePrice!=0)
                        $option_price = $updatePrice;
                } */
                $option_qty = $value1['inventory_quantity'];
                $option_variant1 = $value1['option1'];
                $option_variant2 = $value1['option2'];
                $option_variant3 = $value1['option3'];
                $option_barcode = $value1['barcode'];
                $option_image_url = '';
                $vresult = "";
                $vupdateProduct = array();
                $imageFlag = false;
                $vresult = (object)$connection->createCommand('SELECT option_id,option_title,option_sku,jet_option_attributes,option_image,option_qty,option_weight,option_price,option_unique_id,variant_option1,variant_option2,variant_option3,vendor from `jet_product_variants` where option_id="' . $option_id . '"')->queryOne();
                if (is_array($images)) {
                    foreach ($images as $value2) {
                        if ($value2['id'] == $option_image_id) {
                            $option_image_url = $value2['src'];
                            $imageFlag = true;
                            break;
                        }
                    }
                }
                if (is_object($vresult) && isset($vresult->option_id)) {
                    if ($result->type == "simple") {
                        $updateProduct['type'] = "variants";
                        $updateProduct['attr_ids'] = json_encode($attrId);
                        //$result->type="variants";
                    }
                    if ($option_sku != "" && $vresult->option_sku != $option_sku) {
                        if ($result->sku == $vresult->option_sku || $result->variant_id == $vresult->option_id) {
                            $message .= "add new sku:-" . $product_sku . "\n";
                            //delete product as well as all variants and add new product and archive and upload product with new relation
                            $message .= Jetproductinfo::addNewVariants($data, $product_id, $merchant_id);
                            return;
                        } else {
                            //archive variant option and add new variantion with updated children skus
                            $message .= "update variant sku:-" . $option_sku . "\n";
                            $archiveSkus[] = $vresult->option_sku;
                            $flagskuChange = true;
                            //$vresult->option_sku=$option_sku;
                            $vupdateProduct['option_sku'] = $option_sku;
                        }
                    }
                    if ($option_title != "" && $vresult->option_title != $option_title) {
                        //$vresult->option_title=$option_title;
                        $vupdateProduct['option_title'] = $option_title;
                        if ($option_sku != $product_sku) {
                            $message .= "update child var title :-" . $option_sku . "\n";
                            $flagChange = true;
                            $vskuChangeData['title'] = $product_title . '-' . $option_title;
                        }
                    }
                    if ($product_title != "" && $product_title != $result->title) {
                        $changeParentTitle = true;
                        $updateProduct['title'] = $product_title;
                        //$result->title=$product_title;
                    }
                    if ($changeParentTitle) {
                        $message .= "update parent var title :-" . $option_sku . "\n";
                        $flagChange = true;
                        if ($option_sku == $product_sku) {
                            $vskuChangeData['title'] = $product_title;
                        } else {
                            $vskuChangeData['title'] = $product_title . '-' . $option_title;
                        }
                    }
                    $result->description = strip_tags($result->description);
                    if ($result->description != $product_des) {
                        $changeParentDes = true;
                        $updateProduct['description'] = $product_des;
                        //$result->description=$product_des;
                    }
                    if ($changeParentDes) {
                        $flagChange = true;
                        $message .= "change sku var des:-" . $product_sku . "\n";
                        $vskuChangeData['description'] = $product_des;
                    }
                    if ($result->product_type != $product_type && !$changeParentCat) {
                        //$result->product_type = $product_type;
                        $updateProduct['product_type'] = $product_type;
                        $modelmap = array();
                        $modelmap = $connection->createCommand('SELECT category_id from `jet_category_map` where merchant_id="' . $merchant_id . '" and product_type="' . $product_type . '"')->queryOne();
                        //$modelmap = JetCategoryMap::find()->where(['merchant_id'=>$merchant_id,'product_type'=>$product_type])->one();
                        if (is_array($modelmap) && count($modelmap) > 0) {
                            if ($modelmap['category_id'] != $result->fulfillment_node) {
                                $message .= "change product category in\n";
                                $updateProduct['fulfillment_node'] = $modelmap['category_id'];
                                //$result->fulfillment_node = $modelmap['category_id'];
                                $changeParentCat = true;
                            }
                        } else {
                            //insert new product-type
                            $sql = 'INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . addslashes($product_type) . '")';
                            $connection->createCommand($sql)->execute();
                        }
                        unset($modelmap);
                    }
                    if ($changeParentCat) {
                        $flagChange = true;
                        $message .= "change variant category data\n" . $product_sku . "\n";
                        $vskuChangeData['category'] = $modelmap['category_id'];
                    }
                    if ($option_barcode != "" && $vresult->option_unique_id != $option_barcode) {
                        $message .= "update option barcode :-" . $option_sku . "\n";
                        $flagChange = true;
                        $vskuChangeData['barcode'] = $option_barcode;
                        $vskuChangeData['barcode_as_parent'] = 0;
                        //$vresult->option_unique_id=$option_barcode;
                        $vupdateProduct['option_unique_id'] = $option_barcode;
                        if ($option_sku == $product_sku) {
                            $updateProduct['upc'] = $option_barcode;
                            //$result->upc=$option_barcode;
                            $vskuChangeData['barcode_as_parent'] = 1;
                        }
                    }
                    if ($option_image_url != "" && $imageFlag == true && $vresult->option_image != $option_image_url) {
                        $message .= "update option image :-" . $option_sku . "\n";
                        //$vresult->option_image=$option_image_url;
                        $vupdateProduct['option_image'] = $option_image_url;
                        if ($option_sku == $product_sku) {
                            $updateProduct['image'] = $product_images;
                            //$result->image=$product_images;
                        }
                        $message .= Jetproductinfo::UpdateImageOnJet($option_sku, $product_images, $option_image_url, $jetHelper, $merhcant_id);
                    } elseif (($option_image_url == "" || $vresult->option_image == $option_image_url) && $imageChange) {
                        if ($option_sku == $product_sku) {
                            $updateProduct['image'] = $product_images;
                            //$result->image=$product_images;
                        }
                        $message .= Jetproductinfo::UpdateImageOnJet($option_sku, $product_images, $option_image_url, $jetHelper, $merhcant_id);
                    }
                    if ($vresult->option_qty != $option_qty && $flagskuChange == false) {
                        $message .= "update option qty :-" . $option_sku . "\n";
                        //$vresult->option_qty=$option_qty;
                        $vupdateProduct['option_qty'] = $option_qty;
                        if ($option_sku == $product_sku) {
                            $updateProduct['qty'] = $option_qty;
                            //$result->qty=$option_qty;
                        }
                        if ($option_qty > 0) {
                            $message .= Jetproductinfo::updateQtyOnJet($option_sku, $option_qty, $jetHelper, $fullfillmentnodeid, $merchant_id);
                        }
                        //add function to change qty on jet.com
                    }
                    if ($vresult->vendor != $vendor) {
                        $message .= "update option vendor :-" . $option_sku . "\n";
                        $flagChange = true;
                        $vskuChangeData['vendor'] = $vendor;
                        if ($option_sku == $product_sku) {
                            $updateProduct['vendor'] = $vendor;
                            //$result->vendor=$vendor;
                        }
                        //$vresult->vendor=$vendor;
                        $vupdateProduct['vendor'] = $vendor;
                    }
                    if (!$customPrice && $vresult->option_price != $option_price && $flagskuChange == false) {
                        $message .= "update option price :-" . $option_sku . "\n";
                        //$vresult->option_price=(float)$option_price;
                        $vupdateProduct['option_price'] = (float)$option_price;
                        if ($option_sku == $product_sku) {
                            $updateProduct['price'] = (float)$option_price;
                            //$result->price=(float)$option_price;
                        }
                        //add function to change price on jet.com
                        $message .= Jetproductinfo::updatePriceOnJet($option_sku, (float)$option_price, $jetHelper, $fullfillmentnodeid, $merchant_id);
                    }

                    if ($vresult->option_weight != round($option_weight, 2) && $flagskuChange == false) {
                        $message .= "update option weight :-" . $option_sku . "\n";
                        $flagChange = true;
                        $vskuChangeData['weight'] = $option_weight;
                        //$vresult->option_weight=$option_weight;
                        $vupdateProduct['option_weight'] = $option_weight;
                        if ($option_sku == $product_sku) {
                            $updateProduct['weight'] = $option_weight;
                            //$result->weight=$option_weight;
                        }
                    }
                    if ($option_variant1 != "" && $vresult->variant_option1 != $option_variant1) {
                        $message .= "update option variant1 in :-" . $option_sku . "\n";
                        $attributes = array();
                        $flagChange = true;
                        if ($vresult->jet_option_attributes) {
                            $attributes = json_decode($vresult->jet_option_attributes, true);
                            if (count($attributes) > 1) {
                                foreach ($attributes as $key => $attr_val) {
                                    if (in_array($vresult->variant_option1, $attributes)) {
                                        $attr_val = $option_variant1;
                                        $message .= "update option variant1 change:-" . $option_sku . "\n";
                                        $vskuChangeData['variant_option'][$key] = $option_variant1;
                                        break;
                                    }
                                }
                            }
                            //$vupdateProduct['jet_option_attributes']=json_encode($attributes);
                            //$vresult->jet_option_attributes=json_encode($attributes);
                        }
                        $vupdateProduct['variant_option1'] = $option_variant1;
                        //$vresult->variant_option1=$option_variant1;
                    }
                    if ($option_variant2 != "" && $vresult->variant_option2 != $option_variant2) {
                        $message .= "update option variant2 :-" . $option_sku . "\n";
                        $attributes = array();
                        $flagChange = true;
                        if ($vresult->jet_option_attributes) {
                            $attributes = json_decode($vresult->jet_option_attributes, true);
                            /* if ($merchant_id==7){
                                echo "<pre>";
                                print_r($attributes);;
                                die;
                            } */
                            if (count($attributes) > 1) {
                                foreach ($attributes as $key => $attr_val) {
                                    if (in_array($vresult->variant_option2, $attributes)) {
                                        $attr_val = $option_variant2;
                                        $vskuChangeData['variant_option'][$key] = $option_variant2;
                                        break;
                                    }
                                }
                            }
                            //$vupdateProduct['jet_option_attributes']=json_encode($attributes);
                            //$vupdateProduct['jet_option_attributes']=json_encode($attributes);
                        }
                        $vupdateProduct['variant_option2'] = $option_variant2;
                        //$vresult->variant_option2=$option_variant2;
                    }
                    if ($option_variant3 != "" && $vresult->variant_option3 != $option_variant3) {
                        $message .= "update option variant3 :-" . $option_sku . "\n";
                        $attributes = array();
                        $flagChange = true;
                        if ($vresult->jet_option_attributes) {
                            $attributes = json_decode($vresult->jet_option_attributes, true);
                            foreach ($attributes as $key => $attr_val) {
                                if ($vresult->variant_option3 == $attr_val) {
                                    $attributes[$key] = $option_variant3;
                                    $vskuChangeData['variant_option'][$key] = $option_variant3;
                                    break;
                                }
                            }
                            //$vupdateProduct['jet_option_attributes']=json_encode($attributes);
                            //$vresult->jet_option_attributes=json_encode($attributes);
                        }
                        $vupdateProduct['variant_option3'] = $option_variant3;
                        //$vresult->variant_option3=$option_variant3;
                    }
                    if ($flagChange == true && $flagskuChange == false) {
                        $message .= "change sku variant data request:-" . $option_sku . "\n";
                        $message .= Jetproductinfo::updateSkudataOnJet($option_sku, $product_id, $option_id, $vskuChangeData, "variants", $jetHelper, $merchant_id);
                        //var_Dump($updateInfo);die;
                    }
                    if (is_array($vupdateProduct) && count($vupdateProduct) > 0) {
                        $i = count($vupdateProduct);
                        $j = 1;
                        $query = 'UPDATE `jet_product_variants` SET ';
                        foreach ($vupdateProduct as $key => $val) {
                            if ($i == $j)
                                $query .= '`' . $key . '`="' . addslashes($val) . '"';
                            else
                                $query .= '`' . $key . '`="' . addslashes($val) . '",';
                            $j++;
                        }
                        $query .= ' where option_id="' . $option_id . '"';
                        //echo $query;die("chala");
                        $connection->createCommand($query)->execute();
                        unset($j);
                        unset($i);
                    }
                    //$vresult->save(false);
                } else {
                    $sql = 'INSERT INTO `jet_product_variants`(
                                `option_id`,`product_id`,
                                `merchant_id`,`option_title`,
                                `option_sku`,`option_image`,
                                `option_price`,`option_qty`,
                                `variant_option1`,`variant_option2`,
                                `variant_option3`,`vendor`,
                                `option_unique_id`,`option_weight`
                            )VALUES(
                                "' . $option_id . '","' . $product_id . '",
                                "' . $merchant_id . '","' . addslashes($option_title) . '",
                                "' . $option_sku . '","' . addslashes($option_image_url) . '",
                                "' . (float)$option_price . '","' . (int)$option_qty . '",
                                "' . addslashes($option_variant1) . '","' . addslashes($option_variant2) . '",
                                "' . addslashes($option_variant3) . '","' . addslashes($vendor) . '",
                                "' . $option_barcode . '","' . $option_weight . '"
                            )';
                    $connection->createCommand($sql)->execute();
                    //function to add new variants option and upload on jet.com as well as change variation
                }
            }
        }
        //delete variants if not exist in shopify
        $availble_variants = array();
        $vallresult = array();
        $vallresult = $connection->createCommand('SELECT `option_id` from `jet_product_variants` where product_id="' . $product_id . '"')->queryAll();
        //$vallresult=JetProductVariants::find()->where(['merchant_id'=>$merchant_id,'product_id'=>$product_id])->all();
        if (is_array($vallresult) && count($vallresult) > 0) {
            foreach ($vallresult as $res) {
                $availble_variants[] = trim($res['option_id']);
            }
        }
        unset($vallresult);
        $resulting_arr = array();
        $resulting_arr = array_diff($availble_variants, $variants_ids);
        unset($availble_variants);
        if (is_array($resulting_arr) && count($resulting_arr) > 0) {
            foreach ($resulting_arr as $val) {
                $delresult = array();
                //if deleted variant is parent
                $delresult = $connection->createCommand('SELECT `option_sku` from `jet_product_variants` where option_id="' . $val . '"')->queryOne();
                //$delresult=JetProductVariants::find()->select('option_sku')->where(['option_id'=>$val])->one();
                if (is_array($delresult) && count($delresult) > 0) {
                    //die("del child");
                    if ($delresult['option_sku'] == $result->sku) {
                        $message .= $delresult['option_sku'] . "----delgfdg child";
                        //delete all data from product as well variants and send new variantion
                        $message .= Jetproductinfo::addNewVariants($data, $product_id, $merchant_id);
                        return;
                    } else {
                        $message .= $delresult['option_sku'] . "---------deldfdfdfdf child";
                        $connection->createCommand('DELETE FROM `jet_product_variants` WHERE option_id="' . $val . '"')->execute();
                        //$delresult->delete();
                        //archive skus and change variantion
                        $archiveSkus[] = $result->sku;

                    }
                }
            }
        }
        unset($delresult);
        unset($resulting_arr);
        //change product information
        $skuChangeData = array();
        $flagSim = false;
        $flagSimImage = false;
        $flagSimPrice = false;
        $flagSimQty = false;
        $flagsimpleskuChange = false;
        if ($product_sku == "" && $result->sku != "" && $simpleflag == true) {
            //product not exist in shopify and archive on jet
            $message .= "change simple sku value is null:-" . $product_sku . "\n";
            $flagsimpleskuChange = true;
            $archiveSkus[] = $result->sku;
        }
        if ($product_sku != "" && $simpleflag == true && $result->sku != $product_sku) {
            //product exist but sku change for simple and upload new simple product
            $message .= "change simple sku value:-" . $product_sku . "\n";
            $flagsimpleskuChange = true;
            $archiveSkus[] = $result->sku;
            $updateProduct['sku'] = $product_sku;
            //$result->sku=$product_sku;
        }
        if ($product_title != "" && $product_title != $result->title && $simpleflag == true) {
            $message .= "change simple sku title:-" . $product_sku . "\n";
            $flagSim = true;
            $skuChangeData['title'] = $product_title;
            $updateProduct['title'] = $product_title;
            //$result->title=$product_title;
        }
        if ($result->vendor != $vendor && $simpleflag == true) {
            $message .= "change simple sku vendor:-" . $product_sku . "\n";
            $flagSim = true;
            $skuChangeData['brand'] = $vendor;
            $updateProduct['vendor'] = $vendor;
            //$result->vendor=$vendor;
            //$result->brand=$vendor;
        }
        $result->description = strip_tags($result->description);
        if ($result->description != $product_des && $simpleflag == true) {
            $message .= "change simple sku des:-" . $product_sku . "\n";
            $flagSim = true;
            $skuChangeData['description'] = $product_des;
            $updateProduct['description'] = $product_des;
            //$result->description=$product_des;
        }
        if ($result->weight != round($weight, 2) && $simpleflag == true) {
            $message .= "change simple sku wight:-" . $product_sku . "\n";
            $flagSim = true;
            $skuChangeData['weight'] = $weight;
            $updateProduct['weight'] = $weight;
            //$result->weight=$weight;
        }
        if ($result->upc != $barcode && $simpleflag == true) {
            $message .= "change simple sku upc:-" . $product_sku . "\n";
            $flagSim = true;
            $skuChangeData['barcode'] = $barcode;
            $updateProduct['upc'] = $barcode;
            //$result->upc=$barcode;
        }
        if ($result->product_type != $product_type && $simpleflag == true) {
            //$result->product_type = $product_type;
            $updateProduct['product_type'] = $product_type;
            $modelmap = "";
            $modelmap = array();
            $modelmap = $connection->createCommand('SELECT category_id from `jet_category_map` where merchant_id="' . $merchant_id . '" and product_type="' . $product_type . '"')->queryOne();
            //$modelmap = JetCategoryMap::find()->where(['merchant_id'=>$merchant_id,'product_type'=>$product_type])->one();
            if (is_array($modelmap) && count($modelmap) > 0) {
                if ($modelmap['category_id'] != $result->fulfillment_node) {
                    $message .= "change product category in\n";
                    $updateProduct['fulfillment_node'] = $modelmap['category_id'];
                    //$result->fulfillment_node = $modelmap['category_id'];
                    $changeParentCat = true;
                }
            } else {
                //insert new product-type
                $sql = 'INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . addslashes($product_type) . '")';
                $connection->createCommand($sql)->execute();
            }
            unset($modelmap);
        }
        if ($imageChange == true && $simpleflag == true) {
            $message .= "change sku simple image:-" . $product_sku . "\n";
            $updateProduct['image'] = $product_images;
            //$result->image=$product_images;
            $message .= Jetproductinfo::UpdateImageOnJet($product_sku, $product_images, $imagArr[0], $jetHelper, $merchant_id);
        }
        if (!$customPrice && $result->price != $product_price && $simpleflag == true && $flagsimpleskuChange == false) {
            //send price information
            $message .= "change simple sku price:-" . $product_sku . "\n";
            $updateProduct['price'] = $product_price;
            //$result->price=$product_price;
            $message .= Jetproductinfo::updatePriceOnJet($product_sku, $product_price, $jetHelper, $fullfillmentnodeid, $merchant_id);
        }
        if ($result->qty != $product_qty && $simpleflag == true && $flagsimpleskuChange == false) {//echo "hello";
            //send price information
            $message .= "change simple sku qty:-" . $product_sku . "\n";
            //$result->qty=$product_qty;
            $updateProduct['qty'] = $product_qty;
            if ($product_qty > 0)
                $message .= Jetproductinfo::updateQtyOnJet($product_sku, $product_qty, $jetHelper, $fullfillmentnodeid, $merchant_id);
        }

        if ($simpleflag == true && $result->type == "variants") {
            $updateProduct['type'] = "simple";
            $updateProduct['attr_ids'] = "";
            $updateProduct['jet_attributes'] = "";
            //$result->type="simple";
        }
        if ($simpleflag == true && $flagSim == true && $flagsimpleskuChange == false) {
            //update simple product sku information
            $message .= "update sku simple data request:-" . $product_sku . "\n";
            $message .= Jetproductinfo::updateSkudataOnJet($product_sku, $product_id, "", $skuChangeData, "simple", $jetHelper, $merchant_id);
        }
        //archive prouducts
        $message .= Jetproductinfo::archiveProductOnJet($archiveSkus, $jetHelper, $merchant_id);
        if (is_array($updateProduct) && count($updateProduct) > 0) {
            $i = count($updateProduct);
            $j = 1;
            $query = 'UPDATE `jet_product` SET ';
            foreach ($updateProduct as $k => $v) {
                if ($j == $i)
                    $query .= '`' . $k . '`="' . addslashes($v) . '"';
                else
                    $query .= '`' . $k . '`="' . addslashes($v) . '",';
                $j++;
            }
            $query .= ' where id="' . $product_id . '"';
            unset($j);
            unset($i);
            $connection->createCommand($query)->execute();
        }
        unset($archiveSkus);
        unset($skuChangeData);
        unset($updateProduct);
        //$result->save(false);
        fwrite($file, $message);
    }

    public static function saveNewRecords($data, $merchant_id, $connection = false, $import_option = null)
    {
        try {

            $insert_product = Data::sqlRecords("SELECT `total_variant_products` FROM `insert_product` WHERE `merchant_id`='" . $merchant_id . "'", 'one');
            $total_variants = $insert_product['total_variant_products'];

            if ($total_variants > Data::TOTAL_PRODUCT_LIMIT) {
                $response['error'] = 'You have ' . $total_variants . ' product(s) including variants which is beyond the limit';
                return $response;
            }

            $response = [];
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
                        $option_weight = (float)Jetappdetails::convertWeight($value['weight'], $value['weight_unit']);
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
                            if (!$proResult['id']) {

                                // for checking product description is UTF-8 encoded.
                                if (mb_detect_encoding($data['body_html']) !== 'UTF-8') {
                                    $data['body_html'] = utf8_encode($data['body_html']);
                                }

                                $response['success'] = true;
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
                                        "' . $type . '","' . addslashes($data['body_html']) . '",
                                        "' . addslashes($product_images) . '","' . (float)$val['price'] . '",
                                        "' . (int)$val['qty'] . '","' . addslashes($attr_id) . '",
                                        "' . $val['barcode'] . '","Not Uploaded",
                                        "' . addslashes($data['vendor']) . '","' . $key . '",
                                        "' . addslashes($data['product_type']) . '","' . $val['weight'] . '"
                                    )';
                                Data::sqlRecords($sql, null, 'insert');
                                $new_product_flag = true;
                            }
                            $proDetailsResult = Data::sqlRecords("SELECT `id` FROM `jet_product_details` WHERE product_id='" . $product_id . "' LIMIT 0,1", "one", "select");
                            if (!isset($proDetailsResult['id'])) {
                                $sql = 'INSERT INTO `jet_product_details`
                                    (
                                        `product_id`, 
                                        `merchant_id`
                                    )
                                    VALUES
                                    (
                                        "' . $product_id . '",
                                        "' . $merchant_id . '"
                                    )';
                                Data::sqlRecords($sql, null, "insert");
                            }
                            //save in `walmart_product` table
                            $walresult = Data::sqlRecords("SELECT `product_id` FROM `walmart_product` WHERE product_id='" . $product_id . "' LIMIT 0,1", "one", "select");
                            if (!$walresult) {

                                $sql = "INSERT INTO `walmart_product` (`product_id`,`merchant_id`,`status`,`product_type`) VALUES ('" . $product_id . "','" . $merchant_id . "','" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "','" . addslashes($data['product_type']) . "')";

                                Data::sqlRecords($sql);
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
                                    "' . addslashes($val['variant_option1']) . '","' . $val['variant_option2'] . '",
                                    "' . $val['variant_option3'] . '","' . addslashes($data['vendor']) . '",
                                    "' . $val['barcode'] . '","' . $val['weight'] . '","Not Uploaded"
                                )';
                                Data::sqlRecords($sql);

                            }
                            //Insert Data Into `walmart_product_variants`
                            $walresult = Data::sqlRecords("SELECT `option_id` FROM `walmart_product_variants` WHERE option_id='" . $key . "' LIMIT 0,1", "one", "select");
                            if (!$walresult) {
                                $sql = "INSERT INTO `walmart_product_variants`(
                                        `option_id`,`product_id`,`merchant_id`,`status`,`new_variant_option_1`,`new_variant_option_2`,`new_variant_option_3`
                                        )

                                        VALUES('" . $key . "','" . $product_id . "','" . $merchant_id . "','" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "','" . addslashes($val['variant_option1']) . "','" . addslashes($val['variant_option2']) . "','" . addslashes($val['variant_option3']) . "')";
                                Data::sqlRecords($sql);
                            }
                        }
                    }

                    if (isset($data['product_type']) && $data['product_type']) {
                        //add product type in jet
                        $modelmap = "";
                        $query = "";
                        $queryObj = "";
                        $query = 'SELECT category_id FROM `jet_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . $data['product_type'] . '" LIMIT 0,1';
                        $modelmap = Data::sqlRecords($query, "one", "select");

                        if (isset($modelmap['category_id'])) {
                            $updateResult = "";
                            $query = 'UPDATE `jet_product` SET fulfillment_node="' . $modelmap['category_id'] . '" where id="' . $data['id'] . '"';
                            Data::sqlRecords($query, null, 'update');
                        } else {
                            $queryObj = "";
                            $query = 'INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . $data['product_type'] . '")';
                            Data::sqlRecords($query);
                        }

                        //add product type in walmart
                        $query = 'SELECT category_id FROM `walmart_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($data['product_type']) . '" LIMIT 0,1';
                        $walmodelmap = Data::sqlRecords($query, "one", "select");

                        if ($walmodelmap) {
                            if (isset($walmodelmap['category_id'])) {
                                //walmart new product
                                $updateResult = "";
                                $query = 'UPDATE `walmart_product` SET category="' . $walmodelmap['category_id'] . '" where product_id="' . $product_id . '"';
                                Data::sqlRecords($query);
                            }

                        } else {
                            //walmart category map
                            $queryObj = "";
                            $query = 'INSERT INTO `walmart_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . addslashes($data['product_type']) . '")';
                            Data::sqlRecords($query);
                        }
                    }

                    //send data to walmart
                    //$url = Yii::getAlias('@webwalmarturl')."/webhookupdate/product-create?debug";
                    //self::sendCurlRequest(['product_id'=>$product_id,'data'=>$variantData,'merchant_id'=>$merchant_id],$url);
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
            if (trim($option_attributes['optionsku']) != $variant_sku) {
                if ($variant_upc == trim($option_attributes['upc'])/* && trim($option_attributes['barcode_type'])==trim($barcode_type)*/) {
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
            $matched_flag = $db_matched_flag = self::checkUpcVariants($variant_upc, $product_id, $variant_id, $variant_as_parent, $connection);
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

    public static function checkUpcSimple($product_upc = "", $product_id = "", $connection = null)
    {
        try {
            if (is_null($connection)) {
                $connection = Yii::$app->getDb();
            }
            $merchant_id = Yii::$app->user->identity->id;
            $product_upc = trim($product_upc);
            $main_product_count = 0;
            $main_products = array();
            $variant = array();
            $variant_count = 0;
            $queryObj = "";
            $query = "SELECT `id` FROM `jet_product` WHERE upc='" . $product_upc . "' AND id <> '" . $product_id . "' AND `merchant_id`=" . $merchant_id;
            $queryObj = $connection->createCommand($query);
            $main_products = $queryObj->queryAll();
            $main_product_count = count($main_products);
            unset($main_products);
            $queryObj = "";
            $query = "SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='" . $product_upc . "' AND `merchant_id`=" . $merchant_id;
            $queryObj = $connection->createCommand($query);
            $variant = $queryObj->queryAll();
            $variant_count = count($variant);
            unset($variant);
            if ($main_product_count > 0 || $variant_count > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    public static function checkUpcVariantSimple($product_upc = "", $product_id = "", $product_sku = "", $connection = null)
    {
        try {
            if (is_null($connection)) {
                $connection = Yii::$app->getDb();
            }
            $merchant_id = Yii::$app->user->identity->id;
            $connection = Yii::$app->getDb();
            $product_upc = trim($product_upc);
            $main_product_count = 0;
            $main_products = array();
            $variant = array();
            $variant_count = 0;
            $queryObj = "";
            $query = "SELECT `id` FROM `jet_product` WHERE `upc`='" . $product_upc . "' AND `id`<>'" . $product_id . "' AND `merchant_id`=" . $merchant_id;
            $queryObj = $connection->createCommand($query);
            $main_products = $queryObj->queryAll();
            $main_product_count = count($main_products);
            unset($main_products);
            $queryObj = "";
            $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_sku <> '" . $product_sku . "' AND option_unique_id='" . $product_upc . "' AND `merchant_id`=" . $merchant_id);
            $variant = $queryObj->queryAll();
            $variant_count = count($variant);
            unset($variant);
            if ($main_product_count > 0 || $variant_count > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    public static function checkUpcVariants($product_upc = "", $product_id = "", $variant_id = "", $variant_as_parent = 0, $connection = null)
    {
        try {
            if (is_null($connection)) {
                $connection = Yii::$app->getDb();
            }
            $merchant_id = Yii::$app->user->identity->id;
            $variant_count = 0;
            $main_product_count = 0;
            $main_products = array();
            $variant = array();
            if ($variant_as_parent) {
                $queryObj = "";
                $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE upc='" . trim($product_upc) . "' AND id <> '" . $product_id . "' AND `merchant_id`=" . $merchant_id);
                $main_products = $queryObj->queryAll();
            } else {
                $queryObj = "";
                $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE upc='" . trim($product_upc) . "' AND `merchant_id`=" . $merchant_id);
                $main_products = $queryObj->queryAll();
            }
            $main_product_count = count($main_products);
            unset($main_products);
            $queryObj = "";
            $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='" . trim($product_upc) . "' and option_id <>'" . $variant_id . "' AND `merchant_id`=" . $merchant_id);
            $variant = $queryObj->queryAll();
            $variant_count = count($variant);
            unset($variant);
            if ($main_product_count > 0 || $variant_count > 0) {
                //$msg['success']=true;
                return true;
            }
            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    /*public static function priceChange($price,$priceType,$changePrice){
        $updatePrice=0;
        if($priceType=="increase")
            $updatePrice=(float)($price+($changePrice/100)*($price));
        elseif($priceType=="decrease")
            $updatePrice=(float)($price-($changePrice/100)*($price));
        return $updatePrice;
    }*/

    public static function priceChange($price, $priceType, $changePrice, $priceValueType = "increase")
    {

        $updatePrice = 0;
        if ($priceValueType == "increase") {

            if ($priceType == "percent")
                $updatePrice = (float)($price + ($changePrice / 100) * ($price));
            elseif ($priceType == "fixed")
                $updatePrice = (float)($price + $changePrice);

        } else {

            if ($priceType == "percent")
                $updatePrice = (float)($price - ($changePrice / 100) * ($price));
            elseif ($priceType == "fixed")
                $updatePrice = (float)($price - $changePrice);

        }

        return $updatePrice;
    }

    public static function validateSku($sku, $productId, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = Yii::$app->user->identity->id;
        $sku = addslashes($sku);
        /*$query = "SELECT `jp`.`id`,`jpv`.`option_id` FROM `jet_product` `jp` LEFT JOIN `jet_product_variants` `jpv` ON `jp`.`id`=`jpv`.`product_id` WHERE `jp`.`merchant_id`='{$merchant_id}' AND (`jp`.`sku`='{$sku}' OR `jpv`.`option_sku`='{$sku}')";*/
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

    public static function validateSkuForExistingProduct($sku, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = Yii::$app->user->identity->id;

        /*$query = "SELECT `jp`.`id`,`jpv`.`option_id` FROM (SELECT * FROM `jet_product` WHERE `merchant_id`= '".$merchant_id."') as `jp` LEFT JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`= '".$merchant_id."') as `jpv` ON `jp`.`id`=`jpv`.`product_id` WHERE `jp`.`merchant_id`='{$merchant_id}' AND (`jp`.`sku`='{$sku}' OR `jpv`.`option_sku`='{$sku}') LIMIT 0,1";*/

        $query = "SELECT `result`.* FROM (SELECT `id`, `variant_id` AS `option_id` FROM `jet_product` WHERE `merchant_id`='{$merchant_id}' AND `sku`='{$sku}' UNION SELECT `product_id` AS `id` , `option_id` FROM `jet_product_variants` WHERE `merchant_id`='{$merchant_id}' AND `option_sku`='{$sku}') as `result`";
        $result = Data::sqlRecords($query, 'one', 'select');

        if ($result)
            return $result;
        else
            return true;
    }

    /**
     * Change the status of Product Variants to 'Item Processing'
     * When feed send to walmart
     *
     * @param $optionId
     * @return void
     */
    public static function chnageUploadingProductStatus($optionId)
    {
        /*$query = 'UPDATE `walmart_product` `wp` INNER JOIN `walmart_product_variants` `wpv` on `wp`.`product_id`=`wpv`.`product_id` SET `wp`';*/
        $status = WalmartProductModel::PRODUCT_STATUS_PROCESSING;
        $query = "UPDATE `walmart_product_variants` `wpv` SET `wpv`.`status`='{$status}' WHERE `wpv`.`option_id`='{$optionId}'";
        Data::sqlRecords($query, null, 'update');
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


        $query = "SELECT `merged_data`.* FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple' AND `jet_product`.upc='{$barcode}') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants`  INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=" . $merchant_id . " AND `jet_product_variants`.option_unique_id='{$barcode}')) as `merged_data`";

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

    public static function insertImportErrorProduct($id, $title, $type, $merchant_id)
    {
        $checkExistProduct = Data::sqlRecords("SELECT `id` FROM `product_import_error` WHERE id='" . $id . "' LIMIT 0,1", "one", "select");
        if (!$checkExistProduct) {
            $query = "INSERT INTO `product_import_error`(`id`, `merchant_id`, `missing_value`, `title`) VALUES ('" . $id . "','" . $merchant_id . "','" . $type . "','" . addslashes($title) . "')";
            Data::sqlRecords($query, "", "insert");
        }
    }

    public static function sendCurlRequest($data = [], $url = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    /*Product Upadte using configuration*/
    // Update Product details
    public static function updateDetails($value = [], $sync = [], $merchant_id, $webhook = false)
    {
        try {
            $archiveSKU = [];
            $returnArr = [];
            $product_id = $value['id'];
            $count = 0;
            //check if product is not exits in database

            $result = Data::sqlRecords("SELECT title,sku,type,description,variant_id,image,qty,price,weight,attr_ids,vendor,upc,fulfillment_node,status FROM `jet_product` WHERE id='" . $product_id . "' LIMIT 0,1", "one", "select");
            $resultDetails = Data::sqlRecords("SELECT product_title,product_type,long_description,product_price,product_qty FROM `walmart_product` WHERE product_id='" . $product_id . "' LIMIT 0,1", "one", "select");
            if (!$result) {
                $resp = self::saveNewRecords($value, $merchant_id);
                if (isset($resp['error'])) {
                    $returnArr = ['count' => $count, 'error_message' => $resp['error']];
                }
                $count++;
                return $count;
            }
            $vendor = addslashes($value['vendor']);
            $product_type = isset($value['product_type']) ? $value['product_type'] : "";
            $title = addslashes($value['title']);
            $description = $value['body_html'];
            // for checking product description is UTF-8 encoded.
            if (mb_detect_encoding($description) !== 'UTF-8') {
                $description = addslashes(utf8_encode(preg_replace("/<script.*?\/script>/", "", $description) ?: $description));
            }
            $description = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $description);

            $image = WalmartscriptController::getImplodedImages($value['images']);
            $attr_ids = "";
            $attrId = [];
            foreach ($value['options'] as $val) {
                if ($val['name'] != 'Title') {
                    $attrId[$val['id']] = $val['name'];
                }
            }
            $attr_ids = (is_array($attrId) && count($attrId) > 0) ? addslashes(json_encode($attrId)) : '';
            $product_weight = 0.00;
            $status = WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED;
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
            if ($product_type != $resultDetails['product_type'] && isset($sync['sync-fields']['product_type'])) {
                $isProductTypeChanged = true;
                $query = 'SELECT id,category_id FROM `walmart_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($value['product_type']) . '" LIMIT 0,1';
                $walmodelmap = Data::sqlRecords($query, 'one', 'select');
                if ($walmodelmap) {
                    $category = $walmodelmap['category_id'];
                } else {
                    $query = 'INSERT INTO `walmart_category_map`(`merchant_id`,`product_type`) VALUES("' . $merchant_id . '","' . addslashes($value['product_type']) . '")';
                    Data::sqlRecords($query, null, 'insert');
                }
            }
            if ($resultDetails['product_title'] != $title && isset($sync['sync-fields']['title'])) {
                $isTitleChanged = true;
            }
            if ($resultDetails['long_description'] != $description && isset($sync['sync-fields']['description'])) {
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
                    $updateProduct = $updateProductDetails = $updateProductVariant = $updateWalmartProductVariant = "";
                    if ($variant['sku'] == "" || !self::validateSku($variant['sku'], $product_id, $merchant_id) || in_array($variant['sku'], $skus)) {
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
                            $image_array = WalmartscriptController::getImage($value['images'], $variant['image_id']);
                            $option_image = addslashes($image_array['src']);
                        }
                    }
                    $option_weight = 0.00;
                    $weight_unit = $variant['weight_unit'];
                    if ($variant['weight'] > 0) {
                        $option_weight = (float)Jetappdetails::convertWeight($variant['weight'], $weight_unit);
                    }

                    $option_price = $variant['price'];
                    $option_qty = $variant['inventory_quantity'];
                    $option_barcode = $variant['barcode'];
                    $variant_option1 = isset($variant['option1']) ? $variant['option1'] : '';
                    $variant_option2 = isset($variant['option2']) ? $variant['option2'] : '';
                    $variant_option3 = isset($variant['option3']) ? $variant['option3'] : '';

                    //save data in `jet_product_variants`
                    $resulVar = Data::sqlRecords("SELECT option_title,option_sku,option_image,option_qty,option_weight,option_price,option_unique_id,variant_option1,variant_option2,variant_option3 FROM `jet_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1", "one", "select");
                    $walresult = Data::sqlRecords("SELECT * FROM `walmart_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1", 'one', 'select');
                    $isVariantExist = false;
                    $isMainProduct = false;

                    if (!isset($resulVar['option_sku']) || !isset($walresult['option_id'])) {

                        //variant doesn't exist
                        if (count($variants) > 1) {
                            if (!isset($resulVar['option_sku'])) {
                                $sql = "INSERT INTO `jet_product_variants`(`option_id`, `product_id`, `merchant_id`, `option_title`, `option_sku`, `jet_option_attributes`, `option_image`, `option_qty`, `option_weight`, `option_price`, `option_unique_id`,`variant_option1`, `variant_option2`, `variant_option3`, `vendor`) VALUES ({$option_id},{$product_id},{$merchant_id},'{$option_title}','{$option_sku}',NULL,'" . addslashes($option_image) . "','{$option_qty}','" . (float)$option_weight . "','" . (float)$option_price . "','{$option_barcode}','" . addslashes($variant_option1) . "','" . addslashes($variant_option2) . "','" . addslashes($variant_option3) . "','" . addslashes($vendor) . "')";
                                Data::sqlRecords($sql, null, "insert");
                            }

                            $updateChanges = true;

                            if (!isset($walresult['option_id'])) {

                                $sql = "INSERT INTO `walmart_product_variants`(
                                        `option_id`,`product_id`,`merchant_id`,`status`,`new_variant_option_1`,`new_variant_option_2`,`new_variant_option_3`
                                        )

                                        VALUES({$option_id},{$product_id},{$merchant_id},'" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "','" . addslashes($variant_option1) . "','" . addslashes($variant_option2) . "','" . addslashes($variant_option3) . "')";
                                Data::sqlRecords($sql);
                            }

                        }
                        if ($result['variant_id'] == $option_id) {

                            $isMainProduct = true;
                            $isParentExist = true;

                            if ($result['sku'] != $option_sku && isset($sync['sync-fields']['sku'])) {
                                $archiveSKU[] = $result['sku'];
                                $updateProduct .= "`sku`='" . addslashes($option_sku) . "',";
                                $updateProduct .= "`status`='" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "',";

                            }
                            if ($resultDetails['product_qty'] != $option_qty && isset($sync['sync-fields']['qty'])) {

                                $inventoryData[$option_id] = ["inventory" => (int)$option_qty, "sku" => $option_sku, "merchant_id" => $merchant_id];
                                $updateProductDetails .= "`product_qty`='" . (int)$option_qty . "',";

                            }
                            if (round($result['weight'], 2) != round($option_weight, 2) && isset($sync['sync-fields']['weight'])) {
                                $updateProduct .= "`weight`='" . (float)$option_weight . "',";
                            }
                            if ($resultDetails['product_price'] != $option_price && isset($sync['sync-fields']['price'])) {
                                $isRepricingEnabled = WalmartRepricing::isRepricingEnabled($option_sku);
                                if (!$isRepricingEnabled) {
                                    $priceData[$option_id] = ["product_id" => $product_id, "price" => (float)$option_price, "sku" => $option_sku, "merchant_id" => $merchant_id];
                                }
                                /*$updateProduct.= "`price`='".(float)$option_price."',";
                                if(isset($resultDetails['product_price']) && $resultDetails['product_price'])*/
                                $updateProductDetails .= "`product_price`='" . (float)$option_price . "',";
                            }
                            if ($option_barcode && ($result['upc'] != $option_barcode || strlen($option_barcode) != strlen($result['upc'])) && isset($sync['sync-fields']['upc'])) {
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
                                $updateProduct .= "`status`='" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "',";
                                $updateProductDetails .= "`status`='" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "',";
                            }
                            $updateProductVariant .= "`option_sku`='" . addslashes($option_sku) . "',";
                            $updateProductVariant .= "`status`='" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "',";
                            $updateWalmartProductVariant .= "`status`='" . WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED . "',";
                        }
                        if ($walresult['option_qtys'] !== $option_qty && isset($sync['sync-fields']['qty'])) {
                            $inventoryData[$option_id] = ["inventory" => (int)$option_qty, "sku" => $option_sku, "merchant_id" => $merchant_id];
                            if ($isMainProduct)
                                $updateProductDetails .= "`product_qty`='" . (int)$option_qty . "',";
                            $updateWalmartProductVariant .= "`option_qtys`='" . (int)$option_qty . "',";
                        }
                        if (round($resulVar['option_weight'], 2) != round($option_weight, 2) && isset($sync['sync-fields']['weight'])) {
                            if ($isMainProduct)
                                $updateProduct .= "`weight`='" . (float)$option_weight . "',";
                            $updateProductVariant .= "`option_weight`='" . (float)$option_weight . "',";
                        }
                        if (isset($sync['sync-fields']['price'])) {
                            if ($walresult['option_prices']) {
                                if ($walresult['option_prices'] != $option_price) {
                                    $isRepricingEnabled = WalmartRepricing::isRepricingEnabled($option_sku, $merchant_id);
                                    if (!$isRepricingEnabled) {
                                        $priceData[$option_id] = ["product_id" => $product_id, "price" => (float)$option_price, "sku" => $option_sku, "merchant_id" => $merchant_id];

                                    }
                                    if ($isMainProduct) {
                                        /*$updateProduct.= "`price`='".(float)$option_price."',";
                                        if(isset($resultDetails['product_price']) && $resultDetails['product_price'])*/
                                        $updateProductDetails .= "`product_price`='" . (float)$option_price . "',";
                                    }
                                    $updateWalmartProductVariant .= "`option_prices`='" . (float)$option_price . "',";
                                }
                            } else {
                                if ($resulVar['option_price'] != $option_price) {
                                    $isRepricingEnabled = WalmartRepricing::isRepricingEnabled($option_sku, $merchant_id);
                                    if (!$isRepricingEnabled) {
                                        $priceData[$option_id] = ["product_id" => $product_id, "price" => (float)$option_price, "sku" => $option_sku, "merchant_id" => $merchant_id];

                                    }
                                    if ($isMainProduct) {
                                        /*$updateProduct.= "`price`='".(float)$option_price."',";
                                        if(isset($resultDetails['product_price']) && $resultDetails['product_price'])*/
                                        $updateProductDetails .= "`product_price`='" . (float)$option_price . "',";
                                    }
                                    $updateWalmartProductVariant .= "`option_prices`='" . (float)$option_price . "',";
                                }
                            }
                        }
                        if ($option_barcode && ($resulVar['option_unique_id'] != $option_barcode || strlen($option_barcode) != strlen($resulVar['option_unique_id'])) && isset($sync['sync-fields']['upc'])) {
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
                            $updateWalmartProductVariant .= "`new_variant_option_1`='" . addslashes($variant_option1) . "',";
                        }
                        if ($resulVar['variant_option2'] != $variant_option2 && isset($sync['sync-fields']['variant_options'])) {
                            $updateProductVariant .= "`variant_option2`='" . addslashes($variant_option2) . "',";
                            $updateWalmartProductVariant .= "`new_variant_option_2`='" . addslashes($variant_option2) . "',";
                        }
                        if ($resulVar['variant_option3'] != $variant_option3 && isset($sync['sync-fields']['variant_options'])) {
                            $updateProductVariant .= "`variant_option3`='" . addslashes($variant_option3) . "',";
                            $updateWalmartProductVariant .= "`new_variant_option_3`='" . addslashes($variant_option3) . "',";
                        }
                    }
                    if ($isMainProduct) {
                        if ($isProductTypeChanged) {
                            $updateProductDetails .= "`product_type`='" . addslashes($product_type) . "',";
                        }
                        if ($isTitleChanged) {
                            /*$updateProduct.= "`title`='".$title."',";
                            if(isset($resultDetails['product_title']) && $resultDetails['product_title'])*/
                            $updateProductDetails .= "`product_title`='" . $title . "',";
                        }
                        if ($isDescriptionChanged) {
                            $updateProduct .= "`description`='" . addslashes($description) . "',";
                            if (isset($resultDetails['long_description']) && $resultDetails['long_description'])
                                $updateProductDetails .= "`long_description`='" . addslashes($description) . "',";
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
                        Data::sqlRecords($query);
                    }
                    if ($updateProductDetails) {
                        //echo "<br>updateProductDetails".$updateProductDetails;
                        $updateChanges = true;
                        $updateProductDetails = rtrim($updateProductDetails, ',');
                        $query = "UPDATE `walmart_product` SET " . $updateProductDetails . " WHERE product_id=" . $product_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query);
                    }
                    if ($updateProductVariant) {
                        //echo "<br>updateProductVariant".$updateProductVariant;
                        $updateChanges = true;
                        $updateProductVariant = rtrim($updateProductVariant, ',');
                        $query = "UPDATE `jet_product_variants` SET " . $updateProductVariant . " WHERE option_id=" . $option_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query);
                    }
                    if ($updateWalmartProductVariant) {
                        //echo "<br>updateProductVariant".$updateProductVariant;
                        $updateChanges = true;
                        $updateWalmartProductVariant = rtrim($updateWalmartProductVariant, ',');
                        $query = "UPDATE `walmart_product_variants` SET " . $updateWalmartProductVariant . " WHERE option_id=" . $option_id;
                        //echo $query."<hr>";
                        Data::sqlRecords($query);
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
            //var_dump($e);die;
            Data::createExceptionLog('actionCurlproductcreate', $e->getMessage(), $merchant_id);
            exit(0);
        } catch (Exception $e) {
            Data::createExceptionLog('actionCurlproductcreate', $e->getMessage(), $merchant_id);
            exit(0);
        }
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
        Data::sqlRecords('DELETE FROM `jet_product` WHERE merchant_id="' . $merchant_id . '" AND  id="' . $product_id . '"');
        Data::sqlRecords('DELETE FROM `jet_product_variants` WHERE merchant_id="' . $merchant_id . '" AND  product_id="' . $product_id . '"');
        self::saveNewRecords($data, $merchant_id);
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
                    Data::sqlRecords("DELETE FROM `jet_product_variants` WHERE option_id=" . $value['option_id']);
                }
            }
        }
        return $archiveSKU;
    }
}

?>