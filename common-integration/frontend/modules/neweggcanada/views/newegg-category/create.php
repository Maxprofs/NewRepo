<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggcanada\models\neweggcanadategory */

$this->title = 'Create Newegg Category';
$this->params['breadcrumbs'][] = ['label' => 'Newegg Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="newegg-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
