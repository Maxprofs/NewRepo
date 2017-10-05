<?php

use common\models\JetOrderDetail;
use common\models\JetProduct;
use frontend\modules\jet\components\Dashboard\Earninginfo;
use frontend\modules\jet\components\Dashboard\OrderInfo;
use frontend\modules\jet\components\Dashboard\Productinfo;
use frontend\modules\jet\components\Jetappdetails;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Jet Shop Details';
$this->params['breadcrumbs'][] = $this->title;

?>
<head>
	<style type="text/css">
		.container{
			margin-left: 0;
		}
		.table > tbody > tr.red_alert > td, .table > tbody > tr.red_alert > th, .table > tbody > tr > td.red_alert, .table > tbody > tr > th.red_alert, .table > tfoot > tr.red_alert > td, .table > tfoot > tr.red_alert > th, .table > tfoot > tr > td.red_alert, .table > tfoot > tr > th.red_alert, .table > thead > tr.red_alert > td, .table > thead > tr.red_alert > th, .table > thead > tr > td.red_alert, .table > thead > tr > th.red_alert {
		  background-color: #A91A2E;
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
	 
    <?= Html::beginForm(['jetextensiondetail/bulk'],'post',['id'=>'jet_extention_detail']);?>	
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
            if ($model->sendmail=='no'){
                return ['class'=>'red_alert'];
            }elseif ($model->install_status=='uninstall'){
	    		return ['class'=>'error'];
	    	}elseif (($model->purchase_status=='License Expired')||($model->purchase_status=='Trial Expired')){
    			return ['class'=>'danger'];
    		}elseif ($model->purchase_status=='Purchased'){
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
            'shop_url:url',
            'email:email',
        	[
        		'label'=>'Live',
        		'value' => function($data){
                    return Productinfo::getLiveProducts($data->merchant_id);
                },
               'headerOptions' => ['style'=>'min-width: 140px;'],
                'filter'=>'From : <input class="form-control" name="JetExtensionDetailSearch[live_from]" type="text" value="'.$searchModel->live_from.'"/><br/>To: <input class="form-control" name="JetExtensionDetailSearch[live_to]" type="text" value="'.$searchModel->live_to.'"/>',
                'format'=>'raw',
        	],
        	[
        		'label'=>'Review',
        		'value' => function($data){
                    return Productinfo::getUnderReviewProducts($data->merchant_id);	        		
        		},
                'headerOptions' => ['style'=>'min-width: 140px;'],
                'filter'=>'From : <input class="form-control" name="JetExtensionDetailSearch[review_from]" type="text" value="'.$searchModel->review_from.'"/><br/>To: <input class="form-control" name="JetExtensionDetailSearch[review_to]" type="text" value="'.$searchModel->review_to.'"/>',
        		'format'=>'raw',
        	],
        	[
        		'label'=>'Order',
        		'value' => function($data){
                    return OrderInfo::getCompletedOrdersCount($data->merchant_id);	        		
        		},
                'headerOptions' => ['style'=>'min-width: 140px;'],
        		'filter'=>'From : <input class="form-control" name="JetExtensionDetailSearch[order_from]" type="text" value="'.$searchModel->order_from.'"/><br/>To: <input class="form-control" name="JetExtensionDetailSearch[order_to]" type="text" value="'.$searchModel->order_to.'"/>',
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
		            $isSet = Jetappdetails::checkConfiguration($data->merchant_id);
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
        		'attribute'=>'installed_on',
        		'format'=>'raw',
        		'value' => function($data){
	        		return date("d-m-Y",strtotime($data->installed_on));
	        	}
        	], 
        	
            [
	            'label'=>'Expired On',
	            'attribute'=>'expired_on',
	            'format'=>'raw',
	            'value' => function($data){
	            return date("d-m-Y",strtotime($data->expired_on));
           		 }
            ],
           
            [
   				'attribute' => 'purchase_status',   			   				
    			'filter'=>array("Purchased"=>"Purchased","Not Purchase"=>"Not Purchase","License Expired"=>"License Expired","Trial Expired"=>"Trial Expired"),
			],
            [
	            'format'=>'raw',
	            'attribute' => 'install_status',
                'value'=>function ($data)
                {                    
                    if ($data->install_status == "1") 
                        return "install";                    
                    else if ($data->install_status =="0") 
                        return "uninstall";                    
                },
	            'filter'=>["1"=>"install","0"=>"uninstall"],
	        ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {link}',
                'buttons' => [
                    'link' => function ($url,$model,$key) {
                        return '<a data-pjax="0" href='.Yii::getAlias('@weburl').'/site/managerlogin?ext='.$model['merchant_id'].'>Login as</a>'; 
                    },            
                ],
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?>
<?=Html::endForm();?>
</div>
<script type="text/javascript">
    function selectPage(node){
      var value=$(node).val();
      $('#jet_extention_details').children('select.form-control').val(value);
    }
$('.export_csv_submit').click(function(event){
  if($("input:checkbox:checked.bulk_checkbox").length == 0)
  {
    alert('please select merchant id to perform bulk action');
    event.preventDefault();
  }
});
</script>
