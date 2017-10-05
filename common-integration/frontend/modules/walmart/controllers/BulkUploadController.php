<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartProduct as WalmartProductComponent;

/**
* BulkUpload controller
*/
class BulkUploadController extends Controller 
{
	const ARRAY_FILE_PATH = '/frontend/modules/walmart/filestorage/bulkupload/{merchant_id}';
	const ARRAY_FILE_NAME = 'products';

	const UPLOAD_ERROR_FILE = '/frontend/modules/walmart/filestorage/bulkupload/{merchant_id}/error.txt';
	const UPLOAD_STATUS_FILE_PATH = '/frontend/modules/walmart/filestorage/bulkupload/{merchant_id}/status.txt';

	const SUCCESS_COUNT_FILE_PATH = '/frontend/modules/walmart/filestorage/bulkupload/{merchant_id}/success_count.php';
	const ERROR_COUNT_FILE_PATH = '/frontend/modules/walmart/filestorage/bulkupload/{merchant_id}/error_count.php';

	public function beforeAction($action)
    {
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

	public function setEnvironment($merchant_id)
	{
		$shopDetails = Data::getWalmartShopDetails($merchant_id);

        $token = isset($shopDetails['token'])?$shopDetails['token']:'';
        $email = isset($shopDetails['email'])?$shopDetails['email']:'';
        $currency = isset($shopDetails['currency'])?$shopDetails['currency']:'USD';
        $shop = isset($shopDetails['shop_url'])?$shopDetails['shop_url']:'';

		define("MERCHANT_ID", $merchant_id);
		define("TOKEN", $token);
		define("EMAIL", $email);
		define("CURRENCY", $currency);
        define("SHOP", $shop);

        $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$merchant_id."'", 'one');
        if($walmartConfig)
        {
            define("CONSUMER_CHANNEL_TYPE_ID",$walmartConfig['consumer_channel_type_id']);
            define("API_USER",$walmartConfig['consumer_id']);
            define("API_PASSWORD",$walmartConfig['secret_key']);

            return true;
        }
        return false;
	}

	public function saveArrayInFile($merchant_id, $fileName, $data)
	{
		$dir = Yii::getAlias('@webroot') . strtr(self::ARRAY_FILE_PATH, ['{merchant_id}'=>$merchant_id]) .'/'. $fileName . '.php';
		if (!file_exists(dirname($dir))) {
            mkdir(dirname($dir), 0777, true);
        }
		
		$content = '<?php return ' . var_export($data, true) . ';';

    	$handle = fopen($dir,'w');
		fwrite($handle, $content);
		fclose($handle);
	}

	/**
	 * Send Notification to customers through socket
	 *
	 * @param string $status ("success" or "error")
	 * @param array $message (if status="success" then $message=["progress"=>"", "msg"=>""] elseif status="error" then $message=["msg"=>""])
	 * @return void
	 */
	public function sendNotification($merchant_id, $status, $message)
	{
		$bulkUploadServer = new BulkUploadServerController(Yii::$app->controller->id,'');

		$data = ['sender_ref'=>$merchant_id, 'action'=>'product_upload'];

  		$hash = $bulkUploadServer->getHash($data);

		$host = $bulkUploadServer->host;
		$port = $bulkUploadServer->port;

		$socket = socket_create(AF_INET,SOCK_STREAM,0);
		if (!socket_connect($socket, $host, $port)) {
		    die('Socket error : '.socket_strerror(socket_last_error()));
		}

		$request = "Headers : GET / HTTP/1.1\r\n".
		"Host: localhost:10000\r\n".
		"Sec-WebSocket-Version: 13\r\n".
		"Sec-WebSocket-Extensions: permessage-deflate\r\n".
		"Sec-WebSocket-Key: I6RebVGiUaPOailY3bRxNw==\r\n".
		"Upgrade: w\r\n\r\n";

		socket_write($socket, $request, strlen($request));

		$response = socket_read($socket,2048);

		$Msg = ['hash'=>$hash, 'status'=>$status, 'progress'=>'', 'message'=>$message['msg']];

		if($status == 'success') {
			if(isset($message['progress']))
				$Msg['progress'] = $message['progress'];
			
			if(isset($message['success_count']))
				$Msg['success_count'] = $message['success_count'];
		}
		else {
			if(isset($message['progress']))
				$Msg['progress'] = $message['progress'];

			if(isset($message['error_count']))
				$Msg['error_count'] = $message['error_count'];
		}

		if(isset($message['send_mail'])) {
			$Msg['send_mail'] = "upload_complete";
		}

		$encodedMsg = json_encode($Msg);
		$len = strlen($encodedMsg);
		socket_write($socket, $encodedMsg, $len);
		socket_close($socket);
	}

	public function getPercentage($total, $page)
  	{
  		return ceil((($page+1)/$total)*1000)/10;
  	}

	public function actionStartUpload()
	{
        $merchant_id = Yii::$app->request->post('merchant_id', false);

        if($merchant_id !== false /*&& self::setEnvironment($merchant_id)*/)
		{

	        if(WalmartProductComponent::canSendItemFeed($merchant_id))
	        {
	            $dir = Yii::getAlias('@webroot').WalmartProductComponent::ALL_PRODUCT_UPLOAD_FILEPATH;
	            $filePath = $dir.$merchant_id.'.php';
	            if(file_exists($filePath)) {
	                unlink($filePath);
	            }  
	            self::deleteFiles($merchant_id);

	            $last_send_index = false;

	            $feed_type = WalmartProductComponent::FEED_TYPE_ITEM;
	            $_query = "SELECT `last_send_index` FROM `walmart_feed_stats` WHERE `merchant_id`={$merchant_id} AND `feed_type`='{$feed_type}' LIMIT 0,1";
	            $result = Data::sqlRecords($_query, 'one');
	            if($result) {
	                $last_send_index = $result['last_send_index'];
	                Data::sqlRecords("DELETE FROM `walmart_feed_stats` WHERE `merchant_id`={$merchant_id} AND `feed_type`='{$feed_type}'", null, 'delete');
	            }

	            if($last_send_index !== false) {
	                $query = "SELECT `wal`.`id`,`wal`.`product_id` FROM `walmart_product` `wal` WHERE `merchant_id`=".$merchant_id." AND `id` >= {$last_send_index} ORDER BY `wal`.`id` ASC";
	            } else {
	                $query = "SELECT `wal`.`id`,`wal`.`product_id` FROM `walmart_product` `wal` WHERE `merchant_id`=".$merchant_id." ORDER BY `wal`.`id` ASC";
	            }

	            $product = Data::sqlRecords($query, "all", "select");

	            $totalProducts = count($product);

	            if (is_array($product) && $totalProducts)
	            {
	                $size_of_request = 50; //Number of products to be uploaded at once(in single feed)
	                $pages = (int)(ceil($totalProducts / $size_of_request));

	                $selectedProducts = array_chunk($product, $size_of_request);
	                self::saveArrayInFile($merchant_id, self::ARRAY_FILE_NAME, $selectedProducts);

	                //echo json_encode(['success' => "Staring Upload Process...", 'totalProducts'=>$totalProducts, 'pages'=>$pages]);
	                self::sendNotification($merchant_id, "success", ["msg"=>"Starting Upload Process."]);

	                self::startUpload($merchant_id, $totalProducts, $pages);
	            }
	            else 
	            {
	                //echo json_encode(['error' => "No Products Found.."]);
	                self::sendNotification($merchant_id, "error", ["msg"=>"No Products Found for Upload."]);
	            }
	        }
	        else
	        {
	            //echo json_encode(['error' => "Threshold Limit Exceeded. Please try again after 1 Hour."]);
	            self::sendNotification($merchant_id, "error", ["msg"=>"Threshold Limit Exceeded. Please try again after 1 Hour."]);
	        }
		}
		else
		{
			//echo json_encode(['error' => "Invalid Data."]);
			self::sendNotification($merchant_id, "error", ["msg"=>"Invalid Data."]);
		}
		exit;
	}

	public function startUpload($merchant_id, $totalProducts, $pages, $current_page=0)
	{
		if($pages)
		{
			$directory = strtr(self::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
			$directory = \Yii::getAlias('@webroot').$directory;
			Data::createDirectory(dirname($directory), 0777);
			$handle = fopen($directory,'w');
			fwrite($handle,'0');
			fclose($handle);

			$data = ['merchant_id' => $merchant_id, 'page' => $current_page, 'total_pages' => $pages, 'total_products'=>$totalProducts];
	  		
	  		//$url = Data::getUrl('bulk-upload/prepare-and-upload');
	  		$url = \Yii::getAlias('@webwalmarturl').'/bulk-upload/prepare-and-upload';
	  		
	  		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_TIMEOUT,1);
			$uploadResponse = curl_exec($ch);

			//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//var_dump($httpcode);

			curl_close($ch);

			/*$uploadResult = json_decode($uploadResponse, true);
			if(is_null($uploadResult))
			{
				$fileName = time();
				$error = $uploadResponse;

				$bulkUploadServer = new BulkUploadServerController(Yii::$app->controller->id,'');

				$bulkUploadServer->createErrorLog($merchant_id, $error, $fileName);
				//echo "An Error has occured. Please check error log : ".$fileName.'.log'.PHP_EOL;
			}*/
		}
		else
		{
			self::sendNotification($merchant_id, "error", ["msg"=>"No Products Found for Upload."]);
		}
		return;
	}

	public function actionPrepareAndUpload()
	{
		$page = Yii::$app->request->post('page', false);
		$merchant_id = Yii::$app->request->post('merchant_id', false);
		$total_pages = Yii::$app->request->post('total_pages', false);
		$total_products = Yii::$app->request->post('total_products', false);
		
		if($page !== false && $merchant_id !== false && self::setEnvironment($merchant_id))
		{
			$filePath = Yii::getAlias('@webroot') . strtr(self::ARRAY_FILE_PATH, ['{merchant_id}'=>$merchant_id]) .'/'. self::ARRAY_FILE_NAME . '.php';

            $storedData = [];
            if (file_exists($filePath)) {
                $storedData = require $filePath;
            }//error is here

	        $selectedProducts = isset($storedData[$page]) ? $storedData[$page] : [];
	        $count = count($selectedProducts);

	        $returnArr = [];

	        if (!$count) {
	            $returnArr = ['error' => true, 'error_msg' => 'No Products to Upload'];
	        } 
	        else {
	            try 
	            {
	            	$connection = Yii::$app->getDb();

	                $walmart_product = new WalmartProductComponent(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

	                $productResponse = $walmart_product->uploadAllProductsOnWalmart($selectedProducts,$merchant_id);

	                if (is_array($productResponse) && isset($productResponse['uploadIds']) && count($productResponse['uploadIds'] > 0)) 
	                {
	                    //save product status and data feed
	                    $ids = implode(',', $productResponse['uploadIds']);
	                    foreach ($productResponse['uploadIds'] as $val) {
	                        $query = "UPDATE `walmart_product` SET status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', error='' where product_id='" . $val . "'";
	                        Data::sqlRecords($query, null, "update");
	                    }

	                    $msg = "product feed successfully submitted on walmart.";
	                    $feed_count = count($productResponse['uploadIds']);

	                    $returnArr['success'] = true;
	                    $returnArr['success_msg'] = $msg;
	                    $returnArr['success_count'] = $feed_count;
	                }

	                if(isset($productResponse['feedId']))
	                {
	                    $feed_file = isset($productResponse['feed_file']) ? $productResponse['feed_file'] : '';

	                    $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('" . $merchant_id . "','" . $productResponse['feedId'] . "','" . $ids . "','" . $feed_file . "')";
	                    Data::sqlRecords($query, null, "insert");

	                    $returnArr['feed_id'] = $productResponse['feedId'];
	                }

	                //save errors in database for each errored product
	                if (isset($productResponse['errors'])) 
	                {
	                    $_feedError = null;
	                    if (isset($productResponse['errors']['feedError']))
	                    {
	                        $msg = $productResponse['errors']['feedError'];
	                        $_feedError = $msg;
	                        unset($productResponse['errors']['feedError']);

	                    }

	                    $directory = strtr(self::UPLOAD_ERROR_FILE, ['{merchant_id}'=>$merchant_id]);
						$error_file = \Yii::getAlias('@webroot').$directory;
						$handle = fopen($error_file, 'a');

						$errorsArrayFile = dirname($error_file).'/error.php';
						$errorsArray = [];
						if (file_exists($errorsArrayFile)) {
            				$errorsArray = require $errorsArrayFile;
        				}

	                    foreach ($productResponse['errors'] as $productSku => $error) 
	                    {
                            if (is_array($error)) {
	                            $error = implode(',', $error);
//                                fwrite($handle, 'test error '.json_encode($error));
//                                $error = json_encode($error);
	                        }

                            $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($error) . "' where jp.sku='" . $productSku . "'";
	                        Data::sqlRecords($query, null, "update");

	                        $error_msg = $productSku.' : '.$error."\n";
	                        fwrite($handle, $error_msg);

	                        $errorsArray[$productSku] = $error;
	                    }
	                    fclose($handle);

	                    $errorsArrayFileHandle = fopen($errorsArrayFile, 'w');
						fwrite($errorsArrayFileHandle, '<?php return $arr = ' . var_export($errorsArray, true) . ';  ?>');
						fclose($errorsArrayFileHandle);

	                    $returnArr['error'] = true; 
	                    $returnArr['error_msg'] = $productResponse['errors'];
	                    $returnArr['originalmessage'] = $productResponse['originalmessage'];

	                    $returnArr['error_count'] = count($productResponse['errors']);
	                    $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

	                    if(!is_null($_feedError)) 
	                    {
	                        $returnArr['feedError'] = $_feedError;

	                        $directory = strtr(self::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
							$filePath = dirname(\Yii::getAlias('@webroot').$directory);
							$filePath .= '/feedError.log';
							$handle = fopen($filePath, 'w');
							fwrite($handle, json_encode($_feedError, true));
							fclose($handle);
	                        
	                        self::sendNotification($merchant_id, "error", ["msg"=>'Feed not Submitted to Walmart. Error Code : '.$_feedError['error']['code'], "progress"=>'']);
	                    }
	                }

	                if(isset($productResponse['threshold_error'])) {
	                    $returnArr = ['threshold_error' => $productResponse['threshold_error'], 'stop'=>1];
	                }

	            } catch (Exception $e) {
	                $returnArr = ['error' => true, 'error_msg' => $e->getMessage()];
	            }
	        }

	        //echo json_encode($returnArr);
	        $progress = self::getPercentage($total_pages, $page);

	        if(isset($returnArr['success_count']) && intval($returnArr['success_count']) > 0) 
			{				
				$successCount = self::saveSuccessCount($merchant_id, $returnArr['success_count']);

				self::sendNotification($merchant_id, "success", ["msg"=>$returnArr['success_count'].' products successfully processed.', "progress"=>$progress, 'success_count'=>$successCount]);
			}

			if(isset($returnArr['error_count']) && intval($returnArr['error_count']) > 0) 
			{
				$errorCount = self::saveErrorCount($merchant_id, $returnArr['error_count']);

				self::sendNotification($merchant_id, "error", ["msg"=>$returnArr['error_count'].' products are having error.', "progress"=>$progress, 'error_count'=>$errorCount]);
			}
			self::saveProgress($merchant_id, $progress);

			$page = intval($page)+1;
			if($page < intval($total_pages))
			{
				$data = ['merchant_id' => $merchant_id, 'page' => $page, 'total_pages' => $total_pages, 'total_products'=>$total_products];
		  		
		  		//$url = Data::getUrl('bulk-upload/prepare-and-upload');
		  		$url = \Yii::getAlias('@webwalmarturl').'/bulk-upload/prepare-and-upload';

		  		$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($ch, CURLOPT_TIMEOUT,1);
				$uploadResponse = curl_exec($ch);
				curl_close($ch);
			}
			else
			{
				/*$directory = strtr(self::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
				$filePath = \Yii::getAlias('@webroot').$directory;
				if(file_exists($filePath)) {
					unlink($filePath);
				}*/

				self::sendNotification($merchant_id, "success", ["msg"=>"Uploading Process Completed.", "send_mail"=>"1"]);
			}
		}
	}

	public function saveSuccessCount($merchant_id, $count)
	{
		$directory = strtr(self::SUCCESS_COUNT_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$success_count_file = \Yii::getAlias('@webroot').$directory;

		$old_count = 0;
		if (file_exists($success_count_file)) {
            $old_count = require $success_count_file;
        }

        $count = intval($count)+intval($old_count);

		$handle = fopen($success_count_file, 'w');

		//fwrite($handle, '<?php return $arr = ' . var_export($preparedData, true) . ';');
		fwrite($handle, '<?php return $saved = '.$count.';  ?>');
		fclose($handle);

		return $count;
	}

	public function saveErrorCount($merchant_id, $count)
	{
		$directory = strtr(self::ERROR_COUNT_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$error_count_file = \Yii::getAlias('@webroot').$directory;

		$old_count = 0;
		if (file_exists($error_count_file)) {
            $old_count = require $error_count_file;
        }

        $count = intval($count)+intval($old_count);
        
		$handle = fopen($error_count_file, 'w');

		//fwrite($handle, '<?php return $arr = ' . var_export($preparedData, true) . ';');
		fwrite($handle, '<?php return $saved = '.$count.';  ?>');
		fclose($handle);

		return $count;
	}

	public function saveProgress($merchant_id, $progress)
	{
		$directory = strtr(self::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$filePath = \Yii::getAlias('@webroot').$directory;
		$handle = fopen($filePath, 'w');
		fwrite($handle, $progress);
		fclose($handle);
	}

	public function deleteFiles($merchant_id)
	{
		$directory = strtr(self::UPLOAD_ERROR_FILE, ['{merchant_id}'=>$merchant_id]);
		$filePath = \Yii::getAlias('@webroot').$directory;
		
		$dirName = dirname($filePath);

		$files = glob($dirName.'/*');
		foreach($files as $file) {
			if(is_file($file)) {
				unlink($file);
				//Data::createLog($file,'file.log');
			}
		}
	}

	public function actionViewErrors()
	{
		$this->layout = 'main';
		
		$merchant_id = Yii::$app->user->identity->id;

		$directory = strtr(self::UPLOAD_ERROR_FILE, ['{merchant_id}'=>$merchant_id]);
		$error_file = \Yii::getAlias('@webroot').$directory;

		$errorsArrayFile = dirname($error_file).'/error.php';
		$errorsArray = [];
		if (file_exists($errorsArrayFile)) 
		{
			return $this->render('error-view', [
                    'errorsArrayFile' => $errorsArrayFile,
				]);
		} 
		else 
		{
			Yii::$app->session->setFlash('error', "No Product Upload Error Exist.");
			return \Yii::$app->getResponse()->redirect(Data::getUrl('walmartproduct/index'));
		}
	}

	public function actionClearProgress()
	{
		$merchant_id = Yii::$app->request->post('merchant_id', false);
		
		if($merchant_id)
		{
			$directory = strtr(self::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
			$filePath = \Yii::getAlias('@webroot').$directory;
			if(file_exists($filePath)) {
				unlink($filePath);
			}

			echo json_encode(['success'=>'true']);
		}
		else
		{
			echo json_encode(['error'=>'true']);
		}
		die;
	}
}