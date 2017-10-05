<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\jet\components\Data;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\jet\models\JetProductFileUploadSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$processfile = Yii::getAlias('@webjeturl').'/jetproduct-fileupload/processfile';
$verifyprocessedfile = Yii::getAlias('@webjeturl').'/jetproduct-fileupload/verifyfileprocess';

$this->title = 'Jet Product File Uploads';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-product-file-upload-index content-section ">
	<div class="form new-section">
     <div style="float:right;">
     <?= Html::a('Bulk Uploads', ['jetproduct-fileupload/startupload'], ['data-step'=>'1','data-intro'=>'Upload all products in one click','data-position'=>'top','class' => 'btn btn-primary']) ?>     
    	 <?= Html::a('Bulk Price Update', ['jetproduct-fileupload/priceupdate'], ['data-step'=>'2','data-intro'=>'Bulk update Price for all sku','data-position'=>'top','class' => 'btn btn-primary']) ?>     
      <?= Html::a('Bulk Inventory Update', ['jetproduct-fileupload/inventoryupdate'], [ 'data-step'=>'3','data-intro'=>'Bulk update Inventory for all sku','data-position'=>'top','class' => 'btn btn-primary']) ?>     
      <?= Html::a('Process File', ['jetproduct-fileupload/process-uploaded-file'], ['data-step'=>'4','data-intro'=>'Process,Inventory,Price ,and Sku upload file ','data-position'=>'top','class' => 'btn btn-primary']) ?>     
      <?= Html::a('Verify File', ['jetproduct-fileupload/verify-processing-success'], ['data-step'=>'5','data-intro'=>'Verify Processed File ','data-position'=>'top','class' => 'btn btn-primary']) ?>     
     </div>
         <h1><?= Html::encode($this->title) ?></h1>
	<?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
        <div class="responsive-table-wrap">
	    <?= GridView::widget([
	        'dataProvider' => $dataProvider,
	        'filterModel' => $searchModel,
	        'columns' => [
	            ['class' => 'yii\grid\SerialColumn'],
	
	            //'local_file_path',
	        	[
	        		'attribute'=>'file_name',
	        		'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Unique File name']
	        	],           
	            // 'file_url:ntext',
	        	[
	        		'attribute'=>'jet_file_id',
	        		'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Unique jet file ID']
	        	],
	            // 'received',
	            // 'processing_start',
	            // 'processing_end',
	        	[
	        		'attribute'=>'total_processed',
	        		'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Number of products processed']
	        	],
	        	[
	        		'attribute'=>'error_count',
	        		'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Number of products with error']
	        	],            
	            // 'error_url:ntext',
	            // 'error_excerpt:ntext',
	            // 'expires_in_seconds',
	            // 'file_upload_time',
	        	[
	        		'attribute'=> 'file_type',
	        		'label'=>'File Type',
	        		'headerOptions' => ['width' => 'auto','data-toggle'=>'tooltip1','title'=>'Type of file uploaded'],
	        		'filter' => ["MerchantSKUs"=>"MerchantSKUs","Price"=>"Price","Inventory"=>"Inventory","Variation"=>"Variation","Archive"=>"Archive"],
	        	],
	        	[
	        		'attribute'=> 'status',
	        		'label'=>'Status',
	        		'headerOptions' => ['width' => 'auto','data-toggle'=>'tooltip1','title'=>'File upload status'],
	        		'filter' => ["Acknowledged"=>"Acknowledged","Processing"=>"Processing","Processed with errors"=>"Processed with errors","Processed successfully"=>"Processed successfully"],
	        	],
	            // 'processing_status',
	        		'file_upload_time',
	
	            [
	        		'class' => 'yii\grid\ActionColumn',
	                'template' => '{process}{verify}{error}{link}',
	                'buttons' => [
	                  'process' => function ($url,$model) 
	                   {
	                   	if((trim($model->status)==''))
	                   	{
	                    	return Html::a(
	                      	'<span class="glyphicon glyphicon-road"> </span>',
	                      	'javascript:void(0)', ['title'=>'Click to request jet for file processing', 'data-pjax'=>0, 'onclick'=>'processuploadedfile(this.id)', 'id'=>$model->file_name]);
	                   	}
	                   },
	                   'verify' => function ($url,$model) 
	                   {
	                     if(($model->status=='Acknowledged'))
	                     {
	                      return Html::a(
	                        '<i class="fa fa-rocket" aria-hidden="true"></i>', 
	                        'javascript:void(0)', ['title'=>'Click to verify uploaded file', 'data-pjax'=>0, 'onclick'=>'verifyuploadedfile(this.id)', 'id'=>$model->jet_file_id]);
	                     }
	                    },
	                    'error' => function ($url,$model) 
	                    {
	                      if(($model->error_url!=''))
	                      {
	                      return Html::a(
	                        '<span class="glyphicon glyphicon-cloud-download upload-error"> </span>', 
	                        $model->error_url, ['title' => 'Download error file', 'data-pjax'=>0]);
	                      }
	                    },          		        		           		
	        		],
	        	],
	        ],
	    ]); ?>
	</div>
	<?php Pjax::end(); ?>
	</div>
</div>

<script type="text/javascript">
var csrfToken = $('meta[name="csrf-token"]').attr("content");
function processuploadedfile(file_name) 
{
  var url='<?= $processfile ?>';      
  $('#LoadingMSG').show();
    $.ajax({
      method: "post",
      url: url,
      data: {file_name:file_name,_csrf : csrfToken }
  })
    .done(function(msg) {        
     $('#LoadingMSG').hide();
     alert(msg);
     location.reload(); 
    });
}
function verifyuploadedfile(jet_file_id)
{
	var url='<?= $verifyprocessedfile ?>';      
	  $('#LoadingMSG').show();
	    $.ajax({
	      method: "post",
	      url: url,
	      data: {jet_file_id:jet_file_id,_csrf : csrfToken }
	  })
    .done(function(msg) {        
     $('#LoadingMSG').hide();
     alert(msg);
     location.reload(); 
    });
}
</script>

<?php $get = Yii::$app->request->get();
  if(isset($get['tour'])) : 
?>
  <script type="text/javascript">
    $(document).ready(function(){
        var returnQuicktour = introJs().setOptions({
                doneLabel: 'Next page',
                showStepNumbers: false,
                exitOnOverlayClick: false
            });
        setTimeout(function() {
            returnQuicktour.start().oncomplete(function() {
              window.location.href = '<?= Data::getUrl("jetorderdetail/index?tour") ?>';
          });
         },1000);      
      });
  </script>
<?php endif; ?>