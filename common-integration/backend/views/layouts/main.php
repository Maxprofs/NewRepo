<?php
use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use backend\components\Data;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$calls = Data::sqlRecords('SELECT COUNT( `id` ) callnumber FROM `call_schedule` WHERE `status`="Pending"','one','select');
$pendingCalls = isset($calls['callnumber'])?$calls['callnumber']:0;
?>
<?php $this->beginPage() ?>

    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">

    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <script type="text/javascript" src="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/js/loader.js"></script>
        <script src="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/js/jquery.js"></script>
        <?php $this->head() ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Bootstrap Core CSS -->
        <link href="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/css/sb-admin.css" rel="stylesheet">

        <!-- Morris Charts CSS -->
        <link href="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/css/morris.css" rel="stylesheet">

        <!-- Custom Fonts -->
        <link href="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/css/font-awesome.min.css" rel="stylesheet" type="text/css">

        <style>
            .breadcrumb {
                margin-top: 1%;
            }
            .site-index {
                margin-top: 1%;
            }
            .navbar-nav.navbar-right.nav {
                margin-right: -10%;
            }
            .nav.navbar-nav.side-nav {
                width: 10%;
            }

            #LoadingMSG{
                display:none;
            }
            #LoadingMSG,#LoadingMSG img{
                margin:0 auto;
            }
            .overlay{
                background-color: rgba(0, 0, 0, 0.20);
                height: 100%;
                left: 0;
                position: fixed;
                right: 0;
                text-align: center;
                top: 0;
                width: 100%;
                z-index: 999;
            }
            .rejected {
                color: #bd1500;
            }
            .overlay img{
                position: relative;
                top: 40%;
            }

            /* .dropdown-menu > li > a {
                padding: 3px 19px!important;
            } */
            #fountainG{
                position:relative;
                width:390px;
                height:47px;
                margin:auto;
                top: 45%;
            }

            .fountainG{
                position:absolute;
                top:0;
                background-color:rgb(78,41,158);
                width:47px;
                height:47px;
                animation-name:bounce_fountainG;
                -o-animation-name:bounce_fountainG;
                -ms-animation-name:bounce_fountainG;
                -webkit-animation-name:bounce_fountainG;
                -moz-animation-name:bounce_fountainG;
                animation-duration:1.235s;
                -o-animation-duration:1.235s;
                -ms-animation-duration:1.235s;
                -webkit-animation-duration:1.235s;
                -moz-animation-duration:1.235s;
                animation-iteration-count:infinite;
                -o-animation-iteration-count:infinite;
                -ms-animation-iteration-count:infinite;
                -webkit-animation-iteration-count:infinite;
                -moz-animation-iteration-count:infinite;
                animation-direction:normal;
                -o-animation-direction:normal;
                -ms-animation-direction:normal;
                -webkit-animation-direction:normal;
                -moz-animation-direction:normal;
                transform:scale(.3);
                -o-transform:scale(.3);
                -ms-transform:scale(.3);
                -webkit-transform:scale(.3);
                -moz-transform:scale(.3);
                border-radius:31px;
                -o-border-radius:31px;
                -ms-border-radius:31px;
                -webkit-border-radius:31px;
                -moz-border-radius:31px;
            }

            #fountainG_1{
                left:0;
                animation-delay:0.496s;
                -o-animation-delay:0.496s;
                -ms-animation-delay:0.496s;
                -webkit-animation-delay:0.496s;
                -moz-animation-delay:0.496s;
            }

            #fountainG_2{
                left:49px;
                animation-delay:0.6125s;
                -o-animation-delay:0.6125s;
                -ms-animation-delay:0.6125s;
                -webkit-animation-delay:0.6125s;
                -moz-animation-delay:0.6125s;
            }

            #fountainG_3{
                left:98px;
                animation-delay:0.739s;
                -o-animation-delay:0.739s;
                -ms-animation-delay:0.739s;
                -webkit-animation-delay:0.739s;
                -moz-animation-delay:0.739s;
            }

            #fountainG_4{
                left:146px;
                animation-delay:0.8655s;
                -o-animation-delay:0.8655s;
                -ms-animation-delay:0.8655s;
                -webkit-animation-delay:0.8655s;
                -moz-animation-delay:0.8655s;
            }

            #fountainG_5{
                left:195px;
                animation-delay:0.992s;
                -o-animation-delay:0.992s;
                -ms-animation-delay:0.992s;
                -webkit-animation-delay:0.992s;
                -moz-animation-delay:0.992s;
            }

            #fountainG_6{
                left:244px;
                animation-delay:1.1085s;
                -o-animation-delay:1.1085s;
                -ms-animation-delay:1.1085s;
                -webkit-animation-delay:1.1085s;
                -moz-animation-delay:1.1085s;
            }

            #fountainG_7{
                left:293px;
                animation-delay:1.235s;
                -o-animation-delay:1.235s;
                -ms-animation-delay:1.235s;
                -webkit-animation-delay:1.235s;
                -moz-animation-delay:1.235s;
            }

            #fountainG_8{
                left:341px;
                animation-delay:1.3615s;
                -o-animation-delay:1.3615s;
                -ms-animation-delay:1.3615s;
                -webkit-animation-delay:1.3615s;
                -moz-animation-delay:1.3615s;
            }
            .call_schedule_count {
			  background-color: yellow;
			  color: red;
			  font-size: 18px;
			  font-weight: bold;
			}
        </style>
    </head>

    <body>
    <?php $this->beginBody() ?>
    <div id="wrapper">
        <div id="LoadingMSG" style="display: none;" class="overlay">
            <!-- <img src="/images/loading-large.gif"> -->
            <div id="fountainG">
                <div class="fountainG" id="fountainG_1"></div>
                <div class="fountainG" id="fountainG_2"></div>
                <div class="fountainG" id="fountainG_3"></div>
                <div class="fountainG" id="fountainG_4"></div>
                <div class="fountainG" id="fountainG_5"></div>
                <div class="fountainG" id="fountainG_6"></div>
                <div class="fountainG" id="fountainG_7"></div>
                <div class="fountainG" id="fountainG_8"></div>
            </div>
        </div>
        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">

                <?php
                NavBar::begin([
                    'brandLabel' => '<img src='.Yii::$app->request->baseUrl.'/images/logo.png height="180%" >',
                    'brandUrl' => Yii::$app->request->baseUrl,
                    'options' => [
                        'class' => 'navbar-inverse navbar-fixed-top',
                    ],
                ]);
                $menuItems = [
                    ['label' => 'Home', 'url' => ['site/index']],
                ];

                if (\Yii::$app->user->isGuest) {

                    $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
                } else {
                    $menuItems[] = ['label' => 'Export all E-Mail CSV of Jet', 'url' => ['/site/createcsv']];
                    $menuItems[] = ['label' => 'Export all E-Mail CSV of Walmart', 'url' => ['/site/exportcsv']];
                    $menuItems[] = [
                        'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                        'url' => ['/site/logout'],
                        'linkOptions' => ['data-method' => 'post']
                    ];
                }
                echo Nav::widget([
                    'options' => ['class' => 'navbar-nav navbar-right'],
                    'items' => $menuItems,
                ]);
                NavBar::end();
                ?>
            </div>
            <?php if (!\Yii::$app->user->isGuest) {?>
                <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
                <div class="collapse navbar-collapse navbar-ex1-collapse">
                    <ul class="nav navbar-nav side-nav">
                        <li >
                            <a href="<?=Yii::$app->request->baseUrl;?>/site/index"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/order-report/index"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Falied Orders</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/callschedule/index"><span class="call_schedule_count"><?= $pendingCalls;?></span><i class="fa fa-phone" aria-hidden="true"></i>Call Schedule</a>
                        </li>
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/jetshopdetails/index"><i class="fa fa-list-alt" aria-hidden="true"></i> Jet Shop Details</a>
                        </li>
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/searsextensiondetail/index"><i class="fa fa-list-alt" aria-hidden="true"></i> Sears Shop Details</a>
                        </li>
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/walmartshopdetails/index"><i class="fa fa-list-alt" aria-hidden="true"></i> Walmart Shop Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/jet-recurring-payment/index"><i class="fa fa-money" aria-hidden="true"></i> Jet Payment Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/sears-recurring-payment/index"><i class="fa fa-money" aria-hidden="true"></i> Sears Payment Details</a>
                        </li>
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/walmart-payment/index"><i class="fa fa-money" aria-hidden="true"></i> Walmart Payment Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/newegg-shop-detail/index"><i class="fa fa-bar-chart" aria-hidden="true"></i>Newegg Shop Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/newegg-client-detail/index"><i class="fa fa-bar-chart" aria-hidden="true"></i>Newegg Client Details</a>
                        </li>
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/jetshopdetails/productvalidation"><i class="fa fa-flag-checkered" aria-hidden="true"></i> Validate Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/walmartorderdetails/index"><i class="fa fa-bar-chart" aria-hidden="true"></i>Walmart Order Details</a>
                        </li>
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/jet-faq/index"><i class="fa fa-question" aria-hidden="true"></i> Update FAQ</a>
                        </li>
                        
                        <li>
                            <a style="float: left;" href="<?=Yii::$app->request->baseUrl;?>/magentoextensiondetail/index"><i class="fa fa-flag-checkered" aria-hidden="true"></i> Magento Clients</a>
                        </li>
						<li>
	                        <a href="<?=Yii::$app->request->baseUrl;?>/jetfileupload/index"><i class="fa fa-phone" aria-hidden="true"></i> Jet File Uploads</a>
	                   </li> 
	                    <li>
	                        <a href="<?=Yii::$app->request->baseUrl;?>/upcomingclients/index"><i class="fa fa-phone" aria-hidden="true"></i> Upcoming Clients</a>
	                   </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/jetclientdetails/index"><i class="fa fa-phone" aria-hidden="true"></i> Jet Client Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/walmart-client/index"><i class="fa fa-phone" aria-hidden="true"></i> Walmart Client Details</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/staff-merchant/index"><i class="fa fa-envelope" aria-hidden="true"></i>Merchant Accounts</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/site/sendmail"><i class="fa fa-envelope" aria-hidden="true"></i> Send Mail</a>
                        </li>

                        <!-- Add new updates -->
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/latest-updates/index"><i class="fa fa-envelope" aria-hidden="true"></i> Latest Updates</a>
                        </li>
                        
                        <!-- <li>
                        <a href="<?php //Yii::$app->request->baseUrl;?>/reports/dashboard/index"><i class="fa fa-bar-chart" aria-hidden="true"></i> Reports</a>
                   </li> -->
                        <!-- All template listed here -->
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/reports/jet-email-template/index"><i class="fa fa-bar-chart" aria-hidden="true"></i> Templates</a>
                        </li>
                        <!-- Email report status -->
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/reports/email-report/index"><i class="fa fa-bar-chart" aria-hidden="true"></i>Email Report</a>
                        </li>
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/common-notification/index"><i class="fa fa-bar-chart" aria-hidden="true"></i> Notification</a>
                        </li>

                        <!-- referral -->
                        <li class="dropdown">
                            <a class="dropdown-toggle" href="#" aria-expanded="false" data-toggle="dropdown">
                                <i class="fa fa-phone" aria-hidden="true"></i>Referral
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="<?=Yii::$app->request->baseUrl;?>/referrer/index">Referrers</a>
                                </li>
                                <li>
                                    <a href="<?=Yii::$app->request->baseUrl;?>/referral/index">Referral Installations</a>
                                </li>
                                <li>
                                    <a href="<?=Yii::$app->request->baseUrl;?>/referrer-redeem/index">Redeem Requests</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Issue list -->
                        <li>
                            <a href="<?=Yii::$app->request->baseUrl;?>/reports/issues/index"><i class="fa fa-bar-chart" aria-hidden="true"></i>Issues</a>
                        </li>

                        <li >
                            <a href="<?=Yii::$app->request->baseUrl;?>/jetmerchantshelp/index"><i class="fa fa-envelope-o" aria-hidden="true"></i> Help</a>
                        </li>
                        
                </div>
            <?php } ?>
            <!-- /.navbar-collapse -->
        </nav>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= $content ?>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <!-- Bootstrap Core JavaScript -->
    <!-- <script src="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/js/bootstrap.min.js"></script>-->
    <?php if (!\Yii::$app->user->isGuest && Yii::$app->controller->id.'/'.Yii::$app->controller->action->id == 'site/index') {?>
        <script src="<?= str_replace('/admin','',Yii::$app->request->baseUrl)?>/js/raphael.min.js"></script>

    <?php }?>

    <?php $this->endBody() ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $(document).on('pjax:send', function() {
                $('#LoadingMSG').show();
                console.log('pjax send');
            })
            $(document).on('pjax:complete', function() {
                $('#LoadingMSG').hide()
                console.log('pjax complete');
            })
            $('.validate').each(function(){
                var value=$(this).html();
                if($.trim(value)=="Not Purchased" || $.trim(value)=="Expired")
                {
                    $(this).addClass("rejected");
                }
            });
        });
    </script>

    </body>

    </html>
<?php $this->endPage() ?>