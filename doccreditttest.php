<?php

function httpPost($url,$params)
{
  $postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= $k . '='.$v.'&'; 
   }
   $output="";
   rtrim($postData, '&');
	if (function_exists('curl_init'))
	{
		$ch = curl_init();  
	 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER, true); 
		curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
		$output=curl_exec($ch);   
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);
	}
    return array($output,$header_size);
 
}

$fields = array(
    'PWD' => 'test',
    'USR' => 'test'
);

$output = httpPost("http://doccredit.sedocfinance.net/DCT/Home/Login", $fields);

// print_r($response);

$header = substr($output[0], 0, $output[1]);
preg_match_all('|Set-Cookie: (.*);|U', $header, $cookies);   
$response_cookie = implode(';', $cookies[1]);
$response_cookies = $cookies[1];


print_r($response_cookies);


?>