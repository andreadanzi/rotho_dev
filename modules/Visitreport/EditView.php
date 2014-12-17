<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
//crmv@30447
// danzi.tn@20141217 nuova classificazione da report visite

// if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] =='Accounts' && isset($_REQUEST['parent_id']) && $_REQUEST['parent_id'] > 0) {
if(isset($_REQUEST['return_module']) && 
	$_REQUEST['return_module'] =='Visitreport' && 
	isset($_REQUEST['return_action']) &&
	$_REQUEST['return_action'] =='DetailView' && 
	isset($_REQUEST['record']) && 
	$_REQUEST['record'] > 0) {
	global $adb, $table_prefix, $current_user;
	$record = vtlib_purify($_REQUEST['record']);
	$sql="UPDATE ".$table_prefix."_visitreport SET
	".$table_prefix."_visitreport.vr_account_line = ".$table_prefix."_account.account_line,
	".$table_prefix."_visitreport.vr_account_client_type = ".$table_prefix."_account.account_client_type,  
	".$table_prefix."_visitreport.vr_account_main_activity = ".$table_prefix."_account.account_main_activity,  
	".$table_prefix."_visitreport.vr_account_sec_activity = ".$table_prefix."_account.account_sec_activity,  
	".$table_prefix."_visitreport.vr_account_brand = 	".$table_prefix."_account.account_brand,
	".$table_prefix."_visitreport.vr_area_intervento = 	".$table_prefix."_account.area_intervento,
	".$table_prefix."_visitreport.vr_account_yearly_pot = 	".$table_prefix."_account.account_yearly_pot
	FROM ".$table_prefix."_visitreport
	join ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_visitreport.accountid 
	join ".$table_prefix."_crmentity on  ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
	join ".$table_prefix."_accountscf on  ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
	join ".$table_prefix."_accountbillads on  ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_account.accountid
	where ".$table_prefix."_visitreport.visitreportid = ?";
	$result = $adb->pquery($sql,array($record ));
}
if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] =='Accounts' && isset($_REQUEST['parent_id']) && $_REQUEST['parent_id'] > 0) {
	global $adb, $table_prefix, $current_user;
	$parent_id = vtlib_purify($_REQUEST['parent_id']);
	$sql="SELECT 
		".$table_prefix."_account.accountid, 
		".$table_prefix."_account.accountname,
		".$table_prefix."_account.area_mng_no, 
		".$table_prefix."_account.area_mng_name, bill_country, 
		".$table_prefix."_account.account_line,  
		".$table_prefix."_account.account_client_type,  
		".$table_prefix."_account.account_main_activity,  
		".$table_prefix."_account.account_sec_activity,  
		".$table_prefix."_account.account_brand, 
		".$table_prefix."_account.area_intervento,
		".$table_prefix."_account.account_yearly_pot,
		".$table_prefix."_users.agent_cod_capoarea, amuser.first_name + ' '+ amuser.last_name as agent_name_capoarea
		FROM ".$table_prefix."_crmentity
		join ".$table_prefix."_account on  ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
		join ".$table_prefix."_accountscf on  ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
		join ".$table_prefix."_accountbillads on  ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_account.accountid
		left join ".$table_prefix."_users on ".$table_prefix."_users.id = ?
		LEFT JOIN ".$table_prefix."_users as amuser on amuser.erp_code = ".$table_prefix."_users.agent_cod_capoarea AND  ".$table_prefix."_users.agent_cod_capoarea <> ''
		where ".$table_prefix."_crmentity.crmid = ?";
		$result = $adb->pquery($sql,array($current_user->id,$parent_id ));
		$accounts_customer = $adb->query_result($result,0,"accountid");
		$area_mng_no = $adb->query_result($result,0,"area_mng_no");
		$area_mng_name = $adb->query_result($result,0,"area_mng_name");
		$bill_country = $adb->query_result($result,0,"bill_country");
		$account_line = $adb->query_result($result,0,"account_line");
		$account_client_type = $adb->query_result($result,0,"account_client_type");
		$account_main_activity = $adb->query_result($result,0,"account_main_activity");
		$account_sec_activity = $adb->query_result($result,0,"account_sec_activity");
		$account_brand = $adb->query_result($result,0,"account_brand");
		$agent_cod_capoarea = $adb->query_result($result,0,"agent_cod_capoarea");
		$agent_name_capoarea = $adb->query_result($result,0,"agent_name_capoarea");
		$area_intervento  =  $adb->query_result($result,0,"area_intervento");
		$account_yearly_pot  =  $adb->query_result($result,0,"account_yearly_pot");
		$_REQUEST['vr_account_line'] = $account_line;
		$_REQUEST['vr_account_client_type'] = $account_client_type;
		$_REQUEST['vr_account_main_activity'] = $account_main_activity;
		$_REQUEST['vr_account_sec_activity'] = $account_sec_activity;
		$_REQUEST['vr_account_brand'] = $account_brand;
		$_REQUEST['vr_area_intervento'] = $area_intervento;
		$_REQUEST['vr_account_yearly_pot'] = $account_yearly_pot;
}
require_once('modules/VteCore/EditView.php');
if($focus->mode == 'edit') {
	$smarty->display('salesEditView.tpl');
} else {
	$smarty->display('CreateView.tpl');
}

?>