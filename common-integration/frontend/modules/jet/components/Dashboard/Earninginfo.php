<?php 
namespace frontend\modules\jet\components\Dashboard;

use frontend\modules\jet\components\Data;
use yii\base\Component;

class Earninginfo extends Component
{
    public static $_today = 0;
    public static $_week = 0;
    public static $_month = 0;
    public static $_total = 0;

    /**
     * To check Revenue
     * @param string $merchant_id
     * @return bool
     */
    public static function getTodayEarning($merchant_id)
    {
        $date=date('Y-m-d  H:i:s');
        $date1 = date('Y-m-d 00:00:00');
        if(is_numeric($merchant_id)) 
        {
            $result1 = [];
            $query = "SELECT `order_data` FROM `jet_order_detail` WHERE `shipped_at` BETWEEN '{$date1}' AND '{$date}' AND `status` = 'complete' AND `merchant_id` ={$merchant_id}";
            $result1 = Data::sqlRecords($query, 'all');
            $total=0.00;
            if(!empty($result1) && is_array($result1))
            {
                foreach ($result1 as $val)
                {
                    $priceData = json_decode($val['order_data'],true);
                    $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];                                   
                }
            }  
            return (float)$total; 
        }               
    }
    
    public static function getMonthlyEarning($merchant_id)
    {
        $date=date('Y-m-d');
        $date1=date('Y-m-d',strtotime('-30 days', strtotime(date('Y-m-d'))));
        if(is_numeric($merchant_id)) 
        {
            $query = "SELECT `order_data` FROM `jet_order_detail` WHERE `shipped_at` BETWEEN '{$date1}' AND '{$date}' AND `status` = 'complete' AND `merchant_id` ={$merchant_id}";
            $result1 = Data::sqlRecords($query, 'all');
            $total=0.00;
            if(is_array($result1) && !empty($result1))
            {
                foreach ($result1 as $val)
                {
                    $priceData = json_decode($val['order_data'],true);
                    $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];                  
                }
            }  
            return (float)$total;
        }       
         
    }
    public static function getWeeklyEarning($merchant_id)
    {
        $date=date('Y-m-d');
        $date1=date('Y-m-d',strtotime('-7 days', strtotime(date('Y-m-d'))));
        if(is_numeric($merchant_id)) 
        {

            $query = "SELECT `order_data` FROM `jet_order_detail` WHERE `shipped_at` BETWEEN '{$date1}' AND '{$date}' AND `status` = 'complete' AND `merchant_id` ={$merchant_id}";
            $result1 = Data::sqlRecords($query, 'all');
            $total=0.00;
            if(is_array($result1) && !empty($result1))
            {
                foreach ($result1 as $val)
                {
                    $priceData = json_decode($val['order_data'],true);
                    $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];                  
                }
            }  
            return (float)$total; 
        }                
    }
    public static function getTwoWeeklyEarning($merchant_id)
    {
        $total=0.00;
        $date=date('Y-m-d 00:00:00');
        $date1=date('Y-m-d 00:00:00',strtotime('-15 days', strtotime(date('Y-m-d 00:00:00'))));
        if(is_numeric($merchant_id)) 
        {

            $query = "SELECT `order_data` FROM `jet_order_detail` WHERE `merchant_id` ='{$merchant_id}' AND `order_real_status`='' AND `shipped_at` BETWEEN ('{$date1}' AND '{$date}') AND `status` = 'complete' ";
            $result1 = Data::sqlRecords($query, 'all');            
            if(is_array($result1) && !empty($result1))
            {
                foreach ($result1 as $val)
                {
                    $priceData = json_decode($val['order_data'],true);
                    $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];                  
                }
            }              
        } 
        return (float)$total;                
    }
    public static function getTotalEarning($merchant_id)
    {
       if(is_numeric($merchant_id)) 
       {
            $query = "SELECT `order_data` FROM `jet_order_detail` WHERE `status` = 'complete' AND `merchant_id`=".$merchant_id;
            $result1 = Data::sqlRecords($query, 'all');
            $total=0.00;
            if(is_array($result1) && count($result1)>0)
            {
                foreach ($result1 as $val)
                {
                    $priceData = json_decode($val['order_data'],true);
                    $total +=  $priceData['order_totals']['item_price']['item_tax'] + $priceData['order_totals']['item_price']['item_shipping_cost']+ $priceData['order_totals']['item_price']['item_shipping_tax']+ $priceData['order_totals']['item_price']['base_price'];                   
                }
            } 
            return (float)$total; 
        }               
    }   
}