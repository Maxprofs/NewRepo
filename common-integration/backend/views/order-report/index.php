<?php
use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\WalmartRecurringPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$url = \yii\helpers\Url::toRoute(['order-report/index']);
$this->title = 'Fialed Order Report';
$this->params['breadcrumbs'][] = $this->title;
$isToday=false;
if(date('Y-m-d')==$value){
    $isToday=true;
    $value=date('d-m-Y',strtotime($value));
}   
?>
<style type="text/css">
    h2{
    text-align: left;
    }
    .fa, .fa-5x {
        margin-left: 5%;
    }
    .small-box-footer {
        margin-left: -4%;
    }
    .inner > p {
        margin: 5%;
        
    }
    h1 {
        float: right;
        margin-right: 10%;
        margin-top: 5%;
    }
    a:HOVER {
        text-decoration: none;
    }
    .fa ,h1,.small-box-footer,.inner > p{
        color: white;
    }
    .outer_div{
        background-color: #f5f5f5; 
        
        border-top: 2px solid #DDD;
        padding: 10px 15px;
        margin: 1px;
        margin-bottom: 1px;
    }
    .live-row,.outer_div_live a {
        font-weight:bold;
        font-size: 16px;
        color:#bb321f;
        
    }
    .outer_div_live{
        border-bottom: 1px solid #bb321f;
    }
    .fa-arrow-circle-right{
        margin-left: 56%;
    }       
</style>
<div class="failed-order-header" style="width:70%">
    <div style="float:left;margin-bottom:10px">
    <label>Failed Orders On</label>
    <select class="form-control" onchange="changeDuration(this)">
         <option value=""></option>
        <option value="1 DAY">1 Day</option>
        <option value="2 DAY">2 Days</option>
        <option value="1 WEEK">1 Week</option>
    </select>
    </div>
    <div style="float:left;margin-left:20px;margin-bottom:10px">
    <label>Failed Orders Date</label>
    <?php if($isToday)
    {
        echo DatePicker::widget([
        'name' => 'date',
        'value' => $value,
        'id'=>'date-filter',
        'template' => '{addon}{input}',
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy',
                
            ],
        ]);
    }
    else
    {
        echo DatePicker::widget([
        'name' => 'date',
        'value' => '',
        'id'=>'date-filter',
        'template' => '{addon}{input}',
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy',
                
            ],
        ]);
    }?>
    </div>
</div>
<div style="clear:both;"></div>
<div class="failed-order-report">
    <div id="failed-count-data" class="row">
        <?php foreach ($data as $key=>$val) {
        ?>
        <a href="<?=Yii::$app->request->baseUrl;?>/order-report/view?param=<?=$param?>&marketplace=<?=$key?>&value=<?=$value?>">
            <div class="col-lg-3 col-xs-6 count-failed-<?=$key?>">
              <!-- small box -->
              <div class="small-box bg-aqua" style="background-color: #bb321f" >
                    <div class="inner" >
                        <span><i aria-hidden="true" class="fa fa-exclamation-triangle fa-3x"></i></span>
                        <span><h1><?php echo $val['count'];?></h1></span>
                      <p><?=$key?> Failed Orders</p>
                    </div>
                    <div class="outer_div outer_div_live">
                        More info ...<i class="fa live-row fa-arrow-circle-right"></i>
                    </div>
              </div>
            </div>
        </a>
        <?php 
        }?>
    </div>            
</div>
<script type="text/javascript">
    function changeDuration(node){
        var duration = $(node).val();
        if(duration)
        callAjaxRequest("duration",duration);
    }
    $(document).on('click','.day',function(){
        var date=$('#date-filter').val();
        callAjaxRequest("date",date);
    });
    function callAjaxRequest(param,param_value)
    {
        $.ajax({
           type: "POST",
           data: {param:param,value:param_value,isAjax:true},
           url: "<?php echo $url;?>",
           success: function(countData)
           {
                var html='';
                var faliedCountData=[];
                faliedCountData=JSON.parse(countData);
                console.log(faliedCountData);
                $.each(faliedCountData['data'], function( index, value ) 
                {
                    html+='<a href="<?php echo Yii::$app->request->baseUrl;?>/order-report/view?param='+faliedCountData['param']+'&marketplace='+index+'&value='+faliedCountData['value']+'"><div class="col-lg-3 col-xs-6 count-failed-'+index+'"><div class="small-box bg-aqua" style="background-color: #bb321f" ><div class="inner" ><span><i aria-hidden="true" class="fa fa-exclamation-triangle fa-3x"></i></span><span><h1>'+value['count']+'</h1></span><p>'+index+' Failed Orders</p></div><div class="outer_div outer_div_live">More info ...<i class="fa live-row fa-arrow-circle-right"></i></div></div></div></a>';
                });
                $('#failed-count-data').html('');
                $('#failed-count-data').html(html);
           }
        });
    }
</script>