<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\JetRefund */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Jet Refunds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-refund-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
<div class="table-responsive">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'merchant_order_id',
            'order_item_id',
            'quantity_returned',
            'refund_quantity',
            'refund_reason',
            'refund_feedback',
            'refund_tax',
            'refund_shippingcost',
            'refund_shippingtax',
            'refund_id',
            'refund_amount',
            'refund_id',
            'refund_status',
           // 'merchant_id',
        ],
    ]) ?>
</div>
</div>
