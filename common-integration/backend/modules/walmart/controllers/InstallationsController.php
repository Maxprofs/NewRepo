<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\models\WalmartExtensionDetailSearch;
use backend\modules\walmart\components\Data;
use yii\data\SqlDataProvider;




/**
 * DetailsController implements the CRUD actions for WalmartExtensionDetail model.
 */
class InstallationsController extends BaseController
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
            $sql = 'SELECT DATE(install_date) as formatted_date,DATE_FORMAT(install_date,"%y-%m-%d") from_date,DATE_FORMAT(install_date,"%y-%m-%d") to_date,count(*) as installations FROM '.Data::EXTENSIONS_TABLE.' GROUP BY DATE(`install_date`) ORDER BY install_date DESC ';
        }
        elseif($type=='monthly')
        {
            $sql = 'SELECT DATE_FORMAT(install_date,"%m-%y") as formatted_date,DATE_FORMAT(install_date,"%m-%y") as install_date1,YEAR(install_date) as year,MONTH(install_date) as month,DATE_FORMAT(install_date,"%y-%m-01") from_date,DATE_FORMAT(install_date,"%y-%m-31") to_date,count(*) as installations FROM '.Data::EXTENSIONS_TABLE.' GROUP BY `install_date1` ORDER BY install_date DESC ';
        }
        elseif($type=='yearly')
        {
            $sql = 'SELECT DATE_FORMAT(install_date,"Year %Y") as formatted_date,YEAR(install_date) as year,count(*) as installations,DATE_FORMAT(install_date,"%y-01-31") from_date,DATE_FORMAT(install_date,"%y-12-31") to_date FROM '.Data::EXTENSIONS_TABLE.' GROUP BY `year` ORDER BY install_date DESC ';
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
        
        //$model = WalmartExtensionDetail::find()->where("`install_date` BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:59' ");
        $search = new WalmartExtensionDetailSearch();
        $search->setCustomWhere("`install_date` BETWEEN '{$from} 00:00:00' AND '{$to} 23:59:59' ");
        $dataProvider = $search->search(Yii::$app->request->queryParams);

        return $this->render('view',['model' => $dataProvider,'search' =>$search]);
    }

 
}
