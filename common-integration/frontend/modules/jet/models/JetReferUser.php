<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_refer_user".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $email
 * @property string $domain
 * @property string $is_pay
 * @property double $credit
 * @property string $date
 */
class JetReferUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_refer_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'domain', 'credit'], 'required'],
            [['merchant_id'], 'integer'],
            [['is_pay'], 'string'],
            [['credit'], 'number'],
            [['date'], 'safe'],
            [['email', 'domain'], 'string', 'max' => 255]
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
            'email' => 'Email',
            'domain' => 'Domain',
            'is_pay' => 'Is Pay',
            'credit' => 'Credit',
            'date' => 'Date',
        ];
    }
}
