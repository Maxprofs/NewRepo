<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\JetDynamicPrice */

$this->title = 'sku: '.$model->sku;
$this->params['breadcrumbs'][] = ['label' => 'Jet Dynamic Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->sku, 'url' => ['view', 'sku' => $model->sku]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="jet-dynamic-price-update content-section">
<div class="row form new-section">
	<?=Html::a('Back', \yii\helpers\Url::to(Yii::$app->request->referrer),['class'=>'btn btn-primary','style'=>'float:right']);?>
    <h2><?= Html::encode($this->title) ?></h2>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
</div>
