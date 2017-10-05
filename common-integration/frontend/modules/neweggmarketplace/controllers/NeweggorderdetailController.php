<?php

namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\components\Cronrequest;
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

    public function actionOrderdetails($config = false,$cron = false)
    {
        if ($cron) {

            $merchant_id = $config ? $config['merchant_id']:Yii::$app->user->identity->id;
            if (!$config && Yii::$app->user->isGuest) {
                return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
            }

            $status = 0;

            $data = Orderdetails::orderdetails($status, $config);

            /*$query = "SELECT * FROM `newegg_configuration` INNER JOIN `newegg_shop_detail` WHERE newegg_configuration.merchant_id = newegg_shop_detail.merchant_id AND newegg_shop_detail.install_status = 1 AND newegg_shop_detail.purchase_status != 'Trail Expired' ";
            $config = Data::sqlRecords($query, null, 'all');

            foreach ($config as $value) {
                $status = 0;

                $data = Orderdetails::orderdetails($status, $value);
            }*/
            return true;

        } else {

            if (Yii::$app->user->isGuest) {

                return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
            }

            $counterror = 0;
            $countsuccess = 0;
            $errormsg = '';

            /*$status = Yii::$app->request->post('action');
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $status = $_GET['status'];
            }*/
            $status = 0;

            $data = Orderdetails::orderdetails($status);

            foreach ($data as $value) {
                if ($value['status'] == 'success' && isset($value['updated'])) {
                    $countsuccess++;
                    $successmsg = $value['updated'] . ' Order(s) Successfully Updated';

                } else if ($value['status'] == 'success' && isset($value['inserted'])) {

                    $countsuccess++;
                    $successmsg = $value['inserted'] . 'Order(s) Successfully Fetched';
                } else if($value['status'] == 'error' && isset($value['message'])){
                    $counterror++;
                    $errormsg = $value['message'];

                }else if ($value['status'] == 'error' && !isset($value['error'])) {
                    $counterror++;
                    /*$message = json_decode($value['message'],true);
                    foreach ($message as $val){
                        if(is_array($val['reason'])){
                            $reason = implode(',',$val['reason']);
                            $errormsg .= $val['order_number'].$reason;
                        }
                    }*/
                    $errormsg = "There is error for some orders.Please <a href='https://shopify.cedcommerce.com/integration/neweggmarketplace/neweggorderimporterror/index'>click</a> to check failed order errors.";

                }else{
                    Yii::$app->session->setFlash('success','No Order found in Unshipped state in newegg.com.');
                }
            }
            if ($countsuccess > 0) {
                Yii::$app->session->setFlash('success', $countsuccess . $successmsg);
            }
            if ($counterror > 0) {
                Yii::$app->session->setFlash('error', $errormsg);
            }

            return $this->redirect(['index']);
        }

    }

    // create order in shopify
    public function actionSyncorder($config = false,$cron = false)
    {
        if ($cron) {

            $merchant_id = $config ? $config['merchant_id']:Yii::$app->user->identity->id;
            if (!$config && Yii::$app->user->isGuest) {
                return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
            }

            $status = 0;

            $data = Orderdetails::syncorders($config);

            return true;

        } else {
            $data = Orderdetails::syncorders();

            if ($data) {
                Yii::$app->session->setFlash($data['status'], $data['message']);
            }
        }

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

        if ($data['status'] == 'error') {
            Yii::$app->session->setFlash($data['status'], 'Error : ' . $data['message']);
        } else {
            Yii::$app->session->setFlash($data['status'], 'Success : ' . $data['message']);
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
        if (Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        $data = Orderdetails::viewOrder($id);

        return $this->render('vieworderdetail', [
            'data' => $data,
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

    public function actionCurlprocessfororder($data)
    {

        if ($data && isset($data['id'])) {
            Orderdetails::curlprocessfororder($data);
        } else {
            return;
        }
    }

    public function actionUpdatestatus()
    {

        $session = Yii::$app->session;
        $pages = 1;

        $order_data = Data::sqlRecords("SELECT `order_number` FROM `newegg_order_detail` WHERE `merchant_id`='" . MERCHANT_ID . "'", 'all');

        if (!empty($order_data)) {

            $session['order_data'] = array('order_number' => $order_data, 'pages' => $pages);

            return $this->render('updateorderstatus', [
                'totalcount' => count($order_data),
                'pages' => $pages
            ]);
        }else{

            Yii::$app->session->setFlash('success', 'No order(s) found');

            return $this->redirect(['index']);
        }

    }

    public function actionUpdateorderstatus()
    {
        $count = 0;
        $session = Yii::$app->session;
        $errorstatus = [];
        $returnArr = [];
        $order_data = $session['order_data'];

        foreach ($order_data['order_number'] as $data) {

            $url = '/ordermgmt/orderstatus/orders/' . $data['order_number'];
//            $url = '/ordermgmt/orderstatus/orders/' . 123456789;
            //$param = ['append' => '&version=304'];
            $response = Cronrequest::getRequest($url);
            /*var_dump($response);die('fdgdfg');

            $res = json_decode($response, true);*/

            $lastchar = substr($response, strlen($response) - 1);
            $firstchar = substr($response, 0);
            if ($firstchar[0] == '[') {
                $string = substr($response, 0);
            } else {
                $string = $response;
            }
            if ($lastchar == ']') {
                $string = substr($string, 0, -1);
            }
            $res = json_decode($string, true);

            if (!empty($res) && !empty($res['OrderStatusName'])) {
                $model = Data::sqlRecords('UPDATE `newegg_order_detail` SET order_status_description ="' . $res['OrderStatusName'] . '" WHERE order_number="' . $res['OrderNumber'] . '" AND merchant_id="' . MERCHANT_ID . '" ', null, 'update');
                $count++;
            }elseif(isset($res['Code'])){

                $errorstatus[] = $res['Message'];
            }else{
                $errorstatus[] = 'No data found or this order does not belong to this seller';
            }

        }
        if($count>0) {
            $returnArr['success']['count'] = $count;
        }
        if(!empty($errorstatus)){
            $returnArr['error']= $errorstatus;
        }
        return json_encode($returnArr);

    }


}
