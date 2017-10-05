<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_merchant_products".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $product_id
 * @property string $product_type
 * @property string $product_data
 *
 * @property User $merchant
 * @property User $merchant0
 * @property JetProduct $product
 */
class JetMerchantProducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_merchant_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'product_id', 'product_type', 'product_data'], 'required'],
            [['merchant_id', 'product_id'], 'integer'],
            [['product_data'], 'string']
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
           // 'product_type' => 'Product Type',
            'product_data' => 'Product Data',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant0()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(JetProduct::className(), ['id' => 'product_id']);
    }
}
