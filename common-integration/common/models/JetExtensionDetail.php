<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jet_extension_detail".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $date
 * @property string $expire_date
 * @property string $email
 * @property string $shopurl
 * @property integer $merchant_id
 * @property integer $status
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
            [['order_id', 'date', 'expire_date', 'email', 'shopurl', 'merchant_id', 'status'], 'required'],
            [['order_id', 'merchant_id', 'customer_id'], 'integer'],
            [['install_date','date', 'expire_date'], 'safe'],
            [['shopurl'], 'string'],
            [['email','status',	'app_status'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
        	'install_date'=>'Install Date',
            'date' => 'Date',
            'expire_date' => 'Expire Date',
            'email' => 'Email',
            'shopurl' => 'Shopurl',
            'merchant_id' => 'Merchant ID',
            'status' => 'Status',
        	'customer_id'=>'Customer ID',
        	'app_status'=>'App Status'	
        ];
    }
}
