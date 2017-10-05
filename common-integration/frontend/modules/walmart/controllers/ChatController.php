<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use frontend\modules\walmart\controllers\WalmartmainController;
use frontend\modules\walmart\components\Data;

/**
* Chat controller
*/
class ChatController extends WalmartmainController 
{
	public function actionBox()
  	{
  		$merchant_id = Yii::$app->user->identity->id;
  		
  		$data = ['sender_ref'=>$merchant_id,
  				 'sender_type'=>'customer',
  				 'sender_name'=>$this->getMerchantName($merchant_id),
  				 'reciever_type'=>'group',
  				 'group_id'=>'shopify_walmart_team'

  				];


  		$hash = $this->getHash($data);
  		$this->setHash($hash,$data);
  		
  		$this->layout="chat";
    	$html=$this->render('box',['hash'=>$hash]);
    	return $html; 
  	}

  	public function getMerchantName($id){
  		$merchant = Data::sqlRecords("SELECT `name` FROM jet_registration WHERE merchant_id='{$id}'",'one');
      if(!$merchant) {
        $name = Data::sqlRecords("SELECT `shop_url` FROM `walmart_shop_details` WHERE merchant_id='{$id}'", 'one');
        return $name['shop_url'];
      }
      else
  		  return $merchant['name'];
  	}
  	public function setHash($hash,$data){
  		$directory = \Yii::getAlias('@webroot').'/frontend/view/chat/hash';
  		if(file_exists($directory.'/'.$hash)){
  			if($data['sender_type']=='customer'){
  				Data::sqlRecords('UPDATE client_chat SET active=1 WHERE merchant_id='.$data['sender_ref'],null,'update');
  			}
  			return;
  		}
  		if($data['sender_type']=='customer'){
	  		$conversation = Data::sqlRecords('select conversation from client_chat where merchant_id='.$data['sender_ref'],null,'one');
	  		if($conversation){
	  			Data::sqlRecords('UPDATE client_chat SET active=1 WHERE merchant_id='.$data['sender_ref'],null,'update');
	  		}
	  		else
	  		{
	  			$conversation = addslashes(json_encode([]));
				Data::sqlRecords("insert into client_chat (merchant_id,conversation,active) values({$data['sender_ref']},'{$conversation}','1')",null,'insert');
	  		}
	  	}
        if (!file_exists($directory)){
            mkdir($directory,0775, true);
        }

        $handle = fopen($directory.'/'.$hash,'a');
        fwrite($handle,json_encode($data));
        fclose($handle);
  	}
  	public function actionSupport()
  	{
  		$merchant_id = Yii::$app->user->identity->id;
  		
  		$data = ['group_id'=>'shopify_walmart_team',
  				 'sender_type'=>'group',
  				 'sender_ref'=>'group',
  				 'sender_name'=>'Cedcommerce Support',
  				 'reciever_type'=>'customer',
  				 'reciever_ref'=>'14'

  				];
  		$hash = $this->getHash($data);
  		$this->setHash($hash,$data);

  		$activeMerchants = Data::sqlRecords('SELECT * FROM client_chat WHERE active="1"','all');
  		$this->layout="chat";
    	$html=$this->render('support',['hash'=>$hash,'activeMerchants'=>$activeMerchants]);
    	return $html; 
  	}

  	public function getHash($data)
  	{
  		$finalString = '';
  		foreach($data as $val){
  			$finalString .= $val;
  		}
  		return md5($finalString);
  	}

  	public function actionLoad(){
  		$merchant_id = Yii::$app->user->identity->id;
  		$conversation = Data::sqlRecords('select conversation from client_chat where merchant_id='.$merchant_id,null,'one');
  		$conversation = json_decode($conversation[0]['conversation'],true);
  		$html = '';

      $dates = [];
  		foreach($conversation as $data){

        if(isset($data['date']) && !in_array($data['date'], $dates)) {
          $dates[] = $data['date'];

          $dateId = str_replace(',', '', $data['date']);
          $dateId = str_replace(' ', '_', $dateId);
          $html .= '<li class="chat-date" id="'.$dateId.'">';
          $html .= '<span>'.$data['date'].'</span>';
          $html .= '</li>';
        }

        $time = '';
        if(isset($data['time'])) {
          $time = explode(':', $data['time']);
          unset($time[2]);
          $time = implode(':', $time);
        }

  			//$html .= '<li><span class="user-name">'.$data['sender_name'].'</span> : <span>'.$data['message'].'</span></li>';
        if($data['user_id']=='shopify_walmart_team')
        {
          $sender = $data['sender_name'];
          //$firstLetter = strtoupper(substr($data['sender_name'], 0, 1));
          $firstLetter = 'C';

          $html .= '<li class="other">';
          //$html .= '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar">'.$firstLetter.'<img src="http://i.imgur.com/DY6gND0.png" draggable="false"/></div>';
          $html .= '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar"><span class="chat-person">'.$firstLetter.'</span></div>';
          $html .= '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 msg">';
          $html .= '<p>'.$data['message'].'</p>';
          $html .= '<time>'.$time.'</time>';
          $html .= '</div>';
          $html .= '</li>';
        }
        else
        {
          $sender = $data['sender_name'];
          $firstLetter = strtoupper(substr($data['sender_name'], 0, 1));

          $html .= '<li class="self">';
          //$html .= '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar">'.$firstLetter.'<img src="http://i.imgur.com/HYcn9xO.png" draggable="false"/></div>';
          $html .= '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar"><span class="chat-person">'.$firstLetter.'</span></div>';
          $html .= '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 msg">';
          $html .= '<p>'.$data['message'].'</p>';
          $html .= '<time>'.$time.'</time>';
          $html .= '</div>';
          $html .= '</li>';
        }
  		}

  		echo $html;die;

  	}
  	public function actionClear(){
  		$conversation = addslashes(json_encode([]));
  		$merchant_id = Yii::$app->user->identity->id;
  		Data::sqlRecords("UPDATE client_chat SET conversation='{$conversation}' WHERE merchant_id=".$merchant_id,null,'update');
  		die;
  	}
}