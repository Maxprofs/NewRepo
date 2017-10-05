<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\AttributeMap;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use Yii;
use frontend\modules\neweggmarketplace\components\Data;
use yii\web\Session;

class NeweggAttributemapController extends NeweggMainController
{
    public function actionIndex()
    {
        $session = new Session();
        $session->open();
        $attributes = [];
        $categoryName = '';
        //getting sub category attribte
        $shopify_product_types = AttributeMap::getShopifyProductTypes();

        foreach ($shopify_product_types as $type_arr) {
            $data = "";
            $product_type = $type_arr['product_type'];

            $NeweggCategory = explode(",", $type_arr['category_path']);

            if (isset($session['main_category'])) {

                foreach ($session['main_category'] as $item) {
                    if ($item['IndustryCode'] == $NeweggCategory[0]) {
                        $categoryName = $item['IndustryName'];
                        $categoryId = $item['IndustryCode'];
                    }
                }

            }
             else{
                $categories = Categoryhelper::mainCategory();
                foreach ($categories as $item) {
                    if ($item['IndustryCode'] == $NeweggCategory[0]) {
                        $categoryName = $item['IndustryName'];
                        $categoryId = $item['IndustryCode'];
                    }
                }
            }

            if (is_array($NeweggCategory) && !empty($NeweggCategory[1])) {
                $categoryId = $NeweggCategory[0];
                $subcategoryId = $NeweggCategory[1];

                $model = Categoryhelper::subcategoryAttribute($categoryId, $subcategoryId);

                if (!empty($model)) {
                    $attributesValue = "";
                    foreach ($model as $key => $value) {
                        if ($value['IsRequired'] == 1 || $value['IsGroupBy'] == 1) {
                            $data[] = $value;
                            $propertyName = $value['PropertyName'];

                            // getting sub category attribute value

                            $attributesValue[$propertyName][] = Categoryhelper::subcategoryAttributeValue($categoryId, $subcategoryId, $propertyName);
                        }
                    }

                }

            }
            
            $shopifyAttributes = AttributeMap::getShopifyProductAttributes($product_type);

           $mapped_values = AttributeMap::getAttributeMapValues($product_type);
           
           $mapped_Datas = AttributeMap::getAttributeMapData($product_type);

            if (!empty($data)) {
                $attributes[$product_type] = [
                    'product_type' => $product_type,
                    'neweggcategory' => $categoryName,
                    'neweggattribute' => $data,
                    'shopify_attribute' => $shopifyAttributes,
                    'neweggattributevalue' => $attributesValue,
                    'neweggcategoryId' => $categoryId,
                   'mapped_values' => $mapped_values,
                   'mapped_Datas'=>$mapped_Datas
                ];
            }
        }
        return $this->render('index', ['attributes' => $attributes]);
    }

    public function actionSave()
    {
        
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        if (!isset($connection)) {
            $connection = Yii::$app->getDb();
        }
        $merchant_id = MERCHANT_ID;
        $data = Yii::$app->request->post();
      
        if(!empty($data)) {
            $attributeMap=[];
            $delete = "DELETE FROM `newegg_attribute_map` WHERE `merchant_id`=" . $merchant_id;
            Data::sqlRecords($delete, null, 'delete');
           // print_r($data['newegg']);die;
            if(isset($data['newegg'])){
                foreach ($data['newegg'] as $items => $values) {
              /* $attributeMap
                $attributeMap[$items]=$values;*/
                $productType = $items;
            
                foreach ($values as $key1 => $val1) {
                    $attributeMap=[];
                    $array = [];
                    $categoryId = $key1;
                    /*$attributeData['shopify_product_type']=array('newegg_category_id'=>$productType);*/
                    foreach ($val1 as $key2 => $val2) {
                        $attribute = $key2;
                        foreach ($val2 as $key3 => $val3) {
                            $attributeType = $key3;
                            $attributeValue = $val3;
                            
                            if($attributeType =='map'){
                                $mapData = implode(',',$val3);
                                $mappingData =$attribute.'->'.$mapData;
                                $array[] = $mappingData;
                                continue;
                            }
                            if (!empty($val3)) {

                                 $attributeMap[$items][$categoryId][$attribute]=array('value'=>$attributeValue,'attribute_value_type'=>$attributeType);
                               
                            }
                        }

                    }
                    $attributeMapData = json_encode($attributeMap);
                    if(isset($array) && $array)
                    {
                        $mapNeweggData = json_encode($array);
                         $query = "INSERT INTO `newegg_attribute_map`(`merchant_id`, `shopify_product_type`,`newegg_category_id`,`attribute_map_data`,`attribute_map`) VALUES ('" . $merchant_id . "','" . addslashes($productType) . "','" . $categoryId . "','" .addslashes($attributeMapData). "','" . addslashes($mapNeweggData) . "') ";
                    }
                    
                     else{
                        $query = "INSERT INTO `newegg_attribute_map`(`merchant_id`, `shopify_product_type`,`newegg_category_id`,`attribute_map_data`) VALUES ('" . $merchant_id . "','" . addslashes($productType) . "','" . $categoryId . "','" .addslashes($attributeMapData). "') ";
                     }
                                Data::sqlRecords($query, null, 'insert');
                    
                     
                }

                Yii::$app->session->setFlash('success', "Attributes Have been Mapped Successfully!!");
            }
            }
            else{
                Yii::$app->session->setFlash('error', "No attribute available for Mapping!!");
            }
        }

        unset($connection);
        return $this->redirect(['index']);
    }
}
