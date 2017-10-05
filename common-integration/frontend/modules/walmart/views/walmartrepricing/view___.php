<?php 
use yii\helpers\Html;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartProduct;
use frontend\modules\walmart\components\WalmartRepricing;

$this->title = 'Walmart Repricing Section';

/*$buyBoxReport = WalmartRepricing::readBuyboxCsv($csvFilePath);*/
?>

<div class="content-section">
    <div class="form new-section">
    	<form method="post" action="<?= Data::getUrl('walmartrepricing/save') ?>" name="">
		    <div class="jet-pages-heading">
		        <div class="title-need-help">
		            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		            <!-- <a class="help_jet" target="_blank" href="https://shopify.cedcommerce.com/integration/walmart-marketplace/sell-on-walmart" title="Need Help"></a> -->
		        </div>
		        <div class="product-upload-menu">
		        	<!-- <button type="button" id="instant-help" class="btn btn-primary">Help</button> -->
		        	<a href="<?= Data::getUrl('walmartrepricing/index');?>">
                            <button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
                    </a>
            		</button>
		            <input type="submit" name="submit" value="Save" class="btn btn-primary">
		        </div>
				<div class="clear"></div>
			</div>

			<div class="jet-pages-heading">
				<p class="legend-text">
			    	<b>Min Price</b>
			    	<b>:</b>
			    	<span style="color: #333">Minimun price at which you want your product to be Sold.</span>
			  	</p>
				<p class="legend-text">
				    <b>Max Price</b>
				  	<b>:</b>
				    <span style="color: #333">Maximum price at which you want your product to be Sold.</span>
				</p>
			</div>
<?php 		$products = WalmartProduct::getProductInfo(implode(',', $productIds));
			if($products)
			{
				$key = 0;
				foreach ($products as $product) 
				{
					/*[title] =&gt; Jewelry gold
			        [sku] =&gt; 21-LR8724-32505P-
			        [type] =&gt; variants
			        [product_type] =&gt; Diamond Necklace
			        [price] =&gt; 5004.00
			        [status] =&gt; Item Processing
			        [product_title] =&gt; 
			        [product_price] =&gt; 
			        [product_id] =&gt; 4211751366*/

					$title = empty($product['product_title'])?$product['title']:$product['product_title'];
					$price = empty($product['product_price'])?$product['price']:$product['product_price'];
?>
				
					<div class="ced-entry-heading-wrapper">
						<div class="entry-edit-head">
							<h4 class="fieldset-legend">Product Information</h4>
						</div>
						<div class="fieldset enable_api" id="api-section">
							<table class="table table-striped table-bordered" cellspacing="0">
								<tbody>
									<tr>
									    <td class="value_label"><span>Product Title</span></td>
									    <td><span><?= $title ?></span></td>
									</tr>
									<tr>
									    <td class="value_label"><span>Shopify Product Type</span></td>
									    <td><span><?= $product['product_type'] ?></span></td>
									</tr>
									<tr>
									    <td class="value_label"><span>Type</span></td>
									    <td><span><?= $product['type'] ?></span></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
<?php
					if($product['type'] == 'variants')
					{
						$variants = WalmartProduct::getVariantsProductInfo($product['product_id']);
						if($variants)
						{
							$k = 0;
							foreach ($variants as $variant) 
							{
								/*[option_title] =&gt; steal
					            [option_sku] =&gt; beauty_ring
					            [option_image] =&gt; 
					            [option_price] =&gt; 1200.00
					            [status] =&gt; Item Processing
					            [option_prices] =&gt; */

					            $price = empty($variant['option_prices'])?$variant['option_price']:$variant['option_prices'];
					            $your_price_json = json_decode($variant['your_price'],true);
					            $best_price_json = json_decode($variant['best_prices'],true);

?>
								<div class="ced-entry-heading-wrapper">
									<div class="entry-edit-head">
										<h4 class="fieldset-legend">Product Pricing</h4>
									</div>
									<div class="fieldset enable_api" id="api-section">
										<input type="hidden" name="data[<?= $key?>][product_id]" value="<?= $product['product_id'] ?>" />
										<input type="hidden" name="data[<?= $key?>][variant][<?= $k ?>][option_id]" value="<?= $variant['option_id'] ?>" />
										<table class="table table-striped table-bordered" cellspacing="0">
											<thead>
												<tr>
													<th>TITLE</th>
													<th>SKU</th>
													<th>STATUS ON WALMART</th>
													<!-- <th>QUANTITY</th> -->
													<th>YOUR PRICE</th>
													<th>BEST PRICE ON WALMART</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><span><?= $variant['option_title'] ?></span></td>
												    <td><span><?= $variant['option_sku'] ?></span></td>
												    <td><span><?= $variant['status'] ?></span></td>
												    <!-- <td><span><?= 'option_qty' ?></span></td> -->
												    <td>
												    <?php 
												    	if(!isset($variant['option_sku'])) 
												    	{
												    ?>
												    		<span><?= $price ?></span>
												    <?php
												    	}
												    	else
												    	{
												    ?>
													    	<table>
													    		<tr>
												   					<td>Your Price :</td>
												   					<td><?= $your_price_json['price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>Your Ship Price :</td>
												   					<td><?= $your_price_json['ship'] ?></td>
												   				</tr>
													    	</table>
													<?php
												    	}
													?>
												    </td>
												    <td>
												    	<span>
												    <?php 
												    	if(isset($variant['option_sku'])) {
												    	foreach ($best_price_json as $bestkey => $bestvalue) {?>
												    		<table>
												   				<tr>
												   					<td>Buy Box Winner :</td>
												   					<td><?php  $boxwinner = ($variant['buybox']==0) ? 'NO' : 'YES';
										   					echo $boxwinner; ?></td>
												   				</tr>

												   				<tr>
												   					<td>BuyBox Price :</td>
												   					<td><?= $bestvalue['buybox_item_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>BuyBox Ship Price :</td>
												   					<td><?= $bestvalue['buybox_ship_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>2nd Best Offer Price :</td>
												   					<td><?= $best_price_json[1]['offer2_item_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>2nd Best Offer Ship Price :</td>
												   					<td><?= $best_price_json[1]['offer2_ship_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>3rd Best Offer Price :</td>
												   					<td><?= $best_price_json[2]['offer3_item_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>3rd Best Offer Ship Price :</td>
												   					<td><?= $best_price_json[2]['offer3_ship_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>4th Best Offer Price :</td>
												   					<td><?= $best_price_json[3]['offer4_item_price'] ?></td>
												   				</tr>

												   				<tr>
												   					<td>4th Best Offer Price :</td>
												   					<td><?= $best_price_json[3]['offer4_ship_price'] ?></td>
												   				</tr>
												   			</table>
												    <?php
												    	break;} 	}
												    	else {
										   					echo "<b>This Product(Sku) not Found/Uploaded to Walmart.</b>";
										   				} 
										   			?>
												    	</span>
												    </td>
												</tr>

												<tr>
													<td colspan="5">
														<div>
															<div>
																<label>Want to Perform Repricing For this Product?</label>
																<select name="data[<?= $key?>][variant][<?= $k ?>][enable_repricing]" class="form-control">
																	<option value=""></option>
																	<option value="1" <?php if($variant['repricing_status']=='1')
															echo 'selected="selected"';?>>yes</option>
																	<option value="0" <?php if($variant['repricing_status']=='0')
															echo 'selected="selected"';?>>no</option>
																</select>
															</div>
															<div>
																<label>Min Price</label>
																<input type="text" name="data[<?= $key?>][variant][<?= $k ?>][min_price]" value="<?=$variant['min_price'] ?>" class="form-control" />
															</div>
															<div>
																<label>Max Price</label>
																<input type="text" name="data[<?= $key?>][variant][<?= $k ?>][max_price]" value="<?=$variant['max_price'] ?>" class="form-control" />
															</div>
														</div>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<?php $k++?>
<?php 						}
						}
					}
					elseif($product['type'] == 'simple')
					{
						$price = empty($product['product_price'])?$product['price']:$product['product_price'];
						if(isset($product['your_price'])){
							$price_json = json_decode($product['your_price'],true);
						}
						$best_price_json = json_decode($product['best_prices'],true);
?>
						<div class="ced-entry-heading-wrapper">
							<div class="entry-edit-head">
								<h4 class="fieldset-legend">Product Pricing</h4>
							</div>
							<div class="fieldset enable_api" id="api-section">
								<input type="hidden" name="data[<?= $key?>][product_id]" value="<?= $product['product_id'] ?>" />
								<table class="table table-striped table-bordered" cellspacing="0">
									<thead>
										<tr>
											<th>SKU</th>
											<th>STATUS ON WALMART</th>
											<th>YOUR PRICE</th>
											<th>BEST PRICE ON WALMART</th>
										</tr>
									</thead>
									<tbody>
										<tr>
										    <td><span><?= $product['sku'] ?></span></td>
										    <td><span><?= $product['status'] ?></span></td>
										    <td>
										    <?php 
										   		if(!isset($product['sku'])) 
										   		{
										   	?>
										    		<span><?= $price ?></span>
										    <?php 
										    	}
										    	else
										    	{
										    ?>
											    	<table>
											    		<tr>
											   				<td>Your Price :</td>
											   				<td><?= $price_json['price'] ?></td>
										   				</tr>
										   				<tr>
										   					<td>Your Ship Price :</td>
										   					<td><?= $price_json['ship'] ?></td>
										   				</tr>
											    	</table>
										   	<?php
										   		}
										   	?>
										    </td>
										    <td>
										    	<span>
										   	<?php 
										   		if(isset($product['sku'])) {
										   			foreach ($best_price_json as $bestkey => $bestvalue) {
										   				
										   	?>
										   			<table>
										   				<tr>
										   					<td>Buy Box Winner :</td>
										   					<td><?php  $boxwinner = ($product['buybox']==0) ? 'NO' : 'YES';
										   					echo $boxwinner; ?></td>
										   				</tr>

										   				<tr>
										   					<td>BuyBox Price :</td>
										   					<td><?= $bestvalue['buybox_item_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>BuyBox Ship Price :</td>
										   					<td><?= $bestvalue['buybox_ship_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>2nd Best Offer Price :</td>
										   					<td><?= $best_price_json[1]['offer2_item_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>2nd Best Offer Ship Price :</td>
										   					<td><?= $best_price_json[1]['offer2_ship_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>3rd Best Offer Price :</td>
										   					<td><?= $best_price_json[2]['offer3_item_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>3rd Best Offer Ship Price :</td>
										   					<td><?= $best_price_json[2]['offer3_ship_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>4th Best Offer Price :</td>
										   					<td><?= $best_price_json[3]['offer4_item_price'] ?></td>
										   				</tr>

										   				<tr>
										   					<td>4th Best Offer Price :</td>
										   					<td><?= $best_price_json[3]['offer4_ship_price'] ?></td>
										   				</tr>
										   			</table>
										   	<?php
										   		break;}}
										   		else {
										   			echo "<b>This Product(Sku) not Found/Uploaded to Walmart.</b>";
										   		} 
										   	?>
										    	</span>
										    </td>
										</tr>

										<tr>
											<td colspan="5">
												<div>
													<div>
														<label>Want to Perform Repricing For this Product?</label>
														<select name="data[<?= $key?>][enable_repricing]" class="form-control">
															<option value=""></option>
															<option value="1" <?php if($product['repricing_status']=='1')
															echo 'selected="selected"';?>>yes</option>
															<option value="0" <?php if($product['repricing_status']=='0')
															echo 'selected="selected"';?>>no</option>
														</select>
													</div>
													<div>
														<label>Min Price</label>
														<input type="text" name="data[<?= $key?>][min_price]" class="form-control" value ="<?=$product['min_price']?>"/>
													</div>
													<div>
														<label>Max Price</label>
														<input type="text" name="data[<?= $key?>][max_price]" class="form-control" value ="<?=$product['max_price']?>" />
													</div>
													<div>
														<p class="note"><b>Note :</b> For Deleting Reprice make both Min Price & Max Price Blank. </p>
													</div>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
<?php 				}
			$key++;
				}
			}
			else
			{
				echo "No Products Found for update.";
			}
?>
		</form>
	</div>
</div>
<script type="text/javascript">
	$('.ced-entry-heading-wrapper input').each(function () {
           var type = $(this).attr("type");
           if(type == "text"){
           	console.log($(this).val());
            $(this).after('<span class="glyphicon glyphicon glyphicon-pencil"></span>');
            }
        });
</script>
