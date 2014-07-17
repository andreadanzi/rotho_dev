<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;

//danzi.tn@20140717 creazione nuovo modulo Marketprices
$log->debug("Entering AccountToMarketprices.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$customer_cat = '';
	$country = '';
	$area_mng_name = '';
	$area_mng_no = '';
		
	$query = "SELECT  {$table_prefix}_account.area_mng_no, {$table_prefix}_account.area_mng_name, bill_country, cf_762 as category
							from {$table_prefix}_crmentity
							join {$table_prefix}_account on  {$table_prefix}_account.accountid = {$table_prefix}_crmentity.crmid
							join {$table_prefix}_accountscf on  {$table_prefix}_accountscf.accountid = {$table_prefix}_crmentity.crmid
							join {$table_prefix}_accountbillads on  {$table_prefix}_accountbillads.accountaddressid = {$table_prefix}_crmentity.crmid
							where {$table_prefix}_crmentity.crmid = ?";

	$log->debug("AccountToMarketprices.php customquery ".$query);
	$result = $adb->pquery($query,array($entity_id));
	if ($result && $adb->num_rows($result)>0) {
		$customer_cat = $adb->query_result($result,0,'category');
		$area_mng_name = $adb->query_result($result,0,'area_mng_name');
		$area_mng_no = $adb->query_result($result,0,'area_mng_no');
		$country = $adb->query_result($result,0,'bill_country');
	}
	$autocomplete_return_function[$entity_id] = "return_account_to_marketprices($entity_id, \"$value\", \"$forfield\", \"$customer_cat\", \"$country\", \"$area_mng_name\", \"$area_mng_no\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting AccountToMarketprices.php ...");
?>
