<?php

namespace frontend\modules\referral\controllers;

use Yii;
use yii\web\Controller;

class ErrorController extends Controller
{
	/**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'main';
        
        $exception = Yii::$app->errorHandler->exception;
//        $error=Yii::$app->errorHandler->error;
        if ($exception !== null) {
//            return $this->render('error', ['exception' => $exception, 'error'=>$error]);
            return $this->render('error', ['exception' => $exception]);
        }
    }
}
