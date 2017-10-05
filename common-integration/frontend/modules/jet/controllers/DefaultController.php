<?php

namespace frontend\modules\jet\controllers;

use yii\web\Controller;

/**
 * Default controller for the `jet` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['site/index']);
    }
}
