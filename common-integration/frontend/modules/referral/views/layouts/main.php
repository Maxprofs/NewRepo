<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use frontend\widgets\Alert;
use frontend\modules\referral\assets\AppAsset;
use frontend\modules\referral\components\Helper;

$valuecheck = Yii::$app->request->get('shop');
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<html lang="<?= Yii::$app->language ?>">
	<head>
	<link rel="icon" href="<?php echo Yii::$app->request->baseUrl?>/images/favicon.ico">
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta content="INDEX,FOLLOW" name="robots">
	<meta name="google-site-verification" content="vJ8BS5PRRVFvpk5dCrUMgTZdDVIuWKMbvmCo2QTIEag" />

	<link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/font-awesome.min.css">
	<!-- <link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/bootstrap.css">
	<link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/style.css"> -->

	<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript">
	    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	    ga('create', 'UA-63841461-1', 'auto');
	    ga('send', 'pageview');

	(function (h, o, t, j, a, r) {
        h.hj = h.hj || function () {
                (h.hj.q = h.hj.q || []).push(arguments)
            };
        h._hjSettings = {hjid: 305411, hjsv: 5};
        a = o.getElementsByTagName('head')[0];
        r = o.createElement('script');
        r.async = 1;
        r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
        a.appendChild(r);
    })(window, document, '//static.hotjar.com/c/hotjar-', '.js?sv=');
	</script>

	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode("Shopify App Referral Program | CedCommerce") ?></title>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
	</head>

	<body class="iframe-body referral-panel">
		<?php $this->beginBody() ?>
		<div class="overlay" style="display: none;" id="LoadingMSG">
			<div id="fountainG">
				<div id="fountainG_1" class="fountainG"></div>
				<div id="fountainG_2" class="fountainG"></div>
				<div id="fountainG_3" class="fountainG"></div>
				<div id="fountainG_4" class="fountainG"></div>
				<div id="fountainG_5" class="fountainG"></div>
				<div id="fountainG_6" class="fountainG"></div>
				<div id="fountainG_7" class="fountainG"></div>
				<div id="fountainG_8" class="fountainG"></div>
			</div>
		</div>
		<div class="wrap ced-jet-navigation-mbl">
<?php 	if (!Yii::$app->user->isGuest) { ?>
	        <div class="trial-nav-wrap">
	            <?= $this->render('nav.php') ?>
	        </div>
	<?php 	} ?>
		    <!-- ========================= sticky header ===============================-->
		    <div class="sticky-header"></div>
		    <!-- ========================= sticky header end ===============================-->

		 	<div class="fixed-container-body-class">
		        <?= Breadcrumbs::widget([
		            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
		        ]) ?>
		        	        
		    	<?= Alert::widget() ?>
		        <?= $content ?>	    
		    	<div id="helpSection" style="display:none"></div>
			</div>
		</div>
		<footer class="container-fluid footer-section">
			<div class="contact-section">
				<div class="row">
					<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
						<a title="Click Here to Submit a Support Ticket" href="http://support.cedcommerce.com/" target="_blank">
							<div class="ticket">
								<div class="icon-box">
									<div class="image">
										<img src="<?= Yii::$app->request->baseUrl ?>/frontend/modules/referral/assets/images/ticket.png">
									</div>
								</div>
								<div class="text-box">
									<span>Submit issue via ticket</span>
								</div>
								<div class="clear"></div>
							</div>
						</a>
					</div>
					<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
						<a title="Click Here to Contact us through Mail" href="mailto:shopify@cedcommerce.com" target="_blank">
							<div class="mail">
								<div class="icon-box">
									<div class="image">
										<img src="<?= Yii::$app->request->baseUrl ?>/frontend/modules/referral/assets/images/mail.png">
									</div>
								</div>
								<div class="text-box">
									<span>Send us an E-mail</span>
								</div>
								<div class="clear"></div>
							</div>
						</a>
					</div>
					<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<a title="" href="javascript:void(0)">
						<div class="skype">
							<div class="icon-box">
								<div class="image">
									<img src="<?= Yii::$app->request->baseUrl ?>/frontend/modules/referral/assets/images/skype.png">
								</div>
							</div>
							<div class="text-box">
								<span>Connect via skype</span>
							</div>
							<div class="clear"></div>
						</div>
						</a>
					</div>
				</div>
			</div>
		</footer>
		<div class="copyright-section">
			<div class="row">
				<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<a title="Click Here to Submit a Support Ticket" href="https://play.google.com/store/apps/details?id=com.cedcommerce.shopifyintegration&hl=en" target="_blank">
						<img src="<?= Yii::$app->request->baseUrl ?>/frontend/modules/referral/assets/images/GooglePlay.png" />
					</a>
					<a title="Click Here to Contact us through Mail" href="https://itunes.apple.com/us/app/cedbridge-for-shopify/id1186746708?ls=1&mt=8" target="_blank">
						<img src="<?= Yii::$app->request->baseUrl ?>/frontend/modules/referral/assets/images/App-Store.png" />
					</a>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<div class="copyright">
						<span>Copyright © <?= date('Y');?> CEDCOMMERCE | All Rights Reserved.</span>
					</div>
				</div>
			</div>
		</div>
		<?php $this->endBody() ?>
		<script type="text/javascript">
		    $(document).ready(function () {
		        $('.dropdown').addClass('dropdown1').removeClass('dropdown');
		    });
		</script>
	</body>
</html>
<?php $this->endPage() ?>
