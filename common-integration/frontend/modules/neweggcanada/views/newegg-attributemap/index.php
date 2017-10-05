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
$scriptArray=json_encode($attributes);
foreach ($attributes as $key => $attribute) {
   $shopifyoptions = [];
   if (isset($attribute['shopify_attribute']) && count($attribute['shopify_attribute'])) {
      foreach ($attribute['shopify_attribute'] as $shopify_attr) {
         $shopifyoptions[] = $shopify_attr;
         
      }
      
   }

   ?>
   <tr id="<?=$count?>">
   <?php $name = $attribute['product_type']; ?>
   
   <td><?= $attribute['product_type'] ?></td>
   <td><?= $attribute['neweggcategory'] ?></td>
   <td colspan="1">
   
   <table class="attrmap-inner-table">
   <?php
    $attributeCount=0;
   foreach ($attribute['neweggattribute'] as $value) {
      $propertyname = [];
      ?>
      <tr id='col<?=$attributeCount?>'>
      <td><?= $value['PropertyName'] ?>
      <!-- Here specify attribute property start -->
      <?php
      if($value['IsGroupBy']==1 && $value['IsRequired']==1)
      {
         $var = "var"?>
         <p>
         <span style="border: 1px solid #EFC959; padding: 0 5px; margin-right: 10px; color: #EFC959" class="">Variant</span>
         </p>
         <p>
         <span style="border: 1px solid red; padding: 0 5px; margin-right: 10px; color: red" class="">Required</span>
         </p>
         <?php
      }
      
      else{
         if($value['IsGroupBy']==1){
            $var = "var"?>
            <p><span style="border: 1px solid #EFC959; padding: 0 5px; margin-right: 10px; color: #EFC959" class="">Variant</span>
            <?php
         }
         else{?>
            </p></p><span style="border: 1px solid red; padding: 0 5px; margin-right: 10px; color: red" class="">Required</span> <?php
         }
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
               if (!empty($items['PropertyValueList']))
               {?>
                  <?php
                  if (isset($attribute['shopify_attribute']) && $attribute['shopify_attribute']) {
                     ?>
                     <?php
                     foreach ($attribute['shopify_attribute'] as $k => $v) {
                        ?>
                        <?php $class_name = str_replace(' ','',$name);
                        $class_name = str_replace("'","",$class_name);
                        $class_name = preg_replace("([~!@#$%^&*()_+=`{}\[\]\|\\:;'<>,.\/? ])", "", $class_name);?>
                        <input type="checkbox" value="<?= $v ?>"
                        class = "<?= $class_name ?><?php if(isset($var) && !empty($var)){  echo $var;
                        }?>" name="newegg[<?= $name ?>][<?= $attribute['neweggcategoryId'] ?>][<?= $value['PropertyName'] ?>][map][]" <?php if (isset($attribute['mapped_Datas'][$value['PropertyName']])
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
                  <?php
               }
               else
               {
                  if (isset($shopifyoptions) && count($shopifyoptions)) {
                     ?>
                     <?php
                     foreach ($shopifyoptions as $option) {
                        ?>
                        <input type="checkbox" value="<?= $option ?>"
                        name="newegg[<?= $attribute['product_type'] ?>][<?= $attribute['neweggcategoryId'] ?>][<?= $value['PropertyName'] ?>][map][]"<?php if (isset($attribute['mapped_Datas'][$value['PropertyName']])
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
                  }
                  ?>
                  <td>
                  <input type="text"
                  name="newegg[<?= trim($attribute['product_type'], ' ') ?>][<?= $attribute['neweggcategoryId'] ?>][<?= $value['PropertyName'] ?>][<?= AttributeMap::VALUE_TYPE_TEXT ?>]"
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
      <td class="attrmap-inner-table-inner-td2">
         <div id="wait">
<img style="width:35px !important" src='<?= Yii::$app->request->baseUrl ?>/images/482.gif'>
<br>
Loading..
</div>
      </td>
      </td>
      </tr>
      
      <?php
       $attributeCount++;
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
      var counter = 0;
      var queue = [];
      var scriptArray = <?=$scriptArray?>;
      $.each(scriptArray, function(key,value) {
         var valueCount = 0;
         queue[counter] = [];
         $('#'+counter+' .attrmap-inner-table tbody tr').each(function() {
            queue[counter][valueCount] = new Object();
            queue[counter][valueCount].element = this;
            queue[counter][valueCount].value = value;
            queue[counter][valueCount].counter = counter;
            queue[counter][valueCount].valueCount = valueCount;
            valueCount++;
         });
         counter++;
      });
      aSynchronousCall(queue,0,0);
      function aSynchronousCall(queue,key,innerKey){
         if(typeof queue[key][innerKey]=='undefined')
         {
            key++;
            innerKey = 0;
         }
         if(typeof queue[key]!='undefined' && typeof queue[key][innerKey]!='undefined'){
            setTimeout(function(){
               checkingFunction(queue[key][innerKey].element,queue[key][innerKey].valueCount,queue[key][innerKey].value,queue[key][innerKey].counter);
               aSynchronousCall(queue,key,innerKey+1);
            }, 500);
         }
         
         
      }
      //console.log(queue);
      function checkingFunction(element,valueCount,value,counter){
         var attributeLabel = element.getElementsByTagName('TD')[0].firstChild.nodeValue;
            var label = $.trim(attributeLabel);
            var attributePropertyValue = value.neweggattributevalue[label];
            if(attributePropertyValue[0].IsGroupBy=='1' && attributePropertyValue[0].IsRequired=='1'){
               var dropdown  = $('<select />', {class:'form-control root',name: 'newegg['+$.trim(value.product_type)+']['+$.trim(value.neweggcategoryId)+']['+label+'][<?= AttributeMap::VALUE_TYPE_MIXED ?>]' });
            }
            else{
               if(attributePropertyValue[0].IsGroupBy=='1'){
                  var dropdown  = $('<select />', {class:'form-control root',name: 'newegg['+$.trim(value.product_type)+']['+$.trim(value.neweggcategoryId)+']['+label+'][<?= AttributeMap::VALUE_TYPE_SHOPIFY ?>]' });
               }
               else{
                  var dropdown  = $('<select />', {class:'form-control root', name: 'newegg['+$.trim(value.product_type)+']['+$.trim(value.neweggcategoryId)+']['+label+'][<?= AttributeMap::VALUE_TYPE_NEWEGG ?>]' });
               }
            }
            var dropDownData = attributePropertyValue[0].PropertyValueList;
            var id = [];
            $.each(dropDownData, function(key1,value1) {
               id['map'] = value.mapped_values[label];
               if(typeof id['map']!='undefined' && id['map'].value==value1){
                  dropdown.append($('<option />', {value: value1,selected:'selected'}).html(value1));
               }
               else{
                  dropdown.append($('<option />', {value: value1}).html(value1));
               }
            });
            $('#'+counter+' #col'+valueCount+' .attrmap-inner-table-inner-td2 #wait').remove();
            $('#'+counter+' #col'+valueCount+' .attrmap-inner-table-inner-td2').append(dropdown);
            attributePropertyValue ='';
            
      }
   })
</script>
