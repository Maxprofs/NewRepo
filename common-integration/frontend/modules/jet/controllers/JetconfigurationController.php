<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Jetproductinfo;

use frontend\modules\jet\models\JetConfiguration;
use frontend\modules\jet\components\Data;

use common\models\User;

use Yii; 
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * JetconfigurationController implements the CRUD actions for JetConfiguration model.
 */
class JetconfigurationController extends JetmainController
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

    /**
     * Lists all JetConfiguration models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $dataConfiguration = $dataConfig = $clientData = [];
        $merchant_id = MERCHANT_ID;
        
        if (Yii::$app->request->post()) 
        {                	
        	$query="SELECT * FROM `jet_email_template`";            
            $email = Data::sqlRecords($query,"all");            
            foreach ($email as $key => $value) 
            {
                $emailConfiguration['email/'.$value['template_title']] = isset($_POST['email/'.$value["template_title"]])?1:0;
            }
            if(!empty($emailConfiguration))
            {
                foreach ($emailConfiguration as $key => $value) 
                {
                    Data::sqlRecords("UPDATE `jet_config` SET `value`='".$value."' where `merchant_id`='".MERCHANT_ID."' AND `data`='".$key."'", null, "update");
                }
            }
            if (!empty($_POST['config_data'])) 
            {
                foreach ($_POST['config_data'] as $key11 => $value11) 
                {   
                    if($key11=='day_to_return' && (!is_numeric($value11) || (is_numeric($value11) && $value11<7))){
                        continue;
                    }    
                    if($key11!='setPrice' || $key11!='fixedPriceUpdate' || !$key11=="sync_product_enable") 
                    {
                        Data::jetsaveConfigValue($merchant_id,$key11 , $value11);                                        
                    }                     
                }
                $sync_values="";
                if(isset($_POST['config_data']['sync_product_enable']) && $_POST['config_data']['sync_product_enable']=='enable')
                {
                    $sync_values = json_encode($_POST['sync-fields']);
                    Data::jetsaveConfigValue($merchant_id,'sync_product_enable',$_POST['config_data']['sync_product_enable']);
                    Data::jetsaveConfigValue($merchant_id,'sync-fields',$sync_values);
                }
                if (isset($_POST['config_data']['setPrice']) && $_POST['config_data']['setPrice'] > 0) 
                {
                    if (isset($_POST['config_data']['fixedPriceUpdate']) && trim($_POST['config_data']['fixedPriceUpdate'])!="" ) 
                        $price_value = $_POST['config_data']['fixedPriceUpdate'].'-'.$_POST['config_data']['setPrice'];                                        
                    Data::jetsaveConfigValue($merchant_id,'set_price_amount',$price_value);
                }
                else
                {
                	Data::jetremoveConfigValue($merchant_id,'set_price_amount');	
                }
                if (isset($_POST['config_data']['dynamic_repricing']) && ($_POST['config_data']['dynamic_repricing'] == "Yes") ) 
                {
                	Data::jetremoveConfigValue($merchant_id,'set_price_amount');	
                }
            }
            if (isset($_POST['fullfilment_node_id']))             
            	Data::sqlRecords("UPDATE `jet_configuration` SET `fullfilment_node_id`='".$_POST['fullfilment_node_id']."' where `merchant_id`='".MERCHANT_ID."'", null, "update");
            
            if (isset($_POST['merchant_email']))
            	Data::sqlRecords("UPDATE `jet_shop_details` SET `email`='".$_POST['merchant_email']."' where `merchant_id`='".MERCHANT_ID."'", null, "update");
            
            Yii::$app->session->setFlash('success', "Configuration details have been saved!!!");
        }
        $dataConfiguration = $dataConfig = [];
        
        $dataConfig = Jetproductinfo::getConfigSettings($merchant_id);// Data from jet_config table
        $dataConfiguration = Data::getjetConfiguration($merchant_id); // Data from jet_configuration table
        $dataConfiguration['email']=Data::sqlRecords("SELECT `email` FROM `jet_shop_details` WHERE `merchant_id`='".MERCHANT_ID."' ","one","select");
       
        $query="SELECT * FROM `jet_email_template`";
        $email = Data::sqlRecords($query,"all","select");        
                
        return $this->render('index', [
            'model' => $dataConfiguration,            
            'clientData'=>$dataConfig,
            'email'=>$email,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = JetConfiguration::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
