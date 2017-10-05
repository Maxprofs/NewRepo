<?php

namespace frontend\modules\neweggmarketplace\controllers;

use yii\web\Controller;
use frontend\modules\neweggmarketplace\controllers;
use frontend\modules\neweggmarketplace\components\Data;
use yii\helpers\BaseJson;
use Yii;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;
use frontend\modules\neweggmarketplace\controllers\NeweggMainController;



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
