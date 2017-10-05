 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;

$this->title = 'Free apss with revenue ';
$this->params['breadcrumbs'][] = $this->title;
$arrAction=array('email'=>'Email');
?>
<div class="review-product-index">
<?=Html::beginForm(['paid-no-revenue-no-live/mass'],'post',['id'=>'mass_action']);?>
    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>

   <?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right','id'=>'mass_action_dropdown'])?>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?php 
            
                echo GridView::widget([
                    'id'=>"jet_extention_details",
                    'dataProvider' => $dataProvider,
                    'filterModel'   => $searchModel,
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
                            [
                                'label'=>'Under Review',
                                    'value' => function($data){
                                    $result=JetProduct::find()->where(['status'=>'Under Jet Review','merchant_id'=>$data['merchant_id']])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                            [
                                'label'=>'Live',
                                    'value' => function($data){
                                    $result=JetProduct::find()->where(['status'=>'Available for Purchase','merchant_id'=>$data['merchant_id']])->all();
                                    return count($result);
                                },
                                'format'=>'raw',
                            ],
                            'complete_orders',
                            ['attribute'=>'purchase_status',
                                'filter'=>array("Not Purchase"=>"Not Purchased","Purchased"=>"Purchased"),

                            ],
                            ['attribute'=>'install_status',
                                'filter'=>array("install"=>"Installed","uninstall"=>"Uninstalled"),

                            ],
                            'expired_on',
                        
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