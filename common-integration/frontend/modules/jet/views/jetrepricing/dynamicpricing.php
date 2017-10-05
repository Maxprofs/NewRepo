<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\jet\components\Jetrepricing;
$skuDetails = \yii\helpers\Url::toRoute(['jetproduct/getskudetails']);
$this->title = 'Jet Repricing Section';
?>
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade modal-withscroll" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <!-- <div class="modal-header">
	        	<div class="form new-section">
		        	<div class="jet-pages-heading">
					        <div class="title-need-help">
					            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
					        </div>
					        <div class="product-upload-menu">
					        	<button id="reprice-close" type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
					        	<?php if(count($additionalData)>0):?>
					        		<span class="btn btn-primary" id='savedynamicprice' onclick='saveData(this);'>Save</span>
					            <?php endif;?>
					        </div>
							<div class="clear"></div>
							<div style="display:none;" class="reprice_msg v_success_msg alert-success alert">Information saved successfully.</div>
							<div style="display:none;" class="reprice_msg v_error_msg alert-danger alert"></div>
					</div>
	        	</div>
	        </div> -->
	        
	        <div class="modal-body">
	        	<div class="form new-section">
	        			<?php $form = ActiveForm::begin([
                            'id' => 'jet_dynamic_price_form',
                            'action' => Yii::getAlias('@webjeturl').'/jetrepricing/save',
                            'method'=>'post',
                        ]); ?>
			    		<input type="hidden" name="product_id" value="<?=$id;?>"/>
					    <div class="jet-pages-heading">
					        <div class="title-need-help">
					            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
					        </div>
					        <div class="product-upload-menu">
					        	<button id="reprice-close" type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
					        	<?php if(count($additionalData)>0 && $better==1):?>
					        		<span class="btn btn-primary" id='savedynamicprice' onclick='saveData(this);'>Save</span>
					            <?php endif;?>
					        </div>
							<div class="clear"></div>
							<div style="display:none;" class="reprice_msg v_success_msg alert-success alert">Information saved successfully.</div>
							<div style="display:none;" class="reprice_msg v_error_msg alert-danger alert"></div>
						</div>
						<?php if(count($additionalData)==0):?>
							<div class="jet-pages-heading">
								<p class="legend-text">
							    	<b>No Record Found.</b>
							    </p>
								
							</div>
						<?php else:?>
						<div class="wrapper-reprice-content">
							<?php if($better==1):?>		
								<div class="jet-install">
								    <p class="note">
								    	<b class="note-text">Note:</b> <!--Saving Information from this page will not update price on Jet Marketplace. After save use 'Upload Price' bulk action from 'Manage Products' panel.
								    	<br/>-->Pricing Data may be outdated. So it is recommended to always first click on 'Fetch Marketplace Pricing' button from 'Manage Products' panel or <a id="fetch_pricing" onclick="$('#reprice-close').trigger('click');" href="<?=yii\helpers\Url::toRoute('jetrepricing/getrepricing');?>">click here to fetch marketplace pricing</a> , then go through marketplace pricing.
								    </p>
								</div>	
							
								<div class="jet-pages-heading">
									<p class="legend-text">
								    	<b>Minimum Threshold Price</b>
								    	<b>:</b>
								    	<span style="color: #333">Minimum Price at which you want your product to be Sold.</span>
								  	</p>
									
								</div>
							<?php endif;?>
							<div class="ced-entry-heading-wrapper">
								<div class="entry-edit-head">
									<h4 class="fieldset-legend">Product Information</h4>
								</div>
								<div class="fieldset enable_api" id="api-section">
									<table class="table table-striped table-bordered" cellspacing="0">
										<tbody>
											<tr>
											    <td class="value_label"><span>Product Title</span></td>
											    <td><span><?= $product_title ?></span></td>
											</tr>
											<!--<tr>
											    <td class="value_label"><span>Shopify Product Type</span></td>
											    <td><span></span></td>
											</tr>-->
											<tr>
											    <td class="value_label"><span>Type</span></td>
											    <td><span><?= $type; ?></span></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<div class="ced-entry-heading-wrapper">
									<div class="entry-edit-head">
										<h4 class="fieldset-legend">Product Pricing</h4>
									</div>
									<div class="fieldset enable_api" id="api-section">
										<section class="cd-sku">
											<div class="cd-sku-items">
												<ul id="basics" class="cd-sku-group">
													<?php $i = 0;?>
													<?php foreach($additionalData as $value):?>
														<?php if(trim($value['buybox_status'])=="")continue;?>
														<?php $i++;?>
														<li class="fieldset enable_api sku_lis">
															<div class="cd-sku-trigger" onclick="showContent('<?='sku_'.$i;?>');">
																<table class="table table-striped table-bordered" cellspacing="0">
																	<thead>
																		<tr>
																			<th>SKU : <span><?= $value['sku'] ?></span><span class="<?= ($value['buybox_status']==1)?'best_price':''; ?>" <?= ($value['buybox_status']==1)?'data-toggle = "tooltip" title="Click here to know your best marketplace price on jet.com"':''; ?> ><?= ($value['buybox_status']==1)?"Best Price":'<span class=\'better_price\'>Better Price</span> - Need Repricing'; ?></span></th>
																		</tr>
																	</thead>
																</table>		
															</div>
															<div id="sku_<?=$i;?>" class="cd-sku-content" style="display: none;">
																<table class="table table-striped table-bordered" cellspacing="0">
																	<thead>
																		<tr>
																			<!--<th>SKU</th>-->
																			<th>STATUS ON JET</th>
																			<th>YOUR PRICING</th>
																			<th>BEST PRICING ON JET</th>
																		</tr>
																	</thead>
																	<tbody>
																		<tr>
																			<!--<td><span><?php // $value['sku'] ?></span></td>-->
																		    <td><span><?= $value['status'] ?></span></td>
																		    	<?php $yourPricing = (isset($value['merchant_price']) && $value['merchant_price']!="")?json_decode($value['merchant_price'], true):[];?>
																		    	<?php $yourShipping = 0;?>
																				<?php if(is_array($yourPricing) && isset($yourPricing[0]['shipping_price'])){?>
																					<?php $yourShipping = $yourPricing[0]['shipping_price'];?>	
																				<?php }?>
																		    <td>
																		    		<?php foreach ($yourPricing as $yourkey => $yourvalue):?>
																						<table>
																			   				<tr>
																			   					<td>Your Price :</td>
																			   					<td><?= $yourvalue['item_price'] ?></td>
																			   				</tr>
																			   				<tr>
																			   					<td>Your Ship Method :</td>
																			   					<td><?= (isset($yourvalue['shipping_method']) && $yourvalue['shipping_method']!="")?$yourvalue['shipping_method']:"Unknown"; ?></td>
																			   				</tr>
																							<tr>
																			   					<td>Your Ship Price :</td>
																			   					<td><?= $yourvalue['shipping_price'] ?></td>
																			   				</tr>
																			   				<tr>
																			   					<td>Your Total Price :</td>
																			   					<td><?= (isset($yourvalue['shipping_price']))?($yourvalue['shipping_price'] + $yourvalue['item_price']):$yourvalue['item_price']; ?></td>
																			   				</tr>
																			   			</table>	
																					<?php endforeach;?>
																			</td>
																			<?php $bestPricing = (isset($value['marketplace_price']) && $value['marketplace_price']!="")?json_decode($value['marketplace_price'], true):[];?>
																			<?php $marketplaceTotal = 0;?>
																			<?php if(is_array($bestPricing) && isset($bestPricing[0]['item_price'])){?>
																				<?php if(isset($bestPricing[0]['shipping_price'])):?>
																					<?php $marketplaceTotal = $bestPricing[0]['item_price'] + $bestPricing[0]['shipping_price'];?>
																				<?php else:?>
																					<?php $marketplaceTotal = $bestPricing[0]['item_price'];?>
																				<?php endif;?>	
																			<?php }?>	
																			<td>
																				<?php foreach ($bestPricing as $bestkey => $bestvalue):?>
																					<table>
																		   				<tr>
																		   					<td>Best Price :</td>
																		   					<td><?= $bestvalue['item_price'] ?></td>
																		   				</tr>
																		   				<tr>
																		   					<td>Best Ship Method :</td>
																		   					<td><?= (isset($bestvalue['shipping_method']) && $bestvalue['shipping_method']!="")?$bestvalue['shipping_method']:"Unknown"; ?></td>
																		   				</tr>
																						<tr>
																		   					<td>Best Ship Price :</td>
																		   					<td><?= $bestvalue['shipping_price'] ?></td>
																		   				</tr>
																		   				<tr>
																			   				<td>Best Total Price :</td>
																			   				<td><?= (isset($bestvalue['shipping_price']))?($bestvalue['shipping_price'] + $bestvalue['item_price']):$bestvalue['item_price']; ?></td>
																			   			</tr>
																		   			</table>	
																				<?php endforeach;?>
																			</td>
																		</tr> 
																		<?php 
																		if($value['buybox_status']==0):?>
																		<tr>
																			<td colspan="3">
																				<div>
																					<div>
																						<label>Want to Perform Repricing For this Product?</label>
																						<select name="data[<?= $value['variant_id']?>][enable]" class="form-control">
																							<option value=""></option>
																							<option value="1" <?php if($value['enable']=='1')
																					echo 'selected="selected"';?>>Yes</option>
																							<option value="0" <?php if($value['enable']=='0')
																					echo 'selected="selected"';?>>No</option>
																						</select>
																					</div>
																					<?php if(($marketplaceTotal-$bidPrice-$yourShipping)>=0):?>
																						<div>
																							<label>Repriced Price</label>
																							<span class="form-control repriced_price" style="padding-top:10px;"><?=($marketplaceTotal-$bidPrice-$yourShipping);?></span>
																							<span class="text-validator">This is item repriced price that will be send to marketplace, if repricing enabled.</span>
																						</div>
																					<?php endif;?>
																					<div>
																						<label>Minimum Threshold Price</label>
																						<input type="text" name="data[<?= $value['variant_id']?>][min_price]" value="<?=$value['min_price']; ?>" class="form-control min-price" />
																						<span class="text-validator">You need to set threshold price value which should be less or equal to repriced price.</span>
																					</div>
																				</div>	
																			</td>
																		</tr>
																		<?php endif;?>	   
																	</tbody>
																</table>
															</div> <!-- cd-sku-content -->
														</li>
													<?php endforeach;?>
												</ul>
											</div>
										</section>	
									</div>
									
							</div>
						</div>	
						<?php endif;?>			
					<?php ActiveForm::end(); ?>
				</div>
			</div>
			
	      </div>	      
		</div>
	</div>
</div>

<script type="text/javascript">
$('[data-toggle="tooltip"]').tooltip();
var csrfToken = $('meta[name="csrf-token"]').attr("content");
function showContent(ele){
	$('#'+ele).toggle();
	/*if($('#'+ele).hasClass('show_ele')){
		$('#'+ele).removeClass('show_ele');
		$('#'+ele).addClass('show_ele');
	}else{
		$('#'+ele).removeClass('hide_ele');
		$('#'+ele).addClass('show_ele');
	}*/
}
function getCurrentDetails(sku,sku_id)
{
	var url='<?= $skuDetails; ?>';
	$('#LoadingMSG').show();
    $.ajax({
      method: "post",
      url: url,
      data: {sku:sku,_csrf : csrfToken}
    })
    .done(function(msg)
    {
	   $('#LoadingMSG').hide();
       $('#sku_live_details_'+sku_id).html(msg);
       $('#sku_live_details_'+sku_id).css("display","block");	         
    });
}
$('.min-price').change(function(){
	var valid = false;
	valid = /^\d+(\.\d+)?$/.test($(this).val());
	if(!valid){
			$(this).parent('div').addClass('has-error');
			$(this).closest('div.cd-sku-content').parent('li')/*.children('div.cd-sku-trigger')*/.addClass('has-error-wrp');
			//console.log($(this).closest('div.cd-sku-content').parent('li').children('div.cd-sku-trigger'));
			validated = false;
	}else{
			$(this).parent('div').removeClass('has-error');
			//console.log($(this).closest('div.cd-sku-content').parent('li').children('div.cd-sku-trigger'));
			$(this).closest('div.cd-sku-content').parent('li')/*.children('div.cd-sku-trigger')*/.removeClass('has-error-wrp');
	}
});
function saveData()
{
	$('.reprice_msg.v_success_msg').hide();
	$('.reprice_msg.v_error_msg').hide();
	var validated = true;
	$('.min-price').each(function(idx, ele){
		var valid = false;
		valid = /^\d+(\.\d+)?$/.test($(this).val());
		if(!valid){
			$(this).parent('div').addClass('has-error');
			$(this).closest('div.cd-sku-content').parent('li')/*.children('div.cd-sku-trigger')*/.addClass('has-error-wrp');
			//console.log($(this).closest('div.cd-sku-content').parent('li').children('div.cd-sku-trigger'));
			validated = false;
		}else{
			if(parseFloat($(this).val())>parseFloat($('.repriced_price').eq(idx).text())){
				$(this).parent('div').addClass('has-error');
				$(this).closest('div.cd-sku-content').parent('li')/*.children('div.cd-sku-trigger')*/.addClass('has-error-wrp');
				//console.log($(this).closest('div.cd-sku-content').parent('li').children('div.cd-sku-trigger'));
				validated = false;
			}else{
				$(this).parent('div').removeClass('has-error');
				//console.log($(this).closest('div.cd-sku-content').parent('li').children('div.cd-sku-trigger'));
				$(this).closest('div.cd-sku-content').parent('li')/*.children('div.cd-sku-trigger')*/.removeClass('has-error-wrp');
			}
			
		}
	});
	if(!validated){
		$('.reprice_msg.v_error_msg').html('Minimum Threshold Price must be numeric & less-that or equal-to Repriced Price.');
	    $('.reprice_msg.v_error_msg').show();
		return false;
	}
	var postData = $("#jet_dynamic_price_form").serializeArray();
	var formURL = $("#jet_dynamic_price_form").attr("action");
	//console.log(postData);
	$('#LoadingMSG').show();
	
		$.ajax(
	    {
	        url : formURL,
	        type: "POST",
	        dataType: 'json',
	        data : postData,
	        _csrf : csrfToken,
	        success:function(data, textStatus, jqXHR)
	        {
	        	console.log(data);
	        	$('#LoadingMSG').hide();
	        	if(data.success){
	        		if(parseInt(data.count)>0){
	        			var htm = "Information Successfully Saved. Now you can Upload Repriced price by <a onclick='showDropDownOption();'>Upload Price</a> bulk action.";
	        			var htmlSuccess = '<div class="alert-success alert fade in" id="w1-success-0"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'+htm+'</div>'; 
	        			if($('div.alert-success.alert.fade.in').length>0){
	        				$('div.alert-success.alert.fade.in').remove();
	        			}
	        			$(htmlSuccess).insertAfter('ul.breadcrumb');
	        		}
	        		$('#reprice-close').trigger('click');
	        		$('body').scrollTop('0');
	        	}else{
		        	$('.reprice_msg.v_error_msg').html(data.detail);
	        		$('.reprice_msg.v_error_msg').show();
			    }
			},
	        error: function(jqXHR, textStatus, errorThrown)
	        {
	        	$('#LoadingMSG').hide();
	        	$('.reprice_msg.v_error_msg').html("Information not saved. Please try again.");
	        	$('.reprice_msg.v_error_msg').show();
	        }
	    });
  
}
$('.ced-entry-heading-wrapper input').each(function () {
           var type = $(this).attr("type");
           if(type == "text"){
           		console.log($(this).val());
           		$(this).parent().addClass('pencil-parent');
            	$(this).after('<span class="glyphicon glyphicon glyphicon-pencil"></span>');
            }
});
/*function selectRepricing(node){
	if($(node).val()==0)
	{
		$('.repriced_price').attr('disabled', false);
		$('.min-price').attr('disabled', false);
	}
	else
	{
		$('.repriced_price').attr('disabled', true);
		$('.min-price').attr('disabled', true);
	}
}*/
</script>
<style>
.show_ele{
	display:block;
}
.hide_ele{
	display:none;
}
.sku_lis{
	padding-bottom: 0px;
	margin-bottom: 10px;
}
.has-error-wrp{
	border-color: #a94442;
}
input:has(~ span.glyphicon-pencil), span.glyphicon-pencil:parent{
	display:none !important;
}

</style>