<?php

namespace backend\modules\walmart\models;

use Yii;

/**
 * This is the model class for table "email_template".
 *
 * @property integer $id
 * @property string $template_title
 * @property string $template_path
 * @property string $custom_title
 * @property integer $show_on_admin_setting
 */
class WalmartEmailTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'email_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_title', 'template_path'], 'required'],
            [['show_on_admin_setting'], 'integer'],
            [['template_title', 'template_path', 'custom_title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_title' => 'Handler',
            'template_path' => 'Template Path',
            'custom_title' => 'Custom Title',
            'show_on_admin_setting' => 'Show On Admin Setting',
        ];
    }
}
