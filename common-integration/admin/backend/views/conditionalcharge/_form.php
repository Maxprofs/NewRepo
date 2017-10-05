<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ConditionalCharge */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="conditional-charge-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'charge_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'charge_condition')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'charge_range')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'merchant_base')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'charge_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'apply')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
