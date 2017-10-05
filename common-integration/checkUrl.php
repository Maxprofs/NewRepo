 <?php
$curl = curl_init();
	

// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'http://cedcommerce.com/shopify/frontend/shopifywebhook/productupdate',
    CURLOPT_USERAGENT => 'Codular Sample cURL Request'
));


if(!curl_exec($curl)){
    die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
}
$res = curl_exec($curl);
print_r($res);