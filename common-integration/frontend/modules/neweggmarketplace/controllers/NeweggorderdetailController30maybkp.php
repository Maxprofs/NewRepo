<?php

namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\order\Orderdetails;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;
use frontend\modules\neweggmarketplace\models\NeweggCourtesyrefundDetailSearch;
use Yii;
use frontend\modules\neweggmarketplace\models\NeweggOrderDetail;
use frontend\modules\neweggmarketplace\models\NeweggOrderDetailSearch;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


/**
 * NeweggorderdetailController implements the CRUD actions for NeweggOrderDetail model.
 */
class NeweggorderdetailController extends NeweggMainController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all NeweggOrderDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {

            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        } else {

            $searchModel = new NeweggOrderDetailSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }

    public function actionOrderdetails($cron = false)
    {
        if ($cron) {

            $query = "SELECT * FROM `newegg_configuration` INNER JOIN `newegg_shop_detail` WHERE newegg_configuration.merchant_id = newegg_shop_detail.merchant_id AND newegg_shop_detail.install_status = 1 AND newegg_shop_detail.purchase_status != 'Trail Expired' ";
            $config = Data::sqlRecords($query, null, 'all');

            foreach ($config as $value) {
                $status = 0;

                $data = Orderdetails::orderdetails($status, $value);
            }

        } else {

            if (Yii::$app->user->isGuest) {

                return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
            }

            $counterror = 0 ;
            $countsuccess = 0 ;
            $errormsg = '';

            $status = Yii::$app->request->post('action');
            $data = Orderdetails::orderdetails($status);

            foreach ($data as $value){
                if($value['status']=='success' && isset($value['updated']) ){
                    $countsuccess++;
                    $successmsg = ' Order(s) Successfully Updated';

                }else if($value['status']=='success' && isset($value['inserted'])){

                    $countsuccess++;
                    $successmsg = 'Order(s) Successfully Created';
                } else if ($value['status'] == 'error'){
                    $counterror++;
                    $errormsg = $value['error'];
                }
            }
            if($countsuccess > 0){
                Yii::$app->session->setFlash('success', $countsuccess.$successmsg);
            }
            if ($counterror > 0){
                Yii::$app->session->setFlash('error',$errormsg);
            }

            return $this->redirect(['index']);
        }

    }


    // create order in shopify
    public function actionSyncorder()
    {
        $data = Orderdetails::syncorders();

        return $this->redirect(['index']);

    }

    public function actionCancelorder()
    {
        $val = Yii::$app->request->get();
        $data = Orderdetails::cancelorder($val['order_number']);
        Yii::$app->session->setFlash($data['status'], $data['message']);
        return $this->redirect(['index']);
    }

    public function actionShiporder($id)
    {
        $data = Orderdetails::shiporder($id);

        if($data['status'] == 'error')
        {
            Yii::$app->session->setFlash($data['status'], 'Error : '.$data['message']);
        }else{
            Yii::$app->session->setFlash($data['status'], 'Success : '.$data['message']);
        }
        return $this->redirect(['index']);
    }

    public function actionCourtesyrefund()
    {
        $id = 5;
        $data = Orderdetails::courtesyrefund($id);
        Yii::$app->session->setFlash($data['status'], $data['message']);
        return $this->redirect(['getcourtesyrefund']);
    }

    public function actionGetcourtesyrefund()
    {
        if (Yii::$app->user->isGuest) {

            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        } else {

            $data = Orderdetails::getcourtesyrefund();

            $searchModel = new NeweggCourtesyrefundDetailSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('courtesyrefund', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }

    /**
     * Displays a single NeweggOrderDetail model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
//        $this->layout="main2";
        if (Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        $data = Orderdetails::viewOrder($id);

        return $this->render('vieworderdetail', [
            'model' => $data[0],
        ]);
    }

    /**
     * Deletes an existing NeweggOrderDetail model.
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
     * Finds the NeweggOrderDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NeweggOrderDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NeweggOrderDetail::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionCurlprocessfororder($data){

        if($data && isset($data['id']))
        {
            Orderdetails::curlprocessfororder($data);
        }else{
            return ;
        }
    }


}
