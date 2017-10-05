<?php

namespace backend\modules\walmart\controllers;

use Yii;
use frontend\modules\walmart\models\WalmartShopDetails;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartOrderDetail;
use frontend\modules\walmart\models\WalmartExtensionDetail;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use frontend\modules\walmart\components\Mail;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\JetOrderDetail;
use backend\modules\walmart\components\Data;
use backend\modules\walmart\components\Curlrequests;
use common\models\JetProduct;


/**
 * Base Controller.
 */
class BaseController extends Controller
{


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                     [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ], 
                    [
                        'actions' => ['logout', 'index','view','send-mail','mass','csv','send-mail','get-template','get-merchant-list','create', 'update', 'delete','campaigns'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-template' => ['POST'],
                    'send-mail' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

   public function actionLogin(){
        return $this->redirect(['//site/login']);
   }
    /**
     * Perform Mass Action.
     * @return mixed
     */
    public function actionMass()
    {
        $table = Data::EMAIL_TEMPLATE_TABLE;
        $shoptable = Data::SHOP_DETAILS;
        $registration = Data::REGISTRATION;
        $post = Yii::$app->getRequest()->post();
        if(isset($post['selection']) && count($post['selection'])){
            if($post['action']=='email'){
                $templates = Yii::$app->db->createCommand("SELECT * FROM `{$table}`")->queryAll();
                $post['templates'] = $templates;
                return $this->render('/installations/sendmail',['data'=>$post]);
            }
            elseif($post['action']=='mailchimp'){
                foreach ($post['selection'] as $key => $value) {
                    $merchant_data = Yii::$app->db->createCommand("SELECT *,`wr`.`fname`,`wr`.`lname` FROM `{$shoptable}` as main INNER JOIN `{$registration}` as wr ON `wr`.merchant_id=`main`.`merchant_id` WHERE `main`.`merchant_id`='".$value."'")->queryOne();
                    if(!empty($merchant_data)){
                        $memberId = md5(strtolower($merchant_data['email']));
                        $data = [
                        'email_address'     => $merchant_data['email'],
                        'status'    => 'subscribed',
                       /* 'merge_fields'  => [
                            'FNAME'     => $merchant_data['fname'],
                            'LNAME'     => $merchant_data['lname'],
                        ]*/
                        ];
                        $main = new Curlrequests();
                        $data = $main->postRequest('lists/'.$post['listData'].'/members/'.$memberId.'',$data);
                        if($data=='200'){

                        }
                        else{

                        }
                    }
                    else{

                    }

                }
                
            }
        }
        else
        {
            return $this->redirect(['index']);
        }
        
        
    }

    /*
     * Action for sending mail
     */
    
    public function actionSendMail()
    {

        try{
            $post = Yii::$app->getRequest()->post();
            $merchantIds = $post['merchant_ids'];
            $table = Data::SHOP_DETAILS;
            $table1 = Data::CONFIGURATION_TABLE;
            $table2 = Data::EMAIL_REPORT;
            if(!empty($post['merchant_ids'])){
                if($post['check']){
                    $this->updateTemplate($post['html_content'],$post['template']);
                }
                $merchantIds = explode(',',$merchantIds);
                $connection = Yii::$app->db;
                foreach($merchantIds as $merchantId){
                    $sql = "SELECT `jed`.`merchant_id` as `merchant_id`,`jed`.`shop_url` as `shopurl` ,`jed`.`email` as `email`,`jc`.`consumer_id` as `consumer_id`,`jc`.`secret_key` as `secret_key` FROM `{$table}` `jed` LEFT JOIN `{$table1}` `jc` ON `jc`.`merchant_id` = `jed`.`merchant_id` where `jed`.`merchant_id`={$merchantId}";
                    $data = $connection->createCommand($sql)->queryOne();
                    $data['reciever'] = $data['email'];
                    $data['sender'] = 'shopify@cedcommerce.com';
                    $data['subject'] = $post['subject'];
                    $date = date('Y-m-d');
                    $sql = "INSERT INTO `{$table2}`(`merchant_id`, `send_at`, `mail_status`, `email_template_path`) VALUES ({$merchantId},'{$date}','sending','{$post['template']}')";
                    $connection->createCommand($sql)->execute();
                    $id = $connection->getLastInsertID();
                    if(isset($post['html_content']) && !empty($post['html_content'])){
                        $data['html_content'] = $post['html_content'];
                    }
                    $mailer = new Mail($data,$post['template'],'php',true);
                    $mailer->setTracking($id);

                    if($mailer->sendMail())
                    {
                        $sql = "UPDATE `{$table2}` SET `mail_status`='sent' WHERE `tracking_id`={$id} ";
                        $connection->createCommand($sql)->execute();
                        if(!empty($post['email_cc'])){
                            $bccData = explode(",",$post['email_cc']);
                            foreach ($bccData as $key => $value) {
                                $data['reciever'] = $value;
                                $mailer = new Mail($data,$post['template'],'php',true);
                                $mailer->setTracking(false);
                                $mailer->sendMail();
                            }
                        }
                    }
                    else
                    {
                        $sql = "UPDATE `{$table2}` SET `mail_status`='failed' WHERE `tracking_id`={$id} ";
                        $connection->createCommand($sql)->execute();
                    }
                    
                }
                echo true;
            }
            else{
                echo false;
            }
          
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        
    }


    /*
     * Action for getting mail template content
     */
    public function actionGetTemplate(){
        $post = Yii::$app->getRequest()->post();
        echo file_get_contents(dirname(\Yii::getAlias('@webroot')).'/frontend/views/templates/'.$post['template']);
    }

    /*
     * List already send mail to the merchant
     */
    public function actionGetMerchantList(){
      $post = Yii::$app->getRequest()->post();
      /*$merchant_ids = explode(','$post['merchant_ids']);*/
      $table = Data::EMAIL_REPORT;
      $table1 = Data::SHOP_DETAILS;
      $query = "select jet.shop_url,jet.email,er.* from`{$table1}` jet INNER JOIN `{$table}` er ON er.merchant_id=jet.merchant_id where er.email_template_path ='".$post['template_name']."' and er.merchant_id IN (".$post['merchant_ids'].") GROUP BY er.merchant_id ";
      $sql1 = Yii::$app->db->createCommand($query);
        $merchantList = $sql1->queryAll();
        if($merchantList){
            echo '<span><b>Email Already sent to these Merchant</b><table class="table table-striped table-bordered"><thead>
            <tr><th>#</th><th>Merchant_Id</th><th>Shop Url</th><th>Email</th><th>Send Date</th><th>Read At</th></tr>
            </thead>
            <tbody>';
            foreach ($merchantList as $key => $value) {
                echo '<tr data-key="'.$value['merchant_id'].'" id ="'.$value['merchant_id'].'"><td><input type="checkbox" id="'.$value['merchant_id'].'" class="checkbox" name="'.$value['merchant_id'].'" value="'.$value['tracking_id'].'" checked></td><td>'.$value['merchant_id'].'</td><td>'.$value['shop_url'].'</td><td>'.$value['email'].'</td><td>'.$value['send_at'].'</td><td>'.$value['read_at'].'</td></tr>
                ';
            }
            echo '</tbody></table></span>';
        }
        
    }

    public function updateTemplate($templateData,$path){

      $file_dir = dirname(\Yii::getAlias('@webroot')).'/frontend/views/templates/';
      if (!file_exists($file_dir)){
          mkdir($file_dir,0775, true);
      }
      $filenameOrig="";
      $filenameOrig=$file_dir.'/'.$path;
      $fileOrig="";
      $fileOrig=fopen($filenameOrig,'w+');
      fwrite($fileOrig,$templateData);

      fclose($fileOrig);

    }
     /**
     * Export Csv.
     * @return mixed
     */
    public function actionCsv($id)
    {

         $connection = Yii::$app->db;
         $table = Data::EXTENSIONS_TABLE;
        $exportData = unserialize($id);
        $fields = [];
        foreach ($exportData as $key => $value) {
            if($value){
                   
                        $fields[] = sprintf("%s = '%s'",
                            $key, $value);
                    

                }
            }
            if (count($fields) > 0) {

                $whereClause = "WHERE " . implode(" AND ", $fields) ;
                $query = "select `merchant_id`,`status`,`app_status` from`{$table}` " . $whereClause ;
                $data = $connection->createCommand($query)->queryAll();
                foreach ($data as $key => $value) {
                    $result=WalmartShopDetails::find()->where(['merchant_id'=>$value['merchant_id']])->one();
                    $data[$key]['shop_url'] = $result['shop_url'];
                    $data[$key]['email'] = $result['email'];
                    $data[$key]['unpublished'] = count($result=WalmartProduct::find()->where(['status'=>'UNPUBLISHED','merchant_id'=>$value['merchant_id']])->all());

                    $data[$key]['published'] = count($result=WalmartProduct::find()->where(['status'=>'PUBLISHED','merchant_id'=>$value['merchant_id']])->all());
                   $data[$key]['published'] = count(WalmartOrderDetail::find()->where(['status'=>'completed','merchant_id'=>$value['merchant_id']])->all());
                    

                }
                   $fileName = "export_data" . time() . ".xls";

                header("Content-Disposition: attachment; filename=".$fileName."");
                header("Content-Type: application/vnd.ms-excel");

                $flag = false;
                foreach($data as $row) {
                    if(!$flag) {
                        // display column names as first row
                        echo implode("\t", array_keys($row)) . "\n";
                        $flag = true;
                    }
                    // filter data
                   //array_walk($row, 'filterData');
                    echo implode("\t", array_values($row)) . "\n";
                    
                    

                }
                
                exit;


                
            }
            else{
                $query = "select `merchant_id`,`status`,`app_status` from`{$table}` " ;
                $data = $connection->createCommand($query)->queryAll();
                foreach ($data as $key => $value) {
                 $result=WalmartShopDetails::find()->where(['merchant_id'=>$value['merchant_id']])->one();
                    $data[$key]['shop_url'] = $result['shop_url'];
                    $data[$key]['email'] = $result['email'];
                    $data[$key]['unpublished'] = count($result=WalmartProduct::find()->where(['status'=>'UNPUBLISHED','merchant_id'=>$value['merchant_id']])->all());

                    $data[$key]['published'] = count($result=WalmartProduct::find()->where(['status'=>'PUBLISHED','merchant_id'=>$value['merchant_id']])->all());
                   $data[$key]['published'] = count(WalmartOrderDetail::find()->where(['status'=>'completed','merchant_id'=>$value['merchant_id']])->all());
                    
                }
                $fileName = "export_data" . time() . ".xls";

                header("Content-Disposition: attachment; filename=".$fileName."");
                header("Content-Type: application/vnd.ms-excel");

                $flag = false;
                foreach($data as $row) {
                    if(!$flag) {
                        // display column names as first row
                        echo implode("\t", array_keys($row)) . "\n";
                        $flag = true;
                    }

                    // filter data
                   //array_walk($row, 'filterData');
                    echo implode("\t", array_values($row)) . "\n";
                    
                    

                }
                
                exit;
            }
            
        }
     function filterData(&$str)
    {

        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    }
        
    

}
