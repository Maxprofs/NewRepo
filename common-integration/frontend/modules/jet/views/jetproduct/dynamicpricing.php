<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$skuDetails = \yii\helpers\Url::toRoute(['jetproduct/getskudetails']);
?>
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title" style="text-align: center;font-family: Comic Sans MS;"><?= $product_title ?></h4>
	        </div>
	        <div class="modal-body">
				<div class="jet-product-form">

                    <?php $form = ActiveForm::begin([
                            'id' => 'jet_dynamic_price_form',
                            'action' => Yii::getAlias('@webjeturl').'/jetproduct/updatedynamicprice/?id='.$id,
                            'method'=>'post',
                        ]); ?>
                    <div class="form-group">
                    	<input type="hidden" name="product_type" value="<?= $type?>">
                    	<div class="field-jetproduct">                        	
                        	<table class="table table-striped table-bordered">
                        		<thead>
                        			<tr>
                        				<th colspan="1">SKU</th>
                        				<th colspan="1">*Your Price</th>
                        				<th colspan="1">*Competitor's Price</th>
                        				
                    					<th colspan="1"><p data-toggle="tooltip" title="Please enter MIN PRICE of your product (less than marketplace offer)">Min Price*</p></th>
                    					<th colspan="1"><p data-toggle="tooltip" title="Please enter MAXIMUM PRICE of your product">Max Price*</p></th>
                    					<th colspan="1"><p data-toggle="tooltip" title="Bid price (Ex: $1) ">Bid Price*</p></th>

                        			</tr>
                        		</thead>
                        		<tbody>
                    			<?php                         				
            					foreach ($model as $key12 => $value12) 
                				{
                					?>
            						<tr>
                        				<td colspan="1">
                        					<?= $value12['sku'] ?> 
                        				</td>
                        				<td colspan="1">
                        					<span <?php if($competitorPriceData[$value12['sku']]['type']=="increase"){echo "class=greater";}?>><?=$competitorPriceData[$value12['sku']]['my_price'];?></span>
                        				</td>
                        				<td colspan="1">
                        					<span><?=$competitorPriceData[$value12['sku']]['marketplace_price'];?></span>
                        				</td>
                        				<?php if($competitorPriceData[$value12['sku']]['type']=="increase")
                        				{?>
	                        				<td colspan="1">
	                        					<input type="text" class="min_price form-control" name="sku[<?= $value12['sku'] ?>][sku_min_price]" value="">
	                        				</td>
	                        				<td colspan="1">
	                        					<input type="hidden" name="sku[<?= $value12['sku'] ?>][sku_current_price]" value="<?= $competitorPriceData[$value12['sku']]['my_price'] ?>" readonly="">
	                        					<input type="text" class="max_price form-control" name="sku[<?= $value12['sku'] ?>][sku_max_price]" value="">
	                        				</td>
	                        				<td colspan="1">
	                        					<input type="text" class="bid_price form-control" name="sku[<?= $value12['sku'] ?>][sku_bid_price]" value="">
	                        				</td>	
                        				<?php 
                        				}
                        				else
                        				{?>
                        					<td>
	                        					Your price is correct according to marketplace , no need to reprice
	                        				</td>
                        				<?php
                        				}?>	
                        			</tr>
                					<? 			
                				}
                    			?>                        			
                        		</tbody>
                        	</table>                        	
                        </div>                                               
                	</div>                	
                	<?php ActiveForm::end(); ?>
                </div>
                <div class="modal-footer Attrubute_html">
		          	<div class="v_error_msg" style="display:none;"></div>
		          	<div class="v_success_msg alert-success alert" style="display:none;"></div>
		          	<div class="note_messages">
			        	<p class="note">
						*Your Price and *Competitor's Price include item price and shipping price.
						<br>
						*Min Price: Enter miminum price value that can adjust according to competitor price
						<br>
						*Max Price: Enter maximum price value that can vary accordking to competitor price
						<br>
						*Bid Price: Enter bidding price value that can include/exclude with max/min price to win the order  
						</p>
					</div>
		          	<div class="submit_button">
		          		<?= Html::submitButton('Save', ['class' => 'btn btn-primary','id'=>'savedynamicprice','onclick'=>'saveData()']) ?>
		        		<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
		        	</div>
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
	var postData = $("#jet_dynamic_price_form").serializeArray();
	var formURL = $("#jet_dynamic_price_form").attr("action");
	//console.log(postData);
	//return false;
	$('.v_success_msg').hide()
	$('.v_error_msg').hide()
	$('#LoadingMSG').show();
	if (checkMinPrice() && checkMaxPrice() && checkBidPrice()) 
	{
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
	        	if(data.success)
	        	{
	        		$('.v_success_msg').html('');
	        		$('.v_success_msg').append(data.success);
	        		$('.v_error_msg').hide();
	        		$('.v_success_msg').show();
	        	}
		        else
			    {
		        	$('.v_error_msg').html('');
	        		$('.v_error_msg').append(data.error);
	        		$('.v_success_msg').hide();
	        		$('.v_error_msg').show();
			    }
	        },
	        error: function(jqXHR, textStatus, errorThrown)
	        {
	        	$('.v_error_msg').html('');
	        	$('#LoadingMSG').hide();
	        	$('.v_error_msg').append("something went wrong..");
	        	$('.v_error_msg').show();
	        }
	    });
	}
	else
	{
		$('.v_error_msg').html('');
    	$('#LoadingMSG').hide();
    	$('.v_error_msg').append("Please fill price details in numeric for each sku(s)...");
    	$('.v_error_msg').show();
     	return false;
    }    
}
function checkMinPrice()
{	
	var flag = 0;
	$(".min_price").each(function() 
	{	
	  if ( (!$(this).val().trim().length) || (isNaN($(this).val().trim())) ) 
	  {
	  	flag++;
	  	return 0;
	  }	  
	});

	if (!flag) {
		return true;
	}
	return false;		
}
function checkMaxPrice()
{
	var flag = 0;
	$(".max_price").each(function() 
	{	   			   
	  if ( (!$(this).val().trim().length)  || (isNaN($(this).val().trim())) ) 
	  {
	  	flag++;
	  	return 0;
	  }	  
	});
	if (!flag) {
		return true;
	}
	return false;		
}
function checkBidPrice()
{	
	var flag = 0;
	$(".bid_price").each(function() 
	{	
	  if ( isNaN($(this).val().trim()) ) 
	  {
	  	flag++;
	  	return 0;
	  }	  
	});

	if (!flag) {
		return true;
	}
	return false;		
}
</script>