<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_refund".
 *
 * @property integer $id
 * @property string $refund_id
 * @property string $merchant_order_id
 * @property string $order_reference_id
 * @property integer $merchant_id
 * @property string $order_item_id
 * @property integer $quantity_returned
 * @property integer $refund_quantity
 * @property string $refund_reason
 * @property string $refund_feedback
 * @property double $refund_amount
 * @property double $refund_tax
 * @property double $refund_shippingcost
 * @property double $refund_shippingtax
 * @property string $refund_status
 *
 * @property User $merchant
 */
class JetRefund extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_refund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['refund_id', 'merchant_order_id', 'merchant_id', 'order_item_id', 'quantity_returned', 'refund_quantity', 'refund_reason', 'refund_feedback', 'refund_amount', 'refund_tax', 'refund_shippingcost', 'refund_shippingtax', 'refund_status'], 'required'],
            [['merchant_id', 'quantity_returned', 'refund_quantity'], 'integer'],
            [['refund_amount', 'refund_tax', 'refund_shippingcost', 'refund_shippingtax'], 'number'],
            [['refund_id', 'refund_status'], 'string', 'max' => 100],
            [['merchant_order_id', 'order_reference_id', 'order_item_id'], 'string', 'max' => 255],
            [['refund_reason'], 'string', 'max' => 70],
            [['refund_feedback'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'refund_id' => 'Refund ID',
            'merchant_order_id' => 'Merchant Order ID',
            'order_reference_id' => 'Order Reference ID',
            'merchant_id' => 'Merchant ID',
            'order_item_id' => 'Order Item ID',
            'quantity_returned' => 'Quantity Returned',
            'refund_quantity' => 'Refund Quantity',
            'refund_reason' => 'Refund Reason',
            'refund_feedback' => 'Refund Feedback',
            'refund_amount' => 'Refund Amount',
            'refund_tax' => 'Refund Tax',
            'refund_shippingcost' => 'Refund Shippingcost',
            'refund_shippingtax' => 'Refund Shippingtax',
            'refund_status' => 'Refund Status',
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
