<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_configuration_can".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $seller_id
 * @property string $authorization
 * @property string $secret_key
 * @property string $manufacturer
 * @property string $default_store
 */
class NeweggConfigurationCan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_configuration_can';
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
            [['default_store'], 'string', 'max' => 20]
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
            'default_store' => 'Default Store',
        ];
    }
}
