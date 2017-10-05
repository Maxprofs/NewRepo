<?php
/* @var $this yii\web\View */
?>
<div class="jetcategory">
<div class="entry-edit-head">
	<h4 class="fieldset-legend">Attribute Management</h4>
</div>
<form action="<?= \yii\helpers\Url::toRoute(['jetcatattribute/save']);?>" method="post">
	<div class="enable_api">
	<div class="hot-scroll">
		
		<input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>"/>
		<h4>Submit to import all jet attribute which will associate with jet catgeory.</h4>
		<input type="submit" value="Import Jet Attributes" class="btn btn-primary"/>
	</div>
	</div>
</form>
