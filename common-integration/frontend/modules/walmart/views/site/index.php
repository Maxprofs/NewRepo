<?php 
use frontend\modules\walmart\components\Dashboard\Earninginfo;
// use frontend\modules\walmart\components\Dashboard\Setupprogress;
use frontend\modules\walmart\components\Dashboard\Productinfo;
use frontend\modules\walmart\components\Dashboard\OrderInfo;
use frontend\modules\walmart\components\Dashboard\LatestUpdates;
use frontend\modules\walmart\components\Dashboard\Stockinfo;
use frontend\modules\walmart\components\Data;

$this->title = 'Shopify Walmart Integration | CedCommerce';

$merchant_id=Yii::$app->user->identity->id;

/**productinfo**/
$PublishedProducts = Productinfo::getPublishedProducts($merchant_id);
$ProcessingProducts = Productinfo::getProcessingProducts($merchant_id);
$UnpublishedProducts = Productinfo::getUnpublishedProducts($merchant_id);
$NotUploadedProducts = Productinfo::getNotUploadedProducts($merchant_id);
$StagedProducts = Productinfo::getStagedProducts($merchant_id);
$TotalProducts = Productinfo::getTotalProducts($merchant_id);

$refreshDate = Productinfo::getLastRefreshDate($merchant_id);
/*$DeletedProducts = Productinfo::deletedProducts($merchant_id);*/
$OtherProducts = intval($TotalProducts)-(intval($PublishedProducts)+intval($ProcessingProducts)+intval($UnpublishedProducts)+intval($NotUploadedProducts)+intval($StagedProducts)/*+intval($DeletedProducts)*/);
if($OtherProducts < 0)
	$OtherProducts = 0;
$tempProductCount = Productinfo::getTempProductsCount($merchant_id);
$updatedProductCount = Productinfo::getProductsCountUpdatedToday($merchant_id);
$productsWithLowStock = Stockinfo::getInventoryUpdatesInfo($merchant_id);

// $tempProductCount = Productinfo::getTempProductsCount($merchant_id);

/**orderinfo**/
$CompletedOrders = OrderInfo::getCompletedOrdersCount($merchant_id);
$AcknowledgedOrders = OrderInfo::getAcknowledgedOrdersCount($merchant_id);
$CancelledOrders = OrderInfo::getCancelledOrdersCount($merchant_id);
$FailedOrders = OrderInfo::getFailedOrdersCount($merchant_id);
$TotalOrders = OrderInfo::getTotalOrdersCount($merchant_id);


/**earninginfo**/ 
$TodayEarning = Earninginfo::getTodayEarning($merchant_id);
$WeeklyEarning = Earninginfo::getWeeklyEarning($merchant_id);
$MonthlyEarning = Earninginfo::getMonthlyEarning($merchant_id);
$TotalEarning = Earninginfo::getTotalEarning($merchant_id);

$latestUpdates = LatestUpdates::fetchLatestUpdates();
?>
	<div class="container-fluid">
		<div class="row">
			<!-- <div class="col-md-2 sidebar">
			</div> -->
			<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 content-section">

				<div class="header-wrapper">
					
					<div class="middle-header">
						<div class="row">
							<div class="col-md-12">
								<div class="heading-section">
									<h3 class="page-title">ADMIN PANEL</h3>
									<p class="welcome"><?= Yii::$app->user->identity->username ?></p>
								</div>
							</div>
						</div>
					</div>
					<div class="bottom-header">
						<div class="row">
							<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
								<span class="earnings">STORE EARNING</span>
								<p class="days">TODAY</p>
								<p class="prices"><?= $TodayEarning;?> $</p>
							</div>
							<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
								<span class="earnings">STORE EARNING</span>
								<p class="days">THIS WEEK</p>
								<p class="prices"><?= $WeeklyEarning;?> $</p>
							</div>
							<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
								<span class="earnings">STORE EARNING</span>
								<p class="days">THIS MONTH</p>
								<p class="prices"><?= $MonthlyEarning;?> $</p>
							</div>
							<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
								<span class="earnings">STORE EARNING</span>
								<p class="days">TOTAL</p>
								<p class="prices"><?= $TotalEarning;?> $</p>
							</div>
						</div>
					</div>
				</div>

				<div class="main-content-wrapper container">
					<div class="row">
						<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
							<div class="tab-section new-section">
								<div class="row">
									<div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
										<ul class="nav nav-tabs">
											<li id="product" class="tabs active"><a data-toggle="tab" href="#home">Product Information</a></li>
											<li id="order" class="tabs"><a data-toggle="tab" href="#menu1">Order Information</a></li>
											<a class="refresh-icon" title="Refresh" href="<?=Yii::$app->request->baseUrl;?>/walmart/site/deletecache"><i class="fa fa-refresh" aria-hidden="true"></i></a>
											<div class="refresh_wrapper">
                                            <span class="last_refresh">Last Refresh : </span><p><?php /*echo date_format(date_create($refreshDate),"d/m/Y");*/echo $refreshDate ?></p>
                                            </div>
										</ul>

										<div class="tab-content">
											<div id="home" class="tab-pane fade in active">
												<div class="row">
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="live-box new-section grey-bg">
															<span class="heading earnings">Published Products</span>
															<div class="product-count">
																<h4><?= $PublishedProducts; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=PUBLISHED"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=PUBLISHED"><i class="fa fa-eye" aria-hidden="true"></i></a>
															</div>
														</div>
													</div>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="jet-review-box new-section purple-bg">
															<span class="heading earnings ">Item Processing </span>
															<div class="product-count">
																<h4><?= $ProcessingProducts; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=Item+Processing"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=Item+Processing"><i class="fa fa-binoculars" aria-hidden="true"></i>
																</a>
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="missing-listings new-section purple-bg">
															<span class="heading earnings">Unpublished Products</span>
															<div class="product-count">
																<h4><?php echo $UnpublishedProducts; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=UNPUBLISHED"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=UNPUBLISHED"><i class="fa fa-question-circle-o" aria-hidden="true"></i>
																</a>
															</div>
														</div>
													</div>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="archieves new-section grey-bg">
															<span class="heading earnings">StageProducts</span>
															<div class="product-count">
																<h4><?php echo $StagedProducts; ?></h4> 
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=STAGE"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=STAGE"><i class="fa fa-folder-open" aria-hidden="true"></i>
																</a>
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<div class="not-uploaded new-section black-bg">
															<span class="heading earnings">Not uploaded</span>
															<div class="upload-wrap">
																<div class="left">
																	<h4><?php echo $NotUploadedProducts; ?></h4>
																</div>
																<div class="right">
																	<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=Not+Uploaded"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
																	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/product/index?WalmartProductSearch[status]=Not+Uploaded"><span>know more</span></a>
																</div>
																<div class="clear"></div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="menu1" class="tab-pane fade">
												<div class="row">
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="live-box new-section grey-bg">
															<span class="heading earnings">Completed Orders</span>
															<div class="product-count">
																<h4><?php echo $CompletedOrders; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index?WalmartOrderDetailSearch[status]=completed"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index?WalmartOrderDetailSearch[status]=completed"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
															</div>
														</div>
													</div>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="jet-review-box new-section purple-bg">
															<span class="heading earnings ">Acknowledged Orders</span>
															<div class="product-count">
																<h4><?php echo $AcknowledgedOrders; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index?WalmartOrderDetailSearch[status]=acknowledged"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index?WalmartOrderDetailSearch[status]=acknowledged"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
																</a>
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="missing-listings new-section purple-bg">
															<span class="heading earnings">Cancelled Orders</span>
															<div class="product-count">
																<h4><?php echo $CancelledOrders; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index?WalmartOrderDetailSearch[status]=canceled"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index?WalmartOrderDetailSearch[status]=canceled"><i class="fa fa-window-close-o" aria-hidden="true"></i>
																</a>
															</div>
														</div>
													</div>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<div class="archieves new-section grey-bg">
															<span class="heading earnings">Failed Orders</span>
															<div class="product-count">
																<h4><?php echo $FailedOrders; ?></h4>
																<a href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderimporterror/index"><span>know more</span></a>
																<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderimporterror/index"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i>
																</a>
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
														<div class="not-uploaded new-section black-bg">
															<span class="heading earnings">Total Orders</span>
															<div class="upload-wrap">
																<div class="left">
																<h4><?php echo $TotalOrders + $FailedOrders; ?></h4>
																</div>
																<div class="right">
																	<a class="icon" href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
																	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartorderdetail/index"><span>know more</span></a>
																</div>
																<div class="clear"></div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
										<!-- Products Pie Chart --> 
										<div class="outer-wrapper">
											<div class="inner-wrapper" id="product_piechart">
												<section>
													<div class="productPie pie">
												    	<div class="caption">
												      		<span>Total Products<br><?php echo  $TotalProducts; ?></span>
												    	</div>
												    </div>
												    <ul class="productPie legend">
												      <li>
												        <em>Published Products</em>
												        <span><?php echo $PublishedProducts; ?></span>
												      </li>
												      <li>
												        <em>UnPublished Products</em>
												        <span><?php echo $UnpublishedProducts; ?></span> 
												      </li>
												      <li>
												        <em>Item Processing Products</em>
												        <span><?php echo $ProcessingProducts; ?></span>
												      </li>
												      <li>
												        <em>Not Uploaded Products</em>
												        <span><?php echo $NotUploadedProducts; ?></span>
												      </li>
												      <li>
												        <em>Stage Product</em>
												        <span><?php echo $StagedProducts; ?></span>
												      </li>
												      <li>
												        <em>Other Product</em>
												        <span><?php echo $OtherProducts; ?></span>
												      </li>
												    </ul>
												</section>
											</div>
										</div>

										<!-- Orders Pie Chart -->
										<div class="outer-wrapper">
											<div class="inner-wrapper" id="order_piechart" style="display: none;">
												<section>
													<div class="orderPie pie">
												    	<div class="caption">
												      		<span>Total Orders<br><?php echo $TotalOrders + $FailedOrders; ?></span>
												    	</div>
												    </div>
												    <ul class="orderPie legend">
												      <li>
												        <em>Completed Orders</em>
												        <span><?php echo $CompletedOrders ?></span>
												      </li>
												      <li>
												        <em>Acknowledged Orders</em>
												        <span><?php echo $AcknowledgedOrders ?></span>
												      </li>
												      <li>
												        <em>Cancelled Orders</em>
												        <span><?php echo $CancelledOrders ?></span>
												      </li>
												      <li>
												        <em>Failed Orders</em>
												        <span><?php echo $FailedOrders ?></span>
												      </li>
												    </ul>
												</section>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="box-update-sections">
								<div class="row">
									
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<div class="update-section new-section no-pad">
											<div class="update-heading grey-heading">
												<h5>Latest Updates</h5>
											</div>
											<div class="update-content">
												<ul class="update-list list-style update-list-custom-scroll">
												<?php if(count($latestUpdates)) : ?>
													<?php foreach ($latestUpdates as $latestUpdate) : ?>
													<?php 	$time = LatestUpdates::timeDifference($latestUpdate['created_at']);?>
													<li>
													<?php if(LatestUpdates::isNew($latestUpdate['created_at'])) : ?>
															<span class="label-new">new</span>
													<?php endif; ?>
														<p><a href="<?= Data::getUrl('latest-updates/view?id='.$latestUpdate['id']) ?>"><?= $latestUpdate['title'] ?></a></p>
														<span><?= $time." ago" ?></span>
													</li>
													<?php endforeach; ?>
												<?php else : ?>
													<li>No Latest Updates Found.</li>
												<?php endif; ?>
												</ul>
												<div class="scroller">
													<div class="scroller-inner"></div>
												</div>
											</div>
											<script type="text/javascript">
												$(document).ready(function(){
													var blockheight = $('.update-list-custom-scroll').height();
													$('.scroller-inner').css('height', blockheight);
													$('.scroller').on('scroll', function(){
														customscroll();										
													});
													
													function customscroll(){
														var scrltp = $('.scroller').scrollTop();
														$('.update-list-custom-scroll').css('top', -scrltp);
													}
												});
											</script>
										</div>
									</div>

									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<div class="inventory-update new-section no-pad">
												<div class="inventory-heading grey-heading">
													<h5>Inventory Updates</h5>
												</div>
												<div class="inventory-content">
													<ul class="update-list list-style">

													<?php /*$productsWithLowStock = 0;*/?>
														<?php if($productsWithLowStock['count']>0){?>
															<?php $i = 0;?>
															<?php foreach($productsWithLowStock['title'] as $value){?>
																<li>
																	<p>Attention !<a href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartproduct/index?WalmartProductSearch[title]=<?=urlencode($value);?>"> <?=$value?></a></p>
																	<span>Less than <?=$productsWithLowStock['minQty']?> in inventory.</span>
																</li>
																<?php $i++;?>
																<?php if($i==3){break;}?>
															<?php }?>
															<?php if($productsWithLowStock['count'] > 3){?>
																<li>
																	<a class="btn btn-primary" href="<?=Yii::$app->request->baseUrl;?>/walmart/walmartproduct/index?low=<?=$productsWithLowStock['minQty']?>" target="_blank">Check For More</a>
																</li>
															<?php }?>
														<?php }else{?>
															<li>
																<p>No New Updates.</p>
															</li>
														<?php }?>
														
													</ul>
												</div>
											</div>
									</div>
								</div>
							</div>
						</div>
					</div>


					<!--<div class="row">
						<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
							<div class="syncing-update new-section">
								<div class="syncing-heading grey-heading no-bg">
									<h5>Product Syncing Updates<a href="<?/*=Yii::$app->request->baseUrl;*/?>"><img class="refresh-img" src="<?/*= Yii::$app->request->baseUrl */?>/images/refresh.png"></a></h5>
								</div>
								<div class="syncing-content">
									<ul class="update-list list-style">
							
										<?php /*if($tempProductCount>0 || $updatedProductCount>0){*/?>
										<?php /*if($tempProductCount>0)
										{*/?>
										<li>
											<p><?/*=$tempProductCount;*/?> Products Fetched from Your Store</p>
											<span>sync them now <a href="<?/*=Yii::$app->request->baseUrl;*/?>/walmart/walmartproduct/index?tmp=1">click</a></span>
										</li>
										<?php /*
										}
										if($updatedProductCount>0){*/?>
										<li>
											<p><?/*= $updatedProductCount;*/?> Synced Products to walmart</p>
											<span>check now <a href="<?/*=Yii::$app->request->baseUrl;*/?>/walmart/walmartproduct/index?updated=<?/*=$updatedProductCount;*/?>">click</a></span>
										</li>
										<?php /*}}else{*/?>
										<li>
											<p>No New Updates.</p>
										</li>
										<?php /*}*/?>
									</ul>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="tool-box-section new-section no-pad">
									<div class="tool-box-heading grey-heading">
										<h5>Tool box</h5>
									</div>
									<div class="tool-box-content">
										<img src="<?/*= Yii::$app->request->baseUrl */?>/images/upload.png">
										<a class="tool-box-content-a" href="<?/*= Yii::$app->request->baseUrl */?>/walmart/walmartproduct/index?WalmartProductSearch[status]=Not+Uploaded"><span>Upload New Products </span></a>
									</div>
							</div>
							
						</div>
					</div>-->


				</div>
			</div>
		</div>
	</div>

<style type="text/css">
	.content-section {
	    padding: 0;
	}

</style>
	<?php $get = Yii::$app->request->get();
	if(isset($get['tour'])) : 
?>

<script type="text/javascript">
	$(document).ready(function(){
		var dashboardQuicktour = introJs().setOptions({
			doneLabel: 'Next page',
            showStepNumbers: false,
            exitOnOverlayClick: false,
            steps: [
              	{
	                element: '.bottom-header',
	                intro: 'You can view your  STORE EARNING from here.',
	                position: 'bottom',
	                scrollToElement: true,
              	},

				/*				
				{
	                element: '.store-setup-section',
	                intro: "Your PROGRESS in the Jet Integration Setup Process.",
	                position: 'bottom',
	                scrollToElement: true,
				},*/
				{
	                element: '#product',
	                intro: "Your Products Statistics.",
	                position: 'top',
	                scrollToElement: true,
				},
				{
	                element: '#order',
	                intro: "Your Orders Statistics.",
	                position: 'top',
	                scrollToElement: true,
				},
				/*{
	                element: '.syncing-update',
	                intro: "Get latest updates of Your product(s) syncing with Walmart.",
	                position: 'top',
	                scrollToElement: true,
				},
				{
					element: '.tool-box-content',
	                intro: "Upload your Products on Walmart.com.",
	                position: 'left',
	                scrollToElement: true,
				},*/
				{
					element: '.update-section',
	                intro: "Get all the latest updates related to Walmart Integration.",
	                position: 'bottom',
	                scrollToElement: true,
				},
				{
					element: '.inventory-update',
	                intro: "Get Inventory-related Alerts.",
	                position: 'bottom',
	                scrollToElement: true,
				}
            ]
      	});
		setTimeout(function () {
            dashboardQuicktour.start().oncomplete(function() {
                window.location.href = '<?= Data::getUrl("categorymap/index-new?tour") ?>';
            },1000);
        });

	});
</script>
<?php endif; ?>
