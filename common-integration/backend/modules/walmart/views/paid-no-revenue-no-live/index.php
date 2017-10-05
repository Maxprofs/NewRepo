 <?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\WalmartShopDetails;


$this->title = 'Merchants Paid But No Revenue And No Live Products ';
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
                                'label'=>'Published',
                                    'value' => function($data){
                                        $sql = "SELECT count(*) as `count` FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=".$data['merchant_id']." AND `walmart_product`.`status`='".WalmartShopDetails::PRODUCT_STATUS_UPLOADED."' AND `jet_product`.`type`='simple' AND `walmart_product`.`category` != '') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=".$data['merchant_id']." AND `walmart_product_variants`.`status`='".WalmartShopDetails::PRODUCT_STATUS_UPLOADED."' AND `walmart_product`.`category` != '')) as `merged_data`";
                                        /*$result = JetProduct::findBySql($sql)->all();
                                        return count($result);*/
                                        $result = \frontend\components\Data::sqlRecords($sql, 'one');

                                        return $result['count'];
                                },
                                'format'=>'raw',
                            ],
                                 ['attribute'=>'status',
                                'filter'=>array("Not Purchase"=>"Not Purchased","Purchased"=>"Purchased"),

                            ],
                             ['attribute'=>'app_status',
                                'filter'=>array("install"=>"Installed","uninstall"=>"Uninstalled"),

                            ],
                            'uninstall_date',
                        
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