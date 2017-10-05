<?php
use frontend\modules\referral\components\Redeem;
use frontend\modules\referral\components\Helper;

$canRedeem = Redeem::canRedeem();

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
?>


<div class="content-section">
	<div class="form new-section">
		<ul class="refferal-progressbar">
			<li class="active">Choose payment</li>
			<li>Choose method</li>
		</ul>

		<?= $this->render('../stats/stats.php') ?>
	    
	    <!-- <div>
			<h3>Step 1</h3>
		</div> -->
		<?php if(!$canRedeem) { ?>

				<p>You can not redeem now because you are not fulfilling the criteria. Please read <a href="<?= Helper::getUrl('account/dashboard') ?>#terms-conditions">Terms and Condition</a> for more details.</p>

		<?php } else { ?>
			
			<form name="frm" method="post" action="<?= Redeem::getPaymentFormAction() ?>">
				<div class="table-wrapper grid-view">
					<table class="table table-striped table-bordered product-table1" cellspacing="0">
						<thead>
							<tr>
								<th>Referral Shop Name</th>
								<th>App</th>
								<th><input class="check" type="checkbox" id="select_all" value="1" onclick="selectAll(this)" />Amount</th>
							</tr>
						</thead>

						<tbody id="payment-list">
						</tbody>

						<tfoot>
							<tr>
								<td colspan="2">Total Amount</td>
								<td id="total-amount">$0.00</td>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="btn-wrap">
					<input type="hidden" id="selectedids" name="selectedIds" value="" />
					<button type="button" class="btn btn-primary" onclick="submitForm()">Next</button>
				</div>
			</form>
		</div>
	</div>
<script type="text/javascript">

	$(document).ready(function() {
		var data = <?= json_encode($data) ?>;
		createList(data);
	});

	//createList(msg, {sku:"AKT", title:"3M"}, ['361133717','361133725','361133745']);
	function createList(object, filters=null, checked=null)
    {
    	//console.log(object);
    	if(Object.keys(object).length)
    	{
    		$.each(object,function(index, payment) {
				var paymentId = payment.id;
    			var amount = payment.amount;

    			var referral_shop_name = payment.shop_name;
    			var referral_username = payment.username;

    			var app = payment.app;

    			var checkedStr = '';
    			if(checked != null && Array.isArray(checked)) {
    				if($.inArray(paymentId.toString(), checked) != -1) {
    					checkedStr = 'checked="checked"';
    				}
    			}

    			/*var flag = false;
    			if(filters !== null && (typeof filters === "object")) {
    				$.each(filters,function(key, value) {
    					if(key == "sku" && sku.indexOf(value) == -1) {
    						flag = true;
							return false;
    					}

    					if(key == "title" && title.indexOf(value) == -1) {
    						flag = true;
							return false;
    					}
    				});
    			}

    			if(flag)
    				return true;*/

    			var html = '<tr>';
    			html += '<td><a target="_blank" href="http://'+referral_username+'">'+referral_shop_name+'</a></td>';
    			html += '<td>'+app+'</td>';
    			//html += '<td>'+amount+'</td>';
    			html += '<td><input type="checkbox"'+checkedStr+' name="paymentId[]" value="'+paymentId+'" class="payment_check" onclick="selectOne(this)" /><span class="price">'+amount+'</span></td>';
    			html += '</tr>';
    			$('#payment-list').append(html);
    		});
    	}
    	else
    	{
			var html = '<tr><td colspan="4">No records found.</td></tr>';
			$('#payment-list').append(html);
		}
    }

    function selectAll(element)
    {
    	if($(element).is(':checked')) {
    		$('.payment_check').prop('checked', true);
    		$('#selectedids').val('all');
    	} else {
    		$('.payment_check').prop('checked', false);
    		$('#selectedids').val('');
    	}
    }

    function selectOne(element)
    {
    	var value = $(element).val();
		var selected = $('#selectedids').val();
		var selectedArr = selected.split(",");

		if($("#select_all").is(':checked'))
		{
			$("#select_all").prop("checked", false);

			var paymentIds = [];
			$.each($("input[name='paymentId[]']:checked"), function(){
				paymentIds.push($(this). val());
			});

			if(paymentIds.length) {
				var join = paymentIds.join(","); 
	    		$('#selectedids').val(join);
			}
		}
		else
		{
			var allCheckflag = true;
			$.each($(".payment_check"), function(){
				if($(this).is(':checked') === false) {
					allCheckflag = false;
					return false;
				}
			});

			if(allCheckflag) {
				$('#selectedids').val('all');
				$("#select_all").prop("checked", true);
			} else {
		    	if($(element).is(':checked')) {
		    		if(selected == '') {
			    		$('#selectedids').val(value);
			    	}
			    	else if($.inArray(value, selectedArr) === -1) {
				    	$('#selectedids').val(selected+","+value);
			    	}
		    	} else {
		    		/*if(selectedArr.length === 1)
		    		{
		    			if($.inArray(value.toString(), selectedArr) != -1) {
		    				selectedArr = [];
		    				$('#selectedids').val('');
		    			}
		    		}
		    		else
		    		{*/
		    			//selectedArr = selectedArr.remove(value);
		    			selectedArr = removeFromArray(value, selectedArr);
		    			var join = selectedArr.join(","); 
		    			$('#selectedids').val(join);
		    		//}
		    	}
		    }
    	}
    	updateTotalAmount();
    }

    function removeFromArray(value, array)
    {
		var index = array.indexOf(value);
		if (index > -1) {
    		array.splice(index, 1);
		}
		return array;
    }

    function submitForm()
    {
    	var paymentIds = [];
    	$. each($("input[name='paymentId[]']:checked"), function(){
			paymentIds.push($(this). val());
		});
    	var selectCount = paymentIds.length;

    	//var selectCount = $('#selectedids').val();
    	//if(selectCount != '')
    	if(selectCount)
    	{
    		$('form[name="frm"]').submit();
    	} 
    	else
    	{
    		alert('please select payments.');
    	}
    }

    function updateTotalAmount()
    {
    	var total = 0.00;

    	$.each($(".payment_check"), function(){
			if($(this).is(':checked') === true) {
				total += parseFloat($(this).parent().children('.price').html());
			}
		});

		$('#total-amount').html('$'+total);
    }
</script>

<?php } ?>