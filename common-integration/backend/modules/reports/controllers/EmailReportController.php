<?php

namespace backend\modules\reports\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use backend\modules\reports\models\EmailReportSearch;
use yii\widgets\DetailView;
use backend\modules\reports\components\Data;




/**
 * Email report Controller
 */
class EmailReportController extends BaseController
{
    public function actionIndex()
    {
        $table = Data::EMAIL_REPORT;
    	$sql = "SELECT * FROM `{$table}` ORDER BY tracking_id DESC";
        $searchModel = new EmailReportSearch();
        $searchModel->setCustomQuery($sql);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index',['dataProvider' => $dataProvider, 'searchModel' => $searchModel]);

        /*
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $totalCount,
            'sort' =>false,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
        */
       
    }
    public function actionMass()
        {
            $table = Data::EMAIL_REPORT;
            $post = Yii::$app->getRequest()->post();
            if(isset($post['selection']) && count($post['selection'])){
                foreach ($post['selection'] as $key => $value) {
                    $connection=Yii::$app->getDb();
                    $query = "DELETE FROM `{$table}` where tracking_id='".$value."'";
                    $connection->createCommand($query)->execute();
                    
                }
                Yii::$app->session->setFlash('success', "Selected Data deleted Successfully");
                return $this->redirect(['index']);
            }
            else
            {
                return $this->redirect(['index']);
            }
            
            
        }

    /*action view for email report*/    
    public function actionView($id)
        {
           $file_dir = dirname(\Yii::getAlias('@webroot')).'/var/email/'.$id.'.html';
           if (!file_exists($file_dir)){
                echo "No content found";
           }
           else{
            $data = file_get_contents($file_dir);
            echo $data;
           }
              
           
        }

 
    

    
}
