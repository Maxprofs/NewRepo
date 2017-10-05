<?php 
namespace frontend\modules\walmart\components\Dashboard;

use Yii;
use yii\base\Component;
use frontend\modules\walmart\components\Data;

class Setupprogress extends Component
{
    public $_walmartApiStatus = null;
    public $_productImportStatus = null;
    public $_categoryMapStatus = null;
    public $_attributeMapStatus = null;

    /**
     * To check if Walmart APi is activated or not
     * @param string $merchant_id
     * @return bool
     */
    public function getWalmartApiStatus($merchant_id)
    {
        if(is_null($this->_walmartApiStatus))
        {
            $this->_walmartApiStatus = false;
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `walmart_configuration` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');

                if(isset($result['count']) && $result['count']) {
                    $this->_walmartApiStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return $this->_walmartApiStatus;
        }
    }

    /**
     * To check if Products are imported or not
     * @param string $merchant_id
     * @return bool
     */
    public function getProductImportStatus($merchant_id)
    {
        if(is_null($this->_productImportStatus))
        {
            if(is_numeric($merchant_id)) {
                //$query = "SELECT COUNT(*) as `count` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=".$merchant_id." LIMIT 0,1";
                $query ="SELECT COUNT(*) as `count` FROM (SELECT * FROM `walmart_product` WHERE `merchant_id`='".$merchant_id."') as `walmart_product` INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`='".$merchant_id."') as `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`='".$merchant_id."' LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {

                    $this->_productImportStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return $this->_productImportStatus;
        }
    }

    /**
     * To check if Categories are Mapped or not
     * @param string $merchant_id
     * @return bool
     */
    public function getCategoryMapStatus($merchant_id)
    {
        if(is_null($this->_categoryMapStatus))
        {
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `walmart_category_map` WHERE `merchant_id`=".$merchant_id." AND  `category_id` != '' AND `category_id` IS NOT NULL LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {

                    $this->_categoryMapStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return $this->_categoryMapStatus;
        }
    }

    /**
     * To check if Attributes are Mapped or not
     * @param string $merchant_id
     * @return bool
     */
    public function getAttributeMapStatus($merchant_id)
    {
        if(is_null($this->_attributeMapStatus))
        {
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `walmart_attribute_map` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if(isset($result['count']) && $result['count']) {

                    $this->_attributeMapStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return $this->_attributeMapStatus;
        }
    }

    /**
     * To get Progress status of Profile
     * @param string $merchant_id
     * @return int
     */
    public function getProfileProgress($merchant_id)
    {
        $count = 0;
        if($this->getWalmartApiStatus($merchant_id))
            $count++;
        if($this->getProductImportStatus($merchant_id))
            $count++;
        if($this->getCategoryMapStatus($merchant_id))
            $count++;
        if($this->getAttributeMapStatus($merchant_id))
            $count++;

        $progress  = ($count*100)/4;
        return $progress;
    }
}
?>
