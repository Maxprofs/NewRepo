<?php 
	use frontend\modules\jet\components\Data;
	use yii\helpers\Html;
	$dropdown_option_arr = [];
	$id=$model->fulfillment_node;
	$product_type=$model->type;
	$product_id=$model->id;
	$sku=$model->sku;
	$merchant_id= $model->merchant_id;	
    $v=0;
	$isSize = $isColor = $isPattern = false;
	if(is_array($attributes) && count($attributes)>0)
	{
		foreach ($attributes as $value)
		{			
			if(isset($value['units'],$resultAttr['units']))//if(isset($value['units']))
    	    {
				$unitArray = $opt_arr = [];
				$unitArray=explode(',',$resultAttr['units']);				
				$opt_arr['name']=$value['attribute_description'];
		        $opt_arr['value']=$value['attribute_id'];
				$opt_arr['select']=$value['units'];
				$dropdown_option_arr[]=$opt_arr;
				$v++;            
			}
			elseif(isset($value['attribute_description']) &&  $value['attribute_description']=='Inseam')
		    {
		        $opt_arr = [];
		        $opt_arr['name']=$value['attribute_description'];
		        $opt_arr['value']=$value['attribute_id'];
		        $opt_arr['select_fields']=$value['values'];
		        $dropdown_option_arr[]=$opt_arr;
		        $v++;
			}
			elseif($value['attribute_id']==50)
		    {
		    	$isSize=true;
		        $opt_arr = [];
		        $opt_arr['name']=$value['attribute_description'];
		        $opt_arr['value']=$value['attribute_id'];
		        $dropdown_option_arr[]=$opt_arr;
		        $v++;
			}
			elseif($value['attribute_id']==513842408435658)
		    {
		    	$isPattern=true;
		        $opt_arr = [];
		        $opt_arr['name']=$value['attribute_description'];
		        $opt_arr['value']=$value['attribute_id'];
		        $dropdown_option_arr[]=$opt_arr;
		        $v++;
			}
			elseif($value['attribute_id']==2 || $value['attribute_id']==119 || $value['attribute_description']=='Color')
		    {
		    	$isColor=true;
		    	$opt_arr = [];
		        $opt_arr['name']=$value['attribute_description'];
		        $opt_arr['value']=119;
		        $dropdown_option_arr[]=$opt_arr;
		        $v++;
		    }
		    else
		    {
		    	$opt_arr = [];
		        $opt_arr['name']=$value['attribute_description'];
		        $opt_arr['value']=$value['attribute_id'];
		        $dropdown_option_arr[]=$opt_arr;
		        $v++;
		    }
		}
	}	
	if(!$isColor){
		$dropdown_option_arr[]= ['name'=>'Color','value'=>'119'];
	}
	if(!$isSize){
		$dropdown_option_arr[]= ['name'=>'Size','value'=>'50'];
	}
	if(!$isPattern){
		$dropdown_option_arr[]= ['name'=>'Pattern','value'=>'513842408435658'];
	}
	unset($attributes,$result);
		if($product_type=='variants')
		{
			$shopifyattribues = $jet_attributes_arr = [];
			$shopifyattribues=json_decode(stripslashes($model->attr_ids),true);
			
			if($model->jet_attributes!=""){
					$jet_attributes_arr=json_decode($model->jet_attributes,true);
			}?>
		<table class="table table-striped table-bordered">
    		<thead>
    			<tr>
    				<th>
						<p data-toggle="tooltip" title="Stock Keeping Unit">SKU / Upload on Jet</p>
    				</th>
    				<th>
						<p data-toggle="tooltip" title="Product Image">Image</p>
    				</th>
    				<th>
						<p data-toggle="tooltip" title="Product Price">Price</p>
    				</th>
    				<th>
						<p data-toggle="tooltip" title="Product inventory">Qty</p>
    				</th>
    				<th colspan="2">
						<p data-toggle="tooltip" title="Must be one of the following values in numeric: GTIN-14 (14 digits), EAN (13 digits), ISBN-10 (10 digits), ISBN-13 (13 digits), UPC (12 digits)" >Barcode</p>
    				</th>
    				<th colspan="2">
    					<p data-toggle="tooltip" title="ASIN must be Alphanumeric with length of 10" >ASIN</p>
    				</th>
					<th>
    					<p data-toggle="tooltip" title="Manufacturer Part number provided by the original manufacturer of the merchant SKU." style="text-align: center">MPN</p>
    				</th>
    				<th colspan="3">
    					<p data-toggle="tooltip" title="Map Jet Attribute with shopify options" style="text-align: center">Map Jet Attributes</p>
    				</th>
    			</tr>
    		</thead>
			<tbody>	
				<tr>
					<td rowspan="2" colspan="1">
						<input type="hidden" name="product-type" value="variants"/>
					</td>
					<td rowspan="2" colspan="1">
					</td>
					<td rowspan="2" colspan="1">
					</td>
					<td rowspan="2" colspan="1">
					</td>
					<td rowspan="2" colspan="2">
					</td>
					<td rowspan="2" colspan="2">
					</td>
					<td rowspan="2" colspan="1">
					</td>
					<?php 
						$shopifyArr = $attrArr = [];
					foreach($shopifyattribues as $key=>$val){?>
						<td>
							<div><b><center>Shopify Option</center></b></div>
							<div><center><?=$val?></center></div>
						</td>
						<?php $shopifyArr[]=$key;
					}?>
					</tr>
				<tr>
				<?php 
				$shop_ind=0;
				foreach($shopifyattribues as $key=>$val)
				{

					$bool=false;
					$attr_value="";
					$attr_value=Data::checkMappedAttributes($val,$merchant_id);
					if(!$attr_value)
					{
						if($jet_attributes_arr && count($jet_attributes_arr)>0)
						{
							$bool=array_key_exists ($val,$jet_attributes_arr);
						}
						if($bool)
						{
							$attr_value=$jet_attributes_arr[$val][0];
						}
					}
					if(Yii::$app->getRequest()->getQueryParam('clearattr')==1){
						$attr_value="";
					}
					$app_html='';
					
		            if(isset($jet_attributes_arr[$val]) && count($jet_attributes_arr[$val])==2){
					    $app_html=$jet_attributes_arr[$val][1];
					}
					 $attrArr[$shop_ind]=trim($attr_value);?>
					<td>
						<div><b><center>Jet attribute(s)</center></b></div>
						<select class="jet_attributes_selector form-control" id="sel_<?=$key?>" name="jet_attributes[<?=$key?>][jet_attr_id]">
							<option value="">Select a Jet Attribute...</option>
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
							$("#sel_<?=$key?>").change(function()
							{
								if(!checksame())
								{
										$('.v_error_msg').html("Please don't choose same Jet Attibute for multiple option(s).");
										$('.v_error_msg').show();
										$("#sel_<?=$key?>").val("");
										disablealltextsnselects(this,"");
										return false;
								}else{
									 	$('.v_error_msg').html("");
									 	$('.v_error_msg').hide();
								}

								var val=$("#sel_<?=$key?> option:selected").val();
								<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
										<?php foreach($dropdown_option_arr as $p_ar){?>
											<?php if(array_key_exists('select',$p_ar) && is_array($p_ar['select']) && count($p_ar['select'])>0){?>
													$("#opt_<?=$key?>_<?=$p_ar['value']?>").prop('disabled',true);
													$(".unit_dropdown_div_<?=$key?>_<?=$p_ar['value']?>").css('display','none');
													if(val==<?=trim($p_ar['value'])?>)
													{
														$("#opt_<?=$key?>_<?=$p_ar['value']?>").prop('disabled',false);
														$(".unit_dropdown_div_<?=$key?>_<?=$p_ar['value']?>").css('display','block');
													}
											<?php }?>
										<?php }?>
								<?php }?>
								if(val!="")
								{
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

				$query = 'SELECT option_sku,option_image,option_id,option_qty,COALESCE(update_option_price,option_price) as option_price ,option_unique_id,barcode_type,asin,option_mpn,variant_option1,variant_option2,variant_option3,new_variant_option_1,new_variant_option_2,new_variant_option_3,status,upload_on_jet FROM `jet_product_variants` WHERE product_id='.$product_id.' order by option_sku="'.$sku.'" desc, option_id asc';
				$productOptions= Data::sqlRecords($query,'all','select');
				
				$vairent_count=0; 
				$value="";
				foreach($productOptions as $value)
				{
    				if(trim($value['option_sku'])=="")
    					continue;
    				?>
    				<tr>    					
    					<td>
    						<?php 
                                $url = "https://".SHOP."/admin/products/".$product_id.'/variants/'.$value['option_id'];
                               echo  Html::a($value['option_sku'], $url, ['title' => 'SKU', 'target' => '_blank']) ; 
                            ?>
                            	<select id="upload_on_jet-<?= $value['option_id'] ?>" name="jet_varients_opt[<?= $value['option_id'] ?>][upload_on_jet]">
								    <option value="yes">Yes</option>
								    <option value="no" <?php if (isset($value['upload_on_jet']) && $value['upload_on_jet']=='no') echo "selected='selected'" ?> >No</option>
								</select>
    						<?php 
    							if ($sku == $value['option_sku'])
    							{
    								?>
    									<script type="text/javascript"> 
    										$('#upload_on_jet-'+<?= $value['option_id'] ?>).css('display','none');
    									</script>
    								<?    								
    							}
    						?>                              
							<p>
                                <span style="border: 1px solid #333; padding: 0 5px; color: #555"><?=$value['status']?></span>
                            </p>                         
    					</td>
    					<td>
    						<?php 
    							$arrr = explode(",", $value['option_image']);
    							if (trim($arrr[0])!="") 
    							{
    								?>
    									<img  width="70px" height="70px" src="<?=$arrr[0];?>">	
    								<?		
    							}
    							else
    							{
    								?>
    									<img width="70px" height="70px" src="<?=Yii::$app->getUrlManager()->getBaseUrl()?>/images/noimage.png">	
    								<?
    							}
    						?>	
    					</td> 
    					<td>    						
    						<div>
                                <div class="pull-left">                                    
                                    <input class="form-control jet-price" type="text" id="jet_product_price<?= $value['option_id']; ?>"
                                           name="jet_varients_opt[<?= $value['option_id'] ?>][option_update_price]" value="<?= (float)$value['option_price'] ?>">
                                </div>
                                <div class="pull-left">
                                    <button class="toggle_editor jet-price-button" type="button" onClick="jetPrice(this);" title="Click to update price on app and Jet" product-id="<?= $model->id ?>" product-type="<?= 'variants' ?>" option-id="<?= $value['option_id'] ?>" sku="<?= $value['option_sku'] ?>" option-price="<?= (float)$value['option_price'] ?>">Update
                                    </button>

                                </div>
                                <div class="clear"></div>
                            </div>    						
    					</td>
    					<td>
    						<div>
                                <div class="pull-left">
                                    <input class="form-control jet-inventory" type="text"
                                           id="jet_product_inventory<?= $value['option_id']; ?>"
                                           name="jet_varients_opt[<?= $value['option_id'] ?>][option_qty]"
                                           value="<?= $value['option_qty'];?>">                                    
                                </div>
                                <div class="pull-left">
                                    <button class="toggle_editor jet-inventory-button" type="button"
                                            onClick="jetInventory(this);" title="Click to update Qty on shopify store, app and Jet"
                                            product-id="<?= $model->id ?>" product-type="<?= 'variants' ?>"
                                            option-id="<?= $value['option_id'] ?>" sku="<?= $value['option_sku'] ?>"
                                            option-inventory="<?= (float)$value['option_qty'] ?>">Update
                                    </button>
                                </div>
                                <div class="clear"></div>
                            </div>    						
    					</td>   					
    					<td colspan="2" class="pencil-parent">    						    						
    						<input type="text" class="upc_opt form-control" value="<?=$value['option_unique_id']?>" name="jet_varients_opt[<?=$value['option_id']?>][option_unique_id]">
    						<input type="hidden" class="option_sku form-control" value="<?=$value['option_sku']?>" name="jet_varients_opt[<?=$value['option_id']?>][optionsku]">
    						<span class="glyphicon glyphicon glyphicon-pencil"></span>
    					</td>
    					<td colspan="2" class="pencil-parent">    						
    						<input type="text" class="asin_opt form-control" value="<?=$value['asin']?>" name="jet_varients_opt[<?=$value['option_id']?>][asin]">
    						<span class="glyphicon glyphicon glyphicon-pencil"></span>
    					</td>
						<td colspan="1" class="pencil-parent">
							<input type="text" class="mpn_opt form-control" value="<?= $value['option_mpn'] ?>" name="jet_varients_opt[<?=$value['option_id']?>][mpn]">
							<span class="glyphicon glyphicon glyphicon-pencil"></span>
    					</td>
    					<?php if($value['variant_option1'])
    					{?>
    						<td>
    							<input type="text" class="form-control" readonly="" value="<?=$value['variant_option1']?>" name="jet_attributes[<?=$shopifyArr[0]?>][<?=$value['option_id']?>][value]">
    							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
    								<?php $k=0;?>
    								<?php foreach($dropdown_option_arr as $p_ar){?>
    										<?php if($k==0){?>    											
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
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").css({"display":"block","width":"60%"});
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).prop( "disabled",true);
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).css( "display",'none');
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[0])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).prop( "disabled",false);
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
    							<input type="text" class="form-control" readonly="" value="<?=$value['variant_option2']?>" name="jet_attributes[<?=$shopifyArr[1]?>][<?=$value['option_id']?>][value]">
    							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
    								<?php $k=0;?>
    								<?php foreach($dropdown_option_arr as $p_ar){?>
    										<?php if($k==0){?>
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
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").css({"display":"block","width":"60%"});
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).prop( "disabled",true);
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).css( "display",'none');
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[1])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).prop( "disabled",false);
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
    							<input type="text" class="form-control" readonly="" value="<?=$value['variant_option3']?>" name="jet_attributes[<?=$shopifyArr[2]?>][<?=$value['option_id']?>][value]">
    							<?php if(is_array($dropdown_option_arr) && count($dropdown_option_arr)>0){?>
    								<?php $k=0;?>
    								<?php foreach($dropdown_option_arr as $p_ar){?>
    										<?php if($k==0){?>
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
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").css({"display":"block","width":"60%"});
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).prop( "disabled",true);
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).css( "display",'none');
    														$(".input_<?=trim($p_ar['value'])?>_<?=trim($shopifyArr[2])?>_<?=$vairent_count?>").parent().find('input[type=text]').eq(1).prop( "disabled",false);
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
				}?>
			</tbody>
		</table>
		<?php 
		}
		unset($productOptions);
		?>
		 
    	<script type="text/javascript">
            function optionselectedbyjetattr(ele){
        		var value=$(ele).find("option:selected").val();
        		$( ele ).parent().find('input[type=text]').eq(1).val(value);
            }
            function disablealltextsnselects(ele,value)
            {
        		var string=$(ele).prop('id');
        		var ele_name=string.replace('sel_','');
        		var input_ele_name=".input_"+ele_name;
        		var select_ele_name=".select_"+ele_name;
        		$(select_ele_name).each(function() {
    				$( this ).prop( "disabled",true);
    				$( this ).css( "display",'none');
        				
        		});
        		$(input_ele_name).each(function() {
    				$( this ).prop( "disabled",true);
    				$( this ).css( "display",'none');
    				$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",false);
    				$( this ).parent().find('input[type=text]').eq(0).prop( "style",'');
    				$( this ).parent().find('input[type=text]').eq(0).css( "display",'block');
        		});
            		
            }
            function displayalltexts(ele,value)
            {
        		var string=$(ele).prop('id');
        		var ele_name=string.replace('sel_','');
        		var input_ele_name=".input_"+ele_name;
        		var select_ele_name=".select_"+ele_name;
        		$(select_ele_name).each(function() {
    				$( this ).prop( "disabled",true);
    				$( this ).css( "display",'none');
        		});
        		$(input_ele_name).each(function() {
    				$( this ).prop( "disabled",false);
    				$( this ).prop( "style",'');
    				$( this ).css( "display",'block');
    				$( this ).parent().find('input[type=text]').eq(1).val($( this ).parent().find('input[type=text]').eq(0).val());            				
    				$( this ).val($( this ).parent().find('input[type=text]').eq(0).val());
    				$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",true);
    				$( this ).parent().find('input[type=text]').eq(0).css( "display",'none');
    				$( this ).parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
        		});
            
            }
            function displayallselects(ele,value)
            {
            		var string=$(ele).prop('id');
            		var ele_name=string.replace('sel_','');
            		var select_ele_name=".select_"+ele_name;
            		var input_ele_name=".input_"+ele_name;
            		ele_name=".input_"+value+"_"+ele_name;
            		$(select_ele_name).each(function() {
            				$( this ).prop( "disabled",true);
            				$( this ).css( "display",'none');
            				$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",false);
            		});
            		$(ele_name).each(function() {
            				$( this ).prop( "disabled",false);
            				$( this ).css({"display":"block","width":"60%"});
            				$( this ).parent().find('input[type=text]').eq(0).prop( "disabled",true);
            				$( this ).parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
            				$( this ).parent().find('input[type=text]').eq(1).prop( "disabled",false);
            				$( this ).parent().find('input[type=text]').eq(1).css( "display",'none');
            				$( this ).parent().find('input[type=text]').eq(1).val($(this).find("option:selected").text());
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
            
            	for(k=0;k<karr.length;k++)
            	{
        			for(b=k+1;b<karr.length;b++)
        			{
    					a="";
    					z="";
    					a=$(karr[k]).val();
    					z=$(karr[b]).val();
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
        		for(k=0;k<parr.length;k++)
        		{
        			$(parr[k]).each(function(e)
        			{
    					isDisabled_new = "";
    					new_val="";
    					isDisabled_old ="";
    					old_val="";
    					isDisabled_new = $(parr[k]).is(':disabled');
    					new_val= $(parr[k]).val();
    					isDisabled_old = $(parr[k]).parent().find('input[type=text]').eq(0).is(':disabled');
    					old_val=$(parr[k]).parent().find('input[type=text]').eq(0).val();
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
            function checkselectedBeforeSubmit()
            {
            	var sarr=[];
            	var g="";
            	var addhtml="";
            	<?php foreach($shopifyattribues as $key=>$val){?>
            			sarr.push("#sel_<?=$key?> option:selected");
            	<?php }?>
            	if(!checkBlankInputsBeforeSubmit()){
            		addhtml="Please fill all Shopify Option(s).";
            		$('.v_error_msg').html(addhtml);
            		$('.v_error_msg').show();
            		return false;
            	}
            	for(m=0;m<sarr.length;m++){
            		g="";
            		g=$(sarr[m]).val();
            		if(g!=""){
            			return true;
            		}
            	}
            	$('.v_error_msg').html("Please map atleast one variant option with Jet Attribute.");
            	$('.v_error_msg').show();
            	return false;
            }
		$("#jetproduct-upc").prop("readonly", true); 
		$("#jetproduct-asin").prop("readonly", true); 
    </script>
    		
<?php 
	unset($result);unset($attributes);
?>
<script>
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip({html:true});
	});
</script>
