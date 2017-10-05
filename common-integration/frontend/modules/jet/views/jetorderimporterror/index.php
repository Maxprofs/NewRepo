<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Failed Orders';
$this->params['breadcrumbs'][] = $this->title;
$merchant_id = Yii::$app->user->identity->id;
$viewJetOrderDetails = \yii\helpers\Url::toRoute(['jetorderimporterror/vieworderdetails']);
$url = \yii\helpers\Url::toRoute(['jetorderimporterror/cancel']);

?>
<style>
    .modal-content {
        margin-top: 10%;
    }

    .table-bordered td {
         padding: 20px 28px !important;
         font-size: 14px;
     }
     .table-bordered th {
         font-size: 14px;
     }
     .jet-order-import-error-index tr td{
        text-align: center;
     }
</style>
<div class="jet-order-import-error-index content-section">
<div class="form new-section">
    <div class="jet-pages-heading">
        <div class="title-need-help">
            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="product-upload-menu">
            <?= Html::a('Reset Filter', ['jetorderimporterror/index'], [ 'data-toggle'=>'tooltip','title'=>'Click to reset all filters','data-position'=>'top','class' => 'btn btn-primary']) ?>
        </div>
        <div class="clear"></div>
    </div>

    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <div class="responsive-table-wrap">
        <?= GridView::widget([
            'id' => "ordererror_grid",
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                //['class' => 'yii\grid\CheckboxColumn'],
                [
                    'attribute' => 'reference_order_id',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Reference Order Id'],
                ],
                [
                    'attribute' => 'merchant_order_id',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Merchant Order Id'],
                ],
                [
                    'attribute' => 'reason',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Reason of Order Reject'],
                ],                
                [
                    'attribute'=>'status',
                    'label'=>'Status',
                    'headerOptions' => ['width' => '150','data-toggle'=>'tooltip1','title'=>'Status of order'],
                    'filter' => ["ready"=>"ready","canceled"=>"canceled"],
                    'value' => function($data){                    
                        return $data['status'];
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Created At'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{vieworder}{cancel}{link}',
                    'buttons' => [ 
                    	'vieworder' => function ($url,$model) 
                		{
                        	return Html::a(
                                '<span class="glyphicon glyphicon-eye-open"> </span>',
                                'javascript:void(0)',['title'=>'Order detail on Jet','data-pjax'=>0,'onclick'=>'checkorderstatus(this.id)','id'=>$model->merchant_order_id]);                        	
                		},                   	
                		'cancel' => function ($url,$model) 
                		{
                			if ($model->status =="ready") 
                			{
                            	return Html::a(
                                    '<span>Cancel</span>',
                                    'javascript:void(0)',['title'=>'Cancel Order on Jet','data-pjax'=>0,'onclick'=>'cancel(this.id)','id'=>$model->merchant_order_id]);
                        	}
                		},                    	                        
                    ],
                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end(); ?>
    <?= Html::endForm(); ?>
    </div>
</div>
<div id="view_jet_order" style="display:none"></div>

<script type="text/javascript">
    var submit_form = false;
    $('body').on('keyup', '.filters > td > input', function (event) {
        if (event.keyCode == 13) {
            if (submit_form === false) {
                submit_form = true;
                $("#ordererror_grid-filters").yiiGridView("applyFilter");
            }
        }

    });

    $("body").on('beforeFilter', "#ordererror_grid-filters", function (event) {
        return submit_form;
    });
    $("body").on('afterFilter', "#ordererror_grid-filters", function (event) {
        submit_form = false;
    });

    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    function checkorderstatus(merchant_order_id) {

        var url = '<?= $viewJetOrderDetails; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {merchant_id: merchant_id,merchant_order_id: merchant_order_id, _csrf: csrfToken}
        })
        .done(function (msg) {
            //console.log(msg);
            $('#LoadingMSG').hide();
            $('#view_jet_order').html(msg);
            $('#view_jet_order').css("display", "block");
            $('#view_jet_order #myModal').modal({
                keyboard: false,
                backdrop: 'static'
            })
        })
    }


    function cancel(merchant_order_id) 
    {
    	var res = confirm("Do you want to cancel this order on jet ?");
        if (!res) {
            return false;
        }
        var url = '<?= $url; ?>';
        var merchant_id = '<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {merchant_id: merchant_id,merchant_order_id: merchant_order_id, _csrf: csrfToken}
        })
        .done(function (msg) {
            console.log(msg);
        })
    }

    $(document).ready(function () {
        $('[data-toggle="tooltip1"]').tooltip({container: "th"});
    });
</script>