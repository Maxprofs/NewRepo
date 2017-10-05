<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_category_map".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $product_type
 * @property integer $category_id
 * @property string $category_name
 */
class JetCategoryMap extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_category_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'product_type', 'category_id', 'category_name'], 'required'],
            [['merchant_id', 'category_id'], 'integer'],
            [['product_type', 'category_name'], 'string', 'max' => 255]
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
            'product_type' => 'Product Type',
            'category_id' => 'Category ID',
            'category_name' => 'Category Name',
        ];
    }
}
