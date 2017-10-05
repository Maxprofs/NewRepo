<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\walmart\components\Data;
/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\integration\models\WalmartOrderImportErrorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$viewWalmartOrderDetails = \yii\helpers\Url::toRoute(['walmartorderdetail/vieworderdetails']);

$this->title = 'Walmart Order Import Errors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="walmart-order-import-error-index content-section">
    <div class="form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu">
            </div>
            <div class="clear"></div>
        </div>
        <div class="responsive-table-wrap">
            <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
            <?= GridView::widget([
                'options' => ['class' => 'table-responsive'],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    //['class' => 'yii\grid\SerialColumn'],

                    'id',
                    'purchase_order_id',
                    //'merchant_id',
//                    'reason:ntext',
                    [
                        'attribute' => 'reason',
                        'label' => 'REASON',
                        'value' => function ($data){
                            $pos = 0;
                            $pos = strpos($data['reason'],'{');
                            if($pos){

                                $firststring = substr($data['reason'],0,$pos);
                                $laststring = substr(($data['reason']),$pos,strlen($data['reason']));
                                $error = json_decode($laststring,true);
                                $error_description = $error['error']['description'];
                                $pos = 0;
                                return $firststring .' - ' .  $error_description;
                            }else{
                                return $data['reason'];
                            }

                        }
                    ],
                    'created_at',

                    ['class' => 'frontend\modules\walmart\components\Grid\OrderDetails\ErrorAction'],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
    <div id='view_walmart_order'></div>
</div>
<script>
function checkorderstatus(purchase_order_id) {
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var url = '<?= $viewWalmartOrderDetails; ?>';
            $('#LoadingMSG').show();
            $.ajax({
                method: "post",
                url: url,
                data: {purchase_order_id: purchase_order_id, _csrf: csrfToken}
            })
                .done(function (msg) {
                    $('#LoadingMSG').hide();
                    $('#view_walmart_order').html(msg);
                    $('#view_walmart_order').css("display", "block");
                    $('#view_walmart_order #myModal').modal({
                        keyboard: false,
                        backdrop: 'static'
                    })
                })
        }
</script>
