<?php
namespace frontend\modules\neweggcanada\components\categories;

use Yii;
use yii\base\Component;
use yii\helpers\Url;
use yii\web\Session;

class Categoryhelper extends Component
{

    public static function subCategory($key)
    {
        $model ="";
        $session = Yii::$app->session;
        $subcategoryroute = Yii::getAlias('@webroot') . '/NeweggCategoryJson/SubCat/' . $key. '.json';

        if (file_exists($subcategoryroute)) {
            $str = file_get_contents($subcategoryroute);
            $model = json_decode($str, true);
            $session->set('newegg_subcategory_'.addslashes($key), $model);

        }else{
            return false;
        }

        return $model;
    }

    public static function mainCategory()
    {
        $model ="";
        $session = new Session();
        $session->open();

        $maincategoryroute = Yii::getAlias('@webroot').'/NeweggCategoryJson/categories.json';

        if (file_exists($maincategoryroute)) {
            $str = file_get_contents($maincategoryroute);
            $model = json_decode($str, true);

            $session['main_category'] = $model;

        }else{
            return false;
        }

        return $model;
    }

    public static function subcategoryAttribute($categoryId,$subcategoryId)
    {
        $model ="";
        $session = Yii::$app->session;
        $subcategoryattribute = Yii::getAlias('@webroot') . '/NeweggCategoryJson/SubCat/SubCatFields/' . $categoryId. '/'.$subcategoryId.'.json';

        $index = 'newegg_attribute_value_'.addslashes($categoryId).addslashes($subcategoryId);
        if (file_exists($subcategoryattribute)) {
            $str = file_get_contents($subcategoryattribute);
            $model = json_decode($str, true);
            $session->set($index,$model);

        }else{
            return false;
        }

        return $model;
    }

    public static function subcategoryAttributeValue($categoryId,$subcategoryId,$attributeValue)
    {
        $model ="";
        $session = Yii::$app->session;
        $subcategoryattribute = Yii::getAlias('@webroot') . '/NeweggCategoryJson/SubCat/SubCatFieldValues/' . $categoryId. '/'.$subcategoryId. '/'.$attributeValue.'.json';
        $index =  'newegg_attribute_value_'.addslashes($categoryId).addslashes($subcategoryId).addslashes($attributeValue);
        if (file_exists($subcategoryattribute)) {
            $str = file_get_contents($subcategoryattribute);
            $model = json_decode($str, true);
            $session->set($index, $model);
        }else{
            return false;
        }

        return $model;
    }

    public static function getNeweggCategory($category)
    {
        $session = Yii::$app->session;
        $index = self::getNeweggSessionIdx($category);
        if(!isset($session[$index]))
        {
             $model ="";
                $subcategoryroute = Yii::getAlias('@webroot') . '/NeweggCategoryJson/SubCat/' . $category. '.json';
                if (file_exists($subcategoryroute)) {
                    $str = file_get_contents($subcategoryroute);
                    $model = json_decode($str, true);

                }
            $session->set($index, $model);
            return $model;
        }
        else
        {
            return $session[$index];
        }
    }

    public static function getNeweggSessionIdx($category)
    {
        $index = 'newegg_subcategory_'.addslashes($category);
        return $index;
    }

    /*Get Sub category Required attribute*/

    public static function getSubcategoryRequiredAttribute($category_id,$subcategory_id)
    {
        $session = Yii::$app->session;
        $index = self::getNeweggSessionIdx('requiredattribute_'.$category_id.'_'.$subcategory_id);
        if(!isset($session[$index]))
        {
            $model ="";
            $subcategoryroute = Yii::getAlias('@webroot') . '/NeweggCategoryJson/SubCat/SubCatFields/'.$category_id.'/'.$subcategory_id.'.json';
            if (file_exists($subcategoryroute)) {
                $str = file_get_contents($subcategoryroute);
                $model = json_decode($str, true);
                $requiredattribute = [];
                foreach ($model as $key => $value) {
                    if(isset($value['IsRequired']) && $value['IsRequired']=='1'){
                        $requiredattribute[$value['PropertyName']]=true;
                    }
                }
            }
            $session->set($index, $requiredattribute);
            return $requiredattribute;
        }
        else
        {
            return $session[$index];
        }
    }

}