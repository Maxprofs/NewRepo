<?php
namespace frontend\modules\walmart\controllers;

use common\models\LoginForm;
use common\models\Post;
use common\models\User;
use common\models\MerchantDb;
use frontend\modules\walmart\components\Dashboard;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Installation;
use frontend\modules\walmart\components\Mail;
use frontend\modules\walmart\components\Sendmail;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Walmartappdetails;
use frontend\modules\walmart\models\AppStatus;
use frontend\modules\walmart\models\WalmartExtensionDetail;
use frontend\modules\walmart\models\WalmartShopDetails;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\BaseJson;
use yii\helpers\Url;
use yii\web\Controller;


/**
 * Site controller
 */
class SiteController extends Controller
{
    const MARKETPLACE = 'walmart';
    const STATUS = 'pending';
    const NO_OF_REQUEST = 1;
    const PENDING = 'pending';

    /* Code For Referral Start */
    const REFERRAL_STATUS_PENDING = 'pending';
    const REFERRAL_STATUS_COMPLETE = 'complete';
    /* Code For Referral End */

    /**
     * @inheritdoc
     */
    protected $shop;
    protected $token;
    protected $connection;
    protected $merchant_id;
    protected $sc;

    public function behaviors()
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
            /* 'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ], */
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $this->layout = 'main';
        if (isset(Yii::$app->controller->module->module->requestedRoute) && Yii::$app->controller->module->module->requestedRoute == 'walmart/site/guide') {

            Yii::$app->view->registerMetaTag([
                'name' => 'title',
                'content' => 'Documentation: How to Sell on Walmart.com?'

            ], "title");

            Yii::$app->view->registerMetaTag([
                'name' => 'keywords',
                'content' => 'start to sell on Walmart, how to sell on WalMart, sell in Walmart marketplace, sell Shopify products on Walmart marketplace, sell with Walmart, Walmart Shopify API integration.'

            ], "keywords");

            Yii::$app->view->registerMetaTag([
                'name' => 'description',
                'content' => 'Easily configure Shopify Walmart API Integration app and Sell products on Walmart marketplace with CedCommerce comprehensive user guide document.'

            ], "description");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:title',
                'content' => 'Documentation: How to Sell on Walmart.com?'

            ], "og:title");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:type',
                'content' => 'WEBSITE'

            ], "og:type");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:url',
                'content' => 'https://shopify.cedcommerce.com/integration/walmart/sell-on-walmart/'
            ], "og:url");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:description',
                'content' => 'Easily configure Shopify Walmart API Integration app and Sell products on Walmart marketplace with CedCommerce comprehensive user guide document.'
            ], "og:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:site_name',
                'content' => 'CedCommerce'
            ], "og:site_name");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:card',
                'content' => 'summary'
            ], "twitter:card");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:title',
                'content' => 'Documentation: How to Sell on Walmart.com?'
            ], "twitter:title");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:description',
                'content' => 'Easily configure Shopify Walmart API Integration app and Sell products on Walmart marketplace with CedCommerce comprehensive user guide document.'
            ], "twitter:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:url',
                'content' => 'https://shopify.cedcommerce.com/integration/walmart/sell-on-walmart/'
            ], "twitter:url");

            Yii::$app->view->registerLinkTag([
                'rel' => 'canonical',
                'href' => 'https://shopify.cedcommerce.com/integration/sell-on-walmart/'
            ], true);

            Yii::$app->view->registerMetaTag([
                'name' => 'ROBOTS',
                'content' => 'ADD INDEX,FOLLOW'
            ], "ROBOTS");
        } elseif (isset(Yii::$app->controller->module->module->requestedRoute) && Yii::$app->controller->module->module->requestedRoute == 'walmart/site/pricing') {
            Yii::$app->view->registerMetaTag([
                'name' => 'description',
                'content' => 'Good, Better, Best Save more with Standard Business and Pro Plan of Cedcommerce Shopify Walmart Marketplace API Integration, Start selling on walmart.com'

            ], "main_index"); //this will now replace the default one.
            Yii::$app->view->registerMetaTag([
                'name' => 'keywords',
                'content' => 'Shopify Walmart Integration pricing listing, Shopify Walmart Integration, Walmart Shopify Integration,Walmart Shopify API Integration, Sell on Walmart Marketplace, sell your Shopify products on Walmart marketplace'

            ], "keywords");
        } else {
            Yii::$app->view->registerMetaTag([
                'name' => 'title',
                'content' => 'Shopify Walmart Integration | CedCommerce'

            ], "title");

            Yii::$app->view->registerMetaTag([
                'name' => 'keywords',
                'content' => 'Walmart Shopify API Integration, sell Shopify products on Walmart marketplace, Walmart Marketplace API Integration, Sell on Walmart Marketplace'

            ], "keywords");

            Yii::$app->view->registerMetaTag([
                'name' => 'description',
                'content' => 'Shopify Walmart integration app, Connects your store with Walmart to upload products, manage inventory, order fulfillment, return and refund management .'

            ], "description");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:title',
                'content' => 'Shopify Walmart Integration | CedCommerce'

            ], "og:title");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:type',
                'content' => 'WEBSITE'

            ], "og:type");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:image',
                'content' => 'https://shopify.cedcommerce.com/integration/frontend/modules/walmart/assets/img/2bg_image1.png'

            ], "og:image");
            Yii::$app->view->registerMetaTag([
                'name' => 'og:url',
                'content' => 'https://shopify.cedcommerce.com/integration/walmart-marketplace/'
            ], "og:url");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:description',
                'content' => 'Shopify Walmart integration app, Connects your store with Walmart to upload products, manage inventory, order fulfillment, return and refund management .'
            ], "og:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'og:site_name',
                'content' => 'CedCommerce'
            ], "og:site_name");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:card',
                'content' => 'summary'
            ], "twitter:card");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:title',
                'content' => 'Shopify - Walmart.com Integration | CedCommerce'
            ], "twitter:title");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:description',
                'content' => 'Shopify Walmart integration app, Connects your store with Walmart to upload products, manage inventory, order fulfillment, return and refund management .'
            ], "twitter:description");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:image',
                'content' => 'https://shopify.cedcommerce.com/integration/frontend/modules/walmart/assets/img/2bg_image1.png'
            ], "twitter:image");

            Yii::$app->view->registerMetaTag([
                'name' => 'twitter:url',
                'content' => 'https://shopify.cedcommerce.com/integration/walmart-marketplace/'
            ], "twitter:url");

            Yii::$app->view->registerLinkTag([
                'rel' => 'canonical',
                'href' => 'https://shopify.cedcommerce.com/integration/walmart-marketplace/'
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

    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest)
        {
            $shop = Yii::$app->user->identity->username;
            $merchant_id = Yii::$app->user->identity->id;
            $shopDetails = Data::getWalmartShopDetails($merchant_id);
            $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
            $this->sc = new ShopifyClientHelper($shop,$token, WALMART_APP_KEY, WALMART_APP_SECRET);
            Yii::$app->request->enableCsrfValidation = false;
            return true;
        }
        else
        {
            return true;
        }
    }

    public function actionGuide()
    {
        return $this->render('guide');
    }

    public function actionNeedhelp()
    {
        $this->layout = "main2";
        $html = $this->render('needhelp');
        return $html;
    }

    public function actionSchedulecall()
    {
        if (\Yii::$app->user->isGuest) {
            return $this->redirect(['index']);
        }
        $this->layout = "main2";

        $html = $this->render('schedulecall');
        return $html;
    }

    public function actionFeedback()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['index']);
        }
        $this->layout = "main2";
        $html = $this->render('feedbackform');
        return $html;
    }
    /**
     * @client_feedBackSave
     */
    public function actionClientFeedback()
    {
        $getRequest = Yii::$app->request->post();
        $merchant_id = Yii::$app->user->identity->id;
        $client_record = Data::sqlRecords("SELECT * FROM `walmart_registration` WHERE `merchant_id`='".$merchant_id."'",'one');
        if(isset($client_record['email']) && !empty($client_record['email']) && isset($client_record['fname']) && !empty($client_record['fname'])  && isset($getRequest['description']) && !empty($getRequest['description']) && isset($getRequest['type']) && !empty($getRequest['type']) ){
            if(isset($client_record['lname']) && !empty($client_record['lname'])){
                $name = $client_record['fname'].' '.$client_record['lname'];
            }
            else{
                $name = $client_record['fname'];
            }
            $data['name'] = $name;
            $data['feedback_type'] = $getRequest['type'];
            $data['description'] = $getRequest['description'];
            $data['email'] = $client_record['email'];
            $data['type'] = $getRequest['type'];
            $this->email($data);
            $validateData = ['success' =>true ,'message' =>'feedback send successfully'];
            return BaseJson::encode($validateData);
        }
        else{
            $validateData = ['error' =>true ,'message' =>'Something Went Wrong Please try after some time '];
            return BaseJson::encode($validateData);
        }

    }
    /**
     * @email to shopify@cedcommerce.com
     */
    public static function email($data)
    {
        $mer_email= 'feedback@cedcommerce.com';
        $subject='Feedback for  Walmart App: '.$data['type'];
        $etx_mer="";
        $headers_mer = "MIME-Version: 1.0" . chr(10);
        $headers_mer .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
        $headers_mer .= 'From: '.$data['email'].'' . chr(10);
        $etx_mer .=$data['description'];
        mail($mer_email,$subject, $etx_mer, $headers_mer);
    }

    public function actionRequestcall()
    {

        if (isset($_POST['number']) && is_numeric($_POST['number']) && !empty($_POST['number']) && !empty($_POST['date']) && !empty($_POST['format'] && !empty($_POST['time']))) {
            $preffered_date = $_POST['date'];
            $number = $_POST['number'];
        } else {
            $response = ['error' => true, 'message' => 'Invalid / Wrong phone number'];
            return json_encode($response);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $date = date("Y-m-d H:i:s", time());
        $shop_detail = Data::getWalmartShopDetails($merchant_id);
        $preffered_time = $_POST['time'] . $_POST['format'];

        $call_record = Data::sqlRecords("SELECT * FROM `call_schedule` WHERE `merchant_id`= '".$merchant_id."' AND `marketplace`='".self::MARKETPLACE."' AND `number`= '".$number."'",'one');

        if(!empty($call_record) && $call_record['number'] == $number){

            $call_record['no_of_request'] = $call_record['no_of_request'] + 1;
            $query = "UPDATE `call_schedule` SET `no_of_request`='".$call_record['no_of_request']."',`status` = '".self::PENDING."',`preferred_date`='".$preffered_date."',`preferred_timeslot`='".$preffered_time."'";
            Data::sqlRecords($query,null,'update');
        }else{
            $query = "INSERT INTO `call_schedule` (`merchant_id`,`number`, `shop_url`,`marketplace`,`status`,`time`,`no_of_request`,`preferred_date`,`time_zone`,`preferred_timeslot`) VALUES ('" . $merchant_id . "','" . $number . "','" . $shop_detail['shop_url'] . "','" . self::MARKETPLACE . "','" . self::STATUS . "','" . $date . "','".self::NO_OF_REQUEST."','".$preffered_date."','UTC','".$preffered_time."')";

            Data::sqlRecords($query,null,'insert');
        }

        $response = ['success' => true, 'message' => 'Successfully submit'];

        return json_encode($response);
    }

    /*
    * this login action for Login from Admin
    */
    public function actionManagerlogin()
    {
        $merchant_id = isset($_GET['ext']) ? $_GET['ext'] : false;
        $authorizeAction = array('site/managerlogin'=>true);
        $allowedIp = array('182.72.248.90'=>true,'127.0.0.1'=>true,'103.85.141.198'=>true);
        if(!isset($_GET['enter'])){
            if(isset($authorizeAction[Yii::$app->controller->id.'/'.Yii::$app->controller->action->id])){
                if(!preg_match('/(192.168.0.)\w+/', $_SERVER['REMOTE_ADDR']) && !isset($allowedIp[$_SERVER['REMOTE_ADDR']])){
                    return $this->redirect(['login']);
                }
            }
        }
        if ($merchant_id) {

            $result = "";
            $session = "";
            $session = Yii::$app->session;
            $session->remove('walmart_installed');
            $session->remove('walmart_appstatus');
            $session->remove('walmart_configured');
            $session->remove('walmart_validateapp');
            $session->remove('walmart_dashboard');
            $session->remove('walmart_extension');
            $session->close();
            $result = User::findOne($merchant_id);
            if ($result) {
                $model = new LoginForm();
                $model->login($result->username, true);
                return $this->redirect(['index']);
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionIndex()
    {

        $session = Yii::$app->session;
        //$connection = Yii::$app->getDb();

        // Setting local timezone
        date_default_timezone_set('Asia/Kolkata');

        //save session id of user in user table
        if (!\Yii::$app->user->isGuest) {

            if (!defined('MERCHANT_ID') || Yii::$app->user->identity->id != MERCHANT_ID) {
                $merchant_id = Yii::$app->user->identity->id;

                $shopDetails = Data::getWalmartShopDetails($merchant_id);
                $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
                $email = isset($shopDetails['email']) ? $shopDetails['email'] : '';
                $currency = isset($shopDetails['currency']) ? $shopDetails['currency'] : 'USD';
                define("MERCHANT_ID", $merchant_id);
                define("SHOP", Yii::$app->user->identity->username);
                define("TOKEN", $token);
                define("CURRENCY", $currency);
                define("EMAIL", $email);

                //Save Shopify Admin Shop Details in Session
                $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
                $response = Data::getShopifyShopDetails($sc);
                if (!isset($response['errors'])) {
                    $session->set('shop_details', $response);
                }

                $walmartConfig = [];
                //$queryObj = $connection->createCommand("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".MERCHANT_ID."'");
                //$walmartConfig = $queryObj->queryOne();
                $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='" . MERCHANT_ID . "'", 'one');
                if ($walmartConfig) {
                    define("CONSUMER_CHANNEL_TYPE_ID", $walmartConfig['consumer_channel_type_id']);
                    define("API_USER", $walmartConfig['consumer_id']);
                    define("API_PASSWORD", $walmartConfig['secret_key']);
                }
            }
            $id = MERCHANT_ID;

            $username = SHOP;
            $token = TOKEN;

            //$manager_login = Yii::$app->user->identity->manager;
            $user_id = Yii::$app->user->id;
            $session = Yii::$app->session;
            $manager_login = $session->get('manager_login_'.$user_id);

            $obj = new Walmartappdetails();
            if ($obj->appstatus($username) == false) {
                return $this->redirect('https://apps.shopify.com/walmart-marketplace-integration');
            }

            //get shop name
            $queryString = '';
            $shop = Yii::$app->request->get('shop', false);
            if ($shop)
                $queryString = '?shop=' . $shop;
            //Code By Himanshu Start
            Installation::completeInstallationForOldMerchants(MERCHANT_ID);

            $installation = Installation::isInstallationComplete(MERCHANT_ID);
            if ($installation) {
                if ($installation['status'] == Installation::INSTALLATION_STATUS_PENDING) {
                    $step = $installation['step'];
                    //$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet-install/index?step='.$step,302);
                    $this->redirect(Data::getUrl('walmart-install/index' . $queryString));
                    return false;
                }
            } else {
                $step = Installation::getFirstStep();
                //$this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet-install/index?step='.$step,302);
                $this->redirect(Data::getUrl('walmart-install/index' . $queryString));
                return false;
            }
            //Code By Himanshu End

            //check Configuration Pop-up condition.
            $ispopup = "";
            $flagConfig = true;

            $status = Walmartappdetails::isValidateapp($id);

            if ($status == "trial_expired" && !$manager_login) {
                return $this->redirect(['paymentplan']);
            } elseif ($status == "expire" && !$manager_login) {
                return $this->redirect(['paymentplan']);
            }

            $now = date("Y-m-d H:i:s");
            $popup_expire_model = WalmartExtensionDetail::find()->select('status,expire_date')->where(['merchant_id' => MERCHANT_ID])->one();
            $expire_date = new \DateTime($popup_expire_model['expire_date']);
            $now = new \DateTime();
            $diff = $now->diff($expire_date);
            if (!is_array($walmartConfig) || count($walmartConfig) == 0) {
                $ispopup = "show";
                $flagConfig = false;
            }
            //var_dump($diff->days);die;
            $isExpire = "";
            /* if($diff->days <= 3)
             {
                 $isExpire="showTrialExpirePopup";
             }*/
            if (!is_array($walmartConfig) || count($walmartConfig) == 0) {
                $ispopup = "show";
                $flagConfig = false;
            }
            $dashboard = Dashboard::getDashboardInfo($id, $session);
            $orderData = Dashboard::prepareOrderGraphData($id);
            $donut_chart_data = Dashboard::prepareProductGraphData($id);
            $model = new LoginForm();

            return $this->render('index', ['model' => $model, 'popup' => $ispopup, 'isExpire' => $isExpire, 'dashboard' => $dashboard, 'graphData' => $orderData, 'donut_chart_data' => $donut_chart_data]);
        } else {

            $model = new LoginForm();
            return $this->render('index-new', [
                'model' => $model,
            ]);
        }
    }

    public function actionStartsession(){
        header('P3P:CP="CAO IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        session_start();
        $referrer = Url::toRoute(['site/login', 'shop'=>$_GET['sp']],'https');
        echo '<script>top.location = "'.$referrer.'";</script>';die();
    }

    public function actionLogin()
    {
        // Setting local timezone
        date_default_timezone_set('Asia/Kolkata');

        //$session = Yii::$app->session;

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

            $shopifyClient = new ShopifyClientHelper($shop, "", WALMART_APP_KEY, WALMART_APP_SECRET);

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
            $model->login($shop);

            return $this->redirect(['index']);

        } elseif (isset($_GET['code'])) {

            //session_unset();

            $shopifyClient = new ShopifyClientHelper($_GET['shop'], "", WALMART_APP_KEY, WALMART_APP_SECRET);
            $token = $shopifyClient->getAccessToken($_GET['code']);

            if ($token != '') {

                $sc = new ShopifyClientHelper($_GET['shop'], $token, WALMART_APP_KEY, WALMART_APP_SECRET);
                //Data::createWebhooks($sc, $_GET['shop'], $token); // Creating Webhook

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
                    $merchantModel->app_name = Data::APP_NAME_WALMART;
                    $merchantModel->save(false);

                    $merchant_id = $merchantModel->merchant_id;
                } else {
                    $merchant->app_name = $merchant->app_name. ',' .Data::APP_NAME_WALMART;
                    $merchant->save(false);

                    $merchant_id = $merchant['merchant_id'];
                }

               /**
                * Save data in `user` table
                */
                $userModel = new User();
                $result = $userModel->find()->where(['username' => $shopName])->one();
                if (!$result) {
                    $response = Data::getShopifyShopDetails($sc);

                    $userModel->id = $merchant_id;
                    $userModel->username = $shopName;
                    $userModel->auth_key = '';
                    $userModel->shop_name = $response['name'];
                    $userModel->save(false);
                }

                /* Code For Referral Start */
                $session = Yii::$app->session;
                if ($ref_code = $session->get('ref_code')) {
                    $session->remove('ref_code');

                    $query = "SELECT `id`,`merchant_id` FROM `referrer_user` WHERE `code` LIKE '{$ref_code}'";
                    $referrer = Data::sqlRecords($query, 'one');

                    $appName = 'walmart';
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

                $walmartShopDetailModel = new WalmartShopDetails();
                $walmartShopDetail = $walmartShopDetailModel->find()->where(['shop_url' => $_GET['shop']])->one();
                if (!$walmartShopDetail) {
                    if ($response == '')
                        $response = Data::getShopifyShopDetails($sc);

                    //save data in `walmart_shop_details` table
                    $walmartShopDetailModel->merchant_id = $merchant_id;
                    $walmartShopDetailModel->shop_url = $response['myshopify_domain'];
                    $walmartShopDetailModel->shop_name = $response['name'];
                    $walmartShopDetailModel->email = $response['email'];
                    $walmartShopDetailModel->token = $token;
                    $walmartShopDetailModel->currency = $response['currency'];
                    $walmartShopDetailModel->status = 1;
                    $walmartShopDetailModel->save(false);
                } elseif ($walmartShopDetail->token != $token || $walmartShopDetail->status == '0') {
                    $walmartShopDetail->status = 1;
                    $walmartShopDetail->token = $token;
                    $walmartShopDetail->save(false);
                }

                //if (!isset($session['walmart_extension'])) {
                //$session->set('walmart_extension', true);
                $extensionDetail = WalmartExtensionDetail::find()->select('id')->where(['merchant_id' => $merchant_id])->one();
                if (is_null($extensionDetail)) {
                    $extensionDetailModel = new WalmartExtensionDetail();
                    $extensionDetailModel->merchant_id = $merchant_id;
                    $extensionDetailModel->install_date = date('Y-m-d H:i:s');
                    $extensionDetailModel->date = date('Y-m-d H:i:s');
                    $extensionDetailModel->expire_date = date('Y-m-d H:i:s', strtotime('+7 days', strtotime(date('Y-m-d H:i:s'))));
                    $extensionDetailModel->status = "Not Purchase";
                    $extensionDetailModel->app_status = "install";
                    $extensionDetailModel->save(false);
                    //Sending Mail to clients , when app installed
                    /*if(defined(EMAIL))
                        Yii::$app->Sendmail->installmail(EMAIL);*/
                } elseif ($extensionDetail->app_status != "install") {
                    $extensionDetail->app_status = "install";
                    $extensionDetail->save(false);
                }
                //}

                //create webhooks
                \frontend\components\Webhook::createWebhooks($_GET['shop']);

                if (isset($result['id']) && !empty($result['id'])) {
                    $merchant_id = $result['id'];
                    $emailConfigCheck = "SELECT * FROM `walmart_config` WHERE data LIKE'email/%' and `merchant_id`='" . $merchant_id . "'";
                    $emailConfigCheckdata = Data::sqlRecords($emailConfigCheck, "all");
                    $query = "SELECT * FROM `email_template`";
                    $email = Data::sqlRecords($query, "all");
                    if (empty($emailConfigCheckdata)) {

                        $query = "SELECT * FROM `email_template`";
                        $email = Data::sqlRecords($query, "all");
                        foreach ($email as $key => $value) {
                            $emailConfiguration['email/' . $value['template_title']] = isset($value["template_title"]) ? 1 : 0;
                        }
                        if (!empty($emailConfiguration)) {
                            foreach ($emailConfiguration as $key => $value) {
                                Data::saveConfigValue($merchant_id, $key, $value);
                            }
                        }
                    } else {
                        foreach ($emailConfigCheckdata as $key1 => $value1) {
                            foreach ($email as $key => $value) {
                                $emailTitle = str_replace('email/', '', $value1['data']);
                                if (trim($value["template_title"]) == trim($emailTitle)) {
                                    $emailConfiguration['email/' . $emailTitle] = 0;
                                    break;

                                } else {
                                    $emailConfiguration['email/' . $emailTitle] = 1;

                                }

                            }


                        }
                        if (!empty($emailConfiguration)) {
                            foreach ($emailConfiguration as $key => $value) {

                                if ($value == '1') {
                                    Data::saveConfigValue($merchant_id, $key, $value);
                                }
                            }

                        }
                    }
                }
                $model->login($_GET['shop']);
                $session = Yii::$app->session;
                $redirectData = $session['redirect_url'];
                unset($session['redirect_url']);
                if ($redirectData) {
                    $redirectMainData = str_replace('walmart/', '', $redirectData);
                    return $this->redirect([$redirectMainData]);
                } else {
                    return $this->redirect(['index']);
                }

            }
        } elseif (isset($_GET['shop'])) {
            $walmartShopDetail = WalmartShopDetails::find()->where(['shop_url' => $_GET['shop']])->one();

            //autologin
            if ($walmartShopDetail && $walmartShopDetail->status) {
                $session = "";
                $session = Yii::$app->session;
                $session->remove('walmart_installed');
                $session->remove('walmart_appstatus');
                $session->remove('walmart_configured');
                $session->remove('walmart_validateapp');
                $session->remove('walmart_dashboard');
                $session->remove('walmart_extension');
                $session->close();

                //$sc = new ShopifyClientHelper($_GET['shop'], $walmartShopDetail['token'], WALMART_APP_KEY, WALMART_APP_SECRET);
                //check shop

                //$shopPlan=array();
                //$shopPlan=$sc->call('GET','/admin/shop.json');
                //if(is_array($shopPlan) && $shopPlan['plan_display_name']=="affiliate"){
                //echo '<script type="text/javascript">window.top.location.href = "https://'.$shopPlan["myshopify_domain"]. '/admin/account/please_upgrade"; </script>';
                //die;
                //}
                //unset($shopPlan);
                $model->login($_GET['shop']);
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
                    if (count($_COOKIE) === 0) {
                        if (!array_key_exists("PHPSESSID",$_COOKIE)){
                            $referrer = Url::toRoute(['site/startsession', 'sp'=>$_GET['shop']],'https');
                            echo '<script> top.location = "'.$referrer.'";</script>';die();
                        }
                    }
                }
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
                $shopifyClient = new ShopifyClientHelper($shop, "", WALMART_APP_KEY, WALMART_APP_SECRET);

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

                //var_dump($shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));die;
                if (checkdnsrr($url['host'])) {
                    header("Location: " . $shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
                    exit;
                } else {
                    return $this->render('index-new', [
                        'model' => $model,
                    ]);
                }
            }
        } elseif (!\Yii::$app->user->isGuest && Walmartappdetails::appstatus(\Yii::$app->user->identity->username)) {
            return $this->goHome();
        } else {
            return $this->render('index-new', [
                'model' => $model,
            ]);
        }
    }
    /*===raza===*/
    public function actionApplyCoupan(){
        $merchant_id=Yii::$app->user->identity->id;
        $data = array();
        $coupancode = Data::sqlRecords("SELECT  `amount_type`, `amount`, `applied_on`, `expire_date`, `applied_merchant`, `merchant_id` FROM `coupan_code` WHERE `promo_code`='".$_POST['coupan']."' LIMIT 0,1","one","select");
       //print_r($_POST);die;
        if ($coupancode['applied_on'] == $_POST['app'] && ($coupancode['merchant_id'] == $merchant_id || $coupancode['merchant_id'] ==0)) {
            if($coupancode['amount_type'] =="Fixed") {
                $data['price'] = $_POST['price']-$coupancode['amount'];
                $data['discount'] = $coupancode['amount'];
            }
            else{
                $amount = ($coupancode['amount']/100)*$_POST['price'];
                $data['discount'] = $amount;
                $data['price'] = $_POST['price']-$amount;
            }
            $data['success'] = "Coupon applied";
        }
        else{
            $data['error'] = "Coupon has been expired";
        }

        return json_encode($data);
    }
    public function actionPayment()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        } 
         $offerArr = [];
         $session = Yii::$app->session;
        $session->set('plan_data',$_POST);
         //print_r($_POST);die;
            $update=array(
                'recurring_application_charge'=>array(
                    'name' => 'Recurring Plan For '.$_POST['Activate_plan_time'],
                    "price"=> (float)$_POST['Activate_plan_price'],
                    "return_url"=> Url::toRoute('site/confirmpayment','https', $offerArr),
                    "capped_amount"=> 300,
                    "terms"=>"$10 for 100 orders",
                    "test" => true,
                    'billing_on'=> date('Y-m-d', strtotime("+3 days"))
                )
            ); 
            $response="";
            $response = $this->sc->call('POST','/admin/recurring_application_charges.json',$update);
           // print_r($response);die;
            if($response && !(isset($response['errors']))){
                echo '<script type="text/javascript">window.top.location.href = "'.$response['confirmation_url'].'"; </script>';
                die;
            }
    }
    public function actionConfirmpayment()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        $isPayment=false;   
        $Activate_plan_data = json_decode($_SESSION['plan_data']['Activate_plan_data'],true);
        $plan_data = json_encode($Activate_plan_data[0]['choose_plan_data']);
        $plan_time = $_SESSION['plan_data']['Activate_plan_time'];
        $charge_limit = array();
        $charge_limit['setup_charge'] = $_SESSION['plan_data']['setup_charge_check'];
        $charge_limit['product_limit'] = $Activate_plan_data[0]['product_count'];
        $charge_limit['order_limit'] = $Activate_plan_data[0]['product_count'];
        $charge_limit = json_encode($charge_limit);
        if(isset($_GET['charge_id']))
        {
            $response="";
            $response=$this->sc->call('GET','/admin/recurring_application_charges/'.$_GET['charge_id'].'.json');
            if(isset($response['id']) && $response['status']=="accepted")
            {
                $isPayment=true;
                // $response = []
                $response=$this->sc->call('POST','/admin/recurring_application_charges/'.$_GET['charge_id'].'/activate.json',$response);
                if(is_array($response) && count($response)>0)
                {
                    $recurring = [];
                    $query = 'SELECT `id` FROM `walmart_recurring_payment` WHERE merchant_id="'.$merchant_id.'" LIMIT 0,1';
                    $recurring = Data::sqlRecords($query,"one","select");
                    if(empty($recurring))
                    {
                        $created_at=date('Y-m-d H:i:s',strtotime($response['created_at']));
                        $updated_at=date('Y-m-d H:i:s',strtotime($response['updated_at']));
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="insert into `walmart_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type,charge_limit,choose_plan_data)
                                values('".$_GET['charge_id']."','".$merchant_id."','".$created_at."','".$updated_at."','".$response['status']."','".json_encode($response)."','".$plan_time."','".$charge_limit."','".$plan_data."')";
                               
                        Data::sqlRecords($query,null,'insert');
                        //change data-time and status in walmart-extension-details
                        $expire_date=date('Y-m-d H:i:s',strtotime('+'.$plan_time, strtotime($updated_at)));
                        $query = "UPDATE walmart_extension_detail SET date='" . $updated_at . "',expire_date='" . $expire_date . "' ,status='Purchased' where merchant_id='" . $merchant_id . "'";
                        Data::sqlRecords($query,null,'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '2');
                        /* Code For Referral End */
                    }
                    else
                    {
                        $created_at=date('Y-m-d H:i:s',strtotime($response['created_at']));
                        $updated_at=date('Y-m-d H:i:s',strtotime($response['updated_at']));
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="UPDATE `walmart_recurring_payment` SET `id`= '".$_GET['charge_id']."' ,`plan_type`= '".$plan_time."' ,`choose_plan_data`= '".$plan_data."' , `billing_on`='".$created_at."',`activated_on`='".$updated_at."',`charge_limit`='".$charge_limit."',`status`='".$response['status']."',`recurring_data`='".json_encode($response)."' where merchant_id='".$merchant_id."'";                                
                        Data::sqlRecords($query,null,'update');                        
                         
                        $expire_date=date('Y-m-d H:i:s',strtotime('+'.$plan_time, strtotime($updated_at)));
                        $query = "UPDATE walmart_extension_detail SET date='" . $updated_at . "',expire_date='" . $expire_date . "' ,status='Purchased' where merchant_id='" . $merchant_id . "'";
                        Data::sqlRecords($query,null,'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '2');
                        /* Code For Referral End */
                    }
                }                                
            }
            else
            {
                return $this->redirect(['paymentplan']);
            }
        }
        if($isPayment)
            if (isset($_SESSION['plan_data']['Activate_plan_coupan'])) {
                $query = "INSERT INTO `applied_coupan_code`( `merchant_id`, `used_on`, `coupan_code`) VALUES ('".$merchant_id."','Walmart','".$_SESSION['plan_data']['Activate_plan_coupan']."')";
                Data::sqlRecords($query,null,'insert');
            }
            Yii::$app->session->setFlash('success',"Congratulations! PAYMENT PLAN has been activated.");
        return $this->redirect(['index']);
    }
    /*===end===*/
    public function actionPaymentplan()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        if (!isset($token) || !isset($shop)) {
            $merchant_id = Yii::$app->user->identity->id;
            $shop = Yii::$app->user->identity->username;
            $shopDetails = Data::getWalmartShopDetails($merchant_id);
            $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
        }
        $sc = new ShopifyClientHelper($shop, $token, WALMART_APP_KEY, WALMART_APP_SECRET);

        if (isset($_GET['plan']) && $_GET['plan'] == 2) {
            $update = array(
                'application_charge' => array(
                    'name' => '6 Months Subscription Plan',
                    "price" => 180.00,
                    "return_url" => Url::toRoute('site/checkpayment?plan=2', 'https'),
                    //"test"=>"true",
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
                    "price" => 300.00,
                    "return_url" => Url::toRoute('site/checkpayment?plan=3', 'https'),
                    //"test"=>"true",
                )
            );
            $response = "";
            $response = $sc->call('POST', '/admin/application_charges.json', $update);
            if ($response && !(isset($response['errors']))) {
                echo '<script type="text/javascript">window.top.location.href = "' . $response['confirmation_url'] . '"; </script>';
            }
        } elseif (isset($_GET['plan']) && $_GET['plan'] == 1) {

            /*if($merchant_id !=957 &&  $merchant_id != 1028){
                Yii::$app->session->setFlash('error', "Not a Valid Paymentplan");

                return $this->render('paymentplan');
            }*/
            $update = array(
                'application_charge' => array(
                    "name" => '1 Month Subscription Plan',
                    "price" => 40.00,
                    "return_url" => Url::toRoute('site/checkpayment?plan=1', 'https'),
                    //"test"=>"true",
                )
            );
            $response = "";
            $response = $sc->call('POST', '/admin/application_charges.json', $update);
            if ($response && !(isset($response['errors']))) {
                echo '<script type="text/javascript">window.top.location.href = "' . $response['confirmation_url'] . '"; </script>';
            }
        }  elseif(isset($_GET['plan']) && $_GET['plan'] == 4){

            $update = array(
                'application_charge' => array(
                    "name" => '1 Year Subscription Plan',
                    "price" => 270.00,
                    "return_url" => Url::toRoute('site/checkpayment?plan=4', 'https'),
                    // "test"=>"true",
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
            $shopDetails = Data::getWalmartShopDetails($merchant_id);
            $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
            $connection = Yii::$app->getDb();
        }
        $isPayment = false;
        $sc = new ShopifyClientHelper($shop, $token, WALMART_APP_KEY, WALMART_APP_SECRET);

        if (isset($_GET['charge_id']) && isset($_GET['plan']) && $_GET['plan'] == 1) {
            $response = "";
            $response = $sc->call('GET', '/admin/application_charges/' . $_GET['charge_id'] . '.json');
            if (isset($response['id']) && $response['status'] == "accepted") {
                $isPayment = true;
                $response = array();
                $response = $sc->call('POST', '/admin/application_charges/' . $_GET['charge_id'] . '/activate.json', $response);
                if (is_array($response) && count($response) > 0) {
                    $recurring = "";
                    $recurring = $connection->createCommand('select `id` from `walmart_recurring_payment` where id="' . $_GET['charge_id'] . '"')->queryAll();
                    if (!$recurring) {
                        $created_at = date('Y-m-d H:i:s', strtotime($response['created_at']));
                        $updated_at = date('Y-m-d H:i:s', strtotime($response['updated_at']));
                        $response['timestamp'] = date('d-m-Y H:i:s');
                        $query = "insert into `walmart_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('" . $_GET['charge_id'] . "','" . $merchant_id . "','" . $created_at . "','" . $updated_at . "','" . $response['status'] . "','" . json_encode($response) . "','" . $response['name'] . "')";
                        $connection->createCommand($query)->execute();
                        //change data-time and status in walmart-extension-details
                        $expire_date = date('Y-m-d H:i:s', strtotime('+1 months', strtotime($updated_at)));
                        $query = "UPDATE walmart_extension_detail SET date='" . $updated_at . "',expire_date='" . $expire_date . "' ,status='Purchased' where merchant_id='" . $merchant_id . "'";
                        $connection->createCommand($query)->execute();

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, $_GET['plan']);
                        /* Code For Referral End */

                        /* Payment Mail starts */
                        /*self::paymentMail($merchant_id,$response['name']);*/
                        /* Payment Mail ends*/
                    }
                    Yii::$app->session->setFlash('success', "Thank you for choosing " . $response['name']);
                }
            } else {
                return $this->redirect(['paymentplan']);
            }
        } elseif (isset($_GET['charge_id']) && isset($_GET['plan']) && $_GET['plan'] == 2) {
            $response = "";
            $response = $sc->call('GET', '/admin/application_charges/' . $_GET['charge_id'] . '.json');
            if (isset($response['id']) && $response['status'] == "accepted") {
                $isPayment = true;
                $response = array();
                $response = $sc->call('POST', '/admin/application_charges/' . $_GET['charge_id'] . '/activate.json', $response);
                if (is_array($response) && count($response) > 0) {
                    $recurring = "";
                    $recurring = $connection->createCommand('select `id` from `walmart_recurring_payment` where id="' . $_GET['charge_id'] . '"')->queryAll();
                    if (!$recurring) {
                        $created_at = date('Y-m-d H:i:s', strtotime($response['created_at']));
                        $updated_at = date('Y-m-d H:i:s', strtotime($response['updated_at']));
                        $response['timestamp'] = date('d-m-Y H:i:s');
                        $query = "insert into `walmart_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('" . $_GET['charge_id'] . "','" . $merchant_id . "','" . $created_at . "','" . $updated_at . "','" . $response['status'] . "','" . json_encode($response) . "','" . $response['name'] . "')";
                        $connection->createCommand($query)->execute();
                        //change data-time and status in walmart-extension-details
                        $expire_date = date('Y-m-d H:i:s', strtotime('+6 months', strtotime($updated_at)));
                        $query = "UPDATE walmart_extension_detail SET date='" . $updated_at . "',expire_date='" . $expire_date . "' ,status='Purchased' where merchant_id='" . $merchant_id . "'";
                        $connection->createCommand($query)->execute();

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, $_GET['plan']);
                        /* Code For Referral End */

                        /* Payment Mail starts */
                        /*self::paymentMail($merchant_id,$response['name']);*/
                        /* Payment Mail ends*/
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

                    $recurring = $connection->createCommand('select `id` from `walmart_recurring_payment` where id="' . $_GET['charge_id'] . '"')->queryAll();
                    if (!$recurring) {
                        $created_at = date('Y-m-d H:i:s', strtotime($response['created_at']));
                        $updated_at = date('Y-m-d H:i:s', strtotime($response['updated_at']));
                        $response['timestamp'] = date('d-m-Y H:i:s');
                        $query = "insert into `walmart_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('" . $_GET['charge_id'] . "','" . $merchant_id . "','" . $created_at . "','" . $updated_at . "','" . $response['status'] . "','" . json_encode($response) . "','" . $response['name'] . "')";
                        $connection->createCommand($query)->execute();
                        //change data-time and status in walmart-extension-details
                        $expire_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($updated_at)));
                        $query = "UPDATE walmart_extension_detail SET date='" . $updated_at . "',expire_date='" . $expire_date . "' ,status='Purchased' where merchant_id='" . $merchant_id . "'";
                        $connection->createCommand($query)->execute();

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, $_GET['plan']);
                        /* Code For Referral End */

                        /* Payment Mail starts */
                        /*self::paymentMail($merchant_id,$response['name']);*/
                        /* Payment Mail ends*/
                    }
                    Yii::$app->session->setFlash('success', "Thank you for choosing " . $response['name']);
                }
            } else {
                return $this->redirect(['paymentplan']);
            }
        }elseif(isset($_GET['charge_id']) && isset($_GET['plan']) && $_GET['plan'] == 4){
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

                    $recurring = $connection->createCommand('select `id` from `walmart_recurring_payment` where id="' . $_GET['charge_id'] . '"')->queryAll();
                    if (!$recurring) {
                        $created_at = date('Y-m-d H:i:s', strtotime($response['created_at']));
                        $updated_at = date('Y-m-d H:i:s', strtotime($response['updated_at']));
                        $response['timestamp'] = date('d-m-Y H:i:s');
                        $query = "insert into `walmart_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('" . $_GET['charge_id'] . "','" . $merchant_id . "','" . $created_at . "','" . $updated_at . "','" . $response['status'] . "','" . json_encode($response) . "','" . $response['name'] . "')";
                        $connection->createCommand($query)->execute();
                        //change data-time and status in walmart-extension-details
                        $expire_date = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($updated_at)));
                        $query = "UPDATE walmart_extension_detail SET date='" . $updated_at . "',expire_date='" . $expire_date . "' ,status='Purchased' where merchant_id='" . $merchant_id . "'";
                        $connection->createCommand($query)->execute();

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, $_GET['plan']);
                        /* Code For Referral End */

                        /* Payment Mail starts */
                        /*self::paymentMail($merchant_id,$response['name']);*/
                        /* Payment Mail ends*/
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
    public static function approveReferralPayment($merchant_id, $paymentId, $paymentData, $planId)
    {
        $appName = 'walmart';
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

    public static function calculateCreditAmount($paidAmount, $planId)
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

    public function actionNewplan()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return $this->redirect('paymentplan');
        if (defined('MERCHANT_ID')) {
            $merchant_id = MERCHANT_ID;
            $shop = SHOP;
            $token = TOKEN;
        } else {

            $merchant_id = Yii::$app->user->identity->id;
            $shop = Yii::$app->user->identity->username;
            $shopDetails = Data::getWalmartShopDetails($merchant_id);
            $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
        }
        //$shopifymodel=Shopifyinfo::getShipifyinfo();
        $sc = new ShopifyClientHelper($shop, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
        if (isset($_GET['plan']) && $_GET['plan'] == 2) {
            $update = array(
                'application_charge' => array(
                    'name' => 'Recurring Plan (Yearly)',
                    "price" => 300.0,
                    "return_url" => Url::toRoute('site/checkpayment?plan=3', 'https'),
                    "trial_days" => "0",
                )
            );
            $response = "";
            $response = $sc->call('POST', '/admin/application_charges.json', $update);

            if ($response && !(isset($response['errors']))) {
                echo '<script type="text/javascript">window.top.location.href = "' . $response['confirmation_url'] . '"; </script>';
                die;
            }
        }
        return $this->render('paymentplan');
    }

    public function actionPricing()
    {
        return $this->render('pricing');
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionError()
    {
        //die('ghfhfh');
        $exception = Yii::$app->errorHandler->exception;
        $error = Yii::$app->errorHandler->error;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception, 'error' => $error]);
        }
    }

    public function goHome()
    {
        //$url = \yii\helpers\Url::toRoute(['/walmart/site/index']);
        $url = Data::getUrl('site/index');
        return $this->redirect($url);
    }

    public function actionDeletecache()
    {
        $merchant_id = Yii::$app->user->identity->id;
        self::refreshcache($merchant_id);
        $url = Data::getUrl('site/index');
        return $this->redirect($url);
    }

    public static function refreshcache($merchant_id)
    {
        Yii::$app->cache->delete($merchant_id.'total_products');
        Yii::$app->cache->delete($merchant_id.'published_products');
        Yii::$app->cache->delete($merchant_id.'unpublished_products');
        Yii::$app->cache->delete($merchant_id.'staged_products');
        Yii::$app->cache->delete($merchant_id.'notuploaded_products');
        Yii::$app->cache->delete($merchant_id.'processing_products');
        Yii::$app->cache->delete($merchant_id.'deleted_products');
        Yii::$app->cache->delete($merchant_id.'last_refresh');
        return true;
    }

    public static function paymentMail($merchant_id,$planType)
    {
        $walmartShopDetail = Data::getWalmartShopDetails($merchant_id);
        $subject = "Thank you for making the payment";

        $mailData = [
            'sender' => 'shopify@cedcommerce.com',
            'reciever' => 'shivamverma@cedcoss.com',
            'email' => 'shivamverma@cedcoss.com',
            'subject' => $subject,
        ];
        $mailer = new Mail($mailData,'email/installmail.html','php',true);
        $mailer->sendMail();

    }
}
