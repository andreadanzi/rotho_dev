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
 Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'SalesOrderToInspections', 'modules/Inspections/SalesOrderToInspections.js');
SDK::setExtraSrc('Inspections', 'modules/Inspections/SalesOrderToInspections.js');
SDK::setPopupReturnFunction('Inspections', 'salesorderid', 'modules/Inspections/SalesOrderToInspections.php');
// Per la gestionde delle email
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'MailInsp', 'include/js/Mail.js');
SDK::setExtraSrc('Inspections', 'include/js/Mail.js');
// Revisioni collegate
$module->setRelatedList($module, 'Inspections', Array('SELECT'),'get_related_inspections_list');

?>
