<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\Neweggcategorytree;
use frontend\modules\neweggmarketplace\models\NeweggCategory;
use frontend\modules\neweggmarketplace\models\NeweggCategoryMap;
use Yii;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use yii\web\Session;
use frontend\modules\neweggmarketplace\components\Neweggappdetail;

class CategorymapController extends NeweggMainController
{
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest)
        {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        else
        {
            
            $merchant_id= MERCHANT_ID;
            $model=NeweggCategoryMap::find()->where(['merchant_id'=>$merchant_id])->all();

            // reading main categories of newegg
            $maincategoryroute = Yii::getAlias('@webroot').'/NeweggCategoryJson/categories.json';
            $str = file_get_contents($maincategoryroute);
            $maincategory = json_decode($str, true);

            //session of main category
            $session = new Session();
            $session->open();
            $session['main_category']=$maincategory;

            $category_tree=array();
            $category_detail=array();
            list($category_tree,$category_detail) = Neweggcategorytree::createCategoryTreeArray($maincategory);

            return $this->render('index',['model'=>$model,'data'=>$maincategory,'category_tree'=>$category_tree,'category_detail'=>$category_detail]);
        }
    }

    public function actionSave()
    {
        $error = [];
        if (Yii::$app->user->isGuest)
        {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        if(!isset($connection)){
            $connection=Yii::$app->getDb();
        }
        $merchant_id = MERCHANT_ID;
        $data=Yii::$app->request->post();
        if($data && isset($data['type']))
        {
            foreach($data['type'] as $key=>$value)
            {
                $category_path="";
                $category_id="";
                $key=stripslashes($key);
                //code by Ankit
                $manufacturer = $value['manufacturer'];
                unset($value['manufacturer']);
                //end
                if($manufacturer){
                    if(!Neweggappdetail::validateManufacturer(SELLER_ID,SECRET_KEY, AUTHORIZATION,$manufacturer)) {
                       $error[]= $manufacturer;
                       $manufacturer=null;
                    }
                }
                if(is_array($value) && count($value)>0 && $value[0]!=""){
                 
                    $category_path=implode(',',$value);
                    $category_path=rtrim($category_path,',');
                    $category_id=$value[0];

                    if($category_id=="Other")
                        $category_id=$value[0];
                    $model="";
                    $neweggData = [];
                    $neweggArrayData = [];
                    $categoryData = Categoryhelper::getNeweggCategory($category_id);
                    foreach ($categoryData as $key1 => $val) {
                        if($val['IndustryCode']==$category_id && $val['SubcategoryID']==$value[1]){
                            $neweggArrayData=array('category'=>array('id'=>$category_id,'name'=>str_replace(" ","",$val['IndustryName'])),'subcategory'=>array('id'=>$value[1],'name'=>$val['SubcategoryName']));
                            break;
                        }
                        else{
                            continue;
                        }
                    }
                   
                    $neweggData=json_encode($neweggArrayData);
                    $sql='UPDATE `newegg_category_map` SET  category_id="'.trim($category_id).'",category_path="'.trim($category_path).'",manufacturer="'.$manufacturer.'" where merchant_id="'.$merchant_id.'" and product_type="'.$key.'"';
                    $model = $connection->createCommand($sql)->execute();

                    $product="";
                    $sql='UPDATE `newegg_product` SET  newegg_category="'.trim($category_id).'" ,newegg_data="'.addslashes($neweggData).'"where merchant_id="'.$merchant_id.'" and shopify_product_type="'.$key.'"';
                    $product = $connection->createCommand($sql)->execute();

                }
                else{
                    $model="";
                    $sql='UPDATE `newegg_category_map` SET  category_id="",category_path="" ,manufacturer="'.$manufacturer.'" where merchant_id="'.$merchant_id.'" and product_type="'.$key.'"';
                    $model = $connection->createCommand($sql)->execute();
                    $product="";
                    $sql='UPDATE `newegg_product` SET  newegg_category="" where merchant_id="'.$merchant_id.'" and shopify_product_type="'.$key.'"';
                    $product = $connection->createCommand($sql)->execute();
                    continue;
                }

            }
            unset($data);
            if(empty($error)){
               Yii::$app->session->setFlash('success', "Newegg Categories are mapped successfully with Product Type"); 
            }
            else{
                Yii::$app->session->setFlash('warning', "Newegg Categories are mapped successfully with Product Type but these '".implode(',', $error)."' are not valid manufacturer"); 
            }
            
        }
        unset($connection);
        return $this->redirect(['index']);

    }

}
