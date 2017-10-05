<?php

namespace frontend\modules\jetapi\controllers;

use Yii;
use yii\helpers\BaseJson;
use yii\web\NotFoundHttpException;
use frontend\modules\jetapi\components\Uploadproduct;
use frontend\modules\jetapi\components\Datahelper;
use frontend\modules\jetapi\components\Jetapi;
use frontend\components\Jetproductinfo;

class JetproductController extends JetapiController
{
    protected $connection;
    protected $walmartHelper;

    /**
     * product list
     * @return jsonarray
     */
    public function actionList()
    {
        $pageInfo = Yii::$app->request->post();
        $dynamicFilter = ['title' => array('title' => 'title', 'type' => 'text', 'format' => 'string', 'tag' => 'title'), 'sku' => array('title' => 'sku', 'type' => 'text', 'format' => 'string', 'tag' => 'sku'), 'type' => array('title' => 'Type', 'type' => 'dropdown', 'format' => 'string', 'tag' => 'type', 'value' => array('simple' => 'string', 'variants' => 'string')), 'merchant_id' => array('title' => 'Merchant Id', 'type' => 'range', 'format' => 'int', 'tag' => 'merchant_id'), 'status' => array('title' => 'Status', 'type' => 'dropdown', 'format' => 'string', 'tag' => 'status', 'value' => array('Available for Purchase' => 'string', 'Under Jet Review' => 'string', 'Excluded' => 'string', 'Not Uploaded' => 'string', 'Archived' => 'string', 'Missing Listing Data' => 'string', 'Submitted On Jet' => 'string', 'Unauthorized' => 'string'))];
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
//        if(isset($pageInfo['page']) && isset($pageInfo['limit'])){
//            $page = $pageInfo['page'];
//            $limit = $pageInfo['limit'];
//            $page = $page*$limit;
//        }
//        else{
//            $page = 0;
//            $limit = 50;
//            $page = $page*$limit;
//        }
//        if (isset($pageInfo['limit'])) {
//            $limit = $pageInfo['limit'];
//        } else {
//            $limit = 20;
//        }

        if (isset($pageInfo['page'])) {
            $page = $pageInfo['page'];
        } else {
            $page = 0;
        }

        $limit = 5;

        $page = $page * $limit;

        $requestproduct = [];
        if (isset($pageInfo['filter'])) {

            $pageInfo['filter'] = BaseJson::decode($pageInfo['filter']);
            $allowFilter = ['title' => "title",
                'sku' => "sku",
                'type' => "type",
                'merchant_id' => "merchant_id",
                'status' => 'status'];
            foreach ($pageInfo['filter'] as $condition => $value) {
                if (isset($allowFilter[$condition]) && !empty($value)) {
                    if (gettype($value) == 'string') {
                        $condition = $allowFilter[$condition];
                        $fields[] = sprintf("%s LIKE '%s'",
                            $condition, '%' . $value . '%');
                    } else {
                        $condition = $allowFilter[$condition];
                        $fields[] = sprintf("%s = '%s'",
                            $condition, $value);
                    }

                }

            }
            if (count($fields) > 0) {
                $whereClause = "WHERE " . implode(" AND ", $fields) . ' AND merchant_id =' . $merchant_id;
                $query = 'select * from `jet_product` ' . $whereClause . ' LIMIT ' . $page . ',' . $limit . '';

                $jetProduct = Datahelper::sqlRecords($query, 'all');

                foreach ($jetProduct as $product) {
                    if ($product['type'] == 'variants') {
                        $data = $this->variantsProduct($product['id']);
                        $product['variants'] = $data;
                    }

                    $product['action'][] = 'view';

                    if ($product['status'] == 'Not Uploaded') {
                        $product['action'][] = 'upload';
                        if (isset($product['error']) && !empty($product['error'])) {
                            $product['action'][] = 'error';
                        }
                    }
                    $requestproduct[] = $product;

                }

                $validateData = ['success' => true, 'message' => 'Filter Apply successfully', 'data' => array('product' => $requestproduct, 'filter' => $dynamicFilter)];
                return BaseJson::encode($validateData);
            } else {
                $validateData = ['success' => false, 'message' => 'Not Found'];
                return BaseJson::encode($validateData);
            }
        } else {
            $query = 'select * from `jet_product` where merchant_id =' . $merchant_id . ' LIMIT ' . $page . ',' . $limit . '';
            $jetProduct = Datahelper::sqlRecords($query, 'all');
            foreach ($jetProduct as $product) {
                if ($product['type'] == 'variants') {
                    $data = $this->variantsProduct($product['id']);
                    $product['variants'] = $data;
                }
                $requestproduct[] = $product;
            }

            $validateData = ['success' => true, 'message' => 'All Jet product list', 'data' => array('product' => $requestproduct, 'filter' => $dynamicFilter)];
            return BaseJson::encode($validateData);
        }


    }


    /**
     * variants product list
     * @return array
     */
    public function variantsProduct($productId)
    {
        $query = 'select * from `jet_product_variants` where product_id="' . $productId . '"';
        $variantsProduct = Datahelper::sqlRecords($query, 'all');
        return $variantsProduct;

    }

    /**
     * product view
     * @return jsonarray
     */
    public function actionView()
    {

        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $pageInfo = Yii::$app->request->post();
        if (isset($pageInfo['product_id'])) {
            $query = 'select * from `jet_product`  where merchant_id =' . $merchant_id . ' AND id="' . $pageInfo['product_id'] . '" LIMIT 0,1';
            $jetProduct = Datahelper::sqlRecords($query, 'one');

            if ($jetProduct) {
                if ($jetProduct['type'] == 'variants') {
                    $data = $this->variantsProduct($jetProduct['id']);
                    $jetProduct['variants'] = $data;
                }
                $requestproduct[] = $jetProduct;
                $validateData = ['success' => true, 'message' => 'successfully product view', 'data' => $requestproduct];
                return BaseJson::encode($validateData);
            } else {
                $validateData = ['success' => false, 'message' => 'Product not found.'];
                return BaseJson::encode($validateData);
            }

        } else {
            $validateData = ['success' => false, 'message' => 'No Product Selected'];
            return BaseJson::encode($validateData);
        }
    }


    /**
     * upload single product
     * @return json_array
     */
    public function actionUpload()
    {

        $output = Yii::$app->request->post();
        $data = "";
        $queryObj = "";
        $data = Uploadproduct::getProductDetails($output);
        $validateData = ['data' => $data];
        return BaseJson::encode($validateData);
    }


}