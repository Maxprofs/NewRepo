<?php

namespace backend\modules\walmart\controllers;

use Yii;
use backend\modules\walmart\models\WalmartExtensionDetailSearch;
use backend\modules\walmart\components\Data;
use backend\modules\walmart\components\Curlrequests;
use yii\data\SqlDataProvider;




/**
 * DetailsController implements the CRUD actions for WalmartExtensionDetail model.
 */
class MailchimpController extends BaseController
{
   public function actionIndex(){
   	$data = $this->actionGetlist();
   	$listData = json_decode($data,true);
   	   	$returnArray = [];
   	foreach ($listData['lists'] as $key => $value) {
   		$returnArray[$value['id']] = $value['name'];
   	}
   	return json_encode($returnArray);
   }
   /*
	*Get Merchant list
   */
   public function actionGetlist($filters=array(), $start=0, $limit=25, $sort_field='created', $sort_dir='DESC'){
   	$_params = array("filters" => $filters, "start" => $start, "limit" => $limit, "sort_field" => $sort_field, "sort_dir" => $sort_dir);
   	$main = new Curlrequests();
   	$data = $main->getRequest('lists',$_params);
   	return $data;
   }
   public function actionCampaigns(){
     /* $_params = array("filters" => $filters, "start" => $start, "limit" => $limit, "sort_field" => $sort_field, "sort_dir" => $sort_dir);*/
       $pdata = [
                        'client_id'     => '273444830952',
                        'client_secret'    => 'dae0b80a59729743fcae3ede0965f06ecf5e48a3810c7ce326',
                       /* 'merge_fields'  => [
                            'FNAME'     => $merchant_data['fname'],
                            'LNAME'     => $merchant_data['lname'],
                        ]*/
                        ];
      $main = new Curlrequests();
      $data = $main->postRequest('authorized-apps',$pdata);
      
      print_r(json_decode($data,true));die;
      return $data;
   }

 
}
