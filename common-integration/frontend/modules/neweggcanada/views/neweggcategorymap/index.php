<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\neweggcanada\models\neweggcanadategoryMapSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Newegg Category Maps';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="newegg-category-map-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Newegg Category Map', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'merchant_id',
            'product_type',
            'category_id',
            'category_name',
            // 'category_path:ntext',
            // 'tax_code',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
