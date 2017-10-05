<?php
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\WalmartProductValidate;
use Yii;
use yii\filters\VerbFilter;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Category;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\AttributeMap;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartProductSearch;
use frontend\modules\walmart\models\WalmartProductVariants;

class ProductController extends WalmartmainController
{
    protected $walmartHelper;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        if (parent::beforeAction($action)) {
            $this->walmartHelper = new Walmartapi(API_USER, API_PASSWORD);
            return true;
        }
    }

    /**
     * Lists all WalmartProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
        $merchant_id = MERCHANT_ID;
        
        $searchModel = new WalmartProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($merchant_id == 484) {
            $dataProvider->pagination->pageSize = 100;
        }

        /*$query = "SELECT `product_id` FROM `walmart_product` WHERE `merchant_id` = '{$merchant_id}' LIMIT 0,1";
        $productColl = Data::sqlRecords($query, 'one', 'select');
        if (!$productColl) {
            Data::importWalmartProduct($merchant_id);
        }*/

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'merchant_id' => $merchant_id
        ]);
    }

    /**
     * Product edit for simple and variant products
     *
     */
    public function actionEditdata()
    {
        $this->layout = 'main2';
        $productId = trim(Yii::$app->request->post('id'));
        $merchant_id = trim(Yii::$app->request->post('merchant_id'));

        $model = WalmartProduct::find()->joinWith('jet_product')->where(['walmart_product.id' => $productId])->one();

        $parent_category = $model->parent_category;
        $child_category = $model->category;
        $category_path = $parent_category . '->' . $child_category;

        $_requiredAttributes = [];
        $_unitAttributes = [];
        $_variantAttributes = [];
        $_conditionalAttributes = [];
        $_advancedAttributes = [];
        $_attributeOptions = [];

        //Prepare Required Attributes
        $requiredSubCategoryAttributes = Category::getSubCategoryAttributes($parent_category, $child_category, true);
        foreach ($requiredSubCategoryAttributes as $value) {
            $attributeCode = Category::getAttributeCode($value);

            foreach ($attributeCode as $item) {
                $_requiredAttributes[] = $item;
            }

            $options = Category::getAttributeOptions($value);
            $_attributeOptions = array_merge($_attributeOptions, $options);
        }

        //Prepare Unit Attributes
        $categoryAttributes = Category::getCategoryAttributes($parent_category, false);
        foreach ($categoryAttributes as $categoryAttribute) 
        {
            if(isset($categoryAttribute['child']))
            {
                foreach ($categoryAttribute['child'] as $childAttribute) 
                {
                    if ($childAttribute['name'] == 'unit') {
                        $_unitAttributes[$categoryAttribute['name']] = $categoryAttribute['name'] . AttributeMap::ATTRIBUTE_PATH_SEPERATOR . $childAttribute['name'];
                        
                        $options = Category::getAttributeOptions($categoryAttribute);
                        $_attributeOptions = array_merge($_attributeOptions, $options);
                    }
                }
            }
        }

        //Prepare Variant Attributes
        $categoryVariantAttributes = Category::getCategoryVariantAttributes($parent_category);
        foreach ($categoryVariantAttributes as $values) 
        {
            if (array_key_exists($values, $categoryAttributes)) 
            {
                $attribute_codes = Category::getAttributeCode($categoryAttributes[$values]);
                $name = $categoryAttributes[$values]['name'];
                foreach ($attribute_codes as $attrCode) {
                    if(!in_array($attrCode, $_unitAttributes)) {
                        $_variantAttributes[$name] = $attrCode;
                    }
                }

                $attributeOptions = Category::getAttributeOptions($categoryAttributes[$values]);
                $_attributeOptions = array_merge($_attributeOptions, $attributeOptions);
            }
        }

        //Prepare Conditionally Required Attributes
        $requiredLevel = Category::REQUIREDLEVEL_CONDITIONALLY_REQUIRED;
        $conditionallyRequiredAttributes = Category::getFilteredViaRequiredLevelAttrs($parent_category, $child_category, $requiredLevel);
        foreach ($conditionallyRequiredAttributes as $attrCode => $attrStructure) 
        {
            $flag = false;
            $attributeCode = Category::getAttributeCode($attrStructure);
            foreach ($attributeCode as $code) 
            {
                if(isset($attrStructure['conditionalAttributes']))
                {
                    $conditions = [];
                    foreach ($attrStructure['conditionalAttributes'] as $condition) 
                    {
                        $name = $condition['name'];
                        //get structure of the condition attribute to prepare the attribute code
                        //$contionAttrStructure = Category::getAttributeStructure($parent_category, $child_category, $name);
                        if(isset($requiredSubCategoryAttributes[$name])) 
                        {
                            $contionAttrStructure = $requiredSubCategoryAttributes[$name];
                            $attrcodes = Category::getAttributeCode($contionAttrStructure);
                            $attrcode = current($attrcodes);

                            $conditions[] = ['attribute' => $attrcode, 'value' => $condition['value']];
                        }
                    }

                    if(count($conditions)) {
                        $_conditionalAttributes[$code] = ["code" => $code, "conditions" => $conditions];
                        $flag = true;
                    }
                }
            }

            if($flag) {
                $options = Category::getAttributeOptions($attrStructure);
                $_attributeOptions = array_merge($_attributeOptions, $options);
            }
        }


        //Prepare Optional and Recommended Attributes
        $requiredLevel = [Category::REQUIREDLEVEL_OPTIONAL, Category::REQUIREDLEVEL_RECOMMENDED];
        $conditionallyRequiredAttributes = Category::getFilteredViaRequiredLevelAttrs($parent_category, $child_category, $requiredLevel);
        foreach ($conditionallyRequiredAttributes as $attrCode => $attrStructure) 
        {
            $attributeCode = Category::getAttributeCode($attrStructure);

            foreach ($attributeCode as $item) {
                $_advancedAttributes[] = $item;
            }

            $options = Category::getAttributeOptions($attrStructure);
            $_attributeOptions = array_merge($_attributeOptions, $options);
        }

        //print_r($_advancedAttributes);die;
        /*print_r($_requiredAttributes);
        print_r($_unitAttributes);
        print_r($_variantAttributes);
        print_r($_conditionalAttributes);
        print_r($_attributeOptions);die;*/
        $session = Yii::$app->session;
        $productData = [
            'model' => $model,
            'category_path' => $category_path,
            'requiredAttributes' => $_requiredAttributes,
            'unitAttributes' => $_unitAttributes, 
            'variantAttributes' => $_variantAttributes, 
            'conditionalAttributes' => $_conditionalAttributes, 
            'advancedAttributes' => $_advancedAttributes,
            'attributeOptions' => $_attributeOptions,
            'merchant_id' => $merchant_id
        ];

        $session_key = 'product' . $productId;
        $session->set($session_key, $productData);
        $session->close();

        $html = $this->render('editdata', ['id' => $productId, 'model' => $model], true);
        
        return $html;
    }

    public function actionRenderCategoryTab()
    {
        $this->layout = "main2";

        $session = Yii::$app->session;

        $html = '';

        $id = Yii::$app->request->post('id', false);

        if ($id) 
        {
            $session_key = 'product' . $id;
            $product = $session[$session_key];

            $merchant_id =  $product['merchant_id'];

            $model = $product['model'];
            $category_path = $product['category_path'];
            $_requiredAttributes = $product['requiredAttributes'];
            $_unitAttributes = $product['unitAttributes'];
            $_variantAttributes = $product['variantAttributes'];
            $_conditionalAttributes = $product['conditionalAttributes'];
            $_advancedAttributes = $product['advancedAttributes'];
            $_attributeOptions = $product['attributeOptions'];

            $html = $this->render('category_tab', ['model' => $model, 'category_path' => $category_path, 'requiredAttributes' => $_requiredAttributes, 'unitAttributes' => $_unitAttributes, 'variantAttributes' => $_variantAttributes, 'conditionalAttributes' => $_conditionalAttributes, 'advancedAttributes' => $_advancedAttributes, 'attributeOptions' => $_attributeOptions, 'merchant_id' => $merchant_id], true);
        }

        return json_encode(['html' => $html]);
    }

    /**
     * Product ajax update
     * get product data and save records in database
     * @param integer $id
     */
    public function actionUpdateajax($id)
    {
        $model = WalmartProduct::find()->joinWith('jet_product')->where(['walmart_product.product_id' => $id])->one();
        $notice = null;
        $postData = Yii::$app->request->post("WalmartProduct", false);
        if($postData && $model)
        {
            $merchant_id = $model->merchant_id;

            $validate = self::validateFormData($postData);
            if($validate['success'] === false)
            {
                return json_encode(['error'=>$validate['message']]);
            }

            if($model->jet_product->type == "variants") 
            {
                //walmart_option_attributes, walmart_optional_attributes, option_prices, option_qtys
                $validateVariant = self::validateVariantProduct($postData);
                if(!$validateVariant['success'] && !isset($validateVariant['notice'])) 
                {
                    return json_encode(['error' => $validateVariant['message']]);
                } 
                else 
                {
                    if(isset($validateVariant['notice'])) {
                        $notice = implode('<br>', $validateVariant['message']);
                    }

                    foreach ($postData['product_variants'] as $variantId => $variantData) 
                    {
                        if(isset($validateVariant['message'][$variantData['variant_sku']])) {
                            continue;
                        }

                        if($model->jet_product->sku == $variantData['variant_sku'])
                        {
                            $model->product_price = $variantData['variant_price'];
                            $model->product_qty = $variantData['variant_inventory'];

                            //set upc in jetproduct
                            $model->jet_product->upc = $variantData['variant_upc'];
                        }

                        $walmartOptionAttributes = [];
                        foreach ($variantData['option_values'] as $shopifyOptionId => $shopifyOptionVal) 
                        {
                            if(isset($postData['shopify_options_mapping'][$shopifyOptionId]['walmart_attribute'])) {
                                $mappedWalmartAttr = $postData['shopify_options_mapping'][$shopifyOptionId]['walmart_attribute'];
                                $walmartOptionAttributes[$mappedWalmartAttr] = $shopifyOptionVal;
                            }
                        }

                        $walmartOptionAttributes = addslashes(json_encode($walmartOptionAttributes));
                        $query = "UPDATE `walmart_product_variants` SET `walmart_option_attributes` = '{$walmartOptionAttributes}', `walmart_optional_attributes`=NULL, `option_prices`='{$variantData['variant_price']}', `option_qtys`='{$variantData['variant_inventory']}' WHERE `option_id` = {$variantId}";
                        Data::sqlRecords($query, null, 'update');

                        $query = "UPDATE `jet_product_variants` SET `option_unique_id`='{$variantData['variant_upc']}' WHERE `option_id` = {$variantId}";
                        Data::sqlRecords($query, null, 'update');
                    }

                    $optionMapping = [];
                    foreach ($postData['shopify_options_mapping'] as $shopifyOptionId => $mappingData) {
                        $optionMapping[$mappingData['shopify_option_name']] = [$mappingData['walmart_attribute']];
                    }
                    $model->walmart_attributes = json_encode($optionMapping);
                }
            }
            else
            {
                $validateSimple = self::validateSimpleProduct($postData);
                if(!$validateSimple['success'] && !isset($validateSimple['notice'])) 
                {
                    return json_encode(['error' => $validateSimple['message']]);
                } 
                else 
                {

                    $model->product_price = isset($postData['product_price']) ? $postData['product_price'] : null;
                    $model->product_qty = isset($postData['product_inventory']) ? $postData['product_inventory'] : null;

                    if(isset($validateSimple['notice'])) {
                        $notice = $validateSimple['message'];
                    } else {
                        //set upc in jetproduct
                        $model->jet_product->upc = $postData['product_upc'];
                    }
                }
            }
            //tax_code, long_description, short_description, self_description, common_attributes, walmart_optional_attributes, sku_override, product_id_override, fulfillment_lag_time, product_title, product_price, product_qty, shipping_exceptions

            $model->tax_code = trim(isset($postData['product_tax']) ? $postData['product_tax'] : '');

            // for checking product description is UTF-8 encoded.
            if (mb_detect_encoding($postData['long_description']) !== 'UTF-8') {
                $postData['long_description'] = utf8_encode($postData['long_description']);
            }

            $model->long_description = trim(isset($postData['long_description']) ? $postData['long_description'] : '');
            $model->short_description = trim(isset($postData['short_description']) ? $postData['short_description'] : '');
            $model->self_description = trim(isset($postData['self_description']) ? $postData['self_description'] : '');

            if(isset($postData['common_attributes'])) {
                $common_attr_data = [];
                foreach ($postData['common_attributes'] as $key => $value) {
                    $value = trim($value);
                    if (!empty($value))
                        $common_attr_data[$key] = $value;
                }

                if (count($common_attr_data))
                    $common_attr = json_encode($common_attr_data);
                else
                    $common_attr = null;

                $model->common_attributes = $common_attr;
            } else {
                $model->common_attributes = null;
            }

            if(isset($postData['optional_attributes'])) {
                $optionalAttributes = $postData['optional_attributes'];
                if(is_array($optionalAttributes)) {
                    $optionalAttributes = json_encode($optionalAttributes);
                }
                $model->walmart_optional_attributes = $optionalAttributes;
            } else {
                $model->walmart_optional_attributes = null;
            }

            $model->sku_override = isset($postData['sku_override']) ? $postData['sku_override'] : 0;

            $model->product_id_override = isset($postData['product_id_override']) ? $postData['product_id_override'] : 0;

            $model->fulfillment_lag_time = isset($postData['fulfillment_lag_time']) ? $postData['fulfillment_lag_time'] : 1;

            $model->product_title = trim(isset($postData['product_title']) ? $postData['product_title'] : '');

            $model->shipping_exceptions = isset($postData['exceptions']) ? json_encode($postData['exceptions']) : json_encode([]);

            $model->jet_product->save(false);
            $model->save(false);

        } 
        else 
        {
            return json_encode(['error' => 'No data submitted.']);
        }
        
        if(!is_null($notice)) {
            return json_encode(['success' => 'Product information has been saved successfully.', 'error'=>$notice]);
        } else {
            return json_encode(['success' => 'Product information has been saved successfully.']);
        }
    }

    public function validateFormData($postData)
    {
        if(isset($postData['product_tax']))
        {
            $taxcode = $postData['product_tax'];
            if(strlen($taxcode) != 7 || !is_numeric($taxcode))
            {
                return ['success'=>false, 'message'=>"Invalid 'Product Tax Code'."];
            }
        }
        else {
            return ['success'=>false, 'message'=>"'Product Tax Code' is required."];
        }

        if(isset($postData['sku_override']) && isset($postData['product_id_override']))
        {
            $skuOverride = $postData['sku_override'];
            $idOverride = $postData['product_id_override'];

            if($skuOverride == '1' && $idOverride == '1')
            {
                return ['success'=>false, 'message'=>"Either 'Sku Override' OR 'Product Id Override' can be set to 'YES' at a time."];
            }
        }

        if(isset($postData['fulfillment_lag_time']))
        {
            $fulfillmentLagTime = $postData['fulfillment_lag_time'];
            if(!is_numeric($fulfillmentLagTime))
            {
                return ['success'=>false, 'message'=>"Invalid 'Fulfillment Lag Time'."];
            }
        }
        else {
            return ['success'=>false, 'message'=>"'Fulfillment Lag Time' is required."];
        }

        if(isset($postData['category_id'])) {
            if(trim($postData['category_id']) == '') {
                return ['success'=>false, 'message'=>"Walmart Category not mapped."];
            }
        } else {
            return ['success'=>false, 'message'=>"Walmart Category not mapped."];
        }

        if(isset($postData['parent_category_id'])) {
            if(trim($postData['parent_category_id']) == '') {
                return ['success'=>false, 'message'=>"Walmart Category not mapped."];
            }
        } else {
            return ['success'=>false, 'message'=>"Walmart Category not mapped."];
        }

        if(isset($postData['common_attributes']))
        {
            $common_attributes = $postData['common_attributes'];
            $parent_category = $postData['parent_category_id'];
            $child_category = $postData['category_id'];

            $validateCommonAttr = self::validateCommonAttributes($common_attributes, $parent_category, $child_category);
            if(!$validateCommonAttr['success']) {
                return ['success'=>false, 'message'=>$validateCommonAttr['message']];
            }
        }

        return ['success'=>true];
    }

    function validateCommonAttributes($commonAttributes, $parentCategory, $childCategory=null)
    {
        $categoryAttributes = Category::getCategoryAttributes($parentCategory, false);

        $errorAttrs = [];
        foreach ($commonAttributes as $attrCode => $attrValue) {
            if(trim($attrValue) == '') {
                $errorAttrs[] = "value of '".$attrCode."' is invalid.";
            }
            else {
                $attrCodeExplode = explode(AttributeMap::ATTRIBUTE_PATH_SEPERATOR, $attrCode);
                if(count($attrCodeExplode) == 1)
                {
                    if(isset($categoryAttributes[$attrCodeExplode[0]]))
                    {
                        $attributeStructure = $categoryAttributes[$attrCodeExplode[0]];
                        
                        $validateValue = self::validateValueDataType($attrValue, $attributeStructure);
                        if(!$validateValue['success']) {
                            $message = sprintf($validateValue['message'], $attrCode);
                            //return ['success'=>false, 'message'=>$message];
                            $errorAttrs[] = $message;
                        }
                    }
                    else
                    {
                        //return ['success'=>false, 'message'=>"Invalid attribute '".$attrCode."'"];
                        $errorAttrs[] = "Invalid attribute '".$attrCode."'";
                    }
                }
                elseif(count($attrCodeExplode) == 2)
                {
                    if(isset($categoryAttributes[$attrCodeExplode[0]]))
                    {
                        $attributeStructure = $categoryAttributes[$attrCodeExplode[0]];
                        if(isset($attributeStructure['child']))
                        {
                            $childStructure = false;
                            foreach ($attributeStructure['child'] as $child) {
                                if($child['name'] == $attrCodeExplode[1]) {
                                    $childStructure = $child;
                                    break;
                                }
                            }

                            if($childStructure)
                            {
                                $validateValue = self::validateValueDataType($attrValue, $childStructure);
                                if(!$validateValue['success']) {
                                    $message = sprintf($validateValue['message'], $attrCode);
                                    //return ['success'=>false, 'message'=>$message];
                                    $errorAttrs[] = $message;
                                }
                            }
                            else
                            {
                                //return ['success'=>false, 'message'=>"Invalid attribute '".$attrCode."'"];
                                $errorAttrs[] = "Invalid attribute '".$attrCode."'";
                            }
                        }
                    }
                    else
                    {
                        //return ['success'=>false, 'message'=>"Invalid attribute '".$attrCode."'"];
                        $errorAttrs[] = "Invalid attribute '".$attrCode."'";
                    }
                }
                elseif(count($attrCodeExplode) == 3)
                {
                    if(isset($categoryAttributes[$attrCodeExplode[0]]))
                    {
                        $attributeStructure = $categoryAttributes[$attrCodeExplode[0]];
                        if(isset($attributeStructure['child']))
                        {
                            $childStructure = false;
                            foreach ($attributeStructure['child'] as $child) {
                                if($child['name'] == $attrCodeExplode[1]) {
                                    $childStructure = $child;
                                    break;
                                }
                            }

                            if($childStructure)
                            {
                                if(isset($childStructure['child']))
                                {
                                    $subChildStructure = false;
                                    foreach ($childStructure['child'] as $subChild) {
                                        if($subChild['name'] == $attrCodeExplode[2]) {
                                            $subChildStructure = $subChild;
                                            break;
                                        }
                                    }

                                    if($subChildStructure)
                                    {
                                        $validateValue = self::validateValueDataType($attrValue, $subChildStructure);
                                        if(!$validateValue['success']) {
                                            $message = sprintf($validateValue['message'], $attrCode);
                                            //return ['success'=>false, 'message'=>$message];
                                            $errorAttrs[] = $message;
                                        }
                                    }
                                    else
                                    {
                                        //return ['success'=>false, 'message'=>"Invalid attribute '".$attrCode."'"];
                                        $errorAttrs[] = "Invalid attribute '".$attrCode."'";
                                    }
                                }
                            }
                            else
                            {
                                //return ['success'=>false, 'message'=>"Invalid attribute '".$attrCode."'"];
                                $errorAttrs[] = "Invalid attribute '".$attrCode."'";
                            }
                        }
                    }
                    else
                    {
                        //return ['success'=>false, 'message'=>"Invalid attribute '".$attrCode."'"];
                        $errorAttrs[] = "Invalid attribute '".$attrCode."'";
                    }
                }
            }
        }

        if(count($errorAttrs)) {
            return ['success'=>false, 'message'=>implode('<br>', $errorAttrs)];
        }
        else {
            return ['success'=>true];
        }
    }

    function validateValueDataType($value, $attributeStructure)
    {
        $return = ['success'=>true];

        $dataType = isset($attributeStructure['dataType']) ? $attributeStructure['dataType'] : '';

        switch ($dataType) {
            case 'string':
                if(is_string($value))
                {
                    if(isset($attributeStructure['optionValues']))
                    {
                        $optionValues = $attributeStructure['optionValues'];
                        if(!in_array($value, $optionValues)) {
                            $return = ['success'=>false, 'message'=>"value of '%s' is not from given options."];
                            break;
                        }
                    }
                    elseif(isset($attributeStructure['maxLength']))
                    {
                        $maxLength = $attributeStructure['maxLength'];
                        if(strlen($value) > $maxLength) {
                            $return = ['success'=>false, 'message'=>"value of '%s' must be within $maxLength characters."];
                            break;
                        }
                    }
                }
                else
                {
                    $return = ['success'=>false, 'message'=>"value of '%s' must be a 'string'."];
                }
                break;

            case 'integer':
                if(is_numeric($value))
                {
                    if(isset($attributeStructure['optionValues']))
                    {
                        $optionValues = $attributeStructure['optionValues'];
                        if(!in_array($value, $optionValues)) {
                            $return = ['success'=>false, 'message'=>"value of '%s' is not from given options."];
                            break;
                        }
                    }
                    elseif(isset($attributeStructure['totalDigits']))
                    {
                        $totalDigits = $attributeStructure['totalDigits'];
                        if(strlen($value) > $totalDigits) {
                            $return = ['success'=>false, 'message'=>"total digits of '%s' must not be greater than $totalDigits."];
                            break;
                        }
                    }
                }
                else
                {
                    $return = ['success'=>false, 'message'=>"value of '%s' must be an 'integer'."];
                }
                break;

            case 'anyURI':
                if(!filter_var($value, FILTER_VALIDATE_URL)) 
                {
                    $return = ['success'=>false, 'message'=>"value of '%s' must be a 'url'."];
                }
                break;

            case 'decimal':
                if(is_numeric($value))
                {
                    if(isset($attributeStructure['optionValues']))
                    {
                        $optionValues = $attributeStructure['optionValues'];
                        if(!in_array($value, $optionValues)) {
                            $return = ['success'=>false, 'message'=>"value of '%s' is not from given options."];
                            break;
                        }
                    }
                    elseif(isset($attributeStructure['totalDigits']))
                    {
                        $totalDigits = $attributeStructure['totalDigits'];
                        if(strlen($value) > $totalDigits) {
                            $return = ['success'=>false, 'message'=>"total digits of '%s' must not be greater than $totalDigits."];
                            break;
                        }
                    }
                }
                else
                {
                    $return = ['success'=>false, 'message'=>"value of '%s' must a 'decimal'."];
                }
                break;
            
            default:
                if(isset($attributeStructure['optionValues']))
                {
                    $optionValues = $attributeStructure['optionValues'];
                    if(!in_array($value, $optionValues)) {
                        $return = ['success'=>false, 'message'=>"value of '%s' is not from given options."];
                        break;
                    }
                }
                break;
        }

        return $return;
    }

    public function validateSimpleProduct($postData)
    {
        if(isset($postData['product_price']))
        {
            $productPrice = $postData['product_price'];
            if(!is_numeric($productPrice))
            {
                return ['success'=>false, 'message'=>"Invalid 'Product Price'."];
            }
        }
        else {
            return ['success'=>false, 'message'=>"Invalid 'Product Price'."];
        }

        if(isset($postData['product_inventory']))
        {
            $productInventory = $postData['product_inventory'];
            if(!is_numeric($productInventory))
            {
                return ['success'=>false, 'message'=>"Invalid 'Product Quantity'."];
            }
        }
        else {
            return ['success'=>false, 'message'=>"Invalid 'Product Quantity'."];
        }

        if(isset($postData['product_upc']))
        {
            $upc = trim($postData['product_upc']);
            if(!self::validateBarcode($upc, $postData['category_id'], $postData['product_sku'], $postData['product_id'])) {
                return ['success'=>false, 'message'=>"Invalid 'Product Barcode'.", 'notice'=>true];
            }
        }
        else {
            return ['success'=>false, 'message'=>"Invalid 'Product Barcode'."];
        }

        return ['success'=>true];
    }

    public function validateVariantProduct($postData)
    {
        if(isset($postData['shopify_options_mapping']))
        {
            $optionMapFlag = true;
            foreach ($postData['shopify_options_mapping'] as $optionId => $mapping) {
                if($mapping['walmart_attribute'] == '') {
                    $optionMapFlag = false;
                    break;
                }
            }

            if(!$optionMapFlag) {
                return ['success'=>false, 'message'=>'Please map all shopify options with walmart attributes.'];
            }
        } else {
            return ['success'=>false, 'message'=>'shopify options not mapped with walmart attributes.'];
        }

        if(isset($postData['product_variants']))
        {
            $variantErrors = [];
            $variantNotices = [];
            foreach ($postData['product_variants'] as $variantId => $variantData) 
            {
                $variantSku = isset($variantData['variant_sku']) ? $variantData['variant_sku'] : '';
                $errors = [];

                if(isset($variantData['variant_price'])) {
                    if(!is_numeric($variantData['variant_price'])) {
                        $errors[] = "Invalid Price";
                    }
                } else {
                    $errors[] = "Invalid Price";
                }

                if(isset($variantData['variant_inventory'])) {
                    if(!is_numeric($variantData['variant_inventory'])) {
                        $errors[] = "Invalid Inventory";
                    }
                } else {
                    $errors[] = "Invalid Inventory";
                }

                if(isset($variantData['variant_upc'])) {
                    $variantUpc = $variantData['variant_upc'];
                    if(!self::validateBarcode($variantUpc, $postData['category_id'], $variantSku, $postData['product_id'])) {
                        if(count($errors)) {
                            $errors[] = "Invalid Barcode";
                        } else {
                            $variantNotices[$variantSku] = "<b>$variantSku</b> : Invalid Barcode";
                        }
                    }
                } else {
                    if(count($errors)) {
                        $errors[] = "Invalid Barcode";
                    } else {
                        $variantNotices[$variantSku] = "<b>$variantSku</b> : Invalid Barcode";
                    }
                }

                if(count($errors)) {
                    $variantErrors[] = '<b>' .$variantSku . "</b> : " . implode(', ', $errors);
                }
            }
        } else {
            return ['success'=>false, 'message'=>'variant data not provided.'];
        }

        if(count($variantErrors)) {
            return ['success'=>false, 'message'=>implode('<br>', $variantErrors)];
        }
        elseif(count($variantNotices)) {
            return ['success'=>false, 'message'=>$variantNotices, 'notice'=>true];
        }
        else {
            return ['success'=>true];
        }
    }

    public function validateBarcode($product_upc, $category_id, $sku, $product_id)
    {
        $skipCategory = ['JewelryCategory', 'Jewelry'];
        if (!in_array($category_id, $skipCategory)) 
        {
            if($product_upc == '' || !is_numeric($product_upc)) {
                return false;
            }

            $var = Data::validateUpc($product_upc);
            if(!$var) {
                return false;
            }

            $flag = Jetproductinfo::checkUpcVariantSimple($product_upc, $product_id, $sku);
            if($flag) {
                return false;
            }
        }

        return true;
    }
    /**
     * Product edit for simple and variant products
     *
     */
    public function actionAddSwatch()
    {
        $this->layout = 'main2';
        $productId = trim(Yii::$app->request->post('id'));
        $merchant_id = trim(Yii::$app->request->post('merchant_id'));

        $model = WalmartProduct::find()->joinWith('jet_product')->where(['walmart_product.id' => $productId])->one();

        $html = $this->render('addswatch', ['id' => $productId, 'model' => $model], true);
        
        return $html;
    }
    public function actionSaveswatch()
    {
        //print_r($_POST);die;
        $path = Yii::getAlias('@webroot').'/swatchimage/'.Yii::$app->user->identity->username.'/temp/';
        if(!isset($_POST['WalmartProduct']['product_variants'])){
            return json_encode(['error' => 'Product information has been saved successfully.']);
        }
        $shopify_option = array_values($_POST['WalmartProduct']['shopify_options_mapping']);
        //var_dump($_POST['WalmartProduct']['product_variants']);die;
        foreach ($_POST['WalmartProduct']['product_variants'] as $key => $value) {
            $array = [];
            $urls = array_values($value['url']);

            /*if(!file_exists(dirname($path).'/swatches/thumbnail')){
                $old = umask(0);
                mkdir(dirname($path).'/swatches/thumbnail',0777,true);
                umask($old);
            }*/
          /*  $fileName = array_values($value['name']);
            foreach ($fileName as $nameKey => $name) {
                    copy($path.$name, dirname($path).'/swatches/thumbnail/'.$name);
                    unlink($path.$name);*/
            foreach ($urls as $urlKey => $url) {
                if($url){
                    if($this->checkRemoteFile($url)){
                        $array["swatchImages->swatchImage->swatchVariantAttribute"][] = $shopify_option[$urlKey];
                        $array["swatchImages->swatchImage->swatchImageUrl"][] = $url;
                    }else{
                         return json_encode(['errors' => 'Something Went Wrong']);
                    }
                    if(count($array)>0){
                        Data::sqlRecords('UPDATE `walmart_product_variants` SET `walmart_optional_attributes`=\''.addslashes(json_encode($array)).'\' WHERE `option_id`="'.$value['variant_id'].'"',null,"update");
                    }
                    
                    
                }
                
            }
                    
            /*}*/
              
        }
        return json_encode(['success' => 'Product information has been saved successfully.']);
    }

    public function actionSaveDescription()
    {
        $description = Yii::$app->request->post('description', false);
        // for checking product description is UTF-8 encoded.
        if (mb_detect_encoding($description) !== 'UTF-8') {
            $description = utf8_encode($description);
        }
        $product_id = Yii::$app->request->post('product_id', false);
        if ($product_id && $description && is_numeric($product_id)) {
            $maxLength = WalmartProductValidate::MAX_LENGTH_LONG_DESCRIPTION;
            //htmlspecialchars($description,ENT_XHTML);
            $length = strlen($description);
            if ($length > $maxLength) {
                return json_encode(['error' => true, 'message' => 'Description Should be less than ' . WalmartProductValidate::MAX_LENGTH_LONG_DESCRIPTION . ' characters.']);
            } else {
                $query = "UPDATE `walmart_product` SET `long_description`='" . addslashes($description) . "' WHERE `product_id`='" . $product_id . "'";
                Data::sqlRecords($query, null, 'update');

                return json_encode(['success' => true, 'message' => 'Description saved successfully.']);
            }
        } else {
            return json_encode(['error' => true, 'message' => 'Please Provide Valid Data.']);
        }
    }

    public static function checkRemoteFile($url)
    {
        return true;
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $headers = get_headers($url);
        if (substr($headers[0], 9, 3) == '200') {
            return true;
        } else {
            return false;
        }
    }
}

