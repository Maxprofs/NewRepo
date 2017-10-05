<?php
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Installation;
use yii\helpers\Html;
use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\components\PricingPlaninfo;



$this->title = 'Pricing Plan';
$same_sku_array_count = 0;
$merchant_id=Yii::$app->user->identity->id;
$shop_url = Yii::$app->user->identity->username;
$paymenturl = \yii\helpers\Url::toRoute(['site/payment']);
$coupanurl = \yii\helpers\Url::toRoute(['site/apply-coupan']);


$payment_plan = PricingPlaninfo::getPaymentplan();     
$countProducts = PricingPlaninfo::getProductcount();

?>
<style>
  .glyphicon{
    margin-left:-2%;
  }
</style>

<div class="payment_preview containers">
  <div class="generic-heading-shopify"><br>
    <span style="font-family: verdana;" id="product-count"><?= $countProducts; ?> Products in your store</span>
    <hr class="primary">
  </div>
 <div class="shopify-plan-wrapper pricing-tables container">
<!--    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 shopify-plan-inner-wrap"></div> -->
<div class="row clearfix" id="dynamic-paymentplan">
    <?php 
      foreach ($payment_plan as $value) {
      
    ?>
   <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <div class="jet-plan-wrapper yearly-plan">
        <h3 class="plan-heading"><?= $value['plan_name']; ?></h3>
        <div class="plan-wrapper">
          <span class="old-price">$<?= $value['base_price']; ?></span>
          <span class="price"><strong>$<?= $value['special_price']; ?></strong></span>
          <!-- <h3 class="free"><span>Free Trail</span></h3> -->
          <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
          <p class="push-sign"><?= $value['plan_type']; ?></p>   
        </div>
        
        
        <div class="what-can-do">
        <?= $value['feature']; ?>
        <?php 
          $connection = Yii::$app->getDb();
            $ad_condition = json_decode($value['additional_condition']);
            $plan_data = json_encode($value);
            if ($value['plan_type'] == "Free") {
              $countProducts = 20;
            }
            $product_charge = PricingPlaninfo::getProductcharge($ad_condition,$countProducts);
            $order_charge = PricingPlaninfo::getOrdercharge($ad_condition);

           if($product_charge['amount_type']=="Fixed")
            $product_amount = "$".$product_charge['amount'];
          else
            $product_amount = $product_charge['amount']."%";

          if($order_charge['amount_type']=="Fixed")
            $order_amount = "$".$order_charge['amount'];
          else
            $order_amount = $order_charge['amount']."%";

          if ($product_charge['amount']== 0) {
            $product_amount = "Free";
          }
        ?>

        <ul>
          <li><?= $product_charge['product_charge_name']." ".$product_amount  ?><p>You have to pay setup charge for your product</p></li>
          <li> <?= $product_charge['to_range']  ?> Products <p>You can import and upload <?= $product_charge['to_range']  ?> products</p></li>
         <li> <?= $order_charge['to_range'] ?> Orders <p>you can import <?= $order_charge['to_range'] ?> orders after that you have to pay</p></li>
        </ul>
        <form onsubmit="return false">
        <input type="hidden" name="price_charge" value="<?= $value['special_price']; ?>">
        <input type="hidden" name="time_duration" value="<?= $value['duration']; ?>">
        
        <button class="addtocart yearly-plan">
        <?php if ($value['plan_type'] == "Free") {
              echo "Activate Plan";
            } 
            else{
              echo "Choose this Plan";
            }
            ?>
            
        </button>
        <input type="hidden" name="setup_charge" value="<?= $product_charge['amount']; ?>">
        </form>
        <div id="data_locations" data-locations='[<?= $plan_data ?>]'></div>
          </div>

      </div>
    </div>
    <?php } ?>
    <div id="data_locate" data-locations=''></div>
</div>

<!-- invoice start -->

<div id="cart" style="display: block;" class="plan-invoice-wrapper">
  
    <div class="plan-invoice-heading">
      <h2>Invoice</h2>
    </div>
    <div class="invoice-bill-wrap">
    <dl class="charge_total">
        <dt>
          Time duration
        </dt>
        <dd id="plan_time_duration"></dd>
     
        <dt>Basic plan</dt>
        <dd id="basic_price">0</dd>
      
        <dt><input name="cart_setup_charge" id="cart_setup_charge" checked="" type="checkbox">setup charge</dt>
        <dd id="setup_price"></dd>
    </dl>
    <dl class="charge_total_2">
        <dt id="coupan_msg"></dt>
        <dd id="discount"></dd>
        <dt>Total</dt>
        <dd id="total_price"></dd>
     </dl>
     <a id="promo_code">Have you promo code ?</a>
     <dl class="charge_total_3">
        <dt>Coupan Code</dt>
        <dd><input name="coupan_code" id="coupan_code" type="text"><button onclick="Redeemcoupan()">Apply coupan</button></dd>
        <dd id="promo_error"></dd>
      </dl>
    </div>
      <div class="plan-btn">
        <button class ="change-plan-cart">change plan</button>
        <button onclick="Paynow()">Activate Plan</button>
      </div>
</div>
<!-- invoice end  -->
<!-- <div id="basic_plan_message">
  Thank you for choosing our Free plan ,In this plan you can able to import and upload 20 products and also can sync your first 20 orders.
  <div class="plan-btn">
        <button class="change-plan-cart">change plan</button>
        <button onclick="Paynow()">Activate Plan</button>
      </div>
</div> -->
<!-- message -->
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
  
    </div>
  </div>

  <script type="text/javascript">
  $(document).ready(function(){
    $("#cart").hide();
    $(".charge_total_3").hide();
    $(".registration-config-menu").hide();
    $("#basic_plan_message").hide();
  });
  $('#promo_code').click(function(){
    $(".charge_total_3").show();
    $('#promo_code').hide();
  });
  $(".change-plan-cart").click(function(){
    $("#cart").hide();
    $("#basic_plan_message").hide();
    $('#coupan_msg').text("");
    $('#discount').text("");
    $('#coupan_code').val("");
    $('html, body').animate({scrollTop : 0},800);
    $(".promo_code").show();
    $('#dynamic-paymentplan').show();

  });
    $(".addtocart").click(function(){
      var data =  $(this).parents("form").serializeArray();
      var plan_data = $(this).parents("form").siblings('#data_locations').data('locations');
      $('#data_locate').data('locations',plan_data)
      var cartData = [];
      for (var i = 0; i < data.length; i++) {
        cartData[data[i].name] = data[i].value;
      }
        $("#plan_time_duration").text(cartData.time_duration);
       $("#basic_price").text(cartData.price_charge);
        $("#setup_price").text(cartData.setup_charge);
          var total = parseInt(cartData.price_charge)+parseInt(cartData.setup_charge);
        if (cartData.setup_charge == 0) {
          cartData.setup_charge = "Free";
          $('#cart_setup_charge').hide();
        }
          $("#setup_price").text(cartData.setup_charge);
         $("#total_price").text(total);
       
        $('#dynamic-paymentplan').hide();
        $('html, body').animate({scrollTop : 0},1000);
        if(total == 0 ){
          $("#basic_plan_message").show();
        }else{
          $('#cart_setup_charge').show();
          $('#cart_setup_charge').attr('checked','checked');
          $("#cart").show();
        }
         if (cartData.setup_charge =="Free") {
          $('#cart_setup_charge').hide();
        }
        $('#cart_setup_charge').click(function() {
        if($(this).is(':checked')){
            var total = parseInt(cartData.price_charge)+parseInt(cartData.setup_charge);
          $("#total_price").text(total);
        }else{
           $("#total_price").text(cartData.price_charge);
         }
    });

    });
    function Redeemcoupan(){
      var coupan = $('#coupan_code').val();
      var app = "Jet";
      var price = $('#total_price').text();
      var plan_data = $('#data_locate').data('locations');
      var url = "<?= $coupanurl ?>";
    
      $.ajax({
      method: "post",
      url: url,
      data: {coupan: coupan,app:app,price:price,plan_data:plan_data}
      })
      .done(function (msg) 
      { var msg = JSON.parse(msg);
        alert(msg);
        if (msg.price) {
          $('#total_price').text(msg.price);
          $('#coupan_msg').text(msg.success);
          $('#discount').text("- "+msg.discount);
          $('#promo_code').hide();
          $('#cart_setup_charge').hide();
          $(".charge_total_3").hide();
         
        }
        else if(msg.error){
          $("#promo_error").text(msg.error);
        }
      })
    }
    function Paynow(){
      var price = $('#total_price').text();
      var time = $('#plan_time_duration').text();
      var coupan = $('#coupan_code').val();
      var plan_data = $('#data_locate').data('locations');
      var url = "<?= $paymenturl ?>";
      $.ajax({
      method: "post",
      url: url,
      data: {price: price,time: time,plan_data:plan_data}
      })
      .done(function (msg) 
      {
        alert(msg);
      })
    }
  </script>