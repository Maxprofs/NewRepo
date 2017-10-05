<?php
namespace frontend\modules\walmart\components;

use Yii;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\models\WalmartAttributeMap;
use frontend\modules\walmart\models\WalmartProduct as WalmartProductModel;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartRepricing;

class WalmartProduct extends Walmartapi
{
    const PUT_PRICE_SUB_URL = 'v3/price';
    const ALL_PRODUCT_UPLOAD_FILEPATH = '/frontend/modules/walmart/filestorage/product/create/';
    const FEED_TYPE_ITEM = 'item';
    const PRICE_LIMIT = '500';
    const QTY_LIMIT = '500';

    const REQUIRED_ATTRIBUTE = 'it_is_required';
    const NON_REQUIRED_ATTRIBUTE = 'it_is_not_required';

    public static function getAllProductSku($merchant_id, $filterByStatus = null)
    {
        if (is_null($filterByStatus)) {
            $query = "SELECT `result`.* FROM ((SELECT `jp`.`sku` FROM `jet_product` `jp` INNER JOIN (SELECT `product_id`,`merchant_id`,`status` FROM `walmart_product` WHERE `merchant_id`='{$merchant_id}') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `jp`.`merchant_id`='{$merchant_id}') UNION (SELECT `option_sku` AS `sku` FROM `jet_product_variants` `jpv` INNER JOIN (SELECT `option_id`,`merchant_id`,`status` FROM `walmart_product_variants` WHERE `merchant_id`='{$merchant_id}') as `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` WHERE `jpv`.`merchant_id`='{$merchant_id}')) as `result`";

            /*$query = "SELECT `merged_data`.* FROM ((SELECT `variant_id` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . $merchant_id . " AND `jet_product`.`type`='simple' AND `walmart_product`.`category` != '') UNION (SELECT `walmart_product_variants`.`option_id` AS `variant_id` FROM `walmart_product_variants` INNER JOIN `walmart_product` ON `walmart_product_variants`.`product_id` = `walmart_product`.`product_id` INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`=" . $merchant_id . " AND `walmart_product`.`category` != '')) as `merged_data`";*/
        } else {
            $status = $filterByStatus;
            $query = "SELECT `result`.* FROM ((SELECT `jp`.`sku` FROM `jet_product` `jp` INNER JOIN (SELECT `product_id`,`merchant_id`,`status` FROM `walmart_product` WHERE `merchant_id`='{$merchant_id}') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `jp`.`merchant_id`='{$merchant_id}' AND `wp`.`status`='{$status}') UNION (SELECT `option_sku` AS `sku` FROM `jet_product_variants` `jpv` INNER JOIN (SELECT `option_id`,`merchant_id`,`status` FROM `walmart_product_variants` WHERE `merchant_id`='{$merchant_id}') as `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` WHERE `jpv`.`merchant_id`='{$merchant_id}' AND `wpv`.`status`='{$status}')) as `result`";
        }
        $result = Data::sqlRecords($query, 'column');

        return $result;
    }

    /**
     * Bulk Update Price On Walmart via Csv
     * @param [] $products
     * @return bool
     */
    public function updatePriceviaCsv($products = [])
    {

        $merchant_id = Yii::$app->user->identity->id;

        $repricing_array = [];
        $update_array = [];

        $count = 0;
        $timeStamp = (string)time();
        $priceArray = [
            'PriceFeed' => [
                '_attribute' => [
                    'xmlns:gmp' => "http://walmart.com/",
                ],
                '_value' => [
                    0 => [
                        'PriceHeader' => [
                            'version' => '1.5',
                        ],
                    ],
                ]
            ]
        ];

        $isPriceFeed = 1;

        if (is_array($products) && count($products) > 0) {
            foreach ($products as $product) {
                $check = [];
                $sku = Data::getProductSku($product['option_id']);
                $check['sku'] = $sku;
                $isRepricingEnabled = WalmartRepricing::isRepricingEnabled($check);
                if ($isRepricingEnabled) {
                    $repricing_array[$sku] = "Not Price updated due to repricing enable";
                    continue;
                }
                $update_array[] = $product;

                $price = $product['price'];
                $priceArray['PriceFeed']['_value'][$isPriceFeed] = [
                    'Price' => [
                        'itemIdentifier' => [
                            'sku' => $product['sku']
                        ],
                        'pricingList' => [
                            'pricing' => [
                                'currentPrice' => [
                                    'value' => [
                                        '_attribute' => [
                                            'currency' => $product['currency'],
                                            'amount' => $price
                                        ],
                                        '_value' => [

                                        ]
                                    ]
                                ],
                                'currentPriceType' => 'BASE',
                                'comparisonPrice' => [
                                    'value' => [
                                        '_attribute' => [
                                            'currency' => $product['currency'],
                                            'amount' => $price
                                        ],
                                        '_value' => [

                                        ]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ];

                $isPriceFeed++;
                unset($price);
                unset($isRepricingEnabled);
                unset($sku);
                unset($check);

            }
        }
        if ($isPriceFeed > 1) {
            /*$customGenerator = new Generator();
            $customGenerator->arrayToXml($priceArray);

            $str = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
                $customGenerator->__toString());
            $params['data'] = $str;

            var_dump($str);die("xml file");
            $response = $this->postRequest(self::GET_FEEDS_PRICE_SUB_URL, $params);*/

            if (!$merchant_id)
                $path = 'walmart/product/update/' . date('d-m-Y') . '/' . MERCHANT_ID . '/csv/price';
            else
                $path = 'walmart/product/update/' . date('d-m-Y') . '/' . $merchant_id . '/csv/price';
            $dir = \Yii::getAlias('@webroot') . '/var/' . $path;
            $path1 = 'product/update/' . date('d-m-Y') . '/' . $merchant_id . '/csv/price';

            $logFile = $path1 . '/update.log';
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            $xml = new Generator();
            $file = $dir . '/MPProduct-' . time() . '.xml';

            $xml->arrayToXml($priceArray)->save($file);
            $response = $this->postRequest(self::GET_FEEDS_PRICE_SUB_URL, ['file' => $file]);
            /*$response = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><ns2:FeedAcknowledgement xmlns:ns2="http://walmart.com/"><ns2:feedId>EA0BEDA5AD084E628E459110814676AE@AQkBAQA</ns2:feedId></ns2:FeedAcknowledgement>';*/
            $responseArray = self::xmlToArray($response);

            Data::createLog("Price response from walmart: " . PHP_EOL . json_encode($responseArray) . PHP_EOL, $logFile);

            if (isset($responseArray['FeedAcknowledgement'])) {

                /*foreach ($products as $productsvalue) {

                    if ($productsvalue['type'] == "No Variants") {
                        $query = "update `walmart_product` set product_price ='" . $productsvalue['price'] . "' where merchant_id='" . $merchant_id . "' AND product_id='" . $productsvalue['id'] . "'";
                        Data::sqlRecords($query, null, "update");
                    } else {

                        if ($productsvalue['type'] == 'Parent') {
                            $query = "update `walmart_product` set product_price ='" . $productsvalue['price'] . "' where merchant_id='" . $merchant_id . "' AND product_id='" . $productsvalue['id'] . "'";
                            Data::sqlRecords($query, null, "update");
                        }
                        $query = "update `walmart_product_variants` set option_prices ='" . $productsvalue['price'] . "' where merchant_id='" . $merchant_id . "' AND option_id='" . $productsvalue['option_id'] . "'";
                        Data::sqlRecords($query, null, "update");
                    }
                    $count++;

                }*/

                $prod = array_chunk($products, 100);

                foreach ($prod as $value) {
                    $id = [];
                    $option_id = [];
                    $when_price = '';
                    $when_option_price = '';
                    foreach ($value as $productsvalue) {
                        if ($productsvalue['type'] == "No Variants" /*|| $productsvalue['type'] == "Parent"*/) {
                            $id[] = $productsvalue['id'];

                            $when_price .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['price'] . '"';

                        } else {
                            if ($productsvalue['type'] == 'Parent') {
                                $id[] = $productsvalue['id'];

                                $when_price .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['price'] . '"';
                            }
                            $option_id[] = $productsvalue['option_id'];

                            $when_option_price .= ' WHEN ' . $productsvalue['option_id'] . ' THEN ' . '"' . $productsvalue['price'] . '"';
                        }
                    }
                    $ids = implode(',', $id);
                    $option_ids = implode(',', $option_id);

                    if (!empty($ids)) {
                        $query1 = "UPDATE `walmart_product` SET  
                                    `product_price` = CASE `product_id`
                                    " . $when_price . " 
                                END
                                WHERE product_id IN (" . $ids . ")";

                        Data::sqlRecords($query1, null, 'update');

                        Data::createLog("Query1: " . PHP_EOL . json_encode($query1) . PHP_EOL, $logFile);


                    }

                    if (!empty($option_ids)) {
                        $query2 = "UPDATE `walmart_product_variants` SET  
                                    `option_prices` = CASE `option_id`
                                    " . $when_option_price . " 
                                END
                                WHERE option_id IN (" . $option_ids . ")";

                        Data::sqlRecords($query2, null, 'update');
                        Data::createLog("Query2: " . PHP_EOL . json_encode($query2) . PHP_EOL, $logFile);

                    }
                }

                $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);

                Data::createLog("Price response: " . PHP_EOL . json_encode($result) . PHP_EOL, $logFile);
                /*$results = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                if (isset($results['results'][0], $results['results'][0]['itemsSucceeded']) && $results['results'][0]['itemsSucceeded'] == 1) {
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'count' => $count];
                }*/
                if (isset($responseArray['FeedAcknowledgement']['feedId'])) {
                    $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'count' => count($products)];
                }
            } elseif (isset($responseArray['errors'])) {
                return ['errors' => $responseArray['errors']];
            } else {
                return ['errors' => $responseArray];
            }
        } else {
            return ['errors' => "No products found for price update."];
        }
    }

    /**
     * Bulk Inventory Update On Walmart via Csv
     * @param [] $products
     * @return bool
     */
    public function updateInventoryViaCsv($products = [])
    {
        $merchant_id = Yii::$app->user->identity->id;
        $timeStamp = (string)time();
        $count1 = 0;
        $inventoryArray = [
            'InventoryFeed' => [
                '_attribute' => [
                    'xmlns' => "http://walmart.com/",
                ],
                '_value' => [
                    0 => ['InventoryHeader' => [
                        'version' => '1.4',
                    ],
                    ],
                ]
            ]
        ];

        $count = 1;

        if (is_array($products) && count($products) > 0) {
            foreach ($products as $product) {
                $inventory = $product['qty'];

                $inventoryArray['InventoryFeed']['_value'][$count] = [
                    'inventory' => [
                        'sku' => $product['sku'],
                        'quantity' => [
                            'unit' => 'EACH',
                            'amount' => $inventory,
                        ],
                        'fulfillmentLagTime' => isset($product['fulfillment_lag_time']) ? $product['fulfillment_lag_time'] : '1',
                    ]
                ];

                $count++;
            }
        }

        if ($count > 1) {
            if (!$merchant_id)
                $path = 'walmart/product/update/' . date('d-m-Y') . '/' . MERCHANT_ID . '/csv/inventory';
            else
                $path = 'walmart/product/update/' . date('d-m-Y') . '/' . $merchant_id . '/csv/inventory';
            $dir = \Yii::getAlias('@webroot') . '/var/' . $path;
            $path1 = 'product/update/' . date('d-m-Y') . '/' . $merchant_id . '/csv/inventory';

            $logFile = $path1 . '/update.log';
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            $file = $dir . '/MPProduct-' . time() . '.xml';
            $xml = new Generator();
            $xml->arrayToXml($inventoryArray)->save($file);

            Data::createLog('calling Post Request function : ', $logFile);
            $response = $this->postRequest(self::GET_FEEDS_INVENTORY_SUB_URL, ['file' => $file]);
            Data::createLog("inventory response: " . PHP_EOL . $response . PHP_EOL, $logFile);
            $responseArray = self::xmlToArray($response);
            if (isset($responseArray['FeedAcknowledgement'])) {
                $prod = array_chunk($products, 100);

                foreach ($prod as $value) {
                    $id = [];
                    $option_id = [];
                    $when_inventory = '';
                    $when_option_inventory = '';

                    foreach ($value as $productsvalue) {
                        if ($productsvalue['type'] == "No Variants" /*|| $productsvalue['type'] == "Parent"*/) {
                            $id[] = $productsvalue['id'];

                            $when_inventory .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['qty'] . '"';

                        } else {
                            if ($productsvalue['type'] == 'Parent') {
                                $id[] = $productsvalue['id'];

                                $when_inventory .= ' WHEN ' . $productsvalue['id'] . ' THEN ' . '"' . $productsvalue['qty'] . '"';
                            }
                            $option_id[] = $productsvalue['option_id'];

                            $when_option_inventory .= ' WHEN ' . $productsvalue['option_id'] . ' THEN ' . '"' . $productsvalue['qty'] . '"';
                        }
                    }
                    $ids = implode(',', $id);
                    $option_ids = implode(',', $option_id);

                    if (!empty($ids)) {
                        $query1 = "UPDATE `walmart_product` SET  
                                    `product_qty` = CASE `product_id`
                                    " . $when_inventory . " 
                                END
                                WHERE product_id IN (" . $ids . ")";
                        Data::sqlRecords($query1, null, 'update');
                    }

                    if (!empty($option_ids)) {
                        $query2 = "UPDATE `walmart_product_variants` SET  
                                    `option_qtys` = CASE `option_id`
                                    " . $when_option_inventory . " 
                                END
                                WHERE option_id IN (" . $option_ids . ")";
                        Data::sqlRecords($query2, null, 'update');
                    }
                }

                $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                Data::createLog("inventory response: " . PHP_EOL . json_encode($result) . PHP_EOL, $logFile);

                if (isset($responseArray['FeedAcknowledgement']['feedId'])) {
                    $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'count' => count($products)];

                }
            } elseif (isset($responseArray['errors'])) {
                return ['errors' => $responseArray['errors']];
            } else {
                return ['errors' => $responseArray];
            }
        } else {
            return ['errors' => "No products found for inventory update."];
        }
    }

    /**
     * Get all products info
     *
     * @param $product_ids (comma seperated product ids)
     * @return []
     */
    public static function getProductInfo($product_ids)
    {
        $merchant_id = Yii::$app->user->identity->id;

        $jet_columns = ['`jp`.`title`', '`jp`.`sku`', '`jp`.`type`', '`jp`.`product_type`', '`jp`.`price`,`jp`.`upc`'/*, '`jp`.``'*/];
        $walmart_columns = ['`wp`.`status`', '`wp`.`product_title`', '`wp`.`product_price`', '`wp`.`product_id`'/*, '`wp`.``'*/];
        $query = "SELECT " . implode(',', $jet_columns) . "," . implode(',', $walmart_columns) . " ,`wpr`.* FROM `jet_product` `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='{$merchant_id}' AND `product_id` IN ({$product_ids})) AS `wp` ON `jp`.`id`=`wp`.`product_id` RIGHT JOIN (SELECT * FROM `walmart_product_repricing` WHERE `merchant_id`='{$merchant_id}' AND `product_id` IN ({$product_ids})) AS `wpr` ON `wpr`.`product_id`=`wp`.`product_id`";
        $result = Data::sqlRecords($query, 'all', 'select');


        return $result;
    }

    /**
     * Get all variant products info
     *
     * @param $product_ids (comma seperated product ids)
     * @return []
     */
    public static function getVariantsProductInfo($product_ids)
    {
        $merchant_id = Yii::$app->user->identity->id;

        $jet_columns = ['`jpv`.`option_title`', '`jpv`.`option_sku`', '`jpv`.`option_image`', '`jpv`.`option_price`'];
        $walmart_columns = ['`wpv`.`status`', '`wpv`.`option_prices`', '`wpv`.`option_id`'];
        $query = "SELECT " . implode(',', $jet_columns) . "," . implode(',', $walmart_columns) . ",`wpr`.* FROM `jet_product_variants` `jpv` INNER JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`='{$merchant_id}' AND `product_id` IN ({$product_ids})) AS `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` RIGHT JOIN (SELECT * FROM `walmart_product_repricing` WHERE `merchant_id`='{$merchant_id}' AND `product_id` IN ({$product_ids})) AS `wpr` ON `wpr`.`option_id`=`wpv`.`option_id`";
        $result = Data::sqlRecords($query, 'all', 'select');

        return $result;
    }

    /**
     * Get all variant products info
     *
     * @param $product_ids (comma seperated product ids)
     * @return []
     */
    public static function getVariantsProduct($product_ids)
    {
        $merchant_id = Yii::$app->user->identity->id;

        $jet_columns = ['`jpv`.`option_title`', '`jpv`.`option_sku`', '`jpv`.`option_image`', '`jpv`.`option_price`'];
        $walmart_columns = ['`wpv`.`status`', '`wpv`.`option_prices`', '`wpv`.`option_id`'];
        $query = "SELECT " . implode(',', $jet_columns) . "," . implode(',', $walmart_columns) . ",`wpr`.* FROM `jet_product_variants` `jpv` INNER JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`='{$merchant_id}' AND `option_id` ='{$product_ids}') AS `wpv` ON `jpv`.`option_id`=`wpv`.`option_id` RIGHT JOIN (SELECT * FROM `walmart_product_repricing` WHERE `merchant_id`='{$merchant_id}' AND `option_id` ={$product_ids}) AS `wpr` ON `wpr`.`option_id`=`wpv`.`option_id`";
        $result = Data::sqlRecords($query, 'all', 'select');

        return $result;
    }

    /**
     * Bulk Update Price On Walmart via Csv
     * @param [] $products
     * @return bool
     */
    public function updateSinglePrice($product = null)
    {
        $timeStamp = (string)time();
        /*$priceArray = [
            'PriceFeed' => [
                '_attribute' => [
                    'xmlns:gmp' => "http://walmart.com/",
                ],
                '_value' => [
                    0 => [
                        'PriceHeader' => [
                            'version' => '1.5',
                        ],
                    ],
                ]
            ]
        ];*/

        $isPriceFeed = 1;

        if (!is_null($product)) {
            //foreach ($products as $product) {
            $price = $product['price'];

            //$priceArray['PriceFeed']['_value'][$isPriceFeed] = [
            $priceArray = [
                'Price' => [
                    'itemIdentifier' => [
                        'sku' => $product['sku']
                    ],
                    'pricingList' => [
                        'pricing' => [
                            'currentPrice' => [
                                'value' => [
                                    '_attribute' => [
                                        'currency' => $product['currency'],
                                        'amount' => $price
                                    ],
                                    '_value' => [

                                    ]
                                ]
                            ],
                            'currentPriceType' => 'BASE',
                            'comparisonPrice' => [
                                'value' => [
                                    '_attribute' => [
                                        'currency' => $product['currency'],
                                        'amount' => $price
                                    ],
                                    '_value' => [

                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ];

            $isPriceFeed++;
            //}
        }

        if ($isPriceFeed > 1) {
            $customGenerator = new Generator();
            $customGenerator->arrayToXml($priceArray);

            $str = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8"?>',
                $customGenerator->__toString());
            $params['data'] = $str;

            //var_dump($str);die("xml file");
            $response = $this->putRequest(self::PUT_PRICE_SUB_URL, $params);
            $responseArray = self::xmlToArray($response);
            if (isset($responseArray['FeedAcknowledgement'])) {
                $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                if (isset($results['results'][0], $results['results'][0]['itemsSucceeded']) && $results['results'][0]['itemsSucceeded'] == 1) {
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId']];
                }
            } elseif (isset($responseArray['errors'])) {
                return ['errors' => $responseArray['errors']];
            } else {
                return $responseArray;
            }
        } else {
            return ['errors' => "No products found for price update."];
        }
    }

    public function uploadFeedOnWalmart($feed_file, $feed_type)
    {
        if (file_exists($feed_file)) {

            if($feed_type == 'item') {
                $response = $this->postRequest(self::GET_FEEDS_ITEMS_SUB_URL, ['file' => $feed_file]);
            } elseif($feed_type == 'price') {
                $response = $this->postRequest(self::GET_FEEDS_PRICE_SUB_URL, ['file' => $feed_file]);
            } elseif($feed_type == 'inventory') {
                $response = $this->postRequest(self::GET_FEEDS_INVENTORY_SUB_URL, ['file' => $feed_file]);
            }
            $response = str_replace('ns2:', "", $response);

            $responseArray = [];
            $responseArray = self::xmlToArray($response);

            if (isset($responseArray['FeedAcknowledgement'])) {
                echo "<div style='background-color: #dff0d8; color: #3c763d;'>Feed Uploaded Successfully.</div>";
                print_r($responseArray);
                die;
            } elseif ($responseArray['errors']) {
                echo "<div style='background-color: #f2dede; color: #a94442;'>Error from Walmart.</div>";
                print_r($responseArray);
                die;
            }
        } else {
            echo "<div style='background-color: #f2dede; color: #a94442;'>File Not found.</div>";
        }
    }

    public function uploadAllProductsOnWalmart($ids, $merchant_id)
    {
        if (count($ids) > 0) {
            $productsToBeUploadedInSingleFeed = 5000;

            $dir = Yii::getAlias('@webroot') . self::ALL_PRODUCT_UPLOAD_FILEPATH;
            $filePath = $dir . $merchant_id . '.php';

            $connection = Yii::$app->getDb();

            $error = [];
            $returnArr = [];
            $uploadProductIds = [];

            if (file_exists($filePath)) {
                $storedData = require $filePath;

                $productToUpload = $storedData;

                $count = count($productToUpload['MPItemFeed']['_value']);
                $successXmlCreate = $count - 1;

                end($productToUpload['MPItemFeed']['_value']);
                $key = key($productToUpload['MPItemFeed']['_value']) + 1;
            } else {
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

                $successXmlCreate = 0;
                $key = 1;
            }

            $dir1 = Yii::getAlias('@webroot') . '/var/walmart/filestorage/product/create/';
            $file = $dir1 . $merchant_id . '.php';

            foreach ($ids as $id) {

                Data::createLog(' PRODUCT IDS ' . $id['product_id'].PHP_EOL, $file, 'a+');
                //$not_uploaded_status = WalmartProductModel::PRODUCT_STATUS_NOT_UPLOADED;
                //$query = "SELECT product_id,variant_id,title,sku,type,wal.product_type,wal.status,description,image,qty,price,weight,vendor,upc,walmart_attributes,category,tax_code,short_description,self_description,common_attributes,attr_ids,sku_override,product_id_override,`wal`.`walmart_optional_attributes`,`wal`.`shipping_exceptions` FROM (SELECT * FROM `walmart_product` WHERE `merchant_id`=" . $merchant_id . ") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`='" . $merchant_id . "') as `jet` ON jet.id=wal.product_id WHERE wal.product_id='" . $id['product_id'] . "' LIMIT 1";
                $query = 'SELECT `product_id`, `variant_id`, `title`, `sku`, `type`, `wal`.`product_type`, `wal`.`status`, `wal`.`product_qty`, `description`, `image`, `qty`, `price`, `weight`, `vendor`, `upc`, `walmart_attributes`, `category`, `parent_category`, `tax_code`, `long_description`, `short_description`, `self_description`, `common_attributes`, `attr_ids`, `sku_override`, `product_id_override`, `wal`.`walmart_optional_attributes`, `wal`.`shipping_exceptions` FROM (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . $merchant_id . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . $merchant_id . '") as `jet` ON jet.id=wal.product_id WHERE wal.product_id="' . $id['product_id'] . '" LIMIT 1';

                $productArray = Data::sqlRecords($query, "one", "select");

                if ($productArray) {
                    $validateResponse = self::validateProduct($productArray, $merchant_id);

                    if (is_array($validateResponse) && isset($validateResponse['error'])) {

                        Data::createLog(' Validate Error Response ' . json_encode($validateResponse).PHP_EOL, $file, 'a+');

                        $error[$productArray['sku']] = $validateResponse['error'];
                        continue;
                    } elseif ($validateResponse === true) {

                        Data::createLog(' Validate Success Response ' . $validateResponse.PHP_EOL, $file, 'a+');


                        $image = trim($productArray['image']);
                        $imageArr = explode(',', $image);

                        $variantGroupId = (string)$id['product_id'] . (string)time();

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

                        // walmart product title
                        $title = Data::getWalmartTitle($productArray['product_id'], $merchant_id);

                        if (isset($title['product_title']) && !empty($title)) {
                            $productArray['title'] = $title['product_title'];
                        }

                        if ($productArray['type'] == "simple") {

                            Data::createLog(' PRODUCT TYPE -> simple'.PHP_EOL, $file, 'a+');

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

                            $MPItem = self::prepareMPItem($merchant_id, $product, $productArray);

                            Data::createLog(' MPItem '.json_encode($MPItem).PHP_EOL, $file, 'a+');

                            if (isset($MPItem['status']) && !$MPItem['status']) {
                                $error_msg = isset($MPItem['error']) ? $MPItem['error'] : 'Product can not be uploaded.';
                                $error[$productArray['sku']] = $error_msg;
                                continue;
                            }

                            $validate = WalmartProductValidate::validatev3ProductXml($MPItem);

                            Data::createLog(' validatev3ProductXml '.json_encode($validate).PHP_EOL, $file, 'a+');

                            if (!$validate['status']) {
                                $message = "Rejected by Walmart because there was a glitch on walmart's end. Please contact us.";
                                $error[$productArray['sku']] = ["error" => $message, "xml_validation_error" => $validate['error']];
                                continue;
                            }

                            /*$productToUpload['MPItemFeed']['_value'][$key]['MPItem'] = $MPItem;
                            $key++;*/
                            if (isset($validate['status'])) {

                                $productToUpload['MPItemFeed']['_value'][$key]['MPItem'] = $MPItem;
                                $successXmlCreate++;
                                if (!in_array($id['product_id'], $uploadProductIds)) {
                                    $uploadProductIds[] = $id['product_id'];
                                }

                                if ($successXmlCreate == $productsToBeUploadedInSingleFeed) {
                                    $feedResponse = self::submitAllItemFeed($productToUpload, $filePath);
                                    if (isset($feedResponse['feedId'])) {
                                        $returnArr['feedId'] = $feedResponse['feedId'];
                                        $returnArr['feed_file'] = $feedResponse['feed_file'];

                                        $key = 1;
                                        $successXmlCreate = 0;

                                        if (!self::canSendItemFeed($merchant_id)) {
                                            $check_point = $id['id'];
                                            self::saveLastCheckPoint($merchant_id, $check_point);

                                            return ['threshold_error' => "Threshold Limit Exceeded. Please try again after 1 Hour."];
                                            break;
                                        }
                                    } elseif (isset($feedResponse['feedError'])) {
                                        $error['feedError'] = $feedResponse['feedError'];
                                    }
                                }

                            }
                            $key += 1;
                            /*$dir = Yii::getAlias('@webroot') . '/frontend/modules/walmart/filestorage/product/create/';
                            $file = $dir . $merchant_id . '.php';
                            Data::createLog('SIMPLE KEY '.$key,$file,'a+');*/

                            /*if (!in_array($id, $uploadProductIds)) {
                                $uploadProductIds[] = $id;
                            }*/
                        } else {

                            Data::createLog(' PRODUCT TYPE -> variants'.PHP_EOL, $file, 'a+');

                            $duplicateSkus = [];
                            $query = 'SELECT jet.option_id,option_title,option_sku,wal.walmart_option_attributes,option_image,option_qty,option_price ,option_weight,option_unique_id,`wal`.`walmart_optional_attributes`, `jet`.`variant_option1`, `jet`.`variant_option2`, `jet`.`variant_option3`, `wal`.`status` FROM (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`="' . $merchant_id . '") as wal INNER JOIN (SELECT * FROM `jet_product_variants` WHERE `merchant_id`="' . $merchant_id . '") as jet ON jet.option_id=wal.option_id WHERE wal.product_id="' . $id['product_id'] . '"';
                            $productVarArray = Data::sqlRecords($query, "all", "select");

                            foreach ($productVarArray as $value) {

                                Data::createLog(' OPITION ID '.$value['option_id'].PHP_EOL, $file, 'a+');

                                $value['option_price'] = WalmartRepricing::getProductPrice($value['option_price'], $productArray['type'], $value['option_id'], $merchant_id);

                                if (in_array($value['option_sku'], $duplicateSkus)) {
                                    $error[$productArray['sku']][$value['option_sku']] = "Variant Sku : '" . $value['option_sku'] . "' is duplicate.";
                                    continue;
                                } else
                                    $duplicateSkus[] = $value['option_sku'];

                                if (strlen($value['option_sku']) > WalmartProductValidate::MAX_LENGTH_SKU) {
                                    $error[$productArray['sku']][$value['option_sku']] = "Variant Sku : " . $value['option_sku'] . " must be fewer than 50 characters.";
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

                                Data::createLog(' MPItem '.json_encode($MPItem).PHP_EOL, $file, 'a+');

                                if (isset($MPItem['status']) && !$MPItem['status']) {
                                    $error_msg = isset($MPItem['error']) ? $MPItem['error'] : 'Product can not be uploaded.';
                                    $error[$productArray['sku']][$value['option_sku']] = $error_msg;
                                    continue;
                                }

                                $validate = WalmartProductValidate::validatev3ProductXml($MPItem);

                                Data::createLog(' validatev3ProductXml '.json_encode($validate).PHP_EOL, $file, 'a+');

                                if (!$validate['status']) {
                                    $message = "Rejected by Walmart because there was a glitch on walmart's end. Please contact us.";
                                    $error[$productArray['sku']][$value['option_sku']] = ["error" => $message, "xml_validation_error" => $validate['error']];
                                    continue;
                                }
                                if (isset($validate['status'])) {
                                    $productToUpload['MPItemFeed']['_value'][$key]['MPItem'] = $MPItem;
                                    $successXmlCreate++;
                                    if (!in_array($id['product_id'], $uploadProductIds)) {
                                        $uploadProductIds[] = $id['product_id'];
                                    }

                                    if ($successXmlCreate == $productsToBeUploadedInSingleFeed) {
                                        $feedResponse = self::submitAllItemFeed($productToUpload, $filePath);
                                        if (isset($feedResponse['feedId'])) {
                                            $returnArr['feedId'] = $feedResponse['feedId'];
                                            $returnArr['feed_file'] = $feedResponse['feed_file'];

                                            $key = 1;
                                            $successXmlCreate = 0;

                                            if (!self::canSendItemFeed($merchant_id)) {
                                                $check_point = $id['id'];
                                                self::saveLastCheckPoint($merchant_id, $check_point);

                                                return ['threshold_error' => "Threshold Limit Exceeded. Please try again after 1 Hour."];
                                                break;
                                            }
                                        } elseif (isset($feedResponse['feedError'])) {
                                            $error['feedError'] = $feedResponse['feedError'];
                                        }
                                    }

                                    //set status to 'Item Processing'
                                    Jetproductinfo::chnageUploadingProductStatus($value['option_id']);
                                }
                                $key += 1;


                                /*$dir = Yii::getAlias('@webroot') . '/frontend/modules/walmart/filestorage/product/create/';
                                $file = $dir . $merchant_id . '.php';
                                Data::createLog(' VARIANT KEY '.$key,$file,'a+');*/

                                /*$productToUpload['MPItemFeed']['_value'][$key]['MPItem'] = $MPItem;
                                $key++;

                                if (!in_array($id, $uploadProductIds)) {
                                    $uploadProductIds[] = $id;
                                }*/
                                //set status to 'Item Processing'
                                /*Jetproductinfo::chnageUploadingProductStatus($value['option_id']);*/

                            }
                        }
                    }

                } else {
                    $error[$id['product_id']] = "Product Id : " . $id['product_id'] . " is already uploaded.";
                    continue;
                }

//                break;
            }

            /*$index = intval(Yii::$app->request->post('index', false));
            $total = intval(Yii::$app->request->post('total_pages', false));*/
            $index = intval(Yii::$app->request->post('page', false));
            $total = intval(Yii::$app->request->post('total_pages', false));

            Data::createLog('index : ' . $index . ', total page : ' . $total . PHP_EOL, 'total.log');

            if (($total - 1) == $index) {

                Data::createLog('Uploading feed to walmart.' . PHP_EOL, 'total.log');
                $feedResponse = self::submitAllItemFeed($productToUpload, $filePath);
                Data::createLog(print_r($feedResponse, true), 'total.log');
                if (isset($feedResponse['feedId'])) {
                    $returnArr['feedId'] = $feedResponse['feedId'];
                    $returnArr['feed_file'] = $feedResponse['feed_file'];
                } elseif (isset($feedResponse['feedError'])) {
                    $error['feedError'] = $feedResponse['feedError'];
                }
            } elseif ($successXmlCreate) {

                self::saveFeedData($dir, $filePath, $productToUpload);
            }

            if (count($uploadProductIds)) {
                $returnArr['uploadIds'] = $uploadProductIds;
            }

            if (count($error) > 0) {
                $returnArr['errors'] = $error;
                $returnArr['originalmessage'] = '';
            }
            return $returnArr;
        }
    }

    public static function saveFeedData($dir, $filePath, $preparedData)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }


        file_put_contents($filePath, '<?php return $arr = ' . var_export($preparedData, true) . ';');
    }

    public static function submitAllItemFeed(&$productToUpload, $filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if (isset($productToUpload['MPItemFeed']['_value']) && count($productToUpload['MPItemFeed']['_value']) > 1) {

            if (!file_exists(\Yii::getAlias('@webroot') . '/var/product/xml/' . MERCHANT_ID)) {
                mkdir(\Yii::getAlias('@webroot') . '/var/product/xml/' . MERCHANT_ID, 0775, true);
            }
            $file = Yii::getAlias('@webroot') . '/var/product/xml/' . MERCHANT_ID . '/MPProduct-' . time() . '.xml';
            $xml = new Generator();
            $xml->arrayToXml($productToUpload)->save($file);
            self::unEscapeData($file);

            $response = $this->postRequest(self::GET_FEEDS_ITEMS_SUB_URL, ['file' => $file]);
            /*$response ='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><ns2:FeedAcknowledgement xmlns:ns2="http://walmart
.com/"><ns2:feedId>8B20B555955C47148EA5A5A099BBF864@AQMBAAA</ns2:feedId></ns2:FeedAcknowledgement>';*/
            $response = str_replace('ns2:', "", $response);
            Data::createLog($response, 'total.log');
            $responseArray = [];
            $responseArray = self::xmlToArray($response);

            //$responseArray['FeedAcknowledgement']['feedId'] = 'testfeedid';

            if (isset($responseArray['FeedAcknowledgement'])) {
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

                $feedId = isset($responseArray['FeedAcknowledgement']['feedId']) ? $responseArray['FeedAcknowledgement']['feedId'] : '';
                return ['feedId' => $feedId, 'feed_file' => $file];
            } elseif ($responseArray['errors']) {
                return ['feedError' => $responseArray['errors']];
            }
        }
    }

    public static function canSendItemFeed($merchant_id)
    {
        $query = "SELECT count(*) as `feed_count` FROM `walmart_product_feed` WHERE `created_at`>= DATE_SUB(NOW(),INTERVAL 1 HOUR) AND `merchant_id`={$merchant_id} LIMIT 0,1";
        $result = Data::sqlRecords($query, 'one');

        if (isset($result['feed_count']) && intval($result['feed_count']) < 10) {
            return true;
        } else {
            return false;
        }
    }

    public static function saveLastCheckPoint($merchant_id, $last_send_index)
    {
        $feed_type = self::FEED_TYPE_ITEM;

        $query = "INSERT INTO `walmart_feed_stats` (`merchant_id`, `feed_type`, `last_send_index`) VALUES ({$merchant_id}, '{$feed_type}', '{$last_send_index}')";

        Data::sqlRecords($query, null, 'insert');
    }

    /**
     * if product bulk action count id less than 51 enable put request
     * @return bool
     */
    public static function isEnablePutRequest($count)
    {
        if ($count <= 50) {
            return true;
        } else {
            return false;
        }
    }

    public static function getMPItemStructure()
    {
        $structure = [
            'processMode' => self::NON_REQUIRED_ATTRIBUTE,
            'feedDate' => self::NON_REQUIRED_ATTRIBUTE,
            'sku' => self::REQUIRED_ATTRIBUTE,
            'productIdentifiers' => self::REQUIRED_ATTRIBUTE,
            'MPProduct' => self::REQUIRED_ATTRIBUTE,
            'MPOffer' => self::NON_REQUIRED_ATTRIBUTE,
        ];

        return $structure;
    }

    public static function getMPProductStructure()
    {
        $structure = [
            'SkuUpdate' => self::NON_REQUIRED_ATTRIBUTE,
            'msrp' => self::NON_REQUIRED_ATTRIBUTE,
            'productName' => self::REQUIRED_ATTRIBUTE,
            'additionalProductAttributes' => self::NON_REQUIRED_ATTRIBUTE,
            'ProductIdUpdate' => self::NON_REQUIRED_ATTRIBUTE,
            'category' => self::REQUIRED_ATTRIBUTE,
        ];

        return $structure;
    }

    public static function getMPOfferStructure()
    {
        $structure = [
            'price' => self::REQUIRED_ATTRIBUTE,
            'MinimumAdvertisedPrice' => self::NON_REQUIRED_ATTRIBUTE,
            'StartDate' => self::NON_REQUIRED_ATTRIBUTE,
            'EndDate' => self::NON_REQUIRED_ATTRIBUTE,
            'MustShipAlone' => self::NON_REQUIRED_ATTRIBUTE,
            'ShippingWeight' => self::REQUIRED_ATTRIBUTE,
            'ProductTaxCode' => self::REQUIRED_ATTRIBUTE,
            'shipsInOriginalPackaging' => self::NON_REQUIRED_ATTRIBUTE,
            'additionalOfferAttributes' => self::NON_REQUIRED_ATTRIBUTE,
            'ShippingOverrides' => self::NON_REQUIRED_ATTRIBUTE,
        ];

        return $structure;
    }

    /**
     * get the value of product identifiers for payload
     * @param [] $identifiers
     *           For Example : $identifiers = [['identifier_type'=>'XXXX', 'identifier_value'=>'XXXX'], [], ...]
     *
     * @return array
     */
    public static function getProductIdentifiersValue($identifiers)
    {
        $productIdentifiers = [];
        $formattedIdentifiers = [];
        $allowedIdentifiers = ["UPC", "GTIN", "ISBN", "EAN"];

        if (count($identifiers)) {
            foreach ($identifiers as $identifier) {
                if (in_array($type = $identifier['identifier_type'], $allowedIdentifiers)) {
                    $value = $identifier['identifier_value'];
                    $formattedIdentifiers[] = [
                        'productIdentifier' => ['productIdType' => $type, 'productId' => $value]
                    ];
                }
            }

            if (count($formattedIdentifiers)) {
                $productIdentifiers['_attribute'] = [];
                $productIdentifiers['_value'] = $formattedIdentifiers;
            }
        }

        return $productIdentifiers;
    }

    public static function validateStructure(&$structure)
    {
        foreach ($structure as $attr_key => $attr_value) {
            if ($attr_value == self::REQUIRED_ATTRIBUTE) {
                return ['status' => false, 'error' => $attr_key . " is Required."];
                break;
            } elseif ($attr_value == self::NON_REQUIRED_ATTRIBUTE) {
                unset($structure[$attr_key]);
            }
        }
        return ['status' => true];
    }

    public static function generateXml($arr)
    {
        $file = Yii::getAlias('@webroot') . '/frontend/modules/walmart/components/test.xml';
        $xml = new Generator();
        $xml->arrayToXml($arr)->save($file);
        self::unEscapeData($file);
        die('created');
    }
}