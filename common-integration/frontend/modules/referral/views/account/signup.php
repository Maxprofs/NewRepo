<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\SignupForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Signup';
/*$this->params['breadcrumbs'][] = $this->title;*/
?>

<div class="page-content jet-install">
    <div class="container">
        <div class="row">
            <div class="col-lg-offset-2 col-md-offset-2 col-lg-8 col-md-8 col-sm-12 col-xs-12">
                <div class="content-section">
                    <div class="form new-section">
                        <div class="site-signup">
                            <div class="head">
                                <h1><?= Html::encode($this->title) ?></h1>

                                <p>Please fill out the following fields to signup:</p>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                                        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true]) ?>

                                        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                                        <?= $form->field($model, 'email') ?>

                                        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

                                        <div class="form-group">
                                            <?= Html::submitButton('Signup', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                                        </div>

                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

