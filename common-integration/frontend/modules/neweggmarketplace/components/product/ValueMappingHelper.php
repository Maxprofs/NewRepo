<?php
namespace frontend\modules\neweggmarketplace\components\product;
use frontend\modules\neweggmarketplace\components\Data;
use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;

class ValueMappingHelper extends Component
{
  /*
    product basic info prepare

    */
    public static function getCategoryaArribute($val)
    {
        $array = [];
        $valData = explode(',',$val);
        $data=Data::sqlRecords("SELECT `attribute_map` FROM `newegg_attribute_map` WHERE `merchant_id`='".MERCHANT_ID."' AND `shopify_product_type`='".$valData[0]."'","one","select");
        $data = json_decode($data['attribute_map'],true);
        foreach ($data as $key => $value) {
            $shopify_attribute = explode('->', $value);
            $allval = explode(',',$shopify_attribute[1]);
            if($shopify_attribute[0] == $valData[2]){
                foreach ($allval as $allkey => $allvalue) {
                       if(!in_array($allvalue, $array)){
                    $array[]= $allvalue;
                }
                }
               
            }
              
        }
        return (json_encode($array));
        
        
    }

    public static function getNeweggCategoryaArribute($val)
    {
        $array = [];
        $data=Data::sqlRecords("SELECT `attribute_map` FROM `newegg_attribute_map` WHERE `merchant_id`='".MERCHANT_ID."' AND `shopify_product_type`='".$val."'","one","select");
        $data = json_decode($data['attribute_map'],true);
        foreach ($data as $key => $value) {
            $shopify_attribute = explode('->', $value);
            $allval = explode(',',$shopify_attribute[0]);
            if($shopify_attribute[0]){
                foreach ($allval as $allkey => $allvalue) {
                       if(!in_array($allvalue, $array)){
                    $array[]= $allvalue;
                }
                }
               
            }
              
        }
        return (json_encode($array));
        
    }

        public static function getAttributeValue($val)
    {
        $array = [];
        $valData = explode(',',$val);
        $categoryQuery = "SELECT category_path FROM `newegg_category_map` WHERE product_type='".$valData[0]."' AND category_id='".$valData[1]."' AND merchant_id='".MERCHANT_ID."'";
        $categoryData = Data::sqlRecords($categoryQuery,'one','select');
        $categoryData = explode(',',$categoryData['category_path']);
        $neweggDataValue = Categoryhelper::subcategoryAttributeValue($valData[1],$categoryData[1],$valData[2]);
        $optionValue = $neweggDataValue['PropertyValueList'];
        $query = "SELECT `value` FROM `value_attribute_mapping` WHERE `merchant_id`='".MERCHANT_ID."' AND product_type='".$valData[0]."' AND category_id='".$valData[1]."' AND attribute_name='".$valData[3]."'";
        $valueData = Data::sqlRecords($query,'all','select');
       // $valArray = json_decode($valueData,true);
        foreach ($valueData as $key => $value) {
            $valArray = json_decode($value['value'],true);
            $array = [];
            foreach ($valArray as $key => $value) {
                $array[$key]=['att_value'=>$key,'option_value'=>json_encode($optionValue)];
            }
        }
        return (json_encode($array));
    }



    public static function getMappedData($val)
    {
        $data=Data::sqlRecords("SELECT `mapped_value_data` FROM `newegg_value_mapping` WHERE `shopify_product_type`='".$val."' AND `merchant_id`='".MERCHANT_ID."'","one","select");

        return $data['mapped_value_data'];
    }

}