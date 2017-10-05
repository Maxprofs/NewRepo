<?php 

namespace frontend\modules\jet\components;
use yii\base\Component;

class Sendmail extends Component
{
	public static function installmail($email)
	{
		$mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => "Thank you for installing Shopify-Jet Integration app",
                    'bcc' => 'nidhirajput@cedcommerce.com,nehasingh@cedcommerce.com,swatishukla@cedcommerce.com,anupriyaverma@cedcommerce.com,james@cedcommerce.com,moattarraza@cedcoss.com,kshitijverma@cedcoss.com,satyaprakash@cedcoss.com,abhishekjaiswal@cedcoss.com,karshitbhargava@cedcoss.com'
                    ];
	    $mailer = new Mail($mailData,'email/installmail.html','php',true);
	    $mailer->sendMail();
	} 
	
	// Send mail If merchant un-installs the Jet-Integration app
	public static function uninstallmail($email,$data="")
	{
		$mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    //'data'=>$data,
                    'subject' => "It's Sad To See You Leave(shopify-jet)",
                    'bcc' => 'nidhirajput@cedcommerce.com,nehasingh@cedcommerce.com,swatishukla@cedcommerce.com,anupriyaverma@cedcommerce.com,james@cedcommerce.com,moattarraza@cedcoss.com,kshitijverma@cedcoss.com,satyaprakash@cedcoss.com,abhishekjaiswal@cedcoss.com,karshitbhargava@cedcoss.com'
                    ];
	        $mailer = new Mail($mailData,'email/uninstallmail.html','php',true);
	        $mailer->sendMail();
	}
	
	
	public static function ordermail($email,$reference_order_id,$merchant_order_id,$product_sku,$merchant_id)
	{
			$mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'You have an order from jet.com',
                    'bcc' => 'moattarraza@cedcoss.com,kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'reference_order_id' => $reference_order_id,
                    'merchant_order_id' => $merchant_order_id,
                    'merchant_id'=> $merchant_id,
                    'product_sku' => $product_sku
                    ];
	        $mailer = new Mail($mailData,'email/order.html','php',true);
	        $mailer->sendMail();
	}// sending Order mail completed
	
	public static function orderShipmentError($shopname, $merchant_order_id,$reason,$merchant_id)
	{
		$mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'Shipment Not Completed on Jet',
                    'bcc' => 'james@cedcommerce.com,ankitsingh@cedcoss.com,moattarraza@cedcoss.com,kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'reference_order_id' => $merchant_order_id,
                    'error' => $reason,
                    'merchant_id'=> $merchant_id
                    ];
	        $mailer = new Mail($mailData,'email/order-error.html','php',true);
	        $mailer->sendMail();
	}// Sending shipment not completed on jet mail 

	public static function orderRejectMail($merchant_id,$merchant_order_id,$reference_order_id,$Reason)
	{
		//$headers_mer .= 'Bcc: amitpandey@cedcoss.com' . chr(10);
		
			$etx_mer .='Please check the Jet Failed order Details ';

			$mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'Order not fetched from jet',
                    'bcc' => 'james@cedcommerce.com,ankitsingh@cedcoss.com,moattarraza@cedcoss.com,kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'reference_order_id' => $reference_order_id,
                    'error' => $reason,
                    'merchant_id'=> $merchant_id
                    ];
	        $mailer = new Mail($mailData,'email/orderRejectMail.html','php',true);
	        $mailer->sendMail();

	}// Sending Mails to client if sku not exist in shopify store

	public static function productStockMail($email,$data)
	{	
		$mailData = ['sender' => 'shopify@cedcommerce.com',
                'reciever' => $email,
                'email' => $email,
                'subject' => 'Product stock information on Jet.com',
                'bcc' => 'ankitsingh@cedcoss.com,moattarraza@cedcoss.com,kshitijverma@cedcoss.com',
                'data' => $data,
                'email' =>$email,
                ];
        $mailer = new Mail($mailData,'email/productStockMail.html','php',true);
        $mailer->sendMail();
	}
	
	// Send mail If merchant tring to schedule call Jet-Integration app
	public static function callSchedule($email='shopify@cedcommerce.com')
	{		
		$mailData = ['sender' => $email,
				'reciever' => 'shopify@cedcommerce.com',
				'email' => "jimmoore@cedcommerce.com",
				'subject' => "Callback request for shopify-jet",
				'bcc' => 'nidhirajput@cedcommerce.com,nehasingh@cedcommerce.com,swatishukla@cedcommerce.com,anupriyaverma@cedcommerce.com,james@cedcommerce.com,moattarraza@cedcoss.com,kshitijverma@cedcoss.com,abhishekjaiswal@cedcoss.com,karshitbhargava@cedcoss.com '
		];
		$mailer = new Mail($mailData,'email/callschedulemail.html','php',true);
		$mailer->sendMail();
	}
}
?>