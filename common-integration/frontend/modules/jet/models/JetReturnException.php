<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_return_exception".
 *
 * @property integer $product_id
 * @property integer $merchant_id
 * @property string $sku
 * @property integer $time_to_return
 * @property string $return_location_ids
 * @property string $return_shipping_methods
 */
class JetReturnException extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_return_exception';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'merchant_id', 'sku', 'time_to_return', 'return_location_ids', 'return_shipping_methods'], 'required'],
            [['product_id', 'merchant_id', 'time_to_return'], 'integer'],
            [['return_location_ids'], 'string'],
            [['sku', 'return_shipping_methods'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Product ID',
            'merchant_id' => 'Merchant ID',
            'sku' => 'Sku',
            'time_to_return' => 'Time To Return',
            'return_location_ids' => 'Return Location Ids',
            'return_shipping_methods' => 'Return Shipping Methods',
        ];
    }
}
