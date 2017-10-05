<?php
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\components\WalmartPromoStatus;

$viewVariants = Data::getUrl('walmartproduct/getwalmartdata');
if (count($variantAttributes)) {
    $product_id = $model->product_id;
    $sku = $model->jet_product->sku;
    $shopify_product_type = $model->jet_product->product_type;

    //Get shopify Options
    $shopifyOptions = json_decode(stripslashes($model->jet_product->attr_ids), true) ?: [];

    //Mapping of Shopify Option with walmart attribute
    $shopifyOptionsMapping = [];
    if ($model->walmart_attributes != "") {
        $shopifyOptionsMapping = json_decode($model->walmart_attributes, true);
    }

    $option_status = ['PUBLISHED', 'UNPUBLISHED', 'STAGE'];

    ?>
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <!-- <th>
                <center>Option Id</center>
            </th> -->
            <th>
                <center>SKU</center>
            </th>
            <!-- <th>
                <center>Images</center>
            </th> -->
            <th class="walmart_price">
                <center>Price</center>
            </th>
            <th class="walmart_inventory">
                <center>Inventory</center>
            </th>
            <th colspan="2">
                <center>Barcode</center>
            </th>
            <th colspan="4">
                <center>Map Walmart Attributes</center>
            </th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <!-- <td rowspan="2" colspan="1">
            </td>
            <td rowspan="2" colspan="3"></td> -->
            <td rowspan="2" colspan="7">
            </td>
            <?php
            $shopifyArr = array();
            foreach ($shopifyOptions as $key => $val) {
                ?>
                <td>
                    <div><b>
                            <center>Shopify Option</center>
                        </b></div>
                    <div>
                        <input type="hidden"
                               name="WalmartProduct[shopify_options_mapping][<?= $key ?>][shopify_option_name]"
                               value="<?= $val ?>"/>
                        <center><?= $val ?></center>
                    </div>
                </td>
                <?php
                $shopifyArr[] = $key;
            }
            ?>
        </tr>
        <tr>
            <?php
            $shopifyOptionIds = [];

            foreach ($shopifyOptions as $key => $val) {
                $shopifyOptionIds[] = $key;

                $attr_value = "";
                if (isset($shopifyOptionsMapping[$val])) {
                    //get attribute mapping from product edit
                    $attr_value = trim($shopifyOptionsMapping[$val][0]);
                } else {
                    //get globally mapped attribute value
                    $walmart_attribute_code = Data::getWamartattributecode($merchant_id, $val, $shopify_product_type);
                    $attr_value = trim($walmart_attribute_code['walmart_attribute_code']);
                }

                if (Yii::$app->getRequest()->getQueryParam('clearattr') == 1) {
                    $attr_value = "";
                }
                ?>
                <td>
                    <div>
                        <b>
                            <center>Walmart attribute(s)</center>
                        </b>
                    </div>

                    <select class="variant_attributes_selector form-control" id="sel_<?= $key ?>" data-key="<?= $key ?>"
                            name="WalmartProduct[shopify_options_mapping][<?= $key ?>][walmart_attribute]">
                        <option value="">Select a Walmart Attribute...</option>
                        <?php
                        foreach ($variantAttributes as $p_ar) {
                            $p_ar = trim($p_ar);
                            ?>
                            <option value="<?= $p_ar ?>" <?= ($p_ar == $attr_value) ? "selected='selected'" : "" ?> >
                                <?= $p_ar ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>

                    <?php
                    foreach ($variantAttributes as $varKey => $varVal) {
                        if (isset($unitAttributes[$varKey])) {
                            $attributeName = $unitAttributes[$varKey];
                            $attrOptions = isset($attributeOptions[$attributeName]) ? $attributeOptions[$attributeName] : [];
                            $savedValue = AttributeMap::getSavedAttributeValue($attributeName, $common_attr_values, $saved_attribute_mapping);

                            $p_ar_value = str_replace(AttributeMap::ATTRIBUTE_PATH_SEPERATOR, '_', $attributeName);
                            ?>
                            <div <?= (trim($varVal) == $attr_value) ? "style='display:block;'" : "style='display:none;'" ?>
                                    class="unit_dropdown_class" id="unit_<?= $key ?>_<?= $p_ar_value ?>">

                                <label class="unit_label" style="width:30%;float:left;">Unit </label>

                                <select <?= (trim($varVal) != $attr_value) ? "disabled='disabled'" : "" ?>
                                        style="width:50%;" class="unit_dropdown form-control"
                                        id="opt_<?= $key ?>_<?= $p_ar_value ?>"
                                        name="WalmartProduct[common_attributes][<?= $attributeName ?>]">
                                    <option value=""></option>
                                    <?php
                                    foreach ($attrOptions as $ky => $sel_op) {
                                        ?>
                                        <option value="<?= trim($sel_op) ?>" <?= ($savedValue == $sel_op) ? "selected='selected'" : "" ?>>
                                            <?= trim($sel_op) ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>

                                <div class="clear" style="clear:both;"></div>

                            </div>
                            <?php
                        }
                    }
                    ?>

                </td>
                <?php
            }
            ?>
        </tr>
        <?php
        $query = "SELECT option_sku,option_image,barcode_type,jetvar.option_id,option_qty,option_price,option_unique_id,variant_option1,variant_option2,variant_option3,walvar.new_variant_option_1,walvar.new_variant_option_2,walvar.new_variant_option_3,`walvar`.`status`,`walvar`.`option_prices`,`walvar`.`option_qtys`,jetvar.product_id as product_id FROM `walmart_product_variants` AS walvar INNER JOIN `jet_product_variants` AS jetvar ON walvar.option_id = jetvar.option_id WHERE jetvar.product_id='" . $product_id . "' order by jetvar.option_sku='" . $sku . "' desc, jetvar.option_id asc";
        $productOptions = Data::sqlRecords($query, "all", "select");

        $variant_opt_skus = [];
        if (is_array($productOptions) && count($productOptions) > 0) {
            foreach ($productOptions as $value) {
                if (trim($value['option_sku']) == "" || in_array(trim($value['option_sku']), $variant_opt_skus)) {
                    continue;
                }
                $variant_opt_skus[] = trim($value['option_sku']);
                ?>
                <tr>
                    <td>
                        <input type="hidden"
                                   name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][variant_id]"
                                   value="<?= $value['option_id'] ?>">
                        <?php
                        if (!empty($value['option_image'])) {
                            ?>
                            <img src="<?= $value['option_image'] ?>" width='60px' height='60px' alt="Option Image"/>
                        <?php }elseif($model->jet_product->image !==''){
                                    $images = explode(',', $model->jet_product->image);
                                    if(count($images)>0){?>
                                    <img src="<?= $images[0] ?>" width='60px' height='60px' alt="Option Image"/>
                                    <?php }else{?>
                                        <img width="70px" height="70px" src="<?=Yii::$app->getUrlManager()->getBaseUrl()?>/images/noimage.png">
                                   <?php }
                            }else{?>
                            <img width="70px" height="70px" src="<?=Yii::$app->getUrlManager()->getBaseUrl()?>/images/noimage.png"> 
                           <?php } ?>
                        <?php
                        if (in_array($value['status'], $option_status)) {
                            ?>
                            <a href="javascript:void(0);" onclick="viewVariants(this)"
                               option-sku="<?= $value['option_sku'] ?>"><?= $value['option_sku'] ?></a>
                            <?php
                        } else {
                            ?>
                            <?= $value['option_sku'] ?>
                        <?php } ?>
                        <p>
                    <span style="border: 1px solid #333; padding: 0 5px; color: #555">
                        <?= is_null($value['status']) ? 'No Status' : $value['status'] ?>
                    </span>
                        </p>
                    </td>
                    <td>
                        <div>
                            <div class="pull-left">
                                <input class="form-control walmart-price" type="text"
                                       id="walmart_product_price<?= $value['option_id']; ?>"
                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][variant_price]"
                                       value="<?php if (isset($value['option_prices']) && !empty($value['option_prices'])) {
                                           echo $value['option_prices'];
                                       } else {
                                           echo $value['option_price'];
                                       } ?>">
                            </div>
                            <div class="pull-left">
                                <?php
                                if ($value['status'] != 'Not Uploaded') {
                                    ?>
                                    <button class="toggle_editor walmart-price-button" type="button"
                                            onClick="walmartPrice(this,event);" title="Upload On Walmart"
                                            product-id="<?= $model->product_id ?>" product-type="<?= 'variants' ?>"
                                            option-id="<?= $value['option_id'] ?>" sku="<?= $value['option_sku'] ?>"
                                            option-price="<?= (float)$value['option_price']; ?>">Update
                                    </button>
                                <?php } ?>


                            </div>
                            <div class="clear"></div>
                        </div>

                        <a href="javascript:void(0);" onclick="priceEdit(this)" class="variant-price"
                           product-id="<?= $value['product_id'] ?>" option-id="<?= $value['option_id'] ?>"
                           sku="<?= $value['option_sku'] ?>" option-price="<?= $value['option_price'] ?>">
                            <?php if (WalmartPromoStatus::promotionRulesExist($value['option_sku'], $merchant_id)) { ?>
                                Edit Promotional Price
                            <?php } else { ?>
                                Add Promotional Price
                            <?php } ?>
                        </a>
                    </td>
                    <td>
                        <div>
                            <div class="pull-left">
                                <input class="form-control walmart-inventory" type="text"
                                       id="walmart_product_inventory<?= $value['option_id']; ?>"
                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][variant_inventory]"
                                       value="<?php
                                       if (is_null($value['option_qtys'])) {
                                           echo $value['option_qty'];
                                       } else {
                                           echo $value['option_qtys'];
                                       }
                                       ?>">
                            </div>
                            <div class="pull-left">
                                <?php
                                if ($value['status'] != 'Not Uploaded') {
                                    ?>
                                    <button class="toggle_editor walmart-inventory-button" type="button"
                                            onClick="walmartInventorymodal(this);" title="Upload On Walmart"
                                            product-id="<?= $model->product_id ?>" product-type="<?= 'variants' ?>"
                                            option-id="<?= $value['option_id'] ?>" sku="<?= $value['option_sku'] ?>"
                                            option-inventory="<?= (float)$value['option_qty'] ?>"
                                            fulfillmentLagTime="<?= $model->fulfillment_lag_time ?>">Update
                                    </button>
                                <?php } ?>


                            </div>
                            <div class="clear"></div>
                        </div>
                    </td>
                    <td colspan="4">
                        <input type="text" class="upc_opt form-control"
                               value="<?= $value['option_unique_id'] ?>"
                               name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][variant_upc]"/>
                        <input type="hidden" value="<?= $value['option_sku'] ?>"
                               name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][variant_sku]">
                    </td>

                    <?php
                    if ($value['variant_option1'] && isset($shopifyOptionIds[0])) {
                        ?>
                        <td>
                            <input type="text" class="form-control" readonly=""
                                   value="<?= stripslashes($value['variant_option1']) ?>"
                                   name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][option_values][<?= $shopifyOptionIds[0] ?>]">
                        </td>
                        <?php
                    }
                    if ($value['variant_option2'] && isset($shopifyOptionIds[1])) {
                        ?>
                        <td>
                            <input type="text" class="form-control" readonly=""
                                   value="<?= stripslashes($value['variant_option2']) ?>"
                                   name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][option_values][<?= $shopifyOptionIds[1] ?>]">
                        </td>
                        <?php
                    }
                    if ($value['variant_option3'] && isset($shopifyOptionIds[2])) {
                        ?>
                        <td>
                            <input type="text" class="form-control" readonly=""
                                   value="<?= stripslashes($value['variant_option3']) ?>"
                                   name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][option_values][<?= $shopifyOptionIds[2] ?>]">
                        </td>
                    <?php } ?>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <?php
} else {
    ?>
    <span class="no_attribute_in_category">No attributes for the selected Category</span>

    <?php
}
?>
<!-- <div id="view_walmart_product" style="display:none"></div> -->

<script>
    function viewVariants(element) {
        j$('#LoadingMSG').show();

        var url = '<?= $viewVariants;?>';
        var merchant_id = '<?= $merchant_id;?>';
        j$.ajax({
            method: "post",
            url: url,
            data: {
                id: element.getAttribute('option-sku'),merchant_id: merchant_id,_csrf: csrfToken
            }
        })
            .done(function (msg) {
                //console.log(msg);
                j$('#LoadingMSG').hide();
                j$('#view_walmart_product').html(msg);

                j$('body').attr('data-variant', 'show');
                j$('#edit-modal-close').click();
                $("#edit_walmart_product #myModal").on('hidden.bs.modal', function () {
                    if (j$('body').attr('data-variant') == 'show') {
                        j$('#view_walmart_product').css("display", "block");

                        $('#view_walmart_product #product_view_modal').modal('show');
                        j$('body').removeAttr('data-variant');
                    }
                });
                reloadEditModal2();//file index.php
            });
    }
</script>

<script type="text/javascript">
    j$(".variant_attributes_selector").on('change', function () {
        if (!checkSame(this)) {
            j$(this).val("");

            j$('.v_success_msg').hide();

            var errorMsg = "Please don't choose same Walmart Attibute for multiple option(s).";
            j$('.v_error_msg').html(errorMsg);
            j$('.v_error_msg').show();
            alert(errorMsg);

            disableUnitAttributes(this);

            return false;
        }
        else {
            j$('.v_error_msg').hide();
            j$('.v_error_msg').html("");
        }

        addRemoveRequiredAttributes();
    });

    function disableUnitAttributes(ele) {
        j$(ele).parent().find('.unit_dropdown_class').hide();
        j$(ele).parent().find('.unit_dropdown_class .unit_dropdown').prop("disabled", true);
        j$(ele).parent().find('.unit_dropdown_class .unit_dropdown').removeClass("active");
    }

    function checkSame(element) {
        var currentElementValue = $(element).val();

        if (currentElementValue == '') {
            addOptionMappingError(element);
        } else {
            removeOptionMappingError(element);
        }

        var currentElementId = $(element).attr('id');

        var flag = true;

        j$(".variant_attributes_selector").each(function () {
            var mappedValue = $(this).val();
            var elementId = $(this).attr('id');
            if (mappedValue != '') {
                if (currentElementValue == mappedValue && currentElementId != elementId) {
                    flag = false;
                    return false;//break the loop
                }
            }
        });

        return flag;
    }

    function addOptionMappingError(element) {
        j$(element).css('border', 'solid 1px red');
    }

    function removeOptionMappingError(element) {
        j$(element).css('border', '');
    }

    var requiredAttrs = <?= json_encode($requiredAttributes) ?>;
    var unitAttrs = <?= json_encode($unitAttributes) ?>;
    var attrOptions = <?= json_encode($attributeOptions) ?>;

    /*console.log(requiredAttrs);
     console.log(unitAttrs);*/

    $(document).ready(function () {
        addRemoveRequiredAttributes();
    });

    function addRemoveRequiredAttributes() {
        //array of selected or mapped walmart attributes with shopify option
        var selectedVariantAttr = [];
        j$('.variant_attributes_selector').each(function () {
            var value = j$(this).val();
            if (value != '') {
                selectedVariantAttr.push(value);
            }

            toggleUnitAttributes(this);
        });

        requiredAttrs.forEach(function (requiredAttrCode) {

            var index = selectedVariantAttr.indexOf(requiredAttrCode);
            if (index > -1) {
                //function defined in 'category_tab.php'
                removeCommonAttribute(requiredAttrCode);
            }
            else {
                var toBeAdded = true;

                var requiredAttrs = requiredAttrCode.split('<?=AttributeMap::ATTRIBUTE_PATH_SEPERATOR?>');

                selectedVariantAttr.forEach(function (selectedAttrCode) {
                    var selectedAttrs = selectedAttrCode.split('<?=AttributeMap::ATTRIBUTE_PATH_SEPERATOR?>');

                    if (requiredAttrs[0] == selectedAttrs[0]) {
                        toBeAdded = false;

                        //function defined in 'category_tab.php'
                        removeCommonAttribute(requiredAttrCode);

                        return false;//break loop
                    }
                });

                if (toBeAdded) {
                    addCommonAttribute(requiredAttrCode);
                }
            }
        });

        toggleConditionalAttributes();
    }

    function addCommonAttribute(attributeCode) {
        if (attrOptions.hasOwnProperty(attributeCode)) {
            //function defined in 'category_tab.php'
            createSelectField(attributeCode, attrOptions[attributeCode]);
        }
        else {
            //function defined in 'category_tab.php'
            createTextField(attributeCode)
        }
    }

    function toggleUnitAttributes(element) {
        var selectedValue = j$(element).val();
        var dataKey = j$(element).attr("data-key");

        if (selectedValue.indexOf("<?=AttributeMap::ATTRIBUTE_PATH_SEPERATOR?>") !== -1) {
            var attr = selectedValue.split('<?=AttributeMap::ATTRIBUTE_PATH_SEPERATOR?>');

            if (unitAttrs.hasOwnProperty(attr[0])) {
                var unitAttrCode = unitAttrs[attr[0]];
                unitAttrCode = unitAttrCode.replace("<?=AttributeMap::ATTRIBUTE_PATH_SEPERATOR?>", "_");

                var div_id = "#unit_" + dataKey + "_" + unitAttrCode;
                var element_id = "#opt_" + dataKey + "_" + unitAttrCode;

                j$(div_id).show();
                j$(element_id).prop("disabled", false);
                j$(element_id).addClass("active");
            }
            else {
                disableUnitAttributes(element);
            }
        }
        else {
            disableUnitAttributes(element);
        }
    }

    j$(".unit_dropdown").on('blur', function () {
        var value = j$(this).val();
        if (value == '') {
            addUnitMappingError(this);
        } else {
            removeUnitMappingError(this);
        }
    });

    function addUnitMappingError(element) {
        j$(element).css('border', 'solid 1px red');
    }

    function removeUnitMappingError(element) {
        j$(element).css('border', '');
    }
</script>