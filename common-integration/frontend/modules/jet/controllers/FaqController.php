<?php

namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;

use Yii;
use yii\filters\VerbFilter;
/**
 * FaqController
 */
class FaqController extends JetmainController
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
            $query="SELECT * FROM `jet_faq` ";        
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