<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 1/2/17
 * Time: 4:05 PM
 */
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use frontend\modules\neweggmarketplace\models\NeweggProduct;

$this->title = 'Courtesy Refund Orders';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = MERCHANT_ID;
$courtesyrefund = \yii\helpers\Url::toRoute(['neweggorderdetail/courtesyrefund']);

?>
<div class="jet-product-index content-section">
    <div class="form new-section">

        <?= Html::beginForm(['order/courtesyrefund'], 'post'); ?>
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
             <!--    <a class="help_jet" title="Need Help" target="_blank"
                   href="<?= Yii::$app->request->baseUrl; ?>/walmart-marketplace/sell-on-walmart"></a> -->

            </div>
            <div class="product-upload-menu">
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
        <div class="responsive-table-wrap">

            <?= GridView::widget([
                'id' => "product_grid",
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterSelector' => "select[name='" . $dataProvider->getPagination()->pageSizeParam . "'],input[name='" . $dataProvider->getPagination()->pageParam . "']",

                'pager' => [
                    'class' => \liyunfang\pager\LinkPager::className(),
                    'pageSizeList' => [25, 50, 100],
                    'pageSizeOptions' => ['class' => 'form-control', 'style' => 'display: none;width:auto;margin-top:0px;'],
                    'maxButtonCount' => 5,
                ],
                'columns' => [
                    // ['class' => 'yii\grid\SerialColumn'],
                    //['class' => 'yii\grid\CheckboxColumn'],
                    ['class' => 'yii\grid\CheckboxColumn'],
                    'courtesy_refund_id',
                    'order_number',
                    'order_amount',
                    'invoice_number',
                    'refund_amount',
                    'reason',
                    'status',
                    'edit_date',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Action', 'headerOptions' => ['width' => '80'],
                        'template' => '{courtesyrefund}',
                        'buttons' => [
                            'courtesyrefund' => function ($courtesyrefund, $model) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open"> </span>',
                                    $courtesyrefund, ['title' => 'Order detail on Newegg', 'id' => $model->id]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>





