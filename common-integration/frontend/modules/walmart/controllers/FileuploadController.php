<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\UploadHandler;

class FileuploadController extends Controller
{

    /* V3 Item xml validation */
    public function actionIndex()
    {
        $upload_handler = new UploadHandler();
    }

}
