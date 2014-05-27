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

$module = Vtiger_Module::getInstance('HelpDesk');
// c'è già Vtiger_Event::register($module ,'vtiger.entity.aftersave','HelpDeskHandler','modules/HelpDesk/HelpDeskHandler.php');
Vtiger_Event::register($module ,'vtiger.entity.beforesave','HelpDeskHandler','modules/HelpDesk/HelpDeskHandler.php');


?>