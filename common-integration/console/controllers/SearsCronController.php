<?php
namespace console\controllers;

use frontend\modules\sears\controllers\SearscronController as Shopifysears;
use frontend\modules\sears\controllers\SearsinvsyncController;
use Yii;
use yii\console\Controller;

/**
* Cron controller
*/
class SearsCronController extends Controller 
{
  public function actionIndex() 
  {    
    // echo "cron service runnning";
    echo getcwd();     
  }
  /* public function actionRefundstatus()
  {
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionUpdaterefundstatus(true);
  }
  public function actionCreatereturn()
  {
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionCreatejetreturn(true);
  } */

  public function getErrorArray($errorsArray,$array)
  {
    if(isset($errorsArray[$array['merchant_order_id']])){
      $array['reason'] = $errorsArray[$array['merchant_order_id']]['reason'].'<br>'.$array['reason'];
      $errorsArray[$array['merchant_order_id']] = $array;
    }
    else
    {
      $errorsArray[$array['merchant_order_id']] = $array;
    }
    return $errorsArray;
  }
  public function actionSearsinvupdate(){
  	$obj = new Shopifysears(Yii::$app->controller->id,'');
  	$obj->actionInvsync(true);
  }
  /* 
  public function actionUpdatepaymentstatus()
  {
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionUpdaterecurringpaymentstatus(true);
  }// RecurringPaymentUpdate end
    
  public function actionUpdatedynamicprice(){
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionDynamicprice(true);
  }  

  public function actionFetchsearsorder()
  {
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionFetchorder(true);
  }

  public function actionSyncsearsorder()
  {
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionSyncorder(true);
  }
  public function actionGetshipment(){
    $obj = new Shopifysears(Yii::$app->controller->id,'');
    $obj->actionShiporder(true);
  } */
  
  /*  
   * Update all inventory on jet within 24 hours 
   * */
 /*  public function actionInventoryupdatejson()
  {
  	//$startTime = microtime(true);
  	$obj = new SearsinvsyncController(Yii::$app->controller->id,'');
  	$obj->actionUploadinventory(true);
  	$endTime = microtime(true);
  	echo $startTime.'-'.$endTime."=".($endTime-$startTime)/60;
  	die("<hr>");  
  } */
  /* 
   * Process uploaded inventory json file
   *  */
  /* public function actionProcessinvjson()
  {
  	//$startTime = microtime(true);
  	$obj = new SearsinvsyncController(Yii::$app->controller->id,'');
  	$obj->actionProcessuploadedfiles(true);
  	//$endTime = microtime(true);
  	//echo $startTime.'-'.$endTime."=".($endTime-$startTime)/60;
  	//die("<hr>");
  } */
}
