<?php
namespace frontend\modules\referral\components;

use Yii;
use yii\base\Component;

class Redeem extends component
{
    const MIN_REDEEM_AMOUNT = 200;
    const MIN_REFERRALS = 10;
    
    public static function canRedeem()
    {
        //return true;
        $referrer_id = Helper::getCurrentReferrerId();
        if($referrer_id)
        {
            $activeAmount = floatval(Dashboard::getActiveAmount());
            $referralCount = intval(Dashboard::getReferralCount());

            if($activeAmount >= self::MIN_REDEEM_AMOUNT || $referralCount >= self::MIN_REFERRALS) {
                return true;
            }
        }
        return false;
    }

    public static function getPaymentOptions()
    {
        $paymentOptions = [
                            [
                                'code' => 'paypal',
                                'title' => 'Transfer to <img src="/integration/frontend/modules/referral/assets/images/paypal-logo-preview.png">'
                            ],
                            [
                                'code' => 'subscription',
                                'title' => 'Get subscription'
                            ]
                        ];

        return $paymentOptions;
    }

    public static function caclulatePaymentAmountFromIds($ids)
    {
        $amount = '0.00';

        $referrer_id = Dashboard::getReferrerId();

        if($ids != '')
        {
            $query = "SELECT SUM(`amount`) as `amount` FROM `referrer_payment` WHERE `referrer_id`='{$referrer_id}' AND `type` LIKE 'credit' AND `id` IN ({$ids})";
            $result = Helper::sqlRecords($query, 'one');
            
            if(!is_null($result['amount']))
                $amount = $result['amount'];
        }

        return $amount;
    }

    public static function isAppInstalled($marketplace, $shopurl)
    {
        $return = false;

        switch ($marketplace) {
            case 'jet':
                $query = "SELECT `jet_shop_details`.`install_status` FROM `user` INNER JOIN `jet_shop_details` ON `user`.`id`=`jet_shop_details`.`merchant_id` WHERE `jet_shop_details`.`shop_url` LIKE '{$shopurl}'";
                $result = Helper::sqlRecords($query, 'one');
                if($result && $result['install_status']) {
                    $return = true;
                }
                break;

            case 'walmart':
                $query = "SELECT `walmart_shop_details`.`status` FROM `user` INNER JOIN `walmart_shop_details` ON `user`.`id`=`walmart_shop_details`.`merchant_id` WHERE `walmart_shop_details`.`shop_url` LIKE '{$shopurl}'";
                $result = Helper::sqlRecords($query, 'one');
                if($result && $result['status']) {
                    $return = true;
                }
                break;

            case 'newegg':
                $query = "SELECT `newegg_shop_detail`.`install_status` FROM `user` INNER JOIN `newegg_shop_detail` ON `user`.`id`=`newegg_shop_detail`.`merchant_id` WHERE `newegg_shop_detail`.`shop_url` LIKE '{$shopurl}'";
                $result = Helper::sqlRecords($query, 'one');
                if($result && $result['install_status']) {
                    $return = true;
                }
                break;
        }
        return $return;
    }

    public static function getPaymentFormAction()
    {
        return Helper::getUrl('redeem/choose-method');
    }

    public static function getMethodFormAction()
    {
        return Helper::getUrl('redeem/post');
    }
}

?>
