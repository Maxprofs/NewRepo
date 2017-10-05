<?php
namespace frontend\modules\walmart\components\Inventory;

use Yii;
use yii\base\Component;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\components\Signature;
use frontend\modules\walmart\components\Generator;
use frontend\modules\walmart\components\Xml\Parser;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartAttributeMap;
use yii\base\Response;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\components\WalmartCategory;
use frontend\modules\walmart\components\WalmartProductValidate;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\WalmartPromoStatus;
use frontend\modules\walmart\components\WalmartProduct as WalmartProductComponent;

class InventoryUpdate extends Walmartapi
{
    const GET_FEEDS_INVENTORY_SUB_URL = 'v2/feeds?feedType=inventory';
    const GET_INVENTORY_SUB_URL = 'v2/inventory';
    const GET_ITEMS_SUB_URL = 'v2/items';
    /**
     * Action use in cron/bulkInventoryUpdate
     * Update Inventory On Walmart
     * @param string|[] $ids
     * @return bool
     */
    public function updateInventoryOnWalmart($product = [], $datafrom = null, $merchant_id = false)
    {
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
        $timeStamp = (string)time();
        $isInvFeed = 0;
        $key = 0;
        if ($datafrom == "product") {
            if (is_array($product) && count($product) > 0) {
                foreach ($product as $pro) {
                    //check product available on walmart
                    $response = $this->getItem($pro['sku']);
                    if (is_array($response) && count($response)) {
                        self::saveStatus($pro['id'], $response);
                    }
                    $isInvFeed++;
                    if ($pro['type'] == 'variants') {
                        $varProducts = [];
                        $query = "select option_id,option_sku,option_qty from `jet_product_variants` where product_id='" . $pro['id'] . "'";
                        $varProducts = Data::sqlRecords($query, "all", "select");
                        foreach ($varProducts as $value) {
                            $key += 1;
                            /*if (Data::getInventoryData($merchant_id)) {*/
                                $query = "select option_qtys from `walmart_product_variants` where option_id='" . $value['option_id'] . "'";
                                $walvarProducts = Data::sqlRecords($query, "one", "select");
                                if (!is_null($walvarProducts['option_qtys'])) {
                                    $value['option_qty'] = $walvarProducts['option_qtys'];
                                }
                            if(!Data::checkThresholdValue($value['option_qty'],$merchant_id)){
                                $value['option_qty'] = '0';
                            }
                            /*}*/
                            $inventoryArray['InventoryFeed']['_value'][$key] = [
                                'inventory' => [
                                    'sku' => $value['option_sku'],
                                    'quantity' => [
                                        'unit' => 'EACH',
                                        'amount' => $value['option_qty'],
                                    ],
                                    'fulfillmentLagTime' => isset($pro['fulfillment_lag_time']) ? $pro['fulfillment_lag_time'] : '1',
                                ]
                            ];
                        }
                    } else {
                        $key += 1;
                        /*if (Data::getInventoryData($merchant_id)) {*/
                            if (!is_null($pro['product_qty'])) {
                                $pro['qty'] = $pro['product_qty'];
                            }
                            if(!Data::checkThresholdValue($pro['qty'],$merchant_id)){
                                $pro['qty'] = '0';
                            }

                      /*  }*/
                        $inventoryArray['InventoryFeed']['_value'][$key] = [
                            'inventory' => [
                                'sku' => $pro['sku'],
                                'quantity' => [
                                    'unit' => 'EACH',
                                    'amount' => $pro['qty'],
                                ],
                                'fulfillmentLagTime' => isset($pro['fulfillment_lag_time']) ? $pro['fulfillment_lag_time'] : '1',
                            ]
                        ];
                    }
                }
            }
        } elseif ($datafrom == "webhook") {
            $key += 1;
            $isInvFeed++;
            if(!Data::checkThresholdValue($product['qty'],$merchant_id)){
                $product['qty'] = '0';
            }
            $inventoryArray['InventoryFeed']['_value'][$key] = [
                'inventory' => [
                    'sku' => $product['sku'],
                    'quantity' => [
                        'unit' => 'EACH',
                        'amount' => $product['qty'],
                    ],
                    'fulfillmentLagTime' => isset($product['fulfillment_lag_time']) ? $product['fulfillment_lag_time'] : '1',
                ]
            ];
        } elseif ($datafrom == "cron") {
            $key += 1;
            $isInvFeed++;
            foreach ($product as $pro) {
                if(!Data::checkThresholdValue($pro['inventory'],$merchant_id)){
                    $pro['inventory'] = '0';
                }
                $inventoryArray['InventoryFeed']['_value'][$key++] = [
                    'inventory' => [
                        'sku' => $pro['sku'],
                        'quantity' => [
                            'unit' => 'EACH',
                            'amount' => $pro['inventory'],
                        ],
                        'fulfillmentLagTime' => isset($pro['fulfillment_lag_time']) ? $pro['fulfillment_lag_time'] : '1',
                    ]
                ];
            }
        }

        if (count($product) > 0 && $isInvFeed > 0) {
            if (!$merchant_id)
                $path = 'product/update/' . date('d-m-Y') . '/' . MERCHANT_ID . '/inventory';
            else
                $path = 'product/update/' . date('d-m-Y') . '/' . $merchant_id . '/inventory';
            $dir = \Yii::getAlias('@webroot') . '/var/' . $path;
            $logFile = $path . '/update.log';
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            $file = $dir . '/MPProduct-' . time() . '.xml';
            $xml = new Generator();
            $xml->arrayToXml($inventoryArray)->save($file);

            Data::createLog('calling Post Request function : ', $logFile);

            $response = $this->postRequest(self::GET_FEEDS_INVENTORY_SUB_URL, ['file' => $file]);
            Data::createLog("inventory response: " . PHP_EOL . $response . PHP_EOL, $logFile);
            try {
                $responseArray = self::xmlToArray($response);
            } catch (Exception $e) {
                $path = $dir . '/Exception.log';
                Data::createLog("inventory response: " . PHP_EOL . $response . PHP_EOL, $path, true);
            }
            if (isset($responseArray['FeedAcknowledgement'])) {
                $result = [];
                $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                if (isset($result['results'][0], $result['results'][0]['itemsSucceeded']) && $result['results'][0]['itemsSucceeded'] == 1) {
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId']];
                }
            } elseif (isset($responseArray['errors'])) {
                return ['errors' => $responseArray['errors']];
            }
            //return $responseArray;
        }
    }
    /**
     * Action use in Product selection Inventory Update 
     * Update Inventory On Walmart
     * @param string|[] $product
     * @return array
     */
    public function batchupdateInventoryOnWalmart($product, $datafrom = null)
    {
        $error = [];
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
        $timeStamp = (string)time();
        $isInvFeed = 0;
        $key = 0;
        $merchant_id = MERCHANT_ID;
        foreach ($product as $id) {
            $query = Data::sqlRecords('select jet.id,sku,type,qty,jet.merchant_id,`wal`.`product_qty` from (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . MERCHANT_ID . '" AND `product_id`="' . $id . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '" AND `id`="' . $id . '") as jet ON jet.id=wal.product_id where (wal.status="' . WalmartProduct::PRODUCT_STATUS_UPLOADED . '" OR wal.status="' . WalmartProduct::PRODUCT_STATUS_UNPUBLISHED . '" OR wal.status="' . WalmartProduct::PRODUCT_STATUS_STAGE . '")and wal.merchant_id="' . MERCHANT_ID . '"', 'one');

            if (isset($query) && !empty($query)) {
                if (is_array($query) && count($query) > 0) {
                    $isInvFeed++;
                    if ($query['type'] == 'variants') {
                        $varProducts = [];
                        $query1 = "select option_id,option_sku,option_qty from `jet_product_variants` where product_id='" . $query['id'] . "'";
                        $varProducts = Data::sqlRecords($query1, "all", "select");
                        foreach ($varProducts as $value) {
                            $key += 1;
                            /*if (Data::getInventoryData($merchant_id)) {*/
                                $query = "select option_qtys from `walmart_product_variants` where option_id='" . $value['option_id'] . "'";
                                $walvarProducts = Data::sqlRecords($query, "one", "select");
                                if (!is_null($walvarProducts['option_qtys'])) {
                                    $value['option_qty'] = trim($walvarProducts['option_qtys']);
                                }
                                if(!Data::checkThresholdValue($value['option_qty'],$merchant_id)){
                                    $value['option_qty'] = '0';
                                }
                         /*   }*/
                            $inventoryArray['InventoryFeed']['_value'][$key] = [
                                'inventory' => [
                                    'sku' => $value['option_sku'],
                                    'quantity' => [
                                        'unit' => 'EACH',
                                        'amount' => $value['option_qty'],
                                    ],
                                    'fulfillmentLagTime' => isset($query['fulfillment_lag_time']) ? $query['fulfillment_lag_time'] : '1',
                                ]
                            ];
                        }
                    } else {
                        $key += 1;
                        /*if (Data::getInventoryData($merchant_id)) {*/
                            if (!is_null($query['product_qty'])) {
                                $query['qty'] = trim($query['product_qty']);
                            }
                            if(!Data::checkThresholdValue($query['qty'],$merchant_id)){
                                $query['qty'] = '0';
                            }
                      /*  }*/
                        $inventoryArray['InventoryFeed']['_value'][$key] = [
                            'inventory' => [
                                'sku' => $query['sku'],
                                'quantity' => [
                                    'unit' => 'EACH',
                                    'amount' => $query['qty'],
                                ],
                                'fulfillmentLagTime' => isset($query['fulfillment_lag_time']) ? $query['fulfillment_lag_time'] : '1',
                            ]
                        ];
                    }
                }

            } else {
                $sku = Data::getProductSku($id);
                $error[$sku] = "Product Not Found on Walmart";
            }
        }
        if ($isInvFeed > 0) {
            $path = 'product/update/' . date('d-m-Y') . '/' . MERCHANT_ID . '/inventory';
            $dir = \Yii::getAlias('@webroot') . '/var/' . $path;
            $logFile = $path . '/update.log';
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            $file = $dir . '/MPProduct-' . time() . '.xml';
            $xml = new Generator();
            $xml->arrayToXml($inventoryArray)->save($file);

            Data::createLog('calling Post Request function : ', $logFile);
            $response = $this->postRequest(self::GET_FEEDS_INVENTORY_SUB_URL, ['file' => $file]);
            Data::createLog("inventory response: " . PHP_EOL . $response . PHP_EOL, $logFile);
            try {
                $responseArray = self::xmlToArray($response);
            } catch (Exception $e) {
                $path = $dir . '/Exception.log';
                Data::createLog("inventory response: " . PHP_EOL . $response . PHP_EOL, $path, true);
            }
            if (isset($responseArray['FeedAcknowledgement'])) {
                $result = [];
                $result = $this->getFeeds($responseArray['FeedAcknowledgement']['feedId']);
                if (isset($result['results'][0], $result['results'][0]['itemsSucceeded']) && $result['results'][0]['itemsSucceeded'] == 1) {
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'erroredSkus' => $error, 'error_count' => count($error)];
                } else {
                    return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'erroredSkus' => $error, 'error_count' => count($error)];
                }
            } elseif (isset($responseArray['errors'])) {
                return ['errors' => $responseArray['errors'], 'erroredSkus' => $error, 'error_count' => count($error)];
            }
        } else {
            return ['erroredSkus' => $error, 'error_count' => count($error)];
        }

    }
     /**
     * Action use in Product Edit Inventory Update
     * Update Inventory On Walmart
     * @param string|[] $product
     * @return array
     */
        public function updateWalmartinventory($product = [])
    {
        $isEnablePutRequest = false;

        if (count($product) == 1) {
            $isEnablePutRequest = true;
        }

        $merchant_id = MERCHANT_ID;

        if ($isEnablePutRequest) {
            $inventoryArray = [
                'wm:inventory' => [
                    '_attribute' => [
                        'xmlns:wm' => "http://walmart.com/",
                    ],
                    '_value' => [
                    ]
                ]
            ];
        } else {
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
        }
        $timeStamp = (string)time();
        $isInvFeed = 0;
        $key = 0;
        if (is_array($product) && count($product) > 0) {
            //check product available on walmart
            $response = $this->getItem($product['sku']);
            if (is_array($response) && count($response)) {
                self::saveStatus($product['id'], $response);
            }
            $isInvFeed++;

            $key += 1;
            if ($isEnablePutRequest) {
                $this->prepareInventoryData($inventoryArray, $product);
            } else {
                if(!Data::checkThresholdValue($product['qty'],$merchant_id)){
                    $product['qty'] = '0';
                }
                $inventoryArray['InventoryFeed']['_value'][$key] = [
                    'inventory' => [
                        'sku' => $product['sku'],
                        'quantity' => [
                            'unit' => 'EACH',
                            'amount' => $product['qty'],
                        ],
                        'fulfillmentLagTime' => isset($product['fulfillment_lag_time']) ? $product['fulfillment_lag_time'] : '1',
                    ]
                ];
            }
        }

        if (count($product) > 0 && $isInvFeed > 0) {

            if (!file_exists(\Yii::getAlias('@webroot') . '/var/product/xml/' . MERCHANT_ID . '/updatemanuallyinventory')) {
                mkdir(\Yii::getAlias('@webroot') . '/var/product/xml/' . MERCHANT_ID . '/updatemanuallyinventory', 0775, true);
            }
            $file = Yii::getAlias('@webroot') . '/var/product/xml/' . MERCHANT_ID . '/updatemanuallyinventory/MPProduct-' . time() . '.xml';
            $xml = new Generator();
            $data = $xml->arrayToXml($inventoryArray)->save($file);
            if ($isEnablePutRequest) {
                $response = $this->putRequest(self::GET_INVENTORY_SUB_URL . '?sku=' . $product['sku'], ['data' => file_get_contents($file)]);
                $responseArray = self::xmlToArray($response, true);
                if (isset($responseArray['ns2:errors'])) {
                    $responseArray = self::xmlToArray($response);
                }
                if (isset($responseArray['inventory']['sku'])) {
                    return ['success' => true, 'message' => 'Inventory Feeds is successfully submitted on walmart'];
                } elseif (isset($responseArray['errors'])) {
                    return ['errors' => $responseArray['errors']];
                }
            } else {
                $response = $this->postRequest(self::GET_FEEDS_INVENTORY_SUB_URL, ['file' => $file]);
                $responseArray = self::xmlToArray($response);

                if (isset($responseArray['FeedAcknowledgement'])) {
                    return ['success' => true, 'message' => 'Inventory Feeds is successfully submitted on walmart'];


                } elseif (isset($responseArray['errors'])) {
                    return ['errors' => $responseArray['errors']];
                }
            }
            //return $responseArray;
        }
    }
    /*end by shivam*/
    
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
                if(!Data::checkThresholdValue($inventory,$merchant_id)){
                    $inventory = '0';
                }
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
}