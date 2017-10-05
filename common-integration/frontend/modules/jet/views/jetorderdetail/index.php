<?php

use frontend\modules\jet\components\Data;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Sales Orders';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = MERCHANT_ID;

$flag=0;

$urlShipment= Yii::getAlias('@webjeturl').'/jetorderdetail/viewshipmentdetail';
$cancelJetOrderPopup= Yii::getAlias('@webjeturl').'/jetorderdetail/cancelorderskupopup';
$JetshipmentpartialPopup= Yii::getAlias('@webjeturl').'/jetorderdetail/shipmentpartial';
$viewJetOrderDetails = Yii::getAlias('@webjeturl').'/jetorderdetail/vieworderdetails';
?>

<style type="text/css">
  .skuData{
    text-align: center;
  }
  .table.table-striped.table-bordered tr th {
      font-size: 14px;
  }
  .modal-content {
    border-radius: 16px;
  }
</style>

<div class="jet-order-detail-index content-section">
<div class="form new-section">
<?=Html::beginForm(['jetorderdetail/bulk'],'post',['id'=>'jet_bulk_order']);?>
   <div class="jet-pages-heading">
      <div class="title-need-help">
        <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
      </div>
      <div class="product-upload-menu confirmbox">
        <div class="jet-upload-submit">
          <?php       
            $arrAction=[''=>'Select action','exportcsv'=>'Export CSV'];      
          ?>
          <?php $bulkActionSelect = Html::dropDownList('action','',$arrAction,['class'=>'form-control','id'=>'jet_order_select','data-step'=>'8','data-intro'=>"Select action want to be performed on the Selected Row(s)/Order(s).",'data-position'=>'left'])?>
          <?php $bulkActionSubmit = Html::submitButton('submit', ['class' => 'btn btn-primary', 'id'=>'help-bulk-submit','data-step'=>'9','data-intro'=>"Submit to Perform Selected Action on Selected Order(s).",'data-position'=>'left']);?>
        </div>
        <a class="btn btn-primary" <?php if($countReadyOrders==0){echo "disabled=disabled";}?> data-step='1' data-intro="Fetch Ready state orders from Jet" data-position="left" href="<?= Yii::getAlias('@webjeturl'); ?>/jetorderdetail/create">Fetch Orders<?php if($countReadyOrders>0){?><span class="sync_notication"><?= $countReadyOrders;?></span><?php }?></a>
        <a class="btn btn-primary" <?php if($countOrders==0){echo "disabled=disabled";}?> data-step='2' data-intro="Your Store not contains Order(s) that Listed here, Click to Manually Sync Jet Order(s) to Store." data-position="left" href="<?= Yii::getAlias('@webjeturl'); ?>/jetorderdetail/syncorder">Sync Orders<?php if($countOrders>0){?><span class="sync_notication"><?php echo $countOrders;?></span><?php }?></a>
        <?= Html::a('Reset Filter', ['jetorderdetail/index'], ['data-position'=>'top','class' => 'btn btn-primary','data-step'=>'3', 'data-position'=>'bottom', 'data-intro'=>'Click to reset all filters']) ?>
        <span class="pop_up" id="view_more_options" data-step="4" data-position="bottom" data-intro="Click on view more to sync orders"><i class="fa fa-bars" aria-hidden="true"></i></span>

        <div class="popup-box confirmbox" style="display: none;">
          <a class="btn btn-primary" data-toggle="tooltip" data-step="5" data-position="bottom" data-intro="Click to fetch acknowledged orders" title="Click to fetch acknowledged orders (if order is acknowledged on jet but not listed in app)" href="<?= Yii::getAlias('@webjeturl'); ?>/jetorderdetail/fetchackorder">Fetch Acknowledged Orders</a>
          <a class="btn btn-primary" data-step='6' data-intro="Click to fulfill all orders that are fulfilled from store but not on jet.com" data-position="left"  data-toggle="tooltip" title="Click to fulfill all orders that are fulfilled from store but not on jet.com" href="<?= Yii::getAlias('@webjeturl'); ?>/jetcron/shiporder">Sync Fulfilled Orders</a>
        </div>         
      </div> 
      <div class="clear"></div> 
    </div>
    
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <div class="responsive-table-wrap">
    <?= GridView::widget([
      'id'=>"order_grid",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
        'pager' => [
          'maxButtonCount'=>5,    // Set maximum number of page buttons that can be displayed
        ],
        'summary'=>'<div class="summary clearfix"><div class="col-lg-6 col-md-6 col-sm-12 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-6 col-md-6 col-sm-12 col-xs-12"><div class="row"><div class="bulk-action-wrapper">'.$bulkActionSelect.$bulkActionSubmit.'<span class="help_jet white-bg" title="Need Help" id="bulk-action-help" style="cursor:pointer;"></span></div></div></div></div>',
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn','headerOptions'=>['id'=>'checkbox_header','data-step'=>'7','data-intro'=>"Click here to Check All Rows/Orders or Check Single Row/Order  by clicking against the Row/Order.",'data-position'=>'right'],
              'checkboxOptions' => function($data)
              {
                return ['value' => $data['merchant_order_id']];
              },
            ],                     
            [
              'attribute'=>'reference_order_id',
              'label'=>'Merchant Order',
              'headerOptions' => ['width' => '50'],
            ],                
            [
              'label'=>'Order Name (Shopify)',
              'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Shopify Order Name'],
              'attribute'=>'shopify_order_name',
              'format'=>'raw',
              'value' => function($data)
              {                
                $url = "https://".SHOP."/admin/orders/".$data->shopify_order_id;
                return Html::a($data->shopify_order_name, $url, ['title' => 'Shopify Order Name','target'=>'_blank']);
              }
            ],
            [
              'attribute'=>'shopify_order_id',
              //'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Shopify Order Id'],
            ],
            [
              'label'=>'Merchant Sku(s)',
              'attribute'=>'merchant_sku',
              //'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Shopify Product SKU(S)'],
              'format'=>'raw',
              'value' => function($data){                 
                return implode('<br>',explode(',',$data->merchant_sku));
              }
            ],
            [
              'attribute'=>'deliver_by',
              //'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Last date of order delivery'],
              'format'=>'datetime',//date,datetime, time
              'contentOptions'=>['style'=>'width: 250px;'],
            ],
            [
              'attribute'=> 'status',
              //'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Order status on jet'],
              'contentOptions'=>['style'=>'width: 250px;'],
              'filter' => ["acknowledged"=>"acknowledged","inprogress"=>"inprogress","complete"=>"complete"],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
            	'header'=>'ACTION','headerOptions' => ['width' => '80'],	
                'template' => '{vieworder}{shipment}{cancelack}{partialshipment}{link}',
                'buttons' => [
                  'vieworder' => function ($url,$model) 
                  {
                    return Html::a(
                      '<span class="glyphicon glyphicon-eye-open"> </span>',
                      'javascript:void(0)', ['title'=>'Click to view Order detail', 'data-pjax'=>0, 'onclick'=>'checkorderstatus(this.id)', 'id'=>$model->merchant_order_id]);                         
                  },
                  'shipment' => function ($url,$model) 
                  {
                    if($model->status=='acknowledged' || $model->status=='inprogress')
                    {
                      return Html::a(
                        '<span class="fa fa-truck" style="font-size: 17px;"> </span>', 
                        $url, ['title' => 'Ship Current Order', 'data-pjax'=>0]);
                    }
                  },
                  /*'partialshipment' => function ($url,$model) 
                  {
                    if(($model->status=='inprogress'))
                    {
                      return Html::a(
                        '<span class="fa fa-truck" style="font-size: 17px;"> </span>', 
                        'javascript:void(0)', ['title'=>'Partial ship order on Jet', 'data-pjax'=>0, 'onclick'=>'showpartialpopup("'.$model->merchant_order_id.'")']);
                    }
                  },*/
                  'cancelack' => function ($url,$model) 
                  {
                    if(($model->status=='acknowledged') || ($model->status=='inprogress' ))
                    {
                      return Html::a(
                        '<span class="glyphicon glyphicon-remove-sign"></span>',
                        'javascript:void(0)', ['title'=>'Cancel Acknowledged Order on Jet', 'data-pjax'=>0, 'onclick'=>'showcancelpopup("'.$model->id.'")']);
                    }
                  },                  
                ],
            ],
        ],
    ]); ?>
    </div>
    <?php Pjax::end(); ?>
  </div>
</div>
<div id="partial_jet_order_sku_shipment" style="display: none;"></div>
<div id="view_jet_order" style="display:none"></div>
<div id="ack_order_cancel" style="display:none"></div>
<style>
  .jet-order-detail-index td,th{
    text-align:center;
  }
</style>
<script type="text/javascript">

$('#reset_bulk').click(function() 
{
  var option = confirm("Do you want to reset the Bulk Action");
  if (option == true) 
    location.reload();
  else 
    return false;    
});
$(document).ready(function()
{
  $(".pop_up").click(function () {
    $(".popup-box").toggle("slow");
  });

  var submit_form = false;
  $('body').on('keyup','.filters > td > input', function(event)
  {
    if (event.keyCode == 13)
    {
     if(submit_form === false)
     {
        submit_form = true;
        $("#order_grid-filters").yiiGridView("applyFilter");
     }
    }
  });
  $("body").on('beforeFilter', "#order_grid-filters" , function(event) {
     return submit_form;
  });
  $("body").on('afterFilter', "#order_grid-filters" , function(event) {
    submit_form = false;
  });
}); 

var csrfToken = $('meta[name="csrf-token"]').attr("content");
function showcancelpopup(id) 
{
  var url='<?= $cancelJetOrderPopup ?>';      
  $('#LoadingMSG').show();
    $.ajax({
      method: "post",
      url: url,
      data: {id:id,_csrf : csrfToken }
  })
  .done(function(msg) {        
   $('#LoadingMSG').hide();
   $('#ack_order_cancel').html(msg);
   $('#ack_order_cancel').show();
   $('#ack_order_cancel #myModal').modal('show');
  });
}
function showpartialpopup(merchant_order_id) 
{
  var url='<?= $JetshipmentpartialPopup ?>';      
  $('#LoadingMSG').show();
    $.ajax({
      method: "post",
      url: url,
      data: {merchant_order_id:merchant_order_id,_csrf : csrfToken }
  })
  .done(function(msg) {        
   $('#LoadingMSG').hide();
   $('#partial_jet_order_sku_shipment').html(msg);
   $('#partial_jet_order_sku_shipment').show();
   $('#partial_jet_order_sku_shipment #myModal').modal('show');
  });
}

function checkorderstatus(merchant_order_id) 
{
  var url = '<?= $viewJetOrderDetails; ?>';
  var merchant_id = '<?= $merchant_id;?>';
  $('#LoadingMSG').show();
  $.ajax({
    method: "post",
    url: url,
    data: {merchant_id: merchant_id,merchant_order_id: merchant_order_id, _csrf: csrfToken}
  })
  .done(function (msg) 
  {
      
      $('#LoadingMSG').hide();
      $('#view_jet_order').html(msg);
      $('#view_jet_order').css("display", "block");
      $('#view_jet_order #myModal').modal({
          keyboard: false,
          backdrop: 'static'
      })
  })
}

$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
  $('[data-toggle="tooltip1"]').tooltip({container: "th"});
});

var introBulkAction = "";
$(function(){
   
  var introBulkAction = introJs().setOptions({
        showStepNumbers: false,
        exitOnOverlayClick: false,
        /*steps: [
          {
            element: '.select-on-check-all',
            intro: 'Click here to Check All Rows/Orders or Check Single Row/Order  by clicking against the particular Row/Order.',
            position: 'bottom'
          },
          {
            element: '#jet_order_select',
            intro: "Select action want to be performed on the Selected Row(s)/Order(s).",
            position: 'bottom'
          },
          {
            element: '#help-bulk-submit',
            intro: "Submit to Perform Selected Action on Selected Order(s).",
            position: 'bottom'
          },
          
        ]*/
      });
      introBulkAction.onchange(function(targetElement){
        if(targetElement.id=="view_more_options"){
          showPopupBox(1);
        }
        if(targetElement.id=="checkbox_header"){
          showPopupBox(0);
        }
      })
      $('#bulk-action-help').click(function(){
          introBulkAction.start();
      });
      
});
/*$("#view_more_options").click(function () {
  $(".popup-box").toggle("slow");
}); */
function showPopupBox(toggle){
  if(toggle==1)
    $(".popup-box").toggle("slow");
  else
    $(".popup-box").toggle("hide");
}    
</script>
<?php $get = Yii::$app->request->get();
  if(isset($get['tour'])) : 
?>
  <script type="text/javascript">
    $(document).ready(function()
    {
        var orderQuicktour = introJs().setOptions({
            doneLabel: 'Next page',
            showStepNumbers: false,
            exitOnOverlayClick: false
        });
        orderQuicktour.onchange(function(targetElement){
          if(targetElement.id=="view_more_options"){
            showPopupBox(1);
          }
          if(targetElement.id=="checkbox_header"){
           showPopupBox(0);
          }
        }); 
        setTimeout(function () {
            orderQuicktour.start().oncomplete(function() {
              window.location.href = '<?= Data::getUrl("jetreturn/index?tour") ?>';
          },1000);
        });
    });
  </script>
<?php endif; ?>
