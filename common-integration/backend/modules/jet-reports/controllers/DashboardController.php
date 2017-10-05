<?php

namespace backend\modules\reports\controllers;

use common\models\Admin;
use common\models\AdminLoginForm;
use common\models\JetConfiguration;
use common\models\JetExtensionDetail;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;



/**
 * Reports controller
 */
class DashboardController extends BaseController
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
        ];
    }

    
    public function actionIndex()
    {
    	
    	$connection=Yii::$app->getDb();
    	
    	date_default_timezone_set('Asia/Kolkata');
    	return $this->render('index');
    }
    
}
