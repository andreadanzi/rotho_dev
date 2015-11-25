<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
//danzi.tn@20150630 funzione ajax custom per attaccare i contatti delle aziende di un target
 global $log,$adb;
$action = $_REQUEST["action"];

if($action == 'TargetsAjax')
{
	$ajaxaction = $_REQUEST["ajaxaction"]; // it should be ADDRELATEDCONTACTS
	$recordid =$_REQUEST['recordid'];
	if($recordid != '')
	{
        $sql = "INSERT INTO vtiger_crmentityrel
                (crmid,module,relcrmid,relmodule)
                SELECT DISTINCT 
                vtiger_targets.targetsid,
                'Targets',
                c.contactid,
                'Contacts'
                FROM vtiger_targets 
                JOIN vtiger_crmentityrel on vtiger_crmentityrel.crmid = vtiger_targets.targetsid and vtiger_crmentityrel.relmodule = 'Accounts'
                JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid and vtiger_crmentity.deleted = 0
                JOIN vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid
                JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid = vtiger_account.accountid
                JOIN vtiger_contactdetails c on c.accountid = vtiger_account.accountid
                JOIN vtiger_crmentity conent on conent.crmid = c.contactid and conent.deleted = 0
                LEFT JOIN vtiger_crmentityrel conrel on conrel.relcrmid = conent.crmid and conrel.crmid = vtiger_targets.targetsid and conrel.relmodule = 'Contacts'
                WHERE vtiger_targets.targetsid = ?
                AND conrel.crmid IS NULL";
        $result = $adb->pquery($sql,array($recordid));
		echo ':#:ADD_OK '.$recordid;
        exit();
	} else {
		echo ':#:ADD_FAILURE MISSING RECORDID';
		exit();
	}
} else{
	echo ':#:ADD_FAILURE MISSING TARGETAJAX';
	exit();
}
?>