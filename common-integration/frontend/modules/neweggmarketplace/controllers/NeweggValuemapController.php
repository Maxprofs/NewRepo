<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\AttributeMap;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use Yii;
use frontend\modules\neweggmarketplace\components\Data;
use yii\web\Session;
use frontend\modules\neweggmarketplace\models\NeweggProduct;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;
use frontend\modules\neweggmarketplace\components\product\ValueMappingHelper;

class NeweggValuemapController extends NeweggMainController
{
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        } else {
        	$categoryData=Data::sqlRecords("SELECT `shopify_product_type`,`newegg_category_id` FROM `newegg_attribute_map` WHERE `merchant_id`='".MERCHANT_ID."' AND `attribute_map` IS NOT NULL","all","select");
            $savedData=Data::sqlRecords("SELECT * FROM `newegg_value_mapping` WHERE `merchant_id`='".MERCHANT_ID."'","all","select");
           // print_r($savedData);die;
            return $this->render('index',['categoryData' => $categoryData,'saveData'=>$savedData]);
   
            }
    }


    /**
     * for Value mapping.
     * @return array
     */

    public function actionValuemap(){
        if ($postData = Yii::$app->request->post()) {
            if($postData['desc']=="neweggcategoryattribute"){
                $data = ValueMappingHelper::getNeweggCategoryaArribute($postData['val']);
                echo $data;
                die;

            }
            if($postData['desc']=="shopifyattribute"){
                $data = ValueMappingHelper::getCategoryaArribute($postData['val']);
                echo $data;
                die;

            }
            if($postData['desc']=="valuedata"){
                $data = ValueMappingHelper::getAttributeValue($postData['val']);
                echo $data;
                die;

            }
            if($postData['desc']=="mapData"){
                $data = ValueMappingHelper::getMappedData($postData['val']);
                echo $data;
                die;

            }
            
        }


    }

    public function actionSave(){
        
    $return_status=[];
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
            if(isset($data['newegg']) && !empty($data['newegg'])){
             foreach ($data['newegg'] as $items => $values) {
                $productType = $items;
                foreach ($values as $key1 => $val1) {
                    $attributeMap=[];
                    $categoryId = $key1;

                    foreach ($val1 as $key2 => $val2) {
                        $attribute = $key2;
                        if(!is_array($val2)){
                                $return_status['error']='Mapped all attribute value !!';
                                return json_encode($return_status);
                        }
                        foreach ($val2 as $key3 => $val3) {
                            if(!is_array($val3)){
                                $return_status['error']='Shopify attribute not having any value !!';
                                return json_encode($return_status);
                            }
                            $attributeType = $key3;
                            $attributeValue = $val3;

                            if (!empty($val3)) {
                                 $attributeMap[$items][$categoryId][$attribute][$attributeType]=$attributeValue;   
                            }
                        }

                    }
                    $attributeMapData = json_encode($attributeMap);
                    $select = "SELECT `shopify_product_type` FROM `newegg_value_mapping` WHERE `merchant_id`='".$merchant_id."' AND `shopify_product_type`='".addslashes($productType)."'";
                    $prev_data = Data::sqlRecords($select,"one","select");
                    if(empty($prev_data)){
                     $query = "INSERT INTO `newegg_value_mapping`(`merchant_id`, `shopify_product_type`,`category_id`,`mapped_value_data`) VALUES ('" . $merchant_id . "','" . addslashes($productType) . "','" . $categoryId . "','" .addslashes($attributeMapData). "') ";
                        Data::sqlRecords($query, null, 'insert');
                    }
                    else{
                        $sql='UPDATE `newegg_value_mapping` SET  category_id="'.trim($categoryId).'",mapped_value_data="'.addslashes($attributeMapData).'" where merchant_id="'.$merchant_id.'" and shopify_product_type="'.addslashes($productType).'"';
                        Data::sqlRecords($sql, null, 'update');

                    }
                     
                }
                $return_status['success']='variants product Value Have been Mapped Successfully!!';
             }
            }
            else{
                $return_status['error']='No attribute value for mapping!!';

            }
         
        }

        unset($connection);
        return json_encode($return_status);
        
    }

}