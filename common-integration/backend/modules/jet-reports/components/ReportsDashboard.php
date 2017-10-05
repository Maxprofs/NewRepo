<?php 

namespace backend\modules\reports\components;
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
		$url = 'https://apps.shopify.com/jet-integration';

		$handle=curl_init($url);
		curl_setopt($handle, CURLOPT_VERBOSE, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($handle);
		$data = [];
		
		preg_match_all('/<meta itemprop="ratingValue" content="(.*?)">/s',$content,$estimates);
		$data['ratingValue'] = $estimates[1][0];
		preg_match_all('/<meta itemprop="bestRating" content="(.*?)">/s',$content,$estimates);
		$data['bestRating'] = $estimates[1][0];
		preg_match_all('/<meta itemprop="reviewCount" content="(.*?)">/s',$content,$estimates);
		$data['reviewCount'] = $estimates[1][0];
		
		return $data;
	}

	/*
	 *	function for getting count of paid client with no revenue and no live products
	 */
	public function getNoRevenueNoProductsClientsCount($connection=false)
	{
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `jet_extension_detail` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `jet_order_detail` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Purchased' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ) FINAL ";
		
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}

	/*
	 *	function for getting count of free client with no revenue and no live products
	 */
	public function getFreeClientNoRevenueNoProductsClientsCount($connection=false)
	{
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `jet_extension_detail` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `jet_order_detail` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Not Purchase' AND `jed`.`app_status`= 'install' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products <1 ) FINAL ";
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

		$query = "SELECT count(*) as count FROM `jet_extension_detail` `jed` LEFT JOIN `jet_test_api` `jta` ON `jed`.`merchant_id` = `jta`.`merchant_id` WHERE `jed`.`app_status`='install' AND `jta`.`merchant_id` IS NULL";
		$count = $connection->createCommand($query)->queryOne();
		$data = [];
		$data['testNotConfigured'] = isset($count['count'])?$count['count']:0;

		$query = "SELECT count(*) as count FROM `jet_extension_detail` `jed` LEFT JOIN `jet_configuration` `jc` ON `jed`.`merchant_id` = `jc`.`merchant_id` WHERE `jed`.`app_status`='install' AND `jc`.`merchant_id` IS NULL";
		$data['liveNotConfigured'] = isset($count['count'])?$count['count']:0;
		$count = $connection->createCommand($query)->queryOne();
		
		return $data;
	}

	/*
	 *	function for getting count of paid client with no revenue but having live products
	 */
	public function getNoRevenueWithLiveProductsClientsCount($connection=false)
	{
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `jet_extension_detail` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `jet_order_detail` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Purchased' GROUP BY `jed`.`merchant_id` HAVING count <1 AND live_products > 0 ) FINAL ";
		
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
		$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `jet_extension_detail` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `jet_order_detail` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING count >0 AND live_products > 0 ) FINAL ";
		
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}

	/**
	 * function get count of clients who have no revenue on free app
	 *
	 */
	public function getFreeAppWithNoRevenue($connection=false){
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "select count(*) as count from (SELECT count( `jod`.`merchant_id` ) AS `count`,count( `jp`.`merchant_id` ) AS `live_products` , `jed`.`merchant_id` FROM `jet_extension_detail` AS `jed` LEFT JOIN `jet_product` `jp` ON `jed`.`merchant_id` = `jp`.`merchant_id` AND `jp`.`status`='Available for Purchase' LEFT JOIN `jet_order_detail` `jod` ON `jed`.`merchant_id` = `jod`.`merchant_id` AND `jod`.`status` = 'complete'  WHERE `jed`.`status` = 'Not Purchase' GROUP BY `jed`.`merchant_id` HAVING count <1 ) FINAL ";
		
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}
	/**
	 * count all merchant
	 *
	 */
	public function getTotalMerchant($connection=false){
		if(!$connection)
			$connection=Yii::$app->getDb();
		$query = "SELECT COUNT(merchant_id) as count FROM jet_extension_detail";
		
		$count = $connection->createCommand($query)->queryOne();

		return isset($count['count'])?$count['count']:0;
	}
	public function header()
	{

		$model="";
		$P_avail=0;
		$P_under=0;
		$P_complete=0;
		$connection=Yii::$app->getDb();
		//$this->syncPaymentData();

		$paidWithNoRevnue = $this->getNoRevenueNoProductsClientsCount($connection);
		$freeWithNoRevnue = $this->getFreeClientNoRevenueNoProductsClientsCount($connection);
		$noRevenueWithLiveProductsClientsCount = $this->getNoRevenueWithLiveProductsClientsCount($connection);
		$ratingData = $this->getRatingData();
		$freeAppsWithRevenue = $this->getFreeAppWithRevenue($connection);
		$freeAppsWithNoRevenue = $this->getFreeAppWithNoRevenue($connection);
		$totalMerchant = $this->getTotalMerchant($connection);

		$clientConfigStatus = $this->getClientConfigurationStatus($connection);
		$count = $connection->createCommand("SELECT count(*) as count from `jet_extension_detail` where DATE(install_date) = DATE(NOW())")->queryOne();
		$uninstallCount = $connection->createCommand("SELECT count(*) as count from `jet_extension_detail` where DATE(uninstall_date) = DATE(NOW()) and app_status='uninstall'")->queryOne();


		$detailArray['count']=$count['count'];
		$detailArray['uninstall_count']=$uninstallCount['count'];

		$expiringToday = $connection->createCommand("SELECT count(*) as count from `jet_extension_detail` where (DATE(expire_date) BETWEEN  DATE(NOW()) and DATE(NOW())+INTERVAL 7 DAY) and app_status='install'")->queryOne();
		$expiringToday = $expiringToday['count'];
		$expiringInThreeDays = $connection->createCommand("SELECT count(*) as count from `jet_extension_detail` where (DATE(expire_date) BETWEEN  DATE(NOW()) and DATE(NOW())+INTERVAL 3 DAY) and app_status='install'")->queryOne();
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
			<a href="<?=Yii::$app->request->baseUrl;?>/reports/installations/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	               
	              <div class="small-box bg-aqua" style="background-color: #3207A3" >
	              	
		                <div class="inner" >
		                	<span><i aria-hidden="true" class="fa fa-cogs fa-5x"></i></span>
		                    <span><h1><?php echo $detailArray['count'];?></h1></span>
		                  <p>Apps Installed Today</p>
		                </div>
		                <div class="outer_div outer_div_live">
		                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/installations/index" class="small-box-footer">More info ...<i class="fa live-row fa-arrow-circle-right"></i></a>
		                </div>
	              </div>
	            </div>
            </a>
            <!-- ./col -->
            <a href="<?=Yii::$app->request->baseUrl;?>/reports/uninstallations/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-green" style="background-color: #82124E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-trash fa-5x"></i></span>
		                 <span><h1><?php echo $detailArray['uninstall_count'];?></h1></span>
		                  <p>Apps Uninstalled Today</p>
	                </div>
	                <div class="outer_div outer_div_under" >
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/uninstallations/index" class="small-box-footer">More info ... <i class="fa under-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/paid-no-revenue-no-live/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $paidWithNoRevnue;?></h1></span>
	                  	<p>Paid Clients With No Revenue And No Live Products</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/paid-no-revenue-no-live/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a> 
	        
	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/paid-no-revenue-with-live/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $noRevenueWithLiveProductsClientsCount;?></h1></span>
	                  	<p>Paid Clients With No Revenue With Live Products</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/paid-no-revenue-with-live/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/review-rating/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                <span><h1><?php echo $ratingData['ratingValue']/2;?>/<?php echo $ratingData['bestRating']/2;?>(<?php echo $ratingData['reviewCount'];?>)</h1></span>
	                  	<p>Review/Rating</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/review-rating/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+7 day",strtotime(date('y-m-d')))) ?>">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $expiringToday;?></h1></span>
	                  	<p>Apps Expiring In 7 DAYS</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+7 day",strtotime(date('y-m-d')))) ?>" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>

	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+3 day",strtotime(date('y-m-d')))) ?>">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $expiringInThreeDays;?></h1></span>
	                  	<p>Apps Expiring In 3 DAYS</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/expire/view?from=<?= Date('y-m-d') ?>&to=<?= date('y-m-d',strtotime("+3 day",strtotime(date('y-m-d')))) ?>" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>
	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/apps-not-configured/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><strong style="font-size: 1.6em;">Test:<?php echo $clientConfigStatus['testNotConfigured'];?> Live:<?php echo $clientConfigStatus['liveNotConfigured'];?></strong></span>
	                  	<p>Apps Not Configured</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/apps-not-configured/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>

	        <a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantstoday">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-red" style="background-color: #0BAC31">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-eye fa-5x"></i></span>
	                	<span><h1><?php echo $active_marchants; ?></h1></span>
	                  
	                  <p>Active Merchants (Today) </p>
	                </div>
	                
	                <div class="outer_div outer_div_active">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/site/activemerchantstoday" class="small-box-footer">More info ... <i class="fa active-row fa-arrow-circle-right"></i></a>
	                </div>	
	              </div>
	            </div><!-- ./col -->
            </a>

            <a href="<?=Yii::$app->request->baseUrl;?>/reports/free-apps-with-revenue/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-red" style="background-color: #0BAC31">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-eye fa-5x"></i></span>
	                	<span><h1><?php echo $freeAppsWithRevenue; ?></h1></span>
	                  
	                  <p>Free Apps With Revenue </p>
	                </div>
	                
	                <div class="outer_div outer_div_active">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/free-apps-with-revenue/index" class="small-box-footer">More info ... <i class="fa active-row fa-arrow-circle-right"></i></a>
	                </div>	
	              </div>
	            </div><!-- ./col -->
            </a>

            <a href="<?=Yii::$app->request->baseUrl;?>/reports/free-apps-with-no-revenue/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-red" style="background-color: #0BAC31">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-eye fa-5x"></i></span>
	                	<span><h1><?php echo $freeAppsWithNoRevenue; ?></h1></span>
	                  <p>Free Apps With No Revenue </p>
	                </div>
	                
	                <div class="outer_div outer_div_active">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/free-apps-with-no-revenue/index" class="small-box-footer">More info ... <i class="fa active-row fa-arrow-circle-right"></i></a>
	                </div>	
	              </div>
	            </div><!-- ./col -->
            </a>

              <a href="<?=Yii::$app->request->baseUrl;?>/reports/free-no-revenue-no-live/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $freeWithNoRevnue;?></h1></span>
	                  	<p>Free Clients With No Revenue And No Live Products</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/free-no-revenue-no-live/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
	                </div>
	              </div>
	            </div><!-- ./col -->
	        </a>


	        <a href="<?=Yii::$app->request->baseUrl;?>/reports/total-merchant/index">
	            <div class="col-lg-3 col-xs-6">
	              <!-- small box -->
	              <div class="small-box bg-yellow" style="background-color: #7D1B8E">
	                <div class="inner">
	                	<span><i aria-hidden="true" class="fa fa-dollar fa-5x"></i></span>
		                 <span><h1><?php echo $totalMerchant;?></h1></span>
	                  	<p>Total Merchant</p>
	                </div>
	                <div class="outer_div outer_div_complete">
	                	<a href="<?=Yii::$app->request->baseUrl;?>/reports/total-merchant/index" class="small-box-footer">More info ... <i class="fa complete-row fa-arrow-circle-right"></i></a>
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