<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\AttributeMap;

$this->title = 'Shopify-Newegg Attribute Value Mapping';
$this->params['breadcrumbs'][] = $this->title;
$array = [];
//print_r($saveData);die;
?>
<div class="attribute-map-index content-section" id = "category">
    <form id="attribute_map" class="form new-section" method="post"
          action="<?= Data::getUrl('newegg-attributemap/save') ?>">
        <div class="jet-pages-heading">
            <h3 class="Jet_Products_style"><?= Html::encode($this->title) ?></h3>
            <div class="clear"></div>
        </div>
        <div class="jet-pages-heading">
        </div>
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>"
               value="<?= Yii::$app->request->csrfToken; ?>"/>
        <div class="grid-view table-responsive">
            <table class="table table-striped table-bordered ced-walmart-custome-tbl" id="mytable">
                <thead>
                <tr>
                    <th>Product Type(shopify)</th>
                    <th>
                        <div style="float:left; margin-left:10%">Mapped Value</div>
                        
                    </th>
                </tr>
                </thead>
                <tbody id='tbody'>
                <?php 
                if(!empty($saveData)){
                    foreach ($saveData as $key1 => $value1) {?>
                    <tr>
                        <td><?= $value1['shopify_product_type'];?></td>
                        <td><a href="#" class ="anchor" name="tab" data-index='<?= $value1['mapped_value_data']?>' onclick ="editData(this)" title='<?= $value1['shopify_product_type'].','.$value1['category_id'];?>'>view And edit</a></td>
                    </tr>

                    <?
                    $array[]=$value1['shopify_product_type'];
                }

                }
                if(count($categoryData)>1){
                ?>
                <tr>
                    <td id="category_dropdown">
                    <a id="ext-gen14" onclick='addDropdown()' title='Mapped Product Type' hidefocus="on" href="#" tabindex="1">
                        <img  id = "catimage" src="<?= Yii::$app->request->baseUrl ?>/frontend/modules/neweggmarketplace/assets/images/leaf.gif">Mapped Product Type(<?= count($categoryData)-count($array); ?>)
                    </a>
                    <select class="variant_attributes_selector jet_attributes_selector form-control sel" style = "display:none" name='mapped_category[category_name]' id = "dropdown">
                    <option value="">Please Select Category</option>
                    <?php 

                        foreach ($categoryData as $key => $value) {
                            if(!in_array($value['shopify_product_type'], $array)){
                            ?>
                            <option value="<?= $value['shopify_product_type'].','.$value['newegg_category_id'] ?>"><?= $value['shopify_product_type'] ?></option>
                        <?php }}}
                    ?>
                    </select>
                    </td>
                </tr>
                </tbody>
            </table>
     
        </div>
    </form>
</div>
<div class="container">
        <div class="modal fade mymodel" tabindex="-1" id="category_info" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" style="text-align: center;font-family: "Comic Sans MS";">Value Mapping</h4>
                </div>
                <div class="modal-body">
                     <div class="modal-footer Attrubute_html">
                          <div class="v_error_msg" style="display:none;"></div>
                          <div class="v_success_msg alert-success alert" style="display:none;"></div>
                          <?= Html::submitButton('Save', ['class' => 'btn btn-primary','id'=>'saveedit','onclick'=>'saveData()']) ?>
                          <button type="button" style="" class="btn btn-primary" id="descriptionClose" onclick="closecategory()" >Close </button>
                        </div>
                    <div class="newegg-product-form">
                          <?php $form = ActiveForm::begin([
                            'id' => 'jet_edit_form',
                            'action' => frontend\modules\neweggmarketplace\components\Data::getUrl('newegg-valuemap/save'),
                            'method'=>'post',
                        ]); ?>        
                                <div id = 'modifiersDiv'> 
                                </div>
                    <?php ActiveForm::end(); ?> 
                     
                    </div>
                </div>             
            </div>   
            </div>
            </div>
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
    var count = true;
    function showCategory(val,desc,edit=null) {
        $('#category_info').modal('show');;
        var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggmarketplace/newegg-valuemap/valuemap";
        var prev = val.replace(/\,+/g,'][');
        var arr = val.split(',');
        var fixed ='newegg';
         $.ajax({
            method: "POST",
            url: createmanufacturer,
            data: { val : arr[0] ,desc: desc },
            success:function(data){
                        var obj = JSON.parse(data);
                        var ul = $("<ul style='margin-left:20px'></ul>").attr("class",'category');
                        $.each(obj, function(key,value) {
                        if(edit){
                            var edj = JSON.parse(edit);
                            $.each(edj[arr[0]][arr[1]], function(ekey,evalue) {
                            if(ekey==value){
                                ul.append("<li id ="+ value +"><input type='checkbox'  checked='checked' class ='shopify' name='" +fixed+'['+prev+']'+'['+value+']'+"' value='"+val+','+value+"'/>"+ value + "<br/></li");
                                editDataHelper(edj[arr[0]][arr[1]],ul,val+','+value);
                            }   
                        });
                        }
                        else{
                            ul.append("<li id ="+ value +"><input type='checkbox'  class ='shopify' name='" +fixed+'['+prev+']'+'['+value+']'+"' value='"+val+','+value+"'/>" + value + "<br/></li");
                        } 
                        });
                        if(edit){
                            $(ul).appendTo('#modifiersDiv');    
                        }
                        else{
                            $(ul).appendTo('#modifiersDiv'); 
                        }
                        
                    }
       });

    }

      function closecategory() {
        $('#category').css('display', 'block');
        $('#category_info').modal('hide');;
        $( ".category").remove();
    }

    function addDropdown(){
        j$('#dropdown').css('display', 'block');

    }
    $('#dropdown').on('change',function(){
        var desc ='neweggcategoryattribute';
        showCategory($(this).val(),desc);

    });

    $('#category_info').on('change','.shopify',function(){
        if(this.checked){            
            var desc ="shopifyattribute";
            var val = $(this).val();
            var prev = val.replace(/\,+/g,'][');
            var arr = val.split(',');
            var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggmarketplace/newegg-valuemap/valuemap";
                var fixed ='newegg';
                 $.ajax({
                    method: "POST",
                    url: createmanufacturer,
                    data: { val : val ,desc: desc },
                    success:function(data){
                        var obj = JSON.parse(data);
                        var i = 1;
                        var ul = $("<ul style='margin-left:20px'></ul>").attr("class",arr[2]);
                        $.each(obj, function(key,value) {
                             ul.append("<li id="+arr[2]+value+"><input type='checkbox'  class ='shopify_attr' name='" +fixed+'['+prev+']'+'['+value+']'+"' value='"+val+','+value+','+i+"'/>" + value + "</li>");
                              i+=1;
                        }); 
                         $(ul).appendTo('#'+arr[2]+''); 
                    }
               });
        }
        else{
            var val = $(this).val();
            var arr = val.split(',');
            $( "."+arr[2]).remove();

        
     }
    
    });

    $('#category_info').on('change','.shopify_attr',function(){
        if(this.checked){ 
        var desc ="valuedata";
        var val = $(this).val();
        var prev = val.replace(/\,+/g,'][');
        var arr = val.split(',');
        var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggmarketplace/newegg-valuemap/valuemap";
            var fixed ='newegg';
             $.ajax({
                method: "POST",
                url: createmanufacturer,
                data: { val : val ,desc: desc },
                success:function(data){
                    var obj = JSON.parse(data);
                    var ul = $("<ul style='margin-left:20px'></ul>").attr("class",arr[2]+'_'+arr[3]);
                    $.each(obj, function(key,value) {
                        if(value.att_value!=null){
                            var label = "<b><label class= '"+arr[2]+'_'+arr[3]+"' name='" +fixed+'['+prev+']'+'['+value.att_value+']'+"'>" + value.att_value + "</label></b>";
                            var option_value = JSON.parse(value.option_value);
                            var dropdown = "<select name=\""+fixed+'['+prev+']'+'['+value.att_value+']'+"\" class= '"+arr[2]+'_'+arr[3]+"'>";
                            $.each(option_value, function(key1,value1) {
                                dropdown += '<option value="'+value1+'">'+value1+'</option>';
                            });
                            dropdown = dropdown+"</select>"; 
                            ul.append("<li>"+label+dropdown+"</li>");
                            $(ul).appendTo('#'+arr[2]+arr[3]+'');
                        } 
                    }); 
                }
           });        
     }
     else{
        var val = $(this).val();
        var arr = val.split(',');
        $( "."+arr[2]+'_'+arr[3]).remove();

     }
    
    });


    function saveData()
{
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var postData = j$("#jet_edit_form").serializeArray();
    var formURL = j$("#jet_edit_form").attr("action");
            $.ajax(
            {
                url : formURL,
                type: "POST",
                dataType: 'json',
                data : postData,
                _csrf : csrfToken,
                success:function(data, textStatus, jqXHR) 
                {        /*if(count){
            count=false;
            var mapper = mapData(arr[0]);
            console.log(mapper);
            var tr ="<tr><td>"+arr[0]+"</td><td><a href='#' class='anchor' name='tab' data-index="+mapper+" onclick='editData(this)' title="+arr[0]+','+arr[1]+">view And edit</a></td></tr>";
            $('#tbody').append(tr);
        }*/
                    console.log("kkkk");
                    console.log(postData.length);
                    j$('#LoadingMSG').hide();
                    if(data.success)
                    {   
                        j$('.v_success_msg').html('');
                        j$('.v_success_msg').append(data.success);
                        j$('.v_error_msg').hide();
                        j$('.v_success_msg').show();
                        
                    }
                    else
                    {
                        j$('.v_error_msg').html('');
                        j$('.v_error_msg').append(data.error);
                        j$('.v_success_msg').hide();
                        j$('.v_error_msg').show();
                        
                        
                    }
                },
              
            });
      
   
   
  
}

function editData(val)
{
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var desc ='neweggcategoryattribute';
    var title = $(val).attr('title'); 
    var data = $(val).attr('data-index'); 
    console.log(data);
    showCategory(title,desc,data);
}
function mapData(currentvalue){
      var desc ="mapData";
      var val = currentvalue;
      var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggmarketplace/newegg-valuemap/valuemap";
                 $.ajax({
                    method: "POST",
                    url: createmanufacturer,
                    data: { val : val ,desc: desc },
                    success:function(data){
                    return data;
                    }
               });

}


function editDataHelper(edit,ul,currentvalue){
           var desc ="shopifyattribute";
            var val = currentvalue;
            var prev = val.replace(/\,+/g,'][');
            var arr = val.split(',');
            var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggmarketplace/newegg-valuemap/valuemap";
                var fixed ='newegg';
                 $.ajax({
                    method: "POST",
                    url: createmanufacturer,
                    data: { val : val ,desc: desc },
                    success:function(data){
                        var obj = JSON.parse(data);
                        var i = 1;
                        var ul = $("<ul style='margin-left:20px'></ul>").attr("class",arr[2]);
                        $.each(obj, function(key,value) {
                            $.each(edit[arr[2]], function(ekey,evalue) {
                            if(ekey==value){
                             ul.append("<li id="+arr[2]+value+"><input type='checkbox'  checked='checked' class ='shopify_attr' name='" +fixed+'['+prev+']'+'['+value+']'+"' value='"+val+','+value+','+i+"'/>" + value + "</li>");
                             editAttributeHelper(edit[arr[2]],ul,val+','+value+','+i);
                              i+=1;
                               
                          }
                        });
                        }); 
                         $(ul).appendTo('#'+arr[2]+''); 
                    }
               });

}

function editAttributeHelper(edit,ul,currentvalue) {

        var desc ="valuedata";
        var val = currentvalue;
        var prev = val.replace(/\,+/g,'][');
        var arr = val.split(',');

        var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggmarketplace/newegg-valuemap/valuemap";
            var fixed ='newegg';
             $.ajax({
                method: "POST",
                url: createmanufacturer,
                data: { val : val ,desc: desc },
                success:function(data){
                    var obj = JSON.parse(data);
                    var ul = $("<ul style='margin-left:20px'></ul>").attr("class",arr[2]+'_'+arr[3]);
                    $.each(obj, function(key,value) {
                        if(value.att_value!=null){
                            var label = "<b><label class= '"+arr[2]+'_'+arr[3]+"' name='" +fixed+'['+prev+']'+'['+value.att_value+']'+"'>" + value.att_value + "</label></b>";
                            var option_value = JSON.parse(value.option_value);
                            var dropdown = "<select name=\""+fixed+'['+prev+']'+'['+value.att_value+']'+"\" class= '"+arr[2]+'_'+arr[3]+"'>";
                            $.each(option_value, function(key1,value1) {
                                $.each(edit[arr[3]][arr[4]],function(ekey,evalue)
                                {
                                    console.log(evalue);
                                    if(evalue==value1){
                                        dropdown += '<option selected ="selected" value="'+value1+'">'+value1+'</option>';  
                                    }
                                    else{
                                        dropdown += '<option value="'+value1+'">'+value1+'</option>';  
                                    }
                                    
                                });
                            });
                            dropdown = dropdown+"</select>"; 
                            ul.append("<li>"+label+dropdown+"</li>");
                            $(ul).appendTo('#'+arr[2]+arr[3]+'');

                        } 
                    }); 
                }
           });

}
</script>
