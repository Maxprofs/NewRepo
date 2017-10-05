<?php

/* @var $this \yii\web\View */
/* @var $exception \yii\base\Exception */
/* @var $handler \yii\web\ErrorHandler */
/*----------------------Call Stack Trace Variables--------------------------*/
/* @var $file string|null */
/* @var $line integer|null */
/* @var $class string|null */
/* @var $method string|null */
/* @var $index integer */
/* @var $lines string[] */
/* @var $begin integer */
/* @var $end integer */
/* @var $args array */
use yii\helpers\Html;
use yii\web\ErrorHandler;
use yii\web\View;
use frontend\modules\jet\assets\AppAsset;
$view = new View;
AppAsset::register($view);
$valuecheck = "";
$handler = $this;
$valuecheck = Yii::$app->request->get('shop');
if(!($handler instanceof ErrorHandler) && ($handler instanceof View) && property_exists ( $handler , 'context' )){
	$handler = $handler->context;
}


/*------------------Html saving code starts----------------------*/

$useErrorView = (!YII_DEBUG || $exception instanceof UserException);
$errorView = '@yii/views/errorHandler/error.php';
$exceptionView = '@yii/views/errorHandler/exception.php';
$file = $useErrorView ? $errorView : $exceptionView;
$data = $handler->renderFile($file, [
                    'exception' => $exception,
                ]);

if(true){
	echo $data ;
	die;
}
/* To direct get Error String
$errorString = $this->convertExceptionToString($exception);
*/

$base_path = "";
$base_path = Yii::getAlias('@webroot').'/error';
if(!file_exists($base_path))
{
	mkdir($base_path,0775, true);
}
$filename = time().".html";
$filepath = "";
$filepath = $base_path.DIRECTORY_SEPARATOR.$filename;
$fh = fopen($filepath, (file_exists($filepath)) ? 'a' : 'w');
fwrite($fh, $data);
//fwrite($fh, '<pre>'.print_r($this,true).'</pre>');
fclose($fh);
/*------------------Html saving code ends----------------------*/
$newUrl=Yii::$app->getUrlManager()->getBaseUrl().'/error/'.$filename;
?>
<?php $view->beginPage() ?>
<html lang="<?= Yii::$app->language ?>">
<head>
	<a class="show-error" href="<?= $newUrl ?>" style="display:none"></a>
	<link rel="icon" href="<?php echo Yii::$app->request->baseUrl?>/images/favicon.ico">
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta content="NOINDEX, NOFOLLOW" name="robots">
	<meta name="google-site-verification" content="vJ8BS5PRRVFvpk5dCrUMgTZdDVIuWKMbvmCo2QTIEag" />
	 <script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery.js"></script>
	<link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/font-awesome.min.css">
	<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery.datetimepicker.full.min.js"></script>
	    <?= Html::csrfMetaTags() ?>
	     <title><?= Html::encode("Shopify Jet Integration | CedCommerce");?></title>
	<title><?= Html::encode($view->title) ?></title>
	    <?php $view->head() ?>
	<script type="text/javascript">
	    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	    ga('create', 'UA-63841461-1', 'auto');
	    ga('send', 'pageview');
	</script>  
	
</head>
<body class="iframe-body">
	<?php $view->beginBody() ?>      
 	<div class="wrap ced-jet-navigation-mbl">
 		<div class="trial-nav-wrap">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#"></a>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/index">Home</a></li>
                            <li><a title="Get the answers of your queries" href="<?= Yii::getAlias('@webjeturl') ?>/faq/index">Help & FAQs</a></li>
                            <?php if (Yii::$app->user->isGuest) {
                                ?>
                                <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/pricing">Pricing</a></li>
                                <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/guide">Documentation</a></li>
                                <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/login">Login</a></li>
                            <?php }
                            else{
                                ?>
                                <li class="dropdown">
                                    <a title="Click to expand" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Products<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a title="Sell more by mapping your products in correct category" href="<?= Yii::getAlias('@webjeturl') ?>/categorymap/index">Map Category</a></li>
                                        <li><a title="Map your variant products with correct attribute" href="<?= Yii::getAlias('@webjeturl') ?>/jet-attributemap/index">Map Jet Attributes</a></li>
                                        <li><a title="Manage products according various section" href="<?= Yii::getAlias('@webjeturl') ?>/jetproduct/index">Manage Products</a></li>
                                        <li role="separator"></li>
                                        <li><a title="Set your product price according to your convinience" href="<?= Yii::getAlias('@webjeturl') ?>/jetdynamicprice/index">Jet Repricing</a></li>
                                        <li><a title="Status of your products on Jet.com" href="<?=Yii::getAlias('@webjeturl') ?>/jetproductslist/index">Listing on Jet</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a title="Click to expand" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Export/Import<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li role="separator"></li>
                                        <li><a title="Import or Export your products in bulk through csv file" href="<?= Yii::getAlias('@webjeturl') ?>/productcsv/index">Product Update </a></li>
                                        <li><a title="Archive or Unarchive products" href="<?= Yii::getAlias('@webjeturl') ?>/jetcsvupdate/index">Product Archive/Unarchive</a></li>
                                    </ul>
                                </li>

                                <li class="dropdown">
                                    <a title="Click to expand" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Orders<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a title="Get details of your sales order" href="<?= Yii::getAlias('@webjeturl') ?>/jetorderdetail/index">Sales Orders</a></li>
                                        <li><a title="Get your details of your failed orders" href="<?= Yii::getAlias('@webjeturl') ?>/jetorderimporterror/index">Failed Orders</a></li>
                                        <li><a title="Get details your return order" href="<?= Yii::getAlias('@webjeturl') ?>/jetreturn/index">Return Orders</a></li>
                                        <li role="separator"></li>
                                        <li><a title="Get details of your refund order" href="<?= Yii::getAlias('@webjeturl') ?>/jetrefund/index">Refund Orders</a></li>
                                    </ul>
                                </li>

                                <li ><a title="Settings" class="icon-items" href="<?= Yii::getAlias('@webjeturl') ?>/jetconfiguration/index"><img src="<?= Yii::$app->request->baseUrl ?>/images/setting.png"></a></li>

                                <li class="dropdown ">
                                    <a title="Click to expand" href="#" class="dropdown-toggle icon-items" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="<?= Yii::$app->request->baseUrl ?>/images/dropdown.png"></a>
                                    <ul class="dropdown-menu">
                                        <li><a title="Get the details about payment plan" href="<?= Yii::getAlias('@webjeturl') ?>/site/paymentplan">Pricing</a></li>
                                        <li><a title="Get our support" href="http://support.cedcommerce.com/">Support</a></li>
                                        <li><a title="All the necessary details of config setting and about your store products" href="<?= Yii::getAlias('@webjeturl') ?>/how-to-sell-on-jet-com">Documentation</a></li>
                                        <li><a title="Get a quick tour of your store" href="<?= Yii::getAlias('@webjeturl') ?>/site/index?tour">Quick Tour</a></li>
                                        <li><a title="Get overall report of your products" href="<?= Yii::getAlias('@webjeturl') ?>/jetreport/index">Reports</a></li>
                                        <li class="logout_merchant"><a title="Log out from your store" href="<?= Yii::getAlias('@webjeturl') ?>/site/logout">Logout</a></li>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>


                        <ul class="nav navbar-nav navbar-right navbar-2">
                            <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/index">Home</a></li>
                            <?php
                            if (Yii::$app->user->isGuest)
                            {
                                ?>
                                <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/pricing">Pricing</a></li>
                                <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/guide">Documentation</a></li>
                                <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/login">Login</a></li>
                                <?php
                            }
                            else
                            {
                                ?>
                                <li class="dropdowns">
                                    <a href="#">Products</a>
                                    <ul class="dropdown-menus">
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/categorymap/index">Map Category</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jet-attributemap/index">Map Jet Attributes</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jetproduct/index">Manage Products</a></li>
                                        <li role="separator"></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/productcsv/index">CSV Export/Import</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jetcsvupdate/index">Product Archieve/Unarchieve</a></li>
                                    </ul>
                                </li>
                                <li class="dropdowns">
                                    <a href="#">Order</a>
                                    <ul class="dropdown-menus">
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jetorderdetail/index">Sales Order</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jetorderimporterror/index">Failed Order</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jetreturn/index">Return Order</a></li>
                                        <li role="separator"></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/jetrefund/index">Refund Order</a></li>
                                    </ul>
                                </li>
                                <li >
                                    <a class="icon-items" href="<?= Yii::getAlias('@webjeturl') ?>/jetconfiguration/index">setting</a>
                                </li>
                                <li class="dropdowns ">
                                    <a href="#">Account</a>
                                    <ul class="dropdown-menus">
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/pricing">Pricing</a></li>
                                        <li><a href="http://support.cedcommerce.com/">Support</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/how-to-sell-on-jet-com">Documentation</a></li>
                                        <li><a href="<?= Yii::getAlias('@webjeturl') ?>/site/index?tour">Quick Tour</a></li>
                                        <li class="logout_merchant"><a href="<?= Yii::getAlias('@webjeturl') ?>/site/logout">Logout</a></li>
                                    </ul>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div><!-- /.navbar-collapse -->
                </div><!-- /.container-fluid -->
            </nav>
	 	</div>    	 				
	 	<div class="fixed-container-body-class">
	       <style>
			.error{
			background: none repeat scroll 0 0 #DFDFDF;
			    color: #000000;
			    font-family: Helvetica;
			    font-size: 150px;
			    line-height: 2;
			    padding: 50px 0;
			    text-align: center;
			   }
			.error p{
			 font-size: 54px;
			}
			</style>



			<div class="error">
			<p class="lead">Something Went Wrong</p>
			Not Found !
			</div>

	    </div>
	    <div id="helpSection" style="display:none"></div>
	</div>
	<footer class="container-fluid footer-section">
		<div class="contact-section">
			<div class="row">
				<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<div class="ticket">
						<div class="icon-box">
							<div class="image">
								<a title="Click Here to Submit a Support Ticket" href="http://support.cedcommerce.com/" target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/ticket.png"></a>
							</div>
						</div>
						<div class="text-box">
							<span>Submit issue via ticket</span>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<div class="mail">
						<div class="icon-box">
							<div class="image">
								<a title="Click Here to Contact us through Mail" href="mailto:shopify@cedcommerce.com" target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/mail.png"></a>
							</div>
						</div>
						<div class="text-box">
							<span>Send us an E-mail</span>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<div class="skype">
						<div class="icon-box">
							<div class="image">
								<a title="" href="javascript:void(0)"><img src="<?= Yii::$app->request->baseUrl ?>/images/skype.png"></a>
							</div>
						</div>
						<div class="text-box">
							<span>Connect via skype</span>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				
			</div>
		</div>
	</footer>
	<div class="copyright-section">
		<div class="row">
			<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
				<a title="Click Here to Submit a Support Ticket" href="https://play.google.com/store/apps/details?id=com.cedcommerce.shopifyintegration&hl=en" target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/GooglePlay.png"></a>
				<a title="Click Here to Contact us through Mail" href="https://itunes.apple.com/us/app/cedbridge-for-shopify/id1186746708?ls=1&mt=8
" target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/App-Store.png"></a>
			</div>
			<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
				<div class="copyright">
					<span>Copyright © 2017 CEDCOMMERCE | All Rights Reserved.</span>
				</div>
			</div>
		</div>
	</div>
<?php $view->endBody() ?>
   <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
   <script type="text/javascript">
   	$(document).ready(function(){
		$('.dropdown').addClass('dropdown1').removeClass('dropdown');
	});
   </script>
  <?php 
  	if(!Yii::$app->user->isGuest)
  	{
	    if($valuecheck)
	  	{
		    ?>
			<script type="text/javascript">
				//add css for embedded app
				$(document).ready(function(){
					var head1=$(document).find('head');
					var url = '<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/embapp.css';
					head1.append($("<link/>", { rel: "stylesheet", href: url, type: "text/css" } ));
					$('.logout_merchant').css('display','none');
					//$('nav.navbar-fixed-top').css('display','none');
					$('.wrap > .container').css('padding',0);

				});
				//initialise embedded iframe 
				ShopifyApp.init({
					apiKey: "<?php echo PUBLIC_KEY;?>",
					shopOrigin: "<?php echo "https://".\Yii::$app->user->identity->username;?>"
				});
				ShopifyApp.ready(function()
				{
					ShopifyApp.Bar.loadingOff();
					
				});

			</script>
		<?php 
		}
		?>
		<script type="text/javascript">
		if ( self !== top )
		{
			var head1=$(self.document.head);
			var url = '<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/embapp.css';
			head1.append($("<link/>", { rel: "stylesheet", href: url, type: "text/css" } ));
			$('.logout_merchant').css('display','none');
			//$('nav.navbar-fixed-top').css('display','none');
			//$('.wrap > .container').css('padding',0)
			ShopifyApp.init({
				apiKey: "<?php echo PUBLIC_KEY;?>",
				shopOrigin: "<?php echo "https://".Yii::$app->user->identity->username;?>"
			});
			ShopifyApp.ready(function()
			{
				ShopifyApp.Bar.loadingOff();
				
			});
		}
		</script>
	<?php 
	} 
	?> 
	<!--Start of Zopim Live Chat Script-->
	<script type="text/javascript">
		window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
		d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
		_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
		$.src="//v2.zopim.com/?322cfxiaxE0fIlpUlCwrBT7hUvfrtmuw";z.t=+new Date;$.
		type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
		
		$zopim(function(){
		window.setTimeout(function() {
		//$zopim.livechat.window.show();
		}, 2000); //time in milliseconds
		});
	</script>
	<!-- end menu -->

	</body>
</html>
<?php $view->endPage() ?>