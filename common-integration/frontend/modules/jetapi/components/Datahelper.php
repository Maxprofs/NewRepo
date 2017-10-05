<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

class Datahelper extends Component
{
    /**
     * fire various sql query
     * @param $query string
     * @param $type string 
     * @param $querytype string 
     * @return array
     */
    public static function sqlRecords($query, $type = null, $queryType = null)
    {
        $connection = Yii::$app->getDb();
        $response = [];
        if ($queryType == "update" || $queryType == "delete" || $queryType == "insert") {
            $connection->createCommand($query)->execute();
        } elseif ($type == 'one') {
            $response = $connection->createCommand($query)->queryOne();
        } else {
            $response = $connection->createCommand($query)->queryAll();
        }
        unset($connection);
        return $response;
    }
}