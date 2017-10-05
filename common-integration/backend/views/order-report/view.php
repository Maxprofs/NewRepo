<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
$this->title = 'Failed Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jet-order-import-error-index content-section">
<div class="form new-section">
    <div class="jet-pages-heading">
        <div class="title-need-help">
            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
        </div>
        <?= Html::a('Order Reporting', yii\helpers\Url::toRoute('order-report/index'), ['data-step'=>'8', 'data-position'=>'left', 'data-intro'=>'Back order report page' ,'class' => 'btn btn-primary']) ?>
        <div class="clear"></div>
    </div>

    <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
    <div class="responsive-table-wrap">
        <?= GridView::widget([
            'id' => "ordererror_grid",
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                //['class' => 'yii\grid\CheckboxColumn'],
                [
                    'attribute' => 'order_id',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Order Id'],
                ],
                [
                    'attribute'=> 'merchant_id',
                    'label'=>'Merchant Id',
                    'format' => 'raw',
                    'value'=> function($data){  
                        if($data['marketplace']=='jet')
                            $marketplaceUrl= Yii::getAlias('@webjeturl');      
                        elseif($data['marketplace']=='walmart')
                            $marketplaceUrl= Yii::getAlias('@webwalmarturl');  
                        else                     
                            $marketplaceUrl= Yii::getAlias('@webneweggurl');    
                        return Html::a($data['merchant_id'], $marketplaceUrl.'/site/managerlogin?ext='.$data['merchant_id'].'&&enter=true', ['target' => '_blank','title' => 'click to login as a merchant']);
                    }
                ],
                [
                    'attribute' => 'reason',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Reason'],
                ],                
                [
                    'attribute' => 'created_at',
                    'headerOptions' => ['data-toggle' => 'tooltip1', 'title' => 'Created At'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{vieworder}{cancel}{link}',
                    /*'buttons' => [ 
                    	'vieworder' => function ($url,$model) 
                		{
                        	return Html::a(
                                '<span class="glyphicon glyphicon-eye-open"> </span>',
                                'javascript:void(0)',['title'=>'Order detail on Jet','data-pjax'=>0,'onclick'=>'checkorderstatus(this.id)','id'=>$model->merchant_order_id]);                        	
                		},                   	
                		'cancel' => function ($url,$model) 
                		{
                			if ($model->status =="ready") 
                			{
                            	return Html::a(
                                    '<span>Cancel</span>',
                                    'javascript:void(0)',['title'=>'Cancel Order on Jet','data-pjax'=>0,'onclick'=>'cancel(this.id)','id'=>$model->merchant_order_id]);
                        	}
                		},                    	                        
                    ],*/
                ],
            ],
        ]); ?>
    </div>
    <?php Pjax::end(); ?>
    <?= Html::endForm(); ?>
    </div>
</div>