<?php
use yii\helpers\Html;
use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\AttributeMap;

$this->title = 'Shopify-Newegg Attribute Mapping';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="attribute-map-index content-section">
    <form id="attribute_map" class="form new-section" method="post"
          action="<?= Data::getUrl('newegg-attributemap/save') ?>">
        <div class="jet-pages-heading">
            <h3 class="Jet_Products_style"><?= Html::encode($this->title) ?></h3>
            <input type="submit" value="Submit" class="btn btn-primary"/>

            <div class="clear"></div>
        </div>
        <div class="jet-pages-heading">
            <p class="legend-text">
                <span style="border: 1px solid #EFC959; padding: 0 5px; margin-right: 10px; color: #EFC959;" class="">Variant</span>
                <b>:</b>
                <span style="color: #333">Attribute Can be Used for Creating Variants on Newegg.</span>
            </p>
            <p class="legend-text">
                <span style="border: 1px solid red; padding: 0 5px; margin-right: 10px; color: red;" class="">Required</span>
                <b>:</b>
                <span style="color: #333">Attribute is Required on Newegg.</span>
            </p>
        </div>
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>"
               value="<?= Yii::$app->request->csrfToken; ?>"/>
         <div class="grid-view table-responsive">
            <table class="table table-striped table-bordered ced-walmart-custome-tbl">
                <thead>
                <tr>
                    <th>Product Type(shopify)</th>
                    <th>Newegg Category</th>
                    <th>
                        <div style="float:left; width: 80px; margin-left:5% ; margin-right: 5%">Newegg Attributes</div>
                        <div style="float: left; width: 100px; margin-left: 30%; text-align: center">Shopify Attribute</div>
                        <div style="float:right; width: 20px; margin-right:20%">Newegg Attribute Value</div>
                    </th>
                </tr>
                </thead>
                <tbody>

                <?php
                $count =0;
                foreach ($attributes as $key => $attribute) {
                    $shopifyoptions = [];
                    if (isset($attribute['shopify_attribute']) && count($attribute['shopify_attribute'])) {
                        foreach ($attribute['shopify_attribute'] as $shopify_attr) {
                            $shopifyoptions[] = $shopify_attr;

                        }

                    }


                    ?>
                    <tr>
                        <?php $name = $attribute['product_type']; ?>
                        
                        <td><?= $attribute['product_type'] ?></td>
                        <td><?= $attribute['neweggcanadategory'] ?></td>
                        <td colspan="1">

                            <table class="attrmap-inner-table">
                                <?php
                                foreach ($attribute['neweggattribute'] as $value) {
                                    $propertyname = [];
                                    ?>
                                    <tr>
                                        <td><?= $value['PropertyName'] ?>
                                        <!-- Here specify attribute property start -->
                                        <?php
                                        if($value['IsGroupBy']==1 && $value['IsRequired']==1){
                                             $var = "var"?>
                                        <p>
                                        <span style="border: 1px solid #EFC959; padding: 0 5px; margin-right: 10px; color: #EFC959" class="">Variant</span>
                                        </p>
                                        <p>
                                        <span style="border: 1px solid red; padding: 0 5px; margin-right: 10px; color: red" class="">Required</span>
                                        </p>
                                         <?php }

                                        else{
                                            if($value['IsGroupBy']==1){
                                             $var = "var"?>
                                            <p>
                                                <span style="border: 1px solid #EFC959; padding: 0 5px; margin-right: 10px; color: #EFC959" class="">Variant</span>
                                            <?php }
                                            else{?>
                                            </p>
                                            </p>
                                            <span style="border: 1px solid red; padding: 0 5px; margin-right: 10px; color: red" class="">Required</span>

                                                <?php }

                                        }
                                        ?>
                                        <!-- End Code -->
                                        </p></td>
                                        <td class="attrmap-inner-table-inner-td1">
                                            <?php foreach ($attribute['neweggattributevalue'] as $key => $item1) {
                                            if ($key == $value['PropertyName']) {
                                            foreach ($item1 as $items) {
                                            $mapped_value = '';
                                            if (isset($attribute['mapped_values'][$value['PropertyName']])) {
                                                foreach ($attribute['mapped_values'] as $key => $values) {
                                                    $propertyname[] = $key;
                                                }
                                                $valueType = $attribute['mapped_values'][$value['PropertyName']]['type'];

                                                $mapped_value = $attribute['mapped_values'][$value['PropertyName']]['value'];
                                            }

                                            if (!empty($items['PropertyValueList'])) {


                                            ?>

                                            <?php
                                            if (isset($attribute['shopify_attribute']) && $attribute['shopify_attribute']) {
                                                ?>
                                                <?php
                                                foreach ($attribute['shopify_attribute'] as $k => $v) {

                                                    ?>
                                                    <!-- <option
                                                                        value='<?= $v ?>'<?php if (in_array($shopifyoptions, explode(',', $mapped_value))) {

                                                        echo 'selected="selected"';
                                                    } ?>><?= $v ?></option> -->
                                                    <?php $class_name = str_replace(' ','',$name);
                                                            $class_name = str_replace("'","",$class_name); 
                                                    $class_name = preg_replace("([~!@#$%^&*()_+=`{}\[\]\|\\:;'<>,.\/? ])", "", $class_name);?>
                                                    <input type="checkbox" value="<?= $v ?>"
                                                           class = "<?= $class_name ?><?php if(isset($var) && !empty($var)){  echo $var;
                                                            }?>" name="newegg[<?= $name ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][map][]" <?php if (isset($attribute['mapped_Datas'][$value['PropertyName']])
                                                        && !empty($attribute['mapped_Datas'][$value['PropertyName']])
                                                    ) {
                                                        if (in_array($v, explode(',', $attribute['mapped_Datas'][$value['PropertyName']]))) {
                                                            echo 'checked="checked"';
                                                        }
                                                    }
                                                    ?> >
                                       

                                                    <label><?= $v ?></label>


                                                <?php }
                                                $var = '';
                                            }

                                            ?>


                                            <!--                                                            <select name="attribute_value">-->
                                            <!--                                                            ][-->
                                            <? //= $attribute['neweggcanadategoryId']?><!--]-->
                                        <?php if($value['IsGroupBy']==1 && $value['IsRequired']==1){?>
                                        <td class="attrmap-inner-table-inner-td2">
                                            <select class="form-control root"
                                                name="newegg[<?= trim($attribute['product_type'], ' ') ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][<?= AttributeMap::VALUE_TYPE_MIXED ?>]">
                                                <?php foreach ($items['PropertyValueList'] as $options) {
                                                    ?>
                                                    <option
                                                        value='<?= $options; ?>' <?php

                                                    if ($mapped_value == $options) {
                                                        echo 'selected="selected"';
                                                    } ?> ><?= $options ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>

                                        <?php }
                                        elseif($value['IsGroupBy']==1){ ?>
                                            <td class="attrmap-inner-table-inner-td2">
                                               <select class="form-control root"
                                                name="newegg[<?= trim($attribute['product_type'], ' ') ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][<?= AttributeMap::VALUE_TYPE_SHOPIFY ?>]">
                                                <?php foreach ($items['PropertyValueList'] as $options) {
                                                    ?>
                                                    <option
                                                        value='<?= $options; ?>' <?php

                                                    if ($mapped_value == $options) {
                                                        echo 'selected="selected"';
                                                    } ?> ><?= $options ?></option>
                                                <?php } ?>
                                             </select>
                                            </td>

                                       <?php }
                                        else{?>
                                          <td class="attrmap-inner-table-inner-td2">
                                            <select class="form-control root"
                                                name="newegg[<?= trim($attribute['product_type'], ' ') ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][<?= AttributeMap::VALUE_TYPE_NEWEGG ?>]">
                                                <?php foreach ($items['PropertyValueList'] as $options) {
                                                    ?>
                                                    <option
                                                        value='<?= $options; ?>' <?php
                                                    if ($mapped_value == $options) {
                                                        echo 'selected="selected"';
                                                    } ?> ><?= $options ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>

                                        <?php }

                                        } else {

                                            if (isset($shopifyoptions) && count($shopifyoptions)) {
                                                ?>
                                                <?php
                                                foreach ($shopifyoptions as $option) {
                                                    ?>
                                                    <input type="checkbox" value="<?= $option ?>"
                                                           name="newegg[<?= $attribute['product_type'] ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][map][]"<?php if (isset($attribute['mapped_Datas'][$value['PropertyName']])
                                                        && !empty($attribute['mapped_Datas'][$value['PropertyName']])
                                                    ) {
                                                        if (in_array($option, explode(',', $attribute['mapped_Datas'][$value['PropertyName']]))) {
                                                            echo 'checked="checked"';
                                                        }
                                                    }
                                                    ?> >
                                                    <label><?= $option ?></label>

                                                    <?php

                                                }
                                                ?>
                                                <?php
                                                if (isset($attribute['shopify_attribute']) && $attribute['shopify_attribute']) {
                                                    ?>
                                                    <?php
                                                    foreach ($attribute['shopify_attribute'] as $k => $v) {
                                                        ?>
                                                        <!--               <label><?= $v ?></label>
                                                                <input type="checkbox" value="<?= $v ?>" name="newegg[<?= $name ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][map][]"<?php if (isset($attribute['mapped_Datas'][$value['PropertyName']])
                                                            && !empty($attribute['mapped_Datas'][$value['PropertyName']])
                                                        ) {
                                                            if (in_array($v, explode(',', $attribute['mapped_Datas'][$value['PropertyName']]))) {
                                                                echo 'checked="checked"';
                                                            }
                                                        }
                                                        ?> > -->

                                                    <?php }
                                                } ?>

                                                <?php
                                            }
                                            ?>
                                            <td>
                                                <input type="text"
                                                       name="newegg[<?= trim($attribute['product_type'], ' ') ?>][<?= $attribute['neweggcanadategoryId'] ?>][<?= $value['PropertyName'] ?>][<?= AttributeMap::VALUE_TYPE_TEXT ?>]"
                                                       placeholder="enter data"
                                                       value="<?php if ($valueType == AttributeMap::VALUE_TYPE_TEXT && in_array($value['PropertyName'], $propertyname)) {
                                                           echo $mapped_value;
                                                       } ?>"/>
                                            </td>

                                            <?php
                                            /*die;*/
                                        }
                                        }

                                        }

                                        } ?>
                                        </td>
                                    </tr>

                                    <?php
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                            <script type="text/javascript">
                               var array<?= $count ?> = [];
                               var str<?= $count ?> = "<?= $name ?>"; 
                               var name<?= $count ?> = str<?= $count ?>.replace(/\s+/g,'');
                               var name<?= $count ?> = name<?= $count ?>.replace(/\'+/g,'');
                               var name<?= $count ?> = name<?= $count ?>.replace(/([~!@#$%^&*()_+=`{}\[\]\|\\:;'<>,.\/? ])+/g,''); 
                               $('.'+name<?= $count ?>+'var').ready(function () {
                                    $('.'+name<?= $count ?>+'var:checkbox:checked').each(function () {
                                        var sThisVal = (this.checked ? $(this).val() : "");
                                        array<?= $count ?>.push(sThisVal);
                                    });
                                     });
                                $('.'+name<?= $count ?>+'var').on('change',function(){
                                   var dropdown_value = $(this).val();
                                   if(this.checked){
                                        if($.inArray(dropdown_value, array<?= $count ?>) > -1) {
                                        var errorMsg = "Same value not mapped";
                                        j$('.v_error_msg').html(errorMsg);
                                        alert(errorMsg);
                                        j$('.v_error_msg').show();
                                        $(this).prop('checked', false);
                                        return false;
                                        }
                                        else{
                                           array<?= $count ?>.push(dropdown_value); 
                                        }
                                   }
                                   else{
                                    array<?= $count ?> = jQuery.grep(array<?= $count ?>, function(value) {
                                          return value != dropdown_value;
                                        });
                                    
                                   }
                             
                                   
                                });
                            </script>

                <?php $count++;} ?>

                </tbody>
            </table>
        </div>
    </form>
</div>
<style>
    .center, .cat_root {
        text-align: center;
    }

    .cat_root .form-control {
        display: inline-block;
    }
    .attrmap-inner-table-inner-td1{
        width: 268px;
        text-align: center;
    }
    .attrmap-inner-table-inner-td2{
        width: 268px;
        text-align: center;
    }


</style>
<script type="text/javascript">
    $(document).ready(function(){
        alert("ojjjj");
      /*  var attribute = <?=$attributeValue?>;
        var count = 0;
        $.each(attribute, function(ekey,evalue) {
            console.log(ekey);
            console.log(evalue);
            $('#'+count+'').append("<td>"+evalue.product_type+"</td><td>"+evalue.neweggcanadategory+"</td>");

            count++;
        }); */  
    });

</script>