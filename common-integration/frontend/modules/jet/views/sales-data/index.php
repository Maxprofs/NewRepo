<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\JetSalesDataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'SKU Sales Data';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-sales-data-index content-section">
<div class="form new-section">
    <div class="jet-pages-heading">
        <div class="title-need-help">            
           <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="product-upload-menu">
            <?=Html::a('Sync Sales Data', yii\helpers\Url::toRoute('updateproduct/sales-data'),['class'=>'btn btn-primary','style'=>'float:right']);?>
            <?=Html::a('Back', \yii\helpers\Url::to(Yii::$app->request->referrer),['class'=>'btn btn-primary','style'=>'float:right']);?>
        </div> 
        <div class="clear"></div> 
    </div>

    <p class="note"><b class="note-text">Note:</b> Analyze how your individual product price (item and shipping price) compares to the lowest individual product prices from the marketplace.</p>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'merchant_id',
            'sku',
            //'sales_data:ntext',
            
            [
                'attribute' => 'price',
                'format' => 'html',
                'label' => 'YOUR PRICE*',
                'value' => function ($data) {
                    $salesData=json_decode($data['sales_data'],true);
                    if(isset($salesData['my_best_offer'][0]['item_price']))
                    {
                        $yourTotal=(float)$salesData['my_best_offer'][0]['item_price']+$salesData['my_best_offer'][0]['shipping_price'];
                        $compTotal=(float)$salesData['best_marketplace_offer'][0]['item_price']+$salesData['best_marketplace_offer'][0]['shipping_price'];
                        if($yourTotal>$compTotal){
                            return '<div class="main_price">'.number_format($yourTotal, 2, '.', '').'</div> ('.number_format($salesData['my_best_offer'][0]['item_price'], 2, '.', '').' + <span class="color_shipping">'.number_format($salesData['my_best_offer'][0]['shipping_price'], 2, '.', '').'</span>)<span class="greater"></span>';
                        }
                        elseif($yourTotal<$compTotal)
                        {
                            return '<div class="main_price">'.number_format($yourTotal, 2, '.', '').'</div> ('.number_format($salesData['my_best_offer'][0]['item_price'], 2, '.', '').' + <span class="color_shipping">'.number_format($salesData['my_best_offer'][0]['shipping_price'], 2, '.', '').'</span>)<span class="lesser"></span>';
                        }
                        else
                        {
                            return '<div class="main_price">'.number_format($yourTotal, 2, '.', '').'</div> ('.number_format($salesData['my_best_offer'][0]['item_price'], 2, '.', '').' + <span class="color_shipping">'.number_format($salesData['my_best_offer'][0]['shipping_price'], 2, '.', '').'</span>)';
                        }
                        
                    }else{
                         return "";
                    }
                },
            ],
            [
                'attribute' => 'price',
                'format' => 'html',
                'label' => "COMPETITOR'S PRICE*",
                'value' => function ($data) {
                    $salesData=json_decode($data['sales_data'],true);
                    if(isset($salesData['best_marketplace_offer'][0]['item_price']))
                    {
                        $yourTotal=(float)$salesData['my_best_offer'][0]['item_price']+$salesData['my_best_offer'][0]['shipping_price'];
                        $compTotal=(float)$salesData['best_marketplace_offer'][0]['item_price']+$salesData['best_marketplace_offer'][0]['shipping_price'];
                        if($yourTotal<$compTotal)
                        {
                            return '<div class="main_price">'.number_format($compTotal, 2, '.', '').'</div> ('.number_format($salesData['best_marketplace_offer'][0]['item_price'], 2, '.', '').' + <span class="color_shipping">'.number_format($salesData['best_marketplace_offer'][0]['shipping_price'], 2, '.', '').'</span>)<span class="greater"></span>';
                        }elseif($yourTotal>$compTotal){
                            return '<div class="main_price">'.number_format($compTotal, 2, '.', '').'</div> ('.number_format($salesData['best_marketplace_offer'][0]['item_price'], 2, '.', '').' + <span class="color_shipping">'.number_format($salesData['best_marketplace_offer'][0]['shipping_price'], 2, '.', '').'</span>)<span class="lesser"></span>';
                        }else{
                            return '<div class="main_price">'.number_format($compTotal, 2, '.', '').'</div> ('.number_format($salesData['best_marketplace_offer'][0]['item_price'], 2, '.', '').' + <span class="color_shipping">'.number_format($salesData['best_marketplace_offer'][0]['shipping_price'], 2, '.', '').'</span>)';
                        }    
                    }else{
                         return "";
                    }
                },
            ],
            [
                'attribute' => 'rank',
                'format' => 'html',
                'label' => "RANK",
                'value' => function ($data) {
                    $salesData=json_decode($data['sales_data'],true);
                    if(isset($salesData['sales_rank']['level_0']))
                    {
                        return $salesData['sales_rank']['level_0'];
                    }else{
                         return "";
                    }
                },
            ],
            [
                'attribute' => 'rank',
                'format' => 'html',
                'label' => "UNITS SOLD**",
                'value' => function ($data) {
                    $salesData=json_decode($data['sales_data'],true);
                    if(isset($salesData['units_sold']['last_30_days']))
                    {
                        if($salesData['units_sold']['last_30_days'])
                            return $salesData['units_sold']['last_30_days'];
                    }
                    return 0;
                },
            ],
            //'updated_at',
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    <p class="note">*YOUR PRICE and COMPETITOR'S PRICE include item price and shipping price.<br>**UNITS SOLD Units of SKU sold on Jet by all retailers over the last 30 days, updated daily.</p>
</div>    
</div>