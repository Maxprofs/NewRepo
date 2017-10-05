<?php

namespace frontend\modules\neweggcanada\models;

use Yii;

/**
 * This is the model class for table "newegg_can_product_variants".
 *
 * @property integer $id
 * @property integer $option_id
 * @property integer $product_id
 * @property integer $merchant_id
 * @property string $upload_status
 * @property string $newegg_option_attributes
 * @property string $new_variant_option_1
 * @property string $new_variant_option_2
 * @property string $new_variant_option_3
 * @property double $option_prices
 */
class NeweggProductVariants extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_can_product_variants';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['option_id', 'product_id', 'merchant_id'], 'required'],
            [['option_id', 'product_id', 'merchant_id'], 'integer'],
            [['newegg_option_attributes'], 'string'],
            [['option_prices'], 'number'],
            [['upload_status'], 'string', 'max' => 200],
            [['new_variant_option_1', 'new_variant_option_2', 'new_variant_option_3'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'option_id' => 'Option ID',
            'product_id' => 'Product ID',
            'merchant_id' => 'Merchant ID',
            'upload_status' => 'Upload Status',
            'newegg_option_attributes' => 'Newegg Option Attributes',
            'new_variant_option_1' => 'New Variant Option 1',
            'new_variant_option_2' => 'New Variant Option 2',
            'new_variant_option_3' => 'New Variant Option 3',
            'option_prices' => 'Option Prices',
        ];
    }
}
