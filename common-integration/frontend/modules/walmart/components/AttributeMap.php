<?php
namespace frontend\modules\walmart\components;

use Yii;
use yii\base\Component;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartAttributeMap;
use frontend\modules\walmart\components\WalmartCategory;
use frontend\modules\walmart\components\Walmartapi;

class AttributeMap extends Component
{
    const ATTRIBUTE_PATH_SEPERATOR = '->';

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

        $query = "SELECT `product_type`,`category_id`,`category_path` FROM `walmart_category_map` WHERE `category_id`!='' AND `merchant_id`=" . $merchant_id;
        $records = Data::sqlRecords($query, 'all');
        if ($records)
            return $records;
        else
            return [];
    }

    /**
     * Get Shopify Product Attributes/Options
     *
     * @param string $product_type
     * @param int|null $merchant_id
     * @return array
     */
    public static function getShopifyProductAttributes($product_type, $merchant_id = null)
    {
        if (is_null($merchant_id))
            $merchant_id = MERCHANT_ID;

        $query = 'SELECT `attr_ids`,`type` FROM `jet_product` WHERE `product_type`="' . addslashes($product_type) . '" AND `merchant_id`=' . $merchant_id;

        $records = Data::sqlRecords($query, 'all');

        $shopify_attributes = [];
        if ($records) {
            foreach ($records as $value) {
                if ($value['attr_ids'] != '') {
                    //$attr_ids = json_decode($value['attr_ids'], true);
                    $attr_ids = json_decode(stripslashes($value['attr_ids']), true);

                    if ($value['attr_ids'] != '' && !is_null($value['attr_ids'])) {
                        $attr_ids = json_decode(stripslashes($value['attr_ids']), true);
                        if ($value['type'] == 'variants') {
                            if (!empty($attr_ids)) {
                                foreach ($attr_ids as $option_id => $attr_id) {
                                    if (!in_array($attr_id, $shopify_attributes) && !is_numeric($attr_id))
                                        $shopify_attributes[] = $attr_id;
                                }
                            }

                        } elseif ($value['type'] == 'simple') {
                            if (!empty($attr_ids)) {
                                foreach ($attr_ids as $attr_id => $attr_value) {
                                    if (!in_array($attr_id, $shopify_attributes) && !is_numeric($attr_id))
                                        $shopify_attributes[] = $attr_id;
                                }
                            }

                        }
                    }
                }
            }
            return $shopify_attributes;
        }
    }

    /**
     * Get Walmart Category Attributes
     *
     * @param string $category_id
     * @return array|bool
     */
    /*public static function getWalmartCategoryAttributes1($category_id)
    {
        $session = Yii::$app->session;

        $index = self::getCatAttributeSessionIdx($category_id);
        if (!isset($session[$index])) {
            //new changes
            $additionalCategoryVariantAttrs = [
                "Animal" => ["size", "count"],
                "ArtAndCraft" => ["size"],
                "Baby" => ["size", "count"],
                "CarriersAndAccessories" => ["size"],
                "Clothing" => ["material/materialValue", "inseam", "hatSize", "count"],
                "Electronics" => ["digitalFileFormat", "physicalMediaFormat"],
                "FoodAndBeverage" => ["size"],
                "HealthAndBeauty" => ["size", "count", "flavor"],
                "Home" => ["size", "count"],
                "Jewelry" => ["ringSize"],
                "Media" => ["physicalMediaFormat"],
                "OccasionAndSeasonal" => ["count"],
                "Office" => ["count"],
                "SportAndRecreation" => ["size", "shoeSize", "sportsTeam", "sportsLeague"],
                "ToolsAndHardware" => ["count", "workingLoadLimit", "size"],
                "Toy" => ["size", "count", "flavor"]
            ];
            //end

            $query = 'SELECT `title`,`parent_id`,`attributes`,`attribute_values`,`walmart_attributes`,`walmart_attribute_values` FROM `walmart_category` WHERE `category_id`="' . $category_id . '" LIMIT 0,1';
            $records = Data::sqlRecords($query, 'one');

            if ($records) {
                $attributes = [];
                $required = [];
                if ($records['attributes'] != '') {
                    $_attributes = json_decode($records['attributes'], true);

                    foreach ($_attributes as $_value) {
                        if (is_array($_value)) {
                            $key = key($_value);

                            $attr_id = $key;
                            $sub_attr = reset($_value);
                            if (is_array($sub_attr)) {
                                foreach ($sub_attr as $wal_attr_code) {
                                    if ($wal_attr_code != $key) {
                                        $attr_id .= self::ATTRIBUTE_PATH_SEPERATOR . $wal_attr_code;
                                    }
                                }
                            }
                            $attributes[$attr_id] = $_value[$key];
                            $required[] = $attr_id;
                        } else {
                            $attributes[$_value] = $_value;
                            $required[] = $_value;
                        }
                    }
                }
                $categoryId = $category_id;
                if ($records['parent_id'] != '0')
                    $categoryId = $records['parent_id'];

                $attrs = Walmartapi::isValidateVariant($categoryId);

                if ($records['walmart_attributes'] != '') {
                    $optionalAttrs = explode(',', $records['walmart_attributes']);
                    foreach ($attrs as $attr) {
                        foreach ($optionalAttrs as $optionalAttrsKey => $optionalAttrsValue) {
                            $attrCode = trim(str_replace('/', '->', $optionalAttrsValue));
                            $keys = explode('/', $optionalAttrsValue);
                            if (in_array($attr, $keys)) {
                                if (count($keys) == 1)
                                    $attributes[$attrCode] = $keys[0];
                                else
                                    $attributes[$attrCode] = $keys;

                                unset($optionalAttrs[$optionalAttrsKey]);
                            } elseif (isset($additionalCategoryVariantAttrs[$categoryId]) && in_array($attr, $additionalCategoryVariantAttrs[$categoryId])) {
                                if (!isset($attributes[$attr])) {
                                    $attributes[$attr] = $attr;
                                }
                            }
                        }
                    }
                }

                $attribute_values = [];
                if ($records['attribute_values'] != '') {
                    $_attributeValues = json_decode($records['attribute_values'], true);

                    foreach ($_attributeValues as $_attrValue) {
                        if (is_array($_attrValue)) {
                            $key = key($_attrValue);
                            $attribute_values[$key] = $_attrValue[$key];
                        } else {
                            $attribute_values[$_attrValue] = $_attrValue;
                        }
                    }
                }
                if ($records['walmart_attribute_values'] != '') {
                    $_attributeValues = json_decode($records['walmart_attribute_values'], true);
                    foreach ($_attributeValues as $_attrValue) {
                        if (is_array($_attrValue)) {
                            $key = key($_attrValue);
                            $attribute_values[$key] = $_attrValue[$key];
                        } else {
                            $attribute_values[$_attrValue] = $_attrValue;
                        }
                    }
                }

                if ($records['parent_id']) {
                    $parentAttributes = self::getWalmartCategoryAttributes($records['parent_id']);
                    if ($parentAttributes) {
                        $attributes = array_merge($attributes, $parentAttributes['attributes']);
                        $attribute_values = array_merge($attribute_values, $parentAttributes['attribute_values']);
                    }
                }

                self::addUnitAttributeValues($attribute_values);

                $data = ['attributes' => $attributes, 'attribute_values' => $attribute_values, 'required_attrs' => $required, 'parent_id' => $records['parent_id']];

                $session->set($index, $data);
                $session->close();

                return $data;
            }
            return false;
        } else {
            unset($session[$index]);
            return $session[$index];
        }
    }*/

    public static function getWalmartCategoryAttributes($category_id, $parent_id)
    {
        /*$session = Yii::$app->session;

        $index = self::getCatAttributeSessionIdx($category_id);
        if (!isset($session[$index])) {*/
        //new changes
        $required = [];
        $attribute_code = [];
        $attribute_options = [];
        $variant_attributes=[];

        $requiredsubCategoryAttributes = Category::getSubCategoryAttributes($parent_id, $category_id, true);

        foreach ($requiredsubCategoryAttributes as $value) {
            $attribute_codes = Category::getAttributeCode($value);

            foreach ($attribute_codes as $item)
            {
                $attribute_code[$item] = $item;
            }


            $required[] = $value['name'];

            $attributeOptions = Category::getAttributeOptions($value);

            $attribute_options = array_merge($attribute_options,$attributeOptions);
        }

        $categoryVariantAttributes = Category::getCategoryVariantAttributes($parent_id);

        $subCategoryAttributes = Category::getCategoryAttributes($parent_id, false);

        foreach ($categoryVariantAttributes as $key => $values) {
            if (array_key_exists($values, $subCategoryAttributes)) {

                $attribute_codes = Category::getAttributeCode($subCategoryAttributes[$values]);

                foreach ($attribute_codes as $item)
                {
                    $attribute_code[$item] = $item;
                    $variant_attributes[$item] = $item;
                }

                $attributeOptions = Category::getAttributeOptions($subCategoryAttributes[$values]);

                $attribute_options = array_merge($attribute_options,$attributeOptions);

            }

        }


        /*if(is_array($attribute_options))
        {
            foreach ($attribute_options as $key => $value)
            {
                $attribute_options[$key] = implode(',',$value);
            }
        }*/

        $data = ['attributes' => $attribute_code, 'attribute_values' => $attribute_options, 'variant_attributes'=>$variant_attributes,'required_attrs' => $required, 'parent_id' => $parent_id];

        /*$session->set($index, $data);
        $session->close();*/

        return $data;

        /*} else {
            unset($session[$index]);
            return $session[$index];
        }*/
    }


    /**
     * Add Attribute Values For Unit Type Attributes
     *
     * @param &$attributeValues
     */
    private static function addUnitAttributeValues(&$attributeValues)
    {
        $unitAttrValues = [
            'chainLength->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'pantSize->waistSize->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'waistSize->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'screenSize->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'ramMemory->unit' => 'Terabytes,Kibibytes,Mebibytes,Gibibytes,Kilobytes,Gigabytes,Tebibytes,Megabyte',
            'hardDriveCapacity->unit' => 'Terabytes,Kibibytes,Mebibytes,Gibibytes,Kilobytes,Gigabytes,Tebibytes,Megabyte',
            'cableLength->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'heelHeight->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'carats->unit' => 'Carat',
            'focalLength->unit' => 'Inches,Micrometers,Feet,Millimeters,Centimeters,Meters,Yards,French,Miles,Mil',
            'displayResolution->unit' => 'Dots Per Square Inch,Pixels Per Inch,Volumetric Pixels,Megapixels,Resolution Element,Surface Element,Dots Per Inch,Texels',
            'volts->unit' => 'Volts',
            'amps->unit' => 'Amps',
            'gallonsPerMinute->unit' => '',
            'minimumWeight->unit' => 'Kilograms Per Meter,Kilograms,Milligrams,Ounces,Pounds,Grams,Carat',
            'maximumWeight->unit' => 'Kilograms Per Meter,Kilograms,Milligrams,Ounces,Pounds,Grams,Carat'
        ];

        $attributeValues = array_merge($attributeValues, $unitAttrValues);
    }

    /**
     * get Saved Values of Attribute Mapping
     *
     * @param string $product_type
     * @param int|null $merchant_id
     * @return array
     */
    public static function getAttributeMapValues($product_type, $merchant_id = null)
    {
        $session = Yii::$app->session;

        if (is_null($merchant_id))
            $merchant_id = MERCHANT_ID;

        $main_index = self::getAttrMapSessionMainIdx($merchant_id);
        $index = self::getAttrMapSessionSubIdx($product_type);
        if (!isset($session[$main_index][$index])) {
            $query = 'SELECT `walmart_attribute_code`,`attribute_value_type`,`attribute_value` FROM `walmart_attribute_map` WHERE `shopify_product_type`="' . addslashes($product_type) . '" AND `merchant_id`=' . $merchant_id;

            $records = Data::sqlRecords($query, 'all');

            $mapped_values = [];
            if ($records) {
                foreach ($records as $value) {
                    if ($value['attribute_value'] != '') {
                        $mapped_values[$value['walmart_attribute_code']] = ['type' => $value['attribute_value_type'], 'value' => $value['attribute_value']];
                    }
                }
            }

            $old_session_data = $session[$main_index];
            $old_session_data[$index] = $mapped_values;
            $session->set($main_index, $old_session_data);
            $session->close();

            return $mapped_values;
        } else {
            return $session[$main_index][$index];
        }
    }

    /**
     * Get all Mappings for Shopify Attributes
     *
     * @param string $product_type
     * @param int|null $merchant_id
     * @return array
     */
    public static function getMappedWalmartAttributes($shopify_product_type, $option_id, $merchant_id = null)
    {
        $mapped_attributes = [];
        $attribute_values = [];
        $common_attributes = [];

        $attributeMapValues = self::getAttributeMapValues($shopify_product_type);
        foreach ($attributeMapValues as $walAttrCode => $walAttrValue) {
            if ($walAttrValue['type'] == WalmartAttributeMap::VALUE_TYPE_SHOPIFY) {
                $mapped_attributes[$walAttrCode] = $walAttrValue['value'];
            } elseif ($walAttrValue['type'] == WalmartAttributeMap::VALUE_TYPE_TEXT ||
                $walAttrValue['type'] == WalmartAttributeMap::VALUE_TYPE_WALMART
            ) {
                $common_attributes[$walAttrCode] = $walAttrValue['value'];
            }
        }

        $shopify_attribute_map = [];
        $productOptionValues = self::getOptionValuesForProduct($option_id);
        foreach ($productOptionValues as $key => $value) {
            foreach ($mapped_attributes as $wal_attr => $map_value) {
                if (in_array($key, explode(',', $map_value))) {
                    $shopify_attribute_map[$key] = [$wal_attr];
                    $attribute_values[$wal_attr] = $value;
                }
            }
        }

        if (count($shopify_attribute_map))
            $shopify_attribute_map = json_encode($shopify_attribute_map);
        else
            $shopify_attribute_map = '';

        if (count($attribute_values))
            $attribute_values = json_encode($attribute_values);
        else
            $attribute_values = '';

        if (count($common_attributes))
            $common_attributes = json_encode($common_attributes);
        else
            $common_attributes = '';

        return [
            'mapped_attributes' => $shopify_attribute_map,
            'attribute_values' => $attribute_values,
            'common_attributes' => $common_attributes
        ];
    }

    /**
     * Get Shopify Option Values for Product
     *
     * @param int $product_option_id
     * @return array
     */
    public static function getOptionValuesForProduct($product_option_id)
    {
        //$query = "SELECT `jp`.`attr_ids`,`jpv`.`variant_option1`,`jpv`.`variant_option2`,`jpv`.`variant_option3` FROM `jet_product_variants` `jpv` INNER JOIN `jet_product` `jp` ON `jp`.`id`=`jpv`.`product_id` WHERE `jpv`.`option_id`=".$product_option_id." LIMIT 0,1";
        $query = "SELECT `jp`.`attr_ids`,`jpv`.`variant_option1`,`jpv`.`variant_option2`,`jpv`.`variant_option3` FROM (SELECT * FROM `jet_product_variants` WHERE `option_id` = '" . $product_option_id . "') as `jpv` INNER JOIN `jet_product` `jp` ON `jp`.`id`=`jpv`.`product_id` WHERE `jpv`.`option_id`=" . $product_option_id . " LIMIT 0,1";

        $records = Data::sqlRecords($query, 'one');

        $values = [];
        if ($records) {
            //$shopify_attributes = json_decode($records['attr_ids'], true);
            $shopify_attributes = json_decode(stripslashes($records['attr_ids']), true);

            if (is_array($shopify_attributes))
                $shopify_attributes = array_values($shopify_attributes);

            foreach ($shopify_attributes as $key => $attr) {
                $values[$attr] = $records['variant_option' . ($key + 1)];
            }
        }
        return $values;
    }

    /**
     * To check if attributes are mapped for the given shopify product type
     *
     * @param int $product_option_id
     * @return array
     */
    public static function isProductTypeAttributeMapped($product_type)
    {
        $query = 'SELECT `id` FROM `walmart_attribute_map` WHERE `shopify_product_type`="' . addslashes($product_type) . '" LIMIT 0,1';

        $records = Data::sqlRecords($query, 'one');

        if ($records)
            return true;
        else
            return false;
    }

    public static function getAttrMapSessionMainIdx($merchant_id)
    {
        $index = 'walmart_attribute_map_' . $merchant_id;
        return $index;
    }

    public static function getAttrMapSessionSubIdx($product_type)
    {
        $index = 'attribute_map_value_for_' . addslashes($product_type);
        return $index;
    }

    public static function getCatAttributeSessionIdx($category_id)
    {
        $index = 'walmart_cat_attributes_for_' . addslashes($category_id);
        return $index;
    }

    public static function unsetAttrMapSession()
    {
        $session = Yii::$app->session;
        $main_index = self::getAttrMapSessionMainIdx(MERCHANT_ID);
        $session->remove($main_index);
        $session->close();
    }

    public static function getAttributeType($attribute_code, $available_attribute_values)
    {
        $result = false;

        if (is_array($available_attribute_values) && count($available_attribute_values)) {
            if (isset($available_attribute_values[$attribute_code])) {
//                $result = explode(',', $available_attribute_values[$attribute_code]);
                $result = $available_attribute_values[$attribute_code];
            } else {
                foreach ($available_attribute_values as $attribute_value) {
                    if (isset($attribute_value[$attribute_code])) {
//                        $result = explode(',', $attribute_value[$attribute_code]);
                        $result = $attribute_value[$attribute_code];
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get the saved mapping for any attribute for indivisual product. If any attribute is mapped through "Attribute Mapping" & "Product Edit"
     * then "Product Edit" will override "Attribute Mapping"
     *
     * @param string $attribute_code "attribute code of attribute"
     * @param array $common_attr_values "Mapped data through 'Product Edit' for indivisual product"
     * @param array $saved_attribute_mapping "global attribute mapping for the product type"
     * @return string
     */
    public static function getSavedAttributeValue($attribute_code, $common_attr_values, $saved_attribute_mapping)
    {
        $savedValue = '';

        if(isset($common_attr_values[$attribute_code])) {
            $savedValue = $common_attr_values[$attribute_code];
        }
        elseif(isset($saved_attribute_mapping[$attribute_code])) {
            $savedValue = $saved_attribute_mapping[$attribute_code]['value'];
        }

        return $savedValue;
    }
}
