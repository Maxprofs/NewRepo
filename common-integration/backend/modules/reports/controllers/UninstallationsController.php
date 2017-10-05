<?php

namespace backend\modules\reports\controllers;

use Yii;
use backend\modules\reports\models\JetShopDetailsSearch;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use backend\modules\reports\components\Data;


/**
 * Uninstallations Controller.
 */
class UninstallationsController extends BaseController
{
   
    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        if($type=='daily')
        {
            $sql = 'SELECT DATE(uninstall_date) as formatted_date,DATE_FORMAT(uninstall_date,"%y-%m-%d 00:00:00") from_date,DATE_FORMAT(uninstall_date,"%y-%m-%d 23:59:59") to_date,count(*) as uninstallations FROM '.Data::EXTENSIONS_TABLE.' WHERE install_status="0" AND `uninstall_date` GROUP BY DATE(`uninstall_date`) ORDER BY uninstall_date DESC ';
        }
        elseif($type=='monthly')
        {
            $sql = 'SELECT DATE_FORMAT(uninstall_date,"%m-%y") as formatted_date,DATE_FORMAT(uninstall_date,"%m-%y") as uninstall_date1,YEAR(uninstall_date) as year,MONTH(uninstall_date) as month,DATE_FORMAT(uninstall_date,"%y-%m-01 00:00:00") from_date,DATE_FORMAT(uninstall_date,"%y-%m-31 23:59:59") to_date,count(*) as uninstallations FROM '.Data::EXTENSIONS_TABLE.' WHERE install_status="0" AND `uninstall_date` GROUP BY `uninstall_date1` ORDER BY uninstall_date DESC ';
        }
        elseif($type=='yearly')
        {
            $sql = 'SELECT DATE_FORMAT(uninstall_date,"Year %Y") as formatted_date,YEAR(uninstall_date) as year,count(*) as uninstallations,DATE_FORMAT(uninstall_date,"%y-01-31 00:00:00") from_date,DATE_FORMAT(uninstall_date,"%y-12-31 23:59:59") to_date FROM '.Data::EXTENSIONS_TABLE.' WHERE install_status="0" AND `uninstall_date` GROUP BY `year` ORDER BY uninstall_date DESC ';
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
     * Displays merchant list according to selection.
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
        $searchModel = new JetShopDetailsSearch();
        $searchModel->setCustomWhere("`uninstall_date` BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:59' ");
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);        
        return $this->render('view',['model' => $dataProvider, 'searchModel' => $searchModel]);
    }   
}