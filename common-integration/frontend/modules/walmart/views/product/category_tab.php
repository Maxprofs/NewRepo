<?php
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\AttributeMap;

    $common_attr_values = [];
    $common_attr_str = $model->common_attributes;
    if($common_attr_str != '') {
        $common_attr_values = json_decode($common_attr_str,true);
    }

    $saved_attribute_mapping = AttributeMap::getAttributeMapValues($model->jet_product->product_type);
?>
    <div class="form-group field-jetproduct-jet_attributes enable_api">
        <div class="category_value" style="margin-bottom:5px">
            <span>
                <?= $category_path; ?>
                <input type="hidden" value="<?= $model->category ?>" name="WalmartProduct[category_id]" />
                <input type="hidden" value="<?= $model->parent_category ?>" name="WalmartProduct[parent_category_id]" />
            </span>
        </div>

<?php   
    if(count($requiredAttributes))
    {
?>
        <div id="common_attributes_Wrapper">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr><td colspan="2"><b>Common Attributes </b></td></tr>
                </thead>
                <tbody>
<?php
            foreach ($requiredAttributes as $requiredAttribute) {
                $savedValue = AttributeMap::getSavedAttributeValue($requiredAttribute, $common_attr_values, $saved_attribute_mapping);
?>
                    <tr>
                        <td style="width:50%"><?= ($requiredAttribute) ?></td>
                        <td style="width:50%">
<?php
                if(isset($attributeOptions[$requiredAttribute])) {
?>
                            <select name="WalmartProduct[common_attributes][<?= $requiredAttribute ?>]" class="form-control common_required" onblur="toggleConditionalAttributes(this)">
                                <option value=""></option>
<?php
                        $options = $attributeOptions[$requiredAttribute];
                        if(count($options)) {
                            foreach ($options as $option) {
?>
                                <option value="<?= $option ?>" <?php if($savedValue==$option){echo 'selected="selected"';} ?>><?= $option ?></option>
<?php
                            }
                        }
?>
                            </select>
                            <span class="text-validator">
                                <?= $requiredAttribute . ' attribute is required' ?>
                            </span>
<?php 
                }
                else {
?>
                            <input type="text" value="<?= $savedValue ?>" name="WalmartProduct[common_attributes][<?= $requiredAttribute ?>]" class="form-control common_required" onblur="toggleConditionalAttributes(this)" />
                            <span class="text-validator">
                                <?= $requiredAttribute . ' attribute is required' ?>
                            </span>
<?php
                }
            }
?>    
                </tbody>
            </table>
        </div>

<?php   
        if(Data::getConfigValue($merchant_id, 'advanced_attribute_form'))
        {
            $advanced_attributes = $advancedAttributes;

            if(count($advanced_attributes))
            {
                $skip_attrs = ['swatchImages', 'variantGroupId', 'variantAttributeNames', 'isPrimaryVariant', 'brand'];
?>
            <div class="">
                <table class="walmart-table table-striped table-bordered">
                    <thead>
                        <tr>
                            <td colspan="2">
                                <b>Walmart Advanced Attributes</b>
                                <span style="float: right; margin-right: 50px;" id="toggle_advanced_attr">
                                    <!-- <img height="30" width="30" src="<?= yii::$app->request->baseUrl.'/images/arrow_down.png' ?>"> -->
                                    <span id="open">open</span>
                                    <span id="close" style="display:none">close</span>
                                </span>
                            </td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="advanced_attributes_Wrapper" style="display: none;">
                <table class="walmart-table table-striped table-bordered">
                    <tbody>
            <?php   
                    foreach ($advanced_attributes as $advanced_attribute) 
                    {
                        $savedValue = AttributeMap::getSavedAttributeValue($advanced_attribute, $common_attr_values, $saved_attribute_mapping);

                        if(isset($attributeOptions[$advanced_attribute]))
                        {
            ?>
                            <tr>
                                <td style="width:50%"><?= $advanced_attribute ?></td>
                                <td style="width:50%">
                                    <select class="form-control" name="common_attributes[<?= $advanced_attribute ?>]">    
                                        <option value=""></option>
<?php   
                                    $options = $attributeOptions[$advanced_attribute];
                                    foreach ($options as $option) 
                                    {
?>
                                        <option value="<?= $option ?>" <?= ($savedValue==$option)?"selected='selected'":'' ?>><?= $option ?></option>
<?php                                   
                                    } 
?>
                                    </select>
                                </td>
                            </tr>
<?php
                        }
                        else
                        {
?>
                            <tr>
                                <td style="width:50%"><?= $advanced_attribute ?></td>
                                <td style="width:50%">
                                    <input type="text" name="common_attributes[<?= $advanced_attribute ?>]" placeholder="Attribute Value" class="form-control" value="<?= $savedValue ?>">
                                </td>
                            </tr>
<?php
                        }
                    } 
?>
                    </tbody>
                </table>
            </div>

            <script type="text/javascript">
            $(document).ready(function(){
                $("#toggle_advanced_attr").click(function(){
                    $(".advanced_attributes_Wrapper").slideToggle();
                    $("#open").toggle();
                    $("#close").toggle();
                });
            });
            </script>
<?php   
            }
        }
    }   
?>
    </div>

<script type="text/javascript">
    /*$(".common_required").on("blur", function() {
        //var tagName = $(this).prop("tagName");
        //var value = $(this).val();

        toggleConditionalAttributes();
    });*/

    $(document).ready(function(){
        toggleConditionalAttributes();
    });

    function toggleConditionalAttributes(element=null)
    {
        if(element != null)
        {
            var elementVal = j$(element).val().trim();
            if(elementVal == '') {
                addCommonRequiredError(element);
            } 
            else {
                removeCommonRequiredError(element);
            }
        }

        //array of conditional attributes
        var conditionalAttr = [];

<?php foreach ($conditionalAttributes as $conditionalAttribute) {

        if(isset($attributeOptions[$conditionalAttribute['code']])) {
            $conditionalAttribute['options'] = $attributeOptions[$conditionalAttribute['code']];
        }

        $conditionalAttribute['saved_value'] = AttributeMap::getSavedAttributeValue($conditionalAttribute['code'], $common_attr_values, $saved_attribute_mapping);
?>
        conditionalAttr.push({"<?= $conditionalAttribute['code'] ?>": <?= json_encode($conditionalAttribute) ?>});
<?php } ?>

        
        conditionalAttr.forEach(function (conditions) {
            $.each(conditions, function(attrCode, data) {
                //console.log(attrCode);
                var flag = true;
                $.each(data.conditions, function(index, condition) {
                    var conAttrCode = condition.attribute;
                    var conAttrVal = condition.value;
                    var filledValue = getAttributeValue(conAttrCode);

                    if(filledValue == conAttrVal) {
                        //console.log('add attribute');
                    }
                    else if(conAttrVal == 'null' && filledValue == '') {
                        //console.log('add attribute');
                    }
                    else {
                        //console.log('remove attribute');
                        flag = false;
                    }
                });

                if(flag) {
                    addConditionalAttribute(data);
                }
                else {
                    var attr_code = data.code;
                    removeCommonAttribute(attr_code);
                }

            });
        });
    }

    function isAttributeExist(attributeCode, type='input')
    {
        if(jQuery("#common_attributes_Wrapper "+ type +"[name='WalmartProduct[common_attributes]["+attributeCode+"]']").length) {
            return true;
        }
        else {
            return false;
        }
    }

    function getAttributeValue(attributeCode)
    {
        var value = '';

        if(isAttributeExist(attributeCode, 'input'))
        {
            value = jQuery("#common_attributes_Wrapper input[name='WalmartProduct[common_attributes]["+attributeCode+"]']").val();
        }
        else if(isAttributeExist(attributeCode, 'select'))
        {
            value = jQuery("#common_attributes_Wrapper select[name='WalmartProduct[common_attributes]["+attributeCode+"]']").val();
        }

        return value;
    }

    function addConditionalAttribute(attributeData)
    {
        var attrCode = attributeData.code;

        var savedAttrValue = attributeData.saved_value;

        if (attributeData.hasOwnProperty('options')) 
        {
            var options = attributeData.options;

            createSelectField(attrCode, options, savedAttrValue);
        }
        else
        {
            createTextField(attrCode, savedAttrValue);
        }
    }

    function removeCommonAttribute(requiredAttrCode)
    {
        if(isAttributeExist(requiredAttrCode, 'input'))
        {
            j$("#common_attributes_Wrapper input[name='WalmartProduct[common_attributes]["+requiredAttrCode+"]']").parents("tr").remove();
        }
        else if(isAttributeExist(requiredAttrCode, 'select'))
        {
            j$("#common_attributes_Wrapper select[name='WalmartProduct[common_attributes]["+requiredAttrCode+"]']").parents("tr").remove();
        }
    }

    function createTextField(attributeCode, attributeValue='')
    {
        if(!isAttributeExist(attributeCode, 'input'))
        {
            var reqAttrHtml = '<tr id="' + attributeCode + '">';
            reqAttrHtml += '<td style="width:50%">' + attributeCode + '</td>';
            reqAttrHtml += '<td style="width:50%">';
            reqAttrHtml += '<div>';
            reqAttrHtml += '<input type="text" class="common_required form-control" name="WalmartProduct[common_attributes][' + attributeCode + ']" value="' + attributeValue + '" onblur="toggleConditionalAttributes(this)" />';
            reqAttrHtml += '<span class="text-validator">' + attributeCode + ' attribute is required</span>';
            reqAttrHtml += '</div>';
            reqAttrHtml += '</td>';
            reqAttrHtml += '</tr>';

            $("#common_attributes_Wrapper tbody").append(reqAttrHtml);
        }
    }

    function createSelectField(attributeCode, options, attributeValue='')
    {
        if(!isAttributeExist(attributeCode, 'select'))
        {
            var reqAttrHtml = '<tr id="' + attributeCode + '">';
            reqAttrHtml += '<td style="width:50%">' + attributeCode + '</td>';
            reqAttrHtml += '<td style="width:50%">';
            reqAttrHtml += '<div>';
            reqAttrHtml += '<select class="common_required form-control" name="WalmartProduct[common_attributes][' + attributeCode + ']" onblur="toggleConditionalAttributes(this)">';
            reqAttrHtml += '<option value=""></option>';

            if(typeof options == 'object')
            {
                options.forEach(function (option) {
                    if (attributeValue == option)
                        reqAttrHtml += '<option selected="selected" value="' + option + '">' + option + '</option>';
                    else
                        reqAttrHtml += '<option value="' + option + '">' + option + '</option>';
                });
            }

            reqAttrHtml += '</select>';
            reqAttrHtml += '<span class="text-validator">' + attributeCode + ' attribute is required</span>';
            reqAttrHtml += '</div>';
            reqAttrHtml += '</td>';
            reqAttrHtml += '</tr>'; 

            $("#common_attributes_Wrapper tbody").append(reqAttrHtml);
        }
    }

    function addCommonRequiredError(element)
    {
        j$(element).css('border', 'solid 1px red');
        j$(element).next().css('color', 'red');
    }

    function removeCommonRequiredError(element)
    {
        j$(element).css('border', '');
        j$(element).next().css('color', '');
    }
</script>


<?php
    if($model->jet_product->type=='variants')
    {
?>
    <div id="jet_Attrubute_html" class="Attrubute_html enable_api">
<?php
            echo $this->render('varients', [
                'model' => $model, 
                'requiredAttributes' => $requiredAttributes, 
                'unitAttributes' => $unitAttributes, 
                'variantAttributes' => $variantAttributes, 
                'conditionalAttributes' => $conditionalAttributes, 
                'attributeOptions' => $attributeOptions,
                'saved_attribute_mapping' => $saved_attribute_mapping,
                'merchant_id' => $merchant_id,
                'common_attr_values' => $common_attr_values
            ]);
?>
    </div>
<?php
    }
?>