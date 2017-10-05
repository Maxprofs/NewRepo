<?php 
namespace frontend\modules\jet\components;
use frontend\modules\jet\components\Data;


class Jetproductlastupdated extends component
{
	public static function lastUpdated($sku = "", $merchantId = "")
	{
		if(is_numeric($merchantId)) 
		{
            Data::sqlRecords("UPDATE jet_product SET `last_updated` = '".$lastUpdated."' WHERE `merchant_id`='".$merchantId."' AND sku='".$sku."' ",null,'update');    		 
        } 
	}
}