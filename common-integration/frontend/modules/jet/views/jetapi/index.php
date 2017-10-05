
<?php
/* @var $this yii\web\View */
use common\models\User;
?>
<?php 
$connection = Yii::$app->getDb();
$merchant_id = \Yii::$app->user->identity->id;
$productmodel = $connection->createCommand("SELECT * FROM `jet_config` WHERE merchant_id='".$merchant_id."'");
$result = $productmodel->queryAll();
$skuResult='';$priceResult='';$inventoryResult='';$orderResult='';$shippedResult='';$ordercancelResult='';$orderreturnResult='';
foreach($result as $value) {
	if($value['data']=='sku_data')
		$skuResult=$value['value'];
	elseif($value['data']=='price_data')
		$priceResult=$value['value'];
	elseif($value['data']=='inventory_data')
		$inventoryResult=$value['value'];
	elseif($value['data']=='order_data')
		$orderResult=$value['value'];
	elseif($value['data']=='shipped_data')
		$shippedResult=$value['value'];
	elseif($value['data']=='ordercancel_data')
		$ordercancelResult=$value['value'];
	elseif($value['data']=='return_data')
		$orderreturnResult=$value['value'];
}
?>
<div class="tesst_api">
	<h1>Activate API</h1>
	<p class="notice">Please complete the following steps to activate your account. All schema information are here.</p>
	<div class="product_api api_container">
		<div class="entry-edit-head">
			<h4 class="fieldset-legend">Authorize your API calls</h4>
		</div>
		<div id="product_report_base_fieldset" class="fieldset Activate_api">
			<h5>Activate Product Api</h5>
			<div class="hor-scroll">
				<table class="form-list" cellspacing="0">
					<tbody>
						<tr>
							<td class="value_label">
								<label for="sales_report_report_type">Send Merchant SKU</label>
							</td>
							<td class="value <?php if($skuResult)echo " enable_check";?>">
								<?php if(!$skuResult){?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/productapi?param=sku']) ?>">Send</a>
								<?php }else{?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/productapi?param=sku']) ?>">Resend</a>
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="value_label">
								<label for="sales_report_report_type">Send Price</label>
							</td>
							<td class="value <?php if($priceResult)echo " enable_check";?>">
								<?php if(!$priceResult){?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/productapi?param=price']) ?>">Send</a>
								<?php }else{?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/productapi?param=price']) ?>">Resend</a>
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="value_label">
								<label for="sales_report_report_type">Send Inventory</label>
							</td>
							<td class="value <?php if($inventoryResult)echo " enable_check";?>">
							<?php if(!$inventoryResult){?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/productapi?param=inventory']) ?>">Send</a>
							<?php }else{?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/productapi?param=inventory']) ?>">Resend</a>
							<?php }?>	
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>	
	</div>
	<div class="order_api api_container">
		<div class="entry-edit-head">
			<h4 class="fieldset-legend">Activate Order Api</h4>
		</div>
		<div id="sales_report_base_fieldset" class="fieldset Activate_api">
			<h4>Firstly create order from here..
				<a href="https://partner.jet.com/testapi">
					<b>order generator</b>
				</a>
			</h4>
			<div class="hor-scroll">
				<table class="form-list" cellspacing="0">
					<tbody>
						<tr>
							<td class="value_label">
								<label for="sales_report_report_type">Acknowledge order</label>
							</td>
							<td class="value <?php if($orderResult)echo " enable_check";?>">
								<?php if(!$orderResult){?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/orderapi?param=ack']) ?>">Send</a>
								<?php }else{?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/orderapi?param=ack']) ?>">Resend</a>
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="value_label">
								<label for="sales_report_report_type">Ship order</label>
							</td>
							<td class="value <?php if($shippedResult)echo " enable_check";?>">
								<?php if(!$shippedResult){?>
									<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/orderapi?param=ship']) ?>">	Send</a>
								<?php }else{?>
									<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/orderapi?param=ack']) ?>">Resend</a>
								<?php }?>
							</td>
						</tr>
						<tr>
							<td class="" colspan="2">
								<label for="sales_report_report_type">
									<h4>
										To Activate cancel order Firstly create order with cancel quantity from here..
										<a href="https://partner.jet.com/testapi" target="_blank">
											<b>order generator</b>
										</a>
									</h4>
								</label>
							</td>
						</tr>
						<tr>
							<td class="value_label">
								<label for="sales_report_report_type">Cancel Order</label>
							</td>
							<td class="value <?php if($ordercancelResult)echo " enable_check";?>">
								<?php if(!$ordercancelResult){?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/orderapi?param=cancel']) ?>">Send</a>
								<?php }else{?>
									<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/orderapi?param=ack']) ?>">Resend</a>
								<?php }?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>	
	<div class="return_api api_container">	
		<div class="entry-edit-head">
			<h4 class="fieldset-legend">Activate Return</h4>
		</div>
	<div id="return_report_base_fieldset" class="fieldset Activate_api">
		<h4>
			Firstly create return from here..
			<a href="https://partner.jet.com/testapi" target="_blank">
				<b>return generator</b>
			</a>
		</h4>
		<div class="hor-scroll">
			<table class="form-list" cellspacing="0">
				<tbody>
					<tr>
						<td class="value_label">
							<label for="sales_report_report_type">Complete Return</label>
						</td>
						<td class="value <?php if($orderreturnResult)echo " enable_check";?>">
							
							<?php if(!$orderreturnResult){?>
							<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/returnapi']) ?>">Send</a>
							<?php }else{?>
								<a href="<?php echo \yii\helpers\Url::toRoute(['jetapi/returnapi']) ?>">Resend</a>
							<?php }?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>