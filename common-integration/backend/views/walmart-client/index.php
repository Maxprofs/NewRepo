<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;


$this->title = 'Walmart Client Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-test-api-index">
    <a class="'btn btn-primary" href ="export" style="padding: 10px;">Export New Client detail</a>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            'merchant_id',
            [
                'attribute' => 'fname',
                'label' => 'First Name',
                'format' => 'html',
                //'value'=>'jet_product.price',
                'value' => function ($data) {
                    $viewUrl = Url::toRoute(['/walmart-client/view?id='.$data['merchant_id']]);
                    return '<a href="'.$viewUrl.'">'.$data['fname'].'</a>';
                }
            ],
            'lname',
            'mobile',
            'email',
            [
                'attribute' => 'consumer_id',
                'value' => 'configuration.consumer_id'
            ],
            //'configuration.consumer_id',
            //'configuration.secret_key',
            [
                'attribute' => 'secret_key',
                'value' => 'configuration.secret_key'
            ],
            /*[
               'class' => 'yii\grid\ActionColumn',
               'template' => '{delete}',
           ],*/

        ],
    ]); ?>

</div>
