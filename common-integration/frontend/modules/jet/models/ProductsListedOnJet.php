<?php

namespace frontend\modules\jet\models;
use common\models\User;

use Yii;

/**
 * This is the model class for table "products_listed_on_jet".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property string $sku
 * @property string $title
 * @property string $status
 * @property string $has_inv
 *
 * @property User $merchant
 */
class ProductsListedOnJet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products_listed_on_jet';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'sku', 'title'], 'required'],
            [['merchant_id'], 'integer'],
            [['title', 'has_inv'], 'string'],
            [['sku'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 100],
            [['merchant_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['merchant_id' => 'id']],
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
            'sku' => 'Sku',
            'title' => 'Title',
            'status' => 'Status',
            'has_inv' => 'Has Inv',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }
}
