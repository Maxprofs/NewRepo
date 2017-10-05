<?php

namespace frontend\modules\walmart\models;

use Yii;

/**
 * This is the model class for table "walmart_registration".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $name
 * @property string $plan_type
 * @property string $shipping_source
 * @property integer $product_count
 * @property string $skype
 * @property integer $mobile
 * @property string $email
 * @property string $reference
 * @property string $agreement
 * @property string $other_reference
 */
class WalmartRegistration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'walmart_registration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email', 'agreement'], 'required'],
            [['merchant_id', 'mobile'], 'integer'],
            [['reference'], 'string'],
            [['name', 'shipping_source', 'email', 'agreement', 'other_reference', 'other_shipping_source'], 'string', 'max' => 255],
            [['merchant_id'], 'unique'],
            [['email'],'email','message'=>'Please enter a valid {attribute}.'],
            ['agreement', 'required', 'requiredValue' => 1, 'message' => 'You must agree to the terms and conditions.'],
            ['other_reference', 'required', 'message' => 'This field cannot be blank.', 'when' => function ($model) {
                return $model->reference == 'Other';
            }, 'whenClient' => "function (attribute, value) {
                    return $('#jetregistration-reference').val() == 'Other';
            }"],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'merchant_id' => 'Merchant ID',
            'name' => 'Full Name',
            'plan_type' => 'Plan Type',
            'shipping_source' => 'Shipping Source',
            'product_count' => 'Product Count',
            'skype' => 'Skype',
            'mobile' => 'Mobile',
            'email' => 'Email',
            'reference' => 'Reference',
            'agreement' => 'I Accept Terms & Conditions',
        ];
    }
}
