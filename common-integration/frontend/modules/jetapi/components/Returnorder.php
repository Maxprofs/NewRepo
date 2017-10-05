<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;


class Returnorder extends Component
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
            'id' => 'int',
            'merchant_id' => 'int',
            'returnid' => 'string',
            'order_reference_id' => 'string',
            'status' => 'string'
        );

        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

        if (isset($Output['limit'])) {
            $limit = $Output['limit'];
        } else {
            $limit = 50;
        }

        if (isset($Output['page'])) {
            $page = $Output['page'];
        } else {
            $page = 0;
        }

        $page = $page * $limit;


        if (!empty($Output['filter'])) {
            foreach ($Output['filter'] as $key => $value) {
                # code...
                if (isset($filterarray[$key])) {

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

            $data = Datahelper::sqlRecords("SELECT * FROM `jet_return` $whereClause LIMIT $limit OFFSET $page", 'all');
        } else {
            $data = Datahelper::sqlRecords("SELECT * FROM `jet_return` WHERE merchant_id='" . $merchant_id . "' LIMIT $limit OFFSET $page", 'all');

        }
        if (!empty($data)) {
            $data['filter'] = $filterarray;

            return ['data'=>['order'=> $data, 'filter'=>$filterarray],'success' => true, 'message' => 'Successfully done'];
        } else {
            $returnArr = ['error' => true, 'message' => 'No Failed Order available for this merchant'];
            return $returnArr;
        }
    }
}
