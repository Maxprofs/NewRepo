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
    background-position: 0px 8px;
    background-repeat: no-repeat;
    background-size: 20px auto;
    font-size: 15px;
    line-height: 30px;
    list-style: outside none none;
    margin-bottom: 5px;
    padding-left: 30px;
    text-align: left;
  }
</style>
<div class="containers">
  <div class="product-description-wrapp"> 
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
      <div class="shopify-image-wrap">
        <img class="jet-logo-big img-responsive" src="<?php echo Yii::$app->request->baseUrl?>/images/jet_shopify_large.jpg" width="500" height="400">
      </div>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
      <div class="deascription-wrapp">
        <h4 class="product-name">JET SHOPIFY INTEGRATION</h4>
          <div class="jet-product-form">
            <form "id" ="jet_registration_form" method="post" action="<?=Yii::$app->getUrlManager()->getBaseUrl()?>/site/register">                                                                
              <div class="form-group">
                    <div class="field-jetproduct">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td class="value_label" width="33%">
                                        <span>Shipping Carrier provider</span>
                                    </td>
                                    <td class="value form-group " width="100%">
                                      <input type="checkbox" name="carrier-provider" value="FBA">FBA <br>
                                      <input type="checkbox" name="carrier-provider" value="shipstation">Shipstation <br>
                                      <input type="checkbox" name="carrier-provider" value="shipwork">Shipwork                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td class="value_label" width="33%">
                                        <span>Maximum Product Count</span>
                                    </td>
                                    <td class="value form-group " width="100%">
                                        <input type="radio" name="product-count" value="1000"> 1000 
                                        <input type="radio" name="product-count" value="5000"> 5000 
                                        <input type="radio" name="product-count" value="10000"> 10000
                                        <input type="radio" name="product-count" value="50000"> 50000
                                    </td>
                                </tr> 
                                <tr>
                                  <td class="value_label" width="33%">
                                    <span>Auto cancel jet orders</span>
                                  </td>
                                  <td class="value form-group " width="100%">
                                    <input type="radio" name="cancel_order" value="yes"> Yes 
                                    <input type="radio" name="cancel_order" value="no"> No                                       
                                  </td>
                                </tr> 
                                <tr>
                                  <td class="value_label" width="33%">
                                    <span>Threshhold Product Inventory</span>
                                  </td>
                                  <td class="value form-group " width="100%">
                                    <input type="text" onkeydown="checkInventory()" placeholder="Minimum Inventory" class="form-control" value="" name="inventory" id="inventory"> 
                                    <label id="inventoryerror" style="display: none;">Please enter number</label>
                                  </td>
                                </tr>  
                                <tr>
                                  <td class="value_label">
                                    <span>Custom Pricing On Jet</span>
                                    <span class="text-validator">To manipulate the prices of product (all at once) for selling on jet.</span>
                                  </td>
                                  <td class="value">
                                    <input type="radio" value="fixedAmount" name="fixedPriceUpdate" onchange="radioChange(this)"> Fixed amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="radio" value="percentageAmount" name="fixedPriceUpdate" onchange="radioChange(this)"> %age amount <br>
                                    <input type="text" value="" name="setPrice" class="form-control setPriceField" style="display: none;">
                                        <span class="text-validator" style="display: none;">Choose option and enter value (in numeric) to increase price EITHER by fixed amount OR %age</span>
                                  </td>
                                </tr>  
                                <tr>
                                    <td class="value_label" width="33%">
                                        <span>Email Subscription Setting</span>
                                    </td>
                                    <td class="value form-group " width="100%">
                                      <input type="checkbox" name="carrier-provider" value="email/order-error">Issue in order Import <br>
                                      <input type="checkbox" name="carrier-provider" value="email/order">New order received <br>
                                      <input type="checkbox" name="carrier-provider" value="email/productStockMail"> Product out of stock notification
                                    </td>
                                </tr> 
                                <tr>
                                    <td class="value_label" width="33%">
                                        <span>Where did you here about us</span>
                                    </td>
                                    <td class="value form-group " width="100%">
                                      <input type="radio" value="facebook" name="referrar" onchange="referalChange(this)"> Facebook
                                      <input type="radio" value="appStore" name="referrar" onchange="referalChange(this)"> Shopify App Store
                                      <input type="radio" value="google" name="referrar" onchange="referalChange(this)"> Google
                                      <input type="radio" value="other" name="referrar" onchange="referalChange(this)"> Other <br>
                                      <textarea name="referrar_val" class="form-control setReferrarField" style="display: none;">
                                        
                                      </textarea>
                                    </td>
                                </tr>                               
                            </tbody>
                        </table>                 
                    </div>                                               
              </div> 
            </form>                   
        </div>        
      </div>
    </div>
    <div class="clear"></div>
  </div>
</div>
<script type="text/javascript">
  function checkInventory() 
  {
    var inventory = $("input#inventory").val();
    if (isNaN(inventory))
    {
      var text = 'Please enter number';
      $('#inventoryerror').text(text).show();
      return false;
    }else{
      $('#inventoryerror').hide();
    }
  }
  function radioChange(node) 
  {
    var val = $(node).val();
    if ((val == "fixedAmount") || (val == "percentageAmount")) {
        $(".setPriceField").css("display", "block");
        $(".text-validator").css("display", "block");
        $(".setPriceField").prop('disabled', false);
    } else {
        $(".setPriceField").css("display", "none");
        $(".text-validator").css("display", "none");
        $(".setPriceField").prop('disabled', true);
    }
  }

  function referalChange(node) 
  {
    var val = $(node).val();
    if ((val == "facebook") || (val == "google") || (val == "appStore") || (val == "other") ) {
      $(".setReferrarField").css("display", "block");
      $(".setReferrarField").prop('disabled', false);
    } else {
      $(".setReferrarField").css("display", "none");
      $(".setReferrarField").prop('disabled', true);
    }
  }
</script>