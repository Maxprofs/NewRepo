<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;

/**
 * This is the model class for table "newegg_category".
 *
 * @property integer $id
 * @property string $category_id
 * @property string $title
 * @property string $parent_id
 * @property integer $level
 * @property string $attributes
 * @property string $attribute_values
 * @property string $walmart_attributes
 * @property string $walmart_attribute_values
 * @property string $attributes_order
 */
class NeweggCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'newegg_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'title', 'parent_id', 'level', 'attributes', 'walmart_attributes'], 'required'],
            [['level'], 'integer'],
            [['attributes', 'attribute_values', 'walmart_attributes', 'walmart_attribute_values', 'attributes_order'], 'string'],
            [['category_id', 'title', 'parent_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'title' => 'Title',
            'parent_id' => 'Parent ID',
            'level' => 'Level',
            'attributes' => 'Attributes',
            'attribute_values' => 'Attribute Values',
            'walmart_attributes' => 'Walmart Attributes',
            'walmart_attribute_values' => 'Walmart Attribute Values',
            'attributes_order' => 'Attributes Order',
        ];
    }
}
