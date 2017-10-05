<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_attribute_map".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $shopify_product_type
 * @property integer $jet_attribute_id
 * @property string $attribute_value_type
 * @property string $attribute_value
 */
class JetAttributeMap extends \yii\db\ActiveRecord
{
    const VALUE_TYPE_SHOPIFY = 'map_with_shopify_option';
    const VALUE_TYPE_JET = 'predefined_jet_attribute_value';
    const VALUE_TYPE_TEXT = 'text';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_attribute_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'shopify_product_type', 'jet_attribute_id', 'attribute_value_type', 'attribute_value'], 'required'],
            [['merchant_id', 'jet_attribute_id'], 'integer'],
            [['shopify_product_type', 'attribute_value'], 'string', 'max' => 255],
            [['attribute_value_type'], 'string', 'max' => 100]
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
            'shopify_product_type' => 'Shopify Product Type',
            'jet_attribute_id' => 'Jet Attribute ID',
            'attribute_value_type' => 'Attribute Value Type',
            'attribute_value' => 'Attribute Value',
        ];
    }
}
