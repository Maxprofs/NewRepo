<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$merchant_id = MERCHANT_ID;
$urlJetPrice = \yii\helpers\Url::toRoute(['jetproduct/updatejetprice']);
$urlJetInventory = \yii\helpers\Url::toRoute(['jetproduct/updatejetinventory']);
$product_id = $model->id;
if (trim($custom_details['update_description'])!="")
    $description = $custom_details['update_description'];
else
   $description = $model->description;  

if (trim($custom_details['update_title'])!="")
    $title = $custom_details['update_title'];
else
   $title = $model->title; 

if (trim($custom_details['update_price'])!="")
    $price = $custom_details['update_price'];
else
   $price = $model->price;

?>
<div class="container">
      <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
          <div class="modal-content" id='edit-content'>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title" style="text-align: center;">Edit Product Details</h4>
            </div>
            <div class="modal-body">
                <div class="jet-product-form">
                    <?php $form = ActiveForm::begin([
                            'id' => 'jet_edit_form',
                            'action' => Yii::$app->getUrlManager()->getBaseUrl().'/jet/jetproduct/updateajax/?id='.$model->id,
                            'method'=>'post',
                        ]); ?>
                    <div class="form-group">
                        <?= $form->field($model, 'sku')->hiddenInput()->label(false);?>
                        <div class="field-jetproduct">
                            <?php $brand="";
                            $brand=$model->vendor;
                            ?>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th><p>Title</p></th>
                                        <?php
                                        if($model->type!="variants")
                                        { ?>
                                        <th><p>Sku</p></th>
                                        <th><p>Price</p></th>
                                        <th><p>Quantity</p></th>
                                        <?php
                                        }?>
                                        <th><p>Brand</p></th>
                                        <th><p data-toggle="tooltip" title="Weight (lbs)">Weight</p></th>
                                        <th><p>Multipack Quantity</p></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="pencil-parent">           
                                            <input type="text" value="<?= $title;?>" name="JetProduct[update_title]" class="form-control" id="jetproduct-update_title"><span class="glyphicon glyphicon glyphicon-pencil"></span>           
                                        </td>
                                        <?php
                                        if($model->type!="variants")
                                        {
                                         ?>

                                        <td>
                                        <?= $model->sku;?>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="pull-left">
                                                    <div class="public">
                                                        <input class="form-control jet-price" type="text" id="jet_product_price" name="JetProduct[update_price]" value="<?=(float)$price;  ?>">
                                                    </div>
                                                </div>
                                                <div class="pull-left">
                                                    <button class="toggle_editor jet-price-button" type="button" onClick="jetPrice(this);" title="Instantly update price on jet" product-id="<?= $model->id ?>" product-type="simple" option-id="<?= '' ?>" sku="<?= $model->sku ?>" option-price="<?= (float)$price; ?>" >Update
                                                    </button>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="pull-left">
                                                    <div class="public">
                                                        <input class="form-control jet-inventory" type="text"
                                                           id="jet_product_inventory<?= $model->variant_id; ?>" name="JetProduct[qty]"
                                                           value="<?= $model->qty;?>">
                                                    </div>
                                                </div>
                                                <div class="pull-left">
                                                    <button class="toggle_editor jet-inventory-button" type="button"
                                                            onClick="jetInventory(this);" title="Instantly Update Qty on jet" product-id="<?= $model->id ?>" product-type="<?= 'simple' ?>"
                                                            option-id="<?= $model->variant_id; ?>" sku="<?= $model->sku ?>"
                                                            option-inventory="<?= $model->qty; ?>">Update
                                                    </button>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </td>
                                        <?php
                                        }?>
                                        <td>
                                            <?=$model->vendor;?>
                                        </td>
                                        <td>
                                            <?= (float)$model->weight;?>
                                        </td>
                                        <td class="pencil-parent">           
                                            <input type="text" value="<?=$custom_details['pack_qty'];?>" name="JetProduct[pack_qty]" class="form-control" id="jetproduct-pack_qty"><span class="glyphicon glyphicon glyphicon-pencil"></span>           
                                        </td>                                      
                                    </tr>
                                </tbody>
                            </table>
                            <div> 
                                <p>Description</p> 
                                <textarea name="JetProduct[update_description]"  id="description_change" placeholder="Your Product description (Maximum 500 characters)" style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"><?=$description?></textarea>     
                                <script>
                                    var editor = CKEDITOR.replace('JetProduct[update_description]');   
                                </script>                                  
                            </div>
                            
                            <?php
                            if($model->type=="simple")
                            {
                                ?>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th><p>Barcode(UPC/GTIN/ISBN)</p></th>
                                            <th><p data-toggle="tooltip" title="ASIN must be Alphanumeric with length of 10">ASIN</p></th>
                                            <th><p>MPN</p></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width: 40%" class="pencil-parent">
                                                <label class="general_product_id" style="display:none;"><?=trim($model->id);?></label>
                                                <input type="text" maxlength="500"  value="<?=$model->upc;?>" name="JetProduct[upc]" class="form-control" id="jetproduct-upc"><span class="glyphicon glyphicon glyphicon-pencil"></span>
                                            </td>
                                            <td style="width: 30%" class="pencil-parent">
                                                <input id="jetproduct-asin" class="form-control" type="text" maxlength="255" value="<?= $model->ASIN ?>" name="JetProduct[ASIN]"><span class="glyphicon glyphicon glyphicon-pencil"></span>
                                            </td>
    
                                            <td style="width: 30%" class="pencil-parent">
                                                <input id="jetproduct-mpn" class="form-control " type="text" maxlength="255" value="<?= $model->mpn ?>" name="JetProduct[mpn]"><span class="glyphicon glyphicon glyphicon-pencil"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php
                            }?>
                        </div>
                        <div id="category_tab">
                            <p style="text-align:center;">
                                <img src="<?= $loader_img = yii::$app->request->baseUrl.'/frontend/images/batchupload/rule-ajax-loader.gif';?>">
                            </p>
                            <p style="text-align:center;">Loading.......</p>
                        </div>                        
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="modal-footer Attrubute_html">
              <div class="v_error_msg" style="display:none;"></div>
              <div class="v_success_msg alert-success alert" style="display:none;"></div> 
              <button type="button" class="btn btn-primary" id="sync_with_btn" onclick="cnfrmshopSync()"> Sync With Shopify  </button>               
              <?= Html::submitButton('Save', ['class' => 'btn btn-primary','id'=>'saveedit','onclick'=>'saveData()']) ?>              
              <button type="button" id="close_edit" class="btn btn-primary btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>          
        </div>
    </div>
</div>
<?php
$sync_fields = [
    'sku' => 'SKU',
    'title' => 'Title',
    'image' => 'Image',
    'product_type'=>'Product Type',
    'inventory' => 'Inventory',
    'weight' => 'Weight',
    'price' => 'Price',
    'upc' => 'Barcode(ISBN, UPC, GTIN, etc.)',
    'vendor' => 'Vendor(Brand)',
    'description' => 'Description',
    'variant_options' => 'Variant Options',
];
?>
<!-- Modal Sync Form html  -->
<div id="sync" class="modal fade sync-shopify">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <form id="sync-fields-form">
                    <h4 class="sync-store-prod-popup"> Select Fields to Sync with Shopify store</h4>
                    <div class="sync-fields">                        
                            <div class="checkbox_options">
                                <div>
                                    <input type="checkbox" name="selectAll" onclick="selectAllC(this.checked);"/><label>Select All</label>
                                </div>
                                <?php foreach ($sync_fields as $sync_index => $sync_value) : ?>
                                    <div>
                                         <input type="checkbox" class="sync-fields-checkbox"
                                           name="sync-fields[<?= $sync_index ?>]" value="1"/>
                                         <label><?= $sync_value ?></label>
                                    </div>   
                                 <?php endforeach; ?>
                            </div>                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="sync-yes">Sync</button>
                        <button type="button" class="btn" id="sync-cancel" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>             
        </div>
    </div>
</div>

<script type="text/javascript">
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip({html:true});   
    });
    function saveData()
    {
        var postData = $("#jet_edit_form").serializeArray();
        postData[postData.length] = { name:"JetProduct[update_description]",  value:editor.getData()};  
        var formURL = $("#jet_edit_form").attr("action");
        var type='<?= $model->type ?>';
        if(type=="variants")
        {
            if(checkselectedBeforeSubmit())
            {
                $('#LoadingMSG').show();
                $.ajax(
                {
                    url : formURL,
                    type: "POST",
                    dataType: 'json',
                    data : postData,
                    _csrf : csrfToken,
                    success:function(data, textStatus, jqXHR)
                    {
                        $('#LoadingMSG').hide();
                        if(data.success)
                        {
                            $('.v_success_msg').html('');
                            $('.v_success_msg').append(data.success);
                            $('.v_error_msg').hide();
                            $('.v_success_msg').show();
                        }
                        else
                        {
                            $('.v_error_msg').html('');
                            $('.v_error_msg').append(data.error);
                            $('.v_success_msg').hide();
                            $('.v_error_msg').show();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        $('.v_error_msg').html('');
                        $('#LoadingMSG').hide();
                        $('.v_error_msg').append("something went wrong..");
                        $('.v_error_msg').show();
                    }
                });
            }
            else{
                return false;
            }
        }
        else
        {
            $('#LoadingMSG').show();
            $.ajax(
            {
                url : formURL,
                type: "POST",
                dataType: 'json',
                data : postData,
                success:function(data, textStatus, jqXHR)
                {
                    $('#LoadingMSG').hide();
                    if(data.success)
                    {
                        $('.v_success_msg').html('');
                        $('.v_success_msg').append(data.success);
                        $('.v_error_msg').hide();
                        $('.v_success_msg').show();
                    }
                    else
                    {
                        $('.v_error_msg').html('');
                        $('.v_error_msg').append(data.error);
                        $('.v_success_msg').hide();
                        $('.v_error_msg').show();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    $('.v_error_msg').append('');
                    $('#LoadingMSG').hide();
                    $('.v_error_msg').append("something went wrong..");
                    $('.v_error_msg').show();
                }
            });
        }
    }
        
    function jetPrice(element) 
    {
        var price= $('#jet_product_price'+element.getAttribute('option-id')).val();

        var url = '<?= $urlJetPrice; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            dataType: 'json',
            data: {
                sku: element.getAttribute('sku'),
                id: element.getAttribute('product-id'),
                type: element.getAttribute('product-type'),
                merchant_id: merchant_id,
                option_id: element.getAttribute('option-id'),
                price: price,               
                _csrf: csrfToken
            }
        })
        .done(function (msg) 
        {
            $('#LoadingMSG').hide();
            if (msg.success) 
            {
                $('.v_success_msg').html('');
                $('.v_success_msg').append("Price is successfully updated...");
                $('.v_error_msg').hide();
                $('.v_success_msg').show();
            }
            else if (msg.error) 
            {
                $('.v_error_msg').html('');
                $('.v_error_msg').append(msg.message);
                $('.v_success_msg').hide();
                $('.v_error_msg').show();
            }
            else 
            {
                $('.v_error_msg').html('');
                $('.v_error_msg').append("something went wrong.");
                $('.v_success_msg').hide();
                $('.v_error_msg').show();
            }
        });
    }
    function jetInventory(element) 
    {       
        var inventory = $('#jet_product_inventory'+element.getAttribute('option-id')).val();

        var url = '<?= $urlJetInventory; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            dataType: 'json',
            data: {
                sku: element.getAttribute('sku'),
                id: element.getAttribute('product-id'),
                type: element.getAttribute('product-type'),
                merchant_id: merchant_id,
                option_id: element.getAttribute('option-id'),
                qty: inventory,
                _csrf: csrfToken
            }
        })
        .done(function (msg) 
        {
            $('#LoadingMSG').hide();
            if (msg.success) 
            {
                $('.v_success_msg').html('');
                $('.v_success_msg').append("inventory is successfully updated..");
                $('.v_error_msg').hide();
                $('.v_success_msg').show();
            }
            else if (msg.error) 
            {
                $('.v_error_msg').html('');
                $('.v_error_msg').append(msg.message);
                $('.v_success_msg').hide();
                $('.v_error_msg').show();
            }
            else 
            {
                $('.v_error_msg').html('');
                $('.v_error_msg').append("something went wrong.");
                $('.v_success_msg').hide();
                $('.v_error_msg').show();
            }
        });
    }
    function cnfrmshopSync() 
    {
        $('body').attr('data-sync', 'show');
        $('#close_edit').click();   

        $('#sync').modal('show');
        $("#sync").on('shown.bs.modal', function () {
            $('#sync-yes').unbind('click');
            $('#sync-yes').on('click', function () {
                syncWithShopify();
            });
        });

        /*$("#sync").on('hidden.bs.modal', function () {
            $('#edit_walmart_product #myModal').modal('show');
        });*/
    }

    function syncWithShopify() {
        var selectCount = 0;
        $.each($(".sync-fields-checkbox"), function () {
            if ($(this).is(':checked') === true) {
                selectCount++;
            }
        });

        if (selectCount) {
            $('#sync-cancel').click();
            $('#LoadingMSG').show();
            var url = "<?= \yii\helpers\Url::toRoute(['jetscript/syncproductstore']); ?>";
            var productId = '<?=$product_id;?>';

            var fields = $("#sync-fields-form").serialize();
            $.ajax({
                method: "post",
                url: url,
                dataType: "json",
                data: {product_id: productId, _csrf: csrfToken, sync_fields: fields}
            })
                .done(function (response) {
                    $('#LoadingMSG').hide();
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    }
                    else if (response.error) {
                        alert(response.message);
                    }
                    else {
                        alert("something went wrong.");
                    }
                });
        }
        else {
            alert("Please select fields to sync.");
        }
        $("#sync-fields-form")[0].reset();
    }

    //Select All on Sync With shopify 

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
<!-- end here -->