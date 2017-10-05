<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
/*$this->params['breadcrumbs'][] = $this->title;*/
?>


<div class="page-content jet-install">
    <div class="container">
        <div class="row">
            <div class="col-lg-offset-2 col-md-offset-2 col-lg-8 col-md-8 col-sm-12 col-xs-12">
                <div class="content-section">
                    <div class="form new-section">
                        <div class="site-login">
                            <div class="head">
                                <h1><?= Html::encode($this->title) ?></h1>

                                <!-- <p>Please fill out the following fields to login:</p> -->
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php $form = ActiveForm::begin(['id' => 'referral-login-form', 'class' => 'referral-login']); ?>

                                        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                                        <?= $form->field($model, 'password')->passwordInput() ?>

                                        <?= $form->field($model, 'rememberMe')->checkbox() ?>

                                        <div style="color:#999;margin:1em 0">
                                            If you forgot your password you can <?= Html::a('reset it', ['site/request-password-reset']) ?>.
                                        </div>

                                        <div class="form-group">
                                            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
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

