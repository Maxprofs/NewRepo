<?php
    use yii\helpers\Html;
?>
<div class="container">
      <!-- Modal -->
      <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
          <!-- Modal content-->
          <div class="modal-content" id='edit-content'>
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title" style="text-align: center;font-family: "Comic Sans MS";">Order Current Status on Walmart</h4>
            </div>
            <div class="modal-body">
                <div class="jet-product-form">
                    <div class="form-group">
                        <div class="field-jetproduct">
                            <table class="table table-striped table-bordered">                              
                                <tbody>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Purchase Order ID</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $model['purchaseOrderId']?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Customer Email ID</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $model['customerEmailId']?></span>
                                        </td>
                                    </tr>
                                    <?
                                    if (isset($model['orderLines']['orderLine'])) 
                                    {
                                        foreach ($model['orderLines']['orderLine'] as $key => $value) 
                                        {
                                            ?>
                                            <tr>
                                                <td style="background-color:#BBBBBB;text-align:center;font-weight:bold" colspan="2">
                                                    Order Item Details
                                                </td>                                               
                                                <tr>                                                    
                                                    <td colspan="2" class="order-table-width">
                                                        <table class="table table-striped table-bordered">
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Product Title</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $value['item']['productName']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Product SKU</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $value['item']['sku']?></span>
                                                                </td>
                                                            </tr>
                                                            <?
                                                            if (isset($value['charges']['charge'])) 
                                                            {   
                                                                foreach ($value['charges']['charge'] as $key1 => $val1) 
                                                                {
                                                                    ?>
                                                                        <tr>
                                                                            <td class="value_label" width="33%">
                                                                                <span>Charge Amount</span>
                                                                            </td>
                                                                            <td class="value form-group " width="100%">
                                                                                <span><?= $val1['chargeAmount']['amount']."   ".$val1['chargeAmount']['currency']?></span>
                                                                            </td>
                                                                        </tr>
                                                                    <?                                          
                                                                }                                           
                                                                ?>                                                                                                                          
                                                                <?php
                                                            }
                                                            if (isset($value['orderLineQuantity'])) 
                                                            {                                                                   
                                                                ?>
                                                                    <tr>
                                                                        <td class="value_label" width="33%">
                                                                            <span>Unit Of Measurement</span>
                                                                        </td>
                                                                        <td class="value form-group " width="100%">
                                                                            <span><?= $value['orderLineQuantity']['unitOfMeasurement']?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="value_label" width="33%">
                                                                            <span>Quantity</span>
                                                                        </td>
                                                                        <td class="value form-group " width="100%">
                                                                            <span><?= $value['orderLineQuantity']['amount']?></span>
                                                                        </td>
                                                                    </tr>                                                                               
                                                                <?php
                                                            }
                                                            if (isset($value['orderLineStatuses']['orderLineStatus'])) 
                                                            {   
                                                                foreach ($value['orderLineStatuses']['orderLineStatus'] as $key2 => $val2) 
                                                                {
                                                                    if (isset($val2['status'])) 
                                                                    {
                                                                        ?>
                                                                            <tr>
                                                                                <td class="value_label" width="33%">
                                                                                    <span>Order Status</span>
                                                                                </td>
                                                                                <td class="value form-group " width="100%">
                                                                                    <span><?= $val2['status']?></span>
                                                                                </td>
                                                                            </tr>
                                                                        <?  
                                                                    }
                                                                    if (isset($val2['trackingInfo']['carrierName']['carrier'])) 
                                                                    {
                                                                        ?>
                                                                            <tr>
                                                                                <td class="value_label" width="33%">
                                                                                    <span>Shipping Carrier</span>
                                                                                </td>
                                                                                <td class="value form-group " width="100%">
                                                                                    <span><?= $val2['trackingInfo']['carrierName']['carrier']?></span>
                                                                                </td>
                                                                            </tr>
                                                                        <?  
                                                                    }elseif (isset($val2['trackingInfo']['carrierName']['otherCarrier'])) 
                                                                    {
                                                                        ?>
                                                                            <tr>
                                                                                <td class="value_label" width="33%">
                                                                                    <span>Shipping Carrier</span>
                                                                                </td>
                                                                                <td class="value form-group " width="100%">
                                                                                    <span><?= $val2['trackingInfo']['carrierName']['otherCarrier']?></span>
                                                                                </td>
                                                                            </tr>
                                                                        <?  
                                                                    }
                                                                    if (isset($val2['trackingInfo']['trackingNumber'])) 
                                                                    {
                                                                        ?>
                                                                            <tr>
                                                                                <td class="value_label" width="33%">
                                                                                    <span>Tracking Number</span>
                                                                                </td>
                                                                                <td class="value form-group " width="100%">
                                                                                    <span><?= $val2['trackingInfo']['trackingNumber']?></span>
                                                                                </td>
                                                                            </tr>
                                                                        <?  
                                                                    }
                                                                    if (isset($val2['trackingInfo']['trackingURL'])) 
                                                                    {
                                                                        ?>
                                                                            <tr>
                                                                                <td class="value_label" width="33%">
                                                                                    <span>Tracking URL</span>
                                                                                </td>
                                                                                <td class="value form-group " width="100%">
                                                                                    <span><?= $val2['trackingInfo']['trackingURL']?></span>
                                                                                </td>
                                                                            </tr>
                                                                        <?  
                                                                    }
                                                                    ?>                                              
                                                                <?php                                                               
                                                                }                                                               
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                </tr>                                                                                                       
                                            </tr>
                                        <?
                                        }                                                                                   
                                    }
                                    if (isset($model['shippingInfo'])) 
                                    {
                                        ?>
                                        <tr>
                                            <td style="background-color:#BBBBBB;text-align:center;font-weight:bold" colspan="2">
                                                Order Shipping Information
                                            </td>
                                            <?
                                            if (isset($model['shippingInfo']['postalAddress'])) 
                                            {                                               
                                                ?>  
                                                <tr>                                                    
                                                    <td colspan="2" class="order-table-width">
                                                        <table class="table table-striped table-bordered">
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Customer Name</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['name']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Contact Number</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['phone']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Address</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['address1']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>City</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['city']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>State</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['state']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Zip Code</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['postalCode']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Country</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['country']?></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="value_label" width="33%">
                                                                    <span>Address Type</span>
                                                                </td>
                                                                <td class="value form-group " width="100%">
                                                                    <span><?= $model['shippingInfo']['postalAddress']['addressType']?></span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>                                                   
                                                <?php
                                            }
                                            ?>
                                        </tr>
                                        <?                                          
                                    }                                   
                                    ?>                                  
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