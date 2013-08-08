<?php
include_once 'modules/Inspections/Inspections_conf.php';
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
$log->debug("Entering CategoryToInspections.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$link_to_category = '';
	$link_to_other = '';
	$link_to_baselang = '';
	$link_to_extcode = '';
	$link_to_account_ownerid = '';
	$link_to_account_ownerid_display = '';
	// cf_799 local, ROTHO --> cf_762
	$query = "SELECT ".$table_prefix."_accountscf.".$cf_account_category." AS category, ".$table_prefix."_accountscf.".$cf_account_base_language." AS baselang, ".$table_prefix."_account.email1 AS email , ".$table_prefix."_account.external_code AS external_code  , ".$table_prefix."_crmentity.smownerid AS account_ownerid  , (".$table_prefix."_users.user_name +' ('+".$table_prefix."_users.first_name + ' ' + ".$table_prefix."_users.last_name + ')' ) as display_name 
		FROM ".$table_prefix."_accountscf
		LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_crmentity.crmid 
		LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
		LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid 
		WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_accountscf.accountid = ".$entity_id;
	$log->debug("CategoryToInspections.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$link_to_category = $adb->query_result($result,0,'category');
		$link_to_other = $adb->query_result($result,0,'email');
		$link_to_baselang = $adb->query_result($result,0,'baselang');
		$link_to_extcode = $adb->query_result($result,0,'external_code');
		$link_to_account_ownerid = $adb->query_result($result,0,'account_ownerid');
		$link_to_account_ownerid_display = $adb->query_result($result,0,'display_name');
	}
	$autocomplete_return_function[$entity_id] = "return_category_to_inspection($entity_id, \"$value\", \"$forfield\", \"$link_to_category\", \"$link_to_other\", \"$link_to_baselang\", \"$link_to_extcode\", \"$link_to_account_ownerid\", \"$link_to_account_ownerid_display\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting CategoryToInspections.php ...");
?>
