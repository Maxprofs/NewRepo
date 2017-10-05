<?php

namespace frontend\modules\jet\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

use frontend\modules\jet\models\JetOrderDetail;
use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\models\JetProductVariants;
use frontend\modules\jet\models\JetRefund;
use frontend\modules\jet\models\JetRefundSearch;

use frontend\modules\jet\components\Data;
/**
 * JetrefundController implements the CRUD actions for JetRefund model.
 */
class JetrefundController extends JetmainController
{
    protected $sc,$jetHelper;
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
	
    /**
     * Lists all JetRefund models.
     * @return mixed
     */
    public function actionIndex()
    {
    	if (Yii::$app->user->isGuest) 
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	
        $searchModel = new JetRefundSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single JetRefund model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new JetRefund model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
    	$merchant_id=MERCHANT_ID;
    	$model1 = new JetRefund();
    	$orderModelAll=[];
    	$orderModelAll= Data::sqlRecords("SELECT id,merchant_order_id FROM `jet_order_detail` WHERE merchant_id='".$merchant_id."' and status='complete'",'all','select');
        
        if(Yii::$app->request->post())
        {
        	$model1->load(Yii::$app->request->post());
        	
        	$orderid=$_POST['JetRefund']['merchant_order_id'];
        	$item_id=$_POST['JetRefund']['order_item_id'];        		
        	$return=(int)$_POST['JetRefund']['quantity_returned'];
        	$refund=(int)$_POST['JetRefund']['refund_quantity'];
        	$feedback=$_POST['JetRefund']['refund_feedback'];
        	$reason=$_POST['JetRefund']['refund_reason'];
        	$principal=(float)$_POST['JetRefund']['refund_amount'];
        	$tax=(float)$_POST['JetRefund']['refund_tax'];
        	$stax=(float)$_POST['JetRefund']['refund_shippingtax'];
        	$shippingcost=(float)$_POST['JetRefund']['refund_shippingcost'];
        	$orderInfo = [];
        	$orderInfo = JetOrderDetail::find()->where(['merchant_id'=>$merchant_id,'merchant_order_id'=>$orderid])->one();

            if($orderInfo)
            {
                $order_items=explode(',',$orderInfo->order_item_id);
                $order_skus=explode(',',$orderInfo->merchant_sku);
                $sku = $status = '';
                foreach ($order_items as $key=>$value)
                {
                    if($value==$item_id)
                    {
                        $sku=$order_skus[$key];
                        break;
                    }
                }
                if($sku==''){
                    Yii::$app->session->setFlash('error', "Sku not found" );
                    return $this->redirect(['index']);
                }
                $flag=false;
                $product=JetProduct::find()->where(['merchant_id' => $merchant_id,'sku'=>$sku])->one();
                
                if($product==''){
                    $vproduct=JetProductVariants::find()->where(['merchant_id' => $merchant_id,'option_sku'=>$sku])->one();
                    if($vproduct)
                        $flag=true;
                }
                if($flag)
                    $updateQty=(int)$vproduct['option_qty']+$refund;
                else
                    $updateQty=(int)$product['qty']+$refund;
                $items = $updateInventory = [];
                $items['items'][]=array('order_item_id'=>$item_id,'total_quantity_returned'=>$return,'order_return_refund_qty'=>$refund,'refund_reason'=>$reason,
                        'refund_feedback'=>$feedback,'refund_amount'=>array('principal'=>$principal,'tax'=>$tax,'shipping_cost'=>$shippingcost,'shipping_tax'=>$stax));
                
                $alt_date=time();
                $modelall=JetRefund::find()->all();
                foreach($modelall as $model)
                {
                    if($item_id==$model['order_item_id'])
                    {
                        Yii::$app->session->setFlash('error', "You have already made a Refund request for this item" );
                        return $this->redirect(['create']);
                    }
                }
                $data=$this->jetHelper->CPostRequest('/refunds/'.$orderid.'/'.$alt_date.'',json_encode($items),$merchant_id,$status);
                $response=json_decode($data,true);
                
                if ($status==201 && isset($response['refund_authorization_id']))                                     
                {
                    $refund_authorisation_id=$response['refund_authorization_id'];
                    if(!empty($refund_authorisation_id))
                    {
                        $model1->refund_id = $refund_authorisation_id;
                        $model1->merchant_id = $merchant_id;
                        $model1->refund_status = $response['refund_status'];
                        $model1->save(false);
                        try
                        {
                            // update product inventory in shopify
                            if($flag){
                                $updateInventory['variant']=array(
                                        "id" => $vproduct['option_id'],
                                        "inventory_quantity"=> $updateQty,
                                        "old_inventory_quantity"=>$vproduct['option_qty'],
                                    );
                                $response = $this->sc->call('PUT', '/admin/variants/'.$vproduct['option_id'].'.json',$updateInventory);
                            }
                            else{
                                $updateInventory['variant']=array(
                                        "id" => $product['variant_id'],
                                        "inventory_quantity"=> $updateQty,
                                        "old_inventory_quantity"=>$product['qty'],
                                );
                                $response = $this->sc->call('PUT', '/admin/variants/'.$product['variant_id'].'.json',$updateInventory);
                            }
                        }
                        catch (\Exception $e)
                        {
                            Yii::$app->session->setFlash('error', $e->getMessage());
                            return $this->redirect(['index']);
                        }
                        Data::sqlRecords("UPDATE `jet_order_detail` SET `order_real_status`='order refunded' WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$orderid."'  ",null,'update');
                        Yii::$app->session->setFlash('success', 'Your Refund has been created successfully' );
                        return $this->redirect(['index']);
                    }
                    else
                    {
                        Yii::$app->session->setFlash('error', 'Value inserted by you is not correct' );
                        return $this->redirect(['create']);
                    }
                }
                elseif(isset($response['errors']))
                {
                    $error_array = $response['errors'];
                }
                if(!empty($error_array) && count($error_array)>0)
                { 
                    $err_msg = "";
                    foreach ($error_array as $valerr){
                        $err_msg = $valerr.' | ';
                    }
                    Yii::$app->session->setFlash('error', $err_msg );
                        return $this->redirect(['create']);
                }
            }else{
                Yii::$app->session->setFlash('error', 'Order not available in database..' );
                return $this->redirect(['create']);
            }
        }
        else 
        {
            if(is_array($orderModelAll) && count($orderModelAll)>0)
            {
                return $this->render('create', [
                    'model' => $model1,
                    'data'=> $orderModelAll
                ]);
            }else{
                return $this->redirect(['index']);
            }        
        }        
    }
	public function actionFetch()
	{
		if (Yii::$app->user->isGuest) {
			return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
		}
		$model1=new JetRefund();
		$merchant_id = MERCHANT_ID;
		
		$result=$model1->find()->where(['merchant_id' => $merchant_id , 'refund_status'=>'created'])->all();
		$count=count($result);
		if($count>0)
		{
			$countR=0;
			foreach($result as $res)
			{	
				$refundid = $responsedata = $status = "";
				$refundid=$res['refund_id'];
				$data=$this->jetHelper->CGetRequest('/refunds/state/'.$refundid,$merchant_id,$status);				
				$responsedata=json_decode($data);

				if(is_object($responsedata) && $responsedata->refund_status!='created')
				{
					$countR++;
					$res->refund_status=$responsedata->refund_status;
					$res->save(false);
				}
			}
			if($countR>0)
				Yii::$app->session->setFlash('success', $count." Refund Status(s) has been updated successfully.");
            else
                Yii::$app->session->setFlash('success', " Refund Status(s) already updated");
		}
		else 
		{
			Yii::$app->session->setFlash('error', "No more refund fetched from Jet.");
		}
		return $this->redirect(['index']);
	}
    /**
     * Updates an existing JetRefund model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
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
     * Deletes an existing JetRefund model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    public function actionVieworder()
    {
        if(Yii::$app->request->post())
        {
            $merchant_id = MERCHANT_ID;
            $merchant_order_id=Yii::$app->request->post('id');

            $orderCollection=Data::sqlRecords("SELECT order_data FROM `jet_order_detail` WHERE `merchant_id`=".MERCHANT_ID." and merchant_order_id='".$merchant_order_id."' LIMIT 0,1",'one','select');
            if(is_array($orderCollection) && count($orderCollection)>0)
            {

                return $orderCollection['order_data'];
            }
        }
    }
    public function actionViewrefunddetails()
    {
        $this->layout="main2";
        if(Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $responseOrders = [];
        $response = $returnId = $status = "";

        $merchant_id = MERCHANT_ID;
        
        $refundId = trim($_POST['refund_id']);        
               
        $response = $this->jetHelper->CGetRequest('/refunds/state/'.rawurlencode($refundId),$merchant_id,$status);         
        $responseOrders = json_decode($response,true);        
        if ($status!=200) {
            Yii::$app->session->setFlash('error',"Jet API is not running Properly, Please try again later");
            return $this->redirect(['index']);
        }           
        return $this->render('viewrefunddetail', ['model' => $responseOrders],true);                       
    }
    /**
     * Finds the JetRefund model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JetRefund the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JetRefund::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
