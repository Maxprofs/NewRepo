<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;
use common\models\User;
use common\models\JetProduct;

/**
 * This is the model class for table "newegg_product".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $merchant_id
 * @property string $shopify_product_type
 * @property string $newegg_category
 * @property string $error
 * @property string $upload_status
 *
 * @property User $merchant
 */
class NeweggProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
/*    const PRODUCT_STATUS_UPLOADED = 'PUBLISHED';
    const PRODUCT_STATUS_UNPUBLISHED = 'UNPUBLISHED';
    const PRODUCT_STATUS_STAGE = 'STAGE';
    const PRODUCT_STATUS_NOT_UPLOADED = 'Not Uploaded';
    const PRODUCT_STATUS_PROCESSING = 'Item Processing';*/
    public $option_status,$option_variants_count;
    public $price_from;
    public $price_to;

    public static function tableName()
    {
        return 'newegg_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'merchant_id', 'shopify_product_type', 'newegg_category', 'error', 'upload_status'], 'required'],
            [['product_id', 'merchant_id'], 'integer'],
            [['error'], 'string'],
            [['shopify_product_type', 'newegg_category'], 'string', 'max' => 200],
            [['upload_status'], 'string', 'max' => 100],
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
            'product_id' => 'Product ID',
            'merchant_id' => 'Merchant ID',
            'shopify_product_type' => 'Shopify Product Type',
            'newegg_category' => 'Newegg Category',
            'error' => 'Error',
            'upload_status' => 'Upload Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(User::className(), ['id' => 'merchant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJet_product()
    {
        return $this->hasOne(JetProduct::className(), ['id' => 'product_id']);
    }
}
