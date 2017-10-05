<?php
use frontend\modules\jet\models\JetConfiguration;
use common\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\jet\components\Data;

$this->title = 'Settings';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = Yii::$app->user->identity->id;

$sync_fields = [
    'sku' => 'SKU',
    'title' => 'Title',
    'image' => 'Image',
    'product_type'=>'Product Type',
    'weight' => 'Weight',
    'price' => 'Price',
    'upc' => 'Barcode(ISBN, UPC, GTIN, etc.)',
    'vendor' => 'Vendor(Brand)',
    'description' => 'Description',
    'variant_options' => 'Variant Options',
];
?>
<script>
</script>
<div class="jet-configuration-index content-section">
    <div class="jet_configuration form new-section">
        <?php $form = ActiveForm::begin([
            'id' => 'jet_config',
            'action' => \yii\helpers\Url::toRoute(['jetconfiguration/index']),
            'method' => 'post',
            'options' => ['name' => 'jet_configupdate'],
        ]) ?>       
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu">
                <input type="submit" name="submit" value="save" class="btn btn-primary">
            </div>
            <div class="clear"></div>
        </div>
        <div class="entry-edit-head">
            <h4 class="fieldset-legend">Api Configuration</h4>
        </div>
        <div class="fieldset enable_api" id="api-section">
            <table class="table table-striped table-bordered" cellspacing="0">
                <tbody>                                                  
                <tr>
                    <td class="value_label">
                        <span>Api user</span>
                    </td>
                    <td class="value form-group field-jetconfiguration-api_user">                        
                        <input id="jetconfiguration-api_user" maxlength="255" type="text" name="api_user" value="<?= isset($model['api_user'])? $model['api_user'] : ''; ?>" class="form-control" readonly="">                 
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span> Api password</span>
                    </td>
                    <td class="value form-group field-jetconfiguration-api_password required">
                        <input id="jetconfiguration-api_password" maxlength="255" type="text" name="api_password" value="<?= isset($model['api_password'])? $model['api_password'] : ''; ?>" class="form-control" readonly="">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span> Merchant Id</span>
                    </td>
                    <td class="value form-group field-jetconfiguration-api_password required">
                        <input id="jetconfiguration-merchant"  type="text" name="merchant" value="<?= isset($model['merchant'])? $model['merchant'] : ''; ?>" class="form-control" readonly="">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>Api Fulfillment Node Id</span>
                    </td>
                    <td class="value form-group field-jetconfiguration-fullfilment_node_id required">
                        <input type="text" name="fullfilment_node_id" value="<?= isset($model['fullfilment_node_id'])? $model['fullfilment_node_id'] : ''; ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>Merchant Email</span>
                    </td>
                    <td class="value form-group field-jetconfiguration-merchant_email required">                        
                        <input type="text" name="merchant_email" value="<?= isset($model['email']['email'])? $model['email']['email'] : ''; ?>" class="form-control">                           
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="entry-edit-head">
            <h4 class="fieldset-legend">Return Configuration</h4>
        </div>
        <div class="fieldset enable_api" id="return-location-section">
            <table class="table table-striped table-bordered">
                <tbody>
                <tr>
                    <td class="value_label">
                        <span>First Address</span>
                    </td>
                    <td class="value">
                        <input type="text" name="config_data[first_address]" value="<?= isset($clientData['first_address'])? $clientData['first_address'] : ''; ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>Second Address</span>
                    </td>
                    <td class="value">
                        <input type="text" name="config_data[second_address]" value="<?= isset($clientData['second_address'])? $clientData['second_address'] : ''; ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>City</span>
                    </td>
                    <td class="value">
                        <input type="text" name="config_data[city]" value="<?= isset($clientData['city'])? $clientData['city'] : ''; ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>State</span>
                    </td>
                    <td class="value">
                        <input type="text" name="config_data[state]" value="<?= isset($clientData['state'])? $clientData['state'] : ''; ?>" class="form-control">
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>Zipcode</span>
                    </td>
                    <td class="value">
                        <input id="zipcode" type="text" name="config_data[zipcode]" value="<?= isset($clientData['zipcode'])? $clientData['zipcode'] : ''; ?>" class="form-control" />
                    </td>
                </tr>
                <tr>
                    <td class="value_label">
                        <span>Number of days to return</span>
                        <span class="text-validator">The number of days the customer has to return the shipment item</span>
                    </td>
                    <td class="value">
                        <input type="text" name="config_data[day_to_return]" value="<?= isset($clientData['day_to_return'])? $clientData['day_to_return'] : ''; ?>" class="form-control days-to-return">
                        <span class="text-validator">Number of days to return cannot be less than 7</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="entry-edit-head">
            <h4 class="fieldset-legend">Product Configuration</h4>
        </div>
        <div class="fieldset enable_api">
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <td class="value_label">
                            <span>Jet Repricer</span>

                            <span class="text-validator">Choose "Yes" to allow for product repricing. It works on for AVAILABLE FOR PURCHASE products. You can adjust your poduct price according to competitor product price to increase the chance to win the orders.
                            </span>
                        </td>
                        <td class="value" id="dynamic_repricing">
                            <span>
                                 <select name="config_data[dynamic_repricing]" class="form-control dynamic_repricing">
                                    <option
                                        value="No" <?php if (isset($clientData['dynamic_repricing']) && $clientData['dynamic_repricing'] == "No") {
                                        echo "selected=selected";
                                    } ?>>No</option>
                                    <option value="Yes" <?php if (isset($clientData['dynamic_repricing']) && $clientData['dynamic_repricing'] == "Yes") {
                                        echo "selected=selected";
                                    } ?>>Yes</option>
                                </select>
                            </span>
                        </td>
                    </tr>                
                    <?php 
                        if (isset($clientData['set_price_amount'])){
                            $priceData = explode('-', $clientData['set_price_amount']);
                        }
                    ?>
                    <tr>
                        <td class="value_label">
                            <span>Update Price</span>
                            <span class="text-validator">To manipulate the prices of product (all at once) for selling on jet.</span>
                        </td>
                        <td class="value" id="custom-pricing-section">
                            <input onchange="radioChange(this)" type="radio" name="config_data[fixedPriceUpdate]"
                                   value="fixedAmount" <?php if (isset($clientData['set_price_amount']) && $priceData[0] == 'fixedAmount') {
                                echo "checked";
                            } ?> > Fixed amount &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onchange="radioChange(this)" type="radio" name="config_data[fixedPriceUpdate]"
                                   value="percentageAmount" <?php if (isset($clientData['set_price_amount']) && $priceData[0] == 'percentageAmount') {
                                echo "checked";
                            } ?> > %age amount <br>
                            <?php
                                if (isset($clientData['set_price_amount'])) 
                                {
                                    $priceData = explode('-', $clientData['set_price_amount']);
                                    ?>
                                    <input type="text" class="form-control setPriceField" name="config_data[setPrice]"
                                           value="<?= isset($priceData[1])? $priceData[1] : ''; ?>">
                                    <?php
                                }else{?>
                                    <input style="display:none" type="text" class="form-control setPriceField" name="config_data[setPrice]" value="">
                                <?php
                                } 
                            ?>
                                <span class="text-validator">Choose option and enter value (in numeric) to increase price EITHER by fixed amount OR %age</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="value_label">
                            <span>Threshold Product Inventory</span>
                            <span class="text-validator">Enter the MINIMUM stock of products (in numeric) below which inventory status is sent as Out-Of-Stock to jet.</span>
                        </td>
                        <td class="value" id="threshold_inventory_limit">
                            <input type="text" id="inventory" name="config_data[inventory]" value="<?= isset($clientData['inventory'])? $clientData['inventory'] : ''; ?>" class="form-control" onkeydown="checkInventory()" > <label id="inventoryerror" ></label>
                        </td>
                    </tr>

                    <tr>
                        <td class="value_label">
                            <span>Product Import Option</span>
                        </td>
                        <td>
                            <?php 
                                //var_dump($clientData);die("xcv");
                            ?>
                            <span>
                                <select class="form-control" name="config_data[import_status]">
                                    <option <?php if(isset($clientData['import_status']) && $clientData['import_status']=="any"){echo "selected=selected";}?> value="any">All Products</option>
                                    <option <?php if(isset($clientData['import_status']) && $clientData['import_status']=="published"){echo "selected=selected";}?> value="published">Published Product</option>
                                </select>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="value_label">
                            <span>Product Sync</span>
                        </td>
                        <td>
                            <span>
                                <select name="config_data[sync_product_enable]" class="form-control"
                                        onchange="productsync(this)">
                                    <option value="enable" <?php if (isset($clientData['sync_product_enable']) && $clientData['sync_product_enable'] == 'enable') {
                                        echo "selected=selected";
                                    } ?>>Enable</option>
                                    <option value="disable" <?php if (isset($clientData['sync_product_enable']) && $clientData['sync_product_enable'] == 'disable') {
                                        echo "selected=selected";
                                    } ?>>Disable</option>
                                </select>
                            </span>

                            <div id="sync_product_options">
                                <div class="all_checkbox_options">
                                    <input type="checkbox" class="all-sync-fields-checkbox"
                                           id="all-sync-fields-checkbox" name="" value="1"/>
                                    <label>Select All</label>
                                </div>
                                <div class="sync-fields">
                                    <?php foreach ($sync_fields as $sync_index => $sync_value) : ?>
                                        <div class="checkbox_options">
                                            <input type="checkbox" class="sync-fields-checkbox"
                                                   name="sync-fields[<?= $sync_index ?>]" id="<?= $sync_index?>" value="1"/>
                                            <label><?= $sync_value ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            </div>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>       
        <div class="entry-edit-head">
            <h4 class="fieldset-legend">Order Configuration</h4>
        </div>

        <div class="fieldset enable_api" id="cancel-order-section">
            <table class="table table-striped table-bordered" cellspacing="0">
                <tbody>
                    <tr>
                        <td class="value_label">
                            <span>Auto Cancel Orders</span>

                            <span class="text-validator">Want to automatically cancel orders when product inventory is out of stock or sku is not available or order is not acknowledged within 2 hours</span>
                        </td>
                        <td class="value" id="cancel_order">
                            <span>
                                 <select name="config_data[cancel_order]" class="form-control cancel_order">
                                    <option
                                        value="No" <?php if (isset($clientData['cancel_order']) && $clientData['cancel_order'] == "No") {
                                        echo "selected=selected";
                                    } ?>>No</option>
                                    <option
                                        value="Yes" <?php if (isset($clientData['cancel_order']) && $clientData['cancel_order'] == "Yes") {
                                        echo "selected=selected";
                                    } ?>>Yes</option>
                                </select>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="ced-entry-heading-wrapper">
            <div class="entry-edit-head">
                <h4 class="fieldset-legend">Email Subscription Settings</h4>
            </div>
            <div class="fieldset enable_api" id="email-subscription-section">   
                <table class="table table-striped table-bordered" cellspacing="0">
                    <tbody>
                    <?php foreach($email as $key=>$value): ?>
                        <?php if($value['show_on_admin_setting'] == 1):?>
                        <tr>
                            <td class="value_label">
                                <label><span><?php echo $value['custom_title']; ?></span></label>
                            </td>
                            <?php if(isset($clientData['email/'.$value['template_title']]) && !empty($clientData['email/'.$value['template_title']])):?>

                            <td class="value" id="custom_image_on_jet">
                                <input type="checkbox" id="email/<?php echo $value['template_title'];?>" name="config_data[email/<?php echo $value['template_title']; ?>]" value="<?= $clientData['email/'.$value['template_title']]; ?>" checked>
                            </td>
                            <? else: ?>
                            <td>
                                <input type="checkbox" id="email/<?php echo $value['template_title']; ?>" name="config_data[email/<?php echo $value['template_title']; ?>]" value="1">
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>        
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<style>
.jet-configuration-index .value_label {
    width: 50%;
}
.jet-configuration-index .table-striped select,.jet-configuration-index .form-control{
    width: 100%;
    display: inline-block;
    padding-left: 10px;
}
    .jet-configuration-index .value {
    border: medium none !important;
    display: inline-block;
    width: 100%;
}
#custom_price_csv span, #custom_title_csv span{
    width: 85%;
    display: inline-block;
}
   .jet-configuration-index .help_jet{
        display: inline-block;
   }
   .help_jet {
    width: 50px!important;
}/*
</style>
<script type="text/javascript">
$('.days-to-return').change(function(){
    var valid = false;
    valid = isNaN($(this).val());
    if(valid)
    {
        document.querySelector('.days-to-return').value="";
        alert("Please enter only numeric values");
        return false;
    }           
});
    function defaultChange(node) {
        var val = $(node).val();
        if (val == "yes") {
            $(node).next('input').show();
            $(node).next('input').prop('disabled', false);
        } else {
            $(node).next('input').hide();
            $(node).next('input').prop('disabled', true);
        }
    }
    function checkInventory() {
//        alert("You pressed a key inside the input field");
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
    function radioChange(node) {
        var val = $(node).val();
        if ((val == "fixedAmount") || (val == "percentageAmount")) {
//      $(node).next('input').show();
            $(".setPriceField").css("display", "block");
            $(".setPriceField").prop('disabled', false);
        } else {
            $(".setPriceField").css("display", "none");
            $(".setPriceField").prop('disabled', true);
        }
    }
    
    $( "#zipcode" ).blur(function() 
    {
        var zip = document.getElementById("zipcode").value;
        if (isNaN(zip) || ((zip.length)!=5) ) 
        {
            alert("Please enter valid zip code (numeric and of 5 digit)");
        }
    });   

    function productsync(node) 
    {
        var val = $(node).val();
        if (val == 'enable') 
        {
            $('#sync_product_options').show();
            $('#all-sync-fields-checkbox').prop('checked', true);
            $('.sync-fields-checkbox').prop('checked', true);
        }
        else 
        {
            $('#sync_product_options').hide();
        }
    }
    var prod_sync = '<?php if(isset($clientData['sync_product_enable']) && $clientData['sync_product_enable'] == 'enable')
    {
        echo "enable";
    } 
    elseif(isset($clientData['sync_product_enable']) && $clientData['sync_product_enable']=='disable')
    {
        echo 'disable';
    }
    else
    {
        echo '';
    }?>';
    var flag = true;
    if(prod_sync=='')
    {
        $('#all-sync-fields-checkbox').prop('checked', true);
        $('.sync-fields-checkbox').prop('checked', true);
        flag=false;
    }
    if(flag)
    {
        if(prod_sync=='enable')
        {
            var sync_field = '<?= isset($clientData['sync-fields']) ? $clientData['sync-fields'] : ''?>';
            if(sync_field)
            {
                var fields = JSON.parse(sync_field);
                var counter = 0 ;
                $.each(fields, function (index,value){
                    counter++;
                    $('#'+index+'').prop('checked', true);
                });
                if(counter=='11'){
                    $('#all-sync-fields-checkbox').prop('checked', true);
                }
            }
            
        }else{
            $('#sync_product_options').hide();
        }
    } 
    $('#all-sync-fields-checkbox').click(function() {
        if (!$(this).is(':checked')) {
            $('.sync-fields-checkbox').prop('checked', false);
        }
        else{
            $('.sync-fields-checkbox').prop('checked', true);
        }
    });
    $('.checkbox_options').click(function() {
        $("input:checkbox[class=sync-fields-checkbox]").each(function () {
            if (!$(this).is(':checked')) {
                $('#all-sync-fields-checkbox').prop('checked', false);
                return false;
            }
            else{
                $('#all-sync-fields-checkbox').prop('checked', true);
            }
        });

    });
</script>

<?php $get = Yii::$app->request->get();
  if(isset($get['tour'])) : 
?>
  <script type="text/javascript">    
    $(document).ready(function()
    {
        var configQuicktour = introJs().setOptions({
                doneLabel: 'Finish',
                showStepNumbers: false,
                exitOnOverlayClick: false,
                steps: [
                  {
                    element: '#api-section',
                    intro: 'Update the Jet API details.',
                    position: 'bottom'
                  },
                  {
                    element: '#return-location-section',
                    intro: 'Update Jet Return Configuration.',
                    position: 'bottom'
                  },
                  {
                    element: '#dynamic_repricing',
                    intro: 'Set "yes" to enable Jet repricing',
                    position: 'bottom'
                  },
                  {
                    element: '#custom-pricing-section',
                    intro: 'To manipulate the prices of product (all at once) for selling on jet.',
                    position: 'bottom'
                  },
                  {
                    element: '#threshold_inventory_limit',
                    intro: 'Enter the MINIMUM stock of products (in numeric) below which inventory status is sent as Out-Of-Stock to jet.',
                    position: 'bottom'
                  },
                  {
                    element: '#cancel-order-section',
                    intro: 'Set YES to enable auto-cancel orders if sku/inventory not available in store',
                    position: 'bottom'
                  },
                  {
                    element: '#email-subscription-section',
                    intro: 'Update Email Subscription Setting from here. Check the corresponding Checkbox to receive Mails and Uncheck to Not Receive Mails.',
                    position: 'bottom'
                  }
                ]
            });

            configQuicktour.start().oncomplete(function() {
              window.location.href = '<?= Data::getUrl("site/index") ?>';
          });
      });    

  </script>
<?php endif; ?>
