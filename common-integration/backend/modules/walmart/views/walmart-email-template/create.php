<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\modules\reports\models\WalmartEmailTemplate */

$this->title = 'Create Walmart Email Template';
$this->params['breadcrumbs'][] = ['label' => 'Walmart Email Templates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="walmart-email-template-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
