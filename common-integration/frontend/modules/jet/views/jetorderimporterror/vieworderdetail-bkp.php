<style type="text/css">
	.table-bordered td {
	    padding: 10 !important;
	}
</style>
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title" style="text-align: center;">Order Current Status on Jet</h4>
	        </div>
	        <div class="modal-body">
				<div class="jet-product-form">
                    <div class="">
                        <div class="field-jetproduct">
                        	<table class="table table-striped table-bordered">                        		
                        		<tbody>
                        			<? 
                        				if (isset($model['acknowledgement_status'])) 
                        				{
	                        				?>
	                        					<tr>
													<td class="value_label" width="33%">
														<span>Acknowledgement Status</span>
													</td>
													<td>
														<span><?= $model['acknowledgement_status']?></span>
													</td>
												</tr>
	                        				<?
                        				}
                        			?>
									<tr>
										<td class="value_label" width="33%">
											<span>Customer Name</span>
										</td>
										<td>
											<?php 
												if (isset($model['shipping_to'])) {
													?>
														<span><?= $model['shipping_to']['recipient']['name']?></span>
													<?php
												}else{
													?>
													<span><?= $model['buyer']['name']?></span>
													<?php
												}
											?>
										</td>
									</tr>
									<tr>
										<td class="value_label" width="33%">
											<span>Customer Address</span>
										</td>
										<td>
											<?php 
												if (isset($model['shipping_to']['address'])) {
													?>
														<span><?= $model['shipping_to']['address']['address1']." ".$model['shipping_to']['address']['address2'] ;?></span>
													<?php
												}
											?>
										</td>
									</tr>
        							<tr>
										<td class="value_label" width="33%">
											<span>Reference Order ID</span>
										</td>
										<td>
											<span><?= $model['reference_order_id']?></span>
										</td>
									</tr>
									<?php
										if (isset($model['order_items'])) 
										{
											?>
											<tr>
												<td style="background-color:#BBBBBB;text-align:center;font-weight:bold" colspan="5">
													Order Item Details
												</td>
											</tr>
											<tr>
												<th>Order Item ID</th>
												<th>SKU</th>
												<th>Ordered QTY</th>
												<th>Acknowledgement Status</th>
										    </tr>										    
											<?
											foreach ($model['order_items'] as $key => $value) 
											{
												?>	
													<tr>													
														<td>
															<span><?= $value['order_item_id']?></span>
														</td>											
														<td >
															<span><?= $value['merchant_sku']?></span>
														</td>															
														<td >
															<span><?= $value['request_order_quantity']?></span>
														</td>															
														<td>
															<span><?= $value['order_item_acknowledgement_status']?></span>
														</td>
													</tr>													
												<?php
											}																				
										}
									?>
									<tr>
										<td>
											<span>Status</span>
										</td>
										<td>
											<span><?= $model['status']?></span>
										</td>
									</tr>
                           		</tbody>
                        	</table>  
                        </div>
                     </div>
                </div>                
	        </div>
	        <div class="modal-footer">
	          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
	        </div>
	      </div>		      
		</div>
	</div>	 
</div>