<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 20/6/17
 * Time: 12:15 PM
 */
namespace frontend\modules\neweggcanada\controllers;

use frontend\modules\neweggcanada\components\Helper;
use frontend\modules\neweggcanada\components\product\ProductInventory;
use frontend\modules\neweggcanada\components\product\ProductPrice;
use frontend\modules\neweggcanada\components\productInfo;
use frontend\modules\neweggcanada\controllers\NeweggMainController;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartProduct;
use Yii;
use yii\web\Response;

class UpdatecsvController extends NeweggMainController
{

    const PRICE = 'Price';
    const QTY = 'Qty';
    const BARCODE = 'Barcode';

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
        $action = $_POST['export'];
        $status = $_POST['status'];

        if (empty($action)) {
            Yii::$app->session->setFlash('error', "Please select an option to export CSV");

            return $this->redirect('index');
        }

        if (!file_exists(\Yii::getAlias('@webroot') . '/var/newegg_canada/csv_export/' . $merchant_id . '/' . $action)) {
            mkdir(\Yii::getAlias('@webroot') . '/var/newegg_canada/csv_export/' . $merchant_id . '/' . $action, 0775, true);
        }
        $base_path = \Yii::getAlias('@webroot') . '/var/newegg_canada/csv_export/' . $merchant_id . '/' . $action . '/' . $action . '.csv';
        $file = fopen($base_path, "w");

        $column = '';
        if ($action == 'price') {
            $column = 'Price';
        } elseif ($action == 'qty') {
            $column = 'Qty';
        } elseif ($action == 'upc') {
            $column = self::BARCODE;
        }

        if ($action == 'upc') {
            $headers = array('Id', 'OptionId', 'Sku', 'Type', $column, 'Mpn', 'Status');
        } else {
            $headers = array('Id', 'OptionId', 'Sku', 'Type', $column, 'Status');
        }

        $row = array();
        foreach ($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file, $row);

        $productdata = array();
        $i = 0;

        //$model = JetProduct::find()->select('id,variant_id,sku,' . $action . ',type')->where(['merchant_id' => $merchant_id])->all();

//        var_dump(in_array('all',$status));die;
        if (in_array('all', $status)) {
            $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `newegg_can_product` WHERE `merchant_id`='" . $merchant_id . "') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "'", 'all');
        } else {
            $status1 = implode("','", $status);

            $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `newegg_can_product` WHERE `merchant_id`='" . $merchant_id . "' AND `upload_status` IN ('" . $status1 . "')) as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "'", 'all');
        }

        foreach ($model as $value) {
            if ($value['sku'] == "") {
                continue;
            }
            /*if($status && $status == 'all'){
                $product_price = Data::sqlRecords("SELECT `product_price`,`product_title` FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id`='" . $value->id . "'", 'one');

            }else{
                $product_price = Data::sqlRecords("SELECT `product_price`,`product_title` FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id`='" . $value->id . "' AND `status`='".$status."'", 'one');
            }*/

            if ($value['type'] == "simple") {
                $productdata[$i]['id'] = $value['product_id'];
                $productdata[$i]['sku'] = $value['sku'];
                $productdata[$i]['type'] = "No Variants";
                //$productdata[$i]['price']=$value->price;
                $productdata[$i][$action] = $value[$action];
                $productdata[$i]['option_id'] = $value['variant_id'];
                $productdata[$i]['status'] = $value['status'];
                if ($action == 'upc') {
                    $productdata[$i]['id'] = $value['product_id'];
                    $productdata[$i]['mpn'] = $value['mpn'];
                }

                //$product_price = Data::sqlRecords("SELECT `product_price`,`product_title` FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id`='" . $value->id . "'", 'one');
                /*if (!empty($product_price['product_price']) && $action == 'price') {
                    $productdata[$i][$action] = $product_price['product_price'];
                } elseif (!empty($product_price['product_title']) && $action == 'title') {
                    $productdata[$i][$action] = $product_price['product_title'];
                }*/
                if ($value['product_price'] != '' && $action == 'price') {
                    $productdata[$i][$action] = $value['product_price'];
                }

                $i++;
            } else {
                /*$optionResult = [];
                $query = "SELECT option_id,option_title,option_sku,option_qty,option_unique_id,option_price,asin,option_mpn FROM `jet_product_variants` WHERE product_id='" . $value['id'] . "' order by option_sku='" . addslashes($value['sku']) . "' desc";
                $optionResult = Data::sqlRecords($query, 'all');*/
                if (in_array('all', $status)) {
                    $optionResult = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product_variants` WHERE `merchant_id`='" . $merchant_id . "') as `jpv` INNER JOIN (SELECT * FROM `newegg_can_product_variants` WHERE `merchant_id`='" . $merchant_id . "') as `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` WHERE `wpv`.`merchant_id`= '" . $merchant_id . "' AND `wpv`.`product_id`='" . $value['product_id'] . "'", 'all');

                } else {
                    /*$status = implode("','", $status);*/

                    $optionResult = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product_variants` WHERE `merchant_id`='" . $merchant_id . "') as `jpv` INNER JOIN (SELECT * FROM `newegg_can_product_variants` WHERE `merchant_id`='" . $merchant_id . "' AND `upload_status` IN ('" . $status1 . "')) as `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` WHERE `wpv`.`merchant_id`= '" . $merchant_id . "' AND `wpv`.`product_id`='" . $value['product_id'] . "'", 'all');
                }
                //print_r($optionResult);die;

                if (is_array($optionResult) && count($optionResult) > 0) {
                    foreach ($optionResult as $key => $val) {
                        if ($val['option_sku'] == "")
                            continue;
                        if ($value['sku'] == $val['option_sku']) {
                            $productdata[$i]['type'] = "Parent";
                        } else {
                            $productdata[$i]['type'] = "Variants";
                        }
                        $productdata[$i]['id'] = $value['product_id'];
                        $productdata[$i]['option_id'] = $val['option_id'];
                        $productdata[$i]['sku'] = $val['option_sku'];
                        $productdata[$i]['status'] = $val['status'];

                        if ($action == 'price') {
                            $productdata[$i][$action] = $val['option_price'];
                        } elseif ($action == 'qty') {
                            $productdata[$i][$action] = $val['option_qty'];
                        } elseif ($action == 'title') {
                            $productdata[$i][$action] = $val['option_title'];
                        } elseif ($action == 'upc') {
                            $productdata[$i][$action] = $val['option_unique_id'];
                        }

                        $product_info = Data::sqlRecords("SELECT `option_prices` FROM `newegg_can_product_variants` WHERE `option_id` = '" . $val['option_id'] . "' AND `merchant_id`='" . $merchant_id . "'", 'one');

                        if ($action == 'price' && (!empty($product_info['option_prices']) && $product_info['option_prices'] > 0)) {
                            $productdata[$i][$action] = $product_info['option_prices'];
                        } elseif ($action == 'title' && (!empty($product_price['product_title']))) {
                            $productdata[$i][$action] = $product_price['product_title'];
                        }

                        if ($action == 'upc') {
                            $productdata[$i]['mpn'] = $val['option_mpn'];
                            $productdata[$i]['id'] = $val['product_id'];
                        }

                        $i++;
                    }
                }
            }
        }

        if(empty($productdata)){
            Yii::$app->session->setFlash('error', "No Product found...");
            return $this->render('index');
        }

        foreach ($productdata as $v) {
            $row = array();
            $row[] = $v['id'];
            $row[] = $v['option_id'];
            $row[] = $v['sku'];
            $row[] = $v['type'];
            $row[] = $v[$action];
            if ($action == 'upc') {
                $row[] = $v['mpn'];
            }
            $row[] = $v['status'];

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
        $action = $_POST['import'];

        if (empty($action)) {
            Yii::$app->session->setFlash('error', "Please select an option to import CSV");

            return $this->redirect('index');
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

            if (!file_exists(Yii::getAlias('@webroot') . '/var/newegg_canada/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action)) {
                mkdir(Yii::getAlias('@webroot') . '/var/newegg_canada/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action, 0775, true);
            }

            $target = Yii::getAlias('@webroot') . '/var/newegg_canada/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action . '/' . $newname . '-' . time();
            $row = 0;
            $flag = false;
            $row1 = 0;
            if (!file_exists($target)) {
                move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
            }

            $column = '';
            if ($action == 'price') {
                $column = self::PRICE;
            } elseif ($action == 'qty') {
                $column = self::QTY;
            } elseif ($action == 'upc') {
                $column = self::BARCODE;
            }

            $selectedProducts = array();
            $import_errors = array();
            if (($handle = fopen($target, "r"))) {

                $row = 0;
                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {
                    if ($row == 0 && (trim($data[0]) != 'Id' || trim($data[1]) != 'OptionId' || trim($data[2]) != 'Sku' || trim($data[3]) != 'Type' || trim($data[4]) != $column)) {

                        $flag = true;
                        $import_errors[$row] = 'Error : Invalid file. Please choose a valid file ';
                        break;
                    }

                    $num = count($data);
                    $row++;
                    if ($row == 1)
                        continue;

                    $pro_id = trim($data[0]);
                    $pro_option_id = trim($data[1]);
                    $pro_sku = trim($data[2]);
                    $pro_type = trim($data[3]);
                    $pro_price = trim($data[4]);
                    if ($action == 'upc') {
                        $pro_mpn = trim($data[5]);
                    }

                    if ($pro_id == '' || $pro_sku == '' || $pro_type == '' || $pro_price == '' || $pro_option_id == '') {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid data.';
                        continue;
                    }

                    if ($action != 'upc') {

                        if (!is_numeric($pro_id) || !is_numeric($pro_price)) {
                            $import_errors[$row] = 'Row ' . $row . ' : Invalid product_id / ' . $action;
                            continue;
                        }

                        /*if (!in_array($pro_sku, $allpublishedSku)) {
                            $import_errors[$row] = 'Row ' . $row . ' : ' . 'Sku => "' . $pro_sku . '" is invalid/not published on walmart.';
                            continue;
                        }*/
                    }

                    $productData = array();
                    $productData['id'] = $pro_id;
                    $productData['option_id'] = $pro_option_id;
                    $productData['sku'] = $pro_sku;
                    $productData['type'] = $pro_type;
                    $productData[$action] = $pro_price;
                    if ($action == 'upc') {
                        $productData['mpn'] = $pro_mpn;
                    }

                    $productData['currency'] = CURRENCY;

                    $selectedProducts[] = $productData;
                }
                if (count($selectedProducts)) {
                    $neweggConfig = Data::sqlRecords("SELECT `seller_id`,`secret_key` FROM `newegg_configuration` WHERE merchant_id='" . $merchant_id . "'", 'one');
                    if ($neweggConfig) {

                        $session = Yii::$app->session;

                        if ($action == 'price') {

                            $Productcount = count($selectedProducts);

                            $size_of_request = 5000;//Number of products to be updated at once(in single feed)
                            $pages = (int)(ceil($Productcount / $size_of_request));

                            $max_feed_allowed_per_hour = 10;//We can only send 10 feeds per hour.
                            if ($pages > $max_feed_allowed_per_hour) {
                                $size_of_request = (int)(ceil($Productcount / $max_feed_allowed_per_hour));
                                if ($size_of_request > 5000) {
                                    Yii::$app->session->setFlash('error', "MAX Limit Exceeded.");
                                    return $this->redirect(['index']);
                                }
                                $pages = (int)(ceil($Productcount / $size_of_request));
                            }

                            $Products = array_chunk($selectedProducts, $size_of_request);

                            $session->set('priceupdate', $Products);

                            /*return $this->render('priceupdate', [

                                'totalcount' => count($selectedProducts),
                                'pages' => $pages,
                                'products' => json_encode($selectedProducts)
                            ]);*/
                            return $this->render('priceupdate', [
                                'totalcount' => $Productcount,
                                'pages' => $pages
                            ]);
                        } elseif ($action == 'qty') {

                            $Productcount = count($selectedProducts);

                            $size_of_request = 5000;//Number of products to be updated at once(in single feed)
                            $pages = (int)(ceil($Productcount / $size_of_request));

                            $max_feed_allowed_per_hour = 10;//We can only send 10 feeds per hour.
                            if ($pages > $max_feed_allowed_per_hour) {
                                $size_of_request = (int)(ceil($Productcount / $max_feed_allowed_per_hour));
                                if ($size_of_request > 5000) {
                                    Yii::$app->session->setFlash('error', "MAX Limit Exceeded.");
                                    return $this->redirect(['index']);
                                }
                                $pages = (int)(ceil($Productcount / $size_of_request));
                            }

                            $Products = array_chunk($selectedProducts, $size_of_request);

                            $session->set('inventoryupdate', $Products);

                            return $this->render('inventoryupdate', [
                                'totalcount' => $Productcount,
                                'pages' => $pages
                            ]);

                        } elseif ($action == 'upc') {

                            $size_of_request = 10;//Number of products to be uploaded at once(in single feed)
                            $pages = (int)(ceil(count($selectedProducts) / $size_of_request));

                            return $this->render('barcodeupdate', [
                                'totalcount' => count($selectedProducts),
                                'pages' => $pages,
                                'products' => json_encode($selectedProducts)
                            ]);

                        }

                    } else {
                        Yii::$app->session->setFlash('warning', "Please enter walmartapi...");
                    }

                    if (count($import_errors)) {
                        Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
                    }
                } else {
                    if (count($import_errors)) {
                        Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
                    } else {
                        Yii::$app->session->setFlash('error', "None of your product(s) are published in Walmart from csv....");
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

    public function actionPriceupdate()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        $session = Yii::$app->session;

        $returnarr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['priceupdate'][$index]) ? $session['priceupdate'][$index] : [];

        $response = ProductPrice::updatepriceviacsv($selectedProducts);
        $res = json_decode($response, true);

        if (isset($res['IsSuccess']) && isset($res['ResponseBody']['ResponseList'])) {
            foreach ($res['ResponseBody']['ResponseList'] as $feed) {
                if ($feed['RequestStatus'] == 'SUBMITTED') {
                    $feedId[] = $feed['RequestId'];

                    $prod = array_chunk($selectedProducts, 100);

                    foreach ($prod as $value) {
                        $id = [];
                        $option_id = [];
                        $when_price = '';
                        $when_option_price = '';
                        foreach ($value as $productsvalue) {

                            if ($productsvalue['type'] == "No Variants" /*|| $productsvalue['type'] == "Parent"*/) {
                                $id[] = $productsvalue['id'];

                                $when_price .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['price'] . '"';

                            } else {
                                if ($productsvalue['type'] == 'Parent') {
                                    $id[] = $productsvalue['id'];

                                    $when_price .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['price'] . '"';
                                }
                                $option_id[] = $productsvalue['option_id'];

                                $when_option_price .= ' WHEN ' . $productsvalue['option_id'] . ' THEN ' . '"' . $productsvalue['price'] . '"';
                            }
                        }
                        $ids = implode(',', $id);
                        $option_ids = implode(',', $option_id);

                        if (!empty($ids)) {
                            $query1 = "UPDATE `newegg_can_product` SET  
                                    `product_price` = CASE `product_id`
                                    " . $when_price . " 
                                END
                                WHERE product_id IN (" . $ids . ") AND merchant_id =".MERCHANT_ID ."";

                            Data::sqlRecords($query1, null, 'update');
                        }

                        if (!empty($option_ids)) {
                            $query2 = "UPDATE `newegg_can_product_variants` SET  
                                    `option_prices` = CASE `option_id`
                                    " . $when_option_price . " 
                                END
                                WHERE option_id IN (" . $option_ids . ") AND merchant_id =".MERCHANT_ID ."";
                            Data::sqlRecords($query2, null, 'update');
                        }
                    }
                    $returnarr['success'] = "Product Information has been updated successfully";
                    $returnarr['count'] = count($selectedProducts);
                }
            }

        } elseif (isset($res['Code'])) {
            $returnarr['error'] = $res['Message'];
        } else {
            $returnarr['error'] = "Price Feed of Products is not submitted due to some error.";
        }
        return json_encode($returnarr);
    }

    public function actionInventoryupdate()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        $session = Yii::$app->session;

        $returnarr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['inventoryupdate'][$index]) ? $session['inventoryupdate'][$index] : [];

        $response = ProductInventory::updateinventoryviacsv($selectedProducts);

        $res = json_decode($response, true);

        if (isset($res['IsSuccess']) && isset($res['ResponseBody']['ResponseList'])) {
            foreach ($res['ResponseBody']['ResponseList'] as $feed) {
                if ($feed['RequestStatus'] == 'SUBMITTED') {
                    $feedId[] = $feed['RequestId'];

                    $prod = array_chunk($selectedProducts,100);

                    foreach ($prod as $value)
                    {
                        $id = [];
                        $option_id = [];
                        $when_inventory = '';
                        $when_option_inventory = '';

                        foreach ($value as $productsvalue) {
                            if ($productsvalue['type'] == "No Variants" /*|| $productsvalue['type'] == "Parent"*/) {
                                $id[] = $productsvalue['id'];

                                $when_inventory .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['qty'] . '"';

                            } else {
                                if ($productsvalue['type'] == 'Parent') {
                                    $id[] = $productsvalue['id'];

                                    $when_inventory .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['qty'] . '"';
                                }
                                $option_id[] = $productsvalue['option_id'];

                                $when_option_inventory .= ' WHEN ' . $productsvalue['option_id'] . ' THEN ' . '"' . $productsvalue['qty'] . '"';
                            }
                        }
                        $ids = implode(',', $id);
                        $option_ids = implode(',', $option_id);

                        if (!empty($ids)) {
                            $query1 = "UPDATE `jet_product` SET  
                                    `qty` = CASE `id`
                                    " . $when_inventory . " 
                                END
                                WHERE id IN (" . $ids . ")";
                            Data::sqlRecords($query1, null, 'update');
                        }

                        if (!empty($option_ids)) {
                            $query2 = "UPDATE `jet_product_variants` SET  
                                    `option_qty` = CASE `option_id`
                                    " . $when_option_inventory . " 
                                END
                                WHERE option_id IN (" . $option_ids . ")";

                            Data::sqlRecords($query2, null, 'update');
                        }
                    }
                    $returnarr['success'] = "Product Information has been updated successfully";
                    $returnarr['count'] = count($selectedProducts);
                }
            }

        } elseif (isset($res['Code'])) {
            $returnarr['error'] = $res['Message'];
        } else {
            $returnarr['error'] = "Price Feed of Products is not submitted due to some error.";
        }
        return json_encode($returnarr);
    }

    public function actionBarcodeupdate()
    {
        $session = Yii::$app->session;
        $products = Yii::$app->request->post();
        $valid_product = [];
        $invalid_product = [];

        if (is_array($products) && count($products) > 0) {
            foreach ($products['products'] as $product) {

                $upc = $product['upc'];
                $flag = true;
                $type = $product['type'];
                $merchant_id = MERCHANT_ID;

                $validUpc = productInfo::validateProductBarcode($upc, $product['option_id'], $merchant_id);
                if (!$validUpc) {
                    $message = "Duplicate Barcode.";
                    $flag = false;
                } /*else {
                    if (!Data::validateUpc($product['upc'])) {
                        $message = "Invalid barcode.";
                        $flag = false;
                    }
                }*/

                if ($flag) {
                    $valid_product[] = $product;
                } else {
                    $invalid_product[] = $product['sku'];
                }

            }
        }
        if (count($invalid_product) > 0) {
            $return_msg['error'] = json_encode($invalid_product);
        }

        if (count($valid_product) > 0) {

            $when_barcode = '';
            $when_option_barcode = '';
            $when_mpn = '';
            $when_option_mpn = '';
            $id = [];
            $option_id = [];
            foreach ($valid_product as $product) {

                if ($product['type'] == 'No Variants') {
                    $id[] = $product['id'];

                    $when_barcode .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . $product['upc'] . '"';
                    $when_mpn .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . $product['mpn'] . '"';

                } else/*if ($product['type'] == 'Variants') */ {
                    if ($product['type'] == 'Parent') {
                        $id[] = $product['id'];

                        $when_barcode .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . $product['upc'] . '"';
                        $when_mpn .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . $product['upc'] . '"';

                    }
                    $option_id[] = $product['option_id'];

                    $when_option_barcode .= ' WHEN ' . $product['option_id'] . ' THEN ' . '"' . $product['upc'] . '"';
                    $when_option_mpn .= ' WHEN ' . $product['option_id'] . ' THEN ' . '"' . $product['mpn'] . '"';
                }

            }

            $ids = implode(',', $id);
            $option_ids = implode(',', $option_id);
            try {
                if (!empty($ids)) {
                    $query1 = "UPDATE `jet_product` SET  
                                    `upc` = CASE `id`
                                    " . $when_barcode . " 
                                END,
                                    `mpn` = CASE `id`
                                    " . $when_mpn . "
                                END
                                WHERE id IN (" . $ids . ")";
//                    print_r($query1);
                    Data::sqlRecords($query1, null, 'update');
                }

                if (!empty($option_ids)) {
                    $query2 = "UPDATE `jet_product_variants` SET  
                                    `option_unique_id` = CASE `option_id`
                                    " . $when_option_barcode . " 
                                END,
                                    `option_mpn` = CASE `option_id`
                                    " . $when_option_mpn . "
                                END
                                WHERE option_id IN (" . $option_ids . ")";
//                    print_r($query2);die;
                    Data::sqlRecords($query2, null, 'update');
                }

                $return_msg['success']['message'] = "Product(s) barcode successfully updated";
                $return_msg['success']['count'] = count($valid_product);
            } catch (Exception $e) {

                $return_msg['error'] = 'Invalid Data';
                //$return_msg['error'] = $e->getMessage();
            }
        }

        return json_encode($return_msg);

    }

    /*public function actionReadcsv()
    {

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $action = $_POST['import'];

        if (empty($action)) {
            Yii::$app->session->setFlash('error', "Please select an option to import CSV");

            return $this->redirect('index');
        }
        $merchant_id = Yii::$app->user->identity->id;

        if (isset($_FILES['csvfile']['name'])) {
            $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'text/comma-separated-values', 'application/octet-stream');
            if (!in_array($_FILES['csvfile']['type'], $mimes)) {
                Yii::$app->session->setFlash('error', "Invalid file type. Please import only CSV file");
                return $this->redirect(['index']);
            }

            $newname = $_FILES['csvfile']['name'];

            if (!file_exists(Yii::getAlias('@webroot') . '/var/newegg/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action)) {
                mkdir(Yii::getAlias('@webroot') . '/var/newegg/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action, 0775, true);
            }

            $target = Yii::getAlias('@webroot') . '/var/newegg/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action . '/' . $newname . '-' . time();
            $row = 0;
            $flag = false;
            $row1 = 0;
            if (!file_exists($target)) {
                move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
            }

            $column = '';
            if ($action == 'price') {
                $column = self::PRICE;
            } elseif ($action == 'qty') {
                $column = self::QTY;
            } elseif ($action == 'upc') {
                $column = self::BARCODE;
            }

            $selectedProducts = array();
            $import_errors = array();
            if (($handle = fopen($target, "r"))) {

                $row = 0;
                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {
                    if ($row == 0 && (trim($data[0]) != 'Sku')) {

                        $flag = true;
                        $import_errors[$row] = 'Error : Invalid file. Please choose a valid file ';
                        break;
                    }

                    $num = count($data);
                    $row++;
                    if ($row == 1)
                        continue;

                    $pro_sku = trim($data[0]);
                    $pro_mpn = trim($data[1]);


                    if ($pro_sku == '') {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid data.';
                        continue;
                    }


                    $productData = array();

                    $productData['sku'] = $pro_sku;
                    $productData['mpn'] = $pro_mpn;

                    $selectedProducts[] = $productData;
                }
                if (count($selectedProducts)) {
                    $neweggConfig = Data::sqlRecords("SELECT `seller_id`,`secret_key` FROM `newegg_configuration` WHERE merchant_id='" . $merchant_id . "'", 'one');
                    if ($neweggConfig) {

                        $session = Yii::$app->session;

                        if ($action == 'upc') {

                            $size_of_request = 10;//Number of products to be uploaded at once(in single feed)
                            $pages = (int)(ceil(count($selectedProducts) / $size_of_request));

                            foreach ($selectedProducts as $prod){
                                $sku = $prod['sku'];
                                $mpn = $prod['mpn'];


                                $main_product = Data::sqlRecords('SELECT `id`,`type` FROM `jet_product` WHERE `merchant_id`=1206 AND `sku` = "'.$sku .'"','one');

                                if(!empty($main_product['id']) && $main_product['type'] == 'simple'){

                                    //var_dump($main_product);
                                    Data::sqlRecords('UPDATE `jet_product` SET `mpn`= "'.$prod['mpn.'].'" WHERE `merchant_id` = 1206 AND `id` = "'.$main_product['id'].'"',null,'update');

                                }else{

                                    $main_product_variants = Data::sqlRecords('SELECT `id`,`type` FROM `jet_product` WHERE `merchant_id`="1206" AND `sku` = "'.$sku .'"','one');

                                    if(!empty($main_product_variants['id'])){

                                        //print_r($main_product_variants);

                                        Data::sqlRecords('UPDATE `jet_product` SET `mpn`= "'.$mpn.'" WHERE `merchant_id` = 1206 AND `id` = "'.$main_product['id'].'"',null,'update');
                                    }

                                    $variants_product_variants = Data::sqlRecords('SELECT `option_id`, `product_id` FROM `jet_product_variants` WHERE `merchant_id`="1206" AND `option_sku` = "'.$sku .'"','one');

                                    if(!empty($variants_product_variants)){

                                    // var_dump($variants_product_variants);


                                        Data::sqlRecords('UPDATE `jet_product_variants` SET `option_mpn`= "'.$mpn.'" WHERE `merchant_id` = 1206 AND `option_id` = "'.$variants_product_variants['option_id'].'"',null,'update');
                                    }

                                }


                            }

                            die;

                        }

                    }
                }
            }
        }

        die('fdghfdg');
    }*/

}

