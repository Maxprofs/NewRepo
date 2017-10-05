<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggcanada\models\NeweggOrderDetailSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="newegg-order-detail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'merchant_id') ?>

    <?= $form->field($model, 'seller_id') ?>

    <?= $form->field($model, 'order_number') ?>

    <?= $form->field($model, 'order_data') ?>

    <?= $form->field($model, 'shopify_order_date') ?>

    <?php  echo $form->field($model, 'order_status_description') ?>

    <?php  echo $form->field($model, 'invoice_number') ?>

    <?php  echo $form->field($model, 'order_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
