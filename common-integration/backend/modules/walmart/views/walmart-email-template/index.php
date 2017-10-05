<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\reports\models\WalmartEmailTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Walmart Email Templates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="walmart-email-template-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Walmart Email Template', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'template_title',
            'template_path',
            'custom_title',
               ['attribute'=>'show_on_admin_setting',
             'filter'=>array("1"=>"Yes","0"=>"No"),

            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
