<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();


// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
// Retrieve module instance
$srcdir = 'modules/SDK/src/';
$module = Vtiger_Module::getInstance('Accounts');
// danzi.tn@20160104 visibility albero utenti
global $adb,$table_prefix;

$adb->query("delete from ".$table_prefix."_actionmapping WHERE actionid = 12 AND actionname='Users'");
$adb->query("insert into ".$table_prefix."_actionmapping values(12,'Users',0)");
$module->disableTools('Users');
$module->enableTools('Users');


?>
