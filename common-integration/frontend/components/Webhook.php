<?php
namespace frontend\components;

use Yii;
use yii\base\Component;

class Webhook extends Component
{
    public static $appList = null;

    public static function initialise()
    {
        self::$appList = [
                    [
                        'app_name' => 'walmart',
                        'api_key' => WALMART_APP_KEY,
                        'api_secret' => WALMART_APP_SECRET,
                    ],
                    [
                        'app_name' => 'jet',
                        'api_key' => PUBLIC_KEY,
                        'api_secret' => PRIVATE_KEY,
                    ],
                    [
                        'app_name' => 'newegg',
                        'api_key' => NEWEGG_APP_KEY,
                        'api_secret' => NEWEGG_APP_SECRET,
                    ],
                    /*[
                        'app_name' => 'neweggca',
                        'api_key' => '',
                        'api_secret' => '',
                    ],*/
                    [
                        'app_name' => 'sears',
                        'api_key' => SEARS_APP_KEY,
                        'api_secret' => SEARS_APP_SECRET,
                    ]
                ];
    }

    public static function createWebhooks($shop)
    {
        self::initialise();

        $secureWebUrl = Yii::getAlias("@webbaseurl")."/shopifywebhook/";

        $urls = [
            $secureWebUrl . "productcreate",
            $secureWebUrl . "productupdate",
            $secureWebUrl . "productdelete",
            $secureWebUrl . "createorder",
            $secureWebUrl . "createshipment",
            $secureWebUrl . "createshipment",
            $secureWebUrl . "cancelled",
        ];

        $topics = [
            "products/create",
            "products/update",
            "products/delete",
            "orders/create",
            "orders/partially_fulfilled",
            "orders/fulfilled",
            "orders/cancelled",
        ];

        $otherWebhooks = self::getAllWebhooks($shop);

        $flag = false;
        $created = [];
        foreach (self::$appList as $key => $appKeys) 
        {
            if(isset($appKeys['install']) && $appKeys['install']) 
            {
                $token = $appKeys['token'];
                $sc = new ShopifyClientHelper($shop, $token, $appKeys['api_key'], $appKeys['api_secret']);

                if(!$flag)
                {
                    $flag = true;
                    foreach ($topics as $key => $topic) {
                        if (!in_array($urls[$key], $otherWebhooks['address']) && !in_array($topic, $otherWebhooks['topic'])) {
                            $charge = ['webhook' => ['topic' => $topic, 'address' => $urls[$key]]];
                            $created[] = $sc->call('POST', '/admin/webhooks.json', $charge);
                        }
                    }
                }

                $app = $appKeys['app_name'];
                switch ($app) {
                    case 'walmart':
                        $walmartUnInstallUrl = Yii::getAlias('@webwalmarturl') . "/walmart-webhook/isinstall";
                        $walmartUnInstallTopic = "app/uninstalled";
                        if (!in_array($walmartUnInstallUrl, $otherWebhooks['address'])) {
                            $charge = ['webhook' => ['address' => $walmartUnInstallUrl, 'topic' => $walmartUnInstallTopic]];
                            $created[] = $sc->call('POST', '/admin/webhooks.json', $charge);
                        }
                        break;

                    case 'jet':
                        $jetUnInstallUrl = Yii::getAlias('@webjeturl') . "/jetwebhook/isinstall";
                        $jetUnInstallTopic = "app/uninstalled";
                        if (!in_array($jetUnInstallUrl, $otherWebhooks['address'])) {
                            $charge = ['webhook' => ['address' => $jetUnInstallUrl, 'topic' => $jetUnInstallTopic]];
                            $created[] = $sc->call('POST', '/admin/webhooks.json', $charge);
                        }
                        break;

                    case 'newegg':
                        $neweggUnInstallUrl = Yii::getAlias('@webneweggurl') . "/newegg-webhook/isinstall";
                        $neweggUnInstallTopic = "app/uninstalled";
                        if (!in_array($neweggUnInstallUrl, $otherWebhooks['address']) ) {
                            $charge = ['webhook' => ['address' => $neweggUnInstallUrl, 'topic' => $neweggUnInstallTopic]];
                            $created[] = $sc->call('POST', '/admin/webhooks.json', $charge);
                        }
                        break;

                    /*case 'neweggca':
                        $neweggcaUnInstallUrl = Yii::getAlias('@webneweggcanadaurl') . "/newegg-webhook/isinstall";
                        $neweggcaUnInstallTopic = "app/uninstalled";
                        if (!in_array($neweggcaUnInstallUrl, $otherWebhooks['address']) && !in_array($neweggcaUnInstallTopic, $otherWebhooks['topic'])) {
                            $charge = ['webhook' => ['address' => $neweggcaUnInstallUrl, 'topic' => $neweggcaUnInstallTopic]];
                            $created[] = $sc->call('POST', '/admin/webhooks.json', $charge);
                        }
                        break;*/

                    case 'sears':
                        $searsUnInstallUrl = Yii::getAlias('@websearsurl') . "/searswebhook/isinstall";
                        $searsUnInstallTopic = "app/uninstalled";
                        if (!in_array($searsUnInstallUrl, $otherWebhooks['address'])) {
                            $charge = ['webhook' => ['address' => $searsUnInstallUrl, 'topic' => $searsUnInstallTopic]];
                            $created[] = $sc->call('POST', '/admin/webhooks.json', $charge);
                        }
                        break;
                }
            }
        }
        return ['created'=>$created, 'otherWebhooks'=>$otherWebhooks];
    }

    public static function getAllWebhooks($shop)
    {
        $webhooks = ["topic"=>[], "address"=>[]];

        foreach (self::$appList as $key => $appData) 
        {
            $app = $appData['app_name'];

            /*if($app == 'walmart') {
                continue;
            }*/

            switch ($app) {
                case 'walmart':
                    $query = "SELECT `token`, `status` FROM `walmart_shop_details` WHERE `shop_url` LIKE '{$shop}'";
                    $result = Data::sqlRecords($query, 'one');
                    if(is_array($result) && $result['status']) {
                        self::$appList[$key]['token'] = $result['token'];

                        $created = self::getCreatedWehooks($shop, $result['token'], $appData['api_key'], $appData['api_secret'], $key);
                        $webhooks["topic"] = array_merge($webhooks["topic"], $created["topic"]);
                        $webhooks["address"] = array_merge($webhooks["address"], $created["address"]);
                    }
                    break;

                case 'jet':
                    $query = "SELECT `user`.`auth_key`, `jsd`.`install_status`, `jsd`.`purchase_status` FROM `user` INNER JOIN (SELECT `install_status`, `purchase_status`, `shop_url` FROM `jet_shop_details` WHERE `shop_url` LIKE '{$shop}') `jsd` ON `user`.`username`=`jsd`.`shop_url` WHERE `username` LIKE '{$shop}'";
                    $result = Data::sqlRecords($query, 'one');
                    if(is_array($result) && $result['install_status']) {
                        self::$appList[$key]['token'] = $result['auth_key'];

                        $created = self::getCreatedWehooks($shop, $result['auth_key'], $appData['api_key'], $appData['api_secret'], $key);
                        $webhooks["topic"] = array_merge($webhooks["topic"], $created["topic"]);
                        $webhooks["address"] = array_merge($webhooks["address"], $created["address"]);
                    }
                    break;

                case 'newegg':
                    $query = "SELECT `token`, `install_status` FROM `newegg_shop_detail` WHERE `shop_url` LIKE '{$shop}'";
                    $result = Data::sqlRecords($query, 'one');
                    if(is_array($result) && $result['install_status']) {
                        self::$appList[$key]['token'] = $result['token'];

                        $created = self::getCreatedWehooks($shop, $result['token'], $appData['api_key'], $appData['api_secret'], $key);
                        $webhooks["topic"] = array_merge($webhooks["topic"], $created["topic"]);
                        $webhooks["address"] = array_merge($webhooks["address"], $created["address"]);
                    }
                    break;

                /*case 'neweggca':
                    $query = "SELECT `token`, `install_status` FROM `newegg_shop_detail_can` WHERE `shop_url` LIKE '{$shop}'";
                    $result = Data::sqlRecords($query, 'one');
                    if(is_array($result) && $result['install_status']) {
                        self::$appList[$key]['token'] = $result['token'];

                        $created = self::getCreatedWehooks($shop, $result['token'], $appData['api_key'], $appData['api_secret'], $key);
                        $webhooks["topic"] = array_merge($webhooks["topic"], $created["topic"]);
                        $webhooks["address"] = array_merge($webhooks["address"], $created["address"]);
                    }
                    break;*/

                case 'sears':
                    $query = "SELECT `token`, `status` FROM `sears_shop_details` WHERE `shop_url` LIKE '{$shop}'";
                    $result = Data::sqlRecords($query, 'one');
                    if(is_array($result) && $result['status']) {
                        self::$appList[$key]['token'] = $result['token'];

                        $created = self::getCreatedWehooks($shop, $result['token'], $appData['api_key'], $appData['api_secret'], $key);
                        $webhooks["topic"] = array_merge($webhooks["topic"], $created["topic"]);
                        $webhooks["address"] = array_merge($webhooks["address"], $created["address"]);
                    }
                    break;
            }
        }

        return $webhooks;
    }

    public static function getCreatedWehooks($shop_url, $token, $app_key, $app_secret, $key)
    {
        try {
            $webhooks = ["topic"=>[], "address"=>[]];

            $sc = new ShopifyClientHelper($shop_url, $token, $app_key, $app_secret);

            $response = $sc->call('GET', '/admin/webhooks.json');

            if (!isset($response['errors']))
            {
                self::$appList[$key]['install'] = true;

                foreach ($response as $k => $value) {
                    if (isset($value['topic'])) {
                        $webhooks['topic'][] = $value['topic'];
                        $webhooks['address'][] = $value['address'];
                    }
                }
            }
            return $webhooks;
        } catch (Exception $e) {
            //$e->getMessage();
            return [];
        }   
    }
}