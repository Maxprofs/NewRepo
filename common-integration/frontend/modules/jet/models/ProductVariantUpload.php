<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "product_variant_upload".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $product_id
 * @property integer $option_sku
 * @property integer $status
 * @property string $created_at
 */
class ProductVariantUpload extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_variant_upload';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'product_id', 'option_sku', 'status', 'created_at'], 'required'],
            [['merchant_id', 'product_id', 'option_sku', 'status'], 'integer'],
            [['created_at'], 'safe']
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
            'option_sku' => 'Option Sku',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
