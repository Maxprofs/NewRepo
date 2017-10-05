<?php

namespace frontend\modules\neweggmarketplace;

use Yii;

/**
 * neweggmarketplace module definition class
 */
class NeweggMarketplace extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\neweggmarketplace\controllers';

    public $defaultRoute = 'site';

    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
      
        $this->setAliases([
            '@neweggmarketplace-assets' => __DIR__ . '/assets' 
            ]); 
        // custom initialization code goes here

        /*  Code For Referral Start */
        Yii::$app->on('beforeAction', ['frontend\modules\referral\components\Helper', 'beforeActionEvent']);
        /* Code For Referral End */
    }
}
