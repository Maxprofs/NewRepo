<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PricingPlan */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pricing-plan-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'plan_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plan_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'duration')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plan_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'base_price')->textInput() ?>

    <?= $form->field($model, 'special_price')->textInput() ?>

    <?= $form->field($model, 'apply_on')->textInput() ?>

    <?= $form->field($model, 'additional_condition')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
