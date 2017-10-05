<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\AttributeMap;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use Yii;
use frontend\modules\neweggmarketplace\components\Data;
use yii\web\Session;
use frontend\modules\neweggmarketplace\models\NeweggProduct;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;

class VariantsvaluemapController extends NeweggMainController
{
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        } else {
            $session = new Session();
            $name = [];
            
            $productData=[];

                $query = "SELECT `jet`.`id`,GROUP_CONCAT(`jpv`.`option_sku`) as `option_sku`,type,attr_ids,product_type,`ngp`.`newegg_data` ,`nam`.`attribute_map`,GROUP_CONCAT(`jpv`.`variant_option1`) as `variant_option1` ,GROUP_CONCAT(`jpv`.`variant_option2`) as `variant_option2`,GROUP_CONCAT(`jpv`.`variant_option3`) as `variant_option3` FROM `newegg_product` ngp INNER JOIN `jet_product` jet ON `jet`.`id`=`ngp`.`product_id` INNER JOIN `jet_product_variants` jpv ON `jpv`.`product_id`=`jet`.`id`INNER JOIN `newegg_attribute_map` nam ON `nam`.`shopify_product_type`=`ngp`.`shopify_product_type` WHERE `type`='variants' and `ngp`.`merchant_id`=".MERCHANT_ID." GROUP BY `id` ";
                $productArray = Data::sqlRecords($query,"all","select");
                $k=0;
                $mapper = [];
                $valArray = [];
                foreach ($productArray as $key => $value) {
                 $attributeValue = json_decode($value['newegg_data'],true);

                   $attributeMap = json_decode($value['attribute_map'],true);

                    foreach ($attributeMap as $k2 => $v2) {
                        

                        $attributeMapData = explode('->', $v2);
                        $mapping = explode(',', $attributeMapData[1]);
                        foreach ($mapping as $key5 => $value5) {             
                                $mapper[$attributeMapData[0]]=$value5 ;
                        }
                    } 
                    $attr_ids = json_decode($value['attr_ids'],true);
                    $j = 1;
                    foreach ($attr_ids as $k1 => $v1) {
                        $valArray[$v1] = $j;
                        $j++;
                    }
                $query = "SELECT `mapped_value_data` FROM `variants_product_value_map` WHERE merchant_id =".MERCHANT_ID." AND shopify_product_type='".$value['product_type']."' AND  category_id = '".$attributeValue['category']['id']."'";
                $array = Data::sqlRecords($query,"one","select");
                    $model = Categoryhelper::subcategoryAttribute($attributeValue['category']['id'], $attributeValue['subcategory']['id']);

                        if (!empty($model)) {
                            $attributesValue = "";
                            foreach ($model as $key1 => $value1) {
                                if (($value1['IsRequired'] == 1 && $value1['IsGroupBy'] == 1) || ($value1['IsRequired'] == 0 && $value1['IsGroupBy'] == 1)) {
                                    $data[] = $value;
                                    $propertyName = $value1['PropertyName'];
                                    $name []=$propertyName;
                                    // getting sub category attribute value

                                    $attributesValue[$propertyName][] = Categoryhelper::subcategoryAttributeValue($attributeValue['category']['id'], $attributeValue['subcategory']['id'], $propertyName);
                                    $productData[$k]['value']=$attributesValue;

                                }
                               
                               
                            }
                        }
                       
                        if(isset($name) && !empty($name) && !empty($mapper)){
                            $i= 1;
                            foreach ($name as $key2 => $value2) {
                                $mapValue = [];
                                if(isset($mapper[$value2])){
                                $check = $mapper[$value2];
                                $count = $valArray[$check];
                                if(isset($value['variant_option'.$count.'']) && !empty($value['variant_option'.$count.''])){
                                    $variation1 = explode(',',$value['variant_option'.$count.'']);
                                    foreach ($variation1 as $key3 => $value3) {
                                        $mapValue[$value3] = $variation1;
                                    }

                                }

                                $value[$value2]=implode(',',array_keys($mapValue));
                                $i++;
                            }
                            }
                           
                            
                        }
                        if(isset($array['mapped_value_data']) && !empty($array['mapped_value_data'])){
                            $value['mapped_data'] = json_decode($array['mapped_value_data'],true);
                        }
                        
                        $productData[$k]['data']=$value;
                        $k++;
                        $name=[];
                        $mapper=[];
                        $mapValue=[];

                }
            return $this->render('index', ['data' => $productData]);
   
            }
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
            $delete = "DELETE FROM `variants_product_value_map` WHERE `merchant_id`=" . $merchant_id;
            Data::sqlRecords($delete, null, 'delete');
            foreach ($data['newegg'] as $items => $values) {
                
              /* $attributeMap
                $attributeMap[$items]=$values;*/
                $productType = $items;

            
                foreach ($values as $key1 => $val1) {
                    $attributeMap=[];
                    $categoryId = $key1;
                    /*$attributeData['shopify_product_type']=array('newegg_category_id'=>$productType);*/
                    foreach ($val1 as $key2 => $val2) {
                        $attribute = $key2;
                        foreach ($val2 as $key3 => $val3) {
                            $attributeType = $key3;
                            $attributeValue = $val3;

                            if (!empty($val3)) {

                                 $attributeMap[$items][$categoryId][$attribute][$attributeType]=$attributeValue;
                               
                            }
                        }

                    }
                    $attributeMapData = json_encode($attributeMap);
                     $query = "INSERT INTO `variants_product_value_map`(`merchant_id`, `shopify_product_type`,`category_id`,`mapped_value_data`) VALUES ('" . $merchant_id . "','" . addslashes($productType) . "','" . $categoryId . "','" .addslashes($attributeMapData). "') ";
                                Data::sqlRecords($query, null, 'insert');
                     
                }

                Yii::$app->session->setFlash('success', "variants product Value Have been Mapped Successfully!!");
            }
        }

        unset($connection);
        return $this->redirect(['index']);
    }
}