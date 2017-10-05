<?php
namespace frontend\modules\walmart\components;

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

class Skuproductupload extends Walmartapi
{
    const GET_FEEDS_ITEMS_SUB_URL = 'v3/feeds?feedType=item';
    /**
     * Create Product on Walmart
     * @param string|[] $ids
     * @return bool
     */
    public function createProduct($ids, $merchant_id, $returnPreparedData = false)
    {
        $timeStamp = (string)time();

        $productToUpload = [
            'MPItemFeed' => [
                '_attribute' => [
                    'xmlns' => 'http://walmart.com/'
                ],
                '_value' => [
                    0 => [
                        'MPItemFeedHeader' => [
                            'version' => '3.1',
                            'requestId' => $timeStamp,
                            'requestBatchId' => $timeStamp,
                        ]
                    ]
                ]
            ]
        ];

        if (count($ids) > 0) {
            $key = 1;
            $uploadErrors = [];
            $uploadProductIds = [];
            foreach ($ids as $id => $val) {
                $query = 'SELECT `product_id`, `variant_id`, `title`, `sku`, `type`, `wal`.`product_type`, `wal`.`status`, `wal`.`product_qty`,`wal`.`product_price`, `description`, `image`, `qty`, `price`, `weight`, `vendor`, `upc`, `walmart_attributes`, `category`, `parent_category`, `tax_code`, `long_description`, `short_description`, `self_description`, `common_attributes`, `attr_ids`, `sku_override`, `product_id_override`, `wal`.`walmart_optional_attributes`, `wal`.`shipping_exceptions` FROM (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . $merchant_id . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . $merchant_id . '") as `jet` ON jet.id=wal.product_id WHERE wal.product_id="' . $id . '" LIMIT 1';

                $productArray = Data::sqlRecords($query, "one", "select");

                if ($productArray) {
                    $validateResponse = $this->validateProduct($productArray, $merchant_id);

                    if (is_array($validateResponse) && isset($validateResponse['error'])) {
                        $uploadErrors[$productArray['sku']] = $validateResponse['error'];
                        continue;
                    } elseif ($validateResponse === true) {
                        $image = trim($productArray['image']);
                        $imageArr = explode(',', $image);

                        $variantGroupId = (string)$id . (string)time();

                        $description = empty($productArray['long_description']) ? $productArray['description'] : $productArray['long_description'];

                        $originalmessage = '';

                        //remove <![CDATA[ ]]> from description
                        $description = str_replace('<![CDATA[', '', $description);
                        $description = str_replace(']]>', '', $description);

                        //trim product description more than 4000 characters
                        if (strlen($description) > 3500) {
                            $description = Data::trimString($description, 3500);
                        }

                        $short_description = Data::trimString($description, 800);

                        $tax_code = trim(Data::GetTaxCode($productArray, $merchant_id));

                        $brand = Data::getBrand($productArray['vendor']);

                        // walmart variant product title
                        $title = Data::getWalmartTitle($productArray['product_id'], $merchant_id);

                        if (isset($title['product_title']) && !empty($title)) {
                            $productArray['title'] = $title['product_title'];
                        }

                        if ($productArray['type'] == "simple") {
                            if(empty($productArray['product_price']) || is_null($productArray['product_price']))
                            {
                                $productArray['price'] = trim($productArray['price']);
                            }else{
                                $productArray['price'] = trim($productArray['product_price']);
                            }
                            $productArray['price'] = WalmartRepricing::getProductPrice($productArray['price'], $productArray['type'], $productArray['product_id'], $merchant_id);

                            $type = Jetproductinfo::checkUpcType($productArray['upc']);

                            $product = [
                                'sku' => $productArray['sku'],
                                'name' => Data::getName($productArray['title']),
                                'product_id' => $productArray['product_id'],
                                'variant_id' => $productArray['variant_id'],
                                'description' => $description,
                                'identifier_type' => $type,
                                'upc' => $productArray['upc'],
                                'price' => (string)$productArray['price'],
                                'weight' => (string)$productArray['weight'],
                                'category' => $productArray['category'],
                                'sku_override' => $productArray['sku_override'],
                                'id_override' => $productArray['product_id_override'],
                                'shipping_exceptions' => $productArray['shipping_exceptions'],
                                'tax_code' => $tax_code,
                                'brand' => $brand,
                                'images' => $imageArr,
                                'variantGroupId' => $variantGroupId,
                                'product_status' => $productArray['status']
                            ];

                            $MPItem =$this->prepareMPItem($merchant_id, $product, $productArray);

                            if (isset($MPItem['status']) && !$MPItem['status']) {
                                $error = isset($MPItem['error']) ? $MPItem['error'] : 'Product can not be uploaded.';
                                $uploadErrors[$productArray['sku']] = $error;
                                continue;
                            }

                            $validate = WalmartProductValidate::validatev3ProductXml($MPItem);

                            if (!$validate['status']) {
                                $message = "Rejected by Walmart because there was a glitch on walmart's end. Please contact us.";
                                $uploadErrors[$productArray['sku']] = ["error" => $message, "xml_validation_error" => $validate['error']];
                                continue;
                            }

                            $productToUpload['MPItemFeed']['_value'][$key]['MPItem'] = $MPItem;
                            $key++;

                            if (!in_array($id, $uploadProductIds)) {
                                $uploadProductIds[] = $id;
                            }
                        } else {
                            $duplicateSkus = [];
                            $query = 'SELECT jet.option_id,option_title,option_sku,wal.walmart_option_attributes,wal.option_prices,option_image,option_qty,option_price ,option_weight,option_unique_id,`wal`.`walmart_optional_attributes`, `jet`.`variant_option1`, `jet`.`variant_option2`, `jet`.`variant_option3`, `wal`.`status` FROM (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`="' . $merchant_id . '") as wal INNER JOIN (SELECT * FROM `jet_product_variants` WHERE `merchant_id`="' . $merchant_id . '") as jet ON jet.option_id=wal.option_id WHERE wal.product_id="' . $id . '"';
                            $productVarArray = Data::sqlRecords($query, "all", "select");

                            foreach ($productVarArray as $value) {
                                if(!isset($ids[$id][$value['option_id']])){
                                    continue;
                                }
                                if(empty($value['option_prices']) || is_null($value['option_prices']))
                                {
                                    $value['option_price'] = trim($value['option_price']);
                                }else{
                                    $value['option_price'] = trim($value['option_prices']);
                                }
                                $value['option_price'] = WalmartRepricing::getProductPrice($value['option_price'], $productArray['type'], $value['option_id'], $merchant_id);

                                $variantImages = [];
                                if($value['option_image']) {
                                     $variantImages[] = trim($value['option_image']);
                                }
                                $productImages = [];
                                if(trim($productArray['image']))
                                {
                                    $image = trim($productArray['image']);
                                    $productImages = explode(',', $image);
                                }
                                $imageArr = array_unique(array_merge($variantImages, $productImages));

                                if (in_array($value['option_sku'], $duplicateSkus)) {
                                    $uploadErrors[$productArray['sku']][$value['option_sku']] = "Variant Sku : '" . $value['option_sku'] . "' is duplicate.";
                                    continue;
                                } else
                                    $duplicateSkus[] = $value['option_sku'];

                                if (strlen($value['option_sku']) > WalmartProductValidate::MAX_LENGTH_SKU) {
                                    $uploadErrors[$productArray['sku']][$value['option_sku']] = "Variant Sku : " . $value['option_sku'] . " must be fewer than 50 characters.";
                                    continue;
                                }

                                $type = Jetproductinfo::checkUpcType($value['option_unique_id']);

                                // walmart variant product title
                                $title = Data::getWalmartTitle($productArray['product_id'], $merchant_id);

                                if (isset($title['product_title']) && !empty($title)) {
                                    $productArray['title'] = $title['product_title'];
                                }

                                $product = [
                                    'sku' => $value['option_sku'],
                                    //'name' => Data::getName($productArray['title'] . '~' . $value['option_title']),
                                    'name' => Data::getName($productArray['title']),
                                    'product_id' => $productArray['product_id'],
                                    'variant_id' => $value['option_id'],
                                    'description' => $description,
                                    'identifier_type' => $type,
                                    'upc' => (string)$value['option_unique_id'],
                                    'price' => (string)$value['option_price'],
                                    'weight' => (string)$value['option_weight'],
                                    'category' => $productArray['category'],
                                    'sku_override' => $productArray['sku_override'],
                                    'id_override' => $productArray['product_id_override'],
                                    'shipping_exceptions' => $productArray['shipping_exceptions'],
                                    'tax_code' => $tax_code,
                                    'brand' => $brand,
                                    'images' => $imageArr,
                                    'variantGroupId' => $variantGroupId,
                                    'product_status' => $value['status']
                                ];

                                //variant name should be same as parent for this client
                                if ($merchant_id == '678') {
                                    $product['name'] = Data::getName($productArray['title']);
                                }

                                $MPItem = self::prepareMPItem($merchant_id, $product, $productArray, $value);

                                if (isset($MPItem['status']) && !$MPItem['status']) {
                                    $error = isset($MPItem['error']) ? $MPItem['error'] : 'Product can not be uploaded.';
                                    $uploadErrors[$productArray['sku']][$value['option_sku']] = $error;
                                    continue;
                                }

                                $validate = WalmartProductValidate::validatev3ProductXml($MPItem);
                                if (!$validate['status']) {
                                    $message = "Rejected by Walmart because there was a glitch on walmart's end. Please contact us.";
                                    $uploadErrors[$productArray['sku']][$value['option_sku']] = ["error" => $message, "xml_validation_error" => $validate['error']];
                                    continue;
                                }

                                $productToUpload['MPItemFeed']['_value'][$key]['MPItem'] = $MPItem;
                                $key++;

                                if (!in_array($id, $uploadProductIds)) {
                                    $uploadProductIds[] = $id;
                                }
                                //set status to 'Item Processing'
                                Jetproductinfo::chnageUploadingProductStatus($value['option_id']);
                            }
                        }
                    }
                }
            }
            //Since the value of $key is starting with 1.
            if ($key > 1) {
                //print_r($productToUpload);die("before product upload");
                if (!file_exists(\Yii::getAlias('@webroot') . '/var/skuproduct/xml/' . $merchant_id)) {
                    mkdir(\Yii::getAlias('@webroot') . '/var/skuproduct/xml/' . $merchant_id, 0775, true);
                }
                $file = Yii::getAlias('@webroot') . '/var/skuproduct/xml/' . $merchant_id . '/MPProduct-' . time() . '.xml';
                $xml = new Generator();
                $xml->arrayToXml($productToUpload)->save($file);
                self::unEscapeData($file);

                $response = $this->postRequest(self::GET_FEEDS_ITEMS_SUB_URL, ['file' => $file]);
                /*$response = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><ns2:FeedAcknowledgement xmlns:ns2="http://walmart
.com/"><ns2:feedId>4733B09D61BC41519D46C452BEA65F9C@AQMBAQA</ns2:feedId></ns2:FeedAcknowledgement>';*/
                $response = str_replace('ns2:', "", $response);

                $responseArray = $this->xmlToArray($response);

                if (isset($responseArray['FeedAcknowledgement'])) {
                    $feedId = isset($responseArray['FeedAcknowledgement']['feedId']) ? $responseArray['FeedAcknowledgement']['feedId'] : '';
                    if ($feedId != '') {
                        return ['uploadIds' => $uploadProductIds, 'feedId' => $feedId, 'feed_file' => $file, 'errors' => $uploadErrors];
                    }
                } elseif ($responseArray['errors']) {
                    $uploadErrors['feedError'] = $responseArray['errors'];
                }
            }
            if (count($uploadErrors) > 0) {
                return ['errors' => $uploadErrors];
            }
        }
        return ['errors' => 'No product selected for upload.'];
    }


}
