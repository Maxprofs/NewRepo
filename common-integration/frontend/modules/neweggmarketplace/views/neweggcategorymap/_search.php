<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggmarketplace\models\NeweggCategoryMapSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="newegg-category-map-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'merchant_id') ?>

    <?= $form->field($model, 'product_type') ?>

    <?= $form->field($model, 'category_id') ?>

    <?= $form->field($model, 'category_name') ?>

    <?php // echo $form->field($model, 'category_path') ?>

    <?php // echo $form->field($model, 'tax_code') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
