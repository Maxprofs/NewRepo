<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\components\Data;
use backend\modules\walmart\models\CustomQuerySearch;



/**
 * DetailsController implements the CRUD actions for WalmartExtensionDetail model.
 */
class AppsNotConfiguredController extends BaseController
{

    /**
     * Lists all WalmartExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        $sql = "SELECT `jed`.`id`, `jed`.`merchant_id`,`jed`.`status`,`jed`.`app_status`,`jed`.`expire_date` FROM `".Data::EXTENSIONS_TABLE."` `jed` LEFT JOIN `".Data::CONFIG_TABLE."` `jc` ON `jed`.`merchant_id` = `jc`.`merchant_id` WHERE `jed`.`app_status`='install' AND `jc`.`merchant_id` IS NULL ";
        
        $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$sql}) FINAL")->queryScalar();
        $sql1 = Yii::$app->db->createCommand($sql);
        $searchModel = new CustomQuerySearch();
        $searchModel->setCustomQuery($sql);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index',['dataProvider' => $dataProvider, 'search' => $searchModel]);
    }

    
}
