<?php 
    
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\JetProduct;

$this->title = 'Apps UnInstalled';
$this->params['breadcrumbs'][] = $this->title;
$type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
$chartData = [['Data','Installations']];
foreach($chart as $row){
  $chartData[] = [$row['formatted_date'],$row['uninstallations']];
}
?>
<div class="review-product-index">

  <h2><?= Html::encode($this->title) ?></h2>
   <select>
       <option <?= $type=='daily'?'selected="selected"':'' ?> onclick="window.location ='<?= Yii::$app->urlManagerFrontEnd->createUrl(['admin/walmart/uninstallations/index','type'=>'daily']) ?>'" >Daily</option>
       <option <?= $type=='monthly'?'selected="selected"':'' ?> onclick="window.location ='<?= Yii::$app->urlManagerFrontEnd->createUrl(['admin/walmart/uninstallations/index','type'=>'monthly']) ?>'">Monthly</option>
       <option <?= $type=='yearly'?'selected="selected"':'' ?> onclick="window.location ='<?= Yii::$app->urlManagerFrontEnd->createUrl(['admin/walmart/uninstallations/index','type'=>'yearly']) ?>'">Yearly</option>
   </select>
   <div id="chart_div"></div>

    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <?php 
                $type = Yii::$app->getRequest()->getQueryParam('type')?:'daily';
                echo GridView::widget([
                    'id'=>"jet_extention_details",
                    'dataProvider' => $dataProvider,
                    'columns' => [
                            'formatted_date',
                            'uninstallations',
                           [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{link}',//{view}
                                'buttons' => [
                                    'link' => function ($url,$model,$key) {
                                        return '<a data-pjax="0" href="'.Yii::$app->urlManagerFrontEnd->createUrl(['admin/walmart/uninstallations/view','from'=>$model['from_date'],'to'=>$model['to_date']]).'">View</a>'; 
                                },
                            
                                ],
                            ],
                        
                    ],
                ]); 
    ?>
<?php Pjax::end(); ?>
</div>

<script>
    google.charts.load('current', {packages: ['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawBasic);

  function drawBasic() {

        var data = google.visualization.arrayToDataTable(<?= json_encode($chartData,JSON_NUMERIC_CHECK) ?>);

        var options = {
          title: 'UnInstallations',
          hAxis: {
            title: 'Date',
          },
          vAxis: {
            title: 'UnInstallations'
          }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

        chart.draw(data, options);
      }
 </script>