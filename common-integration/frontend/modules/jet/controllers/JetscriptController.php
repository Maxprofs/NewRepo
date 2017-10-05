<?php
namespace frontend\modules\jet\controllers;

use frontend\modules\jet\components\Data;
use frontend\modules\jet\components\Jetproductinfo;
use frontend\modules\jet\models\JetProduct;
use Yii;

class JetscriptController extends JetmainController
{	
	protected  $jetHelper,$sc;
	public function actionShopifyproductsync()
    {
        $productIdData = $sync = [];
        $total = 0;
        $merchant_id = MERCHANT_ID;
        $session = Yii::$app->session;
        $sync = $_POST;
        
        if(!is_object($session) || empty($sync) ){
            Yii::$app->session->setFlash('error', "Please retry again to sync products");
            return $this->redirect(Yii::$app->request->referrer);
        }
        $productIdSql = "SELECT `id` FROM `jet_product` WHERE `merchant_id`='".$merchant_id."' AND `fulfillment_node`!=0";
        
        $productIdData = Data::sqlRecords($productIdSql,'all','select');
        $total = count($productIdData);
        if (!empty($productIdData) && is_array($productIdData))
        {
            $chunkStatusArray=array_chunk($productIdData, 50);

            foreach ($chunkStatusArray as $ind => $value)
            {
                $session->set('productstatus-'.$ind, $value);
            }
            $session->set('sync', $sync);
            return $this->render('batchupdatestatus', [
                'totalcount' => $total,
                'pages' => count($chunkStatusArray)
            ]);
        }
    }

    public function actionBatchstatusupdate()
    {
        $merchant_id=MERCHANT_ID;
        $session = Yii::$app->session;
        $productData = $sync = [];
        $updateCount=0;
        $index=Yii::$app->request->post('index');
        $return_msg['success'] = $return_msg['error'] = "";
        $productData = $session->get('productstatus-'.$index);
        $sync = $session->get('sync');

        if(is_array($productData) && count($productData)>0)
        {
            $ids = "";
            foreach($productData as $value)
            {
                $ids .= $value['id'].',';
            }
            $ids = rtrim($ids,',');
            
            if(count($sync))
            {
                try
                {
                    $products = [];
                    $products = $this->sc->call('GET', '/admin/products.json?ids='.$ids,[]);
                    
                    if (!empty($products) && is_array($products))
                    {
                    	$count = 0;
                        foreach ($products as $value)
                        {
                            $product_sku = $value['variants'][0]['sku'];

                            /* if ($product_sku == "") 
                                continue; */
                             
                            $count+= Jetproductinfo::updateDetails($value,$sync,$merchant_id);                           
                        }
                        if ($count>0)
                        	return json_encode(['success'=>$count." Product(s) Sync'ed Successfully!!"]);
                        else 
                        	return json_encode(['success'=>"Everything up-to-date."]);
                    }
                    else
                    {
                        return json_encode(['error'=>"Some products not exist on shopify."]);
                    }
                }
                catch (Exception $e)
                {
                    return json_encode(['error'=>$e->getMessage()]);
                }
            } // Sync end
        }
    }

    
    public function actionSyncproductstore()
    {
    	$import_option = $merchant_id = '';
    	$products = [];
    	$merchant_id = MERCHANT_ID;
    
    	$import_option = Data::getConfigValue($merchant_id, 'import_product_option');
    	$prodId = Yii::$app->request->post('product_id', false);
    	parse_str(Yii::$app->request->post('sync_fields'), $sync);
    	
    	$products = $this->sc->call('GET', '/admin/products.json?ids='.$prodId, array());
    	
    	if (isset($products['errors']))
    		return json_encode(['error' => true, 'message' => 'Either Token updated or No product exist! ']);
    
    		if ($products && is_array($products))
    		{
    			$response = '';
    			foreach ($products as $value)
    			{
    				$response = Jetproductinfo::updateDetails($value,$sync,$merchant_id);
    			}
    			if ($response)
    				return json_encode(['success' => true, 'message' => 'Product Synced Successfully!!']);
    				else
    					return json_encode(['success' => true, 'message' => 'No Change in Product!!']);
    		}
    		else
    			$returnArr = ['error' => true, 'message' => "Product doesn't exist on Shopify."];
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
        return $this->redirect(\yii\helpers\Url::toRoute(['jetproduct/index']));
    }

	public static function ImportVariants($product_id,$merchant_id)
	{
		$walmart_query = "SELECT `option_id` FROM `walmart_product_variants` WHERE `merchant_id`=".$merchant_id." AND `product_id`=".$product_id;
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
}