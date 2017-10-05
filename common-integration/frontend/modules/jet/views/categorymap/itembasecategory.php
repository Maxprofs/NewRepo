<?php
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use frontend\modules\jet\components\Jetcategorytree;
use yii\helpers\Html;


//$filteredresultData = array_filter($model, 'filter');
$urlJet = \yii\helpers\Url::toRoute(['categorymap/itembasemapcategory']);
$merchant_id = MERCHANT_ID;
$sku = Yii::$app->request->getQueryParam('sku', '');

$searchModel = ['id' => null, 'sku' => $sku];

$dataProvider = new ArrayDataProvider([
        'key'=>'id',
        'allModels' => $model,
        'sort' => [
            'attributes' => ['id', 'sku'],
        ],
]);

?>
<div class="jet-product-index content-section">
    <div class="form new-section">
   		<div class="responsive-table-wrap">
	<?= GridView::widget([
	        'dataProvider' => $dataProvider,
        	'filterModel' => $searchModel,

	        'columns' => [
                 ['class' => 'yii\grid\CheckboxColumn','checkboxOptions'=>['class'=>'bulk_checkbox'],'headerOptions'=>['id'=>'checkbox_header']],
	            [
	            'attribute' => 'sku', 
	            'value' => 'sku',
	            ],
	            [
	            "attribute" => "fulfillment_node",
	            "label" => "Map category",
	            "value" => function($data){
	            	$path = Jetcategorytree::getCategorypath($data['fulfillment_node']);
       					
       					return $path;
	            },
	            ],
	             [
                        'class' => 'yii\grid\ActionColumn',
                        'header'=>'<a herf="javascript:void(0)">Action</a>','headerOptions' => ['width' => '80'],
                        'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Choose action to view/update/error details'],
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url,$model) 
                            {
                                
                                    $options = ['data-pjax'=>0, 'class'=>'view' ,'onclick'=>'clickView(this.id)','title'=>'Map category','id'=>$model];
                                    
                                    return Html::a(
                                        '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
                                        'javascript:void(0)',$options
                                    );
                            },
                        ],
                    ],

	    ]
	]);
?>
		</div>
	</div>
</div>
<div id="item_map_category"></div>
<script>
    function clickView(id)
    {
        var url='<?= $urlJet ?>';
        var merchant_id='<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        console.log(id);
        $.ajax({
            method: "post",
            url: url,
            data: {id:id}
        })
            .done(function(msg) {
                $('#LoadingMSG').hide();
                $('#item_map_category').html(msg);
                $('#item_map_category').css("display","block");
                $('#item_map_category #myModal').modal('show');
            });
    }
</script>