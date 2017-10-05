 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\walmart\models\WalmartShopDetails;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartOrderDetail;

$this->title = 'Merchants Paid But No Revenue And With Live Products ';
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
                    'filterModel' => $search,
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
                                    $result=WalmartShopDetails::find()->where(['merchant_id'=>$data['merchant_id']])->one();
                                    return $result['shop_url'];
                                },
                                'format'=>'raw',
                            ],
                            
                            [
                                'label'=>'Email',
                                'value' => function($data){
                                    $result=WalmartShopDetails::find()->where(['merchant_id'=>$data['merchant_id']])->one();
                                    return $result['email'];
                                },
                                'format'=>'raw',
                            ],
                               [
                                'label'=>'Unpublished',
                                    'value' => function($data){
                                    $result=WalmartProduct::find()->where(['status'=>'UNPUBLISHED','merchant_id'=>$data['merchant_id']])->all();
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
                            'template' => '{link}',//{view}
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