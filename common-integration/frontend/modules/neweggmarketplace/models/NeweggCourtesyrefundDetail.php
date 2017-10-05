<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_courtesyrefund_detail".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $seller_id
 * @property string $courtesy_refund_id
 * @property integer $order_number
 * @property string $order_amount
 * @property integer $invoice_number
 * @property string $refund_amount
 * @property string $reason
 * @property string $note_to_customer
 * @property string $status
 * @property string $is_newegg_refund
 * @property string $in_user_name
 * @property string $in_date
 * @property integer $edit_user_name
 * @property string $edit_date
 */
class NeweggCourtesyrefundDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_courtesyrefund_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'seller_id', 'courtesy_refund_id', 'order_number', 'order_amount', 'invoice_number', 'refund_amount', 'reason', 'status', 'is_newegg_refund', 'in_user_name', 'in_date'], 'required'],
            [['merchant_id', 'order_number', 'invoice_number', 'edit_user_name'], 'integer'],
            [['order_amount', 'refund_amount'], 'number'],
            [['in_date', 'edit_date'], 'safe'],
            [['seller_id', 'reason', 'status', 'is_newegg_refund', 'in_user_name'], 'string', 'max' => 10],
            [['courtesy_refund_id', 'note_to_customer'], 'string', 'max' => 100],
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
            'courtesy_refund_id' => 'Courtesy Refund ID',
            'order_number' => 'Order Number',
            'order_amount' => 'Order Amount',
            'invoice_number' => 'Invoice Number',
            'refund_amount' => 'Refund Amount',
            'reason' => 'Reason',
            'note_to_customer' => 'Note To Customer',
            'status' => 'Status',
            'is_newegg_refund' => 'Is Newegg Refund',
            'in_user_name' => 'In User Name',
            'in_date' => 'In Date',
            'edit_user_name' => 'Edit User Name',
            'edit_date' => 'Edit Date',
        ];
    }
}
