<div class="return_exception">
<?php //print_r($data['return_exception']);die;
$modelreturnexcep="";
$modelreturnexcep=$data['return_exception'];
?>
	<div class="help-block help-block-error top_return_error alert-danger" style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
	<div class="alert-success" id="top_return_success" style="display: none; border-radius: 4px;margin-bottom: 10px;padding: 10px;">Return Exception request has been sent successfully on jet.</div>
	<div class="has-error">
		<p class="help-block help-block-error error_shipp_Exp" style="display: none;"></p>
	</div>
	<label style="margin-top:10px;margin-bottom:10px">The returns exceptions call is used to set up specific methods that will overwrite your default settings on a fulfillment node level for returns.</label>
	<div class="fieldset enable_api">
		<table class="table table-striped table-bordered">
		<tr>
			<td class="value_label"><span>Time to Return</span></td>
				<td>
					<input type="text" name="time_return" id="time_to_return" value="<?php if($modelreturnexcep) echo $modelreturnexcep->time_to_return ?>" class="validate_return form-control">
					<div class="has-error">
						<p class="help-block help-block-error error_return_map" style="display: none;">*This is required field and must be numeric.</p>
					</div>
					<span class="text-validator">Enter number of days to return the item after order purchased by customer(Maximum value is 30).</span>
				</td>
		</tr>
		<tr>
			<td class="value_label"><span>Return Location Ids</span></td>
				<td>
					<input type="text" name="return_location" id="return_location_id" value="<?php if($modelreturnexcep) echo $modelreturnexcep->return_location_ids;?>" class="validate_return form-control">
					<div class="has-error">
						<p class="help-block help-block-error error_return_map" style="display: none;">*This is required field.</p>
					</div>
					<span class="text-validator">Enter Return Node id from Returns Settings Section on JET Panel.Click <a href="http://partner.jet.com/fulfillmentnode" target="_blank">here</span>
				</td>
		</tr>
		<tr>
			<td class="value_label"><span>Return Shipping Methods</span></td>
				<td>
				<?php $shipping_carrier=array('Freight','FedEx Ground','UPS Ground');?>
				<select id="return_ship_method" class="validate_return form-control" name="return_shipping_method">
					<option value="">Please Select Option</option>
					<?php
					foreach ($shipping_carrier as $val){
						if(is_object($modelreturnexcep) && $modelreturnexcep->return_shipping_methods==$val){?>
							<option value="<?php echo $val;?>" selected="selected"><?php echo $val;?></option>
						<?php }else{?>
							<option value="<?php echo $val;?>"><?php echo $val;?></option>
						<?php }?>
					<?php 
					}
					?>
				</select>
				<div class="has-error">
					<p class="help-block help-block-error error_return_map" style="display: none;">*This is required field.</p>
				</div>	
			</td>
		</tr>
		<tr><td colspan="2"><input type="button" class="btn btn-success exception" id="return_exception" onclick="sendReturnexception()" value="Send Return Exception"></td></tr>
	</table>
</div>
<script type="text/javascript">
function sendReturnexception(){
	var flag=true;
	$('.validate_return').each(function(){
		if($(this).val()=="")
		{
			flag=false;
			$(this).addClass("select_error");
			$(this).next('div').children('.error_return_map').css('display','block');
		}
		else
		{
			if($(this).attr('name')=="time_return" && $(this).val()!="" && !$.isNumeric($(this).val())){
				flag=false;
				$(this).addClass("select_error");
				$(this).next('div').children('.error_return_map').css('display','block');
			}
			else
			{
				$(this).removeClass("select_error");
				$(this).next('div').children('.error_return_map').css('display','none');
			}
		}
	});
	var id='<?php echo $model->id;?>';
	var url='<?php echo Yii::$app->request->baseUrl."/jetproduct/returnexception"; ?>';
	var sku='<?php echo $model->sku;?>';
	var time_to_return=$('#time_to_return').val();
	var return_location_id=$('#return_location_id').val();
	var return_ship_method=$('#return_ship_method').val();
	var csrfToken = $('meta[name="csrf-token"]').attr("content");
	if(flag)
	{
		$('#LoadingMSG').show();
		$.ajax({
	        method: "GET",
	        url: url,
	        data:{ 
			        product_id: id,
			        sku : sku,
			        time_to_return : time_to_return,
			        return_location_id : return_location_id,
			        return_ship_method : return_ship_method,
			        _csrf : csrfToken 
			     }
	    })
	    .done(function( msg ) {
	    	$('#LoadingMSG').hide();
	      	if(msg==""){
	      		$('.top_return_error').css('display','none');
				$('#top_return_success').css('display','block');
		  	}else{
		  		$('.top_return_error').html('');
		  		$('#top_return_success').css('display','none');
		  		$('.top_return_error').html('Error from jet in return excention: '+msg);
		  		$('.top_return_error').css('display','block');
			}
	    });
	}
}
</script>