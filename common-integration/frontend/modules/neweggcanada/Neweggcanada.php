<?php

namespace frontend\modules\neweggcanada;

use Yii;

/**
 * neweggcanada module definition class
 */
class Neweggcanada extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\neweggcanada\controllers';

    public $defaultRoute = 'site';

    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
      
        $this->setAliases([
            '@neweggcanada-assets' => __DIR__ . '/assets' 
            ]); 
        // custom initialization code goes here

        /*  Code For Referral Start */
        Yii::$app->on('beforeAction', ['frontend\modules\referral\components\Helper', 'beforeActionEvent']);
        /* Code For Referral End */
    }
}
