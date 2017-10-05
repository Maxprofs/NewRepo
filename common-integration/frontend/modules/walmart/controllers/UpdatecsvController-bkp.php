<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 1/4/17
 * Time: 1:06 PM
 */
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use frontend\modules\walmart\models\JetProduct;
use frontend\modules\walmart\models\JetProductVariants;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\WalmartProduct;
use frontend\modules\walmart\models\WalmartProduct as WalmartProductModel;

class UpdatecsvController extends WalmartmainController
{
    protected $walmartHelper;

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        return $this->render('index');
    }

    public function actionExport()
    {
        $merchant_id = Yii::$app->user->identity->id;

        if (!file_exists(\Yii::getAlias('@webroot') . '/var/csv_export/product/' . $merchant_id)) {
            mkdir(\Yii::getAlias('@webroot') . '/var/csv_export/product/' . $merchant_id, 0775, true);
        }
        $base_path = \Yii::getAlias('@webroot') . '/var/csv_export/product/' . $merchant_id . '/product.csv';
        $file = fopen($base_path, "w");

        $headers = array('Id', 'Sku', 'Type', 'Title', 'Fullfilment_Lag_Time', 'Sku_Override', 'Product_Id_Override', 'Short_Description', 'Self_Description', 'Description', 'Product_taxcode', 'Barcode');

        $row = array();
        foreach ($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file, $row);

        $productdata = array();
        $i = 0;

        //$model = JetProduct::find()->where(['merchant_id'=>$merchant_id])->all();
        $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "'", 'all');
        foreach ($model as $value) {
            if ($value['sku'] == "") {
                continue;
            }
            $productdata[$i]['title'] = $value['title'];

            if (!empty($value['product_title'])) {
                $productdata[$i]['title'] = $value['product_title'];
            }
            $productdata[$i]['fulfilment_lag_time'] = $value['fulfillment_lag_time'];
            $productdata[$i]['sku_override'] = $value['sku_override'];
            $productdata[$i]['product_id_override'] = $value['product_id_override'];
            $productdata[$i]['short_description'] = $value['short_description'];
            $productdata[$i]['self_description'] = $value['self_description'];
            $productdata[$i]['tax_code'] = $value['tax_code'];
            $productdata[$i]['long_description'] = $value['long_description'];

            if ($value['type'] == "simple") {
                $productdata[$i]['id'] = $value['id'];
                $productdata[$i]['sku'] = $value['sku'];
                $productdata[$i]['type'] = "No Variants";
                $productdata[$i]['upc'] = $value['upc'];
//                $i++;
            } else {
                $optionResult = [];
                //$query = "SELECT option_id,option_title,option_sku,option_price,asin,option_mpn FROM `jet_product_variants` WHERE product_id='".$value['id']."' order by option_sku='".addslashes($value['sku'])."' desc";
                /*$query = "SELECT option_id,option_title,option_sku,option_price,asin,option_mpn FROM `jet_product_variants` WHERE product_id='".$value['id']."' order by option_sku='".addslashes($value['sku'])."' desc";
                $optionResult = Data::sqlRecords($query);*/
                $optionResult = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product_variants` WHERE `merchant_id`='" . $merchant_id . "') as `jpv` INNER JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`='" . $merchant_id . "') as `wpv` ON `jpv`.`product_id`=`wpv`.`product_id` WHERE `wpv`.`merchant_id`= '" . $merchant_id . "'", 'all');

                if (is_array($optionResult) && count($optionResult) > 0) {
                    foreach ($optionResult as $key => $val) {
                        if ($val['option_sku'] == "")
                            continue;
                        if ($value['sku'] == $val['option_sku']) {
                            $productdata[$i]['type'] = "Parent";
                        } else {
                            $productdata[$i]['type'] = "Variants";
                        }
                        $productdata[$i]['id'] = $val['id'];
                        $productdata[$i]['sku'] = $val['option_sku'];
                        $productdata[$i]['upc'] = $val['option_unique_id'];

                    }
                }
            }
            $i++;
        }
        foreach ($productdata as $v) {
            $row = array();
            $row[] = $v['id'];
            $row[] = $v['sku'];
            $row[] = $v['type'];
            $row[] = $v['title'];
            $row[] = $v['fulfilment_lag_time'];
            $row[] = $v['sku_override'];
            $row[] = $v['product_id_override'];
            $row[] = $v['short_description'];
            $row[] = $v['self_description'];
            $row[] = $v['long_description'];
            $row[] = $v['tax_code'];
            $row[] = $v['upc'];

            fputcsv($file, $row);
        }
        fclose($file);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return \Yii::$app->response->sendFile($base_path);
    }

    public function actionReadcsv()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;

        if (isset($_FILES['csvfile']['name'])) {
            //var_dump($_FILES);die;
            $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'text/comma-separated-values');
            if (!in_array($_FILES['csvfile']['type'], $mimes)) {
                Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");
                return $this->redirect(['index']);
            }

            $newname = $_FILES['csvfile']['name'];

            if (!file_exists(Yii::getAlias('@webroot') . '/var/csv_import/product/' . date('d-m-Y') . '/' . $merchant_id)) {
                mkdir(Yii::getAlias('@webroot') . '/var/csv_import/product/' . date('d-m-Y') . '/' . $merchant_id, 0775, true);
            }

            $target = Yii::getAlias('@webroot') . '/var/csv_import/product/' . date('d-m-Y') . '/' . $merchant_id . '/' . $newname . '-' . time();
            $row = 0;
            $flag = false;
            $row1 = 0;
            if (!file_exists($target)) {
                move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
            }

            $selectedProducts = array();
            $import_errors = array();
            if (($handle = fopen($target, "r"))) {
                $status = WalmartProductModel::PRODUCT_STATUS_UPLOADED;
                $allpublishedSku = WalmartProduct::getAllProductSku($merchant_id,$status);
                $row = 0;
                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {
                    if ($row == 0 && (trim($data[0]) != 'Id' || trim($data[1]) != 'Sku' || trim($data[2]) != 'Type' || trim($data[3]) != 'Title' || trim($data[4]) != 'Fullfilment_Lag_Time' || trim($data[5]) != 'Sku_Override' || trim($data[6]) != 'Product_Id_Override' || trim($data[7]) != 'Short_Description' || trim($data[8]) != 'Self_Description' || trim($data[9]) != 'Description' || trim($data[10]) != 'Product_taxcode' || trim($data[11]) != 'Barcode')) {
                        $flag = true;
                        break;
                    }
                    $num = count($data);
                    $row++;
                    if ($row == 1)
                        continue;

                    $pro_id = trim($data[0]);
                    $pro_sku = trim($data[1]);
                    $pro_type = trim($data[2]);
                    $pro_title = trim($data[3]);
                    $pro_fulfillment_lag_time = trim($data[4]);
                    $pro_price_sku_override = trim($data[5]);
                    $pro_price_product_id_override = trim($data[6]);
                    $pro_short_description = trim($data[7]);
                    $pro_self_description = trim($data[8]);
                    $pro_long_description = trim($data[9]);
                    $pro_taxcode = trim($data[10]);
                    $pro_upc = trim($data[11]);

                    if ($pro_id == '' || $pro_sku == '' || $pro_type == '' || $pro_title == '' || $pro_fulfillment_lag_time == '' || $pro_price_sku_override == '' || $pro_price_product_id_override == '' || $pro_short_description == '' || $pro_self_description == '' || $pro_long_description == '' || $pro_upc == '' || $pro_taxcode == '') {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid data.';
                        continue;
                    }

                    if (!is_numeric($pro_id) || !is_numeric($pro_upc)/* || is_numeric($pro_taxcode)*/) {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid product_id / price.';
                        continue;
                    }

                    /*if(!in_array($pro_sku, $allpublishedSku)) {
                        $import_errors[$row] = 'Row '.$row.' : '.'Sku => "'.$pro_sku.'" is invalid/not published on walmart.';
                        continue;
                    }*/

                    $productData = array();
                    $productData['id'] = $pro_id;
                    $productData['sku'] = $pro_sku;
                    $productData['type'] = $pro_type;
                    $productData['title'] = $pro_title;
                    $productData['fulfillment_lag_time'] = $pro_fulfillment_lag_time;
                    $productData['price_sku_override'] = $pro_price_sku_override;
                    $productData['price_product_id_override'] = $pro_price_product_id_override;
                    $productData['short_description'] = $pro_short_description;
                    $productData['self_description'] = $pro_self_description;
                    $productData['long_description'] = $pro_long_description;
                    $productData['tax_code'] = $pro_taxcode;
                    $productData['upc'] = $pro_upc;

                    $productData['currency'] = CURRENCY;

                    $selectedProducts[] = $productData;
                }

                if (count($selectedProducts)) {
                    $when_title = '';
                    $when_fulfillment_lag_time = '';
                    $when_skuoverride = '';
                    $when_product_id_override = '';
                    $when_short_description = '';
                    $when_self_description = '';
                    $when_long_description = '';
                    $when_taxcode = '';
                    $when_barcode = '';

                    /*$when_option_title ='';
                    $when_option_fulfillment_lag_time ='';
                    $when_option_skuoverride = '';
                    $when_option_product_id_override = '';
                    $when_option_short_description = '';
                    $when_option_self_description = '';
                    $when_option_long_description = '';
                    $when_option_taxcode = '';
                    $when_option_barcode = '';*/
                    $id = [];
                    $option_id = [];
                    foreach ($selectedProducts as $product) {

                        if($product['type'] == 'No Variants')
                        {
                            $id[] = $product['id'];
                            $when_title .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['title'].'"';
                            $when_fulfillment_lag_time .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_skuoverride .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['price_sku_override'].'"';
                            $when_product_id_override .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['price_product_id_override'].'"';
                            $when_short_description .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['short_description'].'"';
                            $when_self_description .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['self_description'].'"';
                            $when_long_description .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['long_description'].'"';
                            $when_taxcode .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['tax_code'].'"';
                            $when_barcode .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['upc'].'"';

                        }/*elseif($product['type'] == 'Variants'){
                            $option_id[] = $product['id'];
                            $when_option_title .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['title'].'"';
                            $when_option_fulfillment_lag_time .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_skuoverride .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_product_id_override .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_short_description .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_self_description .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_long_description .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_taxcode .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                            $when_option_barcode .= ' WHEN '. $product['id'] . ' THEN ' .'"'.$product['fulfillment_lag_time'].'"';
                        }*/

                    }
                    $ids = implode(',',$id);
                    $query = "UPDATE `walmart_product` SET 
                                    `product_title` = CASE `id` 
                                   ".$when_title."
                                END, 
                                    `fulfillment_lag_time` = CASE `id`
                                    ".$when_fulfillment_lag_time." 
                                END, 
                                    `sku_override` = CASE `id`
                                    ".$when_skuoverride." 
                                END, 
                                    `product_id_override` = CASE `id`
                                    ".$when_product_id_override." 
                                END, 
                                    `short_description` = CASE `id`
                                    ".$when_short_description." 
                                END, 
                                    `self_description` = CASE `id`
                                    ".$when_self_description." 
                                END, 
                                    `long_description` = CASE `id`
                                    ".$when_long_description." 
                                END, 
                                    `tax_code` = CASE `id`
                                    ".$when_taxcode." 
                                END, 
                                    `upc` = CASE `id`
                                    ".$when_barcode." 
                                END
                                WHERE id IN (".$ids.")";

                    /* $option_ids = implode(',',$option_id);
                     $variants_query = "UPDATE `walmart_product` SET
                                     `product_title` = CASE `id`
                                    ".$when_option_title."
                                 END,
                                     `fulfillment_lag_time` = CASE `id`
                                     ".$when_option_fulfillment_lag_time."
                                 END
                                 WHERE id IN (".$option_ids.")";*/

                    print_r($query);die;

                    //Data::sqlRecords();
                } else {
                    if (count($import_errors)) {
                        Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
                    } else {
                        Yii::$app->session->setFlash('error', "Please Upload Csv file....");
                    }
                }
            } else {
                Yii::$app->session->setFlash('error', "File not found....");
            }
        } else {
            Yii::$app->session->setFlash('error', "Please Upload Csv file....");
        }
        return $this->redirect(['index']);
    }


}