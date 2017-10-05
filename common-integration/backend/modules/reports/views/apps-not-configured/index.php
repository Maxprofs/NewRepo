 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;

$this->title = 'Apps Not Configured ';
$this->params['breadcrumbs'][] = $this->title;
$arrAction=array('email'=>'Email');
?>
<div class="review-product-index">
<?=Html::beginForm(['apps-not-configured/mass'],'post',['id'=>'mass_action']);?>
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
                        'purchase_status',
                        [
                            'format'=>'raw',
                            'attribute' => 'install_status',
                            'value'=>function ($data)
                            {                    
                                if ($data['install_status'] == "1") 
                                    return "install";                    
                                else if ($data['install_status'] =="0") 
                                    return "uninstall";                    
                            },
                            'filter'=>["1"=>"install","0"=>"uninstall"],
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