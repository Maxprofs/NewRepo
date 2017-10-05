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
    'bootstrap' => ['debug'],
	    'modules' => [
	    'debug' => [
	    	'class' => 'yii\debug\Module',
	    	'allowedIPs' => ['182.74.41.196']
	    ]
    ],
   // 'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
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
    				'baseUrl' => 'http://localhost/walmart',
                    'cookieValidationKey' => 'jgyZnCOqZjZEUkBLhvat',
    				'csrfParam' => '_frontendCSRF',
    		],
    		
         	/* 'log' => [
	            'traceLevel' => YII_DEBUG ? 3 : 0,
	            'targets' => [
	                [
	                    'class' => 'yii\log\FileTarget',
	                    'levels' => ['error', 'warning'],
	                ],
	            ],
        	], */
    		 'Sendmail'=>[
    				'class'=>'frontend\components\Sendmail',
    		], 
	        'errorHandler' => [
	            'errorAction' => 'site/error',
	        ],
    		/* 'urlManager' => [
    				'baseUrl' => '/frontend/web',
    				'enablePrettyUrl' => true,
    				'showScriptName' => false,
    				'rules' => [
                        'how-to'=>'site/guide',
                    ]
    		] */
    		/* 'view' =>[
    				'theme' => [
    						 //'pathMap' => ['@app/views' => '@app/themes/material-default'],
    						//'baseUrl'   => '@web/../themes/material-default',
    						'pathMap' => ['@app/views' => '@app/themes/material-default'],
    						'baseUrl' => '@web/themes/material-default',
    						'basePath' => '@app/assets/themes/material-default',
    						//'css'=>'@app/assets/themes/material-default/css', 
    						'pathMap' => [
    							
    							'@app/views' =>[
    									
    										'@app/themes/material-default',

    										'@app/themes/material-default'
    									
    								]
    							
    						],
    						
            'baseUrl' => '@web/../themes/material-default',
    						
    				],
    		],*/
		'log' => [
		'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
				'class' => 'yii\log\FileTarget',
				'levels' => ['error', 'warning'],
				],
			],
		],
        'urlManager' => [
        //'class' => 'yii\web\UrlManager',
        'enablePrettyUrl' => true,
        'showScriptName' => false,
            'rules'=>[
                'how-to-sell-on-jet-com'=>'/site/guide',
                'pricing'=>'/site/pricing',
            ],

        ], 
    ],
    'params' => $params,
		
];
