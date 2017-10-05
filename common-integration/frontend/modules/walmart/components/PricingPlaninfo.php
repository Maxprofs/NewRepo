<?php
namespace frontend\modules\walmart\components;

use Yii;
use yii\base\Component;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Jetproductinfo;

class PricingPlaninfo extends Component
{
    public static function getProductcount($merchant_id, $page = false)
    {
        $limit = 250;
        if (!$page)
            $page = 0;

        $sameSku = "";
        $notSku = "";
        $notProductType = "";
        $sameSkuArray = [];
        $result = [];
        $notSkuArray = [];
        $notProductTypeArray = [];
        $shop = Yii::$app->user->identity->username;
        $merchant_id = Yii::$app->user->identity->id;
        $shopDetails = Data::getWalmartShopDetails($merchant_id);
        $token = isset($shopDetails['token']) ? $shopDetails['token'] : '';
        $sc = new ShopifyClientHelper($shop, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
        $countProducts = $sc->call('GET', '/admin/products/count.json', ['published_status' => "published"]);
        $pages = $nonSkuCount = $totalSkuCount = $nonProductType = $currentTotalProd = 0;
        if (isset($countProducts['errors'])) {
            $result['err'] = $countProducts['errors'];
            return json_encode($result);
        }
        //Data::jetsaveConfigValue($merchant_id, 'import_status', 'published');
        $productData = [];
        $products = "";
        $products = $sc->call('GET', '/admin/products.json', ['fields' => "id, title, variants, product_type", 'published_status' => "published", 'limit' => $limit, 'page' => $page]);
        if (isset($products['errors'])) {
            $result['err'] = $products['errors'];
            return json_encode($result);
        }
        $currentTotalProd = count($products);
        foreach ($products as $prod) {
            if (trim($prod['product_type']) == '') {
                $notProductType .= ',' . $prod['id'];
                $nonProductType++;
                continue;
            }
            $fg = true;
            $varientArray = $prod['variants'];
            //usort($varientArray, array($this, "interchangeArray"));
            $prod['variants'] = $varientArray;
            foreach ($prod['variants'] as $variant) {
                if ($variant['position'] == '1' && trim($variant['sku']) == "") {
                    $nonSkuCount++;
                    $notSku .= ',' . $prod['id'];
                    $fg = false;
                    break;
                }
                $totalSkuCount++;
                if (empty($sameSkuArray)) {
                    $skuKey = Data::getKey($variant['sku']);
                    $sameSkuArray[$skuKey] = '1';
                } else {
                    if (isset($sameSkuArray[$variant['sku']])) {
                        $sameSku .= ',' . $prod['id'];
                        $fg = false;
                        break;
                    } else {
                        $skuKey = Data::getKey($variant['sku']);
                        $sameSkuArray[$skuKey] = '1';
                    }
                }
            }
            if (!$fg) {
                continue;
            }
            $productData[$prod['id']] = $prod;
        }

        /* $result ['total'] = $countProducts;
         $result ['non_sku'] = $nonSkuCount;
         $result ['total_sku_count'] = $totalSkuCount;
         $result ['ready'] = $currentTotalProd - ($nonSkuCount + $nonProductType);
         $result ['non_type'] = $nonProductType;*/
        //$result ['csrf'] = Yii::$app->request->getCsrfToken();
        //$result ['products'] = $productData;

        return $totalSkuCount;
    }

    public static function getPaymentplan()
    {

        $payment_plan = Data::sqlRecords('SELECT * FROM `pricing_plan` WHERE `plan_status`="Enable" AND `apply_on`IN("All","Walmart") ', "all", "select");

        return $payment_plan;
    }

    public static function getProductcharge($ad_condition, $countProducts)
    {

        foreach ($ad_condition as $val) {

            $product_charge_id = Data::sqlRecords('SELECT `id`,`charge_range`,`charge_name` FROM `conditional_charge` WHERE `id`="' . $val . '" AND `charge_condition`= "Product"', "one", "select");

            if ($product_charge_id['charge_range'] == "Range") {

                $product_charge = Data::sqlRecords('SELECT  `amount_type`, `amount`,`to_range`,`fixed_range` FROM `conditional_range` WHERE `charge_id`="' . $product_charge_id['id'] . '" AND  `from_range` <= "' . $countProducts . '" AND  `to_range` >= "' . $countProducts . '"', "one", "select");
                $product_charge['product_charge_name'] = $product_charge_id['charge_name'];

            } elseif ($product_charge_id['charge_range'] == "Fixed") {
                $product_charge = Data::sqlRecords('SELECT  `amount_type`, `amount`,`fixed_range`,`to_range` FROM `conditional_range` WHERE `charge_id`="' . $product_charge_id['id'] . '" AND  `fixed_range` >= "' . $countProducts . '"', "one", "select");
                $product_charge['product_charge_name'] = $product_charge_id['charge_name'];
            }
        }
        return $product_charge;
    }

    public static function getOrdercharge($ad_condition)
    {

        foreach ($ad_condition as $val) {
            $order_charge_id = Data::sqlRecords('SELECT `id`,`charge_range`,`charge_name` FROM `conditional_charge` WHERE `id`="' . $val . '" AND `charge_condition`= "Order"', "one", "select");
            if ($order_charge_id['charge_range'] == "Range") {
                $order_charge = Data::sqlRecords('SELECT  `amount_type`, `amount`,`to_range`,`fixed_range` FROM `conditional_range` WHERE `charge_id`="' . $order_charge_id['id'] . '"', "one", "select");

            } else {
                $order_charge = Data::sqlRecords('SELECT  `amount_type`, `amount`,`fixed_range`,`to_range` FROM `conditional_range` WHERE `charge_id`="' . $order_charge_id['id'] . '"', "one", "select");
            }
        }
        return $order_charge;
    }

    public static function getBasicproductcharge($ad_condition)
    {

        foreach ($ad_condition as $val) {

            $product_charge_id = Data::sqlRecords('SELECT `id`,`charge_range`,`charge_name` FROM `conditional_charge` WHERE `id`="' . $val . '" AND `charge_condition`= "Product"', "one", "select");

            if ($product_charge_id['charge_range'] == "Range") {

                $product_charge = Data::sqlRecords('SELECT  `to_range` FROM `conditional_range` WHERE `charge_id`="' . $product_charge_id['id'] . '" AND  `amount` =0', "one", "select");


            } elseif ($product_charge_id['charge_range'] == "Fixed") {
                $product_charge = Data::sqlRecords('SELECT `fixed_range` FROM `conditional_range` WHERE `charge_id`="' . $product_charge_id['id'] . '" AND  `amount` =0', "one", "select");
            }
        }
        //print_r($product_charge);die;
        if (isset($product_charge['to_range'])) {
            $product_charges = $product_charge['to_range'];
        } else {
            $product_charges = $product_charge['fixed_range'];
        }
        return $product_charges;
    }

    public static function getBasicordercharge($ad_condition)
    {
        foreach ($ad_condition as $val) {
            $order_charge_id = Data::sqlRecords('SELECT `id`,`charge_range`,`charge_name` FROM `conditional_charge` WHERE `id`="' . $val . '" AND `charge_condition`= "Order"', "one", "select");
            if ($order_charge_id['charge_range'] == "Range") {
                $order_charge = Data::sqlRecords('SELECT  `to_range` FROM `conditional_range` WHERE `charge_id`="' . $order_charge_id['id'] . '" AND  `amount` =0', "one", "select");

            } else {
                $order_charge = Data::sqlRecords('SELECT  `fixed_range` FROM `conditional_range` WHERE `charge_id`="' . $order_charge_id['id'] . '" AND  `amount` = 0', "one", "select");
            }
        }
        return $order_charge;
    }

    public static function getProductlimit($merchant_id)
    {
        $charge_limit = Data::sqlRecords("SELECT  `status`, `choose_plan_data`, `charge_limit` FROM `walmart_recurring_payment` WHERE `merchant_id`='" . $merchant_id . "' LIMIT 0,1", "one", "select");
        if ($charge_limit['status'] == "active") {
            $charge_limit = json_decode($charge_limit['charge_limit'], true);
            if ($charge_limit['setup_charge'] == "Yes") {
                $product_limit = $charge_limit['product_limit']['limit'];
            } else {
                $product_limit = $charge_limit['product_limit']['basic'];
            }
            return $product_limit;

        } else {
            return false;
        }
    }

    public static function getOrderlimit($merchant_id)
    {
        $charge_limit = Data::sqlRecords("SELECT  `status`, `choose_plan_data`, `charge_limit` FROM `walmart_recurring_payment` WHERE `merchant_id`='" . $merchant_id . "' LIMIT 0,1", "one", "select");
        if ($charge_limit['status'] == "active") {
            $charge_limit = json_decode($charge_limit['charge_limit'], true);
            if ($charge_limit['setup_charge'] == "Yes") {
                $order_limit = $charge_limit['order_limit']['limit'];
            } else {
                $order_limit = $charge_limit['order_limit']['basic'];
            }
            return $order_limit;
        } else {
            return false;
        }
    }
}