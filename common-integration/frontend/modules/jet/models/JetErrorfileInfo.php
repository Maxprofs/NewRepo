<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_errorfile_info".
 *
 * @property integer $id
 * @property string $jet_file_id
 * @property string $file_name
 * @property string $file_type
 * @property string $status
 * @property string $error
 * @property string $date
 * @property integer $jetinfofile_id
 */
class JetErrorfileInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jet_errorfile_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'jet_file_id', 'file_name', 'file_type', 'status', 'date', 'jetinfofile_id'], 'required'],
            [['id', 'jetinfofile_id'], 'integer'],
            [['error'], 'string'],
            [['product_skus'], 'string'],
            [['date'], 'safe'],
            [['jet_file_id', 'file_name', 'file_type'], 'string', 'max' => 70],
            [['status'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jet_file_id' => 'Jet File ID',
            'product_skus'=>'Product Sku(s)',
            'file_name' => 'File Name',
            'file_type' => 'File Type',
            'status' => 'Status',
            'error' => 'Error',
            'date' => 'Date',
            'jetinfofile_id' => 'Jetinfofile ID',
        ];
    }
}
