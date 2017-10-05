<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$skuDetails = \yii\helpers\Url::toRoute(['jetproduct/getskudetails']);
$this->title = 'Jet Repricing Section';
?>
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <!--<div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal">&times;</button>
		        <h4 class="modal-title" style="text-align: center;font-family: "Comic Sans MS";"><?php // $product_title ?></h4>
	        </div>-->
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
					        	<?php if(count($additionalData)>0):?>
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
						<div class="jet-pages-heading">
							<p class="legend-text">
						    	<b>Minimum Threshold Price</b>
						    	<b>:</b>
						    	<span style="color: #333">Minimum Price at which you want your product to be Sold.</span>
						  	</p>
							
						</div>

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

								<?php foreach($additionalData as $value):?>
									<table class="table table-striped table-bordered" cellspacing="0">
											<thead>
												<tr>
													<th>SKU</th>
													<th>STATUS ON JET</th>
													<th>YOUR PRICING</th>
													<th>BEST PRICING ON JET</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><span><?= $value['sku'] ?></span></td>
												    <td><span><?= $value['status'] ?></span></td>
												    	<?php $yourPricing = (isset($value['merchant_price']) && $value['merchant_price']!="")?json_decode($value['merchant_price'], true):"";?>
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
													   			</table>	
															<?php endforeach;?>
													</td>
													<?php $bestPricing = (isset($value['marketplace_price']) && $value['marketplace_price']!="")?json_decode($value['marketplace_price'], true):"";?>
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
												   			</table>	
														<?php endforeach;?>
													</td>
												</tr> 
												<tr>
													<td colspan="4">
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
															<div>
																<label>Minimum Threshold Price</label>
																<input type="text" name="data[<?= $value['variant_id']?>][min_price]" value="<?=$value['min_price']; ?>" class="form-control min-price" />
																<span class="text-validator">Minimum price at which you want to sell this product on jet.</span>
															</div>
														</div>	
													</td>
												</tr>	   
											</tbody>
										</table>
								<?php endforeach;?>	
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
var csrfToken = $('meta[name="csrf-token"]').attr("content");
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
			validated = false;
		}else{
			$(this).parent('div').removeClass('has-error');
		}
	});
	if(!validated){
		$('.reprice_msg.v_error_msg').html('Minimum Threshold Price must be numeric.');
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
	        	$('#LoadingMSG').hide();
	        	if(data.success){
	        		$('.reprice_msg.v_success_msg').show();
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
            	$(this).after('<span class="glyphicon glyphicon glyphicon-pencil"></span>');
            }
});

</script>