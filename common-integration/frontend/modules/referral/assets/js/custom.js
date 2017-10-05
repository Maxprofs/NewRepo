$(document).ready(function() {
	var ht = $('.navbar-default').height();
	if(ht > 0) {
		$('.referral-panel').css('margin-top', ht+30);
	}
	else {
		$('.referral-panel').css('margin-top', 0);
	}

	// Add slidedown animation to dropdown   
	$('.dropdown').on('show.bs.dropdown', function(e){
	  $(this).find('.dropdown-menu').first().stop(true, true).slideDown(300);
	});

	// Add slideUp animation to dropdown
	$('.dropdown').on('hide.bs.dropdown', function(e){
	  $(this).find('.dropdown-menu').first().stop(true, true).slideUp(300);
	});

	//Show/Hide Pie Chart
	$(".tabs").on("click", function(e){
		var id = $(this).attr('id');
		if(id == 'order') {
			$("#product_piechart").hide();
			$("#order_piechart").show();
		} else if(id == 'product') {
			$("#product_piechart").show();
			$("#order_piechart").hide();
		}
	});
});