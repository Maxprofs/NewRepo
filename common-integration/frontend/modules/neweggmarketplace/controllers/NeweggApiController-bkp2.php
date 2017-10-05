<?php
namespace frontend\modules\neweggmarketplace\controllers;

use frontend\modules\neweggmarketplace\models\NeweggConfigurationCan;
use Yii;
use yii\web\Controller;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\models\NeweggConfiguration;
use frontend\modules\neweggmarketplace\components\Neweggappdetail;

class NeweggApiController extends Controller
{
    public function actionSave()
    {
        if ($postData = Yii::$app->request->post()) {
            $merchant_id = Yii::$app->user->identity->id;
            $seller_id = trim($_POST['seller_id']);
            $authorization = trim($_POST['authorization']);
            $secret_key = trim($postData['secret_key']);
            $newegg_us_detail = isset($_POST['newegg_us_detail']) ? trim($_POST['newegg_us_detail']) : '';
            $newegg_can_detail = isset($_POST['newegg_can_detail']) ? trim($_POST['newegg_can_detail']) : '';

            if ($newegg_us_detail) {

                if ($seller_id == "" || $authorization == "" || $secret_key == ""/* || $manufacturer==""*/) {
                    return json_encode(['error' => true, "message" => "Please Fill all Required Fields."]);
                }

                if (!Neweggappdetail::validateApiCredentials($seller_id, $secret_key, $authorization)) {
                    return json_encode(['error' => true, "message" => "Api credentials are invalid. Please enter valid api credentials"]);
                }
                /*if(!Neweggappdetail::validateManufacturer($seller_id, $secret_key, $authorization,$manufacturer)) {
                   return json_encode(['error'=>true, "message"=>"Manufacturer name is invalid. Please enter valid Manufacturer name"]);
               }*/

                //Check if Details are already used by some other merchant
                $data = Data::sqlRecords("SELECT `merchant_id` FROM `newegg_configuration` WHERE `seller_id`='" . $seller_id . "' AND `authorization`='" . $authorization . "' AND `secret_key`='" . $secret_key . "'", 'one');
                if ($data && isset($data['merchant_id']) && $data['merchant_id'] != $merchant_id) {
                    return json_encode(['error' => true, "message" => "Api Credentials are already in use."]);
                }
                $newegg_us_default = isset($_POST['newegg_us_default']) ? trim($_POST['newegg_us_default']) : '';

                $result = NeweggConfiguration::find()->where(['merchant_id' => $merchant_id])->one();
                if (is_null($result)) {
                    $model = new NeweggConfiguration();
                    $model->merchant_id = $merchant_id;
                    $model->seller_id = $seller_id;
                    $model->authorization = $authorization;
                    $model->secret_key = $secret_key;
                    $model->default_store = $newegg_us_default;
                    //$model->manufacturer = $manufacturer;
                    //$model->skype_id = $skype_id;
                    $model->save(false);
                } else {
                    $result->seller_id = $seller_id;
                    $result->authorization = $authorization;
                    $result->secret_key = $secret_key;
                    $result->default_store = $newegg_us_default;
                    //$result->manufacturer = $manufacturer;
                    //$result->skype_id = $skype_id;
                    $result->save(false);
                }

                return json_encode(['success' => true, "message" => "Api credentials has been Saved Successfully!"]);

            }
            if ($newegg_can_detail) {

                if ($seller_id == "" || $authorization == "" || $secret_key == "") {
                    return json_encode(['error' => true, "message" => "Please Fill all Required Fields."]);
                }

                if (!Neweggappdetail::validateCANApiCredentials($seller_id, $secret_key, $authorization)) {
                    return json_encode(['error' => true, "message" => "Api credentials are invalid. Please enter valid api credentials"]);
                }
                /*if(!Neweggappdetail::validateManufacturer($seller_id, $secret_key, $authorization,$manufacturer)) {
                   return json_encode(['error'=>true, "message"=>"Manufacturer name is invalid. Please enter valid Manufacturer name"]);
               }*/

                //Check if Details are already used by some other merchant
                $data = Data::sqlRecords("SELECT `merchant_id` FROM `newegg_configuration_can` WHERE `seller_id`='" . $seller_id . "' AND `authorization`='" . $authorization . "' AND `secret_key`='" . $secret_key . "'", 'one');
                if ($data && isset($data['merchant_id']) && $data['merchant_id'] != $merchant_id) {
                    return json_encode(['error' => true, "message" => "Api Credentials are already in use."]);
                }
                $newegg_can_default = isset($_POST['newegg_can_default']) ? trim($_POST['newegg_can_default']) : '';


                $result = NeweggConfigurationCan::find()->where(['merchant_id' => $merchant_id])->one();
                if (is_null($result)) {
                    $model = new NeweggConfigurationCan();
                    $model->merchant_id = $merchant_id;
                    $model->seller_id = $seller_id;
                    $model->authorization = $authorization;
                    $model->secret_key = $secret_key;
                    $model->default_store = $newegg_can_default;
                    //$model->manufacturer = $manufacturer;
                    //$model->skype_id = $skype_id;
                    $model->save(false);
                } else {
                    $result->seller_id = $seller_id;
                    $result->authorization = $authorization;
                    $result->secret_key = $secret_key;
                    $result->default_store = $newegg_can_default;
                    //$result->manufacturer = $manufacturer;
                    //$result->skype_id = $skype_id;
                    $result->save(false);
                }

                return json_encode(['success' => true, "message" => "Api credentials has been Saved Successfully!"]);

            }

        }
        return json_encode(['error' => true, "message" => "Api credentials are invalid. Please enter valid api credentials"]);
    }
}

