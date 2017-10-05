<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\modules\neweggcanada\assets;

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
        'frontend/modules/neweggcanada/assets/css/jquery.datetimepicker.css',
        'frontend/modules/neweggcanada/assets/css/site.css',
        'frontend/modules/neweggcanada/assets/css/font-awesome.min.css',
        'frontend/modules/neweggcanada/assets/css/jQuery-plugin-progressbar.css',
        'frontend/modules/neweggcanada/assets/css/bootstrap-material-design.css',
        'frontend/modules/neweggcanada/assets/css/bootstrap.css',
        'frontend/modules/neweggcanada/assets/css/pie-chart.css',
        'frontend/modules/neweggcanada/assets/css/owl.carousel.css',
        //'css/slick.css',
        //'css/slick-theme.css',
        'frontend/modules/neweggcanada/assets/css/style.css',
        'frontend/modules/neweggcanada/assets/css/introjs.css',
        'frontend/modules/neweggcanada/assets/css/intro-themes/introjs-nazanin.css',
        'frontend/modules/neweggcanada/assets/css/litebox.css',
        'frontend/modules/neweggcanada/assets/css/jquery-ui.css',
        'frontend/modules/neweggcanada/assets/css/jquery-ui-timepicker-addon.css'
    ];
    public $js = [
        //'js/jquery.touchSwipe.min.js',
        //'js/jquery-1.10.2.min.js',
        //['js/jquery.js', ['position'=>1]],
        'frontend/modules/neweggcanada/assets/js/bootstrap.min.js',
        'frontend/modules/neweggcanada/assets/js/owl.carousel.js',
        'frontend/modules/neweggcanada/assets/js/owl.carousel.min.js',
        //'js/jQuery-plugin-progressbar.js',
        'frontend/modules/neweggcanada/assets/js/custom.js',
        //'js/slick.js',
        'frontend/modules/neweggcanada/assets/js/intro.js',
        'frontend/modules/neweggcanada/assets/js/pie-chart.js',
        //'js/images-loaded.min.js',
        //'js/litebox.min.js',
        'frontend/modules/neweggcanada/assets/js/jquery-ui.js',
        'frontend/modules/neweggcanada/assets/js/jquery-ui-timepicker-addon.js'

    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
