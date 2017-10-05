<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;

class Dashboard extends Component
{

    /**
     * @param $Output
     * @return array
     */
    public function getDashboardInfo($Output)
    {
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');

        $availableProduct = $jetreview = $readytoshipOrders = $result = '';
        $dashboard = array('liveonjet' => '', 'underjetreview' => '', 'readytoshipOrders' => '', 'totalrevenue' => '', 'cancelorder' => '', 'refundorder' => '');

        try {
            $availableProduct = self::getAvailableProduct($merchant_id);

            $jetreview = self::getJetReview($merchant_id);

            $readytoshipOrders = self::getReadytoship($merchant_id);

            $cancelorder = self::getCancelorder($merchant_id);

            $refundorder = self::getRefundorder($merchant_id);

            $result = self::getTotalOrders($merchant_id);

        } catch (\Exception $e) // an exception is raised if a query fails
        {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $dashboard['underjetreview'] = $jetreview;
        $dashboard['liveonjet'] = $availableProduct;
        $dashboard['readytoshipOrders'] = $readytoshipOrders;
        $dashboard['totalrevenue'] = $result;
        $dashboard['refundorder'] = $refundorder;
        $dashboard['cancelorder'] = $cancelorder;

        return ['data' => $dashboard, 'success' => true, 'message' => 'Dashboard Information'];
    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getAvailableProduct($merchant_id)
    {
//        return Datahelper::sqlRecords("SELECT COUNT(*) as `sku` FROM `jet_product` WHERE `status`='Available for Purchase' AND `merchant_id`=$merchant_id", 'one');
        $count = Datahelper::sqlRecords("SELECT COUNT(*) as `sku` FROM `jet_product` WHERE `status`='Available for Purchase' AND `merchant_id`=$merchant_id", 'one');

        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredjet/Live-Products_2.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitejet/Live-Products_3.png' ;


        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' => 'Live On Jet',
            'value' => $count['sku'],
            'relation' => 'product_list',
            'filter' => ['status' => 'Available for Purchase']
        ];

    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getJetReview($merchant_id)
    {
        $count = Datahelper::sqlRecords("SELECT  COUNT(*) as `sku` FROM `jet_product` WHERE `status`='Under Jet Review' AND `merchant_id`=$merchant_id", 'one');

        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredjet/Under-Review2.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitejet/Under-Review.png' ;
        return $data = [
            'icon' =>  ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title'=>'Under Jet Review',
            'value' => $count['sku'],
            'relation' => 'product_list',
            'filter' => ['status' => 'Under Jet Review']
        ];
    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getReadytoship($merchant_id)
    {
//        Ready-to-Ship-orders2
        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredjet/Ready-to-Ship-orders2.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitejet/Ready-to-Ship-orders.png' ;

        $count = Datahelper::sqlRecords("SELECT COUNT(*) as `merchant_sku` FROM `jet_order_detail` WHERE `status` = 'acknowledged' AND `merchant_id`=$merchant_id", 'one');
        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title'=>'Ready to ship',
            'value' => $count['merchant_sku'],
            'relation' => 'order_list',
            'filter' => ['status' => 'acknowledged']
        ];
    }


    /**
     * @param $merchant_id
     * @return array
     */
    public function getCancelorder($merchant_id)
    {
        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredjet/cancel-Order2.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitejet/cancel-Order.png' ;

        $count = Datahelper::sqlRecords("SELECT COUNT(*) as `merchant_sku` FROM `jet_order_detail` WHERE `status`='canceled' AND `merchant_id`=$merchant_id", 'one');
        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' =>'Cancel Order',
            'value' => $count['merchant_sku'],
            'relation' => 'order_list',
            'filter' => ['status' => 'canceled']
        ];
    }

    /**
     * @param $merchant_id
     * @return array
     */
    public function getRefundorder($merchant_id)
    {
        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredjet/Refund-Order2.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitejet/Refund-Orders.png' ;

        $count = Datahelper::sqlRecords("SELECT  COUNT(*) as `refund_id` FROM `jet_refund` WHERE `merchant_id`=$merchant_id", 'one');
        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title'=>'Refund Order',
            'value' => $count['refund_id'],
            'relation' => 'order_list',
            'filter' => ['status' => 'refund']
        ];
    }

    /**
     * @param $merchant_id
     * @return float|int
     */
    public function getTotalOrders($merchant_id)
    {
        $result = Datahelper::sqlRecords("SELECT `status`,`shipment_data` FROM `jet_order_detail` WHERE `status` = 'complete' AND `merchant_id`=$merchant_id", 'all');

        $total = 0;
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $val) {
                if ($val['shipment_data']) {
                    $shipping_amount = array();
                    $shipping_amount = json_decode($val['shipment_data'], true);
                    if (isset($shipping_amount['total_price']))
                        $total = $total + $shipping_amount['total_price'];
                }
            }
        }
        $coloredImage = Yii::$app->request->baseUrl.'/images/coloredjet/Tootal-Revenue1.png' ;
        $whiteImage = Yii::$app->request->baseUrl.'/images/whitejet/Tootal-Revenue.png' ;
        return $data = [
            'icon' => ['colored_image'=>$coloredImage,'white_image'=>$whiteImage],
            'title' =>'Total Revenue',
            'value' => $total,
            'relation' => 'order_list',
            'filter' => ['status' => 'complete'],
        ];
    }

}