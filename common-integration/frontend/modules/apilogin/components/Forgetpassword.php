<?php

namespace frontend\modules\apilogin\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;
use frontend\modules\walmartapi\components\Walmartapi;
use frontend\modules\walmartapi\components\Datahelper;

class Forgetpassword extends Component
{
    /**
     * @param $Output
     * @return bool
     */
    public function getPassword($Output)
    {
        if (isset($Output['shop_url']) && !empty($Output['shop_url'])) {

            try {
                $passworddetail = self::getEmail($Output);

            } catch (\Exception $e) // an exception is raised if a query fails
            {

                return ['error' => true, 'message' => $e->getMessage()];
            }
        } else {
            return ['error' => true, 'message' => 'Invalid send data.'];
        }

        return $passworddetail;
    }

    /**
     * @param $Output
     * @return bool
     */
    public function getEmail($Output)
    {
        $shop_url = $Output['shop_url'];

        if ((isset($shop_url) && !empty($shop_url))){

            $forgetpassword = self::ExtensionCheck($shop_url);
            return $forgetpassword;
        }else{
            $validateData = ['error'=>true,'message' => 'Please enter Shop_url.'];
            $headerData = BaseJson::encode($validateData);
            return $headerData;
        }
    }


    /* check extention paid by merchant*/
    public function ExtensionCheck($shop_url)
    {
        $extention = ['walmart' => 'WalmartExtensionCheck', 'jet' => 'JetExtensionCheck'];
        foreach ($extention as $key => $value) {

            $data = self::$value($shop_url);
            if ($data) {
                return $data;
            }
        }
        return false;
    }

    public function WalmartExtensionCheck($shop_url)
    {
        $data = Datahelper::sqlRecords("SELECT `email`,`merchant_id` FROM `walmart_shop_details` WHERE shop_url='".$shop_url."'", 'one');
        $OTP = md5(uniqid(rand(), TRUE));

        if(!empty($shop_url) && !empty($data['merchant_id'])){
            $query="UPDATE `app_login_check` SET `forgot_password_key`='".$OTP."' where merchant_id='".$data['merchant_id']."' ";

            Datahelper::sqlRecords($query,null,"update");

            $mer_email= $data['email'];
            $subject="Forget Password";
            $etx_mer="";
            $headers_mer = "MIME-Version: 1.0" . chr(10);
            $headers_mer .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
            $headers_mer .= 'From: shopify@cedcommerce.com' . chr(10);
            $etx_mer .='Your new password is : '.$OTP;

            mail($mer_email,$subject, $etx_mer, $headers_mer);

            return ['success'=>true, 'message'=>'password send to you email'];
        }else{
            return ['error'=>true, 'message'=>'Something went wrong'];
        }
    }

    public function JetExtensionCheck($shop_url)
    {

        $query = 'select jet.merchant_id,jet.email from `jet_extension_detail` jet INNER JOIN `app_status` app ON app.merchant_id=jet.merchant_id where app.shop ="'.$shop_url.'" limit 0,1';

        $data = Datahelper::sqlRecords($query, 'one');

        $OTP = md5(uniqid(rand(), TRUE));

        if(!empty($shop_url) && !empty($data['merchant_id'])){
            $query="UPDATE `app_login_check` SET `forgot_password_key`='".$OTP."' where merchant_id='".$data['merchant_id']."' ";
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
