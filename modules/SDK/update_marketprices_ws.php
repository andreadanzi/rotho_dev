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

// Get module instance
$module = Vtiger_Module::getInstance('Marketprices');
/** Set sharing access of this module */
$module->setDefaultSharing('Private');
/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge'); 
// per aggiungere il supporto ai webservices
$module->initWebservice();

?>
