<?php

namespace frontend\modules\walmart\models;

use Yii;

/**
 * This is the model class for table "jet_product".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $title
 * @property string $sku
 * @property string $type
 * @property string $product_type
 * @property string $description
 * @property integer $variant_id
 * @property string $image
 * @property integer $qty
 * @property double $weight
 * @property double $price
 * @property string $attr_ids
 * @property string $jet_attributes
 * @property string $vendor
 * @property string $upc
 * @property string $barcode_type
 * @property string $ASIN
 * @property integer $fulfillment_node
 * @property string $status
 * @property string $error
 *
 * @property JetMerchantProducts[] $jetMerchantProducts
 * @property User $merchant
 */
class JetProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'type', 'variant_id', 'qty','sku','price'], 'required'],
            [['id', 'merchant_id', 'variant_id', 'qty', 'fulfillment_node'], 'integer'],
            [['title','description', 'image', 'attr_ids', 'jet_attributes', 'error'], 'string'],
            [['weight', 'price'], 'number'],
            [['sku', 'type', 'product_type', 'vendor', 'barcode_type', 'ASIN','status','mpn'], 'string', 'max' => 255],
            [['upc'], 'string', 'max' => 500]
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
            'title' => 'Title',
            'sku' => 'Sku',
            'type' => 'Type',
            'product_type' => 'Product Type',
            'description' => 'Description',
            'variant_id' => 'Variant ID',
            'image' => 'Image',
            'qty' => 'Qty',
            'weight' => 'Weight(lb)',
            'price' => 'Price',
            'attr_ids' => 'Attr Ids',
            'jet_attributes' => 'Jet Attributes',
            'vendor' => 'Brand',
            'upc' => 'Barcode',
            'mpn' => 'MPN',
            'barcode_type' => 'Barcode Type',
            'ASIN' => 'ASIN',
            'fulfillment_node' => 'Jet Browse Node',
            'status' => 'Status',
            'error' => 'Upload Error',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJetMerchantProducts()
    {
        return $this->hasMany(JetMerchantProducts::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }
}
