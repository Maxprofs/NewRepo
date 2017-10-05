<?php

namespace backend\modules\reports\models;
use backend\modules\reports\components\Data;

use Yii;

/**
 * This is the model class for table "jet_email_template".
 *
 * @property integer $id
 * @property string $template_title
 * @property string $template_path
 * @property string $custom_title
 * @property integer $show_on_admin_setting
 */
class JetEmailTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Data::JET_EMAIL_TEMPLATE;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_title', 'template_path', 'custom_title'], 'required'],
            [['show_on_admin_setting'], 'integer'],
            [['template_title', 'template_path', 'custom_title'], 'string', 'max' => 255],
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
