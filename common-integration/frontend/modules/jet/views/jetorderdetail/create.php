<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\JetOrderDetail */

$this->title = 'Create Jet Order Detail';
$this->params['breadcrumbs'][] = ['label' => 'Jet Order Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-order-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
