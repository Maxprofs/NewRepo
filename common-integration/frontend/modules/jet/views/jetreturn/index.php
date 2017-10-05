<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\jet\components\Data;

$merchant_id= MERCHANT_ID;
$viewJetReturnDetails = \yii\helpers\Url::toRoute(['jetreturn/viewreturndetails']);
$this->title = 'Return Orders';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jet-return-index content-section">
<div class="form new-section">
	<div class="jet-pages-heading">
  <div class="title-need-help">
	    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
      <span id="instant-help" class="help_jet" style="cursor:pointer;" title="help"></span>
    </div>
    <div class="product-upload-menu">
	    <?= Html::a('Get Return Status', ['returnstatus'], ['class' => 'btn btn-primary','id'=>'get_return_status','data-step'=>'1','data-intro'=>'Get the Status of Submitted Returns on Jet.','data-position'=>'bottom']) ?>

      <?= Html::a('Fetch Jet Return', ['create'], ['class' => 'btn btn-primary','id'=>'fetch_jet_return','data-step'=>'2','data-intro'=>"Fetch newly-returned orders from Jet.",'data-position'=>'bottom']) ?>	
      <?= Html::a('Reset Filter', ['jetreturn/index'], ['data-position'=>'top','class' => 'btn btn-primary']) ?>     
    </div>
	    <div class="clear"></div>
     </div>
	<?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <div class="responsive-table-wrap">
    <?php 
      $editReturnAction = $viewReturnAction = false;
    ?>
    <?= GridView::widget([
    	'id'=>"return_grid",
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

			[
				'attribute' => 'returnid',
				//'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Jet Return Id'],
			],
			[
				'attribute' => 'order_reference_id',
				//'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Jet order id'],
			],
      [
       	'attribute' => 'agreeto_return',
       // 'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Are you ready to return the jet order'],
        // 'format' => 'raw',
       	'label' => 'Agree To Return',
        'filter' => ["1"=>"Yes","0"=>"No"],
       	'value' => function ($data) {
       		if($data->agreeto_return!="")
       		{
         		if($data->agreeto_return==0){
         			return "No";
         		}else
         			return "Yes";
       		}	
       		return "";
       	},
      ],
      [
        'attribute'=> 'status',
        //'headerOptions'=>['data-toggle'=>'tooltip1','title'=>'Order return status on jet'],
        'contentOptions'=>['style'=>'width: 250px;'],
        'filter' => ["created"=>"created","inprogress"=>"inprogress","completed by merchant"=>"completed by merchant"],
      ],
			
          [
          	'class' => 'yii\grid\ActionColumn',
        		'template' => '{viewreturn}{update}',
        		'buttons' => [
        			'viewreturn' => function ($url,$model) 
              {
                return Html::a(
                  '<span class="glyphicon glyphicon-eye-open" id="jet_return_view"> </span>',
                    'javascript:void(0)',['title'=>'Return detail on Jet','data-pjax'=>0,'onclick'=>'checkreturnstatus(this.id)','id'=>$model->returnid]);                         
              },
              /*'view' => function ($url,$model) use(&$viewReturnAction) {
                $options = ['title'=>Yii::t('app', 'view'),'data-pjax'=>"0"];
                if(!$viewReturnAction && $model->status == 'completed by merchant') {
                  $viewReturnAction = true;
                  $options['id'] = 'jet_return_view';
                  $options['data-step'] = '4';
                  $options['data-intro'] = "View the submitted Return.";
                  $options['data-position'] = 'left';
                }
                return $model->status == 'completed by merchant' ? Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url,$options) : '';
              },*/
              'update' => function ($url,$model) use(&$editReturnAction) {
                $options = ['title'=>Yii::t('app', 'update'), 'data-pjax'=>"0"];
                if(!$editReturnAction && $model->status == 'created') {
                  $editReturnAction = true;
                  $options['id'] = 'jet_return_edit';
                  $options['data-step'] = '2';
                  $options['data-intro'] = "After return is fetched, process the new return and submit it on Jet.";
                  $options['data-position'] = 'left';
                }
               	return $model->status == 'created' ? Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url,$options) : '';
              },
            ],
          ],
        ],
    ]); ?>
    </div>
	<?php Pjax::end(); ?>
  </div>
</div>
<div id="view_jet_return" style="display:none"></div>
<script type="text/javascript">
var csrfToken = $('meta[name="csrf-token"]').attr("content");
var submit_form = false;
$('body').on('keyup','.filters > td > input', function(event) 
{
  if (event.keyCode == 13) 
  {
  	if(submit_form === false) 
    {
      submit_form = true;
      $("#return_grid-filters").yiiGridView("applyFilter");
  	}
  }
});
$("body").on('beforeFilter', "#return_grid-filters" , function(event) {
	return submit_form;
});
$("body").on('afterFilter', "#return_grid-filters" , function(event) {
	submit_form = false;
});

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
	$('[data-toggle="tooltip1"]').tooltip({container: "th"});
});

function checkreturnstatus(return_id) 
{
  var url = '<?= $viewJetReturnDetails; ?>';
  var merchant_id = '<?= $merchant_id;?>';
  $('#LoadingMSG').show();
  $.ajax({
    method: "post",
    url: url,
    data: {merchant_id: merchant_id,return_id: return_id, _csrf: csrfToken}
  })
  .done(function (msg) {
      //console.log(msg);
      $('#LoadingMSG').hide();
      $('#view_jet_return').html(msg);
      $('#view_jet_return').css("display", "block");
      $('#view_jet_return #myModal').modal({
          keyboard: false,
          backdrop: 'static'
      })
  })
}

    $(function(){   
      var intro = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
            steps: [
              {
                element: '#fetch_jet_return',
                intro: 'Click this Button to Fetch new Returns from Jet.',
                position: 'bottom'
              },
              {
                element: '#get_return_status',
                intro: 'Click this Button to Get the Status of Submitted Returns on Jet.',
                position: 'bottom'
              },
              {
                element: '#jet_return_edit',
                intro: 'After Fetching Returns, Click Here to Process the New Return and Submit it on Jet.',
                position: 'bottom'
              },
              {
                element: '#jet_return_view',
                intro: 'Click Here to View the Submitted Return on Jet.',
                position: 'bottom'
              }
            ]
          });  
        setTimeout(function() {
          $('#instant-help').click(function(){
                intro.start();
          });
        },1000);  
    });
</script>

<?php $get = Yii::$app->request->get();
  if(isset($get['tour'])) : 
?>
  <script type="text/javascript">
    $(document).ready(function(){
        var returnQuicktour = introJs().setOptions({
                doneLabel: 'Next page',
                showStepNumbers: false,
                exitOnOverlayClick: false
            });
        setTimeout(function() {
            returnQuicktour.start().oncomplete(function() {
              window.location.href = '<?= Data::getUrl("jetconfiguration/index?tour") ?>';
          });
         },1000);      
      });
  </script>
<?php endif; ?>
