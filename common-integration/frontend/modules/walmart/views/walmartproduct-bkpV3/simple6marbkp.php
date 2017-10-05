<?php 
use frontend\modules\walmart\components\AttributeMap;

$id=$model->category;
$product_type=$model->jet_product->type;
$sku=$model->jet_product->sku;
$merchant_id= $model->merchant_id;	
$walmart_attributes=array();
$walmart_attributes = json_decode($model->walmart_attributes,true);

$html='';
?>
<table class="table table-striped table-bordered">
<?php	
//required attributes
$_requiredArr=[];
if(is_array($attributes) && count($attributes)>0)
{
    foreach ($attributes as $key=>$value)
    {
        $attr_id="";
        if(is_array($value))
        {
            foreach ($value as $key=>$val)
            {
                $attr_id = implode(AttributeMap::ATTRIBUTE_PATH_SEPERATOR, $val);
                /*foreach ($val as $sub_attr_value) 
                {
                    if($sub_attr_value != $key) {
                        $attr_id .= AttributeMap::ATTRIBUTE_PATH_SEPERATOR.$sub_attr_value;
                    }
                }*/
            }
        }
        else
        {
           $attr_id = $value; 
        }

        if($model->jet_product->type=='simple' && !in_array($attr_id, $required))
            continue;

        $_requiredArr[]=$attr_id;
        $v='';
        if(count($walmart_attributes)>0 && array_key_exists($attr_id,$walmart_attributes))
        {
            $v = $walmart_attributes[$attr_id][0];
        }
    	?>
    	<tr>
    		<?php 
    		if(is_array($requiredAttrValues) && count($requiredAttrValues)>0)
    		{
    		    $requiredAttr=[];
    		    $isUsed=false;
    		    
                if(array_key_exists($attr_id, $requiredAttrValues))
                {
                    $isUsed=true;?>
                    <td style="width:50%"><?php echo $attr_id;?></td><td  style="width:50%">
        				<select class="form-control simple-required" name="jet_attributes1[<?php echo $attr_id;?>][]">	
        					<option value="">Please Select Jet Attribute</option>
        					<?php foreach ($val1 as $val) 
        					{ 
        					   foreach(explode(',',$val) as $val2)
                                {?>
        						<option value="<?=$val2?>" <?php if($val2==$v){echo 'selected';}?>><?=$val2?></option>
        					<?php 
                                }
        					}
        					unset($requiredAttr);?>
        				</select>
    				    <span class="text-validator"><?=$attr_id; ?> attribute is required</span>
                    </td>    
		        <?php 
                }
                
    		    if(!$isUsed)
                {
                ?>
                   <td style="width:50%"><?=$attr_id; ?></td>
        	       <td style="width:50%"><input type="text" value="<?= $v?>" class="form-control simple-required" placeholder="Attribute Value" name="jet_attributes1[<?= $attr_id ?>][]"/>
        	           <span class="text-validator"><?=$attr_id; ?> attribute is required</span>
        	       </td>
                <?php 
                }
    		}
    		else
            {?>
    		   <td style="width:50%"><?=$attr_id; ?></td>
    	       <td style="width:50%"><input type="text" value="<?= $v?>" class="form-control simple-required" placeholder="Attribute Value" name="jet_attributes1[<?= $attr_id ?>][]"/>
    	           <span class="text-validator"><?=$attr_id; ?> attribute is required</span>
    	       </td>
    		<?php 
            }
    		?>
    	</tr>
    <?php 
    }
}
if(is_array($optional_attr) && count($optional_attr)>1)
{
    foreach($optional_attr as $value)
    {
        if($value=='variantGroupId' || $value=='isPrimaryVariant' 
            || $value=='brand' || $value=='manufacturer' || 
            $value=='modelNumber' || $value=='manufacturerPartNumber' || in_array($value,$_requiredArr))
        continue;
        $v='';
        if(count($walmart_attributes)>0 && array_key_exists($value,$walmart_attributes))
        {
            $v = $walmart_attributes[$value][0];
        }
        if(is_array($optionalAttrValues) && count($optionalAttrValues)>0)
		{
		    $isUsed=false;
		    $optionalAttr=[];
		    foreach($optionalAttrValues as $key=>$val1)
            {
		        if(array_key_exists($value, $val1))
		        { $isUsed=true;
		        ?>
		          <td style="width:50%"><?php echo $value;?></td>
		          <td  style="width:50%">
    				<select class="form-control" name="jet_attributes1[<?php echo $value;?>][]">	
    					<option value="">Please Select Jet Attribute</option>
    					<?php foreach ($val1 as $val) 
    					{   foreach(explode(',',$val) as $val2){?>
    						<option value="<?=$val2?>" <?php if($val2==$v){echo 'selected';}?>><?=$val2?></option>
    					<?php 
                            }
    					}?>
    				</select>
			     </td>    
		        <?php 
		        }   
		    }
		    if(!$isUsed)
            {?>
    		   <tr>
    	           <td style="width:50%"><?=$value; ?></td><td style="width:50%"><input type="text" value="<?= $v?>" class="form-control required" placeholder="Attribute Value" name="jet_attributes1[<?= $value ?>][]"/></td>
               </tr>
    	   <?php
            }
    	}
    	else
        {?>
    	    <tr>
	           <td style="width:50%"><?=$value; ?></td><td style="width:50%"><input type="text" value="<?= $v?>" class="form-control required" placeholder="Attribute Value" name="jet_attributes1[<?= $value ?>][]"/></td>
           </tr>    
    	<?php 
        } 
		
    }
}
unset($unitArray);
unset($attributes);
unset($v);
unset($data);
?>
</table>
<script type="text/javascript">
var csrfToken = j$('meta[name=csrf-token]').attr("content");
	function clickMore(){

	}
	j$('#jetproduct-upc-select').change(function(){
			j$(this).parent().find('input#jetproduct-upc').trigger('blur');
	});
	j$("#jetproduct-upc").keyup(function(){
		var va=j$(this).val();
		j$(this).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
		j$(this).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
		j$(this).parent().find('select').prop('disabled', true);
		j$(this).parent().find('select').css('display', 'none');
		j$(this).prop('style',"");
		j$(this).css({'width':'60%'});
		//j$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
		if(va == ""){
			j$(this).parent().find('.jetproduct-upc_opt_type').val("");
			return false;
		}
		fillBarcodeType();
	});
	function fillBarcodeType(){
		var ele="#jetproduct-upc";
		var va=j$(ele).val();
		var reg_upc=/^\d{12}$/;///^\d*[.]?\d+/
		var reg_isbn_10=/^\d{10}$/;
		var reg_isbn_13=/^\d{13}$/;
		var reg_gtin_14=/^\d{14}$/;
		if(reg_upc.test(va)){
			j$(ele).prop('style',"");
			j$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			j$(ele).parent().find('.jetproduct-upc_opt_type').val('UPC');
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
			j$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'30%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			j$(ele).parent().find('select').prop('disabled', true);
			j$(ele).parent().find('select').css('display', 'none');
			return true;
		}else if(reg_isbn_10.test(va)){
			j$(ele).prop('style',"");
			j$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			j$(ele).parent().find('.jetproduct-upc_opt_type').val('ISBN-10');
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
			j$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'30%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			j$(ele).parent().find('select').prop('disabled', true);
			j$(ele).parent().find('select').css('display', 'none');
			return true;
		}else if(reg_isbn_13.test(va)){
			j$(ele).prop('style',"");
			j$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', true);
			j$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
			j$(ele).parent().find('select').prop('disabled', false);
			j$(ele).parent().find('select').prop('style',"");
			j$(ele).parent().find('select').css({'width':'30%','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			return true;
		}else if(reg_gtin_14.test(va)){
			j$(ele).prop('style',"");
			j$(ele).css({'width':'60%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
			j$(ele).parent().find('.jetproduct-upc_opt_type').val('GTIN-14');
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
			j$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
			j$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'30%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
			j$(ele).parent().find('select').prop('disabled', true);
			j$(ele).parent().find('select').css('display', 'none');
			return true;
		}else{
		 		return false;
		 }
	}
	fillBarcodeType();
</script>	