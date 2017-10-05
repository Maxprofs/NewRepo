<?php

namespace frontend\modules\walmart\models;

use Yii;

/**
 * This is the model class for table "walmart_feed_stats".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $feed_type
 * @property string $feed_send_time
 * @property string $last_send_index
 */
class WalmartFeedStats extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'walmart_feed_stats';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'feed_type', 'last_send_index'], 'required'],
            [['merchant_id'], 'integer'],
            [['feed_send_time'], 'safe'],
            [['feed_type'], 'string', 'max' => 50],
            [['last_send_index'], 'string', 'max' => 200]
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
            'feed_type' => 'Feed Type',
            'feed_send_time' => 'Feed Send Time',
            'last_send_index' => 'Last Send Index',
        ];
    }
}