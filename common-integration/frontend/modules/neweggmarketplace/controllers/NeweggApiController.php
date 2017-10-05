<?php
namespace frontend\modules\neweggmarketplace\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\models\NeweggConfiguration;
use frontend\modules\neweggmarketplace\components\Neweggappdetail;

class NeweggApiController extends Controller
{
    public function actionSave()
    {
        if ($postData = Yii::$app->request->post())
        {
            $merchant_id = Yii::$app->user->identity->id;
            $seller_id = trim($_POST['seller_id']);
            $authorization = trim($_POST['authorization']);
            $secret_key = trim($postData['secret_key']);

            if($seller_id == "" || $authorization == "" || $secret_key == ""/* || $manufacturer==""*/) {
                return json_encode(['error'=>true, "message"=>"Please Fill all Required Fields."]);
            }

            if(!Neweggappdetail::validateApiCredentials($seller_id, $secret_key, $authorization)) {
                return json_encode(['error'=>true, "message"=>"Api credentials are invalid. Please enter valid api credentials"]);
            }

            //Check if Details are already used by some other merchant
            $data = Data::sqlRecords("SELECT `merchant_id` FROM `newegg_configuration` WHERE `seller_id`='".$seller_id."' AND `authorization`='".$authorization."' AND `secret_key`='".$secret_key."'", 'one');
            if($data && isset($data['merchant_id']) && $data['merchant_id'] != $merchant_id) {
                return json_encode(['error'=>true, "message"=>"Api Credentials are already in use."]);
            }

            $result = NeweggConfiguration::find()->where(['merchant_id'=>$merchant_id])->one();
            if(is_null($result)) {
                $model = new NeweggConfiguration();
                $model->merchant_id = $merchant_id;
                $model->seller_id = $seller_id;
                $model->authorization = $authorization;
                $model->secret_key = $secret_key;
                $model->save(false);
            } else {
                $result->seller_id = $seller_id;
                $result->authorization = $authorization;
                $result->secret_key = $secret_key;
                $result->save(false);
            }

            return json_encode(['success'=>true, "message"=>"Api credentials has been Saved Successfully!"]);
        }
        return json_encode(['error'=>true, "message"=>"Api credentials are invalid. Please enter valid api credentials"]);
    }
}

