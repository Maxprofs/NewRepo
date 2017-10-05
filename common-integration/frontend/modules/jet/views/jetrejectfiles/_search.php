<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\JetErrorfileInfoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-errorfile-info-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'jet_file_id') ?>

    <?= $form->field($model, 'file_name') ?>

    <?= $form->field($model, 'file_type') ?>

    <?= $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'error') ?>

    <?php // echo $form->field($model, 'date') ?>

    <?php // echo $form->field($model, 'jetinfofile_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
