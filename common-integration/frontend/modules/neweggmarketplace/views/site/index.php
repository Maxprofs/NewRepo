<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 10/2/17
 * Time: 4:59 PM
 */
use frontend\modules\neweggmarketplace\components\Dashboard\Earninginfo;
use frontend\modules\neweggmarketplace\components\Dashboard\Productinfo;
use frontend\modules\neweggmarketplace\components\Dashboard\OrderInfo;
use frontend\modules\neweggmarketplace\components\Dashboard\LatestUpdates;
use frontend\modules\neweggmarketplace\components\Dashboard\Stockinfo;
use frontend\modules\neweggmarketplace\components\Data;

$this->title = 'Shopify Newegg Integration - CedCommerce';

?>
<?php

$merchant_id=Yii::$app->user->identity->id;

/**productinfo**/
$ActivatedProducts = Productinfo::getActivatedProducts($merchant_id);
$SubmittedProducts = Productinfo::getSubmittedProducts($merchant_id);
$DeactivatedProducts = Productinfo::getDeactivatedProducts($merchant_id);
$NotUploadedProducts = Productinfo::getNotUploadedProducts($merchant_id);
$ErrorProducts = Productinfo::getErrorProducts($merchant_id);
$TotalProducts = Productinfo::getTotalProducts($merchant_id);
$OtherProducts = intval($TotalProducts)-(intval($ActivatedProducts)+intval($SubmittedProducts)+intval($DeactivatedProducts)+intval($NotUploadedProducts)+intval($ErrorProducts));
if($OtherProducts < 0)
    $OtherProducts = 0;
//$tempProductCount = Productinfo::getTempProductsCount($merchant_id);
//$updatedProductCount = Productinfo::getProductsCountUpdatedToday($merchant_id);
$productsWithLowStock = Stockinfo::getInventoryUpdatesInfo($merchant_id);

// $tempProductCount = Productinfo::getTempProductsCount($merchant_id);

/**orderinfo**/
$ShippedOrders = OrderInfo::getShippedOrdersCount($merchant_id);
$UnshippedOrders = OrderInfo::getUnshippedOrdersCount($merchant_id);
$CancelledOrders = OrderInfo::getCancelledOrdersCount($merchant_id);
$FailedOrders = OrderInfo::getFailedOrdersCount($merchant_id);
$InvoicedOrders = OrderInfo::getInvoicedOrdersCount($merchant_id);
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
                <div class="bottom-header" id="store_earning">
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
                                    </ul>

                                    <div class="tab-content">
                                        <div id="home" class="tab-pane fade in active">
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="live-box new-section grey-bg">
                                                        <span class="heading earnings">Activated Products</span>
                                                        <div class="product-count">
                                                            <h4><?= $ActivatedProducts; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=ACTIVATED"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=ACTIVATED"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="jet-review-box new-section purple-bg">
                                                        <span class="heading earnings ">Submitted Products</span>
                                                        <div class="product-count">
                                                            <h4><?= $SubmittedProducts; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=SUBMITTED"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=SUBMITTED"><i class="fa fa-binoculars" aria-hidden="true"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="missing-listings new-section purple-bg">
                                                        <span class="heading earnings">Deactivated Products</span>
                                                        <div class="product-count">
                                                            <h4><?php echo $DeactivatedProducts; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=DEACTIVATED"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=DEACTIVATED"><i class="fa fa-question-circle-o" aria-hidden="true"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="archieves new-section grey-bg">
                                                        <span class="heading earnings">Upload with Error Products</span>
                                                        <div class="product-count">
                                                            <h4><?php echo $ErrorProducts; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=UPLOAD+WITH+ERROR"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=UPLOAD+WITH+ERROR"><i class="fa fa-folder-open" aria-hidden="true"></i>
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
                                                                <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=Not+Uploaded"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
                                                                <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[upload_status]=Not+Uploaded"><span>know more</span></a>
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
                                                        <span class="heading earnings">Shipped Orders</span>
                                                        <div class="product-count">
                                                            <h4><?php echo $ShippedOrders; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Shipped"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Shipped"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="jet-review-box new-section purple-bg">
                                                        <span class="heading earnings ">Unshipped Orders</span>
                                                        <div class="product-count">
                                                            <h4><?php echo $UnshippedOrders; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Unshipped"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Unshipped"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
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
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Voided"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Voided"><i class="fa fa-window-close-o" aria-hidden="true"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="archieves new-section grey-bg">
                                                        <span class="heading earnings">Failed Orders</span>
                                                        <div class="product-count">
                                                            <h4><?php echo $FailedOrders; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderimporterror/index"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderimporterror/index"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="live-box new-section grey-bg">
                                                        <span class="heading earnings">Invoiced Orders</span>
                                                        <div class="product-count">
                                                            <h4><?php echo $InvoicedOrders; ?></h4>
                                                            <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Shipped"><span>know more</span></a>
                                                            <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index?NeweggOrderDetailSearch[order_status_description]=Shipped"><i class="fa fa-list-alt" aria-hidden="true"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                    <div class="not-uploaded new-section black-bg">
                                                        <span class="heading earnings">Total Orders</span>
                                                        <div class="product-count">
                                                                <h4><?php echo $TotalOrders + $FailedOrders; ?></h4>
                                                                <a class="icon" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
                                                                <a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggorderdetail/index"><span>know more</span></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--<div class="row">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="not-uploaded new-section black-bg">
                                                        <span class="heading earnings">Total Orders</span>
                                                        <div class="upload-wrap">
                                                            <div class="left">
                                                                <h4><?php /*echo $TotalOrders + $FailedOrders; */?></h4>
                                                            </div>
                                                            <div class="right">
                                                                <a class="icon" href="<?/*=Yii::$app->request->baseUrl;*/?>/neweggmarketplace/neweggorderdetail/index"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></a>
                                                                <a href="<?/*=Yii::$app->request->baseUrl;*/?>/neweggmarketplace/neweggorderdetail/index"><span>know more</span></a>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>-->
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
                                                        <em>Activated Products</em>
                                                        <span><?php echo $ActivatedProducts; ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Submitted Products</em>
                                                        <span><?php echo $SubmittedProducts; ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Deactivated Products</em>
                                                        <span><?php echo $DeactivatedProducts; ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Not Uploaded Products</em>
                                                        <span><?php echo $NotUploadedProducts; ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Upload with error Product</em>
                                                        <span><?php echo $ErrorProducts; ?></span>
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
                                                        <em>Shipped Orders</em>
                                                        <span><?php echo $ShippedOrders ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Unshipped Orders</em>
                                                        <span><?php echo $UnshippedOrders ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Cancelled Orders</em>
                                                        <span><?php echo $CancelledOrders ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Failed Orders</em>
                                                        <span><?php echo $FailedOrders ?></span>
                                                    </li>
                                                    <li>
                                                        <em>Invoiced Orders</em>
                                                        <span><?php echo $InvoicedOrders?></span>
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
                                                        <?php 	$time = LatestUpdates::timeDifference($latestUpdate['updated_at']);?>
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

                                                <?php $productsWithLowStock = 0;?>
                                                <?php if($productsWithLowStock['count']>0){?>
                                                    <?php $i = 0;?>
                                                    <?php foreach($productsWithLowStock['title'] as $value){?>
                                                        <li>
                                                            <p>Attention !<a href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[title]=<?=urlencode($value);?>"> <?=$value?></a></p>
                                                            <span>Less than <?=$productsWithLowStock['minQty']?> in inventory.</span>
                                                        </li>
                                                        <?php $i++;?>
                                                        <?php if($i==3){break;}?>
                                                    <?php }?>
                                                    <?php if($productsWithLowStock['count'] > 3){?>
                                                        <li>
                                                            <a class="btn btn-primary" href="<?=Yii::$app->request->baseUrl;?>/neweggmarketplace/neweggproduct/index?low=<?=$productsWithLowStock['minQty']?>" target="_blank">Check For More</a>
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
                                <a class="tool-box-content-a" href="<?/*= Yii::$app->request->baseUrl */?>/neweggmarketplace/neweggproduct/index?NeweggProductSearch[status]=Not+Uploaded"><span>Upload New Products </span></a>
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
                        element: '#store_earning',
                        intro: 'You can view your  STORE EARNING from here.',
                        position: 'bottom',
//                        scrollToElement: true,
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
                        intro: "Your Product’s Order Statistics.",
                        position: 'top',
                        scrollToElement: true,
                    },
                    /*{
                        element: '.syncing-update',
                        intro: "Get latest updates of Your product(s) syncing with Newegg.",
                        position: 'top',
                        scrollToElement: true,
                    },
                    {
                        element: '.tool-box-content',
                        intro: "Upload your Products on Newegg.com.",
                        position: 'left',
                        scrollToElement: true,
                    },*/
                    {
                        element: '.update-section',
                        intro: "Get all the latest updates related to Newegg.",
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
