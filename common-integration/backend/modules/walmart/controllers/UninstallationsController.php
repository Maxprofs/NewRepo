<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\models\WalmartExtensionDetailSearch;
use backend\modules\walmart\components\Data;
use yii\data\SqlDataProvider;




/**
 * Uninstallations Controller.
 */
class UninstallationsController extends BaseController
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
            $sql = 'SELECT DATE(uninstall_date) as formatted_date,DATE_FORMAT(uninstall_date,"%y-%m-%d") from_date,DATE_FORMAT(uninstall_date,"%y-%m-%d") to_date,count(*) as uninstallations FROM '.Data::EXTENSIONS_TABLE.' WHERE app_status="uninstall" AND `uninstall_date` GROUP BY DATE(`uninstall_date`) ORDER BY uninstall_date DESC ';
        }
        elseif($type=='monthly')
        {
            $sql = 'SELECT DATE_FORMAT(uninstall_date,"%m-%y") as formatted_date,DATE_FORMAT(uninstall_date,"%m-%y") as uninstall_date1,YEAR(uninstall_date) as year,MONTH(uninstall_date) as month,DATE_FORMAT(uninstall_date,"%y-%m-01") from_date,DATE_FORMAT(uninstall_date,"%y-%m-31") to_date,count(*) as uninstallations FROM '.Data::EXTENSIONS_TABLE.' WHERE app_status="uninstall" AND `uninstall_date` GROUP BY `uninstall_date1` ORDER BY uninstall_date DESC ';
        }
        elseif($type=='yearly')
        {
            $sql = 'SELECT DATE_FORMAT(uninstall_date,"Year %Y") as formatted_date,YEAR(uninstall_date) as year,count(*) as uninstallations,DATE_FORMAT(uninstall_date,"%y-01-31") from_date,DATE_FORMAT(uninstall_date,"%y-12-31") to_date FROM '.Data::EXTENSIONS_TABLE.' WHERE app_status="uninstall" AND `uninstall_date` GROUP BY `year` ORDER BY uninstall_date DESC ';
        }
        //echo $sql;die();
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $chart = $sql1->queryAll();
        $dataProvider = "";
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
        
        $search = new WalmartExtensionDetailSearch();
        $search->setCustomWhere("`uninstall_date` BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:59'  and app_status='uninstall' ");
        $dataProvider = $search->search(Yii::$app->request->queryParams);
        return $this->render('view',['model' => $dataProvider,'search' =>$search]);
    }



}
