<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/4/17
 * Time: 4:11 PM
 */
namespace frontend\modules\walmart\components;
use frontend\components\Data;
use frontend\modules\walmart\controllers\WalmartmainController;
use yii\helpers\Url;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
/**
 * FaqController
 */
class FaqController extends WalmartmainController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    public function actionIndex()
    {
        if(Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }

        try{
            $resultdata=array();
            $query="SELECT * FROM `walmart_faq` ";
            $resultdata = Data::sqlRecords($query,"all","select");

            return $this->render('index', [
                'data'=>$resultdata
            ]);
        }
        catch(Exception $e)
        {
            echo $e->getMessage();die;
        }
    }

}