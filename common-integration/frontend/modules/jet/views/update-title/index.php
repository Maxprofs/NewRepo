<?php 
use yii\helpers\Html;
$this->title = 'Update Product Title';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product_csv content-section">
  <div class="form new-section">
	<div class="jet-pages-heading">
	    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
		<div class="clear"></div>
	</div>
	<div class="csv_import col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<div class="csv_import1">
			<form action="<?php echo \yii\helpers\Url::toRoute(['update-title/export']);?>" method="post">
				<h4>Export Products Csv</h4>
				<p>Export csv file to update the product title.</p>
				<div class="input-wrap">
					<input type="submit" class="btn btn-primary" value="Export"/>
				</div>
			</form>
		</div>
	</div>
	<div class="csv_export col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<div class="csv_import1">
			<form action="<?php echo \yii\helpers\Url::toRoute(['update-title/readcsv']);?>" method="post" enctype="multipart/form-data">
				<h4>Import Products Csv</h4>
				<p>After export csv file, you can update product title for all products then try to import updated csv file. Product title will be updated on app , after that you can upload product on jet with updated product title.</p>
				<div class="input-wrap">
					<input type="file" name="csvfile"/>
					<input type="submit" class="btn btn-primary" value="Import"/>
				</div>
			</form>
		</div>
	</div>
	<div class="clear"></div>
	</div>

</div>
<style>
	.csv_import {
	  padding-left: 0;
	  padding-right: 10px;
	}
	.csv_export {
  padding-left: 10px;
  padding-right: 0;
}
.csv_import1 {
  border: 1px solid #d4d4d4;
  border-radius: 3px 3px 0 0;
  margin-top: 20px;
  min-height: 210px;
}
.csv_import1 h4 {
  background: #dfdfdf none repeat scroll 0 0;
  font-size: 18px;
  margin: 0 0 15px;
  padding: 13px 10px;
}
.csv_import1 p {
  line-height: 25px;
  padding-left: 10px;
  padding-right: 10px;
}
.input-wrap {
  padding-left: 10px;
  padding-right: 10px;
}
.input-wrap input {
  display: inline-block;
  margin-top: 10px;
}
</style>