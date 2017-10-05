<?php
namespace frontend\modules\neweggcanada\controllers;

use common\models\MerchantDb;
use frontend\modules\neweggcanada\components\Createwebhook;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\User;
use frontend\modules\neweggcanada\components\Helper;
use frontend\modules\neweggcanada\components\Data;
// NeweggShopDetail
use frontend\modules\neweggcanada\models\NeweggShopDetail;
use frontend\modules\neweggcanada\components\ShopifyClientHelper;
use frontend\modules\neweggcanada\controllers\NeweggMainController;
use frontend\modules\neweggcanada\components\Installation;
use frontend\components\Webhook;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /* Code For Referral Start */
    const REFERRAL_STATUS_PENDING = 'pending';
    const REFERRAL_STATUS_COMPLETE = 'complete';
    /* Code For Referral End */

    /**
     * Check request authentication
     * @return user status
     */
    /*public function beforeAction($action)
    {

        if (!Yii::$app->user->isGuest) {
            $merchant_id = Yii::$app->user->id;// current users merchant_id
            $check = Data::sqlRecords("SELECT * FROM `newegg_shop_detail` WHERE merchant_id='" . $merchant_id . "'", 'one');
            if(empty($check)){

                $this->render('index');
                return true;
            }
            return true;

        }
        else{

            $this->render('index');
            return true;
        }

    }*/
    /**
     * @inheritdoc
     */
    /*public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
                ],
            ],
        ];
    }*/

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $this->layout = 'main';
        if (isset(Yii::$app->controller->module->module->requestedRoute) && Yii::$app->controller->module->module->requestedRoute == 'neweggcanada/site/guide') {
            Yii::$app->view->registerMetaTag([
                'name' => 'title',
                'content' => 'Documentation: How to Sell on Newegg.com?'

            ], "title");

            Yii::$app->view->registerMetaTag([
                'name' => 'keywords',
                'content' => 'shopify to newegg integration, newegg shopify integration app.'

            ], "keywords");

            Yii::$app->view->registerMetaTag([
                'name' => 'description',
                'content' => 'Here\'s simple guidlines to integrate you shopify store with newegg.com Marketplace.'

            ], "description");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:title',
                'content' => 'Documentation: How to Sell on Newegg.com?'

            ], "og:title");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:type',
                'content' => 'article'

            ], "og:type");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:url',
                'content' => 'https://shopify.cedcommerce.com/integration/newegg-marketplace/sell-on-newegg/'
            ], "og:url");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:description',
                'content' => 'Here\'s simple guidlines to integrate you shopify store with newegg.com Marketplace.'
            ], "og:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:card',
                'content' => 'summary'
            ], "twitter:card");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:title',
                'content' => 'Documentation: How to Sell on Newegg.com?'
            ], "twitter:title");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:description',
                'content' => 'Here\'s simple guidlines to integrate you shopify store with newegg.com Marketplace..'
            ], "twitter:description");


            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:url',
                'content' => 'https://shopify.cedcommerce.com/integration/newegg-marketplace/sell-on-newegg/'
            ], "twitter:url");

            Yii::$app->view->registerLinkTag([
                'rel' => 'canonical',
                'href' => 'https://shopify.cedcommerce.com/integration/newegg-marketplace/sell-on-newegg/'
            ], true);

            Yii::$app->view->registerMetaTag([
                'name' => 'ROBOTS',
                'content' => 'ADD INDEX,FOLLOW'
            ], "ROBOTS");


        }else{
            Yii::$app->view->registerMetaTag([
                'name' => 'title',
                'content' => 'Shopify Newegg Integration - CedCommerce'

            ], "title");

            Yii::$app->view->registerMetaTag([
                'name' => 'keywords',
                'content' => 'shopify to newegg integration, newegg shopify integration app, shopify newegg integration, newegg marketplace integration app.'

            ], "keywords");

            Yii::$app->view->registerMetaTag([
                'name' => 'description',
                'content' => 'All in one platform for merchants to list products on Newegg marketplace and manage their inventory, order and shipping.'

            ], "description");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:title',
                'content' => 'Shopify Newegg Integration - CedCommerce'

            ], "og:title");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:type',
                'content' => 'article'

            ], "og:type");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:image',
                'content' => 'https://shopify.cedcommerce.com/integration/images/NeweggLogo1.png'

            ], "og:image");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:url',
                'content' => 'https://shopify.cedcommerce.com/integration/neweggcanada/'
            ], "og:url");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:description',
                'content' => 'All in one platform for merchants to list products on Newegg marketplace and manage their inventory, order and shipping.'
            ], "og:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:card',
                'content' => 'summary'
            ], "twitter:card");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:title',
                'content' => 'Shopify Newegg Integration - CedCommerce'
            ], "twitter:title");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:description',
                'content' => 'All in one platform for merchants to list products on Newegg marketplace and manage their inventory, order and shipping.'
            ], "twitter:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:image',
                'content' => 'https://shopify.cedcommerce.com/integration/images/NeweggLogo1.png'
            ], "twitter:image");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:url',
                'content' => 'https://shopify.cedcommerce.com/integration/neweggcanada/'
            ], "twitter:url");

            Yii::$app->view->registerLinkTag([
                'rel' => 'canonical',
                'href' => 'https://shopify.cedcommerce.com/integration/neweggcanada/'
            ], true);

            Yii::$app->view->registerMetaTag([
                'name' => 'ROBOTS',
                'content' => 'ADD INDEX,FOLLOW'
            ], "ROBOTS");
        }
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        } else {
            if (!defined('MERCHANT_ID') || Yii::$app->user->identity->id != MERCHANT_ID) {
                $merchant_id = Yii::$app->user->identity->id;

                $shopDetails = Data::getNeweggShopDetails($merchant_id);
                $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
                $email = isset($shopDetails['email']) ? $shopDetails['email'] : '';
                $currency = isset($shopDetails['currency']) ? $shopDetails['currency'] : 'USD';
                define("MERCHANT_ID", $merchant_id);
                define("SHOP", Yii::$app->user->identity->username);
                define("TOKEN", $token);
                define("CURRENCY", $currency);
                define("EMAIL", $email);

                //Save Shopify Admin Shop Details in Session
                $sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);
                $response = Data::getShopifyShopDetails($sc);
                if (!isset($response['errors'])) {
                    $session->set('shop_details', $response);
                }
                /*if ($shopDetails['install_status'] == 0 || $shopDetails['purchase_status'] == 'uninstall') {
                    return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/neweggcanada/site/login');
                }*/
                $neweggConfig = [];
                //$queryObj = $connection->createCommand("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".MERCHANT_ID."'");
                //$walmartConfig = $queryObj->queryOne();
                /*  $neweggConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `newegg_configuration` WHERE merchant_id='" . MERCHANT_ID . "'", 'one');
                  if ($walmartConfig) {
                      define("CONSUMER_CHANNEL_TYPE_ID", $neweggConfig['consumer_channel_type_id']);
                      define("API_USER", $neweggConfig['consumer_id']);
                      define("API_PASSWORD", $neweggConfig['secret_key']);
                  }*/
            }
            $id = Yii::$app->user->identity->id;
            if ($expire_model = NeweggShopDetail::find()->select('purchase_status,expire_date,install_status,app_status')->where(['merchant_id' => $id])->one()) {

                if ($expire_model && ($expire_model['purchase_status'] == Data::LIECENCE_EXPIRED || $expire_model['purchase_status'] == Data::PURCHASE_STATUS_TRAILEXPIRE)) {
                    return $this->redirect(['paymentplan']);
                }

                if ($expire_model && ($expire_model['install_status'] == '0' || $expire_model['app_status'] == 'uninstall')) {
                    return $this->redirect('https://apps.shopify.com/newegg-marketplace-integration');
                }
            }
            $queryString = '';
            $shop = Yii::$app->request->get('shop', false);
            if ($shop)
                $queryString = '?shop=' . $shop;
            //Code By Ankit Start
            Installation::completeInstallationForOldMerchants($id);

            $installation = Installation::isInstallationComplete($id);
            if ($installation) {
                if ($installation['status'] == Installation::INSTALLATION_STATUS_PENDING) {
                    $step = $installation['step'];
                    //$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet-install/index?step='.$step,302);
                    $this->redirect(Data::getUrl('newegg-install/index' . $queryString));
                    return false;
                }
            } else {
                $step = Installation::getFirstStep();
                //$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet-install/index?step='.$step,302);
                $this->redirect(Data::getUrl('newegg-install/index' . $queryString));
                return false;
            }
            //Code By Ankit End
            return $this->render('index');
        }
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        /*if (!Yii::$app->user->isGuest) {
            die('dfgdfg');
            return $this->redirect(['index']);
        }*/
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            $domain_name = trim($_POST['LoginForm']['username']);

            if (preg_match('/http/', $domain_name)) {
                $domain_url = preg_replace("(^https?://)", "", $domain_name);//removes http from domain_name
                $domain_url = rtrim($domain_url, "/"); // Removes / from last
            } else {
                $domain_url = $domain_name;
            }

            $shop = isset($domain_url) ? $domain_url : $_GET['shop'];

            $shopifyClient = new ShopifyClientHelper($shop, "", NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);

            // get the URL to the current page
            $pageURL = 'http';
            if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
                $pageURL .= "s";

            $pageURL .= "://";

            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }

            if (isset($_GET['shop'])) {
                $urlshop = array();
                $urlshop = parse_url($pageURL);
                $pageURL = $urlshop['scheme'] . "://" . $urlshop['host'] . $urlshop['path'];
                $pageURL = rtrim($pageURL, "/");
            }

            /* Code For Referral Start */
            if($ref_code = Yii::$app->request->post('ref_code', false)) {
                $session = Yii::$app->session;
                $session->set('ref_code', $ref_code);
            }
            /* Code For Referral End */

            $url = parse_url($shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));

            if (checkdnsrr($url['host'], 'A')) {
                header("Location: " . $shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
                exit;
            } else {
                return $this->render('index-new', [
                    'model' => $model,
                ]);
            }

            /* $model->login($shop);

             return $this->redirect(['index']);*/

        } elseif (isset($_GET['code'])) {
            session_unset();

            $shopifyClient = new ShopifyClientHelper($_GET['shop'], "", NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);
            $token = $shopifyClient->getAccessToken($_GET['code']);

            if ($token != '') {

                $sc = new ShopifyClientHelper($_GET['shop'], $token, NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);
                Data::createWebhooks($sc, $_GET['shop'], $_SESSION['token']); // Creating Webhook

                $userModel = new User();
                $result = $userModel->find()->where(['username' => $_GET['shop']])->one();

                $merchant_id = '';
                $response = '';
                $shopName = $_GET['shop'];

                /**
                 * Insert Data in `merchant_db` table
                 */
                $merchantModel = new MerchantDb();
                $merchant = $merchantModel->find()->where(['shop_name' => $shopName])->one();
                if (!$merchant) {
                    $merchantModel->shop_name = $shopName;
                    $merchantModel->db_name = Yii::$app->getCurrentDb();
                    $merchantModel->app_name = Data::APP_NAME_NEWEGG_CAN;
                    $merchantModel->save(false);

                    $merchant_id = $merchantModel->merchant_id;
                } else {
                    $merchant->app_name = $merchant->app_name. ',' .Data::APP_NAME_NEWEGG_CAN;
                    $merchant->save(false);

                    $merchant_id = $merchant['merchant_id'];
                }

                // entry in User table
                if (!$result) {
                    $response = Data::getShopifyShopDetails($sc);

                    //save data in `user` table
                    $userModel->username = $_GET['shop'];
                    $userModel->auth_key = '';
                    $userModel->shop_name = $response['name'];
                    $userModel->save(false);

                    $merchant_id = $userModel->id;
                }

                /* Code For Referral Start */
                $session = Yii::$app->session;
                if ($ref_code = $session->get('ref_code')) {
                    $session->remove('ref_code');

                    $query = "SELECT `id`,`merchant_id` FROM `referrer_user` WHERE `code` LIKE '{$ref_code}'";
                    $referrer = Data::sqlRecords($query, 'one');

                    $appName = 'newegg_ca';
                    $query = "SELECT `id` FROM `referral_user` WHERE `merchant_id`='{$merchant_id}' AND `app` LIKE '{$appName}'";
                    $referral = Data::sqlRecords($query, 'one');
                    if($referrer && !$referral && $referrer['merchant_id']!=$merchant_id) {
                        $referrer_id = $referrer['id'];
                        $status = self::REFERRAL_STATUS_PENDING;
                        $query = "INSERT INTO `referral_user`(`referrer_id`, `merchant_id`, `app`, `status`) VALUES ('{$referrer_id}', '{$merchant_id}', '{$appName}', '{$status}')";
                        Data::sqlRecords($query, null, 'insert');
                    }
                }
                /* Code For Referral End */

                $neweggShopDetailModel = new NeweggShopDetail();
                $neweggShopDetail = $neweggShopDetailModel->find()->where(['shop_url' => $_GET['shop']])->one();
                if (!$neweggShopDetail) {
                    // first time install app
                    if ($response == '')
                        $response = Data::getShopifyShopDetails($sc);

                    if ($merchant_id == '')
                        $merchant_id = $result['id'];

                    //save data in `newegg_shop_details` table
                    $neweggShopDetailModel->merchant_id = $merchant_id;
                    $neweggShopDetailModel->shop_url = $response['myshopify_domain'];
                    $neweggShopDetailModel->shop_name = $response['name'];
                    $neweggShopDetailModel->email = $response['email'];
                    $neweggShopDetailModel->token = $token;
                    $neweggShopDetailModel->currency = $response['currency'];
                    $neweggShopDetailModel->install_status = Data::STATUS_INSTALL;
                    $neweggShopDetailModel->install_date = date('Y-m-d H:i:s');
                    $neweggShopDetailModel->expire_date = date('Y-m-d H:i:s', strtotime('+15 days', strtotime(date('Y-m-d H:i:s'))));
                    $neweggShopDetailModel->country_code = $response['country'];
                    $clientStoreData = json_encode($response);
                    $neweggShopDetailModel->client_data = $clientStoreData;

                    // $neweggShopDetailModel->purchase_date = $response['currency'];
                    $neweggShopDetailModel->purchase_status = Data::PURCHASE_STATUS_TRAIL;
                    $neweggShopDetailModel->app_status = 'install';

                    $neweggShopDetailModel->save(false);
                } elseif ($neweggShopDetail->token != $token || $neweggShopDetail->install_status == '0') {
                    // re-install after uninstalling new-egg app
                    $neweggShopDetail->install_status = Data::STATUS_INSTALL;
                    $neweggShopDetail->purchase_status = Data::PURCHASE_STATUS_TRAIL;
                    $neweggShopDetail->app_status = 'install';
                    $neweggShopDetail->token = $token;
                    $neweggShopDetail->save(false);
                }
                Webhook::createWebhooks($shopName);

                $model->login($_GET['shop']);
                return $this->redirect(['index']);
            }
        } elseif (isset($_GET['shop'])) {

            $neweggShopDetail = NeweggShopDetail::find()->where(['shop_url' => $_GET['shop']])->one();

            //autologin
            if ($neweggShopDetail && $neweggShopDetail->install_status) {
                $session = "";
                $session = Yii::$app->session;
                $session->remove('newegg_installed');
                $session->remove('newegg_appstatus');
                $session->remove('newegg_configured');
                $session->remove('newegg_validateapp');
                $session->remove('newegg_dashboard');
                $session->remove('newegg_extension');
                $session->close();
                $model->login($_GET['shop']);
                return $this->redirect(['index']);
            } else {
                $domain_name = trim($_GET['shop']);

                if (preg_match('/http/', $domain_name)) {
                    $domain_url = preg_replace("(^https?://)", "", $domain_name);//removes http from domain_name
                    $domain_url = rtrim($domain_url, "/"); // Removes / from last
                } else {
                    $domain_url = $domain_name;
                }
                $shop = isset($domain_url) ? $domain_url : $_GET['shop'];
                $shopifyClient = new ShopifyClientHelper($shop, "", NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);

                // get the URL to the current page
                $pageURL = 'http';
                if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
                    $pageURL .= "s";
                }
                $pageURL .= "://";

                if ($_SERVER["SERVER_PORT"] != "80") {
                    $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
                } else {
                    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
                }
                if ($_GET['shop']) {
                    $urlshop = array();
                    $urlshop = parse_url($pageURL);
                    $pageURL = $urlshop['scheme'] . "://" . $urlshop['host'] . $urlshop['path'];
                    $pageURL = rtrim($pageURL, "/");
                }
                $url = parse_url($shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));

                if (checkdnsrr($url['host'])) {
                    header("Location: " . $shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
                    exit;
                } else {
                    return $this->render('index-new', [
                        'model' => $model,
                    ]);
                }
            }
        } elseif (!\Yii::$app->user->isGuest /*&& Walmartappdetails::appstatus(\Yii::$app->user->identity->username)*/) {
            return $this->goHome();
        } else {
            return $this->render('index-new', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function goHome()
    {
        $url = Data::getUrl('site/index');
        return $this->redirect($url);
    }

    /*
    * this login action for Login from Admin
    */
    public function actionManagerlogin()
    {
        $merchant_id = isset($_GET['ext']) ? $_GET['ext'] : false;
        if ($merchant_id) {
            $session = Yii::$app->session;
            $session->remove('newegg_installed');
            $session->remove('newegg_appstatus');
            $session->remove('newegg_configured');
            $session->remove('newegg_validateapp');
            $session->remove('newegg_dashboard');
            $session->remove('newegg_extension');
            $session->close();
            $result = User::findOne($merchant_id);
            if ($result) {
                $model = new LoginForm();
                $model->login($result->username);
                return $this->redirect(['login']);
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionGuide()
    {
        return $this->render('guide');
    }

    /*Action for newegg market place payment*/

    public function actionPaymentplan()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        if (!isset($token) || !isset($shop)) {
            $merchant_id = Yii::$app->user->identity->id;
            $shop = Yii::$app->user->identity->username;
            $shopDetails = Helper::storeDetail($merchant_id);
            $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
        }

        $sc = new ShopifyClientHelper($shop, $token, NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);

        if (isset($_GET['plan']) && $_GET['plan'] == 2) {
            $update = array(
                'application_charge' => array(
                    'name' => '6 Months Subscription Plan',
                    "price" => 162.0,
                    "return_url" => Url::toRoute('site/checkpayment?plan=2', 'https'),
                    "test"=>"true",
                )
            );

            $response = "";
            $response = $sc->call('POST', '/admin/application_charges.json', $update);
            if ($response && !(isset($response['errors']))) {
                echo '<script type="text/javascript">window.top.location.href = "' . $response['confirmation_url'] . '"; </script>';
                die;
            }
        } elseif (isset($_GET['plan']) && $_GET['plan'] == 3) {
            $update = array(
                'application_charge' => array(
                    "name" => '1 Year Subscription Plan',
                    "price" => 299.00,
                    "return_url" => Url::toRoute('site/checkpayment?plan=3', 'https'),
                    "test"=>"true",
                )
            );
            $response = "";
            $response = $sc->call('POST', '/admin/application_charges.json', $update);

            if ($response && !(isset($response['errors']))) {
                echo '<script type="text/javascript">window.top.location.href = "' . $response['confirmation_url'] . '"; </script>';
            }
        }
        return $this->render('paymentplan');
    }

    public function actionCheckpayment()
    {
        if (!isset($token) || !isset($shop)) {
            $merchant_id = Yii::$app->user->identity->id;
            $shop = Yii::$app->user->identity->username;
            $shopDetails = Helper::storeDetail($merchant_id);
            $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
        }
        $isPayment = false;
        $sc = new ShopifyClientHelper($shop, $token, NEWEGGCANADA_APP_KEY, NEWEGGCANADA_APP_SECRET);

        if (isset($_GET['charge_id']) && isset($_GET['plan']) && $_GET['plan'] == 2) {
            $response = "";
            $response = $sc->call('GET', '/admin/application_charges/' . $_GET['charge_id'] . '.json');
            if (isset($response['id']) && $response['status'] == "accepted") {
                $isPayment = true;
                $response = array();
                $response = $sc->call('POST', '/admin/application_charges/' . $_GET['charge_id'] . '/activate.json', $response);
                if (is_array($response) && count($response) > 0) {
                    $recurring = "";
                    $recurring = Data::sqlRecords('select `id` from `newegg_can_payment` where id="' . $_GET['charge_id'] . '"', 'all');
                    if (!$recurring) {
                        $created_at = date('Y-m-d H:i:s', strtotime($response['created_at']));
                        $updated_at = date('Y-m-d H:i:s', strtotime($response['updated_at']));
                        $response['timestamp'] = date('d-m-Y H:i:s');
                        $query = "insert into `newegg_can_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('" . $_GET['charge_id'] . "','" . $merchant_id . "','" . $created_at . "','" . $updated_at . "','" . $response['status'] . "','" . json_encode($response) . "','" . $response['name'] . "')";
                        $model1 = Data::sqlRecords($query, null, 'insert');
                        //change data-time and status in newegg-extension-details
                        $expire_date = date('Y-m-d H:i:s', strtotime('+6 months', strtotime($updated_at)));
                        $query = "UPDATE `newegg_can_shop_detail` SET `purchase_date`='" . $updated_at . "',`expire_date`='" . $expire_date . "' ,`purchase_status`='Purchased' where merchant_id='" . $merchant_id . "'";
                        $model2 = Data::sqlRecords($query, null, 'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, $_GET['plan']);
                        /* Code For Referral End */
                    }
                    Yii::$app->session->setFlash('success', "Thank you for choosing " . $response['name']);
                }
            } else {
                return $this->redirect(['paymentplan']);
            }
        } elseif (isset($_GET['charge_id']) && isset($_GET['plan']) && $_GET['plan'] == 3) {
            $response = "";
            $response = $sc->call('GET', '/admin/application_charges/' . $_GET['charge_id'] . '.json');
            if (isset($response['id']) && $response['status'] == "accepted") {
                $isPayment = true;
                $response = array();
                $response = $sc->call('POST', '/admin/application_charges/' . $_GET['charge_id'] . '/activate.json', $response);
                if (is_array($response) && count($response) > 0) {
                    $recurring = "";
                    //echo $expire_date=date('Y-m-d H:i:s',strtotime('+1 year', strtotime(date('Y-m-d H:i:s',strtotime($response['updated_at'])))));
                    //die("XCvcv");

                    $recurring = Data::sqlRecords('select `id` from `newegg_can_payment` where id="' . $_GET['charge_id'] . '"', 'all');
                    if (!$recurring) {
                        $created_at = date('Y-m-d H:i:s', strtotime($response['created_at']));
                        $updated_at = date('Y-m-d H:i:s', strtotime($response['updated_at']));
                        $response['timestamp'] = date('d-m-Y H:i:s');
                        $query = "insert into `newegg_can_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('" . $_GET['charge_id'] . "','" . $merchant_id . "','" . $created_at . "','" . $updated_at . "','" . $response['status'] . "','" . json_encode($response) . "','" . $response['name'] . "')";
                        $model1 = Data::sqlRecords($query, null, 'insert');
                        //change data-time and status in jet-extension-details
                        $expire_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($updated_at)));
                        $query = "UPDATE `newegg_can_shop_detail` SET `purchase_date`='" . $updated_at . "',`expire_date`='" . $expire_date . "' ,`purchase_status`='Purchased' where merchant_id='" . $merchant_id . "'";
                        $model2 = Data::sqlRecords($query, null, 'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, $_GET['plan']);
                        /* Code For Referral End */
                    }
                    Yii::$app->session->setFlash('success', "Thank you for choosing " . $response['name']);
                }
            } else {
                return $this->redirect(['paymentplan']);
            }
        }
        return $this->redirect(['index']);
    }

    /* Code For Referral Start */
    public function approveReferralPayment($merchant_id, $paymentId, $paymentData, $planId)
    {
        $appName = 'newegg';
        $status = self::REFERRAL_STATUS_PENDING;
        $query = "SELECT `id`, `referrer_id` FROM `referral_user` WHERE `merchant_id` = '{$merchant_id}' AND `status` LIKE '{$status}' AND `app` LIKE '{$appName}'";
        $result = Data::sqlRecords($query, 'one');

        if($result) {
            $status_complete = self::REFERRAL_STATUS_COMPLETE;
            $referral_id = $result['id'];
            $query = "UPDATE `referral_user` SET `status`='{$status_complete}', `payment_date`=NOW() WHERE `id`=".$referral_id;
            Data::sqlRecords($query, null, 'update');

            $referrer_id = $result['referrer_id'];
            $amount = self::calculateCreditAmount($paymentData['price'], $planId);
            $type = 'credit';
            $comment = 'Referrral Amount for Referrral Id : '.$referral_id;
            $query = "INSERT INTO `referrer_payment`(`payment_id`, `referrer_id`, `referral_id`, `referral_merchant_id`, `amount`, `type`, `comment`, `app`) VALUES ('{$paymentId}', '{$referrer_id}', '{$referral_id}', '{$merchant_id}', '{$amount}', '{$type}', '{$comment}', '{$appName}')";
            Data::sqlRecords($query, null, 'insert');
        }
    }

    public function calculateCreditAmount($paidAmount, $planId)
    {
        /*$amount = 0.00;

        $deductionByShopify = 20;//in percentage

        $paidAmount = floatval($paidAmount);
        $amountPaidtoShopify = ($paidAmount*$deductionByShopify)/100;

        switch ($planId) {
            case '1':
                //Monthly Plan
                $amount = $paidAmount-$amountPaidtoShopify;
                break;

            case '2':
                //Half-Yearly Plan
                $amount = ($paidAmount-$amountPaidtoShopify)/6;
                break;

            case '3':
                //Yearly Plan
                $amount = ($paidAmount-$amountPaidtoShopify)/12;
                break;
            
            default:
                
                break;
        }
        return $amount;*/

        $yearlyAmount = 300.00;
        $deductionByShopify = 20;//in percentage
        $amountPaidtoShopify = ($yearlyAmount*$deductionByShopify)/100;

        $amount = ($yearlyAmount-$amountPaidtoShopify)/12;
        return $amount;
    }
    /* Code For Referral End */

    public function actionNeweggerror()
    {
        $exception = Yii::$app->errorHandler->exception;
//        $error=Yii::$app->errorHandler->error;
        if ($exception !== null) {
//            return $this->render('error', ['exception' => $exception, 'error'=>$error]);
            return $this->render('error', ['exception' => $exception]);
        }
    }

}

