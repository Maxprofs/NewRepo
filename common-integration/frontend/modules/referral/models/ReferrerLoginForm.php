<?php
namespace frontend\modules\referral\models;

use Yii;
use common\models\LoginForm;

/**
 * Login form
 */
class ReferrerLoginForm extends LoginForm
{
	public $_user = false;

	/**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login($subusername, $subpass=false, $encoded=false)
    {
        if ($subusername) {
        	if($subUserData = SubUser::authenticateReferrar($subusername, $subpass, $encoded)) {
        		$userId = $subUserData['merchant_id'];
        		return Yii::$app->user->login($this->getUser($userId, $subusername), $this->rememberMe ? 3600 * 24 * 30 : 0);
        	} else {
        		return false;
        	}
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser($userId, $subuser)
    {
        if ($this->_user === false) 
        {
        	if(empty($userId)) {
        		$username = SubUser::REFERRER_CUSTOMER_USERNAME;
        		$user = SubUser::findByUsername($username);
        	}
        	else {
        		$user = SubUser::findIdentity($userId);
        	}
        	$userId = $user->id;

        	$subUser = SubUser::getSubUserByUserName($subuser);
        	$subUserId = $subUser->id;
		
		if($user->_subuser == false) {
			$user->id = $userId.SubUser::SUBUSER_ID_SEPERATOR.$subUserId;
			$user->_subuser = $subUser;
		}

            $this->_user = $user;
        }
        return $this->_user;
    }


}
