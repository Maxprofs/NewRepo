<?php 
use yii\helpers\Html;
$this->title = 'CSV Product Upload';
$this->params['breadcrumbs'][] = $this->title;

$importUrl = \yii\helpers\Url::toRoute(['jetproduct-fileupload/startcsvupload']);
$exportUrl = \yii\helpers\Url::toRoute(['jetproduct-fileupload/exportcsvupload']);
?>
<div class="product_csv content-section">	
	<div class="form new-section">
	<div class="jet-pages-heading">
	    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		<div class="clear"></div>
	</div>	
		<div class="csv_import col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="csv_import1">
				<form action="<?= $exportUrl;?>" method="post">
					<h4>Export Product CSV</h4>
					<p>Here can export Product CSV for upload products on jet,And also can export Products CSV from shopify store for upload products on jet</p>
					
					<div class="input-wrap">
					
						<input type="submit" id="export-data" class="btn btn-primary" title="Click to export products data for updation" value="Export"/>
					</div>
				</form>
			</div>
		</div>
		<div class="csv_export col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="csv_import1">
				<form action="<?= $importUrl;?>" method="post" enctype="multipart/form-data">
					<h4>Import CSV</h4>
					<p>Import product csv to upload products on jet.Here can also import shopify products CSV for upload products on jet<br><b>Note
					:</b> All import Sku Must be list in App for upload on jet.</p>
					<div class="input-wrap clearfix">
						<input type="file" name="csvfile"/>
						<input type="submit" class="btn btn-primary" title="Import the updated csv file" value="Import"/>
					</div>
				</form>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
