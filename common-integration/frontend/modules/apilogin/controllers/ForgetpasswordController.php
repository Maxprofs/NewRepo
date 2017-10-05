<?php

namespace frontend\modules\apilogin\controllers;

use frontend\modules\apilogin\components\Forgetpassword;
use frontend\modules\apilogin\components\Newpassword;
use frontend\modules\walmartapi\controllers\WalmartapiController;
use yii\helpers\BaseJson;
use yii\web\Controller;
use Yii;

class ForgetpasswordController extends Controller
{
    /**
     * @return string
     */
    public function beforeAction($action) { 
        $this->enableCsrfValidation = false; 
        return parent::beforeAction($action);
    }

    public function actionForgetpassword()
    {
        $output = Yii::$app->request->post();

        $data = "";
        $queryObj= "";
        $data = Forgetpassword::getPassword($output);
        return BaseJson::encode($data);
    }

    /**
     * @return string
     */
    public function actionNewpassword()
    {
        $output = Yii::$app->request->post();

        $data = "";
        $queryObj= "";
        $data = Newpassword::getPassword($output);
        return BaseJson::encode($data);
    }
}