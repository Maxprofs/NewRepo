<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Neweggapi;
use Yii;
use frontend\modules\neweggmarketplace\models\NeweggProductFeed;
use frontend\modules\neweggmarketplace\models\NeweggProductFeedSearch;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use frontend\modules\neweggmarketplace\components\Helper;

/**
 * WalmartproductfeedController implements the CRUD actions for WalmartProductFeed model.
 */
class NeweggproductfeedController extends NeweggMainController
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

    /**
     * Lists all WalmartProductFeed models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel = new NeweggProductFeedSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionBulkfeedstatus()
    {
        $pages = 1;
        $session = "";
        $session = Yii::$app->session;

        $feed_ids = (array)Yii::$app->request->post('selection');

        if (!empty($feed_ids)) {

            $session['feed_details'] = array('ids' => $feed_ids, 'pages' => $pages);

            return $this->render('feedupdate', [
                'totalcount' => count($feed_ids),
                'pages' => $pages
            ]);
        }
        Yii::$app->session->setFlash('error', "Please select the Feed Id");
        return $this->redirect('index');

    }

    public function actionUpdatefeedstatus()
    {
        $feed_array=[];
        $session = "";
        $count =0;
        $session = Yii::$app->session;
        $feed_details = $session['feed_details'];

        foreach ($feed_details['ids'] as $feed_id) {

           $query = Data::sqlRecords('SELECT status FROM `newegg_product_feed` WHERE feed_id="'.$feed_id.'"','one');
            if($query['status'] == Data::PRODUCT_STATUS_SUBMITTED) {
                $wal = new Neweggapi(SELLER_ID, AUTHORIZATION, SECRET_KEY);
                $feed_data = $wal->getRequest('/datafeedmgmt/feeds/result/'.$feed_id);
                $getResponse = json_decode($feed_data);
                //print_r($getResponse);die;
                try {
                    $content = [];
                    if(!empty($getResponse))
                    {
                        if(isset($getResponse->Code)){
                            $query="update `newegg_product_feed` set error ='".$getResponse->Message."' where feed_id='".$feed_id."'";
                            Data::sqlRecords($query,null,"update");
                        }
                        else
                        {
                            if(isset($getResponse->NeweggEnvelope->Message->ProcessingReport->ProcessingSummary) && $getResponse->NeweggEnvelope->Message->ProcessingReport->ProcessingSummary->WithErrorCount>0){
                                if(isset($getResponse->NeweggEnvelope->Message->ProcessingReport->Result) && isset($getResponse->NeweggEnvelope->Message->ProcessingReport->Result->ErrorList)){
                                    $errorDescription = $getResponse->NeweggEnvelope->Message->ProcessingReport->Result;
                                    $arrayData = json_encode($errorDescription);
                                    $value = json_decode($arrayData,true);
                                   // $id = str_replace('SPN','',$value['AdditionalInfo']['SellerPartNumber']);
                                    $id = $value['AdditionalInfo']['SellerPartNumber'];
                                    $squery = "SELECT `id` FROM `jet_product` WHERE  `sku`='".$id."' AND `merchant_id`='".MERCHANT_ID."'";
                                    $productData = Data::sqlRecords($squery, "one", "select");
                                    $id = $productData['id'];
                                    $feed_array[$id]=true;
                                    $checkProduct = substr(trim($value['ErrorList']['ErrorDescription'][0]),0,5);
                                    if($checkProduct=='Error'){
                                        if(is_array($value['ErrorList']['ErrorDescription'])){
                                            unset($value['ErrorList']['ErrorDescription'][0]);
                                            foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                                $content[]=$value1;
                                            }
                                        }
                                        $errorFromNewegg = implode(',', $content);
                                        $status = Helper::getProdcutStatus($id);
                                        if($status==Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR || $status==Data::PRODUCT_STATUS_ACTIVATED){
                                            $errorMsg = substr($errorFromNewegg, 0, 1000);
                                            $errorMsg =$errorMsg.'...';
                                            $query="update `newegg_product` set  error='".addslashes($errorMsg)."' where product_id='".$id."'";
                                            Data::sqlRecords($query,null,"update");
                                            $content = [];

                                        }
                                        else{
                                            $errorMsg = substr($errorFromNewegg, 0, 1000);
                                            $errorMsg =$errorMsg.'...';
                                            $query="update `newegg_product` set upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' , error='".addslashes($errorMsg)."' where product_id='".$id."'";
                                            Data::sqlRecords($query,null,"update");
                                            $content = [];

                                        }
                                    }
                                    else{
                                        if(is_array($value['ErrorList']['ErrorDescription'])){
                                            unset($value['ErrorList']['ErrorDescription'][0]);
                                            foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                                $content[]=$value1;
                                            }
                                        }
                                        else{
                                            $content[]=$value['ErrorList']['ErrorDescription'];
                                        }
                                        $errorFromNewegg = implode(',', $content);
                                        $errorMsg = substr($errorFromNewegg, 0, 1000);
                                        $errorMsg =$errorMsg.'...';
                                        $query="update `newegg_product` set upload_status='".Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR."' , error='".addslashes($errorMsg)."' where product_id='".$id."'";
                                        Data::sqlRecords($query,null,"update");
                                        $content = [];
                                        $count++;
                                    }

                                    
                                }
                                else{
                                    $errorDescription = $getResponse->NeweggEnvelope->Message->ProcessingReport->Result;
                                    $arrayData = json_encode($errorDescription);
                                    $error = json_decode($arrayData,true);
                                    foreach ($error as $key => $value) {
                                        $id = $value['AdditionalInfo']['SellerPartNumber'];
                                        $squery = "SELECT `id` FROM `jet_product` WHERE  `sku`='".$id."' AND `merchant_id`='".MERCHANT_ID."'";
                                        $productData = Data::sqlRecords($squery, "one", "select");
                                        if(!$productData){
                                            $squery = "SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE  `option_sku`='".$id."' AND `merchant_id`='".MERCHANT_ID."'";
                                        $productData = Data::sqlRecords($squery, "one", "select");
                                        }
                                        else{
                                            $query = "SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE  `option_sku`='".$id."' AND `merchant_id`='".MERCHANT_ID."'";
                                            $sData = Data::sqlRecords($query, "one", "select");
                                        }
                                        if(isset($productData['option_id'])){
                                            $feed_array['option_id'][$productData['option_id']]=true;
                                            $option_id = $productData['option_id'];
                                            $id = $productData['product_id'];
                                        }
                                        else{
                                            if(isset($sData) && $sData){
                                                $option_id = $sData['option_id'];
                                                $feed_array['option_id'][$sData['option_id']]=true;
                                            }
                                            $id = $productData['id'];
                                        }
                                        if($id){
                                            $feed_array[$id]=true;
                                        }
                                        $checkProduct = substr(trim($value['ErrorList']['ErrorDescription'][0]),0,5);
                                        if($checkProduct=='Error'){
                                            if(is_array($value['ErrorList']['ErrorDescription'])){
                                                unset($value['ErrorList']['ErrorDescription'][0]);
                                                foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                                    $content[]=$value1;
                                                }
                                            }
                                            $errorFromNewegg = implode(',', $content);
                                            $errorMsg = substr($errorFromNewegg, 0, 1000);
                                            $errorMsg =$errorMsg.'...';
                                            $status = Helper::getProdcutStatus($id);
                                            if($status==Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR || $status==Data::PRODUCT_STATUS_ACTIVATED){
                                                $query="update `newegg_product` set  error='".addslashes($errorMsg)."' where product_id='".$id."'";
                                                Data::sqlRecords($query,null,"update");
                                                $content = [];

                                            }
                                            else{
                                                $errorMsg = substr($errorFromNewegg, 0, 1000);
                                                $errorMsg =$errorMsg.'...';
                                                $query="update `newegg_product` set upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."' , error='".addslashes($errorMsg)."' where product_id='".$id."'";
                                                Data::sqlRecords($query,null,"update");
                                                if(isset($feed_array['option_id'])){
                                                    $query="update `newegg_product_variants` set upload_status='".Data::PRODUCT_STATUS_NOT_UPLOADED."'where option_id='".$option_id."'";
                                                Data::sqlRecords($query,null,"update");
                                                $content = [];
                                                }

                                            }
                                        }
                                        else{
                                            if(is_array($value['ErrorList']['ErrorDescription'])){
                                                unset($value['ErrorList']['ErrorDescription'][0]);
                                                foreach ($value['ErrorList']['ErrorDescription'] as $key1 => $value1) {
                                                    $content[]=$value1;
                                                }
                                            }
                                            $errorFromNewegg = implode(',', $content);
                                            $query="update `newegg_product` set upload_status='".Data::PRODUCT_STATUS_UPLOADED_WITH_ERROR."' , error='".addslashes($errorFromNewegg)."' where product_id='".$id."'";
                                            Data::sqlRecords($query,null,"update");
                                            $content = [];
                                            $count++;
                                        }
                                       
                                    }
                                    
                                }
                            }
                            else{
                                $query = "SELECT `product_ids` FROM `newegg_product_feed` WHERE feed_id='".$feed_id."' LIMIT 1";
                                $modelU = Data::sqlRecords($query, "one", "select");
                                $ids = explode(',',$modelU['product_ids']);
                                foreach ($ids as $id) {
                                    $query="update `newegg_product` set upload_status='".Data::PRODUCT_STATUS_ACTIVATED."',error=NULL where product_id='".$id."'";
                                    Data::sqlRecords($query,null,"update");
                                    $count++;
                                }
                            }
                            $query = "SELECT `product_ids` FROM `newegg_product_feed` WHERE feed_id='".$feed_id."' LIMIT 1";
                            $modelU = Data::sqlRecords($query, "one", "select");
                            $ids = explode(',',$modelU['product_ids']);
                            foreach ($ids as $id) {
                                if(!isset($feed_array[$id])){
                                    $squery= "SELECT `type` FROM `jet_product` WHERE id='".$id."'";
                                    $select = Data::sqlRecords($squery,'one',"select");
                                    if($select['type']=='simple'){
                                        $query="update `newegg_product` set upload_status='".Data::PRODUCT_STATUS_ACTIVATED."',error=NULL where product_id='".$id."'";
                                        Data::sqlRecords($query,null,"update");
                                    }
                                    else{
                                        $squery= "SELECT `option_id` FROM `newegg_product_variants` WHERE product_id='".$id."'";
                                        $variantSelect = Data::sqlRecords($squery,'all',"select");
                                        foreach ($variantSelect as $k => $val) {
                                           if(!isset($feed_array['option_id'][$val['option_id']])){
                                            $query="update `newegg_product_variants` set upload_status='".Data::PRODUCT_STATUS_ACTIVATED."' where option_id='".$val['option_id']."'";
                                            Data::sqlRecords($query,null,"update");
                                           }
                                        }
                                    }
                                $count++;

                                }
                            }
                            $query="update `newegg_product_feed` set status='".Data::FEED_STATUS_COMPLETED."' ,error=NULL where feed_id='".$feed_id."'";
                                    Data::sqlRecords($query,null,"update");
                        }
                    }
                    else{
                        Yii::$app->session->setFlash('error', "Something went wrong");
                    }

                } 
                catch (Exception $e) {
                    return $returnArr['error'] = $e->getMessage();
                }

            }else {
                Yii::$app->session->setFlash('success', "Selected feeds are already PROCESSED");
            }
        }
        if($count>0) {
            $returnArr['success']['count'] = $count;
        }else{
            return true;
        }
            return json_encode($returnArr);
    }

    public function actionViewfeed($id)
    {
        $feed_detail = Data::sqlRecords("SELECT * FROM `newegg_product_feed` WHERE id='" . $id . "'", 'one');
        if (!empty($feed_detail['feed_id'])) {
            $wal = new Neweggapi(SELLER_ID, AUTHORIZATION, SECRET_KEY);
            $feed_data = $wal->getRequest('/datafeedmgmt/feeds/result/'.$feed_detail['feed_id']);
        }
        return $this->render('viewfeed', ['feed_data' => json_decode($feed_data,true), 'feed_detail' => $feed_detail]);

    }

    /*show error on ptoduct grid*/
     public function actionErrornewegg()
    {
        $this->layout="main2";
        $id = trim(Yii::$app->request->post('id'));
        $merchant_id = Yii::$app->request->post('merchant_id');
        
        $errorData=array();
        $connection=Yii::$app->getDb();
        $errorData=$connection->createCommand('SELECT `error` from `newegg_product_feed` where merchant_id="'.$merchant_id.'" AND `id`="'.$id.' LIMIT 0, 1"')->queryOne();
        
        $html = $this->render('errors',array('data'=>$errorData),true);
        $connection->close();
        echo $html;
    }


    /**
     * Finds the WalmartProductFeed model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WalmartProductFeed the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartProductFeed::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
