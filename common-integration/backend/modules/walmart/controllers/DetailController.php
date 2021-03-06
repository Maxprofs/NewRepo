<?php

namespace backend\modules\walmart\controllers;

use Yii;
use common\models\JetExtensionDetail;
/**
 * Reports controller
 */
class DetailController extends BaseController 
{
	
    public function actionIndex()
    {
        $date = date('Y-m-d');
        $date1 = date('Y-m-d',strtotime('-31 days', strtotime(date('Y-m-d'))));
        $date2 = date('Y-m-d',strtotime('-8 days', strtotime(date('Y-m-d'))));
        $date3 = date('Y-m-d',strtotime('-2 days', strtotime(date('Y-m-d'))));


        $today = JetExtensionDetail::find()->where(['install_date' => $date])->all();

        $month = JetExtensionDetail::find()->where('install_date > :install_date', [':install_date' => $date1])->all();

        $week = JetExtensionDetail::find()->where('install_date > :install_date', [':install_date' => $date2])->all();


        $yesterday = JetExtensionDetail::find()->where('install_date > :install_date', [':install_date' => $date3])->all();

        print_r($model);die;
         return $this->render('index', [
            
            'today' => $today,
            'month' => $month,
            'week' => $today,
            'week' => $week,
            
        ]);

    }
}
