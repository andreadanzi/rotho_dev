<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/

 // danzi.tn@20140724 gestione parent_id  vtiger_visitreport
 // danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_client_type
if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] =='Visitreport' && isset($_REQUEST['parent_id']) && $_REQUEST['parent_id'] > 0) {
	global $adb, $table_prefix, $current_user;
	$parent_id = vtlib_purify($_REQUEST['parent_id']);
	$sql="SELECT ".$table_prefix."_visitreport.visitreportid, ".$table_prefix."_visitreport.visitreportname,
	".$table_prefix."_account.accountid, 
	".$table_prefix."_account.accountname,
	".$table_prefix."_account.area_mng_no, 
	".$table_prefix."_account.area_mng_name, bill_country, vtiger_account.account_client_type as category,  
	".$table_prefix."_users.agent_cod_capoarea, amuser.first_name + ' '+ amuser.last_name as agent_name_capoarea
	FROM ".$table_prefix."_crmentity
	join ".$table_prefix."_visitreport on ".$table_prefix."_visitreport.visitreportid = ".$table_prefix."_crmentity.crmid
	join ".$table_prefix."_account on  ".$table_prefix."_account.accountid = ".$table_prefix."_visitreport.accountid
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
	$customer_cat = $adb->query_result($result,0,"category");
	$agent_cod_capoarea = $adb->query_result($result,0,"agent_cod_capoarea");
	$agent_name_capoarea = $adb->query_result($result,0,"agent_name_capoarea");
	if( empty($area_mng_no)) {
		$area_mng_no = $agent_cod_capoarea;
		$area_mng_name = $agent_name_capoarea;
	}
	$_REQUEST['accounts_customer'] = $accounts_customer;
	$_REQUEST['customer_cat'] = $customer_cat;
	$_REQUEST['area_mng_no'] = $area_mng_no;
	$_REQUEST['area_mng_name'] = $area_mng_name;
	$_REQUEST['country'] = $bill_country;
}
// danzi.tn@20140724 parent_id 
 
 
 require_once 'modules/VteCore/EditView.php';	//crmv@30447

if ($focus->mode == 'edit')
	$smarty->display('salesEditView.tpl');
else
	$smarty->display('CreateView.tpl');
?>