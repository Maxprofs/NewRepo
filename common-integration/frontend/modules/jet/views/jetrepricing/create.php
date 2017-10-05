<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\models\JetDynamicPrice */

$this->title = 'Create Jet Dynamic Price';
$this->params['breadcrumbs'][] = ['label' => 'Jet Dynamic Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-dynamic-price-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
