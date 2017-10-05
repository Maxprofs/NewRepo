<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\jet\models\JetRegistration */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-registration-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'merchant_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shipping_source')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'other_shipping_source')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reference')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'already_selling')->dropDownList([ 'Yes' => 'Yes', 'No' => 'No', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'api_provider_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_uninstalled_previous')->dropDownList([ 'Yes' => 'Yes', 'No' => 'No', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'agreement')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'other_reference')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
