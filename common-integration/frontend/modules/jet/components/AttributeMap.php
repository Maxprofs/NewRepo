<?php 
namespace frontend\modules\jet\components;

use Yii;
use yii\base\Component;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\models\JetAttributeMap;

class AttributeMap extends Component
{
    const ATTRIBUTE_PATH_SEPERATOR = '->';

    /**
     * Get Shopify Product Types
     *
     * @param int|null $merchant_id
     * @return array
     */
    public static function getShopifyProductTypes($merchant_id=null)
    {
        $records = [];
        // $query = "SELECT `jcm`.`product_type`,`jcm`.`category_id` FROM `jet_category_map` `jcm` INNER JOIN `jet_product` `jp` ON `jcm`.`product_type`=`jp`.`product_type`  where `jcm`.`merchant_id`='".$merchant_id."' AND `jcm`.`category_id`!=0 AND `jp`.`type`='variants' GROUP BY `jp`.`product_type` ";
        $query = "SELECT `jcm`.`product_type`,`jcm`.`category_id` FROM `jet_category_map` `jcm` INNER JOIN `jet_product` `jp` ON `jcm`.`merchant_id`='".$merchant_id."' AND `jcm`.`category_id`!=0 AND `jp`.`type`='variants' AND `jcm`.`product_type`=`jp`.`product_type`  GROUP BY `jp`.`product_type`";                
        $records = Data::sqlRecords($query, 'all','select');
        return $records;       
    }

    /**
     * Get Jet Category Attributes
     *
     * @param string $category_id
     * @return array|bool
     */
    public static function getJetCategoryAttributes($category_node_id, $getFromSession=true, $merchant_id=null)
    {
        $session = Yii::$app->session;
        $index = 'node_id_'.$category_node_id;
        //if(!$session[$index] && $getFromSession)
        {
            $categoryAttributes=[];
            if(API_USER)
            {  
                $fullfillmentnodeid = FULLFILMENT_NODE_ID;
                $api_host = API_HOST;
                $api_user = API_USER;
                $api_password = API_PASSWORD;
                $jetHelper = new Jetapimerchant($api_host,$api_user,$api_password);
                $response = $jetHelper->CGetRequest('/taxonomy/nodes/'.$category_node_id.'/attributes',$merchant_id);
                $attributes = json_decode($response,true);
                $sizeFound=false;
                $colorFound=false;
                if(is_array($attributes) && isset($attributes['attributes']))
                {
                    $attributes = $attributes['attributes'];
                    foreach ($attributes as $attribute) {
                        if(isset($attribute['units']))
                            continue;
                        if($attribute['attribute_id']==50)
                            $sizeFound=true;
                        elseif($attribute['attribute_id']==119 || strcasecmp($attribute['attribute_description'], 'color') == 0)
                            $colorFound=true;
                        $categoryAttributes['attributes'][$attribute['attribute_id']]['title'] = $attribute['attribute_description'];
                        $categoryAttributes['attributes'][$attribute['attribute_id']]['free_text'] = $attribute['free_text'];
                        $categoryAttributes['attributes'][$attribute['attribute_id']]['variant'] = $attribute['variant'];

                        //if(isset($attribute['values']))
                            //$categoryAttributes['attribute_values'][$attribute['attribute_id']] = $attribute['values'];
                    }
                }
                if(!$sizeFound)
                {
                    $categoryAttributes['attributes'][50]['title'] = 'Size';
                    $categoryAttributes['attributes'][50]['free_text'] = true;
                    $categoryAttributes['attributes'][50]['variant'] = true;
                }
                if(!$colorFound)
                {
                    $categoryAttributes['attributes'][119]['title'] = 'Color';
                    $categoryAttributes['attributes'][119]['free_text'] = true;
                    $categoryAttributes['attributes'][119]['variant'] = true;
                }
            }
            $session->set($index, $categoryAttributes);
            $session->close();
        }
        return $session[$index];
    }

    /**
     * Get Shopify Product Attributes
     *
     * @param string $product_type
     * @param int|null $merchant_id
     * @return array
     */
    public static function getShopifyProductAttributes($product_type, $merchant_id=null)
    {
    	if (is_null($merchant_id))
    		$merchant_id = Yii::$app->user->identity->id;
        $query = 'SELECT `attr_ids` FROM `jet_product` WHERE `merchant_id`="'.$merchant_id.'" AND `type`="variants" AND `product_type`="'.addslashes($product_type).'" ';
        
        $records = Data::sqlRecords($query, 'all');
        
        $shopify_attributes = [];
        if($records)
        {
            foreach ($records as  $value) 
            {
                if($value['attr_ids'] != '') 
                {
                    $attr_ids = json_decode($value['attr_ids'], true);
                    if(is_array($attr_ids))
                    {
                       foreach ($attr_ids as $option_id => $attr_id) 
                        {
                            if(!in_array($attr_id, $shopify_attributes))
                                $shopify_attributes[] = $attr_id;
                        }
                    }
                }
            }
        }
        
        return $shopify_attributes;
    }

    /**
     * get Saved Values of Attribute Mapping
     *
     * @param string $product_type
     * @param int|null $merchant_id
     * @return array
     */
    public static function getAttributeMapValues($product_type, $merchant_id=null)
    {       
        $query = 'SELECT `jet_attribute_id`,`attribute_value_type`,`attribute_value` FROM `jet_attribute_map` WHERE `merchant_id`="'.$merchant_id.'" AND `shopify_product_type`="'.addslashes($product_type).'" ';
        
        $records = Data::sqlRecords($query, 'all','select');

        $mapped_values = [];
        if($records)
        {
            foreach ($records as $value) 
            {
                if($value['attribute_value'] != '') 
                {
                    $mapped_values[$value['jet_attribute_id']] = ['type'=>$value['attribute_value_type'], 'value'=>$value['attribute_value']];
                }
            }
        }
        return $mapped_values;
    }

    public static function getUnitAttributeValues()
    {
        return 'meter,centi meter,inches';
    }

    /**
     * Get Shopify Option Values for Product
     *
     * @param int $product_option_id
     * @param string json of shopify_options
     * @return array
     */
    public static function getMappedOptionValues($product_option_id, $shopify_options, $product_type, $merchant_id=null)
    {
        
        $jet_attribute_values = [];
        $shopify_option_values = self::getOptionValuesForProduct($product_option_id, $shopify_options);
        $shopify_options = json_decode($shopify_options, true);
        if(is_array($shopify_options))
        {
            $shopify_options = array_values($shopify_options);
            foreach ($shopify_options as $shopify_option) 
            {
                $query = "SELECT `attribute_value_type`,`attribute_value`,`jet_attribute_id` FROM `jet_attribute_map` WHERE `merchant_id`=".$merchant_id." AND `shopify_product_type`='".addslashes($product_type)."' AND `attribute_value` LIKE '%".$shopify_option."%'";
                $records = Data::sqlRecords($query, 'all');

                if($records)
                {
                    foreach ($records as $record)
                    {
                        if(isset($record['attribute_value_type']) && isset($record['attribute_value']))
                        {
                            $mapped_attributes = explode(',', $record['attribute_value']);
                            if(in_array($shopify_option, $mapped_attributes))
                            {
                                if($record['attribute_value_type']==JetAttributeMap::VALUE_TYPE_TEXT || $record['attribute_value_type']==JetAttributeMap::VALUE_TYPE_JET) 
                                {
                                    $jet_attribute_values[$record['jet_attribute_id']] = $record['attribute_value'];
                                }
                                elseif($record['attribute_value_type']==JetAttributeMap::VALUE_TYPE_SHOPIFY)
                                {
                                    $jet_attribute_values[$record['jet_attribute_id']] = $shopify_option_values[$shopify_option];
                                }
                            }
                        }  
                    }
                }
            }
        }
        return $jet_attribute_values;
    }

    /**
     * Get Shopify Option Values for Product Variant
     *
     * @param int $product_option_id
     * @param string json of $shopify_options
     * @return array
     */
    public static function getOptionValuesForProduct($product_option_id, $shopify_options)
    {
        $query = "SELECT `variant_option1`,`variant_option2`,`variant_option3` FROM `jet_product_variants` WHERE `option_id`=".$product_option_id." LIMIT 0,1";

        $records = Data::sqlRecords($query, 'one');

        $values = [];
        if($records)
        {
            $shopify_attributes = json_decode($shopify_options, true);
            if(is_array($shopify_attributes))
                $shopify_attributes = array_values($shopify_attributes);
			
            if (!empty($shopify_attributes))
            {
            	foreach ($shopify_attributes as $key=>$attr) {
            		$values[$attr] = $records['variant_option'.($key+1)];
            	}	
            }            
        }
        return $values;
    }
    public static function getAttributes()
    {
        $attributes = [];
        $shopify_product_types = self::getShopifyProductTypes();
        foreach ($shopify_product_types as $type_arr)
        {
            $product_type = $type_arr['product_type'];
            $CategoryNodeId = $type_arr['category_id'];

            $categoryAttributes = [];
            if(is_null($CategoryNodeId)) {
                continue;
            }
            $categoryAttributes = self::getJetCategoryAttributes($CategoryNodeId)?:[];
            if(count($categoryAttributes))
            {
                $shopifyAttributes = self::getShopifyProductAttributes($product_type);
                $mapped_values = self::getAttributeMapValues($product_type);
                $attributes[$product_type] = [
                                                'product_type' => $product_type,
                                                'jet_category_id' => $CategoryNodeId,
                                                'category_attributes' => $categoryAttributes,
                                                'shopify_attributes' => $shopifyAttributes,
                                                'mapped_values' => $mapped_values
                                            ];
            }
        }
        return $attributes;
    }
}
