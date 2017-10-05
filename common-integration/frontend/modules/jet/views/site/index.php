<?php
use frontend\modules\jet\components\Dashboard\Setupprogress;
use frontend\modules\jet\components\Dashboard\Earninginfo;
use frontend\modules\jet\components\Dashboard\OrderInfo;
use frontend\modules\jet\components\Dashboard\Productinfo;
use frontend\modules\jet\components\Dashboard\Stockinfo;
use frontend\modules\jet\components\Dashboard\LatestUpdates;
use frontend\modules\jet\components\Data;

$merchant_id=MERCHANT_ID;

/*
	Object creation
*/
$objProductInfo       = new Productinfo();
$objOrderInfo         = new OrderInfo();
$objEarningInfo       = new Earninginfo();
$objSetupProgressInfo = new Setupprogress();
$objStockInfo         = new Stockinfo();
$objLatestUpdateInfo  = new LatestUpdates();
/**productinfo**/
$type="";
$TotalProducts = $objProductInfo->getTotalProducts($merchant_id,$type);
$LiveProducts = $objProductInfo->getLiveProducts($merchant_id);
$UnderReviewProducts = $objProductInfo->getUnderReviewProducts($merchant_id);
$ArchivedProducts = $objProductInfo->getArchivedProducts($merchant_id);
$ExcludedProducts = $objProductInfo->getExcludedProducts($merchant_id);
$MissingDataProducts = $objProductInfo->getMissingDataProducts($merchant_id);
$Unauthorized = $objProductInfo->getUnauthorizedProducts($merchant_id);
$NotUploadedProducts = $objProductInfo->getNotUploadedProducts($merchant_id);
$updatedProductCount = $objProductInfo->getProductsCountUpdatedToday($merchant_id);
$tempProductCount = $objProductInfo->getTempProductsCount($merchant_id);
$CategoryNotMapped = $objProductInfo->getCategoryNotMappedCount($merchant_id);

$productsWithLowStock = $objStockInfo->getInventoryUpdatesInfo($merchant_id);

/**orderinfo**/
$completedOrdersCount = $objOrderInfo->getCompletedOrdersCount($merchant_id);
$acknowledgeOrdersCount = $objOrderInfo->getAcknowledgedOrdersCount($merchant_id);
$inprogressOrdersCount = $objOrderInfo->getInprogressOrdersCount($merchant_id);
$totalOrdersCount = $objOrderInfo->getTotalOrdersCount($merchant_id);
$failedOrdersCount = $objOrderInfo->getFailedOrdersCount($merchant_id);
/**progressinfo**/
$TestApiStatus = $objSetupProgressInfo->getTestApiStatus($merchant_id);
$LiveApiStatus = $objSetupProgressInfo->getLiveApiStatus($merchant_id);
$ProductImportStatus = $objSetupProgressInfo->getProductImportStatus($merchant_id);
$CategoryMapStatus = $objSetupProgressInfo->getCategoryMapStatus($merchant_id);
$AttributeMapStatus = $objSetupProgressInfo->getAttributeMapStatus($merchant_id);
$ProfileProgress = $objSetupProgressInfo->getProfileProgress($merchant_id);
/**earninginfo**/
$TodayEarning = $objEarningInfo->getTodayEarning($merchant_id);
$WeeklyEarning = $objEarningInfo->getWeeklyEarning($merchant_id);
$MonthlyEarning = $objEarningInfo->getMonthlyEarning($merchant_id);
$TotalEarning = $objEarningInfo->getTotalEarning($merchant_id);
/**latest updates**/
$latestUpdates = $objLatestUpdateInfo->fetchLatestUpdates();
?>
<head>
	<meta content="Jet Shopify Integration - Sell Shopify Store Products on jet.com" name="title">  
        <meta content="Jet Shopify Integration facilitates Shopify store owners to have an ultimate selling experience by mapping their Shopify store with Jet It synchronizes product listing, order management" name="description">  
        <meta content="jet shopify integration, sell your shopify store products on jet, jet api integration, jet integration app, jet marketplace, jet to shopify, shopify and jet, sell on jet marketplace, jet marketplace integration, sell on jet, integrate jet with shopify, selling on jet, shopify inventory management integration, inventory management" name="tags">
</head>
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
							<p class="prices"> <?= Data::custom_number_format($TodayEarning,3) ?> $</p>
						</div>
						<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
							<span class="earnings">STORE EARNING</span>
							<p class="days">THIS WEEK</p>
							<p class="prices"> <?= Data::custom_number_format($WeeklyEarning,3) ?> $</p>
						</div>
						<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
							<span class="earnings">STORE EARNING</span>
							<p class="days">THIS MONTH</p>
							<p class="prices"><?= Data::custom_number_format($MonthlyEarning,3) ?> $</p>
						</div>
						<div class="col-md-3 col-lg-3 col-sm-6 col-xs-12">
							<span class="earnings">STORE EARNING</span>
							<p class="days">TOTAL</p>
							<p class="prices" id="total"><?= Data::custom_number_format($TotalEarning,3) ?> $</p>
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
									</ul>

									<div class="tab-content">
										<div id="home" class="tab-pane fade in active">
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="live-box new-section grey-bg">
														<span class="heading earnings">Live Products</span>
														<div class="product-count">
															<h4><?= $LiveProducts; ?></h4>

															<a data-toggle="tooltip" title="Click to see 'Available for Purchase state' products on jet" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Available+for+Purchase"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see 'Available for Purchase state' products on jet" class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Available+for+Purchase"><i class="fa fa-line-chart" aria-hidden="true"></i></a>
														</div>
													</div>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="jet-review-box new-section purple-bg">
														<span class="heading earnings ">Under jet review</span>
														<div class="product-count">
															<h4><?= $UnderReviewProducts; ?></h4>

															<a data-toggle="tooltip" title="Click to see 'Under review state' products on jet" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Under+Jet+Review"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see 'Under review state' products on jet" class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Under+Jet+Review"><i class="fa fa-eye" aria-hidden="true"></i>
															</a>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="missing-listings new-section purple-bg">
														<span class="heading earnings">Missing listing data</span>
														<div class="product-count">
															<h4><?= $MissingDataProducts; ?></h4>

															<a  data-toggle="tooltip" title="Click to see 'Missing Listing Data state' products on jet"  href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Missing+Listing+Data"><span>know more</span></a>
															<a  data-toggle="tooltip" title="Click to see 'Missing Listing Data state' products on jet"  class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Missing+Listing+Data"><i class="fa fa-question-circle-o" aria-hidden="true"></i>
															</a>
														</div>
													</div>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="archieves new-section grey-bg">
														<span class="heading earnings">Archived</span>
														<div class="product-count">
															<h4><?= $ArchivedProducts; ?></h4>

															<a data-toggle="tooltip" title="Click to see 'Archived state' products on jet" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Archived"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see 'Archived state' products on jet" class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Archived"><i class="fa fa-folder-open" aria-hidden="true"></i>
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
																<h4><?= $NotUploadedProducts; ?></h4>
															</div>
															<div class="right">

																<a data-toggle="tooltip" title="Click to see 'Not Uploaded state' products on jet" class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Not+Uploaded"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
</a>
																<a data-toggle="tooltip" title="Click to see 'Not Uploaded state' products on jet" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[status]=Not+Uploaded"><span>know more</span></a>
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
															<h4><?= $completedOrdersCount; ?></h4>

															<a data-toggle="tooltip" title="Click to see orders shipped on jet" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index?JetOrderDetailSearch[status]=complete"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see orders shipped on jet"class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index?JetOrderDetailSearch[status]=complete"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
														</div>
													</div>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="jet-review-box new-section purple-bg">
														<span class="heading earnings ">Acknowledged Orders</span>
														<div class="product-count">
															<h4><?= $acknowledgeOrdersCount; ?></h4>

															<a data-toggle="tooltip" title="Click to see orders 'received' from jet" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index?JetOrderDetailSearch[status]=acknowledged"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see orders 'received' from jet" class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index?JetOrderDetailSearch[status]=acknowledged"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
															</a>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="missing-listings new-section purple-bg">
														<span class="heading earnings">Inprogress Orders</span>
														<div class="product-count">
															<h4><?=$inprogressOrdersCount; ?></h4>
															<a data-toggle="tooltip" title="Click to see orders 'Inprogress' on jet" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index?JetOrderDetailSearch[status]=inprogress"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see orders 'Inprogress' on jet"  class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index?JetOrderDetailSearch[status]=inprogress"><i class="fa fa-truck" aria-hidden="true"></i>

															</a>
														</div>
													</div>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="archieves new-section grey-bg">
														<span class="heading earnings">Failed Orders</span>
														<div class="product-count">
															<h4><?= $failedOrdersCount; ?></h4>
															<a data-toggle="tooltip" title="Click to see orders 'Unable to accept' from jet"  href="<?=Yii::getAlias('@webjeturl');?>/jetorderimporterror/index"><span>know more</span></a>
															<a data-toggle="tooltip" title="Click to see orders 'Unable to accept' from jet"  class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetorderimporterror/index"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i>
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
															<h4><?= $totalOrdersCount + $failedOrdersCount; ?></h4>
															</div>
															<div class="right">

																<a data-toggle="tooltip" title="Click to see orders 'total order' process through app" class="icon" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
</a>
																<a data-toggle="tooltip" title="Click to see orders 'total order' process through app" href="<?=Yii::getAlias('@webjeturl');?>/jetorderdetail/index"><span>know more</span></a>
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
											      		<span>Total Products <br><?php if($type){ echo "(with variants)";}?><br><?= $TotalProducts; ?></span>
											    	</div>
											    </div>
											    <ul class="productPie legend">
											      <li>
											        <em>Live Products</em>
											        <span><?= $LiveProducts; ?></span>
											      </li>
											      <li>
											        <em>Under Review Products</em>
											        <span><?= $UnderReviewProducts; ?></span>
											      </li>
											      <li>
											        <em>Archived Products</em>
											        <span><?= $ArchivedProducts; ?></span>
											      </li>
											      <li>
											        <em>Not Uploaded Products</em>
											        <span><?= $NotUploadedProducts; ?></span>
											      </li>
											      <li>
											        <em>Missing Data Products</em>
											        <span><?= $MissingDataProducts; ?></span>
											      </li>
											      <li>
											        <em>Excluded Products</em>
											        <span><?= $ExcludedProducts; ?></span>
											      </li>
											      <li>
											        <em>Unauthorized Products</em>
											        <span><?= $Unauthorized; ?></span>
											      </li>
											      <li>
												        <em>Category Not Mapped</em>
												        <span><?= $CategoryNotMapped; ?></span> 
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
											      		<span>Total Orders<br><?= $totalOrdersCount + $failedOrdersCount; ?></span>
											    	</div>
											    </div>
											    <ul class="orderPie legend">
											      <li>
											        <em>Completed Orders</em>
											        <span><?= $completedOrdersCount ?></span>
											      </li>
											      <li>
											        <em>Acknowledged Orders</em>
											        <span><?= $acknowledgeOrdersCount ?></span>
											      </li>
											      <li>
											      	<em>Inprogress Orders</em>
											      	<span><?=$inprogressOrdersCount; ?></span>
											      </li>											      
											      <li>
											        <em>Failed Orders</em>
											        <span><?= $failedOrdersCount ?></span>
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
											<ul class="update-list list-style">
											<?php if(count($latestUpdates)) : ?>
												<?php foreach ($latestUpdates as $latestUpdate) : ?>
												<?php 	$time = $objLatestUpdateInfo->timeDifference($latestUpdate['updated_at']);?>
												<li>
													<p><a href="<?= Data::getUrl('latest-updates/view?id='.$latestUpdate['id']) ?>"><?= $latestUpdate['title'] ?></a></p>
													<span><?= $time." ago" ?></span>
												</li>
												<?php endforeach; ?>
											<?php else : ?>
												<li>No Latest Updates Found.</li>
											<?php endif; ?>
											</ul>
										</div>
									</div>
								</div>

								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<div class="inventory-update new-section no-pad">
										<div class="inventory-heading grey-heading">
											<h5>Inventory Updates</h5>
										</div>
										<div class="inventory-content">
											<ul class="update-list list-style">
												<?php if($productsWithLowStock['count']>0)
												{
												    $i = 0;
												    foreach($productsWithLowStock['title'] as $value)
												    {
												        ?>
														<li>
															<p>Attention !<a href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?JetProductSearch[title]=<?=urlencode($value);?>"> <?=$value?></a></p>
															<span>Less than <?=$productsWithLowStock['minQty']?> in inventory.</span>
														</li>
														<?php $i++;?>
														<?php if($i==3){break;}?>
													<?php }?>
													<?php if($productsWithLowStock['count'] > 3){?>
														<li>
															<a class="btn btn-primary" href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?low=<?=$productsWithLowStock['minQty']?>" target="_blank">Check For More</a>
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
				<!-- <div class="row">
				
					<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
						<div class="syncing-update new-section">
							<div class="syncing-heading grey-heading no-bg">
								<h5>Product Syncing Updates<a href="<?=Yii::getAlias('@webjeturl');?>"><img class="refresh-img" src="<?= Yii::$app->request->baseUrl ?>/images/refresh.png"></a></h5>
							</div>
							<div class="syncing-content">
								<ul class="update-list list-style">
									<?php if($tempProductCount>0 || $updatedProductCount>0){?>
									<?php if($tempProductCount>0)
									{?>
									<li>
										<p><?=$tempProductCount;?> Products Fetched from Your Store</p>
										<span>sync them now <a href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?tmp=1">click</a></span>
									</li>
									<?php 
									}
									if($updatedProductCount>0){?>
									<li>
										<p><?= $updatedProductCount;?> Synced Products to Jet</p>
										<span>check now <a href="<?=Yii::getAlias('@webjeturl');?>/jetproduct/index?updated=<?=$updatedProductCount;?>">click</a></span>
									</li>
									<?php }}else{?>
									<li>
										<p>No New Updates.</p>
									</li>
									<?php }?>
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
								<img src="<?= Yii::$app->request->baseUrl ?>/images/upload.png"/>
								<a class="tool-box-content-a" href="<?= Yii::getAlias('@webjeturl') ?>/jetproduct/index?JetProductSearch[status]=Not+Uploaded&sort=-upc"><span>Upload New Products </span></a>
							</div>
						</div>	
					</div>
				</div> -->
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
				{
	                element: '#product',
	                intro: "Your Products Statistics.",
	                position: 'top',
	                scrollToElement: true,
				},
				{
	                element: '#order',
	                intro: "Your Product’s Order Statistics.",
	                position: 'top',
	                scrollToElement: true,
				},				
				
				{
					element: '.update-section',
	                intro: "Get all the latest updates related to Jet.",
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
      		window.location.href = '<?= Data::getUrl("categorymap/index?tour") ?>';
    		});
        },1000);
	});
</script>
<?php endif; ?>
