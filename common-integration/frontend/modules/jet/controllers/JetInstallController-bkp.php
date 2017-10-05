<?php 
namespace frontend\modules\jet\controllers;

use Yii;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Installation;
use frontend\modules\jet\models\JetInstallation;

class JetInstallController extends JetmainController
{
	public function beforeAction($action)
    {
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

	public function actionIndex()
	{
		$this->layout = 'blank';

		$step = Yii::$app->request->get('step',false);
		if(!$step) 
		{
			$installation = Installation::isInstallationComplete(MERCHANT_ID);
            if($installation) 
            {
                if(strcasecmp($installation['status'], 'pending') == 0) {
                    $step = (int)$installation['step'];
                    $step = $step+1;
                } else {
                	$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/site/index');
                    return false;
                }
            } else {
                $step = Installation::getFirstStep();
            }
		}

		return $this->render('installation', ['currentStep'=>$step]);
	}

	public function actionRenderstep()
	{
		$this->layout = 'main2';
		
		$stepId = Yii::$app->request->post('step',false);
		if($stepId)
		{
			$stepInfo = Installation::getStepInfo($stepId);
			if(!isset($stepInfo['error'])) {
				$templateFile = $stepInfo['template'];
				$html = $this->renderAjax($templateFile,[],true);
				return json_encode(['success'=>true,'content'=>$html,'steptitle'=>$stepInfo['name']]);
			} else {
				return json_encode(['error'=>true,'message'=>'Invalid Step Id.']);
			}
		}
		else
		{
			return json_encode(['error'=>true,'message'=>'Invalid Step Id.']);
		}
	}

	public function actionSavestep()
	{
		$stepId = Yii::$app->request->post('step',false);
		if($stepId)
		{
			try
			{
				$model = JetInstallation::find()->where(['merchant_id'=>MERCHANT_ID])->one();
		        if(is_null($model)) {
		            $model = new JetInstallation();
		            $model->merchant_id = MERCHANT_ID;
		        }
		        
		        if($stepId == Installation::getFinalStep())
		            $model->status = Installation::INSTALLATION_STATUS_COMPLETE;
		        else 
		            $model->status = Installation::INSTALLATION_STATUS_PENDING;

		        $model->step = $stepId;
		        $model->save();

		        return json_encode(['success'=>true,'message'=>'Saved Successfully!!']);
	    	}
	    	catch(Exception $e) {
	    		return json_encode(['error'=>true,'message'=>$e->getMessage()]);
	    	}
		}
		else
		{
			return json_encode(['error'=>true,'message'=>'Invalid Step Id.']);
		}
	}
	
	public function actionHelp()
	{
		$this->layout = 'blank';
		if(isset($_GET['step'])){
			return $this->render('help/step_'.$_GET['step'], ['step'=>$_GET['step']]);
		}
	}
	public function actionCheckProgressStatus()
	{
		$userData=Data::sqlRecords("SELECT id FROM user","all","select");
		if(is_array($userData) && count($userData)>0)
		{
			foreach ($userData as $value) 
			{
				$step=Installation::getCompletedStepId($value['id']);
				//check & save progress steps of each merchant
				$installedCollection=Data::sqlRecords("SELECT `id` FROM `jet_installation` WHERE merchant_id=".$value['id']." limit 0,1","one","select");
				if(!$installedCollection){
					echo "merchant_id:".$value['id']." step:".$step."<br>";
				}
			}
		}
	}

	// Save Jet Partner panel login details
	public function actionSavelogindetails ()
	{
		if(Yii::$app->request->post())
		{
			$response = false;
			$data = Yii::$app->request->post();
			$mer_email="nehasingh@cedcoss.com";
			$subject = "Jet partner panel login details";
			$etx_mer='<html><head></head><body style="font-family:Arial,sans-serif; font-size:12px"><center><table border="0" cellspacing="0" cellpadding="0" style="width:80%; text-align:left"><tr><td>'.chr(10);
			$headers_mer = "MIME-Version: 1.0" . chr(10);
			$headers_mer .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
			$headers_mer .= 'From: <shopify@cedcommerce.com>' . chr(10);
			$headers_mer .= 'Bcc: <james@cedcommerce.com>' . chr(10);
			$headers_mer .= 'Bcc: <anupriyaverma@cedcommerce.com>' . chr(10);
            $headers_mer .= 'Bcc: <nidhirajput@cedcommerce.com>' . chr(10);
            $headers_mer .= 'Bcc: <jimmoore@cedcommerce.com>' . chr(10);
            $headers_mer .= 'Bcc: <swatishukla@cedcommerce.com>' . chr(10);
			$headers_mer .= 'Bcc: <arpansrivastava@cedcoss.com>' . chr(10);
			$mode_mer = "";
			$etx_mer .='Store name :'.$data["storename"].' <br /><br /><br/>'.chr(10);
			$etx_mer .='User name :'.$data["username"].' <br /><br /><br/>'.chr(10);
			$etx_mer .='Password :'.$data["password"].' <br /><br /><br/>'.chr(10);
			$etx_mer .='</br></br>'.chr(10);
			$etx_mer .='Thanking you,</br></br>'.chr(10);
			$etx_mer .='James(Jet-Integration app developer)</br></br>'.chr(10);		
			$etx_mer .= '</tr></td></table></center></body></html>'.chr(10);
			$response = mail($mer_email,$subject, $etx_mer, $headers_mer);
			if ($response) 
				echo "Thank you for giving your login details! Our team will contact you soon ";
			else
				echo "Please submit details again";
		}
	}
}	