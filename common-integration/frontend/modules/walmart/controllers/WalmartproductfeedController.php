<?php

namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Walmartapi;
use Yii;
use frontend\modules\walmart\models\WalmartProductFeed;
use frontend\modules\walmart\models\WalmartProductFeedSearch;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use frontend\modules\walmart\components\ProductFeed;

/**
 * WalmartproductfeedController implements the CRUD actions for WalmartProductFeed model.
 */
class WalmartproductfeedController extends WalmartmainController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all WalmartProductFeed models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel = new WalmartProductFeedSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionBulkfeedstatus()
    {
        $pages = 1;
        $session = Yii::$app->session;

        $action = Yii::$app->request->post('action');

        if ($action == 'updatefeedstatus') {
            $feed_ids = (array)Yii::$app->request->post('selection');

            if (!empty($feed_ids)) {

                $session['feed_details'] = array('ids' => $feed_ids, 'pages' => $pages);

                return $this->render('feedupdate', [
                    'totalcount' => count($feed_ids),
                    'pages' => $pages
                ]);
            }
            Yii::$app->session->setFlash('error', "Please select the Feed Id");
            return $this->redirect('index');
        } else {
            Yii::$app->session->setFlash('error', "Please Choose Correct Action.");
            return $this->redirect('index');
        }

    }

    public function actionUpdatefeedstatus()
    {
        $session = "";
        $count = 0;
        $session = Yii::$app->session;
        $feed_details = $session['feed_details'];

        $successFeeds = [];
        $errorFeeds = [];
        $processedFeeds = [];
        foreach ($feed_details['ids'] as $feed_id) {

            $query = Data::sqlRecords('SELECT status FROM `walmart_product_feed` WHERE feed_id="' . $feed_id . '"', 'one');

            if ($query['status'] != 'PROCESSED') {

                $wal = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                $feed_data = $wal->getFeeds($feed_id);

                try {
                    if (isset($feed_data['results']) && !empty($feed_data['results'])) {
                        foreach ($feed_data['results'] as $val) {

                            if (isset($val['feedDate'])) {
                                $feed_date = '"' . date('Y-m-d H:i:s', substr($val['feedDate'], 0, 10)) . '"';
                            } else {
                                $feed_date = 'NULL';//'0000-00-00 00:00:00'
                            }
                            $model = Data::sqlRecords('UPDATE `walmart_product_feed` SET status ="' . $val['feedStatus'] . '", items_received="' . $val['itemsReceived'] . '", items_succeeded="' . $val['itemsSucceeded'] . '", items_failed="' . $val['itemsFailed'] . '", items_processing="' . $val['itemsProcessing'] . '", feed_date=' . $feed_date . ' WHERE feed_id="' . $val['feedId'] . '" AND merchant_id="' . MERCHANT_ID . '" ', null, 'update');
                            $count++;
                        }
                        $successFeeds[] = $feed_id;
                    } else {
                        $errorFeeds[] = $feed_id;
                    }

                } catch (Exception $e) {
                    return $returnArr['error'] = $e->getMessage();
                }

            } else {
                $processedFeeds[] = $feed_id;
            }
        }

        if (count($successFeeds)) {
            Yii::$app->session->setFlash('success', count($successFeeds) . " feed(s) updated successfully.");
        }
        if (count($errorFeeds)) {
            Yii::$app->session->setFlash('error', count($errorFeeds) . " feed(s) are not updated.");
        }
        if (count($processedFeeds)) {
            Yii::$app->session->setFlash('warning', count($processedFeeds) . " feed(s) are already processed.");
        }

        if ($count > 0) {
            $returnArr['success']['count'] = $count;
        } else {
            return true;
        }
        return json_encode($returnArr);
    }

    public function actionViewfeed($id)
    {

        $feed_detail = Data::sqlRecords("SELECT * FROM `walmart_product_feed` WHERE id='" . $id . "'", 'one');
        $limit = $feed_detail['items_received'];
        if (!empty($feed_detail['feed_id'])) {
            $wal = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
            $feed_data = $wal->viewFeed($feed_detail['feed_id'], $limit);
        }

        return $this->render('viewfeed', ['feed_data' => $feed_data, 'feed_created_date' => $feed_detail['created_at']]);

    }

    public function actionFile($id)
    {
        $merchant_id = MERCHANT_ID;

        $query = Data::sqlRecords('SELECT feed_file FROM `walmart_product_feed` WHERE merchant_id="' . $merchant_id . '" AND id= "' . $id . '" ', 'one');

        if (!empty($query['feed_file']) && file_exists($query['feed_file'])) {
            $file = $query['feed_file'];
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
        return $this->redirect('index');

    }

    public function actionReuploadproducts()
    {
        $session = Yii::$app->session;
        $merchant_id = Yii::$app->user->identity->id;

        $errorType = Yii::$app->request->post('error_type', false);
        $skus = Yii::$app->request->post('product_sku', false);

        /*echo '<pre>';
        print_r($errorType);
        print_r($skus);
        die;*/

        if ($errorType && $skus) {
            $uploadIds = [];
            $erroredProducts = [];
            foreach ($skus as $key => $sku) {
                if ($errorType[$key] == ProductFeed::ERROR_CODE_PRODUCT_ID_OVERRIDE) {
                    $id = ProductFeed::getProductIdFromSku($sku);
                    if ($id && !in_array($id, $uploadIds)) {
                        $uploadIds[] = $id;
                        $erroredProducts[ProductFeed::ERROR_CODE_PRODUCT_ID_OVERRIDE][] = $id;

                        /*$pro_id_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_ID_OVERRIDE;
                        ProductFeed::updateProductColumn('product_id_override', '1', $erroredProducts[$pro_id_override_err_code]);*/
                    }
                } elseif ($errorType[$key] == ProductFeed::ERROR_CODE_PRODUCT_SKU_OVERRIDE) {
                    $id = ProductFeed::getProductIdFromSku($sku);
                    if ($id && !in_array($id, $uploadIds)) {
                        $uploadIds[] = $id;
                        $erroredProducts[ProductFeed::ERROR_CODE_PRODUCT_SKU_OVERRIDE][] = $id;

                        /*$pro_sku_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_SKU_OVERRIDE;
                        ProductFeed::updateProductColumn('sku_override', '1', $erroredProducts[$pro_sku_override_err_code]);*/
                    }
                }
            }

            if ($Productcount = count($uploadIds)) {

                $pro_id_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_ID_OVERRIDE;
                $pro_sku_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_SKU_OVERRIDE;

                if (isset($erroredProducts[$pro_id_override_err_code]) && !empty($erroredProducts[$pro_id_override_err_code])) {

                    $pro_id_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_ID_OVERRIDE;
                    ProductFeed::updateProductColumn('product_id_override', '1', $erroredProducts[$pro_id_override_err_code]);

                } elseif (isset($erroredProducts[$pro_sku_override_err_code]) && !empty($erroredProducts[$pro_sku_override_err_code])) {

                    $pro_sku_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_SKU_OVERRIDE;
                    ProductFeed::updateProductColumn('sku_override', '1', $erroredProducts[$pro_sku_override_err_code]);

                }

                $session->set('re_upload_products_' . $merchant_id, $erroredProducts);

                $size_of_request = 100;//Number of products to be uploaded at once(in single feed)
                $pages = (int)(ceil($Productcount / $size_of_request));

                $selectedProducts = array_chunk($uploadIds, $size_of_request);

                //Increase Array Indexes By 1
                //$selectedProducts = array_combine(range(1, count($selectedProducts)), array_values($selectedProducts));

                $session->set('selected_products', $selectedProducts);

                return $this->render('../walmartproduct/ajaxbulkupload', [
                    'totalcount' => $Productcount,
                    'pages' => $pages,
                    'after_upload' => true
                ]);
            }
        } else {
            Yii::$app->session->setFlash('error', "No Product selected...");
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionAfterReupload()
    {
        if (Yii::$app->request->post('start', false)) {
            $session = Yii::$app->session;
            $merchant_id = Yii::$app->user->identity->id;

            $productIds = $session->get('re_upload_products_' . $merchant_id);

            $pro_id_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_ID_OVERRIDE;
            $pro_sku_override_err_code = ProductFeed::ERROR_CODE_PRODUCT_SKU_OVERRIDE;
            if (is_array($productIds)) {
                $session->remove('re_upload_products_' . $merchant_id);
                if (isset($productIds[$pro_id_override_err_code])) {
                    $overrideProducts = $productIds[$pro_id_override_err_code];
                    ProductFeed::updateProductColumn('product_id_override', '0', $overrideProducts);
                } elseif (isset($productIds[$pro_sku_override_err_code])) {
                    $overrideSkuProducts = $productIds[$pro_sku_override_err_code];
                    ProductFeed::updateProductColumn('sku_override', '0', $overrideSkuProducts);
                }
            }

            echo json_encode(['after_reupload' => 'success']);
        }
    }

    /**
     * Finds the WalmartProductFeed model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WalmartProductFeed the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartProductFeed::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
