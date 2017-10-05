<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title" style="text-align: center;font-family: "Comic Sans MS";">Order Return Status on Jet</h4>
	        </div>
	        <div class="modal-body">
				<div class="jet-product-form">
                    <div class="form-group">
                        <div class="field-jetproduct">
                          <div class="table-responsive">
                        	<table class="table table-striped table-bordered">                        		
                        		<tbody>
                        			<?php 
                        				if (isset($model['agree_to_return_charge'])) 
                        				{
                        					?>
                        						<tr>
													<td class="order-column-level" colspan="1">
														<span>Acknowledgement Status</span>
													</td>
													<td colspan="4" >
														<span><? if($model['agree_to_return_charge']==1){echo "Yes";} ?></span>
													</td>
												</tr>
                        					<?php
                        				}
                        				if (isset($model['merchant_order_id'])) 
                        				{
                        					?>
                        						<tr>
													<td class="order-column-level" colspan="1">
														<span>Merchant Order ID</span>
													</td>
													<td colspan="4" >
														<span><?= $model['merchant_order_id']?></span>
													</td>
												</tr>
                        					<?php
                        				}
                        				if (isset($model['reference_order_id'])) 
                        				{
                        					?>
                        						<tr>
													<td class="order-column-level" colspan="1">
														<span>Reference Order ID</span>
													</td>
													<td colspan="4" >
														<span><?= $model['reference_order_id']?></span>
													</td>
												</tr>
                        					<?php
                        				}

                        				if (isset($model['merchant_return_charge'])) 
                        				{
                        					?>
                        						<tr>
													<td class="order-column-level" colspan="1">
														<span>Return Charge</span>
													</td>
													<td colspan="4" >
														<span><?= $model['merchant_return_charge']?></span>
													</td>
												</tr>
                        					<?php
                        				}
                        				if (isset($model['shipping_carrier'])) 
                        				{
                        					?>
                        						<tr>
													<td class="order-column-level" colspan="1">
														<span>Shipping Carrier</span>
													</td>
													<td colspan="4" >
														<span><?= $model['shipping_carrier']?></span>
													</td>
												</tr>
                        					<?php
                        				}
                        				if (isset($model['tracking_number'])) 
                        				{
                        					?>
                        						<tr>
													<td class="order-column-level" colspan="1">
														<span>Tracking Number</span>
													</td>
													<td colspan="4" >
														<span><?= $model['tracking_number']?></span>
													</td>
												</tr>
                        					<?php
                        				}
                        				if (isset($model['items'])) 
										{
											?>
											<tr>
												<td style="background-color:#BBBBBB;text-align:center;font-weight:bold" colspan="5">
													Item Details
												</td>
											</tr>
											<tr>
												<th>Order Item ID</th>
												<th>Order Return Refund Qty</th>
												<th>Total Quantity Returned</th>
												<th>Return Refund Feedback</th>
												<th>Items Refund Amount</th>
										    </tr>										    
											<?
											foreach ($model['items'] as $key => $value) 
											{
												?>	
													<tr>													
														<td>
															<span><?= $value['order_item_id']?></span>
														</td>											
														<td >
															<span><?= $value['order_return_refund_qty']?></span>
														</td>															
														<td >
															<span><?= $value['total_quantity_returned']?></span>
														</td>															
														<td>
															<span><?= isset($value['return_refund_feedback'])?$value['return_refund_feedback']:""; ?></span>
														</td>
														<td>
															<?php 
						                        				if (isset($value['refund_amount'])) 
						                        				{
						                        					$totalAmount = 0.00;
						                        					$totalAmount = $value['refund_amount']['principal'] + $value['refund_amount']['tax'] + $value['refund_amount']['shipping_cost'] + $value['refund_amount']['shipping_tax'];			
						                        				}
						                        			?>
															<span><?= $totalAmount;?></span>
														</td>
													</tr>													
												<?php
											}																
										}
                        				
                        				if (isset($model['return_merchant_SKUs'])) 
										{
											?>
											<tr>
												<td style="background-color:#BBBBBB;text-align:center;font-weight:bold" colspan="5">
													Return SKU(s)
												</td>
											</tr>
											<tr>
												<th>Order Item ID</th>
												<th>SKU</th>
												<th>Return Quantity</th>
												<th>Reason</th>
												<th>Total Refund Amount</th>
										    </tr>										    
											<?
											foreach ($model['return_merchant_SKUs'] as $key => $value) 
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
															<span><?= $value['return_quantity']?></span>
														</td>															
														<td>
															<span><?= $value['reason']?></span>
														</td>
														<td>
															<?php 
						                        				if (isset($value['requested_refund_amount'])) 
						                        				{
						                        					$totalAmount1 = 0.00;
						                        					$totalAmount1 = $value['requested_refund_amount']['principal'] + $value['requested_refund_amount']['tax'] + $value['requested_refund_amount']['shipping_cost'] + $value['requested_refund_amount']['shipping_tax'];			
						                        				}
						                        			?>
															<span><?= $totalAmount1;?></span>
														</td>
													</tr>													
												<?php
											}																
										}                        														
									?>
									<tr>
										<td class="order-column-level" colspan="1">
											<span>Return Status</span>
										</td>
										<td colspan="4" >
											<span><?= $model['return_status']?></span>
										</td>
									</tr>
                           		</tbody>
                        	</table>
                          </div>  
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