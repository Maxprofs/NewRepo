<?php

namespace backend\modules\walmart\controllers;

use Yii;

use backend\modules\walmart\components\Data;
use backend\modules\walmart\models\CustomQuerySearch;



/**
 * DetailsController implements the CRUD actions for WalmartExtensionDetail model.
 */
class PaidNoRevenueNoLiveController extends BaseController
{
    

    /**
     * Lists all WalmartExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
        //$sql = "SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` , `jed`.`app_status` , `jed`.`status` ,`jed`.`uninstall_date` FROM `".Data::EXTENSIONS_TABLE."` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Purchased' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ";
        /*BY sanjeev*///$sql = "SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` , `jed`.`app_status` , `jed`.`status` ,`jed`.`uninstall_date` FROM (Select * from `".Data::EXTENSIONS_TABLE."` where `".Data::EXTENSIONS_TABLE."`.`status` = 'Purchased') AS `jed` LEFT JOIN (Select * from `".Data::MAIN_PRODUCT_TABLE."` where `".Data::MAIN_PRODUCT_TABLE."`.`status`='Available for Purchase') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select * from `".Data::ORDER_TABLE."` where `".Data::ORDER_TABLE."`.`status` = 'complete') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1";
        $sql = "SELECT `jed`.`merchant_id` , `jed`.`app_status` , `jed`.`status` ,`jed`.`uninstall_date` FROM (Select * from `walmart_extension_detail` where `walmart_extension_detail`.`status` = 'Purchased') AS `jed` LEFT JOIN (Select * from `walmart_product` where `walmart_product`.`status`='PUBLISHED') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select * from `walmart_order_details` where `walmart_order_details`.`status` = 'completed') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id`";

        print_r($sql);die;
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
