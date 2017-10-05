<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 10/2/17
 * Time: 1:02 PM
 */
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\models\NeweggProduct;

$this->title = 'Manage Products';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = MERCHANT_ID;
$urlWalmart = \yii\helpers\Url::toRoute(['neweggproduct/getwalmartdata']);
$urlNeweggEdit = \yii\helpers\Url::toRoute(['neweggproduct/editdata']);
$urlWalmartError = \yii\helpers\Url::toRoute(['neweggproduct/errornewegg']);
$formPost = \yii\helpers\Url::toRoute(['neweggproduct/syncproductstore']);

?>
<div class="jet-product-index content-section">
    <div class="form new-section">
        <?= Html::beginForm(['neweggproduct/ajax-bulk-upload'], 'post');//Html::beginForm(['walmartproduct/bulk'],'post');     ?>
        <div class="jet-pages-heading">
            <?php
            $categoryData = Data::sqlRecords("SELECT `shopify_product_type`,`newegg_category_id` FROM `newegg_attribute_map` WHERE `merchant_id`='" . MERCHANT_ID . "' AND `attribute_map` IS NOT NULL", "all", "select");
            if (count($categoryData) > 0) {
                $savedData = Data::sqlRecords("SELECT * FROM `newegg_value_mapping` WHERE `merchant_id`='" . MERCHANT_ID . "'", "all", "select");
                if (empty($savedData)) {
                    ?>
                    <div class="alert alert-warning">
                        <strong>Map atleast one product value for uploading variants product Just click <a
                                    href="<?= yii\helpers\Url::toRoute('newegg-valuemap/index'); ?>"> here </a> to map
                            all shopify variants product value(s) with newegg attribute value. </strong>
                    </div>
                <?php }
            } ?>
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet"
                   href="<?= Yii::$app->request->baseUrl ?>/newegg-marketplace/sell-on-newegg#sec4"
                   target="_blank" title="Need Help"></a>
            </div>
            <div class="product-upload-menu">

                <?= Html::a('Update Product Price', ['updateprice'], ['class' => 'btn btn-primary', 'data-step' => '7', 'data-position' => 'top', 'data-intro' => 'Sync product(s) price on newegg.']) ?>
                <?= Html::a('Update Product Inventory', ['updateinventory'], ['class' => 'btn btn-primary', 'data-step' => '8', 'data-position' => 'top', 'data-intro' => 'Sync product(s) inventory on newegg.']) ?>
                <?= Html::a('Get Product Status', ['batchproductstatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) status from newegg.', 'class' => 'btn btn-primary']) ?>

                <button type="button" class="btn btn-primary noconfirmbox" id="sync_with_btn" data-toggle='tooltip' ,
                        title='Sync product(s) data from shopify.' , onclick="cnfrmSync()">Sync
                    With
                    Shopify
                </button>
            </div>

            <div class="clear"></div>
        </div>
        <div class="jet_notice" style="background-color: #FCF8E3;">
    <span class="font_bell">
      <i class="fa fa-list" aria-hidden="true"></i>
        <!-- <i class="fa fa-bell fa-1x"></i> -->
    </span>
            Don't see all of your products? Just click <a
                    href="<?= yii\helpers\Url::toRoute('categorymap/index'); ?>">here</a>
            to map all shopify product type(s) with newegg category.
            <div class="list-page" style="float:right">
                Show per page
                <select onchange="selectPage(this)" class="form-control"
                        style="display: inline-block; width: auto; margin-top: 0px; margin-left: 5px; margin-right: 5px;"
                        name="per-page">
                    <option value="25" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 25) {
                        echo "selected=selected";
                    } ?>>25
                    </option>
                    <option <?php if (!isset($_GET['per-page'])) {
                        echo "selected=selected";
                    } ?> value="50">50
                    </option>
                    <option value="100" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 100) {
                        echo "selected=selected";
                    } ?> >100
                    </option>
                </select>
                Items
            </div>
            <div style="clear:both"></div>
        </div>

        <div class="submit-upload-wrap">
            <?php $bulkActionSubmit = Html::submitButton('submit', ['class' => 'btn btn-primary', 'data-step' => '3', 'data-intro' => "Submit the operated BULK ACTION.", 'data-position' => 'bottom']); ?>
            <?php $arrAction = array('batch-upload' => 'Upload', 'image-update' => 'Image Update','retire-product'=>'Retire Product'); ?>
            <?php $bulkActionSelect = Html::dropDownList('action', '', $arrAction, ['class' => 'form-control ', 'data-step' => '2', 'data-intro' => "Select the BULK ACTION you want to operate.", 'data-position' => 'bottom']) ?>
        </div>
        <!--        <div class="responsive-table-wrap">-->

        <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
        <?= GridView::widget([
            'id' => "product_grid",
            'options' => ['class' => 'table-responsive'],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterSelector' => "select[name='" . $dataProvider->getPagination()->pageSizeParam . "'],input[name='" . $dataProvider->getPagination()->pageParam . "']",

            'pager' => [
                'class' => \liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [25, 50, 100],
                'pageSizeOptions' => ['class' => 'form-control', 'style' => 'display: none;width:auto;margin-top:0px;'],
                'maxButtonCount' => 5,
            ],
            'summary' => '<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><div class="bulk-action-wrapper">' . $bulkActionSelect . $bulkActionSubmit . '<a href="' . Yii::$app->request->getBaseUrl() . "/neweggmarketplace/neweggproduct/index" . '" class="btn btn-primary reset-filter">Reset</a><span title="Need Help" class="help_jet white-bg" style="cursor:pointer;" id="instant-help"></span></div></div></div>',

            'columns' => [
                // ['class' => 'yii\grid\SerialColumn'],
                //['class' => 'yii\grid\CheckboxColumn'],
                ['class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function ($data) {
                        return ['value' => $data['product_id']];
                    },
                    'headerOptions' => ['id' => 'checkbox_header', 'data-step' => '1', 'data-intro' => "Select Products to Upload.", 'data-position' => 'right']
                ],
                'product_id',
                [
                    'attribute' => 'image',
                    'format' => 'html',
                    'label' => 'Image',
                    'value' => function ($data) {

                        if ($data['jet_product']['image']) {
                            if (count(explode(',', $data['jet_product']['image'])) > 0) {
                                $images = [];
                                $images = explode(',', $data['jet_product']['image']);
                                return Html::img($images[0],
                                    ['width' => '80px', 'height' => '80px']);
                            } else {
                                return Html::img($data['jet_product']['image'],
                                    ['width' => '80px', 'height' => '80px']);
                            }
                        } else {
                            return "";
                        }
                    },
                ],
                [
                    'attribute' => 'title',
                    'label' => 'Title',
                    'value' => function ($data) {
                        if ($data['product_title']) {
                            return $data['product_title'];
                        } else {
                            return $data['jet_product']['title'];
                        }
                    },
                ],
                [
                    'attribute' => 'sku',
                    'label' => 'Sku',
                    'value' => 'jet_product.sku',
                ],
                [
                    'attribute' => 'upload_status',
                    'label' => 'Upload Status',
                    'headerOptions' => ['width' => '160'],
                    'filter' => ["ACTIVATED" => "ACTIVATED", "Not Uploaded" => "Not Uploaded", "SUBMITTED" => "SUBMITTED", "DEACTIVATED" => "DEACTIVATED", "UPLOADED WITH ERROR" => "UPLOAD WITH ERROR", "other" => "Other Products"],
                    'format' => 'html',
                    'value' => function ($data) {
                        if ($data['option_status'] != null) {
                            $status = explode(',', $data['option_status']);
                            $value = array_count_values($status);

                            if (!empty($value)) {
                                //return Html::renderTagAttributes($value);
                                $status = ['DEACTIVATED', 'Not Uploaded', 'ACTIVATED', 'STAGE', 'UNPUBLISHED'];
                                $html1 = '';
                                $html1 .= '<ul>';
                                foreach ($value as $key => $val) {
                                    if (empty($key) || !in_array($key, $status)) {
                                        $key = 'Others';
                                    }

                                    $html1 .= '<li class="' . $key . '">' . $key . ' : ' . $val . '</li>';
                                }
                                $html1 .= '</ul>';
                                return $html1;
                            }
                        } else {
                            $html1 = '';
                            $html1 .= '<ul>';

                            $html1 .= '<li class="' . $data['upload_status'] . '">' . $data['upload_status'] . '</li>';
                            $html1 .= '</ul>';
                            return $html1;
                        }
                        //return $data['status'];
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Action', 'headerOptions' => ['width' => '80'],
                    'template' => '{view}{link}{errors}',
                    'buttons' => [

                        'link' => function ($url, $model) {
                            return '<a data-pjax="0" href="' . \yii\helpers\Url::toRoute(['neweggproduct/editdata', 'id' => $model->id, 'title' => 'Edit']) . '" ><span class="glyphicon glyphicon glyphicon-pencil"> </span></a>';

                        },
                        'view' => function ($url, $model) {
                            if ($model->upload_status == "PUBLISHED") {
                                //var_dump($model);die;
                                return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
                                    'javascript:void(0)', ['data-pjax' => 0, 'onclick' => 'clickView(this.id)', 'title' => 'View Product Detail', 'id' => $model->jet_product->sku]
                                );
                            }
                        },
                        'errors' => function ($url, $model) {
                            if (($model->error != "") && !is_null($model->error)) {
                                return Html::a(
                                    '<span class="fa fa-exclamation-circle upload-error"> </span>',
                                    'javascript:void(0)', ['data-pjax' => 0, 'onclick' => 'checkError(this.id)', 'title' => 'Upload Error', 'id' => $model->id]
                                );
                            }
                        }
                    ],
                ],
                [
                    'attribute' => 'qty',
                    'label' => 'Quantity',
                    'value' => 'jet_product.qty',
                ],
                /*[
                    'attribute' => 'price',
                    'label' => 'Price',
                    'value' => 'jet_product.price',
                ],*/
                [
                    'attribute' => 'price',
                    'label' => 'Price',
                    'format' => 'html',
                    'filter' => 'From : <input id="price_from" class="form-control" type="text" name="NeweggProductSearch[price_from]" value="' . $searchModel->price_from . '" /><br/>' . 'To : <input class="form-control" type="text" name="NeweggProductSearch[price_to]" value="' . $searchModel->price_to . '"/>',
                    'value' => function ($data) {
                        if ($data['product_price']) {
                            return $data['product_price'];
                        } else {
                            return $data['jet_product']['price'];
                        }
                    },
                ],
                [
                    'attribute' => 'shopify_product_type',
                    'label' => 'Product Type',
                    'value' => 'shopify_product_type'
                ],
                [
                    'attribute' => 'upc',
                    'label' => 'Barcode',
                    'value' => 'jet_product.upc',
                ],
                //'tax_code',
                /*[
                  'attribute'=>'tax_code',
                  'label'=>'Tax Code',
                  'format'=>'raw',
                  'headerOptions' => ['width' => '150'],
                  'value' => function ($data)
                    {
                      return $data->tax_code;
                      // return Html::a(
                              // 'Get TaxCode',
                              // 'javascript:void(0)',['data-pjax'=>0,'onclick'=>'getTax(this.id)','id'=>$data->product_id]
                          // );
                    },
                ],*/
                [
                    'attribute' => 'type',
                    'label' => 'Type',
                    'headerOptions' => ['width' => '80'],
                    'filter' => ["simple" => "simple", "variants" => "variants"],
                    'value' => function ($data) {
                        return $data['jet_product']['type'];
                    },

                ],
//                    [
//                        'attribute' => 'upload_status',
//                        'label' => 'Upload Status',
//                        'headerOptions' => ['width' => '160'],
//                        'filter' => ["ACTIVATED"=>"ACTIVATED","Not Uploaded"=>"Not Uploaded","SUBMITTED"=>"SUBMITTED","DEACTIVATED"=>"DEACTIVATED","UPLOAD WITH ERROR"=>"UPLOAD WITH ERROR"],
//                        /*'filter' => ArrayHelper::map(NeweggProduct::find()->all(), 'upload_status', 'upload_status'),*/
//                        'value' => function ($data)
//                         {
//                             return $data['upload_status'];
//                         },
//
//                    ],

            ],
        ]); ?>
        <?php Pjax::end(); ?>
        <?= Html::endForm() ?>
        <!--        </div>-->
    </div>
</div>
<?php
if (isset($productPopup)) {
    ?>
    <div class="walmart_config_popup walmart_config_popup_error" style="">
        <div id="jet-import-product" class="import-product">
            <div class="fieldset welcome_message">
                <div class="entry-edit-head">
                    <h4 class="fieldset-legend">
                        Welcome! to Newegg Products Import Section
                    </h4>
                </div>
                <?php
                if ($countUpload) {
                    ?>
                    <div class="entry-edit-head">
                        <h4>You have <?php echo $countUpload; ?> products in your shopify Store. </h4>
                        <h4 id="product_import" class="alert-success" style="display: none"></h4>
                        <h4 id="not_sku" style="display: none" class="alert-success"></h4>
                    </div>
                    <div class="import-btn">
                        <h4>Click to import Shopify store products to Newegg-Integration App<h4>
                                <a href="<?php echo \yii\helpers\Url::toRoute(['neweggproduct/batchimport']) ?>"
                                   class="btn">Import
                                    Products</a>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="product-error">
                        <h4>Either you don't have any product or none of products have SKU in your shopify Store </h4>
                    </div>
                    <?php
                }
                ?>
                <div class="loading-bar" style="display: none;">
                    <img alt="" src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?>/images/loading_spinner.gif">
                    <h3>Please wait...</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="walmart_config_popup_overlay" style=""></div>
<?php } ?>
<div id="view_walmart_product" style="display:none">
</div>
<div id="edit_walmart_product" style="display:none">
</div>
<div id="products_error" style="display:none">
</div>
<!-- Modal Confirm box html  -->
<div id="confirm" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="cnfrm-yes">Yes</button>
                <button type="button" class="btn" id="cnfrm-no" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>
<?php
$sync_fields = [
    'title' => 'Title',
    'sku' => 'Sku',
    'image' => 'Image',
    'inventory' => 'Inventory',
    'weight' => 'Weight',
    'price' => 'Price',
    'upc' => 'UPC/Barcode/Other',
    'variant_option_values' => 'Variant Option Values',
    'vendor' => 'Vendor',
    'product_type' => 'Product Type',
    'description' => 'Description'
];
?>

<!-- Modal Sync Form html  -->
<div id="sync" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">


            <div class="modal-body">

                <form id="sync-fields-form" method="post" action="<?= $formPost; ?>">
                    <h4>Select Fields to Sync with Shopify :</h4>
                    <div class="all_checkbox_options">
                        <input type="checkbox" class="all-sync-fields-checkbox"
                               id="all-sync-fields-checkbox" name="" value="1"/>
                        <label>Select All</label>
                    </div>
                    <div class="sync-fields">
                        <?php foreach ($sync_fields as $sync_index => $sync_value) : ?>
                            <div class="checkbox_options">
                                <input type="checkbox" class="sync-fields-checkbox"
                                       name="sync-fields[<?= $sync_index ?>]" value="1"/>
                                <label><?= $sync_value ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="sync-yes">Sync</button>
                <button type="button" class="btn" id="sync-cancel" data-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    function cnfrmSync(e) {
        $('body').attr('data-sync', 'show');
        $('#edit-modal-close').click();
        if ($('body').attr('data-sync') == 'show') {
            $('#sync').modal('show');
            $('body').removeAttr('data-sync');
        }

        $("#sync").on('shown.bs.modal', function () {
            $('#sync-yes').unbind('click');
            $('#sync-yes').on('click', function () {
                var selectCount = 0;
                $.each($(".sync-fields-checkbox"), function () {
                    if ($(this).is(':checked') === true) {
                        selectCount++;
                    }
                });
                if (selectCount) {
                    $('#sync-fields-form').submit();
                }
                else {
                    alert("Please select fields to sync.");
                    return false;
                }
            });
        });
    }
    var submit_form = false;
    $('body').on('keyup', '.filters > td > input', function (event) {
        if (event.keyCode == 13) {
            if (submit_form === false) {
                submit_form = true;
                $("#product_grid").yiiGridView("applyFilter");
            }
        }

    });
    $("body").on('beforeFilter', "#product_grid", function (event) {
        return submit_form;
    });
    $("body").on('afterFilter', "#product_grid", function (event) {
        submit_form = false;
    });
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    function clickView(id) {
        var url = '<?= $urlWalmart ?>';
        var merchant_id = '<?= $merchant_id;?>';
        j$('#LoadingMSG').show();
        j$.ajax({
            method: "post",
            url: url,
            data: {id: id, merchant_id: merchant_id, _csrf: csrfToken}
        })
            .done(function (msg) {
                console.log(msg);
                j$('#LoadingMSG').hide();
                j$('#view_walmart_product').html(msg);
                j$('#view_walmart_product').css("display", "block");
                $('#view_walmart_product #myModal').modal('show');
            });
    }
    function clickEdit(id) {
        var url = '<?= $urlNeweggEdit; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        //j$('#LoadingMSG').show();
        j$.ajax({
            method: "post",
            url: url,
            data: {id: id, merchant_id: merchant_id, _csrf: csrfToken}
        })
            .done(function (msg) {
                //console.log(msg);
                j$('#LoadingMSG').hide();
                j$('#edit_walmart_product').html(msg);
                j$('#edit_walmart_product').css("display", "block");
                $('#edit_walmart_product #myModal').modal({
                    keyboard: false,
                    backdrop: 'static'
                })
            });
    }
    function checkError(id) {
        var url = '<?= $urlWalmartError ?>';
        var merchant_id = '<?= $merchant_id;?>';
        j$('#LoadingMSG').show();
        j$.ajax({
            method: "post",
            url: url,
            data: {id: id, merchant_id: merchant_id, _csrf: csrfToken}
        })
            .done(function (msg) {
                console.log(msg);
                j$('#LoadingMSG').hide();
                j$('#products_error').html(msg);
                j$('#products_error').css("display", "block");
                $('#products_error #myModal').modal('show');
            });
    }
    function selectPage(node) {
        var value = $(node).val();
        $('#product_grid').children('select.form-control').val(value);
    }

    $(function () {
        var intro = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
            /*steps: [
             {
             element: '#product_edit_action',
             intro: 'This is Shopify Product Type.',
             position: 'bottom'
             },
             {
             element: '#product_error_action',
             intro: 'This is Shopify Product Type.',
             position: 'bottom'
             }
             ]*/
        });
        $('#instant-help').click(function () {
            intro.start();
        });
    });
</script>
<?php $get = Yii::$app->request->get();
if (isset($get['tour'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var productQuicktour = introJs().setOptions({
                doneLabel: 'Next page',
                showStepNumbers: false,
                exitOnOverlayClick: false,
            });
            setTimeout(function () {

                productQuicktour.start().oncomplete(function () {
                    window.location.href = '<?= \frontend\modules\neweggmarketplace\components\Data::getUrl("neweggorderdetail/index?tour") ?>';
                });
            }, 1000);

        });
    </script>
<?php endif; ?>
<?php $get = Yii::$app->request->get();
if (isset($get['_edt'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            console.log($("a[product_id='<?=trim($get['_edt'])?>']"));
            $("a[product_id='<?=trim($get['_edt'])?>']").trigger('click');
        });
    </script>
<?php endif; ?>
<?php
if (isset($get['_upd'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var introV = introJs().setOptions({
                showStepNumbers: false,
                exitOnOverlayClick: false,
                steps: [
                    {
                        element: '#product_validate',
                        intro: 'Validate Product(s) as per Walmart Requirements.',
                        position: 'bottom'
                    },
                ]
            });
            introV.start();
        });
    </script>
<?php endif; ?>
<!-- Select All on Sync With shopify -->
<script type="text/javascript">
    $('#all-sync-fields-checkbox').click(function () {
        if (!$(this).is(':checked')) {
            $('.sync-fields-checkbox').prop('checked', false);
        }
        else {
            $('.sync-fields-checkbox').prop('checked', true);
        }
    });
    $('.checkbox_options').click(function () {
        $("input:checkbox[class=sync-fields-checkbox]").each(function () {
            if (!$(this).is(':checked')) {
                $('#all-sync-fields-checkbox').prop('checked', false);
                return false;
            }
            else {
                $('#all-sync-fields-checkbox').prop('checked', true);
            }
        });

    });
</script>
<!-- end here -->
