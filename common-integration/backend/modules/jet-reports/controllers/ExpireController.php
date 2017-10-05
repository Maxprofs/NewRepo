<?php

namespace backend\modules\reports\controllers;

use Yii;
use backend\modules\reports\models\JetExtensionDetail;
use backend\modules\reports\models\JetExtensionDetailSearch;
use backend\modules\reports\components\Data;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use frontend\modules\walmart\components\Mail;



/**
 * Expire Controller.
 */
class ExpireController extends BaseController
{

    /**
     * Lists all WalmartExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {

        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        if($type=='daily')
        {
            $sql = 'SELECT DATE(expire_date) as formatted_date,DATE_FORMAT(expire_date,"%y-%m-%d") from_date,DATE_FORMAT(expire_date,"%y-%m-%d") to_date,count(*) as expiring FROM '.Data::EXTENSIONS_TABLE.' WHERE `app_status`="install" GROUP BY DATE(`expire_date`) ORDER BY expire_date DESC ';
        }
        elseif($type=='monthly')
        {
            $sql = 'SELECT DATE_FORMAT(expire_date,"%m-%y") as formatted_date,DATE_FORMAT(expire_date,"%m-%y") as expire_date1,YEAR(expire_date) as year,MONTH(expire_date) as month,DATE_FORMAT(expire_date,"%y-%m-01") from_date,DATE_FORMAT(expire_date,"%y-%m-31") to_date,count(*) as expiring FROM '.Data::EXTENSIONS_TABLE.' WHERE `app_status`="install" GROUP BY `expire_date1` ORDER BY expire_date DESC ';
        }
        elseif($type=='yearly')
        {
            $sql = 'SELECT DATE_FORMAT(expire_date,"Year %Y") as formatted_date,YEAR(expire_date) as year,count(*) as expiring,DATE_FORMAT(expire_date,"%y-01-31") from_date,DATE_FORMAT(expire_date,"%y-12-31") to_date FROM '.Data::EXTENSIONS_TABLE.' WHERE `app_status`="install" GROUP BY `year` ORDER BY expire_date DESC ';
        }
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $chart = $sql1->queryAll();
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
            'chart' => $chart,
        ]);
    }

    /**
     * Displays a detail of row.
     * @return mixed
     */
    public function actionView()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }
        $from = Yii::$app->getRequest()->getQueryParam('from');
        $to = Yii::$app->getRequest()->getQueryParam('to');
        $connection=Yii::$app->getDb();

        $searchModel = new JetExtensionDetailSearch();
        $searchModel->setCustomWhere("`expire_date` BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:59' AND `app_status`='install'");
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('view',['model' => $dataProvider, 'searchModel' => $searchModel]);
    }

    
}
