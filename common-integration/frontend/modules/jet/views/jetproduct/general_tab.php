<?= $form->field($model, 'title')->hiddenInput()->label(false);?>
<?= $form->field($model, 'sku')->hiddenInput()->label(false);?>
<?= $form->field($model, 'weight')->hiddenInput()->label(false);?>
<?= $form->field($model, 'qty')->hiddenInput()->label(false);?>
<div class="form-group field-jetproduct-price">
    <input id="jetproduct-price" class="form-control select_error" type="hidden" value="<?= $model->price;?>" name="JetProduct[price]">
</div>
<?= $form->field($model, 'vendor')->hiddenInput()->label(false);?>
<?= $form->field($model, 'description')->hiddenInput()->label(false); ?>
<div class="field-jetproduct">
	<?php $brand="";
	$brand=$model->vendor;
	?>
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Title</th>
				<th>Sku</th>
				<th>Price</th>
				<th>Quantity</th>
				<th>Brand</th>
				<th>Weight</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?= $model->title;?>
				</td>
				<td>
				<?= $model->sku;?>
				</td>
				<td>
					<?= (float)$model->price;?>
				</td>
				<td>
					<?= $model->qty;?>
				</td>
				<td>
					<?= $model->vendor;?>
				</td>
				<td>
					<?= (float)$model->weight;?>
				</td>
				<td>
					<div class="more">
						<?php 
							$truncated="";
							$truncated = (strlen($model->description) > 50) ? substr($model->description, 0, 50) . '...<a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" onclick=clickMore()>more</a>' : $model->description;
						?>
						<?= $truncated;?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<label for="jetproduct-upc" class="control-label">Barcode(UPC,GTIN, etc)</label>
	<div class="upc_wrapper">
		<label class="general_product_id" style="display:none;"><?=trim($model->id);?></label>
		<input type="text" maxlength="500" value="<?=$model->upc;?>" name="JetProduct[upc]" class="form-control" id="jetproduct-upc" style="width:25%;">
		<input type="text"  value="<?=$model->barcode_type;?>" name="JetProduct[barcode_type]" class="form-control jetproduct-upc_opt_type" id="jetproduct-barcode_type" <?php if("ISBN-13"!=trim($model->barcode_type) && "EAN"!=trim($model->barcode_type) && ""!=trim($model->barcode_type)){echo "style='display:inline-block;'";}else{echo "disabled='disabled' style='display:none;'";}?> readonly/>
		<select id="jetproduct-upc-select" class="form-control" name="JetProduct[barcode_type]" <?php if("ISBN-13"==trim($model->barcode_type) || "EAN"==trim($model->barcode_type)){echo "style='display:inline-block;'";}else{echo "disabled='disabled' style='display:none;'";}?>>
			<option value="ISBN-13" <?php if("ISBN-13"==trim($model->barcode_type)){echo "selected='selected'";}?>>ISBN-13</option>
			<option value="EAN" <?php if("EAN"==trim($model->barcode_type)){echo "selected='selected'";}?>>EAN</option>
		</select>
	</div>
	<div class="help-block"></div>
</div>
<div class="modal fade" id="myModal" role="dialog" style="display:none">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
      	<div class="modal-header">
        	<button type="button" class="close" data-dismiss="modal">&times;</button>
       	 	<h4 class="modal-title">Description</h4>
      	</div>
        <div class="modal-body">
          <?= $model->description; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>
<?= $form->field($model, 'ASIN')->textInput(['maxlength' => true]) ?>
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
							$(this).css({'width':'25%'});
							//$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
							if(va == ""){
								$(this).parent().find('.jetproduct-upc_opt_type').val("");
								return false;
							}
							fillBarcodeType();
	});
	
	<?php if($model->type=="simple"){?>
		disableSaveNUploadButtonForSimpleOnLoadFirst();
		function disableSaveNUploadButtonForSimpleOnLoadFirst(){
				var disable_flag=false;
				if ($(".error-msg")[0]){
					$('#savenuploadbutton').prop('disabled', true);
						    disable_flag=true;
						   // return false;
				}
				//if(disable_flag){
						//	return false;
				//}
				
				var va_price=$( "#jetproduct-price" ).val();
				/* 
				var reg_price=/^\d+\.?\d+$/;///^\d*[.]?\d+/
				if(va_price ==""){
					$(this).addClass('select_error');
					//$(this).after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
					$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					//return false;
				}
				if(va_price !="" && !reg_price.test(va_price)) {
						$(this).addClass('select_error');
						//$(this).after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
				} */
				
				//if(disable_flag){
				//		return false;
				//}
				
				var upc_flag=false;
				var asin_flag=false;
				var upc_null_flag=false;
				var asin_null_flag=false;
				var va_upc=$( "#jetproduct-upc" ).val();
				if(va_upc ==""){
					upc_null_flag=true;
					upc_flag=true;
				}
				if(va_upc !=""){
					if(!fillBarcodeType()) {
						$("#jetproduct-upc").addClass('select_error');
						$("#jetproduct-upc").parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Length Range : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						upc_flag=true;
						//return false;
					}
					var product_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var current_upc_type="";
					if($("#jetproduct-upc").parent().find('select').length >0 && $("#jetproduct-upc").parent().find('select').is(':enabled')){
						current_upc_type=$("#jetproduct-upc").parent().find('select').val();
					}
					if($("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && $("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
						current_upc_type=$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
					}
					var upc_db_flag=false;
					upc_db_flag=checkUPCfromDbforSimpleOnLoadFirst("#jetproduct-upc",va_upc,product_id,current_upc_type);
					if(!upc_db_flag){
							$('#savenuploadbutton').prop('disabled', true);
							disable_flag=true;
							upc_flag=true;
							//return false;
					}
				}
				var va_asin=$( "#jetproduct-asin" ).val();
				var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
				if(va_asin ==""){
						asin_flag=true;
						asin_null_flag=true;
				}
				if(va_asin !=""){
					if(!reg_asin.test(va_asin)){
						$("#jetproduct-asin").addClass("select_error");
						$("#jetproduct-asin" ).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
					}
					var product_asin_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var asin_db_flag=false;
					asin_db_flag=checkASINfromDbforSimpleOnLoadFirst("#jetproduct-asin",va_asin,product_asin_id);
					if(!asin_db_flag){
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
					}
				}
				if(asin_flag && upc_flag){
					if(upc_null_flag && asin_null_flag){
						$("#jetproduct-asin").addClass("select_error");
						$("#jetproduct-upc").addClass("select_error");
						//$("#jetproduct-upc").parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
						//$("#jetproduct-asin" ).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
					}
					$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					//return false;
				}
				//if(disable_flag){
					//return false;
				//}
				
				var va_qty=$( "#jetproduct-qty" ).val();
				var reg_qty=/^\d+$/;///^\d*[.]?\d+/
				if(va_qty ==""){
					$(this).addClass("select_error");
					//$(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
					$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					//return false;
				}
				if(va_qty !="" && !reg_qty.test(va_qty)) {
						$(this).addClass("select_error");
						$(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						//return false;
				}
				
				if(disable_flag){
					return false;
				}
				$('#savenuploadbutton').prop('disabled',false);
				return false;
		}
		function disableSaveNUploadButtonForSimple(){
				var disable_flag=false;
				if ($(".error-msg")[0]){
					$('#savenuploadbutton').prop('disabled', true);
						    disable_flag=true;
						    return false;
				}
				if(disable_flag){
							return false;
				}
				
				var va_price=$( "#jetproduct-price" ).val();
				var reg_price=/^\d+\.?\d+$/;///^\d*[.]?\d+/
				if(va_price ==""){
					$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					return false;
				}
				if(!reg_price.test(va_price)) {
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
				}
				
				if(disable_flag){
						return false;
				}
				
				var upc_flag=false;
				var asin_flag=false;
				var va_upc=$( "#jetproduct-upc" ).val();
				if(va_upc ==""){
					upc_flag=true;
				}
				if(va_upc !=""){
					if(!fillBarcodeType()) {
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						upc_flag=true;
						return false;
					}
					var product_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var current_upc_type="";
					if($("#jetproduct-upc").parent().find('select').length >0 && $("#jetproduct-upc").parent().find('select').is(':enabled')){
						current_upc_type=$("#jetproduct-upc").parent().find('select').val();
					}
					if($("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && $("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
						current_upc_type=$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
					}
					var upc_db_flag=false;
					upc_db_flag=checkUPCfromDbforSimpleOnLoad("#jetproduct-upc",va_upc,product_id,current_upc_type);
					if(!upc_db_flag){
						$('#savenuploadbutton').prop('disabled', true);
							disable_flag=true;
							upc_flag=true;
							return false;
					}
				}
				var va_asin=$( "#jetproduct-asin" ).val();
				var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
				if(va_asin ==""){
						asin_flag=true;
				}
				if(va_asin !=""){
					if(!reg_asin.test(va_asin)){
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
					}
					var product_asin_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
					var asin_db_flag=false;
					asin_db_flag=checkASINfromDbforSimpleOnLoad("#jetproduct-asin",va_asin,product_asin_id);
					if(!asin_db_flag){
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
					}
				}
				if(asin_flag && upc_flag){
					$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					return false;
				}
				if(disable_flag){
					return false;
				}
				
				var va_qty=$( "#jetproduct-qty" ).val();
				var reg_qty=/^\d+$/;///^\d*[.]?\d+/
				if(va_qty ==""){
					$('#savenuploadbutton').prop('disabled', true);
					disable_flag=true;
					return false;
				}
				if(!reg_qty.test(va_qty)) {
						$('#savenuploadbutton').prop('disabled', true);
						disable_flag=true;
						return false;
				}
				
				if(disable_flag){
					return false;
				}
				$('#savenuploadbutton').prop('disabled',false);
				return false;
		}
		$("#jetproduct-qty").blur(function(){
								$(this).parent().find('span.jetproduct-qty-error').remove();
								if($(this).hasClass('select_error')){
						 			$(this).removeClass('select_error');
						 		}
							 	$('#LoadingMSG').show();
							 	var va=$(this).val();
								var reg=/^\d+$/;///^\d*[.]?\d+/
								if(va == ""){
									$(this).addClass('select_error');
									//$(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
									$('#LoadingMSG').hide();
									disableSaveNUploadButtonForSimple();
									return false;
								}
								 if(!reg.test(va)) {
								 		$(this).addClass('select_error');
								 		$(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
								        //$(this).val("");
								        
								 }
								 disableSaveNUploadButtonForSimple();
								 $('#LoadingMSG').hide();
		});
		$("#jetproduct-asin").blur(function(){
				$(this).parent().find('span.jetproduct-asin-error').remove();
				if($(this).hasClass('select_error')){
						$(this).removeClass('select_error');
				}
				var upc_value=$("#jetproduct-upc").val();
				if(upc_value==""){
						$("#jetproduct-upc").removeClass('select_error');
						$("#jetproduct-upc").parent().find('span.jetproduct-upc-error').remove();
				}
				$('#LoadingMSG').show();
				
				var va=$(this).val();
				var reg=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
				if(va == ""){
						if(upc_value==""){
							$(this).addClass('select_error');
							$("#jetproduct-upc").addClass('select_error');
							//$(this).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
						}
						disableSaveNUploadButtonForSimple();
						$('#LoadingMSG').hide();
						return false;
				}
				if(!reg.test(va)) {
					$(this).addClass('select_error');
					$(this).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
					 //$(this).val("");
					disableSaveNUploadButtonForSimple();
					$('#LoadingMSG').hide();
					return false;
				}
				var product_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
				var asin_flag=false;
				asin_flag=checkASINfromDbforSimple(this,va,product_id);
				if(!asin_flag){
					disableSaveNUploadButtonForSimple();
					$('#LoadingMSG').hide();
					return false;
				}
				disableSaveNUploadButtonForSimple();
				$('#LoadingMSG').hide();
		});
		function checkASINfromDbforSimple(ele,asin,product_id){
			var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
			var var_flag=true;
			$.ajax({
						async:false,
						method: "GET",
						url: url,
						dataType:'json',
						data: { product_asin: asin,product_id:product_id,type:'simple', _csrf : csrfToken }
					})
					.done(function( msg ) {
						if(msg.success){
								$(ele).addClass('select_error');
						    	$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
						        //$(ele).val("");
						       // $('#savenuploadbutton').prop('disabled', true);
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
		function checkASINfromDbforSimpleOnLoad(ele,asin,product_id){
			var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
			var var_flag=true;
			$.ajax({
						async:false,
						method: "GET",
						url: url,
						dataType:'json',
						data: { product_asin: asin,product_id:product_id,type:'simple', _csrf : csrfToken }
					})
					.done(function( msg ) {
						if(msg.success){
						    //$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already taken.Please use other ASIN.</span>');
						        //$(ele).val("");
						        //$('#savenuploadbutton').prop('disabled', true);
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
		function checkASINfromDbforSimpleOnLoadFirst(ele,asin,product_id){
			var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
			var var_flag=true;
			$.ajax({
						async:false,
						method: "GET",
						url: url,
						dataType:'json',
						data: { product_asin: asin,product_id:product_id,type:'simple', _csrf : csrfToken }
					})
					.done(function( msg ) {
						if(msg.success){
								$(ele).addClass('select_error');
						    	$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
						        //$(ele).val("");
						        //$('#savenuploadbutton').prop('disabled', true);
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
		$("#jetproduct-upc").blur(function(){
				$(this).parent().find('span.jetproduct-upc-error').remove();
				if($(this).hasClass('select_error')){
						$(this).removeClass('select_error');
				}
				var asin_value=$("#jetproduct-asin").val();
				if(asin_value==""){
						$("#jetproduct-asin").removeClass('select_error');
						$("#jetproduct-asin").parent().find('span.jetproduct-asin-error').remove();
				}
				$('#LoadingMSG').show();
				var va=$(this).val();
				$(this).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
				$(this).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
				$(this).parent().find('select').prop('disabled', true);
				$(this).parent().find('select').css('display', 'none');
				$(this).prop('style',"");
				$(this).css({'width':'25%'});
				//$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
				if(va == ""){
					if(asin_value==""){
						$(this).addClass('select_error');
						$("#jetproduct-asin").addClass('select_error');
						//$(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
					}
					$(this).parent().find('.jetproduct-upc_opt_type').val("");
					disableSaveNUploadButtonForSimple();
					$('#LoadingMSG').hide();
					return false;
				}
				if(!fillBarcodeType()){
					$(this).addClass('select_error');
					$(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Length Range : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
					$(this).parent().find('.jetproduct-upc_opt_type').val("");
					disableSaveNUploadButtonForSimple();
					$('#LoadingMSG').hide();
					return false;
				}
				var product_id=$(this).parent().find('label.general_product_id').text();
				var current_upc_type="";
				if($(this).parent().find('select').length >0 && $(this).parent().find('select').is(':enabled')){
						current_upc_type=$(this).parent().find('select').val();
				}
				if($(this).parent().find('.jetproduct-upc_opt_type').length >0 && $(this).parent().find('.jetproduct-upc_opt_type').is(':enabled')){
					current_upc_type=$(this).parent().find('.jetproduct-upc_opt_type').val();
				}
				var upc_flag=false;
				upc_flag=checkUPCfromDbforSimple(this,va,product_id,current_upc_type);
				if(!upc_flag){
					disableSaveNUploadButtonForSimple();
					$('#LoadingMSG').hide();
					return false;
				}
				disableSaveNUploadButtonForSimple();
				$('#LoadingMSG').hide();
				
		});
		function checkUPCfromDbforSimple(ele,upc,product_id,upc_type){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
						var var_flag=true;
							 $.ajax({
							 	  async:false,
						          method: "GET",
						          url: url,
						          dataType:'json',
						          data: { product_upc: upc,barcode_type:upc_type,product_id:product_id,type:'simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        		    $(ele).addClass('select_error');
						        			$(ele).parent().append('<span class="jetproduct-upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
						        			//$(ele).val("");
						        			//$(ele).parent().find('.jetproduct-upc_opt_type').val("");
							        		//$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
											//$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
											//$(ele).parent().find('select').prop('disabled', true);
											//$(ele).parent().find('select').css('display', 'none');
											//$(ele).prop('style',"");
											//$(ele).css({'width':'25%'});
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
		function checkUPCfromDbforSimpleOnLoad(ele,upc,product_id,upc_type){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
						var var_flag=true;
							 $.ajax({
						          method: "GET",
						          async:false,
						          url: url,
						          dataType:'json',
						          data: { product_upc: upc,barcode_type:upc_type,product_id:product_id,type:'simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			//$(ele).after('<span class="upc-error error-msg">Entered UPC is already taken.Please use other UPC.</span>');
						        			//$(ele).val("");
						        			//$(ele).parent().find('.jetproduct-upc_opt_type').val("");
							        		//$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
											//$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
											//$(ele).parent().find('select').prop('disabled', true);
											//$(ele).parent().find('select').css('display', 'none');
											//$(ele).prop('style',"");
											//$(ele).css({'width':'25%'});
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
		function checkUPCfromDbforSimpleOnLoadFirst(ele,upc,product_id,upc_type){
						var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
						var var_flag=true;
							 $.ajax({
						          method: "GET",
						          async:false,
						          url: url,
						          dataType:'json',
						          data: { product_upc: upc,barcode_type:upc_type,product_id:product_id,type:'simple', _csrf : csrfToken }
						        })
						        .done(function( msg ) {
						        	if(msg.success){
						        			$(ele).addClass('select_error');
						        			$(ele).parent().append('<span class="jetproduct-upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
						        			
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
	<?php }?>

	function fillBarcodeType(){
							var ele="#jetproduct-upc";
							var va=$(ele).val();
							var reg_upc=/^\d{12}$/;///^\d*[.]?\d+/
							var reg_isbn_10=/^\d{10}$/;
							var reg_isbn_13=/^\d{13}$/;
							var reg_gtin_14=/^\d{14}$/;
							if(reg_upc.test(va)){
								$(ele).prop('style',"");
								$(ele).css({'width':'25%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								$(ele).parent().find('.jetproduct-upc_opt_type').val('UPC');
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
								$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'10%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								$(ele).parent().find('select').prop('disabled', true);
								$(ele).parent().find('select').css('display', 'none');
								return true;
							}else if(reg_isbn_10.test(va)){
								$(ele).prop('style',"");
								$(ele).css({'width':'25%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								$(ele).parent().find('.jetproduct-upc_opt_type').val('ISBN-10');
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
								$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'10%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								$(ele).parent().find('select').prop('disabled', true);
								$(ele).parent().find('select').css('display', 'none');
								return true;
							}else if(reg_isbn_13.test(va)){
								$(ele).prop('style',"");
								$(ele).css({'width':'25%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', true);
								$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
								$(ele).parent().find('select').prop('disabled', false);
								$(ele).parent().find('select').prop('style',"");
								$(ele).parent().find('select').css({'width':'10%','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								return true;
							}else if(reg_gtin_14.test(va)){
								$(ele).prop('style',"");
								$(ele).css({'width':'25%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
								$(ele).parent().find('.jetproduct-upc_opt_type').val('GTIN-14');
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
								$(ele).parent().find('.jetproduct-upc_opt_type').prop('style',"");
								$(ele).parent().find('.jetproduct-upc_opt_type').css({'width':'10%','text-align':'center','padding-left':'4px','padding-right':'4px','display':'inline-block'});
								$(ele).parent().find('select').prop('disabled', true);
								$(ele).parent().find('select').css('display', 'none');
								return true;
							}else{
							 		return false;
							 }
	}
	fillBarcodeType();

</script>