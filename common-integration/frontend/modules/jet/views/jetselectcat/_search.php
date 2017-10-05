<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\JetCategorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-category-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'category_id') ?>

    <?= $form->field($model, 'merchant_id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'parent_id') ?>

    <?php // echo $form->field($model, 'parent_title') ?>

    <?php // echo $form->field($model, 'root_id') ?>

    <?php // echo $form->field($model, 'root_title') ?>

    <?php // echo $form->field($model, 'level') ?>

    <?php // echo $form->field($model, 'jet_attributes') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
