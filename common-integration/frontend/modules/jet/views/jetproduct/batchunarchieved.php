<?php 
	$succes_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_success.gif';
	$error_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/error_msg_icon.gif';
	$loader_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/rule-ajax-loader.gif';
	$ipmort_url = \yii\helpers\Url::to(Yii::$app->request->referrer);
	$productUrl = \yii\helpers\Url::toRoute(['jetproduct/index']);
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
						<h1 class="Jet_Products_style">Product Un-Archive Status</h1>
					</div>
					<div class="product-upload-menu">
						<a href="<?php echo $ipmort_url;?>">
							<button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
						</a>
					</div>
					<div class="clear"></div>
			</div>
			<div class="block-content panel-body shopify-api ">			
				<ul class="warning-div" style="margin-top: 66px !important;">
					<li style="background-color:#Fff;">
						<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
						Starting Product Unarchive execution, please wait...
					</li>
					<li style="background-color:#FFD;">
						<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_error.gif';?>" class="v-middle" style="margin-right:5px"/>
						Warning: Please do not close the window during Unarchiving data
					</li>
				</ul>
				
				<ul id="profileRows">
					<li style="background-color:#DDF; ">
						<img class="v-middle" src="<?php echo $succes_img ?>">
						<?php echo 'Total '.$totalcount.' Product(s) Found.'; ?>
					</li>
					<li style="background-color:#DDF;" id="update_row">
						<img class="v-middle" id="status_image" src="<?php echo $loader_img ?>">
						<span id="update_status" class="text">Updating...</span>
					</li>
					<li id="liFinished" style="display:none;background-color:#Fff;">
						<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
						Finished product unarchived execution.
					</li>
				</ul>
			</div>
		</div>     
	</div>
</div>
<script type="text/javascript"> 
	var csrfToken = $('meta[name=csrf-token]').attr("content");
	var totalRecords = parseInt("<?php echo $totalcount; ?>");
	var countOfSuccess = 0;
	var id = 0;
	var my_id = document.getElementById('liFinished');	
	var update_status = document.getElementById('update_status');
	var update_row = document.getElementById('update_row');
	var status_image = document.getElementById('status_image');
	uploaddata();		

	function uploaddata(){
		
		percent = getPercent();
		update_status.innerHTML = percent+'% '+(id+1)+' Of '+totalRecords+' Processing';

		$.ajax({
            url:"<?=\yii\helpers\Url::toRoute(['jetproduct/startbatchunarchieved'])?>",
            method: "post",
            dataType:'json',
            data:{ index : id,_csrf : csrfToken },
        	success: function(json) {//transport
            	id++;
            	if(json.success){
					countOfSuccess++;	
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span class="text">'+json.success+'</span>';
					span.id = 'id-'+id;
					span.style = 'background-color:#DDF';
					update_row.parentNode.insertBefore(span, update_row);									
				}
				if(json.error){
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">'+json.error+'</span>';
					span.id = 'id-'+id;
					span.style = 'background-color:#FDD';
					update_row.parentNode.insertBefore(span, update_row);
				}
					
				if(id < totalRecords){
					uploaddata();
				}else{
					status_image.src = '<?php echo $succes_img ?>';
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Unarchive Requested.'+'</span>';
					span.style = 'background-color:#DDF';
					my_id.parentNode.insertBefore(span, my_id);
					document.getElementById("liFinished").style.display="block";
					$(".warning-div").hide();
					update_status.innerHTML = percent+'% '+(id)+' Of '+totalRecords+' Processed.';

					//setTimeout(function(){
			    		//window.location.href = '<?php //echo $productUrl; ?>';
					//}, 3000);
				}
					
            },
			error: function(){
				id++;
				var span = document.createElement('li');
				span.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">Something Went Wrong </span>';
				span.id = 'id-'+id;
				span.style = 'background-color:#FDD';
				update_row.parentNode.insertBefore(span, update_row);
				
				if(id < totalRecords){
					uploaddata();
				}else{
					status_image.src = '<?php echo $succes_img ?>';
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Unarchived.'+'</span>';
					span.style = 'background-color:#DDF';
					my_id.parentNode.insertBefore(span, my_id);
					$(".warning-div").hide();
					document.getElementById("liFinished").style.display="block";

					//setTimeout(function(){
			    		//window.location.href = '<?php //echo $productUrl; ?>';
					//}, 3000);
				}
			}
		});
		
	}

	function getPercent() {
		
       return Math.ceil(((id+1)/totalRecords)*1000)/10;
    }
	
</script>