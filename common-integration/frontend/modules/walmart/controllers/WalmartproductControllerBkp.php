<?php
namespace frontend\modules\walmart\controllers;

use frontend\components\Jetapimerchant;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\Jetappdetails;
use frontend\modules\walmart\components\Jetproductinfo;
use frontend\modules\walmart\components\ShopifyClientHelper;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\WalmartCategory;
use frontend\modules\walmart\components\WalmartProduct as WalmartProductComponent;
use frontend\modules\walmart\models\JetProductVariants;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\models\WalmartProductSearch;
use frontend\modules\walmart\models\WalmartProductVariants;
use Webmozart\Expression\Selector\Count;
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
//cron files
use frontend\modules\walmart\components\Walmartappdetails;
use frontend\modules\walmart\components\WalmartPromoStatus;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\models\WalmartCronSchedule;

/**
 * WalmartproductController implements the CRUD actions for WalmartProduct model.
 */
class WalmartproductController extends WalmartmainController
{
    protected $connection;
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
            $this->walmartHelper = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
            return true;
        }
    }

    /**
     * Lists all WalmartProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        } else {
            $merchant_id = MERCHANT_ID;
            $modelU = "";
            $productPopup = 0;
            $UpdateRows = array();
            $countUpdate = 0;
            $query = "SELECT `id` FROM `insert_product` WHERE merchant_id='" . $merchant_id . "' LIMIT 1";
            $modelU = Data::sqlRecords($query, "one", "select");

            if (!$modelU) {
                $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
                $countUpload = 0;
                $countUpload = $sc->call('GET', '/admin/products/count.json', array('published_status' => 'published'));
                if (isset($countUpload['errors'])) {
                    //return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/walmart/site/logout', 302);
                    Yii::$app->session->setFlash('error', 'No Product Found.');
                    return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/walmart/site/index');
                }
                $productPopup = 1;
            }
            $searchModel = new WalmartProductSearch();
            //$dataProvider->pagination->pageSize=50;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            if ($merchant_id == 484) {
                $dataProvider->pagination->pageSize = 100;
            }

            if ($productPopup) {
                return $this->render('index', [
                    'productPopup' => $productPopup,
                    'countUpload' => $countUpload,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    //'countUpdate' => $countUpdate,
                ]);
            } else {
                //check product available in walmart_product
                $productColl = [];
                $query = "select product_id from `walmart_product` where merchant_id='" . $merchant_id . "'";
                $productColl = Data::sqlRecords($query, 'all', 'select');
                if (!$productColl) {
                    Data::importWalmartProduct($merchant_id);
                }
                //check category mapped
                return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'countUpdate' => $countUpdate,
                ]);
            }
        }
    }

    /**
     * Displays a single WalmartProduct model.
     * @param integer $id
     * @return mixed
     */

    public function actionView($id)
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new WalmartProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $model = new WalmartProduct();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing WalmartProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing WalmartProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the WalmartProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WalmartProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WalmartProduct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * import product from jet product to walmart table
     * configure walmart table
     */

    public function actionImportwalmart()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $model = JetProduct::find()->select('id,type')->where(['merchant_id' => MERCHANT_ID])->all();
        foreach ($model as $value) {
            $walmartModel = WalmartProduct::find()->where(['product_id' => $value['id']])->one();
            if (!$walmartModel) {
                $modelW = new WalmartProduct();
                $modelW->product_id = $value['id'];
                $modelW->merchant_id = MERCHANT_ID;
                $modelW->save(false);
            }
            if ($type = 'variants') {
                $modelVar = JetProductVariants::find()->select('option_id')->where(['product_id' => $value['id']])->all();
                foreach ($modelVar as $val) {
                    $walmartModelVar = WalmartProductVariants::find()->where(['option_id' => $val['option_id']])->one();
                    if (!$walmartModelVar) {
                        $modelvar = new WalmartProductVariants();
                        $modelvar->option_id = $val['option_id'];
                        $modelvar->merchant_id = MERCHANT_ID;
                        $modelvar->save(false);
                    }
                }
            }
        }
    }

    /**
     * Product edit for simple and variant products
     *
     */
    public function actionEditdata()
    {
        $this->layout = 'main2';
        $id = trim(Yii::$app->request->post('id'));
        $merchant_id = trim(Yii::$app->request->post('merchant_id'));
        $model = WalmartProduct::find()->joinWith('jet_product')->where(['walmart_product.id' => $id])->one();
        $Category = [];
        $category_path = "";
        $query = "SELECT category_id,parent_id,attributes,attribute_values,walmart_attributes,walmart_attribute_values FROM `walmart_category` WHERE category_id='" . $model->category . "' LIMIT 1";
        $Category = Data::sqlRecords($query, "one", "select");
        /*echo "<pre>";
        print_r($Category);
        die("<hr>");*/
        $parent_id = "";
        if (is_array($Category) && count($Category) > 0) {
            if ($Category['parent_id']) {
                $parent_id = $Category['parent_id'];
                //*$category_path=$Category['category_id'].' -> '.$Category['parent_id'];
                $category_path = $Category['parent_id'] . ' -> ' . $Category['category_id'];
            } else {
                //*$parent_id=$Category['parent_id'];
                //*$category_path=$Category['category_id'];
                $parent_id = $category_path = $Category['category_id'];
            }
        }
        $attributes = [];
        //$optional_attr=[];
        $requiredAttrValues = [];
        $optionalAttrValues = [];
        $required = [];
        $attributes = json_decode($Category['attributes'], true) ?: [];

        $common_required_attributes = array();
        $_attributes = [];
        //if(is_array($attributes) && count($attributes)>0)
        //{
        $result = WalmartCategory::getCategoryVariantAttributes($Category['category_id']);
        if (isset($result['common_attributes'])) {
            $common_attributes = $result['common_attributes'];
            $common_required_attributes = $common_attributes;
            unset($result['common_attributes']);
        }

        $attribute_values = [];
        if (isset($result['attribute_values'])) {
            $attribute_values = $result['attribute_values'];
            unset($result['attribute_values']);
        }

        $required = [];
        if (isset($result['required_attributes'])) {
            $required = $result['required_attributes'];
            unset($result['required_attributes']);
        }

        $unitAttributes = [];
        if (isset($result['unit_attributes'])) {
            $unitAttributes = $result['unit_attributes'];
            unset($result['unit_attributes']);
        }

        foreach ($attributes as $key => $value) {
            //$attr_id="";
            if (is_array($value)) {
                foreach ($value as $k => $val) {
                    /*if(!in_array($k, $result) || Data::checkNonVariantAttributes($val)) {
                            $common_required_attributes[] = $attributes[$key];
                            unset($attributes[$key]);
                        }*/
                    $index = implode('->', $val);
                    if (!isset($result[$index])) {
                        if (count($unitAttributes)) {
                            foreach ($unitAttributes as $unitAttributeKey => $unitAttributeVal) {
                                if (is_array($unitAttributeVal)) {
                                    $diff = array_diff($unitAttributeVal, $val);
                                    if (count($diff) != 0) {
                                        $common_required_attributes[] = $attributes[$key];
                                        unset($attributes[$key]);
                                        break;
                                    }
                                } else {
                                    $common_required_attributes[] = $attributes[$key];
                                    unset($attributes[$key]);
                                    break;
                                }
                            }
                        } else {
                            $common_required_attributes[] = $attributes[$key];
                            unset($attributes[$key]);
                        }
                    }
                }
            } else {
                if (!isset($result[$value])) {
                    if (count($unitAttributes)) {
                        foreach ($unitAttributes as $unitAttributeKey => $unitAttributeVal) {
                            if (!is_array($unitAttributeVal)) {
                                $diff = array_diff($unitAttributeVal, $val);
                                if ($unitAttributeVal != $value) {
                                    $common_required_attributes[] = $attributes[$key];
                                    unset($attributes[$key]);
                                    break;
                                }
                            } else {
                                $common_required_attributes[] = $attributes[$key];
                                unset($attributes[$key]);
                                break;
                            }
                        }
                    } else {
                        $common_required_attributes[] = $attributes[$key];
                        unset($attributes[$key]);
                    }
                }
                //$attr_id = $value;
            }
        }

        $_attributes = [];
        foreach ($result as $result_key => $result_value) {
            if (is_array($result_value)) {
                $key = reset($result_value);
                $_attributes[] = [$key => $result_value];
            } else
                $_attributes[] = $result_value;
        }
        //} else {
        //    $_attributes = [];
        //}

        //$optional_attr=explode(',',$Category['walmart_attributes']);
        $requiredAttrValues = json_decode($Category['attribute_values'], true) ?: [];
        $optionalAttrValues = json_decode($Category['walmart_attribute_values'], true) ?: [];

        /* code by himanshu start */
        if (count($requiredAttrValues)) {
            $requiredAttrValues_copy = $requiredAttrValues;
            $requiredAttrValues = [];
            foreach ($requiredAttrValues_copy as $attr_val) {
                if (is_array($attr_val) && count($attr_val)) {
                    $attr_val_key = key($attr_val);
                    $attr_val_value = reset($attr_val);
                    $requiredAttrValues[$attr_val_key] = $attr_val_value;
                }
            }
            $requiredAttrValues = array_merge($requiredAttrValues, $attribute_values);
        } else {
            foreach ($attribute_values as $attr_code => $attribute_value) {
                $requiredAttrValues[$attr_code] = $attribute_value;
            }
        }

        $session = Yii::$app->session;
        $productData = [
            'model' => $model,
            'category_path' => $category_path,
            'attributes' => $_attributes,
            'optional_attr' => []/*$optional_attr*/,
            'requiredAttrValues' => $requiredAttrValues,
            'optionalAttrValues' => $optionalAttrValues,
            'common_required_attributes' => $common_required_attributes,
            'required' => $required,
            'unit_attributes' => $unitAttributes,
            'category_data' => $Category
        ];
        $session_key = 'product' . $id;
        $session->set($session_key, $productData);
        $session->close();
        /* code by himanshu end */

        $html = $this->render('editdata', array('id' => $id, 'model' => $model, 'category_path' => $category_path, 'attributes' => $_attributes, 'optional_attr' => []/*$optional_attr*/, 'requiredAttrValues' => $requiredAttrValues, 'optionalAttrValues' => $optionalAttrValues, 'common_required_attributes' => $common_required_attributes, 'required' => $required), true);
        unset($connection);
        //unset($optional_attr);
        unset($attributes);
        return $html;
    }

    /* code by himanshu start */
    public function actionRenderCategoryTab()
    {
        $this->layout = "main2";

        $session = Yii::$app->session;

        $html = '';

        $id = Yii::$app->request->post('id');
        if ($id) {
            $session_key = 'product' . $id;
            $product = $session[$session_key];
            //var_dump($product);
            $model = $product['model'];
            $category_path = $product['category_path'];
            $attributes = $product['attributes'];
            $optional_attr = $product['optional_attr'];
            $requiredAttrValues = $product['requiredAttrValues'];
            $optionalAttrValues = $product['optionalAttrValues'];
            $common_required_attributes = $product['common_required_attributes'];
            $required = $product['required'];
            $unit_attributes = $product['unit_attributes'];
            $Category = $product['category_data'];

            $html = $this->render('category_tab', [
                'model' => $model,
                'category_path' => $category_path,
                'attributes' => $attributes,
                'optional_attr' => $optional_attr,
                'requiredAttrValues' => $requiredAttrValues,
                'optionalAttrValues' => $optionalAttrValues,
                'common_required_attributes' => $common_required_attributes,
                'required' => $required,
                'unit_attributes' => $unit_attributes,
                'category_data' => $Category
            ]);
        }
        return json_encode(['html' => $html]);
    }

    public function actionPromotions()
    {
        $this->layout = "main2";

        $session = Yii::$app->session;
        $post = Yii::$app->request->post();
        $query = "SELECT * FROM `walmart_promotional_price` WHERE `merchant_id`='{$post['merchant_id']}' AND `product_id`='{$post['product_id']}' AND `option_id`='{$post['option_id']}'";
        $promotions = Data::sqlRecords($query, "all", "select");

        echo $this->render('promotions', ['promotions' => $promotions, 'post' => $post]);
        //print_r($post);
    }

    public function actionPromotionSave()
    {
        $result = ['success' => 1];
        $error_count=0;
        try {
            $session = Yii::$app->session;
            $post = Yii::$app->request->post();

            foreach ($post['promotion']['orignal_price'] as $key => $price) {
                if (empty(trim($post['promotion']['effective_date'][$key])) || empty(trim($post['promotion']['expiration_date'][$key])) || !is_numeric($price) || !is_numeric($post['promotion']['special_price'][$key])) {
                    return json_encode(['error' => 'Either Data is Invalid or Blank!!']);
                }

                $to_delete = 0;
                if (isset($post['promotion']['to_delete'][$key]))
                    $to_delete = 1;
                $effective_date = Data::formatTime($post['promotion']['effective_date'][$key]);
                $expiration_date = Data::formatTime($post['promotion']['expiration_date'][$key]);

                if($effective_date < $expiration_date){

                    if (isset($post['promotion']['id']) && isset($post['promotion']['id'][$key])) {
                        $query = "UPDATE `walmart_promotional_price` SET `original_price`='{$price}',`special_price`='{$post['promotion']['special_price'][$key]}',`effective_date`='{$effective_date}',`expiration_date`='{$expiration_date}',`current_price_type`='{$post['promotion']['current_price_type'][$key]}',`to_delete`='{$to_delete}' WHERE id='{$post['promotion']['id'][$key]}' ";
                        Data::sqlRecords($query, "one", "update");
                    } else {
                        $pending = WalmartPromoStatus::PROMOTIONAL_PRICE_STATUS_PENDING;
                        $query = "INSERT INTO `walmart_promotional_price` (`product_id`,`option_id`,`sku`,`merchant_id`,`original_price`,`special_price`,`effective_date`,`expiration_date`,`current_price_type`,`to_delete`,`walmart_status`) VALUES('{$post['product_id']}','{$post['option_id']}','{$post['sku']}','{$post['merchant_id']}','{$post['promotion']['orignal_price'][$key]}','{$post['promotion']['special_price'][$key]}','{$effective_date}','{$expiration_date}','{$post['promotion']['current_price_type'][$key]}','{$to_delete}','{$pending}') ";
                        Data::sqlRecords($query, "one", "insert");
                    }

                }else{
                    $error_count = $error_count+1;
                }

            }
        } catch (Exception $e) {
            $result = ['error' => 1, 'msg' => $e->getMessage()];
        }
        echo json_encode($result);
        //die('hg');

    }

    /* code by himanshu end */
    /**
     * Product ajax update
     * get product data and save records in database
     * @param integer $id
     */
    public function actionUpdateajax($id)
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $connection = Yii::$app->getDb();
        $model = WalmartProduct::find()->joinWith('jet_product')->where(['walmart_product.product_id' => $id])->one();
        $data = array();
        $sku = $model->jet_product->sku;
        $merchant_id = $model->merchant_id;

        //print_r(Yii::$app->request->post());die;

        if (Yii::$app->request->post()) {
            /*-------------------newly added on 1 April starts----------------------------------*/
            $product_barcode = "";
            $product_sku = "";
            $product_id = "";
            $product_upc = "";
            $product_asin = "";
            $product_short = "";
            $product_self = "";
            $product_tax = "";
            $product_vendor = "";
            $return_status = [];
            $product_error = [];

            $product_id = $model->product_id;

            $variant_id = $model->jet_product->variant_id;
            $exceptions = isset($_POST['exceptions']) ? json_encode($_POST['exceptions']) : json_encode([]);
            $product_sku = $_POST['JetProduct']['sku'];
            $product_upc = trim($_POST['JetProduct']['upc']);
            //$product_mpn=trim($_POST['JetProduct']['mpn']);
            $product_tax = trim($_POST['JetProduct']['product_tax']);
            $product_vendor = trim($_POST['JetProduct']['vendor']);
            $product_short = trim($_POST['JetProduct']['short_description']);
            $product_self = trim($_POST['JetProduct']['self_description']);
            $category = "";
            $category = trim($_POST['JetProduct']['fulfillment_node']);
            if ($product_vendor == "") {
                $return_status['error'] = "Brand is required field.";
                return json_encode($return_status);
            }

            /*if($product_tax != '' && count($product_tax) != 7 )
            {
                $product_error['invalid_taxcode'][] = $product_sku;
            }*/

            if ($product_barcode == "") {
                $product_barcode = Jetproductinfo::checkUpcType($product_upc);
            }

            /* Code By Himanshu Start */
            if (Yii::$app->request->post('common_attributes', false)) {
                $common_attr_data = [];
                foreach (Yii::$app->request->post('common_attributes') as $key => $value) {
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


            $model->shipping_exceptions = $exceptions;
            if (isset($_POST['sku_override'])) {
                $sku_override = Yii::$app->request->post('sku_override');
                $model->sku_override = $sku_override;
            } else {
                $model->sku_override = 0;
            }

            if (isset($_POST['product_id_override'])) {
                $product_id_override = Yii::$app->request->post('product_id_override');
                $model->product_id_override = $product_id_override;
            } else {
                $model->product_id_override = 0;
            }

            if (isset($_POST['fulfillment_lag_time'])) {
                $lag_time = Yii::$app->request->post('fulfillment_lag_time');
                $model->fulfillment_lag_time = $lag_time;
            } else {
                $model->fulfillment_lag_time = 1;
            }
            /* Code By Himanshu End */

            if (Yii::$app->request->post('product-type') == 'variants') {

                $walmart_attr = array();
                $options = array();
                $new_options = array();
                $pro_attr = array();
                $walmart_attributes = array();
                $attributes_of_jet = array();
                $other_vari_opt = array();
                $common_attr = "";
                if (Yii::$app->request->post('jet_attributes')) {
                    $walmart_attributes = Yii::$app->request->post('jet_attributes');
                }

                if (Yii::$app->request->post('attributes_of_jet')) {

                }
                if (Yii::$app->request->post('jet_varients_opt')) {
                    //$product_error = [];
                    $other_vari_opt = Yii::$app->request->post('jet_varients_opt');
                    $er_msg = "";
                    $chek_flag = false;
                    if (is_array($other_vari_opt) && count($other_vari_opt) > 0) {

                        foreach ($other_vari_opt as $k_opt_id => $v_opt) {

                            $option_id = $k_opt_id;
                            $opt_upc = "";
                            $opt_asin = "";
                            $opt_mpn = "";
                            $option_sku = "";
                            $er_msg1 = "";
                            $opt_upc = trim($v_opt['upc']);
                            $option_sku = $v_opt['optionsku'];
                            $opt_barcode = "";
                            /*-------newly added on 1 April starts------------*/
                            if ($opt_barcode == "") {
                                $opt_barcode = Jetproductinfo::checkUpcType($opt_upc);
                            }
                            $upc_success_flag = true;
                            $mpn_success_flag = true;
                            $invalid_asin = false;
                            $invalid_upc = false;
                            $invalid_mpn = false;
                            $upc_error_msg = "";
                            $asin_success_flag = true;
                            $asin_error_msg = "";

                            /*
                            * validate upc
                            */
                            $category_id = trim($_POST['category_id']);
                            $skipCategory = ['Jewelry', 'Rings'];
                            if (!empty($category_id) && !in_array($category_id, $skipCategory)) {
                                if (isset($opt_upc) && !empty($opt_upc)) {
                                    $var = Data::validateUpc($opt_upc);
                                    if ($var == true) {
                                        $invalid_upc = false;
                                    } else {
                                        $invalid_upc = true;
                                    }
                                }

                                /*if (strlen($opt_upc) > 0) {
                                    list($upc_success_flag, $upc_error_msg) = Jetproductinfo::checkProductOptionBarcodeOnUpdate($other_vari_opt, $v_opt, $k_opt_id, $opt_barcode, $product_barcode, $product_upc, $product_id, $product_sku, $connection);
                                }*/
                                $validate = Jetproductinfo::validateProductBarcode($opt_upc, $option_id, $merchant_id);
                                if ($opt_upc == "" || !is_numeric($opt_upc) || (is_numeric($opt_upc) && !$opt_barcode) || (is_numeric($opt_upc) && $opt_barcode && !$validate)) {
                                    $invalid_upc = true;
                                }

                                if ($invalid_upc) {
                                    $chek_flag = true;
                                    unset($other_vari_opt[$option_id]['upc']);
                                    $product_error['invalid_asin'][] = $option_sku;
                                }
                            }

                        }
                    }
                    if (count($product_error) > 0) {
                        $error = "";

                        if (isset($product_error['invalid_asin']) && count($product_error['invalid_asin']) > 0) {
                            $error .= "Invalid/Missing Barcode for sku(s): " . implode(', ', $product_error['invalid_asin']) . "<br>";
                        }
                        $return_status['error'] = $error;

                        unset($error);
                        unset($product_error);
                        //return json_encode($return_status);
                    }
                } else {
                    $upc_success_flag = false;
                    $asin_success_flag = false;
                    $mpn_success_flag = false;
                    $invalid_upc = false;
                    $invalid_asin = false;
                    $invalid_mpn = false;
                    $chek_flag = false;
                    $er_msg = "";
                    $type = "";
                    $type = Jetproductinfo::checkUpcType($product_upc);

                    /*
                     * validate upc
                     */
                    $category_id = trim($_POST['category_id']);
                    $skipCategory = ['Jewelry', 'Rings'];
                    if (!empty($category_id) && !in_array($category_id, $skipCategory)) {

                        if (isset($product_upc) && !empty($product_upc)) {
                            $var = Data::validateUpc($product_upc);
                            if ($var == true) {
                                $invalid_upc = false;
                            } else {
                                $invalid_upc = true;
                            }
                        }

                        if (strlen($product_upc) > 0) {
                            $upc_success_flag = Jetproductinfo::checkUpcVariantSimple($product_upc, $product_id, $product_sku, $connection);
                        }
                        if ($product_upc == "" || !is_numeric($product_upc) || (is_numeric($product_upc) && $type = "") || (is_numeric($product_upc) && $type && $upc_success_flag)) {
                            $invalid_upc = true;
                        }

                        if ($invalid_upc) {
                            $chek_flag = true;
                            $er_msg .= "Invalid/Missing Barcode, must be unique" . "<br>";
                        }
                    }
                    if ($chek_flag) {
                        $return_status['error'] = $er_msg;
                        unset($er_msg);
                        //return json_encode($return_status);
                    }
                    /*-------------check asin and upc for variant-simple here ends----------*/
                }
                if ($walmart_attributes) {
                    foreach ($walmart_attributes as $attr_id => $value_arr) {
                        $flag = false;
                        if (is_array($value_arr) && count($value_arr) > 0) {
                            $walmart_attr_id = "";
                            foreach ($value_arr as $val_key => $chd_arr) {
                                if ($val_key == "jet_attr_id" && trim($chd_arr) == "") {
                                    $flag = true;
                                    foreach ($value_arr as $v_key => $c_ar) {
                                        if ($v_key == "jet_attr_id") {
                                            continue;
                                        } elseif ($v_key == "jet_attr_name") {
                                            continue;
                                        } elseif (is_array($c_ar)) {
                                            $new_options[trim($v_key)][trim($attr_id)] = trim($c_ar['value']);
                                        }
                                    }
                                    break;
                                } else {
                                    if ($val_key == "jet_attr_id") {
                                        $str_id = "";
                                        $str_id_arr = array();
                                        $str_id = trim($chd_arr);
                                        $str_id_arr = explode(',', $str_id);
                                        $walmart_attr_id = trim($str_id_arr[0]);
                                    } elseif ($val_key == "jet_attr_name") {
                                        $unit = "";
                                        $s_unit = [];
                                        if (count($attributes_of_jet) > 0 && array_key_exists($walmart_attr_id, $attributes_of_jet)) {
                                            $unit = $attributes_of_jet[$walmart_attr_id]['unit'];
                                        }
                                        $s_unit[] = trim($walmart_attr_id);
                                        if ($unit != "") {
                                            $s_unit[] = trim($unit);
                                        }
                                        //$s_unit=trim($s_unit);
                                        $pro_attr[trim($chd_arr)] = $s_unit;
                                    } elseif (is_array($chd_arr)) {
                                        //$options[$attr_id]['option_id'][]=trim($val_key);
                                        $options[trim($val_key)][$walmart_attr_id] = trim($chd_arr['value']);
                                        $new_options[trim($val_key)][trim($attr_id)] = trim($chd_arr['value']);
                                    }

                                }
                            }
                        }
                        if ($flag) {
                            //unset($options[$attr_id]);
                            continue;
                        }

                    }
                }
                $walmart_attr = $options;
                //$connection = Yii::$app->getDb();
                $product_id = '';
                $product_id = trim($id);
                if (is_array($walmart_attr) && count($walmart_attr) > 0) {
                    $opt_count = 0;
                    foreach ($walmart_attr as $opt_key => $option_value) {
                        $option_id = "";
                        $option_id = trim($opt_key);
                        $options_save = "";
                        $options_save = json_encode($option_value);
                        //$opt_price="";
                        //$opt_qty="";
                        $opt_upc = "";
                        $opt_asin = "";
                        $opt_mpn = "";
                        $opt_sku = "";
                        if (is_array($other_vari_opt) && count($other_vari_opt) > 0) {
                            //$opt_price=$other_vari_opt[$option_id]['price'];
                            //$opt_qty=$other_vari_opt[$option_id]['qty'];

                            $opt_upc = isset($other_vari_opt[$option_id]['upc']) ? $other_vari_opt[$option_id]['upc'] : '';
                            $opt_sku = $other_vari_opt[$option_id]['optionsku'];
                        }
//                        print_r($opt_upc);
//                        print_r($option_id);
                        $sql = "";
                        $model2 = "";
                        $query = "SELECT `option_id` from `jet_product_variants` WHERE option_id='" . $option_id . "' LIMIT 1";
                        $model2 = Data::sqlRecords($query, "one", "select");
                        if ($model2) {
                            $new_variant_option_1 = "";
                            $new_variant_option_2 = "";
                            $new_variant_option_3 = "";
                            if (is_array($new_options[trim($opt_key)]) && count($new_options[trim($opt_key)]) > 0) {
                                $v_opt_count = 1;
                                foreach ($new_options[trim($opt_key)] as $opts_k => $opts_v) {
                                    if ($v_opt_count == 1) {
                                        $new_variant_option_1 = $opts_v;
                                    }
                                    if ($v_opt_count == 2) {
                                        $new_variant_option_2 = $opts_v;
                                    }
                                    if ($v_opt_count == 3) {
                                        $new_variant_option_3 = $opts_v;
                                    }
                                    $v_opt_count++;
                                }
                            }
                            if (!empty($opt_upc)) {
                                $sql = "UPDATE `jet_product_variants` SET
                                    option_unique_id='" . trim($opt_upc) . "',
                                    option_qty ='" . $other_vari_opt[$option_id]['walmart_product_inventory'] . "'
                                    where option_id='" . $option_id . "'";
                            } else {
                                $sql = "UPDATE `jet_product_variants` SET
                                    option_qty ='" . $other_vari_opt[$option_id]['walmart_product_inventory'] . "'
                                    where option_id='" . $option_id . "'";
                            }

                            Data::sqlRecords($sql, null, "update");
                            //$connection->createCommand($sql)->execute();
                            $model3 = "";
                            $query = "SELECT `option_id` from `walmart_product_variants` WHERE option_id='" . $option_id . "' LIMIT 1";
                            $model3 = Data::sqlRecords($query, "one", "select");
                            if ($model3 !== "") {
                                $sql = "";
                                $sql = "UPDATE `walmart_product_variants` SET
                                    new_variant_option_1='" . addslashes($new_variant_option_1) . "',
                                    new_variant_option_2='" . addslashes($new_variant_option_2) . "',
                                    new_variant_option_3='" . addslashes($new_variant_option_3) . "',
                                    walmart_option_attributes='" . addslashes($options_save) . "' ,
                                    option_prices =" . $other_vari_opt[$option_id]['walmart_product_price'] . "
                                    where option_id='" . $option_id . "'";
                                Data::sqlRecords($sql, null, "update");
                            }
                        }

                        if ($option_id == $variant_id) {
                            $model->jet_product->upc = trim($opt_upc);
                            $model->jet_product->qty = trim($other_vari_opt[$option_id]['walmart_product_inventory']);
                            $model->jet_product->vendor = trim($product_vendor);
                            $model->product_price = $other_vari_opt[$option_id]['walmart_product_price'];
                        }
                        $opt_count++;
                    }
                } else {
                    if (is_array($other_vari_opt) && count($other_vari_opt) > 0) {
                        $opt_count = 0;
                        foreach ($other_vari_opt as $opt_id => $v_arr) {
                            $model2 = "";
                            $option_id = "";
                            $option_id = trim($opt_id);
                            //$opt_price="";
                            //$opt_qty="";
                            $opt_upc = "";
                            $opt_asin = "";
                            $opt_mpn = "";
                            //$opt_price=$other_vari_opt[$option_id]['price'];
                            //$opt_qty=$other_vari_opt[$option_id]['qty'];
                            $opt_upc = $other_vari_opt[$option_id]['upc'];
                            if ($opt_sku == $product_sku) {
                                //if(trim($opt_upc)!=""){
                                $model->jet_product->upc = trim($opt_upc);
                                $model->jet_product->vendor = trim($product_vendor);
                                //}
                            }
                            $sql = "";
                            $model2 = "";
                            $query = "SELECT `option_id` from `jet_product_variants` WHERE option_id='" . $option_id . "' LIMIT 1";
                            $model2 = Data::sqlRecords($query, "one", "select");
                            if ($model2 !== "") {
                                $sql = "";
                                $sql = "UPDATE `jet_product_variants` SET
                                        option_unique_id='" . trim($opt_upc) . "',
                                        option_qty ='" . $other_vari_opt[$option_id]['walmart_product_inventory'] . "'
                                        where option_id='" . $option_id . "'";
                                //$connection->createCommand($sql)->execute();
                                Data::sqlRecords($sql, null, "update");

                            }
                            $model3 = "";
                            $model3 = $connection->createCommand("SELECT `option_id` from `walmart_product_variants` WHERE option_id='" . $option_id . "'")->queryOne();
                            if ($model3 !== "") {
                                $sql = "";
                                $sql = "UPDATE `walmart_product_variants` SET
                                        walmart_option_attributes='',
                                        option_prices =" . $other_vari_opt[$option_id]['walmart_product_price'] . "
                                        where option_id='" . $option_id . "'";
                                Data::sqlRecords($sql, null, "update");
                                //$connection->createCommand($sql)->execute();
                            }
                            $opt_count++;
                        }
                    }
                }
                unset($model2);
                unset($sql);
                unset($options_save);
                if (count($pro_attr) == 0)
                    $model->walmart_attributes = '';
                else
                    $model->walmart_attributes = json_encode($pro_attr);
                $model->jet_product->vendor = $product_vendor;
                $model->short_description = $product_short;
                $model->self_description = $product_self;
                $model->tax_code = $product_tax;
                if (isset($_POST['walmart_product_title'])) {
                    $model->product_title = $_POST['walmart_product_title'];
                }
                $model->jet_product->save(false);
                $model->save(false);
                unset($walmart_attributes);
                unset($other_vari_opt);
                unset($attributes_of_jet);

            } else {

                /*-------------check asin and upc for simple here----------*/
                $upc_success_flag = false;
                $asin_success_flag = false;
                $mpn_success_flag = false;
                $chek_flag = false;
                $invalid_upc = false;
                $er_msg = "";
                $type = "";
                $product_upc = trim($product_upc);

                /*
                 *  validate upc
                 */
                if (isset($product_upc) && !empty($product_upc)) {
                    $var = Data::validateUpc($product_upc);
                    if ($var == true) {
                        $invalid_upc = false;
                    } else {
                        $invalid_upc = true;
                    }
                }

                $type = Jetproductinfo::checkUpcType($product_upc);
                if (strlen($product_upc) > 0) {
                    $upc_success_flag = Jetproductinfo::checkUpcSimple($product_upc, $product_id, $connection);
                }

                if ($product_upc == "" || !is_numeric($product_upc) || (is_numeric($product_upc) && !$type) || (is_numeric($product_upc) && $type && $upc_success_flag)) {
                    // echo "duplicate upc";
                    $invalid_upc = true;
                }


                if ($invalid_upc) {
                    $chek_flag = true;
                    //echo "duplicate upc/asin";
                    $er_msg .= "Invalid/Missing Barcode , please fill unique barcode" . "<br>";
                }
                //echo $er_msg;die;
                if ($chek_flag) {
                    $return_status['error'] = $er_msg;
                    return json_encode($return_status);
                }
                /*-------------check asin and upc for simple here ends----------*/
                $walmart_attributes1 = "";
                if (Yii::$app->request->post('jet_attributes1')) {
                    $walmart_attributes1 = Yii::$app->request->post('jet_attributes1');
                }
                $walmart_attr = array();
                if ($walmart_attributes1) {
                    foreach ($walmart_attributes1 as $key => $value) {
                        if (count($value) == 1 && $value[0] != '') {
                            $walmart_attr[$key] = array(0 => $value[0]);
                        } elseif (count($value) == 2 && $value[0] != '' && $value[1] != '') {
                            $walmart_attr[$key] = array(0 => $value[0], 1 => $value[1]);
                        }
                    }
                }

                if (count($walmart_attr) == 0)
                    $model->walmart_attributes = '';
                else
                    $model->walmart_attributes = json_encode($walmart_attr);
                $model->jet_product->upc = $product_upc;
                $model->jet_product->vendor = $product_vendor;
                $model->short_description = $product_short;
                $model->tax_code = $product_tax;
                $model->self_description = $product_self;
                if (isset($_POST) && !empty($_POST['walmart_product_price']) && !empty($_POST['walmart_product_inventory'])) {

                    $model->product_price = $_POST['walmart_product_price'];
                    $model->product_title = $_POST['walmart_product_title'];
                    $model->jet_product->qty = $_POST['walmart_product_inventory'];
                }
                //$model->category=$category;
                $model->jet_product->save(false);
                $model->save(false);

                // `product_qty`='".$_POST['walmart_product_inventory']."',

                /*if(isset($_POST) && !empty($_POST['walmart_product_price']) && !empty($_POST['walmart_product_inventory'])){
                    $query ="UPDATE `walmart_product` SET `product_price`= '".$_POST['walmart_product_price']."', `product_title`='".addslashes($_POST['walmart_product_title'])."' WHERE `merchant_id`='".$merchant_id."' AND `product_id`='".$_POST['JetProduct']['product_id']."'";

                    Data::sqlRecords($query,null,'update');

                }*/
                unset($walmart_attr);
            }
            if (isset($return_status['error'])) {
                return json_encode($return_status);
            }
            $return_status['success'] = "Product information has been saved successfully..";
            return json_encode($return_status);
        } else {
            //not post successfully
        }
    }

    /**
     * Product bulk upload
     * select all product, validate and upload on walmart
     */
    public function actionBulk()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $action = Yii::$app->request->post('action');
        $selection = (array)Yii::$app->request->post('selection');
        if (count($selection) == 0) {
            Yii::$app->session->setFlash('error', "No Product selected...");
            return $this->redirect(['index']);
        }
        $connection = Yii::$app->getDb();

        if ($action == 'batch-upload') {
            $productResponse = $this->walmartHelper->createProductOnWalmart($selection, $this->walmartHelper, MERCHANT_ID, $connection);
            if (is_array($productResponse) && isset($productResponse['uploadIds'], $productResponse['feedId']) && count($productResponse['uploadIds'] > 0)) {
                //save product status and data feed
                $ids = implode(',', $productResponse['uploadIds']);
                foreach ($productResponse['uploadIds'] as $val) {
                    $query = "UPDATE `walmart_product` SET status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "' where product_id='" . $val . "'";
                    Data::sqlRecords($query, null, "update");
                }
                $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`)VALUES('" . MERCHANT_ID . "','" . $productResponse['feedId'] . "','" . $ids . "')";
                Data::sqlRecords($query, null, "insert");
                Yii::$app->session->setFlash('success', "product feed successfully submitted on walmart.");
            } elseif (isset($productResponse['errors'])) {

                Yii::$app->session->setFlash('error', json_encode($productResponse['errors']));
            }
            return $this->redirect(['index']);
        }
    }

    /**
     * Product bulk upload via Ajax
     * select all product, validate and upload on walmart
     */
    public function actionAjaxBulkUpload()
    {
        if (Yii::$app->user->isGuest)
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));

        $action = Yii::$app->request->post('action');
        $selection = (array)Yii::$app->request->post('selection');
        $Productcount = count($selection);

        if ($Productcount == 0) {
            Yii::$app->session->setFlash('error', "No Product selected...");
            return $this->redirect(['index']);
        }

        $merchant_id = MERCHANT_ID;
        $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);

        $session = Yii::$app->session;
        $session->set('shopify_object', serialize($sc));
        $session->set('merchant_id', $merchant_id);

        $size_of_request = 100;//Number of products to be uploaded at once(in single feed)
        $pages = (int)(ceil($Productcount / $size_of_request));

        $max_feed_allowed_per_hour = 10;//We can only send 10 feeds per hour.
        if ($pages > $max_feed_allowed_per_hour) {
            $size_of_request = (int)(ceil($Productcount / $max_feed_allowed_per_hour));
            if ($size_of_request > 10000) {
                Yii::$app->session->setFlash('error', "MAX Limit Exceeded. Please Unselect Some Products.");
                return $this->redirect(['index']);
            }
            $pages = (int)(ceil($Productcount / $size_of_request));
        }

        if ($action == 'batch-upload') {
            $selectedProducts = array_chunk($selection, $size_of_request);

            //Increase Array Indexes By 1
            //$selectedProducts = array_combine(range(1, count($selectedProducts)), array_values($selectedProducts));

            $session->set('selected_products', $selectedProducts);

            return $this->render('ajaxbulkupload', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);
        } elseif ($action == 'batch-retire') {
            $pages = count($selection);

            $session->set('retire_product', $selection);

            return $this->render('retireproduct', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);

        } elseif ($action == 'batch-product-status') {
            $pages = count($selection);

            $session->set('product_status', $selection);

            return $this->render('bulkproductstatus', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);
        } elseif ($action == 'batch-update-price') {
            //$checkEnablePutRequest= WalmartProductComponent::isEnablePutRequest($Productcount);
            /*if($checkEnablePutRequest){
                $selectedProducts = array_chunk($selection, 1);
                $pages = (int)(ceil($Productcount / 1));
            }
            else{*/
                $selectedProducts = array_chunk($selection, $size_of_request);
         /*   }*/
            $session->set('batch_update_price', $selectedProducts);
            return $this->render('batchupdateprice', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);

        } elseif ($action == 'batch-update-inventory') {
            /*$checkEnablePutRequest= WalmartProductComponent::isEnablePutRequest($Productcount);
            if($checkEnablePutRequest){*/
            /*    $selectedProducts = array_chunk($selection, 1);
                $pages = (int)(ceil($Productcount / 1));
            }
            else{*/
                $selectedProducts = array_chunk($selection, $size_of_request);
   /*         }*/
            $session->set('batch-update-inventory', $selectedProducts);

            return $this->render('batchupdateinventory', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);

        } elseif ($action == 'batch-promotion-price') {
            $data = $this->walmartHelper->updateBulkPromotionalPriceOnWalmart($selection);
            if (isset($data['success'])) {
                Yii::$app->session->setFlash('success', "Successfully Updated Promo Price on Walmart. FeedId : " . $data['feedId']);
            } elseif (isset($data['error'])) {
                Yii::$app->session->setFlash('error', $data['message']);
            } else {
                Yii::$app->session->setFlash('error', "Error Occured. Please try again after some time..");
            }
            return $this->redirect(['index']);
        }

        $session->close();
    }


    public function actionStartbatchupload()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        $session = Yii::$app->session;

        $returnArr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['selected_products'][$index]) ? $session['selected_products'][$index] : [];
        $count = count($selectedProducts);

        if (!$count) {
            $returnArr = ['error' => true, 'error_msg' => 'No Products to Upload'];
        } else {
            $connection = Yii::$app->getDb();

            $merchant_id = "";
            if (isset($session['merchant_id']))
                $merchant_id = $session['merchant_id'];
            else
                $merchant_id = MERCHANT_ID;

            try {
                $productResponse = $this->walmartHelper->createProductOnWalmart($selectedProducts, $this->walmartHelper, MERCHANT_ID, $connection);
                if (is_array($productResponse) && isset($productResponse['uploadIds'], $productResponse['feedId']) && count($productResponse['uploadIds'] > 0)) {
                    //save product status and data feed
                    $ids = implode(',', $productResponse['uploadIds']);
                    foreach ($productResponse['uploadIds'] as $val) {
                        $query = "UPDATE `walmart_product` SET status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', error='' where product_id='" . $val . "'";
                        Data::sqlRecords($query, null, "update");
                    }
                    $feed_file = isset($productResponse['feed_file']) ? $productResponse['feed_file'] : '';
                    $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('" . MERCHANT_ID . "','" . $productResponse['feedId'] . "','" . $ids . "','" . $feed_file . "')";
                    Data::sqlRecords($query, null, "insert");

                    $msg = "product feed successfully submitted on walmart.";
                    $feed_count = count($productResponse['uploadIds']);
                    $feedId = $productResponse['feedId'];
                    $returnArr = ['success' => true, 'success_msg' => $msg, 'success_count' => $feed_count, 'feed_id' => $feedId];
                }

                //save errors in database for each erroed product
                if (isset($productResponse['errors'])) {
                    $_feedError = null;
                    if (isset($productResponse['errors']['feedError'])) {
                        $msg = $productResponse['errors']['feedError'];
                        $_feedError = $msg;
                        unset($productResponse['errors']['feedError']);

                    }

                    foreach ($productResponse['errors'] as $productSku => $error) {
                        if (is_array($error)) {
                            $error = implode(',', $error);
                        }

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($error) . "' where jp.sku='" . $productSku . "'";
                        Data::sqlRecords($query, null, "update");
                    }

                    $returnArr['error'] = true;
                    $returnArr['error_msg'] = $productResponse['errors'];
                    $returnArr['originalmessage'] = $productResponse['originalmessage'];

                    $returnArr['error_count'] = count($productResponse['errors']);
                    $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

                    if (!is_null($_feedError)) {
                        $returnArr['feedError'] = $_feedError;
                    }
                }

            } catch (Exception $e) {
                $returnArr = ['error' => true, 'error_msg' => $e->getMessage()];
            }
        }
        return json_encode($returnArr);
    }

    /*public function actionUpdateinventory()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $query='select jet.id,sku,type,qty,fulfillment_lag_time from `walmart_product` wal INNER JOIN `jet_product` jet ON jet.id=wal.product_id where wal.status!="Not Uploaded" and wal.merchant_id="'.MERCHANT_ID.'"';
        $product = Data::sqlRecords($query,"all","select");
        if(is_array($product) && count($product)>0){
            $response=[];
            $response = $this->walmartHelper->updateInventoryOnWalmart($product,"product");
            if(isset($response['errors']))
                $error++;
        }
        if($success>0){
            Yii::$app->session->setFlash('success', " product(s) inventory is updated successfully on walmart");
        }
        if($error>0 && $success==0){
            Yii::$app->session->setFlash('error', $error." some product inventory not updated successfully on walmart");
        }
        return $this->redirect(['index']);
    }*/

    /**
     * undate price of submitted product
     *
     */
    public function actionUpdateprice()
    {
        $session = Yii::$app->session;
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        /*$query = 'select jet.id,sku,type,price,jet.merchant_id from `walmart_product` wal INNER JOIN `jet_product` jet ON jet.id=wal.product_id where wal.status!="Not Uploaded" and wal.merchant_id="' . MERCHANT_ID . '"';*/
        /*$query = 'select jet.id,sku,type,price,jet.merchant_id,wpv.option_id from (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as jet ON jet.id=wal.product_id LEFT JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`="' . MERCHANT_ID . '") as wpv ON wpv.product_id=wal.product_id where (wal.status="'.WalmartProduct::PRODUCT_STATUS_UPLOADED.'" OR wal.status="'.WalmartProduct::PRODUCT_STATUS_UNPUBLISHED.'" OR wal.status="'.WalmartProduct::PRODUCT_STATUS_STAGE.'" OR wpv.status="'.WalmartProduct::PRODUCT_STATUS_UPLOADED.'" OR wpv.status="'.WalmartProduct::PRODUCT_STATUS_UNPUBLISHED.'" OR wpv.status="'.WalmartProduct::PRODUCT_STATUS_STAGE.'")and wal.merchant_id="' . MERCHANT_ID . '"';*/
        $query = 'select jet.id,COALESCE(jpv.option_sku,sku) as sku,type,COALESCE(wpv.option_prices,jpv.option_price,wal.product_price,jet.price) as price,jet.merchant_id,wpv.option_id from (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as jet ON jet.id=wal.product_id LEFT JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`="' . MERCHANT_ID . '" ) as wpv ON wpv.product_id=wal.product_id LEFT JOIN jet_product_variants as jpv ON wpv.option_id=jpv.option_id where (wal.status="'.WalmartProduct::PRODUCT_STATUS_UPLOADED.'" OR wal.status="'.WalmartProduct::PRODUCT_STATUS_UNPUBLISHED.'" OR wal.status="'.WalmartProduct::PRODUCT_STATUS_STAGE.'" OR wpv.status="'.WalmartProduct::PRODUCT_STATUS_UPLOADED.'" OR wpv.status="'.WalmartProduct::PRODUCT_STATUS_UNPUBLISHED.'" OR wpv.status="'.WalmartProduct::PRODUCT_STATUS_STAGE.'")and wal.merchant_id="' . MERCHANT_ID . '"';
       /* $query = 'select jet.id,sku,type,price,jet.merchant_id from (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as jet ON jet.id=wal.product_id where (wal.status="' . WalmartProduct::PRODUCT_STATUS_UPLOADED . '" OR wal.status="' . WalmartProduct::PRODUCT_STATUS_UNPUBLISHED . '" OR wal.status="' . WalmartProduct::PRODUCT_STATUS_STAGE . '")and wal.merchant_id="' . MERCHANT_ID . '"';*/

        $product = Data::sqlRecords($query, "all", "select");

        $Productcount = count($product);

        if (is_array($product) && $Productcount) {
            $size_of_request = 50;//Number of products to be uploaded at once(in single feed)
            $pages = (int)(ceil($Productcount / $size_of_request));
            $max_feed_allowed_per_hour = 10;
            if ($pages > $max_feed_allowed_per_hour) {
                $size_of_request = (int)(ceil($Productcount / $max_feed_allowed_per_hour));
                if ($size_of_request > 10000) {
                    Yii::$app->session->setFlash('error', "MAX Feed Limit Exceeded.");
                    return $this->redirect(['index']);
                }
                $pages = (int)(ceil($Productcount / $size_of_request));
            }

            $selectedProducts = array_chunk($product, $size_of_request);
            $session->set('products_for_price_update', $selectedProducts);
            $session->close();

            return $this->render('updateprice', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);
        } else {
            Yii::$app->session->setFlash('error', "No Products Found..");
            return $this->redirect(['index']);
        }
    }

    public function actionPricepost()
    {
        $session = Yii::$app->session;
        $returnArr = ['error' => true];
        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['products_for_price_update'][$index]) ? $session['products_for_price_update'][$index] : [];
        $count = count($selectedProducts);
        $errors = [];
        if ($count) {
            $response = $this->walmartHelper->updatePriceOnWalmart($selectedProducts, "product");
            if(isset($response['errored_sku']) && !empty($response['errored_sku'])){
                if (isset($response['errors']))
                $returnArr = ['error' => "Price Feed Error : Price not updated on walmart", 'message' => 'Price for some Products is not updated due to ' . json_encode($response['errors']),'errored_sku'=>json_encode($response['errored_sku']),'error_sku_count'=>$response['errored_sku_count']];
                else
                $returnArr = ['success' => true, 'count' => ($count-$response['errored_sku_count']),'errored_sku'=>json_encode($response['errored_sku'])];
            }
            else{
                if (isset($response['errors']))
                $returnArr = ['error' => "Price Feed Error : Price not updated on walmart", 'message' => 'Price for some Products is not updated due to ' . json_encode($response['errors'])];
            else
                $returnArr = ['success' => true, 'count' => $count];
            }
        }
        return json_encode($returnArr);
    }

    /**
     * undate inventory of submitted product
     *
     */
    public function actionUpdateinventory()
    {
        $session = Yii::$app->session;

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }

        /*$query = 'SELECT `jet`.`id`, `jet`.`merchant_id`, `sku`, `type`, `qty`, `fulfillment_lag_time` FROM `walmart_product` `wal` INNER JOIN `jet_product` `jet` ON `jet`.`id`=`wal`.`product_id` where `wal`.`status`!="Not Uploaded" and `wal`.`merchant_id`="' . MERCHANT_ID . '"';*/
        $query = 'SELECT `jet`.`id`, `jet`.`merchant_id`, `sku`, `type`, `qty`, `fulfillment_lag_time` FROM (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as `wal` INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as `jet` ON `jet`.`id`=`wal`.`product_id` where `wal`.`status`!="' . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . '" and `wal`.`status`!="' . WalmartProduct::PRODUCT_STATUS_DELETE . '" and `wal`.`merchant_id`="' . MERCHANT_ID . '"';
        $product = Data::sqlRecords($query, "all", "select");

        $Productcount = count($product);

        if (is_array($product) && $Productcount) {
            $size_of_request = 200;//Number of products to be uploaded at once(in single feed)
            $pages = (int)(ceil($Productcount / $size_of_request));

            $max_feed_allowed_per_hour = 10;
            if ($pages > $max_feed_allowed_per_hour) {
                $size_of_request = (int)(ceil($Productcount / $max_feed_allowed_per_hour));
                if ($size_of_request > 10000) {
                    Yii::$app->session->setFlash('error', "MAX Feed Limit Exceeded.");
                    return $this->redirect(['index']);
                }
                $pages = (int)(ceil($Productcount / $size_of_request));
            }

            $selectedProducts = array_chunk($product, $size_of_request);
            $session->set('products_for_inventory_update', $selectedProducts);
            $session->close();

            return $this->render('updateinventory', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);
        } else {
            Yii::$app->session->setFlash('error', "No Products Found..");
            return $this->redirect(['index']);
        }
    }

    public function actionInventorypost()
    {
        $session = Yii::$app->session;
        $merchant_id = Yii::$app->user->identity->id;
        if ($merchant_id == '880')
            return;
        $returnArr = ['error' => true];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['products_for_inventory_update'][$index]) ? $session['products_for_inventory_update'][$index] : [];
        $count = count($selectedProducts);

        $errors = [];

        if ($count) {
            $response = $this->walmartHelper->updateInventoryOnWalmart($selectedProducts, "product");

            if (isset($response['errors']))
                $returnArr = ['error' => "Inventory Feed Error : Inventory not updated on walmart", 'message' => 'Inventory for some Products is not updated due to ' . json_encode($response['errors'])];
            else
                $returnArr = ['success' => true, 'count' => $count];
        }
        return json_encode($returnArr);
    }

    /*public function actionUpdateprice()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $query='select jet.id,sku,type,price,jet.merchant_id from `walmart_product` wal INNER JOIN `jet_product` jet ON jet.id=wal.product_id where wal.status!="Not Uploaded" and wal.merchant_id="'.MERCHANT_ID.'"';
        $product = Data::sqlRecords($query,"all","select");
        $error=0;
        //$product = Data::sqlRecords($sql,"all","select");
        if(is_array($product) && count($product)>0)
        {
            $response=[];
            $response = $this->walmartHelper->updatePriceOnWalmart($product,"product");
            if(isset($response['errors']))
                $error++;
        }
        if($error){
            Yii::$app->session->setFlash('error', $error." some product price not updated successfully on walmart");
        }
        return $this->redirect(['index']);
    }
    */
    public function actionBatchimport()
    {
        $connection = Yii::$app->getDb();
        $merchant_id = MERCHANT_ID;
        $shopname = SHOP;
        $token = TOKEN;
        $countProducts = 0;
        $pages = 0;
        $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
        $countProducts = $sc->call('GET', '/admin/products/count.json');

        if (isset($countProducts['errors'])) {
            Yii::$app->session->setFlash('error', $countProducts['errors']);
            return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/walmart/walmartproduct/index');
        }
        $pages = ceil($countProducts / 250);
        $session = "";
        $session = Yii::$app->session;
        if (!is_object($session)) {
            Yii::$app->session->setFlash('error', "Can't initialize Session.Product(s) upload cancelled.");
            return $this->redirect(['index']);
        }
        $session->set('product_page', $pages);
        $session->set('shopify_object', serialize($sc));
        $session->set('merchant_id', $merchant_id);
        $session->close();
        unset($jetConfigarray);
        return $this->render('batchimport', [
            'totalcount' => $countProducts,
            'pages' => $pages
        ]);
    }

    public function actionBatchimportproduct()
    {
        $index = Yii::$app->request->post('index');
        $countUpload = Yii::$app->request->post('count');
        try {
            $session = Yii::$app->session;
            if (!isset($connection)) {
                $connection = Yii::$app->getDb();
            }
            $pages = $session->get('product_page');
            $sc = unserialize($session->get('shopify_object'));
            $merchant_id = $session->get('merchant_id');
            if (!$merchant_id) {
                $merchant_id = MERCHANT_ID;
                $shopname = SHOP;
                $token = TOKEN;
            }
            if (!is_object($sc)) {
                $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
            }
            if ($index == 1) {
                $jProductTotal = 0;
                $not_skuTotal = 0;
            }
            //for($i=1;$i<=$pages;$i++)
            //{
            $products = $sc->call('GET', '/admin/products.json', array('limit' => 250, 'page' => $index));
            $product_qty = 0;
            $attr_id = "";
            $attributes_val = "";
            $brand = "";
            $created_at = "";
            $product_sku = "";
            $product_type = "";
            $jProduct = 0;
            $not_sku = 0;
            if ($products) {
                foreach ($products as $value) {
                    $weight = 0;
                    $unit = "";
                    $product_id = $value['id'];
                    $product_title = $value['title'];
                    $vendor = $value['vendor'];
                    $product_type = $value['product_type'];
                    $product_des = $value['body_html'];
                    $variants = $value['variants'];
                    $images = array();
                    $images = $value['images'];
                    $created_at = $value['created_at'];
                    $product_price = $value['variants'][0]['price'];
                    $barcode = $value['variants'][0]['barcode'];
                    $weight = $value['variants'][0]['weight'];
                    $unit = $value['variants'][0]['weight_unit'];
                    if ($weight > 0) {
                        $weight = (float)Jetappdetails::convertWeight($weight, $unit);
                    }
                    $imagArr = array();
                    $variantArr = array();
                    if (is_array($images) && count($images) > 0) {
                        foreach ($images as $valImg) {
                            $imagArr[] = $valImg['src'];
                        }
                    }
                    $product_images = implode(',', $imagArr);
                    $product_sku = $value['variants'][0]['sku'];
                    if ($product_sku == "") {
                        $not_sku++;
                        continue;
                    }
                    $jProduct++;
                    //$product_base_image=$value['image']['src'];
                    $product_qty = $value['variants'][0]['inventory_quantity'];
                    $variant_id = $value['variants'][0]['id'];

                    if (count($variants) > 1) {
                        foreach ($variants as $value1) {
                            $option_weight = 0;
                            $variantArr[] = $value1['id'];
                            $option_id = $value1['id'];
                            $option_title = $value1['title'];
                            $option_sku = $value1['sku'];
                            $option_image_id = $value1['image_id'];
                            $option_price = $value1['price'];
                            $option_weight = $value1['weight'];
                            if ($option_weight > 0) {
                                $option_weight = (float)Jetappdetails::convertWeight($option_weight, $value1['weight_unit']);
                            }
                            $option_qty = $value1['inventory_quantity'];
                            $option_variant1 = $value1['option1'];
                            $option_variant2 = $value1['option2'];
                            $option_variant3 = $value1['option3'];
                            $option_barcode = $value1['barcode'];
                            $option_image_url = '';
                            foreach ($images as $value2) {
                                if ($value2['id'] == $option_image_id) {
                                    $option_image_url = $value2['src'];
                                }
                            }
                            $result = "";
                            $optionmodel = "";
                            $optionmodel = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1");
                            $result = $optionmodel->queryOne();
                            if (!$result) {
                                $sql = "INSERT INTO `jet_product_variants`(
                                            `option_id`,`product_id`,`merchant_id`,
                                            `option_title`,`option_sku`,`option_image`,
                                            `option_price`,`option_qty`,`variant_option1`,
                                            `variant_option2`,`variant_option3`,`vendor`,
                                            `option_unique_id`,`option_weight`
                                            )
                                            VALUES('" . $option_id . "','" . $product_id . "','" . $merchant_id . "','" . addslashes($option_title) . "','" . addslashes($option_sku) . "','" . addslashes($option_image_url) . "','" . $option_price . "','" . $option_qty . "','" . addslashes($option_variant1) . "','" . addslashes($option_variant2) . "','" . addslashes($option_variant3) . "','" . addslashes($vendor) . "','" . addslashes($option_barcode) . "','" . $option_weight . "')";
                                $connection->createCommand($sql)->execute();
                            }

                            $walresult = $connection->createCommand("SELECT `option_id` FROM `walmart_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1")->queryOne();
                            if (!$walresult) {
                                $sql = "INSERT INTO `walmart_product_variants`(
                                            `option_id`,`product_id`,`merchant_id`
                                            )
                                            VALUES('" . $option_id . "','" . $product_id . "','" . $merchant_id . "')";
                                $connection->createCommand($sql)->execute();
                            }
                        }

                        $options = $value['options'];
                        $attrId = array();
                        foreach ($options as $key => $val) {
                            $attrname = $val['name'];
                            $attrId[$val['id']] = $val['name'];
                            foreach ($val['values'] as $k => $v) {
                                $option_value[$attrname][$k] = $v;
                            }
                        }
                        $attr_id = json_encode($attrId);
                    }

                    //save attribute values for simple products
                    if (count($variants) == 1) {
                        $attr_id = Data::getOptionValuesForSimpleProduct($value);
                    }

                    $jetMappedCategoryId = 0;
                    $walMappedCategoryId = '';
                    if ($product_type) {
                        $query = 'SELECT category_id FROM `jet_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($product_type) . '" LIMIT 0,1';
                        $queryObj = $connection->createCommand($query);
                        $modelmap = $queryObj->queryOne();
                        if ($modelmap) {
                            $jetMappedCategoryId = $modelmap['category_id'];
                        } else {
                            $query = 'INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . addslashes($product_type) . '")';
                            $queryObj = $connection->createCommand($query)->execute();
                        }

                        $query = 'SELECT category_id FROM `walmart_category_map` where merchant_id="' . $merchant_id . '" AND product_type="' . addslashes($product_type) . '" LIMIT 0,1';
                        $queryObj = $connection->createCommand($query);
                        $walmodelmap = $queryObj->queryOne();
                        if ($walmodelmap) {
                            $walMappedCategoryId = $walmodelmap['category_id'];
                        } else {
                            $query = 'INSERT INTO `walmart_category_map`(`merchant_id`,`product_type`)VALUES("' . $merchant_id . '","' . addslashes($product_type) . '")';
                            $queryObj = $connection->createCommand($query)->execute();
                        }
                    }

                    //insert product data
                    $productmodel = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE id='" . $product_id . "' LIMIT 0,1");
                    $result = $productmodel->queryOne();
                    if (!$result) {
                        if (count($variants) > 1)
                            $type = 'variants';
                        else
                            $type = 'simple';

                        $sql = "INSERT INTO `jet_product` (`id`,`merchant_id`,`title`,`sku`,`type`,`description`,`image`,`price`,`qty`,`attr_ids`,`upc`,`status`,`vendor`,`variant_id`,`product_type`,`weight`,`fulfillment_node`) VALUES ('" . $product_id . "','" . $merchant_id . "','" . addslashes($product_title) . "','" . addslashes($product_sku) . "','" . $type . "','" . addslashes($product_des) . "','" . addslashes($product_images) . "','" . $product_price . "','" . $product_qty . "','" . addslashes($attr_id) . "','" . addslashes($barcode) . "','Not Uploaded','" . addslashes($vendor) . "','" . addslashes($variant_id) . "','" . addslashes($product_type) . "','" . $weight . "','" . $jetMappedCategoryId . "')";
                        $model = $connection->createCommand($sql)->execute();
                    }

                    $walresult = $connection->createCommand("SELECT `product_id` FROM `walmart_product` WHERE product_id='" . $product_id . "' LIMIT 0,1")->queryOne();
                    if (!$walresult) {
                        $sql = "INSERT INTO `walmart_product` (`product_id`,`merchant_id`,`status`,`product_type`,`category`) VALUES ('" . $product_id . "','" . $merchant_id . "','Not Uploaded','" . addslashes($product_type) . "','" . $walMappedCategoryId . "')";
                        $model = $connection->createCommand($sql)->execute();
                    }
                }
            }
            $jProductTotal += $jProduct;
            $not_skuTotal += $not_sku;
            unset($result);
            unset($product);
            //}
            if ($index == $pages) {
                $inserted = "";
                $result = "";
                $inserted = $connection->createCommand("SELECT `merchant_id` FROM `insert_product` WHERE merchant_id='" . $merchant_id . "'");
                $result = $inserted->queryOne();
                //insert data into insert products
                if (!$result) {
                    $queryObj = "";
                    $query = 'INSERT INTO `insert_product`
                                (
                                    `merchant_id`,
                                    `product_count`,
                                    `total_product`,
                                    `not_sku`,
                                    `status`
                                )
                                VALUES(
                                    "' . $merchant_id . '",
                                    "' . $jProductTotal . '",
                                    "' . $countUpload . '",
                                    "' . $not_skuTotal . '",
                                    "inserted"  
                                )';
                    $queryObj = $connection->createCommand($query)->execute();
                } else {
                    $updateQuery = "UPDATE `insert_product` SET `product_count`='" . $jProductTotal . "' ,`total_product`='" . $countUpload . "', `not_sku`='" . $not_skuTotal . "' WHERE merchant_id='" . $merchant_id . "'";
                    $updated = $connection->createCommand($updateQuery)->execute();
                }
            }
        } catch (ShopifyApiException $e) {
            return $returnArr['error'] = $e->getMessage();
        } catch (ShopifyCurlException $e) {
            return $returnArr['error'] = $e->getMessage();
        }

        $returnArr['success']['count'] = $jProduct;
        if ($not_sku > 0)
            $returnArr['success']['not_sku'] = $not_sku;

        return json_encode($returnArr);
        //}
    }

    public function actionLoad()
    {
        //echo Yii::$app->homeUrl.'var/MPProduct.xml';die;
        $str = file_get_contents('/opt/lampp/htdocs/walmart/var/MPProduct.xml');
        $response = Walmartapi::xmlToArray($str);
        echo addslashes($response['MPItemFeed']['_value']['MPItem']['Product']['longDescription']);
    }

    public function actionCategoryadd()
    {
        $query = "select category_id,attribute_values,parent_id from walmart_category where level=1";
        $response = Data::sqlRecords($query, "all", "select");
        $parentcategory = [];
        $count = 0;
        foreach ($response as $value) {
            if (!in_array($value['parent_id'], $parentcategory)) {
                $count++;
                $parentcategory[] = $value['parent_id'];
            }
        }
        foreach ($parentcategory as $val) {
            $query = "insert into walmart_category(merchant_id,category_id,title,parent_id,level)values(1,'Other','Other','" . $val . "',1)";
            $response = Data::sqlRecords($query, null, "insert");
        }
    }

    public function actionBatchproductstatus()
    {
        //$query = 'select count(*) as products from `walmart_product` wal INNER JOIN `jet_product` jet ON jet.id=wal.product_id where wal.merchant_id="' . MERCHANT_ID . '" LIMIT 0,1';
        $query = 'select count(*) as products from (SELECT * FROM `walmart_product` WHERE `merchant_id`= "' . MERCHANT_ID . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`= "' . MERCHANT_ID . '") as jet ON jet.id=wal.product_id where wal.merchant_id="' . MERCHANT_ID . '" LIMIT 0,1';
        $product = Data::sqlRecords($query, "one", "select");

        if (is_array($product) && isset($product['products']) && intval($product['products']) > 0) {
            $pages = ceil(intval($product['products']) / 20);
            $session = Yii::$app->session;
            $session->set('walmartHelper', serialize($this->walmartHelper));
            $session->set('product_page', $pages);

            return $this->render('batchstatus',
                [
                    'totalcount' => $product['products'],
                    'pages' => $pages
                ]
            );
        } else {
            echo "No Products Found.";
        }
    }
    
    public function actionProductstatus()
    {
        $getItemsCount = 20;
        $finish = false;
        $finishWithError = false;
        $index = Yii::$app->request->post('index');
        try {
            $session = Yii::$app->session;
            $walmartHelper = unserialize($session->get('walmartHelper'));
            $merchant_id = MERCHANT_ID;
            if (!is_object($walmartHelper)) {
                $walmartHelper = $this->walmartHelper;
            }
            $offset = $index * $getItemsCount;
            // Get $getItemsCount products status(s) from walmart
            $productArray = $walmartHelper->getItems(['limit' => $getItemsCount, 'offset' => $offset]);
            $count = 0;
            if (isset($productArray['error'])) {
                if (is_array($productArray['error'])) {
                    //[description] => No item found
                    foreach ($productArray['error'] as $error) {
                        if (isset($error['code']) && $error['code'] == 'CONTENT_NOT_FOUND.GMP_ITEM_QUERY_API') {
                            $finish = true;
                        } else {
                            $returnArr['error'] = "Error Code : " . $error['code'] . '<br/>' . 'Error Info : ' . $error['info'];
                        }
                    }
                }
            } elseif (isset($productArray['errors'])) {
                if (isset($productArray['errors']['error'])) {
                    if (isset($productArray['errors']['error']['code']) && $productArray['errors']['error']['code'] == 'UNAUTHORIZED.GMP_GATEWAY_API') {
                        $finishWithError = true;
                        $returnArr['error'] = "Walmart API Credentials are invalid.";
                    }
                }
                $returnArr['api_error'] = true;
            } elseif (isset($productArray['MPItemView'])) {
                $errors = [];
                foreach ($productArray['MPItemView'] as $key => $value) {
                    //get product sku
                    $product = [];
                    $flag= false;
                    //$query = "select sku,id from jet_product where merchant_id='" . $merchant_id . "' and sku='" . addslashes($value['sku']) . "' LIMIT 1";
                    $query = "SELECT `jp`.`sku`,`jp`.`id` FROM `walmart_product` INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id` = '{$merchant_id}') as `jp` WHERE `walmart_product`.`merchant_id`='{$merchant_id}' AND `jp`.`sku`='". addslashes($value['sku']) ."' LIMIT 0,1";
                    $product = Data::sqlRecords($query, 'one', 'select');
                    if (is_array($product) && count($product) > 0) {
                        //update main product status(s)
                        $query = "update walmart_product set status='" . $value['publishedStatus'] . "' where product_id='" . $product['id'] . "'";
                        Data::sqlRecords($query, null, 'update');
                        $count++;
                        $flag = true;
                    } else {
                        if (!isset($errors[$value['sku']]))
                            $errors[$value['sku']] = $value['sku'] . " :  This sku not found on app.";
                    }

                    //update variants product status
                    //$query = "select option_sku,option_id from jet_product_variants where merchant_id='" . $merchant_id . "' and option_sku='" . addslashes($value['sku']) . "' LIMIT 1";
                    $query = "SELECT `jpv`.`option_sku`,`jpv`.`option_id` FROM `walmart_product_variants` INNER JOIN (SELECT * FROM `jet_product_variants` WHERE `merchant_id` = '{$merchant_id}') as `jpv` WHERE `jpv`.`merchant_id`='{$merchant_id}' AND `jpv`.`option_sku`='" . addslashes($value['sku']) . "' LIMIT 0,1";
                    $productVariant = Data::sqlRecords($query, 'one', 'select');
                    if (is_array($productVariant) && count($productVariant) > 0) {
                        //update main product status(s)
                        $query = "update walmart_product_variants set status='" . $value['publishedStatus'] . "' where option_id='" . $productVariant['option_id'] . "'";
                        Data::sqlRecords($query, null, 'update');

                        if (!isset($product['sku']))
                            $count++;

                        if (isset($errors[$value['sku']]))
                            unset($errors[$value['sku']]);

                    } elseif(!$flag) {
                        if (!isset($errors[$value['sku']]))
                            $errors[$value['sku']] = $value['sku'] . " :  This sku not found on app.";
                    }
                }

                if (count($errors)) {
                    $returnArr['error'] = implode('<br>', $errors);
                }
            }

            if ($finish || $finishWithError) {
                if ($finish) {
                    //update all product having status is "item_processing"
                    /*$query = "update `walmart_product` set status='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' where status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "' and merchant_id='" . $merchant_id . "'";*/
                    //by shivam
                    /*$query = "UPDATE `walmart_product` `wp` LEFT JOIN `walmart_product_variants` `wpv` on `wp`.`product_id`=`wpv`.`product_id` SET `wp`.`status`='".WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED."',`wpv`.`status`='".WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED."' WHERE (`wp`.`status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING."' OR `wpv`.`status`='".WalmartProduct::PRODUCT_STATUS_PROCESSING."') AND `wp`.`merchant_id`='".$merchant_id."'";*/
//                    $query = "UPDATE `walmart_product` `wp` LEFT JOIN `walmart_product_variants` `wpv` on `wp`.`product_id`=`wpv`.`product_id` SET `wp`.`status`='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "',`wpv`.`status`='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE (`wp`.`status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "' OR `wpv`.`status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "') AND `wp`.`merchant_id`='" . $merchant_id . "'";
                    $query1 = "UPDATE `walmart_product` SET `status`='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE `status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "' AND `merchant_id`='" . $merchant_id . "'";
                    Data::sqlRecords($query1, null, 'update');

                    $query = "UPDATE `walmart_product_variants` SET `status`='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE `status`='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "' AND `merchant_id`='" . $merchant_id . "'";
                    Data::sqlRecords($query, null, 'update');
                }
                $returnArr['finish'] = true;
            } else {
                $returnArr['success']['count'] = $count;
            }
            return json_encode($returnArr);
        } catch (Exception $e) {
            $returnArr['error'] = $e->getMessage();
            return json_encode($returnArr);
        }
    }

    public function actionGetwalmartdata()
    {
        $this->layout = 'main2';
        $html = '';
        $sku = trim(Yii::$app->request->post('id'));
        $merchant_id = Yii::$app->request->post('merchant_id');
        $resultItems = $this->walmartHelper->getItem($sku);
        $resultInventory = $this->walmartHelper->getInventory($sku);
        $result_array = [];
        if (is_array($resultItems) && isset($resultItems['MPItemView'][0]) && count($resultItems) > 0) {
            $result_array = array_merge($resultItems['MPItemView'][0], $resultInventory);
            $html = $this->render('view', array('data' => $result_array), true);
        }
        return $html;
    }

    public function actionGettaxcode()
    {
        $this->layout = 'main2';
        $html = '';
        $id = trim(Yii::$app->request->post('id'));
        $query = "select tax_code,cat_desc from walmart_tax_codes where 1";
        $TaxCollection = Data::sqlRecords($query, 'all', 'select');
        if (is_array($TaxCollection) && count($TaxCollection) > 0) {
            $html = $this->render('productTax', array('taxCollection' => $TaxCollection), true);
        }
        return $html;
    }

    public function actionErrorwalmart()
    {
        $this->layout = "main2";
        $id = trim(Yii::$app->request->post('id'));
        $merchant_id = Yii::$app->request->post('merchant_id');

        $errorData = array();
        $connection = Yii::$app->getDb();
        $errorData = $connection->createCommand('SELECT `error` from `walmart_product` where merchant_id="' . $merchant_id . '" AND `id`="' . $id . ' LIMIT 0, 1"')->queryOne();

        $html = $this->render('errors', array('data' => $errorData), true);
        $connection->close();
        return $html;
    }

    public function actionGetwrongupc()
    {
        $query = "select id,sku,upc,variant_id from jet_product where type='variants'";
        $proCollection = Data::sqlRecords($query, "all", "select");
        $count = 0;
        if (isset($proCollection) && count($proCollection) > 0) {
            foreach ($proCollection as $value) {
                $query = "select option_unique_id from jet_product_variants where option_id='" . $value['variant_id'] . "'";
                $varCollection = Data::sqlRecords($query, "one", "select");
                if (isset($varCollection) && $varCollection['option_unique_id'] != $value['upc']) {
                    $count++;
                    echo $value['id'] . " , sku " . $value['sku'] . " ,Upc " . $value['upc'] . "<br>";
                }
            }
        }
        echo $count;
    }

    public function actionInventoryupdate()
    {
        $cron_array = array();
        //$connection = Yii::$app->getDb();
        $cronData = WalmartCronSchedule::find()->where(['cron_name' => 'fetch_inventory'])->one();
        if ($cronData && $cronData['cron_data'] != "") {
            $cron_array = json_decode($cronData['cron_data'], true);
        } else {
            $cron_array = Walmartappdetails::getConfig();
        }
        $start = 0;
        $countArr = 0;
        $updateInventory = [];
        if (is_array($cron_array) && count($cron_array) > 0) {
            foreach ($cron_array as $k => $Config) {
                try {
                    $isError = false;
                    $merchant_id = $k;
                    $count = 0;
                    $consumer_id = $Config['consumer_id'];
                    $secret_key = $Config['secret_key'];
                    $channel_type_id = $Config['consumer_channel_type_id'];
                    $walmartAPi = new Walmartapi($consumer_id, $secret_key, $channel_type_id);
                    $countArr++;
                    unset($cron_array[$k]);
                    //$query = 'select jet.id,sku,type,qty,fulfillment_lag_time from `walmart_product` wal INNER JOIN `jet_product` jet ON jet.id=wal.product_id where wal.status!="Not Uploaded" and wal.merchant_id="' . $merchant_id . '"';
                    $query = 'select jet.id,sku,type,qty,fulfillment_lag_time from (SELECT * FROM `walmart_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as wal INNER JOIN (SELECT * FROM `jet_product` WHERE `merchant_id`="' . MERCHANT_ID . '") as jet ON jet.id=wal.product_id where wal.status!="' . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . '" and wal.merchant_id="' . MERCHANT_ID . '" ';
                    $product = Data::sqlRecords($query, "all", "select");
                    if (is_array($product) && count($product) > 0) {
                        $response = [];
                        $response = $walmartAPi->updateInventoryOnWalmart($product, "product");
                        //var_dump($response);die("Xvcb");
                        if (isset($response['errors']))
                            $error++;
                        else
                            $count = count($product);
                    }
                    $updateInventory[$merchant_id] = $count;
                    unset($cron_array[$k]);
                    if ($countArr >= 20)
                        break;
                } catch (Exception $e) {
                    Data::createLog("product status exception " . $e->getTraceAsString(), 'productInventory/exception.log', 'a', true);
                }
            }
        }
        if (count($cron_array) == 0)
            $cronData->cron_data = "";
        else
            $cronData->cron_data = json_encode($cron_array);
        $cronData->save(false);
    }

    //by shivam

    public function actionBatchretire()
    {
        $session = Yii::$app->session;

        $selection = isset($session['retire_product']) ? $session['retire_product'] : [];

        $index = Yii::$app->request->post('index');

        if (!empty($selection)) {
            //foreach ($selection as $id ) {
            $id = $selection[$index];

            $query = Data::sqlRecords('SELECT sku,type FROM `jet_product` WHERE id="' . $id . '" AND merchant_id="' . MERCHANT_ID . '" ', 'one');

            if (isset($query) && !empty($query)) {
                if ($query['type'] == 'variants') {
                    $skus = Data::sqlRecords('SELECT option_sku FROM `jet_product_variants` WHERE product_id="' . $id . '" AND merchant_id="' . MERCHANT_ID . '" ', null, 'all');
                    if (!is_array($skus) || (is_array($skus) && !count($skus)))
                        $skus = [];

                } else {
                    $skus[0]['option_sku'] = $query['sku'];
                }

                $errors = [];
                $success = [];
                foreach ($skus as $sku) {

                    $retireProduct = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                    $feed_data = $retireProduct->retireProduct($sku['option_sku']);

                    if (isset($feed_data['ItemRetireResponse'])) {
                        $success[] = '<b>' . $feed_data['ItemRetireResponse']['sku'] . ' : </b>' . $feed_data['ItemRetireResponse']['message'];
                    } elseif (isset($feed_data['errors']['error'])) {
                        if (isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku") {
                            $errors[] = $sku['option_sku'] . ' : Product not Uploaded on Walmart.';
                        } else {
                            $errors[] = $sku['option_sku'] . ' : ' . $feed_data['errors']['error']['description'];
                        }
                    }
                }
                if (count($errors)) {
                    $returnArr['error'] = true;
                    $returnArr['error_msg'] = implode('<br/>', $errors);
                }
                if (count($success)) {
                    $returnArr['success'] = true;
                    $returnArr['success_count'] = count($success);
                    $returnArr['success_msg'] = implode('<br/>', $success);
                }
            }
        } else {
            $returnArr = ['error' => 'Product Id :Not Found'];
        }

        return json_encode($returnArr);
    }

    /**
     * Update Price in Bulk.
     * @return mixed
     */
    public function actionBatchUpdatePrice()
    {
        $returnArr =[];
        $session = Yii::$app->session;
        $data = [];
        $index = Yii::$app->request->post('index');
        $selection = isset($session['batch_update_price'][$index]) ? $session['batch_update_price'][$index] : [];
        $count = count($selection);
        if (!$count) {
            $returnArr = ['error' => true, 'message' => 'No Products to Upload'];
        } else {
            $index = Yii::$app->request->post('index');
            $error = [];
            if (!empty($selection)) {
                $response = $this->walmartHelper->batchupdatePriceOnWalmart($selection, "product");
                if (isset($response['feedId'])) {
                    if (isset($response['erroredSkus'])) {
                        $returnArr = ['success' => true, 'count' => $count - $response['error_count'], 'erroredSkus' => json_encode($response['erroredSkus']), 'error_count' => $response['error_count']];
                    } else {
                        $returnArr = ['success' => true, 'count' => $count];
                    }

                } elseif (isset($response['erroredSkus']) && !empty($response['erroredSkus'])) {
                    $returnArr = ['erroredSkus' => json_encode($response['erroredSkus']), 'error_count' => $response['error_count']];
                } elseif (isset($response['errors'])) {
                    if (isset($response['erroredSkus']) && !empty($response['erroredSkus'])) {
                        $returnArr = ['erroredSkus' => json_encode($response['erroredSkus']), 'error_count' => $response['error_count'], 'error' => $response['errors']['error']];
                    } else {
                        $returnArr = ['error_count' => $response['error_count'], 'error' => $response['errors']['error']];
                    }
                }

            }
            return json_encode($returnArr);
        }
    }

    /**
     * Update Inventory in Bulk.
     * @return mixed
     */
    public function actionBatchUpdateInventory()
    {
        $returnArr=[];
        $session = Yii::$app->session;
        $data = [];
        $index = Yii::$app->request->post('index');
        $selection = isset($session['batch-update-inventory'][$index]) ? $session['batch-update-inventory'][$index] : [];
        $count = count($selection);
        if (!$count) {
            $returnArr = ['error' => true, 'message' => 'No Products to Upload'];
        } else {
            $index = Yii::$app->request->post('index');
            $error = [];
            if (!empty($selection)) {
                $response = $this->walmartHelper->batchupdateInventoryOnWalmart($selection, "product");
                if (isset($response['feedId'])) {
                    if (isset($response['erroredSkus'])) {
                        $returnArr = ['success' => true, 'count' => $count - $response['error_count'], 'erroredSkus' => json_encode($response['erroredSkus']), 'error_count' => $response['error_count']];
                    } else {
                        $returnArr = ['success' => true, 'count' => $count];
                    }

                } elseif (isset($response['erroredSkus']) && !empty($response['erroredSkus'])) {
                    $returnArr = ['erroredSkus' => json_encode($response['erroredSkus']), 'error_count' => $response['error_count']];
                } elseif (isset($response['errors'])) {
                    if (isset($response['erroredSkus']) && !empty($response['erroredSkus'])) {
                        $returnArr = ['erroredSkus' => json_encode($response['erroredSkus']), 'error_count' => $response['error_count'], 'error' => $response['errors']['error']];
                    } else {
                        $returnArr = ['error_count' => $response['error_count'], 'error' => $response['errors']['error']];
                    }
                }
                /*   else{
                      $returnArr = ['success' => true, 'count' => $count];
                   }*/

            }
            return json_encode($returnArr);
        }
    }

    public function actionBulkproductstatus()
    {

        $session = Yii::$app->session;

        $selection = isset($session['product_status']) ? $session['product_status'] : [];

        $index = Yii::$app->request->post('index');
        if (!empty($selection)) {
            //foreach ($selection as $id) {
            $id = $selection[$index];

            //$result = Data::sqlRecords('SELECT sku,type FROM `jet_product` WHERE id="' . $id . '" AND merchant_id="' . MERCHANT_ID . '" LIMIT 0,1 ', 'one');

            $query = "SELECT sku,type FROM (SELECT * FROM `jet_product` WHERE `merchant_id`='" . MERCHANT_ID . "' AND `id`='" . $id . "') as `jp` INNER JOIN (SELECT * FROM `walmart_product` WHERE `merchant_id`='" . MERCHANT_ID . "' AND `product_id`='" . $id . "') as `wp` ON `jp`.`id`=`wp`.`product_id` WHERE `wp`.`merchant_id`='" . MERCHANT_ID . "' ";
            $result = Data::sqlRecords($query, 'one');

            if (isset($result['sku']) && !empty($result)) {
                if ($result['type'] == 'variants') {
//                    $skus = Data::sqlRecords('SELECT option_id,option_sku FROM `jet_product_variants` WHERE product_id="' . $id . '" AND merchant_id="' . MERCHANT_ID . '" ', null, 'all');
                    $query = "SELECT option_sku,`jvp`.option_id FROM (SELECT * FROM `jet_product_variants` WHERE `merchant_id`='" . MERCHANT_ID . "' AND `product_id`='" . $id . "') as `jvp` INNER JOIN (SELECT * FROM `walmart_product_variants` WHERE `merchant_id`='" . MERCHANT_ID . "' AND `product_id`='" . $id . "') as `wvp` ON `jvp`.`option_id`=`wvp`.`option_id` WHERE `wvp`.`merchant_id`='" . MERCHANT_ID . "' ";
                    $skus = Data::sqlRecords($query, 'all');

                    //$skus = Data::sqlRecords('SELECT option_id,option_sku FROM `jet_product_variants` WHERE product_id="' . $id . '" AND merchant_id="' . MERCHANT_ID . '" ', null, 'all');
                    if (is_array($skus) && count($skus)) {
                        $error = [];
                        $uploadCount = 0;
                        $notUploadCount = 0;
//                        $mainProductStatus = WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED;
                        foreach ($skus as $sku) {

                            $productStatus = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                            $feed_data = $productStatus->getItemstatus($sku['option_sku']);

                            if (isset($feed_data['error'])) {
                                $notUploadCount++;
//                                $error[] =  'Error : '.$sku['option_sku'].$feed_data['error'][0]['info'];
                                if ($feed_data['error'][0]['code'] == 'CONTENT_NOT_FOUND.GMP_ITEM_QUERY_API') {

                                    $error[] = 'Error : ' . $sku['option_sku'] . ' : Product not uploaded on Walmart';

                                } else {
                                    $error[] = 'Error : ' . $sku['option_sku'] . ' : ' . $feed_data['error'][0]['info'];
                                }

                                $query = "UPDATE walmart_product_variants SET status='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE option_id='" . $sku['option_id'] . "' AND `merchant_id`='" . MERCHANT_ID . "'";

                            } elseif (isset($feed_data['MPItemView'])) {
                                $uploadCount++;
                                if ($result['sku'] == $sku['option_sku']) {
                                    $mainProductStatus = $feed_data['MPItemView'][0]['publishedStatus'];
                                }

                                $status = $feed_data['MPItemView'][0]['publishedStatus'];
                                $query = "UPDATE walmart_product_variants SET status='" . $status . "' WHERE option_id='" . $sku['option_id'] . "' AND `merchant_id`='" . MERCHANT_ID . "'";
                                Data::sqlRecords($query, null, 'update');

                            } else {
                                $notUploadCount++;
                                $error[] = 'Status Not Updated for variant sku : ' . $sku['option_sku'] . ' of product sku : ' . $skus['sku'];
                            }
                        }

                        //update main product status
                        if ($uploadCount) {
                            if ($notUploadCount) {
                                $query = "UPDATE walmart_product SET status='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE product_id='" . $id . "' AND `merchant_id`='" . MERCHANT_ID . "'";
                            } else {
                                $query = "UPDATE walmart_product SET status='" . WalmartProduct::PRODUCT_STATUS_UPLOADED . "' WHERE product_id='" . $id . "' AND `merchant_id`='" . MERCHANT_ID . "'";
                            }
                        } else {
                            $query = "UPDATE walmart_product SET status='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE product_id='" . $id . "' AND `merchant_id`='" . MERCHANT_ID . "'";
                        }
                        Data::sqlRecords($query, null, 'update');
                        //end

                        if (count($error)) {
                            $returnArr['error'] = implode('<br>', $error);
                        } else {
                            $returnArr = ['success' => ['count' => $uploadCount, 'message' => 'Status Successfully Updated']];
                        }
                    } else {
                        $returnArr['error'] = 'Product not found on Walmart';
                    }
                } else {

                    $productStatus = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);
                    $feed_data = $productStatus->getItemstatus($result['sku']);

                    if (isset($feed_data['MPItemView'])) {

                        $status = $feed_data['MPItemView'][0]['publishedStatus'];

                        //update main product status(s)
                        $query = "UPDATE walmart_product SET status='" . $status . "' WHERE product_id='" . $id . "' AND `merchant_id`='" . MERCHANT_ID . "'";
                        Data::sqlRecords($query, null, 'update');

                        $returnArr = ['success' => ['count' => 1, 'message' => 'Status Successfully Updated']];

                    } elseif (isset($feed_data['error'])) {
                        $error[] = $feed_data['error'][0]['info'];

                        if ($feed_data['error'][0]['code'] == 'CONTENT_NOT_FOUND.GMP_ITEM_QUERY_API') {
                            $returnArr['error'] = $result['sku'] . ' : Product not uploaded on Walmart';
                        } else {
                            $returnArr['error'] = $result['sku'] . ' : ' . $feed_data['error'][0]['info'];
                        }
                        $query = "UPDATE walmart_product SET status='" . WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED . "' WHERE product_id='" . $id . "' AND `merchant_id`='" . MERCHANT_ID . "'";
                        Data::sqlRecords($query, null, 'update');

                    } else {
                        $returnArr['error'] = $result['sku'] . ' : ' . ' Status Not Updated';
                    }

                }
            } else {
                $returnArr['error'] = 'Product Id :' . $id . ' Not Found';
            }

            //}
        }
        return json_encode($returnArr);

    }

    public function actionBatchproductupdate()
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $merchant_id = MERCHANT_ID;
        $query = "SELECT COUNT(id) as ids FROM jet_product_tmp WHERE merchant_id='" . MERCHANT_ID . "'";
        $collection = Data::sqlRecords($query, "all", "select");
        if (is_array($collection) && isset($collection[0]['ids']) && $collection[0]['ids'] > 0) {
            if ($collection[0]['ids'] <= 100)
                $pages = 1;
            else
                $pages = ceil($collection[0]['ids'] / 100);
            $customPrice = "";
            $session = Yii::$app->session;

            // data from jet configuration

            $jet_configuration = Data::sqlRecords('SELECT * FROM `jet_configuration` WHERE `merchant_id`="' . $merchant_id . '"', 'one');
//            $jet_configuration = Data::sqlRecords('SELECT * FROM `jet_configuration` WHERE `merchant_id`=14', 'one');
            if (!empty($jet_configuration)) {
                $query = 'SELECT `data`,`value` from `jet_config` where merchant_id="' . $merchant_id . '" AND data="fixed_price"';
                $customData = Data::sqlRecords($query, "one", "select");
                $query = 'SELECT `data`,`value` from `jet_config` where merchant_id="' . $merchant_id . '" AND data="set_price_amount"';
                $setCustomPrice = Data::sqlRecords($query, "one", "select");
                if (is_array($customData) && $customData['value'] == 'yes') {
                    $customPrice = $customData['value'];
                }
                $newCustomPrice = '';
                if (is_array($setCustomPrice) && isset($setCustomPrice['value'])) {
                    $newCustomPrice = $setCustomPrice['value'];
                }
                $jetHelper = new Jetapimerchant($jet_configuration['api_host'], $jet_configuration['api_user'], $jet_configuration['api_password']);

                $session->set('jetHelper', serialize($jetHelper));
                $session->set('jetConfig', $jet_configuration);
                $session->set('customPrice', $customPrice);
                $session->set('newCustomPrice', $newCustomPrice);
            }
            $session->set('product_page', $pages);
            $session->set('merchant_id', $merchant_id);

            $session->close();

            return $this->render('batchproductupdate',
                [
                    'totalcount' => $collection[0]['ids'],
                    'pages' => $pages
                ]);
        } else {
            Yii::$app->session->setFlash('success', "All Store Product(s) already synced.");
        }
        return $this->redirect(['index']);

    }

    public function actionStartbatchupdate()
    {

        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(Data::getUrl('site/login'));
        }
        $session = Yii::$app->session;

        $message = "";
        $return_msg = $productUpdate = array();

        $index = Yii::$app->request->post('index');
        $merchant_id = MERCHANT_ID;
        $productCount = $session->get('product_page');
        $offset = $index * 100;

        $productUpdate = Data::sqlRecords('SELECT `product_id`,`data` FROM `jet_product_tmp` WHERE merchant_id="' . $merchant_id . '" ORDER BY `jet_product_tmp`.`id`  ASC LIMIT ' . $offset . ',100', 'all');

        if (!empty($productUpdate) && count($productUpdate) > 0) {
            $count = 0;
            foreach ($productUpdate as $value) {

                $path = \Yii::getAlias('@webroot') . '/var/product/update/' . $merchant_id . '/' . date('d-m-Y');
                if (!file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $file = fopen($path . '/' . $value['product_id'] . '.log', 'w');
                $data = $result = array();

                $query = 'SELECT * FROM `jet_product` WHERE `id`="' . $value['product_id'] . '" LIMIT 0,1';
                $result = Data::sqlRecords($query, "one", "select");

                //upload on jet

                if (isset($session['jetConfig'])) {
                    $jetConfiguration = $session['jetConfig'];
                    $jetHelper = $session['jetHelper'];
                    $customPrice = $session['customPrice'];
                    $newCustomPrice = $session['newCustomPrice'];

                    if (!empty($jetConfiguration)) {

                        if (!empty($result)) {
                            $result = (object)$result;
                            $count++;
                            $data = json_decode($value['data'], true);
                            if (is_array($data) && count($data) > 0)
                                Jetproductinfo::productUpdateData($result, $data, $jetHelper, $jetConfiguration['fullfilment_node_id'], $merchant_id, $file, $customPrice, $newCustomPrice);
                        }
                    }
                }

                //upload on walmart

                $jet_products = json_decode($value['data'], true);
                $selectedProducts[] = $jet_products['id'];

                $productResponse = $this->walmartHelper->createProductOnWalmart($selectedProducts, $this->walmartHelper, MERCHANT_ID);
                if (is_array($productResponse) && isset($productResponse['uploadIds'], $productResponse['feedId']) && count($productResponse['uploadIds'] > 0)) {
                    //save product status and data feed
                    $ids = implode(',', $productResponse['uploadIds']);
                    foreach ($productResponse['uploadIds'] as $val) {
                        $query = "UPDATE `walmart_product` SET status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', error='' where product_id='" . $val . "'";
                        Data::sqlRecords($query, null, "update");
                    }
                    $feed_file = isset($productResponse['feed_file']) ? $productResponse['feed_file'] : '';
                    $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('" . MERCHANT_ID . "','" . $productResponse['feedId'] . "','" . $ids . "','" . $feed_file . "')";
                    Data::sqlRecords($query, null, "insert");

                    $msg = "product feed successfully submitted on walmart.";
                    $feed_count = count($productResponse['uploadIds']);
                    $feedId = $productResponse['feedId'];
                    $returnArr = ['success' => true, 'message' => $msg, 'count' => $feed_count, 'feed_id' => $feedId];
                } elseif (isset($productResponse['errors'])) {
                    $msg = json_encode($productResponse['errors']);
                    $returnArr = ['error' => true, 'message' => $msg];
                } elseif (isset($productResponse['feedError'])) {
                    $msg = json_encode($productResponse['feedError']);
                    $returnArr = ['error' => true, 'message' => $msg];
                }

                //save errors in database for each errored product
                $returnArr['error_count'] = 0;
                if (isset($productResponse['erroredSkus'])) {
                    foreach ($productResponse['erroredSkus'] as $productSku => $error) {
                        if (is_array($error))
                            $error = implode(',', $error);

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id SET wp.`error`='" . addslashes($error) . "' where jp.sku='" . $productSku . "'";
                        Data::sqlRecords($query, null, "update");
                    }
                    $returnArr['error_count'] = count($productResponse['erroredSkus']);
                    $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['erroredSkus']));
                }

            }
            /*$query = 'DELETE FROM `jet_product_tmp` where merchant_id="' . $merchant_id . '"';
            Data::sqlRecords($query, null, "delete");*/

            $return_msg['success']['message'] = "Product(s) information successfully updated";
            $return_msg['success']['count'] = $count;
        } else {
            $return_msg['success']['message'] = "Product(s) information successfully updated";
            $return_msg['success']['count'] = 0;
        }

        if ((count($productCount) - 1) == $index) {
            $session->remove('product_page');
            //fclose($file);
        }
        return json_encode($return_msg);
    }

    // end by shivam

    public function actionGetpromostatus()
    {
        $query = "SELECT `product_id` FROM `walmart_product` WHERE merchant_id='" . MERCHANT_ID . "'";
        $productIds = Data::sqlRecords($query, null, 'all');
        if (!is_array($productIds) || (is_array($productIds) && !count($productIds)))
            $productIds = [];
        $productIds = array_column($productIds, 'product_id');
        $result = WalmartPromoStatus::getPromoStatus($productIds);
        if (!$result) {
            Yii::$app->session->setFlash('error', "No Product(s) available...");
        }
        if (!is_array($result) && is_string($result)) {
            Yii::$app->session->setFlash('error', "No Product(s) promo price available...");
        }
        if (is_array($result) && count($result) == 0) {
            Yii::$app->session->setFlash('error', "Error Occured. Please try again..");
        }
        if (is_array($result) && count($result) > 0 && isset($result['exception']) && strlen($result['exception']) > 0) {
            Yii::$app->session->setFlash('error', "Error Occured in Processing. Please try again..");
        } elseif (is_array($result) && count($result) > 0) {
            Yii::$app->session->setFlash('success', "Successfully fetched product(s) promo price status.");
        }
        return $this->redirect(['index']);
    }

    //start by shivam

    public function actionSyncproductstore()
    {

        $session = "";
        $session = Yii::$app->session;
        $connection = "";
        $merchant_id = MERCHANT_ID;
        $shopname = SHOP;
        $token = TOKEN;
        $countProducts = 0;
        $pages = 0;
        $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
        $import_option = Data::getConfigValue($merchant_id, 'import_product_option');
        if ($import_option) {
            $countProducts = $sc->call('GET', '/admin/products/count.json', array('published_status' => $import_option));
        }
        else{
            $countProducts = $sc->call('GET', '/admin/products/count.json', array('published_status' => 'any'));
        }
        

        if ($countProducts['errors']) {
            Yii::$app->session->setFlash('error', " Product doesn't exist ! ");
            return $this->redirect(['index']);

        }
        $pages = ceil($countProducts / 250);

        if (!is_object($session)) {
            Yii::$app->session->setFlash('error', "Can't initialize Session.Product(s) Sync cancelled.");
            return $this->redirect(['index']);
        }

        $session->set('product_page', $pages);
        $session->set('shopify_object', serialize($sc));
        $session->set('select_value', serialize(Yii::$app->request->post()));

        return $this->render('syncprod', [
            'totalcount' => $countProducts,
            'pages' => $pages
        ]);

    }


    public function actionShopifyproductsync()
    {
        $session = Yii::$app->session;
        $index = Yii::$app->request->post('index');
        $countUpload = Yii::$app->request->post('count');
        $returnArr = $products = array();
        $jProduct = 0;

        try {

            $pages = 0;
            $pages = $session->get('product_page');
            $sc = unserialize($session->get('shopify_object'));
            $sync = unserialize($session->get('select_value'));
            //parse_str($select_value,$sync);
            $merchant_id = MERCHANT_ID;
            $shopname = SHOP;
            $token = TOKEN;

            if (!is_object($sc)) {
                $sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);
            }
            // Get all products
            $limit = 250;
            $import_option = Data::getConfigValue($merchant_id, 'import_product_option');
            if ($import_option) {
                $products = $sc->call('GET', '/admin/products.json', array('published_status' => $import_option, 'limit' => $limit, 'page' => $index));
            } else {
                $products = $sc->call('GET', '/admin/products.json', array('published_status' => 'any', 'limit' => $limit, 'page' => $index));
            }
            if ($products && is_array($products)) {
                foreach ($products as $value) {
                    $response = Jetproductinfo::updateDetails($value,$sync,$merchant_id);
                    $jProduct += $response;
                }
                /*return json_encode(['success'=>true, 'message'=>'Product Synced Successfully!!']);*/
            } else {
                /*return json_encode(['error'=>true, 'message'=>"Product doesn't exist on Shopify."]);*/
                $returnArr = ['error' => true, 'message' => "Product doesn't exist on Shopify."];
            }
        } catch (Exception $e) {
            /*return json_encode(['error'=>true, 'message'=>"Error : ".$e->getMessage()]);*/
            $returnArr = ['error' => true, 'message' => "Error : " . $e->getMessage()];
        }
        if ($jProduct)
            $returnArr['success']['count'] = $jProduct;

        return json_encode($returnArr);

    }


    public function actionSaveDescription()
    {
        $description = Yii::$app->request->post('description', false);
        $product_id = Yii::$app->request->post('product_id', false);
        if ($product_id && $description && is_numeric($product_id)) {
            $maxLength = WalmartProductValidate::MAX_LENGTH_LONG_DESCRIPTION;
            //htmlspecialchars($description,ENT_XHTML);
            $length = strlen($description);
            if ($length > $maxLength) {
                return json_encode(['error' => true, 'message' => 'Description Should be less than '.WalmartProductValidate::MAX_LENGTH_LONG_DESCRIPTION.' characters.']);
            } else {
                $query = "UPDATE `walmart_product` SET `long_description`='" . addslashes($description) . "' WHERE `product_id`='" . $product_id . "'";
                Data::sqlRecords($query, null, 'update');

                return json_encode(['success' => true, 'message' => 'Description saved successfully.']);
            }
        } else {
            return json_encode(['error' => true, 'message' => 'Please Provide Valid Data.']);
        }
    }

    public function actionUpdatewalmartprice()
    {
        $product = Yii::$app->request->post();
        $returnArr = ['error' => true];

        $count = count($product);

        $errors = [];

        if ($count) {
            $response = $this->walmartHelper->updateWalmartprice($product);
            if(isset($product['remove']) && $product['remove']){
                if ($product['type'] == 'simple') {
                    $query = "UPDATE `walmart_product_repricing` SET `repricing_status` = '0' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `product_id`='" . $product['id'] . "' ";
                } else {
                    $query = "UPDATE `walmart_product_variants` SET `option_prices` = '" . $product['price'] . "' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `product_id`='" . $product['id'] . "' AND `option_id`='" . $product['option_id'] . "'";
                }

                Data::sqlRecords($query, null, 'update');

            }
            if (isset($response['errors'])) {
                $returnArr = ['error' => "Price Feed Error : Price not updated on walmart", 'message' => 'Price for some Products is not updated due to ' . json_encode($response['errors'])];
            } else {
                if ($product['type'] == 'simple') {
                    $query = "UPDATE `walmart_product` SET `product_price` = '" . $product['price'] . "' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `product_id`='" . $product['id'] . "' ";
                } else {
                    $query = "UPDATE `walmart_product_variants` SET `option_prices` = '" . $product['price'] . "' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `product_id`='" . $product['id'] . "' AND `option_id`='" . $product['option_id'] . "'";
                }
                Data::sqlRecords($query, null, 'update');

                if(isset($product['remove']) && $product['remove']){
                    $query = "UPDATE `walmart_product_repricing` SET `repricing_status` = '0' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `sku`='".$product['sku']."'";
                }

                $returnArr = ['success' => true, 'count' => $count,'message'=>'Price Feed successfully submitted on walmart'];
            }
        }
        return json_encode($returnArr);

    }

    public function actionUpdatewalmartinventory()
    {
        $product = Yii::$app->request->post();
        $returnArr = ['error' => true];

        $count = count($product);

        $errors = [];

        if ($count) {
            $response = $this->walmartHelper->updateWalmartinventory($product);
            if (isset($response['errors'])) {
                $returnArr = ['error' => "Inventory Feed Error : Inventory not updated on walmart", 'message' => 'Inventory for some Products is not updated due to ' . json_encode($response['errors'])];

                return json_encode($returnArr);

            } else {
                if ($product['type'] == 'simple') {
                    $query = "UPDATE `jet_product` SET `qty` = '" . $product['qty'] . "' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `id`='" . $product['id'] . "' ";
                } else {
                    $query = "UPDATE `jet_product_variants` SET `option_qty` = '" . $product['qty'] . "' WHERE `merchant_id`= '" . MERCHANT_ID . "' AND `product_id`='" . $product['id'] . "' AND `option_id`='" . $product['option_id'] . "'";
                }
                Data::sqlRecords($query, null, 'update');
                $returnArr = ['success' => true, 'count' => $count];
            }

            /*update inventory on shopify store*/
            $sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);

            if ($product['type'] == 'simple') {
                $updateInventory['variant'] = array(
                    "id" => $product['variant_id'],
                    "inventory_quantity" => $product['qty'],
                );

                $id = trim($product['variant_id']);
            } else {
                $updateInventory['variant'] = array(
                    "id" => $product['option_id'],
                    "inventory_quantity" => $product['qty'],
                );
                $id = trim($product['option_id']);
            }

            $response = $sc->call('PUT', '/admin/variants/' . $id . '.json', $updateInventory);

            $returnArr = ['success' => true, 'message' => 'Inventory feed successfully submitted on walmart'];
            /*if (isset($response['errors']) || empty($response)) {
                $returnArr = ['error' => true, 'message' => 'Product not found on shopify'];

            } else {
                if (isset($response['inventory_quantity']) && $response['inventory_quantity'] == $product['qty']) {
                    $returnArr = ['success' => true,'message'=>'Inventory feed successfully submitted on walmart'];
                } else {
                    $returnArr = ['success' => true,'message'=>'Inventory feed successfully submitted on walmart'];
                }
            }*/
        }
        return json_encode($returnArr);

    }

    /**
    * Check Product repricing enable or not.
    * frontend\modules\walmart\components\WalmartRepricing 
    * @return array json
    */
     public function actionCheckrepricing(){
        $return_array=[];
        $product = Yii::$app->request->post();
        unset($product['option_id']);
        $check = WalmartRepricing::isRepricingEnabled($product);
        if($check){
            $return_array['success']=true;
        }
        else{
            $return_array['success']=false;
        }
        return json_encode($return_array);
        
     }

     public static function deleteProduct($product,$all=false)
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

    public function actionUploadallproducts()
    {
        $merchant_id = MERCHANT_ID;
        if (WalmartProductComponent::canSendItemFeed($merchant_id)) {
            $session = Yii::$app->session;

            $dir = Yii::getAlias('@webroot') . WalmartProductComponent::ALL_PRODUCT_UPLOAD_FILEPATH;
            $filePath = $dir . $merchant_id . '.php';
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $last_send_index = false;

            $feed_type = WalmartProductComponent::FEED_TYPE_ITEM;
            $_query = "SELECT `last_send_index` FROM `walmart_feed_stats` WHERE `merchant_id`={$merchant_id} AND `feed_type`='{$feed_type}' LIMIT 0,1";
            $result = Data::sqlRecords($_query, 'one');
            if ($result) {
                $last_send_index = $result['last_send_index'];
                Data::sqlRecords("DELETE FROM `walmart_feed_stats` WHERE `merchant_id`={$merchant_id} AND `feed_type`='{$feed_type}'", null, 'delete');
            }

            if ($last_send_index !== false) {
                $query = "SELECT `wal`.`id`,`wal`.`product_id` FROM `walmart_product` `wal` WHERE `merchant_id`=" . $merchant_id . " AND `id` >= {$last_send_index} ORDER BY `wal`.`id` ASC";
            } else {
                $query = "SELECT `wal`.`id`,`wal`.`product_id` FROM `walmart_product` `wal` WHERE `merchant_id`=" . $merchant_id . " ORDER BY `wal`.`id` ASC";
            }

            $product = Data::sqlRecords($query, "all", "select");

            $Productcount = count($product);

            if (is_array($product) && $Productcount) {
                $size_of_request = 50; //Number of products to be uploaded at once(in single feed)
                $pages = (int)(ceil($Productcount / $size_of_request));

                $selectedProducts = array_chunk($product, $size_of_request);
                $session->set('all_products_for_upload', $selectedProducts);
                $session->close();

                return $this->render('uploadallproducts', [
                    'totalcount' => $Productcount,
                    'pages' => $pages
                ]);
            } else {
                Yii::$app->session->setFlash('error', "No Products Found..");
            }
        } else {
            Yii::$app->session->setFlash('error', "Threshold Limit Exceeded. Please try again after 1 Hour.");
        }
        return $this->redirect(['index']);
    }

    public function actionAjaxuploadallproducts()
    {
        $session = Yii::$app->session;

        $returnArr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['all_products_for_upload'][$index]) ? $session['all_products_for_upload'][$index] : [];
        $count = count($selectedProducts);

        if (!$count) {
            $returnArr = ['error' => true, 'error_msg' => 'No Products to Upload'];
        } else {
            $connection = Yii::$app->getDb();

            $merchant_id = Yii::$app->user->identity->id;

            try {
                $walmart_product = new WalmartProductComponent(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

                $productResponse = $walmart_product->uploadAllProductsOnWalmart($selectedProducts, $merchant_id);

                if (is_array($productResponse) && isset($productResponse['uploadIds']) && count($productResponse['uploadIds'] > 0)) {
                    //save product status and data feed
                    $ids = implode(',', $productResponse['uploadIds']);
                    foreach ($productResponse['uploadIds'] as $val) {
                        $query = "UPDATE `walmart_product` SET status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', error='' where product_id='" . $val . "'";
                        Data::sqlRecords($query, null, "update");
                    }

                    $msg = "product feed successfully submitted on walmart.";
                    $feed_count = count($productResponse['uploadIds']);

                    $returnArr['success'] = true;
                    $returnArr['success_msg'] = $msg;
                    $returnArr['success_count'] = $feed_count;
                }

                if (isset($productResponse['feedId'])) {
                    $feed_file = isset($productResponse['feed_file']) ? $productResponse['feed_file'] : '';

                    $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('" . $merchant_id . "','" . $productResponse['feedId'] . "','" . $ids . "','" . $feed_file . "')";
                    Data::sqlRecords($query, null, "insert");

                    $returnArr['feed_id'] = $productResponse['feedId'];
                }

                //save errors in database for each errored product
                if (isset($productResponse['errors'])) {
                    $_feedError = null;
                    if (isset($productResponse['errors']['feedError'])) {
                        $msg = $productResponse['errors']['feedError'];
                        $_feedError = $msg;
                        unset($productResponse['errors']['feedError']);

                    }

                    foreach ($productResponse['errors'] as $productSku => $error) {
                        if (is_array($error)) {
                            $error = implode(',', $error);
                        }

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($error) . "' where jp.sku='" . $productSku . "'";
                        Data::sqlRecords($query, null, "update");
                    }

                    $returnArr['error'] = true;
                    $returnArr['error_msg'] = $productResponse['errors'];
                    $returnArr['originalmessage'] = $productResponse['originalmessage'];

                    $returnArr['error_count'] = count($productResponse['errors']);
                    $returnArr['erroredSkus'] = implode(',', array_keys($productResponse['errors']));

                    if (!is_null($_feedError)) {
                        $returnArr['feedError'] = $_feedError;
                    }
                }

                if (isset($productResponse['threshold_error'])) {
                    $returnArr = ['threshold_error' => $productResponse['threshold_error'], 'stop' => 1];
                }

            } catch (Exception $e) {
                $returnArr = ['error' => true, 'error_msg' => $e->getMessage()];
            }
        }
        return json_encode($returnArr);
    }

    public function actionTest()
    {
        //$this->walmartHelper->updateSingleInventory();
        $this->walmartHelper->updateSinglePrice();
    }

    /*public function actionUploadallproducts()
    {
        $session = Yii::$app->session;

        $query = "SELECT `wal`.`id`,`wal`.`product_id` FROM `walmart_product` `wal` WHERE `merchant_id`=".MERCHANT_ID." ORDER BY `wal`.`id` ASC";

        $product = Data::sqlRecords($query, "all", "select");

        $Productcount = count($product);

        if (is_array($product) && $Productcount) 
        {
            $size_of_request = 50; //Number of products to be uploaded at once(in single feed)
            $pages = (int)(ceil($Productcount / $size_of_request));

            $selectedProducts = array_chunk($product, $size_of_request);
            $session->set('all_products_for_upload', $selectedProducts);
            $session->close();

            return $this->render('uploadallproducts', [
                'totalcount' => $Productcount,
                'pages' => $pages
            ]);
        } 
        else 
        {
            Yii::$app->session->setFlash('error', "No Products Found..");
            return $this->redirect(['index']);
        }
    }

    public function actionAjaxuploadallproducts()
    {
        $session = Yii::$app->session;

        $returnArr = [];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['all_products_for_upload'][$index]) ? $session['all_products_for_upload'][$index] : [];
        $count = count($selectedProducts);

        if (!$count) {
            $returnArr = ['error' => true, 'error_msg' => 'No Products to Upload'];
        } else {
            $connection = Yii::$app->getDb();

            $merchant_id = Yii::$app->user->identity->id;

            try 
            {
                $walmart_product = new WalmartProductComponent(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

                $productResponse = $walmart_product->uploadAllProductsOnWalmart($selectedProducts,$merchant_id);

                if (is_array($productResponse) && isset($productResponse['uploadIds']) && count($productResponse['uploadIds'] > 0)) 
                {
                    //save product status and data feed
                    $ids = implode(',', $productResponse['uploadIds']);
                    foreach ($productResponse['uploadIds'] as $val) {
                        $query = "UPDATE `walmart_product` SET status='" . WalmartProduct::PRODUCT_STATUS_PROCESSING . "', error='' where product_id='" . $val . "'";
                        Data::sqlRecords($query, null, "update");
                    }

                    $msg = "product feed successfully submitted on walmart.";
                    $feed_count = count($productResponse['uploadIds']);

                    $returnArr['success'] = true;
                    $returnArr['success_msg'] = $msg;
                    $returnArr['success_count'] = $feed_count;
                }

                if(isset($productResponse['feedId']))
                {
                    $feed_file = isset($productResponse['feed_file']) ? $productResponse['feed_file'] : '';

                    $query = "INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`,`feed_file`)VALUES('" . $merchant_id . "','" . $productResponse['feedId'] . "','" . $ids . "','" . $feed_file . "')";
                    Data::sqlRecords($query, null, "insert");

                    $returnArr['feed_id'] = $productResponse['feedId'];
                }

                //save errors in database for each errored product
                if (isset($productResponse['errors'])) 
                {
                    $_feedError = null;
                    if (isset($productResponse['errors']['feedError']))
                    {
                        $msg = $productResponse['errors']['feedError'];
                        $_feedError = $msg;
                        unset($productResponse['errors']['feedError']);

                    }

                    foreach ($productResponse['errors'] as $productSku => $error) 
                    {
                        if (is_array($error)) {
                            $error = implode(',', $error);
                        }

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id AND jp.merchant_id = wp.merchant_id SET wp.`error`='" . addslashes($error) . "' where jp.sku='" . $productSku . "'";
                        Data::sqlRecords($query, null, "update");
                    }
                    if(!is_null($_feedError)) {
                        $returnArr['feedError'] = $_feedError;
                    }
                }

            } catch (Exception $e) {
                $returnArr = ['error' => true, 'error_msg' => $e->getMessage()];
            }
        }
        return json_encode($returnArr);
    }*/
}
