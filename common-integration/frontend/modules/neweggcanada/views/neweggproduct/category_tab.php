<?php
use yii\helpers\Html;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
use frontend\modules\neweggcanada\components\categories\Categoryhelper;
//use frontend\modules\neweggcanada\components\AttributeMap;
?>
<?php if($model->newegg_category)
{
    $id=$model->newegg_category;
    if(isset($requiredAttrValues[$id]))
        $comm_attr = $requiredAttrValues[$id];




    //            print_r($requiredAttrValues);die('sss');

    ?>
    <div class="form-group field-jetproduct-jet_attributes enable_api">
        <div class="category_value" style="margin-bottom:5px">
            <span>
                <?= $category_path; ?>
            </span>
            <input id="jetproduct-fulfillment_node" class="form-control" type="hidden" readonly="" value="<?= $id ?>" name="JetProduct[fulfillment_node]">
        </div>
        <?php if(!empty($comm_attr)) { ?>
        <div id="common_attributes_Wrapper">
                    <table class="table table-striped table-bordered">
                      <thead>
                        <tr><td colspan="2"><b>Common Attributes </b></td></tr>
                      </thead>
                      <tbody>

        <?php 
        $product_type = $model->jet_product->type;
        if($product_type=='variants'){
            foreach ($comm_attr as $key1 => $value1) {
            
                $attribute_value = Categoryhelper::subcategoryAttributeValue($id,$value1['SubcategoryID'],$value1['PropertyName']);
                $prevdata = json_decode($model->newegg_attributes,true);
                if(isset($attribute_value['PropertyValueList']) && $attribute_value['PropertyValueList'] && count($attribute_value['PropertyValueList'])>0) { ?>
                <tr>
                   <td style="width:50%"><?= $value1['PropertyName'] ?></td>
                    <td  style="width:50%">
                <select name="common_attributes[<?= $value1['PropertyName'] ?>]" class="form-control common_required">
                <?php foreach($attribute_value['PropertyValueList'] as $val) {

                    if(isset($prevdata[$value1['PropertyName']]) && !empty($prevdata[$value1['PropertyName']])){
                    ?>
                <option value="<?= $val ?>" <?php if($val == $prevdata[$value1['PropertyName']]){ ?> selected ='selected' <?php } ?> ><?= $val ?></option>
                    <?php }

                    else{?>
                         <option value="<?= $val ?>" ><?= $val ?></option>
                   <?php }


                    }?> </select>

                      </td></tr>
                

                <?php }
                else{?>
                     <tr>
                   <td style="width:50%"><?= ($value1['PropertyName']) ?></td>
                            <td  style="width:50%">
                <input type="text" value="" name="common_attributes[<?= $value1['PropertyName'] ?>]" class="form-control common_required" />
                                <span class="text-validator">

                      </td></tr>
                

                <?php
                }
            }}
        }?>
        <div id="jet_Attrubute_html" class="Attrubute_html">
            <?php
            if(count($attributes)>0 ) {

                if (!empty($requiredgroupby)  || !empty($requiredAttrValues) ) {
                    if ($model->jet_product->type == 'variants') {

                        echo $this->render('varient', [
                            'model' => $model,
                            'attributes' => $attributes,
                       'requiredAttrValues'=>$requiredAttrValues,
                        'requiredgroupby'=>$requiredgroupby,
                            'category_path'=>$category_path,
                        ]);
                    } else {
                        
                        echo $this->render('simple', [
                            'model' => $model,
                            'attributes' => $attributes,
                        'requiredAttrValues'=>$requiredAttrValues,
                            'category_path'=>$category_path,

                        ]);
                    }
                    unset($connection);
                }
                else
                {?>
                    <span class="text-validator" style="font-size:14px;background-position: 0 4px;">
                No  attribute(s) are available for selected Newegg category.
                </span>
                    <?php
                }
                ?>
                <?php
            }
            else
            {?>

                <span class="text-validator" style="font-size:14px;background-position: 0 4px;">
                No  attribute(s) are available for selected Newegg category.
                </span>
                <?php
            }
            ?>
        </div>
    </div>

    <?php
}?> 