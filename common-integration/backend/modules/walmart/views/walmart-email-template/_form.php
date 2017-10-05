<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\modules\reports\models\WalmartEmailTemplate;


/* @var $this yii\web\View */
/* @var $model backend\models\JetEmailTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="jet-email-template-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'template_title')->textInput(['maxlength' => true]) ?> 

    <?= $form->field($model, 'template_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'custom_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'show_on_admin_setting')->dropDownList(['0'=>'No','1'=>'Yes']) ?>
    <?php 
    //print_r(JetEmailTemplate::find()->all());die;
    echo '<div class="form-group field-jetemailtemplate-copy_template_from required">
		<label for="jetemailtemplate-copy_template_from" class="control-label">Copy Template from</label>
		<select name="JetEmailTemplate[copy_template_from]" class="form-control" id="jetemailtemplate-copy_template_from">
		<option value="">Select Template</option>';
    	foreach (WalmartEmailTemplate::find()->all() as $key => $value) {
    		    		echo '<option value='.$value->template_title.'>'.$value->template_title.'</option>';
    	}
    	echo '</select><div class="help-block"></div>
</div>';
     ?>
   
    <?= '<div class="form-group field-jetemailtemplate-html_content required"><label for="jetemailtemplate-html_content" class="control-label">Html Content</label><textarea  placeholder="write your html here" id="jetemailtemplate-html_content" name="JetEmailTemplate[html_content]" class="form-control"></textarea></div>' ;?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
         
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?= '<div id="previewitem"></div>'?>
<button  class="btn btn-primary" id="preview" type="">Preview</button>
<script src="jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#jetemailtemplate-copy_template_from').on('change',function(){
        var Name = $(this).val();
        if(Name){
            $.ajax({
                type:'POST',
                url:'htmltemplate',
                data:'name='+Name,
               success:function(html){
                    $('#jetemailtemplate-html_content').text(html); 
                }
            }); 
        }
    });
    
});

$(document).ready(function(){
    $('#preview').on('click',function(){
        var Html = $('#jetemailtemplate-html_content').val();
        $('#previewitem').html(Html);
    });
    
});
$(document).ready(function(){
    $('#walmartemailtemplate-template_title').on('blur',function(){
        var Html = $('#walmartemailtemplate-template_title').val();
        $('#walmartemailtemplate-template_path').val('email/'+Html+'.html');
    });
    
});

$(document).ready(function(){
	setTimeout(function(){ 
		var Name = $('#walmartemailtemplate-template_title').val();
        if(Name){
            $.ajax({
                type:'POST',
                url:'htmltemplate',
                data:'name='+Name,
               success:function(html){
                    $('#jetemailtemplate-html_content').text(html); 
               	
                }
            }); 
        }

	 }, 1000);
   /* $('#jetemailtemplate-template_title').blur(function(){
        

    });
    */
});
</script>
<style type="text/css">
    .field-walmartemailtemplate-template_path {
        display:none;
    }

</style>