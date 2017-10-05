<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\modules\integration\models\WalmartCategory */

$this->title = 'Newegg Manufacturer  View';
$this->params['breadcrumbs'][] = ['label' => 'Newegg Manufacturer  Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="newegg-order-detail-index content-section">
    <div class="form new-section">
         <div class="jet-pages-heading">
            <div class="title-need-help">
             <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>

            </div>
            <div class="product-upload-menu">
                <a class="btn btn-primary"
                           href="<?= Yii::$app->request->baseUrl ?>/neweggmarketplace/manufacturer/index">Back</a>
            </div>
        </div>

 
<div class="responsive-table-wrap">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'manufacturer_name',
            'manufacturer_url',
            'manufacturer_support_email',
            'manufacturer_support_phone',
            'manufacturer_support_url',
            'status',
        ],
    ]) ?>
</div>
</div>
</div>
