<?php
use frontend\integration\models\WalmartConfiguration;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\walmart\components\Data;

$this->title = 'Walmart Configurations';
$this->params['breadcrumbs'][] = $this->title;
$isPrice=false;
$priceType="";
$priceValue="";
$query="SELECT * FROM `email_template`";
$email = Data::sqlRecords($query,"all");

if(isset($clientData['custom_price']) && $clientData['custom_price'])
{
	$pricData=explode('-',$clientData['custom_price']);
	if(is_array($pricData) && count($pricData)>0)
	{
		if($pricData[0]=="fixed")
			$priceType = "fixed";
		else
			$priceType = "percent";
		$priceValue = $pricData[1];
		$isPrice=true;
	}
}
?>
<script>
</script>
<div class="jet-configuration-index content-section">
    <div class="jet_configuration form new-section">
    
   <?php $form = ActiveForm::begin([
	    'id' => 'walmart_config',
	    'action' => \yii\helpers\Url::toRoute(['walmartconfiguration/index']),
		'method'=>'post',
	    'options' => ['name'=>'walmart_configupdate'],
	])?>
		<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>"
               value="<?= Yii::$app->request->csrfToken; ?>"/>
      <div class="jet-pages-heading">
        <div class="title-need-help">
            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            <a class="help_jet" target="_blank" href="https://shopify.cedcommerce.com/integration/walmart-marketplace/sell-on-walmart"
               title="Need Help"></a>
        </div>
        <div class="product-upload-menu">
            <input type="submit" name="submit" value="save" class="btn btn-primary">
        </div>
            <div class="clear"></div>
        </div>
    	<div class="ced-entry-heading-wrapper">
    		<div class="entry-edit-head">
				<h4 class="fieldset-legend">Walmart Setting :</h4>
			</div>
			<div class="fieldset enable_api" id="api-section">
				<div class="ced-form-grid-walmart">
					<ul>
						<div class="ced-form-item odd">
							<li class="ced-lg-hlaf ced-xs-full">
								<label><span>Walmart Consumer Id :</span></label>
							</li>
							<li class="value form-group field-jetconfiguration-api_password required">
								<input id="consumer_id" type="text" name="consumer_id" value="<?=$clientData['consumer_id'];?>" class="form-control">
							</li>
							<div class="clear"></div>
						</div>
						<div class="ced-form-item even">
							<li class="value_label">
								<label><span> Walmart Secret Key :</span></label>
							</li>
							<li class="value form-group field-jetconfiguration-api_user required">
								<textarea rows="4" cols="50" id="secret_key" name="secret_key"><?= $clientData['secret_key']; ?></textarea>
							</li>
							<div class="clear"></div>
						</div>
						<div class="ced-form-item odd">
							<li class="ced-lg-hlaf ced-xs-full">
								<label><span>Walmart Consumer Channel Type ID :</span></label>
							</li>
							<li class="ced-lg-hlaf ced-xs-full">
								<input type="text" id="consumer_channel_type_id" name="consumer_channel_type_id" value="<?= $clientData['consumer_channel_type_id']; ?>" class="form-control">	
							</li>
							<div class="clear"></div>
						</div>
					</ul>
				</div>
			</div>
    	</div>
    	<div class="ced-entry-heading-wrapper">
    		<div class="entry-edit-head">
	    		<h4 class="fieldset-legend">Walmart Return Location</h4>
	    	</div>
	    	<div class="fieldset walmart-configuration-index">
	    		<div class="ced-form-grid-walmart">
		    		<ul>
		    			<div class="ced-form-item odd">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label><span>First Address :</span></label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="first_address" name="first_address" value="<?= $clientData['first_address'];?>" class="form-control">
			    			</li>
			    			<div class="clear"></div>
			    		</div>
			    		<div class="ced-form-item even">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>Second Address :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="second_address" name="second_address" value="<?= $clientData['second_address'] ?>" class="form-control">
			    			</li>
			    			<div class="clear"></div>
		    			</div>
		    			<div class="ced-form-item odd">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>City :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="city" name="city" value="<?= $clientData['city'] ?>" class="form-control" >
			    			</li>
			    			<div class="clear"></div>
			    		</div>
			    		<div class="ced-form-item even">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>State :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="state" name="state" value="<?=  $clientData['state'] ?>" class="form-control">
			    			</li>
			    			<div class="clear"></div>
			    		</div>
			    		<div class="ced-form-item odd">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>Zipcode :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="zipcode" name="zipcode" value="<?= $clientData['zipcode'] ?>" class="form-control">
			    			</li>
			    			<div class="clear"></div>
			    		</div>
			    		<div class="ced-form-item even">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>Skype Id (Optional) :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="skype_id" name="skype_id" value="<?= $clientData['skype_id'] ?>" class="form-control">
			    			</li>
			    			<div class="clear"></div>
			    		</div>
					</ul>
	    		</div>
			</div>
    	</div>
		<div class="ced-entry-heading-wrapper">
			<div class="entry-edit-head">
	    		<h4 class="fieldset-legend">Product Settings</h4>
	    	</div>
	    	<div class="fieldset walmart-configuration-index">
	    		<div class="ced-form-grid-walmart">
		    		<ul>
		    			<div class="ced-form-item odd">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>Product tax Code :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<input type="text" id="tax_code" name="tax_code" value="<?= isset($clientData['tax_code']) ?>" class="form-control">
			    			</li>
			    			<div class="clear"></div>
		    			</div>
		    			<div class="ced-form-item even">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>Product Custom Pricing (fixed or %age) :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full ced-sub-element">
			    				<select onchange="priceChange(this)" name="updateprice" class="form-control">
			    					<option value="no">No</option>
									<option value="yes" <?php if($isPrice){echo "selected=selected";}?>>Yes</option>
								</select>
								<div id="update_price_val" class="update_price" <?php if(!$isPrice){echo "style=display:none";}?>>
									<select name="custom_price" class="form-control" <?php if(!$isPrice){echo "disabled";}?>>
										<option value="fixed" <?php if($priceType=="fixed"){echo "selected=selected";}?>>Fixed</option>
										<option value="percent" <?php if($priceType=="percent"){echo "selected=selected";}?>>Percentage</option>
									</select>
									<input type="text" id ="updateprice_value" name="updateprice_value" value="<?php echo $priceValue; ?>" class="form-control" <?php if(!$isPrice){echo "disabled";}?>>
									<div class="clear"></div>
								</div>
			    			</li>
			    			<div class="clear"></div>
		    			</div>
		    			<div class="ced-form-item even">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label>
			    					<span>Remove Free Shipping From all Products :</span>
			    				</label>
			    			</li>
			    			<li class="ced-lg-hlaf ced-xs-full ced-sub-element">
			    				<select name="remove_free_shipping" class="form-control">
			    					<option value="0">No</option>
									<option value="1" <?php if(isset($clientData['remove_free_shipping']) && $clientData['remove_free_shipping']){echo "selected=selected";}?>>Yes</option>
								</select>
			    			</li>
			    			<div class="clear"></div>
		    			</div>
		    		</ul>
	    		</div>
	    	</div>
		</div>
		<div class="ced-entry-heading-wrapper">
    		<div class="entry-edit-head">
	    		<h4 class="fieldset-legend">Email Subscription Setting</h4>
	    	</div>
	    	<div class="fieldset enable_api" id="email-subscription-section">  
	    		<div class="ced-form-grid-walmart">
		    		<ul>
		    		<?php foreach($email as $key=>$value): ?>
		    			<?php if($value['show_on_admin_setting'] == 1):?>
		    			<div class="ced-form-item odd">
			    			<li class="ced-lg-hlaf ced-xs-full">
			    				<label><span><?php echo $value['custom_title']; ?></span></label>
			    			</li>
			    			<?php if(isset($clientData['email/'.$value['template_title']]) && !empty($clientData['email/'.$value['template_title']])):?>
			    			<li class="value" id="custom_image_on_jet">
			    				<input type="checkbox" id="email/<?php echo $value['template_title'];?>" name="email/<?php echo $value['template_title']; ?>" value="<?= $clientData['email/'.$value['template_title']]; ?>" checked>
			    			
			    			</li>
			    			<? else: ?>
			    			<li class="ced-lg-hlaf ced-xs-full">
  								<input type="checkbox" id="email/<?php echo $value['template_title']; ?>" name="email/<?php echo $value['template_title']; ?>" value="1">
  							</li>
			    			<?php endif; ?>
			    		

			    			<div class="clear"></div>
			    			
			    		</div>
			    		<?php endif; ?>
			    	<?php endforeach; ?>
					</ul>
	    		</div>
			</div>
    	</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>
<style>
    .jet-configuration-index .value_label {
        width: 50%;
    }
.jet-configuration-index .table-striped select,.jet-configuration-index .form-control{
    width: 100%;
    display: inline-block;
    padding-left: 10px;
}
    .jet-configuration-index .value {
    border: medium none !important;
    display: inline-block;
    width: 100%;
}
#custom_price_csv span, #custom_title_csv span{
    width: 85%;
    display: inline-block;
}
   .jet-configuration-index .help_jet{
        display: inline-block;
   }
   .help_jet {
    width: 50px!important;
}

</style>
<script>
	function priceChange(node)
	{
		var val=j$(node).val();
		if(val=='yes')
		{
			$('#update_price_val').children('select').prop('disabled', false);
			$('#update_price_val').children('input').prop('disabled', false);
			$('#update_price_val').show();
		}
		else
		{
			$('#update_price_val').children('select').prop('disabled', true);
			$('#update_price_val').children('input').prop('disabled', true);
			$('#update_price_val').hide();
		}
	}
	$('#walmart_config').submit(function( event ) 
	{
		//alert($('#update_price_val').children('input').val());
		if($('#update_price_val').children('select').is(":not(:disabled)") && ($('#update_price_val').children('input').val()=="" || ($('#update_price_val').children('input').val()!="" && !$.isNumeric($('#update_price_val').children('input').val()))))
		{
			event.preventDefault();
			alert("Please fill valid price value, otherwise set 'No' Custom Pricing");
		}
	});
</script>
