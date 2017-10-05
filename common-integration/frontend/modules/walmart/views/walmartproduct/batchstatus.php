<?php 
use yii\helpers\Html;
	$succes_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_success.gif';
	$error_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/error_msg_icon.gif';
	$loader_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/rule-ajax-loader.gif';
	$ipmort_url=\yii\helpers\Url::toRoute(['walmartproduct/index']);
	/*$merchant_id = Yii::$app->user->identity->id;
	if($merchant_id==14){
		$url=\yii\helpers\Url::toRoute(['jetproduct/startbatchupload1']);
	}else{*/
		$url=\yii\helpers\Url::toRoute(['walmartproduct/productstatus']);
		$productUrl= \yii\helpers\Url::toRoute(['walmartproduct/index']);
	//}
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
                        <h1 class="Jet_Products_style">Product Status</h1>
                    </div>
                    <div class="product-upload-menu">
                        <a href="<?= $ipmort_url;?>">
                            <button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
                        </a>
                    </div>
                    <div class="clear"></div>
            </div>
        	<div class="block-content panel-body shopify-api ">         
                <ul class="warning-div" style="margin-top: 18px">
					<li class="status_finish">
						<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
						Starting Product update status execution, please wait...
					</li>
					<li class="status_warning">
						<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_error.gif';?>" class="v-middle" style="margin-right:5px"/>
						Warning: Please do not close the window during status update process
					</li>
				</ul>
				
				<ul id="profileRows">
					<!-- <li class="status_success">
						<img class="v-middle" src="<?php echo $succes_img ?>">
						<?php //echo 'Total '.$totalcount.' Product(s) Found.'; ?>
					</li> -->
					<li class="" id="success_count" style="background-color: rgb(221, 221, 255); display: none;"></li>
					<li class="status_success" id="update_row">
						<img class="v-middle" id="status_image" src="<?php echo $loader_img ?>">
						<span id="update_status" class="text">Updating...</span>
					</li>
					<li id="liFinished" class="status_finish" style="display:none;">
						<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
						Finished product status update execution.
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
	var success_count = document.getElementById('success_count');
	var apiError = 0;
	uploaddata();		

	function uploaddata(){
		//percent = getPercent();
		//update_status.innerHTML = percent+'% Page '+(id+1)+' Of total product pages '+pages+' Processing';

		j$.ajax({
            url:"<?= $url?>",
            method: "post",
            dataType:'json',
            data:{ index : id,_csrf : csrfToken,count : totalRecords},
        	success: function(json) {
            	id++;
				if(json.success){
					countOfSuccess += json.success.count;
					var innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span class="text">Status of '+countOfSuccess+' products Updated Successfully.</span>';
					success_count.innerHTML = innerHTML;

					var className = 'id-'+id;
					success_count.className = className;
					success_count.style.display = "block";
				}

				if(json.error){
					var li = document.createElement('li');
					li.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">'+json.error+'</span>';
					li.id = 'id-'+id;
					li.style = 'background-color:#FDD';
					update_row.parentNode.insertBefore(li, update_row);
				}

				if(json.api_error) {
					id--;// send request for same page if getting error for that index
					apiError++;
				}

				if(!json.finish && apiError < 5) {
					uploaddata();
				}
				else
				{
					status_image.src = '<?php echo $succes_img ?>';
					var span = document.createElement('li');
					if(notSku && notSku>0){
						span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Imported and '+notSku+' products status unable to change due to missing product sku(s).'+'</span>';
					}else{
						span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product Status(s) Successfully Updated.'+'</span>';
					}
					span.style = 'background-color:#DDF';
					my_id.parentNode.insertBefore(span, my_id);
					document.getElementById("liFinished").style.display = "block";
					j$(".warning-div").hide();
					j$("#profileRows").css({'margin-top':'10px'});
					//update_status.innerHTML = percent+'% '+(id)+' Of '+pages+' Processed.';

					success_count.style.display = "none";
					update_row.style.display = "none";
				}
            },
			error: function(){
				id++;
				var span = document.createElement('li');
				span.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">Something Went Wrong </span>';
				span.id = 'id-'+id;
				span.style = 'background-color:#FDD';
				update_row.parentNode.insertBefore(span, update_row);
				
				if(!json.finish)
				{
					uploaddata();
				}
				else
				{
					status_image.src = '<?php echo $succes_img ?>';
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Uploaded.'+'</span>';
					span.style = 'background-color:#DDF';
					my_id.parentNode.insertBefore(span, my_id);
					j$(".warning-div").hide();
					j$("#profileRows").css({'margin-top':'10px'});
					document.getElementById("liFinished").style.display="block";

					success_count.style.display = "none";
					update_row.style.display = "none";
				}
			}
		});
	}

	function getPercent() {
		
       return Math.ceil(((id+1)/pages)*1000)/10;
    }
	
</script>