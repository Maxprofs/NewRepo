<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
//use yii\grid\GridView;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartProduct;
use kartik\grid\GridView;

$this->title = 'Manage Catalog';
$this->params['breadcrumbs'][] = $this->title;

$controllerName = 'product';
$urlWalmart = Data::getUrl("walmartproduct/getwalmartdata");
$urlWalmartEdit = Data::getUrl("$controllerName/editdata");
$urlWalmartError = Data::getUrl("walmartproduct/errorwalmart");
$urlGetTax = Data::getUrl("walmartproduct/gettaxcode");
$formPost = Data::getUrl("walmartproduct/syncproductstore");
?>
<style>

    .list-page {
        width: 30%;
        float: right;
        text-align: right;
    }

    .left-div {
        width: 75%;
        float: left;
        margin-top: 2px;
    }

    .table.table-striped.table-bordered tr th {
        font-size: 14px;
        /*font-weight: 600;*/
    }

    .jet-product-index .jet_notice {
        font-weight: normal !important;
    }

    .jet-product-index .jet_notice .fa-bell {
        color: #B11600;
    }

    .jet-product-index .no-data {
        display: none;
    }

    .jet-product-index .no_product_error {
        background-color: #f2dede;
        border-color: #ebccd1;
        color: #a94442;
    }

    .jet_config_popup .product-import, .jet_config_popup .welcome_message {
        background: #fff none repeat scroll 0 0;
        border-radius: 5px !important;
        margin: 5% auto 3%;
        overflow: hidden;
        position: relative;
        width: 50%;
        margin-top: 11%;
    }

    .jet-product-index .jet_notice {
        background-color: #f5f5f5;
        border-color: #d6e9c6;
        border-radius: 4px;
        color: #333;
        font-size: 14px;
        font-weight: bold;
        line-height: 19px;
        margin-bottom: 0;
        padding: 4px 8px;
    }

    .import_popup.jet_config_popup.jet_config_popup_error {
        box-shadow: 0 0 6px 3px #000000;
        left: 0;
        top: 0%;
        width: 100%;
    }

    .table.table-bordered tr td a span {
        color: #1A75CF;
    }

    .table.table-bordered tr td a span.upload-error {
        color: #F16935;
        font-size: 1.5em;
        padding: 5px;
    }

    .table.table-bordered tr.danger td {
        background-color: #cfd8dc;
    }

    .Others {
        color: #1A75CF;
    }

    .Not.Uploaded {
        color: red;
    }

    .Item.Processing {
        color: #d08c00;
    }

    .PUBLISHED {
        color: green;
    }

    .STAGE {
        color: green;
    }

    .UNPUBLISHED {
        color: red;
    }

    .viewmore {
        color: #1A75CF;
    }

    .reset {
        float: right;
    }
</style>

<div class="jet-product-index content-section ced-manageproduct">
    <div class="form new-section">
        <?= Html::beginForm(['walmartproduct/ajax-bulk-upload'], 'post', ['id' => 'walmart_bulk_product']); ?>
        <div class="jet-pages-heading walmart-title">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet"
                   href="<?= Yii::$app->request->baseUrl ?>/walmart/sell-on-walmart#sec5"
                   target="_blank" title="Need Help"></a>
            </div>

            <div class="clear"></div>
        </div>
        <div class="jet_notice" style="background-color: #FCF8E3;">
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
    <span class="font_bell">
      <i class="fa fa-list" aria-hidden="true"></i>
    </span>
                Don't see all of your products? Just click <a
                        href="<?= yii\helpers\Url::toRoute('categorymap/index'); ?>">here</a> to map all shopify product
                type(s) with walmart category.
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
                <div class="" style="float:right">
                    <div class="list-pages" style="float: left">
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
                    <div class="reset">
                        <a href="<?= Yii::$app->request->getBaseUrl() . '/walmart/walmartproduct/index' ?>"
                           class="btn btn-primary">Reset Filter</a>
                    </div>
                </div>
            </div>

            <div style="clear:both"></div>
        </div>

        <?php Pjax::begin(['timeout' => 30000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
        <?/*= GridView::widget([
            'id' => "kv-grid-demo",
            'options' => ['class' => 'table-responsive grid-view'],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterSelector' => "select[name='" . $dataProvider->getPagination()->pageSizeParam . "'],input[name='" . $dataProvider->getPagination()->pageParam . "']",
            'pager' => [
                'class' => \liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [25, 50, 100],
                'pageSizeOptions' => ['class' => 'form-control', 'style' => 'display: none;width:auto;margin-top:0px;'],
                'maxButtonCount' => 5,
            ],
            'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'summary' => '<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"></div></div>',
            'columns' => [

                ['class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function ($data) {
                        return ['value' => $data['product_id'], 'class' => 'bulk_checkbox'];
                    },
                    'headerOptions' => ['id' => 'checkbox_header', 'data-step' => '1', 'data-intro' => "Select Products to Upload.", 'data-position' => 'right']
                ],
                [
                    'attribute' => 'sku',
                    'label' => 'Sku',
                    'value' => 'jet_product.sku',
                ],
                [
                    'attribute' => 'image',
                    'format' => 'html',
                    'label' => 'IMAGE',
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
                    'attribute' => 'status',
                    'label' => 'Status',
                    'headerOptions' => ['width' => '160'],
                    'filter' => ["Item Processing" => "Item Processing", "Not Uploaded" => "Not Uploaded", "PUBLISHED" => "PUBLISHED", "STAGE" => "STAGE", "UNPUBLISHED" => "UNPUBLISHED", "other" => "Other Products"],
                    'format' => 'html',
                    'value' => function ($data) {
                        if ($data['option_status'] != null) {
                            $status = explode(',', $data['option_status']);
                            $value = array_count_values($status);

                            if (!empty($value)) {
                                //return Html::renderTagAttributes($value);
                                $status = ['Item Processing', 'Not Uploaded', 'PUBLISHED', 'STAGE', 'UNPUBLISHED'];
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

                            $html1 .= '<li class="' . $data['status'] . '">' . $data['status'] . '</li>';
                            $html1 .= '</ul>';
                            return $html1;
                        }
                        //return $data['status'];
                    }
                ],
                [
                    'attribute' => 'qty',
                    'label' => 'Quantity',
                    'value' => function ($data) {
                        if (is_null($data['product_qty'])) {
                            return $data['jet_product']['qty'];
                        } else {
                            return $data['product_qty'];
                        }
                    }
                ],
                [
                    'attribute' => 'price',
                    'label' => 'Price',
                    'format' => 'html',
                    'filter' => 'From : <input id="price_from" class="form-control" type="text" name="' . $searchModel->getClassName() . '[price_from]" value="' . $searchModel->price_from . '" /><br/>' . 'To : <input class="form-control" type="text" name="' . $searchModel->getClassName() . '[price_to]" value="' . $searchModel->price_to . '"/>',
                    'value' => function ($data) {
                        if ($data['product_price']) {
                            return $data['product_price'];
                        } else {
                            return $data['jet_product']['price'];
                        }
                    },
                ],
                [
                    'attribute' => 'product_type',
                    'label' => 'Product Type',
                    'value' => 'jet_product.product_type'
                ],
                [
                    'attribute' => 'vendor',
                    'label' => 'Brand',
                    'value' => 'jet_product.vendor'
                ],
                [
                    'attribute' => 'upc',
                    'label' => 'Barcode',
                    'value' => 'jet_product.upc',
                ],
                [
                    'attribute' => 'type',
                    'label' => 'Type',
                    'headerOptions' => ['width' => '80'],
                    'filter' => ["simple" => "simple", "variants" => "variants"],
                    'value' => function ($data) {
                        return $data['jet_product']['type'];
                    },

                ],
                'product_id',

            ],
        ]); */?>


        <?= GridView::widget([
            'dataProvider'=>$dataProvider,
            'filterModel'=>$searchModel,
            'showPageSummary'=>true,
            'pjax'=>true,
            'striped'=>true,
            'hover'=>true,
            'panel'=>['type'=>'primary', 'heading'=>'Grid Grouping Example'],
            'columns'=>[
                ['class'=>'kartik\grid\SerialColumn'],
                [
                    'attribute'=>'product_id',
                    'width'=>'310px',
                    'value'=>function ($dataProvider, $key, $index, $widget) {
                        return $dataProvider->product_id;
                    },
                    //'filterType'=>GridView::FILTER_SELECT2,
                    //'filter'=>\yii\helpers\ArrayHelper::map(WalmartProduct::find()->orderBy('product_id')->asArray()->all(), 'id', 'company_name'),
                    'filterWidgetOptions'=>[
                        'pluginOptions'=>['allowClear'=>true],
                    ],
                    'filterInputOptions'=>['placeholder'=>'Any supplier'],
                    'group'=>true,  // enable grouping
                ],
                [
                    'attribute'=>'sku',
                    'width'=>'250px',
                    /*'value'=>function ($dataProvider, $key, $index, $widget) {
                        return $dataProvider->sku;
                    },*/
                    'value'=>'jet_product.option_id',
//                    'filterType'=>GridView::FILTER_SELECT2,
                    'filter'=>\yii\helpers\ArrayHelper::map(\frontend\modules\walmart\models\WalmartProductVariants::find()->orderBy('product_id')->asArray()->all(), 'product_id', 'option_id'),
                    'filterWidgetOptions'=>[
                        'pluginOptions'=>['allowClear'=>true],
                    ],
                    'filterInputOptions'=>['placeholder'=>'Any category']
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
                    'attribute' => 'status',
                    'label' => 'Status',
                    'headerOptions' => ['width' => '160'],
                    'filter' => ["Item Processing" => "Item Processing", "Not Uploaded" => "Not Uploaded", "PUBLISHED" => "PUBLISHED", "STAGE" => "STAGE", "UNPUBLISHED" => "UNPUBLISHED", "other" => "Other Products"],
                    'format' => 'html',
                    'value' => function ($data) {
                        if ($data['option_status'] != null) {
                            $status = explode(',', $data['option_status']);
                            $value = array_count_values($status);

                            if (!empty($value)) {
                                //return Html::renderTagAttributes($value);
                                $status = ['Item Processing', 'Not Uploaded', 'PUBLISHED', 'STAGE', 'UNPUBLISHED'];
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

                            $html1 .= '<li class="' . $data['status'] . '">' . $data['status'] . '</li>';
                            $html1 .= '</ul>';
                            return $html1;
                        }
                        //return $data['status'];
                    }
                ],
                [
                    'attribute' => 'qty',
                    'label' => 'Quantity',
                    'value' => function ($data) {
                        if (is_null($data['product_qty'])) {
                            return $data['jet_product']['qty'];
                        } else {
                            return $data['product_qty'];
                        }
                    }
                ],
                [
                    'attribute' => 'price',
                    'label' => 'Price',
                    'format' => 'html',
                    'filter' => 'From : <input id="price_from" class="form-control" type="text" name="' . $searchModel->getClassName() . '[price_from]" value="' . $searchModel->price_from . '" /><br/>' . 'To : <input class="form-control" type="text" name="' . $searchModel->getClassName() . '[price_to]" value="' . $searchModel->price_to . '"/>',
                    'value' => function ($data) {
                        if ($data['product_price']) {
                            return $data['product_price'];
                        } else {
                            return $data['jet_product']['price'];
                        }
                    },
                ],
                [
                    'attribute' => 'product_type',
                    'label' => 'Product Type',
                    'value' => 'jet_product.product_type'
                ],
                [
                    'attribute' => 'vendor',
                    'label' => 'Brand',
                    'value' => 'jet_product.vendor'
                ],
                [
                    'attribute' => 'upc',
                    'label' => 'Barcode',
                    'value' => 'jet_product.upc',
                ],
                [
                    'attribute' => 'type',
                    'label' => 'Type',
                    'headerOptions' => ['width' => '80'],
                    'filter' => ["simple" => "simple", "variants" => "variants"],
                    'value' => function ($data) {
                        return $data['jet_product']['type'];
                    },

                ],
                'product_id',
            ],

        ]); ?>
        <?php Pjax::end(); ?>
        <?= Html::endForm() ?>
    </div>
</div>
<?php
?>

<!-- Grid Scroll Script By Sanjeev -->
<script>
    var heightTbody = 0;
    var containerWidth = 0;
    var contentWidth = 0;
    var scrollRight = 0;
    var scrollbarWidth = 0;//SCROLLBAR width actual
    var scrollbarHeight = 0;
    var swipeHtml = '<div class="follow"><div class="swipe-left" id="swipeLeft" style="display:none;" ><span><i class="fa fa-chevron-left" aria-hidden="true"></i>' +
        '</span></div><div class="swipe-right" id="swipeRight" style="display:none;" ><span><i class="fa fa-chevron-right" aria-hidden="true"></i>' +
        '</span></div></div>';

    /*var looper = (function tempName(previousInnerHTML)
     {
     setTimeout((function(resetTimeout)
     {
     return function()
     {
     if (document.body.innerHTML !== previousInnerHTML) {
     gridContentChanged();
     }
     resetTimeout();
     };
     }(tempName)),1000);
     }(document.body.innerHTML));*/
    /*$( document ).ajaxSuccess(function( event, request, settings ) {
     gridContentChanged();
     });*/
    $(document).on('pjax:success', function () {
        gridContentChanged();
    });
    $(function () {
        $('body').prepend(swipeHtml);
        heightTbody = $('table.table tbody').outerHeight();
        appendScrollTabFunction();
    });

    $(window).on('resize', function () {
        $('#swipeLeft').css('display', 'none');
        $('#swipeRight').css('display', 'none');
        containerWidth = 0;
        contentWidth = 0;
        scrollbarWidth = 0;
        scrollbarHeight = 0;
        heightTbody = $('table.table tbody').outerHeight();
        appendScrollTabFunction();
    });

    function gridContentChanged() {
        $('#swipeLeft').css('display', 'none');
        $('#swipeRight').css('display', 'none');
        containerWidth = 0;
        contentWidth = 0;
        scrollbarWidth = 0;
        scrollbarHeight = 0;
        heightTbody = $('table.table tbody').outerHeight();
        appendScrollTabFunction();
    }
    function appendScrollTabFunction() {
        containerWidth = $('div.table-responsive').width();
        containerWidth = parseFloat(containerWidth);
        contentWidth = $('table.table').width();
        contentWidth = parseFloat(contentWidth);
        scrollbarHeight = getScrollbarHeight();
        //maxScroll = parseFloat(((containerWidth/contentWidth)*100).toFixed(2));//SCROLLBAR width
        if (contentWidth > containerWidth) {
            scrollbarWidth = getScrollbarWidth();
            mainContentScroll();
        }
    }

    $(document).on('click', '#swipeRight', function () {
        $('div.table-responsive').animate({scrollLeft: contentWidth}, 800);
    });

    $(document).on('click', '#swipeLeft', function () {
        $('div.table-responsive').animate({scrollLeft: 0}, 800);
    });
    $('div.table-responsive').scroll(function () {
        if (contentWidth > containerWidth) {
            mainContentScroll();
        }
    });
    $(document).scroll(function () {
        if (contentWidth > containerWidth) {
            mainContentScroll();
        }
    });
    function mainContentScroll() {
        var currentScrollValue = $('div.table-responsive').scrollLeft();
        currentScrollValue = parseFloat(currentScrollValue);
        var top = $('table.table tbody').offset().top;
        //var scr = parseFloat($('table.table thead').height())/*+parseFloat($('table.table tbody tr:first').height())*/+/**/parseFloat($('table.table thead').offset().top);//125;
        $('.follow').css('display', 'none');
        if ((parseFloat($(window).scrollTop()) + scrollbarHeight) >= top + 100 && (parseFloat($(window).scrollTop()) + scrollbarHeight) <= top + heightTbody + 100) {// - followHeight
            //scr += parseFloat($(window).scrollTop());//(parseFloat($(window).scrollTop())+scrollbarHeight)-top;
            $('.follow').css('display', 'block');
        }
        /* var offset = parseInt(scr);
         if (offset > 0) {
         //$('.follow').css('transform', 'translateY('+ offset +'px)');
         }*/

        //if(elementInViewportNew(document.querySelectorAll('table.table tbody tr'))){//if(elementStart < scroll && elementEnd > scroll){
        if (currentScrollValue == 0) {// && currentScrollValue < maxScroll
            //scrollbar at left part
            $('#swipeLeft').css('display', 'none');
            $('#swipeRight').css('display', 'block');
        } else if (currentScrollValue > 0 && currentScrollValue < contentWidth - scrollbarWidth) {
            //scrollbar at middle part
            $('#swipeLeft').css('display', 'block');
            $('#swipeRight').css('display', 'block');
        } else {
            //scrollbar at right part
            $('#swipeLeft').css('display', 'block');
            $('#swipeRight').css('display', 'none');
        }
    }

    function getScrollbarWidth() {
        var original = $('div.table-responsive').scrollLeft();
        $('div.table-responsive').scrollLeft(contentWidth);
        scrollRight = $('div.table-responsive').scrollLeft();
        scrollRight = parseFloat(scrollRight);
        $('div.table-responsive').scrollLeft(original);
        return contentWidth - scrollRight
    }
    function getScrollbarHeight() {
        var original = $(document).scrollTop();
        $(window).scrollTop($(document).height());
        var scrollBottom = $(window).scrollTop();
        scrollBottom = parseFloat(scrollBottom);
        $(document).scrollTop(original);
        return parseFloat($(document).height()) - scrollBottom;
    }
    /*$('#price_from').change(function(event){
     alert("jji");
     event.preventDefault();
     event.stopPropagation();
     });*/
</script>
<!-- Grid Scroll Script By Sanjeev End -->