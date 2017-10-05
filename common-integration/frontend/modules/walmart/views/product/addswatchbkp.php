<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartPromoStatus;

$tax_code = Data::GetTaxCode($model, MERCHANT_ID);
$controllerName = 'product';
$shop_url = Yii::$app->user->identity->username;

if (!$tax_code) {
    $tax_code = "";
}

if (is_null($model->self_description) || $model->self_description == '')
    $model->self_description = $model->jet_product->title;
//Get shopify Options
    $shopifyOptions = json_decode(stripslashes($model->jet_product->attr_ids), true) ?: [];

    //Mapping of Shopify Option with walmart attribute
    $shopifyOptionsMapping = [];
    if ($model->walmart_attributes != "") {
        $shopifyOptionsMapping = json_decode($model->walmart_attributes, true);
    }
?>
<div class="container">
    <!-- Modal -->
    <div class="modal fade" tabindex="-1" id="myModal" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content" id='edit-content'>
                <div class="modal-header">
                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS
                    ";"><?= $model->jet_product->title ?></h4>
                </div>
                <div class="modal-body">
                    <div class="jet-product-form">
                        <?php $form = ActiveForm::begin([
                            'id' => 'fileupload',
                          'action' => frontend\modules\walmart\components\Data::getUrl($controllerName . '/saveswatch'),
                            'method' => 'post',
                            'options' => ['class'=>'dropzone needsclick dz-clickable','enctype'=>'multipart/form-data'],
                            
                        ]); ?>
                        <div class="form-group">
                            <input type="hidden" name="WalmartProduct[product_id]" value="<?= $model->product_id ?>"
                                   id="productid"/>
                        <table class="table table-striped table-bordered">
                            <tr>
                                <td>
                                    <div><b>
                                         <center>Sku</center>
                                        </b>
                                    </div>
                                </td>
                                <td>
                                    <div><b>
                                         <center>Shopify Option</center>
                                        </b>
                                    </div>
                                </td>
                                <td>
                                    <div><b>
                                         <center>Shopify Option Value</center>
                                        </b>
                                    </div>
                                </td>
                                 <td>
                                    <div><b>
                                         <center>SwatchImageUrl/Browse</center>
                                        </b>
                                    </div>
                                </td>
                            </tr>
                                   <?php 
                                   $query = "SELECT option_sku,jetvar.option_id,variant_option1,variant_option2,variant_option3,walvar.new_variant_option_1,walvar.new_variant_option_2,walvar.new_variant_option_3,jetvar.product_id as product_id FROM `walmart_product_variants` AS walvar INNER JOIN `jet_product_variants` AS jetvar ON walvar.option_id = jetvar.option_id WHERE jetvar.product_id='" . $model->product_id . "' order by jetvar.option_sku='" . $model->jet_product->sku . "' desc, jetvar.option_id asc";
                                    $productOptions = Data::sqlRecords($query, "all", "select");
                                    $variant_opt_skus = [];
                            if (is_array($productOptions) && count($productOptions) > 0) {
                                foreach ($productOptions as $value) {
                                    if (trim($value['option_sku']) == "" || in_array(trim($value['option_sku']), $variant_opt_skus)) {
                                        continue;
                                    }
                                    $variant_opt_skus[] = trim($value['option_sku']);
                                    ?>
                                    <tr>
                                      <td>
                                            <input type="hidden"
                                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][variant_id]"
                                                       value="<?= $value['option_id'] ?>">
                                            <div>
                                               <center><?= $value['option_sku'] ?></center> 
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $shopifyArr = array();
                                            foreach ($shopifyOptions as $key => $val) {
                                                ?>
                                                        <input type="hidden"
                                                               name="WalmartProduct[shopify_options_mapping][<?= $key ?>][shopify_option_name]"
                                                               value="<?= $val ?>"/>
                                                        <center><?= $val ?></center>
                                                
                                                <?php
                                                $shopifyArr[] = $key;
                                            }
                                            ?>
                                        </td>
                                        <td>
                                        <?php
                                        $url_box_count = 0;
                                        if ($value['variant_option1']) {
                                            $url_box_count++;
                                            ?>
                                            
                                                <input type="hidden" class="form-control" readonly=""
                                                       value="<?= $value['variant_option1'] ?>"
                                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][option_values]">
                                                <center><?=  $value['variant_option1'] ?></center>
                                            <?php
                                        }
                                        if ($value['variant_option2']) {
                                            $url_box_count++;
                                            ?>
                                                <input type="hidden" class="form-control" readonly=""
                                                       value="<?= $value['variant_option2'] ?>"
                                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][option_values]">
                                                <center><?= $value['variant_option2'] ?></center>
                                            <?php
                                        }
                                        if ($value['variant_option3']) {
                                            $url_box_count++;
                                            ?>
                                                <input type="hidden" class="form-control" readonly=""
                                                       value="<?= $value['variant_option3'] ?>"
                                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][option_values]">
                                                <center><?= $value['variant_option3'] ?></center>
                                            
                                        <?php } ?>
                                        </td>
                                        <td>
                                        <?php 
                                        for ($i=0; $i < $url_box_count ; $i++) { ?>
                                                    <!-- The fileinput-button span is used to style the file input field as button -->
                                        <span class="btn btn-success fileinput-button">
                                                        <i class="glyphicon glyphicon-plus"></i>
                                                        <span>Add files...</span>
                                                        <!-- The file input field used as target for the file upload widget -->
                                                        <input id="fileupload<?=$value['option_sku']?><?= $i ?>" type="file" name="files[]" multiple>
                                                    </span>
                                                    <br>
                                                    <!-- The global progress bar -->
                                                    <div id="progress<?=$value['option_sku']?><?= $i ?>" class="progress">
                                                        <div class="progress-bar progress-bar-success"></div>
                                                    </div>
                                                    <!-- The container for the uploaded files -->
                                                    <div id="files<?=$value['option_sku']?><?= $i ?>" class="files"></div>
                                                    <br>
                                                    <input type="hidden" class="form-control"
                                                       value="<?= $value['variant_option3'] ?>"
                                                       name="WalmartProduct[product_variants][<?= $value['option_id'] ?>][url]">
                                                       
                                                    <script>
                                                    /*jslint unparam: true, regexp: true */
                                                    /*global window, $ */

                                                        $(function () {
                                                        'use strict';
                                                        // Change this to the location of your server-side upload handler:

                                                        var url = window.location.hostname === 'blueimp.github.io' ?
                                                                    '//jquery-file-upload.appspot.com/' : '<?= frontend\modules\walmart\components\Data::getUrl('fileupload/index')?>',
                                                            uploadButton = $('<button class="upload" style="display:none;"/>')
                                                                .addClass('btn btn-primary')
                                                                .prop('disabled', true)
                                                                .text('Processing...')
                                                                .on('click', function () {
                                                                    var $this = $(this),
                                                                        data = $this.data();
                                                                    $this
                                                                        .off('click')
                                                                        .text('Abort')
                                                                        .on('click', function () {
                                                                            $this.remove();
                                                                            data.abort();
                                                                        });
                                                                    data.submit().always(function () {
                                                                        $this.remove();
                                                                    });
                                                                });
                                                        
                                                        $('#fileupload<?=$value['option_sku']?><?= $i ?>').fileupload({
                                                            url: url,
                                                            dataType: 'json',
                                                            autoUpload: false,
                                                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                                                            maxFileSize: 999000,
                                                            // Enable image resizing, except for Android and Opera,
                                                            // which actually support image resizing, but fail to
                                                            // send Blob objects via XHR requests:
                                                            disableImageResize: /Android(?!.*Chrome)|Opera/
                                                                .test(window.navigator.userAgent),
                                                            previewMaxWidth: 100,
                                                            previewMaxHeight: 100,
                                                            previewCrop: true
                                                        }).on('fileuploadadd', function (e, data) {

                                                            data.context = $('<div/>').appendTo('#files<?=$value['option_sku']?><?= $i ?>');
                                                            $.each(data.files, function (index, file) {
                                                                var node = $('<p/>')
                                                                        .append($('<span/>').text(file.name));
                                                                if (!index) {
                                                                    node
                                                                        .append('<br>')
                                                                        .append(uploadButton.clone(true).data(data));
                                                                }

                                                                node.appendTo(data.context);
                                                                $(node).find('.upload').click();
                                                            });
                                                        }).on('fileuploadprocessalways', function (e, data) {
                                                            var index = data.index,
                                                                file = data.files[index],
                                                                node = $(data.context.children()[index]);
                                                            if (file.preview) {
                                                                node
                                                                    .prepend('<br>')
                                                                    .prepend(file.preview);
                                                            }
                                                            if (file.error) {
                                                                node
                                                                    .append('<br>')
                                                                    .append($('<span class="text-danger"/>').text(file.error));
                                                            }
                                                            if (index + 1 === data.files.length) {
                                                                data.context.find('button')
                                                                    .text('Upload')
                                                                    .prop('disabled', !!data.files.error);
                                                            }
                                                        }).on('fileuploadprogressall', function (e, data) {
                                                            var progress = parseInt(data.loaded / data.total * 100, 10);
                                                            $('#progress<?=$value['option_sku']?><?= $i ?> .progress-bar').css(
                                                                'width',
                                                                progress + '%'
                                                            );
                                                        }).on('fileuploaddone', function (e, data) {
                                                            $.each(data.result.files, function (index, file) {
                                                                if (file.url) {
                                                                    var link = $('<a>')
                                                                        .attr('target', '_blank')
                                                                        .prop('href', file.url);
                                                                    $(data.context.children()[index])
                                                                        .wrap(link);

                                                                    $('input[name="WalmartProduct\[product_variants\]\[<?= $value['option_id'] ?>\]\[url\]"]').val(file.name); 
                                                                } else if (file.error) {
                                                                    var error = $('<span class="text-danger"/>').text(file.error);
                                                                    $(data.context.children()[index])
                                                                        .append('<br>')
                                                                        .append(error);
                                                                }
                                                            });
                                                        }).on('fileuploadfail', function (e, data) {
                                                            $.each(data.files, function (index) {
                                                                var error = $('<span class="text-danger"/>').text('File upload failed.');
                                                                $(data.context.children()[index])
                                                                    .append('<br>')
                                                                    .append(error);
                                                            });
                                                        }).prop('disabled', !$.support.fileInput)
                                                            .parent().addClass($.support.fileInput ? undefined : 'disabled');
                                                    });
                                                    </script>
                                        <?php }?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }       
                        ?>
                        </table>    
                        </div>
                        <button type="submit" class="btn btn-primary">Save
                         </button>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <div class="modal-footer Attrubute_html">
                    <button type="button" class="btn btn-default" id="edit-modal-close" data-dismiss="modal">Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
<style>
    .walmart-price {
        width: 50px;
    }

    .walmart-inventory {
        width: 50px;
    }

    .walmart-price-button {
        background: rgba(0, 0, 0, 0) linear-gradient(to bottom, #73efed 0%, #3b67dd 100%) repeat scroll 0 0;
        border: 1px solid #f2f2f2;
        color: #fff;
        font-size: 13px;
        margin: 0;
        padding: 5px 5px;
        text-transform: capitalize;
        transition: all 0.2s ease 0s;
    }

    .walmart-inventory-button {
        background: rgba(0, 0, 0, 0) linear-gradient(to bottom, #73efed 0%, #3b67dd 100%) repeat scroll 0 0;
        border: 1px solid #f2f2f2;
        color: #fff;
        font-size: 13px;
        margin: 0;
        padding: 5px 5px;
        text-transform: capitalize;
        transition: all 0.2s ease 0s;
    }

    .modal-lg {
        width: 975px !important;
    }

    .walmart_price {
        width: 151px;
    }

    .walmart_inventory {
        width: 151px;
    }
    .field-jetproduct .public input {
        display: inline-block;
        margin-right: 12px !important;
        width: 74%;
    }

    .field-jetproduct .public span {
        display: inline-block;
    }
</style>
