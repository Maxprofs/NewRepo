<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/1/17
 * Time: 5:19 PM
 */

use yii\helpers\Html;

$data = json_decode($model['order_data'], true);
?>
<div class="container">
    <!-- Modal -->
    <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content" id='edit-content'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Order Details on
                    Newegg</h4>
                </div>
                <div class="modal-body">
                    <div class="jet-product-form">
                        <div class="form-group">
                            <div class="field-jetproduct">
                                <table class="table table-striped table-bordered">
                                    <tbody>

                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Order Number</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['OrderNumber'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Invoice Number</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['InvoiceNumber'] ?></span>
                                        </td>
                                    </tr>
                                    <!--<tr>
                                        <td class="value_label" width="33%">
                                            <span>Order Downloaded</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><? /*= $data['OrderDownloaded']*/ ?></span>
                                        </td>
                                    </tr>-->
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Order Date</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['OrderDate'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Order Status Description</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['OrderStatusDescription'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Customer Name</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['CustomerName'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Customer Phone Number</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['CustomerPhoneNumber'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Customer Email Address</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['CustomerEmailAddress'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Customer Address</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['ShipToAddress1'], $data['ShipToAddress2'], $data['ShipToCityName'], $data['ShipToStateCode'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Zipcode</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['ShipToZipCode'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Ship To Country</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['ShipToCountryCode'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Ship Service</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['ShipService'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Order Item Amount</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['OrderItemAmount'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Refund Amount</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['RefundAmount'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="value_label" width="33%">
                                            <span>Order Qty</span>
                                        </td>
                                        <td class="value form-group " width="100%">
                                            <span><?= $data['OrderQty'] ?></span>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                                <div class="modal-header">
                                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Item Info List</h4>
                                </div>
                                <div class="field-jetproduct">
                                    <table class="table table-striped table-bordered" border="2">
                                        <tbody>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>SellerPartNumber</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['SellerPartNumber'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>NeweggItemNumber</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['NeweggItemNumber'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>MfrPartNumber</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['MfrPartNumber'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>UPCCode</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['UPCCode'] ?></span>
                                            </td>
                                        </tr>
                                        <!--<tr>
                                            <td class="value_label" width="33%">
                                                <span>UPCCode</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?/*= $data['ShipToZipCode'] */?></span>
                                            </td>
                                        </tr>-->
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>Description</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['Description'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>OrderedQty</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['OrderedQty'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>ShippedQty</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['ShippedQty'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>UnitPrice</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['UnitPrice'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>ExtendUnitPrice</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?=  $data['ItemInfoList'][0]['ExtendUnitPrice'] ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>ExtendShippingCharge</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['ExtendShippingCharge'] ?></span>
                                            </td>
                                        </tr>
                                        <!--<tr>
                                            <td class="value_label" width="33%">
                                                <span>ExtendUnitPrice</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?/*= $data['ItemInfoList'][0]['ExtendUnitPrice'] */?></span>
                                            </td>
                                        </tr>-->
                                        <!--<tr>
                                            <td class="value_label" width="33%">
                                                <span>Status</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?/*= $data['ShipToZipCode'] */?></span>
                                            </td>
                                        </tr>-->
                                        <tr>
                                            <td class="value_label" width="33%">
                                                <span>StatusDescription</span>
                                            </td>
                                            <td class="value form-group " width="100%">
                                                <span><?= $data['ItemInfoList'][0]['StatusDescription'] ?></span>
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