<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$url=\yii\helpers\Url::toRoute(['jet-config/index']);
$saveloginurl=\yii\helpers\Url::toRoute(['jet-install/savelogindetails']);
?>
<div class="api_enable jet_config content-section test-api-step">
	<div class="">
       		<p class="note"><b class="note-text">Note:</b> Test API configuration setup is used to tell the jet that you are ready to upload the products to jet and you can process the jet orders efficiently.So In order to obtain the LIVE API credentials, you must have to complete the TEST APIs setup. </p>
       		<p class="note" style="display: none;"><b class="note-text">Note:</b> Currently we are getting some API issue from Jet side, so you might be unable to complete the API configuration setup, so please <a  href="#get_login_details" title="Click on More Help Button ">submit you Jet partner login details</a>, our team will make the configuration setup for you. You can checkout the <strong><a href="https://developer.jet.com/v1.02/page/status" target="_blank">Jet API status from here</a> </strong></p>
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
		<ul class="table table-sliiped" cellspacing="0">
			<li>
				<div>
					<div class="value_label">
						<span class="control-label">API User</span>
					</div>
					<div class="form-group field-jetconfiguration-api_user required">
						<input placeholder="Please enter API User" autofocus="autofocus" id="test-jetconfiguration-api_user" class="form-control" type="text" value="" name="username" maxlength="255">
						<div class="has-error">
							<p class="help-block help-block-error error_category_map" style="display: none;">API User is Required</p>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
			<li>
				<div>
					<div class="value_label">
						<span class="control-label">Secret</span>
					</div>
					<div class="form-group field-jetconfiguration-api_password required">
						<input placeholder="Please enter Secret" autofocus="autofocus" id="test-jetconfiguration-api_password" class="form-control" type="text" value="" name="password" maxlength="255">
						<div class="has-error">
							<p class="help-block help-block-error error_category_map" style="display: none;">Secret key is Required</p>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
			<li>
				<div>
					<div class="value_label">
						<span class="control-label">Merchant Id</span>
					</div>
					<div class="form-group field-jetconfiguration-merchant_email required">
					
						<input placeholder="Please enter Merchant Id" autofocus="autofocus" id="test-jetconfiguration-merchant_id" class="form-control" type="text" value="" name="merchant">
						<div class="has-error">
							<p class="help-block help-block-error error_category_map" style="display: none;">Merchant Id is Required</p>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
			<li class="test_mode_details">
				<div>
					<div class="value_label">
						<span class="control-label">Fulfillment Node Id</span>
						
					</div>
					<div class="form-group field-jetconfiguration-fullfilment_node_id required">
						<input placeholder="Please enter Fulfillment Node Id" autofocus="autofocus" id="test-jetconfiguration-fullfilment_node_id" class="form-control" type="text" value="" name="fulfillment">
						<div class="has-error">
							<p class="help-block help-block-error error_category_map" style="display: none;">Fulfillment Id is Required</p>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
			<li>
				<div>
					<div class="get_details" style="float: left;">
						<input type="button" class="btn btn-primary api_details" value="More Help?" id="get_login_details" title="Unable to complete configuration setup?">
					</div>
					<div class="clearfix">
						<input type="button" class="btn btn-primary next" value="Next" id="test_button">
					</div>
				</div>
			</li>

		</ul>
		<?php 
			ActiveForm::end();
		?>
	</div>	
</div>
<div id="get_clients_details" style="display:none">
    <div class="container">    
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="text-align: center;">Jet partner panel login details</h4>
                    </div>
                    <div class="modal-body">
                    	<span>
                    	 	You just need to enter your jet partner login credentails , team will setup your api configurable and let you know very soon.
                    	 </span>
                    </div>
                    <div class="modal-body" id="clients_details">                    
						<ul class="table table-sliiped">
							<li>
								<div>
									<div class="value_label">
										<span class="control-label">Store URL</span>
									</div>
									<div class="form-group field-jetconfiguration-api_user required">
										<input placeholder="Please enter your shopify store name" autofocus="autofocus" id="storename" class="form-control" type="text" value="<?= SHOP;?>" name="logindetails[storename]">
									</div>
									<div class="clear"></div>
								</div>
							</li>
							<li>
								<div>
									<div class="value_label">
										<span class="control-label">User name</span>
									</div>
									<div class="form-group field-jetconfiguration-api_password required">
										<input placeholder="Please enter Jet partner panel user name" autofocus="autofocus" id="jet_username" class="form-control" type="text" value="" name="logindetails[jet-username]" maxlength="255">										
									</div>
									<div class="clear"></div>
								</div>
							</li>
							<li>
								<div>
									<div class="value_label">
										<span class="control-label">Password</span>
									</div>
									<div class="form-group field-jetconfiguration-merchant_email required">
										<input placeholder="Please enter Jet partner panel password" autofocus="autofocus" id="jet_password" class="form-control" type="text" value="" name="logindetails[jet-password]">
									</div>
									<div class="clear"></div>
								</div>
							</li>
							<li>
								<div class="model-footer">
									<span class="mote"><b>Note : </b> We will keep your login details as confidentials.After the configuration setup, you can update/change the login details(if required) </span>
								</div>
							</li>							
							<li>
								<div>
									<div class="clearfix">
										<button style="float: right;margin-left: 2%;" type="button" class="btn btn-primary btn-default" data-dismiss="modal">Close</button>
										<button id="submit_details" style="float: right;margin-left: 2%;" type="button" class="btn btn-primary" data-dismiss="modal">Submit</button>
									</div>								
								</div>
							</li>
						</ul>						
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var csrfToken = $('meta[name="csrf-token"]').attr("content");

$(document).ready(function(){
	var admin = "";
	url_string = window.location.href;
	var url_param = new URL(url_string);
	 admin = url_param.searchParams.get("ced_developer");
	 if( (admin !== null )&& admin.trim()!="")
	 {
		 var url = '<?= $url;?>'+'?ced_developer='+admin;
	 }else
	 {
		 var url = '<?= $url;?>';
	 }		
	 
    UnbindNextClick()
	$('.next').on('click', function(event){	
		//check validation
    	event.preventDefault();
		var flag=false;
		$('form .form-control').each(function()
		{
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
          $('.top_error').hide();
          nextStep();
        }
        else{
          $('.top_error').html(msg);
          $('.top_error').show();
        }
      });
    } 
	});
});

$('#submit_details').click(function(){
	var url='<?= $saveloginurl; ?>';
    $('#LoadingMSG').show();
    var  storename = $("#storename").val();
    var  username  = $("#jet_username").val();
    var  password  = $("#jet_password").val(); 
    $.ajax({
      method: "post",
      url: url,
      data: {storename:storename,username:username,password:password,_csrf : csrfToken}
    })
    .done(function(msg)
    {
       $('#LoadingMSG').hide();
       alert(msg);
    });
});

$('#get_login_details').click(function(){		
   $('#get_clients_details').css("display","block");      
   $('#get_clients_details #myModal').modal({
       keyboard: false,
       backdrop: 'static'
   });	    
});
</script>