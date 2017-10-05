<?php
use yii\helpers\Html;
use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\AttributeMap;


$this->title = 'Shopify-Newegg Variants Product  Attribute value Mapping';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="attribute-map-index content-section">
    <form id="attribute_map" method="post" class="form new-section"action="<?= Data::getUrl('variantsvaluemap/save') ?>">
        <div class="jet-pages-heading">
            <h3 class="Jet_Products_style"><?= Html::encode($this->title) ?></h3>
            <input type="submit" value="Submit" class="btn btn-primary"/>
            <div class="clear"></div>
        </div>
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>"
               value="<?= Yii::$app->request->csrfToken; ?>"/>
        <input type="hidden" name="id"
               value="<?= $id; ?>"/>
        <div class="grid-view">
            <table class="table table-striped table-bordered ced-walmart-custome-tbl responsive-table-wrap">
                <thead>
                <tr>
                    <th>Product Type(shopify)</th>
                    <th>Newegg Category</th>
                    <th>
                        <div class="pull-left newegg_attribute_value">Newegg Attributes</div>
                        <div class="pull-right newegg_attribute_value">Newegg Attribute Value</div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php 
                foreach ($data as $key => $value1) { 
                    $category = json_decode($value1['data']['newegg_data'],true);
                    ?>
                    <tr>
                    <td><?= $value1['data']['product_type'] ?></td>
                    <td><?= $category['category']['name'] ?></td>
                    <td colspan="1">
                            <table class="attrmap-inner-table">
                                <?php
                                if(isset($value1['value']) && !empty($value1['value'])){
                                foreach ($value1['value'] as $key1 => $value2) {
                                    ?>
                                    <tr>

                                        
                                        <?php 
                                        if(isset($value1['data'][$key1])){?>
                                        <td><?= $key1 ?></td>
                                        <?php
                                        $shopifyAttributes = $value1['data'][$key1];
                                        if(isset($value1['data']['mapped_data'])){
                                            $prevdata = $value1['data']['mapped_data'];
                                           if(isset($prevdata[$value1['data']['product_type']][$category['category']['id']][$key1])&& !empty($prevdata[$value1['data']['product_type']][$category['category']['id']][$key1])){
                                                $autoFilled = $prevdata[$value1['data']['product_type']][$category['category']['id']][$key1];
                                           }
                                        }
                                        
                                        $attribute = explode(',', $shopifyAttributes);
                                        $product_sku = explode(',',$value1['data']['option_sku']);
                                        $i=0;
                                        foreach ($attribute as $a => $v) {
                                        ?>
                                        <tr>
                                        <td><?php echo ($v.'('.$product_sku[$i].')'); ?></td>

                                        <td>
                                           <?php foreach ($value2 as $key2 => $value3) {
                                            ?>
                                                        <select  class="form-control root" name="newegg[<?= $value1['data']['product_type'] ?>][<?= $category['category']['id']?>][<?= $key1?>][<?= $product_sku[$i]?>]" id="attribute_value">
                                                            <option
                                                                        value=''>Please select value 
                                                            </option>
                                                            <?php
                                                            if(isset($value3['PropertyValueList']) && $value3['PropertyValueList']){
                                                                foreach ($value3['PropertyValueList'] as $key3 => $value4) {
                                                                    if(isset($autoFilled) && !empty($autoFilled) && isset($autoFilled[$product_sku[$i]]) && !empty($autoFilled[$product_sku[$i]])){
                                                                ?>
                                                            <option
                                                                        value='<?= $value4;?>' <?php if($value4 == $autoFilled[$product_sku[$i]]){ ?> selected ='selected' <?php } ?> ><?= $value4?>
                                                            </option>
                                                            <?php }
                                                            else{?>
                                                                <option value='<?= $value4?>'><?= $value4?></option>
                                                            <?php }
                                                            ?>
                                                            <?php } }}$i++;}}?>
                                                        </select>
                                        </td>
                                        </tr>
                                    </tr>

                                    <?php
                                }}
                                ?>
                            </table>
                        </td>
                </tr>
                <?php }


                ?>
                
        

                </tbody>
            </table>
        </div>
    </form>
</>
<style>
    .center, .cat_root {
        text-align: center;
    }

    .cat_root .form-control {
        display: inline-block;
    }
    .newegg_attribute_value {
        padding-left: 10%;
        padding-right: 10%;
    }

</style>
<!-- Not same value mapped -->
<script type="text/javascript">
    var array = [];
 $('.root').on('change',function(){
   var dropdown_value = $(this).val();
   if($.inArray(dropdown_value, array) > -1) {
        var errorMsg = "Same value not mapped";
        j$('.v_error_msg').html(errorMsg);
        alert(errorMsg);
        j$('.v_error_msg').show();
        $(this).val('');
        return false;
    }
   array.push(dropdown_value);
 });

</script>
