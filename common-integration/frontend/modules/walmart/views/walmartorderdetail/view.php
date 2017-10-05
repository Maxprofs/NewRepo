<div class="container">
    <!-- Modal -->
    <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content" id='edit-content'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h2 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Order Current
                    Status on Walmart</h2>
                </div>
                <?php if(!isset($model['order'])){ ?>
                    <div class="modal-body">
                        <p> Order Information Not Found On Walmart.com</p>
                    </div>
                <?php }else{?>
                <div class="modal-body">
                    <div class="jet-product-form">
                        <div class="form-group">
                            <div class="field-jetproduct">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="order-information">
                                            <h4>Order information</h4>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <ul class="lable-list">
                                                        <li class="value-list">
                                                            <div class="order_heading">Purchase Order ID</div>
                                                            <div class="order_details"><?= $model['purchaseOrderId'] ?></div>
                                                        </li>
                                                        <li class="value-list">
                                                            <div class="order_heading">Customer Order ID</div>
                                                            <div class="order_details"><?= $model['customerOrderId'] ?></div>
                                                        </li>
                                                        <li class="value-list">
                                                            <div class="order_heading">Customer Email ID</div>
                                                            <div class="order_details"><?= $model['customerEmailId'] ?></div>
                                                        </li>
                                                        <li class="value-list">
                                                            <div class="order_heading">Order Date</div>
                                                            <div class="order_details"><?= date('Y-m-d H:i:s', $model['orderDate'] / 1000) ?></div>
                                                        </li>
                                                        <?php
                                                        if (isset($model['shippingInfo'])) { ?>

                                                            <li class="value-list">
                                                                <div class="order_heading">Order Estimated Ship Date</div>
                                                                <div class="order_details"><?= date('Y-m-d H:i:s', $model['shippingInfo']['estimatedShipDate'] / 1000) ?></div>
                                                            </li>
                                                            <li class="value-list">
                                                                <div class="order_heading">Order Estimated Delivery Date</div>
                                                                <div class="order_details"><?= date('Y-m-d H:i:s', $model['shippingInfo']['estimatedDeliveryDate'] / 1000) ?></div>
                                                            </li>

                                                        <?php }
                                                        ?>
                                                    </ul>
                                                </div>
                                                <!--<div class="col-md-6">
                                                    <ul class="value-list">
                                                        <li class="form-group">
                                                            <span><? /*= $model['purchaseOrderId'] */ ?></span></li>
                                                        <li class="form-group">
                                                            <span><? /*= $model['customerOrderId'] */ ?></span></li>
                                                        <li class="form-group">
                                                            <span><? /*= $model['customerEmailId'] */ ?></span></li>
                                                        <li class="form-group">
                                                            <span><? /*= date('Y-m-d H:i:s', $model['orderDate'] / 1000) */ ?></span>
                                                        </li>

                                                        <?php
                                                /*                                                        if (isset($model['shippingInfo'])) { */ ?>

                                                            <li class="form-group">
                                                                <span><? /*= date('Y-m-d H:i:s', $model['shippingInfo']['estimatedShipDate'] / 1000) */ ?></span>
                                                            </li>
                                                            <li class="form-group">
                                                                <span><? /*= date('Y-m-d H:i:s', $model['shippingInfo']['estimatedDeliveryDate'] / 1000) */ ?></span>
                                                            </li>

                                                        <?php /*}
                                                        */ ?>
                                                    </ul>
                                                </div>-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="address-information">
                                            <h4>Address information</h4>
                                            <div class="row">
                                                <!--<div class="col-md-12">
                                                    <ul class="lable-list">
                                                        <li class="value_label"><span>Customer Name</span></li>
                                                        <li class="value_label"><span>Contact Number</span></li>
                                                        <li class="value_label"><span>Address</span></li>
                                                        <li class="value_label"><span>City</span></li>
                                                        <li class="value_label"><span>State</span></li>
                                                        <li class="value_label"><span>Zip Code</span></li>
                                                        <li class="value_label"><span>Country</span></li>
                                                        <li class="value_label"><span>Address Type</span></li>
                                                    </ul>
                                                </div>-->
                                                <?
                                                if (isset($model['shippingInfo']['postalAddress'])) {
                                                    ?>
                                                    <div class="col-md-12">
                                                        <ul class="value-list">
                                                            <li class="form-group">
                                                                <div class="address_heading">Customer Name</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['name'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">Contact Number</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['phone'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">Address</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['address1'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">City</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['city'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">State</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['state'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">Zip Code</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['postalCode'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">Country</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['country'] ?></div>
                                                            </li>

                                                            <li class="form-group">
                                                                <div class="address_heading">Address Type</div>
                                                                <div class="address_details"><?= $model['shippingInfo']['postalAddress']['addressType'] ?></div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Purchase Order ID</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $model['purchaseOrderId'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Customer Email ID</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $model['customerEmailId'] ?></span>
                                        </td>
                                    </tr>
                                    </tbody>
                            </table>  -->
                                <?
                                if (isset($model['orderLines']['orderLine'])) {
                                    ?>
                                    <div class="table-responsive">
                                        <h3>Items Information</h3>
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Product Title</th>
                                                <th>Product SKU</th>
                                                <th>Amount</th>
                                                <th>Shipping</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                                <th>Shipping Carrier</th>
                                                <th>Tracking Number</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?
                                            foreach ($model['orderLines']['orderLine'] as $key => $value) {
                                                ?>

                                                <tr>
                                                    <td>
                                                        <span><?= $value['item']['productName'] ?></span>
                                                    </td>
                                                    <td>
                                                        <span><?= $value['item']['sku'] ?></span>
                                                    </td>

                                                    <?

                                                    if (isset($value['charges']['charge'][0]) && isset($value['charges']['charge'][0]['chargeType']) == 'PRODUCT') { ?>
                                                        <td>
                                                            <?php echo $value['charges']['charge'][0]['chargeAmount']['amount'] . "   " . $value['charges']['charge'][0]['chargeAmount']['currency']; ?>
                                                        </td>
                                                    <?php }

                                                    if (isset($value['charges']['charge'][1]) && isset($value['charges']['charge'][1]['chargeType']) == 'SHIPPING') { ?>
                                                        <td>
                                                            <?php echo $value['charges']['charge'][1]['chargeAmount']['amount'] . "   " . $value['charges']['charge'][1]['chargeAmount']['currency']; ?>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td></td>
                                                    <?php }

                                                    if (isset($value['orderLineQuantity'])) {
                                                        ?>

                                                        <td>
                                                            <span><?= $value['orderLineQuantity']['amount'] ?></span>
                                                        </td>

                                                        <!--<td >
                                                                        <span><?/*= $value['orderLineQuantity']['amount']*/
                                                        ?></span>
                                                                    </td>-->
                                                        <?php
                                                    }

                                                    if (isset($value['orderLineStatuses']['orderLineStatus'])) {
                                                        foreach ($value['orderLineStatuses']['orderLineStatus'] as $key2 => $val2) {
                                                            if (isset($val2['status'])) {
                                                                ?>

                                                                <td>
                                                                    <span><?= $val2['status'] ?></span>
                                                                </td>
                                                                <?
                                                            }
                                                            if (isset($val2['trackingInfo']['carrierName']['carrier'])) {
                                                                ?>

                                                                <td>
                                                                    <span><?= $val2['trackingInfo']['carrierName']['carrier'] ?></span>
                                                                </td>
                                                                <?
                                                            } elseif (isset($val2['trackingInfo']['carrierName']['otherCarrier'])) {
                                                                ?>

                                                                <td>
                                                                    <span><?= $val2['trackingInfo']['carrierName']['otherCarrier'] ?></span>
                                                                </td>
                                                                <?
                                                            }else{
                                                                ?>

                                                                <td>
                                                                </td>
                                                                <?
                                                            }
                                                            if (isset($val2['trackingInfo']['trackingNumber'])) {
                                                                ?>

                                                                <td>
                                                                    <span><a href="<?= $val2['trackingInfo']['trackingURL'] ?>" target="_blank"><?= $val2['trackingInfo']['trackingNumber'] ?></a></span>
                                                                </td>
                                                                <?
                                                            }
                                                            /*if (isset($val2['trackingInfo']['trackingURL']))
                                                            {
                                                                ?>

                                                                    <td >
                                                                        <span><?= $val2['trackingInfo']['trackingURL']?></span>
                                                                    </td>
                                                                <?
                                                            }*/
                                                            ?>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </tr>

                                            <? } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?
                                }
                                ?>
                                <!-- <?
                                if (isset($model['shippingInfo'])) {
                                    ?>
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <th>Contact Number</th>
                                                    <th>Address</th>
                                                    <th>City</th>
                                                    <th>State</th>
                                                    <th>Zip Code</th>
                                                    <th>Country</th>
                                                    <th>Address Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            <?
                                    if (isset($model['shippingInfo']['postalAddress'])) {
                                        ?>
                                                <tr>                                                    
                                                        
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['name'] ?></span>
                                                                </td>
                                                                
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['phone'] ?></span>
                                                                </td>
                                                            
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['address1'] ?></span>
                                                                </td>
                                                            
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['city'] ?></span>
                                                                </td>
                                                            
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['state'] ?></span>
                                                                </td>
                                                            
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['postalCode'] ?></span>
                                                                </td>
                                                            
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['country'] ?></span>
                                                                </td>
                                                           
                                                                <td>
                                                                    <span><?= $model['shippingInfo']['postalAddress']['addressType'] ?></span>
                                                                </td>
                                                            </tr>
                                                    
                                                <?php
                                    }
                                    ?>
                                        </tr>
                                            </tbody>
                                        </table>
                                        
                                        <?
                                }
                                ?> -->
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>