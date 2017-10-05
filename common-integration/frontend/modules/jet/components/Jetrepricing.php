<?php 
namespace frontend\modules\jet\components;
use frontend\modules\jet\components\Data;
use Yii;
use yii\base\Component;

class Jetrepricing extends component
{
    const BID = 1;

    public static function getSalesDataNSaveInFile($merchant_id, $jetHelper,$reportType= "")
    {
        $secondLimit = 0;
        $status_code = "";
        $rawDetails = $jetHelper->CPostRequest('/reports/'.$reportType,null,$merchant_id, $status_code);
        
        $salesDataCollection = json_decode($rawDetails, true);
        $salesDataCollection1 = [];
        if(isset($salesDataCollection) && isset($salesDataCollection['report_id']))
        {
                $report_status = 'requested';
                while($report_status != 'ready' /*&& $secondLimit<=50*/)
                {
                    $rawDetails1 = "";
                    $rawDetails1 = $jetHelper->CGetRequest('/reports/state/'.$salesDataCollection['report_id'],$merchant_id, $status_code);
                    $salesDataCollection1 = json_decode($rawDetails1, true);
                    if(!empty($salesDataCollection1) && isset($salesDataCollection1['report_id'],$salesDataCollection1['report_status']))
                    {
                         $report_status = $salesDataCollection1['report_status'];//
                         if($report_status!='ready'){
                            $secondLimit += 8; 
                            sleep(8);//seconds
                         }
                    }else{
                        $secondLimit += 8; 
                        sleep(8);//seconds
                    }
                }
                /*if($merchant_id==1116){
                    echo $secondLimit."<hr><pre>";
                    var_dump($salesDataCollection1);
                    die("gfdg");
                
                }*/
                if(isset($salesDataCollection1) && is_array($salesDataCollection1) && isset($salesDataCollection1['report_url']) /* && $salesDataCollection1['report_type']=='SalesData' */)
                {
                    Data::sqlRecords("INSERT INTO `jet_product_report` (`report_id`,`json_file_path`, `merchant_id`) VALUES ( '".$salesDataCollection['report_id']."', '".$salesDataCollection1['report_url']."', '".$merchant_id."')",null,'insert');
                    $output = "";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $salesDataCollection1['report_url']);
                    //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    //curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec ($ch);
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $path = \Yii::getAlias('@webroot').'/var/jet/report/'.$merchant_id.'/'.$reportType;
                    if($httpcode == 200)
                    {
                        $filePath = $path.'/'.$reportType.'.gz';
                        if(!file_exists(dirname($filePath))) {
                            mkdir(dirname($filePath), 0775, true);
                        }
                        $file = fopen($filePath, "w");
                        fwrite($file, $output);
                        fclose($file);
                        $uncompressedFilePath = $path.'/'.$reportType;
                        if(!file_exists(dirname($uncompressedFilePath))) {
                            mkdir(dirname($uncompressedFilePath), 0775, true);
                        }
                        self::uncompressGz($filePath, $uncompressedFilePath);
                        return true;
                    }
                }
        }else{
            die($status_code);
            
        }
        return false;
    }

    public static function uncompressGz($srcName, $dstName) 
    {
        $sfp = gzopen($srcName, "rb");
        $fp = fopen($dstName, "w");
        while (!gzeof($sfp)) 
        {
            $string = gzread($sfp, 4096);
            fwrite($fp, $string, strlen($string));
        }
        gzclose($sfp);
        fclose($fp);
    }

    public static function getSalesDataJson($merchant_id,$reportType="")
    {
        $path = \Yii::getAlias('@webroot').'/var/jet/report/'.$merchant_id.'/'.$reportType;
        $uncompressedFilePath = $path.'/'.$reportType;
        if(!file_exists($uncompressedFilePath)) {
            return false;
        }
        return file_get_contents ($uncompressedFilePath);
    }

    public static function getMarketplacePrice($proData=[],$merchant_id)
    {
        $rawSkuDetails=[];
        $noDataSkus = [];
        if(is_array($proData) && count($proData)>0)
        {
            foreach ($proData as $value) 
            {
                $my_best_price = "";
                $marketplace_best_price = "";
                $buybox_status = "";
                $my_best_price = isset($value['my_best_offer'][0])?$value['my_best_offer'][0]['shipping_price']+$value['my_best_offer'][0]['item_price']:0;
                $marketplace_best_price = isset($value['best_marketplace_offer'][0])?$value['best_marketplace_offer'][0]['shipping_price']+$value['best_marketplace_offer'][0]['item_price']:0;
                if(isset($value['my_best_offer']) && isset($value['best_marketplace_offer']) && is_array($value['my_best_offer']) && is_array($value['best_marketplace_offer']))
                {
                    if($my_best_price <= $marketplace_best_price)
                        $buybox_status = 1;
                    else
                        $buybox_status = 0;                 
                }
                $merchant_price = isset($value['my_best_offer'])?json_encode($value['my_best_offer']):"";
                $marketplace_price = isset($value['best_marketplace_offer'])?json_encode($value['best_marketplace_offer']):"";
                $query = "SELECT * FROM `jet_repricing` WHERE `sku`='".$value['sku']."'";
                $repriceData = Data::sqlRecords($query,'one','select');
                if (!empty($repriceData)) {
                    $query = "UPDATE `jet_repricing` SET `merchant_price`='".$merchant_price."',`marketplace_price`='".$marketplace_price."', `buybox_status`='".$buybox_status."' WHERE `sku`='".$value['sku']."'";
                    $data = Data::sqlRecords($query,'one','update');
                }elseif(isset($value['product_id'],$value['sku'],$merchant_price,$marketplace_price,$buybox_status) && trim($buybox_status)!="")
                {
                    $query = "INSERT INTO `jet_repricing`(`merchant_id`,`product_id`,`sku`, `variant_id`,`merchant_price`, `marketplace_price`, `buybox_status`) VALUES ('".$merchant_id."','".$value['product_id']."','".addslashes($value['sku'])."','".$value['variant_id']."','".$merchant_price."','".$marketplace_price."', ".$buybox_status.")";
                    Data::sqlRecords($query,null,'update');
                }
            }
        }else{
            return [false, $noDataSkus];
        }
        return [true, $noDataSkus];
    }

    public static function getNSaveRepricingForAll($merchantIds = [], $jetHelper){
        foreach ($merchantIds as $merchant_id) {
            $json = false;
            $salesArray = [];
            $tableProducts = [];
            $skusTableArray = [];
            $finalProductArray = [];
            if(self::getSalesDataNSaveInFile($merchant_id, $jetHelper)){
                $json = self::getSalesDataJson($merchant_id);
            }else{
                continue;
            }
            if($json && strlen($json)>0){
                $salesArray = json_decode($json, true); 
                if(isset($salesArray['SalesData'])){
                    $salesArray = $salesArray['SalesData'];
                }
            }else{
                continue;
            }
            if(count($salesArray)==0){
                //Yii::$app->session->setFlash('error', "Data Not Available on Jet.");
                continue;
            }
            $sql = "SELECT `id` as `product_id`, `variant_id` ,`sku`,`type` FROM `jet_product` WHERE type='simple' AND status='Available for Purchase' AND merchant_id=".$merchant_id. " UNION Select `product_id` , `option_id` as `variant_id`, `option_sku` as `sku`, 'variants' from jet_product_variants where status='Available for Purchase' AND merchant_id=".$merchant_id;
            $tableProducts = Data::sqlRecords($sql,'all','select');
            if(count($tableProducts)>0){
                $skusTableArray = array_reduce($tableProducts, function ($result, $item) {
                    $result[$item['sku']] = $item;
                    return $result;
                });
            }else{
                continue;
            }
            $finalProductArray = array_merge_recursive(array_intersect_key($salesArray, $skusTableArray), $skusTableArray);
            //echo "<pre>";print_r($finalProductArray);die('</pre>');
            if(count($finalProductArray)==0){
                continue;
            }
            self::invalidateRepricingSavedData($merchant_id);
            list($flag, $noDataSkus) = self::getMarketplacePrice($finalProductArray,$merchant_id);
        }
    }

    public static function deleteSalesDataFromTable($finalProductArray,$merchant_id){
        if(count($finalProductArray)==0 || $merchant_id==""){
            return;
        }
        $skusArray = [];
        $skusArray = array_keys($finalProductArray);
        //var_dump($skusArray);die('lll');
        if(count($skusArray)==0)return;
        $skus = '"'.implode('","', $skusArray).'"';
        $sql = "DELETE FROM `jet_repricing` WHERE merchant_id=".$merchant_id." AND sku not in (".$skus.")";
        $tableProducts = Data::sqlRecords($sql,'all','delete');
    }

    public static function invalidateRepricingSavedData($merchant_id){
        $sql = "UPDATE `jet_repricing` set `buybox_status`='', `merchant_price`='', `marketplace_price`='' WHERE merchant_id=".$merchant_id;
        $tableProducts = Data::sqlRecords($sql,'all','update');
    }

    public static function getMarketplacePriceSingly($proData=[],$jetHelper,$merchant_id)
    {
        $rawSkuDetails=[];
        $noDataSkus = [];
        if(is_array($proData) && count($proData)>0)
        {
            foreach ($proData as $value) 
            {
                $my_best_price = "";
                $marketplace_best_price = "";
                $buybox_status = 0;
                $rawDetails = $jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($value['sku']).'/salesdata',$merchant_id, $status_code);
                $salesDataCollection=json_decode($rawDetails, true);
                if (is_array($salesDataCollection) && count($salesDataCollection)>0 && $status_code==200)
                {
                    $my_best_price = isset($salesDataCollection['my_best_offer'][0])?$salesDataCollection['my_best_offer'][0]['shipping_price']+$salesDataCollection['my_best_offer'][0]['item_price']:0;
                    $marketplace_best_price = isset($salesDataCollection['best_marketplace_offer'][0])?$salesDataCollection['best_marketplace_offer'][0]['shipping_price']+$salesDataCollection['best_marketplace_offer'][0]['item_price']:0;
                    if($my_best_price <= $marketplace_best_price){
                        $buybox_status = 1;
                    }
                    $merchant_price = json_encode($salesDataCollection['my_best_offer']);
                    $marketplace_price = json_encode($salesDataCollection['best_marketplace_offer']);
                    $query = "SELECT * FROM `jet_repricing` WHERE `sku`='".$value['sku']."'";
                    $repriceData = Data::sqlRecords($query,'one','select');
                    if (!empty($repriceData)) {
                        $query = "UPDATE `jet_repricing` SET `merchant_price`='".$merchant_price."',`marketplace_price`='".$marketplace_price."', `buybox_status`='".$buybox_status."' WHERE `sku`='".$value['sku']."'";                        
                        Data::sqlRecords($query,'one','update');
                    }elseif (isset($value['product_id'],$value['sku'])) 
                    {                        
                        $query = "INSERT INTO `jet_repricing`(`merchant_id`,`product_id`,`sku`, `variant_id`,`merchant_price`, `marketplace_price`, `buybox_status`) VALUES ('".$merchant_id."','".$value['product_id']."','".$value['sku']."','".$value['variant_id']."','".$merchant_price."','".$marketplace_price."', ".$buybox_status.")";
                        
                        Data::sqlRecords($query);
                    }
                }else{
                    $noData[] = $value['sku'];
                    continue;
                }
            }
        }else{
            return [false, $noDataSkus];
        }
        return [true, $noDataSkus];
    }

    public static function getRepricedPrice($sku, $price, $merchant_id){
        if(trim($sku)=="" && trim($price)=="" && trim($merchant_id)==""){
            return $price;
        }
        $query = "SELECT * FROM `jet_repricing` WHERE `sku`='".$sku."' AND `merchant_id`=".$merchant_id;
        $repriceData = Data::sqlRecords($query,'one','select');
        if(isset($repriceData) && is_array($repriceData) && count($repriceData)>0 && $repriceData['enable']==1){
            $min_price = isset($repriceData['min_price'])?$repriceData['min_price']:0;
            $marketplace_price = 0;
            $merchant_price = 0;
            $merchant_shipping = 0;
            $newPrice = $price;
            $marketplaceArr = isset($repriceData['marketplace_price'])?json_decode($repriceData['marketplace_price'], true):[];
            $yourArr = isset($repriceData['merchant_price'])?json_decode($repriceData['merchant_price'], true):[];
            if(count($marketplaceArr)>0){
                $marketplace_shipping_price = (isset($marketplaceArr[0]['shipping_price']) && $marketplaceArr[0]['shipping_price']!="")?$marketplaceArr[0]['shipping_price']:0;
                $marketplace_item_price = (isset($marketplaceArr[0]['item_price']) && $marketplaceArr[0]['item_price']!="")?$marketplaceArr[0]['item_price']:0;
                $marketplace_price = $marketplace_item_price + $marketplace_shipping_price;
            }
            if(count($yourArr)>0){
                $merchant_shipping = (isset($yourArr[0]['shipping_price']) && $yourArr[0]['shipping_price']!="")?$yourArr[0]['shipping_price']:$merchant_shipping;
                $merchant_price = $newPrice + $merchant_shipping;
            }
            if($merchant_price > $marketplace_price){
                $newPrice = $marketplace_price-self::BID-$merchant_shipping;
                $price = (($newPrice>0) && $newPrice>=$min_price)?$newPrice:$price;
            }
        }
        return $price;
    }
}