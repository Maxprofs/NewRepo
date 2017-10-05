<?php 
namespace frontend\components;

use Yii;
use yii\base\Component;
use common\models\User;
use app\models\AppStatus;
use frontend\components\Data;
use frontend\modules\walmart\components\ShopifyClientHelper;

class Webhookmgmt extends Component
{	
	public function createWebhook($shop_name, $token)
	{
        try
        {
            $query = 'SELECT * FROM `jet_extension_details`';
	     
        }
        catch(Exception $e)
   		{
        	//exit(0);
        	return "";
        }   	 
	}
    public function createWebhook($shop_name,$prublicKey,$privateKey)
    {
        try
        {
            $query = 'SELECT '
         
        }
        catch(Exception $e)
        {
            //exit(0);
            return "";
        }   
    }
}
?>