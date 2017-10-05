<?php 
		$dropdown_option_arr=array();
		$id=Yii::$app->getRequest()->getQueryParam('id');
    	$product_type=Yii::$app->getRequest()->getQueryParam('product_type');
    	$product_id=Yii::$app->getRequest()->getQueryParam('product_id');
    	$merchant_id= \Yii::$app->user->identity->id;
    	$html='';
    	$Attrmodel=$model;
        $connection = Yii::$app->getDb();
        $merchantCategory = $connection->createCommand("SELECT * FROM `jet_category` WHERE category_id='".$id."'");// AND merchant_id='".$merchant_id."'
        $result = $merchantCategory->queryOne();
		if($result['jet_attributes']){
			$attributes=explode(',',$result['jet_attributes']);
?>
<style>
.unit_dropdown_div_calss {
  padding-top: 8px;
}
.upc_opt{
	text-align:center;
}
.unit_dropdown_div_calss .unit_label {
  margin-top: 5px;
  width: auto !important;
  font-weight: normal;
}
.unit_dropdown_div_calss .unit_dropdown {
  float: right;
  width: 75% !important;
}
.field-jetproduct-jet_attributes .table-striped td{
      vertical-align: middle !important;
}
.table-striped .qty_opt.form-control {
  width: 60px;
}
.table-striped .price_opt.form-control {
  width: 70px;
}
.table-striped .upc_opt.form-control {
  width: 150px;
}
.field-jetproduct-jet_attributes .error-msg {
  color: #ff0000;
  display: block;
  font-size: 12px;
  line-height: 17px;
  padding-top: 7px;
}
/* .Attrubute_html .v_error_msg::before {
  background: rgb(255, 0, 0) none repeat scroll 0 0;
  border-radius: 100%;
  color: rgb(255, 255, 255);
  content: "x";
  display: inline-block;
  font-size: 17px;
  font-weight: bold;
  height: 20px;
  left: 0;
  line-height: 18px;
  position: absolute;
  text-align: center;
  top: 6px;
  width: 20px;
} */
.Attrubute_html .v_error_msg {
  border-radius: 4px;
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f2dede;
    border-color: #ebccd1;
    color: #a94442;

}
</style>
		<div class="v_error_msg" style="display:none;"></div>

		
			<!--<ul class="attribute_listing" style="display:none;">-->
			<?php $v=0;
			foreach ($attributes as $value)
			{
				$result="";
				$result=$Attrmodel->find()->where(['id' => $value])->one();
				if($result=="")
					continue;
				$attrName=$result->display_name;
				$attrDes=$result->description;
				$resultAttr = $connection->createCommand("SELECT * FROM `jet_attribute_value` WHERE attribute_id='".$value."'")->queryOne();
				$attrvalues=explode(',',$resultAttr['value']);
				
				if($result->free_text==0 && $result->display=='TRUE' && $result->variant=='TRUE'){
					if($product_type=='variants')
					{?>
						<?php 	$opt_arr=array();
								$opt_arr['name']=$attrDes;
								$opt_arr['value']=$value;
								if(is_array($attrvalues) && count($attrvalues)>0){
									if($attrvalues[0]!=""){
										$opt_arr['select_fields']=$attrvalues;
									}
								}
								$dropdown_option_arr[]=$opt_arr;
						?>
						<!--<li class="text-values"><span><?=$attrDes?></span>&nbsp<span class="attribute_id">(Jet Attribute Id: <?=$value ?>)</span></li>
						<div style="clear:both"></div>-->
					<?php	$v++;
					 }
					
				}
				elseif($result->free_text==1 && $result->display=='TRUE' && $result->variant=='TRUE')
				{
					if($product_type=='variants')
					{?>
						<?php 	$opt_arr=array();
								$opt_arr['name']=$attrDes;
								$opt_arr['value']=$value;
								$dropdown_option_arr[]=$opt_arr;
						?>
						<!--<li class="text-values"><span><?=$attrDes?></span>&nbsp<span class="attribute_id">(Jet Attribute Id:<?=$value?>)</span></li>
						<div style="clear:both"></div>-->
				<?php $v++;
				 	}
				}
				elseif($result->free_text==2 && $result->display=='TRUE' && $result->variant=='TRUE')
				{
					$unitArray=array();
					$unitArray=explode(',',$resultAttr['units']);

					if($product_type=='variants')
					{?>
						<!--<li class="text-values"><span><?=$attrDes?></span>&nbsp<span class="attribute_id">(Jet Attribute Id:<?=$value?>)</span></li>-->
						
						<?php 	$opt_arr=array();
								$opt_arr['name']=$attrDes;
								$opt_arr['value']=$value;
								if(is_array($unitArray) && count($unitArray)>0){
										$opt_arr['select']=$unitArray;
								}
								$dropdown_option_arr[]=$opt_arr;
						?>
						
						<!--<span class="text-validator">Please select unit if you want to map this jet attribute with given variant option(s).</span></li>
						<div style="clear:both"></div>-->
						<?php $v++;
						                 
				}
					
				}
			}?>
			<!--</ul>-->
			
			<?php if($product_type=='variants'){
				$shopifyattribues=array();
				$product= $connection->createCommand("SELECT * FROM `jet_product` WHERE id='".$product_id."' AND merchant_id='".$merchant_id."'");
				$resultProduct = $product->queryOne();
				$shopifyattribues=json_decode($resultProduct['attr_ids'],true);
				$jet_attributes_arr=array();
				if($resultProduct['jet_attributes']!=""){
						$jet_attributes_arr=json_decode($resultProduct['jet_attributes'],true);
				}?>
				<!--<label class="control-label" for="jetproduct-jet_attributes">Variant Options</label>-->
				<table class="table table-striped table-bordered">
					<tr>
						<th colspan="2">
							<center>SKU</center>
						</th>
						<th>
							<center>Price</center>
						</th>
						<th>
							<center>Inventory</center>
						</th>
						<th colspan="3">
							<center>Barcode(UPC,GTIN, etc)</center>
						</th>
						<th style="display:none">
							<center>MPN</center>
						</th>
						<th>
							<center>ASIN</center>
						</th>
						<th colspan="3">
							<center>Map Option(s) with Jet Attributes</center>
						</th>
					</tr>
					<tr>
						<td rowspan="2" colspan="2">
							<input type="hidden" name="product-type" value="variants"/>
						</td>
						
						<td rowspan="2">
						</td>
						<td rowspan="2">
						</td>
						<td rowspan="2" colspan="3">
						</td>
						<td rowspan="2">
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
				</tr><tr>
				<?php $shop_ind=0;?>
				<?php foreach($shopifyattribues as $key=>$val){
					$bool=false;
					if(count($jet_attributes_arr)>0){
						$bool=array_key_exists ( $val , $jet_attributes_arr);
					}
					$attr_value="";
					if($bool){
						$attr_value=$jet_attributes_arr[$val];
					}
					if(Yii::$app->getRequest()->getQueryParam('clearattr')==1){
						$attr_value="";
					}
					$app_html='';
					if($attr_value !="" && strstr($attr_value, ',')){
						$str_id="";
						$jet_attr_unit="";
						$str_id_arr=array();
						$str_id=trim($attr_value);
						$str_id_arr=explode(',',$str_id);
						$jet_attr_unit=trim($str_id_arr[1]);
						$app_html=$jet_attr_unit;
						$attr_value=trim($str_id_arr[0]);
					}?>
					<?php $attrArr[$shop_ind]=trim($attr_value);?>
					<!--<td><input type="text" placeholder="Jet Attribute Id" class="form-control" value="<?=$attr_value?>" name="jet_attributes[<?=$key?>][jet_attr_id]"/><?=$app_html?><input type="hidden" name="jet_attributes[<?=$key?>][jet_attr_name]" value="<?=$val?>"/></td>-->
					<td>
							<div><b><center>Jet Attribute(s) List</center></b></div>
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
												disableSavenUpload();
									});
									
							</script>
					</td>
					<?php $shop_ind++;?>
				<?php }?>
				</tr>
				<?php $productOptions= $connection->createCommand("SELECT * FROM `jet_product_variants` WHERE product_id='".$product_id."' AND merchant_id='".$merchant_id."'");
				$resultOptions = $productOptions->queryAll();?>
				<?php $vairent_count=0;?>
				<?php foreach ($resultOptions as $value){?>
					<?php if(trim($value['option_sku'])==""){
							continue;
					}?>
					<tr>
						<td>
							<div class="image_pro_div">
								<?php if(trim($value['option_image'])==""){?>
									<img src="<?php echo Yii::$app->request->baseurl."/images/no_image_product.png";?>" width="100px" height="80px"/>
								<?php }else{?>
									<img src="<?=$value['option_image']?>" width="100px" height="80px"/>
								<?php }?>
							</div>
							
						</td>
						<td>
							<?=$value['option_sku']?>
						</td>
						
						<td>
							<input type="text" class="price_opt form-control" value="<?=$value['option_price']?>" name="jet_varients_opt[<?=$value['option_id']?>][price]">
						</td>
						<td><input type="text" class="qty_opt form-control" value="<?=$value['option_qty']?>" name="jet_varients_opt[<?=$value['option_id']?>][qty]"></td>
						<td colspan="3">
							<label class="variant_as_parent" style="display:none;"><?php if(trim($resultProduct['sku'])==trim($value['option_sku'])){echo "1";}else{echo "0";}?></label>
							<label class="variant_option_id" style="display:none;"><?=$value['option_id']?></label>
							<label class="variant_option_sku" style="display:none;"><?=$value['option_sku']?></label>
							<label class="variant_product_id" style="display:none;"><?=$value['product_id']?></label>
							<input type="text" style="" class="upc_opt form-control" value="<?=$value['option_unique_id']?>" name="jet_varients_opt[<?=$value['option_id']?>][upc]">
							<input type="text" class="upc_opt_type form-control" value="<?=$value['barcode_type']?>" name="jet_varients_opt[<?=$value['option_id']?>][barcode_type]" readonly  <?php if("ISBN-13"!=trim($value['barcode_type']) && "EAN"!=trim($value['barcode_type']) && ""!=trim($value['barcode_type'])){echo "style='display:inline-block;'";}else{echo "disabled='disabled' style='display:none;'";}?>/>
							<select class="upc_opt_select form-control" name="jet_varients_opt[<?=$value['option_id']?>][barcode_type]" <?php if("ISBN-13"==trim($value['barcode_type']) || "EAN"==trim($value['barcode_type'])){echo "style='display:inline-block;'";}else{echo "disabled='disabled' style='display:none;'";}?>>
								<option value="ISBN-13" <?php if("ISBN-13"==trim($value['barcode_type'])){echo "selected='selected'";}?>>ISBN-13</option>
								<option value="EAN" <?php if("EAN"==trim($value['barcode_type'])){echo "selected='selected'";}?>>EAN</option>
							</select>
						</td>
						<td style="display:none"><input type="text" class="mpn_opt form-control" value="" name="jet_varients_opt[<?=$value['option_id']?>][mpn]"></td>
						<td>
							<label class="variant_as_parent" style="display:none;"><?php if(trim($resultProduct['sku'])==trim($value['option_sku'])){echo "1";}else{echo "0";}?></label>
							<label class="variant_option_id" style="display:none;"><?=$value['option_id']?></label>
							<label class="variant_option_sku" style="display:none;"><?=$value['option_sku']?></label>
							<label class="variant_product_id" style="display:none;"><?=$value['product_id']?></label>
							<input type="text" class="asin_opt form-control" value="<?=$value['asin']?>" name="jet_varients_opt[<?=$value['option_id']?>][asin]">
							<input type="hidden"  value="<?=$value['option_sku']?>" name="jet_varients_opt[<?=$value['option_id']?>][optionsku]">
						</td>
						<?php if($value['variant_option1']){?>
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
						<?php }
						if($value['variant_option2']){?>
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
						<?php }
						if($value['variant_option3']){?>
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
						<?php }?>
					</tr>
					<?php $vairent_count++;?>
				<?php }?>
				
			<?php }?>
			</table>
			<script type="text/javascript">
						function optionselectedbyjetattr(ele){
								var value=j$(ele).find("option:selected").val();
								j$( ele ).parent().find('input[type=text]').eq(1).val(value);
								//console.log(j$(ele).prop('class'));
						}
						function disablealltextsnselects(ele,value){
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
						function displayalltexts(ele,value){
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
						function displayallselects(ele,value){
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
										j$( this ).parent().find('input[type=text]').eq(0).css( "display",'block');
										j$( this ).parent().find('input[type=text]').eq(0).css({"width":"35%","margin-right":"1px","float":"left"});
										j$( this ).parent().find('input[type=text]').eq(1).prop( "disabled",false);
										//j$( this ).parent().find('input[type=text]').eq(1).prop( "readonly",true);
										j$( this ).parent().find('input[type=text]').eq(1).css( "display",'none');
										j$( this ).parent().find('input[type=text]').eq(1).val(j$(this).find("option:selected").text());
								});
						}
						function checksame(){
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
						function checkBlankInputsBeforeSubmit(){
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
							j$('.v_error_msg').html("Please map atleast one variant option with Jet Attribute.");
							j$('.v_error_msg').show();
							return false;
						}
						
						function checkUPCuniquenessOnLoad(ele){
								var current_value=j$(ele).val();
								var index=j$(".upc_opt").index(ele);
								var option_id=j$(ele).parent().find('label.variant_option_id').text();
								var variant_as_parent=j$(ele).parent().find('label.variant_as_parent').text();
								var product_id=j$(ele).parent().find('label.variant_product_id').text();
								var option_sku=j$(ele).parent().find('label.variant_option_sku').text();
								var checkpresentinsiblings=false;
								var parent_sku=j$("#jetproduct-sku").val();
								var parent_upc_value=j$("#jetproduct-upc").val();
								var parent_upc_type="";
								var current_option_upc_type="";
								if(j$("#jetproduct-upc").parent().find('select').length >0 && j$("#jetproduct-upc").parent().find('select').is(':enabled')){
									parent_upc_type=j$("#jetproduct-upc").parent().find('select').val();
								}
								if(j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
									parent_upc_type=j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
								}
								if(j$(ele).parent().find('select').length >0 && j$(ele).parent().find('select').is(':enabled')){
									current_option_upc_type=j$(ele).parent().find('select').val();
								}
								if(j$(ele).parent().find('.upc_opt_type').length >0 && j$(ele).parent().find('.upc_opt_type').is(':enabled')){
									current_option_upc_type=j$(ele).parent().find('.upc_opt_type').val();
								}
								if(option_sku!=parent_sku){
									 if(parent_upc_value==current_value && parent_upc_type==current_option_upc_type && current_option_upc_type !="" && current_value !=""){
									 		//j$(ele).parent().append('<span class="upc-error error-msg">Please insert different UPC from its Main Product.</span>');
										    //j$(ele).val("");
										    //disableSavenUpload();
										    return false;
									 }
									 		
								}
								j$(".upc_opt").each(function(inx,obj){
									 if(inx==index){
									 	return true;
									 }
									 var loop_option_upc_type="";
									 if(j$(obj).parent().find('.upc_opt_type').length >0 && j$(obj).parent().find('.upc_opt_type').is(':enabled')){
										loop_option_upc_type=j$(obj).parent().find('.upc_opt_type').val();
									 }
									 if(j$(obj).parent().find('select').length >0 && j$(obj).parent().find('select').is(':enabled')){
										loop_option_upc_type=j$(obj).parent().find('select').val();
									 }
									 if(current_value==j$(obj).val() && current_option_upc_type==loop_option_upc_type && current_option_upc_type != "" && current_value !=""){
									 	checkpresentinsiblings=true;
									 	return false;
									 }
								});
								if(checkpresentinsiblings){
									//j$(ele).parent().append('<span class="upc-error error-msg">Please insert different UPC from other Variant(s).</span>');
									//j$(ele).val("");
									//disableSavenUpload();
									return false;
								}
								var check_upc_correct=false;
								check_upc_correct=checkUPCAlreadyExistsFromDbForVariantOnLoad(ele,current_value,'variant',variant_as_parent,current_option_upc_type,option_id,product_id);
								if(!check_upc_correct){
									return false;
								}
								return true;
						}
						function checkUPCuniquenessOnLoadFirst(ele){
								var current_value=j$(ele).val();
								var index=j$(".upc_opt").index(ele);
								var option_id=j$(ele).parent().find('label.variant_option_id').text();
								var variant_as_parent=j$(ele).parent().find('label.variant_as_parent').text();
								var product_id=j$(ele).parent().find('label.variant_product_id').text();
								var option_sku=j$(ele).parent().find('label.variant_option_sku').text();
								var checkpresentinsiblings=false;
								var parent_sku=j$("#jetproduct-sku").val();
								var parent_upc_value=j$("#jetproduct-upc").val();
								var parent_upc_type="";
								var current_option_upc_type="";
								if(j$("#jetproduct-upc").parent().find('select').length >0 && j$("#jetproduct-upc").parent().find('select').is(':enabled')){
									parent_upc_type=j$("#jetproduct-upc").parent().find('select').val();
								}
								if(j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
									parent_upc_type=j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
								}
								if(j$(ele).parent().find('select').length >0 && j$(ele).parent().find('select').is(':enabled')){
									current_option_upc_type=j$(ele).parent().find('select').val();
								}
								if(j$(ele).parent().find('.upc_opt_type').length >0 && j$(ele).parent().find('.upc_opt_type').is(':enabled')){
									current_option_upc_type=j$(ele).parent().find('.upc_opt_type').val();
								}
								if(option_sku!=parent_sku){
									 if(parent_upc_value==current_value && parent_upc_type==current_option_upc_type && current_option_upc_type !="" && current_value !=""){
									 		j$(ele).parent().append('<span class="upc-error error-msg">Please insert different UPC from its Main Product.</span>');
										    j$(ele).addClass('select_error');
										    //j$(ele).val("");
										    //disableSavenUpload();
										    return false;
									 }
									 		
								}
								j$(".upc_opt").each(function(inx,obj){
									 if(inx==index){
									 	return true;
									 }
									 var loop_option_upc_type="";
									 if(j$(obj).parent().find('.upc_opt_type').length >0 && j$(obj).parent().find('.upc_opt_type').is(':enabled')){
										loop_option_upc_type=j$(obj).parent().find('.upc_opt_type').val();
									 }
									 if(j$(obj).parent().find('select').length >0 && j$(obj).parent().find('select').is(':enabled')){
										loop_option_upc_type=j$(obj).parent().find('select').val();
									 }
									 if(current_value==j$(obj).val() && current_option_upc_type==loop_option_upc_type && current_option_upc_type != "" && current_value !=""){
									 	checkpresentinsiblings=true;
									 	return false;
									 }
								});
								if(checkpresentinsiblings){
									j$(ele).parent().append('<span class="upc-error error-msg">Please insert different UPC from other Variant(s).</span>');
									j$(ele).addClass('select_error');
									//j$(ele).val("");
									//disableSavenUpload();
									return false;
								}
								var check_upc_correct=false;
								check_upc_correct=checkUPCAlreadyExistsFromDbForVariantOnLoadFirst(ele,current_value,'variant',variant_as_parent,current_option_upc_type,option_id,product_id);
								if(!check_upc_correct){
									return false;
								}
								return true;
						}
						function checkUPCAlreadyExistsFromDbForVariantOnLoad(ele,eleval,type,variant_as_parent,eletype,option_id,product_id){
							var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
							var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: eleval,barcode_type : eletype,variant_id : option_id,product_id:product_id,variant_as_parent:variant_as_parent,type:type, _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			//j$(ele).parent().append('<span class="upc-error error-msg">Entered UPC is already taken.Please use other UPC.</span>');
						        			//j$(ele).val("");
						        			var_flag=false;
						        			return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
						        return true;
						}
						function checkUPCAlreadyExistsFromDbForVariantOnLoadFirst(ele,eleval,type,variant_as_parent,eletype,option_id,product_id){
							var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
							var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: eleval,barcode_type : eletype,variant_id : option_id,product_id:product_id,variant_as_parent:variant_as_parent,type:type, _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).parent().append('<span class="upc-error error-msg">Entered Barcode already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			var_flag=false;
						        			return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
						        return true;
						}
						function checkASINuniqueness(ele){
								var current_value=j$(ele).val();
								var index=j$(".asin_opt").index(ele);
								var option_id=j$(ele).parent().find('label.variant_option_id').text();
								var variant_as_parent=j$(ele).parent().find('label.variant_as_parent').text();
								var product_id=j$(ele).parent().find('label.variant_product_id').text();
								var option_sku=j$(ele).parent().find('label.variant_option_sku').text();
								var checkpresentinsiblings=false;
								var parent_sku=j$("#jetproduct-sku").val();
								var parent_upc_value=j$("#jetproduct-upc").val();
								
								if(option_sku!=parent_sku){
									 if(parent_upc_value==current_value && current_value !=""){
									 		j$(ele).after('<span class="asin-error error-msg">Please insert different ASIN from its Main Product.</span>');
										    j$(ele).addClass('select_error');
										    //j$(ele).val("");
										    //disableSavenUpload();
										    return false;
									 }
									 		
								}
								j$(".asin_opt").each(function(inx,obj){
									 if(inx==index){
									 	return true;
									 }
									 if(current_value==j$(obj).val() && current_value !=""){
									 	checkpresentinsiblings=true;
									 	return false;
									 }
								});
								if(checkpresentinsiblings){
									j$(ele).after('<span class="asin-error error-msg">Please insert different ASIN from other Variant(s).</span>');
									j$(ele).addClass('select_error');
									//j$(ele).val("");
									//disableSavenUpload();
									return false;
								}
								var check_asin_correct=false;
								check_asin_correct=checkASINAlreadyExistsFromDbForVariant(ele,current_value,'variant',variant_as_parent,option_id,product_id);
								if(!check_asin_correct){
									return false;
								}
								return true;
						}
						function checkASINuniquenessOnLoad(ele){
								var current_value=j$(ele).val();
								var index=j$(".asin_opt").index(ele);
								var option_id=j$(ele).parent().find('label.variant_option_id').text();
								var variant_as_parent=j$(ele).parent().find('label.variant_as_parent').text();
								var product_id=j$(ele).parent().find('label.variant_product_id').text();
								var option_sku=j$(ele).parent().find('label.variant_option_sku').text();
								var checkpresentinsiblings=false;
								var parent_sku=j$("#jetproduct-sku").val();
								var parent_upc_value=j$("#jetproduct-upc").val();
								
								if(option_sku!=parent_sku){
									 if(parent_upc_value==current_value && current_value !=""){
									 		//j$(ele).after('<span class="asin-error error-msg">Please insert different ASIN from its Main Product.</span>');
										    //j$(ele).val("");
										    //disableSavenUpload();
										    return false;
									 }
									 		
								}
								j$(".asin_opt").each(function(inx,obj){
									 if(inx==index){
									 	return true;
									 }
									 if(current_value==j$(obj).val() && current_value !=""){
									 	checkpresentinsiblings=true;
									 	return false;
									 }
								});
								if(checkpresentinsiblings){
									//j$(ele).after('<span class="asin-error error-msg">Please insert different ASIN from other Variant(s).</span>');
									//j$(ele).val("");
									//disableSavenUpload();
									return false;
								}
								var check_asin_correct=false;
								check_asin_correct=checkASINAlreadyExistsFromDbForVariantOnLoad(ele,current_value,'variant',variant_as_parent,option_id,product_id);
								if(!check_asin_correct){
									return false;
								}
								return true;
						}
						function checkASINuniquenessOnLoadFirst(ele){
								var current_value=j$(ele).val();
								var index=j$(".asin_opt").index(ele);
								var option_id=j$(ele).parent().find('label.variant_option_id').text();
								var variant_as_parent=j$(ele).parent().find('label.variant_as_parent').text();
								var product_id=j$(ele).parent().find('label.variant_product_id').text();
								var option_sku=j$(ele).parent().find('label.variant_option_sku').text();
								var checkpresentinsiblings=false;
								var parent_sku=j$("#jetproduct-sku").val();
								var parent_upc_value=j$("#jetproduct-upc").val();
								
								if(option_sku!=parent_sku){
									 if(parent_upc_value==current_value && current_value !=""){
									 		j$(ele).after('<span class="asin-error error-msg">Please insert different ASIN from its Main Product.</span>');
										    j$(ele).addClass('select_error');
										    //j$(ele).val("");
										    //disableSavenUpload();
										    return false;
									 }
									 		
								}
								j$(".asin_opt").each(function(inx,obj){
									 if(inx==index){
									 	return true;
									 }
									 if(current_value==j$(obj).val() && current_value !=""){
									 	checkpresentinsiblings=true;
									 	return false;
									 }
								});
								if(checkpresentinsiblings){
									j$(ele).after('<span class="asin-error error-msg">Please insert different ASIN from other Variant(s).</span>');
									j$(ele).addClass('select_error');
									//j$(ele).val("");
									//disableSavenUpload();
									return false;
								}
								var check_asin_correct=false;
								check_asin_correct=checkASINAlreadyExistsFromDbForVariantOnLoadFirst(ele,current_value,'variant',variant_as_parent,option_id,product_id);
								if(!check_asin_correct){
									return false;
								}
								return true;
						}
						function checkASINAlreadyExistsFromDbForVariant(ele,eleval,type,variant_as_parent,option_id,product_id){
							var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
							 var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_asin: eleval,variant_id : option_id,product_id:product_id,variant_as_parent:variant_as_parent,type:type, _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).after('<span class="asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//disableSavenUpload();
						        			var_flag=false;
							        		return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
						}
						function checkASINAlreadyExistsFromDbForVariantOnLoad(ele,eleval,type,variant_as_parent,option_id,product_id){
							var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
							var var_flag=true;
							j$.ajax({
								  async : false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_asin: eleval,variant_id : option_id,product_id:product_id,variant_as_parent:variant_as_parent,type:type, _csrf : csrfToken }
						    }).done(function( msg ) {
						        	if(msg.success){
						        			//j$(ele).after('<span class="asin-error error-msg">Entered ASIN is already taken.Please use other ASIN.</span>');
						        			//j$(ele).val("");
						        			//disableSavenUpload();
						        			var_flag=false;
							        		return false;
						        	}
						           
						    });	
						    if(var_flag){
						    	return true;
						    }else{
						    	return false;
						    }
						        
						}
						function checkASINAlreadyExistsFromDbForVariantOnLoadFirst(ele,eleval,type,variant_as_parent,option_id,product_id){
							var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
							var var_flag=true;
							j$.ajax({
								  async : false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_asin: eleval,variant_id : option_id,product_id:product_id,variant_as_parent:variant_as_parent,type:type, _csrf : csrfToken }
						    }).done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).after('<span class="asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//disableSavenUpload();
						        			var_flag=false;
							        		return false;
						        	}
						           
						    });	
						    if(var_flag){
						    	return true;
						    }else{
						    	return false;
						    }
						        
						}
						function checkUPCuniqueness(ele){
								var current_value=j$(ele).val();
								var index=j$(".upc_opt").index(ele);
								var option_id=j$(ele).parent().find('label.variant_option_id').text();
								var variant_as_parent=j$(ele).parent().find('label.variant_as_parent').text();
								var product_id=j$(ele).parent().find('label.variant_product_id').text();
								var option_sku=j$(ele).parent().find('label.variant_option_sku').text();
								var checkpresentinsiblings=false;
								var parent_sku=j$("#jetproduct-sku").val();
								var parent_upc_value=j$("#jetproduct-upc").val();
								var parent_upc_type="";
								var current_option_upc_type="";
								if(j$("#jetproduct-upc").parent().find('select').length >0 && j$("#jetproduct-upc").parent().find('select').is(':enabled')){
									parent_upc_type=j$("#jetproduct-upc").parent().find('select').val();
								}
								if(j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
									parent_upc_type=j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
								}
								if(j$(ele).parent().find('select').length >0 && j$(ele).parent().find('select').is(':enabled')){
									current_option_upc_type=j$(ele).parent().find('select').val();
								}
								if(j$(ele).parent().find('.upc_opt_type').length >0 && j$(ele).parent().find('.upc_opt_type').is(':enabled')){
									current_option_upc_type=j$(ele).parent().find('.upc_opt_type').val();
								}
								if(option_sku!=parent_sku){
									 if(parent_upc_value==current_value && parent_upc_type==current_option_upc_type && current_option_upc_type !="" && current_value !=""){
									 		j$(ele).parent().append('<span class="upc-error error-msg">Please insert different Barcode from its Main Product.</span>');
										    j$(ele).addClass('select_error');
										    //j$(ele).val("");
										    //disableSavenUpload();
										    return false;
									 }
									 		
								}
								j$(".upc_opt").each(function(inx,obj){
									 if(inx==index){
									 	return true;
									 }
									 var loop_option_upc_type="";
									 if(j$(obj).parent().find('.upc_opt_type').length >0 && j$(obj).parent().find('.upc_opt_type').is(':enabled')){
										loop_option_upc_type=j$(obj).parent().find('.upc_opt_type').val();
									 }
									 if(j$(obj).parent().find('select').length >0 && j$(obj).parent().find('select').is(':enabled')){
										loop_option_upc_type=j$(obj).parent().find('select').val();
									 }
									 if(current_value==j$(obj).val() && current_option_upc_type==loop_option_upc_type && current_option_upc_type != "" && current_value !=""){
									 	checkpresentinsiblings=true;
									 	return false;
									 }
								});
								if(checkpresentinsiblings){
									j$(ele).parent().append('<span class="upc-error error-msg">Please insert different Barcode from other Variant(s).</span>');
									j$(ele).addClass('select_error');
									//j$(ele).val("");
									//disableSavenUpload();
									return false;
								}
								var check_upc_correct=false;
								check_upc_correct=checkUPCAlreadyExistsFromDbForVariant(ele,current_value,'variant',variant_as_parent,current_option_upc_type,option_id,product_id);
								if(!check_upc_correct){
									return false;
								}
								return true;
						}
						function checkUPCAlreadyExistsFromDbForVariant(ele,eleval,type,variant_as_parent,eletype,option_id,product_id){
							var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
							 var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: eleval,barcode_type : eletype,variant_id : option_id,product_id:product_id,variant_as_parent:variant_as_parent,type:type, _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).parent().append('<span class="upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//disableSavenUpload();
						        			var_flag=false;
							        		return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
						}
						
						j$('#saveedit').click(function(e) {
								e.preventDefault(e);
								e.stopPropagation();
								if(checkselectedBeforeSubmit()){
								  		j$('form').submit();
								}else{
								  		return false;
								}
							  
						});
			
					j$(".price_opt").blur(function(){
						 	j$(this).parent().find('span.price-error').remove();
						 	if(j$(this).hasClass('select_error')){
						 			j$(this).removeClass('select_error');
						 	}
						 	j$('#LoadingMSG').show();
						 	
							var va=j$(this).val();
							var reg=/^\d+\.?\d+$/;///^\d*[.]?\d+/
							if(va == ""){
								//j$(this).after('<span class="price-error error-msg">Invalid Price.</span>');
							    j$(this).addClass('select_error');
							    disableSavenUpload();
								j$('#LoadingMSG').hide();
								return false;
							}
							 if(!reg.test(va)) {
							 		j$(this).after('<span class="price-error error-msg">Invalid Price.</span>');
							        j$(this).addClass('select_error');
							       // j$(this).val("");
							        
							 }
							 disableSavenUpload();
							 j$('#LoadingMSG').hide();
					});
					j$('.upc_opt_select').change(function(){
							j$(this).parent().find('input.upc_opt').trigger('blur');
					});
					j$(".upc_opt").keyup(function(){
							var va=j$(this).val();
							j$(this).parent().find('.upc_opt_type').prop('disabled', false);
							j$(this).parent().find('.upc_opt_type').css('display', 'none');
							j$(this).parent().find('select').prop('disabled', true);
							j$(this).parent().find('select').css('display', 'none');
							j$(this).prop('style',"");
							j$(this).css({'width':'100%'});
							//j$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
							if(va == ""){
								j$(this).parent().find('.upc_opt_type').val("");
								return false;
							}
							upcfieldchange(this);
					});
					j$(".upc_opt").blur(function(){
						 	j$(this).parent().find('span.upc-error').remove();
						 	if(j$(this).hasClass('select_error')){
						 			j$(this).removeClass('select_error');
						 	}
						 	var index=j$( ".upc_opt" ).index(this);
						 	var asin_obj=j$( ".asin_opt" ).eq(index);
						 	var asin_value=j$(asin_obj).val();
						 	if(asin_value==""){
						 			j$(asin_obj).parent().find('span.asin-error').remove();
						 			j$(asin_obj).removeClass('select_error');
						 	}
						 	j$('#LoadingMSG').show();
						 	var va=j$(this).val();
							j$(this).parent().find('.upc_opt_type').prop('disabled', false);
							j$(this).parent().find('.upc_opt_type').css('display', 'none');
							j$(this).parent().find('select').prop('disabled', true);
							j$(this).parent().find('select').css('display', 'none');
							j$(this).prop('style',"");
							j$(this).css({'width':'100%'});
							//j$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
							if(va == ""){
								if(asin_value==""){
									//j$(this).parent().append('<span class="upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
							        // j$(this).val("");
							        j$(this).addClass('select_error');
							        j$(asin_obj).addClass('select_error');
								}
								j$(this).parent().find('.upc_opt_type').val("");
								j$('#LoadingMSG').hide();
								disableSavenUpload();
								return false;
							}
							if(!upcfieldchange(this)){
									j$(this).parent().append('<span class="upc-error error-msg">Invalid Barcode.Length Range : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
							        // j$(this).val("");
							        j$(this).addClass('select_error');
							        j$(this).parent().find('.upc_opt_type').val("");
							        disableSavenUpload();
							        j$('#LoadingMSG').hide();
							        return false;
							}
							var check_upc_is_correct=false;
							check_upc_is_correct=checkUPCuniqueness(this);
							if(!check_upc_is_correct){
									//j$(this).parent().find('.upc_opt_type').val("");
									//j$(this).parent().find('.upc_opt_type').prop('disabled', false);
									//j$(this).parent().find('.upc_opt_type').css('display', 'none');
									//j$(this).parent().find('select').prop('disabled', true);
									//j$(this).parent().find('select').css('display', 'none');
									//j$(this).prop('style',"");
									//j$(this).css({'width':'100%'});
									disableSavenUpload();
									j$('#LoadingMSG').hide();
							        return false;
							}
							disableSavenUpload();
							j$('#LoadingMSG').hide();
							return;
							
					});
					j$(".asin_opt").blur(function(){
						 	j$(this).parent().find('span.asin-error').remove();
						 	if(j$(this).hasClass('select_error')){
						 			j$(this).removeClass('select_error');
						 	}
						 	var index=j$( ".asin_opt" ).index(this);
						 	var upc_obj=j$( ".upc_opt" ).eq(index);
						 	var upc_value=j$(upc_obj).val();
						 	if(upc_value==""){
						 			j$(upc_obj).parent().find('span.upc-error').remove();
						 			j$(upc_obj).removeClass('select_error');
						 	}
						 	j$('#LoadingMSG').show();
						 	
							var va=j$(this).val();
							var reg=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
							if(va == ""){
								if(upc_value==""){
									//j$(this).after('<span class="asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
							        j$(this).addClass('select_error');
							        j$(upc_obj).addClass('select_error');
							    }
								disableSavenUpload();
								j$('#LoadingMSG').hide();
								return false;
							}
							if(va !=""){
								if(!reg.test(va)) {
							 		j$(this).after('<span class="asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
							        j$(this).addClass('select_error');
							        //j$(this).val("");
							        disableSavenUpload();
							        j$('#LoadingMSG').hide();
							        return false;
								}
								var asin_correct=false;
								asin_correct=checkASINuniqueness(this);
								if(!asin_correct){
									disableSavenUpload();
									j$('#LoadingMSG').hide();
									return false;
								}
								
							}
							disableSavenUpload();
							j$('#LoadingMSG').hide();
					});

					j$(".qty_opt").blur(function(){
						 	j$(this).parent().find('span.qty-error').remove();
						 	if(j$(this).hasClass('select_error')){
						 			j$(this).removeClass('select_error');
						 	}
						 	j$('#LoadingMSG').show();
						 	
							var va=j$(this).val();
							var reg=/^\d+$/;///^\d*[.]?\d+/
							if(va == ""){
								//j$(this).after('<span class="qty-error error-msg">Invalid Inventory.</span>');
							    j$(this).addClass('select_error');
								j$('#LoadingMSG').hide();
								disableSavenUpload();
								return false;
							}
							 if(!reg.test(va)) {
							 		j$(this).after('<span class="qty-error error-msg">Invalid Inventory.</span>');
							        j$(this).addClass('select_error');
							        //j$(this).val("");
							        
							 }
							 disableSavenUpload();
							 j$('#LoadingMSG').hide();
					});

					function disableSavenUploadOnLoadFirst(){
						var disable_flag=false;
						if (j$(".error-msg")[0]){
						    // Do something if class exists
						    j$('#savenuploadbutton').prop('disabled', true);
						    disable_flag=true;
						    //return false;
						}
						//if(disable_flag){
							//return false;
						//}
						j$( ".price_opt" ).each(function( index ) {
							var va=j$(this).val();
							var reg=/^\d+\.?\d+$/;///^\d*[.]?\d+/
							if(va ==""){
								//j$(this).after('<span class="price-error error-msg">Invalid Price.</span>');
								j$(this).addClass('select_error');
								j$('#savenuploadbutton').prop('disabled', true);
								disable_flag=true;
							 	//return false;
							}
							 if(va !="" && !reg.test(va)) {
							 	 j$(this).after('<span class="price-error error-msg">Invalid Price.</span>');
							 	 j$(this).addClass('select_error');
							 	 j$('#savenuploadbutton').prop('disabled', true);
							 	 disable_flag=true;
							 	 //return false;
							 }
						});
						//if(disable_flag){
							//return false;
						//}
						j$( ".upc_opt" ).each(function( index ) {
							var upc_flag=false;
							var upc_null_flag=false;
							var asin_flag=false;
							var asin_null_flag=false;
							var va_upc=j$(this).val();
							var reg_upc=/^\d{12}$/;///^\d*[.]?\d+/
							if(va_upc ==""){
								upc_flag=true;
								upc_null_flag=true;
							}
							if(va_upc !=""){
									if(!upcfieldchange(this)) {
										j$(this).parent().append('<span class="upc-error error-msg">Invalid Barcode.Length Range : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
										j$(this).addClass('select_error');
										j$('#savenuploadbutton').prop('disabled', true);
										disable_flag=true;
										upc_flag=true;
									 	//return false;
									 	
									}
									if(!checkUPCuniquenessOnLoadFirst(this)){
										j$('#savenuploadbutton').prop('disabled', true);
										disable_flag=true;
										upc_flag=true;
									 	//return false;
									}
							}
							
							var va_asin=j$( ".asin_opt" ).eq(index).val();
							var asin_obj=j$( ".asin_opt" ).eq(index);
							var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
							if(va_asin ==""){
										asin_flag=true;
										asin_null_flag=true;
							}
							if(va_asin !=""){
								if(!reg_asin.test(va_asin)) {
									j$(asin_obj).addClass('select_error');
									j$(asin_obj).after('<span class="asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
							        j$('#savenuploadbutton').prop('disabled', true);
									disable_flag=true;
							 		//return false;
								}
								var asin_correct=false;
								asin_correct=checkASINuniquenessOnLoadFirst(j$( ".asin_opt" ).eq(index));
								if(!asin_correct){
									j$('#savenuploadbutton').prop('disabled', true);
									disable_flag=true;
							 		//return false;
								}
								
							}
							if(asin_flag && upc_flag){
									if(upc_null_flag && asin_null_flag){
										//j$(this).parent().append('<span class="upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
										//j$(asin_obj).after('<span class="asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
										j$(this).addClass('select_error');
										j$(asin_obj).addClass('select_error');
									}
									j$('#savenuploadbutton').prop('disabled', true);
									disable_flag=true;
							 		//return false;
							}
						});
						//if(disable_flag){
						//	return false;
						//}
						j$( ".qty_opt" ).each(function( index ) {
							var va=j$(this).val();
							var reg=/^\d+$/;///^\d*[.]?\d+/
							if(va ==""){
								j$('#savenuploadbutton').prop('disabled', true);
								//j$(this).after('<span class="qty-error error-msg">Invalid Inventory.</span>');
								j$(this).addClass('select_error');
								disable_flag=true;
							 	//return false;
							}
							 if(va !="" && !reg.test(va)) {
							 	j$(this).after('<span class="qty-error error-msg">Invalid Inventory.</span>');
							 	j$('#savenuploadbutton').prop('disabled', true);
							 	j$(this).addClass('select_error');
							 	disable_flag=true;
							 	//return false;
							 }
						});
						//if(disable_flag){
							//return false;
						//}
						if(!checkselectedBeforeSubmit()){
								j$('#savenuploadbutton').prop('disabled', true);
								disable_flag=true;
							 	//return false;
						}
						if(disable_flag){
							return false;
						}
						j$('#savenuploadbutton').prop('disabled',false);
						return false;
					}

					function disableSavenUpload(){
						var disable_flag=false;
						if (j$(".error-msg")[0]){
						    // Do something if class exists
						    j$('#savenuploadbutton').prop('disabled', true);
						    disable_flag=true;
						    return false;
						}// else {
						    // Do something if class does not exist
						    //j$('.savenuploadbutton').prop('disabled', false);
						//}
						if(disable_flag){
							return false;
						}
						j$( ".price_opt" ).each(function( index ) {
							var va=j$(this).val();
							var reg=/^\d+\.?\d+$/;///^\d*[.]?\d+/
							if(va ==""){
								j$('#savenuploadbutton').prop('disabled', true);
								disable_flag=true;
							 	return false;
							}
							 if(!reg.test(va)) {
							 	 j$('#savenuploadbutton').prop('disabled', true);
							 	 disable_flag=true;
							 	 return false;
							 }
						});
						if(disable_flag){
							return false;
						}
						j$( ".upc_opt" ).each(function( index ) {
							var upc_flag=false;
							var asin_flag=false;
							var va_upc=j$(this).val();
							var reg_upc=/^\d{12}$/;///^\d*[.]?\d+/
							if(va_upc ==""){
								upc_flag=true;
							}
							if(va_upc !=""){
									if(!upcfieldchange(this)) {
										j$('#savenuploadbutton').prop('disabled', true);
										disable_flag=true;
										upc_flag=true;
									 	return false;
									 	
									}
									if(!checkUPCuniquenessOnLoad(this)){
										j$('#savenuploadbutton').prop('disabled', true);
										disable_flag=true;
										upc_flag=true;
									 	return false;
									}
							}
							
							var va_asin=j$( ".asin_opt" ).eq(index).val();
							
							var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
							if(va_asin ==""){
										asin_flag=true;
							}
							if(va_asin !=""){
								if(!reg_asin.test(va_asin)) {
							 		j$('#savenuploadbutton').prop('disabled', true);
									disable_flag=true;
							 		return false;
								}
								var asin_correct=false;
								asin_correct=checkASINuniquenessOnLoad(j$( ".asin_opt" ).eq(index));
								if(!asin_correct){
									j$('#savenuploadbutton').prop('disabled', true);
									disable_flag=true;
							 		return false;
								}
								
							}
							if(asin_flag && upc_flag){
									j$('#savenuploadbutton').prop('disabled', true);
									disable_flag=true;
							 		return false;
							}
						});
						if(disable_flag){
							return false;
						}
						j$( ".qty_opt" ).each(function( index ) {
							var va=j$(this).val();
							var reg=/^\d+$/;///^\d*[.]?\d+/
							if(va ==""){
								j$('#savenuploadbutton').prop('disabled', true);
								disable_flag=true;
							 	return false;
							}
							 if(!reg.test(va)) {
							 	j$('#savenuploadbutton').prop('disabled', true);
							 	disable_flag=true;
							 	return false;
							 }
						});
						if(disable_flag){
							return false;
						}
						if(!checkselectedBeforeSubmit()){
								j$('#savenuploadbutton').prop('disabled', true);
								disable_flag=true;
							 	return false;
						}
						if(disable_flag){
							return false;
						}
						j$('#savenuploadbutton').prop('disabled',false);
						return false;
					}
					
					function insertBarcodeType(){
							j$(".upc_opt").each(function(){
								 	var va=j$(this).val();
								 	j$(this).parent().find('.upc_opt_type').prop('disabled', false);
									j$(this).parent().find('.upc_opt_type').css('display', 'none');
									j$(this).parent().find('select').prop('disabled', true);
									j$(this).parent().find('select').css('display', 'none');
									j$(this).prop('style',"");
									j$(this).css({'width':'100%'});
									if(va == ""){
										j$(this).parent().find('.upc_opt_type').val('');
										return true;
									}
									upcfieldchange(this);
							});
					}
					function upcfieldchange(ele){
							var va=j$(ele).val();
							var reg_upc=/^\d{12}$/;///^\d*[.]?\d+/
							var reg_isbn_10=/^\d{10}$/;
							var reg_isbn_13=/^\d{13}$/;
							var reg_gtin_14=/^\d{14}$/;
							if(reg_upc.test(va)){
								j$(ele).prop('style',"");
								j$(ele).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								j$(ele).parent().find('.upc_opt_type').val('UPC');
								j$(ele).parent().find('.upc_opt_type').prop('disabled', false);
								j$(ele).parent().find('.upc_opt_type').prop('style',"");
								j$(ele).parent().find('.upc_opt_type').css({'width':'35%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								j$(ele).parent().find('select').prop('disabled', true);
								j$(ele).parent().find('select').css('display', 'none');
								return true;
							}else if(reg_isbn_10.test(va)){
								j$(ele).prop('style',"");
								j$(ele).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								j$(ele).parent().find('.upc_opt_type').val('ISBN-10');
								j$(ele).parent().find('.upc_opt_type').prop('disabled', false);
								j$(ele).parent().find('.upc_opt_type').prop('style',"");
								j$(ele).parent().find('.upc_opt_type').css({'width':'35%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								j$(ele).parent().find('select').prop('disabled', true);
								j$(ele).parent().find('select').css('display', 'none');
								return true;
							}else if(reg_isbn_13.test(va)){
								j$(ele).prop('style',"");
								j$(ele).css({'width':'65%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								j$(ele).parent().find('.upc_opt_type').prop('disabled', true);
								j$(ele).parent().find('.upc_opt_type').css('display', 'none');
								j$(ele).parent().find('select').prop('disabled', false);
								j$(ele).parent().find('select').prop('style',"");
								j$(ele).parent().find('select').css({'width':'30%','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								return true;
							}else if(reg_gtin_14.test(va)){
								j$(ele).prop('style',"");
								j$(ele).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								j$(ele).parent().find('.upc_opt_type').val('GTIN-14');
								j$(ele).parent().find('.upc_opt_type').prop('disabled', false);
								j$(ele).parent().find('.upc_opt_type').prop('style',"");
								j$(ele).parent().find('.upc_opt_type').css({'width':'35%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								j$(ele).parent().find('select').prop('disabled', true);
								j$(ele).parent().find('select').css('display', 'none');
								return true;
							}else{
							 		return false;
							 }
					}
					/*j$("#jetproduct-upc").blur(function(){
						 	j$(this).parent().find('span.jetproduct-upc-error').remove();
						 	j$('#LoadingMSG').show();
						 	disableSavenUpload();
						 	var va=j$(this).val();
							j$(this).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
							j$(this).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
							j$(this).parent().find('select').prop('disabled', true);
							j$(this).parent().find('select').css('display', 'none');
							j$(this).prop('style',"");
							j$(this).css({'width':'25%'});
							//j$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
							if(va == ""){
								j$(this).parent().find('.jetproduct-upc_opt_type').val("");
								j$('#LoadingMSG').hide();
								return false;
							}
							if(!fillBarcodeType()){
									j$(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
							        disableSavenUpload();
							}
							j$('#LoadingMSG').hide();
					});*/
					disableSavenUploadOnLoadFirst();
					insertBarcodeType();
					j$("#jetproduct-upc").prop("readonly", true); 
					j$("#jetproduct-asin").prop("readonly", true); 
			</script>
			
		<?php }else{?>
			<span class="no_attribute_in_category">No attributes for the selected Category</span>
			<script type="text/javascript">
				j$("#jetproduct-upc").prop("readonly", false); 
				j$("#jetproduct-asin").prop("readonly", false);
				disableSaveNUploadButtonForVariantSimpleOnLoadFirst(); 
				j$("#jetproduct-price").blur(function(){
							j$(this).parent().find('span.jetproduct-price-error').remove();
						 	if(j$(this).hasClass('select_error')){
						 		j$(this).removeClass('select_error');
						 	}
						 	j$('#LoadingMSG').show();
						 	var va=j$(this).val();
							var reg=/^\d+\.?\d+$/;///^\d*[.]?\d+/
							if(va == ""){
								//j$(this).after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
							    j$(this).addClass('select_error');
							    disableSaveNUploadButtonForVariantSimple();
								j$('#LoadingMSG').hide();
								return false;
							}
							 if(!reg.test(va)) {
							 		j$(this).after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
							        j$(this).addClass('select_error');
							        //j$(this).val("");
							        
							 }
							 disableSaveNUploadButtonForVariantSimple();
							 j$('#LoadingMSG').hide();
				});
				j$("#jetproduct-qty").blur(function(){
										j$(this).parent().find('span.jetproduct-qty-error').remove();
									 	if(j$(this).hasClass('select_error')){
									 		j$(this).removeClass('select_error');
									 	}
									 	j$('#LoadingMSG').show();
									 	
										var va=j$(this).val();
										var reg=/^\d+$/;///^\d*[.]?\d+/
										if(va == ""){
											//j$(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
										    j$(this).addClass('select_error');
										    disableSaveNUploadButtonForVariantSimple();
											j$('#LoadingMSG').hide();
											return false;
										}
										 if(!reg.test(va)) {
										 		j$(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
										        j$(this).addClass('select_error');
										        //j$(this).val("");
										        
										 }
										 disableSaveNUploadButtonForVariantSimple();
										 j$('#LoadingMSG').hide();
				});
				j$("#jetproduct-asin").blur(function(){
							j$(this).parent().find('span.jetproduct-asin-error').remove();
							if(j$(this).hasClass('select_error')){
						 		j$(this).removeClass('select_error');
						 	}
						 	var upc_value=j$("#jetproduct-upc").val();
							if(upc_value==""){
									j$("#jetproduct-upc").removeClass('select_error');
									j$("#jetproduct-upc").parent().find('span.jetproduct-upc-error').remove();
							}
							j$('#LoadingMSG').show();
						 	
							var va=j$(this).val();
							var reg=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
							if(va == ""){
								if(upc_value==""){
									j$(this).addClass('select_error');
									j$("#jetproduct-upc").addClass('select_error');
									//j$(this).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
								}
								disableSaveNUploadButtonForVariantSimple();
								j$('#LoadingMSG').hide();
								return false;
							}
							if(!reg.test(va)) {
							 		j$(this).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
							        //j$(this).val("");
							        j$(this).addClass('select_error');
							        disableSaveNUploadButtonForVariantSimple();
							        j$('#LoadingMSG').hide();
							        return false;
							}
							var product_id=j$("#jetproduct-upc").parent().find('label.general_product_id').text();
							var product_sku=j$("#jetproduct-sku").val();
							var asin_flag=false;
							asin_flag=checkASINfromDbforVariantSimple(this,va,product_id,product_sku);
							if(!asin_flag){
								disableSaveNUploadButtonForVariantSimple();
								j$('#LoadingMSG').hide();
								return false;
							}
							disableSaveNUploadButtonForVariantSimple();
							j$('#LoadingMSG').hide();
							
				});
				function checkASINfromDbforVariantSimple(ele,eleval,product_id,product_sku){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
						var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_asin: eleval,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//j$('#savenuploadbutton').prop('disabled', true);
							        		var_flag=false;
							        		return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
				}
				function checkASINfromDbforVariantSimpleOnLoad(ele,eleval,product_id,product_sku){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
						var var_flag=true;	 
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_asin: eleval,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			//j$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already taken.Please use other ASIN.</span>');
						        			//j$(ele).val("");
						        			//j$('#savenuploadbutton').prop('disabled', true);
							        		var_flag=false;
							        		return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
				}
				function checkASINfromDbforVariantSimpleOnLoadFirst(ele,eleval,product_id,product_sku){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
						var var_flag=true;	 
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_asin: eleval,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//j$('#savenuploadbutton').prop('disabled', true);
							        		var_flag=false;
							        		return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
				}
				j$("#jetproduct-upc").blur(function(){
						 	j$(this).parent().find('span.jetproduct-upc-error').remove();
						 	if(j$(this).hasClass('select_error')){
						 		j$(this).removeClass('select_error');
						 	}
						 	var asin_value=j$("#jetproduct-asin").val();
							if(asin_value==""){
									j$("#jetproduct-asin").removeClass('select_error');
									j$("#jetproduct-asin").parent().find('span.jetproduct-asin-error').remove();
							}
						 	j$('#LoadingMSG').show();
						 	var va=j$(this).val();
							j$(this).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
							j$(this).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
							j$(this).parent().find('select').prop('disabled', true);
							j$(this).parent().find('select').css('display', 'none');
							j$(this).prop('style',"");
							j$(this).css({'width':'25%'});
							//j$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
							if(va == ""){
								if(asin_value==""){
									j$(this).addClass('select_error');
									j$("#jetproduct-asin").addClass('select_error');
									//j$(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
								}
								j$(this).parent().find('.jetproduct-upc_opt_type').val("");
								disableSaveNUploadButtonForVariantSimple();
								j$('#LoadingMSG').hide();
								return false;
							}
							if(!fillBarcodeType()){
									j$(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Length Range : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
							        j$(this).addClass('select_error');
							        j$(this).parent().find('.jetproduct-upc_opt_type').val("");
							        disableSaveNUploadButtonForVariantSimple();
							        j$('#LoadingMSG').hide();
							        return false;
							}
							var product_id=j$(this).parent().find('label.general_product_id').text();
							var product_sku=j$("#jetproduct-sku").val();
							var current_upc_type="";
							if(j$(this).parent().find('select').length >0 && j$(this).parent().find('select').is(':enabled')){
									current_upc_type=j$(this).parent().find('select').val();
							}
							if(j$(this).parent().find('.jetproduct-upc_opt_type').length >0 && j$(this).parent().find('.jetproduct-upc_opt_type').is(':enabled')){
									current_upc_type=j$(this).parent().find('.jetproduct-upc_opt_type').val();
							}
							var upc_flag=false;
							upc_flag=checkUPCfromDbforVariantSimple(this,va,product_id,product_sku,current_upc_type);
							if(!upc_flag){
								disableSaveNUploadButtonForVariantSimple();
								j$('#LoadingMSG').hide();
								return false;
							}
							disableSaveNUploadButtonForVariantSimple();
							j$('#LoadingMSG').hide();
							
				});
				function checkUPCfromDbforVariantSimple(ele,upc,product_id,product_sku,upc_type){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
						var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: upc,barcode_type:upc_type,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).parent().append('<span class="jetproduct-upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//j$(ele).parent().find('.jetproduct-upc_opt_type').val("");
							        		//j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
											//j$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
											//j$(ele).parent().find('select').prop('disabled', true);
											//j$(ele).parent().find('select').css('display', 'none');
											//j$(ele).prop('style',"");
											//j$(ele).css({'width':'25%'});
											var_flag=false;
											return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
				}
				function checkUPCfromDbforVariantSimpleOnLoad(ele,upc,product_id,product_sku,upc_type){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
						var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: upc,barcode_type:upc_type,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			//j$(ele).after('<span class="upc-error error-msg">Entered UPC is already taken.Please use other UPC.</span>');
						        			//j$(ele).val("");
						        			//j$(ele).parent().find('.jetproduct-upc_opt_type').val("");
							        		//j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
											//j$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
											//j$(ele).parent().find('select').prop('disabled', true);
											//j$(ele).parent().find('select').css('display', 'none');
											//j$(ele).prop('style',"");
											//j$(ele).css({'width':'25%'});
											var_flag=false;
											return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
				}
				function checkUPCfromDbforVariantSimpleOnLoadFirst(ele,upc,product_id,product_sku,upc_type){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
						var var_flag=true;
							 j$.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: upc,barcode_type:upc_type,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			j$(ele).parent().append('<span class="jetproduct-upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
						        			j$(ele).addClass('select_error');
						        			//j$(ele).val("");
						        			//j$(ele).parent().find('.jetproduct-upc_opt_type').val("");
							        		//j$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
											//j$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
											//j$(ele).parent().find('select').prop('disabled', true);
											//j$(ele).parent().find('select').css('display', 'none');
											//j$(ele).prop('style',"");
											//j$(ele).css({'width':'25%'});
											var_flag=false;
											return false;
						        	}
						           
						        });	
						        if(var_flag){
							    	return true;
							    }else{
							    	return false;
							    }
				}
		function disableSaveNUploadButtonForVariantSimpleOnLoadFirst(){
				var disable_flag=false;
				if (j$(".error-msg")[0]){
					j$('#savenuploadbutton').prop('disabled', true);
						    disable_flag=true;
						    //return false;
				}
				//if(disable_flag){
							//return false;
				//}
				
				var va_price=j$( "#jetproduct-price" ).val();
				var reg_price=/^\d+\.?\d+$/;///^\d*[.]?\d+/
				if(va_price ==""){
					//j$("#jetproduct-price").after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
					j$("#jetproduct-price").addClass('select_error');
					j$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					//return false;
				}
				if(va_price !="" &&!reg_price.test(va_price)) {
						j$("#jetproduct-price").after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
						j$("#jetproduct-price").addClass('select_error');
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
				}
				
				//if(disable_flag){
					//	return false;
				//}
				
				var upc_flag=false;
				var asin_flag=false;
				var upc_null_flag=false;
				var asin_null_flag=false;
				var va_upc=j$( "#jetproduct-upc" ).val();
				if(va_upc ==""){
					upc_null_flag=true;
					upc_flag=true;
				}
				if(va_upc !=""){
					if(!fillBarcodeType()) {
						j$("#jetproduct-upc").parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Length Range : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
						j$("#jetproduct-upc").addClass('select_error');
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						upc_flag=true;
						//return false;
					}
					var product_id=j$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var product_sku=j$("#jetproduct-sku").val();
					var current_upc_type="";
					if(j$("#jetproduct-upc").parent().find('select').length >0 && j$("#jetproduct-upc").parent().find('select').is(':enabled')){
						current_upc_type=j$("#jetproduct-upc").parent().find('select').val();
					}
					if(j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
						current_upc_type=j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
					}
					var upc_db_flag=false;
					upc_db_flag=checkUPCfromDbforVariantSimpleOnLoadFirst("#jetproduct-upc",va_upc,product_id,product_sku,current_upc_type);
					if(!upc_db_flag){
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						upc_flag=true;
						//return false;
					}
				}
				var va_asin=j$( "#jetproduct-asin" ).val();
				var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
				if(va_asin ==""){
						asin_null_flag=true;
						asin_flag=true;
				}
				if(va_asin !=""){
					if(!reg_asin.test(va_asin)){
						j$("#jetproduct-asin").after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
						j$("#jetproduct-asin").addClass('select_error');
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
					}
					var product_asin_id=j$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var product_asin_sku=j$("#jetproduct-sku").val();
					var asin_db_flag=false;
					asin_db_flag=checkASINfromDbforVariantSimpleOnLoadFirst("#jetproduct-asin",va_asin,product_asin_id,product_asin_sku);
					if(!asin_db_flag){
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
					}
				}
				if(asin_flag && upc_flag){
					if(upc_null_flag && asin_null_flag){
						j$("#jetproduct-upc").addClass('select_error');
						j$("#jetproduct-asin").addClass('select_error');
						//j$("#jetproduct-asin").after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
						//j$("#jetproduct-upc").parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
					}
					j$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					//return false;
				}
				//if(disable_flag){
				//	return false;
				//}
				
				var va_qty=j$( "#jetproduct-qty" ).val();
				var reg_qty=/^\d+$/;///^\d*[.]?\d+/
				if(va_qty ==""){
					j$("#jetproduct-qty").addClass('select_error');
					//j$("#jetproduct-qty").after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
					j$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					//return false;
				}
				if(va_qty !="" && !reg_qty.test(va_qty)) {
						j$("#jetproduct-qty").addClass('select_error');
						j$("#jetproduct-qty").after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
				}
				
				if(disable_flag){
					return false;
				}
				j$('#savenuploadbutton').prop('disabled',false);
				return false;
		}
		function disableSaveNUploadButtonForVariantSimple(){
				var disable_flag=false;
				if (j$(".error-msg")[0]){
					j$('#savenuploadbutton').prop('disabled', true);
						    disable_flag=true;
						    return false;
				}
				if(disable_flag){
							return false;
				}
				
				var va_price=j$( "#jetproduct-price" ).val();
				var reg_price=/^\d+\.?\d+$/;///^\d*[.]?\d+/
				if(va_price ==""){
					j$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					return false;
				}
				if(!reg_price.test(va_price)) {
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
				}
				
				if(disable_flag){
						return false;
				}
				
				var upc_flag=false;
				var asin_flag=false;
				var va_upc=j$( "#jetproduct-upc" ).val();
				if(va_upc ==""){
					upc_flag=true;
				}
				if(va_upc !=""){
					if(!fillBarcodeType()) {
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						upc_flag=true;
						return false;
					}
					var product_id=j$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var product_sku=j$("#jetproduct-sku").val();
					var current_upc_type="";
					if(j$("#jetproduct-upc").parent().find('select').length >0 && j$("#jetproduct-upc").parent().find('select').is(':enabled')){
						current_upc_type=j$("#jetproduct-upc").parent().find('select').val();
					}
					if(j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
						current_upc_type=j$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
					}
					var upc_db_flag=false;
					upc_db_flag=checkUPCfromDbforVariantSimpleOnLoad("#jetproduct-upc",va_upc,product_id,product_sku,current_upc_type);
					if(!upc_db_flag){
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						upc_flag=true;
						return false;
					}
				}
				var va_asin=j$( "#jetproduct-asin" ).val();
				var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
				if(va_asin ==""){
						asin_flag=true;
				}
				if(va_asin !=""){
					if(!reg_asin.test(va_asin)){
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
					}
					var product_asin_id=j$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var product_asin_sku=j$("#jetproduct-sku").val();
					var asin_db_flag=false;
					asin_db_flag=checkASINfromDbforVariantSimpleOnLoad("#jetproduct-asin",va_asin,product_asin_id,product_asin_sku);
					if(!asin_db_flag){
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
					}
				}
				if(asin_flag && upc_flag){
					j$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					return false;
				}
				if(disable_flag){
					return false;
				}
				
				var va_qty=j$( "#jetproduct-qty" ).val();
				var reg_qty=/^\d+$/;///^\d*[.]?\d+/
				if(va_qty ==""){
					j$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					return false;
				}
				if(!reg_qty.test(va_qty)) {
						j$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
				}
				
				if(disable_flag){
					return false;
				}
				j$('#savenuploadbutton').prop('disabled',false);
				return false;
		}
			</script>
			
		<?php }?>