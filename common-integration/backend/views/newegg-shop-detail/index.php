<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\JetProduct;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\NeweggShopDetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Newegg Shop Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<head>
    <style type="text/css">
        .container{
            margin-left: 0;
        }
        .table > tbody > tr.review > td, .table > tbody > tr.review > th, .table > tbody > tr > td.review, .table > tbody > tr > th.review, .table > tfoot > tr.review > td, .table > tfoot > tr.review > th, .table > tfoot > tr > td.review, .table > tfoot > tr > th.review, .table > thead > tr.review > td, .table > thead > tr.review > th, .table > thead > tr > td.review, .table > thead > tr > th.review {
            background-color: #ffffdc;
        }

        .table > tbody > tr.error > td, .table > tbody > tr.error > th, .table > tbody > tr > td.error, .table > tbody > tr > th.error, .table > tfoot > tr.error > td, .table > tfoot > tr.error > th, .table > tfoot > tr > td.error, .table > tfoot > tr > th.error, .table > thead > tr.error > td, .table > thead > tr.error > th, .table > thead > tr > td.error, .table > thead > tr > th.error {
            background-color: #FFB9BB;
        }
    </style>
</head>
<div class="newegg-shop-detail-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
         'rowOptions'=>function ($model){
            if ($model->purchase_status=='uninstall'){
                return ['class'=>'error'];
            }elseif (($model->purchase_status=='License Expired')||($model->purchase_status=='Trial Expired')){
                return ['class'=>'danger'];
            }elseif ($model->purchase_status=='Purchased'){
                return ['class'=>'success'];
            }elseif ($model->purchase_status=='Not Purchase'){
                return ['class'=>'review'];
            }

        },
        'columns' => [
            /*['class' => 'yii\grid\SerialColumn'],*/

            /*'id',*/
            'merchant_id',
            'shop_url:ntext',
            'shop_name',
            [
                'label'=>'ACTIVATED',
                'value' => function($data){
                    $connection = Yii::$app->getDb();
                    $sql = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$data->merchant_id." AND `newegg_product`.`upload_status`='ACTIVATED') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$data->merchant_id." AND `newegg_product_variants`.`upload_status`='ACTIVATED')) as `merged_data`";
                    $result = $connection->createCommand($sql)->queryOne();
                    //$result = JetProduct::findBySql($sql)->all();
                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'SUBMITTED',
                'value' => function($data){
                    $connection = Yii::$app->getDb();
                    $sql = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$data->merchant_id." AND `newegg_product`.`upload_status`='SUBMITTED' ) UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$data->merchant_id." AND `newegg_product_variants`.`upload_status`='SUBMITTED')) as `merged_data`";
                    $result = $connection->createCommand($sql)->queryOne();
                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'DEACTIVATED',
                'value' => function($data){
                    $connection = Yii::$app->getDb();

                    $sql = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$data->merchant_id." AND `newegg_product`.`upload_status`='DEACTIVATED') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$data->merchant_id." AND `newegg_product_variants`.`upload_status`='DEACTIVATED')) as `merged_data`";
                    $result = $connection->createCommand($sql)->queryOne();
                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'UPLOAD WITH ERROR',
                'value' => function($data){
                    $connection = Yii::$app->getDb();

                    $sql = "SELECT count(*) as `count` FROM ((SELECT `sku` FROM `newegg_product` INNER JOIN `jet_product` ON `newegg_product`.`product_id`=`jet_product`.`id` WHERE `newegg_product`.`merchant_id`=".$data->merchant_id." AND `newegg_product`.`upload_status`='UPLOADED WITH ERROR') UNION (SELECT `option_sku` AS `sku` FROM `newegg_product_variants` INNER JOIN `jet_product_variants` ON `newegg_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `newegg_product_variants`.`merchant_id`=".$data->merchant_id." AND `newegg_product_variants`.`upload_status`='UPLOADED WITH ERROR')) as `merged_data`";
                    $result = $connection->createCommand($sql)->queryOne();
                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'TOTAL PRODUCTS',
                'value' => function($data){
                    $connection = Yii::$app->getDb();
                    $sql = "select count(*) as `count` from (SELECT * from `newegg_product` where `merchant_id`=".$data->merchant_id." ) as `neweggproduct` LEFT JOIN (select * from `newegg_product_variants` where `merchant_id`=".$data->merchant_id." ) as `neweggvariantprod` ON `neweggproduct`.`product_id` = `neweggvariantprod`.`product_id` LIMIT 0,1";
                    $result = $connection->createCommand($sql)->queryOne();
                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'Orders',
                'value' => function($data){
                    $sql = 'SELECT `order_total` FROM `newegg_order_detail` WHERE `order_status_description` = "SHIPPED" AND `merchant_id`='.$data->merchant_id;
                    $result = JetProduct::findBySql($sql)->all();
                    return count($result);
                },
                'format'=>'raw',
            ],
            [
                'label'=>'Revenue',
                'value' => function($data){
                    $result1 = Yii::$app->getDb()->createCommand("SELECT `order_total` FROM `newegg_order_detail` WHERE `order_status_description` = 'SHIPPED' AND `merchant_id`=$data->merchant_id")->queryAll();
                    $total=0;
                    foreach ($result1 as $val)
                    {

                        $total=$total+$val['order_total'];

                    }

                    return (float)$total;
                },
                'format'=>'raw',
            ],

            [
                'label'=>'Config Set',
                'value' => function($data){
                    $isSet = Yii::$app->getDb()->createCommand("SELECT `seller_id` FROM  `newegg_configuration` where `merchant_id`='".$data->merchant_id."'")->queryOne();
                    if ($isSet){
                        return "Yes";
                    }else{
                        return "No";
                    }
                },
                'format'=>'raw',
            ],
            'email:email',
             'token:ntext',
             'country_code',
             'currency',
             'install_status',
             'install_date',
             'expire_date',
             'purchase_date',
             'purchase_status',
             //'client_data:ntext',
             'uninstall_date',
             'app_status',

            /*['class' => 'yii\grid\ActionColumn'],*/
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {link}',
                'buttons' => [
                    'link' => function ($url,$model,$key) {
                        return '<a data-pjax="0" href="'.Yii::getAlias('@webneweggurl').'/site/managerlogin?ext='.$model['merchant_id'].'&&enter=true" target="_blank">Login as</a>';
                    },
                    'view' => function ($url,$model)
                    {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',  ['/newegg-shop-detail/view','id'=>$model->id]

                        );
                    },
                    'update' => function ($url,$model)
                    {
                        return Html::a(
                            '<span class="glyphicon glyphicon-plus-sign"> </span>',['/newegg-shop-detail/update','id'=>$model->id]
                        );
                    },


                ],
            ],
        ],
    ]); ?>

</div>
