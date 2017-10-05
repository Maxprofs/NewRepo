<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\JetReturn */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Jet Returns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$requestData=array();
$responseData=array();$isRes=false;
$requestData=json_decode($model->return_data,true);
$responseData=json_decode($model->response_return_data,true);
if(is_array($responseData) && count($responseData)>0){
	$isRes=true;
}
?>
<div class="jet-return-view content-section">
	<div class="form new-section">
	<div class="jet-pages-heading">
		<div class="title-need-help">
		<h1 class="Jet_Products_style"><?= 'View Return:'.Html::encode($this->title) ?></h1>
		</div>
		<div class="product-upload-menu">
		 <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
		</div>
		 <div class="clear"></div>
	 </div>
    
	<div class="fieldset enable_api">
	<div class="table-responsive">
		<table class="table table-striped table-bordered return_info" cellspacing="0">
			<tbody>
				<tr>
					<td>Return Id</td>
					<td><?= $model->returnid;?></td>
				</tr>
				<tr>	
					<td>Merchant Order</td>
					<td>
						<?= $model->order_reference_id;?>
					</td>
				</tr>
				<tr>
					<td>
						Agree To Return
					</td>
					<td>
						<?php if($model->agreeto_return=="1")
								 echo "Yes";
							  else
							  	echo "No";
						?>
					</td>
				</tr>		
				<?php if($model->agreeto_return=="0")
				{?>
					<tr id="return_change">
						<td>Return Charge Feedback</td>
						<td>
							<?= $responseData['return_charge_feedback'];?>
						</td>
					</tr>
			<?php }?>
			</tbody>
		</table>
	</div>
	<div class="table-responsive">		
		<table class="table table-striped table-bordered return_info_items">
			<thead>
				<th>Merchant Sku</th>
				<th>Order Item Id</th>
				<th>Return Quantity</th>
				<th>Refund Feedback</th>
				<th>Refund Amount</th>
				<th>Shipping Cost</th>
				<th>Shipping Tax</th>
				<th>Tax</th>
			</thead>
			<tbody>
		<?php 
			foreach ($responseData['items'] as $key=>$value){?>
				<tr>
					<td>
						<?php 
						foreach ($requestData['return_merchant_SKUs'] as $val){
							if($val['order_item_id']==$value['order_item_id']){
								$sku="";
								$sku=$val['merchant_sku'];
								break;
							}
						}
						?>
						<?= $sku;?>
					</td>
					<td>
						<?= $value['order_item_id'];?>
					</td>
					<td>
						<?= $value['total_quantity_returned'];?>
					</td>
					<td>	
						<?= $value['return_refund_feedback'];?>
					</td>
					<td>
						<?= (float)$value['refund_amount']['principal'];?>
					</td>
					
					<td>
						<?= (float)$value['refund_amount']['shipping_cost'];?>
					</td>
					<td>
						<?= (float)$value['refund_amount']['shipping_tax'];?>
					</td>
					<td>
						<?= (float)$value['refund_amount']['tax'];?>
					</td>
				</tr>
		<?php 
			}
		?>
			</tbody>
		</table>
		</div>
	</div>
	</div>
</div>
