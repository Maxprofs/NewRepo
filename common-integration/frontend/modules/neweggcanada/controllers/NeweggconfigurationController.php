<?php

namespace frontend\modules\neweggcanada\controllers;

use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\Neweggappdetail;
use Yii;
use frontend\modules\neweggcanada\models\NeweggConfiguration;
use frontend\modules\neweggcanada\models\NeweggConfigurationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * NeweggconfigurationController implements the CRUD actions for NeweggConfiguration model.
 */
class NeweggconfigurationController extends NeweggMainController
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
     * Lists all NeweggConfiguration models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataConfig = $clientData = array();
        $data = Data::sqlRecords("SELECT * FROM `newegg_can_configuration` WHERE `merchant_id`='" . MERCHANT_ID . "'", 'one');

        if ($postData = Yii::$app->request->post()) {

            $query = "SELECT * FROM `newegg_can_email_template`";
            $email = Data::sqlRecords($query, "all");

            foreach ($email as $key => $value) {
                $emailConfiguration['email/' . $value['template_title']] = isset($_POST['email/' . $value["template_title"]]) ? 1 : 0;
            }

            $seller_id = trim($_POST['seller_id']);
            $secret_key = trim($_POST['secret_key']);
            $authorization = trim($_POST['authorization']);

            if (!Neweggappdetail::validateApiCredentials($seller_id, $secret_key, $authorization)) {
                Yii::$app->session->setFlash('error', "Api credentials are invalid. Please enter valid api credentials");
                return $this->render('index', ['data' => $data, 'clientData' => $postData]);
            } else {
                $isConfigurationExist = Data::sqlRecords("SELECT `seller_id` FROM  newegg_can_configuration WHERE `merchant_id`='" . MERCHANT_ID . "' ", "one", "select");
                if (!empty($isConfigurationExist)) {
                    Data::sqlRecords("UPDATE `newegg_can_configuration` SET `seller_id`='" . $seller_id . "',`secret_key`='" . $secret_key . "',`authorization`='" . $authorization . "' where `merchant_id`='" . MERCHANT_ID . "'", null, "update");
                } else {
                    //save api credentials
                    Data::sqlRecords("INSERT INTO `newegg_can_configuration` (`merchant_id`, `seller_id`,`secret_key`,`authorization`) values(" . MERCHANT_ID . ",'" . $secret_key . "','" . $authorization . "','" . $authorization . "') ", null, "insert");
                }
            }

            if (!empty($emailConfiguration)) {
                foreach ($emailConfiguration as $key => $value) {
                    Data::sqlRecords("UPDATE `newegg_can_config` SET `value`='" . $value . "' where `merchant_id`='" . MERCHANT_ID . "' AND `data`='" . $key . "'", null, "update");
                }
            }

            $configFields = ['cancel_order', 'shopify_order_sync'];

            foreach ($postData as $key => $value) {
                if (in_array($key, $configFields)) {
                    Data::saveConfigValue(MERCHANT_ID, $key, $value);
                }
            }

            $clientData = $postData;
            Yii::$app->session->setFlash('success', 'Newegg Configurations has been Saved Successfully!');
        } else {
            $data = Data::sqlRecords("SELECT * FROM `newegg_can_configuration` WHERE `merchant_id`='" . MERCHANT_ID . "'", 'one');

            $dataConfig = Data::getneweggConfig(MERCHANT_ID);
            if (!empty($dataConfig)) {
                foreach ($dataConfig as $val) {
                    $clientData[$val['data']] = $val['value'];
                }
            }

        }

        return $this->render('index', [
            'data' => $data,
            'clientData' => $clientData,
        ]);
    }

    /**
     * Displays a single NeweggConfiguration model.
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
     * Creates a new NeweggConfiguration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new NeweggConfiguration();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing NeweggConfiguration model.
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
     * Deletes an existing NeweggConfiguration model.
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
     * Finds the NeweggConfiguration model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NeweggConfiguration the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NeweggConfiguration::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
