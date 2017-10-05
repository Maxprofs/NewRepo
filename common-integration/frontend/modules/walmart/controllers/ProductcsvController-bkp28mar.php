<?php 
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use frontend\modules\walmart\models\JetProduct;
use frontend\modules\walmart\models\JetProductVariants;
use frontend\modules\walmart\models\WalmartProduct as WalmartProductModel;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\WalmartProduct;

class ProductcsvController extends WalmartmainController
{
	protected $walmartHelper;

	public function actionIndex()
	{
		if (Yii::$app->user->isGuest) {
			return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
		}
	
		return $this->render('index');
	}
	public function actionExport()
	{
		$merchant_id=Yii::$app->user->identity->id;
		
		if (!file_exists(\Yii::getAlias('@webroot').'/var/csv_export/'.$merchant_id)) {
			mkdir(\Yii::getAlias('@webroot').'/var/csv_export/'.$merchant_id,0775, true);
		}
		$base_path=\Yii::getAlias('@webroot').'/var/csv_export/'.$merchant_id.'/product.csv';
		$file = fopen($base_path,"w");
		
		$headers = array('Id','Sku','Type','Price');
		
		$row = array();
		foreach($headers as $header) {
			$row[] = $header;
		}
		fputcsv($file,$row);
		
		$productdata=array();
		$i=0;

		$model = JetProduct::find()->select('id,sku,price,type')->where(['merchant_id'=>$merchant_id])->all();
		foreach($model as $value)
		{
			if($value->sku=="")
			{
				continue;
			}
			if($value->type=="simple")
			{
				$productdata[$i]['id']=$value->id;
				$productdata[$i]['sku']=$value->sku;
				$productdata[$i]['type']="No Variants";
				$productdata[$i]['price']=$value->price;
				$i++;
			}
			else
			{
				$optionResult=[];
				$query = "SELECT option_id,option_title,option_sku,option_price,asin,option_mpn FROM `jet_product_variants` WHERE product_id='".$value['id']."' order by option_sku='".addslashes($value['sku'])."' desc";
				$optionResult = Data::sqlRecords($query);
				
    			if(is_array($optionResult) && count($optionResult)>0)
    			{	
    				foreach($optionResult as $key=>$val)
    				{
    					if($val['option_sku']=="")
    						continue;
    					if($value['sku']==$val['option_sku'])
    					{
    						$productdata[$i]['type']="Parent";
    					}
    					else
    					{	
    						$productdata[$i]['type']="Variants";
    					}
    					$productdata[$i]['id']=$value['id'];
    					$productdata[$i]['sku']=$val['option_sku'];
    					$productdata[$i]['price']=$val['option_price'];
    					
    					
    					$i++;
    				}
    			}
			}
		}
		
		foreach($productdata as $v)
		{
			$row = array();
			$row[] =$v['id'];
			$row[] =$v['sku'];
			$row[] =$v['type'];
			$row[] =$v['price'];
			
			fputcsv($file,$row);
		}
		fclose($file);
		$encode = "\xEF\xBB\xBF"; // UTF-8 BOM
		$content = $encode . file_get_contents($base_path);
		return \Yii::$app->response->sendFile($base_path);
	}

	public function actionReadcsv() 
	{
		if (Yii::$app->user->isGuest) {
			return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
		}
		$merchant_id = Yii::$app->user->identity->id;
		
		if(isset($_FILES['csvfile']['name']))
		{
			//var_dump($_FILES);die;
			$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','text/comma-separated-values');
			if (!in_array($_FILES['csvfile']['type'], $mimes)) 
			{
				Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");		
				return $this->redirect(['index']);
			}

			$newname = $_FILES['csvfile']['name'];

			if (!file_exists(Yii::getAlias('@webroot').'/var/csv_import/'.date('d-m-Y').'/'.$merchant_id ) ){
		        mkdir(Yii::getAlias('@webroot').'/var/csv_import/'.date('d-m-Y').'/'.$merchant_id,0775, true);
		    }
		    
			$target =Yii::getAlias('@webroot').'/var/csv_import/'.date('d-m-Y').'/'.$merchant_id.'/'.$newname.'-'.time();
			$row=0;
			$flag=false;
			$row1=0;
			if(!file_exists($target))
			{
				move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
			}

			$selectedProducts = array();
			$import_errors = array();
			if (($handle = fopen($target, "r")))
			{
				$status = WalmartProductModel::PRODUCT_STATUS_UPLOADED;
				$allpublishedSku = WalmartProduct::getAllProductSku($merchant_id,$status);
				$row=0;
				while (($data = fgetcsv($handle, 90000, ",")) !== FALSE)
				{		
					if($row==0 && ( trim($data[0])!='Id' || trim($data[1])!='Sku' || trim($data[2])!='Type' || trim($data[3])!='Price' ))
					{
						$flag=true;
						break;
					}
					$num = count($data);
					$row++;
					if($row==1)
						continue;

					$pro_id = trim($data[0]);
					$pro_sku = trim($data[1]);
					$pro_type = trim($data[2]);
					$pro_price = trim($data[3]);

					if($pro_id=='' || $pro_sku=='' || $pro_type=='' || $pro_price=='') {
						$import_errors[$row] = 'Row '.$row.' : Invalid data.';
						continue;
					}

					if(!is_numeric($pro_id) || !is_numeric($pro_price)) {
						$import_errors[$row] = 'Row '.$row.' : Invalid product_id / price.';
						continue;
					}

					if(!in_array($pro_sku, $allpublishedSku)) {
						$import_errors[$row] = 'Row '.$row.' : '.'Sku => "'.$pro_sku.'" is invalid/not published on walmart.';
						continue;
					}

					$productData = array();
					$productData['id'] = $pro_id;
					$productData['sku'] = $pro_sku;
					$productData['type'] = $pro_type;
					$productData['price'] = $pro_price;
					$productData['currency'] = CURRENCY;

					$selectedProducts[] = $productData;
				}

				if(count($selectedProducts))
				{
					$walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key` FROM `walmart_configuration` WHERE merchant_id='".$merchant_id."'", 'one');
	                if($walmartConfig)
	                {
	                	$this->walmartHelper = new WalmartProduct($walmartConfig['consumer_id'],$walmartConfig['secret_key']);

						$priceUploadCountPerRequest  = 1000;
						$selectedProducts = array_chunk($selectedProducts, $priceUploadCountPerRequest);
						foreach ($selectedProducts as $_selectedProducts)
						{
							$response = $this->walmartHelper->updatePriceviaCsv($_selectedProducts);

							if(isset($response['errors'])) 
							{
								if(isset($response['errors']['error'])) {
			            			Yii::$app->session->setFlash('warning', $response['errors']['error']['code']);
			            		} else {
			            			Yii::$app->session->setFlash('warning', "Price of Products is not updated due to some error.");
			            		}
							} 
							elseif(isset($response['error'])) 
							{
								if(isset($response['error'][0]['code'])) {
			            			Yii::$app->session->setFlash('warning', $response['error'][0]['code']);
			            		} else {
			            			Yii::$app->session->setFlash('warning', "Price of Products is not updated due to unknown error.");
			            		}
							} 
							elseif(isset($response['feedId']))
							{
								Yii::$app->session->setFlash('success', "Product Information has been updated successfully");
							}
							else
							{
								Yii::$app->session->setFlash('warning', "Price of Products is not updated.");
							}
						}
					}
					else
					{
						Yii::$app->session->setFlash('warning', "Please enter walmartapi...");
					}

					if(count($import_errors)) {
						Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
					}
				}
				else
				{
					if(count($import_errors)) {
						Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
					} else {
						Yii::$app->session->setFlash('error', "Please Upload Csv file....");
					}
				}
			}	
			else
			{	
				Yii::$app->session->setFlash('error', "File not found....");
			}
		}
		else
		{
			Yii::$app->session->setFlash('error', "Please Upload Csv file....");
		}
		return $this->redirect(['index']);
	}
}

