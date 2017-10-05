<?php
namespace frontend\modules\jet\controllers;
use yii\web\NotFoundHttpException;

use common\models\LatestUpdates;


/**
 * LatestUpdatesController implements the CRUD actions for LatestUpdates model.
 */
class LatestUpdatesController extends JetmainController
{
    /**
     * Displays a single LatestUpdates model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the LatestUpdates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return LatestUpdates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LatestUpdates::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
