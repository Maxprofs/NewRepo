<?php

use backend\models\WalmartExtensionDetail;
use backend\models\WalmartShopDetails;
use backend\models\WalmartShopDetailsSearch;
use common\models\JetProduct;
use dosamigos\datepicker\DatePicker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;




/* @var $this yii\web\View */
/* @var $searchModel backend\models\WalmartShopDetailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Walmart Shop Details';
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
<div class="walmart-shop-details-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?=Html::beginForm(['walmartshopdetails/export'],'post');?>
    <?=Html::submitButton('Export CSV', ['class' => ' pull-right btn btn-primary ',]);?>
    <div class="list-page" style="float:right">
        Show per page
        <select onchange="selectPage(this)" class="form-control" style="display: inline-block; width: auto; margin-top: 0px; margin-left: 5px; margin-right: 5px;" name="per-page">
            <option value="25" <?php if(isset($_GET['per-page']) && $_GET['per-page']==25){echo "selected=selected";}?>>25</option>
            <option <?php if(!isset($_GET['per-page'])){echo "selected=selected";}?> value="50">50</option>
            <option value="100" <?php if(isset($_GET['per-page']) && $_GET['per-page']==100){echo "selected=selected";}?> >100</option>
        </select>
        Items

    </div>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>

    <?= GridView::widget([
        'id'=>"jet_extention_details",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager' => [
            'class' => \liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [25,50,100],
            'pageSizeOptions' => ['class' => 'form-control','style' => 'display: none;width:auto;margin-top:0px;'],
            'maxButtonCount'=>5,
        ],
        'rowOptions'=>function ($model){
            if ($model->status=='uninstall'){
                return ['class'=>'error'];
            }elseif (($model->walmartExtensionDetail->status=='License Expired')||($model->walmartExtensionDetail->status=='Trial Expired')){
                return ['class'=>'danger'];
            }elseif ($model->walmartExtensionDetail->status=='Purchased'){
                return ['class'=>'success'];
            }elseif ($model->walmartExtensionDetail->status=='Not Purchase'){
                return ['class'=>'review'];
            }

        },
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function($data)
                {
                    return ['value' => $data['merchant_id']];
                },
            ],
            /* [
                 'attribute'=>'merchant_id',
                 'label'=>'Merchant Id',
                 'value'=>'walmartExtensionDetails.merchant_id',
             ],*/
            'merchant_id',
            /*[
                'attribute' => 'merchant_id',
                'label' => 'Merchant Id',
                'format' => 'html',
                'filter' => 'From : <input id="merchant_id" class="form-control" type="text" value="' . $searchModel->merchant_id . '" /><br/>' . 'To : <input class="form-control" type="text"  value="' . $searchModel->merchant_id . '"/>',
                'value' => 'merchant_id',
            ],*/
            'shop_url:url',
            'shop_name',
            'email:email',
            //'walmartExtensionDetail.install_date',
            [
                'label'=>'ITEM PROCESSING',
                'value' => function($data){
                    //$sql = 'SELECT status FROM walmart_product where status="PUBLISHED" AND merchant_id='.$data->merchant_id;
                    $sql = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=".$data->merchant_id." AND `walmart_product`.`status`='".WalmartShopDetails::PRODUCT_STATUS_PROCESSING."' AND `jet_product`.`type`='simple' AND `walmart_product`.`category` != '') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=".$data->merchant_id." AND `walmart_product_variants`.`status`='".WalmartShopDetails::PRODUCT_STATUS_PROCESSING."' AND `walmart_product`.`category` != '')) as `merged_data`";
//                    $result = JetProduct::findBySql($sql)->one();
                    $result = \frontend\components\Data::sqlRecords($sql, 'one');

                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'PUBLISHED',
                'value' => function($data){
                    //$sql = 'SELECT status FROM walmart_product where status="PUBLISHED" AND merchant_id='.$data->merchant_id;
                    $sql = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=".$data->merchant_id." AND `walmart_product`.`status`='".WalmartShopDetails::PRODUCT_STATUS_UPLOADED."' AND `jet_product`.`type`='simple' AND `walmart_product`.`category` != '') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=".$data->merchant_id." AND `walmart_product_variants`.`status`='".WalmartShopDetails::PRODUCT_STATUS_UPLOADED."' AND `walmart_product`.`category` != '')) as `merged_data`";
                    /*$result = JetProduct::findBySql($sql)->all();
                    return count($result);*/
                    $result = \frontend\components\Data::sqlRecords($sql, 'one');

                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'UNPUBLISHED',
                'value' => function($data){
                    //$sql = 'SELECT status FROM walmart_product where status="UNPUBLISHED" AND merchant_id='.$data->merchant_id;
                    $sql = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=".$data->merchant_id." AND `walmart_product`.`status`='".WalmartShopDetails::PRODUCT_STATUS_UNPUBLISHED."' AND `jet_product`.`type`='simple' AND `walmart_product`.`category` != '') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=".$data->merchant_id." AND `walmart_product_variants`.`status`='".WalmartShopDetails::PRODUCT_STATUS_UNPUBLISHED."' AND `walmart_product`.`category` != '')) as `merged_data`";

                    /*$result = JetProduct::findBySql($sql)->all();
                    return count($result);*/
                    $result = \frontend\components\Data::sqlRecords($sql, 'one');

                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'STAGE',
                'value' => function($data){
                    $sql = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=".$data->merchant_id." AND `walmart_product`.`status`='".WalmartShopDetails::PRODUCT_STATUS_STAGE."' AND `jet_product`.`type`='simple' AND `walmart_product`.`category` != '') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=".$data->merchant_id." AND `walmart_product_variants`.`status`='".WalmartShopDetails::PRODUCT_STATUS_STAGE."' AND `walmart_product`.`category` != '')) as `merged_data`";
                    /*$result = JetProduct::findBySql($sql)->all();
                    return count($result);*/
                    $result = \frontend\components\Data::sqlRecords($sql, 'one');

                    return $result['count'];
                },
                'format'=>'raw',
            ],
            [
                'label'=>'Orders',
                'value' => function($data){
                    $sql = 'SELECT `order_total` FROM `walmart_order_details` WHERE `status` = "completed" AND `merchant_id`='.$data->merchant_id;
                    $result = JetProduct::findBySql($sql)->all();
                    return count($result);
                },
                'format'=>'raw',
            ],
            [
                'label'=>'Revenue',
                'value' => function($data){
                    $result1 = Yii::$app->getDb()->createCommand("SELECT `order_total` FROM `walmart_order_details` WHERE `status` = 'completed' AND `merchant_id`=$data->merchant_id")->queryAll();
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
                    $isSet = Yii::$app->getDb()->createCommand("SELECT `consumer_id` FROM  `walmart_configuration` where `merchant_id`='".$data->merchant_id."'")->queryOne();
                    if ($isSet){
                        return "Yes";
                    }else{
                        return "No";
                    }
                },
                'format'=>'raw',
            ],
            [
                'label'=>'Seller Username',
                'attribute'=>'seller_username',                
            ],  
            [
                'label'=>'Seller Password',
                'attribute'=>'seller_password',                
            ],   
            [
                'attribute'=>'install_date',
                'format'=>'raw',
                'label'=>'Install Date',
                'value'=>'walmartExtensionDetail.install_date',
                'filter'=>"<strong>From :</strong> ".DatePicker::widget([
                        'model'=>$searchModel,
                        'attribute'=>'install_date',
                        'clientOptions'=>[
                            'autoclose'=>true,
                            'format'=>'yyyy-mm-dd',
                        ]
                    ])."<strong>To :</strong>".DatePicker::widget([
                        'model'=>$searchModel,
                        'attribute'=>'install_date2',
                        'clientOptions'=>[
                            'autoclose'=>true,
                            'format'=>'yyyy-mm-dd',
                        ]
                    ]),
            ],
            //'walmartExtensionDetail.expire_date',
            [
                'attribute'=>'expire_date',
                'format'=>'raw',
                'label'=>'Expire Date',
                'value'=>'walmartExtensionDetail.expire_date',
                'filter'=>"<strong>From :</strong> ".DatePicker::widget([
                        'model'=>$searchModel,
                        'attribute'=>'expire_date',
                        'clientOptions'=>[
                            'autoclose'=>true,
                            'format'=>'yyyy-mm-dd',
                        ]
                    ])."<strong>To :</strong>".DatePicker::widget([
                        'model'=>$searchModel,
                        'attribute'=>'expire_date2',
                        'clientOptions'=>[
                            'autoclose'=>true,
                            'format'=>'yyyy-mm-dd',
                        ]
                    ]),
            ],
            //'walmartExtensionDetail.status',
            [
                'attribute'=>'status1',
                'label'=>'Status',
                'value'=>'walmartExtensionDetail.status',

                'filter'=>array("Purchased"=>"Purchased","Not Purchase"=>"Not Purchase","License Expired"=>"License Expired","Trial Expired"=>"Trial Expired"),


            ],
            [
                'format'=>'raw',
                'attribute' => 'status',
                'value'=>function ($data){

                    if ($data->status == "1") {
                        return "install";
                    }
                    else if ($data->status =="0") {
                        return "uninstall";
                    }
                },

                'filter'=>array(1=>"install",0=>"uninstall"),
            ],
            [
                'attribute'=>'uninstall_date',
                'label'=>'uninstall_date',
                'value'=>'walmartExtensionDetail.uninstall_date',

                'filter'=>"<strong>From :</strong> ".DatePicker::widget([
                        'model'=>$searchModel,
                        'attribute'=>'uninstall_date',
                        'clientOptions'=>[
                            'autoclose'=>true,
                            'format'=>'yyyy-mm-dd',
                        ]
                    ])."<strong>To :</strong>".DatePicker::widget([
                        'model'=>$searchModel,
                        'attribute'=>'uninstall_date2',
                        'clientOptions'=>[
                            'autoclose'=>true,
                            'format'=>'yyyy-mm-dd',
                        ]
                    ]),

            ],
            // 'token',
            // 'currency',
            //'status',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {link}',
                'buttons' => [
                    'link' => function ($url,$model,$key) {
                        return '<a data-pjax="0" href="/integration/walmart/site/managerlogin?ext='.$model['merchant_id'].'&&enter=true">Login as</a>';
                    },
                    'view' => function ($url,$model)
                    {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',  ['/walmartshopdetails/view','id'=>$model->merchant_id]

                        );
                    },
                    'update' => function ($url,$model)
                    {
                        return Html::a(
                            '<span class="glyphicon glyphicon-plus-sign"> </span>',['/walmartshopdetails/update','id'=>$model->merchant_id]
                        );
                    },


                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>
<script type="text/javascript">
    function selectPage(node){
        var value=$(node).val();
        $('#jet_extention_details').children('select.form-control').val(value);
    }
</script>