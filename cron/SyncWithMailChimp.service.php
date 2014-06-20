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

$db = PearDatabase::getInstance();

$query = 'SELECT * FROM '.$table_prefix.'_mailchimpsync';

$result = $db->query($query);

while($donnee = $db->fetch_row($result)){
	
	$record = $donnee['mailchimpsyncid'];
	$list_id = getListId();
	//Synchronization from Vtiger To MailChimp
	echo '<h2>syncSubscribedWithMailChimp</h2>';
	syncSubscribedWithMailChimp();
	syncUnsubscribedWithMailChimp();
	//Synchronization from MailChimp to Vtiger
	echo '<h2>getupdatedmembers</h2>';
	getListMembers('updated');
	echo '<h2>getunsubscribedmembers</h2>';
	getListMembers('unsubscribed');
	//Set New sync date and update diff table
	setLastSyncDate();
	updateVtigerDiffTable();
}


/** Close and remove the PID file. */
if($servicePIDFp) {
	fclose($servicePIDFp);
	unlink($servicePIDFile);
}
?>