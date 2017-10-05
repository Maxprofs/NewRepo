<?php 
use yii\helpers\Html;

?>
<script>
 function settlement(node)
 {
    		var label=j$(node).text();
    		//alert(label);
            j$.ajax({ 
                url: '<?php echo Yii::$app->homeUrl; ?>jetsettlement/view',
            	data: {label:label},
            	method: "GET",
            	error: function(e) {
                	console.log(e);
             	 },
             	dataType: 'json',
             	success: function(response) {
                  //  j$('#settle1').append(data); 

						//alert(response['settlement_report_id']);
             		j$('#settlement_report_id').html(response['settlement_report_id']);
            		j$('#settlement_state').html(response['settlement_state']);
            		j$('#currency').html(response['currency']);
            		j$('#unavailable_balance').html(response['unavailable_balance']);
            		
            		j$('#return_balance').html(response['return_balance']);
            		j$('#order_balance').html(response['order_balance']);
            		j$('#jet_adjustment').html(response['jet_adjustment']);
            		j$('#settlement_value').html(response['settlement_value']);
            		
            		j$('#settlement_period_start').html(response['settlement_period_start']);
            		j$('#settlement_period_end').html(response['settlement_period_end']);
            		
            		j$('#order_balance_details #merchant_price').html(response['order_balance_details']['merchant_price']);
            		j$('#order_balance_details #jet_variable_commission').html(response['order_balance_details']['jet_variable_commission']);
            		j$('#order_balance_details #fixed_commission').html(response['order_balance_details']['fixed_commission']);
            		j$('#order_balance_details #tax').html(response['order_balance_details']['tax']);
            		j$('#order_balance_details #shipping_revenue').html(response['order_balance_details']['shipping_revenue']);
            		j$('#order_balance_details #shipping_tax').html(response['order_balance_details']['shipping_tax']);
            		j$('#order_balance_details #shipping_charge').html(response['order_balance_details']['shipping_charge']);
            		j$('#order_balance_details #fulfillment_fee').html(response['order_balance_details']['fulfillment_fee']);
            		j$('#order_balance_details #product_cost').html(response['order_balance_details']['product_cost']);
            		j$('#order_balance_details #order_balance').html(response['order_balance']); 
            		 	
            		j$('#return_balance_details #merchant_price').html(response['return_balance_details']['merchant_price']);
            		j$('#return_balance_details #jet_variable_commission').html(response['return_balance_details']['jet_variable_commission']);
            		j$('#return_balance_details #fixed_commission').html(response['return_balance_details']['fixed_commission']);
            		j$('#return_balance_details #tax').html(response['return_balance_details']['tax']);
            		j$('#return_balance_details #shipping_tax').html(response['return_balance_details']['shipping_tax']);
            		j$('#return_balance_details #merchant_return_charge').html(response['return_balance_details']['merchant_return_charge']);
            		j$('#return_balance_details #return_processing_fee').html(response['return_balance_details']['return_processing_fee']);
            		j$('#return_balance_details #product_cost').html(response['return_balance_details']['product_cost']);
            		j$('#return_balance_details #return_balance').html(response['return_balance']); 
                } 
            });     
}
</script>
<?php 
$this->title = 'Jet Settlement';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order_settlement">
	
	<form name="form-settlement" action="<?php echo \yii\helpers\Url::toRoute(['jetsettlement/index']);?>" method="post">
		<div class="jet-pages-heading">
			<h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
			<input type="submit" value="Send" class="btn btn-primary"/>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>"/>
		<div class="fieldset enable_api">
			<div class="entry-edit-head">
				<h4 class="fieldset-legend">Jet Settlement</h4>
			</div>
			<table class="table table-striped table-bordered">
				<tr>
					<td>
						<span>Enter Days</span>
					</td>
					<td><input type="text" value="" name="settlement" class="form-control" maxlength=6></td>
					<td><span class="text-validator">Enter The No. of days for that you want settlement Reports.</span></td>
				</tr>
			</table>
		</div>	
	</form>
</div>
<?php 
if(isset($result))
{?>
	<div class="enable_api">
	<?php if(empty($result['settlement_report_urls'])){?>
		<span class="text-validator">No Response from Jet Api</span>
		
	<?php }else{?>
			<?php foreach($result['settlement_report_urls'] as $data)
			{?>
	
				<a  data-toggle="modal" data-target="#myModal" href="javascript:void(0);" name="report" id="addclick" onclick="settlement(this)" value="<?php echo $data;?>"><?php echo $data;?></a> <br>
		
			<?php
			}?>
	<?php }?>
	
	</div>
	
<?php 
}?>

 <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Settlement Report</h4>
        </div>
        <div class="modal-body">
		<div id="firstdiv" >
	        Settlement Report Id  <p id="settlement_report_id">No RESPONSE From Jet</p>
			Settlement State  <p id="settlement_state">No RESPONSE From Jet</p>
			Currency  <p id="currency">No RESPONSE From Jet</p>
			unavailable_balance  <p id="unavailable_balance">No RESPONSE From Jet</p>
		</div>
		<div id="seconddiv"  >
				return_balance  <p id="return_balance">No RESPONSE From Jet</p>
				order_balance  <p id="order_balance">No RESPONSE From Jet</p>
				jet_adjustment  <p id="jet_adjustment">No RESPONSE From Jet</p>
				settlement_value  <p id="settlement_value">No RESPONSE From Jet</p>
				settlement_period_start  <p id="settlement_period_start">No RESPONSE From Jet</p>
				settlement_period_end  <p id="settlement_period_end">No RESPONSE From Jet</p><div>
		 		<center>	 <img id="loader" src="<?= Yii::$app->request->baseUrl.'images/482.gif'; ?>" style="display:none;"/></center>
					<table class="table" id="order_balance_details">					  
						<tr><th class="text-right">Merchant Price:</th><td class="amount text-right nowrap" id="merchant_price">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Jet  Variable Commission:</th><td class="amount text-right nowrap" id="jet_variable_commission">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Fixed Commission:</th><td class="amount text-right nowrap" id="fixed_commission">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Tax:</th><td class="amount text-right nowrap" id="tax">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Shipping Revenue:</th><td class="amount text-right nowrap" id="shipping_revenue">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Shipping Tax:</th><td class="amount text-right nowrap" id="shipping_tax">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Shipping Charge:</th><td class="amount text-right nowrap" id="shipping_charge">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Fulfillment Fee:</th><td class="amount text-right nowrap" id="fulfillment_fee">No RESPONSE From Jet</td ></tr>
						<tr><th class="text-right">Product Cost:</th><td class="amount text-right nowrap" id="product_cost">No RESPONSE From Jet</td></tr>
						<tr><th >Order Balance:</th><td class="amount text-right nowrap" id="order_balance">No RESPONSE From Jet</td></tr>
					</table>
					<table class="table" id="return_balance_details">
						<tr><th class="text-right">Merchant Price:</th><td class="amount text-right nowrap" id="merchant_price">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Jet Variable Commission:</th><td class="amount text-right nowrap" id="jet_variable_commission">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Fixed Commission:</th><td class="amount text-right nowrap" id="fixed_commission">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Tax:</th><td class="amount text-right nowrap" id="tax">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Shipping Tax:</th><td class="amount text-right nowrap" id="shipping_tax">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Merchant Return Charge:</th><td class="amount text-right nowrap" id="merchant_return_charge">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Return Processing Fee:</th><td class="amount text-right nowrap" id="return_processing_fee">No RESPONSE From Jet</td></tr>
						<tr><th class="text-right">Product Cost:</th><td class="amount text-right nowrap" id="product_cost">No RESPONSE From Jet</td></tr>
						<tr><th>Return Balance:</th><td class="amount text-right nowrap" id="return_balance">No RESPONSE From Jet </td></tr>
					</table>
        </div>
        <div class="modal-footer"></div>
      </div>
      
    </div>
  </div>
</div>
</div>


