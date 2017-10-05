<?php 
namespace frontend\modules\jet\components;
use Mailgun\Mailgun;
use Mandrill;
use yii\base\Component;
class MailSources extends Component
{

	public function send($data,$html,$source){
		$subject = isset($data['subject'])?$data['subject']:'';
		$reciever = isset($data['reciever'])?$data['reciever']:'';
		$sender = isset($data['sender'])?$data['sender']:'';
		if($this->_debug)
		{
			$error = '';
			$error = $sender == ''?' Sender Missing;':'';
			$error .= $reciever == ''?' Reciever Missing;':'';
			if($error!='')
				$this->log($error);
		}
		
		if($source=='mailgun'){
			$emails = isset($data['bcc'])?explode(',',$data['bcc']):[];
			$bccEmails = '';
			foreach($emails as $key => $bccEmail){
				$emails[$key] = $bccEmail."<{$bccEmail}>";
			}
			$bccEmails = implode(',',$emails); 
			return $this->mailgunMailer($sender,$reciever,$bccEmails,$subject, $html);
		}
		else
		if($source=='mailchimp'){
			$emails = isset($data['bcc'])?explode(',',$data['bcc']):[];
			$bccEmails = '';
			foreach($emails as $key => $bccEmail){
				$emails[$key] = $bccEmail."<{$bccEmail}>";
			}
			$bccEmails = implode(',',$emails); 
			return $this->mailchimpMailer($sender,$reciever,$bccEmails,$subject, $html);
		}
		else
		if($source=='php')
		{
			$headers = "MIME-Version: 1.0" . chr(10);
			$headers .= "Content-type:text/html;charset=iso-8859-1" . chr(10);
			$headers .= 'From: '.$sender . chr(10);
			$bccEmails = isset($data['bcc'])?explode(',',$data['bcc']):[];
			foreach($bccEmails as $bccEmail){
				$headers .= 'Bcc: '.$bccEmail. chr(10);
			}
			return $this->phpMailer($reciever,$subject, $html, $headers);
		}
	}
	public function phpMailer($reciever,$subject, $html, $headers){
		if(mail($reciever,$subject, $html, $headers)){
			return true;
		}
		else
		{
			return false;
		}
	}
	public function mailgunMailer($sender,$reciever,$bccEmails,$subject, $html){
		try{
			die('test');
			$mgClient = new Mailgun('key-081d780ed03cf89ca6c10a908b37d7b1');
			$domain = "sandboxaa397a12e02f48fca42f0defd603324d.mailgun.org";
			$reciever = 'satyaprakash@cedcoss.com';
			$mailData = array(
			    'from'    => $sender.' <'.$sender.'>',
			    'to'      => $reciever.' <'.$reciever.'>',
			    'subject' => $subject,
			    'html'    => $html
			);
			if($bccEmails!=''){
				$mailData['bcc'] = $bccEmails;
			}
			# Make the call to the client.
			$result = $mgClient->sendMessage($domain,$mailData );
			
			if($result->http_response_body && $result->http_response_code==200)
			{
				return true;
			}
			else
			{
				$this->log(print_r($result,true));
				return false;
			}
		}
		catch(Exception $e)
		{
			$this->log($e->getMessage());
		}
		
	}
	public function mailchimpMailer($sender,$reciever,$bccEmails,$subject, $html){
		try{

			$mandrill = new Mandrill('DE7GGhsxhFYrldYLF-oPqg');
			$message = array(
		        'html' => '<p>Example HTML content</p>',
		        'text' => 'Example text content',
		        'subject' => 'Test',
		        'from_email' => 'shopify@cedcommerce.com',
		        'from_name' => 'satyaprakash',
		        'to' => array(
		            array(
		                'email' => 'satyaprakash@cedcoss.com',
		                'name' => 'satyaprakash@cedcoss.com',
		                'type' => 'to'
		            )
		        ),
		        'headers' => array('Reply-To' => 'satyaprakash@cedcoss.com'),
		        'important' => false,
		        'track_opens' => null,
		        'track_clicks' => null,
		        'auto_text' => null,
		        'auto_html' => null,
		        'inline_css' => null,
		        'url_strip_qs' => null,
		        'preserve_recipients' => null,
		        'view_content_link' => null,
		        'bcc_address' => 'satyaprakash@cedcoss.com',
		        'tracking_domain' => null,
		        'signing_domain' => null,
		        'return_path_domain' => null,
		        'merge' => true,
		        'merge_language' => 'mailchimp',
		        'global_merge_vars' => array(
		            array(
		                'name' => 'merge1',
		                'content' => 'merge1 content'
		            )
		        ),
		        'merge_vars' => array(
		            array(
		                'rcpt' => 'satyaprakash@cedcoss.com',
		                'vars' => array(
		                    array(
		                        'name' => 'satyaprakash@cedcoss.com',
		                        'content' => 'satyaprakash@cedcoss.com'
		                    )
		                )
		            )
		        ),
		        'tags' => array('Testing'),
		        'google_analytics_domains' => array('cedcommerce.com'),
		        'google_analytics_campaign' => 'shopify@cedcommerce.com',
		        'metadata' => array('website' => 'www.cedcommerce.com'),
		        'recipient_metadata' => array(
		            array(
		                'rcpt' => 'shopify@cedcommerce.com',
		                'values' => array('user_id' => 123456)
		            )
		        ),
		    );
		    $async = false;
		    $ip_pool = 'Main Pool';
		    $send_at = date('Y-m-d H:i:s');

		    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
		    var_dump($result);
		    die;

			$to_emails = array($reciever);
			$to_names = array($reciever);

			$message = array(
			    'html'=>$html,
			    'text'=>'',
			    'subject'=>$subject,
			    'from_name'=>$sender,
			    'from_email'=>$sender,
			    'to_email'=>$to_emails,
			    'to_name'=>$to_names
			);

			$tags = array($subject);
			$apiKey = '8c4e4e614a2f49abe06ac4792f355633-us13';
			$params = array(
			    'apikey'=>$apiKey,
			    'message'=>$message,
			    'track_opens'=>true,
			    'track_clicks'=>false,
			    'tags'=>$tags
			);
			$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
			$url = "https://{$dataCenter}.sts.mailchimp.com/1.0/SendEmail";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params));
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$result = curl_exec($ch);
			echo $result;
			echo curl_error($ch);;
			curl_close ($ch);
			var_dump($result);
			$data = json_decode($result);

			var_dump($data);
 
			die;
		}
		catch(Exception $e)
		{
			$this->log($e->getMessage());
		}
		
	}
}