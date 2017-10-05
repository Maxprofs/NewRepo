<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\modules\referral\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\referral\models\ReferrerPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Referrer Payments';
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-section">
    <div class="form new-section">
        <div class="referrer-payment-index">

            <h3><?= Html::encode($this->title) ?></h3>
            <span><a onclick="refreshPayment()" class="refresh-stats"><i class="fa fa-refresh" aria-hidden="true"></i></a></span>
            
            <?= $this->render('../stats/stats.php') ?>

            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '-'],
                'summary' => '',
                'columns' => [
                    //['class' => 'yii\grid\SerialColumn'],

                    //'id',
                    'payment_id',
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
                                    return "-";
                                }
                            },
                    ],
                    'amount',
                    'type',
                    [
                        'attribute' => 'payment_date',
                        'label' => 'Date',
                        'value' => function ($data) {
                            if (!is_null($data['payment_date'])) {
                                $payment_date = $data['payment_date'];
                                $payment_date = date_create($payment_date);
                                return date_format($payment_date,"F d, Y");
                            } else {
                                return "-";
                            }
                        },
                    ],
                    'app',
                    [
                        'attribute' => 'status',
                        'label' => 'Status',
                        'format' => 'html',
                        'value' => function ($data) {
                                if (isset($data['status'])) {
                                    if($data['status']=='pending' && $data['type'] == 'credit') {
                                        $data['status'] = $data['status']."<span class=\"referral-tooltip\"><i title=\"Status will be 'complete' after 30 days from date of payment.\">i</i></span>";
                                    }
                                    return $data['status'];
                                } else {
                                    return "-";
                                }
                            },
                    ],
                    'comment:ntext',
                    /*[
                        'attribute' => 'plan_type',
                        'label' => 'Plan',
                        'value' => function ($data) {
                            if (isset($data['walmart_recurring_payment']['plan_type'])) {
                                return $data['walmart_recurring_payment']['plan_type'];
                            } else {
                                return '-';
                            }
                        },
                    ],
                    [
                        'attribute' => 'recurring_paymnt_status',
                        'label' => 'Recurring Payment Status',
                        'value' => function ($data) {
                            if (isset($data['walmart_recurring_payment']['status'])) {
                                return $data['walmart_recurring_payment']['status'];
                            } else {
                                return '-';
                            }
                        },
                    ],
                    [
                        'attribute' => 'recurring_data',
                        'label' => 'Recurring Data',
                        'value' => function ($data) {
                            if (isset($data['walmart_recurring_payment']['recurring_data'])) {
                                return $data['walmart_recurring_payment']['recurring_data'];
                            } else {
                                return '-';
                            }
                        },
                    ],*/

                    //['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
            <div class="nav-links">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-center">
                        <a href="<?= Helper::getUrl('stats/index') ?>">History</a> 
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
