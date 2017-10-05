<?php
use yii\helpers\Html;
	$this->title = 'Send Mail to Clients';
	$templates = [];
	$templates['-'] = '--Select Template--';
	foreach($data['templates'] as $template){
		$templates[$template['template_path']] = $template['custom_title'];
	}
	$templates['-'] = '--Select Template--';
?>              
<script type="text/javascript" src="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/js/jquery.wysiwygEditor.js"></script>
<link rel="stylesheet" href="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/css/jquery.wysiwygEditor.css"/>

<!-- quick email widget -->
 <div class="box box-info">
    <div class="box-header">
        <h3 class="box-title"> <i class="fa fa-envelope"></i> Quick Email for merchant</h3>
    </div>
    <div id="mail-success-msg"></div>
    <div class="box-body" id="form-body-data">
    	<div class="box-footer clearfix" style="margin-top: 2%;">
	         <button class="pull-right btn btn-default" id="sendEmail" >Send <i class="fa fa-arrow-circle-right"></i></button>
	    </div>
       <form action="#" method="post" id ="mailform">
       		<input type="hidden" id="merchant_ids" name="merchant_ids" value="<?= implode(',',$data['selection']) ?>">
       		<div class="form-group">
           	   <span><b>Template</b></span>
               <?=Html::dropDownList('template','',$templates,['class'=>'form-control pull-right','id'=>'templates_dropdown'])?>
            </div>
            <div class="form-group" id="alredy-send-email" name="already-send-email" class="summary"></div>
            <div class="form-group">
           	   <span><b>CC</b></span>
               <input type="email" class="form-control" id="email_cc" name="email_cc" placeholder="CC:">
            </div>
            <div class="form-group">
           		<span><b>Subject</b></span>
               <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject">
            </div>
              <div class="form-group">
              <span><b>Html Content</b></span>
              <textarea  placeholder="write your html here" id="jetemailtemplate-html_content" name="jetemailtemplate-html_content" class="form-control"></textarea>
              </div>
              <div class="form-group">
              <span><input  type="checkbox"  id="check" name="check" value=""></span>
              <span><b>Save Templates</b></span>
              </div>
            <div> 
                <div id="template-content">
                	
                </div>
            </div> 

         	
       </form>
    </div>
    
</div>
<div id="wait_load" style="display: none;" align="center">
	<img alt="wait" src="<?= str_replace('/admin','',Yii::$app->request->baseUrl).'/images/wait.gif'?>">
</div>
<div class="jet_config_popup_overlay" style="display: none;"></div>
<button  class="btn btn-primary" id="preview" type="">Preview</button>
<script type="text/javascript">

	/*$('#templates_dropdown').change(function(){
		
		$.ajax({
	        method: "POST",
	        url: "<?= Yii::$app->request->baseUrl?>/reports/installations/get-template",
	        data: { template : $(this).val() }
	   }).done(function(data) {
		    $('#template-content').html(data);
	  });
	});*/
	$(document).ready(function(){
		var j$ = $;
    $('#preview').on('click',function(){
        var Html = $('#jetemailtemplate-html_content').val();
        $('#template-content').html(Html);
    });
    
});
	$("#sendEmail").click(function(){
		var j$ = $;
		var email_cc=merchant_ids=subject=templates_dropdown=html_content=$check='';
		 email_cc     = j$('#email_cc').val();
		 subject	  = j$('#subject').val();
		 merchant_ids = j$('#merchant_ids').val();
		 html_content = j$('#jetemailtemplate-html_content').val();
		 //console.log(document.getElementById('check').checked);
		 if (document.getElementById('check').checked){
		 	check = 1;
		 }
		 else{
		 	check = 0;
		 }
		 templates_dropdown = j$('#templates_dropdown').val();

		// All fields must be filled
		if(templates_dropdown.trim()=="" || subject.trim()=="" /* || msg.trim()=="" */){
			alert("Please fill all the fields!");
			return false;
		}
    
	    // Post ajax request 
	    var sendmailquery="<?= Yii::$app->request->baseUrl?>/reports/installations/send-mail";
	    //j$('#form-body-data').css('display','none');
	    j$('.jet_config_popup_overlay').css('display','block');
	    j$('#wait_load').css('display','block');
	    $.ajax({
	        method: "POST",
	        url: sendmailquery,
	        data: { template : templates_dropdown ,email_cc : email_cc,subject: subject, merchant_ids: merchant_ids,html_content: html_content ,check:check}
	   }).done(function(html) {
	   		if(html){
	   			j$('.jet_config_popup_overlay').css('display','none');
	   			j$('#wait_load').css('display','none');
	   			$('#mail-success-msg').html('<div style="" class="v_success_msg alert-success alert">Mail sent successfully..</div>');
	   		}
	   		else{
	   			j$('.jet_config_popup_overlay').css('display','none');
	   			j$('#wait_load').css('display','none');
	   			$('#mail-success-msg').html('<div style="" class="alert alert-danger">No Merchant to be selected..</div>');
	   		}
		    
	  });
	   
	   /*  j$('#clientMailId').val("");
	    j$('#subject').val("");
	    j$('#msg').val("");	  
	    alert("Mail Sent to : "+clientMailId);	      */
	});
	$(document).ready(function(){
	    $('#templates_dropdown').on('change',function(){
	        var Name = $(this).val();
	        var ids = $("#merchant_ids").val();
	        if(Name){
	            $.ajax({
	                type:'POST',
	                url:'<?= Yii::$app->request->baseUrl?>/reports/installations/get-merchant-list',
	                data:{template_name:Name,merchant_ids:ids },
	               success:function(html){
	                    $('#alredy-send-email').html(html); 
	                }
	            }); 
	        }
	    });
	    
	});
	$(document).ready(function(){
		$('#mailform').on('change', 'input[type=checkbox]', function(e) {
			var Name = this.name;
	        var ids = $("#merchant_ids").val();
			if (this.checked) {
				var search = ids.search(",");
				if(search>1){
					var ids = ids+','+Name;
	            	$('#merchant_ids').val(ids);	
				}
	            else{
	            	var abc = ids.search(Name);
	            	if(abc>1){
	            	}
	            	else{
	            		if(ids){
	            			var ids = ids+','+Name;
	            			$('#merchant_ids').val(ids);
	            		}
	            		else{
	            			var ids = ids+Name;
	            			$('#merchant_ids').val(ids);
	            		}
	            		
	            	}
	            	
	            }
		    }
	        else{
	        	var search = ids.search(",");
	        	if(search>1){
	        		length = Name.length;
	        		var abc = ids.substring(ids.indexOf(Name) +length);
	        		if(abc){
	        			var ids = ids.replace(Name+',', "");
	        			$('#merchant_ids').val(ids); 
	        		}
	        		else{
	        			var ids = ids.replace(','+Name, "");
	        			$('#merchant_ids').val(ids); 
	        		}
	        		
	        	}
	        	else{
	        		$('#merchant_ids').val(''); 
	        	}
	        	
	        }
	    });
	    
	});

$(document).ready(function(){
    $('#templates_dropdown').on('change',function(){
        var Name = $(this).val();
        if(Name){
            $.ajax({
                type:'POST',
                url:'<?= Yii::$app->request->baseUrl?>/reports/jet-email-template/htmltemplate',
                data:'name='+Name,
               success:function(html){
                    $('#jetemailtemplate-html_content').text(html); 
                }
            }); 
        }
    });
    
});
</script>