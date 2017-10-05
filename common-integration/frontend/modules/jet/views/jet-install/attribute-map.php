<?php
use yii\helpers\Html;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\models\JetAttributeMap;
use frontend\modules\jet\components\AttributeMap;


$url=Data::getUrl('category-attribute/save-attribute-ajax');
$attributes=AttributeMap::getAttributes();

?>
<div class="attribute-map-step attribute-map-index content-section">
	<div class="">
	     <p class="note"><b class="note-text">Note:</b> If you got variations for your products, map jet attributes with your product variant options, or Click <b>Next</b> </p>
    </div>
    <form id="attribute_map" class="" method="post" action="<?= Data::getUrl('jet-attributemap/save-ajax') ?>">
		<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
		<div class="grid-view">
			<table class="table table-striped table-bordered ced-jet-custome-tbl">
				<thead>
					<tr>
						<th>Product Type(shopify)</th>
						<th>Jet - Shopify Attributes</th>
					</tr>
				</thead>
				<tbody>
			<?php 
			if(is_array($attributes) && count($attributes)) {
			  $noAttrFlag = false;
			  $i=0;
			  $count=0;
			  foreach ($attributes as $key=>$attribute) 
			  {

				$options = [];
				if(isset($attribute['shopify_attributes']) && count($attribute['shopify_attributes'])){
					foreach ($attribute['shopify_attributes'] as $shopify_attr) {
						$options[] = $shopify_attr;
					}
				}

				if(count($attribute['category_attributes']['attributes']))
				{
					//$attribute['category_attributes']['attribute_values']['unit'] = AttributeMap::getUnitAttributeValues();
					$count++;
			?>
					<tr <?php if($count%2==0){echo "class='even'";}else{echo "class='odd'";}?>>
						<td><?= $attribute['product_type'] ?></td>
						<td>
							<table class="attrmap-inner-table">
								<th>Jet</th>
								<th>Shopify</th>
								<?php
								foreach ($attribute['category_attributes']['attributes'] as $wal_attr=>$val) {
									//if(!$val['variant'])
										//continue;

									$mapped_value = '';
									if(isset($attribute['mapped_values'][$wal_attr])) {
										$valueType = $attribute['mapped_values'][$wal_attr]['type'];
										$mapped_value = $attribute['mapped_values'][$wal_attr]['value'];
									}
								?>
								<tr>
									<td><?= $val['title'] ?></td>
									<td>
								<?php
							
									$selectTypeFlag = false;
									if(strpos($wal_attr, AttributeMap::ATTRIBUTE_PATH_SEPERATOR) !== false)
									{
										$wal_attrs = explode(AttributeMap::ATTRIBUTE_PATH_SEPERATOR, $wal_attr);
										if(is_array($wal_attrs)) {
											foreach ($wal_attrs as $_wal_attr) {
												if(isset($attribute['category_attributes']['attribute_values'][$_wal_attr]) 
													&& !$val['free_text']) {
													$selectTypeFlag = true;
													$jet_attribute_values = $attribute['category_attributes']['attribute_values'][$_wal_attr];
												}
											}
										}
									}
									else
									{
										if(isset($attribute['category_attributes']['attribute_values'][$wal_attr]) 
											&& !$val['free_text']) {
											$selectTypeFlag = true;
											$jet_attribute_values = $attribute['category_attributes']['attribute_values'][$wal_attr];
										}
									}


									if($selectTypeFlag) {
					
										$walAttrValue = ($jet_attribute_values);
										foreach ($walAttrValue as $value) {
										?>
										<div class="checkbox_options">
											<input type="checkbox" name="jet[<?= $attribute['product_type']?>][<?= $wal_attr ?>]" value="<?= $value ?>" <?php if(in_array($value,explode(',', $mapped_value)) && $valueType==JetAttributeMap::VALUE_TYPE_JET)
												  { echo 'checked="checked"'; } ?>>
											<label><?= $value ?></label>
										</div>
										<?php
										}
									} else {
										if(count($options)) {
										?>				
										
										<?php
										  foreach ($options as $option) {
										?>
											<div class="checkbox_options">
												<input type="checkbox" name="jet[<?= $attribute['product_type']?>][<?= $wal_attr ?>][]" value="<?= $option ?>" <?php if(in_array($option,explode(',', $mapped_value)) && $valueType==JetAttributeMap::VALUE_TYPE_SHOPIFY)
													{ echo 'checked="checked"'; } ?>>
												<label><?= $option ?></label>
											</div>
										<?php
											
										  }
										?>
					    		
										<?php 		
										}
									}
									?>
									</td>
								</tr>	
								<?php
								}
								?>
							</table>
						</td>
					</tr>
				<?php
				}
				elseif(!$noAttrFlag)
				{
					$noAttrFlag = true;
				?>
					<tr>
						<td colspan="3">No Mapped Jet Category has Required Attributes to Map.</td>
					</tr>
				<?php
				}
			  }
			}
			else
			{
			?>
					<tr>
						<td colspan="3">Shopify Product Types are not Mapped With Jet Categories.</td>
					</tr>
			<?php
			}
			?>
				</tbody>
			</table>
			<input type="button" value="Next"  class="btn btn-primary next" />
		</div>	
	</form>
</div>
<style>
	.center,.cat_root{
		text-align: center;
	}
	.table.table-striped.table-bordered.ced-jet-custome-tbl {
	    min-width: 600px;
	}
	.cat_root .form-control{
		display: inline-block;
	}
	.attrmap-inner-table .checkbox_options {
	    float: left;
	    min-width: 30%;
	    margin-right: 8px;
	}
	.attrmap-inner-table tr td {
	    width: 50%;
	}
	
</style>
<script type="text/javascript">
$(document).ready(function(){
	var url = '<?php echo $url;?>';
	UnbindNextClick();
	$('.next').on('click', function(event){	
		//check validation
		$('#LoadingMSG').show();
	    $.ajax({
	            method: "POST",
	            url: url,
	            data: $("form").serialize(),
	    })
	    .done(function(msg)
	    {
	        $('#LoadingMSG').hide();
	        if(msg=="6")
	        {
	          nextStep();
	        }
	        else{
	   
	          $('.top_error').html(msg);
	          $('.top_error').show();
	        }
		});
	});
});
</script>





