<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\walmart\models\WalmartProductFeedSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Manage Feed Details';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = MERCHANT_ID;
$urlError = \yii\helpers\Url::toRoute(['neweggproductfeed/errornewegg']);

$viewurl = \yii\helpers\Url::toRoute(['neweggproductfeed/viewfeed']);

$url = \yii\helpers\Url::toRoute(['neweggproductfeed/feeddetail']);
$link = \yii\helpers\Url::toRoute(['neweggproductfeed/file']);


?>
<?= Html::beginForm(['neweggproductfeed/bulkfeedstatus'], 'post',['id'=>'walmart_feed']);//Html::beginForm(['walmartproduct/bulk'],'post');    ?>
<div class="newegg-product-feed-index content-container" id = "main">
    <div class="form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style">Manage Feed Details</h1>
            </div>

            <div class="product-upload-menu">
                <button class="btn btn-primary " type="submit" title="Update Latest Feed">
                    <span>Update Latest Feed</span>
                </button>
            </div>
            <div class="clear"></div>
        </div>
        <div class="jet_notice" style="background-color: #FCF8E3;">

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

        <h1><?= Html::encode($this->title) ?></h1>


        <?= GridView::widget([
            'id' => "feed_grid",
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
            'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
                ['class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function ($data) {
                        return ['value' => $data['feed_id']];
                    },
                ],
   //      'id',
//            'merchant_id',
                'feed_id',
                'product_ids:ntext',
                'created_at',
                'status',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Action', 'headerOptions' => ['width' => '80'],
                    'template' => '{viewfeed}{file}{error}',
                    'buttons' => [

                        'viewfeed' => function ($viewurl, $data) {
                            $feed_id = $data['feed_id'];
                            if (($data['error'] != "") && !is_null($data['error'])) {
                                return Html::a(
                                    '<span class="fa fa-exclamation-circle upload-error"> </span>',
                                    'javascript:void(0)', ['data-pjax' => 0, 'onclick' => 'checkError(this.id)', 'title' => 'Upload Error', 'id' => $data['id']]
                                );
                            }
                            elseif($data['status']=='COMPLETED'){
                                  return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
                                    $viewurl, ['title' => 'View Feed Detail',]
                                );
                            }
                        }

                    ],

                ],
            ]
        ]); ?>
    </div>

</div>
<div class="modal-content" id="products_error" >
                
</div>
<style type="text/css">
     table.table-bordered tbody td {
    max-width: 317px;
    padding: 22px 15px;
    word-wrap: break-word;
}
</style>

<script type="text/javascript">
    var csrfToken = j$('meta[name=csrf-token]').attr("content");

    function clickView(feed_id) {
        var url = '<?= $viewurl ?>';
        j$.ajax({
            method: "post",
            url: url,
            data: {feed_id: feed_id, _csrf: csrfToken},
            success: function (response) {
                $('#content').append(response);
            }
        });
    }

    function selectPage(node) {
        var value = $(node).val();
        $('#feed_grid').children('select.form-control').val(value);
    }
    function checkError(id) {
        var url = '<?= $urlError ?>';
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
</script>
