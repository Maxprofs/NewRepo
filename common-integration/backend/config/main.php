<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
                    'reports' => [
                        'class' => 'backend\modules\reports\reports',
                    ],
                    'walmart' => [
                        'class' => 'backend\modules\walmart\reports',
                    ],
                    'gridview' => ['class' => 'kartik\grid\Module'],
                ],
/*    'mailchimp' => [
    'class' => 'sammaye\yiichimp\Mailchimp',
    'apikey' => '8c4e4e614a2f49abe06ac4792f355633-us13'
],*/
    'components' => [
        'user' => [
            'identityClass' => 'common\models\Admin',
            'enableAutoLogin' => true,
        		'identityCookie' => [
        				'name' => '_backendUser', // unique for backend
        		]
        ],
    /*'clientScript' => [
        'class' => 'CClientScript',
        // disable default yii scripts
        'scriptMap' => [
            'jquery.js'     => false,
        ],
        ],*/
        'i18n' => [
        'translations' => [
            'app' => [
                'class' => 'yii\i18n\PhpMessageSource',
                //'basePath' => '@app/messages',
            ],
            'kvgrid' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@app/messages',
            ],
        ],
        ],
    		'session' => [
    				'name' => 'PHPBACKSESSID',
    				'savePath' => sys_get_temp_dir(),
    		],
    		
    		'request' => [
    				// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    				'cookieValidationKey' => 'GVVGeetzzkgQiwgwOdVL',
    				'csrfParam' => '_backendCSRF',
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
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    	'MyDashboard'=>[
    			'class'=>'backend\components\MyDashboard',
    	],
        'ReportsDashboard'=>[
                'class'=>'backend\modules\reports\components\ReportsDashboard',
        ],
        'WalmartReportsDashboard'=>[
                'class'=>'backend\modules\walmart\components\ReportsDashboard',
        ],	
    	'mailer'=>[
    			'class'=>'yii\swiftmailer\Mailer',
    			'useFileTransport'=>'false'
    	],	
        'urlManager' => [
        //'class' => 'yii\web\UrlManager',
        'enablePrettyUrl' => true,
        'showScriptName' => false,
            'rules'=>[
            ],
        ], 
        'urlManagerFrontEnd' => [
	        'class' => 'yii\web\urlManager',
	        'baseUrl' => '/marketplace-integration/',
	        'enablePrettyUrl' => true,
	        'showScriptName' => false,
        ],
    ],
    'params' => $params,
];
