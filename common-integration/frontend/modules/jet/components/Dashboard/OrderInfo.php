<?php 
namespace frontend\modules\jet\components\Dashboard;

use Yii;
use yii\base\Component;
use frontend\modules\jet\components\Data;

class OrderInfo extends Component
{
    /**
     * To Get Complete Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getCompletedOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) 
        {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `jet_order_detail` WHERE `merchant_id` ='{$merchantId}' AND `status` = 'complete' ";           
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
    public static function getAcknowledgedOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;

            $query = "SELECT COUNT(*) FROM `jet_order_detail` WHERE `merchant_id` ='{$merchantId}' AND `status` = 'acknowledged' ";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }      
    }
    
    /**
     * To Get Inprogress Orders Count
     * @param string $merchant_id
     * @return int
     */
    public static function getInprogressOrdersCount($merchantId)
    {
    	if(is_numeric($merchantId)) 
    	{
    		$result = [];
    		$total = 0;
    		$query = "SELECT COUNT(*) FROM `jet_order_detail` WHERE `merchant_id` ='{$merchantId}' AND `status` = 'inprogress' ";
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
    /* public static function getCancelledOrdersCount($merchantId)
    {
        if(is_numeric($merchantId)) {
            $result = [];
            $total = 0;
            $query = "SELECT COUNT(*) FROM `jet_order_detail` WHERE `status` = 'canceled' AND `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }      
    } */

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
            $query = "SELECT COUNT(*) FROM `jet_order_detail` WHERE `merchant_id` ={$merchantId}";
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
            $query = "SELECT COUNT(*) FROM `jet_order_import_error` WHERE `merchant_id` ={$merchantId}";
            $result = Data::sqlRecords($query, 'all');
            $total = is_array($result)?$result[0]["COUNT(*)"]:0;
            return $total;
        }      
    }


    public static function removeFailedOrders($merchant_id)
    {
        //$merchant_id = 14;//Yii::$app->user->identity->id;
        $failedOrders = array();
        $failedOrdersList = [];
        $orderExistList = [];
        $failedOrders = Data::sqlRecords("SELECT `merchant_order_id` FROM `jet_order_import_error` WHERE `merchant_id`='".$merchant_id."'   ","all","select");
        if(is_array($failedOrders) && count($failedOrders)>0){
            $failedOrdersList = array_column($failedOrders, 'merchant_order_id');
        }
        if(count($failedOrdersList)>0){
            $orderExist = array();
            $orderExist = Data::sqlRecords("SELECT `merchant_order_id` FROM `jet_order_detail` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id` IN('".implode("' , '", $failedOrdersList)."') ","all","select");
            if(is_array($orderExist) && count($orderExist)>0){
                $orderExistList = array_column($orderExist, 'merchant_order_id');
                if(count($orderExistList)>0){
                    Data::sqlRecords("DELETE FROM `jet_order_import_error` WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id` IN('".implode("' , '", $orderExistList)."') ",null,"update");
                }
            }
            
        }
    }
    // Get total revenue and orders count for perticular time period
    public static function getOrdersCount($merchantId,$month)
    {
        if(is_numeric($merchantId)) 
        {
            $result =  [];
            //$response['order'] = 0;
            $response['revenue'] = 0;
            
            $total = 0;            
            $revenue = 0.00;

            $query = "SELECT `id`,`order_data` FROM `jet_order_detail` WHERE `merchant_id` ='{$merchantId}' AND `order_real_status`='' AND `status` = 'complete' AND `shipped_at` LIKE '%".$month."%' ";  

            $result = Data::sqlRecords($query, 'all','select');
            if (!empty($result)) 
            {
                $revenue =  self::calculateRevenue($result);
                $response = ['revenue'=>$revenue];
                //$total = count($result);
                //$response = ['order'=>$total,'revenue'=>$revenue];
            }
            return $response; 
        }      
    }
    public function calculateRevenue($result1)
    {
        $total=0.00;
        foreach ($result1 as $val)
        {
            $priceData = json_decode($val['order_data'],true);
            $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];                                   
        }
        return (float)$total; 
    }
}
