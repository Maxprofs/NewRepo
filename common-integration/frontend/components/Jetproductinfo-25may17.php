<?php 
namespace frontend\components;
use app\models\JetProductVariants;
use Yii;
use yii\base\Component;
//use app\models\ProductVariantUpload;

//use common\models\User;
//use frontend\components\Shopifyinfo;
//use app\models\JetConfiguration;
//use app\models\JetMerchantProducts;
//use frontend\models\JetConfig;
use app\models\JetCategoryMap;
//use frontend\components\Jetapi;
use frontend\components\Jetappdetails;

class Jetproductinfo extends component
{
	public static function createoption($product,$carray,$jetHelper,$fullfillmentnodeid,$merchant_id,$collection)
	{
		
		
		if(!isset($connection)){
			$connection=Yii::$app->getDb();
		}
		
		$options=array();
		//$options=JetProductVariants::find()->where(['merchant_id'=>$merchant_id,'product_id'=>$product->id])->all();
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id`,`option_title`,`option_sku`,`jet_option_attributes`,`option_image`,`option_qty`,`option_weight`,`option_price`,`option_unique_id`,`barcode_type`,`asin`,`option_mpn`,`jet_option_attributes`,`variant_option1`,`variant_option2`,`variant_option3`,`new_variant_option_1`,`new_variant_option_2`,`new_variant_option_3` FROM `jet_product_variants` WHERE product_id='".$product->id."'");
        $options = $queryObj->queryAll();
		$error="";
		$responseOptions=array();
		$count=0;
		$resultDes="";
		$isParent=false;
        
        if(is_array($options) && count($options)>0)
        {
        	$eligibleVariants=array();
        	$eligibleVariants=self::getEligibleVariants($product,$merchant_id,$options);
        	
	        foreach($options as $val)
			{
			    $val=(object)$val;
			    
				if(!array_key_exists(trim($val->option_id),$eligibleVariants)){
					$error.= trim($val->option_sku).": Duplicate Jet Attribute Options,";
					$count++;
					//$error['duplicate'][]= trim($val->option_sku);
					continue;
				}  
				//echo "==CodeAfter";
				$SKU_Array= array();
				$unique=array();
				$Attribute_arr = array();
				$Attribute_array = array();
				$_uniquedata=array();
				$price=array();
				$node=array();
				$node1=array();
				$inventory=array();
				$asin='';
				$upc='';
				$mpn='';
				$option_weight=0;
				$sku=$val->option_sku;
				//echo "==skuIn==";echo "<br>";
				if($sku=="")
					continue;
				$data=array('is_archived'=>true);
				
				$upc = $val->option_unique_id;
				$asin = $val->asin;
				$option_weight =(float)$val->option_weight; 
				$mpn = $val->option_mpn;
				$brand=$product->vendor;
				$is_variation =false;
				$_uniquedata =array();
				$type="";
				$type=$val->barcode_type;
				if($type=="")
					$type=self::checkUpcType($upc);
				/*
				$_uniquedata=array("type"=>$type,"value"=>$upc); */
				if($product->sku==$sku)
				{
					$isParent=true;
					$name=$product->title;
				}
				else
				{
					$name=$product->title.'-'.$val->option_title;
				}
				$SKU_Array['product_title']=$name;
				$nodeid = (int)$product->fulfillment_node;
				$attribute=$product->jet_attributes;
				$attribute_opt=[];
				$attribute_opt=json_decode($val->jet_option_attributes,true);
				$Attribute_arr=json_decode($attribute,true);
				$SKU_Array['jet_browse_node_id']=$nodeid;

				if($carray[$sku]['upc_var'])
				{
					$_uniquedata=array("type"=>$type,"value"=>$upc);
					$unique['standard_product_code']=$_uniquedata['value'];
					$unique['standard_product_code_type']=$_uniquedata['type'];
					$SKU_Array['standard_product_codes'][]=$unique;
				}
				if($asin!=null && $carray[$sku]['asin_var'])
				{
					$SKU_Array['ASIN']=$asin;
				}
				if($mpn!=null && $carray[$sku]['mpn_var']){
				    $SKU_Array['mfr_part_number']=$mpn;
				}
				$SKU_Array['manufacturer']=$brand;
	    		//$SKU_Array["country_of_origin"]="U.S.A.";
				if(is_float($option_weight) && $option_weight>=0.01){
					$SKU_Array['shipping_weight_pounds']=(float)$option_weight;
				}
				$SKU_Array['multipack_quantity']= 1;
				$SKU_Array['brand']=$brand;
				$description="";
				$description=$product->description;
				//trim description string more than 2000
				if(strlen($description)>2000)
					$description=$jetHelper->trimString($description, 2000);
				$SKU_Array['product_description']=$description;
				//send images
				$parentmainImage="";$kmain=0;$images=array();
				$images=explode(',',$product->image);

				if($product->image!="" && count($images)>0)
				{
					foreach($images as $key=>$value){
						if(self::checkRemoteFile($value)==true){
							$kmain=$key;
							$parentmainImage=$value;
							break;
						}
					}
				}
				if($product->sku==$sku)
	    		{
	    			$SKU_Array['main_image_url']=$parentmainImage;
		    		if(count($images)>1)
		    		{
		    			$i=1;
		    			foreach($images as $key=>$value)
		    			{
		    				if($key==$kmain)
		    					continue;
		    				if($i>8)
		    					break;
		    				if($value!='' && self::checkRemoteFile($value)==true){
		    					$SKU_Array['alternate_images'][]= array(
		    							'image_slot_id'=>(int)$i,
		    							'image_url'=> $value
			    					);
		    					$i++;
		    				}
		    			}
		    		}
		    	}
	    		else{
	    			if($val->option_image!="" && self::checkRemoteFile($val->option_image)==true)
	    				$SKU_Array['main_image_url']=$val->option_image;
	    			else
	    				$SKU_Array['main_image_url']=$parentmainImage;
	    		}
	    	    foreach ($Attribute_arr as $key=>$value)
				{
				    $attr_val="";
				    if($attribute_opt && array_key_exists($value[0],$attribute_opt)){
				        $attr_val=$attribute_opt[$value[0]];
				    }else{
				        continue;
				    }
				    if(!isset($responseOptions['variation_refinements']) || !in_array($value[0],$responseOptions['variation_refinements']))
					{	
						$responseOptions['variation_refinements'][]=(int)$value[0];
					}
					if(count($value)==1)
					{
						$Attribute_array[] = array(
								'attribute_id'=>(int)$value[0],
								'attribute_value'=>$attr_val
	    				);
	    			}
	    			elseif(count($value)==2)
	    			{
						$Attribute_array[] = array(
							'attribute_id'=>(int)$value[0],
								'attribute_value'=>$attr_val,
								'attribute_value_unit'=>$value[1]
		    			);
		    		}
		    	}
				if(!empty($SKU_Array))
				{
				    
					if(count($Attribute_array)>0)
					$SKU_Array['attributes_node_specific'] = $Attribute_array; // add attributes details
					
				
					//file log for product option upload
					if(!file_exists(\Yii::getAlias('@webroot').'/var/productUpload/variant'.date('d-m-Y'))){
					    mkdir(\Yii::getAlias('@webroot').'/var/productUpload/variant'.date('d-m-Y'),0775, true);
					}
					$filenameOrig="";
					$filenameOrig=\Yii::getAlias('@webroot').'/var/productUpload/variant'.date('d-m-Y').'/'.$merchant_id.'.txt';
					$fileOrig="";
					$fileOrig=fopen($filenameOrig,'a+');
					fwrite($fileOrig,"\n product sku: ".$sku."\n".json_encode($SKU_Array));
					fclose($fileOrig);
					//file log
					
					$response=array();
					$responsedata="";
					$responsedata = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku),json_encode($SKU_Array));
					$response=json_decode($responsedata,true);
					if(count($response)==0)
					{
						$price=array();
						$price['price']=(float)$val->option_price;
						if($price['price']==""){
							$price['price']=(float)$product->price;
						}
						$node['fulfillment_node_id']=$fullfillmentnodeid;
						$node['fulfillment_node_price']=$price['price'];
						$price['fulfillment_nodes'][]=$node;
						/* if($merchant_id==14){
						    var_dump($node);echo "<hr>";die;
						} */
						$responsePrice=array();
						$responseData="";
						$responseData = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($price));
						$responsePrice=json_decode($responseData,true);
						if(count($responsePrice)==0){
							$qty="";
							$qty= $val->option_qty;
							if(trim($qty)==""){
								$qty=trim($product->qty);
							}
							$inventory=array();
							$node1['fulfillment_node_id']=$fullfillmentnodeid;
							$node1['quantity']=(int)$qty;
							$inventory['fulfillment_nodes'][]=$node1;
							$responseInventory=array();
							$responseData="";
							$responseData = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory));
							$responseInventory=json_decode($responseData,true);
							if(isset($responseInventory['errors']) && count($responseInventory)>0){
								$count++;
								$error.= $sku.": ".json_encode($responseInventory['errors'])."\n";
								//$error['variant_inventory_upload_error']= $sku.": ".json_encode($responseInventory['errors'])."<br>";
								continue;
							}
						}
						elseif(isset($responsePrice['errors']) && count($responsePrice)>0){
							$count++;
							$error.= $sku.": ".json_encode($responsePrice['errors'])."\n";
							//$error['variant_price_upload_error']= $sku.": ".json_encode($responsePrice['errors'])."<br>";
							continue;
						}
					}
					elseif(isset($response['errors']) && count($response)>0)
					{
						$count++;
						$error.= $sku.": ".json_encode($response['errors'])."\n";
						//$error['variant_sku_upload_error']= $sku.": ".json_encode($response['errors'])."<br>";
						continue;
					}				
	    			// get sku on jet
					$responseData="";
					$responseSku=array();
	    			$responseData = $jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku));
	    			$responseSku=json_decode($responseData,true);
	    			if(count($responseSku)>0 && !isset($responseSku['errors']))
	    			{
	    				
	    				if($product->sku!=$sku)
	    					$responseOptions['children_skus'][]=$sku;
	    			}
				}
		    }
		    unset($options);
		    unset($eligibleVariants);
		    unset($unique);
		    unset($Attribute_arr);
		    unset($Attribute_array);
		    unset($_uniquedata);
			unset($price);
			unset($node);
			unset($node1);
			unset($inventory);
		    if($error!="" && $count>0)
	    	{
	    		$responseOptions['errors']=$error;
	    	}
	    	$responseOptions['relationship']='Variation';
    		
		}else{
			$responseOptions['errors']="No Variant Options Available";
		}    
    	return $responseOptions;
    }

    public static function checkBeforeDataPrepare($product="",$merchant_id="",$connection="")
    {
        $carray=array();
        $carray['success']=false;
        $carray['error']="";
        $Errors=array();
        $cflag=0;
       
        if($merchant_id && $product && trim($product->type))
        {
            if(trim($product->type)=="simple")
            {
                $upc="";
                $asin="";
                $price="";
                $qty="";
                $nodeid="";
                $upc_err=false;
                $mpn_err=false;
                $brand="";
                $sku="";
                $image="";
                $qty=trim($product->qty);
                $image=trim($product->image);
                $countImage=0;
                $imageArr=array();
                $ImageFlag=false;
                $imageArr=explode(',',$image);
                //If all images all broken
                if($image!="" && count($imageArr)>0){
                    foreach ($imageArr as $value){
                        if(self::checkRemoteFile($value)==false)
                            $countImage++;
                    }
                    if(count($imageArr)==$countImage)
                        $ImageFlag=true;
                }
                $price=trim($product->price);
                $upc = trim($product->upc);
                $asin = trim($product->ASIN);
                $mpn = trim($product->mpn);
                $brand=trim($product->vendor);
                $nodeid = $product->fulfillment_node;
                $sku=$product->sku;
                if($sku==''){
                	$Errors['sku_error']="Missing Product Sku,";
                    $Errors['sku']=$product->sku;
                    $cflag++;
                }
                if($upc=='' && $asin=='' && $mpn==''){
                	$Errors['upc_error']="Missing Barcode or ASIN or MPN, ";
                    $Errors['upc']=$product->sku;
                    $cflag++;
                }
                if($brand==''){
                	$Errors['brand_error']="Missing brand,";
                    $Errors['brand']=$product->sku;
                    $cflag++;
                }
                if($nodeid==''){
                	$Errors['node_id_error']="Missing Jet Browse Node,";
                    $Errors['node_id']=$product->sku;
                    $cflag++;
                }
                if($image=='' || $ImageFlag){
                	$Errors['image_error']="Missing or Invalid Image,";
                    $Errors['image']=$product->sku;
                    $cflag++;
                }
                if(($price<=0 || ($price && !is_numeric($price))) || trim($price)==""){
                	$Errors['price_error']="Invalid Price,";
                    $Errors['price']=$product->sku;
                    $cflag++;
                }
                if(($qty && !is_numeric($qty))||trim($qty)==""||($qty<=0 && is_numeric($qty))){
                	$Errors['qty_error']="Invalid Qauntity,";
                    $Errors['qty']=$product->sku;
                    $cflag++;
                }
                //check upc type
                $type="";
                $existUpc=false;
                $existAsin=false;
                $asinFlag=false;
                $existMpn=false;
                //check upc is unique
                
                $type=self::checkUpcType($upc);
                if($type!="")
                    $existUpc=self::checkUpcSimple($upc,$product->id,$connection);
                //check ASIN is unique
                $existAsin=self::checkAsinSimple($asin,$product->id,$connection);
                $existMpn=self::checkMpnSimple($mpn,$product->id,$connection);
                if($upc=="" || (strlen($upc)>0 && $type=="") || (strlen($upc)>0 && $existUpc)){
                	//$Errors['upc_error_info']="Duplicate or Invalid Barcode,";
                    $Errors['upc']=$product->sku;
                    $upc_err=true;
                    //$cflag++;
                }
                
                if(($upc_err && $asin=="") || ($upc_err && strlen($asin)!=10) || ($upc_err && strlen($asin)==10 && !ctype_alnum ($asin)) || ($upc_err && strlen($asin)==10 && ctype_alnum ($asin) && $existAsin)){
                	//$Errors['asin_error_info']="Duplicate or Invalid ASIN,";
                	$Errors['upc']=$product->sku;
                    //$cflag++;
                }
                if($mpn=="" || strlen($mpn)>50 || (strlen($mpn)>50 && $existMpn)){
                    //$Errors['mpn_error']="Invalid Mpn,";
                    $mpn_err=true;
                    $Errors['upc']=$product->sku;
                    //$cflag++;
                }
                if($asin=="" || strlen($asin)!=10 || (strlen($asin)==10 && !ctype_alnum ($asin)) || (strlen($asin)==10 && ctype_alnum ($asin) && $existAsin))
                {
                	$asinFlag=true;
                }
                if(!$asinFlag){
                	$carray['asin_simp']=true;
                }
                if(!$upc_err){
                	$carray['upc_simp']=true;
                }
                if(!$mpn_err){
                    $carray['mpn_simp']=true;
                }
                if($asinFlag && $upc_err && $mpn_err){
                	$Errors['asin_error_info']="Invalid/Missing Barcode or ASIN or MPN, ";
                    $cflag++;
                }
            }
            elseif(trim($product->type)=="variants")
            {
                $brand="";
                $nodeid = "";
                $par_qty=0;
                $par_price="";
                $image="";
                $countImage=0;
                $image=trim($product->image);
                $imageArr=array();
                $ImageFlag=false;
                $imageArr=explode(',',$image);
                //If all images all broken
                if($image!="" && count($imageArr)>0){
                    foreach ($imageArr as $value){
                        if(self::checkRemoteFile($value)==false)
                            $countImage++;
                    }
                    if(count($imageArr)==$countImage)
                        $ImageFlag=true;
                }
                $par_qty=trim($product->qty);
                if($par_qty=="")$par_qty=0;
                $par_price=trim($product->price);
                $c_par_price=false;
                $c_par_qty=false;
                if($par_price<=0 || (trim($par_price) && !is_numeric($par_price)) || trim($par_price)==""){
                    $c_par_price=false;
                }else{
                    $c_par_price=true;
                }
                if((trim($par_qty)<=0 || !is_numeric($par_qty))){
                    $c_par_qty=false;
                }else{
                    $c_par_qty=true;
                }
                $brand=trim($product->vendor);
                if($brand==''){
                	$Errors['brand_error']="Missing brand,";
                    $Errors['brand']=$product->sku;
                    $cflag++;
                }
                $nodeid = $product->fulfillment_node;
                if($nodeid=='')
                {
                	$Errors['node_id_error']="Missing Jet Browse Node,";
                    $Errors['node_id']=$product->sku;
                    $cflag++;
                }
                if($image=="" || $ImageFlag)
                {
                	$Errors['image_error']="Missing or Invalid Image,";
                    $Errors['image']=$product->sku;
                    $cflag++;
                }
                $options=array();
                $queryObj="";
                $queryObj = $connection->createCommand("SELECT `option_id`,`option_sku`,`option_qty`,`option_price`,`option_unique_id`,`asin`,`option_mpn` FROM `jet_product_variants` WHERE product_id='".$product->id."'");
                $options = $queryObj->queryAll();
                //$options=JetProductVariants::find()->where(['merchant_id'=>$merchant_id,'product_id'=>$product->id])->all();
                if(is_array($options) && count($options)>0)
                {
                    foreach($options as $pro)
                    {
                        $upc="";
                        $asin="";
                        $price="";
                        $qty=0;
                        $nodeid="";
                        $opt_sku="";
                        $upc_err=false;
                        $mpn_err=false;
                        $opt_sku=trim($pro['option_sku']);
                        $qty=trim($pro['option_qty']);
                        if($qty=="")$qty=0;
                        $price=trim($pro['option_price']);
                        $upc = trim($pro['option_unique_id']);
                        $asin = trim($pro['asin']);
                        $mpn = trim($pro['option_mpn']);
                        if($opt_sku==""){
                        	$Errors['sku_error_var'][]=$opt_sku;
                        	$Errors['upc']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                        	$cflag++;
                        }
                        if($upc=='' && $asin=='' && $mpn==''){
                        	//$Errors['upc_error']="Missing Variants Barcode Or ASIN";
                        	$Errors['upc_error_var'][]=$opt_sku;
                            $Errors['upc']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                            $cflag++;
                        }
                        if(trim($price) && !is_numeric($price)){
                        	//$Errors['price_error']="Invalid Variants Price";
                        	$Errors['price_error_var'][]=$opt_sku;
                            $Errors['price']=$product->sku;//$product->sku; //"variant : ".$opt_sku." of product : ".
                            $cflag++;
                        }
                        if((!$c_par_price && trim($price)=="") || (!$c_par_price && trim($price)<=0)){
                        	//$Errors['price_error']="Invalid Variants Price";
                        	$Errors['price_error_var'][]=$opt_sku;
                            $Errors['price']=$product->sku;//$product->sku;   //"variant : ".$opt_sku." of product : ".
                            $cflag++;
                        }
                        if(trim($qty)<=0 && !is_numeric($qty)){
                        	$Errors['qty_error_var'][]=$opt_sku;
                        	//$Errors['qty_error']="Invalid Variants Quantity";
                            $Errors['qty']=$product->sku;//$product->sku; //"variant : ".$opt_sku." of product : ".
                            $cflag++;
                        }
                        if(!$c_par_qty && trim($qty)<=0){
                        	$Errors['qty_error_var'][]=$opt_sku;
                        	//$Errors['price_error']="Invalid Variants Price";
                            $Errors['qty']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                            $cflag++;
                        }
                        //check upc type
                        $type="";
                        $existUpc=false;
                        $existAsin=false;
                        $asinFlag=false;
                        $existMpn=false;
                        //check upc is unique
                        $type=self::checkUpcType($upc);
                        $productasparent=0;
                        if($product->sku==$pro['option_sku']){
                            $productasparent=1;
                        }
                        if($type!="")
                            $existUpc=self::checkUpcVariants($upc,$product->id,$pro['option_id'],$productasparent,$connection);
                        //check ASIN is unique
                        $existAsin=self::checkAsinVariants($asin,$product->id,$pro['option_id'],$productasparent,$connection);
                        $existMpn=self::checkMpnVariants($mpn,$product->id,$pro['option_id'],$productasparent,$connection);
                        if($upc=="" || (strlen($upc)>0 && $type=="") || (strlen($upc)>0 && $existUpc)){
                        	//$Errors['upc_error_info']="Duplicate or Invalid Variants Barcode";
                        	//$Errors['upc_error_info_var'][]=$opt_sku;
                        	$Errors['upc']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                            $upc_err=true;
                            //$cflag++;
                        }
                        if($mpn=="" || strlen($mpn)>50 || (strlen($mpn)>50 && $existMpn)){
                        	//$Errors['mpn_error']="Invalid Variants Mpn";
                        	//$Errors['mpn_error_var'][]=$opt_sku;
                            $mpn_err=true;
                            $Errors['upc']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                            //$cflag++;
                            
                        }
                        if(($upc_err && $asin=="") || ($upc_err && strlen($asin)!=10) || ($upc_err && strlen($asin)==10 && !ctype_alnum ($asin)) || ($upc_err && strlen($asin)==10 && ctype_alnum ($asin) && $existAsin)){
                        	//$Errors['asin_error_info']="Duplicate or Invalid Variants ASIN";
                        	//$Errors['asin_error_info_var'][]=$opt_sku;
                        	$Errors['upc']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                            //$cflag++;
                            $asinFlag=true;
                        }
                        if($asin=="" || strlen($asin)!=10 || (strlen($asin)==10 && !ctype_alnum ($asin)) || (strlen($asin)==10 && ctype_alnum ($asin) && $existAsin))
		                {

		                	$asinFlag=true;
		                }
		                if(!$asinFlag){
		                	$carray[$opt_sku]['asin_var']=true;
		                }
		                if(!$upc_err){
		                	$carray[$opt_sku]['upc_var']=true;
		                }
		                if(!$mpn_err){
		                    $carray[$opt_sku]['mpn_var']=true;
		                }
		                if($asinFlag && $upc_err && $mpn_err){
		                	$Errors['asin_error_info_var'][]=$opt_sku;
                            $cflag++;
		                }
                        /*if(!$asinFlag){
                        	$carray[$opt_sku]['asin_var']=true;
                        }
                        if(!$upcFlag){
                        	$carray[$opt_sku]['upc_var']=true;
                        }
                        if($asinFlag && $upcFlag){
                        	$Errors['asin_error_info_var'][]=$opt_sku;
                            $cflag++;
                        }*/
                        $attr_ids="";
                        $jet_mapped="";
                        $attr_ids_arr=array();
                        $jet_mapped_arr=array();
                        $attr_ids=$product->attr_ids;
                        $jet_mapped=$product->jet_attributes;
                        if($attr_ids !=""){
                            $attr_ids_arr=json_decode($attr_ids,true);
                        }
                        if($jet_mapped !=""){
                            $jet_mapped_arr=json_decode($jet_mapped,true);
                        }
                        $acflag=0;
                        if(is_array($attr_ids_arr) && count($attr_ids_arr)>0){
                            if(is_array($jet_mapped_arr) && count($jet_mapped_arr)>0){
                                foreach($attr_ids_arr as $k_a=>$v_a){
                                    if(array_key_exists(trim($v_a),$jet_mapped_arr) && $jet_mapped_arr[$v_a]!=""){
                                        $acflag++;
                                    }
                                }
                                if($acflag==0){
                                	//$Errors['attribute_mapping_error']="Map Variant Options with Jet Attributes";
                                	$Errors['attribute_mapping_error'][]=$opt_sku;
                                    $Errors['attribute_mapping']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                                    $cflag++;
                                }
                            }else{
                            	//$Errors['attribute_mapping_error']="";
                            	$Errors['attribute_mapping_error'][]=$opt_sku;
                                $Errors['attribute_mapping']=$product->sku;//$product->sku;//"variant : ".$opt_sku." of product : ".
                                $cflag++;
                            }
                        }
    
                    }
                }
                 
            }
        }
        if($cflag==0){
            $carray['success']=true;
        }
        $carray['error']=$Errors;
        return $carray;
    }
    public static function getEligibleVariants($product="",$merchant_id="",$options){
            $eligibleVariants=array();
            if(is_array($options) && count($options)>0){
                    $i=0;
                    foreach($options as $val){
                            $attribute="";
                            $option_id="";
                            $option_id=trim($val['option_id']);
                            $attribute=trim($val['jet_option_attributes']);
                            if($i==0){
                                $eligibleVariants[$option_id]=$attribute;
                            }else{
                                if(!in_array($attribute,$eligibleVariants)){
                                    $eligibleVariants[$option_id]=$attribute;
                                }
                            }
                            $i++;
                    }
            }
            return $eligibleVariants;
    }
    public static function checkproductnoattr($product,$merchant_id,$connection)
    {
        $carray=array();
        $carray['success']=false;
        $carray['error']="";
        $Errors=array();
        $cflag=0;
        
        if($product)
        {
            $upc="";
            $asin="";
            $price="";
            $qty="";
            $nodeid="";
            $upc_err=false;
            $mpn_err=false;
            $brand="";
            $sku="";
            $qty=trim($product->qty);
            $price=trim($product->price);
            $upc = trim($product->upc);
            $asin = trim($product->ASIN);
            $mpn = trim($product->mpn);
            $brand=trim($product->vendor);
            $nodeid = $product->fulfillment_node;
            $sku=$product->sku;
            $countImage=0;
            $image=trim($product->image);
            $imageArr=array();
            $ImageFlag=false;
            $imageArr=explode(',',$image);
            //If all images all broken
            if($image!="" && count($imageArr)>0){
                foreach ($imageArr as $value){
                    if(self::checkRemoteFile($value)==false)
                        $countImage++;
                }
                if(count($imageArr)==$countImage)
                    $ImageFlag=true;
            }
       		if($sku==''){
       			$Errors['sku_error']="Missing Product Sku,";
       			$Errors['sku']=$product->sku;
       			$cflag++;
       		}
       		if($upc=='' && $asin=='' && $mpn==''){
       			$Errors['upc_error']="Missing Barcode or ASIN or MPN,";
       			$Errors['upc']=$product->sku;
       			$cflag++;
       		}
       		if($brand==''){
       			$Errors['brand_error']="Missing brand,";
       			$Errors['brand']=$product->sku;
       			$cflag++;
       		}
       		if($nodeid==''){
       			$Errors['node_id_error']="Missing Jet Browse Node,";
       			$Errors['node_id']=$product->sku;
       			$cflag++;
       		}
       		if($image=='' || $ImageFlag){
       			$Errors['image_error']="Missing or Invalid Image,";
       			$Errors['image']=$product->sku;
       			$cflag++;
       		}
       		if(($price<=0 || ($price && !is_numeric($price))) || trim($price)==""){
       			$Errors['price_error']="Invalid Price,";
       			$Errors['price']=$product->sku;
       			$cflag++;
       		}
       		if(($qty && !is_numeric($qty))||trim($qty)==""||($qty<=0 && is_numeric($qty))){
       			$Errors['qty_error']="Invalid Qauntity,";
       			$Errors['qty']=$product->sku;
       			$cflag++;
       		}  	
            //check upc type
            $type="";
            $existUpc=false;
            $existAsin=false;
            $upcFlag=false;
            $asinFlag=false;
            $existMpn=false;
            //check upc is unique
            $type=self::checkUpcType($upc);
            if($type!="")
                $existUpc=self::checkUpcVariantSimple($upc,$product->id,$sku,$connection);
            //check ASIN is unique
            $existAsin=self::checkAsinVariantSimple($asin,$product->id,$sku,$connection);
            $existMpn=self::checkMpnVariantSimple($mpn,$product->id,$sku,$connection);
            if($upc=="" || (strlen($upc)>0 && $type=="") || (strlen($upc)>0 && $existUpc)){
            	//$Errors['upc_error_info']="Duplicate or Invalid Barcode,";
                $Errors['upc']=$product->sku;
                $upc_err=true;
                $upcFlag=true;
                //$cflag++;
            }
            if($mpn=="" || strlen($mpn)>50 || (strlen($mpn)>50 && $existMpn)){
            	//$Errors['mpn_error']="Invalid Mpn,";
                $mpn_err=true;
                $Errors['upc']=$product->sku;
                //$cflag++;
            }
            if(($upc_err && $asin=="") || ($upc_err && strlen($asin)!=10) || ($upc_err && strlen($asin)==10 && !ctype_alnum ($asin)) || ($upc_err && strlen($asin)==10 && ctype_alnum ($asin) && $existAsin)){
            	//$Errors['asin_error_info']="Duplicate or Invalid ASIN,";
            	$Errors['upc']=$product->sku;
                $asinFlag=true;
                //$cflag++;
            }
            if($asin=="" || strlen($asin)!=10 || (strlen($asin)==10 && !ctype_alnum ($asin)) || (strlen($asin)==10 && ctype_alnum ($asin) && $existAsin))
            {
            	$asinFlag=true;
            }
            if(!$asinFlag){
            	$carray['asin_simp']=true;
            }
            if(!$upc_err){
            	$carray['upc_simp']=true;
            }
            if(!$mpn_err){
                $carray['mpn_simp']=true;
            }
            if($asinFlag && $upc_err && $mpn_err){
            	$Errors['asin_error_info']="Duplicate/Invalid Barcode or ASIN,";
                $cflag++;
            }
            /*if(!$asinFlag){
            	$carray['asin_simp']=true;
            }
            if(!$upcFlag){
            	$carray['upc_simp']=true;
            }
            if($asinFlag && $upcFlag){
            	$Errors['asin_error_info']="Duplicate/Invalid Barcode or ASIN,";
                $cflag++;
            }   */
        }  
        if($cflag==0){
            $carray['success']=true;
        }       
        $carray['error']=$Errors;
        return $carray;
    }
    /* public static function checkCategoryAttributeNotExists($category_id="",$merchant_id="")
    {
    	$category_id=trim($category_id);
    	$merchant_id=trim($merchant_id);
    	try{
    		if($category_id && $merchant_id){
    			$connection = Yii::$app->getDb();
    			$merchantCategory = $connection->createCommand("SELECT `jet_attributes` FROM `jet_category` WHERE category_id='".$category_id."'");// AND merchant_id='".$merchant_id."'
    			$result = $merchantCategory->queryOne();
    			if($result['jet_attributes'])
    				return false;
    		}
    		return true;
    	}catch(Exception $e){
    		return true;
    	}
    	 
    } */
	public static function checkCategoryAttributeNotExists($category_id="",$jetHelper=[],$merchant_id="")
	{
	    $response="";
	    $response = $jetHelper->CGetRequest('/taxonomy/nodes/'.$category_id.'/attributes');
	    $attributes=[];
	    $attributes=json_decode($response,true);
	    if($attributes && count($attributes)>0 && isset($attributes['attributes'])){
	        return false;
	    }
	    return true;
	}
    public static function getLeafCategoryId($jetBrowsenode="",$categoryModel="")
    {
    	$result="";
    	$result=$categoryModel->find()->where(['category_id'=>$jetBrowsenode])->one();
    	if($result){
    		if($result->level==1){
    			$resultLeafId="";
    			$resultLeafId=$categoryModel->find()->where(['parent_id'=>$jetBrowsenode,'level'=>2])->one();
    			if($resultLeafId){
    				return $resultLeafId->category_id;
    			}
    		}elseif($result->level==0){
    			$resultRLeafId="";
    			$resultRLeafId=$categoryModel->find()->where(['root_id'=>$jetBrowsenode,'level'=>2])->one();
    			if($resultRLeafId){
    				return $resultRLeafId->category_id;
    			}
    		}
    	}
    	return false;
    }
    public static function checkRemoteFile($url)
    {
    	$headers = get_headers($url);
    	if(substr($headers[0], 9, 3) == '200') {
    		return true;
    	}else{
    		return false;
    	}
    }
    public static function productUpdateData($result,$data,$jetHelper,$fullfillmentnodeid,$merchant_id,$file,$customPrice="",$connection)
    {
    	//$connection=Yii::$app->getDb();
    	$result1_rows=array();
    	$updateInfo=array();
    	$variants_ids=array();
    	$new_variants_ids=array();
    	$availble_variants=array();
    	$archiveSkus=array();
    	$updateProduct=array();
    	$value=$data;
    	//change custom price
   		/*  	
   		if($customPrice){
    		$priceType="";
    		$changePrice=0;
    		$customPricearr=array();
    		$customPricearr = explode('-',$customPrice);
    		$priceType = $customPricearr[0];
    		$changePrice = $customPricearr[1];
    		unset($customPricearr);
    	} */
    	$product_id=$value['id'];
    	$product_title=$value['title'];
    	$vendor=$value['vendor'];
    	$brand=$value['vendor'];
    	$product_type=$value['product_type'];
    	$product_des=$value['body_html'];
    	$product_des=strip_tags($product_des);
    	$variants=$value['variants'];
    	$images=$value['images'];
    	$product_price=$value['variants'][0]['price'];
    	/* if($priceType && $changePrice!=0){
    		$updatePrice=0;
    		$updatePrice=self::priceChange($product_price,$priceType,$changePrice);
    		if($updatePrice!=0)
    			$product_price = $updatePrice;
    	} */
    	$barcode=$value['variants'][0]['barcode'];
    	$weight=0;$unit="";
	    $weight=$value['variants'][0]['weight'];
	    $unit=$value['variants'][0]['weight_unit'];
	    $message="";
	    $message.="\nProduct_id: ".$product_id."\n";
	    
	    if($weight>0)
	    {
	    	$weight=(float)Jetappdetails::convertWeight($weight,$unit);
	    }					
    	$imagArr=array();
    	$product_images="";
    	$variantArr=array();
    	$simpleflag=false;
    	$OldImages=array();
    	$imageChange=false;
    	$OldImages=explode(',',$result->image);
    	if(is_array($images))
    	{
	    	foreach ($images as $valImg)
	    	{
	    		if(!in_array($valImg['src'],$OldImages)){
	    			$imageChange=true;
	    		}
	    		$imagArr[]=$valImg['src'];
	    	}
	    	$product_images=implode(',',$imagArr);
    	}
    	/* if($product_id==4211751366){
    		var_dump($imageChange);
    	echo "<br>".$product_images;} */
    	unset($OldImages);
    	$product_sku="";
    	$product_sku=$value['variants'][0]['sku'];
    	$product_qty=$value['variants'][0]['inventory_quantity'];
    	$variant_id=$value['variants'][0]['id'];
    	if(trim($product_sku)==""){
    		return;
    	}
    	if(count($variants)==1){
    		$simpleflag=true;
    	}
    	if(count($variants)>1)
    	{
    		$options=$value['options'];
    		$attrId=array();
    		$attrValue=array();
    		$attFlag=false;
    		$attrValue=json_decode($result->attr_ids,true);
    		foreach($options as $key=>$val){
    			$attrname=$val['name'];
    			if(is_array($attrValue) && !in_array($attrname, $attrValue))
    			{
    				$attFlag=true;
    			}
    			$attrId[$val['id']]=$val['name'];
    			foreach ($val['values'] as $k => $v) {
    				$option_value[$attrname][$k]=$v;
    			}
    		}
    		if($attFlag){
    			$message.= "wrong attr\n";
    			//update product option label
    			$updateProduct['attr_ids']=json_encode($attrId);
    			//$result->attr_ids=json_encode($attrId);
    			//function to delete/archive product variants create new and update attr_id on parent product
    			$message.=Jetproductinfo::addNewVariants($product_id,$product_sku,$data,$jetHelper,$merchant_id,$connection);
    			return;
    		}
    		$changeParentTitle=false;
    		$changeParentDes=false;
    		$changeParentCat=false;
    		foreach($variants as $value1)
    		{
    			$option_sku="";
    			$option_title="";
    			$option_image_id="";
    			$option_price="";
    			$option_qty="";
    			$option_barcode="";
    			$option_variant1="";
    			$option_variant2="";
    			$option_variant3="";
    			$flagChange=false;
    			$flagskuChange=false;
    			$vskuChangeData=array();
    			$option_weight=0;$option_unit="";
    			$option_weight=$value1['weight'];
    			$option_unit=$value1['weight_unit'];
    			if($option_weight>0)
    			{
    				$option_weight=(float)Jetappdetails::convertWeight($option_weight,$option_unit);
    			}
    			$variantArr[]=$value1['id'];
    			$option_id=$value1['id'];
    			$variants_ids[]=trim($option_id);
    			$option_title=$value1['title'];
    			$option_sku=$value1['sku'];
    			$option_image_id=$value1['image_id'];
    			$option_price=$value1['price'];
    			/* if($priceType && $changePrice!=0){
    				$updatePrice=0;
    				$updatePrice=self::priceChange($option_price,$priceType,$changePrice);
    				if($updatePrice!=0)
    					$option_price = $updatePrice;
    			} */
    			$option_qty=$value1['inventory_quantity'];
    			$option_variant1=$value1['option1'];
    			$option_variant2=$value1['option2'];
    			$option_variant3=$value1['option3'];
    			$option_barcode=$value1['barcode'];
    			$option_image_url='';
    			$vresult="";
    			$vupdateProduct=array();
    			$imageFlag=false;
    			$vresult=(object)$connection->createCommand('SELECT option_id,option_title,option_sku,jet_option_attributes,option_image,option_qty,option_weight,option_price,option_unique_id,variant_option1,variant_option2,variant_option3,vendor from `jet_product_variants` where option_id="'.$option_id.'"')->queryOne();
    			if(is_array($images))
    			{
	    			foreach ($images as $value2){
	    				if($value2['id']== $option_image_id){
	    					$option_image_url=$value2['src'];
	    					$imageFlag=true;
	    					break;
	    				}
	    			}
    			}
    			if(is_object($vresult) && isset($vresult->option_id))
    			{
    				if($result->type=="simple"){
    					$updateProduct['type']="variants";
    					$updateProduct['attr_ids']=json_encode($attrId);
    					//$result->type="variants";
    				}
    				if($option_sku!="" && $vresult->option_sku!=$option_sku)
    				{
    					if($result->sku==$vresult->option_sku || $result->variant_id==$vresult->option_id)
    					{   $message.= "add new sku:-".$product_sku."\n";
    						//delete product as well as all variants and add new product and archive and upload product with new relation
    						$message.=Jetproductinfo::addNewVariants($product_id,$product_sku,$data,$jetHelper,$merchant_id,$connection);
    						return;
    					}
    					else{
    						//archive variant option and add new variantion with updated children skus
    						$message.= "update variant sku:-".$option_sku."\n";
    						$archiveSkus[]=$vresult->option_sku;
    						$flagskuChange=true;
    						//$vresult->option_sku=$option_sku;
    						$vupdateProduct['option_sku']=$option_sku;
    					}
    				}
    				if($option_title!="" && $vresult->option_title!=$option_title)
    				{	
    					//$vresult->option_title=$option_title;
    					$vupdateProduct['option_title']=$option_title;
    					if($option_sku!=$product_sku)
    					{
    						$message.= "update child var title :-".$option_sku."\n";
    						$flagChange=true;
    						$vskuChangeData['title']=$product_title.'-'.$option_title;
    					}
    				}
    				if($product_title!="" && $product_title!=$result->title)
    				{
    					$changeParentTitle=true;
    					$updateProduct['title']=$product_title;
    					//$result->title=$product_title;
    				}
    				if($changeParentTitle){
    					$message.= "update parent var title :-".$option_sku."\n";
    					$flagChange=true;
    					if($option_sku==$product_sku)
    					{
    						$vskuChangeData['title']=$product_title;
    					}
    					else
    					{
    						$vskuChangeData['title']=$product_title.'-'.$option_title;
    					}
    				}
    				$result->description=strip_tags($result->description);
    				if($result->description!=$product_des)
    				{
    					$changeParentDes=true;
    					$updateProduct['description']=$product_des;
    					//$result->description=$product_des;
    				}
    				if($changeParentDes){
    					$flagChange=true;
    					$message.= "change sku var des:-".$product_sku."\n";
    					$vskuChangeData['description']=$product_des;
    				}
    				if($result->product_type!=$product_type && !$changeParentCat){
    					//$result->product_type = $product_type;
    					$updateProduct['product_type']=$product_type;
    					$modelmap=array();
    					$modelmap=$connection->createCommand('SELECT category_id from `jet_category_map` where merchant_id="'.$merchant_id.'" and product_type="'.$product_type.'"')->queryOne();
    					//$modelmap = JetCategoryMap::find()->where(['merchant_id'=>$merchant_id,'product_type'=>$product_type])->one();
    					if(is_array($modelmap) && count($modelmap)>0){
    						if($modelmap['category_id']!=$result->fulfillment_node){
    							$message.="change product category in\n";
    							$updateProduct['fulfillment_node']=$modelmap['category_id'];
    							//$result->fulfillment_node = $modelmap['category_id'];
    							$changeParentCat=true;
    						}
    					}else{
    						//insert new product-type
    						$sql='INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("'.$merchant_id.'","'.addslashes($product_type).'")';
    						$connection->createCommand($sql)->execute();
    					}
    					unset($modelmap);
    				}
    				if($changeParentCat){
    					$flagChange=true;
    					$message.= "change variant category data\n".$product_sku."\n";
    					$vskuChangeData['category']=$modelmap['category_id'];
    				}
    				if($option_barcode!="" && $vresult->option_unique_id!=$option_barcode)
    				{
    					$message.= "update option barcode :-".$option_sku."\n";
    					$flagChange=true;
    					$vskuChangeData['barcode']=$option_barcode;
    					$vskuChangeData['barcode_as_parent']=0;
    					//$vresult->option_unique_id=$option_barcode;
    					$vupdateProduct['option_unique_id']=$option_barcode;
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['upc']=$option_barcode;
    						//$result->upc=$option_barcode;
    						$vskuChangeData['barcode_as_parent']=1;
    					}	
    				}
    				if($option_image_url!="" && $imageFlag==true && $vresult->option_image!=$option_image_url)
    				{
    					$message.= "update option image :-".$option_sku."\n";
    					//$vresult->option_image=$option_image_url;
    					$vupdateProduct['option_image']=$option_image_url;
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['image']=$product_images;
    						//$result->image=$product_images;
    					}
    					$message.=Jetproductinfo::UpdateImageOnJet($option_sku,$product_images,$option_image_url,$jetHelper,$merhcant_id);
    				}
    				elseif(($option_image_url=="" || $vresult->option_image==$option_image_url) && $imageChange)
    				{
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['image']=$product_images;
    						//$result->image=$product_images;
    					}
    					$message.=Jetproductinfo::UpdateImageOnJet($option_sku,$product_images,$option_image_url,$jetHelper,$merhcant_id);
    				}
    				if($vresult->option_qty!=$option_qty && $flagskuChange==false)
    				{
    					$message.= "update option qty :-".$option_sku."\n";
    					//$vresult->option_qty=$option_qty;
    					$vupdateProduct['option_qty']=$option_qty;
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['qty']=$option_qty;
    						//$result->qty=$option_qty;
    					}
    					if($option_qty>0)
    					{
    						$message.=Jetproductinfo::updateQtyOnJet($option_sku,$option_qty,$jetHelper,$fullfillmentnodeid,$merchant_id);
    					}
    					//add function to change qty on jet.com
    				}
    				if($vresult->vendor!=$vendor)
    				{
    					$message.= "update option vendor :-".$option_sku."\n";
    					$flagChange=true;
    					$vskuChangeData['vendor']=$vendor;
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['vendor']=$vendor;
    						//$result->vendor=$vendor;
    					}
    					//$vresult->vendor=$vendor;
    					$vupdateProduct['vendor']=$vendor;
    				}
    				if(!$customPrice && $vresult->option_price!=$option_price && $flagskuChange==false)
    				{
    					$message.= "update option price :-".$option_sku."\n";
    					//$vresult->option_price=(float)$option_price;
    					$vupdateProduct['option_price']=(float)$option_price;
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['price']=(float)$option_price;
    						//$result->price=(float)$option_price;
    					}
    					//add function to change price on jet.com
    					$message.=Jetproductinfo::updatePriceOnJet($option_sku,(float)$option_price,$jetHelper,$fullfillmentnodeid,$merchant_id);
    				}
    				
    				if($vresult->option_weight!=round($option_weight,2) && $flagskuChange==false)
    				{
    					$message.= "update option weight :-".$option_sku."\n";
    					$flagChange=true;
    					$vskuChangeData['weight']=$option_weight;
    					//$vresult->option_weight=$option_weight;
    					$vupdateProduct['option_weight']=$option_weight;
    					if($option_sku==$product_sku)
    					{
    						$updateProduct['weight']=$option_weight;
    						//$result->weight=$option_weight;
    					}
    				}
    				if($option_variant1!="" && $vresult->variant_option1!=$option_variant1)
    				{ 
    					$message.= "update option variant1 in :-".$option_sku."\n";
    					$attributes=array();
    					$flagChange=true;
    					if($vresult->jet_option_attributes)
    					{
    						
	    					$attributes=json_decode($vresult->jet_option_attributes,true);
	    					if (count($attributes)>1)
	    					{
		    					foreach ($attributes as $key=>$attr_val){
		    						if(in_array($vresult->variant_option1,$attributes)){
		    							$attr_val=$option_variant1;
		    							$message.= "update option variant1 change:-".$option_sku."\n";
		    							$vskuChangeData['variant_option'][$key]=$option_variant1;
		    							break;
		    						}
		    					}
    						}
	    					//$vupdateProduct['jet_option_attributes']=json_encode($attributes);
	    					//$vresult->jet_option_attributes=json_encode($attributes);
	    				}
	    				$vupdateProduct['variant_option1']=$option_variant1;
    					//$vresult->variant_option1=$option_variant1;
    				}
    				if($option_variant2!="" && $vresult->variant_option2!=$option_variant2)
    				{   
    					$message.= "update option variant2 :-".$option_sku."\n";
    					$attributes=array();
    					$flagChange=true;
    					if($vresult->jet_option_attributes)
    					{
	    					$attributes=json_decode($vresult->jet_option_attributes,true);
	    					/* if ($merchant_id==7){
	    						echo "<pre>";
	    						print_r($attributes);;
	    						die;
	    					} */
	    					if (count($attributes)>1)
	    					{
		    					foreach ($attributes as $key=>$attr_val)
		    					{
		    						if(in_array($vresult->variant_option2,$attributes))
		    						{
		    							$attr_val=$option_variant2;
		    							$vskuChangeData['variant_option'][$key]=$option_variant2;
		    							break;
		    						}
		    					}
	    					}
	    					//$vupdateProduct['jet_option_attributes']=json_encode($attributes);
	    					//$vupdateProduct['jet_option_attributes']=json_encode($attributes);
    					}
    					$vupdateProduct['variant_option2']=$option_variant2;
    					//$vresult->variant_option2=$option_variant2;
    				}
    				if($option_variant3!="" && $vresult->variant_option3!=$option_variant3)
    				{ 
    					$message.= "update option variant3 :-".$option_sku."\n";
    					$attributes=array();
    					$flagChange=true;
    					if($vresult->jet_option_attributes)
    					{
	    					$attributes=json_decode($vresult->jet_option_attributes,true);
	    					foreach ($attributes as $key=>$attr_val){
	    						if($vresult->variant_option3==$attr_val){
	    							$attributes[$key]=$option_variant3;
	    							$vskuChangeData['variant_option'][$key]=$option_variant3;
	    							break;
	    						}
	    					}
	    					//$vupdateProduct['jet_option_attributes']=json_encode($attributes);
	    					//$vresult->jet_option_attributes=json_encode($attributes);
	    				}
	    				$vupdateProduct['variant_option3']=$option_variant3;
    					//$vresult->variant_option3=$option_variant3;
    				}
    				if($flagChange==true && $flagskuChange==false){
    					$message.= "change sku variant data request:-".$option_sku."\n";
    					$message.=Jetproductinfo::updateSkudataOnJet($option_sku,$product_id,$option_id,$vskuChangeData,"variants",$jetHelper,$merchant_id);
    					//var_Dump($updateInfo);die;
    				}
    				if(is_array($vupdateProduct) && count($vupdateProduct)>0){
    					$i=count($vupdateProduct);
    					$j=1;
    					$query='UPDATE `jet_product_variants` SET ';
    					foreach($vupdateProduct as $key=>$val){
    						if($i==$j)
    							$query.='`'.$key.'`="'.addslashes($val).'"';
    						else
    							$query.='`'.$key.'`="'.addslashes($val).'",';
    						$j++;
    					}
    					$query.=' where option_id="'.$option_id.'"';
    					//echo $query;die("chala");
    					$connection->createCommand($query)->execute();
    					unset($j);
    					unset($i);
    				}
    				//$vresult->save(false);
    			}
    			else
    			{
    				$sql='INSERT INTO `jet_product_variants`(
	    						`option_id`,`product_id`,
	    						`merchant_id`,`option_title`,
	    						`option_sku`,`option_image`,
	    						`option_price`,`option_qty`,
	    						`variant_option1`,`variant_option2`,
	    						`variant_option3`,`vendor`,
	    						`option_unique_id`,`option_weight`
    						)VALUES(
	    						"'.$option_id.'","'.$product_id.'",
	    						"'.$merchant_id.'","'.addslashes($option_title).'",
	    						"'.$option_sku.'","'.addslashes($option_image_url).'",
	    						"'.(float)$option_price.'","'.(int)$option_qty.'",
	    						"'.addslashes($option_variant1).'","'.addslashes($option_variant2).'",
	    						"'.addslashes($option_variant3).'","'.addslashes($vendor).'",
	    						"'.$option_barcode.'","'.$option_weight.'"
    						)';
    				$connection->createCommand($sql)->execute();
    				//function to add new variants option and upload on jet.com as well as change variation
    			}
    		}
    	}
    	//delete variants if not exist in shopify
    	$availble_variants=array();
    	$vallresult=array();
    	$vallresult=$connection->createCommand('SELECT `option_id` from `jet_product_variants` where product_id="'.$product_id.'"')->queryAll();
    	//$vallresult=JetProductVariants::find()->where(['merchant_id'=>$merchant_id,'product_id'=>$product_id])->all();
    	if(is_array($vallresult) && count($vallresult)>0){
    		foreach($vallresult as $res){
    			$availble_variants[]=trim($res['option_id']);
    		}
    	}
    	unset($vallresult);
    	$resulting_arr=array();
    	$resulting_arr = array_diff($availble_variants, $variants_ids);
    	unset($availble_variants);
    	if(is_array($resulting_arr) && count($resulting_arr)>0){
    		foreach($resulting_arr as $val)
    		{
    			$delresult=array();
    			//if deleted variant is parent
    			$delresult=$connection->createCommand('SELECT `option_sku` from `jet_product_variants` where option_id="'.$val.'"')->queryOne();
    			//$delresult=JetProductVariants::find()->select('option_sku')->where(['option_id'=>$val])->one();
    			if(is_array($delresult) && count($delresult)>0){
    				//die("del child");
    				if($delresult['option_sku']==$result->sku){
    					$message.= $delresult['option_sku']."----delgfdg child";
    					//delete all data from product as well variants and send new variantion
    					$message.=Jetproductinfo::addNewVariants($product_id,$product_sku,$data,$jetHelper,$merchant_id,$connection);
    					return;
    				}
    				else
    				{   
    					$message.= $delresult['option_sku']."---------deldfdfdfdf child";
    					$connection->createCommand('DELETE FROM `jet_product_variants` WHERE option_id="'.$val.'"')->execute();
    					//$delresult->delete();
    					//archive skus and change variantion
    					$archiveSkus[]=$result->sku;
    					
    				}
    			}	
    		}
    	}
    	unset($delresult);
    	unset($resulting_arr);
    	//change product information
    	$skuChangeData=array();
    	$flagSim=false;
    	$flagSimImage=false;
    	$flagSimPrice=false;
    	$flagSimQty=false;
    	$flagsimpleskuChange=false;
    	if($product_sku=="" && $result->sku!="" && $simpleflag==true)
    	{
    		//product not exist in shopify and archive on jet
    		$message.= "change simple sku value is null:-".$product_sku."\n";
    		$flagsimpleskuChange=true;
    		$archiveSkus[]=$result->sku;
    	}
    	if($product_sku!="" && $simpleflag==true && $result->sku!=$product_sku)
    	{
    		//product exist but sku change for simple and upload new simple product 
    		$message.= "change simple sku value:-".$product_sku."\n";
    		$flagsimpleskuChange=true;
    		$archiveSkus[]=$result->sku;
    		$updateProduct['sku']=$product_sku;
    		//$result->sku=$product_sku;
    	}
    	if($product_title!="" && $product_title!=$result->title && $simpleflag==true)
    	{
    		$message.= "change simple sku title:-".$product_sku."\n";
    		$flagSim=true;
    		$skuChangeData['title']=$product_title;
    		$updateProduct['title']=$product_title;
    		//$result->title=$product_title;
    	}
    	if($result->vendor!=$vendor && $simpleflag==true)
    	{
    		$message.= "change simple sku vendor:-".$product_sku."\n";
    		$flagSim=true;
    		$skuChangeData['brand']=$vendor;
    		$updateProduct['vendor']=$vendor;
    		//$result->vendor=$vendor;
    		//$result->brand=$vendor;
    	}
    	$result->description=strip_tags($result->description);
    	if($result->description!=$product_des && $simpleflag==true)
    	{
    		$message.= "change simple sku des:-".$product_sku."\n";
    		$flagSim=true;
    		$skuChangeData['description']=$product_des;
    		$updateProduct['description']=$product_des;
    		//$result->description=$product_des;
    	}
    	if($result->weight!=round($weight,2) && $simpleflag==true)
    	{
    		$message.= "change simple sku wight:-".$product_sku."\n";
    		$flagSim=true;
    		$skuChangeData['weight']=$weight;
    		$updateProduct['weight']=$weight;
    		//$result->weight=$weight;
    	}
    	if($result->upc!=$barcode && $simpleflag==true)
    	{
    		$message.= "change simple sku upc:-".$product_sku."\n";
    		$flagSim=true;
    		$skuChangeData['barcode']=$barcode;
    		$updateProduct['upc']=$barcode;
    		//$result->upc=$barcode;
    	}
    	if($result->product_type!=$product_type && $simpleflag==true)
    	{
    		//$result->product_type = $product_type;
    		$updateProduct['product_type']=$product_type;
    		$modelmap="";
    		$modelmap=array();
    		$modelmap=$connection->createCommand('SELECT category_id from `jet_category_map` where merchant_id="'.$merchant_id.'" and product_type="'.$product_type.'"')->queryOne();
    		//$modelmap = JetCategoryMap::find()->where(['merchant_id'=>$merchant_id,'product_type'=>$product_type])->one();
    		if(is_array($modelmap) && count($modelmap)>0){
    			if($modelmap['category_id']!=$result->fulfillment_node){
    				$message.="change product category in\n";
    				$updateProduct['fulfillment_node']=$modelmap['category_id'];
    				//$result->fulfillment_node = $modelmap['category_id'];
    				$changeParentCat=true;
    			}
    		}else{
    			//insert new product-type
    			$sql='INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("'.$merchant_id.'","'.addslashes($product_type).'")';
    			$connection->createCommand($sql)->execute();
    		}
    		unset($modelmap);
    	}
    	if($imageChange==true && $simpleflag==true)
    	{
    		$message.= "change sku simple image:-".$product_sku."\n";
    		$updateProduct['image']=$product_images;
    		//$result->image=$product_images;
    		$message.=Jetproductinfo::UpdateImageOnJet($product_sku,$product_images,$imagArr[0],$jetHelper,$merchant_id);
    	}
    	if(!$customPrice && $result->price!=$product_price && $simpleflag==true && $flagsimpleskuChange==false)
    	{
    		//send price information
    		$message.= "change simple sku price:-".$product_sku."\n";
    		$updateProduct['price']=$product_price;
    		//$result->price=$product_price;
    		$message.= Jetproductinfo::updatePriceOnJet($product_sku,$product_price,$jetHelper,$fullfillmentnodeid,$merchant_id);
    	}
    	if($result->qty!=$product_qty && $simpleflag==true && $flagsimpleskuChange==false)
    	{//echo "hello";
    		//send price information
    		$message.= "change simple sku qty:-".$product_sku."\n";
    		//$result->qty=$product_qty;
    		$updateProduct['qty']=$product_qty;
    		if($product_qty>0)
    			$message.=Jetproductinfo::updateQtyOnJet($product_sku,$product_qty,$jetHelper,$fullfillmentnodeid,$merchant_id);
    	}
    	
    	if($simpleflag==true && $result->type=="variants")
    	{
    		$updateProduct['type']="simple";
    		$updateProduct['attr_ids']="";
    		$updateProduct['jet_attributes']="";
    		//$result->type="simple";
    	}
    	if($simpleflag==true && $flagSim==true && $flagsimpleskuChange==false)
    	{
    		//update simple product sku information
    		$message.= "update sku simple data request:-".$product_sku."\n";
    		$message.=Jetproductinfo::updateSkudataOnJet($product_sku,$product_id,"",$skuChangeData,"simple",$jetHelper,$merchant_id);
    	}
    	//archive prouducts
    	$message.=Jetproductinfo::archiveProductOnJet($archiveSkus,$jetHelper,$merchant_id);
    	if(is_array($updateProduct) && count($updateProduct)>0)
    	{
    		$i=count($updateProduct);
    		$j=1;
    		$query='UPDATE `jet_product` SET ';
    		foreach($updateProduct as $k=>$v){
    			if($j==$i)
    				$query.='`'.$k.'`="'.addslashes($v).'"';
    			else
    				$query.='`'.$k.'`="'.addslashes($v).'",';
    			$j++;
    		}
    		$query.=' where id="'.$product_id.'"';
    		unset($j);
    		unset($i);
    		$connection->createCommand($query)->execute();
    	}
    	unset($archiveSkus);
    	unset($skuChangeData);
    	unset($updateProduct);
    	//$result->save(false);
    	fwrite($file, $message);
    }
    public static function updateSkudataOnJet($sku,$product_id,$option_id,$changeData,$type,$jetHelper,$merchant_id)
    {
    	$message="";
    	$resultJet="";
    	$connection=Yii::$app->getDb();
    	$resultJet=self::checkSkuOnJet($sku,$jetHelper,$merchant_id);
    	if($resultJet==false){
    		return;
    	}
    	$response=array();
    	$response=json_decode($resultJet,true);
    	$SKU_Array= array();
    	$unique=array();
    	$isUploadDes=false;
    	$isUploadTitle=false;
    	$isUploadVendor=false;
    	$isUploadWeight=false;
    	$isUploadbarcode=false;
    	$isUploadVariant1=false;
    	$isUploadVariant2=false;
    	$isUploadVariant3=false;
    	$isUpload=false;
    	$Attribute_arr = array();
    	$Attribute_array = array();
    	$SKU_Array['product_title']=$response['product_title'];
    	$SKU_Array['jet_browse_node_id']=$response['jet_browse_node_id'];
    	$SKU_Array['multipack_quantity']=$response['multipack_quantity'];
    	$SKU_Array['brand']=$response['brand'];
    	$response['product_description']=strip_tags($response['product_description']);
    	$SKU_Array['product_description']=$response['product_description'];
    	$SKU_Array['main_image_url']=$response['main_image_url'];
    	$SKU_Array['alternate_images']=$response['alternate_images'];
    	if(isset($response['ASIN']))
    		$SKU_Array['ASIN']=$response['ASIN'];
    	if(isset($response['standard_product_codes']))
    		$SKU_Array['standard_product_codes']=$response['standard_product_codes'];
    	
    	$SKU_Array['attributes_node_specific']=$response['attributes_node_specific'];
    	$SKU_Array['manufacturer']=$response['brand'];
    	$SKU_Array['mfr_part_number']=$sku;
    	 
    	if($type=="variants"){
    		if(is_array($changeData)){
    			if(array_key_exists("title",$changeData)){
    				$message.= "update sku variants title parent fun in: ".$changeData['title']."\n";
    				$isUpload=true;
    				$SKU_Array['product_title']=addslashes($changeData['title']);
    			}
    			if(array_key_exists("description",$changeData)){
    				$message.= "update sku variants desc fun in: \n";
    				$isUpload=true;
    				$description="";
    				$description=$changeData['description'];
    				$description=strip_tags($description);
    				if(strlen($description)>2000)
    					$description=$jetHelper->trimString($description, 2000);
    				$SKU_Array['product_description']=addslashes($description);
    			}
    			if(array_key_exists("vendor",$changeData)){
    				$message.= "update sku variants vendor fun in \n";
    				$isUpload=true;
    				$SKU_Array['brand']=$changeData['vendor'];
    				$SKU_Array['manufacturer']=$changeData['vendor'];
    			}
    			if(array_key_exists("weight",$changeData)){
    				$message.= "update sku variants weight fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['shipping_weight_pounds']=(float)$changeData['weight'];
    			}
    			/* if(array_key_exists("category",$changeData)){
    				$message.= "update sku variants category fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['jet_browse_node_id']=(int)$changeData['category'];
    			} */
    			if(array_key_exists("barcode",$changeData)){
    				$barcode=$changeData['barcode'];
    				$type="";
    				$type=self::checkUpcType($barcode);
    				if($type!="" && self::checkUpcVariants($barcode,$product_id,$option_id,$changeData['barcode_as_parent'],$connection)==false)
    				{
    					$message.= "update sku variants barcode fun in"."\n";
    					$isUpload=true;
    					$SKU_Array['standard_product_codes'][]=array('standard_product_code'=>$barcode,'standard_product_code_type'=>$type);
    				}
    			}
    			if(array_key_exists("variant_option",$changeData)){
    				//var_dump($changeData['variant_option']);
    				foreach($response['attributes_node_specific'] as $key=>$value)
    				{
    					if(array_key_exists($value['attribute_id'],$changeData['variant_option']) && $value['attribute_value']!=$changeData['variant_option'][$value['attribute_id']]){
    						$message.= "update sku variants option fun in"."\n";
    						$isUpload=true;
    						$response['attributes_node_specific'][$key]['attribute_value']=$changeData['variant_option'][$value['attribute_id']];
    					}
    				}
    				$SKU_Array['attributes_node_specific']=$response['attributes_node_specific'];
    			}
    		}
    	}
    	else
    	{
    		if(is_array($changeData)){
    			if(array_key_exists("title",$changeData)){
    				$message.= "update sku simple title fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['product_title']=$changeData['title'];
    			}
    			if(array_key_exists("vendor",$changeData)){
    				$message.= "update sku simple vendor fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['brand']=$changeData['vendor'];
    				$SKU_Array['manufacturer']=$changeData['vendor'];
    			}
    			if(array_key_exists("weight",$changeData)){
    				$message.= "update sku simple weight fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['shipping_weight_pounds']=(float)$changeData['weight'];
    			}
    			/* if(array_key_exists("category",$changeData)){
    				$message.= "update sku simple category fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['jet_browse_node_id']=(int)$changeData['category'];
    			} */
    			if(array_key_exists("barcode",$changeData)){
    				$barcode=$changeData['barcode'];
    				$type="";
    				$type=self::checkUpcType($barcode);
    
    				if($type!="" && self::checkUpcSimple($barcode,$product_id,$collection))
    				{
    					$message.= "update sku simple upc fun in"."\n";
    					$isUpload=true;
    					$SKU_Array['standard_product_codes'][]=array('standard_product_code'=>$barcode,'standard_product_code_type'=>$type);
    				}
    			}
    			if(array_key_exists("description",$changeData)){
    				$description=$changeData['description'];
    				$description=strip_tags($description);
    				if(strlen($description)>2000)
    					$description=$jetHelper->trimString($description, 2000);
    				$message.= "update sku simple des fun in"."\n";
    				$isUpload=true;
    				$SKU_Array['product_description']=addslashes($description);
    			}
    		}
    	}
    	
    	if($isUpload==true){
    		$newResponse="";
    		$newResponsearr=array();
    		$message.= "change Sku Data:\n".json_encode($SKU_Array);
    		$newResponse=$jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku),json_encode($SKU_Array),$merchant_id);
    		$newResponsearr=json_decode($newResponse,true);
    		if(isset($newResponsearr['errors'])){
    			$message.= "update sku variants error fun in"."\n";
    			$message.= $sku.' : '.$newResponsearr['errors'].'\n';
    		}
    	}
    	//var_dump($SKU_Array);
    	$message.= "\n<!----------------------updateSkudataOnJet function End------------------------------>\n";
    	return $message;
    }
    public static function updatePriceOnJet($sku,$price,$jetHelper,$fullfillmentnodeid,$merchant_id)
    {
    	$message="";
    	if(self::checkSkuOnJet($sku,$jetHelper,$merchant_id)==false){
    		return;
    	}
    	$message.= "update sku price fun in"."\n";
    	$priceArray=array();
    	$priceinfo=array();
    	$priceArray['price']=(float)$price;
    	$priceinfo['fulfillment_node_id']=$fullfillmentnodeid;
    	$priceinfo['fulfillment_node_price']=(float)$price;
    	$priceArray['fulfillment_nodes'][]=$priceinfo;
    	$responsePrice="";
    	$responsePrice = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/price',json_encode($priceArray),$merchant_id);
    	$responsePrice=json_decode($responsePrice,true);
    	if(isset($responsePrice['errors']))
    	{
    		$message.= '\n'.json_encode($priceArray)."--price upload error--".$responsePrice['errors'];
    		//return $sku."=>".$responsePrice['errors'];
    	}
    	$message.= "\n<!----------------------updatePriceOnJet function End------------------------------>\n";
    	return $message;
    }
    public static function updateQtyOnJet($sku,$qty,$jetHelper,$fullfillmentnodeid,$merchant_id)
    {//echo "hello qty";
    	$message="";
    	$message.=  "update sku qty fun in"."\n";
    	if(self::checkSkuOnJet($sku,$jetHelper,$merchant_id)==false){
    	return;
    	}
	    $inv=array();
	    $inventory=array();
	    $inv['fulfillment_node_id']=$fullfillmentnodeid;
	    $inv['quantity']=(int)$qty;
	    $inventory['fulfillment_nodes'][]=$inv;
	    $responseInventory="";
	    $response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory),$merchant_id);
	    $responseInventory = json_decode($response,true);
	    if(isset($responseInventory['errors'])){
	    	$message.=  '\n'.json_encode($inventory)."--qty upload error--".$responseInventory['errors'];
	    	//return $sku."=>".$responseInventory['errors'];
	    }
	     $message.= "\n<!----------------------updateQtyOnJet function End------------------------------>\n";
	     return $message;
    }
    public static function UpdateImageOnJet($sku,$images,$image="",$jetHelper,$merchant_id)
    {//echo "hello image";
    	$message="";
    	if(self::checkSkuOnJet($sku,$jetHelper,$merchant_id)==false){
    		return;
   	 	}
	    $product_images=array();
	    $product_images=explode(',',$images);
	    $Imagesupdate=array();
	    $i=1;
	    //if main image is broken
	    $keyMain=0;
	    if($image && self::checkRemoteFile($image)==false){
	    	foreach ($product_images as $key=>$value){
	    		if($value!="" && self::checkRemoteFile($value)==true){
	    			$Imagesupdate['main_image_url']=$value;
	    			$keyMain=$key;
	    			break;
	    		}
	    	}
	    }
	    else
	    	$Imagesupdate['main_image_url']=$image;
	     
	    //alternate images
	    foreach ($product_images as $key=>$value){
	    	if($value!="" && self::checkRemoteFile($value)==true){
	    		if($keyMain==$key)
	    			continue;
	    		if($i==8)
	    			break;
	    		$Imagesupdate['alternate_images'][]=array('image_slot_id'=>$i,'image_url'=>$value);
	    		$i++;
	    	}
	    }
	    //send updated images on jet
	    if($Imagesupdate)
	    {
	    	$message.=  "update sku image fun in"."\n";
	    	$responseImages="";$response="";
	    	$response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/image',json_encode($Imagesupdate),$merchant_id);
	    	$responseImages = json_decode($response,true);
	    	if(isset($responseImages['errors'])){
	    		$message.= $sku."=>".$responseImages['errors'].'\n';
	    	}
	    }
	    $message.= "\n<!----------------------UpdateImageOnJet function End------------------------------>\n";
	    return $message;
    }
    public static function archiveProductOnJet($skus,$jetHelper,$merchant_id)
    {
    	$message="";
    	if(is_array($skus) && count($skus)>0)
    	{
    		foreach ($skus as $sku)
    		{
    			$message.= "update sku archive fun in"."\n";
    			$newResponse="";
    			$newResponsearr=array();
    			$newResponse=$jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode(array("is_archived"=>true)),$merchant_id);
    			$newResponsearr=json_decode($newResponse,true);
    			if(isset($newResponsearr['errors'])){
    				$error['archive'][]='\n'.$sku.' : '.$newResponsearr;
    			}
    		}
    	}
    	if(isset($error['archive']))
    		$message.= implode(', ',$error['archive'])."\n";
    	$message.= "\n<!----------------------archiveProductOnJet function End------------------------------>\n";
    	return $message;
    }
    public static function addNewVariants($product_id,$sku,$data,$jetHelper,$merchant_id,$connection)
    {
    	//archive skus
    	$message="";
    	$message.="hello new variants in"."\n";
    	$error=array();
    	$modelProVar=array();
    	$archiveSkus=array();
    	if(self::checkSkuOnJet($sku,$jetHelper,$merchant_id)==true){
    		$archiveSkus[]=$sku;
    	}
    	$modelProVar=$connection->createCommand('SELECT `option_sku`,`option_id` from `jet_product_variants` where product_id="'.$product_id.'"')->queryAll();
    	//$modelProVar = JetProductVariants::find()->select('option_sku')->where(['product_id'=>$result->id])->all();
    	if(is_array($modelProVar) && count($modelProVar)>0)
    	{
    		foreach($modelProVar as $value)
    		{
    			if(self::checkSkuOnJet($value['option_sku'],$jetHelper,$merchant_id)==true)
    			{
    				$archiveSkus[]=$value['option_sku'];
    			}
    			$connection->createCommand('DELETE FROM `jet_product_variants` WHERE option_id="'.$value['option_id'].'"')->execute();
    			//$value->delete();
    		}
    	}
    	$connection->createCommand('DELETE FROM `jet_product` WHERE id="'.$product_id.'"')->execute();
    	//$result->delete();
    	//add new records to database
    	$message.=self::archiveProductOnJet($archiveSkus,$jetHelper,$merchant_id);
    	$message.=self::saveNewRecords($data,$merchant_id,$connection);
    	$message.= "\n<!----------------------addNewVariants function End------------------------------>\n";
    	return $message;
    }
    public static function checkSkuOnJet($sku,$jetHelper,$merchant_id)
    {
    	$response="";
    	$response = $jetHelper->CGetRequest('/merchant-skus/'.rawurlencode($sku),$merchant_id);
    	$responsearray=array();
    	$responsearray=json_decode($response,true);
    	if($responsearray && !isset($responsearray['errors']))
    		return $response;
    	else
    		return false;
    }
    public static function saveNewRecords($data,$merchant_id,$connection)
    {
    	$message="";
    	//$connection=Yii::$app->getDb();
    	if($data)
    	{
    		$product_images="";
    		$images=array();
    		$images=$data['images'];
    		$imagArr=array();
    		foreach ($images as $valImg)
    		{
    			$imagArr[]=$valImg['src'];
    		}
    		$product_images=implode(',',$imagArr);
    		if(count($data['variants'])>1)
    		{
    			foreach ($data['variants'] as $value)
    			{
    				if($value['sku']=="")
    					continue;
    					$option_weight=0;
    					$option_price=0;
    					$option_price=(float)$value['price'];
    					/* if($priceType && $changePrice!=0){
    					 $updatePrice=0;
    					 $updatePrice=self::priceChange($option_price,$priceType,$changePrice);
    					 if($updatePrice!=0)
    					 	$option_price = $updatePrice;
    					 	} */
    					 $option_weight=$value['weight'];
    					 $option_image_url="";
    					 foreach ($images as $value2){
    					 	if($value2['id']== $value['image_id']){
    					 		$option_image_url=$value2['src'];
    					 	}
    					 }
    					 $sql='INSERT INTO `jet_product_variants`(
	    						`option_id`,`product_id`,
	    						`merchant_id`,`option_title`,
	    						`option_sku`,`option_image`,
	    						`option_price`,`option_qty`,
	    						`variant_option1`,`variant_option2`,
	    						`variant_option3`,`vendor`,
	    						`option_unique_id`,`option_weight`
    						)VALUES(
	    						"'.$value['id'].'","'.$value['product_id'].'",
	    						"'.$merchant_id.'","'.addslashes($value['title']).'",
	    						"'.$value['sku'].'","'.addslashes($option_image_url).'",
	    						"'.(float)$option_price.'","'.(int)$value['inventory_quantity'].'",
	    						"'.addslashes($value['option1']).'","'.addslashes($value['option2']).'",
	    						"'.addslashes($value['option3']).'","'.addslashes($vendor).'",
	    						"'.$value['barcode'].'","'.$option_weight.'"
    						)';
    					 $model = $connection->createCommand($sql)->execute();
    
    			}
    			//add attribute
    			$options=$data['options'];
    			$attrId=array();
    			foreach($options as $key=>$val){
    				$attrname=$val['name'];
    				$attrId[$val['id']]=$val['name'];
    				foreach ($val['values'] as $k => $v) {
    					$option_value[$attrname][$k]=$v;
    				}
    			}
    			$attr_id=json_encode($attrId);
    		}
    		$model="";
    		$product_price=0;
    		$product_price=(float)$data['variants'][0]['price'];
    		/* if($priceType && $changePrice!=0){
    		 $updatePrice=0;
    		 $updatePrice=self::priceChange($product_price,$priceType,$changePrice);
    		 if($updatePrice!=0)
    		 	$product_price = $updatePrice;
    		 	} */
    		 $weight=0;
    		 $weight=$data['variants'][0]['weight'];
    		 if($weight>0)
    		 {
    		 	$weight=(float)Jetappdetails::convertWeight($weight,$data['variants'][0]['weight_unit']);
    		 }
    		 //add product into database
    		 $new_product_flag=false;
    		 $productmodel="";
    		 $result="";
    		 $productmodel = $connection->createCommand("SELECT * FROM `jet_product` WHERE id='".$product_id."'");
    		 $result = $productmodel->queryOne();
    		 if(!$result)
    		 {
    		 	if(count($data['variants'])>1)
    		 	{
    		 		$sql='INSERT INTO `jet_product`
    						(
	    						`id`,`merchant_id`,
	    						`title`,`sku`,
	    						`type`,`description`,
	    						`image`,`price`,
    							`qty`,`attr_ids`,
	    						`upc`,`status`,
	    						`vendor`,`variant_id`,
	    						`product_type`,`weight`
    						)
    						VALUES
    						(
    							"'.$data['id'].'","'.$merchant_id.'",
    							"'.addslashes($data['title']).'","'.addslashes($data['variants'][0]['sku']).'",
    							"variants","'.addslashes($data['body_html']).'",
    							"'.addslashes($product_images).'","'.(float)$product_price.'",
    							"'.(int)$data['variants'][0]['inventory_quantity'].'","'.addslashes($attr_id).'",
    							"'.addslashes($data['variants'][0]['barcode']).'","Not Uploaded",
    							"'.addslashes($data['vendor']).'","'.$data['variants'][0]['id'].'",
    							"'.addslashes($data['product_type']).'","'.$data['variants'][0]['weight'].'"
    						)';
    		 		$model = $connection->createCommand($sql)->execute();
    		 		$new_product_flag=true;
    		 	}
    		 	else
    		 	{
    		 		$sql='INSERT INTO `jet_product`
    						(
	    						`id`,`merchant_id`,
	    						`title`,`sku`,
	    						`type`,`description`,
	    						`image`,`price`,
    							`qty`,`attr_ids`,
	    						`upc`,`status`,
	    						`vendor`,`variant_id`,
	    						`product_type`,`weight`
    						)
    						VALUES
    						(
    							"'.$data['id'].'","'.$merchant_id.'",
    							"'.addslashes($data['title']).'","'.addslashes($data['variants'][0]['sku']).'",
    							"simple","'.addslashes($data['body_html']).'",
    							"'.addslashes($product_images).'","'.(float)$product_price.'",
    							"'.(int)$data['variants'][0]['inventory_quantity'].'","",
    							"'.addslashes($data['variants'][0]['barcode']).'","Not Uploaded",
    							"'.addslashes($data['vendor']).'","'.$data['variants'][0]['id'].'",
    							"'.addslashes($data['product_type']).'","'.$data['variants'][0]['weight'].'"
    						)';
    		 		$model = $connection->createCommand($sql)->execute();
    		 		$new_product_flag=true;
    		 	}
    		 }
    
    		 $modelNew='';
    		 if($data['product_type'])
    		 {
    		 	$modelmap="";
    		 	$query="";
    		 	$queryObj="";
    		 	$query='SELECT category_id FROM `jet_category_map` where merchant_id="'.$merchant_id.'" AND product_type="'.$data['product_type'].'"';
    		 	$queryObj = $connection->createCommand($query);
    		 	$modelmap = $queryObj->queryOne();
    		 	//$modelmap=JetCategoryMap::find()->where(['merchant_id'=>$merchant_id,'product_type'=>$product_type])->one();
    		 	if($modelmap){
    		 		if($new_product_flag){
    		 			$updateResult="";
    		 			$query='UPDATE `jet_product` SET fulfillment_node="'.$modelmap['category_id'].'" where id="'.$data['id'].'"';
    		 			$updateResult = $connection->createCommand($query)->execute();
    		 		}
    		 	}else{
    
    		 		$queryObj="";
    		 		$query='INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("'.$merchant_id.'","'.$data['product_type'].'")';
    		 		$queryObj = $connection->createCommand($query)->execute();
    		 	}
    		 }
    		 //inssert merchant products
    		 $merchantProduct="";
    		 $result="";
    		 $merchantProduct = $connection->createCommand("SELECT * FROM `jet_merchant_products` WHERE product_id='".$data['id']."'");
    		 $result = $merchantProduct->queryOne();
    		 if(!$result)
    		 {
    		 	$sql="";
    		 	$sql="INSERT INTO `jet_merchant_products`(`product_id`,`merchant_id`) VALUES ('".$data['id']."','".$merchant_id."')";
    		 	$model = $connection->createCommand($sql)->execute();
    		 }
    		 $queryObj="";
    		 $modelU="";
    		 $query="SELECT product_count,total_product FROM `insert_product` where merchant_id='".$merchant_id."'";
    		 $queryObj = $connection->createCommand($query);
    		 $modelU = $queryObj->queryOne();
    		 if($modelU)
    		 {
    		 	$updatedProduct=$countUpload=0;
    		 	$updatedProduct=$modelU['product_count']+1;
    		 	$countUpload=$modelU['total_product']+1;
    		 	 
    		 	$query="UPDATE `insert_product` SET product_count='".$updatedProduct."',total_product='".$countUpload."' where merchant_id='".$merchant_id."' ";
    		 	$result = $connection->createCommand($query)->execute();
    		 }
    	}
    	unset($data);
    	unset($images);
    	unset($imagArr);
    	unset($attrId);
    	unset($options);
    	unset($modelU);
    	unset($result);
    	return $message;
    }
    public static function saveNewRecords1($data,$merchant_id,$connection)
    {
    	$message="";
    	//$connection=Yii::$app->getDb();
    	$product_images="";
    	$images=array();
    	if($data)
    	{
    		
    		if (isset($data['images'])){
    			$images=$data['images'];
    			$imagArr=array();
    			foreach ($images as $valImg)
    			{
    				$imagArr[]=$valImg['src'];
    			}
    			$product_images=implode(',',$imagArr);
    		}
    		if(count($data['variants'])>1)
    		{
    			foreach ($data['variants'] as $value)
    			{
    				if($value['sku']=="")
    					continue;
    				$option_weight=0;
    				$option_price=0;
    				$option_price=(float)$value['price'];
    				/* if($priceType && $changePrice!=0){
    					$updatePrice=0;
    					$updatePrice=self::priceChange($option_price,$priceType,$changePrice);
    					if($updatePrice!=0)
    						$option_price = $updatePrice;
    				} */
    				$option_weight=$value['weight'];
    				$option_image_url="";
    				
    				if (isset($data['images']))
    				{
	    				foreach ($images as $value2)
	    				{
	    					if($value2['id']== $value['image_id'])
	    					{
	    						$option_image_url=$value2['src'];
	    					}
	    				}
    				}
    				$sql='INSERT INTO `jet_product_variants`(
	    						`option_id`,`product_id`,
	    						`merchant_id`,`option_title`,
	    						`option_sku`,`option_image`,
	    						`option_price`,`option_qty`,
	    						`variant_option1`,`variant_option2`,
	    						`variant_option3`,`vendor`,
	    						`option_unique_id`,`option_weight`
    						)VALUES(
	    						"'.$value['id'].'","'.$value['product_id'].'",
	    						"'.$merchant_id.'","'.addslashes($value['title']).'",
	    						"'.$value['sku'].'","'.addslashes($option_image_url).'",
	    						"'.(float)$option_price.'","'.(int)$value['inventory_quantity'].'",
	    						"'.addslashes($value['option1']).'","'.addslashes($value['option2']).'",
	    						"'.addslashes($value['option3']).'","'.addslashes($vendor).'",
	    						"'.$value['barcode'].'","'.$option_weight.'"
    						)';
    				$model = $connection->createCommand($sql)->execute();
    
    			}
    			//add attribute
    			$options=$data['options'];
    			$attrId=array();
    			foreach($options as $key=>$val){
    				$attrname=$val['name'];
    				$attrId[$val['id']]=$val['name'];
    				foreach ($val['values'] as $k => $v) {
    					$option_value[$attrname][$k]=$v;
    				}
    			}
    			$attr_id=json_encode($attrId);
    		}
    		$model="";
    		$product_price=0;
    		$product_price=(float)$data['variants'][0]['price'];
    		/* if($priceType && $changePrice!=0){
    			$updatePrice=0;
    			$updatePrice=self::priceChange($product_price,$priceType,$changePrice);
    			if($updatePrice!=0)
    				$product_price = $updatePrice;
    		} */
    		$weight=0;
    		$weight=$data['variants'][0]['weight'];
    		if($weight>0)
    		{
    			$weight=(float)Jetappdetails::convertWeight($weight,$data['variants'][0]['weight_unit']);
    		}
    		//add product into database
    		$new_product_flag=false;
    		$productmodel="";
    		$result="";
    		$productmodel = $connection->createCommand("SELECT * FROM `jet_product` WHERE id='".$product_id."'");
    		$result = $productmodel->queryOne();
    		if(!$result)
    		{
    			if(count($data['variants'])>1)
    			{
    				$sql='INSERT INTO `jet_product` 
    						(
	    						`id`,`merchant_id`,
	    						`title`,`sku`,
	    						`type`,`description`,
	    						`image`,`price`,
    							`qty`,`attr_ids`,
	    						`upc`,`status`,
	    						`vendor`,`variant_id`,
	    						`product_type`,`weight`
    						) 
    						VALUES 
    						(
    							"'.$data['id'].'","'.$merchant_id.'",
    							"'.addslashes($data['title']).'","'.addslashes($data['variants'][0]['sku']).'",
    							"variants","'.addslashes($data['body_html']).'",
    							"'.addslashes($product_images).'","'.(float)$product_price.'",
    							"'.(int)$data['variants'][0]['inventory_quantity'].'","'.addslashes($attr_id).'",
    							"'.addslashes($data['variants'][0]['barcode']).'","Not Uploaded",
    							"'.addslashes($data['vendor']).'","'.$data['variants'][0]['id'].'",
    							"'.addslashes($data['product_type']).'","'.$data['variants'][0]['weight'].'"
    						)';
    				$model = $connection->createCommand($sql)->execute();
    				$new_product_flag=true;
    			}
    			else
    			{
    				$sql='INSERT INTO `jet_product` 
    						(
	    						`id`,`merchant_id`,
	    						`title`,`sku`,
	    						`type`,`description`,
	    						`image`,`price`,
    							`qty`,`attr_ids`,
	    						`upc`,`status`,
	    						`vendor`,`variant_id`,
	    						`product_type`,`weight`
    						) 
    						VALUES 
    						(
    							"'.$data['id'].'","'.$merchant_id.'",
    							"'.addslashes($data['title']).'","'.addslashes($data['variants'][0]['sku']).'",
    							"simple","'.addslashes($data['body_html']).'",
    							"'.addslashes($product_images).'","'.(float)$product_price.'",
    							"'.(int)$data['variants'][0]['inventory_quantity'].'","",
    							"'.addslashes($data['variants'][0]['barcode']).'","Not Uploaded",
    							"'.addslashes($data['vendor']).'","'.$data['variants'][0]['id'].'",
    							"'.addslashes($data['product_type']).'","'.$data['variants'][0]['weight'].'"
    						)';
    				$model = $connection->createCommand($sql)->execute();
    				$new_product_flag=true;
    			}
    		}

    		$modelNew='';
    		if($data['product_type'])
    		{
    			$modelmap="";
    			$query="";
    			$queryObj="";
    			$query='SELECT category_id FROM `jet_category_map` where merchant_id="'.$merchant_id.'" AND product_type="'.$data['product_type'].'"';
    			$queryObj = $connection->createCommand($query);
    			$modelmap = $queryObj->queryOne();
    			//$modelmap=JetCategoryMap::find()->where(['merchant_id'=>$merchant_id,'product_type'=>$product_type])->one();
    			if($modelmap){
    				if($new_product_flag){
    					$updateResult="";
    					$query='UPDATE `jet_product` SET fulfillment_node="'.$modelmap['category_id'].'" where id="'.$data['id'].'"';
    					$updateResult = $connection->createCommand($query)->execute();
    				}
    			}else{
    		
    				$queryObj="";
    				$query='INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("'.$merchant_id.'","'.$data['product_type'].'")';
    				$queryObj = $connection->createCommand($query)->execute();
    			}
    		}
    		//inssert merchant products
    		$merchantProduct="";
    		$result="";
    		$merchantProduct = $connection->createCommand("SELECT * FROM `jet_merchant_products` WHERE product_id='".$data['id']."'");
    		$result = $merchantProduct->queryOne();
    		if(!$result)
    		{
    			$sql="";
    			$sql="INSERT INTO `jet_merchant_products`(`product_id`,`merchant_id`) VALUES ('".$data['id']."','".$merchant_id."')";
    			$model = $connection->createCommand($sql)->execute();
    		}
    		$queryObj="";
    		$modelU="";
    		$query="SELECT product_count,total_product FROM `insert_product` where merchant_id='".$merchant_id."'";
    		$queryObj = $connection->createCommand($query);
    		$modelU = $queryObj->queryOne();
    		if($modelU)
    		{
    			$updatedProduct=$countUpload=0;
    			$updatedProduct=$modelU['product_count']+1;
    			$countUpload=$modelU['total_product']+1;
    			
    			$query="UPDATE `insert_product` SET product_count='".$updatedProduct."',total_product='".$countUpload."' where merchant_id='".$merchant_id."' ";
    			$result = $connection->createCommand($query)->execute();
    		}
    	}
    	unset($data);
    	unset($images);
    	unset($imagArr);
    	unset($attrId);
    	unset($options);
    	unset($modelU);
    	unset($result);
    	return $message;
    }
    public static function checkProductOptionBarcodeOnUpdate($option_array=array(),$variant_array=array(),$variant_id="",$barcode_type="",$product_barcode="",$product_upc="",$product_id="",$product_sku="",$connection=array())
    {
    		//$collection=Yii::$app->getDb();
            $return_array=array();
            $return_array['success']=true;
            $return_array['error_msg']="";
            $variant_upc="";
            $variant_sku="";
            $err_msg="";
            $variant_upc=trim($variant_array['upc']);
            $variant_sku=trim($variant_array['optionsku']);
            $match_skus_array=array();
            $matched_flag=false;
            $db_matched_flag=false;
            $parent_matched_flag=false;
            $variant_as_parent=0;
            if($variant_sku==trim($product_sku)){
                $variant_as_parent=1;
            }
            foreach($option_array as $option_id=>$option_attributes){
                if(trim($option_attributes['optionsku'])!=$variant_sku){
                    if($variant_upc==trim($option_attributes['upc']) && trim($option_attributes['barcode_type'])==trim($barcode_type)){
                        $match_skus_array[]=trim($option_attributes['optionsku']);
                        $matched_flag=true;
                    }
                }
            }
            if($variant_as_parent!=1 && $product_upc==$variant_upc && $product_barcode==$barcode_type){
                $matched_flag=true;
                $parent_matched_flag=true;
            }
            if(!$matched_flag){
                $matched_flag=$db_matched_flag=self::checkUpcVariants($variant_upc,$product_id,$variant_id,$variant_as_parent,$connection);
            }
            if($matched_flag){
                    if(count($match_skus_array)>0){
                            $err_msg="Entered Barcode matched with Option Sku(s) : ".implode(' , ',$match_skus_array);
                    }
                    if($parent_matched_flag){
                        if($err_msg==""){
                            $err_msg="Entered Barcode matched with its Main Product";
                        }else{
                            $err_msg .=" & with its Main Product";
                        }
                    }
                    if($db_matched_flag){
                        $err_msg="Entered Barcode already exists";
                    }
                    $err_msg.=".Please enter unique Barcode.";
                    $return_array['success']=false;
                    $return_array['error_msg']=$err_msg;
            }

            return array($return_array['success'],$return_array['error_msg']);
    }

    public static function checkProductOptionAsinOnUpdate($option_array=array(),$variant_array=array(),$variant_id="",$product_asin="",$product_id="",$product_sku="",$product_collection=array(),$variant_collection=array())
    {
    		$collection=Yii::$app->getDb();
            $return_array=array();
            $return_array['success']=true;
            $return_array['error_msg']="";
            $variant_asin="";
            $variant_sku="";
            $err_msg="";
            $variant_asin=trim($variant_array['asin']);
            $variant_sku=trim($variant_array['optionsku']);
            $match_skus_array=array();
            $matched_flag=false;
            $db_matched_flag=false;
            $parent_matched_flag=false;
            $variant_as_parent=0;
            if($variant_sku==trim($product_sku)){
                $variant_as_parent=1;
            }
            foreach($option_array as $option_id=>$option_attributes){
                if(trim($option_attributes['optionsku'])!=$variant_sku){
                    if($variant_asin==trim($option_attributes['asin'])){
                        $match_skus_array[]=trim($option_attributes['optionsku']);
                        $matched_flag=true;
                    }
                }
            }
            if($variant_as_parent!=1 && $product_asin==$variant_asin){
                $matched_flag=true;
                $parent_matched_flag=true;
            }
            if(!$matched_flag){
                $matched_flag=$db_matched_flag=self::checkAsinVariants($variant_asin,$product_id,$variant_id,$variant_as_parent,$collection);
            }
            if($matched_flag){
                    if(count($match_skus_array)>0){
                            $err_msg="Entered ASIN matched with Option Sku(s) : ".implode(' , ',$match_skus_array);
                    }
                    if($parent_matched_flag){
                        if($err_msg==""){
                            $err_msg="Entered ASIN matched with its Main Product";
                        }else{
                            $err_msg .=" & with its Main Product";
                        }
                    }
                    if($db_matched_flag){
                        $err_msg="Entered ASIN already exists";
                    }
                    $err_msg.=".Please enter unique ASIN.";
                    $return_array['success']=false;
                    $return_array['error_msg']=$err_msg;
            }
            return array($return_array['success'],$return_array['error_msg']);
    } 
    public static function checkProductOptionMpnOnUpdate($option_array=array(),$variant_array=array(),$variant_id="",$product_mpn="",$product_id="",$product_sku="",$product_collection=array(),$variant_collection=array())
    {
        $collection=Yii::$app->getDb();
        $return_array=array();
        $return_array['success']=true;
        $return_array['error_msg']="";
        $variant_mpn="";
        $variant_sku="";
        $err_msg="";
        $variant_mpn=trim($variant_array['mpn']);
        $variant_sku=trim($variant_array['optionsku']);
        $match_skus_array=array();
        $matched_flag=false;
        $db_matched_flag=false;
        $parent_matched_flag=false;
        $variant_as_parent=0;
        if($variant_sku==trim($product_sku)){
            $variant_as_parent=1;
        }
        foreach($option_array as $option_id=>$option_attributes){
            if(trim($option_attributes['optionsku'])!=$variant_sku){
                if($variant_mpn==trim($option_attributes['mpn'])){
                    $match_skus_array[]=trim($option_attributes['optionsku']);
                    $matched_flag=true;
                }
            }
        }
        if($variant_as_parent!=1 && $product_mpn==$variant_mpn){
            $matched_flag=true;
            $parent_matched_flag=true;
        }
        if(!$matched_flag){
            $matched_flag=$db_matched_flag=self::checkAsinVariants($variant_mpn,$product_id,$variant_id,$variant_as_parent,$collection);
        }
        if($matched_flag){
            if(count($match_skus_array)>0){
                $err_msg="Entered MPN matched with Option Sku(s) : ".implode(' , ',$match_skus_array);
            }
            if($parent_matched_flag){
                if($err_msg==""){
                    $err_msg="Entered MPN matched with its Main Product";
                }else{
                    $err_msg .=" & with its Main Product";
                }
            }
            if($db_matched_flag){
                $err_msg="Entered MPN already exists";
            }
            $err_msg.=".Please enter unique MPN.";
            $return_array['success']=false;
            $return_array['error_msg']=$err_msg;
        }
        return array($return_array['success'],$return_array['error_msg']);
    }
    public static function checkUpcType($product_upc){
        if(is_numeric($product_upc))
        {
            if(strlen($product_upc)==12)
                return "UPC";
            elseif(strlen($product_upc)==10)
                return "ISBN";
            elseif(strlen($product_upc)==13)
                return "ISBN";
            elseif(strlen($product_upc)==14)
                return "GTIN";
        }
        return "";
    }
    public static function checkUpcSimple($product_upc="",$product_id="",$connection=array())
    {
    	if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $product_upc=trim($product_upc);
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        $variant_count=0;
        $queryObj="";
        $query="SELECT `id` FROM `jet_product` WHERE upc='".$product_upc."' AND id <> '".$product_id."'";
        $queryObj = $connection->createCommand($query);
        $main_products = $queryObj->queryAll();
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $query="SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='".$product_upc."'";
        $queryObj = $connection->createCommand($query);
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
                return true;
        }
        return false;
    }
    public static function checkUpcVariantSimple($product_upc="",$product_id="",$product_sku="",$connection=array())
    {
    	if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $connection = Yii::$app->getDb();
        $product_upc=trim($product_upc);
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        $variant_count=0;
        $queryObj="";
        $query="SELECT `id` FROM `jet_product` WHERE `upc`='".$product_upc."' AND `id`<>'".$product_id."'";
        $queryObj = $connection->createCommand($query);
        $main_products = $queryObj->queryAll();
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_sku <> '".$product_sku."' AND option_unique_id='".$product_upc."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
                return true;
        }
        return false;
    }
    public static function checkUpcVariants($product_upc="",$product_id="",$variant_id="",$variant_as_parent=0,$connection=array())
    {
    	if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $variant_count=0;
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        if($variant_as_parent){
        	$queryObj="";
        	$queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE upc='".trim($product_upc)."' AND id <> '".$product_id."'");
        	$main_products = $queryObj->queryAll();
        }else{
        	$queryObj="";
        	$queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE upc='".trim($product_upc)."'");
        	$main_products = $queryObj->queryAll();
        }
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_unique_id='".trim($product_upc)."' and option_id <>'".$variant_id."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
            //$msg['success']=true;
            return true;
        }
        return false;
    }

    public static function checkAsinSimple($product_asin="",$product_id="",$connection=array())
    {
    	if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $product_asin=trim($product_asin);
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        $variant_count=0;
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE ASIN='".$product_asin."' AND id <> '".$product_id."'");
        $main_products = $queryObj->queryAll();
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE asin='".$product_asin."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
            //$msg['success']=true;
            return true;
        }
        return false;
    }
    public static function checkAsinVariantSimple($product_asin="",$product_id="",$product_sku="",$connection=array())
    {
    	if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $product_asin=trim($product_asin);
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        $variant_count=0;
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE ASIN='".$product_asin."' AND id <> '".$product_id."'");
        $main_products = $queryObj->queryAll();
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_sku <> '".$product_sku."' AND asin='".$product_asin."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
            //$msg['success']=true;
            return true;
        }
        return false;
    }
    public static function checkAsinVariants($product_asin="",$product_id="",$variant_id="",$variant_as_parent=0,$connection=array())
    {
    	if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
    	$product_asin=trim($product_asin);
        $variant_count=0;
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        if($variant_as_parent){
        	$queryObj="";
        	$queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE ASIN='".trim($product_asin)."' AND id <> '".$product_id."'");
        	$main_products = $queryObj->queryAll();
        }else{
        	$queryObj="";
        	$queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE ASIN='".trim($product_asin)."'");
        	$main_products = $queryObj->queryAll();
        }
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE asin='".trim($product_asin)."' and option_id <> '".$variant_id."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
  		if($variant_count>0 || $main_product_count>0){
            //$msg['success']=true;
            return true;
        }
        return false;
    }
    public static function checkMpnSimple($product_mpn="",$product_id="",$connection=array())
    {
        if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $product_mpn=trim($product_mpn);
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        $variant_count=0;
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE mpn='".$product_mpn."' AND id <> '".$product_id."'");
        $main_products = $queryObj->queryAll();
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_mpn='".$product_mpn."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
            //$msg['success']=true;
            return true;
        }
        return false;
    }
    public static function checkMpnVariantSimple($product_mpn="",$product_id="",$product_sku="",$connection=array())
    {
        if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $product_mpn=trim($product_mpn);
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        $variant_count=0;
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE mpn='".$product_mpn."' AND id <> '".$product_id."'");
        $main_products = $queryObj->queryAll();
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_sku <> '".$product_sku."' AND option_mpn='".$product_mpn."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
        if($main_product_count > 0 || $variant_count > 0){
            //$msg['success']=true;
            return true;
        }
        return false;
    }
    public static function checkMpnVariants($product_mpn="",$product_id="",$variant_id="",$variant_as_parent=0,$connection=array())
    {
        if(!isset($connection)){
            $connection = Yii::$app->getDb();
        }
        $product_mpn=trim($product_mpn);
        $variant_count=0;
        $main_product_count=0;
        $main_products=array();
        $variant=array();
        if($variant_as_parent){
            $queryObj="";
            $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE mpn='".trim($product_mpn)."' AND id <> '".$product_id."'");
            $main_products = $queryObj->queryAll();
        }else{
            $queryObj="";
            $queryObj = $connection->createCommand("SELECT `id` FROM `jet_product` WHERE mpn='".trim($product_mpn)."'");
            $main_products = $queryObj->queryAll();
        }
        $main_product_count=count($main_products);
        unset($main_products);
        $queryObj="";
        $queryObj = $connection->createCommand("SELECT `option_id` FROM `jet_product_variants` WHERE option_mpn='".trim($product_mpn)."' and option_id <> '".$variant_id."'");
        $variant = $queryObj->queryAll();
        $variant_count= count($variant);
        unset($variant);
      		if($variant_count>0 || $main_product_count>0){
      		    //$msg['success']=true;
      		    return true;
      		}
      		return false;
    }
    public static function priceChange($price,$priceType,$changePrice){
    	$updatePrice=0;
    	if($priceType=="increase")
    		$updatePrice=(float)($price+($changePrice/100)*($price));
    	elseif($priceType=="decrease")
    		$updatePrice=(float)($price-($changePrice/100)*($price));
    	return $updatePrice;
    }

    public static function getConfigSettings($merchant_id, $connection=null)
    {
    	if(is_null($connection))
    		$connection = $connection = Yii::$app->getDb();

    	$config = [];
    	$jetConfig = $connection->createCommand('SELECT `data`,`value` from `jet_config` where merchant_id="'.$merchant_id.'"')->queryAll();
    	if (is_array($jetConfig) && count($jetConfig)>0){
			foreach ($jetConfig as $configValue) {
				$config[$configValue['data']] = $configValue['value'];
			}
			return $config;
		}
		else {
			return [];
		}
    }

    public static function getPriceToBeUpdatedOnJet($merchant_id, $price, $configSetting, $connection=null)
    {
		if(isset($configSetting['set_price_amount']) && $configSetting['set_price_amount']!='') {
			$priceFormat = explode('-', $configSetting['set_price_amount']);
			if(is_array($priceFormat) && count($priceFormat)==2) {
				$price = floatval($price);
				$format = $priceFormat[0];
				$value = floatval($priceFormat[1]);
				return self::priceChange($price, $format, $value);
			} else {
				return $price; 
			}
		} else {
			return $price;
		}
    }
}
?>