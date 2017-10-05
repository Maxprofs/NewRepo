<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggcanada\models\NeweggConfiguration */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="newegg-configuration-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'merchant_id')->textInput() ?>

    <?= $form->field($model, 'seller_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'authorization')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'secret_key')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
