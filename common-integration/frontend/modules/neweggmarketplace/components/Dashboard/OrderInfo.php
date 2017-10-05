<?php 
namespace frontend\modules\neweggmarketplace\components\Dashboard;

use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\components\Data;

class OrderInfo extends Component
{
    /**
     * To Get Complete Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getShippedOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `newegg_order_detail` WHERE `order_status_description` = '".Data::ORDER_STATUS_SHIPPED."' AND `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total; 
        }      
    }
    
    /**
     * To Get Acknowledged Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getUnshippedOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `newegg_order_detail` WHERE `order_status_description` = '".Data::ORDER_STATUS_UNSHIPPED."' AND `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }
    }

    /**
     * To Get Cancelled Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getCancelledOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `newegg_order_detail` WHERE `order_status_description` = '".Data::ORDER_STATUS_VOIDED."' AND `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }      
    }

    /**
     * To Get Cancelled Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getTotalOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `newegg_order_detail` WHERE `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }      
    }

    /**
     * To Get Cancelled Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getFailedOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `newegg_order_import_error` WHERE `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }      
    }


    public function removeFailedOrders($merchant_id)
    {
        $failedOrders = array();
        $failedOrdersList = [];
        $orderExistList = [];
        $failedOrders = Data::sqlRecords("SELECT `merchant_order_id` FROM `newegg_order_import_error` WHERE `merchant_id`='".$merchant_id."'   ","all","select");
        if(is_array($failedOrders) && count($failedOrders)>0){
            $failedOrdersList = array_column($failedOrders, 'merchant_order_id');
        }
        if(count($failedOrdersList)>0){
            $orderExist = array();
            $orderExist = Data::sqlRecords("SELECT `merchant_order_id` FROM `newegg_order_detail` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id` IN('".implode("' , '", $failedOrdersList)."') ","all","select");
            if(is_array($orderExist) && count($orderExist)>0){
                $orderExistList = array_column($orderExist, 'merchant_order_id');
                if(count($orderExistList)>0){
                    Data::sqlRecords("DELETE FROM `newegg_order_import_error` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id` IN('".implode("' , '", $orderExistList)."') ",null,"update");
                }
            }
            
        }
    }

    public static function getInvoicedOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `newegg_order_detail` WHERE `order_status_description` = '".Data::ORDER_STATUS_INVOICED."' AND `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }
    }
   
}
