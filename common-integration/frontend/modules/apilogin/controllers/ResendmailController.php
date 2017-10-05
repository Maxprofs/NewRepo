<?php

namespace frontend\modules\apilogin\controllers;
use Yii;
use yii\web\Controller;
use yii\helpers\BaseJson;
use frontend\modules\walmartapi\components\Datahelper;
use frontend\modules\walmartapi\controllers\WalmartapiController;

class ResendmailController extends Controller
{

	public function beforeAction($action) {
	    $this->enableCsrfValidation = false; 
	    return parent::beforeAction($action);
 	}
 	public function actionResendmail(){ 
 	 	$getRequest = Yii::$app->request->post();
 	 	if ((isset($getRequest['shop_url']) && !empty($getRequest['shop_url']))){
	        $shopUrl = $getRequest['shop_url'];
	        $currentDate =  date('Y-m-d H:i:s');
	        $userValidate = Datahelper::sqlRecords("SELECT * FROM `user` WHERE `username`='".$shopUrl."' LIMIT 0,1", 'one');
	        if(!empty($userValidate)){
					$validUser = $this->ExtensionCheck($shopUrl);
		        	if($validUser) {
				       			 $password = md5(uniqid(rand(), TRUE));
				       			 $email = $validUser['email'];
				       			 $loginUser = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `shop_url`='".$shopUrl."' LIMIT 0,1", 'one');
				       			 $merchant_id = $loginUser['merchant_id'];
				       			 $sendEmail = $this->email($email ,$password);

				       			 $merchant_id = $validUser['merchant_id'];
					       		 $created_at =  date('Y-m-d H:i:s');
					       		 $i = 30;
					       		 $expiry_date = strtotime($created_at . " +".$i."days");
					       		 $expiry_date = date('Y-m-d H:i:s',$expiry_date);
					       		 $hash_key = md5(uniqid(rand(), TRUE));
					       		 $status = 'pending';
					       		 $model = Datahelper::sqlRecords("UPDATE `app_login_check` SET status='".$status."' ,password ='".$password."' where merchant_id='".$merchant_id."'", 'all','update');
								 $validateData = ['success' => true , 'login_status' => $status,'message' =>"Verification Password has been send to your Registered Shopify Store Email."];
								 $headerData = BaseJson::encode($validateData);
									        return $headerData;

			       	}
		       		else{
					       	$validateData = ['error'=>true,'message' => 'You Have not install our any integration app'];
				       		 $headerData = BaseJson::encode($validateData);
				       		 return $headerData;
								
					}
				}
			else{
				$validateData = ['error'=>true,'message' => 'Please go to shopify panel and Install Our Integration App'];
				$headerData = BaseJson::encode($validateData);
				return $headerData;
			}
		}
		else{
		$validateData = ['error'=>true,'message' => 'Please enter Shop_url.'];
		$headerData = BaseJson::encode($validateData);
		return $headerData;
		}

 	}



	public  function email($email ,$password)
	{
		$mer_email= $email;
		$subject="Login verification";
		$etx_mer="";
		$headers_mer = "MIME-Version: 1.0" . chr(10);
		$headers_mer .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
		$headers_mer .= 'From: shopify@cedcommerce.com' . chr(10);
		
		$etx_mer .='Your Verification password is : '.$password;

		mail($mer_email,$subject, $etx_mer, $headers_mer);
	}// Sending shipment not completed on jet mail

	    public function ExtensionCheck($shopUrl){
    	$extention = ['walmart'=>'WalmartExtensionCheck','jet'=>'JetExtensionCheck'];
    	foreach ($extention as $key => $value) {
    		$data = $this->$value($shopUrl);
    		if($data){
    			return $data;
    		}
    	}
    	return false;
    }

	public function WalmartExtensionCheck($shopUrl){
		$validUser = Datahelper::sqlRecords("SELECT `id`,`merchant_id`,`shop_url` , `email` FROM `walmart_shop_details` WHERE `shop_url`='".$shopUrl."' AND `status`=1 LIMIT 0,1", 'one');
		if(empty($validUser)){
				return false;
		}
		else{
			return $validUser;
		}

	}


	public function JetExtensionCheck($shopUrl)	{
			$query = 'select *,app.shop from `jet_extension_detail` jet INNER JOIN `app_status` app ON app.merchant_id=jet.merchant_id where app.shop ="'.$shopUrl.'" AND jet.app_status = "install" AND jet.status = "Purchased" limit 0,1';
			$validUser = Datahelper::sqlRecords($query, 'one');
			if(empty($validUser)){
				return false;
			}
			else{
				return $validUser;
			}
	}
    


}
