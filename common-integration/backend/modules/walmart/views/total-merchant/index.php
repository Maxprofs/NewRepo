 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;
use common\models\JetOrderDetail;
use yii\helpers\ArrayHelper;
use frontend\modules\walmart\models\WalmartShopDetails;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartOrderDetail;
use frontend\modules\walmart\models\WalmartExtensionDetail;

$this->title = 'Total Merchant ';
$this->params['breadcrumbs'][] = $this->title;
$mailchimplist = 
$arrAction=array('email'=>'Email','mailchimp'=>'Mail Chimp');
?>
<div class="review-product-index">
<!--  -->

<?=Html::beginForm(['total-merchant/mass'],'post',['id'=>'mass_action']);?>

    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>
   <?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right','id'=>'mass_action_dropdown'])?>
   <div id="register-list"></div>

    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= Html::a('<button type="button" class="btn btn-notification" id="csv-button">Export Csv</button>', ['csv','id'=>serialize($searchModel->getAttributes())], 
                        [
                            'title' => 'Download',
                            'data-pjax' => '0',
                        ]) ?>
    <?php 
                echo GridView::widget([
                    'id'=>"jet_extention_details",
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                            ['class' => 'yii\grid\CheckboxColumn',
                                'checkboxOptions' => function($data) 
                                {
                                    return ['value' => $data['merchant_id']];
                                },
                            ],
                            'merchant_id',
                                [
                                'label'=>'Shop Url',
                                    'value' => function($data){
                                    $result=WalmartShopDetails::find()->where(['merchant_id'=>$data->merchant_id])->one();
                                    return $result['shop_url'];
                                },
                                'format'=>'raw',
                            ],
                                [
                                'label'=>'Email',
                                    'value' => function($data){
                                    $result=WalmartShopDetails::find()->where(['merchant_id'=>$data->merchant_id])->one();
                                    return $result['email'];
                                },
                                'format'=>'raw',
                            ],
                                           [
                                'label'=>'Unpublished',
                                    'value' => function($data){
                                    $result=WalmartProduct::find()->where(['status'=>'UNPUBLISHED','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                            [
                                'label'=>'Published',
                                    'value' => function($data){
                                    $result=WalmartProduct::find()->where(['status'=>'PUBLISHED','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                                [
                                'label'=>'Order',
                                    'value' => function($data){
                                    $result=WalmartOrderDetail::find()->where(['status'=>'completed','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                             ['attribute'=>'status',
                                 'filter'=>ArrayHelper::map(WalmartExtensionDetail::find()->all(),'status','status'),
                                ],
                              ['attribute'=>'app_status',
                             'filter'=>ArrayHelper::map(WalmartExtensionDetail::find()->all(),'app_status','app_status'),

                            ],

                        
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{link}','{view}',
                            'buttons' => [
                                'link' => function ($url,$model,$key) {
                                    return '<a data-pjax="0" href="'.Yii::$app->urlManagerFrontEnd->createUrl(['walmart/site/managerlogin','ext'=>$model['merchant_id']]).'">Login as</a>'; 
                            },
                        
                            ],
                        ],
                    ],
                ]); 
    ?>
<?php Pjax::end(); ?>
</div>
<script type="text/javascript">
    $(document).on('change','#mass_action_dropdown',function(){
        var val = $(this).val();
        if(val=='mailchimp'){
            $.ajax({
            method: "POST",
            url: "<?= Yii::$app->request->baseUrl?>/walmart/mailchimp/index",
            data: { template : $(this).val() }
           }).done(function(data) {
                  var option_value = JSON.parse(data);
                var dropdown = "<select name='listData' class='form-control pull-right'>";
                dropdown += '<option value="">Please select list</option>';
                $.each(option_value, function(key1,value1) {
                    dropdown += '<option value="'+key1+'">'+value1+'</option>';
                });
                dropdown = dropdown+"</select>";
                $('#register-list').html(dropdown);
          });
        }
        else{
            $('#register-list').css('display','none');
        }

    });

</script>