<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetrepricing;
use Yii;
/**
 * JetrepricingController implements the CRUD actions for JetDynamicPrice model.
 */
class JetrepricingController extends JetmainController
{
    
    public function actionStartbatchupdatesingly(){
        $noDataSkus = [];
        if($data = Yii::$app->request->post('data')){
            $data = json_decode($data, true);
            $merchant_id = MERCHANT_ID;
            list($flag, $noDataSkus) = Jetrepricing::getMarketplacePriceSingly($data,$this->jetHelper,$merchant_id);
            if($flag){
                return json_encode(['success'=>'Data Fetched Successfully.', 'no_data'=>$noDataSkus]);
            }
        }
        return json_encode(['error'=>'Data Not Fetched.', 'no_data'=>$noDataSkus]);
    }
    public function actionStartbatchupdate(){
        $noDataSkus = [];
        if($data = Yii::$app->request->post('data')){
            $data = json_decode($data, true);
            //echo "<pre>";print_r($data);die("</pre>");
            $merchant_id = MERCHANT_ID;
            list($flag, $noDataSkus) = Jetrepricing::getMarketplacePrice($data,$merchant_id);
            if($flag){
                return json_encode(['success'=>'Data Fetched Successfully.', 'no_data'=>$noDataSkus]);
            }
        }
        return json_encode(['error'=>'Data Not Fetched.', 'no_data'=>$noDataSkus]);
    }
    
    public function actionGetrepricingsingly(){
        $merchant_id = MERCHANT_ID;
        $product = [];
        $chunks = [];
        $sql = "SELECT `id` as `product_id`, `variant_id` ,`sku`,`type` FROM `jet_product` WHERE type='simple' AND status='Available for Purchase' AND merchant_id=".$merchant_id. " UNION Select `product_id` , `option_id` as `variant_id`, `option_sku` as `sku`, 'variants' from jet_product_variants where status='Available for Purchase' AND merchant_id=".$merchant_id;
        $product = Data::sqlRecords($sql,'all','select');
        $totalcount = count($product)?count($product):0;
        if($totalcount==0){
            Yii::$app->session->setFlash('error', "No product present on 'Available for Purchase' status.");
            return $this->redirect(\yii\helpers\Url::to(Yii::$app->request->referrer));
        }
        $chunks = array_chunk($product, 15);
        $pages = count($chunks)?count($chunks):0;
        $productJson = isset($chunks)?json_encode($chunks):"";
        return $this->render('getrepricingsingly', ['jsonData'=>$productJson, 'merchant_id'=>$merchant_id, 'pages'=>$pages, 'totalcount'=>$totalcount]);
    }

    public function actionGetrepricingbkp(){
        $chunkSize = 100;
        $merchant_id = MERCHANT_ID;
        $json = false;
        $salesArray = [];
        $tableProducts = [];
        $skusTableArray = [];
        $finalProductArray = [];
        if(Jetrepricing::getSalesDataNSaveInFile($merchant_id, $this->jetHelper)){
            $json = Jetrepricing::getSalesDataJson($merchant_id);
        }
        if($json && strlen($json)>0){
            $salesArray = json_decode($json, true); 
            if(isset($salesArray['SalesData'])){
                $salesArray = $salesArray['SalesData'];
            }
        }
        if(count($salesArray)==0){
            Yii::$app->session->setFlash('error', "Data Not Available on Jet.");
            return $this->redirect(\yii\helpers\Url::to(Yii::$app->request->referrer));
        }
        $sql = "SELECT `id` as `product_id`, `variant_id` ,`sku`,`type` FROM `jet_product` WHERE type='simple' AND status='Available for Purchase' AND merchant_id=".$merchant_id. " UNION Select `product_id` , `option_id` as `variant_id`, `option_sku` as `sku`, 'variants' from jet_product_variants where status='Available for Purchase' AND merchant_id=".$merchant_id;
        $tableProducts = Data::sqlRecords($sql,'all','select');
        if(count($tableProducts)>0){
            $skusTableArray = array_reduce($tableProducts, function ($result, $item) {
                $result[$item['sku']] = $item;
                return $result;
            });
            //echo "<pre>";print_r($skusTableArray);die('</pre>');
        }
        $finalProductArray = array_merge_recursive(array_intersect_key($salesArray, $skusTableArray), $skusTableArray);
        //echo "<pre>";print_r($finalProductArray);die('</pre>');
        $totalcount = count($finalProductArray)?count($finalProductArray):0;
        if($totalcount==0){
            Yii::$app->session->setFlash('error', "No product present of 'Available for Purchase' status.");
            return $this->redirect(\yii\helpers\Url::to(Yii::$app->request->referrer));
        }
        $chunks = array_chunk($finalProductArray, $chunkSize, true);
        $pages = count($chunks)?count($chunks):0;
        $productJson = isset($chunks)?json_encode($chunks):"";
        //echo count($salesArray)."<hr/>".count($tableProducts)."<hr/>$totalcount<hr/><pre>";print_r($chunks);die('</pre>');
        return $this->render('getrepricing-bkp', ['jsonData'=>json_encode($productJson), 'merchant_id'=>$merchant_id, 'pages'=>$pages, 'totalcount'=>$totalcount]);
    }
    public function actionGetrepricing(){
        $merchant_id = MERCHANT_ID;
        $sql = "SELECT `id` as `product_id`, `variant_id` ,`sku`,`type` FROM `jet_product` WHERE type='simple' AND status='Available for Purchase' AND merchant_id=".$merchant_id. " UNION Select `product_id` , `option_id` as `variant_id`, `option_sku` as `sku`, 'variants' from jet_product_variants where status='Available for Purchase' AND merchant_id=".$merchant_id;
        $tableProducts = Data::sqlRecords($sql,'all','select');
        if(count($tableProducts)==0){
            Yii::$app->session->setFlash('error', "No product present of 'Available for Purchase' status.");
            return $this->redirect(\yii\helpers\Url::to(Yii::$app->request->referrer));
        }
        return $this->render('getrepricing');
    }
    public function actionGetrepricingdata()
    {
        $chunkSize = 100;
        $merchant_id = MERCHANT_ID;
        $json = false;
        $salesArray = [];
        $tableProducts = [];
        $skusTableArray = [];
        $finalProductArray = [];
        if(Jetrepricing::getSalesDataNSaveInFile($merchant_id, $this->jetHelper,'SalesData')){
            $json = Jetrepricing::getSalesDataJson($merchant_id,'SalesData');
        }
        if($json && strlen($json)>0){
            $salesArray = json_decode($json, true); 
            if(isset($salesArray['SalesData'])){
                $salesArray = $salesArray['SalesData'];
            }
        }
        if(count($salesArray)==0){
            return json_encode(['error'=>"Data Not Available on Jet."]);
        }
        $sql = "SELECT `id` as `product_id`, `variant_id` ,`sku`,`type` FROM `jet_product` WHERE type='simple' AND status='Available for Purchase' AND merchant_id=".$merchant_id. " UNION Select `product_id` , `option_id` as `variant_id`, `option_sku` as `sku`, 'variants' from jet_product_variants where status='Available for Purchase' AND merchant_id=".$merchant_id;
        $tableProducts = Data::sqlRecords($sql,'all','select');
        if(count($tableProducts)>0){
            $skusTableArray = array_reduce($tableProducts, function ($result, $item) {
                $result[$item['sku']] = $item;
                return $result;
            });
            //echo "<pre>";print_r($skusTableArray);die('</pre>');
        }
        $finalProductArray = array_merge_recursive(array_intersect_key($salesArray, $skusTableArray), $skusTableArray);
        //echo "<pre>";print_r($finalProductArray);die('</pre>');
        $totalcount = count($finalProductArray)?count($finalProductArray):0;
        if($totalcount==0){
            return json_encode(['error'=>"No product present of 'Available for Purchase' status."]);
        }
        $chunks = array_chunk($finalProductArray, $chunkSize, true);
        $pages = count($chunks)?count($chunks):0;
        Jetrepricing::invalidateRepricingSavedData($merchant_id);
        //$productJson = isset($chunks)?json_encode($chunks):"";
        //echo count($salesArray)."<hr/>".count($tableProducts)."<hr/>$totalcount<hr/><pre>";print_r($chunks);die('</pre>');
        return json_encode(['jsonData'=>$chunks, 'merchant_id'=>$merchant_id, 'pages'=>$pages, 'totalcount'=>$totalcount]);
    }
    
    public function actionDynamicprice(){
        $this->layout="main2";
        $product_id = trim(Yii::$app->request->post('id'));
        $type = trim(Yii::$app->request->post('type'));
        $product_title = trim(Yii::$app->request->post('title'));
        $best = trim(Yii::$app->request->post('best'));
        $better = trim(Yii::$app->request->post('better'));
        $buyboxWhere = "";
        if($best==1 && $better==1){
            $buyboxWhere = " AND (`buybox_status`!='') "; 
        }elseif($best==1){
            $buyboxWhere = " AND (`buybox_status`='1') "; 
        }elseif($better==1){
            $buyboxWhere = " AND (`buybox_status`='0') "; 
        }
        $merchant_id = MERCHANT_ID;
        $sql = "";
        /*if($type=="simple"){
            $sql = "Select * from (Select id, merchant_id, title, status, variant_id , product_type from jet_product where `merchant_id`=".$merchant_id." AND `id`=".$product_id.")
            as jp LEFT JOIN (Select product_id, merchant_id, update_title from jet_product_details 
            where update_title IS NOT NULL AND update_title != '' AND `merchant_id`=".$merchant_id."  AND `product_id`=".$product_id.") as jpd ON jp.merchant_id= jpd.merchant_id 
            AND jp.id=jpd.product_id INNER JOIN (Select * from jet_repricing where `merchant_id`=".$merchant_id."  AND `product_id`=".$product_id.") as jr ON jr.merchant_id=jp.merchant_id AND jr.product_id=jp.id AND jp.variant_id=jr.variant_id";
        }else{
              $sql = "Select * from (SELECT option_id,merchant_id, product_id, status FROM `jet_product_variants`
              where `merchant_id`=".$merchant_id." AND `product_id`=".$product_id.")
              as jpv INNER JOIN (Select id, merchant_id, title, product_type from jet_product where `merchant_id`=".$merchant_id." AND `id`=".$product_id.")
              as jp ON jpv.merchant_id=jp.merchant_id AND jpv.product_id=jp.id LEFT JOIN (Select product_id, merchant_id, update_title from jet_product_details 
              where update_title IS NOT NULL AND update_title != '' AND `merchant_id`=".$merchant_id."  AND `product_id`=".$product_id.") as jpd ON jpv.merchant_id= jpd.merchant_id 
              AND jpv.product_id=jpd.product_id INNER JOIN (Select * from jet_repricing where `merchant_id`=".$merchant_id."  AND `product_id`=".$product_id.") as jr ON jr.merchant_id=jpv.merchant_id AND jr.product_id=jpv.product_id AND jpv.option_id=jr.variant_id";
        }*/

        if($type=="simple"){
            $sql = "Select * from (Select id, merchant_id, status, variant_id from jet_product where `merchant_id`='".$merchant_id."' AND `id`='".$product_id."')
            as jp INNER JOIN (Select * from jet_repricing where `merchant_id`='".$merchant_id."'  AND `product_id`='".$product_id."'".$buyboxWhere.") as jr ON jr.merchant_id=jp.merchant_id AND jr.product_id=jp.id AND jp.variant_id=jr.variant_id";
        }else{
              $sql = "Select * from (SELECT option_id,merchant_id, product_id, status FROM `jet_product_variants`
              where `merchant_id`='".$merchant_id."' AND `product_id`='".$product_id."')
              as jpv INNER JOIN (Select * from jet_repricing where `merchant_id`='".$merchant_id."'  AND `product_id`='".$product_id."'".$buyboxWhere.") as jr ON jr.merchant_id=jpv.merchant_id AND jr.product_id=jpv.product_id AND jpv.option_id=jr.variant_id";
        }
        
        $data = Data::sqlRecords($sql,'all','select');
        //echo "<pre>";print_r($data);die("</pre>");
        $html = $this->render(
                    'dynamicpricing',
                    [
                        'id'=>$product_id,
                        'product_title'=>$product_title,
                        'type'=>$type,
                        'additionalData'=> $data,
                        'bidPrice' => Jetrepricing::BID,
                        'better' => $better
                    ],
                    true
        );
        return $html;
    }
    public function actionSave(){
        try{
            $post = Yii::$app->request->post();
            $product_id = isset($post['product_id'])?$post['product_id']:"";
            $merchant_id = MERCHANT_ID;
            $data = 0;
            if(isset($post['data']) && $product_id && $merchant_id){
               foreach($post['data'] as $variant_id => $value){
                    if(!is_numeric($value['min_price']) || $value['min_price']==0){
                        return json_encode(['success'=>false, 'detail'=>'Minimum Threshold Price must be numeric. Information not saved.']);
                    }
                    $query = "";
                    $query = "UPDATE `jet_repricing` SET `enable`='".$value['enable']."',`min_price`='".$value['min_price']."' WHERE `product_id`='".$product_id."' AND `merchant_id`='".$merchant_id."' AND `variant_id`='".$variant_id."'";
                    $data = Data::sqlRecords($query,'one','update');
                }
            }else{
                return json_encode(['success'=>false, 'detail'=>'Data Missing. Information not saved.']);
            }
        }catch(yii\db\Exception $e){
            return json_encode(['success'=>false, 'detail'=>'Error Occured. Information not saved.']);
        }
        catch(Exception $e){
            return json_encode(['success'=>false, 'detail'=>'Error Occured. Information not saved.']);
        }
        return json_encode(['success'=>true, 'count'=>$data]);
    }
    
}
