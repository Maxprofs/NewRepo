<?php
namespace frontend\modules\walmart\components;

use Yii;
use yii\base\Component;

class ProductFeed extends Component
{
    const ERROR_CODE_PRODUCT_ID_OVERRIDE = 1;
    const ERROR_CODE_PRODUCT_SKU_OVERRIDE = 0;

    public function isListedError($ingestionError)
    {
        $ERROR_PRODUCT_ID_OVERRIDE = "This SKU is already set up with a different Product ID (i.e., GTIN, UPC, ISBN, etc.). If you are trying to change this SKU's Product ID, submit the product_id_override flag in the request. To learn more, search 'How to Update an Item's SKU' in the Knowledge Base.";
        $ERROR_SKU_OVERRIDE = "SKU Override";

        $errorList = [self::ERROR_CODE_PRODUCT_ID_OVERRIDE => $ERROR_PRODUCT_ID_OVERRIDE, self::ERROR_CODE_PRODUCT_SKU_OVERRIDE => $ERROR_SKU_OVERRIDE];
        $errorCode = false;
        foreach ($ingestionError as $error) {
            if (in_array($error['description'], $errorList)) {
                $errorCode = array_search($error['description'], $errorList);

            } elseif (strpos($error['description'], $ERROR_SKU_OVERRIDE) > 0) {
                $errorCode = array_search($ERROR_SKU_OVERRIDE, $errorList);
            }
            return $errorCode;
        }
        return false;
    }

    public function getProductIdFromSku($sku)
    {
        $merchant_id = Yii::$app->user->identity->id;

        $query = "SELECT `id` FROM `jet_product` WHERE `merchant_id` = {$merchant_id} AND `sku` LIKE '{$sku}' LIMIT 0,1";
        $result = Data::sqlRecords($query, 'one');

        if ($result) {
            return $result['id'];
        } else {
            $query = "SELECT `product_id` FROM `jet_product_variants` WHERE `merchant_id` = {$merchant_id} AND `option_sku` LIKE '{$sku}' LIMIT 0,1";
            $result = Data::sqlRecords($query, 'one');

            if ($result) {
                return $result['product_id'];
            }
        }

        return false;
    }

    public function updateProductColumn($column_name, $value, $productIds)
    {
        if (is_array($productIds) && count($productIds)) {
            $productId = implode(',', $productIds);

            $query = "UPDATE `walmart_product` SET `{$column_name}` = '{$value}' WHERE `product_id` IN ({$productId})";
            Data::sqlRecords($query, null, 'update');
        }
    }
}
