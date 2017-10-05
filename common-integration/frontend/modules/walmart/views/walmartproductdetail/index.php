<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 24/3/17
 * Time: 5:54 PM
 */
?>
<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Walmart Product';
$merchant_id = MERCHANT_ID;
$urlWalmart = \yii\helpers\Url::toRoute(['walmartproductdetail/viewproduct']);
$urlRetireWalmart = \yii\helpers\Url::toRoute(['walmartproductdetail/retireproduct']);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-product-index content-section ced-manageproduct">
    <div class="form new-section">
        <div class="jet-pages-heading walmart-title">

            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu m-menu confirmbox">
                <?= Html::a('Refresh Catalog', ['index?refresh=true'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Retire All Product(s)', ['ajax-bulk-update?retire=all'], ['class' => 'btn btn-primary']) ?>
            </div>

            <div class="product-upload-menu confirmbox">
                <?= Html::a('Refresh Catalog', ['index?refresh=true'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Retire All Product(s)', ['ajax-bulk-update?retire=all'], ['class' => 'btn btn-primary']) ?>
                <?/*= Html::a('Update Inventory', ['ajax-bulk-update?inventory=all'], ['class' => 'btn btn-primary noconfirmbox']) */?>
                <?= Html::Button('Update Inventory', ['class' => 'btn btn-primary noconfirmbox', 'onclick' => 'updateinventory()']); ?>

            </div>

            <div class="clear"></div>

        </div>
        <?= Html::beginForm(['walmartproductdetail/ajax-bulk-update'], 'post', ['id' => 'jet_bulk_product']);//Html::beginForm(['walmartproduct/bulk'],'post');        ?>


        <? /*= GridView::widget([
                'dataProvider' => $Provider,
                'filterModel' => $searchModel,
                'columns' => [
//                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'SKU',
                        'filter' => '<input class="form-control" name="SKU" value="'. $searchModel['SKU'] .'" type="text">',
                        'value' => 'SKU'
                    ],
                    [
                        'attribute' => 'PRIMARY IMAGE URL',
                        'format' => 'html',
                        'label' => 'PRIMARY IMAGE URL',
                        'value' => function($data){
                            if (count(explode(',', $data['PRIMARY IMAGE URL'])) > 0) {
                                $images = [];
                                $images = explode(',', $data['PRIMARY IMAGE URL']);
                                return Html::img($images[0],
                                    ['width' => '80px', 'height' => '80px']);
                            } else {
                                return Html::img($data['jet_product']['image'],
                                    ['width' => '80px', 'height' => '80px']);
                            }
                        }
                    ],
                    [
                        'attribute' => 'PRODUCT NAME',
                        'filter' => '<input class="form-control" name="PRODUCT NAME" value="'. $searchModel['PRODUCT NAME'] .'" type="text">',
                        'value' => 'PRODUCT NAME',
                    ],
                    [
                        "attribute" => "PRODUCT CATEGORY",
                        'filter' => '<input class="form-control" name="PRODUCT CATEGORY" value="'. $searchModel['PRODUCT CATEGORY'] .'" type="text">',
                        'value' => 'PRODUCT CATEGORY',
                    ],
                    [
                        "attribute" => "PRICE",
                        'filter' => '<input class="form-control" name="PRICE" value="'. $searchModel['PRICE'] .'" type="text">',
                        'value' => 'PRICE',
                    ],[
                        "attribute" => "INVENTORY COUNT",
                        'filter' => '<input class="form-control" name="INVENTORY COUNT" value="'. $searchModel['INVENTORY COUNT'] .'" type="text">',
                        'value' => 'INVENTORY COUNT',
                    ],
                    [
                        "attribute" => "PUBLISH STATUS",
                        'filter' => '<input class="form-control" name="PUBLISH STATUS" value="'. $searchModel['PUBLISH STATUS'] .'" type="text">',
                        'value' => 'PUBLISH STATUS',
                    ],
                    [
                        "attribute" => "UPC",
                        'filter' => '<input class="form-control" name="UPC" value="'. $searchModel['UPC'] .'" type="text">',
                        'value' => 'UPC',
                    ]
                ],
            ]);
            */ ?>
        <?php
        $bulkActionSelect = Html::dropDownList('action', null, ['' => '-- select bulk action --', 'retireproduct' => 'Retire Product', 'update_inventory' => 'Update Inventory'], ['id' => 'jet_product_select', 'class' => 'form-control']);
        $bulkActionSubmit = Html::Button('submit', ['class' => 'btn btn-primary', 'onclick' => 'validateBulkAction()', 'data-step' => '3', 'data-intro' => "Submit the operated BULK ACTION.", 'data-position' => 'bottom']);

        ?>
        <?php Pjax::begin(['timeout' => 30000, 'clientOptions' => ['container' => 'pjax-container']]); ?>

        <?= GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'allModels' => $items,
                'sort' => [
                    'attributes' => $searchAttributes,
                ],
            ]),
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
            'filterModel' => $searchModel,
            'summary' => '<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><div class="bulk-action-wrapper">' . $bulkActionSelect . $bulkActionSubmit . '<span title="Need Help" class="help_jet white-bg" style="cursor:pointer;" id="instant-help"></span></div></div></div>',
            'columns' => array_merge(

                $searchColumns, [
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'ACTION', 'headerOptions' => ['width' => '80'],
                        'template' => '{view}{cancel}',
                        'buttons' => [
                            'view' => function ($url, $items) {
                                $options = ['data-pjax' => 0, 'onclick' => 'clickView(this.id)', 'title' => 'View Product Detail', 'id' => $items['SKU']];

                                return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open walmart-data"> </span>',
                                    'javascript:void(0)', $options
                                );
                            },
                            'cancel' => function ($url, $items) {
                                $options = ['data-pjax' => 0, 'onclick' => 'retire(this.id)', 'title' => 'View Product Detail', 'id' => $items['SKU']];

                                return Html::a(
                                    '<span class="glyphicon glyphicon-remove-circle"> </span>',
                                    'javascript:void(0)', $options
                                );
                            }
                        ],
                    ],
                ]
            )
            /* 'columns' => $searchColumns,*/
        ]); ?>
        <?php Pjax::end(); ?>


    </div>
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
<div id="view_walmart_product_detail" style="display:none">
</div>
<div id="update_inventory" style="display:none">
    <div class="container">
        <!-- Modal -->
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h2 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Enter
                        Inventory for selected products</h2>
                    </div>
                    <div class="modal-body">
                        <form action="walmartproductdetail/ajax-bulk-action" method="post">
                            <input type="text" name="update_inventory" required="required"/>
                            <input type="button" id="update_qty" value="button"/>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
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
                j$('#LoadingMSG').hide();
                j$('#view_walmart_product_detail').html(msg);
                j$('#view_walmart_product_detail').css("display", "block");
                $('#view_walmart_product_detail #myModal').modal('show');
            });
    }

    function retire(sku) {

        var url = '<?= $urlRetireWalmart ?>';
        var merchant_id = '<?= $merchant_id;?>';
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
                j$('#LoadingMSG').show();
                j$.ajax({
                    method: "post",
                    url: url,
                    dataType: 'json',
                    data: {id: sku, merchant_id: merchant_id, _csrf: csrfToken},
                    success: function (json) {
                        j$('#LoadingMSG').hide();
                        if (json.success) {
                            alert(json.success);
                        }
                        if (json.error) {
                            alert(json.error);
                        }
                    }
                });
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
            else if (action == 'retireproduct') {
                $('body').attr('data-cnfrm', 'show');

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
            else if (action == 'update_inventory') {
                $('body').attr('data-cnfrm', 'show');
                $('#update_inventory').css("display", "block");

                if ($('body').attr('data-cnfrm') == 'show') {
                    $('#update_inventory #myModal').modal('show');
                    $('#update_qty').on('click',function () {
                        $("#jet_bulk_product").submit();
                    });
                    $('body').removeAttr('data-cnfrm');
                }
            }
            else {
                $("#jet_bulk_product").submit();
                //return true;
            }
        }
    }

    function updateinventory(){
        $('body').attr('data-cnfrm', 'show');
        $('#update_inventory').css("display", "block");

        if ($('body').attr('data-cnfrm') == 'show') {
            $('#update_inventory #myModal').modal('show');
            $('#update_qty').on('click',function () {
                $("#jet_bulk_product").submit();
            });
            $('body').removeAttr('data-cnfrm');
        }
    }
</script>