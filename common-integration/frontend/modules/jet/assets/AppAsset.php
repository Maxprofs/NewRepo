<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\modules\jet\assets;

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
        //'web/css/site.css',
        //'css/jquery.treeview.css',
        //'css/jquery-checktree.css',
        //'frontend/modules/jet/assets/css/creative.css',
        'frontend/modules/jet/assets/css/jquery.datetimepicker.css',
        'frontend/modules/jet/assets/css/site.css',
        'frontend/modules/jet/assets/css/font-awesome.min.css',
        'frontend/modules/jet/assets/css/jQuery-plugin-progressbar.css',
        'frontend/modules/jet/assets/css/bootstrap-material-design.css',
        //'frontend/modules/jet/assets/css/bootstrap.css',
        'frontend/modules/jet/assets/css/pie-chart.css',
        'frontend/modules/jet/assets/css/owl.carousel.css',
        //'css/slick.css',
        //'css/slick-theme.css',
        'frontend/modules/jet/assets/css/style.css',
        'frontend/modules/jet/assets/css/introjs.css',
        'frontend/modules/jet/assets/css/intro-themes/introjs-nazanin.css',
        'frontend/modules/jet/assets/css/litebox.css',
        'frontend/modules/jet/assets/css/jquery-ui.css',
        'frontend/modules/jet/assets/css/jquery-ui-timepicker-addon.css'
    ];
    public $js = [
        //'js/jquery.touchSwipe.min.js',
        //'js/jquery-1.10.2.min.js',
        //['js/jquery.js', ['position'=>1]],
        'frontend/modules/jet/assets/js/bootstrap.min.js',
        'frontend/modules/jet/assets/js/owl.carousel.js',
        'frontend/modules/jet/assets/js/owl.carousel.min.js',
        //'js/jQuery-plugin-progressbar.js',
        'frontend/modules/jet/assets/js/custom.js',
        //'js/slick.js',
        'frontend/modules/jet/assets/js/intro.js',
        'frontend/modules/jet/assets/js/pie-chart.js',
        //'js/images-loaded.min.js',
        //'js/litebox.min.js',
        'frontend/modules/jet/assets/js/jquery-ui.js',
        //'frontend/modules/jet/assets/js/jquery-ui-timepicker-addon.js'

    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
