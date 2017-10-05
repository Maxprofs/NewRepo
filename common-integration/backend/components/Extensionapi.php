<?php 
namespace backend\components;
use yii\base\Component;
use yii\helpers\Url;

class Extensionapi extends component
{    
    /**
     * Get Option Values Simple Product
     */
    public function getClientsDetails($storeUrl="",&$plateform="")
    {
        $url = "";
        if (($plateform=='m1')||($plateform=='m2')) {
            $url= $storeUrl."/walmart/walmartanalytics/getAnalytics"; 
            $plateform = "magento";   
        }elseif($plateform=='woocommerce') {
            $url = $storeUrl."/woocommerce/walmart/walmartanalytics/getAnalytics";    
            $plateform = "woocommerce";   
        }else{
            return "please choose plateform";
        }
        
        $ch = curl_init($url);
                
        curl_setopt($ch, CURLOPT_HEADER, 1); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = substr($server_output, $header_size);
        curl_close ($ch);
        return $body;
    }
}
?>
