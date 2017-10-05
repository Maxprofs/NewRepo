<?php
namespace frontend\modules\jet\controllers;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\models\JetCronSchedule;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Productvalidator;
use Yii;
use yii\web\Controller;
use yii\base\Exception;

class JetinvsyncController extends Controller
{
	public function actionUploadinventory()
	{				
		$cronData="";		
		$cronData=JetCronSchedule::find()->where(['cron_name'=>'qty_sync'])->one();
		if($cronData && trim($cronData['cron_data'])!=""){
			$cron_array=json_decode($cronData['cron_data'],true);
		}
		else
		{
			$cron_array = Jetappdetails::getConfig();
		}
		
		$start = $count = $countArr = 0;
		$status_array['total_count']=count($cron_array);
		
		if(!empty($cron_array) && count($cron_array)>0)
		{
			
			foreach($cron_array as $key=>$jetConfig)
			{
				try 
				{
					$merchant_id=$key;
					/* if ($merchant_id!=335)						
						continue; */
					
					//$starttime = time();
					$response = $jetHelper = $arrInv = $invUpdateRes = [];
					$fullfillmentnodeid = $jetConfig['fullfilment_node_id'];
					$api_host = $jetConfig['api_host'];
					$api_user = $jetConfig['api_user'];
					$api_password = $jetConfig['api_password'];
					$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
					$validatorObj = new Productvalidator();
					$validatorObj->invSync($merchant_id,$fullfillmentnodeid,$arrInv);					
					$countArr++;
					
					unset($cron_array[$key]);
					
					$file_path = Yii::getAlias('@webroot').'/var/jet/inv/file-upload/'.$merchant_id.'/jetupload';
			        if(!file_exists($file_path)){
			        	mkdir($file_path,0775, true);
			        }
			        
			        $t=time()+rand(2,5);
			        if (!empty($arrInv))
					  $uploadArr = $validatorObj->createJsonFile($file_path,$t,"Inventory", $arrInv,$jetHelper,$merchant_id);
					
					if($countArr>=50){
						break;
					}					
				}catch (Exception $e)
				{
					unset($cron_array[$key]);					
					continue;
				}
			}
		}
		if(count($cron_array)==0)
			$cronData->cron_data="";
		else
			$cronData->cron_data=json_encode($cron_array);
		$cronData->save(false);
	}
	public function actionProcessuploadedfiles()
	{		
		$cronData="";
		$cronData=JetCronSchedule::find()->where(['cron_name'=>'process_json'])->one();
		if($cronData && trim($cronData['cron_data'])!=""){
			$cron_array=json_decode($cronData['cron_data'],true);
		}
		else
		{
			$cron_array = Jetappdetails::getConfig();
		}
		
		$start = $count = $countArr = 0;
		$status_array['total_count']=count($cron_array);
		
		if(!empty($cron_array) && count($cron_array)>0)
		{
			foreach($cron_array as $key=>$jetConfig)
			{
				try
				{
					$merchant_id=$key;
					/* if ($merchant_id!=1116)
						continue; */
					//$starttime = time();
					$response = $jetHelper = $arrInv = $invUpdateRes = [];
					$fullfillmentnodeid = $jetConfig['fullfilment_node_id'];
					$api_host = $jetConfig['api_host'];
					$api_user = $jetConfig['api_user'];
					$api_password = $jetConfig['api_password'];
					$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
					$validatorObj = new Productvalidator();
					$validatorObj->processAllInventoryJson($merchant_id,$jetHelper);
					$validatorObj->verifyAllInventoryJson($merchant_id,$jetHelper);
					
					$countArr++;
					unset($cron_array[$key]);					
						
					if($countArr>=100)
						break;					
				}catch (Exception $e)
				{
					unset($cron_array[$key]);					
					continue;
				}
			}
		}
		if(count($cron_array)==0)
			$cronData->cron_data="";
		else
			$cronData->cron_data=json_encode($cron_array);
		$cronData->save(false);		
	}
	/*file upload for price*/
	/*public function actionPriceupdate()
	{

		$merchant_id=14;
		$jetConfig = Data::getjetConfiguration($merchant_id);		
		$response = $jetHelper = $arrPrice = [];
		$fullfillmentnodeid = $jetConfig['fullfilment_node_id'];
		$api_user = $jetConfig['api_user'];
		$api_password = $jetConfig['api_password'];
		$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
		$validatorObj = new Productvalidator();
		$arrPrice = $validatorObj->priceSync($merchant_id,$arrPrice);


		$chunkStatusArray=array_chunk($allSku, 10000);

		$totalProd = count($allSku);
		$totalPages = count($chunkStatusArray);
		
	}*/

	public function actionArchiveunarchive()
	{			
		try
		{

			$merchant_id=14;	
			$jetConfig = Data::getjetConfiguration($merchant_id);		
			$response = $jetHelper = $arrUnarchive = $invUpdateRes = [];
			$fullfillmentnodeid = $jetConfig['fullfilment_node_id'];
			$api_user = $jetConfig['api_user'];
			$api_password = $jetConfig['api_password'];
			$jetHelper = new Jetapimerchant("https://merchant-api.jet.com/api",$api_user,$api_password);
			
			$validatorObj = new Productvalidator();
			$validatorObj->unarchiveSku($merchant_id,$jetHelper);				
		}catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
}