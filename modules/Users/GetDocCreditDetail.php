<?php
// danzi.tn@20140929 dettaglio con partita IVA
global $current_user, $adb;


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

$base_dc_url = "http://dc.rothoblaas.com/dcWebAgenti";
$PartIVA = $_POST["euvat"];
$sql = "SELECT doccredituser, doccreditpwd FROM vtiger_users WHERE vtiger_users.id = ?";
$result = $adb->pquery($sql,array($current_user->id));
$doccredituser = $adb->query_result($result, 0, 'doccredituser');
$doccreditpwd = $adb->query_result($result, 0, 'doccreditpwd');
if(empty($doccredituser) ) {
	echo "_EMPTY_USR_";
} else {
	$fields = array(
		'Password' => urlencode($doccreditpwd),
		'UserName' => urlencode($doccredituser)
	);
	$output = httpPost($base_dc_url, $fields);
	// $dcurl= $base_dc_url."/Home/UserLogOn";DCWSoggetti
	$dcurl= $base_dc_url."/DCWSheet/Andamentale?CodiceSoggetto=".$PartIVA;
	$response = $output[0];
	$header_size = $output[1];
	$header = substr($response, 0, $header_size);
	if(preg_match_all('|Set-Cookie: (.*);|U', $header, $cookies))
	{
		$response_cookie_str = implode(';', $cookies[1]);
		$response_cookies_arr = $cookies[1];
		$net_sessionid = $response_cookies_arr[0];
		$aspxauth = $response_cookies_arr[1];
		$net_sessionid_arr = explode('=',$net_sessionid);
		$aspxauth_arr = explode('=',$aspxauth);
		setcookie($net_sessionid_arr[0],$net_sessionid_arr[1],time()+3600,"/","rothoblaas.com");
		setcookie($aspxauth_arr[0],$aspxauth_arr[1],time()+3600,"/","rothoblaas.com");
		// echo $doccredituser."##".$doccreditpwd."##".$response_cookie_str;
		echo $dcurl;
	} else {
		echo "_EMPTY_COOKIE_";
	}
}
exit;
//crmv@20140324
?>