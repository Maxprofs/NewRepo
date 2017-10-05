<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/*$form = ActiveForm::begin([
	'action'=>\yii\helpers\Url::toRoute(['jetcategory/save']),
    'id' => 'product-form',
	'method'=>'post',
    'options' => ['enctype' => 'multipart/form-data'],
]) ?>

	<?= $form->field($model, 'csvfile')->fileInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() */?>
<div class="jetcategory">
<h4>Firstly you need to download jet category csv. Download From here..<a href=<?php echo Yii::$app->request->BaseUrl?>/upload/Jet_Taxonomy.csv>Download CSV</a></h4>



<div class="entry-edit-head">
	
	<h4 class="fieldset-legend">General</h4>
</div>

<form action="<?php echo \yii\helpers\Url::toRoute(['jetcategory/save'])?>" method="post" enctype='multipart/form-data'>
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