<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\filters\VerbFilter;

use common\models\User;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\JetDataValidation;


/**
 * JetproductController implements the CRUD actions for JetProduct model.
 */
class JetvalidateController extends JetmainController
{
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
    protected $connection;
    /**
     * Lists all JetProduct models.
     * @return mixed
     */
    
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        
        $result = [];
        $merchant_id = MERCHANT_ID;
        $result = JetDataValidation::validateData([], $merchant_id);
        if(count($result)>0){
            return $this->render('index', ['data' => $result]);
        }else{
            Yii::$app->session->setFlash('success',"All product(s) validated successfully. No error found.");
            return $this->redirect(['jetproduct/index']);
        }                
    }
    public function actionValidateMore(){

        $this->layout = "main2";

        $html = $this->render('validatemore');
        return $html;
    }
    public function actionExportValidation($merchant_id=false){
          $merchant_id = 14;
          $sql = "SELECT `jet_product`.`id`,COALESCE(`update_title`,`title`) as `title`,`sku`,`type`,`product_type`,COALESCE(`update_description`,`description`) as `description`,COALESCE(`update_price`,`price`) as `price`,`variant_id`,`image`,`qty`,`weight`,`attr_ids`,`jet_attributes`,`vendor`,`upc`,`barcode_type`,`mpn`,`ASIN`,`fulfillment_node`,`pack_qty` FROM `jet_product` LEFT JOIN `jet_product_details` ON `jet_product`.`id`=`jet_product_details`.`product_id` WHERE `jet_product`.`merchant_id`='".$merchant_id."'";

        $product = Data::sqlRecords($sql,'all','select');

        $i = 0;         
        $logPath = \Yii::getAlias('@webroot').'/var/sears/product/custom_csv/export/'.$merchant_id;
        if (!file_exists($logPath)){
            mkdir($logPath,0775, true);
        }
        $base_path=$logPath."/validated-".time().'.csv';
        $file = fopen($base_path,"w");
        
        $headers = array('Sku','Errors');

                
        foreach($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file,$row);
        foreach ($product as $val) {

            $carray = Jetproductinfo::checkBeforeDataPrepare((object)$val,$merchant_id);

            foreach ($carray['validated_error'] as $key => $value) {

                $productData[$i]['sku'] = $key;
                $productData[$i]['error'] = implode(" ", $value);
                $i++;
            }
        } 
       // print_r($productData);die;       
        foreach($productData as $v)
        {
            $row = [];
            $row[] =$v['sku'];
            $row[] =$v['error'];
        fputcsv($file,$row);
        }
        fclose($file);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return \Yii::$app->response->sendFile($base_path);  
    }

}
