<?php

namespace backend\modules\reports\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

use backend\modules\reports\models\JetExtensionDetailSearch;

/**
 * PaidNoRevenueNoLiveController
 */
class TotalMerchantController extends BaseController
{

    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        

        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        $searchModel = new JetExtensionDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index',['dataProvider' => $dataProvider, 'searchModel' => $searchModel]);
        /*
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $chart = $sql1->queryAll();
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $totalCount,
            'sort' =>false,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'chart' => $chart,
        ]);*/
    }

    

    /**
     * Perform Mass Action.
     * @return mixed
     */
    public function actionMass()
    {


        $post = Yii::$app->getRequest()->post();
        if(isset($post['selection']) && count($post['selection'])){
            if($post['action']=='email'){
                $templates = Yii::$app->db->createCommand("SELECT * FROM jet_email_template")->queryAll();
                $post['templates'] = $templates;
                return $this->render('/installations/sendmail',['data'=>$post]);
            }
            elseif($post['action']=='validate_sku'){
                $result = [];
                //$skuRestrictedCharacters = ['/','+','*',':'];
                if(isset($post['selection_all']) && $post['selection_all']==1){
                   

                    /***Validate Sku***/

                    $query = "SELECT count(`merchant_id`) `invalid_skus`,`merchant_id`,GROUP_CONCAT(`sku`) as `sku` FROM `jet_product` WHERE `type`='simple' AND `sku` LIKE '%/%' OR `sku` LIKE '%+%' OR `sku` LIKE '%*%' OR `sku` LIKE '%:%' GROUP BY `merchant_id` ";
                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    
                    foreach($data as $row){
                        $result[$row['merchant_id']] = ['invalid_skus_count'=>$row['invalid_skus'],'invalid_skus'=>$row['sku']];
                    }


                    $query = "SELECT count(`merchant_id`) `invalid_skus`,`merchant_id`,GROUP_CONCAT(`option_sku`) as `sku` FROM `jet_product_variants` WHERE `option_sku` LIKE '%/%' OR `option_sku` LIKE '%+%' OR `option_sku` LIKE '%*%' OR `option_sku` LIKE '%:%'  GROUP BY `merchant_id` ";
                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        if(isset($result[$row['merchant_id']])){
                            $result[$row['merchant_id']]['invalid_variant_skus_count'] = $row['invalid_skus'];
                            $result[$row['merchant_id']]['invalid_variant_skus'] = $row['sku'];
                        }
                        else{
                            $result[$row['merchant_id']] = ['invalid_variant_skus_count'=>$row['invalid_skus'],'invalid_variant_skus'=>$row['sku']];
                        }
                    }
                    /***End Validate Sku***/

                    /***End Validate Title***/

                    $query = "SELECT count(`merchant_id`) `invalid_titles`,`merchant_id`,GROUP_CONCAT(`title`) as `title` FROM `jet_product` WHERE `type`='simple' AND ((CHAR_LENGTH(`title`))<5 OR (CHAR_LENGTH(`title`))>500 OR `title` LIKE '%sale%' OR `title` LIKE '%offer%' OR `title` LIKE '%new%' ) GROUP BY `merchant_id` ";

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        $result[$row['merchant_id']]['invalid_titles_count'] = $row['invalid_titles'];
                        $result[$row['merchant_id']]['invalid_titles'] = $row['title'];
                    }


                    $query = "SELECT count(`merchant_id`) `invalid_titles`,`merchant_id`,GROUP_CONCAT(`option_title`) as `title` FROM `jet_product_variants` WHERE ((CHAR_LENGTH(`option_title`))<5 OR (CHAR_LENGTH(`option_title`))>500 OR `option_title` LIKE '%sale%' OR `option_title` LIKE '%offer%' OR `option_title` LIKE '%new%' ) GROUP BY `merchant_id` ";

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        if(isset($result[$row['merchant_id']])){
                            $result[$row['merchant_id']]['invalid_variant_titles_count'] = $row['invalid_titles'];
                            $result[$row['merchant_id']]['invalid_variant_titles'] = $row['title'];
                        }
                        else{
                            $result[$row['merchant_id']] = [];
                            $result[$row['merchant_id']]['invalid_variant_titles_count'] = $row['invalid_titles'];
                            $result[$row['merchant_id']]['invalid_variant_titles'] = $row['title'];
                        }
                    }

                    /***End Validate Title***/


                    /*** Validate Barcode ***/
                    $query = "SELECT count(`merchant_id`) `invallid_barcode_count`,`merchant_id`,GROUP_CONCAT(`upc`) as `upc`,GROUP_CONCAT(`mpn`) as `mpn`,GROUP_CONCAT(`ASIN`) as `asin`,GROUP_CONCAT(`sku`) as `product_ids` FROM `jet_product` WHERE `type`='simple' AND ((CHAR_LENGTH(`upc`))!=12 AND (CHAR_LENGTH(`upc`))!=14 AND (CHAR_LENGTH(`upc`))!=13 AND CHAR_LENGTH(`upc`)!=10) AND (CHAR_LENGTH(`ASIN`)!=10) AND (CHAR_LENGTH(`mpn`)>50 OR CHAR_LENGTH(`mpn`)<1 ) GROUP BY `merchant_id` " ;


                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    
                    foreach($data as $row){
                        $result[$row['merchant_id']]['invalid_barcode_count'] = $row['invallid_barcode_count'];
                        $result[$row['merchant_id']]['invalid_barcode'] = $row['upc'];
                        $result[$row['merchant_id']]['invalid_barcode_products'] = $row['product_ids'];
                        $result[$row['merchant_id']]['invalid_mpn'] = $row['mpn'];
                        $result[$row['merchant_id']]['invalid_asin'] = $row['asin'];
                    }


                    $query = "SELECT count(`merchant_id`) `invallid_barcode_count`,`merchant_id`,GROUP_CONCAT(`option_unique_id`) as `upc`,GROUP_CONCAT(`option_mpn`) as `mpn`,GROUP_CONCAT(`asin`) as `asin`,GROUP_CONCAT(`option_sku`) as `product_ids` FROM `jet_product_variants` WHERE ((CHAR_LENGTH(`option_unique_id`))!=12 AND(CHAR_LENGTH(`option_unique_id`))!=14 AND (CHAR_LENGTH(`option_unique_id`))!=13 AND CHAR_LENGTH(`option_unique_id`)!=10) AND (CHAR_LENGTH(`asin`)!=10) AND (CHAR_LENGTH(`option_mpn`)>50 OR CHAR_LENGTH(`option_mpn`)<1 ) GROUP BY `merchant_id` " ;

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    
                    foreach($data as $row){
                        if(isset($result[$row['merchant_id']])){
                            $result[$row['merchant_id']]['variant_invalid_barcode_count'] = $row['invallid_barcode_count'];
                            $result[$row['merchant_id']]['variant_invalid_barcode'] = $row['upc'];
                            $result[$row['merchant_id']]['variant_invalid_barcode_products'] = $row['product_ids'];
                            $result[$row['merchant_id']]['variant_nvalid_mpn'] = $row['mpn'];
                            $result[$row['merchant_id']]['variant_invalid_asin'] = $row['asin'];
                        }
                        else{
                            $result[$row['merchant_id']] = [];
                            $result[$row['merchant_id']]['variant_invalid_barcode_count'] = $row['invallid_barcode_count'];
                            $result[$row['merchant_id']]['variant_invalid_barcode'] = $row['upc'];
                            $result[$row['merchant_id']]['variant_invalid_barcode_products'] = $row['product_ids'];
                            $result[$row['merchant_id']]['variant_nvalid_mpn'] = $row['mpn'];
                            $result[$row['merchant_id']]['variant_invalid_asin'] = $row['asin'];
                        }
                    }
                    /*** End Validate Barcode ***/

                    $query = "SELECT count(`merchant_id`) `invalid_des`,`merchant_id`,GROUP_CONCAT(`id`) as `product_ids` FROM `jet_product` WHERE ((CHAR_LENGTH(`description`))<1 OR (CHAR_LENGTH(`description`))>2000) GROUP BY `merchant_id` ";

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        $result[$row['merchant_id']]['invalid_description_count'] = $row['invalid_des'];
                        $result[$row['merchant_id']]['invalid_description_products'] = $row['product_ids'];
                    }


                   


                    //var_dump($data);die;
                    print_r($result);die;
                    
                }
                else
                {


                    $merhcantIds = implode(',',$post['selection']);

                    /***Validate Sku***/

                    $query = "SELECT count(`merchant_id`) `invalid_skus`,`merchant_id`,GROUP_CONCAT(`sku`) as `sku` FROM `jet_product` WHERE `merchant_id` IN ({$merhcantIds}) AND `type`='simple' AND `sku` LIKE '%/%' OR `sku` LIKE '%+%' OR `sku` LIKE '%*%' OR `sku` LIKE '%:%' GROUP BY `merchant_id` ";
                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    
                    foreach($data as $row){
                        $result[$row['merchant_id']] = ['invalid_skus_count'=>$row['invalid_skus'],'invalid_skus'=>$row['sku']];
                    }


                    $query = "SELECT count(`merchant_id`) `invalid_skus`,`merchant_id`,GROUP_CONCAT(`option_sku`) as `sku` FROM `jet_product_variants` WHERE `merchant_id` IN ({$merhcantIds}) AND `option_sku` LIKE '%/%' OR `option_sku` LIKE '%+%' OR `option_sku` LIKE '%*%' OR `option_sku` LIKE '%:%'  GROUP BY `merchant_id` ";
                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        if(isset($result[$row['merchant_id']])){
                            $result[$row['merchant_id']]['invalid_variant_skus_count'] = $row['invalid_skus'];
                            $result[$row['merchant_id']]['invalid_variant_skus'] = $row['sku'];
                        }
                        else{
                            $result[$row['merchant_id']] = ['invalid_variant_skus_count'=>$row['invalid_skus'],'invalid_variant_skus'=>$row['sku']];
                        }
                    }
                    /***End Validate Sku***/

                    /***End Validate Title***/

                    $query = "SELECT count(`merchant_id`) `invalid_titles`,`merchant_id`,GROUP_CONCAT(`title`) as `title` FROM `jet_product` WHERE `merchant_id` IN ({$merhcantIds}) AND `type`='simple' AND ((CHAR_LENGTH(`title`))<5 OR (CHAR_LENGTH(`title`))>500 OR `title` LIKE '%sale%' OR `title` LIKE '%offer%' OR `title` LIKE '%new%' ) GROUP BY `merchant_id` ";

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        $result[$row['merchant_id']]['invalid_titles_count'] = $row['invalid_titles'];
                        $result[$row['merchant_id']]['invalid_titles'] = $row['title'];
                    }


                    $query = "SELECT count(`merchant_id`) `invalid_titles`,`merchant_id`,GROUP_CONCAT(`option_title`) as `title` FROM `jet_product_variants` WHERE `merchant_id` IN ({$merhcantIds}) AND ((CHAR_LENGTH(`option_title`))<5 OR (CHAR_LENGTH(`option_title`))>500 OR `option_title` LIKE '%sale%' OR `option_title` LIKE '%offer%' OR `option_title` LIKE '%new%' ) GROUP BY `merchant_id` ";

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        if(isset($result[$row['merchant_id']])){
                            $result[$row['merchant_id']]['invalid_variant_titles_count'] = $row['invalid_titles'];
                            $result[$row['merchant_id']]['invalid_variant_titles'] = $row['title'];
                        }
                        else{
                            $result[$row['merchant_id']] = [];
                            $result[$row['merchant_id']]['invalid_variant_titles_count'] = $row['invalid_titles'];
                            $result[$row['merchant_id']]['invalid_variant_titles'] = $row['title'];
                        }
                    }

                    /***End Validate Title***/


                    /*** Validate Barcode ***/
                    $query = "SELECT count(`merchant_id`) `invallid_barcode_count`,`merchant_id`,GROUP_CONCAT(`upc`) as `upc`,GROUP_CONCAT(`mpn`) as `mpn`,GROUP_CONCAT(`ASIN`) as `asin`,GROUP_CONCAT(`id`) as `product_ids` FROM `jet_product` WHERE `merchant_id` IN ({$merhcantIds}) AND `type`='simple' AND ((CHAR_LENGTH(`upc`))!=12 AND (CHAR_LENGTH(`upc`))!=14 AND (CHAR_LENGTH(`upc`))!=13 AND CHAR_LENGTH(`upc`)!=10) AND (CHAR_LENGTH(`ASIN`)!=10) AND (CHAR_LENGTH(`mpn`)>50 OR CHAR_LENGTH(`mpn`)<1 ) GROUP BY `merchant_id` " ;


                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    
                    foreach($data as $row){
                        $result[$row['merchant_id']]['invalid_barcode_count'] = $row['invallid_barcode_count'];
                        $result[$row['merchant_id']]['invalid_barcode'] = $row['upc'];
                        $result[$row['merchant_id']]['invalid_barcode_products'] = $row['product_ids'];
                        $result[$row['merchant_id']]['invalid_mpn'] = $row['mpn'];
                        $result[$row['merchant_id']]['invalid_asin'] = $row['asin'];
                    }


                    $query = "SELECT count(`merchant_id`) `invallid_barcode_count`,`merchant_id`,GROUP_CONCAT(`option_unique_id`) as `upc`,GROUP_CONCAT(`option_mpn`) as `mpn`,GROUP_CONCAT(`asin`) as `asin`,GROUP_CONCAT(`option_id`) as `product_ids` FROM `jet_product_variants` WHERE `merchant_id` IN ({$merhcantIds}) AND ((CHAR_LENGTH(`option_unique_id`))!=12 AND(CHAR_LENGTH(`option_unique_id`))!=14 AND (CHAR_LENGTH(`option_unique_id`))!=13 AND CHAR_LENGTH(`option_unique_id`)!=10) AND (CHAR_LENGTH(`asin`)!=10) AND (CHAR_LENGTH(`option_mpn`)>50 OR CHAR_LENGTH(`option_mpn`)<1 ) GROUP BY `merchant_id` " ;

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    
                    foreach($data as $row){
                        if(isset($result[$row['merchant_id']])){
                            $result[$row['merchant_id']]['variant_invalid_barcode_count'] = $row['invallid_barcode_count'];
                            $result[$row['merchant_id']]['variant_invalid_barcode'] = $row['upc'];
                            $result[$row['merchant_id']]['variant_invalid_barcode_products'] = $row['product_ids'];
                            $result[$row['merchant_id']]['variant_nvalid_mpn'] = $row['mpn'];
                            $result[$row['merchant_id']]['variant_invalid_asin'] = $row['asin'];
                        }
                        else{
                            $result[$row['merchant_id']] = [];
                            $result[$row['merchant_id']]['variant_invalid_barcode_count'] = $row['invallid_barcode_count'];
                            $result[$row['merchant_id']]['variant_invalid_barcode'] = $row['upc'];
                            $result[$row['merchant_id']]['variant_invalid_barcode_products'] = $row['product_ids'];
                            $result[$row['merchant_id']]['variant_nvalid_mpn'] = $row['mpn'];
                            $result[$row['merchant_id']]['variant_invalid_asin'] = $row['asin'];
                        }
                    }
                    /*** End Validate Barcode ***/

                    $query = "SELECT count(`merchant_id`) `invalid_des`,`merchant_id`,GROUP_CONCAT(`id`) as `product_ids` FROM `jet_product` WHERE `merchant_id` IN ({$merhcantIds}) AND ((CHAR_LENGTH(`description`))<1 OR (CHAR_LENGTH(`description`))>2000) GROUP BY `merchant_id` ";

                    $data = Yii::$app->db->createCommand($query)->queryAll();
                    foreach($data as $row){
                        $result[$row['merchant_id']]['invalid_description_count'] = $row['invalid_des'];
                        $result[$row['merchant_id']]['invalid_description_products'] = $row['product_ids'];
                    }


                   


                    //var_dump($data);die;
                    //print_r($result);die('celect');

                }
                
            }
        }
        else
        {
            return $this->redirect(['index']);
        }
        
        
    }


}
