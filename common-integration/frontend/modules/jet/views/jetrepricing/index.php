<?php

use yii\helpers\Html;
use yii\grid\GridView;
$urlJetPrice = \yii\helpers\Url::toRoute(['jetproduct/getskudetails']);
/* @var $this yii\web\View */
/* @var $searchModel frontend\models\JetDynamicPriceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Jet Repricing';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-dynamic-price-index content-section">
    <div class="row form new-section">
    
    <?= Html::a('Reset Filter', ['jetdynamicprice/index'], [ 'data-toggle'=>'tooltip','title'=>'Click to reset all filters','data-position'=>'top','class' => 'btn btn-primary','style'=>'float:right']) ?>
    
    <div class="title-need-help">
    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
    </div>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'product_id',
            [
                'attribute'=> 'sku',
                'label'=>'SKU',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Stock keeping unit'],
            ],
            [
                'attribute'=> 'min_price',
                'label'=>'Min Price',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Min price of product'],
            ],
            [
                'attribute'=> 'current_price',
                'label'=>'Current Price',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Product current Price on app'],
            ],
            [
                'attribute'=> 'max_price',
                'label'=>'Max Price',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Maximum price of product on jet'],
            ],
            [
                'attribute'=> 'bid_price',
                'label'=>'Bid Price',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Products bidding amount'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header'=>'Action','headerOptions' => ['width' => '80'],
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Choose action to view/update details'],
                'template' => '{view}{update}{link}',
                'buttons' => [
                    'view' => function ($url,$model)
                    {
                        $options = ['data-pjax'=>0,'onclick'=>'clickPricing("'.$model->sku.'")','title'=>'Get Price Details',];
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>',
                            'javascript:void(0)',$options
                        );
                    },                
                ],
          ],
        ],
    ]); ?>
    </div>
</div>
<div id="jet_dynamic_pricing" style="display:none">
    <div class="container">    
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Product
                        Information on Jet</h4>
                    </div>
                    <div class="modal-body" id="skudetailsonjet">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var csrfToken = $('meta[name="csrf-token"]').attr("content");
function clickPricing(sku)
{
    var url='<?= $urlJetPrice; ?>';
    $('#LoadingMSG').show();
    $.ajax({
      method: "post",
      url: url,
      data: {sku:sku,_csrf : csrfToken}
    })
    .done(function(msg)
    {
       $('#LoadingMSG').hide();
       $('#skudetailsonjet').html(msg);
       $('#jet_dynamic_pricing').css("display","block");      
       $('#jet_dynamic_pricing #myModal').modal({
           keyboard: false,
           backdrop: 'static'
       });
    });
}
</script>