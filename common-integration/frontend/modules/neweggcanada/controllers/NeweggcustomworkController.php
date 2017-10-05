<?php

namespace frontend\modules\neweggcanada\controllers;

use yii\web\Controller;
use frontend\modules\neweggcanada\controllers;
use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\Neweggapi;
use frontend\modules\neweggcanada\components\Cronrequest;
use yii\helpers\BaseJson;
use Yii;
use frontend\modules\neweggcanada\components\product\Productimport;
use frontend\modules\neweggcanada\components\ShopifyClientHelper;
use frontend\modules\neweggcanada\components\product\ProductPrice;
use frontend\modules\neweggcanada\components\product\ProductInventory;
use frontend\modules\neweggcanada\components\product\ProductStatus;
use frontend\modules\neweggcanada\components\Mail;
use frontend\modules\neweggcanada\controllers\ShopifywebhookController;
use frontend\modules\neweggcanada\components\product\ValueMappingHelper;


class NeweggcustomworkController extends NeweggMainController
{
    /**
     * Check request authentication
     * @return user status
     */

    public function actionIndex()
    {

        return $this->render('index');
    }

    public function actionCustomvaluemap()
    {
        $all_merchant = "SELECT `merchant_id` FROM `newegg_can_installation` WHERE status = 'complete'";
        $merchant_ids = Data::sqlRecords($all_merchant, 'all', 'select');
        foreach ($merchant_ids as $merchant_id) {
            $all_category = "SELECT `product_type`,`category_id` FROM `newegg_can_category_map` WHERE merchant_id = '" . $merchant_id['merchant_id'] . "'";
            $category_datas = Data::sqlRecords($all_category, 'all', 'select');
            foreach ($category_datas as $category_data) {
                if ($category_data['category_id']) {
                    $query = "SELECT `jet`.* FROM `jet_product` AS jet INNER JOIN `newegg_can_product` AS ng ON `jet`.`id`=`ng`.`product_id` WHERE `ng`.`merchant_id` = '" . $merchant_id['merchant_id'] . "' AND `ng`.`shopify_product_type`='" . addslashes($category_data['product_type']) . "' AND `ng`.`newegg_category`='" . $category_data['category_id'] . "'";
                    $all_category_product = Data::sqlRecords($query, 'all', 'select');
                    foreach ($all_category_product as $product) {
                        if ($product['attr_ids']) {
                            $attr_ids = json_decode($product['attr_ids'], true);
                            $count = 1;
                            foreach ($attr_ids as $attr_id) {
                                $selectQuery = "SELECT `variant_option" . $count . "` AS optionval FROM `jet_product_variants` WHERE merchant_id='" . $merchant_id['merchant_id'] . "' AND product_id='" . $product['id'] . "'";
                                $variant_product_data = Data::sqlRecords($selectQuery, 'all', 'select');
                                $count++;
                                $attrArray = [];
                                foreach ($variant_product_data as $key => $value) {
                                    $attrArray[$value['optionval']] = '1';
                                    $selectval = "SELECT * FROM `newegg_can_value_attribute_mapping` WHERE merchant_id='" . $merchant_id['merchant_id'] . "' AND product_type='" . addslashes($category_data['product_type']) . "' AND attribute_name ='" . $attr_id . "'";
                                    $selectedVal = Data::sqlRecords($selectval, 'one', 'select');
                                    if ($selectedVal) {
                                        $prev_attr = json_decode($selectedVal['value'], true);
                                        $newArray = array_merge($prev_attr, $attrArray);
                                        $updateQuery = "UPDATE `newegg_can_value_attribute_mapping` SET `value`='" . addslashes(json_encode($newArray)) . "' WHERE merchant_id='" . $merchant_id['merchant_id'] . "' AND product_type='" . addslashes($category_data['product_type']) . "' AND attribute_name ='" . $attr_ids . "'";
                                        Data::sqlRecords($updateQuery, null, 'update');
                                    } else {
                                        $newArray = json_encode($attrArray);
                                        $insertQuery = "INSERT INTO `newegg_can_value_attribute_mapping`(`attribute_name`, `merchant_id`, `product_type`,`category_id`, `value`) VALUES ('" . $attr_id . "','" . $merchant_id['merchant_id'] . "','" . addslashes($category_data['product_type']) . "','" . $category_data['category_id'] . "','" . addslashes($newArray) . "')";
                                        $selectedVal = Data::sqlRecords($insertQuery, null, 'insert');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function actionNeweggordersync()
    {
        $obj = new NeweggorderdetailController(Yii::$app->controller->id, '');
        $obj->actionSyncorder(true);
    }

    public function actionNeweggorderdetails()
    {
        $obj = new NeweggorderdetailController(Yii::$app->controller->id, '');
        $obj->actionOrderdetails(true);
    }

    public function actionGetproduct()
    {
        $shopname = Yii::$app->user->identity->username;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        $prod_id = $_GET['id'];
        $countProducts = $sc->call('GET', '/admin/products/' . $prod_id . '.json');
        echo "<pre>";
        print_r($countProducts);
        die;
    }

    public function actionGetallproduct()
    {
        $index = $_GET['index'];
        $shopname = Yii::$app->user->identity->username;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        $prod_id = $_GET['id'];
        $countProducts = $sc->call('GET', '/admin/products.json', array('limit' => 250, 'page' => $index));
        echo "<pre>";
        print_r($countProducts);
        die;
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
                    if ($row == 0 && (trim($data[0]) != 'Id' || trim($data[1]) != 'Product Id' || trim($data[2]) != 'Sku' /* || trim($data[3]) != 'Title' || trim($data[4]) != 'Fullfilment_Lag_Time' || trim($data[5]) != 'Sku_Override' || trim($data[6]) != 'Product_Id_Override' || trim($data[7]) != 'Short_Description' || trim($data[8]) != 'Self_Description' || trim($data[9]) != 'Description' || trim($data[10]) != 'Product_taxcode'*/)) {
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
                    $pro_qty = trim($data[3]);
                    $pro_price = trim($data[4]);
                    $pro_barcode = trim($data[5]);

                    if ($pro_id == '' || $pro_sku == '' /*|| $pro_title == '' || $pro_weight == '' || $pro_manufacturer == '' || $pro_bullet_description == '' || $pro_long_description == ''*/) {
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
                    $productData['qty'] = $pro_qty;
                    $productData['price'] = $pro_price;
                    $productData['upc'] = $pro_barcode;
                    $selectedProducts[] = $productData;
                }

                if (count($selectedProducts)) {

                    foreach ($selectedProducts as $product) {
                        $data = Data::sqlRecords('SELECT * FROM `jet_product` WHERE `product_id`="' . $product['product_id'] . '" AND `merchant_id`=1253', 'one');
                        if ($data) {
                            Data::sqlRecords('UPDATE `jet_product` SET `upc`="' . $product['upc'] . '" , `qty`="' . $product['qty'] . '" WHERE `merchant_id`=1253 AND `id`="' . $product['product_id'] . '" AND `sku`="' . $product['sku'] . '"', 'update');
                        }
                    }

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

    public function actionTestmail(){
        $mailData = [
            'sender' => 'shopify@cedcommerce.com',
            'reciever' => 'shivamverma8829@gmail.com',
            'email' => 'shivamverma8829@gmail.com',
            'subject' => 'newegg canada installmail',
            'bcc' => 'shivamverma@cedcoss.com',
            'cc'=> 'shivamverma@cedcoss.com',
        ];
        $mailer = new Mail($mailData,'email/installmail.html','php',true);
        $mailer->sendMail();
    }

}

