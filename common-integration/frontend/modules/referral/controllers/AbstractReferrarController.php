<?php
namespace frontend\modules\referral\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use frontend\modules\referral\models\SubUser;
use frontend\modules\referral\components\Helper;

class AbstractReferrarController extends Controller
{
    public function beforeAction($action)
    {
    	$this->enableCsrfValidation = false;
    	if (parent::beforeAction($action)) {
	    	if (!Yii::$app->user->isGuest) {
	    		if (SubUser::createReferrer()) {
	    			Yii::$app->response->redirect(['referral/account/dashboard']);
            		Yii::$app->end();
	    		}
	    		elseif (!Helper::isAuthorised()) {
	    			Yii::$app->session->setFlash('error', 'Not Authorized Action');
		            throw new UnauthorizedHttpException('Not Authorized Action.');
		            Yii::$app->end();
	    		}
	    		elseif (!($accountStatus=Helper::checkAccountStatus()) && $this->action->controller->id == 'account') {
	    			$allowed = ['approval', 'logout'];
	    			if(!in_array($this->action->id, $allowed)) {
	    				//Yii::$app->session->setFlash('error', 'Your account is Under Review.');
	            		Yii::$app->response->redirect(['referral/account/approval']);
        				Yii::$app->end();
	    			}
	    		}
	    		elseif ($accountStatus && $this->action->controller->id == 'account' && $this->action->id == 'approval') {
	    			//Yii::$app->session->setFlash('sucess', 'Your account has been approved.');
		            Yii::$app->response->redirect(['referral/account/dashboard']);
            		Yii::$app->end();
	    		}
	        }
	        else {
	        	$actionId = $this->action->id;
	        	$allowedActions = ['login', 'signup'];
	        	if($this->action->controller->id == 'account' &&  !in_array($actionId, $allowedActions))
	        	{
	        		Yii::$app->response->redirect(['referral/account/login']);
            		Yii::$app->end();
	        	}
	        }
	        $this->layout = 'main';
	        return true;
    	} 
    	else {
    		return false;
    	}
    }
}
