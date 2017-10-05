<?php 
namespace frontend\modules\jet\components;
use yii\base\Component;
use frontend\modules\jet\models\JetNotifications;

class Jetnotificationscom extends component
{	
	public static function saveNotification($merchant_id="",$code="",$count="",$ids_array=[])
	{
		$merchant_id=trim($merchant_id);
		$code=trim($code);
		$count=trim($count);
		if($merchant_id!="" && $code!="" && is_array($ids_array) && count($ids_array)>0)
		{
			$model="";
			$model = new JetNotifications();
			$result="";
			$result=$model->find()->where(['merchant_id' => $merchant_id,'code'=>$code])->one();
			if(is_object($result))
			{
					$old_count=$new_count=0;
					$old_array=$new_array=[];
					$new_array=array_unique($ids_array);
					if($result->count!=""){
						$old_count=$result->count;
					}
					if($result->array_of_ids !=""){
							$old_array=json_decode($result->array_of_ids,true);
					}
					if(count($old_array)>0){
							$merged=array();
							$merged=array_merge($old_array,$ids_array);
							$new_array=array_unique($merged);
					}
					$new_count=count($new_array);
					$result->count=$new_count;
					$result->array_of_ids=json_encode($new_array);
					$result->save(false);
			}else{
				$new_array=array();
				if(is_array($ids_array) && count($ids_array)>0){
					$new_array=array_unique($ids_array);
				}
				$model->count=count($new_array);
				$model->code=$code;
				if(count($new_array)==0){
					$model->array_of_ids="";
				}else{
					$model->array_of_ids=json_encode($new_array);
				}
				$model->merchant_id=$merchant_id;
				$model->save(false);

			}
		}
		
	}
	public static function getCount($merchant_id="",$code="")
	{
		$merchant_id=trim($merchant_id);
		$code=trim($code);
		$count=0;
		$model="";
		$model = new JetNotifications();
		$result="";
		$result=$model->find()->where(['merchant_id' => $merchant_id,'code'=>$code])->one();
		if(is_object($result))
		{
			if($result->count!="")
					$count=(int)$result->count;				
		}
		return $count;
	}
	public static function resetCount($merchant_id="",$code="")
	{
			$merchant_id=trim($merchant_id);
			$code=trim($code);
			$count=0;
			$model="";
			$model = new JetNotifications();
			$result="";
			$result=$model->find()->where(['merchant_id' => $merchant_id,'code'=>$code])->one();
			if(is_object($result)){
					$result->count=0;
					$result->array_of_ids="";
					$result->save(false);
			}
	}
	
}
?>