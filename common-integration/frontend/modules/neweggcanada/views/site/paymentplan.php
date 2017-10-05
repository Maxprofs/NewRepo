
<style>
    .glyphicon{
        margin-left:-2%;
    }
</style>

<div class="payment_preview containers">
    <h2 style="font-family: verdana;" class="payment_preview_thanku">Thank you for choosing Newegg-Integration App</h2>
    <div class="generic-heading-shopify">
        <h2 class="section-heading">Payment Plan</h2>
        <span style="font-family: verdana;">No obligations.Change plans anytime.Maximise your Earnings!</span>
        <hr class="primary">
    </div>
    <div class="container">
        <div class="shopify-plan-wrapper pricing-tables">
            <!--    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 shopify-plan-inner-wrap"></div> -->
            <div class="row clearfix">
                <div class="col-lg-offset-2 col-md-offset-2 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <div class="jet-plan-wrapper yearly-plan">
                        <h3 class="plan-heading">Yearly Plan</h3>
                        <div class="plan-wrapper">
                            <span class="old-price"></span>
                            <span class="price"><strong>$25</strong><span class="month">/mo</span><br> (USD billed annually)</span>

                            <h3 class="free"><span>FREE 15 Days</span></h3>
                            <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
                            <p class="push-sign">Save 17%</p>


                        </div>
                        <a href="<?= Yii::$app->request->getBaseUrl().'/neweggcanada/site/paymentplan?plan=3' ?>">
                            <div class="addtocart yearly-plan">
                                Choose this Plan
                            </div>
                        </a>
                        <div class="what-can-do">

                            <ul>
                                <li>Simple and Variants Product Management</li>
                                <li>Mapping Newegg Category with Product Type</li>
                                <li>Inventory Synchronization</li>
                                <li>Order Management</li>
                                <li>Return Management</li>
                                <li>Settlement</li>
                                <li>Flat 17% Saving</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <div class="jet-plan-wrapper active monthly-plan">
                        <h3 class="plan-heading">Half Yearly plan</h3>
                        <div class="plan-wrapper">
                            <span style="padding: 0px;margin-top:3%;" class="price"><strong> $27</strong><span class="month">/mo</span><br>(USD billed half-yearly)</span>
                            <h3 class="free"><span>FREE 15 Days</span></h3>

                            <div class="clear"></div>
                            <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->

                        </div>
                        <a href="<?= Yii::$app->request->getBaseUrl().'/neweggcanada/site/paymentplan?plan=2' ?>">
                            <div class="addtocart yearly-plan">
                                Choose this Plan
                            </div>
                        </a>
                        <div class="what-can-do">

                            <ul>
                                <li>Simple and Variants Product Management</li>
                                <li>Mapping Newegg Category with Product Type</li>
                                <li>Inventory Synchronization</li>
                                <li>Order Management</li>
                                <li>Return Management</li>
                                <li>Settlement</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="extra-plane">
                <div class="col-xs-12">
                    <div class="generic-heading-shopify ">
                        <h2 class="section-heading">Additional Perks</h2>
                        <hr class="primary">
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="plans">
                        <img class="sub-feature-images1" src="<?php echo Yii::$app->request->baseUrl?>/images/free_installation1.png" width="100" height="auto">
                        <div class="extra-features-text">Free Installation</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="plans">
                        <img class="sub-feature-images1" src="<?php echo Yii::$app->request->baseUrl?>/images/free_support1.png" width="100" height="auto">
                        <div class="extra-features-text">Free Support</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="plans">
                        <img class="sub-feature-images1" src="<?php echo Yii::$app->request->baseUrl?>/images/document.png" width="100" height="auto">
                        <div class="extra-features-text">Documention</div>
                    </div>
                </div>
                <div style="clear:both"></div>
            </div>
            <!-- boostrap model popup for images -->
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                            <!-- Wrapper for slides -->
                            <div class="carousel-inner">
                                <div class="item active" id='login'>
                                    <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/login.png" alt="Login">
                                    <div class="carousel-caption">
                                        Login on jet partner panel
                                    </div>
                                </div>
                                <div class="item" id='test'>
                                    <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/test-api.png" alt="test api">
                                    <div class="carousel-caption">
                                        API Section
                                    </div>
                                </div>
                                <div class="item" id='fulfill'>
                                    <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/fulfillment.png" alt="fulfillment node">
                                    <div class="carousel-caption">
                                        Fulfillment Section
                                    </div>
                                </div>
                                <div class="item" id='filled-api'>
                                    <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/test-api-filled.png" alt="api congiguration">
                                    <div class="carousel-caption">
                                        Enter test api on app
                                    </div>
                                </div>
                                <div class="item" id='live'>
                                    <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/live-api.png" alt="live api">
                                    <div class="carousel-caption">
                                        Live api on dashboard
                                    </div>
                                </div>
                            </div>
                            <!-- Controls -->
                            <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- review slider -->

           <!-- <section id="reviews" class="review-section">
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
                                                        <img class="lazy-loading" data-src="/integration/images/bariatric.png" src="/integration/images/bariatric.png">
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
                                                            I am super impressed with this app. Within 72 hours we had over 500 of our products listed on Walmart.com . There are some little bugs or issues but overall we are very satisfied. Their support is also pretty responsive and helpful, even if english is their second language.
                                                        </div>
                                                        <div class="name">
                                                            <a target="_blank" href="https://store.bariatricpal.com/">- Bariatricpal</a>
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

            </section>-->

        </div>
    </div>
</div>


<script type="text/javascript">
    function changeImage(id){
        $('div.carousel-inner div').removeClass('active');
        if(id=='test_api'){
            $('div.carousel-inner div#login').addClass('active');
        }else if(id=='activate_api'){
            $('div.carousel-inner div#filled-api').addClass('active');
        }else if(id=='live_api'){
            $('div.carousel-inner div#live').addClass('active');
        }
    }

</script>