<?php
ini_set('max_execution_time', "1800");
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
error_reporting(E_ALL);

//change app uri
/*if(!defined('IS_ALLOW'))
{
	$sqlresult="";
	$result="";
	$connection=mysql_connect("localhost","root","");
	if($connection)
	{
		mysql_select_db("walmart");
	}
	if(isset($_GET['user_id']) && $_GET['user_id']) 
	{ 
		$query="SELECT id,is_allow FROM `user` WHERE id='".$_GET['user_id']."'";
		$sqlresult=mysql_query($query);
		if ($sqlresult)
		{
			$result = mysql_fetch_assoc($sqlresult);
			if($result['is_allow']=='jet-walmart' && !isset($_GET['is_allow']))
			{
				header('Location: http://localhost/jet/register.php'); 
				exit(0); 
			}
			else
			{
				define('IS_ALLOW',$result['is_allow']);
			}
		}
	}
	/*elseif(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],'jet') !== false){
		define('IS_ALLOW','jet');
	}
	elseif(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],'walmart') !== false){
		define('IS_ALLOW','walmart');
	}
	else{
		header('Location: http://localhost/jet/register.php');  
		exit(0);
	}
}*/
$url="";
if($_SERVER['HTTP_HOST']=="shopify.cedcommerce.com" && $_SERVER['REQUEST_SCHEME']=="http"){
	if($_SERVER['HTTPS']!="on") {
		$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		header("Location:$redirect");
	}
}
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/frontend/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/common/config/main.php'),
    require(__DIR__ . '/common/config/main-local.php'),
    require(__DIR__ . '/frontend/config/main.php'),
    require(__DIR__ . '/frontend/config/main-local.php')
);
//Yii::setAlias('phpseclib', __DIR__  . '/vendor/phpseclib');

if(file_exists(__DIR__ . '/maintenance.flag') && !isset($_GET['maintenanceprocess'])){
	$config['controllerNamespace'] = 'maintenance\controllers';
	$modules = [
					'debug'=>$config['modules']['debug'],
					'gii'=>$config['modules']['gii']
				];
	$config['modules'] = $modules;
	$config['components']['errorHandler']['errorAction'] = 'site/error';
}

$application = new \frontend\Application($config);
$application->run();