<?php
namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_order_detail".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $order_item_id
 * @property string $merchant_order_id
 * @property string $merchant_sku
 * @property string $deliver_by
 * @property string $shopify_order_id
 * @property string $status
 * @property string $order_data
 * @property string $shipment_data
 * @property string $reference_order_id
 * @property string $shipped_at
 
 */
class JetOrderDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_order_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'order_item_id', 'merchant_order_id', 'merchant_sku', 'deliver_by','shopify_order_name','shopify_order_id', 'status', 'order_data', 'shipment_data', 'reference_order_id'], 'required'],
            [['merchant_id'], 'integer'],
            [['order_item_id','order_data', 'shipment_data'], 'string'],
            //[[], 'string', 'max' => 100],
            [['reference_order_id', 'shopify_order_name','lines_items', 'merchant_order_id', 'merchant_sku','deliver_by','shopify_order_id', 'status','shipped_at'], 'string', 'max' => 255]
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
            'order_item_id' => 'Order Item ID',
            'merchant_order_id' => 'Merchant Order ID',
            'merchant_sku' => 'Merchant Sku',
            'deliver_by' => 'Deliver By',
            'shopify_order_name' => 'Shopify Order Name',
            'shopify_order_id' => 'Shopify Order ID',
            'lines_items'=>'lines_items',
            'status' => 'Status',
            'order_data' => 'Order Data',
            'shipment_data' => 'Shipment Data',
            'reference_order_id' => 'Reference Order ID',
            'shipped_at' => 'Order Shipped on',
        ];
    }
}
