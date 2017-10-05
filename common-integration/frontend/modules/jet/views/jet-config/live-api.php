<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\jet\components\Data;

$url=\yii\helpers\Url::toRoute(['jet-config/save']);
if(!isset($fulfillment_node)){
	$merchant_id=Yii::$app->user->identity->id;
	$query="SELECT user,secret,fulfillment_node FROM `jet_test_api` WHERE merchant_id='".$merchant_id."' LIMIT 0,1";
	$testConfig=Data::sqlRecords($query,'one','select');
	$fulfillment_node=$testConfig['fulfillment_node'];
}
?>
<div class="api_enable jet_config content-section">
	<div class="jet-pages-heading">
       		<p>Congratulation!! your jet live api(s) are enabled. Now, You are ready to Jet.</p>
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
						<input class="form-control" type="hidden" value="<?php echo $fulfillment_node;?>" name="fulfillment">
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
		var flag=false;
		event.preventDefault();
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
		    .done(function(msg)
		    {
		        $('#LoadingMSG').hide();
		        if(msg=="3")
		        {
		          alert("3");
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
