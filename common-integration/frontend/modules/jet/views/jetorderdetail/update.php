<?php
$this->title = 'Edit fulfillment: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Jet Order Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="jet-order-detail-update">
	<?= $this->render('_form', [
        'model' => $model,'carriers'=>$carriers,
    ]) ?>
</div>