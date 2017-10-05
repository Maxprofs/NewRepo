 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;
use common\models\JetOrderDetail;
use yii\helpers\ArrayHelper;
use backend\modules\reports\models\JetExtensionDetail;

$this->title = 'Total Merchant ';
$this->params['breadcrumbs'][] = $this->title;
$arrAction=array('email'=>'Email','validate_sku'=>'Validate Sku','mail_chimp'=>'Mail Chimp');
?>
<div class="review-product-index">
<!--  -->

<?=Html::beginForm(['total-merchant/mass'],'post',['id'=>'mass_action']);?>

    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>
   <?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right','id'=>'mass_action_dropdown'])?>

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
                                    'label'=>'Shop URL',
                                    'attribute'=>'shop_url',
                            ],
                            'email',
                            'purchase_status',
                            'install_status',
                            
                            [
                                'label'=>'Completed Order',
                                    'value' => function($data){
                                    $result=JetOrderDetail::find()->where(['status'=>'complete','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                                [
                                'label'=>'Under Review',
                                    'value' => function($data){
                                    $result=JetProduct::find()->where(['status'=>'Under Jet Review','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                                  [
                                'label'=>'Live Products',
                                    'value' => function($data){
                                    $result=JetProduct::find()->where(['status'=>'Available for Purchase','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],

                        
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}{link}',
                            'buttons' => [
                                'link' => function ($url,$model,$key) {
                                    return '<a data-pjax="0" href="'.Yii::$app->urlManagerFrontEnd->createUrl(['site/managerlogin','ext'=>$model['merchant_id']]).'">Login as</a>'; 
                            },
                        
                            ],
                        ],
                    ],
                ]); 
    ?>
<?php Pjax::end(); ?>
</div>