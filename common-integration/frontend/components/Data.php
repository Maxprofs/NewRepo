<?php
namespace frontend\components;

use Yii;
use yii\base\Component;

class Data extends component
{
    public static function sqlRecords($query,$type=null,$queryType=null)
    {
        $connection=Yii::$app->getDb();
        $response=[];
        if($queryType=="update" || $queryType=="delete" || $queryType=="insert" || ($queryType==null && $type==null))           
               $response= $connection->createCommand($query)->execute();                   
        elseif($type=='one')
            $response=$connection->createCommand($query)->queryOne();        
        else
            $response=$connection->createCommand($query)->queryAll();
        
        unset($connection);
        return $response;
    }


    public static function checkInstalledApp($merchant_id,$type=false,&$installData=[])
    {
        $installInfo = $jetData = [];
        $jetData = self::sqlRecords("SELECT `auth_key` FROM `user` WHERE id='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($jetData['auth_key']) && ($jetData['auth_key']!="") )
        {
            $installInfo['jet']['url']=Yii::getAlias('@weburl');
            if($type)
               $installInfo['jet']['type']="Switch";
        }
        else
        {
            $installInfo['jet']['url']='https://apps.shopify.com/jet-integration';
            if($type)
               $installInfo['jet']['type']="Install";
        }
        $walmartData=self::sqlRecords("SELECT `id` FROM `walmart_shop_details` WHERE `merchant_id`='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($walmartData['id']))
        {
            $installData['walmart']=true;
            $installInfo['walmart']['url']=Yii::getAlias('@webwalmarturl');
            if($type)
               $installInfo['walmart']['type']="Switch"; 
        }
        else
        {
            $installInfo['walmart']['url']='https://apps.shopify.com/walmart-marketplace-integration';
            if($type)
               $installInfo['walmart']['type']="Install"; 
        }
        $neweggData=self::sqlRecords("SELECT `id` FROM `newegg_shop_detail` WHERE `merchant_id`='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($neweggData['id']))
        {
            $installData['newegg']=true;
            $installInfo['newegg']['url']=Yii::getAlias('@webneweggurl');
            if($type)
               $installInfo['newegg']['type']="Switch"; 
        }
        else
        {
            $installInfo['newegg']['url']='https://apps.shopify.com/newegg-marketplace-integration';
            if($type)
               $installInfo['newegg']['type']="Install"; 
        }
        $searsData=self::sqlRecords("SELECT `id` FROM `sears_shop_details` WHERE `merchant_id`='".$merchant_id."' LIMIT 0,1","one","select");
        if(isset($searsData['id']))
        {
            $installData['sears']=true;
            $installInfo['sears']['url']=Yii::getAlias('@websearsurl');
            if($type)
               $installInfo['sears']['type']="Switch"; 
        }
        else
        {
            $installInfo['sears']['url']='https://apps.shopify.com/sears-marketplace-integration';
            if($type)
               $installInfo['sears']['type']="Install"; 
        }
        return $installInfo;
    }

    public static function sendCurlRequest($data=[],$url="")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
?>