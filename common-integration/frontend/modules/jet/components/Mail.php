<?php 
namespace frontend\modules\jet\components;
use Yii;

class Mail extends MailSources
{
	private $_data = null;
	private $_template = null;
	public $_debug = false;
	private $_html = null;
	private $_handle;
	private $_source;
	private $_tracking = false;
	public function __construct($data,$template,$source = 'php',$debug = false)
	{
		$data['domain'] = 'https://shopify.cedcommerce.com/';
		$this->_data = $data;

		$this->_template = $template;
		$this->_debug = $debug;
		$this->insertTemplate($this->_template);
		$this->_source = $source;
		if($this->_debug)
		{
			$dir = Yii::getAlias('@webroot').'/var/jet';
			if(!file_exists($dir)){
                mkdir($dir,0775, true);
            }
			$this->_handle = fopen($dir.'/mail.log','a');
			if(isset($this->_data['html_content']) && !empty($this->_data['html_content'])){
               $data['html_content'] = 'yes';
            }
			$this->log(print_r($data,true));
		}
	}

	public function setTracking($tracking)
	{
		$this->_tracking = $tracking;
	}
	/* function for processing email template */
	private function processedTemplate(){

		$file = $this->getFile();
		if($file){
			$html = file_get_contents($file);
			$html1 = preg_replace_callback(
		        '/{{ var (.*?) }}/',
		       /* '/(\{{)(.*?)(\}})/',*/
		        function ($matches) {
		        	/*return $this->_condition($matches);*/
		            return $this->_replaceVariable($matches);
		        },
		        $html
		    );
		    return $html1;
		}
		
		
	}
		/* function for processing email content */
	private function htmlTemplate(){

			$html = $this->_data['html_content'];
			$html1 = preg_replace_callback(
		        '/{{ var (.*?) }}/',
		       /* '/(\{{)(.*?)(\}})/',*/
		        function ($matches) {
		        	/*return $this->_condition($matches);*/
		            return $this->_replaceVariable($matches);
		        },
		        $html
		    );
		    return $html1;
		
		
	}
	/* function for conditional data */
	private function _condition($matches){
		$value = '';
		if(isset($matches[2])){
			$matches[2] = trim($matches[2]);
			if(strpos($matches[1], '.')!==false){
				$value = $this->getNestedValue(explode('.',$matches[1]),$this->_data);
			}
			else
			{
				$value = isset($this->_data[$matches[1]]) ? $this->_data[$matches[1]] : '';
			}
		}

		if(is_string($value)){
			return $value;
		}elseif(is_array($value)){
			return json_encode($value);
		}
		else
		{
			return $value;
		}


	}

	/* function for replacing template variable with correct value */
	private function _replaceVariable($matches){
		$value = '';

		if(isset($matches[1])){
			$matches[1] = trim($matches[1]);
			if(strpos($matches[1], '.')!==false){
				$value = $this->getNestedValue(explode('.',$matches[1]),$this->_data);
			}
			else
			{
				$value = isset($this->_data[$matches[1]]) ? $this->_data[$matches[1]] : '';
			}
		}
		if(is_string($value)){
			return $value;
		}elseif(is_array($value)){
			return json_encode($value);
		}
		else
		{
			return $value;
		}

	}

	/*function for getting nested value for template variable */
	public function getNestedValue($arr,$data){
		$value = '';
		foreach($arr as $key){
			if(isset($data[$key])){
				$data = $data[$key];
				$value = $data;
			}
			else
			{
				$value = '';
				break;
			}
		}
		return $value;
	}

	/* function for sending email  */
	public function sendMail()
	{
		try
		{
			if(isset($this->_data['merchant_id']) && !empty($this->_data['merchant_id']))
			{
				$template = str_replace('.html', '', $this->_template);
				$query = "SELECT `value` FROM  `jet_config` WHERE `merchant_id`='".$this->_data['merchant_id']."' AND `data`='".$template."'";
                $emailTemplatevalue = Data::sqlRecords($query,"one");
              	
                if($emailTemplatevalue['value'] ==1){
                	if(isset($this->_data['html_content']) && !empty($this->_data['html_content'])){
                		$html = $this->htmlTemplate();
                	}
                	else{
                		$html = $this->processedTemplate();
                	}
					$html = $this->trackingMail($html);
					if($this->send($this->_data,$html,$this->_source)){
						return true;
					}
					else
					{
						$this->log('Unable to send mail.');
						return false;
					}
				}
				else
				{
					$this->log('Mail not allowed.'.$query);
					return false;
				}
			}
			else{

				$html = $this->processedTemplate();

				if($this->send($this->_data,$html,$this->_source)){
					return true;
				}
				else
				{
					$this->log('Unable to send mail.');
					return false;
				}
			}
		}
		catch(Exception $e){
			$this->log($e->getMessage());
		}
	}

	public function log($message){
		fwrite($this->_handle,date(DATE_RFC822).' : '.$message.PHP_EOL);
	}

	/* function for getting template file path */
	public function getFile(){
		$serach = [];
		$file = Yii::getAlias('@webroot').'/views/templates/'.$this->_template;
		$serach[]=$file;
		if(file_exists($file)){
			return $file;
		}
		$file = Yii::getAlias('@webroot').'/frontend/views/templates/'.$this->_template;
		$serach[]=$file;
		if(file_exists($file)){
			return $file;
		}
		$file = dirname(Yii::getAlias('@webroot')).'/frontend/views/templates/'.$this->_template;
		$serach[]=$file;
		if(file_exists($file)){
			return $file;
		}
		$file = dirname(Yii::getAlias('@webroot')).'/frontend/modules/jet/views/templates/'.$this->_template;
		$serach[]=$file;
		if(file_exists($file)){
			return $file;
		}
		$file = Yii::getAlias('@webroot').'/frontend/modules/jet/views/templates/'.$this->_template;
		$serach[]=$file;
		if(file_exists($file)){
			return $file;
		}
		else
		{
			$this->log('Template Not Found : '.print_r($serach,true));
			return false;
		}

	}
	public function insertTemplate($template)
	{
		$query="SELECT `template_path` FROM `jet_email_template` WHERE template_path='".$template."'";
	    $allData = Data::sqlRecords($query, 'one');

	  if(empty($allData))
	  {
	    if(empty($allData))
	    {
	    	$mainTitle =str_replace('.html', '',$template);
	    	$title =str_replace('email/', '',$mainTitle);
	    	
	    	$model = Data::sqlRecords("INSERT INTO `jet_email_template` (`template_title`,`template_path`,`custom_title`) VALUES ('".$title."','".$template."','".$title."')", 'all','insert');
	    	if(isset($this->_data['merchant_id'])){
	    		$model = Data::sqlRecords("INSERT INTO `jet_config` (`data`,`value`,`merchant_id`) VALUES ('".$mainTitle."','1','".$this->_data['merchant_id']."')", 'all','insert');
	    		}
			}
		}
    }


    public function trackingMail($html){
    	if($this->_tracking){
			if($html){
				$this->saveEditContent($this->_tracking,$html);
				$html1 = preg_replace_callback(
			        '/<body[^>]*>(.*?)<\/body>/is',
			        function ($matches) {
			            return $this->_addhtmlImage($matches);
			        },
			        $html
			    );
			    return $html1;
			}
    		
    		
    		
    	}
    	return $html;
    }


    /*function for adding image tag in template file*/
   private function _addhtmlImage($matches){
   		$path = Yii::getAlias('@webjeturl')."/admin/reports/client-email-read/index?id=".$this->_tracking;
	   	$data = $matches[1].'<img src="'.$path.'"'; 
	    return $data;
	}
	/*function for save edit content */
	private function saveEditContent($id,$htmlContent){
      $file_dir = dirname(\Yii::getAlias('@webroot')).'/var/email';
      if (!file_exists($file_dir)){
          mkdir($file_dir,0775, true);
      }
      $filenameOrig="";
      $filenameOrig=$file_dir.'/'.$id.'.html';
      $fileOrig="";
      $fileOrig=fopen($filenameOrig,'w+');
      fwrite($fileOrig,$htmlContent);

      fclose($fileOrig);
    }
}