<?php
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/

require_once('config.inc.php');

/** Verify the script call is from trusted place. */
global $application_unique_key;
if($_REQUEST['app_key'] != $application_unique_key) {
	echo "Access denied!";
	exit;
}
/**
 * Check if instance of this service is already running?
 */
$svcname = $_REQUEST['service'];
// We need to make sure the PIDfile name is unqique
$servicePIDFile = "logs/$svcname-service.pid";

if(file_exists($servicePIDFile)) {
	echo "Service $svcname already running! Check $servicePIDFile";
	exit;
} else {
	$servicePIDFp = fopen($servicePIDFile, 'a');
}

/**
 * Turn-off PHP error reporting.
 */
//try { error_reporting(0); } catch(Exception $e) { }

require_once('modules/MailchimpSync/SyncWithMailChimpUtils.php');

global $record;
global $module_name;
global $table_prefix;

$module_name = 'MailchimpSync';
$record_array = getAllTargetsToSync();
foreach($record_array as $record_item) {
	$record = $record_item;
	// Add subscribed contact to mailchimp since last sync
	syncTargetsWithMailChimp();
	syncUnsubscribedWithMailChimp();
	updateVtigerSyncDiffTable();
}
syncCampaings();


/** Close and remove the PID file. */
if($servicePIDFp) {
	fclose($servicePIDFp);
	unlink($servicePIDFile);
}
?>