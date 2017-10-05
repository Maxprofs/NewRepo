<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
'id' => 'app-frontend',
'basePath' => dirname(__DIR__),
//'bootstrap' => ['debug'],
'modules' => [
	    /*'debug' => [
		'class' => 'yii\debug\Module',
		'allowedIPs' => ['182.74.41.196']
	    ],*/
	    'walmartapi' => [
            'class' => 'frontend\modules\walmartapi\Walmartapi',
       		 ],
        'apilogin' => [
            'class' => 'frontend\modules\apilogin\apilogin',
        ],
	    'walmart' => [
		    'class' => 'frontend\modules\walmart\Walmart',
	    ],
        'jet' => [
            'class' => 'frontend\modules\jet\Jet',
        ],
		'sears' => [
				'class' => 'frontend\modules\sears\Sears',
		],
        'neweggmarketplace' => [
            'class' => 'frontend\modules\neweggmarketplace\NeweggMarketplace',
        ],
        'neweggcanada' => [
            'class' => 'frontend\modules\neweggcanada\Neweggcanada',
        ],
        'referral' => [
            'class' => 'frontend\modules\referral\Refer',
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ]
],
// 'bootstrap' => ['log'],
'controllerNamespace' => 'frontend\controllers',
'aliases'=>[
    '@phpseclib'=>'@vendor/phpseclib',
    '@Xml'=>'@vendor/Xml',
],
'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                        'name' => '_frontendUser', // unique for frontend
                ]
        ],
   
        'session' => [
                'name' => 'PHPFRONTSESSID',
                'savePath' => sys_get_temp_dir(),
        ],
        'request' => [
                // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
                'cookieValidationKey' => 'jgyZnCOqZjZEUkBLhvat',
                'csrfParam' => '_frontendCSRF',
        ],        
        'Sendmail'=>[
                'class'=>'frontend\components\Sendmail',
        ], 
        'errorHandler' => [
            'errorAction' => '/walmart/site/error',
            'errorView' => __DIR__ . '/../views/errorHandler/errorNexception.php',
            'exceptionView' => __DIR__ . '/../views/errorHandler/errorNexception.php',
        ],
        'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => 
        [
        //'class' => 'yii\web\UrlManager',
        'enablePrettyUrl' => true,
        'showScriptName' => false,
            'rules'=>
            [
                'kashifa.com'=>'192.168.0.128',
                'walmart/sell-on-walmart'=>'/walmart/site/guide',
                'walmart'=>'/walmart/site/index',
                'walmart/pricing'=>'/walmart/site/pricing',
                'walmart/paymentplan'=>'/walmart/site/paymentplan',
                'newegg-marketplace/sell-on-newegg'=>'neweggmarketplace/site/guide',
                'jet/how-to-sell-on-jet-com'=>'/jet/site/guide',
                'jet'=>'/jet/site/index',
                'jet/pricing'=>'/jet/site/pricing',
                'jet/faq'=>'/jet/faq/index',
                'jet/paymentplan'=>'/jet/site/paymentplan',

        		//add sears pretty urls
        		'sears/sell-on-sears'=>'/sears/site/guide',
        		'sears'=>'/sears/site/index',
        		'sears/pricing'=>'/sears/site/pricing',
        		'sears/faq'=>'/sears/faq/index',
        		'sears/paymentplan'=>'/sears/site/paymentplan',
            ],
        ], 
    ],
    'params' => $params,        
];