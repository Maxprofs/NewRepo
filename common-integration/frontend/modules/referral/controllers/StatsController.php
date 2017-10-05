<?php

namespace frontend\modules\referral\controllers;

use Yii;
use frontend\modules\referral\models\ReferralUser;
use frontend\modules\referral\models\ReferralUserSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StatsController implements the CRUD actions for ReferralUser model.
 */
class StatsController extends AbstractReferrarController
{
    /**
     * Displays a single ReferralUser model.
     * @param integer $id
     * @return mixed
     */
    public function actionHistory()
    {
        return $this->redirect(['account/dashboard']);
        //return $this->render('history');
    }

    /**
     * Lists all ReferralUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReferralUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
