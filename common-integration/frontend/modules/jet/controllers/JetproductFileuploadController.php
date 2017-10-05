<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\components\Productvalidator;
use frontend\modules\jet\models\JetProductFileUpload;
use frontend\modules\jet\models\JetProductFileUploadSearch;
use frontend\modules\jet\components\Jetproductinfo;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class JetproductFileuploadController extends JetmainController
{
	protected $jetHelper;
	
	public function behaviors()
	{
		return [
				'verbs' => [
						'class' => VerbFilter::className(),
						'actions' => [
								'delete' => ['post'],
						],
				],
		];
	}
	
	/**
	 * Lists all JetProductFileUpload models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new JetProductFileUploadSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
	
		return $this->render('index', [
				'searchModel' => $searchModel,
				'dataProvider' => $dataProvider,
		]);
	}
    /*raza start*/
    /*file upload for price*/
    public function actionPriceupdate()
    {

        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;

        $response  = $arrPrice = [];
      
        $validatorObj = new Productvalidator();
        $arrPrice = $validatorObj->priceSync($merchant_id,$arrPrice);
        if (!empty($arrPrice)) {
           
            $chunkStatusArray=array_chunk($arrPrice, 10000);

            $totalProd = count($arrPrice);
            $totalPages = count($chunkStatusArray);
            foreach ($chunkStatusArray as $ind => $value)
            {   
                $session->set('productarr'.$ind, $value);
            }
            return $this->render('batchupdateprice', [
                    'totalcount' => $totalProd,
                    'pages' => $totalPages
            ]);
        }
        
    }
    public function actionBatchpriceupdate()
    {
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] =  "";
        $productData = $session->get('productarr'.$index);
        $updateCount = 0;
        $newCustomPrice = "";
        if (!empty($productData))
        {
            $arrPrice = array();
            $setCustomPrice=Data::sqlRecords('SELECT `value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"  ','one','select');
        
            if (is_array($setCustomPrice) && isset($setCustomPrice['value']) && trim($setCustomPrice['value'])!="" )        
            $newCustomPrice=$setCustomPrice['value'];

            foreach ($productData as $key => $value)
            {   if ($newCustomPrice!="") {
                        $updatePrice=0.00;
                        $customPricearr = explode('-',$newCustomPrice);
                        $updatePriceType = $customPricearr[0];
                        $updatePriceValue = $customPricearr[1];
                        if ($updatePriceValue!="" && $updatePriceType!="") {
                            $updatePrice=Jetproductinfo::priceChange($value['price'],$updatePriceType,$updatePriceValue);
                            if($updatePrice!=0)
                                $value['price'] = $updatePrice;
                        }
                        
                }
                $pricenode['price'] = (float)$value['price'];
                $arrPrice[$value['sku']] = $pricenode;
                $updateCount++;
            }   
            $file_path = Yii::getAlias('@webroot').'/var/jet/price/file-upload/'.$merchant_id.'/jetupload';
            if(!file_exists($file_path)){
                mkdir($file_path,0775, true);
            }
            
            $t=time()+rand(2,5);
            if (!empty($arrPrice))
                $validatorObj = new Productvalidator();
                $uploadArr = $validatorObj->createJsonFile($file_path,$t,"Price", $arrPrice,$this->jetHelper,$merchant_id);

            if($uploadArr && $updateCount>0)
                $return_msg['success']=$updateCount." Products uploaded successfully";
            else
                $return_msg['error']=" 0 product uploaded on jet ";
            return json_encode($return_msg);
                    
        }   
    }
    /*file price upload end  file inventory upload start*/
    public function actionInventoryupdate()
    {

        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;

        $response  = $arrInv= [];
        $allSku = [];
            $sql = "SELECT option_qty as qty,option_sku as sku FROM jet_product_variants WHERE merchant_id=".$merchant_id." AND (status='Under Jet Review' OR status='Available For Purchase') UNION SELECT qty,sku FROM jet_product WHERE merchant_id=".$merchant_id." AND (status='Under Jet Review' OR status='Available For Purchase')";

        $arrInv = Data::sqlRecords($sql,"all","select");
        if (!empty($arrInv)) {
           
            $chunkStatusArray=array_chunk($arrInv, 10000);

            $totalProd = count($arrInv);
            $totalPages = count($chunkStatusArray);
            foreach ($chunkStatusArray as $ind => $value)
            {   
                $session->set('productarr'.$ind, $value);
            }
            return $this->render('batchupdateinv', [
                    'totalcount' => $totalProd,
                    'pages' => $totalPages
            ]);
        }
        
    }
    public function actionBatchinvupdate()
    {
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] =  "";
        $productData = $session->get('productarr'.$index);
        $arrInv = array();
        $jet_config = Jetappdetails::getConfig();
        if (!empty($productData)){
         
        $fullfillmentnodeid = $jet_config[$merchant_id]['fullfilment_node_id'];
        $validatorObj = new Productvalidator();
        $validatorObj->createInvArray($merchant_id,$fullfillmentnodeid,$arrInv,$productData);

        $file_path = Yii::getAlias('@webroot').'/var/jet/price/file-upload/'.$merchant_id.'/jetupload';
         if(!file_exists($file_path)){
            mkdir($file_path,0775, true);
          }
          $t=time()+rand(2,5);
            if (!empty($arrInv))
                $validatorObj = new Productvalidator();
                $uploadArr = $validatorObj->createJsonFile($file_path,$t,"Inventory", $arrInv,$this->jetHelper,$merchant_id);

            if($uploadArr && count($productData)>0)
                $return_msg['success']=count($productData)." Products uploaded successfully";
            else
                $return_msg['error']=" 0 product uploaded on jet ";
            return json_encode($return_msg);
        }
    }
    /*end inv file update*/
    /*csv product upload start*/
    public function actionCsvupload()
    {
        return $this->render('csvupload');
    }
    public function actionExportcsvupload()
    {
        $merchant_id=MERCHANT_ID;
        $post_data = $csvcolumn = $products = $productdata = $row = [];        
        $i = 0;         
        
        $logPath = \Yii::getAlias('@webroot').'/var/jet/product/custom_csv/export/'.$merchant_id;
        if (!file_exists($logPath)){
            mkdir($logPath,0775, true);
        }
        $base_path=$logPath."/".time().'.csv';
        $file = fopen($base_path,"w");
        
        $headers = array('Title','Variant SKU');

        
        
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file,$row);
        $sql = "SELECT jet_product.id,sku,type, COALESCE(jet_product_details.update_title,jet_product.title) as title  FROM `jet_product` LEFT JOIN `jet_product_details` ON `jet_product`.`id` = `jet_product_details`.`product_id` WHERE `jet_product`.`merchant_id`='{$merchant_id}'";

        $products = Data::sqlRecords($sql,'all','select');
        
        $i=0;
        foreach($products as $value)
        {
            if($value['sku']=="")
            {
                continue;
            }
            if($value['type']=="simple")
            {
                $productdata[$i]['Title']=$value['title'];
                $productdata[$i]['Variant SKU']=$value['sku'];
                $i++;
            }
            else
            {
                $optionResult=[];
                $query="SELECT option_sku FROM `jet_product_variants` WHERE product_id='".$value['id']."' order by option_sku='".addslashes($value['sku'])."' desc";
                $optionResult=Data::sqlRecords($query,'all','select');
                
                if(is_array($optionResult) && !empty($optionResult))
                {   
                    foreach($optionResult as $key=>$val)
                    {
                        if($val['option_sku']=="")
                            continue;
                        
                        $productdata[$i]['Title']=$value['title'];
                        $productdata[$i]['Variant SKU']=$val['option_sku'];
                       
                        
                        $i++;
                    }
                }
            }
        }
        
        foreach($productdata as $v)
        {
            $row = [];
            $row[] =$v['Title'];
            $row[] =$v['Variant SKU'];
            
            fputcsv($file,$row);
        }
        fclose($file);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return \Yii::$app->response->sendFile($base_path);                  
    }   
    public function actionStartcsvupload()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $count=[];        
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream','text/comma-separated-values');
        if (!in_array($_FILES['csvfile']['type'], $mimes)) 
        {
            Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");        
            return $this->redirect(['index']);
        }
        if ($_FILES['csvfile']['name']) 
        {
            $newname = $_FILES['csvfile']['name'];
            $log = Yii::getAlias('@webroot').'/var/jet/product/csvupload/'.MERCHANT_ID;
            if (!file_exists($log)) 
            {
                mkdir($log, 0775, true);
            }
            $fileInfo=pathinfo($newname);
            $log_file=$fileInfo['filename'].'.log';
            $file = fopen($log.'/'.$log_file,"w");
            fwrite($file, "product UnArchieve start".PHP_EOL);
            fclose($file);
            $target = $log.'/'.$newname;
            $imageModel=UploadedFile::getInstanceByName('csvfile');
            $imageModel->saveAs($target);
            $fp = file($target);
            $row = 1;
            $m = 0;
            $skus = [];
            if (($handle = fopen($target, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    $row++;
                    for ($c=0; $c < $num; $c++) {
                        if ($data[$c]=="Variant SKU") {
                            $m = $c;
                        }
                    }
                    if ($m!==0 && $data[$m]!=="Variant SKU") {
                    if ($data[$m]!=="")
                       $skus[] = $data[$m];
                    }
                }
                fclose($handle);
            }
            
        }
        $details = $setCustomPrice = [];
        $newCustomPrice = $updatePriceType = "";
        $updatePriceValue=0.00;
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        $allSku = implode(',',$skus);
        $allsku = str_replace("'","",$allSku);
        $allSkus = str_replace(",","','",$allsku);
        
        $sql = "SELECT pro.id,sku,type,product_type,image,qty,weight,attr_ids,jet_attributes,vendor,upc,ASIN,mpn,fulfillment_node,pack_qty,COALESCE(details.update_title,pro.title) as title,COALESCE(details.update_description,pro.description) as description,COALESCE(details.update_price,pro.price) as price,pack_qty FROM `jet_product` as `pro` LEFT JOIN `jet_product_details` as `details` ON details.product_id=pro.id WHERE pro.merchant_id='{$merchant_id}' AND sku IN ('$allSkus') AND pro.fulfillment_node!=0";
        $details = Data::sqlRecords($sql,'all','select');
        // Custom Price Upload on Jet 
        $setCustomPrice=Data::sqlRecords('SELECT `value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"  ','one','select');
        
        if (is_array($setCustomPrice) && isset($setCustomPrice['value']) && trim($setCustomPrice['value'])!="" )        
            $newCustomPrice=$setCustomPrice['value'];
                
        if(trim($newCustomPrice)!="")
        {
            $customPricearr = [];
            $customPricearr = explode('-',$newCustomPrice);
            $updatePriceType = $customPricearr[0];
            $updatePriceValue = $customPricearr[1];
        }
        unset($customPricearr,$newCustomPrice);
        $session->set('priceType', serialize($updatePriceType));
        $session->set('priceValue', serialize($updatePriceValue));
        $session->set('target',$target);
        if(!empty($details))
        {
            $fileNameArr = [];
            $chunkStatusArray=array_chunk($details, 1000);
            $totalProd = count($details);
            $totalPages = count($chunkStatusArray);         
            foreach ($chunkStatusArray as $ind => $value)
            {
                $session->set('productstatus-'.$ind, $value);
            }
            return $this->render('batchupdatestatus', [
                    'totalcount' => $totalProd,
                    'pages' => $totalPages
            ]);
        }
    }
    /*csv product upload end*/
    /*raza end*/
    // Get all products for current client
    public function actionStartupload()
    {
    	$details = $setCustomPrice = [];
    	$newCustomPrice = $updatePriceType = "";
    	$updatePriceValue=0.00;
    	
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        $sql = "SELECT pro.id,sku,type,product_type,image,qty,weight,attr_ids,jet_attributes,vendor,upc,ASIN,mpn,fulfillment_node,pack_qty,COALESCE(details.update_title,pro.title) as title,COALESCE(details.update_description,pro.description) as description,COALESCE(details.update_price,pro.price) as price,pack_qty FROM `jet_product` as `pro` LEFT JOIN `jet_product_details` as `details` ON details.product_id=pro.id WHERE pro.merchant_id='{$merchant_id}' AND pro.fulfillment_node!=0";
        $details = Data::sqlRecords($sql,'all','select');
        
        // Custom Price Upload on Jet        
        $setCustomPrice=Data::sqlRecords('SELECT `value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"  ','one','select');
        
        if (is_array($setCustomPrice) && isset($setCustomPrice['value']) && trim($setCustomPrice['value'])!="" )        
        	$newCustomPrice=$setCustomPrice['value'];
                
        if(trim($newCustomPrice)!="")
        {
        	$customPricearr = [];
        	$customPricearr = explode('-',$newCustomPrice);
        	$updatePriceType = $customPricearr[0];
        	$updatePriceValue = $customPricearr[1];
        }
        unset($customPricearr,$newCustomPrice);
        $session->set('priceType', serialize($updatePriceType));
        $session->set('priceValue', serialize($updatePriceValue));
        
        if(!empty($details))
        {
        	$fileNameArr = [];
        	if($merchant_id==1116 || $merchant_id==1506 )
        	    $chunkStatusArray=array_chunk($details, 3);
        	else    
        	    $chunkStatusArray=array_chunk($details, 50);
        	$totalProd = count($details);
        	$totalPages = count($chunkStatusArray);        	
        	foreach ($chunkStatusArray as $ind => $value)
        	{
        		$session->set('productstatus-'.$ind, $value);
        	}
        	return $this->render('batchupdatestatus', [
        			'totalcount' => $totalProd,
        			'pages' => $totalPages
        	]);
        }
    }
    
 	public function actionBatchstatusupdate()
    {
        $merchant_id=MERCHANT_ID;
        $session = Yii::$app->session;
        $err = []; 
        $productData = [];
        $arrSku = $arrPrice = $arrInv= $arrVar = [];
        $updateCount=0;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = $zipFilePath =  "";
        $productData = $session->get('productstatus-'.$index);
        
        // Chceking for custom price (increased in %age or Fixed)
        $priceType=unserialize($session['priceType']);
        $priceValue=unserialize($session['priceValue']);
       
        $validatorObj = new Productvalidator();
        if(is_array($productData) && count($productData)>0)
        {
            foreach($productData as $value)
            {
            	$validatorObj->collectData($value,$priceType,$priceValue,$arrSku,$arrPrice,$arrInv,$arrVar,$merchant_id,$err);
                $updateCount++;               
            }
        }
        $session->remove('productstatus-'.$index);
        $file_path = Yii::getAlias('@webroot').'/var/jet/file-upload/'.$merchant_id.'/jetupload';
        if(!file_exists($file_path)){
        	mkdir($file_path,0775, true);
        }
        $t=time()+rand(2,5);
        if (!empty($arrSku))        	
        	$validatorObj->createJsonFile($file_path,$t,"MerchantSKUs", $arrSku,$this->jetHelper,$merchant_id);
    	if (!empty($arrPrice))
    		$validatorObj->createJsonFile($file_path,$t,"Price", $arrPrice,$this->jetHelper,$merchant_id);
		if (!empty($arrInv))
			$validatorObj->createJsonFile($file_path,$t,"Inventory", $arrInv,$this->jetHelper,$merchant_id);
		if (!empty($arrVar) && !empty($arrSku))
		 $validatorObj->createJsonFile($file_path,$t,"Variation", $arrVar,$this->jetHelper,$merchant_id);   
        if (!empty($err))
            $return_msg['errors'] = implode(",", $err)." SKUs have some missing info";
        else if($updateCount>0)
            $return_msg['success']=$updateCount." Products uploaded successfully";
        else
            $return_msg['error']=" 0 product uploaded on jet ";
        return json_encode($return_msg);
    }    

    // 	Request to process all the files in BULK
    public function actionProcessUploadedFile()
    {
    	$merchant_id = MERCHANT_ID;
        $count = 0;
    	$sql = "SELECT `file_url`,`file_type`,`file_name` FROM `jet_product_file_upload` WHERE `merchant_id`='{$merchant_id}' AND `status`=''";
    	$allFiles =  Data::sqlRecords($sql,"all",'select');
    	if (!empty($allFiles))
    	{
    		foreach ($allFiles as $key=>$val)
    		{
    			$uploadArr = $ackResponse = [];
    			$uploadArr['url'] = $val['file_url'];
    			$uploadArr['file_type'] = $val['file_type'];
    			$uploadArr['file_name'] = $val['file_name'];
    			
    			$status = $res = "";
    			$res = $this->jetHelper->CPostRequest('/files/uploaded',json_encode($uploadArr),$merchant_id,$status);
    			$ackResponse = json_decode($res,true);
    			if ($status==200 && !empty($ackResponse) && !isset($ackResponse['error']))
    			{
    				$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$ackResponse['received']."',`status`='".$ackResponse['status']."' WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$ackResponse['jet_file_id']."' ";
    				Data::sqlRecords($updateSql,null,'update');
                    $count++;
    			}
    		}
            Yii::$app->session->setFlash('success', $count." File successfully uploaded on jet");
            return $this->redirect(['index']);
    	}
        else{
             Yii::$app->session->setFlash('error', "No file for update on jet !.");
            return $this->redirect(['index']);
        }   	
    }
    
    // Verify uploaded files in bulk
    public function actionVerifyProcessingSuccess()
    {
    	$merchant_id = MERCHANT_ID;
    	$sql = "SELECT `jet_file_id` FROM `jet_product_file_upload` WHERE `merchant_id`=".$merchant_id." AND `status`='Acknowledged'";
    	$allFilesUploaded =  Data::sqlRecords($sql,"all",'select');
        $count=0;
    	if (!empty($allFilesUploaded))
    	{
    		foreach ($allFilesUploaded as $key=>$val)
    		{
    			$status = $res = "";
    			$processedResponse = [];
    			$res =  $this->jetHelper->CGetRequest('/files/'.$val['jet_file_id'],$merchant_id,$status);
    			$processedResponse = json_decode($res,true);
    			
    			if ($status==200 && !empty($processedResponse) )
    			{
    				$sql1= "";
    				if (isset($processedResponse['error_count']))    				
    					$sql1.=",`error_count`='".$processedResponse['error_count']."'";
    				if (isset($processedResponse['error_url']))
    					$sql1.=",`error_url`='".addslashes($processedResponse['error_url'])."'";
    				if (isset($processedResponse['error_excerpt']))
    					$sql1.=",`error_excerpt`='".addslashes(json_encode($processedResponse['error_excerpt']))."'";
    				$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$processedResponse['received']."',`processing_start`='".$processedResponse['processing_start']."',`processing_end`='".$processedResponse['processing_end']."' ,`total_processed`='".$processedResponse['total_processed']."',`status`='".$processedResponse['status']."' ".$sql1."  WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$processedResponse['jet_file_id']."' ";
    				Data::sqlRecords($updateSql,null,'update');
                    $count++;
    			}       			
    		}
    	 Yii::$app->session->setFlash('success', $count." File successfully uploaded on jet");
            return $this->redirect(['index']);
        }
        else{
             Yii::$app->session->setFlash('error', "No file for update on jet !.");
            return $this->redirect(['index']);
        }       
    }
    
    // Request to process perticular file
    public function actionProcessfile()
    {
    	$merchant_id = MERCHANT_ID;
    	$filename = isset($_POST['file_name'])?$_POST['file_name'] : "";
    	$sql = "SELECT `file_url`,`file_type`,`file_name` FROM `jet_product_file_upload` WHERE `merchant_id`='{$merchant_id}' AND `file_name`='{$filename}' ";
    	$val =  Data::sqlRecords($sql,"one",'select');
    	if (!empty($val))
    	{    		
			$uploadArr = $ackResponse = [];
			$uploadArr['url'] = $val['file_url'];
			$uploadArr['file_type'] = $val['file_type'];
			$uploadArr['file_name'] = $val['file_name'];
			 
			$status = $res = "";
			$res = $this->jetHelper->CPostRequest('/files/uploaded',json_encode($uploadArr),$merchant_id,$status);
			$ackResponse = json_decode($res,true);
			
			if ($status==200 && !empty($ackResponse) && !isset($ackResponse['errors']))
			{
				$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$ackResponse['received']."',`status`='".$ackResponse['status']."' WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$ackResponse['jet_file_id']."' ";
				Data::sqlRecords($updateSql,null,'update');
				echo "File acknowledged on jet for precessing";
			}  
			else 
			{
				$msg = "";
				if (preg_match('/File was already acknowledged/',$ackResponse['errors'][0])){
					$msg = ",`status` = 'Acknowledged' " ; 
				}
				$updateSql = "UPDATE `jet_product_file_upload` SET `error`='".$ackResponse['errors'][0]."' ".$msg." WHERE `merchant_id`='".$merchant_id."' AND `file_name`='{$filename}' ";
				Data::sqlRecords($updateSql,null,'update');
				echo "Error : ".$ackResponse['errors'][0];
			}
    	}
    }
    
    //Verify The Uploaded file (already acknowledged) 
    public function actionVerifyfileprocess()
    {
    	$merchant_id = MERCHANT_ID;
    	$jet_file_id = isset($_POST['jet_file_id'])?$_POST['jet_file_id'] : "";    	
    	if (trim($jet_file_id)!="")
    	{    		
			$status = $res = "";
			$processedResponse = [];
			$res =  $this->jetHelper->CGetRequest('/files/'.$jet_file_id,$merchant_id,$status);
			$processedResponse = json_decode($res,true);
			
			if ($status==200 && !empty($processedResponse) )
			{
				$sql1= "";
				if (isset($processedResponse['processing_start']))
					$sql1.=",`processing_start`='".$processedResponse['processing_start']."'";
				if (isset($processedResponse['processing_end']))
					$sql1.=",`processing_end`='".$processedResponse['processing_end']."'";					
				if (isset($processedResponse['error_count']))
					$sql1.=",`error_count`='".$processedResponse['error_count']."'";
				if (isset($processedResponse['error_url']))
					$sql1.=",`error_url`='".addslashes($processedResponse['error_url'])."'";
				if (isset($processedResponse['error_excerpt']))
					$sql1.=",`error_excerpt`='".addslashes(json_encode($processedResponse['error_excerpt']))."'";
				$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$processedResponse['received']."',`total_processed`='".$processedResponse['total_processed']."',`status`='".$processedResponse['status']."' ".$sql1."  WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$processedResponse['jet_file_id']."' ";
				Data::sqlRecords($updateSql,null,'update');
				echo $processedResponse['status'];
			} 
			else     			
				echo "There is some api issue, please try again later";    			
    	}    	
    }
}