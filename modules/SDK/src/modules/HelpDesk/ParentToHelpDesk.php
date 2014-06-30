<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
//crmv@36406
// danzi.tn@20140630 decodifica area_mng_name e area_mng_no
global $default_charset;
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	
	global $adb, $table_prefix;
	$result1 = $adb->pquery("select {$table_prefix}_users.id 
							, {$table_prefix}_account.area_mng_no, {$table_prefix}_account.area_mng_name
							from {$table_prefix}_crmentity
							inner join {$table_prefix}_users on {$table_prefix}_users.id = {$table_prefix}_crmentity.smownerid
							left join {$table_prefix}_account on  {$table_prefix}_account.accountid = {$table_prefix}_crmentity.crmid
							where {$table_prefix}_users.status = ? and {$table_prefix}_crmentity.crmid = ?",array('Active',$entity_id));
	$userid = '';
	$area_mng_name = '';
	$area_mng_no = '';
	if ($result1 && $adb->num_rows($result1)) {
		$userid = $adb->query_result($result1,0,'id');
		$area_mng_name = $adb->query_result($result1,0,'area_mng_name');
		$area_mng_no = $adb->query_result($result1,0,'area_mng_no');
	}
	
	global $autocomplete_return_function;
	$autocomplete_return_function[$entity_id] = "return_parent_to_helpdesk($entity_id, \"$value\", \"$forfield\", \"$userid\", \"$area_mng_name\", \"$area_mng_no\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>";
}
//crmv@36406e
?>