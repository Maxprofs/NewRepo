<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\components\Data;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use frontend\modules\walmart\components\Mail;
use backend\modules\walmart\models\CustomQuerySearch;



/**
 * FreeAppsWithRevenue Controller
 */
class FreeAppsWithNoRevenueController extends BaseController
{
 

    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        

        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        $sql = "SELECT count( `jed`.`merchant_id` ) AS `complete_orders`, `jed`.`merchant_id`,`sd`.`email`,`jed`.`status`,`jed`.`app_status`,`jed`.`expire_date`,`sd`.`shop_url` as `shopurl` FROM `".Data::EXTENSIONS_TABLE."` `jed` JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  LEFT JOIN `".Data::SHOP_DETAILS."` `sd` ON `jed`.`merchant_id` = `sd`.`merchant_id` WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING complete_orders <0 ";

        
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $chart = $sql1->queryAll();
        $searchModel = new CustomQuerySearch();
        $searchModel->andFilterWhere(['gt','complete_orders',0]);
        $searchModel->setCustomQuery($sql);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index',['dataProvider' => $dataProvider, 'searchModel' => $searchModel ,'chart'=>$chart]);
    }

}
