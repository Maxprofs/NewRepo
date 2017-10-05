<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 24/3/17
 * Time: 5:01 PM
 */
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Generator;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\WalmartReport;
use frontend\modules\walmart\components\WalmartProduct as WalmartProductComponent;

use Yii;
use frontend\modules\walmart\models\WalmartproductdetailSearch;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;


/**
 * Walmart product detail Controller
 */
class WalmartproductdetailController extends WalmartmainController
{

    public function actionIndex()
    {
        if (!isset($_GET['error'])) {

            $session = Yii::$app->session;
            if (isset($_GET['refresh'])) {
                unset($session[MERCHANT_ID . 'walmart_product_data']);
            }
            $report_data = $this->getReportData();

            $data = array_chunk($report_data, 100);

            $session_data = $session->get(MERCHANT_ID . 'walmart_product_data');
            if (!isset($session_data) && empty($session_data)) {
                $session->set(MERCHANT_ID . 'walmart_product_page', count($data));
                $session->set(MERCHANT_ID . 'walmart_product_data', $data);
                return $this->render('matchcatalog',
                    [
                        'totalcount' => count($report_data),
                        'pages' => count($data)
                    ]
                );
            } else {
                $item = $session_data;
            }
            $items = [];
            foreach ($item as $val) {
                $items = array_merge($items, $val);
            }

            $searchAttributes = ['SKU', 'PRIMARY IMAGE URL', 'PRODUCT NAME', 'PRODUCT CATEGORY', 'PRICE', 'INVENTORY COUNT', 'PUBLISH STATUS', 'MATCHED SKUS', 'UPC'];
            $searchModel = [];
            $searchColumns = [];

            $searchColumns[] = ['class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($searchAttribute) {
                    return ['value' => $searchAttribute['SKU'], 'class' => 'bulk_checkbox'];
                },
                'headerOptions' => ['id' => 'checkbox_header']
            ];

            foreach ($searchAttributes as $searchAttribute) {
                $filterName = $searchAttribute;

                $filterValue = Yii::$app->request->getQueryParam(str_replace(' ', '_', $filterName), '');
                $searchModel[$searchAttribute] = $filterValue;
                if ($searchAttribute == 'PRIMARY IMAGE URL') {
                    $searchColumns[] = [
                        'attribute' => $searchAttribute,
                        'filter' => '<input class="form-control" name="' . str_replace(' ', '_', $filterName) . '" value="' . $filterValue . '" type="text">',
                        'format' => 'html',
                        'label' => 'PRIMARY IMAGE URL',
                        'value' => function ($searchAttribute) {
                            if (count(explode(',', $searchAttribute['PRIMARY IMAGE URL'])) > 0) {
                                $images = [];
                                $images = explode(',', $searchAttribute['PRIMARY IMAGE URL']);
                                return Html::img($images[0],
                                    ['width' => '100px', 'height' => '100px']);
                            } else {
                                return Html::img($searchAttribute['jet_product']['image'],
                                    ['width' => '100px', 'height' => '100px']);
                            }
                        }
                    ];
                } else {
                    $searchColumns[] = [
                        'attribute' => $searchAttribute,
                        'filter' => '<input class="form-control" name="' . str_replace(' ', '_', $filterName) . '" value="' . $filterValue . '" type="text">',
                        'value' => $searchAttribute,
                    ];
                }

                $filterTypes = ['SKU' => 'pattern', 'PRODUCT NAME' => 'pattern', 'PRODUCT CATEGORY' => 'pattern', 'PRICE' => 'equal', 'PUBLISH STATUS' => 'equal', 'INVENTORY COUNT' => 'equal', 'UPC' => 'equal'];

                $items = array_filter($items, function ($item) use (&$filterValue, &$searchAttribute, &$filterTypes) {

                    if (in_array($searchAttribute, $filterTypes) && $filterTypes[$searchAttribute] == 'pattern') {
                        return strlen($filterValue) > 0 ? stripos('/^' . strtolower($item[$searchAttribute]) . '/', strtolower($filterValue)) : true;
                    } else {
                        return strlen($filterValue) > 0 ? $item[$searchAttribute] == $filterValue : true;

                    }
                });
            }

        } else {
            Yii::$app->session->setFlash('error', "File Not Found Or Invalid API Credentials..");

            $items = [];
            $searchAttributes = ['SKU', 'PRIMARY IMAGE URL', 'PRODUCT NAME', 'PRODUCT CATEGORY', 'PRICE', 'INVENTORY COUNT', 'PUBLISH STATUS', 'UPC'];
            $searchModel = [];
            $searchColumns = [];
        }

        return $this->render('index', ['items' => $items, 'searchAttributes' => $searchAttributes, 'searchModel' => $searchModel, 'searchColumns' => $searchColumns]);

    }

    public function getReportData($sku = '')
    {

        $walmartReport = new WalmartReport(API_USER, API_PASSWORD);
        $csvFilePath = $walmartReport->downloadItemReport(MERCHANT_ID);
//        $rows = array_map('str_getcsv', file('/home/cedcoss/Downloads/Test.csv'));

        if (!$csvFilePath) {
            return $this->redirect('index?error=true');
        }

        $rows = array_map('str_getcsv', file($csvFilePath));
        $header = array_shift($rows);
        $items = array();

        foreach ($rows as $row) {

            $items[] = array_combine($header, $row);
        }
        if ($sku) {
            foreach ($items as $item) {
                if ($item['SKU'] == $sku) {
                    return $item;
                }
            }
        }

        return $items;
    }

    public function actionViewproduct()
    {
        $this->layout = 'main2';
        $html = '';
        $partner_id = Yii::$app->request->post('id');
        if ($partner_id) {
            $item = self::getReportData($partner_id);
        }
        $html = $this->render('view', array('data' => $item), true);
        return $html;

    }

    public function actionMatchcatalog()
    {
        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index');

        $data = Yii::$app->session->get(MERCHANT_ID . 'walmart_product_data');
        $allProductSku = WalmartProductComponent::getAllProductSku(MERCHANT_ID);

        if ($data[$index]) {
            foreach ($data[$index] as $key => $item) {

                if (in_array($item['SKU'], $allProductSku)) {
                    unset($_SESSION[MERCHANT_ID . 'walmart_product_data'][$index][$key]);
                } else {
                    $barcode = ltrim($item['UPC'],'0');
                    $query = "SELECT `sku` FROM `walmart_product` INNER JOIN `jet_product` ON `walmart_product`.`product_id`=`jet_product`.`id` WHERE `walmart_product`.`merchant_id`=" . MERCHANT_ID . " AND `jet_product`.upc LIKE '%".$barcode."%'";

                    $wal_result = Data::sqlRecords($query, 'column', 'all');
                    if ($wal_result) {
                        $_SESSION[MERCHANT_ID . 'walmart_product_data'][$index][$key]['MATCHED SKUS'] = implode(',', $wal_result);
                    } else {
                        $query = "SELECT `jet_product_variants`.`option_sku` AS `sku` FROM `walmart_product_variants`  INNER JOIN `jet_product_variants` ON `walmart_product_variants`.`option_id`=`jet_product_variants`.`option_id` WHERE `walmart_product_variants`.`merchant_id`='" . MERCHANT_ID . "' AND `jet_product_variants`.option_unique_id LIKE '%".$barcode."%'";

                        $wal_variant_result = Data::sqlRecords($query, 'column', 'all');
                        if ($wal_variant_result) {
                            $_SESSION[MERCHANT_ID . 'walmart_product_data'][$index][$key]['MATCHED SKUS'] = implode(',', $wal_variant_result);
                        }

                    }
                }
            }
        }

        $returnarr['success'] = "Product Information has been updated successfully";
        //$returnArr['count'] = count($data[$index]);
        $returnarr['count'] = count($_SESSION[MERCHANT_ID . 'walmart_product_data'][$index]);

        return json_encode($returnarr);

    }

    public function actionRetireproduct()
    {

        $skus = Yii::$app->request->post('id');
        $retunArr = [];
        $errors = '';
        $success = '';

        $retireProduct = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
        $feed_data = $retireProduct->retireProduct($skus);

        if (isset($feed_data['ItemRetireResponse'])) {
            $success = '<b>' . $feed_data['ItemRetireResponse']['sku'] . ' : </b>' . $feed_data['ItemRetireResponse']['message'];
        } elseif (isset($feed_data['errors']['error'])) {
            if (isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku") {
                $errors = $skus . ' : Product not Uploaded on Walmart.';
            } else {
                $errors = $skus . ' : ' . $feed_data['errors']['error']['description'];
            }
        } else {
            $success = 'product successfully updated';
        }
        if ($success) {
            $retunArr['success'] = $success;
        }
        if ($errors) {
            $retunArr['error'] = $success;
        }
        return json_encode($retunArr);

    }

    public function actionAjaxBulkUpdate()
    {
        if (Yii::$app->user->isGuest)
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));

        $session = Yii::$app->session;

        if (isset($_GET['retire']) && $_GET['retire'] == 'all') {
            $action = 'retireproduct';
            $session_data = $session->get(MERCHANT_ID . 'walmart_product_data');
            $selection = [];
            $merged_data = [];
            foreach ($session_data as $val) {
                $merged_data = array_merge($merged_data, $val);
            }
            foreach ($merged_data as $value) {
                $selection[] = $value['SKU'];
            }
            $selections = array_chunk($selection, 100);

            $Productcount = count($selection);
            $pages = (int)(ceil(count($selection) / 100));

        } elseif (isset($_POST['action']) && empty($_POST['action']) && isset($_POST['update_inventory']) && !empty($_POST['update_inventory'])) {

            $action = 'update_inventory';
            $session_data = $session->get(MERCHANT_ID . 'walmart_product_data');
            $selection = [];
            $merged_data = [];
            foreach ($session_data as $val) {
                $merged_data = array_merge($merged_data, $val);
            }
            foreach ($merged_data as $value) {
                $selection[] = $value['SKU'];
            }
            $selections = array_chunk($selection, 500);

            $Productcount = count($selection);
            $pages = (int)(ceil(count($selection) / 500));
        } else {
            $action = Yii::$app->request->post('action');
            $selections[] = (array)Yii::$app->request->post('selection');
            $Productcount = count($selections[0]);
            $pages = count($selections);

            if ($Productcount == 0) {
                Yii::$app->session->setFlash('error', "No Product selected...");
                return $this->redirect(['index']);
            }
        }
        if ($action == 'update_inventory') {

            $prods_inventory = Yii::$app->request->post('update_inventory');

            $session->set('update_inventory', $selections);
            $session->set('prods_inventory', $prods_inventory);

            return $this->render('updateinventory', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);

        }
        if ($action == 'retireproduct') {

            $session->set('retire_product', $selections);

            return $this->render('retireproduct', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);

        }

        $session->close();
    }

    public function actionBatchretire()
    {
        $session = Yii::$app->session;

        $selection = isset($session['retire_product']) ? $session['retire_product'] : [];

        $index = Yii::$app->request->post('index');

        if (!empty($selection)) {
            $skus = $selection[$index];

            $errors = [];
            $success = [];
            foreach ($skus as $sku) {

                $retireProduct = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                $feed_data = $retireProduct->retireProduct($sku);

                if (isset($feed_data['ItemRetireResponse'])) {
                    $success[] = '<b>' . $feed_data['ItemRetireResponse']['sku'] . ' : </b>' . $feed_data['ItemRetireResponse']['message'];
                } elseif (isset($feed_data['errors']['error'])) {
                    if (isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku") {
                        $errors[] = $sku . ' : Product not Uploaded on Walmart.';
                    } else {
                        $errors[] = $sku . ' : ' . $feed_data['errors']['error']['description'];
                    }
                } else {
                    $success[] = $sku . ' : Product not found';
                }
                if (count($errors)) {
                    $returnArr['error'] = true;
                    $returnArr['error_msg'] = implode('<br/>', $errors);
                }
                if (count($success)) {
                    $returnArr['success'] = true;
                    $returnArr['success_count'] = count($success);
                    $returnArr['success_msg'] = implode('<br/>', $success);
                }

            }

        } else {
            $returnArr = ['error' => 'Product Sku :Not Found'];
        }

        return json_encode($returnArr);
    }

    public function actionUpdateinventory()
    {
        $session = Yii::$app->session;

        $selection = isset($session['update_inventory']) ? $session['update_inventory'] : [];

        $index = Yii::$app->request->post('index');
        $inventory = $session['prods_inventory'];

        if (!empty($selection)) {
            $skus = $selection[$index];

            $errors = [];
            $success = [];
            $response = self::updateInventory($skus, $inventory);

            if (isset($response['errors'])) {
                if (isset($response['errors']['error'])) {
                    $returnArr['error'] = $response['errors']['error']['code'];
                } else {
                    $returnArr['error'] = "Inventory of Products is not updated due to some error.";
                }
            } elseif (isset($response['error'])) {
                if (isset($response['error'][0]['code'])) {
                    $returnArr['error'] = $response['error'][0]['code'];
                } else {
                    $returnArr['error'] = "Inventory of Products is not updated due to unknown error.";
                }
            } elseif (isset($response['feedId'])) {
                $returnArr['success'] = true;
                $returnArr['success_count'] = $response['count'];
                $returnArr['success_msg'] = "Inventory Feeds is successfully submitted on walmart";
            } elseif (isset($response['success'])) {

                $returnArr['success'] = true;
                $returnArr['success_count'] = $response['count'];
                $returnArr['success_msg'] = "Inventory Feeds is successfully submitted on walmart";
                $returnArr['original_reponse'] = $response['original_reponse'];
            } else {
                $returnArr['error'] = "Products is not updated.";
            }

        } else {
            $returnArr['error'] = ['Product Sku :Not Found'];
        }

        return json_encode($returnArr);
    }

    /**
     * @param array $skus
     * @param string $inventory
     * @return array
     *  POST REQUEST
     */

    /*public function updateInventory($skus = [], $inventory = '')
    {
        $merchant_id = Yii::$app->user->identity->id;

        // Set the content type to be XML, so that the browser will   recognise it as XML.
        header("content-type: application/xml; charset=ISO-8859-15");

        // "Create" the document.
        $xml = new \DOMDocument("1.0");
        // Create elements.
        $xml_inventory_feed = $xml->createElement("InventoryFeed");
        $xml_inventory_feed->setAttribute("xmlns", "http://walmart.com/");

        $xml_inventory_header = $xml->createElement("InventoryHeader");
        $xml_inventory_version = $xml->createElement("version", "1.4");

        $xml_inventory_header->appendChild($xml_inventory_version);
        $xml_inventory_feed->appendChild($xml_inventory_header);

        if (is_array($skus) && count($skus) > 0) {
            foreach ($skus as $sku) {

                $xml_inventory = $xml->createElement("inventory");
                $xml_sku = $xml->createElement("sku", $sku);
                $xml_qty = $xml->createElement("quantity");
                $xml_unit = $xml->createElement("unit", "EACH");
                $xml_amount = $xml->createElement("amount", $inventory);

                $xml_qty->appendChild($xml_unit);
                $xml_qty->appendChild($xml_amount);

                $xml_fulfillmentLagTime = $xml->createElement("fulfillmentLagTime", "1");

                $xml_inventory->appendChild($xml_sku);
                $xml_inventory->appendChild($xml_qty);
                $xml_inventory->appendChild($xml_fulfillmentLagTime);
                $xml_inventory_feed->appendChild($xml_inventory);

            }
        }

        $xml->appendChild($xml_inventory_feed);

        $generated_xml = $xml->saveXML();

        $walmartapi = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
        $response='';

        $response = $walmartapi->postRequest(Walmartapi::GET_FEEDS_INVENTORY_SUB_URL, ['data' => $generated_xml]);

        $responseArray = Walmartapi::xmlToArray($response);
        if (isset($responseArray['FeedAcknowledgement'])) {

            if (isset($responseArray['FeedAcknowledgement']['feedId'])) {
                return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'count' => count($skus)];

            }
        } elseif (isset($responseArray['errors'])) {
            return ['errors' => $responseArray['errors']];
        } else {
            return ['errors' => $responseArray];
        }
    }*/
    /*public function updateInventory($skus = [], $inventory = '')
    {
        $merchant_id = Yii::$app->user->identity->id;
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
        $key = 0;

        foreach ($skus as $sku) {
            $key += 1;
            $inventoryArray['InventoryFeed']['_value'][$key] = [
                'inventory' => [
                    'sku' => $sku,
                    'quantity' => [
                        'unit' => 'EACH',
                        'amount' => $inventory,
                    ],
                    'fulfillmentLagTime' => '1',
                ]
            ];
        }

        $path = 'product/update-tet/' . date('d-m-Y') . '/' . $merchant_id . '/inventory';

        $dir = \Yii::getAlias('@webroot') . '/var/' . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        $file = $dir . '/MPProduct-' . time() . '.xml';
        $xml = new Generator();
        $xml->arrayToXml($inventoryArray)->save($file);

        $walmartapi = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
        $response = '';

        $response = $walmartapi->postRequest(Walmartapi::GET_FEEDS_INVENTORY_SUB_URL, ['file' => $file]);

        $responseArray = Walmartapi::xmlToArray($response);
        if (isset($responseArray['FeedAcknowledgement'])) {

            if (isset($responseArray['FeedAcknowledgement']['feedId'])) {
                return ['feedId' => $responseArray['FeedAcknowledgement']['feedId'], 'count' => count($skus)];

            }
        } elseif (isset($responseArray['errors'])) {
            return ['errors' => $responseArray['errors']];
        } else {
            return ['errors' => $responseArray];
        }
    }*/

    /**
     * @param array $skus
     * @param string $inventory
     * @return array
     * PUT INVENTORY
     */
    public function updateInventory($skus = [], $inventory = '')
    {
        $merchant_id = Yii::$app->user->identity->id;
        $returnArr = [];
        $count = 0;

        if (is_array($skus) && count($skus) > 0) {
            foreach ($skus as $sku) {

                // Set the content type to be XML, so that the browser will   recognise it as XML.
                header("content-type: application/xml; charset=ISO-8859-15");

                // "Create" the document.
                $xml = new \DOMDocument("1.0");
                // Create elements.

                $xml_inventory = $xml->createElement("wm:inventory");
                $xml_inventory->setAttribute("xmlns:wm", "http://walmart.com/");

                $xml_sku = $xml->createElement("wm:sku", $sku);
                $xml_qty = $xml->createElement("wm:quantity");
                $xml_unit = $xml->createElement("wm:unit", "EACH");
                $xml_amount = $xml->createElement("wm:amount", $inventory);

                $xml_qty->appendChild($xml_unit);
                $xml_qty->appendChild($xml_amount);

                $xml_fulfillmentLagTime = $xml->createElement("wm:fulfillmentLagTime", "1");

                $xml_inventory->appendChild($xml_sku);
                $xml_inventory->appendChild($xml_qty);
                $xml_inventory->appendChild($xml_fulfillmentLagTime);


                $xml->appendChild($xml_inventory);

                $generated_xml = $xml->saveXML();

                $walmartapi = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                $url = 'v2/inventory?sku=' . $sku;
                $count++;


                $response = $walmartapi->putRequest($url, ['data' => $generated_xml]);
                $responseArray = Walmartapi::xmlToArray($response, true);
                if (isset($responseArray['ns2:errors'])) {
                    $responseArray = Walmartapi::xmlToArray($response);
                }
                if (isset($responseArray['inventory']['sku'])) {
                    $respArr[] = $responseArray;
                    $returnArr = ['success' => true, 'message' => 'Inventory Feeds is successfully submitted on walmart', 'original_reponse' => $respArr];
                } elseif (isset($responseArray['errors'])) {
                    $returnArr = ['errors' => $responseArray['errors']];
                }
            }
            $returnArr['count'] = $count;

            return $returnArr;
        }
    }

}
