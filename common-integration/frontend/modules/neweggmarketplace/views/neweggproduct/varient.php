<?php
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\categories\Categoryhelper;

$shopifyattribues = array();
$urlAttributeValue = \yii\helpers\Url::toRoute(['neweggproduct/getattributevalue']);
$shopifyattribues = json_decode(stripslashes($model->jet_product->attr_ids), true);
$product_id = $model->product_id;
$sku = $model->jet_product->sku;
$url = \yii\helpers\Url::toRoute(['neweggproduct/getattribute']);
$w = 0;
$newegg_data = json_decode($model->newegg_data, true);
$category_id = $newegg_data['category']['id'];
$subcategory_id = $newegg_data['subcategory']['id'];
//print_r($requiredgroupby);die('ssss');


?>

<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>
            <center>Option Id</center>
        </th>
        <th>
            <center>SKU</center>
        </th>
        <th>
            <center>Images</center>
        </th>
        <th>
            <center>Price</center>
        </th>
        <th>
            <center>Inventory</center>
        </th>
        <th>
            <center>Mpn</center>
        </th>
        <th colspan="1">
            <center>Barcode(UPC/EAN/ISBN)</center>
        </th>
        <th colspan="3">
            <center>Map Newegg Attributes</center>
        </th>


    </tr>
    </thead>
    <tbody>
    <tr>
        <td rowspan="2" colspan="1">
            <input type="hidden" name="product-type" value="variants"/>
        </td>
        <td rowspan="2" colspan="4">
        </td>
        <td rowspan="2" colspan="2">
        </td>
        <?php $shopifyArr = array();

        $query1 = "SELECT `attribute_map` FROM `newegg_attribute_map` WHERE merchant_id =" . MERCHANT_ID . " AND shopify_product_type='" . $model->shopify_product_type . "' AND  newegg_category_id = '" . $model->newegg_category . "'";
        $array = Data::sqlRecords($query1, "one", "select");
        $query = "SELECT * FROM `newegg_product_variants` AS neweggvar INNER JOIN `jet_product_variants` AS jetvar ON neweggvar.option_id = jetvar.option_id WHERE jetvar.product_id='$product_id' order by jetvar.option_sku='" . $sku . "' desc, jetvar.option_id asc";
        $productOptions = Data::sqlRecords($query, "all", "select");

        $comm_prev = [];
        if ($productOptions) 
        {
        if (array_key_exists('newegg_option_attributes', $productOptions[0])){
        $comm_prev = json_decode($productOptions[0]['newegg_option_attributes'], true);
        $arr = array();
        $attrArr = array();
        $mapper = [];
        $valArray = [];
        $attributeMap = json_decode($array['attribute_map'], true);
        if (!empty($attributeMap)) {
            foreach ($attributeMap as $k2 => $v2) {
                $attributeMapData = explode('->', $v2);
                $mapping = explode(',', $attributeMapData[1]);
                foreach ($mapping as $key5 => $value5) {
                    $mapper[$attributeMapData[0]] = $value5;
                }
            }
        }
        /*else{ ?>
            <!-- For attribute mapping Notification -->
            <script type="text/javascript">
                $('#attributes_notification').css("display", "block");
             </script>
        <?php }*/
        //$attr_ids = json_decode($value['attr_ids'],true);
        $j = 0;
        if(count($shopifyattribues)>0){
            foreach ($shopifyattribues as $k1 => $v1) {
                $valArray[$v1] = $j;
                $j++;
            }
            // print_r($shopifyattribues);die;
            $flag = 0;
            foreach ($shopifyattribues as $key => $val) {
                $flag++;
                ?>
                <td>
                    <div><b>
                            <center>Shopify Option</center>
                        </b></div>
                    <div>
                        <center><?= $val ?></center>
                    </div>
                </td>
                <?php $shopifyArr[] = $key;
                $arr[] = $val;
            } 
        }
        ?>
    </tr>
    <tr>
        <?php
        for ($i = 0; $i < $flag; $i++) {
            ?>
            <td>
            <div><b>
                    <center>Newegg Attributes</center>
                </b></div>
        <select class="variant_attributes_selector jet_attributes_selector form-control sel" id="sel_<?= $i ?>"
                name="jet_attributes[<?= $i ?>][newegg_attr_id] ">
            <option value="">Please Select Category</option>

            <?php
            $count = -1;
            if (isset($requiredgroupby) && !empty($requiredgroupby)) {
                foreach ($requiredgroupby as $attr_val) {
                    foreach ($attr_val as $keys => $val1) {
                        $count++;

                        ?>
                        <?php
                        ?>
                        <option value="<?= $val1['PropertyName'] ?>"<?php if (!empty($comm_prev)) {
                            if (array_key_exists($val1['PropertyName'], $comm_prev)) { ?> selected='selected' <?php $unset = $val1['PropertyName'];
                            }
                        } ?>><?= $val1['PropertyName'] ?></option>


                        <?php
                    }
                    if (isset($unset)) {
                        unset($comm_prev[$unset]);
                    }
                } ?></select></td><?php }
        }; ?>


    </tr>

    <?php }
    } ?>

    <?php

    $i = 0;
    foreach ($productOptions as $data) { ?>

        <tr>
            <td><?= $data['option_id'] ?></td>
            <td class="sku"><?= $data['option_sku'] ?></td>
            <td style="text-align: center;">
                <?php
                if (!empty($data['option_image'])) {
                    ?>

                    <img src="<?= $data['option_image'] ?>" width='60px' height='60px' alt="Option Image">

                <?php } ?>

            </td>

            <!--<td><? /*= $data['option_qty'] */ ?></td>-->
            <td>
                <div class="pull-left">
                    <!--<input class="form-control walmart-price" type="text"
                                           id="walmart_product_price<? /*= $value['option_id']; */ ?>"
                                           name="jet_varients_opt[<? /*= $value['option_id'] */ ?>][walmart_product_price]"
                                           value="<? /*= (float)$value['option_price']; */ ?>">-->
                    <input class="form-control newegg-price" type="text"
                           id="newegg_product_price<?= $data['option_id']; ?>"
                           name="jet_varients_opt[<?= $data['option_id'] ?>][newegg_product_price]"
                           value="<?php if (isset($data['option_prices']) && !empty($data['option_prices'])) {
                               echo $data['option_prices'];
                           } else {
                               echo $data['option_price'];
                           } ?>">
                </div>
            </td>
            <td>
                <div class="pull-left">
                    <input class="form-control newegg-inventory" type="text"
                           id="newegg_product_inventory<?= $data['option_id']; ?>"
                           name="jet_varients_opt[<?= $data['option_id'] ?>][newegg_product_inventory]"
                           value="<?php
                           echo $data['option_qty'];;
                           ?>">
                </div>
            </td>
            <td>
                <input type="text" style="" class="upc_opt form-control" value="<?= $data['option_mpn'] ?>"
                       name="jet_varients_opt[<?= $data['option_id'] ?>][mpn]">
            </td>

            <td>
                <input type="text" style="" class="upc_opt form-control" value="<?= $data['option_unique_id'] ?>"
                       name="jet_varients_opt[<?= $data['option_id'] ?>][upc]">
                <label class="variant_as_parent"
                       style="display:none;"><?php if (trim($sku) == trim($data['option_sku'])) {
                        echo "1";
                    } else {
                        echo "0";
                    } ?></label>
                <input type="hidden" value="<?= $data['option_sku'] ?>"
                       name="jet_varients_opt[<?= $data['option_id'] ?>][optionsku]">
            </td>
            <?php if ($data['variant_option1']) {
                ?>
                <td class="variants_0" id='variants_0_<?= $i ?>'><input type="text"
                                                                        value="<?= $data['variant_option1'] ?>"
                                                                        name="jet_attributes[0][<?= $data['option_id'] ?>][value]"
                                                                        readonly=""></td>
                <?php
            }
            if ($data['variant_option2']) {
                ?>
                <td class="variants_1" id='variants_1_<?= $i ?>'><input type="text"
                                                                        value="<?= $data['variant_option2'] ?>"
                                                                        name="jet_attributes[1][<?= $data['option_id'] ?>][value]"
                                                                        readonly=""></td>
                <?php
            }
            if ($data['variant_option3']) {
                ?>
                <td class="variants_2" id='variants_2_<?= $i ?>'><input type="text"
                                                                        value="<?= $data['variant_option3'] ?>"
                                                                        name="jet_attributes[2][<?= $data['option_id'] ?>][value]"
                                                                        readonly=""></td>

            <?php }
            ?>


        </tr>

        <?php $i++;
    } ?>
    <!--    </tr>-->
    </tbody>
</table>
<script type="text/javascript">

    function checkselectedBeforeSubmit() {
        var sarr = [];
        var g = "";
        var addhtml = "";
        var dropdown_value = '';
        var jval = $('.sel').val();
        var jval = false;
        $('.sel').each(function () {
            dropdown_value = $(this).val();
            if (dropdown_value != '') {
                jval = true;
            }
        });
        j$('.v_success_msg').hide();
        if (!checkBlankInputsBeforeSubmit()) {
            addhtml = "Please fill all Shopify Option(s).";
            alert(addhtml);
            j$('.v_error_msg').html(addhtml);
            j$('.v_error_msg').show();
            return false;
        }

        if (!checkCommonAttributes()) {
            addhtml = "Please fill Common Attributes.";
            alert(addhtml);
            j$('.v_error_msg').html(addhtml);
            j$('.v_error_msg').show();
            return false;
        }
        if (jval) {
            return true;
        }
        var errorMsg = "Please map atleast one variant option with Newegg Attribute.";
        j$('.v_error_msg').html(errorMsg);
        alert(errorMsg);
        j$('.v_error_msg').show();
        return false;
    }
    function checkBlankInputsBeforeSubmit() {
        var parr = [];
        <?php foreach($shopifyattribues as $key=>$val){?>
        parr.push(".input_<?=$key?>");
        <?php }?>
        var isDisabled_new = "";
        var old_val = "";
        var new_val = "";
        var isDisabled_old = "";
        var retflag = false;
        for (k = 0; k < parr.length; k++) {
            j$(parr[k]).each(function (e) {
                isDisabled_new = "";
                new_val = "";
                isDisabled_old = "";
                old_val = "";
                isDisabled_new = j$(parr[k]).is(':disabled');
                new_val = j$(parr[k]).val();
                isDisabled_old = j$(parr[k]).parent().find('input[type=text]').eq(0).is(':disabled');
                old_val = j$(parr[k]).parent().find('input[type=text]').eq(0).val();
                if (isDisabled_new && isDisabled_old) {
                    retflag = true;
                    return false;

                } else {
                    if (isDisabled_new && old_val == "") {
                        retflag = true;
                        return false;
                    }
                    if (isDisabled_old && new_val == "") {
                        retflag = true;
                        return false;
                    }
                }
            });
            if (retflag) {
                return false;
            }
        }
        if (retflag) {
            return false;
        }
        return true;
    }
    function checkCommonAttributes() {
        var flag = true;
        j$('input.common_required').each(function () {
            if (j$(this).val() == '')
                flag = false;
        });

        j$('select.common_required').each(function () {
            if (j$(this).find(":selected").val() == '')
                flag = false;
        });
        return flag;
    }

    function checkOverrideFields() {
        var flag = true;

        var id_override_val = j$("select[name='product_id_override'] option:selected").val();
        var sku_override_val = j$("select[name='sku_override'] option:selected").val();

        if (id_override_val == sku_override_val && id_override_val == '1')
            flag = false;

        return flag;
    }

</script>
<script type="text/javascript">
    $('.sel').on('change', function () {
        var flag = false;
        var flag1 = false;
        var flag2 = false;
        var flag3 = false;
        var select0 = false;
        var select1 = false;
        var select2 = false;
        var sel_0 = $('#sel_0').val();
        var sel_1 = $('#sel_1').val();
        var sel_2 = $('#sel_2').val();
        if (typeof sel_0 != 'undefined') {
            if (sel_0 == (sel_1 || sel_2)) {
                flag = true;
            }
            else {
                select0 = true;
            }
        }
        if (typeof sel_1 != 'undefined') {
            if (sel_1 == (sel_0 || sel_2)) {
                flag = true;
            }
            else {
                select1 = true;
            }
        }
        if (typeof sel_2 != 'undefined') {
            if (sel_2 == (sel_0 || sel_1)) {
                flag = true;
            }
            else {
                select2 = true;
            }
        }
        if (flag) {
            var errorMsg = "Same value not mapped";
            j$('.v_error_msg').html(errorMsg);
            alert(errorMsg);
            j$('.v_error_msg').show();
            $(this).val('');
            var sel_0 = '';
            var sel_1 = '';
            var sel_2 = '';
            var flag = false;
            return false;
        }
        if ($(this).attr('id') == 'sel_0') {
            select1 = false;
            select2 = false;
        }
        if ($(this).attr('id') == 'sel_1') {
            select0 = false;
            select2 = false;
        }
        if ($(this).attr('id') == 'sel_2') {
            select1 = false;
            select0 = false;
        }
        if (select0) {
            var url = '<?= $urlAttributeValue; ?>';
            var category_id = '<?= $category_id;?>';
            var subcategory_id = '<?= $subcategory_id;?>';
            j$.ajax({
                method: "post",
                url: url,
                data: {subcategory_id: subcategory_id, category_id: category_id, name: sel_0, _csrf: csrfToken}
            })
                .done(function (data) {
                    if (data) {
                        $(".dropdown_0").remove();
                        $("#sel_0").prop("disabled", true);
                        var innerKey = 0;
                        $('.variants_0').append("<div class='wait'><img style='width:35px !important' src='<?= Yii::$app->request->baseUrl ?>/images/482.gif'><br>Loading..</div>");

                        function setDropdown(queue, innerKey) {
                            var input = $('#variants_0_' + innerKey + '').closest('tr').find('.sku').text();
                            var dropdown = $('<select />', {
                                class: 'dropdown_0',
                                name: 'newegg[<?= $model->shopify_product_type ?>][' + category_id + '][' + sel_0 + '][' + input + ']'
                            });
                            var predefined = '<?= $model->mapped_value_data ?>';
                            var jsonCheck = true;
                            try {
                                var objpredefined = JSON.parse(predefined);
                            }
                            catch (err) {
                                jsonCheck = false;
                            }
                            var productType = '<?= $model->shopify_product_type ?>';
                            var dropDownData = JSON.parse(queue);
                            dropdown.append($('<option />', {value: ''}).html('please select'));
                            $.each(dropDownData, function (key1, value1) {
                                if (jsonCheck && typeof objpredefined[productType][category_id][sel_0] != 'undefined' && objpredefined[productType][category_id][sel_0][input] != 'undefined' && objpredefined[productType][category_id][sel_0][input] == value1) {
                                    dropdown.append($('<option />', {
                                        value: value1,
                                        selected: 'selected'
                                    }).html(value1));
                                }
                                else {
                                    dropdown.append($('<option />', {value: value1}).html(value1));
                                }


                            });
                            $('#variants_0_' + innerKey + '').append(dropdown);
                            $('#variants_0_' + innerKey + ' .wait').remove();
                        }

                        function aSynchronousCall(queue, innerKey) {
                            if ($('#variants_0_' + innerKey + '').length <= 0) {
                                innerKey = 0;
                                $("#sel_0").prop("disabled", false);
                            }
                            else {
                                setTimeout(function () {
                                    setDropdown(queue, innerKey);
                                    aSynchronousCall(queue, innerKey + 1);
                                }, 500);
                            }
                        }

                        aSynchronousCall(data, innerKey);

                    }
                    else {
                        $(".dropdown_0").remove();
                    }

                });

        }
        if (select1) {
            var url = '<?= $urlAttributeValue; ?>';
            var category_id = '<?= $category_id;?>';
            var subcategory_id = '<?= $subcategory_id;?>';
            j$.ajax({
                method: "post",
                url: url,
                data: {subcategory_id: subcategory_id, category_id: category_id, name: sel_1, _csrf: csrfToken}
            })
                .done(function (data) {
                    if (data) {
                        $(".dropdown_1").remove();
                        $("#sel_1").prop("disabled", true);
                        var innerKey = 0;
                        $('.variants_1').append("<div class='wait'><img style='width:35px !important' src='<?= Yii::$app->request->baseUrl ?>/images/482.gif'><br>Loading..</div>");

                        function setDropdown(queue, innerKey) {
                            var input = $('#variants_1_' + innerKey + '').closest('tr').find('.sku').text();
                            var dropdown = $('<select />', {
                                class: 'dropdown_1',
                                name: 'newegg[<?= $model->shopify_product_type ?>][' + category_id + '][' + sel_1 + '][' + input + ']'
                            });
                            var dropDownData = JSON.parse(queue);
                            var predefined = '<?= $model->mapped_value_data ?>';
                            var jsonCheck = true;
                            try {
                                var objpredefined = JSON.parse(predefined);
                            }
                            catch (err) {
                                jsonCheck = false;
                            }
                            var productType = '<?= $model->shopify_product_type ?>';
                            dropdown.append($('<option />', {value: ''}).html('please select'));
                            $.each(dropDownData, function (key1, value1) {
                                if (jsonCheck && typeof objpredefined[productType][category_id][sel_1] != 'undefined' && objpredefined[productType][category_id][sel_1][input] != 'undefined' && objpredefined[productType][category_id][sel_1][input] == value1) {
                                    dropdown.append($('<option />', {
                                        value: value1,
                                        selected: 'selected'
                                    }).html(value1));
                                }
                                else {
                                    dropdown.append($('<option />', {value: value1}).html(value1));
                                }

                            });
                            $('#variants_1_' + innerKey + '').append(dropdown);
                            $('#variants_1_' + innerKey + ' .wait').remove();
                        }

                        function aSynchronousCall(queue, innerKey) {
                            if ($('#variants_1_' + innerKey + '').length <= 0) {
                                innerKey = 0;
                                $("#sel_1").prop("disabled", false);
                            }
                            else {
                                setTimeout(function () {
                                    setDropdown(queue, innerKey);
                                    aSynchronousCall(queue, innerKey + 1);
                                }, 500);
                            }
                        }

                        aSynchronousCall(data, innerKey);

                    }
                    else {
                        $(".dropdown_1").remove();
                    }

                });

        }
        if (select2) {
            var url = '<?= $urlAttributeValue; ?>';
            var category_id = '<?= $category_id;?>';
            var subcategory_id = '<?= $subcategory_id;?>';
            j$.ajax({
                method: "post",
                url: url,
                data: {subcategory_id: subcategory_id, category_id: category_id, name: sel_2, _csrf: csrfToken}
            })
                .done(function (data) {
                    if (data) {
                        $(".dropdown_2").remove();
                        $("#sel_2").prop("disabled", true);
                        var innerKey = 0;
                        $('.variants_2').append("<div class='wait'><img style='width:35px !important' src='<?= Yii::$app->request->baseUrl ?>/images/482.gif'><br>Loading..</div>");

                        function setDropdown(queue, innerKey) {
                            var input = $('#variants_2_' + innerKey + '').closest('tr').find('.sku').text();
                            var dropdown = $('<select />', {
                                class: 'dropdown_2',
                                name: 'newegg[<?= $model->shopify_product_type ?>][' + category_id + '][' + sel_2 + '][' + input + ']'
                            });
                            var predefined = '<?= $model->mapped_value_data ?>';
                            var jsonCheck = true;
                            try {
                                var objpredefined = JSON.parse(predefined);
                            }
                            catch (err) {
                                jsonCheck = false;
                            }
                            var productType = '<?= $model->shopify_product_type ?>';
                            var dropDownData = JSON.parse(queue);
                            dropdown.append($('<option />', {value: ''}).html('please select'));
                            $.each(dropDownData, function (key1, value1) {
                                if (jsonCheck && typeof objpredefined[productType][category_id][sel_2] != 'undefined' && objpredefined[productType][category_id][sel_2][input] != 'undefined' && objpredefined[productType][category_id][sel_2][input] == value1) {
                                    dropdown.append($('<option />', {
                                        value: value1,
                                        selected: 'selected'
                                    }).html(value1));
                                }
                                else {
                                    dropdown.append($('<option />', {value: value1}).html(value1));
                                }

                            });
                            $('#variants_2_' + innerKey + '').append(dropdown);
                            $('#variants_2_' + innerKey + ' .wait').remove();
                        }

                        function aSynchronousCall(queue, innerKey) {
                            if ($('#variants_2_' + innerKey + '').length <= 0) {
                                innerKey = 0;
                                $("#sel_2").prop("disabled", false);
                            }
                            else {
                                setTimeout(function () {
                                    setDropdown(queue, innerKey);
                                    aSynchronousCall(queue, innerKey + 1);
                                }, 500);
                            }
                        }

                        aSynchronousCall(data, innerKey);

                    }
                    else {
                        $(".dropdown_2").remove();
                    }

                });

        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        var sel_0 = $('#sel_0').val();
        var sel_1 = $('#sel_0').val();
        var sel_2 = $('#sel_0').val();
        if (sel_0 && sel_0 != '') {
            $('#sel_0').trigger("change");
        }
        if (sel_1 && sel_1 != '') {
            $('#sel_1').trigger("change");
        }
        if (sel_2 && sel_2 != '') {
            $('#sel_2').trigger("change");
        }
    });
</script>


