<?php
use frontend\assets\AppAsset;
//use frontend\components\Jetappdetails;
use frontend\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\Menu;
$valuecheck="";
//$obj=new Jetappdetails();
//$valuecheck=$obj->autologin();
AppAsset::register($this);
$uri = array();
if(isset($_SERVER['REQUEST_URI']))
{
    $uri=explode('/',$_SERVER['REQUEST_URI']);
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <link rel="icon" href="<?php echo Yii::$app->request->baseUrl?>/images/favicon.ico">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="INDEX,FOLLOW" name="robots">

    <script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery-1.10.2.min.js"></script>

    <?php if(Yii::$app->controller->id.'/'.Yii::$app->controller->action->id=='site/index'){?>
        <script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery.lightbox-0.5.min.js"></script>
        <link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/jquery.lightbox-0.5.css">
    <?php }?>
    <link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/font-awesome.min.css">
    <script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/js/jquery.datetimepicker.full.min.js"></script>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode("Shopify Newegg Integration | CedCommerce");?></title>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>


    <script type="text/javascript">
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', 'UA-63841461-1', 'auto');
        ga('send', 'pageview');
    </script>
</head>

<?php
if(Yii::$app->controller->action->id=='pricing') {
    echo "<body class='pricing-page'>";
} else {
    echo "<body>";
}
?>

<?php $this->beginBody() ?>

<div class="wrap ced-jet-navigation-mbl">

    <?php if (!Yii::$app->user->isGuest) {
        ?>
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

                        <!-- <?php if (!Yii::$app->user->isGuest) {
                        ?> -->
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/site/index">Home</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Products<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/categorymap/index">Map Category</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggattributemap/index">Attributes Mapping</a></li>
                                    <li role="separator"></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/product/index">Manage Products</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggproductfeed/index">Newegg Feeds</a></li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Order<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggorderdetail/index">Sales Order</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggorderdetail/getcourtesyrefund">Courtesy Refund Orders</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggorderimporterror/index">Failed Order</a></li>
                                </ul>
                            </li>

                            <li ><a class="icon-items" href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggconfiguration/index"><img src="<?= Yii::$app->request->baseUrl ?>/images/setting.png"></a></li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle icon-items" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="<?= Yii::$app->request->baseUrl ?>/images/dropdown.png"></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/site/paymentplan">Payment Plan</a></li>
                                    <li><a href="http://support.cedcommerce.com/">Support</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/newegg-marketplace/sell-on-newegg">Documentation</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/site/index?tour">Quick Tour</a></li>
                                    <li class="logout_merchant"><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/site/logout">Logout</a></li>
                                </ul>
                            </li>
                        </ul>

                        <ul class="nav navbar-nav navbar-right navbar-2">
                            <li><a href="<?= Yii::$app->request->baseUrl ?>/site/index">Home</a></li>
                            <li class="dropdowns">
                                <a href="#">Products</a>
                                <ul class="dropdown-menus">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/categorymap/index">Map Category</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggattributemap/index">Attributes Mapping</a></li>
                                    <li role="separator"></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/product/index">Manage Products</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggproductfeed/index">Newegg Feeds</a></li>
                                </ul>
                            </li>

                            <li class="dropdowns">
                                <a href="#">Order</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggorderdetail/index">Sales Order</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggorderdetail/getcourtesyrefund">Courtesy Refund Orders</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggorderimporterror/index">Failed Order</a></li>
                                </ul>
                            </li>

                            <li ><a class="icon-items" href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/neweggconfiguration/index">Setting</a></li>

                            <li class="dropdowns">
                                <a href="#">Account</a>
                                <ul class="dropdown-menus">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/site/paymentplan">Payment Plan</a></li>
                                    <li><a href="http://support.cedcommerce.com/">Support</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart-marketplace/sell-on-walmart">Documentation</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/site/index?tour">Quick Tour</a></li>
                                    <li class="logout_merchant"><a href="<?= Yii::$app->request->baseUrl ?>/neweggcanada/site/logout">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                        <!-- <?php } ?> -->

                    </div>
                </div>
            </nav>
        </div>
    <?php } ?>
    <!-- ========================= sticky header ===============================-->
    <div class="sticky-header"></div>
    <!-- ========================= sticky header end ===============================-->

    <div class="fixed-container-body-class">

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
    <div id="helpSection" style="display:none"></div>
</div>
<?php
if(Yii::$app->controller->id.'/'.Yii::$app->controller->action->id != 'site/guide')
{
    ?>
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
                                <a title="Click Here to Connect With us through Skype" href="javascript:void(0)"><img src="<?= Yii::$app->request->baseUrl ?>/images/skype.png"></a>
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
                <a title="Click Here to Contact us through Mail" href="https://itunes.apple.com/us/app/cedbridge-for-shopify/id1186746708?ls=1&mt=8" target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/App-Store.png"></a>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="copyright">
                    <span>Copyright © 2010 CEDCOMMERCE | All Rights Reserved.</span>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<?php $this->endBody() ?>


<?php if(!\Yii::$app->user->isGuest && $valuecheck) {	?>
    <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
    <script type="text/javascript">
        ShopifyApp.init({
            apiKey: "<?php echo WALMART_API_KEY;?>",
            shopOrigin: "<?php echo "https://".SHOP;?>"
        });
        ShopifyApp.ready(function(){
            ShopifyApp.Bar.initialize({
                //icon: "https://lay-buys.com/vt/themes/hebo/img/LAY-BUYS-logo-medium.png",
                //title: "Need Help",
                buttons:
                {
                    primary: {
                        label: 'Help',
                        message: 'need help',
                        callback: function(){
                            ShopifyApp.Bar.loadingOn();
                            //doSomeCustom();
                        }
                    }
                }
            });

        });
    </script>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('pjax:send', function() {
            j$('#LoadingMSG').show();
            console.log('pjax send');
        });
        $(document).on('pjax:complete', function() {
            j$('#LoadingMSG').hide()
            console.log('pjax complete');
        });
        $('.carousel').carousel({
            interval: 6000
        });
    });

    if( self !== top ){
        var head1=$(self.document).find('head');
        console.log(head1);
        var url = '<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/embapp.css';
        head1.append($("<link/>", { rel: "stylesheet", href: url, type: "text/css" } ));
        $('.logout_merchant').css('display','none');
    }
</script>

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
<!--End of Zopim Live Chat Script-->

<script type="text/javascript">
    $(document).ready(function(){
        $('.dropdown').addClass('dropdown1').removeClass('dropdown');
    });

</script>

<!-- Hotjar Tracking Code for https://shopify.cedcommerce.com/integration -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:305411,hjsv:5};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'//static.hotjar.com/c/hotjar-','.js?sv=');
</script>
</body>
</html>
<?php $this->endPage() ?>
