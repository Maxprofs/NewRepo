<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\JetRefund */
/* @var $form yii\widgets\ActiveForm */
$this->title = 'Create Jet Refund';
$this->registerJsFile(
    '@web/js/bootstrap-select.min.js',
    ['depends' => [\yii\web\JqueryAsset::className(),\yii\bootstrap\BootstrapAsset::className()],'position'=>View::POS_END]
);
$this->registerCssFile(
	"@web/css/bootstrap-select.min.css", 
	[
    'depends' => [\yii\bootstrap\BootstrapAsset::className()],
    ]
);
$viewJetOrder= \yii\helpers\Url::toRoute(['jetrefund/vieworder']);
?>

<div class="jet-refund-form form new-section">
    <?php $form = ActiveForm::begin(); ?>
	<div class="form-group">
		<div class="jet-pages-heading">
			<div class="title-need-help">
			 <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
			 </div>
			 <div class="product-upload-menu">
			 <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
			 <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
			 </div>
			 <div class="clear"></div>
		</div>	
	</div>
    <div class="form-group field-jetrefund-merchant_order_id">
		<label for="jetrefund-merchant_order_id" class="control-label">Merchant Order ID</label>
		<select name="JetRefund[merchant_order_id]" class="form-control selectpicker selectcheck" data-live-search="true" onchange="selectOrder(this)">
			<option data-tokens="">Select merchant order id ..</option>
		    <?php foreach($data as $value){?>
		    	<option data-tokens="<?=$value['id']?>"><?=$value['merchant_order_id']?></option>
		    <?php	
		    }?>
		</select>
		<div class="help-block"></div>
	</div>
	<div id="order_details" style="display:none">
		<div class="form-group field-jetrefund-order_item_id">
			
			
		</div>
	    <?= $form->field($model, 'quantity_returned')->textInput(['maxlength' => true]) ?>
	    <?= $form->field($model, 'refund_quantity')->textInput(['maxlength' => true]) ?>
	   <?php 
		   $b=[
			   "No longer want this item"=>"No longer want this item",
			   "Received the wrong item"=>"Received the wrong item",
			   "Website description is inaccurate"=>"Website description is inaccurate",
			   "Product is defective / does not work"=>"Product is defective / does not work",
			   "Item arrived damaged - box intact"=>"Item arrived damaged - box intact",
			   "Item arrived damaged - box damaged"=>"Item arrived damaged - box damaged",
			   "Package never arrived"=>"Package never arrived",
			   "Package arrived late"=>"Package arrived late",
			   "Wrong quantity received"=>"Wrong quantity received",
			   "Better price found elsewhere"=>"Better price found elsewhere",
			   "Unwanted gift"=>"Unwanted gift",
			   "Accidental order"=>"Accidental order",
			   "Unauthorized purchase"=>"Unauthorized purchase",
			   "Item is missing parts / accessories"=>"Item is missing parts / accessories",
			   "Return to Sender - damaged, undeliverable, refused"=>"Return to Sender - damaged, undeliverable, refused",
			   "Return to Sender - lost in transit only"=>"Return to Sender - lost in transit only",
			   "Item is refurbished"=>"Item is refurbished",
			   "Item is expired"=>"Item is expired",
			   "Package arrived after estimated delivery date"=>"Package arrived after estimated delivery date"  
			];
		    /* $b= ['wrong quantity received' => 'wrong quantity received', 'received wrong item than what was ordered' => 'received wrong item than what was ordered', 
		   		'accidental order' => 'accidental order','item is damaged/broken'=>'item is damaged/broken', 'item is defective/does not work properly'=>'item is defective/does not work properly',
		   		'shipping box and item are both damaged' => 'shipping box and item are both damaged','item was different than website description'=>'item was different than website description', 'package arrived later than promised delivery date'=>'package arrived later than promised delivery date',
		   		'package never arrived' => 'package never arrived','unwanted gift'=>'unwanted gift', 'unauthorized purchase'=>'unauthorized purchase', 'better price available'=>'better price available', 'no longer need/want'=>'no longer need/want',
		    ]; */
	    ?>
	    <?= $form->field($model, 'refund_reason')->dropDownList($b,['prompt'=>'']) ?>

	    <?php  $a= ['item damaged' => 'item damaged', 'not shipped in original packaging' => 'not shipped in original packaging', 'customer opened item' => 'customer opened item' ];?>
	    <?= $form->field($model, 'refund_feedback')->dropDownList($a,['prompt'=>'']) ?>

	    <?= $form->field($model, 'refund_tax')->textInput(['maxlength' => true]) ?>

	    <?= $form->field($model, 'refund_shippingcost')->textInput(['maxlength' => true]) ?>

	    <?= $form->field($model, 'refund_shippingtax')->textInput(['maxlength' => true]) ?>

	    <?= $form->field($model, 'refund_amount')->textInput(['maxlength' => true]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script type="text/javascript">
var csrfToken = $('meta[name="csrf-token"]').attr("content");
var url_path='<?= $viewJetOrder ?>';
function selectOrder(node)
{
	var id=node.value;
	//load order information
	$('#LoadingMSG').show();
  	$.ajax({
        method: "post",
        url: url_path,
        dataType:"json",
        data: {id:id,_csrf : csrfToken }
	})
    .done(function(msg) {
       console.log(msg.order_items);
       $('#LoadingMSG').hide();
       var items_array=msg.order_items;
       var html="<label for='jetrefund-order_item_id' class='control-label'>Order Item ID</label><select name='JetRefund[order_item_id]' class='form-control'>";
       $.each(items_array, function(key, value){
	      html+="<option value="+value.order_item_id+">"+value.order_item_id+"</option>";
	   });
	   html+="</select>";
	   $('field-jetrefund-order_item_id').html('');
	   $('.field-jetrefund-order_item_id').html(html);
	   $('#order_details').show();
    });
}
$('form').on('submit', function(event)
{
	var select= $('select.selectcheck option:selected').val();
	if(select =="Select merchant order id .."){
		alert('please select merchant order id ...');
		event.preventDefault();
		return false;
	}
});
</script>
