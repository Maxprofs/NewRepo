<?php 
use yii\helpers\Html;
	$succes_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_success.gif';
	$error_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/error_msg_icon.gif';
	$loader_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/rule-ajax-loader.gif';

	$url = \frontend\modules\neweggmarketplace\components\Data::getUrl('neweggproduct/startimageupdate');
	$backUrl = \frontend\modules\neweggmarketplace\components\Data::getUrl('neweggproduct/index');
?>
<style type="text/css" >
   .shopify-api ul { list-style-type:none; padding:0; margin:0; }
   .shopify-api ul li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif; }
   .shopify-api img { margin-right:5px; }
   li span ul li{
   	border : 0px !important;
   	margin-left:18px !important;
   }
</style>

<div class="row">
    <div class="col-md-12" style="margin-top: 10px;">
        <div class="panel panel-default form new-section">
            <div class="jet-pages-heading">
                    <div class="title-need-help">
                        <h1 class="Jet_Products_style">Image Update</h1>
                    </div>
                    <div class="product-upload-menu">
                        <a href="<?= $backUrl;?>">
                            <button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
                        </a>
                    </div>
                    <div class="clear"></div>
            </div>
         <div class="block-content panel-body shopify-api ">         
               <ul class="warning-div" style="margin-top: 18px">
               <li style="background-color:#Fff;">
                  <img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
                  Starting  Product Image update, please wait...
               </li>
               <li style="background-color:#FFD;">
                  <img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_error.gif';?>" class="v-middle" style="margin-right:5px"/>
                  Warning: Please do not close the window during image update process
               </li>
            </ul>
            
            <ul id="profileRows">
               <li style="background-color:#DDF; ">
                  <img class="v-middle" src="<?php echo $succes_img ?>">
                  <?php echo 'Total '.$totalcount.' Product(s) Selected for image update.'; ?>
               </li>
               <li style="background-color:#DDF;" id="update_row">
                  <img class="v-middle" id="status_image" src="<?php echo $loader_img ?>">
                  <span id="update_status" class="text">Uploading...</span>
               </li>
               <li id="liFinished" style="display:none;background-color:#Fff;">
                  <img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
                  Finished Product image Update process.
               </li>
            </ul>
            </div>
        </div>     
    </div>
</div>

<script type="text/javascript"> 
   var csrfToken = j$('meta[name=csrf-token]').attr("content");
   var totalRecords = parseInt("<?php echo $totalcount; ?>");
   var pages = parseInt("<?php echo $pages; ?>");
   var countOfSuccess = 0;
   var notSku=0;
   var id = 0;
   var my_id = document.getElementById('liFinished'); 
   var update_status = document.getElementById('update_status');
   var update_row = document.getElementById('update_row');
   var status_image = document.getElementById('status_image');
   retiredata();     

   function retiredata(){
      percent = getPercent();
      update_status.innerHTML = percent+'% Page '+(id+1)+' Of total product pages '+pages+' Processing';

      j$.ajax({
            url:"<?= $url?>",
            method: "post",
            dataType:'json',
            data:{ index : id,_csrf : csrfToken},
         success: function(json) {//transport
               //var json = transport.responseText.evalJSON();
               id++;
               //console.log(json);
            if(json.success) {
               console.log(json.success);
               countOfSuccess+=json.count;
               notSku+=json.not_sku;
               var span = document.createElement('li');
               span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span class="text">'+json.count+' products imported of page '+id+'...</span>';
               span.id = 'id-'+id;
               span.style = 'background-color:#DDF';
               update_row.parentNode.insertBefore(span, update_row);                         
            }
            if(json.error_count > 0) {
               var span = document.createElement('li');
               span.innerHTML = span.innerHTML+'<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">'+json.error_count +' Product(s) Having Sku(s) '+json.erroredSkus +' Have Some Errors/Missing Info.'+'</span>';
               span.style = 'background-color:#FDD';
               update_row.parentNode.insertBefore(span, update_row);
            }
            if(json.error){
               var span = document.createElement('li');
               span.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">'+json.message+'</span>';
               span.id = 'id-'+id;
               span.style = 'background-color:#FDD';
               update_row.parentNode.insertBefore(span, update_row);
            }
            if(id < pages) {
               retiredata();
            } else {
               status_image.src = '<?php echo $succes_img ?>';
               var span = document.createElement('li');
               if(notSku && notSku>0){
                  span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Imported and '+notSku+' products status unable to change due to missing product sku(s).'+'</span>';
               }else{
                  span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product Successfully Updated.'+'</span>';
               }
               span.style = 'background-color:#DDF';
               my_id.parentNode.insertBefore(span, my_id);
               //$('liFinished').show();
               document.getElementById("liFinished").style.display="block";
               j$(".warning-div").hide();
               j$("#profileRows").css({'margin-top':'10px'});
               update_status.innerHTML = percent+'% '+(id)+' Of '+pages+' Processed.';
               /*setTimeout(function(){
                  window.location.href = '';
               }, 1500);*/
            }
            },
         error: function(){
            id++;
            var span = document.createElement('li');
            span.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">Something Went Wrong </span>';
            span.id = 'id-'+id;
            span.style = 'background-color:#FDD';
            update_row.parentNode.insertBefore(span, update_row);
            
            if(id < pages){
               retiredata();
            }else{
               status_image.src = '<?php echo $succes_img ?>';
               var span = document.createElement('li');
               span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Updated.'+'</span>';
               span.style = 'background-color:#DDF';
               my_id.parentNode.insertBefore(span, my_id);
               //$('liFinished').show();
               j$(".warning-div").hide();
               j$("#profileRows").css({'margin-top':'10px'});
               document.getElementById("liFinished").style.display="block";
            }
         }
      });
      
   }

   function getPercent() {
      
       return Math.ceil(((id+1)/pages)*1000)/10;
    }
   
</script>