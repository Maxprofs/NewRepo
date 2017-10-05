<?php
namespace frontend\modules\jet\components;
use Yii;
use yii\base\Component;
use frontend\modules\jet\models\JetConfiguration;

class Jetapimerchant extends component
{
	protected $apiHost;
	protected $user;
	protected $pass;  

	public function __construct($apiHost,$user,$pass)
	{
		$this->apiHost =$apiHost;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	public function JrequestTokenCurl()
	{	
		$ch = curl_init();
		$url= $this->apiHost.'/Token';
		$postFields='{"user":"'.$this->user.'","pass":"'.$this->pass.'"}';
		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json;"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	
		$server_output = curl_exec ($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($server_output, 0, $header_size);
		$body = substr($server_output, $header_size);
		curl_close ($ch);
		$token_data =json_decode($body);
		
		if(is_object($token_data) && isset($token_data->id_token))
			return json_decode($body);
		else		
			return false;
	}
	
	/*
	 * Post Request on Jetcom
	*/
	//	public function CPostRequest($method,$postFields){

	public function CPostRequest($method,$postFields=null, $merchant_id=null,&$status=false)
	{
		// New way to post data
	
		$url= $this->apiHost.$method;
		
		$tObject =$this->Authorise_token($merchant_id);
	
		$headers = array();
		$headers[] = "Content-Type: application/json";
		$headers[] = "Authorization: Bearer $tObject->id_token";
	
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);



		$server_output = curl_exec ($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$status=$http_code;
		if($http_code == 503){
			$file = 'var/jetapi/'.date('d-m-Y').'/'.$merchant_id.'/post.log';
	        $message = $url.PHP_EOL.$postFields.PHP_EOL.'Request Status : '.$http_code.PHP_EOL.PHP_EOL;
	        Data::createLog($message,$file,'a',true);
	        self::saveJetScheduleData($url,"POST",$postFields,$merchant_id);
		}

		$header = substr($server_output, 0, $header_size);
		$body = substr($server_output, $header_size);
		curl_close ($ch);

		return $body;
	}

	/* PUT Request on Jetcom
			*
			*/
	public function CPutRequest($method, $post_field, $merchant_id,&$status=false)
	{
		$url= $this->apiHost.$method;
		$ch = curl_init($url);
		$tObject =$this->Authorise_token($merchant_id);
		if($tObject==false)
			return false;
		$headers = array();
		$headers[] = "Content-Type: application/json";
		$headers[] = "Authorization: Bearer $tObject->id_token";
				
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_field);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		//curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec ($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$status=$http_code;
		if($http_code == 503 ){
			$file = 'var/jetapi/'.date('d-m-Y').'/'.$merchant_id.'/put.log';
	        $message = $url.PHP_EOL.$post_field.PHP_EOL.'Request Status : '.$http_code.PHP_EOL.PHP_EOL;
	        Data::createLog($message,$file,'a',true);
	        self::saveJetScheduleData($url,"PUT",$post_field,$merchant_id);
		}

		$header = substr($server_output, 0, $header_size);
		$body = substr($server_output, $header_size);
		curl_close ($ch);

		return $body;

	}
	
	public function CGetRequest($method,$merchant_id,&$status=false)
	{
		// authorise current token
		$tObject =$this->Authorise_token($merchant_id);
		
		if($tObject==false)
			return false;
		$ch = curl_init();
		$url= $this->apiHost.$method;
		$headers = array();
		$headers[] = "Content-Type: application/json";
		$headers[] = "Authorization: Bearer $tObject->id_token";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec ($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$status=$http_code;
		
		if($http_code==503)
		{
			$file = 'jetapi/'.date('d-m-Y').'/'.$merchant_id.'/get.log';
	        $message = $url.PHP_EOL.'Request Status : '.$http_code.PHP_EOL.PHP_EOL;
	        Data::createLog($message,$file,'a',true);
	        self::saveJetScheduleData($url,"GET","",$merchant_id);
		}
		$header = substr($server_output, 0, $header_size);
		$body = substr($server_output, $header_size);
		curl_close ($ch);

		return $body;
		//print_r($body); die;
	}
	
	public function Authorise_token($id)
	{
		//$merchant_id = \Yii::$app->user->identity->id;
		$merchant_id = $id;
		$Jtoken="";
		$modelFlag=false;
		$model = new JetConfiguration();
		$result=$model->find()->where(['merchant_id' => $merchant_id])->one();
		if($result){
			$modelFlag=true;
			$Jtoken=json_decode($result->jet_token);
		}
		//else{
			//$refresh_token =false;
			//Yii::$app->session->setFlash('error', 'API user & API password either or Invalid.Please set API user & API pass from jet configuration.');
			//return false;
		    //return $this->redirect(array("jetconfiguration/index"));
		//}
		$refresh_token =false;
		if(is_object($Jtoken) && $Jtoken!=null){
			$ch = curl_init();
			$url= $this->apiHost.'/authcheck';

			$headers = array();
			$headers[] = "Content-Type: application/json";
			$headers[] = "Authorization: Bearer $Jtoken->id_token";
	
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
			$server_output = curl_exec ($ch);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($server_output, 0, $header_size);
			$body = substr($server_output, $header_size);
			curl_close ($ch);
			
			$bjson = json_decode($body);
			
			if(is_object($bjson) &&
			 $bjson->Message!='' &&
			  $bjson->Message=='Authorization has been denied for this request.')
			 {
				// refresh token	
				$refresh_token =true;	
	   		 }		
		}
		else
		{
			$refresh_token =true;	
		}
		if($refresh_token){
			$token_data = $this->JrequestTokenCurl();
			if($token_data!= false && $modelFlag==true){
				$result->jet_token=json_encode($token_data);
				$result->save();
				return $token_data;
			}
			elseif($token_data!= false){
				return $token_data;
				//Yii::$app->session->setFlash('error', 'API user & API password either or Invalid.Please set API user & API pass from jet configuration.');
				//return $this->redirect(array("jetconfiguration/index"));
			}
			else{
				return false;
			}		
		}else{
			return $Jtoken;
		}
	
	}
	
	/**
	* GZIPs a file on disk (appending .gz to the name)
	*
	* From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
	* Based on function by Kioob at:
	* http://www.php.net/manual/en/function.gzwrite.php#34955
	*
	* @param string $source Path to file that should be compressed
	* @param integer $level GZIP compression level (default: 9)
	* @return string New filename (with .gz appended) if success, or false if operation fails
	*/
	public function gzCompressFile($source, $level = 9)
	{
		$dest = $source . '.gz';
		$mode = 'wb' . $level;
		$error = false;
		if ($fp_out = gzopen($dest, $mode)) {
		if ($fp_in = fopen($source,'rb')) {
		while (!feof($fp_in))
			gzwrite($fp_out, fread($fp_in, 1024 * 512));
				fclose($fp_in);
			} else {
				$error = true;
			}
				gzclose($fp_out);
			} else {
				$error = true;
			}
		if ($error)
			return false;
		else
			return $dest;
	}

	/*
		* New function to upload file
		*/
	public function uploadFile($localfile,$url,$merchant_id)	
	{
// 		$headers = [];
		
// 		$headers[] = "Authorization: Bearer $tObject->id_token";

		
			 $headers = array();
			 $headers[] = "Content-Type: application/json";
			 $headers[] = "x-ms-blob-type:BlockBlob";
			 $ch = curl_init();
			 curl_setopt($ch, CURLOPT_URL, $url);
			 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			 curl_setopt($ch, CURLOPT_HEADER, 1);
			 curl_setopt($ch, CURLOPT_PUT, 1);
			 $fp = fopen ($localfile, 'r');
			 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			 curl_setopt($ch, CURLOPT_INFILE, $fp);
			 curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
	
	
			 $http_result = curl_exec($ch);
			 $error = curl_error($ch);
			 $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
			 /* echo $http_code."<hr><pre>";
			 print_r($http_result);
			 die("<hr>hgf"); */
			 curl_close($ch);
			 fclose($fp);
	}
	
	public function uploadFile1($localfile ,$url,$merchant_id)
	{		
		 $tObject =$this->Authorise_token($merchant_id);
		 if($tObject==false)
			return false;
		 $headers = [];
		 $headers[] = "Content-Type: application/json";
		 $headers[] = "Authorization: Bearer $tObject->id_token";
		 $headers[] = "x-ms-blob-type:BlockBlob";
				
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, $url);
		 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		 curl_setopt($ch, CURLOPT_HEADER, 1);
		 curl_setopt($ch, CURLOPT_PUT, 1);
		 $fp = fopen ($localfile, 'r');
		 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 curl_setopt($ch, CURLOPT_INFILE, $fp);
		 curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));

		 $http_result = curl_exec($ch);
		 $error = curl_error($ch);
		 //$status = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
		 echo "<hr><pre>";
		 print_r($http_result);
		 die("<hr>hgf");
		 curl_close($ch);
		 fclose($fp);
	}
	public function trimString($str, $maxLen) 
	{	
		if (strlen($str) > $maxLen && $maxLen > 1) {
			preg_match("#^.{1,".$maxLen."}\.#s", $str, $matches);
			return $matches[0];
		} else {
			return $str;
		}
	}
	public static function saveJetScheduleData($url="",$request_type="",$api_data="",$merchant_id="")
	{
		if($merchant_id)
		{
			$jetScheduleData=Data::sqlRecords("SELECT api_data FROM `jet_api_schedule` WHERE id=1 LIMIT 0,1","one","select");
			$url=addslashes($url);
			$api_data=addslashes($api_data);
			if(isset($jetScheduleData['api_data']))
			{
				Data::sqlRecords("UPDATE `jet_api_schedule` SET `api_data`='".$api_data."',`api_url`='".$url."',`api_request_type`='".$request_type."',`is_enabled`=1,`merchant_id`='".$merchant_id."' WHERE 1");
			}
			else
			{
				Data::sqlRecords("INSERT INTO `jet_api_schedule`(`id`, `api_data`, `api_url`, `api_request_type`, `is_enabled`,`merchant_id`) VALUES (1,'".$api_data."','".$url."','".$request_type."',1,'".$merchant_id."')");
			}
		}
	}
	
}	

?>