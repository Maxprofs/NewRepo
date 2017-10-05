<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\JetProduct;
use app\models\JetMerchantProducts;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JetProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Manage Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php if(JetProduct::find()->where(['merchant_id'=>Yii::$app->user->identity->id])->count()==0):// || Yii::$app->user->identity->id==14?>
    <div class="yii_notice no_product_error">
        Either there is no product(s) in your shopify store or all product(s) having no sku.Please fill sku for all product(s) from Shopify Store.
    </div>
<?php endif;?>    
<style>
.jet_notice{
	font-weight: normal !important;
}
.fa-bell {
  color: #B11600;
}

</style>
<div class="jet-product-index">
<?=Html::beginForm(['jetproduct/bulk'],'post');?>
	<div class="jet-pages-heading">
		<h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		<a class="help_jet" href="<?= Yii::$app->request->baseUrl ?>/how-to-sell-on-jet-com#sec3" target="_blank" title="Need Help"></a>
		<?= Html::a('Get Jet Product Status(s)', ['productstatus'], ['class' => 'btn btn-primary']) ?>
		<?=Html::submitButton('submit', ['class' => 'btn btn-primary',]);?>
		<?php $arrAction=array('batch-upload'=>'Upload','archieved-batch'=>'Archived','unarchieved-batch'=>'UnArchived');?>
		<?php if(Yii::$app->user->identity->id==14){?>
			<?php $arrAction['upload']='Batch Upload';?>
			<?php //$arrAction['unarchive']='Unarchieved-Batch';//unarchive?>
			<?php //$arrAction['archive']='Archieved-Batch';//archive?>
		<?php }?>

		<?=Html::dropDownList('action','',$arrAction,['class'=>'form-control pull-right',])?>
		
		<div class="clear"></div>
	</div>
	<div class="jet_notice">
		<span class="font_bell">
			<i class="fa fa-bell fa-1x"></i>
		</span>
		Product(s) whose product-type(s) are already mapped with jet category.Please <a class="category_m" href="<?= Yii::$app->request->getBaseUrl().'/categorymap/index';?>">Click</a> Refer the link to map shopify product type with Jet category.
		<div style="clear:both"></div>
	</div>
   <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= GridView::widget([
    	'id'=>"product_grid",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
           // ['class' => 'yii\grid\SerialColumn'],
        		['class' => 'yii\grid\CheckboxColumn'],
          // 'id',
           // 'merchant_id',
        	[
        		'attribute' => 'image',
        				'format' => 'html',
        				'label' => 'Image',
        				'value' => function ($data) {
        						return Html::img($data['image'],
        						['width' => '80px','height'=>'80px']);
        		},
        	],
            [
        		'attribute'=>'title',
        		'contentOptions'=>['style'=>'width: 400px;'],
        		//'format'=>'text',
            	'format'=>'raw',	
	            'value' => function($data){
	            	if($data['status']=="Available for Purchase"){
	            		$productArr=array();
	            		$modelPro="";
	            		$modelPro=JetMerchantProducts::find()->where(['product_id'=>$data->id])->one();
	            		if($modelPro)
	            		{
	            			$productArr = json_decode($modelPro->product_data,true);
	            		}
	            		if(count($productArr)>0){
	            			$url = "https://jet.com/search?term=".$productArr['jet_sku'];
	            			return Html::a($data['title'], $url, ['title' => 'View live product on Jet','target'=>'_blank']);
	            		}	
	            		else
	            			return $data['title'];
	            	}else
	            		return $data['title'];
	            },
        	],
            [
             	'attribute'=> 'sku',
             	'label'=>'SKU',
             	'headerOptions' => ['width' => '250'],
            ],
            //'vendor',
            //'product_type',
            //'description',
           
            'qty',
            //'price',
            //'attr_ids:ntext',
           // 'jet_attributes:ntext',
            'upc',
            //'brand',
           'ASIN',
     
             [
             	'attribute'=> 'type',
             	'label'=>'Type',
             	'headerOptions' => ['width' => 'auto'],
             	'filter' => ["simple"=>"simple","variants"=>"variants"],
             ],
     
            [
            	'attribute'=>'status',
            	'label'=>'Status',
            	'headerOptions' => ['width' => '400'],
            	'filter' => ["Available for Purchase"=>"Available for Purchase","Excluded"=>"Excluded","Missing Listing Data"=>"Missing Listing Data","Under Jet Review"=>"Under Jet Review","Archived"=>"Archived","Not Uploaded"=>"Not Uploaded"],
            	'value' => function($data){
            		if($data['status']=="Missing Listing Data"){
            			$productArr=array();
            			$substatuses=array();
            			//$productArr=json_decode($data['id'],true);
            			$modelPro="";
	            		$modelPro=JetMerchantProducts::find()->where(['product_id'=>$data->id])->one();
	            		if($modelPro)
	            		{
	            			$productArr = json_decode($modelPro->product_data,true);
	            		}
            			if(isset($productArr['sub_status']) && count($productArr['sub_status'])>0){
            				foreach($productArr['sub_status'] as $key=>$value)
							{
								$substatuses[$key]=$value;
							}
							return $data['status'].' ('.implode(', ',$substatuses).')';
						}
            		}
            		return $data['status'];
            	},
            ],
            [
            	'attribute'=>'error',
            	'label'=>'Error',
            	'headerOptions' => ['width' => '800'],
            	'format'=>'html',
            	//'filter' => ["Available for Purchase"=>"Available for Purchase","Excluded"=>"Excluded","Missing Listing Data"=>"Missing Listing Data","Under Jet Review"=>"Under Jet Review","Archived"=>"Archived","Not Uploaded"=>"Not Uploaded"],
            	'value' => function($data){
	           		 if($data['error']){
							return Html::ul(array_filter(explode('<br>',$data['error'])),['title' => 'Upload Error','class'=>'error_list_upload']);
	            	}else{
							return "";
            		}
            	},
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header'=>'Action','headerOptions' => ['width' => '80'],
                'template' => '{view} {update} {link}',
                
            ],
            
           
        ],
    ]); ?>
<?php Pjax::end(); ?>
</div>
	<?php //slide for help
	if($productPopup)
	{            	
	?>
	<div class="jet_config_popup jet_config_popup_error" style="">
		<div id="jet-import-product" class="import-product" >
			<div class="fieldset welcome_message">
				<div class="entry-edit-head">
					<h4 class="fieldset-legend">
						Welcome! to Jet Products Manage Section
					</h4>
				</div>
				<?php 
					if ($countUpload){
						?>
						<div class="entry-edit-head">
						    <h4>You have <?php echo $countUpload;?> products in your shopify Store </h4>
							<h4 id="product_import" class="alert-success" style="display: none"></h4>
							<h4 id="not_sku" style="display: none" class="alert-success"></h4>		
						</div>
						<div class="import-btn">
							<h4>Click to import Shopify store products to Jet-Integration App<h4>
							<a href="<?php echo \yii\helpers\Url::toRoute(['jetproduct/batchimport'])?>" class="btn">Import Products</a>		
						</div>
						<?php 
					}else{
						?>
						<div class="product-error">
							<h4>Either you don't have any product or none of products have SKU in your shopify Store </h4>
						</div>
						<?php 
					}
				?>
				<div class="loading-bar" style="display: none;">
					<img alt="" src="<?=Yii::$app->getUrlManager()->getBaseUrl() ?>/images/loading_spinner.gif">
					<h3>Please wait...</h3>
				</div>
			</div>
		</div>
	</div>
	<div class="jet_config_popup_overlay" style=""></div>
<?php }?>
<style>	
    .no_product_error{
        background-color: #f2dede;
        border-color: #ebccd1;
        color: #a94442;
    }
   .jet_config_popup .product-import,.jet_config_popup .welcome_message{
    	background: #fff none repeat scroll 0 0;
	    border-radius: 5px !important;
	    margin: 5% auto 3%;
	    overflow: hidden;
	    position: relative;
	    width: 50%;
	    margin-top: 11%;
    }
    .import-btn .btn{
	    background-color: #067365 !important;
	    border-color: #067365 !important;
		margin-bottom: 8px;
	    margin-left: 5px;
	    color:white;
	    padding: 11px 22px;
	}
	.jet_notice a {
    background: #00acac none repeat scroll 0 0;
    border-radius: 2px;
    color: #fff;
    float: right;
    font-size: 14px;
    padding: 1px 8px;
    text-decoration: unset;
	}
	.jet_notice {
	    background-color: #f5f5f5;
	    border-color: #d6e9c6;
	    border-radius: 4px;
	    color: #333;
	    font-size: 14px;
	    font-weight: bold;
	    line-height: 19px;
	    margin-bottom: 0;
	    padding: 4px 8px;
	}
	
</style>
<script type="text/javascript">
var submit_form = false;
$('body').on('keyup','.filters > td > input', function(event) {
	    if (event.keyCode == 13) {
	    	 if(submit_form === false) {
	    	        submit_form = true;
	    	        $("#product_grid").yiiGridView("applyFilter");
	    	    }
	    }

});
$("body").on('beforeFilter', "#product_grid" , function(event) {
	 return submit_form;
});
$("body").on('afterFilter', "#product_grid" , function(event) {
	submit_form = false;
});
/*function importProducts(node){
	var base_url = window.location.origin;
	j$('.loading-bar').show();
	j$('.import-btn').hide();
	j$.post(base_url+"/jet/jetproduct/importproduct",
    {},
    function(data, status){
    	j$('.loading-bar').hide();
    	var message=jQuery.parseJSON(data);
    	if("error" in message){
			alert(message.error);
        }
        else
        if("import_products" in message){
            j$('#product_import').html("Total Products Imported:"+message['import_products']);
			j$('#product_import').show();	
			if("not_sku" in message){
				j$('#product_import').html("Product without Sku(s):"+message['not_sku']);
				j$('#product_import').show();
			}
			setTimeout(function(){
			    window.location.reload();
			}, 1000);
        }
    });
}*/
</script>