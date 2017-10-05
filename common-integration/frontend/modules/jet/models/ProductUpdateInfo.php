<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "product_update_info".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property double $price
 * @property integer $inventory
 * @property string $created_at
 * @property string $updated_at
 */
class ProductUpdateInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_update_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'price', 'inventory', 'created_at'], 'required'],
            [['merchant_id', 'inventory'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe']
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
            'price' => 'Price',
            'inventory' => 'Inventory',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
