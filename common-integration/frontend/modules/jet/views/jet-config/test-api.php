<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$url=\yii\helpers\Url::toRoute(['jet-config/index']);

?>
<div class="api_enable jet_config content-section">
	<div class="jet-pages-heading">
       		<p>To integrate Shopify store with jet.com, first the merchant needs to enable his API setup on Jet.com</p>
       		<p class="note"><b class="note-text">Note:</b> In order to obtain the live mode credentials all the Test APIs MUST be running because Jet doesn’t provides Live Mode details until all the Test APIs have been set.</p>
    </div>
	<div class="api_field fieldset enable_api">
    <div class="help-block help-block-error top_error alert-danger" style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
		<?php
		  
			$form = ActiveForm::begin([
            'id' => 'jet_config_api',
            'action' => $url,
            'method' => 'post',
            'options' => ['name' => 'jet_config'],
        	]) ?>
			<table class="table table-striped table-bordered" cellspacing="0">
				<tbody>
					
						<tr>
							<td class="value_label" width="45%">
								<span>API User</span>
							</td>
							<td class="value form-group field-jetconfiguration-api_user required" width="100%">
								<input placeholder="Please enter API User" autofocus="autofocus" id="test-jetconfiguration-api_user" class="form-control" type="text" value="" name="username" maxlength="255">
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">API User is Required</p>
								</div>
							</td>
						</tr>
						<tr>
							<td class="value_label" width="45%">
								<span>Secret</span>
							</td>
							<td class="value form-group field-jetconfiguration-api_password required" width="100%">
								<input placeholder="Please enter Secret" autofocus="autofocus" id="test-jetconfiguration-api_password" class="form-control" type="text" value="" name="password" maxlength="255">
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">API Password is Required</p>
								</div>
							</td>
						</tr>
						<tr>
							<td class="value_label" width="45%">
								<span>Merchant Id</span>
							</td>
							<td class="value form-group field-jetconfiguration-merchant_email required" width="100%">
							
								<input placeholder="Please enter Merchant Id" autofocus="autofocus" id="test-jetconfiguration-merchant_id" class="form-control" type="text" value="" name="merchant">
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">Merchant Id is Required</p>
								</div>
							</td>
						</tr>
						<tr class="test_mode_details">
							<td class="value_label" width="45%">
								<span>Fulfillment Node Id</span>
								<br><span class="text-validator">Get Fulfillment Node from Jet Partner Panel.Click <a href="https://partner.jet.com/fulfillmentnode" target="_blank">here</a></span>
							</td>
							<td class="value form-group field-jetconfiguration-fullfilment_node_id required" width="100%">
								<input placeholder="Please enter Fulfillment Node Id from Jet Partner Panel" autofocus="autofocus" id="test-jetconfiguration-fullfilment_node_id" class="form-control" type="text" value="" name="fulfillment">
								<div class="has-error">
									<p class="help-block help-block-error error_category_map" style="display: none;">Fulfillment Id is Required</p>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="button" class="btn btn-primary next" value="Next" id="test_button">
							</td>
						</tr>
				</tbody>
			</table>
		<?php 
			ActiveForm::end();
		?>
	</div>	
</div>

<script type="text/javascript">
$(document).ready(function(){
  var url = '<?php echo $url;?>';
	$('.next').on('click', function(event){	
		//check validation
    event.preventDefault();
		var flag=false;
		$('form .form-control').each(function(){
			 if($(this).val()=="")
			 {
				flag=true;
			  	$(this).addClass("select_error");
			  	$(this).next('div').children('.error_category_map').show();
			 }
			 else
			 {
				$(this).removeClass("select_error");
				$(this).next('div').children('.error_category_map').hide();
			 }
		});
    if(!flag){
      $('#LoadingMSG').show(); 
      $.ajax({
            method: "POST",
            url: url,
            data: $("form").serialize(),
       })
     .done(function(msg){
        $('#LoadingMSG').hide();
        if(msg=="2")
        {
          alert("2");
        }
        else{
          $('.top_error').html(msg);
          $('.top_error').show();
        }
      });
    } 
	});
});
</script>
