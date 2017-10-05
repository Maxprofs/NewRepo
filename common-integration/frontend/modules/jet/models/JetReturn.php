<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_return".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $returnid
 * @property string $order_reference_id
 * @property string $agreeto_return
 * @property string $return_data
 * @property string $response_return_data
 * @property string $status
 *
 * @property User $merchant
 */
class JetReturn extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_return';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'returnid', 'order_reference_id', 'agreeto_return'], 'required'],
            [['merchant_id'], 'integer'],
            [['return_data', 'response_return_data'], 'string'],
            [['returnid', 'order_reference_id', 'agreeto_return', 'status'], 'string', 'max' => 255]
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
            'returnid' => 'Return Id',
            'order_reference_id' => 'Merchant Order',
            'agreeto_return' => 'Agreeto Return',
            'return_data' => 'Return Data',
            'response_return_data' => 'Response Return Data',
            'status' => 'Status',
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
