<?php 
namespace frontend\modules\jet\controllers;

use Yii;
use frontend\modules\jet\models\ProductsListedOnJet;
use frontend\modules\jet\models\ProductsListedOnJetSearch;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetrepricing;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * JetproductslistController implements the CRUD actions for ProductsListedOnJet model.
 */
class JetproductslistController extends JetmainController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ProductsListedOnJet models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductsListedOnJetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing ProductsListedOnJet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */

    public function actionExport()
    {
    	$merchant_id = MERCHANT_ID;
        $action=Yii::$app->request->post('bulk_name');
        $selection=(array)Yii::$app->request->post('selection');
        if ($action=='export') 
        {
        	if (count($selection)==0) 
        	{
        		Yii::$app->session->setFlash('error', "Please select atleast one sku to export csv");
            	return $this->redirect(['index']);	
        	}
        	else
        	{
        		$details = $row = $value = $csvdata = [];

        		$sql = "SELECT `sku`,`title`,`status`,`has_inv` FROM `products_listed_on_jet` WHERE `merchant_id`='{$merchant_id}' AND `id` IN (".implode(',', $selection).") ";        		
        		$details = Data::sqlRecords($sql,'all','select');
        		$dir= Yii::getAlias('@webroot').'/var/jet/product/listedonjet/export/selected/'.$merchant_id;
	            if (!file_exists($dir)){
	                mkdir($dir,0775, true);
	            }
	            $base_path=$dir."/".time().'.csv';
	            $file = fopen($base_path,"w");
	            $headers = ['SKU','Product Title','Status','Has Inventory'];
	            	             
	            foreach($headers as $header) {
	                $row[] = $header;
	            }
	            fputcsv($file,$row);
	            
	            $i=0;
	            foreach ($details as $key=>$val)
	            {	      	               
	                $csvdata[$i]['SKU']=$val['sku'];
	                $csvdata[$i]['Product Title']=$val['title'];
	                $csvdata[$i]['Status']=$val['status'];
	                $csvdata[$i]['Has Inventory']=$val['has_inv'];	                
	                $i++;        
	            }
	       
	            foreach($csvdata as $v)
	            {    
	                $row = [];
	                $row[] =$v['SKU'];
	                $row[] =$v['Product Title'];
	                $row[] =$v['Status'];
	                $row[] =$v['Has Inventory'];	                	                        
	                fputcsv($file,$row);
	            }
	            fclose($file);
	            $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
	            $content = $encode . file_get_contents($base_path);
	            // Yii::$app->session->setFlash('success', "For the selected sku(s), CSV has been exported");
	            return  Yii::$app->response->sendFile($base_path);  
        	}        	
        }
        /*elseif ($action='other') 
        {
            return $this->redirect(\yii\helpers\Url::toRoute(['updateproduct/export-missing-jet-product']));
        }
        else 
        {
        	$details = $row = $value = $csvdata = [];

    		$sql = "SELECT `sku`,`title`,`status`,`has_inv` FROM `products_listed_on_jet` WHERE `merchant_id`='{$merchant_id}'";        		
    		$details = Data::sqlRecords($sql,'all','select');
    		$dir= Yii::getAlias('@webroot').'/var/jet/product/listedonjet/export/all/'.$merchant_id;
            if (!file_exists($dir)){
                mkdir($dir,0775, true);
            }
            $base_path=$dir."/".time().'.csv';
            $file = fopen($base_path,"w");
            $headers = ['SKU','Product Title','Status','Has Inventory'];
            	             
            foreach($headers as $header) {
                $row[] = $header;
            }
            fputcsv($file,$row);
            
            $i=0;
            foreach ($details as $key=>$val)
            {	      	               
                $csvdata[$i]['SKU']=$val['sku'];
                $csvdata[$i]['Product Title']=$val['title'];
                $csvdata[$i]['Status']=$val['status'];
                $csvdata[$i]['Has Inventory']=$val['has_inv'];	                
                $i++;        
            }
       
            foreach($csvdata as $v)
            {    
                $row = [];
                $row[] =$v['SKU'];
                $row[] =$v['Product Title'];
                $row[] =$v['Status'];
                $row[] =$v['Has Inventory'];	                	                        
                fputcsv($file,$row);
            }
            fclose($file);
            $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
            $content = $encode . file_get_contents($base_path);
            // Yii::$app->session->setFlash('success', "For the selected sku(s), CSV has been exported");
            return  Yii::$app->response->sendFile($base_path); 
        } */       
    }

    /**
     * Finds the ProductsListedOnJet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductsListedOnJet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductsListedOnJet::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

   /* public function actionSyncdata()
    {
        $merchant_id = MERCHANT_ID ;
        $response = $status = "";
        $updateCount = 0;
        $checkUploadedCount = $resArray = [];
        $checkUploadedCount = json_decode($this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size=1',$merchant_id),true);
        $response =$this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size='.$checkUploadedCount['total'],$merchant_id,$status);
        $resArray=json_decode($response,true);
        if(isset($resArray['merchant_skus']) && count($resArray['merchant_skus'])>0 && $status==200)
        {
            foreach ($resArray['merchant_skus'] as $key => $value)
            {
                $inv ="";
                if ($value['has_inventory']==1)
                    $inv = "Yes";
                else
                    $inv = "No";
                if (isset($value['status'],$value['product_title']))
                    Data::insertLiveProduct($value['merchant_sku'],$value['product_title'],$value['status'],$inv,$merchant_id);
            }
            Yii::$app->session->setFlash('success', "Products Sync'ed successfully");
            return $this->redirect(['index']);
        }
    }*/
/*public function actionSyncdata()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        $session = Yii::$app->session;
        $merchant_id=MERCHANT_ID;

        if(!is_object($session)){
            Yii::$app->session->setFlash('error', "Can't initialize Session,Please retry to update product status");
            return $this->redirect(['index']);
        }
        if(!$this->jetHelper)
            $this->jetHelper = new Jetapimerchant(API_HOST,API_USER,API_PASSWORD);

        if($merchant_id)
        {
            $response = $status = "";
            $updateCount = 0;
            $checkUploadedCount = $resArray = [];
            $checkUploadedCount = json_decode($this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size=1',$merchant_id),true);

            $response =$this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size='.$checkUploadedCount['total'],$merchant_id,$status);
            $resArray=json_decode($response,true);

            if(isset($resArray['merchant_skus']) && count($resArray['merchant_skus'])>0 && $status==200)
            {
                $chunkStatusArray=array_chunk($resArray['merchant_skus'], 100);
                foreach ($chunkStatusArray as $ind=> $value)
                {
                    $session->set('productstatus-'.$ind, $value);
                }
                return $this->render('batchupdatestatus', [
                    'totalcount' => $resArray['total'],
                    'pages' => count($chunkStatusArray)
                ]);
            }
            else
            {
                Yii::$app->session->setFlash('error', "Product Status api not working..");
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
    public function actionBatchstatusupdate()
    {
        $merchant_id=MERCHANT_ID;
        $session = Yii::$app->session;
        $productData = [];
        $updateCount=0;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = "";
        $productData = $session->get('productstatus-'.$index);

        if(is_array($productData) && count($productData)>0)
        {
            foreach($productData as $value)
            {
                $inv ="";
                if ($value['has_inventory']==1)
                    $inv = "Yes";
                else
                    $inv = "No";
                if (isset($value['status'],$value['product_title']))
                    Data::insertLiveProduct($value['merchant_sku'],$value['product_title'],$value['status'],$inv,$merchant_id);
                $updateCount++;
            }
        }
        $session->remove('productstatus-'.$index);
        if($updateCount>0)
            $return_msg['success']=$updateCount." Products details Sync'ed successfully";
        else
            $return_msg['error']=" 0 product details Sync'ed with Jet ";
        return json_encode($return_msg);
    }*/
    public function actionSyncdata()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        $session = Yii::$app->session;
        $merchant_id=MERCHANT_ID;

        if(!is_object($session)){
            Yii::$app->session->setFlash('error', "Can't initialize Session,Please retry to update product status");
            return $this->redirect(['index']);
        }
        if(!$this->jetHelper)
            $this->jetHelper = new Jetapimerchant(API_HOST,API_USER,API_PASSWORD);

        if($this->jetHelper)
        {
            $json = false;
            $salesArray = $finalProductArray = [];
            $reportData=Data::sqlRecords("SELECT report_id,created_at FROM `jet_product_report` WHERE merchant_id='".$merchant_id."'",'one','select');
            $invalidReportId=false;
            $filePath="";
            if(isset($reportData['report_id']))
            {
                $createAt=strtotime($reportData['created_at']);
                $hours=(time()-$createAt)/(60*60);
                if($hours>24)
                {
                    $invalidReportId=true;
                    Data::sqlRecords('DELETE FROM `jet_product_report` WHERE report_id="'.$reportData['report_id'].'"',null,'delete');
                }                
            }
            else
            {
                $invalidReportId=true;
            } 
           
            if (!$invalidReportId)
            {
                $json = Jetrepricing::getSalesDataJson($merchant_id,'ProductStatus');
            }
            elseif(Jetrepricing::getSalesDataNSaveInFile($merchant_id, $this->jetHelper,'ProductStatus'))
            {
                $json = Jetrepricing::getSalesDataJson($merchant_id,'ProductStatus');
            }
            $resArray = json_decode($json);
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
                Yii::$app->session->setFlash('error', "Product Status api not working..");
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
    public function actionBatchstatusupdate()
    {
        $merchant_id=MERCHANT_ID;
        $session = Yii::$app->session;
        $productData = [];
        $updateCount=0;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = "";
        $productData = $session->get('productstatus-'.$index);
        
        if(is_array($productData) && count($productData)>0)
        {
            foreach($productData as $key => $value)
            {
                $merchant_sku = $key;
                $status = $value['status'];
                if (isset($value['status'])){
                    $isAlreadyExist = [];
                    $sql = "SELECT `id` FROM `products_listed_on_jet` WHERE `merchant_id`= '{$merchant_id}' AND `sku`='".addslashes($merchant_sku)."' ";
                    $isAlreadyExist = Data::sqlRecords($sql,'one','select');
                    if (empty($isAlreadyExist)) 
                    {
                        $sqlInsert = "INSERT INTO `products_listed_on_jet` (`merchant_id`, `sku`, `status`) VALUES ('{$merchant_id}', '".addslashes($merchant_sku)."','{$status}')";
                        Data::sqlRecords($sqlInsert,null,'insert');
                    }
                    else
                    {
                        $sqlUpdate = "UPDATE `products_listed_on_jet` SET `status` = '{$status}' WHERE `sku` = '".addslashes($merchant_sku)."' AND  `merchant_id` = '{$merchant_id}'";
                        Data::sqlRecords($sqlUpdate,null,'update');   
                    }
                }

                    //Data::insertLiveProduct($key,$value['status'],$merchant_id);
                $updateCount++;
            }
        }
        $session->remove('productstatus-'.$index);
        if($updateCount>0)
            $return_msg['success']=$updateCount." Products details Sync'ed successfully";
        else
            $return_msg['error']=" 0 product details Sync'ed with Jet ";
        return json_encode($return_msg);
    }
    
    public function actionSynccategorystatus()
    {
    	$session = Yii::$app->session;
    	
    	if(!is_object($session)){
    		Yii::$app->session->setFlash('error', "Can't initialize Session,Please retry to update product status");
    		return $this->redirect(['index']);
    	}
        $resArray = [];
        $sql = "SELECT  `category_id` FROM `jet_category`";
        $resArray = Data::sqlRecords($sql,'all','column');
       
        $total  = count($resArray);
		if(!empty($resArray))
		{
			$chunkStatusArray=array_chunk($resArray, 100);
			foreach ($chunkStatusArray as $ind=> $value)
			{
				$session->set('productstatus-'.$ind, $value);
			}
			return $this->render('batchupdatestatus', [
				'totalcount' => $total,
				'pages' => count($chunkStatusArray),
				'type' => 'category'
			]);
		}
		else
		{
			die("Product Status api not working..");
		}
    		
    }
    public function actionBatchcategorystatusupdate()
    {
    	$session = Yii::$app->session;
    	$productData = $return_msg = [];
    	$updateCount=0;
        $merchant_id = MERCHANT_ID;
    	$index=Yii::$app->request->post('index');
    	$return_msg['success'] = $return_msg['error'] = "";
    	$productData = $session->get('productstatus-'.$index);
        
    	if(is_array($productData) && count($productData)>0)
    	{
    		foreach($productData as $key=>$value)
    		{
               
    			$response = $this->jetHelper->CGetRequest('/taxonomy/nodes/'.$value,$merchant_id); 
    			$catRes = json_decode($response,true);
                if (isset($catRes['active']) && $catRes['active']!=1)  
    			 Data::sqlRecords("UPDATE `jet_category` SET `is_active` = '0' WHERE `category_id` =".$value,null,'update');
    		}
    	}
    	$session->remove('productstatus-'.$index);
    	if($updateCount>0)
    		$return_msg['success']=$updateCount." Products details Sync'ed successfully";
    	else
    		$return_msg['error']=" 0 product details Sync'ed with Jet ";
    	return json_encode($return_msg);
    }
}
