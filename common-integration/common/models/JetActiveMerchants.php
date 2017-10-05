<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jet_active_merchants".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $shop_url
 * @property string $created_at
 * @property string $updated_at
 */
class JetActiveMerchants extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_active_merchants';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'shop_url', 'created_at'], 'required'],
            [['merchant_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['shop_url'], 'string', 'max' => 255]
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
            'shop_url' => 'Shop Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
