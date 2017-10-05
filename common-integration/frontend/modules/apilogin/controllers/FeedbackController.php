<?php

namespace frontend\modules\apilogin\controllers;
use frontend\modules\walmart\components\Sendmail;
use yii\helpers\BaseJson;
use Yii;

class FeedbackController extends \yii\web\Controller
{
	public function beforeAction($action) { 
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action);
    }
    public function actionClientFeedback()
    {
    	$getRequest = Yii::$app->request->post();

    	if(isset($getRequest['email']) && !empty($getRequest['email']) && isset($getRequest['first_name']) && !empty($getRequest['first_name']) && isset($getRequest['last_name']) && !empty($getRequest['last_name']) && isset($getRequest['description']) && !empty($getRequest['description']) && isset($getRequest['type']) && !empty($getRequest['type']) ){
    		$name = $getRequest['first_name'].' '.$getRequest['last_name'];
    		$data['name'] = $name;
    		$data['feedback_type'] = $getRequest['feedback_type'];
    		$data['description'] = $getRequest['description'];
    		$data['email'] = $getRequest['email'];
            $data['type'] = $getRequest['type'];
    		$this->email($data);
    		$validateData = ['success' =>true ,'message' =>'feedback send successfully'];
            return BaseJson::encode($validateData);
    	}
    	else{
    		$validateData = ['success' =>false ,'message' =>'invalid data provide'];
            return BaseJson::encode($validateData);
    	}

    }

	public  function email($data)
	{
		$mer_email= 'shopify@cedcommerce.com';
		$subject='Feedback for  App: '.$data['type'];
		$etx_mer="";
		$headers_mer = "MIME-Version: 1.0" . chr(10);
		$headers_mer .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
		$headers_mer .= 'From: '.$data['email'].'' . chr(10);
		$etx_mer .=$data['description'];

		mail($mer_email,$subject, $etx_mer, $headers_mer);
	}//Mail for customer feedback
    
}



 