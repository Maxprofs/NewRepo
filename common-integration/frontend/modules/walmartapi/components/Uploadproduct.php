<?php

namespace frontend\modules\walmartapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;
use frontend\modules\walmartapi\components\Walmartapi;

class Uploadproduct extends Component
{
    protected $connection;
    protected $value;

    /**
     * @param $Output
     * @return bool|string
     */
	public function getUploadproduct($Output)
	{
        if(isset($Output['product_id']) && !empty($Output['product_id'])){

            try {
                $orderdetail = self::getDetails($Output);

            } catch (\Exception $e) // an exception is raised if a query fails
            {
                
                return ['error'=>true, 'message'=>$e->getMessage()];
//                return false;
            }
        }else{
            return ['error'=>true, 'message'=>'Invalid send data.'];
//            return true;
        }

        return $orderdetail;
	}

    /**
     * @param $Output
     * @return string
     */
    public function getDetails($Output)
    {
        $merchant_id = Yii::$app->request->getHeaders()->get('MERCHANTID');
        $hash_key = Yii::$app->request->getHeaders()->get('HASHKEY');
        $id[] = $Output['product_id'];


        $jetConfig=[];

        $jetConfig = Datahelper::sqlRecords("SELECT `consumer_id`,`secret_key`,`consumer_channel_type_id` FROM `walmart_configuration` WHERE merchant_id='".$merchant_id."'", 'one');
        // print_r($jetConfig);die;
        if(isset($jetConfig))
        {
            $consumer_channel_type_id = $jetConfig['consumer_channel_type_id'];
            $api_user = $jetConfig['consumer_id'];
            $api_password = $jetConfig['secret_key'];
        }

        
    
            $connection = Yii::$app->getDb();


            try {
                $value =  new Walmartapi($api_user,$api_password,$consumer_channel_type_id);
                 define("MERCHANT_ID",$merchant_id);

                $productResponse = $value->createProductOnWalmart($id,$value,$merchant_id,$connection);
                // print_r($productResponse);
                // die('shivam');

                if(is_array($productResponse) && isset($productResponse['uploadIds'],$productResponse['feedId']) && count($productResponse['uploadIds']>0))
                {
                    //save product status and data feed
                    // die('shivam');
                    $ids = implode(',',$productResponse['uploadIds']);
                    foreach($productResponse['uploadIds'] as $val)
                    {
                        $query="UPDATE `walmart_product` SET status='Items Processing', error='' where product_id='".$val."'";
                        Datahelper::sqlRecords($query,null,"update");
                    }
                    $query="INSERT INTO `walmart_product_feed`(`merchant_id`,`feed_id`,`product_ids`)VALUES('".$merchant_id."','".$productResponse['feedId']."','".$ids."')";
                    Datahelper::sqlRecords($query,null,"insert");
                    
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
                    foreach($productResponse['erroredSkus'] as $productSku=>$error)
                    {
                        if(is_array($error))
                            $error = implode(',', $error);

                        $query = "UPDATE `walmart_product` wp JOIN `jet_product` jp ON wp.product_id=jp.id SET wp.`error`='".$error."' where jp.sku='".$productSku."'";
                        Datahelper::sqlRecords($query,null,"update");
                    }
                    $returnArr['error_count'] = count($productResponse['erroredSkus']);
                    $returnArr['erroredSkus'] = implode(',',array_keys($productResponse['erroredSkus']));
                }

            }
            catch (Exception $e)
            {
                $returnArr = ['error'=>true, 'message'=>$e->getMessage()];
            }
        
        return $returnArr;
    }
}
