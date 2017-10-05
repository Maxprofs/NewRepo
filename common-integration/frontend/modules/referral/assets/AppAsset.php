<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\modules\referral\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'frontend/modules/referral/assets/css/site.css',
        'frontend/modules/referral/assets/css/font-awesome.min.css',
        'frontend/modules/referral/assets/css/bootstrap.css',
        'frontend/modules/referral/assets/css/style.css',
    ];
    public $js = [
        'frontend/modules/referral/assets/js/bootstrap.min.js',
        'frontend/modules/referral/assets/js/custom.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
