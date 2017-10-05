<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;


class Salesorder extends Component
{
    /**
     * @param $Output
     * @return array|bool|string
     */
    public function getOrderDetail($Output)
    {

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
            'reference_order_id' => 'int',
            'merchant_order_id' => 'string',
            'shopify_order_name' => 'string',
            'merchant_sku' => 'string',
            'status' => 'string'
        );

        $dynamicfilter = array(
            'id' => array(
                'title' => 'id',
                'type' => 'int',
                'format' => 'string',
                'tag' => 'id'),
            'reference_order_id' => array(
                'title' => 'reference order id',
                'type' => 'int',
                'format' => 'string',
                'tag' => 'reference_order_id'),
            'merchant_order_id' => array(
                'title' => 'merchant order id',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'merchant_order_id'),
            'shopify_order_name' => array(
                'title' => 'shopify order name',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'shopify_order_name'),
            'merchant_sku' => array(
                'title' => 'merchant sku',
                'type' => 'string',
                'format' => 'string',
                'tag' => 'merchant_sku'),
            'status' => array(
                'title' => 'status',
                'type' => 'dropdown',
                'format' => 'string',
                'tag' => 'status',
                'value'=>array(
                    'acknowledged'=>'string',
                    'canceled'=>'string',
                    'complete'=>'string',
                    'inprogress'=>'string'
                    )
            ),
        );


        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

//        if (isset($Output['limit'])) {
//            $limit = $Output['limit'];
//        } else {
//            $limit = 50;
//        }

        if (isset($Output['page'])) {
            $page = $Output['page'];
        } else {
            $page = 0;
        }
        $limit = 20;

        $page = $page * $limit;


        if (!empty($Output['filter']) && !empty($Output['filter']['status'])) {
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
            $data = Datahelper::sqlRecords("SELECT * FROM `jet_order_detail`  $whereClause LIMIT $limit OFFSET $page", 'all');
        } else {
            $data = Datahelper::sqlRecords("SELECT * FROM `jet_order_detail` WHERE merchant_id='" . $merchant_id . "' LIMIT $limit OFFSET $page", 'all');
        }
        if (!empty($data)) {

            foreach ($data as $key=>$item){
                if($item['status'] == 'acknowledged')
                {
                    $data[$key]['action'] ='shipment';

                }
            }
            return ['data' => ['order' => $data, 'filter' => $dynamicfilter], 'success' => true, 'message' => 'Successfully done'];
        } else {
            $returnArr = ['success' => false, 'message' => 'No Order available for this merchant'];
            return $returnArr;
        }
    }
}
