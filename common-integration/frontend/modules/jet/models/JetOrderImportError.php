<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_order_import_error".
 *
 * @property integer $id
 * @property string $merchant_order_id
 * @property string $reason
 * @property string $created_at
 * @property integer $merchant_id
 *
 * @property User $merchant
 */
class JetOrderImportError extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_order_import_error';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_order_id', 'reason', 'created_at', 'merchant_id'], 'required'],
            [[ 'reason','status', 'created_at'], 'string'],
            [['merchant_order_id','reference_order_id'], 'string', 'max' => 255],
            [['merchant_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_order_id' => 'Merchant Order ID',
            'reference_order_id'=>'Merchant Order',
            'reason' => 'Reason',
            'status' =>'Status',
            'created_at' => 'Created At',
            'merchant_id' => 'Merchant ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }
}
