<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_notifications".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $msg
 * @property string $status
 */
class JetNotifications extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_notifications';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'msg'], 'required'],
            [['merchant_id'], 'integer'],
            [['msg', 'status'], 'string']
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
            'msg' => 'Message',
            'status' => 'Status',
        ];
    }
}
