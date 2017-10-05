<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Mail;
use frontend\modules\jet\components\Sendmail;

use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\components\Shopifyinfo;
use frontend\modules\jet\models\JetConfiguration;

class TestController extends Controller
{


    public function actionTestMail(){
      
        $mailer = new Mail(['sender'=>'satyaprakash@cedcoss.com','reciever'=>'ankitsingh1436@gmail.com','email'=>'satyaprakash@cedcoss.com','merchant_id'=>'14','subject'=>'21dec/againTesting'],'email/order.html','php',true);
        $today = date("d/m/y");
        $connection=Yii::$app->getDb();
        $query ="INSERT INTO `email_report` (`merchant_id`,`send_at`,`mail_status`,`email_template_path`) VALUES ('14','".$today."','send','email/order.html')";
        $model = $connection->createCommand($query)->execute();
        $id = $connection->getLastInsertID();
        $mailer->setTracking($id);
        $mailer->sendMail();
    }

    public function actionEmailforproductupdate()
  {
    
    
    $data = array (
  'id' => '4211750342',
  'title' => 'New1 Engagement Rings',
  'body_html' => '

How appropriate this natural fancy yellow pear shape is a pleasing golden color. Indeed the GIA certified, SI1 gorgeous brilliance of the center stone, combined with 38 round VS1-VS2 near colorless pave diamonds in a slender and delicate setting is the ultimate combination of gracefulness and flashy. Shiny, elegant and unique craftsmanship in polished platinum or 18K white gold provide a lifetime of enjoyment for the special lady who calls this ring her own. If you wish to purchase the center 1.50 ct. pear-shaped natural fancy yellow diamond (GIA certified) please contact us by calling our toll free number: (888) 530-5212.

This ring includes a GIA Report for the center stone.

This setting is also available in 18K White Gold also with a polished finish.
 
 We manufacture all of our pieces in-house in Los Angeles which allows us to have full control over the entire creation process.

              

Once you have selected your ideal setting, we are more than happy to find the perfect center stone.  Please give us a call at (888) 530-5212 or email at info@clevereve.com and we will locate the perfect stone for you right here in the jewelry district in downtown Los Angeles. 



    Total Carat Weight:0.34


    Average Color:G-H


    Average Clarity:VS1-VS


    Number of Stones:38


    Type of Side-Stones:Round


    Setting Style(s):Pave




    Order By 5:00PM Pacific Standard Time


    And Your Order Ships Overnight Within 3 Days

    Automatic expiration check: The meter will not allow you to test samples using an expired test strip or a damaged test 

    Benefits Arthritis Users: The ACCU-CHEK Aviva test strips and ACCU-CHEK Multiclix lancing device are part of the first complete system to receive the Arthritis Foundation\'s Ease-Of-Use Commendation. The system has an easy-to-use grip and helps you test right the first time.4 In independent lab testing, the ACCU-CHEK Aviva system was found to help people living with arthritis check their blood sugar easily

    Easy to Use: Simply place a test strip into the ACCU-CHEK Aviva glucose meter, place a drop of blood in the yellow window and wait for your results!

    Includes:

    100 Accu-Chek Aviva Diabetic Test Strips

    Notes & Specifications:

    Accu-Check Aviva Test Strips are designed to be used with the Accu-Chek Aviva Plus Glucose Meter

    Preferred on most healthcare plan formularies, so you may save money.
    Package includes 100 test strips
    For Testing Glucose in Whole Blood".30 days


',
  'vendor' => 'Cedcommerce',
  'product_type' => 'Engagement Ring',
  'created_at' => '2015-12-29T07:25:46-05:00',
  'handle' => '1-50-ct-fancy-yellow-pear-shaped-diamond-ring-in-platinum-22k-yellow-gold-0-34-ct-tw',
  'updated_at' => '2016-11-23T08:07:46-05:00',
  'published_at' => '2015-12-29T07:25:00-05:00',
  'published_scope' => 'global',
  'tags' => '',
  'variants' => 
  array (
    0 => 
    array (
      'id' => '21083941126',
      'product_id' => '4211750342',
      'title' => 'steal',
      'price' => '5.00',
      'sku' => 'beauty_ring',
      'position' => '1',
      'grams' => '50',
      'inventory_policy' => 'deny',
      'compare_at_price' => '13010.00',
      'fulfillment_service' => 'manual',
      'inventory_management' => 'shopify',
      'option1' => 'steal',
      'created_at' => '2016-06-08T10:03:01-04:00',
      'updated_at' => '2016-11-23T08:07:46-05:00',
      'taxable' => '1',
      'barcode' => 'ytutgu',
      'inventory_quantity' => '10',
      'weight' => '0.05',
      'weight_unit' => 'kg',
      'old_inventory_quantity' => '10',
      'requires_shipping' => '1',
    ),
    1 => 
    array (
      'id' => '21140046726',
      'product_id' => '4211750342',
      'title' => 'gold',
      'price' => '2.00',
      'sku' => 'beauty_ring1',
      'position' => '2',
      'grams' => '280050',
      'inventory_policy' => 'deny',
      'compare_at_price' => '13010.00',
      'fulfillment_service' => 'manual',
      'inventory_management' => 'shopify',
      'option1' => 'gold',
      'created_at' => '2016-06-09T08:50:50-04:00',
      'updated_at' => '2016-11-23T08:07:46-05:00',
      'taxable' => '1',
      'barcode' => '154874585498',
      'inventory_quantity' => '5',
      'weight' => '280.05',
      'weight_unit' => 'kg',
      'old_inventory_quantity' => '5',
      'requires_shipping' => '1',
    ),
  ),
  'options' => 
  array (
    0 => 
    array (
      'id' => '5147174726',
      'product_id' => '4211750342',
      'name' => 'Metal',
      'position' => '1',
      'values' => 
      array (
        0 => 'steal',
        1 => 'gold',
      ),
    ),
  ),
  'images' => 
  array (
    0 => 
    array (
      'id' => '12942956038',
      'product_id' => '4211750342',
      'position' => '1',
      'created_at' => '2016-06-10T10:48:40-04:00',
      'updated_at' => '2016-10-24T11:44:09-04:00',
      'src' => 'https://cdn.shopify.com/s/files/1/1009/2336/products/il_340x270.862053495_asnx.jpeg?v=1477323849',
    ),
    1 => 
    array (
      'id' => '18871313036',
      'product_id' => '4211750342',
      'position' => '2',
      'created_at' => '2016-09-21T06:48:46-04:00',
      'updated_at' => '2016-09-26T07:59:41-04:00',
      'src' => 'https://cdn.shopify.com/s/files/1/1009/2336/products/EIV1vJb_b0f85f3c-6f5e-4b1d-b0be-8be807a47595.jpg?v=1474891181',
    ),
    2 => 
    array (
      'id' => '18871315212',
      'product_id' => '4211750342',
      'position' => '3',
      'created_at' => '2016-09-21T06:48:50-04:00',
      'updated_at' => '2016-10-31T11:44:28-04:00',
      'src' => 'https://cdn.shopify.com/s/files/1/1009/2336/products/extn_no.png?v=1477928668',
    ),
    3 => 
    array (
      'id' => '18871317644',
      'product_id' => '4211750342',
      'position' => '4',
      'created_at' => '2016-09-21T06:48:58-04:00',
      'updated_at' => '2016-11-21T10:17:25-05:00',
      'src' => 'https://cdn.shopify.com/s/files/1/1009/2336/products/system_clean.png?v=1479741445',
    ),
  ),
  'image' => 
  array (
    'id' => '12942956038',
    'product_id' => '4211750342',
    'position' => '1',
    'created_at' => '2016-06-10T10:48:40-04:00',
    'updated_at' => '2016-10-24T11:44:09-04:00',
    'src' => 'https://cdn.shopify.com/s/files/1/1009/2336/products/il_340x270.862053495_asnx.jpeg?v=1477323849',
  ),
  'shopName' => 'ced-jet.myshopify.com',
);

    $date =  date('Y-m-d H:i:s');
      $productinfo =[];
      $query = 'select jet.email,jet.merchant_id from `jet_shop_details` jet INNER JOIN `user` user ON user.id=jet.merchant_id where user.username ="'.$data['shopName'].'" limit 0,1';
            $allData = Data::sqlRecords($query, 'one');
            $productThresoldValue = 25;
            if (!isset($allData['email']) && empty($allData['email']))
            {
                $productThresoldValue = 25;
                $query="SELECT `email`,`merchant_id` FROM `walmart_shop_details` WHERE shop_url='".$data['shopName']."'";
                $allData = Data::sqlRecords($query, 'one');
                $email = $allData['email'];
            }
            else{
              $email = $allData['email'];
            }
            if(isset($email)){
                    $templatedata = '';
                    foreach ($data['variants'] as $key => $value) {
                                if($productThresoldValue > $value['inventory_quantity']){
                                        $templatedata .= '<tr><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
                                            .$data['title'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
                                            .$value['sku'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
                                            .$value['inventory_quantity'].'</td></tr>';
                                            $productinfo[] = $value['sku'];
                                            
                                }
                               
                            }
        if(!empty($productinfo)){

                  $model = Data::sqlRecords("SELECT * FROM `jet_notifications` WHERE `merchant_id`='".$allData['merchant_id']."' AND `status`=1 AND `product_id`='".$data['id']."'" , 'one');
                  
                  if(empty($model)){
                    
                      $i = 1;
                $date = strtotime($date . " +".$i."days");
                $date = date('Y-m-d H:i:s',$date);
                $productSku = implode(",",$productinfo);
                $query ="INSERT INTO `jet_notifications` (`merchant_id`,`product_id`,`child_sku`,`date`,`type`,`status`) VALUES ('".$allData['merchant_id']."',".$data['id'].",'".$productSku."','".$date."','for inventry update','1')";
                        $model = Data::sqlRecords($query, 'all','insert');
                      Sendmail::productStockMail($email,$templatedata);
                      return true;
                  }
                  else{
                    $currentDate =  date('Y-m-d H:i:s');
                    $dbchildsKu = explode(",",$model['child_sku']);
                    sort($dbchildsKu);
                    sort($productinfo);
                    if($dbchildsKu != $productinfo){
                      if(strtotime($model['date']) < strtotime($currentDate)){
                        $templatedata = '';
                          foreach ($data['variants'] as $key => $value) {
                                      if($productThresoldValue > $value['inventory_quantity']){ 
                                        foreach ($dbchildsKu as $key => $val) {
                                        if($val != $value['sku'])
                                          {
                                                $templatedata .= '<tr><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
                                                    .$data['title'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
                                                    .$value['sku'].'</td><td style="color: #7d7d7d;font-family: arial;font-size: 14px;padding-left:3px;padding-right:3px;padding-top:7px;padding-bottom:7px;text-align: center;">'
                                                    .$value['inventory_quantity'].'</td></tr>';
                                                    $model = Data::sqlRecords("INSERT INTO `jet_notifications` (`merchant_id`,`product_id`,`status`,`date`,`type`) VALUES ('".$allData['merchant_id']."','".$value['product_id']."','1','".$date."','for productinventry update')", 'all','insert');
                                          }
                                           }
                                      }
                                     
                                  }
                          
                          Sendmail::productStockMail($email,$templatedata);
                          return true;
                      }
                    }
                  }
              }
        }
        return true;
}
    public function actionTrackingMail(){
     
        $file = Yii::getAlias('@webroot').'/jet/frontend/views/templates/email/productStockMail.html';
        //$matches[1] = $matches[1].'<img src="smiley.gif">';
      if($file){
        $html = file_get_contents($file);
        $html1 = preg_replace_callback(
              '/<body[^>]*>(.*?)<\/body>/is',
              function ($matches) {
                  return $this->_addhtmlImage($matches);
              },
              $html
          );
        print_r(htmlentities($html1));die;
        return $html1;
      }
        
        
     
    }


    /*function for adding image tag in template file*/
   private function _addhtmlImage($matches){
    $data = $matches[1].'<img src="smiley.gif">'; 
    return $data;
    
  }
  /* test action for report of sales data */
  /*test report id = 6dbd2fdec75a4d0d983d5e7db8552060*/

  /*HTTP/1.1 200 OK Cache-Control: private Content-Type: application/json Server: Microsoft-IIS/8.0 X-AspNet-Version: 4.0.30319 X-Powered-By: ASP.NET Date: Wed, 21 Dec 2016 08:05:04 GMT Content-Length: 55 Connection: keep-alive Set-Cookie: ARRAffinity=8642ddcd72d33f692310f6958bcc9510ccaba6b3a0c8c0e5eaab2632c7da4cce;Path=/;Domain=merchant-api-website.azurewebsites.net { "report_id": "6dbd2fdec75a4d0d983d5e7db8552060" }*/





  /*report stattus

HTTP/1.1 200 OK Cache-Control: private Content-Type: application/json Server: Microsoft-IIS/8.0 X-AspNet-Version: 4.0.30319 X-Powered-By: ASP.NET Date: Wed, 21 Dec 2016 08:13:49 GMT Content-Length: 639 Connection: keep-alive Set-Cookie: ARRAffinity=9fbff265a248ebe17d5349615696f46cb8c19d4f8cf4c1ab60493e8adb708f13;Path=/;Domain=merchant-api-website.azurewebsites.net { "merchant_id": "f0451564cc1d42998ee3e370a32a3f63", "report_id": "6dbd2fdec75a4d0d983d5e7db8552060", "report_requested_date": "2016-12-21T08:05:04.0777406+00:00", "report_status": "ready", "report_type": "SalesData", "report_expiration_date": "2016-12-22T08:05:05.1371882+00:00", "report_url": "https://prodimupload.blob.core.windows.net/merchant-reports/6dbd2fdec75a4d0d983d5e7db8552060.gz?sv=2015-04-05&sr=b&sig=whZC%2F90qaLsEO5y5yhy7nGR4CFfmYP2JjXWNJURZC4w%3D&se=2016-12-22T08%3A05%3A05Z&sp=r", "processing_end": "2016-12-21T08:05:05.1371882+00:00", "processing_start": "2016-12-21T08:05:04.3559156+00:00" }
  */


/*for valid client





*/
  public function actionReport(){
    //$url= 'https://merchant-api.jet.com/api/reports/state/4438ce2aef054cafacbbb9f9447336eb';
    $url= 'https://merchant-api.jet.com/api/reports/SalesData';
  
    $tObject =$this->Authorise_token('144');
    $postFields = '';
    $headers = array();
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer $tObject->id_token";
  
      
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    //curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);



    $server_output = curl_exec ($ch);
    print_r($server_output);die;
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($server_output, 0, $header_size);
    $body = substr($server_output, $header_size);
    curl_close ($ch);

    return $body;
  }


    public function Authorise_token($id)
  {
    //$merchant_id = \Yii::$app->user->identity->id;
    $merchant_id = $id;
    $result="";
    $result=JetConfiguration::find()->select('id,merchant_id,jet_token')->where(['merchant_id' => $merchant_id])->one();
    if($result->id){
      $Jtoken=json_decode($result->jet_token);
    }
    else{
      
      //Yii::$app->session->setFlash('error', 'API user & API password either or Invalid.Please set API user & API pass from jet configuration.');
      return false;
        //return $this->redirect(array("jetconfiguration/index"));
    }
    $refresh_token =false;
    
    if(is_object($Jtoken) && $Jtoken!=null){
      $ch = curl_init();
      $url= 'https://merchant-api.jet.com/api/authcheck';

      $headers = array();
      $headers[] = "Content-Type: application/json";
      $headers[] = "Authorization: Bearer $Jtoken->id_token";
  
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
      $server_output = curl_exec ($ch);
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $header = substr($server_output, 0, $header_size);
      $body = substr($server_output, $header_size);
      curl_close ($ch);
      
      $bjson = json_decode($body);
      
      if(is_object($bjson) &&
       $bjson->Message!='' &&
        $bjson->Message=='Authorization has been denied for this request.')
       {
        // refresh token  
        $refresh_token =true; 
         }    
    }
    else
    {
      $refresh_token =true; 
    }
    
    if($refresh_token){
      $token_data = $this->JrequestTokenCurl();
      if($token_data!= false){
        $result->jet_token=json_encode($token_data);
        $result->save();
        return $token_data;
      }else{
        //Yii::$app->session->setFlash('error', 'API user & API password either or Invalid.Please set API user & API pass from jet configuration.');
        //return $this->redirect(array("jetconfiguration/index"));
        return false;
      }   
    }else{
      return $Jtoken;
    }
  
  }

  public function JrequestTokenCurl(){
  
    $ch = curl_init();
    $url= 'https://merchant-api.jet.com/api/Token';
    $postFields='{"user":"85777776FDFA9A58CB7C5AE5549CF94EBB31E211","pass":"Gk2Whok05X7H317DbecCSIBkzJYB5GmMzHgzp8uCDs+P"}';
    
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json;"));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
  
    $server_output = curl_exec ($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($server_output, 0, $header_size);
    $body = substr($server_output, $header_size);
    curl_close ($ch);
    $token_data =json_decode($body);
    
    if(is_object($token_data) && isset($token_data->id_token)){
      //$data = new Mage_Core_Model_Config();
      //$data->saveConfig('jetcom/token', $body, 'default', 0);
      return json_decode($body);

    }
    else
    {
      return false;
    }
      
  }

/*usage recurring charge status*/
public function actionUsage(){

  $shopifymodel=Shopifyinfo::getShipifyinfo();
  $sc = new ShopifyClientHelper('leaps-rebounds.myshopify.com', 'e2f8f128cf8caace7d419e1028a2ca28', $shopifymodel[0]['api_key'], $shopifymodel[0]['secret_key']);
  $response=$sc->call('GET','/admin/recurring_application_charges/3656329/usage_charges.json');
  print_r($response);die("lll");

}
  
}