<?php
namespace frontend\modules\neweggmarketplace\components;

use yii\base\Component;

class Sendmail extends Component
{
    public static function installmail($email)
    {
        $mailData = [
            'sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'Thanks for installing shopify Newegg integration app',
            'bcc' => 'ankitsingh@cedcoss.com,shivamverma@cedcoss.com',
        ];
        $mailer = new Mail($mailData, 'email/installmail.html', 'php', true);
        $mailer->sendMail();
    }

    // Send mail If merchant un-installs the Newegg-Integration app
    //by shivam

    public static function uninstallmail($email)
    {
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => "It's Sad To See You Leave",
            'bcc' => 'shivamverma@cedcoss.com,ankitsingh@cedcoss.com'
        ];
        $mailer = new Mail($mailData, 'email/uninstallmail.html', 'php', true);
        $mailer->sendMail();

    }


    /*public function configurationMail($email)
    {
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'You configuration setting is not set on Newegg.com',
            'bcc' => 'shivamverma@cedcoss.com,ankitsingh@cedcoss.com',
        ];
        $mailer = new Mail($mailData, 'email/configurationMail.html', 'php', true);
        $mailer->sendMail();

    }*/

    /*public function serverStatusMail($email, $errorMessage)
    {
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'Server error acknowledgedment',
            'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
            'errorMessage' => $errorMessage,
        ];
        $mailer = new Mail($mailData, 'email/serverStatusMail.html', 'php', true);
        $mailer->sendMail();
    }*/

    /*public function productStockMail($email, $data)
    {

        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'Product stock information on Newegg.com',
            'bcc' => 'kshitijverma@cedcoss.com,ankitsingh@cedcoss.com',
            'data' => $data,
        ];
        $mailer = new Mail($mailData, 'email/productStockMail.html', 'php', true);
        $mailer->sendMail();
    }*/

    // by shivam

    public static function fetchOrder($email, $ordernumber, $orderstatus)
    {
        $data = [
            'ordernumber' => $ordernumber,
            'orderstatus' => $orderstatus
        ];
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'Order information on newegg.com',
            'bcc' => 'shivamverma@cedcoss.com',
            'data' => $data,
        ];
        $mailer = new Mail($mailData, 'email/fetchOrder.html', 'php', true);
        $mailer->sendMail();
    }

    public static function neworderMail($email, $ordernumber, $shopify_order_id)
    {

        $data = [
            'ordernumber' => $ordernumber,
            'orderstatus' => $shopify_order_id
        ];
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'Order Create information on newegg.com',
            'bcc' => 'shivamverma@cedcoss.com',
            'data' => $data,
        ];
        $mailer = new Mail($mailData, 'email/order.html', 'php', true);
        $mailer->sendMail();
    }

    public static function orderError($email, $ordernumber, $error_reason)
    {

        $data = [
            'ordernumber' => $ordernumber,
            'ordererror' => $error_reason
        ];
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'Order Error information on newegg.com',
            'bcc' => 'shivamverma@cedcoss.com',
            'data' => $data,
        ];
        $mailer = new Mail($mailData, 'email/order-error.html', 'php', true);
        $mailer->sendMail();
    }

    public static function shipOrder($email, $ordernumber, $status)
    {

        $data = [
            'ordernumber' => $ordernumber,
            'status' => $status
        ];

        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'You order has been shipped on Newegg.com',
            'bcc' => 'shivamverma@cedcoss.com',
            'data' => $data
        ];
        $mailer = new Mail($mailData, 'email/shipOrder.html', 'php', true);
        $mailer->sendMail();
    }

    public static function failedshipment($email, $ordernumber, $status)
    {

        $data = [
            'ordernumber' => $ordernumber,
            'status' => $status
        ];

        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'You order has not been shipped on Newegg.com',
            'bcc' => 'shivamverma@cedcoss.com',
            'data' => $data
        ];
        $mailer = new Mail($mailData, 'email/shippedOrderMail.html', 'php', true);
        $mailer->sendMail();
    }

    public static function cancelOrder($email, $ordernumber, $status)
    {

        $data = [
            'ordernumber' => $ordernumber,
            'order_status' => $status
        ];

        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'You order not to be shipped on Newegg.com',
            'bcc' => 'shivamverma@cedcoss.com',
            'data' => $data
        ];
        $mailer = new Mail($mailData, 'email/cancelOrder.html', 'php', true);
        $mailer->sendMail();

    }

    /*public function jetOrderNotFetchMail($email,$order_Id,$sku){
        $mailData = ['sender' => 'shopify@cedcommerce.com',
            'reciever' => $email,
            'email' => $email,
            'subject' => 'You order has been shipped on Newegg.com',
            'bcc' => 'kshitijverma@cedcoss.com,satyaprakash@cedcoss.com',
            'order_id' => $order_Id,
            'sku' => $sku,
        ];
        $mailer = new Mail($mailData,'email/jetOrdernotFetchMail.html','php',true);
        $mailer->sendMail();
    }*/

}

?>