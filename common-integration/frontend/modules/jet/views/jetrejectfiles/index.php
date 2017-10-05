<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\JetErrorfileInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Rejected Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-errorfile-info-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Fetch Rejected Files', ['create'], ['class' => 'btn btn-primary']) ?>
    </p>
    <?=Html::beginForm(['jetrejectfiles/massdelete'],'post');?>
	<?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>
	<?=Html::dropDownList('action','',['delete'=>'Delete'],['class'=>'form-control pull-right',])?>
	<div class="clear"></div>
	 <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= GridView::widget([
    	'id'=>'reject_grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            ['class' => 'yii\grid\CheckboxColumn'],
            //'id',
            //'jet_file_id',
            //'file_name',
            
            [
            		'label'=>'Product Sku(s)',
            		'attribute'=>'product_skus',
            		'format'=>'raw',
            		'value' => function($data){
            			$productSkus=array();
            			$productSkus=implode(', ',explode(',',$data->product_skus));
            			return $productSkus;
            		}
            ],
            'file_type',
            'error:ntext',
            'date',
            'status',
           // 'jetinfofile_id',

            [
            	'class' => 'yii\grid\ActionColumn',
            	'template' => '{view}{delete}{link}',
			],
        ],
    ]); ?>
<?php Pjax::end(); ?>
 <?= Html::endForm();?> 
</div>
<script type="text/javascript">
var submit_form = false;
$('body').on('keyup','.filters > td > input', function(event) {
	    if (event.keyCode == 13) {
	    	 if(submit_form === false) {
	    	        submit_form = true;
	    	        $("#reject_grid").yiiGridView("applyFilter");
	    	    }
	    }

});
$("body").on('beforeFilter', "#reject_grid" , function(event) {
	 return submit_form;
});
$("body").on('afterFilter', "#reject_grid" , function(event) {
	submit_form = false;
});
</script>