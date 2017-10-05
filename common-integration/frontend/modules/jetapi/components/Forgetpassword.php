<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;

class Forgetpassword extends Component
{
    /**
     * @param $Output
     * @return bool
     */
	public function getPassword($Output)
	{
        // die('in getpassword');
        if(isset($Output['shop_url']) && !empty($Output['shop_url'])){

            try {
                $passworddetail = self::getEmail($Output);

            } catch (\Exception $e) // an exception is raised if a query fails
            {
                
                return ['error'=>true, 'message'=>$e->getMessage()];
//                return false;
            }
        }else{
            return ['error'=>true, 'message'=>'Invalid data send.'];
        }
        
        return $passworddetail;
//        return true;
	}

    /**
     * @param $Output
     * @return bool
     */
    public function getEmail($Output)
    {
        $shop_url = $Output['shop_url'];

        $query = 'select jet.merchant_id,jet.email from `jet_extension_detail` jet INNER JOIN `app_status` app ON app.merchant_id=jet.merchant_id where app.shop ="'.$shop_url.'" limit 0,1';
        
        $data = Datahelper::sqlRecords($query, 'one');

        $OTP = md5(uniqid(rand(), TRUE));

        if(!empty($shop_url) && !empty($data['merchant_id'])){
            $query="UPDATE `jet_login_check` SET `forgot_password_key`='".$OTP."', `status`='passwordpending' where merchant_id='".$data['merchant_id']."' ";
            Datahelper::sqlRecords($query,null,"update");

            $mer_email= $data['email'];
            $subject="Forget Password";
            $etx_mer="";
            $headers_mer = "MIME-Version: 1.0" . chr(10);
            $headers_mer .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
            $headers_mer .= 'From: shopify@cedcommerce.com' . chr(10);
            $etx_mer .='Your Verification password is : '.$OTP;

            mail($mer_email,$subject, $etx_mer, $headers_mer);

            return ['success'=>true, 'message'=>'password send to you email'];
        }else{
            return ['error'=>true, 'message'=>'Something went wrong'];
        }
    }
}
