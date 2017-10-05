<?php

namespace frontend\modules\walmartapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;

class Dashboard extends Component
{
    const PRODUCT_STATUS_UPLOADED = 'PUBLISHED';
    const PRODUCT_STATUS_NOT_UPLOADED = 'Not Uploaded';
    const ORDER_STATUS_ACKNOWLEDGED = 'acknowledged';
    const ORDER_STATUS_COMPLETED = 'completed';

    /**
     * @param $Output
     * @return array
     */
    public function getDashboardInfo($Output)
    {
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

        $availableProduct = $completedOrders = $readytoshipOrders = $result = '';
        $dashboard = array('totalOrders' => '', 'availableProduct' => '', 'readytoshipOrders' => '', 'totalrevenue' => '');

        try {
            $availableProduct = self::getAvailableProduct($merchant_id);

            $completedOrders = self::getCompletedOrder($merchant_id);

            $readytoshipOrders = self::getReadytoship($merchant_id);

            $result = self::getTotalOrders($merchant_id);

        } catch (\Exception $e) // an exception is raised if a query fails
        {
            return ['error'=>true ,'message'=>$e->getMessage()];
        }

        $dashboard['totalOrders'] = $completedOrders;
        $dashboard['availableProduct'] = $availableProduct;
        $dashboard['readytoshipOrders'] = $readytoshipOrders;
        $dashboard['totalrevenue'] = $result;

        return ['data'=>$dashboard,'success'=>true,'message'=>'Dashboard Information'];
    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getAvailableProduct($merchant_id)
    {

        $count = Datahelper::sqlRecords("SELECT COUNT(*) as `available_product` FROM `walmart_product`  WHERE `status`= 'PUBLISHED' AND `merchant_id`=$merchant_id", 'one');

        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredwalmart/Live-Products_3.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitewalmart/Live-Products_3.png' ;

        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' => 'Live On Walmart',
            'value' => $count['available_product'],
            'relation' => 'product_list',
            'filter' => ['status' => 'PUBLISHED']
        ];

    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getCompletedOrder($merchant_id)
    {
        $count = Datahelper::sqlRecords("SELECT COUNT(*) as `total_orders` FROM `walmart_order_details` WHERE `status`='completed' AND `merchant_id`=$merchant_id", 'one');

        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredwalmart/box-1.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitewalmart/box.png' ;

        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' => 'Total Order',
            'value' => $count['total_orders'],
            'relation' => 'order_list',
            'filter' => ['status' => 'completed']
        ];
    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getReadytoship($merchant_id)
    {

        $count = Datahelper::sqlRecords("SELECT COUNT(*) as `ready` FROM `walmart_order_details` WHERE `status` = 'acknowledged' AND `merchant_id`=$merchant_id", 'one');

        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredwalmart/Ready-to-Ship-orders.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitewalmart/Ready-to-Ship-orders.png' ;

        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' => 'Ready To Ship Orders',
            'value' => $count['ready'],
            'relation' => 'order_list',
            'filter' => ['status' => 'acknowledged']
        ];
    }

    /**
     * @param $merchant_id
     * @return float|int
     */
    public function getTotalOrders($merchant_id)
    {
        $result = Datahelper::sqlRecords("SELECT `order_total` FROM `walmart_order_details` WHERE `status` = 'completed' AND `merchant_id`=$merchant_id", 'all');
        $total = 0;
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $val) {
                if (isset($val['order_total'])) {
                    $total += floatval($val['order_total']);
                }
            }
        }

        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredwalmart/Tootal-Revenue.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitewalmart/Tootal-Revenue.png' ;

        // return $total;
        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' => 'Total Revenue',
            'value' => $total,
            'relation' => 'order_list',
            'filter' => ['status' => 'completed']
        ];
    }

}