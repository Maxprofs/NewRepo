<?php 
?>
<div class="container">
  <!-- Modal -->
  <div class="modal fade rounded" id="myModal" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content modal-content-error">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" style="text-align: center;font-family: "Comic Sans MS";">Shipping Exception: <?=$sku?></h4>
        </div>
        <div class="modal-body">
			<div class="ship_exception">
				<div class="help-block help-block-error top_exception_error alert-danger" style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
				<div class="alert-success" id="top_exception_success" style="display: none; border-radius: 4px;margin-bottom: 10px;padding: 10px;">Shipping Exception request has been sent successfully on jet.</div>
				<div class="has-error">
						<p class="help-block help-block-error error_shipp_Exp" style="display: none;"></p>
				</div>
				<label style="margin-top:10px;margin-bottom:10px">The shipping exceptions call is used to set up specific methods and costs for individual Product SKUs that will override your default settings.</label>
				<div class="fieldset enable_api">
					<table class="table table-striped table-bordered">
						<tr>
							<td class="value_label"><span>Shipping Exception Type</span></td>
							<td>
								<select name="ship_exception" id="ship_exception_validate" class="validate form-control">
									<option value="">Please Select Option</option>
									<?php $ship_exception=array('exclusive','restricted','include');
									foreach ($ship_exception as $val){
										if(is_array($model) && count($model)>0 && $model['shipping_exception_type']==$val){?>
											<option value="<?php echo $val;?>" selected="selected"><?php echo $val;?></option>
										<?php }else{?>
											<option value="<?php echo $val;?>"><?php echo $val;?></option>
										<?php }?>
								<?php }?>
								</select> 
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">*This is required field.</p>
								</div>
							</td>
						</tr>
						<tr>
							<td class="value_label"><span>Shipping Method</span></td>
							<?php
								$shipping_method_arr=array(
									'DHL Global Mail',
									'FedEx 2 Day',
									'FedEx Express Saver',
									'FedEx First Overnight',
									'FedEx Ground',
									'FedEx Home Delivery',
									'FedEx Priority Overnight',
									'FedEx Smart Post',
								    'FedEx Standard Overnight',
								    'Freight',
								    'Ontrac Ground',
								    'UPS 2nd Day Air AM',
								    'UPS 2nd Day Air',
								    'UPS 3 Day Select',
								    'UPS Ground',
								    'UPS Mail Innovations',
								    'UPS Next Day Air Saver',
								    'UPS Next Day Air',
								    'UPS SurePost',
								    'USPS First Class Mail',
								    'USPS Media Mail',
								    'USPS Priority Mail Express',
								    'USPS Priority Mail',
								    'USPS Standard Post',
								    'Other'				
								);?>
							<td>
							 	<select name="ship_method" id="ship_method_validate"  class="validate form-control">
							 		<option value="">Please Select Option</option>
								 	<?php 
								 	foreach($shipping_method_arr as $value){
								 		if(is_array($model) && count($model)>0 && $model['shipping_method']==$value){?>
											<option value="<?php echo $value;?>" selected="selected"><?php echo $value;?></option>
								  <?php }else{?>
								 			<option value="<?php echo $value;?>"><?php echo $value;?></option>
								  <?php }
								 	}?>
							 	</select>	
							 	
							 	<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">*Either Service Level or Shipping Method must be provided</p>
								</div>
						   </td>				
						</tr>
						<tr>
							<td class="value_label"><span>Service Level</span></td>
							<td>
								<select name="ship_level" id="ship_level_validate" class="validate form-control">
									<option value="">Please Select Option</option>
									<?php $service_level=array('Second Day','Next Day','Scheduled (freight)','Expedited','Standard','Priority','5 to 10 Day','11 to 20 Day','Scheduled (freight 11 to 20 day)');
									foreach ($service_level as $val){
									   if(is_array($model) && count($model)>0 && $model['service_level']==$val){?>
											<option value="<?php echo $val;?>" selected="selected"><?php echo $val;?></option>
								 <?php }else{?>
								 			<option value="<?php echo $val;?>"><?php echo $val;?></option>
								 <?php }?>	
							  <?php }?>		  	
								</select> 
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">*Either Service Level or Shipping Method must be provided</p>
								</div>
							</td>
						</tr>
						<tr id="overrid_check">
							<td class="value_label"><span>Override Type</span></td>
							<td>
								<select name="override_type" id="override_type_validate" class="validate form-control">
									<option value="">Please Select Option</option>
					  				<option value="Override charge" <?php if(is_array($model) && count($model)>0 && $model['override_type']=="Override charge"){echo "selected=selected";}?>>Override charge</option>
					  				<option value="Additional charge" <?php if(is_array($model) && count($model)>0 && $model['override_type']=="Additional charge"){echo "selected=selected";}?>>Additional charge</option>
								</select> 
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">*Required if shipping_exception_type is either "exclusive" or "include".</p>
								</div>
							</td>
						</tr>
						<tr  id="shipping_charge_check">
							<td class="value_label"><span>Shipping Charge Amount</span></td>
								<td><input type="text" id="shipping_charge_validate" name="shipping_charge_amount" value="<?php 
								if(is_array($model) && count($model)>0){echo $model['shipping_charge_amount'];}?>" class="validate form-control">
									<div class="has-error">
										<p class="help-block help-block-error error_category_map" style="display: none;">*Required if override_type is provided and must be numeric and greater than 0.</p>
									</div>
								</td>
							</td>
						</tr>
					</table>
				</div>
			</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
          <input type="button" class="btn btn-primary exception" id="ship_exception" onclick="sendShipexception()" value="Submit"></td>
        </div>
      </div>	      
    </div>
  </div>	  
</div>	

<script type="text/javascript">
function sendShipexception(){
	var exflag=false;
	var sflag=false;
	var oflag=false;
	var chflag=false;

	if(!$('#ship_exception_validate').val())
	{
		exflag=true;
		$('#ship_exception_validate').addClass("select_error");
		$('#ship_exception_validate').next('div').children('.error_category_map').css('display','block');
	}
	else{
		exflag=false;
		$('#ship_exception_validate').removeClass("select_error");
		$('#ship_exception_validate').next('div').children('.error_category_map').css('display','none');
	}
	if(!$('#ship_method_validate').val() && !$('#ship_level_validate').val())
	{
		sflag=true;
		if(!$('#ship_method_validate').val())
		{
			$('#ship_method_validate').addClass("select_error");
			$('#ship_method_validate').next('div').children('.error_category_map').css('display','block');
		}
		else
		{
			$('#ship_level_validate').addClass("select_error");
			$('#ship_level_validate').next('div').children('.error_category_map').css('display','block');
		}
	}
	else{
		sflag=false;
		$('#ship_method_validate').removeClass("select_error");
		$('#ship_method_validate').next('div').children('.error_category_map').css('display','none');
	}
	if($('#ship_exception_validate').val()!="restricted" && !$('#override_type_validate').val())
	{
		oflag=true;
		$('#override_type_validate').addClass("select_error");
		$('#override_type_validate').next('div').children('.error_category_map').css('display','block');
	}
	else{
		oflag=false;
		$('#override_type_validate').removeClass("select_error");
		$('#override_type_validate').next('div').children('.error_category_map').css('display','none');
	}
	if($('#ship_exception_validate').val()!="restricted" && (!$('#shipping_charge_validate').val() || !$.isNumeric($('#shipping_charge_validate').val()) || $('#shipping_charge_validate').val()<=0))
	{
		chflag=true;
		$('#shipping_charge_validate').addClass("select_error");
		$('#shipping_charge_validate').next('div').children('.error_category_map').css('display','block');
	}
	else{
		chflag=false;
		$('#shipping_charge_validate').removeClass("select_error");
		$('#shipping_charge_validate').next('div').children('.error_category_map').css('display','none');
	}
	var id='<?php echo $id;?>';
	var url='<?php echo Yii::$app->request->baseUrl."/jet/jetproduct/shipexception"; ?>';
	var sku='<?php echo $sku;?>';
	var ship_exception=$('#ship_exception_validate').val();
	var ship_level=$('#ship_level_validate').val();
	var ship_method=$('#ship_method_validate').val();
	var override_type=$('#override_type_validate').val();
	var shipping_charge_amount=$('#shipping_charge_validate').val();
	var csrfToken = $('meta[name="csrf-token"]').attr("content");
	if(!exflag && !sflag && !oflag && !chflag)
	{
		$('#LoadingMSG').show();
		$.ajax({
	        method: "GET",
	        url: url,
	        data:{ 
			        product_id: id,
			        sku : sku,
			        ship_exception : ship_exception,
			        ship_level : ship_level,
			        ship_method : ship_method,
			        override_type : override_type,
			        shipping_charge_amount : shipping_charge_amount,
			        _csrf : csrfToken 
			     }
	    })
	    .done(function( msg ) {
	    	$('#LoadingMSG').hide();
	      	if(msg==""){
	      		$('.top_exception_error').css('display','none');
				$('#top_exception_success').css('display','block');
		  	}else{
		  		$('.top_exception_error').html('');
		  		$('#top_exception_success').css('display','none');
		  		$('.top_exception_error').html('Error from jet in shipping excention: '+msg);
		  		$('.top_exception_error').css('display','block');
			}
	    });
	}
}
</script>