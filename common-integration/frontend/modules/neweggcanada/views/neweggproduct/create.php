<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggcanada\models\NeweggProduct */

$this->title = 'Create Newegg Product';
$this->params['breadcrumbs'][] = ['label' => 'Newegg Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="newegg-product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
