<style>
  .ced_price-box {
    width:100%;
    text-align:center;
    font-size:60px;
  }
  .addtocart{
    background:none repeat scroll 0 0 #FF5200;
    border-radius:5px;
    color: #FA942F;
    display: inline-block;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 19px;
    padding: 11px 92px;
    font-color:white;
  }
  .addtocart  {
    font-color: white !important;
  }
  ol, ul {
    list-style: none;
    margin: 0;
    padding: 0;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    list-style: decimal outside;
  }
  .what-can-do li {
    background-position: 262px 8px;
    background-repeat: no-repeat;
    background-size: 20px auto;
    font-size: 15px;
    line-height: 30px;
    list-style: outside none none;
    margin-bottom: 5px;
    padding-left: 297px;
    text-align: left;
  }
</style>


<!--=========================================
=            DESCRIPTION WRAPPER            =
==========================================-->
<section id="product-description-wrapp" class="section">
  <div class="container">
    <div class="product-description-wrapp"> 
      <div class="col-lg-4">
        <div class="shopify-image-wrap">
          <img class="jet-logo-big img-responsive" src="<?php echo Yii::$app->request->baseUrl?>/frontend/images/splash.png" width="500" height="400">
        </div>
        <div class="get-walmart">
          <a href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank" class="btn get-btn btn-block">GET</a>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="deascription-wrapp">
          <h4 class="product-name">Walmart Marketplace-Integration</h4>
          <p>
            CedCommerce Walmart Marketplace-Integration, an app developed by CedCommerce, 
            Riding high on the success of its <a href="https://apps.shopify.com/jet-integration" target="_blank">JET INTEGRATION</a> app, Cedcommerce offers seamless integration with the second largest marketplace of USA,Walmart.And Jet Integration App users can expect even better experience!
          </p>
          <div class="feature-list">
            <h4>Features</h4>
            <ul class="features">
              <li>Uploads products in bulk quickly. Yes, It’s fast!</li>
              <li>Imports the orders from Walmart, auto acknowledges them and fulfillment is done in the single step!</li>
              <li>Synchronizes the inventory in the real time.</li>
            </ul>
          </div>
          <!-- <a href="" class="btn get-btn">GET</a> -->
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
</section>
<!--====  End of DESCRIPTION WRAPPER  ====-->


<!--=====================================
=            PRICING SECTION            =
======================================-->
<section class="new_pricing_wrap section" id="pricing"> 
  <div class="container">
    <div class="common_heading_wrapp bot_mrg">
      <h1 class="wow fadeInDown" data-wow-delay="">PRICING</h1>
      <p class="wow fadeInDown" data-wow-delay="0.2s">WITHIN YOUR REACH</p>
      <img class="wow fadeInDown" data-wow-delay="0.3s" style="margin-top: 0;" src=<?= Yii::$app->request->getBaseUrl().'/images/divider.png'?>>
    </div>
    <div class="new_pricing_wrap_inner">
<!--       <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <div class="pricing-col basic wow fadeInUp" data-wow-delay="">
          <div class="pricing_head">
            <div class="plan-wrapper-up">
              <h3 class="plan-heading">STANDARD</h3>
              <div class="plan_duration">
                <h4>3 months plan</h4>
              </div>
              <div class="plan-wrapper">
                <span class="old-price">$90</span>
                <span class="price_val current_price">$</span><span class="walmart-price">60</span>
                <div class="clear"></div>
              </div>
              <div class="save_details">
                <p>Save <strong>$30</strong></p>
              </div>
            </div>
          </div>
          <div class="pricing_body">
            <div class="pricing_body_inner">
              <ul class="shopify-plan-list">
                <li class="yes">Discount</li>
                <li class="yes"> 5000 products Upload On Walmart </li>
                <li class="yes"> Unlimited Orders fulfill on Walmart </li>
                <li class="no"> Transaction fees </li>
                <li class="yes"> Real-time fulfillment </li>
                <li class="yes"> Free Setup  </li>
                <li class="yes"> 24/7 support </li>
                <li class="yes"> Auto fulfillment with Shipstation </li>
                <li class="yes"> Auto fulfillment with Shipwork </li>
                <li class="yes"> Skype Support  </li>
                <li class="yes"> Price Customization on Walmart </li>
              </ul>
            </div>
          </div>
        </div>
      </div> -->
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="pricing-col advance wow fadeInUp" data-wow-delay="0.2s">
          <div class="pricing_head">
            <div class="plan-wrapper-up">
              <h3 class="plan-heading">
                <img class="jet-shopify-hot-price" src=<?= Yii::$app->request->getBaseUrl().'/images/jet-shopify-hot-price.png'?>>
                BUSINESS</h3>
                <div class="plan_duration">
                  <h4>1 year plan</h4>
                </div>
                <div class="plan-wrapper">
                  <span class="old-price">$360</span>
                  <span class="price_val current_price">$</span><span class="walmart-price">270</span>
                  <div class="clear"></div>
                </div>
                <div class="save_details">
                  <p>Save <strong>$90</strong></p>
                </div>
              </div>
            </div>
            <div class="pricing_body">
              <div class="pricing_body_inner">
                <ul class="shopify-plan-list">
                 <li class="yes">Discount</li>
                 <li class="yes"> 10000 products Upload On Walmart </li>
                 <li class="yes"> 2000 Orders fulfill on Walmart </li>
                 <li class="no"> Transaction fees </li>
                 <li class="yes"> Real-time fulfillment </li>
                 <li class="yes"> Free Setup  </li>
                 <li class="yes"> 24/7 support </li>
                 <li class="yes"> Auto fulfillment with Shipstation </li>
                 <li class="yes"> Auto fulfillment with Shipwork </li>
                 <li class="yes"> Skype Support  </li>
                 <li class="yes"> Price Customization on Walmart </li>
               </ul>
             </div>
           </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="pricing-col featured wow fadeInUp" data-wow-delay="0.3s">
          <div class="pricing_head">
            <div class="plan-wrapper-up">
              <h3 class="plan-heading">PRO</h3>
              <div class="plan_duration">
                <h4>6 months plan</h4>
              </div>
              <div class="plan-wrapper">
                <span class="old-price">$180</span>
                <span class="price_val current_price">$</span><span class="walmart-price">150</span>
                <div class="clear"></div>
              </div>
              <div class="save_details">
                <p>Save <strong>$30</strong></p>
              </div>
            </div>
          </div>
          <div class="pricing_body">
            <div class="pricing_body_inner">
              <ul class="shopify-plan-list">
                <li class="yes">Discount</li>
                <li class="yes"> Unlimited products Upload On Walmart </li>
                <li class="yes"> Unlimited Orders fulfill on Walmart </li>
                <li class="no"> Transaction fees </li>
                <li class="yes"> Real-time fulfillment </li>
                <li class="yes"> Free Setup  </li>
                <li class="yes"> 24/7 support </li>
                <li class="yes"> Auto fulfillment with Shipstation </li>
                <li class="yes"> Auto fulfillment with Shipwork </li>
                <li class="yes"> Skype Support  </li>
                <li class="yes"> Price Customization on Walmart </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </div><!-- pricing table end -->
</section>
<!--====  End of PRICING SECTION  ====-->


<!--===================================
=            SUB FEATURES             =
====================================-->
<section class="bg-primary section doing-section" id="features">
  <div class="container">
    <div class="row">
      <div class="common_heading_wrapp bot_mrg">
        <h1 class="wow fadeInDown" data-wow-delay="">Doing it for you</h1>
        <img class="wow fadeInDown" data-wow-delay="0.3s" style="margin-top: 0;" src=<?= Yii::$app->request->getBaseUrl().'/images/divider.png'?>>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12 text-center">
        <div class="row">
          <div class="columns-wrap">
            <div class="col-md-4 columns">
              <div class="panels panels-shortcode row">
                <div class="col-xs-12 panel-item margin-bottom-60">
                  <div class="panels-icon text-color-theme">
                    <img class="img-responsive wow fadeInDown" src=<?= Yii::$app->request->getBaseUrl().'/images/005.png'?>>
                  </div>
                  <div class="panels-text wow fadeInUp">
                    <h6>Auto Acknowledge Orders</h6>
                    Auto accepts orders within 15 minutes
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 columns">
              <div class="panels panels-shortcode row">
                <div class="col-xs-12 panel-item margin-bottom-60">
                  <div class="panels-icon text-color-theme">
                    <img class="img-responsive wow fadeInDown" src=<?= Yii::$app->request->getBaseUrl().'/images/044.png'?>>
                  </div>
                  <div class="panels-text wow fadeInUp">
                    <h6>Products Synchronization</h6>
                    Updates any changes in products real time
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 columns">
              <div class="panels panels-shortcode row">
                <div class="col-xs-12 panel-item margin-bottom-60">
                  <div class="panels-icon text-color-theme">
                    <img class="img-responsive wow fadeInDown" src=<?= Yii::$app->request->getBaseUrl().'/images/031.png'?>>
                  </div>
                  <div class="panels-text wow fadeInUp">
                    <h6>Orders Management</h6>
                    Easily import products and make them ready for fulfillment       
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="columns-wrap">
            <div class="col-md-4 columns">
              <div class="panels panels-shortcode row">
                <div class="col-xs-12 panel-item margin-bottom-60">
                  <div class="panels-icon text-color-theme">
                    <img class="img-responsive wow fadeInDown" src=<?= Yii::$app->request->getBaseUrl().'/images/094.png'?>>
                  </div>
                  <div class="panels-text wow fadeInUp">
                    <h6>Variants Product Support</h6>
                    Virtual or real, supports any product
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 columns">
              <div class="panels panels-shortcode row">
                <div class="col-xs-12 panel-item margin-bottom-60">
                  <div class="panels-icon text-color-theme">
                    <img class="img-responsive wow fadeInDown" src=<?= Yii::$app->request->getBaseUrl().'/images/042.png'?>>
                  </div>
                  <div class="panels-text wow fadeInUp">
                    <h6>Realtime Error Handling</h6>
                    Get error details preventing products from uploading
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 columns">
              <div class="panels panels-shortcode row">
                <div class="col-xs-12 panel-item margin-bottom-60">
                  <div class="panels-icon text-color-theme">
                    <img class="img-responsive wow fadeInDown" src=<?= Yii::$app->request->getBaseUrl().'/images/089.png'?>>
                  </div>
                  <div class="panels-text wow fadeInUp">
                    <h6>Fully Featured Dashboard</h6>
                    Comprehensive dashboard accounting for all sales
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="text-center">
          <a id="get-start" class="btn get-start-btn btn-xl page-scroll button-install page-scroll wow fadeInDown" data-wow-delay="" href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank">Get Started!</a>
        </div>
      </div>
    </div>
  </div>
</section>
<!--====  End of SUB FEATURES   ====-->


<!--===================================
=            JET PLAN WRAP            =
====================================-->
<section class="customer_support section">
  <div class="container">
    <div class="common_heading_wrapp bot_mrg">
      <h1>Catch Points</h1>
      <p>How far we go, if you want</p>
      <img style="margin-top: 0;" src=<?= Yii::$app->request->getBaseUrl().'/images/divider.png'?>>
    </div>
    <div class="extra-plane">
      <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <img class="sub-feature-images1" src="/integration/images/free_installation1.png" height="auto" width="100">
        <div class="extra-features-text">Free Configuration & Uploading</div>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <img class="sub-feature-images1" src="/integration/images/free_support1.png" height="auto" width="100">
        <div class="extra-features-text">Expert Training</div>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <img class="sub-feature-images1" src="/integration/images/document.png" height="auto" width="100">
        <div class="extra-features-text">Documention</div>
      </div>
      <div style="clear:both"></div>
    </div>
  </div>
</section>
<!--====  End of JET PLAN WRAP  ====-->
