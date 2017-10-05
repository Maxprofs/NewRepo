<?php 
namespace frontend\modules\jet\components;
use Yii;
use yii\base\Component;
use yii\helpers\Url;

class Data extends component
{
    const UNDER_JET_REVIEW = "Under Jet Review";
    const AVAILABLE_FOR_PURCHASE = "Available for Purchase";
    const NOT_UPLOADED = "Not Uploaded";
    const MISSING_LISTING_DATA = "Missing Listing Data";
    const EXCLUDED = "Excluded";
    const ARCHIVED = "Archived";
    const PURCHASED = "Purchased";
    const NOT_PURCHASE = "Not Purchase";
    const LICENSE_EXPIRED = "License Expired";
    const TRIAL_EXPIRED = "Trial Expired";
    const UNAUTHORIZED = "Unauthorized";
    const MARKETPLACE = 'jet';
    const CALL_SCHEDULE_STATUS = 'pending';
    const NO_OF_REQUEST = 1;
    const APP_NAME_JET = 'jet';

    
    public static function sqlRecords($query,$type=null,$queryType=null)
    {
        $connection=Yii::$app->getDb();
        $response=[];
        if($queryType=="update" || $queryType=="delete" || $queryType=="insert" || ($queryType==null && $type==null))            
            $response = $connection->createCommand($query)->execute();    
        elseif($queryType=='column') 
            $response = $connection->createCommand($query)->queryColumn();        
        elseif($type=='one')
            $response=$connection->createCommand($query)->queryOne();        
        else
            $response=$connection->createCommand($query)->queryAll();        
        unset($connection);
        return $response;
    }

    public static function getWalmartShopDetails($merchant_id)
    {      
        $shopDetails = array();
        if(is_numeric($merchant_id)) {
            $shopDetails = self::sqlRecords("SELECT `token`,`currency`,`email`,`shop_name`,`shop_url` FROM `walmart_shop_details` WHERE merchant_id=".$merchant_id, 'one');
        }
        return $shopDetails;
    }

    public static function getUrl($path)
    {
        $url = Url::toRoute([$path]);
        return $url;
    }
   
    public static function saveConfigValue($merchant_id, $field_name, $field_value)
    {
        $query = "SELECT `data`,`value` FROM  `walmart_config` WHERE `merchant_id`='".$merchant_id."' AND `data`='".$field_name."'";
        if (empty(self::sqlRecords($query,"one","select"))) 
        {
            self::sqlRecords("INSERT INTO `walmart_config` (`data`,`value`,`merchant_id`) values('".$field_name."','".$field_value."','".$merchant_id."')", null, "insert");
        } 
        else 
        {
            self::sqlRecords("UPDATE `walmart_config` SET `value`='".$field_value."' where `merchant_id`='".$merchant_id."' AND `data`='".$field_name."'", null, "update");
        }
    }
    public static function jetsaveConfigValue($merchant_id, $field_name, $field_value)
    {
        $query = "SELECT `data`,`value` FROM  `jet_config` WHERE `merchant_id`='".$merchant_id."' AND `data`='".$field_name."'";
        
        if (empty(self::sqlRecords($query,"one","select"))) 
        {
            $sql="INSERT INTO `jet_config` (`data`,`value`,`merchant_id`) values('".$field_name."','".$field_value."','".$merchant_id."')";
            self::sqlRecords($sql,NULL,'insert');
        } 
        else 
        {           
            $sql="UPDATE `jet_config` SET `value`='".$field_value."' where `merchant_id`='".$merchant_id."' AND `data`='".$field_name."'";
            self::sqlRecords($sql, null, "update");
        }
    }
    public static function jetremoveConfigValue($merchant_id, $field_name)
    {
        $sql = "DELETE FROM `jet_config` WHERE `merchant_id`='{$merchant_id}' AND `data`='{$field_name}' ";        
        self::sqlRecords($sql,null,'delete');
    }

    public static function importWalmartProduct($merchant_id)
    {
        $productColl=[];
        $query="select id,product_type,type from jet_product where merchant_id='".$merchant_id."'";
        $productColl=self::sqlRecords($query,'all','select');
        $countVar=0;
        if(is_array($productColl) && count($productColl)>0)
        {
            $queryProduct="INSERT INTO `walmart_product` (`product_id`,`merchant_id`,`product_type`,`status`)VALUES";
            $queryVariant="INSERT INTO `walmart_product_variants` (`product_id`,`merchant_id`,`option_id`)VALUES";
            foreach ($productColl as $value) 
            {
                $queryProduct.="('".$value['id']."','".$merchant_id."','".addslashes($value['product_type'])."','Not Uploaded'),";
                if($value['type']=="variants")
                {
                    $query="select option_id from jet_product_variants where product_id='".$value['id']."'";
                    $productVarColl=[];
                    $productVarColl=self::sqlRecords($query,'all','select');

                    foreach ($productVarColl as $val) 
                    {
                        $countVar++;
                        $queryVariant.="('".$value['id']."','".$merchant_id."','".$val['option_id']."'),";
                    }
                }
            }
            $queryProduct=rtrim($queryProduct,',');
            $queryVariant=rtrim($queryVariant,',');
            self::sqlRecords($queryProduct,null,'insert');
            if($countVar>0)
                self::sqlRecords($queryVariant,null,'insert');
            //insert product type
            $productTypeColl=[];
            $query="select id from `walmart_category_map` where merchant_id='".$merchant_id."'";
            $productTypeColl= self::sqlRecords($query,"all",'select');
            if(!$productTypeColl)
            {
                $jetproductTypeColl=[];
                $query="select product_type from `jet_category_map` where merchant_id='".$merchant_id."'";
                $jetproductTypeColl= self::sqlRecords($query,"all",'select');
                if(is_array($jetproductTypeColl) && count($jetproductTypeColl)>0)
                {
                    $queryProType="INSERT INTO `walmart_category_map` (`product_type`,`merchant_id`)VALUES";
                    foreach ($jetproductTypeColl as $v) 
                    {
                        $queryProType.="('".addslashes($v['product_type'])."','".$merchant_id."'),";
                    }
                    $queryProType=rtrim($queryProType,',');
                    self::sqlRecords($queryProType,null,'insert');
                }
            }
        }
    }

    public static function getShopifyShopDetails($sc)
    {
        $response = $sc->call('GET','/admin/shop.json');
        return $response;
    }

    /**
     * Get Product Tax code
     * @param [] $product
     * @return string | bool
     */
    public static function GetTaxCode($product, $merchant_id)
    {
        $tax_code = '';
        $productType = '';
        if(is_array($product)) {
            $tax_code = $product['tax_code'];
            $productType = $product['product_type'];
        }
        else {
            $tax_code = $product->tax_code;
            $productType = $product->product_type;
        }

        if(!$tax_code) {
            $query = "SELECT `tax_code` FROM `walmart_category_map` WHERE `product_type`='".$productType."' AND `merchant_id`=".$merchant_id." LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');
            if($result && (isset($result['tax_code']))) {
                return $result['tax_code'];
            }
            else {
                $query = "SELECT `value` FROM `walmart_config` WHERE `data`='tax_code' AND `merchant_id`=".$merchant_id." LIMIT 0,1";
                $result = Data::sqlRecords($query, 'one');
                if($result && (isset($result['value']))) {
                    return $result['value'];
                }
            }
        } else {
            if(!is_numeric($tax_code))
                return false;
            else
                return $tax_code;
        }
        return false;
    }

    public static function createLog($message,$path='jet-common.log',$mode='a',$sendMail=false)
    {
        $file_path=Yii::getAlias('@webroot').'/var/jet/'.$path;
        $dir = dirname($file_path);
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        $fileOrig=fopen($file_path,$mode);
        fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".$message);
        fclose($fileOrig); 
        if($sendMail){
            self::sendEmail($file_path,$message);
        }
    }

    public static function createFile($path='jet-common.log',$mode='a')
    {
        $file_path=Yii::getAlias('@webroot').'/var/jet/'.$path;
        $dir = dirname($file_path);
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        $fileOrig=fopen($file_path,$mode);
        return $fileOrig;
    }

    public static function getKey($string){
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    /**
     * function for sending mail with attachment
     */
    public static function sendEmail($file,$msg,$email = 'satyaprakash@cedcoss.com')
    {
       try
       {
            $name = 'Shopify-jet | Cedcommerce';
        
            $EmailTo = $email.',kshitijverma@cedcoss.com';
            $EmailFrom = $email;
            $EmailSubject = "Shopify-Jet Exception Log" ;
            $from ='Shopify-jet Cedcommerce';
            $message = $msg;
            $separator = md5(time());

            // carriage return type (we use a PHP end of line constant)
            $eol = PHP_EOL;

            // attachment name
            $filename = 'exception';//store that zip file in ur root directory
            $attachment = chunk_split(base64_encode(file_get_contents($file)));

            // main header
            $headers  = "From: ".$from.$eol;
            $headers .= "MIME-Version: 1.0".$eol; 
            $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

            // no more headers after this, we start the body! //

            $body = "--".$separator.$eol;
            $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol.$eol;
            $body .= $message.$eol;

            // message
            $body .= "--".$separator.$eol;
            /*  $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
            $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
            $body .= $message.$eol; */

            // attachment
            $body .= "--".$separator.$eol;
            $body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol; 
            $body .= "Content-Transfer-Encoding: base64".$eol;
            $body .= "Content-Disposition: attachment".$eol.$eol;
            $body .= $attachment.$eol;
            $body .= "--".$separator."--";

            // send message
            if (mail($EmailTo, $EmailSubject, $body, $headers)) {
                $mail_sent = true;
            } else {
                $mail_sent = false;
            }
        }
        catch(Exception $e)
        {
            
        }
    }
    /**
     * Get Option Values Simple Product
     */
    public static function getOptionValuesForSimpleProduct($product)
    {
        $options = [];
        if (is_array($product) && isset($product['variants'])) {
            $variant = reset($product['variants']);
            if (isset($product['options'])) {
                foreach ($product['options'] as $value) {
                    if ($value['name'] != 'Title') {
                        $options[$value['name']] = $variant['option' . $value['position']];
                    }
                }
            }
        }

        if (count($options))
            return json_encode($options);
        else
            return '';
    }

    /**
    * preapre curl request for ios and android notification 
    */
    public static function curlrequest($url,$curtRequestParams,$merchant_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL =>$url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $curtRequestParams,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        $file_dir = \Yii::getAlias('@webroot').'/var/jet/notification/'.$merchant_id.'/'.date("Y-m-d H:i:s");
        if (!file_exists($file_dir)){
          mkdir($file_dir,0775, true);
        }
        $filenameOrig="";
        $filenameOrig=$file_dir.'/'.$merchant_id.'.log';
        $fileOrig="";
        $fileOrig=fopen($filenameOrig,'w+');
       // $handle = fopen($file_dir.'/'.$data['id'].'-qty-price.log', 'w+');
        fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($resp));
        fclose($fileOrig);
        return;
    }
    public static function getjetConfiguration($merchant_id)
    {
        $jetConfiguration =[];
        $jetConfiguration=self::sqlRecords("SELECT `fullfilment_node_id`,`api_user`,`api_password`,`merchant`,`merchant_email` from `jet_configuration` where merchant_id='".$merchant_id."'",'one','select');        
        return $jetConfiguration;               
    }
    public static function getjetConfig($merchant_id)
    {
        $jetConfig=array();
        $jetConfig=self::sqlRecords("SELECT `data`,`value` from `jet_config` where merchant_id='".$merchant_id."'",'all','select');        
        return $jetConfig;               
    }

    public static function saveDynamicPricevalue($merchant_id,$product_id,$sku, $min_price,$current_price,$max_price,$bid_price)
    {
        $query = "SELECT `sku` FROM  `jet_dynamic_price` WHERE `merchant_id`='{$merchant_id}' AND `sku`='{$sku}' AND `product_id`='{$product_id}'";
        if (empty(self::sqlRecords($query,"one","select"))) 
        {
            $sql = "INSERT INTO `jet_dynamic_price` (`merchant_id`,`product_id`,`sku`,`min_price`,`current_price`,`max_price`,`bid_price`) values('".$merchant_id."','".$product_id."','".$sku."','".$min_price."','".$current_price."','".$max_price."','".$bid_price."')";            
            self::sqlRecords($sql, null, "insert");
        } 
        else 
        {
            $sql = "UPDATE `jet_dynamic_price` SET `min_price`='".$min_price."',`current_price`='".$current_price."',`max_price`='".$max_price."',`bid_price`='".$bid_price."' where `merchant_id`='".$merchant_id."' AND `product_id`='".$product_id."' AND `sku`='".$sku."'";            
            self::sqlRecords($sql, null, "update");
        }
    }
    public static function generateReport($jetHelper=[],$type=null,$merchant_id,$handle="")
    {
    	$status = "";
        $StatusReport = $jetHelper->CPostRequest('/reports/'.$type,"",$merchant_id,$status);  
        $reportId=json_decode($StatusReport,true);
        fwrite($handle, "post request to generate response data: Status =>".$status.PHP_EOL.$StatusReport.PHP_EOL);
        if(isset($salesDataCollection) && is_array($salesDataCollection) && isset($salesDataCollection['report_id']))
        {
        	$report_status = 'requested';
        	while($report_status != 'ready' && $secondLimit<=30)
        	{
        		$rawDetails1 = "";
        		$rawDetails1 = $jetHelper->CGetRequest('/reports/state/'.$salesDataCollection['report_id'],$merchant_id, $status_code);
        		$salesDataCollection1 = json_decode($rawDetails1, true);
        		if(isset($salesDataCollection1) && is_array($salesDataCollection1) && isset($salesDataCollection1['report_id']))
        		{
        			$report_status = $salesDataCollection1['report_status'];//
        			if($report_status!='ready'){
        				$secondLimit += 8;
        				sleep(8);//seconds
        			}
        		}else{
        			$secondLimit += 8;
        			sleep(8);//seconds
        		}
        	}
        	if(isset($salesDataCollection1) && is_array($salesDataCollection1) && isset($salesDataCollection1['report_url']) && $salesDataCollection1['report_type']=='SalesData')
        	{
        		$output = "";
        		$ch = curl_init();
        		curl_setopt($ch, CURLOPT_URL, $salesDataCollection1['report_url']);
        		//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        		//curl_setopt($ch, CURLOPT_HEADER, 1);
        		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        		$output = curl_exec ($ch);
        		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        		if($httpcode == 200){
        			$filePath = \Yii::getAlias('@webroot').'/var/jet/report/'.$merchant_id.'/salesdata/salesdata.gz';
        			if(!file_exists(dirname($filePath))) {
        				mkdir(dirname($filePath), 0775, true);
        			}
        			$file = fopen($filePath, "w");
        			fwrite($file, $output);
        			fclose($file);
        			$uncompressedFilePath = \Yii::getAlias('@webroot').'/var/jet/report/'.$merchant_id.'/salesdata/uncompressed';
        			if(!file_exists(dirname($uncompressedFilePath))) {
        				mkdir(dirname($uncompressedFilePath), 0775, true);
        			}
        			self::uncompressGz($filePath, $uncompressedFilePath);
        			return true;
        		}
        	}
        }
        return false;
    }
    public static function generateReportUrl($report_id=null,$jetHelper=[],$type=null,$merchant_id=14,$handle=false)
    {
        $status=true;
        $productStatusReportData = $jetHelper->CGetRequest('/reports/state/'.$report_id,$merchant_id);    
        $productStatusReportIdDetailsArr=json_decode($productStatusReportData,true);
        fwrite($handle,"generate response report data:".PHP_EOL.$productStatusReportData.PHP_EOL);
        if(isset($productStatusReportIdDetailsArr['report_url']))
        {
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $file_path=Yii::getAlias('@webroot').'/var/jet/report/'.$type.'/'.$merchant_id;
            if(!file_exists($file_path)){
                mkdir($file_path,0775, true);
            }
            //chmod($file_path, 0777);
            //echo $merchant_id."<br>";
            //echo $file_path.'/*.gz';die;
            array_map('unlink', glob($file_path.'/*.gz'));
            array_map('unlink', glob($file_path."/*.txt"));
            $fileNewPath=$file_path.'/'.$productStatusReportIdDetailsArr['report_id'].'.gz';

            $fileUrl = $file_path.'/'.$productStatusReportIdDetailsArr['report_id'];
            $content = file_get_contents($productStatusReportIdDetailsArr['report_url'], false, stream_context_create($arrContextOptions));
            file_put_contents($fileNewPath, $content);

            $buffer_size = 40096; // read 4kb at a time
            $out_file_name = str_replace('.gz', '', $fileNewPath); 
            // Open our files (in binary mode)
            $file = gzopen($fileNewPath, 'rb');
            $out_file = fopen($out_file_name, 'wb'); 
            while (!gzeof($file)) {
                fwrite($out_file, gzread($file, $buffer_size));
            }
            fclose($out_file);
            gzclose($file);
            return $fileUrl;
        }
        return false;
    }

    public static function checkInstalledApp($merchant_id,$type=false,&$installData=[])
    {
        $installInfo = $jetData = [];
        $jetData = self::sqlRecords("SELECT `auth_key` FROM `user` WHERE id='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($jetData['auth_key']) && ($jetData['auth_key']!="") )
        {
            $installInfo['jet']['url']=Yii::getAlias('@webjeturl');
            if($type)
               $installInfo['jet']['type']="Switch";
        }
        else
        {
            $installInfo['jet']['url']='https://apps.shopify.com/jet-integration';
            if($type)
               $installInfo['jet']['type']="Install";
        }
        $walmartData=self::sqlRecords("SELECT `id` FROM `walmart_shop_details` WHERE merchant_id='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($walmartData['id']))
        {
            $installData['walmart']=true;
            $installInfo['walmart']['url']=Yii::getAlias('@webwalmarturl');
            if($type)
               $installInfo['walmart']['type']="Switch"; 
        }
        else
        {
            $installInfo['walmart']['url']='https://apps.shopify.com/walmart-marketplace-integration';
            if($type)
               $installInfo['walmart']['type']="Install"; 
        }
        $neweggData=self::sqlRecords("SELECT `id` FROM `newegg_shop_detail` WHERE merchant_id='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($neweggData['id']))
        {
            $installData['newegg']=true;
            $installInfo['newegg']['url']=Yii::getAlias('@webneweggurl');
            if($type)
               $installInfo['newegg']['type']="Switch"; 
        }
        else
        {
            $installInfo['newegg']['url']='https://apps.shopify.com/newegg-marketplace-integration';
            if($type)
               $installInfo['newegg']['type']="Install"; 
        }
        $searsData=self::sqlRecords("SELECT `id` FROM `sears_shop_details` WHERE merchant_id='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($searsData['id']))
        {
        	$installData['sears']=true;
        	$installInfo['sears']['url']=Yii::getAlias('@websearsurl');
        	if($type)
        		$installInfo['sears']['type']="Switch";
        }
        else
        {
        	$installInfo['sears']['url']='https://shopify.cedcommerce.com/integration/sears/site/login';
        	if($type)
        		$installInfo['sears']['type']="Install";
        }
        return $installInfo;
    }
    public static function createSingleWebhook($sc,$topic,$path)
    {
        self::deleteWebhookByTopic($sc,$topic);
        $webhookData['webhook']=['topic'=>$topic,"address"=>$path];
        $response = $sc->call('POST','/admin/webhooks.json',$webhookData);
        if(!isset($response['errors'])){
            return true;
        }
        return false;
    }
    public static function deleteWebhookByTopic($sc,$topic)
    {
        $webhookData=$sc->call("GET","/admin/webhooks.json",["topic"=>$topic]);
        if(is_array($webhookData) && count($webhookData)>0)
        {
            foreach ($webhookData as $value) 
            {
                $sc->call('DELETE','/admin/webhooks/'.$value['id'].'.json');
            }
        }
    }
    public static function insertLiveProduct($merchant_sku,$product_title,$status,$inv="",$merchant_id)
    {

        $isAlreadyExist = [];
        $sql = "SELECT `id` FROM `products_listed_on_jet` WHERE `merchant_id`= '{$merchant_id}' AND `sku`='".addslashes($merchant_sku)."' ";
        $isAlreadyExist = self::sqlRecords($sql,'one','select');
        if (empty($isAlreadyExist)) 
        {
            $sqlInsert = "INSERT INTO `products_listed_on_jet` (`merchant_id`, `sku`, `title`, `status`, `has_inv`) VALUES ('{$merchant_id}', '".addslashes($merchant_sku)."', '".addslashes($product_title)."', '{$status}', '{$inv}')";
            self::sqlRecords($sqlInsert,null,'insert');
        }
        else
        {
            $sqlUpdate = "UPDATE `products_listed_on_jet` SET `status` = '{$status}',`has_inv`='{$inv}' WHERE `sku` = '".addslashes($merchant_sku)."' AND  `merchant_id` = '{$merchant_id}'";
            self::sqlRecords($sqlUpdate,null,'update');   
        }
    }

    /* 
     * function for creating log 
     */
    public static function createExceptionLog($functionName,$msg,$shopName = 'common')
    {
        $dir = \Yii::getAlias('@webroot').'/var/jet/exceptions/'.$functionName.'/'.$shopName;
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        try
        {
            throw new Exception($msg);
        }catch(Exception $e){
            $filenameOrig = $dir.'/'.time().'.txt';
            $handle = fopen($filenameOrig,'a');
            $msg = date('d-m-Y H:i:s')."\n".$msg."\n".$e->getTraceAsString();
            fwrite($handle,$msg);
            fclose($handle);
            $this->sendEmail($filenameOrig,$msg);   
        }
        
    }

    /**
    * get errored product info on shopify
    * @return string
    */
    public static function getCustomsku($id)
    {
        $custom_sku = 'ced_'.$id;
        return $custom_sku;
    }
    public static function checkMappedAttributes($attribute_value,$merchant_id)
    {
        $jetMappedAttrData=Data::sqlRecords("SELECT `jet_attribute_id` FROM `jet_attribute_map` WHERE `merchant_id`=".$merchant_id." AND `attribute_value`='".addslashes($attribute_value)."' LIMIT 0,1","one","select");
        if(isset($jetMappedAttrData['jet_attribute_id'])){
            return $jetMappedAttrData['jet_attribute_id'];
        }
        return false;
    }

    public static function custom_number_format($n, $precision = 3)
    {
        if ($n < 1000) {
            // Anything less than a billion
            $n_format = number_format($n);
        }
        else if ($n < 1000000) {
            // Anything less than a million
            $n_format = number_format($n / 1000, $precision) . 'K';
        } else if ($n < 1000000000) {
            // Anything less than a billion
            $n_format = number_format($n / 1000000, $precision) . 'M';
        } else {
            // At least a billion
            $n_format = number_format($n / 1000000000, $precision) . 'B';
        }

        return $n_format;
    }
    public static function trimString($str, $maxLen)
    {
        if (strlen($str) > $maxLen && $maxLen > 1) {
            preg_match("#^.{1,".$maxLen."}\.#s", $str, $matches);
            return $matches[0];
        } else {
            return $str;
        }
    }
    public static function sendCurlRequest($data=[],$url="")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    public static function getJetScheduleMessage(){
        $jetScheduleData=Data::sqlRecords("SELECT `api_url` FROM `jet_api_schedule` WHERE id=1 LIMIT 0,1","one","select");
        $scheduleParams=['ready'=>"Fetch jet order api ",'withoutShipmentDetail'=>'Jet order data api ','shipped'=>'Jet order shipment api ','acknowledge'=>'Jet order acknowledgement api ','returns'=>'Return order api ','refunds'=>'Refund order api '];
        if(isset($jetScheduleData['api_url']))
        {
            foreach($scheduleParams as $key=>$value) 
            {
                if(strpos($jetScheduleData['api_url'], $key) !== false)
                {
                    return $value."is under maintenance. Want to contact Jet Team <a href='https://jetsupport.desk.com/customer/portal/emails/new' target='blank'>Click Here</a>";
                }   
            }   
        }
        return false;
    }
    
    public static function getConfigValue($merchant_id, $field_name = 'all')
    {
    	if ($field_name != 'all') {
    		$query = "SELECT `data`,`value` FROM  `jet_config` WHERE `merchant_id`='" . $merchant_id . "' AND `data` LIKE '" . $field_name . "'";
    		$result = self::sqlRecords($query, "one");
    		if (!empty($result)) {
    			return $result['value'];
    		}
    	} else {
    		$query = "SELECT `data`,`value` FROM  `sears_config` WHERE `merchant_id`='" . $merchant_id . "'";
    		$result = self::sqlRecords($query, "all");
    		if (empty($result)) {
    			$config = [];
    			foreach ($result as $value) {
    				$config[$value['data']] = $value['value'];
    			}
    			return $config;
    		}
    	}
    	return false;
    }
}
?>
