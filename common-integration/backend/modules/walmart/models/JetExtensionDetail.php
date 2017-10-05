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
class JetExtensionDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_extension_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'install_date', 'date', 'expire_date', 'email', 'shopurl'], 'required'],
            [['merchant_id'], 'integer'],
            [['install_date', 'date', 'expire_date', 'uninstall_date'], 'safe'],
            [['shopurl'], 'string'],
            [['email', 'status', 'app_status'], 'string', 'max' => 255],
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
            'shopurl' => 'Shopurl',
            'status' => 'Status',
            'app_status' => 'App Status',
            'uninstall_date' => 'Uninstall Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return /*$this->hasOne(User::className(), ['id' => 'merchant_id'])*/'';
    }
}
