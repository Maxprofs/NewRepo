<?php
namespace frontend\modules\jet\controllers;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\models\JetProductSearch;
use frontend\modules\jet\models\JetProductVariants;
use frontend\modules\jet\models\JetReturnException;
use frontend\modules\jet\models\JetShippingException;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetapimerchant;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\components\ShopifyClientHelper;
use frontend\modules\jet\components\AttributeMap;
use common\models\User;
/**
 * JetproductController implements the CRUD actions for JetProduct model.
 */
class JetproductController extends JetmainController
{
	protected $sc,$jetHelper;
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
    
    /**
     * Lists all JetProduct models.
     * @return mixed
     */
    
    public function actionIndex()
    {
    	if (Yii::$app->user->isGuest) {
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }        
        $session = Yii::$app->session;            
        $merchant_id=MERCHANT_ID;        
        $countUpdate = 0;
        $UpdateRows = $showDynamicPrice = [];

        $UpdateRows = Data::sqlRecords("SELECT `product_id` FROM `jet_product_tmp` WHERE merchant_id='".$merchant_id."'",'all','select');
        if(is_array($UpdateRows) && count($UpdateRows)>0){
            $countUpdate=count($UpdateRows);
        }
        
        $searchModel = new JetProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $showDynamicPrice = Data::sqlRecords("SELECT `value`  FROM `jet_config` WHERE `merchant_id` ='{$merchant_id}'  AND `data` LIKE 'dynamic_repricing'",'one','select');

        $priceDynamic = isset($showDynamicPrice['value']) ? $showDynamicPrice['value'] : "no";
        
        //check product status available for purchase
        $query="SELECT `id` FROM `jet_product` WHERE merchant_id='".MERCHANT_ID."' and status='Available for Purchase'";
        $avail_for_sale=false;
        $afpCollection=Data::sqlRecords($query,'one','select');
        if( !empty($afpCollection) ){
            $avail_for_sale=true;
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'countUpdate' => $countUpdate,
            'priceDynamic'=>$priceDynamic,
            'avail_for_sale'=>$avail_for_sale
        ]);
    }
    
    /**
     * Finds the JetProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JetProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JetProduct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested product does not exist.');
        }
    }
    public function actionBulk()
    {
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        $action=Yii::$app->request->post('action');
        $selection=(array)Yii::$app->request->post('selection');
        
        if(!is_object($session))
        {
            Yii::$app->session->setFlash('error', "Can't initialize Session.Please try again later.");
            return $this->redirect(['index']);
        }
        if(count($selection)==0)
        {
            Yii::$app->session->setFlash('error', "No Product selected...");
            return $this->redirect(['index']);
        }                
        elseif($action=='archieved-batch')
        {
            $productAll = [];
            $query="SELECT `id`,`sku`,`type` FROM `jet_product` WHERE id IN(".implode(',',$selection).")";
            $productAll = Data::sqlRecords($query,"all","select");
            $session->set('productAll', serialize($productAll));
            unset($productAll);
            
            return $this->render('batcharchieved', [
                    'totalcount' => count($selection),
                ]);
        }
        elseif($action=='unarchieved-batch')
        {
            $productAll = [];
            $query="SELECT `id`,`sku`,`qty`,`type` FROM `jet_product` WHERE id IN(".implode(',',$selection).")";
            $productAll = Data::sqlRecords($query,"all","select");
            $session->set('productAll', serialize($productAll));
            return $this->render('batchunarchieved', [
                'totalcount' => count($selection),
            ]);
        }
        elseif($action=='batch-upload')
        {
            $session->set('productforbatchupload', $selection);
            // Custom Price Upload on Jet
            $setCustomPrice = [];
            $newCustomPrice = false;
            $setCustomPrice=Data::sqlRecords('SELECT `value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"  ','one','select');
            if (is_array($setCustomPrice) && isset($setCustomPrice['value']))
            {
                $newCustomPrice=$setCustomPrice['value'];
            }
            $updatePriceType="";
            $updatePriceValue=0;
            if($newCustomPrice)
            {
                $customPricearr = [];
                $customPricearr = explode('-',$newCustomPrice);
                $updatePriceType = $customPricearr[0];
                $updatePriceValue = $customPricearr[1];             
            }
            unset($customPricearr,$newCustomPrice);
            $session->set('priceType', serialize($updatePriceType));
            $session->set('priceValue', serialize($updatePriceValue));            
            return $this->render('batchupload', [
                    'totalcount' => count($selection),
                    'param' => 'Sku'
            ]);
        }
        elseif($action=='batch-resend')
        {
            $session->set('productforbatchupload', $selection);
            // Custom Price Upload on Jet
            $setCustomPrice = [];
            $newCustomPrice = false;
            $setCustomPrice=Data::sqlRecords('SELECT `value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"  ','one','select');
            if (is_array($setCustomPrice) && isset($setCustomPrice['value']))
            {
                $newCustomPrice=$setCustomPrice['value'];
            }
            $updatePriceType="";
            $updatePriceValue=0;
            if($newCustomPrice)
            {
                $customPricearr = [];
                $customPricearr = explode('-',$newCustomPrice);
                $updatePriceType = $customPricearr[0];
                $updatePriceValue = $customPricearr[1];             
            }
            unset($customPricearr,$newCustomPrice);
            $session->set('priceType', serialize($updatePriceType));
            $session->set('priceValue', serialize($updatePriceValue));            
            return $this->render('batchupload', [
                    'totalcount' => count($selection),
                    'param' => 'Resend'
            ]);
        }
        elseif($action=='batch-inventory')
        {
            $session->set('productforbatchupload', $selection);           
            return $this->render('batchupload', [
                    'totalcount' => count($selection),
                    'param' => 'Inventory'
            ]);
        }
        elseif($action=='batch-price')
        {
            // Custom Price Upload on Jet
            $setCustomPrice = [];
            $newCustomPrice = false;
            $setCustomPrice=Data::sqlRecords('SELECT `value` from `jet_config` where merchant_id="'.$merchant_id.'" AND data="set_price_amount"  ','one','select');
            if (is_array($setCustomPrice) && isset($setCustomPrice['value']))
            {
                $newCustomPrice=$setCustomPrice['value'];
            }
            $updatePriceType="";
            $updatePriceValue=0;
            if($newCustomPrice)
            {
                $customPricearr = [];
                $customPricearr = explode('-',$newCustomPrice);
                $updatePriceType = $customPricearr[0];
                $updatePriceValue = $customPricearr[1];             
            }
            unset($customPricearr);  unset($newCustomPrice);
            $session->set('priceType', serialize($updatePriceType));
            $session->set('priceValue', serialize($updatePriceValue)); 
            $session->set('productforbatchupload', $selection);           
            return $this->render('batchupload', [
                    'totalcount' => count($selection),
                    'param' => 'Price'
            ]);
        }
        else
        {
            return $this->redirect(['index']);
        }        
    }
    public function actionStartBatchUploadInventory()
    {
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $merchant_id = MERCHANT_ID;
        $fullfillmentnodeid = FULLFILMENT_NODE_ID;
        $return_msg['success'] = $return_msg['error'] = "";
        $productforbatchupload=$session->get('productforbatchupload');
        $pid=trim($productforbatchupload[$index]);
        if((count($productforbatchupload)-1)==$index)
            $session->remove('productforbatchupload');
        $query="SELECT pro.qty,var.option_qty,pro.sku,var.option_sku,pro.type FROM `jet_product` as pro LEFT JOIN `jet_product_variants` as var ON pro.id=var.product_id WHERE pro.id='".$pid."'";
        $productCollection=Data::sqlRecords($query,"all","select");
        if(is_array($productCollection) && count($productCollection)>0)
        {
            $skus=[];
            $responseData='';
            foreach ($productCollection as $value) 
            {
                $qty=0;
                if($value['type']=="variants")
                {
                    $sku=$value['option_sku'];
                    $qty=$value['option_qty'];
                }
                else
                {
                    $sku=$value['sku'];
                    $qty=$value['qty'];
                }
                if($qty<=0)
                    $qty=0;
                $status=true;
                $batchResponse=false;
                JetProductInfo::updateQtyOnJet($sku,$qty,$this->jetHelper,$fullfillmentnodeid,$merchant_id,$batchResponse);
                if($batchResponse==202)
                {
                    $skus[]=$sku;
                }
            }
            if(is_array($skus) && count($sku)>0)
                $responseData = implode(',', $skus);
            if($responseData)
                $return_msg['success'] = $responseData." sku(s) inventory data sent";
            else
                $return_msg['error'] = "unable to update inventory data";
            
            return json_encode($return_msg); 
        }
    } 
    public function actionStartBatchUploadPrice()
    {
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $merchant_id = MERCHANT_ID;
        $fullfillmentnodeid = FULLFILMENT_NODE_ID;
        if(!$this->jetHelper)
            $this->jetHelper = new Jetapimerchant(API_HOST,API_USER,API_PASSWORD);
        $return_msg['success'] = $return_msg['error'] = "";
        $productforbatchupload=$session->get('productforbatchupload');
        $priceType=unserialize($session->get('priceType'));
        $priceValue=unserialize($session->get('priceValue'));
        $pid=trim($productforbatchupload[$index]);
        if((count($productforbatchupload)-1)==$index)
            $session->remove('productforbatchupload');
        $query="SELECT COALESCE(`update_option_price`,`option_price`,`update_price`,`price`) as `price`,COALESCE(`option_sku`,`sku`) as `sku` FROM `jet_product` as pro LEFT JOIN `jet_product_variants` as var ON `pro`.`id`=`var`.`product_id` LEFT JOIN `jet_product_details` as `details` ON `pro`.`id`=`details`.`product_id` WHERE `pro`.`id`='".$pid."'";
        $productCollection=Data::sqlRecords($query,"all","select");
        if(is_array($productCollection) && count($productCollection)>0)
        {
            $skus=[];
            $responseData='';
            foreach ($productCollection as $value) 
            {
                
                $sku=$value['sku'];
                $price=(float)$value['price'];
                if($priceType !='' && $priceValue!=0)
                {
                    $updatePrice=0.00;
                    $updatePrice=self::priceChange($price,$priceType,$priceValue);
                    if($updatePrice!=0)
                        $price = (float)$updatePrice;
                }
                $batchResponse=false;
                if($price>0){
                    JetProductInfo::updatePriceOnJet($sku,$price,$this->jetHelper,$fullfillmentnodeid,$merchant_id,$batchResponse);
                }
                if($batchResponse==202)
                {
                    $skus[]=$sku;
                }
            }
            if(is_array($skus) && count($sku)>0)
                $responseData = implode(',', $skus);
            
            if($responseData)
                $return_msg['success'] = $responseData." sku(s) price data sent";
            else
                $return_msg['error'] = "unable to update price data";
            
            return json_encode($return_msg); 
        }
    }  
    public function actionStartbatchupload()
    {
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $merchant_id = MERCHANT_ID;
        $fullfillmentnodeid = FULLFILMENT_NODE_ID;
        $return_msg['success'] = $return_msg['error'] = $priceType = $resultDes = $message = $errordisplay = $type = $parentmainImage = "";
        $priceValue = $count = $variationCount = $pid = $kmain = 0;
                
        $productforbatchupload = $result = $node = $inventory = $price = $uploadErrors = $responseOption = $SKU_Array =  $unique = $Attribute_arr = $Attribute_array = $_uniquedata = $carray = $product = $_uniquedata = $images = [];
        $productforbatchupload=$session->get('productforbatchupload');
        $priceType=unserialize($session->get('priceType'));
        $priceValue=unserialize($session->get('priceValue'));

        $not_exists_flag = $is_variation = false;
        $status_code=true;
        
        $pid=trim($productforbatchupload[$index]);
        if((count($productforbatchupload)-1)==$index)
        {
            $session->remove('productforbatchupload');
        }
        $sql = "SELECT `jet_product`.`id`,COALESCE(`update_title`,`title`) as `title`,`sku`,`type`,`product_type`,COALESCE(`update_description`,`description`) as `description`,COALESCE(`update_price`,`price`) as `price`,`variant_id`,`image`,`qty`,`weight`,`attr_ids`,`jet_attributes`,`vendor`,`upc`,`barcode_type`,`mpn`,`ASIN`,`fulfillment_node`,`pack_qty` FROM `jet_product` LEFT JOIN `jet_product_details` ON `jet_product`.`id`=`jet_product_details`.`product_id` WHERE `jet_product`.`id`='".$pid."' LIMIT 0,1";

        $product = (object)Data::sqlRecords($sql,'one','select');
        $carray=Jetproductinfo::checkBeforeDataPrepare($product,$merchant_id);
        
        if(!$carray['success'] || !empty($carray['error']))
        {
            if($carray['error'] && is_array($carray['error']))
            {
                $isCheckSimpleUpc = $isCheckVarUpc = false;
                
                foreach($carray['error'] as $ckey=>$cvalue)
                {
                    if(is_array($cvalue))
                    {
                        $str="";
                        foreach($cvalue as $ck=>$cv){
                            $str.=$cv.", ";
                            $uploadErrors[$ckey][]=$cv;
                        }
                        if($ckey=="sku_error_var")
                            $errordisplay.="Missing Variants Sku(s)[".$str."]<br>";
                        if($ckey=="upc_error_var")
                        {   
                            $isCheckVarUpc=true;
                            $errordisplay.="Missing Variants Barcode or ASIN or MPN[".$str."]<br>";
                        }
                        if($ckey=="price_error_var")
                            $errordisplay.="Invalid Variants Price[".$str."]<br>";
                        if($ckey=="qty_error_var")
                            $errordisplay.="Invalid Variants Quantity[".$str."]<br>";                       
                        if($ckey=="mpn_error_var")
                            $errordisplay.="Invalid Variants Mpn[".$str."]<br>";
                        if($ckey=="asin_error_info_var" && $isCheckVarUpc==false)
                            $errordisplay.="Duplicate/Invalid Variants Barcode or ASIN or MPN[".$str."]<br>";
                        if($ckey=="attribute_mapping_error")
                            $errordisplay.="Need to map Variant Options with Jet Attributes[".$str."]<br>";
                    }
                    else
                    {
                        $uploadErrors[$ckey][]=$cvalue;
                        if($ckey=="brand_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="node_id_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="image_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="sku_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="upc_error")
                        {
                            $isCheckSimpleUpc=true;
                            $errordisplay.=$cvalue."<br>";
                        }
                        if($ckey=="price_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="title_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="qty_error")
                            $errordisplay.=$cvalue."<br>";                        
                        if($ckey=="mpn_error")
                            $errordisplay.=$cvalue."<br>";
                        if($ckey=="asin_error_info" && $isCheckSimpleUpc==false)
                            $errordisplay.=$cvalue."<br>";
                    } 
                }
                if(count($uploadErrors)>0)
                {
                    $message.="<b>There are following information are incomplete/wrong for given product(s):</b><ul>";
                    if(isset($uploadErrors['price']) && count($uploadErrors['price'])>0){
                        $message.="<li><span class='required_label'>Wrong Price</span>
                                        <ul>
                                            <li>".implode(', ',$uploadErrors['price'])."</li>
                                        </ul>
                                    </li>";
                    }   
                    if(isset($uploadErrors['qty']) && count($uploadErrors['qty'])>0)
                    {
                        $message.="<li><span class='required_label'>Wrong Quantity : Quantity must be greater than 0</span>
                                        <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['qty'])."</span></li>
                                        </ul>
                                  </li>";
                    }   
                    if(isset($uploadErrors['attribute_mapping']) && count($uploadErrors['attribute_mapping'])>0)
                    {   
                        $message.="<li><span class='required_label'>For variants product - Shopify Option(s) must be mapped with at least one Jet Attributes.</span>
                                        <ul>
                                            <li>".implode(', ',$uploadErrors['attribute_mapping'])."</li>
                                        </ul>
                                    </li>";
                    }
                    if(isset($uploadErrors['upc']) && count($uploadErrors['upc'])>0)
                    {   
                        $message.="<li><span class='required_label'>Product must require Unique Code either Barcode(UPC,GTIN-14,ISBN-10,ISBN-13) Or ASIN.Barcode or ASIN must be unique for each product and their variants.</span>
                                        <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['upc'])."</span></li>
                                        </ul>
                                    </li>";
                    }
                    if(isset($uploadErrors['mpn']) && count($uploadErrors['mpn'])>0)
                    {   
                        $message.="<li><span class='required_label'>Invalid MPN.Length must be atmost 50.</span>
                                        <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['mpn'])."</span></li>
                                        </ul>
                                    </li>";
                    }
                    if(isset($uploadErrors['node_id']) && count($uploadErrors['node_id'])>0)
                    {   
                        $message.="<li>
                                       <span class='required_label'>Missging Jet Browse Node.</span>
                                       <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['node_id'])."</span></li>
                                        </ul>
                                  </li>";
                    }
                    if(isset($uploadErrors['title']) && count($uploadErrors['title'])>0)
                    {   
                        $message.="<li>
                                       <span class='required_label'>Invalid Title (between 5 to 500 characters).</span>
                                       <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['title'])."</span></li>
                                        </ul>
                                  </li>";
                    }
                    if(isset($uploadErrors['brand']) && count($uploadErrors['brand'])>0)
                    {
                        $message.="<li><span class='required_label'>Missing Brand</span></li>
                                        <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['brand'])."</span></li>
                                        </ul>
                                    </li>";
                    }
                        
                    if(isset($uploadErrors['image']) && count($uploadErrors['image'])>0)
                    {
                        $message.="<li><span class='required_label'>Product must have atleast one valid image</span>
                                        <ul>
                                            <li><span class='required_values'>".implode(', ',$uploadErrors['image'])."</span></li>
                                        </ul>
                                    </li>";
                    }
                                
                    $message.="</ul>";
                    $return_msg['error']=$message;
                    if($errordisplay!="")
                    {
                        $sql="UPDATE `jet_product` SET  error='".$errordisplay."' where id='".$pid."'";
                        Data::sqlRecords($sql,null,'update');
                    }
                    
                    if(!$carray['success']){
                        unset($uploadErrors,$errordisplay,$product);
                        return json_encode($return_msg); 
                    }  
                }
            }
        }

        $upc = trim($product->upc);
        $asin = trim($product->ASIN);
        $mpn = trim($product->mpn);
        $brand=trim($product->vendor);        
        $sku=$product->sku;
        $name=$product->title;
        $weight =  $product->weight;
        $SKU_Array['product_title']=$name;
        $nodeid = (int)$product->fulfillment_node;
        $attribute=$product->jet_attributes;

        $Attribute_arr=json_decode(stripslashes($attribute),true);
        $SKU_Array['jet_browse_node_id']=$nodeid;
        $type=JetProductInfo::checkUpcType($upc);
            
        if($upc && $type && (isset($carray['upc_simp'])|| isset($carray[$sku]['upc_var'])) )
        {
            $unique['standard_product_code']=$upc;
            $unique['standard_product_code_type']=$type;
            $SKU_Array['standard_product_codes'][]=$unique;
        }
            
        if($asin!=null && (isset($carray['asin_simp'])||isset($carray[$sku]['asin_var'])) )
        {
            $SKU_Array['ASIN']=$asin;
        }
        if($weight>=0.01)
        {
            $SKU_Array['shipping_weight_pounds']=(float)$weight;
        }
        $SKU_Array['manufacturer']=$brand;
        $SKU_Array['brand']=$brand;
        if($mpn!=null && (isset($carray['mpn_simp']) || isset($carray[$sku]['mpn_var']) ) ){
            $SKU_Array['mfr_part_number']=$mpn;
        }
        $SKU_Array['multipack_quantity']= (int)$product->pack_qty;
        
        $description=$product->description; //utf8_decode($product->description);
        
        if(strlen($description)>2000)
            $description=$this->jetHelper->trimString($description, 2000);
        $SKU_Array['product_description']=$description;    
        
        $images=explode(',',$product->image);
        foreach($images as $key=>$value)
        {
            if($value=="")
                continue;
                                
            $value = preg_replace( '~\s+~', '%20', $value);    
            if(Jetproductinfo::checkRemoteFile($value)==true)
            {
                $kmain=$key;
                $SKU_Array['main_image_url']=$value;
                //$SKU_Array['swatch_image_url']=$value;
                break;
            }
        }
            
        if(count($images)>1)
        {
            $i=1;
            foreach($images as $key=>$value)
            {
                if($key==$kmain)
                    continue;
                if($i>8)
                    break;
                    
                /* if(strpos($value,'upload/images')!== false) {
                    $value='https://shopify.cedcommerce.com'.Yii::$app->getUrlManager()->getBaseUrl().'/'.$value;
                }  */
                $value = preg_replace( '~\s+~', '%20', $value); 
                if($value!='' && Jetproductinfo::checkRemoteFile($value)==true)
                {
                    $SKU_Array['alternate_images'][]= array(
                            'image_slot_id'=>(int)$i,
                            'image_url'=> $value
                    );
                    $i++;
                }
            }
        }
        
        unset($images);     
        
        if($product->type=='simple')
        {
            $uploadErrors=array();
            if (!empty($Attribute_arr) && is_array($Attribute_arr)) {
                foreach ($Attribute_arr as $key =>$arr)
                {
                    if(count($arr)==1)
                    {
                        $Attribute_array[] = array(
                            'attribute_id'=>(int)$key,
                            'attribute_value'=>$arr[0]
                        );
                    }
                    // get value of text type with unit
                    elseif(count($arr)==2)
                    {                        
                        $Attribute_array[] = array(
                            'attribute_id'=>(int)$key,
                            'attribute_value'=>$arr[0],
                            'attribute_value_unit'=>$arr[1]
                        );
                    } 
                } 
                unset($Attribute_arr);                
            }            
        }
        else
        {
            $errordisplay = $vresult = "";
            $uploadErrors = $responseA = $vresponse = [];
            if(isset($carray['attribute_mapped']))
                $responseOption=Jetproductinfo::createoption($product,$carray,$this->jetHelper,FULLFILMENT_NODE_ID,$merchant_id,$carray['attribute_mapped']);
            else
                $responseOption=Jetproductinfo::createoption($product,$carray,$this->jetHelper,FULLFILMENT_NODE_ID,$merchant_id);
               
            if(isset($responseOption['errors']))
            {   
                $uploadErrors['variation_upload'][$product->id]=$responseOption['errors'];
                $errordisplay.="Variants Upload Error: ".$responseOption['errors'].'<br>';
                unset($responseOption['errors']);
            }

            if(isset($responseOption['children_skus']) && count($responseOption['children_skus'])>=1)
            {   
                $sku = $responseOption['isParent'];
                unset($responseOption['isParent']);

                $path=\Yii::getAlias('@webroot').'/var/jet/product/upload/'.$merchant_id.'/variant/'.$product->sku.'<=>'.$sku;
                if(!file_exists($path)){
                    mkdir($path,0775, true);
                }

                $filenameOrig=$path.'/variation.log';
                $fileOrig=fopen($filenameOrig,'w');
                fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($responseOption));
               
                //file log
                $status_code=true;
                $responseA=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/variation',json_encode($responseOption),MERCHANT_ID,$status_code);

                fwrite($fileOrig,PHP_EOL."variation status code : ".$status_code);
                fwrite($fileOrig,PHP_EOL."variation response: ".PHP_EOL.json_encode($responseA));
                $responseA=json_decode($responseA,true);

                if(isset($responseA['errors'])){
                    $errordisplay.='Error in variation relationship Upload (sku)'.$sku.'=>'.json_encode($responseA['errors']).'<br>';
                    $uploadErrors['variation'][]=$sku." : ".json_encode($responseA['errors']);
                }
                elseif($status_code==200 || $status_code==201 || $status_code==202)
                {
                	unlink($filenameOrig);// remove log file
                    $status_code=true;
                    $vresult=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),MERCHANT_ID,$status_code);
                    $vresponse=json_decode($vresult,true);
                    if(count($vresponse)>0 && isset($vresponse['variation_refinements']))
                    {
                        //$variationCount++;
                        $sql="UPDATE `jet_product` SET  error='',status='Under Jet Review' where id='".$pid."'";
                        Data::sqlRecords($sql,null,'update');
                        $return_msg['success']="Product with sku : <b>".$sku."</b> successfully uploaded.";
                        unset($product);
                        return json_encode($return_msg);
                    }
                }
                fclose($fileOrig);
            }
            unset($responseA,$vresult,$vresponse,$responseOption);
            if(count($uploadErrors)>0)
            {
                $message="";
                $message.="<b>There are following information that are incomplete/wrong for given product(s):</b><ul>";
                if(isset($uploadErrors['variation']) && count($uploadErrors['variation'])>0)
                {   
                    $message.="<li><span class='required_label'>Error in Variantion Product(s)</span>
                                    <ul>
                                        <li><span class='required_values'>".implode(', ',$uploadErrors['variation'])."</span></li>
                                    </ul>
                                </li>";
                }
                if(isset($uploadErrors['variation_upload']) && count($uploadErrors['variation_upload'])>0){
                    $message.="<li><span class='required_label'>Some Variant Product(s) Not uploaded.</span><ul>";
                    foreach($uploadErrors['variation_upload'] as $key=>$value){
                        $message.="<li><b>".$sku."</b> => <span class='required_values'>".$value."</span></li>";
                    }
                    $message.="</ul></li>";
                }   
                $message.="</ul>";
                $return_msg['error']=$message;
                if($errordisplay!="")
                {
                    $sql="UPDATE `jet_product` SET  error='".$errordisplay."' where id='".$pid."'";
                    Data::sqlRecords($sql,null,'update');
                }
                
                 if(!$carray['success']){
                    unset($uploadErrors,$product);
                        return json_encode($return_msg); 
                    }  
            }
        }
        
            
        $path=\Yii::getAlias('@webroot').'/var/jet/product/upload/'.$merchant_id.'/simple/'.$sku;
        if(!file_exists($path)){
            mkdir($path,0775, true);
        }   
        if(!empty($SKU_Array))
        {
            if(count($Attribute_array)>0)
                $SKU_Array['attributes_node_specific'] = $Attribute_array; // add attributes details
            $result[$sku]= $SKU_Array; // add merchant sku
            
            //file log
                
                $filenameOrig="";
                $filenameOrig=$path.'/Sku.log';
                $fileOrig="";
                $fileOrig=fopen($filenameOrig,'a+');
                fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($result));
                //fclose($fileOrig);
            //file log
            unset($SKU_Array,$Attribute_array);
            $qty=0;
            if(is_numeric($product->qty) && $product->qty>0)
                $qty=$product->qty;
            $resultQty='';
            
            $newPriceValue=$product->price;
            // change new price
            $option_price_new=0.00;
            if($priceType !='' && $priceValue!=0)
            {
                $updatePrice=0.00;
                $updatePrice=self::priceChange($newPriceValue,$priceType,$priceValue);
                if($updatePrice!=0)
                    $newPriceValue = $updatePrice;
            }
            $price[$sku]['price']=(float)$newPriceValue;//$product->price;
            $node['fulfillment_node_id']=$fullfillmentnodeid;
            $node['fulfillment_node_price']=(float)$newPriceValue;//$product->price;
            $price[$sku]['fulfillment_nodes'][]=$node; //price
            // Add inventory
            //$qty= $product->qty;
            $node1['fulfillment_node_id']=$fullfillmentnodeid;
            $node1['quantity']=(int)$qty;
            $inventory[$sku]['fulfillment_nodes'][]=$node1; // inventory            
        }
       
        if(!empty($result) && count($result)>0)
        {
            $uploaded_flag=false;
            $responseArray="";
            $status_code=true;
            
            $response = $this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku),json_encode($result[$sku]),MERCHANT_ID,$status_code);
            $responseArray=json_decode($response,true);
            //echo $response.'---code-'.$status_code;die;
            fwrite($fileOrig,"upload sku response: ".PHP_EOL.json_encode($response));
            fwrite($fileOrig,PHP_EOL."sku status code: ".$status_code.PHP_EOL);
            fclose($fileOrig);
            unset($result);
            if(/*$responseArray=="" &&*/ ($status_code==202 || $status_code==201 || $status_code==200 ))
            {
                $responsePrice="";
                $filenameOrig="";
                //price log
                $filenameOrig=$path.'/Price.log';
                $fileOrig="";
                $fileOrig=fopen($filenameOrig,'a+');
                fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($price[$sku]));
                //fclose($fileOrig);
                $status_code=true;
                $responsePrice = $this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($price[$sku]),MERCHANT_ID,$status_code);
                $responsePrice=json_decode($responsePrice,true);
                
                fwrite($fileOrig,PHP_EOL."price status code: ".$status_code.PHP_EOL);
                fwrite($fileOrig,PHP_EOL."price response: ".json_encode($responsePrice).PHP_EOL);
                fclose($fileOrig);
                unset($node);unset($price);
                
                if($responsePrice=="" && $status_code==202)
                {
                    $errordisplay="";
                    $responseInventory="";
                    //inventory log
                    $filenameOrig=$path.'/Inventory.log';
                    $fileOrig="";
                    $fileOrig=fopen($filenameOrig,'a+');
                    fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($inventory[$sku]));
                    //fclose($fileOrig);
                    $status_code=true;
                    $response = $this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory[$sku]),MERCHANT_ID,$status_code);
                    $responseInventory = json_decode($response,true);
                    fwrite($fileOrig,PHP_EOL."inventory status code: ".$status_code.PHP_EOL);
                    fwrite($fileOrig,PHP_EOL."inventory response: ".$response.PHP_EOL);
                    unset($node1);unset($inventory);
                    fclose($fileOrig);
                    if($status_code!=202)
                    {
                        $message="";
                        $uploadInventoryError=($responseInventory||isset($responseInventory['errors']))?json_encode($responseInventory):"HTTP Response Code: ".$status_code;    
                        $message.="<li><span class='required_label'>Product with sku :<b>".$sku."</b> not uploaded due to error in inventory data.</span>
                                    <ul>
                                        <li><span class='required_values'>Error from Jet : ".$uploadInventoryError." </span></li>
                                    </ul>
                                </li>";
                        $message.="</ul>";
                        $return_msg['error']=$message;
                        $sql="UPDATE `jet_product` SET  error='".$uploadInventoryError."' where id='".$pid."'";
                        Data::sqlRecords($sql,null,'update');
                        unset($product);
                        return json_encode($return_msg);
                    }
                    $uploaded_flag=true;
                }
                else
                {
                    $message="";
                    $uploadPriceError=($responsePrice||isset($responsePrice['errors']))?json_encode($responsePrice):"HTTP Response Code: ".$status_code;    
                    $message.="<li><span class='required_label'>Product with sku :<b>".$sku."</b> not uploaded due to error in price data.</span>
                                    <ul>
                                        <li><span class='required_values'>Error from Jet : ".$uploadPriceError." </span></li>
                                    </ul>
                                </li>";
                    $message.="</ul>";
                    $return_msg['error']=$message;
                    $sql="UPDATE `jet_product` SET  error='".$uploadPriceError."' where id='".$pid."'";
                    Data::sqlRecords($sql,null,'update');
                    unset($responsePrice);unset($product);
                    return json_encode($return_msg);
                }
            }
            else
            {
                $message="";
                $uploadSkuError=($responseArray||isset($responseArray['errors']))?json_encode($responseArray):"HTTP Response Code: ".$status_code;
                $message.="<b>There are following information that are incomplete/wrong for given product:</b><ul>";
                $message.="<li><span class='required_label'>Product with sku :<b>".$sku."</b> not uploaded due to error in sku data.</span>
                                    <ul>
                                        <li><span class='required_values'>".$uploadSkuError." </span></li>
                                    </ul>
                                </li>";
                $message.="</ul>";
                $return_msg['error']=$message;
                $sql="UPDATE `jet_product` SET  error='".$uploadSkuError."' where id='".$pid."'";
                Data::sqlRecords($sql,null,'update');
                unset($product);
                return json_encode($return_msg);
            }
        if($uploaded_flag)
            {
                $uploadErrors = [];
                $result = $response = "";
                $uploadCount=0;
                $result=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),MERCHANT_ID,$status_code);
                $response=json_decode($result,true);
                if($response && !(isset($response['errors']))  && ($status_code==200 || $status_code==201 || $status_code==202) )
                {
                    $uploadCount++;
                    $sql="UPDATE `jet_product` SET  error='',status='Under Jet Review' where id='".$pid."'";
                    $model = Data::sqlRecords($sql,null,'update');
                    $return_msg['success']="Product with sku : <b>".$sku."</b> successfully uploaded.";
                    unset($product);
                    return json_encode($return_msg);
                }elseif($response!="" && isset($response['errors']))
                {
                    $message="";
                    $message.="<b>There are following information that are incomplete/wrong for given product:</b><ul>";
                    $message.="<li><span class='required_label'>Product with sku :".$sku."</span>
                                        <ul>
                                            <li><span class='required_values'>Error from Jet : ".json_encode($response['errors'])." </span></li>
                                        </ul>
                                    </li>";
                    $message.="</ul>";
                    $return_msg['error']=$message;
                    unset($product);
                    return json_encode($return_msg);
                } else
                {
                    $return_msg['error']= $sku."=> Response from jet : ".$status_code;
                    unset($product);
                    return json_encode($return_msg);
                }                 
            }
        }        
    }
    public function actionResendbatchupload()
    {
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $merchant_id = MERCHANT_ID;
        $return_msg['success'] = $return_msg['error']  = $resultDes = $message = $errordisplay = $type ="";
        $responseOptions = [];
        $count = $variationCount = $pid = $kmain = 0;
                
        $productforbatchupload = $result = $node = $inventory = $price = $uploadErrors = $responseOption = $SKU_Array =  $unique = $Attribute_arr = $Attribute_array = $_uniquedata = $carray = $product = $_uniquedata = $images = [];
        $productforbatchupload=$session->get('productforbatchupload');

        $not_exists_flag = $is_variation = false;
        $status_code=true;
        
        $pid=trim($productforbatchupload[$index]);
        if((count($productforbatchupload)-1)==$index)
        {
            $session->remove('productforbatchupload');
        }
        $mainProduct = Data::sqlRecords('SELECT `sku`,`attr_ids`,`product_type` FROM `jet_product` WHERE `type` = "variants" AND `id` ="'.$pid.'"','one','select');
       
        $varProduct = Data::sqlRecords('SELECT `option_sku`,`jet_option_attributes`,`option_id` from `jet_product_variants` where product_id="'.$pid.'"','all','select');

        if(is_array($varProduct) && count($varProduct)>0)
        {
            $responseOption['relationship'] = 'Variation';
            $attributes_of_jet = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($mainProduct['sku']),MERCHANT_ID,$status_code);
            $attr_jet=json_decode($attributes_of_jet,true);
           
            if(is_array($attr_jet['attributes_node_specific']))
            foreach ($attr_jet['attributes_node_specific'] as $val) {
                 $responseOption['variation_refinements'][]=(int)$val['attribute_id'];
            }
            foreach($varProduct as $value)
            { 
                if(!isset($responseOption['variation_refinements'])){
                    if (!empty($value['jet_option_attributes'])) {
                       $attr = json_decode(stripslashes($value['jet_option_attributes']),true);
                        foreach ($attr as $k => $map_val)
                        {
                            if(!in_array($k,$responseOption['variation_refinements']))
                            {
                                $responseOption['variation_refinements'][]=(int)$k;
                            }
                        }
                    }
                    else{
                        $resAttr = AttributeMap::getMappedOptionValues($value['option_id'], $mainProduct['attr_ids'], $mainProduct['product_type'], $merchant_id);
                        if(!empty($resAttr))
                        {
                           foreach ($resAttr as $k=>$map_val)
                            {
                                if(!isset($responseOption['variation_refinements']) || !in_array($k,$responseOption['variation_refinements']))
                                {
                                    $responseOption['variation_refinements'][]=(int)$k;
                                }
                            
                            } 
                        }
                    }
                }
                $checkSKU = Jetproductinfo::checkSkuOnJet($value['option_sku'],$this->jetHelper,$merchant_id);
                if ($checkSKU) {
                    if ($mainProduct['sku']!==$value['option_sku']) 
                        $responseOption['children_skus'][]=$value['option_sku'];
                }
            }
        }
        if(isset($responseOption['children_skus']) && count($responseOption['children_skus'])>=1)
        {   
            $sku = $mainProduct['sku'];
            $path=\Yii::getAlias('@webroot').'/var/jet/product/upload/'.$merchant_id.'/variant/'.$mainProduct['sku'].'<=>'.$sku;
            if(!file_exists($path)){
                mkdir($path,0775, true);
            }
            $filenameOrig=$path.'/variation.log';
            $fileOrig=fopen($filenameOrig,'w');
            fwrite($fileOrig,"\n".date('d-m-Y H:i:s')."\n".json_encode($responseOption));
           
            //file log
            $status_code=true;
            $responseA=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/variation',json_encode($responseOption),MERCHANT_ID,$status_code);
            fwrite($fileOrig,PHP_EOL."variation status code : ".$status_code);
            fwrite($fileOrig,PHP_EOL."variation response: ".PHP_EOL.json_encode($responseA));
            $responseA=json_decode($responseA,true);
            if(isset($responseA['errors'])){
                $errordisplay.='Error in variation relationship Upload (sku)'.$sku.'=>'.json_encode($responseA['errors']).'<br>';
                $uploadErrors['variation'][]=$sku." : ".json_encode($responseA['errors']);
            }
            elseif($status_code==200 || $status_code==201 || $status_code==202)
            {
                $status_code=true;
                $vresult=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),MERCHANT_ID,$status_code);
                $vresponse=json_decode($vresult,true);
                if(count($vresponse)>0 && isset($vresponse['variation_refinements']))
                {
                    //$variationCount++;
                    $sql="UPDATE `jet_product` SET  error='',status='Under Jet Review' where id='".$pid."'";
                    Data::sqlRecords($sql,null,'update');
                    $return_msg['success']="Product with sku : <b>".$sku."</b> successfully uploaded.";
                    unset($product);
                    return json_encode($return_msg);
                }
            }
            fclose($fileOrig);
        }
        unset($responseA,$vresult,$vresponse,$responseOption);
        if(count($uploadErrors)>0)
        {
            $message="";
            $message.="<b>There are following information that are incomplete/wrong for given product(s):</b><ul>";
            if(isset($uploadErrors['variation']) && count($uploadErrors['variation'])>0)
            {   
                $message.="<li><span class='required_label'>Error in Variantion Product(s)</span>
                                <ul>
                                    <li><span class='required_values'>".implode(', ',$uploadErrors['variation'])."</span></li>
                                </ul>
                            </li>";
            }
            if(isset($uploadErrors['variation_upload']) && count($uploadErrors['variation_upload'])>0){
                $message.="<li><span class='required_label'>Some Variant Product(s) Not uploaded.</span><ul>";
                foreach($uploadErrors['variation_upload'] as $key=>$value){
                    $message.="<li><b>".$sku."</b> => <span class='required_values'>".$value."</span></li>";
                }
                $message.="</ul></li>";
            }   
            $message.="</ul>";
            $return_msg['error']=$message;
            if($errordisplay!="")
            {
                $sql="UPDATE `jet_product` SET  error='".$errordisplay."' where id='".$pid."'";
                Data::sqlRecords($sql,null,'update');
            }
            unset($uploadErrors,$product);
            return json_encode($return_msg);
        }

        return json_encode("Variantion not sent ! Please Resend variants");
    }

    public function actionStartbatchunarchieved()
    {
        $session = Yii::$app->session;
        $jetconfig = $productAll = $productLoad = [];  
        $merchant_id = MERCHANT_ID;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = "";
        $status=true;
              
        $productAll=unserialize($session->get('productAll'));
                
        $pid=0;
        
        $productLoad=$productAll[$index];
        if((count($productAll)-1)==$index)
        {
            $session->remove('productAll');
            unset($productAll);
        }
        if(!empty($productLoad))
        {
            $sku=$productLoad['sku'];
            $qty=$productLoad['qty'];
            $id1=$productLoad['id'];
            $prod_type=$productLoad['type'];
            
            $prod_exist = "";
            $prod_exist = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id,$status);
            $prod_exist_response = json_decode($prod_exist,true);

            if($prod_exist_response=="")
            {
                $return_msg['error']="Product with sku : <b>".$sku."</b> is not uploaded on jet.";
                return json_encode($return_msg);
            }
            $inventory = [];
            $data['is_archived']=false;
            
            if ($prod_type=='simple') 
            {
                $node1['fulfillment_node_id']=FULLFILMENT_NODE_ID;
                $node1['quantity']=(int)$qty;
                $inventory['fulfillment_nodes'][]=$node1;
                $status=true;
                $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode($data),$merchant_id,$status);
                if ($status!=202) {
                	$data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode($data),$merchant_id,$status);
                }
                $status=true;
                $inventry=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory),$merchant_id,$status);
                
                if($status==202)
                {
                    $status=true;
                    $response=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id,$status); 
                    $skuResponse=json_decode($response,true);
                    if(is_array($skuResponse) && count($skuResponse)>0 && $status==200){
                        $sql="UPDATE `jet_product` SET  status='".$skuResponse['status']."' where id='".$id1."'";
                        Data::sqlRecords($sql,null,'update');
                    }
                    $return_msg['success']="Product with sku : <b>".$sku."</b> is successfully unarchived on jet.";
                    return json_encode($return_msg);
                }
                else
                {
                    $return_msg['error']="Product with sku : <b>".$sku."</b> is not unarchived on jet.";
                    return json_encode($return_msg);
                }
            }
            elseif ($prod_type=='variants') 
            {
                $notUnarchiveCount = $variantCount = $unarchiveCount = 0;
                $optionProduct = [];
                $sql="SELECT `option_sku`,`option_qty`,`option_id` FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `product_id`='".$id1."'";
                $optionProduct = Data::sqlRecords($sql,"all","select");
                if (!empty($optionProduct)) 
                {
                    foreach ($optionProduct as $key => $value) 
                    {     
                        $variantCount++;
                        $node1=$option_inventory=[];                                
                        $node1['fulfillment_node_id']=FULLFILMENT_NODE_ID;
                        $node1['quantity']=(int)$value['option_qty'];
                        $option_inventory['fulfillment_nodes'][]=$node1;
                        $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($value['option_sku']).'/status/archive',json_encode($data),$status);
                        if($status!=202)
                        {
                            $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($value['option_sku']).'/status/archive',json_encode($data),$merchant_id,$status);
                        }
                        $inventry=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($value['option_sku']).'/inventory',json_encode($option_inventory),$merchant_id); 
                        if($status==202)
                        {
                            $unarchiveCount++;
                            if($sku==$value['option_sku'])
                            {
                                $response=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($value['option_sku']),$merchant_id,$status); 
                                $skuResponse=json_decode($response,true);
                                if(is_array($skuResponse) && count($skuResponse)>0 && $status==200){
                                    $sql="UPDATE `jet_product` SET  status='".$skuResponse['status']."' where id='".$id1."'";
                                    Data::sqlRecords($sql,null,'update');
                                }                               
                            }
                        }
                        else
                        {
                            $notUnarchiveCount++;
                        }                       
                    }
                    if($unarchiveCount==$variantCount){
                        $return_msg['success']="All variant sku(s) is unarchived on jet";
                        return json_encode($return_msg);
                    }
                    else
                    {
                        $return_msg['error']=$unarchiveCount." variant sku(s) is unarchived and ".$notUnarchiveCount." is not unarchived on jet";
                        return json_encode($return_msg);
                    }

                }else{
                    $return_msg['error']="No more variantion available for this product";
                    return json_encode($return_msg);
                }
            }     
        }else{
            $return_msg['error']="Product not Found.";
            return json_encode($return_msg);
        }                    
        return json_encode($return_msg);
    }
    public function actionStartbatcharchieved()
    {
        $session = Yii::$app->session;
        $jetconfig = $productAll = $productLoad = $inventory = $node1 = []; 
        $return_msg['success'] = $return_msg['error'] = "";  
        $status=true;            
        $notArchiveCount = $archiveCount = $pid = 0;

        $merchant_id = MERCHANT_ID;
        $index=Yii::$app->request->post('index');        
        $productAll=unserialize($session->get('productAll'));        
        $productLoad=$productAll[$index];
        if((count($productAll)-1)==$index)
        {
            $session->remove('productAll');
            unset($productAll);
        }
        $fullfillmentnodeid = FULLFILMENT_NODE_ID;
        $node1['fulfillment_node_id']=FULLFILMENT_NODE_ID;
        $node1['quantity']=0;
        $inventory['fulfillment_nodes'][]=$node1;
        if(!empty($productLoad))
        {
            $sku=$productLoad['sku'];
            $id1=$productLoad['id'];
            $prod_type=$productLoad['type'];

            $result=$this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id,$status);
            $response=json_decode($result,true);
            if($response && isset($response['errors']) )
            {
                $return_msg['error']="Product sku : <b>".$sku."</b> is not uploaded on jet.";
                return json_encode($return_msg);
            }
            $data=array();
            $data['is_archived']=true;
                       

            if ($prod_type=='simple') 
            {        
                $this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory),$merchant_id);
                $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode($data),$merchant_id,$status);  
                if($status!=202)
                    $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode($data),$merchant_id,$status);  
                Jetproductinfo::updateQtyOnJet($sku,0,$this->jetHelper,$fullfillmentnodeid,$merchant_id);             
                if($status==202)
                {
                    $sql="UPDATE `jet_product` SET  status='Archived' where id='".$id1."'";
                    Data::sqlRecords($sql,null,'update');
                    $return_msg['success']="Product with sku : <b>".$sku."</b> is successfully archived on jet.";
                    return json_encode($return_msg);
                }else{
                    $return_msg['error']="Product with sku : <b>".$sku."</b> is not archived on jet.";
                    return json_encode($return_msg);
                }
            }
            elseif ($prod_type=='variants') 
            {
                $optionProduct = [];
                $sql="SELECT `option_sku` FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND `product_id`='".$id1."'";
                $optionProduct = Data::sqlRecords($sql,"all","select");
                $variantCount = $archiveCount = $notArchiveCount = 0;
                if (!empty($optionProduct)) 
                {
                    foreach ($optionProduct as $key => $value) 
                    {   
                        $variantCount++;   
                        $this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($value['option_sku']).'/inventory',json_encode($inventory),$merchant_id);                                        
                        $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($value['option_sku']).'/status/archive',json_encode($data),$merchant_id,$status);
                        if($status!=202){
                            $data1=$this->jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($value['option_sku']).'/status/archive',json_encode($data),$merchant_id,$status);
                        }
                        Jetproductinfo::updateQtyOnJet($value['option_sku'],0,$this->jetHelper,$fullfillmentnodeid,$merchant_id);
                        if($status==202)
                        {
                            $archiveCount++;
                            if($sku==$value['option_sku'])
                            {
                                $sql="UPDATE `jet_product` SET  status='Archived' where id='".$id1."'";
                                Data::sqlRecords($sql,null,'update');
                            }
                        }
                        else
                        {
                            $notArchiveCount++;
                        }                                            
                    }
                    
                    if($archiveCount==$variantCount){
                        $return_msg['success']="All variant sku(s) is archived on jet";
                        return json_encode($return_msg);
                    }
                    else
                    {
                        $return_msg['error']=$archiveCount." variant sku(s) is archived and ".$notArchiveCount." is not archived on jet";
                        return json_encode($return_msg);
                    }  
                }else{
                    $return_msg['error']="No more variantion available for this product";
                    return json_encode($return_msg);
                }
            }            
        }else{
            $return_msg['error']="Product not Found.";
            return json_encode($return_msg);
        }
        return json_encode($return_msg);
    }
   
    public function actionReturnexception()
    {
        $merchant_id=MERCHANT_ID;
        $fullfillmentnodeid=FULLFILMENT_NODE_ID;
       
        $data=array();
        $data=Yii::$app->request->queryParams;
         
        if($data)
        {
            $returnexception=array();
            $returnexception['time_to_return']=(int)$data['time_to_return'];
            $returnexception['return_location_ids'][]=$data['return_location_id'];
            $returnexception['return_shipping_methods'][]=$data['return_ship_method'];
            $response="";
            $response=$this->jetHelper->CPutRequest("/merchant-skus/".rawurlencode($data['sku'])."/returnsexception",json_encode($returnexception),$merchant_id);
            if(json_decode($response,true)=="")
            {
                $model="";
                $model=JetReturnException::findOne($data['product_id']);
                if($model){
                    $model->time_to_return=$data['time_to_return'];
                    $model->return_location_ids=$data['return_location_id'];
                    $model->return_shipping_methods=$data['return_ship_method'];
                    $model->save(false);
                }else{
                    $modelnew="";
                    $modelnew = new JetReturnException();
                    $modelnew->product_id=$data['product_id'];
                    $modelnew->sku=$data['sku'];
                    $modelnew->merchant_id=$merchant_id;
                    $modelnew->time_to_return=$data['time_to_return'];
                    $modelnew->return_location_ids=$data['return_location_id'];
                    $modelnew->return_shipping_methods=$data['return_ship_method'];
                    $modelnew->save(false);
                }
            }
            return $response;
        }
    }
    public function actionProductstatus()
    {
        $session = Yii::$app->session;
        $collection = $collection = [];
        $merchant_id=MERCHANT_ID;
        
        if(!is_object($session)){
            Yii::$app->session->setFlash('error', "Can't initialize Session,Please retry to update product status");
            return $this->redirect(['index']);
        }
        if(!$this->jetHelper)
            $this->jetHelper = new Jetapimerchant(API_HOST,API_USER,API_PASSWORD);        
        
        if($merchant_id)
        {
            $response = $status = $status1 =  "";
            $updateCount = 0;
            $checkUploadedCount = $resArray = [];
            $checkUploadedCount = json_decode($this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size=1',$merchant_id,$status1),true);  
            
            if ($status1==200 && isset($checkUploadedCount['total'])){
	            $response =$this->jetHelper->CGetRequest('/portal/merchantskus?from=0&size='.$checkUploadedCount['total'],$merchant_id,$status);
	            $resArray=json_decode($response,true);
            }
            
            if(isset($resArray['merchant_skus']) && count($resArray['merchant_skus'])>0 && $status==200)
            {
                $chunkStatusArray=array_chunk($resArray['merchant_skus'], 100);
                foreach ($chunkStatusArray as $ind=> $value) 
                {
                    $session->set('productstatus-'.$ind, $value);                               
                }
                return $this->render('batchupdatestatus', [
                        'totalcount' => $resArray['total'],
                        'pages' => count($chunkStatusArray)
                ]);
            }
            else
            {
                Yii::$app->session->setFlash('error', "Product Status api not working, Please try later ...");
            }
        } 
        return $this->redirect(Yii::$app->request->referrer);   
    }
    public function actionBatchProductStatus()
    {
        $merchant_id=MERCHANT_ID;
        $session = Yii::$app->session;        
        $productData = [];
        $updateCount=0;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = "";
        $productData = $session->get('productstatus-'.$index);
        $notSkuAvailable=0;
        $flag=false;
        if(is_array($productData) && count($productData)>0)
        {        
            foreach($productData as $value)
            {                     
                if (isset($value['status'])) 
                {
                    $query='UPDATE jet_product set status="'.$value['status'].'" WHERE merchant_id="'.$merchant_id.'" AND sku="'.addslashes($value['merchant_sku']).'"';
                    $updateProResponse=Data::sqlRecords($query,null,'update');     
                    
                    if($updateProResponse==1)
                        $updateCount++;
                    
                    $updateVarResponse = Data::sqlRecords('UPDATE jet_product_variants set status="'.$value['status'].'" WHERE merchant_id="'.$merchant_id.'" AND option_sku="'.addslashes($value['merchant_sku']).'"',null,'update');

                    if($updateVarResponse==1)
                        $updateCount++;                    
                }                    
            }
        }
        $session->remove('productstatus-'.$index);
        if($updateCount>0)
            $return_msg['success']=$updateCount." Product Status(s) Updated";
        else
            $return_msg['success']="product sku(s) status already uptodate in app";
        return json_encode($return_msg);
    }

    public function actionBatchproductupdate()
    {
        if (Yii::$app->user->isGuest) 
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
         
        $session = Yii::$app->session;
        $merchant_id=MERCHANT_ID;
        $query="SELECT COUNT(id) as ids FROM jet_product_tmp WHERE merchant_id='".MERCHANT_ID."'";
        $collection=Data::sqlRecords($query,"all","select");
        
        if(isset($collection[0]['ids']) && $collection[0]['ids']>0)
        {
            if($collection[0]['ids']<=100)
                $pages=1;    
            else
                $pages=ceil($collection[0]['ids']/100);
            $installData = [];
            $customData = JetProductInfo::getConfigSettings($merchant_id);
            $customPrice = (isset($customData['fixed_price']) && $customData['fixed_price']=='yes')?$customData['fixed_price']:"";
            $newCustomPrice = (isset($customData['set_price_amount']) && $customData['set_price_amount'])?$customData['set_price_amount']:"";
            $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";
            Data::checkInstalledApp($merchant_id,$type=false,$installData);
            $onWalmart=isset($installData['walmart'])?true:false;
            $onNewEgg=isset($installData['newegg'])?true:false;
            $session->set('product_page',$pages);
            $session->set('customPrice',$customPrice);
            $session->set('newCustomPrice',$newCustomPrice);
            $session->set('import_status',$import_status);
            $session->set('onWalmart',$onWalmart);
            $session->set('onNewEgg',$onNewEgg);
            return $this->render('batchproductupdate', 
            [
                'totalcount' => $collection[0]['ids'],
                'pages'=>$pages
            ]);
        }
        else
        {
            Yii::$app->session->setFlash('success', "All Store Product(s) already synced.");
        }
        return $this->redirect(['index']);

    }
    public function actionStartbatchupdate()
    {
        if (Yii::$app->user->isGuest) 
            return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
         
        $session = Yii::$app->session;        
        $message="";
        $return_msg = $productUpdate = [];
        
        $index=Yii::$app->request->post('index');
        $merchant_id=MERCHANT_ID;
        $fullfillmentnodeid=FULLFILMENT_NODE_ID;
        $productCount=$session->get('product_page');
        $customPrice=$session->get('customPrice');
        $newCustomPrice=$session->get('newCustomPrice');
        $import_status = $session->get('import_status');
        $onWalmart = $session->get('onWalmart');
        $onNewEgg = $session->get('onNewEgg');
        $offset=$index*100;
        $query='SELECT `product_id`,`data` FROM `jet_product_tmp` WHERE merchant_id="'.$merchant_id.'" LIMIT '.$offset.',100';
        $productUpdate=Data::sqlRecords($query,'all','select');
        if(!empty($productUpdate) && count($productUpdate)>0)
        {
            $count = 0;
            foreach($productUpdate as $value)
            {
                $file=Data::createFile('jet/product/update/'.$merchant_id.'/'.$value['product_id'].'.log',$mode='w');
                $data = $result = [];
                $query='SELECT id,title,sku,type,product_type,description,variant_id,image,qty,weight,price,attr_ids,jet_attributes,vendor,upc,fulfillment_node FROM `jet_product` WHERE `id`="'.$value['product_id'].'" LIMIT 0,1';
                $result=Data::sqlRecords($query,"one","select");
                $data = json_decode($value['data'],true);
                if(!empty($result)) 
                {
                    $count++;
                    if(is_array($data) && count($data)>0)
                        Jetproductinfo::productUpdateData($result,$data,$this->jetHelper,$fullfillmentnodeid,$merchant_id,$file,$customPrice,$newCustomPrice,$onWalmart,$onNewEgg,$import_status);
                }
                else
                {
                    $message= "add new product with product id: ".$value['product_id'].PHP_EOL;
                    fwrite($file, $message);
                    $customData = JetProductInfo::getConfigSettings($merchant_id);
                    $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:""; 
                    Jetproductinfo::saveNewRecords($data, $merchant_id, $import_status);
                }
            }
            $query='DELETE FROM `jet_product_tmp` where merchant_id="'.$merchant_id.'"';
            Data::sqlRecords($query,null,"delete");

            $return_msg['success']['message'] = "Product(s) information successfully updated";
            $return_msg['success']['count'] = $count;
        }
        else
        {
            $return_msg['success']['message'] = "All products already synced";
            $return_msg['success']['count'] = 0;
        }

        if((count($productCount)-1)==$index)
        {
            $session->remove('product_page');
        }
        return json_encode($return_msg);        
    }
    
    public function actionBatchimport()
    {
        $merchant_id =MERCHANT_ID;        
        $countProducts=0;$pages=0;
        $customData = JetProductInfo::getConfigSettings($merchant_id);
        $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";
        if($import_status=="published")
            $countProducts=$this->sc->call('GET', '/admin/products/count.json',array('published_status'=>'published')); 
        else
            $countProducts=$this->sc->call('GET', '/admin/products/count.json'); 

        if(isset($countProducts['errors'])){
            Yii::$app->session->setFlash('error', "Shopify api token is incorrect...");
            return $this->redirect(['index']);
        }
        $pages=ceil($countProducts/250);
        // $pages+=1;
        $session = Yii::$app->session;
        if(!is_object($session)){
            Yii::$app->session->setFlash('error', "Can't initialize Session.Product(s) import cancelled");
            return $this->redirect(['index']);
        }
        $session->set('product_page',$pages);
        return $this->render('batchimport', [
                'totalcount' => $countProducts,
                'pages'=>$pages
        ]);
    }
    public function actionBatchimportproduct()
    {
        $index = Yii::$app->request->post('index');

        $merchant_id = MERCHANT_ID;
        try
        {            
            if(!$this->sc)
            {
                $this->sc = new ShopifyClientHelper(SHOP, TOKEN, PUBLIC_KEY, PRIVATE_KEY);    
            }
            $customData = JetProductInfo::getConfigSettings($merchant_id);

            $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";
            /* if ($merchant_id==397)
            	$importParam['published_status'] = "unpublished";
            else */
            	$importParam['published_status'] = $import_status;

            $importParam['limit']=250;
            $importParam['page']=$index;
            //array('published_status'=>'published','limit'=>$limit,'page'=>$index)
            $products = $this->sc->call('GET', '/admin/products.json',$importParam); 
            if(isset($products['errors'])){
                $returnArr['error'] = $products['errors'];
                return json_encode($returnArr);
            }
            $readyCount = $notSku = $notType = 0;
            if($products)
            {
                foreach ($products as $prod)
                {
                    $response = Jetproductinfo::saveNewRecords($prod, $merchant_id, $import_status, true);
                    if(isset($response['success']))
                        $readyCount++;
                    if(isset($response['error']))
                    {
                        if($response['error']=="sku")
                            $notSku++;
                        if($response['error']=="product_type")
                            $notType++;
                    }
                }
            }
          
        }
        catch (ShopifyApiException $e){
            return $returnArr['error'] = $e->getMessage();
        }
        catch (ShopifyCurlException $e){
            return $returnArr['error'] = $e->getMessage();
        }
        $returnArr['success']['count'] = $readyCount;
        $returnArr['success']['not_sku'] = $notSku;
        $returnArr['success']['not_type'] = $notType;
        return json_encode($returnArr);
    }
    
    public function actionShowimage()
    {
        $this->layout="main2";
        $data=Yii::$app->request->post('ids');
        $ids=[];
        if(is_array($data) && count($data)>0){
            foreach ($data as $key => $value)
            {
                if($value['name']=="selection[]" && $value['value'])
                {
                    $ids[]="'".trim($value['value'])."'";
                    //break;
                }
    
            }
            $id=implode(',',$ids);
            //echo $id;die;
            $collection=[];
            $sql="SELECT  image,id,sku from jet_product  where id in (".$id.")";
            $collection=Yii::$app->getDb()->createCommand($sql)->queryAll();
            $html=$this->render('show_image',array('collection'=>$collection),true);
            return $html;
        }
    }
    public function actionSaveimage()
    {
        $merchant_id=MERCHANT_ID;
        if (Yii::$app->request->isPost) 
        {
            $files=[];
            $id=Yii::$app->request->post('id');
            $images=[];
            $images=Yii::$app->request->post('image');
            
            $model="";
            $model=JetProduct::find()->select('id,image')->where(["id"=>$id , "merchant_id"=>$merchant_id])->one();
            
            $arrImage=[];
            $finalimges=[];
            $imageNameArr=[];
            $basPath=\Yii::getAlias('@webroot').'/upload/jet/images/'.$merchant_id.'/'.$id;
            if(!file_exists($basPath)){
                mkdir($basPath,0775, true);
            }
            //$basPath=Yii::getAlias('@webroot').'/upload/images/'.$merchant_id.'/'.$id;
            if(is_array($images) && count($images)>0)
            {
                foreach ($images as $key => $value)
                {
                    $imageNameArr[$value]='upload/images/'.$merchant_id.'/'.$id.'/'.$key;
                }
                $imageModel='';
                $imageModel=UploadedFile::getInstancesByName('files');
                if(is_array($imageModel) && count($imageModel))
                {
                    foreach ($imageModel as $key => $value)
                    {
                        $url=[];
                        $url=pathinfo($value->name);
                        $value->saveAs($basPath.'/'.$url['basename']);
                    }
                }
                ksort($imageNameArr);
                //var_dump($imageNameArr);echo "<hr>";
                $finalimges=array_values($imageNameArr);
                //var_dump($finalimges);die;
                //$additional_info=[];
                //$additional_info=json_decode($model->image,true);
                //$additional_info['images']=$finalimges;
                //echo "<pre>";
                //print_r($finalimges);
                
                //die;
                $model->image=implode(",",$finalimges);
                $model->save(false);
                //$model="";
                //$model=JetProduct::find()->select('id,image')->where(['id'=>$id])->one();
                return "image updated successfully";
    
            }
        }
    }
     
    public function actionGetjetdata()
    {
        $this->layout="main2";
        $merchant_id = MERCHANT_ID;
        $sku=trim(Yii::$app->request->post('id'));
        $skuResponse = $qtyResponse = $priceResponse = "";
        $skuResult = $qtyResult = $priceResult = [];
        $skuResponse = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id);                
        $qtyResponse = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',$merchant_id);       
        $priceResponse = $this->jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku).'/price',$merchant_id);
        
        $skuResult = json_decode($skuResponse,true);
        $qtyResult = json_decode($qtyResponse,true);
        $priceResult = json_decode($priceResponse,true);
        
        if($skuResponse && !isset($skuResult['errors']))
            $html=$this->render('viewJet',['skuData'=>$skuResult,'qtyData'=>$qtyResult,'priceData'=>$priceResult],true);
        else
            return "Error From Jet:".json_encode($skuResponse);
        return $html;            
    }

    public function actionEditdata()
    {
        $this->layout="main2";
        $merchant_id = MERCHANT_ID;
        $id=trim(Yii::$app->request->post('id'));        
        $model = $this->findModel($id);
        
        $session = Yii::$app->session;
        $productData = [
                        'model'=>$model,
                        ];
        $session_key = 'product'.$id;
        $session->set($session_key, $productData);
        $sql = "SELECT `update_title`,`update_price`,`update_description`,`pack_qty` FROM `jet_product_details` WHERE `merchant_id`='{$merchant_id}' AND `product_id`='{$id}'";
        $custom_details = Data::sqlRecords($sql,'one','select');       
        $html = $this->render('editdata',
                            [
                                'id'=>$id,
                                'model'=>$model,
                                'custom_details' => $custom_details,
                            ],
                            true);
        return $html;
    }
    
    public function actionRenderCategoryTab()
    {
        $this->layout="main2";
        $session = Yii::$app->session;
        $html = '';
        $id = Yii::$app->request->post('id');
        $merchant_id = MERCHANT_ID ;
        if($id)
        {
            $session_key = 'product'.$id;
            $product = $session->get($session_key);
                       
            if(is_array($product))
            {
                $model = $product['model'];

                $attributes=[];
                if(defined('API_USER'))
                {
                    $fullfillmentnodeid=FULLFILMENT_NODE_ID;                    
                    $response = $this->jetHelper->CGetRequest('/taxonomy/nodes/'.$model->fulfillment_node.'/attributes',$merchant_id);
                    $attributes=json_decode($response,true);
                }

                $merchantCategory = Data::sqlRecords("SELECT `title`,`parent_title`,`root_title` FROM `jet_category` WHERE category_id='".$model->fulfillment_node."' LIMIT 0,1","one","select");

                $html = $this->render('category_tab2',[
                                'model' => $model,
                                'merchantCategory'=>$merchantCategory,
                                'attributes'=>$attributes
                            ]);
            }
        }
        //$session->close();
        return json_encode(['html'=>$html]);
    }   

    public function actionErrorjet()
    {
        $this->layout="main2";
        $id=trim(Yii::$app->request->post('id'));
        $merchant_id = MERCHANT_ID;
        $errorData = [];
        $errorData = Data::sqlRecords('SELECT `title`,`error` from `jet_product` where merchant_id="'.$merchant_id.'" AND `id`="'.$id.'"','one','select');
        
        $html=$this->render('errorjet',array('data'=>$errorData),true);
        return $html;           
    }
    public function actionUpdateajax($id)
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
    	$merchant_id = MERCHANT_ID;
    	$model = $this->findModel($id);
    	$data = [];
    	$sku=$model->sku;
    	
    	if ($model->load(Yii::$app->request->post()))
    	{
    		$product_sku = $product_id = $product_asin = $product_mpn = $product_vendor = "";
    		$return_status=[];
    	
    		$product_id=$model->id;
    		$product_sku=$_POST['JetProduct']['sku'];
    		$upc = isset($_POST['JetProduct']['upc'])?$_POST['JetProduct']['upc']:$model->upc;
    		$product_asin=isset($_POST['JetProduct']['ASIN'])?trim($_POST['JetProduct']['ASIN']):$model->ASIN;
    		$product_mpn=isset($_POST['JetProduct']['mpn'])?trim($_POST['JetProduct']['mpn']):$model->mpn;
    	
    		$update_title = isset($_POST['JetProduct']['update_title'])?$_POST['JetProduct']['update_title']:"";
    		$update_description = isset($_POST['JetProduct']['update_description'])?$_POST['JetProduct']['update_description']:"";
    		$pack_qty = isset($_POST['JetProduct']['pack_qty'])?$_POST['JetProduct']['pack_qty']:"";
    	
    		$sql1 = "UPDATE `jet_product_details` SET `update_title` = '".addslashes($update_title)."',`pack_qty`='".(int)$pack_qty."',`update_description` = '".addslashes($update_description)."' WHERE `jet_product_details`.`product_id` = ".$product_id;
    		Data::sqlRecords($sql1,null,'update');
    	
    		if(Yii::$app->request->post('product-type')=='variants')
    		{
    			$jet_attr = $options = $new_options = $pro_attr = $attributes_of_jet = $other_vari_opt = [];
    			if(Yii::$app->request->post('jet_attributes')){
    				$jet_attributes=Yii::$app->request->post('jet_attributes');
    			}
    			if(Yii::$app->request->post('attributes_of_jet')){
    				$attributes_of_jet=Yii::$app->request->post('attributes_of_jet');
    			}
    			if($other_vari_opt = Yii::$app->request->post('jet_varients_opt'))
    			{
                    $product_error=[];
    				$er_msg="";
    				$chek_flag=false;
    				if(is_array($other_vari_opt) && count($other_vari_opt)>0)
    				{
    					foreach($other_vari_opt as $k_opt_id=>$v_opt)
    					{
    						$opt_asin = $opt_mpn = $option_sku = $er_msg1 = $upc_error_msg = $asin_error_msg = $mpn_error_msg = "";
    						$upc_success_flag = $mpn_success_flag = $invalid_asin = $invalid_upc = $invalid_mpn = false;
    						$asin_success_flag=true;
    						$opt_upc=$v_opt['option_unique_id'];
    						$opt_asin=$v_opt['asin'];
    						$opt_mpn=$v_opt['mpn'];
    						$option_sku=$v_opt['optionsku'];
    						$opt_barcode_type=Jetproductinfo::checkUpcType($opt_upc);
    	
    						$asin_error_msg="";
    						if(strlen($opt_upc)>0){
    							list($upc_success_flag,$upc_error_msg)=Jetproductinfo::checkProductOptionBarcodeOnUpdate($other_vari_opt,$v_opt,$k_opt_id,$opt_barcode_type,$upc,$product_id,$product_sku);
    						}
    						if(strlen($opt_asin)>0){
    							list($asin_success_flag,$asin_error_msg)=Jetproductinfo::checkProductOptionAsinOnUpdate($other_vari_opt,$v_opt,$k_opt_id,$product_asin,$product_id,$product_sku);
    						}
    						if(strlen($opt_mpn)>0){
    							list($mpn_success_flag,$mpn_error_msg)=Jetproductinfo::checkProductOptionMpnOnUpdate($other_vari_opt,$v_opt,$k_opt_id,$product_mpn,$product_id,$product_sku);
    						}
    						if( (is_numeric($opt_upc) && !$opt_barcode_type) || (is_numeric($opt_upc) && $opt_barcode_type && !$upc_success_flag))
    						{
    							$invalid_upc=true;
    						}
    						if((strlen($opt_asin)>0 && strlen($opt_asin)!=10) || (strlen($opt_asin)==10 && !ctype_alnum ($opt_asin)) || (strlen($opt_asin)==10 && ctype_alnum ($opt_asin) && !$asin_success_flag))
    						{
    							$invalid_asin=true;
    						}
    						if( strlen($opt_mpn)>50 || (strlen($opt_mpn)<=50 && !$mpn_success_flag)){
    							//$product_error['invalid_mpn'][]=$option_sku;
    							//$chek_flag=true;
    							$invalid_mpn=true;
    						}
    	
    						if($invalid_upc && $invalid_asin && $invalid_mpn){
    							$chek_flag=true;
    							$product_error['invalid_asin'][]=$option_sku;
    						}
    					}
    				}
    	
    				if(count($product_error)>0)
    				{
    					$error="";
    					if(isset($product_error['invalid_asin']) && count($product_error['invalid_asin'])>0){
    						$error.="Invalid/Missing Barcode or ASIN or MPN for sku(s): ".implode(', ',$product_error['invalid_asin'])."<br>";
    					}
    					$return_status['error']=$error;
    					unset($error,$product_error);
    					return json_encode($return_status);
    				}
    			}
    			else
    			{
    				$asin_success_flag=$mpn_success_flag=$invalid_asin=$invalid_mpn=$chek_flag=false;
    				$er_msg=$type="";
    	
    				if(strlen($product_asin)>0){
    					$asin_success_flag=Jetproductinfo::checkAsinVariantSimple($product_asin,$product_id,$product_sku,$merchant_id);
    				}
    				if(strlen($product_mpn)>0){
    					$mpn_success_flag=Jetproductinfo::checkMpnVariantSimple($product_mpn,$product_id,$product_sku,$merchant_id);
    				}
    	
    				if($product_asin=="" || (strlen($product_asin)>0 && strlen($product_asin)!=10) || (strlen($product_asin)==10 && !ctype_alnum ($product_asin)) || (strlen($product_asin)==10 && ctype_alnum ($product_asin) && $asin_success_flag))
    				{
    					$invalid_asin=true;
    				}
    				if($product_mpn=="" || strlen($product_mpn)>50 || (strlen($product_mpn)<=50 && $mpn_success_flag)){
    					$invalid_mpn=true;
    				}
    				if($invalid_asin && $invalid_mpn){
    					$chek_flag=true;
    					$er_msg.="Invalid/Missing Barcode or ASIN or MPN, must be unique"."<br>";
    				}
    				if($chek_flag){
    					$return_status['error']=$er_msg;
    					return json_encode($return_status);
    				}
    			}
    			if($jet_attributes)
    			{
    				foreach($jet_attributes as $attr_id=>$value_arr)
    				{
    					$flag=false;
    					if(is_array($value_arr) && count($value_arr)>0)
    					{
    						$jet_attr_id="";
    						foreach($value_arr as $val_key=>$chd_arr)
    						{
    							if($val_key=="jet_attr_id" && trim($chd_arr)=="")
    							{
    								$flag=true;
    								foreach($value_arr as $v_key=>$c_ar)
    								{
    									if($v_key=="jet_attr_id"){
    										continue;
    									}elseif($v_key=="jet_attr_name"){
    										continue;
    									}elseif(is_array($c_ar)){
    										$new_options[trim($v_key)][trim($attr_id)]=trim($c_ar['value']);
    									}
    								}
    								break;
    							}
    							else
    							{
    								if($val_key=="jet_attr_id")
    								{
    									$str_id="";
    									$str_id_arr=array();
    									$str_id=trim($chd_arr);
    									$str_id_arr=explode(',',$str_id);
    									$jet_attr_id=trim($str_id_arr[0]);
    								}elseif($val_key=="jet_attr_name")
    								{
    									$unit="";
    									$s_unit=[];
    									if(count($attributes_of_jet)>0 && array_key_exists($jet_attr_id,$attributes_of_jet)){
    										$unit=$attributes_of_jet[$jet_attr_id]['unit'];
    									}
    									$s_unit[]=trim($jet_attr_id);
    									if($unit!=""){
    										$s_unit[]=trim($unit);
    									}
    									$pro_attr[trim($chd_arr)]=$s_unit;
    								}elseif(is_array($chd_arr))
    								{
    									$options[trim($val_key)][$jet_attr_id]=trim($chd_arr['value']);
    									$new_options[trim($val_key)][trim($attr_id)]=trim($chd_arr['value']);
    								}
    							}
    						}
    					}
    					if($flag){
    						continue;
    					}
    				}
    			}
    			$jet_attr=$options;
    			$product_id='';
    			$product_id=trim($id);
    			if(is_array($jet_attr) && count($jet_attr)>0)
    			{
    				$opt_count=0;
    				foreach($jet_attr as $opt_key=>$option_value)
    				{
    					$option_id="";
    					$option_id=trim($opt_key);
    					$options_save="";
    	
    					$options_save=json_encode($option_value,JSON_UNESCAPED_UNICODE);//json_encode($option_value);
    	
    					$opt_upc = $opt_asin = $opt_mpn = $opt_sku = $opt_price = $opt_qty = "";
    					if(is_array($other_vari_opt) && count($other_vari_opt)>0)
    					{
    						$opt_upc=$other_vari_opt[$option_id]['option_unique_id'];
    						$opt_asin=$other_vari_opt[$option_id]['asin'];
    						$opt_mpn=$other_vari_opt[$option_id]['mpn'];
                            $opt_sku=$other_vari_opt[$option_id]['optionsku'];
    						$UploadOnJet=$other_vari_opt[$option_id]['upload_on_jet'];
    						$opt_price= $other_vari_opt[$option_id]['option_update_price'];
    						$opt_qty= $other_vari_opt[$option_id]['option_qty'];
    					}
    					$sql="";
    					$new_variant_option_1=$new_variant_option_2=$new_variant_option_3="";
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
                                            new_variant_option_1='".addslashes($new_variant_option_1)."',
                                            new_variant_option_2='".addslashes($new_variant_option_2)."',
                                            new_variant_option_3='".addslashes($new_variant_option_3)."',
                                            jet_option_attributes='".addslashes($options_save)."',
                                            option_unique_id='".trim($opt_upc)."',
                                            update_option_price='".(float)trim($opt_price)."',
                                            option_qty='".(int)trim($opt_qty)."',
                                            option_mpn='".trim($opt_mpn)."',
                                            asin='".trim($opt_asin)."',
                                            upload_on_jet='".trim($UploadOnJet)."'
                                            where option_id='".$option_id."'";
    					Data::sqlRecords($sql,null,'update');
    					if($opt_sku==$product_sku)
    					{
    						$model->upc=trim($opt_upc);
    						$model->ASIN=trim($opt_asin);
    						$model->mpn=trim($opt_mpn);
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
    						$model2 = $opt_asin = $opt_mpn = $option_id = $opt_price = $opt_qty ="";
    						$option_id=trim($opt_id);
    						$opt_upc=$other_vari_opt[$option_id]['option_unique_id'];
    						$opt_asin=$other_vari_opt[$option_id]['asin'];
    						$opt_mpn=$other_vari_opt[$option_id]['mpn'];
    						$opt_price= $other_vari_opt[$option_id]['option_update_price'];
    						$opt_qty= $other_vari_opt[$option_id]['option_qty'];
    						if($opt_sku==$product_sku)
    						{
    							 
    							$model->upc=trim($opt_upc);
    							$model->ASIN=trim($opt_asin);
    							$model->mpn=trim($opt_mpn);
    							$model->vendor=trim($product_vendor);
    						}
    						$sql="";
    						$model2 ="";
    						$model2 = Data::sqlRecords("SELECT `option_id` from `jet_product_variants` WHERE option_id='".$option_id."'","one","select");
    						if($model2 !=="")
    						{
    							$sql="";
    							$sql="UPDATE `jet_product_variants` SET
                                                    jet_option_attributes='',
                                                    option_unique_id='".trim($opt_upc)."',
                                                    asin='".trim($opt_asin)."'
                                                    option_mpn='".trim($opt_mpn)."',
                                                    update_option_price='".(float)trim($opt_price)."',
                                                    option_qty='".(int)trim($opt_qty)."',
                                                    where option_id='".$option_id."'";
    							Data::sqlRecords($sql,null,'update');
    						}
    						$opt_count++;
    					}
    	
    				}
    			}
    			unset($model2,$sql,$options_save);
    			if(count($pro_attr)==0)
    				$model->jet_attributes='';
    			else
    				$model->jet_attributes=json_encode($pro_attr);
    			$model->save(false);
    			unset($jet_attributes,$other_vari_opt,$attributes_of_jet);
    		}
    		else
    		{
    			$updatePrice = isset($_POST['JetProduct']['update_price'])?$_POST['JetProduct']['update_price']:0.00;
    			$updateQty = isset($_POST['JetProduct']['qty'])?$_POST['JetProduct']['qty']:"";
    	
    			$sql = "UPDATE `jet_product_details` SET `update_price` = '".(float)$updatePrice."' WHERE `jet_product_details`.`product_id` = ".$product_id;
    			Data::sqlRecords($sql,null,'update');
    			$upc_success_flag = $asin_success_flag = $mpn_success_flag = $chek_flag = $invalid_upc = $invalid_asin = $invalid_mpn = false;
    			$er_msg = $type = "";
    			if(strlen($upc)>0){
    				$upc_success_flag=Jetproductinfo::checkUpcSimple($upc,$product_id,$merchant_id);
    			}
    			if(strlen($product_asin)>0){
    				$asin_success_flag=Jetproductinfo::checkAsinSimple($product_asin,$product_id,$merchant_id);
    			}
    			if(strlen($product_mpn)>0){
    				$mpn_success_flag=Jetproductinfo::checkMpnSimple($product_mpn,$product_id,$merchant_id);
    			}
    	
    			if($upc=="" || (strlen($upc)<10 || strlen($upc)>14) || $upc_success_flag)
    			{
    				$invalid_upc=true;
    			}
    	
    			if($product_asin=="" || (strlen($product_asin)>0 && strlen($product_asin)!=10) || (strlen($product_asin)==10 && !ctype_alnum ($product_asin)) || (strlen($product_asin)==10 && ctype_alnum ($product_asin) && $asin_success_flag))
    			{
    				$invalid_asin=true;
    			}
    			if($product_mpn=="" || strlen($product_mpn)>50 || (strlen($product_mpn)<=50 && $mpn_success_flag)){
    				$invalid_mpn=true;
    			}
    	
    			if($invalid_upc && $invalid_asin && $invalid_mpn){
    				$chek_flag=true;
    				$er_msg.="Invalid/Missing Barcode or ASIN or MPN, please fill unique UPC or ASIN or MPN"."<br>";
    			}
    			if($chek_flag){
    				$return_status['error']=$er_msg;
    				return json_encode($return_status);
    			}
    			if(Yii::$app->request->post('jet_attributes1')){
    				$jet_attributes1=Yii::$app->request->post('jet_attributes1');
    			}
    			$jet_attr = [];
    			if(isset($jet_attributes1))
    			{
    				foreach($jet_attributes1 as $key=>$value)
    				{
    					if(count($value)==1 && $value[0]!=''){
    						$jet_attr[$key]=array(0=>$value[0]);
    					}elseif(count($value)==2 && $value[0]!='' && $value[1]!=''){
    						$jet_attr[$key]=array(0=>$value[0],1=>$value[1]);
    					}
    				}
    			}
    			if(count($jet_attr)==0)
    				$model->jet_attributes='';
    				else
    					$model->jet_attributes=json_encode($jet_attr);
    	
    					$model->save(false);
    					unset($jet_attr);
    		}
    		$return_status['success']="Product information has been saved successfully..";
    		return json_encode($return_status);
    	}    	
    }
    
    public function actionSyncshopifyproduct()
    {
        $session = Yii::$app->session;
        
        $merchant_id = MERCHANT_ID;        
        $countProducts = $pages = 0;     
        $customData = JetProductInfo::getConfigSettings($merchant_id);
        $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";
        if($import_status=="published")
            $countProducts=$this->sc->call('GET', '/admin/products/count.json',array('published_status'=>'published')); 
        else
            $countProducts=$this->sc->call('GET', '/admin/products/count.json'); 
        if ($countProducts['errors']) {
            Yii::$app->session->setFlash('error', " Unavailable Shop ! ");
            return $this->redirect(['index']);          
        }
        $pages=ceil($countProducts/200);
        
        if(!is_object($session)){
            Yii::$app->session->setFlash('error', "Can't initialize Session.Product(s) Sync cancelled.");
            return $this->redirect(['index']);
        }
                
        $configSetting = Jetproductinfo::getConfigSettings($merchant_id);
        $session->set('product_page',$pages);
        $session->set('configSetting',$configSetting);
        
        return $this->render('syncprod', [
                'totalcount' => $countProducts,
                'pages'=>$pages
        ]);
    }
    public function actionShopifyproductsync()
    {
        $session = Yii::$app->session;
        $index=Yii::$app->request->post('index');
        $countUpload=Yii::$app->request->post('count');
        $merchant_id=MERCHANT_ID;
        $returnArr = $products = [];
        $jProduct=0;
        try
        {
            $pages=0;
            $pages=$session->get('product_page');
            $configSetting=$session->get('configSetting');
            $fullfillmentnodeid= FULLFILMENT_NODE_ID;
                        
            $dir = Yii::getAlias('@webroot').'/var/jet/product/inventorysync/'.$merchant_id.'/'.date('d-m-Y');
            if(!file_exists($dir))
            {
                mkdir($dir,0775, true);
            }
            $base_path=$dir.'/'.time().'.log';
            $file = fopen($base_path,"a+");
                        
            // Get all products
            $limit=250;
            if (($merchant_id==397)||($merchant_id==7)) {
                $limit=200;
            }
            $customData = JetProductInfo::getConfigSettings($merchant_id);
            $import_status = (isset($customData['import_status']) && $customData['import_status'])?$customData['import_status']:"";
            if($import_status=="published")
                $importParam['published_status'] = $import_status;
            $importParam['limit']=$limit;
            $importParam['page']=$index;
            //array('published_status'=>'published','limit'=>$limit,'page'=>$index)
            $products = $this->sc->call('GET', '/admin/products.json',$importParam); 
            
            if($products && is_array($products))
            {
                foreach ($products as $value)
                {
                    $jProduct++;
                    $weight = $product_qty = 0;
                    $unit = $product_sku = $response = "";
                    $product_id=$value['id'];                    
                    $product_price=$value['variants'][0]['price'];
                    $barcode=$value['variants'][0]['barcode'];
                    $product_sku=$value['variants'][0]['sku'];
                    if($product_sku=="")
                    {
                        continue;
                    }
                    $product_qty=$value['variants'][0]['inventory_quantity'];
                    $variant_id=$value['variants'][0]['id'];
                    $variants=$value['variants'];

                    $images = $imagArr = [] ;
                    $images=$value['images'];
                    if(is_array($images) && count($images)>0)
                    {
                        foreach ($images as $valImg)
                        {
                            $imagArr[]=$valImg['src'];
                        }
                    }   
                    $product_images=implode(',',$imagArr);

                    if(count($variants)>1 && is_array($variants))
                    {
                        foreach($variants as $value1)
                        {
                            $result = $optionmodel = $model_prod = array();
                            fwrite($file,PHP_EOL." VARIANT PRODUCTS SYNC ".PHP_EOL);
                            $option_id=$value1['id'];
                            $option_sku=$value1['sku'];
                            $option_price=$value1['price'];
                            $option_qty=$value1['inventory_quantity'];
                            $option_barcode=$value1['barcode'];
                            $option_image_id=$value1['image_id'];
                            $option_image_url='';
                            foreach ($images as $value2) 
                            {
                                if($value2['id']== $option_image_id){
                                    $option_image_url=$value2['src'];
                                }
                            }

                            $result = Data::sqlRecords("SELECT `option_id`,`product_id`,`option_sku`  FROM `jet_product_variants` WHERE option_id='".$option_id."' ","one","select");
                            if(!empty($result))
                            {
                                if(!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) 
                                { 
                                    $sql="UPDATE `jet_product_variants` SET `option_qty`='".$option_qty."',`option_price`='".$option_price."',`option_unique_id`='".addslashes($option_barcode)."',`option_image`='".addslashes($option_image_url)."' WHERE `merchant_id`='".$merchant_id."' AND `option_id`='".$result['option_id']."'";
                                }else{
                                    $sql="UPDATE `jet_product_variants` SET `option_qty`='".$option_qty."',`option_unique_id`='".addslashes($option_barcode)."',`option_image`='".addslashes($option_image_url)."' WHERE `merchant_id`='".$merchant_id."' AND `option_id`='".$result['option_id']."'";
                                }   
                                Data::sqlRecords($sql,null,"update");
                                
                                $get_sql="SELECT `id`,`sku` FROM `jet_product`  WHERE `merchant_id`='".$merchant_id."' AND `id`='".$result['product_id']."' AND `status`!='Not Uploaded' ";
                                $model_prod = Data::sqlRecords($get_sql,"one","select");
                                if (!empty($model_prod))
                                {                                    
                                    $response = Jetproductinfo::updateQtyOnJet($result['option_sku'],$option_qty,$this->jetHelper,$fullfillmentnodeid,$merchant_id);  
                                    fwrite($file,PHP_EOL." UPDATEING QYANTITY ON JET FOR VARIANT SKU => ".$result['option_sku'].PHP_EOL."RESPONSE FROM JET => ".$response.PHP_EOL);
                                    if(!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) 
                                    {
                                        fwrite($file,PHP_EOL." UPDATE PRICE ON JET FOR VARIANT SKU => ".$result['option_sku'].PHP_EOL);
                                        $price = Jetproductinfo::getPriceToBeUpdatedOnJet($option_id, $merchant_id, $option_price, $configSetting,"variants");
                                        Jetproductinfo::updatePriceOnJet($result['option_sku'],$price,$this->jetHelper,$fullfillmentnodeid,$merchant_id);
                                    }                               
                                }
                                unset($sql);unset($model);unset($get_sql);unset($model_prod);
                            }
                        }                        
                    }

                    $result=array();
                    $result = Data::sqlRecords("SELECT `id`,`sku` FROM `jet_product` WHERE id='".$product_id."'","one","select");
                    if(!empty($result))
                    {            
                        if(!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) 
                        {
                            $sql="UPDATE `jet_product` SET `qty`='".$product_qty."',price='".$product_price."',`upc`='".addslashes($barcode)."',`image`='".addslashes($product_images)."' WHERE `merchant_id`='".$merchant_id."' AND `id`='".$product_id."'";                             
                        }else{
                            $sql="UPDATE `jet_product` SET `qty`='".$product_qty."',`upc`='".addslashes($barcode)."' ,`image`='".addslashes($product_images)."'WHERE `merchant_id`='".$merchant_id."' AND `id`='".$product_id."'";
                        }
                        Data::sqlRecords($sql,null,"update");
                        
                        $get_sql="SELECT `id`,`sku` FROM `jet_product`  WHERE `merchant_id`='".$merchant_id."' AND `id`='".$result['id']."' AND `status`!='Not Uploaded' ";
                        $model_prod = Data::sqlRecords($get_sql,"one","select");
                        if (!empty($model_prod))
                        {        
                            fwrite($file,PHP_EOL." UPDATEING QYANTITY ON JET FOR SIMPLE SKU => ".$model_prod['sku'].PHP_EOL);                       
                            $response = Jetproductinfo::updateQtyOnJet($model_prod['sku'],$product_qty,$this->jetHelper,$fullfillmentnodeid,$merchant_id);
                            fwrite($file,PHP_EOL." UPDATEING QYANTITY ON JET FOR SIMPLE SKU => ".$model_prod['sku'].PHP_EOL."RESPONSE FROM JET => ".$response.PHP_EOL);
                            if(!isset($configSetting['fixed_price']) || (isset($configSetting['fixed_price']) && $configSetting['fixed_price']=='no')) 
                            {
                                $price = Jetproductinfo::getPriceToBeUpdatedOnJet($product_id, $merchant_id, $product_price, $configSetting, "simple");
                                Jetproductinfo::updatePriceOnJet($model_prod['sku'],$price,$this->jetHelper,$fullfillmentnodeid,$merchant_id);
                            }                                                                   
                        }                                                           
                        unset($sql);unset($model);unset($get_sql);unset($model_prod);
                    }                    
                }
            }
        }        
        catch (Exception $e)
        {
            fwrite($file,PHP_EOL." Exception => ".json_encode($e->getMessage()).PHP_EOL);                       
            return $returnArr['error']=$e->getMessage();
        }
        $returnArr['success']['count']=$jProduct;
        fclose($file);        
        return json_encode($returnArr);            
    }

	public static function priceChange($price,$priceType,$changePrice)
    {
        $updatePrice=0;
        if($priceType=="percentageAmount"){
            $updatePrice = (float)($price+($changePrice/100)*($price));
            $updatePrice = number_format($updatePrice, 2, '.', '');
        }
        elseif($priceType=="fixedAmount")
        {
            $updatePrice=(float)($price + $changePrice);
        }
        return $updatePrice;
    }
	public function actionChangevariantimage()
    {
		$this->layout="main2";
		$product_id='';
		$product_id=Yii::$app->request->post('product_id');
	
		$collection=array();
		$sql="SELECT product_id,option_id, option_image,option_sku from jet_product_variants  where product_id=".$product_id;
		$collection=Data::sqlRecords($sql,'all','select');
		$html=$this->render('changevariantimage',array('collection'=>$collection),true);
		return $html;	
	}
	public function actionSavevariantimage()
    {
        $merchant_id=MERCHANT_ID;
        
        if (Yii::$app->request->isPost) 
        {
            $files=[];
            $id=Yii::$app->request->post('id');
            $images=[];
            $images=Yii::$app->request->post('image');

            $model_variant="";$model_simple='';
            $model_variant=JetProductVariants::find()->select('option_id,option_image')->where(["merchant_id"=>$merchant_id,"option_id"=>$id])->one();
            $model_simple=JetProduct::find()->select('variant_id,image')->where(["merchant_id"=>$merchant_id,"variant_id"=>$id])->one();
    
            $arrImage=[];
            $finalimges=[];
            $imageNameArr=[];
            $basPath=Yii::getAlias('@webroot').'/upload/jet/images/'.$merchant_id.'/'.$id;
            if(!file_exists($basPath)){
                mkdir($basPath,0775, true);
            }
            
            if(is_array($images) && count($images)>0)
            {
                foreach ($images as $key => $value)
                {
                    $imageNameArr[$value]='upload/images/'.$merchant_id.'/'.$id.'/'.$key;
                }
                $imageModel='';
                $imageModel=UploadedFile::getInstancesByName('files');
                if(is_array($imageModel) && count($imageModel))
                {
                    foreach ($imageModel as $key => $value)
                    {
                        $url=[];
                        $url=pathinfo($value->name);
                        $value->saveAs($basPath.'/'.$url['basename']);
                    }
                }
                ksort($imageNameArr);
                $finalimges=array_values($imageNameArr);
                $model_variant->option_image=implode(",",$finalimges);
                $model_variant->save(false);
                if ($model_simple !=''){
                    $updateImg="UPDATE  `jet_product` SET `image`='".implode(",",$finalimges)."'  where variant_id=".$id;
                    $collection=Yii::$app->getDb()->createCommand($updateImg)->execute();
                }
                return "image updated successfully";    
            }
        }    
    }
    public function actionMassupdateprice()
    {
        $api_url="https://merchant-api.jet.com/api";
        $connection=Yii::$app->getDb();
        $data= Yii::$app->request->post();
        $api_user=$data['user'];
        $api_password=$data['pwd'];
        $fullfilment_node_id=$data['fullfilment_node_id'];
        $merchant_id=$data['merchant'];
        $this->jetHelper = new Jetapimerchant($api_url,$api_user,$api_password);
        $response = $this->jetHelper->CGetRequest('/portal/merchantskus/export',$merchant_id);
        $prodData=explode(',', $response);
        $path=Yii::getAlias('@webroot').'/var/jet/jetProductStatusCsv/';
        if(!file_exists($path))
        {
            mkdir($path,0775, true);
        }
        $base_path=$path.'/'.$merchant_id.'.csv';
        $file = fopen($base_path,"w");
        fwrite($file,$response);
        fclose($file);
        $encode = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content = $encode . file_get_contents($base_path);
        return "completed";
    }
    public function actionAjaxpriceupdate()
    {
        $connection=Yii::$app->getDb();
        $query='select config.merchant_id,fullfilment_node_id,api_user,api_password from jet_configuration config inner join jet_shop_details ext on ext.merchant_id=config.merchant_id where ext.purchase_status="Purchased"';
        $configurable=$connection->createCommand($query)->queryAll();
        $merchantids=is_array($configurable)?json_encode($configurable):json_encode(array());
        $urlpriceupdate= \yii\helpers\Url::toRoute(['jetproduct/massupdateprice']);
        $html="<script>
        var baseUrl = '".$urlpriceupdate."';
        var _csrf= '".Yii::$app->request->getCsrfToken()."';
        var merchants = ".$merchantids.";
        var count = 0;
        //console.log(merchants);
        processRequest();
        function processRequest() {
          if(typeof merchants[count]=='undefined')
         {
            alert('Done');
            return;
         }
          var xhttp = new XMLHttpRequest();
          var merchant = merchants[count].merchant_id;
          var fullfilment_node_id=merchants[count].fullfilment_node_id;
          var user = merchants[count].api_user;
          var pwd = merchants[count].api_password;
          xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) 
            {
              if(this.responseText=='next'){
                //page++;
                processRequest();
              }
              else if(this.responseText=='completed')
              {
                count++;
                //page = 1;
                processRequest();
              }
            }
            else
            {
                //setTimeout(function(){ processRequest(); }, 3000);
            }
          };
          var params = 'merchant='+merchant+'&user='+user+'&pwd='+pwd+'&fullfilment_node_id='+fullfilment_node_id+'&_csrf='+_csrf;
          url = baseUrl;
          xhttp.open('POST',url, false);
          xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhttp.send(params);
        }
        </script>";
        echo $html;
        die;
    }
    public function actionSaveprice()
    {
        $collection = Data::sqlRecords("select jet_product.id,price,merchant_id,type,username,auth_key from `jet_product` inner join `user` on jet_product.merchant_id = user.id where price=0 ",'all','select');
        if(is_array($collection) && count($collection)>0)
        {
            foreach ($collection as $key => $value) 
            {                
                $merchant_id =$value['merchant_id'];
                $shopname=$value['username'];
                $token=$value['auth_key'];
                //echo $merchant_id;die;
                $Products_check=$this->sc->call('GET', '/admin/products/'.$value['id'].'.json');
                //print_r($Products_check);
                if($merchant_id!=93 && $merchant_id!=115 && $merchant_id!=188 && $merchant_id!=258 && $merchant_id!=269 && $merchant_id!=285 && $merchant_id!=288 && $merchant_id!=308 )
                {
                    $count++;
                    echo 'id: '.$value['id'].'---'.$Products_check['variants'][0]['price']."<br>";
                    //$updateQuery="UPDATE `jet_product` SET `price`='".$Products_check['variants'][0]['price']."'  WHERE merchant_id='".$merchant_id."' and id='".$value['id']."'";
                    //$updated = Data::sqlRecords($updateQuery,null,'update');
                }
            }
            echo $count;
            die;
        }
    }
   
    public function actionViewshipexception()
    {
        $this->layout="main2";
        $merchant_id = MERCHANT_ID;
        $id=trim(Yii::$app->request->post('id'));        
        $sku=trim(Yii::$app->request->post('sku'));
        $model=array();
        $model=Data::sqlRecords('SELECT id,sku,merchant_id,shipping_method,override_type,shipping_charge_amount,shipping_exception_type,service_level from `jet_shipping_exception` where merchant_id="{$merchant_id }"  AND `id`="'.$id.'"','one','select');
        $html=$this->render('shipexception_tab.php',array('model'=>$model,'sku'=>$sku, 'id'=>$id),true);
        unset($model);
        return $html;  
    }
    public function actionShipexception($shipping_array="")
    {
        $message="";
        //$model = new JetConfiguration();
        $merchant_id=MERCHANT_ID;
        
        $data=array();
        if(is_array($shipping_array) && count($shipping_array)>0)
        {
            $data=$shipping_array;
        }
        else
        {
            $data=Yii::$app->request->queryParams;
        }
        if($data)
        {
            $shipping=array();
            $shipping_carr=[];
            $status=false;
            if(isset($data['ship_level']) && $data['ship_level']){
                $shipping_carr=['service_level'=>$data['ship_level'],'shipping_exception_type'=>$data['ship_exception']];
            }
            else
            {
                $shipping_carr=['shipping_method'=>$data['ship_method'],'shipping_exception_type'=>$data['ship_exception']];
            }
            if($data['ship_exception']=="restricted")
            {
                $shipping['fulfillment_nodes'][]=array(
                    'fulfillment_node_id'=>FULLFILMENT_NODE_ID,
                    'shipping_exceptions'=>array(
                           $shipping_carr, 
                        )
                    );
                unset($shipping_carr);
            }
            else
            {
                //send either ship level or shipping method
                if(isset($data['ship_level']) && $data['ship_level'])
                    $shipping_carr['service_level']=$data['ship_level'];
                else
                    $shipping_carr['shipping_method']=$data['ship_method'];
                
                //send override type when shipping_charge_amount>0
                if((isset($data['shipping_charge_amount']) && is_numeric(trim($data['shipping_charge_amount'])) && trim($data['shipping_charge_amount'])>0))
                {
                    //echo "hello";
                    $shipping_carr['override_type']=$data['override_type'];
                    $shipping_carr['shipping_charge_amount']=(float)$data['shipping_charge_amount'];
                }else{
                    $shipping_carr['override_type']="Override charge";
                    $shipping_carr['shipping_charge_amount']=(float)0;
                }
                $shipping_carr['shipping_exception_type']=$data['ship_exception'];
                $shipping['fulfillment_nodes'][]=array(
                    'fulfillment_node_id'=>FULLFILMENT_NODE_ID,
                    'shipping_exceptions'=>array(
                            $shipping_carr,
                        )   
                    );
            }
            $response="";$responsearray=array();
            if(is_array($shipping) && count($shipping)>0)
            {
                $response=$this->jetHelper->CPutRequest("/merchant-skus/".rawurlencode($data['sku'])."/shippingexception",json_encode($shipping),$status);
                $responseData=json_decode($response,true);
                if(!isset($responseData['errors']) && $status==202)
                {
                    $model="";
                    $model=JetShippingException::find()->where(['merchant_id'=>$merchant_id,'sku'=>$data['sku']])->one();
                    if($model){
                        $model->shipping_exception_type=$data['ship_exception'];
                        $model->shipping_method=$data['ship_method'];
                        $model->override_type=$data['override_type'];
                        $model->service_level=$data['ship_level'];
                        $model->shipping_charge_amount=(float)$data['shipping_charge_amount'];
                        $model->save(false);
                    }else{
                        $modelnew="";
                        $modelnew = new JetShippingException();
                        //$modelnew->product_id=$data['product_id'];
                        $modelnew->sku=$data['sku'];
                        $modelnew->merchant_id=$merchant_id;
                        $modelnew->shipping_exception_type=$data['ship_exception'];
                        $modelnew->shipping_method=$data['ship_method'];
                        $modelnew->override_type=$data['override_type'];
                        $modelnew->service_level=$data['ship_level'];
                        $modelnew->shipping_charge_amount=(float)$data['shipping_charge_amount'];
                        $modelnew->save(false);
                    }
                }
            }
            return $response;
        }
    }

   
    public function actionGetskudetails()
    {
        $this->layout="main2";
        $sku = trim(Yii::$app->request->post('sku'));
        $rawDetails = $this->jetHelper->CGetRequest('/merchant-skus/'.$sku.'/salesdata',MERCHANT_ID);
        $rawDetails = json_decode($rawDetails,true);
        $html = $this->render(
            'skudetails',
            [                    
                'details'=>$rawDetails,
            ],
            true
        );                       
        return $html;
    }   

    
    public function actionUpdatejetprice()
    {
        $product = [];
        $product = Yii::$app->request->post();
       
        $merchant_id = MERCHANT_ID;
        if (!empty($product)) 
        {
            Jetproductinfo::updatePriceOnJet($product['sku'],$product['price'],$this->jetHelper,FULLFILMENT_NODE_ID,MERCHANT_ID);
            self::updatePriceInv($product,'price');          
        } 
        $returnArr = ['success' => true,'message'=>"Success"];                         
        return json_encode($returnArr);
    }

    public function actionUpdatejetinventory()
    {
        $product = $response = $response22 = $updateInventory = [];
        $product = Yii::$app->request->post();
        $merchant_id = MERCHANT_ID;
        if (!empty($product)) 
        {
            $response = Jetproductinfo::updateQtyOnJet($product['sku'],$product['qty'],$this->jetHelper,FULLFILMENT_NODE_ID,MERCHANT_ID);
            $updateInventory['variant']=array(
                                "id" => $product['option_id'],
                                "inventory_quantity"=> $product['qty'],
                        );
            $response22 = $this->sc->call('PUT', '/admin/variants/'.$product['option_id'].'.json',$updateInventory);  
            self::updatePriceInv($product,'qty');          
        }
        
        $res = json_decode($response,true);  
        $returnArr = ['success' => true,'message'=>"Success"];   
        /*if ($res['status_code']==202) {
            $returnArr = ['success' => true,'message'=>"Success"];        
        }else{
            $returnArr = ['error' => true,'message'=>"Either product not uploaded on jet or product api is not working"];
        }  */          
        return json_encode($returnArr);
    }
    public static function updatePriceInv($product=[],$updateType="")
    {
        $merchant_id = MERCHANT_ID;
        if ($updateType=='qty')
        {
            $sql = "UPDATE `jet_product` SET `qty` = '{$product['qty']}' WHERE merchant_id='{$merchant_id}' AND `id` ='{$product['id']}'  ";
            Data::sqlRecords($sql,null,'update');
            if ($product['type']=="variants")
            {
                $sql = "UPDATE `jet_product_variants` SET `option_qty` = '{$product['qty']}' WHERE merchant_id='{$merchant_id}' AND option_id='{$product['option_id']}' AND `product_id` ='{$product['id']}'  ";
                Data::sqlRecords($sql,null,'update');
            }
        }elseif ($updateType=='price')
        {
            $isExist = [];
            $queryType = $sql = "";
            $isExist = Data::sqlRecords("SELECT `id` FROM `jet_product_details` WHERE `merchant_id`='{$merchant_id}' AND `product_id`='{$product['id']}' ","one","select");
            if (!empty($isExist))
            {
                $sql = "UPDATE `jet_product_details` SET `update_price` = '{$product['price']}' WHERE merchant_id='{$merchant_id}' AND `product_id` ='{$product['id']}' ";

                $queryType = "update";
            }
            else
            {
                $isMainExist = [];
                $isMainExist = Data::sqlRecords("SELECT `id` FROM `jet_product` WHERE `merchant_id`='{$merchant_id}' AND `id`='{$product['id']}' ","one","select");
                if (!empty($isMainExist))
                {
                    $sql = "INSERT INTO `jet_product_details` (`product_id`, `merchant_id`,`update_price`) VALUES ( '{$product['id']}', '{$merchant_id}', '{$product['price']}')";
                    $queryType = "insert";
                }
           }


            Data::sqlRecords($sql,null,$queryType);

            if ($product['type']=="variants")
            {
                $sql = "UPDATE `jet_product_variants` SET `update_option_price` = '{$product['price']}' WHERE merchant_id='{$merchant_id}' AND `option_id` = '{$product['option_id']}' AND `product_id` ='{$product['id']}'  ";
                Data::sqlRecords($sql,null,'update');
            }
        }
    }
}