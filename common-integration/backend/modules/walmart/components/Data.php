<?php 
namespace backend\modules\walmart\components;

use Yii;
use yii\base\Component;

class Data extends component
{
    const ORDER_TABLE = 'walmart_order_details';
    const EXTENSIONS_TABLE = 'walmart_extension_detail';
    const CONFIG_TABLE = 'walmart_configuration';
    const PRODUCT_TABLE = 'walmart_product';
    const EMAIL_TEMPLATE_TABLE = 'email_template';
    const SHOP_DETAILS = 'walmart_shop_details';
    const EMAIL_REPORT = 'walmart_email_report';
    const CONFIGURATION_TABLE = 'walmart_configuration';
    const WALMART_CONFIG_TABLE ='walmart_config';
    //const MAIN_PRODUCT_TABLE = 'jet_product';
    const MAIN_PRODUCT_TABLE = 'walmart_product';
    const REGISTRATION = 'walmart_registration';
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
        $file = $rootPath.'/frontend/modules/walmart/views/templates/'.$template;
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
