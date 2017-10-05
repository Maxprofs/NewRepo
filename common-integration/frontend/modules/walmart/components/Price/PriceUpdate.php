<?php
namespace frontend\modules\walmart\components\Price;

use Yii;
use yii\base\Component;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\components\Signature;
use frontend\modules\walmart\components\Generator;
use frontend\modules\walmart\components\Xml\Parser;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartAttributeMap;
use yii\base\Response;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\components\WalmartCategory;
use frontend\modules\walmart\components\WalmartProductValidate;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\WalmartPromoStatus;
use frontend\modules\walmart\components\WalmartProduct as WalmartProductComponent;

class PriceUpdate extends Component
{
    const GET_FEEDS_PRICE_SUB_URL = 'v3/feeds?feedType=price';
    const UPDATE_PRICE_SUB_URL = 'v2/prices';
    //const UPDATE_BULK_PROMOTIONAL_PRICE_SUB_URL = 'v2/feeds?feedType=promo';
    const UPDATE_BULK_PROMOTIONAL_PRICE_SUB_URL = 'v3/price?feedType=promo';
    const UPDATE_PROMOTIONAL_PRICE_SUB_URL = 'v3/price?promo=true';

    public $apiUrl;
    public $apiConsumerId;
    public $apiConsumerChannelId;
    public $apiPrivateKey;
    public $apiSignature;
    public $requestedXml = '';

    public function __construct($apiConsumerId = "", $apiPrivateKey = "", $apiConsumerChannelId = "")
    {
        $this->apiUrl = "https://marketplace.walmartapis.com/";
        $this->apiConsumerId = $apiConsumerId;
        $this->apiPrivateKey = $apiPrivateKey;
        $this->apiSignature = new Signature();
    }
}
