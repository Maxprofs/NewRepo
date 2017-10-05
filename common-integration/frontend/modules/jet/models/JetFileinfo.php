<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_fileinfo".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $batch_info
 * @property string $jet_file_id
 * @property string $token_url
 * @property string $file_name
 * @property integer $file_type
 * @property string $status
 */
class JetFileinfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_fileinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'batch_info', 'jet_file_id', 'token_url', 'file_name', 'file_type', 'status'], 'required'],
            [['merchant_id', 'file_type'], 'integer'],
            [['batch_info', 'token_url'], 'string', 'max' => 1000],
            [['jet_file_id'], 'string', 'max' => 400],
            [['file_name', 'status'], 'string', 'max' => 255]
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
            'batch_info' => 'Batch Info',
            'jet_file_id' => 'Jet File ID',
            'token_url' => 'Token Url',
            'file_name' => 'File Name',
            'file_type' => 'File Type',
            'status' => 'Status',
        ];
    }
}
