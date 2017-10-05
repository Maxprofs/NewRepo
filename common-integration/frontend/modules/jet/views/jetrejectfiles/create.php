<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\JetErrorfileInfo */

$this->title = 'Create Jet Errorfile Info';
$this->params['breadcrumbs'][] = ['label' => 'Jet Errorfile Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-errorfile-info-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
