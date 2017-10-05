<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "jet_merchats_help".
 *
 * @property integer $id
 * @property string $merchant_store_name
 * @property string $merchant_email_id
 * @property string $subject
 * @property string $query
 * @property string $status
 * @property string $time
 */
class JetMerchatsHelp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_merchats_help';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['query', 'status'], 'string'],
            [['time'], 'safe'],
            [['merchant_store_name', 'merchant_email_id', 'subject'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_store_name' => 'Merchant Store Name',
            'merchant_email_id' => 'Merchant Email ID',
            'subject' => 'Subject',
            'query' => 'Query',
            'status' => 'Status',
            'time' => 'Time',
        ];
    }
}
