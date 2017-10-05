<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\models\WalmartProduct;
use frontend\modules\walmart\components\Jetappdetails;
use frontend\modules\walmart\components\Walmartapi;
use frontend\modules\walmart\components\WalmartCategory;
use frontend\modules\walmart\components\WalmartRepricing;
use frontend\modules\walmart\components\ShopifyClientHelper;

class WalmartscriptController extends WalmartmainController
{
	const MAX_SHORT_DESCRIPTION = 1000;
	const MAX_SHELF_DESCRIPTION = 1000;
	const MAX_LONG_DESCRIPTION = 4000;

	public function actionDeleteproduct()
    {
    	$product_ids = Yii::$app->request->post('product_ids',false);
    	$retire = Yii::$app->request->post('retire');
    	if(!is_array($product_ids))
    		$product_ids = explode(',', $product_ids);

		if($product_ids && count($product_ids))
		{
			$merchant_id = MERCHANT_ID;

            try {
            	$walmartApi = new Walmartapi(API_USER, API_PASSWORD, CONSUMER_CHANNEL_TYPE_ID);

            	$errors = [];
            	foreach ($product_ids as $product_id) {
            		$productData = WalmartRepricing::getProductData($product_id);

            		if($productData && isset($productData['type']))
            		{
            			if($productData['type'] == 'simple')
            			{
            				$deleteProductFlag = false;
            				if($retire && $productData['status'] != WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED)
            				{
            					$sku = $productData['sku'];
	            				$feed_data = [];
	            				//$feed_data = $walmartApi->retireProduct($sku);

			                    if(isset($feed_data['ItemRetireResponse']))
			                    {
			                        $deleteProductFlag = true;
			                    }
			                    elseif (isset($feed_data['errors']['error']))
			                    {
			                        if(isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku")
			                        {
			                            $errors[$sku][] = $sku.' : Product not Uploaded on Walmart.';
			                        }
			                        else
			                        {
			                            $errors[$sku][] = $sku.' : '.$feed_data['errors']['error']['description'];
			                        }
			                    } 
		                	} 
		                	else 
		                	{
		                		$deleteProductFlag = true;
		                	}

		                	if($deleteProductFlag) {
            					$deleteQuery = "DELETE FROM `jet_product` WHERE `product_id`='{$product_id}'";
	    						//Data::sqlRecords($deleteQuery, null, 'delete');
            				}
            			}
            			elseif($productData['type'] == 'variants')
            			{
            				$productVariants = WalmartRepricing::getProductVariants($product_id);
            				if($productVariants)
            				{
            					if($retire)
            					{
            						$variantErr = [];
            						$deleteProductFlag2 = true;
	            					foreach ($productVariants as $variant) {
	            						$sku = $variant['option_sku'];

	            						if($variant['status']!=WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED)
	            						{
		            						$feed_data = [];
		            						//$feed_data = $walmartApi->retireProduct($sku);

						    				if(isset($feed_data['ItemRetireResponse']))
						                    {
						                        continue;
						                    }
						                    elseif (isset($feed_data['errors']['error']))
						                    {
						                        if(isset($feed_data['errors']['error']['code']) && $feed_data['errors']['error']['code'] == "CONTENT_NOT_FOUND.GMP_ITEM_INGESTOR_API" && $feed_data['errors']['error']['field'] == "sku")
						                        {
						                        	//product not uploaded, so it can not be retired.
						                            continue;
						                        }
						                        else
						                        {
						                            $variantErr[] = $sku.' : '.$feed_data['errors']['error']['description'];
						                            $deleteProductFlag2 = false;
						                            break;
						                        }
						                    }
					                	}
	            					}

	            					if(count($variantErr)) {
	            						$errors[$productData['sku']] = implode(',', pieces);
	            					}
	            					elseif($deleteProductFlag2)
	            					{
		            					//self::deleteProduct(['id'=>$product_id],true);
	            						
	            						$deleteQuery = "DELETE FROM `jet_product` WHERE `product_id`='{$product_id}'";
		    							//Data::sqlRecords($deleteQuery, null, 'delete');
            						}
            					}
            					else
            					{
            						//self::deleteProduct(['id'=>$product_id],true);

            						$deleteQuery = "DELETE FROM `jet_product` WHERE `product_id`='{$product_id}'";
	    							//Data::sqlRecords($deleteQuery, null, 'delete');
            					}
            				}
            				else
            				{
            					$errors[$productData['sku']] = "no variants found for this product.";
            				}
            			}
            		}
            	}
            	if(count($errors))
            		return json_encode(['error'=>true, 'message'=>implode(',', $errors)]);
            	else
            		return json_encode(['success'=>true, 'message'=>"Product(s) Deleted Successfully!!"]);
    		} catch(Exception $e) {
    			return json_encode(['error'=>true, 'message'=>"Error : ".$e->getMessage()]);
    		}
		}
		else
		{
			return json_encode(['error'=>true, 'message'=>"No product selected for delete."]);
		}
    }

	public function actionShopifyproductsync()
    {
    
    	//comma seperated product ids
        //$product_ids = '9678336780,9677379724,9622625740';

        $product_ids = Yii::$app->request->post('product_id',false);

		if($product_ids && strlen($product_ids))
		{
            $merchant_id = MERCHANT_ID;
            $shopname = SHOP;
            $token = TOKEN;

            try 
    		{
				$sc = new ShopifyClientHelper($shopname, $token, WALMART_APP_KEY, WALMART_APP_SECRET);

	           	$products = $sc->call('GET', '/admin/products.json?ids='.$product_ids, array());
	           	//$products = $sc->call('GET', '/admin/products.json', array());
	           	//print_r($products);die;
	            if ($products && is_array($products)) 
	            {
	                foreach ($products as $value) 
	                {
	                	$product_sku = $value['variants'][0]['sku'];

	                	if ($product_sku == "") {
	                        continue;
	                    }

	                    //delete variant products
	                    self::deleteProduct($value);

	                    $product_id = $value['id'];
	                    $vendor = addslashes($value['vendor']);
	                    $product_price = $value['variants'][0]['price'];
	                    $barcode = $value['variants'][0]['barcode'];
	                    $product_qty = $value['variants'][0]['inventory_quantity'];
	                    $product_type = $value['product_type'];
	                    $title = addslashes($value['title']);
	                    $type = 'simple';
	                    $description = addslashes($value['body_html']);
	                    $variant_id = $value['variants'][0]['id'];
	                    $image = self::getImplodedImages($value['images']);

	                    $weight_unit = $value['variants'][0]['weight_unit'];
	                	$product_weight = 0;
	                	if($value['variants'][0]['weight'] > 0) {
					    	$product_weight = (float)Jetappdetails::convertWeight($value['variants'][0]['weight'],$weight_unit);
					    }

					    $status = WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED;

					    $fulfillment_node = 0;
					    $category = '';

					    if(isset($value['product_type']) && !is_null($value['product_type']) && $value['product_type']!='')
			    		{
			    		 	$query = 'SELECT category_id FROM `jet_category_map` where merchant_id="'.$merchant_id.'" AND product_type="'.addslashes($value['product_type']).'" LIMIT 0,1';
			    		 	$modelmap = Data::sqlRecords($query,'one','select');
			    		 	if($modelmap) 
			    		 	{
			    		 		$fulfillment_node = $modelmap['category_id'];
			    		 	} 
			    		 	else 
			    		 	{
								$query = 'INSERT INTO `jet_category_map`(`merchant_id`,`product_type`)VALUES("'.$merchant_id.'","'.addslashes($value['product_type']).'")';
								Data::sqlRecords($query, null, 'insert');
							}

			                $query = 'SELECT id,category_id FROM `walmart_category_map` where merchant_id="'.$merchant_id.'" AND product_type="'.addslashes($value['product_type']).'" LIMIT 0,1';
			                $walmodelmap = Data::sqlRecords($query, 'one', 'select');
			                if($walmodelmap)
			                {
			                	$category = $walmodelmap['category_id'];
			                }
			                else
			                {
			                    $query = 'INSERT INTO `walmart_category_map`(`merchant_id`,`product_type`) VALUES("'.$merchant_id.'","'.addslashes($value['product_type']).'")';
			                    Data::sqlRecords($query, null, 'insert');
			                }
			    		}
	                    
	                    /* save variants start */
	                    $variants = $value['variants'];
	                    if (count($variants) > 1 && is_array($variants)) 
	                    {
	                    	$type = 'variants';
	                        foreach ($variants as $variant)
	                        {
	                            $option_id = $variant['id'];
	                            $option_title = addslashes($variant['title']);
	                            $option_sku = addslashes($variant['sku']);

	                            $option_image = '';
	                            if(!is_null($variant['image_id'])) {
	                            	$image_array = self::getImage($value['images'],$variant['image_id']);
	                            	$option_image = addslashes($image_array['src']);
	                        	}

	                        	$option_weight = 0;
	                        	$weight_unit = $variant['weight_unit'];
	                        	if($variant['weight'] > 0) {
							    	$option_weight = (float)Jetappdetails::convertWeight($variant['weight'],$weight_unit);
							    }	

	                            $option_price = $variant['price'];
	                            $option_qty = $variant['inventory_quantity'];
	                            $option_barcode = $variant['barcode'];

	                            $variant_option1 = addslashes($variant["option1"]);
						        $variant_option2 = addslashes($variant["option2"]);
						        $variant_option3 = addslashes($variant["option3"]);

						        //save data in `jet_product_variants`
	                            $result = Data::sqlRecords("SELECT * FROM `jet_product_variants` WHERE option_id='" . $option_id . "' LIMIT 0,1", "one", "select");
	                            if (!$result)
	                            {
	                            	//variant doesn't exist
	                            	$sql = "INSERT INTO `jet_product_variants`(`option_id`, `product_id`, `merchant_id`, `option_title`, `option_sku`, `jet_option_attributes`, `option_image`, `option_qty`, `option_weight`, `option_price`, `option_unique_id`,`variant_option1`, `variant_option2`, `variant_option3`, `vendor`) VALUES ({$option_id},{$product_id},{$merchant_id},'{$option_title}','{$option_sku}',NULL,'{$option_image}','{$option_qty}','{$option_weight}','{$option_price}','{$option_barcode}','{$variant_option1}','{$variant_option2}','{$variant_option3}','{$vendor}')";

	                                Data::sqlRecords($sql, null, "insert");
	                            }
	                        	else
	                            {
	                            	//variant exist
	                                $sql = "UPDATE `jet_product_variants` SET ";

	                                $updateVariant = [];

	                                if($result['option_title'] != $option_title)
	                                	$updateVariant['option_title'] = $option_title;

	                                if($result['option_sku'] != $option_sku)
	                                	$updateVariant['option_sku'] = $option_sku;

	                                if($result['option_image'] != $option_image)
	                                	$updateVariant['option_image'] = $option_image;

	                                if($result['option_qty'] != $option_qty)
	                                	$updateVariant['option_qty'] = $option_qty;

	                                if($result['option_weight'] != $option_weight)
	                                	$updateVariant['option_weight'] = $option_weight;

	                                if($result['option_price'] != $option_price)
	                                	$updateVariant['option_price'] = $option_price;
	                                
	                                if($result['option_unique_id'] != $option_barcode)
	                                	$updateVariant['option_unique_id'] = $option_barcode;
	                                
	                                if($result['variant_option1'] != $variant_option1)
	                                	$updateVariant['variant_option1'] = $variant_option1;
	                                
	                                if($result['variant_option2'] != $variant_option2)
	                                	$updateVariant['variant_option2'] = $variant_option2;
	                                
	                                if($result['variant_option3'] != $variant_option3)
	                                	$updateVariant['variant_option3'] = $variant_option3;
	                                
	                                if($result['vendor'] != $vendor)
	                                	$updateVariant['vendor'] = $vendor;

	                                $flag = false;
	                                foreach ($updateVariant as $updateKey=>$updateVal) {
	                                	if(!$flag)
	                                		$flag = true;
	                                	$sql .= "`$updateKey`='{$updateVal}',";
	                                }
	                                $sql = rtrim($sql,',');

	                                $sql .= " WHERE `option_id`='".$option_id."' AND `merchant_id`='".$merchant_id."'";
	                                if($flag)
	                                	Data::sqlRecords($sql, null, "update");
	                            }

	                            //Insert Data Into `walmart_product_variants`
			                    $walresult = Data::sqlRecords("SELECT * FROM `walmart_product_variants` WHERE option_id='".$option_id."' LIMIT 0,1", 'one', 'select');
			                    if(!$walresult)
			                    {
			                        $sql = "INSERT INTO `walmart_product_variants`(
			                                `option_id`,`product_id`,`merchant_id`,`status`,`new_variant_option_1`,`new_variant_option_2`,`new_variant_option_3`
			                                )
			                                VALUES('".$option_id."','".$product_id."','".$merchant_id."','".WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED."','".$variant_option1."','".$variant_option2."','".$variant_option3."')";
			                        Data::sqlRecords($sql, null, 'insert');
			                    }
			                    else
			                    {
			                    	$sql = "UPDATE `walmart_product_variants` SET ";

			                    	$updateVariant = [];

			                    	if($walresult['new_variant_option_1'] != $variant_option1)
	                                	$updateVariant['new_variant_option_1'] = $variant_option1;
	                                
	                                if($walresult['new_variant_option_2'] != $variant_option2)
	                                	$updateVariant['new_variant_option_2'] = $variant_option2;
	                                
	                                if($walresult['new_variant_option_3'] != $variant_option3)
	                                	$updateVariant['new_variant_option_3'] = $variant_option3;

	                                $flag = false;
	                                foreach ($updateVariant as $updateKey=>$updateVal) {
	                                	if(!$flag)
	                                		$flag = true;
	                                	$sql .= "`$updateKey`='{$updateVal}',";
	                                }
	                                $sql = rtrim($sql,',');

	                                $sql .= " WHERE `option_id`='".$option_id."' AND `merchant_id`='".$merchant_id."'";
	                                if($flag)
	                                	Data::sqlRecords($sql, null, "update");
			                    }
	                        }

	                    	//add attribute
		                    $attrId = [];
			    			$options = $value['options'];
			    			foreach($options as $val){
			    				$attrId[$val['id']] = $val['name'];
			    			}
			    			$attr_id = addslashes(json_encode($attrId));
	                    }
	                    else
	                    {
	                    	$attr_id = addslashes(Data::getOptionValuesForSimpleProduct($value));
	                    }
	                    /* save variants end */

	                    /* save main product start */
	                    //save in `jet_product` table
	                    $result = Data::sqlRecords("SELECT * FROM `jet_product` WHERE id='".$product_id."' LIMIT 0,1", "one", "select");
	                    if (!$result) 
	                    {
	                    	//product doesn't exist
	                    	$sql = "INSERT INTO `jet_product`(`id`, `merchant_id`, `title`, `sku`, `type`, `product_type`, `description`, `variant_id`, `image`, `qty`, `weight`, `price`, `attr_ids`, `jet_attributes`, `vendor`, `upc`, `fulfillment_node`, `status`) VALUES ('{$product_id}','{$merchant_id}','{$title}','{$product_sku}','{$type}','{$product_type}','{$description}','{$variant_id}','{$image}','{$product_qty}','{$product_weight}','{$product_price}','{$attr_id}',NULL,'{$vendor}','{$barcode}','{$fulfillment_node}','{$status}')";

	                    	Data::sqlRecords($sql, null, "insert");
	                    }
	                    else
	                    {
	                    	//product exist
	                    	$sql = "UPDATE `jet_product` SET ";

	                    	$updateProduct = [];

	                    	if($result['title'] != $title)
	                    		$updateProduct['title'] = $title;

	                    	if($result['sku'] != $product_sku)
	                    		$updateProduct['sku'] = $product_sku;

	                    	if($result['product_type'] != $product_type)
	                    		$updateProduct['product_type'] = $product_type;

	                    	if($result['type'] != $type) {
	                    		$updateProduct['type'] = $type;

	                    		if($result['type'] == 'variants') {
	                    			self::deleteProduct($value,true);
	                    		}
	                    	}

	                    	if($result['description'] != $description)
	                    		$updateProduct['description'] = $description;

	                    	if($result['variant_id'] != $variant_id)
	                    		$updateProduct['variant_id'] = $variant_id;

	                    	if($result['image'] != $image)
	                    		$updateProduct['image'] = $image;

	                    	if($result['qty'] != $product_qty)
	                    		$updateProduct['qty'] = $product_qty;

	                    	if($result['weight'] != $product_weight)
	                    		$updateProduct['weight'] = $product_weight;

	                    	if($result['price'] != $product_price)
	                    		$updateProduct['price'] = $product_price;

	                    	if($result['attr_ids'] != $attr_id)
	                    		$updateProduct['attr_ids'] = $attr_id;

	                    	if($result['vendor'] != $vendor)
	                    		$updateProduct['vendor'] = $vendor;

	                    	if($result['upc'] != $barcode)
	                    		$updateProduct['upc'] = $barcode;

	                    	if($result['fulfillment_node'] != $fulfillment_node)
	                    		$updateProduct['fulfillment_node'] = $fulfillment_node;

	                    	$flag = false;
	                        foreach ($updateProduct as $updateKey=>$updateVal) {
	                        	if(!$flag)
	                        		$flag = true;
	                        	$sql .= "`$updateKey`='{$updateVal}',";
	                        }
	                        $sql = rtrim($sql,',');

	                    	$sql .= " WHERE `id`='".$product_id ."' AND `merchant_id`='".$merchant_id."'";

	                    	if($flag)
	                        	Data::sqlRecords($sql, null, "update");
	                    }

	                    //save in `walmart_product` table
						$walresult = Data::sqlRecords("SELECT * FROM `walmart_product` WHERE product_id='".$product_id."' LIMIT 0,1", 'one', 'select');
						if(!$walresult)
						{
							$sql = "INSERT INTO `walmart_product` (`product_id`,`merchant_id`,`status`,`product_type`,`category`) VALUES ('".$product_id."','".$merchant_id."','".WalmartProduct::PRODUCT_STATUS_NOT_UPLOADED."','".$product_type."','".$category."')";
							Data::sqlRecords($sql, null, 'insert');
						}
						else
						{
							$sql = "UPDATE `walmart_product` SET ";

	                    	$updateProduct = [];

	                    	if($walresult['product_type'] != $product_type)
	                        	$updateProduct['product_type'] = $product_type;
	                        
	                        if($walresult['category'] != $category)
	                        	$updateProduct['category'] = $category;

	                        $flag = false;
	                        foreach ($updateProduct as $updateKey=>$updateVal) {
	                        	if(!$flag)
	                        		$flag = true;
	                        	$sql .= "`$updateKey`='{$updateVal}',";
	                        }
	                        $sql = rtrim($sql,',');

	                        $sql .= " WHERE `product_id`='".$product_id."' AND `merchant_id`='".$merchant_id."'";
	                        if($flag)
	                        	Data::sqlRecords($sql, null, "update");
						}
						/* save main product end */
	                }
	            }
	            else
	            {
	            	return json_encode(['error'=>true, 'message'=>"Product doesn't exist on Shopify."]);
	            }
			} 
			catch (Exception $e)
			{
				return json_encode(['error'=>true, 'message'=>"Error : ".$e->getMessage()]);
			}
        }
        else 
        {
        	return json_encode(['error'=>true, 'message'=>"No product selected for sync."]);
        }

      	return json_encode(['success'=>true, 'message'=>'Product Synced Successfully!!']);
    }

    public static function getImage($images, $image_id)
    {
    	if(count($images))
    	{
    		foreach ($images as $image) {
    			if($image['id'] == $image_id) {
    				return $image;
    			}
    		}
    	}
    	return ['src'=>''];
    }

    public static function getImplodedImages($images)
    {
    	$img_arr = [];
    	if(count($images))
    	{
    		foreach ($images as $image) {
    			$img_arr[] = $image['src']; 
    		}
    	}
    	return implode(',', $img_arr);
    }

    public static function deleteProduct($product,$all=false)
    {
    	if(is_array($product) && count($product))
    	{
    		$product_id = $product['id'];

    		if($all)
    		{
    			$deleteQuery = "DELETE FROM `jet_product_variants` WHERE `product_id`='{$product_id}'";
	    		return Data::sqlRecords($deleteQuery, null, 'delete');
    		}
    		elseif(!$all)
    		{
	    		$variants = Data::sqlRecords("SELECT `option_id` FROM `jet_product_variants` WHERE `product_id`='{$product_id}'", 'all', 'select');

	    		if($variants)
	    		{
	    			$current_variants = [];
	    			foreach ($variants as $variant) {
	    				$current_variants[] = $variant['option_id'];
	    			}

	    			$new_variants = [];
	    			foreach ($product['variants'] as $value) {
	    				$new_variants[] = $value['id'];
	    			}

	    			$productsToDelete = array_diff($current_variants, $new_variants);

	    			if(count($productsToDelete)) {
	    				$deleteQuery = "DELETE FROM `jet_product_variants` WHERE `option_id` IN (".implode(',', $productsToDelete).")";
	    				return Data::sqlRecords($deleteQuery, null, 'delete');
	    			}
	    		}
    		}
    	}
    	return false;
    }

    /**
	 *	Import products,product_types from jet_product,jet_product_variants tables
	 */
	public function actionIndex()
    {
    	$merchant_id = '';

    	$query = "SELECT `product_id` FROM `walmart_product`";

    	if($merchant_id != '')
    		$query .=  " WHERE merchant_id=".$merchant_id;

		$walmart_data  = Data::sqlRecords($query, "all", "select");

		$walmart_skus = '';
		if($walmart_data && is_array($walmart_data) && count($walmart_data))
		{
			foreach ($walmart_data as $key=>$_walmart) {
				$walmart_skus .= $_walmart['product_id'];
				if(isset($walmart_data[$key+1]))
					$walmart_skus .= ',';
			}
		}

		$query = "SELECT * FROM `jet_product`";
		if($walmart_skus != '')
		{
			$query .= " WHERE `id` NOT IN (".$walmart_skus.")";

			if($merchant_id != '')
    			$query .=  " AND merchant_id=".$merchant_id;
    	}
    	else
    	{
    		if($merchant_id != '')
    			$query .=  " WHERE merchant_id=".$merchant_id;
    	}

		$jet_data  = Data::sqlRecords($query, "all", "select");
		if($jet_data && is_array($jet_data) && count($jet_data))
		{
			$insert_data = [];
			foreach ($jet_data as $jet_product) {
				$value_str = "(";
				$value_str .= $jet_product['id'].",";//product_id
				$value_str .= $jet_product['merchant_id'].",";//merchant_id
				$value_str .= "'".addslashes($jet_product['product_type'])."',";//product_type
				$value_str .= "'',";//category
				$value_str .= "'',";//tax_code
				//$value_str .= $jet_product[''].',';//min_price
				$value_str .= "'".addslashes(self::getData($jet_product['description'], self::MAX_SHORT_DESCRIPTION))."',";//short_description
				$value_str .= "'".addslashes(self::getData($jet_product['title'],self::MAX_SHELF_DESCRIPTION))."',";//self_description
				$value_str .= "'Not Uploaded'";//status
				$value_str .= ")";
				$insert_data[] = $value_str;

				echo "Inserted product id : ".$jet_product['id']."<br>";

				//save product variants
				if($jet_product['type'] == 'variants')
					self::ImportVariants($jet_product['id'], $jet_product['merchant_id']);

				//save product_type
				self::InsertProductType($jet_product['product_type'], $jet_product['merchant_id']);

				echo "<br>---------------------********************----------------------<br>";
			}

			$query = "INSERT INTO `walmart_product`(`product_id`, `merchant_id`, `product_type`, `category`, `tax_code`, `short_description`, `self_description`, `status`) VALUES ".implode(',', $insert_data);
			Data::sqlRecords($query, null, "insert");
		}
		else
		{
			echo "No Products to Import!!";
		}
    }

	public static function ImportVariants($product_id,$merchant_id)
	{
		$walmart_query = "SELECT `option_id` FROM `walmart_product_variants` WHERE `product_id`=".$product_id." AND `merchant_id`=".$merchant_id;
		$walmart_product_variants = Data::sqlRecords($walmart_query, "all", "select");

		$option_ids = '';
		if($walmart_product_variants && is_array($walmart_product_variants))
		{
			foreach ($walmart_product_variants as $key=>$product_variants) {
				$option_ids .= $product_variants['option_id'];
				if(isset($walmart_product_variants[$key+1]))
					$option_ids .= ',';
			}
		}

		$query = "SELECT * FROM `jet_product_variants` WHERE `product_id`=".$product_id;
		if($option_ids != '')
			$query .= " AND `option_id` NOT IN (".$option_ids.")";

		$jet_variants  = Data::sqlRecords($query, "all", "select");
		if($jet_variants && is_array($jet_variants))
		{
			$insert_data = [];
			foreach ($jet_variants as $variant) {
				$value_str = "(";
				$value_str .= $variant['option_id'].",";//option_id
				$value_str .= $variant['product_id'].",";//product_id
				$value_str .= $variant['merchant_id'].",";//merchant_id
				$value_str .= "'".addslashes($variant['variant_option1'])."',";//new_variant_option_1
				$value_str .= "'".addslashes($variant['variant_option2'])."',";//new_variant_option_2
				$value_str .= "'".addslashes($variant['variant_option3'])."',";//new_variant_option_3
				$value_str .= "'Not Uploaded'";//status
				$value_str .= ")";
				$insert_data[] = $value_str;

				echo "Inserted product variants id : ".$variant['option_id']."<br>";
			}

			$query = "INSERT INTO `walmart_product_variants`(`option_id`, `product_id`, `merchant_id`, `new_variant_option_1`, `new_variant_option_2`, `new_variant_option_3`, `status`) VALUES ".implode(',', $insert_data);
			Data::sqlRecords($query, null, "insert");
		}
	}

	public static function InsertProductType($product_type, $merchant_id)
	{
		$query = "SELECT * FROM `walmart_category_map` WHERE `merchant_id` = ".$merchant_id." AND `product_type` LIKE '".$product_type."' LIMIT 0,1";

		$data = Data::sqlRecords($query, "one", "select");

		if(!$data)
		{
			$query = "INSERT INTO `walmart_category_map`(`merchant_id`, `product_type`) VALUES (".$merchant_id.",'".addslashes($product_type)."')";
			Data::sqlRecords($query, null, "insert");

			echo "Inserted product type : ".$product_type."<br>";
		}
	}

	public static function getData($string, $length)
	{
		if(strlen($string) > $length)
		{
			$string = substr($string, 0, $length);
		}
		return $string;
	}

	/**
	 *	Create Webhooks
	 */
	public function actionCreatewebhooks()
	{
		$sc = new ShopifyClientHelper(SHOP, TOKEN, WALMART_APP_KEY, WALMART_APP_SECRET);
		Data::createWebhooks($sc);
	}

	/**
	 *	Import Products from Shopify
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

}