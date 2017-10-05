<?php

namespace frontend\modules\walmart;

use Yii;

class Walmart extends \yii\base\Module
{
    public $controllerNamespace = 'frontend\modules\walmart\controllers';

    public $defaultRoute = 'site';

    public function init()
    {
        parent::init();
        $this->setAliases([
            '@walmart-assets' => __DIR__ . '/assets' 
            ]); 
        // custom initialization code goes here

        /*  Code For Referral Start */
        Yii::$app->on('beforeAction', ['frontend\modules\referral\components\Helper', 'beforeActionEvent']);
        /* Code For Referral End */
    }
}
