<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetdata;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

class JetcsvupdateController extends JetmainController
{
    protected $jetHelper;
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return $this->render('index');
    }

    public function actionSamplecsv()
    {
        $path=\Yii::getAlias('@webroot').'/var/jet/product/sample';
        $merchant_id=MERCHANT_ID;
        if (!file_exists($path)){
            mkdir($path,0777, true);
        }
        $base_path=$path.'/'.'Sample.csv';
        $file = fopen($base_path,"a+");

        if (isset($merchant_id)){
            $headers = array('sku');
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
    public function actionAjaxUnarchiveCsv()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $count=[];        
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream','text/comma-separated-values');
        if (!in_array($_FILES['csvfile']['type'], $mimes)) 
        {
            Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");        
            return $this->redirect(['index']);
        }
        if ($_FILES['csvfile']['name']) 
        {
            $newname = $_FILES['csvfile']['name'];
            $log = Yii::getAlias('@webroot').'/var/jet/product/unarchieve/'.MERCHANT_ID;
            if (!file_exists($log)) 
            {
                mkdir($log, 0775, true);
            }
            $fileInfo=pathinfo($newname);
            $log_file=$fileInfo['filename'].'.log';
            $file = fopen($log.'/'.$log_file,"w");
            fwrite($file, "product UnArchieve start".PHP_EOL);
            fclose($file);
            $target = $log.'/'.$newname;
            $imageModel=UploadedFile::getInstanceByName('csvfile');
            $imageModel->saveAs($target);
            $fp = file($target);
            $countLines = count($fp);   
            if(count($countLines)>0){
                $pages=ceil($countLines/100);
                return $this->render('ajaxbatchcsv', [
                    'totalcount' => $countLines,
                    'pages'=>$pages,
                    'target' => $target,
                    'param' => 'unarchive',
                    'log_file'=>$log.'/'.$log_file,
                ]);
            }
        }    

    }
    public function actionMassUnarchiveCsv()
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
                    if ($postData['index']==1 && $row == 0 && strcasecmp($data[0], "sku") != 0) 
                    {
                        $flag = true;
                        break;
                    }
                    $row++;
                    if ($postData['index']==1 && $row == 0)
                        continue;
                    fwrite($file,PHP_EOL."sku : ".$data[0] . PHP_EOL);
                    if($row==100){
                        $cur_pos = ftell($handle);
                        $last_sku=$data[0];
                        fwrite($file, "cur_pos : ".$cur_pos. PHP_EOL);
                        fwrite($file, "last_sku : ".$last_sku. PHP_EOL);
                        break;
                    }
                    if (MERCHANT_ID==397 && trim($data[0])!='sku')
                    {
                    	$sql = "DELETE FROM `jet_product_variants` WHERE `merchant_id`=397 AND `product_id`=".$data[0];
                    	Data::sqlRecords($sql,null,'update');
                    	
                    }else 
                    {
                    	if (isset($data[0]))
                    	{
                    		$sku = $data[0];
                    		$isProductExist=false;
                    		$prod_data = Data::sqlRecords("SELECT `qty` FROM `jet_product` WHERE merchant_id='".MERCHANT_ID."' AND sku='".addslashes($sku)."' LIMIT 0,1","one","select");
                    		if(isset($prod_data['qty'])) {
                    			$qty = (int)$prod_data['qty'];
                    			$isProductExist=true;
                    		}
                    		else
                    		{
                    	
                    			$option_prod_data = Data::sqlRecords("SELECT `option_qty` FROM `jet_product_variants` WHERE merchant_id='".MERCHANT_ID."' AND option_sku='".addslashes($sku)."' LIMIT 0,1","one","select");
                    			if(isset($option_prod_data['option_qty']))
                    			{
                    				$isProductExist=true;
                    				$qty = $option_prod_data['option_qty'];
                    			}
                    		}
                    		if($isProductExist)
                    		{
                    			$result = Jetdata::unarchieved($this->jetHelper,FULLFILMENT_NODE_ID,$data[0],$qty,MERCHANT_ID,$file);
                    			if(isset($result['success']))
                    			{
                    				$count++;
                    				fwrite($file, "success unarchive: ".$sku . PHP_EOL);
                    			}else{
                    				fwrite($file, "failed unarchive: ".$sku . PHP_EOL);
                    			}
                    		}
                    	}
                    }                                                      
                }
                if(!$flag && isset($cur_pos))
                {
                    $response['success']['cur_pos']=$cur_pos;
                    $response['success']['last_sku']=$last_sku;
                }
                elseif(!$flag)
                {
                    $response['success']['end']="end";
                }
                else
                    $response['error']="Please upload correct csv file...";
                $response['success']['unarchive_count']=$count;
                fwrite($file, "unarchive_count : ".$count. PHP_EOL);
            }
            return json_encode($response);
        }
    }
    public function actionAjaxArchiveCsv()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $count=array();
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream','text/comma-separated-values');
        if (!in_array($_FILES['csvfile']['type'], $mimes)) 
        {
            Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");        
            return $this->redirect(['index']);
        }
        if ($_FILES['csvfile']['name']) 
        {
            $newname = $_FILES['csvfile']['name'];
            $log = Yii::getAlias('@webroot').'/var/jet/product/archieve/'.MERCHANT_ID;
            if (!file_exists($log)) 
            {
                mkdir($log, 0775, true);
            }
            $fileInfo=pathinfo($newname);
            $log_file=$fileInfo['filename'].'.log';
            $file = fopen($log.'/'.$log_file,"w");
            fwrite($file, "product archieve start".PHP_EOL);
            fclose($file);
            $target = $log.'/'.$newname;
            $imageModel=UploadedFile::getInstanceByName('csvfile');
            $imageModel->saveAs($target);
            $fp = file($target);
            $countLines = count($fp);   
            if(count($countLines)>0){
                $pages=ceil($countLines/100);
                return $this->render('ajaxbatchcsv', [
                    'totalcount' => $countLines,
                    'pages'=>$pages,
                    'target' => $target,
                    'param' => 'archive',
                    'log_file'=>$log.'/'.$log_file,
                ]);
            }
        }    

    }
    public function actionMassArchiveCsv()
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
                    if ($postData['index']==1 && $row == 0 && strcasecmp($data[0], "sku") != 0) 
                    {
                        $flag = true;
                        break;
                    }
                    $row++;
                    if ($postData['index']==1 && $row == 0)
                        continue;
                    fwrite($file, "sku : ".$data[0] . PHP_EOL);
                    if($row==100){
                       
                        $cur_pos = ftell($handle);
                        $last_sku=$data[0];
                        fwrite($file, "cur_pos : ".$cur_pos. PHP_EOL);
                        fwrite($file, "last_sku : ".$last_sku. PHP_EOL);
                        break;
                    }
                    if (isset($data[0])) 
                    {
                        $sku = $data[0];
                        $result = Jetdata::archieved($this->jetHelper,$data[0],FULLFILMENT_NODE_ID,MERCHANT_ID,$file);
                        if(isset($result['success'])) 
                        {
                            $count++;
                            fwrite($file, "success archive: ".$sku . PHP_EOL);
                        }else{
                            fwrite($file, "failed archive: ".$sku . PHP_EOL);
                        }
                    }              
                }
                if(!$flag && isset($cur_pos))
                {
                    $response['success']['cur_pos']=$cur_pos;
                    $response['success']['last_sku']=$last_sku;
                }
                elseif(!$flag)
                {
                    $response['success']['end']="end";
                }
                else
                    $response['error']="Please upload correct csv file...";
                $response['success']['archive_count']=$count;
                fwrite($file, "archive_count : ".$count. PHP_EOL);
            }
            return json_encode($response);
        }
    }
}

?>
