<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\walmart\components\Data;

$this->title = 'Manage Products';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = MERCHANT_ID;
$urlWalmart = \yii\helpers\Url::toRoute(['walmartproduct/getwalmartdata']);
$urlWalmartEdit = \yii\helpers\Url::toRoute(['walmartproduct/editdata']);
$urlWalmartError = \yii\helpers\Url::toRoute(['walmartproduct/errorwalmart']);
$urlGetTax = \yii\helpers\Url::toRoute(['walmartproduct/gettaxcode']);
$formPost = \yii\helpers\Url::toRoute(['walmartproduct/syncproductstore']);
?>
<style>
    /*.ced-survey {
        background-color: #1A75CF;
        display: inline-block;
        width: 60%;
        color: #fff;
        font-size: 12px;
        padding: 1px 10px;
        margin-left: 15px;
    }*/
    .list-page {
        width: 24%;
        float: right;
        text-align: right;
    }

    /*.ced-survey a{
      float: right;
      color: #fff;
      text-decoration: underline;
    }*/
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
</style>

<div class="jet-product-index content-section ced-manageproduct">
    <div class="form new-section">
        <?= Html::beginForm(['walmartproduct/ajax-bulk-upload'], 'post', ['id' => 'jet_bulk_product']);//Html::beginForm(['walmartproduct/bulk'],'post');     ?>
        <div class="jet-pages-heading walmart-title">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet"
                   href="<?= Yii::$app->request->baseUrl ?>/walmart/sell-on-walmart#sec5"
                   target="_blank" title="Need Help"></a>
            </div>

            <div class="product-upload-menu m-menu confirmbox">

                <?= Html::a('Update Price', ['updateprice'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) price on walmart.', 'class' => 'btn btn-primary']) ?>
                <?= Html::a('Update Inventory', ['updateinventory'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) inventory on walmart.', 'class' => 'btn btn-primary']) ?>
                <?= Html::a('Get Product Status', ['batchproductstatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) status from walmart.', 'class' => 'btn btn-primary']) ?>

                <button type="button" class="btn btn-primary noconfirmbox" id="sync_with_btn" data-toggle='tooltip' ,
                        title='Sync product(s) inventory , Price and Barcode from shopify.' , onclick="cnfrmSync()">Sync
                    With Shopify
                </button>
                <?= Html::a('Get Promo Price Status', ['getpromostatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) promo price status from walmart.', 'class' => 'btn btn-primary']) ?>
                <?= Html::a('Validate Product(s)', ['walmartvalidate/index'], ['target' => '_blank', 'data-toggle' => 'tooltip', 'title' => 'Validate Product(s) as per Walmart Requirements.', 'class' => 'btn btn-primary']) ?>


            </div>

            <div class="product-upload-menu confirmbox">

                <?= Html::a('Update Price', ['updateprice'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) price on walmart.',  'class' => 'btn btn-primary']) ?>
                <?= Html::a('Update Inventory', ['updateinventory'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) inventory on walmart.',  'class' => 'btn btn-primary']) ?>
                <?= Html::a('Get Product Status', ['batchproductstatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) status from walmart.', 'class' => 'btn btn-primary']) ?>
                <?php /*echo Html::a('Upload Products', ['uploadallproducts'], ['data-toggle' => 'tooltip', 'title' => 'Submit all Products to Walmart.', 'class' => 'btn btn-primary'])*/ ?>
                <?= Html::a('Update Price', ['updateprice'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) price on walmart.', 'data-step' => '7', 'data-position' => 'top', 'data-intro' => 'Sync product(s) price on walmart.', 'class' => 'btn btn-primary']) ?>
                <?= Html::a('Update Inventory', ['updateinventory'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) inventory on walmart.', 'data-step' => '8', 'data-position' => 'top', 'data-intro' => 'Sync product(s) inventory on walmart.', 'class' => 'btn btn-primary']) ?>
                <?= Html::a('Get Product Status', ['batchproductstatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) status from walmart.', 'data-step' => '9', 'data-position' => 'top', 'data-intro' => 'Get product(s) status from walmart.', 'class' => 'btn btn-primary']) ?>

                <!--<button type="button" class="btn btn-primary noconfirmbox" id="sync_with_btn" data-toggle = 'tooltip', title = 'Sync product(s) inventory , Price and Barcode from shopify.', data-step = '11', data-position = 'top', data-intro ='Sync store products' onclick="cnfrmSync()">Sync With
                    Shopify
                </button>-->

                <span class="pop_up" id="view_more_options" data-step ='7' data-position = 'top' data-intro = 'View More' >view more</span>
                <div class="popup-box confirmbox">
                    <?= Html::a('Update Price', ['updateprice'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) price on walmart.', 'data-step' => '8', 'data-position' => 'top', 'data-intro' => 'Sync product(s) price on walmart.', 'class' => 'btn btn-primary','id'=>'update-price']) ?>

                    <?= Html::a('Update Inventory', ['updateinventory'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) inventory on walmart.','data-step' => '9', 'data-position' => 'top', 'data-intro' => 'Sync product(s) inventory on walmart.','class' => 'btn btn-primary']) ?>

                    <?= Html::a('Get Product Status', ['batchproductstatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) status from walmart.','data-step' => '10', 'data-position' => 'top', 'data-intro' => 'Get product(s) status from walmart.', 'class' => 'btn btn-primary']) ?>
                    <button type="button" class="btn btn-primary noconfirmbox" id="sync_with_btn" data-step ='11' data-position = 'top' data-intro = 'Sync Product from shopify' data-toggle='tooltip'
                            , title='Sync product(s) inventory , Price and Barcode from shopify.' ,
                            onclick="cnfrmSync()">Sync With
                        Shopify
                    </button>
                    <?= Html::a('Get Promo Price Status', ['getpromostatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) promo price status from walmart.', 'data-step' => '12', 'data-position' => 'top', 'data-intro' => 'Get product(s) promo price status from walmart.', 'class' => 'btn btn-primary']) ?>
                    <?= Html::a('Validate Product(s)', ['walmartvalidate/index'], ['target' => '_blank', 'data-toggle' => 'tooltip', 'title' => 'Validate Product(s) as per Walmart Requirements.','data-step' => '13', 'data-position' => 'top', 'data-intro' => 'Click here to validate product catalog',  'class' => 'btn btn-primary']) ?>

                <span class="pop_up" id="view_more_options">view more</span>
                <div class="popup-box confirmbox">
                    <?php /*echo Html::a('Upload Products', ['uploadallproducts'], ['data-toggle' => 'tooltip', 'title' => 'Submit all Products to Walmart.', 'class' => 'btn btn-primary'])*/ ?>
                    <?= Html::a('Update Price', ['updateprice'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) price on walmart.', 'class' => 'btn btn-primary']) ?>

                    <?= Html::a('Update Inventory', ['updateinventory'], ['data-toggle' => 'tooltip', 'title' => 'Sync product(s) inventory on walmart.', 'class' => 'btn btn-primary']) ?>

                    <?= Html::a('Get Product Status', ['batchproductstatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) status from walmart.', 'class' => 'btn btn-primary']) ?>
                    <button type="button" class="btn btn-primary noconfirmbox" id="sync_with_btn" data-toggle='tooltip'
                            , title='Sync product(s) inventory , Price and Barcode from shopify.' , onclick="cnfrmSync()">Sync With
                        Shopify
                    </button>
                    <?= Html::a('Get Promo Price Status', ['getpromostatus'], ['data-toggle' => 'tooltip', 'title' => 'Get product(s) promo price status from walmart.', 'class' => 'btn btn-primary']) ?>
                    <?= Html::a('Validate Product(s)', ['walmartvalidate/index'], ['target' => '_blank', 'data-toggle' => 'tooltip', 'title' => 'Validate Product(s) as per Walmart Requirements.', 'class' => 'btn btn-primary']) ?>

                    <div>
                    </div>

                </div>

            </div>

            <div class="clear"></div>
        </div>
        <div class="jet_notice" style="background-color: #FCF8E3;">
    <span class="font_bell">
      <i class="fa fa-list" aria-hidden="true"></i>
        <!-- <i class="fa fa-bell fa-1x"></i> -->
    </span>
            Don't see all of your products? Just click <a
                    href="<?= yii\helpers\Url::toRoute('categorymap/index'); ?>">here</a> to map all shopify product
            type(s) with walmart category.
            <div class="list-page" style="float:right">
                Show per page
                <select onchange="selectPage(this)" class="form-control"
                        style="display: inline-block; width: auto; margin-top: 0px; margin-left: 5px; margin-right: 5px;"
                        name="per-page">
                    <option value="25" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 25) {
                        echo "selected=selected";
                    } ?>>25
                    </option>
                    <option value="50" <?php if(!isset($_GET['per-page']) || (isset($_GET['per-page']) && $_GET['per-page'] == 50)) {
                        echo "selected=selected";
                    } ?>>50
                    </option>
                    <option value="100" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 100) {
                        echo "selected=selected";
                    } ?> >100
                    </option>
                    <option value="200" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 200) {
                        echo "selected=selected";
                    } ?> >200
                    </option>
                    <option value="500" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 500) {
                        echo "selected=selected";
                    } ?> >500
                    </option>
                    <option value="1000" <?php if (isset($_GET['per-page']) && $_GET['per-page'] == 1000) {
                        echo "selected=selected";
                    } ?> >1000
                    </option>
                </select>
                Items
            </div>
            <div style="clear:both"></div>
        </div>
        <?php
        $errorActionFlag = false;
        $editActionFlag = false;
        $imageActionFlag = false;
        $viewActionFlag = false;
        $shipActionFlag = false;
        $bulkActionSelect = Html::dropDownList('action', null, ['' => '-- select bulk action --', 'batch-upload' => 'Upload Product', 'batch-promotion-price' => 'Upload Promotional Price', 'batch-product-status' => 'Update Product Status', 'batch-retire' => 'Retire Product', 'batch-update-price' => 'Update Price', 'batch-update-inventory' => 'Update Inventory'], ['id' => 'jet_product_select', 'class' => 'form-control', 'data-step' => '2', 'data-intro' => "Select the BULK ACTION you want to operate.", 'data-position' => 'bottom']);
        $bulkActionSubmit = Html::Button('submit', ['class' => 'btn btn-primary', 'onclick' => 'validateBulkAction()', 'data-step' => '3', 'data-intro' => "Submit the operated BULK ACTION.", 'data-position' => 'bottom']);
        ?>
        <?php Pjax::begin(['timeout' => 30000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
        <?= GridView::widget([
            'id' => "product_grid",
            'options' => ['class' => 'table-responsive'],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterSelector' => "select[name='" . $dataProvider->getPagination()->pageSizeParam . "'],input[name='" . $dataProvider->getPagination()->pageParam . "']",
            'pager' => [
                'class' => \liyunfang\pager\LinkPager::className(),
                'pageSizeList' => [25, 50, 100, 200, 500, 1000],
                'pageSizeOptions' => ['class' => 'form-control', 'style' => 'display: none;width:auto;margin-top:0px;'],
                'maxButtonCount' => 5,
            ],
            'summary' => '<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><div class="bulk-action-wrapper">' . $bulkActionSelect . $bulkActionSubmit . '<a href="' . Yii::$app->request->getBaseUrl() . "/walmart/walmartproduct/index" . '" class="btn btn-primary reset-filter">Reset</a><span title="Need Help" class="help_jet white-bg" style="cursor:pointer;" id="instant-help"></span></div></div></div>',
            'columns' => [
                // ['class' => 'yii\grid\SerialColumn'],
                //['class' => 'yii\grid\CheckboxColumn'],
                ['class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function ($data) {
                        return ['value' => $data['product_id'], 'class' => 'bulk_checkbox'];
                    },
                    'headerOptions' => ['id' => 'checkbox_header', 'data-step' => '1', 'data-intro' => "Select Products to Upload.", 'data-position' => 'right']
                ],
                'product_id',
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
                /* [
                     'attribute' => 'title',
                     'label' => 'Title',
                     'value' => 'jet_product.title',
                 ],*/
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
                    'attribute' => 'qty',
                    'label' => 'Quantity',
                    'value' => 'jet_product.qty',
                ],
                /*[
                    'attribute' => 'price',
                    'label' => 'Price',
                    'format' => 'html',
                    'value' => function ($data) {
                        $html = '';
                        $html = Html::a(
                                'Repricing',
                                Data::getUrl('walmart-reprice/edit')."?id=".$data['product_id'],
                                []
                            );
                        if ($data['product_price']) {
                            return '<span style="display:block;">'.$data['product_price'].'</span>'.'&nbsp;&nbsp;'.$html;
                        } else {
                            return '<span style="display:block;">'.$data['jet_product']['price'].'</span>'.'&nbsp;&nbsp;'.$html;
                        }
                    },
                ],*/
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
                        /*if($data['option_variants_count'] != null && $data['jet_product']['type']!='simple') {
                            return $data['jet_product']['type'] . '[' . $data['option_variants_count'] . ']';
                        }else{*/
                        return $data['jet_product']['type'];
                        /*}*/
                    },

                ],
                /* [
                     'attribute' => 'status',
                     'label' => 'Status',
                     'headerOptions' => ['width' => '160'],
                     'filter' => ["Item Processing" => "Item Processing", "Not Uploaded" => "Not Uploaded", "PUBLISHED" => "PUBLISHED", "STAGE" => "STAGE", "UNPUBLISHED" => "UNPUBLISHED", "other" => "Other Products"],
                     'value' => function ($data) {
                         return $data['status'];
                     }
                 ],*/
                [
                    'attribute' => 'status',
                    'label' => 'Status',
                    'headerOptions' => ['width' => '160'],
                    'filter' => ["Item Processing" => "Item Processing", "Not Uploaded" => "Not Uploaded", "PUBLISHED" => "PUBLISHED", "STAGE" => "STAGE", "UNPUBLISHED" => "UNPUBLISHED", "other" => "Other Products"],
                    'format' => 'html',
                    /*'value' => function ($data) {
                        if($data['option_status'] != null){
                            $status = explode(',',$data['option_status']);
                            $value =array_count_values($status);

                            if(!empty($value)){
                                return Html::renderTagAttributes($value);
                            }
                        }
                        return $data['status'];
                    }*/
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
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'ACTION', 'headerOptions' => ['width' => '80'],
                    'template' => '{update}{view}{link}{errors}',
                    'buttons' => [

                        'update' => function ($url, $model) use (&$editActionFlag) {
                            $options = ['data-pjax' => 0, 'onclick' => 'clickEdit(this.id)', 'title' => 'Edit product', 'id' => $model->id, 'product_id' => $model->product_id];
                            if (!$editActionFlag) {
                                $editActionFlag = true;
                                $options['data-step'] = '4';
                                $options['data-intro'] = "Edit Product Information.";
                                $options['data-position'] = 'left';
                            }
                            return Html::a(
                                '<span class="glyphicon glyphicon glyphicon-pencil"> </span>',
                                'javascript:void(0)', $options);
                        },
                        'view' => function ($url, $model) use (&$viewActionFlag) {
                            if ($model->status == "PUBLISHED") {
                                $options = ['data-pjax' => 0, 'onclick' => 'clickView(this.id)', 'title' => 'View Product Detail', 'id' => $model->jet_product->sku];
                                if (!$viewActionFlag) {
                                    $viewActionFlag = true;
                                    $options['data-step'] = '6';
                                    $options['data-intro'] = "View Product Information.";
                                    $options['data-position'] = 'left';
                                }
                                return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
                                    'javascript:void(0)', $options
                                );
                            }
                        },
                        'errors' => function ($url, $model) use (&$errorActionFlag) {
                            if (($model->error != "") && !is_null($model->error)) {
                                $options = ['data-pjax' => 0, 'onclick' => 'checkError(this.id)', 'title' => 'Upload Error', 'id' => $model->id];
                                if (!$errorActionFlag) {
                                    $errorActionFlag = true;
                                    $options['data-step'] = '5';
                                    $options['data-intro'] = "Click Here to get Errors during the Uploading of this product.";
                                    $options['data-position'] = 'left';
                                }
                                return Html::a(
                                    '<span class="fa fa-exclamation-circle upload-error"> </span>',
                                    'javascript:void(0)', $options
                                );
                            }
                        }
                    ],
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
        <?= Html::endForm() ?>
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
                        Welcome! to Walmart Products Import Section
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
                        <h4>Click to import Shopify store products to Walmart-Integration App<h4>
                                <a href="<?php echo \yii\helpers\Url::toRoute(['walmartproduct/batchimport']) ?>"
                                   class="btn">Import Products</a>
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
    function validateBulkAction() {
        var action = $('#jet_product_select').val();
        if (action == '') {
            alert('Please Select Bulk Action');
            //return false;
        } else {
            if ($("input:checkbox:checked.bulk_checkbox").length == 0) {
                alert('Please Select Products Before Submit.');
                //return false;
            }
            else if (action == 'batch-retire') {
                $('body').attr('data-cnfrm', 'show');
                $('#edit-modal-close').click();

                if ($('body').attr('data-cnfrm') == 'show') {
                    $('#confirm').modal('show');
                    $('body').removeAttr('data-cnfrm');
                }

                $("#confirm").on('shown.bs.modal', function () {
                    $('#cnfrm-yes').unbind('click');
                    $('#cnfrm-yes').on('click', function () {
                        $('#cnfrm-no').click();
                        $("#jet_bulk_product").submit();
                    });
                });
            }
            else {
                $("#jet_bulk_product").submit();
                //return true;
            }
        }
    }


    $(".pop_up").click(function () {
        $(".popup-box").toggle("slow");
    });

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
        var url = '<?= $urlWalmartEdit; ?>';
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

    function reloadEditModal() {
        $("#price-edit #price-edit-modal").on('hidden.bs.modal', function () {
            $('#edit_walmart_product #myModal').modal('show');
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
    function getTax(id) {
        var url = '<?= $urlGetTax ?>';
        j$('#LoadingMSG').show();
        j$.ajax({
            method: "post",
            url: url,
            data: {id: id, _csrf: csrfToken}
        })
            .done(function (msg) {
                //console.log(msg);
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

    /*$(function () {
     var intro = introJs().setOptions();
     $(document).on('click', '#instant-help', function () {
     intro.start();
     });
     });*/
    $(document).on('click', '#instant-help', function () {
        var productQuicktour = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
            doneLabel: 'Done',
            /*steps: [
             {
             element: '#update-price',
             intro: ' Click here to export the CSV of all the products that are availabe on the app.',
             position: 'left'
             },{
             element: '#browse',
             intro: "Click here to browse the CSV file that you have updated or edited.",
             position: 'left'
             },
             {
             element: '#import',
             intro: "Click here to import the CSV file that you have browsed.",
             position: 'left'
             }
             ]*/

        });
        productQuicktour.onchange(function (targetElement) {
            if (targetElement.id == "view_more_options") {
                showPopupBox(1);
            }
        });
        productQuicktour.start().oncomplete(function () {
            showPopupBox(0);

        }, 1000);
    });

    function showPopupBox(toggle){
        if(toggle==1)
            $(".popup-box").toggle("slow");
        else
            $(".popup-box").toggle("hide");
    }
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
            productQuicktour.onchange(function (targetElement) {
                if (targetElement.id == "view_more_options") {
                    showPopupBox(1);
                }
            });
            setTimeout(function () {
                productQuicktour.start().oncomplete(function () {
                    showPopupBox(0);
                    window.location.href = '<?= Data::getUrl("walmartorderdetail/index?tour") ?>';
                }, 1000);
            });

            /*setTimeout(function () {
                productQuicktour.start(function(targetElement){

                    if(targetElement.id=="view_more_options"){
                        showPopupBox(1);
                    }
                }).oncomplete(function() {
                    window.location.href = '<?= Data::getUrl("walmartorderdetail/index?tour") ?>';
                },1000);
            });

            function showPopupBox(toggle){
                if(toggle==1)
                    $(".popup-box").toggle("slow");
                else
                    $(".popup-box").toggle("hide");
            }*/
        });
        function showPopupBox(toggle){
            if(toggle==1)
                $(".popup-box").toggle("slow");
            else
                $(".popup-box").toggle("hide");
        }
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

<style>
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
</style>
