<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;
use backend\modules\reports\models\Issues;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\reports\models\IssuesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Issues';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="issues-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Issues', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            
            ['attribute'=>'issue_type',
             'filter'=>ArrayHelper::map(Issues::find()->all(),'issue_type','issue_type'),
            ],
             
           
            'issue_description:ntext',
               [
                'attribute'=>'issue_date',
                'value'=>'issue_date',
                'format'=>'raw',
                'filter'=> DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'issue_date',
                                'clientOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                        ]),


            ],
                  [
                'attribute'=>'resolve_date',
                'value'=>'resolve_date',
                'format'=>'raw',
                'filter'=> DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'resolve_date',
                                'clientOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                        ]),


            ],
             'assign',
              ['attribute'=>'issue_status',
             'filter'=>array("pending"=>"Pending","solved"=>"Solved"),

            ],

            ['class' => 'yii\grid\ActionColumn'],
            [
            'class' => 'yii\grid\ActionColumn',
                'template' => '{my_button}', 
                'buttons' => [
                    'my_button' => function ($url, $model, $key) {
                        if($model->issue_status!="solved"){
                        return Html::a('<button type="button" class="btn btn-warning" id="issue-button">Solve</button>',
                        ['resolve', 'id' => $model->id], 
                        [
                            'title' => 'Download',
                            'data-pjax' => '0',
                        ]);
                    }
                        else{
                             return Html::a('<button type="button" class="btn btn-success" id="issue-button">Resolved</button>');
                        }
                                    },
                                ]
                        ],
                        ],
                    ]); ?>
</div>
