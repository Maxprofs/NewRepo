<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_attribute_value".
 *
 * @property integer $attribute_id
 * @property string $value
 * @property string $units
 * @property string $retired
 */
class JetAttributeValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_attribute_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attribute_id', 'value', 'retired'], 'required'],
            [['attribute_id'], 'integer'],
            [['value', 'units', 'retired'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'attribute_id' => 'Attribute ID',
            'value' => 'Value',
            'units' => 'Units',
            'retired' => 'Retired',
        ];
    }
}
