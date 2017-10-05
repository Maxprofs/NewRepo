<?php

namespace frontend\modules\walmartapi\controllers;
use Yii;
use yii\helpers\BaseJson;
use yii\web\NotFoundHttpException;
use frontend\modules\walmartapi\components\Uploadproduct;
use frontend\modules\walmartapi\components\Datahelper;
use frontend\modules\walmartapi\components\Walmartapi;

class WalmartproductController extends WalmartapiController
{
    protected $connection;
    protected $walmartHelper;
    /**
     * product list
     * @return jsonarray  
    */
    public function actionList()
    {
        $pageInfo = Yii::$app->request->post();
        $dynamicFilter = ['title'=>array('title'=>'title','type'=>'text','format'=>'string','tag'=>'title'),'sku'=>array('title'=>'sku','type'=>'text','format'=>'string','tag'=>'sku'),'type'=>array('title'=>'Type','type'=>'text','format'=>'string','tag'=>'type','value'=>array('simple'=>'string','variants'=>'string')),/*'merchant_id'=>array('title'=>'Merchant Id','type'=>'range','format'=>'int','tag'=>'merchant_id'),*/'status'=>array('title'=>'Status','type'=>'dropdown','format'=>'string','tag'=>'status','value'=>array('IN_PROGRESS'=>'string','Item Processing'=>'string','Not Uploaded'=>'string','PUBLISHED'=>'string','STAGE'=>'string','SYSTEM_PROBLEM'=>'string','UNPUBLISHED'=>'string'))];
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        if (isset($pageInfo['page'])) {
            $page = $pageInfo['page'];
        } else {
            $page = 0;
        }

        $limit = 20;

        $page = $page * $limit;
        $requestproduct = [];
        if(isset($pageInfo['filter'])){
            $pageInfo['filter'] = BaseJson::decode($pageInfo['filter']);
            $jetTableAliasName = 'jet.';
            $walmartTableAliasName = 'wal.';
            $allowFilter = ['title' => $jetTableAliasName."title",
                              'sku' => $jetTableAliasName."sku" ,
                              'type' => $jetTableAliasName."type",
                              'merchant_id' => $jetTableAliasName."merchant_id",
                              'status' => $walmartTableAliasName.'status'];
            foreach ($pageInfo['filter'] as $condition => $value) 
                {
                   if(isset($allowFilter[$condition]) && !empty($value)) {
                            if(gettype($value) == 'string'){
                                $condition = $allowFilter[$condition];
                                $fields[] = sprintf("%s LIKE '%s'",
                                $condition, '%'.$value.'%');
                            }
                            else{
                                $condition =  $allowFilter[$condition];
                                $fields[] = sprintf("%s = '%s'",
                                $condition, $value);
                            }
                            
                       }
                    

                }
                if (count($fields) > 0)
                {
                    $whereClause = "WHERE " . implode(" AND ", $fields).' AND wal.merchant_id ='.$merchant_id;
                    $query = 'select title,sku,type,description,variant_id,image,qty,weight,price,vendor,wal.product_id,wal.short_description,wal.self_description,wal.status,wal.error from `jet_product` jet INNER JOIN `walmart_product` wal ON wal.product_id=jet.id '.$whereClause.' LIMIT '.$page.','.$limit.'';
                        $jetProduct = Datahelper::sqlRecords($query, 'all');

                        foreach ($jetProduct as $product) {
                            if ($product['type'] == 'variants') {
                                $data = $this->variantsProduct($product['product_id']);
                                $product['variants'] = $data;
                            }
                            $product['id'] = $product['product_id'];
                            $requestproduct[] = $product ;
                        }
                        $validateData = ['success' =>true ,'message' =>'Filter Apply successfully','data'=>array('product'=>$requestproduct,'filter'=>$dynamicFilter)];
                        return BaseJson::encode($validateData);
                }
                else{
                    $validateData = ['success' =>false ,'message' =>'Not Found'];
                    return BaseJson::encode($validateData);
                }
        }
        else{
            $query = 'select title,sku,type,description,variant_id,image,qty,weight,price,vendor,wal.product_id,wal.short_description,wal.self_description,wal.status,wal.error from `jet_product` jet INNER JOIN `walmart_product` wal ON wal.product_id=jet.id where wal.merchant_id ='.$merchant_id.' LIMIT '.$page.','.$limit.'';
            $jetProduct = Datahelper::sqlRecords($query, 'all');
            foreach ($jetProduct as $product) {
                if ($product['type'] == 'variants') {
                    $data = $this->variantsProduct($product['product_id']);
                    $product['variants'] = $data;
                }
                $requestproduct[] = $product ;
            }
            $validateData = ['success' =>true ,'message' =>'All walmart product list','data'=>array('product'=>$requestproduct,'filter'=>$dynamicFilter)];
            return BaseJson::encode($validateData);
        }
        
        
    }
    /**
     * variants product list
     * @return array  
    */
    public function variantsProduct($productId)
    {
       $query = 'select *,wal.* from `jet_product_variants` jet INNER JOIN `walmart_product_variants` wal ON wal.option_id=jet.option_id where wal.product_id="'.$productId.'"';
       $variantsProduct = Datahelper::sqlRecords($query, 'all');
       return $variantsProduct;

    }
    /**
     * product view
     * @return jsonarray  
    */
    public function actionView()
    {
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $pageInfo = Yii::$app->request->post();
        if(isset($pageInfo['product_id'])){
            $query = 'select title,sku,type,description,variant_id,image,qty,weight,price,vendor,wal.* from `jet_product` jet INNER JOIN `walmart_product` wal ON wal.product_id=jet.id where wal.merchant_id ='.$merchant_id.' AND wal.product_id="'.$pageInfo['product_id'].'" LIMIT 0,1';

            $jetProduct = Datahelper::sqlRecords($query, 'one');
            if($jetProduct){
                if ($jetProduct['type'] == 'variants') {
                    $data = $this->variantsProduct($jetProduct['product_id']);
                    $jetProduct['variants'] = $data;
                }
                $requestproduct[] = $jetProduct ;
                $validateData = ['success' => true, 'message' => 'successfully product view', 'data' => $requestproduct];
                return BaseJson::encode($validateData);
            }
            else{
                $validateData = ['success' =>false ,'message' =>'Product not found.'];
                return BaseJson::encode($validateData);
            }
            
        }
        else{
             $validateData = ['success' =>false ,'message' =>'No Product Selected'];
             return BaseJson::encode($validateData);
        }
    }

    /**
     * upload multiple product
     * @return json_array  
    */
    public function actionUpload()
    {
        $pageInfo = Yii::$app->request->post();
        $selectedProducts = explode(',',$pageInfo['ids']);
        $count = count($selectedProducts);
        if(!$count) {
            $returnArr = ['success'=>false, 'message'=>'No Products to Upload'];
            return BaseJson::encode($returnArr);
        } 
        else 
        {   if($count < 51){
                $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');


                try {
                    $jetConfig=[];
                    $jetConfig = Datahelper::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$merchant_id."'", 'one');
                    if($jetConfig)
                    {
                        $consumer_channel_type_id = $jetConfig['consumer_channel_type_id'];
                        $consumer_id = $jetConfig['consumer_id'];
                        $secret_key = $jetConfig['secret_key'];
                        $this->walmartHelper = new Walmartapi($consumer_id ,$secret_key,$consumer_channel_type_id);
                        $connection = Yii::$app->getDb();
                        define("MERCHANT_ID",$merchant_id);

                        $productResponse = $this->walmartHelper->createProductOnWalmart($selectedProducts,$this->walmartHelper,$merchant_id,$connection);             print_r($productResponse);die;      
                        if(is_array($productResponse) && isset($productResponse['uploadIds'],$productResponse['feedId']) && count($productResponse['uploadIds']>0))
                        {
                            //save product status and data feed
                            $ids = implode(',',$productResponse['uploadIds']);
                            foreach($productResponse['uploadIds'] as $val)
                            {
                                $query="UPDATE `walmart_product` SET status='Items Processing', error='' where product_id='".$val."'";
                                Datahelper::sqlRecords($query,null,"update");
                            }
                            $query="INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`)VALUES('".MERCHANT_ID."','".$productResponse['feedId']."','".$ids."')";
                            Datahelper::sqlRecords($query,null,"insert");
                            
                            $msg = "product successfully submitted on walmart.";
                            $feed_count = count($productResponse['uploadIds']);
                            $feedId = $productResponse['feedId'];
                            $returnArr = ['success'=>true, 'message'=>$msg, 'count'=>$feed_count, 'feed_id'=>$feedId];
                        }
                        elseif(isset($productResponse['errors'])) {
                            foreach ($productResponse['errors'] as $key => $value) {
                                $sku = $key;
                            }
                            $msg = json_encode($productResponse['errors']);
                            $returnArr = ['error'=>true, 'message'=>$msg];
                            $apistatusmessage[$sku]['Product with sku'] = $sku ;
                            $apistatusmessage[$sku]['Error from Walmart'] = json_encode($productResponse['errors']);
                            $apistatusmessage[$sku]['success'] = 'false';
                            $apistatusmessage[$sku]['message'] = 'product not uploaded';
                        }
                        elseif (isset($productResponse['feedError'])) {
                            $msg = json_encode($productResponse['feedError']);
                            $returnArr = ['success'=>true, 'message'=>$msg];
                        }

                        //save errors in database for each erroed product
                        $returnArr['error_count'] = 0;
                        if(isset($productResponse['erroredSkus']))
                        {
                            foreach($productResponse['erroredSkus'] as $productSku=>$error)
                            {
                                if(is_array($error))
                                    $error = implode(',', $error);

                                $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id SET wp.`error`='".$error."' where jp.sku='".$productSku."'";
                                Datahelper::sqlRecords($query,null,"update");
                            }
                            $returnArr['error_count'] = count($productResponse['erroredSkus']);
                            $returnArr['erroredSkus'] = implode(',',array_keys($productResponse['erroredSkus']));
                        }
                    }
                    else{
                        $returnArr = ['success'=>false ,'message'=>"Please fill the walmart setting from your shopify panel"];
                        return BaseJson::encode($returnArr);

                    }

                }
                catch (Exception $e)
                {
                    $returnArr = ['success'=>false, 'message'=>$e->getMessage()];
                }
            }
            else{
                $returnArr = ['success'=>false, 'message'=>'Upload only 50 products at a time'];
                return BaseJson::encode($returnArr);
            }
        }
        $validateData = ['data' =>$validateData,'message' =>'Not Found'];
        return BaseJson::encode($validateData);
    }
   

    /**
     * change product thresold value for notification
     * @return notification meaaage 
    */
    public function actionChangeProductThreshold()
    {
        $getRequest = Yii::$app->request->post();
        if(isset($getRequest['product_threshold']) && !empty($getRequest['product_threshold']) && isset($getRequest['merchant_id']) && !empty($getRequest['merchant_id'])){
            $merchant_id = $getRequest['merchant_id'];
            $thresholdvalue = $getRequest['product_thresholdvalue'];
            $model = Datahelper::sqlRecords("INSERT INTO `walmart_config` (`merchant_id`,`data`,`value`) VALUES ('".$merchant_id."','product_threshold','".$thresholdvalue."')", 'all','insert');
             $returnArr = ['success'=>true, 'message'=>'ProductThreshold successfully Saved'];
                return BaseJson::encode($returnArr);
        }
        else{
            $returnArr = ['success'=>false, 'message'=>'Invalid Data provided'];
            return BaseJson::encode($returnArr);
        }

            
    }

}
         
                          