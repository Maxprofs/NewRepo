<?php
/* @var $this yii\web\View */
/* @var $model app\models\JetRefund */

$this->title = 'Create Jet Refund';
$this->params['breadcrumbs'][] = ['label' => 'Jet Refunds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-refund-create content-section">
			 
    <?= $this->render('_form', [
        'model' => $model,
        'data'=>$data,
    ]) ?>

</div>
