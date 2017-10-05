<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggmarketplace\models\NeweggCategory */

$this->title = 'Update Newegg Category: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Newegg Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="newegg-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
