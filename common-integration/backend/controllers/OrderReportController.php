<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use backend\components\Data;

class OrderReportController extends Controller
{
    public function beforeAction($action)
    {
        Yii::$app->controller->enableCsrfValidation = false;
        return true;
    }
    public function actionIndex()
    {
        //get records of failed orders created by today
        $countFailedOrders=[];
        $param="";
        $value="";
        $isDate=false;
        if(Yii::$app->request->post('param'))
        {
            $param = Yii::$app->request->post('param');
            if(Yii::$app->request->post('param')=="duration")
            {
                $value=Yii::$app->request->post('value');
                $jet_query='SELECT count(*) as `count` FROM `jet_order_import_error` WHERE `created_at`> DATE_SUB(NOW(), INTERVAL '.$value.')';
                $walmart_query='SELECT count(*) as `count` FROM `walmart_order_import_error` WHERE `created_at`> DATE_SUB(NOW(), INTERVAL '.$value.')';
                $newegg_query='SELECT count(*) as `count` FROM `newegg_order_import_error` WHERE `created_at`> DATE_SUB(NOW(), INTERVAL '.$value.')';
            }
            elseif(Yii::$app->request->post('param')=="date")
            {
                $isDate=true;
                $value=date('Y-m-d',strtotime(Yii::$app->request->post('value')));
            }
        }     
        else
        {
            $isDate=true;
            $value=date('Y-m-d');
        }
        if($isDate)
        {
            $jet_query='SELECT count(*) as `count` FROM `jet_order_import_error` WHERE DATE(`created_at`) = "'.$value.'"';
            $walmart_query='SELECT count(*) as `count` FROM `walmart_order_import_error` WHERE DATE(`created_at`) = "'.$value.'"';
            $newegg_query='SELECT count(*) as `count` FROM `newegg_order_import_error` WHERE DATE(`created_at`) = "'.$value.'"';
        }    
        $countFailedOrders['jet'] = Data::sqlRecords($jet_query,'one');
        $countFailedOrders['walmart'] = Data::sqlRecords($walmart_query,'one');
        $countFailedOrders['newegg'] = Data::sqlRecords($newegg_query,'one');
        if(!$param){
            $param='date';
            $value=date('Y-m-d');
        }
        if(Yii::$app->request->post('isAjax'))
        {
            $response = ['data'=>$countFailedOrders,'param'=>$param,'value'=>$value];
            return json_encode($response);
        }else{
            return $this->render('index',['data'=>$countFailedOrders,'param'=>$param,'value'=>$value]);    
        }
        
    }

    public function actionView()
    {
        $data = Yii::$app->request->get();
        $countFailedOrders='';
        if($data && isset($data['marketplace'],$data['param']))
        {
            if($data['param']=="date")
            {   
                $value=date('Y-m-d',strtotime($data['value']));
                $jet_query='SELECT id,merchant_id,merchant_order_id as order_id,reason,created_at,"jet" as marketplace FROM `jet_order_import_error` WHERE DATE(`created_at`) = "'.$value.'"';
                $walmart_query='SELECT id,merchant_id,purchase_order_id as order_id,reason,created_at,"walmart" as marketplace FROM `walmart_order_import_error` WHERE DATE(`created_at`) = "'.$value.'"';
                $newegg_query='SELECT id,merchant_id,order_number as order_id,error_reason as reason,created_at,"newegg" as marketplace FROM `newegg_order_import_error` WHERE DATE(`created_at`) = "'.$value.'"';
            }
            elseif($data['param']=="duration")
            {
                $value=$data['value'];
                $jet_query='SELECT id,merchant_id,merchant_order_id as order_id,reason,created_at,"jet" as marketplace FROM `jet_order_import_error` WHERE `created_at`> DATE_SUB(NOW(), INTERVAL '.$value.')';
                $walmart_query='SELECT id,merchant_id,purchase_order_id as order_id,reason,created_at,"walmart" as marketplace FROM `walmart_order_import_error` WHERE `created_at`> DATE_SUB(NOW(), INTERVAL '.$value.')';
                $newegg_query='SELECT id,merchant_id,order_number as order_id,error_reason as reason,created_at,"newegg" as marketplace FROM `newegg_order_import_error` WHERE `created_at`> DATE_SUB(NOW(), INTERVAL '.$value.')';
            }
            if($data['marketplace']=="jet")
                $countFailedOrders = Data::sqlRecords($jet_query,'all');
            elseif($data['marketplace']=="walmart")
                $countFailedOrders = Data::sqlRecords($walmart_query,'all');
            else
                $countFailedOrders = Data::sqlRecords($newegg_query,'all');   
        }
        if($countFailedOrders)
        {
            $dataProvider = new ArrayDataProvider([
                'allModels' => $countFailedOrders,
                'sort' => [
                    'attributes' => ['id', 'merchant_id','created_at','order_id'],
                ],
                'pagination' => [
                    'pageSize' => 30,
                ],
                'key' => 'id',
            ]);
            //var_dump($dataProvider->getModels());die;
            return $this->render('view', [
                'dataProvider' => $dataProvider,
                'marketplace'=>$data['marketplace']
            ]);
        }
    }
}    