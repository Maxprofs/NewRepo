<?php
use yii\helpers\Html;
use frontend\modules\jet\components\Data;

$this->title = 'Map Category';

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-map-index content-section">
	
    <!-- <div class="clear"></div>  -->
    <form id="category_map" class="form new-section" method="post" action="<?php echo \yii\helpers\Url::toRoute(['categorymap/save']) ?>">
    	<div class="jet-pages-heading">
    	<div class="title-need-help">
	    	<h3 class="Jet_Products_style"><?= Html::encode($this->title) ?></h3>
	    </div>
	    <div class="product-upload-menu">
			
				<button type="button" id="instant-help" data-toggle='tooltip' title="Click to checkout tour (how to map category)" class="btn btn-primary">Help</button>
				<input type="submit" value="Submit"  class="btn btn-primary" id="save-category-map"  data-toggle='tooltip' title="Click to save mapped category"  data-step='5' data-intro="Save performed Changes." data-position='left'/>
		</div>
			<div class="clear"></div>
		</div>	
		<div class="responsive-table-wrap">
		<table id="map_producttype" class="table table-striped table-bordered">
			<tr>
				<th data-toggle="tooltip" title="Id">Id</th>
				<th data-toggle="tooltip" title="Product type on shopify">Product Type(shopify)</th>
				<th data-toggle="tooltip" title="Jet Category Name" class="center" colspan="3"><label class="label-text-center">Jet Category Name</label></th>
			</tr>
			<?php 
			$i=0;
			foreach($model as $value){
				$i++;?>
				<?php $is_selected=false;?>
				<tr <?= $i==1?'id="intro_first_tr"':"";?>>
					<td><?php echo $i; ?></td>
					<td <?= $i==1?'id="intro_first_td_label" data-step="1" data-position="bottom" data-intro="This is Shopify Product Type."':"";?>><?php echo $value->product_type; ?></td>
					<?php $category_path=array();?>
					<?php $category_path_str="";?>
					<?php $category_path_str=$value->category_path;?>
					<?php if(trim($value->category_path)!=""){?>
						<?php $category_path=explode(',',$value->category_path);?>
					<?php }?>
		  			<td class="cat_root" <?= $i==1?'id="intro_first_td_root" data-step="2" data-position="bottom" data-intro="Select Root Category From DropDown corresponding to Product Type."':"";?>>
						<select id="select_<?=$i?>" name="type[<?=trim($value->product_type)?>][]" style="width:auto" class="form-control root" onchange="selectChild(this,1,<?php echo "'".trim($category_path_str)."'";?>,<?php echo "'".trim(addslashes($value->product_type))."'";?>)">
							<option value="">Please Select Category</option>
							<?php 
							foreach($data as $val)
							{
								if($val->level==0){
									if(count($category_path)>0 && $category_path[0]==$val->category_id){?>	
										<?php $is_selected=true;?>						
										<option value="<?=$val->category_id;?>" selected="selected"><?=$val->title;?></option>
								<?php }else{?>	
										<option value="<?=$val->category_id;?>"><?=$val->title;?></option>
								<?php }
								}
							}
							?>
						</select>
						<?php if($is_selected){?>
								<script type="text/javascript">
									$(document).ready(function(){
										$("#select_<?=$i?>").trigger('change');
									});
								</script>
						<?php }?>
					</td>	
					<td style="display:none;" class="cat_child" <?= $i==1?'id="intro_first_td_sub" data-step="3" data-position="bottom" data-intro="Select Child Category From DropDown corresponding to Selected Root Category."':"";?>></td>
					<td style="display:none;" class="cat_sub_child" <?= $i==1?'id="intro_first_td_sub_sub" data-step="4" data-position="bottom" data-intro="Select Child Category From DropDown corresponding to Selected Jet Category."':"";?>></td>	
				</tr>
				<?php 				
			}
			?>
		</table>
		</div>
	</form>
</div>
<?php $url= \yii\helpers\Url::toRoute(['categorymap/getcategory']);?>    
<script>
var category_tree=<?=json_encode($category_tree)?>;
var category_detail=<?=json_encode($category_detail)?>;

var csrfToken = $('meta[name="csrf-token"]').attr("content");

function selectChild(node,level,path_str,type){
		
		var global_level="";
		var global_type="";
		var global_path_str="";
		var global_type_str="";
		var select="";
		var options="";
		var path_arr=[];
		var node_val=$(node).val();
		var option_name="";
		if(level==1){
				
				if(node_val==""){
						$(node).parent('td').parent('tr').find('td.cat_child').css('display','none');
						$(node).parent('td').parent('tr').find('td.cat_sub_child').css('display','none');
						$(node).parent('td').parent('tr').find('td.cat_sub_child').html("");
						$(node).parent('td').parent('tr').find('td.cat_child').html("");
						return true;
				}
				if(path_str !=""){
						path_arr=path_str.split(',');
				}
				global_level=level+1;
				global_path_str="'"+path_str+"'";
				global_type_str="'"+type.replace(/'/g, "\\'")+"'";
				
				options="";
				select="";
				$.each(category_tree, function(first_key, first_arr) {
					if(first_key==node_val && ($.type(first_arr) === "object")){
						select='<select name="type['+type+'][]" class="form-control"  onchange="selectChild(this,'+global_level+','+global_path_str+','+global_type_str+')">';
						//select="<select name='type["+type+"][]' class='form-control'  onchange='selectChild(this,"+global_level+","+global_path_str+","+global_type_str+")'>";
							$.each(first_arr, function(sec_key, sec_arr) {
									option_name="";
									$.each(category_detail, function(cat_id, cat_name) {
											if(cat_id==sec_key){
												option_name=cat_name;
												return false;
											}
									});
									if(option_name !=""){
											if($.type(path_arr)==="array" && path_arr.length>level){
												if(sec_key==path_arr[level]){
													options+="<option selected='selected' value='"+sec_key+"'>"+option_name+"</option>";
												}else{
													options+="<option value='"+sec_key+"'>"+option_name+"</option>";
												}
											}else{
												options+="<option value='"+sec_key+"'>"+option_name+"</option>";
											}
											
									}
							});
						if(options !=""){
							select+=options;
							select+="</select>";
						}else{
							select="";
						}
						return false;
				    }
				});
			
			$(node).parent('td').parent('tr').find('td.cat_child').html("");
		    $(node).parent('td').parent('tr').find('td.cat_sub_child').html("");
		    $(node).parent('td').parent('tr').find('td.cat_child').html(select);
		    if(select ==""){
		    	$(node).parent('td').parent('tr').find('td.cat_child').css('display','none');
		    }else{
		    	$(node).parent('td').parent('tr').find('td.cat_child').css('display','table-cell');
			    if($(node).parent('td').parent('tr').find('td.cat_child').children('select').length){
			          $(node).parent('td').parent('tr').find('td.cat_child').children('select').trigger('change');
			    }
		    }
		    //kshitij
		    if(node_val!="" && $(node).hasClass('select_error')){
		    	$(node).removeClass('select_error');
		    	$(node).next('div').children('.error_category_map').css('display','none');
			}
		    	
		}else if(level==2){
				
				var check_complete=false;
				options="";
				select="";
				if(path_str !=""){
						path_arr=path_str.split(',');
				}
				$.each(category_tree, function(first_key, first_arr) {
					$.each(first_arr, function(sec_key, sec_arr) {
							if(sec_key==node_val && ($.type(sec_arr) === "object")){
								select='<select name="type['+type+'][]" class="form-control">';
								//select="<select name='type["+type+"][]' class='form-control'>";
								$.each(sec_arr, function(third_key, third_value) {
										option_name="";
										$.each(category_detail, function(cat_id, cat_name) {
													if(cat_id==third_key){
														option_name=cat_name;
														return false;
													}
										});
										if(option_name !=""){
											if($.type(path_arr)==="array" && path_arr.length>level){
												if(third_key==path_arr[level]){
													options+="<option selected='selected' value='"+third_key+"'>"+option_name+"</option>";
												}else{
													options+="<option value='"+third_key+"'>"+option_name+"</option>";
												}
											}else{
												options+="<option value='"+third_key+"'>"+option_name+"</option>";
											}
										}
								});
								if(options !=""){
									select+=options;
									select+="</select>";
								}else{
									select="";
								}
								check_complete=true;
								return false;	
							}
				    });
				    if(check_complete){
				    	return false;	
				    }
				});
				
				$(node).parent('td').parent('tr').find('td.cat_sub_child').html("");
		        $(node).parent('td').parent('tr').find('td.cat_sub_child').html(select);
		        if(select == ""){
		        	$(node).parent('td').parent('tr').find('td.cat_sub_child').css('display','none');
		        }else{
		        	$(node).parent('td').parent('tr').find('td.cat_sub_child').css('display','table-cell');
		        }
		        
		}

}
$("#category_map").submit(function(){
		//if(!checkBeforeSave()){
		//	return false;
		//}
		//return false;
});
function checkBeforeSave(){
	var selector_arr=[];
	var rows_value_arr=[];
	var value_str="";
	var first_select="";
	var second_select="";
	var third_select="";
	var stop_form=false;
	<?php $i=1;?>
	<?php foreach($model as $value){?>
			selector_arr.push("#select_<?=$i?>");
	<?php $i++;?>
	<?php }?>
		for(j=0;j<selector_arr.length;j++){
				first_select="";
				second_select="";
				third_select="";
				first_select=$(selector_arr[j]);
				value_str=first_select.val();
				second_select=$(selector_arr[j]).parent().parent('tr').find('td.cat_child').children('select');
				third_select=$(selector_arr[j]).parent().parent('tr').find('td.cat_sub_child').children('select');
				if(second_select.length){
					value_str=value_str+","+second_select.val();
				}
				if(third_select.length){
					value_str=value_str+","+third_select.val();
				}
				rows_value_arr.push(value_str);
		}
		for(j=0;j<rows_value_arr.length;j++){
			for(u=j+1;u<rows_value_arr.length;u++){
					if(rows_value_arr[j]==rows_value_arr[u] && rows_value_arr[u]!=""){
						stop_form=true;
						return false;
					}
			}
			if(stop_form){
				return false;
			}
		}
		if(stop_form){
			return false;
		}
		return true;
}
$('#category_map').submit(function( event ) {
	  var flag=false;
	  $('.cat_root .root').each(function(){
		 if($(this).val()==""){
		  	flag=true;
		  	$(this).addClass("select_error");
		  	//$('.error_category_map').css('display','block');
		 }
		 else{
			 flag=false;
			 $(this).removeClass("select_error");
			 //$('.error_category_map').css('display','none');
			 return false;
		 }
	  });
	  if(flag){
		  return false;
	  }
});
$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip({container: "th"});
});

 var intro = "";
    $(function(){
       
      var intro = introJs().setOptions({
      	    showStepNumbers: false,
      		exitOnOverlayClick: false,
          });  
          $('#instant-help').click(function(){
                intro.start();
          });   
    });
</script>

<?php $get = Yii::$app->request->get();
	if(isset($get['tour'])) : 
?>
	<script type="text/javascript">
		$(document).ready(function(){
			var categorymapQuicktour = introJs().setOptions({
				doneLabel: 'Next page',
	            showStepNumbers: false,
	            exitOnOverlayClick: false,
	            /*steps: [
	              {
	                element: '#intro_first_td_label',
	                intro: 'This is Shopify Product Type.',
	                position: 'bottom'
	              },
	              {
	                element: '#intro_first_td_root',
	                intro: "Select Root Category From DropDown corresponding to Product Type.",
	                position: 'bottom'
	              },
	              {
	                element: '#intro_first_td_sub',
	                intro: "Select Child Category From DropDown corresponding to Selected Root Category.",
	                position: 'bottom'
	              },
	              {
	                element: '#intro_first_td_sub_sub',
	                intro: "Select Child Category From DropDown corresponding to Selected Jet Category.",
	                position: 'bottom'
	              },
	              {
	                element: '#save-category-map',
	                intro: "Save performed Changes.",
	                position: 'left'
	              }
	            ]*/
          	});
			setTimeout(function () {
          		categorymapQuicktour.start().oncomplete(function() {
          		window.location.href = '<?= Data::getUrl("jetproduct/index?tour") ?>';
        		},1000);
        	});
    	});
	</script>
<?php endif; ?>

<style>
	.center,.cat_root{
		text-align: center;
	}
	.cat_root .form-control{
		display: inline-block;
	}
	
</style>