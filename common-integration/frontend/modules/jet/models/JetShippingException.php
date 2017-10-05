<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_shipping_exception".
 *
 * @property integer $product_id
 * @property string $sku
 * @property integer $merchant_id
 * @property string $shipping_method
 * @property string $override_type
 * @property double $shipping_charge_amount
 * @property string $shipping_exception_type
 * @property string $service_level
 */
class JetShippingException extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_shipping_exception';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'sku', 'merchant_id', 'shipping_exception_type', 'service_level'], 'required'],
            [['product_id', 'merchant_id'], 'integer'],
            [['shipping_charge_amount'], 'number'],
            [['sku', 'shipping_method', 'override_type', 'shipping_exception_type', 'service_level'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Product ID',
            'sku' => 'Sku',
            'merchant_id' => 'Merchant ID',
            'shipping_method' => 'Shipping Method',
            'override_type' => 'Override Type',
            'shipping_charge_amount' => 'Shipping Charge Amount',
            'shipping_exception_type' => 'Shipping Exception Type',
            'service_level' => 'Service Level',
        ];
    }
}
