<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_installation".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $status
 * @property string $step
 */
class JetInstallation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_installation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'status', 'step'], 'required'],
            [['merchant_id'], 'integer'],
            [['status'], 'string', 'max' => 100],
            [['step'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'Merchant ID',
            'status' => 'Status',
            'step' => 'Step',
        ];
    }
}
