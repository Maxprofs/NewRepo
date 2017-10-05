<?php

namespace frontend\modules\referral\models;

use Yii;
use common\models\User;

/**
 * This is the model class for table "referral_user".
 *
 * @property integer $id
 * @property integer $referrer_id
 * @property integer $merchant_id
 * @property string $status
 * @property string $installation_date
 * @property string $payment_date
 */
class ReferralUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'referral_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['referrer_id', 'merchant_id', 'status'], 'required'],
            [['referrer_id', 'merchant_id'], 'integer'],
            [['installation_date', 'payment_date'], 'safe'],
            [['status'], 'string', 'max' => 50],
            [['app'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Referral ID',
            'referrer_id' => 'Referrer ID',
            'merchant_id' => 'Merchant ID',
            'status' => 'Status',
            'installation_date' => 'Installation Date',
            'payment_date' => 'Payment Date',
            'app' => 'Installed App'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }
}
