<?php 
namespace frontend\modules\jet\components;
use Yii;
use yii\base\Component;

class Logger extends component
{
	protected $handle;
	public function __construct($path='jet-common.log',$mode='a')
	{
        $file_path=Yii::getAlias('@webroot').'/var/jet/'.$path;
        $dir = dirname($file_path);
        if (!file_exists($dir)){
            mkdir($dir,0775, true);
        }
        $handle=fopen($file_path,$mode);
        $this->handle=$handle;
	}
	public function __destruct()
	{
       fclose($this->handle);
	}
	public function log($message=""){
		fwrite($this->handle,'\n'.$message);
	}	
}