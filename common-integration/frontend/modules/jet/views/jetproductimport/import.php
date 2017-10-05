<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use frontend\modules\jet\models\JetProduct;

$this->title = 'Import Products';
$merchant_id=Yii::$app->user->identity->id;
$urlTotal = \yii\helpers\Url::toRoute(['jetproductimport/gettotaldetails']);
$succes_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_success.gif';
$error_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/error_msg_icon.gif';
$loader_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/rule-ajax-loader.gif';
$url = \yii\helpers\Url::toRoute(['jetproductimport/batchimport']);

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
<div class="content-section">
	<div id="import-error" class="help-block help-block-error top_error alert-danger" style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
	<div class="import-content-wrapper">
		<div class="import-dropdown">
			<span class="value_label">Products to Import</span>
			<select class="import-select form-control" name="import-select" id="import-select" onchange="getTotalDetails(this);">
			    <option value="">Choose..</option>
				<option value="any">All</option>
				<option value="published">Published</option>
			</select>
		</div>

		<table class="table table-striped table-bordered" id="total-display" cellspacing="0" style="display:none;">
			<tbody>
				<tr>
					<td class="value_label">
                        <span>Total Products Available</span>
                    </td>
                    <td class="value">
                        <span id="total-products-available"></span>
                    </td>
				</tr>
				<tr>
					<td class="value_label">
                        <span>Products not having "Sku"</span>
                    </td>
                    <td class="value">
                        <span id="non-sku-products"></span>
                    </td>
				</tr>
				<tr>
					<td class="value_label">
                        <span>Products Ready To Import</span>
                    </td>
                    <td class="value">
                        <span id="ready-products"></span>
                    </td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"><button class="btn btn-primary" id="start-import-button">Start Import</button></td>	
				</tr>
			</tfoot>	
		</table>
	</div>

	<!-- *******************************************AJAX IMPORT****************************************** -->
	<div class="row ajax-import-wrapper" id="ajax-import-wrapper" style="display:none;">
		<div class="col-md-12" style="margin-top: 10px;">
			<div class="panel panel-default">
					<div class="jet-pages-heading">
						<h1 class="Jet_Products_style">Product Import Status</h1>
						<!--<a href="<?php //echo $ipmort_url;?>">
							<button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
						</a>-->
						<div class="clear"></div>
					</div>
					<div style="display:none;" id="success-import-msg" class="alert alert-success fade in alert-dismissable" style="margin-top:18px;">
					  <strong>Successfully Imported!</strong> Now click on <strong>Next</strong> button to proceed.
					</div>
				<div class="block-content panel-body shopify-api ">			
					<ul class="warning-div" style="margin-top: 18px">
						<li style="background-color:#Fff;">
							<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
							Starting Product Import execution, please wait...
						</li>
						<li style="background-color:#FFD;">
							<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/fam_bullet_error.gif';?>" class="v-middle" style="margin-right:5px"/>
							Warning: Please do not close the window during import data
						</li>
					</ul>
					
					<ul id="profileRows">
						<li style="background-color:#DDF; ">
							<img class="v-middle" src="<?php echo $succes_img ?>">
							Total <span id="total_show"></span> Product(s) Found.
						</li>
						<li style="background-color:#DDF;" id="update_row">
							<img class="v-middle" id="status_image" src="<?php echo $loader_img ?>">
							<span id="update_status" class="text">Updating...</span>
						</li>
						<li id="liFinished" style="display:none;background-color:#Fff;">
							<img src="<?php echo yii::$app->request->baseUrl.'/frontend/images/batchupload/note_msg_icon.gif';?>" class="v-middle" style="margin-right:5px"/>
							Finished product import execution.
						</li>
					</ul>
				</div>
			</div>     
		</div>
	</div>
	<button class="next btn btn-primary" onclick="nextStep();" id="import-next">Next</button>
</div>

<script>

var csrfToken = $('meta[name=csrf-token]').attr("content");
var totalRecords = 0;
var pages = 0;
var countOfSuccess = 0;
var notSku=0;
var id = 0;
var my_id = document.getElementById('liFinished');	
var update_status = document.getElementById('update_status');
var update_row = document.getElementById('update_row');
var status_image = document.getElementById('status_image');
var merchant_id='<?= $merchant_id?:0;?>';
$('.next').attr('disabled', true);
function getTotalDetails(ele){
	var selectVal =$(ele).val();
	if(selectVal!=""){
		var url='<?= $urlTotal?:""; ?>';
		$('#LoadingMSG').show();
	    $.ajax({
	      method: "post",
	      url: url,
	      dataType: 'json',
	      data: {select:selectVal,merchant_id : merchant_id,_csrf : csrfToken}
	    })
	    .done(function(msg){
			//console.log(msg);
			if($.isEmptyObject(msg) || (!$.isEmptyObject(msg) && msg.hasOwnProperty('err'))) {
				var error = (!$.isEmptyObject(msg) && msg.hasOwnProperty('err')) ? msg.err : "Error - No product(s) found.";
				$('#import-error').val(error);
			}else{
			   $('#total-products-available').html(msg.total);
		       $('#non-sku-products').html(msg.non_sku);
		       $('#ready-products').html(msg.ready);
		       $('#total-display').css("display","block");	 
			}
	       $('#LoadingMSG').hide();
	    });
	}else{
		$('#total-products-available').html('');
	    $('#non-sku-products').html('');
	    $('#ready-products').html('');
		$('#total-display').css("display","none");	
	}
}

$("#start-import-button").click(function(){
	//console.log(parseInt($('#ready-products').html()));
	if(parseInt($('#ready-products').html())>0){
		$('#import-content-wrapper').fadeTo( "fast", 0.1 );
		$("#start-import-button").attr('disabled', true);
		$("#import-select").attr('disabled', true);
		$('#ajax-import-wrapper').css('display', 'block');
		totalRecords = parseInt($('#total-products-available').html());
		$('#total_show').html(totalRecords);
		pages = Math.ceil(parseInt($('#total-products-available').html())/250);
		uploaddata();
	}
});
function uploaddata(){
		percent = getPercent();
		update_status.innerHTML = percent+'% Page '+(id+1)+' Of total product pages '+pages+' Processing';
		var select = $('#import-select').val();
		$.ajax({
            url:"<?= $url?>",
            method: "post",
            dataType:'json',
            data:{ index : id+1, _csrf : csrfToken, select: select, merchant_id: merchant_id},
        	success: function(json) {//transport
            	//var json = transport.responseText.evalJSON();
            	id++;
				if(json.success){
					console.log(json.success);
					countOfSuccess+=json.success.count;
					notSku+=json.success.not_sku;
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span class="text">'+json.success.count+' products imported of page '+id+'...</span>';
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
				if(id+1 <= pages){
					uploaddata();
				}else{
					status_image.src = '<?php echo $succes_img ?>';
					var span = document.createElement('li');
					if(notSku && notSku>0){
						span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Imported and '+notSku+' products unable to import due to missing product sku(s).'+'</span>';
					}else{
						span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Imported.'+'</span>';
					}
					span.style = 'background-color:#DDF';
					my_id.parentNode.insertBefore(span, my_id);
					//$('liFinished').show();
					document.getElementById("liFinished").style.display="block";
					$(".warning-div").hide();
					$("#success-import-msg").css('display', 'block');
					$('.next').attr('disabled', false);
					$("#profileRows").css({'margin-top':'10px'});
					update_status.innerHTML = percent+'% '+(id)+' Of '+pages+' Processed.';
				}
            },
			error: function(){
				id++;
				var span = document.createElement('li');
				span.innerHTML = '<img class="v-middle" src="<?php echo $error_img ?>"><span class="text">Something Went Wrong </span>';
				span.id = 'id-'+id;
				span.style = 'background-color:#FDD';
				update_row.parentNode.insertBefore(span, update_row);
				if(id+1 <= pages){
					uploaddata();
				}else{
					status_image.src = '<?php echo $succes_img ?>';
					var span = document.createElement('li');
					span.innerHTML = '<img class="v-middle" src="<?php echo $succes_img ?>"><span id="update_status" class="text">'+countOfSuccess+' Product(s) Successfully Uploaded.'+'</span>';
					span.style = 'background-color:#DDF';
					my_id.parentNode.insertBefore(span, my_id);
					//$('liFinished').show();
					$(".warning-div").hide();
					$("#profileRows").css({'margin-top':'10px'});
					document.getElementById("liFinished").style.display="block";
				}
			}
		});
	}

	function getPercent() {
		return Math.ceil(((id+1)/pages)*1000)/10;
    }
</script>

			