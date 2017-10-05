<?php

namespace frontend\modules\referral\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\referral\models\ReferrerLoginForm;
use frontend\modules\referral\models\ReferrerSignupForm;

class AccountController extends AbstractReferrarController
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

    public function actionDashboard()
    {
        return $this->render('dashboard');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard']);
        }

        $model = new ReferrerLoginForm();
        if($post=Yii::$app->request->post())
        {
        	$post = $post['ReferrerLoginForm'];
        	$login = $model->login($post['username'], $post['password']);
        	if($login) {
        		return $this->redirect(['dashboard']);
        	} else {
                Yii::$app->session->setFlash('error', 'Username Or Password is Incorrect.');
        	   return $this->redirect(['login']);	
        	}
        }
        else 
        {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        $this->redirect(['login']);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard']);
        }

        $model = new ReferrerSignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (self::getUser()->login($user->username, $user->password, true)) {
                    return $this->redirect(['dashboard']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error during signup.');
                   return $this->redirect(['signup']);   
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public static function getUser()
    {
        $model = new ReferrerLoginForm();
        return $model;
    }

    public function actionApproval()
    {
    	return $this->render('approval');
    }
}
