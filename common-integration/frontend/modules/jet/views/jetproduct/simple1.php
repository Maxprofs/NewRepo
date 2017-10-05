<?php 
$id=$model->fulfillment_node;
$product_type=$model->type;
$sku=$model->sku;
$merchant_id= MERCHANT_ID ;	
$jet_attributes = [];
$jet_attributes = json_decode($model->jet_attributes,true);
$html='';
?>
<table class="table table-striped table-bordered">
<?php	
if(is_array($attributes) && count($attributes)>0)
{
	foreach ($attributes as $value)
	{
	    $v='';
	    if(count($jet_attributes)>0 && array_key_exists($value['attribute_id'],$jet_attributes)){
	        $v=$jet_attributes[$value['attribute_id']][0];
	    }
		?>
		<tr>
			<?php 
			if(isset($value['values']) && $value['variant']==true && !isset($value['units']))
			{
				?>
				<td style="width:50%"><?php echo $value['attribute_description'];?></td><td  style="width:50%">					
					<select class="form-control" name="jet_attributes1[<?php echo $value['attribute_id'];?>][]">	
						<option value="">Please Select Jet Attribute</option>
    					<?php foreach ($value['values'] as $val) 
    					{?>
    						<option value="<?=$val?>" <?php if($val==$v){echo 'selected';}?>><?=$val?></option>
    					<?php 
    					}?>
					</select>				
				</td>
	    	<?php 
			}
	    	elseif( $value['variant']==true)
	    	{
	    		?>
	    		<td style="width:50%"><?=$value['attribute_description'] ?></td><td style="width:50%"><input type="text" value="<?= $v?>" class="form-control" placeholder="Attribute Value" name="jet_attributes1[<?= $value['attribute_id'] ?>][]"/></td>
	    		<?php 
	    	}
	    	elseif(isset($value['units']) &&  $value['variant']==true)
	    	{
			?>
				<td style="width:50%"><?=$value['attribute_description'] ?>
					<span class="text-validator">Enter Attribute Value with Unit field.</span>
				</td>
				<td style="width:50%">
					<input type="text" value="" class="form-control" placeholder="Attribute Value" name="jet_attributes1[<?=$value['attribute_id'] ?>][]"/>
	    			<select class="form-control" name="jet_attributes1[<?=$value['attribute_id'] ?>][]">
	    			<option value="">Please Select Attribute Unit</option>
	    			<?php foreach ($value['units'] as $val) {?>
	    				<option value="<?=$val ?>"><?=$val ?></option>
	    			<?php }?>
	    			</select>
				</td>
	    	<?php 
	    	}?>
		</tr>
	<?php 
	}
}else{
	echo "No attributes available for this category..";
}
unset($unitArray);unset($attributes);unset($v);unset($data);
?>
</table>
<script type="text/javascript">
var csrfToken = $('meta[name=csrf-token]').attr("content");
	function clickMore(){

	}
	$('#jetproduct-upc-select').change(function(){
			$(this).parent().find('input#jetproduct-upc').trigger('blur');
	});
	$("#jetproduct-upc").keyup(function(){
							var va=$(this).val();
							$(this).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
							$(this).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
							$(this).parent().find('select').prop('disabled', true);
							$(this).parent().find('select').css('display', 'none');
							$(this).prop('style',"");
							$(this).css({'width':'60%'});							
							if(va == ""){
								$(this).parent().find('.jetproduct-upc_opt_type').val("");
								return false;
							}
							fillBarcodeType();
	});
	function fillBarcodeType()
	{
		var ele="#jetproduct-upc";
		var va=$(ele).val();
		var reg_upc=/^\d{12}$/;///^\d*[.]?\d+/
		var reg_isbn_10=/^\d{10}$/;
		var reg_isbn_13=/^\d{13}$/;
		var reg_gtin_14=/^\d{14}$/;
		if(reg_upc.test(va)){
			$(ele).prop('style',"");
			$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			$(ele).parent().find('.jetproduct-upc_opt_type').val('UPC');
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
			$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'30%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			$(ele).parent().find('select').prop('disabled', true);
			$(ele).parent().find('select').css('display', 'none');
			return true;
		}else if(reg_isbn_10.test(va)){
			$(ele).prop('style',"");
			$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			$(ele).parent().find('.jetproduct-upc_opt_type').val('ISBN-10');
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
			$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'30%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			$(ele).parent().find('select').prop('disabled', true);
			$(ele).parent().find('select').css('display', 'none');
			return true;
		}else if(reg_isbn_13.test(va)){
			$(ele).prop('style',"");
			$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', true);
			$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
			$(ele).parent().find('select').prop('disabled', false);
			$(ele).parent().find('select').prop('style',"");
			$(ele).parent().find('select').css({'width':'30%','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			return true;
		}else if(reg_gtin_14.test(va)){
			$(ele).prop('style',"");
			$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			$(ele).parent().find('.jetproduct-upc_opt_type').val('GTIN-14');
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
			$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
			$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'30%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			$(ele).parent().find('select').prop('disabled', true);
			$(ele).parent().find('select').css('display', 'none');
			return true;
		}else{
		 		return false;
		 }
	}
	fillBarcodeType();
</script>	