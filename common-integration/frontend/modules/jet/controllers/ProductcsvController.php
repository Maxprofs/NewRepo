<?php 
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\models\JetProductVariants;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

class ProductcsvController extends JetmainController
{
	protected $jetHelper,$sc;
	public function actionIndex()
	{
		if (Yii::$app->user->isGuest) {
			return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
		}	
		return $this->render('index');
	}
	public function actionExport()
	{
		$merchant_id=MERCHANT_ID;
		$post_data = $csvcolumn = $products = $productdata = $row = [];
		$columns = $optionColumn = $updateTitle = $updateDesc = $updateUPC = $updateMPN = $updateASIN = $updatePrice = "";
		$post_data = $_POST['col'];
		
		for ($i=0; $i < count($post_data) ; $i++) 
		{ 
			if ($post_data[$i]=='title') {
				//$columns .=$post_data[$i];
				$updateTitle = "Update Title";
			}
			/*else{
				$columns .=','.$post_data[$i];
			}*/

			if ($post_data[$i]=='price')
			{				
				$optionColumn .=',COALESCE(update_option_price,option_price) as option_price';
				$columns .=',COALESCE(update_price,price) as price';
				$updatePrice = "Update Price";
			}
			if ($post_data[$i]=='upc'){
				$optionColumn .=',option_unique_id';
				$columns .=','.$post_data[$i];
				$updateUPC = "Update Barcode";				
			}
			if ($post_data[$i]=='asin'){
				$optionColumn .=',asin';
				$columns .=','.$post_data[$i];
				$updateASIN = "Update ASIN";
			}
			if ($post_data[$i]=='mpn'){
				$optionColumn .=',option_mpn';
				$columns .=','.$post_data[$i];
				$updateMPN = "Update MPN";
			}
			if ($post_data[$i]=='description'){
				$columns .=',COALESCE(update_description,description) as description';
				$updateDesc = "Update Description";
			}
		}
		
		$i = 0;			
		
		$logPath = \Yii::getAlias('@webroot').'/var/jet/product/custom_csv/export/'.$merchant_id;
		if (!file_exists($logPath)){
			mkdir($logPath,0775, true);
		}
		$base_path=$logPath."/".time().'.csv';
		$file = fopen($base_path,"w");
		
		$headers = array('Title','Sku','Type');

		if ($updateTitle!="") 
			$headers[]= $updateTitle;
		
		if ($updatePrice!="") 
			$headers[]= $updatePrice;
		
		if ($updateUPC!="") 
			$headers[]= $updateUPC;
		
		if ($updateASIN!="") 
			$headers[]= $updateASIN;
		
		if ($updateMPN!="") 
			$headers[]= $updateMPN;
		
		if ($updateDesc!="") 
			$headers[]= $updateDesc;
		
		foreach($headers as $header) {
			$row[] = $header;
		}
		fputcsv($file,$row);
		$sql = "SELECT jet_product.id,sku,type, COALESCE(jet_product_details.update_title,jet_product.title) as title  ".$columns." FROM `jet_product` LEFT JOIN `jet_product_details` ON `jet_product`.`id` = `jet_product_details`.`product_id` WHERE `jet_product`.`merchant_id`='{$merchant_id}'";

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
				$productdata[$i]['Sku']=$value['sku'];
				$productdata[$i]['Type']="No Variants";
				if ($updateTitle!="") 
					$productdata[$i][$updateTitle]="";
				
				if ($updatePrice!="") 
					$productdata[$i][$updatePrice]=$value['price'];
				
				if ($updateUPC!="") 
					$productdata[$i][$updateUPC]=$value['upc'];

				if ($updateASIN!="") 
					$productdata[$i][$updateASIN]=$value['asin'];
				
				if ($updateMPN!="") 
					$productdata[$i][$updateMPN]=$value['mpn'];
				
				if ($updateDesc!="") 
					$productdata[$i][$updateDesc]=$value['description'];
								
				$i++;
			}
			else
			{
				$optionResult=[];
				$query="SELECT option_sku".$optionColumn." FROM `jet_product_variants` WHERE product_id='".$value['id']."' order by option_sku='".addslashes($value['sku'])."' desc";
				$optionResult=Data::sqlRecords($query,'all','select');
				
    			if(is_array($optionResult) && !empty($optionResult))
    			{	
    				foreach($optionResult as $key=>$val)
    				{
    					if($val['option_sku']=="")
    						continue;
    					
    					$productdata[$i]['Title']=$value['title'];
    					$productdata[$i]['Sku']=$val['option_sku'];
    					if($value['sku']==$val['option_sku'])    					
    						$productdata[$i]['Type']="Parent";    					
    					else    						
    						$productdata[$i]['Type']="Variants";
						
						if ($updatePrice!="") 
							$productdata[$i][$updatePrice]=$val['option_price'];
						
						if ($updateUPC!="") 
							$productdata[$i][$updateUPC]=$val['option_unique_id'];

						if ($updateASIN!="") 
							$productdata[$i][$updateASIN]=$val['asin'];
						
						if ($updateMPN!="") 
							$productdata[$i][$updateMPN]=$val['option_mpn'];
												   					
						if ($updateDesc!="")
							$productdata[$i][$updateDesc]=$value['description'];
    					$i++;
    				}
    			}
			}
		}
		
		foreach($productdata as $v)
		{
			$row = [];
			$row[] =$v['Title'];
			$row[] =$v['Sku'];
			$row[] =$v['Type'];
			
			if ($updateTitle!="")
				$row[] ="";
			if ($updatePrice!="") 
				$row[] =$v[$updatePrice];
			if ($updateUPC!="") 
				$row[] =$v[$updateUPC];
			if ($updateASIN!="") 
				$row[] =$v[$updateASIN];
			if ($updateMPN!="") 
				$row[] =$v[$updateMPN];
			if ($updateDesc!="") 
				$row[] =$v[$updateDesc];
			fputcsv($file,$row);
		}
		fclose($file);
		$encode = "\xEF\xBB\xBF"; // UTF-8 BOM
		$content = $encode . file_get_contents($base_path);
		return \Yii::$app->response->sendFile($base_path);				 	
	}	
	
	public function actionAjaxCsvImport()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = MERCHANT_ID;
        $count = [];
        
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream','text/comma-separated-values');
        if (!in_array($_FILES['csvfile']['type'], $mimes)) 
        {
            Yii::$app->session->setFlash('error', "Only CSV file allowed");        
            return $this->redirect(['index']);
        }
        if ($_FILES['csvfile']['name']) 
        {
            $newname = $_FILES['csvfile']['name'];
            $fileInfo=pathinfo($newname);
            $log = \Yii::getAlias('@webroot').'/var/jet/product/custom_csv/import/'.$merchant_id;
            if (!file_exists($log)) 
            {
                mkdir($log, 0775, true);
            }
                        
            $log_file=$fileInfo['filename'].'.log';
            $file = fopen($log.'/'.$log_file,"w");
            fwrite($file, "product update start".PHP_EOL);
            $target = $log.'/'.$newname;
            $imageModel=UploadedFile::getInstanceByName('csvfile');
            $imageModel->saveAs($target);
            $fp = file($target);
            $countLines = count($fp);   
            if(count($countLines)>0)
            {
                $pages=ceil($countLines/200);
                return $this->render('ajaxbatchcsv', [
                    'totalcount' => $countLines,
                    'pages'=>$pages,
                    'target' => $target,
                    'log_file'=>$log.'/'.$log_file,
                ]);
            }
        }    

    }
    public function actionMassUpdateCsv()
    {
        $postData= Yii::$app->request->post();   

        $session = Yii::$app->session;		  
		$flag=false;
        if($postData && isset($postData['index']))
        {
            $response=[];
            $count=0;
            $merchant_id=MERCHANT_ID;
            $fullfillmentnodeid = FULLFILMENT_NODE_ID;
            if (($handle = fopen($postData['target'], "r"))) 
            {
                $file=fopen($postData['file_path'],'a+');
                if($postData['index']>1)
                    fseek($handle, $postData['current_pos']);
                $row = 0;
                $isVariantflag=false;
                $titleIndex = $priceIndex = $barcodeIndex = $asinIndex = $mpnIndex = $descIndex = "";
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
                {
                	$updateFlag=false;
					if ($postData['index']==1 && $row == 0)
					{
						if((trim($data[0])!='Title' || trim($data[1])!='Sku' || trim($data[2])!='Type' ))
						{
							$flag=true;
							break;
						}
						$titleIndex = array_search("Update Title",$data);
	                	$priceIndex = array_search("Update Price",$data);
	                	$barcodeIndex = array_search("Update Barcode",$data);
	                	$asinIndex = array_search("Update ASIN",$data);
	                	$mpnIndex = array_search("Update MPN",$data);
	                	$descIndex = array_search("Update Description",$data);
						$session->set('titleIndex',$titleIndex);
						$session->set('priceIndex',$priceIndex);
						$session->set('barcodeIndex',$barcodeIndex);
						$session->set('asinIndex',$asinIndex);
						$session->set('mpnIndex',$mpnIndex);
						$session->set('descIndex',$descIndex);
						
                    	if ($titleIndex)                    	
                    		fwrite($file, "title column: ".$titleIndex.PHP_EOL);
                    	if ($descIndex)                    	
                    		fwrite($file, "description column: ".$descIndex.PHP_EOL);
                    	if($asinIndex)
                    		fwrite($file, "asin column: ".$asinIndex.PHP_EOL);
                    	if($mpnIndex)
                    		fwrite($file, "mpn column: ".$mpnIndex.PHP_EOL);
                    	if ($priceIndex) {
                    		fwrite($file, "price column: ".$priceIndex.PHP_EOL);
                    		Data::jetsaveConfigValue($merchant_id,'fixed_price',"yes");
                    	}
                    	if ($barcodeIndex) 
                    		fwrite($file, "barcode column: ".$barcodeIndex.PHP_EOL);

                    	$row ++;
                        continue;
                    }
                    else
                    {

                    	$titleIndex = $session['titleIndex'];
                    	$priceIndex = $session['priceIndex']; 
                    	$barcodeIndex = $session['barcodeIndex'];
                    	$asinIndex = $session['asinIndex'];
                    	$mpnIndex = $session['mpnIndex'];
                    	$descIndex = $session['descIndex'];	
	
	                	//unset($session['titleIndex'],$session['priceIndex'],$session['barcodeIndex'],$session['asinIndex'],$session['mpnIndex'],$session['descIndex']);				
                    }
/*                    echo $session['mpnIndex']."---";
                    echo $data[$session['mpnIndex']];
                    die("Cvb");*/
					$num = count($data);
                    fwrite($file, PHP_EOL."sku: ".$data[1].PHP_EOL);	
					if( trim($data[1])!='sku' &&  (trim($data[2])=="No Variants" || trim($data[2])=="Parent") )
					{
						//$count++;	
						$presult = $queryParam = $queryParamDetails = "";
						$presult=Data::sqlRecords("SELECT product_id,sku,update_price,update_title,update_description,upc,ASIN,mpn FROM `jet_product` as pro LEFT JOIN `jet_product_details` as details ON pro.id=details.product_id WHERE pro.merchant_id='".$merchant_id."' AND pro.sku='".addslashes($data[1])."' LIMIT 0,1",'one','select');
						
						if(!empty($presult)&& isset($presult['sku']))
						{ 													
							if( trim($priceIndex)!="" && trim($data[$priceIndex])!="" && $presult['update_price']!=trim($data[$priceIndex]) && $data[$priceIndex]>0)
							{
								fwrite($file, "update price on jet for sku: ".$presult['sku']." price: ".$data[$priceIndex].PHP_EOL);
								$message = Jetproductinfo::updatePriceOnJet($presult['sku'],(float)$data[$priceIndex],$this->jetHelper,$fullfillmentnodeid,$merchant_id);
								fwrite($file, "update price on jet response: ".$message.PHP_EOL);
								$queryParamDetails.="update_price='".(float)$data[$priceIndex]."',";
							}
							if (trim($data[$barcodeIndex])!="")
							{
								$barUpc = trim($data[$barcodeIndex]);
								if (strlen($barUpc)==11)
									$barUpc = '0'.$barUpc;
								if(Jetproductinfo::validateBarcode($barUpc) && $presult['upc']!=trim($barUpc))
								{
									$queryParam.="upc='".trim($barUpc)."',";
									fwrite($file, "upc: ".$barUpc.PHP_EOL);
									//$presult->product['upc']=trim($data[$barcodeIndex]);
								}							
							}
							
							if(Jetproductinfo::validateAsin(trim($data[$asinIndex])) && $presult['ASIN']!=trim($data[$asinIndex]))
							{
								$queryParam.="ASIN='".trim($data[$asinIndex])."',";
								fwrite($file, "ASIN: ".$data[$asinIndex].PHP_EOL);
								//$presult->product['ASIN']=trim($data[$asinIndex]);
							}
							if( trim($mpnIndex)!="" && trim($data[$mpnIndex])!="" && $presult['mpn']!=trim($data[$mpnIndex]))
							{
								$queryParam.="mpn='".trim($data[$mpnIndex])."',";
								fwrite($file, "mpn: ".trim($data[$mpnIndex]).PHP_EOL);
								//$presult->product['mpn']=trim($data[$mpnIndex]);
							}
							if( trim($titleIndex)!="" && trim($data[$titleIndex])!="" && $presult['update_title']!=trim($data[$titleIndex]))
							{
								$queryParamDetails.="update_title='".addslashes(trim($data[$titleIndex]))."',";
								fwrite($file, "update_title: ".addslashes(trim($data[$titleIndex])).PHP_EOL);
								//$presult->update_title=trim($data[$titleIndex]);
							}
							if( trim($descIndex)!="" && trim($data[$descIndex])!="" && $presult['update_description']!=trim($data[$descIndex]))
							{
								$queryParamDetails.="update_description='".addslashes(trim($data[$descIndex]))."',";
								fwrite($file, "update_description: ".addslashes(trim($data[$descIndex])).PHP_EOL);
								//$presult->update_description=trim($data[$descIndex]);
							}
							if($queryParam){
								$updateFlag=true;
								$queryParam=rtrim($queryParam,',');
								$sql="UPDATE jet_product SET ".$queryParam." WHERE id='".$presult['product_id']."' ";
								fwrite($file, "update product query: ".$sql.PHP_EOL);
								Data::sqlRecords($sql);
							}
							if($queryParamDetails){
								$updateFlag=true;
								$queryParamDetails=rtrim($queryParamDetails,',');
								$sql="UPDATE jet_product_details SET ".$queryParamDetails." WHERE product_id='".$presult['product_id']."'";
								fwrite($file, "update product details query: ".$sql.PHP_EOL);
								Data::sqlRecords($sql);
							}
							//$presult->save(false);
						}	
						if(trim($data[2])=="Parent")
						{
							$isVariantflag = true;
						}
					}
					else
					{
						$isVariantflag = true;
					}
					if($isVariantflag)  
					{
						// $count++;
						$vresult=JetProductVariants::find()->select('option_id,option_sku,update_option_price,asin,option_mpn,option_unique_id')->where(['merchant_id'=>$merchant_id,'option_sku'=>$data[1]])->one();
						if($vresult)
						{
							//$count++;
							if( trim($priceIndex)!="" && trim($data[$priceIndex])!="" && $vresult->update_option_price!=trim($data[$priceIndex]) && $data[$priceIndex]>0)
							{
								$updateFlag=true;
								fwrite($file, "update price on jet for sku: ".$vresult->option_sku." price: ".$data[$priceIndex].PHP_EOL);
								$message = Jetproductinfo::updatePriceOnJet($vresult->option_sku,(float)$data[$priceIndex],$this->jetHelper,$fullfillmentnodeid,$merchant_id);
								fwrite($file, "update price on jet response: ".$message.PHP_EOL);
								$vresult->update_option_price=trim($data[$priceIndex]);
							}
							if (trim($data[$barcodeIndex])!="")
							{
								$barUpcVar = trim($data[$barcodeIndex]);
								if (strlen($barUpcVar)==11)
									$barUpcVar = '0'.$barUpcVar;
									if(Jetproductinfo::validateBarcode($barUpcVar) && $vresult->option_unique_id!=trim($barUpcVar))
									{
										$updateFlag=true;
										$vresult->option_unique_id=trim($barUpcVar);
										fwrite($file, "variant upc: ".$barUpcVar.PHP_EOL);																				
									}
							}
							if(Jetproductinfo::validateBarcode(trim($data[$barcodeIndex])) && $vresult->option_unique_id!=trim($data[$barcodeIndex]))
							{
								$updateFlag=true;
								$vresult->option_unique_id=trim($data[$barcodeIndex]);
								fwrite($file, "variant upc: ".$data[$barcodeIndex].PHP_EOL);
							}
							if(Jetproductinfo::validateAsin(trim($data[$asinIndex])) && $vresult->asin!=trim($data[$asinIndex]))
							{
								$updateFlag=true;
								$vresult->asin=trim($data[$asinIndex]);
								fwrite($file, "variant asin: ".$data[$asinIndex].PHP_EOL);
							}
							if( trim($mpnIndex)!="" && trim($data[$mpnIndex])!="" && $vresult->option_mpn!=trim($data[$mpnIndex]))
							{
								$updateFlag=true;
								$vresult->option_mpn=trim($data[$mpnIndex]);
								fwrite($file, "variant mpn: ".$data[$mpnIndex].PHP_EOL);
							}

							$vresult->save(false);
						}
					}
					if($row==200){
                        $cur_pos = ftell($handle);
                        $last_sku=$data[1];
                        break;
                    }
					$row++;  
					if($updateFlag)
						$count++;  
                }
                if(!$flag && isset($cur_pos))
                {
                    $response['success']['cur_pos']=$cur_pos;
                    $response['success']['last_sku']=$last_sku;
                }
                elseif(!$flag)
                {
                	unset($session['titleIndex'],$session['priceIndex'],$session['barcodeIndex'],$session['asinIndex'],$session['mpnIndex'],$session['descIndex']);
                    $response['success']['end']="end";
                }
                else
                    $response['error']="Please upload correct csv file...";
                $response['success']['update_count']=$count;
            }
            return json_encode($response);
        }
    }		
}
?>