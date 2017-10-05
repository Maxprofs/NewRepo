<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_shipment_details".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $order_increment_id
 * @property string $jet_order_id
 * @property string $shopify_order_id
 * @property string $shopify_shipment_id
 * @property string $order_items
 * @property string $ship_to_date
 * @property string $carrier_pick_up_date
 * @property string $expected_delivery_date
 * @property string $tracking_number
 * @property string $shipping_carrier
 */
class JetShipmentDetails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_shipment_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'order_increment_id', 'jet_order_id', 'shopify_order_id', 'shopify_shipment_id', 'order_items', 'ship_to_date', 'carrier_pick_up_date', 'expected_delivery_date', 'tracking_number', 'shipping_carrier'], 'required'],
            [['merchant_id', 'order_increment_id'], 'integer'],
            [['order_items', 'shipping_carrier'], 'string'],
            [['ship_to_date', 'carrier_pick_up_date', 'expected_delivery_date'], 'safe'],
            [['jet_order_id', 'shopify_order_id', 'shopify_shipment_id', 'tracking_number'], 'string', 'max' => 255]
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
            'order_increment_id' => 'Order Increment ID',
            'jet_order_id' => 'Jet Order ID',
            'shopify_order_id' => 'Shopify Order ID',
            'shopify_shipment_id' => 'Shopify Shipment ID',
            'order_items' => 'Order Items',
            'ship_to_date' => 'Ship To Date',
            'carrier_pick_up_date' => 'Carrier Pick Up Date',
            'expected_delivery_date' => 'Expected Delivery Date',
            'tracking_number' => 'Tracking Number',
            'shipping_carrier' => 'Shipping Carrier',
        ];
    }
}
