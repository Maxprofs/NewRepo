<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_configuration".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $seller_id
 * @property string $authorization
 * @property string $secret_key
 */
class NeweggConfiguration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_configuration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'seller_id', 'authorization', 'secret_key'], 'required'],
            [['merchant_id'], 'integer'],
            [['seller_id', 'authorization', 'secret_key'], 'string', 'max' => 255],
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
            'authorization' => 'Authorization',
            'secret_key' => 'Secret Key',
        ];
    }
}
