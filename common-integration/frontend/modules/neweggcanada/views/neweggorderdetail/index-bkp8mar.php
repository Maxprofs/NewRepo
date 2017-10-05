<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 1/2/17
 * Time: 3:06 PM
 */

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\neweggcanada\models\NeweggOrderDetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Newegg Order Details';
$this->params['breadcrumbs'][] = $this->title;
$viewNeweggOrderDetails = \yii\helpers\Url::toRoute(['neweggorderdetail/view']);
$cancelNeweggOrderDetails = \yii\helpers\Url::toRoute(['neweggorderdetail/cancelorder']);
$ship = \yii\helpers\Url::toRoute(['neweggorderdetail/shiporder']);

?>
<div class="newegg-order-detail-index content-section">
    <?= Html::beginForm(['neweggorderdetail/orderdetails'], 'post'); ?>
    <div class="form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet" title="Need Help" target="_blank"
                   href="<?= Yii::$app->request->baseUrl; ?>/newegg-marketplace/sell-on-newegg#sec6"></a>
            </div>
            <div class="product-upload-menu">
                <?php $arrAction = array('0' => 'Unshipped', '1' => 'Partially Shipped', '2' => 'Shipped', '3' => 'Invoiced', '4' => 'Voided'); ?>
                <?= Html::dropDownList('action', '', $arrAction, ['class' => 'form-control ', 'id' => "fetch-newegg-orders", 'data-step' => "1", 'data-intro' => "Select the BULK ACTION you want to operate.", 'data-position' => "left"]) ?>
                <?= Html::submitButton('submit', ['class' => 'btn btn-primary ', 'id' => "sync-newegg-orders", 'data-step' => "2", 'data-intro' => "Submit the operated BULK ACTION.", 'data-position' => "left"]); ?>
                <span title="Need Help" class="help_jet white-bg pull-right" style="cursor:pointer;"
                      id="bulk-action-help"></span>


            </div>
            <div class="clear"></div>
        </div>
        <div class="jet_notice">

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
        <br>


        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <div class="responsive-table-wrap">
            <?= GridView::widget([
                'options' => ['class' => 'grid-view table-responsive'],
                'dataProvider' => $dataProvider,
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'filterModel' => $searchModel,
                'filterSelector' => "select[name='" . $dataProvider->getPagination()->pageSizeParam . "'],input[name='" . $dataProvider->getPagination()->pageParam . "']",
                'pager' => [
                    'class' => \liyunfang\pager\LinkPager::className(),
                    'pageSizeList' => [25, 50, 100],
                    'pageSizeOptions' => ['class' => 'form-control', 'style' => 'display: none;width:auto;margin-top:0px;'],
                    'maxButtonCount' => 5,
                ],
                'summary' => '<div class="summary clearfix">
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
                                    <span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span>
                                </div>
                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                                    <div class="bulk-action-wrapper">
                                        <a href="' . Yii::$app->request->getBaseUrl() . "/neweggcanada/neweggorderdetail/index" . '" class="btn btn-primary reset-filter">Reset</a>
                                    </div>
                                </div>
                             </div>',

                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

            /*'id',
            'merchant_id',
            'seller_id',*/
                    'order_number',
                    [
                        'attribute' => 'sku',
                        'label' => 'PRODUCT SKU',
                        'headerOptions' => ['width' => '80'],
                        'value' => function ($dataProvider) {
                            return $dataProvider['sku'];
                        },

                    ],
                    'shopify_order_name',
                    /*'order_status_description',*/
                    [
                        'attribute' => 'order_status_description',
                        'label' => 'Order Status Description',
                        'headerOptions' => ['width' => '80'],
                        'filter' => ["Unshipped" => "Unshipped", "Shipped" => "Shipped","Invoiced"=>"Invoiced","Partial Shipped"=>"Partial Shipped","Voided"=>"Voided"],
                        'value' => function ($dataProvider) {
                            return $dataProvider['order_status_description'];
                        },

                    ],
                    'invoice_number',
                    'order_date',
                    'order_error',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'ACTION', 'headerOptions' => ['width' => '80'],
                        'template' => '{view}{cancelorder}{shiporder}',
                        'buttons' => [
                            'view' => function ($viewNeweggOrderDetails, $model) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open view-order"> </span>',
                                    $viewNeweggOrderDetails, ['title' => 'Order detail on Newegg', 'id' => $model->id]);
                            },
                            'cancelorder' => function ($cancelNeweggOrderDetails, $model) {
                                if (($model->order_status_description == 'Unshipped') /*|| ($model->status=='inprogress' )*/) {

                                    return Html::a(
                                        '<span class="glyphicon glyphicon glyphicon-remove cancel-order"> </span>',
                                        $cancelNeweggOrderDetails . '&order_number=' . $model->order_number, ['title' => 'Cancel', 'pdsf_id' => $model->id, 'order_number' => $model->order_number]);
                                }
                            },
                            'shiporder' => function ($ship, $model) {
                                if (($model->order_status_description == 'Unshipped') /*|| ($model->status=='inprogress' )*/) {
                                    return Html::a(
                                        '<span class="fa fa-truck ship-order"></span>',
                                        $ship, ['data-pjax' => 0]);
                                }
                            }
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<div id="view_jet_order" style="display:none"></div>

<script>
    var introBulkAction = "";
    $(function () {

        var introBulkAction = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
            steps: [

                {
                    element: '#fetch-newegg-orders',
                    intro: " Fetch the order(s) that are created from newegg.",
                    position: 'bottom'
                },
                {
                    element: '#sync-newegg-orders',
                    intro: " Sync Order(s) from newegg.",
                    position: 'bottom'
                },
                {
                    element: '.view-order',
                    intro: "Click to view order detail",
                    position: 'bottom'
                },
                {
                    element: '.cancel-order',
                    intro: "Click to cancel particular order",
                    position: 'bottom'
                },
                {
                    element: '.ship-order',
                    intro: "Click to ship order",
                    position: 'bottom'
                }
            ]
        });
        $('#bulk-action-help').click(function () {
            introBulkAction.start();
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
                    window.location.href = '<?= \frontend\modules\neweggcanada\components\Data::getUrl("neweggconfiguration/index?tour") ?>';
                });
            },1000);
        });
    </script>
<?php endif; ?>
<style>
    table.table-bordered tbody td {
        max-width: 235px;
        padding: 22px 15px;
        word-wrap: break-word;
    }
</style>