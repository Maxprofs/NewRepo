<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\models\WalmartEmailTemplate;
use backend\modules\walmart\models\WalmartEmailTemplateSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use backend\modules\walmart\components\Data;

/**
 * JetEmailTemplateController implements the CRUD actions for JetEmailTemplate model.
 */
class WalmartEmailTemplateController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                   
                    [
                        'actions' => ['create', 'update','view','htmltemplate','index','delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            
        ];
    }
    /**
     * Lists all JetEmailTemplate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WalmartEmailTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single JetEmailTemplate model.
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
     * Creates a new JetEmailTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        
        if($_POST){
            $html_content = $_POST['JetEmailTemplate']['html_content'];
            $file_dir = Yii::getAlias('@frontend').'/modules/walmart/views/templates/email';
          if (!file_exists($file_dir)){
              mkdir($file_dir,0775, true);
          }
          $filenameOrig="";
          $filenameOrig=$file_dir.'/'.$_POST['WalmartEmailTemplate']['template_title'].'.html';
          $fileOrig="";
          $fileOrig=fopen($filenameOrig,'w+');
          fwrite($fileOrig,$html_content);

          fclose($fileOrig);
            $model = new WalmartEmailTemplate();
            $data = $model->find()->all();
            foreach ($data as $key => $value) {
                if($value->template_title == $_POST['WalmartEmailTemplate']['template_title']){
                   $data = false;
                }
            }
            if($data){
                if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $connection=Yii::$app->getDb();
                $table = Data::EXTENSIONS_TABLE;
                $table1 = Data::WALMART_CONFIG_TABLE;
                $query="SELECT `merchant_id`  FROM `{$table}`";
                $emailtemp = $connection->createCommand($query)->queryAll();
                foreach ($emailtemp as $key => $value) {
                    $sqlmodel ="INSERT INTO `{$table1}` (`data`,`value`,`merchant_id`) VALUES ('email".'/'."".$_POST['WalmartEmailTemplate']['template_title']."','1','".$value['merchant_id']."')";
                    $connection->createCommand($sqlmodel)->execute();

                }
                    return $this->redirect(['view', 'id' => $model->id]);
                } 
                else {
                        return $this->render('create', [
                            'model' => $model,
                        ]);
                }
            }
            else{
                Yii::$app->session->setFlash('error', "Template Already Exist");
                return $this->render('create', [
                            'model' => $model,
                        ]);
            }
           
            
           
        }
        else{
            $model = new WalmartEmailTemplate();
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
     * Updates an existing JetEmailTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->updateTemplate($_POST['JetEmailTemplate']['html_content'],$_POST['WalmartEmailTemplate']['template_path']);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing JetEmailTemplate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $htmlName = $this->findModel($id)->template_title;
        $file_dir = Yii::getAlias('@frontend')."/modules/walmart/views/templates/email/".$htmlName.'.html';
        unlink($file_dir);
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the JetEmailTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JetEmailTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartEmailTemplate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

     /**
     * Ajax request for html content
     */
     public function actionHtmltemplate()
    {
        
       $name = $_POST['name'];
       if(strpos($name,'/')){
            echo file_get_contents(Yii::getAlias('@frontend')."/modules/walmart/views/templates/".$name);
       }
       else{
            echo file_get_contents(Yii::getAlias('@frontend')."/modules/walmart/views/templates/email/".$name.'.html');
       }
       
       
    }
}
