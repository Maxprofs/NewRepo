<?php
namespace frontend\modules\referral\components;

use Yii;
use yii\helpers\Url;
use yii\base\Component;
use yii\web\UnauthorizedHttpException;
use frontend\modules\referral\models\SubUser;
use frontend\modules\referral\models\ReferrerUser;

class Helper extends component
{
    const MIN_REDEEM_AMOUNT  = 200;
    const MIN_REFERRALS = 10;

    public static function sqlRecords($query, $type = null, $queryType = null)
    {
        $connection=Yii::$app->getDb();
        $response=[];
        if($queryType=="update" || $queryType=="delete" || $queryType=="insert" || ($queryType==null && $type==null)) {
            $response = $connection->createCommand($query)->execute();
        }
        elseif($type=='one') {
            $response = $connection->createCommand($query)->queryOne();
        }
        elseif($type=='column') {
            $response = $connection->createCommand($query)->queryColumn();
        }
        else {
            $response = $connection->createCommand($query)->queryAll();
        }
        unset($connection);
        return $response;
    }

    public static function getUrl($path)
    {
        $url = Url::toRoute(['/referral/' . $path]);
        return $url;
    }

    public static function isAuthorised()
    {
        if($identity = Yii::$app->user->identity) {
            $subuser = $identity->_subuser;
            if($subuser && $subuser->id) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function checkAccountStatus()
    {
        if($identity = Yii::$app->user->identity) {
            $status = $identity->_subuser->status;
            if($status==ReferrerUser::REFERRER_STATUS_APPROVED) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getCurrentReferrerId()
    {
        if($identity = Yii::$app->user->identity) {
            return $identity->_subuser->id;
        } else {
            return false;
        }
    }

    public static function isReferrerUserLoggedIn()
    {
        if (!Yii::$app->user->isGuest) {
            $userName = Yii::$app->user->identity->username;
           // $shopName = Yii::$app->user->identity->shop_name;
            if($userName==SubUser::REFERRER_CUSTOMER_USERNAME /*&& $shopName==SubUser::REFERRER_CUSTOMER_SHOPNAME*/) {
                return true;
            }
        }
        return false;
    }

    public static function beforeActionEvent($event)
    {
        $allowedModulesForReferrerUser = ['referral'];

        $moduleName = $event->sender->controller->module->id;
        //$controller = $event->sender->controller->id;
        //$action = $event->sender->controller->action->id;

        if(!in_array($moduleName, $allowedModulesForReferrerUser) && self::isReferrerUserLoggedIn())
        {
            Yii::$app->session->setFlash('error', 'Not Authorized Action');
            Yii::$app->response->redirect(['referral/account/login']);
            //throw new UnauthorizedHttpException('Not Authorized Action.');
            Yii::$app->end();
        }
    }

    public static function updatePaymentStatus($forceUpdate=false)
    {
        $session = Yii::$app->session;

        $referrer_id = self::getCurrentReferrerId();

        if($referrer_id)
        {
            $indexName = 'payment_status_updated_'.$referrer_id;

            $paymentStatus = $session->get($indexName);

            if(!$paymentStatus || $forceUpdate)
            {
                $query = "UPDATE `referrer_payment` SET `status` = 'complete' WHERE `referrer_id` = '{$referrer_id}' AND DATE_ADD(payment_date,INTERVAL 30 DAY) < NOW() AND `status` = 'pending'";
                self::sqlRecords($query, null, 'update');

                $session->set($indexName, true);
            }
        }
    }

    public static function canRedeem()
    {
        //return true;
        $referrer_id = self::getCurrentReferrerId();
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
}

?>
