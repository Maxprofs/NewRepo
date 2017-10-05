<?php
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Mail;
use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\components\Jetappdetails;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\WalmartCategory;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\ShopifyClientHelper;

class WalmartscriptController extends WalmartmainController
{
    const MAX_SHORT_DESCRIPTION = 1000;
    const MAX_SHELF_DESCRIPTION = 1000;
    const MAX_LONG_DESCRIPTION = 4000;

    public function actionDeleteproduct()
    {
        $product_ids = Yii::$app->request->post('product_id', false);
        $retire = Yii::$app->request->post('retire');
        if (!is_array($product_ids))
            $product_ids = explode(',', $product_ids);

        if ($product_ids && count($product_ids)) {
            $merchant_id = MERCHANT_ID;

            try {
                $walmartApi = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

                $errors = [];
                foreach ($product_ids as $product_id) {
                    $productData = WalmartRepricing::getProductData($product_id);

                    if ($productData && isset($productData['type'])) {
                        if ($productData['type'] == 'simple') {
                            $deleteProductFlag = false;
                            if ($retire && $productData['status'] != WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED) {
                                $sku = $productData['sku'];
                                $feed_data = [];
                                $feed_data = $walmartApi->retireProduct($sku);

                                if (isset($feed_data['ItemRetireResponse'])) {
                                    $deleteProductFlag = true;
                                } elseif (isset($feed_data['errors']['error'])) {
                                    if (isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku") {
                                        $errors[$sku][] = $sku . ' : Product not Uploaded on Walmart.';
                                    } else {
                                        $errors[$sku][] = $sku . ' : ' . $feed_data['errors']['error']['description'];
                                    }
                                }
                            } else {
                                $deleteProductFlag = true;
                            }

                            if ($deleteProductFlag) {
                                self::walmartDeleteProductStatus($product_id, $productData['type'], $merchant_id);
                            }
                        } elseif ($productData['type'] == 'variants') {
                            $productVariants = WalmartRepricing::getProductVariants($product_id);
                            if ($productVariants) {
                                if ($retire) {
                                    $variantErr = [];
                                    $deleteProductFlag2 = true;
                                    foreach ($productVariants as $variant) {
                                        $sku = $variant['option_sku'];

                                        if ($variant['status'] != WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED) {
                                            $feed_data = [];
                                            $feed_data = $walmartApi->retireProduct($sku);

                                            if (isset($feed_data['ItemRetireResponse'])) {
                                                continue;
                                            } elseif (isset($feed_data['errors']['error'])) {
                                                if (isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku") {
                                                    //product not uploaded, so it can not be retired.
                                                    continue;
                                                } else {
                                                    $variantErr[] = $sku . ' : ' . $feed_data['errors']['error']['description'];
                                                    $deleteProductFlag2 = false;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    if (count($variantErr)) {
                                        $errors[$productData['sku']] = implode(',', pieces);
                                    } elseif ($deleteProductFlag2) {
                                        self::walmartDeleteProductStatus($product_id, $productData['type'], $merchant_id);
                                    }
                                } else {
                                    self::walmartDeleteProductStatus($product_id, $productData['type'], $merchant_id);
                                }
                            } else {
                                $errors[$productData['sku']] = "no variants found for this product.";
                            }
                        }
                    }
                }
                if (count($errors))
                    return json_encode(['error' => true, 'message' => implode(',', $errors)]);
                else
                    return json_encode(['success' => true, 'message' => "Product(s) Deleted Successfully!!"]);
            } catch (Exception $e) {
                return json_encode(['error' => true, 'message' => "Error : " . $e->getMessage()]);
            }
        } else {
            return json_encode(['error' => true, 'message' => "No product selected for delete."]);
        }
    }

    public function actionShopifyproductsync()
    {

        //comma seperated product ids
        //$product_ids = '9678336780,9677379724,9622625740';
        $product_ids = Yii::$app->request->post('product_id', false);

        parse_str(Yii::$app->request->post('sync_fields'), $sync);

        if ($product_ids && strlen($product_ids) && count($sync['sync-fields'])) {
            $merchant_id = MERCHANT_ID;
            $shopname = SHOP;
            $token = TOKEN;

            try {
                $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);

                $products = $sc->call('GET', '/admin/products.json?ids=' . $product_ids, array());
                //$products = $sc->call('GET', '/admin/products.json', array());
                //print_r($products);die;
                if ($products && is_array($products)) {
                    foreach ($products as $key => $product) {
                        $response = Jetproductinfo::updateDetails($product, $sync, $merchant_id);
                    }
                    if ($response) {
                        return json_encode(['success' => true, 'message' => 'Product Synced Successfully!!']);
                    } else {
                        return json_encode(['success' => true, 'message' => 'No Change in Product!!']);
                    }

                } else {
                    return json_encode(['error' => true, 'message' => "Product doesn't exist on Shopify."]);
                }
            } catch (Exception $e) {
                return json_encode(['error' => true, 'message' => "Error : " . $e->getMessage()]);
            }
        } else {
            return json_encode(['error' => true, 'message' => "No product selected for sync."]);
        }
    }

    public static function getImage($images, $image_id)
    {
        if (count($images)) {
            foreach ($images as $image) {
                if ($image['id'] == $image_id) {
                    return $image;
                }
            }
        }
        return ['src' => ''];
    }

    public static function getImplodedImages($images)
    {
        $img_arr = [];
        if (count($images)) {
            foreach ($images as $image) {
                $img_arr[] = $image['src'];
            }
        }
        return implode(',', $img_arr);
    }

    public static function deleteProduct($product, $all = false)
    {
        if (is_array($product) && count($product)) {
            $product_id = $product['id'];

            if ($all) {
                $deleteQuery = "DELETE FROM `jet_product_variants` WHERE `product_id`='{$product_id}'";
                return Data::sqlRecords($deleteQuery, null, 'delete');
            } elseif (!$all) {
                $variants = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE `product_id`='{$product_id}'", 'all', 'select');

                if ($variants) {
                    $current_variants = [];
                    foreach ($variants as $variant) {
                        $current_variants[] = $variant['option_id'];
                    }

                    $new_variants = [];
                    foreach ($product['variants'] as $value) {
                        $new_variants[] = $value['id'];
                    }

                    $productsToDelete = array_diff($current_variants, $new_variants);

                    if (count($productsToDelete)) {
                        $deleteQuery = "DELETE FROM `jet_product_variants` WHERE `option_id` IN (" . implode(',', $productsToDelete) . ")";
                        return Data::sqlRecords($deleteQuery, null, 'delete');
                    }
                }
            }
        }
        return false;
    }

    /**
     *    Import products,product_types from jet_product,jet_product_variants tables
     */
    public function actionIndex()
    {
        $merchant_id = '';

        $query = "SELECT `product_id` FROM `walmart_product`";

        if ($merchant_id != '')
            $query .= " WHERE merchant_id=" . $merchant_id;

        $walmart_data = Data::sqlRecords($query, "all", "select");

        $walmart_skus = '';
        if ($walmart_data && is_array($walmart_data) && count($walmart_data)) {
            foreach ($walmart_data as $key => $_walmart) {
                $walmart_skus .= $_walmart['product_id'];
                if (isset($walmart_data[$key + 1]))
                    $walmart_skus .= ',';
            }
        }

        $query = "SELECT * FROM `jet_product`";
        if ($walmart_skus != '') {
            $query .= " WHERE `id` NOT IN (" . $walmart_skus . ")";

            if ($merchant_id != '')
                $query .= " AND merchant_id=" . $merchant_id;
        } else {
            if ($merchant_id != '')
                $query .= " WHERE merchant_id=" . $merchant_id;
        }

        $jet_data = Data::sqlRecords($query, "all", "select");
        if ($jet_data && is_array($jet_data) && count($jet_data)) {
            $insert_data = [];
            foreach ($jet_data as $jet_product) {
                $value_str = "(";
                $value_str .= $jet_product['id'] . ",";//product_id
                $value_str .= $jet_product['merchant_id'] . ",";//merchant_id
                $value_str .= "'" . addslashes($jet_product['product_type']) . "',";//product_type
                $value_str .= "'',";//category
                $value_str .= "'',";//tax_code
                //$value_str .= $jet_product[''].',';//min_price
                $value_str .= "'" . addslashes(self::getData($jet_product['description'], self::MAX_SHORT_DESCRIPTION)) . "',";//short_description
                $value_str .= "'" . addslashes(self::getData($jet_product['title'], self::MAX_SHELF_DESCRIPTION)) . "',";//self_description
                $value_str .= "'Not Uploaded'";//status
                $value_str .= ")";
                $insert_data[] = $value_str;

                echo "Inserted product id : " . $jet_product['id'] . "<br>";

                //save product variants
                if ($jet_product['type'] == 'variants')
                    self::ImportVariants($jet_product['id'], $jet_product['merchant_id']);

                //save product_type
                self::InsertProductType($jet_product['product_type'], $jet_product['merchant_id']);

                echo "<br>---------------------********************----------------------<br>";
            }

            $query = "INSERT INTO `walmart_product`(`product_id`, `merchant_id`, `product_type`, `category`, `tax_code`, `short_description`, `self_description`, `status`) VALUES " . implode(',', $insert_data);
            Data::sqlRecords($query, null, "insert");
        } else {
            echo "No Products to Import!!";
        }
    }

    public static function ImportVariants($product_id, $merchant_id)
    {
        $walmart_query = "SELECT `option_id` FROM `walmart_product_variants` WHERE `product_id`=" . $product_id . " AND `merchant_id`=" . $merchant_id;
        $walmart_product_variants = Data::sqlRecords($walmart_query, "all", "select");

        $option_ids = '';
        if ($walmart_product_variants && is_array($walmart_product_variants)) {
            foreach ($walmart_product_variants as $key => $product_variants) {
                $option_ids .= $product_variants['option_id'];
                if (isset($walmart_product_variants[$key + 1]))
                    $option_ids .= ',';
            }
        }

        $query = "SELECT * FROM `jet_product_variants` WHERE `product_id`=" . $product_id;
        if ($option_ids != '')
            $query .= " AND `option_id` NOT IN (" . $option_ids . ")";

        $jet_variants = Data::sqlRecords($query, "all", "select");
        if ($jet_variants && is_array($jet_variants)) {
            $insert_data = [];
            foreach ($jet_variants as $variant) {
                $value_str = "(";
                $value_str .= $variant['option_id'] . ",";//option_id
                $value_str .= $variant['product_id'] . ",";//product_id
                $value_str .= $variant['merchant_id'] . ",";//merchant_id
                $value_str .= "'" . addslashes($variant['variant_option1']) . "',";//new_variant_option_1
                $value_str .= "'" . addslashes($variant['variant_option2']) . "',";//new_variant_option_2
                $value_str .= "'" . addslashes($variant['variant_option3']) . "',";//new_variant_option_3
                $value_str .= "'Not Uploaded'";//status
                $value_str .= ")";
                $insert_data[] = $value_str;

                echo "Inserted product variants id : " . $variant['option_id'] . "<br>";
            }

            $query = "INSERT INTO `walmart_product_variants`(`option_id`, `product_id`, `merchant_id`, `new_variant_option_1`, `new_variant_option_2`, `new_variant_option_3`, `status`) VALUES " . implode(',', $insert_data);
            Data::sqlRecords($query, null, "insert");
        }
    }

    public static function InsertProductType($product_type, $merchant_id)
    {
        $query = "SELECT * FROM `walmart_category_map` WHERE `merchant_id` = " . $merchant_id . " AND `product_type` LIKE '" . $product_type . "' LIMIT 0,1";

        $data = Data::sqlRecords($query, "one", "select");

        if (!$data) {
            $query = "INSERT INTO `walmart_category_map`(`merchant_id`, `product_type`) VALUES (" . $merchant_id . ",'" . addslashes($product_type) . "')";
            Data::sqlRecords($query, null, "insert");

            echo "Inserted product type : " . $product_type . "<br>";
        }
    }

    public static function getData($string, $length)
    {
        if (strlen($string) > $length) {
            $string = substr($string, 0, $length);
        }
        return $string;
    }

    /**
     *    Create Webhooks
     */
    public function actionCreatewebhooks()
    {
        $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
        Data::createWebhooks($sc);
    }

    /**
     *    Import Products from Shopify
     */
    public function actionImportproducts()
    {
        /*$sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
        $countUpload=0;
        $countUpload=$sc->call('GET', '/admin/products/count.json', array('published_status'=>'published'));*/
    }

    public function actionTest()
    {
        $category = 'Jewelry';
        var_dump(WalmartCategory::getCategoryVariantAttributes($category));
    }

    /**
     *    Walmart Product Delete
     */
    public static function walmartDeleteProductStatus($product_id, $type, $merchant_id)
    {
        if ($type == "simple") {
            $updateQuery = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_DELETE . "' WHERE merchant_id='" . $merchant_id . "' AND `product_id`='{$product_id}'";
            Data::sqlRecords($updateQuery, null, 'update');
        } else {
            $updateQuery = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_DELETE . "' WHERE merchant_id='" . $merchant_id . "' AND `product_id`='{$product_id}'";
            Data::sqlRecords($updateQuery, null, 'update');
            $updateQuery = "UPDATE `walmart_product_variants` SET `status`='" . WalmartProduct::PRODUCT_STATUS_DELETE . "' WHERE merchant_id='" . $merchant_id . "' AND `product_id`='{$product_id}'";
            Data::sqlRecords($updateQuery, null, 'update');
        }
    }

    public function actionNotificationMail()
    {
        $data = Data::sqlRecords("SELECT * FROM `notification_mail` WHERE `send_mail`='1' AND `marketplace` LIKE '%walmart%'", 'all');
        if (!empty($data)) {

            foreach ($data as $value) {

                switch ($value['mail_type']) {
                    case 'trial_expire':
                        $days = json_decode($value['days']);
                        foreach ($days as $day) {
                            $new_date = date('Y-m-d', strtotime("+" . $day . " days"));
                            $merchant_data = Data::sqlRecords("SELECT * FROM `walmart_extension_detail` INNER JOIN `walmart_shop_details` ON `walmart_extension_detail`.`merchant_id`=`walmart_shop_details`.`merchant_id` WHERE `walmart_extension_detail`.`expire_date` LIKE '%" . $new_date . "%' AND `walmart_extension_detail`.`status` IN ('Trial','Not Purchase') AND `walmart_shop_details`.`status`='1' ", 'all');
                            if (!empty($merchant_data)) {
                                foreach ($merchant_data as $merchant) {
                                    $mailData = [
                                        'sender' => 'shopify@cedcommerce.com',
                                        'reciever' => 'shivamverma@cedcoss.com',
                                        'email' => 'shivamverma@cedcoss.com',
                                        'subject' => $value['subject']. $merchant['shop_url'],
                                    ];
                                    $mailer = new Mail($mailData,'email/trialexpire.html','php',true);
                                    $mailer->sendMail();
                                }
                            }
                        }
                        break;
                    case 'license_expire':
                        $days = json_decode($value['days']);
                        foreach ($days as $day) {
                            $new_date = date('Y-m-d', strtotime("+" . $day . " days"));
                            $merchant_data = Data::sqlRecords("SELECT * FROM `walmart_extension_detail` INNER JOIN `walmart_shop_details` ON `walmart_extension_detail`.`merchant_id`=`walmart_shop_details`.`merchant_id` WHERE `walmart_extension_detail`.`expire_date` LIKE '%" . $new_date . "%' AND `walmart_extension_detail`.`status` IN ('Purchased') AND `walmart_shop_details`.`status`='1' ", 'all');
                            if (!empty($merchant_data)) {
                                foreach ($merchant_data as $merchant) {
                                    $mailData = [
                                        'sender' => 'shopify@cedcommerce.com',
                                        'reciever' => 'shivamverma8829@gmail.com',
                                        'email' => 'shivamverma8829@gmail.com',
                                        'subject' => $value['subject'],
                                        'shop_name'=> $merchant['shop_name'],
                                        'shop_url'=>$merchant['shop_url'],
                                    ];
                                    $mailer = new Mail($mailData,'email/licenceexpire.html','php',true);
                                    $mailer->sendMail();
                                }

                            }

                        }
                        break;
                    /*case 'purchased':
                        $new_date = date('Y-m-d');
                        $merchant_data = Data::sqlRecords("select * from walmart_extension_detail wed inner join walmart_shop_details wsd on wed.merchant_id = wsd.merchant_id inner join walmart_recurring_payment wrp on wsd.merchant_id = wrp.merchant_id where wed.status='purchased' and wsd.status='1' and wrp.status='active' and wrp.billing_on LIKE '%" . $new_date . "%'", 'all');

                        if (!empty($merchant_data)) {

                            foreach ($merchant_data as $merchant) {
                                $mailData = [
                                    'sender' => 'shopify@cedcommerce.com',
                                    'reciever' => 'shivamverma@cedcoss.com',
                                    'email' => 'shivamverma@cedcoss.com',
                                    'subject' => $value['subject'],
                                ];
                                $mailer = new Mail($mailData,'email/purchased.html','php',true);
                                $mailer->sendMail();
                            }
                        }
                        break;*/
                    case 'not_registered':
                        $days = json_decode($value['days']);
                        foreach ($days as $day) {
                            $new_date = date('Y-m-d', strtotime("+" . $day . " days"));
                            $merchant_data = Data::sqlRecords("select * from walmart_extension_detail wed inner join walmart_shop_details wsd on wed.merchant_id = wsd.merchant_id inner join walmart_installation wal_install on wsd.merchant_id = wrp.merchant_id where wed.status='purchased' and wsd.status='1' and wal_install.status='pending' and wal_install.step = 1 and `wed`.`expire_date` LIKE '%" . $new_date . "%' AND `wed`.`status` IN ('Trial','Not Purchase') ", 'all');
                            if (!empty($merchant_data)) {
                                foreach ($merchant_data as $merchant) {
                                    $mailData = [
                                        'sender' => 'shopify@cedcommerce.com',
                                        'reciever' => 'shivamverma@cedcoss.com',
                                        'email' => 'shivamverma@cedcoss.com',
                                        'subject' => $value['subject'],
                                    ];
                                    /*$mailer = new Mail($mailData,'email/installmail.html','php',true);
                                    $mailer->sendMail();*/
                                }

                            }
                        }
                        break;
                    case 'not_configured':
                        $days = json_decode($value['days']);
                        foreach ($days as $day) {
                            $new_date = date('Y-m-d', strtotime("+" . $day . " days"));
                            $merchant_data = Data::sqlRecords("select * from walmart_extension_detail wed inner join walmart_shop_details wsd on wed.merchant_id = wsd.merchant_id inner join walmart_installation wal_install on wsd.merchant_id = wrp.merchant_id where wed.status='purchased' and wsd.status='1' and wal_install.step = 1 and `wed`.`expire_date` LIKE '%" . $new_date . "%' AND `wed`.`status` IN ('Trial','Not Purchase','Purchased') ", 'all');
                            if (!empty($merchant_data)) {
                                foreach ($merchant_data as $merchant) {
                                    $mailData = [
                                        'sender' => 'shopify@cedcommerce.com',
                                        'reciever' => 'shivamverma@cedcoss.com',
                                        'email' => 'shivamverma@cedcoss.com',
                                        'subject' => $value['subject'],
                                    ];
                                    /*$mailer = new Mail($mailData,'email/installmail.html','php',true);
                                    $mailer->sendMail();*/
                                }

                            }
                        }
                        break;
                    case 'product_upload':
                        $days = json_decode($value['days']);
                        foreach ($days as $day) {
                            $new_date = date('Y-m-d', strtotime("+" . $day . " days"));
                            $merchant_ids = Data::sqlRecords("Select DISTINT(merchant_id) from walmart product where status != 'PUBLISHED' AND status != 'Item Processing'", 'all');
                            if ($merchant_ids) {
                                foreach ($merchant_ids as $merchant_id) {
                                    $merchant_data = Data::sqlRecords("select * from walmart_extension_detail wed inner join walmart_shop_details wsd on wed.merchant_id = wsd.merchant_id inner join walmart_installation wal_install on wsd.merchant_id = wrp.merchant_id where wed.status='purchased' and wsd.status='1' and wal_install.step = 1 and `wed`.`expire_date` LIKE '%" . $new_date . "%' AND `wed`.`status` IN ('Trial','Not Purchase','Purchased') ", 'one');
                                    if (!empty($merchant_data)) {
                                        $mailData = [
                                            'sender' => 'shopify@cedcommerce.com',
                                            'reciever' => 'shivamverma@cedcoss.com',
                                            'email' => 'shivamverma@cedcoss.com',
                                            'subject' => $value['subject'],
                                        ];
                                        /*$mailer = new Mail($mailData, 'email/installmail.html', 'php', true);
                                        $mailer->sendMail();*/

                                    }
                                }
                            }

                        }
                        break;

                }
            }
        }
        print_r($data);
        die;
    }

}