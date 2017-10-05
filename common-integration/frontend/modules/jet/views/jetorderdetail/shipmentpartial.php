<?php
use yii\helpers\Html;
$merchant_id = Yii::$app->user->identity->id;
$partialshipmentData= \yii\helpers\Url::toRoute(['jetorderdetail/partialshipment']);
?>
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <div class="modal-header">
	          <h4 class="modal-title" style="text-align: center;"> Jet Order Shipment</h4>
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
											<input type="text"  name="merchant_order_id" class="form-control" id="merchant_order_id" value="<?= $merchant_order_id ?> ">
										</td>
									</tr>
        							<tr>
										<td class="value_label" width="33%">
											<span>Shipping Carrier </span>
										</td>
										<?php 
								            if(is_array($carriers) && count($carriers)>0)
								            {?>
								        		<td class="value form-group " width="100%">
									            	<select  id="carrier" class="form-control" name="carrier">
										            <?php 
														foreach($carriers as $val)
														{
															?>
															<option value="<?= $val?>"><?= $val;?></option>
												  <?php }?>
											  		</select>
										  		</td>
								            <?php 
								        	}else{
								            	?>
								            		<td class="value form-group " width="100%">
														<input type="text"  name="carrier" class="form-control" id="carrier">
													</td>
								            	<?php
								            }
								        ?>	
									</tr>
									<tr>
										<td class="value_label" width="33%">
											<span>Shipment Tracking Number </span>
										</td>
										<td class="value form-group " width="100%">
											<input type="text"  name="shipment_tracking_number" class="form-control" id="shipment_tracking_number">
										</td>
									</tr>									
									<tr>
										<td class="value_label" width="33%">
											<span>Product Sku </span>
										</td>
										<td class="value form-group " width="100%">
											<input type="text"  name="merchant_sku" class="form-control" id="merchant_sku">
										</td>
									</tr>
									<tr>
										<td class="value_label" width="33%">
											<span>Shipment Quantity</span>
										</td>
										<td class="value form-group " width="100%">
											<input type="text"  name="response_shipment_sku_quantity" class="form-control" id="response_shipment_sku_quantity">
										</td>
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
	          
	          <?= Html::submitButton('Ship Order', ['class' => 'btn btn-primary','id'=>'shipOrder','onclick'=>'orderShip()']) ?>
	          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
	        </div>
	      </div>		      
		</div>
	</div>	 
</div>

<script type="text/javascript">

function orderShip()
{
	var merchant_order_id=document.getElementById("merchant_order_id").value;
	var shipment_tracking_number=document.getElementById("shipment_tracking_number").value;
	var carrier=document.getElementById("carrier").value;
	var merchant_sku=document.getElementById("merchant_sku").value;
	var response_shipment_sku_quantity=document.getElementById("response_shipment_sku_quantity").value; 

	if(((response_shipment_sku_quantity)=="") || ((response_shipment_sku_quantity)=="")||((merchant_order_id)=="")||((shipment_tracking_number)=="")||((carrier)=="")||((merchant_sku)=="")) {
		alert("Please fill all fields");
		return false;
	}
	var url='<?= $partialshipmentData; ?>';
	
	$('#LoadingMSG').show();
	//submit simple form	
	$.post(url,
    {
		merchant_order_id : merchant_order_id ,
		shipment_tracking_number : shipment_tracking_number,
		carrier : carrier, 
		merchant_sku : merchant_sku,
		response_shipment_sku_quantity : response_shipment_sku_quantity,				
		_csrf : csrfToken
    },
    function(msg, status){
    	$('#LoadingMSG').hide();
    	alert("Shipment Status : " + msg );
        return true;
    });
}   
</script>
