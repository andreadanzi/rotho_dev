<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/

/* mycrmv@2707m */

$startTime = microtime();

global $current_user, $currentModule, $adb, $table_prefix;
$currentModule = 'Leads';
$category = getParentTab();

require_once 'include/Webservices/ConvertLead.php';
require_once 'include/utils/VtlibUtils.php';

$result = $adb->pquery("select {$table_prefix}_leaddetails.*,{$table_prefix}_crmentity.smownerid from {$table_prefix}_leaddetails
						inner join {$table_prefix}_crmentity on {$table_prefix}_crmentity.crmid = {$table_prefix}_leaddetails.leadid
						inner join vte_listview_check on vte_listview_check.crmid = {$table_prefix}_leaddetails.leadid
						where {$table_prefix}_crmentity.deleted = 0 and vte_listview_check.userid = ? and vte_listview_check.tabid = ?",
						array($current_user->id,7));
$lead_info = array();
if ($result && $adb->num_rows($result)) {
	while($row=$adb->fetchByAssoc($result)) {
		$lead_info[$row['leadid']] = $row;
	}
} else {
	showError();
}

$elements = array();
$skipped_convertions = array();
$selected_leads = getListViewCheck($currentModule);
if (empty($selected_leads)) {
	showError();
}
foreach ($selected_leads as $recordId) {
	$leadId = vtws_getWebserviceEntityId('Leads', $recordId);
	
	if (empty($lead_info[$recordId]['company'])) {
		$skipped_convertions['company'] = $recordId;
		continue;
	}
	if (empty($lead_info[$recordId]['lastname'])) {
		$skipped_convertions['lastname'] = $recordId;
		continue;
	}

	if (isset($_REQUEST['enable_massconvert_assigned_to']) && $_REQUEST['enable_massconvert_assigned_to'] == 'on') {
		$assigned_to = vtlib_purify($_REQUEST["c_assigntype"]);
		if ($assigned_to == "U") {
			$assigned_user_id = vtlib_purify($_REQUEST["c_assigned_user_id"]);
			$assignedTo = vtws_getWebserviceEntityId('Users', $assigned_user_id);
		} else {
			$assigned_user_id = vtlib_purify($_REQUEST["c_assigned_group_id"]);
			$assignedTo = vtws_getWebserviceEntityId('Groups', $assigned_user_id);
		}
	} else {
		$assigned_user_id = $lead_info[$recordId]['smownerid'];
		$result = $adb->pquery("select * from {$table_prefix}_users where id = ?",array($assigned_user_id));
		if ($result && $adb->num_rows($result) > 0) {
			$assignedTo = vtws_getWebserviceEntityId('Users', $assigned_user_id);
		} else {
			$assignedTo = vtws_getWebserviceEntityId('Groups', $assigned_user_id);
		}
	}

	$transferRelatedRecordsTo = vtlib_purify($_REQUEST['transferto']);
	if (empty($transferRelatedRecordsTo)) {
		$transferRelatedRecordsTo = 'Contacts';
	}

	$entityValues = array();

	$entityValues['transferRelatedRecordsTo'] = $transferRelatedRecordsTo;
	$entityValues['assignedTo'] = $assignedTo;
	$entityValues['leadId'] = $leadId;

	if(vtlib_isModuleActive('Accounts')){
		$entityValues['entities']['Accounts']['create'] = true;
		$entityValues['entities']['Accounts']['name'] = 'Accounts';
		$entityValues['entities']['Accounts']['accountname'] = $lead_info[$recordId]['company'];
	}

	if(vtlib_isModuleActive('Contacts')){
		$entityValues['entities']['Contacts']['create'] = true;
		$entityValues['entities']['Contacts']['name'] = 'Contacts';
		$entityValues['entities']['Contacts']['lastname'] = $lead_info[$recordId]['lastname'];
		$entityValues['entities']['Contacts']['firstname'] = $lead_info[$recordId]['firstname'];
		$entityValues['entities']['Contacts']['email'] = $lead_info[$recordId]['email'];
	}
	try{
		$result = vtws_convertlead($entityValues,$current_user);
	}catch(Exception $e){
		$skipped_convertions['errors'] = $recordId;
		continue;
	}

	$accountIdComponents = vtws_getIdComponents($result['Accounts']);
	$accountId = $accountIdComponents[1];
	$contactIdComponents = vtws_getIdComponents($result['Contacts']);
	$contactId = $contactIdComponents[1];
	$elements[] = array('lead'=>$leadId,'account'=>$accountId,'contact'=>$contactId);
}

/*
echo '<pre>';
print_r($skipped_convertions);
print_r($elements);
echo '</pre>';
$endTime = microtime();
list($usec, $sec) = explode(" ", $endTime);
$endTime = ((float)$usec + (float)$sec);
list($usec, $sec) = explode(" ", $startTime);
$startTime = ((float)$usec + (float)$sec);
$deltaTime = round($endTime - $startTime,2);
echo('&nbsp;Server response time: '.$deltaTime.' seconds.');
die;
*/
header("Location: index.php?action=index&module=Leads");

function showError(){
	require_once 'include/utils/VtlibUtils.php';
	global $current_user, $currentModule, $theme, $app_strings,$log;
    echo "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
    echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
    echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
		<tbody><tr>
		<td rowspan='2' width='11%'><img src='" . vtiger_imageurl('denied.gif', $theme) . "' ></td>
		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
			<span class='genHeaderSmall'>". getTranslatedString('SINGLE_'.$currentModule, $currentModule)." ".
			getTranslatedString('CANNOT_CONVERT', $currentModule)  ."
		<br>
		<ul> ". getTranslatedString('LBL_FOLLOWING_ARE_POSSIBLE_REASONS', $currentModule) .":
			<li>". getTranslatedString('LBL_LEADS_FIELD_MAPPING_INCOMPLETE', $currentModule) ."</li>
			<li>". getTranslatedString('LBL_MANDATORY_FIELDS_ARE_EMPTY', $currentModule) ."</li>
		</ul>
		</span>
		</td>
		</tr>
		<tr>
		<td class='small' align='right' nowrap='nowrap'>";

    if (is_admin($current_user)) {
        echo "<a href='index.php?module=Settings&action=CustomFieldList&parenttab=Settings&formodule=Leads'>". getTranslatedString('LBL_LEADS_FIELD_MAPPING', $currentModule) ."</a><br>";
    }

    echo "<a href='javascript:window.history.back();'>". getTranslatedString('LBL_GO_BACK', $currentModule) ."</a><br>";

    echo "</td>
               </tr>
		</tbody></table>
		</div>
                </td></tr></table>";
}
?>