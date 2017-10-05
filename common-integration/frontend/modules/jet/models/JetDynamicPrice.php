<?php

namespace frontend\modules\jet\models;
use common\models\User;
use Yii;

/**
 * This is the model class for table "jet_dynamic_price".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $product_id
 * @property string $sku
 * @property double $min_price
 * @property double $current_price
 * @property double $max_price
 *
 * @property User $merchant
 */
class JetDynamicPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_dynamic_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id'], 'required'],
            [['merchant_id', 'product_id'], 'integer'],
            [['min_price', 'current_price', 'max_price','bid_price'], 'number'],
            [['sku'], 'string', 'max' => 255],
            [['merchant_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['merchant_id' => 'id']],
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
            'min_price' => 'Min Price',
            'current_price' => 'Current Price in App',
            'max_price' => 'Max Price',
            'bid_price' => 'Bid price',
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
