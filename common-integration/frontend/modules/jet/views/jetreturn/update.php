<?php



/* @var $this yii\web\View */
/* @var $model app\models\JetReturn */

$this->title = 'Update Jet Return';
$this->params['breadcrumbs'][] = ['label' => 'Jet Returns', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="jet-return-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
