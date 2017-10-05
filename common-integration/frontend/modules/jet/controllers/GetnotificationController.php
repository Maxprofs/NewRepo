<?php
namespace frontend\modules\jet\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\jet\components\Data;

/**
* BulkUpload controller
*/
class GetnotificationController extends Controller 
{

	public function beforeAction($action)
    {
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

	public static function getNotification($merchant_id)
	{
        $returnHtml = '';
        $date = date('Y/m/d', time());
        $walmartnotification = Data::sqlRecords("SELECT * FROM `common_notification` WHERE `enable`='yes' AND `marketplace` IN ('all','jet') AND `to_date` >= '".$date."' AND `from_date` <='".$date."' ORDER BY `sort_order` ASC", 'all');        $date = strtotime($date);
        foreach ($walmartnotification as $key => $value) {
            $i=2;
            $afterTwoDaydate = strtotime(date("Y-m-d", strtotime($value['from_date'])) . " +".$i."days");
            $afterTwoDaydate = date('Y/m/d', $afterTwoDaydate);
            $afterTwoDaydate = strtotime($afterTwoDaydate);
        	if(is_null($value['enable_merchant'])){
                if(strpos($value['seen_clients'],",")){
                    $seen_clients = explode(',',$value['seen_clients']);
                    if (in_array($merchant_id, $seen_clients)){
                        if($afterTwoDaydate>=$date){
                            $returnHtml.='<div class="noti-ced"><span class="new">New</span>'.$value['html_content'].'</div>';
                        }
                        else{
                            $returnHtml.='<div class="noti-ced">'.$value['html_content'].'</div>';
                        }
                        
                    }
                    else{
                        if($afterTwoDaydate>=$date){
                            $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                        }
                        else{

                            $returnHtml.='<div class="noti-ced show-notification">'.$value['html_content'].'</div>';
                        }
                        
                    }
                }
                else{
                    if($value['seen_clients']==$merchant_id){
                        if($afterTwoDaydate>=$date){
                            $returnHtml.='<div class="noti-ced"><span class="new">New</span>'.$value['html_content'].'</div>';
                        }
                        else{
                            $returnHtml.='<div class="noti-ced">'.$value['html_content'].'</div>';
                        }
                    }
                    else{
                        if($afterTwoDaydate>=$date){
                            $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                        }
                        else{
                            
                            $returnHtml.='<div class="noti-ced show-notification">'.$value['html_content'].'</div>';
                        }
                    }
                }
        	}
        	else{
        		if(strpos($value['enable_merchant'],",")){
        			$merchant_ids = explode(',',$value['enable_merchant']);
        			if (in_array($merchant_id, $merchant_ids)){
        				if(strpos($value['seen_clients'],",")){
                            $seen_clients = explode(',',$value['seen_clients']);
                            if (in_array($merchant_id, $seen_clients)){
                                if($afterTwoDaydate>=$date){
                                    $returnHtml.='<div class="noti-ced"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    $returnHtml.='<div class="noti-ced">'.$value['html_content'].'</div>';
                                }
                            }
                            else{
                                if($afterTwoDaydate>=$date){
                                $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    
                                    $returnHtml.='<div class="noti-ced show-notification">'.$value['html_content'].'</div>';
                                }
                            }
                        }
                        else{
                            if($value['seen_clients']==$merchant_id){
                                if($afterTwoDaydate>=$date){
                                    $returnHtml.='<div class="noti-ced"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    $returnHtml.='<div class="noti-ced">'.$value['html_content'].'</div>';
                                }
                            }
                            else{
                                if($afterTwoDaydate>=$date){
                                $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    
                                    $returnHtml.='<div class="noti-ced show-notification">'.$value['html_content'].'</div>';
                                }
                            }
                        }
        			}

        		}
        		else{
        			if($value['enable_merchant']==$merchant_id){
        				if(strpos($value['seen_clients'],",")){
                            $seen_clients = explode(',',$value['seen_clients']);
                            if (in_array($merchant_id, $seen_clients)){
                                if($afterTwoDaydate>=$date){
                                    $returnHtml.='<div class="noti-ced"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    $returnHtml.='<div class="noti-ced">'.$value['html_content'].'</div>';
                                }
                            }
                            else{
                                if($afterTwoDaydate>=$date){
                                $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    
                                    $returnHtml.='<div class="noti-ced show-notification">'.$value['html_content'].'</div>';
                                }
                            }
                        }
                        else{
                            if($value['seen_clients']==$merchant_id){
                                if($afterTwoDaydate>=$date){
                                $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                            }
                            else{
                                if($afterTwoDaydate>=$date){
                                $returnHtml.='<div class="noti-ced show-notification"><span class="new">New</span>'.$value['html_content'].'</div>';
                                }
                                else{
                                    
                                    $returnHtml.='<div class="noti-ced show-notification">'.$value['html_content'].'</div>';
                                }
                            }
                        }
        			}
        		}
        		
        	}
        }
        if($returnHtml==''){
            $returnHtml.='<div class="noti-ced"><p>No Notification available right now!</p></div>';
        }
        return $returnHtml;
	}
    /*trace client who read notification*/
    public static function actionSetread(){
        $date = date('Y/m/d', time());
        $walmartnotification = Data::sqlRecords("SELECT * FROM `common_notification` WHERE `enable`='yes' AND `to_date` >= '".$date."' ORDER BY `sort_order` ASC", 'all');
        foreach ($walmartnotification as $key => $value) {
            if(is_null($value['seen_clients'])){
                $query = "update `common_notification` set seen_clients ='" . $_POST['id'] . "' where id='" . $value['id'] . "'";
            }
            else{
                $string = $value['seen_clients'].','.$_POST['id'];
                $query = "update `common_notification` set seen_clients ='" . $string . "' where id='" . $value['id'] . "'";
            }
            Data::sqlRecords($query,null,'update');
        }
    }

}