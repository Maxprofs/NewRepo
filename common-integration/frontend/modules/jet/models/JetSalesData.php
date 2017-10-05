<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_sales_data".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $sku
 * @property string $sales_data
 * @property string $updated_at
 *
 * @property User $merchant
 */
class JetSalesData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_sales_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'sku', 'sales_data'], 'required'],
            [['merchant_id'], 'integer'],
            [['sales_data'], 'string'],
            [['updated_at'], 'safe'],
            [['sku'], 'string', 'max' => 255],
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
            'sku' => 'Sku',
            'sales_data' => 'Sales Data',
            'updated_at' => 'Updated At',
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
