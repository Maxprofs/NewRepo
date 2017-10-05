<div class="content-section">
	<div class="form new-section">
		<div class="jet-pages-heading">
			<div class="title-need-help">
		    	<h3 class="Jet_Products_style">FAQ</h3>
		    </div>
		</div>
		<div>
			<section class="cd-faq">

				<div class="cd-faq-items">
					<ul id="basics" class="cd-faq-group">

						<?php foreach($data as $row){ ?>
						<li id="<?= $row['id']?>">
							<a class="cd-faq-trigger" href="#0"><?= $row['query'] ?></a>
							<div class="cd-faq-content"  id="div_<?= $row['id']?>">
								<p><?= $row['description'] ?></p>
							</div> <!-- cd-faq-content -->
						</li>
						<?php } ?>
						
					</ul> <!-- cd-faq-group -->

				</div> <!-- cd-faq-items -->
				<a href="#0" class="cd-close-panel">Close</a>
			</section> <!-- cd-faq -->
		</div>
	</div>
</div>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/frontend/modules/walmart/assets/css/faqstyle.css"> <!-- Resource style -->
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/modernizr.js"></script>
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery-2.1.1.js"></script>
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery.mobile.custom.min.js"></script>
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/main.js"></script>
<script>
    $(document).ready(function () {
        /*function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }*/
        var id = window.location.hash.substr(1);
        //var id = getParameterByName('id');

        if(id!='')
        {
            document.getElementById('div_'+id).style.display = 'block';
        }


    });
</script>
