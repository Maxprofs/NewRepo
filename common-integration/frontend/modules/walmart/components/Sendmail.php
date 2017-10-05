<?php 
namespace frontend\modules\walmart\components;

use yii\base\Component;

class Sendmail extends Component
{
    public static function installmail($email)
    {
        $mailData = [
                    'sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'Thanks for installing shopify walmart integration app',
                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'cc'=> 'karshitbhargava@cedcoss.com',
                ];
        $mailer = new Mail($mailData,'email/installmail.html','php',true);
        $mailer->sendMail();
    } 
    
    // Send mail If merchant un-installs the Walmart-Integration app
    //by shivam

    public static function uninstallmail($email)
    {
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => "It's Sad To See You Leave",
            'bcc' => 'moattarraza@cedcoss.com,kshitijverma@cedcoss.com,satyaprakash@cedcoss.com,abhishekjaiswal@cedcoss.com,karshitbhargava@cedcoss.com,shopify@cedcommerce.com'
        ];
        $mailer = new Mail($mailData,'email/uninstallmail.html','php',true);
        $mailer->sendMail();

    }
    public function shippedOrderMail($email,$orderPurchasedId,$sku){
        $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'You order has been shipped on Walmart.com',
                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'purchase_order_id' => $orderPurchasedId,
                    'product_sku' => $sku
                    ];
        $mailer = new Mail($mailData,'email/shippedOrderMail.html','php',true);
        $mailer->sendMail();
    }

    public function failedOrderMail($email,$orderPurchasedId,$sku){
            $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'You order not to be shipped on Walmart.com',
                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'purchase_order_id' => $orderPurchasedId,
                    'product_sku' => $sku
                    ];
            $mailer = new Mail($mailData,'email/failedOrderMail.html','php',true);
            $mailer->sendMail();
        
    }

    public function configurationMail($email){
            $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'You configuration setting is not set on Walmart.com',
                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    ];
            $mailer = new Mail($mailData,'email/configurationMail.html','php',true);
            $mailer->sendMail();
        
    }

    public function serverStatusMail($email,$errorMessage){
            $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'Server error acknowledgedment',
                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'errorMessage' => $errorMessage,
                    ];
            $mailer = new Mail($mailData,'email/serverStatusMail.html','php',true);
            $mailer->sendMail();
    }

    public function jetOrderNotFetchMail($email,$order_Id,$sku){
            $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'You order has been shipped on Walmart.com',
                    'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
                    'order_id' => $order_Id,
                    'sku' => $sku,
                    ];
            $mailer = new Mail($mailData,'email/jetOrdernotFetchMail.html','php',true);
            $mailer->sendMail();
    }

    public function productStockMail($email,$data){
            
            $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'Product stock information on Walmart.com',
                    'bcc' => 'kshitijverma@cedcoss.com,ankitsingh@cedcoss.com',
                    'data' => $data,
                    ];
            $mailer = new Mail($mailData,'email/productStockMail.html','php',true);
            $mailer->sendMail();
    }
    // by shivam

    public function neworderMail($email,$reference_order_id,$merchant_order_id,$product_sku){
        
        $data = ['reference_order_id'=>$reference_order_id,
                'merchant_order_id'=>$merchant_order_id,
                'product_sku'=>$product_sku
                    ];
            $mailData = ['sender' => 'shopify@cedcommerce.com',
                    'reciever' => $email,
                    'email' => $email,
                    'subject' => 'Product stock information on Walmart.com',
                    'bcc' => 'kshitijverma@cedcoss.com,ankitsingh@cedcoss.com',
                    'data' => $data,
                    ];
            $mailer = new Mail($mailData,'email/productStockMail.html','php',true);
            $mailer->sendMail();
    }
}
?>