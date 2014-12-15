<?php
// danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_client_type
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
$log->debug("Entering CategoryToRelations.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$link_to_category = '';
	$link_to_other = '';
	// danzi.tn@20141212
	$query = "SELECT ".$table_prefix."_account.account_client_type AS category FROM ".$table_prefix."_account JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid  WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_account.accountid = ".$entity_id;
	$log->debug("CategoryToRelations.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$link_to_category = $adb->query_result($result,0,'category');
	}
	$autocomplete_return_function[$entity_id] = "return_category_to_relation($entity_id, \"$value\", \"$forfield\", \"$link_to_category\", \"$link_to_other\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting CategoryToRelations.php ...");
?>
