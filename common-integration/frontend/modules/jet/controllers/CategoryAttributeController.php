<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use common\models\User;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\models\JetAttributeMap;

class CategoryAttributeController extends Controller
{
	public function actionSaveCategoryAjax()
    {
        $merchant_id= Yii::$app->user->identity->id;
        $data=Yii::$app->request->post();

        if($data && isset($data['type']))
        {
            foreach($data['type'] as $key=>$value)
            {

                $category_path="";
                $category_id="";
                $key=stripslashes($key);
                if(is_array($value) && count($value)>0 && $value[0]!="")
                {
                    $category_path=implode(',',$value);
                    $category_path=rtrim($category_path,',');
                    $category_id=end($value);
                    $model="";
                    $sql='UPDATE `jet_category_map` SET  category_id="'.trim($category_id).'",category_path="'.trim($category_path).'" where merchant_id="'.$merchant_id.'" and product_type="'.addslashes($key).'"';
                    Data::sqlRecords($sql,null,'update');
                    $product="";
                    $sql='UPDATE `jet_product` SET  fulfillment_node="'.trim($category_id).'" where merchant_id="'.$merchant_id.'" and product_type="'.addslashes($key).'"';
                    Data::sqlRecords($sql,null,'update');
                }
            }
            return "5";
        }else{
            return "Your haven't defined any Product type for products at your store.";
        }
        
    }
    public function actionSaveAttributeAjax()
    {
        $data = Yii::$app->request->post();
        if($data && isset($data['jet']))
        {
            //var_dump($data);die("VBnn");
            $merchant_id= Yii::$app->user->identity->id;
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

                //Yii::$app->session->setFlash('success', "Attributes Have been Mapped Successfully!!");
            }
        }
        return "6";
    }
}