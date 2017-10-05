<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\JetDynamicPrice */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-dynamic-price-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'merchant_id')->hiddenInput()->label(false)?>

    <?= $form->field($model, 'product_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'sku')->textInput(['readonly' => 'readonly']) ?>

    <?= $form->field($model, 'min_price')->textInput() ?>

    <?= $form->field($model, 'current_price')->textInput() ?>

    <?= $form->field($model, 'max_price')->textInput() ?>

    <?= $form->field($model, 'bid_price')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
