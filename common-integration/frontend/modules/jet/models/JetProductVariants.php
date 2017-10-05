<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_product_variants".
 *
 * @property integer $option_id
 * @property integer $product_id
 * @property integer $merchant_id
 * @property string $option_title
 * @property string $option_sku
 * @property string $jet_option_attributes
 * @property string $option_image
 * @property integer $option_qty
 * @property double $option_weight
 * @property double $option_price
 * @property string $option_unique_id
 * @property string $barcode_type
 * @property string $asin
 * @property string $variant_option1
 * @property string $variant_option2
 * @property string $variant_option3
 * @property string $new_variant_option_1
 * @property string $new_variant_option_2
 * @property string $new_variant_option_3
 * @property string $vendor
 *
 * @property User $merchant
 */
class JetProductVariants extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_product_variants';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['option_id', 'product_id', 'merchant_id'], 'required'],
            [['option_id', 'product_id', 'merchant_id', 'option_qty'], 'integer'],
            [['option_weight', 'option_price'], 'number'],
            [['asin', 'new_variant_option_1', 'new_variant_option_2', 'new_variant_option_3'], 'string'],
            [['option_title', 'option_sku', 'jet_option_attributes', 'option_image', 'option_unique_id', 'barcode_type', 'variant_option1', 'variant_option2', 'variant_option3', 'vendor','option_mpn'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'option_id' => 'Option ID',
            'product_id' => 'Product ID',
            'merchant_id' => 'Merchant ID',
            'option_title' => 'Option Title',
            'option_sku' => 'Option Sku',
            'jet_option_attributes' => 'Jet Option Attributes',
            'option_image' => 'Option Image',
            'option_qty' => 'Option Qty',
            'option_weight' => 'Option Weight',
            'option_price' => 'Option Price',
        	'option_mpn'=>'Option MPN',
            'option_unique_id' => 'Barcode(GTIN-14,UPC, etc)',
            'barcode_type' => 'Barcode Type',
            'asin' => 'Asin',
            'variant_option1' => 'Variant Option1',
            'variant_option2' => 'Variant Option2',
            'variant_option3' => 'Variant Option3',
            'new_variant_option_1' => 'New Variant Option 1',
            'new_variant_option_2' => 'New Variant Option 2',
            'new_variant_option_3' => 'New Variant Option 3',
            'vendor' => 'Vendor',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }

    public function getJetProductVariantsDetails()
    {
        return $this->hasMany(JetProductVariantsDetails::className(), ['option_id' => 'option_id']);
    }
}
