<?php
$ch = curl_init();
$postData = array(
    'login' => 'ahgryd',
    'password' => 'jadilahlegenda'
);

curl_setopt_array($ch, array(
    CURLOPT_URL => 'https://member.expireddomains.net/login/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_FOLLOWLOCATION => true,
    //CURLOPT_COOKIESESSION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7',
    CURLOPT_COOKIEJAR => 'cookie.txt',
    //CURLOPT_COOKIEFILE => 'cookie.txt',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST =>false,
    //CURLOPT_URL => 'https://member.expireddomains.net/domains/expiredcom/'
));


$output = curl_exec($ch);
curl_setopt($ch, CURLOPT_URL, 'https://member.expireddomains.net/domains/expiredcom/');
$content = curl_exec($ch);
echo $content;
//echo $output;

 ?>