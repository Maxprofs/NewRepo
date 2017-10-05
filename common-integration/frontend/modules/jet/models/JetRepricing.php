<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_repricing".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $product_id
 * @property string $sku
 * @property integer $repricing
 * @property string $buybox_status
 * @property integer $merchant_price
 * @property integer $marketplace_price
 * @property integer $min_price
 * @property string $sales_sku_data
 */
class JetRepricing extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_repricing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'product_id', 'sku', 'buybox_status', 'merchant_price', 'marketplace_price', 'min_price', 'sales_sku_data'], 'required'],
            [['id', 'merchant_id', 'product_id', 'repricing', 'merchant_price', 'marketplace_price', 'min_price'], 'integer'],
            [['sales_sku_data'], 'string'],
            [['sku'], 'string', 'max' => 255],
            [['buybox_status'], 'string', 'max' => 244]
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
            'product_id' => 'Product ID',
            'sku' => 'Sku',
            'repricing' => 'Repricing',
            'buybox_status' => 'Buybox Status',
            'merchant_price' => 'Merchant Price',
            'marketplace_price' => 'Marketplace Price',
            'min_price' => 'Min Price',
            'sales_sku_data' => 'Sales Sku Data',
        ];
    }
}
