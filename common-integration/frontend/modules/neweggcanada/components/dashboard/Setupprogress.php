<?php 
namespace frontend\modules\neweggcanada\components\dashboard;

use Yii;
use yii\base\Component;
use frontend\modules\neweggcanada\components\Data;

class Setupprogress extends Component
{
    public $_apiStatus = null;
    public $_productImportStatus = null;
    public $_categoryMapStatus = null;
    public $_attributeMapStatus = null;

    /**
     * To check if APi is activated or not
     * @param string $merchant_id
     * @return bool
     */
    public function getApiStatus($merchant_id)
    {
        if(is_null($this->_apiStatus))
        {
            $this->_apiStatus = false;
            if(is_numeric($merchant_id)) {
                $query = "SELECT COUNT(*) as `count` FROM `newegg_can_configuration` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');

                if(isset($result['count']) && $result['count']) {
                    $this->_apiStatus = true;
                    return true;
                }
            }
            return false;
        }
        else
        {
            return $this->_apiStatus;
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
                $query = "SELECT COUNT(*) as `count` FROM `newegg_can_product` INNER JOIN `jet_product` ON `newegg_can_product`.`product_id`=`jet_product`.`id` WHERE `newegg_can_product`.`merchant_id`=".$merchant_id." LIMIT 0,1";
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
                $query = "SELECT COUNT(*) as `count` FROM `newegg_can_category_map` WHERE `merchant_id`=".$merchant_id." AND  `category_id` != '' AND `category_id` IS NOT NULL LIMIT 0,1";
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
                $query = "SELECT COUNT(*) as `count` FROM `newegg_can_attribute_map` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
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
        if($this->getApiStatus($merchant_id))
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
