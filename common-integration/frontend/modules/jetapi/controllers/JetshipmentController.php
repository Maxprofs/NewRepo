<?php

namespace frontend\modules\jetapi\controllers;

use frontend\modules\jetapi\components\Shipment;
use yii\helpers\BaseJson;
use Yii;

class JetshipmentController extends JetapiController
{
    /**
     * @return string
     */
    public function actionShipment()
    {
        $output = Yii::$app->request->post();

        $data = "";
        $queryObj= "";
        $data = Shipment::getShipment($output);
        return BaseJson::encode($data);
    }
}