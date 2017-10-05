<?php
namespace frontend\modules\referral\models;

use Yii;
use common\models\User;
use frontend\modules\referral\components\Helper;

class SubUser extends User
{
    public $_subuser = false;

    const SUBUSER_ID_SEPERATOR = '~';

    const REFERRER_CUSTOMER_USERNAME = 'other';
    const REFERRER_CUSTOMER_AUTHKEY = 'other';
    const REFERRER_CUSTOMER_SHOPNAME = 'other';
    const REFERRER_CUSTOMER_STATUS = User::STATUS_ACTIVE;//STATUS_DELETED

    public static function authenticateReferrar($username, $password, $encoded)
    {
        if(!$encoded)
            $password = self::getEncodedPassword($password);
    	$query = "SELECT * FROM `referrer_user` WHERE `username` LIKE '{$username}' AND `password` LIKE '{$password}'";
    	$referraData = Helper::sqlRecords($query, 'one');
    	if($referraData) {
    		return $referraData;
    	}
    	return false;
    }

    public static function getEncodedPassword($password)
    {
        return md5($password);
    }

    public static function getSubUserById($subUserId)
    {
    	$subUser = ReferrerUser::findOne(['id' => $subUserId]);
    	return $subUser;
    }

    public static function getSubUserByUserName($username)
    {
    	$referrar = ReferrerUser::findOne(['username' => $username]);
    	return $referrar;
    }

    public static function getSubUserByMerchantId($merchant_id)
    {
    	$referrar = ReferrerUser::findOne(['merchant_id' => $merchant_id]);
    	return $referrar;
    }

    public static function findIdentity($id)
    {
    	if (strrpos($id, self::SUBUSER_ID_SEPERATOR) !== false)
    	{
    		$ids = explode(self::SUBUSER_ID_SEPERATOR, $id);
    		$userId = $ids[0];
    		$subUserId = $ids[1];

    		$user = static::findOne(['id' => $userId, 'status' => self::STATUS_ACTIVE]);

        	$subUser = self::getSubUserById($subUserId);

        	$user->id = $userId.SubUser::SUBUSER_ID_SEPERATOR.$subUserId;
        	$user->_subuser = $subUser;
        	
        	return $user;
    	}
    	else
    	{
    		$subUser = self::getSubUserByMerchantId($id);
    		if($subUser) 
    		{
    			$user = static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);

				$user->id = $id.SubUser::SUBUSER_ID_SEPERATOR.$subUser->id;
        		$user->_subuser = $subUser;
        	
        		return $user;
    		} 
    		else 
    		{
    			return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    		}
    	}
    }

    public static function createReferrer()
    {
    	if(($identity = Yii::$app->user->identity) && !Yii::$app->user->identity->_subuser) 
    	{
    		$merchant_id = $identity->id;
    		$query = "SELECT `id` FROM `referrer_user` WHERE `merchant_id` = '{$merchant_id}'";
	    	$referraData = Helper::sqlRecords($query, 'one');
	    	if(!$referraData) 
	    	{
	    		$name = $identity->shop_name;
    			$username = $identity->username;
    			$password = self::generatePassword($merchant_id);
    			$status = ReferrerUser::REFERRER_STATUS_APPROVED;

    			$query = "INSERT INTO `referrer_user`(`name`, `username`, `password`, `merchant_id`, `status`) VALUES ('{$name}', '{$username}', '{$password}', '{$merchant_id}', {$status})";
	    		return Helper::sqlRecords($query, null, 'insert');
	    	}
    	}
        return false;
    }

    public static function generatePassword($merchant_id)
    {
        $password = 'password-'.$merchant_id;
    	return self::getEncodedPassword($password);
    }

    public static function createMainReferrarCustomer()
    {
    	$username = self::REFERRER_CUSTOMER_USERNAME;
    	$auth_key = self::REFERRER_CUSTOMER_AUTHKEY;
    	$shop_name = self::REFERRER_CUSTOMER_SHOPNAME;
    	$status = self::REFERRER_CUSTOMER_STATUS;

    	$query = "INSERT INTO `user`(`username`, `auth_key`, `shop_name`, `status`) VALUES ('{$username}', '{$auth_key}', '{$shop_name}', '{$status}')";
	    Helper::sqlRecords($query, null, 'insert');
    }
}
