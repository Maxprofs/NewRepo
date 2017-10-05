<?php
use frontend\modules\jet\models\JetCategory;
use yii\widgets\ActiveForm;

$stack='';
$categortObj=new JetCategory;
$label="Select jet attribute";
?> 
<div class="form-group field-jetproduct-jet_attributes enable_api">
 <?php if($model->type!='simple'){?>
    <label class="control-label" for="jetproduct-jet_attributes">
   		<?php echo "Shopify Product Varient(s)";?>
  	</label>
 <?php }?>
    <div id="jet_Attrubute_html" class="Attrubute_html"></div>    
</div>    
 <?php ActiveForm::end(); ?>
