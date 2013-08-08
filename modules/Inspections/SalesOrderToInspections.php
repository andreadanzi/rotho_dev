<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log,$current_user;
$log->debug("Entering SalesOrderToInspections.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$salesorder_display_date = '';
	$link_to_other = '';
	// cf_779 local, cf_762 ROTHO data_ordine_ven duedate
	$query = "SELECT ".$table_prefix."_salesorder.data_ordine_ven AS salesorder_date FROM ".$table_prefix."_salesorder LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_crmentity.crmid  WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_salesorder.salesorderid = ".$entity_id;
	$log->debug("SalesOrderToInspections.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$salesorder_date = $adb->query_result($result,0,'salesorder_date');
		$cur_date_val = getValidDisplayDate($salesorder_date);
		$date_value = explode(' ',$cur_date_val);
		$salesorder_display_date = $date_value [0];
	}
	$autocomplete_return_function[$entity_id] = "return_salesorder_to_inspection($entity_id, \"$value\", \"$forfield\", \"$salesorder_display_date\", \"$link_to_other\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting SalesOrderToInspections.php ...");
?>