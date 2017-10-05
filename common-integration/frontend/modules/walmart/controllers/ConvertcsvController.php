<?php
namespace frontend\modules\walmart\controllers;
use Yii;
use yii\web\Response;

class ConvertcsvController extends WalmartmainController
{
    /** index
    *   render view
    */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return $this->render('index');
    }
    /** convert format
    *   @return CSV FILE
    */
    public function actionConvert()
    {
        
    }

}
