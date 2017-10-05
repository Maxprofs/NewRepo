<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\neweggmarketplace\models\NeweggConfiguration */

$this->title = 'Create Newegg Configuration';
$this->params['breadcrumbs'][] = ['label' => 'Newegg Configurations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="newegg-configuration-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
