<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20140602 Vendor handler
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Create module instance and save it first
$module = Vtiger_Module::getInstance('Vendors');
Vtiger_Event::register($module ,'vtiger.entity.beforesave','VendorsHandler','modules/SDK/src/modules/Vendors/VendorsHandler.php');
Vtiger_Event::register($module ,'vtiger.entity.aftersave','VendorsHandler','modules/SDK/src/modules/Vendors/VendorsHandler.php');
// danzi.tn@20140602e

?>
