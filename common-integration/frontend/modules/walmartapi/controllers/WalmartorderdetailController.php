<?php

namespace frontend\modules\walmartapi\controllers;

use frontend\modules\walmartapi\components\Orderdetail;
use frontend\modules\walmartapi\components\Vieworder;
use yii\helpers\BaseJson;
use Yii;

class WalmartorderdetailController extends WalmartapiController
{
    /**
     * @return string
     */
        public function actionList()
        {
        $output = Yii::$app->request->post();
        $data = "";
        $queryObj= "";
        $data = Orderdetail::getOrderDetail($output);
        return BaseJson::encode($data);
        }


    /**
     * @return string
     */
        public function actionView()
        {

        $output = Yii::$app->request->post();
        $data = "";
        $queryObj= "";
        $data = Vieworder::getVieworder($output);
        return BaseJson::encode($data);
        }

}
?>