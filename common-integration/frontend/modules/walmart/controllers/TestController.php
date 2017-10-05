<?php
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Sendmail;
use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\JetProduct;
use frontend\modules\walmart\models\JetProductVariants;
use frontend\modules\walmart\components\Mail;
use console\controllers\CronController;
use frontend\modules\walmart\controllers\WalmartorderdetailController;
use frontend\modules\walmart\controllers\WalmartrepricingController;
use frontend\modules\walmart\components\Walmartappdetails;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartCronSchedule;
use frontend\modules\walmart\controllers\WalmartWebhookController;
use frontend\modules\walmart\models\WalmartProduct as WalmartProductModel;
use frontend\modules\walmart\controllers\GetnotificationController;

class TestController extends Controller
{
    public function actionTest()
    {
        print_r(GetnotificationController::setread());die;
        $data = '{"2317287619":{"product_id":"827410883","price":"49.99","sku":"CA-008258 24","merchant_id":"7"}}';
        $data = json_decode($data,true);
       // print_r($data);die;
        //$obj = new WalmartWebhookController();
        WalmartWebhookController::actionPriceupdate($data);
        echo Yii::$app->request->referrer;
        die("kjkkk");
        
        $this->actionProductrepricing();
        die("kkkkkk");
        /*var_dump(Data::sqlRecords("UPDATE `walmart_product_repricing` SET `option_id`='1478523690' WHERE `id`=1", null, 'update'));
        die("kk");*/

        self::addShippingException(true);
        die("kk");
        //var_dump(json_decode('[{"color":{"1":"color","2":"colorValue"}}]',true));
        //var_dump(json_decode('[{"color":{"1":"color","2":"colorValue"}}]',true));die;
        $jsonFilePath = Yii::getAlias('@webroot') . '/WalmartCategories.json';

        if (file_exists($jsonFilePath)) {
            $myfile = fopen($jsonFilePath, "r") or die("Unable to open file!");
            $fileContent = fread($myfile, filesize($jsonFilePath));
            fclose($myfile);

            $categories = json_decode($fileContent, true);

            $common_required_attrs = explode(',', 'sku,productName,longDescription,shelfDescription,shortDescription,mainImage/mainImageUrl,productIdentifiers/productIdentifier/productIdType,productIdentifiers/productIdentifier/productId,productTaxCode,brand,price/amount,shippingWeight/value');

            $insert_values = [];
            foreach ($categories as $key => $value) {
                $walmart_required_attributes = explode(',', $value['walmart_required_attributes']);
                $test = array_diff($walmart_required_attributes, $common_required_attrs);

                if (is_array($test)) {
                    $new_required_attr = [];
                    foreach ($test as $value1) {
                        if (strpos($value1, '/') !== false) {
                            $explode = explode('/', $value1);
                            $new_required_attr[] = [$explode[0] => array_combine(range(1, count($explode)), array_values($explode))];
                        } else {
                            $new_required_attr[] = $value1;
                        }
                    }

                    if (count($new_required_attr))
                        $categories[$key]['walmart_required_attributes'] = json_encode($new_required_attr);
                    else
                        $categories[$key]['walmart_required_attributes'] = '';
                } else {
                    $categories[$key]['walmart_required_attributes'] = '';
                }

                $query = 'UPDATE `walmart_category` SET `attributes` = ' . "'" . $categories[$key]['walmart_required_attributes'] . "'" . ' WHERE `walmart_category`.`category_id` LIKE ' . "'" . $categories[$key]['cat_id'] . "'";
                /*echo $query.'<br>------------**********************-----------------<br>';*/
                Data::sqlRecords($query, null, 'insert');
                echo $value['cat_id'] . '<br>------------**********************-----------------<br>';

            }
        } else {
            die('File Not Found.');
        }
        die('test');
    }


    public function actionTestOrder()
    {

            $query = "SELECT * FROM `walmart_configuration` WHERE `merchant_id`='14' ";
    $config = Data::sqlRecords($query,"one","select");

    $objController=Yii::$app->createController('walmart/walmartorderdetail');
    $objController[0]->actionCreate($config,true);

       /* $email = 'ankitsingh@cedcoss.com';
        $mailData = ['sender' => 'shopify@cedcommerce.com',
                                                    'reciever' => $email,
                                                    'email' => $email,
                                                    'merchant_id'=>'14',
                                                    'subject' => 'You have an order from Walmart.com',
                                                    'bcc' => 'kshitijverma@cedcoss.com,ankitsingh1436@gmail.com',
                                                    'reference_order_id' => 10,
                                                    'merchant_order_id' => 20,
                                                    'product_sku' => 'test'
                                                    ];
                                        $mailer = new Mail($mailData,'email/order.html','php',true);
                                        $mailer->sendMail();
                                        die("kkkk");*/
    }

    public function actionTestMail()
    {

        $mailer = new Mail(['sender' => 'ankitsingh1436@gmail.com', 'reciever' => 'ankitsingh1436@gmail.com', 'email' => 'ankitsingh1436@gmail.com', 'merchant_id' => '6', 'subject' => 'Testing'], 'email/order.html', 'php', true);
        $mailer->sendMail();
    }

    public function actionTesttemplates()
    {
        $email_id = 'shivamverma8829@gmail.com';
        Sendmail::installmail($email_id);

    }


    public function actionProductInventoryExport()
    {
        $merchant_id = Yii::$app->user->identity->id;

        if (!file_exists(\Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id)) {
            mkdir(\Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id, 0775, true);
        }
        $base_path = \Yii::getAlias('@webroot') . '/var/csv_export/' . $merchant_id . '/productinventory.csv';
        $file = fopen($base_path, "w");

        $headers = array('Id', 'Sku', 'Type', 'Inventory');

        $row = array();
        foreach ($headers as $header) {
            $row[] = $header;
        }
        fputcsv($file, $row);

        $productdata = array();
        $i = 0;

        $model = JetProduct::find()->select('id,sku,type,qty')->where(['merchant_id' => $merchant_id])->all();
        foreach ($model as $value) {
            if ($value->sku == "") {
                continue;
            }
            if ($value->type == "simple") {
                $productdata[$i]['id'] = $value->id;
                $productdata[$i]['sku'] = $value->sku;
                $productdata[$i]['type'] = "No Variants";
                $productdata[$i]['qty'] = $value->qty;
                $i++;
            } else {
                $optionResult = [];
                $query = "SELECT option_id,option_title,option_sku,option_qty,asin,option_mpn FROM `jet_product_variants` WHERE product_id='" . $value['id'] . "' order by option_sku='" . addslashes($value['sku']) . "' desc";
                $optionResult = Data::sqlRecords($query);

                if (is_array($optionResult) && count($optionResult) > 0) {
                    foreach ($optionResult as $key => $val) {
                        if ($val['option_sku'] == "")
                            continue;
                        if ($value['sku'] == $val['option_sku']) {
                            $productdata[$i]['type'] = "Parent";
                        } else {
                            $productdata[$i]['type'] = "Variants";
                        }
                        $productdata[$i]['id'] = $value['id'];
                        $productdata[$i]['sku'] = $val['option_sku'];
                        $productdata[$i]['qty'] = $val['option_qty'];


                        $i++;
                    }
                }
            }
        }

        foreach ($productdata as $v) {
            $row = array();
            $row[] = $v['id'];
            $row[] = $v['sku'];
            $row[] = $v['type'];
            $row[] = $v['qty'];

            fputcsv($file, $row);
        }
        fclose($file);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return \Yii::$app->response->sendFile($base_path);
    }


    public function actionReadinventorycsv()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $merchant_id = Yii::$app->user->identity->id;
        if (isset($_FILES['csvfile']['name'])) {
            $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'text/comma-separated-values');

            if (!in_array($_FILES['csvfile']['type'], $mimes)) {
                Yii::$app->session->setFlash('error', "CSV File type Changed, Please import only CSV file");
                return $this->redirect(['index']);
            }

            $newname = $_FILES['csvfile']['name'];

            if (!file_exists(Yii::getAlias('@webroot') . '/var/csv_import/' . date('d-m-Y') . '/' . $merchant_id)) {
                mkdir(Yii::getAlias('@webroot') . '/var/csv_import/' . date('d-m-Y') . '/' . $merchant_id, 0775, true);
            }

            $target = Yii::getAlias('@webroot') . '/var/csv_import/' . date('d-m-Y') . '/' . $merchant_id . '/' . $newname . '-' . time();
            $row = 0;
            $flag = false;
            $row1 = 0;
            if (!file_exists($target)) {
                move_uploaded_file($_FILES['csvfile']['tmp_name'], $target);
            }
            $selectedProducts = array();
            $import_errors = array();
            if (($handle = fopen($target, "r"))) {
                $status = WalmartProductModel::PRODUCT_STATUS_UPLOADED;
                $allpublishedSku = WalmartProduct::getAllProductSku($merchant_id, $status);
                $row = 0;
                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE) {
                    if ($row == 0 && (trim($data[0]) != 'Id' || trim($data[1]) != 'Sku' || trim($data[2]) != 'Type' || trim($data[3]) != 'Inventory')) {
                        $flag = true;
                        break;
                    }
                    $num = count($data);
                    $row++;
                    if ($row == 1)
                        continue;

                    $pro_id = trim($data[0]);
                    $pro_sku = trim($data[1]);
                    $pro_type = trim($data[2]);
                    $pro_qty = trim($data[3]);

                    if ($pro_id == '' || $pro_sku == '' || $pro_type == '' || $pro_qty == '') {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid data.';
                        continue;
                    }

                    if (!is_numeric($pro_id) || !is_numeric($pro_qty)) {
                        $import_errors[$row] = 'Row ' . $row . ' : Invalid product_id / inventory.';
                        continue;
                    }

                    if (!in_array($pro_sku, $allpublishedSku)) {
                        $import_errors[$row] = 'Row ' . $row . ' : ' . 'Sku => "' . $pro_sku . '" is invalid/not published on walmart.';
                        continue;
                    }

                    $productData = array();
                    $productData['id'] = $pro_id;
                    $productData['sku'] = $pro_sku;
                    $productData['type'] = $pro_type;
                    $productData['inventory'] = $pro_qty;

                    $selectedProducts[] = $productData;
                }

                if (count($selectedProducts)) {
                    $walmartConfig = Data::sqlRecords("SELECT `consumer_id`,`secret_key` FROM `walmart_configuration` WHERE merchant_id='" . $merchant_id . "'", 'one');
                    if ($walmartConfig) {
                        $this->walmartHelper = new WalmartProduct($walmartConfig['consumer_id'], $walmartConfig['secret_key']);

                        $priceUploadCountPerRequest = 1000;
                        $selectedProducts = array_chunk($selectedProducts, $priceUploadCountPerRequest);
                        foreach ($selectedProducts as $_selectedProducts) {
                            $response = $this->walmartHelper->updateInventoryViaCsv($_selectedProducts);
                            if ($_selectedProducts['type'] == "No Variants") {
                                $query = "update `jet_product` set qty ='" . $_selectedProducts['qty'] . "' where merchant_id='" . MERCHANT_ID . "' AND id='" . $_selectedProducts['id'] . "' AND sku='" . $_selectedProducts['sku'] . "'";
                                Data::sqlRecords($query, null, "update");

                            } else {
                                $query = "update `jet_product_variants` set option_qty ='" . $_selectedProducts['qty'] . "' where merchant_id='" . MERCHANT_ID . "' AND product_id='" . $_selectedProducts['id'] . "' AND option_sku='" . $_selectedProducts['sku'] . "'";
                                Data::sqlRecords($query, null, "update");
                            }


                            if (isset($response['errors'])) {
                                if (isset($response['errors']['error'])) {
                                    Yii::$app->session->setFlash('warning', $response['errors']['error']['code']);
                                } else {
                                    Yii::$app->session->setFlash('warning', "Inventory of Products is not updated due to some error.");
                                }
                            } elseif (isset($response['error'])) {
                                if (isset($response['error'][0]['code'])) {
                                    Yii::$app->session->setFlash('warning', $response['error'][0]['code']);
                                } else {
                                    Yii::$app->session->setFlash('warning', "Inventory of Products is not updated due to unknown error.");
                                }
                            } elseif (isset($response['feedId'])) {
                                Yii::$app->session->setFlash('success', "Product Information has been updated successfully");
                            } else {
                                Yii::$app->session->setFlash('warning', "Inventory of Products is not updated.");
                            }
                        }
                    } else {
                        Yii::$app->session->setFlash('warning', "Inventory enter walmartapi...");
                    }

                    if (count($import_errors)) {
                        Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
                    }
                } else {
                    if (count($import_errors)) {
                        Yii::$app->session->setFlash('error', implode('<br>', $import_errors));
                    } else {
                        Yii::$app->session->setFlash('error', "Please Upload Csv file....");
                    }
                }
            } else {
                Yii::$app->session->setFlash('error', "File not found....");
            }
        } else {
            Yii::$app->session->setFlash('error', "Please Upload Csv file....");
        }
        return $this->redirect(['index']);
    }

    public function actionCommonattributeinsert()
    {

        //{"color->colorValue":"Gray"}
        $connection = Yii::$app->getDb();
        $query = "SELECT title,id,sku from `jet_product` where merchant_id='932'";
        $optionResult = Data::sqlRecords($query);
        foreach ($optionResult as $key => $value) {
            $data = Data::sqlRecords("SELECT common_attributes from `walmart_product` where merchant_id='932' AND product_id='" . $value['id'] . "'", 'one');
            $array = [];
            if (is_null($data['common_attributes'])) {
                $input = explode('Color', $value['title']);
                if (isset($input[1]) && !empty($input[1])) {
                    $array['color->colorValue'] = $input[1];
                    $query = "UPDATE `walmart_product` SET `common_attributes`='" . addslashes(json_encode($array)) . "' WHERE merchant_id='932' AND product_id='" . $value['id'] . "'";
                    $connection->createCommand($query)->execute();
                } else {
                    $array['color->colorValue'] = 'multicolor';
                    $query = "UPDATE `walmart_product` SET `common_attributes`='" . addslashes(json_encode($array)) . "' WHERE merchant_id='932' AND product_id='" . $value['id'] . "'";
                    $connection->createCommand($query)->execute();
                }

            }
        }
    }

    public function actionUpdateupc()
    {
        $data = file_get_contents('/opt/lampp/htdocs/backup/3apr/public_html/shopify/integration/frontend/modules/walmart/controllers/202.csv');
        $rows = explode("\n", $data);
        $csvArray = array();
        foreach ($rows as $row) {
            $csvArray[] = str_getcsv($row);
        }
        foreach ($csvArray as $csvArraykey => $csvArrayvalue) {
            if ($csvArraykey == 0) {
                continue;
            } else {
                if (isset($csvArrayvalue[0]) && !empty($csvArrayvalue[0])) {
                    $selectquery = "SELECT `additional_info` FROM `jet_product` WHERE `sku`='" . $csvArrayvalue[1] . "' AND `merchant_id`='202'";
                    $modelU = Data::sqlRecords($selectquery, "one", "select");
                    $modelU['additional_info'] = '{"upc_code":null,"brand":"Eat My Tackle","mpn":""}';
                    $jsonData = json_decode($modelU['additional_info'], true);
                    foreach ($jsonData as $key => $value) {
                        if (isset($csvArrayvalue[14]) && !empty($csvArrayvalue[14])) {
                            if ($key == 'upc_code') {
                                $jsonData[$key] = $csvArrayvalue[14];
                            }
                        }
                    }
                    $real_Data = json_encode($jsonData);
                    $query = "UPDATE `jet_product`  SET `upc`='" . $csvArrayvalue[14] . "',`additional_info`='" . $real_Data . "' WHERE `sku`='" . $csvArrayvalue[1] . "' AND `merchant_id`='202'";
                    Data::sqlRecords($query, null, "update");
                }
            }
        }

        print_r("ok done");
        die;
    }

    public static function addShippingException($removeFree)
    {
        $returnArray = [];
        $prepare = [];
        $shipping_region = [];
        foreach ($data['isShippingAllowed'] as $key => $value) {
            if ($removeFree) {
                if ($data['shipMethod'][$key] == 'Value') {
                    $shipping_region[$key] = $data['shipRegion'][$key];
                } else {
                    $prepare['shippingOverride']['isShippingAllowed'] = $value;
                    $prepare['shippingOverride']['shipRegion'] = $data['shipRegion'][$key];
                    $prepare['shippingOverride']['shipMethod'] = $data['shipMethod'][$key];
                    $prepare['shippingOverride']['shipPrice'] = $data['shipPrice'][$key];
                    $returnArray[] = $prepare;
                }
            } else {
                $prepare['shippingOverride']['isShippingAllowed'] = $value;
                $prepare['shippingOverride']['shipRegion'] = $data['shipRegion'][$key];
                $prepare['shippingOverride']['shipMethod'] = $data['shipMethod'][$key];
                $prepare['shippingOverride']['shipPrice'] = $data['shipPrice'][$key];
                $returnArray[] = $prepare;
            }
        }
        if (!empty($shipping_region)) {
            $freeShippingTag = self::removeFreeShippingData($shipping_region);
            $returnArray = $returnArray + $freeShippingTag['value']['_value'];
            foreach ($shipping_region as $skey => $svalue) {
                $prepare['shippingOverride']['isShippingAllowed'] = $data['isShippingAllowed'][$skey];
                $prepare['shippingOverride']['shipRegion'] = $data['shipRegion'][$skey];
                $prepare['shippingOverride']['shipMethod'] = $data['shipMethod'][$skey];
                $prepare['shippingOverride']['shipPrice'] = $data['shipPrice'][$skey];
                $returnArray[] = $prepare;
            }
        }
        return $returnArray;

    }

    public static function removeFreeShippingData($method = [])
    {

        $shipRegions = ['STREET_48_STATES', 'PO_BOX_48_STATES', 'STREET_AK_AND_HI', 'PO_BOX_AK_AND_HI',
            'PO_BOX_US_PROTECTORATES', 'STREET_US_PROTECTORATES'];
        if (!empty($method)) {
            $shipRegions = array_diff($shipRegions, $method);
        }
        foreach ($shipRegions as $shipRegionkey => $shipRegion) {
            $freeShippingTag['value']['_attribute'] = [];
            $freeShippingTag['value']['_value'][$shipRegionkey]['shippingOverride']['isShippingAllowed'] = 'false';
            $freeShippingTag['value']['_value'][$shipRegionkey]['shippingOverride']['shipRegion'] = $shipRegion;
            $freeShippingTag['value']['_value'][$shipRegionkey]['shippingOverride']['shipMethod'] = 'VALUE';
            $freeShippingTag['value']['_value'][$shipRegionkey]['shippingOverride']['shipPrice'] = '0.0';
        }
        // print_r($freeShippingTag);die("jjj");
        $freeShippingTag['key'] = 'shippingOverrides';

        return $freeShippingTag;
    }
 public function actionProductrepricing()
  {
    $obj = new WalmartrepricingController(Yii::$app->controller->id,''); 

     if (file_exists(\Yii::getAlias('@webroot').'/var/walmart/productpricing')) {
        $files = glob(\Yii::getAlias('@webroot').'/var/walmart/productpricing/*'); // get all file names
        if(!empty($files)){
            foreach($files as $file){ // iterate files
               $baseName = basename($file);
               $contentFiles = glob($file.'/*');
               if(!empty($contentFiles)){
                    foreach ($contentFiles as $key => $value) {
                      $data = file_get_contents($value);
                      $status= WalmartrepricingController::postPricedata(json_decode($data,true),$baseName);
                       unlink($value);// delete file
                    }
               }
            }
        }
    }
    $cron_array = array();
    $connection = Yii::$app->getDb();
    $cronData = WalmartCronSchedule::find()->where(['cron_name'=>'product_repricing'])->one();
    if($cronData && $cronData['cron_data'] != "") {
        $cron_array = json_decode($cronData['cron_data'],true);
        $query = "SELECT * FROM `walmart_product_repricing` WHERE `repricing_status`='1' AND `id`>'".$cronData['cron_data']."'ORDER BY `id` ASC LIMIT 100000";
        $cron_array = Data::sqlRecords($query,'all','select');
       if(empty($cron_array)){
        $query = "SELECT * FROM `walmart_product_repricing` WHERE `repricing_status`='1' ORDER BY `id` ASC LIMIT 100000";
        $cron_array = Data::sqlRecords($query,'all','select');
       }
    }
    else {
        $query = "SELECT * FROM `walmart_product_repricing` WHERE `repricing_status`='1' ORDER BY `id` ASC LIMIT 100000";
        $cron_array = Data::sqlRecords($query,'all','select');
    }

    if(is_array($cron_array) && count($cron_array)>0)
    {
        $obj->actionCronPriceUpdate($cron_array);

    }
    $html = ob_get_clean();
  }

  /**
  * Action for webhook functionality
  */

  public function actionTestwebhook(){
    $url = Yii::getAlias('@webwalmarturl')."/walmart-webhook/productupdate?maintenanceprocess=1";
    $data = '{"id":"9762898252","title":"puma tshirt","body_html":"puma_bags\u00a0puma_bags\u00a0puma_bags","vendor":"Ced-Jet Test Store","product_type":"tshirt","created_at":"2017-02-23T02:35:57-05:00","handle":"puma_bags","updated_at":"2017-05-23T09:05:57-04:00","published_at":"2017-02-23T02:35:00-05:00","published_scope":"global","tags":"","variants":[{"id":"34777380940","product_id":"9762898252","title":"x","price":"50.00","sku":"iuyiyuiu-1","position":"1","grams":"10000","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"x","created_at":"2017-02-23T02:35:57-05:00","updated_at":"2017-05-23T09:05:36-04:00","taxable":"1","barcode":"","inventory_quantity":"157","weight":"10","weight_unit":"kg","old_inventory_quantity":"157","requires_shipping":"1"},{"id":"34783706188","product_id":"9762898252","title":"xl","price":"50.00","sku":"iuyiyuiu-2","position":"2","grams":"10000","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"xl","created_at":"2017-02-23T06:03:18-05:00","updated_at":"2017-05-23T09:05:48-04:00","taxable":"1","barcode":"","inventory_quantity":"1142","weight":"10","weight_unit":"kg","old_inventory_quantity":"1142","requires_shipping":"1"},{"id":"34783706316","product_id":"9762898252","title":"n","price":"50.00","sku":"iuyiyuiu-3","position":"3","grams":"0","inventory_policy":"deny","fulfillment_service":"manual","inventory_management":"shopify","option1":"n","created_at":"2017-02-23T06:03:18-05:00","updated_at":"2017-05-23T09:05:57-04:00","taxable":"1","barcode":"","inventory_quantity":"125554554","weight":"0","weight_unit":"kg","old_inventory_quantity":"125454","requires_shipping":"1"}],"options":[{"id":"11807809740","product_id":"9762898252","name":"Size","position":"1","values":["x","xl","n"]}],"images":[{"id":"23368516364","product_id":"9762898252","position":"1","created_at":"2017-03-27T00:48:35-04:00","updated_at":"2017-04-19T00:59:57-04:00","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/ZopimChat_df3faf7e-c4ee-444e-840e-25e2b3f81e86.png?v=1492577997"}],"image":{"id":"23368516364","product_id":"9762898252","position":"1","created_at":"2017-03-27T00:48:35-04:00","updated_at":"2017-04-19T00:59:57-04:00","src":"https:\/\/cdn.shopify.com\/s\/files\/1\/1009\/2336\/products\/ZopimChat_df3faf7e-c4ee-444e-840e-25e2b3f81e86.png?v=1492577997"},"shopName":"ced-jet.myshopify.com"}';
    var_dump(Data::sendCurlRequest(json_decode($data,true),$url));
    
  }
  public function actionCheck()
  {
        $dir = \Yii::getAlias('@webroot').'/var/exceptions/checkhack/';
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        $data = [];
        $filenameOrig = $dir.$_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'].'.log';
        $data = file_get_contents($filenameOrig);
        $data = (int)$data+1;
        
        $handle = fopen($filenameOrig,'w');
        fwrite($handle,$data);
        fclose($handle);
  }

}
