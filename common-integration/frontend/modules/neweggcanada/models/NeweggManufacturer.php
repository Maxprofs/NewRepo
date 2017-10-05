<?php

namespace frontend\modules\neweggcanada\models;

use Yii;

/**
 * This is the model class for table "newegg_manufacturer".
 *
 * @property integer $id
 * @property string $merchant_id
 * @property string $manufacturer_name
 * @property string $manufacturer_url
 * @property string $manufacturer_support_email
 * @property string $manufacturer_support_phone
 * @property string $manufacturer_support_url
 * @property string $status
 */
class NeweggManufacturer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_can_manufacturer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'manufacturer_name', 'status'], 'required'],
            [['merchant_id', 'manufacturer_name', 'manufacturer_url', 'manufacturer_support_email', 'manufacturer_support_phone', 'manufacturer_support_url'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 100],
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
            'manufacturer_name' => 'Manufacturer Name',
            'manufacturer_url' => 'Manufacturer Url',
            'manufacturer_support_email' => 'Manufacturer Support Email',
            'manufacturer_support_phone' => 'Manufacturer Support Phone',
            'manufacturer_support_url' => 'Manufacturer Support Url',
            'status' => 'Status',
        ];
    }
}
