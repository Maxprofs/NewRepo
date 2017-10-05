<?php
namespace frontend\modules\jet\components;

use common\models\Post;
use yii\base\Component;
use yii;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\ShopifyClientHelper;

class Createwebhook extends component
{
	public static function createNewWebhook($sc,$shop,$token)
    {
        $urls = [
                    "productupdate",
                    "productdelete",
                    "isinstall",
                    "createshipment",
                    "cancelled",
                    "productcreate",
                    "createshipment",
                    "createorder",
                ];

        $topics = [
                    "products/update",
                    "products/delete",
                    "app/uninstalled",
                    "orders/fulfilled",
                    "orders/cancelled",
                    "products/create",
                    "orders/partially_fulfilled",
                    "orders/create",
                   // "products/create"
                  ];

        $otherWebhooks = self::getOtherAppsWebhooks($shop);

        $response = $sc->call('GET','/admin/webhooks.json');
       	
        if(count($response)>0 && !isset($response['errors']))
        {
            foreach ($urls as $key => $val_url)
            {

                $url=Yii::$app->getUrlManager()->createAbsoluteUrl('shopifywebhook/'.$val_url, 'https');

                $continueFlag = false;

                foreach ($response as $k=>$value)
                {
                    if(isset($value['address']) && ($value['address']==$url || in_array($value['address'], $otherWebhooks))) {
                        $continueFlag = true;
                        unset($response[$k]);
                        break;
                    }
                }
                if(!$continueFlag)
                {
                    $charge = ['webhook' => ['topic'=>$topics[$key], 'address'=>$url]];
                    $sc->call('POST','/admin/webhooks.json', $charge);
                }
            }
        }
        else
        {
            foreach ($urls as $key => $val_url)
            {
                $url=Yii::$app->getUrlManager()->createAbsoluteUrl('shopifywebhook/'.$val_url, 'https');
            	if(!in_array($url, $otherWebhooks))
				{
	                $charge = ['webhook' => ['topic'=>$topics[$key], 'address'=>$url]];
	                $sc->call('POST','/admin/webhooks.json', $charge);
            	}
            }
        }
    }

	public static function getOtherAppsWebhooks($shop)
    {
    	$query = "SELECT `token` FROM `walmart_shop_details` WHERE `shop_url` LIKE '".$shop."' LIMIT 0 , 1";
    	$results = Data::sqlRecords($query, 'one');

    	$webhooks = [];

    	if(is_array($results) && isset($results['token']))
    	{
    		$token = $results['token'];
	        $walmart_app_key = WALMART_APP_KEY;
	        $walmart_app_secret = WALMART_APP_SECRET;

	        try 
            {
		        $sc = new ShopifyClientHelper($shop, $token, $walmart_app_key, $walmart_app_secret);

		        $response = $sc->call('GET','/admin/webhooks.json');

		        if(count($response)>0 && !isset($response['errors']))
		        {
		            foreach ($response as $k=>$value)
		            {
		                if(isset($value['address'])) {
		                    $webhooks[] = $value['address'];
		                }
		            }
		        }
		    }
			catch(Exception $e) {
				$e->getMessage();
			}
		}
        return $webhooks;
    }
}	
?>