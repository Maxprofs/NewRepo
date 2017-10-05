<?php 
namespace frontend\modules\neweggmarketplace\components\Dashboard;

use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\models\NeweggProduct;

class Productinfo extends Component
{
    public static $_totalProducts = null;
    public static $_publishedProducts = null;
    public static $_unpublishedProducts = null;
    public static $_stageProducts = null;
    public static $_notUploadedProducts = null;
    public static $_processingProducts = null;

    /**
     * get Total Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getTotalProducts($merchant_id)
    {
        if(is_null(self::$_totalProducts))
        {
            self::$_totalProducts = 0;

            if(is_numeric($merchant_id)) {

                //$query = "select count(*) as `count` from (SELECT * from `newegg_product` where `merchant_id`=".$merchant_id." ) as `neweggproduct` LEFT JOIN (select * from `newegg_product_variants` where `merchant_id`=".$merchant_id." ) as `neweggvariantprod` ON `neweggproduct`.`product_id` = `neweggvariantprod`.`product_id` LIMIT 0,1";
                $query = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple') UNION (SELECT `newegg_product_variants`.`option_id` AS `variant_id` FROM `newegg_product_variants` INNER JOIN `newegg_product` ON `newegg_product_variants`.`product_id` = `newegg_product`.`product_id` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=" . $merchant_id . " )) as `merged_data`";

                $result = Data::sqlRecords($query, 'one');

                if(isset($result['count']) && $result['count']) {
                    self::$_totalProducts = $result['count'];
                }
            }
        }
        return self::$_totalProducts;
    }

    /**
     * get Published Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getActivatedProducts($merchant_id)
    {
        if(is_null(self::$_publishedProducts))
        {
            self::$_publishedProducts = 0;

            if(is_numeric($merchant_id)) {

                $query = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$merchant_id." AND `newegg_product`.`upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$merchant_id." AND `newegg_product_variants`.`upload_status`='".Data::PRODUCT_STATUS_ACTIVATED."')) as `merged_data`";

                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {
                    self::$_publishedProducts = $result['count'];
                }
            }
        }
        return self::$_publishedProducts;
    }

    /**
     * get Unpublished Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getDeactivatedProducts($merchant_id)
    {
        if(is_null(self::$_unpublishedProducts))
        {
            self::$_unpublishedProducts = 0;

            if(is_numeric($merchant_id)) {

                $query = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$merchant_id." AND `newegg_product`.`upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$merchant_id." AND `newegg_product_variants`.`upload_status`='".Data::PRODUCT_STATUS_DEACTIVATED."')) as `merged_data`";

                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {
                    self::$_unpublishedProducts = $result['count'];
                }
            }
        }
        return self::$_unpublishedProducts;
    }

    /**
     * get Staged Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getErrorProducts($merchant_id)
    {
        if(is_null(self::$_stageProducts))
        {
            self::$_stageProducts = 0;

            if(is_numeric($merchant_id)) {

                $query = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$merchant_id." AND `newegg_product`.`upload_status`='".Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR."') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$merchant_id." AND `newegg_product_variants`.`upload_status`='".Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR."')) as `merged_data`";

                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {
                    self::$_stageProducts = $result['count'];
                }
            }
        }
        return self::$_stageProducts;
    }

    /**
     * get Not Uploaded Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getNotUploadedProducts($merchant_id)
    {
        if(is_null(self::$_notUploadedProducts))
        {
            self::$_notUploadedProducts = 0;

            if(is_numeric($merchant_id)) {

                $query = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=" . $merchant_id . " AND `newegg_product`.`upload_status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "' AND `jet_product`.`type`='simple' ) UNION (SELECT `newegg_product_variants`.`option_id` AS `variant_id` FROM `newegg_product_variants` INNER JOIN `newegg_product` ON `newegg_product_variants`.`product_id` = `newegg_product`.`product_id` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=" . $merchant_id . " AND `newegg_product_variants`.`upload_status`='" . Data::PRODUCT_STATUS_NOT_UPLOADED . "')) as `merged_data`";

                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {
                    self::$_notUploadedProducts = $result['count'];
                }
            }
        }
        return self::$_notUploadedProducts;
    }

    /**
     * get Processing Products Count
     * @param string $merchant_id
     * @return int
     */
    public static function getSubmittedProducts($merchant_id)
    {
        if(is_null(self::$_processingProducts))
        {
            self::$_processingProducts = 0;

            if(is_numeric($merchant_id)) {

                $query = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$merchant_id." AND `newegg_product`.`upload_status`='".Data::PRODUCT_STATUS_SUBMITTED."') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$merchant_id." AND `newegg_product_variants`.`upload_status`='".Data::PRODUCT_STATUS_SUBMITTED."')) as `merged_data`";

                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {
                    self::$_processingProducts = $result['count'];
                }
            }
        }
        return self::$_processingProducts;
    }

    public function getProductsCountUpdatedToday($merchantId){
        if(is_numeric($merchantId)) {
                $result = [];
                $dateTimeFrom = date('Y-m-d 00:00:00');
                $dateTimeTo = date('Y-m-d 23:59:59');
                $query = "SELECT COUNT(*) as `count` FROM `jet_product` WHERE `merchant_id`=".$merchantId." AND `updated_at` between '".$dateTimeFrom."' AND '".$dateTimeTo."'";
                $result = Data::sqlRecords($query, 'one');
                return isset($result['count'])?$result['count']:0; 
        }

    }

    /*public function getTempProductsCount($merchantId, $detail = false){
        if(is_numeric($merchantId) && !$detail) {
                $result = [];
                $query = "SELECT COUNT(*) as `count` FROM `jet_product_tmp` WHERE `merchant_id`=".$merchantId;
                $result = Data::sqlRecords($query, 'one');
                return isset($result['count'])?$result['count']:0;
        }elseif(is_numeric($merchantId)){
                $result = [];
                $tmpProductIds = [];
                $query = "SELECT  `product_id` FROM `jet_product_tmp` WHERE `merchant_id`=".$merchantId;
                $result = Data::sqlRecords($query, 'all');
                if(is_array($result) && count($result)>0){
                    $tmpProductIds = array_column($result, 'product_id');
                }
                return is_array($tmpProductIds)?$tmpProductIds:[];
        }

    }*/

    
}
?>
