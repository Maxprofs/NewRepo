<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\JetDynamicPriceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-dynamic-price-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'merchant_id') ?>

    <?= $form->field($model, 'product_id') ?>

    <?= $form->field($model, 'sku') ?>

    <?= $form->field($model, 'min_price') ?>

    <?php // echo $form->field($model, 'current_price') ?>

    <?php // echo $form->field($model, 'max_price') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
