<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use app\models\JetProduct;
use app\models\JetErrorfileInfo;
use app\models\JetOrderDetail;
use app\models\JetOrderImportError;
use frontend\modules\walmart\components\Data;

use yii\bootstrap\ActiveForm;

$this->title = 'Shopify Walmart Integration | CedCommerce';
$this->params['breadcrumbs'][] = $this->title;

?>
<div id="home-page">
    <?php
    $this->registerCssFile(Yii::$app->request->baseUrl . "/css/animate.min.css");
    $this->registerCssFile(Yii::$app->request->baseUrl . "/css/creative.css");

    ?>
    <div class="trial-nav-wrap">
        <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <a class="navbar-brand page-scroll" href="#home-page"><img alt="Ced Commerce"
                                                                               src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/logo.png"></a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a class="page-scroll" href="#features">Features</a>
                        </li>
                        <li>
                            <a class="page-scroll" href="#services">Services</a>
                        </li>
                        <li>
                            <a class="page-scroll" href="#pricing">Pricing</a>
                        </li>
                        <li>
                            <a class="page-scroll" href="#contact">Contact</a>
                        </li>
                        <li>
                            <a class="page-scroll"
                               href="<?php echo Yii::$app->request->baseUrl ?>/walmart/sell-on-walmart"
                               target="_blank">How to Sell on Walmart?</a>
                        </li>
                    </ul>

                    <ul class="nav navbar-nav navbar-right navbar-2">
                        <li>
                            <a class="page-scroll" href="#features">Features</a>
                        </li>
                        <li>
                            <a class="page-scroll" href="#services">Services</a>
                        </li>
                        <li>
                            <a class="page-scroll" href="#pricing">Pricing</a>
                        </li>
                        <li>
                            <a class="page-scroll" href="#contact">Contact</a>
                        </li>
                        <li>
                            <a class="page-scroll"
                               href="<?php echo Yii::$app->request->baseUrl ?>/walmart/sell-on-walmart"
                               target="_blank">How to Sell on Walmart?</a>
                        </li>
                    </ul>
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.container-fluid -->
        </nav>
    </div>

    <div class="sticky-header"></div>
    <header class="header-section">
        <div class="header-overlay"></div>
        <div class="header-content">
            <div class="header-content-inner">
                <h1 class="wow fadeInUp">Start Selling On</h1>
                <img class="img-responsive wow fadeInUp walmart-img" alt="Walmart Marketplace Integration" data-wow-delay="0.2s" style="margin-top: 0;"
                     src=<?= Yii::$app->request->getBaseUrl() . '/frontend/modules/walmart/assets/images/walmart.png' ?>>
                <hr>
                <p>All in one platform for merchants to list products on walmart marketplace and manage their inventory,
                    order and shipping</p>
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['class' => 'form-horizontal'],
                    'action' => Data::getUrl('site/login'),
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"col-lg-12 shop-url-wrap\">{input}</div>",
                    ],
                ]); ?>

                <?= $form->field($model, 'username')->textInput(['placeholder' => 'example.myshopify.com']) ?>

                <?= Html::submitButton('Install', ['class' => 'install-me btn btn-primary button-inline btn btn-primary btn-xl page-scroll button-install ', 'name' => 'login-button']) ?>
                
                <!-- Code For Referral Start -->
                <?php if($ref_code = Yii::$app->request->get('ref', false)) { ?>
                    <input type="hidden" name="ref_code" value="<?= $ref_code ?>" />
                <?php } ?>
                <!-- Code For Referral End -->
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </header>
    <section class="bg-primary" id="features">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                    <h2 class="section-heading text-center">Most Useful Features</h2>
                    <hr class="">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns wow bounceIn">
                    <div class="panels panels-shortcode">
                        <div class="panel-item margin-bottom-60">
                            <div class="panels-icon text-color-theme">
                                <img src="<?= Yii::$app->request->baseUrl; ?>/img/icons/Auto-Acknowledge-Orders.png">
                            </div>
                            <div class="panels-text">
                                <h6>Auto Acknowledge Orders</h6>
                                Auto acknowledgement within 15 minutes of order placed on walmart.com.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns wow bounceIn">
                    <div class="panels panels-shortcode">
                        <div class="panel-item margin-bottom-60">
                            <div class="panels-icon text-color-theme">
                                <img src="<?= Yii::$app->request->baseUrl; ?>/img/icons/Products-Synchronization.png">
                            </div>
                            <div class="panels-text">
                                <h6>Products Synchronization</h6>
                                Synchronized product information on walmart with revised inventory.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns wow bounceIn">
                    <div class="panels panels-shortcode">
                        <div class="panel-item margin-bottom-60">
                            <div class="panels-icon text-color-theme">
                                <img src="<?= Yii::$app->request->baseUrl; ?>/img/icons/Orders-Management_2.png">
                            </div>
                            <div class="panels-text">
                                <h6>Orders Management</h6>
                                Easy listing and fulfilment with tracking information of walmart orders from shopify
                                store.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns">
                    <div class="panels panels-shortcode wow bounceIn">
                        <div class="panel-item margin-bottom-60">
                            <div class="panels-icon text-color-theme">
                                <img src="<?= Yii::$app->request->baseUrl; ?>/img/icons/Variants-Product-Support.png">
                            </div>
                            <div class="panels-text">
                                <h6>Variants Product Support</h6>
                                Variant products are one of the mostly used products in any E-Commerce store. Shopify
                                variant products support is provided with this app.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns">
                    <div class="panels panels-shortcode wow bounceIn">
                        <div class="panel-item margin-bottom-60">
                            <div class="panels-icon text-color-theme">
                                <img src="<?= Yii::$app->request->baseUrl; ?>/img/icons/Realtime-Error-Handling.png">
                            </div>
                            <div class="panels-text">
                                <h6>Realtime Error Handling</h6>
                                Realtime error handling is achieved with our app. If issues occur in any product
                                uploaded on walmart.com, proper messages are shown in the notification section for
                                errors.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns">
                    <div class="panels panels-shortcode wow bounceIn">
                        <div class="panel-item margin-bottom-60">
                            <div class="panels-icon text-color-theme">
                                <img src="<?= Yii::$app->request->baseUrl; ?>/img/icons/Dashboard.png">
                            </div>
                            <div class="panels-text">
                                <h6>Fully Featured Dashboard</h6>
                                Dashboard of our app gives the information about the total number of products uploaded
                                on walmart.com, number of live and archived products along with the number of orders
                                imported by the app and your shops lifetime sales.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center text-center">
                    <a id="get-start"
                       class="btn btn-primary button-inline btn btn-primary btn-xl page-scroll button-install page-scroll"
                       href="#home-page">Get Started!</a>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="ced-blue-bg1">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                    <h2 class="section-heading">At Your Service</h2>
                    <hr class="primary">
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">

                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/services/jet.png">
                        <hr>
                        <h6>Connect with Walmart</h6>
                        <p class="text-muted">Get notified automatically when any order is placed on Walmart for your
                            products!</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/services/Regular-Earning.png">
                        <hr>
                        <h6>Regular Earning</h6>
                        <p class="text-muted">Earn regularly with your products sold on walmart.com.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/services/DATE.png">
                        <hr>
                        <h6>Up to Date</h6>
                        <p class="text-muted">Remain updated every time something new happens to your products on
                            walmart.com.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/services/Love-It.png">
                        <hr>
                        <h6>Love It</h6>
                        <p class="text-muted">You will love it when you use it!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="bg-primary" id="pricing">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                    <h2 class="section-heading">Pricing</h2>
                    <span>Reasonable prices to cater your needs within your budget</span>
                    <hr class="primary">
                </div>
            </div>
        </div>
        <div class="container">
            <!-- <div class="col-xs-12 col-sm-6 col-sm-offset-3">
        <div class="jet-plan-wrapper active">
            <h3 class="plan-heading">Monthly plan</h3>
            <div class="plan-wrapper">
            <span class="price"><strong>$30</strong> per month</span>
                  <h3><b>(FREE 30 Days)</b></h3>
            <div class="clear"></div>
            <a href="https://apps.shopify.com/jet-integration" class="btn btn-primary button-inline btn btn-primary btn-xl page-scroll button-install">Add to cart</a>
            <div class="what-can-do">
        <p class="plush-sign1">+</p>
          <ul>
            <li>Unlimited Product Import</li>
            <li>Unlimited Order Fulfillment</li>
            <li>Inventory Synchronization</li>
            <li>Order Management</li>
            <li>Return Management</li>
            <li>Settlement</li>
          </ul>
            </div>
          </div>
        </div>
      </div>   -->
            <div class="shopify-plan-wrapper pricing-tables">
                <!--    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 shopify-plan-inner-wrap"></div> -->
                <?php 
                    echo $this->render('pricing');
                ?>

                <div class="extra-plane">
                    <div class="col-xs-12 col-md-12 col-sm-12 col-xs-12">
                        <h2 class="section-heading">Additional Perks</h2>
                        <hr class="primary">
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="plans">
                            <img height="auto" width="100"
                                 src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/free_installation1.png"
                                 class="sub-feature-images1">
                            <div class="extra-features-text">Free Installation</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="plans">
                            <img height="auto" width="100"
                                 src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/free_support1.png"
                                 class="sub-feature-images1">
                            <div class="extra-features-text">Free Support</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                        <div class="plans">
                            <img height="auto" width="100"
                                 src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/document.png"
                                 class="sub-feature-images1">
                            <div class="extra-features-text">Documentation</div>
                        </div>
                    </div>
                    <div style="clear:both"></div>
                </div>

            </div>
    </section>

    <section id="reviews" class="review-section">
        <div class="customer container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                    <h2 class="section-heading">What our client say</h2>
                    <hr class="primary">
                </div>
            </div>
            <div class="customer-review">
                <div class="text-center-1">

                    <div class="owl-carousel owl-theme customer-review-inner">
                        <div class="item">
                            <div class="bk-item">
                                <div class="row">
                                    <div class="col-lg-offset-2 col-md-offset-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                        <div class="img-wrapper">
                                            <div class="img-customer">
                                                <img class="lazy-loading"
                                                     src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/bariatric.png">
                                                <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                        <div class="">
                                            <div class="infomation-shop">
                                                <div class="name-shop">
                                                    <h3 class="review-heading">I am super impressed . . .</h3>
                                                </div>
                                                <div class="review">
                                                  <span class="rateit">
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                  </span>
                                                </div>

                                                <div class="information">
                                                    I am super impressed with this app. Within 72 hours we had over 500
                                                    of our products listed on Walmart.com . There are some little bugs
                                                    or issues but overall we are very satisfied. Their support is also
                                                    pretty responsive and helpful, even if english is their second
                                                    language.
                                                </div>
                                                <div class="name">
                                                    <a target="_blank" href="https://store.bariatricpal.com/">-
                                                        Bariatricpal</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="bk-item">
                                <div class="row">
                                    <div class="col-lg-offset-2 col-md-offset-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                        <div class="img-wrapper">
                                            <div class="img-customer">
                                                <img class="lazy-loading"
                                                     src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/swingdesign.png">
                                                <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                        <div class="">
                                            <div class="infomation-shop">
                                                <div class="name-shop">
                                                    <h3 class="review-heading">Swing Design . . .</h3>
                                                </div>
                                                <div class="review">
                                                  <span class="rateit">
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                  </span>
                                                </div>

                                                <div class="information">
                                                    We have been using this app for a few months now. The app allowed us to quickly start selling on Walmart.com. The app is well priced and orders flow right into our shopify account. We are able to manage everything within shopify, making it very easy to update information across multiple platforms. We also utilize their Jet.com app. Between the 2 apps we have seen our web sales grow 300%. If an issue came up, the Cedcommerce team worked hard to resolve it in a timely manner and ensure our sales were not disrupted.
                                                </div>
                                                <div class="name">
                                                    <a target="_blank" href="https://www.swingdesign.com/">-
                                                        Swing Design</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="bk-item">
                                <div class="row">
                                    <div class="col-lg-offset-2 col-md-offset-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                        <div class="img-wrapper">
                                            <div class="img-customer">
                                                <img class="lazy-loading"
                                                     src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/hawaiianstore.jpg">
                                                <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                        <div class="">
                                            <div class="infomation-shop">
                                                <div class="name-shop">
                                                    <h3 class="review-heading">Quick support. . .</h3>
                                                </div>
                                                <div class="review">
                                                  <span class="rateit">
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                  </span>
                                                </div>

                                                <div class="information">
                                                    Every time I have needed answers, the support has been quick, efficient and very easy to understand. My time is valuable and I feel like the people in charge of this realize that and do a great job at resolving any issues, as well as explaining things when I have questions or concerns
                                                </div>
                                                <div class="name">
                                                    <a target="_blank" href="https://hawaiian-store.myshopify.com/">-
                                                        Hawaiian Store</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="bk-item">
                                <div class="row">
                                    <div class="col-lg-offset-2 col-md-offset-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                        <div class="img-wrapper">
                                            <div class="img-customer">
                                                <img class="lazy-loading"
                                                     src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/tool.png">
                                                <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                        <div class="">
                                            <div class="infomation-shop">
                                                <div class="name-shop">
                                                    <h3 class="review-heading">Excellent support. . .</h3>
                                                </div>
                                                <div class="review">
                                                  <span class="rateit">
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                  </span>
                                                </div>

                                                <div class="information">
                                                    CEDCommerce has been a true partner in helping me launch on Walmart and become a successful seller. This APP is exactly what you need if you want to sell on Walmart.com. Every step of the way support has been there to help me. Thank you CED!
                                                </div>
                                                <div class="name">
                                                    <a target="_blank" href="https://tool.myshopify.com/">-
                                                        Tool</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="bk-item">
                                <div class="row">
                                    <div class="col-lg-offset-2 col-md-offset-2 col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                        <div class="img-wrapper">
                                            <div class="img-customer">
                                                <img class="lazy-loading"
                                                     src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/activedogtoys.png">
                                                <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                        <div class="">
                                            <div class="infomation-shop">
                                                <div class="name-shop">
                                                    <h3 class="review-heading">Works perfectly! Customer support is AMAZING! . . .</h3>
                                                </div>
                                                <div class="review">
                                                  <span class="rateit">
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                       <i class="glyphicon glyphicon-star"></i>
                                                  </span>
                                                </div>

                                                <div class="information">
                                                    Works perfectly! Customer support is AMAZING! Very helpful and will guide you through the process. It's very easy to get your shopify products onto the walmart marketplace (once you're approved by walmart)! I thought the process would take a long time but my products were live on walmart the next day!!! Happy selling!
                                                </div>
                                                <div class="name">
                                                    <a target="_blank" href="https://activedogtoys.myshopify.com/">-
                                                        Activedogtoys</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>

    </section>


    <aside class="ced-blue-bg">
        <div class="container text-center">
            <div class="call-to-action">
                <span>Install the app for Free on Your Shopify Shop from CedCommerce!</span>
                <a href="#page-top" style=" margin: 0 0 0 10px;" class="btn btn-default btn-xl wow tada"
                   onClick="$('html,body').animate({scrollTop:0},'slow');return false;">Install Now!</a>
            </div>
        </div>
    </aside>

    <section id="contact" class="bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <h2 class="section-heading">Let's Get In Touch!</h2>

                    <span>Ready to start your Shopify-Walmart Integration with us? That's great! Give us a call or send us an email and we will get back to you as soon as possible!</span>
                    <hr class="primary">
                </div>
                <div class="col-lg-4 col-lg-offset-2 text-center">
                    <i class="fa fa-ticket fa-3x wow bounceIn"></i>
                    <p><a href="http://support.cedcommerce.com" target="_blank">support.cedcommerce.com</a></p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fa fa-envelope-o fa-3x wow bounceIn" data-wow-delay=".1s"></i>
                    <p><a href="mailto:shopify@cedcommerce.com">shopify@cedcommerce.com</a></p>
                </div>
            </div>
        </div>
    </section>


    <!-- jQuery -->
    <?php /* <script src="js/jquery.js"></script>*/ ?>

    <!-- Bootstrap Core JavaScript -->
    <?php /*<script src="js/bootstrap.min.js"></script> */ ?>

    <!-- Plugin JavaScript -->

    <?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/bootstrap.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]); ?>

    <?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/jquery.easing.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]); ?>
    <?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/jquery.fittext.js', ['depends' => [\yii\web\JqueryAsset::className()]]); ?>
    <?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/wow.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]); ?>

    <?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/creative.js', ['depends' => [\yii\web\JqueryAsset::className()]]); ?>

    <?php /*
    <script src="js/jquery.easing.min.js"></script>
    <script src="js/jquery.fittext.js"></script>
    <script src="js/wow.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/creative.js"></script> */ ?>

    <!-- Custom Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800'
          rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic'
          rel='stylesheet' type='text/css'>

    <script>

    </script>
    <style>

        @media (max-width: 767px) {
            .ced-jet-navigation-mbl .navbar-collapse {
                background-color: #333;
                border: medium none;
                /*height: 100%;*/
                margin: 0;
                max-height: 95%;
                overflow-y: scroll !important;
                padding: 20px;
                position: fixed;
                left: -100%;
                top: 55px;
                transition: all 0.3s ease 0s;
                width: 250px;
            }

    </style>
