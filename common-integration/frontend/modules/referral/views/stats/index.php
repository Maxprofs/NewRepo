<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\modules\referral\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\referral\models\ReferralUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Referral History';
//$this->params['breadcrumbs'][] = $this->title;

?>

<div class="content-section">
    <div class="form new-section">
        <div class="referral-user-index">

            <h3><?= Html::encode($this->title) ?></h3>
            <span><a onclick="refreshPayment()" class="refresh-stats"><i class="fa fa-refresh" aria-hidden="true"></i></a></span>
            
            <?= $this->render('../stats/stats.php') ?>

            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'summary' => '',
                'columns' => [
                    //['class' => 'yii\grid\SerialColumn'],

                    'id',
                    /*[
                        'attribute' => 'username',
                        'label' => 'Store Url',
                        'value' => 'user.username',
                    ],*/
                    [
                        'attribute' => 'shop_name',
                        'label' => 'Store Name',
                        'format' => 'html',
                        'value' => function ($data) {
                                if (isset($data['user']['username']) && isset($data['user']['shop_name'])) {
                                    return '<a title="'.$data['user']['shop_name'].'" href="http://'.$data['user']['username'].'" target="_blank">'.$data['user']['shop_name'].'</a>';
                                } else {
                                    return "";
                                }
                            },
                    ],
                    'app',
                    [
                        'attribute' => 'installation_date',
                        'label' => 'Installation Date',
                        'value' => function ($data) {
                            if (!is_null($data['installation_date'])) {
                                $installation_date = $data['installation_date'];
                                $installation_date = date_create($installation_date);
                                return date_format($installation_date,"F d, Y");
                            } else {
                                return "Hasn't Installed Yet";
                            }
                        },
                    ],
                    [
                        'attribute' => 'payment_date',
                        'label' => 'Payment Date',
                        'value' => function ($data) {
                            if (!is_null($data['payment_date'])) {
                                $payment_date = $data['payment_date'];
                                $payment_date = date_create($payment_date);
                                return date_format($payment_date,"F d, Y");
                            } else {
                                return "Hasn't Paid Yet";
                            }
                        },
                    ],
                    
                    //['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>

            <div class="nav-links">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-center">
                        <a href="<?= Helper::getUrl('payment/index') ?>">All Transactions</a> 
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-center">
                        <a href="<?= Helper::getUrl('redeem/choose-payment') ?>">Request Redeem</a>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script type="text/javascript">
    function refreshPayment()
    {
        window.location = "<?= Helper::getUrl('payment/refresh') ?>";
    }
</script>
