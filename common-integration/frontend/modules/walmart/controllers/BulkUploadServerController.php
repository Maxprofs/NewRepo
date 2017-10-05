<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartThread;

/**
* BulkUploadServer controller
*/
class BulkUploadServerController extends Controller 
{
//	public $host = '192.168.0.39';
	public $host = 'localhost';
	public $port = '10000';
	public $socket = '';
	public $clients = [];
	public $data = ['user_type'=>'merchant','id'=>false];
	public $liveMembers = [];
	public $resourcesMap = [];

	private $_hashFilePath = '/frontend/modules/walmart/filestorage/bulkupload/hash';
	private $_serverErrorFilePath = '/frontend/modules/walmart/filestorage/bulkupload/error/{merchant_id}';

	public function beforeAction($action)
    {
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

	public function actionStart($data=false)
	{
		echo 'Start'.PHP_EOL;

	   	//Create TCP/IP sream socket
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		//reuseable port
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);

		//bind socket to specified host
		socket_bind($this->socket, 0, $this->port);
		echo 'socket Bind'.PHP_EOL;
		//listen to port
		socket_listen($this->socket);
		echo 'socket listen'.PHP_EOL;

		//create & add listning socket to the list
		$this->clients = array($this->getResourceId($this->socket)=>$this->socket);
		//start endless loop, so that our script doesn't stop
		while (true) {

			//manage multipal connections
			$changed = $this->clients;
			//returns the socket resources in $changed array
			socket_select($changed, $null, $null, 0, 10);
			
			//check for new socket
			if (in_array($this->socket, $changed)) {

//				print_r($_GET);
				$socket_new = socket_accept($this->socket); //accpet new socket
				echo 'Accept new socket'.PHP_EOL;
				$this->clients[$this->getResourceId($socket_new)] = $socket_new; //add socket to client array
				var_dump($this->getResourceId($socket_new));
				echo 'Reading new socket'.PHP_EOL;
				$header = socket_read($socket_new, 1024); //read data sent by the socket
				//print_r('Headers : ');print_r($header);echo PHP_EOL;
				$this->performHandshaking($header, $socket_new, $this->host, $this->port); //perform websocket handshake
				socket_getpeername($socket_new, $ip); //get ip address of connected socket
				
				
				
				//make room for new socket
				$found_socket = array_search($this->socket, $changed);
				unset($changed[$found_socket]);
			}
			
			//loop through all connected sockets
			foreach ($changed as $changed_socket) {
				echo 'looping sockets';
				//check for any incomming data
				while(socket_recv($changed_socket, $buf, 1024, 0) >= 1)
				{

					echo 'Updated Resource :'.$this->getResourceId($changed_socket).PHP_EOL;
					$resourceId = $this->getResourceId($changed_socket);
					echo 'recieveMessageFromClient'.PHP_EOL;
					//var_dump($buf);
					if($bufferMsg=json_decode($buf,true))
					{
						//echo "here: ";var_dump($bufferMsg);
						if($data = $this->getHashData($bufferMsg['hash']))
						{
							$sendMail = isset($bufferMsg['send_mail'])?true:false;
							
							$isClientOnline = false;
							foreach ($this->resourcesMap as $key => $resource) {
								if($resource['sender_ref'] == $data['sender_ref']) {
									//$resources[] = $this->clients[$key];
									$isClientOnline = true;
									if($bufferMsg['status']=='success') {
										$msgData = ['type'=>'notification', 'message'=>$bufferMsg['message'], 'progress'=>$bufferMsg['progress'], 'status'=>'success'];

										if(isset($bufferMsg['success_count'])) {
											$msgData['success_count'] = $bufferMsg['success_count'];
										}
									}
									else {
										$msgData = ['type'=>'notification', 'message'=>$bufferMsg['message'], 'status'=>'error'];

										if(isset($bufferMsg['progress'])) {
											$msgData['progress'] = $bufferMsg['progress'];
										}

										if(isset($bufferMsg['error_count'])) {
											$msgData['error_count'] = $bufferMsg['error_count'];
										}
									}
									$msg = $this->mask(json_encode($msgData));
									@socket_write($this->clients[$key], $msg,strlen($msg));
								}
							}

							if(!$isClientOnline && $sendMail)
							{
								$sendMailType = $bufferMsg['send_mail'];
								if($sendMailType == 'upload_complete')
								{
									$stats = self::getProductUploadStats($data['sender_ref']);
									//send mail to client regarding the upload status.
									Data::createLog('Send Mail', 'send_mail.log');
									Data::createLog(print_r($stats, true), 'send_mail.log');
								}
							}
						}
						else
						{
							echo 'Hash Not Found'.PHP_EOL;
						}
					}
					else
					{
						$received_text = $this->unMask($buf); //unmask data
						$tst_msg = json_decode($received_text,true); //json decode 
						if(isset($tst_msg['hash']))
						{
							if($data = $this->getHashData($tst_msg['hash']))
							{
								if(!isset($this->liveMembers[$data['sender_ref']])) {
									$this->liveMembers[$data['sender_ref']] = $data;
								}
								$this->resourcesMap[$resourceId] = $data;
								print_r($this->resourcesMap);

								$directory = strtr(BulkUploadController::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$data['sender_ref']]);
								$directory = \Yii::getAlias('@webroot').$directory;
								if (file_exists($directory)) 
								{
									$successCount = self::getSuccessCount($data['sender_ref']);
  									$errorCount = self::getErrorCount($data['sender_ref']);
									$progress = self::getUploadProgress($data['sender_ref']);

									if($progress != '100')
									{
										$msgData = array('type'=>'action', 'message'=>'disable_button', 'progress'=>$progress, 'success_count'=>$successCount, 'error_count'=>$errorCount);
									}
									else
									{
										$msgData = array('type'=>'action', 'message'=>'enable_button', 'progress'=>$progress, 'success_count'=>$successCount, 'error_count'=>$errorCount);
									}

									$msg = $this->mask(json_encode($msgData));
									$recieverSocket = $this->clients[$resourceId];
									@socket_write($recieverSocket,$msg,strlen($msg));
								}
							}
							else
							{
								echo 'Hash Not Found'.PHP_EOL;
							}
							echo 'recieved Hash:'.$tst_msg['hash'];
						}
						else
						{
							if(isset($tst_msg['action']) && $tst_msg['action']=='start-upload-process') {
								if(isset($this->resourcesMap[$resourceId]))
								{
									echo 'Starting Upload Process'.PHP_EOL;
									$merchant_id = $this->resourcesMap[$resourceId]['sender_ref'];
									echo "For merchant Id : $merchant_id".PHP_EOL;

									$resources = [];
									foreach ($this->resourcesMap as $key => $resource) {
										if($resource['sender_ref'] == $merchant_id) {
											$resources[] = $this->clients[$key];
										}
									}
                                    //$this->StartUploadProcess($merchant_id, $this->clients[$resourceId]);
									
									$this->StartUploadProcess($merchant_id, $resources);
									//$this->startViaThread($merchant_id, $resources);
								}
								else
								{
									print_r($this->resourcesMap);
									echo 'Resource '.$resourceId.' Not Found'.PHP_EOL;
								}
							}
						}
					}
					
					break 2; //exist this loop
				}
				
				$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
				if ($buf === false) { // check disconnected client
					// remove client for $this->clients array

					$this->clientDisconnected($this->getResourceId($changed_socket));
					
					
					//notify all users about disconnected connection
					/*$response = $this->mask(json_encode(array('type'=>'system', 'message'=>$ip.' disconnected')));
					$this->sendMessage($response);*/
				}
			}
		      
		}
		// close the listening socket
		socket_close($this->socket);
	}

	public function clientDisconnected($resourceId)
	{
		/*if($data['sender_type']=='customer' ){
			$response = array('message_type'=>'status', 'message'=>'disconnected','user_id'=>$data['sender_ref']);
			//notify to shopify walmart team that client is connected
			
			if(isset($this->liveMembers['customer'][$data['sender_ref']]) && $this->liveMembers['customer'][$data['sender_ref']]['resource_id']==$resourceId){
				$this->sendMessageToGroupMembers('shopify_walmart_team',$response);
				$this->setInactive($data['sender_ref']);
				unset($this->liveMembers['customer'][$data['sender_ref']]);
			}
			unset($this->resourcesMap[$resourceId]);
		}
		else
		{
			unset($this->resourcesMap[$resourceId]);
		}*/

		if(isset($this->resourcesMap[$resourceId])) {
			$data = $this->resourcesMap[$resourceId];
			echo 'disconnected : Mid-'.$data['sender_ref'].', Rid : '.$resourceId.PHP_EOL;
			unset($this->resourcesMap[$resourceId]);
		}
		else {
			echo 'Resource '.$resourceId.' Not Found'.PHP_EOL; 
		}

		if(isset($this->clients[$resourceId])) {
			unset($this->clients[$resourceId]);
		}
	}

	public function getHashData($hash)
	{
		$directory = \Yii::getAlias('@webroot').$this->_hashFilePath;
		if(file_exists($directory.'/'.$hash)){
			return json_decode(file_get_contents($directory.'/'.$hash),true);
		}
		return false;
	}

	public function getResourceId($resource)
	{
		$idString = (string)$resource;
		return str_replace('Resource id #', '', $idString);
	}

	//Unmask incoming framed message
	public function unMask($text) {
		$length = ord($text[1]) & 127;
		if($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		}
		elseif($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		}
		else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$text .= $data[$i] ^ $masks[$i%4];
		}
		return $text;
	}

	//Encode message for transfer to client.
	public function mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header.$text;
	}

	//handshake new client.
	public function performHandshaking($receved_header,$client_conn, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $receved_header);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}

		if(isset($headers['Sec-WebSocket-Key']))
		{
			$secKey = $headers['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			//hand shaking header
			$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"WebSocket-Origin: {$this->host}\r\n" .
			"WebSocket-Location: ws://{$this->host}:{$this->port}/demo/shout.php\r\n".
			"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
			socket_write($client_conn,$upgrade,strlen($upgrade));
		}
	}

	public function actionGeneratehash()
	{
		$merchant_id = Yii::$app->user->identity->id;
  		
  		$data = [
  					'sender_ref'=>$merchant_id,
  				 	'action'=>'product_upload',
  				];

  		$hash = $this->getHash($data);
  		$this->setHash($hash,$data);
  		return $hash;
	}

	public function getHash($data)
  	{
  		$finalString = '';
  		foreach($data as $val){
  			$finalString .= $val;
  		}
  		return md5($finalString);
  	}

  	public function setHash($hash,$data)
  	{
  		$directory = \Yii::getAlias('@webroot').$this->_hashFilePath;
  		if(file_exists($directory.'/'.$hash)){
  			/*if($data['sender_type']=='customer'){
  				Data::sqlRecords('UPDATE client_chat SET active=1 WHERE merchant_id='.$data['sender_ref'],null,'update');
  			}
  			return;*/
  		}
  		/*if($data['sender_type']=='customer'){
	  		$conversation = Data::sqlRecords('select conversation from client_chat where merchant_id='.$data['sender_ref'],null,'one');
	  		if($conversation){
	  			Data::sqlRecords('UPDATE client_chat SET active=1 WHERE merchant_id='.$data['sender_ref'],null,'update');
	  		}
	  		else
	  		{
	  			$conversation = addslashes(json_encode([]));
				Data::sqlRecords("insert into client_chat (merchant_id,conversation,active) values({$data['sender_ref']},'{$conversation}','1')",null,'insert');
	  		}
	  	}*/

        if (!file_exists($directory)){
            mkdir($directory,0775, true);
        }

        if(!file_exists($directory.'/'.$hash)) {
	        $handle = fopen($directory.'/'.$hash,'w');
	        fwrite($handle,json_encode($data));
	        fclose($handle);
    	}
  	}

  	public function sendCurlRequest($resourceId)
  	{
  		$data = ['resource_id' => $resourceId];
  		$url = Data::getUrl('bulk-upload-server/curl-request');
  		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_TIMEOUT,1);
		$result = curl_exec($ch);
		curl_close($ch);
  	}

  	public function actionCurlRequest()
  	{
  		/*$resourceId = Yii::$app->request->post('resource_id',false);
  		if($resourceId !== false)
  		{
  			//Data::createLog('', 'data.log');
  		}*/

  		/*$merchant_id = Yii::$app->request->post('merchant_id', false);
  		if($merchant_id===false)
  			$merchant_id = '656';

  		$dir = Yii::getAlias('@webroot') . strtr('/frontend/modules/walmart/filestorage/bulkupload/{merchant_id}', ['{merchant_id}'=>$merchant_id]) .'/product.php';
		if (!file_exists(dirname($dir))) {
            mkdir(dirname($dir), 0777, true);
        }

        $content = '<?php return $arr = [];';

    	$handle = fopen($dir,'w');
		fwrite($handle, $content);
		fclose($handle);*/
  	}

  	public function startViaThread($merchant_id, $socketResources)
  	{
  		$thread = new WalmartThread($socketResources);
  		$thread->start();
  	}

  	public function StartUploadProcess($merchant_id, $socketResources)
  	{
		$data = ['merchant_id' => $merchant_id];
//  		$url = Data::getUrl('bulk-upload/start-upload');
  		$url =  \Yii::getAlias('@webwalmarturl').'/bulk-upload/start-upload';
  		echo 'send curl to url '.$url.PHP_EOL;
  		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_TIMEOUT,1);
		$response = curl_exec($ch);
		//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//var_dump($httpcode);
		curl_close($ch);
//		var_dump($response);

		return;
  	}

  	public function createErrorLog($merchant_id, $error, $fileName)
  	{
  		$directory = strtr($this->_serverErrorFilePath, ['{merchant_id}'=>$merchant_id]);
		$directory = \Yii::getAlias('@webroot').$directory;
		Data::createDirectory($directory, 0777);

		$directory = $directory.'/'.$fileName.'.log';
		$handle = fopen($directory,'w');
		fwrite($handle, $error);
		fclose($handle);
  	}

  	public function getProductUploadStats($merchant_id)
  	{
  		$feedError = self::getFeedError($merchant_id);

  		if(count($feedError))
  		{
  			$error = [];
  			if(isset($feedError['error'])) {
  				$error['code'] = $feedError['error']['code'];
  				$error['description'] = $feedError['error']['description'];
  			} elseif (isset($feedError['code'])) {
  				$error['code'] = $feedError['code'];
  				$error['description'] = $feedError['description'];
  			}
  			return ['error'=>$error];
  		}
  		else
  		{
  			$successCount = self::getSuccessCount($merchant_id);
  			$errorCount = self::getErrorCount($merchant_id);
  			return ['success'=>$successCount, 'error'=>$errorCount, 'feedError'=>$feedError];
  		}
  	}

  	public function getSuccessCount($merchant_id)
  	{
  		$directory = strtr(BulkUploadController::SUCCESS_COUNT_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$success_count_file = \Yii::getAlias('@webroot').$directory;

		$count = 0;
		if (file_exists($success_count_file)) {
            $count = require $success_count_file;
        }

        return $count;
  	}

  	public function getErrorCount($merchant_id)
  	{
  		$directory = strtr(BulkUploadController::ERROR_COUNT_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$error_count_file = \Yii::getAlias('@webroot').$directory;

		$count = 0;
		if (file_exists($error_count_file)) {
            $count = require $error_count_file;
        }

        return $count;
  	}

  	public function getFeedError($merchant_id)
  	{
  		$directory = strtr(BulkUploadController::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$filePath = dirname(\Yii::getAlias('@webroot').$directory);
		$filePath .= '/feedError.log';

		if(file_exists($filePath))
		{
			$handle = fopen($filePath, 'r');
			$jsonData = fread($handle,filesize($filePath));
			fclose($handle);
			$data = json_decode($jsonData, true);
			return $data;
		}
		return [];
  	}

  	public function getUploadProgress($merchant_id)
  	{
  		$directory = strtr(BulkUploadController::UPLOAD_STATUS_FILE_PATH, ['{merchant_id}'=>$merchant_id]);
		$filePath = \Yii::getAlias('@webroot').$directory;
		
		$progress = '';
		if(file_exists($filePath))
		{
			$handle = fopen($filePath, 'r');
			$progress = fread($handle,filesize($filePath));
			fclose($handle);
		}
		return $progress;
  	}
}