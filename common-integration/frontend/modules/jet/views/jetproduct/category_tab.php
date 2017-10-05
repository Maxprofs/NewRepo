<?php
use yii\helpers\Html;
use yii\captcha\Captcha;
use frontend\modules\jet\models\JetCategory;
use frontend\modules\jet\models\JetAttributes;
use yii\widgets\ActiveForm;
?> 

<?php if($model->fulfillment_node)
{
    $id=$model->fulfillment_node;
    if(!isset($connection)){
        $connection=Yii::$app->getDb();
    }
        $merchantCategory="";
        $merchantCategory = $connection->createCommand("SELECT `jet_attributes`,`title`,`parent_title`,`root_title` FROM `jet_category` WHERE category_id='".$id."'")->queryOne();
    ?> 
    <div class="form-group field-jetproduct-jet_attributes enable_api">
        <label class="control-label" for="jetproduct-fulfillment_node">Jet Category & Attributes</label>
        <div class="category_value" style="margin-bottom:5px">
            <span>
                <?= $merchantCategory['root_title'].'&nbsp
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                &nbsp'.$merchantCategory['parent_title'].'&nbsp
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                &nbsp'.$merchantCategory['title'] ?>
            </span>
            <input id="jetproduct-fulfillment_node" class="form-control" type="hidden" readonly="" value="<?= $id ?>" name="JetProduct[fulfillment_node]">
        </div>
        <div id="jet_Attrubute_html" class="Attrubute_html">
            <?php
            if($merchantCategory['jet_attributes'])
            {
                $attributes=array();
                $attributes=explode(',',$merchantCategory['jet_attributes']);
                unset($merchantCategory);
                if($model->type=='variants')
                {
                    /*$html=$this->render('varients1',array('model'=>$model,'connection'=>$connection,'attributes'=>$attributes),true);
                    var_dump($html);die;
                    return $html;*/
                    echo $this->render('varients', [
                        'model' => $model,
                        'attributes'=>$attributes,
                        'connection'=>$connection,
                    ]);
                }
                else
                    echo $this->render('simple',[
                        'model'=>$model,
                        'connection'=>$connection,
                        'attributes'=>$attributes,
                    ]);
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
<?php ActiveForm::end(); ?>
<?php /*

*/?>
<script type="text/javascript">
$("#jetproduct-upc").prop("readonly", false); 
$("#jetproduct-asin").prop("readonly", false);
disableSaveNUploadButtonForVariantSimpleOnLoadFirst(); 
$("#jetproduct-price").blur(function(){
            $(this).parent().find('span.jetproduct-price-error').remove();
            if($(this).hasClass('select_error')){
                $(this).removeClass('select_error');
            }
            $('#LoadingMSG').show();
            var va=$(this).val();
            var reg=/^\d+\.?\d+$/;///^\d*[.]?\d+/
            if(va == ""){
                $(this).after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
                $(this).addClass('select_error');
                disableSaveNUploadButtonForVariantSimple();
                $('#LoadingMSG').hide();
                return false;
            }
             if(!reg.test(va)) {
                    $(this).after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
                    $(this).addClass('select_error');
                    //$(this).val("");
                    
             }
             disableSaveNUploadButtonForVariantSimple();
             $('#LoadingMSG').hide();
});
$("#jetproduct-qty").blur(function(){
                        $(this).parent().find('span.jetproduct-qty-error').remove();
                        if($(this).hasClass('select_error')){
                            $(this).removeClass('select_error');
                        }
                        $('#LoadingMSG').show();
                        
                        var va=$(this).val();
                        var reg=/^\d+$/;///^\d*[.]?\d+/
                        if(va == ""){
                            $(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
                            $(this).addClass('select_error');
                            disableSaveNUploadButtonForVariantSimple();
                            $('#LoadingMSG').hide();
                            return false;
                        }
                         if(!reg.test(va)) {
                                $(this).after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
                                $(this).addClass('select_error');
                                //$(this).val("");
                                
                         }
                         disableSaveNUploadButtonForVariantSimple();
                         $('#LoadingMSG').hide();
});
$("#jetproduct-asin").blur(function(){
            $(this).parent().find('span.jetproduct-asin-error').remove();
            if($(this).hasClass('select_error')){
                $(this).removeClass('select_error');
            }
            var upc_value=$("#jetproduct-upc").val();
            if(upc_value==""){
                    $("#jetproduct-upc").removeClass('select_error');
                    $("#jetproduct-upc").parent().find('span.jetproduct-upc-error').remove();
            }
            $('#LoadingMSG').show();
            
            var va=$(this).val();
            var reg=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
            if(va == ""){
                if(upc_value==""){
                    $(this).addClass('select_error');
                    $(this).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
                }
                disableSaveNUploadButtonForVariantSimple();
                $('#LoadingMSG').hide();
                return false;
            }
            if(!reg.test(va)) {
                    $(this).after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
                    //$(this).val("");
                    $(this).addClass('select_error');
                    disableSaveNUploadButtonForVariantSimple();
                    $('#LoadingMSG').hide();
                    return false;
            }
            var product_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
            var product_sku=$("#jetproduct-sku").val();
            var asin_flag=false;
            asin_flag=checkASINfromDbforVariantSimple(this,va,product_id,product_sku);
            if(!asin_flag){
                disableSaveNUploadButtonForVariantSimple();
                $('#LoadingMSG').hide();
                return false;
            }
            disableSaveNUploadButtonForVariantSimple();
            $('#LoadingMSG').hide();
            
});
function checkASINfromDbforVariantSimple(ele,eleval,product_id,product_sku){
        var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
        var var_flag=true;
             $.ajax({
                  async:false,
                  method: "GET",
                  url: url,
                  dataType:'json',
                  data: { product_asin: eleval,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
                })
                .done(function( msg ) {
                    if(msg.success){
                            $(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
                            $(ele).addClass('select_error');
                            //$(ele).val("");
                            //$('#savenuploadbutton').prop('disabled', true);
                            var_flag=false;
                            return false;
                    }
                   
                }); 
                if(var_flag){
                    return true;
                }else{
                    return false;
                }
}
function checkASINfromDbforVariantSimpleOnLoad(ele,eleval,product_id,product_sku){
        var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
        var var_flag=true;   
             $.ajax({
                  async:false,
                  method: "GET",
                  url: url,
                  dataType:'json',
                  data: { product_asin: eleval,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
                })
                .done(function( msg ) {
                    if(msg.success){
                            //$(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already taken.Please use other ASIN.</span>');
                            //$(ele).val("");
                            //$('#savenuploadbutton').prop('disabled', true);
                            var_flag=false;
                            return false;
                    }
                   
                }); 
                if(var_flag){
                    return true;
                }else{
                    return false;
                }
}
function checkASINfromDbforVariantSimpleOnLoadFirst(ele,eleval,product_id,product_sku){
        var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkasin'])?>";
        var var_flag=true;   
             $.ajax({
                  async:false,
                  method: "GET",
                  url: url,
                  dataType:'json',
                  data: { product_asin: eleval,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
                })
                .done(function( msg ) {
                    if(msg.success){
                            $(ele).after('<span class="jetproduct-asin-error error-msg">Entered ASIN is already exists.Please enter unique ASIN.</span>');
                            $(ele).addClass('select_error');
                            //$(ele).val("");
                            //$('#savenuploadbutton').prop('disabled', true);
                            var_flag=false;
                            return false;
                    }
                   
                }); 
                if(var_flag){
                    return true;
                }else{
                    return false;
                }
}
$("#jetproduct-upc").blur(function(){
            $(this).parent().find('span.jetproduct-upc-error').remove();
            if($(this).hasClass('select_error')){
                $(this).removeClass('select_error');
            }
            var asin_value=$("#jetproduct-asin").val();
            if(asin_value==""){
                    $("#jetproduct-asin").removeClass('select_error');
                    $("#jetproduct-asin").parent().find('span.jetproduct-asin-error').remove();
            }
            $('#LoadingMSG').show();
            var va=$(this).val();
            $(this).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
            $(this).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
            $(this).parent().find('select').prop('disabled', true);
            $(this).parent().find('select').css('display', 'none');
            $(this).prop('style',"");
            $(this).css({'width':'25%'});
            //$(this).css({'width':'59%','margin-right': '7px','display':'inline-block','float':'left','padding-left':'4px','padding-right':'4px'});
            if(va == ""){
                if(asin_value==""){
                    $(this).addClass('select_error');
                    $(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
                }
                $(this).parent().find('.jetproduct-upc_opt_type').val("");
                disableSaveNUploadButtonForVariantSimple();
                $('#LoadingMSG').hide();
                return false;
            }
            if(!fillBarcodeType()){
                    $(this).parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
                    $(this).addClass('select_error');
                    $(this).parent().find('.jetproduct-upc_opt_type').val("");
                    disableSaveNUploadButtonForVariantSimple();
                    $('#LoadingMSG').hide();
                    return false;
            }
            var product_id=$(this).parent().find('label.general_product_id').text();
            var product_sku=$("#jetproduct-sku").val();
            var current_upc_type="";
            if($(this).parent().find('select').length >0 && $(this).parent().find('select').is(':enabled')){
                    current_upc_type=$(this).parent().find('select').val();
            }
            if($(this).parent().find('.jetproduct-upc_opt_type').length >0 && $(this).parent().find('.jetproduct-upc_opt_type').is(':enabled')){
                    current_upc_type=$(this).parent().find('.jetproduct-upc_opt_type').val();
            }
            var upc_flag=false;
            upc_flag=checkUPCfromDbforVariantSimple(this,va,product_id,product_sku,current_upc_type);
            if(!upc_flag){
                disableSaveNUploadButtonForVariantSimple();
                $('#LoadingMSG').hide();
                return false;
            }
            disableSaveNUploadButtonForVariantSimple();
            $('#LoadingMSG').hide();
            
});
function checkUPCfromDbforVariantSimple(ele,upc,product_id,product_sku,upc_type){
        var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
        var var_flag=true;
             $.ajax({
                  async:false,
                  method: "GET",
                  url: url,
                  dataType:'json',
                  data: { product_upc: upc,barcode_type:upc_type,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
                })
                .done(function( msg ) {
                    if(msg.success){
                            $(ele).parent().append('<span class="jetproduct-upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
                            $(ele).addClass('select_error');
                            //$(ele).val("");
                            //$(ele).parent().find('.jetproduct-upc_opt_type').val("");
                            //$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
                            //$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
                            //$(ele).parent().find('select').prop('disabled', true);
                            //$(ele).parent().find('select').css('display', 'none');
                            //$(ele).prop('style',"");
                            //$(ele).css({'width':'25%'});
                            var_flag=false;
                            return false;
                    }
                   
                }); 
                if(var_flag){
                    return true;
                }else{
                    return false;
                }
}
function checkUPCfromDbforVariantSimpleOnLoad(ele,upc,product_id,product_sku,upc_type){
        var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
        var var_flag=true;
             $.ajax({
                  async:false,
                  method: "GET",
                  url: url,
                  dataType:'json',
                  data: { product_upc: upc,barcode_type:upc_type,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
                })
                .done(function( msg ) {
                    if(msg.success){
                            //$(ele).after('<span class="upc-error error-msg">Entered UPC is already taken.Please use other UPC.</span>');
                            //$(ele).val("");
                            //$(ele).parent().find('.jetproduct-upc_opt_type').val("");
                            //$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
                            //$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
                            //$(ele).parent().find('select').prop('disabled', true);
                            //$(ele).parent().find('select').css('display', 'none');
                            //$(ele).prop('style',"");
                            //$(ele).css({'width':'25%'});
                            var_flag=false;
                            return false;
                    }
                   
                }); 
                if(var_flag){
                    return true;
                }else{
                    return false;
                }
}
function checkUPCfromDbforVariantSimpleOnLoadFirst(ele,upc,product_id,product_sku,upc_type){
        var url="<?=\yii\helpers\Url::toRoute(['jetcatattribute/checkupc'])?>";
        var var_flag=true;
             $.ajax({
                  async:false,
                  method: "GET",
                  url: url,
                  dataType:'json',
                  data: { product_upc: upc,barcode_type:upc_type,product_sku : product_sku,product_id:product_id,type:'variant-simple', _csrf : csrfToken }
                })
                .done(function( msg ) {
                    if(msg.success){
                            $(ele).parent().append('<span class="jetproduct-upc-error error-msg">Entered Barcode is already exists.Please enter unique Barcode(UPC,ISBN-10,ISBN-13,EAN or GTIN-14).</span>');
                            $(ele).addClass('select_error');
                            //$(ele).val("");
                            //$(ele).parent().find('.jetproduct-upc_opt_type').val("");
                            //$(ele).parent().find('.jetproduct-upc_opt_type').prop('disabled', false);
                            //$(ele).parent().find('.jetproduct-upc_opt_type').css('display', 'none');
                            //$(ele).parent().find('select').prop('disabled', true);
                            //$(ele).parent().find('select').css('display', 'none');
                            //$(ele).prop('style',"");
                            //$(ele).css({'width':'25%'});
                            var_flag=false;
                            return false;
                    }
                   
                }); 
                if(var_flag){
                    return true;
                }else{
                    return false;
                }
}
function disableSaveNUploadButtonForVariantSimpleOnLoadFirst(){
        var disable_flag=false;
        if ($(".error-msg")[0]){
            $('#savenuploadbutton').prop('disabled', true);
                    disable_flag=true;
                    return false;
        }
        if(disable_flag){
                    return false;
        }
        
        var va_price=$( "#jetproduct-price" ).val();
        var reg_price=/^\d+\.?\d+$/;///^\d*[.]?\d+/
        if(va_price ==""){
            $("#jetproduct-price").after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
            $("#jetproduct-price").addClass('select_error');
            $('#savenuploadbutton').prop('disabled', true);
            disable_flag=true;
            return false;
        }
        if(!reg_price.test(va_price)) {
                $("#jetproduct-price").after('<span class="jetproduct-price-error error-msg">Invalid Price.</span>');
                $("#jetproduct-price").addClass('select_error');
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
        }
        
        if(disable_flag){
                return false;
        }
        
        var upc_flag=false;
        var asin_flag=false;
        var va_upc=$( "#jetproduct-upc" ).val();
        if(va_upc ==""){
            upc_flag=true;
        }
        if(va_upc !=""){
            if(!fillBarcodeType()) {
                $("#jetproduct-upc").parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
                $("#jetproduct-upc").addClass('select_error');
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                upc_flag=true;
                return false;
            }
            var product_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
            var product_sku=$("#jetproduct-sku").val();
            var current_upc_type="";
            if($("#jetproduct-upc").parent().find('select').length >0 && $("#jetproduct-upc").parent().find('select').is(':enabled')){
                current_upc_type=$("#jetproduct-upc").parent().find('select').val();
            }
            if($("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && $("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
                current_upc_type=$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
            }
            var upc_db_flag=false;
            upc_db_flag=checkUPCfromDbforVariantSimpleOnLoadFirst("#jetproduct-upc",va_upc,product_id,product_sku,current_upc_type);
            if(!upc_db_flag){
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                upc_flag=true;
                return false;
            }
        }
        var va_asin=$( "#jetproduct-asin" ).val();
        var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
        if(va_asin ==""){
                asin_flag=true;
        }
        if(va_asin !=""){
            if(!reg_asin.test(va_asin)){
                $("#jetproduct-asin").after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
                $("#jetproduct-asin").addClass('select_error');
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
            }
            var product_asin_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
            var product_asin_sku=$("#jetproduct-sku").val();
            var asin_db_flag=false;
            asin_db_flag=checkASINfromDbforVariantSimpleOnLoadFirst("#jetproduct-asin",va_asin,product_asin_id,product_asin_sku);
            if(!asin_db_flag){
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
            }
        }
        if(asin_flag && upc_flag){
            $("#jetproduct-upc").addClass('select_error');
            $("#jetproduct-asin").addClass('select_error');
            $("#jetproduct-asin").after('<span class="jetproduct-asin-error error-msg">Invalid ASIN.Maximum ASIN length - 10 (in alphanumeric).</span>');
            $("#jetproduct-upc").parent().append('<span class="jetproduct-upc-error error-msg">Invalid Barcode.Maximum length : UPC - 12,ISBN-10 - 10,ISBN-13 - 13,EAN - 13,GTIN-14 - 14 (in digits).</span>');
            $('#savenuploadbutton').prop('disabled', true);
            disable_flag=true;
            return false;
        }
        if(disable_flag){
            return false;
        }
        
        var va_qty=$( "#jetproduct-qty" ).val();
        var reg_qty=/^\d+$/;///^\d*[.]?\d+/
        if(va_qty ==""){
            $("#jetproduct-qty").addClass('select_error');
            $("#jetproduct-qty").after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
            $('#savenuploadbutton').prop('disabled', true);
            disable_flag=true;
            return false;
        }
        if(!reg_qty.test(va_qty)) {
                $("#jetproduct-qty").addClass('select_error');
                $("#jetproduct-qty").after('<span class="jetproduct-qty-error error-msg">Invalid Inventory.</span>');
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
        }
        
        if(disable_flag){
            return false;
        }
        $('#savenuploadbutton').prop('disabled',false);
        return false;
}
function disableSaveNUploadButtonForVariantSimple(){
        var disable_flag=false;
        if ($(".error-msg")[0]){
            $('#savenuploadbutton').prop('disabled', true);
                    disable_flag=true;
                    return false;
        }
        if(disable_flag){
                    return false;
        }
        
        var va_price=$( "#jetproduct-price" ).val();
        var reg_price=/^\d+\.?\d+$/;///^\d*[.]?\d+/
        if(va_price ==""){
            $('#savenuploadbutton').prop('disabled', true);
            disable_flag=true;
            return false;
        }
        if(!reg_price.test(va_price)) {
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
        }
        
        if(disable_flag){
                return false;
        }
        
        var upc_flag=false;
        var asin_flag=false;
        var va_upc=$( "#jetproduct-upc" ).val();
        if(va_upc ==""){
            upc_flag=true;
        }
        if(va_upc !=""){
            if(!fillBarcodeType()) {
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                upc_flag=true;
                return false;
            }
            var product_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
            var product_sku=$("#jetproduct-sku").val();
            var current_upc_type="";
            if($("#jetproduct-upc").parent().find('select').length >0 && $("#jetproduct-upc").parent().find('select').is(':enabled')){
                current_upc_type=$("#jetproduct-upc").parent().find('select').val();
            }
            if($("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').length >0 && $("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').is(':enabled')){
                current_upc_type=$("#jetproduct-upc").parent().find('.jetproduct-upc_opt_type').val();
            }
            var upc_db_flag=false;
            upc_db_flag=checkUPCfromDbforVariantSimpleOnLoad("#jetproduct-upc",va_upc,product_id,product_sku,current_upc_type);
            if(!upc_db_flag){
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                upc_flag=true;
                return false;
            }
        }
        var va_asin=$( "#jetproduct-asin" ).val();
        var reg_asin=/^[A-Za-z0-9]{10}$/;///^\d*[.]?\d+/
        if(va_asin ==""){
                asin_flag=true;
        }
        if(va_asin !=""){
            if(!reg_asin.test(va_asin)){
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
            }
            var product_asin_id=$("#jetproduct-upc").parent().find('label.general_product_id').text();
            var product_asin_sku=$("#jetproduct-sku").val();
            var asin_db_flag=false;
            asin_db_flag=checkASINfromDbforVariantSimpleOnLoad("#jetproduct-asin",va_asin,product_asin_id,product_asin_sku);
            if(!asin_db_flag){
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
            }
        }
        if(asin_flag && upc_flag){
            $('#savenuploadbutton').prop('disabled', true);
            disable_flag=true;
            return false;
        }
        if(disable_flag){
            return false;
        }
        
        var va_qty=$( "#jetproduct-qty" ).val();
        var reg_qty=/^\d+$/;///^\d*[.]?\d+/
        if(va_qty ==""){
            $('#savenuploadbutton').prop('disabled', true);
            disable_flag=true;
            return false;
        }
        if(!reg_qty.test(va_qty)) {
                $('#savenuploadbutton').prop('disabled', true);
                disable_flag=true;
                return false;
        }
        
        if(disable_flag){
            return false;
        }
        $('#savenuploadbutton').prop('disabled',false);
        return false;
}
 </script>
