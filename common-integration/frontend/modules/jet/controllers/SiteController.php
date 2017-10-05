<?php
namespace frontend\modules\jet\controllers;

use common\models\AppStatus;
use common\models\LoginForm;
use common\models\Post;
use common\models\User;
use common\models\MerchantDb;
use frontend\modules\jet\components\Createwebhook;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Installation;
use frontend\modules\jet\components\Jetappdetails;
use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\models\JetRegistration;
use frontend\components\Webhook;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use frontend\modules\jet\components\Sendmail;

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
     * @inheritdoc
     */
    protected $sc;
    public function beforeAction($action)
    {
        $this->layout = 'main';                       
        if(!\Yii::$app->user->isGuest && (!defined('MERCHANT_ID') || (MERCHANT_ID !=Yii::$app->user->identity->id ) ) ) 
        {
            define("MERCHANT_ID",Yii::$app->user->identity->id);
            define("SHOP",Yii::$app->user->identity->username);
            define("TOKEN",Yii::$app->user->identity->auth_key);
            $this->sc = new ShopifyClientHelper(SHOP, TOKEN, PUBLIC_KEY, PRIVATE_KEY);
        }
        return true;            
    }
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup', 'tester'],
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
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

    public function actionStartsession(){
        header('P3P:CP="CAO IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        session_start();
        $referrer = Url::toRoute(['site/login', 'shop'=>$_GET['sp']],'https');
        echo '<script>top.location = "'.$referrer.'";</script>';die();
    }

    public function actionIndex()
    {
        $obj=new Jetappdetails();        
        $session = Yii::$app->session;
        date_default_timezone_set('Asia/Kolkata');// Setting local timezone
        //save session id of user in user table
        if(!\Yii::$app->user->isGuest) 
        {
            $token = TOKEN;
            $username = SHOP;
            $id = MERCHANT_ID;
            if($username && $token=='')
            {

                return $this->redirect('http://apps.shopify.com/jet-integration');
            }

            if($obj->appstatus($username)==false)
            {
                $msg='Please install app to continue jet integration for your shop store';
                Yii::$app->session->setFlash('error',$msg);
                return $this->redirect(['logout']);
            }              
            //get shop name
            if(!isset($session['shop_details']))
            {
                $storeInformation=$obj->getStoreInformation($id);
                if(isset($storeInformation['shop_owner']))
                {
                   $session->set('shop_details', $storeInformation);
                }
            }
            $queryString = '';
            $shop = Yii::$app->request->get('shop',false);
            
            if($shop)
                $queryString = '?shop='.$shop;

            //Code By Himanshu Start
            Installation::completeInstallationForOldMerchants(MERCHANT_ID);
            $installation = Installation::isInstallationComplete(MERCHANT_ID);
            if($installation) 
            {
                if($installation['status'] == Installation::INSTALLATION_STATUS_PENDING) {
                    $step = $installation['step'];
                    $this->redirect(Yii::getAlias('@webjeturl').'/jet-install/index'.$queryString);
                    return false;
                }
            } 
            else 
            {
                $step = Installation::getFirstStep();
                $this->redirect(Yii::getAlias('@webjeturl').'/jet-install/index'.$queryString);
                return false;
            }
            if($obj->isValidateapp($id)=="expire" || $obj->isValidateapp($id)=="not purchase")
            {
                return $this->redirect(['paymentplan']);
            }
            return $this->render('index');          
        }
        else
        {
            $model = new LoginForm(); 
                return $this->render('index-new',[
                    'model' => $model,
                    ]);
        }   
    }

    public function actionGuide()
    {
        Yii::$app->view->registerMetaTag([
            'name' => 'description',
            'content' => 'Jet-Shopify Integration Documentation provides seamless integration for shopify, Sell products on Jet.com, Jet Integration app for shopify users. You can install this app in few easy steps of installation.'
                
        ],"main_index"); //this will now replace the default one.
    
            return $this->render('guide');
    }
   
    /*
    * this login action for Login from Admin
    */
    public function actionManagerlogin()
    {
        $merchant_id = isset($_GET['ext']) ? $_GET['ext'] :false;
        $authorizeAction = array('site/managerlogin'=>true);
        $allowedIp = array('182.72.248.90'=>true,'127.0.0.1'=>true,'103.85.140.162'=>true);
        if(!isset($_GET['enter'])){
            if(isset($authorizeAction[Yii::$app->controller->id.'/'.Yii::$app->controller->action->id])){
                if(!preg_match('/(192.168.0.)\w+/', $_SERVER['REMOTE_ADDR']) && !isset($allowedIp[$_SERVER['REMOTE_ADDR']])){
                    return $this->redirect(['login']);
                }
            }
        }
        if($merchant_id)
        {
            $result=User::findOne($merchant_id);            
            if($result)
            {
                $session ="";
                $session = Yii::$app->session;
                $session->remove('shop_details');
                $model = new LoginForm();
                $model->login($result->username);
                return $this->redirect(['index']);
            }            
            else
            {
                die("User Does not exist!!!");
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
    
    public function actionLogin()
    {   
        if(!\Yii::$app->user->isGuest && Yii::$app->user->identity->auth_key!='') 
        {
            $this->redirect('http://apps.shopify.com/jet-integration');
        } 
        $model = new LoginForm(); 
        if ($model->load(Yii::$app->request->post()))
        {        
            /**validation code for domains in cedcommerce***/
            $domain_name=trim($_POST['LoginForm']['username']);
   
            if(preg_match('/http/',$domain_name))
            {
                $domain_url = preg_replace("(^https?://)", "", $domain_name );//removes http from domain_name
                $domain_url=rtrim($domain_url, "/"); // Removes / from last
            }
            else{
                $domain_url=$domain_name;
            }
            $shop=isset($domain_url) ? $domain_url : $_GET['shop'];
            
            define("SHOP",$shop);
            
            $shopifyClient = new ShopifyClientHelper(SHOP, "", JET_APP_KEY,JET_SECRET_KEY);

            // get the URL to the current page
            $pageURL = 'http';
            if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") 
            { 
                $pageURL .= "s"; 
            }
            $pageURL .= "://";
           
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            if(SHOP){// if($_GET['shop']){
                $urlshop=[];
                $urlshop=parse_url($pageURL);
                $pageURL=$urlshop['scheme']."://".$urlshop['host'].$urlshop['path'];
                $pageURL = rtrim($pageURL, "/");
            }

            /* Code For Referral Start */
            if($ref_code = Yii::$app->request->post('ref_code', false)) {
                $session = Yii::$app->session;
                $session->set('ref_code', $ref_code);
            }
            /* Code For Referral End */
       
            $url=parse_url($shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
            if(checkdnsrr($url['host'], 'A')) {
                header("Location: " . $shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
                exit;
            }
            else
            {
                 return $this->render('index-new', [
                'model' => $model,
                ]);
            }
            
        } 
        elseif(isset($_GET['code'])) 
        {  //if(!defined('SHOP')){
                define("SHOP",$_GET['shop']);
            //}
            $shopifyClient = new ShopifyClientHelper(SHOP, "", JET_APP_KEY, JET_SECRET_KEY);
            $token = $shopifyClient->getAccessToken($_GET['code']);

            //if(!defined('TOKEN')){
                define("TOKEN",$token);
            //}
            if ($token != '')
            {
                $sc = new ShopifyClientHelper(SHOP, $token, JET_APP_KEY, JET_SECRET_KEY);
                //Createwebhook::createNewWebhook($sc,SHOP,$token);
                // entry in User table
                $merchant_id="";
                $shopName = SHOP;
                /**
                * Insert Data in `merchant_db` table
                */
                $merchantModel = new MerchantDb();
                $merchant = $merchantModel->find()->where(['shop_name' => $shopName])->one();

                if (!$merchant) {
                    $merchantModel->shop_name = $shopName;
                    $merchantModel->db_name = Yii::$app->getCurrentDb();
                    $merchantModel->app_name = Data::APP_NAME_JET;
                    $merchantModel->save(false);
                    $merchant_id = $merchantModel->merchant_id;
                } else {
                    $merchant->app_name = $merchant->app_name. ',' .Data::APP_NAME_JET;
                    $merchant->save(false);

                    $merchant_id = $merchant['merchant_id'];
                }

                $result=User::find()->where(['username' => SHOP])->one();
                $shopData = $sc->call('GET','/admin/shop.json');
                $shop_name = $email = $country_code = $currency = "";
                if(!isset($shopData['errors']))
                {
                    $shop_name = $shopData['name'];
                    $shop_email = $shopData['email'];
                    $country_code = $shopData['country_code']; 
                    $currency = $shopData['currency'];
                }
                if(!$result)
                {
                    $newModel = new User();
                    $newModel->id = $merchant_id;
                    $newModel->username = SHOP;
                    $newModel->auth_key = $token;
                    //$newModel->shop_name = $shop_name;
                    $newModel->save(false);
                    //$merchant_id = $newModel->id;
                }
                elseif($result->auth_key!=$token)
                {
                    $result->auth_key=$token;
                    $result->save(false);
                    //$merchant_id = $result->id;
                }
                $model->login(SHOP);
                if($merchant_id)
                {
                    /* Code For Referral Start */
                    $session = Yii::$app->session;
                    if ($ref_code = $session->get('ref_code')) {
                        $session->remove('ref_code');

                        $query = "SELECT `id`,`merchant_id` FROM `referrer_user` WHERE `code` LIKE '{$ref_code}'";
                        $referrer = Data::sqlRecords($query, 'one');

                        $appName = 'jet';
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

                    $emailConfigCheck="SELECT * FROM `jet_config` WHERE `merchant_id`='".$merchant_id."' AND data LIKE'email/%'";
                    $emailConfigCheckdata = Data::sqlRecords($emailConfigCheck,"all","select");
                    $query="SELECT * FROM `jet_email_template`";
                    $email = Data::sqlRecords($query,"all","select");
                    if(empty($emailConfigCheckdata))
                    {                                        
                        foreach ($email as $key => $value) {
                            $emailConfiguration['email/'.$value['template_title']] = isset($value["template_title"])?1:0;
                        }
                        if(!empty($emailConfiguration))
                        {
                            foreach ($emailConfiguration as $key => $value) 
                            {
                                Data::jetsaveConfigValue($merchant_id, $key, $value);
                            }
                        }
                    }
                    else
                    {
                        foreach ($emailConfigCheckdata as $key1 => $value1) 
                        {
                            foreach ($email as $key => $value) 
                            {
                                $emailTitle = str_replace('email/', '',$value1['data']);
                                if(trim($value["template_title"])==trim($emailTitle)){
                                  $emailConfiguration['email/'.$emailTitle] =0;
                                  break;                          
                                }
                                else{
                                  $emailConfiguration['email/'.$emailTitle] =1;                              
                                }
                            }
                        }
                        if(!empty($emailConfiguration))
                        {
                            foreach ($emailConfiguration as $key => $value) 
                            {
                               
                                if($value=='1'){
                                    Data::jetsaveConfigValue($merchant_id, $key, $value);
                                }
                            }                            
                        }
                    }
                    //save information in jet_shop_details
                    Jetappdetails::saveJetShopDetails($merchant_id,SHOP,$shop_name,$shop_email,$country_code,$currency,$shopData);
                    Webhook::createWebhooks(SHOP); // Creating Webhook
                }  
                $cookies = Yii::$app->request->cookies;
                if ($cookies->getValue('redirect_url') !== null) {
                        $redirectData = $cookies->getValue('redirect_url');
                    }
                Yii::$app->getResponse()->getCookies()->remove($cookies['redirect_url']);
                if(isset($redirectData)){
                    return $this->redirect([$redirectData]);   
                }
                else{
                  return $this->redirect(['index?shop='.SHOP]);
                }
            } 
        }
        elseif(isset($_GET['shop']))
        {

            //if(!defined('SHOP')){
                define("SHOP",$_GET['shop']);
            //}
            $shopDetails=Data::sqlRecords("SELECT install_status FROM `jet_shop_details` WHERE shop_url='".SHOP."' LIMIT 0,1","one","select");
            if(isset($shopDetails['install_status']) && $shopDetails['install_status']==1)
            {
                $model->login(SHOP);
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
                        if (count($_COOKIE) === 0) {
                            if (!array_key_exists("PHPSESSID",$_COOKIE)){
                                    $referrer = Url::toRoute(['site/startsession', 'sp'=>$_GET['shop']],'https');
                                    echo '<script> top.location = "'.$referrer.'";</script>';die();
                            }
                        }
                }
                return $this->redirect(['index?shop='.SHOP]);
            }
            else
            {

                $domain_name=trim(SHOP);
                if(preg_match('/http/',$domain_name))
                {
                    $domain_url = preg_replace("(^https?://)", "", $domain_name );//removes http from domain_name
                    $domain_url=rtrim($domain_url, "/"); // Removes / from last
                }
                else{
                    $domain_url=$domain_name;
                }
                $shop=isset($domain_url) ? $domain_url : SHOP;
                $shopifyClient = new ShopifyClientHelper($shop, "",JET_APP_KEY, JET_SECRET_KEY);
        
                // get the URL to the current page
                $pageURL = 'http';
                if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
                {
                    $pageURL .= "s";
                }
                $pageURL .= "://";
                 
                if ($_SERVER["SERVER_PORT"] != "80") {
                    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
                } else {
                    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
                }
                if($_GET['shop']){
                    $urlshop=array();
                    $urlshop=parse_url($pageURL);
                    $pageURL=$urlshop['scheme']."://".$urlshop['host'].$urlshop['path'];
                    $pageURL = rtrim($pageURL, "/");
                }
                $url=parse_url($shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
                if(checkdnsrr($url['host'])){
                    header("Location: " . $shopifyClient->getAuthorizeUrl(SCOPE, $pageURL));
                    exit;
                }
                else
                {
                    return $this->render('index-new', [
                            'model' => $model,
                    ]);
                }
            }
        }
        else
        {
            return $this->render('index-new', [
                    'model' => $model,
            ]);
        }
    }

    /*raza*/
    public function actionApplyCoupan(){
        $merchant_id = MERCHANT_ID;
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
            $data['success'] = "coupan applied";
        }
        else{
            $data['error'] = "invalid coupan code";
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
            $response=$this->sc->call('POST','/admin/recurring_application_charges.json',$update);
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
        $merchant_id = MERCHANT_ID;
        $isPayment=false; 
        //print_r($_SESSION);die;  
        $Activate_plan_data = json_decode($_SESSION['plan_data']['Activate_plan_data'],true);
        $plan_data = json_encode($Activate_plan_data[0]['choose_plan_data']);
        $plan_time = $_SESSION['plan_data']['Activate_plan_time'];
        $charge_limit = array();
        $charge_limit['setup_charge'] = $_SESSION['plan_data']['setup_charge_check'];
        $charge_limit['product_limit'] = $Activate_plan_data[0]['product_count'];
        $charge_limit['order_limit'] = $Activate_plan_data[0]['product_count'];
        $charge_limit = json_encode($charge_limit);
        //print_r($_SESSION);die;
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
                    $query = 'SELECT `id` FROM `jet_recurring_payment` WHERE merchant_id="'.$merchant_id.'" LIMIT 0,1';

                    $recurring = Data::sqlRecords($query,"one","select");
                    if(empty($recurring))
                    {
                        $created_at=date('Y-m-d H:i:s',strtotime($response['created_at']));
                        $updated_at=date('Y-m-d H:i:s',strtotime($response['updated_at']));
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="INSERT INTO `jet_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type,charge_limit,choose_plan_data)
                                VALUES('".$_GET['charge_id']."','".$merchant_id."','".$created_at."','".$updated_at."','".$response['status']."','".json_encode($response)."','".$plan_time."','".$charge_limit."','".$plan_data."')";
                       
                        Data::sqlRecords($query,null,'insert');
                        //change data-time and status in jet-extension-details
                        $expire_date=date('Y-m-d H:i:s',strtotime('+'.$plan_time, strtotime($updated_at)));
                        $query="UPDATE jet_shop_details SET purchased_on='".$updated_at."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."' WHERE merchant_id='".$merchant_id."'";
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
                        $query="UPDATE `jet_recurring_payment` SET `id`= '".$_GET['charge_id']."' ,`plan_type`= '".$plan_time."' ,`choose_plan_data`= '".$plan_data."' , `billing_on`='".$created_at."',`activated_on`='".$updated_at."',`charge_limit`='".$charge_limit."',`status`='".$response['status']."',`recurring_data`='".json_encode($response)."' where merchant_id='".$merchant_id."'";                                
                        Data::sqlRecords($query,null,'update');                        
                         
                        $expire_date=date('Y-m-d H:i:s',strtotime('+'.$plan_time, strtotime($updated_at)));
                        $query="UPDATE jet_shop_details SET purchased_on='".$updated_at."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."' WHERE merchant_id='".$merchant_id."'";
                        Data::sqlRecords($query,null,'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '2');
                        /* Code For Referral End */
                    }
                }   
            }
            /*else
            {
                return $this->redirect(['paymentplan']);
            }*/
        }
        if($isPayment)
            if (isset($_SESSION['plan_data']['Activate_plan_coupan'])) {
                $query = "INSERT INTO `applied_coupan_code`( `merchant_id`, `used_on`, `coupan_code`) VALUES ('".$merchant_id."','JET','".$_SESSION['plan_data']['Activate_plan_coupan']."')";
                Data::sqlRecords($query,null,'insert');
            }
            Yii::$app->session->setFlash('success',"Congratulations! PAYMENT PLAN has been activated.");
            return $this->redirect(['index']);
    }
    /*end*/
    public function actionPaymentplan()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }      
        
        if(isset($_GET['plan']) && $_GET['plan']==1)
        {
            $update=array(
                'recurring_application_charge'=>array(
                    'name'=>'Recurring Plan (Monthly)',
                    "price"=> 40,
                    "return_url"=> Url::toRoute('site/checkpayment','https'),
                )
            );
            $response="";
            $response=$this->sc->call('POST','/admin/recurring_application_charges.json',$update);

            if($response && !(isset($response['errors']))){
                echo '<script type="text/javascript">window.top.location.href = "'.$response['confirmation_url'].'"; </script>';
                die;
            }
        }
        elseif(isset($_GET['plan']) && $_GET['plan']==2)
        {
            $update=array(
                'application_charge'=>array(
                    'name'=>'Recurring Plan (Yearly)',
                    "price"=> 299.0,
                    "return_url"=> Url::toRoute('site/checkyrpayment','https'),
                    "trial_days"=>"0",
                )
            );            
            $response="";
            $response=$this->sc->call('POST','/admin/application_charges.json',$update);
            if($response && !(isset($response['errors']))){
                echo '<script type="text/javascript">window.top.location.href = "'.$response['confirmation_url'].'"; </script>';
                die;
            }
        }
        elseif(isset($_GET['plan']) && $_GET['plan']==3)
        {
            $update=array(
                'application_charge'=>array(
                    'name'=>'Recurring Plan (Half Yearly)',
                    "price"=> 180.0,
                    "return_url"=> Url::toRoute('site/checkhalfyrpayment','https'),
                    "trial_days"=>"0",
                )
            );
            $response="";
            $response=$this->sc->call('POST','/admin/application_charges.json',$update);
            
            if($response && !(isset($response['errors']))){
                echo '<script type="text/javascript">window.top.location.href = "'.$response['confirmation_url'].'"; </script>';
                die;
            }
        }
        //return $this->render('pricing');
        return $this->render('paymentplan');
    }
    public function actionCheckpayment()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = MERCHANT_ID;
        $isPayment=false;
        if(isset($_GET['charge_id']))
        {
            $response = "";
            $response = $this->sc->call('GET','/admin/recurring_application_charges/'.$_GET['charge_id'].'.json');
            if(isset($response['id']) && $response['status']=="accepted")
            {
                $isPayment=true;
                // $response=[];
                $response=$this->sc->call('POST','/admin/recurring_application_charges/'.$_GET['charge_id'].'/activate.json',$response);
                if(is_array($response) && count($response)>0)
                {
                    $recurring = [];
                    $recurring=Data::sqlRecords('select `id` from `jet_recurring_payment` where merchant_id="'.$merchant_id.'" AND `plan_type`="Recurring Plan (Monthly)"','one','select');
                    if(empty($recurring))
                    {
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="insert into `jet_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('".$_GET['charge_id']."','".$merchant_id."','".$response['billing_on']."','".$response['activated_on']."','".$response['status']."','".json_encode($response)."','Recurring Plan (Monthly)')";
                        Data::sqlRecords($query,null,'insert');
                        //change data-time and status in jet-extension-details
                         
                        $expire_date=date('Y-m-d H:i:s',strtotime('+35 days', strtotime($response['activated_on'])));
                        $query="UPDATE jet_shop_details SET purchased_on='".$response['activated_on']."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."'  where merchant_id='".$merchant_id."'";                        
                        Data::sqlRecords($query,null,'update');    

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '1');
                        /* Code For Referral End */                    
                    }
                    else
                    {
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="UPDATE `jet_recurring_payment` SET `id`= '".$_GET['charge_id']."' , `billing_on`='".$response['billing_on']."',`activated_on`='".$response['activated_on']."',`status`='".$response['status']."',`recurring_data`='".json_encode($response)."' where merchant_id='".$merchant_id."' AND `plan_type`='Recurring Plan (Monthly)'";                                
                        Data::sqlRecords($query,null,'update');                        
                         
                        $expire_date=date('Y-m-d H:i:s',strtotime('+35 days', strtotime($response['activated_on'])));
                        $query="UPDATE jet_shop_details SET purchased_on='".$response['activated_on']."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."'  where merchant_id='".$merchant_id."'";
                        Data::sqlRecords($query,null,'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '1');
                        /* Code For Referral End */
                    }
                }
            }else{
                return $this->redirect(['paymentplan']);
            }
        }
        if($isPayment)
            Yii::$app->session->setFlash('success',"Congratulations! Your MONTHLY PLAN has been activated successfully");
        return $this->redirect(['index']);
    }
    public function actionCheckyrpayment()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = MERCHANT_ID;
        $isPayment=false;        
        if(isset($_GET['charge_id']))
        {
            $response="";
            $response=$this->sc->call('GET','/admin/application_charges/'.$_GET['charge_id'].'.json');
            if(isset($response['id']) && $response['status']=="accepted")
            {
                $isPayment=true;
                // $response = []
                $response=$this->sc->call('POST','/admin/application_charges/'.$_GET['charge_id'].'/activate.json',$response);
                if(is_array($response) && count($response)>0)
                {
                    $recurring = [];
                    $query = 'SELECT `id` FROM `jet_recurring_payment` WHERE merchant_id="'.$merchant_id.'" AND `plan_type`="Recurring Plan (Yearly)" LIMIT 0,1';
                    $recurring = Data::sqlRecords($query,"one","select");
                    if(empty($recurring))
                    {
                        $created_at=date('Y-m-d H:i:s',strtotime($response['created_at']));
                        $updated_at=date('Y-m-d H:i:s',strtotime($response['updated_at']));
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="insert into `jet_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('".$_GET['charge_id']."','".$merchant_id."','".$created_at."','".$updated_at."','".$response['status']."','".json_encode($response)."','Recurring Plan (Yearly)')";
                        Data::sqlRecords($query,null,'insert');
                        //change data-time and status in jet-extension-details
                        $expire_date=date('Y-m-d H:i:s',strtotime('+1 year', strtotime($updated_at)));
                        $query="UPDATE jet_shop_details SET purchased_on='".$updated_at."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."' WHERE merchant_id='".$merchant_id."'";
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
                        $query="UPDATE `jet_recurring_payment` SET `id`= '".$_GET['charge_id']."' , `billing_on`='".$created_at."',`activated_on`='".$updated_at."',`status`='".$response['status']."',`recurring_data`='".json_encode($response)."' where merchant_id='".$merchant_id."' AND `plan_type`='Recurring Plan (Yearly)'";                                
                        Data::sqlRecords($query,null,'update');                        
                         
                        $expire_date=date('Y-m-d H:i:s',strtotime('+1 year', strtotime($updated_at)));
                        $query="UPDATE jet_shop_details SET purchased_on='".$updated_at."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."' WHERE merchant_id='".$merchant_id."'";
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
            Yii::$app->session->setFlash('success',"Congratulations! Your YEARLY PLAN has been activated.");
        return $this->redirect(['index']);
    }
    public function actionCheckhalfyrpayment()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id=MERCHANT_ID;
        
        $isPayment=false;
        if(isset($_GET['charge_id']))
        {
            $response="";
            $response=$this->sc->call('GET','/admin/application_charges/'.$_GET['charge_id'].'.json');
            if(isset($response['id']) && $response['status']=="accepted")
            {
                $isPayment=true;
                // $response=[];
                $response=$this->sc->call('POST','/admin/application_charges/'.$_GET['charge_id'].'/activate.json',$response);
                if(is_array($response) && count($response)>0)
                {
                    $recurring=[];
                    $recurring=Data::sqlRecords('select `id` from `jet_recurring_payment` where `merchant_id`="'.$merchant_id.'" AND `plan_type`="Recurring Plan (Half Yearly)" ','one','select');
                    if(empty($recurring))
                    {
                        $created_at=date('Y-m-d H:i:s',strtotime($response['created_at']));
                        $updated_at=date('Y-m-d H:i:s',strtotime($response['updated_at']));
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="insert into `jet_recurring_payment`
                                (id,merchant_id,billing_on,activated_on,status,recurring_data,plan_type)
                                values('".$_GET['charge_id']."','".$merchant_id."','".$created_at."','".$updated_at."','".$response['status']."','".json_encode($response)."','Recurring Plan (Half Yearly)')";
                        Data::sqlRecords($query,null,'insert');
                        //change data-time and status in jet-extension-details
                        $expire_date=date('Y-m-d H:i:s',strtotime('+180 days', strtotime($updated_at)));
                        $query="UPDATE jet_shop_details SET purchased_on='".$updated_at."',expired_on='".$expire_date."' ,purchase_status='".Data::PURCHASED."' where merchant_id='".$merchant_id."'";
                        Data::sqlRecords($query,null,'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '3');
                        /* Code For Referral End */
                    }
                    else
                    {
                        $created_at=date('Y-m-d H:i:s',strtotime($response['created_at']));
                        $updated_at=date('Y-m-d H:i:s',strtotime($response['updated_at']));
                        $response['timestamp']=date('d-m-Y H:i:s');
                        $query="UPDATE `jet_recurring_payment` SET `id`= '".$_GET['charge_id']."' , `billing_on`='".$created_at."',`activated_on`='".$updated_at."',`status`='".$response['status']."',`recurring_data`='".json_encode($response)."' where merchant_id='".$merchant_id."' AND `plan_type`='Recurring Plan (Yearly)'";                                
                        Data::sqlRecords($query,null,'update');                        
                         
                        $expire_date=date('Y-m-d H:i:s',strtotime('+180 days', strtotime($updated_at)));
                        $query="UPDATE jet_shop_details SET purchased_on='".$updated_at."',expired_on='".$expire_date."',purchase_status='".Data::PURCHASED."' WHERE merchant_id='".$merchant_id."'";
                        Data::sqlRecords($query,null,'update');

                        /* Code For Referral Start */
                        self::approveReferralPayment($merchant_id, $_GET['charge_id'], $response, '3');
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
            Yii::$app->session->setFlash('success',"Congratulations! Your HALF YEARLY PLAN has been activated.");
        return $this->redirect(['index']);
    }

    /* Code For Referral Start */
    public function approveReferralPayment($merchant_id, $paymentId, $paymentData, $planId)
    {
        $appName = 'jet';
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

    public function actionPricing()
    {
        return $this->render('pricing');
    }
    public function actionRegister()
    {
        $model = new JetRegistration();
        $merchant_id = MERCHANT_ID;
        if ($_POST) 
        {                       
            $data = Yii::$app->request->post();
            $data['JetRegistration']['shipping_source'] = json_encode($data['JetRegistration']['shipping_source']);
            $data['JetRegistration']['merchant_id'] = $merchant_id;
            if ($model->load($data) && $model->save()) 
            {
                Yii::$app->session->setFlash('success',"Congratulations! Registration has been copmpleted.");
                return $this->redirect('index');           
            }
        }
        else
        {           
            return $this->render('register', [
                'model' => $model,
            ]);
        }
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['index']);
    }    
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        $error=Yii::$app->errorHandler->error;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception, 'error'=>$error]);
        }
    }
    public function actionActivatePayment()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        if(!$this->sc){
            $this->sc = new ShopifyClientHelper(SHOP, TOKEN, PUBLIC_KEY, PRIVATE_KEY);
        }
        $handle=Data::createFile('jet/activate-payment/'.MERCHANT_ID.'.log');
        fwrite($handle, PHP_EOL.date('d-m-Y H:i:s').PHP_EOL."check actiavted payment".PHP_EOL);
        if(isset($_GET['plan']))
        {
            $charge_response=[];
            if($_GET['plan']=='yearly' || $_GET['plan']=='half')
            {
                fwrite($handle, "check yearly/halfyearly payment".PHP_EOL);
                $charge_response=$this->sc->call('GET','/admin/application_charges.json?status=expired',['status'=>'expired']);  
            }
            elseif($_GET['plan']=='monthly')
            {
                fwrite($handle, "check monthly payment".PHP_EOL);
                $charge_response=$this->sc->call('GET','/admin/recurring_application_charges.json');
            }
            if(is_array($charge_response) && count($charge_response)>0)
            {
                foreach ($charge_response as $value) 
                {
                    if($value['status']=="accepted")
                    {
                        fwrite($handle, "accepted payment data.".print_r($value,true).PHP_EOL);
                        if($value['name']=="Recurring Plan (Yearly)")
                        {
                            //redirect to yearly plan
                            return $this->redirect(['checkyrpayment?charge_id='.$value['id']]);
                        }
                        elseif($value['name']=="Recurring Plan (Half Yearly)")
                        {
                            return $this->redirect(['checkhalfyrpayment?charge_id='.$value['id']]);
                        }
                        else
                        {
                            return $this->redirect(['checkpayment?charge_id='.$value['id']]);
                        }
                    }
                }
            }
        }
        return $this->redirect(['index']);
    }
    public function actionSchedulecall()
    {
        $this->layout = "main2";

        $html = $this->render('schedulecall');
        return $html;
    }
    public function actionRequestcall()
    {
    	if (!empty($_POST))
    	{
    		$merchant_id = MERCHANT_ID;
    		$date = date("d-m-Y H:i:s");

    		$preffered_time = $_POST['time']." ".$_POST['format'];
    		$number = $_POST['number'];
    		$preffered_date = $_POST['date'];
    		$timeZone = $_POST['time_zone'];
    		$emailid = Data::sqlRecords("select email from jet_shop_details where merchant_id=".$merchant_id,'one','select');
    		$call_record = Data::sqlRecords("SELECT * FROM `call_schedule` WHERE `merchant_id`= '".$merchant_id."' AND `marketplace`='".Data::MARKETPLACE."' AND `number`= '".$number."'","one","select");
    		if(!empty($call_record) && $call_record['number'] == $number)
    		{
    			$call_record['no_of_request'] += 1;
    			$query = "UPDATE `call_schedule` SET `no_of_request`='".$call_record['no_of_request']."',`status` = '".Data::CALL_SCHEDULE_STATUS."',`preferred_date`='".$preffered_date."',`preferred_timeslot`='".$preffered_time."'";
    			Data::sqlRecords($query,null,'update');
    		}else
    		{
    			$query = "INSERT INTO `call_schedule` (`merchant_id`,`number`, `shop_url`,`marketplace`,`status`,`time`,`no_of_request`,`preferred_date`,`time_zone`,`preferred_timeslot`) VALUES ('".$merchant_id."','".$number."','".SHOP."','".Data::MARKETPLACE."','".Data::CALL_SCHEDULE_STATUS."','".$date."','".Data::NO_OF_REQUEST."','".$preffered_date."','".$timeZone."','".$preffered_time."')";
    	
    			Data::sqlRecords($query,null,'insert');
    		}
    		if (isset($emailid['email']))
    			Sendmail::callSchedule($emailid['email']);
    		$response = ['success' => true, 'message' => 'Submitted Successfully '];
    	
    		return json_encode($response);
    	}
    }
    public function actionDiscountoffer()
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(Yii::getAlias('@webjeturl').'/site/index');
    	}
    	return $this->render('newpaymentplan');
    }
    public function actionNewplan() {
    	if(isset($_GET['plan']) && $_GET['plan']==2)
    	{
    		$update=array(
    				'application_charge'=>array(
    						'name'=>'Recurring Plan (Yearly)',
    						"price"=> 270.0,
    						"return_url"=> Url::toRoute('site/checkyrpayment','https'),
    						"trial_days"=>"0",
    						//'test'=>true,
    				)
    		);
    		$response="";
    		$response=$this->sc->call('POST','/admin/application_charges.json',$update);
    		if($response && !(isset($response['errors']))){
    			echo '<script type="text/javascript">window.top.location.href = "'.$response['confirmation_url'].'"; </script>';
    			die;
    		}
    	}
    }
}
