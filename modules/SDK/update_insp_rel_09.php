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
// Remove Help Desk to Relations
// $module = Vtiger_Module::getInstance('Inspections');
// $module->unsetRelatedList(Vtiger_Module::getInstance('HelpDesk'), 'Help Desk');
// Remove Help Desk to Relations
// $module = Vtiger_Module::getInstance('Relations');
// $module->unsetRelatedList(Vtiger_Module::getInstance('HelpDesk'), 'Help Desk');

$helpdesk = Vtiger_Module::getInstance('HelpDesk');
$helpdesk->unsetRelatedList(Vtiger_Module::getInstance('Inspections'), 'Inspections');
$helpdesk->unsetRelatedList(Vtiger_Module::getInstance('Relations'), 'Inspections');

?>
