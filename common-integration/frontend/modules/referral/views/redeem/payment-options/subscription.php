<?php 
use frontend\modules\referral\components\Redeem;

$requestedSubscription = 0;
if(isset($post['selectedIds']) && isset($post['paymentId']))
{
	if($post['selectedIds'] == 'all') {
		$requestedSubscription = count($post['paymentId']);
	} else {
		$requestedSubscription = count(explode(',', $post['selectedIds']));
	}
}

$account = '';
$other_account = '';
$app = '';
if(isset($post['formData']) && $post['formData']['redeem-option']=='subscription')
{
	$account = isset($post['formData']['account'])?$post['formData']['account']:'';
	$other_account = isset($post['formData']['other-account'])?$post['formData']['other-account']:'';
	$app = isset($post['formData']['app'])?$post['formData']['app']:'';
}

$appOptions = ['jet'=>'Jet Integration', 'walmart'=>'Walmart Integration', 'newegg'=>'Newegg Integration'];
?>
<div>
	<!-- <h4>Subscription</h4> -->
	<div id="shopify-account-field-wrapper">
		<p>
			<input class="shopify-account" type="radio" name="account" value="self" class="form-control" id="account-self" <?= ($account=='self')?'checked="checked"':'' ?>/>
			<label for="account-self">Subscription for your account</label>
		</p>
		<p>
			<input class="shopify-account" type="radio" name="account" value="other" class="form-control" id="account-other" <?= ($account=='other')?'checked="checked"':'' ?>/>
			<label for="account-other">Subscription for Other account</label>
		</p>
	</div>

	<p id="other-account-field" style="display:none;">
		<label for="shop-url">Shop-Url</label>
		<input type="input" name="other-account" value="<?= $other_account ?>" placeholder="example.myshopify.com" class="form-control" disabled="" id="shop-url" />
	</p>

	<p id="other-account-field">
		<label for="choose-app">Choose App</label>
		<select id="choose-app" class="form-control required" name="app">
			<option value="">choose app</option>
		<?php foreach ($appOptions as $key => $value) : ?>
			<?php if($app == $key) : ?>
				<option value="<?= $key ?>" selected="selected"><?= $value ?></option>
			<?php else : ?>
				<option value="<?= $key ?>"><?= $value ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
		</select>
	</p>

	<p>
		<label for="subscription-months">Subscription Months</label>
		<input type="input" name="months" placeholder="Enter Subscription Months" class="form-control required" id="subscription-months" readonly="" value="<?= $requestedSubscription ?>" />
	</p>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$('.shopify-account').on('click', function() {
			var value = $(this).val();
			toggleOtherAccShopUrlField(value);
		});
	});

	function toggleOtherAccShopUrlField(value)
	{
		if(value == 'other') {
			$('#other-account-field').show();
			$('#other-account-field input').prop('disabled', false);
		} else {
			$('#other-account-field').hide();
			$('#other-account-field input').prop('disabled', true);
		}
	}

	function subscriptionValidate()
	{
		var validate = false;
		var shopurl = '';
		var option = '';
		$.each($(".shopify-account:checked"), function(){
			option = $(this).val();
		});

		if(option == '') {
			showError();
		} else {
			hideError();

			if(option != 'other') {
				validate = true;
			} else {
				if((shopurl=$('#shop-url').val()) == '') {
					if(!$('#shop-url-error').length)
						$('#shop-url').after("<p id='shop-url-error' style='color:red;'>Please enter shop-url.</p>");
				} else {
					$('#shop-url-error').remove();
					if(validateShopUrl(shopurl)) {
						validate = true;
						$('#shop-url-error').remove();
					} else {
						$('#shop-url').after("<p id='shop-url-error' style='color:red;'>Invalid shop-url.</p>");
					}
				}
			}
		}
		return validate;
	}

	function showError()
	{
		$('#shopify-account-field-wrapper').css({"border": "1px solid red"});
		if(!$('#shopify-account-error').length)
			$('#shopify-account-field-wrapper').after("<p id='shopify-account-error' style='color:red;'>Please choose one option.</p>");
	}

	function hideError()
	{
		$('#shopify-account-field-wrapper').css({"border": ""});
		$('#shopify-account-error').remove();
	}

	function validateShopUrl(shopurl)
	{
		var re = new RegExp(".*.myshopify.com$");
		if (re.test(shopurl)) {
			var index = shopurl.indexOf(".myshopify.com");
			if(index > 3) {
		    	return true;
			}
		}
		
		return false;
	}

<?php if($account != '') : ?>
	toggleOtherAccShopUrlField('<?= $account ?>');
<?php endif; ?>	
</script>