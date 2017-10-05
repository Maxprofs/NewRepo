<?php

namespace frontend\modules\jet\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\models\JetOrderImportError;
use frontend\modules\jet\models\JetOrderImportErrorSearch;


/**
 * JetorderimporterrorController implements the CRUD actions for JetOrderImportError model.
 */
class JetorderimporterrorController extends JetmainController
{
    protected $jetHelper;
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
     * Lists all JetOrderImportError models.
     * @return mixed
     */
    public function actionIndex()
    {       
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $searchModel = new JetOrderImportErrorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 50;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the JetOrderImportError model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JetOrderImportError the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JetOrderImportError::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionCancel($post=false)
    {
        $data = [];
        $calledAsFunction = false;
        if($post)
        {
            $calledAsFunction = true;
            $this->jetHelper = $post['jetHelper'];
        }
        else
        {  
            $post = $_POST;
        }

        $merchant_id = $post['merchant_id'];

        $merchant_order_id = $post['merchant_order_id'];
        
        if ($merchant_order_id) 
        {                       
            $resultResponse = $this->jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchant_order_id, $merchant_id);
            $data = json_decode($resultResponse, true);
            
            if (isset($data) && !empty($data)) 
            {
                $reference_order_id = $data['reference_order_id'];
                $order_items = $response = [];
                foreach ($data['order_items'] as $key => $value) 
                {
                    $sql = "SELECT qty from  `jet_product` WHERE `merchant_id`='{$merchant_id}' AND `sku`='{$value['merchant_sku']}'";
                    $simProd = Data::sqlRecords($sql,'one','select');
                    if (empty($simProd)) 
                    {
                        $sql = "SELECT option_qty from  `jet_product_variants` WHERE `merchant_id`='{$merchant_id}' AND `option_sku`='{$value['merchant_sku']}'";
                        $varProd = Data::sqlRecords($sql,'one','select');
                        if (empty($varProd)) 
                        {
                            $order_items[] = array(
                                "order_item_acknowledgement_status" => "nonfulfillable - invalid merchant SKU",
                                "order_item_id" => $value['order_item_id'],
                            );         
                        }
                        elseif ($varProd['option_qty']<$value['request_order_quantity']) 
                        {
                            $order_items[] = array(
                            "order_item_acknowledgement_status" => "nonfulfillable - no inventory",
                            "order_item_id" => $value['order_item_id'],
                            );
                        }else 
                        {
                            $order_items[] = array(
                                "order_item_acknowledgement_status" => "fulfillable",
                                "order_item_id" => $value['order_item_id'],
                            );
                        }     
                    }elseif ($simProd['qty']<$value['request_order_quantity']) 
                    {
                        $order_items[] = array(
                        "order_item_acknowledgement_status" => "nonfulfillable - no inventory",
                        "order_item_id" => $value['order_item_id'],
                        );
                    }
                    else
                    {
                        $order_items[] = array(
                            "order_item_acknowledgement_status" => "fulfillable",
                            "order_item_id" => $value['order_item_id'],
                        );
                    }                    
                }
                $Orderarray = array(
                    "acknowledgement_status" => "rejected - item level error", 
                    "alt_order_id" => "".time(),
                    "order_items" => $order_items,
                );
                                
                $result = $this->jetHelper->CPutRequest('/orders/'.rawurlencode($merchant_order_id).'/acknowledge', json_encode($Orderarray), $merchant_id);
                $response = json_decode($result, true);
                if ($response['errors']) 
                {
                    if($calledAsFunction){
                        return false;
                    }
                    
                    Yii::$app->session->setFlash('error', $response['errors'][0]);                    
                    return $this->redirect(['index']);                                        
                } 
                else 
                {                    
                    if($calledAsFunction)
                    {
                        $sql='INSERT INTO `jet_order_import_error`(`merchant_order_id`,`reference_order_id`,`merchant_id`,`reason`,`status`)
                            VALUES("'.$merchant_order_id.'","'.$reference_order_id.'","'.$merchant_id.'","Qty/SKU Not Available","canceled")';
                        Data::sqlRecords($sql,'','insert');
                        return true;
                    }
                    else
                    {
                        Data::sqlRecords("UPDATE `jet_order_import_error` SET `status` = 'canceled' WHERE `merchant_order_id`='".$merchant_order_id."' ",null,"update");
                    }
                    Yii::$app->session->setFlash('success', 'Order Successfully Cancelled on Jet');
                    return $this->redirect(['index']);
                }
            } else {
                if($calledAsFunction){
                    return false;
                }
                Yii::$app->session->setFlash('error', 'There is some jet order api issue');
                return $this->redirect(['index']);
            }
        }
    }

    public function actionVieworderdetails()
    {
        $this->layout="main2";
        if(Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $jetConfig = $data = $response = $responseOrders = array();
        $merchant_id = $merchantOrderid = "";

        $merchant_id = $_POST['merchant_id'];
        $merchantOrderid = $_POST['merchant_order_id'];
        $jetConfig =  Data::sqlRecords("SELECT `api_host`,`api_user`,`api_password` FROM `jet_configuration` WHERE `merchant_id`='".$merchant_id."' " ,"one","select");
        
        if(!empty($jetConfig))
        {
            $api_host=$jetConfig['api_host'];
            $api_user=$jetConfig['api_user'];
            $api_password=$jetConfig['api_password'];
            $jetHelper = new Jetapimerchant($api_host, $api_user, $api_password);
            $responseToken = "";
            $responseToken = $jetHelper->JrequestTokenCurl();
            if ($responseToken == false) {
                Yii::$app->session->setFlash('error',"Jet API is not running Properly, Please try later");
                return $this->redirect(['index']);
            }
            $response = $jetHelper->CGetRequest('/orders/withoutShipmentDetail/'.$merchantOrderid,$merchant_id);         
            $responseOrders = json_decode($response,true);
            
            $html=$this->render('vieworderdetail', [
                    'model' => $responseOrders,
            ],true);
            return $html;
        }                
    }
}
