<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

use frontend\modules\jet\models\JetCategorySearch;
use frontend\modules\jet\models\JetSelectedCategory;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JetCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Category Selection';
$this->params['breadcrumbs'][] = $this->title;

//var_dump($category_ids);
//echo "<hr>";
$array=array(0=>1000000,1=>1000001,2=>1000002);

//var_dump($array);
//die;

?>
<div class="jet-category-index">

    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
     <a class="help_jet" data-toggle="modal" data-target=".bs-example-modal-lg" href="javascript:void(0);" title="Need Help"></a>
     <div class="clear"></div>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?=Html::beginForm(['jetselectcat/category'],'post');?>
    <?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>
   
     <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= GridView::widget([
    	'id'=>"select_cat",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
           // ['class' => 'yii\grid\SerialColumn'],
        		['class' => 'yii\grid\CheckboxColumn',
	        		'checkboxOptions' => function($data, $key, $index, $column) {
		        		$result="";$cat=array();
		        		$merchant_id=Yii::$app->user->identity->id;
		        		$result=JetSelectedCategory::find()->where(['merchant_id'=>$merchant_id])->all();
		        		if($result){
		        			foreach ($result as $value){
		        				$cat[]=$value->category_id;
		        			}
		        		}
	        			$bool = in_array($data->category_id,$cat);
	        			return ['checked' => $bool,'value'=>$data->category_id];
	        		}
				],
            //'id',
            'category_id',
            //'merchant_id',
            'title:ntext',
            'parent_id',
            'parent_title:ntext',
            'root_id',
            'root_title:ntext',
            'level',
        	/* [
            	'label' => 'Jet Attributes',
           		 'value' => function($data){
                return JetCategorySearch::get_attributes($data->category_id);
            	},
        	], */
			'jet_attributes',
        	[
        		'label' => 'Status',
        		'value' => function($data){
        			return JetCategorySearch::get_status($data->category_id);
        				 
        		},
        		'options' => ['style' => 'width:100px;'],
        	],
            //'jet_attributes:ntext',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
	 <?= Html::endForm();?> 
	 <div class="container text-center">
		<!-- Large modal -->
		<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div id="myCarousel" class="carousel slide" data-ride="carousel">
				  <!-- Wrapper for slides -->
				  <div class="carousel-inner">
					<!-- <div class="item active">
					 <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/Shopify-Jet-Integration-9.png" alt="...">
					  <div class="carousel-caption">
						<p>Every product need ASIN or UPC and Brand
						</p>
					  </div>
					</div> -->
					<div class="item active">
						  <img class="img-responsive" src="<?= Yii::$app->getUrlManager()->getBaseUrl();?>/images/Shopify-Jet-Integration-6.png" alt="...">
						  <div class="carousel-caption">
							<p>Every product must assign with atleast one jet category.Merchant need to create categories for their store products.</p>
						  </div>
					 </div>
				  </div>
				  <!-- Indicators -->
					<!-- <ol class="carousel-indicators">
					  <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
					   <li data-target="#myCarousel" data-slide-to="1" class="active"></li>
					   <li data-target="#myCarousel" data-slide-to="2" class="active"></li>
					    <li data-target="#myCarousel" data-slide-to="3" class="active"></li>
					</ol> -->
				  <!-- Controls -->
				  <!-- <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left"></span>
				  </a>
				  <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right"></span>
				  </a> -->
				</div>
			</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var submit_form = false;
$('body').on('keyup','.filters > td > input', function(event) {
	    if (event.keyCode == 13) {
	    	 if(submit_form === false) {
	    	        submit_form = true;
	    	        $("#select_cat").yiiGridView("applyFilter");
	    	    }
	    }

});
$("body").on('beforeFilter', "#select_cat" , function(event) {
	 return submit_form;
});
$("body").on('afterFilter', "#select_cat" , function(event) {
	submit_form = false;
});
</script>