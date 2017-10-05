<?php 
use frontend\components\Data;
		$dropdown_option_arr=array();
		$id=$model->category;
    	$product_type=$model->jet_product->type;
    	$product_id=$model->product_id;
    	$sku=$model->jet_product->sku;
    	$merchant_id= $model->merchant_id;	
?>
	<!--<ul class="attribute_listing" style="display:none;">-->
	<?php $v=0;
	if(is_array($attributes) && count($attributes)>0)
	{
		foreach ($attributes as $value)
		{
		    $attr_id="";
		    if(is_array($value)){
		        foreach ($value as $key=>$val)
		        {
		            $attr_id = $key;
		            break;
		        }
		    }
		    else
		    {
		        $attr_id = $value;
		    }
			if(is_array($requiredAttrValues) && count($requiredAttrValues)>0)
			{
			    foreach($requiredAttrValues as $key=>$attr_opt_val)
			    {
			        if(array_key_exists($attr_id, $attr_opt_val))
			        {
			            $opt_arr=array();
			            $opt_arr['name']=$attr_id;
			            $opt_arr['value']=$attr_id;
			            $opt_arr['select_fields']=explode(',',$attr_opt_val[$attr_id]);
			            $dropdown_option_arr[]=$opt_arr;
			            $v++;
			            break;
			        }
			        else
			        {
			            $opt_arr=array();
			            $opt_arr['name']=$attr_id;
			            $opt_arr['value']=$attr_id;
			            //$opt_arr['select_fields']=$value['values'];
			            $dropdown_option_arr[]=$opt_arr;
			            $v++;
			        }
			    }
			}
			else
			{ 
			    
		        $opt_arr=array();
		        $opt_arr['name']=$attr_id;
		        $opt_arr['value']=$attr_id;
		        $dropdown_option_arr[]=$opt_arr;
		        $v++;

			}
		}
		unset($attributes);
		unset($result);
		?>
		<!--</ul>-->
		<?php if($product_type=='variants')
		{
			$shopifyattribues=array();
			$shopifyattribues=json_decode($model->jet_product->attr_ids,true);
			$walmart_attributes_arr=array();
			if($model->walmart_attributes!=""){
					$walmart_attributes_arr=json_decode($model->walmart_attributes,true);
			}?>
			<!--<label class="control-label" for="jetproduct-jet_attributes">Variant Options</label>-->
		<table class="table table-striped table-bordered">
    		<thead>
    			<tr>
    				<th>
    					<center>SKU</center>
    				</th>
    				<th>
    					<center>Price</center>
    				</th>
    				<th>
    					<center>Inventory</center>
    				</th>
    				<th colspan="2">
    					<center>Barcode</center>
    				</th>
    				<th colspan="3">
    					<center>Map Walmart Attributes</center>
    				</th>
					
    			</tr>
    		</thead>
			<tbody>	
				<tr>
					<td rowspan="2" colspan="1">
						<input type="hidden" name="product-type" value="variants"/>
					</td>
					<td rowspan="2" colspan="2">
					</td>
					<td rowspan="2" colspan="2">
					</td>
					<?php $shopifyArr=array();
							$attrArr=array();
					foreach($shopifyattribues as $key=>$val){?>
						<td>
							<div><b><center>Shopify Option</center></b></div>
							<div><center><?=$val?></center></div>
						</td>
						<?php $shopifyArr[]=$key;
					}?>
					</tr>
				<tr>
				<?php $shop_ind=0;?>
				<?php 
				foreach($shopifyattribues as $key=>$val)
				{
					$bool=false;
					if($walmart_attributes_arr && count($walmart_attributes_arr)>0){
						$bool=array_key_exists ($val,$walmart_attributes_arr);
					}
					$attr_value="";
					if($bool){
						$attr_value=$walmart_attributes_arr[$val][0];
					}
					if(Yii::$app->getRequest()->getQueryParam('clearattr')==1){
						$attr_value="";
					}
					$app_html='';
					
		            if(isset($walmart_attributes_arr[$val]) && count($walmart_attributes_arr[$val])==2){
					    $app_html=$walmart_attributes_arr[$val][1];
					}?>
					<?php $attrArr[$shop_ind]=trim($attr_value);?>
					<td>
						<div><b><center>Walmart attribute(s)</center></b></div>
						<select class="jet_attributes_selector form-control" id="sel_<?=$key?>" name="jet_attributes[<?=$key?>][jet_attr_id]">
							<option value="">Select a Walmart Attribute...</option>
							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
									<?php foreach($dropdown_option_arr as $p_ar){?>
										<option value="<?=trim($p_ar['value'])?>" <?php if(trim($p_ar['value'])==trim($attr_value)){echo "selected='selected'";}?>><?=$p_ar['name']?></option>
									<?php }?>
							<?php }?>
						</select>
						<input type="hidden" name="jet_attributes[<?=$key?>][jet_attr_name]" value="<?=$val?>"/>
						
						<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
							<?php foreach($dropdown_option_arr as $p_ar){?>
								<?php if(array_key_exists('select',$p_ar) && is_array($p_ar['select']) && count($p_ar['select'])>0){?>
									<div <?php if($app_html==""){echo "style='display:none;'";}?> class="unit_dropdown_div_calss unit_dropdown_div_<?=$key?>_<?=$p_ar['value']?>">
										<label class="unit_label" style="width:30%;float:left;">Unit </label>
										<select <?php if($app_html==""){echo "disabled='disabled'";}?> style="width:50%;" class="unit_dropdown form-control" id="opt_<?=$key?>_<?=$p_ar['value']?>" name="attributes_of_jet[<?=$p_ar['value']?>][unit]">
										<?php foreach($p_ar['select'] as $ky=>$sel_op){?>
											<option value="<?=trim($sel_op)?>" <?php if(trim($app_html)==trim($sel_op)){echo "selected='selected'";}?>><?=trim($sel_op)?></option>
										<?php }?>
										</select>
										<div class="clear" style="clear:both;"></div>
									</div>
								<?php }?>
							<?php }?>
						<?php }?>
						
						<script type="text/javascript">
							j$("#sel_<?=$key?>").change(function(){
												if(!checksame()){
														j$('.v_error_msg').html("Please don't choose same Jet Attibute for multiple option(s).");
														j$('.v_error_msg').show();
														j$("#sel_<?=$key?>").val("");
														disablealltextsnselects(this,"");
														return false;
												}else{
													 	j$('.v_error_msg').html("");
													 	j$('.v_error_msg').hide();
												}

												var val=j$("#sel_<?=$key?> option:selected").val();

												<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
														<?php foreach($dropdown_option_arr as $p_ar){?>
															<?php if(array_key_exists('select',$p_ar) && is_array($p_ar['select']) && count($p_ar['select'])>0){?>
																	j$("#opt_<?=$key?>_<?=$p_ar['value']?>").prop('disabled',true);
																	j$(".unit_dropdown_div_<?=$key?>_<?=$p_ar['value']?>").css('display','none');
																	if(val==<?=trim($p_ar['value'])?>){
																				j$("#opt_<?=$key?>_<?=$p_ar['value']?>").prop('disabled',false);
																				j$(".unit_dropdown_div_<?=$key?>_<?=$p_ar['value']?>").css('display','block');
																	}
															<?php }?>
														<?php }?>
												<?php }?>
												if(val!=""){
													<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
															<?php $g=0;?>
															<?php foreach($dropdown_option_arr as $p_ar){?>
																<?php if(array_key_exists('select_fields',$p_ar) && is_array($p_ar['select_fields']) && count($p_ar['select_fields'])>0){?>
																		<?php if($g==0){?>
																			if(val=="<?=trim($p_ar['value'])?>"){
																				displayallselects(this,val);
																			}
																		<?php }else{?>
																			else if(val=="<?=trim($p_ar['value'])?>"){
																				displayallselects(this,val);
																			}
																		<?php }?>
																		<?php $g++;?>
																<?php }?>
															<?php }?>
															<?php if($g>0){?>
																else{
																		displayalltexts(this,val);
																}
															<?php }else{?>
																displayalltexts(this,val);
															<?php }?>	
													<?php }?>
												}
												else{
													disablealltextsnselects(this,val);
												}
												//disableSavenUpload();
									});
						</script>
					</td>
					<?php $shop_ind++;?>
				<?php 
				}?>
				</tr>
				<?php 
				//$query="SELECT option_sku,option_image,barcode_type,jetvar.option_id,option_qty,option_price,option_unique_id,variant_option1,variant_option2,variant_option3,walvar.new_variant_option_1,walvar.new_variant_option_2,walvar.new_variant_option_3 FROM `walmart_product_variants` AS walvar INNER JOIN `jet_product_variants` AS jetvar ON walvar.product_id = jetvar.product_id WHERE jetvar.product_id='".$product_id."' order by jetvar.option_sku='".$sku."' desc, jetvar.option_id asc";
				$query="SELECT option_sku,option_image,barcode_type,jetvar.option_id,option_qty,option_price,option_unique_id,variant_option1,variant_option2,variant_option3,walvar.new_variant_option_1,walvar.new_variant_option_2,walvar.new_variant_option_3 FROM `walmart_product_variants` AS walvar INNER JOIN `jet_product_variants` AS jetvar ON walvar.option_id = jetvar.option_id WHERE jetvar.product_id='".$product_id."' order by jetvar.option_sku='".$sku."' desc, jetvar.option_id asc";
				$productOptions= Data::sqlRecords($query,"all","select");
				?>
				<?php $vairent_count=0;?>
				<?php 
				$value="";
				if(is_array($productOptions) && count($productOptions)>0)
                {
                $value="";
				foreach($productOptions as $value)
				{
    				?>
    				<?php if(trim($value['option_sku'])=="")
    				{
    						continue;
    				}?>
    				<tr>
    					<td>
    						<?=$value['option_sku']?>
    					</td>
    					<td>
    					   <?=$value['option_price']?>
    					</td>
    					<td>
    					   <?=$value['option_qty']?>
    						
    					</td>
    					<td colspan="2">
    						<label class="variant_as_parent" style="display:none;"><?php if(trim($model->jet_product->sku)==trim($value['option_sku'])){echo "1";}else{echo "0";}?></label>
    						<label class="variant_option_id" style="display:none;"><?=$value['option_id']?></label>
    						<label class="variant_option_sku" style="display:none;"><?=$value['option_sku']?></label>
    						<label class="variant_product_id" style="display:none;"><?=$model->product_id?></label>
    						<input type="text" style="" class="upc_opt form-control" value="<?=$value['option_unique_id']?>" name="jet_varients_opt[<?=$value['option_id']?>][upc]">
    						<input type="text" class="upc_opt_type form-control" value="<?=$value['barcode_type']?>" name="jet_varients_opt[<?=$value['option_id']?>][barcode_type]" readonly  <?php if("ISBN-13"!=trim($value['barcode_type']) && "EAN"!=trim($value['barcode_type']) && ""!=trim($value['barcode_type'])){echo "style='display:inline-block;'";}else{echo "disabled='disabled' style='display:none;'";}?>/>
    						<select class="upc_opt_select form-control" name="jet_varients_opt[<?=$value['option_id']?>][barcode_type]" <?php if("ISBN-13"==trim($value['barcode_type']) || "EAN"==trim($value['barcode_type'])){echo "style='display:inline-block;'";}else{echo "disabled='disabled' style='display:none;'";}?>>
    							<option value="ISBN-13" <?php if("ISBN-13"==trim($value['barcode_type'])){echo "selected='selected'";}?>>ISBN-13</option>
    							<option value="EAN" <?php if("EAN"==trim($value['barcode_type'])){echo "selected='selected'";}?>>EAN</option>
    						</select>
    						<label class="variant_as_parent" style="display:none;"><?php if(trim($model->jet_product->sku)==trim($value['option_sku'])){echo "1";}else{echo "0";}?></label>
    						<label class="variant_option_id" style="display:none;"><?=$value['option_id']?></label>
    						<label class="variant_option_sku" style="display:none;"><?=$value['option_sku']?></label>
    						<label class="variant_product_id" style="display:none;"><?=$model->product_id?></label>
    						<input type="hidden"  value="<?=$value['option_sku']?>" name="jet_varients_opt[<?=$value['option_id']?>][optionsku]">
    					</td>

    					<?php if($value['variant_option1'])
    					{?>
    						<td>
    							<!--<div class="div-first"><center><b>Previous Value</b></center></div>-->
    							<input type="text" class="form-control" readonly="" value="<?=$value['variant_option1']?>" name="jet_attributes[<?=$shopifyArr[0]?>][<?=$value['option_id']?>][value]">
    							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
    								<?php $k=0;?>
    								<?php foreach($dropdown_option_arr as $p_ar){?>
    										<?php if($k==0){?>
    											<!--<div class="div-second" style="display:none;"><center><b>Enter New Value</b></center></div>-->
    											<input style="display:none;" type="text" class="form-control input_<?=trim($shopifyArr[0])?>" placeholder="Fill New Value" value="<?php if($value['new_variant_option_1']){echo $value['new_variant_option_1'];}else{echo $value['variant_option1'];}?>" name="jet_attributes[<?=$shopifyArr[0]?>][<?=$value['option_id']?>][value]" disabled="disabled" readonly="readonly"/>
    										<?php }?>
    										<?php if(array_key_exists('select_fields',$p_ar) && is_array($p_ar['select_fields']) && count($p_ar['select_fields'])>0){?>
    											<?php $flg_val=false;?>
    											<?php if(trim($attrArr[0])==trim($p_ar['value'])){?>
    													<?php $flg_val=true;?>
    											<?php }?>
    											<select <?php if(!$flg_val){echo "style='display:none;'  disabled='disabled'";} ?> class="form-control select_<?=trim($shopifyArr[0])?> input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?> input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>" onchange="optionselectedbyjetattr(this);" >
    													<?php foreach($p_ar['select_fields'] as $ky=>$sel_op){?>
    															<option value="<?=trim($sel_op)?>" <?php if(trim($value['new_variant_option_1'])==trim($sel_op)){echo "selected='selected'";}?>><?=trim($sel_op)?></option>
    													<?php }?>
    											</select>
    											<?php if($flg_val){?>
    													<script type="text/javascript">
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").css({"display":"block","width":"60%"});
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).prop( "disabled",true);
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).css( "display",'none');
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).prop( "disabled",false);
    													</script>
    											<?php }?>
    										<?php }?>
    										<?php $k++;?>
    								<?php }?>
    							<?php }?>
    						</td>
    					<?php 
    					}
    					if($value['variant_option2'])
    					{?>
    						<td>
    							<!--<div class="div-first"><center><b>Previous Value</b></center></div>-->
    							<input type="text" class="form-control" readonly="" value="<?=$value['variant_option2']?>" name="jet_attributes[<?=$shopifyArr[1]?>][<?=$value['option_id']?>][value]">
    							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
    								<?php $k=0;?>
    								<?php foreach($dropdown_option_arr as $p_ar){?>
    										<?php if($k==0){?>
    												<!--<div class="div-second" style="display:none;"><center><b>Enter New Value</b></center></div>-->
    												<input style="display:none;" type="text" class="form-control input_<?=trim($shopifyArr[1])?>" placeholder="Fill New Value" value="<?php if($value['new_variant_option_2']){echo $value['new_variant_option_2'];}else{echo $value['variant_option2'];}?>" name="jet_attributes[<?=$shopifyArr[1]?>][<?=$value['option_id']?>][value]" disabled="disabled" readonly="readonly"/>
    										<?php }?>
    										<?php if(array_key_exists('select_fields',$p_ar) && is_array($p_ar['select_fields']) && count($p_ar['select_fields'])>0){?>
    											<?php $flg_val=false;?>
    											<?php if(trim($attrArr[1])==trim($p_ar['value'])){?>
    													<?php $flg_val=true;?>
    											<?php }?>
    											<select <?php if(!$flg_val){echo "style='display:none;'  disabled='disabled'";} ?>  class="form-control select_<?=trim($shopifyArr[1])?>  input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?> input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>" onchange="optionselectedbyjetattr(this);" >
    													<?php foreach($p_ar['select_fields'] as $ky=>$sel_op){?>
    															<option value="<?=trim($sel_op)?>" <?php if(trim($value['new_variant_option_2'])==trim($sel_op)){echo "selected='selected'";}?>><?=trim($sel_op)?></option>
    													<?php }?>
    											</select>
    											<?php if($flg_val){?>
    													<script type="text/javascript">
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").css({"display":"block","width":"60%"});
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).prop( "disabled",true);
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).css( "display",'none');
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).prop( "disabled",false);
    													</script>
    											<?php }?>
    										<?php }?>
    										<?php $k++;?>
    								<?php }?>
    							<?php }?>
    						</td>
    					<?php 
    					}
    					if($value['variant_option3'])
    					{?>
    						<td>
    							<!--<div class="div-first"><center><b>Previous Value</b></center></div>-->
    							<input type="text" class="form-control" readonly="" value="<?=$value['variant_option3']?>" name="jet_attributes[<?=$shopifyArr[2]?>][<?=$value['option_id']?>][value]">
    							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
    								<?php $k=0;?>
    								<?php foreach($dropdown_option_arr as $p_ar){?>
    										<?php if($k==0){?>
    											<!--<div class="div-second" style="display:none;"><center><b>Enter New Value</b></center></div>-->
    											<input style="display:none;" type="text" class="form-control input_<?=trim($shopifyArr[2])?>" placeholder="Fill New Value" value="<?php if($value['new_variant_option_3']){echo $value['new_variant_option_3'];}else{echo $value['variant_option3'];}?>" name="jet_attributes[<?=$shopifyArr[2]?>][<?=$value['option_id']?>][value]" disabled="disabled" readonly="readonly"/>
    										<?php }?>
    										<?php if(array_key_exists('select_fields',$p_ar) && is_array($p_ar['select_fields']) && count($p_ar['select_fields'])>0){?>
    											<?php $flg_val=false;?>
    											<?php if(trim($attrArr[2])==trim($p_ar['value'])){?>
    													<?php $flg_val=true;?>
    											<?php }?>
    											<select <?php if(!$flg_val){echo "style='display:none;'  disabled='disabled'"; } ?>  class="form-control select_<?=trim($shopifyArr[2])?>  input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?> input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>" onchange="optionselectedbyjetattr(this);" >
    													<?php foreach($p_ar['select_fields'] as $ky=>$sel_op){?>
    															<option value="<?=trim($sel_op)?>" <?php if(trim($value['new_variant_option_3'])==trim($sel_op)){echo "selected='selected'";}?>><?=trim($sel_op)?></option>
    													<?php }?>
    											</select>
    											<?php if($flg_val){?>
    													<script type="text/javascript">
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").css({"display":"block","width":"60%"});
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).prop( "disabled",true);
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).css( "display",'none');
    														j$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).prop( "disabled",false);
    													</script>
    											<?php }?>
    										<?php }?>
    										<?php $k++;?>
    								<?php }?>
    							<?php }?>
    						</td>
    					<?php 
    					}?>
    			    </tr>
    				<?php $vairent_count++;
				}
				}?>
			</tbody>
		</table>
		<?php 
		}
		unset($productOptions);
		?>
		<?php //die("v");?>
    	<script type="text/javascript">
            function optionselectedbyjetattr(ele){
            		var value=j$(ele).find("option:selected").val();
            		j$( ele ).parent().find('input[type=text]').eq(1).val(value);
            		//console.log(j$(ele).prop('class'));
            }
            function disablealltextsnselects(ele,value)
            {
            		var string=j$(ele).prop('id');
            		var ele_name=string.replace('sel_','');
            		var input_ele_name=".input_"+ele_name;
            		var select_ele_name=".select_"+ele_name;
            		j$(select_ele_name).each(function() {
            				j$( this ).prop( "disabled",true);
            				j$( this ).css( "display",'none');
            				
            		});
            		j$(input_ele_name).each(function() {
            				j$( this ).prop( "disabled",true);
            				j$( this ).css( "display",'none');
            				//j$( this ).prop( "readonly",true);
            				j$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",false);
            				j$( this ).parent().find('input[type=text]').eq(0).prop( "style",'');
            				j$( this ).parent().find('input[type=text]').eq(0).css( "display",'block');
            				//j$( this ).parent().find('div.div-second').css( "display",'none');
            				//j$( this ).parent().find('div.div-first').css( "display",'block');
            		});
            		
            }
            function displayalltexts(ele,value)
            {
            		var string=j$(ele).prop('id');
            		var ele_name=string.replace('sel_','');
            		var input_ele_name=".input_"+ele_name;
            		var select_ele_name=".select_"+ele_name;
            		j$(select_ele_name).each(function() {
            				j$( this ).prop( "disabled",true);
            				j$( this ).css( "display",'none');
            		});
            		j$(input_ele_name).each(function() {
            				j$( this ).prop( "disabled",false);
            				j$( this ).prop( "style",'');
            				j$( this ).css( "display",'block');
            				//j$( this ).css({"display":"block","width":"60%"});
            				j$( this ).parent().find('input[type=text]').eq(1).val(j$( this ).parent().find('input[type=text]').eq(0).val());
            				//j$( this ).prop( "readonly",false);
            				//j$( this ).prop( "readonly",true);
            				//j$( this ).parent().find('div.div-first').css( "display",'none');
            				//j$( this ).parent().find('div.div-second').css( "display",'block');
            				j$( this ).val(j$( this ).parent().find('input[type=text]').eq(0).val());
            				j$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",true);
            				j$( this ).parent().find('input[type=text]').eq(0).css( "display",'none');
            				j$( this ).parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
            		});
            
            }
            function displayallselects(ele,value)
            {
            		var string=j$(ele).prop('id');
            		var ele_name=string.replace('sel_','');
            		var select_ele_name=".select_"+ele_name;
            		var input_ele_name=".input_"+ele_name;
            		ele_name=".input_"+value+"_"+ele_name;
            		j$(select_ele_name).each(function() {
            				j$( this ).prop( "disabled",true);
            				j$( this ).css( "display",'none');
            				j$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",false);
            		});
            		j$(ele_name).each(function() {
            				j$( this ).prop( "disabled",false);
            				//j$( this ).css( "display",'block');
            				j$( this ).css({"display":"block","width":"60%"});
            				//j$( this ).parent().find('div.div-first').css( "display",'none');
            				//j$( this ).parent().find('div.div-second').css( "display",'block');
            				j$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",true);
            				//j$( this ).parent().find('input[type=text]').eq(0).css( "display",'none');
            				j$( this ).parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
            				j$( this ).parent().find('input[type=text]').eq(1).prop( "disabled",false);
            				//j$( this ).parent().find('input[type=text]').eq(1).prop( "readonly",true);
            				j$( this ).parent().find('input[type=text]').eq(1).css( "display",'none');
            				j$( this ).parent().find('input[type=text]').eq(1).val(j$(this).find("option:selected").text());
            		});
            }
            function checksame()
            {
            	var karr=[];
            	var a="";
            	var z="";
            	<?php foreach($shopifyattribues as $key=>$val){?>
            				karr.push("#sel_<?=$key?> option:selected");
            	<?php }?>
            
            	for(k=0;k<karr.length;k++){
            			for(b=k+1;b<karr.length;b++){
            					a="";
            					z="";
            					a=j$(karr[k]).val();
            					z=j$(karr[b]).val();
            					if(a==z && a!=""){
            							return false;
            					}
            			}
            	}
            	return true;	
            }
            function checkBlankInputsBeforeSubmit()
            {
            		var parr=[];
            		<?php foreach($shopifyattribues as $key=>$val){?>
            			parr.push(".input_<?=$key?>");
            		<?php }?>
            		var isDisabled_new ="";
            		var old_val="";
            		var new_val="";
            		var isDisabled_old ="";
            		var retflag=false;
            		for(k=0;k<parr.length;k++){
            			j$(parr[k]).each(function(e){
            					isDisabled_new = "";
            					new_val="";
            					isDisabled_old ="";
            					old_val="";
            					isDisabled_new = j$(parr[k]).is(':disabled');
            					new_val= j$(parr[k]).val();
            					isDisabled_old = j$(parr[k]).parent().find('input[type=text]').eq(0).is(':disabled');
            					old_val=j$(parr[k]).parent().find('input[type=text]').eq(0).val();
            					if(isDisabled_new && isDisabled_old){
            							retflag=true;
            							return false;
            					}else{
            						if(isDisabled_new && old_val==""){
            							retflag=true;
            							return false;
            						}
            						if(isDisabled_old && new_val==""){
            							retflag=true;
            							return false;
            						}
            					}
            			});
            			if(retflag){
            				return false;
            			}
            		}
            		if(retflag){
            				return false;
            		}
            		return true;
            }
            function checkselectedBeforeSubmit(){
            	var sarr=[];
            	var g="";
            	var addhtml="";
            	<?php foreach($shopifyattribues as $key=>$val){?>
            			sarr.push("#sel_<?=$key?> option:selected");
            	<?php }?>
            	if(!checkBlankInputsBeforeSubmit()){
            		addhtml="Please fill all Shopify Option(s).";
            		j$('.v_error_msg').html(addhtml);
            		j$('.v_error_msg').show();
            		return false;
            	}
            	for(m=0;m<sarr.length;m++){
            		g="";
            		g=j$(sarr[m]).val();
            		if(g!=""){
            			return true;
            		}
            	}
            	j$('.v_error_msg').html("Please map atleast one variant option with Walmart Attribute.");
            	j$('.v_error_msg').show();
            	return false;
            }

			<?php /*?>
		

			
			
			j$('#saveedit').click(function(e) {
					e.preventDefault(e);
					e.stopPropagation();
					if(checkselectedBeforeSubmit()){
					  		j$('form').submit();
					}else{
					  		return false;
					}
				  
			});

	
		disableSavenUploadOnLoadFirst();<?php */?>
		//insertBarcodeType();
		j$("#jetproduct-upc").prop("readonly", true); 
		j$("#jetproduct-asin").prop("readonly", true); 
    </script>
    		
<?php 
		unset($result);
		unset($attributes);

}else{?>
	<span class="no_attribute_in_category">No attributes for the selected Category</span>
	
<?php }?>