<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 28/3/17
 * Time: 5:07 PM
 */
namespace frontend\modules\neweggcanada\controllers;

use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\controllers\NeweggMainController;
use Yii;
use yii\base\Exception;
use yii\web\Response;
use yii\web\UploadedFile;

class ProductcsvController extends NeweggMainController
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
        if(isset($_POST['export'])){
            $action = $_POST['export'];
        }else{
            $action = '';
        }

        if($action==''){
            Yii::$app->session->setFlash('error', "Please Select the status of product....");
            return $this->render('index');
        }

        if (!file_exists(\Yii::getAlias('@webroot') . '/var/newegg/csv_export/product/whole-product' . $merchant_id)) {
            mkdir(\Yii::getAlias('@webroot') . '/var/newegg/csv_export/product/whole-product' . $merchant_id, 0775, true);
        }
        $base_path = \Yii::getAlias('@webroot') . '/var/newegg/csv_export/product/whole-product' . $merchant_id . '/product.csv';
        $file = fopen($base_path, "w");

        $headers = array('Id', 'Product id','Sku', 'Type', 'Title', 'Weight', 'Manufactutrer', 'Bullet_Description', 'Long_Description', 'Status');

        $row = array();
        foreach ($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file, $row);

        $productdata = array();
        $i = 0;

        //$model = JetProduct::find()->where(['merchant_id'=>$merchant_id])->all();
        /* if ($merchant_id == '947') {
             $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id_override`='1') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "'", 'all');

         } else {*/
        if (in_array('all', $action)) {

            $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `newegg_can_product` WHERE `merchant_id`='" . $merchant_id . "') as `np` ON `jp`.`id`=`np`.`product_id` WHERE `np`.`merchant_id`= '" . $merchant_id . "'", 'all');

        } else {
            $status = implode("','", $action);
            $query = "SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `newegg_can_product` WHERE `merchant_id`='" . $merchant_id . "' AND `upload_status` IN ('" . $status . "')) as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "'";
            $model = Data::sqlRecords($query, 'all');
        }

        /*}*/

        //print_r("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "'");die('dfgd');
        foreach ($model as $value) {

            if ($value['sku'] == "") {
                continue;
            }
            $productdata[$i]['title'] = $value['title'];

            if (!empty($value['product_title'])) {
                $productdata[$i]['title'] = $value['product_title'];
            }

            if (!empty($value['long_description'])) {
                $productdata[$i]['long_description'] = $value['long_description'];
            } else {
                $productdata[$i]['long_description'] = $value['description'];
            }

            /*if (!empty($value['short_description'])) {
                $productdata[$i]['short_description'] = $value['short_description'];
            }*/

            /*if ($value['type'] == "simple") {*/
            $productdata[$i]['manufacturer'] = $value['manufacturer'];
            $productdata[$i]['weight'] = $value['weight'];
            $productdata[$i]['id'] = $value['id'];
            $productdata[$i]['product_id'] = $value['product_id'];
            $productdata[$i]['sku'] = $value['sku'];
            $productdata[$i]['type'] = "No Variants";
            $productdata[$i]['upload_status'] = $value['upload_status'];
            $productdata[$i]['short_description'] = $value['short_description'];

            /*} else {
                continue;
            }*/
            $i++;
        }
        if(empty($productdata)){
            Yii::$app->session->setFlash('error', "No Product found...");
            return $this->render('index');
        }

        foreach ($productdata as $v) {

            $row = array();
            $row[] = $v['id'];
            $row[] = $v['product_id'];
            $row[] = $v['sku'];
            $row[] = $v['type'];
            $row[] = $v['title'];
            $row[] = $v['weight'];
            $row[] = $v['manufacturer'];
            $row[] = $v['short_description'];
            $row[] = $v['long_description'];
            $row[] = $v['upload_status'];

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
            $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'text/comma-separated-values', 'application/octet-stream');
            if (!in_array($_FILES['csvfile']['type'], $mimes)) {
                Yii::$app->session->setFlash('error', "Invalid file type. Please import only CSV file");
                return $this->redirect(['index']);
            }

            $newname = $_FILES['csvfile']['name'];

            if (!file_exists(Yii::getAlias('@webroot') . '/var/newegg/csv_import/product/' . date('d-m-Y') . '/' . $merchant_id)) {
                mkdir(Yii::getAlias('@webroot') . '/var/newegg/csv_import/product/' . date('d-m-Y') . '/' . $merchant_id, 0775, true);
            }

            $target = Yii::getAlias('@webroot') . '/var/newegg/csv_import/product/' . date('d-m-Y') . '/' . $merchant_id . '/' . $newname . '-' . time();
            $row = 0;
            $flag = false;
            $row1 = 0;
            if (!file_exists($target)) {
                move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
            }

            $selectedProducts = array();
            $import_errors = array();
            if (($handle = fopen($target, "r"))) {

                $row = 0;
                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {
                    if ($row == 0 && (trim($data[0]) != 'Id' || trim($data[1]) != 'Product id'|| trim($data[2]) != 'Sku' || trim($data[3]) != 'Type'/* || trim($data[3]) != 'Title' || trim($data[4]) != 'Fullfilment_Lag_Time' || trim($data[5]) != 'Sku_Override' || trim($data[6]) != 'Product_Id_Override' || trim($data[7]) != 'Short_Description' || trim($data[8]) != 'Self_Description' || trim($data[9]) != 'Description' || trim($data[10]) != 'Product_taxcode'*/)) {
                        $flag = true;
                        $import_errors[$row] = 'Error : Invalid file. Please choose a valid file ';

                        break;
                    }
                    $num = count($data);
                    $row++;
                    if ($row == 1)
                        continue;

                    $pro_id = trim($data[0]);
                    $pro_product_id = trim($data[1]);
                    $pro_sku = trim($data[2]);
                    $pro_type = trim($data[3]);
                    $pro_title = trim($data[4]);
                    $pro_weight = trim($data[5]);
                    $pro_manufacturer = trim($data[6]);
                    $pro_bullet_description = trim($data[7]);
                    $pro_long_description = trim($data[8]);

                    if ($pro_id == '' || $pro_sku == '' || $pro_type == '' /*|| $pro_title == '' || $pro_weight == '' || $pro_manufacturer == '' || $pro_bullet_description == '' || $pro_long_description == ''*/) {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid data.';
                        continue;
                    }

                    if (!is_numeric($pro_id) /*|| !is_numeric($pro_taxcode)*/) {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid product_id / product data.';
                        continue;
                    }

                    $productData = array();
                    $productData['id'] = $pro_id;
                    $productData['product_id'] = $pro_product_id;
                    $productData['sku'] = $pro_sku;
                    $productData['type'] = $pro_type;
                    $productData['title'] = addslashes($pro_title);
                    $productData['weight'] = $pro_weight;
                    $productData['manufacturer'] = $pro_manufacturer;
                    $productData['bullet_description'] = addslashes($pro_bullet_description);
                    $productData['long_description'] = addslashes($pro_long_description);
                    $selectedProducts[] = $productData;
                }

                if (count($selectedProducts)) {

                    $session = Yii::$app->session;

                    $size_of_request = 10;//Number of products to be uploaded at once(in single feed)
                    $pages = (int)(ceil(count($selectedProducts) / $size_of_request));

                    return $this->render('ajaxbulkupdate', [
                        'totalcount' => count($selectedProducts),
                        'pages' => $pages,
                        'products' => json_encode($selectedProducts)
                    ]);

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

    public function actionBatchupdate()
    {

        $session = Yii::$app->session;
        $products = Yii::$app->request->post();

        $when_title = '';
        $when_weight = '';
        $when_manufacturer = '';
        $when_bullet_description = '';
        $when_long_description = '';
        $id = [];
        $option_id = [];
        foreach ($products['products'] as $product) {

            if ($product['type'] == 'No Variants') {
                $id[] = $product['id'];
                $product_id[]=$product['product_id'];
                $when_title .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . addslashes($product['title']) . '"';
                $when_weight .= ' WHEN ' . $product['product_id'] . ' THEN ' . '"' . $product['weight'] . '"';
                $when_manufacturer .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . addslashes($product['manufacturer']) . '"';
                $when_bullet_description .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . addslashes($product['bullet_description']) . '"';
                $when_long_description .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . addslashes($product['long_description']) . '"';
            }

        }
        $ids = implode(',', $id);
        $product_ids = implode(',', $product_id);
        try {
            $query = "UPDATE `newegg_can_product` SET 
                                    `product_title` = CASE `id` 
                                   " . $when_title . "
                                END, 
                                    `manufacturer` = CASE `id`
                                    " . $when_manufacturer . " 
                                END, 
                                    `short_description` = CASE `id`
                                    " . $when_bullet_description . " 
                                END, 
                                    `long_description` = CASE `id`
                                    " . $when_long_description . " 
                                END 
                                WHERE id IN (" . $ids . ")";

            Data::sqlRecords($query, null, 'update');

            $query1 = "UPDATE `jet_product` SET 
                                    `weight` = CASE `id` 
                                   " . $when_weight . "
                                END
                                WHERE id IN (" . $product_ids . ")";

            Data::sqlRecords($query1, null, 'update');

            $return_msg['success']['message'] = "Product(s) information successfully updated";
            $return_msg['success']['count'] = count($id);
        } catch (Exception $e) {
            $return_msg['error'] = 'Invalid Data';
            //$return_msg['error'] = $e->getMessage();
        }
        return json_encode($return_msg);
    }
}