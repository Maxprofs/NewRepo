<?php
namespace frontend\modules\neweggcanada\components;

use Yii;
use yii\base\Component;
use frontend\modules\neweggcanada\components\Data;

class AttributeMap extends Component
{

    const VALUE_TYPE_SHOPIFY = 'map_with_shopify_option';
    const VALUE_TYPE_NEWEGG = 'predefined_newegg_attribute_value';
    const VALUE_TYPE_MIXED = 'predefined_newegg_attribute_value_map_with_shopify_option';
    const VALUE_TYPE_TEXT = 'text';

    /**
     * Get Shopify Product Types
     *
     * @param int|null $merchant_id
     * @return array
     */
    public static function getShopifyProductTypes($merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = MERCHANT_ID;

        $query = "SELECT `product_type`,`category_path`,`category_id` FROM `newegg_can_category_map` WHERE `category_path`!='' AND `merchant_id`=" . $merchant_id;
        $records = Data::sqlRecords($query, 'all');

        if ($records) {
            return $records;
        } else {
            return [];
        }
    }

    public static function getShopifyProductAttributes($product_type, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = MERCHANT_ID;

        $query = 'SELECT `attr_ids` FROM `jet_product` WHERE `product_type`="' . $product_type . '" AND `merchant_id`=' . $merchant_id;

        $records = Data::sqlRecords($query, 'all');

        $shopify_attributes = [];
        if ($records) {
            foreach ($records as $value) {
                if ($value['attr_ids'] != '') {
                    $attr_ids = json_decode(stripslashes($value['attr_ids']), true);
                    foreach ($attr_ids as $option_id => $attr_id) {
                        if (!in_array($attr_id, $shopify_attributes))
                            $shopify_attributes[] = $attr_id;
                    }
                }
            }
        }
        return $shopify_attributes;
    }

    public static function getAttributeMapValues($product_type, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = MERCHANT_ID;

        $query = 'SELECT `shopify_product_type`,`newegg_category_id`,`attribute_map_data`,`attribute_map` FROM `newegg_can_attribute_map` WHERE `shopify_product_type`="' . $product_type . '" AND `merchant_id`=' . $merchant_id;

        $records = Data::sqlRecords($query, 'one');
        $mappedData = json_decode($records['attribute_map_data'], true);
        $mapped_values = [];
        if($records)
        {
            if(isset($mappedData[$records['shopify_product_type']])){
                foreach ($mappedData[$records['shopify_product_type']] as $value) {
                    foreach ($value as $key => $val1) {
                        if($key != '') {
                            $mapped_values[$key] = ['value'=>$val1['value'], 'type'=>$val1['attribute_value_type']];
                        }
                    }
                }
            }
        }

        return $mapped_values;
    }


    public static function getAttributeMapData($product_type, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = MERCHANT_ID;
        $mapper = [];
        $query = 'SELECT `shopify_product_type`,`newegg_category_id`,`attribute_map_data`,`attribute_map` FROM `newegg_can_attribute_map` WHERE `shopify_product_type`="' . $product_type . '" AND `merchant_id`=' . $merchant_id;
        $records = Data::sqlRecords($query, 'one');
        $mappedData = json_decode($records['attribute_map'], true);
        if (!empty($mappedData)) {
            foreach ($mappedData as $k2 => $v2) {
                $attributeMapData = explode('->', $v2);

                $mapper[$attributeMapData[0]] = $attributeMapData[1];

            }
        }

        return $mapper;
    }

}
