<?php
namespace frontend\modules\walmart\controllers;
use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\Generator;
use frontend\modules\walmart\components\Xml\Parser;
use frontend\modules\walmart\components\Jetproductinfo;

class WebhookupdateController extends Controller
{
	public function beforeAction($action)
	{
		$disableCsrActions = ['productupdate'=>'',
							'createshipment'=>'',
							'product-delete'=>'',
							'product-update1'=>'',
							];
		/*if(isset($disableCsrActions[$this->action->id]))
			return false;
		*/
		return true;
	}

	/**
	 * Delete Product On Walmart
	 */
	public function actionProductDelete()
	{
		$data=$_POST;
		
		if(isset($data['id'])){
			try{
				$id = $data['id'];
				$logFIle = 'product/delete/'.$id;
				Data::createLog('Requested Data: '.json_encode($data),$logFIle,'a');
				
		        $product = Data::sqlRecords('SELECT sku,type FROM `jet_product` WHERE id="' . $id . '" ', 'one');
		        if(!$product)
		        	return;

		        $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$data['merchant_id']."'",'one','select');
		        if (isset($product) && !empty($product)) {
		            if ($product['type'] == 'variants') {
		                $skus = Data::sqlRecords('SELECT option_sku FROM `jet_product_variants` WHERE product_id="' . $id .'" ', null, 'all');
		                if (!is_array($skus) || (is_array($skus) && !count($skus)))
		                    $skus = [];

		            } else {
		                $skus[0]['option_sku'] = $product['sku'];
		            }

		            
		            $result = [];
		            foreach ($skus as $sku) {

		                $walmartApi = new Walmartapi($walmartConfig['consumer_id'],$walmartConfig['secret_key']);
		                $feed_data = $walmartApi->retireProduct($sku['option_sku']);
		                
		                if(isset($feed_data['ItemRetireResponse']))
		                {
		                    $result['success'][] = '<b>'.$feed_data['ItemRetireResponse']['sku'].' : </b>'.$feed_data['ItemRetireResponse']['message'];
		                }
		                elseif (isset($feed_data['errors']['error']))
		                {
		                    if(isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku")
		                    {
		                        $result['error'][] = $sku['option_sku'].' : Product not Uploaded on Walmart.';
		                    }
		                    else
		                    {
		                        $result['error'][] = $sku['option_sku'].' : '.$feed_data['errors']['error']['description'];
		                    }
		                } 
		            }
		            
		        }
		        Data::createLog('Result : '.json_encode($result),$logFIle,'a');
		     }
		     catch(Exception $e){
		     	Data::createLog('Exception : '.$e->getMessage(),$logFIle,'a');
		     }
	    }
		die;
	}
	/**
	 * Create and store array file for product update
	 */
	public function actionProductCreate()
	{

		$data = $_POST;
		$merchantId=$data['merchant_id'];
		$logFIle = 'product/create/'.$data['merchant_id'];
		Data::createLog('Data : '.json_encode($data),$logFIle,'a');
	
		$connection = Yii::$app->getDb();
		$result = Jetproductinfo::saveNewRecords($data['data'],$data['merchant_id'],$connection);
	}


	/**
	 * Create and store array file for product update
	 */
	public function actionProductUpdate1()
	{	
		
		$data = $_POST;
		$connection = Yii::$app->getDb();
		if(isset($data['id'])){
			try{

				$id = $data['id'];
				$productData = $data['data'];
				$logFIle = 'product/update/'.$id;
				Data::createLog('getting walmart config : ',$logFIle,'a');
				$walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$data['merchant_id']."'",'one','select');

				$walmartApi = new Walmartapi($walmartConfig['consumer_id'],$walmartConfig['secret_key']);
				Data::createLog('create product on walmart : ',$logFIle,'a');

				$preparedData = $walmartApi->createProductOnWalmart([$id],$walmartApi,$data['merchant_id'],$connection,true);
				//Data::createLog('response:'.print_r($preparedData,true),$logFIle,'a');
				Data::createLog('got prepared data : ',$logFIle,'a');
				foreach ($productData as $key => $pro_val) 
				{
					foreach($pro_val as $index=>$val){
						switch($index){
							case 'name':
									$preparedData['Product']['productName'] = '<![CDATA[' . $val . ']]>';
								break;

							case 'description':
									$preparedData['Product']['longDescription'] = '<![CDATA[' . $val . ']]>';
								break;

							case 'shelfDescription':
									$preparedData['Product']['shelfDescription'] = '<![CDATA[' . $val . ']]>';
								break;

							case 'main_image':
									$preparedData['Product']['mainImage'] = [
						                    'mainImageUrl' => $val,
						                    'altText' => isset($pro_val['shelfDescription'])?$pro_val['shelfDescription']:$pro_val['sku'],
						                ];
								break;

							case 'default':
								break;
						}
					}
		    		
				}

				$dir = Yii::getAlias('@webroot').'/frontend/modules/walmart/filestorage/product/update/';
				$filePath = $dir.$data['merchant_id'].'.php';
				if(file_exists($filePath)){

					$storedData = require $filePath;
					unset($preparedData['MPItemFeed']['_value'][0]);
					$feedItems = $preparedData['MPItemFeed']['_value'];
					$storedItems = $storedData['MPItemFeed']['_value'];
					$feedItems = array_merge($storedItems,$feedItems);
					$preparedData['MPItemFeed']['_value'] = $feedItems;

				}
				
				if (!file_exists($dir)) {
		            mkdir($dir, 0775, true);

		        }
		        Data::createLog('saving data : ',$logFIle,'a');
				file_put_contents($filePath, '<?php return $arr = ' . var_export($preparedData, true) . ';');
		        Data::createLog('Result : '.json_encode($preparedData),$logFIle,'a');
		     }
		     catch(Exception $e){
		     	Data::createLog('Exception : '.$e->getMessage(),$logFIle,'a');
		     }
	    }
	}

	/**
	 * Update Product On Walmart
	 */
	public function actionProductUpdateUsingFile()
	{
		$filePath = Yii::getAlias('@webroot').'/frontend/modules/walmart/filestorage/product/update';
		$files = scandir($filePath);
		foreach($files as $file){
			$fullFilePath = $filePath.'/'.$file;;
			if(!is_dir($fullFilePath)){
				$storedData = require $fullFilePath;
				$merchant_id = str_replace('.php','',$file);
				$xmlDir = $filePath.'/xml/'.$merchant_id;
				if (!file_exists($xmlDir)) {
		            mkdir($xmlDir, 0775, true);

		        }
		        $xmlFile = $xmlDir. '/MPProduct-' . time() . '.xml';
		        $xml = new Generator();
                $xml->arrayToXml($storedData)->save($xmlFile);
                Walmartapi::unEscapeData($xmlFile);
                $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$merchant_id."'",'one','select');
                $walmartApi = new Walmartapi($walmartConfig['consumer_id'],$walmartConfig['secret_key']);
                $response = $walmartApi->postRequest(Walmartapi::GET_FEEDS_ITEMS_SUB_URL, ['file' => $xmlFile]);
                $response = str_replace('ns2:', "", $response);

                $responseArray = [];
                $responseArray = Walmartapi::xmlToArray($response);
                if (isset($responseArray['FeedAcknowledgement'])) {
                    $result = [];
                    unlink($fullFilePath);
                    $feedId = isset($responseArray['FeedAcknowledgement']['feedId']) ? $responseArray['FeedAcknowledgement']['feedId'] : '';
                    if ($feedId != '') {
                        $result = $walmartApi->getFeeds($feedId);
                        if (isset($results['results'][0], $results['results'][0]['itemsSucceeded']) && $results['results'][0]['itemsSucceeded'] == 1) {
                            $result =  ['feedId' => $feedId, 'feed_file' => $xmlFile];
                        }
                        $result = ['feedId' => $feedId, 'feed_file' => $xmlFile];
                    }
                } elseif ($responseArray['errors']) {
                    $error['feedError'] = $responseArray['errors'];
                }
		        
			}
			
		}
		die;
	}
	/**
	 * Update Inventory/price On Walmart
	 * @param [sku,price,merchant_id,type] 
	 * @return string
	 */
	public function actionProductupdate()
	{
		if($_POST)
		{	
			try
			{
				
				$product=$_POST;
				$path='productupdate/'.$product['merchant_id'].'/update.log';
				$walmartConfig=[];
			    $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$product['merchant_id']."'",'one','select');
			    $merchant_id = $product['merchant_id'];
			    if(is_array($walmartConfig) && count($walmartConfig)>0)
			    {
			    	Data::createLog("walmart_configuration available: ".PHP_EOL,$path);
			        //$walmartHelper = new Walmartapi($walmartConfig['consumer_id'],$walmartConfig['secret_key'],$walmartConfig['consumer_channel_type_id']);
			       // define("MERCHANT_ID", $merchant_id);
			        if(isset($product['type']) && $product['type']=="price")
			        {
			        	//update custom price on walmart
			        	/*$updatePrice = Data::getCustomPrice($product['price'],$merchant_id);
			        	if($updatePrice)
			        		$product['price']=$updatePrice;*/

			        	$product['price'] = WalmartRepricing::getProductPrice($product['price'], $product['type'], $product['id'], $merchant_id);
			        	
			        	//change price log
			        	//$path='productupdate/price/'.$merchant_id.'/'.Data::getKey($product['sku']).'.log';
			        	//Data::createLog("price data: ".json_encode($product).PHP_EOL,$path);
			        	$shopDetails = Data::getWalmartShopDetails(MERCHANT_ID);
			            $product['currency'] = isset($shopDetails['currency'])?$shopDetails['currency']:'USD';

			            //define("CURRENCY", $currency);
			            //$walmartHelper->updatePriceOnWalmart($product,"webhook");
			        }
			        elseif(isset($product['type']) && $product['type']=="inventory")
			        {
			        	//change price log
			        	//$path='productupdate/inventory/'.$merchant_id.'/'.Data::getKey($product['sku']).'.log';
			        	//Data::createLog("inventory data: ".json_encode($product).PHP_EOL,$path);
			        	//$walmartHelper->updateInventoryOnWalmart($product,"webhook");

			        }
			        //save product update log
			        $productExist=Data::sqlRecords("SELECT id FROM walmart_price_inventory_log WHERE merchant_id='".$product['merchant_id']."' and sku='".addslashes($product['sku'])."' LIMIT 0,1",'one','select');
			        if(is_array($productExist) && count($productExist)>0)
			        {

			        	$query="UPDATE walmart_price_inventory_log SET type='".$product['type']."',data='".addslashes(json_encode($product))."' WHERE merchant_id='".$product['merchant_id']."' and sku='".addslashes($product['sku'])."'";
			        	Data::createLog("product update data: ".$query.PHP_EOL,$path);
			        	//echo "<br>"."update".$query;
			        	Data::sqlRecords($query,null,'update');
			        }
			        else
			        {
			        	$sku = addslashes($product['sku']);
			        	$query="INSERT INTO `walmart_price_inventory_log`(`merchant_id`,`type`,`data`,`sku`) VALUES('{$product['merchant_id']}','{$product['type']}','".addslashes(json_encode($product))."','{$sku}')";
			        	//echo "<br>"."insert".$query;
			        	Data::createLog("product insert data: ".$query.PHP_EOL,$path);
			        	Data::sqlRecords($query,null,'insert');
			        }
			    }
			}
			catch(Exception $e)
			{
				Data::createLog("productupdate error ".json_decode($_POST),'productupdate/exception.log','a',true);
			}
	    }
	    else
		{
			Data::createLog("product update error");
		}
	}
	/**
	 * Update fulfillment On Walmart
	 * @param  []
	 * @return string
	 */
	public function actionCreateshipment()
	{
		if($_POST && isset($_POST['id']))
		{
			$shop=isset($_POST['shopName'])?$_POST['shopName']:"NA";
			$path='shipment/'.$shop.'/'.Data::getKey($_POST['id']).'.log';
			try
			{	
				//create shipment data
			    Data::createLog("order shipment in walmart".PHP_EOL.json_encode($_POST),$path);
				$objController=Yii::$app->createController('walmart/walmartorderdetail');
				$objController[0]->actionCurlprocessfororder();
			}
			catch(Exception $e)
			{
				Data::createLog("order shipment error ".json_decode($_POST),$path,'a',true);
			}
			
		}
		else
		{
			Data::createLog("order shipment error wrong post");
		}
	}

}