<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "insert_product".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $product_count
 * @property integer $not_sku
 * @property string $status
 * @property integer $total_product
 *
 * @property User $merchant
 */
class InsertProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'insert_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id'], 'required'],
            [['merchant_id', 'product_count', 'not_sku', 'total_product'], 'integer'],
            [['status'], 'string', 'max' => 255]
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
            'product_count' => 'Product Count',
            'not_sku' => 'Not Sku',
            'status' => 'Status',
            'total_product' => 'Total Product',
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
