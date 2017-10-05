<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartPromoStatus;

$tax_code = Data::GetTaxCode($model, MERCHANT_ID);

/*$query = "SELECT `shop_url` from `walmart_shop_details` where `merchant_id`=" . MERCHANT_ID;
$shopUrl = Data::sqlRecords($query, 'one');
$shop_url = is_array($shopUrl) && isset($shopUrl['shop_url']) ? trim($shopUrl['shop_url']) : "";*/

/*$query2 = "SELECT * FROM `walmart_product` WHERE `product_id` = '".$model->product_id."' AND `merchant_id` =" . MERCHANT_ID ;
$walmartproduct = Data::sqlRecords($query2, 'one');*/

$shop_url = Yii::$app->user->identity->username;

$controllerName = 'product';

if (!$tax_code) {
    $tax_code = "";
}

if (!is_null($model->long_description))
    $model->jet_product->description = $model->long_description;

if (is_null($model->short_description) || $model->short_description == '')
    $model->short_description = substr($model->jet_product->description, 0, strpos($model->jet_product->description, '.'));
if ($model->short_description == '')
    $model->short_description = substr($model->jet_product->description, 0, 50);

if (is_null($model->self_description) || $model->self_description == '')
    $model->self_description = $model->jet_product->title;
?>
<div class="container">
    <!-- Modal -->
    <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content" id='edit-content'>
                <div class="modal-header">
                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS
                    ";"><?= $model->jet_product->title ?></h4>
                </div>
                <div class="modal-body">
                    <div class="jet-product-form">
                        <?php $form = ActiveForm::begin([
                            'id' => 'walmart_edit_form',
                            'action' => frontend\modules\walmart\components\Data::getUrl($controllerName . '/updateajax/?id=' . $model->product_id),
                            'method' => 'post',
                        ]); ?>
                        <div class="form-group">
                            <input type="hidden" name="WalmartProduct[product_id]" value="<?= $model->product_id ?>"
                                   id="productid"/>
                            <div class="field-jetproduct">
                                <div class="table-responsive grid-view">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <!-- <th>
                                            Short Description
                                            &nbsp;&nbsp;
                                            <button type="button" onClick="toggleEditor1(this);" class="toggle_editor">
                                                Show/Hide Editor
                                            </button>
                                        </th>
                                        <th>
                                            Shelf Description
                                            &nbsp;&nbsp;
                                            <button type="button" onClick="toggleEditor2(this);" class="toggle_editor">
                                                Show/Hide Editor
                                            </button>
                                        </th> -->
                                        <th>Title</th>
                                        <th>
                                            Description&nbsp;&nbsp;<a id="desc_edit_tag"
                                                                      onclick='editDescription(event)'>edit</a>
                                        </th>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <!-- <td>
                                            <?php
                                            //$var_string = $model->short_description;
                                            //$var_string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $var_string);
                                            ?>
                                                <textarea maxlength="500" id="shortDescriptionField"
                                                          name="WalmartProduct[short_description]" class="form-control"
                                                          id="jetproduct-short_description"><?php //echo $var_string; ?></textarea>
                                            </td>

                                            <td>
                                            <?php
                                            //$var_string = $model->self_description;
                                            //$var_string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $var_string);
                                            ?>
                                                <textarea maxlength="500" id="shelfDescriptionField"
                                                          name="WalmartProduct[self_description]" class="form-control"
                                                          id="jetproduct-self_description"><?php //echo $var_string; ?></textarea>
                                            </td> -->

                                            <td>
                                                <div class="public">
                                                    <input class="form-control walmart-title" type="text"
                                                           id="walmart_product_title"
                                                           name="WalmartProduct[product_title]"
                                                           value="<?php if ($model->product_title) {
                                                               echo htmlspecialchars($model->product_title);
                                                           } else {
                                                               echo htmlspecialchars($model->jet_product->title);
                                                           } ?>">
                                                </div>
                                            </td>

                                            <td>
                                                <?php
                                                $productDescription = $model->jet_product->description;
                                                $productDescription = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $productDescription);
                                                ?>
                                                <textarea name="WalmartProduct[long_description]" id="product_desc"
                                                          class="form-control"><?= $productDescription ?></textarea>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="table-responsive grid-view">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Brand</th>
                                            <th>Weight</th>
                                            <th>Product Type</th>
                                            <th>Product Tax Code</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <input type="hidden" maxlength="500"
                                                       value="<?= $model->jet_product->vendor; ?>"
                                                       name="WalmartProduct[vendor]"
                                                       class="form-control" id="jetproduct-vendor"/>
                                                <?= $model->jet_product->vendor ?>
                                            </td>
                                            <td>
                                                <?= (float)$model->jet_product->weight; ?>
                                            </td>
                                            <td>
                                                <?= $model->product_type; ?>
                                            </td>
                                            <td>
                                                <div class="public">
                                                    <input type="text" name="WalmartProduct[product_tax]"
                                                           value="<?= $tax_code; ?>"
                                                           class="form-control" id="inputSelectCode">
                                                </div>
                                                <span class="text-validator">
                                                        To get product tax code click <a target="_blank"
                                                                                         href="<?= yii\helpers\Url::toRoute(['walmarttaxcodes/index']); ?>">here
                                                    </span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- new fields start -->
                                <div class="table-responsive grid-view">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Sku Override</th>
                                            <th>Product Id Override</th>
                                            <th>Fulfillment Lag Time</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-control" name="WalmartProduct[sku_override]">
                                                    <option value="0" <?php if (!$model->sku_override) {
                                                        echo "selected";
                                                    } ?>>NO
                                                    </option>
                                                    <option value="1" <?php if ($model->sku_override) {
                                                        echo "selected";
                                                    } ?>>YES
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control" name="WalmartProduct[product_id_override]">
                                                    <option value="0" <?php if (!$model->product_id_override) {
                                                        echo "selected";
                                                    } ?>>NO
                                                    </option>
                                                    <option value="1" <?php if ($model->product_id_override) {
                                                        echo "selected";
                                                    } ?>>YES
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="public">
                                                    <input type="text" name="WalmartProduct[fulfillment_lag_time]"
                                                           value="<?= $model->fulfillment_lag_time; ?>"
                                                           class="form-control">
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- new fields end -->
                                <?php
                                if ($model->jet_product->type == "simple") {
                                    ?>
                                    <div class="table-responsive grid-view">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Sku</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Barcode(UPC/GTIN/ISBN)</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <b><?= $model->jet_product->sku; ?></b>
                                                    <input type="hidden" name="WalmartProduct[product_sku]"
                                                           value="<?= $model->jet_product->sku ?>"/>
                                                </td>

                                                <td>
                                                    <div>
                                                        <div class="pull-left">
                                                            <div class="public">
                                                                <input class="form-control walmart-price" type="text"
                                                                       id="walmart_product_price"
                                                                       name="WalmartProduct[product_price]"
                                                                       value="<?php if ($model->product_price) {
                                                                           echo $model->product_price;
                                                                       } else {
                                                                           echo $model->jet_product->price;
                                                                       } ?>">
                                                            </div>
                                                        </div>
                                                        <div class="pull-left">
                                                            <?php
                                                            if ($model->status != 'Not Uploaded') { ?>
                                                                <button class="toggle_editor walmart-price-button"
                                                                        type="button"
                                                                        onClick="walmartPrice(this,event);"
                                                                        title="Upload On Walmart"
                                                                        product-id="<?= $model->product_id ?>"
                                                                        product-type="<?= 'simple' ?>"
                                                                        option-id="<?= '' ?>"
                                                                        sku="<?= $model->jet_product->sku ?>"
                                                                        option-price="<?= (float)$model->jet_product->price; ?>">
                                                                    Update
                                                                </button>
                                                            <?php } ?>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <a href="javascript:void(0);" onclick="priceEdit(this)"
                                                       class="variant-price" product-id="<?= $model->product_id ?>"
                                                       option-id="<?= '' ?>" sku="<?= $model->jet_product->sku ?>"
                                                       option-price="<?= (float)$model->jet_product->price; ?>">
                                                        <?php if (WalmartPromoStatus::promotionRulesExist($model->jet_product->sku, MERCHANT_ID)) { ?>
                                                            Edit Promotional Price
                                                        <?php } else { ?>
                                                            Add Promotional Price
                                                        <?php } ?>
                                                    </a>
                                                </td>

                                                <td>
                                                    <div>
                                                        <div class="pull-left">
                                                            <div class="public">
                                                                <input class="form-control walmart-inventory"
                                                                       type="text"
                                                                       id="walmart_product_inventory"
                                                                       name="WalmartProduct[product_inventory]"
                                                                       value="<?php
                                                                       if (is_null($model->product_qty)) {
                                                                           echo $model->jet_product->qty;
                                                                       } else {
                                                                           echo $model->product_qty;
                                                                       }
                                                                       ?>">
                                                            </div>
                                                        </div>
                                                        <div class="pull-left">
                                                            <?php
                                                            if ($model->status != 'Not Uploaded') { ?>
                                                            <button class="toggle_editor walmart-inventory-button"
                                                                    type="button"
                                                                    onClick="walmartInventorymodal(this);"
                                                                    title="Upload On Walmart"
                                                                    product-id="<?= $model->product_id ?>"
                                                                    product-type="<?= 'simple' ?>"
                                                                    option-id="<?= '' ?>"
                                                                    sku="<?= $model->jet_product->sku ?>"
                                                                    option-inventory="<?= (float)$model->jet_product->qty; ?>"
                                                                    fulfillmentLagTime="<?= $model->fulfillment_lag_time ?>"
                                                                    variant_id="<?= $model->jet_product->variant_id ?> ">
                                                                Update
                                                            </button>
                                                            <?php } ?>

                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="public">
                                                        <input type="text" maxlength="500"
                                                               value="<?= $model->jet_product->upc; ?>"
                                                               name="WalmartProduct[product_upc]"
                                                               class="form-control" id="jetproduct-upc">
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
                                } ?>
                            </div>

                            <div id="category_tab">
                                <p style="text-align:center;">
                                    <img src="<?= $loader_img = yii::$app->request->baseUrl . '/frontend/images/batchupload/rule-ajax-loader.gif'; ?>">
                                </p>
                                <p style="text-align:center;">Loading.......</p>
                            </div>
                        </div>

                        <div id="shhipping_exceptions">
                            <div class="form-group field-jetproduct-jet_attributes enable_api">
                                <div>
                                    <table class="table table-striped table-bordered ced-walmart-custome-tbl table-responsive grid-view">
                                        <thead>
                                        <tr>
                                            <th>Shipping Allowed</th>
                                            <th>Shipping Method</th>
                                            <th>Shipping Region</th>
                                            <th>Shipping Price</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="shipping-exceptions-body">
                                        <?php $exceptions = $model->shipping_exceptions;
                                        $exceptions = $exceptions != '' ? json_decode($exceptions, true) : [];
                                        if (isset($exceptions['isShippingAllowed']))
                                            foreach ($exceptions['isShippingAllowed'] as $key => $isShippingAllowed) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        <select default-value="<?= $isShippingAllowed ?>"
                                                                class="form-control exceptions"
                                                                name="WalmartProduct[exceptions][isShippingAllowed][]">
                                                            <option value="1">Yes</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select default-value="<?= $exceptions['shipMethod'][$key] ?>"
                                                                class="form-control exceptions"
                                                                name="WalmartProduct[exceptions][shipMethod][]">
                                                            <option value="VALUE">VALUE</option>
                                                            <option value="STANDARD">STANDARD</option>
                                                            <option value="EXPEDITED">EXPEDITED</option>
                                                            <option value="ONEDAY">ONEDAY</option>
                                                            <option value="FREIGHT">FREIGHT</option>
                                                            <option value="FREIGHT_WITH_WHITE_GLOVE">
                                                                FREIGHT_WITH_WHITE_GLOVE
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select default-value="<?= $exceptions['shipRegion'][$key] ?>"
                                                                class="form-control exceptions"
                                                                name="WalmartProduct[exceptions][shipRegion][]">
                                                            <option value="STREET_48_STATES">STREET_48_STATES</option>
                                                            <option value="PO_BOX_48_STATES">PO_BOX_48_STATES</option>
                                                            <option value="STREET_AK_AND_HI">STREET_AK_AND_HI</option>
                                                            <option value="PO_BOX_AK_AND_HI">PO_BOX_AK_AND_HI</option>
                                                            <option value="STREET_US_PROTECTORATES">
                                                                STREET_US_PROTECTORATES
                                                            </option>
                                                            <option value="PO_BOX_US_PROTECTORATES">
                                                                PO_BOX_US_PROTECTORATES
                                                            </option>
                                                            <option value="APO_FPO">APO_FPO</option>
                                                        </select>
                                                    </td>
                                                    <td><input default-value="<?= $exceptions['shipPrice'][$key] ?>"
                                                               class="form-control exceptions" value="0.00" type="text"
                                                               name="WalmartProduct[exceptions][shipPrice][]"/></td>
                                                    <td>
                                                        <button class="btn btn-primary" type="button"
                                                                onclick="deleteException(this)">Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button style="float:right;" class="btn btn-primary" type="button"
                                                        id="add-exception-button">Add Shipping Exception
                                                </button>
                                            </td>
                                        </tr>

                                        </tfoot>
                                    </table>
                                </div>

                                <div class="Attrubute_html">
                                    <table class="table table-striped table-bordered">
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>


                    <script>
                        $script = '<td>\
                                                <select class="form-control" name="WalmartProduct[exceptions][isShippingAllowed][]">\
                                                    <option value="1">Yes</option>\
                                                    <option value="0">No</option>\
                                                </select>\
                                            </td>\
                                            <td>\
                                                <select class="form-control" name="WalmartProduct[exceptions][shipMethod][]">\
                                                    <option value="VALUE">VALUE</option>\
                                                    <option value="STANDARD">STANDARD</option>\
                                                    <option value="EXPEDITED">EXPEDITED</option>\
                                                    <option value="ONEDAY">ONEDAY</option>\
                                                    <option value="FREIGHT">FREIGHT</option>\
                                                    <option value="FREIGHT_WITH_WHITE_GLOVE">FREIGHT_WITH_WHITE_GLOVE</option>\
                                                </select>\
                                            </td>\
                                            <td>\
                                                <select class="form-control"  name="WalmartProduct[exceptions][shipRegion][]">\
                                                    <option value="STREET_48_STATES">STREET_48_STATES</option>\
                                                    <option value="PO_BOX_48_STATES">PO_BOX_48_STATES</option>\
                                                    <option value="STREET_AK_AND_HI">STREET_AK_AND_HI</option>\
                                                    <option value="PO_BOX_AK_AND_HI">PO_BOX_AK_AND_HI</option>\
                                                    <option value="STREET_US_PROTECTORATES">STREET_US_PROTECTORATES</option>\
                                                    <option value="PO_BOX_US_PROTECTORATES">PO_BOX_US_PROTECTORATES</option>\
                                                    <option value="APO_FPO">APO_FPO</option>\
                                                </select>\
                                            </td>\
                                            <td><input class="form-control" value="0.00" type="text" name="WalmartProduct[exceptions][shipPrice][]"/></td>\
                                            <td>\
                                                <button class="btn btn-primary" type="button" onclick="deleteException(this)">Delete</button>\
                                            </td>';
                        document.getElementById('add-exception-button').onclick = function () {
                            var newTr = document.createElement("TR");
                            newTr.innerHTML = $script;
                            document.getElementById('shipping-exceptions-body').appendChild(newTr);
                        }
                        function deleteException(element) {
                            element.parentNode.parentNode.parentNode.removeChild(element.parentNode.parentNode);
                        }
                    </script>
                </div>

                <div class="modal-footer Attrubute_html">
                    <div class="v_error_msg" style="display:none; text-align:left;"></div>
                    <div class="v_success_msg alert-success alert" style="display:none; text-align:left;"></div>
                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary', 'id' => 'saveedit', 'onclick' => 'saveData()']) ?>
                    <?php if ($shop_url != "") { ?>
                        <a class="btn btn-primary"
                           href="https://<?= $shop_url; ?>/admin/products/<?= trim($model->jet_product->id); ?>"
                           target="_blank" class="" onclick="goToEditShopify();" data-toggle="tooltip"
                           title="To change product information from Shopify.">Edit from Shopify</a>
                    <?php } ?>
                    <button type="button" class="btn btn-primary" id="sync_with_btn" onclick="cnfrmSync()">Sync With
                        Shopify
                    </button>
                    <button type="button" class="btn btn-primary" onclick="cnfrmDelete()">Delete From App</button>
                    <button type="button" class="btn btn-default" id="edit-modal-close" data-dismiss="modal">Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    var csrfToken = $('meta[name="csrf-token"]').attr("content");

    $(document).ready(function () {

        var count = 1;

        $('#myModal input').each(function () {
            var type = $(this).attr("type");
            if (type == "text") {
                $(this).after('<span class="glyphicon glyphicon glyphicon-pencil"></span>');
            }
        });

        $('[data-toggle="tooltip"]').tooltip({html: true});

        $('.exceptions').each(function (e, element) {
            if ($(element).attr('default-value'))
                $(element).val($(element).attr('default-value'));
        });
    });

    function saveData() {
        j$('.v_success_msg').hide();
        j$('.v_success_msg').html('');
        j$('.v_error_msg').hide();
        j$('.v_error_msg').html('');

        var exceptions = [];
        var duplicateExeption = false;
        $('#shipping-exceptions-body tr').each(function (index, element) {
            $method = $(element).find("[name='WalmartProduct[exceptions][shipMethod][]']").val();
            $region = $(element).find("[name='WalmartProduct[exceptions][shipRegion][]']").val();

            if (typeof exceptions[$method + '_' + $region] == 'undefined') {
                exceptions[$method + '_' + $region] = true;
            }
            else {

                duplicateExeption = true;
            }
        });

        if (duplicateExeption) {
            j$('.v_error_msg').html('Duplicate Shipping Method And Region Combination.');
            j$('.v_error_msg').show();
            return;
        }
        var postData = j$("#walmart_edit_form").serializeArray();
        //console.log(postData);
        var formURL = j$("#walmart_edit_form").attr("action");
        var type = '<?= $model->jet_product->type ?>';

        if (type == "variants") {
            if (checkselectedBeforeSubmit()) {
                j$('#LoadingMSG').show();
                j$.ajax(
                    {
                        url: formURL,
                        type: "POST",
                        dataType: 'json',
                        data: postData,
                        _csrf: csrfToken,
                        success: function (data, textStatus, jqXHR) {
                            j$('#LoadingMSG').hide();
                            if (data.success) {
                                j$('.v_success_msg').html(data.success);
                                j$('.v_success_msg').show();

                            }
                            if (data.error) {
                                j$('.v_error_msg').html(data.error);
                                j$('.v_error_msg').show();
                            }
                            //data: return data from server
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            j$('#LoadingMSG').hide();

                            j$('.v_error_msg').html("something went wrong..");
                            j$('.v_error_msg').show();
                        }
                    });
            }
            else {
                return false;
            }
        }
        else {
            if (checkCommonAttributes()) {
                j$('#LoadingMSG').show();
                //submit simple form
                j$.ajax(
                    {
                        url: formURL,
                        type: "POST",
                        dataType: 'json',
                        data: postData,
                        success: function (data, textStatus, jqXHR) {
                            j$('#LoadingMSG').hide();
                            if (data.success) {
                                j$('.v_success_msg').html(data.success);
                                j$('.v_success_msg').show();

                            }
                            if (data.error) {
                                j$('.v_error_msg').html(data.error);
                                j$('.v_error_msg').show();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            j$('#LoadingMSG').hide();

                            j$('.v_error_msg').html("something went wrong..");
                            j$('.v_error_msg').show();
                        }
                    });
            }
        }

    }

    function checkselectedBeforeSubmit() {
        if (!checkCommonAttributes()) {
            return false;
        }

        var mappingFlag = true;
        j$(".variant_attributes_selector").each(function () {
            var mappedValue = $(this).val();
            if (mappedValue == '') {
                mappingFlag = false;
                //function defined in 'varients.php'
                addOptionMappingError(this);
            }
            else {
                //function defined in 'varients.php'
                removeOptionMappingError(this);
            }
        });

        if (!mappingFlag) {
            var errorMsg = "Please map atleast one variant option with Walmart Attribute.";
            j$('.v_error_msg').html(errorMsg);
            alert(errorMsg);
            j$('.v_error_msg').show();

            return false;
        }

        var unitMappingFlag = true;
        j$(".unit_dropdown.active").each(function () {
            var value = $(this).val();
            if (value == '') {
                unitMappingFlag = false;
                //function defined in 'varients.php'
                addUnitMappingError(this);
            }
            else {
                //function defined in 'varients.php'
                removeUnitMappingError(this);
            }
        });
        if (!unitMappingFlag) {
            var errorMsg = "Please choose unit of mapped walmart attribute.";
            j$('.v_error_msg').html(errorMsg);
            alert(errorMsg);
            j$('.v_error_msg').show();

            return false;
        }

        return true;
    }

    function checkCommonAttributes() {
        var flag = true;
        j$('input.common_required').each(function () {
            if (j$(this).val() == '') {
                flag = false;
                //function defined in 'category_tab.php'
                addCommonRequiredError(this);
            }
            else {
                //function defined in 'category_tab.php'
                removeCommonRequiredError(this)
            }
        });

        j$('select.common_required').each(function () {
            if (j$(this).find(":selected").val() == '') {
                flag = false;
                //function defined in 'category_tab.php'
                addCommonRequiredError(this);
            }
            else {
                //function defined in 'category_tab.php'
                removeCommonRequiredError(this)
            }
        });

        if (!flag) {
            addhtml = "Please fill Common Attributes.";
            alert(addhtml);
            j$('.v_error_msg').html(addhtml);
            j$('.v_error_msg').show();
            return false;
        }

        if (!checkOverrideFields()) {
            addhtml = "Either 'Sku Override' OR 'Product Id Override' can be set to 'YES' at a time.";
            alert(addhtml);
            j$('.v_error_msg').html(addhtml);
            j$('.v_error_msg').show();
            return false;
        }

        return true;
    }

    function checkOverrideFields() {
        var flag = true;

        var id_override_val = j$("select[name='WalmartProduct[product_id_override]'] option:selected").val();
        var sku_override_val = j$("select[name='WalmartProduct[sku_override]'] option:selected").val();

        if (id_override_val == sku_override_val && id_override_val == '1')
            flag = false;

        return flag;
    }
</script>

<!-- Code By Himanshu Start -->
<?php
$categoryTabUrl = Data::getUrl("$controllerName/render-category-tab");
?>
<script type="text/javascript">
    j$(document).ready(function () {
        //setTimeout(function(){ renderCategoryTab(); }, 5000);
        renderCategoryTab();
    });

    function renderCategoryTab() {
        var csrf_token = $('meta[name="csrf-token"]').attr("content");
        j$.ajax({
            showLoader: true,
            url: '<?= $categoryTabUrl ?>',
            type: "POST",
            dataType: 'json',
            data: {id: <?= $id ?>, _csrf: csrf_token}
        }).done(function (data) {
            j$('#category_tab').html(data.html);
        });
    }
</script>
<!-- Code By Himanshu End -->

<div id="price-edit"></div>

<div id="description-edit">
    <div class="container">
        <div id="description-edit-modal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" id='edit-content'>
                    <div class="modal-header">
                        <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">
                        Edit Description
                        </h4>
                    </div>
                    <div class="modal-body">
                        <textarea cols="50" id="textarea-description"><?php
                            $var_string = $model->jet_product->description;
                            $var_string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $var_string);
                            echo $var_string;
                            ?></textarea>
                    </div>
                    <div class="modal-footer Attrubute_html" style="padding-right:24px;">
                        <div style="display:none;" class="alert-error alert"></div>
                        <div style="display:none;" class="alert-success alert"></div>

                        <button class="btn btn-primary" onclick="saveDescription(event)" id="save-description"
                                type="submit">Save
                        </button>
                        <button data-dismiss="modal" id="close_desc_modal" class="btn btn-default" type="button">Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirm box html  -->
<div id="confirm" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="cnfrm-yes">Yes</button>
                <button type="button" class="btn" id="cnfrm-no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<?php
$sync_fields = [
    'title' => 'Title',
    'sku' => 'Sku',
    'image' => 'Image',
    'qty' => 'Inventory',
    'weight' => 'Weight',
    'price' => 'Price',
    'upc' => 'UPC/Barcode/Other',
    'variant_option_values' => 'Variant Option Values',
    'vendor' => 'Vendor',
    'product_type' => 'Product Type',
    'description' => 'Description'
];
?>
<!-- Modal Sync Form html  -->
<div id="sync" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="sync-fields-form">
                    <h4>Select Fields to Sync with Shopify :</h4>
                    <div class="sync-fields">
                        <div class="all_checkbox_options">
                            <input type="checkbox" class="all-sync-fields-checkbox"
                                   id="all-sync-fields-checkbox" name="" value="1"/>
                            <label>Select All</label>
                        </div>
                        <?php foreach ($sync_fields as $sync_index => $sync_value) : ?>
                            <div class="checkbox_options">
                                <input type="checkbox" class="sync-fields-checkbox"
                                       name="sync-fields[<?= $sync_index ?>]" value="1"/>
                                <label><?= $sync_value ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="sync-yes">Sync</button>
                <button type="button" class="btn" id="sync-cancel" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirm box html for delete product  -->
<div id="confirm-delete" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                Are you sure?
                <div>
                    <input type="checkbox" name="retire" value="1" id="retire-from-walmart"/>
                    <label style="font-weight:normal; font-size:14px;" for="retire-from-walmar">Delete(Retire) this
                        Product from "Walmart" as well?</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="deleteFromShopify()" id="delete">Delete</button>
                <button type="button" id="close-cnfrm-modal" data-dismiss="modal" class="btn">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Confirm box html for update product inventory -->
<div id="confirm-inventory" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                Are you sure?
                <div>
                    <input type="checkbox" name="update" value="1" id="update-on-shopify"/>
                    <label style="font-weight:normal; font-size:14px;" for="retire-from-walmar">update this
                        Product inventory on "Shopify"?</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="walmartInventory()" id="update">ok</button>
                <button type="button" id="close-cnfrm-modal" data-dismiss="modal" class="btn">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php
$merchant_id = MERCHANT_ID;
$urlWalmartPromotions = \yii\helpers\Url::toRoute(['walmartproduct/promotions']);
$urlWalmartPrice = \yii\helpers\Url::toRoute(['walmartproduct/updatewalmartprice']);
$urlWalmartInventory = \yii\helpers\Url::toRoute(['walmartproduct/updatewalmartinventory']);
$urlCheckRepricing = \yii\helpers\Url::toRoute(['walmartproduct/checkrepricing']);

?>
<script type="text/javascript">

    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    function priceEdit(element) {
        var url = '<?= $urlWalmartPromotions; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        //j$('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {
                sku: element.getAttribute('sku'),
                product_id: element.getAttribute('product-id'),
                merchant_id: merchant_id,
                option_id: element.getAttribute('option-id'),
                price: element.getAttribute('option-price'),
                _csrf: csrfToken
            }
        })
            .done(function (msg) {
                //console.log(msg);
                $('#LoadingMSG').hide();
                $('#price-edit').html(msg);
                //j$('#edit_walmart_product').css("display","block");
                /*$('#price-edit #price-edit-modal').modal({
                 // keyboard: false,
                 //backdrop: 'static'
                 });*/

                $('body').attr('data-promo', 'show');
                $('#edit-modal-close').click();
                $("#edit_walmart_product #myModal").on('hidden.bs.modal', function () {
                    if ($('body').attr('data-promo') == 'show') {
                        $('#price-edit #price-edit-modal').modal('show');
                        $('body').removeAttr('data-promo');
                    }
                });

                reloadEditModal();//file index.php

            });

    }
    function goToEditShopify() {
        $('#myModal').modal('hide');
    }

    function walmartPrice(element, e) {

        var price = $('#walmart_product_price' + element.getAttribute('option-id')).val();

        var url = '<?= $urlWalmartPrice; ?>';

        var checkurl = '<?= $urlCheckRepricing; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        $('#LoadingMSG').show();

        $.ajax({
            method: "post",
            url: checkurl,
            dataType: 'json',
            data: {
                sku: element.getAttribute('sku'),
                id: element.getAttribute('product-id'),
                type: element.getAttribute('product-type'),
                merchant_id: merchant_id,
                option_id: element.getAttribute('option-id'),
                price: price,
                _csrf: csrfToken,
            },
            success: function (data) {
                if (data.success) {
                    $('#LoadingMSG').hide();

                    e.preventDefault();
                    alertify.confirm(" Repricing is currently enabled for this product. Click OK  to disable repricing", function (e) {
                        if (e) {
                            var remove = true;
                            $('#LoadingMSG').show();

                            $.ajax({
                                method: "post",
                                url: url,
                                dataType: 'json',
                                data: {
                                    sku: element.getAttribute('sku'),
                                    id: element.getAttribute('product-id'),
                                    type: element.getAttribute('product-type'),
                                    merchant_id: merchant_id,
                                    option_id: element.getAttribute('option-id'),
                                    price: price,
                                    remove: remove,
                                    _csrf: csrfToken
                                }
                            })
                                .done(function (msg) {
                                    $('#LoadingMSG').hide();

                                    if (msg.success) {
                                        alert_msg(msg.message, 'success', 'bottomCenter', 5000);

                                        j$('.v_success_msg').html('');
                                        j$('.v_success_msg').append(msg.message);
                                        j$('.v_error_msg').hide();
                                        j$('.v_success_msg').show();
                                    }
                                    else if (msg.error) {
                                        /*new Noty({
                                         text: msg.error,
                                         type: 'error',
                                         timeout: 5000,
                                         layout: 'center',
                                         animation: {
                                         open: 'animated bounceInUp', // Animate.css class names
                                         close: 'animated bounceOutDown',
                                         easing: 'swing',
                                         speed: 500 // opening & closing animation speed
                                         }
                                         }).show();*/
                                        alert_msg(msg.message, 'error', 'bottomCenter', 5000)

                                        j$('.v_error_msg').html('');
                                        j$('.v_error_msg').append(msg.error);
                                        j$('.v_success_msg').hide();
                                        j$('.v_error_msg').show();
                                    }
                                    else {
                                        alert_msg("something went wrong.", 'error', 'bottomCenter', 5000)

                                        j$('.v_error_msg').html('');
                                        j$('.v_error_msg').append("something went wrong.");
                                        j$('.v_success_msg').hide();
                                        j$('.v_error_msg').show();
                                    }

                                });
                        }
                        /*else {
                            alert("oh");
                        }*/
                    });

                }
                else {
                    $('#LoadingMSG').show();

                    $.ajax({
                        method: "post",
                        url: url,
                        dataType: 'json',
                        data: {
                            sku: element.getAttribute('sku'),
                            id: element.getAttribute('product-id'),
                            type: element.getAttribute('product-type'),
                            merchant_id: merchant_id,
                            option_id: element.getAttribute('option-id'),
                            price: price,
                            //                price: element.getAttribute('option-price'),
                            //                data:data,
                            _csrf: csrfToken
                        }
                    })
                        .done(function (msg) {
                            $('#LoadingMSG').hide();

                            if (msg.success) {
                                alert_msg(msg.message, 'success', 'bottomCenter', 5000)

                                j$('.v_success_msg').html('');
                                j$('.v_success_msg').append(msg.message);
                                j$('.v_error_msg').hide();
                                j$('.v_success_msg').show();
                                window.location.reload();
                            }
                            else if (msg.error) {
                                alert_msg(msg.error, 'error', 'bottomCenter', 5000)

                                j$('.v_error_msg').html('');
                                j$('.v_error_msg').append(msg.error);
                                j$('.v_success_msg').hide();
                                j$('.v_error_msg').show();
                            }
                            else {
                                alert_msg("something went wrong.", 'error', 'bottomCenter', 5000)

                                j$('.v_error_msg').html('');
                                j$('.v_error_msg').append("something went wrong.");
                                j$('.v_success_msg').hide();
                                j$('.v_error_msg').show();
                            }

                        });
                }
            },
        });


    }

    function walmartInventorymodal() {
        $('body').attr('data-cnfrmdel', 'show');
        $('#edit-modal-close').click();
        $("#edit_walmart_product #myModal").on('hidden.bs.modal', function () {
            if ($('body').attr('data-cnfrmdel') == 'show') {
                $('#confirm-inventory').modal('show');
                $('body').removeAttr('data-cnfrmdel');
            }
        });

        $("#confirm-inventory").on('hidden.bs.modal', function () {
            $('#edit_walmart_product #myModal').modal('show');
        });
    }

    function walmartInventory() {
        $('#confirm-inventory').modal('hide');

        var element = $('.toggle_editor.walmart-inventory-button');
        var checked = $('#update-on-shopify').prop("checked");
        var inventory = $('#walmart_product_inventory' + element.attr('option-id')).val();
        var url = '<?= $urlWalmartInventory; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        $('#LoadingMSG').show();

        $.ajax({
            method: "post",
            url: url,
            dataType: 'json',
            data: {
                sku: element.attr('sku'),
                id: element.attr('product-id'),
                type: element.attr('product-type'),
                merchant_id: merchant_id,
                option_id: element.attr('option-id'),
                fulfillmentLagTime: element.attr('fulfillmentLagTime'),
                variant_id: element.attr('variant_id'),
                qty: inventory,
                isupdate: checked,
                _csrf: csrfToken
            }
        })
            .done(function (msg) {
                $('#LoadingMSG').hide();

                if (msg.success) {
                    alert_msg(msg.success, 'success', 'bottomCenter', 5000)


                    j$('.v_success_msg').html('');
                    j$('.v_success_msg').append(msg.message);
                    j$('.v_error_msg').hide();
                    j$('.v_success_msg').show();
                }
                else if (msg.error) {
                    alert_msg(msg.error, 'error', 'bottomCenter', 5000);

                    j$('.v_error_msg').html('');
                    j$('.v_error_msg').append(msg.error);
                    j$('.v_success_msg').hide();
                    j$('.v_error_msg').show();
                }
                else {
                    alert_msg("something went wrong.", 'success', 'bottomCenter', 5000)

                    j$('.v_error_msg').html('');
                    j$('.v_error_msg').append("something went wrong.");
                    j$('.v_success_msg').hide();
                    j$('.v_error_msg').show();
                }

            });

    }

    function alert_msg(message, type, layout, timeout) {
        new Noty({
            text: message,
            type: type,
            timeout: timeout,
            layout: layout,
            animation: {
                open: 'animated bounceInUp', // Animate.css class names
                close: 'animated bounceOutDown',
                easing: 'swing',
                speed: 500 // opening & closing animation speed
            }
        }).show();
    }
</script>

<script type="text/javascript">

    function editDescription(event) {
        $('body').attr('data-desc', 'show');
        $('#edit-modal-close').click();
        $("#edit_walmart_product #myModal").on('hidden.bs.modal', function () {
            if ($('body').attr('data-desc') == 'show') {
                $('#description-edit #description-edit-modal').modal('show');
                $('body').removeAttr('data-desc');
            }
        });

        $("#description-edit #description-edit-modal").on('shown.bs.modal', function () {
            var editor = new nicEditor({buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'removeformat', 'indent', 'outdent', 'hr', 'image', 'forecolor', 'bgcolor', 'link', 'unlink', 'fontSize', 'fontFamily', 'fontFormat', 'xhtml']/*fullPanel : true*/}).panelInstance('textarea-description');
        });

        $("#description-edit #description-edit-modal").on('hidden.bs.modal', function () {
            $('#edit_walmart_product #myModal').modal('show');
        });
    }

    function saveDescription(event) {
        j$('#LoadingMSG').show();
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        var nicInstance = nicEditors.findEditor('textarea-description');
        var description = nicInstance.getContent();

        var url = "<?= Data::getUrl('product/save-description') ?>";
        var productId = $("#productid").val();

        $.ajax({
            method: "post",
            url: url,
            dataType: "json",
            data: {product_id: productId, description: description, _csrf: csrfToken}
        })
            .done(function (response) {
                j$('#LoadingMSG').hide();
                if (response.success) {

                    $('#product_desc').val(description);

                    /*var newDesc = description.replace(/(<([^>]+)>)/ig, "");
                     if (newDesc.length > 50) {
                     var moreDesc = newDesc.substr(0, 50);

                     moreDesc = moreDesc + "...<a  onclick='showDescription(event)' title='More Description' href='#'>more</a>";
                     moreDesc = moreDesc + '<a onclick="editDescription(event)">edit</a>';

                     $('.more').html(moreDesc);
                     }
                     else {
                     newDesc = newDesc + '<a onclick="editDescription(event)">edit</a>';
                     $('.more').html(newDesc);
                     }*/

                    j$('#close_desc_modal').click();
                }
                else if (response.error) {
                    alert(response.message);
                }
                else {
                    alert("something went wrong.");
                }
            });
    }
</script>

<script type="text/javascript">
    /*var area1, area2;

     function toggleEditor1() {
     if (!area1) {
     area1 = new nicEditor({buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'removeformat', 'indent', 'outdent', 'hr', 'image', 'forecolor', 'bgcolor', 'link', 'unlink', 'fontSize', 'fontFamily', 'fontFormat', 'xhtml']}).panelInstance('shortDescriptionField', {hasPanel: true});

     area1.addEvent('blur', function () {
     if (area1) {
     area1.removeInstance('shortDescriptionField');
     area1 = null;
     }
     });
     } else {
     area1.removeInstance('shortDescriptionField');
     area1 = null;
     }
     }

     function toggleEditor2() {
     if (!area2) {
     area2 = new nicEditor({buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'removeformat', 'indent', 'outdent', 'hr', 'image', 'forecolor', 'bgcolor', 'link', 'unlink', 'fontSize', 'fontFamily', 'fontFormat', 'xhtml']}).panelInstance('shelfDescriptionField', {hasPanel: true});

     area2.addEvent('blur', function () {
     if (area2) {
     area2.removeInstance('shelfDescriptionField');
     area2 = null;
     }
     });
     } else {
     area2.removeInstance('shelfDescriptionField');
     area2 = null;
     }
     }*/
</script>

<script>
    function cnfrmSync() {
        $('body').attr('data-sync', 'show');
        $('#edit-modal-close').click();
        $("#edit_walmart_product #myModal").on('hidden.bs.modal', function () {
            if ($('body').attr('data-sync') == 'show') {
                $('#sync').modal('show');
                $('body').removeAttr('data-sync');
            }
        });

        $("#sync").on('shown.bs.modal', function () {
            $('#sync-yes').unbind('click');
            $('#sync-yes').on('click', function () {
                syncWithShopify();
            });
        });

        $("#sync").on('hidden.bs.modal', function () {
            $('#edit_walmart_product #myModal').modal('show');
        });
    }

    function syncWithShopify() {
        var selectCount = 0;
        $.each($(".sync-fields-checkbox"), function () {
            if ($(this).is(':checked') === true) {
                selectCount++;
            }
        });

        if (selectCount) {
            $('#sync-cancel').click();
            $('#LoadingMSG').show();
            var url = "<?= Data::getUrl('walmartscript/shopifyproductsync') ?>";
            var productId = $("#productid").val();

            var fields = $("#sync-fields-form").serialize();

            $.ajax({
                method: "post",
                url: url,
                dataType: "json",
                data: {product_id: productId, _csrf: csrfToken, sync_fields: fields}
            })
                .done(function (response) {
                    $('#LoadingMSG').hide();
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    }
                    else if (response.error) {
                        alert(response.message);
                    }
                    else {
                        alert("something went wrong.");
                    }
                });
        }
        else {
            alert("Please select fields to sync.");
        }
        $("#sync-fields-form")[0].reset();
    }

    function cnfrmDelete() {
        $('body').attr('data-cnfrmdel', 'show');
        $('#edit-modal-close').click();
        $("#edit_walmart_product #myModal").on('hidden.bs.modal', function () {
            if ($('body').attr('data-cnfrmdel') == 'show') {
                $('#confirm-delete').modal('show');
                $('body').removeAttr('data-cnfrmdel');
            }
        });

        $("#confirm-delete").on('hidden.bs.modal', function () {
            $('#edit_walmart_product #myModal').modal('show');
        });
    }

    function deleteFromShopify() {
        j$('#close-cnfrm-modal').click();
        j$('#LoadingMSG').show();
        var url = "<?= Data::getUrl('walmartscript/deleteproduct') ?>";
        var productId = $("#productid").val();
        var Retire = 0;
        if ($("#retire-from-walmart").is(":checked")) {
            Retire = 1;
        }

        $.ajax({
            method: "post",
            url: url,
            dataType: "json",
            data: {product_id: productId, _csrf: csrfToken, retire: Retire}
        })
            .done(function (response) {
                j$('#LoadingMSG').hide();
                if (response.success) {
                    alert("Product Deleted Successfully.");
                    window.location.reload();
                }
                else if (response.error) {
                    alert(response.message);
                }
                else {
                    alert("something went wrong.");
                }
            });
    }


</script>
<!-- Select All on Sync With shopify -->
<script type="text/javascript">
    $('#all-sync-fields-checkbox').click(function () {
        if (!$(this).is(':checked')) {
            $('.sync-fields-checkbox').prop('checked', false);
        }
        else {
            $('.sync-fields-checkbox').prop('checked', true);
        }
    });
    $('.checkbox_options').click(function () {
        $("input:checkbox[class=sync-fields-checkbox]").each(function () {
            if (!$(this).is(':checked')) {
                $('#all-sync-fields-checkbox').prop('checked', false);
                return false;
            }
            else {
                $('#all-sync-fields-checkbox').prop('checked', true);
            }
        });

    });
</script>
<!-- end here -->

<style>
    .walmart-price {
        width: 50px;
    }

    .walmart-inventory {
        width: 50px;
    }

    .walmart-price-button {
        background: rgba(0, 0, 0, 0) linear-gradient(to bottom, #73efed 0%, #3b67dd 100%) repeat scroll 0 0;
        border: 1px solid #f2f2f2;
        color: #fff;
        font-size: 13px;
        margin: 0;
        padding: 5px 5px;
        text-transform: capitalize;
        transition: all 0.2s ease 0s;
    }

    .walmart-inventory-button {
        background: rgba(0, 0, 0, 0) linear-gradient(to bottom, #73efed 0%, #3b67dd 100%) repeat scroll 0 0;
        border: 1px solid #f2f2f2;
        color: #fff;
        font-size: 13px;
        margin: 0;
        padding: 5px 5px;
        text-transform: capitalize;
        transition: all 0.2s ease 0s;
    }

    .modal-lg {
        width: 975px !important;
    }

    .walmart_price {
        width: 151px;
    }

    .walmart_inventory {
        width: 151px;
    }

    /*    .public::after {
        clear: both;
        content: "";
        display: table;
        }
        .public .form-control {
            float: left;
            width: 130px;
        }
        .public .glyphicon {
            display: inline-block;
            float: left;
            margin: 9px 5px 0;
            width: 20px;
        }*/
    .field-jetproduct .public input {
        display: inline-block;
        margin-right: 12px !important;
        width: 74%;
    }

    .field-jetproduct .public span {
        display: inline-block;
    }
</style>