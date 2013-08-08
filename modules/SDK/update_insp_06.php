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
$module = Vtiger_Module::getInstance('Inspections');
// Gestione popup con setPopupReturnFunction per il popup Aziende
 Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'ProductToInspections', 'modules/Inspections/ProductToInspections.js');
 Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'CategoryToInspections', 'modules/Inspections/CategoryToInspections.js');

?>
