<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseJson;

class Newpassword extends Component
{
    /**
     * @param $Output
     * @return bool
     */
    public function getPassword($Output)
    {
        if (isset($Output['verification_password']) && !empty($Output['verification_password']) && isset($Output['new_password']) && !empty($Output['new_password']) && isset($Output['shop_url']) && !empty($Output['shop_url'])) {

            try {
                $passworddetail = self::getNewpassword($Output);

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
    public function getNewpassword($Output)
    {

        $shop_url = $Output['shop_url'];
        $new_password = md5($Output['new_password']);

        $oldpassword = $Output['verification_password'];
        $data = Datahelper::sqlRecords("SELECT `forgot_password_key` FROM `jet_login_check` WHERE shop_url='" . $shop_url . "'", 'one');

        if (isset($oldpassword) && !empty($oldpassword)) {
            if (strlen($new_password) >= 8) {
                if (!empty($data) && $data['forgot_password_key'] == $oldpassword) {
                    $query = "UPDATE `jet_login_check` SET `forgot_password_key`= NULL,`password`='" . $new_password . "', `status`='complete' where shop_url='" . $shop_url . "' ";
                    Datahelper::sqlRecords($query, null, "update");

                    return ['success' => true, 'message' => 'Password Updated.'];
                } else {
                    return ['error' => true, 'message' => 'Invalid data send.'];
                }
            } else {
                return ['error' => true, 'message' => 'Password must be greater than 7 digit'];
            }
        } else {
            return ['error' => true, 'message' => 'Invalid send data.'];
        }

    }
}
