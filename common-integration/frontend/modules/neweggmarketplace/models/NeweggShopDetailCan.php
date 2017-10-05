<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_shop_detail_can".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $shop_url
 * @property string $install_status
 * @property string $install_date
 * @property string $expire_date
 * @property string $purchase_date
 * @property string $purchase_status
 * @property string $uninstall_date
 * @property string $app_status
 * @property integer $refund_id
 */
class NeweggShopDetailCan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_shop_detail_can';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'shop_url', 'install_status', 'install_date', 'expire_date', 'purchase_status'], 'required'],
            [['merchant_id', 'refund_id'], 'integer'],
            [['shop_url'], 'string'],
            [['install_date', 'expire_date', 'purchase_date', 'uninstall_date'], 'safe'],
            [['install_status', 'purchase_status'], 'string', 'max' => 40],
            [['app_status'], 'string', 'max' => 45]
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
            'shop_url' => 'Shop Url',
            'install_status' => 'Install Status',
            'install_date' => 'Install Date',
            'expire_date' => 'Expire Date',
            'purchase_date' => 'Purchase Date',
            'purchase_status' => 'Purchase Status',
            'uninstall_date' => 'Uninstall Date',
            'app_status' => 'App Status',
            'refund_id' => 'Refund ID',
        ];
    }
}
