<?php

namespace frontend\modules\jetapi\controllers;

use frontend\modules\jetapi\components\Dashboard;
use yii\helpers\BaseJson;
use Yii;

class JetdashboardController extends JetapiController
{

    /**
     * @return string
     */
    public function actionDashboard()
    {
        $output = Yii::$app->request->post();

        $data = "";
        $queryObj= "";
        $data = Dashboard::getDashboardInfo($output);
        return BaseJson::encode($data);
    }

}