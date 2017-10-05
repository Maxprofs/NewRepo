<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggmarketplace\models\NeweggOrderImportErrorSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="newegg-order-import-error-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'order_number') ?>

    <?= $form->field($model, 'merchant_id') ?>

    <?= $form->field($model, 'error_reason') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'newegg_item_number') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
