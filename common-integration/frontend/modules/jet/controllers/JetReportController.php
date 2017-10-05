<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetrepricing;

use Yii;

/**
 * JetreturnController implements the CRUD actions for JetReturn model.
 */
class JetReportController extends JetmainController
{
    public function actionIndex()
    {       
        $updateCount=0;
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        
        if(isset($merchant_id , $this->jetHelper))
        {
            $json = false;
            $salesArray = $finalProductArray = [];
            
            $reportData=Data::sqlRecords("SELECT report_id,created_at FROM `jet_product_report` WHERE merchant_id='".$merchant_id."'",'one','select');
            $invalidReportId=false;
            $filePath="";
            if(isset($reportData['report_id']))
            {
                /*$createAt=strtotime($reportData['created_at']);
               $hours=(time()-$createAt)/(60*60);
                if($hours>24)
                {*/
                    $invalidReportId=true;
                    Data::sqlRecords('DELETE FROM `jet_product_report` WHERE report_id="'.$reportData['report_id'].'"',null,'delete');
                //}
                              
            }
            else
            {
                $invalidReportId=true;
            } 
           
            if (!$invalidReportId)
            {
               /* if($merchant_id==1116){
                    die("not invalid");
                
                }*/
                $json = Jetrepricing::getSalesDataJson($merchant_id,'ProductStatus');
            }
            elseif(Jetrepricing::getSalesDataNSaveInFile($merchant_id, $this->jetHelper,'ProductStatus'))
            {
                /*if($merchant_id==1116){
                    die("invalid");
                
                }*/
                $json = Jetrepricing::getSalesDataJson($merchant_id,'ProductStatus');
            }
            /* if($merchant_id==1116){
                die($json);
                
            }*/
            if($json && strlen($json)>0)
            {
                $salesArray = json_decode($json, true);             
                if(isset($salesArray['ProductStatus'])){
                    $salesArray = $salesArray['ProductStatus'];
                }
            }
            if(!empty($salesArray))
            {
                $totalSku = count($salesArray);
                $chunkStatusArray=array_chunk($salesArray, 100,true);
                foreach ($chunkStatusArray as $ind=> $value)
                {
                    $session->set('productstatus-'.$ind, $value);
                }
                return $this->render('batchupdatestatus', [
                        'totalcount' => $totalSku,
                        'pages' => count($chunkStatusArray)
                ]);                             
            } 
            else
            {
                
                Yii::$app->session->setFlash('success',"None of products status updated...");
                    
                return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet/jetproduct/index'); 
                //return $this->redirect(Yii::$app->request->referrer);
            }
            
        }
        /* if ($updateCount>0)
            Yii::$app->session->setFlash('success',$updateCount." Product status(s) are updated successfully...");
        else
            Yii::$app->session->setFlash('success',"None of products status updated...");
                    
        return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl().'/jet/jetproduct/index'); */
    }

    public function actionBatchProductStatus()
    {
        $merchant_id=MERCHANT_ID;
        $session = Yii::$app->session;
        $productData = [];
        $updateCount=0;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = "";
        $productData = $session->get('productstatus-'.$index);
        $notSkuAvailable=0;
        $flag=false;
        if(is_array($productData) && count($productData)>0)
        {   
            foreach($productData as $key=>$value)
            {   
                if (isset($value['status']))
                {
                    $query='UPDATE jet_product set status="'.$value['status'].'" WHERE merchant_id="'.$merchant_id.'" AND sku="'.addslashes($key).'"';
                    $updateProResponse=Data::sqlRecords($query,null,'update');
                    
                    if($updateProResponse==1)
                        $updateCount++;
    
                    $updateVarResponse = Data::sqlRecords('UPDATE jet_product_variants set status="'.$value['status'].'" WHERE merchant_id="'.$merchant_id.'" AND option_sku="'.addslashes($key).'"',null,'update');
                    if($updateVarResponse==1)
                        $updateCount++;
                    
                   /*if (!$updateProResponse || !$updateVarResponse)
                   {
                   		$query = "INSERT INTO `jet_product_not_in_app`(`sku`, `merchant_id`, `status`) VALUES ('".addslashes($key)."','".$merchant_id."','".$value['status']."')"; 
                   		Data::sqlRecords($query,null,'insert');
                   }*/                   	                        
                }
            }
        }
        $session->remove('productstatus-'.$index);
        if($updateCount>0)
            $return_msg['success']=$updateCount." Product Status(s) Updated";
        else
            $return_msg['success']="product sku(s) status already uptodate in app";
        return json_encode($return_msg);
    }         
}    