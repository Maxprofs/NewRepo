<?php

namespace frontend\modules\referral\models;

use Yii;
use common\models\User;

/**
 * This is the model class for table "referrer_payment".
 *
 * @property integer $id
 * @property integer $payment_id
 * @property integer $referrer_id
 * @property string $amount
 * @property string $type
 * @property string $comment
 * @property string $payment_date
 * @property string $app
 * @property string $status
 */
class ReferrerPayment extends \yii\db\ActiveRecord
{
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_COMPLETE = 'complete';
    const PAYMENT_STATUS_REDEEMED = 'redeemed';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'referrer_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_id', 'referrer_id', 'amount', 'type', 'comment', 'app'], 'required'],
            [['payment_id', 'referrer_id', 'referral_id'], 'integer'],
            [['amount'], 'number'],
            [['comment'], 'string'],
            [['payment_date'], 'safe'],
            [['type', 'app'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_id' => 'Payment ID',
            'referrer_id' => 'Referrer ID',
            'referral_id' => 'Referral ID',
            'amount' => 'Amount',
            'type' => 'Type',
            'comment' => 'Comment',
            'payment_date' => 'Payment Date',
            'app' => 'App',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWalmart_recurring_payment()
    {
        return $this->hasOne(WalmartRecurringPayment::className(), ['id' => 'payment_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'referral_merchant_id']);
    }
}
