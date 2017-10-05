<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$merchant_id = MERCHANT_ID;
$cancelJetOrderSku= \yii\helpers\Url::toRoute(['jetorderdetail/cancelsingleordersku']);
?>
<link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/bootstrap_file_field.css">
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade cancel_model" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	       <?php $form = ActiveForm::begin([
            'id' => 'jet_cancel_form',
            'action' => $cancelJetOrderSku,
            'method'=>'post',
          ]); ?>
	      <div class="modal-content" id='edit-content'>
	        <div class="modal-header">
	          <h4 class="modal-title" style="text-align: center;">Cancel Acknowledged Order on Jet</h4>
	        </div>
	        <div class="modal-body">
	        	
				<div class="jet-product-form">
                    <div class="form-group">
                        <div class="field-jetproduct">
                        	<table class="table table-striped table-bordered">                        		
                        		<tbody>
                        			<tr>
										<td class="value_label" width="33%">
											<span>Merchant Order ID</span>
										</td>
										<td class="value form-group " width="100%">
											<input type="hidden" name="merchant_order_id" value="<?= $order_data['merchant_order_id'] ?>">
											<input type="hidden" name="merchant_id" value="<?= $merchant_id ?>">
											<?= $order_data['merchant_order_id'] ?>
										</td>
									</tr>
									<tr>
										<table class="table table-striped table-bordered" id="cancel_order_qty">
											<thead>
												<tr>
													<th>Product Sku</th>
													<th>Cancel Qauntity</th>
													<th>Ordered Qauntity</th>
												</tr>
											</thead>
											<tbody>
											<?php 
											if(count($order_data['order_items'])>0)
											{
												foreach($order_data['order_items'] as $value)
												{
												?>
													<tr>
														<td><?= $value['merchant_sku']?></td>
														<td>
															<input type="hidden" class="requested_qty" name="cancel_data[<?=$value['merchant_sku']?>][request_order_quantity]" value="<?=$value['request_order_quantity']?>">
															<input type="text" class="form-control response_shipment_cancel_qty" name="cancel_data[<?=$value['merchant_sku']?>][request_cancel_quantity]" value="">
														</td>
														<td><?= $value['request_order_quantity']?></td>
													</tr>
												<?php
												}
											}?>
											</tbody>
										</table>
									</tr>	
                           		</tbody>
                        	</table>  
                        </div>
                     </div>
                </div>

                <div class="block-show-callout type-warning block-show-callout type-warning">
	         </div>
	        </div>
	        <div class="modal-footer">
	          <div class="v_error_msg" style="display:none;"></div>
	          <div class="v_success_msg alert-success alert" style="display:none;"></div>
	          
	          <?= Html::button('Cancel Order', ['class' => 'btn btn-primary','id'=>'cancelOrder','onclick'=>'orderCancel()']) ?>
	          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
	        </div>
	      </div>	
	      <?php ActiveForm::end(); ?>	      
		</div>
	</div>	 
</div>

<script type="text/javascript">
function orderCancel(){
	var j$ = $;
	var flag = false;
	/*var response_shipment_cancel_qty=document.getElementById("response_shipment_cancel_qty").value;
	var response_shipment_requested_qty=document.getElementById("requested_qty").value;*/
	$("#cancel_order_qty .response_shipment_cancel_qty").each(function(){
		var response_shipment_cancel_qty=$(this).val();
		var response_shipment_requested_qty=$(this).prev("input").val();
		if($.isNumeric(response_shipment_cancel_qty) && response_shipment_cancel_qty>0 && response_shipment_cancel_qty<=response_shipment_requested_qty)
		{
			flag=true;
		}
	});
	if(!flag)
	{
		alert("Please fill correct cancel quantity...");
		return false;
	}
	var url='<?= $cancelJetOrderSku; ?>';
	var merchant_id='<?= $merchant_id;?>';
	$('#LoadingMSG').show();
	//submit simple form	
	/*$.post(url,
		    {
				merchant_id  : merchant_id ,
				merchant_sku : merchant_sku,
				merchant_order_id : merchant_order_id ,
				response_shipment_cancel_qty : response_shipment_cancel_qty,
				_csrf : csrfToken
		    },
		    function(msg, status){
		    	$('#LoadingMSG').hide();
		    	alert("Cancel Status : " + msg );
		        return true;
		    });*/
	$.ajax({
		url: url,
		data: $('form').serialize(),
		datatype: 'json',
		type : "POST",
		success: function(data) 
		{
			$('#LoadingMSG').hide();
		    if(data=="success")
		    {
		    	alert("Order qauntity successfully cancelled on jet");
		    	$('.cancel_model').modal('hide');
		    }
		    else
		    {
		    	alert("Order qauntity not cancelled on jet");
		    }
		    return true;
		}
	});
}   
</script>
