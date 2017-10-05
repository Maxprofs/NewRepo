<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 13/1/17
 * Time: 11:14 AM
 */
use yii\helpers\Html;
$this->title = 'View Newegg Order Detail';
$this->params['breadcrumbs'][] = $this->title;
//$backurl=\yii\helpers\Url::toRoute(['walmartproductfeed/index']);
/*print_r($model);die;*/
/*$data = json_decode($model['order_data'], true);*/
?>
<?= Html::beginForm(['neweggorderdetail/index'], 'post');
?>
<?php
?>
<div class="content-section">
    <div class="view-order form new-section">
        <div class="jet-pages-heading">
            <div class=" title-need-help">
                <h1 class="Jet_Products_style">Order Details on
                    Newegg</h1>
                </div>
                <div class="product-upload-menu">
                    <button class="btn btn-primary " type="submit" title="Back"><span>Back</span>
                    </button>
                </div>
                <div class="clear"></div>
            </div>
            <div class="ced-entry-heading-wrapper">
                <div class="fieldset walmart-configuration-index">
                    <div class="ced-form-grid-walmart-sec one">
                    <h4>Customer Information</h4>
                        <table class="table table-striped table-bordered">
                            <tbody>
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
                            </tbody>
                        </table>
                    </div>
                    <div class="ced-form-grid-walmart-sec two">
                    <h4>Order Information</h4>
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
                    </div>
                    <?php if (!empty($data['PackageInfoList'][0])) { ?>
                    <div class="ced-form-grid-walmart-sec three">
                        <!-- <div class="jet-pages-heading">
                            <h1 class="Jet_Products_style">Package Info List</h1>
                            <div class="clear"></div>
                        </div> -->
                        <h4>Purchase Information</h4>
                        <div class="ced-entry-heading-wrapper">
                            <div class="fieldset walmart-configuration-index">
                                <div class="ced-form-grid-walmart">
                                    <table class="table table-striped table-bordered">
                                        <tbody>
                                            <tr>
                                                <td class="value_label" width="33%">
                                                    <span>Package Type</span>
                                                </td>
                                                <td class="value form-group " width="100%">
                                                    <span><?= $data['PackageInfoList'][0]['PackageType'] ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="value_label" width="33%">
                                                    <span>Ship Carrier</span>
                                                </td>
                                                <td class="value form-group " width="100%">
                                                    <span><?= $data['PackageInfoList'][0]['ShipCarrier'] ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="value_label" width="33%">
                                                    <span>Ship Service</span>
                                                </td>
                                                <td class="value form-group " width="100%">
                                                    <span><?= $data['PackageInfoList'][0]['ShipService'] ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="value_label" width="33%">
                                                    <span>Tracking Number</span>
                                                </td>
                                                <td class="value form-group " width="100%">
                                                    <span><?= $data['PackageInfoList'][0]['TrackingNumber'] ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="value_label" width="33%">
                                                    <span>Ship Date</span>
                                                </td>
                                                <td class="value form-group " width="100%">
                                                    <span><?= $data['PackageInfoList'][0]['ShipDate'] ?></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ced-entry-heading-wrapper table-responsive">
                <div class="entry-edit-head">
                    <h4 class="fieldset-legend">Item Info List</h4>
                </div>
                <div class="Attrubute_html" id="jet_Attrubute_html">
                    <table class="table feed_table table-striped table-bordered" border="2">
                        <thead>
                            <tr>
                                <th>
                                    SellerPartNo
                                </th>
                                <th>
                                    ItemNumber
                                </th>
                                <th>
                                    MfrPartNo
                                </th>
                                <th>
                                    UPCCode
                                </th>
                                <th>
                                    Description
                                </th>
                                <th>
                                    OrderedQty
                                </th>
                                <th>
                                    UnitPrice
                                </th>
                                <th>
                                    ExtendUnitPrice
                                </th>
                                <th>
                                    ExtendShippingCharge
                                </th>
                                <th>
                                    Status
                                </th>
                                <th>
                                    StatusDescription
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($data['ItemInfoList'])) {
                                foreach ($data['ItemInfoList'] as $value) {
                                    ?>
                                    <tr>
                                        <td>
                                            <?= $value['SellerPartNumber'] ?>
                                        </td>
                                        <td>
                                            <?= $value['NeweggItemNumber'] ?>
                                        </td>
                                        <td>
                                            <?= $value['MfrPartNumber'] ?>
                                        </td>
                                        <td>
                                            <?= $value['UPCCode'] ?>
                                        </td>
                                        <td>
                                            <?= $value['Description'] ?>
                                        </td>
                                        <td>
                                            <?= $value['OrderedQty'] ?>
                                        </td>
                                        <td>
                                            <?= $value['UnitPrice'] ?>
                                        </td>
                                        <td>
                                            <?= $value['ExtendUnitPrice'] ?>
                                        </td>
                                        <td>
                                            <?= $value['ExtendShippingCharge'] ?>
                                        </td>
                                        <td>
                                            <?= $value['Status'] ?>
                                        </td>
                                        <td>
                                            <?= $value['StatusDescription'] ?>
                                        </td>
                                    </tr>
                                    <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ced-entry-heading-wrapper">
                    <div class="entry-edit-head">
                        <h4 class="fieldset-legend">Package Info List -> Item Info List</h4>
                    </div>
                    <div class="Attrubute_html" id="jet_Attrubute_html">
                        <table class="table feed_table table-striped table-bordered" border="2">
                            <thead>
                                <tr>
                                    <th>
                                        Seller Part Number
                                    </th>
                                    <th>
                                        Mfr Part Number
                                    </th>
                                    <th>
                                        Shipped Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($data['ItemInfoList'])) {
                                    foreach ($data['ItemInfoList'] as $value) {
                                        ?>
                                        <tr>
                                            <td>
                                                <?= $value['SellerPartNumber'] ?>
                                            </td>
                                            <td>
                                                <?= $value['MfrPartNumber'] ?>
                                            </td>
                                            <td>
                                                <?= $value['ShippedQty'] ?>
                                            </td>
                                        </tr>
                                        <?php }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php }
                    ?>
                </div>
            </div>
            <style>
                td {
                    text-align: center;
                }
                .feed_error {
                    background-color: #ffb2b2 !important;
                }
                .feed_success {
                    background-color: #b2FBb2 !important;
                }
                .feed_table {
                    margin-bottom: 0px;
                }
                #feed_error_des {
                    color: #333;
                    text-align: left;
                }
            </style>