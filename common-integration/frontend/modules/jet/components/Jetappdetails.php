<?php 
namespace frontend\modules\jet\components;

use Yii;
use yii\base\Component;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\models\AppStatus;
use frontend\modules\jet\models\JetProduct;

use common\models\User;

class Jetappdetails extends component{
	
	const STATUS_TRIAL_EXPIRED = 'Trial Expired';
    const STATUS_LICENSE_EXPIRED = 'License Expired';
    const STATUS_NOT_PURCHASED = 'Not Purchase';
    const STATUS_PURCHASED = 'Purchased';

	public static function isValidateapp($merchant_id)
	{
		try
		{
			$subscriptionStatus = [];
			$subscriptionStatus = Data::sqlRecords("Select merchant_id,expired_on,purchase_status from jet_shop_details where merchant_id='".$merchant_id."'",'one','select');
			
			if(!empty($subscriptionStatus))
			{
				$expdate=strtotime($subscriptionStatus['expired_on']);
				
				if(time()>$expdate)
				{
					if($subscriptionStatus['purchase_status']=="Trial Expired")
					{
						return "not purchase";
					}
					elseif($subscriptionStatus['purchase_status']=="License Expired")
					{
						return "expire";
					}
					elseif($subscriptionStatus['purchase_status']=="Not Purchase")
					{
						$sql = "UPDATE `jet_shop_details` SET purchase_status='Trial Expired' where merchant_id='".$merchant_id."'";						
						Data::sqlRecords($sql,null,'update');
						return "not purchase";
					}
					elseif($subscriptionStatus['purchase_status']=="Purchased")
					{
						$sql = "UPDATE `jet_shop_details` SET purchase_status='License Expired' where merchant_id='".$merchant_id."'";
						Data::sqlRecords($sql,null,'update');
						return "expire";
					}
				}
			}
		}
		catch(Exception $e)
		{
			return "";
		}
	}
	
	public static function appstatus($shop)
	{
		$isInstall = [];
		$isInstall = Data::sqlRecords("SELECT install_status FROM `jet_shop_details` where shop_url='".$shop."' LIMIT 0,1",'one','select');
		if(isset($isInstall['install_status']) && $isInstall['install_status']==1)
		{
			return true;
		}
		return false;
	}
	
	public static function autologin()
	{
		$url = $shop = "";
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$url=parse_url($_SERVER['HTTP_REFERER']);
			if(isset($url['host']) && strpos($url['host'], "myshopify.com") !== false)
			{
				$shop=$url['host'];
				return $shop;
			}
		}
		return false;
	}
	public static function appstatus1($id)
	{
		$model="";
		$usermodel="";
		$usermodel=User::findOne($id);
		$model=AppStatus::find()->where(["shop"=>$usermodel->username])->one();
		if($model){
			if($model->status==0)
				return false;
		}
		return true;
	}
	public static function customPrice($pricevalue="",$jetHelper,$connection,$merchant_id)
	{

		$model="";
		if(!isset($merchant_id)){
			$merchant_id= Yii::$app->user->identity->id;
		}
		if(!isset($connection)){
			$connection=Yii::$app->getDb();
		}
		
		$updatePriceType="";
		$updatePriceValue=0;
		if($pricevalue)
		{			
			$customPricearr=array();
			$customPricearr = explode('-',$pricevalue);
			$updatePriceType = $customPricearr[0];
			$updatePriceValue = $customPricearr[1];			
		}
		unset($customPricearr);unset($pricevalue);
		
		
		//$model=JetProduct::find()->where(['merchant_id'=>$merchant_id])->all();
		$model=array();
		$model=$connection->createCommand('select id,price,type,sku from `jet_product` where merchant_id="'.$merchant_id.'" and status!="Not Uploaded"')->queryAll();
		$message="";
		if(is_array($model) && count($model)>0)
		{
			foreach($model as $value)
			{
				$price=0;
				if($value['type']=="variants")
				{

					//$price=(float)($value['price']+($pricevalue/100)*($value['price']));
					//$connection->createCommand('update `jet_product` set price="'.$price.'" where id="'.$value['id'].'"')->execute();
					$vmodel=array();
					$vmodel=$connection->createCommand('select option_id,option_price,option_sku from `jet_product_variants` where product_id="'.$value['id'].'"')->queryAll();
					if(is_array($vmodel) && count($vmodel))
					{
						foreach($vmodel as $val)
						{
							// change new price
							$option_price_new=0;
							if($updatePriceType && $updatePriceValue!=0)
							{
								$updatePrice=0;
								$updatePrice=self::priceChange($val['option_price'],$updatePriceType,$updatePriceValue);
								if($updatePrice!=0)
									$option_price_new = $updatePrice;
							}
							
							// $vprice=0;
							// $vprice=(float)($val['option_price']+($pricevalue/100)*($val['option_price']));
							//$connection->createCommand('update `jet_product_variants` set option_price="'.$vprice.'" where option_id="'.$val['option_id'].'"')->execute();
							//$message.= Jetproductinfo::updatePriceOnJet($val['option_sku'],$vprice,$jetHelper,$fullfillmentnodeid,$merchant_id);
							$message.= Jetproductinfo::updatePriceOnJet($val['option_sku'],$option_price_new,$jetHelper,$fullfillmentnodeid,$merchant_id);
						}
					}
				}
				else
				{
					// change new price
					$product_price_new=0;
					if($updatePriceType && $updatePriceValue!=0){
						$updatePrice=0;
						$updatePrice=self::priceChange($value['price'],$updatePriceType,$updatePriceValue);
						if($updatePrice!=0)
							$product_price_new = $updatePrice;
					}
					//$price=(float)($value['price']+($pricevalue/100)*($value['price']));
					//$connection->createCommand('update `jet_product` set price="'.$price.'" where id="'.$value['id'].'"')->execute();
					//$message.= Jetproductinfo::updatePriceOnJet($value['sku'],$price,$jetHelper,$fullfillmentnodeid,$merchant_id);
					$message.= Jetproductinfo::updatePriceOnJet($value['sku'],$product_price_new,$jetHelper,$fullfillmentnodeid,$merchant_id);
				}
			}
		}
		
	}
	
	// Check whether merchant had completed the Jet-Configuration setup
	public static function checkConfiguration($id)
	{
		$jetConfig=[];
		$jetConfig=Data::sqlRecords("SELECT `api_user` from `jet_configuration` where merchant_id='".$id."'",'one','select');
		return empty($jetConfig)?false:true;		
	}
	public static function checkMapping($id)
	{
		$categoryMapped = $prodImported = [];
		$prodImported=Data::sqlRecords("SELECT `id` from `jet_product` where merchant_id='".$id."'",'one','select');
	
		if(is_array($prodImported) && count($prodImported)>0){
			$categoryMapped=Data::sqlRecords("SELECT `id` from `jet_category_map` where merchant_id='".$id."' and `category_id`!=0",'one','select');
		}
		if(!$prodImported){
			unset($prodImported);
			unset($categoryMapped);
			return "product";
		}elseif(!$categoryMapped){
			unset($prodImported);
			unset($categoryMapped);
			return "category";
		}
	}	
	
	public static function convertWeight($weight="",$unit="")
	{
		$newWeight=0;
		if($unit=='kg'){
			$newWeight = (float)($weight*2.2046226218);
			return $newWeight;
		}
		if($unit=='g'){
			$newWeight = (float)($weight*0.0022046226218);
			return $newWeight;
		}
		if($unit=='oz'){
			$newWeight = (float)($weight/16);
			return $newWeight;
		}
		if($unit=='lb'){
			return $weight;
		}
		else{
			return "";
		}
	}
	public static function getConfig()
	{
		$model = $cron_array = [];
		
		$query="SELECT config.merchant_id, fullfilment_node_id, api_host, api_user, api_password, username, auth_key FROM `jet_configuration` config INNER JOIN `user` user_m ON (user_m.id = config.merchant_id) INNER JOIN `jet_shop_details` jet_shop WHERE  jet_shop.install_status=1 AND (jet_shop.purchase_status='Purchased' OR jet_shop.purchase_status='Not Purchase' )  AND (user_m.id = jet_shop.merchant_id)";
		$model = Data::sqlRecords($query,'all','select');
		foreach($model as $jetConfig)
		{
			/* $shop=$isValidate="";
			$returnStatus=false;
			
			$shop = $jetConfig['username'];

			$returnStatus = self::appstatus($shop);
			$isValidate = self::isValidateapp($jetConfig['merchant_id']);
			if($returnStatus==false || $isValidate=="expire" || $isValidate=="not purchase")
			{
				continue;
			} */
			$cron_array[$jetConfig['merchant_id']]['fullfilment_node_id']=$jetConfig['fullfilment_node_id'];
			$cron_array[$jetConfig['merchant_id']]['api_host']=$jetConfig['api_host'];
			$cron_array[$jetConfig['merchant_id']]['api_user']=$jetConfig['api_user'];
			$cron_array[$jetConfig['merchant_id']]['api_password']=$jetConfig['api_password'];
		}
		unset($model);		
		return $cron_array;
	}
	// Get Config Details for single merchant
	public static function getConfigurationDetails($merchant_id)
	{
		$model = $cron_array = [];
		$query = "SELECT config.merchant_id, fullfilment_node_id, api_host, api_user, api_password, username, auth_key FROM `jet_configuration` config INNER JOIN `user` user_m ON (user_m.id = config.merchant_id) INNER JOIN `jet_shop_details` jet_shop WHERE jet_shop.`merchant_id`='".$merchant_id."' AND  jet_shop.install_status=1 AND (jet_shop.purchase_status='Purchased' OR jet_shop.purchase_status='Not Purchase' ) ";
		$cron_array = Data::sqlRecords($query,'one','select');		
		return $cron_array;
	}

	// Get Config Details for single merchant
	public static function getShpoifyClientObj($merchant_id)
	{
		$model = $storeData = [];
		$query = "SELECT `username`,`auth_key` FROM `user` LEFT JOIN `jet_shop_details` jsd ON user.id=`jsd`.`merchant_id` WHERE jsd.`merchant_id`='{$merchant_id}' AND jsd.`install_status`=1";
		$storeData = Data::sqlRecords($query,'one','select');	
		if (!empty($storeData)) {
			$sc = new ShopifyClientHelper($storeData['username'], $storeData['auth_key'], PUBLIC_KEY, PRIVATE_KEY); 
			return $sc; 
		}	
		return false;
	}

	public static function priceChange($price,$priceType,$changePrice)
	{
		$updatePrice=0.00;
		if($priceType=="percentageAmount")
			$updatePrice=(float)($price+($changePrice/100)*($price));
		elseif($priceType=="fixedAmount")
			$updatePrice=(float)($price + $changePrice);
		
		$updatePrice = number_format($updatePrice, 2, '.', '');
		return $updatePrice;
	}
	public static function isWalmartValidateapp($merchant_id)
	{
        try
        {
	        $expdate=$query=$model=$queryObj="";
	        $query = "Select merchant_id,expire_date,status FROM `walmart_extension_detail` WHERE merchant_id='".$merchant_id."'";
	    	$model = Data::sqlRecords($query, 'one','select');
			if($model)
	        {
	        	$expdate=strtotime($model['expire_date']);
	        	if(time()>$expdate)
	        	{
	        		if($model['status']==self::STATUS_PURCHASED)
	        		{
			        	$sql="UPDATE `walmart_extension_detail` SET status='".self::STATUS_LICENSE_EXPIRED."' where merchant_id='".$merchant_id."'";
			        	$result = Data::sqlRecords($sql, null, 'update');
	        		}
	        		elseif($model['status']==self::STATUS_NOT_PURCHASED)
	        		{
	        			$sql="UPDATE `walmart_extension_detail` SET status='".self::STATUS_TRIAL_EXPIRED."' where merchant_id='".$merchant_id."'";
	        			$result = Data::sqlRecords($sql, null, 'update');
	        			return "not purchase";
	        		}
	        		return "expire";
	        	}
	        }
        }
        catch(Exception $e)
   		{
        	return "";
        }   	 
	}
	public static function getStoreInformation($merchant_id)
	{
		$storeInfo=Data::sqlRecords("SELECT id,shop_data FROM `jet_shop_details` WHERE merchant_id='".$merchant_id."'","one","select");
		if(isset($storeInfo['id']) && $storeInfo['shop_data']){
			return json_decode($storeInfo['shop_data'],true);
		}
		return false;
	}
	public static function walmartAppstatus($shop,$connection=null)
	{
		$query="";
		$model=[];
		$query = "SELECT `status` FROM `walmart_shop_details` WHERE `shop_url`='".$shop."'";
		$model = Data::sqlRecords($query, 'one','select');
		
		if(empty($model) || ($model['status']==0) ){
				return false;
		}
		return true;
	}
	public static function checkRegistration($merchant_id)
	{
		$registerData=Data::sqlRecords('SELECT id FROM `jet_registration` WHERE merchant_id='.$merchant_id,'one','select');
		if(is_array($registerData) && count($registerData)>0)
		{
			return true;
		}
		return false;
	}

	public static function getJetConfigDetails($merchant_id)
	{
		$sql = "SELECT `data`,`value` FROM `jet_config` WHERE `merchant_id`='{$merchant_id}' ";
		return Data::sqlRecords($sql,"all","select");
	}
	public static function saveJetShopDetails($merchant_id,$shop_url,$shop_name,$email,$country_code,$currency,$shopData="")
	{
		$getJetShopData=Data::sqlRecords("SELECT id FROM `jet_shop_details` WHERE merchant_id='".$merchant_id."'","one","select");
		$expired_on = date('Y-m-d H:i:s',strtotime('+30 days', strtotime(date('Y-m-d H:i:s'))));
		if(isset($getJetShopData['id']))
		{
			//update if client info install app again
			Data::sqlRecords("UPDATE `jet_shop_details` SET `install_status`=1 WHERE merchant_id='".$merchant_id."'",'null','update');
		}
		else
		{
			$query="INSERT INTO `jet_shop_details`(`merchant_id`,`shop_url`,`shop_name`,`email`,`country_code`,`currency`,`install_status`,`installed_on`,`expired_on`,`purchase_status`,`shop_data`)VALUES('".$merchant_id."','".$shop_url."','".$shop_name."','".$email."','".$country_code."','".$currency."',1,'".date('Y-m-d H:i:s')."','".$expired_on."','".Data::NOT_PURCHASE."','".json_encode($shopData)."')";
			Data::sqlRecords($query,'null','insert');
		}
	}  

	public static function calculateDynamicPrice($merchant_id,$current_price,$min_price,$max_price,$bid_amount,$my_best_offer=[],$best_marketplace_offer=[])  
	{	
		$newPrice = 0.00;	
		if ( !empty($my_best_offer) && !empty($best_marketplace_offer) )  
	    {
	        $marketplacePrice = $best_marketplace_offer['shipping_price']+$best_marketplace_offer['item_price'];
	        $ourPrice = $my_best_offer['shipping_price']+$my_best_offer['item_price'];  

	        if ($ourPrice > $marketplacePrice) 
	        {
	        	if ( ($min_price+$bid_amount) < $marketplacePrice )	        	
	        		$newPrice = $min_price+$bid_amount;
	        	else
	        		$newPrice = $min_price;		        	
	        }
	        elseif ($ourPrice < $marketplacePrice) 
	        {
	        	if ( ($max_price) < $marketplacePrice )	        	
	        		$newPrice = $max_price;
	        	elseif (($current_price+$bid_amount) < $marketplacePrice) 
	        		$newPrice = $current_price+$bid_amount;	
	        }	                        
	    }
	    return (float)$newPrice;
	}        
}
?>