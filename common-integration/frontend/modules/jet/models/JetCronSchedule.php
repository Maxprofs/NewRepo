<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_cron_schedule".
 *
 * @property integer $id
 * @property string $cron_name
 * @property string $cron_data
 */
class JetCronSchedule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_cron_schedule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cron_data'], 'string'],
            [['cron_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cron_name' => 'Cron Name',
            'cron_data' => 'Cron Data',
        ];
    }
}
