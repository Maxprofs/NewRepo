<?php

namespace backend\modules\reports\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\modules\reports\components\Data;



/**
 * check send email read by user or not
 */
class ClientEmailReadController extends BaseController
{
    /**
     * @inheritdoc
     */
        /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

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
