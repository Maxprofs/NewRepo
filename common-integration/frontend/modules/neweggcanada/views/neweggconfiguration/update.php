<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggcanada\models\NeweggConfiguration */

$this->title = 'Update Newegg Configuration: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Newegg Configurations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="newegg-configuration-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
