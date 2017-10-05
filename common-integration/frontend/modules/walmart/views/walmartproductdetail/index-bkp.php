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
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-product-index content-section">
    <div class="form new-section">
        <div class="walmart-tax-codes-index">

            <h1><?= Html::encode($this->title) ?></h1>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
            <!-- --><? /*= GridView::widget([
                'options' => ['class'=>'grid-view table-responsive'],
                'dataProvider' => $Provider,
//                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    //'id',
                    'tax_code',
                    'cat_desc:ntext',
                    'sub_cat_desc:ntext',
                ],
            ]); */ ?>
            <?php Pjax::begin(['timeout' => 30000, 'clientOptions' => ['container' => 'pjax-container']]); ?>

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
            <?= GridView::widget([
                'dataProvider' => new \yii\data\ArrayDataProvider([
                    'allModels' => $items,
                    'sort' => [
                        'attributes' => $searchAttributes,
                    ],
                ]),
                'filterModel' => $searchModel,
                'columns' => array_merge(
                    $searchColumns, [
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACTION', 'headerOptions' => ['width' => '80'],
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $items){
                                    $options = ['data-pjax' => 0, 'onclick' => 'clickView(this.id)', 'title' => 'View Product Detail', 'id' => $items['SKU']];

                                    return Html::a(
                                        '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
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
</div>
<div id="view_walmart_product_detail" style="display:none">
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
</script>