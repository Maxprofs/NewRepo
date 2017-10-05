<?php

namespace frontend\modules\jetapi\controllers;

use frontend\modules\jetapi\components\Salesorder;
use frontend\modules\jetapi\components\Failedorder;
use frontend\modules\jetapi\components\Returnorder;
use frontend\modules\jetapi\components\Refundorder;
use frontend\modules\jetapi\components\Vieworder;
use yii\helpers\BaseJson;
use Yii;

class JetorderdetailController extends JetapiController
{

    public function actionOrderlist()
    {
        $Output = Yii::$app->request->post();
        if (isset($Output['filter'])) {
            $Output['filter'] = json_decode($Output['filter'], true);

            if (isset($Output['filter']['status']) && !empty($Output['filter']['status'])) {
                $status = $Output['filter']['status'];

                if ($status == 'acknowledged' || $status =='complete' || $status == 'canceled' || $status == 'inprogress') {
                    $response = self::Sales($Output);

                } elseif ($Output['filter']['status'] == 'refund') {

                    $response = self::Refund($Output);

                } else {

                    $response = ['success'=>false,'message'=>'Undefined value of status'];
                }
            } else {
                $response = self::Sales($Output);
            }
        }else{
            $response = self::Sales($Output);
        }

        return BaseJson::encode($response);

    }

    /**
     * @return string
     */
    public function Sales($Output)
    {
//        $output = Yii::$app->request->post();
        $data = "";
        $queryObj = "";
        $data = Salesorder::getOrderDetail($Output);
        return $data;
    }


    /**
     * @return string
     */
//        public function Failed()
//        {
//        $output = Yii::$app->request->post();
//        $data = "";
//        $queryObj= "";
//        $data = Failedorder::getOrderDetail($output);
//        return BaseJson::encode($data);
//        }

    /**
     * @return string
     */
//        public function Returnorder()
//        {
//        $output = Yii::$app->request->post();
//        $data = "";
//        $queryObj= "";
//        $data = Returnorder::getOrderDetail($output);
//        return BaseJson::encode($data);
//        }


    /**
     * @return string
     */
    public function Refund($Output)
    {

        $data = "";
        $queryObj = "";
        $data = Refundorder::getOrderDetail($Output);
        return BaseJson::encode($data);
    }

    /**
     * @return string
     */
    public function actionView()
    {
        $output = Yii::$app->request->post();
        $data = "";
        $queryObj = "";
        $data = Vieworder::getViewOrderDetail($output);
        return BaseJson::encode($data);
    }

}

?>