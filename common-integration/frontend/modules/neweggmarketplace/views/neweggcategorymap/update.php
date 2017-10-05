<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggmarketplace\models\NeweggCategoryMap */

$this->title = 'Update Newegg Category Map: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Newegg Category Maps', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="newegg-category-map-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
