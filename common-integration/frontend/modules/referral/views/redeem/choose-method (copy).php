<?php
use frontend\modules\referral\components\Redeem;
use frontend\modules\referral\components\Helper;
use frontend\modules\referral\components\Dashboard;

$canRedeem = Redeem::canRedeem();

$totalAmount = Dashboard::getTotalAmount();
$activeAmount = Dashboard::getActiveAmount();
$referralCount = Dashboard::getReferralCount();

$paymentOptions = [];

$selectedIds = '';
if(isset($post['selectedIds']) && isset($post['paymentId']))
{
	if($post['selectedIds'] == 'all') {
		$selectedIds = implode(',', $post['paymentId']);
	} else {
		$selectedIds = $post['selectedIds'];
	}
}

$redeem_option = '';
if(isset($post['formData']) && isset($post['formData']['redeem-option'])) {
	$redeem_option = $post['formData']['redeem-option'];
}
?>
<div class="content-section">
	<div class="form new-section">
		<ul class="refferal-progressbar">
			<li class="completed">Choose payment</li>
			<li class="active">Choose method</li>
		</ul>
		<div class="amount">
	        <span class="total">Total Amount : $<?= $totalAmount ?></span>
	        <span class="usable">Usable Amount : $<?= $activeAmount ?>/$200</span>
	        <span class="non-usable">No of Referrals  : <?= $referralCount ?>/10</span>
	    </div>

<?php if($canRedeem) { ?>
<?php 
		$paymentOptions = Redeem::getPaymentOptions();
?>
	    <div class="redeem-panel">
<?php 	if(count($paymentOptions)) {	?>

	    	<form method="post" name="frm" action="<?= Redeem::getMethodFormAction() ?>">
	    		<ul id="payment-options-wrapper">
		<?php 	foreach ($paymentOptions as $key => $paymentOption) {	?>
					<li>
						<input class="select-redeem-option" type="radio" name="redeem-option" value="<?= $paymentOption['code'] ?>" id="payment-option-<?= $paymentOption['code'] ?>" <?= ($redeem_option==$paymentOption['code'])?'checked="checked"':'' ?>/>
						<label for="payment-option-<?= $paymentOption['code'] ?>"><?= $paymentOption['title'] ?></label>

						<div style="display:none;" id="payment-form-<?= $paymentOption['code'] ?>" class="payment-form">
							<?= $this->render('payment-options/'.$paymentOption['code'].'.php', ['post'=>$post]); ?>
						</div>
					</li>
		<?php 	}	?>
				</ul>
				<input type="hidden" name="paymentIds" value="<?= $selectedIds ?>" />
				<div>
					<button type="button" onclick="submitRedeemRequest()" class="btn btn-primary">Submit</button>
				</div>
	    	</form>

<?php 	} else {	?>

			<div>
				<p>No Redeem Options Available.</p>
	    	</div>

<?php 	}	?>
	    </div>

<?php } else { ?>

	<div>
		<p>You can not redeem now.</p>
	</div>

<?php } ?>

	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('.select-redeem-option').on('click', function() {
			//get selected option
			var option = $(this).val();

			activatePaymentOption(option);
		});
	});

	function activatePaymentOption(option)
	{
		//hide and disable all options form
		$('.payment-form').hide();
		disableAllFields();

		var id = '#payment-form-'+option;

		//show and enable only selected option
		$(id).show();
		enableFieldsSection(option);
	}

<?php if($redeem_option != '') : ?>
	activatePaymentOption('<?= $redeem_option ?>');
<?php endif; ?>

	function submitRedeemRequest()
	{
		var option = '';
		$.each($(".select-redeem-option:checked"), function(){
			option = $(this). val();
		});

		if(option == '') {
			$('#payment-options-wrapper').css({"border": "1px solid red"});
			$('#payment-options-wrapper').after("<p id='redeem-option-error' style='color:red;'>Please Choose Redeem Option.</p>");
		}
		else {
			$('#payment-options-wrapper').css({"border": ""});
			$('#redeem-option-error').remove();

			if(validate(option)) {
				$('form[name="frm"]').submit();
			}
		}
	}

	function disableAllFields()
	{
		$.each($(".select-redeem-option"), function(){
			var code = $(this).val();
			var id = '#payment-form-' + code;

			$.each([':input', 'textarea', 'select'], function( index, value ) {
				var selector = id + " " + value;
				if($(selector).length) {
					$(selector).attr("disabled", true);
				}
			});
		});
	}

	function enableFieldsSection(section)
	{
		var id = "#payment-form-" + section;
		$.each([':input', 'textarea', 'select'], function( index, value ) {
			var selector = id + " " + value;
			if($(selector).length) {
				$(selector).attr("disabled", false);
			}
		});
	}

	function validate(option)
	{
		var validateStatus = true;

<?php 	if(count($paymentOptions)) {
			foreach ($paymentOptions as $paymentOption) { 	?>

				if('<?= $paymentOption['code'] ?>' == option && typeof <?= $paymentOption['code'] ?>Validate == 'function') {
					var validateStatus = <?= $paymentOption['code'] ?>Validate();
				}

<?php 		}
		} 	?>
		//if(validateStatus)
		{
			if(!validateInputFields(option))
				validateStatus = false;
			if(!validateTextarea(option))
				validateStatus = false;
			if(!validateDropdown(option))
				validateStatus = false;
		}
		return validateStatus;
	}

	function validateInputFields(section)
	{
		var validate = true;

		var id = "#payment-form-" + section;
		var selector = id + " :input";
		if($(selector).length) {
			$(selector).each(function() {
				var name = $(this).attr('name');
				var id  = name + '-error';
				if($(this).hasClass('required') && $(this).val()=='') {
					validate = false;
					if(!$('#'+id).length)
						$(this).after("<p id='"+id+"' style='color:red;'>Please enter "+name+".</p>");
				} else {
					$('#'+id).remove();
				}
			});
		}

		return validate;
	}

	function validateTextarea(section)
	{
		var validate = true;

		var id = "#payment-form-" + section;
		var selector = id + " textarea";
		if($(selector).length) {
			$(selector).each(function() {
				var name = $(this).attr('name');
				var id  = name + '-error';
				if($(this).hasClass('required') && $(this).val()=='') {
					validate = false;
					if(!$('#'+id).length)
						$(this).after("<p id='"+id+"' style='color:red;'>Please enter "+name+".</p>");
				} else {
					$('#'+id).remove();
				}
			});
		}

		return validate;
	}

	function validateDropdown(section)
	{
		var validate = true;

		var id = "#payment-form-" + section;
		var selector = id + " select";
		if($(selector).length) {
			$(selector).each(function() {
				var name = $(this).attr('name');
				var id  = name + '-error';
				if($(this).hasClass('required') && $(this).find(":selected").val()=='') {
					validate = false;
					if(!$('#'+id).length)
						$(this).after("<p id='"+id+"' style='color:red;'>Please choose "+name+".</p>");
				} else {
					$('#'+id).remove();
				}
			});
		}

		return validate;
	}
</script>