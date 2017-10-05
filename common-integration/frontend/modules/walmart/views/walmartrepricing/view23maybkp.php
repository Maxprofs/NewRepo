<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 10/2/17
 * Time: 1:39 PM
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\neweggmarketplace\components\Data;

$query2 = "SELECT * FROM `newegg_product` WHERE `product_id` = '" . $model->product_id . "' AND `merchant_id` =" . MERCHANT_ID;
$neweggproduct = Data::sqlRecords($query2, 'one');
$query = "SELECT `shop_url` from `newegg_shop_detail` where `merchant_id`=" . MERCHANT_ID;
$shopUrl = Data::sqlRecords($query, 'one');
$shop_url = is_array($shopUrl) && isset($shopUrl['shop_url']) ? trim($shopUrl['shop_url']) : "";

?>
<div class="container content-section">
    <!-- Modal -->
    <div class="" tabindex="-1" id="myModal" role="" aria-labelledby="">
        <div class="form new-section">
            <!-- Modal content-->
            <div class="" id='edit-content'>
                <div class="jet-pages-heading">
                    <div id="attributes_notification" style="display: none" class="alert alert-danger">
                        <span>Product attribute not map !!</span>
                    </div>
                    <div class="title-need-help">
                        <h1 class="Jet_Products_style">Edit Product</h1>
                    </div>
                    <div class="product-upload-menu">
                        <div class="v_error_msg" style="display:none;"></div>
                        <div class="v_success_msg alert-success alert" style="display:none;"></div>
                        <a class="btn btn-primary"
                           href="<?= Yii::$app->request->baseUrl ?>/neweggmarketplace/neweggproduct/index">BACK</a>
                        <?php
                        if ($model->jet_product->type == 'variants') {

                            ?>
                            <a class="btn btn-primary"
                               href="<?= Yii::$app->request->baseUrl ?>/neweggmarketplace/variantsvaluemap/index?id=<?= $model->product_id; ?>">Go
                                For Value Mapping</a>
                        <?php } ?>
                        <?= Html::submitButton('Save', ['class' => 'btn btn-primary', 'id' => 'saveedit', 'onclick' => 'saveData()']) ?>
                        <?php if ($shop_url != "") { ?>
                            <a class="btn btn-primary"
                               href="https://<?= $shop_url; ?>/admin/products/<?= trim($model->jet_product->id); ?>"
                               target="_blank" class="" onclick="goToEditShopify();" data-toggle="tooltip"
                               title="To change product information from Shopify.">Edit from Shopify</a>
                        <?php } ?>
                        <button type="button" class="btn btn-primary" onclick="deleteFromShopify()" id="delete">Delete
                        </button>
                    </div>
                </div>
                <div class="clear"></div>

                <div class="">
                    <div class="jet-product-form">

                        <?php $form = ActiveForm::begin([
                            'id' => 'jet_edit_form',
                            'action' => frontend\modules\neweggmarketplace\components\Data::getUrl('neweggproduct/productsave/?id=' . $model->product_id),
                            'method' => 'post',
                        ]); ?>
                        <div class="form-group">
                            <input type="hidden" name="JetProduct[product_id]" value="<?= $model->product_id ?>"
                                   id="productid"/>
                            <?= $form->field($model->jet_product, 'sku')->hiddenInput()->label(false); ?>
                            <? /*<?= $form->field($model, 'title')->hiddenInput()->label(false);?>
                        <?= $form->field($model, 'weight')->hiddenInput()->label(false);?>
                        <?= $form->field($model, 'qty')->hiddenInput()->label(false);?>
                         <?= $form->field($model, 'vendor')->hiddenInput()->label(false);?>
                        <?= $form->field($model, 'description')->hiddenInput()->label(false); ?>
                        <div class="form-group field-jetproduct-price">
                            <input id="jetproduct-price" class="form-control select_error" type="hidden" value="<?= $model->price;?>" name="JetProduct[price]">
                        </div>
                       */ ?>
                            <div class="field-jetproduct">
                                <?php $brand = "";
                                $brand = $model->jet_product->vendor;
                                ?>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Title</th>
                                        <?php
                                        if ($model->jet_product->type != "variants" || count($attributes) == 0) { ?>
                                            <th>Sku</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <?php
                                        } ?>
                                        <th>Brand</th>
                                        <th>SellerPartNumber(SPN)</th>
                                        <th>Weight</th>
                                        <th>Product Type</th>
                                        <th>Description&nbsp;&nbsp;<a style="color:blue !important" id="desc_edit_tag"
                                                                      onclick='editDescription(event)'>edit</a></th>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <? /*= $model->jet_product->title; */ ?>
                                            <div class="public">
                                                <input class="form-control newegg-title" type="text"
                                                       id="newegg_product_title" name="newegg_product_title"
                                                       value="<?php if (isset($neweggproduct['product_title']) && !empty($neweggproduct['product_title'])) {
                                                           echo $neweggproduct['product_title'];
                                                       } else {
                                                           echo $model->jet_product->title;
                                                       } ?>">
                                            </div>
                                        </td>
                                        <?php
                                        if ($model->jet_product->type != "variants" || count($attributes) == 0) { ?>

                                            <td>
                                                <?= $model->jet_product->sku; ?>
                                            </td>
                                            <td>
                                                <? /*= (float)$model->jet_product->price; */ ?>
                                                <div class="pull-left">
                                                    <div class="public">
                                                        <input class="form-control newegg-price" type="text"
                                                               id="newegg_product_price" name="newegg_product_price"
                                                               value="<?php if (isset($neweggproduct['product_price']) && !empty($neweggproduct['product_price'])) {
                                                                   echo $neweggproduct['product_price'];
                                                               } else {
                                                                   echo $model->jet_product->price;
                                                               } ?>">
                                                    </div>
                                                </div>

                                            </td>
                                            <td>
                                                <? /*= $model->jet_product->qty; */ ?>
                                                <div class="public">
                                                    <input class="form-control newegg-inventory" type="text"
                                                           id="newegg_product_inventory" name="newegg_product_inventory"
                                                           value="<?php
                                                           echo $model->jet_product->qty;
                                                           ?>">
                                                </div>
                                            </td>
                                            <?php
                                        } ?>
                                        <td>
                                            <input type="text" maxlength="500"
                                                   value="<?= $model->jet_product->vendor; ?>" name="JetProduct[vendor]"
                                                   class="form-control" id="jetproduct-vendor" readonly="">
                                        </td>
                                        <td>
                                            <input type="text" maxlength="500"
                                                   value="<?= $model->spn; ?>" name="JetProduct[spn]"
                                                   class="form-control" id="" readonly="">
                                        </td>
                                        <td>
                                            <?= (float)$model->jet_product->weight; ?>
                                        </td>
                                        <td>
                                            <?= $model->jet_product->product_type; ?>
                                        </td>
                                        <td>
                                            <div class="more">
                                                <?php
                                                //var_dump($model->jet_product->description);die;
                                                $truncated = "";
                                                $var_string = "";
                                                $var_string = strip_tags($model->long_description);
                                                if (empty($var_string) && $var_string == '') {
                                                    $var_string = strip_tags($model->jet_product->description);
                                                }
                                                $truncated = (strlen($var_string) > 50) ? substr($var_string, 0, 50) . "...<a  onclick='showDescription()' title='More Description' href='#'>more</a>" : $var_string;
                                                ?>
                                                <?= $truncated; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <th>
                                        Bullet Description <b>(use '^^' for seperating your bullet point)</b>
                                    </th>
                                    <?php if ($model->jet_product->type == "variants") { ?>
                                        <th>Manufacturer</th>

                                    <?php } ?>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <?php //echo $model->self_description;die;?>
                                            <textarea maxlength="500" name="JetProduct[short_description]"
                                                      class="form-control"
                                                      id="shortDescriptionField"
                                                      placeholder="First bullet point ^^ Second bullet point"><?= $model->short_description;//$model->short_description;  ?></textarea>
                                        </td>
                                        <?php if ($model->jet_product->type == "variants") { ?>
                                            <td style="">
                                                <label class="general_product_id"
                                                       style="display:none;"><?= trim($model->product_id); ?></label>
                                                <input type="text" maxlength="500"
                                                       value="<?= $model->manufacturer; ?>"
                                                       name="JetProduct[manufacturer]"
                                                       class="form-control" id="">
                                            </td>

                                        <?php } ?>

                                        <!--    <td>
                                            <textarea maxlength="500" name="JetProduct[self_description]"
                                                      class="form-control"
                                                      id="shelfDescriptionField"><?= $model->jet_product->title; ?></textarea>
                                        </td> -->

                                    </tr>
                                    </tbody>
                                </table>
                                <?php
                                if ($model->jet_product->type == "variants" && (count($attributes) > 0 || count($optionalAttrValues) > 0)) {
                                    echo $form->field($model->jet_product, 'ASIN')->hiddenInput()->label(false);
                                    echo $form->field($model->jet_product, 'upc')->hiddenInput()->label(false);
                                    echo $form->field($model->jet_product, 'mpn')->hiddenInput()->label(false);
                                } else {
                                    ?>
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Barcode(UPC/EAN/ISBN)</th>
                                            <th>Mpn</th>
                                            <th>Manufacturer</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td style="width: 40%">
                                                <label class="general_product_id"
                                                       style="display:none;"><?= trim($model->product_id); ?></label>
                                                <input type="text" maxlength="500"
                                                       value="<?= $model->jet_product->upc; ?>" name="JetProduct[upc]"
                                                       class="form-control" id="jetproduct-upc">
                                            </td>
                                            <td style="width: 40%">
                                                <label class="general_product_id"
                                                       style="display:none;"><?= trim($model->product_id); ?></label>
                                                <input type="text" maxlength="500"
                                                       value="<?= $model->jet_product->mpn; ?>" name="JetProduct[mpn]"
                                                       class="form-control" id="jetproduct-mpn" >
                                            </td>
                                            <td style="width: 40%">
                                                <label class="general_product_id"
                                                       style="display:none;"><?= trim($model->product_id); ?></label>
                                                <input type="text" maxlength="500"
                                                       value="<?= $model->manufacturer; ?>"
                                                       name="JetProduct[manufacturer]"
                                                       class="form-control" id="">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <?php
                                } ?>
                            </div>

                            <!-- Code By Himanshu -->
                            <div id="category_tab">
                                <p style="text-align:center;">
                                    <img
                                            src="<?= $loader_img = yii::$app->request->baseUrl . '/frontend/images/batchupload/rule-ajax-loader.gif'; ?>">
                                </p>
                                <p style="text-align:center;">Loading.......</p>
                            </div>
                            <!-- End -->
                            <?php /*echo $this->render('category_tab',[
                            'model' => $model,
                            'category_path'=>$category_path,
                            'attributes'=>$attributes,
                            'optional_attr'=>$optional_attr,
                            'requiredAttrValues'=>$requiredAttrValues,
                            'optionalAttrValues'=>$optionalAttrValues,
                            'common_required_attributes'=>$common_required_attributes
                        ])*/ ?>
                            <?php unset($connection); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                    <!-- <div class="block-callout block-show-callout type-warning block-show-callout type-warning">
                        <div class="note">
                          <h4>
                            <i class="fa fa-exclamation-circle on" title="Warning"></i>
                            <span>BARCODE</span>
                          </h4>
                            <p>Must be one of the following values: GTIN-14 (14 digits), EAN (13 digits), ISBN-10 (10 digits), ISBN-13 (13 digits), UPC (1
                          2 digits).</p>
                          <div class="clear"></div>
                       </div>
                      <div class="note">
                          <h4>
                            <i class="fa fa-exclamation-circle on" title="Warning"></i>
                            <span>ASIN</span>
                          </h4>
                          <p>ASIN must be Alphanumeric with length of 10</p>
                         <div class="clear"></div>
                      </div>
                       <div class="note">
                          <h4 >
                            <i class="fa fa-exclamation-circle on" title="Warning"></i>
                            <span>MPN</span>
                          </h4>
                          <p>Manufacturer Part number provided by the original manufacturer of the merchant SKU.</p>
                        <div class="clear"></div>
                      </div>
                       <div class="note">Every product must have least one combination : Brand + BARCODE or Brand + ASIN or Brand + MPN.</div>
                    </div> -->
                </div>
                <div class="modal-footer">

                </div>
            </div>

            <div class="modal-content" id="product_description"
                 style="padding:20px;display:none"><?= $model->jet_product->description ?>
                <button type="button" style="margin-left: 90%;" class="btn btn-primary" id="descriptionClose"
                        onclick="closedescription()">Close
                </button>
            </div>
        </div>
    </div>
</div>
<div id="wait" style="display:none;position:absolute;top:50%;left:50%;padding:2px;"><img
            src='<?= Yii::$app->request->baseUrl ?>/images/482.gif'/><br>Loading..
</div>
<div id="description-edit">
    <div class="container">
        <div id="description-edit-modal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" id='edit-content'>
                    <div class="modal-header">
                        <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">
                        Edit Description
                        </h4>
                    </div>
                    <div class="modal-body">
                        <textarea cols="100" rows="10" id="textarea-description"><?php
                            if (!empty($model->long_description)) {
                                echo trim($model->long_description);
                            } else {
                                echo trim($model->jet_product->description);
                            }
                            ?></textarea>
                    </div>

                    <div class="modal-footer Attrubute_html" style="padding-right:24px;">
                        <div style="display:none;" class="alert-error alert"></div>
                        <div style="display:none;" class="alert-success alert"></div>

                        <button class="btn btn-primary" onclick="saveDescription(event)" id="save-description"
                                type="submit">Save
                        </button>
                        <button data-dismiss="modal" id="close_desc_modal" class="btn btn-default" type="button"
                                onclick="closedescription()">Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip({html: true});
// $('[data-toggle="tooltip"]').tooltip();
        /* $('.danger').popover({
         html : true,
         content: function() {
         return $('#popover_content_wrapper').html();
         }
         });  */
    });
    function deleteFromShopify() {
        j$('#close-cnfrm-modal').click();
        j$('#LoadingMSG').show();
        var url = "<?= Data::getUrl('neweggproduct/deleteproduct') ?>";
        var productId = $("#productid").val();
        var Retire = 0;
        if ($("#retire-from-walmart").is(":checked")) {
            Retire = 1;
        }

        $.ajax({
            method: "post",
            url: url,
            dataType: "json",
            data: {product_id: productId, _csrf: csrfToken, retire: Retire}
        })
            .done(function (response) {
                j$('#LoadingMSG').hide();
                if (response.success) {
                    alert("Product Deleted Successfully.");
//                    window.location.reload();
                    window.location.href= 'index';
                }
                else if (response.error) {
                    alert(response.message);
                }
                else {
                    alert("something went wrong.");
                }
            });
    }
    function saveData() {
        var postData = j$("#jet_edit_form").serializeArray();
        var formURL = j$("#jet_edit_form").attr("action");
        var type = '<?= $model->jet_product->type ?>';
        var attr_count = '<?= count($attributes) ?>';
        if (type == "variants" && attr_count > 0) {
            if (checkselectedBeforeSubmit()) {
                if (bulletDiscription()) {
                        var ValidateData = {};
                        ValidateData = valueMapping1();
                        if($.isEmptyObject(ValidateData['error'])){
                            $("#wait").css("display", "block");
                            j$('#LoadingMSG').show();
                            j$.ajax(
                            {
                                url: formURL,
                                type: "POST",
                                dataType: 'json',
                                data: postData,
                                _csrf: csrfToken,
                                success: function (data, textStatus, jqXHR) {
                                    j$('#LoadingMSG').hide();
                                    if (data.success) {
                                        j$('.v_success_msg').html('');
                                        j$('.v_success_msg').append(data.success);
                                        j$('.v_error_msg').hide();
                                        j$('.v_success_msg').show();
                                        $("#wait").css("display", "none");


                                    }
                                    else {
                                        j$('.v_error_msg').html('');
                                        j$('.v_error_msg').append(data.error);
                                        j$('.v_success_msg').hide();
                                        j$('.v_error_msg').show();
                                        $("#wait").css("display", "none");


                                    }
                                    //data: return data from server
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    j$('.v_error_msg').html('');
                                    j$('#LoadingMSG').hide();
                                    j$('.v_error_msg').append("something went wrong..");
                                    j$('.v_error_msg').show();
                                    $("#wait").css("display", "none");

                                }
                            });
                        }
                        else{
                            $.each(ValidateData['error'], function(key,val1) {
                                $('#variants_0_'+key+'').parents('tr').css("border", "solid red 2px");
                            });
                            var errorMsg = "Either Value are Not mapped or Repeated Map.";
                            j$('.v_error_msg').html(errorMsg);
                            alert(errorMsg);
                            j$('.v_error_msg').show();
                            return false;
                        }

                }
                else {
                    var errorMsg = "html tag not allowed.";
                    j$('.v_error_msg').html(errorMsg);
                    alert(errorMsg);
                    j$('.v_error_msg').show();

                }
            }
            else {
                return false;
            }

        }

        else {
            if (bulletDiscription()) {
                j$('#LoadingMSG').show();
                j$('.v_success_msg').hide();
                //submit simple form
                $("#wait").css("display", "block");
                j$.ajax(
                    {
                        url: formURL,
                        type: "POST",
                        dataType: 'json',
                        data: postData,
                        success: function (data, textStatus, jqXHR) {
                            j$('#LoadingMSG').hide();
                            if (data.success) {
                                j$('.v_success_msg').html('');
                                j$('.v_success_msg').append(data.success);
                                j$('.v_error_msg').hide();
                                j$('.v_success_msg').show();
                                $("#wait").css("display", "none");

                            }
                            else {
                                j$('.v_error_msg').html('');
                                j$('.v_error_msg').append(data.error);
                                j$('.v_success_msg').hide();
                                j$('.v_error_msg').show();
                                $("#wait").css("display", "none");

                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            j$('.v_error_msg').append('');
                            j$('#LoadingMSG').hide();
                            j$('.v_error_msg').append("something went wrong..");
                            j$('.v_error_msg').show();
                            $("#wait").css("display", "none");
                            //console.log(textStatus);
                        }
                    });
            }
            else {
                var errorMsg = "Bullet description must less than 200 words without html tag.";
                j$('.v_error_msg').html(errorMsg);
                alert(errorMsg);
                j$('.v_error_msg').show();
            }

        }


    }
    function editDescription(event) {
        j$('#edit-content').css('display', 'none');
        $('#description-edit-modal').modal('show');

    }
    function showDescription() {
        j$('#edit-content').css('display', 'none');
        j$('#product_description').css('display', 'block');
    }
    function closedescription() {
        j$('#edit-content').css('display', 'block');
        j$('#product_description').css('display', 'none');
    }
    function saveDescription(event) {
        j$('#LoadingMSG').show();
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
//        var description = $('#textarea-description').html();
        var description = $('#textarea-description').val();
        var url = "<?= Data::getUrl('neweggproduct/save-description') ?>";
        var productId = $("#productid").val();
        $.ajax({
            method: "post",
            url: url,
            dataType: "json",
            data: {product_id: productId, description: description, _csrf: csrfToken}
        })
            .done(function (response) {
                j$('#LoadingMSG').hide();
                if (response.success) {
                    $('#product_description_content').html(description);
                    $('#textarea-description').html(description);

                    var newDesc = description.replace(/(<([^>]+)>)/ig, "");

                    if (newDesc.length > 50) {
                        var moreDesc = newDesc.substr(0, 50);

                        moreDesc = moreDesc + "...<a  onclick='showDescription(event)' title='More Description' href='#'>more</a>";
                        $('.more').html(moreDesc);
                    }


                    j$('#close_desc_modal').click();
                }
                else if (response.error) {
                    alert(response.message);
                }
                else {
                    alert("something went wrong.");
                }
            });
    }
</script>

<?php
$categoryTabUrl = Data::getUrl('neweggproduct/render-category-tab');
?>
<script type="text/javascript">
    var area1, area2;

    function toggleEditor1() {
        if (!area1) {
            area1 = new nicEditor({buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'removeformat', 'indent', 'outdent', 'hr', 'image', 'forecolor', 'bgcolor', 'link', 'unlink', 'fontSize', 'fontFamily', 'fontFormat', 'xhtml']/*fullPanel : true*/}).panelInstance('shortDescriptionField', {hasPanel: true});

            area1.addEvent('blur', function () {
                if (area1) {
                    area1.removeInstance('shortDescriptionField');
                    area1 = null;
                }
            });
        } else {
            area1.removeInstance('shortDescriptionField');
            area1 = null;
        }
    }

    function toggleEditor2() {
        if (!area2) {
            area2 = new nicEditor({buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'removeformat', 'indent', 'outdent', 'hr', 'image', 'forecolor', 'bgcolor', 'link', 'unlink', 'fontSize', 'fontFamily', 'fontFormat', 'xhtml']/*fullPanel : true*/}).panelInstance('shelfDescriptionField', {hasPanel: true});

            area2.addEvent('blur', function () {
                if (area2) {
                    area2.removeInstance('shelfDescriptionField');
                    area2 = null;
                }
            });
        } else {
            area2.removeInstance('shelfDescriptionField');
            area2 = null;
        }
    }
    j$(document).ready(function () {
        //setTimeout(function(){ renderCategoryTab(); }, 5000);
        renderCategoryTab();
    });
    function renderCategoryTab() {

        j$.ajax({
            showLoader: true,
            url: '<?= $categoryTabUrl ?>',
            type: "POST",
            dataType: 'json',
            data: {id: <?= $id ?>}
        }).done(function (data) {
            j$('#category_tab').html(data.html);
        });
    }

</script>

<!-- description setting  only these tag are allowed 'ol','ul','li','br','p','b','i','u','em','strong','sub','sup'-->

<script type="text/javascript">
    function selfDescription() {
        var data = validateDescription(null, '#shelfDescriptionField');
        if (!data) {
            return true;
        }
        else {
            return false;
        }
    }
    function shortDescription() {
        var data = validateDescription('#shortDescriptionField');
        if (data) {
            return true;
        }
        else {
            return false;
        }
    }

    /*   Validation for bullet Discription  */
    function bulletDiscription() {
        var data = validateBulletDiscription('#shortDescriptionField');
        if (data) {
            return true;
        }
        else {
            return false;
        }

    }
    function validateBulletDiscription(id) {
        var description = $(id).val();
        if (description) {
            var length = description.length;
            if (length <= 200) {
                var pattern = /<([\w]+)[^>]*>(.*?)<\/\1>/;
                match = pattern.exec(description);
                if (match) {
                    return false;
                }
                else {
                    return true;
                }
            }
            else {
                return false;
            }

        }
        else {
            return true;
        }


    }


    /* validate value mapping and analize validation mapping depth */
     function valueMapping(){
        var data = $('.sel').length;
        var optionValidation =  false;
        if(data==1){
            optionValidation = valuedepth1();
            //console.log(valuedepth1());
        }
        if(data==2){
           optionValidation = valuedepth2(); 
        }
        if(data==3){
           optionValidation = valuedepth3(); 
        }
        //console.log(optionValidation['errors']['error'].length);
        console.log(optionValidation['errors']);
        if(typeof optionValidation['errors']['error']!='undefined' && optionValidation['errors'].length>0){
            optionValidation = false;
        }
        else{
            optionValidation = true;
        }
        return optionValidation;
     }

     /* start value mapping validation */
     function valuedepth1(check='',depthCall1=false,depthCall2=false){
        var mapArray = new Object();
        var returnArray = new Array();
        //var returnArray['errors'] = new Array();
        var mainArray = new Object();
        mainArray['errors'] = new Object();
        if(check == ''){
           var check = $('.dropdown_0'); 
        }
        var i = 0;
        var emptyCounter = 0;
        $.each(check, function(key,val1) {
            var val = $(this).val();
            if(val!=''){
                if(key==0){
                    mapArray[$(this).val()] = $(this).val();
                    emptyCounter++;
                }
                else{
                    if(mapArray[val]){
                        returnArray[i] = val;
                        i++;
                        emptyCounter++;
                       }else{
                        mapArray[$(this).val()] = $(this).val();
                       }
                }
            }
            else{
                if(emptyCounter==0){
                    emptyCounter = 0;
                    returnArray[i] = val;
                    i++;
                }
                else{
                    emptyCounter=emptyCounter-1;
                    returnArray[i] = val;
                    i++;
                }
            }
        });
        if(emptyCounter==0){
            mainArray['errors']['error']=false;
        }
        if(typeof returnArray!='undefined'){
            returnArray['errors']=returnArray;
            return returnArray;
        }
        else{
            return mainArray;
        }
        
     }

     function valuedepth2(check='',check1='',depthCall0=false,depthCall2=false){
        var mapArray = new Object();
        var returnArray = new Array();
        var returnArray1 = new Array();
        var mainArray = new Object();
        mainArray['errors'] = new Object();
        if(check == ''){
           var check = $('.dropdown_0'); 
        }
         if(check1 == ''){
           var check1 = $('.dropdown_1'); 
        }
        var i = 0;
        var emptyCounter = 0;
        $.each(check, function(key,val1) {
            var val = $(this).val();
            if(val!=''){
                if(key==0){
                    mapArray[$(this).val()] = $(this).val();
                    returnArray[i] = val;
                    i++;
                    emptyCounter++;
                }
                else{
                    emptyCounter++;
                    if(mapArray[val]){
                        returnArray[i] = val;
                        i++;
                    }else{
                        mapArray[$(this).val()] = $(this).val();
                        returnArray[i] = val;
                        i++;
                    }
                }
            }
            else{
                if(emptyCounter==0){
                    emptyCounter = 0;
                    returnArray[i] = val;
                    i++;
                }
                else{
                    emptyCounter=emptyCounter1-1;
                    returnArray[i] = val;
                    i++;
                }
            }
            
        });
        if(check.length==emptyCounter){
            var j = 0;
            var emptyCounter1 = 0;
            $.each(check1, function(key,val1) {
                 var val = $(this).val();
                if(val!=''){
                    if(key==0){
                        mapArray[$(this).val()] = $(this).val();
                        emptyCounter1++;
                        returnArray1[j] = val;
                        j++;
                    }
                    else{
                        emptyCounter1++;
                        if(mapArray[val]){
                            returnArray1[j] = val;
                            j++;
                        }else{
                            mapArray[$(this).val()] = $(this).val();
                            returnArray1[j] = val;
                            j++;
                        }
                    }
                }
                else{
                    if(emptyCounter1==0){
                        emptyCounter1=0;
                        returnArray1[j] = val;
                        j++;
                    }
                    else{
                        emptyCounter1=emptyCounter1-1;
                        returnArray1[j] = val;
                        j++;
                    }
                }
                
            });
            if(check1.length==emptyCounter1){
                for (var k =0; k<returnArray1.length; k++) {
                    var value1 = returnArray[k];
                    var value2 = returnArray1[k];
                    if(typeof mainArray[value1]=='undefined'){
                        mainArray[value1] = new Object();
                    }
                    if(k==0){
                        mainArray[value1][value2]=true;
                    }
                    else{
                        if(mainArray[value1][value2]){
                            mainArray['errors'][k]=true;
                        }
                        else{
                            mainArray[value1][value2]=true;
                        }

                    }
                }
            }
            else{
               return valuedepth1($('.dropdown_0'),false);
            }
          
        }
        else{
            return valuedepth1($('.dropdown_1'),true);
        }
        if(!mainArray['errors']['error']){
            return mainArray['errors']['error'];
        }
        return mainArray;
     }

     function valuedepth3(check='',check1='',check2='',depthCall0=false,depthCall1=false){
        
     }
     /*end value mapping validation*/


    /*    function validateDescription(string=null,id=null) {
     var pattern=/<([\w]+)[^>]*>(.*?)<\/\1>/;
     var pattern1 = "'ol','ul','li','br','p','b','i','u','em','strong','sub','sup'";
     if(id){
     var description= $(id).val();
     if(description){
     var match;
     match = pattern.exec(description);

     if(match){
     if (pattern1.indexOf(match[1]) >= 0){
     var data = validateDescription(match[2],null);
     if(data){
     return true;
     }
     else{
     return false;
     }
     }
     else{
     return false;
     }
     }
     else{
     return true;
     }

     }
     else{
     return true;
     }
     }
     else{
     if(string){
     match = pattern.exec(string);
     if(match){
     if (pattern1.indexOf(match[1]) >= 0){
     if(match[2]){
     var data = validateDescription(match[2],null);
     if(data){
     return true;
     }
     else{
     return false;
     }
     }
     else{
     return true;
     }

     }
     else{
     return false;
     }

     }
     else{
     return true;
     }

     }
     else{
     return true;
     }


     }


     }*/

function valueMapping1(){
    /*check enable mapping */
    var data = $('.sel').length;
    var optionValue = {};
    if(data==1){
        optionValue = mapOneLevel(); 
    }
    if(data==2){
        optionValue = mapTwoLevel(); 
    }
    if(data==3){
        optionValue = mapThreeLevel(); 
    }
    return optionValue;
}
/*check empty and valid with upper and lower dropdown*/

function mapOneLevel(check = ''){
    var mainreturn = {};
    var validator = {};
    mainreturn['error'] = {};
    if(check==''){
        check = $('.dropdown_0');
    }
    if(check.length>0){
        $.each(check, function(key,val1) {
        if($(this).val()){
            if(validator[$(this).val()]){
                mainreturn['error'][key]=false; 
            }
            else{
               validator[$(this).val()]=true;
            }
        }
        else{
            mainreturn['error'][key]=false;
        }
    });
    }
    return mainreturn;
}

/*check empty with selected varainats attribute and validate according to selection */

function mapTwoLevel(check = '',check1 = ''){
    var mainreturn = {};
    var validator = {};
    mainreturn['error'] = {};
    if($('.dropdown_0').length==0){
        mainreturn = mapOneLevel($('.dropdown_1'));
        return mainreturn
    }
    $.each($('.dropdown_1'), function(key,val1) {
        if($(this).val()){
            if($('#variants_0_'+key+'').find('select').val()){
                var index = $(this).val()+'_'+$('#variants_0_'+key+'').find('select').val();
                if(validator[$(this).val()]){
                    mainreturn['error'][key]=false; 
                }
                else{
                   validator[$(this).val()]=true;
                }
            }
            else{
                mainreturn['error'][key]=false;
            }
        }
        else{
            mainreturn['error'][key]=false;
        }
    });
    return mainreturn;
}

function mapThreeLevel(){
    var mainreturn = {};
    var validator = {};
    mainreturn['error'] = {};
    if($('.dropdown_0').length==0){
        mainreturn = mapOneLevel($('.dropdown_1'));
        return mainreturn
    }
    $.each($('.dropdown_1'), function(key,val1) {
        if($(this).val()){
            if($('#variants_1_'+key+'').find('select').val()){
                var index = $(this).val()+'_'+$('#variants_1_'+key+'').find('select').val();
                if(validator[$(this).val()]){
                    mainreturn['error'][key]=false; 
                }
                else{
                   validator[$(this).val()]=true;
                }
            }
            else{
                mainreturn['error'][key]=false;
            }
        }
        else{
            mainreturn['error'][key]=false;
        }
    });
    return mainreturn;
}
</script>




