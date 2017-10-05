<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 19/1/17
 * Time: 5:34 PM
 */
namespace frontend\modules\neweggcanada\controllers;

use common\models\AppStatus;
use common\models\User;
use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\Helper;
use frontend\modules\neweggcanada\components\order\Orderdetails;
use frontend\modules\neweggcanada\components\Sendmail;
use frontend\modules\neweggcanada\models\NeweggOrderDetail;
use frontend\modules\neweggcanada\components\Neweggapi;
use frontend\modules\neweggcanada\components\product\ProductPrice;
use frontend\modules\neweggcanada\components\productinfo;
use frontend\modules\neweggcanada\components\product\Productimport;

use frontend\modules\neweggcanada\models\NeweggShopDetail;
use Yii;

use yii\base\Exception;
use yii\web\Controller;

class ShopifywebhookController extends Controller
{
    public function beforeAction($action)
    {
        if ($this->action->id == 'neweggproductcreate') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'neweggproductupdate') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'neweggproductdelete') {
            Yii::$app->controller->enableCsrfValidation = false;
        }

        if ($this->action->id == 'createshipment') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'isinstall') {
            Yii::$app->controller->enableCsrfValidation = false;
        }

        if ($this->action->id == 'createorder') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        if ($this->action->id == 'cancelled') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        return true;
    }


    public function actionCreateshipment()
    {
        $shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
        try
        {
            $webhook_content = '';
            $webhook = fopen('php://input' , 'rb');
            while(!feof($webhook)){
                $webhook_content .= fread($webhook, 4096);
            }
            $realdata="";
            $data=array();
            $orderData=array();
            fclose($webhook);
            $realdata=$webhook_content;
            if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
                return;
            }
            $data = json_decode($realdata,true); // ab ye data ka array hai

            if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
                $data['shopName']=$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
            if(isset($data['note']) && $data['note']=="Newegg-Integration(newegg.com)")
            {
                $url = Yii::getAlias('@webneweggurl')."/shopifywebhook/shipment";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch, CURLOPT_TIMEOUT,1);
                $result = curl_exec($ch);
                curl_close($ch);
                exit(0);
            }

        }
        catch(Exception $e){
            Helper::createExceptionLog('actioncreateshipment',$e->getMessage(),$shopName);
            exit(0);
        }
    }

    public function actionShipment()
    {
        Orderdetails::curlprocessfororder();
        if($_POST && isset($_POST['id']))
        {
            $shop=isset($_POST['shopName'])?$_POST['shopName']:"NA";
            $path = 'order/ship/webhook/'.$shop.'/'.date('d-m-Y').'/'.time().'.log';
            try
            {

                Orderdetails::curlprocessfororder($_POST);

            }
            catch(Exception $e)
            {
                Helper::createLog("order shipment error ".json_decode($_POST),$path,'a',true);
            }

        }
        else
        {
            Helper::createLog("order shipment error wrong post");
        }
    }

    /*public function actionCancelled()
    {

        $shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
        $log ='';
        $file_path = 'order/cancelorder/error/webhook/'.date('d-m-Y');
        $log = 'hello';
        $mode = 'a+';
        $log .= PHP_EOL.$shopName.PHP_EOL;

        Helper::createLog($log,$file_path,$mode);

        $webhook_content = '';
        $webhook = fopen('php://input' , 'rb');
        while(!feof($webhook)){
            $webhook_content .= fread($webhook, 4096);
        }
        $realdata="";
        $data=array();
        $orderData=array();
        fclose($webhook);
        $realdata = $webhook_content;
        if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
            return;
        }
        $data = json_decode($realdata,true);

        $log ='';

        $log .= PHP_EOL."[".date('d-m-Y H:i:s')."]\n";

        $log .= PHP_EOL.$realdata.PHP_EOL;

        Helper::createLog($log,$file_path,$mode);


        if($data && isset($data['id']))
        {

            $log .= PHP_EOL.'-------data-------'.PHP_EOL.$data.PHP_EOL.'------- end data ---------';

            $orderData=NeweggOrderDetail::find()->where(['shopify_order_id'=>$data['id'],'shopify_order_name'=>$data['name']])->one();

            $log .= PHP_EOL.'------ start order data -------'.PHP_EOL.$orderData.'-------- end order data --------';

            if($orderData)
            {
                $merchant_id=$orderData->merchant_id;

                $file_path = 'order/cancelorder/'.$merchant_id.'/webhook/'.date('d-m-Y');

                $log .= PHP_EOL.'-----order number------'.PHP_EOL.$orderData->order_number;

                $return = Orderdetails::cancelorder($orderData->order_number);

                $log .= PHP_EOL.'-----order response------'.PHP_EOL.$return;

                //Helper::createLog($log,$file_path);

            }else{
                $log .= PHP_EOL.'----- order data not found -----'.PHP_EOL;
            }
        }
        Helper::createLog($log,$file_path,$mode);
    }*/

    public function actionCancelled()
    {
        $shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
        $mode = 'a+';
        $merchant_id = Helper::getMerchantId($shopName);

        $file_path = 'order/cancelorder/'.$merchant_id['merchant_id'].'/webhook/'.date('d-m-Y');

        Helper::createLog($merchant_id['merchant_id'],$file_path,$mode);

        $webhook_content = '';
        $webhook = fopen('php://input' , 'rb');
        while(!feof($webhook)){
            $webhook_content .= fread($webhook, 4096);
        }
        $realdata="";
        $data=array();
        $orderData=array();
        fclose($webhook);
        $realdata = $webhook_content;
        if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
            return;
        }
        $data = json_decode($realdata,true);

        $log ='';

        $log .= PHP_EOL."[".date('d-m-Y H:i:s')."]\n";

        if($data && isset($data['id']))
        {

            $log .= PHP_EOL.'-------data-------'.PHP_EOL.json_encode($data).PHP_EOL.'------- end data ---------';

            Helper::createLog($log,$file_path,$mode);

            $orderData=NeweggOrderDetail::find()->where(['shopify_order_id'=>$data['id'],'shopify_order_name'=>$data['name'],'merchant_id' =>$merchant_id['merchant_id']])->one();

            $log .= PHP_EOL.'------ start order data -------'.PHP_EOL.json_encode($orderData).'-------- end order data --------';

            if($orderData)
            {

                $log .= PHP_EOL.'-----order number------'.PHP_EOL.$orderData->order_number;

                $return = Orderdetails::cancelorder($orderData->order_number);

                $log .= PHP_EOL.'-----order response------'.PHP_EOL.json_encode($return);

                Helper::createLog($log,$file_path);

            }
        }else{
            $log .= PHP_EOL.'------- Order Data Not Found -------'.PHP_EOL;
            Helper::createLog($log,$file_path,$mode);
        }
    }


    public function actionNeweggproductcreate(){
        $shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
        try{
            $webhook_content = '';
            $webhook = fopen('php://input' , 'rb');
            while(!feof($webhook)){
                $webhook_content .= fread($webhook, 4096);
            }
            $realdata="";
            $data=array();
            fclose($webhook);
            $realdata=$webhook_content;
            if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
                return;
            }
            $data = json_decode($realdata,true);// array of webhook data

            if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
                $data['shopName']=$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];

            $url = Yii::getAlias('@webneweggurl')."/shopifywebhook/curlproductcreate";
            //var_dump(http_build_query( $data ));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch, CURLOPT_TIMEOUT,1);
            $result = curl_exec($ch);
            curl_close($ch);
            exit(0);
        }
        catch(Exception $e){
            Helper::createExceptionLog('actionProductcreate',$e->getMessage(),$shopName);
            exit(0);
        }
    }

    public function actionCurlproductcreate()
    {
        $data = $_POST;
        if(isset($data['shopName']) && isset($data['id']))
        {
            try
            {
                $file_dir = \Yii::getAlias('@webroot').'/var/newegg/product/create/'.$data['shopName'].'/'.date('d-m-Y');
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0775, true);
                }
                $filenameOrig="";
                $filenameOrig=$file_dir.'/'.$data['id'].'.log';
                $fileOrig="";
                $fileOrig=fopen($filenameOrig,'w');
                fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($data));


                fclose($fileOrig);

                $connection = Yii::$app->getDb();
                $queryObj="";
                $result="";
                $query="SELECT `id` FROM `user` WHERE username='".$data['shopName']."'";
                $result = $connection->createCommand($query)->queryOne();

                if($result)
                {

                    $merchant_id=$result['id'];
                    $queryObj="";
                    $proresult="";
                    $prod_create_query="SELECT `id` FROM `jet_product` WHERE id='".$data['id']."'";
                    $proresult =$connection->createCommand($prod_create_query)->queryOne();
                    if(!$proresult)
                    {
                        $newdata [] =$data;
                        $productCount = count($newdata);
                        Productimport::batchimport($newdata, $productCount,$merchant_id,true);
                    }
                    else{
                        $newdata [] =$data;
                        $productCount = count($newdata);
                        Productimport::batchimport($newdata, $productCount,$merchant_id,true);

                    }
                }
                unset($data);
                unset($query);
                unset($proresult);
                unset($result);
                unset($queryObj);
            }
            catch(Exception $e){
                Helper::createExceptionLog('actionCurlproductcreate',$e->getMessage(),$data['shopName']);
                exit(0);
            }
        }
    }
    public function actionNeweggproductupdate()

    {
        $shopName = isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:'common';
        try{
            $webhook = fopen('php://input' , 'rb');
            $webhook_content = '';
            while(!feof($webhook)){
                $webhook_content .= fread($webhook, 4096);
            }
            $realdata="";
            $data=array();
            $orderData=array();
            fclose($webhook);
            $realdata=$webhook_content;
            if ( $webhook_content=='' || empty(json_decode($realdata,true))) {
                return;
            }
            $data = json_decode($realdata,true);// ab ye data ka array hai

            if(isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']))
                $data['shopName']=$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
            //$this->emailforproductupdate($data);
            $url = Yii::getAlias('@webneweggurl')."/shopifywebhook/curlprocessforproductupdate";
            //var_dump(http_build_query( $data ));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch, CURLOPT_TIMEOUT,1);
            $result = curl_exec($ch);
            curl_close($ch);
            exit(0);
        }
        catch (Exception $e)
        {
            $this->createExceptionLog('actionProductupdate',$e->getMessage(),$shopName);
            return;
        }
    }
    public function actionCurlprocessforproductupdate()
    {

        $data = $_POST;
        if(isset($data['shopName']) && isset($data['id']))
        {
            try
            {
                $file_dir = \Yii::getAlias('@webroot').'/var/newegg/product/update/'.$data['shopName'].'/'.date('d-m-Y');
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0775, true);
                }
                $filenameOrig="";
                $filenameOrig=$file_dir.'/'.$data['id'].'.log';
                $fileOrig="";
                $fileOrig=fopen($filenameOrig,'w');
                fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($data));


                fclose($fileOrig);

                $connection = Yii::$app->getDb();
                $queryObj="";
                $result="";
                $query="SELECT `id` FROM `user` WHERE username='".$data['shopName']."'";
                $result = $connection->createCommand($query)->queryOne();

                if($result)
                {
                    $merchant_id=$result['id'];
                    $queryObj="";
                    $proresult="";

                    $prod_create_query="SELECT `id` FROM `jet_product` WHERE id='".$data['id']."'";
                    $proresult =$connection->createCommand($prod_create_query)->queryOne();
                    if(!$proresult)
                    {
                        $newdata [] =$data;
                        $productCount = count($newdata);
                        Productimport::batchimport($newdata, $productCount,$merchant_id,true);
                    }
                    else{
                        $newdata [] =$data;
                        $productCount = count($newdata);
                        Productimport::batchimport($newdata, $productCount,$merchant_id,true);
                    }
                }
                unset($data);
                unset($query);
                unset($proresult);
                unset($result);
                unset($queryObj);
            }
            catch(Exception $e){
                Helper::createExceptionLog('actionCurlproductupdate',$e->getMessage(),$data['shopName']);
                exit(0);
            }
        }

    }

    public function actionNeweggproductdelete()
    {

        $webhook_content = '';
        $webhook = fopen('php://input' , 'rb');
        while(!feof($webhook)){ //loop through the input stream while the end of file is not reached
            $webhook_content .= fread($webhook, 4096); //append the content on the current iteration
        }
        fclose($webhook); //close the resource
        $connection = Yii::$app->getDb();
        $data=$webhook_content;
        if ( $webhook_content=='') {

            return;
        }
        $data = json_decode($webhook_content,true); //convert the json to array
        $data['shopName']= isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:"common";
        $file_dir = \Yii::getAlias('@webroot').'/var/newegg/product/delete/'.$data['shopName'].'/'.date('d-m-Y');
        if (!file_exists($file_dir)){
            mkdir($file_dir,0775, true);
        }
        $file_path = $file_dir.'/'.$data['id'].'.log';
        $myfile = fopen($file_path, "w");
        fwrite($myfile, print_r($data,true));
        //fclose($myfile);
        $errorstr="";
        $connection = Yii::$app->getDb();
        if($data && isset($data['id']))
        {
            $product_id="";
            $product_id=trim($data['id']);
            $deleted_variants=0;
            $count_variants=0;
            $sqlresult="";
            $query="";
            $productmodel ="";
            $merchant_id="";
            $archiveSku=[];
            $result=false;
            $query="SELECT `id` FROM `user` WHERE username='".$data['shopName']."'";
            $resultMerchant_id = $connection->createCommand($query)->queryOne();
            $configColl=Helper::configurationDetail($resultMerchant_id['id']);
            //print_r($configColl);die;
            $isConfig=false;
            if($configColl)
            {
                $isConfig=true;
                //$api_host="https://merchant-api.jet.com/api";
                $merchant_id=$resultMerchant_id['id'];
                $jetHelper=new Neweggapi($configColl['seller_id'],$configColl['authorization'],$configColl['secret_key']);
            }
            $query="SELECT `product_id`,`option_sku`,`option_price` FROM `jet_product_variants` WHERE product_id='".$product_id."'";
            try{
                $productmodel = $connection->createCommand($query);
                $result = $productmodel->queryOne();
                $sqlresult=$connection->createCommand($query)->queryAll();
                $count_variants=count($sqlresult);
                // $transaction->commit();
            }
            catch(Exception $e) // an exception is raised if a query fails
            {
                $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                $message1 .= 'Whole query: ' . $query;
                //$errorstr.="<hr>".$message1;
                fwrite($myfile, $message1.PHP_EOL);
                $this->createExceptionLog('actionProductdelete',$message1);
                //$transaction->rollback();
            }
            if(is_array($result)){
                $result1=false;
                //prepare skus for archive
                if(is_array($sqlresult) && count($sqlresult)>0 && $isConfig)
                {
                    foreach ($sqlresult as $value_sku) {
                        $archiveSku['sku']=$value_sku['option_sku'];
                        $archiveSku['price']=$value_sku['option_price'];
                        $archiveSku['id'] = $product_id;
                        $storeData = Helper::storeDetail($resultMerchant_id['id']);
                        $archiveSku['currency'] = $storeData['currency'];
                        $archiveSku['country_code'] = $storeData['country_code'];
                        $archiveSku['merchant_id'] = $merchant_id;
                        $archiveSku['type'] = 'variants';
                        $archiveSku['webhook'] = true;
                    }
                    fwrite($myfile, "variant sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
                }
                $sql = "DELETE FROM `jet_product_variants` WHERE product_id='".$product_id."'";
                try{
                    $result1 = $connection->createCommand($sql)->execute();
                    $deleted_variants=count($result1);
                }catch(Exception $e){
                    $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                    $message1 .= 'Whole query: ' . $sql;
                    fwrite($myfile, $message1.PHP_EOL);
                    Helper::createExceptionLog('actionProductdelete',$message1);
                    //$errorstr.="<hr>".$message1;
                }
            }

            //delete product data
            $deleted_product=0;
            $query="";
            $productmodel ="";
            $result=false;
            $query="SELECT `id`,`merchant_id`,`sku`,`price` FROM `jet_product` WHERE id='".$product_id."'";
            try
            {
                $productmodel = $connection->createCommand($query);
                $result = $productmodel->queryOne();
                // $transaction->commit();
            }
            catch(Exception $e) // an exception is raised if a query fails
            {
                $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                $message1 .= 'Whole query: ' . $query;
                fwrite($myfile, $message1.PHP_EOL);
                //$errorstr.="<hr>".$message1;
                Helper::createExceptionLog('actionProductdelete',$message1);
                //$transaction->rollback();
            }
            if(is_array($result)){
                $merchant_id=$result['merchant_id'];
                $result1=false;
                //prepare skus for archive
                if($isConfig){
                    $archiveSku['sku']=$result['sku'];
                    $archiveSku['price'] = $result['price'];
                    $archiveSku['id'] = $product_id;
                    $archiveSku['merchant_id'] = $merchant_id;
                    $storeData = Helper::storeDetail($resultMerchant_id['id']);
                    $archiveSku['currency'] = $storeData['currency'];
                    $archiveSku['country_code'] = $storeData['country_code'];
                    $archiveSku['type'] = 'simple';
                    $archiveSku['webhook'] = true;
                    fwrite($myfile, "simple sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
                }

                $sql = "DELETE FROM `jet_product` WHERE id='".$product_id."'";
                try{
                    $result1 = $connection->createCommand($sql)->execute();
                    $deleted_product=count($result1);
                    $query="";
                    $productmodel ="";
                    $result=false;
                    $query="SELECT * FROM `insert_product` WHERE merchant_id='".$merchant_id."'";
                    $productmodel = $connection->createCommand($query);
                    $result = $productmodel->queryOne();
                    if(is_array($result)){
                        if($result['product_count']>0)
                            $count=$result['product_count']-1;
                        $sqlresult1=false;
                        $query1="UPDATE `insert_product` SET  product_count='".$count."' where merchant_id='".$merchant_id."'";
                        $result1 = $connection->createCommand($query1)->execute();

                    }
                }catch(Exception $e){
                    $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                    $message1 .= 'Whole 1st query: ' . $sql . "\n";
                    $message1 .= 'Whole 2nd query: ' . $query . "\n";
                    $message1 .= 'Whole 3rd query: ' . $query1 . "\n";
                    //$errorstr.="<hr>".$message1;
                    fwrite($myfile, $message1.PHP_EOL);
                    Helper::createExceptionLog('actionProductdelete',$message1);
                }

            }else{
                $message1  = json_encode($result).' Either select result is false or deleted varients-'.$deleted_variants.' not equal to count varients-'.$count_variants;
                fwrite($myfile, $message1.PHP_EOL);
                //$errorstr.="<hr>".$message1;
            }
            if(count($archiveSku)>0 && $isConfig){
                //send request for disable the product from newegg store
                $query = "SELECT `upload_status` FROM `newegg_can_product` WHERE `product_id`= '".$product_id."' AND `upload_status`= '".Data::PRODUCT_STATUS_ACTIVATED."' ";
                $validate = $connection->createCommand($query);
                if(!empty($validate)){
                    $arrayData[]= $archiveSku;
                    $message=ProductPrice::updatePriceOnNewegg($arrayData,$merchant_id,$connection,true);
                    fwrite($myfile,PHP_EOL."archive sku(s) response from jet:".PHP_EOL.$message.PHP_EOL);
                }

            }
        }
        fclose($myfile);

        /* //fclose($myfile);
         $errorstr="";
         if($data && isset($data['id']))
         {
             $product_id="";
             $product_id=trim($data['id']);
             $deleted_variants=0;
             $count_variants=0;
             $sqlresult="";
             $query="";
             $productmodel ="";
             $merchant_id="";
             $archiveSku=[];
             $result=false;
             $query="SELECT `id` FROM `user` WHERE username='".$data['shopName']."'";
             $resultMerchant_id = $connection->createCommand($query)->queryOne();
             $configColl=Helper::configurationDetail($resultMerchant_id['id']);
             $isConfig=false;
             if($configColl)
             {
                 $isConfig=true;
                 $api_host="https://merchant-api.jet.com/api";
                 $merchant_id=$configColl['merchant_id'];
                 $jetHelper=new Jetapimerchant($api_host,$configColl['api_user'],$$configColl['api_password']);
             }
             $query="SELECT `product_id`,`option_sku` FROM `jet_product_variants` WHERE product_id='".$product_id."'";
             try{
                 $productmodel = $connection->createCommand($query);
                 $result = $productmodel->queryOne();
                 $sqlresult=$connection->createCommand($query)->queryAll();
                 $count_variants=count($sqlresult);
                // $transaction->commit();
             }
             catch(Exception $e) // an exception is raised if a query fails
             {
                 $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                 $message1 .= 'Whole query: ' . $query;
                 //$errorstr.="<hr>".$message1;
                 fwrite($myfile, $message1.PHP_EOL);
                 $this->createExceptionLog('actionProductdelete',$message1);
                 //$transaction->rollback();
             }
             if(is_array($result)){
                 $result1=false;
                 //prepare skus for archive
                 if(is_array($sqlresult) && count($sqlresult)>0 && $isConfig)
                 {
                     foreach ($sqlresult as $value_sku) {
                         $archiveSku[]=$value_sku['option_sku'];
                     }
                     fwrite($myfile, "variant sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
                 }
                 $sql = "DELETE FROM `jet_product_variants` WHERE product_id='".$product_id."'";
                 try{
                         $result1 = $connection->createCommand($sql)->execute();
                         $deleted_variants=count($result1);
                 }catch(Exception $e){
                             $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                             $message1 .= 'Whole query: ' . $sql;
                             fwrite($myfile, $message1.PHP_EOL);
                             $this->createExceptionLog('actionProductdelete',$message1);
                             //$errorstr.="<hr>".$message1;
                 }
             }

         //delete product data
             $deleted_product=0;
             $query="";
             $productmodel ="";
             $result=false;
             $query="SELECT `id`,`merchant_id`,`sku` FROM `jet_product` WHERE id='".$product_id."'";
             try
             {
                 $productmodel = $connection->createCommand($query);
                 $result = $productmodel->queryOne();
                 // $transaction->commit();
             }
             catch(Exception $e) // an exception is raised if a query fails
             {
                 $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                 $message1 .= 'Whole query: ' . $query;
                 fwrite($myfile, $message1.PHP_EOL);
                 //$errorstr.="<hr>".$message1;
                 $this->createExceptionLog('actionProductdelete',$message1);
                 //$transaction->rollback();
             }
             if(is_array($result)){
                 $merchant_id=$result['merchant_id'];
                 $result1=false;
                 //prepare skus for archive
                 if($isConfig){
                     $archiveSku[]=$result['sku'];
                     fwrite($myfile, "simple sku ready for archive:".PHP_EOL.json_encode($archiveSku).PHP_EOL);
                 }

                 $sql = "DELETE FROM `jet_product` WHERE id='".$product_id."'";
                 try{
                     $result1 = $connection->createCommand($sql)->execute();
                     $deleted_product=count($result1);
                     $query="";
                     $productmodel ="";
                     $result=false;
                     $query="SELECT * FROM `insert_product` WHERE merchant_id='".$merchant_id."'";
                     $productmodel = $connection->createCommand($query);
                     $result = $productmodel->queryOne();
                     if(is_array($result)){
                         if($result['product_count']>0)
                             $count=$result['product_count']-1;
                         $sqlresult1=false;
                         $query1="UPDATE `insert_product` SET  product_count='".$count."' where merchant_id='".$merchant_id."'";
                         $result1 = $connection->createCommand($query1)->execute();

                     }
                 }catch(Exception $e){
                     $message1  = 'Invalid query: ' . $e->getMessage() . "\n";
                     $message1 .= 'Whole 1st query: ' . $sql . "\n";
                     $message1 .= 'Whole 2nd query: ' . $query . "\n";
                     $message1 .= 'Whole 3rd query: ' . $query1 . "\n";
                     //$errorstr.="<hr>".$message1;
                     fwrite($myfile, $message1.PHP_EOL);
                     $this->createExceptionLog('actionProductdelete',$message1);
                 }

             }else{
                 $message1  = json_encode($result).' Either select result is false or deleted varients-'.$deleted_variants.' not equal to count varients-'.$count_variants;
                 fwrite($myfile, $message1.PHP_EOL);
                 //$errorstr.="<hr>".$message1;
             }
             if(count($archiveSku)>0 && $isConfig){
                 //send request for archive
                 $message=Jetproductinfo::deativateProductOnNewegg($archiveSku,$jetHelper,$merchant_id);
                 fwrite($myfile,PHP_EOL."archive sku(s) response from jet:".PHP_EOL.$message.PHP_EOL);
             }
         }
         fclose($myfile);
     */

    }

    /*public function actionShipmenttest()
    {
//        if($_POST && isset($_POST['id']))
//        {
        $value = '{"id":"5251599820","email":"cusa.q1mw1x7erw0t9nhm@marketplace.newegg.com","created_at":"2017-02-21T04:25:07-05:00","updated_at":"2017-02-21T04:41:09-05:00","number":"758","note":"Newegg-Integration(newegg.com)","token":"376c5b0624ddafef6cc1d44d5d2a0cb9","gateway":"","test":"0","total_price":"0.12","subtotal_price":"0.12","total_weight":"0","total_tax":"0.00","taxes_included":"0","currency":"AUD","financial_status":"paid","confirmed":"1","total_discounts":"0.00","total_line_items_price":"0.12","buyer_accepts_marketing":"0","name":"#1758","total_price_usd":"0.09","processed_at":"2017-02-21T04:25:07-05:00","order_number":"1758","processing_method":"","source_name":"1226798","fulfillment_status":"fulfilled","tags":"jet.com","contact_email":"cusa.q1mw1x7erw0t9nhm@marketplace.newegg.com","line_items":[{"id":"10594192076","variant_id":"34349640460","title":"testing","quantity":"1","price":"0.12","grams":"0","sku":"SPN9641294924","vendor":"Ced-Jet Test Store","fulfillment_service":"manual","product_id":"9683749388","requires_shipping":"1","taxable":"1","gift_card":"0","name":"testing","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"}],"shipping_lines":[{"id":"4336631500","title":"Standard Shipping (5-7 business days)","price":"0.00","code":"Standard Shipping (5-7 business days)","source":"Newegg"}],"shipping_address":{"first_name":"Test","address1":"9997 Rose Hills Road","phone":"626-271-9700","city":"Whitter","zip":"90601","province":"California","country":"United States","last_name":" John","address2":"","latitude":"34.0174281","longitude":"-118.0512898","name":"Test  John","country_code":"US","province_code":"CA"},"fulfillments":[{"id":"4460647436","order_id":"5251599820","status":"success","created_at":"2017-02-21T04:41:09-05:00","service":"manual","updated_at":"2017-02-21T04:41:09-05:00","tracking_company":"DHL","tracking_number":"2904199424","tracking_numbers":["2904199424"],"tracking_url":"http:\/\/www.dhl-usa.com\/content\/us\/en\/express\/tracking.shtml?brand=DHL&AWB=2904199424","tracking_urls":["http:\/\/www.dhl-usa.com\/content\/us\/en\/express\/tracking.shtml?brand=DHL&AWB=2904199424"],"line_items":[{"id":"10594192076","variant_id":"34349640460","title":"testing","quantity":"1","price":"0.12","grams":"0","sku":"SPN9641294924","vendor":"Ced-Jet Test Store","fulfillment_service":"manual","product_id":"9683749388","requires_shipping":"1","taxable":"1","gift_card":"0","name":"testing","variant_inventory_management":"shopify","product_exists":"1","fulfillable_quantity":"0","total_discount":"0.00","fulfillment_status":"fulfilled"}]}],"customer":{"id":"5526504460","email":"cusa.q1mw1x7erw0t9nhm@marketplace.newegg.com","accepts_marketing":"0","created_at":"2017-01-13T09:49:19-05:00","updated_at":"2017-02-21T04:25:07-05:00","first_name":"Test John","orders_count":"102","state":"disabled","total_spent":"0.00","last_order_id":"5251599820","verified_email":"1","tax_exempt":"0","tags":"","last_order_name":"#1758","default_address":{"id":"5832840780","first_name":"Test","last_name":" John","address1":"9997 Rose Hills Road","address2":"","city":"Whitter","province":"California","country":"United States","zip":"90601","phone":"626-271-9700","name":"Test  John","province_code":"CA","country_code":"US","country_name":"United States","default":"1"}},"shopName":"ced-jet.myshopify.com"}';
        $data = json_decode($value,true);
            $shop=isset($_POST['shopName'])?$_POST['shopName']:"ced-jet.myshopify.com";
            $path = 'order/ship/webhooktest/'.$shop.'/'.date('d-m-Y').'/'.time().'.log';
            try
            {
                //create shipment data
//                Helper::createLog("order shipment in newegg".PHP_EOL.json_encode($_POST),$path,'a+');
                /*$objController=Yii::$app->createController('neweggcanada/neweggorderdetail');
                $objController[0]->actionCurlprocessfororder($data);*/

    /*Orderdetails::curlprocessfororder($data);

}
catch(Exception $e)
{
    Helper::createLog("order shipment error ".json_decode($_POST),$path,'a',true);
}

//        }
//        else
//        {
//            Helper::createLog("order shipment error wrong post");
//        }
}*/


    public function actionIsinstall()
    {
        $webhook_content = '';
        $webhook = fopen('php://input' , 'rb');
        while(!feof($webhook)){ //loop through the input stream while the end of file is not reached
            $webhook_content .= fread($webhook, 4096); //append the content on the current iteration
        }
        fclose($webhook); //close the resource
        $data="";
        $data=$webhook_content;
        if ( $webhook_content=='' || empty(json_decode($data,true))) {
            return;
        }
        $data = json_decode($webhook_content,true); //convert the json to array
        $data['shopName']= isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])?$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']:"common";
        $path=\Yii::getAlias('@webroot').'/var/newegg/uninstall/'.date('d-m-Y').'/'.$data['shopName'];
        if (!file_exists($path)){
            mkdir($path,0775, true);
        }
        $file_path = $path.'/data.log';
        $myfile = fopen($file_path, "a+");
        fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
        fwrite($myfile, print_r($data,true));
        fclose($myfile);

        $url = Yii::getAlias('@webneweggurl')."/shopifywebhook/curlprocessforuninstall";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function actionCurlprocessforuninstall()
    {

        $data = $_POST;
        $shop = "";
        $model = "";
        $model1 = "";
        $modelnew = "";

        $path=\Yii::getAlias('@webroot').'/var/newegg/uninstall/'.date('d-m-Y').'/'.$data['shopName'];
        if (!file_exists($path)){
            mkdir($path,0775, true);
        }
        $file_path = $path.'/data.log';
        $myfile = fopen($file_path, "a+");
        // fwrite($myfile, "\n[".date('d-m-Y H:i:s')."]\n");
        fwrite($myfile, print_r($data,true));
        //fclose($myfile);

        if ($data && isset($data['id'])) {
            $shop = $data['myshopify_domain'];
            $model = User::find()->where(['username' => $shop])->one();

            fwrite($myfile, print_r($model,true));
            fwrite($myfile,PHP_EOL."SHOP NAME".PHP_EOL);
            fwrite($myfile, $shop.PHP_EOL);

            if ($model) {
                fwrite($myfile, PHP_EOL.'In if condition'.PHP_EOL);

                $extensionModel = "";
                $email_id = "";
                $extensionModel = NeweggShopDetail::find()->where(['LIKE', 'shop_url', $shop])->one();

                fwrite($myfile, PHP_EOL.'Extension Detail'.PHP_EOL);
                fwrite($myfile, print_r($extensionModel,true));

                if ($extensionModel) {
                    $email_id = $extensionModel->email;
                    $extensionModel->app_status = "uninstall";
                    $extensionModel->uninstall_date = date('Y-m-d H:i:s');
                    $extensionModel->install_status = 0;
                    $extensionModel->save(false);

                    fwrite($myfile, PHP_EOL.'Extension Detail After Save'.PHP_EOL);
                    fwrite($myfile, print_r($extensionModel,true));
                    Sendmail::uninstallmail($email_id);
                }
            }
        }
    }
}