<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\components\Data;



/**
 * check send email read by user or not
 */
class ClientEmailReadController extends BaseController
{
    

    public function actionIndex($id)
    {
        $today = date("y/m/d");
        $connection=Yii::$app->getDb();
        $table = Data::EMAIL_REPORT;
        $query = "UPDATE `{$table}` SET `read_at`='" . $today . "', `mail_status`='read' where tracking_id='" . $id . "' ";
        $updateMailStatus = $connection->createCommand($query)->execute();
        $img = file_get_contents(Yii::getAlias('@weburl').'/images/transparent-image.png');
        header("Content-type: image/png");
        return $img;

        
    }

    
}
