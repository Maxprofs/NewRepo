<?php
use yii\helpers\Html;
	$this->title = 'Send Mail to Clients( No Revenue With Live Products)';
	$templates = [];
	$templates['-'] = '--Select Template--';
	foreach($data['templates'] as $template){
		$templates[$template['template_path']] = $template['custom_title'];
	}
?>              
<script type="text/javascript" src="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/js/jquery.wysiwygEditor.js"></script>
<link rel="stylesheet" href="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/css/jquery.wysiwygEditor.css"/>

<!-- quick email widget -->
 <div class="box box-info">
    <div class="box-header">
        <h3 class="box-title"> <i class="fa fa-envelope"></i> Quick Email for Uninstalled merchants</h3>
    </div>
    <div id="mail-success-msg"></div>
    <div class="box-body" id="form-body-data">
    	<div class="box-footer clearfix" style="margin-top: 2%;">
	         <button class="pull-right btn btn-default" id="sendEmail" >Send <i class="fa fa-arrow-circle-right"></i></button>
	    </div>
       <form action="#" method="post">
       		<input type="hidden" id="merchant_ids" name="merchant_ids" value="<?= implode(',',$data['selection']) ?>">
       		<div class="form-group">
           	   <span><b>Template</b></span>
               <?=Html::dropDownList('template','',$templates,['class'=>'form-control pull-right','id'=>'templates_dropdown'])?>
            </div>
            
            <div class="form-group">
           	   <span><b>CC</b></span>
               <input type="email" class="form-control" id="email_cc" name="email_cc" placeholder="CC:">
            </div>
            <div class="form-group">
           		<span><b>Subject</b></span>
               <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject">
            </div>
            <div> 
            	<span><b>Template Preview</b></span>
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
<script type="text/javascript">

	$('#templates_dropdown').change(function(){
		
		$.ajax({
	        method: "POST",
	        url: "<?= Yii::$app->request->baseUrl?>/reports/installations/get-template",
	        data: { template : $(this).val() }
	   }).done(function(data) {
		    $('#template-content').html(data);
	  });
	});
	$("#sendEmail").click(function(){
		var email_cc=merchant_ids=subject=templates_dropdown='';
		 email_cc     = j$('#email_cc').val();
		 subject	  = j$('#subject').val();
		 merchant_ids = j$('#merchant_ids').val();
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
	        data: { template : templates_dropdown ,email_cc : email_cc,subject: subject, merchant_ids: merchant_ids }
	   }).done(function() {
		    j$('.jet_config_popup_overlay').css('display','none');
	   		j$('#wait_load').css('display','none');
	   		$('#mail-success-msg').html('<div style="" class="v_success_msg alert-success alert">Mail sent successfully..</div>');
	  });
	   
	   /*  j$('#clientMailId').val("");
	    j$('#subject').val("");
	    j$('#msg').val("");	  
	    alert("Mail Sent to : "+clientMailId);	      */
	});
</script>