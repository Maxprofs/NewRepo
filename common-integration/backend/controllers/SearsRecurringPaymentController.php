<?php

namespace backend\controllers;

use Yii;
use backend\models\SearsRecurringPayment;
use backend\models\SearsRecurringPaymentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use frontend\components\ShopifyClientHelper;

/**
 * SearsRecurringPaymentController implements the CRUD actions for SearsRecurringPayment model.
 */
class SearsRecurringPaymentController extends Controller
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
     * Lists all SearsRecurringPayment models.
     * @return mixed
     */
    public function actionIndex()
    {
    	if (\Yii::$app->user->isGuest) {
    		return $this->goHome();
    	}
        $searchModel = new SearsRecurringPaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SearsRecurringPayment model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SearsRecurringPayment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
     /*
    cancel recurring monthly payment 
    */
    public function actionCancelpayment(){
        //print_r($_POST);die;
        $connection = Yii::$app->getDb();
        
        $merchant_id=$_POST['merchant_id'];
        $payment_id=$_POST['id'];
        $planType=$_POST['type'];
         $selectShop = $connection->createCommand("SELECT `shop_url`, `token` FROM `sears_shop_details` WHERE `merchant_id` ='".$merchant_id."' ")->queryOne();
        $shop=$selectShop['shop_url'];
        $token=$selectShop['token'];
        
        $sc = new ShopifyClientHelper($shop, $token,SEARS_APP_KEY,SEARS_APP_SECRET);
                
        $response=[];
        if ($planType=='Recurring Plan (Monthly)'){
            $cancelresponse = $sc->call('DELETE','/admin/recurring_application_charges/'.$payment_id.'.json');
        }
        $response = $sc->call('GET','/admin/recurring_application_charges/'.$payment_id.'.json');

        if ($response['status'] == "cancelled") {
            $message = "Recurring charge has been cancelled ! Thank you";
        }
        else{
            $message = "Recurring charge is not cancelled";
        }
        return $message;
    }
     public function actionViewpayment(){
        $this->layout="main2";
        $payment_id=$_POST['id'];
        $merchant_id=$_POST['merchant_id'];
        $planType=$_POST['type'];
        
        $connection=Yii::$app->getDb();
          
        $selectShop = $connection->createCommand("SELECT `shop_url`, `token` FROM `sears_shop_details` WHERE `merchant_id` ='".$merchant_id."' ")->queryOne();
        $shop=$selectShop['shop_url'];
        $token=$selectShop['token'];
        
        $sc = new ShopifyClientHelper($shop, $token,SEARS_APP_KEY,SEARS_APP_SECRET);
                
        $response=array();
        if ($planType=='1 Year Subscription Plan' || $planType=='6 Months Subscription Plan'){
            $response=$sc->call('GET','/admin/application_charges/'.$payment_id.'.json');
        }else{
            $response=$sc->call('GET','/admin/recurring_application_charges/'.$payment_id.'.json');
        }

        if($response && !isset($response['errors']))
        {
            $html=$this->render('viewpayment',array('data'=>$response),true);
        }
        return $html;
         
    }
    public function actionCreate()
    {
        $model = new SearsRecurringPayment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SearsRecurringPayment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SearsRecurringPayment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SearsRecurringPayment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SearsRecurringPayment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SearsRecurringPayment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
