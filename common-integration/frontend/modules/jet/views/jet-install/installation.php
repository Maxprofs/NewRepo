<?php 
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Installation;

$steps = Installation::$steps;
$nextStepUrl = Data::getUrl('jet-install/renderstep');
?>
	<div class="page-content jet-install">
		<div class="container">
			<div class="row">
				<div class="col-lg-offset-2 col-md-offset-2 col-lg-8 col-md-8 col-sm-12 col-xs-12">
					<div class="content-section">
						<div class="form new-section">
							<nav class="nav ">
							    <ul class="nav-bar">
									<?php 	$disabledFlag = false;
									foreach ($steps as $step_id => $step_data) : ?>
							<?php 	$class = 'completed';
									if($disabledFlag)
										$class = 'disabled';

									if($step_id == $currentStep) {
										$class = 'active';
										$disabledFlag = true;
									}
								?>
									<li class="<?= $class ?>" id="step-<?= $step_id ?>">
							            <a class="" data-url="<?php //Data::getUrl($step_data['url']) ?>" href="javascript:void(0);"><?= $step_data['name'] ?></a>
							        </li>
									<?php endforeach; ?>
							    </ul>
							</nav>
							<div class="content form new-section">
				     
								<div class="jet-pages-heading">
									<div class="title-need-help">
										<h2 class="page-sub-title">Step <?= $currentStep?>: <?= $steps[$currentStep]['name'] ?></h2>										
									</div>
									<div class="registration-config-menu">										
										<a class="btn btn-primary" id="help-btn" target="_blank" href="">Need Help ?</a>										
										<?php 
											if ($currentStep==2 || $currentStep==3)
											{
												?>
													<button class="btn btn-primary" id="<?= $currentStep;?>" onclick="showMeVideo(this.id)" type="button" title="Checkout the jet-integration app overview"><span>Show Video</span></button>
												<?php 
											}
										?>
												
										<button class="btn next btn-primary" type="button">Next</button>
									</div>
									<div class="clear"></div>
								</div>
								<div class="" id="step_content"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="configuration_model">
		<div id="myModal" class="modal">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
		            </div>
		            <div class="modal-body">
		                <iframe width="560" height="400" frameborder="0" allowfullscreen=""></iframe>
		            </div>
		        </div>
		    </div>
		</div>
	</div>	

<script type="text/javascript">
	
	var csrfToken = $('meta[name="csrf-token"]').attr("content");

	function loadStep(stepId)
	{
		$('#LoadingMSG').show();

		CURRENT_STEP_ID = stepId;

	    $.ajax({
	      method: "post",
	      url: "<?= $nextStepUrl ?>",
	      data: {step : stepId, _csrf : csrfToken},
	      dataType : "json"
	    })
	    .done(function(response){
	    	$('#LoadingMSG').hide();
			if(response.success) {
				//set content of current step
				$("#step_content").html(response.content);

				//set ttile of current step
				var title = "Step " + stepId + ": " + response.steptitle; 
				$(".page-sub-title").text(title);

				//set help of current step
				changeHelpUrl(stepId);
			}
			else if(response.error) {
				alert(response.message);
			} else {
				alert("Something Went Wrong");
			}
	    });
	}

	function nextStep()
	{
		$('#LoadingMSG').show();

		var finalStepId = "<?= Installation::getFinalStep() ?>";
		
		$.ajax({
	      method: "post",
	      url: "<?= Data::getUrl('jet-install/savestep') ?>",
	      data: {step : CURRENT_STEP_ID, _csrf : csrfToken},
	      dataType : "json"
	    })
	    .done(function(response) {
			if(response.success) 
			{
				if(CURRENT_STEP_ID == finalStepId)
				{
					window.location.href = "<?= Data::getUrl('site/index?tour') ?>";
				}
				else
				{
					var currentStep = parseInt(CURRENT_STEP_ID);
			    	var nextStep = currentStep+1;

			    	//make previous step completed and next step active in progress bar
					var currStepId = 'step-'+CURRENT_STEP_ID;
					var nextStepId = 'step-'+nextStep;
					document.getElementById(currStepId).className = "";
					document.getElementById(nextStepId).className = "";
					$("#"+currStepId).addClass('completed');
					$("#"+nextStepId).addClass('active');
					
					loadStep(nextStep);
				}
			} 
			else 
			{
				console.log("last step not saved.");
			}
	    });
	}

	function changeHelpUrl(stepId)
	{
		var url = "<?= Data::getUrl('jet-install/help') ?>";
		url += '?step='+stepId;

		if(stepId != 1) {
			$('#help-btn').attr("href", url);
			$('#help-btn').show();
		} else {
			$('#help-btn').hide();
		}
	}

	function UnbindNextClick()
	{
		$('.next').unbind('click');
	}

$(document).ready(function(){
	loadStep(<?= $currentStep ?>);
});
function showMeVideo(id)
{
	var src ="";
	// Node == new video url for each section
	if(id==2)	
		src = "https://www.youtube.com/embed/BhHMCTBWvjY";
	else 
		src = "https://www.youtube.com/embed/BhHMCTBWvjY";
	$('.configuration_model #myModal').modal('show');
    $('.configuration_model #myModal iframe').attr('src', src);
    //$('.model').attr("style", "display: block !important");
}
$('.configuration_model #myModal .close').click(function () {
    $('.configuration_model #myModal iframe').removeAttr('src');
    $('.configuration_model #myModal').modal('show');
});
</script>