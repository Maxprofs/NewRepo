<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile("@jetbasepath/assets/css/creative.css", [], 'creative');
?>
<script type="text/javascript">
  $("link[href='<?= Yii::$app->request->getBaseUrl()?>/frontend/modules/jet/assets/css/bootstrap-material-design.css']").remove();
</script>
<div id="home-page">
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
    
                <a class="navbar-brand page-scroll" href="#home-page" ><img alt="Ced Commerce" src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/images/logo.png"></a>
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
                        <a class="page-scroll" href="#reviews">Reviews</a>
                    </li>
                    <li>
                      <a class="page-scroll" id="how-to-sell" href="<?= Yii::getAlias("@webjeturl")?>/how-to-sell-on-jet-com" target="_blank">How to Sell on JET?</a>
                    </li>
                    <script>
                    /*setTimeout(function(){
                      document.getElementById('how-to-sell').setAttribute("onclick", "event.stopPropagation();");
                      //document.getElementById('how-to-sell').onclick = function(event){event.stopPropagation();};
                    }, 5000);*/
                    
                    
                    </script>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <header class="header-section">
        <div class="header-overlay"></div>
        <div class="header-content">
            <div class="header-content-inner">
                <h1>Start Selling on</h1>
                <img class="img-responsive wow fadeInUp jet-img" data-wow-delay="0.2s" style="margin-top: 0;" src=<?= Yii::$app->request->getBaseUrl().'/images/jet-logo.png'?>>
                <hr>
                <p>All in one platform for merchants like you to list products on jet marketplace</p>
                <p>Manage your Inventory, Order and Shipping</p>
        <?php $form = ActiveForm::begin([
              'id' => 'login-form',
              'options' => ['class' => 'form-horizontal'],
              'action'  => Yii::getAlias("@webjeturl").'/site/login',
              'fieldConfig' => [
                  'template' => "{label}\n<div class=\"col-lg-12 shop-url-wrap\">{input}</div>",
               ],
          ]); ?>

          <?= $form->field($model, 'username')->textInput(['placeholder'=>'example.myshopify.com']) ?>
          
          <?= Html::submitButton('Login', ['class' => 'install-me btn btn-primary button-inline btn btn-primary btn-xl page-scroll button-install ', 'name' => 'login-button']) ?>

          <!-- Code For Referral Start -->
          <?php if($ref_code = Yii::$app->request->get('ref', false)) { ?>
              <input type="hidden" name="ref_code" value="<?= $ref_code ?>" />
          <?php } ?>
          <!-- Code For Referral End -->
          
      <?php ActiveForm::end(); ?>
            </div>
        </div>
        <meta content="Jet Shopify Integration - Sell Shopify Store Products on jet.com" name="title">  
        <meta content="Jet Shopify Integration facilitates Shopify store owners to have an ultimate selling experience by mapping their Shopify store with Jet It synchronizes product listing, order management" name="description">  
        <meta content="jet shopify integration, sell your shopify store products on jet, jet api integration, jet integration app, jet marketplace, jet to shopify, shopify and jet, sell on jet marketplace, jet marketplace integration, sell on jet, integrate jet with shopify, selling on jet, shopify inventory management integration, inventory management" name="tags">
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
                              <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/img/icons/Auto-Acknowledge-Orders.png">
                          </div>
                          <div class="panels-text">
                                  <h6>Auto Acknowledge Orders</h6>
                                  Auto acknowledgement within 15 minutes of order placed on Jet.com.
                          </div>
                      </div>
                  </div>
              </div>


              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns wow bounceIn">
                  <div class="panels panels-shortcode">
                      <div class="panel-item margin-bottom-60">
                          <div class="panels-icon text-color-theme">
                              <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/img/icons/Products-Synchronization.png">
                          </div>
                          <div class="panels-text">
                              <h6>Products Synchronization</h6>
                              Synchronized product information on jet with revised inventory.
                          </div>
                      </div>
                  </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns wow bounceIn">
                  <div class="panels panels-shortcode">
                      <div class="panel-item margin-bottom-60">
                          <div class="panels-icon text-color-theme">
                              <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/img/icons/Orders-Management_2.png">
                          </div>
                          <div class="panels-text">
                              <h6>Orders Management</h6>
                                 Easy listing and fulfilment with tracking information of jet orders from shopify store.                  
                          </div>
                      </div>
                  </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns">
                  <div class="panels panels-shortcode wow bounceIn">
                     <div class="panel-item margin-bottom-60">
                        <div class="panels-icon text-color-theme">
                           <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/img/icons/Variants-Product-Support.png">
                        </div>
                        <div class="panels-text">
                           <h6>Variants Product Support</h6>
                           Variant products are one of the mostly used products in any E-Commerce store. Shopify variant products support is provided with this app.
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns">
                  <div class="panels panels-shortcode wow bounceIn">
                     <div class="panel-item margin-bottom-60">
                        <div class="panels-icon text-color-theme">
                          <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/img/icons/Realtime-Error-Handling.png">
                        </div>
                        <div class="panels-text">
                           <h6>Realtime Error Handling</h6>
                           Realtime error handling is achieved with our app. If issues occur in any product uploaded on jet.com, proper messages are shown in the notification section for errors.
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center columns">
                  <div class="panels panels-shortcode wow bounceIn">
                     <div class="panel-item margin-bottom-60">
                        <div class="panels-icon text-color-theme">
                          <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/img/icons/Dashboard.png">
                        </div>
                        <div class="panels-text">
                           <h6>Fully Featured Dashboard</h6>
                           Dashboard of our app gives the information about the total number of products uploaded on jet.com, number of live and archived products along with the number of orders imported by the app and your shops lifetime sales.
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center text-center">
                <a id="get-start" class="btn btn-primary button-inline btn btn-primary btn-xl page-scroll button-install page-scroll" href="#home-page">Get Started!</a>
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
                        <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/images/services/jet.png">
                        <hr>
                        <h6>Connect with Jet</h6>
                        <p class="text-muted">Get notified automatically when any order is placed on Jet for your products!</p>
                    </div>
                </div>               
     <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/images/services/Regular-Earning.png">
                        <hr>
                        <h6>Regular Earning</h6>
                        <p class="text-muted">Earn regularly with your products sold on Jet.com.</p>
                    </div>
                </div>
               
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/images/services/DATE.png">
                        <hr>
                        <h6>Up to Date</h6>
                        <p class="text-muted">Remain updated every time something new happens to your products on Jet.com.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 text-center">
                    <div class="service-box">
                        <img src="<?= Yii::getAlias('@jetbasepath/assets/') ?>/images/services/Love-It.png">
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
<div class="row clearfix">
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <div class="jet-plan-wrapper active monthly plan">
        <h3 class="plan-heading">Gold</h3>
        <div class="plan-wrapper">
          <span style="padding: 0px;margin-top:3%;" class="price"><strong> $30</strong><span class="month">/mo</span><br>( when billed half yearly)</span>
          <h3 class="free"><span>FREE 30 Days</span></h3>
          <p class="push-sign">Save $120</p>
          <div class="clear"></div>
          <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
          
        </div>
        <div class="what-can-do">
         
            <ul>
              <li>Initial Data Analysis</li>
              <li>Simple and Variants Product Management</li>
              <li>Jet Category/Attributes Mapping</li>
              <li>Inventory Synchronization</li>
              <li>Price Customization</li>
              <li>Order Management</li>
              <li>Real-time fulfillment</li>
              <li>FBA Integration</li>
              <li>Shipwork and Shipstation Integration</li>
              <li>Return Management</li>
              <li>Free Api Setup</li>
              <li>24/7 support</li>
              <li>Skype Support</li>
              <li>Flat 25% Saving</li>
            </ul>
          </div>
      </div>
    </div>
   <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <div class="jet-plan-wrapper yearly-plan">
         <h3 class="plan-heading">Platinum</h3>
        <div class="plan-wrapper">
          <span class="old-price"></span>
          <span class="price"><strong>$25</strong><span class="month">/mo</span><br> (when billed annually)</span>
          <h3 class="free"><span>FREE 30 Days</span></h3>
          <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
          <p class="push-sign">Save $180</p>
          
        </div>
        <div class="what-can-do">
            
            <ul>
              <li>Initial Data Analysis</li>
              <li>Simple and Variants Product Management</li>
              <li>Jet Category/Attributes Mapping</li>
              <li>Inventory Synchronization</li>
              <li>Price Customization</li>
              <li>Order Management</li>
              <li>Real-time fulfillment</li>
              <li>FBA Integration</li>
              <li>Shipwork and Shipstation Integration</li>
              <li>Return Management</li>
              <li>Free Api Setup</li>
              <li>24/7 support</li>
              <li>Skype Support</li>
              <li>Flat 37.50% Saving</li>
            </ul>
          </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <div class="jet-plan-wrapper active monthly plan">
        <h3 class="plan-heading">Silver</h3>
        <div class="plan-wrapper">
          <span style="padding: 0px;margin-top:3%;" class="price"><strong> $40</strong><span class="month">/mo</span><br>( when billed monthly)</span>
          <h3 class="free"><span>FREE 30 Days</span></h3>
          <div class="clear"></div>
          <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
          
        </div>
        <div class="what-can-do">
         
            <ul>
              <li>Initial Data Analysis</li>
              <li>Simple and Variants Product Management</li>
              <li>Jet Category/Attributes Mapping</li>
              <li>Inventory Synchronization</li>
              <li>Price Customization</li>
              <li>Order Management</li>
              <li>Real-time fulfillment</li>
              <li>FBA Integration</li>
              <li>Shipwork and Shipstation Integration</li>
              <li>Return Management</li>
              <li>Free Api Setup</li>
              <li>24/7 support</li>
              <li>Skype Support</li>
            </ul>
          </div>
      </div>
    </div>
    </div>
   
    <div class="extra-plane">
      <div class="col-xs-12 col-md-12 col-sm-12 col-xs-12">
        <h2 class="section-heading">Additional Perks</h2>
        <hr class="primary">
      </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <div class="plans">
            <img height="auto" width="100" src="<?= Yii::getAlias('@jetbasepath/assets/')?>/images/free_installation1.png" class="sub-feature-images1">
            <div class="extra-features-text">Free Installation</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <div class="plans">
          <img height="auto" width="100" src="<?= Yii::getAlias('@jetbasepath/assets/')?>/images/free_support1.png" class="sub-feature-images1">
          <div class="extra-features-text">Free Support</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
          <div class="plans">
          <img height="auto" width="100" src="<?= Yii::getAlias('@jetbasepath/assets/')?>/images/document.png" class="sub-feature-images1">
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
                                   <img class="lazy-loading" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/swing.png" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/swing.png">
                                   <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                              </div>
                          </div>
                      </div>
                      <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                          <div class="">
                              <div class="infomation-shop">
                                  <div class="name-shop">
                                      <a>Recommend others try it out . . .</a>
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
                                      I have been using the jet integration for about 2 months now. CedCommerce has done a great job in helping me to get up and running + continuing to make enhancements to their app. The app works well and I would recommend others try it out. 
                                  </div>
                                  <div class="name">
                                    <a target="_blank" href="http://www.swingdesign.com/"><span>- Swing Design</span></a>
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
                                  <img class="lazy-loading" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/bariatric.png" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/bariatric.png">
                                  <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                              </div>
                          </div>
                      </div>
                      <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                          <div class="">
                              <div class="infomation-shop">
                                  <div class="name-shop">
                                      <a>very impressed with this app</a>
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
                                      I am very impressed with this app. It's allowed us to sell on Jet.com which surprisingly has brought us quite a few new orders daily. Their support is also very responsive.
                                  </div>
                                  <div class="name">
                                    <a target="_blank" href="https://store.bariatricpal.com/"><span>- Bariatricpal</span></a>
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
                                  <img class="lazy-loading" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/trinity.png" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/trinity.png">
                                  <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i>
</span>
                              </div>
                          </div>
                      </div>
                      <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                          <div class="">
                              <div class="infomation-shop">
                                  <div class="name-shop">
                                      <a>these guys are the best</a>
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
                                      I am changing my rating to a 5 star. My reason is this: I have over 3000 products and every issue I have has been answered, whether it is theirs or Jets. I use apps all the time, I am a developer and never have this type of support. Support is what makes an application and these guys are the best! Their app just works and that's what an app should do. Thank you guys!
                                      Love this app... 
                                  </div>
                                  <div class="name">
                                    <a target="_blank" href="https://www.trinitysunshades.com/"><span>- Trinityreflections</span></a>
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
                                  <img class="lazy-loading" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/blue.png" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/blue.png">
                                  <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                              </div>
                          </div>
                      </div>
                      <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                          <div class="">
                              <div class="infomation-shop">
                                  <div class="name-shop">
                                      <a>customer service is excellent !! </a>
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
                                      Great way to get products uploaded to Jet quickly and efficiently. They helped with the API for my store (www.bluebangle.com) which has over 4000 products. I would not have been able to do this without this app. Customer service is excellent -- they are online and interactive -- solving issues and making changes on the fly. Highly recommend. 
                                  </div>
                                  <div class="name">
                                    <a target="_blank" href="https://www.bluebangle.com/"><span>- Blue Bangle</span></a>
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
                                  <img class="lazy-loading" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/moderno.png" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/moderno.png">
                                  <span class="quote"><i class="fa fa-quote-left" aria-hidden="true"></i></span>
                              </div>
                          </div>
                      </div>
                      <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                          <div class="">
                              <div class="infomation-shop">
                                  <div class="name-shop">
                                      <a target="_blank" href="https://www.modernokids.com/">This is the only app . . .</a>
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
                                      Works if this is the only app connected to your Jet.com account, otherwise lots of problems. Installation requirements should mention this limitation.
                                  </div>
                                  <div class="name">
                                    <a target="_blank" href="https://www.modernokids.com/"><span>- Moderno Kids</span></a>
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
                <a href="https://apps.shopify.com/jet-integration" style=" margin: 0 0 0 10px;" class="btn btn-default btn-xl wow tada" target="_blank">Install Now!</a>
            </div>
        </div>
    </aside>

    <!-- Plugin JavaScript -->

 <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/bootstrap.min.js',['depends' => [\yii\web\JqueryAsset::className()]]); ?>

    <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/jquery.easing.min.js',['depends' => [\yii\web\JqueryAsset::className()]]); ?>
    <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/jquery.fittext.js',['depends' => [\yii\web\JqueryAsset::className()]]); ?>
    <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/wow.min.js',['depends' => [\yii\web\JqueryAsset::className()]]); ?>
    <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/owl.carousel.js',['depends' => [\yii\web\JqueryAsset::className()]]); ?>
    <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/creative.js',['depends' => [\yii\web\JqueryAsset::className()]]); ?>

<?php /*
    <script src="js/jquery.easing.min.js"></script>
    <script src="js/jquery.fittext.js"></script>
    <script src="js/wow.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/creative.js"></script> */ ?>

    <!-- Custom Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>

   
<script>
</script>
<style type="text/css">
  @media (max-width: 767px) {
      .ced-jet-navigation-mbl .navbar-collapse {
      top: 80px;
}
}
    

</style>


