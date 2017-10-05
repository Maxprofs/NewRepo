<?php 

namespace backend\modules\walmart\components;
use Yii;
use common\models\JetOrderDetail;
use common\models\JetProduct;
use common\models\JetActiveMerchants;
use yii\base\Component;
use frontend\components\ShopifyClientHelper;

class ReportsDashboard extends Component
{
	public function syncPaymentData(){

		
		
		
		$connection = Yii::$app->getDb();
		$query = "SELECT id as merchant_id,username,auth_key  FROM `user` where id=484";
        $merchantCollection = $connection->createCommand($query)->queryAll();
        foreach($merchantCollection as $merchant){

        	$sc = new ShopifyClientHelper($merchant['username'], $merchant['auth_key'], PUBLIC_KEY, PRIVATE_KEY);
        	print_r($merchant);
        	$charges = $sc->call('GET', '/admin/application_charges.json',array('since_id'=>0));

        	
        	print_r($charges);
        	$charges = $sc->call('GET', '/admin/recurring_application_charges.json');

        	
        	print_r($charges);
        	
        		
        }
        die;
	}

	public function getRatingData(){
		$url = 'https://apps.shopify.com/5eabff626f2420c086bbba243b69d22a/reviews.json';

		$handle=curl_init($url);
		curl_setopt($handle, CURLOPT_VERBOSE, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($handle);
		$rating = json_decode($content,true);
		$data['ratingValue'] = $rating['overall_rating'];
		$data['reviewCount'] = count($rating['reviews']);
		
	/*	preg_match_all('/<meta itemprop="ratingValue" content="(.*?)">/s',$content,$estimates);
		$data['ratingValue'] = $estimates[1][0];
		preg_match_all('/<meta itemprop="bestRating" content="(.*?)">/s',$content,$estimates);
		$data['bestRating'] = $estimates[1][0];
		preg_match_all('/<meta itemprop="reviewCount" content="(.*?)">/s',$content,$estimates);
		$data['reviewCount'] = $estimates[1][0];*/
		
		
		return $data;
	}

	/*
	 *	function for getting count of paid client with no revenue and no live products
	 */
	public function getNoRevenueNoProductsClientsCount($connection=false)
	{

		if(!$connection)
			$connection=Yii::$app->getDb();
		/*By Sanjeev*///$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM (Select * from `".Data::EXTENSIONS_TABLE."` where `".Data::EXTENSIONS_TABLE."`.`status` = 'Purchased') AS `jed` LEFT JOIN (Select * from `".Data::MAIN_PRODUCT_TABLE."` where `".Data::MAIN_PRODUCT_TABLE."`.`status`='PUBLISHED') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select * from `".Data::ORDER_TABLE."` where `".Data::ORDER_TABLE."`.`status` = 'completed') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1) FINAL";
		//$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `".Data::EXTENSIONS_TABLE."` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Purchased' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ) FINAL ";
		//echo $query;die();

        /* BY Shivam */$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM (Select DISTINCT `merchant_id` from `walmart_extension_detail` where `walmart_extension_detail`.`status` = 'Purchased') AS `jed` LEFT JOIN (Select DISTINCT `merchant_id` from `walmart_product` where `walmart_product`.`status` != 'PUBLISHED') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select DISTINCT `merchant_id` from `walmart_order_details` where `walmart_order_details`.`status` != 'completed') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1) FINAL";

        $count = $connection->createCommand($query)->queryOne();
		return isset($count['count'])?$count['count']:0;
	}

	/*
	 *	function for getting count of free client with no revenue and no live products
	 */
	public function getFreeNoRevenueNoProductsClientsCount($connection=false)
	{
		if(!$connection)
			$connection=Yii::$app->getDb();
		//$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `".Data::EXTENSIONS_TABLE."` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ) FINAL ";
		/*By Sanjeev*/$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM (Select * from `".Data::EXTENSIONS_TABLE."` where `".Data::EXTENSIONS_TABLE."`.`status` = 'Not Purchase') AS `jed` LEFT JOIN (Select * from `".Data::MAIN_PRODUCT_TABLE."` where `".Data::MAIN_PRODUCT_TABLE."`.`status` != 'PUBLISHED') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select * from `".Data::ORDER_TABLE."` where `".Data::ORDER_TABLE."`.`status` != 'completed') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ) FINAL";
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}

	/*
	 *	function for getting client configuration sataus
	 */
	public function getClientConfigurationStatus($connection=false)
	{
		if(!$connection)
			$connection=Yii::$app->getDb();

		$query = "SELECT count(*) as count FROM `".Data::EXTENSIONS_TABLE."` `wed` LEFT JOIN `".Data::CONFIG_TABLE."` `wc` ON `wed`.`merchant_id` = `wc`.`merchant_id` WHERE `wed`.`app_status`='install' AND `wc`.`merchant_id` IS NULL";
		$count = $connection->createCommand($query)->queryOne();
			$data['liveNotConfigured'] = isset($count['count'])?$count['count']:0;
		
		return $data;
	}

	/*
	 *	function for getting count of paid client with no revenue but having live products
	 */
	public function getNoRevenueWithLiveProductsClientsCount($connection=false)
	{
		if(!$connection)
			$connection=Yii::$app->getDb();
		//$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `".Data::EXTENSIONS_TABLE."` AS `jed` LEFT JOIN `".Data::PRODUCT_TABLE."` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='PUBLISHED' LEFT JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Purchased' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products > 0 ) FINAL ";
		/*By Sanjeev*///$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM (Select * from `".Data::EXTENSIONS_TABLE."` where `".Data::EXTENSIONS_TABLE."`.`status` = 'Purchased') as `jed` LEFT JOIN ( Select * from `".Data::PRODUCT_TABLE."` where `".Data::PRODUCT_TABLE."`.`status`='PUBLISHED') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select * from `".Data::ORDER_TABLE."` where `".Data::ORDER_TABLE."`.`status` = 'complete') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products > 0) FINAL";
		/*By Shivam*/$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM (Select DISTINCT `merchant_id` from `walmart_extension_detail` where `walmart_extension_detail`.`status` = 'Purchased') as `jed` LEFT JOIN ( Select DISTINCT `merchant_id` from `walmart_product` where `walmart_product`.`status`='PUBLISHED') as `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` LEFT JOIN (Select DISTINCT `merchant_id` from `walmart_order_details` where `walmart_order_details`.`status` = 'completed') as `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products > 0) FINAL";
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}


	/**
	 * function get count of clients who have revenue on free app
	 *
	 */
	public function getFreeAppWithRevenue($connection=false){
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `".Data::EXTENSIONS_TABLE."` AS `jed` LEFT JOIN `".Data::PRODUCT_TABLE."` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='PUBLISHED' LEFT JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'completed'  WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING count >0 AND live_products > 0 ) FINAL ";
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}

	/**
	 * function get count of clients who have revenue on free app
	 *
	 */
	public function getFreeAppNoRevenue($connection=false){
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "select count(*) as count from (SELECT count( `jed`.`merchant_id` ) AS `complete_orders`, `jed`.`merchant_id`,`sd`.`email`,`jed`.`status`,`jed`.`app_status`,`jed`.`expire_date`,`sd`.`shop_url` as `shopurl` FROM `".Data::EXTENSIONS_TABLE."` `jed` JOIN `".Data::ORDER_TABLE."` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'completed'  LEFT JOIN `".Data::SHOP_DETAILS."` `sd` ON `jed`.`merchant_id` = `sd`.`merchant_id` WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING complete_orders <0) FINAL ";
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}

	public function getTotalMerchant($connection=false){
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "SELECT COUNT(merchant_id) as count FROM `".Data::EXTENSIONS_TABLE."`";
		
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}

	public function header()
	{

		if (Yii::$app->user->isGuest) {
			return $this->redirect(['login']);
		}

		$model="";
		$P_avail=0;
		$P_under=0;
		$P_complete=0;
		$connection=Yii::$app->getDb();
		//$this->syncPaymentData();

		$paidWithNoRevnue = $this->getNoRevenueNoProductsClientsCount($connection);
		$freeWithNoRevnue = $this->getFreeNoRevenueNoProductsClientsCount($connection);
		$noRevenueWithLiveProductsClientsCount = $this->getNoRevenueWithLiveProductsClientsCount($connection);
		$ratingData = $this->getRatingData();
		$freeAppsWithRevenue = $this->getFreeAppWithRevenue();
		$clientConfigStatus = $this->getClientConfigurationStatus();
		$freeAppsWithNoRevenue = $this->getFreeAppNoRevenue($connection);
		$totalMerchant = $this->getTotalMerchant($connection);
		$count = $connection->createCommand("SELECT count(*) as count from `".Data::EXTENSIONS_TABLE."` where DATE(install_date) = DATE(NOW())")->queryOne();
		$uninstallCount = $connection->createCommand("SELECT count(*) as count from `".Data::EXTENSIONS_TABLE."` where DATE(uninstall_date) = DATE(NOW()) and app_status='uninstall'")->queryOne();
		

		$detailArray['count']=$count['count'];
		$detailArray['uninstall_count']=$uninstallCount['count'];
				
		$expiringToday = $connection->createCommand("SELECT count(*) as count from `".Data::EXTENSIONS_TABLE."` where (DATE(expire_date) BETWEEN  DATE(NOW()) and DATE(NOW())+INTERVAL 7 DAY) and app_status='install'")->queryOne();
		$expiringToday = $expiringToday['count'];
		$expiringInThreeDays = $connection->createCommand("SELECT count(*) as count from `".Data::EXTENSIONS_TABLE."` where (DATE(expire_date) BETWEEN  DATE(NOW()) and DATE(NOW())+INTERVAL 3 DAY) and app_status='install'")->queryOne();
		$expiringInThreeDays = $expiringInThreeDays['count'];

		$active_marchants_model="";
		$active_marchants=0;
		
		date_default_timezone_set('Asia/Kolkata');
		$active_marchants= count($connection->createCommand("SELECT  `merchant_id` from `jet_active_merchants` WHERE updated_at LIKE '%".date('Y-m-d')."%'")->queryAll());
		
		?>
		<head>
		
		
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
				color:#3207A3;
				
			}
			.outer_div_live{
				border-bottom: 1px solid #3207A3;
			}
			.outer_div_under{
				border-bottom: 1px solid #82124E;
			}
			.outer_div_active{
				border-bottom: 1px solid #10DC00;
			}
			.outer_div_complete{
				border-bottom: 1px solid #7D1B8E;
			}
			.under-row,.outer_div_under a{
				color:#82124E;
				font-weight:bold;
				font-size: 16px;
			}
			.active-row,.outer_div_active a{
				color:#0BAC31;
				font-weight:bold;
				font-size: 16px;
			}
			.complete-row , .outer_div_complete a{
				color:#7D1B8E;
				font-weight:bold;
				font-size: 16px;
			}
			.fa-arrow-circle-right{
				margin-left: 56%;
			}		
		</style>
		</head>
		<div class="row">
			<a href="<?=Yii::$app->request->baseUrl;?>/walmart/installations/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	               
	              <div class="small-box bg-aqua" style="background-color: #3207A3" >
	              	
		                <div class="inner" >
		                	<span><i aria-hidden="true" class="fa fa-cogs fa-5x"></i></span>
		                    <span><h1><?php echo $detailArray['count'];?></h1></span>
		                  <p>Apps Installed Today</p>
		                </div>
		                <div class="outer_div outer_div_live">
		                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/installations/index" class="small-box-footer">More info ...<i class="fa live-row fa-arrow-circle-right"></i></a>
		                </div>
	              </div>
	            </div>
            </a>
            <!-- ./col -->
            <a href="<?=Yii::$app->request->baseUrl;?>/walmart/uninstallations/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-green" style="background-color: #82124E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-trash fa-5x"></i></span>
		                 <span><h1><?php echo $detailArray['uninstall_count'];?></h1></span>
		                  <p>Apps Uninstalled Today</p>
	                </div>
	                <div class="outer_div outer_div_under" >
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/uninstallations/index" class="small-box-footer">More info ... <i class="fa under-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>

	        <a href="<?=Yii::$app->request->baseUrl;?>/walmart/paid-no-revenue-no-live/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $paidWithNoRevnue;?></h1></span>
	                  	<p>Paid Clients With No Revenue And No Live Products</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/paid-no-revenue-no-live/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a> 
	        
	        <a href="<?=Yii::$app->request->baseUrl;?>/walmart/paid-no-revenue-with-live/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $noRevenueWithLiveProductsClientsCount;?></h1></span>
	                  	<p>Paid Clients With No Revenue With Live Products</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/paid-no-revenue-with-live/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/walmart/review-rating/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">

	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
	                	<? if ($ratingData['ratingValue'] != 0): ?>
		                <span><h1><?php echo $ratingData['ratingValue']/2;?>/<?php echo '5' ;?>(<?php echo $ratingData['reviewCount'];?>)</h1></span>
		                <? else: ?>
						  <span><h1>Review Api Not Working</span>
						<? endif; ?>
	                  	<p>Review/Rating</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/review-rating/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/walmart/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+7 day",strtotime(date('y-m-d')))) ?>">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $expiringToday;?></h1></span>
	                  	<p>Apps Expiring In 7 DAYS</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+7 day",strtotime(date('y-m-d')))) ?>" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>

	        <a href="<?=Yii::$app->request->baseUrl;?>/walmart/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+3 day",strtotime(date('y-m-d')))) ?>">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $expiringInThreeDays;?></h1></span>
	                  	<p>Apps Expiring In 3 DAYS</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+3 day",strtotime(date('y-m-d')))) ?>" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/walmart/apps-not-configured/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><strong style="font-size: 1.6em;">Live:<?php echo $clientConfigStatus['liveNotConfigured'];?></strong></span>
	                  	<p>Apps Not Configured</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/apps-not-configured/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>

	        <!--<a href="<?/*=Yii::$app->request->baseUrl;*/?>/site/activemerchantstoday">
	            <div class="col-lg-3 col-xs-6">
	              <div class="small-box bg-red" style="background-color: #0BAC31">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-eye fa-5x"></i></span>
	                	<span><h1><?php /*echo $active_marchants; */?></h1></span>
	                  
	                  <p>Active Merchants (Today) </p>
	                </div>
	                
	                <div class="outer_div outer_div_active">
	                	<a href="<?/*=Yii::$app->request->baseUrl;*/?>/site/activemerchantstoday" class="small-box-footer">More info ... <i class="fa active-row fa-arrow-circle-right"></i></a>
	                </div>	
	              </div>
	            </div>
            </a>-->

            <a href="<?=Yii::$app->request->baseUrl;?>/walmart/free-apps-with-revenue/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-red" style="background-color: #0BAC31">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-eye fa-5x"></i></span>
	                	<span><h1><?php echo $freeAppsWithRevenue; ?></h1></span>
	                  
	                  <p>Free Apps With Revenue </p>
	                </div>
	                
	                <div class="outer_div outer_div_active">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/free-apps-with-revenue/index" class="small-box-footer">More info ... <i class="fa active-row fa-arrow-circle-right"></i></a>
	                </div>	
	              </div>
	            </div><!-- ./col -->
            </a>
                     <a href="<?=Yii::$app->request->baseUrl;?>/walmart/free-apps-with-no-revenue/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-red" style="background-color: #0BAC31">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-eye fa-5x"></i></span>
	                	<span><h1><?php echo $freeAppsWithNoRevenue; ?></h1></span>
	                  <p>Free Apps With No Revenue </p>
	                </div>
	                
	                <div class="outer_div outer_div_active">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/free-apps-with-no-revenue/index" class="small-box-footer">More info ... <i class="fa active-row fa-arrow-circle-right"></i></a>
	                </div>	
	              </div>
	            </div><!-- ./col -->
            </a>
               <a href="<?=Yii::$app->request->baseUrl;?>/walmart/free-no-revenue-no-live/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $freeWithNoRevnue;?></h1></span>
	                  	<p>Free Clients With No Revenue And No Live Products</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/free-no-revenue-no-live/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a> 
	              <a href="<?=Yii::$app->request->baseUrl;?>/walmart/total-merchant/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $totalMerchant;?></h1></span>
	                  	<p>Total Merchant</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/walmart/total-merchant/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
          </div><!-- /.row -->
       
		
		
		<?php 
		
	}
	
	public function activeMerchantsMenu(){
		?>
			<html>
				<head>
					<style>
						div.jet-extension-detail-index ul {
						    list-style-type: none;
						    margin: 0;
						    padding: 0;
						    overflow: hidden;
						    background-color: #8F0D85;
						}

						div.jet-extension-detail-index ul li {
						    float: left;
						    border-right:1px solid #bbb;
						    width: 20%;
						}

						div.jet-extension-detail-index ul li:last-child {
						    border-right: none;
						    float: right;
						}

						div.jet-extension-detail-index ul li a {
						    display: block;
						    color: white;
						    text-align: center;
						    padding: 14px 16px;
						    text-decoration: none;
						}
						div.jet-extension-detail-index ul li a:HOVER{
							background-color: #4CAF50;
						}

					</style>
				</head>
				<body>
				    <ul>
					  <li><a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantstoday">Active-Today </a></li>
					  <li><a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantsyesterday">Active-Yesterday </a></li>
					  <li><a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantssevendays">Active-Within Seven Days</a></li>
					  <li><a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantsfifteendays">Active-Within Fifteen Days</a></li>
					  <li><a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantsonemonth">Active-Current Month</a></li>
					</ul>
				</body>
			</html>

		<?php
	}
}
?>