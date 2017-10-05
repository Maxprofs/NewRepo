<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\JetReturn */

$this->title = 'Create Jet Return';
$this->params['breadcrumbs'][] = ['label' => 'Jet Returns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-return-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
