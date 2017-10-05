<style>
    .glyphicon {
        margin-left: -2%;
    }
</style>

<div class="payment_preview containers">
    <h2 class="payment_preview_thanku" style="font-family: verdana;">Thank you for choosing Walmart-Integration App</h2>
    <div class="generic-heading-shopify">
        <h2 class="section-heading">Payment Plan</h2>
        <span style="font-family: verdana;">No obligations.Change plans anytime.Maximise your Earnings!</span>
        <hr class="primary">
    </div>
  
    <div class="container">
        <div class="shopify-plan-wrapper pricing-tables">
            <!--    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 shopify-plan-inner-wrap"></div> -->

            <?php                          

                echo $this->render('pricing');
            ?>

            <div class="extra-plane">
                <div class="col-xs-12">
                    <div class="generic-heading-shopify ">
                        <h2 class="section-heading">Additional Perks</h2>
                        <hr class="primary">
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="plans">
                        <img class="sub-feature-images1"
                             src="<?php echo Yii::$app->request->baseUrl ?>/images/free_installation1.png" width="100"
                             height="auto">
                        <div class="extra-features-text">Free Installation</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="plans">
                        <img class="sub-feature-images1"
                             src="<?php echo Yii::$app->request->baseUrl ?>/images/free_support1.png" width="100"
                             height="auto">
                        <div class="extra-features-text">Free Support</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="plans">
                        <img class="sub-feature-images1"
                             src="<?php echo Yii::$app->request->baseUrl ?>/images/document.png" width="100"
                             height="auto">
                        <div class="extra-features-text">Documention</div>
                    </div>
                </div>
                <div style="clear:both"></div>
            </div>

            <!-- boostrap model popup for images -->
            <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                            <!-- Wrapper for slides -->
                            <div class="carousel-inner">
                                <div class="item active" id='login'>
                                    <img class="img-responsive"
                                         src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/images/login.png"
                                         alt="Login">
                                    <div class="carousel-caption">
                                        Login on jet partner panel
                                    </div>
                                </div>
                                <div class="item" id='test'>
                                    <img class="img-responsive"
                                         src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/images/test-api.png"
                                         alt="test api">
                                    <div class="carousel-caption">
                                        API Section
                                    </div>
                                </div>
                                <div class="item" id='fulfill'>
                                    <img class="img-responsive"
                                         src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/images/fulfillment.png"
                                         alt="fulfillment node">
                                    <div class="carousel-caption">
                                        Fulfillment Section
                                    </div>
                                </div>
                                <div class="item" id='filled-api'>
                                    <img class="img-responsive"
                                         src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/images/test-api-filled.png"
                                         alt="api congiguration">
                                    <div class="carousel-caption">
                                        Enter test api on app
                                    </div>
                                </div>
                                <div class="item" id='live'>
                                    <img class="img-responsive"
                                         src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/images/live-api.png"
                                         alt="live api">
                                    <div class="carousel-caption">
                                        Live api on dashboard
                                    </div>
                                </div>
                            </div>
                            <!-- Controls -->
                            <a class="left carousel-control" href="#carousel-example-generic" role="button"
                               data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="right carousel-control" href="#carousel-example-generic" role="button"
                               data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- review slider -->

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
                                                        <span class="quote"><i class="fa fa-quote-left"
                                                                               aria-hidden="true"></i></span>
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
                                                  </span>
                                                        </div>

                                                        <div class="information">
                                                            I am super impressed with this app. Within 72 hours we had
                                                            over 500
                                                            of our products listed on Walmart.com . There are some
                                                            little bugs
                                                            or issues but overall we are very satisfied. Their support
                                                            is also
                                                            pretty responsive and helpful, even if english is their
                                                            second
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
                                                        <span class="quote"><i class="fa fa-quote-left"
                                                                               aria-hidden="true"></i></span>
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
                                                            We have been using this app for a few months now. The app
                                                            allowed us to quickly start selling on Walmart.com. The app
                                                            is well priced and orders flow right into our shopify
                                                            account. We are able to manage everything within shopify,
                                                            making it very easy to update information across multiple
                                                            platforms. We also utilize their Jet.com app. Between the 2
                                                            apps we have seen our web sales grow 300%. If an issue came
                                                            up, the Cedcommerce team worked hard to resolve it in a
                                                            timely manner and ensure our sales were not disrupted.
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
                                                        <span class="quote"><i class="fa fa-quote-left"
                                                                               aria-hidden="true"></i></span>
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
                                                            Every time I have needed answers, the support has been
                                                            quick, efficient and very easy to understand. My time is
                                                            valuable and I feel like the people in charge of this
                                                            realize that and do a great job at resolving any issues, as
                                                            well as explaining things when I have questions or concerns
                                                        </div>
                                                        <div class="name">
                                                            <a target="_blank"
                                                               href="https://hawaiian-store.myshopify.com/">-
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
                                                        <span class="quote"><i class="fa fa-quote-left"
                                                                               aria-hidden="true"></i></span>
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
                                                            CEDCommerce has been a true partner in helping me launch on
                                                            Walmart and become a successful seller. This APP is exactly
                                                            what you need if you want to sell on Walmart.com. Every step
                                                            of the way support has been there to help me. Thank you CED!
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
                                                        <span class="quote"><i class="fa fa-quote-left"
                                                                               aria-hidden="true"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                                <div class="">
                                                    <div class="infomation-shop">
                                                        <div class="name-shop">
                                                            <h3 class="review-heading">Works perfectly! Customer support
                                                                is AMAZING! . . .</h3>
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
                                                            Works perfectly! Customer support is AMAZING! Very helpful
                                                            and will guide you through the process. It's very easy to
                                                            get your shopify products onto the walmart marketplace (once
                                                            you're approved by walmart)! I thought the process would
                                                            take a long time but my products were live on walmart the
                                                            next day!!! Happy selling!
                                                        </div>
                                                        <div class="name">
                                                            <a target="_blank"
                                                               href="https://activedogtoys.myshopify.com/">-
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


        </div>
    </div>
</div>


<script type="text/javascript">
    function changeImage(id) {
        $('div.carousel-inner div').removeClass('active');
        if (id == 'test_api') {
            $('div.carousel-inner div#login').addClass('active');
        } else if (id == 'activate_api') {
            $('div.carousel-inner div#filled-api').addClass('active');
        } else if (id == 'live_api') {
            $('div.carousel-inner div#live').addClass('active');
        }
    }

</script>