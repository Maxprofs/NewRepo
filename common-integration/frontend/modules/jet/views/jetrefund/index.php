<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
$viewJetRefundDetails = \yii\helpers\Url::toRoute(['jetrefund/viewrefunddetails']);
/* @var $this yii\web\View */
/* @var $searchModel app\models\JetRefundSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Refund Orders';
$this->params['breadcrumbs'][] = $this->title;

?>
<style type="text/css">
    .table-bordered th {
        font-size: 14px;
    }
</style>
<div class="jet-refund-index content-section">
<div class="form new-section">
	<div class="jet-pages-heading">
	<div class="title-need-help">
	    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
	    </div>	    
	<div class="product-upload-menu">
	    <p>
	        <?= Html::a('Create Jet Refund', ['create'], ['class' => 'btn btn-primary','data-toggle'=>'tooltip','title'=>'Create Jet Refund']) ?>
	        <?= Html::a('Get Refund Status', ['fetch'], ['class' => 'btn btn-primary','data-toggle'=>'tooltip','title'=>'Get Refund Status']) ?>
	        <?= Html::a('Reset filter', ['jetrefund/index'], [ 'data-toggle'=>'tooltip','title'=>'Click to reset all filters','data-position'=>'top','class' => 'btn btn-primary']) ?>
	    </p>
	</div>
	    <div class="clear"></div>
	 </div> 
	<?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <div class="responsive-table-wrap">
    <?= GridView::widget([
    	'id'=>'refund_grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],            
			[
				'attribute'=>'merchant_order_id',
				'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Merchant Order Id'],
			],
			[
				'attribute'=>'refund_id',
				'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Refund Id'],
			],
			[
				'attribute'=>'order_item_id',
				'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Order Item Id'],
			],
			[
				'attribute'=>'refund_reason',
				'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Refund Reason'],
			],
			[
				'attribute'=>'refund_feedback',
				'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Return Feedback'],
			], 
			[
		        'attribute'=> 'refund_status',
		        'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Order refund status on jet'],
		        'contentOptions'=>['style'=>'width: 250px;'],
		        'filter' => ["Created"=>"Created","Processing"=>"Processing","Accepted"=>"Accepted","Rejected"=>"Rejected"],
		    ],
		    [
	          	'class' => 'yii\grid\ActionColumn',
        		'template' => '{viewrefund}',
        		'buttons' => [
        		   'viewrefund' => function ($url,$model) 
	               {
	                  return Html::a(
	                  '<span class="glyphicon glyphicon-eye-open"> </span>',
	                    'javascript:void(0)',['title'=>'Get refund order detail from Jet','data-pjax'=>0,'onclick'=>'checkrefundstatus(this.id)','id'=>$model['refund_id']]);                         
	                },	              	              
	            ],
	        ],         			            
        ],
    ]); ?>
    </div>
<?php Pjax::end(); ?>
</div>
</div>
<div id="view_jet_refund" style="display:none"></div>
<script type="text/javascript">
var csrfToken = $('meta[name="csrf-token"]').attr("content");
var submit_form = false;
$('body').on('keyup','.filters > td > input', function(event) {
	    if (event.keyCode == 13) {
	    	 if(submit_form === false) {
	    	        submit_form = true;
	    	        $("#refund_grid").yiiGridView("applyFilter");
	    	    }
	    }

});
$("body").on('beforeFilter', "#refund_grid" , function(event) {
	 return submit_form;
});
$("body").on('afterFilter', "#refund_grid" , function(event) {
	submit_form = false;
});

$(document).ready(function () {
	$('[data-toggle="tooltip"]').tooltip();
	$('[data-toggle="tooltip1"]').tooltip({container: "th"});
});

function checkrefundstatus(refund_id) 
{
  var url = '<?= $viewJetRefundDetails; ?>';
  $('#LoadingMSG').show();
  $.ajax({
    method: "post",
    url: url,
    data: {refund_id: refund_id, _csrf: csrfToken}
  })
  .done(function (msg) {
      $('#LoadingMSG').hide();
      $('#view_jet_refund').html(msg);
      $('#view_jet_refund').css("display", "block");
      $('#view_jet_refund #myModal').modal({
          keyboard: false,
          backdrop: 'static'
      })
  })
}
</script>
