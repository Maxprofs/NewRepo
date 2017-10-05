<?php
namespace frontend\modules\walmart\controllers;
use Yii;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartDataValidation;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * JetproductController implements the CRUD actions for JetProduct model.
 */
class WalmartvalidateController extends WalmartmainController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    protected $connection;
    /**
     * Lists all JetProduct models.
     * @return mixed
     */

    public function actionIndex1()
    {

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        else
        {

            $result = [];
            $count = [];
            $total = 0;
            $merchant_id = Yii::$app->user->identity->id;
            $result = WalmartDataValidation::validateData([], $merchant_id);

            $query = "";
            //$query = "SELECT COUNT(*) as `count` from `jet_product` as `main` INNER JOIN `walmart_product` as `wp` ON `main`.`id`=`wp`.`product_id` where `main`.`merchant_id`= {$merchant_id}";
            $query = "SELECT COUNT(*) as `count` from (SELECT * FROM `jet_product` WHERE `merchant_id`='".$merchant_id."' ) `main` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='".$merchant_id."') as `wp` ON `main`.`id`=`wp`.`product_id` where `main`.`merchant_id`='".$merchant_id."' ";
            //$count = $connection->createCommand($query)->queryOne();
            $count = Data::sqlRecords($query, 'all');
            $total = is_array($count) && isset($count[0]["count"])?$count[0]["count"]:0;

            $taxcode = Data::getConfigValue($merchant_id,'tax_code');
            if(empty($taxcode)){
                $product_type = Data::sqlRecords("SELECT DISTINCT `product_type` FROM `walmart_category_map` WHERE `merchant_id`='".$merchant_id."' AND `tax_code`=''",'column');
                $product_types = implode('","',$product_type);

                if(!empty($product_types)){

                    //print_r('SELECT `product_id` FROM `walmart_product` WHERE `merchant_id`="'.$merchant_id.'" AND `product_type` IN ("'.$product_types.'") AND `tax_code`!=""');die;
                        $product_id[] = Data::sqlRecords('SELECT `product_id` FROM `walmart_product` WHERE `merchant_id`="'.$merchant_id.'" AND `product_type` IN ("'.$product_types.'") AND `tax_code`!=""','column');
                }
                if(!empty($product_id) && !is_null($product_id))
                {
                    $product_ids = array_shift($product_id);
                    foreach ($result as $key => $value){

                        if(in_array($key, $product_ids)){

                            $result[$key]['taxcode']= 'Error : Invalid Product Taxcode';

                        }
                    }
                }

            }
            $category = Data::sqlRecords("SELECT `product_type` FROM `walmart_category_map` WHERE `merchant_id`='".$merchant_id."' AND `category_id`=''",'column');
            $new_category = implode('","',$category);

            if(!empty($new_category)){

                $product_id[] = Data::sqlRecords('SELECT `product_id` FROM `walmart_product` WHERE `merchant_id`="'.$merchant_id.'" AND `product_type` IN ("'.$new_category.'") ','column');

            }

            if(!empty($product_id) && !is_null($product_id))
            {
                $product_ids = array_shift($product_id);

                foreach ($result as $key => $value){

                    if(in_array($key, $product_ids)){

                        $result[$key]['category']= 'Error : Category Not Mapped';

                    }
                }
            }

            if(count($result)>0){
                return $this->render('index', ['data' => $result]);
            }elseif(count($result)==0 && $total>0){
                Yii::$app->session->setFlash('success',"All product(s) validated successfully. No error found.");
            }else{
                Yii::$app->session->setFlash('success',"No product(s) available to validate.");
            }
         }
        return $this->redirect(['walmartproduct/index']);
    }
    /**
     * Validate Product 
     * @return mixed
     */

    public function actionIndex()
    {

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        else
        {
            
            $result = [];
            $count = [];
            $total = 0;
            $merchant_id = Yii::$app->user->identity->id;
            $result = WalmartDataValidation::validateData([], $merchant_id);
            $query = "";
            //$query = "SELECT COUNT(*) as `count` from `jet_product` as `main` INNER JOIN `walmart_product` as `wp` ON `main`.`id`=`wp`.`product_id` where `main`.`merchant_id`= {$merchant_id}";
            $query = "SELECT COUNT(*) as `count` from (SELECT * FROM `jet_product` WHERE `merchant_id`='".$merchant_id."' ) `main` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='".$merchant_id."') as `wp` ON `main`.`id`=`wp`.`product_id` where `main`.`merchant_id`='".$merchant_id."' ";
            //$count = $connection->createCommand($query)->queryOne();
            $count = Data::sqlRecords($query, 'all');
            $total = is_array($count) && isset($count[0]["count"])?$count[0]["count"]:0;

            $taxcode = Data::getConfigValue($merchant_id,'tax_code');
            if(empty($taxcode)){
                $product_type = Data::sqlRecords("SELECT DISTINCT `product_type` FROM `walmart_category_map` WHERE `merchant_id`='".$merchant_id."' AND `tax_code`=''",'column');
                $product_types = implode('","',$product_type);

                if(!empty($product_types)){

                    //print_r('SELECT `product_id` FROM `walmart_product` WHERE `merchant_id`="'.$merchant_id.'" AND `product_type` IN ("'.$product_types.'") AND `tax_code`!=""');die;
                        $product_id[] = Data::sqlRecords('SELECT `product_id` FROM `walmart_product` WHERE `merchant_id`="'.$merchant_id.'" AND `product_type` IN ("'.$product_types.'") AND `tax_code`!=""','column');
                }
                if(!empty($product_id) && !is_null($product_id))
                {
                    $product_ids = array_shift($product_id);
                    foreach ($result as $key => $value){

                        if(in_array($key, $product_ids)){

                            $result[$key]['taxcode']= 'Error : Invalid Product Taxcode';

                        }
                    }
                }

            }
            $category = Data::sqlRecords("SELECT `product_type` FROM `walmart_category_map` WHERE `merchant_id`='".$merchant_id."' AND `category_id`=''",'column');
            $new_category = implode('","',$category);

            if(!empty($new_category)){

                $product_id[] = Data::sqlRecords('SELECT `product_id` FROM `walmart_product` WHERE `merchant_id`="'.$merchant_id.'" AND `product_type` IN ("'.$new_category.'") ','column');

            }
            $attribute = Data::sqlRecords("SELECT `product_type` FROM `walmart_category_map` WHERE `merchant_id`='".$merchant_id."' AND `category_id`=''",'column');

            if(!empty($product_id) && !is_null($product_id))
            {
                $product_ids = array_shift($product_id);

                foreach ($result as $key => $value){

                    if(in_array($key, $product_ids)){

                        $result[$key]['category']= 'Error : Category Not Mapped';

                    }
                }
            }

            if(count($result)>0){
                return $this->render('index', ['data' => $result]);
            }elseif(count($result)==0 && $total>0){
                Yii::$app->session->setFlash('success',"All product(s) validated successfully. No error found.");
            }else{
                Yii::$app->session->setFlash('success',"No product(s) available to validate.");
            }
         }
        return $this->redirect(['walmartproduct/index']);
    }

}
