<?php
use yii\helpers\Html;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;
?> 
<?php if($model->category)
{
    $id=$model->category;
    ?> 
    <div class="form-group field-jetproduct-jet_attributes enable_api">
        <div class="category_value" style="margin-bottom:5px">
            <span>
                <?= $category_path; ?>
            </span>
            <input id="jetproduct-fulfillment_node" class="form-control" type="hidden" readonly="" value="<?= $id ?>" name="JetProduct[fulfillment_node]">
        </div>

        <!-- Code By Himanshu Start -->
        <?php
        //if($model->jet_product->type=='variants')
        {
            $common_attr_values = array();
            $common_attr_str = $model->common_attributes;
            if($common_attr_str != '')
                $common_attr_values = json_decode($common_attr_str,true);

            $common_attributes = array();
            if(is_array($common_required_attributes) && 
                count($common_required_attributes))
            {
                foreach ($common_required_attributes as $value) {
                    foreach($requiredAttrValues as $attr_val) {
                        $attr_temp = [];
                        if(is_array($value)) {
                            //$keys = array_keys($value);
                            //$attr_code = $keys[0];
                            $sub_temp = [];
                            foreach ($value as $key => $sub_attr) {
                                $attr_code = $key;
                                foreach ($sub_attr as $sub_attr_value) {
                                    $attr_value = '';
                                    if(isset($common_attr_values[$sub_attr_value]))
                                        $attr_value = $common_attr_values[$sub_attr_value];

                                    if(isset($attr_val[$sub_attr_value])) {
                                        $options = explode(',', $attr_val[$sub_attr_value]);
                                        $sub_temp[] = array('type'=>'select', 'name'=>$sub_attr_value, 'options'=>$options, 'value'=> $value);
                                    } else {
                                        $sub_temp[] = array('type'=>'text', 'name'=>$sub_attr_value, 'value'=> $attr_value);
                                    }
                                }
                            }
                            $attr_temp = ['name'=>$attr_code, 'type'=>'sub_attr', 'sub_fields'=>$sub_temp];
                        } else {
                            $attr_code = $value;

                            $attr_value = '';
                            if(isset($common_attr_values[$attr_code]))
                                $attr_value = $common_attr_values[$attr_code];
                            
                            if(isset($attr_val[$attr_code])){
                                $options = explode(',', $attr_val[$attr_code]);
                                $attr_temp = array('type'=>'select', 'name'=>$attr_code, 'options'=>$options, 'value'=> $attr_value);
                            } else {
                                $attr_temp = array('type'=>'text', 'name'=>$attr_code, 'value'=> $attr_value);
                            }
                        }
                        $common_attributes[] = $attr_temp;
                    }
                }
            }
        ?>

        <?php
            if(count($common_attributes))
            {
        ?>
                <div>
                    <table class="table table-striped table-bordered">
                      <thead>
                        <tr><td colspan="2"><b>Common Attributes </b></td></tr>
                      </thead>
                      <tbody>
        <?php
                foreach ($common_attributes as $attr) {
                    if($attr['type']=='text'){
        ?>
                        <tr>
                            <td style="width:50%"><?= ucfirst($attr['name']) ?></td>
                            <td  style="width:50%">
                                <input type="text" value="<?= $attr['value'] ?>" name="common_attributes[<?= $attr['name'] ?>]" class="form-control" />
                                <span class="text-validator"><?= $attr['name'] ?> attribute is required</span>
                            </td>
                        </tr>
        <?php
                    } elseif($attr['type']=='select') {
        ?>
                        <tr>
                            <td style="width:50%"><?= ucfirst($attr['name']) ?></td>
                            <td  style="width:50%">
                                <select name="common_attributes[<?= $attr['name'] ?>]" class="jet_attributes_selector form-control">
                    <?php
                        if(is_array($attr['options']) && count($attr['options'])) :
                            foreach ($attr['options'] as $option) {
                    ?>
                                    <option value="<?= $option ?>" <?php if($option==$attr['value']){echo 'selected="selected"';} ?>><?= $option ?></option>
                    <?php
                            }
                        endif;
                    ?>
                                </select>
                                <span class="text-validator"><?= $attr['name'] ?> attribute is required</span>
                            </td>
                        </tr>
        <?php
                    } elseif($attr['type']=='sub_attr') {
                        if(count($attr['sub_fields'])) {
        ?>
                        <tr>
                            <td style="width:50%"><?= ucfirst($attr['name']) ?></td>
                            <td  style="width:50%">
        <?php
                            foreach ($attr['sub_fields'] as $field) {
                                if($field['type']=='text') {
        ?>
                                    <label><?= ucfirst($field['name']) ?></label>
                                    <input type="text" value="<?= $field['value'] ?>" name="common_attributes[<?= $field['name'] ?>]" class="form-control" />
                                    <span class="text-validator"><?= $field['name'] ?> attribute is required</span>
        <?php
                                } elseif($field['type']=='select') {
        ?>
                                    <label><?= ucfirst($field['name']) ?></label>
                                    <select name="common_attributes[<?= $attr['name'] ?>]" class="jet_attributes_selector form-control">
        <?php
                                    if(is_array($field['options']) && count($field['options'])) :
                                        foreach ($field['options'] as $option) {
        ?>
                                        <option value="<?= $option ?>" <?php if($option==$field['value']){echo 'selected="selected"';} ?>><?= $option ?></option>
        <?php
                                        }
                                    endif;
        ?>
                                    </select>
        <?php
                                }
        ?>                      
        <?php
                            }
        ?>                  </td>
                        </tr>
        <?php
                        }
                    }
                }
        ?>
                      </tbody>
                    </table>
                </div>
        <?php
            }
        }
        ?>
        <!-- Code By HImanshu End -->

        <div id="jet_Attrubute_html" class="Attrubute_html">
            <?php
            if(count($attributes)>0 || count($optionalAttrValues)>0)
            {
                //$attributes=array();
                //$attributes=explode(',',$merchantCategory['jet_attributes']);
                unset($merchantCategory);
                if($model->jet_product->type=='variants')
                {

                    echo $this->render('varients', [
                        'model' => $model,
                        'attributes'=>$attributes,
                        'optional_attributes'=>$optional_attr,
                        'requiredAttrValues'=>$requiredAttrValues,
                        'optionalAttrValues'=>$optionalAttrValues
                    ]);
                }
                else
                {
                    echo $this->render('simple',[
                        'model'=>$model,
                        'attributes'=>$attributes,
                        'optional_attr'=>$optional_attr,
                        'requiredAttrValues'=>$requiredAttrValues,
                        'optionalAttrValues'=>$optionalAttrValues
                    ]);
                } 
                unset($connection);
            }    
            else
            {?>
                <span class="text-validator" style="font-size:14px;background-position: 0 4px;">
                No more attribute(s) are available for selected jet category.
                </span>
            <?php
            }   
            ?>
        </div>    
    </div>
    
<?php 
}?> 