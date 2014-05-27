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

// Create module instance and save it first
$module = Vtiger_Module::getInstance('Vendors');

$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activities',Array('ADD'),'get_activities');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activity History',Array('ADD'),'get_history');

?>
