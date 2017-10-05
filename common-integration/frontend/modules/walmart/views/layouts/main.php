<?php
use frontend\modules\walmart\assets\AppAsset;
//use frontend\components\Jetappdetails;
use frontend\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use frontend\modules\walmart\components\Upgradeplan;

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use frontend\modules\walmart\components\Data;
use yii\widgets\Menu;
use frontend\modules\walmart\controllers\BulkUploadServerController;
use frontend\modules\walmart\controllers\GetnotificationController;

$valuecheck = "";
//$obj=new Jetappdetails();
//$valuecheck=$obj->autologin();
AppAsset::register($this);
$urlCall = \yii\helpers\Url::toRoute(['site/schedulecall']);
$feedbackurl = \yii\helpers\Url::toRoute(['site/feedback']);
$notificationurl = \yii\helpers\Url::toRoute(['getnotification/setread']);

$uri = array();
if (isset($_SERVER['REQUEST_URI'])) {
    $uri = explode('/', $_SERVER['REQUEST_URI']);
}

$bulkUploadServer = new BulkUploadServerController(Yii::$app->controller->id,'');
?>
<?php $this->beginPage();

if (!headers_sent()) {
    header('P3P:CP="CAO IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT NOI NID CUR BUS"');
} ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <link rel="icon" href="<?php echo Yii::$app->request->baseUrl ?>/images/favicon.ico">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="INDEX,FOLLOW" name="robots">

    <script type="text/javascript"
            src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery-1.10.2.min.js"></script>

    <?php if (Yii::$app->controller->id . '/' . Yii::$app->controller->action->id == 'site/index') { ?>
        <script type="text/javascript"
                src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery.lightbox-0.5.min.js"></script>
        <link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/css/jquery.lightbox-0.5.css">
    <?php } ?>
    <link rel="stylesheet" href="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/css/font-awesome.min.css">
    <script type="text/javascript"
            src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery.datetimepicker.full.min.js"></script>
    <?= Html::csrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>


    <script type="text/javascript">
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
        ga('create', 'UA-63841461-1', 'auto');
        ga('send', 'pageview');
    </script>

    <!-- Socket Start -->
    <!-- <script src="//code.jquery.com/jquery-3.2.1.js"></script> -->
    <script type="text/javascript">

        $(document).ready(function(){
            //create a new WebSocket object.
            var wsUri = "ws://<?= $bulkUploadServer->host ?>:<?= $bulkUploadServer->port ?>";
            websocket = new WebSocket(wsUri);
            var vendorSid="vendorSid";
            var vendorRid="vendorRid";

            var Hash = getHash();

            websocket.onopen = function(ev) { // connection is open 
                console.log("Connected!"); //notify user
                //prepare json data
                var msg = {
                    hash: Hash
                };
                //convert and send data to server
                websocket.send(JSON.stringify(msg));
            }
            
            //#### Message received from server?
            websocket.onmessage = function(ev) {
                var msg = JSON.parse(ev.data); //PHP sends Json data
                console.log(msg);
                if(msg.hasOwnProperty('type'))
                {
                    var type = msg.type;
                    if(type == 'action')
                    {
                        var message = msg.message;
                        if(message == "disable_button")
                        {
                            var alreadyClicked = $('body').attr('data-bulkupload');
                            if(alreadyClicked != 'yes') 
                            {
                                $('body').attr('data-bulkupload', 'yes');
                                $('.upload_all_products').prop("disabled", true);
                            }
                        }
                        else if(message == "enable_button")
                        {
                            $('body').removeAttr('data-bulkupload');
                            $('.upload_all_products').prop("disabled", false);
                        }

                        if(msg.hasOwnProperty('progress') && msg.progress != '')
                        {
                            var progress = msg.progress;
                            setProgress(progress);
                        }

                        if(msg.hasOwnProperty('error_count') && msg.error_count != '')
                        {
                            $('#upload-error-count').html(msg.error_count);
                            $('#upload-error-text').html('Producrs are having error.');
                        }
                    }
                    else if(type == 'notification')
                    {
                        var message = msg.message;
                        var status = msg.status;
                        //alert(message);
                        if(status == 'success')
                        {
                            if(msg.hasOwnProperty('success_count')) {
                                var successMsg = msg.success_count+' Products Successfully Submitted to Walmart.';
                            }
                        }
                        else if(status == 'error')
                        {
                            if(msg.hasOwnProperty('error_count')) {
                                $('#upload-error-count').html(msg.error_count);
                                $('#upload-error-text').html('Producrs are having error.');
                            }
                        }

                        if(msg.hasOwnProperty('progress') && msg.progress != '')
                        {
                            <?php if(Yii::$app->controller->id=='bulk-upload' && Yii::$app->controller->action->id=='view-errors') { ?>
                            window.location.reload();
                            <?php } ?>

                            var progress = msg.progress;
                            setProgress(progress);
                        }
                    }
                }
                
            };
            
            websocket.onerror = function(ev){
                var notified="Error Occurred-"+ev.data;
               // alert(notified);
            };
            
            websocket.onclose = function(ev){
               // alert("Connection Closed")
            }; 
        });

        function getHash()
        {
            var hash = '';
            var url = "<?= Data::getUrl('bulk-upload-server/generatehash') ?>";
            j$.ajax({
                method: "post",
                url: url,
                async: false,
                data: {_csrf: ''}
            })
            .done(function (response) {
                hash = response;
            });

            return hash;
        }

        function setProgress(progress)
        {
            $("#upload-progress").attr('aria-valuenow', progress);
            $("#upload-progress").css("width", progress+"%");
            $("#upload-progress").html('<span class="">'+progress+"% Complete"+'</span>');
            
            if(progress=='100') 
            {
                //enable close button for product-upload-progress-wrapper when upload is 100% done
                $("#close-upload-progress").prop('disabled', false);
                
                //enable upload button when upload is 100% done
                $('body').removeAttr('data-bulkupload');
                $('.upload_all_products').prop("disabled", false);
            } 
            else 
            {
                //disable close button for product-upload-progress-wrapper until product upload is under process
                $("#close-upload-progress").prop('disabled', true);
            }

            //show product-upload-progress-wrapper when ever product upload starts
            $('.product-upload-progress-wrapper').show();
        }

        function resetUploadProgress()
        {
            //reset all progress
            var progress = 0;
            $("#upload-progress").attr('aria-valuenow', progress);
            $("#upload-progress").css("width", progress+"%");
            $("#upload-progress").html('<span class="">'+progress+"% Complete"+'</span>');
            
            //disable close button for product-upload-progress-wrapper until product upload is under process
            $("#close-upload-progress").prop('disabled', true);

            //show product-upload-progress-wrapper when ever product upload starts
            $('.product-upload-progress-wrapper').show();

            //reset error count
            $('#upload-error-count').html('');
            $('#upload-error-text').html('');

        }
    </script>
    <!-- Socket End -->
</head>

<?php
if (Yii::$app->controller->action->id == 'pricing') {
    echo "<body class='pricing-page'>";
} else {
    echo "<body>";
}
?>

<?php $this->beginBody() ?>
<?php
if (!Yii::$app->user->isGuest) {
    ?>
    <div class="trial-wrapper">
        <div class="col-sm-9 plateform-switch-body no-padding">
            <?php

            $manager_login = Yii::$app->user->identity->manager;
            $merchant_id = Yii::$app->user->identity->id;

            if($manager_login)
            {
                $message = Data::getmerchantMessage($merchant_id);
                echo $message;
            }


            $controllerArray = ['walmarttaxcodes', 'walmartproductfeed', 'walmart-carriers-map', 'updatecsv', 'productcsv', 'csvmap', 'report','product'];

            $walsql = $jetpath = $newpath = $jetpathurl = "";
            $isWalmartClient = $isNeweggClient = [];
            $sql = "SELECT `id` FROM `user` WHERE `id`='{$merchant_id}' AND `auth_key`!='' ";
            $isJetClient = Data::sqlRecords($sql, 'one', 'select');

            $eggsql = "SELECT `id` FROM `newegg_shop_detail`  WHERE `merchant_id`='{$merchant_id}' ";
            $isNeweggClient = Data::sqlRecords($eggsql, 'one', 'select');
            if (empty($isJetClient)) {
                $jetpath = '<a href="https://apps.shopify.com/jet-integration" ><button class="btn-path">Install Now</button></a>';
            } else {
                $newpath = preg_replace('/walmart/', 'jet', Yii::$app->controller->id);

                if (in_array(Yii::$app->controller->id, $controllerArray)) {
                    $newpath = 'site';
                }

                $jetpathurl = Yii::getAlias('@webjeturl') . "/" . $newpath . "/index?to_switch=jet";

                $jetpath = '<a href="' . $jetpathurl . '" ><button class="btn-path">Switch Store</button></a>';
            }

            if (empty($isNeweggClient)) {
                $eggpath = '<a href="https://apps.shopify.com/newegg-marketplace-integration" ><button class="btn-path">Install Now</button></a>';
            } else {
                $neweggpath = preg_replace('/walmart/', 'newegg', Yii::$app->controller->id);

                if (in_array(Yii::$app->controller->id, $controllerArray)) {
                    $neweggpath = 'site';
                }

                $eggpathurl = Yii::getAlias('@webneweggurl') . "/" . $neweggpath . "/index?to_switch=newegg";
                $eggpath = '<a href="' . $eggpathurl . '" ><button class="btn-path">Switch Store</button></a>';
            }
            ?>
            <div class="install-walmart">
                <div class="install-button">
                    <div>
                        <h2 class="rw-sentence">
                            <span>Try/Switch other integrations like</span>
                            <div class="rw-words rw-words-1">
                                <span>JET</span>
                                <span>NEWEGG</span>
                            </div>
                            <i class="fa fa-chevron-down" id="show_apps_div" aria-hidden="true"></i>
                            <!-- Code For Referral Start -->

                            <!-- Code For Referral End -->
                       <!--  </h2> -->
                        
                    </div>
                    <div id="display_apps" style="display: none;">
                        <div class="jet">
                            <span class="jet-app">Jet app</span>
                            <?= $jetpath ?>
                        </div>
                        <div class="newegg">
                            <span class="newegg-app">Newegg app</span>
                            <?= $eggpath ?>
                        </div>
                    </div>
                </div>
            </div>
            <? /*
                    }
                    */ ?>
        </div>
        <div class="col-sm-3 upgradeplean-body no-padding">
            <?php
            if(( (Yii::$app->controller->id.'/'.Yii::$app->controller->action->id!='site/paymentplan') ))
            {
                Upgradeplan::remainingDays(Yii::$app->user->identity->id);
            }
            ?>
        </div>

    </div>
    <?
}
?>
<div class="wrap ced-jet-navigation-mbl">
    <?php if (!Yii::$app->user->isGuest) {
        ?>

        <div class="trial-nav-wrap">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">

                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                                data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
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
                            <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/site/index">Home</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Products<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/categorymap/index-new">Map
                                            Category</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmart-attributemap/index">Attributes
                                            Mapping</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/product/index">Manage
                                            Products</a></li>
                                    <li role="separator"></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmarttaxcodes/index">Get
                                            Taxcodes</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartproductfeed/index">Walmart
                                            Feeds</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartrepricing/index">Repricing</a>
                                    </li>
                                    <!--<li><a href="<?/*= Yii::$app->request->baseUrl */?>/walmart/faq/index#32">Click for Help</a></li>-->

                                    <!--<li><a href="<? /*= Yii::$app->request->baseUrl */ ?>/walmart/productcsv/index">CSV Price
                                            Update</a></li>-->
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Order<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartorderdetail/index">Sales
                                            Order</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartorderimporterror/index">Failed
                                            Order</a></li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Import/Export<span
                                            class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/updatecsv/index">Product
                                            Update</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/productcsv/index">Price,
                                            Inventory and Barcode</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/updatecsv/index-retire">Retire Product</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/csv-upload/index">Product Upload</a></li>

                                </ul>
                            </li>
                            <li>
                                <a href="<?= Yii::$app->request->baseUrl ?>/walmart/help/index">Help</a>
                            </li>

                            <!-- Code For Referral Start -->
                            <li>
                                <a href="<?= Yii::$app->request->baseUrl ?>/referral/account/dashboard" target="_blank">Referrals</a>
                                <span class="ref">new</span>
                            </li>
                            <!-- Code For Referral End -->
                            <li>
                                    <a id="anchor-notification" class="notification-anchor">
                                        <img src="<?= Yii::getAlias('@webbaseurl')?>/images/notification.png">
                                        <span class="notification-bell"></span>
                                    </a>
                            </li>
                            <!--<li>
                                <a class="icon-items">
                                    <img src="<?/*= Yii::getAlias('@walmartbasepath') */?>/assets/images/walmart-guide/icons/Layer-6.png">
                                </a>
                            </li>-->

                            <li>
                                <a class="icon-items"
                                   href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartconfiguration/index"><img
                                            src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/icons/Layer-4.png"></a>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle icon-items" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false"><img
                                            src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/icons/Layer-5.png"></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/paymentplan">Payment
                                            Plan</a></li>
                                    <li><a href="http://support.cedcommerce.com/" target="_blank">Support</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/sell-on-walmart">Documentation</a>
                                    </li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/site/index?tour">Quick
                                            Tour</a></li>

                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/report/index">Report</a>
                                    </li>

                                    <li class="logout_merchant"><a
                                                href="<?= Yii::$app->request->baseUrl ?>/walmart/site/logout">Logout</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>

                        <ul class="nav navbar-nav navbar-right navbar-2">
                            <li><a href="<?= Yii::$app->request->baseUrl ?>/site/index">Home</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Products<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/categorymap/index-new">Map
                                            Category</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmart-attributemap/index">Attributes
                                            Mapping</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/product/index">Manage
                                            Products</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmarttaxcodes/index">Get
                                            Taxcodes</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartproductfeed/index">Walmart
                                            Feeds</a></li>
                                    <!--<li><a href="<?/*= Yii::$app->request->baseUrl */?>/walmart/faq/index#32">Click for Help</a></li>-->

                                    <!--<li><a href="<? /*= Yii::$app->request->baseUrl */ ?>/walmart/walmartrepricing/index">Repricing</a>
                                    </li>-->
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Order<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartorderdetail/index">Sales
                                            Order</a>
                                    </li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartorderimporterror/index">Failed
                                            Order</a>
                                    </li>

                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Import/Export<span
                                            class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/updatecsv/index">Product
                                            Update</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/productcsv/index">Price,
                                            Inventory and Barcode</a></li>

                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/updatecsv/index-retire">Retire
                                            Product</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/productuploadcsv/upload">Price,
                                            Product Upload</a></li>

                                </ul>
                            </li>
                            <li>
                                <a href="<?= Yii::$app->request->baseUrl ?>/walmart/help/index">Help</a>
                            </li>

                            <!-- Code For Referral Start -->
                            <li>
                                <a href="<?= Yii::$app->request->baseUrl ?>/referral/account/dashboard" target="_blank">Referrals</a>
                                <span class="ref-mobile">new</span>
                            </li>
                             <!-- Code For Referral End -->

                            <li>
                                    <a id="anchor-notification" class="notification-anchor">
                                        <span>Notification(s)</span>
                                        <span class="notification-bell"></span>
                                    </a>
                                   
                                
                            </li>
                           
                            <!-- Code For Referral End -->

                            <!--<li>
                                <a class="icon-items">
                                    <img src="<?/*= Yii::getAlias('@walmartbasepath') */?>/assets/images/walmart-guide/icons/Layer-6.png">
                                </a>
                            </li>-->


                            <li>
                                <a class="icon-items"
                                   href="<?= Yii::$app->request->baseUrl ?>/walmart/walmartconfiguration/index">Setting</a>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-haspopup="true" aria-expanded="false">Account<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/paymentplan">Payment
                                            Plan</a></li>
                                    <li><a href="http://support.cedcommerce.com/" target="_blank">Support</a></li>
                                    <li>
                                        <a href="<?= Yii::$app->request->baseUrl ?>/walmart/sell-on-walmart">Documentation</a>
                                    </li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/site/index?tour">Quick
                                            Tour</a></li>
                                    <li><a href="<?= Yii::$app->request->baseUrl ?>/walmart/report/index">Report</a>
                                    </li>


                                    <li class="logout_merchant"><a
                                                href="<?= Yii::$app->request->baseUrl ?>/walmart/site/logout">Logout</a>
                                    </li>
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
            'homeLink' => ['label' => 'Home','url' => Yii::$app->getUrlManager()->getBaseUrl().'/walmart/site/index'],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <?= Alert::widget() ?>
        <?= $content ?>
    </div>

    <?php
    if (!Yii::$app->user->isGuest) {?>
            <div class="feedback" id="feedback">
                <div class="square-wrapper">
                    <a href="javascript:void(0)" id ="hide">
                        <i class="glyphicon glyphicon-chevron-right" id="i_tag"></i>
                        <span>f</span>
                        <span>e</span>
                        <span>e</span>
                        <span>d</span>
                        <span>b</span>
                        <span>a</span>
                        <span>c</span>
                        <span>k</span>
                    </a>
                </div>
                <div id="view_feedback"></div>
            </div>

    <?php } ?>

    <div id="view_call" style="display: none;"></div>
    <div id="helpSection" style="display:none"></div>

</div>
<?php
if (Yii::$app->controller->id . '/' . Yii::$app->controller->action->id != 'site/guide') {
    ?>
    <footer class="container-fluid footer-section">
        <div class="contact-section">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="ticket">
                        <div class="icon-box">
                            <div class="image">
                                <a title="Click Here to Submit a Support Ticket" href="http://support.cedcommerce.com/"
                                   target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/ticket.png"></a>
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
                                <a title="Click Here to Contact us through Mail" href="mailto:shopify@cedcommerce.com"
                                   target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/mail.png"></a>
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
                        <?php
                        $skypeIds = ['live:cedcommerce', 'live:support_35785', 'live:danbakker_2', 'tonystark.cedcommerce', 'live:nehasingh_20', 'live:nidhirajput_2', 'barryallen.cedcommerce', 'live:swatishukla_5', 'jimmoore.cedcommerce', 'live:steveroger_7', 'stephenjones.cedcommerce', 'james.cedcommerce', 'live:f4acf2f09cc4c216'];
                        $topic = 'CEDCOMMERCE SUPPORT';
                        if (!Yii::$app->user->isGuest) {
                            $topic = Yii::$app->user->identity->username;
                        }
                        ?>
                                <a title="Click Here to Connect With us through Skype" href="skype:<?= implode(';', $skypeIds) ?>?chat&topic=<?= $topic ?>"><img
                                            src="<?= Yii::$app->request->baseUrl ?>/images/skype.png"></a>
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
                <a title="Click Here to get android app"
                   href="https://play.google.com/store/apps/details?id=com.cedcommerce.shopifyintegration&hl=en"
                   target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/GooglePlay.png"></a>
                <a title="Click Here to get ios app"
                   href="https://itunes.apple.com/us/app/cedbridge-for-shopify/id1186746708?ls=1&mt=8"
                   target="_blank"><img src="<?= Yii::$app->request->baseUrl ?>/images/App-Store.png"></a>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="copyright">
                    <span>Copyright © 2010 CEDCOMMERCE | All Rights Reserved.</span>
                </div>
            </div>
        </div>
        <div class="overlay" style="display: none;" id="LoadingMSG">
            <!-- <img src="<?php //echo Yii::$app->getUrlManager()->getBaseUrl();?>/images/loading-large.gif"> -->
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
    </div>
    <?php
}
?>
<?php $this->endBody() ?>

<?php if (!\Yii::$app->user->isGuest && $valuecheck) { ?>
    <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
    <script type="text/javascript">
        ShopifyApp.init({
            apiKey: "<?php echo WALMART_API_KEY;?>",
            shopOrigin: "<?php echo "https://" . SHOP;?>"
        });
        ShopifyApp.ready(function () {
            ShopifyApp.Bar.initialize({
                //icon: "https://lay-buys.com/vt/themes/hebo/img/LAY-BUYS-logo-medium.png",
                //title: "Need Help",
                buttons: {
                    primary: {
                        label: 'Help',
                        message: 'need help',
                        callback: function () {
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
    function callView() {
        var url = '<?= $urlCall ?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,

        })
            .done(function (msg) {
                //console.log(msg);
                $('#LoadingMSG').hide();
                $('#view_call').html(msg);
                $('#view_call').css("display", "block");
                $('#view_call #myModal').modal('show');
            });
    }
    function showfeedback() {
        var check ='<?=!Yii::$app->user->isGuest;?>';
        if(check){
            var url = '<?= $feedbackurl ?>';
            $.ajax({
                method: "post",
                url: url,

            })
                .done(function (msg) {
                    //console.log(msg);

                    //$('#feedback').removeAttr("style");
                    //$("#i_tag").attr('class', "glyphicon glyphicon-chevron-right");
                   // $('#hide').attr('onclick','').unbind('click');
                    $('#view_feedback').html(msg);
                    //$('#view_feedback').css("display", "block");
                    //$('#view_feedback #myModal').modal('show');
                });
        }
    }
    $(document).ready(function () {
        $(document).on('pjax:send', function () {
            j$('#LoadingMSG').show();
            console.log('pjax send');
        })
        $(document).on('pjax:complete', function () {
            j$('#LoadingMSG').hide()
            console.log('pjax complete');
        })
        $('.carousel').carousel({
            interval: 6000
        });
    });

    if (self !== top) {
        var head1 = $(self.document).find('head');
        console.log(head1);
        var url = '<?= Yii::$app->getUrlManager()->getBaseUrl();?>/css/embapp.css';
        head1.append($("<link/>", {rel: "stylesheet", href: url, type: "text/css"}));
        $('.logout_merchant').css('display', 'none');
    }
</script>

<!--Start of Zopim Live Chat Script-->
<script type="text/javascript">
    window.$zopim || (function (d, s) {
        var z = $zopim = function (c) {
            z._.push(c)
        }, $ = z.s =
            d.createElement(s), e = d.getElementsByTagName(s)[0];
        z.set = function (o) {
            z.set._.push(o)
        };
        z._ = [];
        z.set._ = [];
        $.async = !0;
        $.setAttribute("charset", "utf-8");
        $.src = "//v2.zopim.com/?322cfxiaxE0fIlpUlCwrBT7hUvfrtmuw";
        z.t = +new Date;
        $.type = "text/javascript";
        e.parentNode.insertBefore($, e)
    })(document, "script");

    $zopim(function () {
        window.setTimeout(function () {
            //$zopim.livechat.window.show();
        }, 2000); //time in milliseconds
    });
</script>
<!--End of Zopim Live Chat Script-->
<script type="text/javascript">
    $(document).ready(function(){
        showfeedback();
        $('#hide').click(function(e){
            if($('#i_tag').attr('class')=='glyphicon glyphicon-chevron-right'){
                 $("#i_tag").attr('class', "glyphicon glyphicon-chevron-left");
            }
            else{
                 $("#i_tag").attr('class', "glyphicon glyphicon-chevron-right");
            }
            //$('#hide').on('click', function () {
            // $('#feedback').attr('style', 'left: -254px !important');
            // $('.square').attr('onclick','showfeedback()');
            $('#feedback').toggleClass('show');
            e.preventDefault();
        })
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.dropdown').addClass('dropdown1').removeClass('dropdown');
    });

</script>

<!-- Hotjar Tracking Code for https://shopify.cedcommerce.com/integration -->
<script>
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

    function changeTo() {
        var market_place = document.getElementById("marketplace_name").value;
        if (market_place == "jet") {
            var newpath = "<?= preg_replace('/walmart/', 'jet', Yii::$app->controller->id)?>";
            window.location.href = "<?= Yii::getAlias('@webjeturl') ?>/" + newpath + "/index?to_switch=jet";
        }
    }


    // by shivam

    $("#show_apps_div").click(function () {
        var x = document.getElementById('display_apps');
        if (x.style.display === 'none') {
            x.style.display = 'block';
        } else {
            x.style.display = 'none';
        }
    });
    $('body').click(function(event) {
        var id = event.target.id;
        //alert(id);

        if(id != 'show_apps_div'){
            var x = document.getElementById('display_apps');
            x.style.display = 'none';
        }
        /*alert(id); */// Will alert the id if the element has one
    });
    /*$("#testing-trial").click(function(){
     var x = document.getElementById('show_plan');
     if (x.style.display === 'none') {
     x.style.display = 'block';
     } else {
     x.style.display = 'none';
     }
     });*/

    //end by shivam


    $('.fixed-container-body-class').on('click', '.confirmbox .btn.btn-primary', function (e) {
        var lastclass = $(this).attr('class').split(' ').pop();
        if (lastclass != 'noconfirmbox') {
            e.preventDefault();
            var url = $(this).attr('href');
            alertify.confirm("Click OK to continue For " + $(this).text() + "?", function (e) {
                if (e) {
                    window.location = url;
                } else {
                }
            });
        }
    });
</script>
<!-- notfication html  -->
<?php 
if(!Yii::$app->user->isGuest) {
?>
<div id="inner-notification" class="inner-notification">
    <div class="inner-notification-pos">
        <a href="#" class="notification-close"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
        <div class="inner-notification-pos-scroll">
            <div class="notification-pos-scroll">
<?php 
                echo GetnotificationController::getNotification(Yii::$app->user->identity->id);
?>
            </div>
        </div>
    </div>
</div>
<!-- notfication html  end -->
<script type="text/javascript">
    $('.notification-anchor').on('click',function(){
        $('.inner-notification').toggleClass('active');
        if($('.notification-bell').length>0){
            var url = '<?= $notificationurl ?>';
            var merchant_id = '<?= Yii::$app->user->identity->id; ?>';
            $.ajax({
              method: "post",
              url: url,
              data: {id: merchant_id}
            })
            .done(function (response) {
                $(".notification-bell").remove();
            });
        }
    });

    $('.notification-close').on('click',function(e){
        //alert('dsfdsfdsfdas');
        $('.inner-notification').removeClass('active');
        e.preventDefault();
    });

</script>
<style>
.ankit-body{
    padding-top: 58px !important;
}
.ankit-nav{
    margin-top: 0px !important;
}
</style>
<script type="text/javascript">
    $(document).ready(function() {
        notification();
        mobilenotification();   
        $(window).resize(function(){
            notification();
            mobilenotification();
        });
        
        if($('.show-notification').length>0){

        }
        else{
            $(".notification-bell").remove();
        }
    });

    function notification(){
        var ht = $('.notification-pos-scroll').height();
        var wh = $(window).height();
        if(ht > wh){
            $('.inner-notification').addClass('overflow');
        }
        else{
            $('.inner-notification').removeClass('overflow');
        }
    }

    function mobilenotification(){
        if($(window).width() < 768){
            $('.notification-anchor').on('click',function(){
                $('.navbar-default #bs-example-navbar-collapse-1').removeClass('in');
            });
        }
    }
</script>
<?php 
}
?>

</body>
</html>
<?php $this->endPage() ?>

