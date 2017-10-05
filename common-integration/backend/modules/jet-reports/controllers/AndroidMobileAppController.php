<?php

namespace backend\modules\reports\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use backend\modules\reports\components\Data;
use backend\modules\reports\models\CustomQuerySearch;

/**
 * PaidNoRevenueNoLiveController
 */
class AndroidMobileAppController extends BaseController
{

    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        

        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        $table = Data::App_Table;
        $check = 'check';
        $sql = "SELECT * FROM {$table} where `android_reg_id` IS NOT NULL";
        $searchModel = new CustomQuerySearch();
        $searchModel->setCustomQuery($sql);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('/ios-mobile-app/index',['dataProvider' => $dataProvider, 'searchModel' => $searchModel,'check'=>$check]);
        /*
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
        ]);*/
    }



}
