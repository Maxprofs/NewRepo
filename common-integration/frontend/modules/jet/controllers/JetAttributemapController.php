<?php
namespace frontend\modules\jet\controllers;

use Yii;

use frontend\modules\jet\components\AttributeMap;
use frontend\modules\jet\models\JetAttributeMap;
use frontend\modules\jet\components\Data;

class JetAttributemapController extends JetmainController
{
    public function actionIndex()
    {
        $merchant_id = MERCHANT_ID;
        $attributes = [];
        $shopify_product_types = AttributeMap::getShopifyProductTypes($merchant_id);

        foreach ($shopify_product_types as $type_arr)
        {
            $product_type = $type_arr['product_type'];
            $CategoryNodeId = $type_arr['category_id'];

            $categoryAttributes = [];
            if(is_null($CategoryNodeId)) {
                continue;
            }

            $categoryAttributes = AttributeMap::getJetCategoryAttributes($CategoryNodeId)?:[];
            if(count($categoryAttributes))
            {
                $shopifyAttributes = AttributeMap::getShopifyProductAttributes($product_type,$merchant_id);
                $mapped_values = AttributeMap::getAttributeMapValues($product_type,$merchant_id);
                $attributes[$product_type] = [
                                                'product_type' => $product_type,
                                                'jet_category_id' => $CategoryNodeId,
                                                'category_attributes' => $categoryAttributes,
                                                'shopify_attributes' => $shopifyAttributes,
                                                'mapped_values' => $mapped_values
                                            ];
            }
        }
        //print_r($attributes);die;
        return $this->render('index',['attributes'=>$attributes]);
    }

    public function actionSave()
    {
        $data = Yii::$app->request->post();
        if($data && isset($data['jet']))
        {
            $merchant_id = MERCHANT_ID;
            $insert_value = [];
            foreach($data['jet'] as $key => $value)
            {
                $shopifyProductType = addslashes($key);
                foreach ($value as $walmart_attr => $value) {
                    $jetAttrId = $walmart_attr;
                    $attrValueType = '';
                    $attrValue = '';
                    if(is_array($value)) {
                        if(count($value) > 1) {
                            unset($value['text']);
                            $attrValueType = JetAttributeMap::VALUE_TYPE_SHOPIFY;
                            $attrValue = implode(',', $value);
                        } elseif(count($value) == 1) {
                            if(isset($value['text'])) {
                                $attrValueType = JetAttributeMap::VALUE_TYPE_TEXT;
                                $attrValue = $value['text'];
                            } else {
                                $attrValueType = JetAttributeMap::VALUE_TYPE_SHOPIFY;
                                $attrValue = reset($value);
                            }
                        }
                    }
                    elseif ($value != '') {
                        $attrValueType = JetAttributeMap::VALUE_TYPE_JET;
                        $attrValue = $value;
                    }

                    if($attrValue=='Color'){
                        $jetAttrId=119;
                    }

                    if($attrValueType != '' && $attrValue != '')
                    {
                        $insert_value[] = "(".$merchant_id.",'".$shopifyProductType."','".addslashes($jetAttrId)."','".addslashes($attrValueType)."','".addslashes($attrValue)."')";
                    }
                }
            }
            if(count($insert_value)) {
                $delete = "DELETE FROM `jet_attribute_map` WHERE `merchant_id`=".$merchant_id;
                Data::sqlRecords($delete, null, 'delete');

                $query = "INSERT INTO `jet_attribute_map`(`merchant_id`, `shopify_product_type`, `jet_attribute_id`, `attribute_value_type`, `attribute_value`) VALUES ".implode(',', $insert_value);
                Data::sqlRecords($query, null, 'insert');

                Yii::$app->session->setFlash('success', "Attributes Have been Mapped Successfully!!");
            }
        }
        return $this->redirect(['index']);
    }
    /*public function actionTest()
    {
        $product_option_id = 15856827014; 
        $shopify_options = '{"5955260550":"Size","5955260614":"Color"}'; 
        $product_type = "Men's Clothes";
        $merchant_id = 14;

        var_dump(AttributeMap::getMappedOptionValues($product_option_id, $shopify_options, $product_type, $merchant_id));
    }*/
}
