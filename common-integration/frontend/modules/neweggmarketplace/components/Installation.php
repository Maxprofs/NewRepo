<?php
namespace frontend\modules\neweggmarketplace\components;

use Yii;
use yii\base\Component;
use frontend\modules\neweggmarketplace\components\Data;
use frontend\modules\neweggmarketplace\models\NeweggInstallation;
use frontend\modules\neweggmarketplace\models\NeweggRegistration;
use frontend\modules\neweggmarketplace\components\dashboard\Setupprogress;

class Installation extends Component
{
    const INSTALLATION_STATUS_COMPLETE = 'complete';
    const INSTALLATION_STATUS_PENDING = 'pending';

    public static $steps = [
        '1'=>[
            'name'=>'Registration',
            'template'=>'register.php'
        ],
        '2'=>[
            'name'=>'Enter Newegg Api',
            'template'=>'newegg-api.php'
        ],
        '3'=>[
            'name'=>'Import Products',
            'template'=>'import-products.php'
        ],
        '4'=>[
            'name'=>'Category Mapping',
            'template'=>'category-map.php'
        ],
        '5'=>[
            'name'=>'Attribute Mapping',
            'template'=>'attribute-map.php'
        ]
    ];

    public static function isInstallationComplete($merchant_id)
    {
        if(is_numeric($merchant_id)) {
            $query = "SELECT `status`,`step` FROM `newegg_installation` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
            $result = Data::sqlRecords($query,'one');
            if($result && isset($result['status'])) {
                return $result;
            } else {
                return false;
            }
        }
    }

    public static function getFirstStep()
    {
        return '1';
    }

    public static function getFinalStep()
    {
        return '5';
        /*return '4';*/
    }

    public static function getStepInfo($stepId)
    {
        if(isset(self::$steps[$stepId])) {
            $stepData = self::$steps[$stepId];
        } else {
            $stepData = ['error'=>'Invalid Step'];
        }
        return $stepData;
    }

    public static function completeInstallationForOldMerchants($merchant_id)
    {
        $model = NeweggInstallation::find()->where(['merchant_id'=>$merchant_id])->one();
        if(is_null($model))
        {
            $Setupprogress = new Setupprogress();
            if($Setupprogress->getApiStatus($merchant_id) && $Setupprogress->getCategoryMapStatus($merchant_id))
            {
                $model = new NeweggInstallation();
                $model->merchant_id = $merchant_id;
                $model->status = Installation::INSTALLATION_STATUS_COMPLETE;
                $model->step = self::getFinalStep();
                $model->save();
            }
            /*else
            {
                $step = self::getCompletedStepId($merchant_id);

                if(is_null($model)) {
                    $model = new WalmartInstallation();
                    $model->merchant_id = $merchant_id;
                }

                if($step == Installation::getFinalStep())
                    $model->status = Installation::INSTALLATION_STATUS_COMPLETE;
                else
                    $model->status = Installation::INSTALLATION_STATUS_PENDING;

                $model->step = $step;
                $model->save();
            }*/
        }
    }

    public static function getCompletedStepId($merchant_id, $current_step=0)
    {
        $Setupprogress = new Setupprogress();

        $step = '0';

        $registerStatus = false;
        if(self::getRegistrationStatus($merchant_id) && 1>$current_step) {
            $registerStatus = true;
            $step = '1';
        }

        $apiStatus = false;
        if($Setupprogress->getApiStatus($merchant_id) && $registerStatus && 2>$current_step) {
            $step = '2';
            $apiStatus = true;
        }

        $productImportStatus = false;
        if($Setupprogress->getProductImportStatus($merchant_id) && $apiStatus && 2>$current_step) {
            $step = '3';
            $productImportStatus = true;
        }

        $categoryMapStatus = false;
        if($Setupprogress->getCategoryMapStatus($merchant_id) && $productImportStatus && 3>$current_step) {
            $step = '4';
            $categoryMapStatus = true;
        }

        if($Setupprogress->getAttributeMapStatus($merchant_id) && $categoryMapStatus) {
            $step = '5';
        }

        return $step;
    }

    public static function getRegistrationStatus($merchant_id)
    {
        $isExist = NeweggRegistration::find()->where(['merchant_id'=>$merchant_id])->one();
        if(is_null($isExist))
            return false;
        else
            return true;
    }
}
