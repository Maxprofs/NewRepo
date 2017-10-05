<?php

namespace frontend\modules\walmart\controllers;
use frontend\modules\walmart\models\WalmartConfig;
use frontend\modules\walmart\models\WalmartConfiguration;
use Yii;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\controllers\WalmartmainController;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\Walmartappdetails;

/**
 * WalmartconfigurationController implements the CRUD actions for WalmartConfiguration model.
 */
class WalmartconfigurationController extends WalmartmainController
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
     * Lists all WalmartConfiguration models.
     * @return mixed
     */
    public function actionIndex()
    {
        $clientData=array();
        $isConfigurationExist=array();
        $consumer_id='';
        $secret_key='';
        $consumer_channel_type_id='';
        $first_address='';
        $second_address='';
        $city='';
        $state='';
        $zipcode='';
        $skype_id='';

        if ($postData = Yii::$app->request->post())
        {
            $query="SELECT * FROM `email_template`";
            $email = Data::sqlRecords($query,"all");

            foreach ($email as $key => $value) {
                $emailConfiguration['email/'.$value['template_title']] = isset($_POST['email/'.$value["template_title"]])  ? 1 : 0;
            }
            $consumer_id = trim($_POST['consumer_id']);
            $secret_key = trim($_POST['secret_key']);
            //$consumer_channel_type_id = trim($_POST['consumer_channel_type_id']);
            $consumer_channel_type_id = '7b2c8dab-c79c-4cee-97fb-0ac399e17ade';
            $skype_id = trim($_POST['skype_id']);

            //prepare custom price setting data
            if(isset($postData['updateprice']))
            {
                if($postData['updateprice']=="yes" && isset($postData['custom_price'],$postData['updateprice_value']) && $postData['updateprice_value'] && is_numeric($postData['updateprice_value']))
                    $postData['custom_price'] = $postData['custom_price'].'-'.$postData['updateprice_value'].'-'.$postData['custom_price_type'];
                else
                    $postData['custom_price']="";
            }

            //Check if Details are already used by some other merchant
            $data = Data::sqlRecords("SELECT `merchant_id` FROM `walmart_configuration` WHERE `consumer_id`='".$consumer_id."' AND `secret_key`='".$secret_key."'", 'all');

            $valid = false;
            if($data) {
                foreach ($data as $api) {
                    if(isset($api['merchant_id']) && $api['merchant_id'] == MERCHANT_ID) {
                        $valid = true;
                        break;
                    }
                }
            }else{
                $valid = true;
            }
            if(isset($postData['sync_product_enable']) && $postData['sync_product_enable']=='enable' && isset($postData['sync-fields']))
            {
                $sync_values = json_encode($postData['sync-fields']);
                $postData['sync-fields'] = $sync_values;
            }else{
                $postData['sync-fields'] = '';
            }

            if(!$valid)
            {
                Yii::$app->session->setFlash('error', "Api Credentials are already in use. Please enter valid api credentials");
                return $this->render('index', ['clientData' => $postData]);
            }

            //Check if Credentials are valid
            if(!Walmartappdetails::validateApiCredentials($consumer_id, $secret_key, $consumer_channel_type_id)  && !isset($postData['test']))
            {
                Yii::$app->session->setFlash('error', "Api credentials are invalid. Please enter valid api credentials");

                //return $this->render('index', ['clientData' => $postData]);
            }
            else
            {
                $isConfigurationExist = Data::sqlRecords("SELECT `consumer_id` FROM  walmart_configuration WHERE `merchant_id`='".MERCHANT_ID."' ", "one", "select");
                if (!empty($isConfigurationExist)){
                    Data::sqlRecords("UPDATE `walmart_configuration` SET `consumer_id`='".$consumer_id."',`secret_key`='".$secret_key."', `consumer_channel_type_id`='".$consumer_channel_type_id."',`skype_id`='".$skype_id."' where `merchant_id`='".MERCHANT_ID."'", null, "update");
                } else {
                    //save api credentials
                    Data::sqlRecords("INSERT INTO `walmart_configuration` (`merchant_id`, `consumer_id`,`secret_key`,`consumer_channel_type_id`,`skype_id`) values(".MERCHANT_ID.",'".$consumer_id."','".$secret_key."','".$consumer_channel_type_id."','".$skype_id."') ", null, "insert");
                }
            }

            $configFields = ['first_address', 'second_address', 'city', 'state', 'zipcode','tax_code','remove_free_shipping','custom_price','ordersync','advanced_attribute_form','inventory','import_product_option','partialorder','sync_product_enable','sync-fields'];

            /* Save Email Subscription Setting */
            /*if(!empty($emailConfiguration)){
                 foreach ($emailConfiguration as $key => $value)
                    {

                       Data::sqlRecords("UPDATE `walmart_config` SET `value`='".$value."' where `merchant_id`='".MERCHANT_ID."' AND `data`='".$key."'", null, "update");
                    }
            }*/
            if(!empty($emailConfiguration)){
                foreach ($emailConfiguration as $key => $value)
                {
                    $email =  Data::sqlRecords("SELECT * FROM `walmart_config` WHERE `merchant_id`='".MERCHANT_ID."' AND `data`='".$key."'", 'one');

                    if(!$email)
                    {

                        Data::sqlRecords("INSERT INTO `walmart_config` (`data`,`value`,`merchant_id`) values('" . $key . "','" . $value . "','" . MERCHANT_ID . "')", null, "insert");

                    }else{
                        Data::sqlRecords("UPDATE `walmart_config` SET `value`='".$value."' where `merchant_id`='".MERCHANT_ID."' AND `data`='".$key."'", null, "update");

                    }
                }
            }


            /* End */
            foreach ($postData as $key => $value)
            {
                if(in_array($key, $configFields))
                {
                    Data::saveConfigValue(MERCHANT_ID, $key, $value);
                }
            }
            $clientData = $postData;
            Yii::$app->session->setFlash('success','Walmart Configurations has been Saved Successfully!');
        }
        else
        {
            $walmart_configuration_data = Data::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id`,`skype_id` FROM  walmart_configuration WHERE `merchant_id`='".MERCHANT_ID."' ","one");
            $walmart_config_data = Data::sqlRecords("SELECT `data`,`value` FROM  walmart_config WHERE `merchant_id`='".MERCHANT_ID."' ","all","select");
            $clientData['consumer_id']=$walmart_configuration_data['consumer_id'];
            $clientData['secret_key']=$walmart_configuration_data['secret_key'];
            $clientData['consumer_channel_type_id']=$walmart_configuration_data['consumer_channel_type_id'];
            $clientData['skype_id']=$walmart_configuration_data['skype_id'];
            $clientData['first_address']=$first_address;
            $clientData['second_address']=$second_address;
            $clientData['city']=$city;
            $clientData['state']=$state;
            $clientData['zipcode']=$zipcode;
            /*$clientData['import_product_option']=$walmart_config_data['import_product_option'];*/
          
            if (!empty($walmart_config_data))
            {
                foreach ($walmart_config_data as $val)
                {

                     $clientData[$val['data']] = $val['value'];
                }

            }
        }
        return $this->render('index', ['clientData' => $clientData]);
    }

    /**
     * Displays a single WalmartConfiguration model.
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
     * Creates a new WalmartConfiguration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WalmartConfiguration();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing WalmartConfiguration model.
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
     * Deletes an existing WalmartConfiguration model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the WalmartConfiguration model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WalmartConfiguration the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartConfiguration::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

