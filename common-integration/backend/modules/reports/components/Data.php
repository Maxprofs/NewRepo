<?php 
namespace backend\modules\reports\components;

use Yii;
use yii\base\Component;

class Data extends component
{
    const ORDER_TABLE = 'jet_order_detail';
    const EXTENSIONS_TABLE = 'jet_shop_details';
    const EMAIL_REPORT = 'email_report';
    const ISSUES = 'issues';
    const CONFIGURATION_TABLE = 'jet_configuration';
    const JET_PRODUCT_TABLE = 'jet_product';
    const JET_CONFIG = 'jet_config';
    const JET_EMAIL_TEMPLATE = 'jet_email_template';


     /* function for getting template file path */
   public static function getEmailTemplate($template){
        $serach = [];
        $rootPath = dirname(Yii::getAlias('@webroot'));
        $file = $rootPath.'/views/templates/'.$template;
        $serach[]=$file;
        if(file_exists($file)){
            return $file;
        }
        $file = $rootPath.'/frontend/views/templates/'.$template;
        $serach[]=$file;
        if(file_exists($file)){
            return $file;
        }
        $file = $rootPath.'/backend/views/templates/'.$template;
        $serach[]=$file;
        if(file_exists($file)){
            return $file;
        }
        $file = $rootPath.'/frontend/views/templates/'.$template;
        $serach[]=$file;
        if(file_exists($file)){
            return $file;
        }
        $file = $rootPath.'/frontend/modules/jet/views/templates/'.$template;
        $serach[]=$file;
        if(file_exists($file)){
            return $file;
        }
        else
        {
        	//print_r($serach);die;
            return false;
        }

    }
}
?>
