<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content' style="width: 103%;">
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title" style="text-align: center;font-family:Comic Sans MS;">Order Current Status on Jet</h4>
	        </div>
	        <div class="modal-body">
				<div class="jet-product-form">
                    <div class="form-group">
                        <div class="field-jetproduct">
                        <?php 
                        	if (empty($model['acknowledgement_status'])) {
                        		$model['acknowledgement_status'] = $model['status'];
                        	}
                        ?>
                        	<table class="table table-striped table-bordered">                        		
                        		<tbody>
                        			<tr>
										<td class="order-column-level" class="order-column-level" colspan="1" >
											<span>Acknowledgement Status</span>
										</td>
										<td colspan="4" >
											<span><?= $model['acknowledgement_status']?></span>
										</td>
									</tr>
									<tr>
										<td class="order-column-level" colspan="1">
											<span>Merchant Order ID</span>
										</td>
										<td  colspan="4">
											<span><?= $model['merchant_order_id']?></span>
										</td>
									</tr>
									<tr>
										<td class="order-column-level" colspan="1">
											<span>Customer Name</span>
										</td>
										<td  colspan="4">
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
										<td class="order-column-level" colspan="1">
											<span>Customer Phone Number</span>
										</td>
										<td  colspan="4">
											<?php if (isset($model['buyer'])) {?>
													<span><?= $model['buyer']['phone_number']?></span>
											<?php } ?>
										</td>
									</tr>
									<?php if (isset($model["hash_email"])) {?>
										<tr>
											<td class="order-column-level" colspan="1">
												<span>Customer Email Id</span>
											</td>
											<td  colspan="4">
												<span><?= $model['hash_email'];?></span>
											</td>
										</tr>
									<?php } ?>
									<tr>
										<td class="order-column-level" colspan="1">
											<span>Customer Address</span>
										</td>
										<td  colspan="4">
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
										<td class="order-column-level" colspan="1">
											<span>Reference Order ID</span>
										</td>
										<td  colspan="4">
											<span><?= $model['reference_order_id']?></span>
										</td>
									</tr>
									<?php
										if (isset($model['exception_state'])) 
										{
											?>
											<tr>
												<td class="order-column-level" colspan="1">
													<span>Exception State</span>
												</td>
												<td  colspan="4">
													<span><?= $model['exception_state']?></span>
												</td>
											</tr>
											<?
										}
										if (isset($model['order_items'])) 
										{
											?>
											<tr>
												<td class="order-view-td-header" colspan="5">
													Order Item Details
												</td>
											</tr>
											<tr>
												<th>Order Item ID</th>
												<th>SKU</th>
												<th>Ordered QTY</th>
												<th colspan="2">Acknowledgement Status</th>
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
														<?php if (empty($value['order_item_acknowledgement_status'])) {
															$value['order_item_acknowledgement_status'] = "";
														} ?>															
														<td colspan="2">
															<span><?= $value['order_item_acknowledgement_status']?></span>
														</td>
													</tr>													
												<?php
											}																				
										}
										if (isset($model['shipments'])) 
										{
											?>
											<tr>
												<td class="order-view-td-header" colspan="5">
													Order Shipment Details
												</td>
											</tr>
											<tr>
												<th>Carrier</th>
												<th>Tracking Number</th>												
												<th>SKU</th>
												<th>Ordered Qty</th>
												<th>Cancelled Qty</th>
										    </tr>		
											<?
											foreach ($model['shipments'] as $key => $value) 
											{
												?>	
													<tr>	
														<td>
															<span><?= isset($value['carrier'])?$value['carrier']:""; ?></span>
														</td>
														<td>
															<span><?= isset($value['shipment_tracking_number'])?$value['shipment_tracking_number']:""; ?></span>
														</td>
														<td>
															<span><?= $value['shipment_items'][0]['merchant_sku']?></span>
														</td>																	
														<td>
															<span><?= isset($value['shipment_items'][0]['response_shipment_sku_quantity'])?  $value['shipment_items'][0]['response_shipment_sku_quantity'] : "" ?></span>
														</td>			
														<td>
															<span><?= isset($value['shipment_items'][0]['response_shipment_cancel_qty']) ?  $value['shipment_items'][0]['response_shipment_cancel_qty'] : ""; ?></span>
														</td>			
													</tr>													
												<?php
											}																					
										}
									?>
									<tr>
										<td class="order-column-level" ccolspan="1">
											<span>Status</span>
										</td>
										<td colspan="4">
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