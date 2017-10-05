<?php
namespace frontend\modules\walmart\components;

use Yii;
use \DOMDocument;
use yii\base\Component;
use frontend\modules\walmart\components\Excel\Reader;


class Upload extends component
{
    private $_consumerId;
    private $_secretKey;

    public function __construct($consumerId, $secretKey)
    {
        $this->_consumerId = $consumerId;
        $this->_secretKey = $secretKey;
    }

    public function readItemCsv($csvFilePath, $limit=null, $page=0)
    {
        $csvData = [];
        if(file_exists($csvFilePath))
        {
            if (($handle = fopen($csvFilePath, "r")))
            {
                $row=0;
                $columns = [];

                $start = 1+($page*$limit);
                $end = $limit+($page*$limit);

                while (($data = fgetcsv($handle, 90000, ",")) !== FALSE)
                {
                    if($row == 0) {
                        $row++;
                        $columns = $data;
                        continue;
                    }

                    if(is_null($limit))
                    {
                        if(count($prepareData=self::prepareCsvData($columns,$data))) {
                            $csvData[] = $prepareData;
                        }
                    }
                    else
                    {
                        if($start <= $row && $row <= $end)
                        {
                            if(count($prepareData=self::prepareCsvData($columns,$data))) {
                                $csvData[] = $prepareData;
                            }
                        }
                        elseif($row > $end)
                        {
                            break;
                        }
                    }
                    $row++;
                }
            }
        }
        return $csvData;
    }

    /**
     * Prepare CSV Data
     *
     * @param array $header Csv Header Row
     * @param array $row Csv Data Row
     * @return array
     */
    public static function prepareCsvData($header, $row)
    {
        $data = [];
        if(is_array($header) && is_array($row))
        {
            foreach ($header as $headerKey => $headerValue) 
            {
                if(isset($row[$headerKey]))
                {
                    $index = str_replace(' ', '_', strtolower($headerValue));
                    $data[$index] = $row[$headerKey];
                }
            }
        }
        return $data;
    }

    /**
     * Get the Number of rows in csv
     *
     * @param string $csvFilePath Path of Csv File
     * 
     * @return array
     */
    public function getRowsInCsv($csvFilePath)
    {
        if(file_exists($csvFilePath)) {
            return count(file($csvFilePath))-1;
        }
        else {
            return 0;
        }
    }

    public function prepareProduct($productData, $xml, &$MPItemFeed)
    {
        if($productData['sku'] != '' && $productData['mpn'] != '')
            return false;

        if($productData['status_(1=enabled,_0=_disabled)'] == '0')
            return false;

        if($productData['model'] == '')
            return false;

        $category = ['HealthAndBeauty', 'MedicineAndSupplements'];

        $taxcode = '2048154';

        $uploadedProducts = [];
        if($variants=$this->getProductVariantOptions($productData))
        {
            $group_id = 'grp_id_'.time();
            foreach ($variants as $variant) {
                $qty = $variant['qty'];
                $price = intval($productData['price']) + $variant['price'];
                $weight = self::getProductWeight($productData, intval($productData['weight']) + $variant['weight']);
                
                $barcode = self::getBarcode($productData['model'], $variant['options']['Flavor'], isset($variant['options']['Weight'])?$variant['options']['Weight']:$variant['options']['Weight & servings']);
                if($barcode === false)
                {
                    return false;
                }
                $variantInfo = [];
                $variantInfo['type'] = 'variant';
                $variantInfo['product_name'] = $productData['product_name'];
                $variantInfo['sku'] = 'SKU-'.$productData['product_id'].'-'.implode('~', $variant['options']);
                $variantInfo['qty'] = $qty;
                $variantInfo['price'] = $price;
                $variantInfo['weight'] = $weight;
                $variantInfo['options'] = $variant['options'];
                $variantInfo['mapping'] = ['Weight' => 'size', 'Flavor' => 'flavor', 'Weight & servings' => 'size'];
                $variantInfo['description'] = $productData['description'];
                $variantInfo['main_image'] = $productData['image(main_image)'];;
                $variantInfo['secondary_images'] = explode(';', $productData['(image1;image2;image3)']);
                $variantInfo['group_id'] = $group_id;
                $variantInfo['taxcode'] = $taxcode;
                $variantInfo['brand'] = $productData['model'];
                $variantInfo['id_type'] = 'UPC';
                $variantInfo['id_value'] = $barcode;
                
                $MPItem = self::prepareMPItem($variantInfo, $category, $xml);

                $MPItemFeed->appendChild($MPItem);

                $uploadedProducts[] = ['product_id'=>$productData['product_id'], 'variant'=>$variant['options']];
            }
        }
        else
        {
            $qty = $productData['quantity'];
            $price = intval($productData['price']);
            $weight = self::getProductWeight($productData, intval($productData['weight']));
            
            $productInfo = [];
            $productInfo['type'] = 'simple';
            $productInfo['product_name'] = $productData['product_name'];;
            //$productInfo['sku'] = 'ENTER_PRODUCT_SKU';
            $productInfo['sku'] = empty($productData['sku']) ? 'SKU-'.$productData['product_id'] : $productData['sku'];
            $productInfo['qty'] = $qty;
            $productInfo['price'] = $price;
            $productInfo['weight'] = $weight;
            $productInfo['description'] = $productData['description'];
            $productInfo['main_image'] = $productData['image(main_image)'];
            $productInfo['secondary_images'] = explode(';', $productData['(image1;image2;image3)']);
            $productInfo['taxcode'] = $taxcode;
            $productInfo['brand'] = $productData['model'];
           /* $productInfo['id_type'] = 'ENTER_IDENTIFIER_TYPE';
            $productInfo['id_value'] = 'ENTER_IDENTIFIER_VALUE';*/
            $productInfo['id_type'] = 'UPC';
            $productInfo['id_value'] = $productData['mpn'];

            $MPItem = self::prepareMPItem($productInfo, $category, $xml);

            $MPItemFeed->appendChild($MPItem);
        }

        if(count($uploadedProducts)) {
            var_dump($uploadedProducts);
        }
    }


    public function getProductVariantOptions($productData)
    {
        //Weight:radio;Flavor:select;
        if(!empty($options=$productData['option_(name_and_type)_size:select;color:radio']))
        {
            $variantOptions = [];

            $options = explode(';', $options);
            $options = array_filter($options);

            foreach ($options as $option) 
            {
                $option = explode(':', $option);
                $optionName = addslashes($option[0]);

                if($option[1]=='select' || $option[1]=='radio')
                    $variantOptions[$optionName] = [];
            }

            //Weight:3 lbs-500-0-add-0-add;Weight:5 lbs-500-15-add-2-add;Flavor:unflavored-1974-0-add-0-add;
            if(!empty($optionValues=$productData['option:value1-qty-price-add/sub-weight-add/sub;option:value1-qty-subtract_stock-price-points-weight']))
            {
                $optionValues = explode(';', $optionValues);
                $optionValues = array_filter($optionValues);

                foreach ($optionValues as $optionValue) 
                {
                    $_variantData = [];

                    $optionValue = explode(':', $optionValue);
                    $optionName = $optionValue[0];

                    if(isset($variantOptions[$optionName]))
                    {
                        $value = explode('-', $optionValue[1]);

                        $_variantData['value'] = $value[0];
                        $_variantData['qty'] = intval($value[1]);

                        if($value[3]=='add') {
                            $_variantData['price'] = floatval($value[2]);
                        }
                        elseif($value[3]=='sub') {
                            $_variantData['price'] = (-1) * floatval($value[2]);
                        }

                        if($value[5]=='add') {  
                            $_variantData['weight'] = floatval($value[4]);
                        }
                        elseif($value[5]=='sub') {
                            $_variantData['weight'] = (-1) * floatval($value[4]);
                        }

                        $variantOptions[$optionName][$value[0]] = $_variantData;
                    }
                }
            }
            $combinations = self::prepareCombinations($variantOptions, $productData);
            return $combinations;
        }

        return false;
    }

    public function prepareCombinations($variantOptions, $productData)
    {
        $arrays = [];
        $options = [];
        foreach ($variantOptions as $optionName => $optionData) {
            $arrays[] = array_keys($optionData);
            $options[] = $optionName;
        }

        $combinations = self::combinations($arrays);

        $variantData = [];

        $tempData = [];
        foreach ($combinations as $combination) {
            $qty = [];
            $price = 0;
            $weight = 0;
            $optionValue = [];
            foreach ($combination as $key => $value) {
                $qty[] = $variantOptions[$options[$key]][$value]['qty'];
                $price += $variantOptions[$options[$key]][$value]['price'];
                $weight += $variantOptions[$options[$key]][$value]['weight'];
                $optionValue[$options[$key]] = $value;
            }
            $variantData[] = ['qty'=>min($qty), 'price'=>$price, 'weight'=>$weight, 'options'=>$optionValue];

            $indx = implode('~', $combination);
            $tempData[$indx] = '';
        }

        //self::saveCombination($productData['product_id'], $tempData);

        return $variantData;
    }

    public function combinations($arrays, $i = 0) 
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = self::combinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ? 
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }

    public function saveCombination($index, $combinations)
    {
        $filePath = '/opt/lampp/htdocs/integration/frontend/modules/walmart/test/barcodes.php';  

        $storedData = [];
        if (file_exists($filePath)) {
            $storedData = require $filePath;

            $storedData = is_array($storedData) ? $storedData : [];
        }

        $storedData[$index] = $combinations;

        file_put_contents($filePath, '<?php return $arr = ' . var_export($storedData, true) . ';');
    }

    //returns weight in lb
    public function getProductWeight($productData, $weight)
    {
        $convertedWt = 1;
        $unit = $productData['weight_class__(1=kilogram,2=gram,6=ounce,pound=5)'];

        switch ($unit) {
            case '1':
                //kilogram
                $convertedWt = round($weight * 2.2, 2);
                break;
            
            case '2':
                //gram
                $convertedWt =  round($weight/453.6, 2);
                break;

            case '6':
                //ounce
                $convertedWt = round($weight/35.274, 2);
                break;

            case '5':
                $convertedWt = round($weight/16, 2);
                //pound
                break;
        }

        return ['unit'=>'lb', 'value'=>$convertedWt];
    }

    public function prepareMPItem($product, $categ, $xml)
    {        
        $processMode = 'CREATE';

        if($product['type'] == 'simple')
        {
            $MPItem = $xml->createElement("MPItem");

                $processMode = $xml->createElement("processMode", $processMode);
                $MPItem->appendChild($processMode);

                $sku = $xml->createElement("sku", $product['sku']);
                $MPItem->appendChild($sku);

                $productIdentifiers = $xml->createElement("productIdentifiers");
                    $productIdentifier = $xml->createElement("productIdentifier");
                        $productIdType = $xml->createElement("productIdType", $product['id_type']);
                        $productIdentifier->appendChild($productIdType);
                        $productId = $xml->createElement("productId", $product['id_value']);
                        $productIdentifier->appendChild($productId);
                    $productIdentifiers->appendChild($productIdentifier);
                $MPItem->appendChild($productIdentifiers);

                $MPProduct = $xml->createElement("MPProduct");

                    $productName = $xml->createElement("productName");
                        $productNameValue = $xml->createCDATASection($product['product_name']);
                        $productName->appendChild($productNameValue);
                    $MPProduct->appendChild($productName);

                    $category = $xml->createElement("category");
                        $category1 = $xml->createElement($categ[0]);
                            
                            $category2 = $xml->createElement($categ[1]);
                
                                $shortDescription = $xml->createElement("shortDescription");
                                    //$shortDescriptionValue = $xml->createTextNode('<![CDATA['. $product['description']. ']]>');
                                    $shortDescriptionValue = $xml->createCDATASection($product['description']);
                                    $shortDescription->appendChild($shortDescriptionValue);
                                $category2->appendChild($shortDescription);

                                $brand = $xml->createElement("brand", $product['brand']);
                                $category2->appendChild($brand);

                                $hasIngredientList = $xml->createElement("hasIngredientList", "No");
                                $category2->appendChild($hasIngredientList);

                                $mainImageUrl = $xml->createElement("mainImageUrl", $product['main_image']);
                                $category2->appendChild($mainImageUrl);

                                if(count($product['secondary_images']))
                                {
                                    $productSecondaryImageURL = $xml->createElement("productSecondaryImageURL");
                                    foreach ($product['secondary_images'] as $secondary_image) {
                                        $productSecondaryImageURLValue = $xml->createElement("productSecondaryImageURLValue", $secondary_image);
                                        $productSecondaryImageURL->appendChild($productSecondaryImageURLValue);
                                    }
                                    $category2->appendChild($productSecondaryImageURL);
                                }

                            $category1->appendChild($category2);
                        $category->appendChild($category1);
                    $MPProduct->appendChild($category);
                $MPItem->appendChild($MPProduct);


                $MPOffer = $xml->createElement("MPOffer");

                    $price = $xml->createElement("price", $product['price']);
                    $MPOffer->appendChild($price);

                    $ShippingWeight = $xml->createElement("ShippingWeight");
                        $measure = $xml->createElement("measure", $product['weight']['value']);
                        $ShippingWeight->appendChild($measure);
                        $unit = $xml->createElement("unit", $product['weight']['unit']);
                        $ShippingWeight->appendChild($unit);
                    $MPOffer->appendChild($ShippingWeight);

                    $ProductTaxCode = $xml->createElement("ProductTaxCode", $product['taxcode']);
                    $MPOffer->appendChild($ProductTaxCode);
                
                $MPItem->appendChild($MPOffer);
        }
        else
        {
            $MPItem = $xml->createElement("MPItem");

                $processMode = $xml->createElement("processMode", $processMode);
                $MPItem->appendChild($processMode);

                $sku = $xml->createElement("sku", $product['sku']);
                $MPItem->appendChild($sku);

                $productIdentifiers = $xml->createElement("productIdentifiers");
                    $productIdentifier = $xml->createElement("productIdentifier");
                        $productIdType = $xml->createElement("productIdType", $product['id_type']);
                        $productIdentifier->appendChild($productIdType);
                        $productId = $xml->createElement("productId", $product['id_value']);
                        $productIdentifier->appendChild($productId);
                    $productIdentifiers->appendChild($productIdentifier);
                $MPItem->appendChild($productIdentifiers);

                $MPProduct = $xml->createElement("MPProduct");

                    $productName = $xml->createElement("productName");
                        $productNameValue = $xml->createCDATASection($product['product_name']);
                        $productName->appendChild($productNameValue);
                    $MPProduct->appendChild($productName);

                    $category = $xml->createElement("category");
                        $category1 = $xml->createElement($categ[0]);
                            
                            $category2 = $xml->createElement($categ[1]);
                                
                                if(count($product['options']))
                                {
                                    $variantAttributeNames = $xml->createElement("variantAttributeNames");
                                    foreach ($product['options'] as $key => $option) 
                                    {   //['Weight' => 'size', 'Flavor' => 'flavor'];mapping
                                        $variantAttributeName = $xml->createElement("variantAttributeName", $product['mapping'][$key]);
                                        $variantAttributeNames->appendChild($variantAttributeName);
                                    }

                                    $category2->appendChild($variantAttributeNames);

                                    $variantGroupId = $xml->createElement("variantGroupId", $product['group_id']);
                                    $category2->appendChild($variantGroupId);
                                }

                                foreach ($product['mapping'] as $key => $value) {
                                    if(isset($product['options'][$key]))
                                    {
                                        $attribute = $xml->createElement($value, $product['options'][$key]);
                                        $category2->appendChild($attribute);
                                    }
                                }

                                $shortDescription = $xml->createElement("shortDescription");
                                    //$shortDescriptionValue = $xml->createTextNode('<![CDATA['. $product['description']. ']]>');
                                    $shortDescriptionValue = $xml->createCDATASection($product['description']);
                                    $shortDescription->appendChild($shortDescriptionValue);
                                $category2->appendChild($shortDescription);

                                $brand = $xml->createElement("brand", $product['brand']);
                                $category2->appendChild($brand);

                                $hasIngredientList = $xml->createElement("hasIngredientList", "No");
                                $category2->appendChild($hasIngredientList);

                                $mainImageUrl = $xml->createElement("mainImageUrl", $product['main_image']);
                                $category2->appendChild($mainImageUrl);

                                if(count($product['secondary_images']))
                                {
                                    $productSecondaryImageURL = $xml->createElement("productSecondaryImageURL");
                                    foreach ($product['secondary_images'] as $secondary_image) {
                                        $productSecondaryImageURLValue = $xml->createElement("productSecondaryImageURLValue", $secondary_image);
                                        $productSecondaryImageURL->appendChild($productSecondaryImageURLValue);
                                    }
                                    $category2->appendChild($productSecondaryImageURL);
                                }

                            $category1->appendChild($category2);
                        $category->appendChild($category1);
                    $MPProduct->appendChild($category);
                $MPItem->appendChild($MPProduct);


                $MPOffer = $xml->createElement("MPOffer");

                    $price = $xml->createElement("price", $product['price']);
                    $MPOffer->appendChild($price);

                    $ShippingWeight = $xml->createElement("ShippingWeight");
                        $measure = $xml->createElement("measure", $product['weight']['value']);
                        $ShippingWeight->appendChild($measure);
                        $unit = $xml->createElement("unit", $product['weight']['unit']);
                        $ShippingWeight->appendChild($unit);
                    $MPOffer->appendChild($ShippingWeight);

                    $ProductTaxCode = $xml->createElement("ProductTaxCode", $product['taxcode']);
                    $MPOffer->appendChild($ProductTaxCode);
                
                $MPItem->appendChild($MPOffer);
        }

        return $MPItem;
    }

    public function getBarcode($model, $flavour, $weight)
    {
        $model = strtolower($model);
        $flavour = strtolower($flavour);

        $index = strpos($weight, 'lbs');
        $weight = trim(substr($weight, 0, $index));

        // ExcelFile($filename, $encoding);
        $data = new Reader();


        // Set output Encoding.
        $data->setOutputEncoding('CP1251');
        $data->read('/home/cedcoss/Desktop/UPC-12 Barcodes List.xls');

        error_reporting(E_ALL ^ E_NOTICE);

        for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
            
            if($model == trim($data->sheets[0]['cells'][$i][3]) && $flavour == trim($data->sheets[0]['cells'][$i][4]) && $weight == trim($data->sheets[0]['cells'][$i][2])) 
            {
                    return ltrim($data->sheets[0]['cells'][$i][1],"'");
                    break;
            }
        }
        return false;
    }

    public function prepareData($feedType, $productData, $xml, &$InventoryFeed)
    {
        if($feedType == 'inventory')
        {
            if($productData['sku'] != '' && $productData['mpn'] != '')
            return false;

            if($productData['status_(1=enabled,_0=_disabled)'] == '0')
                return false;

            if($productData['model'] == '')
                return false;

            $uploadedProducts = [];
            if($variants=$this->getProductVariantOptions($productData))
            {
                foreach ($variants as $variant) 
                {   
                    $barcode = self::getBarcode($productData['model'], $variant['options']['Flavor'], isset($variant['options']['Weight'])?$variant['options']['Weight']:$variant['options']['Weight & servings']);

                    if($barcode === false)
                    {
                        return false;
                    }

                    $qty = $variant['qty'];
                    $proSku = 'SKU-'.$productData['product_id'].'-'.implode('~', $variant['options']);
                    
                    $inventory =  $xml->createElement("inventory");

                        $sku =  $xml->createElement("sku", $proSku);

                        $inventory->appendChild($sku);

                        $quantity = $xml->createElement('quantity');

                            $unit = $xml->createElement('unit', "EACH");
                            $quantity->appendChild($unit);

                            $amount = $xml->createElement('amount', $qty);
                            $quantity->appendChild($amount);

                        $inventory->appendChild($quantity);

                        $fulfillmentLagTime = $xml->createElement("fulfillmentLagTime", 1);
                        $inventory->appendChild($fulfillmentLagTime);

                    $InventoryFeed->appendChild($inventory);

                    $uploadedProducts[] = ['product_id'=>$productData['product_id'], 'variant'=>$variant['options']];
                }
            }
            else
            {
                $qty = $productData['quantity'];
                
                $proSku = empty($productData['sku']) ? 'SKU-'.$productData['product_id'] : $productData['sku'];
               
                $inventory =  $xml->createElement("inventory");

                    $sku =  $xml->createElement("sku", $proSku);
                    
                    $inventory->appendChild($sku);

                    $quantity = $xml->createElement('quantity');

                        $unit = $xml->createElement('unit', "EACH");
                        $quantity->appendChild($unit);

                        $amount = $xml->createElement('amount', $qty);
                        $quantity->appendChild($amount);

                    $inventory->appendChild($quantity);

                $InventoryFeed->appendChild($inventory);
            }

            if(count($uploadedProducts)) {
                var_dump($uploadedProducts);
            }
        }
    }
}
