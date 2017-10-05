<?php

namespace backend\modules\reports\controllers;

use Yii;
use backend\modules\reports\models\Issues;
use backend\modules\reports\models\IssuesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use frontend\components\Mail;
use yii\filters\VerbFilter;
use backend\modules\reports\components\Data;


/**
 * IssuesController implements the CRUD actions for Issues model.
 */
class IssuesController extends BaseController
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
     * Lists all Issues models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new IssuesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Issues model.
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
     * Creates a new Issues model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if($_POST){
            $model = new Issues();
            if(isset($_POST['Issues']['new-issues']) && !empty($_POST['Issues']['new-issues'])){
                $_POST['Issues']['issue_type']=$_POST['Issues']['new-issues'];
                $_POST['Issues']['issue_status']='pending';
                if(isset($_POST['Issues']['new_employee_email']) && !empty($_POST['Issues']['new_employee_email'])){
                    $_POST['Issues']['employee_email']=$_POST['Issues']['new_employee_email'];
                }
                $model->load($_POST);
                $model->save();
                $data['reciever'] = $_POST['Issues']['employee_email'];
                $data['sender'] = 'shopify@cedcommerce.com';
                $data['subject'] = 'Issues Assigned To You';
                $data['Description']=$_POST['Issues']['issue_description'];
                $template = 'email/Issues.html';
                $mailer = new Mail($data,$template,'php',true);
                $mailer->sendMail();
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else{
                $_POST['Issues']['issue_status']='pending';
                if(isset($_POST['Issues']['new_employee_email']) && !empty($_POST['Issues']['new_employee_email'])){
                    $_POST['Issues']['employee_email']=$_POST['Issues']['new_employee_email'];
                }
                if ($model->load($_POST) && $model->save()) {
                    $data['reciever'] = $_POST['Issues']['employee_email'];
                    $data['sender'] = 'shopify@cedcommerce.com';
                    $data['subject'] = 'Issues Assigned To You';
                    $data['Description']=$_POST['Issues']['issue_description'];
                    $template = 'email/Issues.html';
                    $mailer = new Mail($data,$template,'php',true);
                    $mailer->sendMail();
                    return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        return $this->render('create', [
                            'model' => $model,
                        ]);
                }
            }
          
        }
        else{
            $model = new Issues();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        
    }

    /**
     * Updates an existing Issues model.
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
     * Deletes an existing Issues model.
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
     * Finds the Issues model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Issues the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Issues::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /*change issue status*/

    public function actionResolve($id){
        $tableName = Data::ISSUES;
        $model = $this->findModel($id);
        $today = date("y/m/d");
        $connection=Yii::$app->getDb();
        $query = "UPDATE `{$tableName}` SET `resolve_date`='" . $today . "',`issue_status`='solved' where `id`='".$id."'";
        $updateMailStatus = $connection->createCommand($query)->execute();
          return $this->redirect('index',$id);
    }
}
