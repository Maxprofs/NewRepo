<?php

namespace backend\modules\reports\models;
use backend\modules\reports\components\Data;

use Yii;

/**
 * This is the model class for table "issues".
 *
 * @property integer $id
 * @property string $issue_type
 * @property string $issue_description
 * @property string $issue_date
 * @property string $resolve_date
 * @property string $assign
 */
class Issues extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Data::ISSUES;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['issue_type', 'issue_description', 'issue_date', 'assign'], 'required'],
            [['issue_description'], 'string'],
            [['issue_date', 'resolve_date'], 'safe'],
            [['issue_type', 'assign','issue_status','employee_email'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'issue_type' => 'Issue Type',
            'issue_description' => 'Issue Description',
            'issue_date' => 'Issue Date',
            'resolve_date' => 'Resolve Date',
            'assign' => 'Assign',
            'issue_status' => 'Issue Status',
            'employee_email'=>'Employee Email',
        ];
    }
}
