<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\neweggcanada\models\NeweggOrderDetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Newegg Order Details';
$this->params['breadcrumbs'][] = $this->title;
$viewNeweggOrderDetails = \yii\helpers\Url::toRoute(['neweggorderdetail/view']);
$cancelNeweggOrderDetails = \yii\helpers\Url::toRoute(['neweggorderdetail/cancelorderr']);
$ship = \yii\helpers\Url::toRoute(['neweggorderdetail/shiporder']);
?>
<div class="newegg-order-detail-index content-section">
    <?= Html::beginForm(['neweggorderdetail/orderdetails'], 'post'); ?>

    <div class="jet-pages-heading">
        <h3 class="Jet_Products_style"><?= Html::encode($this->title) ?></h3>

        <div class="submit-upload-wrap">
            <?= Html::submitButton('submit', ['class' => 'btn btn-primary',]); ?>
            <?php $arrAction = array('0' => 'Unshipped', '1' => 'Partially Shipped', '2' => 'Shipped', '3' => 'Invoiced', '4' => 'Voided'); ?>
            <?= Html::dropDownList('action', '', $arrAction, ['class' => 'form-control pull-right',]) ?>
        </div>
        <div class="clear"></div>
    </div>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
//            'merchant_id',
//            'seller_id',
            'order_number',
            'shopify_order_name',
            'order_status_description',
            'invoice_number',
            'order_date',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{cancelorder}{shiporder}',
                'buttons' => [
                    'view' => function ($viewNeweggOrderDetails, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"> </span>',
                            $viewNeweggOrderDetails, ['title' => 'Order detail on Newegg', 'id' => $model->id]);
                    },
                    'cancelorder' => function ($cancelNeweggOrderDetails, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon glyphicon-remove"> </span>',
                            $cancelNeweggOrderDetails, ['title' => 'Cancel', 'id' => $model->id]);
                    },
                    'shiporder' => function ($ship, $model){
                        if(($model->order_status_description=='Unshipped') /*|| ($model->status=='inprogress' )*/)
                        {
                            return Html::a(
                                '<span class="ship">Ship</span>',
                                $ship,['data-pjax'=>0]);
                        }
                    }
                ],
            ],
        ],
    ]); ?>
</div>
<div id="view_jet_order" style="display:none"></div>

