<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\models\WalmartAttributeMap;

use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Walmartapi;

class WalmartCarriersMapController extends WalmartmainController
{
    public function actionIndex()
    {
        $merchant_id = MERCHANT_ID;
        $query = "SELECT value FROM `walmart_config` WHERE `merchant_id`={$merchant_id} AND data='shipping_mappings' ";
        $result = Data::sqlRecords($query, null, 'select');
        
        $shipping_mappings = '';
        if(count($result)==0)
        {
            $shipping_mappings = [];
        }
        else
        {
            $shipping_mappings = json_decode($result[0]['value'],true);
        }
        $carriers = ['UPS','USPS','FedEx','Airborne','OnTrac'];
        
        return $this->render('index',['mappings'=>$shipping_mappings,'carriers'=>$carriers]);
    }

    public function actionSave()
    {
        $merchant_id = MERCHANT_ID;
        $data = Yii::$app->request->post();        
        if(isset($data['carrier'])) {

            $result = [];
            foreach ($data['carrier']['shopify'] as $key => $value) {
                $result[$this->getKey($value)] = ['shopify' => $value, 'walmart' => $data['carrier']['walmart'][$key]];
            }
           
            $result = addslashes(json_encode($result));
            $query = "SELECT value FROM `walmart_config` WHERE `merchant_id`={$merchant_id} AND data='shipping_mappings' ";
            $result1 = Data::sqlRecords($query, null, 'select');
            if (count($result1) == 0) {
                $query = "INSERT INTO `walmart_config`(`merchant_id`,`data`,`value`) VALUES('{$merchant_id}','shipping_mappings','{$result}') ";
                Data::sqlRecords($query, null, 'insert');
            } else {
                $query = "UPDATE `walmart_config` SET `value`='{$result}' WHERE `merchant_id`={$merchant_id} AND data='shipping_mappings' ";
                Data::sqlRecords($query, null, 'update');
            }
            Yii::$app->session->setFlash('success', "Shopify carrier map with Walmart carrier successfully...");

        }else{
            $query = "DELETE from `walmart_config` WHERE `merchant_id`='".$merchant_id ."' AND data='shipping_mappings' ";
            Data::sqlRecords($query, null, 'delete');
            Yii::$app->session->setFlash('success', "Successfully Deleted all carrier mapping...");
        }
        return $this->redirect(['index']);
    }

    public function getKey($value){
        return trim(str_replace(' ','',$value));
    }

}
