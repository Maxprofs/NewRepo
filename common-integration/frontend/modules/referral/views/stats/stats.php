<?php 
use frontend\modules\referral\components\Dashboard;
use frontend\modules\referral\components\Helper;

$totalAmount = Dashboard::getTotalAmount();
$activeAmount = Dashboard::getActiveAmount();
$referralCount = Dashboard::getReferralCount();
$redeemedAmount = Dashboard::getRedeemedAmount();

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
?>

<div class="amount redeem-panel">
<?php if($controller == 'redeem' && $action == 'choose-method') { ?>
	<a class="back-btn" href="<?= Helper::getUrl('redeem/choose-payment') ?>">Back</a>
<?php } ?>
    <span class="total">Total Amount : $<?= $totalAmount ?></span>
    <span class="usable">Usable Amount : $<?= $activeAmount ?></span>
    <!-- <span class="non-usable">No of Referrals  : <?= $referralCount ?></span> -->
    <span class="redeemed">Redeemed Amount : $<?= $redeemedAmount ?></span>
</div>