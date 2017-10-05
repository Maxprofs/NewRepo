<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PricingPlanSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Pricing Plans');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pricing-plan-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Pricing Plan'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'plan_name',
            'plan_type',
            'duration',
            'plan_status',
            // 'base_price',
            // 'special_price',
            // 'apply_on',
            // 'additional_condition',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
