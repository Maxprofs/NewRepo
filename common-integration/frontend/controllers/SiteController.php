<?php
namespace frontend\controllers;

use Yii;
use frontend\modules\jet\components\Data;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        $this->layout='blank';
        $urls=[];
        if(!\Yii::$app->user->isGuest) 
        {
          $merchant_id=Yii::$app->user->identity->id;
          $urls = Data::checkInstalledApp($merchant_id);
        }
        else
        {
            $urls['jet']['url']=Yii::getAlias('@webjeturl');
            $urls['walmart']['url']=Yii::getAlias('@webwalmarturl');
            $urls['newegg']['url']=Yii::getAlias('@webneweggurl');
            $urls['sears']['url']=Yii::getAlias('@websearsurl');
        }
        
        return $this->render('index',['urls'=>$urls]); 
    }
    /**
     * page not found
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
    public function actionSavedetails()
    {
    	$msg = ""; 
    	echo '<script language="javascript">';

    	if ($_POST && isset($_POST['emailid'],$_POST['comment']) && trim($_POST['emailid'])!="" && trim($_POST['comment'])!="" ) 
    	{
    		$query = "INSERT INTO `upcoming_clients` (`name`, `email`, `description`) VALUES ('".addslashes($_POST['firstname'])."', '".addslashes($_POST['emailid'])."', '".addslashes($_POST['comment'])."');";
    		Data::sqlRecords($query,null,'insert');
    		$msg= 'alert("Your details submitted successfully");';
    	}else
    	{
    		$msg= 'alert("Please enter the details ...");';
    	}
		echo $msg ;

		echo 'window.location.href="'.Yii::$app->getUrlManager()->getBaseUrl().'";';
		echo '</script>';
    }
}
