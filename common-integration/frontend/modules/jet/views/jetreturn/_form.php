<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\JetReturn */
/* @var $form yii\widgets\ActiveForm */
$viewJetOrderDetails = \yii\helpers\Url::toRoute(['jetorderdetail/vieworderdetails']);
$merchant_id = $model->merchant_id;
?>
<div class="jet-return-form content-section">
<div class="form new-section">
   <?php $form = ActiveForm::begin(
		[
    		'id'=>'jet_edit_return',
    		'options' => ['onsubmit' => 'return validateReturnForm(this);'],
		]
    ); ?>
   
	<div class="form-group">
		<div class="jet-pages-heading">
		<div class="title-need-help">
			 <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
			 <span style="cursor:pointer;" id="instant-help" class="help_jet" title="help"></span>
			 </div>
			 <div class="product-upload-menu">
	         <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Submit Return', ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
	         <a href="<?= \yii\helpers\Url::to(Yii::$app->request->referrer);?>">
				<button class="btn btn-primary uptransform" type="button" title="Back to previous page"><span>Back</span></button>
			 </a>
	         </div>
	    	 <div class="clear"></div>
    	</div>
    </div>
     
    
	<div class="entry-edit-head">
		<h4 class="fieldset-legend">Return Configuration</h4>
	</div>
		<div class="fieldset enable_api">
			<?php 
				$returnArr = [];
				$returnArr = json_decode($model->return_data,true);
			?>
			<div class="table-responsive"> 
			<table class="table table-striped table-bordered return_info" cellspacing="0">
				<tbody>
					<tr>
						<td>Return Id</td>
						<td><input id="jetreturn-returnid" class="form-control" type="text" readonly="" value="<?= $model->returnid;?>" name="JetReturn[returnid]"></td>
					</tr>
					<tr>	
						<td title="Jet's unique ID for a given merchant order"> Merchant Order</td>
						<td>
							<span title="Click to view the order details for current order return" style="float: right;margin-right:30%;" type="button" class="btn btn-primary" onclick="checkorderstatus('<?=$returnArr['merchant_order_id'];?>')">View Order Detail</span>
						    <input style="width: 40%" id="jetreturn-order_reference_id" class="form-control" readonly="" type="text" value=<?= $model->order_reference_id;?>> 
						    
							<input id="jetreturn-merchant_order_id" class="form-control" type="hidden" readonly="" value="<?= $returnArr['merchant_order_id'];?>" name="JetReturn[merchant_order_id]"/>						    
						</td>
					</tr>
					<tr id="agree_to_return">
						<td>Agree To Return
							<span class="text-validator">Does merchant agree to the return charge for the return notification?</span>
						</td>
						<td><select id="jetreturn-agreeto_return" class="form-control" name="JetReturn[agreeto_return]" onchange="clickArgee(this)">
								<option value="">Please select option</option>
								<option value="1">Yes</option>
								<option value="0">No</option>
							</select>
							<div class="has-error">
								<p class="help-block help-block-error error_return" style="display: none;">*This is required field.</p>
							</div>
						</td>
					</tr>
					<tr id="return_change" style="display:none">
						<td>Return Charge Feedback
							<span class="text-validator">The reason the merchant does not agree to the return charge for the return notification.</span>
						</td>
						<td>
							<select id="jetreturn-return_charge_feedback" class="form-control" name="JetReturn[return_charge_feedback]" disabled>
								<option value="">Please select option</option>
								<option value="other">other</option>
								<option value="wrongItem">wrongItem</option>
								<option value="fraud">fraud</option>
								<option value="outsideMerchantPolicy">outsideMerchantPolicy</option>
								<option value="notMerchantError">notMerchantError</option>
								<option value="returnedOutsideWindow">returnedOutsideWindow</option>
							</select>
							<div class="has-error">
								<p class="help-block help-block-error error_return" style="display: none;">*This is required field.</p>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			</div>
			<div class="table-responsive"> 
			<table class="table table-striped table-bordered return_info_items">
				<thead>
					<th title="Product sku for return">Sku</th>
					<th title="The reason the customer is returning the item">*Reason</th>
					<th title="Quantity of the given item that was returned">Return Quantity</th>
					<th title="The reason this refund is less than the full amount">*Return Refund Feedback</th>
					<th title="The amount the retailer is willing to refund to the customer">*Refund Amount</th>
					<th title="Amount to be refunded for the given item in USD associated with the shipping cost that was allocated to this item">Shipping Cost</th>
					<th title="Amount to be refunded for the given item in USD associated with the tax that was charged on shipping">Shipping Tax</th>
					<th title="Amount to be refunded for the given item in USD associated with tax that was charged for the item">Tax</th>
				</thead>
				<tbody>
			<?php 
				foreach ($returnArr['return_merchant_SKUs'] as $key=>$value){?>
					<tr>
						<td>
							<?= $value['merchant_sku'];?>
							<input id="jetreturn-order_item_sku" type="hidden" value="<?= $value['merchant_sku'];?>" name="JetReturn[sku][]">
						</td>
							<input id="jetreturn-order_item_id" class="form-control check_disable" type="hidden" maxlength="255" readonly="" value="<?= $value['order_item_id'];?>" name="JetReturn[order_item_id][]">
						<td>
							<?= $value['reason'];?>
						</td>
						<td>
							<input id="jetreturn-total_quantity_returned" class="form-control check_disable" type="text"  value="<?= $value['return_quantity'];?>" name="JetReturn[total_quantity_returned][]">
						</td>
						<td>
							<select id="jetreturn-return_refund_feedback" class="form-control check_disable_feedback check_disable" name="JetReturn[return_refund_feedback][]">
							    <option value="">Please select option</option>
							    <option value="other">other</option>
							    <option value="item damaged">item damaged</option>
							    <option value="not shipped in original packaging">not shipped in original packaging</option>
							    <option value="customer opened item">customer opened item</option>
							</select>
							<div class="has-error">
								<p class="help-block help-block-error error_return" style="display: none;">This is required field.</p>
							</div>
						</td>
						<td>
							<input id="jetreturn-principal_real" class="form-control principal_real check_disable" name="JetReturn[principal_real][]" type="hidden" value="<?= (float)($value['requested_refund_amount']['principal']+$value['requested_refund_amount']['shipping_cost']+$value['requested_refund_amount']['shipping_tax']+$value['requested_refund_amount']['tax']);?>">
							<input id="jetreturn-principal" class="form-control check_disable" type="text" value="<?= (float)$value['requested_refund_amount']['principal'];?>" name="JetReturn[principal][]">
							<div class="has-error">
								<p class="help-block help-block-error error_return" style="display: none;">*Please enter valid refund amount..</p>
							</div>
						</td>
						<td>
							<input id="jetreturn-shipping_cost" class="form-control check_disable" type="text" value="<?= (float)$value['requested_refund_amount']['shipping_cost'];?>" name="JetReturn[shipping_cost][]">
						</td>
						<td>
							<input id="jetreturn-shipping_tax" class="form-control check_disable" type="text" value="<?= (float)$value['requested_refund_amount']['shipping_tax'];?>" name="JetReturn[shipping_tax][]">
						</td>
						<td>
							<input id="jetreturn-tax" class="form-control check_disable" type="text" value="<?= (float)$value['requested_refund_amount']['tax'];?>" name="JetReturn[tax][]">
						</td>
					</tr>
			<?php 
				}
			?>
					<tr>
						<td colspan="8">
							<span>
								<label>*Reason:</label> The reason the customer is returning the item<br>
								<label>*Return Refund Feedback:</label> The reason this refund is less than the full amount<br>
								<label>*Refund Amount:</label> The amount the retailer is willing to refund to the customer	
							</span>
						</td>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
    <?php ActiveForm::end(); ?>
    </div>
</div>
<div id="view_jet_order" style="display:none"></div>
<style>
.return_info_items td{
width:21%;
}
.return_info td{
width:50%;
}
</style>
<script type="text/javascript">
	function validateReturnForm(event){
		var flag=false;
		var isNotArgee=false;
		if($('#jetreturn-agreeto_return').val()==""){
			console.log("agreeto_return");
			flag=true;
			$('#jetreturn-agreeto_return').addClass("select_error");
			$('#jetreturn-agreeto_return').next('div').children('.error_return').css('display','block');
		}
		else if($('#jetreturn-agreeto_return').val()=="0"){
			console.log("agreeto_return->no");
			isNotArgee=true;
			$('#return_change').show();
			$('#jetreturn-return_charge_feedback').prop('disabled', false);
			$('.check_disable_feedback').each(function(){
				if($(this).val()==""){
					$(this).addClass("select_error");
					$(this).next('div').children('.error_return').css('display','block');
				}else{
					$(this).removeClass("select_error");
					$(this).next('div').children('.error_return').css('display','none');
				}
			});
			$('#jetreturn-agreeto_return').removeClass("select_error");
			$('#jetreturn-agreeto_return').next('div').children('.error_return').css('display','none');
		}else{
			console.log("agreeto_return->yes");
			$('#return_change').hide();
			$('#jetreturn-return_charge_feedback').prop('disabled', true);
			/* $('.check_disable').each(function(){
				$(this).prop('disabled',false);
			}); */
			var i=0;
			$('.principal_real').each(function(){
				var total=0;
				total = parseFloat($(this).next().val()) + parseFloat($(this).parent().next().children('input').val()) + parseFloat($(this).parent().next().next().children('input').val()) + parseFloat($(this).parent().next().next().next().children('input').val());
				console.log(total);
				
				if($(this).val() > total && $(this).parent().prev().children('select').val()==""){
					console.log('less price with reason');
					flag=true;
					//$(this).parent().prev().children('select').prop('disabled',false);
					$(this).parent().prev().children('select').addClass("select_error");
					$(this).parent().prev().children('select').next().children().css('display','block');
				}
				else if($(this).val() < total || !($.isNumeric($(this).next().val()))){
					flag=true;
					$(this).next().next('div').addClass("select_error");
					$(this).next().next('div').children().css('display','block');
				}
				else{
					console.log('equal price w/o reason');
					//$(this).parent().prev().children('select').prop('disabled',true);
					$(this).parent().prev().children('select').removeClass("select_error");
					$(this).parent().prev().children('select').next().children().css('display','none');
					$(this).next().next().removeClass("select_error");
					$(this).next().next().children().css('display','none');
				}
			});
			$('#jetreturn-agreeto_return').removeClass("select_error");
			$('#jetreturn-agreeto_return').next('div').children('.error_return').css('display','none');
		}
		if(isNotArgee && $('#jetreturn-return_charge_feedback').val()==""){
			console.log("not agree");
			flag=true;
			$('#jetreturn-return_charge_feedback').addClass("select_error");
			$('#jetreturn-return_charge_feedback').next('div').children('.error_return').css('display','block');
		}else{
			$('#jetreturn-return_charge_feedback').removeClass("select_error");
			$('#jetreturn-return_charge_feedback').next('div').children('.error_return').css('display','none');
		}
		if(flag)
		{
			console.log("hello");
			return false;
		}	
	}
	function clickArgee(node){
		if($(node).val()=="0"){
			$('#return_change').show();
			$('#jetreturn-return_charge_feedback').prop('disabled', false);
			/* $('.check_disable').each(function(){
				$(this).prop('disabled',true);
			}); */
			$('.check_disable_feedback').each(function(){
				if($(this).val()==""){
					$(this).addClass("select_error");
					$(this).next('div').children('.error_return').css('display','block');
				}else{
					$(this).removeClass("select_error");
					$(this).next('div').children('.error_return').css('display','none');
				}
			});
			$(node).removeClass("select_error");
			$(node).next('div').children('.error_return').css('display','none');
		}else if($(node).val()=="1"){
			console.log("agreeto_return->yes");
			$('#return_change').hide();
			$('#jetreturn-return_charge_feedback').prop('disabled', true);
			/* $('.check_disable').each(function(){
				$(this).prop('disabled',false);
			}); */
			$('.check_disable_feedback').each(function(){
				if($(this).val()=="" && $(this).hasClass('select_error')){
					$(this).removeClass("select_error");
					$(this).next('div').children('.error_return').css('display','none');
				}
			});
			$(node).removeClass("select_error");
			$(node).next('div').children('.error_return').css('display','none');
		}
		else{
			$(node).addClass("select_error");
			$(node).next('div').children('.error_return').css('display','block');
		}
	}
	var csrfToken = $('meta[name="csrf-token"]').attr("content");

	function checkorderstatus(merchant_order_id) 
	{
	  var url = '<?= $viewJetOrderDetails; ?>';
	  $('#LoadingMSG').show();
	  $.ajax({
	    method: "post",
	    url: url,
	    data: {merchant_order_id: merchant_order_id, _csrf: csrfToken}
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
</script>
<script>
    $(function(){   
      var intro = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
            steps: [
              {
                element: '#agree_to_return',
                intro: 'Select an Option if you are agree or not to fulfill this Return.',
                position: 'bottom'
              },
              {
                element: '#jetreturn-return_refund_feedback',
                intro: 'Select a feedback for this Return.',
                position: 'left'
              },
              {
                element: '#jetreturn-principal',
                intro: 'Enter the Principal Amount to be Refunded.',
                position: 'bottom'
              },
              {
                element: '#jetreturn-shipping_cost',
                intro: 'Enter Shipping Amount to be Refunded.',
                position: 'bottom'
              },
              {
                element: '#jetreturn-shipping_tax',
                intro: 'Enter Shipping Tax Amount to be Refunded.',
                position: 'bottom'
              },
              {
                element: '#jetreturn-tax',
                intro: 'Enter Tax Amount to be Refunded.',
                position: 'bottom'
              }
            ]
          });  
          $('#instant-help').click(function(){
                intro.start();
          });
    });
</script>