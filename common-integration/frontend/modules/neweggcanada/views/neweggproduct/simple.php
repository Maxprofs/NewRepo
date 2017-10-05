<?php

$id = $model->newegg_category;
$product_type = $model->jet_product->type;
$sku = $model->jet_product->sku;
$merchant_id = $model->merchant_id;
$newegg_attributes = array();
$count = 1;
$newegg_attributes = json_decode($model->newegg_attributes, true);
$html = '';
?>
<table class="table table-striped table-bordered">

    <?php
    if (!empty($requiredAttrValues)) {
        foreach ($requiredAttrValues as $key1 => $value1) {
            foreach ($value1 as $key => $value) {
         //  print_r($value);
            $val = \frontend\modules\neweggcanada\components\categories\Categoryhelper::subcategoryAttributeValue($key1, $value['SubcategoryID'], $value['PropertyName']);

            ?>
            <tr>
                <td style="width:50%"><?php echo $value['PropertyName']; ?></td>
                <td style="width:50%">
                    <select name="newegg_product[<?= $value['PropertyName'] ?>]" style="width:auto"
                            class="form-control root">

                        <?php
                        if (isset($val['PropertyValueList']) && !empty($val['PropertyValueList'])) {
                            foreach ($val['PropertyValueList'] as $item) {
                               
                                if(count($newegg_attributes)>0){
                                

                                    ?>

                                        <option value="<?= $item ?>" <?php if(array_key_exists($value['PropertyName'], $newegg_attributes) && $newegg_attributes[$value['PropertyName']]==$item){ ?> selected ='selected' <?php } ?>><?= $item ?></option>
                                        <?php ;?>

                                        <?php
                                    
                                
                            }
                            else{
                                
                                echo '<option value='.$item.' selected="selected">'.$item.'</option>';
                            }

                            }
                        } 
                        else {
                            ?>
                            <input type="text" value="" name="newegg_product[<?= $value['PropertyName'] ?>]"
                                   class="form-control required"
                                   placeholder="Attribute Value" name=""/>
                            <span class="text-validator"><?= $value['PropertyName']; ?> attribute is required</span>
                            <?php
                        }
                        ?>
                    </select>

                </td>

            </tr>
        <?php }}
    } else {
        ?>
        <span class="text-validator" style="font-size:14px;background-position: 0 4px;">
                No  attribute(s) are available for selected Newegg category.
                </span>
        <?php
    }
    ?>

</table>
