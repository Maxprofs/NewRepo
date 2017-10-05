<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_order_detail".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $seller_id
 * @property string $shopify_order_name
 * @property string $order_data
 * @property string $order_status_description
 * @property integer $invoice_number
 * @property string $order_date
 */
class NeweggOrderDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_order_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'seller_id'], 'required'],
            [['merchant_id', 'invoice_number'], 'integer'],
            [['order_date'], 'safe'],
            [['seller_id', 'order_number','shopify_order_name', 'order_data'], 'string', 'max' => 255],
            [['order_status_description'], 'string', 'max' => 45],
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
            'seller_id' => 'Seller ID',
            'order_number' => 'Order Number',
            'shopify_order_name' => 'Shopify Order Name',
            'order_data' => 'Order Data',
            'order_status_description' => 'Order Status Description',
            'invoice_number' => 'Invoice Number',
            'order_date' => 'Order Date',
        ];
    }
}
