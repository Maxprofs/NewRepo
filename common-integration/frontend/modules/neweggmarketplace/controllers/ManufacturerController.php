<?php

namespace frontend\modules\neweggmarketplace\controllers;

use yii\web\Controller;
use frontend\modules\neweggmarketplace\controllers;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\components\Neweggapi;
use yii\helpers\BaseJson;
use Yii;
use frontend\modules\neweggmarketplace\components\product\Productimport;
use frontend\modules\neweggmarketplace\components\ShopifyClientHelper;
use frontend\modules\neweggmarketplace\components\product\ProductPrice;
use frontend\modules\neweggmarketplace\components\product\ProductInventory;
use frontend\modules\neweggmarketplace\components\Mail;
use frontend\modules\neweggmarketplace\models\NeweggManufacturerSearch;
use frontend\modules\neweggmarketplace\models\NeweggManufacturer;
use yii\data\ActiveDataProvider;


class ManufacturerController extends NeweggMainController
{
    /**
     * Manufacturer  grid authentication
     *
    */

    public function actionIndex(){
        $searchModel = new NeweggManufacturerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionSyncmanufacturer()
    {
        $manufacturerCollection=Data::sqlRecords("SELECT `manufacturer_name` FROM `newegg_manufacturer` WHERE merchant_id=".MERCHANT_ID." and status='".Data::MANUFACTURER_PENDING_STATUS."'","all","select");
        foreach ($manufacturerCollection as $key => $value) {
            $jsonData = [];
            $requestBody = [];
            $jsonData['OperationType']='GetManufacturerRequest';

            $requestBody['RequestCriteria']=self::preparesyncjson($value);
            $jsonData['RequestBody']=$requestBody;
            $postData= json_encode($jsonData);
            $obj = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
            $response = $obj->putRequest('/contentmgmt/manufacturer/manufacturerinfo',['body' => $postData]);
            $server_output = json_decode($response,true);

            if(isset($server_output['ResponseBody']['ManufacturerList']) && !empty($server_output['ResponseBody']['ManufacturerList'])){
                $query = 'UPDATE `newegg_manufacturer` SET  status="'.Data::MANUFACTURER_COMPLETE_STATUS.'" where merchant_id="'.MERCHANT_ID.'" ';
                Data::sqlRecords($query,null,'update');
            }
            
        }
         $searchModel = new NeweggManufacturerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
       

    }

        public static function preparejson($data){
            $array = [];
            $array['Name']=$data['mn'];
            if(isset($data['mn']) && !empty($data['mu'])){
                $array['URL']=$data['mu'];
            }
            if(isset($data['mn']) && !empty($data['mu'])){
                $array['SupportEmail']=$data['mse'];
            }
            if(isset($data['mn']) && !empty($data['mu'])){
                $array['SupportPhone']=trim($data['msp']);
            }
            if(isset($data['mn']) && !empty($data['mu'])){
                $array['SupportURL']=$data['msu'];
            }
            return $array;

        }

           public static function preparesyncjson($data){
            $array = [];
            $array['ManufacturerName']=$data['manufacturer_name'];
            return $array;

        }

        public function actionCreatemanufacturer(){
              if ($postData = Yii::$app->request->post()) {
            $jsonData = [];
            $requestBody = [];
            $jsonData['OperationType']='SubmitManufacturerRequest';
            $requestBody['ManufacturerRequest']=self::preparejson($postData);
            $jsonData['RequestBody']=$requestBody;
            $postData= json_encode($jsonData);
            $obj = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
            $response = $obj->postRequest('/contentmgmt/manufacturer/creationrequest',['body' => $postData]);
            $server_output = json_decode($response,true);
            if(isset($server_output['IsSuccess']) && !empty($server_output['IsSuccess'])){
                if($server_output['IsSuccess']==1){
                    try{
                        $mn=$mu=$mse=$msp=$msu="";
                        if(isset($requestBody['ManufacturerRequest']['Name'])){
                            $mn = $requestBody['ManufacturerRequest']['Name'];
                        }
                        if(isset($requestBody['ManufacturerRequest']['URL'])){
                            $mu = $requestBody['ManufacturerRequest']['URL'];
                        }
                        if(isset($requestBody['ManufacturerRequest']['SupportEmail'])){
                            $mse = $requestBody['ManufacturerRequest']['SupportEmail'];
                        }
                        if(isset($requestBody['ManufacturerRequest']['SupportPhone'])){
                            $msp = $requestBody['ManufacturerRequest']['SupportPhone'];
                        }
                        if(isset($requestBody['ManufacturerRequest']['SupportURL'])){
                            $msu = $requestBody['ManufacturerRequest']['SupportURL'];
                        }
                        $status = Data::MANUFACTURER_PENDING_STATUS;
                        $manufacturerCollection=Data::sqlRecords("SELECT `manufacturer_name` FROM `newegg_manufacturer` WHERE merchant_id=".MERCHANT_ID." and manufacturer_name='".$mn."'","one","select");
                        if(empty($manufacturerCollection)){
                            $model = Data::sqlRecords("INSERT INTO `newegg_manufacturer` (`merchant_id`,`manufacturer_name`,`manufacturer_url`,`manufacturer_support_email`,`manufacturer_support_phone`,`manufacturer_support_url`,`status`) VALUES ('".MERCHANT_ID."','".$mn."','".$mu."','".$mse."','".$msp."' , '".$msu."' , '".$status."')", 'all','insert');
                                 $msg['success']="Manufacturer Successfully Created";
                                $data = json_encode($msg);
                                echo $data;
                                die;
                        }
                        else{
                                $query = 'UPDATE `newegg_manufacturer` SET  manufacturer_name="'.$mn.'",manufacturer_url="'.$mu.'",manufacturer_support_email="'.$mse.'",manufacturer_support_phone="'.$msp.'",manufacturer_support_url="'.$msu.'","status="'.$status.'" where merchant_id="'.MERCHANT_ID.'" ';
                                Data::sqlRecords($query,null,'update');
                                $msg['success']="Manufacturer Successfully Created";
                                $data = json_encode($msg);
                                echo $data;
                                die;
                            }
                    
                    }
                    catch (Exception $e)
                    {
                        echo $e->Message();
                        die;
                    }
                    
                }
            }
            else{
                if(isset($server_output['Message'])){
                   $msg['error']= $server_output['Message'];
                   $data = json_encode($msg);
                    echo $data; 
                    die;
                }
                
            }

            }

            return $this->render('createmanufacturer');
        }
        /*
        action for view manufacturer info
        */
       public function actionView($id){
          return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
       }

        protected function findModel($id)
        {
            if (($model = NeweggManufacturer::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }

}

