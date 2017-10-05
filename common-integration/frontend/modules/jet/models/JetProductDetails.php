<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_product_details".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $merchant_id
 * @property string $update_title
 * @property double $update_price
 * @property string $update_description
 * @property string $status
 * @property string $updated_at
 *
 * @property User $merchant
 * @property JetProduct $product
 */
class JetProductDetails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_product_details';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'merchant_id'], 'required'],
            [['product_id', 'merchant_id'], 'integer'],
            [['update_title', 'update_description'], 'string'],
            [['update_price'], 'number'],
            [['updated_at'], 'safe'],
            [['status'], 'string', 'max' => 255],
            [['merchant_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['merchant_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => JetProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'merchant_id' => 'Merchant ID',
            'update_title' => 'Update Title',
            'update_price' => 'Update Price',
            'update_description' => 'Update Description',
            'status' => 'Status',
            'updated_at' => 'Updated At',
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
    public function getProduct()
    {
        return $this->hasOne(JetProduct::className(), ['id' => 'product_id']);
    }
}
