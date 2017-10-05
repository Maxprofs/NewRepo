<?php

?>
<script>
	$('document').load(function(){
		$('#LoadingMSG').show();
		var url='<?=  \yii\helpers\Url::toRoute(['jet-report/processstatus']); ?>';
        var merchant_id='<?= MERCHANT_ID;?>';
		$.ajax({
            method: "post",
            url: url,
            data: {merchant_id : merchant_id,_csrf : csrfToken }
        })
        .done(function(msg) {
                $('#LoadingMSG').hide();                
       });
	});
</script>