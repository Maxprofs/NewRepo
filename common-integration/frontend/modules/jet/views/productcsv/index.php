<?php 
use yii\helpers\Html;
$this->title = 'CSV Export/Import';
$this->params['breadcrumbs'][] = $this->title;

$importUrl=\yii\helpers\Url::toRoute(['productcsv/ajax-csv-import']);

?>
<div class="product_csv content-section">	
	<div class="form new-section">
	<div class="jet-pages-heading">
	    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		<div class="clear"></div>
	</div>	
		<div class="csv_import col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="csv_import1">
				<form action="<?= \yii\helpers\Url::toRoute(['productcsv/export']);?>" method="post">
					<h4>Export CSV</h4>
					<p>Here desired fields can be chosen, you want to update. By clicking on export you would be able to get a csv of your products to update them</p>
					
					<div class="input-wrap">
						<div class="checkox_label">
						<input class="export-option" type="checkbox" value="title" name="col[]"> Title
						<input class="export-option" type="checkbox" value="price" name="col[]"> Price
						<input class="export-option" type="checkbox" value="upc" name="col[]"> Barcode 
						<input class="export-option" type="checkbox" value="asin" name="col[]"> ASIN
						<input class="export-option" type="checkbox" value="mpn" name="col[]"> MPN
						<input class="export-option" type="checkbox" value="description" name="col[]"> Description
					</div>
						<input type="submit" id="export-data" class="btn btn-primary" title="Click to export products data for updation" value="Export" disabled="disabled" />
					</div>
				</form>
			</div>
		</div>
		<div class="csv_export col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<div class="csv_import1">
				<form action="<?= $importUrl;?>" method="post" enctype="multipart/form-data">
					<h4>Import CSV</h4>
					<p>After update desired fields , you need to import CSV file to upload all changes on product database.</p>
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
<script type="text/javascript">
var enableMe = false;
$( ".export-option" ).change(function() 
{
  	$(".export-option" ).each(function() 
	{
		if ($(this). prop("checked") == true) 
		{
			enableMe = true;
			return;
		}
	});	
  	if (enableMe) {
		$('#export-data').prop('disabled', false);
	}else
	{
		$('#export-data').prop('disabled', true);
	}
enableMe = false;
});
</script>