<?php
use yii\helpers\Html;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartRepricing;

$this->title = 'Walmart Repricing Section';

$reprice = new WalmartRepricing();
?>

<div class="content-section">
    <div class="form new-section">
    	<form method="post" action="<?= Data::getUrl('walmart-reprice/save') ?>" name="">
		    <div class="jet-pages-heading">
		        <div class="title-need-help">
		            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		            <!-- <a class="help_jet" target="_blank" href="https://shopify.cedcommerce.com/integration/walmart-marketplace/sell-on-walmart" title="Need Help"></a> -->
		        </div>
		        <div class="product-upload-menu">
		        	<!-- <button type="button" id="instant-help" class="btn btn-primary">Help</button> -->
		            <input type="submit" name="submit" value="save" class="btn btn-primary">
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

			<div class="ced-entry-heading-wrapper">
				<div class="entry-edit-head">
					<h4 class="fieldset-legend">Product Information</h4>
				</div>
				<div class="fieldset enable_api" id="api-section">
					<table class="table table-striped table-bordered" cellspacing="0">
						<tbody>
							<tr>
							    <td class="value_label"><span>Product Title</span></td>
							    <td><span><?= $productData['title'] ?></span></td>
							</tr>
							<tr>
							    <td class="value_label"><span>Status on Walmart</span></td>
							    <td><span><?= $productData['status'] ?></span></td>
							</tr>
							<tr>
							    <td class="value_label"><span>Product Type</span></td>
							    <td><span><?= $productData['type'] ?></span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

	<?php if($productData['type'] == 'simple') : ?>
		<?php 
				$upc = $productData['upc'];
				$bestMktPrice = $reprice->getBestMarketplacePrice($upc,true);
				$apiData = [];
				if(isset($bestMktPrice['errors'])) {
					$bestMktPrice = ['error'=>'Product Not Found on Walmart.'];
                }elseif(!isset($bestMktPrice['items'][0]['bestMarketplacePrice'])) {
                	$bestMktPrice = ['error'=>'NOT AVAILABLE'];
                } else {
                	$apiData = $bestMktPrice['items'][0];
                	$bestMktPrice = $apiData['bestMarketplacePrice'];
                }

                $total = 0;
                $savedData = WalmartRepricing::getSavedRepricingData($productData['product_id']);
		?>
			<div class="ced-entry-heading-wrapper">
				<div class="entry-edit-head">
					<h4 class="fieldset-legend">Product Pricing</h4>
				</div>
				<div class="fieldset enable_api" id="api-section">
					<input type="hidden" name="product_id" value="<?= $productData['product_id'] ?>" />
					<table class="table table-striped table-bordered" cellspacing="0">
						<thead>
							<tr>
								<th>SKU</th>
								<th>YOUR PRICE</th>
								<th>BEST PRICE ON WALMART</th>
							</tr>
						</thead>
						<tbody>
							<tr>
							    <td><span><?= $productData['sku'] ?></span></td>
							    <td><span><?= $productData['price'] ?></span></td>
							    <td>
							    	<span>
							    <?php if(!isset($bestMktPrice['error']))
							    	  {
							    		$bestMktPrice = WalmartRepricing::removeIndexes($bestMktPrice);
							    		$total = WalmartRepricing::getTotal($bestMktPrice);
							    		foreach ($bestMktPrice as $key => $value) :
							    ?>
							    		<span><?= $key ?> : <?= $value ?></span><br/>
							    <?php 	endforeach; ?>
							    		<span><b>Total : <?= $total ?></b></span>
							    <?php
						    		  }
						    		  else
						    		  {
						    			echo $bestMktPrice['error'];
						    		  } 
							    ?>
							    	</span>
							    </td>
							</tr>

							<tr>
								<input type="hidden" name="id" value="<?= isset($savedData[$productData['product_id']]['id'])?$savedData[$productData['product_id']]['id']:'' ?>" />
								<input type="hidden" name="option_id" value="<?= '' ?>" />
								<input type="hidden" name="best_price" value="<?= $total ?>" />
								<input type="hidden" name="walmart_itemid" value="<?= isset($apiData['itemId'])?$apiData['itemId']:'' ?>" />
								<input type="hidden" name="upc" value="<?= $upc ?>" />
								<td colspan="5">
									<div>
										<div>
											<label>Min Price</label>
											<input type="text" name="min_price" value="<?= isset($savedData[$productData['product_id']]['min_price'])?$savedData[$productData['product_id']]['min_price']:'' ?>" class="form-control" />
										</div>
										<div>
											<label>Max Price</label>
											<input type="text" name="max_price" value="<?= isset($savedData[$productData['product_id']]['max_price'])?$savedData[$productData['product_id']]['max_price']:'' ?>" class="form-control" />
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
	<?php elseif($productData['type'] == 'variants') :	?>
			<div class="ced-entry-heading-wrapper">
				<div class="entry-edit-head">
					<h4 class="fieldset-legend">Product Pricing</h4>
				</div>
				<div class="fieldset enable_api" id="api-section">
					<input type="hidden" name="product_id" value="<?= $productData['product_id'] ?>" />
			<?php 
				$variants = WalmartRepricing::getProductVariants($productData['product_id']);
				$savedData = WalmartRepricing::getSavedRepricingData($productData['product_id']);
				if($variants) {
					foreach ($variants as $variant) {
						$upc = $variant['option_unique_id'];
						$bestMktPrice = $reprice->getBestMarketplacePrice($upc,true);
						$apiData = [];
						if(isset($bestMktPrice['errors'])) {
							$bestMktPrice = ['error'=>'Product Not Found on Walmart.'];
		                }elseif(!isset($bestMktPrice['items'][0]['bestMarketplacePrice'])) {
		                	$bestMktPrice = ['error'=>'NOT AVAILABLE'];
		                } else {
		                	$apiData = $bestMktPrice['items'][0];
		                	$bestMktPrice = $apiData['bestMarketplacePrice'];
		                }

		                $total = 0;
			?>
					<table class="table table-striped table-bordered" cellspacing="0">
						<thead>
							<tr>
								<th>TITLE</th>
								<th>SKU</th>
								<th>QUANTITY</th>
								<th>YOUR PRICE</th>
								<th>BEST PRICE ON WALMART</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><span><?= $variant['option_title'] ?></span></td>
							    <td><span><?= $variant['option_sku'] ?></span></td>
							    <td><span><?= $variant['option_qty'] ?></span></td>
							    <td><span><?= $variant['option_price'] ?></span></td>
							    <td>
							    	<span>
							    <?php if(!isset($bestMktPrice['error']))
							    	  {
							    	  	//var_dump($bestMktPrice);
							    		$bestMktPrice = WalmartRepricing::removeIndexes($bestMktPrice);
							    		$total = WalmartRepricing::getTotal($bestMktPrice);
							    		foreach ($bestMktPrice as $key => $value) :
							    ?>
							    		<span><?= $key ?> : <?= $value ?></span><br/>
							    <?php 	endforeach; ?>
							    		<span><b>Total : <?= $total ?></b></span>
							    <?php
						    		  }
						    		  else
						    		  {
						    			echo $bestMktPrice['error'];
						    		  } 
							    ?>
							    	</span>
							    </td>
							</tr>

							<tr>
								<input type="hidden" name="id[]" value="<?= isset($savedData[$variant['option_id']]['id'])?$savedData[$variant['option_id']]['id']:'' ?>" />
								<input type="hidden" name="option_id[]" value="<?= $variant['option_id'] ?>" />
								<input type="hidden" name="best_price[]" value="<?= $total ?>" />
								<input type="hidden" name="walmart_itemid[]" value="<?= isset($apiData['itemId'])?$apiData['itemId']:'' ?>" />
								<input type="hidden" name="upc[]" value="<?= $upc ?>" />
								<td colspan="5">
									<div>
										<div>
											<label>Min Price</label>
											<input type="text" name="min_price[]" value="<?= isset($savedData[$variant['option_id']]['min_price'])?$savedData[$variant['option_id']]['min_price']:'' ?>" class="form-control" />
										</div>
										<div>
											<label>Max Price</label>
											<input type="text" name="max_price[]" value="<?= isset($savedData[$variant['option_id']]['max_price'])?$savedData[$variant['option_id']]['max_price']:'' ?>" class="form-control" />
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
			<?php 
					}	
				}	
			?>
				</div>
			</div>
	<?php endif;	?>
		</form>
	</div>
</div>