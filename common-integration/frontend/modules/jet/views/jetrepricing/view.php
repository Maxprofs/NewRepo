<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\models\JetDynamicPrice */

$this->title = $model->sku;
$this->params['breadcrumbs'][] = ['label' => 'Jet Dynamic Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-dynamic-price-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'sku' => $model->sku], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'sku' => $model->sku], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'merchant_id',
            'product_id',
            'sku',
            'min_price',
            'current_price',
            'max_price',
        ],
    ]) ?>

</div>
