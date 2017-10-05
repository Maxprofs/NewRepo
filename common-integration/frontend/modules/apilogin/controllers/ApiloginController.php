<?php

namespace frontend\modules\apilogin\controllers;
use Yii;
use yii\web\Controller;
use frontend\modules\walmartapi\components\Datahelper;
use yii\helpers\BaseJson;

class ApiloginController extends Controller
{

	public function beforeAction($action) {
	    $this->enableCsrfValidation = false; 
	    return parent::beforeAction($action);
 	}
 	/**
     * validate login detail and manage db
     * @return json_array 
     */
    public function actionLogin()
    {
    	$getRequest = Yii::$app->request->post();
    	if ((isset($getRequest['shop_url']) && !empty($getRequest['shop_url'])) && (isset($getRequest['password']) && !empty($getRequest['password']) ))
    	{   
	        $shopUrl = $getRequest['shop_url'];
	        $password = $getRequest['password'];
	        $currentDate =  date('Y-m-d H:i:s');

	        $loginUser = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `shop_url`='".$shopUrl."'", 'one');
			if(empty($loginUser))
			{
       			$validateData = ['error'=>true ,'message' =>'Shopurl or Password not valid'];
				$headerData = BaseJson::encode($validateData);
				return $headerData;
			}
		    else
		    {
		    	if($loginUser['status'] == 'complete'){
		    		if(md5($password) == $loginUser['password'])
		    		{
				    	$expiry_date = strtotime($loginUser['expiry_date']);
			    		$today = strtotime($currentDate);
			    		if($today<=$expiry_date){
					    	$hash_key = $loginUser['hash_key'];
					    	$merchant_id = $loginUser['merchant_id'];
					    	$appDetail = $this->EnableDisableCheck($getRequest['shop_url']);
					    	$validateData = ['hash_key' =>$hash_key ,'merchant_id' =>$merchant_id,'appDetail'=>$appDetail];
						    $headerData = BaseJson::encode($validateData);
						    return $headerData;
						}
						else{
					       	$i = 30;
					        $expiry_date = strtotime($currentDate . " +".$i."days");
					        $expiry_date = date('Y-m-d H:i:s',$expiry_date);
							$merchant_id = $loginUser['merchant_id'];
							$hash_key = md5(uniqid(rand(), TRUE));
							$appDetail = $this->EnableDisableCheck($getRequest['shop_url']);
							$model = Datahelper::sqlRecords("UPDATE `app_login_check` SET hash_key='".$hash_key."', expiry_date='".$expiry_date."' where merchant_id='".$merchant_id."'", 'all','update');
							$validateData = ['hash_key' =>$hash_key ,'merchant_id' =>$merchant_id,'appDetail'=>$appDetail];
					       	$headerData = BaseJson::encode($validateData);
					        return $headerData;

						}
					}
					else
					{
						$validateData = ['error'=>true ,'message' =>'Shopurl or Password not valid'];
						$headerData = BaseJson::encode($validateData);
						return $headerData;
					}
				}
				else{
					if(isset($getRequest['newpassword']) && !empty($getRequest['newpassword'])){
						if(strlen($getRequest['newpassword']) >= 8){
					    	if($loginUser['password'] == $password){
					    		$merchant_id = $loginUser['merchant_id'];
					    		$status = 'complete';
					    		$newPassword = md5($getRequest['newpassword']);
					    		$appDetail = $this->EnableDisableCheck($getRequest['shop_url']);
								$model = Datahelper::sqlRecords("UPDATE `app_login_check` SET status='".$status."' ,password ='".$newPassword."' where merchant_id='".$merchant_id."'", 'all','update');
								$validateData = ['hash_key' =>$loginUser['hash_key'] ,'merchant_id' =>$merchant_id , 'success' => true , 'login_status' => $status,'appDetail'=>$appDetail];
						       	$headerData = BaseJson::encode($validateData);
						        return $headerData;
					    	}
					    	else{
					    		$validateData = ['error'=>true,'message' => 'Please enter valid password'];
					       		 $headerData = BaseJson::encode($validateData);
					       		 return $headerData;
					    	}
					    }
					    else{
					    	$status = 'pending';
					    	$validateData = ['error' => true, 'message' =>"Please enter atleast eight digit password" ,'login_status' => $status];
						    $headerData = BaseJson::encode($validateData);
						    return $headerData;
					    }
					}
					else{
						$status = 'pending';
				    	$validateData = ['error' => true, 'message' =>"Please enter new password",'login_status' => $status];
					    $headerData = BaseJson::encode($validateData);
					    return $headerData;
					}
				}

	 		}
		}
		 else{
		 	$validateData = ['error'=>true,'message' =>'Invalid data Provide'];
			$headerData = BaseJson::encode($validateData);
			return $headerData;
		 }
			
    }

    public function actionRegister(){
    	$getRequest = Yii::$app->request->post();
    	if ((isset($getRequest['shop_url']) && !empty($getRequest['shop_url']))){
	        $shopUrl = $getRequest['shop_url'];
	        $userValidate = Datahelper::sqlRecords("SELECT * FROM `user` WHERE `username`='".$shopUrl."' LIMIT 0,1", 'one');
	        if(!empty($userValidate)){
		        $alreadylogin = Datahelper::sqlRecords("SELECT * FROM `app_login_check` WHERE `shop_url`='".$shopUrl."' LIMIT 0,1", 'one');
		        if(empty($alreadylogin)){
			        	$currentDate =  date('Y-m-d H:i:s');
			        	$validUser = $this->ExtensionCheck($shopUrl);
				       	if($validUser) {
				       		if(isset($getRequest['device_access_token']) && !empty($getRequest['device_access_token'])){
				       			 $password = md5(uniqid(rand(), TRUE));
				       			 $email = $validUser['email'];
				       			 $sendEmail = $this->email($email ,$password);
				       			 $merchant_id = $validUser['merchant_id'];
					       		 $created_at =  date('Y-m-d H:i:s');
					       		 $i = 30;
					       		 $expiry_date = strtotime($created_at . " +".$i."days");
					       		 $expiry_date = date('Y-m-d H:i:s',$expiry_date);
					       		 $hash_key = md5(uniqid(rand(), TRUE));
					       		 $status = 'pending';
					       		 $model = Datahelper::sqlRecords("INSERT INTO `app_login_check` (`merchant_id`,`shop_url`,`expiry_date`,`created_at`,`hash_key`,`password`,`status`,`device_access_token`) VALUES ('".$merchant_id."','".$shopUrl."','".$expiry_date."','".$created_at."','".$hash_key."' , '".$password."' , '".$status."', '".$getRequest['device_access_token']."')", 'all','insert');
					       		 $validateData = ['message' => "Verification Password has been send to your Registered Shopify Store Email.",'success' =>true ,'login_status' => $status];
					       		 $headerData = BaseJson::encode($validateData);
					       		 return $headerData;
					       	}
					     	elseif(isset($getRequest['android_reg_id']) && !empty($getRequest['android_reg_id'])){
					     		 $password = md5(uniqid(rand(), TRUE));
				       			 $email = $validUser['email'];
				       			 $sendEmail = $this->email($email ,$password);
				       			 $merchant_id = $validUser['merchant_id'];
					       		 $created_at =  date('Y-m-d H:i:s');
					       		 $i = 30;
					       		 $expiry_date = strtotime($created_at . " +".$i."days");
					       		 $expiry_date = date('Y-m-d H:i:s',$expiry_date);
					       		 $hash_key = md5(uniqid(rand(), TRUE));
					       		 $status = 'pending';
					       		 $model = Datahelper::sqlRecords("INSERT INTO `app_login_check` (`merchant_id`,`shop_url`,`expiry_date`,`created_at`,`hash_key`,`password`,`status`,`android_reg_id`) VALUES ('".$merchant_id."','".$shopUrl."','".$expiry_date."','".$created_at."','".$hash_key."' , '".$password."' , '".$status."', '".$getRequest['android_reg_id']."')", 'all','insert');
					       		 $validateData = ['message' => "Verification Password has been send to your Registered Shopify Store Email.",'success' =>true ,'login_status' => $status];
					       		 $headerData = BaseJson::encode($validateData);
					       		 return $headerData;
					     	}
					       	else{
					       		$validateData = ['message' => "Device Access token not set.",'error' =>true ];
					       		 $headerData = BaseJson::encode($validateData);
					       		 return $headerData;
					       	}

				       	}
				       	else{
						       	$validateData = ['error'=>true,'message' => 'You Have not install our any integration app'];
					       		 $headerData = BaseJson::encode($validateData);
					       		 return $headerData;
									
						}
				}   
				else{
					if(isset($getRequest['device_access_token']) && !empty($getRequest['device_access_token'])){
							if($getRequest['device_access_token'] != $alreadylogin['device_access_token']){
								$model = Datahelper::sqlRecords("UPDATE `app_login_check` SET device_access_token='".$getRequest['device_access_token'].",".$alreadylogin['device_access_token']."' where merchant_id='".$alreadylogin['merchant_id']."'", 'all','update');
							}
					}
					if(isset($getRequest['android_reg_id']) && !empty($getRequest['android_reg_id'])){
						if($getRequest['android_reg_id'] != $alreadylogin['android_reg_id']){
							$model = Datahelper::sqlRecords("UPDATE `app_login_check` SET android_reg_id='".$getRequest['android_reg_id'].",".$alreadylogin['android_reg_id']."' where merchant_id='".$alreadylogin['merchant_id']."'", 'all','update');
						}
					}
					if($alreadylogin['status'] =='complete')
					{
						$validateData = ['success'=>true, 'message' =>"Please Enter Your Password.",'login_status' =>$alreadylogin['status']];
						$headerData = BaseJson::encode($validateData);
						return $headerData;
					}
					elseif(isset($alreadylogin['forgot_password_key']) && !empty($alreadylogin['forgot_password_key'])){
					$status = 'passwordpending';
					$validateData = ['success'=>true, 'message' =>"Please Enter the Verification Password that has been send to your Registered Shopify Store Email.",'login_status' =>$status];
					$headerData = BaseJson::encode($validateData);
					return $headerData;
					}
					else{
						$status = 'pending';
						$validateData = ['success'=>true, 'message' =>"Please Enter the Verification Password that has been send to your Registered Shopify Store Email.",'login_status' =>$status];
						$headerData = BaseJson::encode($validateData);
						return $headerData;
					}


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
	}// Sending

    /* check extention paid by merchant*/
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
			$query = 'select *,app.shop from `jet_extension_detail` jet INNER JOIN `app_status` app ON app.merchant_id=jet.merchant_id where app.shop ="'.$shopUrl.'" AND jet.app_status = "install" AND jet.status != "Trial Expired" limit 0,1';
			$validUser = Datahelper::sqlRecords($query, 'one');
			if(empty($validUser)){
				return false;
			}
			else{
				return $validUser;
			}
	}

	 /* enable-disable app setting manage*/
	public function EnableDisableCheck($shopUrl){
		$appDetail = [];
		$extension = ['Ced_Walmart'=>'WalmartExtensionCheck','Ced_jet'=>'JetExtensionCheck'];
		foreach ($extension as $key => $value) {
			$data = $this->$value($shopUrl);
			if($data){
				$appDetail[$key]=true;
			}
			else{
				$appDetail[$key]=false;
			}
			
		}
		return $appDetail;

	}

}
