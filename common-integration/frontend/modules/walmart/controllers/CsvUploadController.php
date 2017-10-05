<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 14/9/17
 * Time: 1:28 PM
 */
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Skuproductupload;
use frontend\modules\walmart\components\WalmartProductValidate;
use frontend\modules\walmart\components\WalmartRepricing;
use Yii;
use yii\filters\VerbFilter;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Category;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartProductSearch;
use frontend\modules\walmart\models\WalmartProductVariants;

class CsvUploadController extends WalmartmainController
{

    const SIZE_OF_REQUEST = 1000;
    const UPLOAD_SIZE = 250;

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return $this->render('index');
    }

    /** Export Csv Using Php buffer
     * @return Csv File
     */
    public function actionExportproduct()
    {
        $headers = array('sku');
        // Open the output stream
        $fh = fopen('php://output', 'w');
        // Start output buffering (to capture stream contents)
        ob_start();
        // Loop over the * to export
        $row = array();
        foreach ($headers as $header) {
            $row[] = $header;
        }
        fputcsv($fh, $row);
        // Get the contents of the output buffer
        $string = ob_get_clean();
        $filename = 'productupload_' . date('Ymd') . '_' . date('His');

        // Output CSV-specific headers
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename.csv\";");
        header("Content-Transfer-Encoding: binary");
        fclose($fh);
        exit($string);

    }

    public function actionReadCsv()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;

        if (!isset($_FILES['csvfile']['name']) || empty($_FILES['csvfile']['name'])) {
            Yii::$app->session->setFlash('error', "Please Upload Csv file....");
            return $this->redirect(['index']);
        }

        $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'text/comma-separated-values', 'application/octet-stream');
        if (!in_array($_FILES['csvfile']['type'], $mimes)) {
            Yii::$app->session->setFlash('error', "Invalid file type. Please import only CSV file");
            return $this->redirect(['index']);
        }

        $newname = $_FILES['csvfile']['name'];

        if (!file_exists(Yii::getAlias('@webroot') . '/var/walmart/csv-upload/' . $merchant_id . '/'  . date('d-m-Y'))) {
            mkdir(Yii::getAlias('@webroot') . '/var/walmart/csv-upload/' . $merchant_id .'/'. date('d-m-Y') , 0775, true);
        }

        $file_path = Yii::getAlias('@webroot') . '/var/walmart/csv-upload/' . $merchant_id .'/' . date('d-m-Y') . '/' . $merchant_id . '.csv';

        $csv_data_file = Yii::getAlias('@webroot') . '/var/walmart/csv-upload/' . $merchant_id .'/' . date('d-m-Y') . '/data.php';

        if(file_exists($csv_data_file))
        {
            unlink($csv_data_file);
        }
        if(file_exists($file_path))
        {
            unlink($file_path);
        }

        if (!file_exists($file_path)) {
            move_uploaded_file($_FILES['csvfile']['tmp_name'], $file_path);
        }

        if (file_exists($file_path)) {
            $itemCount = WalmartRepricing::getRowsInCsv($file_path);

            if ($itemCount) {
                $size_of_request = self::SIZE_OF_REQUEST;

                $pages = (int)(ceil($itemCount / $size_of_request));

                return $this->render('csv_count', [
                    'total_products' => $itemCount,
                    'pages' => $pages,
                    'csvFilePath' => $file_path,
                    'csvDataPath' => $csv_data_file
                ]);
            } else {
                Yii::$app->session->setFlash('error', 'No data found in CSV File.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'CSV File not found.');
        }
        return $this->redirect(['index']);
    }

    public function actionCountProduct()
    {
        $returnArr = [];

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index', false);
        $isLastPage = Yii::$app->request->post('isLast');

        $merchant_id = Yii::$app->user->identity->id;
        $csvFilePath = Yii::$app->request->post('csvFilePath', false);
        $csv_data_file = Yii::$app->request->post('csvDataFile', false);

        /*if (!file_exists(Yii::getAlias('@webroot') . '/var/' . $merchant_id)) {
            mkdir(Yii::getAlias('@webroot') . '/var/' . $merchant_id, 0775, true);
        }

        $csv_data_file = Yii::getAlias('@webroot') . '/var/' . $merchant_id . '/data.php';*/

        $csvData = self::readItemCsv($csvFilePath, self::SIZE_OF_REQUEST, $index, $csv_data_file);

        $content = '<?php return ' . var_export($csvData, true) . ';';
        $handle = fopen($csv_data_file, 'w');
        fwrite($handle, $content);
        fclose($handle);

        /*$createProduct = new Skuproductupload(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

        $productResponse = $createProduct->createProduct($csvData, $merchant_id);

        if (isset($productResponse['feedId'])) {
            //save product status and data feed
            foreach ($productResponse['uploadIds'] as $val) {
                $query = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', `error`='' WHERE `product_id`='{$val}'";
                Data::sqlRecords($query, null, "update");
            }

            $ids = implode(',', $productResponse['uploadIds']);
            $feed_file = $productResponse['feed_file'];
            $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('{$merchant_id}', '{$productResponse['feedId']}', '{$ids}', '{$feed_file}')";
            Data::sqlRecords($query, null, "insert");

            $msg = "product feed successfully submitted on walmart.";
            $feed_count = count($productResponse['uploadIds']);
            $feedId = $productResponse['feedId'];
            $returnArr = ['success' => true, 'success_msg' => $msg, 'success_count' => $feed_count, 'feed_id' => $feedId];
        }

        //save errors in database for each errored product
        if (isset($productResponse['errors'])) {
            if (is_array($productResponse['errors'])) {
                if (count($productResponse['errors'])) {
                    $_feedError = null;
                    if (isset($productResponse['errors']['feedError'])) {
                        $_feedError = $productResponse['errors']['feedError'];
                        unset($productResponse['errors']['feedError']);
                    }

                    $uploadErrors = [];
                    foreach ($productResponse['errors'] as $productSku => $error) {
                        if (is_array($error)) {
                            //Simple Product with xml validation error
                            if (isset($error['error'], $error['xml_validation_error'])) {
                                $errorMsg = $error['error'];
                            } //Variant product error
                            else {
                                $errorMsg = '';
                                foreach ($error as $variantSku => $variantError) {
                                    //Variant product with xml validation error
                                    if (is_array($variantError)) {
                                        $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError['error'] . '<br/>';
                                    } //Variant product with direct error message
                                    else {
                                        $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError . '<br/>';
                                    }
                                }
                            }
                            $uploadErrors[$productSku] = $error;
                        } //Simple Product with direct error message
                        else {
                            $errorMsg = $error;
                            $uploadErrors[$productSku] = $errorMsg;
                        }

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($errorMsg) . "' WHERE jp.sku='" . $productSku . "'";
                        Data::sqlRecords($query, null, "update");
                    }

                    $returnArr['error'] = true;
                    $returnArr['error_msg'] = $uploadErrors;
                    $returnArr['error_count'] = count($productResponse['errors']);
                    $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

                    if (!is_null($_feedError)) {
                        $returnArr['feedError'] = $_feedError;
                    }
                }
            } else {
                $returnArr = ['error' => true, 'error_msg' => $productResponse['errors']];
            }
        }*/
        $returnArr = ['success' => true, 'success_msg' => 'test', 'success_count' => count($csvData), 'feed_id' => 'dfdff'];
        return json_encode($returnArr);
    }

    public function readItemCsv($csvFilePath, $limit = null, $page = 0, $csv_data_file)
    {
        $product = array();
        $csvData = array();
        if (file_exists($csvFilePath)) {
            if (($handle = fopen($csvFilePath, "r"))) {
                $row = 0;

                $start = 1 + ($page * $limit);
                $end = $limit + ($page * $limit);

                if (file_exists($csv_data_file)) {
                    $csvData = include $csv_data_file;

                }

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {

                    if ($row == 0 || $data[0] == 'Sku') {
                        $row++;
                        continue;
                    }

                    if (is_null($limit)) {
                        $pro_sku = trim($data[0]);

                        $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `sku` ='" . $pro_sku . "' ", 'one');

                        if (isset($model1) && $model1['type'] == 'simple') {

                            if (!isset($csvData[$model1['id']])) {
                                $csvData[$model1['id']] = 1;

                            }/*else{
                                $csvData[$model1['id']] = 1;

                            }*/
                        }

                        $model2 = Data::sqlRecords("SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `option_sku` ='" . $pro_sku . "' ", 'one');

                        if ($model2) {

                            if (!isset($csvData[$model2['product_id']][$model2['option_id']])) {
                                $csvData[$model2['product_id']][$model2['option_id']] = 1;

                            }
                            //$csvData[$model2['product_id']][$model2['option_id']] = 1;
                        }
                    } else {
                        if ($start <= $row && $row <= $end) {
                            $pro_sku = trim($data[0]);

                            $model1 = Data::sqlRecords("SELECT `id`,`type` FROM `jet_product` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `sku` ='" . $pro_sku . "' ", 'one');

                            if (!empty($model1) && $model1['type'] == 'simple') {

                                if (!isset($csvData[$model1['id']])) {
                                    $csvData[$model1['id']] = 1;

                                }
                            }

                            $model2 = Data::sqlRecords("SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `option_sku` ='" . $pro_sku . "' ", 'one');

                            if ($model2) {

                                if (!isset($csvData[$model2['product_id']][$model2['option_id']])) {

                                    $csvData[$model2['product_id']] = [];
                                    $csvData[$model2['product_id']][$model2['option_id']] = 1;
                                }
                            }
                        } elseif ($row > $end) {
                            break;
                        }
                    }
                    $row++;
                }
            }
        }
        return $csvData;
    }

    public function actionUploadProduct()
    {
        if (isset($_POST['csv_data_file']) && file_exists($_POST['csv_data_file'])) {
            $csvData = include $_POST['csv_data_file'];
            $Productcount = $_POST['product_count'];
            $session = Yii::$app->session;
            $selectedProducts = array_chunk($csvData, self::UPLOAD_SIZE, true);

            $session->set('selected_csv_products', $selectedProducts);
            $pages = (int)(ceil($Productcount / self::UPLOAD_SIZE));

            return $this->render('upload_csv_product', [
                'total_products' => $Productcount,
                'pages' => $pages
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'Invalid File or Empty');
        }
        return $this->redirect(['index']);

    }

    public function actionUploadCsvProduct()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $session = Yii::$app->session;

        $returnArr = [];

        $index = Yii::$app->request->post('index');
        $csvData = isset($session['selected_csv_products'][$index]) ? $session['selected_csv_products'][$index] : [];
        $count = count($csvData);

        $merchant_id = MERCHANT_ID;

        if (!$count) {
            $returnArr = ['error' => true, 'error_msg' => 'No Products to Upload'];
        } else {

            $createProduct = new Skuproductupload(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

            $productResponse = $createProduct->createProduct($csvData, $merchant_id);

            if (isset($productResponse['feedId'])) {
                //save product status and data feed
                foreach ($productResponse['uploadIds'] as $val) {
                    $query = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', `error`='' WHERE `product_id`='{$val}'";
                    Data::sqlRecords($query, null, "update");
                }

                $ids = implode(',', $productResponse['uploadIds']);
                $feed_file = $productResponse['feed_file'];
                $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('{$merchant_id}', '{$productResponse['feedId']}', '{$ids}', '{$feed_file}')";
                Data::sqlRecords($query, null, "insert");

                $msg = "product feed successfully submitted on walmart.";
                $feed_count = count($productResponse['uploadIds']);
                $feedId = $productResponse['feedId'];
                $returnArr = ['success' => true, 'success_msg' => $msg, 'success_count' => $feed_count, 'feed_id' => $feedId];
            }

            //save errors in database for each errored product
            if (isset($productResponse['errors'])) {
                if (is_array($productResponse['errors'])) {
                    if (count($productResponse['errors'])) {
                        $_feedError = null;
                        if (isset($productResponse['errors']['feedError'])) {
                            $_feedError = $productResponse['errors']['feedError'];
                            unset($productResponse['errors']['feedError']);
                        }

                        $uploadErrors = [];
                        foreach ($productResponse['errors'] as $productSku => $error) {
                            if (is_array($error)) {
                                //Simple Product with xml validation error
                                if (isset($error['error'], $error['xml_validation_error'])) {
                                    $errorMsg = $error['error'];
                                } //Variant product error
                                else {
                                    $errorMsg = '';
                                    foreach ($error as $variantSku => $variantError) {
                                        //Variant product with xml validation error
                                        if (is_array($variantError)) {
                                            $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError['error'] . '<br/>';
                                        } //Variant product with direct error message
                                        else {
                                            $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError . '<br/>';
                                        }
                                    }
                                }
                                $uploadErrors[$productSku] = $error;
                            } //Simple Product with direct error message
                            else {
                                $errorMsg = $error;
                                $uploadErrors[$productSku] = $errorMsg;
                            }

                            $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($errorMsg) . "' WHERE jp.sku='" . $productSku . "'";
                            Data::sqlRecords($query, null, "update");
                        }

                        $returnArr['error'] = true;
                        $returnArr['error_msg'] = $uploadErrors;
                        $returnArr['error_count'] = count($productResponse['errors']);
                        $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

                        if (!is_null($_feedError)) {
                            $returnArr['feedError'] = $_feedError;
                        }
                    }
                } else {
                    $returnArr = ['error' => true, 'error_msg' => $productResponse['errors']];
                }
            }
        }

        return json_encode($returnArr);
    }

}