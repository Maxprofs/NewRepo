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

    const SIZE_OF_REQUEST = 100;

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return $this->render('index');
    }

    public function actionUpload()
    {

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $import_errors = array();
        $error_array = array();
        $count = 0;
        $product = array();
        if (isset($_FILES['csvfile']['name'])) {
            $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'text/comma-separated-values', 'application/octet-stream');
            if (!in_array($_FILES['csvfile']['type'], $mimes)) {
                Yii::$app->session->setFlash('error', "Invalid file type. Please import only CSV file");
                return $this->redirect(['index']);
            }
        } else {
            Yii::$app->session->setFlash('error', "Please Upload Csv file....");
        }
//        $file_path = $_FILES['csvfile']['tmp_name'] . '/' . $_FILES['csvfile']['name'];

        $file_path = Yii::getAlias('@webroot') . '/var/Exp.csv';

        file_get_contents($file_path);

        if (file_exists($file_path)) {
            $itemCount = WalmartRepricing::getRowsInCsv($file_path);

            if ($itemCount) {
                $size_of_request = self::SIZE_OF_REQUEST;

                $pages = (int)(ceil($itemCount / $size_of_request));

                return $this->render('batch_upload', [
                    'total_products' => $itemCount,
                    'pages' => $pages,
                    'csvFilePath' => $file_path
                ]);
            } else {
                Yii::$app->session->setFlash('error', 'No data found in item report.');
            }
        }

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

    public function actionUploadProduct()
    {
        $returnArr = [];

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $import_errors = array();
        $error_array = array();
        $count = 0;
        $product = array();

        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index', false);
        $isLastPage = Yii::$app->request->post('isLast');

        $merchant_id = Yii::$app->user->identity->id;
        $csvFilePath = Yii::$app->request->post('csvFilePath', false);

        $csvData = self::readItemCsv($csvFilePath, self::SIZE_OF_REQUEST, $index);

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

        return json_encode($returnArr);

    }

    public function readItemCsv($csvFilePath, $limit = null, $page = 0)
    {
        $product = array();
        $csvData = array();
        if (file_exists($csvFilePath)) {
            if (($handle = fopen($csvFilePath, "r"))) {
                $row = 0;
                $columns = [];
                $skuIndex = '';

                $start = 1 + ($page * $limit);
                $end = $limit + ($page * $limit);

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {
                    if ($row == 0) {
                        $row++;
                        continue;
                    }

                    if (is_null($limit)) {
                        $pro_sku = trim($data[0]);

                        $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `sku` ='" . $pro_sku . "' ", 'one');

                        if ($model1) {
                            $csvData[$model1['id']] = 1;
                        }

                        $model2 = Data::sqlRecords("SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `option_sku` ='" . $pro_sku . "' ", 'one');

                        if ($model2) {
                            $csvData[$model2['product_id']][$model2['option_id']] = 1;
                        }
                    } else {
                        if ($start <= $row && $row <= $end) {
                            $pro_sku = trim($data[0]);

                            $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `sku` ='" . $pro_sku . "' ", 'one');

                            if ($model1) {
                                $csvData[$model1['id']] = 1;
                            }

                            $model2 = Data::sqlRecords("SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `option_sku` ='" . $pro_sku . "' ", 'one');

                            if ($model2) {
                                $csvData[$model2['product_id']][$model2['option_id']] = 1;
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

}