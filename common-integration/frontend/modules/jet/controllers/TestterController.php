<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Installation;
use frontend\modules\jet\components\ShopifyClientHelper;

class TestterController extends Controller
{
    public function actionTest()
    {
      $connection=Yii::$app->getDb();
     
      $query="SELECT `merchant_id`  FROM `jet_shop_details`";
      $emailtemp = $connection->createCommand($query)->queryAll();
      
      $query="SELECT * FROM `jet_email_template`";
      $email = Data::sqlRecords($query,"all");
     
      foreach ($email as $key => $value) {
        $emailConfiguration['email/'.$value['template_title']] = isset($value["template_title"])?1:0;
      }

      if(!empty($emailConfiguration)) {
          foreach ($emailtemp as $temp => $tempdata) {
            foreach ($emailConfiguration as $key => $value) {
              Data::jetsaveConfigValue($tempdata['merchant_id'], $key, $value);
            }
        }
      }
    }

    public function actionIndex()
    {
      Installation::completeInstallationForOldMerchants('14');
      die('hete');
    }
    public function actionGetshopdetails(){
        $connection = Yii::$app->getDb();
        $jet_app_key = "5c6572757797b3edb02915535ce47d11";
        $jet_app_secret = "50110e94818b0399dc08d3c4daf2dbb5";
        $wal_app_key = "9734f2dc206eacd36b36ece7f020091a";
        $wal_app_secret = "eecac101ee8e176bdc541f9aa04936f4";
        $shop_type = isset($_GET['type'])?trim($_GET['type']):"jet";
        $mid = $_GET['mid'];
        $sql = ($shop_type=="walmart")?"Select `token`, `shop_url` from `walmart_shop_details` where `merchant_id`=".$mid:"Select `auth_key` as `token`, `username` as `shop_url` from `user` where `id`=".$mid;
        echo $sql;echo "<hr/>";
        $result = $connection->createCommand($sql)->queryOne();
        echo "<pre>";print_r($result);echo "</pre>";echo "<hr/>";
        $shopname = $result ['shop_url'];
        $token = $result ['token'];
        if($shop_type=='jet'){
            $sc = new ShopifyClientHelper($shopname, $token, $jet_app_key, $jet_app_secret);
        }else{
            $sc = new ShopifyClientHelper($shopname, $token, $wal_app_key, $wal_app_secret);
        }
        $shop = $sc->call('GET', '/admin/shop.json');
        echo "<pre>";print_r($shop);echo "</pre>";die("uu");
    }
    public function actionUpdatedetails(){
        $connection = Yii::$app->getDb();
        $sql = "SELECT `usr`.`id`, `usr`.`username` as `shop_url`, `usr`.`auth_key` as `jet_token`, `jet`.`id` as `jet_id`, `jet`.`name` as `jet_name`, `jet`.`email` as `jet_email`, `jet`.`mobile` as `jet_mobile`, `jet`.`agreement` as `jet_agreement`, `mart`.`id` as `mart_id`, `mart`.`token` as `mart_token`, `wreg`.`id` as `wreg_id`, `wreg`.`email` as `wreg_email`, `wreg`.`mobile` as `wreg_mobile`, `wreg`.`name` as `wreg_name`, `wreg`.`agreement` as `wreg_agreement` From `user` as `usr` LEFT JOIN `jet_registration` as `jet` ON `usr`.`id`=`jet`.`merchant_id` LEFT JOIN `walmart_shop_details` as `mart` ON `usr`.`id`=`mart`.`merchant_id` LEFT JOIN `walmart_registration` as `wreg` ON `usr`.`id`=`wreg`.`merchant_id`  order by `usr`.`id` asc";
        $result = $connection->createCommand($sql)->queryAll();
        //echo "<pre>";print_r($result); echo "</pre>";die();
        $jet_app_key = "5c6572757797b3edb02915535ce47d11";
        $jet_app_secret = "50110e94818b0399dc08d3c4daf2dbb5";
        $wal_app_key = "9734f2dc206eacd36b36ece7f020091a";
        $wal_app_secret = "eecac101ee8e176bdc541f9aa04936f4";
        $i = 0;
        $limit = 100;
        $get = isset($_GET['indx'])?$_GET['indx']:1;
        echo "Total : ".count($result)."<hr/>";
        foreach ($result as $rows) {
          try{
            $i++;
            if ($i<$get) {
              continue;
            }
            if ($i>($limit+$get-1)){
              break;
            }
            echo "Count -".$i."<br/>";
            $shopDetails = [];
            $retrieved = false;
            $shopname = $rows ['shop_url'];
            if(!empty($rows['jet_token'])){
                $token = trim($rows ['jet_token']);
                if (isset($rows['jet_mobile']) && $rows['jet_mobile']==='0'){
                    $rows['jet_mobile'] = "";
                }
                if (empty($rows ['jet_name']) || empty($rows ['jet_email']) || empty($rows ['jet_mobile'])) {// || empty($rows ['jet_agreement'])
                    // retrieve shop data from shopify
                      //var_dump($rows ['jet_name']);
                      //var_dump($rows ['jet_email']);
                      //var_dump($rows ['jet_mobile']);
                     $sc = new ShopifyClientHelper($shopname, $token, $jet_app_key, $jet_app_secret);
                     $shop = $sc->call('GET', '/admin/shop.json');
                     //var_dump($shop);die();
                     if (!$shop || (is_array($shop) && count($shop)==0)) {
                        continue;
                     }
                     $retrieved = true;

                      $email = "";
                      $mobile = "";
                      $name = "";
                      if (isset($rows['jet_name']) && $rows['jet_name']!=""){
                          $name = $rows['jet_name'];
                      }
                      if (isset($rows['jet_email']) && $rows['jet_email']!=""){
                          $email = $rows['jet_email'];
                      }
                      if (isset($rows['jet_mobile']) && $rows['jet_mobile']!=""){
                          $mobile = $rows['jet_mobile'];
                      }
                      if (isset($shop['shop_owner'])){
                          $name = trim($shop['shop_owner']);
                      }
                      if (isset($shop['email'])){
                          $email = trim($shop['email']);
                      }
                      if (isset($shop['phone'])){
                          $mobile = trim($shop['phone']);
                      }
                      if(!empty($rows['jet_id'])){

                          $sql = "Update `jet_registration` SET `email`='".$email."', `mobile`='".$mobile."', `name`='".$name."' where `merchant_id`=".$rows ['id'];//, `agreement`='1'
                          echo $sql; echo "<hr/>";
                          $connection->createCommand($sql)->execute();
                      }else{
                          //insert in jet_reg
                          $sql = "Insert into `jet_registration` (`merchant_id`, `name`, `mobile`, `email`, `agreement`) VALUES ( '".$rows ['id']."', '".$name."', '".$mobile."', '".$email."' , '0' )";
                          echo $sql; echo "<hr/>";
                          $connection->createCommand($sql)->execute();
                      }
                }
                
            }
            if (!empty($rows['mart_token'])){
                $token = trim($rows ['mart_token']);
                if (isset($rows['wreg_mobile']) && $rows['wreg_mobile']==='0'){
                    $rows['wreg_mobile'] = "";
                }
                if (empty($rows ['wreg_name']) || empty($rows ['wreg_email']) || empty($rows ['wreg_mobile'])) {
                    // retrieve shop data from shopify
                    if(!$retrieved){
                         $sc = new ShopifyClientHelper($shopname, $token, $wal_app_key, $wal_app_secret);
                         $shop = $sc->call('GET', '/admin/shop.json');
                         if (!$shop || (is_array($shop) && count($shop)==0)) {
                            continue;
                         }
                    }

                    $email = "";
                    $mobile = "";
                    $name = "";
                    if (isset($rows['wreg_name']) && $rows['wreg_name']!=""){
                        $name = $rows['wreg_name'];
                    }
                    if (isset($rows['wreg_email']) && $rows['wreg_email']!=""){
                        $email = $rows['wreg_email'];
                    }
                    if (isset($rows['wreg_mobile']) && $rows['wreg_mobile']!=""){
                        $mobile = $rows['wreg_mobile'];
                    }
                    if (isset($shop['shop_owner']) && $shop['shop_owner']!=""){
                        $name = trim($shop['shop_owner']);
                    }
                    if (isset($shop['email'])){
                        $email = trim($shop['email']);
                    }
                    if (isset($shop['phone'])){
                        $mobile = trim($shop['phone']);
                    }
                    if (!empty($rows['wreg_id'])) {
                        //update in wal_reg
                        $sql = "Update `walmart_registration` SET `email`='".$email."', `mobile`='".$mobile."', `name`='".$name."' where `merchant_id`=".$rows ['id'];//, `agreement`='1'
                        echo $sql; echo "<hr/>";
                        $connection->createCommand($sql)->execute();
                    }else {
                        //insert in wal_reg
                        $sql = "Insert into `walmart_registration` (`merchant_id`, `name`, `mobile`, `email`, `agreement`) VALUES ( '".$rows ['id']."', '".$name."', '".$mobile."', '".$email."' , '0' )";
                        echo $sql; echo "<hr/>";
                        $connection->createCommand($sql)->execute();
                    }
                }
                
            }
          }catch(\yii\db\Exception $e){
            echo "Exception : ".$e->getMessage();
           } 
           catch(\Exception $e){
              echo "Exception : ".$e->getMessage();
           }    
        }
       
    }

    public function actionUpdateinsallation(){
        $result = [];
        $connection = Yii::$app->getDb();
        $sql = "Select `merchant_id` from `walmart_registration` where `merchant_id` NOT IN (Select `merchant_id` from `walmart_installation`) and `merchant_id` NOT IN (Select `merchant_id` from `walmart_configuration` where `consumer_id`!='' and `secret_key`!='')";
        $result = $connection->createCommand($sql)->queryAll();
        echo count($result). "<hr/>";
        $i = 1;
        foreach ($result as $rows) {
            $sql = "Insert into `walmart_installation` (`merchant_id`, `status`, `step`) VALUES ( '".$rows ['merchant_id']."', 'pending', '1' )";
            echo "Step {$i} : ".$sql; echo "<hr/>";
            $connection->createCommand($sql)->execute();
            $i ++;
        }
        die("hell");
    }
    public function actionTestplan(){
    try{
      $offerArr = [];
      $sc = new ShopifyClient(SHOP, TOKEN, SHOPIFY_APP_KEY, SHOPIFY_APP_SECRET);
      $update = array(
                      'recurring_application_charge'=>array(
                          'name' => '<p style="color:red;">Recurring Plan (Monthly) with free 30 days</p>',
                          "price" => 29.0,
                          "return_url" => Data::getUrl('api/startplan','https', $offerArr),
                          //"trial_days" => 60,
                          'test' => true,
                          'capped_amount' => 100,
                          "terms"=> "<b>$1 for 1000 emails</b>",
                          'billing_on'=> date('Y-m-d', strtotime("+3 days"))
                      )
          );
          $response = "";
          $response = $sc->call('POST','/admin/recurring_application_charges.json',$update);
          if($response && !(isset($response['errors']))){
              echo '<script type="text/javascript">window.top.location.href = "'.$response['confirmation_url'].'"; </script>';
              die;
          }
    }
    catch(Exception $e){
      echo $e->getMessage();die('hhh');
    }
    
  }

  public function actionStartplan(){
    $sc = new ShopifyClient(SHOP, TOKEN, SHOPIFY_APP_KEY, SHOPIFY_APP_SECRET);
        $isPayment = false;
        var_dump($_GET['charge_id']);echo "<hr/>";
        if(isset($_GET['charge_id']))
        {
            $response = "";
            $response = $sc->call('GET','/admin/recurring_application_charges/'.$_GET['charge_id'].'.json');
            var_dump($response);echo "<hr/>";
            if(isset($response['id']) && $response['status']=="accepted")
            {
              $response ['billing_on'] = date('Y-m-d', strtotime("+10 days"));
                $isPayment = true;
                // $response=[];
                $response = $sc->call('POST','/admin/recurring_application_charges/'.$_GET['charge_id'].'/activate.json',$response);
                var_dump($response);echo "<hr/>";die();
                if(is_array($response) && count($response)>0)
                {

                }
            }
        }
  }

  public function actionUpdateplan(){
    /*string(7) "5848543" array(19) 
    { ["id"]=> int(5848543) ["name"]=> string(68) "

  Recurring Plan (Monthly) with free 30 days
  " ["api_client_id"]=> int(1661889) ["price"]=> string(5) "49.00" ["status"]=> string(8) "accepted" ["return_url"]=> string(63) "https://localhost/yii-app/index.php/shopifymobile/api/startplan" ["billing_on"]=> NULL ["created_at"]=> string(25) "2017-08-03T06:26:20-04:00" ["updated_at"]=> string(25) "2017-08-03T06:27:16-04:00" ["test"]=> bool(true) ["activated_on"]=> NULL ["trial_ends_on"]=> NULL ["cancelled_on"]=> NULL ["trial_days"]=> int(60) ["capped_amount"]=> string(6) "100.00" ["balance_used"]=> float(0) ["balance_remaining"]=> float(100) ["risk_level"]=> float(0) ["decorated_return_url"]=> string(81) "https://localhost/yii-app/index.php/shopifymobile/api/startplan?charge_id=5848543" } 
  array(19) { ["id"]=> int(5848543) ["name"]=> string(68) "

  Recurring Plan (Monthly) with free 30 days
  " ["api_client_id"]=> int(1661889) ["price"]=> string(5) "49.00" ["status"]=> string(6) "active" ["return_url"]=> string(63) "https://localhost/yii-app/index.php/shopifymobile/api/startplan" ["billing_on"]=> string(10) "2017-10-02" ["created_at"]=> string(25) "2017-08-03T06:26:20-04:00" ["updated_at"]=> string(25) "2017-08-03T06:27:23-04:00" ["test"]=> bool(true) ["activated_on"]=> string(10) "2017-08-03" ["trial_ends_on"]=> string(10) "2017-10-02" ["cancelled_on"]=> NULL ["trial_days"]=> int(60) ["capped_amount"]=> string(6) "100.00" ["balance_used"]=> float(0) ["balance_remaining"]=> float(100) ["risk_level"]=> float(0) ["decorated_return_url"]=> string(81) "https://localhost/yii-app/index.php/shopifymobile/api/startplan?charge_id=5848543" } 
    */

  /*string(7) "5849431" array(19) { ["id"]=> int(5849431) ["name"]=> string(68) "

Recurring Plan (Monthly) with free 30 days
" ["api_client_id"]=> int(1661889) ["price"]=> string(5) "29.00" ["status"]=> string(8) "accepted" ["return_url"]=> string(63) "https://localhost/yii-app/index.php/shopifymobile/api/startplan" ["billing_on"]=> NULL ["created_at"]=> string(25) "2017-08-03T08:08:11-04:00" ["updated_at"]=> string(25) "2017-08-03T08:08:20-04:00" ["test"]=> bool(true) ["activated_on"]=> NULL ["trial_ends_on"]=> NULL ["cancelled_on"]=> NULL ["trial_days"]=> int(0) ["capped_amount"]=> string(6) "100.00" ["balance_used"]=> float(0) ["balance_remaining"]=> float(100) ["risk_level"]=> float(0) ["decorated_return_url"]=> string(81) "https://localhost/yii-app/index.php/shopifymobile/api/startplan?charge_id=5849431" } array(19) { ["id"]=> int(5849431) ["name"]=> string(68) "

Recurring Plan (Monthly) with free 30 days
" ["api_client_id"]=> int(1661889) ["price"]=> string(5) "29.00" ["status"]=> string(6) "active" ["return_url"]=> string(63) "https://localhost/yii-app/index.php/shopifymobile/api/startplan" ["billing_on"]=> string(10) "2017-08-03" ["created_at"]=> string(25) "2017-08-03T08:08:11-04:00" ["updated_at"]=> string(25) "2017-08-03T08:08:30-04:00" ["test"]=> bool(true) ["activated_on"]=> string(10) "2017-08-03" ["trial_ends_on"]=> string(10) "2017-08-03" ["cancelled_on"]=> NULL ["trial_days"]=> int(0) ["capped_amount"]=> string(6) "100.00" ["balance_used"]=> float(0) ["balance_remaining"]=> float(100) ["risk_level"]=> float(0) ["decorated_return_url"]=> string(81) "https://localhost/yii-app/index.php/shopifymobile/api/startplan?charge_id=5849431" } 
      */$sc = new ShopifyClient(SHOP, TOKEN, SHOPIFY_APP_KEY, SHOPIFY_APP_SECRET);
      $chargeId = 5849431;//"5848543";
      $update = array(
                        'usage_charge' => array(
                            'description' => 'Recurring Plan (Monthly) with free 30 days',
                            "price" => 5.0,
                         )
        );
          $response = "";
          $response = $sc->call('POST','/admin/recurring_application_charges/'.$chargeId.'/usage_charges.json',$update);
          var_dump($response);die('HH');
          /*array(8) { ["id"]=> int(621923) ["description"]=> string(68) "

      Recurring Plan (Monthly) with free 30 days
      " ["price"]=> string(5) "51.00" ["created_at"]=> string(25) "2017-08-03T06:40:16-04:00" ["billing_on"]=> string(10) "2017-10-02" ["balance_used"]=> float(51) ["balance_remaining"]=> float(49) ["risk_level"]=> float(0) } */
          /*array(8) { ["id"]=> int(622301) ["description"]=> string(42) "Recurring Plan (Monthly) with free 30 days" ["price"]=> string(4) "5.00" ["created_at"]=> string(25) "2017-08-03T08:11:17-04:00" ["billing_on"]=> string(10) "2017-08-03" ["balance_used"]=> float(5) ["balance_remaining"]=> float(95) ["risk_level"]=> float(0) }*/
          if($response && !(isset($response['errors']))){

          }

  }
}


