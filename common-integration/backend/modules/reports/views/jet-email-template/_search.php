<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\JetEmailTemplateSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-email-template-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'template_title') ?>

    <?= $form->field($model, 'template_path') ?>

    <?= $form->field($model, 'custom_title') ?>

    <?= $form->field($model, 'show_on_admin_setting') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
