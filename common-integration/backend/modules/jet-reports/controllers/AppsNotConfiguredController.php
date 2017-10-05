<?php

namespace backend\modules\reports\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\SqlDataProvider;
use backend\modules\reports\models\CustomQuerySearch;
use backend\modules\reports\components\Data;


/**
 * Apps Not Configured Controller.
 */
class AppsNotConfiguredController extends BaseController
{


    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {

        $table = Data::EXTENSIONS_TABLE;
        $table1 = Data::CONFIGURATION_TABLE;
        $sql = "SELECT `jed`.`merchant_id`,`jed`.`email`,`jed`.`status`,`jed`.`app_status`,`jed`.`expire_date`,`jed`.`shopurl` FROM `{$table}` `jed` LEFT JOIN `{$table1}` `jc` ON `jed`.`merchant_id` = `jc`.`merchant_id` WHERE `jed`.`app_status`='install' AND `jc`.`merchant_id` IS NULL ";
        
        $searchModel = new CustomQuerySearch();
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

}
