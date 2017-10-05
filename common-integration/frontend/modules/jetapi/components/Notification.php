<?php

namespace frontend\modules\jetapi\components;

use Yii;
use yii\base\Component;

class Notification extends Component
{
    private static $passphrase = '';
    public static $API_ACCESS_KEY = 'AAAArkR0B0w:APA91bEvr37ThOKEThuvVVRphs_qAElLWT-lhA3AoQ_nBHRDRS3_r_EjNaSWiprXVO_guDRjAZ9wwTztBw6nIKE2-zHZVwkvqQNDJuQvTQT5Uh2JI0oYjgzVmGbIP4WeI8jtiXUD56rVGM96oT4o7ppOMNlLNpqQKQ';

    public function iOS($data, $devicetoken) {

        /*print_r(openssl_get_cert_locations());
        die("kkk");*/
       
        $cert = __DIR__ . '/walmartShopify.pem';
        $deviceToken = $devicetoken;
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);
        $fp = stream_socket_client(
            'ssl://gateway.sandbox.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        // Create the payload body
        $body['aps'] = array(
            'alert' => array(
                'title' => $data['mtitle'],
                'body' => $data['mdesc'],
                'platform' => $data['platform'],
                'id' => $data['id'],
                'relation' => $data['relation']
             ),
            'sound' => 'default'
        );
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
        fclose($fp);
        if (!$result)
            return 'Message not delivered' . PHP_EOL;
        else
            return 'Message successfully delivered' . PHP_EOL;
    }
    public function android($data, $reg_id) {
            //$reg_id = 'cblS64ofByY:APA91bFT0vMQsoISEBT97uuz1fRI_3I2zRt-gt-O2rSzfJx36TwlovC9UWsTgC-MLa0ZgxAa--LE-g2a-cYuEWjBGMlQGAGtxHZ979K5-oappHibFjxFcYv9W-gpEWWEE6_tcLU1SyRV';
            $url = 'https://android.googleapis.com/gcm/send';
            //$url = 'https://gcm-http.googleapis.com/gcm/send';
            $message = array(
                'title' => $data['mtitle'],
                'message' => $data['mdesc'],
                'platform' => $data['platform'],
                'id' => $data['id'],
                'relation' => $data['relation'],
                'subtitle' => 'asasd',
                'tickerText' => 'asdsadasd',
                'msgcnt' => 1,
                'vibrate' => 1
            );
            
            $headers = array(
                'Authorization: key=' .self::$API_ACCESS_KEY,
                'Content-Type: application/json'
            );
    
            $fields = array(
                'registration_ids' => array($reg_id),
                'data' => $message,
            );

            return self::useCurl($url, $headers, json_encode($fields));
        }
    
    // Sends Push's toast notification for Windows Phone 8 users
    
    // Curl 
    public function useCurl($url, $headers, $fields = null) {
            // Open connection
       /* print_r($headers);
        echo "</br>";
        print_r($fields);*/
            $ch = curl_init();
            if ($url) {
                // Set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         
                // Disabling SSL Certificate support temporarly
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                if ($fields) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                }
         
                // Execute post
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('Curl failed: ' . curl_error($ch));
                }
         
                // Close connection
                curl_close($ch);
    
                return $result;
        }
    }
    
}