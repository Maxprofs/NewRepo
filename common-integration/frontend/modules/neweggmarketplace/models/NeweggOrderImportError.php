<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_order_import_error".
 *
 * @property integer $id
 * @property string $order_number
 * @property integer $merchant_id
 * @property string $error_reason
 * @property string $created_at
 * @property string $newegg_item_number
 */
class NeweggOrderImportError extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_order_import_error';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_number', 'merchant_id', 'error_reason', 'created_at'], 'required'],
            [['merchant_id'], 'integer'],
            [['error_reason'], 'string'],
            [['created_at'], 'safe'],
            [['order_number', 'newegg_item_number'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_number' => 'Order Number',
            'merchant_id' => 'Merchant ID',
            'error_reason' => 'Error Reason',
            'created_at' => 'Created At',
            'newegg_item_number' => 'Newegg Item Number',
        ];
    }
}
