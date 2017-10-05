<?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;

$this->title = 'Merchants Paid But No Revenue And No Live Products ';
$this->params['breadcrumbs'][] = $this->title;
$arrAction=array('email'=>'Email');
?>
<div class="review-product-index">
<?=Html::beginForm(['uninstallations/mass'],'post',['id'=>'mass_action']);?>
    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>

   <?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right','id'=>'mass_action_dropdown'])?>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?php 
            
                echo GridView::widget([
                    'id'=>"jet_extention_details",
                    'dataProvider' => $model,
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
                            'status',
                            'app_status',
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