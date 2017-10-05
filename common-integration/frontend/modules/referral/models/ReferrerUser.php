<?php

namespace frontend\modules\referral\models;

use Yii;

/**
 * This is the model class for table "referrer_user".
 *
 * @property integer $id
 * @property string $name
 * @property string $username
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 * @property integer $status
 */
class ReferrerUser extends \yii\db\ActiveRecord
{
    const REFERRER_STATUS_PENDING = 0;
    const REFERRER_STATUS_APPROVED = 1;
    const REFERRER_STATUS_UNAPPROVED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'referrer_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'username', 'password'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'username'], 'string', 'max' => 200],
            [['password'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'username' => 'UserName',
            'password' => 'Password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status'
        ];
    }
}
