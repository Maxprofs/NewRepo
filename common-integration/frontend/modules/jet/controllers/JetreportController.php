<?php
namespace frontend\modules\jet\controllers;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use frontend\modules\jet\components\Dashboard\OrderInfo;
use frontend\modules\jet\components\Data;


/**
 * JetreportController implements the CRUD actions for product and order graph model.
 */
class JetreportController extends JetmainController
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
        $merchant_id = MERCHANT_ID;
        $details = $orderData = $revinue = $orderCount = [];
        
        for ($i=0; $i < 12; $i++) 
        { 
            $date = date('Y-m', strtotime("-{$i} month"));
            $orderCount[date("M", strtotime($date))]= OrderInfo::getOrdersCount($merchant_id,$date); 
            
//             echo "<hr>".$date."==>";
        }
//         die("<hr>fdg");
        $sql = "SELECT `merchant_sku`, COUNT(`merchant_order_id`) as `counter` FROM `jet_order_detail` WHERE `merchant_id` = {$merchant_id}  group by merchant_sku  order by counter desc limit 10";
        $details = Data::sqlRecords($sql,'all','select');
        
       
        return $this->render('index', [
            'orders' => $details,
            'salesrevinuew'=>$orderCount
        ]);
    }

    /**
     * Displays a single ProductsListedOnJet model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ProductsListedOnJet model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductsListedOnJet();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ProductsListedOnJet model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ProductsListedOnJet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionSyncdata()
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
    }

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
        }        
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
}
