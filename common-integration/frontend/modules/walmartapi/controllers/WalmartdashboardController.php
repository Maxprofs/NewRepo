<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 25/10/16
 * Time: 4:28 PM
 */
namespace frontend\modules\walmartapi\controllers;

use frontend\modules\walmartapi\components\Dashboard;
use yii\helpers\BaseJson;
use Yii;

class WalmartdashboardController extends WalmartapiController
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
        // print_r($data);die;
        return BaseJson::encode($data);
    }

}