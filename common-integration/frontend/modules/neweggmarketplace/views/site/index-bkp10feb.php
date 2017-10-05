<?php
use frontend\modules\neweggmarketplace\components\Data;
?>
<div class="site-index">

<?php 
if(!\Yii::$app->user->isGuest) 
{
?>
	<div class="bs-component">
        <div class="alert alert-dismissible alert-success" >
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong style="float:left;">welcome <?php echo str_replace(".myshopify.com","",Yii::$app->user->identity->username);?> ! </strong>
			<!-- <div style="float:right"> How to start ? <a class="alert-link" href="<?= Yii::$app->request->baseUrl ?>/how-to-sell-on-jet-com" target="_blank">Click here</a></div> -->
			<div style="clear:both"></div>
        </div>
    </div>

    <!-- Dashboard Starts From Here -->
    <div class="row">
        <div class="col-lg-3 col-md-3 col-xs-12 col-sm-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?= '0';//$dashboard['availableProduct'];?></h3>
                    <p>Live on Walmart</p>
                </div>
                <div class="icon">
                    <i class="fa fa-cart-plus"></i>
                </div>
                <a href="<?= Data::getUrl('walmartproduct/index'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-3 col-md-3 col-xs-12 col-sm-6">
            <!-- small box -->
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3><?= '0';//$dashboard['readytoshipOrders'];?></h3>
                    <p>Ready to Ship</p>
                </div>
                <div class="icon">
                    <i class="fa fa-truck"></i>
                </div>
                <a href="<?= Data::getUrl('walmartorderdetail/index'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
         <div class="col-lg-3 col-md-3 col-xs-12 col-sm-6">
            <!-- small box -->
            <div class="small-box bg-light-blue-active">
                <div class="inner">
                    <h3><?= '0';//$dashboard['totalOrders'];?></h3>
                    <p>Orders Fulfilled</p>
                </div>
                <div class="icon">

                    <i class="fa fa-bar-chart"></i>
                </div>
                <a href="<?= Data::getUrl('walmartorderdetail/index'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
        <div class="col-lg-3 col-md-3 col-xs-12 col-sm-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?= "$".'0';//$dashboard['total'];?></h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fa fa-usd"></i>
                </div>
                <a href="<?= Data::getUrl('walmartorderdetail/index'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div><!-- ./col -->
    </div><!-- /.row -->
</div>
<?php } ?>
