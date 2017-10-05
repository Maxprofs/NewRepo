<?php 
namespace frontend\modules\jet\components;

use frontend\modules\jet\components\Dashboard\Setupprogress;
use frontend\modules\jet\components\Data;
use frontend\modules\jet\models\JetInstallation;
use yii\base\Component;

class Installation extends Component
{
    const INSTALLATION_STATUS_COMPLETE = 'complete';
    const INSTALLATION_STATUS_PENDING = 'pending';

    public static $steps = [
                            '1'=>[
                                    'name'=>'Registration',
                                    //'url'=>'jet-install/Registration',
                                    'template'=>'register.php'
                                 ],

                            '2'=>[
                                    'name'=>'Enter Test Api',
                                    //'url'=>'jet-install/test-api',
                                    'template'=>'test-api.php'
                                 ],
                            '3'=>[
                                    'name'=>'Enter Live Api',
                                    //'url'=>'jet-install/Live-Api',
                                    'template'=>'live-api.php'
                                 ],
                            /*'4'=>[
                                    'name'=>'Pricing Plan',
                                    //'url'=>'jet-install/Registration',
                                    'template'=>'pricing.php'
                                 ],*/
                            '4'=>[
                                    'name'=>'Import Products',
                                    //'url'=>'jet-install/Import-Products',
                                    'template'=>'import-products.php'
                                 ],
                            '5'=>[
                                    'name'=>'Category Mapping',
                                    //'url'=>'jet-install/Category-Mapping',
                                    'template'=>'category-map.php'
                                 ],
                            '6'=>[
                                    'name'=>'Attribute Mapping',
                                    //'url'=>'jet-install/Attribute-Mapping',
                                    'template'=>'attribute-map.php'
                                 ]
                           ];

    public static function isInstallationComplete($merchant_id)
    {
        if(is_numeric($merchant_id)) {
            $query = "SELECT `status`,`step` FROM `jet_installation` WHERE `merchant_id`=".$merchant_id." LIMIT 0,1";
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
        return '6';
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
        $model = JetInstallation::find()->where(['merchant_id'=>$merchant_id])->one();

        if(is_null($model))
        {
            if(Setupprogress::getLiveApiStatus($merchant_id) && Setupprogress::getCategoryMapStatus($merchant_id)) 
            {
                $model = new JetInstallation();
                $model->merchant_id = $merchant_id;                
                $model->status = Installation::INSTALLATION_STATUS_COMPLETE;
                $model->step = '6';
                $model->save();
            } 
        }
    }

    public static function getCompletedStepId($merchant_id)
    {
        $step = '1';
        /*$testApiStatus = false;
        if(Setupprogress::getTestApiStatus($merchant_id)) {
            $step = '2';
            $testApiStatus = true;
        }
        */
        $liveApiStatus = false;
        if(Setupprogress::getLiveApiStatus($merchant_id)) {
            $step = '3';
            $liveApiStatus = true;
        }

        $productImportStatus = false;
        if(Setupprogress::getProductImportStatus($merchant_id) && $liveApiStatus) {
            $step = '4';
            $productImportStatus = true;
        }

        $categoryMapStatus = false;
        if(Setupprogress::getCategoryMapStatus($merchant_id) && $productImportStatus) {
            $step = '5';
            $categoryMapStatus = true;
        }
        /*
        if(Setupprogress::getAttributeMapStatus($merchant_id) && $categoryMapStatus) {
            $step = '6';
        }*/
        return $step;
    }
}
