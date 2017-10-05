<?php
namespace frontend\modules\walmart\controllers;

use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Walmartappdetails;

class WebhookDataController extends Controller
{
	public function beforeAction($action)
    {
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

	public function actionSendwebhook()
	{
		$product_creation_webhook_content = '{"id":788032119674292922,"title":"Example T-Shirt","body_html":null,"vendor":"Acme","product_type":"Shirts","created_at":null,"handle":"example-t-shirt","updated_at":null,"published_at":"2017-05-19T09:03:06-04:00","template_suffix":null,"published_scope":"global","tags":"mens t-shirt example","variants":[{"id":642667041472713922,"product_id":788032119674292922,"title":"","price":"19.99","sku":"example-shirt-s","position":0,"grams":200,"inventory_policy":"deny","compare_at_price":"24.99","fulfillment_service":"manual","inventory_management":null,"option1":"Small","option2":null,"option3":null,"created_at":null,"updated_at":null,"taxable":true,"barcode":null,"image_id":null,"inventory_quantity":75,"weight":0.2,"weight_unit":"kg","old_inventory_quantity":75,"requires_shipping":true},{"id":757650484644203962,"product_id":788032119674292922,"title":"","price":"19.99","sku":"example-shirt-m","position":0,"grams":200,"inventory_policy":"deny","compare_at_price":"24.99","fulfillment_service":"manual","inventory_management":"shopify","option1":"Medium","option2":null,"option3":null,"created_at":null,"updated_at":null,"taxable":true,"barcode":null,"image_id":null,"inventory_quantity":50,"weight":0.2,"weight_unit":"kg","old_inventory_quantity":50,"requires_shipping":true}],"options":[{"id":527050010214937811,"product_id":null,"name":"Title","position":1,"values":["Small","Medium"]}],"images":[{"id":539438707724640965,"product_id":788032119674292922,"position":0,"created_at":null,"updated_at":null,"src":"\/\/cdn.shopify.com\/s\/assets\/shopify_shirt-39bb555874ecaeed0a1170417d58bbcf792f7ceb56acfe758384f788710ba635.png","variant_ids":[]}],"image":null}';

		$post = $product_creation_webhook_content;
		//$post = json_decode($product_creation_webhook_content, true);

		try
		{
			$url = 'http://182.72.248.90/integration/walmart/webhook-data/receivewebhook';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);


			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_TIMEOUT,1);
			$result = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			echo "Http code : ".$http_code."<br>";

			curl_close($ch);

			echo "end.";
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}

	public function actionReceivewebhook()
	{
		$webhook_content = '';

		$webhook = fopen('php://input' , 'rb');
		while(!feof($webhook)){
			$webhook_content .= fread($webhook, 4096);
		}
		fclose($webhook);

		Data::createLog($webhook_content, 'product-create-webhook.log');
	}

	public function actionTest()
	{
		//$merchants = Walmartappdetails::getMerchants();
		$merchants = Walmartappdetails::getConfig();
		var_dump($merchants);die;
	}
}