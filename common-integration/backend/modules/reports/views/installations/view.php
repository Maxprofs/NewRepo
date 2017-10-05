<?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;
use common\models\JetOrderDetail;

$this->title = 'Apps Installed';
$this->params['breadcrumbs'][] = $this->title;
$arrAction=array('email'=>'Email','archieved-batch'=>'Archived','unarchieved-batch'=>'UnArchived');
?>
<div class="review-product-index">
<?=Html::beginForm(['installations/mass'],'post',['id'=>'mass_action']);?>
    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>

   <?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right','id'=>'jet_product_select'])?>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?php 
            
                echo GridView::widget([
                    'id'=>"jet_extention_details",
                    'dataProvider' => $model,
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
                                    'attribute'=>'shopurl',
                            ],
                            'email',
                            [
                                'label'=>'Under Review',
                                    'value' => function($data){
                                    $result=JetProduct::find()->where(['status'=>'Under Jet Review','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                            [
                                'label'=>'Live',
                                    'value' => function($data){
                                    $result=JetProduct::find()->where(['status'=>'Available for Purchase','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                                [
                                'label'=>'Completed Order',
                                    'value' => function($data){
                                    $result=JetOrderDetail::find()->where(['status'=>'complete','merchant_id'=>$data->merchant_id])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],

                            ['attribute'=>'status',
                                'filter'=>array("Not Purchase"=>"Not Purchased","Purchased"=>"Purchased"),

                            ],
                            ['attribute'=>'app_status',
                                'filter'=>array("install"=>"Installed","uninstall"=>"Uninstalled"),

                            ],
                            'expire_date',
                        
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