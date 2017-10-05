<?php
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\WalmartProduct;
use frontend\modules\walmart\models\JetProduct;
use frontend\modules\walmart\components\Inventory\InventoryUpdate;
use frontend\modules\walmart\models\WalmartProduct as WalmartProductModel;
use Yii;
use yii\web\Response;

class ProductcsvController extends WalmartmainController
{
    protected $walmartHelper;

    const PRICE = 'Price';
    const QTY = 'Qty';
    const TITLE = 'Title';
    const BARCODE = 'Barcode';

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        return $this->render('index');
    }

    public function actionExportproduct()
    {
        $merchant_id = Yii::$app->user->identity->id;

        if (isset($_GET['status'])) {
            $base_path = \Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $_GET['status'] . '/' . $_GET['status'] . '.csv';
            if (file_exists($base_path)) {

                $file = fopen($base_path, "r");

                $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
                $content = $encode . file_get_contents($base_path);
                \Yii::$app->response->sendFile($base_path);
                return $this->render('index');

            } else {
                return $this->render('index');

            }
        }else{
            $action = $_POST['export'];

            $base_path = \Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $action ;

            if (file_exists($base_path)) {
                unlink(\Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $action .'/'.$action.'.csv');
                rmdir($base_path);
            }
        }
        $status = $_POST['status'];

        $action = $_POST['export'];
        if (empty($action)) {
            Yii::$app->session->setFlash('error', "Please select an option to export CSV");

            return $this->redirect('index');
        }

        /*if ($status && $status == 'all') {
            $query = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple' ) UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=" . $merchant_id . " AND `walmart_product`.`category` != '')) as `merged_data`";
        } else {
            $query = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple' AND `walmart_product`.`status`='" . $status . "') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=" . $merchant_id . " AND `walmart_product`.`category` != ''AND `walmart_product_variants`.`status`='" . $status . "')) as `merged_data`";
        }*/
        if ($status && $status == 'all') {
            $query = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " )) as `merged_data`";
        } else {
            $query = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " AND `walmart_product`.`status`='" . $status . "')) as `merged_data`";
        }
        $product = Data::sqlRecords($query, "one", "select");


        if (is_array($product) && isset($product['count']) && $product['count'] > 1) {
            $pages = ceil(intval($product['count']) / 100);
            $session = Yii::$app->session;
            $session->set('action', $action);
            $session->set('status', $status);
            $session->set('product_page', $pages);

            return $this->render('exportproduct',
                [
                    'totalcount' => $product['count'],
                    'pages' => $pages,
                    'action' => $action
                ]
            );

        } else {
            echo "No Products Found.";
        }

    }

    public function actionExport()
    {
        $getItemsCount = 100;
        $finish = false;
        $merchant_id = Yii::$app->user->identity->id;
        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index');
        $offset = $index * $getItemsCount;
        $limit = $getItemsCount;

        $action = $session->get('action');
        $status = $session->get('status');
        if (empty($action)) {
            Yii::$app->session->setFlash('error', "Please select an option to export CSV");

            return $this->redirect('index');
        }

        $column = '';
        if ($action == 'price') {
            $column = 'Price';
        } elseif ($action == 'qty') {
            $column = 'Qty';
        } elseif ($action == 'upc') {
            $column = self::BARCODE;
        }

        if (!file_exists(\Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $action)) {
            mkdir(\Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $action, 0775, true);

            $headers = array('Id', 'OptionId', 'Sku', 'Type', $column, 'Status');

            $base_path = \Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $action . '/' . $action . '.csv';
            $file = fopen($base_path, "w");
            $row = array();
            foreach ($headers as $header) {
                $row[] = $header;
            }
            fputcsv($file, $row);

        } else {

            $base_path = \Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/' . $action . '/' . $action . '.csv';
            $file = fopen($base_path, "a");
            $row = array();

        }

        $productdata = array();
        $i = 0;


        if ($status && $status == 'all') {
            $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "' LIMIT $offset,$limit", 'all');
        } else {
            $model = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='" . $merchant_id . "' AND `status`='" . $status . "') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`= '" . $merchant_id . "' LIMIT $offset,$limit", 'all');
        }

        foreach ($model as $value) {
            if ($value['sku'] == "") {
                continue;
            }

            if ($value['type'] == "simple") {

                $productdata[$i]['id'] = $value['product_id'];

                if ($action == 'upc') {
                    $productdata[$i]['id'] = $value['product_id'];
                }
                $productdata[$i]['sku'] = $value['sku'];
                $productdata[$i]['type'] = "No Variants";
                $productdata[$i][$action] = $value[$action];
                $productdata[$i]['option_id'] = $value['variant_id'];
                $productdata[$i]['status'] = $value['status'];

                if ($value['product_price'] != '' && $action == 'price') {
                    $productdata[$i][$action] = $value['product_price'];
                }

                $i++;
            } else {

                if ($status && $status == 'all') {
                    $optionResult = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product_variants` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id`='" . $value['product_id'] . "') as `jpv` INNER JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id`='" . $value['product_id'] . "') as `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` WHERE `wpv`.`merchant_id`= '" . $merchant_id . "' AND `wpv`.`product_id`='" . $value['product_id'] . "'", 'all');

                } else {
                    $optionResult = Data::sqlRecords("SELECT * FROM (SELECT * FROM `jet_product_variants` WHERE `merchant_id`='" . $merchant_id . "' AND `product_id`='" . $value['product_id'] . "') as `jpv` INNER JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`='" . $merchant_id . "' AND `status`='" . $status . "' AND `product_id`='" . $value['product_id'] . "') as `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` WHERE `wpv`.`merchant_id`= '" . $merchant_id . "' AND `wpv`.`product_id`='" . $value['product_id'] . "'", 'all');
                }

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

                        $product_info = Data::sqlRecords("SELECT `option_prices` FROM `walmart_product_variants` WHERE `option_id` = '" . $val['option_id'] . "' AND `merchant_id`='" . $merchant_id . "'", 'one');

                        if ($action == 'price' && (!empty($product_info['option_prices']) && $product_info['option_prices'] > 0)) {
                            $productdata[$i][$action] = $product_info['option_prices'];
                        } elseif ($action == 'title' && (!empty($product_price['product_title']))) {
                            $productdata[$i][$action] = $product_price['product_title'];
                        }

                        $i++;
                    }
                }
            }
        }

        foreach ($productdata as $v) {
            $row = array();
            $row[] = $v['id'];
            $row[] = $v['option_id'];
            $row[] = $v['sku'];
            $row[] = $v['type'];
            $row[] = $v[$action];
            $row[] = $v['status'];

            fputcsv($file, $row);
        }

        fclose($file);

        $returnarr['success'] = "Product Information has been updated successfully";
        $returnarr['count'] = count($productdata);

        return json_encode($returnarr);
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

            if (!file_exists(Yii::getAlias('@webroot') . '/var/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action)) {
                mkdir(Yii::getAlias('@webroot') . '/var/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action, 0775, true);
            }

            $target = Yii::getAlias('@webroot') . '/var/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $action . '/' . $newname . '-' . time();
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
            } elseif ($action == 'title') {
                $column = self::TITLE;
            } elseif ($action == 'upc') {
                $column = self::BARCODE;
            }

            $selectedProducts = array();
            $import_errors = array();
            if (($handle = fopen($target, "r"))) {
                /*$status = WalmartProductModel::PRODUCT_STATUS_UPLOADED;
                $allpublishedSku = WalmartProduct::getAllProductSku($merchant_id, $status);*/


                $status = WalmartProductModel::PRODUCT_STATUS_UPLOADED;
                $stage = WalmartProductModel::PRODUCT_STATUS_STAGE;
                $unpublished = WalmartProductModel::PRODUCT_STATUS_UNPUBLISHED;
                $allpublishedSku = WalmartProduct::getAllProductSku($merchant_id, $status);

                if ($action == 'qty' || $action == 'price') {

                    $allstageSku = WalmartProduct::getAllProductSku($merchant_id, $stage);
                    $allunpublishedSku = WalmartProduct::getAllProductSku($merchant_id, $unpublished);
                    $allpublishedSku = array_merge($allpublishedSku, $allunpublishedSku, $allstageSku);
                }

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

                    if ($pro_id == '' || $pro_sku == '' || $pro_type == '' || $pro_price == '' || $pro_option_id == '') {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid data.';
                        continue;
                    }

                    if ($action != 'upc') {

                        if (!is_numeric($pro_id) || !is_numeric($pro_price)) {
                            $import_errors[$row] = 'Row ' . $row . ' : Invalid product_id / ' . $action;
                            continue;
                        }

                        if (!in_array($pro_sku, $allpublishedSku)) {
                            $import_errors[$row] = 'Row ' . $row . ' : ' . 'Sku => "' . $pro_sku . '" is invalid/not published on walmart.';
                            continue;
                        }
                    }

                    $productData = array();
                    $productData['id'] = $pro_id;
                    $productData['option_id'] = $pro_option_id;
                    $productData['sku'] = $pro_sku;
                    $productData['type'] = $pro_type;
                    $productData[$action] = $pro_price;

                    $productData['currency'] = CURRENCY;

                    $selectedProducts[] = $productData;
                }
                if (count($selectedProducts)) {
                    $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key` FROM `walmart_configuration` WHERE merchant_id='" . $merchant_id . "'", 'one');
                    if ($walmartConfig) {
                        $this->walmartHelper = new WalmartProduct($walmartConfig['consumer_id'], $walmartConfig['secret_key']);

                        $session = Yii::$app->session;

                        /*$priceUploadCountPerRequest = 1000;
                        $selectedProducts = array_chunk($selectedProducts, $priceUploadCountPerRequest);*/

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

        $returnArr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['priceupdate'][$index]) ? $session['priceupdate'][$index] : [];
        //$count = count($selectedProducts);

        //        $products = Yii::$app->request->post();

        $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key` FROM `walmart_configuration` WHERE merchant_id='" . MERCHANT_ID . "'", 'one');
        if ($walmartConfig) {
            $this->walmartHelper = new WalmartProduct($walmartConfig['consumer_id'], $walmartConfig['secret_key']);
            /*$response = $this->walmartHelper->updatePriceviaCsv($products['products']);*/
            $response = $this->walmartHelper->updatePriceviaCsv($selectedProducts);
            $response['action'] = 'Price';

            if (isset($response['errors'])) {
                if (isset($response['errors']['error'])) {
                    /*Yii::$app->session->setFlash('warning', $response['errors']['error']['code']);*/
                    $returnarr['error'] = $response['errors']['error']['code'];
                } else {
                    /*Yii::$app->session->setFlash('warning', $response['action'] . " of Products is not updated due to some error.");*/
                    $returnarr['error'] = "Price of Products is not updated due to some error.";
                }
            } elseif (isset($response['error'])) {
                if (isset($response['error'][0]['code'])) {
                    $returnarr['error'] = $response['error'][0]['code'];
                } else {
                    $returnarr['error'] = "Price of Products is not updated due to unknown error.";
                }
            } elseif (isset($response['feedId'])) {
                $returnarr['success'] = "Product Information has been updated successfully";
                $returnarr['count'] = $response['count'];
            } else {
                $returnarr['error'] = "Products is not updated.";
            }
            return json_encode($returnarr);


        }
    }

    public function actionInventoryupdate()
    {
        //$products = Yii::$app->request->post();
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        $session = Yii::$app->session;


        //$returnArr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['inventoryupdate'][$index]) ? $session['inventoryupdate'][$index] : [];
        //$count = count($selectedProducts);
        $returnarr = [];
        $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key` FROM `walmart_configuration` WHERE merchant_id='" . MERCHANT_ID . "'", 'one');
        if ($walmartConfig) {
            $inventoryupdate = new InventoryUpdate($walmartConfig['consumer_id'], $walmartConfig['secret_key']);
            $response = $inventoryupdate->updateInventoryViaCsv($selectedProducts);
            if (isset($response['errors'])) {
                if (isset($response['errors']['error'])) {
                    /*Yii::$app->session->setFlash('warning', $response['errors']['error']['code']);*/
                    $returnarr['error'] = $response['errors']['error']['code'];
                } else {
                    /*Yii::$app->session->setFlash('warning', $response['action'] . " of Products is not updated due to some error.");*/
                    $returnarr['error'] = "Inventory of Products is not updated due to some error.";
                }
            } elseif (isset($response['error'])) {
                if (isset($response['error'][0]['code'])) {
                    $returnarr['error'] = $response['error'][0]['code'];
                } else {
                    $returnarr['error'] = "Inventory of Products is not updated due to unknown error.";
                }
            } elseif (isset($response['feedId'])) {
                $returnarr['success'] = "Product Information has been updated successfully";
                $returnarr['count'] = $response['count'];
            } else {
                $returnarr['error'] = "Products is not updated.";
            }

            return json_encode($returnarr);
        }
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

                $validUpc = Jetproductinfo::validateProductBarcode($upc, $product['option_id'], $merchant_id);
                if (!$validUpc) {
                    $message = "Duplicate Barcode.";
                    $flag = false;
                } else {
                    if (!Data::validateUpc($product['upc'])) {
                        $message = "Invalid barcode.";
                        $flag = false;
                    }
                }

                $valid_product[] = $product;

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
            $id = [];
            $option_id = [];
            foreach ($valid_product as $product) {

                /*if(strlen($product['upc'])==11){
                    $product['upc'] = '0'.$product['upc'];

                }*/

                if ($product['type'] == 'No Variants') {
                    $id[] = $product['id'];

                    $when_barcode .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . $product['upc'] . '"';

                } else/*if ($product['type'] == 'Variants') */ {
                    if ($product['type'] == 'Parent') {
                        $id[] = $product['id'];

                        $when_barcode .= ' WHEN ' . $product['id'] . ' THEN ' . '"' . $product['upc'] . '"';

                    }
                    $option_id[] = $product['option_id'];

                    $when_option_barcode .= ' WHEN ' . $product['option_id'] . ' THEN ' . '"' . $product['upc'] . '"';
                }

            }

            $ids = implode(',', $id);
            $option_ids = implode(',', $option_id);
            try {
                if (!empty($ids)) {
                    $query1 = "UPDATE `jet_product` SET  
                                    `upc` = CASE `id`
                                    " . $when_barcode . " 
                                END
                                WHERE id IN (" . $ids . ")";

                    Data::sqlRecords($query1, null, 'update');
                }

                if (!empty($option_ids)) {
                    $query2 = "UPDATE `jet_product_variants` SET  
                                    `option_unique_id` = CASE `option_id`
                                    " . $when_option_barcode . " 
                                END
                                WHERE option_id IN (" . $option_ids . ")";

                    Data::sqlRecords($query2, null, 'update');
                }

                $return_msg['success']['message'] = "Product(s) barcode successfully updated";
                $return_msg['success']['count'] = count($valid_product);
            } catch (Exception $e) {
                $return_msg['error'] = $e->getMessage();
            }
        }


        return json_encode($return_msg);

    }

}
