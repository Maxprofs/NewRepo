<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\neweggcanada\components\Data;
$this->title = 'Manufacturer Configuration';
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="wait_load" style="display: none;" align="center">
	<img alt="wait" src="<?= Yii::$app->request->baseUrl.'/images/482.gif'?>">
</div>
<div id="mail-success-msg"></div>
<div class="jet-configuration-index content-section">
    <div class="jet_configuration form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu">
                <button type="button" id="instant-help" class="btn btn-primary">Help</button>
                <input type="submit" name="submit" value="save" class="btn btn-primary" id="savedata">
                <a class="btn btn-primary"
                           href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/manufacturer/index">Back</a>
            </div>
            <div class="clear"></div>
        </div>
    
<!--        <input type="hidden" name="<?/*= Yii::$app->request->csrfParam; */?>"
               value="<?/*= Yii::$app->request->csrfToken; */?>"/>-->

        <div class="ced-entry-heading-wrapper">
            <div class="fieldset enable_api" id="api-section">

                <table class="table table-striped table-bordered" cellspacing="0">
                    <tbody>
                    <tr id="field">
                        <td class="value_label" id= "field">
                            <span id="label">Manufacturer Name</span>
                        </td>
                        <td>
                            <span><input id="mn" type="text" name="mn"
                                         value="" class="form-control" required oninvalid="this.setCustomValidity('Manufacturer  name cannot be empty.')" 
                                    onchange="this.setCustomValidity('')"></span>
                        </td>
                    </tr>
                    <tr id = "field1">
                        <td class="value_label" id ="field1">
                            <span id="label1">Manufacturer URL</span>
                        </td>
                        <td>
                            <!-- <span><textarea rows="4" cols="50" id="authorization" name="authorization"></textarea></span>-->
                            <span><input type="text" id="mu" name="mu"
                                         value="" class="form-control" placeholder="http://www.example.com"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="value_label">
                            <span>Manufacturer Support Email</span>
                        </td>
                        <td>
                            <span><input type="email" id="mse" name="mse"
                                         value="" class="form-control" placeholder="abc@domail.com"></span>
                        </td>
                    </tr>
                     <tr>
                        <td class="value_label">
                            <span>Manufacturer Support Phone#</span>
                        </td>
                        <td>
                            <span class="input-group-addon"><i class="icon-phone"></i><input type="test" id="msp" name="msp"
                                         value="" class="form-control"></span>
                            <span><input name="customerRedemption"  value="enable"  type="radio" id = "us" checked>
                                <span class="country" >US Phone Number</span>
                                 <input name="customerRedemption"  value="Disable"  type="radio" id="in">
                                <span class="country">International Phone Number</span>
                            </label></span>
                        </td>
                    </tr>
                         <tr>
                        <td class="value_label">
                            <span>Manufacturer Support URL#</span>
                        </td>
                        <td>
                            <span><input type="test" id="msu" name="msu"
                                         value="" class="form-control" placeholder="http://www.example.com"></span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

              <!-- Manufacturer setting start-->



  

       
    </div>
</div>

<style>
    .jet-configuration-index .value_label {
        width: 50%;
    }

    .jet-configuration-index .table-striped select, .jet-configuration-index .form-control {
        width: 100%;
        display: inline-block;
        padding-left: 10px;
    }

    .jet-configuration-index .value {
        border: medium none !important;
        display: inline-block;
        width: 100%;
    }

    #custom_price_csv span, #custom_title_csv span {
        width: 85%;
        display: inline-block;
    }

    .jet-configuration-index .help_jet {
        display: inline-block;
    }

    .help_jet {
        width: 50px !important;
    }

    .jet-configuration-index .value_label {
        width: 50%;
    }
#field #label:after {
  content:"*";
  color:red;
}
#field #label:after {
  content:"*";
  color:red;
}
#field1 #label1:after {
  content:"*";
  color:red;
}
</style>

<script type="text/javascript">
    $('#instant-help').click(function () {
        var configQuicktour = introJs().setOptions({
            doneLabel: 'Finish',
            showStepNumbers: false,
            exitOnOverlayClick: false,
            steps: [
                {
                    element: '#api-section',
                    intro: 'Fill Manufacturer details.',
                    position: 'bottom'
                },

            ]
        });

        configQuicktour.start().oncomplete(function () {
            window.location.href = '<?= Data::getUrl("site/index") ?>';
        });
    });
</script>

<?php $get = Yii::$app->request->get();
if (isset($get['tour'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var configQuicktour = introJs().setOptions({
                doneLabel: 'Finish',
                showStepNumbers: false,
                exitOnOverlayClick: false,
                steps: [
                    {
                        element: '#api-section',
                        intro: 'Fill Manufacturer detail.',
                        position: 'bottom'
                    },
                ]
            });

            configQuicktour.start().oncomplete(function () {
                window.location.href = '<?= Data::getUrl("site/index") ?>';
            });
        });
    </script>
<?php endif; ?>
    <script type="text/javascript">
    $("#msp").keyup(function() {
    	var a = document.getElementById('in').checked;
    	if(a){
    		$(this).val($(this).val().replace(/^(\d{4})(\d{8})(\d)+$/, "$1-$2EXT$3"));
    	}
    	else{
    		$(this).val($(this).val().replace(/^(\d{3})(\d{3})(\d)+$/, "$1-$2-$3"));
    	}
		
		});

 $('#savedata').on('click',function(){
 		var mn=mu=mse=msp=msu='';
		 mn     = j$('#mn').val();
		 mu	  = j$('#mu').val();
		 mse = j$('#mse').val();
		 msp = j$('#msp').val();
		 msu = j$('#msu').val();
		  mn = j$('#mn').val();
		if(mn.trim()==""){
			alert("Manufacturer Name  must be filled!");
			return false;
		}
		if(mu.trim()==""){
			alert("Manufacturer URL  must be filled!");
			return false;
		}
	   var createmanufacturer="<?= Yii::$app->request->baseUrl?>/neweggcanada/manufacturer/createmanufacturer";
	   $('#wait_load').css('display','block');
	     $.ajax({
	        method: "POST",
	        url: createmanufacturer,
	        data: { mn : mn ,mu : mu,mse: mse, msp: msp,msu: msu },
	        success:function(data){
	        	        $('#wait_load').css('display','none');
	        	        var obj = JSON.parse(data);
	        	        if(obj.success){
	        	        	$('#mail-success-msg').html('<div style="" class="v_success_msg alert-success alert">'+obj.success+'</div>');
	        	        }
	        	        else{
	        	        	$('#mail-success-msg').html('<div style="" class="alert alert-danger">'+obj.error+'</div>');
	        	        }
	        	        
	        
	                }
	   });
	       
	    });

    </script>