<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\WalmartShopDetails;
use backend\models\WalmartExtensionDetail;
use backend\models\WalmartShopDetailsSearch;

/**
 * WalmartshopdetailsController implements the CRUD actions for WalmartShopDetails model.
 */
class WalmartshopdetailsController extends Controller
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
     * Lists all WalmartShopDetails models.
     * @return mixed
     */
    public function actionIndex()
    {
        if(Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $searchModel = new WalmartShopDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single WalmartShopDetails model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = WalmartShopDetails::find()->joinWith(['walmartExtensionDetail'])->where(['walmart_shop_details.merchant_id'=>$id])->One();
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing WalmartShopDetails model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = WalmartShopDetails::find()->joinWith(['walmartExtensionDetail'])->where(['walmart_shop_details.merchant_id'=>$id])->One();
        // print_r($model);die('dcfgiesw');
        if ($model->load(Yii::$app->request->post()) && $model->walmartExtensionDetail->load(Yii::$app->request->post())) 
        {

            if($model->save(false) && $model->walmartExtensionDetail->save(false)){
                Yii::$app->session->setFlash('success','Details is saved successfully');
            }
            else{
                Yii::$app->session->setFlash('error','Details is not saved successfully');
            }
            return $this->redirect(['view', 'id'=> $id]);
        }
        else {
            return $this->render('update', [
                'model' => $model,

        ]);
        }   
    }
    public function actionExport(){
      
        
        $selection=(array)Yii::$app->request->post('selection');
        if ($selection) {
          
        if (!file_exists(\Yii::getAlias('@webroot').'/var/email_csv-'.date('Y-m-d'))){
            mkdir(\Yii::getAlias('@webroot').'/var/email_csv-'.date('Y-m-d'),0775, true);
        }
        $base_path=\Yii::getAlias('@webroot').'/var/email_csv-'.date('Y-m-d').'/'.time().'.csv';
        $file = fopen($base_path,"a+");
        $headers = array('Merchant id','Shop Url','Email id','Purchase Status','Install Status','Expire Date');
        $row = array();
        $value=array();
        
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file,$row);
        
        $csvdata=array();
        $i=0;
        foreach ($selection as $val)
        {

            $client = WalmartShopDetails::find()->joinWith(['walmartExtensionDetail'])->where(['walmart_shop_details.merchant_id'=>$val])->all();
            foreach($client as $value)
            {
                if ($value['status'] == 1) {
                    $appstatus = "install";
                }
                else{
                     $appstatus = "uninstall";
                }

                $csvdata[$i]['Merchant id']=$value['merchant_id'];
                $csvdata[$i]['Shop Url']=$value['shop_url'];
                $csvdata[$i]['Email id']=$value['email'];
                $csvdata[$i]['Purchase Status']=$value['walmartExtensionDetail']['status'];
                $csvdata[$i]['Install Status']=$appstatus;
                $csvdata[$i]['Expire Date']=$value['walmartExtensionDetail']['expire_date'];
                $i++; 
            }
            

            
        }

        foreach($csvdata as $v)
        {
            
            $row = array();
            $row[] =$v['Merchant id'];
            $row[] =$v['Shop Url'];
            $row[] =$v['Email id'];
            $row[] =$v['Purchase Status'];
            $row[] =$v['Install Status'];
            $row[] =$v['Expire Date'];
        
            fputcsv($file,$row);
        }
        fclose($file);
        //$link=Yii::$app->request->baseUrl.'/var/product_csv-'.$merchant_id.'/products.csv';
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return  Yii::$app->response->sendFile($base_path);
       }
       else{

            Yii::$app->session->setFlash('error','Please select row for export csv');
            return $this->redirect('index');
       }
        
    }
 
    /**
     * Finds the WalmartShopDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WalmartShopDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartShopDetails::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
