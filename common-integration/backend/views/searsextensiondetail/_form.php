<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\SearsExtensionDetail */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sears-extension-detail-form">

    <?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'merchant_id')->textInput(['readonly'=> !$model->isNewRecord]) ?>

    <?= $form->field($model, 'install_date')->textInput() ?>

    <?= $form->field($model, 'date')->textInput() ?>

    <?= $form->field($model, 'expire_date')->textInput() ?>	
    
    <?= $form->field($model, 'panel_username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'panel_password')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uninstall_date')->textInput() ?>
    
    <?php $list= ["1"=>"Installed","0"=>"Uninstalled"]; ?>
    <?= $form->field($model, 'app_status')->dropDownList($list,['prompt'=>'Is Installed']);?>

    <?php $listdata= array("Purchased"=>"Purchased","Not Purchase"=>"Not Purchase",
        "License Expired"=>"License Expired","Trial Expired"=>"Trial Expired");    ?>
    <?= $form->field($model, 'status')->dropDownList($listdata,['prompt'=>'Select Status']);?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
