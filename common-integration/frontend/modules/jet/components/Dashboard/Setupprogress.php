<?php 
namespace frontend\modules\jet\components\Dashboard;

use frontend\modules\jet\components\Data;
use yii\base\Component;

class Setupprogress extends Component
{
    public static $_testApiStatus = null;
    public static $_liveApiStatus = null;
    public static $_productImportStatus = null;
    public static $_categoryMapStatus = null;
    public static $_attributeMapStatus = null;

    /**
     * To check if Test APi is activated or not
     * @param string $merchant_id
     * @return bool
     */
    public static function getTestApiStatus($merchant_id)
    {
        if(is_null(self::$_testApiStatus))
        {
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `jet_test_api` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {
                    self::$_testApiStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return self::$_testApiStatus;
        }
    }

    /**
     * To check if Live APi is activated or not
     * @param string $merchant_id
     * @return bool
     */
    public static function getLiveApiStatus($merchant_id)
    {
        if(is_null(self::$_liveApiStatus))
        {
            self::$_liveApiStatus = false;
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `jet_configuration` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');

                if(isset($result['count']) && $result['count']) {
                    self::$_liveApiStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return self::$_liveApiStatus;
        }
    }

    /**
     * To check if Products are imported or not
     * @param string $merchant_id
     * @return bool
     */
    public static function getProductImportStatus($merchant_id)
    {
        if(is_null(self::$_productImportStatus))
        {
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `jet_product` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {

                    self::$_productImportStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return self::$_productImportStatus;
        }
    }

    /**
     * To check if Categories are Mapped or not
     * @param string $merchant_id
     * @return bool
     */
    public static function getCategoryMapStatus($merchant_id)
    {
        if(is_null(self::$_categoryMapStatus))
        {
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `jet_category_map` WHERE `merchant_id`=".$merchant_id." AND  `category_id`!=0 LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {

                    self::$_categoryMapStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return self::$_categoryMapStatus;
        }
    }

    /**
     * To check if Attributes are Mapped or not
     * @param string $merchant_id
     * @return bool
     */
    public static function getAttributeMapStatus($merchant_id)
    {
        if(is_null(self::$_attributeMapStatus))
        {
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `jet_attribute_map` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {

                    self::$_attributeMapStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return self::$_attributeMapStatus;
        }
    }

    /**
     * To get Progress status of Profile
     * @param string $merchant_id
     * @return int
     */
    public static function getProfileProgress($merchant_id)
    {
        $count = 0;
        if(self::getTestApiStatus($merchant_id))
            $count++;
        if(self::getLiveApiStatus($merchant_id))
            $count++;
        if(self::getProductImportStatus($merchant_id))
            $count++;
        if(self::getCategoryMapStatus($merchant_id))
            $count++;
        if(self::getAttributeMapStatus($merchant_id))
            $count++;

        $progress  = ($count*100)/5;
        return $progress;
    }
}
?>
