<?php
function str_format(&$key,&$item){
	//$key = utf8_decode(decode_html($key));
	$key = utf8_encode(decode_html($key));
}
if (!function_exists('replaceSelectQuery')){
	function replaceSelectQuery($query,$replace = "count(*) AS count",$group_by=false)
	{
	    // Remove all the \n, \r and white spaces to keep the space between the words consistent. 
	    // This is required for proper pattern matching for words like ' FROM ', 'ORDER BY', 'GROUP BY' as they depend on the spaces between the words.
	    $query = preg_replace("/[\n\r\s]+/"," ",$query);
	    
	    //Strip of the current SELECT fields and replace them by "select count(*) as count"
	    // Space across FROM has to be retained here so that we do not have a clash with string "from" found in select clause
	    $query = "SELECT $replace ".substr($query, stripos($query,' FROM '),strlen($query));
	
	    //Strip of any "GROUP BY" clause
	//    if ($group_by){
	//    	if(stripos($query,'GROUP BY') > 0)
	//		$query = substr($query, 0, stripos($query,'GROUP BY'));
	//	}
	    //Strip of any "ORDER BY" clause
	    if(strripos($query,'ORDER BY') > 0)
		$query = substr($query, 0, strripos($query,'ORDER BY'));
	
	    //That's it
	    return( $query);
	}
}
function sanitize_array_sql(&$item,&$key){
	global $adb;
	if(is_string($item)) {
		$item = preg_replace("/[\n]+/","",$item);
		if($item == '') {
			$item = $adb->database->Quote($item);
		}
		else {
			$item = "'".$adb->sql_escape_string($item). "'";
		}
	} 
	if($item === null) {
		$item = "NULL";
	}
}

function getAccExternalCode($val){
	global $adb;
	
	if(trim($val) == '') return '';
	
	$sql="select accountid 
		  from vtiger_account 
		  inner join vtiger_crmentity 
			on vtiger_crmentity.crmid=vtiger_account.accountid 
			and deleted=0 
		  where external_code=?";
	$res=$adb->pquery($sql,array($val));
	if($res && $adb->num_rows($res) > 0){
		return $adb->query_result($res,0,'accountid');
	}
	else
		return '';
}

function get_smowner($val) {
	global $adb;

	if($val == '') return '1';
	
	$sql = "SELECT id FROM vtiger_users WHERE erp_code=?";
	$res = $adb->pquery($sql,array($val));
	if($res && $adb->num_rows($res) > 0){
		return $adb->query_result($res,0,'id');
	}else{
		return '1';
	}
}

//prendo l'assegnatario dell'azienda collegata
function get_acc_smowner($val) {
	global $adb;

	if($val == '') return '1';
	
	$accountid=getAccExternalCode($val);
	if($accountid != ''){
		$owner=getRecordOwnerId($accountid);
		if($owner['Users']!=''){
			return $owner['Users'];
		}
		else{
			return $owner['Groups'];
		}
	}
	else{
		return '1';
	}
}

function get_rating($val){
	if($val=='1'){
		$val='Active'; //mycrmv@38872
	}
	elseif($val=='2'){
		$val='Attivita Cessata';
	}
	
	return $val;
}

?>