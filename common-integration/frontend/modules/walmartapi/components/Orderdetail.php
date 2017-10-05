<?php

namespace frontend\modules\walmartapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;


class Orderdetail extends Component
{
    /**
     * @param $Output
     * @return array|bool|string
     */
    public function getOrderDetail($Output)
    {
        if (isset($Output['filter'])) {
            $out = json_decode($Output['filter'], true);

            $Output['filter'] = $out;
        }

        try {
            $orderdetail = self::getDetails($Output);

        } catch (\Exception $e) // an exception is raised if a query fails
        {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return $orderdetail;
    }

    /**
     * @param $Output
     * @return array|string
     */
    public function getDetails($Output)
    {
        $filterarray = array(
            /*'id' => 'int',*/
            'shopify_order_id' => 'string',
            /*'merchant_id' => 'int',*/
            'sku' => 'int',
            'status' => 'string'
        );

        $dynamicfilter = array(
            /*'id' => array(
                'title' => 'id',
                'type' => 'int',
                'format' => 'string',
                'tag' => 'id'),*/
            /*'merchant_id' => array(
                'title' => 'merchant id',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'merchant_id'),*/
            'shopify_order_id' => array(
                'title' => 'shopify order id',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'shopify_order_id'),
            'sku' => array(
                'title' => 'sku',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'sku'),
            'status' => array(
                'title' => 'status',
                'type' => 'dropdown',
                'format' => 'string',
                'tag' => 'status',
                'value'=>array(
                    'acknowledged'=>'string',
                    'canceled'=>'string',
                    'complete'=>'string',
                )
            ),
        );

        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

        if (isset($Output['page'])) {
            $page = $Output['page'];
        } else {
            $page = 0;
        }

//        if (isset($Output['limit'])) {
//            $limit = $Output['limit'];
//        } else {
//            $limit = 50;
//        }
        $limit = 20;

        $page = $page * $limit;

        if (!empty($Output['filter']) && $Output['filter']!='') {
            foreach ($Output['filter'] as $key => $value) {
                # code...
                if (isset($filterarray[$key]) && !empty($value)) {

                    if ($filterarray[$key] == 'string') {

                        $filters[] = sprintf("`%s` LIKE '%s'",
                            $key, '%' . $value . '%');
                    } else {
                        $filters[] = sprintf("`%s` = '%s'",
                            $key, $value);
                    }
                    $whereClause = "WHERE " . implode(" AND ", $filters) . ' AND merchant_id =' . $merchant_id;
                }

            }

            $data = Datahelper::sqlRecords("SELECT * FROM `walmart_order_details` $whereClause LIMIT $limit OFFSET $page", 'all');
        } else {
            $data = Datahelper::sqlRecords("SELECT * FROM `walmart_order_details` WHERE merchant_id='" . $merchant_id . "' LIMIT $limit OFFSET $page", 'all');
        }
        if (!empty($data)) {
            // $data['filter'] = $filterarray;

            $return = ['data' => ['order' => $data, 'filter' => $dynamicfilter], 'success' => true, 'message' => 'Successfully done'];
            $return['data']['order'] = array_values($return['data']['order']);
            /*return ['data' => ['order' => $data, 'filter' => $dynamicfilter], 'success' => true, 'message' => 'Successfully done'];*/
            return $return;
        } else {
            $returnArr = ['success' => false, 'message' => 'No Order available for this merchant'];
            return $returnArr;
        }
    }
}
