<?php

namespace frontend\modules\jet\models;
use backend\models\JetShopDetails;
use frontend\modules\jet\models\JetConfiguration;
use Yii;

/**
 * This is the model class for table "jet_registration".
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
 * @property string $already_selling
 * @property string $previous_api_provider_name
 * @property string $is_uninstalled_previous

 */
class JetRegistration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_registration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email','mobile', 'agreement'], 'required'],
            [['merchant_id', 'mobile'], 'integer'],
            [['reference'], 'string'],
            [['name', 'shipping_source', 'email', 'agreement', 'other_reference', 'other_shipping_source','already_selling','previous_api_provider_name','is_uninstalled_previous'], 'string', 'max' => 255],
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
     * @return \yii\db\ActiveQuery
     */
    public function getJet_shop_details()
    {
        return $this->hasOne(JetShopDetails::className(), ['merchant_id' => 'merchant_id']);
    }
    public function getJet_configuration()
    {
        return $this->hasOne(JetConfiguration::className(), ['merchant_id' => 'merchant_id']);
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
            'already_selling'=>'Already on jet',
            'previous_api_provider_name'=>'Provider name',
            'is_uninstalled_previous' => 'Uninstalled previous App',
            'agreement' => 'I Accept Terms & Conditions',
        ];
    }
}
