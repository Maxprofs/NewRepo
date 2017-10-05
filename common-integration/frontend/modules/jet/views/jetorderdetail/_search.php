<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\JetOrderDetailSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-order-detail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'merchant_id') ?>

    <?= $form->field($model, 'order_item_id') ?>

    <?= $form->field($model, 'merchant_order_id') ?>

    <?= $form->field($model, 'merchant_sku') ?>

    <?php // echo $form->field($model, 'deliver_by') ?>

    <?php  echo $form->field($model, 'shopify_order_name') ?>

    <?php  echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'order_data') ?>

    <?php // echo $form->field($model, 'shipment_data') ?>

    <?php  echo $form->field($model, 'reference_order_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
