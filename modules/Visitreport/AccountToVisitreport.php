<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log,$current_user;
// danzi.tn@20141217 nuova classificazione da report visite
$log->debug("Entering visitreportToMarketprices.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$customer_line = '---';
	$customer_client_type = '---';
	$customer_main_act = '---';
	$customer_sec_act = '---';
	$customer_brand = '---';
	$account_yearly_pot = '---';
	$area_intervento = '---';
	$country = '';
	$area_mng_name = '';
	$area_mng_no = '';
		
	$query = "SELECT  {$table_prefix}_account.area_mng_no, {$table_prefix}_account.area_mng_name, bill_country, {$table_prefix}_account.account_line, 
							{$table_prefix}_account.account_client_type,
							{$table_prefix}_account.account_main_activity,
							{$table_prefix}_account.account_sec_activity,
							{$table_prefix}_account.account_brand,
							{$table_prefix}_account.area_intervento,
							{$table_prefix}_account.account_yearly_pot,
							{$table_prefix}_users.agent_cod_capoarea,
							amuser.first_name + ' '+ amuser.last_name as agent_name_capoarea
							from {$table_prefix}_crmentity
							join {$table_prefix}_account on  {$table_prefix}_account.accountid = {$table_prefix}_crmentity.crmid
							join {$table_prefix}_accountscf on  {$table_prefix}_accountscf.accountid = {$table_prefix}_crmentity.crmid
							join {$table_prefix}_accountbillads on  {$table_prefix}_accountbillads.accountaddressid = {$table_prefix}_crmentity.crmid
							left join {$table_prefix}_users on {$table_prefix}_users.id = ?
							LEFT JOIN {$table_prefix}_users as amuser on amuser.erp_code = {$table_prefix}_users.agent_cod_capoarea AND {$table_prefix}_users.agent_cod_capoarea <> ''
							where  {$table_prefix}_crmentity.crmid = ?";

	$log->debug("visitreportToMarketprices.php customquery ".$query);
	$result = $adb->pquery($query,array($current_user->id,$entity_id));
	if ($result && $adb->num_rows($result)>0) {
		$customer_line = $adb->query_result($result,0,'account_line');
		$customer_client_type = $adb->query_result($result,0,'account_client_type');
		$customer_main_act = $adb->query_result($result,0,'account_main_activity');
		$customer_sec_act = $adb->query_result($result,0,'account_sec_activity');
		$customer_brand = $adb->query_result($result,0,'account_brand');
		$account_yearly_pot = $adb->query_result($result,0,'account_yearly_pot');
		$area_intervento = $adb->query_result($result,0,'area_intervento');
		$area_mng_name = $adb->query_result($result,0,'area_mng_name');
		$area_mng_no = $adb->query_result($result,0,'area_mng_no');
		$country = $adb->query_result($result,0,'bill_country');
		$agent_cod_capoarea = $adb->query_result($result,0,'agent_cod_capoarea');
		$agent_name_capoarea = $adb->query_result($result,0,'agent_name_capoarea');
		if( empty($area_mng_no)) {
			$area_mng_no = $agent_cod_capoarea;
			$area_mng_name = $agent_name_capoarea;
		}
	}
	$autocomplete_return_function[$entity_id] = "return_visitreport_to_account($entity_id, \"$value\", \"$forfield\", \"$customer_line\", \"$customer_client_type\", \"$customer_main_act\", \"$customer_sec_act\", \"$customer_brand\",  \"$account_yearly_pot\", \"$area_intervento\", \"$country\", \"$area_mng_name\", \"$area_mng_no\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting visitreportToMarketprices.php ...");
?>