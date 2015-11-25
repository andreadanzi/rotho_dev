<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): mmbrich
 ********************************************************************************/

require_once('modules/CustomView/CustomView.php');
require_once('user_privileges/default_module_view.php');
global $table_prefix;
global $singlepane_view,$adb,$current_user,$currentModule;

//danzi.tn@20150630 funzione add_related_contacts custom per attaccare i contatti delle aziende di un target
function add_related_contacts($module, $crmid, $with_module, $with_crmid) {
    global $adb,$table_prefix,$log;
    $log->debug("add_related_contacts is starting(".$crmid.", ".$with_module.")");    
    if(!is_array($with_crmid)) $with_crmid = Array($with_crmid);
    $accountsid = implode(",",$with_crmid);
    if($with_module=='Accounts') {
        $sql = "INSERT INTO ".$table_prefix."_crmentityrel 
            (crmid,module,relcrmid,relmodule) 
            SELECT DISTINCT 
            ".$table_prefix."_targets.targetsid,
            'Targets',
            c.contactid,
            'Contacts'
            FROM ".$table_prefix."_targets 
            JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid and ".$table_prefix."_crmentityrel.relmodule = 'Accounts' 
            JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_crmentityrel.relcrmid and ".$table_prefix."_crmentity.deleted = 0
            JOIN ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
            JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_account.accountid
            JOIN ".$table_prefix."_contactdetails c on c.accountid = ".$table_prefix."_account.accountid
            JOIN ".$table_prefix."_crmentity conent on conent.crmid = c.contactid and conent.deleted = 0
            LEFT JOIN ".$table_prefix."_crmentityrel conrel on conrel.relcrmid = conent.crmid and conrel.crmid = ".$table_prefix."_targets.targetsid and conrel.relmodule = 'Contacts'
            WHERE ".$table_prefix."_targets.targetsid = ?
            AND conrel.crmid IS NULL 
            AND ".$table_prefix."_account.accountid in (".$accountsid.")";
            $log->debug($sql);
        $result = $adb->pquery($sql,array($crmid));            
    }
    $log->debug("add_related_contacts terminated");
}


$queryGenerator = QueryGenerator::getInstance(vtlib_purify($_REQUEST["list_type"]), $current_user);
$queryGenerator->initForCustomViewById(vtlib_purify($_REQUEST["cvid"]));
$list_query = $queryGenerator->getQuery();
$list_query = replaceSelectQuery($list_query,$table_prefix.'_crmentity.crmid');
$res = $adb->query($list_query);
if ($res && $adb->num_rows($res)>0) {
	$ids = array();
	$focus = CRMEntity::getInstance($currentModule);
	while($row=$adb->fetchByAssoc($res)) {
		$ids[] = $row['crmid'];
	}
	$focus->save_related_module($currentModule, $_REQUEST['return_id'], $_REQUEST["list_type"], $ids);
    //danzi.tn@20150630 funzione add_related_contacts custom per attaccare i contatti delle aziende di un target
    add_related_contacts($currentModule, $_REQUEST['return_id'],  $_REQUEST["list_type"], $ids);
}

header("Location: index.php?module=Targets&action=TargetsAjax&file=CallRelatedList&ajax=true&".
"record=".vtlib_purify($_REQUEST['return_id']));
?>