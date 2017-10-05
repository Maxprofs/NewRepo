<?php

namespace frontend\modules\jet;
use Yii;
/**
 * jet module definition class
 */
class Jet extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\jet\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setAliases([
            '@jet-assets' => __DIR__ . '/assets' 
            ]); 
        // custom initialization code goes here
//        if(property_exists(Yii::$app->errorHandler, 'errorAction'))
//        {
//            Yii::$app->errorHandler->errorAction = '/jet/site/error';
//            Yii::$app->errorHandler->errorView =  __DIR__ . '/views/errorHandler/errorNexception.php';
//            Yii::$app->errorHandler->exceptionView = __DIR__ . '/views/errorHandler/errorNexception.php';
//        }

        /*  Code For Referral Start */
        Yii::$app->on('beforeAction', ['frontend\modules\referral\components\Helper', 'beforeActionEvent']);
        /* Code For Referral End */
    }
}
