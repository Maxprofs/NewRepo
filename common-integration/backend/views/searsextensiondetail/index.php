<?php
// use backend\models\JetShopDetailsSearch;
use frontend\modules\sears\components\Dashboard\Earninginfo;
use frontend\modules\sears\components\Dashboard\OrderInfo;
use frontend\modules\sears\components\Dashboard\Productinfo;
use frontend\modules\sears\components\Searsappdetails;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Sears Shop Details';
$this->params['breadcrumbs'][] = $this->title;

?>
<head>
	<style type="text/css">
		.container{
			margin-left: 0;
		}
		.table > tbody > tr.red_alert > td, .table > tbody > tr.red_alert > th, .table > tbody > tr > td.red_alert, .table > tbody > tr > th.red_alert, .table > tfoot > tr.red_alert > td, .table > tfoot > tr.red_alert > th, .table > tfoot > tr > td.red_alert, .table > tfoot > tr > th.red_alert, .table > thead > tr.red_alert > td, .table > thead > tr.red_alert > th, .table > thead > tr > td.red_alert, .table > thead > tr > th.red_alert {
		  background-color: #c04f4f;
		}
		
	 	.table > tbody > tr.error > td, .table > tbody > tr.error > th, .table > tbody > tr > td.error, .table > tbody > tr > th.error, .table > tfoot > tr.error > td, .table > tfoot > tr.error > th, .table > tfoot > tr > td.error, .table > tfoot > tr > th.error, .table > thead > tr.error > td, .table > thead > tr.error > th, .table > thead > tr > td.error, .table > thead > tr > th.error {
		  background-color: #FFB9BB;
		} 
	</style>
</head>
<div class="jet-extension-detail-index">
	 <div class="jet-pages-heading">
		<h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		<div class="clear"></div>
	 </div>
	 
    <?= Html::beginForm(['jetshopdetails/bulk'],'post',['id'=>'jet_extention_detail']);?>	
    <?= Html::dropDownList('bulk_name', null, [''=>'-- select bulk action --','export'=>'Export Csv', 'staff-account' => 'Create staff account'], ['id'=>'jet_bulk_select','class'=>'form-control','data-step'=>'2','data-intro'=>"Select the BULK ACTION you want to operate.",'data-position'=>'bottom']);
    ?> 
    <?=Html::submitButton('Submit', ['class'=>'btn btn-primary export_csv_submit','value'=>'submit']);?>

	 <div class="list-page" style="float:right">
          Show per page 
          <select onchange="selectPage(this)" class="form-control" style="display: inline-block; width: auto; margin-top: 0px; margin-left: 5px; margin-right: 5px;" name="per-page">
            <option value="25" <?php if(isset($_GET['per-page']) && $_GET['per-page']==25){echo "selected=selected";}?>>25</option>
            <option <?php if(!isset($_GET['per-page'])){echo "selected=selected";}?> value="50">50</option>
            <option value="100" <?php if(isset($_GET['per-page']) && $_GET['per-page']==100){echo "selected=selected";}?> >100</option>
          </select>
          Items

        </div>
	   <br>
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?= GridView::widget([
    	'id'=>"jet_extention_details",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
        'pager' => [
            'class' => \liyunfang\pager\LinkPager::className(),
            'pageSizeList' => [25,50,100],
            'pageSizeOptions' => ['class' => 'form-control','style' => 'display: none;width:auto;margin-top:0px;'],
            'maxButtonCount'=>5,
        ],
    	'rowOptions'=>function ($model){
            if ($model->app_status=='0'){
	    		return ['class'=>'error'];
	    	}elseif (($model->status=='License Expired')||($model->status=='Trial Expired')){
    			return ['class'=>'danger'];
    		}elseif ($model->status=='Purchased'){
    			return ['class'=>'success'];
    		}
    		
    	},	
        'columns' => [
			['class' => 'yii\grid\CheckboxColumn',
        		'checkboxOptions' => function($data) 
        		{
        			return ['value' => $data['merchant_id'],'class'=>'bulk_checkbox','headerOptions'=>['id'=>'checkbox_header','data-step'=>'1','data-intro'=>"Select merchants to Export CSV",'data-position'=>'right']];
        		},               
    		],
            [
        		'label'=>'M-ID',
        		'attribute'=>'merchant_id',
        	],
            'sears_shop_details.shop_url:url',
            'sears_shop_details.email:email',
        	[
        		'label'=>'PUBLISHED',
        		'value' => function($data){
                    return Productinfo::getPublishedProducts($data->merchant_id);
                },
               'headerOptions' => ['style'=>'min-width: 140px;'],
                'filter'=>'From : <input class="form-control" name="JetShopDetailsSearch[live_from]" type="text" value="'.$searchModel->live_from.'"/><br/>To: <input class="form-control" name="JetShopDetailsSearch[live_to]" type="text" value="'.$searchModel->live_to.'"/>',
                'format'=>'raw',
        	],
        	[
        		'label'=>'ITEM PROCESSING',
        		'value' => function($data){
                    return Productinfo::getUnpublishedProducts($data->merchant_id);	        		
        		},
                'headerOptions' => ['style'=>'min-width: 140px;'],
                'filter'=>'From : <input class="form-control" name="JetShopDetailsSearch[review_from]" type="text" value="'.$searchModel->review_from.'"/><br/>To: <input class="form-control" name="JetShopDetailsSearch[review_to]" type="text" value="'.$searchModel->review_to.'"/>',
        		'format'=>'raw',
        	],
        	[
        		'label'=>'Order',
        		'value' => function($data){
                    return OrderInfo::getCompletedOrdersCount($data->merchant_id);	        		
        		},
                'headerOptions' => ['style'=>'min-width: 140px;'],
        		'filter'=>'From : <input class="form-control" name="JetShopDetailsSearch[order_from]" type="text" value="'.$searchModel->order_from.'"/><br/>To: <input class="form-control" name="JetShopDetailsSearch[order_to]" type="text" value="'.$searchModel->order_to.'"/>',
                'format'=>'raw',
        	],
            [
                'label'=>'Revenue',
                'value' => function($data){
                    return Earninginfo::getTotalEarning($data->merchant_id);  
                },
                'format'=>'raw',
            ],
            [
	            'label'=>'Config Set',
	            'value' => function($data)
                {
		            $isSet = Searsappdetails::isAppConfigured($data->merchant_id);
		            if ($isSet){
		            	return "Yes";
		            }else{
		            	return "No";
		            }		            
	            },
	            'format'=>'raw',
            ],        		
        	[
        		'label'=>'Installed On',
        		'attribute'=>'install_date',        		
        	], 
        	
            [
	            'label'=>'Expired On',
	            'attribute'=>'expire_date',	            
            ],

            [
                'label'=>'Uninstalled On',
                'attribute'=>'uninstall_date',              
            ],

            [
   				'attribute' => 'status',   			   				
    			'filter'=>array("Purchased"=>"Purchased","Not Purchase"=>"Not Purchase","License Expired"=>"License Expired","Trial Expired"=>"Trial Expired"),
			],
            [
	            'format'=>'raw',
	            'attribute' => 'app_status',
            	'value'=>function ($data)
            	{
            		if ($data->app_status == "1")
            			return "Installed";
            		else if ($data->app_status =="0")
            			return "Uninstalled";
            	},
	            'filter'=>["1"=>"Installed","0"=>"Uninstalled"],
	        ],
	        'panel_username',
	        'panel_password',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {link}',
                'buttons' => [
                    'link' => function ($url,$model,$key) {
                        return '<a data-pjax="0" href='.Yii::getAlias('@websearsurl').'/site/managerlogin?ext='.$model['merchant_id'].'&&enter=true>Login as</a>'; 
                    },            
                ],
            ],
        ],
    ]); ?>
<?php if(isset($this->assetBundles)):?>
    <?php unset($this->assetBundles['yii\web\JqueryAsset']);?>
<?endif;?>  
<?php Pjax::end(); ?>
<?=Html::endForm();?>
</div>
<script type="text/javascript">
    function selectPage(node){
      var value=$(node).val();
      $('#jet_shop_details').children('select.form-control').val(value);
    }
$('.export_csv_submit').click(function(event){
  if($("input:checkbox:checked.bulk_checkbox").length == 0)
  {
    alert('please select merchant id to perform bulk action');
    event.preventDefault();
  }
});
</script>
