<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\JetRefundSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-refund-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?php echo $form->field($model, 'merchant_order_id') ?>
    	
    <?= $form->field($model, 'order_item_id') ?>

    <?= $form->field($model, 'quantity_returned') ?>

    <?= $form->field($model, 'refund_quantity') ?>

    <?= $form->field($model, 'refund_reason') ?>

    <?php // echo $form->field($model, 'refund_feedback') ?>

    <?php // echo $form->field($model, 'refund_tax') ?>

    <?php // echo $form->field($model, 'refund_shippingcost') ?>

    <?php // echo $form->field($model, 'refund_shippingtax') ?>

    <?php // echo $form->field($model, 'merchant_order_id') ?>

    <?php // echo $form->field($model, 'refund_amount') ?>

    <?php // echo $form->field($model, 'refund_id') ?>

    <?php // echo $form->field($model, 'refund_status') ?>

    <?php // echo $form->field($model, 'merchant_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
