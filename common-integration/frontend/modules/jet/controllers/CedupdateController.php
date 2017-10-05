<?php
namespace frontend\modules\jet\controllers;
use Yii;
use yii\web\Controller;

class CedupdateController extends Controller
{	
	public function beforeAction($action)
	{
		if ($this->action->id == 'productupdate1') {
			Yii::$app->controller->enableCsrfValidation = false;
		}
		return true;
	}
	public function actionProductupdate1()
	{
		$webhook="";
		$connection = Yii::$app->getDb();
		$webhook_content = '';
		$webhook = fopen('php://input' , 'rb');
		while(!feof($webhook)){ //loop through the input stream while the end of file is not reached
			$webhook_content .= fread($webhook, 4096); //append the content on the current iteration
		}
		fclose($webhook); //close the resource
		//$webhook_content='{"id":4211751046,"title":"Gold Ring 18k","body_html":"\u003cp\u003e\u003cspan\u003e\u003cspan\u003eLove and luxury unite in a scintillating combination of pink and white. Her heart is pure like the GIA certified, soft pink natural fancy radiant cut center diamond, all outlined with 88 near colorless and pink VS1-VS2 round pave rows. Two unique symmetrical cut diamonds bezel set within the sides add a hint of luxury for a total of 1.28 carats of accents. Cleverly crafted with escalating side view and unique scrollwork in platinum or 18K white gold and 18K pink gold. Show your love on Valentine\'s Day or any day with this fabulous pink ring of wonder. If you wish to purchase the center 2.02 ct. radiant cut natural fancy pink GIA certified diamond center please contact us by calling our toll free number:\u003c\/span\u003e \u003c\/span\u003e(888) 530-5212.\u003c\/p\u003e\n\u003cp\u003eThis ring includes a GIA Report for the center stone.\u003c\/p\u003e\n\u003cp\u003eThis setting is also available in 18K White Gold also with a polished finish.\u003cbr\u003e \u003cbr\u003e \u003ciframe src=\"\/\/www.youtube.com\/embed\/BXr8Cv5QaMs\" allowfullscreen=\"\" frameborder=\"0\" height=\"251\" width=\"335\"\u003e\u003c\/iframe\u003e \u003cbr\u003e \u003cbr\u003e We manufacture all of our pieces in-house in Los Angeles which allows us to have full control over the entire creation process.\u003c\/p\u003e\n\u003cp style=\"text-align: left;\"\u003e   \u003ca href=\"\/pages\/clevereve-layaway-plan\" target=\"_blank\"\u003e\u003cimg alt=\"\" src=\"\/\/cdn.shopify.com\/s\/files\/1\/0232\/4827\/files\/ce_layaway.png?1356\"\u003e\u003c\/a\u003e           \u003ca href=\"http:\/\/cdn.shopify.com\/s\/files\/1\/0232\/4827\/files\/clevereve-printable-ring-sizer.pdf?1804\" target=\"_blank\"\u003e\u003cimg alt=\"\" src=\"\/\/cdn.shopify.com\/s\/files\/1\/0232\/4827\/files\/ce_ringsize.png?1356\"\u003e\u003c\/a\u003e\u003c\/p\u003e\n\u003cp style=\"text-align: left;\"\u003eOnce you have selected your ideal setting, we are more than happy to find the perfect center stone.  Please give us a call at (888) 530-5212 or email at \u003ca href=\"%22mailto:info@clevereve.com%22\" target=\"_blank\"\u003einfo@clevereve.com\u003c\/a\u003e and we will locate the perfect stone for you right here in the jewelry district in downtown Los Angeles. \u003c\/p\u003e\n\u003cul id=\"diamond-info\"\u003e\n\u003cli\u003e\n\u003cb\u003eTotal Carat Weight:\u003c\/b\u003e\u003cspan\u003e1.28\u003c\/span\u003e\n\u003c\/li\u003e\n\u003cli\u003e\n\u003cb\u003eAverage Color:\u003c\/b\u003e\u003cspan\u003eG-H, Pink\u003c\/span\u003e\n\u003c\/li\u003e\n\u003cli\u003e\n\u003cb\u003eAverage Clarity:\u003c\/b\u003e\u003cspan\u003eVS1-VS2\u003c\/span\u003e\n\u003c\/li\u003e\n\u003cli\u003e\n\u003cb\u003eNumber of Stones:\u003c\/b\u003e\u003cspan\u003e88\u003c\/span\u003e\n\u003c\/li\u003e\n\u003cli\u003e\n\u003cb\u003eType of Side-Stones:\u003c\/b\u003e\u003cspan\u003eRound \u0026amp; Sym.\u003c\/span\u003e\n\u003c\/li\u003e\n\u003cli\u003e\n\u003cb\u003eSetting Style(s):\u003c\/b\u003e\u003cspan\u003ePave \u0026amp; Bezel\u003c\/span\u003e\n\u003c\/li\u003e\n\u003c\/ul\u003e\n\u003cul id=\"shipping-info\"\u003e\n\u003cli\u003e\n\u003cb\u003eOrder By \u003c\/b\u003e5:00PM Pacific Standard Time\u003c\/li\u003e\n\u003cli\u003e\n\u003cb\u003eAnd Your Order Ships Overnight Within \u003c\/b\u003e30 days\u003c\/li\u003e\n\u003c\/ul\u003e\n\u003cdiv style=\"display: none;\" id=\"variant-weight-data\"\u003eGold: 11 grams, Plat: 22 grm.\u003c\/div\u003e","vendor":"Fashion Apparel","product_type":"Engagement Ring","created_at":"2015-12-29T07:25:49-05:00","handle":"2-02-ct-fancy-pink-radiant-diamond-ring-with-pink-pave-in-platinum-18k-pink-gold-1-28-ctw","updated_at":"2016-04-18T07:27:11-04:00","published_at":"2015-12-29T07:25:00-05:00","template_suffix":null,"published_scope":"global","tags":"","variants":[{"id":13528400454,"product_id":4211751046,"title":"18K White Gold \u0026 18K PG","price":"7622.00","sku":"20-LR8724-32784W-1","position":1,"grams":0,"inventory_policy":"deny","compare_at_price":"95280.00","fulfillment_service":"manual","inventory_management":"shopify","option1":"18K White Gold \u0026 18K PG","option2":null,"option3":null,"created_at":"2015-12-29T07:25:49-05:00","updated_at":"2016-02-25T06:42:24-05:00","taxable":true,"barcode":"719236005016","image_id":null,"inventory_quantity":255,"weight":0.0,"weight_unit":"lb","old_inventory_quantity":255,"requires_shipping":true},{"id":13528400518,"product_id":4211751046,"title":"Platinum \u0026 18K Pink Gold","price":"1188.00","sku":"20-LR8724-32784P-1","position":2,"grams":0,"inventory_policy":"deny","compare_at_price":"148580.00","fulfillment_service":"manual","inventory_management":"shopify","option1":"Platinum \u0026 18K Pink Gold","option2":null,"option3":null,"created_at":"2015-12-29T07:25:49-05:00","updated_at":"2016-02-25T06:42:24-05:00","taxable":true,"barcode":"","image_id":null,"inventory_quantity":5445,"weight":0.0,"weight_unit":"lb","old_inventory_quantity":5445,"requires_shipping":true}],"options":[{"id":5147175622,"product_id":4211751046,"name":"Metal","position":1,"values":["18K White Gold \u0026 18K PG","Platinum \u0026 18K Pink Gold"]}],"images":[{"id":8947212678,"product_id":4211751046,"position":1,"created_at":"2016-01-22T05:41:42-05:00","updated_at":"2016-01-22T05:41:42-05:00","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/index.jpeg?v=1453459302","variant_ids":[]}],"image":{"id":8947212678,"product_id":4211751046,"position":1,"created_at":"2016-01-22T05:41:42-05:00","updated_at":"2016-01-22T05:41:42-05:00","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/index.jpeg?v=1453459302","variant_ids":[]}}';
		//echo $webhook_content;die;
		$data=array();
		$data = json_decode($webhook_content,true);
		$file_path="";
		$myfile="";
		$path=\Yii::getAlias('@webroot').'/var/jet/productUpdate/ced';
		if (!file_exists($path)){
			mkdir($path,0775, true);
		}
		if($data && isset($data['id']))
		{
			try
			{
				$file_path = $path.'/updateJson.log';
				$myfile = fopen($file_path, "w") ;
				fwrite($myfile, $webhook_content);
				fclose($myfile);
				$errorstr="";
				$merchant_id="";
				$prodExist=array();
				$prodExist=$connection->createCommand("SELECT `merchant_id` FROM `jet_product` WHERE id='".$data['id']."'")->queryOne();
				if (is_array($prodExist) && count($prodExist)>0){
					$merchant_id=$prodExist['merchant_id'];
					$checkExistPro=array();
					$checkExistPro=$connection->createCommand('SELECT `product_id` from `jet_product_tmp` where `product_id`="'.$data['id'].'"')->queryOne();
					if(is_array($checkExistPro) && count($checkExistPro)>0){
						$connection->createCommand('UPDATE `jet_product_tmp` SET `data`="'.addslashes($webhook_content).'" where merchant_id="'.$merchant_id.'" AND  `product_id`="'.$data['id'].'"')->execute();
					}
					else
					{
						$connection->createCommand('INSERT into  `jet_product_tmp` (`merchant_id`,`product_id`,`data`)VALUES
							("'.$merchant_id.'","'.$data['id'].'","'.addslashes($webhook_content).'")')->execute();
					}
				}
			}
			catch(Exception $e){
				echo $e->getMesage();	
			}
		}
	}	
}