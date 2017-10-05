<?php
namespace frontend\modules\neweggcanada\controllers;

use frontend\modules\neweggcanada\components\categories\Categoryhelper;
use frontend\modules\neweggcanada\components\Data;
use frontend\modules\neweggcanada\components\ShopifyClientHelper;
use frontend\modules\neweggcanada\models\neweggcanadategory;
use frontend\modules\neweggcanada\models\NeweggProductSearch;
use frontend\modules\neweggcanada\components\product\Productimports;
use frontend\modules\neweggcanada\components\product\Productimport;
use frontend\modules\neweggcanada\components\product\ProductApi;
use Yii;
use frontend\modules\neweggcanada\components\Neweggapi;
use frontend\modules\neweggcanada\models\NeweggProduct;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductController implements the CRUD actions for NeweggProduct model.
 */
class ProductController extends NeweggMainController
{
    protected $connection;
    protected $neweggHelper;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    public function beforeAction($action)
    {
        if(parent::beforeAction($action))
        {
            $this->neweggHelper = new Neweggapi(SELLER_ID,AUTHORIZATION,SECRET_KEY);
            return true;
        }
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {

            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        } else {
            $merchant_id = MERCHANT_ID;
            $modelU = "";
            $productPopup = 0;
            $UpdateRows = array();
            $countUpdate = 0;
            $query = "SELECT `id` FROM `insert_product` WHERE merchant_id='" . $merchant_id . "' LIMIT 1";
            $modelU = Data::sqlRecords($query, "one", "select");
            if (!$modelU) {

                $sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
                $countUpload = 0;
                $countUpload = $sc->call('GET', '/admin/products/count.json', array('published_status' => 'published'));
                if (isset($countUpload['errors'])) {
                    return $this->redirect(Yii::$app->getUrlManager()->getBaseUrl() . '/newegg/site/logout', 302);
                }
                $productPopup = 1;
            }

            $searchModel = new NeweggProductSearch();
            //$dataProvider->pagination->pageSize=50;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


            if ($productPopup) {
                return $this->render('index', [
                    'productPopup' => $productPopup,
                    'countUpload' => $countUpload,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            } else {
                //check product available in newegg_product
                $productColl = [];
                $query = "select product_id from `newegg_product` where merchant_id='" . $merchant_id . "'";
                $productColl = Data::sqlRecords($query, 'all', 'select');
                if (!$productColl) {
                    Data::importNeweggProduct($merchant_id);
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
     * @return string|\yii\web\Response
     */
    public function actionBatchimport()
    {
        $merchant_id = MERCHANT_ID;
        $countProducts = 0;
        $pages = 0;

        $sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);
        $countProducts = $sc->call('GET', '/admin/products/count.json');
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

        return $this->render('batchimport', [
            'totalcount' => $countProducts,
            'pages' => $pages
        ]);
    }

    /**
     * @return string
     */
    public function actionBatchimportproduct()
    {
        $index = Yii::$app->request->post('index');
        $countUpload = Yii::$app->request->post('count');

        $data = Productimport::batchimport($index, $countUpload);
        return json_encode($data);
    }

    /**
     * @return string
     */

    public function actionEditdata()
    {
        $this->layout = 'main2';
        $id = trim(Yii::$app->request->post('id'));

        $merchant_id = MERCHANT_ID;
        $model = NeweggProduct::find()->joinWith('jet_product')->where(['newegg_product.id' => $id])->one();
        $product_type = $model->jet_product->product_type;
        $Category = [];
        $category_path = "";

        $mainCategory = Categoryhelper::mainCategory();

        if (isset($mainCategory) && !empty($mainCategory)) {

            foreach ($mainCategory as $value) {
                if ($value['IndustryCode'] == $model->newegg_category) {
                    $industryCode = $value['IndustryCode'];
                    $industryName = $value['IndustryName'];
                }
            }
        }


        $query = 'SELECT `category_path` FROM `newegg_category_map` WHERE `product_type`="' . $product_type . '" AND `category_id`="' . $industryCode . '" AND `merchant_id`=' . $merchant_id;

        $records = Data::sqlRecords($query, 'one');
        $values = explode(',', $records['category_path']);

//        print_r($values);die('ssss');

        $subcategoryattributes = '';
        $subcategorygroup = '';
        $requiredAttrValues = '';
        $attributes = '';


        if (!empty($values[1])) {
            $attr = Categoryhelper::subcategoryAttribute($values[0], $values[1]);
            
            if (!empty($attr)) {
                foreach ($attr as $key) {
                    if ($key['IsRequired'] == 1 && $key['IsGroupBy'] == 0) {
                        $subcategoryattributes[$values[0]][] = $key;
                    }
                    if (($key['IsRequired'] == 0 && $key['IsGroupBy'] == 1) || ($key['IsRequired'] == 1 && $key['IsGroupBy'] == 1)) {
                        $subcategorygroup[$values[0]][] = $key;
                    }

                    $attributes[] = $key['PropertyName'];
                }
            }
        }
        $category_path = $industryCode . '->' . $industryName;

        $session = Yii::$app->session;
        //print_r($subcategorygroup);die;
        $productData = [
            'model' => $model,
            'category_path' => $category_path,
            'attributes' => $attributes,
            'requiredAttrValues' => $subcategoryattributes,
            'groupedby' => $subcategorygroup,
        ];
        $session_key = 'product' . $id;
        $session->set($session_key, $productData);
        $session->close();

        $html = $this->render('editdata', array('id' => $id, 'model' => $model, 'category_path' => $category_path, 'attributes' => $attributes, 'common_required_attributes' => $subcategoryattributes, 'required' => $subcategorygroup,'category_id'=>$industryCode), true);

        unset($connection);
        unset($attributes);
        return $html;
    }

    public function actionRenderCategoryTab()
    {
        $this->layout = "main2";

        $session = Yii::$app->session;

        $html = '';

        $id = Yii::$app->request->post('id');
        if ($id) {
            $session_key = 'product' . $id;
            $product = $session[$session_key];
            $model = $product['model'];
            $category_path = $product['category_path'];
            $attributes = $product['attributes'];
            $requiredAttrValues = $product['requiredAttrValues'];
            $requiredgroupby = $product['groupedby'];
          //  $categories = $product['newegg_category'];
            $html = $this->render('category_tab', [
                'model' => $model,
                'category_path' => $category_path,
                'attributes' => $attributes,
                'requiredAttrValues' => $requiredAttrValues,
                'requiredgroupby' => $requiredgroupby,
                //'industryCode' => $categories,
            ]);
        }
        return json_encode(['html' => $html]);
    }

    public function actionProductsave($id)
    {
        if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        $connection = Yii::$app->getDb();
        $model = NeweggProduct::find()->joinWith('jet_product')->where(['newegg_product.product_id' => $id])->one();
        $opt_sku="";
        $data = array();
        $sku = $model->jet_product->sku;
        $merchant_id = $model->merchant_id;

//        print_r(Yii::$app->request->post());die;
        if (Yii::$app->request->post()) {
            $product_barcode = "";
            $product_sku = "";
            $product_id = "";
            $product_upc = "";
            $product_short = "";
            $product_self = "";
            $product_vendor = "";
            $return_status = [];

            $product_id = $model->product_id;
            $variant_id = $model->jet_product->variant_id;
            $product_sku = $_POST['JetProduct']['sku'];
            $product_upc = trim($_POST['JetProduct']['upc']);
            $product_vendor = trim($_POST['JetProduct']['vendor']);
            $product_short = trim($_POST['JetProduct']['short_description']);
            $product_self = trim($_POST['JetProduct']['self_description']);
            $category = "";
            $category = trim($_POST['JetProduct']['fulfillment_node']);
            if ($product_vendor == "") {
                $return_status['error'] = "Brand is required field.";
                return json_encode($return_status);
            }
              if($product_barcode==""){
                $product_barcode=ProductApi::checkUpcType($product_upc);
            }
            /* Code By Ankit singh Start */
                if(Yii::$app->request->post('common_attributes')) {
                    $common_attr = json_encode(Yii::$app->request->post('common_attributes'));
                    $model->newegg_attributes = $common_attr;
                }

                if(isset($_POST['sku_override'])) {
                    $sku_override = Yii::$app->request->post('sku_override');
                    $model->sku_override = $sku_override;
                }

                if(isset($_POST['product_id_override'])) {
                    $product_id_override = Yii::$app->request->post('product_id_override');
                    $model->product_id_override = $product_id_override;
                }
            /* Code By Ankit singh End */
               if(Yii::$app->request->post('product-type')=='variants')
            {
                $walmart_attr=array();
                $options=array();
                $new_options=array();
                $pro_attr=array();
                $walmart_attributes = array();
                $attributes_of_jet=array();
                $other_vari_opt=array();
                $common_attr="";
                if(Yii::$app->request->post('jet_attributes')){
                    $walmart_attributes=Yii::$app->request->post('jet_attributes');
                }

                if(Yii::$app->request->post('attributes_of_jet')) {

                }
                if(Yii::$app->request->post('jet_varients_opt'))
                {
                    $product_error=[];
                    $other_vari_opt=Yii::$app->request->post('jet_varients_opt');
                    $er_msg="";
                    $chek_flag=false;
                    if(is_array($other_vari_opt) && count($other_vari_opt)>0)
                    {
        
                        foreach($other_vari_opt as $k_opt_id=>$v_opt)
                        {
                            

                            $opt_upc="";
                            $opt_asin="";
                            $opt_mpn="";
                            //$option_sku="";
                            $er_msg1="";
                            if(isset($v_opt['upc'])){
                                $opt_upc=trim($v_opt['upc']);
                            }
                            if(isset($v_opt['optionsku'])){
                                $option_sku=$v_opt['optionsku'];
                            }
                            
                            
                            $opt_barcode="";
                            /*-------newly added on 1 April starts------------*/
                            if($opt_barcode==""){
                                $opt_barcode=ProductApi::checkUpcType($opt_upc);

                            }
                            $upc_success_flag=true;
                            $mpn_success_flag=true;
                            $invalid_asin=false;
                            $invalid_upc=false;
                            $invalid_mpn=false;
                            $upc_error_msg="";
                            $asin_success_flag=true;
                            $asin_error_msg="";
                            if(strlen($opt_upc)>0){
                                list($upc_success_flag,$upc_error_msg)=ProductApi::checkProductOptionBarcodeOnUpdate($other_vari_opt,$v_opt,$k_opt_id,$opt_barcode,$product_barcode,$product_upc,$product_id,$product_sku,$connection);
                            }
                          /*  if($opt_upc=="" || !is_numeric($opt_upc) || (is_numeric($opt_upc) && !$opt_barcode) || (is_numeric($opt_upc) && $opt_barcode && !$upc_success_flag))
                            {
                                $invalid_upc=true;
                            }*/
                            if($invalid_upc){
                                $chek_flag=true;
                                $product_error['invalid_asin'][]=$option_sku;
                            }
                        }

                    }
                    if(count($product_error)>0)
                    {
                        $error="";
                        
                        if(isset($product_error['invalid_asin']) && count($product_error['invalid_asin'])>0 && !empty($product_error['invalid_asin'])){
                            $error.="Invalid/Missing Barcode for sku(s): ".implode(',',$product_error['invalid_asin'])."<br>";
                        }
                        $return_status['error']=$error;

                        unset($error);
                        unset($product_error);
                        return json_encode($return_status);
                    }
                }
                else
                {
                    $upc_success_flag=false;
                    $asin_success_flag=false;
                    $mpn_success_flag=false;
                    $invalid_upc=false;
                    $invalid_asin=false;
                    $invalid_mpn=false;
                    $chek_flag=false;
                    $er_msg="";
                    $type="";
                    $type=Jetproductinfo::checkUpcType($product_upc);
                    if(strlen($product_upc)>0){
                        $upc_success_flag=ProductApi::checkUpcVariantSimple($product_upc,$product_id,$product_sku,$connection);
                    }
                    if($product_upc=="" || !is_numeric($product_upc) || (is_numeric($product_upc) && $type="") || (is_numeric($product_upc) && $type && $upc_success_flag))
                    {
                        $invalid_upc=true;
                    }
                    if($invalid_upc){
                        $chek_flag=true;
                        $er_msg.="Invalid/Missing Barcode, must be unique"."<br>";
                    }
                    if($chek_flag){
                        $return_status['error']=$er_msg;
                        return json_encode($return_status);
                    }
                    /*-------------check asin and upc for variant-simple here ends----------*/
                }

                if($walmart_attributes)
                {

                    foreach($walmart_attributes as $attr_id=>$value_arr)
                    {
                        $flag=false;
                        if(is_array($value_arr) && count($value_arr)>0)
                        {
                            $walmart_attr_id="";
                            foreach($value_arr as $val_key=>$chd_arr)
                            {
                                if($val_key=="newegg_attr_id" && trim($chd_arr)=="")
                                {
                                    $flag=true;
                                    foreach($value_arr as $v_key=>$c_ar)
                                    {
                                        if($v_key=="newegg_attr_id"){
                                            continue;
                                        }
                                        elseif($v_key=="newegg_attr_name")
                                        {
                                            continue;
                                        }elseif(is_array($c_ar)){
                                            $new_options[trim($v_key)][trim($attr_id)]=trim($c_ar['value']);
                                        }
                                    }
                                    break;
                                }
                                else{
                                    if($val_key=="newegg_attr_id"){
                                        $str_id="";
                                        $str_id_arr=array();
                                        $str_id=trim($chd_arr);
                                        $str_id_arr=explode(',',$str_id);
                                        $walmart_attr_id=trim($str_id_arr[0]);
                                    }elseif($val_key=="newegg_attr_name"){
                                        $unit="";
                                        $s_unit=[];
                                        if(count($attributes_of_jet)>0 && array_key_exists($walmart_attr_id,$attributes_of_jet)){
                                            $unit=$attributes_of_jet[$walmart_attr_id]['unit'];
                                        }
                                        $s_unit[]=trim($walmart_attr_id);
                                        if($unit!=""){
                                            $s_unit[]=trim($unit);
                                        }
                                        //$s_unit=trim($s_unit);
                                        $pro_attr[trim($chd_arr)]=$s_unit;
                                    }elseif(is_array($chd_arr)){
                                       
                                        //$options[$attr_id]['option_id'][]=trim($val_key);
                                        $options[trim($val_key)][$walmart_attr_id]=trim($chd_arr['value']);
                                        $new_options[trim($val_key)][trim($attr_id)]=trim($chd_arr['value']);
                                    }
        
                                }
                            }
                        }
                        if($flag){
                            //unset($options[$attr_id]);
                            continue;
                        }
        
                    }
                }
                $walmart_attr=$options;

                //$connection = Yii::$app->getDb();
                $product_id='';
                $product_id=trim($id);
                if(is_array($walmart_attr) && count($walmart_attr)>0)
                {
                    $opt_count=0;
                    foreach($walmart_attr as $opt_key=>$option_value)
                    {
                        $option_id="";
                        $option_id=trim($opt_key);
                        $options_save="";
                        $options_save=json_encode($option_value);
                        //$opt_price="";
                        //$opt_qty="";
                        $opt_upc="";
                        $opt_asin="";
                        $opt_mpn="";
                        $opt_sku="";
                        if(is_array($other_vari_opt) && count($other_vari_opt)>0){
                            //$opt_price=$other_vari_opt[$option_id]['price'];
                            //$opt_qty=$other_vari_opt[$option_id]['qty'];
                            $opt_upc=$other_vari_opt[$option_id]['upc'];
                            $opt_sku=$other_vari_opt[$option_id]['optionsku'];
                        }
                        $sql="";
                        $model2 ="";
                        $query="SELECT `option_id` from `jet_product_variants` WHERE option_id='".$option_id."' LIMIT 1";

                        $model2 = Data::sqlRecords($query,"one","select");
                        if($model2)
                        {
                            $new_variant_option_1="";
                            $new_variant_option_2="";
                            $new_variant_option_3="";
                            if(is_array($new_options[trim($opt_key)]) && count($new_options[trim($opt_key)])>0)
                            {
                                $v_opt_count=1;
                                foreach($new_options[trim($opt_key)] as $opts_k=>$opts_v){
                                    if($v_opt_count==1){
                                        $new_variant_option_1=$opts_v;
                                    }
                                    if($v_opt_count==2){
                                        $new_variant_option_2=$opts_v;
                                    }
                                    if($v_opt_count==3){
                                        $new_variant_option_3=$opts_v;
                                    }
                                    $v_opt_count++;
                                }
                            }
                            $sql="UPDATE `jet_product_variants` SET
                                    option_unique_id='".trim($opt_upc)."'
                                    where option_id='".$option_id."'";
                            Data::sqlRecords($sql,null,"update");
                            //$connection->createCommand($sql)->execute();
                            $model3 ="";
                            $query = "SELECT `option_id` from `newegg_product_variants` WHERE option_id='".$option_id."' LIMIT 1";

                            $model3 = Data::sqlRecords($query,"one","select");
                            if($model3 !=="")
                            {
                                $sql="";
                                $sql="UPDATE `newegg_product_variants` SET
                                    new_variant_option_1='".$new_variant_option_1."',
                                    new_variant_option_2='".$new_variant_option_2."',
                                    new_variant_option_3='".$new_variant_option_3."',
                                    newegg_option_attributes='".$options_save."'
                                    where option_id='".$option_id."'";
                                Data::sqlRecords($sql,null,"update");
                            }
                        }
        
                        if($option_id==$variant_id)
                        {
                            $model->jet_product->upc=trim($opt_upc);
                            $model->jet_product->vendor=trim($product_vendor);
                        }
                        $opt_count++;
                    }
                }
                else
                {
                    if(is_array($other_vari_opt) && count($other_vari_opt)>0)
                    {
                        $opt_count=0;
                        foreach($other_vari_opt as $opt_id=>$v_arr)
                        {
                            $model2 ="";
                            $option_id="";
                            $option_id=trim($opt_id);
                            //$opt_price="";
                            //$opt_qty="";
                            $opt_upc="";
                            $opt_asin="";
                            $opt_mpn="";
                            //$opt_price=$other_vari_opt[$option_id]['price'];
                            //$opt_qty=$other_vari_opt[$option_id]['qty'];
                            if(isset($other_vari_opt[$option_id]['upc'])){
                                $opt_upc=$other_vari_opt[$option_id]['upc'];
                            }
                            
                            if($opt_sku==$product_sku)
                            {
                                //if(trim($opt_upc)!=""){
                                $model->jet_product->upc=trim($opt_upc);
                                $model->jet_product->vendor=trim($product_vendor);
                                //}
                           }
                            $sql="";
                            $model2 ="";
                            $query="SELECT `option_id` from `jet_product_variants` WHERE option_id='".$option_id."' LIMIT 1";
                            $model2 = Data::sqlRecords($query,"one","select");
                            if($model2 !==""){
                                $sql="";
                                $sql="UPDATE `jet_product_variants` SET
                                        option_unique_id='".trim($opt_upc)."'
                                        where option_id='".$option_id."'";
                                //$connection->createCommand($sql)->execute();
                                Data::sqlRecords($sql,null,"update");
                                
                            }
                            $model3 ="";
                            $model3 = $connection->createCommand("SELECT `option_id` from `newegg_product_variants` WHERE option_id='".$option_id."'")->queryOne();
                            if($model3 !==""){
                                $sql="";
                                $sql="UPDATE `newegg_product_variants` SET
                                        newegg_option_attributes=''
                                        where option_id='".$option_id."'";
                                Data::sqlRecords($sql,null,"update");
                                //$connection->createCommand($sql)->execute();
                            }
                            $opt_count++;
                       }
                    }
                }
                unset($model2);
                unset($sql);
                unset($options_save);
              /*  if(count($pro_attr)==0)
                    $model->newegg_attributes='';
                else
                    $model->newegg_attributes=json_encode($pro_attr);*/
                $model->jet_product->vendor=$product_vendor;
               // $model->short_description=$product_short;
               // $model->self_description=$product_self;
                //$model->tax_code=$product_tax;
                //print_r($model);die;
                $model->jet_product->save(false);
                $model->save(false);
                unset($walmart_attributes);
                unset($other_vari_opt);
                unset($attributes_of_jet);
                                     
            }

            if (Yii::$app->request->post('newegg_product')) {
             
                $newegg_attr = json_encode(Yii::$app->request->post('newegg_product'));
                $query = "UPDATE `newegg_product` SET `newegg_attributes`='" . $newegg_attr . "' WHERE `product_id`='" . $product_id . "'";
                Data::sqlRecords($query, null, "update");
            }

        }


        $return_status['success'] = "Product information has been saved successfully..";
        return json_encode($return_status);
    }

        public function actionAjaxBulkUpload()
    {
        if (Yii::$app->user->isGuest)
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);

        $action = Yii::$app->request->post('action');
        $selection = (array)Yii::$app->request->post('selection');
        $Productcount = count($selection);

        if($Productcount == 0) {
            Yii::$app->session->setFlash('error', "No Product selected...");
            return $this->redirect(['index']);
        }

        $merchant_id = MERCHANT_ID;
        $sc = new ShopifyClientHelper(SHOP, TOKEN, NEWEGG_APP_KEY, NEWEGG_APP_SECRET);

        $session = Yii::$app->session;
        $session->set('shopify_object', serialize($sc));
        $session->set('merchant_id', $merchant_id);

        $size_of_request = 100;//Number of products to be uploaded at once(in single feed)
        $pages = (int)(ceil($Productcount/$size_of_request));

        $max_feed_allowed_per_hour = 10;//We can only send 10 feeds per hour.
        if($pages > $max_feed_allowed_per_hour)
        {
            $size_of_request = (int)(ceil($Productcount/$max_feed_allowed_per_hour));
            if($size_of_request > 10000) {
                Yii::$app->session->setFlash('error', "MAX Limit Exceeded. Please Unselect Some Products.");
                return $this->redirect(['index']);
            }
            $pages = (int)(ceil($Productcount/$size_of_request));
        }

        if($action=='batch-upload')
        {
            $selectedProducts = array_chunk($selection, $size_of_request);

            //Increase Array Indexes By 1
            //$selectedProducts = array_combine(range(1, count($selectedProducts)), array_values($selectedProducts));

            $session->set('selected_products', $selectedProducts);

            return $this->render('ajaxbulkupload', [
                    'totalcount' => $Productcount,
                    'pages' => $pages
            ]);
        }
   /*     elseif($action == 'batch-retire')
        {
            $pages = count($selection);

            $session->set('retire_product', $selection);

            return $this->render('retireproduct',[
                        'totalcount' => $Productcount,
                        'pages'=>$pages            
            ]);

        }elseif($action == 'batch-product-status')
        {
            $pages = count($selection);

            $session->set('product_status', $selection);

            return $this->render('bulkproductstatus',[
                        'totalcount' => $Productcount,
                        'pages' =>$pages
            ]);
        }*/

        $session->close();
        
    }


    public function actionStartbatchupload()
    {
        $session = Yii::$app->session;

        $returnArr = ['error' => true];

        $index = Yii::$app->request->post('index');
        $selectedProducts = isset($session['selected_products'][$index])?$session['selected_products'][$index]:[];
        $count = count($selectedProducts);

        if(!$count) {
            $returnArr = ['error'=>true, 'message'=>'No Products to Upload'];
        } 
        else 
        {
            $connection = Yii::$app->getDb();

            $merchant_id = "";
            if(isset($session['merchant_id']))
                $merchant_id = $session['merchant_id'];
            else
                $merchant_id = MERCHANT_ID;

            try {
                $productResponse = ProductApi::createProductOnNewegg($selectedProducts,$this->neweggHelper,MERCHANT_ID,$connection);
                if(is_array($productResponse) && isset($productResponse['uploadIds'],$productResponse['feedId']) && count($productResponse['uploadIds']>0))
                {
                    //save product status and data feed
                    $ids = implode(',',$productResponse['uploadIds']);
                    $feed_file = isset($productResponse['feed_file'])?$productResponse['feed_file']:'';
                    
                    $msg = "product feed successfully submitted on walmart.";
                    $feed_count = count($productResponse['uploadIds']);
                    $feedId = $productResponse['feedId'];
                    $returnArr = ['success'=>true, 'message'=>$msg, 'count'=>$feed_count, 'feed_id'=>$feedId];
                }
                elseif(isset($productResponse['errors'])) {
                    $msg = json_encode($productResponse['errors']);
                    $returnArr = ['error'=>true, 'message'=>$msg];
                }
                elseif (isset($productResponse['feedError'])) {
                    $msg = json_encode($productResponse['feedError']);
                    $returnArr = ['error'=>true, 'message'=>$msg];
                }

                //save errors in database for each erroed product
                $returnArr['error_count'] = 0;
                if(isset($productResponse['erroredSkus']))
                {
                    $returnArr['error_count'] = count($productResponse['erroredSkus']);
                    $returnArr['erroredSkus'] = implode(',',array_keys($productResponse['erroredSkus']));
                }

            }
            catch (Exception $e)
            {
                $returnArr = ['error'=>true, 'message'=>$e->getMessage()];
            }
        }
        return json_encode($returnArr);
    }
    /*show error on ptoduct grid*/
     public function actionErrornewegg()
    {
        $this->layout="main2";
        $id = trim(Yii::$app->request->post('id'));
        $merchant_id = Yii::$app->request->post('merchant_id');
        
        $errorData=array();
        $connection=Yii::$app->getDb();
        $errorData=$connection->createCommand('SELECT `error` from `newegg_product` where merchant_id="'.$merchant_id.'" AND `id`="'.$id.' LIMIT 0, 1"')->queryOne();
        
        $html = $this->render('errors',array('data'=>$errorData),true);
        $connection->close();
        return $html;
    }
        /*show error on ptoduct grid*/
     public function actionGetattribute()
    {
        $this->layout="main2";

        $mainData = Yii::$app->request->post();
        $requiredAttrValues= unserialize($mainData['data']);
      /*  $content = '<select name="" style="width:auto" class="form-control root" id = "map_attribute">
                <option value="">Please Select Category</option>';*/
       // print_r($requiredAttrValues);die;
                $i = 0;
        foreach ($requiredAttrValues as $key1 => $value1) {
            foreach ($value1 as $key => $value) {
            if($value['PropertyName'] == $mainData['name']){
                $val = \frontend\modules\neweggcanada\components\categories\Categoryhelper::subcategoryAttributeValue($key1, $value['SubcategoryID'], $value['PropertyName']);
                $attributeValue = $val['PropertyValueList'];
                $content = '<select name="" style="width:auto" name="jet_varients_opt[[option]'.$mainData['id'].$i.']class="form-control root" id = "map_attribute">
                <option value="">Please Select Category</option>';
                foreach ($attributeValue as $key => $value2) {
                    $content .='<tr><option "value="'.$value2.' ">'.$value2.'</option></tr>';
                   
                }
                 $i++;
                break;
            }
            
        }
        
             
        }
        echo $content;
    }
   
}
