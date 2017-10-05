<?php

namespace frontend\modules\referral;

use Yii;

class Refer extends \yii\base\Module
{
    public $controllerNamespace = 'frontend\modules\referral\controllers';

    public $defaultRoute = 'account/dashboard';

    public function init()
    {
        parent::init();

        if(property_exists(Yii::$app->user, 'identityClass'))
        {
        	Yii::$app->user->identityClass = 'frontend\modules\referral\models\SubUser';
        }

        if(property_exists(Yii::$app->errorHandler, 'errorAction'))
        {
            Yii::$app->errorHandler->errorAction = '/referral/error/index';
            Yii::$app->errorHandler->errorView =  __DIR__ . '/views/errorHandler/errorNexception.php';
            Yii::$app->errorHandler->exceptionView = __DIR__ . '/views/errorHandler/errorNexception.php';
        }
    }
}
