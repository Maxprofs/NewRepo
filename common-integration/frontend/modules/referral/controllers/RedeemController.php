<?php

namespace frontend\modules\referral\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\referral\models\SubUser;
use frontend\modules\referral\models\ReferrerPayment;
use frontend\modules\referral\components\Helper;
use frontend\modules\referral\components\Redeem;
use frontend\modules\referral\components\Dashboard;

class RedeemController extends AbstractReferrarController
{
	public function actionChoosePayment()
	{
		$referrer_id = Helper::getCurrentReferrerId();

		if($referrer_id)
        {
            $status = ReferrerPayment::PAYMENT_STATUS_COMPLETE;

            $query = "SELECT `referrer_payment`.*, `user`.`username`, `user`.`shop_name` FROM `referrer_payment` INNER JOIN `user` ON `user`.`id` = `referrer_payment`.`referral_merchant_id` WHERE `referrer_payment`.`referrer_id`='{$referrer_id}' AND `referrer_payment`.`status`='{$status}' AND `referrer_payment`.`type` LIKE 'credit'";
            
            $result = Helper::sqlRecords($query, 'all');

         	return $this->render('choose-payment', ['data'=>$result]);   
        }
        else
        {
        	return $this->redirect(['referral/account/dashboard']);
        }
	}

    public function actionChooseMethod()
    {
    	$session = Yii::$app->session;
    	$formData = $session->get('form-data');
    	
    	if(Yii::$app->request->post('selectedIds', false)) {
    		$post = Yii::$app->request->post();
    		return $this->render('choose-method', ['post'=>$post]);
    	} elseif($formData && isset($formData['paymentIds']) && !empty($formData['paymentIds'])) {
    		//$session->remove('form-data');
    		$post = ['selectedIds'=> $formData['paymentIds'], 'paymentId'=> explode(',', $formData['paymentIds']), 'formData' => $formData];
    		return $this->render('choose-method', ['post'=>$post]);
    	}
    	else {
    		Yii::$app->session->setFlash('error', 'Please choose payments.');
    		return $this->redirect(['redeem/choose-payment']);
    	}
    	//return $this->redirect(['account/dashboard']);
        //die('Under Development');
    }

    public function actionPost()
    {
        $session = Yii::$app->session;
        $session->remove('form-data');
        
    	if($redeem_type=Yii::$app->request->post('redeem-option', false) && $paymentIds=Yii::$app->request->post('paymentIds', false))
    	{
    		$referrer_id = Helper::getCurrentReferrerId();
    		$postData = Yii::$app->request->post();

    		if($postData['redeem-option'] == 'paypal')
    		{
    			$requestedAmount = $postData['amount'];
    			if(!is_numeric($requestedAmount)) {
    				Yii::$app->session->setFlash('error', 'Please enter valid amount.');
    				return $this->gotoChooseMethod($postData);
    			}
    			elseif(!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
    				Yii::$app->session->setFlash('error', 'Please enter valid email.');
    				return $this->gotoChooseMethod($postData);
    			}
    			else
    			{
    				$activeAmount = floatval(Dashboard::getActiveAmount());

    				$requestedAmount = Redeem::caclulatePaymentAmountFromIds($paymentIds);
    				
    				if($requestedAmount == '0.00' || $activeAmount < floatval($requestedAmount)) {
    					Yii::$app->session->setFlash('error', 'Requested amount can not be redeemed.');
    					return $this->gotoChooseMethod($postData);
    				}
    				else 
    				{
    					//insert debit request in referrer payment table
    					$type = 'debit';
    					$comment = 'Redeemed via Paypal.';
    					/*$insertQuery = "INSERT INTO `referrer_payment`(`payment_id`, `referrer_id`, `referral_id`, `referral_merchant_id`, `amount`, `type`, `comment`, `app`) VALUES ('', '{$referrer_id}', '', '', '{$requestedAmount}', '{$type}', '{$comment}', '')";
    					Helper::sqlRecords($insertQuery, null, 'insert');*/
                        $referrerPaymentData = ['referrer_id'=>$referrer_id, 'amount'=>$requestedAmount, 'type'=>$type, 'comment'=>$comment];
                        $referrerPayment = new ReferrerPayment();
                        $referrerPayment->load(['ReferrerPayment'=>$referrerPaymentData]);
                        $referrerPayment->save(false);
                        
    					//send request to admin about the request
    					$redeem_method = 'paypal';
                        $postData['referrer_payment_id'] = $referrerPayment->id;
    					$data = addslashes(json_encode($postData));
    					$query = "INSERT INTO `referrer_redeem_requests`(`referrer_id`, `amount`, `redeem_method`, `data`) VALUES ('{$referrer_id}', '{$requestedAmount}', '{$redeem_method}', '{$data}')";
    					Helper::sqlRecords($query, null, 'insert');

    					//change status of selected referrel payments
    					$status = ReferrerPayment::PAYMENT_STATUS_REDEEMED;
    					$updateQuery = "UPDATE `referrer_payment` SET `status`='{$status}' WHERE `id` IN ('{$paymentIds}')";
    					Helper::sqlRecords($updateQuery, null, 'update');

    					Yii::$app->session->setFlash('success', 'Your request has been submitted successfully.');
    					return $this->redirect(['payment/index']);
    				}
    			}
    		}
    		elseif($postData['redeem-option'] == 'subscription')
    		{
    			$requestedAmount = $postData['months'];
    			if(!is_numeric($requestedAmount)) {
    				Yii::$app->session->setFlash('error', 'Please enter valid months.');
    				return $this->gotoChooseMethod($postData);
    			}

    			if($postData['account'] == 'other') {
    				$shopurl = $postData['other-account'];
    				if(!preg_match("/.*.myshopify.com$/", $shopurl) || strpos($shopurl, '.myshopify.com') < 4) {
    					Yii::$app->session->setFlash('error', 'Please enter valid shop-url.');
    					return $this->gotoChooseMethod($postData);
    				}
    			}
    			else {
    				if(!Yii::$app->user->isGuest && Yii::$app->user->identity->username != SubUser::REFERRER_CUSTOMER_USERNAME) {
			            $shopurl = Yii::$app->user->identity->username;
			        } else {
			        	Yii::$app->session->setFlash('error', 'You shop-url is not valid.');
						return $this->gotoChooseMethod($postData);
			        }
    			}

    			if($postData['app']== '' || ($postData['app'] != '' && !Redeem::isAppInstalled($postData['app'], $shopurl))) {
    				Yii::$app->session->setFlash('error', 'Haven\'t installed choosen app.');
					return $this->gotoChooseMethod($postData);
    			}
				
				$availableAmount = Dashboard::getReferralCount();
				if(intval($availableAmount) < intval($requestedAmount)) {
					Yii::$app->session->setFlash('error', 'Requested amount can not be redeemed.');
					return $this->gotoChooseMethod($postData);
				}
				else 
				{
					//insert debit request in referrer payment table
					$type = 'debit';
					$comment = 'Redeemed through '.$requestedAmount.' month(s) Subscription for '.$postData['app'].'.';
					/*$insertQuery = "INSERT INTO `referrer_payment`(`payment_id`, `referrer_id`, `referral_id`, `referral_merchant_id`, `amount`, `type`, `comment`, `app`) VALUES ('', '{$referrer_id}', '', '', '{$requestedAmount}', '{$type}', '{$comment}', '')";
					Helper::sqlRecords($insertQuery, null, 'insert');*/
                    $referrerPaymentData = ['referrer_id'=>$referrer_id, 'amount'=>$requestedAmount, 'type'=>$type, 'comment'=>$comment];
                    $referrerPayment = new ReferrerPayment();
                    $referrerPayment->load(['ReferrerPayment'=>$referrerPaymentData]);
                    $referrerPayment->save(false);
                    
					//send request to admin about the request
					$redeem_method = 'subscription';
                    $postData['referrer_payment_id'] = $referrerPayment->id;
					$data = addslashes(json_encode($postData));
					$query = "INSERT INTO `referrer_redeem_requests`(`referrer_id`, `amount`, `redeem_method`, `data`) VALUES ('{$referrer_id}', '{$requestedAmount}', '{$redeem_method}', '{$data}')";
					Helper::sqlRecords($query, null, 'insert');

					//change status of selected referrel payments
					$status = ReferrerPayment::PAYMENT_STATUS_REDEEMED;
					$updateQuery = "UPDATE `referrer_payment` SET `status`='{$status}' WHERE `id` IN ('{$paymentIds}')";
					Helper::sqlRecords($updateQuery, null, 'update');

					Yii::$app->session->setFlash('success', 'Your request has been submitted successfully.');
    				return $this->redirect(['payment/index']);
				}
    		}
    	}
    	else
    	{
    		$postData = Yii::$app->request->post();
    		Yii::$app->session->setFlash('error', 'Please Choose Redeem Option.');
    		return $this->gotoChooseMethod($postData);
    	}
    }

    public function gotoChooseMethod($postData)
    {
    	$session = Yii::$app->session;
    	$session->set('form-data', $postData);
    	return $this->redirect(['choose-method']);
    }
}
