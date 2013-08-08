<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
$log->debug("Entering CategoryFromToRelations.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$link_from_category = '';
	$link_from_other = '';
	// cf_779 local, cf_762 ROTHO
	$query = "SELECT ".$table_prefix."_accountscf.cf_762 AS category FROM ".$table_prefix."_accountscf LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_crmentity.crmid  WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_accountscf.accountid = ".$entity_id;
	$log->debug("CategoryFromToRelations.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$link_from_category = $adb->query_result($result,0,'category');
	}
	$autocomplete_return_function[$entity_id] = "return_category_from_relation($entity_id, \"$value\", \"$forfield\", \"$link_from_category\",\"$link_from_other\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting CategoryFromToRelations.php ...");
?>
