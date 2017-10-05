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
$p_counturl = \yii\helpers\Url::toRoute(['jet-install/product-counts']);
$payment_plan = PricingPlaninfo::getPaymentplan();     
$countProducts = 0;//PricingPlaninfo::getProductcount($merchant_id);
if (isset($_POST['total_count'])) {
  $countProducts = $_POST['total_count'];
}
?>

<style>
  .glyphicon{
    margin-left:-2%;
  }
</style>
<div id="main_container_pricing">
<div class="payment_preview containers">
  <div class="generic-heading-shopify"><br>
    <span style="font-family: verdana;" id="product-count"><?= $countProducts; ?> Products(with variants) in your store</span>
    <hr class="primary">
  </div>
 <div class="shopify-plan-wrapper pricing-tables container">
<div class="row clearfix" id="dynamic-paymentplan">
  <?php 
  foreach ($payment_plan as $value) 
  {
  ?>
  <form method="post" action="#" id="plan-<?=$value['id']?>">
   <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <div class="jet-plan-wrapper yearly-plan">
        <h3 class="plan-heading"><?= $value['plan_name']; ?></h3>
        <div class="plan-wrapper">
          <span class="old-price">$<?= $value['base_price']; ?></span>
          <span class="price"><strong>$<?= $value['special_price']; ?></strong></span>
          
          <p class="push-sign"><?= $value['plan_type']; ?></p>  
          <span class="time_duration"><?= $value['duration']; ?></span> 
          <?php if($value['trial_period'])
          {
            echo "<span class='trial-period'>".$value['trial_period']."</span>";
          }?>
        </div>
        
        <input type="hidden" name="price_charge" value="<?= $value['special_price']; ?>">
        <input type="hidden" name="time_duration" value="<?= $value['duration']; ?>">
        <div class="what-can-do">
          <?= $value['feature']; ?>
          <?php 
          //$connection = Yii::$app->getDb();
          $html = "";
          if($value['additional_condition'])
          {
            $conditionIds = implode(',',json_decode($value['additional_condition']));
            $preparedConditionData = PricingPlaninfo::prepareConditionData($conditionIds);
           
            if(is_array($preparedConditionData) && count($preparedConditionData)>0)
            {
              $html = '<ul class="tooltip_feature">';
              foreach ($preparedConditionData as $cond_val) 
              {
                if($cond_val['condition_apply_for']=="Merchant" && $cond_val['merchant_ids'] && !in_array($merchant_id, explode(',', $cond_val['merchant_ids'])))
                {
                  continue;
                }
                //prepare range/fixed data with price value
                $preparedConditionData[$value['id']]['condition_price_range'] = PricingPlaninfo::chargeCulculator($cond_val['condition_apply_on'],$cond_val['condition_charge_type'],$cond_val['condition_price_range']);
                if($cond_val['condition_apply_on']=="Product")
                {
                  //calculate product charges
                  $productChargeAmount = 0;
                  $productChargeAmount = round(PricingPlaninfo::productChargeCulculator($countProducts,$preparedConditionData[$value['id']]['condition_price_range'],$cond_val['condition_charge_type']),2);
                  if($productChargeAmount)
                  {
                    $html.= '<input type="hidden" value="'.$productChargeAmount.'" name=planData["'.$value['id'].'"]["product_charge"]>';
                    $html.='<li class="tooltip-content">$'.$productChargeAmount." ".$cond_val["condition_name"].'<p class="tooltip-content-inner">'.$cond_val["condition_description"].'</p></li>';  
                  }

                }
                $html.='<li class="tooltip-content">'.$cond_val["condition_name"].'<p class="tooltip-content-inner">'.$cond_val["condition_description"].'</p></li>'; 
              } 
              $html.='</ul>'; 
            }
            
          }
          
          /*if ($value['plan_type'] == "Free" && $countProducts!=0) {
            $countProducts = 20;
          }
          $product_charge = PricingPlaninfo::getProductcharge($ad_condition,$countProducts);
          $order_charge = PricingPlaninfo::getOrdercharge($ad_condition);
          $basic_product_charge = PricingPlaninfo::getBasicproductcharge($ad_condition);
          $basic_order_charge = PricingPlaninfo::getBasicordercharge($ad_condition);
          if($product_charge['amount_type']=="Fixed" && !empty($product_charge['fixed_range'])){
            $product_amount = "$".$product_charge['amount'];
            $product_count = $product_charge['fixed_range'];
          }
          else{
            $product_amount = $product_charge['amount']."%";
            $product_count = $product_charge['to_range'];
          }
          if($order_charge['amount_type']=="Fixed" && !empty($product_charge['fixed_range'])){
            $order_amount = "$".$order_charge['amount'];
            $order_count = $order_charge['fixed_range'];
          }
          else{
            $order_amount = $order_charge['amount']."%";
            $order_count = $order_charge['to_range'];
          }
          if ($product_charge['amount']== 0) {
            $product_amount = "Free";
          }
          ?>
          <ul class="tooltip_feature">
             <li class="tooltip-content"><?= $product_charge['product_charge_name']." ".$product_amount  ?>
              <p class="tooltip-content-inner">You have to pay setup charge for your product</p>
             </li>
            <li class="tooltip-content"> <?= $product_count;  ?> Products 
              <p class="tooltip-content-inner">You can import and upload <?= $product_count;  ?> products</p>
            </li>
           <li class="tooltip-content"> <?= $order_count ?> Orders 
            <p class="tooltip-content-inner">you can import <?= $order_count ?> orders after that you have to pay</p>
           </li>
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
          <?php $plan_data = array();
                $plan_data['product_count']['limit'] = $product_count;
                $plan_data['order_count']['limit'] =  $order_count;
                $plan_data['product_count']['basic'] = $basic_product_charge;
                $plan_data['order_count']['basic'] =  $basic_order_charge;
                $plan_data['choose_plan_data'] = $value;
                $plan_data = json_encode($plan_data); ?>
          <input type="hidden" name="setup_charge" value="<?= $product_charge['amount']; ?>">
          </form>
          <div class="data_locations" data-locations='[<?= $plan_data ?>]'></div>
            </div>
          <?php */?>
        </div>
      </div>
      </div>  
  </form>
  <?php 
  }?>
</div>  
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
        <form method="post" action="<?= \yii\helpers\Url::toRoute(['site/payment']); ?>">
          <input type="hidden" name="Activate_plan_data" value="" id="Activate_plan_data">
          <input type="hidden" name="Activate_plan_price" value="" id="Activate_plan_price">
          <input type="hidden" name="Activate_plan_time" value="" id="Activate_plan_time">
          <input type="hidden" name="Activate_plan_coupan" value="" id="Activate_plan_coupan">
          <input type="hidden" name="setup_charge_check" value="Yes" id="setup_charge_check" >
          <button id="Activate_plan_submit" type="submit">Activate Plan</button>
        </form>
        
      </div>
</div>
<!-- invoice end  -->
<div id="basic_plan_message" class="plan-success-msg">
<i class="fa fa-check"></i>
  <p>Thank you for choosing our Free plan ,In this plan you can able to import and upload 20 products and also can sync your first 20 orders.</p>
  <div class="plan-btn">
    <button class ="change-plan-cart">change plan</button>
    <form method="post" action="<?= \yii\helpers\Url::toRoute(['site/payment']); ?>">
      <input type="hidden" name="Activate_plan_data" value="" id="Activate_plan_data">
      <input type="hidden" name="Activate_plan_price" value="" id="Activate_plan_price">
      <input type="hidden" name="Activate_plan_time" value="" id="Activate_plan_time">
      <input type="hidden" name="Activate_plan_coupan" value="" id="Activate_plan_coupan">
      <input type="hidden" name="setup_charge_check" value="Yes" id="setup_charge_check" >
      <button id="Activate_plan_submit" type="submit">Activate Plan</button>
    </form>
  </div>
</div> 
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
      var plan_data = $(this).parents("form").siblings('.data_locations').data('locations');
      $('#data_locate').data('locations',plan_data);
      $("#Activate_plan_data").val(JSON.stringify(plan_data));
      //console.log(JSON.stringify(plan_data));
      var cartData = [];
      for (var i = 0; i < data.length; i++) {
        cartData[data[i].name] = data[i].value;
      }
        $("#plan_time_duration").text(cartData.time_duration);
        $("#Activate_plan_time").val(cartData.time_duration);
       $("#basic_price").text(cartData.price_charge);
        $("#setup_price").text(cartData.setup_charge);
          var total = parseInt(cartData.price_charge)+parseInt(cartData.setup_charge);
        if (cartData.setup_charge == 0) {
          cartData.setup_charge = "Free";
          $('#cart_setup_charge').hide();
        }
          $("#setup_price").text(cartData.setup_charge);
         $("#total_price").text(total);
       $("#Activate_plan_price").val(total);
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
      $("#Activate_plan_coupan").val(coupan);
      $.ajax({
      method: "post",
      url: url,
      data: {coupan: coupan,app:app,price:price,plan_data:plan_data}
      })
      .done(function (msg) 
      { var msg = JSON.parse(msg);
        if (msg.price) {
          $('#total_price').text(msg.price);
          $("#Activate_plan_price").val(msg.price);
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
     $("#cart_setup_charge").change(function(){
      if ($('#cart_setup_charge').is(":checked"))
      {
        $("#setup_charge_check").val('Yes');
        alert("Now you can upload all products");
      }
      else{
        $("#setup_charge_check").val('No');
        alert("You can not upload your all products with out setup charge");
      }
  });

  </script>
  <script type="text/javascript">
  $(document).ready(function(){
    $('.tooltip-content-inner').slideUp(0);
      $('.tooltip-content').click(function(){
      $(this).toggleClass('active');
      $(this).children('.tooltip-content-inner').slideToggle(200);
    });
  });
  
  </script>
  </div>
  <script type="text/javascript">
    $( window ).on( "load", function(){
    var url = '<?= $p_counturl ?>';
    var page = 0;
    var getCount =1;
    var limit = 250;
    var total_count = 0;
     var countProducts = '<?= $countProducts; ?>';
    if (countProducts==0){ 
    $("#product-count").hide();
    $('#LoadingMSG').show();
    $('button').prop('disabled', true);
    $.ajax({
      method: "post",
      url: url,
      data: {page:page,getCount:getCount}
      })
      .done(function (msg) 
      {
        var total_page = Math.ceil(msg/limit);
        //alert(page);
        while(page <= total_page){

           $.ajax({
            method: "post",
            url: url,
            async : false,
            data: {page:page}
            })
            .done(function (msg) 
            {
              total_count = parseInt(total_count)+parseInt(msg);
              //console.log(total_count);
            })
            page++;
            }
            $.ajax({
            method: "post",
            url: location.href,
            async : false,
            data: {total_count:total_count}
            })
            .done(function (msg) 
            {
              /*var tempDom = $('<output>').append($.parseHTML(msg));
              var appContainer = $('#main_container_pricing', tempDom);*/

              var newDoc = document.open("text/html", "replace");
                    newDoc.write(msg);
                    newDoc.close();
              //alert("You have "+total_count+" Products in your store(including variant)");
            })
            //console.log(total_count);
            $('button').prop('disabled', false);
            $('#LoadingMSG').hide();
      })
    }
  });
  </script>