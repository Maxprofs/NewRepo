<?php
namespace frontend\modules\jet\controllers;
use common\models\User;
use Yii;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetcategorytree;
use frontend\modules\jet\models\JetCategory;
use frontend\modules\jet\models\JetCategoryMap;
use frontend\modules\jet\models\JetProduct;

class CategorymapController extends JetmainController
{
    protected $sc,$jetHelper;
    public function actionIndex()
    {
    	if (Yii::$app->user->isGuest) 
    		return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	
    	$merchant_id = MERCHANT_ID;
        //check duplicate product-types
        $duplicateProductType=Data::sqlRecords("SELECT product_type FROM jet_category_map GROUP BY product_type HAVING count(*) > 1 ");
        if(isset($duplicateProductType['product_type']))
        {
            $sql = "DELETE FROM `jet_category_map` WHERE `id` NOT IN (SELECT `id` FROM (SELECT `id` FROM `jet_category_map` GROUP BY `product_type`) jetcat )";
            Data::sqlRecords($sql,null,'delete');
        }
	    $model=JetCategoryMap::find()->where(['merchant_id'=>$merchant_id])->all();
	    $data=JetCategory::find()->where(['<>', 'is_active', 1])->all();
	    //$data=JetCategory::find()->all();
        $category_tree = $category_detail = [];
        list($category_tree,$category_detail)=Jetcategorytree::createCategoryTreeArray($data);
        return $this->render('index',['model'=>$model,'data'=>$data,'category_tree'=>$category_tree,'category_detail'=>$category_detail]);
   }
    public function actionSave()
    {
    	if (Yii::$app->user->isGuest) {
    		return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
    	$merchant_id= MERCHANT_ID;
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
                    if(!$category_id)
                    {
                        $last_key=key($value);
                        $category_id=$value[$last_key-1];
                    }
                    $model="";
                    $sql='UPDATE `jet_category_map` SET  category_id="'.trim($category_id).'",category_path="'.trim($category_path).'" where merchant_id="'.$merchant_id.'" and product_type="'.$key.'"';
                    Data::sqlRecords($sql,null,'update');
                    $product="";
                    $sql='UPDATE `jet_product` SET  fulfillment_node="'.trim($category_id).'" where merchant_id="'.$merchant_id.'" and product_type="'.addslashes($key).'"';
                    Data::sqlRecords($sql,null,'update');
    			}
                else
                {
                    $sql='UPDATE `jet_category_map` SET  category_id=0,category_path="" where merchant_id="'.$merchant_id.'" and product_type="'.addslashes($key).'"';
                    Data::sqlRecords($sql,null,'update');
                    $product="";
                    $sql='UPDATE `jet_product` SET  fulfillment_node=0 where merchant_id="'.$merchant_id.'" and product_type="'.$key.'"';
                    Data::sqlRecords($sql,null,'update');
                }
    		}
            unset($data);
    		Yii::$app->session->setFlash('success', "Jet Categories are mapped successfully with Product Type");
    	}
        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet/jetproduct/index',302);
    }
    
    public function actionGetcategory()
    {
        $msg["html"]="";
        $msg['error']="";
        try{
                $html="";
                $id=Yii::$app->request->post('id');
                $level=Yii::$app->request->post('level');
                $level=(int)$level;
                $level_1=$level+1;
                $path_str=Yii::$app->request->post('path_str');
                $type=Yii::$app->request->post('type');
                $category_path=array();
                if(trim($path_str)!=""){
                    $category_path=explode(',',$path_str);
                }
                $type=trim($type);
                $path_str='"'.trim($path_str).'"';
                $type_str='"'.$type.'"';
                $result="";
                $result=JetCategory::find()->where(['parent_id'=>$id,'level'=>$level])->all();
                if(count($result)>0)
                {
                    $html.="<select name='type[".$type."][]' class='form-control'  onchange='selectChild(this,".$level_1.",".$path_str.",".$type_str.")'>";
                    foreach($result as $value){
                        if(count($category_path)>$level && $category_path[$level]==trim($value->category_id)){
                            $html.="<option selected='selected' value='".$value->category_id."'>".$value->title."</option>";
                        }else{
                            $html.="<option value='".$value->category_id."'>".$value->title."</option>";
                        }
                        
                    }
                    $html.="<select>";
                }
                $msg["html"]=$html;
               
        }catch(Exception $e){
               $msg['error']=$e->getMessage();
        }
        return json_encode($msg);
    }
    public function actionItembasemapcategory(){
        $this->layout="main2";
        $data = JetCategory::find()->where(['<>', 'is_active', 1])->all();
        $category_tree = $category_detail = [];
        list($category_tree,$category_detail)=Jetcategorytree::createCategoryTreeArray($data);
        $model[0] = json_decode($_POST['id'],true);
        return $this->render('itembasemap',['model'=>$model,'data'=>$data,'category_tree'=>$category_tree,'category_detail'=>$category_detail]);
    }
    public function actionItembasecategory(){
        if (Yii::$app->user->isGuest) 
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        
        $merchant_id = MERCHANT_ID;
        
        $model = Data::sqlRecords('SELECT `id`,`merchant_id`,`sku`,`fulfillment_node` FROM `jet_product` WHERE `merchant_id`="'.$merchant_id.'"','select','all');
        $data = JetCategory::find()->where(['<>', 'is_active', 1])->all();
        //$data=JetCategory::find()->all();
        $category_tree = $category_detail = [];
        list($category_tree,$category_detail)=Jetcategorytree::createCategoryTreeArray($data);

        return $this->render('itembasecategory',['model'=>$model,'data'=>$data,'category_tree'=>$category_tree,'category_detail'=>$category_detail]);
    }
    public function actionSaveitemcategory()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id= MERCHANT_ID;
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
                    if(!$category_id)
                    {
                        $last_key=key($value);
                        $category_id=$value[$last_key-1];
                    }
                   
                    $sql='UPDATE `jet_product` SET  fulfillment_node="'.trim($category_id).'" where merchant_id="'.$merchant_id.'" AND sku="'.addslashes($key).'"';
                    Data::sqlRecords($sql,null,'update');
                }
                else
                {
                    $sql='UPDATE `jet_product` SET  fulfillment_node=0 where merchant_id="'.$merchant_id.'" AND sku="'.$key.'"';
                    Data::sqlRecords($sql,null,'update');
                }
            }
            unset($data);
            Yii::$app->session->setFlash('success', "Jet Categories are mapped successfully with Product Type");
        }
        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet/categorymap/itembasecategory',302);
    }
    
}