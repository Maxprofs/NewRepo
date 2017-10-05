<?php
namespace frontend\modules\referral\components;

use Yii;
use yii\base\Component;
use frontend\modules\referral\models\ReferrerPayment;

class Dashboard extends Component
{
    static $_totalAmount = null;
    static $_activeAmount = null;
    static $_referralCount = null;
    static $_redeemedAmount = null;

    public static function getReferrerId()
    {
        if(!Yii::$app->user->isGuest && Yii::$app->user->identity->_subuser) {
            $referrer_id = Yii::$app->user->identity->_subuser->id;
            return $referrer_id;
        } 
        else {
            return false;
        }
    }

    public static function getReferrerLink()
    {
        if(!Yii::$app->user->isGuest && Yii::$app->user->identity->_subuser) {
            $refUserId = Yii::$app->user->identity->_subuser->id;
            $code = self::getReferrerCode($refUserId);

            $walmartRefUrl = Yii::getAlias('@webbaseurl').'/walmart/site/login?ref='.$code;
            $jetRefUrl = Yii::getAlias('@webbaseurl').'/jet/site/login?ref='.$code;
            $neweggRefUrl = Yii::getAlias('@webbaseurl').'/neweggmarketplace/site/login?ref='.$code;

            return ['jet'=>$jetRefUrl, 'walmart'=>$walmartRefUrl, 'newegg'=>$neweggRefUrl];
        } 
        else {
            return [];
        }
    }

    public static function getReferrerCode($refUserId)
    {
        $query = "SELECT `id`, `code` FROM `referrer_user` WHERE `id`='{$refUserId}'";
        $result = Helper::sqlRecords($query, 'one');
        if($result && empty($result['code']))
        {
            $string = 'referrer'.$refUserId;
            $code = md5($string);

            $query = "UPDATE `referrer_user` SET `code`='{$code}' WHERE `id`='{$refUserId}'";
            Helper::sqlRecords($query, null, 'update');
        }
        else
        {
            $code = $result['code'];
        }
        return $code;
    }

    public static function getActiveAmount()
    {
        if(is_null(self::$_activeAmount))
        {
            $amount = '0.00';

            $referrer_id = self::getReferrerId();
            if($referrer_id)
            {
                $status = ReferrerPayment::PAYMENT_STATUS_COMPLETE;
                $query = "SELECT SUM(`amount`) as `amount` FROM `referrer_payment` WHERE `referrer_id`='{$referrer_id}' AND `status`='{$status}' AND `type` LIKE 'credit'";
                $result = Helper::sqlRecords($query, 'one');
                if(!is_null($result['amount']))
                    $amount = $result['amount'];
            }
            self::$_activeAmount = $amount;
        }
        return self::$_activeAmount;
    }

    public static function getTotalAmount()
    {
        if(is_null(self::$_totalAmount))
        {
            $amount = '0.00';

            $referrer_id = self::getReferrerId();
            if($referrer_id)
            {
                $status = ReferrerPayment::PAYMENT_STATUS_COMPLETE;
                $query = "SELECT SUM(`amount`) as `amount` FROM `referrer_payment` WHERE `referrer_id`='{$referrer_id}' AND `type` LIKE 'credit'";
                $result = Helper::sqlRecords($query, 'one');
                if(!is_null($result['amount']))
                    $amount = $result['amount'];
            }
            self::$_totalAmount = $amount;
        }
        return self::$_totalAmount;
    }

    public static function getReferralCount()
    {
        if(is_null(self::$_referralCount))
        {
            $amount = '0';

            $referrer_id = self::getReferrerId();
            if($referrer_id)
            {
                $status = ReferrerPayment::PAYMENT_STATUS_COMPLETE;
                $query = "SELECT COUNT(`id`) as `referrals` FROM `referrer_payment` WHERE `referrer_id`='{$referrer_id}' AND `status`='{$status}' AND `type` LIKE 'credit'";
                $result = Helper::sqlRecords($query, 'one');
                if(!is_null($result['referrals']))
                    $amount = $result['referrals'];
            }
            self::$_referralCount = $amount;
        }
        return self::$_referralCount;
    }

    public static function getRedeemedAmount()
    {
        if(is_null(self::$_redeemedAmount))
        {
            $amount = '0.00';

            $referrer_id = self::getReferrerId();
            if($referrer_id)
            {
                $status = ReferrerPayment::PAYMENT_STATUS_REDEEMED;
                $query = "SELECT SUM(`amount`) as `amount` FROM `referrer_payment` WHERE `referrer_id`='{$referrer_id}' AND `status`='{$status}' AND `type` LIKE 'credit'";
                $result = Helper::sqlRecords($query, 'one');
                if(!is_null($result['amount']))
                    $amount = $result['amount'];
            }
            self::$_redeemedAmount = $amount;
        }
        return self::$_redeemedAmount;
    }
}

?>
