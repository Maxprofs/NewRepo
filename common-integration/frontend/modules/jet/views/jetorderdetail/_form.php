<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

$merchant_id = MERCHANT_ID;
$request_shipping_carrier = $shipData = $shipdatetime = "";
$flagCarr = $flag = false;
$orderData=json_decode($model->order_data,true);
if($orderData){
    $request_service_level=$orderData['order_detail']['request_service_level'];
    $dt = new \DateTime($orderData['order_detail']['request_ship_by']);
    $request_ship_by=$dt->format('Y/m/d H:i');
    $dt1 = new \DateTime($orderData['order_detail']['request_delivery_by']);
    $request_delivery_by=$dt1->format('Y/m/d H:i');
}

$shipData=json_decode($model->shipment_data,true);
if($shipData)
{
	if($shipData['fulfillments'][0]['tracking_number']!='' && $shipData['fulfillments'][0]['tracking_company']!='')
		$flag=true;
	$dt = new \DateTime($shipData['fulfillments'][0]['updated_at']);
	$shipdatetime=$dt->format('Y/m/d H:i');
}
?>

<div class="jet-order-detail-form content-section">
<div class="form new-section">
    <?php $form = ActiveForm::begin([
	    'id' => 'jet_order_form',
		'method'=>'post',
	    'options' => ['onsubmit' => 'return validateForm()','name'=>'form_order_name'],
	]) ?>
	
	<div class="form-group">
		<div class="jet-pages-heading">
        <div class="title-need-help">
	    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="product-upload-menu">
	     <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Order Shipment', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	     <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
         </div>
	    <div class="clear"></div>	
    </div>
       
    </div>
     
    <div class="shipping_data">
        <?= $form->field($model, 'merchant_order_id')->textInput(['maxlength' => true,'readonly' => true]) ?>
        <?= $form->field($model, 'shopify_order_id')->textInput(['maxlength' => true,'readonly' => true]) ?>
        <?= $form->field($model, 'shopify_order_name')->textInput(['maxlength' => true,'readonly' => true]) ?>
        <div class="form-group field-jetorderdetail-request_ship_by required has-success">
            <label class="control-label" for="jetorderdetail-request_ship_by">Request Ship By</label>
            <input id="jetorderdetail-request_ship_by" class="form-control" type="text" maxlength="100" readonly="" value="<?php echo $request_ship_by; ?>" name="JetOrderDetail[request_ship_by]">
        </div>
        <div class="form-group field-jetorderdetail-deliver_by required">
			<label class="control-label" for="jetorderdetail-deliver_by">Deliver By</label>
			<input id="jetorderdetail-deliver_by" class="form-control" type="text" maxlength="100" readonly="" value="<?php echo $request_delivery_by;?>" name="JetOrderDetail[deliver_by]">
			<div class="help-block"></div>
		</div>
        <div class="form-group field-jetorderdetail-request_shipping_carrier required has-success">
            <label class="control-label" for="jetorderdetail-request_shipping_carrier">Request Shipping Carrier</label>
            <?php 
            if(is_array($carriers) && count($carriers)>0){?>
            	<select id="jetorderdetail-request_shipping_carrier" class="form-control" name="JetOrderDetail[request_shipping_carrier]">
	            <?php 
					foreach($carriers as $val){
						?>
						<option value="<?php echo $val?>"><?php echo $val;?></option>
			  <?php }?>
		  		</select>
            <?php }
          	elseif($flag)
                {?>
            	   <input id="jetorderdetail-request_shipping_carrier" class="form-control" type="text" maxlength="100" value="<?= $shipData['fulfillments'][0]['tracking_company']?>" name="JetOrderDetail[request_shipping_carrier]">
            <?php }else{?>
                    <input id="jetorderdetail-request_shipping_carrier" class="form-control" type="text" maxlength="100" value="" name="JetOrderDetail[request_shipping_carrier]">
                <?php 
                }?>            
        </div>
        <div class="form-group field-jetorderdetail-tracking_number required has-success">
            <label class="control-label" required="required" for="jetorderdetail-tracking_number">Tracking Number</label>
            <input id="jetorderdetail-tracking_number" class="form-control" type="text" maxlength="100" value="<?php if($flag){echo $shipData['fulfillments'][0]['tracking_number'];}?>" name="JetOrderDetail[tracking_number]">
        </div>        
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Product sku</th>
                    <th>Quantity fulfillable</th>
                    <th>Send Return Address</th>
                    <th>RMA Number</th>
                    <th>Days to Return</th>
                </tr>
                <?php foreach ($orderData['order_items'] as $key => $value){?>
                    <tr>   
                    	<?php
						$qty=0;
						$qty=(int)$value['request_order_quantity'];
						?>
                        <td>
                            <input class="form-control" id="merchant_sku" readonly="" type="text" value="<?php echo $value['merchant_sku'];?>" name="JetOrderDetail[merchant_sku][]">
                        </td>
                        <td>
                            <?php
							if($qty>1)
                            {
                                $i=1;
                                ?>
                                   <select class="form-control" name="JetOrderDetail[response_shipment_sku_quantity][]">
                                <?php
                                while($i<=$qty)
								{?>
                                    <option value="<?php echo $i?>" <?php if($i==$qty)echo "selected=selected"?>><?php echo $i?></option>
                                <?php $i++;
                             	}?>
                                </select>
                            <?php 
                            }
                            else
                            {?>
                                <input class="form-control" type="text" class="" value="<?php echo $qty ?>" name="JetOrderDetail[response_shipment_sku_quantity][]">
                            <?php 
                            } ?>
                            <input type="hidden" class="" value="<?php echo $qty ?>" name="JetOrderDetail[response_shipment_sku_quantity_hidden][]">
                            <input type="hidden" class="" value="<?php echo $value['request_order_cancel_qty'] ?>" name="JetOrderDetail[response_shipment_cancel_quantity_hidden][<?php echo $value['merchant_sku']; ?>]">
                        </td>
                        <td>
                            <select id="send_return_address" class="form-control return_address" name="JetOrderDetail[send_return_address][]">
                                <option selected="selected" value="1">YES</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                        <td>
                            <input class="form-control" id="rma_number" type="text" value="" name="JetOrderDetail[RMA_number][]">
                        </td>
                        <td>
                            <input class="form-control" id="days_return" type="text" placeholder="0" value="" name="JetOrderDetail[days_to_return][]">
                        </td>                   
                    </tr>
                <?php }?>
            </thead>
        </table>
    </div>
    <?php ActiveForm::end(); ?>
    </div>
</div>
<script type="text/javascript">    
    function validateForm() 
    {
        var x = document.getElementById("jetorderdetail-tracking_number").value;		
        if (x == null || x == "" ) {
            alert("Tracking Number must be filled out");
            return false;
        }
    }
</script>