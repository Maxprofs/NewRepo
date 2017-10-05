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
/* @var $searchModel frontend\modules\neweggmarketplace\models\NeweggOrderDetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Newegg Manufacturer  Details';
$this->params['breadcrumbs'][] = $this->title;
$viewManufacturer = \yii\helpers\Url::toRoute(['manufacturer/view']);
?>
<div class="newegg-order-detail-index content-section">
    <div class="form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu">
                <?= Html::a('CreateManufacturer', ['createmanufacturer'],['class' => 'btn btn-primary ','id'=>"sync-newegg-orders" ,'data-step'=>"2" ,'data-intro'=>"Create Manufacturer.", 'data-position'=>"left" ]); ?>
                <?= Html::a('SyncManufacturer', ['syncmanufacturer'],['class' => 'btn btn-primary ','id'=>"sync-newegg-orders" ,'data-step'=>"2" ,'data-intro'=>"Sync Manufacturer.", 'data-position'=>"left" ]); ?>
                
            </div>
            <div class="clear"></div>
        </div>


        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <div class="responsive-table-wrap">
            <?= GridView::widget([
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
                    ['class' => 'yii\grid\SerialColumn'],

//            'id',
//            'merchant_id',
//            'seller_id',
                    'manufacturer_name',
                    'manufacturer_url',
                    'manufacturer_support_email',
                    'manufacturer_support_phone',
                    'manufacturer_support_url',
                    'status',

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}{cancelorder}{shiporder}',
                        'buttons' => [
                            'view' => function ($viewNeweggOrderDetails, $model) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-eye-open"> </span>',
                                    $viewNeweggOrderDetails, ['title' => 'Manufacturer Detail on Newegg', 'id' => $model->id]);
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

       /* var introBulkAction = introJs().setOptions({
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

            ]
        });*/
        $('#bulk-action-help').click(function () {
            introBulkAction.start();
        });

    });

</script>
<?php $get = Yii::$app->request->get();
if(isset($get['tour'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            var productQuicktour = introJs().setOptions({
                doneLabel: 'Next page',
                showStepNumbers: false,
                exitOnOverlayClick: false,
            });

            productQuicktour.start().oncomplete(function() {
                window.location.href = '<?= \frontend\modules\neweggmarketplace\components\Data::getUrl("neweggconfiguration/index?tour") ?>';
            });
        });
    </script>
<?php endif; ?>
