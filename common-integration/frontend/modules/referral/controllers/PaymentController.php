<?php

namespace frontend\modules\referral\controllers;

use Yii;
use frontend\modules\referral\models\ReferrerPayment;
use frontend\modules\referral\models\ReferrerPaymentSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use frontend\modules\referral\components\Helper;

/**
 * PaymentController implements the CRUD actions for ReferrerPayment model.
 */
class PaymentController extends AbstractReferrarController
{
    /**
     * Lists all ReferrerPayment models.
     * @return mixed
     */
    public function actionIndex()
    {
        Helper::updatePaymentStatus();

        $searchModel = new ReferrerPaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRefresh()
    {
        Helper::updatePaymentStatus(true);
        Yii::$app->session->setFlash('success', 'Refreshed Successfully.');
        return $this->redirect(Yii::$app->request->referrer);
    }
}
