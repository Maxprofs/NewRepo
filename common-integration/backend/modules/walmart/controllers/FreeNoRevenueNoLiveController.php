<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\models\WalmartExtensionDetail;
use backend\modules\walmart\models\WalmartExtensionDetailSearch;
use backend\modules\walmart\components\Data;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use frontend\modules\walmart\components\Mail;
use backend\modules\walmart\models\CustomQuerySearch;



/**
 * DetailsController implements the CRUD actions for WalmartExtensionDetail model.
 */
class FreeNoRevenueNoLiveController extends BaseController
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
                    'get-template' => ['POST'],
                    'send-mail' => ['POST'],
                ],

            ],
        ];
    }

    /**
     * Lists all WalmartExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        

        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        $sql = "SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` as `merchant_id`,`jed`.`status`,`jed`.`app_status`,`jed`.`expire_date` FROM `".Data::EXTENSIONS_TABLE."` AS `jed` LEFT JOIN `".Data::PRODUCT_TABLE."` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='PUBLISHED' LEFT JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ";
        
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $chart = $sql1->queryAll();
        $searchModel = new CustomQuerySearch();
        $searchModel->setCustomQuery($sql);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'chart' => $chart,
            'search' => $searchModel,
        ]);
    }


    

}
