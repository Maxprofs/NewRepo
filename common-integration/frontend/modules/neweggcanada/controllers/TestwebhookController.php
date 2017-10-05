<?php

namespace frontend\modules\neweggcanada\controllers;

use yii\web\Controller;
use frontend\modules\neweggcanada\controllers;
use frontend\modules\neweggcanada\components\Data;
use yii\helpers\BaseJson;
use Yii;
use frontend\modules\neweggcanada\components\ShopifyClientHelper;
use frontend\modules\neweggcanada\controllers\NeweggMainController;



class TestwebhookController extends NeweggMainController
{
    /**
     * Check request authentication
     * @return user status 
    */
    public function actionTest()
    {
     
     $sc = new ShopifyClientHelper($_GET['shop'], $token, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
     print_r($sc);die("jjjjj");
     $this->createWebhooks($sc,$_GET['shop'],$_SESSION['token']); // Creating Webhook
 	}

 	public function createWebhooks(){
 		
 	}
}
