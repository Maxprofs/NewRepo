<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\jet\components\Data;
/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\jet\models\ProductsListedOnJetSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$merchant_id = MERCHANT_ID ;
$urlJet= \yii\helpers\Url::toRoute(['jetproduct/getjetdata']);

$this->title = 'Listing On Jet';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="products-listed-on-jet-index content-section">
<div class="row form new-section">    
    <div class="jet-pages-heading ">
        <div class="title-need-help">
         <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>       
        </div>      
        <div class="product-upload-menu">  
        
            <?php  
            $bulkActionSelect = Html::dropDownList('bulk_name', null, [''=>'-- select bulk action --','export'=>'Export selected sku(s) Csv'], ['id'=>'jet_sku_select','class'=>'form-control']);
            $bulkActionSubmit = Html::Button('Submit', ['class'=>'btn btn-primary','value'=>'submit','onclick'=>'validateBulkCsvAction()','action'=>'export_selected_sku']);
            ?>     
                             
            <?=Html::a('Sync Listing', yii\helpers\Url::toRoute('jetproductslist/syncdata'),['class'=>'btn btn-primary','style'=>'float:right','title'=>"Click to sync products from jet"]);?>
            <?=Html::a('Products from other api provider', yii\helpers\Url::toRoute('updateproduct/export-missing-jet-product'),['class'=>'btn btn-primary','style'=>'float:right','title'=>"Products from other api provider"]);?>
            <?=Html::a('Export all products', yii\helpers\Url::toRoute('updateproduct/export-jet-csv'),['class'=>'btn btn-primary','style'=>'float:right','title'=>"Export all products"]);?>
        </div>
        <div class="clear"></div>
    </div>
    <?=Html::beginForm(['jetproductslist/export'],'post',['id'=>'jet_bulk_sku_product'/*,'onsubmit'=>'return validateBulkCsvAction()'*/]);?> 
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= GridView::widget([
        'id'=>"product_list_grid",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
        'summary'=>'<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><div class="bulk-action-wrapper">'.$bulkActionSelect.$bulkActionSubmit.'</div></div></div>',
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function($data) 
                {
                    return ['value' => $data['id'],'class'=>'bulk_checkbox','headerOptions'=>['id'=>'checkbox_header','title'=>'Select Sku(s) to export csv' ]];
                },               
            ],
            [
                'attribute'=> 'sku',
                'label'=>'Product SKU',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip','title'=>'Stock keeping unit'],
            ], 
            /* [
                'attribute'=> 'title',
                'label'=>'Product Title',
                'headerOptions' => ['width' => '250','data-toggle'=>'tooltip','title'=>'Product title on jet'],
            ], */
            [
                'attribute'=>'status',
                'label'=>'Status',
                'headerOptions' => ['width' => '200','data-toggle'=>'tooltip1','title'=>'Product Status On jet'],
                'filter' => [Data::AVAILABLE_FOR_PURCHASE=>Data::AVAILABLE_FOR_PURCHASE,Data::EXCLUDED=>Data::EXCLUDED,Data::MISSING_LISTING_DATA=>Data::MISSING_LISTING_DATA,Data::UNDER_JET_REVIEW=>Data::UNDER_JET_REVIEW,Data::ARCHIVED=>Data::ARCHIVED,Data::NOT_UPLOADED=>Data::NOT_UPLOADED,Data::UNAUTHORIZED=>Data::UNAUTHORIZED],
                'value' => function($data){                 
                    return $data['status'];
                },
            ],
            /* [
                'attribute'=>'has_inv',
                'label'=>'Has Qty on Jet',
                'headerOptions' => ['width' => '20','data-toggle'=>'tooltip1','title'=>'Does product have qty on jet'],
                'filter' => ["Yes"=>"Yes","No"=>"No"],                
                'value' => function($data){                 
                    return $data['has_inv'];
                },
            ], */

            [
                'class' => 'yii\grid\ActionColumn',
                'header'=>'Action','headerOptions' => ['width' => '80'],
                    'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Choose action to view details'],
                'template' => '{view}{link}',
                'buttons' => [
                    'view' => function ($url,$model) use (&$viewActionFlag)
                    {
                        $options = ['data-pjax'=>0,'onclick'=>'clickView(this.id)','title'=>'View Product Detail','id'=>$model->sku];
                        if(!$viewActionFlag) {
                            $viewActionFlag = true;
                            $options['data-step']='6';
                            $options['data-intro']="View Product Information.";
                            $options['data-position']='left';
                        }
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
                            'javascript:void(0)',$options
                        );
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    <?=Html::endForm();?>         
 </div>
</div>
<div id="view_jet_product" style="display:none"></div>
<script type="text/javascript">
    $('#reset_bulk').click(function() 
    {
        var option = confirm("Do you want to reset the Bulk Action");
        if (option == true) 
          location.reload();
        else 
          return false;    
    });
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    function clickView(id)
    {
        var url='<?= $urlJet ?>';
        var merchant_id='<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {id:id,merchant_id : merchant_id,_csrf : csrfToken }
        })
            .done(function(msg) {
                $('#LoadingMSG').hide();
                $('#view_jet_product').html(msg);
                $('#view_jet_product').css("display","block");
                $('#view_jet_product #myModal').modal('show');
            });
    }
    function validateBulkCsvAction(){
       var action = $('#jet_sku_select').val();
        if(action == '') {
            alert('Please select bulk action');
            //return false;
        }
        else
        {
            if($("input:checkbox:checked.bulk_checkbox").length == 0)
            {
                alert('Please select products before submit');
                //return false;
            }
            else
            {
                $("#jet_bulk_sku_product").submit();
                //return true;
            }
        } 
    }
</script>