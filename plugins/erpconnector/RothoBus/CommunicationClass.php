<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 * ********************************************************************************** */
// Switch the working directory to base
// chdir(dirname(__FILE__) . '/../..');

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'data/CRMEntity.php';

class CommunicationClass {
	var $log_active = false;
	var $import_result = Array();
	var $mapping = Array();
	
	function __construct() { 
		// set mapping between columns of temp tables and vtiger fieldnames
	}
		
	function setLog($log_active) {
		$this->log_active = $log_active;
	}
	/*
	SELECT 
				vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.date_start, vtiger_crmentity.description, accentity.crmid, accentity.setype, accentity.smownerid, vtiger_account.email1, vtiger_account.email2
				from vtiger_activity
				join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.deleted = 0
				join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid 
				join vtiger_crmentity accentity on accentity.crmid = vtiger_seactivityrel.crmid AND accentity.deleted = 0
				join vtiger_account on vtiger_account.accountid = accentity.crmid 
				where vtiger_activity.activitytype = 'Comunicazione variazioni (Auto-gen)'
				AND vtiger_activity.eventstatus = 'Planned'
				AND  vtiger_activity.date_start = CONVERT (date, GETDATE())
				AND  vtiger_activity.time_start <= left( CONVERT (time, GETDATE()), 5)
	*/
	// populates vtiger entities from temp tables
	function communicateNow($bTest = false, $test_email = "andrea@danzi.tn.it") {
		global $adb,$table_prefix,$default_charset;
		if($this->log_active) echo "CommunicationClass.populateNow is starting!\n";
		// associative array for outputting the number of items inserted or updated
		$this->import_result['records_created']=0;
		$this->import_result['records_updated']=0;
		$sql = "SELECT 
				".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject, ".$table_prefix."_activity.date_start, ".$table_prefix."_crmentity.description, accentity.crmid, accentity.setype, accentity.smownerid, ".$table_prefix."_account.email1, ".$table_prefix."_account.email2
				from ".$table_prefix."_activity
				join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid AND ".$table_prefix."_crmentity.deleted = 0
				join ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid 
				join ".$table_prefix."_crmentity accentity on accentity.crmid = ".$table_prefix."_seactivityrel.crmid AND accentity.deleted = 0
				join ".$table_prefix."_account on ".$table_prefix."_account.accountid = accentity.crmid 
				where ".$table_prefix."_activity.activitytype = 'Comunicazione variazioni (Auto-gen)'
				AND ".$table_prefix."_activity.eventstatus = 'Planned'
				AND  ".$table_prefix."_activity.date_start = CONVERT (date, GETDATE())
				AND  ".$table_prefix."_activity.time_start <= left( CONVERT (time, GETDATE()), 5)";
		if($this->log_active) echo "CommunicationClass.populateNow sql = ".$sql."\n";
		$wsresult = $adb->query($sql);
		$matches = array();
		while($row = $adb->fetchByAssoc($wsresult)) {
			$ifound = 0;
			$description = $row['description'];
			$description = html_entity_decode($description, ENT_NOQUOTES, $default_charset);
			if($this->log_active) echo "CommunicationClass.communicateNow description is ".  $description;
			preg_match_all("/<templateid>(.*?)<\/templateid>/s", $description, $matches);
			if($this->log_active) print_r($matches);
			if(!empty($matches[1]) ){
				$match = $matches[1][0];
				if($this->log_active) echo "CommunicationClass.communicateNow found emailtemplateid match {$match} for activityid = ".$row['activityid']."\n";
				if(intval($match) > 0) {
					$email1 = $row['email1'];
					$email2 = $row['email2'];
					$smownerid = $row['smownerid'];
					if($bTest) {
						$email1 = $test_email;
						$email2 = $test_email;
						$smownerid = 1;
					} 
					send_client_notification( intval($match),$row['crmid'], $email1, $email2, $smownerid);
					$this->import_result['records_created']++;
					$usql = "UPDATE  ".$table_prefix."_activity SET ".$table_prefix."_activity.eventstatus = 'Held' WHERE ".$table_prefix."_activity.activityid = ?";
					$adb->pquery($usql,array($row['activityid']));
					$ifound++;
				}
			}
			if($ifound == 0) {
				if($this->log_active) echo "CommunicationClass.communicateNow match not found for activityid = ".$row['activityid']."\n";
				$usql = "UPDATE  ".$table_prefix."_activity SET ".$table_prefix."_activity.eventstatus = 'Not Held' WHERE ".$table_prefix."_activity.activityid = ?";
				$adb->pquery($usql,array($row['activityid']));
				$this->import_result['records_updated']++;
			}
		}	
		return $this->import_result;
	}

	
}

?>
