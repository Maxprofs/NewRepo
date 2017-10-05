<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\JetShopDetailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Jet Shop Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-shop-details-index">

    <h1><?= Html::encode($this->title) ?></h1>
   
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            'merchant_id',
            'shop_url:url',
            'shop_name',
            'email:email',
            // 'country_code',
            // 'currency',
            // 'install_status',
            // 'installed_on',
            // 'expired_on',
            // 'purchased_on',
            // 'purchase_status',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
