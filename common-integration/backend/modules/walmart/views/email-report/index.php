<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\datepicker\DatePicker;
use backend\modules\reports\models\Issues;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\JetEmailTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Email Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-report-index">
<?=Html::beginForm(['email-report/mass'],'post',['id'=>'mass_action']);?>
    <h2><?= Html::encode($this->title) ?></h2>
   <?=Html::submitButton('delete', ['class' => 'btn btn-danger',]);?>

 
    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
             ['class' => 'yii\grid\CheckboxColumn',
                                'checkboxOptions' => function($data) 
                                {
                                    return ['value' => $data['tracking_id']];
                                },
            ],
           // ['class' => 'yii\grid\SerialColumn'],
            'tracking_id',
            'merchant_id',
            [
                'attribute'=>'send_at',
                'value'=>'send_at',
                'format'=>'raw',
                'filter'=> DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'send_at',
                                'clientOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                        ]),


            ],
             [
                'attribute'=>'read_at',
                'value'=>'read_at',
                'format'=>'raw',
                'filter'=> DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'read_at',
                                'clientOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                        ]),


            ],
            ['attribute'=>'mail_status',
             'filter'=>array("sending"=>"sending","sent"=>"sent","read"=>"read","failed"=>"failed"),

            ],
            'email_template_path',
                 [
            'class' => 'yii\grid\ActionColumn',
                'template' => '{my_button}', 
                'buttons' => [
                    'my_button' => function ($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>',

                        ['view', 'id' => $data['tracking_id']], 
                        [
                            'title' => 'view',
                            'data-pjax' => '0',
                            'target'=>'_blank',
                        ]);
                    
                                    },
                                ]
                        ],
        ],
    ]); ?>
</div>
