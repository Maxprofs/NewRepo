<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\JetReturnSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-return-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'merchant_order_id') ?>

    <?= $form->field($model, 'order_item_id') ?>

    <?= $form->field($model, 'qty_returned') ?>

    <?= $form->field($model, 'qty_refunded') ?>

    <?php // echo $form->field($model, 'return_refundfeedback') ?>

    <?php // echo $form->field($model, 'agreeto_return') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'returnid') ?>

    <?php // echo $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'shipping_cost') ?>

    <?php // echo $form->field($model, 'shipping_tax') ?>

    <?php // echo $form->field($model, 'tax') ?>

    <?php // echo $form->field($model, 'merchant_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
