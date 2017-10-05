<?php
namespace frontend\modules\referral\models;

use yii\base\Model;

/**
 * Signup form
 */
class ReferrerSignupForm extends Model
{
    public $name;
    public $username;
    public $email;
    public $password;
    public $code;
    public $merchant_id;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'username', 'password', 'email'], 'required'],
            
            ['name', 'string', 'min' => 2, 'max' => 200],

            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            //['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new ReferrerUser();
        $user->name = $this->name;
        $user->username = $this->username;
        $user->password = SubUser::getEncodedPassword($this->password);
        $user->status = self::getReferrerStatus();
        
        return $user->save() ? $user : null;
    }

    public static function getReferrerStatus()
    {
        return ReferrerUser::REFERRER_STATUS_PENDING;
    }
}
