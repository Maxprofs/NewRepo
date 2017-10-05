<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Jetrepricing;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\models\JetProduct;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

class UpdateproductController extends JetmainController
{
    protected $sc,$jetHelper;
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return $this->render('index');
    }

    public function actionUpdatebarcode()
    {
        $count = array();
        $count['success'] = $count['error'] = $count['notuploaded'] = 0;

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = MERCHANT_ID;
        
        $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
        if ($_FILES['csvfile']['name']) {
            $newname = $_FILES['csvfile']['name'];

            $log = Yii::getAlias('@webroot') . '/var/jet/product/barcode/'.$merchant_id;

            if (!file_exists($log)) {
                mkdir($log, 0775, true);
            }

            $target = $log.'/'.$newname.'-'.time();

            $flag = false;

            if (!file_exists($target)) {
                move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
            }

            $filename1 = $log.'barcode'.'-'.time();
            $file1 = fopen($filename1, 'a+');

            $errorMessage = 'Product barcode Log' . PHP_EOL;
            fwrite($file1, $errorMessage);

            $productData = array();
            if (($handle = fopen($target, "r"))) 
            {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
                {
                    if ($row == 0 && ((strtolower(trim($data[0])) != 'sku') && (strtolower(trim($data[1])) != 'upc'))) {
                        $flag = true;
                        break;
                    }

                    $num = count($data);
                    $row++;
                    if ($row == 1)
                        continue;

                    if (isset($data[0]) && isset($data[1])) 
                    {

                        $sku = $data[0];
                        $barcode = $data[1];

                        $model = Data::sqlRecords("SELECT id,variant_id FROM `jet_product` WHERE merchant_id='" . $merchant_id . "' AND sku='" . $sku . "'", 'one');

                        if (!empty($model)) 
                        {
                            $variant['variant'] = array(
                                'id' => $model['variant_id'],
                                'product_id' => $model['id'],
                                'sku' => $sku,
                                'barcode' => $barcode,
                            );
                            
                            $shipmentResponse = $this->sc->call('PUT', '/admin/variants/'.$model['variant_id'].'.json',$variant);

                            if (!empty($shipmentResponse)) {
                                Yii::$app->session->setFlash('success', "Product Barcode Updated");
                            }
                        }
                    }
                }

                if ($flag) {
                    Yii::$app->session->setFlash('error', "Product Csv columns has been changed...");
                }
            } else {
                Yii::$app->session->setFlash('error', "Please Upload Correct Csv Format....");
            }
        } else {
            Yii::$app->session->setFlash('error', "Please Upload Csv file....");
        }
        return $this->redirect(['index']);
    }
    public function actionSamplecsv()
    {
        $merchant_id = MERCHANT_ID;
        $path = Yii::getAlias('@webroot').'/var/jet/product/samplecsv/'.$merchant_id;
        if (!file_exists($path)){
            mkdir($path,0777, true);
        }
        $base_path=$path.'/'.time().'.csv';
        $file = fopen($base_path,"a+");

        if (isset($merchant_id)){
            $headers = array('sku','upc');
        }
        $row = array();
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file,$row);

        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return \Yii::$app->response->sendFile($base_path);
    }
    public function actionSyncUpdate()
    {
        if(isset($_GET['param']))
        {    
            $merchant_id = MERCHANT_ID;
            $shopname=SHOP;
            $token=TOKEN;
            $countProducts = $pages = 0;
            $countProducts=$sc->call('GET', '/admin/products/count.json',array('published_status'=>'published'));
            $pages=ceil($countProducts/250);
            $session = Yii::$app->session;
            if(!is_object($session)){
                Yii::$app->session->setFlash('error', "Can't initialize Session.Product(s) upload cancelled.");
                return $this->redirect(['index']);
            }
            $session->set('product_page',$pages);            
            return $this->render('syncproduct', [
                    'totalcount' => $countProducts,
                    'pages'=>$pages,
                    'param'=>$_GET['param'],
            ]);
        }
    }
    public function actionSyncdescription()
    {
        $merchant_id=MERCHANT_ID;
        $index=Yii::$app->request->post('index');
        $countUpload=Yii::$app->request->post('count');
        $returnArr=[];
        $jProduct=0;
        try
        {
            $session = Yii::$app->session;
            $pages=$session->get('product_page');
            
            // Get all products
            $products = $this->sc->call('GET', '/admin/products.json', array('published_status'=>'published','limit'=>250,'page'=>$index));

            if($products)
            {
                foreach ($products as $value)
                {
                    $jProduct++;
                    $productDescription= preg_replace("/<script.*?\/script>/", "", $value['body_html'])? : $value['body_html'] ;//$value['body_html'];
                    $product_title = $value['title'];
                    $product_id=$value['id'];
                    $query="UPDATE jet_product SET description='".addslashes($productDescription)."',title = '".addslashes($product_title)."' WHERE id='".$product_id."'";
                    Data::sqlRecords($query,null,'update');
                }
            }
        }
        catch (Exception $e)
        {
            return $returnArr['error']=$e->getMessage();
        }
        $returnArr['success']['count']=$jProduct;

        return json_encode($returnArr);
    } 
    public function actionInventoryUpdate()
    {
      $limit=1000;
      $query="select id,total_count,last_offset from jet_product_cron_update where id=1 limit 0,1";
      $handle=Data::createFile('product/updateInventory/'.date('d-m-Y').'.log','w');
      $countIds=Data::sqlRecords($query,'one','select');
      if(is_array($countIds) && count($countIds)>0)
      {
          $offset=$countIds['last_offset']*$limit;
          Jetproductinfo::getProductInventory($offset,$limit,$handle);
          $newTotal=$countIds['total_count']-1;
          $last_offset=$countIds['last_offset']+1;
          if($newTotal>0)
          {
            Data::sqlRecords('update jet_product_cron_update set last_offset="'.$last_offset.'",total_count="'.$newTotal.'" where id=1',null,'update');
          }
          else
          {
              Data::sqlRecords('delete from jet_product_cron_update where id=1',null,'delete');
          }
      }
      else
      {
          fwrite($handle, PHP_EOL."Product inventory update-".date('d-m-Y H:i:s').PHP_EOL);
          $query="select count(*) id from `jet_product` jet LEFT JOIN `walmart_product` wal ON jet.id=wal.product_id where jet.status='Under Jet Review' or jet.status='Available for Purchase' or wal.status='PUBLISHED' order by jet.status='Available for Purchase' DESC";
          $TotalProduCount=Data::sqlRecords($query,'all','select');
          if(isset($TotalProduCount[0]['id']))
          {
             fwrite($handle, PHP_EOL."Total products-".$TotalProduCount[0]['id'].PHP_EOL);
             $totalProductMarched=$TotalProduCount[0]['id'];
             $offset_count=ceil($totalProductMarched/$limit); 
             Jetproductinfo::getProductInventory(0,$limit,$handle);
             $total_rem_offset=$offset_count-1;
             Data::sqlRecords('INSERT INTO `jet_product_cron_update`(`id`,`total_count`, `last_offset`) VALUES (1,"'.$total_rem_offset.'",1)',null,'insert');
          }
      }
      fwrite($handle, PHP_EOL."Total products update end-".PHP_EOL);
      fclose($handle);
   }
   public function actionCheckbarcode()
   {
        if(isset($_GET['sku'],$_GET['upc']))
        {
            //check upc in jet_product 
            $matchedRecords = Data::sqlRecords('SELECT id,sku,merchant_id FROM jet_product WHERE sku<>"'.$_GET['sku'].'" and upc="'.$_GET['upc'].'"','all','select');
            if(!$matchedRecords)
            {
                //check upc in variant table
                $matchedVarRecords = Data::sqlRecords('SELECT product_id,option_id,option_sku,merchant_id FROM jet_product_variants WHERE option_sku<>"'.$_GET['sku'].'" and option_unique_id="'.$_GET['upc'].'"','all','select');
                if(is_array($matchedVarRecords) && count($matchedVarRecords)>0)
                {
                    foreach ($matchedVarRecords as $key => $val) 
                    {
                        echo "Product Id:".$val['product_id'].", Option Id:".$val['option_id'].", Sku:".$val['option_sku'].", MerchantId:".$val['merchant_id']."<br>";       
                    } 
                }
            }
            else
            {
                foreach ($matchedRecords as $key => $value) 
                {
                    echo "Product Id:".$value['id'].", Sku:".$value['sku'].", MerchantId:".$value['merchant_id']."<br>";       
                } 
            }
        }   
        elseif(isset($_GET['sku'],$_GET['asin']))
        {
            //check asin in jet_product 
            $matchedRecords = Data::sqlRecords('SELECT id,sku,merchant_id FROM jet_product WHERE sku<>"'.$_GET['sku'].'" and ASIN="'.$_GET['asin'].'"','all','select');
            if(!$matchedRecords)
            {
                //check asin in variant table
                $matchedVarRecords = Data::sqlRecords('SELECT product_id,option_id,option_sku,merchant_id FROM jet_product_variants WHERE option_sku<>"'.$_GET['sku'].'" and asin="'.$_GET['asin'].'"','all','select');
                if(is_array($matchedVarRecords) && count($matchedVarRecords)>0)
                {
                    foreach ($matchedVarRecords as $key => $val) 
                    {
                        echo "Product Id:".$val['product_id'].", Option Id:".$val['option_id'].", Sku:".$val['option_sku'].", MerchantId:".$val['merchant_id']."<br>";       
                    } 
                }
            }
            else
            {
                foreach ($matchedRecords as $key => $value) 
                {
                    echo "Product Id:".$value['id'].", Sku:".$value['sku'].", MerchantId:".$value['merchant_id']."<br>";       
                } 
            }
        }
   }
   public function actionUpdateprice()
   {
        $merchant_id=MERCHANT_ID;
        if(isset($_GET['sku']))
        {
            $fullfillmentnodeid=FULLFILMENT_NODE_ID;            
            $product=Data::sqlRecords('SELECT price FROM `jet_product` WHERE sku="'.$_GET['sku'].'" and merchant_id="'.$merchant_id.'"','one','select');
            if(!$product)
            {
                $productVar=Data::sqlRecords('SELECT option_price FROM `jet_product_variants` WHERE option_sku="'.$_GET['sku'].'" and merchant_id="'.$merchant_id.'"','one','select');
                if($productVar)
                {
                    $price=$productVar['option_price'];
                }
            }else{
                $price=$product['price'];
            }
            if($price)
            {
                $setCustomPrice = [];
                $setCustomPrice=Data::sqlRecords('SELECT `data`,`value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"','one','select');
                $newCustomPrice = $updatePriceType = "";
                $updatePriceValue=0;
                if (is_array($setCustomPrice) && isset($setCustomPrice['value']))
                {
                    $newCustomPrice=$setCustomPrice['value'];
                    if($newCustomPrice)
                    {
                        $customPricearr = [];
                        $customPricearr = explode('-',$newCustomPrice);
                        $updatePriceType = $customPricearr[0];
                        $updatePriceValue = $customPricearr[1];
                        unset($customPricearr);
                    }
                    $price=Jetproductinfo::priceChange($price,$updatePriceType,$updatePriceValue);
                }
                $message=Jetproductinfo::updatePriceOnJet($_GET['sku'],(float)$price,$this->jetHelper,$fullfillmentnodeid,$merchant_id);
                echo $message;die("price");
            }
            
        }
    } 

    public function actionExportMissingJetProduct()
    {
        $merchant_id=MERCHANT_ID;
       
        $jet_csv_path=\Yii::getAlias('@webroot').'/var/jet/product/listedonjet/export/other/'.$merchant_id;
        if (!file_exists($jet_csv_path)){
            mkdir($jet_csv_path,0775, true);
        }
        $jet_csv_file = $jet_csv_path.'/'.time().'.csv';
        $file_handle = fopen($jet_csv_file,"w");
        $headers = array('SKU','Status');
        $row = [];
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file_handle,$row);
        $new_row = [];
        $i = 0;
        $query = "SELECT  `merchant_id`, `sku`, `status` FROM `products_listed_on_jet` WHERE `merchant_id`='".$merchant_id."'";
        $data=Data::sqlRecords($query,'all','select');
        foreach ($data as $value) {
                $row++;
                if($row==1)
                    continue;
                $query = "SELECT id FROM `jet_product` WHERE sku='".addslashes($value['sku'])."' and merchant_id='".$merchant_id."' LIMIT 0,1";
                $prodColl=Data::sqlRecords($query,'one','select');
                               
                if(!$prodColl)
                {
                    $query = "SELECT option_id FROM `jet_product_variants` WHERE option_sku='".addslashes($value['sku'])."' and merchant_id='".$merchant_id."' LIMIT 0,1";
                    $prodVariantColl=Data::sqlRecords($query,'one','select');
                    if(!$prodVariantColl)
                    {
                        
                        $new_row[$i]['sku'] = $value['sku'];
                        $new_row[$i]['status'] = $value['status'];
                        $i++;
                        
                    }
                }
        }
        if (empty($new_row)) {
            Yii::$app->session->setFlash('error', "There is not any sku from other API provider");
              return $this->redirect(\Yii::$app->urlManager->createUrl("/jet/jetproductslist/index"));
        }
        foreach($new_row as $v)
        {
            
            $row = array(); 
            $row[] = $v['sku'];
            $row[] = $v['status'];
            fputcsv($file_handle,$row);
        }
        fclose($file_handle);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($jet_csv_file);
        
        return \Yii::$app->response->sendFile($jet_csv_file);
    }               
    public function actionExportJetCsv()
    {
    
        $merchant_id=MERCHANT_ID;
       
        $jet_csv_path=\Yii::getAlias('@webroot').'/var/jet/product/listedonjet/export/all/'.$merchant_id;
        if (!file_exists($jet_csv_path)){
            mkdir($jet_csv_path,0775, true);
        }
        $jet_csv_file = $jet_csv_path.'/'.time().'.csv';
        $file_handle = fopen($jet_csv_file,"w");
        $headers = array('SKU','Status');
        $row = [];
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file_handle,$row);
        $new_row = [];
        $i = 0;
        $query = "SELECT  `merchant_id`, `sku`, `status` FROM `products_listed_on_jet` WHERE `merchant_id`='".$merchant_id."'";
        $data=Data::sqlRecords($query,'all','select');
        foreach ($data as $value) {
                $row++;
                if($row==1)
                    continue;
                 
                $new_row[$i]['sku'] = $value['sku'];
                $new_row[$i]['status'] = $value['status'];
                $i++;
                        
                
        }

        foreach($new_row as $v)
        {
            
            $row = array(); 
            $row[] = $v['sku'];
            $row[] = $v['status'];
            fputcsv($file_handle,$row);
        }
        fclose($file_handle);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($jet_csv_file);
        
        return \Yii::$app->response->sendFile($jet_csv_file);
    }  
    public function actionExprortProduct()
    {
        $merchant_id=MERCHANT_ID;
        $query="SELECT id,title,sku,type FROM jet_product WHERE merchant_id='".$merchant_id."' and (status='".JetProduct::UNDER_JET_REVIEW."' OR status='".JetProduct::AVAILABLE_FOR_PURCHASE."')";
        $model=Data::sqlRecords($query,'all','select');
        $exceptionPath = \Yii::getAlias('@webroot').'/var/jet/product/shipping_exception/'.$merchant_id;
        if (!file_exists($exceptionPath)){
            mkdir($exceptionPath,0775, true);
        }
        $base_path=$exceptionPath.'/shipping_exception.csv';
        $file = fopen($base_path,"w");
        $headers = array('Title','Sku','Shipping Exception Type','Shipping Method','Service Level','Override Type','Shipping Charge Amount');
        
        $row = $productdata = [];
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file,$row);
        
        $i=0;
        if(is_array($model) && count($model)>0)
        {
            foreach($model as $value)
            {
                if($value['sku']=="")
                {
                    continue;
                }
                if($value['type']=="simple")
                {
                    $row=[];
                    $row[]=$value['title'];
                    $row[]=$value['sku'];
                    fputcsv($file,$row);
                    $i++;
                }
                else
                {
                    $optionResult=[];
                    $query="SELECT option_id,option_title,option_sku,option_price,asin,option_mpn FROM `jet_product_variants` WHERE product_id='".$value['id']."' order by option_sku='".addslashes($value['sku'])."' desc";
                    $optionResult=Data::sqlRecords($query,'all','select');
                    
                    if(is_array($optionResult) && count($optionResult)>0)
                    {   
                        foreach($optionResult as $key=>$val)
                        {
                            $row=[];
                            if($val['option_sku']=="")
                                continue;
                            $row[]=$value['title'];
                            $row[]=$val['option_sku'];
                            if($value['sku']==$val['option_sku'])
                            {
                                //$row[]="Parent";
                            }
                            else
                            {   
                                //$row[]="Child";
                            }
                            fputcsv($file,$row);
                            $i++;
                        }
                    }
                }
            }
        }
        fclose($file);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return \Yii::$app->response->sendFile($base_path);    
    }
    public function actionBulkShippingException()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $count = [];
        $count['success'] = $count['error'] = 0;
        $merchant_id = MERCHANT_ID;

        $flag=false;
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

        if(isset($_FILES['csvfile']))
        {
            if (!in_array($_FILES['csvfile']['type'], $mimes)) 
            {
                Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");        
                return $this->render('bulk_shipping_exception');
            }
            
            if ($_FILES['csvfile']['name']) 
            {
                $newname = $_FILES['csvfile']['name'];
                $target = Yii::getAlias('@webroot').'/var/jet/product/shipping_exception/'.$merchant_id;

                if (!file_exists($target)) {
                    mkdir($target, 0775, true);
                }
                $imageModel=UploadedFile::getInstanceByName('csvfile');
                $imageModel->saveAs($target.'shipping_exception.csv');
                $filename1 = $target.'shipping_exception.log';
                $file = fopen($filename1, 'w');
                $errorMessage = 'Product shipping_exception Log' . PHP_EOL;
                fwrite($file, $errorMessage);
                $fp = file($target.'shipping_exception.csv');
                $countLines = count($fp);   
                if(count($countLines)>0)
                {
                    $pages=ceil($countLines/100);
                    return $this->render('ajaxbatchcsv', [
                        'totalcount' => $countLines,
                        'pages'=>$pages,
                        'target' => $target.'shipping_exception.csv',
                        'param' => 'Bulk Shipping Exception',
                        'log_file'=>$filename1,
                    ]);
                }                
            } 
            else 
            {
                Yii::$app->session->setFlash('error', "Please Upload Csv file....");
            }
        }
        
        return $this->render('bulk_shipping_exception');
    }
    public function actionMassBulkShippingException()
    {
        $postData= Yii::$app->request->post();
        if($postData && isset($postData['index']))
        {
            $response=[];
            $count=0;
            if (($handle = fopen($postData['target'], "r"))) 
            {
                $file=fopen($postData['file_path'],'a+');
                if($postData['index']>1){
                    fseek($handle, $postData['current_pos']);
                }
                $row = 0;
                $flag=false;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
                {
                    if ($postData['index']==1 && $row == 0 && strcasecmp($data[1], "sku") != 0) 
                    {
                        $flag = true;
                        break;
                    }
                    $row++;
                    if ($postData['index']==1 && $row == 0)
                        continue;
                   
                    if($row==100)
                    {
                        $cur_pos = ftell($handle);
                        $last_sku=$data[1];
                        break;
                    }
                    if (isset($data[1]) && $data[1]) 
                    {
                        fwrite($file, "sku: ".$data[1].PHP_EOL);
                        if(Jetproductinfo::checkSkuOnJet(trim($data[1]),$this->jetHelper,MERCHANT_ID)==true)
                        {
                            $shipping_array=[];
                            $shipping_array['sku']=trim($data[1]);
                            $shipping_array['ship_exception']=trim($data[2]);
                            $shipping_array['ship_method']=trim($data[3]);
                            $shipping_array['ship_level']=trim($data[4]);
                            if(trim($data[6]) && is_numeric(trim($data[6])) && trim($data[6])>0)
                            {
                                if(!trim($data[5]))
                                {
                                    $shipping_array['override_type']= "Override charge";   
                                }
                                else
                                {
                                    $shipping_array['override_type']=trim($data[5]);
                                }
                                $shipping_array['shipping_charge_amount']=trim($data[6]);
                            }
                            fwrite($file, json_encode($shipping_array).PHP_EOL);
                            $productObj = new JetproductController(Yii::$app->controller->id,'');
                            $shipment_response=$productObj->actionShipexception($shipping_array);
                            
                            if(is_null(json_decode($shipment_response,true))){
                                $count++;
                            }
                            fwrite($file, $shipment_response.PHP_EOL);
                        }
                    }              
                }
                if(!$flag && isset($cur_pos))
                {
                    $response['success']=$cur_pos;
                    $response['last_sku']=$last_sku;
                }
                elseif(!$flag)
                {
                    $response['success']="end";
                }
                else
                    $response['error']="Please upload correct csv file...";
                $response['bulk_shipping_exception']=$count;
            }
            return json_encode($response);
        }
    } 
    public function actionSalesData()
    {
        $merchant_id=MERCHANT_ID;

        if(API_USER) 
        {
            $query="SELECT `sku` FROM `jet_product` WHERE merchant_id='".MERCHANT_ID."' and status='Available for Purchase'";
            $prodCollection=Data::sqlRecords($query,'all','select');
            $skus=[];
            foreach ($prodCollection as $key => $value) {
                $skus[]=addslashes($value['sku']);
            }            
            $sql = 'DELETE FROM `jet_sales_data` WHERE `merchant_id`="'.$merchant_id.'" AND `sku` NOT IN ("'.implode('","', $skus).'")';            
            Data::sqlRecords($sql,null,"delete");
            $isInsert=false;
            $values="INSERT INTO `jet_sales_data`(`merchant_id`, `sku`, `sales_data`)VALUES";
            if(is_array($prodCollection) && count($prodCollection)>0)
            {
                foreach ($prodCollection as $key => $value) 
                {
                    $saleReport=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($value['sku']).'/salesdata',$merchant_id);   
                    $saleReportData=json_decode($saleReport,true);                    
                    //Save sales data
                    if(is_array($saleReportData) && count($saleReportData)>0 && !isset($saleReportData['error']))
                    {
                         $query="SELECT id FROM `jet_sales_data` WHERE sku='".$value['sku']."' and merchant_id='".MERCHANT_ID."' LIMIT 0,1";
                        $salesDataColl=Data::sqlRecords($query,'one','select');
                        if(is_array($salesDataColl)&&count($salesDataColl)>0)
                        {
                            $query="UPDATE `jet_sales_data` SET `sales_data`='".addslashes($saleReport)."' WHERE sku='".$value['sku']."' and merchant_id='".MERCHANT_ID."'";
                            Data::sqlRecords($query,null,'update');
                        }
                        else
                        {
                            $isInsert=true;
                            $values.='("'.MERCHANT_ID.'","'.$value['sku'].'","'.addslashes($saleReport).'"),';
                        }
                    }
                }
                if($isInsert)
                {
                    Data::sqlRecords(rtrim($values, ","),null,'insert');
                }                
            }
            return $this->redirect(\yii\helpers\Url::toRoute(['sales-data/index'])); 
        }
        return $this->redirect(Yii::getAlias('@webjeturl').'/jetproduct/index');
    } 
    public function actionGetproduct(){
        $countProducts=$this->sc->call('GET', '/admin/products.json');
        var_dump($countProducts);die;
    }
    public function actionOrderReady()
    {
        if(isset($_GET['offset'],$_GET['limit']))
        {
            $query="SELECT config.merchant_id,api_user,api_password FROM `jet_configuration` as config INNER JOIN `jet_shop_details` WHERE install_status='1' AND (purchase_status='Not Purchase' OR purchase_status='Purchased') LIMIT ".$_GET['offset'].",".$_GET['limit'];
            $merchantColl=Data::sqlRecords($query,'all','select');
        }
        if(is_array($merchantColl) && count($merchantColl)>0)
        {
            foreach ($merchantColl as $key => $value)
            {
                $response = $this->jetHelper->CGetRequest('/orders/ready',$value['merchant_id']);
                $responseArr = json_decode($response,true);
                if(isset($responseArr['order_urls']) && count($responseArr['order_urls'])>0)
                {
                    echo $value['merchant_id']."--".$response."<hr>";
                }
            }
        }
    }

    public function actionCheckPayment()
    {
        $sql="SELECT `jet_recurring_payment`.merchant_id FROM `jet_recurring_payment` INNER JOIN `jet_shop_details` ON `jet_shop_details`.`merchant_id`=`jet_recurring_payment`.`merchant_id` WHERE `jet_shop_details`.`expired_on` BETWEEN '".$CurDate."' AND '$nextDate'";
    }
    public function actionCheckWebhook()
    {
        Data::createSingleWebhook($this->sc,"products/delete","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/productdelete");

        /*Data::createSingleWebhook($this->sc,"products/update","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/productupdate");

        Data::createSingleWebhook($this->sc,"orders/fulfilled","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/createshipment");

        Data::createSingleWebhook($this->sc,"orders/partially_fulfilled","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/createshipment");

        Data::createSingleWebhook($this->sc,"orders/cancelled","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/ordercancelled");

        Data::createSingleWebhook($this->sc,"orders/create","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/createorder");

        Data::createSingleWebhook($this->sc,"app/uninstalled","http://ec2-52-39-2-50.us-west-2.compute.amazonaws.com/rmq/rabbitmq/shopifywebhook/isinstall");
        */
        $webhookData=$this->sc->call("GET","/admin/webhooks.json"/*,["topic"=>"orders/create"]*/);
        echo "jet<hr>";var_dump($webhookData);
        /*$walmart = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
        $webhook=$walmart->call("GET","/admin/webhooks.json",["topic"=>"orders/create"]);
        echo "walmart<hr>";var_dump($webhook);*/
    }
    public function actionUpdateonjet()
    {
    try
    {
      $message="";
      $productUpdate=Data::sqlRecords('SELECT temp.id,product_id,data,temp.merchant_id,fullfilment_node_id,api_user,api_password FROM `jet_product_tmp` as temp LEFT JOIN `jet_configuration` as config ON temp.merchant_id=config.merchant_id ORDER BY temp.id  ASC LIMIT 0,500','all','select');
      
      $fullfillmentnodeid = '';
      if(is_array($productUpdate) && count($productUpdate)>0)
      {  
        foreach($productUpdate as $value)
        {
          $merchant_id = $value['merchant_id'];
          $path=\Yii::getAlias('@webroot').'/var/jet/product/update/'.$merchant_id.'/';
          if (!file_exists($path))
          {
            mkdir($path,0775, true);
          }
          $filename=$path.'/'.$value['product_id'].'.log';
          $file=fopen($filename,'w');
          fwrite($file,PHP_EOL.date('d-m-Y H:i:s')." Product Update".PHP_EOL);
          $jetHelper="";
          $fullfillmentnodeid = '';
          if($value['api_user'] && $value['api_password'])
          {
            $check='\n product config set';
            fwrite($file,PHP_EOL.$check);
            $fullfillmentnodeid = $value['fullfilment_node_id'];
            $jetHelper = new Jetapimerchant(API_HOST,$value['api_user'],$value['api_password']);
          }
          $customData = JetProductInfo::getConfigSettings($merchant_id);
          $customPrice = (isset($customData['fixed_price']) && $customData['fixed_price']=='yes')?$customData['fixed_price']:"";
          $newCustomPrice = (isset($customData['set_price_amount']) && $customData['set_price_amount'])?$customData['set_price_amount']:"";
          $configBarcode = (isset($customData['update_barcode']) && $customData['update_barcode']=="yes")?$customData['update_barcode']:"";
          $updateTitle = (isset($customData['update_title']) && $customData['update_title']=="yes")?$customData['update_title']:"";
          $updateDescription = (isset($customData['update_description']) && $customData['update_description']=="yes")?$customData['update_description']:"";
          Data::checkInstalledApp($merchant_id,$type=false,$installData);
          $onWalmart=isset($installData['walmart'])?true:false;
          $onNewEgg=isset($installData['newegg'])?true:false;
        
          $query='SELECT id,title,sku,type,product_type,description,variant_id,image,qty,weight,price,attr_ids,jet_attributes,vendor,upc,fulfillment_node FROM `jet_product` WHERE `id`="'.$value['product_id'].'" LIMIT 0,1';
          $result=Data::sqlRecords($query,"one","select");
          $data = json_decode($value['data'],true);

          if(isset($result['id'])) 
          {
            //$count++;
            if(is_array($data) && count($data)>0)
                Jetproductinfo::productUpdateData($result,$data,$jetHelper,$fullfillmentnodeid,$merchant_id,$file,$customPrice,$newCustomPrice,$updateTitle,$updateDescription,$configBarcode,$onWalmart,$onNewEgg);
          }
          else
          {
              //add new product
              $message= "add new product with product id: ".$value['product_id'].PHP_EOL;
              fwrite($file, $message);
              Jetproductinfo::saveNewRecords($data, $merchant_id);
          }
         
          //Data::sqlRecords('DELETE FROM `jet_product_tmp` where id="'.$value['product_id'].'"');
          fclose($file);
          //$resultValues[$merchant_id]=$check;
        }
      }
      unset($productUpdate);
      unset($result);
      unset($data);
      unset($jetHelper);
      unset($customPrice);
      //unset($file);
      unset($jetConfig);
      unset($customData);
       
     }
     catch(Exception $e)
     {
        //echo $e->getMessage();die;
        $path=\Yii::getAlias('@webroot').'/var/product/update/Exception';
        if (!file_exists($path)){
            mkdir($path,0775, true);
        }
        $file="";
        $filename=$path.'/Error.log';
        $file=fopen($filename,'a+');
        fwrite($file,"\n".date('d-m-Y H:i:s')."Exception Error:\n".$e->getMessage());
        fclose($file);
     }  
    } 
}