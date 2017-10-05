<?php 
namespace frontend\modules\jet\components\Dashboard;

use frontend\modules\jet\components\Data;
use yii\base\Component;

class Productinfo extends Component
{
    const JET_LIVE_PRODUCT_STATUS = 'Available for Purchase';
    const JET_UNDER_REVIEW_PRODUCT_STATUS = 'Under Jet Review';
    const JET_ARCHIVED_PRODUCT_STATUS = 'Archived';
    const JET_EXCLUDED_PRODUCT_STATUS = 'Excluded';
    const JET_UNAUTHORIZED_PRODUCT_STATUS = 'Unauthorized';
    const JET_NOT_UPLOADED_PRODUCT_STATUS = 'Not Uploaded';
    const JET_MISSING_DATA_PRODUCT_STATUS = 'Missing Listing Data';
    public static $_categoryNotMappedCount = 0;
    /**
     * get Total Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getTotalProducts($merchant_id,&$type=null)
    {
        $_totalProducts = 0;
        $variantCount=Data::sqlRecords("SELECT count(*) as var_count FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."'","one","select");
        if(isset($variantCount['var_count']) && $variantCount['var_count']>0) 
        {
            $type="Variants";
        }
        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one','select');
            if(isset($result['count']) && $result['count']) {
                $_totalProducts = $result['count'];
            }
        }        
        return $_totalProducts;
    }

    /**
     * get Live Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getLiveProducts($merchant_id)
    {     
        $liveProducts = 0;   
        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_LIVE_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_LIVE_PRODUCT_STATUS."') as `main` LIMIT 0,1";            
            $result = Data::sqlRecords($query, 'one','select');
            if(isset($result['count']) && $result['count']) {
                $liveProducts = $result['count'];
            }
        }        
        return $liveProducts;
    }

    /**
     * get Under Review Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getUnderReviewProducts($merchant_id)
    {
        $underReviewProducts = 0;
        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_UNDER_REVIEW_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_UNDER_REVIEW_PRODUCT_STATUS."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');

            if(isset($result['count']) && $result['count']) {
                $underReviewProducts = $result['count'];
            }
        }        
        return $underReviewProducts;
    }

    /**
     * get Archived Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getArchivedProducts($merchant_id)
    {
        $_archivedProducts = 0;

        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_ARCHIVED_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_ARCHIVED_PRODUCT_STATUS."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');
            if(isset($result['count']) && $result['count']) {
                $_archivedProducts = $result['count'];
            }
        }        
        return $_archivedProducts;
    }

    /**
     * get Excluded Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getExcludedProducts($merchant_id)
    {
        $_ExcludedProducts = 0;

        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_EXCLUDED_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_EXCLUDED_PRODUCT_STATUS."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');
            if(isset($result['count']) && $result['count']) {
                $_ExcludedProducts = $result['count'];
            }
        }        
        return $_ExcludedProducts;
    }

    /**
     * get Unauthorized Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getUnauthorizedProducts($merchant_id)
    {
        $_UnauthorizedProducts = 0;

        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_UNAUTHORIZED_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_UNAUTHORIZED_PRODUCT_STATUS."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');
            if(isset($result['count']) && $result['count']) {
                $_UnauthorizedProducts = $result['count'];
            }
        }        
        return $_UnauthorizedProducts;
    }


    /**
     * get Not Uploaded Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getNotUploadedProducts($merchant_id)
    {
        $_notUploadedProducts = 0;

        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_NOT_UPLOADED_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_NOT_UPLOADED_PRODUCT_STATUS."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');
            if(isset($result['count']) && $result['count']) {
                $_notUploadedProducts = $result['count'];
            }
        }        
        return $_notUploadedProducts;
    }

    /**
     * get Missing Listing Data Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getMissingDataProducts($merchant_id)
    {
        $_missingDataProducts = 0;

        if(is_numeric($merchant_id)) 
        {
            $query="SELECT COUNT(*) as count FROM (SELECT `variant_id` as opt_id FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_MISSING_DATA_PRODUCT_STATUS."' UNION SELECT `option_id` as opt_id FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `status`='".self::JET_MISSING_DATA_PRODUCT_STATUS."') as `main` LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one','select');
            if(isset($result['count']) && $result['count']) {
                $_missingDataProducts = $result['count'];
            }
        }        
        return $_missingDataProducts;
    }

    public function getProductsCountUpdatedToday($merchantId)
    {
        if(is_numeric($merchantId)) 
        {
            $result = [];
            $dateTimeFrom = date('Y-m-d 00:00:00');
            $dateTimeTo = date('Y-m-d 23:59:59');
            $query = "SELECT COUNT(*) as `count` FROM `jet_product_details` WHERE `merchant_id`=".$merchantId." AND `updated_at` between '".$dateTimeFrom."' AND '".$dateTimeTo."'";
            $result = Data::sqlRecords($query, 'one');
            return isset($result['count'])?$result['count']:0; 
        }
    }

    public function getTempProductsCount($merchantId, $detail = false)
    {
        if(is_numeric($merchantId) && !$detail) 
        {
            $result = [];
            $query = "SELECT COUNT(*) as `count` FROM `jet_product_tmp` WHERE `merchant_id`=".$merchantId;
            $result = Data::sqlRecords($query, 'one','select');
            return isset($result['count'])?$result['count']:0; 
        }
        elseif(is_numeric($merchantId))
        {
            $result = $tmpProductIds = [];
            $query = "SELECT  `product_id` FROM `jet_product_tmp` WHERE `merchant_id`=".$merchantId;
            $result = Data::sqlRecords($query, 'all','select');
            if(is_array($result) && count($result)>0){
                $tmpProductIds = array_column($result, 'product_id');
            }
            return is_array($tmpProductIds)?$tmpProductIds:[];
        }
    }
    /**
     * get category not mapped Count
     * @param string $merchant_id
     * @return int
     */
    public static function getCategoryNotMappedCount($merchant_id)
    {
    	if (is_numeric($merchant_id)) {
    		$query = "SELECT count(*) as `count` FROM `sears_category_map` WHERE `merchant_id`=" . $merchant_id . "  AND `category_id`=0";
    		$result = Data::sqlRecords($query, 'one');
    
    		if (isset($result['count']) && $result['count']) {
    			self::$_categoryNotMappedCount = $result['count'];
    		}
    	}
    	return self::$_categoryNotMappedCount;
    }
}
?>