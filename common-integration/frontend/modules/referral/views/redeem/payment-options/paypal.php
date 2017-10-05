<?php 
use frontend\modules\referral\components\Redeem;

$selectedIds = '';
if(isset($post['selectedIds']) && isset($post['paymentId']))
{
	if($post['selectedIds'] == 'all') {
		$selectedIds = implode(',', $post['paymentId']);
	} else {
		$selectedIds = $post['selectedIds'];
	}
}

$requestedAmount = Redeem::caclulatePaymentAmountFromIds($selectedIds);

$name = '';
$email = '';
if(isset($post['formData']) && $post['formData']['redeem-option']=='paypal')
{
	$name = isset($post['formData']['name'])?$post['formData']['name']:'';
	$email = isset($post['formData']['email'])?$post['formData']['email']:'';
}
?>
<div>
	<!-- <h4>Paypal</h4> -->
	<p>
		<label>Name</label>
		<input type="text" name="name" placeholder="Your Name" value="<?= $name ?>" class="form-control required" />
	</p>

	<p>
		<label>Email</label>
		<input type="text" name="email" placeholder="Email" value="<?= $email ?>" class="form-control required" />
	</p>

	<p>
		<label>Amount</label>
		<input type="text" name="amount" placeholder="Amount" class="form-control required" / readonly="" value="<?= $requestedAmount ?>">
	</p>
</div>

<script type="text/javascript">
	function paypalValidate()
	{
		var validate = false;
		var email = $('input[name="email"]').val();

		if(ValidateEmail(email)) {
			validate = true;
			if($('#email-invalid').length)
				$('#email-invalid').remove();
		}
		else {
			if(!$('#email-invalid').length) {
				$('input[name="email"]').after("<p id='email-invalid' style='color:red;'>Email is invalid.</p>");
			}
		}

		return validate;
	}

	function ValidateEmail(email)
    {
	    if (/^\w+([\.-]?\ w+)*@\w+([\.-]?\ w+)*(\.\w{2,3})+$/.test(email)) {
	    	return true;
	    }
	    return false;
	}
</script>