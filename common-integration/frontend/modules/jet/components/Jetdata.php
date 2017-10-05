<?php 
namespace frontend\modules\jet\components;

use yii\base\Component;

use frontend\modules\jet\components\Jetproductinfo;


class Jetdata extends component
{
    public static function archieved($jetHelper, $sku,$fullfillmentnodeid,$merchant_id,$file=false)
    {        
        $status=true;
        $value = $ArchiveResponse = [];
        $value['is_archived'] = true;
        $msg = "";
        if(Jetproductinfo::checkSkuOnJet($sku,$jetHelper,$merchant_id))
        {
            $msg.= "product available on jet".PHP_EOL;
            if(isset($response['is_archived']) && $response['is_archived']==true)
            {         
                $res = Jetproductinfo::updateQtyOnJet(rawurlencode($sku),0,$jetHelper,$fullfillmentnodeid,$merchant_id); 
                $response = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($inventory[$sku]),$merchant_id,$status); 
                $msg.="product already archive on jet".PHP_EOL;            
                if($status==202) 
                {
                    $msg.="product already archived and inventory changed to 0".PHP_EOL;   
                    $ArchiveResponse = ['success'=>true, 'message'=>'already archived and inventory change to 0'];         
                }else{
                    $msg.="product already archived but inventory not changed to 0".PHP_EOL;   
                    $ArchiveResponse =  ['success'=>true, 'message'=>'already archived but inventory not change to 0'];    
                }    
            }
            else
            {
                $archiveResponse=[];
                $data=$jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode($value),$merchant_id,$status);
                $msg.="archive post response".$data.PHP_EOL;   
                $archiveFlag=false;
                if($status==202)
                {     
                    $archiveFlag=true;                                   
                }
                $status=true;
                $resp = Jetproductinfo::updateQtyOnJet(rawurlencode($sku),0,$jetHelper,$fullfillmentnodeid,$merchant_id,$status);
                $msg.="archive inventory post response".$data.PHP_EOL;   
                if($status==202 && $archiveFlag){
                    $msg.="product archived and inventory change to 0".PHP_EOL;   
                    $ArchiveResponse =  ['success'=>true, 'message'=>'product archived and inventory change to 0..'];                  
                }elseif($status!=202 && $archiveFlag){
                    $msg.="product archived and inventory not change to 0".PHP_EOL;   
                    $ArchiveResponse =  ['success'=>true, 'message'=>'product archived and inventory not change to 0..'];                  
                }else{
                    $msg.="product not archived on jet".PHP_EOL;   
                    $ArchiveResponse =  ['error'=>true, 'message'=>'product not archived on jet..'];                  
                }
            }
        } 
        if(is_resource($file))        
        	fwrite($file, $msg);        
                
        return $ArchiveResponse ;
    }

    public static function unarchieved($jetHelper,$fullfillmentnodeid,$sku,$qty,$merchant_id,$file=false)
    {        
        $value['is_archived']=false;
        $msg = $status = "";
        if(Jetproductinfo::checkSkuOnJet($sku,$jetHelper,$merchant_id))
        {
            $msg.="product available on jet".PHP_EOL;   
            $unarchiveResponse=[];
            $data=$jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/status/archive',json_encode($value),$merchant_id,$status);
            $msg.="unarchive post response".$data.PHP_EOL;   
            $unarchived = array(
                'fullfillment_nodes'=>array(
                    'fullfilment_node_id'=>$fullfillmentnodeid,
                    'quantity'=>(int)$qty,
                )
            );
            $unarchiveFlag=false;
            if($status==202){
                fwrite($file, "product unarchive on jet".PHP_EOL);   
                $unarchiveFlag=true;
            }
            $status=false;
            $msg.="inventory post data".json_encode($unarchived).PHP_EOL;   
            $unarchive = $jetHelper->CPutRequest('/merchant-skus/'.rawurlencode($sku).'/inventory',json_encode($unarchived),$merchant_id,$status);
            $msg.="unarchive inventory post response".$unarchive.PHP_EOL;   
            if($status==202 && $unarchiveFlag)
            {
                $msg.="product unarchive and inventory updated successfully".PHP_EOL;   
                $unArchiveResponse = ['success'=>true, 'message'=>'product unarchive and inventory updated successfully..'];
            }                     
            elseif($status!=202 && $unarchiveFlag)
            {
                $msg.= "product unarchive but inventory not updated".PHP_EOL;   
                $unArchiveResponse = ['success'=>true, 'message'=>'product unarchive but inventory not updated..'];
            }
            else
            {
                $msg.="product not unarchive on jet".PHP_EOL;   
                $unArchiveResponse = ['success'=>true, 'message'=>'product not unarchive on jet..'];
            }
        }  
        if(is_resource($file))        
            fwrite($file, $msg);        
                
        return $unArchiveResponse ;      
    }     
}
?>
