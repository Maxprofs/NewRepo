<?php 
namespace frontend\modules\walmart\components;
use Yii;
use yii\base\Component;

class Log extends Component
{
	private $_handle = null;

	public function __construct($file = 'common.log',$mode = 'a'){
		$file = Yii::getAlias('@webroot').'/var/'.$file;
		$this->_handle = fopen($file,$mode);
	}

	public function log($message){
		fwrite($this->_handle,$message.PHP_EOL);
	}
	public function __construct(){
		fclose($this->_handle);
	}
}