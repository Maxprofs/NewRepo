<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;
use backend\modules\reports\models\Issues;

/* @var $this yii\web\View */
/* @var $model backend\modules\reports\models\Issues */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="issues-form">

    <?php $form = ActiveForm::begin(); ?>
    <?php if(ArrayHelper::map(Issues::find()->all(),'issue_type','issue_type')) : ?>
    <?= $form->field($model, 'issue_type')->dropDownList( ArrayHelper::map(Issues::find()->all(),'issue_type','issue_type')/*,['prompt'=>'Select an Issues type']*/
    ) ?>

    <?= '<div class="form-group field-new-issues ">
    <label for="new-issues" class="control-label">New Issue Type</label>
    <input type="text" maxlength="255" name="Issues[new-issues]" class="form-control" id="new-issues">

    <div class="help-block"></div>
    </div>' ?>
    <?php else : ?>
        <?= $form->field($model, 'issue_type')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>
    <?= $form->field($model, 'issue_description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'issue_date')->widget(
    DatePicker::className(), [
        // inline too, not bad
         'inline' => true, 
         // modify template for custom rendering
        'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ]
]) ?>


    <?= $form->field($model, 'assign')->textInput(['maxlength' => true]) ?>

 <?php if(ArrayHelper::map(Issues::find()->all(),'employee_email','employee_email')) : ?>
    <?= $form->field($model, 'employee_email')->dropDownList( ArrayHelper::map(Issues::find()->all(),'employee_email','employee_email') )?>


      <?= '<div class="form-group field-new_employee-email ">
<label for="new_employee_email" class="control-label">New Employee Email</label>
<input type="email" maxlength="255" name="Issues[new_employee_email]" class="form-control" id="new_employee_email">

<div class="help-block"></div>
</div>' ?>
 <?php else : ?>
        <?= $form->field($model, 'employee_email')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
