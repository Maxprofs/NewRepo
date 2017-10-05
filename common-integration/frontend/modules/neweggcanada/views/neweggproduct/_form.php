<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggcanada\models\NeweggProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="newegg-product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'product_id')->textInput() ?>

    <?= $form->field($model, 'merchant_id')->textInput() ?>

    <?= $form->field($model, 'shopify_product_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'newegg_category')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'error')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'upload_status')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
