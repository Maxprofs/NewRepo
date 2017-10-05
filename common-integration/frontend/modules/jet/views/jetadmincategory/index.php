<?php
$this->title = 'Jet Category';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jetcategory">
	<div class="entry-edit-head">		
		<h4 class="fieldset-legend">General</h4>
	</div>	
	<form action="<?php echo \yii\helpers\Url::toRoute(['jetadmincategory/ajax-csv-import'])?>" method="post" enctype='multipart/form-data'>
		<div class="enable_api">
		<div class="hot-scroll">
		<h4> Now you need to upload selected category csv file that you want to create. </h4>
		<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
		<input type="file" name="csv_file" />
		<input type="submit" value="Save Category" class="btn btn-primary"/>
		</div>
		</div>
	</form>
</div>