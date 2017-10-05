<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/4/17
 * Time: 11:36 AM
 */
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Attributemap;
use frontend\modules\walmart\components\Data;

use frontend\modules\walmart\components\Mail;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Skuproductupload;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Walmartcategory;
//use frontend\modules\walmart\components\WalmartProduct;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\models\WalmartProduct;

use Yii;
use yii\base\Exception;

class WalmartcustomworkController extends WalmartmainController
{
    const SIZE_OF_REQUEST = 1000;
    const UPLOAD_SIZE = 250;

    /*public function beforeAction($action)
    {

        $shopname = Yii::$app->user->identity->username;
        $token = Yii::$app->user->identity->auth_key;

        $this->sc = new ShopifyClientHelper($shopname, $token, PUBLIC_KEY, PRIVATE_KEY);
        return true;
    }*/

    public function actionGetproductreport()
    {
        $reprice = new WalmartRepricing(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
        $reprice->fetchWalmartProductReport(null, true);

    }

    public function actionGetbuyboxreport()
    {
        $reprice = new WalmartRepricing(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
        $reprice->fetchWalmartBuyboxReport(null, true);
    }

    public function actionTestupc()
    {
        $product['upc'] = $_GET['upc'];
        $value = Data::validateUpc($product['upc']);

        var_dump($product['upc']);
        var_dump($value);
        die('sdsdsd');
    }

    public function actionMatchcatalog()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $import_errors = array();
        $error_array = array();
        $count = 0;

        $file_path = Yii::getAlias('@webroot') . '/var/ItemReport_10000002750_2017-04-18T171007.2990000.csv';
        if (file_exists($file_path)) {

            $row = 0;

            if (($handle = fopen($file_path, "r"))) {
                $allSku = WalmartProduct::getAllProductSku($merchant_id);
                $row = 0;

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {

                    $row++;
                    if ($row == 1) {

                        $header = $data;
                        continue;
                    }

                    $pro_sku = trim($data[1]);

                    if (!in_array($pro_sku, $allSku)) {

                        $error_array[] = $data;

                        $import_errors[] = array_combine($header, $data);
                        $count++;
                    }
                }

            }

        } else {
            echo 'file not found';
        }

        /*if(!empty($error_array)){

            if (!file_exists(\Yii::getAlias('@webroot') . '/var/')) {
                mkdir(\Yii::getAlias('@webroot') . '/var/', 0775, true);
            }
            $base_path = \Yii::getAlias('@webroot') . '/var/'.$merchant_id.'.csv';
            $file = fopen($base_path, "w");

            $row = array();
            foreach ($header as $head) {
                $row[] = $head;
            }
            fputcsv($file, $row);

            foreach ($error_array as $v) {

                fputcsv($file, $v);
            }

            fclose($file);
            $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
            $content = $encode . file_get_contents($base_path);
            return \Yii::$app->response->sendFile($base_path);
        }*/
        echo '<pre>';
        print_r($count);
        print_r($import_errors);
        die('success');
    }

    public function actionCheckPayment()
    {
        if (isset($_GET['mid'])) {
            $shopData = Data::getWalmartShopDetails($_GET['mid']);
            if (isset($shopData['shop_url'])) {
                $sc = new ShopifyClientHelper($shopData['shop_url'], $shopData['token'], WALMART_APP_KEY, WALMART_APP_SECRET);
                $response = $sc->call('GET', '/admin/application_charges.json');
                echo "pre";
                print_r($response);
                die("cvb");
            }
        }
    }

    public function actionGetfeedstatus()
    {
        if (isset($_GET['feed']) && !empty($_GET['feed'])) {
            print_r($_GET['feed']);
            $walmartapi = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
            $result = $walmartapi->getFeeds($_GET['feed']);
            var_dump($result);
            die('success');

        }
    }

    public function actionGetproduct()
    {
        $shopname = Yii::$app->user->identity->username;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        $prod_id = $_GET['id'];
        $countProducts = $sc->call('GET', '/admin/products/' . $prod_id . '.json');
        echo "<pre>";
        print_r($countProducts);
        die;
    }

    public function actionGetallproduct()
    {
        $index = $_GET['index'];
        $shopname = Yii::$app->user->identity->username;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        $prod_id = $_GET['id'];
        $countProducts = $sc->call('GET', '/admin/products.json', array('limit' => 250, 'page' => $index));
        echo "<pre>";
        print_r($countProducts);
        die;
    }

    public function actionGetwebhook()
    {
        $shopname = Yii::$app->user->identity->username;
        //$token = Yii::$app->user->identity->auth_key;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        // var_dump(TOKEN);die;
        $webhook = $sc->call('GET', '/admin/webhooks.json');
        print_r($webhook);
        die('webhook');
    }

    public function actionUpdateShopifyWebhook()
    {
        $webhookUrls = [
            "https://shopify.cedcommerce.com/jet/shopifywebhook/productupdate",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/productdelete",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/isinstall",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/cancelled",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/productcreate",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/createshipment",
            "https://shopify.cedcommerce.com/jet/shopifywebhook/createorder",
        ];

        $merchant_data = Data::sqlRecords("SELECT * FROM walmart_shop_details WHERE status=1", 'all');

        foreach ($merchant_data as $value) {
            $shopname = $value['shop_url'];
            $sc = new ShopifyClientHelper($shopname, $value['token'], PUBLIC_KEY, PRIVATE_KEY);

            echo $shopname . PHP_EOL;

            $webhooks = $sc->call('GET', '/admin/webhooks.json');

            echo PHP_EOL . 'Before Update';
            echo '<pre>';
            print_r($webhooks);
            echo '</pre>';

            if (!isset($webhooks['errors'])) {
                foreach ($webhooks as $key => $val) {
                    if (in_array($val['address'], $webhookUrls)) {
                        $change_webhook_data['webhook'] = [
                            'id' => $val['id'],
                            'address' => str_replace("jet", "integration", $val['address'])
                        ];
                        $response = $sc->call("PUT", "/admin/webhooks/" . $val['id'] . ".json", $change_webhook_data);
                    }
                }
            }
            echo 'After Udpate';
            echo '<pre>';
            print_r($webhooks);
            echo '</pre>';

            echo '<hr>';

        }
        die('webhook');
    }

    public function actionDeletewebhook()
    {
        $shopname = Yii::$app->user->identity->username;

        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        $webhooks = $sc->call('GET', '/admin/webhooks.json');
        if (!isset($webhooks['errors'])) {
            foreach ($webhooks as $key => $value) {
                //$sc->call('DELETE','/admin/webhooks/'.$value['id'].'.json');
            }
        }
    }

    public function actionUpdatewebhook()
    {
        $shopname = Yii::$app->user->identity->username;

        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        /*$urls = [
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/productupdate",
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/productdelete",
            "https://dev.shopify.cedcommerce.com/integration/walmartwebhook/isinstall",
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/createshipment",
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/cancelled",
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/productcreate",
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/createshipment",
            "https://dev.shopify.cedcommerce.com/jet/shopifywebhook/createorder"
        ];*/
        /*$webhooks=$sc->call('GET','/admin/webhooks.json');
        if(!isset($webhooks['errors']))
        {
            foreach ($webhooks as $key => $value)
            {*/
        $url = "https://shopify.cedcommerce.com/integration/shopifywebhook/createorder";
        $charge = ['webhook' => ['id' => '336555268', 'address' => $url]];
        $response = $sc->call('PUT', '/admin/webhooks/336555268.json', $charge);
        var_dump($response);
        die('dfg');
        /* }
     }*/

    }

    public function actionViewfeed()
    {

        if (isset($_GET['feed']) && !empty($_GET['feed'])) {
            $id = $_GET['feed'];
            $limit = 50;

            if (!empty($id)) {
                $wal = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                $feed_data = $wal->viewFeed($id, $limit);

                print_r($feed_data);
                die('success');
            }
        }
    }

    public function actionCommonattributeinsert()
    {

        //{"color->colorValue":"Gray"}
        $connection = Yii::$app->getDb();
        $query = "SELECT title,id,sku from `jet_product` where merchant_id='932'";
        $optionResult = Data::sqlRecords($query);
        foreach ($optionResult as $key => $value) {
            $data = Data::sqlRecords("SELECT common_attributes from `walmart_product` where merchant_id='932' AND product_id='" . $value['id'] . "'", 'one');
            $array = [];
            if (is_null($data['common_attributes'])) {
                $input = explode('Color', $value['title']);
                if (isset($input[1]) && !empty($input[1])) {
                    $array['color->colorValue'] = $input[1];
                    $query = "UPDATE `walmart_product` SET `common_attributes`='" . addslashes(json_encode($array)) . "' WHERE merchant_id='932' AND product_id='" . $value['id'] . "'";
                    $connection->createCommand($query)->execute();
                } else {
                    $array['color->colorValue'] = 'multicolor';
                    $query = "UPDATE `walmart_product` SET `common_attributes`='" . addslashes(json_encode($array)) . "' WHERE merchant_id='932' AND product_id='" . $value['id'] . "'";
                    $connection->createCommand($query)->execute();
                }

            }
        }
    }

    public function actionRequiredAttributeInsert()
    {

        //{"color->colorValue":"Gray"}
        $connection = Yii::$app->getDb();
        $query = "SELECT title from `jet_product` where merchant_id='1253'";
        $optionResult = Data::sqlRecords($query);
        foreach ($optionResult as $key => $value) {
            if (stristr($value['title'], ' men')) {

            } elseif (stristr($value['title'], ' women')) {

            }
        }
    }

    /*
    * get Walmart order info 
    */
    public function actionWalmartorderinfo()
    {
        $status = false;
        if (isset($_GET['status'])) {
            $status = $_GET['status'];
        }
        $merchant_id = MERCHANT_ID;
        $test = false;
        $prev_date = date('Y-m-d', strtotime(date('Y-m-d') . ' -2 month'));
        $config = Data::getConfiguration($merchant_id);
        $walmartHelper = new Walmartapi($config['consumer_id'], $config['secret_key']);
        if ($status) {
            $orderdata = $walmartHelper->getOrders(['status' => $status, 'limit' => '100', 'createdStartDate' => $prev_date], Walmartapi::GET_ORDERS_SUB_URL, $test);
        } else {
            $orderdata = $walmartHelper->getOrders(['limit' => '100', 'createdStartDate' => $prev_date], Walmartapi::GET_ORDERS_SUB_URL, $test);
        }

        print_r($orderdata);
        die;
    }

    /*
    * get Walmart product info 
    */
    public function actionWalmartproductinfo()
    {
        $sku = false;
        $productdata = "add sku on url like ?sku=product_sku";
        if (isset($_GET['sku'])) {
            $sku = $_GET['sku'];
        }
        $merchant_id = MERCHANT_ID;
        $config = Data::getConfiguration($merchant_id);
        $walmartHelper = new Walmartapi($config['consumer_id'], $config['secret_key']);
        if ($sku) {
            $productdata = $walmartHelper->getItem($sku);
        }
        print_r($productdata);
        die;
    }

    /*
    * get Walmart product info 
    */
    public function actionGetpromotion()
    {
        $sku = false;
        $productdata = "add sku on url like ?sku=product_sku";
        if (isset($_GET['sku'])) {
            $sku = $_GET['sku'];
        }
        $merchant_id = '323';
        //$config = Data::getConfiguration($merchant_id);
        $walmartHelper = new Walmartapi('899c9675-6302-43f2-9420-e8f55b176671', 'MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAJf0uSx+vD19j4ROQnXj+3nSnwIsSnoRB5VAhDuaiLLDWKQvFbwWB4cAaZNX9fgdyT5KEuiW5QpXNV6ui8B6Ex/lgdlaXaaANnEd8FPjcct4WuGM3JaurUyQj24b7S1eCAPwoJqPyJPjpCQ5OYGgGvi4mHFmawq4rOhgsVT9upIdAgMBAAECgYAxGWob7nd0hvWwknj3Dsta+atXUGhgONBycX5IpA43dNdXdb9YHuYfwQpcCbf4i+dSsSya6ubnCHa+OTf+4XL8A4DnQojj3sfXx6B/N8k4eNHZ8wpbDQAceA+gdw2n466iKfW76cdXiL+2DUhS6qNtvaQ72r5/2KBRgidr7NR7AQJBANdXMA7oOjZ0lOPJCQsSn9nzQqYJMSqN1EvpFMlNX63VnUDlj1kV1MbWbo+sN9AZylrtjcg0YcZenI3bHCvF1QkCQQC0pb5u81zqKOCV7leboWp1LUTmMYWJNeO26lT8knhGN42O2W/+qXI5cJbY3USN32xxX4gK/MtKUBuhZ7Ow+M11AkAUkoLH2c296BNVU55mjWfyFXhXjmdBDn2qpuDSfm7Wl6LHUWcJdrl2KYQ0e5p1ahFX8HvsFX0Fy4IfV0BwuhypAkBQbqvHwtvP9rtohmLDjK9V1P4kcFBAs5ncS6Hjg2PB/+IrhGz1OoT9RkAj9wEbGiuynxJ3se7h+6ER0JaVaXIxAkBNi7N5cfePcyPfXS89NpTtByi+Yg4QQthv9RvCM/TH6yLpMWBWprRq6ufAMl4NHrFXWZRsA7wUqMYHj8+vB18I', '7b2c8dab-c79c-4cee-97fb-0ac399e17ade');
        if ($sku) {
            $productdata = $walmartHelper->getRequest('v3/promo/sku/CE-0000-0149');
        }
        print_r($productdata);
        die;
    }

    /*
    * get Walmart product info 
    */
    public function actionWalmartproductinventoryinfo()
    {
        $sku = false;
        $productdata = "add sku on url like ?sku=product_sku";
        if (isset($_GET['sku'])) {
            $sku = $_GET['sku'];
        }
        $merchant_id = MERCHANT_ID;
        $config = Data::getConfiguration($merchant_id);
        $walmartHelper = new Walmartapi($config['consumer_id'], $config['secret_key']);
        if ($sku) {
            $productdata = $walmartHelper->getInventory($sku);
        }

        print_r($productdata);
        die;
    }

    public function actionGetwalmartproductstatus()
    {
        if (isset($_GET['sku']) && !empty($_GET['sku'])) {
            $sku = $_GET['sku'];
            $limit = 50;

            if (!empty($sku)) {
                $wal = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                $feed_data = $wal->getItem($sku);


                print_r($feed_data);
                die('success');
            }
        }
    }

    /*public function actionGetallproduct()
    {
        $index = $_GET['index'];
        $shopname = Yii::$app->user->identity->username;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);

        $prod_id=$_GET['id'];
        $countProducts=$sc->call('GET', '/admin/products.json', array('limit' => 250, 'page' => $index));
        echo "<pre>";
        print_r(json_encode($countProducts));
        die ;
    }*/

    public function actionUpdatestatus()
    {
        die('status');
        $data = Data::sqlRecords("SELECT * FROM `product_import_error` WHERE `merchant_id` = 397 AND `missing_value` LIKE '%hidden%' ", 'all');
        $count = 0;
        foreach ($data as $value) {
            $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = 397 AND `sku` ='" . $value['sku'] . "' ", 'one');

            if ($model1) {
                $wal_model = Data::sqlRecords("SELECT `id` FROM `walmart_product` WHERE `merchant_id` = 397 AND `product_id` =" . $model1['id'] . " ", 'one');

                if ($wal_model) {
                    try {

                        Data::sqlRecords("UPDATE `walmart_product` SET `status`='DELETED' WHERE `merchant_id`=397 AND `product_id`=" . $wal_model['id'] . "", null, 'update');

                        echo '<pre>';
                        echo $value['sku'] . ' success in walmart product';

                    } catch (Exception $e) {
                        echo 'error' . $e->getMessage();
                    }
                }
            }

            $model2 = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE `merchant_id` = 397 AND `option_sku` ='" . $value['sku'] . "' ", 'one');
            if ($model2) {
                $wal_variants_model = Data::sqlRecords("SELECT `option_id` FROM `walmart_product_variants` WHERE `merchant_id` = 397 AND `option_id` =" . $model2['option_id'] . " ", 'one');

                if ($wal_variants_model) {
                    try {

                        Data::sqlRecords("UPDATE `walmart_product_variants` SET `status`='DELETED'  WHERE `merchant_id`=397 AND `option_id`=" . $wal_variants_model['option_id'] . " ", null, 'update');

                        echo '<pre>';
                        echo $value['sku'] . ' success in walmart product variants';

                    } catch (Exception $e) {
                        echo 'error' . $e->getMessage();
                    }
                }
            }
            $count++;
        }

        echo $count;
    }

    public function actionGetshopifyorder()
    {
        $shopname = Yii::$app->user->identity->username;
        $sc = new ShopifyClientHelper($shopname, TOKEN, PUBLIC_KEY, PRIVATE_KEY);
        $shopify_order_id = $_GET['id'];
        $response = $sc->call('GET', '/admin/orders/' . $shopify_order_id . '.json');
        print_r($response);
        die;
    }

    public function actionRefundprocess()
    {

        if (isset($_GET['id']) && isset($_GET['app']) && !empty($_GET['id']) && !empty($_GET['app'])) {
            $merchant_id = $_GET['id'];
            $app = $_GET['app'];
            if ($app == 'walmart') {
                $table = 'walmart_recurring_payment';
                $shop_data_table = 'walmart_shop_details';
                $extension_detail = 'walmart_extension_detail';
                $status = 'status';
            } elseif ($app == 'jet') {
                $table = 'jet_recurring_payment';
                $shop_data_table = 'jet_shop_details';
                $extension_detail = 'jet_extension_detail';
                $status = 'status';

            } elseif ($app == 'newegg') {
                $table = 'newegg_payment';
                $shop_data_table = 'newegg_shop_detail';
                $extension_detail = 'newegg_shop_detail';
                $status = 'purchase_status';

            } else {
                echo 'Sorry !!! App does not exist :(';
                die;
            }

            $charge_data = Data::sqlRecords('SELECT * FROM ' . $table . ' WHERE `merchant_id` = "' . $merchant_id . '"  ORDER BY billing_on DESC ', 'one');

            if ($charge_data) {

                $charge_date = $charge_data['billing_on'];
                $current_date = date('Y-m-d', strtotime('+30 days', strtotime(date('Y-m-d'))));

                $recurring_data = json_decode($charge_data['recurring_data'], true);

                $charge_id = $recurring_data['id'];

                $client_data = Data::sqlRecords('SELECT * FROM ' . $shop_data_table . ' WHERE `merchant_id`="' . $merchant_id . '"', 'one');

                if ($client_data) {

                    $shopname = $client_data['shop_url'];
                    $token = $client_data['token'];

                    $sc = new ShopifyClientHelper($shopname, $token, PUBLIC_KEY, PRIVATE_KEY);

                    $response = $sc->call('GET', '/admin/application_charges/' . $charge_id . '.json');

                    if (isset($response['errors'])) {
                        echo $response['errors'];
                        die;
                    } else {
                        $price = $response['price'];

                        $update = array(
                            'application_credit' => array(
                                "description" => 'refund for shopify-' . $app,
                                "amount" => $price,
                                "test" => "true",
                            )
                        );
                        $res = $sc->call('POST', '/admin/application_credits.json', $update);

                        /*$res = [
                            'id' => 23679,
                            'amount' => 162.00,
                            'description' => 'refund for shopify-newegg',
                            'test' => 1
                        ];*/
                        if ($res && isset($res['id'])) {
                            Data::sqlRecords('UPDATE ' . $shop_data_table . ' SET `refund_id`=' . $res['id'] . ' WHERE `merchant_id`=' . $merchant_id . ' ', null, 'update');

                            $expire_date = date('Y-m-d H:i:s');
                            $query = 'UPDATE ' . $extension_detail . ' SET ' . $status . ' = "License Expired" , expire_date=' . $expire_date . ' WHERE  `merchant_id`=' . $merchant_id;

                            Data::sqlRecords($query, null, 'update');

                            echo 'refund successfully created :)' . $app;
                            die;
                        } elseif (isset($response['errors'])) {
                            echo $response['errors'] . ':(';
                            die;
                        }

                    }
                } else {
                    echo 'Sorry !!! Shop Data not found :(';
                    die;
                }

            } else {
                echo 'Sorry !!! Recurring data not found :(';
                die;
            }
        } else {
            echo 'Sorry !!! Invalid Merchant Id Or App :(';
            die;
        }
        die('fdgdf');

    }

    public function actionReadxsd()
    {
        $xsdstring = <<<XML
<?xml version="1.0" encoding="ISO-8859-1" ?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="shiporder">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="orderperson" type="xs:string"/>
        <xs:element name="shipto">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="name" type="xs:string"/>
              <xs:element name="address" type="xs:string"/>
              <xs:element name="city" type="xs:string"/>
              <xs:element name="country" type="xs:string"/>
            </xs:sequence>
          </xs:complexType>
        </xs:element>
        <xs:element name="item" maxOccurs="unbounded">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="title" type="xs:string"/>
              <xs:element name="note" type="xs:string" minOccurs="0"/>
              <xs:element name="quantity" type="xs:positiveInteger"/>
              <xs:element name="price" type="xs:decimal"/>
            </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
      <xs:attribute name="orderid" type="xs:string" use="required"/>
    </xs:complexType>
  </xs:element>
</xs:schema>
XML;
        $doc = new \DOMDocument();
        $doc->loadXML(mb_convert_encoding($xsdstring, 'utf-8', mb_detect_encoding($xsdstring)));
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('xs', 'http://www.w3.org/2001/XMLSchema');

        function echoElements($indent, $elementDef)
        {
            global $doc, $xpath;
            echo "<div>" . $indent . $elementDef->getAttribute('name') . "</div>\n";
            $elementDefs = $xpath->evaluate("xs:complexType/xs:sequence/xs:element", $elementDef);
            foreach ($elementDefs as $elementDef) {
                echoElements($indent . "&nbsp;&nbsp;&nbsp;&nbsp;", $elementDef);
            }
        }

        $elementDefs = $xpath->evaluate("/xs:schema/xs:element");
        foreach ($elementDefs as $elementDef) {
            echoElements("", $elementDef);
        }
    }

    public function actionGetwalmartcategory()
    {
        $rootcategory = Walmartcategory::getrootcategory();

        print_r($rootcategory);
        die;
    }

    public function actionGetwalmartchildcategory()
    {
        $rootcategory = Walmartcategory::getchildcategory('Vehicle');

        print_r($rootcategory);
        die;
    }

    public function actionUpdateBarcode()
    {


        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $import_errors = array();
        $error_array = array();
        $count = 0;

        $file_path = Yii::getAlias('@webroot') . '/var/ItemReport_10000002750_2017-04-18T171007.2990000.csv';
        if (file_exists($file_path)) {

            $row = 0;

            if (($handle = fopen($file_path, "r"))) {
                $row = 0;

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {

                    $row++;
                    if ($row == 1) {

                        $header = $data;
                        continue;
                    }

                    $pro_sku = trim($data[0]);
                    $pro_barcode = trim($data[1]);

                    $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = 1116 AND `sku` ='" . $pro_sku . "' ", 'one');

                    if ($model1) {

                        Data::sqlRecords("UPDATE `jet_product` SET `upc`='" . $pro_barcode . "' WHERE `merchant_id`=1116 AND `sku`='" . $pro_sku . "'", null, 'update');

                    }

                    $model2 = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE `merchant_id` = 1116 AND `option_sku` ='" . $pro_sku . "' ", 'one');
                    if ($model2) {

                        Data::sqlRecords("UPDATE `jet_product_variants` SET `option_unique_id`='" . $pro_barcode . "'  WHERE `merchant_id`=1116 AND `option_sku`='" . $pro_sku . "' ", null, 'update');

                    }

                }

            }

        } else {
            echo 'file not found';
        }

        echo '<pre>';
        print_r($count);
        die('success');

    }

    public function actionDeletecatalog()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $import_errors = array();
        $error_array = array();
        $count = 0;

        $file_path = Yii::getAlias('@webroot') . '/var/walmart-hidden-skus-formatted.csv';
        if (file_exists($file_path)) {

            if (($handle = fopen($file_path, "r"))) {
                $row = 0;

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {

                    $row++;
                    if ($row == 1) {

                        $header = $data;
                        continue;
                    }

                    $pro_sku = trim($data[0]);

                    if ($pro_sku) {
                        $main_product = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id`=1291 AND `sku`='" . $pro_sku . "'", 'one');
                        if ($main_product) {
//                            Data::sqlRecords("DELETE * FROM `jet_product` WHERE `merchant_id`=1291 AND `id`='".$main_product['id']."'",null,'delete');

                            echo '<pre>';
                            echo $main_product['id'];
                        } else {
                            echo '<pre>';
                            echo '<hr>';
                            echo $main_product['id'];
                            echo $pro_sku;
                            echo '<hr>';

                        }
                    }
                }

            }

        } else {
            echo 'file not found';
        }
        die('done');

    }


    public function actionPromotion()
    {
        $walmartHelper = new Walmartapi('899c9675-6302-43f2-9420-e8f55b176671', 'MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAJf0uSx+vD19j4ROQnXj+3nSnwIsSnoRB5VAhDuaiLLDWKQvFbwWB4cAaZNX9fgdyT5KEuiW5QpXNV6ui8B6Ex/lgdlaXaaANnEd8FPjcct4WuGM3JaurUyQj24b7S1eCAPwoJqPyJPjpCQ5OYGgGvi4mHFmawq4rOhgsVT9upIdAgMBAAECgYAxGWob7nd0hvWwknj3Dsta+atXUGhgONBycX5IpA43dNdXdb9YHuYfwQpcCbf4i+dSsSya6ubnCHa+OTf+4XL8A4DnQojj3sfXx6B/N8k4eNHZ8wpbDQAceA+gdw2n466iKfW76cdXiL+2DUhS6qNtvaQ72r5/2KBRgidr7NR7AQJBANdXMA7oOjZ0lOPJCQsSn9nzQqYJMSqN1EvpFMlNX63VnUDlj1kV1MbWbo+sN9AZylrtjcg0YcZenI3bHCvF1QkCQQC0pb5u81zqKOCV7leboWp1LUTmMYWJNeO26lT8knhGN42O2W/+qXI5cJbY3USN32xxX4gK/MtKUBuhZ7Ow+M11AkAUkoLH2c296BNVU55mjWfyFXhXjmdBDn2qpuDSfm7Wl6LHUWcJdrl2KYQ0e5p1ahFX8HvsFX0Fy4IfV0BwuhypAkBQbqvHwtvP9rtohmLDjK9V1P4kcFBAs5ncS6Hjg2PB/+IrhGz1OoT9RkAj9wEbGiuynxJ3se7h+6ER0JaVaXIxAkBNi7N5cfePcyPfXS89NpTtByi+Yg4QQthv9RvCM/TH6yLpMWBWprRq6ufAMl4NHrFXWZRsA7wUqMYHj8+vB18I', '7b2c8dab-c79c-4cee-97fb-0ac399e17ade');

        $file = '/opt/lampp/htdocs/backup/1sep/bkp6sep/jet/jet/PromoPrice-1504786806.xml';
        $response = $walmartHelper->postRequest('v3/price?feedType=promo', ['file' => $file]);
        $responseArray = Walmartapi::xmlToArray($response);
        var_dump($responseArray);
        die;
    }

    public function actionGetBlogData()
    {
        $walamrt_cat_id = 165;
        $query = "SELECT
                    * FROM ced_posts p
                    JOIN ced_term_relationships tr ON (p.ID = tr.object_id)
                    JOIN ced_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    JOIN ced_terms t ON (tt.term_id = t.term_id)
                    WHERE p.post_type='post'
                    AND p.post_status = 'publish'
                    AND tt.taxonomy = 'category'
                    AND t.term_id = $walamrt_cat_id";
        $blogData = Yii::$app->db2->createCommand($query)->queryAll();

        echo '<pre>';
        print_r($blogData);
        die('blog');
    }

    public function actionUpdatePrice()
    {


        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $import_errors = array();
        $error_array = array();
        $count = 0;

        $file_path = Yii::getAlias('@webroot') . '/var/ItemReport_10000002750_2017-04-18T171007.2990000.csv';
        if (file_exists($file_path)) {

            $row = 0;

            if (($handle = fopen($file_path, "r"))) {
                $row = 0;

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {

                    $row++;
                    if ($row == 1) {

                        $header = $data;
                        continue;
                    }

                    $pro_sku = trim($data[0]);
                    $pro_barcode = trim($data[1]);

                    $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = 1116 AND `sku` ='" . $pro_sku . "' ", 'one');

                    if ($model1) {

                        Data::sqlRecords("UPDATE `jet_product` SET `upc`='" . $pro_barcode . "' WHERE `merchant_id`=1116 AND `sku`='" . $pro_sku . "'", null, 'update');

                    }

                    $model2 = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE `merchant_id` = 1116 AND `option_sku` ='" . $pro_sku . "' ", 'one');
                    if ($model2) {

                        Data::sqlRecords("UPDATE `jet_product_variants` SET `option_unique_id`='" . $pro_barcode . "'  WHERE `merchant_id`=1116 AND `option_sku`='" . $pro_sku . "' ", null, 'update');

                    }

                }

            }

        } else {
            echo 'file not found';
        }

        echo '<pre>';
        print_r($count);
        die('success');

    }


    public function actionTestlang()
    {
        Data::saveConfigValue('14', 'test', 'Do It  بالعربي');
        die('ddd');
    }

    public function actionReadCsv()
    {

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $import_errors = array();
        $error_array = array();
        $count = 0;
        $product = array();

        $file_path = Yii::getAlias('@webroot') . '/var/Exp.csv';

        if (!file_exists(Yii::getAlias('@webroot') . '/var/' . $merchant_id)) {
            mkdir(Yii::getAlias('@webroot') . '/var/' . $merchant_id, 0775, true);
        }

        $csv_data_file = Yii::getAlias('@webroot') . '/var/' . $merchant_id . '/data.php';

        if (file_exists($file_path)) {
            $itemCount = WalmartRepricing::getRowsInCsv($file_path);

            if ($itemCount) {
                $size_of_request = self::SIZE_OF_REQUEST;

                $pages = (int)(ceil($itemCount / $size_of_request));

                return $this->render('csv_count', [
                    'total_products' => $itemCount,
                    'pages' => $pages,
                    'csvFilePath' => $file_path,
                    'csvDataPath' => $csv_data_file
                ]);
            } else {
                Yii::$app->session->setFlash('error', 'No data found in CSV File.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'CSV File not found.');
        }
        return $this->redirect(['index']);
    }

    public function actionCountProduct()
    {
        $returnArr = [];

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index', false);
        $isLastPage = Yii::$app->request->post('isLast');

        $merchant_id = Yii::$app->user->identity->id;
        $csvFilePath = Yii::$app->request->post('csvFilePath', false);
        $csv_data_file = Yii::$app->request->post('csvDataFile', false);

        /*if (!file_exists(Yii::getAlias('@webroot') . '/var/' . $merchant_id)) {
            mkdir(Yii::getAlias('@webroot') . '/var/' . $merchant_id, 0775, true);
        }

        $csv_data_file = Yii::getAlias('@webroot') . '/var/' . $merchant_id . '/data.php';*/

        $csvData = self::readItemCsv($csvFilePath, self::SIZE_OF_REQUEST, $index, $csv_data_file);

        $content = '<?php return ' . var_export($csvData, true) . ';';
        $handle = fopen($csv_data_file, 'w');
        fwrite($handle, $content);
        fclose($handle);

        /*$createProduct = new Skuproductupload(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

        $productResponse = $createProduct->createProduct($csvData, $merchant_id);

        if (isset($productResponse['feedId'])) {
            //save product status and data feed
            foreach ($productResponse['uploadIds'] as $val) {
                $query = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', `error`='' WHERE `product_id`='{$val}'";
                Data::sqlRecords($query, null, "update");
            }

            $ids = implode(',', $productResponse['uploadIds']);
            $feed_file = $productResponse['feed_file'];
            $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('{$merchant_id}', '{$productResponse['feedId']}', '{$ids}', '{$feed_file}')";
            Data::sqlRecords($query, null, "insert");

            $msg = "product feed successfully submitted on walmart.";
            $feed_count = count($productResponse['uploadIds']);
            $feedId = $productResponse['feedId'];
            $returnArr = ['success' => true, 'success_msg' => $msg, 'success_count' => $feed_count, 'feed_id' => $feedId];
        }

        //save errors in database for each errored product
        if (isset($productResponse['errors'])) {
            if (is_array($productResponse['errors'])) {
                if (count($productResponse['errors'])) {
                    $_feedError = null;
                    if (isset($productResponse['errors']['feedError'])) {
                        $_feedError = $productResponse['errors']['feedError'];
                        unset($productResponse['errors']['feedError']);
                    }

                    $uploadErrors = [];
                    foreach ($productResponse['errors'] as $productSku => $error) {
                        if (is_array($error)) {
                            //Simple Product with xml validation error
                            if (isset($error['error'], $error['xml_validation_error'])) {
                                $errorMsg = $error['error'];
                            } //Variant product error
                            else {
                                $errorMsg = '';
                                foreach ($error as $variantSku => $variantError) {
                                    //Variant product with xml validation error
                                    if (is_array($variantError)) {
                                        $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError['error'] . '<br/>';
                                    } //Variant product with direct error message
                                    else {
                                        $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError . '<br/>';
                                    }
                                }
                            }
                            $uploadErrors[$productSku] = $error;
                        } //Simple Product with direct error message
                        else {
                            $errorMsg = $error;
                            $uploadErrors[$productSku] = $errorMsg;
                        }

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($errorMsg) . "' WHERE jp.sku='" . $productSku . "'";
                        Data::sqlRecords($query, null, "update");
                    }

                    $returnArr['error'] = true;
                    $returnArr['error_msg'] = $uploadErrors;
                    $returnArr['error_count'] = count($productResponse['errors']);
                    $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

                    if (!is_null($_feedError)) {
                        $returnArr['feedError'] = $_feedError;
                    }
                }
            } else {
                $returnArr = ['error' => true, 'error_msg' => $productResponse['errors']];
            }
        }*/
        $returnArr = ['success' => true, 'success_msg' => 'test', 'success_count' => count($csvData), 'feed_id' => 'dfdff'];
        return json_encode($returnArr);
    }

    public function readItemCsv($csvFilePath, $limit = null, $page = 0, $csv_data_file)
    {
        $product = array();
        $csvData = array();
        if (file_exists($csvFilePath)) {
            if (($handle = fopen($csvFilePath, "r"))) {
                $row = 0;

                $start = 1 + ($page * $limit);
                $end = $limit + ($page * $limit);

                if (file_exists($csv_data_file)) {
                    $csvData = include $csv_data_file;

                }

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {

                    if ($row == 0 || $data[0] == 'Sku') {
                        $row++;
                        continue;
                    }

                    if (is_null($limit)) {
                        $pro_sku = trim($data[0]);

                        $model1 = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `sku` ='" . $pro_sku . "' ", 'one');

                        if (isset($model1) && $model1['type'] == 'simple') {

                            if (!isset($csvData[$model1['id']])) {
                                $csvData[$model1['id']] = 1;

                            }/*else{
                                $csvData[$model1['id']] = 1;

                            }*/
                        }

                        $model2 = Data::sqlRecords("SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `option_sku` ='" . $pro_sku . "' ", 'one');

                        if ($model2) {

                            if (!isset($csvData[$model2['product_id']][$model2['option_id']])) {
                                $csvData[$model2['product_id']][$model2['option_id']] = 1;

                            }
                            //$csvData[$model2['product_id']][$model2['option_id']] = 1;
                        }
                    } else {
                        if ($start <= $row && $row <= $end) {
                            $pro_sku = trim($data[0]);

                            $model1 = Data::sqlRecords("SELECT `id`,`type` FROM `jet_product` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `sku` ='" . $pro_sku . "' ", 'one');

                            if (!empty($model1) && $model1['type'] == 'simple') {

                                if (!isset($csvData[$model1['id']])) {
                                    $csvData[$model1['id']] = 1;

                                }
                            }

                            $model2 = Data::sqlRecords("SELECT `product_id`,`option_id` FROM `jet_product_variants` WHERE `merchant_id` = '" . MERCHANT_ID . "' AND `option_sku` ='" . $pro_sku . "' ", 'one');

                            if ($model2) {

                                if (!isset($csvData[$model2['product_id']][$model2['option_id']])) {
                                    $csvData[$model2['product_id']][$model2['option_id']] = 1;

                                }
                            }
                        } elseif ($row > $end) {
                            break;
                        }
                    }
                    $row++;
                }
            }
        }
        return $csvData;
    }

    public function actionUploadProduct()
    {
        if (isset($_POST['csv_data_file']) && file_exists($_POST['csv_data_file'])) {
            $csvData = include $_POST['csv_data_file'];
            $Productcount = $_POST['product_count'];
            $session = Yii::$app->session;
            $selectedProducts = array_chunk($csvData, self::UPLOAD_SIZE, true);

            $session->set('selected_csv_products', $selectedProducts);
            $pages = (int)(ceil($Productcount / self::UPLOAD_SIZE));

            return $this->render('upload_csv_product', [
                'total_products' => $Productcount,
                'pages' => $pages
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'Invalid File or Empty');
        }
        return $this->redirect(['index']);

    }

    public function actionUploadCsvProduct()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $session = Yii::$app->session;

        $returnArr = [];

        $index = Yii::$app->request->post('index');
        $csvData = isset($session['selected_csv_products'][$index]) ? $session['selected_csv_products'][$index] : [];
        $count = count($csvData);

        $merchant_id = MERCHANT_ID;

        if (!$count) {
            $returnArr = ['error' => true, 'error_msg' => 'No Products to Upload'];
        } else {

            $createProduct = new Skuproductupload(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

            $productResponse = $createProduct->createProduct($csvData, $merchant_id);

            if (isset($productResponse['feedId'])) {
                //save product status and data feed
                foreach ($productResponse['uploadIds'] as $val) {
                    $query = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', `error`='' WHERE `product_id`='{$val}'";
                    Data::sqlRecords($query, null, "update");
                }

                $ids = implode(',', $productResponse['uploadIds']);
                $feed_file = $productResponse['feed_file'];
                $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('{$merchant_id}', '{$productResponse['feedId']}', '{$ids}', '{$feed_file}')";
                Data::sqlRecords($query, null, "insert");

                $msg = "product feed successfully submitted on walmart.";
                $feed_count = count($productResponse['uploadIds']);
                $feedId = $productResponse['feedId'];
                $returnArr = ['success' => true, 'success_msg' => $msg, 'success_count' => $feed_count, 'feed_id' => $feedId];
            }

            //save errors in database for each errored product
            if (isset($productResponse['errors'])) {
                if (is_array($productResponse['errors'])) {
                    if (count($productResponse['errors'])) {
                        $_feedError = null;
                        if (isset($productResponse['errors']['feedError'])) {
                            $_feedError = $productResponse['errors']['feedError'];
                            unset($productResponse['errors']['feedError']);
                        }

                        $uploadErrors = [];
                        foreach ($productResponse['errors'] as $productSku => $error) {
                            if (is_array($error)) {
                                //Simple Product with xml validation error
                                if (isset($error['error'], $error['xml_validation_error'])) {
                                    $errorMsg = $error['error'];
                                } //Variant product error
                                else {
                                    $errorMsg = '';
                                    foreach ($error as $variantSku => $variantError) {
                                        //Variant product with xml validation error
                                        if (is_array($variantError)) {
                                            $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError['error'] . '<br/>';
                                        } //Variant product with direct error message
                                        else {
                                            $errorMsg .= '<b>' . $variantSku . " : </b>" . $variantError . '<br/>';
                                        }
                                    }
                                }
                                $uploadErrors[$productSku] = $error;
                            } //Simple Product with direct error message
                            else {
                                $errorMsg = $error;
                                $uploadErrors[$productSku] = $errorMsg;
                            }

                            $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($errorMsg) . "' WHERE jp.sku='" . $productSku . "'";
                            Data::sqlRecords($query, null, "update");
                        }

                        $returnArr['error'] = true;
                        $returnArr['error_msg'] = $uploadErrors;
                        $returnArr['error_count'] = count($productResponse['errors']);
                        $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

                        if (!is_null($_feedError)) {
                            $returnArr['feedError'] = $_feedError;
                        }
                    }
                } else {
                    $returnArr = ['error' => true, 'error_msg' => $productResponse['errors']];
                }
            }
        }

        return json_encode($returnArr);
    }

    public function actionTestmail(){
        $mailData = [
            'sender' => 'shopify@cedcommerce.com',
            'reciever' => 'shivamverma8829@gmail.com',
            'email' => 'shivamverma8829@gmail.com',
            'subject' => 'trialexpire',
            'bcc' => 'shivamverma@cedcoss.com',
            'cc'=> 'shivamverma@cedcoss.com',
        ];
        $mailer = new Mail($mailData,'email/trialexpire.html','php',true);
        $mailer->sendMail();
    }

    public function actionTest()
    {
        //echo Yii::getAlias('@webroot');
        //echo Yii::$app->basePath;
        echo Yii::$app->request->baseUrl;
//        echo Yii::getAlias('@webroot');
        die('dddff');
    }
}