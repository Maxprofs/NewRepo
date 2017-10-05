<div class="container">
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="text-align: center;">Product
                    Information on Jet</h4>
                </div>
                <div class="modal-body">
                	<div class="sku_details jet_details_heading">
                		<span>SKU Data from jet</span>
	                    <table class="table table-striped table-bordered">
	                        <tbody>
	                        <tr>
	                            <td class="value_label" width="33%">
	                                <span>Title</span>
	                            </td>
	                            <td class="value form-group " width="100%">
	                                <span><?= $skuData['product_title'] ?></span>
	                            </td>
	                        </tr>
	                        <tr>
	                            <td class="value_label" width="33%">
	                                <span>SKU</span>
	                            </td>
	                            <td class="value form-group " width="100%">
	                                <span><?= $skuData['merchant_sku'] ?></span>
	                            </td>
	                        </tr>                                            
	                        <tr>
	
	                            <?php if (isset($skuData['best_marketplace_offer'])) { ?>
	                                <td class="value_label" width="33%">
	                                    <span>Best Marketplace Price </span>
	                                </td>
	                                <td class="value form-group " width="100%">
	                                    <span><?= $skuData['best_marketplace_offer'][0]['shipping_price'] + $skuData['best_marketplace_offer'][0]['item_price'] ?></span>
	                                </td>
	                            <?php } ?>
	                        </tr>
	
	                        <tr>
	                            <td class="value_label" width="33%">
	                                <span>Brand</span>
	                            </td>
	                            <td class="value form-group " width="100%">
	                                <span><?= $skuData['brand'] ?></span>
	                            </td>
	                        </tr>
	                        <tr>
	                            <td class="value_label" width="33%">
	                                <span>Image</span>
	                            </td>
	                            <td class="value form-group " width="100%">
	                                <img src="<?= $skuData['main_image_url'] ?>" height="100" width="100"/>
	                            </td>
	                        </tr>
	                        <?php if (isset($skuData['standard_product_codes']) && count($skuData['standard_product_codes']) > 0) {
	                            foreach ($skuData['standard_product_codes'] as $key => $val) {
	                                ?>
	                                <tr>
	                                    <td class="value_label" width="33%">
	                                        <span>Barcode</span>
	                                    </td>
	                                    <td class="value form-group " width="100%">
	                                        <span><?= $val['standard_product_code'] ?></span>
	                                        <span
	                                            style="margin-left: 3%; border: medium;"><b><?= $val['standard_product_code_type'] ?></b></span>
	                                    </td>
	                                </tr>
	                                <?php
	                            }
	                        } ?>
	                        <?php if (isset($skuData['ASIN']) && $skuData['ASIN']) {
	                            ?>
	                            <tr>
	                                <td class="value_label" width="33%">
	                                    <span>ASIN</span>
	                                </td>
	                                <td class="value form-group " width="100%">
	                                    <span><?= $skuData['ASIN'] ?></span>
	                                </td>
	                            </tr>
	                            <?php
	                        } ?>
	                        <?php if (isset($skuData['multipack_quantity']) && $skuData['multipack_quantity']) {
	                            ?>
	                            <tr>
	                                <td class="value_label" width="33%">
	                                    <span>Multipack Quantity</span>
	                                </td>
	                                <td class="value form-group " width="100%">
	                                    <span><?= $skuData['multipack_quantity'] ?></span>
	                                </td>
	                            </tr>
	                            <?php
	                        } ?>
	                        <?php if (isset($skuData['mfr_part_number']) && $skuData['mfr_part_number']) {
	                            ?>
	                            <tr>
	                                <td class="value_label" width="33%">
	                                    <span>Manufacturar Part Number</span>
	                                </td>
	                                <td class="value form-group " width="100%">
	                                    <span><?= $skuData['mfr_part_number'] ?></span>
	                                </td>
	                            </tr>
	                            <?php
	                        } ?>
	                        <?php if (isset($skuData['children_skus']) && count($skuData['children_skus']) > 0) {
	                            $childSku = array();
	                            foreach ($skuData['children_skus'] as $key => $val) {
	                                $childSku[] = $val['merchant_sku'];
	                            }
	                            ?>
	                            <tr>
	                                <td class="value_label" width="33%">
	                                    <span>Childrens SKU's</span>
	                                </td>
	                                <td class="value form-group " width="100%">
	                                    <span><? ?></span>
	                                    <span style="margin-right: 3%;"><?= implode(' ,  ', $childSku); ?></span>
	                                </td>
	                            </tr>
	                            <?php
	                        } ?>
	                        <?php if (isset($skuData['jet_sku']) && $skuData['jet_sku']) {
	                            ?>
	                                <tr>
	                                    <td class="value_label" width="33%">
	                                        <span>Jet SKU</span>
	                                    </td>
	                                    <td class="value form-group " width="100%">
	                                        <span><?= $skuData['jet_sku'] ?></span>
	                                    </td>
	                                </tr>
	                            <?php
	                        }
	                        if (isset($skuData['is_archived']) && $skuData['is_archived']) {
	                            ?>
	                                <tr>
	                                    <td class="value_label" width="33%">
	                                        <span>Is Archived</span>
	                                    </td>
	                                    <td class="value form-group " width="100%">
	                                        <span>Yes</span>
	                                    </td>
	                                </tr>
	                            <?php
	                        }
	                        if (isset($skuData['status']) && $skuData['status']) {
	                            ?>
	                                <tr>
	                                    <td class="value_label" width="33%">
	                                        <span>Status</span>
	                                    </td>
	                                    <td class="value form-group " width="100%">
	                                        <span><?= $skuData['status'] ?></span>
	                                    </td>
	                                </tr>
	                            <?php
	                        }
	                        if (count($skuData['sub_status']) > 0) 
	                        {
	                            ?>
	                            <tr>
	                                <td class="value_label" width="33%">
	                                    <span>Sub Status</span>
	                                </td>
	                                <td class="value form-group " width="100%">
	                                    <span><?= $skuData['sub_status'][0] ?></span>
	                                </td>
	                            </tr>
	                            <?php
	                        } ?>                        
	                        </tbody>
	                    </table>
                    </div>
                    <?php 
                    	if (!empty($priceData) && isset($priceData['price']))
                    	{
                    		?>
                    			<div class="price_details jet_details_heading">
			                		<span>Price details from jet</span>
				                    <table class="table table-striped table-bordered">
				                        <tbody>
					                       <tr>
		                                        <td class="value_label" width="33%">
		                                            <span>Your Price</span>
		                                        </td>
		                                        <td class="value form-group " width="100%">
		                                            <span><?= $priceData['price'] ?></span>
		                                        </td>
		                                    </tr>
		                                     <tr>
		                                        <td class="value_label" width="33%">
		                                            <span>Price last updated</span>
		                                        </td>
		                                        <td class="value form-group " width="100%">
		                                            <span><?= $priceData['price_last_update'] ?></span>
		                                        </td>
		                                    </tr>
				                        </tbody>
				                    </table>
				              </div>
                    		<?php 
                    	}
                    	if (!empty($qtyData) && isset($qtyData['inventory_last_update']))
                    	{
                    		?>
                    			 <div class="price_details jet_details_heading">
				                		<span>Inventory details from jet</span>
					                    <table class="table table-striped table-bordered">
					                        <tbody>
						                        <tr>
						                            <td class="value_label" width="33%">
						                                <span>Inventory</span>
						                            </td>
						                            <td class="value form-group " width="100%">
						                                <span><?= isset($skuData['inventory_by_fulfillment_node'][0]['quantity']) ? $skuData['inventory_by_fulfillment_node'][0]['quantity'] : 0 ?></span>
						                            </td>
						                        </tr> 
						                        
						                        <tr>
						                            <td class="value_label" width="33%">
						                                <span>Inventory</span>
						                            </td>
						                            <td class="value form-group " width="100%">
						                                <span><?= isset($skuData['inventory_last_update']) ? $skuData['inventory_last_update'] : '' ?></span>
						                            </td>
						                        </tr> 
					                        </tbody>
					                    </table>
					              </div>                    			
                    		<?php 
                    	}
                    ?>	             
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>