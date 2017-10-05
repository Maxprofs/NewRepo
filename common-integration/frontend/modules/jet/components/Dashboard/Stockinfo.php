<?php 
namespace frontend\modules\jet\components\Dashboard;

use frontend\modules\jet\components\Data;
use yii\base\Component;

class Stockinfo extends Component
{
	/**
     * To check if product inventory is low
     * @param string $merchant_id
     * @return bool
     */
    public static function getInventoryUpdatesInfo($merchantId)
    {
    	if(is_numeric($merchantId))
        {
    		$result = [];
    		$output = [
    					'title'=>[],
    					'minQty'=>5,
    					'count'=>0
					];

    		$query = "SELECT `value` FROM `jet_config` WHERE `merchant_id` ='{$merchantId}' AND `data` = 'inventory' ";

            $result = Data::sqlRecords($query, 'one','select');

            $minQty = isset($result['value']) ? $result['value'] : 5;
            if (trim($minQty)=="") {
                $minQty = 5;
            }
            $result = [];


            $query="SELECT COALESCE(details.update_title,pro.title) as title FROM `jet_product` as `pro` LEFT JOIN `jet_product_details` as `details` ON details.product_id=pro.id WHERE pro.merchant_id='{$merchantId}' AND pro.fulfillment_node!=0 AND pro.qty<'{$minQty}' order by pro.qty ASC LIMIT 0,5";      

    		$result = Data::sqlRecords($query, 'all','select');
            $countRows = count($result)>0?count($result):0;
            $titleArray = count($result)>0?array_column($result, 'title'):[];
            $titleArray = array_slice($titleArray, 0, 5, true);
            $output = [
    					'title' => $titleArray,
    					'minQty' => $minQty,
    					'count' => $countRows,
					];
            return $output;
        }
    }
}