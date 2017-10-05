<?php
namespace frontend\modules\neweggmarketplace\components;

use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\models\NeweggCategory;
use yii\web\Session;


class Neweggcategorytree extends Component
{
    public static function createCategoryTreeArray($data = "")
    {
        $session = new Session();
        $session->open();
        $category_tree = array();
        $category_detail = array();
        $return_arr = array();
        foreach ($data as $val) {

            $category_detail[trim($val['IndustryCode'])] = trim($val['IndustryName']);
            if (isset($val['IndustryCode'])) {
                $category_tree[trim($val['IndustryCode'])] = array();
                $key = trim($val['IndustryCode']);
                $subcategoryroute = Yii::getAlias('@webroot') . '/NeweggCategoryJson/SubCat/' . $key . '.json';

                if (file_exists($subcategoryroute)) {
                    $str = file_get_contents($subcategoryroute);
                    $model = json_decode($str, true);
                    $subcategories = [];
                    if (!empty($model)) {

                        foreach ($model as $subcat) {
                            $subcategories[$subcat['SubcategoryID']] = [];
                            $category_detail[$subcat['SubcategoryID']] = trim($subcat['SubcategoryName']);
                        }
                        $category_tree[$key] = $subcategories;
                    }
//                    }
                } else {
                    unset($category_tree[trim($val['IndustryCode'])]);
                }
            }

        }


        $return_arr[] = $category_tree;
        $return_arr[] = $category_detail;
        return $return_arr;
    }
}

?>