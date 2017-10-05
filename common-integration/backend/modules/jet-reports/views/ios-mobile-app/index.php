 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;

$this->title = 'Mobile App Installed';
$this->params['breadcrumbs'][] = $this->title;
$arrAction=array('email'=>'Email');
?>
<div class="review-product-index">

<?php
 if(isset($check) && $check=='check'){
      echo Html::beginForm(['android-mobile-app/mass'],'post',['id'=>'mass_action']);
 }
 else{

   echo Html::beginForm(['ios-mobile-app/mass'],'post',['id'=>'mass_action']);
  }  ?>
    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>

   <?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right','id'=>'mass_action_dropdown'])?>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
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
                            'password',
                            'hash_key',
                            'shop_url',
                            ['attribute'=>'status',
                                'filter'=>array("pending"=>"Pending","Complete"=>"Completed"),

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