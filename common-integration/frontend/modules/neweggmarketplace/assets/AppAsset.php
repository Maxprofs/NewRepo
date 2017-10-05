<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\modules\neweggmarketplace\assets;

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
        'frontend/modules/neweggmarketplace/assets/css/jquery.datetimepicker.css',
        'frontend/modules/neweggmarketplace/assets/css/site.css',
        'frontend/modules/neweggmarketplace/assets/css/font-awesome.min.css',
        'frontend/modules/neweggmarketplace/assets/css/jQuery-plugin-progressbar.css',
        'frontend/modules/neweggmarketplace/assets/css/bootstrap-material-design.css',
        'frontend/modules/neweggmarketplace/assets/css/bootstrap.css',
        'frontend/modules/neweggmarketplace/assets/css/pie-chart.css',
        'frontend/modules/neweggmarketplace/assets/css/owl.carousel.css',
        //'css/slick.css',
        //'css/slick-theme.css',
        'frontend/modules/neweggmarketplace/assets/css/style.css',
        'frontend/modules/neweggmarketplace/assets/css/introjs.css',
        'frontend/modules/neweggmarketplace/assets/css/intro-themes/introjs-nazanin.css',
        'frontend/modules/neweggmarketplace/assets/css/litebox.css',
        'frontend/modules/neweggmarketplace/assets/css/jquery-ui.css',
        'frontend/modules/neweggmarketplace/assets/css/jquery-ui-timepicker-addon.css'
    ];
    public $js = [
        //'js/jquery.touchSwipe.min.js',
        //'js/jquery-1.10.2.min.js',
        //['js/jquery.js', ['position'=>1]],
        'frontend/modules/neweggmarketplace/assets/js/bootstrap.min.js',
        'frontend/modules/neweggmarketplace/assets/js/owl.carousel.js',
        'frontend/modules/neweggmarketplace/assets/js/owl.carousel.min.js',
        //'js/jQuery-plugin-progressbar.js',
        'frontend/modules/neweggmarketplace/assets/js/custom.js',
        //'js/slick.js',
        'frontend/modules/neweggmarketplace/assets/js/intro.js',
        'frontend/modules/neweggmarketplace/assets/js/pie-chart.js',
        //'js/images-loaded.min.js',
        //'js/litebox.min.js',
        'frontend/modules/neweggmarketplace/assets/js/jquery-ui.js',
        'frontend/modules/neweggmarketplace/assets/js/jquery-ui-timepicker-addon.js',
        'https://cdn.shopify.com/s/javascripts/currencies.js',

    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
