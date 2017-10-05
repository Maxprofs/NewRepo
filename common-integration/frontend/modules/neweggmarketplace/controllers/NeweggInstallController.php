<?php 
namespace frontend\modules\neweggmarketplace\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Installation;
use frontend\modules\neweggmarketplace\models\NeweggInstallation;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;
use frontend\modules\neweggmarketplace\components\Neweggappdetail;

class NeweggInstallController extends NeweggMainController
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->layout = 'blank';

        $step = Yii::$app->request->get('step',false);
        if(!$step) {
            $installation = Installation::isInstallationComplete(MERCHANT_ID);
            if($installation) {
                if($installation['status'] == 'pending') {
                    $step = (int)$installation['step'];
                    $step = $step+1;
                } else {

                    $this->redirect(Data::getUrl('site/index'),302);
                    return false;
                }
            } else {
                $step = Installation::getFirstStep();
            }
        }

        return $this->render('installation', ['currentStep'=>$step]);
    }

    public function actionRenderstep()
    {
        $this->layout = 'main2';

        $stepId = Yii::$app->request->post('step',false);
        if($stepId)
        {
            $stepInfo = Installation::getStepInfo($stepId);
            if(!isset($stepInfo['error'])) {
                $templateFile = $stepInfo['template'];
                $html = $this->renderAjax($templateFile,[],true);
                return json_encode(['success'=>true,'content'=>$html,'steptitle'=>$stepInfo['name']]);
            } else {
                return json_encode(['error'=>true,'message'=>'Invalid Step Id.']);
            }
        }
        else
        {
            return json_encode(['error'=>true,'message'=>'Invalid Step Id.']);
        }
    }

    public function actionSavestep()
    {
        $stepId = Yii::$app->request->post('step',false);
        if($stepId)
        {
            try
            {
                $model = NeweggInstallation::find()->where(['merchant_id'=>MERCHANT_ID])->one();
                if(is_null($model)) {
                    $model = new NeweggInstallation();
                    $model->merchant_id = MERCHANT_ID;
                }
                
                if($stepId == Installation::getFinalStep())
                    $model->status = Installation::INSTALLATION_STATUS_COMPLETE;
                else 
                    $model->status = Installation::INSTALLATION_STATUS_PENDING;

                $model->step = $stepId;
                $model->save();

                return json_encode(['success'=>true,'message'=>'Saved Successfully!!']);
            }
            catch(Exception $e) {
                return json_encode(['error'=>true,'message'=>$e->getMessage()]);
            }
        }
        else
        {
            return json_encode(['error'=>true,'message'=>'Invalid Step Id.']);
        }
    }
    
    public function actionHelp()
    {
        $this->layout = 'blank';
        if(isset($_GET['step'])){
            return $this->render('help/step_'.$_GET['step'], ['step'=>$_GET['step']]);
        }
    }

    public function actionCheckProgressStatus()
    {
        $userData=Data::sqlRecords("SELECT id FROM user","all","select");
        if(is_array($userData) && count($userData)>0)
        {
            foreach ($userData as $value) 
            {
                $step=Installation::getCompletedStepId($value['id']);
                //check & save progress steps of each merchant
                $installedCollection=Data::sqlRecords("SELECT `id` FROM `newegg_installation` WHERE merchant_id=".$value['id']." limit 0,1","one","select");
                if(!$installedCollection){
                    echo "merchant_id:".$value['id']." step:".$step."<br>";
                }
            }
        }
    }

    public function actionSaveCategoryMap()
    {
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
                //$manufacturer = $value['manufacturer'];
                unset($value['manufacturer']);
                //end
                /*if($manufacturer){
                    if(!Neweggappdetail::validateManufacturer(SELLER_ID,SECRET_KEY, AUTHORIZATION,$manufacturer)) {
                       $error[]= $manufacturer;
                       $manufacturer=null;
                    }
                }*/

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
                    $sql='UPDATE `newegg_category_map` SET  category_id="'.trim($category_id).'",category_path="'.trim($category_path).'" where merchant_id="'.$merchant_id.'" and product_type="'.$key.'"';
                    $model = $connection->createCommand($sql)->execute();

                    $product="";
                    $sql='UPDATE `newegg_product` SET  newegg_category="'.trim($category_id).'" ,newegg_data="'.addslashes($neweggData).'"where merchant_id="'.$merchant_id.'" and shopify_product_type="'.$key.'"';
                    $product = $connection->createCommand($sql)->execute();

                }
                else{
                    $model="";
                    $sql='UPDATE `newegg_category_map` SET  category_id="",category_path="" where merchant_id="'.$merchant_id.'" and product_type="'.$key.'"';
                    $model = $connection->createCommand($sql)->execute();
                    $product="";
                    $sql='UPDATE `newegg_product` SET  newegg_category="" where merchant_id="'.$merchant_id.'" and shopify_product_type="'.$key.'"';
                    $product = $connection->createCommand($sql)->execute();
                    continue;
                }

            }
            unset($data);
            if(empty($error)){
               return json_encode(['success'=>true, "message"=>"Newegg Categories are mapped successfully with Product Type"]);
            }
            else{
                return json_encode(['success'=>true, "Newegg Categories are mapped successfully with Product Type but these '".implode(',', $error)."' are not valid manufacturer"]);
            }

            
        }
        else
        {
            return json_encode(['error'=>true, 'message'=>'Cannot Save Data.']);
        }
    }

    public function actionSaveAttributeMap()
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
            if(isset($data['newegg'])){
                  foreach ($data['newegg'] as $items => $values) {
              /* $attributeMap
                $attributeMap[$items]=$values;*/
                $productType = $items;
            
                foreach ($values as $key1 => $val1) {
                    $attributeMap=[];
                    $array = [];
                    $categoryId = $key1;
                    foreach ($val1 as $key2 => $val2) {

                        $attribute = $key2;
                        foreach ($val2 as $key3 => $val3) {
                            $attributeType = $key3;
                            $attributeValue = $val3;
                            if($attributeType=='map'){
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

            }
            }
                return json_encode(['success'=>true, 'message'=>"Attributes Have been Mapped Successfully!!"]);
        }
        else
        {
            return json_encode(['success'=>true, 'message'=>"Attributes not Mapped."]);
        }
    }
}   


