<?php

namespace backend\modules\walmart\models;

use Yii;

/**
 * This is the model class for table "jet_extension_detail".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $install_date
 * @property string $date
 * @property string $expire_date
 * @property string $email
 * @property string $shopurl
 * @property string $status
 * @property string $app_status
 * @property string $uninstall_date
 *
 * @property User $merchant
 */
class WalmartExtensionDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'walmart_extension_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'install_date', 'date', 'expire_date', 'email'], 'required'],
            [['merchant_id'], 'integer'],
            [['install_date', 'date', 'expire_date', 'uninstall_date'], 'safe'],
            [['email', 'status', 'app_status','shop_url'], 'string', 'max' => 255],
           /* [['merchant_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['merchant_id' => 'id']],*/
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
            'install_date' => 'Install Date',
            'date' => 'Date',
            'expire_date' => 'Expire Date',
            'email' => 'Email',
            'status' => 'Status',
            'app_status' => 'App Status',
            'uninstall_date' => 'Uninstall Date',
            'shop_url' =>'Shop Url',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWalmartShopDetails()
    {
        return $this->hasOne(WalmartShopDetails::className(), ['id' => 'id']);
    }
}
