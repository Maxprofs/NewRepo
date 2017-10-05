<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;


class Refundorder extends Component
{
    /**
     * @param $Output
     * @return array|bool|string
     */
    public function getOrderDetail($Output)
    {
//        if (isset($Output['filter'])) {
////            $out = json_decode($Output['filter'], true);
////
////            $Output['filter'] = $out;
//            $orderdetail = self::getDetails($Output);
//        }
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
            'id' => 'int',
            'refund_id' => 'string',
            'merchant_order_id' => 'string',
            'merchant_id' => 'int',
            'order_item_id' => 'string',
        );

        $dynamicfilter = array(
            'id' => array(
                'title' => 'id',
                'type' => 'int',
                'format' => 'string',
                'tag' => 'id'),
            'refund_id' => array(
                'title' => 'reference order id',
                'type' => 'int',
                'format' => 'string',
                'tag' => 'refund_id'),
            'merchant_order_id' => array(
                'title' => 'merchant order id',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'merchant_order_id'),
            'merchant_id' => array(
                'title' => 'shopify order name',
                'type' => 'int',
                'format' => 'string',
                'tag' => 'merchant_id'),
            'order_item_id' => array(
                'title' => 'merchant sku',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'order_item_id'
            ),
        );

        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

//        if (isset($Output['limit'])) {
//            $limit = $Output['limit'];
//        } else {
//            $limit = 50;
//        }
//
        if (isset($Output['page'])) {
            $page = $Output['page'];
        } else {
            $page = 0;
        }

        $limit = 20;

        $page = $page * $limit;

        if (!empty($Output['filter'])) {
            unset($Output['filter']['status']);

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
            if (!isset($whereClause)) {
                $data = Datahelper::sqlRecords("SELECT * FROM `jet_refund` WHERE merchant_id='" . $merchant_id . "' LIMIT $limit OFFSET $page", 'all');

            } else {
                $data = Datahelper::sqlRecords("SELECT * FROM `jet_refund` $whereClause LIMIT $limit OFFSET $page", 'all');
            }

        } else {
            $data = Datahelper::sqlRecords("SELECT * FROM `jet_refund` WHERE merchant_id='" . $merchant_id . "' LIMIT $limit OFFSET $page", 'all');

        }
        if (!empty($data)) {

//            $data['filter'] = $filterarray;

            return ['data' => ['order' => $data, 'filter' => $dynamicfilter], 'success' => true, 'message' => 'Successfully done'];
        } else {
            $returnArr = ['success' => false, 'message' => 'No refund Order available for this merchant'];
            return $returnArr;
        }
    }
}
