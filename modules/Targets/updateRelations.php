<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('include/database/PearDatabase.php');
@include_once('user_privileges/default_module_view.php');
//danzi.tn@20150630 funzione add_related_contacts/delete_related_contacts custom per attaccare/staccare i contatti delle aziende di un target
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

function delete_related_contacts($module, $crmid, $with_module, $with_crmid) {
    global $adb,$table_prefix,$log;
    $log->debug("delete_related_contacts is starting(".$crmid.", ".$with_module.")");
    if(!is_array($with_crmid)) $with_crmid = Array($with_crmid);
    $accountsid = implode(",",$with_crmid);
    if($with_module=='Accounts') {
        $deletesql = "DELETE ".$table_prefix."_crmentityrel  FROM 
            ".$table_prefix."_crmentityrel 
            JOIN ".$table_prefix."_contactdetails on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentityrel.relcrmid 
            JOIN ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_contactdetails.accountid 
            JOIN ".$table_prefix."_crmentityrel accrel on accrel.crmid = ".$table_prefix."_crmentityrel.crmid and accrel.relcrmid = ".$table_prefix."_account.accountid             
            WHERE ".$table_prefix."_crmentityrel.crmid = ".$crmid." 
            AND ".$table_prefix."_account.accountid in (".$accountsid.")";
        $log->debug($deletesql);
        $result = $adb->query($deletesql);            
    }
    $log->debug("delete_related_contacts terminated");
}


global $adb, $singlepane_view, $currentModule;
$idlist            = vtlib_purify($_REQUEST['idlist']);
$destinationModule = vtlib_purify($_REQUEST['destination_module']);
$parenttab         = getParentTab();

$forCRMRecord = vtlib_purify($_REQUEST['parentid']);
$mode = $_REQUEST['mode'];
set_time_limit(0);
if($singlepane_view == 'true')
	$action = "DetailView";
else
	$action = "CallRelatedList";

$focus = CRMEntity::getInstance($currentModule);

if($mode == 'delete') {
	// Split the string of ids
	$ids = explode (";",$idlist);
	if(!empty($ids)) {
        delete_related_contacts($currentModule, $forCRMRecord, $destinationModule, $ids);
		$focus->delete_related_module($currentModule, $forCRMRecord, $destinationModule, $ids);
	}
} else {
	if(!empty($_REQUEST['idlist'])) {
		// Split the string of ids
		$ids = explode (";",trim($idlist,";"));
	} else if(!empty($_REQUEST['entityid'])){
		$ids = $_REQUEST['entityid'];
	}
	if(!empty($ids)) {
		$focus->save_related_module($currentModule, $forCRMRecord, $destinationModule, $ids);
        add_related_contacts($currentModule, $forCRMRecord, $destinationModule, $ids);
	}
}
header("Location: index.php?module=$currentModule&record=$forCRMRecord&action=$action&parenttab=$parenttab");
?>