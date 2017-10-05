<?php
namespace frontend\modules\jet\components;
use Yii;
use yii\base\Component;
use function GuzzleHttp\json_encode;

class Productvalidator extends Component
{
	/*  
	 * Function to prepare raw data for sku upload
	 * */
	public static function collectData($data=[],$priceType="",$priceValue=0,&$arrSku,&$arrPrice,&$arrInv,&$arrVar,$merchant_id=NULL,&$err)
	{		
		$SKU_Array = $bullets = $images = $Attribute_arr = $Attribute_array = [];
		$type = '';
		if ($data['sku']=="tees23") 
		$SKU_Array['product_title']=$data['title'];
		$SKU_Array['jet_browse_node_id']=(int)$data['fulfillment_node'];		
		$brand = $data['vendor'];
		$mpn = $data['mpn'];
		$description = $data['description'];
		if(strlen($description)>2000)
			$description=Data::trimString($description, 2000);
		
		$SKU_Array['multipack_quantity']= (int)$data['pack_qty'];
		$SKU_Array['brand']=$brand;
		$SKU_Array['manufacturer']=$brand;		
		$SKU_Array['mfr_part_number']=$mpn;
		$SKU_Array['product_description']=$description;
		
		//$SKU_Array['bullets']=$bullets; // Send bullet points array to jet
		$Attribute_arr=json_decode($data['jet_attributes'],true);
		$attr_ids_arr=json_decode($data['attr_ids'],true);
		$images=explode(',',$data['image']);
		$kmain=0;
		if(!empty($images) && is_array($images) )
		{
			foreach($images as $key=>$value)
			{
				if($value=="")
					continue;
				$value = preg_replace( '~\s+~', '%20', $value);
				if(self::imageValidate($value)==true)
				{
					$kmain=$key;
					$SKU_Array['main_image_url']=$value;
					$SKU_Array['swatch_image_url']=$value;
					break;
				}
			}
		}
		if(!empty($images) && is_array($images) )
		{
			$i=1;
			foreach($images as $key=>$value)
			{
				if($key==$kmain)
					continue;
				if($i>8)
					break;
		
				$value = preg_replace( '~\s+~', '%20', $value);
				if($value!='' && self::imageValidate($value)==true)
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
		
		if ($data['type']=='simple')
		{
			$upc = $data['upc'];
			$type =Jetproductinfo::checkUpcType($upc);
			if(isset($upc,$type))
			{
				$unique['standard_product_code']=$upc;
				$unique['standard_product_code_type']=$type;
				$SKU_Array['standard_product_codes'][]=$unique;
			}
			if (trim($data['ASIN'])!="")
				$SKU_Array['ASIN'] = $data['ASIN'];
			$SKU_Array['mfr_part_number'] = $data['mpn'];
			
			// Weight must be greater than 0.01
			if ((float)$data['weight']>0.01)
				$SKU_Array['shipping_weight_pounds'] = (float)$data['weight'];
			
			if (is_array($Attribute_arr)&& count($Attribute_arr))
			{
				foreach ($Attribute_arr as $key =>$arr)
				{
					if(count($arr)==1)
					{
						$Attribute_array[] = array(
								'attribute_id'=>(int)$key,
								'attribute_value'=>$arr[0]
						);
					}
					elseif(count($arr)==2) // get value of text type with unit
					{
						$Attribute_array[] = array(
								'attribute_id'=>(int)$key,
								'attribute_value'=>$arr[0],
								'attribute_value_unit'=>$arr[1]
						);
					}
				} 
				if(count($Attribute_array)>0)
					$SKU_Array['attributes_node_specific'] = $Attribute_array; // add attributes details
			}			
			unset($Attribute_arr);
			
			/*  
			 * Validating prepared sku array
			 * */
			$validateRes = self::checkBeforeUpload($SKU_Array, "simple", $merchant_id);

			if($validateRes=='Unable to upload'){
				$err[] = $data['sku'];
				return false;
			}
			
			/*  
			 * Final Merchant sku preparation 
			 * */
				
				
			$arrSku[$data['sku']] = $SKU_Array;
			
			//$arrSku = $merchant_sku; 			
			
			/*  
			 * Prepare data for inventory array
			 * */
			$qty=0;
			$node1 = $inventory = $node = $price = [];
			if(is_numeric($data['qty']) && $data['qty']>0)
				$qty=$data['qty'];
			$node1['fulfillment_node_id']=FULLFILMENT_NODE_ID;
			$node1['quantity']=(int)$qty;
			$arrInv[$data['sku']]['fulfillment_nodes'][]=$node1; // inventory
			//$arrInv = $inventory;
			
			/*  
			 * Prepare Data for Price array
			 * */
			
			$newPriceValue=$data['price'];
			// change new price
			if($priceType !='' && $priceValue!=0)
			{
				$updatePrice=0.00;
				$updatePrice=Jetproductinfo::priceChange($newPriceValue,$priceType,$priceValue);
				if($updatePrice!=0)
					$newPriceValue = $updatePrice;
			}
			//$price[$data['sku']]['price']=(float)$newPriceValue;
			$arrPrice[$data['sku']]['price']=(float)$newPriceValue;
			$node['fulfillment_node_id']=FULLFILMENT_NODE_ID;
			$node['fulfillment_node_price']=(float)$newPriceValue;
			$arrPrice[$data['sku']]['fulfillment_nodes'][]=$node; //price
			
			//$arrPrice = $price; // Add Price array
		}
		else 
		{		
			$responseOption = $variationInfo = [];
			$responseOption=self::createoption($data,$SKU_Array,FULLFILMENT_NODE_ID,$arrSku,$arrPrice,$arrInv,$merchant_id,$err);	
			$arrVar[$data['sku']] = $responseOption;			
			//$arrVar = $variationInfo;
			//print_r($err);die;
		}
	}
		
    // is valid and Unique product UPC
	public static function upcValidate($product_upc="",$type="",$merchant_id="")
	{		 
		$allUpc = [] ;		
		if($type="simple"){
			$sqlQuery = "SELECT `id` FROM `jet_product` WHERE upc='".trim($product_upc)."' AND merchant_id='".$merchant_id."'";			
		}else{
			$sqlQuery = "SELECT `product_id` FROM `jet_product_variants` WHERE option_unique_id='".trim($product_upc)."' AND merchant_id='".$merchant_id."'";
			
		}
		$allUpc = Data::sqlRecords($sqlQuery,'all','select');
		if (empty($allUpc) || count($allUpc)>1)
			return 'invalid';
		else 
		{
			$upcLength = strlen($product_upc);
			if ( ( !($upcLength>10 || $upcLength<14) ) || ($upcLength==11) )
				return 'invalid';
		}
		return 'valid';		
	}	
	
	// is valid and Unique product ASIN 
	public static function AsinValidate($asin="",$type="",$merchant_id="")
	{
		$allASIN = [] ;
		if($type="simple")
			$sqlQuery = "SELECT `id` FROM `jet_product` WHERE  merchant_id='".$merchant_id."' AND ASIN='".trim($asin)."' ";
		else
			$sqlQuery = "SELECT `product_id` FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND asin='".trim($asin)."' ";
						
		$allASIN = Data::sqlRecords($sqlQuery,'all','select');
		if (trim($asin)=="" || empty($allASIN) || count($allASIN)>1)
			return 'invalid';
		else
		{
			if ( !(strlen($asin)==10 && ctype_alnum($asin)) )
				return 'invalid';
		}
		return 'valid';
	}
	
	// is valid and Unique product MPN 
	public static function mpnValidate($mpn="",$type="",$merchant_id="")
	{
		$allMPN = [] ;
		if($type="simple")
			$sqlQuery = "SELECT `id` FROM `jet_product` WHERE merchant_id='".$merchant_id."' AND mpn='".trim($mpn)."' ";
		else
			$sqlQuery = "SELECT `product_id` FROM `jet_product_variants` WHERE merchant_id='".$merchant_id."' AND option_mpn='".trim($mpn)."' ";
				
		$allMPN = Data::sqlRecords($sqlQuery,'all','select');
		if (trim($mpn)=="" || empty($allMPN) || count($allMPN)>1)
			return 'invalid';
		else
		{
			$mpnLength = strlen($mpn);
			if ( !($mpnLength > 0 && $mpnLength <= 50) )
				return 'invalid';
		}
		return 'valid';
	}
	
	// Validate Product Title
	public static function titleValidate($title="")
	{
		if(strlen($title)<5)		
			return 'invalid';
		
		if(strlen($title)>500)
			$title=Data::trimString($title, 500);
		return $title;
	}
	
	// Validate Product Description
	public static function descValidate($description="")
	{
		if(strlen($description)>2000)
			$description=Data::trimString($description, 2000);
		return $description;		
	}
	
	// Validate Product Image
	public static function imageValidate($imgUrl="")
	{
		stream_context_set_default([
				'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
				],
		]);
		$headers = get_headers($imgUrl); 		
		if(substr($headers[0], 9, 3) == '200') 
			return true;
		else
			return false;				
	}
	
	// Creating array for the variant products
	public static function createoption($product,&$SKU_Array,$fullfillmentnodeid,&$arrSku,&$arrPrice,&$arrInv,$merchant_id,&$err)
	{
		$session = Yii::$app->session;
		/*
		 *Variables initialization area
		*/
		$skuerr="";
		$status_code = $priceType = '';
		$priceValue = $product_qty = 0;
		$isParent = false;
		$options = $responseOptions = [];		
		$images=explode(',',$product['image']);
		// Chceking for custom price (increased in %age or Fixed)
		$priceType=unserialize($session['priceType']);
		$priceValue=unserialize($session['priceValue']);
		if (isset($session['target'])) {
			$target = $session['target'];
			$row = 1;
            $m = 0;
            $skus = [];
            if (($handle = fopen($target, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    $row++;
                    for ($c=0; $c < $num; $c++) {
                        if ($data[$c]=="Variant SKU") {
                            $m = $c;
                        }
                    }
                    if ($m!==0 && ($data[$m]!=="Variant SKU") && ($data[$m]!=="") ) 
                       $skus[] = $data[$m];
                    
                }
                fclose($handle);
            }
        }
		$query ="SELECT `option_id`,`option_title`,`option_sku`,`jet_option_attributes`,`option_image`,`option_qty`,`option_weight`,COALESCE(`update_option_price`,`option_price`) as `option_price`,`option_unique_id`,`asin`,`option_mpn`,`jet_option_attributes` FROM `jet_product_variants` WHERE `merchant_id`='".$merchant_id."' AND product_id='".$product['id']."'";
		$options = Data::sqlRecords($query,"all","select");
		if(is_numeric($product['qty']) && $product['qty']>0)
			$product_qty=$product['qty'];
		if(is_array($options) && count($options)>0)
		{	
			foreach($options as $val)
			{
				$isMapped = false;
				$unique = $Attribute_arr = $Attribute_array =  $price = $node = $node1 = $inventory = [];
				$asin = $upc = $mpn = $type = "";
				$sku=$val['option_sku'];
				
				if (isset($session['target'])) {
					if (!in_array($sku, $skus))
					{	
						$err[] = $sku;
						continue;
					}	
				}
				if($sku=="")
					continue;
				
				if (empty($val['option_unique_id'])) {
					$err[] = $sku;
						continue;
				}
				$upc = $val['option_unique_id'];
				$asin = $val['asin'];
				$mpn = $val['option_mpn'];

				$type=Jetproductinfo::checkUpcType($upc);

				if($product['sku']!=$sku)
				{
					$isParent=true;
					//$SKU_Array['product_title']=$product['title'].'-'.$val['option_title'];
					$responseOptions['children_skus'][]=$sku;
				}
				
				$attribute_opt=[];
				if(trim($product['jet_attributes'])!="")
				{
					$isMapped=true;
					$attribute_opt=json_decode($val['jet_option_attributes'],true);
					$Attribute_arr=json_decode($product['jet_attributes'],true);
				}
				else
				{
					$isMapped=false;
				}
				if (!$isParent) 
				{	
					if(isset($upc,$type))
					{
						$unique = [] ;
						$unique['standard_product_code']=$upc;
						$unique['standard_product_code_type']=$type;
						$SKU_Array['standard_product_codes'][]=$unique;
					}
					if (trim($asin)!="")
						$SKU_Array['ASIN']=$asin;
					$SKU_Array['mfr_part_number']=$mpn;
					if( (float)$val['option_weight']>=0.01)					
						$SKU_Array['shipping_weight_pounds']=(float)$val['option_weight'];		
					$parentMainImage = $SKU_Array['main_image_url'];	
					if($val['option_image']!="" && self::imageValidate($val['option_image'])==true)
					{						 
						$SKU_Array['main_image_url']=$val['option_image'];
						//$SKU_Array['swatch_image_url']=$val['option_image'];						
					}		
					$alternateImageRes = [];
					$alternateImageRes = Jetproductinfo::getAdditionalImages($images,$val['option_image'],$parentMainImage);
					if (isset($alternateImageRes['alternate_images']) && !empty($alternateImageRes['alternate_images']))
						$SKU_Array += $alternateImageRes;					
				}								
				
				if($isMapped)
				{
					foreach ($Attribute_arr as $key=>$value)
					{
						$attr_val="";
						if(is_array($attribute_opt) && array_key_exists($value[0],$attribute_opt)){
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
				}
				else
				{
					$mappedAttr = AttributeMap::getMappedOptionValues($val['option_id'], $product['attr_ids'], $product['product_type'], $merchant_id); 
					
					foreach ($mappedAttr as $k=>$map_val)
					{
						if(!isset($responseOptions['variation_refinements']))
						{
							$responseOptions['variation_refinements'][]=(int)$k;
						}
						$Attribute_array[] = [
							'attribute_id'=>(int)$k,
							'attribute_value'=>$map_val,
						];
					}
				}												
				if (!$isParent && !empty($Attribute_array)) 
					$SKU_Array['attributes_node_specific'] = $Attribute_array; // add attributes details
				
				$varResponse = ""; 
				
				$varResponse = self::checkBeforeUpload($SKU_Array, "variants", $merchant_id);
				
				if($varResponse=='Unable to upload'){
					$err[] = $val['option_sku'];
					return false;
				}
				
// 				echo $varResponse."=> ".$val['option_sku'];

				if ( !( isset($SKU_Array['ASIN']) || isset($SKU_Array['standard_product_codes']) || isset($SKU_Array['mfr_part_number']) ))	
					continue;
					
// 				echo "===>".$val['option_sku'];	
// 				die("<hr>");									
				$merchant_sku[$val['option_sku']] = $SKU_Array;
				$arrSku = $merchant_sku;
					
				$price = [];
				$optionPrice = 0.00;
				$optionPrice=(float)$val['option_price'];
				if($optionPrice=="" || $optionPrice == 0.00)
					$optionPrice=(float)$product['price'];
				
				// change new price
				if($priceType!="" && $priceValue!=0)
				{
					$updatePrice = 0.00;
					$updatePrice = Jetproductinfo::priceChange($optionPrice,$priceType,$priceValue);
					if($updatePrice!=0.00)
						$optionPrice = (float)$updatePrice;
				}

				$node['fulfillment_node_id']=$fullfillmentnodeid;
				$node['fulfillment_node_price']=$optionPrice;
				$price['price'] = $optionPrice ;
				$price['fulfillment_nodes'][]=$node;
						
				$priceArr[$val['option_sku']] = $price;
				$arrPrice =  $priceArr;
				
				$qty = 0;
				
				if(is_numeric($val['option_qty']) && $val['option_qty']>0 )					
					$qty=trim($val['option_qty']);
									
				$inventory = [];
				$node1['fulfillment_node_id']=$fullfillmentnodeid;
				$node1['quantity']=(int)$qty;
				$inventory['fulfillment_nodes'][]=$node1;	
				$m_inv[$val['option_sku']] = $inventory;
				$arrInv = $m_inv;				
			}	
			unset($options,$unique,$Attribute_arr,$Attribute_array,$price,$node,$node1,$inventory);
			$responseOptions['relationship']='Variation';	
		}
		return $responseOptions;
	}		

	// Create Json File for the Respected File type
	public static function createJsonFile($path,$t,$type, $data,$jetHelper,$merchant_id=NULL )
	{	
		$uploadArr = $ackResponse = $jetResponse = [];
		$file_name = $type.$t.".json";
		$file_path = $path."/".$file_name;
		
		$fileOrigSKU=fopen($file_path,'w');
		fwrite($fileOrigSKU, json_encode($data));
		fclose($fileOrigSKU);		
		$zipCreationResponse = self::gzCompressFile($file_path,9);
		/* $uploadArr['url'] = $zipCreationResponse;
		$uploadArr['file_type'] = $type;
		$uploadArr['file_name'] = $file_name; */
		$status = "";
		if ($zipCreationResponse)
		{
			/*  
			 * Requesting Jet for the zip file upload URL 
			 * */
			$jetResponse = $jetHelper->CGetRequest('/files/uploadToken',$merchant_id,$status);
			if ($status==200)
			{
				$zipUPloadResponse = $response = [];
				$response = json_decode($jetResponse,true);
				$sql = "INSERT INTO `jet_product_file_upload`(`merchant_id`, `local_file_path`,`file_name`, `file_type`, `file_url`, `expires_in_seconds`,`jet_file_id`) VALUES ('".$merchant_id."','".addslashes($file_path)."','".addslashes($file_name)."','".$type."','".addslashes($response['url'])."','".$response['expires_in_seconds']."','".$response['jet_file_id']."')";
				Data::sqlRecords($sql,null,'insert');
				
				/*  
				 * Uploading $type.json.gz file to Jet 
				 * */
				$jetHelper->uploadFile($zipCreationResponse,$response['url'],$merchant_id);											
			}
		}
		
		unlink($zipCreationResponse);
		unlink($file_path);
		return true;
		//return json_encode($uploadArr);
	}
	
	// 	compress all files inside $filepath directory
	public static function gzCompressFile($source, $level = 9)
	{
		$dest = $source.'.gz';
		$mode = 'wb'.$level;
		$error = false;
		if ($fp_out = gzopen($dest, $mode)) 
		{
			if ($fp_in = fopen($source,'rb')) 
			{
				while (!feof($fp_in))
					gzwrite($fp_out, fread($fp_in, 1024 * 512));
					fclose($fp_in);
			} 
			else 
			{
				$error = true;
			}
			gzclose($fp_out);
		} 
		else 
		{
			$error = true;
		}
		if ($error)
			return false;
		else
			return $dest;
	}
	
	// Product is being validated here (upc/asin/mpn/image/title/desc etc)
	public static function checkBeforeUpload(&$SKU_Array,$type="",$merchant_id)
	{
		$upcRes = $asinRes = $mpnRes = "";
		$imgRes = $imgAltRes= true;
		//$SKU_Array['product_title'] = self::titleValidate($SKU_Array['product_title']);
		$SKU_Array['product_description'] = self::descValidate($SKU_Array['product_description']);
		if (isset($SKU_Array['ASIN']) )
		{
			$asinRes = self::AsinValidate($SKU_Array['ASIN'],$type,$merchant_id);
			if ($asinRes=="invalid")
				unset($SKU_Array['ASIN']);
		}
		if (isset($SKU_Array['standard_product_codes']))
		{
			$upcRes = self::upcValidate($SKU_Array['standard_product_codes'][0]['standard_product_code'],$type,$merchant_id);
			if ($upcRes=="invalid")
				unset($SKU_Array['standard_product_codes']);
		}
		if (isset($SKU_Array['mfr_part_number']))
		{
			$mpnRes = self::mpnValidate($SKU_Array['mfr_part_number'],$type,$merchant_id);
			if ($mpnRes=="invalid")
				unset($SKU_Array['mfr_part_number']);
		}
	
		if (isset($SKU_Array['main_image_url']))
		{
			$imgRes = self::imageValidate($SKU_Array['main_image_url']);
			if (!$imgRes)
				unset($SKU_Array['main_image_url']);
		}
		if (isset($SKU_Array['alternate_images']) && count($SKU_Array['alternate_images']))
		{
			foreach ($SKU_Array['alternate_images'] as $key => $val)
			{
				$imgAltRes = self::imageValidate($val['image_url']);
				if (!$imgAltRes)
					unset($SKU_Array['alternate_images'][$key]);
			}
		}
		
		$validateRes = "";
// 		echo $upcRes.'=>'.$asinRes."=>".$mpnRes."=>"."<hr>";
		if ( ( ($upcRes=="invalid") && ($asinRes=="invalid" || $asinRes=="" ) && ($mpnRes=="invalid") ) || !isset($SKU_Array['main_image_url']) )		
			$validateRes = "Unable to upload"; 
		else
			$validateRes = "ready to upload";
		unset($asinRes,$mpnRes,$upcRes);
		return $validateRes;
	}
		/*Function to get price for all sku*/
	
	public static function priceSync($merchant_id,$arrPrice)
	{
		try 
		{
			$allSku = [];
			$sql = "SELECT option_price as price,option_sku as sku FROM jet_product_variants WHERE merchant_id=".$merchant_id." AND (status='Under Jet Review' OR status='Available For Purchase') UNION SELECT price,sku FROM jet_product WHERE merchant_id=".$merchant_id." AND (status='Under Jet Review' OR status='Available For Purchase')";
			
			$allSku = Data::sqlRecords($sql,"all","select");
			/*if (!empty($allSku))
			{
				
				foreach ($allSku as $value)
				{	
					self::createPriceArray($merchant_id,$arrPrice,$value);
					
				}	
				return $arrPrice;
			}			*/		
			return $allSku;	
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
	public static function createPriceArray($merchant_id,&$arrPrice,$products=[])
	{
		if (!empty($products))
		{
			foreach ($products as $val)
			{
				self::preparePriceArray($products['sku'], $products['price'] , $arrPrice);						
			}
		}
		return true;
	}
	
	//Prepare inv array
	public static function preparePriceArray($sku,$price,&$arrPrice)
	{
		$node1 = [];
		if(!is_numeric($price) ||  $price<1)
			$price=0;
		$pricenode['price'] = (float)$price;
		$arrPrice[$sku] = $pricenode;

		return $arrPrice;
	}
	/*  
	 * Function to get all sku and qty for perticular merchant 
	 * */
	public static function invSync($merchant_id,$fullfillmentnodeid,&$arrInv)
	{
		try 
		{
			$allSku = [];
			$sql = "SELECT option_qty as qty,option_sku as sku FROM jet_product_variants WHERE merchant_id=".$merchant_id." AND (status='Under Jet Review' OR status='Available For Purchase') UNION SELECT qty,sku FROM jet_product WHERE merchant_id=".$merchant_id." AND (status='Under Jet Review' OR status='Available For Purchase')";
			//$sql = "SELECT COALESCE(sku,option_sku) as sku, COALESCE(qty,option_qty) as qty FROM `jet_product` INNER JOIN jet_product_variants on jet_product.merchant_id=jet_product_variants.merchant_id WHERE jet_product.merchant_id=".$merchant_id." AND (jet_product.status='Under Jet Review' OR jet_product.status='Available for Purchase' OR jet_product_variants.status='Under Jet Review' OR jet_product_variants.status='Available for Purchase') GROUP BY sku";
			$allSku = Data::sqlRecords($sql,"all","select");
			if (!empty($allSku))
			{
				$chunkStatusArray=array_chunk($allSku, 10000);
			
				$totalProd = count($allSku);
				$totalPages = count($chunkStatusArray);
				foreach ($chunkStatusArray as $ind => $value)
				{
					self::createInvArray($merchant_id,$fullfillmentnodeid,$arrInv,$value);
				}				
				return $arrInv;
			}						
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
	//create inv array
	public static function createInvArray($merchant_id,$fullfillmentnodeid,&$arrInv,$products=[])
	{
		if (!empty($products))
		{
			foreach ($products as $key=>$val)
			{	
				self::prepareInvArray($val['sku'], $val['qty'], $fullfillmentnodeid, $arrInv);						
			}
		}
		return true;
	}
	
	//Prepare inv array
	public static function prepareInvArray($squ,$qty,$fullfillmentnodeid,&$arrInv)
	{
		$node1 = [];
		if(!is_numeric($qty) ||  $qty<1)
			$qty=0;
		$node1['fulfillment_node_id']=$fullfillmentnodeid;
		$node1['quantity']=(int)$qty;
		$arrInv[$squ]['fulfillment_nodes'][]=$node1;
		return $arrInv;
	}
	
	//create inv array
	public static function createArchiveArray($merchant_id,&$arrInv,$products=[])
	{
		if (!empty($products))
		{
			foreach ($products as $key=>$val)
			{
				self::prepareArchiveArray($val['sku'], $arrInv);
			}
		}
		return true;
	}
	
	//Prepare inv array
	public static function prepareArchiveArray($squ,&$arrInv)
	{
		
		$arrInv[$squ]=['is_archived'=>false];
		return $arrInv;					
	}
	
	/*  
	 * Request Jet for processing uploaded file
	 * */
	public static function fileProcessingRequest($jetHelper,$uploadArr,$merchant_id)
	{
		$status = $res = "";
		$res = $jetHelper->CPostRequest('/files/uploaded',json_encode($uploadArr),$merchant_id,$status);
		$ackResponse = json_decode($res,true);
		
		if ($status==200 && !empty($ackResponse) && !isset($ackResponse['error']))
		{
			$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$ackResponse['received']."',`status`='".$ackResponse['status']."' WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$ackResponse['jet_file_id']."' ";
			Data::sqlRecords($updateSql,null,'update');
			return $ackResponse['jet_file_id'];
		}
		return false;
	}	
	
	/*  
	 * Verify uploaded file
	 * */
	public static function verifyUploadedFile($jetHelper,$merchant_id,$jet_file_id)
	{
		$status = $res = "";
		$processedResponse = [];
		$res =  $jetHelper->CGetRequest('/files/'.$jet_file_id,$merchant_id,$status);
		$processedResponse = json_decode($res,true);
		 
		if ($status==200 && !empty($processedResponse) )
		{
			$sql1= "";
			if (isset($processedResponse['error_count']))
				$sql1.=",`error_count`='".$processedResponse['error_count']."'";
			if (isset($processedResponse['error_url']))
				$sql1.=",`error_url`='".addslashes($processedResponse['error_url'])."'";
			if (isset($processedResponse['total_processed']))
				$sql1 .= ",`total_processed`='".$processedResponse['total_processed']."'";
			if (isset($processedResponse['error_excerpt']))
				$sql1.=",`error_excerpt`='".addslashes(json_encode($processedResponse['error_excerpt']))."'";
			$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$processedResponse['received']."',`processing_start`='".$processedResponse['processing_start']."',`processing_end`='".$processedResponse['processing_end']."' ,`processing_status`='".$processedResponse['status']."' ".$sql1."  WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$processedResponse['jet_file_id']."' ";
			Data::sqlRecords($updateSql,null,'update');
			
			return "File Verified";
		}
	}
	
	/*  
	 * Get all the inventory files from DB for processing
	 * */
	public static function processAllInventoryJson($merchant_id,$jetHelper)
	{
		try 
		{
			$sql = "SELECT `file_url`,`file_name`,`jet_file_id`,`file_type` FROM `jet_product_file_upload` WHERE `merchant_id`='{$merchant_id}'  AND `status`='' ";
			$allFiles =  Data::sqlRecords($sql,"all",'select');
			
			
			if (!empty($allFiles))
			{
				foreach ($allFiles as $key=>$val)
				{
					$verifyFIle = false;
					$uploadArr = $ackResponse = [];
					$uploadArr['url'] = $val['file_url'];
					$uploadArr['file_type'] = $val['file_type'];
					$uploadArr['file_name'] = $val['file_name'];
					$jet_file_id = $val['jet_file_id'];
					$status = $res = "";
					$res = $jetHelper->CPostRequest('/files/uploaded',json_encode($uploadArr),$merchant_id,$status);
					$ackResponse = json_decode($res,true);
					if ($status==200 && !empty($ackResponse) && !isset($ackResponse['errors']))
					{
						$verifyFIle = true;
						$updateSql = "UPDATE `jet_product_file_upload` SET `received`='".$ackResponse['received']."',`status`='".$ackResponse['status']."' WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$ackResponse['jet_file_id']."' ";
					}
					else
					{
						$msg = "";
						if (preg_match('/File was already acknowledged/',$ackResponse['errors'][0])){
							$msg = ",`status` = 'Acknowledged' " ;
							$verifyFIle = true;
						}
						$updateSql = "UPDATE `jet_product_file_upload` SET `error`='".addslashes($ackResponse['errors'][0])."' ".$msg." WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='{$val['jet_file_id']}' ";
					}
					Data::sqlRecords($updateSql,null,'update');
					if ($verifyFIle)
					{
						$status = $res = "";
						$processedResponse = [];
						$res =  $jetHelper->CGetRequest('/files/'.$jet_file_id,$merchant_id,$status);
						$processedResponse = json_decode($res,true);
							
						if ($status==200 && !empty($processedResponse) )
						{
							$sql1= "";
							if (isset($processedResponse['processing_start']))
								$sql1.=",`processing_start`='".$processedResponse['processing_start']."'";
							if (isset($processedResponse['processing_end']))
								$sql1.=",`processing_end`='".$processedResponse['processing_end']."'";
							if (isset($processedResponse['error_count']))
								$sql1.=",`error_count`='".$processedResponse['error_count']."'";
							if (isset($processedResponse['error_url']))
								$sql1.=",`error_url`='".addslashes($processedResponse['error_url'])."'";
							if (isset($processedResponse['total_processed']))
								$sql1 .= ",`total_processed`='".$processedResponse['total_processed']."'";
							if (isset($processedResponse['error_excerpt']))
								$sql1.=",`error_excerpt`='".addslashes(json_encode($processedResponse['error_excerpt']))."'";
							$updateSql1 = "UPDATE `jet_product_file_upload` SET `received`='".$processedResponse['received']."',`total_processed`='".$processedResponse['total_processed']."',`status`='".$processedResponse['status']."' ".$sql1."  WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$processedResponse['jet_file_id']."' ";
							Data::sqlRecords($updateSql1,null,'update');
						}
					}
				}
			}
			return true;
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	/*
	 * Verify all the acknowledged files
	 * */
	public static function verifyAllInventoryJson($merchant_id,$jetHelper)
	{
		try 
		{
			$sql = "SELECT `file_url`,`file_name`,`jet_file_id`,`file_type` FROM `jet_product_file_upload` WHERE `merchant_id`='{$merchant_id}'  AND `status`='Acknowledged'  ";
			$allFiles =  Data::sqlRecords($sql,"all",'select');
			$msg = "";
			if (!empty($allFiles))
			{
				foreach ($allFiles as $key=>$val)
				{
					$status = $res = $updateSql1 = $jet_file_id = "";
					$jet_file_id = $val['jet_file_id'];
					
					$processedResponse = [];
					$res =  $jetHelper->CGetRequest('/files/'.$jet_file_id,$merchant_id,$status);
					$processedResponse = json_decode($res,true);
					
					if ($status==200 && !empty($processedResponse) )
					{
						$sql1= "";
						if (isset($processedResponse['processing_start']))
							$sql1.=",`processing_start`='".$processedResponse['processing_start']."'";
						if (isset($processedResponse['processing_end']))
							$sql1.=",`processing_end`='".$processedResponse['processing_end']."'";
						if (isset($processedResponse['error_count']))
							$sql1.=",`error_count`='".$processedResponse['error_count']."'";
						if (isset($processedResponse['error_url']))
							$sql1.=",`error_url`='".addslashes($processedResponse['error_url'])."'";
						if (isset($processedResponse['error_excerpt']))
							$sql1.=",`error_excerpt`='".addslashes(json_encode($processedResponse['error_excerpt']))."'";
						$updateSql1 = "UPDATE `jet_product_file_upload` SET `received`='".$processedResponse['received']."',`total_processed`='".$processedResponse['total_processed']."',`status`='".$processedResponse['status']."' ".$sql1."  WHERE `merchant_id`='".$merchant_id."' AND `jet_file_id`='".$processedResponse['jet_file_id']."' ";
						Data::sqlRecords($updateSql1,null,'update');
					}
					$msg .= $merchant_id.'::'.$status."::".$updateSql1."<hr>";										
				}
			}
			return $msg;
		}catch(Exception $e)
		{
			return $e->getMessage();
		}
	}

	/*  
	 * Function to get all sku for perticular merchant to archive 
	 * */
	public static function unarchiveSku($merchant_id,$jetHelper)
	{
		try 
		{
			$allSku = [];
			$sql = "SELECT `sku` FROM `jet_product_not_in_app` WHERE merchant_id=".$merchant_id;
			$allSku = Data::sqlRecords($sql,"all","select");
			if (!empty($allSku))
			{
				$chunkStatusArray=array_chunk($allSku, 10000);
			
				$totalProd = count($allSku);
				$totalPages = count($chunkStatusArray);
				foreach ($chunkStatusArray as $ind => $value)
				{
					$arrUnarchive = [];
					self::createArchiveArray($merchant_id,$arrUnarchive,$value);
					if(!empty($arrUnarchive))
						self::uploadFilesToJet($merchant_id,"Archive",$arrUnarchive,$jetHelper);	
					unset($arrUnarchive);
				}				
				return true;
			}						
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	/*  
	 * */
	
	public static function uploadFilesToJet($merchant_id,$fileType,$dataArray,$jetHelper)
	{
		$file_path = Yii::getAlias('@webroot').'/var/jet/file-upload/'.$merchant_id.'/jetupload/'.$fileType;
		if(!file_exists($file_path)){
			mkdir($file_path,0775, true);
		}
		
		$t=time()+rand(2,5);
		if (!empty($dataArray)){
			$uploadArr = self::createJsonFile($file_path,$t,$fileType, $dataArray,$jetHelper,$merchant_id);			
		}
	}
	
}