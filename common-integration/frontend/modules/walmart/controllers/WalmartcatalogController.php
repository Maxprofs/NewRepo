<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 2/9/17
 * Time: 3:11 PM
 */
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\filters\VerbFilter;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\models\WalmartProductSearch;

class WalmartcatalogController extends WalmartmainController
{
    protected $walmartHelper;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        if (parent::beforeAction($action)) {
            $this->walmartHelper = new Walmartapi(API_USER, API_PASSWORD);
            return true;
        }
    }

    /**
     * Lists all WalmartProduct models.
     * @return mixed
     */
    /*public function actionIndex()
    {
        $merchant_id = MERCHANT_ID;

        $searchModel = new WalmartProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($merchant_id == 484) {
            $dataProvider->pagination->pageSize = 100;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'merchant_id' => $merchant_id
        ]);
    }*/
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $inventory_data = Data::sqlRecords("SELECT * FROM (SELECT * FROM (SELECT * FROM (SELECT `product_id` ,`product_title` FROM `walmart_product` WHERE `merchant_id`= '".MERCHANT_ID."') as `wp` INNER JOIN (SELECT title,sku,type,id,qty FROM `jet_product` WHERE `merchant_id`='".MERCHANT_ID."' ) as `jp` ON `wp`.`product_id`=`jp`.`id`) as `walmart_main` ) as `walmart_table` LEFT JOIN (SELECT * FROM (SELECT * FROM (SELECT `option_id` as `walmart_option_id` FROM `walmart_product_variants` WHERE `merchant_id`= '".MERCHANT_ID."') as `wpv` INNER JOIN (SELECT `option_id`,`option_qty`,`option_sku`,`product_id` FROM `jet_product_variants` WHERE `merchant_id`='".MERCHANT_ID."' ) as `jpv` ON `wpv`.`walmart_option_id`=`jpv`.`option_id`) as `jet_main` ) as `jet_table` ON `walmart_table`.`product_id`=`jet_table`.`product_id`", 'all');
        echo '<pre>';print_r($inventory_data);die;

        

    }

}

