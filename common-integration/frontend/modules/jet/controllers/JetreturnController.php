<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\models\JetProductVariants;
use frontend\modules\jet\models\JetReturn;
use frontend\modules\jet\models\JetReturnSearch;

use frontend\modules\jet\components\Data;

/**
 * JetreturnController implements the CRUD actions for JetReturn model.
 */
class JetreturnController extends JetmainController
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
     * Lists all JetReturn models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) 
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        
        $searchModel = new JetReturnSearch();       
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            // 'countReadyReturns'=>$countReadyReturn,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single JetReturn model.
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
     * Creates a new JetReturn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) 
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        
        $false_return = $success_return = "";
        $success_count = $false_count = $count = 0;
        $merchant_id = MERCHANT_ID;
        
        $model1 = new JetReturn();
               
        $data = $this->jetHelper->CGetRequest('/returns/created',$merchant_id);
        $response  = json_decode($data,true);
        
        if(!empty($response) && count($response['return_urls'])>0)
        {            
            foreach($response['return_urls'] as $res)
            {
                $arr = $resultdata = [];
                $arr=explode("/",$res);
                $returnid="";
                $returnid=$arr[3];
                
                $resultdata=$model1->find()->where(['merchant_id' => $merchant_id,'returnid' => $returnid])->one();
                if($resultdata=="")
                {
                    $returndetails="";
                    $returndetails = $this->jetHelper->CGetRequest(rawurlencode($res),$merchant_id);

                    if($returndetails)
                    {
                        $return = [];
                        $return = json_decode($returndetails,true);
                        $return['timestamp']=date('d-m-Y H:i:s');
                        $return_merchant_skus=$return['return_merchant_SKUs'];
                                                
                        $count++;
                        $model=new JetReturn();
                        $model->returnid=$returnid;
                        $model->order_reference_id=$return['reference_order_id'];
                        $model->merchant_id=$merchant_id;
                        $model->return_data=json_encode($return);
                        $model->save(false);                        
                    }
                }
            }
        }
        if($count>0)  
            Yii::$app->session->setFlash('success',$count." Return Request has been fetched from jet successfully"); 
        else
            Yii::$app->session->setFlash('success', " No more Return(s) Fetched from Jet");
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing JetReturn model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $status = "";
        $refund = 0;
        $merchant_id = MERCHANT_ID;
        $model1 = $this->findModel($id);

        if(Yii::$app->request->post())
        {            
            $model1->load(Yii::$app->request->post());            
            $order_items = $order_refund_data = $order_refund_items = [];
            $returnid = $order_id = $agree_to_refund = $agree_to_refund_fb = "";            
            $noRefund=false;
            
            $returnid=$_POST['JetReturn']['returnid'];
            $order_id=$_POST['JetReturn']['merchant_order_id'];
            $agree_to_refund=$_POST['JetReturn']['agreeto_return'];
            if($agree_to_refund==0)
            {
                $noRefund=true;
                $agree_to_refund_fb = $_POST['JetReturn']['return_charge_feedback'];
                if($agree_to_refund_fb=="")
                {
                    Yii::$app->session->setFlash('error', 'return_charge_feedback is required in case of agree_to_refund=>No');
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
            
            $order_items=$_POST['JetReturn']['order_item_id'];
            foreach ($order_items as $key=>$value)
            {
                $total=0.00;
                $total = (float)$_POST['JetReturn']['principal'][$key]+$_POST['JetReturn']['shipping_cost'][$key]+$_POST['JetReturn']['shipping_tax'][$key]+$_POST['JetReturn']['tax'][$key];
                if($total<(float)$_POST['JetReturn']['principal_real'][$key] || $noRefund)
                {
                    if($_POST['JetReturn']['return_refund_feedback'][$key]==""){
                        Yii::$app->session->setFlash('error', 'Please enter return_refund_feedback in case of partial refund.');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                    $refund = (int)$_POST['JetReturn']['total_quantity_returned'][$key];
                    $order_refund_items[]=array(
                        'order_item_id'=>$value,
                        'total_quantity_returned'=>(int)$_POST['JetReturn']['total_quantity_returned'][$key],
                        'order_return_refund_qty'=>(int)$_POST['JetReturn']['total_quantity_returned'][$key],
                        'return_refund_feedback'=>$_POST['JetReturn']['return_refund_feedback'][$key],
                        'refund_amount'=>array(
                            'principal'=>(float)$_POST['JetReturn']['principal'][$key],
                            'tax'=>(float)$_POST['JetReturn']['tax'][$key],
                            'shipping_cost'=>(float)$_POST['JetReturn']['shipping_cost'][$key],
                            'shipping_tax'=>(float)$_POST['JetReturn']['shipping_tax'][$key],
                    	)
                    );
                }elseif($total>(float)$_POST['JetReturn']['principal_real'][$key])
                {
                    Yii::$app->session->setFlash('error', 'Total refund amount must be less or equal to order amount');
                    return $this->redirect(Yii::$app->request->referrer);
                }
                else
                {
                    $order_refund_items[]=array(
                        'order_item_id'=>$value,
                        'total_quantity_returned'=>(int)$_POST['JetReturn']['total_quantity_returned'][$key],
                        'order_return_refund_qty'=>(int)$_POST['JetReturn']['total_quantity_returned'][$key],
                        'refund_amount'=>array(
                            'principal'=>(float)$_POST['JetReturn']['principal'][$key],
                            'tax'=>(float)$_POST['JetReturn']['tax'][$key],
                            'shipping_cost'=>(float)$_POST['JetReturn']['shipping_cost'][$key],
                            'shipping_tax'=>(float)$_POST['JetReturn']['shipping_tax'][$key],
                        )
                    );
                }
            }

            if($noRefund)
            {
                $order_refund_data = array(
                    'merchant_order_id'=>$order_id,
                    'agree_to_return_charge'=>false,
                    'return_charge_feedback'=>$agree_to_refund_fb,
                    'items'=>$order_refund_items,
                );
            }
            else
            {
                $order_refund_data = array(
                    'merchant_order_id'=>$order_id,
                    'items'=>$order_refund_items,
                    'agree_to_return_charge'=>true,
                );
            }
            
            $data="";
            $data=$this->jetHelper->CPutRequest('/returns/'.rawurlencode($returnid).'/complete',json_encode($order_refund_data),$merchant_id,$status);
            $responsedata=[];
            $responsedata=json_decode($data,true);
            
            if($status==204)
            {
                foreach($order_items as $key=>$value)
                {
                    $flag=false;
                    $product=[];
                    $product=JetProduct::find()->where(['merchant_id' => $merchant_id,'sku'=>$_POST['JetReturn']['sku'][$key]])->one();
                    //shopify call first
                    if($product=='')
                    {
                        $vproduct="";
                        $vproduct=JetProductVariants::find()->where(['merchant_id' => $merchant_id,'option_sku'=>$_POST['JetReturn']['sku'][$key]])->one();
                        if($vproduct)
                            $flag=true;
                    }
                    $updateInventory=array();
                    if($flag)
                        $updateQty=(int)$vproduct['option_qty']+$refund;
                    else
                        $updateQty=(int)$product['qty']+$refund;
                    
                    if($flag)
                    {
                        $updateInventory['variant']=array(
                                "id" => $vproduct['option_id'],
                                "inventory_quantity"=> $updateQty,
                                "old_inventory_quantity"=>$vproduct['option_qty'],
                        );
                        $response = $this->sc->call('PUT', '/admin/variants/'.$vproduct['option_id'].'.json',$updateInventory);
                    }
                    else
                    {
                        $updateInventory['variant']=array(
                                "id" => $product['variant_id'],
                                "inventory_quantity"=> $updateQty,
                                "old_inventory_quantity"=>$product['qty'],
                        );
                        $response = $this->sc->call('PUT', '/admin/variants/'.$product['variant_id'].'.json',$updateInventory);
                    }
                }
                //save records in database
                $order_refund_data['timestamp']=date('d-m-Y H:i:s');
                $model1->agreeto_return=$agree_to_refund;
                $model1->response_return_data=json_encode($order_refund_data);
                $model1->status="inprogress";
                $model1->save(false);
                Data::sqlRecords("UPDATE `jet_order_detail` SET `order_real_status`='order returned' WHERE `merchant_id`='".$merchant_id."' AND `merchant_order_id`='".$order_id."'  ",null,'update');
                Yii::$app->session->setFlash('success', 'Return Request has been sent successfully');
                return $this->redirect('index');
            }
            elseif(isset($responsedata['errors']))
            {
            	Yii::$app->session->setFlash('error', 'Return error from jet: '.$responsedata['errors'][0]);
                return $this->render('update', [
                        'model' => $model1,
                ]);
            }
            return $this->redirect(['index']);
        }
        else
        {
            return $this->render('update', [
                    'model' => $model1,
            ]);
        }
    }
    /**
     * Deletes an existing JetReturn model.
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
    public function actionReturnstatus()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = MERCHANT_ID;
                
        $completemerchantdata="";
        $count=0;
        $status=array('completed by merchant','inprogress');
        foreach ($status as $val)
        {
            $returnData="";
            $returnArray = [];
            $returnData=$this->jetHelper->CGetRequest('/returns/'.rawurlencode($val),$merchant_id);
            $returnArray=json_decode($returnData,true);
            if(count($returnArray)>0 && isset($returnArray['return_urls']))
            {
                foreach ($returnArray['return_urls'] as $value)
                {
                    $modelReturn="";
                    $arr = [];
                    $arr = explode("/",$value);
                    $returnid="";
                    $returnid=$arr[3];
                    $modelReturn = JetReturn::find()->where(['merchant_id'=>$merchant_id,'returnid'=>$returnid])->one();
                    if($modelReturn){
                        $count++;
                        $modelReturn->status=$val;
                        $modelReturn->save(false);
                    }
                }
            }
        }
        if($count>0)
            Yii::$app->session->setFlash('success', $count." Return status(s) have been updated successfully");
        else
            Yii::$app->session->setFlash('success',"No more return status(s) updated");
        return $this->redirect(['index']);
    }
    /**
     * Finds the JetReturn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JetReturn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JetReturn::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionViewreturndetails()
    {
        $this->layout="main2";
        if(Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $responseOrders = [];
        $response = $returnId = $status = "";

        $merchant_id = MERCHANT_ID;
        
        $returnId = trim($_POST['return_id']);        
               
        $response = $this->jetHelper->CGetRequest('/returns/state/'.rawurlencode($returnId),$merchant_id,$status);         
        $responseOrders = json_decode($response,true);
        
        if ($status!=200) {
            Yii::$app->session->setFlash('error',"Jet API is not running Properly, Please try again later");
            return $this->redirect(['index']);
        }           
        return $this->render('viewreturndetail', ['model' => $responseOrders],true);                       
    } 
}
