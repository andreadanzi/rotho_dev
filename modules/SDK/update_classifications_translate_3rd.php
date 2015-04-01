<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix,$default_charset;
session_start();
// html_entity_decode($description, ENT_NOQUOTES, $default_charset);
// htmlentities( , ENT_NOQUOTES, $default_charset);
//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$sql = "select distinct vtiger_account_client_type.account_client_type, sdk_language.language , convert(varchar(200), sdk_language.trans_label ) as trans_label
from vtiger_account_client_type 
JOIN sdk_language on convert(varchar(200),sdk_language.label) = vtiger_account_client_type.account_client_type 
AND sdk_language.module = 'Accounts'";
$res = $adb->query($sql);
$wsresult = $adb->query($sql);
while($row = $adb->fetchByAssoc($wsresult)) {
	$trans_label = $row['trans_label'];
	$trans_label = html_entity_decode($trans_label, ENT_COMPAT, 'UTF-8');
	SDK::setLanguageEntry('DNZ4QLIK' , $row['language'] , $row['account_client_type'] , $trans_label);
}

$sql = "select distinct vtiger_account_main_activity.account_main_activity, sdk_language.language , convert(varchar(200), sdk_language.trans_label ) as trans_label
from vtiger_account_main_activity
JOIN sdk_language on convert(varchar(200),sdk_language.label) = vtiger_account_main_activity.account_main_activity
AND sdk_language.module = 'Accounts'";
$res = $adb->query($sql);
$wsresult = $adb->query($sql);
while($row = $adb->fetchByAssoc($wsresult)) {
	$trans_label = $row['trans_label'];
	$trans_label = html_entity_decode($trans_label, ENT_COMPAT, 'UTF-8');
	SDK::setLanguageEntry('DNZ4QLIK' , $row['language'] , $row['account_main_activity'] , $trans_label);
}

$sql = "select distinct vtiger_account_sec_activity.account_sec_activity, sdk_language.language , convert(varchar(200), sdk_language.trans_label ) as trans_label
from vtiger_account_sec_activity
JOIN sdk_language on convert(varchar(200),sdk_language.label) = vtiger_account_sec_activity.account_sec_activity 
AND sdk_language.module = 'Accounts';";
$res = $adb->query($sql);
$wsresult = $adb->query($sql);
while($row = $adb->fetchByAssoc($wsresult)) {
	$trans_label = $row['trans_label'];
	$trans_label = html_entity_decode($trans_label, ENT_COMPAT, 'UTF-8');
	SDK::setLanguageEntry('DNZ4QLIK' , $row['language'] , $row['account_sec_activity'] , $trans_label);
}


?>