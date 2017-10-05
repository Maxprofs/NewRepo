<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\neweggcanada\models\NeweggOrderImportErrorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Newegg Order Import Errors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="newegg-order-import-error-index content-section">

    <div class="form new-section">

        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet" title="Need Help" target="_blank"
                   href="<?= Yii::$app->request->baseUrl; ?>/newegg-marketplace/sell-on-newegg#sec6-3"></a>
            </div>

            <div class="clear"></div>
        </div>

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

//                'id',
                'order_number',
//                'merchant_id',
                'error_reason:ntext',
                'created_at',
                // 'newegg_item_number',

                /*['class' => 'yii\grid\ActionColumn'],*/
            ],
        ]); ?>
    </div>
</div>
