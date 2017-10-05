<?php

namespace frontend\modules\jet\models;

use Yii;
use common\models\User;

/**
 * This is the model class for table "jet_configuration".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $api_host
 * @property string $api_user
 * @property string $api_password
 * @property string $merchant
 * @property string $fullfilment_node_id
 * @property string $merchant_email
 * @property string $jet_token
 *
 * @property User $merchant0
 */
class JetConfiguration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_configuration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'api_host', 'api_user', 'api_password', 'merchant', 'fullfilment_node_id', 'jet_token'], 'required'],
            [['merchant_id'], 'integer'],
            [['api_host', 'api_user', 'api_password', 'fullfilment_node_id', 'merchant_email', 'jet_token'], 'string'],
            [['merchant'], 'string', 'max' => 255]
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
            'api_host' => 'Api Host',
            'api_user' => 'Api User',
            'api_password' => 'Api Password',
            'merchant' => 'Merchant',
            'fullfilment_node_id' => 'Fullfilment Node ID',
            'merchant_email' => 'Merchant Email',
            'jet_token' => 'Jet Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchantid()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }
}
